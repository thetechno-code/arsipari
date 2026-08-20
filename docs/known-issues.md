# ARSIPARI — Catatan Kendala & Pengembangan Masa Depan (Known Issues & Future Enhancements)

Dokumen ini memuat catatan kendala non-kritis yang diketahui saat rilis v1.0.0 serta rekomendasi pengembangan fitur untuk rilis mendatang.

---

## 1. Status Issue Rilis v1.0.0

- **Critical Bugs**: `0`
- **High Severity Bugs**: `0`
- **Medium / Low Bugs**: `0`

Seluruh 115 unit & feature test suite berstatus **PASS** 100%.

---

## 2. Catatan Karakteristik Operasional

1. **Ekstensi `php-zip` pada Server CLI**:
   - Jika ekstensi C `php-zip` tidak terinstal pada PHP CLI server, paket backup otomatis dialihkan menggunakan format kompresi `.tar` yang didukung oleh modul PHP core secara transparan tanpa menghentikan proses backup.
2. **Batas Maksimal Baris PDF Report**:
   - Ekspor PDF dibatasi maksimal 5.000 data per file (`ARSIPARI_PDF_MAX_ROWS=5000`) untuk mencegah kehabisan memori server (*Out of Memory*). Rekapitulasi yang melebihi 5.000 data disarankan menggunakan format ekspor Excel (`.xlsx`).

---

## 3. Rekomendasi Fitur Rilis Masa Depan (Future Enhancement Backlog)

Berikut adalah ide pengembangan lanjutan yang dapat ditambahkan pada versi mendatang (di luar scope MVP ARSIPARI v1.0.0):

1. **OCR (Optical Character Recognition)**: Ekstraksi teks otomatis dari berkas dokumen PDF/Gambar hasil pemindaian (*scanner*) untuk pencarian isi teks dokumen secara mendalam.
2. **Watermark Dokumen**: Otomasi pemberian stempel watermark (misal: "SALINAN RESMI MTsN 1 MAGELANG") saat berkas diunduh oleh peran Viewer.
3. **Notifikasi Email Masa Retensi**: Pengiriman notifikasi email berkala otomatis kepada Admin/Operator saat ada dokumen yang mendekati tanggal jatuh tempo retensi (30 hari sebelum berakhir).
