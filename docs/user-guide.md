# ARSIPARI — Panduan Pengguna (User Guide)

Selamat datang di **ARSIPARI (Sistem Manajemen Arsip Digital MTsN 1 Magelang)**. Dokumen ini adalah panduan penggunaan aplikasi untuk seluruh staf dan pengguna sekolah.

---

## 1. Hak Akses & Peran Pengguna (Roles)

1. **Administrator (Admin)**: Memiliki hak akses penuh untuk mengelola pengguna, master data, arsip, tempat sampah, audit trail, laporan, dan backup sistem.
2. **Operator (Petugas Arsip)**: Berhak mengunggah arsip baru, memperbarui metadata, mengunggah revisi versi dokumen, serta mengunduh dan mencetak laporan.
3. **Viewer (Pembaca)**: Berhak melakukan pencarian, melihat detail metadata, membaca riwayat arsip, dan mengunduh berkas dokumen (Read Only).

---

## 2. Fitur & Cara Penggunaan

### A. Pencarian Arsip Cepat (Archive Search)
1. Akses menu **Semua Arsip** atau gunakan bilah pencarian global pada Dashboard.
2. Masukkan kata kunci (Nomor Arsip, Nomor Dokumen, Judul, Uraian Isi, atau Keyword).
3. Gunakan **Advanced Filter** untuk mempersempit pencarian berdasarkan Kategori, Unit Kerja, Jenis Dokumen, Status, dan Rentang Tanggal.

### B. Mengunggah Arsip Baru (Tambah Arsip)
*(Khusus Admin & Operator)*
1. Klik menu **Tambah Arsip** pada sidebar.
2. Isi formulir **Informasi Utama Dokumen** (Judul, Nomor Dokumen, Tanggal Dokumen, dan Tahun).
3. Pilih **Kategori Arsip**, **Unit / Bidang Kerja**, **Jenis Dokumen**, dan **Kebijakan Retensi**.
4. Upload berkas dokumen (Format: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG, ZIP, Maksimal 20 MB).
5. Klik **Simpan Arsip**. Sistem akan menggenerasi Nomor Arsip unik otomatis (misal: `ARSIP-2026-000123`).

### C. Mengunggah Revisi & Melihat Riwayat Versi
*(Khusus Admin & Operator)*
1. Buka detail arsip yang ingin diperbarui.
2. Klik tombol **"+ Versi Baru"** pada header detail atau bagian Riwayat Versi.
3. Pilih file baru dan tuliskan **Catatan Perubahan** (contoh: *"Revisi halaman 2 tanda tangan kepala madrasah"*).
4. Berkas lama tetap tersimpan aman sebagai versi sebelumnya (`v1`, `v2`, dll) dan dapat diunduh sewaktu-waktu.

### D. Membuat & Mencetak Laporan Arsip
1. Buka menu **Laporan Arsip** (`/reports/archives`).
2. Tentukan kriteria filter laporan (misal: Periode Tanggal 01/01/2026 s/d 31/12/2026 dan Unit "Tata Usaha").
3. Klik tombol **Tampilkan Laporan** untuk melihat pratinjau data.
4. Klik **Export Excel** untuk mengunduh rekap `.xlsx` atau **Export PDF** untuk mengunduh dokumen `.pdf` siap cetak.
