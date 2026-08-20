<?php

namespace App\Http\Controllers;

use App\Models\Archive;
use App\Models\ArchiveVersion;
use App\Services\ArchiveFileService;
use App\Services\ArchiveService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ArchiveVersionController extends Controller
{
    public function __construct(
        protected ArchiveService $archiveService,
        protected ArchiveFileService $archiveFileService
    ) {}

    /**
     * Store a newly created version for an archive.
     */
    public function store(Request $request, Archive $archive): RedirectResponse
    {
        $this->authorize('update', $archive);

        $maxKb = env('ARSIPARI_MAX_FILE_SIZE_MB', 20) * 1024;
        $maxMb = env('ARSIPARI_MAX_FILE_SIZE_MB', 20);

        $request->validate([
            'file' => [
                'required',
                'file',
                "max:{$maxKb}",
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,zip',
            ],
            'change_note' => ['nullable', 'string', 'max:500'],
        ], [
            'file.required' => 'Berkas versi baru wajib diunggah.',
            'file.max'      => "Ukuran berkas maksimal {$maxMb} MB.",
            'file.mimes'    => 'Format berkas tidak diizinkan. Format yang didukung: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG, ZIP.',
        ]);

        try {
            $version = $this->archiveService->createNewVersion(
                archive: $archive,
                file: $request->file('file'),
                changeNote: $request->input('change_note'),
                user: auth()->user()
            );

            return redirect()->route('archives.show', $archive)
                ->with('success', "Versi baru ({$version->version_label}) berhasil diunggah.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal mengunggah versi baru: ' . $e->getMessage());
        }
    }

    /**
     * Download a specific version file.
     */
    public function download(Archive $archive, ArchiveVersion $version): StreamedResponse|RedirectResponse
    {
        // IDOR protection check
        if ($version->archive_id !== $archive->id) {
            abort(404, 'Versi dokumen tidak ditemukan.');
        }

        $this->authorize('download', $archive);

        if (! $this->archiveFileService->fileExists($version->file_path)) {
            return back()->with('error', 'Berkas versi tidak ditemukan pada penyimpanan fisik.');
        }

        return $this->archiveFileService->downloadStream($version);
    }

    /**
     * Restore an older version as the new current version (Admin only).
     */
    public function restore(Archive $archive, ArchiveVersion $version): RedirectResponse
    {
        // IDOR protection check
        if ($version->archive_id !== $archive->id) {
            abort(404, 'Versi dokumen tidak ditemukan.');
        }

        $this->authorize('restore', $archive);

        try {
            $newVersion = $this->archiveService->restoreVersion(
                archive: $archive,
                oldVersion: $version,
                user: auth()->user()
            );

            return redirect()->route('archives.show', $archive)
                ->with('success', "Versi {$version->version_label} berhasil dipulihkan sebagai versi terbaru ({$newVersion->version_label}).");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memulihkan versi: ' . $e->getMessage());
        }
    }
}
