<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id ?? $this->route('user');

        return [
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $userId],
            'role'          => ['required', 'string', 'in:' . implode(',', UserRole::values())],
            'department_id' => ['nullable', 'exists:departments,id'],
            'is_active'     => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'        => 'Nama lengkap wajib diisi.',
            'email.required'       => 'Alamat email wajib diisi.',
            'email.email'          => 'Format alamat email tidak valid.',
            'email.unique'         => 'Alamat email sudah terdaftar pada pengguna lain.',
            'role.required'        => 'Role pengguna wajib dipilih.',
            'role.in'              => 'Pilihan role tidak valid.',
            'department_id.exists' => 'Pilihan unit/bidang tidak valid.',
        ];
    }
}
