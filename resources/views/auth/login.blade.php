@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
<div>
    <h2 class="text-lg font-semibold text-white mb-1">Selamat datang</h2>
    <p class="text-slate-400 text-sm mb-6">Masuk ke akun Anda untuk melanjutkan</p>

    {{-- Validation errors --}}
    @if($errors->any())
    <div class="alert-error mb-5 fade-in" x-data>
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            @foreach($errors->all() as $error)
                <p class="text-sm">{{ $error }}</p>
            @endforeach
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5" x-data="{ loading: false }" @submit="loading = true">
        @csrf

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-slate-300 mb-1.5">
                Alamat Email
            </label>
            <input id="email"
                   type="email"
                   name="email"
                   value="{{ old('email') }}"
                   autocomplete="email"
                   autofocus
                   required
                   placeholder="nama@mtsn1magelang.sch.id"
                   class="block w-full px-4 py-2.5 text-sm
                          bg-white/10 border border-white/20 rounded-lg
                          text-white placeholder:text-slate-500
                          focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent
                          transition-colors duration-150
                          {{ $errors->has('email') ? 'border-red-400' : '' }}">
        </div>

        {{-- Password --}}
        <div>
            <label for="password" class="block text-sm font-medium text-slate-300 mb-1.5">
                Kata Sandi
            </label>
            <div class="relative" x-data="{ show: false }">
                <input id="password"
                       :type="show ? 'text' : 'password'"
                       name="password"
                       autocomplete="current-password"
                       required
                       placeholder="••••••••"
                       class="block w-full px-4 py-2.5 pr-11 text-sm
                              bg-white/10 border border-white/20 rounded-lg
                              text-white placeholder:text-slate-500
                              focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent
                              transition-colors duration-150
                              {{ $errors->has('password') ? 'border-red-400' : '' }}">
                <button type="button"
                        @click="show = !show"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-200">
                    <svg x-show="!show" class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg x-show="show" class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Remember me --}}
        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 cursor-pointer">
                <input id="remember"
                       type="checkbox"
                       name="remember"
                       class="w-4 h-4 rounded border-slate-600 bg-white/10 text-primary-600
                              focus:ring-primary-500 focus:ring-offset-0">
                <span class="text-sm text-slate-400">Ingat saya</span>
            </label>
        </div>

        {{-- Submit --}}
        <button type="submit"
                :disabled="loading"
                class="w-full flex items-center justify-center gap-2 px-4 py-3 text-sm font-semibold
                       bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white
                       rounded-lg transition-all duration-150
                       focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 focus:ring-offset-transparent
                       disabled:opacity-60 disabled:cursor-not-allowed">
            <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" style="display:none;">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span x-text="loading ? 'Memproses...' : 'Masuk'">Masuk</span>
        </button>
    </form>
</div>
@endsection
