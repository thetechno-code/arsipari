<?php

namespace Database\Seeders;

use App\Models\RetentionPolicy;
use Illuminate\Database\Seeder;

class RetentionPolicySeeder extends Seeder
{
    /**
     * Seed initial retention policies.
     */
    public function run(): void
    {
        $policies = [
            [
                'name'           => 'Permanen',
                'duration_years' => null,
                'is_permanent'   => true,
                'description'    => 'Dokumen disimpan selamanya secara permanen (Dokumen penting sekolah/SK/ijazah/aset).',
            ],
            [
                'name'           => '1 Tahun',
                'duration_years' => 1,
                'is_permanent'   => false,
                'description'    => 'Masa simpan 1 tahun sejak tanggal dokumen (Dokumen operasional harian/surat biasa).',
            ],
            [
                'name'           => '3 Tahun',
                'duration_years' => 3,
                'is_permanent'   => false,
                'description'    => 'Masa simpan 3 tahun sejak tanggal dokumen (Laporan kegiatan berkala).',
            ],
            [
                'name'           => '5 Tahun',
                'duration_years' => 5,
                'is_permanent'   => false,
                'description'    => 'Masa simpan 5 tahun sejak tanggal dokumen (Dokumen pertanggungjawaban keuangan/SPJ).',
            ],
            [
                'name'           => '10 Tahun',
                'duration_years' => 10,
                'is_permanent'   => false,
                'description'    => 'Masa simpan 10 tahun sejak tanggal dokumen (Dokumen kepegawaian/inventaris).',
            ],
        ];

        foreach ($policies as $data) {
            RetentionPolicy::firstOrCreate(
                ['name' => $data['name']],
                $data
            );
        }
    }
}
