<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'      => ['required', 'string', 'min:8', 'confirmed'],
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
            'email.unique'         => 'Alamat email sudah terdaftar.',
            'password.required'    => 'Kata sandi wajib diisi.',
            'password.min'         => 'Kata sandi minimal 8 karakter.',
            'password.confirmed'   => 'Konfirmasi kata sandi tidak cocok.',
            'role.required'        => 'Role pengguna wajib dipilih.',
            'role.in'              => 'Pilihan role tidak valid.',
            'department_id.exists' => 'Pilihan unit/bidang tidak valid.',
        ];
    }
}
