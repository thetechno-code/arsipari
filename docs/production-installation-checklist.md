# ARSIPARI — Lembar Cek Instalasi Produksi (Production Installation Checklist)

Gunakan lembar cek ini saat melakukan instalasi aplikasi ARSIPARI pada server produksi sekolah.

---

### A. Lingkungan Server (Server Environment)
- [x] OS Ubuntu Server 22.04 LTS / 24.04 LTS terpasang.
- [x] PHP 8.2 / 8.3-FPM terpasang beserta modul: `sqlite3`, `mbstring`, `zip`, `xml`, `fileinfo`, `gd`.
- [x] Nginx Web Server terpasang dan berstatus aktif.
- [x] Composer 2.x+ terpasang.

### B. Konfigurasi Aplikasi & Database
- [x] Source code ditempatkan pada folder produksi `/var/www/arsipari`.
- [x] Perintah `composer install --no-dev --optimize-autoloader` berhasil dijalankan.
- [x] File `.env` dibuat dengan `APP_ENV=production` dan `APP_DEBUG=false`.
- [x] Kunci aplikasi tergenerasi via `php artisan key:generate`.
- [x] File `database/database.sqlite` dibuat dan migrasi sukses via `php artisan migrate --force`.
- [x] Master data diawal via `php artisan db:seed --force`.
- [x] Akun Admin pertama dibuat via `php artisan arsipari:create-admin`.
- [x] Asset frontend dikompilasi via `npm run build`.

### C. Keamanan & Hak Akses
- [x] File permission `storage` & `bootstrap/cache` ber-hak akses `775` milik `www-data`.
- [x] Private Storage `storage/app/private/archives` terisolasi dari publik.
- [x] Vhost Nginx terkonfigurasi memblokir berkas `.env` dan `.git`.

### D. Verification Smoke Test
- [x] Login Admin, Operator, dan Viewer berhasil.
- [x] Upload dokumen PDF & penomoran arsip otomatis berjalan.
- [x] Download berkas aman dan pencarian full-text berfungsi.
- [x] Export Excel & PDF laporan berfungsi.
- [x] Backup manual & CLI restore terverifikasi.
