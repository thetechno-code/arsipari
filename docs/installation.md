# ARSIPARI — Panduan Instalasi (Installation Guide)

Dokumen ini berisi panduan langkah demi langkah untuk melakukan instalasi aplikasi **ARSIPARI (Sistem Manajemen Arsip Digital)** pada server internal **MTsN 1 Magelang**.

---

## 1. Persyaratan Server (Server Requirements)

### Spesifikasi Minimum Hardware:
* **CPU**: 2 Core vCPU
* **RAM**: 2 GB
* **Disk**: 20 GB SSD/NVMe (tergantung volume berkas arsip)
* **OS**: Ubuntu Server 22.04 LTS atau 24.04 LTS

### Software Stack Required:
* **PHP**: 8.2 atau 8.3+ (beserta modul `php-cli`, `php-fpm`, `php-sqlite3`, `php-mbstring`, `php-zip`, `php-xml`, `php-fileinfo`, `php-gd`)
* **Web Server**: Nginx
* **Database**: SQLite3
* **Composer**: 2.x+
* **Node.js & npm**: Node.js 18+ (hanya untuk build asset frontend)

---

## 2. Langkah-Langkah Instalasi Clean (Fresh Installation)

### Langkah 1: Clone / Upload Source Code
Upload atau clone source code aplikasi ke directory server (misalnya `/var/www/arsipari`):

```bash
cd /var/www
git clone https://github.com/mtsn1magelang/arsipari.git arsipari
cd /var/www/arsipari
```

### Langkah 2: Install Dependensi PHP
Jalankan composer install tanpa dependensi development (`--no-dev`):

```bash
composer install --no-dev --optimize-autoloader
```

### Langkah 3: Konfigurasi File Lingkungan (`.env`)
Salin file contoh konfigurasi `.env.example` menjadi `.env`:

```bash
cp .env.example .env
php artisan key:generate
```

Sesuaikan variabel di `.env`:

```env
APP_NAME=ARSIPARI
APP_ENV=production
APP_DEBUG=false
APP_URL=http://192.168.1.100 # Sesuaikan IP LAN / Domain Sekolah

DB_CONNECTION=sqlite
DB_DATABASE=/var/www/arsipari/database/database.sqlite

ARSIPARI_BACKUP_RETENTION=7
ARSIPARI_PDF_MAX_ROWS=5000
ARSIPARI_RETENTION_WARNING_DAYS=90
```

### Langkah 4: Inisialisasi Database SQLite
Buat file database SQLite kosong jika belum ada:

```bash
touch database/database.sqlite
```

Jalankan migrasi database dan seeder data awal:

```bash
php artisan migrate --force
php artisan db:seed --class=DatabaseSeeder --force
```

### Langkah 5: Buat Akun Administrator Pertama
Jalankan perintah interaktif CLI untuk membuat akun Admin sekolah:

```bash
php artisan arsipari:create-admin
```

Masukkan nama, email, password (minimal 8 karakter), dan unit kerja Admin.

### Langkah 6: Kompilasi Assets Frontend
Kompilasi asset Tailwind CSS & Alpine JS untuk lingkungan produksi:

```bash
npm install
npm run build
```

### Langkah 7: Konfigurasi Hak Akses Direktori (Permissions)
Beri hak akses simpan kepada user web server Nginx (`www-data`):

```bash
chown -R www-data:www-data /var/www/arsipari
chmod -R 755 /var/www/arsipari
chmod -R 775 /var/www/arsipari/storage
chmod -R 775 /var/www/arsipari/bootstrap/cache
chmod -R 775 /var/www/arsipari/database
```

### Langkah 8: Cache Konfigurasi & Rute
Jalankan perintah optimasi produksi Laravel:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 3. Konfigurasi Nginx Web Server

Buat file vhost Nginx baru di `/etc/nginx/sites-available/arsipari`:

```nginx
server {
    listen 80;
    server_name arsipari.local 192.168.1.100;
    root /var/www/arsipari/public;

    index index.php;

    charset utf-8;

    # Maximum Upload File Size Limit (Matching 20MB limit)
    client_max_body_size 25M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Block access to hidden files (.env, .git, etc)
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Aktifkan konfigurasi dan reload Nginx:

```bash
ln -s /etc/nginx/sites-available/arsipari /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

---

## 4. Pengujian Akses Pertama (Smoke Test)

1. Buka peramban (browser) di komputer LAN: `http://192.168.1.100` atau `http://arsipari.local`.
2. Login menggunakan akun Administrator yang telah dibuat pada Langkah 5.
3. Masuk ke halaman **Kesehatan Sistem** (`/admin/system`) dan pastikan seluruh indikator berwarna hijau (Normal).
