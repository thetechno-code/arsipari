<?php

namespace App\Policies;

use App\Models\Archive;
use App\Models\User;

class ArchivePolicy
{
    /**
     * Admin bypasses all checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Archive $archive): bool
    {
        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return $user->is_active && $user->hasRole(['admin', 'operator']);
    }

    public function update(User $user, Archive $archive): bool
    {
        return $user->is_active && $user->hasRole(['admin', 'operator']);
    }

    public function delete(User $user, Archive $archive): bool
    {
        return $user->is_active && $user->isAdmin();
    }

    public function restore(User $user, Archive $archive): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Archive $archive): bool
    {
        return $user->isAdmin();
    }

    public function download(User $user, Archive $archive): bool
    {
        return $user->is_active;
    }
}
