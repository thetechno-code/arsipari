# ARSIPARI — Lembar Cek Deployment (Deployment Checklist)

Gunakan lembar cek ini saat hendak melakukan rilis atau instalasi aplikasi ARSIPARI di lingkungan server produksi sekolah.

---

## 1. Persiapan Server
- [ ] OS Ubuntu Server 22.04 LTS / 24.04 LTS siap dan up-to-date.
- [ ] Engine PHP 8.2+ terinstal beserta modul: `php-cli`, `php-fpm`, `php-sqlite3`, `php-mbstring`, `php-zip`, `php-xml`, `php-fileinfo`, `php-gd`.
- [ ] Nginx Web Server terinstal dan berjalan (`systemctl status nginx`).
- [ ] Composer 2.x+ terinstal.

## 2. Pengaturan Aplikasi & Basis Data
- [ ] Source code ditempatkan pada direktori produksi (misal `/var/www/arsipari`).
- [ ] Dependensi terinstal via `composer install --no-dev --optimize-autoloader`.
- [ ] File `.env` dibuat dengan `APP_ENV=production` dan `APP_DEBUG=false`.
- [ ] Kunci aplikasi tergenerasi via `php artisan key:generate`.
- [ ] File `database/database.sqlite` dibuat dan migrasi sukses via `php artisan migrate --force`.
- [ ] Seeder data awal terisi via `php artisan db:seed --force`.
- [ ] Akun Administrator dibuat via `php artisan arsipari:create-admin`.
- [ ] Asset frontend dikompilasi via `npm run build`.

## 3. Hak Akses & Keamanan Direktori
- [ ] Ownership file diset ke `www-data:www-data`.
- [ ] Opsi permission direktori `storage` & `bootstrap/cache` ber-hak akses `775`.
- [ ] Opsi Private File Storage `storage/app/private/archives` terlindungi dari akses web publik direct.
- [ ] Berkas sensitif (`.env`, `.git`) terblokir dari Nginx.

## 4. Pengujian Fungsi Inti (Production Smoke Test)
- [ ] Halaman Login terbuka dan autentikasi Admin berhasil.
- [ ] Pengunggahan arsip digital + berkas fisik PDF sukses.
- [ ] Pengunduhan aman (*Secure Download*) berkas berjalan lancar.
- [ ] Pencarian & Advanced Filter berfungsi normal.
- [ ] Pengunggahan versi baru (Versioning) membentuk `v2` secara sukses.
- [ ] Pembuatan Laporan Rekapitulasi + Export Excel (`.xlsx`) & PDF (`.pdf`) berfungsi.
- [ ] Pembuatan Backup manual via Web UI & CLI (`php artisan arsipari:backup`) sukses.
- [ ] Akses halaman Kesehatan Sistem (`/admin/system`) menampilkan status Normal.
