<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTagRequest;
use App\Http\Requests\Admin\UpdateTagRequest;
use App\Models\Tag;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search'));

        $tags = Tag::query()
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(config('labgoto.pagination.per_page'))
            ->withQueryString();

        return view('admin.tags.index', compact('tags', 'search'));
    }

    public function create()
    {
        return view('admin.tags.create');
    }

    public function store(StoreTagRequest $request)
    {
        $tag = Tag::create($request->validated());

        app(AuditLogService::class)->logWithSubject('taxonomy.tag.created', $tag, $tag->toArray(), $request);

        return redirect()->route('admin.tags.index')->with('success', 'Tag created successfully.');
    }

    public function edit(Tag $tag)
    {
        return view('admin.tags.edit', compact('tag'));
    }

    public function update(UpdateTagRequest $request, Tag $tag)
    {
        $tag->update($request->validated());

        app(AuditLogService::class)->logWithSubject('taxonomy.tag.updated', $tag, $tag->getChanges(), $request);

        return redirect()->route('admin.tags.index')->with('success', 'Tag updated successfully.');
    }

    public function destroy(Request $request, Tag $tag)
    {
        $changes = ['name' => $tag->name, 'slug' => $tag->slug];
        $tag->delete();

        app(AuditLogService::class)->logWithSubject('taxonomy.tag.deleted', $tag, $changes, $request);

        return redirect()->route('admin.tags.index')->with('success', 'Tag deleted successfully.');
    }
}
