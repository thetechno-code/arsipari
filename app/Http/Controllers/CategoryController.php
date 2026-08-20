<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryService $categoryService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Category::class);

        $query = Category::with('parent')->withCount('archives');

        // Search term
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status') && $request->input('status') !== null && $request->input('status') !== '') {
            $status = filter_var($request->input('status'), FILTER_VALIDATE_BOOLEAN);
            $query->where('is_active', $status);
        }

        // Level filter (root vs subcategory)
        if ($level = $request->input('level')) {
            if ($level === 'root') {
                $query->roots();
            } elseif ($level === 'sub') {
                $query->whereNotNull('parent_id');
            }
        }

        $categories = $query->orderByRaw('COALESCE(parent_id, id), parent_id IS NOT NULL, code ASC')
            ->paginate(15)
            ->withQueryString();

        $rootCategories = Category::roots()->active()->get();

        return view('categories.index', compact('categories', 'rootCategories'));
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->authorize('create', Category::class);

        try {
            $validated = $request->validated();
            $validated['is_active'] = $request->boolean('is_active', true);

            $this->categoryService->createCategory($validated);

            return redirect()->route('categories.index')->with('success', 'Kategori berhasil dibuat.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        try {
            $validated = $request->validated();
            $validated['is_active'] = $request->boolean('is_active', true);

            $this->categoryService->updateCategory($category, $validated);

            return redirect()->route('categories.index')->with('success', 'Kategori berhasil diperbarui.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function toggleStatus(Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        try {
            $newStatus = $this->categoryService->toggleStatus($category);
            $msg = $newStatus ? 'Kategori berhasil diaktifkan.' : 'Kategori berhasil dinonaktifkan.';
            return back()->with('success', $msg);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        try {
            $this->categoryService->deleteCategory($category);
            return redirect()->route('categories.index')->with('success', 'Kategori berhasil dihapus.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
