<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [
                'code'        => 'TU',
                'name'        => 'Tata Usaha',
                'description' => 'Bagian Tata Usaha dan Administrasi Umum MTsN 1 Magelang',
                'is_active'   => true,
            ],
            [
                'code'        => 'KUR',
                'name'        => 'Kurikulum',
                'description' => 'Bidang Akademik dan Pengembangan Kurikulum',
                'is_active'   => true,
            ],
            [
                'code'        => 'KSW',
                'name'        => 'Kesiswaan',
                'description' => 'Bidang Kesiswaan dan Ekstrakurikuler',
                'is_active'   => true,
            ],
            [
                'code'        => 'KEU',
                'name'        => 'Keuangan',
                'description' => 'Pengelolaan Keuangan dan Pelaporan SPJ',
                'is_active'   => true,
            ],
            [
                'code'        => 'SARPRAS',
                'name'        => 'Sarana Prasarana',
                'description' => 'Pengelolaan Sarana, Prasarana, dan Inventaris Sekolah',
                'is_active'   => true,
            ],
            [
                'code'        => 'PEG',
                'name'        => 'Kepegawaian',
                'description' => 'Pengelolaan Data Guru dan Tenaga Kependidikan',
                'is_active'   => true,
            ],
            [
                'code'        => 'UMUM',
                'name'        => 'Umum',
                'description' => 'Unit Pelayanan dan Hubungan Masyarakat',
                'is_active'   => true,
            ],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(
                ['code' => $dept['code']],
                $dept
            );
        }
    }
}
