<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Archive extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'archive_number',
        'document_number',
        'title',
        'description',
        'status',
        'retention_policy_id',
        'retention_until',
        'category_id',
        'department_id',
        'year',
        'document_date',
        'document_type',
        'keywords',
        'original_filename',
        'stored_filename',
        'file_path',
        'mime_type',
        'file_size',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'document_date'   => 'date',
            'retention_until' => 'date',
            'year'            => 'integer',
            'file_size'       => 'integer',
        ];
    }

    // ─────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function retentionPolicy(): BelongsTo
    {
        return $this->belongsTo(RetentionPolicy::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ArchiveVersion::class)->orderBy('version_number', 'desc');
    }

    public function latestVersion(): HasOne
    {
        return $this->hasOne(ArchiveVersion::class)->latestOfMany('version_number');
    }

    // ─────────────────────────────────────────────
    // Accessors
    // ─────────────────────────────────────────────

    public function getCurrentVersionAttribute(): ?ArchiveVersion
    {
        return $this->latestVersion;
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

    public function getRetentionStatusAttribute(): string
    {
        if ($this->retentionPolicy?->is_permanent || ! $this->retention_until) {
            return 'permanent';
        }

        $today = now()->startOfDay();
        $until = $this->retention_until->startOfDay();

        if ($until->isPast()) {
            return 'expired';
        }

        $warningDays = (int) env('ARSIPARI_RETENTION_WARNING_DAYS', 90);
        $daysRemaining = (int) $today->diffInDays($until, false);

        if ($daysRemaining >= 0 && $daysRemaining <= $warningDays) {
            return 'due_soon';
        }

        return 'not_due';
    }

    public function getRetentionStatusLabelAttribute(): string
    {
        return match ($this->retention_status) {
            'permanent' => 'Permanen',
            'expired'   => 'Telah Berakhir',
            'due_soon'  => 'Akan Berakhir',
            'not_due'   => 'Aktif',
            default     => '—',
        };
    }

    public function getRetentionStatusBadgeAttribute(): string
    {
        return match ($this->retention_status) {
            'permanent' => 'badge-blue',
            'expired'   => 'badge-red',
            'due_soon'  => 'badge-amber',
            'not_due'   => 'badge-green',
            default     => 'badge-gray',
        };
    }

    // ─────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────

    public function scopeSearch($query, ?string $term)
    {
        if (! $term || trim($term) === '') return $query;

        $term = trim($term);

        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('archive_number', 'like', "%{$term}%")
              ->orWhere('document_number', 'like', "%{$term}%")
              ->orWhere('keywords', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }

    public function scopeFilterCategory($query, ?int $categoryId)
    {
        if (! $categoryId) return $query;
        return $query->where('category_id', $categoryId);
    }

    public function scopeFilterDepartment($query, ?int $departmentId)
    {
        if (! $departmentId) return $query;
        return $query->where('department_id', $departmentId);
    }

    public function scopeFilterYear($query, ?int $year)
    {
        if (! $year) return $query;
        return $query->where('year', $year);
    }

    public function scopeFilterDocumentType($query, ?string $type)
    {
        if (! $type) return $query;
        return $query->where('document_type', strtolower($type));
    }

    public function scopeFilterStatus($query, ?string $status)
    {
        if (! $status) return $query;
        return $query->where('status', strtolower($status));
    }

    public function scopeFilterRetentionPolicy($query, ?int $policyId)
    {
        if (! $policyId) return $query;
        return $query->where('retention_policy_id', $policyId);
    }

    public function scopeFilterRetentionStatus($query, ?string $status)
    {
        if (! $status) return $query;

        $warningDays = (int) env('ARSIPARI_RETENTION_WARNING_DAYS', 90);
        $today = now()->startOfDay()->format('Y-m-d');
        $warningDate = now()->startOfDay()->addDays($warningDays)->format('Y-m-d');

        return match (strtolower($status)) {
            'permanent' => $query->whereHas('retentionPolicy', fn($q) => $q->where('is_permanent', true)),
            'expired'   => $query->whereNotNull('retention_until')->whereDate('retention_until', '<', $today),
            'due_soon'  => $query->whereNotNull('retention_until')
                                 ->whereDate('retention_until', '>=', $today)
                                 ->whereDate('retention_until', '<=', $warningDate),
            'not_due'   => $query->whereNotNull('retention_until')->whereDate('retention_until', '>', $warningDate),
            default     => $query,
        };
    }

    public function scopeFilterDateFrom($query, ?string $dateFrom)
    {
        if (! $dateFrom) return $query;
        return $query->whereDate('document_date', '>=', $dateFrom);
    }

    public function scopeFilterDateTo($query, ?string $dateTo)
    {
        if (! $dateTo) return $query;
        return $query->whereDate('document_date', '<=', $dateTo);
    }

    public function scopeSortBy($query, ?string $sort = 'created_at', ?string $direction = 'desc')
    {
        $allowedSorts = [
            'created_at'    => 'created_at',
            'document_date' => 'document_date',
            'year'          => 'year',
            'title'         => 'title',
        ];

        $column = $allowedSorts[$sort] ?? 'created_at';
        $dir = strtolower($direction) === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($column, $dir);
    }
}
