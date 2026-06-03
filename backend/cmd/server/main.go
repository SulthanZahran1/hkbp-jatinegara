package main

import (
	"context"
	"crypto/rand"
	"crypto/sha256"
	"database/sql"
	"encoding/hex"
	"errors"
	"fmt"
	"io/fs"
	"log"
	"math"
	"os"
	"path/filepath"
	"sort"
	"strconv"
	"strings"
	"time"

	"github.com/gofiber/fiber/v2"
	"github.com/gofiber/fiber/v2/middleware/cors"
	"github.com/gofiber/fiber/v2/middleware/logger"
	"github.com/gofiber/fiber/v2/middleware/recover"
	"github.com/golang-jwt/jwt/v5"
	"github.com/joho/godotenv"
	_ "github.com/tursodatabase/libsql-client-go/libsql"
	"golang.org/x/crypto/bcrypt"
	_ "modernc.org/sqlite"
)

const bcryptCost = 12

type Config struct {
	Port          string
	Driver        string
	DSN           string
	JWTSecret     string
	AccessExpiry  time.Duration
	RefreshExpiry time.Duration
	CORSOrigin    string
	UploadDir     string
	StaticDir     string
	MaxUploadSize int64
}

type Server struct {
	db  *sql.DB
	cfg Config
}

type AuthUser struct {
	ID           int64
	Username     string
	Email        string
	PasswordHash string
	NamaDepan    string
	NamaBelakang *string
	RoleID       int64
	RoleName     string
	SektorID     *int64
	Status       string
	LastAccess   *string
}

type LoginRequest struct {
	Username string `json:"username"`
	Password string `json:"password"`
}

type RefreshRequest struct {
	RefreshToken string `json:"refresh_token"`
}

type SectorRequest struct {
	Name string `json:"name"`
}

type UserRequest struct {
	Username     string `json:"username"`
	Email        string `json:"email"`
	Password     string `json:"password"`
	NamaDepan    string `json:"nama_depan"`
	NamaBelakang string `json:"nama_belakang"`
	RoleID       int64  `json:"role_id"`
	SektorID     *int64 `json:"sektor_id"`
	Status       string `json:"status"`
	NewPassword  string `json:"new_password"`
}

type FamilyRequest struct {
	SectorID int64           `json:"sector_id"`
	Alamat   string          `json:"alamat"`
	Members  []MemberRequest `json:"members"`
}

type MemberRequest struct {
	ID                int64  `json:"id"`
	FamilyID          int64  `json:"family_id"`
	SectorID          int64  `json:"sector_id"`
	Nama              string `json:"nama"`
	Marga             string `json:"marga"`
	Gender            string `json:"gender"`
	TempatLahir       string `json:"tempat_lahir"`
	TanggalLahir      string `json:"tanggal_lahir"`
	GolDarah          string `json:"gol_darah"`
	HubunganKeluarga  string `json:"hubungan_keluarga"`
	Pendidikan        string `json:"pendidikan"`
	Pekerjaan         string `json:"pekerjaan"`
	Talenta           string `json:"talenta"`
	NoHP              string `json:"no_hp"`
	Alamat            string `json:"alamat"`
	Provinsi          string `json:"provinsi"`
	Kota              string `json:"kota"`
	Kecamatan         string `json:"kecamatan"`
	Kelurahan         string `json:"kelurahan"`
	KodePos           string `json:"kode_pos"`
	FotoURL           string `json:"foto_url"`
	TglBaptis         string `json:"tgl_baptis"`
	GerejaBaptis      string `json:"gereja_baptis"`
	PendetaBaptis     string `json:"pendeta_baptis"`
	TglSidi           string `json:"tgl_sidi"`
	GerejaSidi        string `json:"gereja_sidi"`
	PendetaSidi       string `json:"pendeta_sidi"`
	NatsSidi          string `json:"nats_sidi"`
	TglPerkawinan     string `json:"tgl_perkawinan"`
	GerejaPerkawinan  string `json:"gereja_perkawinan"`
	PendetaPerkawinan string `json:"pendeta_perkawinan"`
	NatsPerkawinan    string `json:"nats_perkawinan"`
	IsHeadOfFamily    bool   `json:"is_head_of_family"`
}

type OfferingRequest struct {
	FamilyID int64  `json:"family_id"`
	Amount   int64  `json:"amount"`
	Month    int    `json:"month"`
	Year     int    `json:"year"`
	Notes    string `json:"notes"`
}

type SintuaRequest struct {
	MemberID int64 `json:"member_id"`
}

type AttendanceRequest struct {
	MemberID int64  `json:"member_id"`
	Date     string `json:"date"`
	Status   string `json:"status"`
	Seksi    string `json:"seksi"`
}

type scanner interface {
	Scan(dest ...any) error
}

type txExec interface {
	Exec(query string, args ...any) (sql.Result, error)
	QueryRow(query string, args ...any) *sql.Row
}

func main() {
	cfg := loadConfig()

	db, err := openDB(cfg)
	if err != nil {
		log.Fatalf("database: %v", err)
	}
	defer db.Close()

	if err := runMigrations(db); err != nil {
		log.Fatalf("migrations: %v", err)
	}
	if err := seedAdmin(db); err != nil {
		log.Fatalf("seed admin: %v", err)
	}

	if err := os.MkdirAll(cfg.UploadDir, 0755); err != nil {
		log.Fatalf("upload dir: %v", err)
	}

	server := &Server{db: db, cfg: cfg}
	app := fiber.New(fiber.Config{
		BodyLimit: int(cfg.MaxUploadSize + 1024*1024),
	})
	app.Use(recover.New())
	app.Use(logger.New())
	app.Use(cors.New(cors.Config{
		AllowOrigins:     cfg.CORSOrigin,
		AllowHeaders:     "Origin, Content-Type, Accept, Authorization",
		AllowMethods:     "GET,POST,PUT,DELETE,OPTIONS",
		AllowCredentials: true,
	}))

	server.registerRoutes(app)

	log.Printf("HKBP Jatinegara API listening on :%s using %s", cfg.Port, cfg.Driver)
	if err := app.Listen(":" + cfg.Port); err != nil {
		log.Fatal(err)
	}
}

func loadConfig() Config {
	_ = godotenv.Load()

	port := env("PORT", "8080")
	tursoURL := env("TURSO_URL", env("TURSO_DATABASE_URL", "http://127.0.0.1:8081"))
	tursoToken := env("TURSO_AUTH_TOKEN", "")

	driver := "libsql"
	dsn := tursoURL
	if sqlitePath := env("SQLITE_PATH", ""); sqlitePath != "" {
		driver = "sqlite"
		dsn = sqlitePath
	} else if strings.HasPrefix(tursoURL, "file:") || strings.HasSuffix(tursoURL, ".db") {
		driver = "sqlite"
		dsn = tursoURL
	}
	if driver == "sqlite" {
		sep := "?"
		if strings.Contains(dsn, "?") {
			sep = "&"
		}
		dsn = dsn + sep + "_pragma=foreign_keys(1)"
	} else if tursoToken != "" && !strings.Contains(tursoToken, "<") {
		sep := "?"
		if strings.Contains(tursoURL, "?") {
			sep = "&"
		}
		dsn = tursoURL + sep + "authToken=" + tursoToken
	}

	return Config{
		Port:          port,
		Driver:        driver,
		DSN:           dsn,
		JWTSecret:     env("JWT_SECRET", "dev-secret-change-before-production"),
		AccessExpiry:  secondsEnv("JWT_ACCESS_EXPIRY", 900),
		RefreshExpiry: secondsEnv("JWT_REFRESH_EXPIRY", 604800),
		CORSOrigin:    env("CORS_ORIGIN", "http://localhost:5173"),
		UploadDir:     env("UPLOAD_DIR", "./uploads"),
		StaticDir:     env("STATIC_DIR", ""),
		MaxUploadSize: int64Env("MAX_UPLOAD_SIZE", 5242880),
	}
}

func openDB(cfg Config) (*sql.DB, error) {
	db, err := sql.Open(cfg.Driver, cfg.DSN)
	if err != nil {
		return nil, err
	}
	db.SetMaxOpenConns(1)
	db.SetMaxIdleConns(1)

	ctx, cancel := context.WithTimeout(context.Background(), 5*time.Second)
	defer cancel()
	if err := db.PingContext(ctx); err != nil {
		_ = db.Close()
		return nil, err
	}
	return db, nil
}

func runMigrations(db *sql.DB) error {
	if _, err := db.Exec(`CREATE TABLE IF NOT EXISTS _migrations (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		name TEXT NOT NULL UNIQUE,
		applied_at TEXT NOT NULL DEFAULT (datetime('now'))
	)`); err != nil {
		return err
	}

	entries, err := os.ReadDir("migrations")
	if err != nil {
		if errors.Is(err, fs.ErrNotExist) {
			return nil
		}
		return err
	}
	sort.Slice(entries, func(i, j int) bool { return entries[i].Name() < entries[j].Name() })

	for _, entry := range entries {
		if entry.IsDir() || !strings.HasSuffix(entry.Name(), ".sql") {
			continue
		}

		var count int
		if err := db.QueryRow("SELECT COUNT(1) FROM _migrations WHERE name = ?", entry.Name()).Scan(&count); err != nil {
			return err
		}
		if count > 0 {
			continue
		}

		body, err := os.ReadFile(filepath.Join("migrations", entry.Name()))
		if err != nil {
			return err
		}

		tx, err := db.Begin()
		if err != nil {
			return err
		}
		if err := execSQLStatements(tx, string(body)); err != nil {
			_ = tx.Rollback()
			return fmt.Errorf("%s: %w", entry.Name(), err)
		}
		if _, err := tx.Exec("INSERT INTO _migrations (name) VALUES (?)", entry.Name()); err != nil {
			_ = tx.Rollback()
			return err
		}
		if err := tx.Commit(); err != nil {
			return err
		}
	}
	return nil
}

func execSQLStatements(tx *sql.Tx, sqlBody string) error {
	if _, err := tx.Exec(sqlBody); err == nil {
		return nil
	}

	var b strings.Builder
	inTrigger := false
	for _, line := range strings.Split(sqlBody, "\n") {
		trimmed := strings.TrimSpace(line)
		upper := strings.ToUpper(trimmed)
		if strings.HasPrefix(upper, "CREATE TRIGGER") {
			inTrigger = true
		}
		b.WriteString(line)
		b.WriteString("\n")

		if trimmed == "" || strings.HasPrefix(trimmed, "--") {
			continue
		}
		endsStatement := strings.HasSuffix(trimmed, ";")
		if inTrigger {
			endsStatement = strings.EqualFold(trimmed, "END;")
		}
		if endsStatement {
			stmt := strings.TrimSpace(b.String())
			if _, err := tx.Exec(stmt); err != nil {
				return err
			}
			b.Reset()
			inTrigger = false
		}
	}
	if stmt := strings.TrimSpace(b.String()); stmt != "" {
		if _, err := tx.Exec(stmt); err != nil {
			return err
		}
	}
	return nil
}

func seedAdmin(db *sql.DB) error {
	var count int
	if err := db.QueryRow("SELECT COUNT(1) FROM users").Scan(&count); err != nil {
		return err
	}
	if count > 0 {
		return nil
	}

	var roleID int64
	if err := db.QueryRow("SELECT id FROM roles WHERE name = 'admin'").Scan(&roleID); err != nil {
		return err
	}

	hash, err := bcrypt.GenerateFromPassword([]byte("admin123"), bcryptCost)
	if err != nil {
		return err
	}

	_, err = db.Exec(`INSERT INTO users
		(username, email, password_hash, nama_depan, nama_belakang, role_id, sektor_id, status)
		VALUES (?, ?, ?, ?, ?, ?, NULL, 'active')`,
		"admin", "admin@hkbpjatinegara.local", string(hash), "Admin", "HKBP", roleID)
	return err
}

const swaggerUI = `<!DOCTYPE html>
<html>
<head>
  <title>HKBP Jatinegara API</title>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist/swagger-ui.css">
</head>
<body>
<div id="swagger-ui"></div>
<script src="https://unpkg.com/swagger-ui-dist/swagger-ui-bundle.js"></script>
<script>
SwaggerUIBundle({
  url: "/docs/openapi.yaml",
  dom_id: '#swagger-ui',
  presets: [SwaggerUIBundle.presets.apis, SwaggerUIBundle.SwaggerUIStandalonePreset],
  layout: "BaseLayout",
  deepLinking: true,
  persistAuthorization: true,
})
</script>
</body>
</html>`

func (s *Server) registerRoutes(app *fiber.App) {
	app.Get("/health", func(c *fiber.Ctx) error {
		return c.JSON(fiber.Map{"status": "ok"})
	})

	app.Get("/docs", func(c *fiber.Ctx) error {
		c.Set("Content-Type", "text/html; charset=utf-8")
		return c.SendString(swaggerUI)
	})
	app.Get("/docs/openapi.yaml", func(c *fiber.Ctx) error {
		c.Set("Content-Type", "application/x-yaml")
		return c.SendFile("docs/openapi.yaml")
	})

	api := app.Group("/api/v1")
	api.Post("/auth/login", s.login)
	api.Post("/auth/refresh", s.refresh)

	protected := api.Group("", s.authMiddleware)
	protected.Get("/auth/me", s.me)

	protected.Get("/roles", s.listRoles)

	protected.Get("/sectors", s.listSectors)
	protected.Post("/sectors", requireRole("admin"), s.createSector)
	protected.Put("/sectors/:id", requireRole("admin"), s.updateSector)
	protected.Delete("/sectors/:id", requireRole("admin"), s.deleteSector)

	protected.Get("/users", requireRole("admin"), s.listUsers)
	protected.Post("/users", requireRole("admin"), s.createUser)
	protected.Put("/users/:id", requireRole("admin"), s.updateUser)
	protected.Put("/users/:id/password", s.changePassword)
	protected.Delete("/users/:id", requireRole("admin"), s.deleteUser)

	protected.Get("/families", s.listFamilies)
	protected.Get("/families/:id", s.getFamily)
	protected.Post("/families", s.createFamily)
	protected.Put("/families/:id", s.updateFamily)
	protected.Delete("/families/:id", requireRole("admin"), s.deleteFamily)

	protected.Get("/members", s.listMembers)
	protected.Get("/members/:id", s.getMember)
	protected.Put("/members/:id", s.updateMember)
	protected.Post("/members/:id/foto", s.uploadMemberPhoto)
	protected.Delete("/members/:id", s.deleteMember)

	protected.Get("/offerings", s.listOfferings)
	protected.Post("/offerings", s.createOffering)
	protected.Get("/offerings/report", s.offeringReport)
	protected.Delete("/offerings/:id", requireRole("admin"), s.deleteOffering)

	protected.Get("/sintua", s.listSintua)
	protected.Post("/sintua", s.createSintua)
	protected.Delete("/sintua/:id", s.deleteSintua)

	protected.Get("/attendance", s.listAttendance)
	protected.Post("/attendance", s.recordAttendance)

	registerStaticRoutes(app, s.cfg.StaticDir)
}

func registerStaticRoutes(app *fiber.App, staticDir string) {
	if strings.TrimSpace(staticDir) == "" {
		return
	}
	indexPath := filepath.Join(staticDir, "index.html")
	if _, err := os.Stat(indexPath); err != nil {
		log.Printf("static frontend disabled: %s not readable: %v", indexPath, err)
		return
	}

	app.Static("/", staticDir)
	app.Get("*", func(c *fiber.Ctx) error {
		if strings.HasPrefix(c.Path(), "/api/") {
			return c.Status(fiber.StatusNotFound).JSON(fiber.Map{"error": "not found"})
		}
		return c.SendFile(indexPath)
	})
}

func (s *Server) login(c *fiber.Ctx) error {
	var req LoginRequest
	if err := c.BodyParser(&req); err != nil {
		return badRequest(c, "invalid request body")
	}
	req.Username = strings.TrimSpace(req.Username)
	if req.Username == "" || req.Password == "" {
		return badRequest(c, "username and password are required")
	}

	user, err := s.findAuthUserByUsername(req.Username)
	if err != nil {
		if errors.Is(err, sql.ErrNoRows) {
			return c.Status(fiber.StatusUnauthorized).JSON(fiber.Map{"error": "invalid credentials"})
		}
		return internalError(c, err)
	}
	if user.Status != "active" {
		return c.Status(fiber.StatusUnauthorized).JSON(fiber.Map{"error": "invalid credentials"})
	}
	if err := bcrypt.CompareHashAndPassword([]byte(user.PasswordHash), []byte(req.Password)); err != nil {
		return c.Status(fiber.StatusUnauthorized).JSON(fiber.Map{"error": "invalid credentials"})
	}

	accessToken, err := s.generateAccessToken(user)
	if err != nil {
		return internalError(c, err)
	}
	refreshToken, err := randomToken()
	if err != nil {
		return internalError(c, err)
	}
	refreshHash := hashToken(refreshToken)
	expiresAt := time.Now().UTC().Add(s.cfg.RefreshExpiry).Format(time.RFC3339)

	tx, err := s.db.Begin()
	if err != nil {
		return internalError(c, err)
	}
	if _, err := tx.Exec("DELETE FROM refresh_tokens WHERE user_id = ? OR expires_at <= datetime('now')", user.ID); err != nil {
		_ = tx.Rollback()
		return internalError(c, err)
	}
	if _, err := tx.Exec("INSERT INTO refresh_tokens (user_id, token_hash, expires_at) VALUES (?, ?, ?)", user.ID, refreshHash, expiresAt); err != nil {
		_ = tx.Rollback()
		return internalError(c, err)
	}
	if _, err := tx.Exec("UPDATE users SET last_access = datetime('now'), updated_at = datetime('now') WHERE id = ?", user.ID); err != nil {
		_ = tx.Rollback()
		return internalError(c, err)
	}
	if err := tx.Commit(); err != nil {
		return internalError(c, err)
	}

	return c.JSON(fiber.Map{
		"access_token":  accessToken,
		"refresh_token": refreshToken,
		"expires_in":    int(s.cfg.AccessExpiry.Seconds()),
		"user":          user.publicMap(),
	})
}

func (s *Server) refresh(c *fiber.Ctx) error {
	var req RefreshRequest
	if err := c.BodyParser(&req); err != nil {
		return badRequest(c, "invalid request body")
	}
	if strings.TrimSpace(req.RefreshToken) == "" {
		return badRequest(c, "refresh_token is required")
	}

	userID, err := s.userIDByRefreshToken(req.RefreshToken)
	if err != nil {
		if errors.Is(err, sql.ErrNoRows) {
			return c.Status(fiber.StatusUnauthorized).JSON(fiber.Map{"error": "invalid refresh token"})
		}
		return internalError(c, err)
	}
	user, err := s.findAuthUserByID(userID)
	if err != nil {
		if errors.Is(err, sql.ErrNoRows) {
			return c.Status(fiber.StatusUnauthorized).JSON(fiber.Map{"error": "invalid refresh token"})
		}
		return internalError(c, err)
	}
	if user.Status != "active" {
		return c.Status(fiber.StatusUnauthorized).JSON(fiber.Map{"error": "invalid refresh token"})
	}

	accessToken, err := s.generateAccessToken(user)
	if err != nil {
		return internalError(c, err)
	}
	return c.JSON(fiber.Map{
		"access_token": accessToken,
		"expires_in":   int(s.cfg.AccessExpiry.Seconds()),
	})
}

func (s *Server) me(c *fiber.Ctx) error {
	userID := localInt64(c, "user_id")
	user, err := s.findAuthUserByID(userID)
	if err != nil {
		if errors.Is(err, sql.ErrNoRows) {
			return notFound(c, "user not found")
		}
		return internalError(c, err)
	}
	return c.JSON(user.publicMap())
}

func (s *Server) findAuthUserByUsername(username string) (AuthUser, error) {
	return s.scanAuthUser(s.db.QueryRow(`SELECT u.id, u.username, u.email, u.password_hash, u.nama_depan,
		u.nama_belakang, u.role_id, r.name, u.sektor_id, u.status, u.last_access
		FROM users u JOIN roles r ON r.id = u.role_id WHERE u.username = ?`, username))
}

func (s *Server) findAuthUserByID(id int64) (AuthUser, error) {
	return s.scanAuthUser(s.db.QueryRow(`SELECT u.id, u.username, u.email, u.password_hash, u.nama_depan,
		u.nama_belakang, u.role_id, r.name, u.sektor_id, u.status, u.last_access
		FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ?`, id))
}

func (s *Server) scanAuthUser(row scanner) (AuthUser, error) {
	var user AuthUser
	var namaBelakang, lastAccess sql.NullString
	var sektorID sql.NullInt64
	err := row.Scan(&user.ID, &user.Username, &user.Email, &user.PasswordHash, &user.NamaDepan,
		&namaBelakang, &user.RoleID, &user.RoleName, &sektorID, &user.Status, &lastAccess)
	if err != nil {
		return user, err
	}
	user.NamaBelakang = stringPtr(namaBelakang)
	user.SektorID = int64Ptr(sektorID)
	user.LastAccess = stringPtr(lastAccess)
	return user, nil
}

func (u AuthUser) publicMap() fiber.Map {
	return fiber.Map{
		"id":            u.ID,
		"nama_depan":    u.NamaDepan,
		"nama_belakang": u.NamaBelakang,
		"username":      u.Username,
		"email":         u.Email,
		"role_id":       u.RoleID,
		"role_name":     u.RoleName,
		"sektor_id":     u.SektorID,
		"status":        u.Status,
		"last_access":   u.LastAccess,
	}
}

func (s *Server) userIDByRefreshToken(token string) (int64, error) {
	var userID int64
	err := s.db.QueryRow(`SELECT user_id FROM refresh_tokens
		WHERE token_hash = ? AND expires_at > datetime('now')`, hashToken(token)).Scan(&userID)
	return userID, err
}

func (s *Server) generateAccessToken(user AuthUser) (string, error) {
	claims := jwt.MapClaims{
		"user_id":   user.ID,
		"role_id":   user.RoleID,
		"role":      user.RoleName,
		"sektor_id": user.SektorID,
		"exp":       time.Now().UTC().Add(s.cfg.AccessExpiry).Unix(),
		"iat":       time.Now().UTC().Unix(),
	}
	token := jwt.NewWithClaims(jwt.SigningMethodHS256, claims)
	return token.SignedString([]byte(s.cfg.JWTSecret))
}

func (s *Server) authMiddleware(c *fiber.Ctx) error {
	header := c.Get("Authorization")
	if !strings.HasPrefix(header, "Bearer ") {
		return c.Status(fiber.StatusUnauthorized).JSON(fiber.Map{"error": "missing bearer token"})
	}
	raw := strings.TrimSpace(strings.TrimPrefix(header, "Bearer "))
	token, err := jwt.Parse(raw, func(token *jwt.Token) (any, error) {
		if _, ok := token.Method.(*jwt.SigningMethodHMAC); !ok {
			return nil, fmt.Errorf("unexpected signing method")
		}
		return []byte(s.cfg.JWTSecret), nil
	})
	if err != nil || !token.Valid {
		return c.Status(fiber.StatusUnauthorized).JSON(fiber.Map{"error": "invalid token"})
	}

	claims, ok := token.Claims.(jwt.MapClaims)
	if !ok {
		return c.Status(fiber.StatusUnauthorized).JSON(fiber.Map{"error": "invalid token"})
	}
	userID, ok := claimInt64(claims["user_id"])
	if !ok {
		return c.Status(fiber.StatusUnauthorized).JSON(fiber.Map{"error": "invalid token"})
	}
	roleID, _ := claimInt64(claims["role_id"])
	roleName, _ := claims["role"].(string)
	c.Locals("user_id", userID)
	c.Locals("role_id", roleID)
	c.Locals("role", roleName)
	if sectorID, ok := claimInt64(claims["sektor_id"]); ok {
		c.Locals("sektor_id", sectorID)
	}
	return c.Next()
}

func requireRole(roles ...string) fiber.Handler {
	allowed := map[string]bool{}
	for _, role := range roles {
		allowed[role] = true
	}
	return func(c *fiber.Ctx) error {
		role, _ := c.Locals("role").(string)
		if !allowed[role] {
			return c.Status(fiber.StatusForbidden).JSON(fiber.Map{"error": "forbidden"})
		}
		return c.Next()
	}
}

func (s *Server) listRoles(c *fiber.Ctx) error {
	rows, err := s.db.Query("SELECT id, name, created_at FROM roles ORDER BY id")
	if err != nil {
		return internalError(c, err)
	}
	defer rows.Close()

	data := []fiber.Map{}
	for rows.Next() {
		var id int64
		var name, createdAt string
		if err := rows.Scan(&id, &name, &createdAt); err != nil {
			return internalError(c, err)
		}
		data = append(data, fiber.Map{"id": id, "name": name, "created_at": createdAt})
	}
	return c.JSON(fiber.Map{"data": data})
}

func (s *Server) listSectors(c *fiber.Ctx) error {
	rows, err := s.db.Query("SELECT id, name, created_at, updated_at FROM sectors ORDER BY name")
	if err != nil {
		return internalError(c, err)
	}
	defer rows.Close()

	data := []fiber.Map{}
	for rows.Next() {
		var id int64
		var name, createdAt, updatedAt string
		if err := rows.Scan(&id, &name, &createdAt, &updatedAt); err != nil {
			return internalError(c, err)
		}
		data = append(data, fiber.Map{"id": id, "name": name, "created_at": createdAt, "updated_at": updatedAt})
	}
	return c.JSON(fiber.Map{"data": data})
}

func (s *Server) createSector(c *fiber.Ctx) error {
	var req SectorRequest
	if err := c.BodyParser(&req); err != nil {
		return badRequest(c, "invalid request body")
	}
	req.Name = strings.TrimSpace(req.Name)
	if req.Name == "" {
		return badRequest(c, "name is required")
	}
	res, err := s.db.Exec("INSERT INTO sectors (name) VALUES (?)", req.Name)
	if err != nil {
		return badRequest(c, err.Error())
	}
	id, _ := res.LastInsertId()
	return c.Status(fiber.StatusCreated).JSON(fiber.Map{"id": id, "name": req.Name})
}

func (s *Server) updateSector(c *fiber.Ctx) error {
	id, err := paramID(c)
	if err != nil {
		return badRequest(c, "invalid sector id")
	}
	var req SectorRequest
	if err := c.BodyParser(&req); err != nil {
		return badRequest(c, "invalid request body")
	}
	req.Name = strings.TrimSpace(req.Name)
	if req.Name == "" {
		return badRequest(c, "name is required")
	}
	res, err := s.db.Exec("UPDATE sectors SET name = ?, updated_at = datetime('now') WHERE id = ?", req.Name, id)
	if err != nil {
		return badRequest(c, err.Error())
	}
	if affected, _ := res.RowsAffected(); affected == 0 {
		return notFound(c, "sector not found")
	}
	return c.JSON(fiber.Map{"id": id, "name": req.Name})
}

func (s *Server) deleteSector(c *fiber.Ctx) error {
	id, err := paramID(c)
	if err != nil {
		return badRequest(c, "invalid sector id")
	}
	res, err := s.db.Exec("DELETE FROM sectors WHERE id = ?", id)
	if err != nil {
		return badRequest(c, err.Error())
	}
	if affected, _ := res.RowsAffected(); affected == 0 {
		return notFound(c, "sector not found")
	}
	return c.SendStatus(fiber.StatusNoContent)
}

func (s *Server) listUsers(c *fiber.Ctx) error {
	query := `SELECT u.id, u.username, u.email, u.nama_depan, u.nama_belakang,
		u.role_id, r.name, u.sektor_id, s.name, u.status, u.last_access, u.created_at
		FROM users u
		JOIN roles r ON r.id = u.role_id
		LEFT JOIN sectors s ON s.id = u.sektor_id
		WHERE 1 = 1`
	args := []any{}
	if sector := c.Query("sektor_id"); sector != "" {
		query += " AND u.sektor_id = ?"
		args = append(args, sector)
	}
	query += " ORDER BY u.username"

	rows, err := s.db.Query(query, args...)
	if err != nil {
		return internalError(c, err)
	}
	defer rows.Close()

	data := []fiber.Map{}
	for rows.Next() {
		user, err := scanUserRow(rows)
		if err != nil {
			return internalError(c, err)
		}
		data = append(data, user)
	}
	return c.JSON(fiber.Map{"data": data})
}

func (s *Server) createUser(c *fiber.Ctx) error {
	var req UserRequest
	if err := c.BodyParser(&req); err != nil {
		return badRequest(c, "invalid request body")
	}
	if req.Username == "" || req.Email == "" || req.Password == "" || req.NamaDepan == "" || req.RoleID == 0 {
		return badRequest(c, "username, email, password, nama_depan, and role_id are required")
	}
	status := req.Status
	if status == "" {
		status = "active"
	}
	hash, err := bcrypt.GenerateFromPassword([]byte(req.Password), bcryptCost)
	if err != nil {
		return internalError(c, err)
	}
	res, err := s.db.Exec(`INSERT INTO users
		(username, email, password_hash, nama_depan, nama_belakang, role_id, sektor_id, status)
		VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
		strings.TrimSpace(req.Username), strings.TrimSpace(req.Email), string(hash), strings.TrimSpace(req.NamaDepan),
		nilIfEmpty(req.NamaBelakang), req.RoleID, req.SektorID, status)
	if err != nil {
		return badRequest(c, err.Error())
	}
	id, _ := res.LastInsertId()
	return c.Status(fiber.StatusCreated).JSON(fiber.Map{"id": id})
}

func (s *Server) updateUser(c *fiber.Ctx) error {
	id, err := paramID(c)
	if err != nil {
		return badRequest(c, "invalid user id")
	}
	var req UserRequest
	if err := c.BodyParser(&req); err != nil {
		return badRequest(c, "invalid request body")
	}
	if req.Username == "" || req.Email == "" || req.NamaDepan == "" || req.RoleID == 0 {
		return badRequest(c, "username, email, nama_depan, and role_id are required")
	}
	status := req.Status
	if status == "" {
		status = "active"
	}
	res, err := s.db.Exec(`UPDATE users
		SET username = ?, email = ?, nama_depan = ?, nama_belakang = ?, role_id = ?, sektor_id = ?, status = ?, updated_at = datetime('now')
		WHERE id = ?`,
		strings.TrimSpace(req.Username), strings.TrimSpace(req.Email), strings.TrimSpace(req.NamaDepan),
		nilIfEmpty(req.NamaBelakang), req.RoleID, req.SektorID, status, id)
	if err != nil {
		return badRequest(c, err.Error())
	}
	if affected, _ := res.RowsAffected(); affected == 0 {
		return notFound(c, "user not found")
	}
	return c.JSON(fiber.Map{"id": id})
}

func (s *Server) changePassword(c *fiber.Ctx) error {
	id, err := paramID(c)
	if err != nil {
		return badRequest(c, "invalid user id")
	}
	var req UserRequest
	if err := c.BodyParser(&req); err != nil {
		return badRequest(c, "invalid request body")
	}
	if req.NewPassword == "" {
		return badRequest(c, "new_password is required")
	}
	currentUserID := localInt64(c, "user_id")
	isAdmin := c.Locals("role") == "admin"
	if !isAdmin && currentUserID != id {
		return c.Status(fiber.StatusForbidden).JSON(fiber.Map{"error": "forbidden"})
	}
	if !isAdmin {
		user, err := s.findAuthUserByID(id)
		if err != nil {
			return internalError(c, err)
		}
		if err := bcrypt.CompareHashAndPassword([]byte(user.PasswordHash), []byte(req.Password)); err != nil {
			return c.Status(fiber.StatusUnauthorized).JSON(fiber.Map{"error": "invalid password"})
		}
	}

	hash, err := bcrypt.GenerateFromPassword([]byte(req.NewPassword), bcryptCost)
	if err != nil {
		return internalError(c, err)
	}
	res, err := s.db.Exec("UPDATE users SET password_hash = ?, updated_at = datetime('now') WHERE id = ?", string(hash), id)
	if err != nil {
		return internalError(c, err)
	}
	if affected, _ := res.RowsAffected(); affected == 0 {
		return notFound(c, "user not found")
	}
	_, _ = s.db.Exec("DELETE FROM refresh_tokens WHERE user_id = ?", id)
	return c.JSON(fiber.Map{"status": "ok"})
}

func (s *Server) deleteUser(c *fiber.Ctx) error {
	id, err := paramID(c)
	if err != nil {
		return badRequest(c, "invalid user id")
	}
	res, err := s.db.Exec("UPDATE users SET status = 'inactive', updated_at = datetime('now') WHERE id = ?", id)
	if err != nil {
		return internalError(c, err)
	}
	if affected, _ := res.RowsAffected(); affected == 0 {
		return notFound(c, "user not found")
	}
	return c.SendStatus(fiber.StatusNoContent)
}

func (s *Server) listFamilies(c *fiber.Ctx) error {
	page, perPage := paginationInput(c)
	where := "WHERE 1 = 1"
	args := []any{}
	if sector := firstQuery(c, "sektor_id", "sector_id"); sector != "" {
		where += " AND f.sector_id = ?"
		args = append(args, sector)
	}
	if search := strings.TrimSpace(c.Query("search")); search != "" {
		where += " AND (hm.nama LIKE ? OR hm.marga LIKE ? OR f.alamat LIKE ?)"
		like := "%" + search + "%"
		args = append(args, like, like, like)
	}

	var total int
	countSQL := `SELECT COUNT(1) FROM families f
		LEFT JOIN members hm ON hm.id = f.head_member_id ` + where
	if err := s.db.QueryRow(countSQL, args...).Scan(&total); err != nil {
		return internalError(c, err)
	}

	query := `SELECT f.id, f.sector_id, s.name, f.head_member_id, hm.nama, f.alamat, COUNT(m.id), f.created_at
		FROM families f
		JOIN sectors s ON s.id = f.sector_id
		LEFT JOIN members hm ON hm.id = f.head_member_id
		LEFT JOIN members m ON m.family_id = f.id ` + where + `
		GROUP BY f.id, f.sector_id, s.name, f.head_member_id, hm.nama, f.alamat, f.created_at
		ORDER BY COALESCE(hm.nama, f.alamat, printf('%012d', f.id))
		LIMIT ? OFFSET ?`
	queryArgs := append(append([]any{}, args...), perPage, (page-1)*perPage)
	rows, err := s.db.Query(query, queryArgs...)
	if err != nil {
		return internalError(c, err)
	}
	defer rows.Close()

	data := []fiber.Map{}
	for rows.Next() {
		var id, sectorID, memberCount int64
		var sectorName, createdAt string
		var headMemberID sql.NullInt64
		var headMemberName, alamat sql.NullString
		if err := rows.Scan(&id, &sectorID, &sectorName, &headMemberID, &headMemberName, &alamat, &memberCount, &createdAt); err != nil {
			return internalError(c, err)
		}
		data = append(data, fiber.Map{
			"id":               id,
			"sector_id":        sectorID,
			"sector_name":      sectorName,
			"head_member_id":   int64Ptr(headMemberID),
			"head_member_name": stringPtr(headMemberName),
			"alamat":           stringPtr(alamat),
			"member_count":     memberCount,
			"created_at":       createdAt,
		})
	}
	return c.JSON(fiber.Map{"data": data, "pagination": paginationMap(page, perPage, total)})
}

func (s *Server) getFamily(c *fiber.Ctx) error {
	id, err := paramID(c)
	if err != nil {
		return badRequest(c, "invalid family id")
	}
	var sectorID int64
	var sectorName, createdAt string
	var headMemberID sql.NullInt64
	var alamat sql.NullString
	err = s.db.QueryRow(`SELECT f.sector_id, s.name, f.head_member_id, f.alamat, f.created_at
		FROM families f JOIN sectors s ON s.id = f.sector_id WHERE f.id = ?`, id).
		Scan(&sectorID, &sectorName, &headMemberID, &alamat, &createdAt)
	if err != nil {
		if errors.Is(err, sql.ErrNoRows) {
			return notFound(c, "family not found")
		}
		return internalError(c, err)
	}

	members, err := s.membersForFamily(id)
	if err != nil {
		return internalError(c, err)
	}
	return c.JSON(fiber.Map{
		"id":             id,
		"sector_id":      sectorID,
		"sector_name":    sectorName,
		"head_member_id": int64Ptr(headMemberID),
		"alamat":         stringPtr(alamat),
		"members":        members,
		"created_at":     createdAt,
	})
}

func (s *Server) createFamily(c *fiber.Ctx) error {
	var req FamilyRequest
	if err := c.BodyParser(&req); err != nil {
		return badRequest(c, "invalid request body")
	}
	if req.SectorID == 0 || len(req.Members) == 0 {
		return badRequest(c, "sector_id and members are required")
	}
	headCount := 0
	for _, member := range req.Members {
		if member.HubunganKeluarga == "Kepala Keluarga" {
			headCount++
		}
	}
	if headCount != 1 {
		return badRequest(c, "family must contain exactly one Kepala Keluarga")
	}

	tx, err := s.db.Begin()
	if err != nil {
		return internalError(c, err)
	}
	res, err := tx.Exec("INSERT INTO families (sector_id, alamat) VALUES (?, ?)", req.SectorID, nilIfEmpty(req.Alamat))
	if err != nil {
		_ = tx.Rollback()
		return badRequest(c, err.Error())
	}
	familyID, _ := res.LastInsertId()

	var headID int64
	for _, member := range req.Members {
		if member.Alamat == "" {
			member.Alamat = req.Alamat
		}
		memberID, err := insertMember(tx, familyID, req.SectorID, member)
		if err != nil {
			_ = tx.Rollback()
			return badRequest(c, err.Error())
		}
		if member.HubunganKeluarga == "Kepala Keluarga" {
			headID = memberID
		}
	}
	if _, err := tx.Exec("UPDATE families SET head_member_id = ? WHERE id = ?", headID, familyID); err != nil {
		_ = tx.Rollback()
		return internalError(c, err)
	}
	if err := tx.Commit(); err != nil {
		return internalError(c, err)
	}
	return c.Status(fiber.StatusCreated).JSON(fiber.Map{"id": familyID, "head_member_id": headID})
}

func (s *Server) updateFamily(c *fiber.Ctx) error {
	id, err := paramID(c)
	if err != nil {
		return badRequest(c, "invalid family id")
	}
	var req FamilyRequest
	if err := c.BodyParser(&req); err != nil {
		return badRequest(c, "invalid request body")
	}
	if req.SectorID == 0 {
		return badRequest(c, "sector_id is required")
	}
	res, err := s.db.Exec("UPDATE families SET sector_id = ?, alamat = ?, updated_at = datetime('now') WHERE id = ?",
		req.SectorID, nilIfEmpty(req.Alamat), id)
	if err != nil {
		return badRequest(c, err.Error())
	}
	if affected, _ := res.RowsAffected(); affected == 0 {
		return notFound(c, "family not found")
	}
	_, _ = s.db.Exec("UPDATE members SET sector_id = ?, updated_at = datetime('now') WHERE family_id = ?", req.SectorID, id)
	return c.JSON(fiber.Map{"id": id})
}

func (s *Server) deleteFamily(c *fiber.Ctx) error {
	id, err := paramID(c)
	if err != nil {
		return badRequest(c, "invalid family id")
	}
	tx, err := s.db.Begin()
	if err != nil {
		return internalError(c, err)
	}
	_, _ = tx.Exec("DELETE FROM attendance WHERE member_id IN (SELECT id FROM members WHERE family_id = ?)", id)
	_, _ = tx.Exec("DELETE FROM sintua WHERE member_id IN (SELECT id FROM members WHERE family_id = ?)", id)
	_, _ = tx.Exec("DELETE FROM offerings WHERE family_id = ?", id)
	_, _ = tx.Exec("DELETE FROM members WHERE family_id = ?", id)
	res, err := tx.Exec("DELETE FROM families WHERE id = ?", id)
	if err != nil {
		_ = tx.Rollback()
		return internalError(c, err)
	}
	if affected, _ := res.RowsAffected(); affected == 0 {
		_ = tx.Rollback()
		return notFound(c, "family not found")
	}
	if err := tx.Commit(); err != nil {
		return internalError(c, err)
	}
	return c.SendStatus(fiber.StatusNoContent)
}

func (s *Server) listMembers(c *fiber.Ctx) error {
	page, perPage := paginationInput(c)
	where := "WHERE 1 = 1"
	args := []any{}
	if sector := firstQuery(c, "sektor_id", "sector_id"); sector != "" {
		where += " AND m.sector_id = ?"
		args = append(args, sector)
	}
	if family := c.Query("family_id"); family != "" {
		where += " AND m.family_id = ?"
		args = append(args, family)
	}
	if hubungan := c.Query("hubungan"); hubungan != "" {
		where += " AND m.hubungan_keluarga = ?"
		args = append(args, hubungan)
	}
	if search := strings.TrimSpace(c.Query("search")); search != "" {
		where += " AND (m.nama LIKE ? OR m.marga LIKE ? OR m.no_hp LIKE ?)"
		like := "%" + search + "%"
		args = append(args, like, like, like)
	}

	var total int
	if err := s.db.QueryRow("SELECT COUNT(1) FROM members m "+where, args...).Scan(&total); err != nil {
		return internalError(c, err)
	}

	query := memberSelectSQL() + " " + where + " ORDER BY m.nama LIMIT ? OFFSET ?"
	queryArgs := append(append([]any{}, args...), perPage, (page-1)*perPage)
	rows, err := s.db.Query(query, queryArgs...)
	if err != nil {
		return internalError(c, err)
	}
	defer rows.Close()

	data, err := scanMemberRows(rows)
	if err != nil {
		return internalError(c, err)
	}
	return c.JSON(fiber.Map{"data": data, "pagination": paginationMap(page, perPage, total)})
}

func (s *Server) getMember(c *fiber.Ctx) error {
	id, err := paramID(c)
	if err != nil {
		return badRequest(c, "invalid member id")
	}
	member, err := s.memberByID(id)
	if err != nil {
		if errors.Is(err, sql.ErrNoRows) {
			return notFound(c, "member not found")
		}
		return internalError(c, err)
	}
	return c.JSON(member)
}

func (s *Server) updateMember(c *fiber.Ctx) error {
	id, err := paramID(c)
	if err != nil {
		return badRequest(c, "invalid member id")
	}
	var req MemberRequest
	if err := c.BodyParser(&req); err != nil {
		return badRequest(c, "invalid request body")
	}
	if req.Nama == "" || req.Gender == "" || req.HubunganKeluarga == "" {
		return badRequest(c, "nama, gender, and hubungan_keluarga are required")
	}

	var familyID, sectorID int64
	if err := s.db.QueryRow("SELECT family_id, sector_id FROM members WHERE id = ?", id).Scan(&familyID, &sectorID); err != nil {
		if errors.Is(err, sql.ErrNoRows) {
			return notFound(c, "member not found")
		}
		return internalError(c, err)
	}
	if req.FamilyID != 0 {
		familyID = req.FamilyID
	}
	if req.SectorID != 0 {
		sectorID = req.SectorID
	}
	isHead := req.HubunganKeluarga == "Kepala Keluarga" || req.IsHeadOfFamily
	res, err := s.db.Exec(memberUpdateSQL(), familyID, sectorID, strings.TrimSpace(req.Nama), nilIfEmpty(req.Marga), req.Gender,
		nilIfEmpty(req.TempatLahir), nilIfEmpty(req.TanggalLahir), nilIfEmpty(req.GolDarah), req.HubunganKeluarga,
		nilIfEmpty(req.Pendidikan), nilIfEmpty(req.Pekerjaan), nilIfEmpty(req.Talenta), nilIfEmpty(req.NoHP),
		nilIfEmpty(req.Alamat), nilIfEmpty(req.Provinsi), nilIfEmpty(req.Kota), nilIfEmpty(req.Kecamatan), nilIfEmpty(req.Kelurahan),
		nilIfEmpty(req.KodePos), nilIfEmpty(req.FotoURL), nilIfEmpty(req.TglBaptis), nilIfEmpty(req.GerejaBaptis),
		nilIfEmpty(req.PendetaBaptis), nilIfEmpty(req.TglSidi), nilIfEmpty(req.GerejaSidi), nilIfEmpty(req.PendetaSidi),
		nilIfEmpty(req.NatsSidi), nilIfEmpty(req.TglPerkawinan), nilIfEmpty(req.GerejaPerkawinan),
		nilIfEmpty(req.PendetaPerkawinan), nilIfEmpty(req.NatsPerkawinan), boolInt(isHead), id)
	if err != nil {
		return badRequest(c, err.Error())
	}
	if affected, _ := res.RowsAffected(); affected == 0 {
		return notFound(c, "member not found")
	}
	if isHead {
		_, _ = s.db.Exec("UPDATE families SET head_member_id = ?, updated_at = datetime('now') WHERE id = ?", id, familyID)
	}
	return c.JSON(fiber.Map{"id": id})
}

func (s *Server) uploadMemberPhoto(c *fiber.Ctx) error {
	id, err := paramID(c)
	if err != nil {
		return badRequest(c, "invalid member id")
	}
	file, err := c.FormFile("foto")
	if err != nil {
		file, err = c.FormFile("file")
	}
	if err != nil {
		return badRequest(c, "foto file is required")
	}

	filename := fmt.Sprintf("member-%d-%d%s", id, time.Now().UnixNano(), filepath.Ext(file.Filename))
	path := filepath.Join(s.cfg.UploadDir, filename)
	if err := c.SaveFile(file, path); err != nil {
		return internalError(c, err)
	}
	url := "/uploads/" + filename
	res, err := s.db.Exec("UPDATE members SET foto_url = ?, updated_at = datetime('now') WHERE id = ?", url, id)
	if err != nil {
		return internalError(c, err)
	}
	if affected, _ := res.RowsAffected(); affected == 0 {
		return notFound(c, "member not found")
	}
	return c.JSON(fiber.Map{"foto_url": url})
}

func (s *Server) deleteMember(c *fiber.Ctx) error {
	id, err := paramID(c)
	if err != nil {
		return badRequest(c, "invalid member id")
	}
	var hubungan string
	var isHead int
	if err := s.db.QueryRow("SELECT hubungan_keluarga, is_head_of_family FROM members WHERE id = ?", id).Scan(&hubungan, &isHead); err != nil {
		if errors.Is(err, sql.ErrNoRows) {
			return notFound(c, "member not found")
		}
		return internalError(c, err)
	}
	if hubungan == "Kepala Keluarga" || isHead == 1 {
		return badRequest(c, "cannot delete Kepala Keluarga without reassigning the family head")
	}
	res, err := s.db.Exec("DELETE FROM members WHERE id = ?", id)
	if err != nil {
		return internalError(c, err)
	}
	if affected, _ := res.RowsAffected(); affected == 0 {
		return notFound(c, "member not found")
	}
	return c.SendStatus(fiber.StatusNoContent)
}

func (s *Server) listOfferings(c *fiber.Ctx) error {
	page, perPage := paginationInput(c)
	where := "WHERE 1 = 1"
	args := []any{}
	if sector := firstQuery(c, "sektor_id", "sector_id"); sector != "" {
		where += " AND o.sector_id = ?"
		args = append(args, sector)
	}
	if family := c.Query("family_id"); family != "" {
		where += " AND o.family_id = ?"
		args = append(args, family)
	}
	if month := c.Query("month"); month != "" {
		where += " AND o.month = ?"
		args = append(args, month)
	}
	if year := c.Query("year"); year != "" {
		where += " AND o.year = ?"
		args = append(args, year)
	}

	var total int
	if err := s.db.QueryRow("SELECT COUNT(1) FROM offerings o "+where, args...).Scan(&total); err != nil {
		return internalError(c, err)
	}
	query := offeringSelectSQL() + " " + where + " ORDER BY o.year DESC, o.month DESC, o.created_at DESC LIMIT ? OFFSET ?"
	queryArgs := append(append([]any{}, args...), perPage, (page-1)*perPage)
	rows, err := s.db.Query(query, queryArgs...)
	if err != nil {
		return internalError(c, err)
	}
	defer rows.Close()
	data, err := scanOfferingRows(rows)
	if err != nil {
		return internalError(c, err)
	}
	return c.JSON(fiber.Map{"data": data, "pagination": paginationMap(page, perPage, total)})
}

func (s *Server) createOffering(c *fiber.Ctx) error {
	var req OfferingRequest
	if err := c.BodyParser(&req); err != nil {
		return badRequest(c, "invalid request body")
	}
	if req.FamilyID == 0 || req.Amount <= 0 || req.Month < 1 || req.Month > 12 || req.Year == 0 {
		return badRequest(c, "family_id, amount, month, and year are required")
	}
	var sectorID int64
	if err := s.db.QueryRow("SELECT sector_id FROM families WHERE id = ?", req.FamilyID).Scan(&sectorID); err != nil {
		if errors.Is(err, sql.ErrNoRows) {
			return notFound(c, "family not found")
		}
		return internalError(c, err)
	}
	res, err := s.db.Exec(`INSERT INTO offerings (family_id, sector_id, amount, month, year, notes, created_by)
		VALUES (?, ?, ?, ?, ?, ?, ?)`, req.FamilyID, sectorID, req.Amount, req.Month, req.Year, nilIfEmpty(req.Notes), localInt64(c, "user_id"))
	if err != nil {
		return badRequest(c, err.Error())
	}
	id, _ := res.LastInsertId()
	return c.Status(fiber.StatusCreated).JSON(fiber.Map{"id": id})
}

func (s *Server) offeringReport(c *fiber.Ctx) error {
	month := c.QueryInt("month", int(time.Now().Month()))
	year := c.QueryInt("year", time.Now().Year())
	where := "WHERE o.month = ? AND o.year = ?"
	args := []any{month, year}
	if sector := firstQuery(c, "sektor_id", "sector_id"); sector != "" {
		where += " AND o.sector_id = ?"
		args = append(args, sector)
	}

	var total int64
	if err := s.db.QueryRow("SELECT COALESCE(SUM(o.amount), 0) FROM offerings o "+where, args...).Scan(&total); err != nil {
		return internalError(c, err)
	}

	rows, err := s.db.Query(`SELECT o.sector_id, s.name, COALESCE(SUM(o.amount), 0), COUNT(DISTINCT o.family_id)
		FROM offerings o JOIN sectors s ON s.id = o.sector_id `+where+`
		GROUP BY o.sector_id, s.name ORDER BY s.name`, args...)
	if err != nil {
		return internalError(c, err)
	}
	bySector := []fiber.Map{}
	for rows.Next() {
		var sectorID, sectorTotal, familyCount int64
		var sectorName string
		if err := rows.Scan(&sectorID, &sectorName, &sectorTotal, &familyCount); err != nil {
			_ = rows.Close()
			return internalError(c, err)
		}
		bySector = append(bySector, fiber.Map{
			"sektor_id":    sectorID,
			"sektor_name":  sectorName,
			"sector_id":    sectorID,
			"sector_name":  sectorName,
			"total":        sectorTotal,
			"family_count": familyCount,
		})
	}
	_ = rows.Close()

	entryRows, err := s.db.Query(offeringSelectSQL()+" "+where+" ORDER BY s.name, hm.nama", args...)
	if err != nil {
		return internalError(c, err)
	}
	defer entryRows.Close()
	entries, err := scanOfferingRows(entryRows)
	if err != nil {
		return internalError(c, err)
	}
	return c.JSON(fiber.Map{"total": total, "by_sector": bySector, "entries": entries})
}

func (s *Server) deleteOffering(c *fiber.Ctx) error {
	id, err := paramID(c)
	if err != nil {
		return badRequest(c, "invalid offering id")
	}
	res, err := s.db.Exec("DELETE FROM offerings WHERE id = ?", id)
	if err != nil {
		return internalError(c, err)
	}
	if affected, _ := res.RowsAffected(); affected == 0 {
		return notFound(c, "offering not found")
	}
	return c.SendStatus(fiber.StatusNoContent)
}

func (s *Server) listSintua(c *fiber.Ctx) error {
	where := "WHERE 1 = 1"
	args := []any{}
	if sector := firstQuery(c, "sektor_id", "sector_id"); sector != "" {
		where += " AND st.sektor_id = ?"
		args = append(args, sector)
	}
	rows, err := s.db.Query(`SELECT st.id, st.member_id, m.nama, st.sektor_id, sec.name, st.created_at
		FROM sintua st
		JOIN members m ON m.id = st.member_id
		JOIN sectors sec ON sec.id = st.sektor_id `+where+`
		ORDER BY sec.name, m.nama`, args...)
	if err != nil {
		return internalError(c, err)
	}
	defer rows.Close()
	data := []fiber.Map{}
	for rows.Next() {
		var id, memberID, sectorID int64
		var memberName, sectorName, createdAt string
		if err := rows.Scan(&id, &memberID, &memberName, &sectorID, &sectorName, &createdAt); err != nil {
			return internalError(c, err)
		}
		data = append(data, fiber.Map{
			"id":          id,
			"member_id":   memberID,
			"member_name": memberName,
			"sektor_id":   sectorID,
			"sektor_name": sectorName,
			"sector_id":   sectorID,
			"sector_name": sectorName,
			"created_at":  createdAt,
		})
	}
	return c.JSON(fiber.Map{"data": data})
}

func (s *Server) createSintua(c *fiber.Ctx) error {
	var req SintuaRequest
	if err := c.BodyParser(&req); err != nil {
		return badRequest(c, "invalid request body")
	}
	if req.MemberID == 0 {
		return badRequest(c, "member_id is required")
	}
	var sectorID int64
	if err := s.db.QueryRow("SELECT sector_id FROM members WHERE id = ?", req.MemberID).Scan(&sectorID); err != nil {
		if errors.Is(err, sql.ErrNoRows) {
			return notFound(c, "member not found")
		}
		return internalError(c, err)
	}
	res, err := s.db.Exec("INSERT INTO sintua (member_id, sektor_id) VALUES (?, ?)", req.MemberID, sectorID)
	if err != nil {
		return badRequest(c, err.Error())
	}
	id, _ := res.LastInsertId()
	return c.Status(fiber.StatusCreated).JSON(fiber.Map{"id": id})
}

func (s *Server) deleteSintua(c *fiber.Ctx) error {
	id, err := paramID(c)
	if err != nil {
		return badRequest(c, "invalid sintua id")
	}
	res, err := s.db.Exec("DELETE FROM sintua WHERE id = ?", id)
	if err != nil {
		return internalError(c, err)
	}
	if affected, _ := res.RowsAffected(); affected == 0 {
		return notFound(c, "sintua not found")
	}
	return c.SendStatus(fiber.StatusNoContent)
}

func (s *Server) listAttendance(c *fiber.Ctx) error {
	where := "WHERE 1 = 1"
	args := []any{}
	if date := c.Query("date"); date != "" {
		where += " AND a.date = ?"
		args = append(args, date)
	}
	if seksi := c.Query("seksi"); seksi != "" {
		where += " AND a.seksi = ?"
		args = append(args, seksi)
	}
	rows, err := s.db.Query(`SELECT a.id, a.member_id, m.nama, a.date, a.status, a.seksi, a.created_by, a.created_at
		FROM attendance a
		JOIN members m ON m.id = a.member_id `+where+`
		ORDER BY a.date DESC, a.seksi, m.nama`, args...)
	if err != nil {
		return internalError(c, err)
	}
	defer rows.Close()
	data := []fiber.Map{}
	for rows.Next() {
		var id, memberID, createdBy int64
		var memberName, date, status, seksi, createdAt string
		if err := rows.Scan(&id, &memberID, &memberName, &date, &status, &seksi, &createdBy, &createdAt); err != nil {
			return internalError(c, err)
		}
		data = append(data, fiber.Map{
			"id":          id,
			"member_id":   memberID,
			"member_name": memberName,
			"date":        date,
			"status":      status,
			"seksi":       seksi,
			"created_by":  createdBy,
			"created_at":  createdAt,
		})
	}
	return c.JSON(fiber.Map{"data": data})
}

func (s *Server) recordAttendance(c *fiber.Ctx) error {
	var req AttendanceRequest
	if err := c.BodyParser(&req); err != nil {
		return badRequest(c, "invalid request body")
	}
	if req.MemberID == 0 || req.Date == "" || req.Status == "" || req.Seksi == "" {
		return badRequest(c, "member_id, date, status, and seksi are required")
	}
	res, err := s.db.Exec(`INSERT INTO attendance (member_id, date, status, seksi, created_by)
		VALUES (?, ?, ?, ?, ?)
		ON CONFLICT(member_id, date, seksi) DO UPDATE
		SET status = excluded.status, created_by = excluded.created_by`,
		req.MemberID, req.Date, req.Status, req.Seksi, localInt64(c, "user_id"))
	if err != nil {
		return badRequest(c, err.Error())
	}
	id, _ := res.LastInsertId()
	return c.Status(fiber.StatusCreated).JSON(fiber.Map{"id": id})
}

func insertMember(exec txExec, familyID, sectorID int64, req MemberRequest) (int64, error) {
	if strings.TrimSpace(req.Nama) == "" || req.Gender == "" || req.HubunganKeluarga == "" {
		return 0, fmt.Errorf("member nama, gender, and hubungan_keluarga are required")
	}
	isHead := req.HubunganKeluarga == "Kepala Keluarga" || req.IsHeadOfFamily
	res, err := exec.Exec(memberInsertSQL(), familyID, sectorID, strings.TrimSpace(req.Nama), nilIfEmpty(req.Marga), req.Gender,
		nilIfEmpty(req.TempatLahir), nilIfEmpty(req.TanggalLahir), nilIfEmpty(req.GolDarah), req.HubunganKeluarga,
		nilIfEmpty(req.Pendidikan), nilIfEmpty(req.Pekerjaan), nilIfEmpty(req.Talenta), nilIfEmpty(req.NoHP),
		nilIfEmpty(req.Alamat), nilIfEmpty(req.Provinsi), nilIfEmpty(req.Kota), nilIfEmpty(req.Kecamatan), nilIfEmpty(req.Kelurahan),
		nilIfEmpty(req.KodePos), nilIfEmpty(req.FotoURL), nilIfEmpty(req.TglBaptis), nilIfEmpty(req.GerejaBaptis),
		nilIfEmpty(req.PendetaBaptis), nilIfEmpty(req.TglSidi), nilIfEmpty(req.GerejaSidi), nilIfEmpty(req.PendetaSidi),
		nilIfEmpty(req.NatsSidi), nilIfEmpty(req.TglPerkawinan), nilIfEmpty(req.GerejaPerkawinan),
		nilIfEmpty(req.PendetaPerkawinan), nilIfEmpty(req.NatsPerkawinan), boolInt(isHead))
	if err != nil {
		return 0, err
	}
	return res.LastInsertId()
}

func (s *Server) membersForFamily(familyID int64) ([]fiber.Map, error) {
	rows, err := s.db.Query(memberSelectSQL()+" WHERE m.family_id = ? ORDER BY CASE m.hubungan_keluarga WHEN 'Kepala Keluarga' THEN 1 WHEN 'Istri' THEN 2 ELSE 3 END, m.nama", familyID)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	return scanMemberRows(rows)
}

func (s *Server) memberByID(id int64) (fiber.Map, error) {
	rows, err := s.db.Query(memberSelectSQL()+" WHERE m.id = ?", id)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	members, err := scanMemberRows(rows)
	if err != nil {
		return nil, err
	}
	if len(members) == 0 {
		return nil, sql.ErrNoRows
	}
	return members[0], nil
}

func memberSelectSQL() string {
	return `SELECT m.id, m.family_id, m.sector_id, s.name, m.nama, m.marga, m.gender,
		m.tempat_lahir, m.tanggal_lahir, m.gol_darah, m.hubungan_keluarga, m.pendidikan,
		m.pekerjaan, m.talenta, m.no_hp, m.alamat, m.provinsi, m.kota, m.kecamatan,
		m.kelurahan, m.kode_pos, m.foto_url, m.tgl_baptis, m.gereja_baptis,
		m.pendeta_baptis, m.tgl_sidi, m.gereja_sidi, m.pendeta_sidi, m.nats_sidi,
		m.tgl_perkawinan, m.gereja_perkawinan, m.pendeta_perkawinan, m.nats_perkawinan,
		m.is_head_of_family, m.created_at
		FROM members m JOIN sectors s ON s.id = m.sector_id`
}

func memberInsertSQL() string {
	return `INSERT INTO members
		(family_id, sector_id, nama, marga, gender, tempat_lahir, tanggal_lahir, gol_darah,
		hubungan_keluarga, pendidikan, pekerjaan, talenta, no_hp, alamat, provinsi, kota,
		kecamatan, kelurahan, kode_pos, foto_url, tgl_baptis, gereja_baptis, pendeta_baptis,
		tgl_sidi, gereja_sidi, pendeta_sidi, nats_sidi, tgl_perkawinan, gereja_perkawinan,
		pendeta_perkawinan, nats_perkawinan, is_head_of_family)
		VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`
}

func memberUpdateSQL() string {
	return `UPDATE members SET
		family_id = ?, sector_id = ?, nama = ?, marga = ?, gender = ?, tempat_lahir = ?,
		tanggal_lahir = ?, gol_darah = ?, hubungan_keluarga = ?, pendidikan = ?, pekerjaan = ?,
		talenta = ?, no_hp = ?, alamat = ?, provinsi = ?, kota = ?, kecamatan = ?, kelurahan = ?,
		kode_pos = ?, foto_url = ?, tgl_baptis = ?, gereja_baptis = ?, pendeta_baptis = ?,
		tgl_sidi = ?, gereja_sidi = ?, pendeta_sidi = ?, nats_sidi = ?, tgl_perkawinan = ?,
		gereja_perkawinan = ?, pendeta_perkawinan = ?, nats_perkawinan = ?, is_head_of_family = ?,
		updated_at = datetime('now') WHERE id = ?`
}

func scanMemberRows(rows *sql.Rows) ([]fiber.Map, error) {
	data := []fiber.Map{}
	for rows.Next() {
		var id, familyID, sectorID int64
		var sectorName, nama, gender, hubungan, createdAt string
		var isHead int
		var marga, tempatLahir, tanggalLahir, golDarah, pendidikan, pekerjaan, talenta, noHP sql.NullString
		var alamat, provinsi, kota, kecamatan, kelurahan, kodePos, fotoURL sql.NullString
		var tglBaptis, gerejaBaptis, pendetaBaptis, tglSidi, gerejaSidi, pendetaSidi, natsSidi sql.NullString
		var tglPerkawinan, gerejaPerkawinan, pendetaPerkawinan, natsPerkawinan sql.NullString

		err := rows.Scan(&id, &familyID, &sectorID, &sectorName, &nama, &marga, &gender,
			&tempatLahir, &tanggalLahir, &golDarah, &hubungan, &pendidikan, &pekerjaan, &talenta,
			&noHP, &alamat, &provinsi, &kota, &kecamatan, &kelurahan, &kodePos, &fotoURL,
			&tglBaptis, &gerejaBaptis, &pendetaBaptis, &tglSidi, &gerejaSidi, &pendetaSidi,
			&natsSidi, &tglPerkawinan, &gerejaPerkawinan, &pendetaPerkawinan, &natsPerkawinan,
			&isHead, &createdAt)
		if err != nil {
			return nil, err
		}
		data = append(data, fiber.Map{
			"id":                 id,
			"family_id":          familyID,
			"sector_id":          sectorID,
			"sector_name":        sectorName,
			"nama":               nama,
			"marga":              stringPtr(marga),
			"gender":             gender,
			"tempat_lahir":       stringPtr(tempatLahir),
			"tanggal_lahir":      stringPtr(tanggalLahir),
			"gol_darah":          stringPtr(golDarah),
			"hubungan_keluarga":  hubungan,
			"pendidikan":         stringPtr(pendidikan),
			"pekerjaan":          stringPtr(pekerjaan),
			"talenta":            stringPtr(talenta),
			"no_hp":              stringPtr(noHP),
			"alamat":             stringPtr(alamat),
			"provinsi":           stringPtr(provinsi),
			"kota":               stringPtr(kota),
			"kecamatan":          stringPtr(kecamatan),
			"kelurahan":          stringPtr(kelurahan),
			"kode_pos":           stringPtr(kodePos),
			"foto_url":           stringPtr(fotoURL),
			"tgl_baptis":         stringPtr(tglBaptis),
			"gereja_baptis":      stringPtr(gerejaBaptis),
			"pendeta_baptis":     stringPtr(pendetaBaptis),
			"tgl_sidi":           stringPtr(tglSidi),
			"gereja_sidi":        stringPtr(gerejaSidi),
			"pendeta_sidi":       stringPtr(pendetaSidi),
			"nats_sidi":          stringPtr(natsSidi),
			"tgl_perkawinan":     stringPtr(tglPerkawinan),
			"gereja_perkawinan":  stringPtr(gerejaPerkawinan),
			"pendeta_perkawinan": stringPtr(pendetaPerkawinan),
			"nats_perkawinan":    stringPtr(natsPerkawinan),
			"is_head_of_family":  isHead == 1,
			"created_at":         createdAt,
		})
	}
	return data, rows.Err()
}

func offeringSelectSQL() string {
	return `SELECT o.id, o.family_id, o.sector_id, s.name, hm.nama, o.amount, o.month, o.year,
		o.notes, o.created_by, o.created_at
		FROM offerings o
		JOIN sectors s ON s.id = o.sector_id
		JOIN families f ON f.id = o.family_id
		LEFT JOIN members hm ON hm.id = f.head_member_id`
}

func scanOfferingRows(rows *sql.Rows) ([]fiber.Map, error) {
	data := []fiber.Map{}
	for rows.Next() {
		var id, familyID, sectorID, amount, createdBy int64
		var month, year int
		var sectorName, createdAt string
		var familyHeadName, notes sql.NullString
		if err := rows.Scan(&id, &familyID, &sectorID, &sectorName, &familyHeadName,
			&amount, &month, &year, &notes, &createdBy, &createdAt); err != nil {
			return nil, err
		}
		data = append(data, fiber.Map{
			"id":               id,
			"family_id":        familyID,
			"sector_id":        sectorID,
			"sector_name":      sectorName,
			"family_head_name": stringPtr(familyHeadName),
			"amount":           amount,
			"month":            month,
			"year":             year,
			"notes":            stringPtr(notes),
			"created_by":       createdBy,
			"created_at":       createdAt,
		})
	}
	return data, rows.Err()
}

func scanUserRow(rows *sql.Rows) (fiber.Map, error) {
	var id, roleID int64
	var username, email, namaDepan, roleName, status, createdAt string
	var namaBelakang, sectorName, lastAccess sql.NullString
	var sektorID sql.NullInt64
	if err := rows.Scan(&id, &username, &email, &namaDepan, &namaBelakang, &roleID,
		&roleName, &sektorID, &sectorName, &status, &lastAccess, &createdAt); err != nil {
		return nil, err
	}
	return fiber.Map{
		"id":            id,
		"username":      username,
		"email":         email,
		"nama_depan":    namaDepan,
		"nama_belakang": stringPtr(namaBelakang),
		"role_id":       roleID,
		"role_name":     roleName,
		"sektor_id":     int64Ptr(sektorID),
		"sector_name":   stringPtr(sectorName),
		"status":        status,
		"last_access":   stringPtr(lastAccess),
		"created_at":    createdAt,
	}, nil
}

func env(key, fallback string) string {
	if value := os.Getenv(key); value != "" {
		return value
	}
	return fallback
}

func secondsEnv(key string, fallback int64) time.Duration {
	return time.Duration(int64Env(key, fallback)) * time.Second
}

func int64Env(key string, fallback int64) int64 {
	value := os.Getenv(key)
	if value == "" {
		return fallback
	}
	parsed, err := strconv.ParseInt(value, 10, 64)
	if err != nil {
		return fallback
	}
	return parsed
}

func paramID(c *fiber.Ctx) (int64, error) {
	return strconv.ParseInt(c.Params("id"), 10, 64)
}

func localInt64(c *fiber.Ctx, key string) int64 {
	value, _ := c.Locals(key).(int64)
	return value
}

func claimInt64(value any) (int64, bool) {
	switch v := value.(type) {
	case float64:
		return int64(v), true
	case int64:
		return v, true
	case int:
		return int64(v), true
	case string:
		parsed, err := strconv.ParseInt(v, 10, 64)
		return parsed, err == nil
	default:
		return 0, false
	}
}

func paginationInput(c *fiber.Ctx) (int, int) {
	page := c.QueryInt("page", 1)
	perPage := c.QueryInt("per_page", 20)
	if page < 1 {
		page = 1
	}
	if perPage < 1 {
		perPage = 20
	}
	if perPage > 100 {
		perPage = 100
	}
	return page, perPage
}

func paginationMap(page, perPage, total int) fiber.Map {
	totalPages := int(math.Ceil(float64(total) / float64(perPage)))
	return fiber.Map{
		"page":        page,
		"per_page":    perPage,
		"total":       total,
		"total_pages": totalPages,
	}
}

func firstQuery(c *fiber.Ctx, keys ...string) string {
	for _, key := range keys {
		if value := c.Query(key); value != "" {
			return value
		}
	}
	return ""
}

func nilIfEmpty(value string) any {
	value = strings.TrimSpace(value)
	if value == "" {
		return nil
	}
	return value
}

func stringPtr(value sql.NullString) *string {
	if !value.Valid {
		return nil
	}
	return &value.String
}

func int64Ptr(value sql.NullInt64) *int64 {
	if !value.Valid {
		return nil
	}
	return &value.Int64
}

func boolInt(value bool) int {
	if value {
		return 1
	}
	return 0
}

func randomToken() (string, error) {
	buf := make([]byte, 32)
	if _, err := rand.Read(buf); err != nil {
		return "", err
	}
	return hex.EncodeToString(buf), nil
}

func hashToken(token string) string {
	sum := sha256.Sum256([]byte(token))
	return hex.EncodeToString(sum[:])
}

func badRequest(c *fiber.Ctx, message string) error {
	return c.Status(fiber.StatusBadRequest).JSON(fiber.Map{"error": message, "code": "VALIDATION_ERROR"})
}

func notFound(c *fiber.Ctx, message string) error {
	return c.Status(fiber.StatusNotFound).JSON(fiber.Map{"error": message})
}

func internalError(c *fiber.Ctx, err error) error {
	log.Printf("internal error: %v", err)
	return c.Status(fiber.StatusInternalServerError).JSON(fiber.Map{"error": "internal server error"})
}
