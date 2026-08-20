@extends('layouts.app')

@section('title', 'Cari & Kelola Arsip Digital')
@section('page-title', 'Arsip Digital')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600">Dashboard</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-600">Semua Arsip</span>
@endsection

@section('content')
<div class="space-y-6" x-data="{
    filterOpen: false,
    deleteModalOpen: false,
    deleteArchive: null,
    openDelete(archive) {
        this.deleteArchive = archive;
        this.deleteModalOpen = true;
    }
}">

    {{-- ─── Header & Action Button ─── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-xl font-bold text-gray-900">Pencarian & Penemuan Arsip Digital</h3>
            <p class="text-xs text-gray-500 mt-0.5">Pencarian cepat dokumen arsip digital internal MTsN 1 Magelang</p>
        </div>
        @can('create', App\Models\Archive::class)
        <a href="{{ route('archives.create') }}" class="btn-primary flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Arsip
        </a>
        @endcan
    </div>

    {{-- ─── PROMINENT SEARCH BAR & ADVANCED FILTERS ─── --}}
    <div class="card p-5 space-y-4">
        <form method="GET" action="{{ route('archives.index') }}" id="searchForm">

            {{-- Main Prominent Search Input Bar --}}
            <div class="flex flex-col sm:flex-row items-stretch gap-2">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari nomor arsip, judul dokumen, nomor dokumen, atau kata kunci..."
                           class="input pl-10 pr-4 py-2.5 text-sm font-medium border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-lg shadow-2xs">
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="btn-primary flex-1 sm:flex-none px-5 py-2.5 font-bold flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Cari
                    </button>

                    <button type="button" @click="filterOpen = !filterOpen"
                            class="btn-secondary px-3.5 py-2.5 text-xs font-semibold flex items-center gap-1.5"
                            :class="filterOpen ? 'bg-gray-200 border-gray-400' : ''">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filter Lanjutan
                        <svg class="w-3.5 h-3.5 transition-transform duration-150" :class="filterOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                </div>
            </div>

            {{-- Collapsible Advanced Filters Panel --}}
            <div x-show="filterOpen" x-transition class="pt-4 mt-4 border-t border-gray-100 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" style="display: none;">

                {{-- Category Filter --}}
                <div>
                    <label for="category_id" class="label">Kategori Arsip</label>
                    <select id="category_id" name="category_id" class="input text-xs">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $parentCat)
                            <optgroup label="{{ $parentCat->name }} ({{ $parentCat->code }})">
                                <option value="{{ $parentCat->id }}" {{ request('category_id') == $parentCat->id ? 'selected' : '' }}>
                                    {{ $parentCat->name }} (Induk)
                                </option>
                                @foreach($parentCat->children as $childCat)
                                    <option value="{{ $childCat->id }}" {{ request('category_id') == $childCat->id ? 'selected' : '' }}>
                                        └─ {{ $childCat->name }} ({{ $childCat->code }})
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                {{-- Department Filter --}}
                <div>
                    <label for="department_id" class="label">Unit / Bidang Kerja</label>
                    <select id="department_id" name="department_id" class="input text-xs">
                        <option value="">Semua Unit / Bidang</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }} ({{ $dept->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Year Filter --}}
                <div>
                    <label for="year" class="label">Tahun Dokumen</label>
                    <select id="year" name="year" class="input text-xs">
                        <option value="">Semua Tahun</option>
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                                Tahun {{ $y }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Document Type Filter --}}
                <div>
                    <label for="document_type" class="label">Jenis Dokumen</label>
                    <select id="document_type" name="document_type" class="input text-xs">
                        <option value="">Semua Jenis</option>
                        @foreach($documentTypes as $dt)
                            <option value="{{ strtolower($dt->code) }}" {{ request('document_type') == strtolower($dt->code) ? 'selected' : '' }}>
                                {{ $dt->name }} ({{ $dt->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Date From Filter --}}
                <div>
                    <label for="date_from" class="label">Tanggal Dokumen Dari</label>
                    <input id="date_from" type="date" name="date_from" value="{{ request('date_from') }}" class="input text-xs">
                </div>

                {{-- Date To Filter --}}
                <div>
                    <label for="date_to" class="label">Tanggal Dokumen Sampai</label>
                    <input id="date_to" type="date" name="date_to" value="{{ request('date_to') }}" class="input text-xs">
                </div>

                {{-- Sorting Filter --}}
                <div>
                    <label for="sort" class="label">Urutkan Berdasarkan</label>
                    <select id="sort" name="sort" class="input text-xs">
                        <option value="created_at" {{ request('sort', 'created_at') === 'created_at' ? 'selected' : '' }}>Waktu Unggah Terbaru</option>
                        <option value="document_date" {{ request('sort') === 'document_date' ? 'selected' : '' }}>Tanggal Dokumen</option>
                        <option value="year" {{ request('sort') === 'year' ? 'selected' : '' }}>Tahun Dokumen</option>
                        <option value="title" {{ request('sort') === 'title' ? 'selected' : '' }}>Judul (A-Z)</option>
                    </select>
                </div>

                {{-- Filter Action Buttons --}}
                <div class="flex items-end gap-2">
                    <button type="submit" class="btn-primary btn-sm flex-1">Terapkan Filter</button>
                    @if(request()->hasAny(['search', 'category_id', 'department_id', 'year', 'document_type', 'date_from', 'date_to', 'sort']))
                        <a href="{{ route('archives.index') }}" class="btn-secondary btn-sm">Reset</a>
                    @endif
                </div>

            </div>
        </form>

        {{-- Active Filter Chips --}}
        @if(request()->hasAny(['search', 'category_id', 'department_id', 'year', 'document_type', 'date_from', 'date_to']))
        <div class="flex flex-wrap items-center gap-1.5 pt-2 border-t border-gray-100 text-xs">
            <span class="text-gray-500 font-medium mr-1">Filter Aktif:</span>

            @if(request('search'))
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-primary-50 text-primary-800 border border-primary-200">
                    Kata kunci: "{{ request('search') }}"
                    <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="hover:text-red-600 font-bold">×</a>
                </span>
            @endif

            @if(request('category_id'))
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-800 border border-blue-200">
                    Kategori ID: {{ request('category_id') }}
                    <a href="{{ request()->fullUrlWithQuery(['category_id' => null]) }}" class="hover:text-red-600 font-bold">×</a>
                </span>
            @endif

            @if(request('department_id'))
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-orange-50 text-orange-800 border border-orange-200">
                    Unit ID: {{ request('department_id') }}
                    <a href="{{ request()->fullUrlWithQuery(['department_id' => null]) }}" class="hover:text-red-600 font-bold">×</a>
                </span>
            @endif

            @if(request('year'))
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-green-50 text-green-800 border border-green-200">
                    Tahun {{ request('year') }}
                    <a href="{{ request()->fullUrlWithQuery(['year' => null]) }}" class="hover:text-red-600 font-bold">×</a>
                </span>
            @endif

            @if(request('document_type'))
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-purple-50 text-purple-800 border border-purple-200">
                    Format: {{ strtoupper(request('document_type')) }}
                    <a href="{{ request()->fullUrlWithQuery(['document_type' => null]) }}" class="hover:text-red-600 font-bold">×</a>
                </span>
            @endif

            @if(request('date_from'))
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-800 border border-gray-300">
                    Dari: {{ request('date_from') }}
                    <a href="{{ request()->fullUrlWithQuery(['date_from' => null]) }}" class="hover:text-red-600 font-bold">×</a>
                </span>
            @endif

            @if(request('date_to'))
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-800 border border-gray-300">
                    Sampai: {{ request('date_to') }}
                    <a href="{{ request()->fullUrlWithQuery(['date_to' => null]) }}" class="hover:text-red-600 font-bold">×</a>
                </span>
            @endif

            <a href="{{ route('archives.index') }}" class="text-xs text-red-600 font-semibold hover:underline ml-2">Hapus Semua Filter</a>
        </div>
        @endif

    </div>

    {{-- ─── Result Summary Count Header ─── --}}
    <div class="flex items-center justify-between text-xs text-gray-600 px-1">
        <p>
            Menampilkan <strong class="text-gray-900">{{ $archives->total() }}</strong> arsip digital
            @if(request('search'))
                untuk kata kunci <strong class="text-primary-700">"{{ request('search') }}"</strong>
            @endif
        </p>
        <p class="font-mono text-gray-400">Halaman {{ $archives->currentPage() }} dari {{ $archives->lastPage() }}</p>
    </div>

    {{-- ─── Archives Table ─── --}}
    <div class="card overflow-hidden">
        <div class="table-container border-0 rounded-none">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nomor Arsip</th>
                        <th>Judul Dokumen</th>
                        <th>Kategori</th>
                        <th>Unit / Bidang</th>
                        <th>Tanggal</th>
                        <th>Berkas</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($archives as $archive)
                    <tr>
                        <td>
                            <a href="{{ route('archives.show', $archive) }}" class="font-mono text-xs font-bold text-primary-700 hover:underline">
                                {{ $archive->archive_number }}
                            </a>
                            @if($archive->document_number)
                                <p class="text-[11px] text-gray-500 font-mono mt-0.5">No: {{ $archive->document_number }}</p>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('archives.show', $archive) }}" class="font-bold text-gray-900 text-sm hover:text-primary-600 transition-colors line-clamp-1">
                                {{ $archive->title }}
                            </a>
                            @if($archive->keywords)
                                <p class="text-[11px] text-gray-400 mt-0.5 truncate max-w-xs">
                                    <span class="font-medium text-gray-500">Kata kunci:</span> {{ $archive->keywords }}
                                </p>
                            @endif
                        </td>
                        <td>
                            <span class="badge-blue text-xs">
                                {{ $archive->category?->name ?? '—' }}
                            </span>
                        </td>
                        <td>
                            <span class="text-xs text-gray-700 font-medium">
                                {{ $archive->department?->name ?? '—' }}
                            </span>
                        </td>
                        <td>
                            <span class="text-xs text-gray-700 block">
                                {{ $archive->document_date ? $archive->document_date->translatedFormat('d M Y') : $archive->year }}
                            </span>
                            <span class="text-[10px] text-gray-400 font-mono">Tahun {{ $archive->year }}</span>
                        </td>
                        <td>
                            <div class="flex items-center gap-1.5">
                                <span class="badge-gray uppercase text-[10px] font-bold">
                                    {{ $archive->file_extension }}
                                </span>
                                <span class="text-[11px] text-gray-500">
                                    {{ $archive->file_size_formatted }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                {{-- View Detail --}}
                                <a href="{{ route('archives.show', $archive) }}" class="btn-ghost btn-sm text-gray-600" title="Lihat Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>

                                {{-- Secure Download --}}
                                <a href="{{ route('archives.download', $archive) }}" class="btn-ghost btn-sm text-green-600" title="Unduh Berkas">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                </a>

                                {{-- Edit (Admin & Operator) --}}
                                @can('update', $archive)
                                <a href="{{ route('archives.edit', $archive) }}" class="btn-ghost btn-sm text-blue-600" title="Edit Metadata">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                @endcan

                                {{-- Delete (Admin Only) --}}
                                @can('delete', $archive)
                                <button @click="openDelete({{ json_encode($archive) }})" class="btn-ghost btn-sm text-red-600" title="Hapus Arsip">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-12 text-gray-500 text-sm">
                            <div class="max-w-xs mx-auto space-y-3">
                                <div class="w-12 h-12 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mx-auto">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-800">Arsip Tidak Ditemukan</p>
                                    <p class="text-xs text-gray-500 mt-1">Coba gunakan kata kunci lain atau bersihkan filter pencarian Anda.</p>
                                </div>
                                @if(request()->hasAny(['search', 'category_id', 'department_id', 'year', 'document_type', 'date_from', 'date_to']))
                                    <a href="{{ route('archives.index') }}" class="btn-secondary btn-sm inline-flex">Hapus Pencarian & Filter</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($archives->hasPages())
        <div class="p-4 border-t border-gray-100 bg-gray-50">
            {{ $archives->links() }}
        </div>
        @endif
    </div>

    {{-- ─── Modal Delete Confirmation (Admin Only) ─── --}}
    <div x-show="deleteModalOpen" x-transition class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" style="display:none;" @click.self="deleteModalOpen = false">
        <div class="bg-white rounded-xl max-w-sm w-full p-6 shadow-xl relative" @click.stop>
            <template x-if="deleteArchive">
                <form :action="`/archives/${deleteArchive.id}`" method="POST" class="space-y-4 text-center">
                    @csrf
                    @method('DELETE')
                    <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900">Konfirmasi Hapus Arsip</h4>
                        <p class="text-xs text-gray-500 mt-1">
                            Apakah Anda yakin ingin menghapus arsip <strong x-text="deleteArchive.title"></strong>?
                        </p>
                    </div>
                    <div class="flex justify-center gap-2 pt-2">
                        <button type="button" @click="deleteModalOpen = false" class="btn-secondary btn-sm">Batal</button>
                        <button type="submit" class="btn-danger btn-sm">Hapus Arsip</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>
@endsection
