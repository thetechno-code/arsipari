<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\AdminResetPasswordRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Department;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Display listing of users with search, filtering, and pagination.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $query = User::with('department');

        // Search filter (name or email)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        // Department filter
        if ($departmentId = $request->input('department_id')) {
            $query->where('department_id', $departmentId);
        }

        // Status filter (active/inactive)
        if ($request->has('status') && $request->input('status') !== null && $request->input('status') !== '') {
            $status = filter_var($request->input('status'), FILTER_VALIDATE_BOOLEAN);
            $query->where('is_active', $status);
        }

        $users = $query->latest('created_at')->paginate(15)->withQueryString();
        $departments = Department::active()->get();
        $roles = UserRole::cases();

        return view('users.index', compact('users', 'departments', 'roles'));
    }

    /**
     * Show form to create a new user.
     */
    public function create(): View
    {
        $this->authorize('create', User::class);

        $departments = Department::active()->get();
        $roles = UserRole::cases();

        return view('users.create', compact('departments', 'roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['password']  = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $user = User::create($validated);

        $this->auditLogService->logUserCreate($user);

        return redirect()->route('users.index')->with('success', 'User berhasil dibuat.');
    }

    /**
     * Display specific user details.
     */
    public function show(User $user): View
    {
        $this->authorize('view', $user);

        $user->load(['department', 'auditLogs' => fn ($q) => $q->recent(10)]);

        return view('users.show', compact('user'));
    }

    /**
     * Show form to edit an existing user.
     */
    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        $departments = Department::active()->get();
        $roles = UserRole::cases();

        return view('users.edit', compact('user', 'departments', 'roles'));
    }

    /**
     * Update specified user in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        // Protection: Admin cannot demote or deactivate themselves
        if ($user->id === auth()->id()) {
            $validated['role']      = UserRole::ADMIN->value;
            $validated['is_active'] = true;
        } else {
            $validated['is_active'] = $request->boolean('is_active');
        }

        $user->update($validated);

        $this->auditLogService->logUserUpdate($user, $validated);

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Toggle active/inactive status of a user.
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        // Self-deactivation protection
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $newStatus = ! $user->is_active;
        $user->update(['is_active' => $newStatus]);

        $this->auditLogService->logUserStatusChange($user, $newStatus);

        $message = $newStatus ? 'User berhasil diaktifkan.' : 'User berhasil dinonaktifkan.';

        return back()->with('success', $message);
    }

    /**
     * Reset user password by Admin.
     */
    public function resetPassword(AdminResetPasswordRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $user->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        $this->auditLogService->logPasswordChange($user, isSelfChange: false);

        return back()->with('success', 'Password berhasil diubah.');
    }
}
