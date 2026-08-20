<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RetentionPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'duration_years',
        'is_permanent',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'duration_years' => 'integer',
            'is_permanent'   => 'boolean',
            'is_active'      => 'boolean',
        ];
    }

    public function archives(): HasMany
    {
        return $this->hasMany(Archive::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
