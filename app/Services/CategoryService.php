<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Models\Category;
use InvalidArgumentException;

class CategoryService
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Create a new category with 2-level hierarchy validation.
     */
    public function createCategory(array $data): Category
    {
        $data['code'] = strtoupper(trim($data['code']));
        $parentId = $data['parent_id'] ?? null;

        if ($parentId) {
            $parent = Category::findOrFail($parentId);

            // Level 3 Prevention: parent cannot be a child itself
            if ($parent->parent_id !== null) {
                throw new InvalidArgumentException('Subkategori tidak boleh dijadikan induk (Maksimal 2 level hirarki).');
            }

            // Parent must be active
            if (! $parent->is_active) {
                throw new InvalidArgumentException('Kategori induk yang dipilih tidak aktif.');
            }
        }

        // Validate name uniqueness within same parent
        $this->validateUniqueNameInParent($data['name'], $parentId);

        $category = Category::create($data);

        $this->auditLogService->logCategoryMutation(
            AuditAction::CATEGORY_CREATE,
            $category,
            "Membuat kategori: {$category->name} ({$category->code})"
        );

        return $category;
    }

    /**
     * Update an existing category with hierarchy & circular reference prevention.
     */
    public function updateCategory(Category $category, array $data): Category
    {
        $data['code'] = strtoupper(trim($data['code']));
        $parentId = array_key_exists('parent_id', $data) ? $data['parent_id'] : $category->parent_id;

        if ($parentId && (int)$parentId !== (int)$category->parent_id) {
            // Prevent setting self as parent
            if ((int)$parentId === (int)$category->id) {
                throw new InvalidArgumentException('Kategori tidak dapat menjadi induk bagi dirinya sendiri.');
            }

            $newParent = Category::findOrFail($parentId);

            // Level 3 Prevention
            if ($newParent->parent_id !== null) {
                throw new InvalidArgumentException('Subkategori tidak boleh dijadikan induk (Maksimal 2 level hirarki).');
            }

            // If category already has children, it cannot become a child itself
            if ($category->children()->count() > 0) {
                throw new InvalidArgumentException('Kategori yang memiliki subkategori tidak dapat dipindahkan menjadi subkategori.');
            }
        }

        $this->validateUniqueNameInParent($data['name'], $parentId, $category->id);

        $oldData = $category->only(['name', 'code', 'parent_id', 'is_active']);
        $category->update($data);

        $this->auditLogService->logCategoryMutation(
            AuditAction::CATEGORY_UPDATE,
            $category,
            "Mengubah kategori: {$category->name}",
            ['old' => $oldData, 'new' => $data]
        );

        return $category;
    }

    /**
     * Toggle active/inactive status safely.
     */
    public function toggleStatus(Category $category): bool
    {
        $newStatus = ! $category->is_active;

        // Parent deactivation safety: cannot deactivate if active children exist
        if (! $newStatus && $category->children()->where('is_active', true)->count() > 0) {
            throw new InvalidArgumentException('Kategori induk tidak dapat dinonaktifkan karena masih memiliki subkategori aktif.');
        }

        $category->update(['is_active' => $newStatus]);

        $statusText = $newStatus ? 'mengaktifkan' : 'menonaktifkan';
        $this->auditLogService->logCategoryMutation(
            AuditAction::CATEGORY_UPDATE,
            $category,
            "Admin {$statusText} kategori: {$category->name}",
            ['is_active' => $newStatus]
        );

        return $newStatus;
    }

    /**
     * Safely delete category if not in use and has no children.
     */
    public function deleteCategory(Category $category): void
    {
        if ($category->children()->count() > 0) {
            throw new InvalidArgumentException('Kategori tidak dapat dihapus karena masih memiliki subkategori.');
        }

        if ($category->archives()->count() > 0) {
            throw new InvalidArgumentException('Kategori tidak dapat dihapus karena masih digunakan oleh arsip.');
        }

        $name = $category->name;
        $category->delete();

        $this->auditLogService->logCategoryMutation(
            AuditAction::CATEGORY_DELETE,
            $category,
            "Menghapus kategori: {$name}"
        );
    }

    /**
     * Helper to validate name uniqueness within same parent.
     */
    protected function validateUniqueNameInParent(string $name, ?int $parentId, ?int $ignoreId = null): void
    {
        $query = Category::where('name', $name);

        if ($parentId === null) {
            $query->whereNull('parent_id');
        } else {
            $query->where('parent_id', $parentId);
        }

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($query->exists()) {
            throw new InvalidArgumentException('Nama kategori sudah digunakan pada tingkat/induk yang sama.');
        }
    }
}
