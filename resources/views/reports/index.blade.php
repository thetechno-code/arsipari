@extends('layouts.app')

@section('title', 'Laporan Arsip Digital')
@section('page-title', 'Laporan Arsip Digital')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600">Dashboard</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-600">Laporan Arsip</span>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Header Title & Action Buttons --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-xl shadow-xs border border-gray-100">
        <div>
            <h3 class="text-lg font-bold text-gray-900">Laporan Rekapitulasi Arsip Digital</h3>
            <p class="text-xs text-gray-500 mt-0.5">Buat rekapitulasi data arsip berdasarkan kriteria periode, kategori, unit, dan retensi</p>
        </div>
        <div class="flex items-center gap-2">
            {{-- Export Excel Button --}}
            <a href="{{ route('reports.archives.excel', request()->query()) }}" class="btn btn-success flex items-center gap-1.5" title="Export Rekap ke Excel (.xlsx)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export Excel
            </a>

            {{-- Export PDF Button --}}
            <a href="{{ route('reports.archives.pdf', request()->query()) }}" class="btn btn-danger flex items-center gap-1.5" title="Export Rekap ke PDF (.pdf)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Export PDF
            </a>
        </div>
    </div>

    {{-- Filter Form Box --}}
    <div class="card p-6">
        <form method="GET" action="{{ route('reports.archives') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                {{-- Date From --}}
                <div>
                    <label for="date_from" class="label">Dari Tanggal Dokumen</label>
                    <input id="date_from" type="date" name="date_from" value="{{ request('date_from') }}" class="input">
                </div>

                {{-- Date To --}}
                <div>
                    <label for="date_to" class="label">Sampai Tanggal Dokumen</label>
                    <input id="date_to" type="date" name="date_to" value="{{ request('date_to') }}" class="input">
                </div>

                {{-- Category Filter --}}
                <div>
                    <label for="category_id" class="label">Kategori Arsip</label>
                    <select id="category_id" name="category_id" class="input">
                        <option value="">-- Semua Kategori --</option>
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
                    <select id="department_id" name="department_id" class="input">
                        <option value="">-- Semua Unit --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }} ({{ $dept->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Document Type Filter --}}
                <div>
                    <label for="document_type" class="label">Jenis Dokumen</label>
                    <select id="document_type" name="document_type" class="input">
                        <option value="">-- Semua Jenis Dokumen --</option>
                        @foreach($documentTypes as $dt)
                            <option value="{{ strtolower($dt->code) }}" {{ request('document_type') == strtolower($dt->code) ? 'selected' : '' }}>
                                {{ $dt->name }} ({{ $dt->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status Operational --}}
                <div>
                    <label for="status" class="label">Status Operational</label>
                    <select id="status" name="status" class="input">
                        <option value="">-- Semua Status --</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>

                {{-- Retention Policy Filter --}}
                <div>
                    <label for="retention_policy_id" class="label">Kebijakan Retensi</label>
                    <select id="retention_policy_id" name="retention_policy_id" class="input">
                        <option value="">-- Semua Kebijakan Retensi --</option>
                        @foreach($retentionPolicies as $rp)
                            <option value="{{ $rp->id }}" {{ request('retention_policy_id') == $rp->id ? 'selected' : '' }}>
                                {{ $rp->name }} {{ $rp->is_permanent ? '(Permanen)' : "({$rp->duration_years} Tahun)" }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Retention Status --}}
                <div>
                    <label for="retention_status" class="label">Status Masa Retensi</label>
                    <select id="retention_status" name="retention_status" class="input">
                        <option value="">-- Semua Status Retensi --</option>
                        <option value="permanent" {{ request('retention_status') === 'permanent' ? 'selected' : '' }}>Permanen</option>
                        <option value="not_due" {{ request('retention_status') === 'not_due' ? 'selected' : '' }}>Aktif (Belum Jatuh Tempo)</option>
                        <option value="due_soon" {{ request('retention_status') === 'due_soon' ? 'selected' : '' }}>Akan Berakhir (<= 90 Hari)</option>
                        <option value="expired" {{ request('retention_status') === 'expired' ? 'selected' : '' }}>Telah Berakhir</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                <a href="{{ route('reports.archives') }}" class="btn-secondary btn-sm">Reset Filter</a>
                <button type="submit" class="btn-primary btn-sm flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Tampilkan Laporan
                </button>
            </div>
        </form>
    </div>

    {{-- Aggregate Summary Metric Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="card p-4 text-center">
            <p class="text-xs text-gray-500 font-medium">Total Arsip</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($summary['total']) }}</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-xs text-green-600 font-medium">Status Aktif</p>
            <p class="text-2xl font-bold text-green-700 mt-1">{{ number_format($summary['active']) }}</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-xs text-red-600 font-medium">Tidak Aktif</p>
            <p class="text-2xl font-bold text-red-700 mt-1">{{ number_format($summary['inactive']) }}</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-xs text-blue-600 font-medium">Retensi Permanen</p>
            <p class="text-2xl font-bold text-blue-700 mt-1">{{ number_format($summary['permanent']) }}</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-xs text-amber-600 font-medium">Akan Berakhir</p>
            <p class="text-2xl font-bold text-amber-700 mt-1">{{ number_format($summary['due_soon']) }}</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-xs text-red-600 font-medium">Telah Berakhir</p>
            <p class="text-2xl font-bold text-red-700 mt-1">{{ number_format($summary['expired']) }}</p>
        </div>
    </div>

    {{-- Report Result Table --}}
    <div class="card overflow-hidden">
        <div class="card-header flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900">
                Pratinjau Hasil Laporan ({{ $archives->total() }} Data Ditemukan)
            </h3>
            <span class="text-xs text-gray-500 font-mono">Halaman {{ $archives->currentPage() }} dari {{ $archives->lastPage() }}</span>
        </div>

        <div class="table-container border-0 rounded-none">
            <table class="table">
                <thead>
                    <tr>
                        <th width="4%">No</th>
                        <th>Nomor Arsip</th>
                        <th>Judul Arsip</th>
                        <th>Kategori</th>
                        <th>Unit / Bidang</th>
                        <th>Tgl Dokumen</th>
                        <th>Jenis</th>
                        <th>Status</th>
                        <th>Retensi</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($archives as $index => $archive)
                    <tr>
                        <td class="text-center font-mono text-xs text-gray-500">
                            {{ $archives->firstItem() + $index }}
                        </td>
                        <td>
                            <code class="text-xs font-mono font-bold text-primary-800 bg-primary-50 px-2 py-0.5 rounded border border-primary-200">
                                {{ $archive->archive_number }}
                            </code>
                        </td>
                        <td>
                            <a href="{{ route('archives.show', $archive) }}" class="font-bold text-gray-900 hover:text-primary-600 text-xs block max-w-xs truncate" title="{{ $archive->title }}">
                                {{ $archive->title }}
                            </a>
                            @if($archive->document_number)
                                <span class="text-[11px] font-mono text-gray-400 block">{{ $archive->document_number }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="text-xs text-gray-800 font-medium">{{ $archive->category?->name ?? '—' }}</span>
                        </td>
                        <td>
                            <span class="text-xs text-gray-800 font-medium">{{ $archive->department?->name ?? '—' }}</span>
                        </td>
                        <td>
                            <span class="text-xs text-gray-700 font-mono block">
                                {{ $archive->document_date ? $archive->document_date->translatedFormat('d M Y') : '—' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge-gray uppercase text-[10px] font-mono font-bold">
                                {{ $archive->document_type }}
                            </span>
                        </td>
                        <td>
                            @if($archive->status === 'active')
                                <span class="badge-green text-[10px]">Aktif</span>
                            @else
                                <span class="badge-red text-[10px]">Non-Aktif</span>
                            @endif
                        </td>
                        <td>
                            <span class="{{ $archive->retention_status_badge }} text-[10px]">
                                {{ $archive->retention_status_label }}
                            </span>
                        </td>
                        <td>
                            <div class="flex items-center justify-end">
                                <a href="{{ route('archives.show', $archive) }}" class="btn-ghost btn-sm text-primary-600 text-xs" title="Lihat Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-10 text-gray-500 text-xs">
                            Tidak ada arsip yang sesuai dengan kriteria filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($archives->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $archives->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
