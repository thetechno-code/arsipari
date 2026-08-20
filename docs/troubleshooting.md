# ARSIPARI — Panduan Troubleshooting (Pemecahan Masalah)

Dokumen ini berisi solusi teknis ringkas untuk menangani kendala operasional umum pada server **ARSIPARI**.

---

## 1. Masalah Umum & Solusi Teknis

### A. HTTP 500 Internal Server Error
* **Penyebab**: Kegagalan pada konfigurasi PHP, hak akses direktori, atau kesalahan skrip.
* **Solusi**:
  1. Periksa log kesalahan Laravel: `tail -n 100 /var/www/arsipari/storage/logs/laravel.log`.
  2. Pastikan file `.env` ada dan `APP_KEY` tergenerasi (`php artisan key:generate`).
  3. Pastikan permission direktori `storage` dan `bootstrap/cache` ber-hak akses `775` milik `www-data`.

### B. SQLite Database Locked (`Database is locked`)
* **Penyebab**: Beberapa proses bersamaan mencoba menulis ke file SQLite atau transaksi long-running yang belum diselesaikan.
* **Solusi**:
  1. Pastikan folder `database/` dan file `database.sqlite` dapat ditulis oleh Nginx/PHP-FPM (`chown www-data:www-data database/database.sqlite`).
  2. Restart PHP-FPM service: `sudo systemctl restart php8.3-fpm`.

### C. Gagal Unggah Berkas (*File Upload Failed / 413 Payload Too Large*)
* **Penyebab**: Ukuran berkas melebihi batas yang diizinkan oleh Nginx atau PHP.
* **Solusi**:
  1. Periksa konfigurasi Nginx `/etc/nginx/sites-available/arsipari`: tambahkan `client_max_body_size 25M;`.
  2. Periksa `php.ini` (`/etc/php/8.3/fpm/php.ini`):
     ```ini
     upload_max_filesize = 25M
     post_max_size = 25M
     ```
  3. Reload Nginx & PHP-FPM: `sudo systemctl reload nginx php8.3-fpm`.

### D. Export PDF Gagal (*PDF Export Exceeded Max Rows Limit*)
* **Penyebab**: Hasil filter laporan melebihi batas maksimal penanganan PDF (`ARSIPARI_PDF_MAX_ROWS=5000`).
* **Solusi**:
  1. Penyajian PDF pada ribuan baris disarankan dialihkan menggunakan **Export Excel** (`.xlsx`) yang didesain untuk dataset besar.
  2. Persempit rentang tanggal atau filter laporan jika tetap memerlukan format PDF.

### E. Halaman Kosong / Tampilan Rusak (CSS & JS Tidak Loading)
* **Penyebab**: Asset Vite belum dikompilasi atau cache konfigurasi lama masih tersimpan.
* **Solusi**:
  1. Bersihkan cache aplikasi: `php artisan optimize:clear`.
  2. Rebuild asset frontend: `npm run build`.
  3. Buat cache ulang: `php artisan config:cache && php artisan route:cache && php artisan view:cache`.
