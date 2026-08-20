@extends('layouts.app')

@section('title', 'Manajemen Backup Sistem')
@section('page-title', 'Backup & Pemulihan Sistem')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600">Dashboard</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-600">Backup Sistem</span>
@endsection

@section('content')
<div class="space-y-6" x-data="{ deleteBackupModal: false, targetBackup: '' }">

    {{-- Header Card --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-xl shadow-xs border border-gray-100">
        <div>
            <h3 class="text-lg font-bold text-gray-900">Paket Backup Sistem & Data Arsip</h3>
            <p class="text-xs text-gray-500 mt-0.5">Cadangkan database SQLite, berkas arsip digital private, dan manifest konfigurasi dalam paket ZIP</p>
        </div>
        <div>
            <form action="{{ route('admin.backups.store') }}" method="POST">
                @csrf
                <button type="submit" class="btn-primary flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                    + Buat Backup Baru
                </button>
            </form>
        </div>
    </div>

    {{-- Info Alert Banner --}}
    <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl text-blue-900 text-xs flex items-start gap-3">
        <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div class="space-y-1">
            <p class="font-bold">Informasi Kebijakan Backup & Restore:</p>
            <ul class="list-disc list-inside space-y-0.5 text-blue-800">
                <li>Sistem secara otomatis menyimpan maksimal <strong>{{ $retentionLimit }} file backup terbaru</strong>. Backup terlama akan dibersihkan otomatis.</li>
                <li>Guna keamanan tingkat tinggi, pemulihan data (*Restore*) dijalankan melalui terminal server dengan perintah: <code class="bg-blue-100 px-1.5 py-0.5 rounded font-mono font-bold text-blue-900">php artisan arsipari:restore</code></li>
                <li>Disarankan mengunduh berkas backup secara berkala ke media penyimpanan eksternal (NAS / Harddisk Eksternal).</li>
            </ul>
        </div>
    </div>

    {{-- Table of Backups --}}
    <div class="card overflow-hidden">
        <div class="table-container border-0 rounded-none">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal & Waktu</th>
                        <th>Nama Paket Backup (ZIP)</th>
                        <th>Ukuran</th>
                        <th>Jumlah Berkas</th>
                        <th>Status / Jenis</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($backups as $b)
                    <tr>
                        <td>
                            <span class="text-xs font-mono font-bold text-gray-900 block">{{ $b['created_at'] }}</span>
                        </td>
                        <td>
                            <code class="text-xs font-mono font-bold text-primary-800 bg-primary-50 px-2 py-1 rounded border border-primary-200 block truncate max-w-sm" title="{{ $b['filename'] }}">
                                {{ $b['filename'] }}
                            </code>
                        </td>
                        <td>
                            <span class="text-xs font-mono text-gray-700">{{ $b['size_formatted'] }}</span>
                        </td>
                        <td>
                            <span class="text-xs text-gray-800 font-medium">
                                {{ $b['manifest']['archive_files_count'] ?? '—' }} Berkas
                            </span>
                        </td>
                        <td>
                            @if($b['is_emergency'])
                                <span class="badge-amber text-[10px] uppercase font-mono font-bold">Pre-Restore Emergency</span>
                            @else
                                <span class="badge-green text-[10px] uppercase font-mono font-bold">OK / Normal</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                {{-- Download Button --}}
                                <a href="{{ route('admin.backups.download', $b['filename']) }}" class="btn-ghost btn-sm text-green-600 flex items-center gap-1" title="Unduh Paket ZIP">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    Unduh
                                </a>

                                {{-- Delete Button --}}
                                <button @click="targetBackup = '{{ $b['filename'] }}'; deleteBackupModal = true" class="btn-ghost btn-sm text-red-600 flex items-center gap-1" title="Hapus File Backup">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-10 text-gray-500 text-xs">
                            Belum ada paket backup tersimpan. Klik tombol "+ Buat Backup Baru" di atas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Delete Modal Confirmation --}}
    <div x-show="deleteBackupModal" x-transition class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" style="display:none;" @click.self="deleteBackupModal = false">
        <div class="bg-white rounded-xl max-w-sm w-full p-6 shadow-xl relative" @click.stop>
            <form :action="`/admin/backups/${targetBackup}`" method="POST" class="space-y-4 text-center">
                @csrf
                @method('DELETE')
                <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div>
                    <h4 class="font-bold text-gray-900">Konfirmasi Hapus Backup</h4>
                    <p class="text-xs text-gray-500 mt-1">
                        Apakah Anda yakin ingin menghapus file backup <strong x-text="targetBackup" class="font-mono text-gray-900"></strong>?
                    </p>
                </div>
                <div class="flex justify-center gap-2 pt-2">
                    <button type="button" @click="deleteBackupModal = false" class="btn-secondary btn-sm">Batal</button>
                    <button type="submit" class="btn-danger btn-sm">Hapus Permanent</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
