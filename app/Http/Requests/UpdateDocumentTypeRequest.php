<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $typeId = $this->route('document_type')?->id ?? $this->route('document_type');

        return [
            'name'        => ['required', 'string', 'max:100'],
            'code'        => ['required', 'string', 'max:30', 'unique:document_types,code,' . $typeId],
            'description' => ['nullable', 'string'],
            'is_active'   => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama jenis dokumen wajib diisi.',
            'name.max'      => 'Nama jenis dokumen maksimal 100 karakter.',
            'code.required' => 'Kode jenis dokumen wajib diisi.',
            'code.max'      => 'Kode jenis dokumen maksimal 30 karakter.',
            'code.unique'   => 'Kode jenis dokumen sudah digunakan oleh jenis lain.',
        ];
    }
}
