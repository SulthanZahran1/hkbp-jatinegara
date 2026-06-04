package main

import (
	"database/sql"
	"errors"
	"strconv"
	"strings"
	"time"

	"github.com/gofiber/fiber/v2"
)

type accessDecisionRequest struct {
	RoleID       int64  `json:"role_id"`
	SektorID     *int64 `json:"sektor_id"`
	NamaDepan    string `json:"nama_depan"`
	NamaBelakang string `json:"nama_belakang"`
	TargetUserID *int64 `json:"target_user_id"`
	Note         string `json:"note"`
}

// ensureAccessRequestTx creates a pending access request for an unknown/unlinked/
// disabled identity, unless one is already pending or a recent rejection is still
// inside the cooldown window. Returns whether a new row was created.
func (s *Server) ensureAccessRequestTx(tx *sql.Tx, reqType, issuer string, claims oidcClaims, userID *int64, ip string) (bool, error) {
	var status, decidedAt sql.NullString
	err := tx.QueryRow(`SELECT status, decided_at FROM access_requests
		WHERE issuer = ? AND subject = ? ORDER BY id DESC LIMIT 1`, issuer, claims.Subject).Scan(&status, &decidedAt)
	switch {
	case err == nil:
		switch status.String {
		case "pending":
			return false, nil // already queued; don't create spam rows
		case "rejected":
			if withinCooldown(decidedAt, s.cfg.RejectedCooldown) {
				return false, nil // cooldown still active → show denied/pending instead
			}
		}
	case !errors.Is(err, sql.ErrNoRows):
		return false, err
	}

	var suggested, target any
	if userID != nil {
		switch reqType {
		case "link_existing_user":
			suggested = *userID
		case "reactivate_user":
			target = *userID
		}
	}

	res, err := tx.Exec(`INSERT INTO access_requests
		(request_type, status, issuer, subject, preferred_username, email, email_verified, suggested_user_id, target_user_id)
		VALUES (?, 'pending', ?, ?, ?, ?, ?, ?, ?)`,
		reqType, issuer, claims.Subject, claims.PreferredUsername, nullable(claims.Email), boolInt(claims.EmailVerified), suggested, target)
	if err != nil {
		return false, err
	}
	id, _ := res.LastInsertId()
	s.audit(tx, EventAccessRequestCreated, nil, "access_request", strconv.FormatInt(id, 10),
		map[string]any{"request_type": reqType, "preferred_username": claims.PreferredUsername, "issuer": issuer}, ip)
	return true, nil
}

// withinCooldown reports whether a rejected request decided at decidedAt is still
// inside the cooldown window.
func withinCooldown(decidedAt sql.NullString, cooldown time.Duration) bool {
	if !decidedAt.Valid || decidedAt.String == "" {
		return false
	}
	t, err := time.Parse(sqlTime, decidedAt.String)
	if err != nil {
		return false
	}
	return time.Now().UTC().Before(t.Add(cooldown))
}

func (s *Server) listAccessRequests(c *fiber.Ctx) error {
	where := "WHERE 1 = 1"
	args := []any{}
	if status := c.Query("status"); status != "" {
		where += " AND ar.status = ?"
		args = append(args, status)
	}

	rows, err := s.db.Query(`SELECT ar.id, ar.request_type, ar.status, ar.issuer, ar.subject,
		ar.preferred_username, ar.email, ar.email_verified, ar.suggested_user_id, su.username,
		ar.target_user_id, tu.username, ar.decided_by, du.username, ar.decided_at, ar.decision_note,
		ar.created_at, ar.updated_at
		FROM access_requests ar
		LEFT JOIN users su ON su.id = ar.suggested_user_id
		LEFT JOIN users tu ON tu.id = ar.target_user_id
		LEFT JOIN users du ON du.id = ar.decided_by `+where+`
		ORDER BY CASE ar.status WHEN 'pending' THEN 0 ELSE 1 END, ar.id DESC`, args...)
	if err != nil {
		return internalError(c, err)
	}
	defer rows.Close()

	data := []fiber.Map{}
	for rows.Next() {
		var id int64
		var reqType, status, issuer, subject, preferredUsername, createdAt, updatedAt string
		var emailVerified int
		var email, suggestedUsername, targetUsername, decidedByUsername, decidedAt, note sql.NullString
		var suggestedUserID, targetUserID, decidedBy sql.NullInt64
		if err := rows.Scan(&id, &reqType, &status, &issuer, &subject, &preferredUsername, &email, &emailVerified,
			&suggestedUserID, &suggestedUsername, &targetUserID, &targetUsername, &decidedBy, &decidedByUsername,
			&decidedAt, &note, &createdAt, &updatedAt); err != nil {
			return internalError(c, err)
		}
		data = append(data, fiber.Map{
			"id":                  id,
			"request_type":        reqType,
			"status":              status,
			"issuer":              issuer,
			"subject":             subject,
			"preferred_username":  preferredUsername,
			"email":               stringPtr(email),
			"email_verified":      emailVerified == 1,
			"suggested_user_id":   int64Ptr(suggestedUserID),
			"suggested_username":  stringPtr(suggestedUsername),
			"target_user_id":      int64Ptr(targetUserID),
			"target_username":     stringPtr(targetUsername),
			"decided_by":          int64Ptr(decidedBy),
			"decided_by_username": stringPtr(decidedByUsername),
			"decided_at":          stringPtr(decidedAt),
			"decision_note":       stringPtr(note),
			"created_at":          createdAt,
			"updated_at":          updatedAt,
		})
	}

	var pending int
	if err := s.db.QueryRow("SELECT COUNT(1) FROM access_requests WHERE status = 'pending'").Scan(&pending); err != nil {
		return internalError(c, err)
	}
	return c.JSON(fiber.Map{"data": data, "counts": fiber.Map{"pending": pending}})
}

type pendingRequest struct {
	ID                int64
	RequestType       string
	Issuer            string
	Subject           string
	PreferredUsername string
	Email             sql.NullString
	EmailVerified     int
	SuggestedUserID   sql.NullInt64
	TargetUserID      sql.NullInt64
}

func loadPendingRequestTx(tx *sql.Tx, id int64) (pendingRequest, error) {
	var r pendingRequest
	var status string
	err := tx.QueryRow(`SELECT id, request_type, status, issuer, subject, preferred_username,
		email, email_verified, suggested_user_id, target_user_id
		FROM access_requests WHERE id = ?`, id).
		Scan(&r.ID, &r.RequestType, &status, &r.Issuer, &r.Subject, &r.PreferredUsername,
			&r.Email, &r.EmailVerified, &r.SuggestedUserID, &r.TargetUserID)
	if err != nil {
		return r, err
	}
	if status != "pending" {
		return r, errNotPending
	}
	return r, nil
}

var errNotPending = errors.New("access request is not pending")

// approveAccessRequest transactionally applies the approval. It never creates an
// active browser session: the user must sign in again through the IdP.
func (s *Server) approveAccessRequest(c *fiber.Ctx) error {
	id, err := paramID(c)
	if err != nil {
		return badRequest(c, "invalid access request id")
	}
	var body accessDecisionRequest
	if err := c.BodyParser(&body); err != nil {
		return badRequest(c, "invalid request body")
	}
	actor := localUserPtr(c)
	ip := clientIP(c)

	tx, err := s.db.Begin()
	if err != nil {
		return internalError(c, err)
	}
	committed := false
	defer func() {
		if !committed {
			_ = tx.Rollback()
		}
	}()

	req, err := loadPendingRequestTx(tx, id)
	if errors.Is(err, sql.ErrNoRows) {
		return notFound(c, "access request not found")
	}
	if errors.Is(err, errNotPending) {
		return badRequest(c, "access request already decided")
	}
	if err != nil {
		return internalError(c, err)
	}

	claims := oidcClaims{Subject: req.Subject, PreferredUsername: req.PreferredUsername,
		Email: req.Email.String, EmailVerified: req.EmailVerified == 1}

	var resultUserID int64
	switch req.RequestType {
	case "new_user":
		if body.RoleID == 0 {
			return badRequest(c, "role_id is required to approve a new user")
		}
		namaDepan := strings.TrimSpace(body.NamaDepan)
		if namaDepan == "" {
			namaDepan = req.PreferredUsername
		}
		res, e := tx.Exec(`INSERT INTO users (username, email, nama_depan, nama_belakang, role_id, sektor_id, status, provisioning_status, session_version)
			VALUES (?, ?, ?, ?, ?, ?, 'active', 'active', 1)`,
			req.PreferredUsername, nullable(claims.Email), namaDepan, nilIfEmpty(body.NamaBelakang), body.RoleID, body.SektorID)
		if e != nil {
			return badRequest(c, e.Error())
		}
		resultUserID, _ = res.LastInsertId()
		if e := linkIdentityTx(tx, resultUserID, req.Issuer, claims); e != nil {
			return badRequest(c, e.Error())
		}
		s.audit(tx, EventIdentityLinked, actor, "user", strconv.FormatInt(resultUserID, 10),
			map[string]any{"request_type": req.RequestType, "preferred_username": req.PreferredUsername}, ip)

	case "link_existing_user":
		target := body.TargetUserID
		if target == nil && req.SuggestedUserID.Valid {
			target = &req.SuggestedUserID.Int64
		}
		if target == nil {
			return badRequest(c, "target_user_id is required to approve a link request")
		}
		var username string
		if e := tx.QueryRow("SELECT username FROM users WHERE id = ?", *target).Scan(&username); e != nil {
			if errors.Is(e, sql.ErrNoRows) {
				return badRequest(c, "target user not found")
			}
			return internalError(c, e)
		}
		if e := linkIdentityTx(tx, *target, req.Issuer, claims); e != nil {
			return badRequest(c, e.Error())
		}
		resultUserID = *target
		detail := map[string]any{"request_type": req.RequestType, "preferred_username": req.PreferredUsername}
		if username != req.PreferredUsername {
			// Username drift: HKBP handle and IdP preferred_username diverge. Surface
			// it for reconciliation rather than silently overwriting either side.
			detail["username_drift"] = map[string]any{"hkbp_username": username, "idp_preferred_username": req.PreferredUsername}
		}
		s.audit(tx, EventIdentityLinked, actor, "user", strconv.FormatInt(resultUserID, 10), detail, ip)

	case "reactivate_user":
		target := req.TargetUserID
		if body.TargetUserID != nil {
			target = sql.NullInt64{Int64: *body.TargetUserID, Valid: true}
		}
		if !target.Valid {
			return badRequest(c, "target_user_id is required to reactivate")
		}
		res, e := tx.Exec(`UPDATE users SET status = 'active', session_version = session_version + 1, updated_at = datetime('now') WHERE id = ?`, target.Int64)
		if e != nil {
			return internalError(c, e)
		}
		if affected, _ := res.RowsAffected(); affected == 0 {
			return badRequest(c, "target user not found")
		}
		// Keep the identity link in sync in case the subject rotated.
		if e := linkIdentityTx(tx, target.Int64, req.Issuer, claims); e != nil {
			return badRequest(c, e.Error())
		}
		resultUserID = target.Int64
		s.audit(tx, EventUserReactivated, actor, "user", strconv.FormatInt(resultUserID, 10),
			map[string]any{"preferred_username": req.PreferredUsername}, ip)

	default:
		return badRequest(c, "unknown request type")
	}

	if _, e := tx.Exec(`UPDATE access_requests SET status = 'approved', target_user_id = ?, decided_by = ?, decided_at = datetime('now'), decision_note = ?, updated_at = datetime('now') WHERE id = ?`,
		resultUserID, actorOrNil(actor), nilIfEmpty(body.Note), id); e != nil {
		return internalError(c, e)
	}
	s.audit(tx, EventAccessRequestApproved, actor, "access_request", strconv.FormatInt(id, 10),
		map[string]any{"request_type": req.RequestType, "user_id": resultUserID}, ip)

	if e := tx.Commit(); e != nil {
		return internalError(c, e)
	}
	committed = true
	return c.JSON(fiber.Map{"id": id, "status": "approved", "user_id": resultUserID,
		"message": "Approved. The user must sign in again to receive an active session."})
}

func (s *Server) rejectAccessRequest(c *fiber.Ctx) error {
	id, err := paramID(c)
	if err != nil {
		return badRequest(c, "invalid access request id")
	}
	var body accessDecisionRequest
	_ = c.BodyParser(&body)
	actor := localUserPtr(c)

	res, err := s.db.Exec(`UPDATE access_requests SET status = 'rejected', decided_by = ?, decided_at = datetime('now'), decision_note = ?, updated_at = datetime('now')
		WHERE id = ? AND status = 'pending'`, actorOrNil(actor), nilIfEmpty(body.Note), id)
	if err != nil {
		return internalError(c, err)
	}
	if affected, _ := res.RowsAffected(); affected == 0 {
		return badRequest(c, "access request not found or already decided")
	}
	s.audit(s.db, EventAccessRequestRejected, actor, "access_request", strconv.FormatInt(id, 10),
		map[string]any{"note": body.Note}, clientIP(c))
	return c.JSON(fiber.Map{"id": id, "status": "rejected"})
}

func (s *Server) reopenAccessRequest(c *fiber.Ctx) error {
	id, err := paramID(c)
	if err != nil {
		return badRequest(c, "invalid access request id")
	}
	actor := localUserPtr(c)
	res, err := s.db.Exec(`UPDATE access_requests SET status = 'pending', decided_by = NULL, decided_at = NULL, decision_note = NULL, updated_at = datetime('now')
		WHERE id = ? AND status != 'pending'`, id)
	if err != nil {
		return internalError(c, err)
	}
	if affected, _ := res.RowsAffected(); affected == 0 {
		return badRequest(c, "access request not found or already pending")
	}
	s.audit(s.db, EventAccessRequestReopened, actor, "access_request", strconv.FormatInt(id, 10), nil, clientIP(c))
	return c.JSON(fiber.Map{"id": id, "status": "pending"})
}

// linkIdentityTx inserts the OIDC identity link, or updates it in place if the
// (issuer, subject) already exists for this user (e.g. reactivation).
func linkIdentityTx(tx *sql.Tx, userID int64, issuer string, claims oidcClaims) error {
	_, err := tx.Exec(`INSERT INTO user_identities (user_id, issuer, subject, preferred_username, email, email_verified)
		VALUES (?, ?, ?, ?, ?, ?)
		ON CONFLICT(issuer, subject) DO UPDATE SET
			user_id = excluded.user_id,
			preferred_username = excluded.preferred_username,
			email = excluded.email,
			email_verified = excluded.email_verified,
			updated_at = datetime('now')`,
		userID, issuer, claims.Subject, claims.PreferredUsername, nullable(claims.Email), boolInt(claims.EmailVerified))
	return err
}

func actorOrNil(actor *int64) any {
	if actor == nil {
		return nil
	}
	return *actor
}
