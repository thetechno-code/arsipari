# Changelog — ARSIPARI

Semua perubahan dan rilis aplikasi **ARSIPARI (Sistem Manajemen Arsip Digital MTsN 1 Magelang)** dicatat dalam dokumen ini.

---

## [1.0.0] - 2026-08-20

### Fitur Utama (Features)
- **Autentikasi & RBAC Foundation**: Multi-role (Admin, Operator, Viewer) dengan proteksi otorisasi bertingkat.
- **Pengelolaan Pengguna & Profil**: Manajemen akun pengguna, pencarian, reset password, dan pembaruan profil mandiri.
- **Master Data Klasifikasi**: Pengelolaan Kategori Arsip 2-tingkat (Induk-Subkategori), Unit/Bidang Kerja, Jenis Dokumen, dan Kebijakan Retensi.
- **Manajemen & Upload Arsip**: Unggah dokumen digital dengan metadata lengkap, penomoran arsip unik otomatis (`ARSIP-YYYY-XXXXXX`), dan Private File Storage yang terisolasi dari akses publik direct.
- **Pencarian & Advanced Filter**: Pencarian teks penuh (Full-text search) serta penyaringan multi-kriteria (Kategori, Unit, Jenis, Status, Retensi, Tanggal).
- **Dashboard Statistik & KPI**: Ringkasan jumlah arsip total, aktif, retensi, aktivitas terbaru, dan akses cepat.
- **Archive Versioning (Riwayat Versi)**: Dukungan imutabilitas versi dokumen (`v1`, `v2`, `v3`) dan pemulihan versi lama sebagai versi baru.
- **Archive Lifecycle & Trash**: Opsi status operasional (Aktif/Tidak Aktif), Soft Delete ke Tempat Sampah, dan Pemulihan (Restore).
- **Retention Policy**: Kebijakan retensi permanen dan berjangka (1-10 Tahun) lengkap dengan deteksi jatuh tempo (*Due Soon* 90 Hari) & *Expired Warning Banner*.
- **Audit Trail & Log**: Audit log otomatis mencatat setiap aktivitas sistem dilengkapi modal JSON metadata viewer dan rekam jejak aktivitas per arsip.
- **Laporan & Export (Excel & PDF)**: Halaman Laporan Rekapitulasi dengan penyaringan fleksibel, ekspor Excel `.xlsx` (PhpSpreadsheet), dan ekspor PDF `.pdf` A4 Landscape (DomPDF).
- **Backup & Restore System**: Command CLI `php artisan arsipari:backup` & `php artisan arsipari:restore` beserta Web UI Management backup ZIP (Database + Berkas Private + Manifest + Checksum SHA-256).
- **Deployment & Security Hardening**: Dokumentasi instalasi lengkap, lembar cek deployment, script otomasi `scripts/deploy.sh`, dan halaman Kesehatan Sistem.
