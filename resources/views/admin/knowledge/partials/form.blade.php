@props([
    'knowledge' => null,
    'statuses' => [],
    'action' => '',
    'method' => 'POST',
    'submitLabel' => 'Save Knowledge Resource',
    'showSaveAndCreate' => false,
])

@php
    $oldValue = fn (string $field) => old($field, $knowledge?->{$field});
    $authorNameDefault = $knowledge?->author_name ?? auth()->user()->name;
    $currentFeaturedImageIsUrl = is_string($knowledge?->featured_image) && preg_match('#^https?://#i', $knowledge->featured_image);
    $featuredImageUrlValue = old('featured_image_url', $currentFeaturedImageIsUrl ? $knowledge->featured_image : '');
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <x-ui.card>
        <x-form.section
            title="Basic Information"
            description="Core details and publication status for this knowledge resource."
        >
            <div>
                <x-form.label for="name" required>Name</x-form.label>
                <x-ui.input
                    id="name"
                    name="name"
                    value="{{ $oldValue('name') }}"
                    placeholder="Introduction to Hematology"
                    :error="$errors->first('name')"
                    aria-required="true"
                />
            </div>

            <div>
                <x-form.label for="slug">Slug</x-form.label>
                <x-ui.input
                    id="slug"
                    name="slug"
                    value="{{ $oldValue('slug') }}"
                    :error="$errors->first('slug')"
                />
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Auto-generated from the name if left blank.</p>
            </div>

            <div>
                <x-form.label for="short_description">Short description</x-form.label>
                <x-ui.textarea id="short_description" name="short_description" rows="3">{{ $oldValue('short_description') }}</x-ui.textarea>
                <x-form.error for="short_description" />
            </div>

            <div>
                <x-form.label for="status" required>Status</x-form.label>
                <x-ui.select id="status" name="status">
                    @foreach($statuses as $statusOption)
                        <option value="{{ $statusOption->value }}" @selected(old('status', $knowledge?->status?->value ?? 'draft') === $statusOption->value)>
                            {{ $statusOption->label() }}
                        </option>
                    @endforeach
                </x-ui.select>
                <x-form.error for="status" />
            </div>
        </x-form.section>
    </x-ui.card>

    <x-ui.card>
        <x-form.section
            title="Content"
            description="Write the body in Markdown. HTML is sanitized before rendering on the public site."
        >
            <div>
                <div class="mb-2 flex flex-wrap items-center justify-between gap-3">
                    <x-form.label for="content" required>Content (Markdown)</x-form.label>
                    <x-ai.generate-button
                        use-case="draft_knowledge"
                        target="content"
                        label="Draft with AI"
                        title-field="name"
                        outline-field="learning_objectives"
                        source-field="short_description"
                    />
                </div>
                <x-form.markdown-editor id="content" name="content" type="knowledge" rows="16" :error="$errors->first('content')" aria-required="true">{{ $oldValue('content') }}</x-form.markdown-editor>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Supports headings, bold, italics, links, lists, blockquotes, code blocks, and images. Paste or drag & drop an image to insert it.</p>
            </div>
        </x-form.section>
    </x-ui.card>

    <x-ui.card>
        <x-form.section
            title="Classification & Learning"
            description="Topic, difficulty level, and learning objectives for this resource."
        >
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-form.label for="topic_category">Topic category</x-form.label>
                    <x-ui.input id="topic_category" name="topic_category" value="{{ $oldValue('topic_category') }}" placeholder="Hematology" :error="$errors->first('topic_category')" />
                </div>

                <div>
                    <x-form.label for="difficulty_level">Difficulty level</x-form.label>
                    <x-ui.select id="difficulty_level" name="difficulty_level">
                        <option value="">Select difficulty...</option>
                        @foreach(['Beginner', 'Intermediate', 'Advanced'] as $level)
                            <option value="{{ $level }}" @selected(old('difficulty_level', $knowledge?->difficulty_level) === $level)>
                                {{ $level }}
                            </option>
                        @endforeach
                    </x-ui.select>
                    <x-form.error for="difficulty_level" />
                </div>
            </div>

            <div>
                <x-form.label for="learning_objectives">Learning objectives</x-form.label>
                <x-ui.textarea id="learning_objectives" name="learning_objectives" rows="4">{{ $oldValue('learning_objectives') }}</x-ui.textarea>
                <x-form.error for="learning_objectives" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-form.label for="author_name">Author name</x-form.label>
                    <x-ui.input id="author_name" name="author_name" value="{{ old('author_name', $authorNameDefault) }}" :error="$errors->first('author_name')" />
                </div>

                <div>
                    <x-form.label for="reading_time_minutes">Reading time (minutes)</x-form.label>
                    <x-ui.input id="reading_time_minutes" name="reading_time_minutes" type="number" min="1" value="{{ $oldValue('reading_time_minutes') }}" :error="$errors->first('reading_time_minutes')" />
                </div>
            </div>
        </x-form.section>
    </x-ui.card>

    <x-ui.card>
        <x-form.section
            title="Featured Image"
            description="Optional image displayed at the top of the public detail page. Max 2 MB (JPEG, PNG, WebP, or SVG), or paste a direct link to an image hosted elsewhere."
        >
            <div>
                @if($knowledge?->featured_image_url)
                    <img
                        src="{{ $knowledge->featured_image_url }}"
                        alt="Current featured image"
                        class="mb-3 max-h-40 w-auto rounded-lg border border-slate-200 dark:border-slate-700"
                        onerror="this.style.display='none'"
                    >
                @endif
                <x-ui.input id="featured_image" name="featured_image" type="file" accept=".jpg,.jpeg,.png,.webp" :error="$errors->first('featured_image')" />
                <x-form.error for="featured_image" />

                <div class="mt-4">
                    <x-form.label for="featured_image_url">Or use an image URL</x-form.label>
                    <x-ui.input
                        id="featured_image_url"
                        name="featured_image_url"
                        type="url"
                        value="{{ $featuredImageUrlValue }}"
                        placeholder="https://example.com/path/to/image.jpg"
                        :error="$errors->first('featured_image_url')"
                    />
                    <x-form.error for="featured_image_url" />
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Paste a direct http(s) link to an image hosted elsewhere. If you also upload a file, the uploaded file is used.</p>
                </div>

                @if($knowledge?->featured_image)
                    <label class="mt-3 inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                        <input type="checkbox" name="remove_featured_image" value="1" class="rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600">
                        Remove current image
                    </label>
                @endif
            </div>
        </x-form.section>
    </x-ui.card>

    <x-ui.card>
        <x-form.section title="Taxonomy" description="Organize this resource by category and tag.">
            <div>
                <x-form.label>Categories</x-form.label>
                <x-admin.resources.category-selector
                    :selected="old('categories', $knowledge?->categories->pluck('id')->all() ?? [])"
                />
                @error('categories')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-form.label>Tags</x-form.label>
                <x-admin.resources.tag-input
                    :selected="old('tags', $knowledge?->tags->pluck('id')->all() ?? [])"
                />
                @error('tags')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                @enderror
            </div>
        </x-form.section>
    </x-ui.card>

    <x-admin.seo-eeat-fields :model="$knowledge" />

    <div class="flex items-center justify-end gap-3">
        <x-ui.button href="{{ route('admin.knowledge.index') }}" variant="ghost">
            Cancel
        </x-ui.button>
        @if($showSaveAndCreate)
            <x-ui.button type="submit" variant="secondary" name="save_action" value="save_and_create">
                Save and Create Another
            </x-ui.button>
        @endif
        <x-ui.button type="submit" name="save_action" value="save">
            {{ $submitLabel }}
        </x-ui.button>
    </div>
</form>
