# ARSIPARI — Panduan Deployment & Arsitektur Produksi

Dokumen ini menjelaskan arsitektur deployment, rekomendasi infrastruktur, hardening keamanan, dan otomasi pemeliharaan aplikasi **ARSIPARI** di MTsN 1 Magelang.

---

## 1. Topologi Arsitektur Jaringan LAN (Offline Network)

ARSIPARI didesain khusus untuk beroperasi secara mandiri (*standalone*) dalam jaringan lokal (LAN) sekolah tanpa ketergantungan pada internet atau cloud service eksternal.

```text
              [ Laptop / PC Pengguna ]
                         │ (Wi-Fi / Kabel LAN)
                         ▼
              [ Router / Switch Sekolah ]
                         │
                         ▼
     ┌──────────────────────────────────────┐
     │   Server Internal MTsN 1 Magelang    │
     │  ┌────────────────────────────────┐  │
     │  │ Nginx Web Server (Port 80/443) │  │
     │  └───────────────┬────────────────┘  │
     │                  │                   │
     │  ┌───────────────▼────────────────┐  │
     │  │ PHP 8.3-FPM (Laravel 12 Engine)│  │
     │  └───────┬───────────────┬────────┘  │
     │          │               │           │
     │  ┌───────▼───────┐ ┌─────▼─────────┐ │
     │  │ SQLite DB File│ │Private Storage│ │
     │  └───────────────┘ └───────────────┘ │
     └──────────────────────────────────────┘
```

---

## 2. Praktik Deployment Aplikasi (Deployment Script)

Saat melakukan pembaharuan source code di server produksi, gunakan script deployment otomatis `scripts/deploy.sh`:

```bash
#!/bin/bash
set -e

echo "=== MEMULAI DEPLOYMENT ARSIPARI ==="

# 1. Tarik perubahan code terbaru
git pull origin main

# 2. Update dependensi tanpa paket dev
composer install --no-dev --optimize-autoloader

# 3. Jalankan migrasi skema database secara aman (TANPA RESET DATA)
php artisan migrate --force

# 4. Rebuild asset frontend
npm install
npm run build

# 5. Reset & Re-cache konfigurasi produksi
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=== DEPLOYMENT SELESAI DENGAN SUKSES ==="
```

---

## 3. Hardening Keamanan Produksi (Security Hardening)

1. **`APP_DEBUG=false`**: Pastikan opsi debug selalu dinonaktifkan agar stack trace sensitif tidak pernah terekspos ke layar pengguna.
2. **Private File Storage**: Berkas fisik arsip berada di `storage/app/private/archives` dan **TIDAK PERNAH** disimbolik-link (*symlink*) ke folder `public/`. Pengunduhan wajib melalui pengontrol otorisasi Laravel.
3. **Nginx Security Rules**: Blokir akses ke direktori tersembunyi (`.env`, `.git`, `composer.json`, `database/`, `storage/`).
4. **Proteksi Path Traversal**: Fitur download dan backup menggunakan sanitasi nama berkas guna mencegah manipulasi path (`../`).

---

## 4. Jadwal Cron Backup Otomatis (Optional Scheduling)

Tambahkan perintah Laravel Schedule ke Cron server Linux (`crontab -e -u www-data`):

```cron
* * * * * cd /var/www/arsipari && php artisan schedule:run >> /dev/null 2>&1
```

Sistem akan secara otomatis menjalankan backup harian pada jam 02:00 pagi WIB.
