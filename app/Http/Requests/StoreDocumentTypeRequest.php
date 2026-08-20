<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:100'],
            'code'        => ['required', 'string', 'max:30', 'unique:document_types,code'],
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
            'code.unique'   => 'Kode jenis dokumen sudah digunakan.',
        ];
    }
}
