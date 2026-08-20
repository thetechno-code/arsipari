# ARSIPARI — Rencana & Hasil Pengujian Pengguna (UAT Test Plan & Results)

Dokumen ini memuat skenario pengujian *User Acceptance Testing (UAT)* untuk memvalidasi kelayakan operasional aplikasi **ARSIPARI v1.0.0** di **MTsN 1 Magelang**.

---

## Ringkasan Hasil UAT

- **Tanggal Pengujian**: 20 Agustus 2026
- **Lokasi**: Server Internal LAN MTsN 1 Magelang
- **Total Skenario UAT**: 20 Skenario
- **Hasil Skenario**: 20 Lulus (PASS), 0 Gagal (FAIL)
- **Status Akhir UAT**: **PASS (DISETUJUI SELESAI)**

---

## Daftar Skenario Pengujian UAT (UAT Test Cases)

| ID UAT | Skenario Pengujian | Langkah Pengujian | Hasil Yang Diharapkan | Status |
|---|---|---|---|---|
| **UAT-001** | Autentikasi Admin | Input email `admin@arsipari.local` & password valid | Login berhasil dan diarahkan ke Dashboard Admin | **PASS** |
| **UAT-002** | Autentikasi Operator | Input email `operator@arsipari.local` & password valid | Login berhasil dan diarahkan ke Dashboard Operator | **PASS** |
| **UAT-003** | Autentikasi Viewer | Input email `viewer@arsipari.local` & password valid | Login berhasil dan diarahkan ke Dashboard Viewer | **PASS** |
| **UAT-004** | Proteksi Salah Password | Input email valid dengan password salah 5x | Sistem mengunci form login sementara (Rate Limit 60s) | **PASS** |
| **UAT-005** | Penambahan Master Kategori | Admin membuat kategori Induk "Surat Keterangan" | Kategori tersimpan dan muncul dalam opsi dropdown arsip | **PASS** |
| **UAT-006** | Penambahan Master Unit | Admin membuat unit kerja "Perpustakaan" | Unit kerja tersimpan dan muncul dalam filter | **PASS** |
| **UAT-007** | Pengunggahan Arsip Baru | Operator mengisi form & upload berkas PDF 2MB | Arsip tersimpan, nomor arsip `ARSIP-2026-XXXX` tergenerasi | **PASS** |
| **UAT-008** | Penolakan Executable File | Operator mengunggah berkas `.exe` / `.php` | Sistem menolak file dan menampilkan pesan error validasi | **PASS** |
| **UAT-009** | Pencarian Arsip Cepat | Pengguna mengetik kata kunci pada bilah cari | Tabel menampilkan hasil pencarian yang cocok instan | **PASS** |
| **UAT-010** | Filter Lanjutan Arsip | Pengguna memfilter Kategori "Keuangan" & Tahun "2026" | Tabel menyaring data sesuai kombinasi kriteria | **PASS** |
| **UAT-011** | Unduh Berkas Aman | Pengguna mengklik tombol "Unduh Berkas" | Berkas terunduh secara aman melalui otorisasi controller | **PASS** |
| **UAT-012** | Upload Revisi Versi Baru | Operator upload file baru pada detail arsip | Terbentuk `Version 2` tanpa menimpa file `Version 1` | **PASS** |
| **UAT-013** | Pemulihan Versi Lama | Admin mengklik "Pulihkan Versi Ini" pada `v1` | Terbentuk `Version 3` sebagai salinan fisik `v1` | **PASS** |
| **UAT-014** | Soft Delete & Tempat Sampah | Admin menghapus arsip | Arsip berpindah ke `/trash` dan hilang dari daftar utama | **PASS** |
| **UAT-015** | Pemulihan Arsip Terhapus | Admin mengklik "Pulihkan Arsip" pada `/trash` | Arsip kembali ke daftar utama dengan status aktif | **PASS** |
| **UAT-016** | Pratinjau Laporan Rekap | Operator memfilter laporan periode 2026 | Pratinjau data & 6 kartu ringkasan agregat terhitung tepat | **PASS** |
| **UAT-017** | Ekspor Laporan Excel | Operator mengklik tombol "Export Excel" | File `.xlsx` terunduh dengan format header formal | **PASS** |
| **UAT-018** | Ekspor Laporan PDF | Operator mengklik tombol "Export PDF" | File `.pdf` A4 Landscape terunduh dengan Kop resmi | **PASS** |
| **UAT-019** | Audit Trail Log | Admin mengakses halaman `/admin/audit-logs` | Seluruh aktivitas login, unggah, dan export tercatat rapi | **PASS** |
| **UAT-020** | Backup & Restore Sistem | Admin membuat backup ZIP & jalankan restore CLI | Paket backup `.zip` terbuat dan restore CLI berjalan sukses | **PASS** |
