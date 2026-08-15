@extends('layouts.admin')

@section('title', 'Review Contribution - ' . config('app.name'))

@section('header', 'Review Contribution')

@section('content')
<div x-data="{ rejectOpen: {{ $errors->has('rejection_reason') ? 'true' : 'false' }} }" class="space-y-6">
    @if($errors->any())
        <x-ui.alert type="error">Please correct the errors below before approving.</x-ui.alert>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.contributions.index') }}" class="text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400">
            &larr; Back to review queue
        </a>
    </div>

    <x-admin.contributions.review-banner :resource="$resource" :type="$contributionType" />

    <form method="POST" action="{{ route('admin.contributions.approve', ['type' => $type, 'id' => $resource->id]) }}" class="space-y-6">
        @csrf

        <x-ui.card>
            <x-form.section
                title="Contribution Content"
                description="Edit the contribution as needed before approving. Markdown is supported and HTML is sanitized on render."
            >
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <x-form.label for="name" required>Name</x-form.label>
                        <x-ui.input
                            id="name"
                            name="name"
                            value="{{ old('name', $resource->name) }}"
                            :error="$errors->first('name')"
                            aria-required="true"
                        />
                    </div>

                    <div>
                        <x-form.label for="short_description" required>Short description</x-form.label>
                        <x-ui.textarea id="short_description" name="short_description" rows="3">{{ old('short_description', $resource->short_description) }}</x-ui.textarea>
                        <x-form.error for="short_description" />
                    </div>

                    @if($contributionType === \App\Enums\ContributionType::SOP)
                        @foreach([
                            'purpose' => 'Purpose',
                            'scope' => 'Scope',
                            'procedure_content' => 'Procedure steps',
                            'quality_control' => 'Quality control',
                            'safety_information' => 'Safety information',
                            'references' => 'References',
                        ] as $field => $label)
                            <div>
                                <x-form.label :for="$field">{{ $label }}</x-form.label>
                                <x-ui.textarea id="{{ $field }}" name="{{ $field }}" rows="4">{{ old($field, $resource->{$field}) }}</x-ui.textarea>
                                <x-form.error :for="$field" />
                            </div>
                        @endforeach
                    @else
                        <div>
                            <x-form.label for="content" required>Content (Markdown)</x-form.label>
                            <x-ui.textarea id="content" name="content" rows="16">{{ old('content', $resource->content) }}</x-ui.textarea>
                            <x-form.error for="content" />
                        </div>
                    @endif

                    @if($contributionType === \App\Enums\ContributionType::KNOWLEDGE)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-form.label for="topic_category">Topic category</x-form.label>
                                <x-ui.input id="topic_category" name="topic_category" value="{{ old('topic_category', $resource->topic_category) }}" :error="$errors->first('topic_category')" />
                            </div>
                            <div>
                                <x-form.label for="difficulty_level">Difficulty level</x-form.label>
                                <x-ui.select id="difficulty_level" name="difficulty_level">
                                    <option value="">Select difficulty</option>
                                    @foreach(['Beginner', 'Intermediate', 'Advanced'] as $level)
                                        <option value="{{ $level }}" @selected(old('difficulty_level', $resource->difficulty_level) === $level)>{{ $level }}</option>
                                    @endforeach
                                </x-ui.select>
                                <x-form.error for="difficulty_level" />
                            </div>
                        </div>
                        <div>
                            <x-form.label for="learning_objectives">Learning objectives</x-form.label>
                            <x-ui.textarea id="learning_objectives" name="learning_objectives" rows="4">{{ old('learning_objectives', $resource->learning_objectives) }}</x-ui.textarea>
                            <x-form.error for="learning_objectives" />
                        </div>
                    @endif

                    @if($contributionType === \App\Enums\ContributionType::INTERPRETATION)
                        <div>
                            <x-form.label for="clinical_context">Clinical context</x-form.label>
                            <x-ui.textarea id="clinical_context" name="clinical_context" rows="6">{{ old('clinical_context', $resource->clinical_context) }}</x-ui.textarea>
                            <x-form.error for="clinical_context" />
                        </div>
                    @endif
                </div>
            </x-form.section>
        </x-ui.card>

        <x-ui.card>
            <x-form.section title="Taxonomy" description="Organize this contribution by category and tag.">
                <div>
                    <x-form.label>Categories</x-form.label>
                    <x-admin.resources.category-selector
                        :selected="old('categories', $resource->categories->pluck('id')->all())"
                    />
                    @error('categories')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <x-form.label>Tags</x-form.label>
                    <x-admin.resources.tag-input
                        :selected="old('tags', $resource->tags->pluck('id')->all())"
                    />
                    @error('tags')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
            </x-form.section>
        </x-ui.card>

        <x-ui.card>
            <x-form.section title="SEO" description="Optional search engine metadata for the public detail page.">
                <div>
                    <x-form.label for="seo_title">SEO title</x-form.label>
                    <x-ui.input id="seo_title" name="seo_title" value="{{ old('seo_title', $resource->seo_title) }}" maxlength="70" :error="$errors->first('seo_title')" />
                </div>
                <div>
                    <x-form.label for="seo_description">SEO description</x-form.label>
                    <x-ui.textarea id="seo_description" name="seo_description" rows="3">{{ old('seo_description', $resource->seo_description) }}</x-ui.textarea>
                    <x-form.error for="seo_description" />
                </div>
            </x-form.section>
        </x-ui.card>

        <div class="flex flex-wrap items-center justify-end gap-3">
            <x-ui.button type="submit" name="decision" value="approve">
                Approve &amp; Publish
            </x-ui.button>
            <x-ui.button type="button" variant="danger" @click="rejectOpen = true">
                Reject
            </x-ui.button>
        </div>
    </form>

    <div
        x-cloak
        x-show="rejectOpen"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="reject-dialog-title"
        @keydown.escape.window="rejectOpen = false"
    >
        <div
            class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-800"
            @click.outside="rejectOpen = false"
        >
            <h2 id="reject-dialog-title" class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                Reject contribution
            </h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                A rejection reason is required. The contributor will be able to revise and resubmit.
            </p>

            <form method="POST" action="{{ route('admin.contributions.reject', ['type' => $type, 'id' => $resource->id]) }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <x-form.label for="rejection_reason" required>Rejection reason</x-form.label>
                    <x-ui.textarea id="rejection_reason" name="rejection_reason" rows="4" :error="$errors->first('rejection_reason')" aria-required="true">{{ old('rejection_reason') }}</x-ui.textarea>
                    <x-form.error for="rejection_reason" />
                </div>

                <div class="flex flex-wrap items-center justify-end gap-3">
                    <x-ui.button type="button" variant="ghost" @click="rejectOpen = false">
                        Cancel
                    </x-ui.button>
                    <x-ui.button type="submit" variant="danger">
                        Reject contribution
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
