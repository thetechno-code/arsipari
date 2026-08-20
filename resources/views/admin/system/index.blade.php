@extends('layouts.app')

@section('title', 'Informasi Sistem & Kesehatan Server')
@section('page-title', 'Informasi & Kesehatan Sistem')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600">Dashboard</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-600">Sistem & Server</span>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="bg-white p-6 rounded-xl shadow-xs border border-gray-100 flex items-center justify-between">
        <div>
            <h3 class="text-lg font-bold text-gray-900">Informasi & Kesehatan Server ARSIPARI</h3>
            <p class="text-xs text-gray-500 mt-0.5">Pantau status lingkungan server, ekstensi PHP, hak akses direktori penyimpanan, dan database</p>
        </div>
        <div>
            <span class="badge-green font-mono font-bold text-xs px-3 py-1">Sistem Normal</span>
        </div>
    </div>

    {{-- Info Cards Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- Card 1: Versi Software --}}
        <div class="card p-6 space-y-4">
            <h4 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Versi Aplikasi & Framework
            </h4>
            <dl class="space-y-3 text-xs">
                <div class="flex justify-between items-center">
                    <dt class="text-gray-500">Versi ARSIPARI</dt>
                    <dd class="font-mono font-bold text-primary-700 bg-primary-50 px-2 py-0.5 rounded border border-primary-200">v{{ $appVersion }}</dd>
                </div>
                <div class="flex justify-between items-center">
                    <dt class="text-gray-500">Versi Framework</dt>
                    <dd class="font-mono font-semibold text-gray-900">Laravel {{ $laravelVersion }}</dd>
                </div>
                <div class="flex justify-between items-center">
                    <dt class="text-gray-500">Versi PHP Server</dt>
                    <dd class="font-mono font-semibold text-gray-900">PHP {{ $phpVersion }}</dd>
                </div>
            </dl>
        </div>

        {{-- Card 2: Status Database --}}
        <div class="card p-6 space-y-4">
            <h4 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                Database SQLite Internal
            </h4>
            <dl class="space-y-3 text-xs">
                <div class="flex justify-between items-center">
                    <dt class="text-gray-500">Driver Database</dt>
                    <dd class="font-mono font-bold text-gray-900 uppercase">{{ $dbDriver }}</dd>
                </div>
                <div class="flex justify-between items-center">
                    <dt class="text-gray-500">Koneksi Database</dt>
                    <dd>
                        @if($dbStatus === 'OK')
                            <span class="badge-green text-[10px]">OK (Terhubung)</span>
                        @else
                            <span class="badge-red text-[10px]">Error</span>
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between items-center">
                    <dt class="text-gray-500">Ukuran File Database</dt>
                    <dd class="font-mono text-gray-800 font-semibold">{{ round($dbSize / 1024, 2) }} KB</dd>
                </div>
            </dl>
        </div>

        {{-- Card 3: Status I/O Storage --}}
        <div class="card p-6 space-y-4">
            <h4 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                Hak Akses Penyimpanan File
            </h4>
            <dl class="space-y-3 text-xs">
                <div class="flex justify-between items-center">
                    <dt class="text-gray-500">Folder Arsip Private</dt>
                    <dd>
                        @if($archiveWritable)
                            <span class="badge-green text-[10px]">Writable (Dapat Ditulis)</span>
                        @else
                            <span class="badge-red text-[10px]">Permission Denied</span>
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between items-center">
                    <dt class="text-gray-500">Folder Backup Sistem</dt>
                    <dd>
                        @if($backupWritable)
                            <span class="badge-green text-[10px]">Writable (Dapat Ditulis)</span>
                        @else
                            <span class="badge-red text-[10px]">Permission Denied</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

    </div>

    {{-- PHP Extensions Table --}}
    <div class="card overflow-hidden">
        <div class="card-header">
            <h4 class="text-sm font-bold text-gray-900">Status Ekstensi PHP Terinstal</h4>
        </div>
        <div class="table-container border-0 rounded-none">
            <table class="table">
                <thead>
                    <tr>
                        <th>Ekstensi PHP</th>
                        <th>Fungsi Utama dalam ARSIPARI</th>
                        <th class="text-right">Status Server</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requiredExtensions as $ext => $installed)
                    <tr>
                        <td><code class="font-mono font-bold text-xs text-gray-900">php-{{ $ext }}</code></td>
                        <td>
                            @switch($ext)
                                @case('pdo_sqlite') Penggerak database SQLite internal @break
                                @case('mbstring') Pemrosesan string multibyte UTF-8 @break
                                @case('zip') Pembuatan & kompresi paket backup ZIP @break
                                @case('xml') Renderer dokumen XML & PDF Export @break
                                @case('fileinfo') Deteksi jenis MIME fisik berkas aman @break
                                @case('gd') Pemrosesan gambar & thumbnail @break
                                @default Modul pendukung PHP @break
                            @endswitch
                        </td>
                        <td class="text-right">
                            @if($installed)
                                <span class="badge-green text-[10px]">Terinstal</span>
                            @else
                                <span class="badge-red text-[10px]">Tidak Ditemukan</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
