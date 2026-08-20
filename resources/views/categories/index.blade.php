@extends('layouts.app')

@section('title', 'Kategori Arsip')
@section('page-title', 'Kategori Arsip')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600">Dashboard</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-600">Kategori Arsip</span>
@endsection

@section('content')
<div class="space-y-6" x-data="{
    createModalOpen: false,
    editModalOpen: false,
    editCategory: null,
    deleteModalOpen: false,
    deleteCategory: null,
    openEdit(category) {
        this.editCategory = category;
        this.editModalOpen = true;
    },
    openDelete(category) {
        this.deleteCategory = category;
        this.deleteModalOpen = true;
    }
}">

    {{-- ─── Header & Action Button ─── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-bold text-gray-900">Klasifikasi & Kategori Arsip</h3>
            <p class="text-xs text-gray-500 mt-0.5">Kelola hirarki pengelompokan arsip 2 level (Kategori → Subkategori)</p>
        </div>
        <button @click="createModalOpen = true" class="btn-primary flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Kategori
        </button>
    </div>

    {{-- ─── Filters & Search ─── --}}
    <div class="card p-4">
        <form method="GET" action="{{ route('categories.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label for="search" class="sr-only">Cari</label>
                <input id="search" type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau kode..." class="input">
            </div>

            <div>
                <label for="level" class="sr-only">Tingkat Hirarki</label>
                <select id="level" name="level" class="input">
                    <option value="">Semua Tingkat</option>
                    <option value="root" {{ request('level') === 'root' ? 'selected' : '' }}>Kategori Induk (Root)</option>
                    <option value="sub" {{ request('level') === 'sub' ? 'selected' : '' }}>Subkategori</option>
                </select>
            </div>

            <div>
                <label for="status" class="sr-only">Status</label>
                <select id="status" name="status" class="input">
                    <option value="">Semua Status</option>
                    <option value="true" {{ request('status') === 'true' ? 'selected' : '' }}>Aktif</option>
                    <option value="false" {{ request('status') === 'false' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="btn-primary btn-sm flex-1">Filter</button>
                @if(request()->hasAny(['search', 'level', 'status']))
                    <a href="{{ route('categories.index') }}" class="btn-secondary btn-sm">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- ─── Categories Table ─── --}}
    <div class="card overflow-hidden">
        <div class="table-container border-0 rounded-none">
            <table class="table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Kategori</th>
                        <th>Kategori Induk</th>
                        <th>Status</th>
                        <th>Jumlah Arsip</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $cat)
                    <tr class="{{ $cat->parent_id ? 'bg-gray-50/50' : 'bg-white font-medium' }}">
                        <td>
                            <code class="text-xs px-2 py-0.5 rounded font-mono border {{ $cat->parent_id ? 'bg-slate-50 border-slate-200 text-slate-700' : 'bg-blue-50 border-blue-200 text-blue-800 font-bold' }}">
                                {{ $cat->code }}
                            </code>
                        </td>
                        <td>
                            <div class="flex items-center gap-1.5">
                                @if($cat->parent_id)
                                    <span class="text-gray-300 font-mono text-xs">└─</span>
                                @endif
                                <span class="text-sm {{ $cat->parent_id ? 'text-gray-700' : 'text-gray-900 font-bold' }}">
                                    {{ $cat->name }}
                                </span>
                            </div>
                            @if($cat->description)
                                <p class="text-[11px] text-gray-400 mt-0.5 truncate max-w-xs">{{ $cat->description }}</p>
                            @endif
                        </td>
                        <td>
                            @if($cat->parent)
                                <span class="text-xs text-blue-700 font-medium bg-blue-50 px-2 py-0.5 rounded">
                                    {{ $cat->parent->name }} ({{ $cat->parent->code }})
                                </span>
                            @else
                                <span class="text-xs text-gray-400 italic">— Kategori Induk</span>
                            @endif
                        </td>
                        <td>
                            @if($cat->is_active)
                                <span class="badge-green">Aktif</span>
                            @else
                                <span class="badge-red">Tidak Aktif</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge-gray text-xs font-semibold">
                                {{ number_format($cat->archives_count) }} arsip
                            </span>
                        </td>
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                {{-- Edit --}}
                                <button @click="openEdit({{ json_encode($cat) }})" class="btn-ghost btn-sm text-blue-600" title="Edit Kategori">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>

                                {{-- Toggle Status --}}
                                <form action="{{ route('categories.status', $cat) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn-ghost btn-sm {{ $cat->is_active ? 'text-amber-600' : 'text-green-600' }}" title="{{ $cat->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        @if($cat->is_active)
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @endif
                                    </button>
                                </form>

                                {{-- Delete --}}
                                <button @click="openDelete({{ json_encode($cat) }})" class="btn-ghost btn-sm text-red-600" title="Hapus Kategori">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-10 text-gray-500 text-sm">
                            Belum ada kategori arsip tersimpan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($categories->hasPages())
        <div class="p-4 border-t border-gray-100 bg-gray-50">
            {{ $categories->links() }}
        </div>
        @endif
    </div>

    {{-- ─── Modal 1: Tambah Kategori ─── --}}
    <div x-show="createModalOpen" x-transition class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" style="display:none;" @click.self="createModalOpen = false">
        <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl relative" @click.stop>
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                <h3 class="text-base font-bold text-gray-900">Tambah Kategori Arsip</h3>
                <button @click="createModalOpen = false" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form action="{{ route('categories.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="name" class="label">Nama Kategori <span class="text-red-500">*</span></label>
                    <input id="name" type="text" name="name" required placeholder="Contoh: Surat Keputusan" class="input">
                </div>
                <div>
                    <label for="code" class="label">Kode Klasifikasi <span class="text-red-500">*</span></label>
                    <input id="code" type="text" name="code" required placeholder="Contoh: ADM-SK" class="input uppercase">
                </div>
                <div>
                    <label for="parent_id" class="label">Kategori Induk (Parent)</label>
                    <select id="parent_id" name="parent_id" class="input">
                        <option value="">-- Tanpa Induk (Root Category) --</option>
                        @foreach($rootCategories as $rc)
                            <option value="{{ $rc->id }}">{{ $rc->name }} ({{ $rc->code }})</option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-gray-400 mt-1">Pilih kategori induk jika ini merupakan subkategori (Maksimal 2 level).</p>
                </div>
                <div>
                    <label for="description" class="label">Deskripsi</label>
                    <textarea id="description" name="description" rows="2" class="input" placeholder="Penjelasan singkat..."></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" @click="createModalOpen = false" class="btn-secondary btn-sm">Batal</button>
                    <button type="submit" class="btn-primary btn-sm">Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ─── Modal 2: Edit Kategori ─── --}}
    <div x-show="editModalOpen" x-transition class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" style="display:none;" @click.self="editModalOpen = false">
        <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl relative" @click.stop>
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                <h3 class="text-base font-bold text-gray-900">Edit Kategori Arsip</h3>
                <button @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <template x-if="editCategory">
                <form :action="`/categories/${editCategory.id}`" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="edit_name" class="label">Nama Kategori <span class="text-red-500">*</span></label>
                        <input id="edit_name" type="text" name="name" :value="editCategory.name" required class="input">
                    </div>
                    <div>
                        <label for="edit_code" class="label">Kode Klasifikasi <span class="text-red-500">*</span></label>
                        <input id="edit_code" type="text" name="code" :value="editCategory.code" required class="input uppercase">
                    </div>
                    <div>
                        <label for="edit_parent_id" class="label">Kategori Induk (Parent)</label>
                        <select id="edit_parent_id" name="parent_id" class="input">
                            <option value="">-- Tanpa Induk (Root Category) --</option>
                            @foreach($rootCategories as $rc)
                                <option value="{{ $rc->id }}" :selected="editCategory.parent_id == {{ $rc->id }}">{{ $rc->name }} ({{ $rc->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="edit_description" class="label">Deskripsi</label>
                        <textarea id="edit_description" name="description" rows="2" class="input" x-text="editCategory.description"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                        <button type="button" @click="editModalOpen = false" class="btn-secondary btn-sm">Batal</button>
                        <button type="submit" class="btn-primary btn-sm">Simpan Perubahan</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    {{-- ─── Modal 3: Hapus Kategori ─── --}}
    <div x-show="deleteModalOpen" x-transition class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" style="display:none;" @click.self="deleteModalOpen = false">
        <div class="bg-white rounded-xl max-w-sm w-full p-6 shadow-xl relative" @click.stop>
            <template x-if="deleteCategory">
                <form :action="`/categories/${deleteCategory.id}`" method="POST" class="space-y-4 text-center">
                    @csrf
                    @method('DELETE')
                    <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900">Konfirmasi Hapus Kategori</h4>
                        <p class="text-xs text-gray-500 mt-1">
                            Apakah Anda yakin ingin menghapus kategori <strong x-text="deleteCategory.name"></strong>?
                        </p>
                        <p class="text-[11px] text-red-500 font-medium mt-1">
                            Kategori yang masih memiliki subkategori atau digunakan oleh arsip tidak akan dapat dihapus.
                        </p>
                    </div>
                    <div class="flex justify-center gap-2 pt-2">
                        <button type="button" @click="deleteModalOpen = false" class="btn-secondary btn-sm">Batal</button>
                        <button type="submit" class="btn-danger btn-sm">Hapus Kategori</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>
@endsection
