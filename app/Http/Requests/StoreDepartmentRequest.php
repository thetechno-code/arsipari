<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:100'],
            'code'        => ['required', 'string', 'max:30', 'unique:departments,code'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama unit/bidang wajib diisi.',
            'name.max'      => 'Nama unit/bidang maksimal 100 karakter.',
            'code.required' => 'Kode unit/bidang wajib diisi.',
            'code.max'      => 'Kode unit/bidang maksimal 30 karakter.',
            'code.unique'   => 'Kode unit/bidang sudah digunakan.',
        ];
    }
}
