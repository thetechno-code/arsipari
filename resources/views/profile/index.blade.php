@extends('layouts.app')

@section('title', 'Profil Saya')
@section('page-title', 'Profil Pengguna')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600">Dashboard</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-600">Profil Saya</span>
@endsection

@section('content')
<div class="max-w-4xl space-y-6">

    {{-- ─── Section 1: Informasi Profil & Edit Nama ─── --}}
    <div class="card">
        <div class="card-header flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-gray-900">Informasi Pengguna</h3>
                <p class="text-xs text-gray-500 mt-0.5">Kelola informasi nama akun Anda</p>
            </div>
            <span class="badge-blue">{{ $user->role_label }}</span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Nama Lengkap (Editable) --}}
                    <div>
                        <label for="name" class="label">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required class="input @error('name') input-error @enderror">
                        @error('name')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email (Read-only) --}}
                    <div>
                        <label class="label">Alamat Email</label>
                        <input type="text" value="{{ $user->email }}" disabled class="input bg-gray-100 text-gray-500 cursor-not-allowed">
                        <p class="text-[11px] text-gray-400 mt-1">Alamat email hanya dapat diubah oleh Administrator.</p>
                    </div>

                    {{-- Role (Read-only) --}}
                    <div>
                        <label class="label">Role / Peran</label>
                        <input type="text" value="{{ $user->role_label }}" disabled class="input bg-gray-100 text-gray-500 cursor-not-allowed">
                    </div>

                    {{-- Unit / Department (Read-only) --}}
                    <div>
                        <label class="label">Unit / Bidang Kerja</label>
                        <input type="text" value="{{ $user->department?->name ?? 'Umum / Tanpa Unit' }}" disabled class="input bg-gray-100 text-gray-500 cursor-not-allowed">
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="btn-primary">
                        Simpan Perubahan Profil
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ─── Section 2: Ubah Kata Sandi ─── --}}
    <div class="card">
        <div class="card-header">
            <h3 class="text-base font-semibold text-gray-900">Ubah Kata Sandi</h3>
            <p class="text-xs text-gray-500 mt-0.5">Pastikan kata sandi baru Anda minimal 8 karakter dan aman</p>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
                @csrf
                @method('PUT')

                {{-- Password saat ini --}}
                <div>
                    <label for="current_password" class="label">Kata Sandi Saat Ini <span class="text-red-500">*</span></label>
                    <input id="current_password" type="password" name="current_password" required autocomplete="current-password" class="input @error('current_password') input-error @enderror">
                    @error('current_password')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Password Baru --}}
                    <div>
                        <label for="password" class="label">Kata Sandi Baru <span class="text-red-500">*</span></label>
                        <input id="password" type="password" name="password" required autocomplete="new-password" class="input @error('password') input-error @enderror">
                        @error('password')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Konfirmasi Password Baru --}}
                    <div>
                        <label for="password_confirmation" class="label">Konfirmasi Kata Sandi Baru <span class="text-red-500">*</span></label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="input">
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="btn-primary">
                        Perbarui Kata Sandi
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
