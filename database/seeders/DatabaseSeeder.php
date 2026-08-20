<?php

namespace Database\Seeders;

use App\Models\Archive;
use App\Models\ArchiveVersion;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            CategorySeeder::class,
            DocumentTypeSeeder::class,
            RetentionPolicySeeder::class,
            UserSeeder::class,
            DemoArchiveSeeder::class,
        ]);

        // Existing Archives Data Migration: ensure all archives have at least ArchiveVersion v1
        $archivesWithoutVersions = Archive::whereDoesntHave('versions')->get();
        foreach ($archivesWithoutVersions as $archive) {
            ArchiveVersion::create([
                'archive_id'        => $archive->id,
                'version_number'    => 1,
                'original_filename' => $archive->original_filename,
                'stored_filename'   => $archive->stored_filename,
                'file_path'         => $archive->file_path,
                'mime_type'         => $archive->mime_type,
                'file_size'         => $archive->file_size,
                'change_note'       => 'Dokumen awal diunggah',
                'uploaded_by'       => $archive->uploaded_by,
            ]);
        }

        $this->command->info('✅ ARSIPARI Database Seeding & Version Migration Completed Successfully:');
        $this->command->table(
            ['Email', 'Role', 'Department'],
            [
                ['admin@arsipari.local',    'admin',    'Tata Usaha'],
                ['operator@arsipari.local', 'operator', 'Kurikulum'],
                ['viewer@arsipari.local',   'viewer',   'Umum'],
            ]
        );
    }
}
