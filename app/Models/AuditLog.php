<?php

namespace App\Models;

use App\Enums\AuditAction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'description',
        'ip_address',
        'user_agent',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata'   => 'array',
            'created_at' => 'datetime',
            'action'     => AuditAction::class,
        ];
    }

    // ─────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─────────────────────────────────────────────
    // Static Helper
    // ─────────────────────────────────────────────

    public static function record(
        string|AuditAction $action,
        ?string $description = null,
        ?Model $entity = null,
        ?array $metadata = null
    ): void {
        $actionValue = $action instanceof AuditAction ? $action->value : $action;

        static::create([
            'user_id'     => auth()->id(),
            'action'      => $actionValue,
            'entity_type' => $entity ? get_class($entity) : null,
            'entity_id'   => $entity?->getKey(),
            'description' => $description,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
            'metadata'    => $metadata,
            'created_at'  => now(),
        ]);
    }

    // ─────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────

    public function scopeRecent($query, int $limit = 50)
    {
        return $query->latest('created_at')->limit($limit);
    }

    // ─────────────────────────────────────────────
    // Accessors
    // ─────────────────────────────────────────────

    public function getActionLabelAttribute(): string
    {
        if ($this->action instanceof AuditAction) {
            return $this->action->label();
        }

        return match ($this->action) {
            'login'      => 'Login',
            'logout'     => 'Logout',
            'create'     => 'Membuat',
            'update'     => 'Mengubah',
            'delete'     => 'Menghapus',
            'download'   => 'Mengunduh',
            'restore'    => 'Memulihkan',
            default      => ucfirst((string) $this->action),
        };
    }
}
