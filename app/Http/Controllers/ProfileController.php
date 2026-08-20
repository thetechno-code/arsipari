<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Show self profile view.
     */
    public function index(): View
    {
        $user = auth()->user()->load('department');
        return view('profile.index', compact('user'));
    }

    /**
     * Update user profile (name only).
     */
    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $user->update($request->validated());

        $this->auditLogService->logUserUpdate($user, ['name' => $user->name]);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Change user own password.
     */
    public function changePassword(ChangePasswordRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $user->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        $this->auditLogService->logPasswordChange($user, isSelfChange: true);

        return back()->with('success', 'Password berhasil diubah.');
    }
}
