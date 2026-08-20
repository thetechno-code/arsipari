<?php

namespace App\Http\Controllers;

use App\Models\Archive;
use App\Services\ArchiveService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrashController extends Controller
{
    public function __construct(
        protected ArchiveService $archiveService
    ) {}

    /**
     * Display a listing of soft-deleted archives (Admin Only).
     */
    public function index(Request $request): View
    {
        $query = Archive::onlyTrashed()->with(['category.parent', 'department', 'uploader']);

        if ($search = $request->input('search')) {
            $query->search($search);
        }

        $trashedArchives = $query->latest('deleted_at')->paginate(15)->withQueryString();

        return view('archives.trash', compact('trashedArchives'));
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
            return redirect()->route('archives.trash')
                ->with('success', "Arsip \"{$archive->title}\" ({$archive->archive_number}) berhasil dipulihkan.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
