# ARSIPARI — Matriks Penelusuran Kebutuhan (Requirement Traceability Matrix)

Dokumen ini memetakan seluruh kebutuhan fungsional dan non-fungsional aplikasi **ARSIPARI** untuk **MTsN 1 Magelang** terhadap komponen modul dan status pengujian akhir.

---

## Matriks Pemetaan Kebutuhan (Traceability Matrix)

| Ref ID | Kebutuhan / Fitur | Modul Aplikasi | Komponen Implementation | Status Test | Status Akhir |
|---|---|---|---|---|---|
| **REQ-01** | Autentikasi Login, Logout & Rate Limiting | Auth | `LoginController`, `RateLimiter` | `AuthenticationTest` | **PASS** |
| **REQ-02** | Manajemen User & Reset Password | User Management | `UserController`, `User` model | `UserManagementTest` | **PASS** |
| **REQ-03** | Hak Akses Bertingkat (Admin, Operator, Viewer) | RBAC | `CheckRole` middleware, `ArchivePolicy` | `AuthorizationTest` | **PASS** |
| **REQ-04** | Master Data Unit Kerja / Department | Master Data | `DepartmentController`, `Department` model | `DepartmentManagementTest` | **PASS** |
| **REQ-05** | Master Data Kategori & Subkategori (Hierarkis) | Master Data | `CategoryController`, `Category` model | `CategoryManagementTest` | **PASS** |
| **REQ-06** | Master Data Jenis Dokumen & Kode Klasifikasi | Master Data | `DocumentTypeController`, `DocumentType` model | `DocumentTypeManagementTest` | **PASS** |
| **REQ-07** | Master Data Kebijakan Retensi Arsip | Master Data | `RetentionPolicyController`, `RetentionPolicy` model | `ArchiveRetentionTest` | **PASS** |
| **REQ-08** | Unggah Dokumen Arsip & Auto Numbering | Archive Core | `ArchiveController`, `ArchiveService` | `ArchiveCreationTest` | **PASS** |
| **REQ-09** | Private File Storage Isolation | Security / Storage | `ArchiveFileService`, Private disk | `ArchiveDownloadTest` | **PASS** |
| **REQ-10** | Pengunduhan Berkas Aman & Proteksi IDOR | Secure Download | `ArchiveController@download`, `ArchivePolicy` | `ArchiveDownloadTest` | **PASS** |
| **REQ-11** | Pencarian Full-text & Filter Multi-Kriteria | Search & Discovery | `ArchiveController@index`, Eloquent Scopes | `ArchiveSearchFilterTest` | **PASS** |
| **REQ-12** | Versi Dokumen Imutabel (Versioning v1, v2, v3) | Versioning | `ArchiveVersionController`, `ArchiveVersion` model | `ArchiveVersioningTest` | **PASS** |
| **REQ-13** | Pemulihan Versi Lama (*Version Restore*) | Versioning | `ArchiveVersionController@restore` | `ArchiveVersioningTest` | **PASS** |
| **REQ-14** | Status Operasional & Retensi Warning Banner | Lifecycle | `Archive` Accessors, `RetentionService` | `ArchiveRetentionTest` | **PASS** |
| **REQ-15** | Soft Delete Arsip & Tempat Sampah (*Trash*) | Lifecycle / Trash | `TrashController`, `SoftDeletes` | `ArchiveLifecycleTest` | **PASS** |
| **REQ-16** | Rehat Audit Trail Aktivitas System | Audit Log | `AuditLogController`, `AuditLogService` | `AuditLogUiTest` | **PASS** |
| **REQ-17** | Dashboard Statistik & KPI Real-time | Dashboard | `DashboardController` | `DashboardStatisticsTest` | **PASS** |
| **REQ-18** | Pratinjau Laporan Rekapitulasi | Reporting | `ReportController`, `ReportService` | `ReportExportTest` | **PASS** |
| **REQ-19** | Export Laporan Excel (.xlsx) | Reporting / Export | `ReportService@exportExcel`, `PhpSpreadsheet` | `ReportExportTest` | **PASS** |
| **REQ-20** | Export Laporan PDF A4 Landscape (.pdf) | Reporting / Export | `ReportService@exportPdf`, `DomPDF` | `ReportExportTest` | **PASS** |
| **REQ-21** | Paket Backup Sistem (DB + Berkas + Manifest) | Backup System | `BackupService`, `php artisan arsipari:backup` | `BackupSystemTest` | **PASS** |
| **REQ-22** | Pemulihan Bencana CLI (*Disaster Restore*) | Backup System | `php artisan arsipari:restore` | `BackupSystemTest` | **PASS** |
| **REQ-23** | Perintah CLI Inisialisasi Admin Pertama | CLI Setup | `php artisan arsipari:create-admin` | Manual QA | **PASS** |
| **REQ-24** | Halaman Informasi & Kesehatan Server | System Health | `SystemHealthController` | `SystemHealthTest` | **PASS** |
| **REQ-25** | Otomasi Deployment Produksi & Dokumentasi | Handover / Docs | `scripts/deploy.sh`, `docs/*` suite | Manual QA | **PASS** |

---

## Kesimpulan Traceability
Seluruh **25 kebutuhan utama** project ARSIPARI v1.0.0 telah 100% diimplementasikan, teruji oleh unit test, dan terverifikasi berstatus **PASS**.
