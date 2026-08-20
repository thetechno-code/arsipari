<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Show the login form.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'Alamat email wajib diisi.',
            'email.email'       => 'Format alamat email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        // Rate limiting: max 5 attempts per minute per email+IP
        $throttleKey = Str::lower($request->email) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
                ]);
        }

        $credentials = $request->only('email', 'password');
        $remember    = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            RateLimiter::hit($throttleKey, 60);

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Email atau password salah.']);
        }

        $user = Auth::user();

        // Check active status
        if (! $user->is_active) {
            Auth::logout();
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Akun Anda tidak aktif. Silakan hubungi administrator.']);
        }

        RateLimiter::clear($throttleKey);

        // Update last_login_at
        $user->update(['last_login_at' => now()]);

        // Audit log login
        $this->auditLogService->logLogin($user);

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            $this->auditLogService->logLogout($user);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
