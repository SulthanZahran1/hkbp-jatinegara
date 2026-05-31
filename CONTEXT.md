# HKBP Jatinegara — Domain Glossary

## Project
Modernizing HKBP Jatinegara's church administration from a legacy PHP stack (Laravel thin client + separate backend API) to **TypeScript (Vue SPA frontend) + Go (Fiber API backend)**.

## Database
**Turso** (libSQL/SQLite-based edge database). Chosen for simplicity, zero operations overhead, and adequate performance for <10 concurrent users.

## Authentication
**JWT** with access tokens (short-lived) and refresh tokens (long-lived). Chosen to support both the SPA and any future mobile/native clients from a single auth system.

## Architecture

- **Vue SPA** — browser client, communicates with Go backend via JSON API
- **Go API (Fiber)** — single backend serving all business logic and data access
- **Database** — Turso (libSQL)

## Domain Concepts

### Member
An individual registered in the church system. Belongs to a **Family** and a **Sector**. Rich profile including personal details, education, occupation, baptism/confirmation/marriage sacramental records, and contact info.

### Marga
Batak clan/family name. A cultural attribute of a **Member**.

### Family
A household unit within the church. Has a **Head of Family** (Kepala Keluarga), optional spouse (Istri/Suami), and children (Anak). Tracked under a specific **Sector**. Family relationship is modeled as an attribute on **Member** (Kepala Keluarga / Istri / Anak).

### Sector (Sektor)
A geographical or organizational subdivision within the church. Members and families are grouped by sector.

### Elder (Sintua)
A church elder/presbyter. A role assigned to certain members within a sector.

### Profession (Profesi)
A member's occupation, tracked as master data.

### Monthly Offering (Persembahan Bulanan)
Recurring financial contributions tracked per family or per sector, aggregated into monthly reports.

### Attendance / Presence
Tracking of member attendance, currently used by the Music and Multimedia sections (Seksi Musik & Seksi Multimedia).

### User (Pengguna)
A system user with authentication credentials. Has a **role** (e.g. admin) and is scoped to a **sector**.

### Role
Access control level assigned to a **User**.

### Service Material (Bahan)
Content for church services, organized by time slot: morning (Pagi), afternoon (Siang), evening (Sore).

## Multi-Tenancy Note
The legacy architecture separates the frontend (Laravel) from the backend (a separate API at `dbruas-be.hkbpjtn.web.id`). The new architecture collapses this into a single Go API + Vue SPA.
