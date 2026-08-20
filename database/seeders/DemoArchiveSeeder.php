<?php

namespace Database\Seeders;

use App\Models\Archive;
use App\Models\Category;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoArchiveSeeder extends Seeder
{
    public function run(): void
    {
        $admin    = User::where('email', 'admin@arsipari.local')->first();
        $skCategory = Category::where('code', 'ADM-SK')->first();
        $tuDept   = Department::where('code', 'TU')->first();

        if ($admin && $skCategory && $tuDept) {
            Archive::firstOrCreate(
                ['archive_number' => 'ARSIP-2026-000001'],
                [
                    'document_number'   => '800/001/MTsN/2026',
                    'title'             => 'SK Pembagian Tugas Guru & Pegawai Semester Genap 2025/2026',
                    'description'       => 'Surat Keputusan Kepala MTsN 1 Magelang tentang Pembagian Tugas Mengajar dan Tugas Tambahan.',
                    'category_id'       => $skCategory->id,
                    'department_id'     => $tuDept->id,
                    'year'              => 2026,
                    'document_date'     => '2026-01-05',
                    'document_type'     => 'pdf',
                    'keywords'          => 'sk, pembagian tugas, guru, pegawai, 2026',
                    'original_filename' => 'SK_Pembagian_Tugas_2026.pdf',
                    'stored_filename'   => 'demo_sk_pembagian_tugas_2026.pdf',
                    'file_path'         => 'archives/demo_sk_pembagian_tugas_2026.pdf',
                    'mime_type'         => 'application/pdf',
                    'file_size'         => 1048576, // 1 MB
                    'uploaded_by'       => $admin->id,
                ]
            );
        }
    }
}
