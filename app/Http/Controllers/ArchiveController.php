<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreArchiveRequest;
use App\Http\Requests\UpdateArchiveRequest;
use App\Models\Archive;
use App\Models\Category;
use App\Models\Department;
use App\Models\DocumentType;
use App\Models\RetentionPolicy;
use App\Services\ArchiveFileService;
use App\Services\ArchiveService;
use App\Services\AuditLogService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ArchiveController extends Controller
{
    public function __construct(
        protected ArchiveService $archiveService,
        protected ArchiveFileService $archiveFileService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Display a listing of archives with search, advanced filters, sorting, and pagination.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Archive::class);

        $request->validate([
            'search'              => ['nullable', 'string', 'max:255'],
            'category_id'         => ['nullable', 'exists:categories,id'],
            'department_id'       => ['nullable', 'exists:departments,id'],
            'year'                => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'document_type'       => ['nullable', 'string', 'max:30'],
            'status'              => ['nullable', 'string', 'in:active,inactive'],
            'retention_policy_id' => ['nullable', 'exists:retention_policies,id'],
            'retention_status'    => ['nullable', 'string', 'in:permanent,not_due,due_soon,expired'],
            'date_from'           => ['nullable', 'date'],
            'date_to'             => ['nullable', 'date', 'after_or_equal:date_from'],
            'sort'                => ['nullable', 'string', 'in:created_at,document_date,year,title'],
            'direction'           => ['nullable', 'string', 'in:asc,desc'],
        ]);

        $query = Archive::with(['category.parent', 'department', 'uploader', 'retentionPolicy', 'latestVersion']);

        if ($search = $request->input('search')) {
            $query->search($search);
        }

        if ($categoryId = $request->input('category_id')) {
            $query->filterCategory($categoryId);
        }

        if ($departmentId = $request->input('department_id')) {
            $query->filterDepartment($departmentId);
        }

        if ($year = $request->input('year')) {
            $query->filterYear($year);
        }

        if ($documentType = $request->input('document_type')) {
            $query->filterDocumentType($documentType);
        }

        if ($status = $request->input('status')) {
            $query->filterStatus($status);
        }

        if ($retentionPolicyId = $request->input('retention_policy_id')) {
            $query->filterRetentionPolicy($retentionPolicyId);
        }

        if ($retentionStatus = $request->input('retention_status')) {
            $query->filterRetentionStatus($retentionStatus);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->filterDateFrom($dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->filterDateTo($dateTo);
        }

        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');
        $query->sortBy($sort, $direction);

        $archives = $query->paginate(15)->withQueryString();

        // Master data options for filter panel
        $categories        = Category::with('children')->roots()->active()->get();
        $departments       = Department::active()->get();
        $documentTypes     = DocumentType::active()->get();
        $retentionPolicies = RetentionPolicy::active()->get();
        $years             = Archive::select('year')->distinct()->orderBy('year', 'desc')->pluck('year');

        return view('archives.index', compact(
            'archives',
            'categories',
            'departments',
            'documentTypes',
            'retentionPolicies',
            'years'
        ));
    }

    /**
     * Show form to create a new archive.
     */
    public function create(): View
    {
        $this->authorize('create', Archive::class);

        $categories        = Category::with('children')->roots()->active()->get();
        $departments       = Department::active()->get();
        $documentTypes     = DocumentType::active()->get();
        $retentionPolicies = RetentionPolicy::active()->get();

        return view('archives.create', compact('categories', 'departments', 'documentTypes', 'retentionPolicies'));
    }

    /**
     * Store a newly created archive in storage.
     */
    public function store(StoreArchiveRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $file = $request->file('file');

            $archive = $this->archiveService->createArchive(
                metadata: $validated,
                file: $file,
                user: auth()->user()
            );

            return redirect()->route('archives.show', $archive)
                ->with('success', "Arsip \"{$archive->title}\" berhasil disimpan dengan nomor {$archive->archive_number}.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal menyimpan arsip: ' . $e->getMessage());
        }
    }

    /**
     * Display specific archive details, versions history, and activity timeline.
     */
    public function show(Archive $archive): View
    {
        $this->authorize('view', $archive);

        $archive->load(['category.parent', 'department', 'uploader', 'retentionPolicy', 'versions.uploader']);

        // Fetch activity timeline related to this archive
        $activities = \App\Models\AuditLog::with('user')
            ->where('entity_type', Archive::class)
            ->where('entity_id', $archive->id)
            ->latest('created_at')
            ->take(15)
            ->get();

        return view('archives.show', compact('archive', 'activities'));
    }

    /**
     * Show form to edit an archive.
     */
    public function edit(Archive $archive): View
    {
        $this->authorize('update', $archive);

        $archive->load(['category', 'department', 'retentionPolicy']);
        $categories        = Category::with('children')->roots()->active()->get();
        $departments       = Department::active()->get();
        $documentTypes     = DocumentType::active()->get();
        $retentionPolicies = RetentionPolicy::active()->get();

        return view('archives.edit', compact('archive', 'categories', 'departments', 'documentTypes', 'retentionPolicies'));
    }

    /**
     * Update specified archive in storage.
     */
    public function update(UpdateArchiveRequest $request, Archive $archive): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $file = $request->file('file');

            $updatedArchive = $this->archiveService->updateArchive(
                archive: $archive,
                metadata: $validated,
                file: $file,
                changeNote: $request->input('change_note'),
                user: auth()->user()
            );

            return redirect()->route('archives.show', $updatedArchive)
                ->with('success', 'Metadata arsip berhasil diperbarui.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal memperbarui arsip: ' . $e->getMessage());
        }
    }

    /**
     * Toggle operational status (active <-> inactive). Admin only.
     */
    public function toggleStatus(Archive $archive): RedirectResponse
    {
        $this->authorize('restore', $archive);

        $newStatus = $archive->status === 'active' ? 'inactive' : 'active';
        $this->archiveService->updateStatus($archive, $newStatus, auth()->user());

        $statusLabel = $newStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Status operasional arsip \"{$archive->archive_number}\" berhasil {$statusLabel}.");
    }

    /**
     * Download secure file from private storage stream.
     */
    public function download(Archive $archive): StreamedResponse|RedirectResponse
    {
        $this->authorize('download', $archive);

        if (! $this->archiveFileService->fileExists($archive->file_path)) {
            logger()->error("Archive physical file missing: {$archive->file_path} for Archive ID {$archive->id}");
            return back()->with('error', 'File arsip tidak ditemukan pada penyimpanan fisik. Silakan hubungi administrator.');
        }

        // Audit log download
        $this->auditLogService->logArchiveDownload($archive);

        return $this->archiveFileService->downloadStream($archive);
    }

    /**
     * Soft delete specified archive.
     */
    public function destroy(Archive $archive): RedirectResponse
    {
        $this->authorize('delete', $archive);

        $title = $archive->title;
        $this->archiveService->deleteArchive($archive);

        return redirect()->route('archives.index')
            ->with('success', "Arsip \"{$title}\" berhasil dipindahkan ke status terhapus.");
    }

    /**
     * Restore a soft-deleted archive.
     */
    public function restore(string $id): RedirectResponse
    {
        $archive = Archive::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $archive);

        try {
            $this->archiveService->restoreArchive($archive);
            return redirect()->route('archives.show', $archive)
                ->with('success', "Arsip \"{$archive->title}\" berhasil dipulihkan.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
