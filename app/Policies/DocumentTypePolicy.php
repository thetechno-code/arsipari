<?php

namespace App\Policies;

use App\Models\DocumentType;
use App\Models\User;

class DocumentTypePolicy
{
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

    public function view(User $user, DocumentType $documentType): bool
    {
        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, DocumentType $documentType): bool
    {
        return false;
    }

    public function delete(User $user, DocumentType $documentType): bool
    {
        return false;
    }
}
