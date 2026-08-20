<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:100'],
            'code'        => ['required', 'string', 'max:30', 'unique:categories,code'],
            'parent_id'   => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Nama kategori wajib diisi.',
            'name.max'          => 'Nama kategori maksimal 100 karakter.',
            'code.required'     => 'Kode kategori wajib diisi.',
            'code.max'          => 'Kode kategori maksimal 30 karakter.',
            'code.unique'       => 'Kode kategori sudah digunakan.',
            'parent_id.exists'  => 'Pilihan kategori induk tidak ditemukan.',
        ];
    }
}
