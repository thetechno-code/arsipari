@extends('layouts.app')

@section('title', 'Audit Trail Aktivitas')
@section('page-title', 'Audit Trail Aktivitas Sistem')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600">Dashboard</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-600">Audit Log</span>
@endsection

@section('content')
<div class="space-y-6" x-data="{
    detailModalOpen: false,
    selectedLog: null,
    openDetail(log) {
        this.selectedLog = log;
        this.detailModalOpen = true;
    }
}">

    {{-- ─── Header ─── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-bold text-gray-900">Audit Trail System & Audit Log</h3>
            <p class="text-xs text-gray-500 mt-0.5">Rekam jejak audit aktivitas pengguna, otentikasi, mutasi arsip, dan keamanan sistem (Read-Only)</p>
        </div>
    </div>

    {{-- ─── Filters & Search ─── --}}
    <div class="card p-4">
        <form method="GET" action="{{ route('audit-logs.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 text-xs">

            {{-- Search --}}
            <div>
                <label for="search" class="sr-only">Cari</label>
                <input id="search" type="text" name="search" value="{{ request('search') }}" placeholder="Cari deskripsi, target ID, IP..." class="input">
            </div>

            {{-- User Filter --}}
            <div>
                <label for="user_id" class="sr-only">Pengguna</label>
                <select id="user_id" name="user_id" class="input">
                    <option value="">Semua Pengguna</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }} ({{ $u->role_label }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Action Filter --}}
            <div>
                <label for="action" class="sr-only">Aksi</label>
                <select id="action" name="action" class="input">
                    <option value="">Semua Aksi Audit</option>
                    @foreach($actions as $act)
                        <option value="{{ $act->value }}" {{ request('action') === $act->value ? 'selected' : '' }}>
                            {{ $act->label() }} ({{ $act->value }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Date From --}}
            <div>
                <input id="date_from" type="date" name="date_from" value="{{ request('date_from') }}" class="input" placeholder="Dari Tanggal">
            </div>

            {{-- Date To & Filter Buttons --}}
            <div class="flex items-center gap-2">
                <input id="date_to" type="date" name="date_to" value="{{ request('date_to') }}" class="input flex-1" placeholder="Sampai Tanggal">
                <button type="submit" class="btn-primary btn-sm">Cari</button>
                @if(request()->hasAny(['search', 'user_id', 'action', 'date_from', 'date_to']))
                    <a href="{{ route('audit-logs.index') }}" class="btn-secondary btn-sm">Reset</a>
                @endif
            </div>

        </form>
    </div>

    {{-- ─── Audit Trail Feed Table ─── --}}
    <div class="card overflow-hidden">
        <div class="table-container border-0 rounded-none">
            <table class="table">
                <thead>
                    <tr>
                        <th>Waktu / Tanggal</th>
                        <th>Pengguna</th>
                        <th>Aksi</th>
                        <th>Deskripsi Aktivitas</th>
                        <th>IP Address</th>
                        <th class="text-right">Rincian</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auditLogs as $log)
                    <tr>
                        <td>
                            <span class="text-xs text-gray-900 font-mono font-bold block">
                                {{ $log->created_at ? $log->created_at->translatedFormat('d M Y H:i:s') : '—' }}
                            </span>
                            <span class="text-[10px] text-gray-400">{{ $log->created_at ? $log->created_at->diffForHumans() : '' }}</span>
                        </td>
                        <td>
                            <p class="font-bold text-gray-900 text-xs">{{ $log->user?->name ?? 'Sistem' }}</p>
                            <p class="text-[10px] text-gray-500">{{ $log->user?->role_label ?? '—' }}</p>
                        </td>
                        <td>
                            <span class="badge-blue text-[10px] uppercase font-mono font-bold">
                                {{ $log->action_label }}
                            </span>
                        </td>
                        <td>
                            <p class="text-xs text-gray-800 line-clamp-2 max-w-md">{{ $log->description }}</p>
                            @if($log->entity_id)
                                <p class="text-[10px] font-mono text-gray-400 mt-0.5">Target ID: {{ $log->entity_id }}</p>
                            @endif
                        </td>
                        <td>
                            <code class="text-[11px] font-mono text-gray-600 bg-gray-100 px-2 py-0.5 rounded">
                                {{ $log->ip_address ?? '—' }}
                            </code>
                        </td>
                        <td>
                            <div class="flex items-center justify-end">
                                <button @click="openDetail({{ json_encode($log) }})" class="btn-ghost btn-sm text-primary-600 text-xs flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Metadata
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-10 text-gray-500 text-sm">
                            Belum ada rekam jejak audit log.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($auditLogs->hasPages())
        <div class="p-4 border-t border-gray-100 bg-gray-50">
            {{ $auditLogs->links() }}
        </div>
        @endif
    </div>

    {{-- ─── Modal Detail Metadata JSON ─── --}}
    <div x-show="detailModalOpen" x-transition class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" style="display:none;" @click.self="detailModalOpen = false">
        <div class="bg-white rounded-xl max-w-lg w-full p-6 shadow-xl relative" @click.stop>
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                <h3 class="text-base font-bold text-gray-900">Rincian Metadata Audit Log</h3>
                <button @click="detailModalOpen = false" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <template x-if="selectedLog">
                <div class="space-y-4 text-xs">
                    <div class="grid grid-cols-2 gap-3 bg-gray-50 p-3 rounded-lg border border-gray-200">
                        <div>
                            <span class="text-gray-500">Waktu:</span>
                            <p class="font-mono font-bold text-gray-900" x-text="selectedLog.created_at"></p>
                        </div>
                        <div>
                            <span class="text-gray-500">Aksi:</span>
                            <p class="font-mono font-bold text-primary-700 uppercase" x-text="selectedLog.action"></p>
                        </div>
                        <div>
                            <span class="text-gray-500">User:</span>
                            <p class="font-bold text-gray-900" x-text="selectedLog.user ? selectedLog.user.name : 'Sistem'"></p>
                        </div>
                        <div>
                            <span class="text-gray-500">IP Address:</span>
                            <p class="font-mono text-gray-900" x-text="selectedLog.ip_address || '—'"></p>
                        </div>
                    </div>

                    <div>
                        <span class="text-gray-500 font-semibold">Deskripsi:</span>
                        <p class="text-gray-900 mt-0.5 font-medium" x-text="selectedLog.description"></p>
                    </div>

                    <div>
                        <span class="text-gray-500 font-semibold">User Agent:</span>
                        <p class="font-mono text-[11px] text-gray-600 break-all mt-0.5" x-text="selectedLog.user_agent || '—'"></p>
                    </div>

                    <div>
                        <span class="text-gray-500 font-semibold">JSON Metadata:</span>
                        <pre class="bg-slate-900 text-green-400 p-3 rounded-lg font-mono text-[11px] overflow-x-auto mt-1 max-h-48" x-text="JSON.stringify(selectedLog.metadata, null, 2)"></pre>
                    </div>

                    <div class="flex justify-end pt-2 border-t border-gray-100">
                        <button type="button" @click="detailModalOpen = false" class="btn-secondary btn-sm">Tutup</button>
                    </div>
                </div>
            </template>
        </div>
    </div>

</div>
@endsection
