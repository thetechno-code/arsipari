<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\Department;
use App\Services\DepartmentService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function __construct(
        protected DepartmentService $departmentService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Department::class);

        $query = Department::withCount(['users', 'archives']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->input('status') !== null && $request->input('status') !== '') {
            $status = filter_var($request->input('status'), FILTER_VALIDATE_BOOLEAN);
            $query->where('is_active', $status);
        }

        $departments = $query->orderBy('code')->paginate(15)->withQueryString();

        return view('departments.index', compact('departments'));
    }

    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        $this->authorize('create', Department::class);

        try {
            $validated = $request->validated();
            $validated['is_active'] = $request->boolean('is_active', true);

            $this->departmentService->createDepartment($validated);

            return redirect()->route('departments.index')->with('success', 'Unit/bidang berhasil dibuat.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function update(UpdateDepartmentRequest $request, Department $department): RedirectResponse
    {
        $this->authorize('update', $department);

        try {
            $validated = $request->validated();
            $validated['is_active'] = $request->boolean('is_active', true);

            $this->departmentService->updateDepartment($department, $validated);

            return redirect()->route('departments.index')->with('success', 'Unit/bidang berhasil diperbarui.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function toggleStatus(Department $department): RedirectResponse
    {
        $this->authorize('update', $department);

        try {
            $newStatus = $this->departmentService->toggleStatus($department);
            $msg = $newStatus ? 'Unit/bidang berhasil diaktifkan.' : 'Unit/bidang berhasil dinonaktifkan.';
            return back()->with('success', $msg);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Department $department): RedirectResponse
    {
        $this->authorize('delete', $department);

        try {
            $this->departmentService->deleteDepartment($department);
            return redirect()->route('departments.index')->with('success', 'Unit/bidang berhasil dihapus.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
