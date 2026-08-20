@extends('layouts.app')

@section('title', 'Manajemen Pengguna')
@section('page-title', 'Pengguna')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600">Dashboard</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-600">Pengguna</span>
@endsection

@section('content')
<div class="space-y-6" x-data="{
    detailModalOpen: false,
    selectedUser: null,
    resetModalOpen: false,
    resetUser: null,
    statusModalOpen: false,
    statusUser: null,
    openDetail(user) {
        this.selectedUser = user;
        this.detailModalOpen = true;
    },
    openReset(user) {
        this.resetUser = user;
        this.resetModalOpen = true;
    },
    openStatus(user) {
        this.statusUser = user;
        this.statusModalOpen = true;
    }
}">

    {{-- ─── Header & Action Button ─── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-bold text-gray-900">Daftar Pengguna</h3>
            <p class="text-xs text-gray-500 mt-0.5">Kelola hak akses, role, dan akun pengguna sistem ARSIPARI</p>
        </div>
        <a href="{{ route('users.create') }}" class="btn-primary flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Pengguna
        </a>
    </div>

    {{-- ─── Filters & Search Bar ─── --}}
    <div class="card p-4">
        <form method="GET" action="{{ route('users.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            {{-- Search input --}}
            <div class="lg:col-span-2">
                <label for="search" class="sr-only">Cari</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input id="search" type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..." class="input pl-9">
                </div>
            </div>

            {{-- Role filter --}}
            <div>
                <label for="role" class="sr-only">Role</label>
                <select id="role" name="role" class="input">
                    <option value="">Semua Role</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->value }}" {{ request('role') === $r->value ? 'selected' : '' }}>
                            {{ $r->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Department filter --}}
            <div>
                <label for="department_id" class="sr-only">Unit/Bidang</label>
                <select id="department_id" name="department_id" class="input">
                    <option value="">Semua Unit</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Status filter & buttons --}}
            <div class="flex items-center gap-2">
                <select name="status" class="input flex-1">
                    <option value="">Semua Status</option>
                    <option value="true" {{ request('status') === 'true' ? 'selected' : '' }}>Aktif</option>
                    <option value="false" {{ request('status') === 'false' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
                <button type="submit" class="btn-primary btn-sm">Filter</button>
                @if(request()->hasAny(['search', 'role', 'department_id', 'status']))
                    <a href="{{ route('users.index') }}" class="btn-secondary btn-sm">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- ─── Users Data Table ─── --}}
    <div class="card overflow-hidden">
        <div class="table-container border-0 rounded-none">
            <table class="table">
                <thead>
                    <tr>
                        <th>Pengguna</th>
                        <th>Role</th>
                        <th>Unit / Bidang</th>
                        <th>Status</th>
                        <th>Login Terakhir</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-primary-600 text-white font-bold text-xs flex items-center justify-center flex-shrink-0">
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-gray-900 text-sm truncate">{{ $u->name }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ $u->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($u->isAdmin())
                                <span class="badge-blue">Administrator</span>
                            @elseif($u->isOperator())
                                <span class="badge-green">Operator</span>
                            @else
                                <span class="badge-gray">Viewer</span>
                            @endif
                        </td>
                        <td>
                            <span class="text-xs text-gray-700 font-medium">
                                {{ $u->department?->name ?? '—' }}
                            </span>
                        </td>
                        <td>
                            @if($u->is_active)
                                <span class="badge-green inline-flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Aktif
                                </span>
                            @else
                                <span class="badge-red inline-flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Tidak Aktif
                                </span>
                            @endif
                        </td>
                        <td>
                            <span class="text-xs text-gray-500">
                                {{ $u->last_login_at ? $u->last_login_at->diffForHumans() : 'Belum pernah' }}
                            </span>
                        </td>
                        <td>
                            <div class="flex items-center justify-end gap-1.5">
                                {{-- Detail button --}}
                                <button @click="openDetail({{ json_encode($u->load('department')) }})" class="btn-ghost btn-sm text-gray-600" title="Detail Pengguna">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>

                                {{-- Edit button --}}
                                <a href="{{ route('users.edit', $u) }}" class="btn-ghost btn-sm text-blue-600" title="Edit Pengguna">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>

                                {{-- Reset Password button --}}
                                <button @click="openReset({{ json_encode($u) }})" class="btn-ghost btn-sm text-amber-600" title="Reset Password">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                </button>

                                {{-- Toggle Active Status button --}}
                                @if($u->id !== auth()->id())
                                <button @click="openStatus({{ json_encode($u) }})" class="btn-ghost btn-sm {{ $u->is_active ? 'text-red-600' : 'text-green-600' }}" title="{{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                    @if($u->is_active)
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @endif
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-10 text-gray-500 text-sm">
                            Tidak ada data pengguna ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination links --}}
        @if($users->hasPages())
        <div class="p-4 border-t border-gray-100 bg-gray-50">
            {{ $users->links() }}
        </div>
        @endif
    </div>

    {{-- ─── Modal 1: Detail User ─── --}}
    <div x-show="detailModalOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4"
         style="display:none;"
         @click.self="detailModalOpen = false">
        <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl relative" @click.stop>
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                <h3 class="text-base font-bold text-gray-900">Detail Pengguna</h3>
                <button @click="detailModalOpen = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <template x-if="selectedUser">
                <div class="space-y-3 text-sm">
                    <div>
                        <span class="text-xs text-gray-500 block">Nama Lengkap</span>
                        <p class="font-semibold text-gray-900" x-text="selectedUser.name"></p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 block">Alamat Email</span>
                        <p class="font-medium text-gray-800" x-text="selectedUser.email"></p>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <span class="text-xs text-gray-500 block">Role</span>
                            <span class="badge-blue uppercase text-[10px]" x-text="selectedUser.role"></span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500 block">Status</span>
                            <span :class="selectedUser.is_active ? 'badge-green' : 'badge-red'" x-text="selectedUser.is_active ? 'Aktif' : 'Tidak Aktif'"></span>
                        </div>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 block">Unit / Bidang</span>
                        <p class="text-gray-800 font-medium" x-text="selectedUser.department ? selectedUser.department.name : 'Umum / Tanpa Unit'"></p>
                    </div>
                </div>
            </template>
            <div class="mt-6 flex justify-end">
                <button type="button" @click="detailModalOpen = false" class="btn-secondary btn-sm">Tutup</button>
            </div>
        </div>
    </div>

    {{-- ─── Modal 2: Reset Password ─── --}}
    <div x-show="resetModalOpen"
         x-transition
         class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4"
         style="display:none;"
         @click.self="resetModalOpen = false">
        <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl relative" @click.stop>
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                <h3 class="text-base font-bold text-gray-900">Reset Password Pengguna</h3>
                <button @click="resetModalOpen = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <template x-if="resetUser">
                <form :action="`/users/${resetUser.id}/password`" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <p class="text-xs text-gray-600">
                        Atur kata sandi baru untuk <strong x-text="resetUser.name"></strong>.
                    </p>
                    <div>
                        <label for="admin_pwd" class="label">Kata Sandi Baru</label>
                        <input id="admin_pwd" type="password" name="password" required class="input" placeholder="Minimal 8 karakter">
                    </div>
                    <div>
                        <label for="admin_pwd_confirm" class="label">Konfirmasi Kata Sandi Baru</label>
                        <input id="admin_pwd_confirm" type="password" name="password_confirmation" required class="input">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="resetModalOpen = false" class="btn-secondary btn-sm">Batal</button>
                        <button type="submit" class="btn-primary btn-sm">Simpan Password Baru</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    {{-- ─── Modal 3: Confirmation Dialog Toggle Status ─── --}}
    <div x-show="statusModalOpen"
         x-transition
         class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4"
         style="display:none;"
         @click.self="statusModalOpen = false">
        <div class="bg-white rounded-xl max-w-sm w-full p-6 shadow-xl relative" @click.stop>
            <template x-if="statusUser">
                <form :action="`/users/${statusUser.id}/status`" method="POST" class="space-y-4 text-center">
                    @csrf
                    @method('PUT')
                    <div class="w-12 h-12 rounded-full flex items-center justify-center mx-auto"
                         :class="statusUser.is_active ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600'">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900" x-text="statusUser.is_active ? 'Konfirmasi Nonaktifkan' : 'Konfirmasi Aktifkan'"></h4>
                        <p class="text-xs text-gray-500 mt-1">
                            Apakah Anda yakin ingin <span x-text="statusUser.is_active ? 'menonaktifkan' : 'mengaktifkan'"></span> pengguna <strong x-text="statusUser.name"></strong>?
                        </p>
                    </div>
                    <div class="flex justify-center gap-2 pt-2">
                        <button type="button" @click="statusModalOpen = false" class="btn-secondary btn-sm">Batal</button>
                        <button type="submit" :class="statusUser.is_active ? 'btn-danger btn-sm' : 'btn-primary btn-sm'"
                                x-text="statusUser.is_active ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan'">
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>
@endsection
