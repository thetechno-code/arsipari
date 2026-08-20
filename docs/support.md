# ARSIPARI — Panduan Dukungan & Pemeliharaan (Support & Maintenance Guide)

Dokumen ini memuat jadwal pemeliharaan berkala dan kontak dukungan teknis aplikasi **ARSIPARI**.

---

## 1. Jadwal Pemeliharaan Rutin (Routine Maintenance Schedule)

| Periode | Aktivitas Pemeliharaan | Pelaksana |
|---|---|---|
| **Harian** | Memantau log kesalahan sistem di `storage/logs/laravel.log` | Tim IT / Admin |
| **Mingguan** | Memeriksa ketersediaan berkas backup ZIP di `/admin/backups` dan menyalin ke NAS/Harddisk Eksternal | Admin Sekolah |
| **Bulanan** | Memeriksa penggunaan kapasitas memori penyimpanan disk server dan menguji pemulihan (*Restore Test*) pada staging | Admin Sekolah |
| **Tahunan** | Melakukan peninjauan kebijakan retensi arsip yang jatuh tempo dan pembaruan versi PHP/Laravel jika diperlukan | Tim IT / Developer |

---

## 2. Informasi Rilis & Dukungan Teknis

- **Aplikasi**: ARSIPARI (Sistem Manajemen Arsip Digital)
- **Versi Rilis**: 1.0.0 (Production Ready)
- **Klien**: MTsN 1 Magelang
- **Lisensi**: Hak Cipta Penggunaan Internal MTsN 1 Magelang

### Kontak Tim Penanggung Jawab Teknisi:
- **Pengembang**: Senior Software Architect & Laravel Engineering Team
- **Administrator Internal Sekolah**: Tim IT MTsN 1 Magelang
