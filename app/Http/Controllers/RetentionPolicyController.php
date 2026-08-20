<?php

namespace App\Http\Controllers;

use App\Enums\AuditAction;
use App\Models\RetentionPolicy;
use App\Services\AuditLogService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RetentionPolicyController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Display listing of retention policies.
     */
    public function index(Request $request): View
    {
        $query = RetentionPolicy::withCount('archives');

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        if ($request->has('status') && $request->status !== null && $request->status !== '') {
            $query->where('is_active', filter_var($request->status, FILTER_VALIDATE_BOOLEAN));
        }

        $policies = $query->orderBy('is_permanent', 'desc')
            ->orderBy('duration_years', 'asc')
            ->paginate(15)
            ->withQueryString();

        return view('retention-policies.index', compact('policies'));
    }

    /**
     * Store new retention policy.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:100', 'unique:retention_policies,name'],
            'duration_years' => ['nullable', 'required_if:is_permanent,0', 'integer', 'min:1', 'max:100'],
            'is_permanent'   => ['nullable', 'boolean'],
            'description'    => ['nullable', 'string', 'max:500'],
        ], [
            'name.required'             => 'Nama retensi wajib diisi.',
            'name.unique'               => 'Nama retensi sudah terdaftar.',
            'duration_years.required_if' => 'Durasi tahun wajib diisi jika bukan permanen.',
        ]);

        $isPermanent = $request->boolean('is_permanent');

        $policy = RetentionPolicy::create([
            'name'           => $validated['name'],
            'duration_years' => $isPermanent ? null : $validated['duration_years'],
            'is_permanent'   => $isPermanent,
            'description'    => $validated['description'] ?? null,
            'is_active'      => true,
        ]);

        $this->auditLogService->record(
            AuditAction::RETENTION_POLICY_CREATE,
            "Membuat kebijakan retensi baru: {$policy->name}",
            $policy
        );

        return redirect()->route('retention-policies.index')
            ->with('success', 'Kebijakan retensi berhasil dibuat.');
    }

    /**
     * Update existing retention policy.
     */
    public function update(Request $request, RetentionPolicy $retentionPolicy): RedirectResponse
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:100', 'unique:retention_policies,name,' . $retentionPolicy->id],
            'duration_years' => ['nullable', 'required_if:is_permanent,0', 'integer', 'min:1', 'max:100'],
            'is_permanent'   => ['nullable', 'boolean'],
            'description'    => ['nullable', 'string', 'max:500'],
        ]);

        $isPermanent = $request->boolean('is_permanent');

        $retentionPolicy->update([
            'name'           => $validated['name'],
            'duration_years' => $isPermanent ? null : $validated['duration_years'],
            'is_permanent'   => $isPermanent,
            'description'    => $validated['description'] ?? null,
        ]);

        $this->auditLogService->record(
            AuditAction::RETENTION_POLICY_UPDATE,
            "Mengubah kebijakan retensi: {$retentionPolicy->name}",
            $retentionPolicy
        );

        return redirect()->route('retention-policies.index')
            ->with('success', 'Kebijakan retensi berhasil diperbarui.');
    }

    /**
     * Toggle active status.
     */
    public function toggleStatus(RetentionPolicy $retentionPolicy): RedirectResponse
    {
        $newStatus = ! $retentionPolicy->is_active;
        $retentionPolicy->update(['is_active' => $newStatus]);

        $this->auditLogService->record(
            AuditAction::RETENTION_POLICY_UPDATE,
            "Admin mengubah status retensi {$retentionPolicy->name} menjadi " . ($newStatus ? 'Aktif' : 'Tidak Aktif'),
            $retentionPolicy
        );

        return redirect()->route('retention-policies.index')
            ->with('success', 'Status kebijakan retensi berhasil diperbarui.');
    }

    /**
     * Delete retention policy safely.
     */
    public function destroy(RetentionPolicy $retentionPolicy): RedirectResponse
    {
        if ($retentionPolicy->archives()->exists()) {
            return back()->with('error', 'Kebijakan retensi tidak dapat dihapus karena masih digunakan oleh arsip. Anda dapat menonaktifkan statusnya.');
        }

        $name = $retentionPolicy->name;
        $retentionPolicy->delete();

        $this->auditLogService->record(
            AuditAction::DELETE,
            "Menghapus kebijakan retensi: {$name}"
        );

        return redirect()->route('retention-policies.index')
            ->with('success', "Kebijakan retensi \"{$name}\" berhasil dihapus.");
    }
}
