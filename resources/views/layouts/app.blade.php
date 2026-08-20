<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — ARSIPARI</title>
    <meta name="description" content="ARSIPARI — Sistem Manajemen Arsip Digital MTsN 1 Magelang">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="h-full bg-gray-50" x-data="{ sidebarOpen: false }">

    {{-- Mobile sidebar overlay --}}
    <div x-show="sidebarOpen"
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false"
         class="fixed inset-0 bg-black/60 z-20 lg:hidden"
         style="display:none;">
    </div>

    <div class="flex h-full">
        {{-- ─── SIDEBAR ─── --}}
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-30 w-64 bg-sidebar-bg flex flex-col transform transition-transform duration-200 ease-in-out
                      lg:relative lg:translate-x-0 lg:flex-shrink-0">

            {{-- Sidebar Header --}}
            <div class="flex items-center gap-3 px-5 py-5 border-b border-white/5">
                <div class="w-9 h-9 rounded-xl bg-primary-600 flex items-center justify-center flex-shrink-0 shadow-md">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-white leading-tight">ARSIPARI</p>
                    <p class="text-xs text-slate-500 leading-tight">MTsN 1 Magelang</p>
                </div>
                <button @click="sidebarOpen = false" class="ml-auto text-slate-500 hover:text-white lg:hidden">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Navigation Links --}}
            <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">

                {{-- Dashboard --}}
                <a href="{{ route('dashboard') }}"
                   class="sidebar-link {{ request()->routeIs('dashboard*') ? 'active' : '' }}">
                    <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Dashboard
                </a>

                {{-- MASTER DATA Section (Admin Only) --}}
                @if(auth()->user()->isAdmin())
                <p class="sidebar-heading">Master Data</p>

                <a href="{{ route('categories.index') }}"
                   class="sidebar-link {{ request()->routeIs('categories*') ? 'active' : '' }}">
                    <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a2 2 0 012-2z"/>
                    </svg>
                    Kategori Arsip
                </a>

                <a href="{{ route('departments.index') }}"
                   class="sidebar-link {{ request()->routeIs('departments*') ? 'active' : '' }}">
                    <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Unit / Bidang
                </a>

                <a href="{{ route('document-types.index') }}"
                   class="sidebar-link {{ request()->routeIs('document-types*') ? 'active' : '' }}">
                    <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Jenis Dokumen
                </a>

                <a href="{{ route('retention-policies.index') }}"
                   class="sidebar-link {{ request()->routeIs('retention-policies*') ? 'active' : '' }}">
                    <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Retensi Arsip
                </a>
                @endif

                {{-- ARSIP Section --}}
                <p class="sidebar-heading">Arsip Digital</p>

                <a href="{{ route('archives.index') }}"
                   class="sidebar-link {{ request()->routeIs('archives.index') || request()->routeIs('archives.show') || request()->routeIs('archives.edit') ? 'active' : '' }}">
                    <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                    Semua Arsip
                </a>

                @can('create', App\Models\Archive::class)
                <a href="{{ route('archives.create') }}"
                   class="sidebar-link {{ request()->routeIs('archives.create') ? 'active' : '' }}">
                    <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Arsip
                </a>
                @endcan

                <a href="{{ route('reports.archives') }}"
                   class="sidebar-link {{ request()->routeIs('reports*') ? 'active' : '' }}">
                    <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Laporan Arsip
                </a>

                @if(auth()->user()->isAdmin())
                <a href="{{ route('archives.trash') }}"
                   class="sidebar-link {{ request()->routeIs('archives.trash*') ? 'active' : '' }}">
                    <svg class="w-4.5 h-4.5 flex-shrink-0 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Tempat Sampah
                </a>
                @endif

                {{-- PENGGUNA Section (Admin Only) --}}
                @if(auth()->user()->isAdmin())
                <p class="sidebar-heading">Pengguna</p>

                <a href="{{ route('users.index') }}"
                   class="sidebar-link {{ request()->routeIs('users*') ? 'active' : '' }}">
                    <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Pengguna
                </a>
                @endif

                {{-- AKTIVITAS & SISTEM Section (Admin Only) --}}
                @if(auth()->user()->isAdmin())
                <p class="sidebar-heading">Aktivitas & Sistem</p>

                <a href="{{ route('audit-logs.index') }}"
                   class="sidebar-link {{ request()->routeIs('audit-logs*') ? 'active' : '' }}">
                    <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Audit Trail
                </a>

                <a href="{{ route('admin.backups.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.backups*') ? 'active' : '' }}">
                    <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                    </svg>
                    Backup Sistem
                </a>

                <a href="{{ route('admin.system.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.system*') ? 'active' : '' }}">
                    <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Kesehatan Sistem
                </a>
                @endif

            </nav>

            {{-- Sidebar Footer --}}
            <div class="px-3 py-3 border-t border-white/5" x-data="{ open: false }">
                <div class="px-3 pb-2 text-[11px] text-slate-500 font-mono flex items-center justify-between">
                    <span>ARSIPARI v{{ config('arsipari.version', '1.0.0') }}</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500" title="System Online"></span>
                </div>

                <button @click="open = !open"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition-all duration-150">
                    <div class="w-8 h-8 rounded-full bg-primary-600 flex items-center justify-center flex-shrink-0 text-white text-xs font-bold shadow-sm">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 text-left min-w-0">
                        <p class="text-sm font-medium text-slate-300 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-slate-500 truncate">{{ auth()->user()->role_label }}</p>
                    </div>
                    <svg class="w-4 h-4 flex-shrink-0 transition-transform duration-150"
                         :class="open ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="open"
                     @click.away="open = false"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-95"
                     class="mt-2 space-y-1 bg-slate-900/90 rounded-lg p-1.5 border border-white/10"
                     style="display:none;">
                    <a href="{{ route('profile.index') }}"
                       class="flex items-center gap-2.5 px-3 py-2 rounded-md text-xs font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Profil Saya
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center gap-2.5 px-3 py-2 rounded-md text-xs font-medium text-red-400 hover:bg-red-950/40 hover:text-red-300 transition-colors">
                            <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- ─── MAIN CONTENT ─── --}}
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            {{-- Top Navbar --}}
            <header class="bg-white border-b border-gray-200 flex-shrink-0">
                <div class="flex items-center gap-4 px-4 sm:px-6 h-16">
                    <button @click="sidebarOpen = true"
                            class="text-gray-500 hover:text-gray-700 lg:hidden"
                            aria-label="Buka menu">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <div class="flex-1 min-w-0">
                        <h2 class="text-base font-semibold text-gray-900 truncate">
                            @yield('page-title', 'Dashboard')
                        </h2>
                        @hasSection('breadcrumb')
                        <nav class="flex items-center gap-1.5 text-xs text-gray-500 mt-0.5">
                            @yield('breadcrumb')
                        </nav>
                        @endif
                    </div>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('profile.index') }}"
                           class="hidden sm:flex items-center gap-2 hover:opacity-80 transition-opacity">
                            <div class="w-8 h-8 rounded-full bg-primary-600 text-white text-xs font-bold flex items-center justify-center">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <div class="text-left">
                                <p class="text-xs font-semibold text-gray-800 leading-tight">{{ auth()->user()->name }}</p>
                                <p class="text-[10px] text-gray-500 leading-tight">{{ auth()->user()->role_label }}</p>
                            </div>
                        </a>
                    </div>
                </div>
            </header>

            {{-- ─── Modern Floating Toast Notifications (2026 Look) ─── --}}
            <div class="fixed top-5 right-5 z-50 flex flex-col gap-3 max-w-md w-full px-4 pointer-events-none sm:px-0">

                {{-- SUCCESS FLASH TOAST --}}
                @if(session('success'))
                <div x-data="{ show: true, progress: 100 }"
                     x-init="
                        const interval = setInterval(() => {
                            progress -= 2;
                            if (progress <= 0) {
                                show = false;
                                clearInterval(interval);
                            }
                        }, 100);
                     "
                     x-show="show"
                     x-transition:enter="transition ease-out duration-300 transform"
                     x-transition:enter-start="opacity-0 translate-y-[-12px] scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-200 transform"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-y-[-12px] scale-95"
                     class="pointer-events-auto relative overflow-hidden bg-white/95 backdrop-blur-md rounded-xl border border-emerald-200/90 shadow-xl shadow-emerald-950/5 p-4 flex items-start gap-3.5"
                     role="alert">
                    
                    <div class="w-9 h-9 rounded-lg bg-emerald-500/15 border border-emerald-500/20 text-emerald-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <h4 class="text-xs font-bold text-emerald-950 uppercase tracking-wider">Berhasil</h4>
                        <p class="text-xs text-emerald-900/90 font-medium leading-relaxed mt-0.5">{{ session('success') }}</p>
                    </div>

                    <button @click="show = false" class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-lg hover:bg-slate-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>

                    <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-emerald-100">
                        <div class="h-full bg-emerald-500 transition-all duration-100 ease-linear" :style="`width: ${progress}%`"></div>
                    </div>
                </div>
                @endif

                {{-- ERROR FLASH TOAST --}}
                @if(session('error'))
                <div x-data="{ show: true, progress: 100 }"
                     x-init="
                        const interval = setInterval(() => {
                            progress -= 1.6;
                            if (progress <= 0) {
                                show = false;
                                clearInterval(interval);
                            }
                        }, 100);
                     "
                     x-show="show"
                     x-transition:enter="transition ease-out duration-300 transform"
                     x-transition:enter-start="opacity-0 translate-y-[-12px] scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-200 transform"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-y-[-12px] scale-95"
                     class="pointer-events-auto relative overflow-hidden bg-white/95 backdrop-blur-md rounded-xl border border-rose-200/90 shadow-xl shadow-rose-950/5 p-4 flex items-start gap-3.5"
                     role="alert">
                    
                    <div class="w-9 h-9 rounded-lg bg-rose-500/15 border border-rose-500/20 text-rose-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <h4 class="text-xs font-bold text-rose-950 uppercase tracking-wider">Perhatian / Kendala</h4>
                        <p class="text-xs text-rose-900/90 font-medium leading-relaxed mt-0.5">{{ session('error') }}</p>
                    </div>

                    <button @click="show = false" class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-lg hover:bg-slate-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>

                    <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-rose-100">
                        <div class="h-full bg-rose-500 transition-all duration-100 ease-linear" :style="`width: ${progress}%`"></div>
                    </div>
                </div>
                @endif

                {{-- WARNING FLASH TOAST --}}
                @if(session('warning'))
                <div x-data="{ show: true, progress: 100 }"
                     x-init="
                        const interval = setInterval(() => {
                            progress -= 2;
                            if (progress <= 0) {
                                show = false;
                                clearInterval(interval);
                            }
                        }, 100);
                     "
                     x-show="show"
                     x-transition:enter="transition ease-out duration-300 transform"
                     x-transition:enter-start="opacity-0 translate-y-[-12px] scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-200 transform"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-y-[-12px] scale-95"
                     class="pointer-events-auto relative overflow-hidden bg-white/95 backdrop-blur-md rounded-xl border border-amber-200/90 shadow-xl shadow-amber-950/5 p-4 flex items-start gap-3.5"
                     role="alert">
                    
                    <div class="w-9 h-9 rounded-lg bg-amber-500/15 border border-amber-500/20 text-amber-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <h4 class="text-xs font-bold text-amber-950 uppercase tracking-wider">Peringatan</h4>
                        <p class="text-xs text-amber-900/90 font-medium leading-relaxed mt-0.5">{{ session('warning') }}</p>
                    </div>

                    <button @click="show = false" class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-lg hover:bg-slate-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>

                    <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-amber-100">
                        <div class="h-full bg-amber-500 transition-all duration-100 ease-linear" :style="`width: ${progress}%`"></div>
                    </div>
                </div>
                @endif

                {{-- INFO FLASH TOAST --}}
                @if(session('info'))
                <div x-data="{ show: true, progress: 100 }"
                     x-init="
                        const interval = setInterval(() => {
                            progress -= 2;
                            if (progress <= 0) {
                                show = false;
                                clearInterval(interval);
                            }
                        }, 100);
                     "
                     x-show="show"
                     x-transition:enter="transition ease-out duration-300 transform"
                     x-transition:enter-start="opacity-0 translate-y-[-12px] scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-200 transform"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-y-[-12px] scale-95"
                     class="pointer-events-auto relative overflow-hidden bg-white/95 backdrop-blur-md rounded-xl border border-sky-200/90 shadow-xl shadow-sky-950/5 p-4 flex items-start gap-3.5"
                     role="alert">
                    
                    <div class="w-9 h-9 rounded-lg bg-sky-500/15 border border-sky-500/20 text-sky-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <h4 class="text-xs font-bold text-sky-950 uppercase tracking-wider">Informasi</h4>
                        <p class="text-xs text-sky-900/90 font-medium leading-relaxed mt-0.5">{{ session('info') }}</p>
                    </div>

                    <button @click="show = false" class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-lg hover:bg-slate-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>

                    <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-sky-100">
                        <div class="h-full bg-sky-500 transition-all duration-100 ease-linear" :style="`width: ${progress}%`"></div>
                    </div>
                </div>
                @endif

            </div>

            {{-- Main Content --}}
            <main class="flex-1 overflow-y-auto p-4 sm:p-6">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
