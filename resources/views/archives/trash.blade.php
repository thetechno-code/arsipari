@extends('layouts.app')

@section('title', 'Sampah Arsip Terhapus')
@section('page-title', 'Arsip Terhapus')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600">Dashboard</a>
    <span class="text-gray-400">/</span>
    <a href="{{ route('archives.index') }}" class="text-gray-400 hover:text-gray-600">Arsip</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-600">Terhapus</span>
@endsection

@section('content')
<div class="space-y-6" x-data="{
    restoreModalOpen: false,
    restoreArchive: null,
    openRestore(archive) {
        this.restoreArchive = archive;
        this.restoreModalOpen = true;
    }
}">

    {{-- ─── Header ─── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-bold text-gray-900">Tempat Sampah Arsip Digital</h3>
            <p class="text-xs text-gray-500 mt-0.5">Daftar arsip yang di-soft delete. Berkas fisik tetap aman dan dapat dipulihkan oleh Admin.</p>
        </div>
        <a href="{{ route('archives.index') }}" class="btn-secondary btn-sm flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Semua Arsip
        </a>
    </div>

    {{-- ─── Search ─── --}}
    <div class="card p-4">
        <form method="GET" action="{{ route('archives.trash') }}" class="flex items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari arsip terhapus..." class="input text-xs flex-1">
            <button type="submit" class="btn-primary btn-sm">Cari</button>
            @if(request('search'))
                <a href="{{ route('archives.trash') }}" class="btn-secondary btn-sm">Reset</a>
            @endif
        </form>
    </div>

    {{-- ─── Trashed Archives Table ─── --}}
    <div class="card overflow-hidden">
        <div class="table-container border-0 rounded-none">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nomor Arsip</th>
                        <th>Judul Dokumen</th>
                        <th>Kategori</th>
                        <th>Unit / Bidang</th>
                        <th>Tanggal Dihapus</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trashedArchives as $archive)
                    <tr class="bg-red-50/20">
                        <td>
                            <code class="text-xs font-mono font-bold text-red-800 bg-red-100 px-2 py-0.5 rounded">
                                {{ $archive->archive_number }}
                            </code>
                        </td>
                        <td>
                            <p class="font-bold text-gray-900 text-xs">{{ $archive->title }}</p>
                        </td>
                        <td>
                            <span class="badge-blue text-[10px]">
                                {{ $archive->category?->name ?? '—' }}
                            </span>
                        </td>
                        <td>
                            <span class="text-xs text-gray-700 font-medium">
                                {{ $archive->department?->name ?? '—' }}
                            </span>
                        </td>
                        <td>
                            <span class="text-xs text-gray-600 font-mono block">
                                {{ $archive->deleted_at ? $archive->deleted_at->translatedFormat('d M Y H:i') : '—' }}
                            </span>
                        </td>
                        <td>
                            <div class="flex items-center justify-end">
                                <button @click="openRestore({{ json_encode($archive) }})" class="btn-primary btn-sm flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    Pulihkan Arsip
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-10 text-gray-500 text-sm">
                            Tidak ada arsip di tempat sampah.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($trashedArchives->hasPages())
        <div class="p-4 border-t border-gray-100 bg-gray-50">
            {{ $trashedArchives->links() }}
        </div>
        @endif
    </div>

    {{-- ─── Modal Restore Confirmation ─── --}}
    <div x-show="restoreModalOpen" x-transition class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" style="display:none;" @click.self="restoreModalOpen = false">
        <div class="bg-white rounded-xl max-w-sm w-full p-6 shadow-xl relative" @click.stop>
            <template x-if="restoreArchive">
                <form :action="`/archives/${restoreArchive.id}/restore`" method="POST" class="space-y-4 text-center">
                    @csrf
                    @method('PUT')
                    <div class="w-12 h-12 rounded-full bg-green-100 text-green-600 flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900">Konfirmasi Pemulihan Arsip</h4>
                        <p class="text-xs text-gray-500 mt-1">
                            Apakah Anda yakin ingin memulihkan arsip <strong x-text="restoreArchive.title"></strong>?
                        </p>
                        <p class="text-[11px] text-green-600 font-medium mt-1">
                            Arsip akan kembali muncul pada daftar arsip aktif.
                        </p>
                    </div>
                    <div class="flex justify-center gap-2 pt-2">
                        <button type="button" @click="restoreModalOpen = false" class="btn-secondary btn-sm">Batal</button>
                        <button type="submit" class="btn-success btn-sm">Pulihkan Arsip</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>
@endsection
