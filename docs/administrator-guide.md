# ARSIPARI — Panduan Administrator (Administrator Guide)

Dokumen ini berisi panduan teknis pengelolaan aplikasi untuk **Administrator Sistem MTsN 1 Magelang**.

---

## 1. Pengelolaan Pengguna (User Management)
Menu: **Pengguna** (`/users`)

- **Tambah Pengguna Baru**: Daftarkan akun baru dengan menentukan Nama, Email, Role (Admin/Operator/Viewer), dan Unit/Department.
- **Reset Password**: Admin dapat mereset kata sandi akun pengguna jika terjadi lupa password.
- **Aktivasi / Non-aktifkan Akun**: Pengguna yang sudah mutasi/pindah tugas dapat dinonaktifkan tanpa menghapus rekam jejak arsip yang pernah diunggahnya.

---

## 2. Pengelolaan Master Data Klasifikasi
Menu: **Master Data**

- **Kategori Arsip (`/categories`)**: Struktur hierarkis 2-tingkat (Induk -> Subkategori) sesuai dengan kode klasifikasi arsip madrasah.
- **Unit / Bidang Kerja (`/departments`)**: Daftar unit kerja (Tata Usaha, Kurikulum, Keuangan, Kesiswaan, Humas, Sarpras, Perpustakaan, Laboratorium).
- **Jenis Dokumen (`/document-types`)**: Pengelompokan jenis fisik (Surat Keputusan, Surat Tugas, Sertifikat, SPJ, Laporan, Nota Dinas).
- **Kebijakan Retensi (`/retention-policies`)**: Pengaturan jangka waktu simpan dokumen (Permanen, 1 Tahun, 3 Tahun, 5 Tahun, 10 Tahun).

---

## 3. Pengelolaan Tempat Sampah & Pemulihan (Trash & Lifecycle)
Menu: **Tempat Sampah** (`/trash`)

- Arsip yang dihapus oleh Admin akan masuk ke Tempat Sampah (*Soft Delete*).
- Admin dapat memulihkan (*Restore*) arsip terhapus kembali ke status aktif.
- Opsi Hapus Permanen disembunyikan untuk menjaga keutuhan dokumen institusi.

---

## 4. Audit Trail & Audit Log UI
Menu: **Aktivitas & Sistem -> Audit Trail** (`/admin/audit-logs`)

- Seluruh aktivitas pengguna (Login, Logout, Unggah, Edit, Unduh Berkas, Pemulihan Versi, Export Laporan) dicatat secara otomatis lengkap dengan Waktu, User, IP Address, dan JSON Metadata rincian aksi.
- Klik tombol **"Metadata"** pada tabel untuk melihat payload JSON aktivitas secara mendalam.

---

## 5. Pemeliharaan & Backup Sistem
Menu: **Aktivitas & Sistem -> Backup Sistem** (`/admin/backups`)

- Buat backup manual secara berkala dengan menekan tombol **"+ Buat Backup Baru"**.
- Unduh berkas ZIP backup dan simpan pada media eksternal aman.
- Pantau kesehatan server melalui menu **Kesehatan Sistem** (`/admin/system`).
