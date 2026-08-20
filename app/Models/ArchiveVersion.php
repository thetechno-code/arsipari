<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArchiveVersion extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'archive_id',
        'version_number',
        'original_filename',
        'stored_filename',
        'file_path',
        'mime_type',
        'file_size',
        'change_note',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'version_number' => 'integer',
            'file_size'      => 'integer',
        ];
    }

    // ─────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────

    public function archive(): BelongsTo
    {
        return $this->belongsTo(Archive::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // ─────────────────────────────────────────────
    // Accessors
    // ─────────────────────────────────────────────

    public function getVersionLabelAttribute(): string
    {
        return "v{$this->version_number}";
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 2) . ' KB';
        return round($bytes / 1048576, 2) . ' MB';
    }

    public function getFileExtensionAttribute(): string
    {
        return strtolower(pathinfo($this->original_filename, PATHINFO_EXTENSION));
    }
}
