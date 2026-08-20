@extends('layouts.app')

@section('title', 'Edit Pengguna')
@section('page-title', 'Edit Pengguna')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600">Dashboard</a>
    <span class="text-gray-400">/</span>
    <a href="{{ route('users.index') }}" class="text-gray-400 hover:text-gray-600">Pengguna</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-600">Edit</span>
@endsection

@section('content')
<div class="max-w-3xl space-y-6">

    <div class="card">
        <div class="card-header flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-gray-900">Form Edit Pengguna</h3>
                <p class="text-xs text-gray-500 mt-0.5">Ubah informasi profile, role, dan unit pengguna</p>
            </div>
            <a href="{{ route('users.index') }}" class="btn-secondary btn-sm">Kembali</a>
        </div>
        <div class="card-body">

            @if($errors->any())
            <div class="alert-error mb-5">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                    <p class="font-semibold">Terdapat kesalahan pada input Anda:</p>
                    <ul class="list-disc list-inside text-xs mt-1 space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-4">
                @csrf
                @method('PUT')

                {{-- Nama Lengkap --}}
                <div>
                    <label for="name" class="label">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required class="input @error('name') input-error @enderror">
                    @error('name')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Alamat Email --}}
                <div>
                    <label for="email" class="label">Alamat Email <span class="text-red-500">*</span></label>
                    <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required class="input @error('email') input-error @enderror">
                    @error('email')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Role & Department --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="role" class="label">Role / Peran <span class="text-red-500">*</span></label>
                        @if($user->id === auth()->id())
                            <input type="text" value="Administrator (Akun Anda Sendiri)" disabled class="input bg-gray-100 text-gray-500 cursor-not-allowed">
                            <input type="hidden" name="role" value="admin">
                            <p class="text-[11px] text-gray-400 mt-1">Anda tidak dapat mengubah role akun Anda sendiri.</p>
                        @else
                            <select id="role" name="role" required class="input @error('role') input-error @enderror">
                                @foreach($roles as $r)
                                    <option value="{{ $r->value }}" {{ old('role', $user->role->value ?? $user->role) === $r->value ? 'selected' : '' }}>
                                        {{ $r->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>

                    <div>
                        <label for="department_id" class="label">Unit / Bidang Kerja</label>
                        <select id="department_id" name="department_id" class="input @error('department_id') input-error @enderror">
                            <option value="">-- Pilih Unit (Opsional) --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id', $user->department_id) == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }} ({{ $dept->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Status --}}
                <div class="pt-2">
                    @if($user->id === auth()->id())
                        <p class="text-xs text-gray-500">Status Akun: <strong class="text-green-600">Aktif</strong> (Anda tidak dapat menonaktifkan akun Anda sendiri)</p>
                        <input type="hidden" name="is_active" value="1">
                    @else
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            <span class="text-sm font-medium text-gray-700">Akun ini Aktif</span>
                        </label>
                    @endif
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-xs text-blue-800 flex items-center justify-between">
                    <span>Memerlukan perubahan password untuk pengguna ini?</span>
                    <a href="{{ route('users.index') }}" class="font-bold underline">Gunakan fitur Reset Password di daftar pengguna</a>
                </div>

                {{-- Action buttons --}}
                <div class="flex justify-end gap-2 pt-4 border-t border-gray-100">
                    <a href="{{ route('users.index') }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">Simpan Perubahan</button>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection
