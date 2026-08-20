<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Login') — ARSIPARI</title>
    <meta name="description" content="ARSIPARI — Sistem Manajemen Arsip Digital MTsN 1 Magelang">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900">

    <div class="min-h-full flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            {{-- Logo / Branding --}}
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-primary-600 shadow-lg mb-4">
                    <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-white tracking-tight">ARSIPARI</h1>
                <p class="text-slate-400 text-sm mt-1">Sistem Manajemen Arsip Digital</p>
                <p class="text-slate-500 text-xs mt-0.5">MTsN 1 Magelang</p>
            </div>

            {{-- Card --}}
            <div class="bg-white/10 backdrop-blur-xl border border-white/10 rounded-2xl shadow-2xl p-8">
                @yield('content')
            </div>

            <p class="text-center text-slate-600 text-xs mt-6">
                &copy; {{ date('Y') }} MTsN 1 Magelang. All rights reserved.
            </p>
        </div>
    </div>

</body>
</html>
