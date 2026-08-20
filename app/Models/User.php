<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'department_id',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'is_active'         => 'boolean',
            'password'          => 'hashed',
            'role'              => UserRole::class,
        ];
    }

    // ─────────────────────────────────────────────
    // Role helpers
    // ─────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN || $this->role === UserRole::ADMIN->value;
    }

    public function isOperator(): bool
    {
        return $this->role === UserRole::OPERATOR || $this->role === UserRole::OPERATOR->value;
    }

    public function isViewer(): bool
    {
        return $this->role === UserRole::VIEWER || $this->role === UserRole::VIEWER->value;
    }

    public function hasRole(string|UserRole|array $roles): bool
    {
        $roleValues = array_map(function ($r) {
            return $r instanceof UserRole ? $r->value : (string) $r;
        }, (array) $roles);

        $currentValue = $this->role instanceof UserRole ? $this->role->value : (string) $this->role;

        return in_array($currentValue, $roleValues, true);
    }

    public function getRoleLabelAttribute(): string
    {
        if ($this->role instanceof UserRole) {
            return $this->role->label();
        }

        return match ($this->role) {
            'admin'    => 'Administrator',
            'operator' => 'Operator',
            'viewer'   => 'Viewer',
            default    => ucfirst((string) $this->role),
        };
    }

    // ─────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function uploadedArchives(): HasMany
    {
        return $this->hasMany(Archive::class, 'uploaded_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }
}
