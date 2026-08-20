@extends('layouts.app')

@section('title', 'Retensi Arsip')
@section('page-title', 'Retensi Arsip')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600">Dashboard</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-600">Retensi Arsip</span>
@endsection

@section('content')
<div class="space-y-6" x-data="{
    createModalOpen: false,
    editModalOpen: false,
    editPolicy: null,
    deleteModalOpen: false,
    deletePolicy: null,
    isPermanentCreate: false,
    isPermanentEdit: false,
    openEdit(policy) {
        this.editPolicy = policy;
        this.isPermanentEdit = policy.is_permanent;
        this.editModalOpen = true;
    },
    openDelete(policy) {
        this.deletePolicy = policy;
        this.deleteModalOpen = true;
    }
}">

    {{-- ─── Header & Action Button ─── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-bold text-gray-900">Master Kebijakan Retensi Arsip</h3>
            <p class="text-xs text-gray-500 mt-0.5">Kelola aturan jangka waktu penyimpanan dan jadwal retensi arsip digital</p>
        </div>
        <button @click="createModalOpen = true" class="btn-primary flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Retensi
        </button>
    </div>

    {{-- ─── Filters & Search ─── --}}
    <div class="card p-4">
        <form method="GET" action="{{ route('retention-policies.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="sm:col-span-2">
                <label for="search" class="sr-only">Cari</label>
                <input id="search" type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau deskripsi kebijakan retensi..." class="input">
            </div>

            <div class="flex items-center gap-2">
                <select name="status" class="input flex-1">
                    <option value="">Semua Status</option>
                    <option value="true" {{ request('status') === 'true' ? 'selected' : '' }}>Aktif</option>
                    <option value="false" {{ request('status') === 'false' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
                <button type="submit" class="btn-primary btn-sm">Filter</button>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('retention-policies.index') }}" class="btn-secondary btn-sm">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- ─── Retention Policies Table ─── --}}
    <div class="card overflow-hidden">
        <div class="table-container border-0 rounded-none">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama Kebijakan</th>
                        <th>Jangka Waktu Retensi</th>
                        <th>Deskripsi</th>
                        <th>Jumlah Arsip Terikat</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($policies as $policy)
                    <tr>
                        <td>
                            <p class="font-bold text-gray-900 text-sm">{{ $policy->name }}</p>
                        </td>
                        <td>
                            @if($policy->is_permanent)
                                <span class="badge-blue font-bold text-xs">Permanen (Selamanya)</span>
                            @else
                                <span class="badge-gray font-mono font-bold text-xs">{{ $policy->duration_years }} Tahun</span>
                            @endif
                        </td>
                        <td>
                            <p class="text-xs text-gray-500 truncate max-w-sm">{{ $policy->description ?? '—' }}</p>
                        </td>
                        <td>
                            <span class="font-mono text-xs font-bold text-gray-700 bg-gray-100 px-2 py-0.5 rounded">
                                {{ $policy->archives_count }} Arsip
                            </span>
                        </td>
                        <td>
                            @if($policy->is_active)
                                <span class="badge-green">Aktif</span>
                            @else
                                <span class="badge-red">Tidak Aktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                <button @click="openEdit({{ json_encode($policy) }})" class="btn-ghost btn-sm text-blue-600" title="Edit Kebijakan">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>

                                <form action="{{ route('retention-policies.status', $policy) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn-ghost btn-sm {{ $policy->is_active ? 'text-amber-600' : 'text-green-600' }}" title="{{ $policy->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        @if($policy->is_active)
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @endif
                                    </button>
                                </form>

                                <button @click="openDelete({{ json_encode($policy) }})" class="btn-ghost btn-sm text-red-600" title="Hapus Kebijakan">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-10 text-gray-500 text-sm">
                            Belum ada kebijakan retensi tersimpan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($policies->hasPages())
        <div class="p-4 border-t border-gray-100 bg-gray-50">
            {{ $policies->links() }}
        </div>
        @endif
    </div>

    {{-- ─── Modal 1: Tambah Retention Policy ─── --}}
    <div x-show="createModalOpen" x-transition class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" style="display:none;" @click.self="createModalOpen = false">
        <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl relative" @click.stop>
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                <h3 class="text-base font-bold text-gray-900">Tambah Kebijakan Retensi</h3>
                <button @click="createModalOpen = false" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form action="{{ route('retention-policies.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="name" class="label">Nama Kebijakan <span class="text-red-500">*</span></label>
                    <input id="name" type="text" name="name" required placeholder="Contoh: 5 Tahun / Permanen" class="input">
                </div>

                <div class="flex items-center gap-2 py-1">
                    <input id="is_permanent_create" type="checkbox" name="is_permanent" value="1" x-model="isPermanentCreate" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    <label for="is_permanent_create" class="text-xs font-semibold text-gray-800 cursor-pointer">
                        Retensi Selamanya (Permanen)
                    </label>
                </div>

                <div x-show="!isPermanentCreate">
                    <label for="duration_years" class="label">Durasi Masa Simpan (Tahun) <span class="text-red-500">*</span></label>
                    <input id="duration_years" type="number" name="duration_years" min="1" max="100" placeholder="Contoh: 5" class="input">
                </div>

                <div>
                    <label for="desc" class="label">Deskripsi</label>
                    <textarea id="desc" name="description" rows="2" class="input" placeholder="Keterangan aturan retensi..."></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" @click="createModalOpen = false" class="btn-secondary btn-sm">Batal</button>
                    <button type="submit" class="btn-primary btn-sm">Simpan Retensi</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ─── Modal 2: Edit Retention Policy ─── --}}
    <div x-show="editModalOpen" x-transition class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" style="display:none;" @click.self="editModalOpen = false">
        <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl relative" @click.stop>
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                <h3 class="text-base font-bold text-gray-900">Edit Kebijakan Retensi</h3>
                <button @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <template x-if="editPolicy">
                <form :action="`/retention-policies/${editPolicy.id}`" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="edit_name" class="label">Nama Kebijakan <span class="text-red-500">*</span></label>
                        <input id="edit_name" type="text" name="name" :value="editPolicy.name" required class="input">
                    </div>

                    <div class="flex items-center gap-2 py-1">
                        <input id="is_permanent_edit" type="checkbox" name="is_permanent" value="1" x-model="isPermanentEdit" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        <label for="is_permanent_edit" class="text-xs font-semibold text-gray-800 cursor-pointer">
                            Retensi Selamanya (Permanen)
                        </label>
                    </div>

                    <div x-show="!isPermanentEdit">
                        <label for="edit_duration_years" class="label">Durasi Masa Simpan (Tahun) <span class="text-red-500">*</span></label>
                        <input id="edit_duration_years" type="number" name="duration_years" :value="editPolicy.duration_years" min="1" max="100" class="input">
                    </div>

                    <div>
                        <label for="edit_desc" class="label">Deskripsi</label>
                        <textarea id="edit_desc" name="description" rows="2" class="input" x-text="editPolicy.description"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                        <button type="button" @click="editModalOpen = false" class="btn-secondary btn-sm">Batal</button>
                        <button type="submit" class="btn-primary btn-sm">Simpan Perubahan</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    {{-- ─── Modal 3: Hapus Retention Policy ─── --}}
    <div x-show="deleteModalOpen" x-transition class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" style="display:none;" @click.self="deleteModalOpen = false">
        <div class="bg-white rounded-xl max-w-sm w-full p-6 shadow-xl relative" @click.stop>
            <template x-if="deletePolicy">
                <form :action="`/retention-policies/${deletePolicy.id}`" method="POST" class="space-y-4 text-center">
                    @csrf
                    @method('DELETE')
                    <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900">Konfirmasi Hapus Kebijakan Retensi</h4>
                        <p class="text-xs text-gray-500 mt-1">
                            Apakah Anda yakin ingin menghapus kebikajan <strong x-text="deletePolicy.name"></strong>?
                        </p>
                        <p class="text-[11px] text-red-500 font-medium mt-1">
                            Kebijakan retensi yang terikat pada arsip tidak dapat dihapus. Anda dapat menonaktifkan statusnya.
                        </p>
                    </div>
                    <div class="flex justify-center gap-2 pt-2">
                        <button type="button" @click="deleteModalOpen = false" class="btn-secondary btn-sm">Batal</button>
                        <button type="submit" class="btn-danger btn-sm">Hapus Kebijakan</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>
@endsection
