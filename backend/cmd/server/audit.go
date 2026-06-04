package main

import (
	"database/sql"
	"encoding/json"
	"log"

	"github.com/gofiber/fiber/v2"
)

// Audit event names. MVP wires auth/access events; the audit_logs table and this
// helper are generic so future work can audit all mutations without schema churn.
const (
	EventAccessRequestCreated  = "access_request_created"
	EventAccessRequestApproved = "access_request_approved"
	EventAccessRequestRejected = "access_request_rejected"
	EventAccessRequestReopened = "access_request_reopened"
	EventIdentityLinked        = "identity_linked"
	EventIdentityEmailChanged  = "identity_email_changed"
	EventUserReactivated       = "user_reactivated"
	EventUsernameChanged       = "username_changed"
	EventUsernameChangeBlocked = "username_change_blocked"
	EventSessionRevoked        = "session_revoked"
	EventLogout                = "logout"
	EventLogin                 = "login"
	EventUserProvisioned       = "user_provisioned"
	EventSetupLinkCreated      = "setup_link_created"
)

// audit writes an audit row using the given executor (db or tx). Failures are
// logged but never block the originating operation.
func (s *Server) audit(exec txExec, event string, actorUserID *int64, targetType, targetID string, detail map[string]any, ip string) {
	var detailJSON any
	if len(detail) > 0 {
		if b, err := json.Marshal(detail); err == nil {
			detailJSON = string(b)
		}
	}
	var actor any
	if actorUserID != nil {
		actor = *actorUserID
	}
	if _, err := exec.Exec(`INSERT INTO audit_logs (event, actor_user_id, target_type, target_id, detail, ip)
		VALUES (?, ?, ?, ?, ?, ?)`, event, actor, nullable(targetType), nullable(targetID), detailJSON, nullable(ip)); err != nil {
		log.Printf("audit: failed to write %s: %v", event, err)
	}
}

func nullable(value string) any {
	if value == "" {
		return nil
	}
	return value
}

func (s *Server) listAuditLogs(c *fiber.Ctx) error {
	page, perPage := paginationInput(c)
	where := "WHERE 1 = 1"
	args := []any{}
	if event := c.Query("event"); event != "" {
		where += " AND event = ?"
		args = append(args, event)
	}

	var total int
	if err := s.db.QueryRow("SELECT COUNT(1) FROM audit_logs "+where, args...).Scan(&total); err != nil {
		return internalError(c, err)
	}

	query := `SELECT a.id, a.event, a.actor_user_id, u.username, a.target_type, a.target_id, a.detail, a.ip, a.created_at
		FROM audit_logs a LEFT JOIN users u ON u.id = a.actor_user_id ` + where +
		" ORDER BY a.id DESC LIMIT ? OFFSET ?"
	args = append(args, perPage, (page-1)*perPage)
	rows, err := s.db.Query(query, args...)
	if err != nil {
		return internalError(c, err)
	}
	defer rows.Close()

	data := []fiber.Map{}
	for rows.Next() {
		var id int64
		var event, createdAt string
		var actorUserID sql.NullInt64
		var actorUsername, targetType, targetID, detail, ip sql.NullString
		if err := rows.Scan(&id, &event, &actorUserID, &actorUsername, &targetType, &targetID, &detail, &ip, &createdAt); err != nil {
			return internalError(c, err)
		}
		data = append(data, fiber.Map{
			"id":             id,
			"event":          event,
			"actor_user_id":  int64Ptr(actorUserID),
			"actor_username": stringPtr(actorUsername),
			"target_type":    stringPtr(targetType),
			"target_id":      stringPtr(targetID),
			"detail":         stringPtr(detail),
			"ip":             stringPtr(ip),
			"created_at":     createdAt,
		})
	}
	return c.JSON(fiber.Map{"data": data, "pagination": paginationMap(page, perPage, total)})
}
