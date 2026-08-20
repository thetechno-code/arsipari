# ARSIPARI — Panduan Backup & Pemulihan Data (Backup & Restore)

Dokumen ini menjelaskan prosedur pencadangan data (*Backup*) dan pemulihan bencana (*Disaster Recovery / Restore*) untuk aplikasi **ARSIPARI**.

---

## 1. Komponen Paket Backup

Paket backup ARSIPARI disimpan dalam format file kompresi ZIP (`arsipari-backup-YYYY-MM-DD_HHMMSS.zip`) di direktori `storage/app/backups/`. Setiap paket backup mencakup:

1. **`database.sqlite`**: Salinan utuh basis data SQLite internal.
2. **`archives/`**: Seluruh versi berkas dokumen digital yang tersimpan pada Private Storage.
3. **`manifest.json`**: Berkas metadata yang memuat versi aplikasi, waktu pencadangan, jumlah berkas, dan SHA-256 Checksum untuk verifikasi integritas data.

---

## 2. Cara Menjalankan Backup Manual

### Opsi A: Melalui Web Interface (Admin Only)
1. Login sebagai **Admin**.
2. Buka menu **Aktivitas & Sistem -> Backup Sistem** (`/admin/backups`).
3. Klik tombol **"+ Buat Backup Baru"**.
4. Paket ZIP baru akan dibuat dan langsung dapat diunduh (*download*).

### Opsi B: Melalui Perintah Terminal (CLI Command)
Jalankan perintah berikut pada terminal server:

```bash
php artisan arsipari:backup
```

Contoh Output:
```text
==================================================
  ARSIPARI - PROSES BACKUP SISTEM ARSIP DIGITAL   
==================================================
Waktu Mulai: 2026-08-20 14:30:00
✔ Backup Database SQLite: OK
✔ Backup Berkas Arsip Private: OK (128 Berkas)
✔ Generate Manifest JSON: OK
✔ Kompresi Paket ZIP: OK
✔ SHA-256 Checksum: 8f9b...e4a1

🎉 BACKUP SELESAI DENGAN SUKSES!
File Backup : arsipari-backup-2026-08-20_143000.zip
Lokasi      : /var/www/arsipari/storage/app/backups/arsipari-backup-2026-08-20_143000.zip
Ukuran File : 45.2 MB
```

---

## 3. Prosedur Pemulihan Data (Restore Procedure)

> [!CAUTION]
> Pemulihan data (*Restore*) akan menimpa database dan berkas arsip yang ada di server saat ini dengan data dari file backup. 

Demi keamanan data sekolah, **proses Restore tidak disediakan pada Web UI**, melainkan dijalankan secara aman melalui Terminal CLI oleh Administrator Sistem.

### Langkah-Langkah Restore:

1. Pastikan file paket backup ZIP berada di folder `storage/app/backups/`.
2. Jalankan perintah restore:

```bash
php artisan arsipari:restore
```

3. Pilih paket backup yang ingin dipulihkan dari daftar yang muncul.
4. Sistem akan secara otomatis membuat **Emergency Pre-Restore Backup** terlebih dahulu (`pre-restore-backup-YYYY-MM-DD_HHMMSS.zip`).
5. Ketik kata **`RESTORE`** saat diminta konfirmasi.
6. Sistem akan memulihkan basis data dan berkas arsip fisik secara utuh.

---

## 4. Rekomendasi Disaster Recovery Sekolah

- **Penyimpanan Offsite**: File backup yang berada pada server yang sama **bukan** penanganan bencana yang aman. Administrator Sekolah **wajib** menyalin file `.zip` backup ke penyimpanan eksternal (NAS, Harddisk Eksternal, atau PC khusus) secara berkala (minimal 1x seminggu).
- **Retensi Backup**: Sistem mempertahankan 7 file backup terbaru secara otomatis (`ARSIPARI_BACKUP_RETENTION=7`).
