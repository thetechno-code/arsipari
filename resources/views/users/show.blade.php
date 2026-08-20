@extends('layouts.app')

@section('title', 'Detail Pengguna — ' . $user->name)
@section('page-title', 'Detail Pengguna')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600">Dashboard</a>
    <span class="text-gray-400">/</span>
    <a href="{{ route('users.index') }}" class="text-gray-400 hover:text-gray-600">Pengguna</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-600">Detail</span>
@endsection

@section('content')
<div class="max-w-4xl space-y-6">

    <div class="card">
        <div class="card-header flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-primary-600 text-white font-bold text-base flex items-center justify-center flex-shrink-0">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">{{ $user->name }}</h3>
                    <p class="text-xs text-gray-500">{{ $user->email }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('users.edit', $user) }}" class="btn-secondary btn-sm">Edit</a>
                <a href="{{ route('users.index') }}" class="btn-ghost btn-sm">Kembali</a>
            </div>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                    <span class="text-xs text-gray-500 block">Role</span>
                    <span class="font-semibold text-gray-900">{{ $user->role_label }}</span>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                    <span class="text-xs text-gray-500 block">Unit / Bidang</span>
                    <span class="font-semibold text-gray-900">{{ $user->department?->name ?? 'Umum' }}</span>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                    <span class="text-xs text-gray-500 block">Status</span>
                    @if($user->is_active)
                        <span class="badge-green">Aktif</span>
                    @else
                        <span class="badge-red">Tidak Aktif</span>
                    @endif
                </div>
                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                    <span class="text-xs text-gray-500 block">Login Terakhir</span>
                    <span class="font-semibold text-gray-900">{{ $user->last_login_at ? $user->last_login_at->translatedFormat('d M Y, H:i') : 'Belum Pernah' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Audit Log History for this user --}}
    <div class="card">
        <div class="card-header">
            <h3 class="text-sm font-bold text-gray-900">Riwayat Aktivitas Terakhir</h3>
        </div>
        <div class="divide-y divide-gray-100 text-xs">
            @forelse($user->auditLogs as $log)
            <div class="p-3 flex items-center justify-between">
                <div>
                    <span class="font-bold text-gray-800">[{{ $log->action_label }}]</span>
                    <span class="text-gray-600 ml-1">{{ $log->description }}</span>
                </div>
                <span class="text-gray-400">{{ $log->created_at->diffForHumans() }}</span>
            </div>
            @empty
            <div class="p-6 text-center text-gray-500">
                Belum ada aktivitas tercatat.
            </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
