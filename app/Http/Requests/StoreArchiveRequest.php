<?php

namespace App\Http\Requests;

use App\Models\Archive;
use Illuminate\Foundation\Http\FormRequest;

class StoreArchiveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Archive::class) ?? false;
    }

    public function rules(): array
    {
        $maxKb = env('ARSIPARI_MAX_FILE_SIZE_MB', 20) * 1024;

        return [
            'document_number'     => ['nullable', 'string', 'max:100'],
            'title'               => ['required', 'string', 'max:255'],
            'category_id'         => ['required', 'exists:categories,id'],
            'department_id'       => ['required', 'exists:departments,id'],
            'retention_policy_id' => ['nullable', 'exists:retention_policies,id'],
            'document_date'       => ['required', 'date'],
            'year'                => ['required', 'integer', 'min:1900', 'max:2100'],
            'document_type'       => ['required', 'string', 'max:30'],
            'keywords'            => ['nullable', 'string', 'max:500'],
            'description'         => ['nullable', 'string'],
            'file'                => [
                'required',
                'file',
                "max:{$maxKb}",
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,zip',
            ],
        ];
    }

    public function messages(): array
    {
        $maxMb = env('ARSIPARI_MAX_FILE_SIZE_MB', 20);

        return [
            'title.required'         => 'Judul arsip wajib diisi.',
            'title.max'              => 'Judul arsip maksimal 255 karakter.',
            'category_id.required'   => 'Kategori arsip wajib dipilih.',
            'category_id.exists'     => 'Kategori yang dipilih tidak valid.',
            'department_id.required' => 'Unit/bidang wajib dipilih.',
            'department_id.exists'   => 'Unit/bidang yang dipilih tidak valid.',
            'document_date.required' => 'Tanggal dokumen wajib diisi.',
            'document_date.date'     => 'Format tanggal dokumen tidak valid.',
            'year.required'          => 'Tahun arsip wajib diisi.',
            'document_type.required' => 'Jenis dokumen wajib dipilih.',
            'file.required'          => 'Berkas dokumen arsip wajib diunggah.',
            'file.file'              => 'Unggahan berkas tidak valid.',
            'file.max'               => "Ukuran berkas maksimal {$maxMb} MB.",
            'file.mimes'             => 'Format berkas tidak diizinkan. Format yang didukung: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG, ZIP.',
        ];
    }
}
