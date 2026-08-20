# ARSIPARI — Dokumentasi Skema Database (Database Schema)

Aplikasi **ARSIPARI** menggunakan **SQLite3** sebagai basis data terpusat internal server. Dokumen ini menjelaskan struktur tabel, relasi, dan tipe data.

---

## 1. Diagram Relasi Tabel (Entity Relationship Summary)

```text
users ───(1:N)─── archives ───(1:N)─── archive_versions
  │                  │                     │
(N:1)              (N:1)                 (N:1)
  │                  │                     │
departments      categories            uploader (users)
                     │
                   (N:1)
             retention_policies
```

---

## 2. Rincian Tabel Database

### A. Tabel `users`
Penyimpanan akun pengguna sistem.
- `id` (INTEGER, Primary Key, Auto Increment)
- `name` (VARCHAR, Required)
- `email` (VARCHAR, Unique, Required)
- `password` (VARCHAR, Hashed, Required)
- `role` (VARCHAR, Enum: `admin`, `operator`, `viewer`)
- `department_id` (INTEGER, Foreign Key -> `departments.id`, Nullable)
- `is_active` (BOOLEAN, Default: `true`)
- `last_login_at` (TIMESTAMP, Nullable)
- `created_at`, `updated_at`

### B. Tabel `departments`
Penyimpanan unit / bidang kerja madrasah.
- `id` (INTEGER, Primary Key, Auto Increment)
- `name` (VARCHAR, Required)
- `code` (VARCHAR, Unique, Required)
- `description` (TEXT, Nullable)
- `is_active` (BOOLEAN, Default: `true`)
- `created_at`, `updated_at`

### C. Tabel `categories`
Penyimpanan struktur kategori arsip 2-tingkat.
- `id` (INTEGER, Primary Key, Auto Increment)
- `name` (VARCHAR, Required)
- `code` (VARCHAR, Unique, Required)
- `parent_id` (INTEGER, Foreign Key -> `categories.id`, Nullable)
- `description` (TEXT, Nullable)
- `is_active` (BOOLEAN, Default: `true`)
- `created_at`, `updated_at`

### D. Tabel `document_types`
Penyimpanan jenis dokumen (SK, SPJ, Sertifikat, dll).
- `id` (INTEGER, Primary Key, Auto Increment)
- `name` (VARCHAR, Required)
- `code` (VARCHAR, Unique, Required)
- `description` (TEXT, Nullable)
- `is_active` (BOOLEAN, Default: `true`)
- `created_at`, `updated_at`

### E. Tabel `retention_policies`
Penyimpanan kebijakan retensi jangka waktu simpan.
- `id` (INTEGER, Primary Key, Auto Increment)
- `name` (VARCHAR, Required)
- `duration_years` (INTEGER, Nullable - null jika permanen)
- `is_permanent` (BOOLEAN, Default: `false`)
- `description` (TEXT, Nullable)
- `is_active` (BOOLEAN, Default: `true`)
- `created_at`, `updated_at`

### F. Tabel `archives`
Tabel utama penyimpanan metadata arsip digital.
- `id` (CHAR 26, ULID, Primary Key)
- `archive_number` (VARCHAR, Unique, Format: `ARSIP-YYYY-XXXXXX`)
- `document_number` (VARCHAR, Nullable)
- `title` (VARCHAR, Required)
- `description` (TEXT, Nullable)
- `status` (VARCHAR, Enum: `active`, `inactive`, Default: `active`)
- `retention_policy_id` (INTEGER, Foreign Key -> `retention_policies.id`, Nullable)
- `retention_until` (DATE, Nullable, Indexed)
- `category_id` (INTEGER, Foreign Key -> `categories.id`, Required)
- `department_id` (INTEGER, Foreign Key -> `departments.id`, Required)
- `year` (INTEGER, Required, Indexed)
- `document_date` (DATE, Required, Indexed)
- `document_type` (VARCHAR, Required)
- `keywords` (TEXT, Nullable)
- `original_filename` (VARCHAR, Required)
- `stored_filename` (VARCHAR, Required)
- `file_path` (VARCHAR, Required)
- `mime_type` (VARCHAR, Required)
- `file_size` (BIGINT, Required)
- `uploaded_by` (INTEGER, Foreign Key -> `users.id`, Required)
- `deleted_at` (TIMESTAMP, Nullable - Soft Delete)
- `created_at`, `updated_at`

### G. Tabel `archive_versions`
Penyimpanan riwayat versi dokumen imutabel (`v1`, `v2`, `v3`).
- `id` (CHAR 26, ULID, Primary Key)
- `archive_id` (CHAR 26, Foreign Key -> `archives.id`, Required)
- `version_number` (INTEGER, Required)
- `original_filename` (VARCHAR, Required)
- `stored_filename` (VARCHAR, Required)
- `file_path` (VARCHAR, Required)
- `mime_type` (VARCHAR, Required)
- `file_size` (BIGINT, Required)
- `change_note` (TEXT, Nullable)
- `uploaded_by` (INTEGER, Foreign Key -> `users.id`, Required)
- `created_at`, `updated_at`
- Unique Index: `['archive_id', 'version_number']`

### H. Tabel `audit_logs`
Penyimpanan rekam jejak aktivitas pengguna (*Audit Trail*).
- `id` (INTEGER, Primary Key, Auto Increment)
- `user_id` (INTEGER, Foreign Key -> `users.id`, Nullable)
- `action` (VARCHAR, Required)
- `entity_type` (VARCHAR, Nullable)
- `entity_id` (VARCHAR, Nullable)
- `description` (TEXT, Required)
- `ip_address` (VARCHAR, Nullable)
- `user_agent` (TEXT, Nullable)
- `metadata` (JSON, Nullable)
- `created_at`, `updated_at`
