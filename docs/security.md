# ARSIPARI — Arsitektur Keamanan System (Security Architecture & Hardening)

Dokumen ini menjelaskan kontrol dan lapisan keamanan yang diterapkan pada aplikasi **ARSIPARI** untuk melindungi integritas data arsip madrasah.

---

## 1. Lapisan Keamanan (Security Layers)

```text
┌─────────────────────────────────────────────────────────────┐
│ 1. Nginx Server Level: Block Hidden Files & Direct Access  │
├─────────────────────────────────────────────────────────────┤
│ 2. Laravel Middleware: Rate Limiting & CSRF Protection      │
├─────────────────────────────────────────────────────────────┤
│ 3. Authentication & RBAC Policy: Auth check & User Roles    │
├─────────────────────────────────────────────────────────────┤
│ 4. Private Storage Isolation: Non-public File Pathing       │
├─────────────────────────────────────────────────────────────┤
│ 5. Data Sanitization & Protection: SQL/XSS/IDOR Defense     │
├─────────────────────────────────────────────────────────────┤
│ 6. Audit Trail Logging: Immutable Append-Only Logs          │
└─────────────────────────────────────────────────────────────┘
```

---

## 2. Rincian Hardening Keamanan

### A. Proteksi Pengunggahan Berkas (Upload Security)
- **Sanitasi File Storage Name**: Nama file yang diunggah dikonversi menjadi nama acak ULID unik. Nama file asli dari komputer pengguna **TIDAK PERNAH** dijadikan path penyimpanan filesystem untuk mencegah kerentanan pengungkapan path.
- **Isolasi Private Storage**: Seluruh berkas fisik disimpan di folder non-publik (`storage/app/private/archives`). Folder ini **TIDAK** dibuka ke direktori public web root Nginx. Pengaksesan berkas wajib melalui pengontrol otorisasi Laravel.
- **Validasi Ekstensi & MIME Type**: Sistem secara ketat memeriksa ekstensi berkas yang diperbolehkan (PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG, ZIP). Ekstensi berkas yang berpotensi mengeksekusi skrip (`.php`, `.sh`, `.exe`, `.bat`, `.py`) ditolak secara absolut.

### B. Proteksi Jalur Pengunduhan & Path Traversal
- **Pemeriksaan Otorisasi IDOR**: Pengunduhan versi berkas memverifikasi bahwa ID versi cocok dengan ID arsip (`version.archive_id === archive.id`) dan pengguna yang login memiliki hak baca.
- **Sanitasi Path Traversal**: Pengontrol pengunduhan berkas dan manajemen backup menggunakan fungsi `basename()` dan sanitasi string untuk memblokir karakter manipulasi direktori seperti `../` atau `..\`.

### C. Proteksi Autentikasi & Sesi
- **Rate Limiting Login**: Form login membatasi maksimal 5 kali percobaan gagal per menit per alamat email + IP Address untuk mencegah *Brute-force Attack*.
- **Password Hashing**: Kata sandi disimpan menggunakan algoritma Hashing Bcrypt terenskripsi kuat.
- **Regenerasi Sesi**: Sesi pengguna di-regenerasi secara otomatis saat login dan ditutup penuh saat logout.

### D. Keamanan Data & Query
- **SQL Injection Defense**: Seluruh query data dibangun menggunakan Eloquent ORM atau Query Builder terparameter tanpa merangkai query SQL mentah (*raw string concatenation*).
- **XSS Escaping**: Semua keluaran variabel pada Blade template menggunakan escaping bawaan `{{ }}`.
- **Mass Assignment Defense**: Seluruh model Eloquent menetapkan daftar `$fillable` yang diizinkan saja.
- **Integritas Audit Log**: Log aktivitas bersifat *append-only* (hanya bisa ditambahkan) dan tidak dapat diubah atau dihapus melalui UI oleh pengguna biasa.
