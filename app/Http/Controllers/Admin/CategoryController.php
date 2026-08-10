<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\AuditLogService;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search'));

        $categories = Category::query()
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $tree = app(CategoryService::class)->buildTree($categories);

        return view('admin.categories.index', compact('tree', 'search'));
    }

    public function create()
    {
        $parentOptions = app(CategoryService::class)->options();

        return view('admin.categories.create', compact('parentOptions'));
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = Category::create($request->validated());

        app(AuditLogService::class)->logWithSubject('taxonomy.category.created', $category, $category->toArray(), $request);

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        $parentOptions = app(CategoryService::class)->options($category);

        return view('admin.categories.edit', compact('category', 'parentOptions'));
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        app(AuditLogService::class)->logWithSubject('taxonomy.category.updated', $category, $category->getChanges(), $request);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Request $request, Category $category)
    {
        if ($category->children()->exists()) {
            return redirect()->route('admin.categories.index')->with('error', 'Category cannot be deleted because it has child categories.');
        }

        $changes = ['name' => $category->name, 'slug' => $category->slug];
        $category->delete();

        app(AuditLogService::class)->logWithSubject('taxonomy.category.deleted', $category, $changes, $request);

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully.');
    }
}
