<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $structure = [
            [
                'code'        => 'ADM',
                'name'        => 'Administrasi',
                'description' => 'Kategori dokumen administrasi umum dan kelembagaan',
                'children'    => [
                    ['code' => 'ADM-SK', 'name' => 'Surat Keputusan', 'description' => 'Dokumen Surat Keputusan (SK)'],
                    ['code' => 'ADM-SE', 'name' => 'Surat Edaran', 'description' => 'Dokumen Surat Edaran resmi'],
                    ['code' => 'ADM-BA', 'name' => 'Berita Acara', 'description' => 'Dokumen Berita Acara kegiatan'],
                ],
            ],
            [
                'code'        => 'KEU',
                'name'        => 'Keuangan',
                'description' => 'Kategori dokumen pelaporan dan transaksi keuangan',
                'children'    => [
                    ['code' => 'KEU-LAP', 'name' => 'Laporan Keuangan', 'description' => 'Laporan keuangan bulanan/tahunan'],
                    ['code' => 'KEU-SPJ', 'name' => 'SPJ', 'description' => 'Surat Pertanggungjawaban belanja'],
                    ['code' => 'KEU-BKT', 'name' => 'Bukti Pembayaran', 'description' => 'Kwitansi, nota, dan bukti bayar'],
                ],
            ],
            [
                'code'        => 'PEG',
                'name'        => 'Kepegawaian',
                'description' => 'Kategori dokumen berkas guru dan staf kependidikan',
                'children'    => [
                    ['code' => 'PEG-SK', 'name' => 'SK Kepegawaian', 'description' => 'SK Pengangkatan, kenaikan pangkat/berkala'],
                    ['code' => 'PEG-DAT', 'name' => 'Data Kepegawaian', 'description' => 'Biodata, sertifikat, dan berkas GTK'],
                ],
            ],
            [
                'code'        => 'KUR',
                'name'        => 'Kurikulum',
                'description' => 'Kategori dokumen akademik dan pembelajaran',
                'children'    => [
                    ['code' => 'KUR-DOC', 'name' => 'Dokumen Kurikulum', 'description' => 'KSP, K13, silabus, RPP/Modul Ajar'],
                    ['code' => 'KUR-JDW', 'name' => 'Jadwal', 'description' => 'Jadwal pelajaran, ujian, dan kalender pendidikan'],
                    ['code' => 'KUR-EVL', 'name' => 'Evaluasi', 'description' => 'Hasil asesmen, nilai, dan analisis evaluasi'],
                ],
            ],
            [
                'code'        => 'KSW',
                'name'        => 'Kesiswaan',
                'description' => 'Kategori dokumen kegiatan dan data siswa',
                'children'    => [
                    ['code' => 'KSW-DAT', 'name' => 'Data Siswa', 'description' => 'Data induk, buku klaper, berkas siswa'],
                    ['code' => 'KSW-KGT', 'name' => 'Kegiatan Siswa', 'description' => 'Laporan ekstrakurikuler, lomba, OSIM'],
                ],
            ],
            [
                'code'        => 'SAR',
                'name'        => 'Sarana Prasarana',
                'description' => 'Kategori dokumen aset dan pemeliharaan gedung/barang',
                'children'    => [
                    ['code' => 'SAR-INV', 'name' => 'Inventaris', 'description' => 'Buku inventarisasi barang dan gedung'],
                    ['code' => 'SAR-HAR', 'name' => 'Pemeliharaan', 'description' => 'Laporan perbaikan dan perawatan sarpras'],
                ],
            ],
            [
                'code'        => 'UMUM',
                'name'        => 'Umum',
                'description' => 'Dokumen umum dan arsip serbaguna',
                'children'    => [],
            ],
        ];

        foreach ($structure as $item) {
            $parent = Category::firstOrCreate(
                ['code' => $item['code']],
                [
                    'name'        => $item['name'],
                    'description' => $item['description'],
                    'is_active'   => true,
                    'parent_id'   => null,
                ]
            );

            foreach ($item['children'] as $child) {
                Category::firstOrCreate(
                    ['code' => $child['code']],
                    [
                        'name'        => $child['name'],
                        'description' => $child['description'],
                        'is_active'   => true,
                        'parent_id'   => $parent->id,
                    ]
                );
            }
        }
    }
}
