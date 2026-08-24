<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuestionCategoryController extends Controller
{
    public function index()
    {
        $categories = QuestionCategory::query()
            ->withCount('questions')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.questions.categories', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        QuestionCategory::create([
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug(Str::slug($validated['name'])),
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.question-categories.index')
            ->with('success', 'Subject created.');
    }

    public function update(Request $request, QuestionCategory $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $category->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? $category->description,
            'sort_order' => $validated['sort_order'] ?? $category->sort_order,
        ]);

        return redirect()
            ->route('admin.question-categories.index')
            ->with('success', 'Subject updated.');
    }

    public function toggle(QuestionCategory $category)
    {
        if ($category->is_active && $category->questions()->where('status', 'active')->exists()) {
            return redirect()
                ->route('admin.question-categories.index')
                ->with(
                    'error',
                    "\"{$category->name}\" has active questions and cannot be deactivated. Move or archive those questions first."
                );
        }

        $category->update(['is_active' => ! $category->is_active]);

        $state = $category->is_active ? 'activated' : 'deactivated';

        return redirect()
            ->route('admin.question-categories.index')
            ->with('success', "Subject {$state}.");
    }

    private function uniqueSlug(string $base): string
    {
        $slug = $base !== '' ? $base : 'subject';
        $candidate = $slug;
        $suffix = 2;

        while (QuestionCategory::where('slug', $candidate)->exists()) {
            $candidate = $slug.'-'.$suffix++;
        }

        return $candidate;
    }
}
