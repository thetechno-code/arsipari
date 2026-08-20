@extends('layouts.app')

@section('title', 'Unit / Bidang Kerja')
@section('page-title', 'Unit / Bidang')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600">Dashboard</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-600">Unit / Bidang</span>
@endsection

@section('content')
<div class="space-y-6" x-data="{
    createModalOpen: false,
    editModalOpen: false,
    editDepartment: null,
    deleteModalOpen: false,
    deleteDepartment: null,
    openEdit(dept) {
        this.editDepartment = dept;
        this.editModalOpen = true;
    },
    openDelete(dept) {
        this.deleteDepartment = dept;
        this.deleteModalOpen = true;
    }
}">

    {{-- ─── Header & Action Button ─── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-bold text-gray-900">Master Data Unit / Bidang Kerja</h3>
            <p class="text-xs text-gray-500 mt-0.5">Kelola unit kerja sekolah (Tata Usaha, Kurikulum, Kesiswaan, Keuangan, Sarpras, Kepegawaian, Umum)</p>
        </div>
        <button @click="createModalOpen = true" class="btn-primary flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Unit / Bidang
        </button>
    </div>

    {{-- ─── Filters & Search ─── --}}
    <div class="card p-4">
        <form method="GET" action="{{ route('departments.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="sm:col-span-2">
                <label for="search" class="sr-only">Cari</label>
                <input id="search" type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau kode unit..." class="input">
            </div>

            <div class="flex items-center gap-2">
                <select name="status" class="input flex-1">
                    <option value="">Semua Status</option>
                    <option value="true" {{ request('status') === 'true' ? 'selected' : '' }}>Aktif</option>
                    <option value="false" {{ request('status') === 'false' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
                <button type="submit" class="btn-primary btn-sm">Filter</button>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('departments.index') }}" class="btn-secondary btn-sm">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- ─── Departments Table ─── --}}
    <div class="card overflow-hidden">
        <div class="table-container border-0 rounded-none">
            <table class="table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Unit / Bidang</th>
                        <th>Jumlah Pengguna</th>
                        <th>Jumlah Arsip</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $dept)
                    <tr>
                        <td>
                            <code class="text-xs px-2 py-0.5 rounded font-mono font-bold bg-purple-50 text-purple-800 border border-purple-200">
                                {{ $dept->code }}
                            </code>
                        </td>
                        <td>
                            <p class="font-bold text-gray-900 text-sm">{{ $dept->name }}</p>
                            @if($dept->description)
                                <p class="text-xs text-gray-500 truncate max-w-xs">{{ $dept->description }}</p>
                            @endif
                        </td>
                        <td>
                            <span class="badge-blue text-xs">
                                {{ number_format($dept->users_count) }} pengguna
                            </span>
                        </td>
                        <td>
                            <span class="badge-gray text-xs">
                                {{ number_format($dept->archives_count) }} arsip
                            </span>
                        </td>
                        <td>
                            @if($dept->is_active)
                                <span class="badge-green">Aktif</span>
                            @else
                                <span class="badge-red">Tidak Aktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                <button @click="openEdit({{ json_encode($dept) }})" class="btn-ghost btn-sm text-blue-600" title="Edit Unit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>

                                <form action="{{ route('departments.status', $dept) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn-ghost btn-sm {{ $dept->is_active ? 'text-amber-600' : 'text-green-600' }}" title="{{ $dept->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        @if($dept->is_active)
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @endif
                                    </button>
                                </form>

                                <button @click="openDelete({{ json_encode($dept) }})" class="btn-ghost btn-sm text-red-600" title="Hapus Unit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-10 text-gray-500 text-sm">
                            Belum ada unit/bidang kerja tersimpan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($departments->hasPages())
        <div class="p-4 border-t border-gray-100 bg-gray-50">
            {{ $departments->links() }}
        </div>
        @endif
    </div>

    {{-- ─── Modal 1: Tambah Department ─── --}}
    <div x-show="createModalOpen" x-transition class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" style="display:none;" @click.self="createModalOpen = false">
        <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl relative" @click.stop>
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                <h3 class="text-base font-bold text-gray-900">Tambah Unit / Bidang Kerja</h3>
                <button @click="createModalOpen = false" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form action="{{ route('departments.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="dept_name" class="label">Nama Unit / Bidang <span class="text-red-500">*</span></label>
                    <input id="dept_name" type="text" name="name" required placeholder="Contoh: Kepegawaian" class="input">
                </div>
                <div>
                    <label for="dept_code" class="label">Kode Unit <span class="text-red-500">*</span></label>
                    <input id="dept_code" type="text" name="code" required placeholder="Contoh: PEG" class="input uppercase">
                </div>
                <div>
                    <label for="dept_desc" class="label">Deskripsi</label>
                    <textarea id="dept_desc" name="description" rows="2" class="input" placeholder="Penjelasan bidang kerja..."></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" @click="createModalOpen = false" class="btn-secondary btn-sm">Batal</button>
                    <button type="submit" class="btn-primary btn-sm">Simpan Unit</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ─── Modal 2: Edit Department ─── --}}
    <div x-show="editModalOpen" x-transition class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" style="display:none;" @click.self="editModalOpen = false">
        <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl relative" @click.stop>
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                <h3 class="text-base font-bold text-gray-900">Edit Unit / Bidang Kerja</h3>
                <button @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <template x-if="editDepartment">
                <form :action="`/departments/${editDepartment.id}`" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="edit_dept_name" class="label">Nama Unit / Bidang <span class="text-red-500">*</span></label>
                        <input id="edit_dept_name" type="text" name="name" :value="editDepartment.name" required class="input">
                    </div>
                    <div>
                        <label for="edit_dept_code" class="label">Kode Unit <span class="text-red-500">*</span></label>
                        <input id="edit_dept_code" type="text" name="code" :value="editDepartment.code" required class="input uppercase">
                    </div>
                    <div>
                        <label for="edit_dept_desc" class="label">Deskripsi</label>
                        <textarea id="edit_dept_desc" name="description" rows="2" class="input" x-text="editDepartment.description"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                        <button type="button" @click="editModalOpen = false" class="btn-secondary btn-sm">Batal</button>
                        <button type="submit" class="btn-primary btn-sm">Simpan Perubahan</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    {{-- ─── Modal 3: Hapus Department ─── --}}
    <div x-show="deleteModalOpen" x-transition class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" style="display:none;" @click.self="deleteModalOpen = false">
        <div class="bg-white rounded-xl max-w-sm w-full p-6 shadow-xl relative" @click.stop>
            <template x-if="deleteDepartment">
                <form :action="`/departments/${deleteDepartment.id}`" method="POST" class="space-y-4 text-center">
                    @csrf
                    @method('DELETE')
                    <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900">Konfirmasi Hapus Unit</h4>
                        <p class="text-xs text-gray-500 mt-1">
                            Apakah Anda yakin ingin menghapus unit <strong x-text="deleteDepartment.name"></strong>?
                        </p>
                        <p class="text-[11px] text-red-500 font-medium mt-1">
                            Unit yang masih memiliki pengguna terdaftar atau arsip tidak akan dapat dihapus.
                        </p>
                    </div>
                    <div class="flex justify-center gap-2 pt-2">
                        <button type="button" @click="deleteModalOpen = false" class="btn-secondary btn-sm">Batal</button>
                        <button type="submit" class="btn-danger btn-sm">Hapus Unit</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>
@endsection
