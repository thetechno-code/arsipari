<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentTypeRequest;
use App\Http\Requests\UpdateDocumentTypeRequest;
use App\Models\DocumentType;
use App\Services\DocumentTypeService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentTypeController extends Controller
{
    public function __construct(
        protected DocumentTypeService $documentTypeService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', DocumentType::class);

        $query = DocumentType::query();

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

        $documentTypes = $query->orderBy('code')->paginate(15)->withQueryString();

        return view('document-types.index', compact('documentTypes'));
    }

    public function store(StoreDocumentTypeRequest $request): RedirectResponse
    {
        $this->authorize('create', DocumentType::class);

        try {
            $validated = $request->validated();
            $validated['is_active'] = $request->boolean('is_active', true);

            $this->documentTypeService->createDocumentType($validated);

            return redirect()->route('document-types.index')->with('success', 'Jenis dokumen berhasil dibuat.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function update(UpdateDocumentTypeRequest $request, DocumentType $documentType): RedirectResponse
    {
        $this->authorize('update', $documentType);

        try {
            $validated = $request->validated();
            $validated['is_active'] = $request->boolean('is_active', true);

            $this->documentTypeService->updateDocumentType($documentType, $validated);

            return redirect()->route('document-types.index')->with('success', 'Jenis dokumen berhasil diperbarui.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function toggleStatus(DocumentType $documentType): RedirectResponse
    {
        $this->authorize('update', $documentType);

        try {
            $newStatus = $this->documentTypeService->toggleStatus($documentType);
            $msg = $newStatus ? 'Jenis dokumen berhasil diaktifkan.' : 'Jenis dokumen berhasil dinonaktifkan.';
            return back()->with('success', $msg);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(DocumentType $documentType): RedirectResponse
    {
        $this->authorize('delete', $documentType);

        try {
            $this->documentTypeService->deleteDocumentType($documentType);
            return redirect()->route('document-types.index')->with('success', 'Jenis dokumen berhasil dihapus.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
