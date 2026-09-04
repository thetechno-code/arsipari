# ARSIPKAN — Sistem Manajemen Arsip

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3%2B-blue.svg)](https://php.net)
[![SQLite](https://img.shields.io/badge/Database-SQLite-green.svg)](https://sqlite.org)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

**ARSIPKAN** adalah sistem manajemen arsip digital berbasis web yang dikembangkan untuk mengelola arsip dan dokumen digital secara terpusat, terstruktur, aman, dan efisien pada server internal sekolah (LAN).

---

## 🚀 Fitur Utama

- 🔐 **Authentication & RBAC**: Peran Admin, Operator, dan Viewer dengan hak akses terisolasi.
- 📁 **Master Data Klasifikasi**: Kategori Arsip Hierarkis 2-Tingkat, Unit Kerja, Jenis Dokumen, & Kebijakan Retensi.
- 📄 **Arsip Digital & Private Storage**: Pengunggahan dokumen dengan metadata lengkap, penomoran arsip otomatis (`ARSIP-2026-000123`), dan penyimpanan berkas private terlindungi.
- 🔍 **Pencarian Cepat & Filter Lanjutan**: Pencarian kata kunci full-text dan penyaringan multi-kriteria.
- 🔄 **Archive Versioning**: Riwayat versi dokumen imutabel (`v1`, `v2`, `v3`) tanpa risiko file tertimpa.
- ⏳ **Archive Lifecycle & Retensi**: Status operasional, retensi permanen/berjangka (1-10 Tahun), deteksi jatuh tempo 90 hari, dan Tempat Sampah (*Trash*).
- 📜 **Audit Trail & Log**: Catatan aktivitas pengguna otomatis dilengkapi rincian JSON metadata.
- 📊 **Laporan & Ekspor**: Ekspor rekapitulasi data ke format **Excel (.xlsx)** dan **PDF (.pdf)** A4 Landscape.
- 📦 **Backup & Restore System**: Paket backup otomatis (ZIP) mencakup DB SQLite, Berkas Private, Manifest JSON, dan Checksum SHA-256 via CLI & Web UI.
- 🛠️ **Deployment Ready**: Dokumentasi instalasi, script otomasi deployment, dan halaman Kesehatan Sistem.

---

## 🛠️ Stack Teknologi

- **Backend Framework**: Laravel 12 (PHP 8.3+)
- **Database Engine**: SQLite3
- **Frontend & Styling**: Tailwind CSS, Alpine.js, Blade Components
- **Export Libraries**: `phpoffice/phpspreadsheet`, `barryvdh/laravel-dompdf`
- **Architecture**: Single-tenant internal LAN web application

---

## ⚡ Instalasi Cepat (Development / Staging)

```bash
# 1. Clone repository
git clone https://github.com/mtsn1magelang/arsipari.git
cd arsipari

# 2. Install dependensi PHP & Node.js
composer install
npm install

# 3. Konfigurasi file .env
cp .env.example .env
php artisan key:generate

# 4. Inisialisasi Database SQLite
touch database/database.sqlite
php artisan migrate --seed

# 5. Buat Akun Admin Pertama
php artisan arsipari:create-admin

# 6. Kompilasi asset & Jalankan Server Development
npm run build
php artisan serve
```

Akses aplikasi pada peramban web: `http://127.0.0.1:8000`.

---

## 📚 Dokumentasi Lengkap (`docs/`)

- [Panduan Instalasi Server](docs/installation.md) (`docs/installation.md`)
- [Arsitektur Deployment Produksi](docs/deployment.md) (`docs/deployment.md`)
- [Prosedur Backup & Restore Data](docs/backup-restore.md) (`docs/backup-restore.md`)
- [Panduan Pengguna (User Guide)](docs/user-guide.md) (`docs/user-guide.md`)
- [Panduan Administrator](docs/administrator-guide.md) (`docs/administrator-guide.md`)
- [Panduan Troubleshooting Error](docs/troubleshooting.md) (`docs/troubleshooting.md`)
- [Lembar Cek Deployment](docs/deployment-checklist.md) (`docs/deployment-checklist.md`)

---

## 📄 Lisensi

Pengembangan aplikasi ARSIPARI berlisensi internal untuk **MTsN 1 Magelang**.
