<?php

namespace App\Services;

use App\Models\Archive;
use App\Models\ArchiveVersion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ArchiveFileService
{
    protected string $disk = 'archives';

    /**
     * Store versioned file into private storage: storage/app/private/archives/{year}/{archiveId}/v{versionNumber}/{stored_filename}
     */
    public function storeVersionFile(UploadedFile $file, int $year, string $archiveId, int $versionNumber): array
    {
        $ulid = strtolower((string) Str::ulid());
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension());
        $storedFilename = "{$ulid}.{$extension}";
        $directory = "{$year}/{$archiveId}/v{$versionNumber}";
        $relativePath = "{$directory}/{$storedFilename}";

        // Save file to archives disk
        Storage::disk($this->disk)->putFileAs($directory, $file, $storedFilename);

        return [
            'original_filename' => sanitize_filename($file->getClientOriginalName()),
            'stored_filename'   => $storedFilename,
            'file_path'         => $relativePath,
            'mime_type'         => $file->getMimeType() ?? 'application/octet-stream',
            'file_size'         => $file->getSize(),
        ];
    }

    /**
     * Copy an existing version file for version restoration.
     */
    public function copyVersionFile(string $sourcePath, int $year, string $archiveId, int $newVersionNumber, string $originalFilename): array
    {
        if (! Storage::disk($this->disk)->exists($sourcePath)) {
            throw new \InvalidArgumentException("Berkas versi sumber tidak ditemukan pada {$sourcePath}");
        }

        $ulid = strtolower((string) Str::ulid());
        $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
        $storedFilename = "{$ulid}.{$extension}";
        $directory = "{$year}/{$archiveId}/v{$newVersionNumber}";
        $destinationPath = "{$directory}/{$storedFilename}";

        Storage::disk($this->disk)->copy($sourcePath, $destinationPath);

        return [
            'original_filename' => $originalFilename,
            'stored_filename'   => $storedFilename,
            'file_path'         => $destinationPath,
            'mime_type'         => Storage::disk($this->disk)->mimeType($sourcePath) ?? 'application/octet-stream',
            'file_size'         => Storage::disk($this->disk)->size($sourcePath),
        ];
    }

    /**
     * Check if physical file exists in storage.
     */
    public function fileExists(string $relativePath): bool
    {
        return Storage::disk($this->disk)->exists($relativePath);
    }

    /**
     * Delete file from storage if it exists.
     */
    public function deleteFile(?string $relativePath): void
    {
        if ($relativePath && Storage::disk($this->disk)->exists($relativePath)) {
            Storage::disk($this->disk)->delete($relativePath);

            $dir = dirname($relativePath);
            if ($dir && $dir !== '.' && empty(Storage::disk($this->disk)->files($dir))) {
                Storage::disk($this->disk)->deleteDirectory($dir);
            }
        }
    }

    /**
     * Create a secure download response for Archive or ArchiveVersion streaming from private storage.
     */
    public function downloadStream(Archive|ArchiveVersion $target): StreamedResponse
    {
        return Storage::disk($this->disk)->download(
            $target->file_path,
            $target->original_filename,
            ['Content-Type' => $target->mime_type]
        );
    }
}

/**
 * Sanitize original filename for safe display.
 */
if (! function_exists('sanitize_filename')) {
    function sanitize_filename(string $filename): string
    {
        return preg_replace('/[^a-zA-Z0-9_\-\. ]/', '_', basename($filename));
    }
}
