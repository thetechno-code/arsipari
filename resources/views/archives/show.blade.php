@extends('layouts.app')

@section('title', $archive->title)
@section('page-title', 'Detail Arsip & Riwayat Versi')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600">Dashboard</a>
    <span class="text-gray-400">/</span>
    <a href="{{ route('archives.index') }}" class="text-gray-400 hover:text-gray-600">Arsip</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-600">{{ $archive->archive_number }}</span>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    deleteModalOpen: false,
    newVersionModalOpen: false,
    restoreVersionModalOpen: false,
    selectedVersion: null,
    openRestoreVersion(ver) {
        this.selectedVersion = ver;
        this.restoreVersionModalOpen = true;
    }
}">

    {{-- ─── Header & Action Buttons ─── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-xl shadow-xs border border-gray-100">
        <div class="space-y-1">
            <div class="flex flex-wrap items-center gap-2">
                <code class="text-xs px-2.5 py-1 rounded-md font-mono font-bold bg-primary-50 text-primary-800 border border-primary-200">
                    {{ $archive->archive_number }}
                </code>

                {{-- Status Operational Badge --}}
                @if($archive->status === 'active')
                    <span class="badge-green text-xs">Status: Aktif</span>
                @else
                    <span class="badge-red text-xs">Status: Tidak Aktif</span>
                @endif

                {{-- Retention Status Badge --}}
                <span class="{{ $archive->retention_status_badge }} text-xs">
                    Retensi: {{ $archive->retention_status_label }}
                </span>

                <span class="badge-blue text-xs">{{ $archive->category?->full_name ?? '—' }}</span>
                <span class="badge-gray uppercase text-xs font-mono font-bold">{{ $archive->file_extension }}</span>
            </div>
            <h3 class="text-xl font-bold text-gray-900 leading-tight mt-1.5">{{ $archive->title }}</h3>
            @if($archive->document_number)
                <p class="text-xs text-gray-500 font-mono">No. Dokumen: {{ $archive->document_number }}</p>
            @endif
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            {{-- Secure Download Current Version --}}
            <a href="{{ route('archives.download', $archive) }}" class="btn-success flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Unduh Berkas Terbaru
            </a>

            {{-- Upload Versi Baru (Admin & Operator) --}}
            @can('update', $archive)
            <button @click="newVersionModalOpen = true" class="btn-primary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                + Versi Baru
            </button>

            {{-- Edit Metadata --}}
            <a href="{{ route('archives.edit', $archive) }}" class="btn-secondary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Metadata
            </a>
            @endcan

            {{-- Toggle Status (Admin Only) --}}
            @can('restore', $archive)
            <form action="{{ route('archives.status', $archive) }}" method="POST" class="inline">
                @csrf
                @method('PUT')
                <button type="submit" class="btn-secondary flex items-center gap-1.5 {{ $archive->status === 'active' ? 'text-amber-700' : 'text-green-700' }}"
                        title="{{ $archive->status === 'active' ? 'Nonaktifkan Arsip' : 'Aktifkan Arsip' }}">
                    @if($archive->status === 'active')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        Nonaktifkan
                    @else
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Aktifkan
                    @endif
                </button>
            </form>

            {{-- Soft Delete (Admin Only) --}}
            <button @click="deleteModalOpen = true" class="btn-danger flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Hapus
            </button>
            @endcan
        </div>
    </div>

    {{-- ─── Retention Expiry Warning Banner ─── --}}
    @if($archive->retention_status === 'expired')
        <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-900 text-xs flex items-center gap-3">
            <svg class="w-6 h-6 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div>
                <p class="font-bold">Masa Retensi Telah Berakhir!</p>
                <p class="mt-0.5">Jangka waktu penyimpanan arsip berdasarkan kebijakan <strong>{{ $archive->retentionPolicy?->name }}</strong> telah berakhir pada tanggal {{ $archive->retention_until?->translatedFormat('d F Y') }}. (Arsip tetap tersimpan aman dan tidak dihapus otomatis).</p>
            </div>
        </div>
    @elseif($archive->retention_status === 'due_soon')
        <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-amber-900 text-xs flex items-center gap-3">
            <svg class="w-6 h-6 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <p class="font-bold">Masa Retensi Akan Berakhir Segera</p>
                <p class="mt-0.5">Masa retensi arsip ini akan berakhir pada tanggal {{ $archive->retention_until?->translatedFormat('d F Y') }}.</p>
            </div>
        </div>
    @endif

    {{-- ─── Metadata Cards Grid ─── --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- Left 2 Columns: Metadata Info --}}
        <div class="md:col-span-2 space-y-6">
            <div class="card p-6 space-y-4">
                <h4 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Metadata Dokumen
                </h4>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <dt class="text-gray-500 font-medium">Nomor Arsip (Sistem)</dt>
                        <dd class="font-mono font-bold text-gray-900 mt-0.5">{{ $archive->archive_number }}</dd>
                    </div>

                    <div>
                        <dt class="text-gray-500 font-medium">Nomor Dokumen</dt>
                        <dd class="font-mono font-semibold text-gray-900 mt-0.5">{{ $archive->document_number ?? '—' }}</dd>
                    </div>

                    <div>
                        <dt class="text-gray-500 font-medium">Kategori</dt>
                        <dd class="font-semibold text-gray-900 mt-0.5">{{ $archive->category?->full_name ?? '—' }}</dd>
                    </div>

                    <div>
                        <dt class="text-gray-500 font-medium">Unit / Bidang Kerja</dt>
                        <dd class="font-semibold text-gray-900 mt-0.5">{{ $archive->department?->name ?? '—' }}</dd>
                    </div>

                    <div>
                        <dt class="text-gray-500 font-medium">Tanggal Dokumen</dt>
                        <dd class="font-semibold text-gray-900 mt-0.5">
                            {{ $archive->document_date ? $archive->document_date->translatedFormat('d F Y') : '—' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-gray-500 font-medium">Tahun</dt>
                        <dd class="font-semibold text-gray-900 mt-0.5">Tahun {{ $archive->year }}</dd>
                    </div>

                    <div>
                        <dt class="text-gray-500 font-medium">Kebijakan Retensi</dt>
                        <dd class="font-semibold text-gray-900 mt-0.5">
                            {{ $archive->retentionPolicy?->name ?? 'Permanen' }}
                            @if($archive->retention_until)
                                <span class="text-gray-500 font-normal block text-[11px]">Sampai {{ $archive->retention_until->translatedFormat('d F Y') }}</span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-gray-500 font-medium">Jenis Dokumen</dt>
                        <dd class="font-semibold uppercase text-gray-900 mt-0.5">{{ $archive->document_type }}</dd>
                    </div>

                    <div class="sm:col-span-2">
                        <dt class="text-gray-500 font-medium">Kata Kunci</dt>
                        <dd class="font-medium text-gray-900 mt-0.5">{{ $archive->keywords ?? '—' }}</dd>
                    </div>
                </dl>

                @if($archive->description)
                <div class="border-t border-gray-100 pt-3 mt-3">
                    <dt class="text-xs text-gray-500 font-medium">Deskripsi / Uraian Isi</dt>
                    <dd class="text-xs text-gray-800 mt-1 whitespace-pre-line leading-relaxed">{{ $archive->description }}</dd>
                </div>
                @endif
            </div>
        </div>

        {{-- Right Column: Physical File Info --}}
        <div class="space-y-6">
            <div class="card p-6 space-y-4">
                <h4 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3 flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        Berkas Versi Saat Ini
                    </span>
                    <span class="badge-green font-mono text-[10px]">Versi Terbaru</span>
                </h4>

                <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 text-center space-y-2">
                    <div class="w-12 h-12 rounded-full bg-green-100 text-green-700 flex items-center justify-center mx-auto font-bold text-sm uppercase">
                        {{ $archive->file_extension }}
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-900 break-all" title="{{ $archive->original_filename }}">
                            {{ $archive->original_filename }}
                        </p>
                        <p class="text-[11px] text-gray-500 mt-0.5">{{ $archive->file_size_formatted }}</p>
                    </div>
                    <a href="{{ route('archives.download', $archive) }}" class="btn-success btn-sm w-full inline-flex justify-center items-center gap-1.5 mt-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Unduh Berkas Terbaru
                    </a>
                </div>

                <dl class="space-y-2 text-xs border-t border-gray-100 pt-3">
                    <div>
                        <dt class="text-gray-500 font-medium">Diupload Oleh</dt>
                        <dd class="font-semibold text-gray-900 mt-0.5">{{ $archive->uploader?->name ?? 'Sistem' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 font-medium">Waktu Unggah</dt>
                        <dd class="text-gray-700 font-mono text-[11px] mt-0.5">{{ $archive->created_at ? $archive->created_at->translatedFormat('d F Y H:i') : '—' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

    </div>

    {{-- ─── SECTION: Riwayat Versi Dokumen (Version History) ─── --}}
    <div class="card overflow-hidden">
        <div class="card-header flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                <svg class="w-4.5 h-4.5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Riwayat Versi Dokumen (Version History)
            </h3>
            @can('update', $archive)
            <button @click="newVersionModalOpen = true" class="btn-primary btn-sm flex items-center gap-1">
                + Upload Versi Baru
            </button>
            @endcan
        </div>

        <div class="table-container border-0 rounded-none">
            <table class="table">
                <thead>
                    <tr>
                        <th>Versi</th>
                        <th>Nama Berkas</th>
                        <th>Ukuran</th>
                        <th>Catatan Perubahan</th>
                        <th>Diupload Oleh</th>
                        <th>Tanggal</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($archive->versions as $index => $ver)
                    <tr class="{{ $index === 0 ? 'bg-green-50/30' : '' }}">
                        <td>
                            <div class="flex items-center gap-1.5">
                                <span class="font-mono text-xs font-bold text-gray-900">{{ $ver->version_label }}</span>
                                @if($index === 0)
                                    <span class="badge-green text-[9px] font-mono uppercase font-bold">CURRENT</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <p class="font-bold text-gray-900 text-xs truncate max-w-xs" title="{{ $ver->original_filename }}">{{ $ver->original_filename }}</p>
                        </td>
                        <td>
                            <span class="text-xs text-gray-500 font-mono">{{ $ver->file_size_formatted }}</span>
                        </td>
                        <td>
                            <p class="text-xs text-gray-700 max-w-xs">{{ $ver->change_note ?? '—' }}</p>
                        </td>
                        <td>
                            <span class="text-xs text-gray-800 font-medium">{{ $ver->uploader?->name ?? 'Sistem' }}</span>
                        </td>
                        <td>
                            <span class="text-xs text-gray-600 font-mono block">{{ $ver->created_at ? $ver->created_at->translatedFormat('d M Y H:i') : '—' }}</span>
                        </td>
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                {{-- Download specific version --}}
                                <a href="{{ route('archives.versions.download', [$archive, $ver]) }}" class="btn-ghost btn-sm text-green-600" title="Unduh Versi Ini">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                </a>

                                {{-- Restore old version as new version (Admin Only) --}}
                                @can('restore', $archive)
                                @if($index !== 0)
                                <button @click="openRestoreVersion({{ json_encode($ver) }})" class="btn-ghost btn-sm text-amber-600" title="Pulihkan Versi Ini Sebagai Versi Terbaru">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                </button>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-6 text-gray-500 text-xs">
                            Belum ada riwayat versi tersimpan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ─── SECTION: Activity Timeline ─── --}}
    <div class="card p-6 space-y-4">
        <h4 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Aktivitas & Rekam Jejak Arsip (Audit Log Timeline)
        </h4>

        <div class="divide-y divide-gray-100">
            @forelse($activities as $act)
            <div class="py-3 flex items-start gap-3 text-xs">
                <div class="w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center flex-shrink-0 font-bold text-slate-700 text-[11px] mt-0.5">
                    {{ $act->user ? strtoupper(substr($act->user->name, 0, 1)) : '?' }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-gray-800">
                        <span class="font-bold text-gray-900">{{ $act->user?->name ?? 'Sistem' }}</span>
                        <span class="badge-blue text-[10px] uppercase ml-1 font-mono font-bold">{{ $act->action_label }}</span>
                        <span class="text-gray-600 block mt-0.5">{{ $act->description }}</span>
                    </p>
                    <div class="flex items-center gap-3 text-[10px] text-gray-400 font-mono mt-1">
                        <span>{{ $act->created_at ? $act->created_at->translatedFormat('d M Y H:i:s') : '' }}</span>
                        @if($act->ip_address)
                            <span>· IP: {{ $act->ip_address }}</span>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <p class="text-xs text-gray-500 text-center py-4">Belum ada aktivitas tercatat untuk arsip ini.</p>
            @endforelse
        </div>
    </div>

    {{-- ─── Modal 1: Upload Versi Baru ─── --}}
    <div x-show="newVersionModalOpen" x-transition class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" style="display:none;" @click.self="newVersionModalOpen = false">
        <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl relative" @click.stop>
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                <h3 class="text-base font-bold text-gray-900">Upload Berkas Versi Baru</h3>
                <button @click="newVersionModalOpen = false" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form action="{{ route('archives.versions.store', $archive) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label for="ver_file" class="label">Pilih Berkas Baru <span class="text-red-500">*</span></label>
                    <input id="ver_file" type="file" name="file" required class="input">
                    <p class="text-[11px] text-gray-400 mt-1">Maksimal {{ env('ARSIPARI_MAX_FILE_SIZE_MB', 20) }} MB. Format: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG, ZIP.</p>
                </div>
                <div>
                    <label for="ver_note" class="label">Catatan Perubahan <span class="text-gray-400 font-normal">(Opsional)</span></label>
                    <textarea id="ver_note" name="change_note" rows="3" placeholder="Alasan atau rincian perbaikan berkas..." class="input"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" @click="newVersionModalOpen = false" class="btn-secondary btn-sm">Batal</button>
                    <button type="submit" class="btn-primary btn-sm">Simpan Versi Baru</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ─── Modal 2: Restore Versi Lama (Admin Only) ─── --}}
    <div x-show="restoreVersionModalOpen" x-transition class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" style="display:none;" @click.self="restoreVersionModalOpen = false">
        <div class="bg-white rounded-xl max-w-sm w-full p-6 shadow-xl relative" @click.stop>
            <template x-if="selectedVersion">
                <form :action="`/archives/{{ $archive->id }}/versions/${selectedVersion.id}/restore`" method="POST" class="space-y-4 text-center">
                    @csrf
                    @method('PUT')
                    <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900">Pulihkan Versi Dokumen</h4>
                        <p class="text-xs text-gray-500 mt-1">
                            Apakah Anda yakin ingin memulihkan <strong x-text="selectedVersion.version_label"></strong> (<span x-text="selectedVersion.original_filename"></span>)?
                        </p>
                        <p class="text-[11px] text-amber-700 font-medium mt-1">
                            Sistem akan membuat versi baru berdasarkan file ini tanpa menghapus riwayat versi lainnya.
                        </p>
                    </div>
                    <div class="flex justify-center gap-2 pt-2">
                        <button type="button" @click="restoreVersionModalOpen = false" class="btn-secondary btn-sm">Batal</button>
                        <button type="submit" class="btn-primary btn-sm">Pulihkan Versi Ini</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    {{-- ─── Modal 3: Delete Confirmation (Admin Only) ─── --}}
    <div x-show="deleteModalOpen" x-transition class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" style="display:none;" @click.self="deleteModalOpen = false">
        <div class="bg-white rounded-xl max-w-sm w-full p-6 shadow-xl relative" @click.stop>
            <form action="{{ route('archives.destroy', $archive) }}" method="POST" class="space-y-4 text-center">
                @csrf
                @method('DELETE')
                <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div>
                    <h4 class="font-bold text-gray-900">Konfirmasi Hapus Arsip</h4>
                    <p class="text-xs text-gray-500 mt-1">
                        Apakah Anda yakin ingin menghapus arsip <strong>{{ $archive->title }}</strong>?
                    </p>
                    <p class="text-[11px] text-gray-400 mt-1">
                        Arsip akan dipindahkan ke tempat sampah (soft-delete) dan dapat dipulihkan oleh Admin.
                    </p>
                </div>
                <div class="flex justify-center gap-2 pt-2">
                    <button type="button" @click="deleteModalOpen = false" class="btn-secondary btn-sm">Batal</button>
                    <button type="submit" class="btn-danger btn-sm">Hapus Arsip</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
