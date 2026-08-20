@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Overview')

@section('breadcrumb')
    <span class="text-gray-400">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
    </span>
    <span class="text-gray-400">/</span>
    <span class="text-gray-600">Dashboard</span>
@endsection

@section('content')
<div class="space-y-6">

    {{-- ─── Welcome Banner & Quick Discovery Actions ─── --}}
    <div class="bg-gradient-to-r from-primary-800 via-primary-700 to-primary-600 rounded-xl p-6 text-white shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-xl font-bold">
                    Selamat datang, {{ $user->name }}! 👋
                </h3>
                <div class="flex flex-wrap items-center gap-3 text-primary-100 text-xs mt-2">
                    <span class="inline-flex items-center gap-1 bg-white/10 px-2.5 py-1 rounded-md">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Role: {{ $user->role_label }}
                    </span>
                    <span class="inline-flex items-center gap-1 bg-white/10 px-2.5 py-1 rounded-md">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        Unit: {{ $user->department?->name ?? 'Umum / Tanpa Unit' }}
                    </span>
                    <span class="inline-flex items-center gap-1 bg-white/10 px-2.5 py-1 rounded-md">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        {{ now()->translatedFormat('l, d F Y') }}
                    </span>
                </div>
            </div>

            {{-- Quick Action Buttons --}}
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('archives.index') }}" class="bg-white/20 hover:bg-white/30 text-white text-xs font-semibold px-3 py-2 rounded-lg transition-colors flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Cari Arsip
                </a>
                @can('create', App\Models\Archive::class)
                <a href="{{ route('archives.create') }}" class="bg-white text-primary-800 hover:bg-primary-50 text-xs font-bold px-3.5 py-2 rounded-lg shadow-sm transition-colors flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Arsip
                </a>
                @endcan
            </div>
        </div>
    </div>

    {{-- ─── 4 Real KPI Summary Cards ─── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- 1. Total Arsip --}}
        <a href="{{ route('archives.index') }}" class="stat-card hover:border-primary-300 transition-all">
            <div class="stat-icon bg-blue-50 text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-2xl font-bold text-gray-900">{{ number_format($summary['total_archives']) }}</p>
                <p class="text-xs text-gray-500 truncate">Total Arsip Digital</p>
            </div>
        </a>

        {{-- 2. Arsip Tahun Ini --}}
        <a href="{{ route('archives.index', ['year' => $summary['current_year']]) }}" class="stat-card hover:border-primary-300 transition-all">
            <div class="stat-icon bg-green-50 text-green-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-2xl font-bold text-gray-900">{{ number_format($summary['current_year_archives']) }}</p>
                <p class="text-xs text-gray-500 truncate">Arsip Tahun {{ $summary['current_year'] }}</p>
            </div>
        </a>

        {{-- 3. Total Kategori --}}
        <a href="{{ auth()->user()->isAdmin() ? route('categories.index') : route('archives.index') }}" class="stat-card hover:border-primary-300 transition-all">
            <div class="stat-icon bg-purple-50 text-purple-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a2 2 0 012-2z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-2xl font-bold text-gray-900">{{ number_format($summary['total_categories']) }}</p>
                <p class="text-xs text-gray-500 truncate">Kategori Utama</p>
            </div>
        </a>

        {{-- 4. Total Unit / Bidang --}}
        <a href="{{ auth()->user()->isAdmin() ? route('departments.index') : route('archives.index') }}" class="stat-card hover:border-primary-300 transition-all">
            <div class="stat-icon bg-orange-50 text-orange-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-2xl font-bold text-gray-900">{{ number_format($summary['total_departments']) }}</p>
                <p class="text-xs text-gray-500 truncate">Unit / Bidang Kerja</p>
            </div>
        </a>

    </div>

    {{-- ─── Main Content Discovery Section ─── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left 2 Columns: Recent Archives Discovery Table --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="card overflow-hidden">
                <div class="card-header flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-4.5 h-4.5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="text-sm font-semibold text-gray-900">Arsip Terbaru Dilindungi</h3>
                    </div>
                    <a href="{{ route('archives.index') }}" class="text-xs font-semibold text-primary-600 hover:text-primary-700">
                        Lihat Semua Arsip →
                    </a>
                </div>

                <div class="table-container border-0 rounded-none">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nomor Arsip</th>
                                <th>Judul Dokumen</th>
                                <th>Kategori</th>
                                <th>Tanggal</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentArchives as $arc)
                            <tr>
                                <td>
                                    <a href="{{ route('archives.show', $arc) }}" class="font-mono text-xs font-bold text-primary-700 hover:underline">
                                        {{ $arc->archive_number }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('archives.show', $arc) }}" class="font-bold text-gray-900 text-xs hover:text-primary-600 transition-colors line-clamp-1">
                                        {{ $arc->title }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge-blue text-[10px]">
                                        {{ $arc->category?->name ?? '—' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-xs text-gray-600 font-mono">
                                        {{ $arc->document_date ? $arc->document_date->format('d/m/Y') : $arc->year }}
                                    </span>
                                </td>
                                <td>
                                    <div class="flex items-center justify-end">
                                        <a href="{{ route('archives.show', $arc) }}" class="btn-ghost btn-sm text-primary-600 text-xs" title="Lihat Detail">
                                            Detail
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-8 text-gray-500 text-xs">
                                    Belum ada arsip digital tersimpan.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Right Column: Category & Year Breakdown Statistics --}}
        <div class="space-y-6">

            {{-- Statistik Kategori --}}
            <div class="card p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 30a1 1 0 000 2h2a1 1 0 000-2h-2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a2 2 0 012-2z"/></svg>
                        Top Kategori Arsip
                    </h3>
                    <span class="text-[11px] text-gray-400 font-mono">Top {{ count($categoryStatistics) }}</span>
                </div>

                <div class="space-y-3">
                    @forelse($categoryStatistics as $catStat)
                    @php
                        $pct = $summary['total_archives'] > 0 ? round(($catStat->archive_count / $summary['total_archives']) * 100) : 0;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <a href="{{ route('archives.index', ['category_id' => $catStat->id]) }}" class="font-bold text-gray-800 hover:text-primary-600 truncate max-w-[180px]">
                                {{ $catStat->name }}
                            </a>
                            <span class="font-mono text-gray-500 font-bold">{{ $catStat->archive_count }} arsip</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                            <div class="bg-purple-600 h-2 rounded-full transition-all duration-300" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-gray-500 text-center py-4">Belum ada statistik kategori.</p>
                    @endforelse
                </div>
            </div>

            {{-- Statistik Tahun --}}
            <div class="card p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Distribusi Arsip per Tahun
                    </h3>
                    <span class="text-[11px] text-gray-400 font-mono">Tahun</span>
                </div>

                <div class="space-y-2.5">
                    @forelse($yearStatistics as $yStat)
                    @php
                        $pct = $summary['total_archives'] > 0 ? round(($yStat->count / $summary['total_archives']) * 100) : 0;
                    @endphp
                    <a href="{{ route('archives.index', ['year' => $yStat->year]) }}" class="group flex items-center justify-between text-xs p-2 rounded-lg hover:bg-gray-50 transition-colors">
                        <span class="font-mono font-bold text-gray-800 group-hover:text-primary-600">Tahun {{ $yStat->year }}</span>
                        <div class="flex items-center gap-2">
                            <div class="w-24 bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-green-600 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                            </div>
                            <span class="font-mono text-gray-600 font-bold min-w-[40px] text-right">{{ $yStat->count }}</span>
                        </div>
                    </a>
                    @empty
                    <p class="text-xs text-gray-500 text-center py-4">Belum ada statistik tahun.</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
