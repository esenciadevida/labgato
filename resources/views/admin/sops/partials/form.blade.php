@props([
    'sop' => null,
    'statuses' => [],
    'action' => '',
    'method' => 'POST',
    'submitLabel' => 'Save SOP',
    'showSaveAndCreate' => false,
])

@php
    $oldValue = fn (string $field) => old($field, $sop?->{$field});
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6" x-data="{ isNewVersion: false }">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <x-ui.card>
        <x-form.section
            title="Basic Information"
            description="Core details and publication status for this standard operating procedure."
        >
            <div>
                <x-form.label for="name" required>Name</x-form.label>
                <x-ui.input
                    id="name"
                    name="name"
                    value="{{ $oldValue('name') }}"
                    placeholder="Daily Hematology Analyzer Quality Control"
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
                <div class="mb-2 flex flex-wrap items-center justify-between gap-3">
                    <x-form.label for="short_description">Short description</x-form.label>
                    <x-ai.generate-button
                        use-case="summarize_sop"
                        target="short_description"
                        label="Summarize Procedure"
                        title-field="name"
                        source-field="content"
                        :min-source-chars="100"
                    />
                </div>
                <x-ui.textarea id="short_description" name="short_description" rows="3">{{ $oldValue('short_description') }}</x-ui.textarea>
                <x-form.error for="short_description" />
            </div>

            <div>
                <x-form.label for="status" required>Status</x-form.label>
                <x-ui.select id="status" name="status">
                    @foreach($statuses as $statusOption)
                        <option value="{{ $statusOption->value }}" @selected(old('status', $sop?->status?->value ?? 'draft') === $statusOption->value)>
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
                <x-form.label for="content" required>Content (Markdown)</x-form.label>
                <x-form.markdown-editor id="content" name="content" type="sop" rows="16" :error="$errors->first('content')" aria-required="true">{{ $oldValue('content') }}</x-form.markdown-editor>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Supports headings, bold, italics, links, lists, blockquotes, code blocks, and images. Paste or drag & drop an image to insert it.</p>
            </div>
        </x-form.section>
    </x-ui.card>

    <x-ui.card>
        <x-form.section
            title="Procedure Metadata"
            description="Department, versioning, authorship, and review information."
        >
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-form.label for="department">Department</x-form.label>
                    <x-ui.input id="department" name="department" value="{{ $oldValue('department') }}" placeholder="Hematology" :error="$errors->first('department')" />
                </div>

                <div>
                    <x-form.label for="version">Version</x-form.label>
                    @if($sop)
                        <div class="mt-1 text-sm font-medium text-slate-900 dark:text-slate-100">{{ $sop->version ?: '1.0' }}</div>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Set a new version number when saving as a new version below.</p>
                    @else
                        <x-ui.input id="version" name="version" value="{{ $oldValue('version') }}" placeholder="v2.1" :error="$errors->first('version')" />
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-form.label for="author_name">Author name</x-form.label>
                    <x-ui.input id="author_name" name="author_name" value="{{ $oldValue('author_name') }}" :error="$errors->first('author_name')" />
                </div>

                <div>
                    <x-form.label for="reviewed_date">Reviewed date</x-form.label>
                    <x-ui.input id="reviewed_date" name="reviewed_date" type="date" value="{{ $oldValue('reviewed_date') ? ($sop?->reviewed_date ? $sop->reviewed_date->format('Y-m-d') : $oldValue('reviewed_date')) : '' }}" :error="$errors->first('reviewed_date')" />
                </div>
            </div>

            <div>
                <x-form.label for="last_verified_at">Last Verified Date</x-form.label>
                <x-ui.input id="last_verified_at" name="last_verified_at" type="date" value="{{ $oldValue('last_verified_at') }}" :error="$errors->first('last_verified_at')" />
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">The date this data was last validated against a trusted source.</p>
            </div>

            <div>
                <x-form.label for="mandatory_disclaimer">Mandatory disclaimer</x-form.label>
                <x-ui.textarea id="mandatory_disclaimer" name="mandatory_disclaimer" rows="3" :error="$errors->first('mandatory_disclaimer')">{{ $oldValue('mandatory_disclaimer') }}</x-ui.textarea>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Shown as a required disclaimer at the top of the public detail page. Leave blank to use the site default.</p>
            </div>
        </x-form.section>
    </x-ui.card>

    <x-ui.card>
        <x-form.section
            title="Featured Image"
            description="Optional image displayed at the top of the public detail page. Large images are automatically resized and converted to WebP."
        >
            <div>
                @if($sop?->featured_image)
                    <img
                        src="{{ \Illuminate\Support\Facades\Storage::url($sop->featured_image) }}"
                        alt="Current featured image"
                        class="mb-3 max-h-40 w-auto rounded-lg border border-slate-200 dark:border-slate-700"
                    >
                @endif
                <x-ui.input id="featured_image" name="featured_image" type="file" accept=".jpg,.jpeg,.png,.webp" :error="$errors->first('featured_image')" />
                <x-form.error for="featured_image" />

                @if($sop?->featured_image)
                    <label class="mt-3 inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                        <input type="checkbox" name="remove_featured_image" value="1" class="rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600">
                        Remove current image
                    </label>
                @endif
            </div>
        </x-form.section>
    </x-ui.card>

    @if($sop)
        @php
            $suggestedVersion = app(\App\Services\SopVersionService::class)->suggestNextVersion($sop->version ?: '1.0');
        @endphp
        <x-ui.card>
            <x-form.section
                title="Versioning"
                description="Track revisions of this SOP as immutable snapshots. Each version preserves the content and metadata at save time."
            >
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Current version</p>
                        <p class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ $sop->version ?: '1.0' }}</p>
                    </div>
                    <x-ui.button href="{{ route('admin.sops.versions.index', $sop) }}" variant="secondary" size="sm">
                        Version History ({{ $sop->versions()->count() }})
                    </x-ui.button>
                </div>

                <div class="mt-4">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="is_new_version" value="1" class="mt-1" x-model="isNewVersion">
                        <span class="text-sm text-slate-700 dark:text-slate-200">Save as a new version</span>
                    </label>
                </div>

                <div x-show="isNewVersion" x-cloak class="mt-4 space-y-4">
                    <div>
                        <x-form.label for="version">New version number</x-form.label>
                        <x-ui.input
                            id="version"
                            name="version"
                            value="{{ old('version', $suggestedVersion) }}"
                            placeholder="v2.2"
                            :error="$errors->first('version')"
                        />
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Leave blank to auto-bump to the next version.</p>
                    </div>

                    <div>
                        <x-form.label for="change_summary">Change summary</x-form.label>
                        <x-ui.textarea id="change_summary" name="change_summary" rows="3" placeholder="Describe what changed in this version"></x-ui.textarea>
                        <x-form.error for="change_summary" />
                    </div>
                </div>
            </x-form.section>
        </x-ui.card>
    @endif

    <x-ui.card>
        <x-form.section title="Taxonomy" description="Organize this SOP by category and tag.">
            <div>
                <x-form.label>Categories</x-form.label>
                <x-admin.resources.category-selector
                    :selected="old('categories', $sop?->categories->pluck('id')->all() ?? [])"
                />
                @error('categories')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-form.label>Tags</x-form.label>
                <x-admin.resources.tag-input
                    :selected="old('tags', $sop?->tags->pluck('id')->all() ?? [])"
                />
                @error('tags')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                @enderror
            </div>
        </x-form.section>
    </x-ui.card>

    <x-admin.seo-eeat-fields :model="$sop" />

    <div class="flex items-center justify-end gap-3">
        <x-ui.button href="{{ route('admin.sops.index') }}" variant="ghost">
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
