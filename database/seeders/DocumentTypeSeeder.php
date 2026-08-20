<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'PDF',   'name' => 'PDF',   'description' => 'Dokumen Portable Document Format (.pdf)'],
            ['code' => 'DOC',   'name' => 'DOC',   'description' => 'Dokumen Microsoft Word (.doc)'],
            ['code' => 'DOCX',  'name' => 'DOCX',  'description' => 'Dokumen Microsoft Word OpenXML (.docx)'],
            ['code' => 'XLS',   'name' => 'XLS',   'description' => 'Lembar Kerja Microsoft Excel (.xls)'],
            ['code' => 'XLSX',  'name' => 'XLSX',  'description' => 'Lembar Kerja Microsoft Excel OpenXML (.xlsx)'],
            ['code' => 'PPT',   'name' => 'PPT',   'description' => 'Presentasi Microsoft PowerPoint (.ppt)'],
            ['code' => 'PPTX',  'name' => 'PPTX',  'description' => 'Presentasi Microsoft PowerPoint OpenXML (.pptx)'],
            ['code' => 'IMAGE', 'name' => 'Gambar','description' => 'Berkas gambar/foto (.jpg, .jpeg, .png)'],
            ['code' => 'ZIP',   'name' => 'ZIP',   'description' => 'Berkas arsip kompresi (.zip, .rar)'],
        ];

        foreach ($types as $t) {
            DocumentType::firstOrCreate(
                ['code' => $t['code']],
                [
                    'name'        => $t['name'],
                    'description' => $t['description'],
                    'is_active'   => true,
                ]
            );
        }
    }
}
