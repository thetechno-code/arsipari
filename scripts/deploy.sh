#!/usr/bin/env bash
# ==============================================================================
# ARSIPARI Automated Production Deployment Script
# MTsN 1 Magelang
# ==============================================================================

set -e

echo "--------------------------------------------------"
echo "  ARSIPARI - OTOMASI DEPLOYMENT PRODUKSI          "
echo "--------------------------------------------------"
echo "Waktu: $(date)"

# 1. Update source code
echo "[1/6] Menarik perubahan code dari repository..."
git pull origin main

# 2. Install composer dependencies without dev packages
echo "[2/6] Memasang dependensi Composer (production mode)..."
composer install --no-dev --optimize-autoloader --no-interaction

# 3. Database migrations (SAFE - No data destruction)
echo "[3/6] Menjalankan migrasi skema database..."
php artisan migrate --force

# 4. Rebuild frontend assets
echo "[4/6] Mengompilasi asset frontend..."
npm install
npm run build

# 5. Clear and recreate production caches
echo "[5/6] Memperbarui cache konfigurasi, rute, dan view..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Restart PHP-FPM if available
echo "[6/6] Menyeleksi layanan PHP-FPM..."
if command -v systemctl >/dev/null 2>&1; then
    sudo systemctl reload php8.3-fpm || true
fi

echo "--------------------------------------------------"
echo "🎉 DEPLOYMENT ARSIPARI SELESAI DENGAN SUKSES!"
echo "--------------------------------------------------"
