@props([
    'model' => null,
])

@php
    $oldValue = fn (string $field) => old($field, $model?->{$field});
    $faqItems = old('faq', $model?->faq ?? []);
    if (! is_array($faqItems)) {
        $faqItems = [];
    }
    $faqItems = array_values(array_filter($faqItems, fn ($item) => is_array($item) && (
        trim((string) ($item['question'] ?? '')) !== '' || trim((string) ($item['answer'] ?? '')) !== ''
    )));
    $lastReviewed = old('last_reviewed_at', $model?->last_reviewed_at?->format('Y-m-d'));
@endphp

<x-ui.card>
    <x-form.section title="SEO" description="Optional search engine metadata for the public detail page.">
        <div>
            <x-form.label for="seo_title">SEO title</x-form.label>
            <x-ui.input
                id="seo_title"
                name="seo_title"
                value="{{ $oldValue('seo_title') }}"
                :error="$errors->first('seo_title')"
                maxlength="70"
            />
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Leave blank to auto-generate from the name and focus keyword.</p>
        </div>

        <div>
            <x-form.label for="focus_keyword">Focus keyword</x-form.label>
            <x-ui.input
                id="focus_keyword"
                name="focus_keyword"
                value="{{ $oldValue('focus_keyword') }}"
                :error="$errors->first('focus_keyword')"
                placeholder="e.g. egfr calculator"
            />
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Primary search term this page should rank for.</p>
        </div>

        <div>
            <x-form.label for="seo_description">SEO description</x-form.label>
            <x-ui.textarea id="seo_description" name="seo_description" rows="3">{{ $oldValue('seo_description') }}</x-ui.textarea>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Falls back to the short description or content excerpt when blank.</p>
            <x-form.error for="seo_description" />
        </div>
    </x-form.section>

    <div class="mt-8 border-t border-slate-100 dark:border-slate-800 pt-6">
        <x-form.section title="Trust & Review" description="Editorial review signals shown publicly to build E-E-A-T credibility.">
            <div>
                <x-form.label for="reviewer_name">Reviewed by</x-form.label>
                <x-ui.input
                    id="reviewer_name"
                    name="reviewer_name"
                    value="{{ $oldValue('reviewer_name') }}"
                    :error="$errors->first('reviewer_name')"
                    placeholder="Dr. Jane Doe, MD"
                />
            </div>

            <div>
                <x-form.label for="reviewer_credentials">Reviewer credentials</x-form.label>
                <x-ui.input
                    id="reviewer_credentials"
                    name="reviewer_credentials"
                    value="{{ $oldValue('reviewer_credentials') }}"
                    :error="$errors->first('reviewer_credentials')"
                    placeholder="MBBS, FMCPath"
                />
            </div>

            <div>
                <x-form.label for="last_reviewed_at">Last reviewed date</x-form.label>
                <x-ui.input
                    id="last_reviewed_at"
                    name="last_reviewed_at"
                    type="date"
                    value="{{ $lastReviewed }}"
                    :error="$errors->first('last_reviewed_at')"
                />
            </div>
        </x-form.section>
    </div>

    <div class="mt-8 border-t border-slate-100 dark:border-slate-800 pt-6" x-data="{ faq: @js($faqItems) }">
        <x-form.section title="FAQ" description="Questions shown in an accordion on the detail page and emitted as FAQPage structured data.">
            <template x-for="(item, index) in faq" :key="index">
                <div class="rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 p-4 space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400" x-text="'Question ' + (index + 1)"></span>
                        <button type="button" class="text-xs font-medium text-danger-600 hover:text-danger-700 dark:text-danger-400" @click="faq.splice(index, 1)">Remove</button>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" :for="'faq-question-' + index">Question</label>
                        <input
                            :id="'faq-question-' + index"
                            type="text"
                            class="w-full rounded-md border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 focus:border-primary-500 focus:ring-primary-500 text-sm"
                            x-model="item.question"
                            :name="'faq[' + index + '][question]'"
                            maxlength="500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" :for="'faq-answer-' + index">Answer</label>
                        <textarea
                            :id="'faq-answer-' + index"
                            rows="3"
                            class="w-full rounded-md border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 focus:border-primary-500 focus:ring-primary-500 text-sm"
                            x-model="item.answer"
                            :name="'faq[' + index + '][answer]'"
                        ></textarea>
                    </div>
                </div>
            </template>

            <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-md border border-dashed border-slate-300 dark:border-slate-600 px-3 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:border-primary-500 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
                @click="faq.push({ question: '', answer: '' })"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Add Question
            </button>

            <p class="text-xs text-slate-500 dark:text-slate-400">Empty or incomplete rows are ignored on save.</p>
        </x-form.section>
    </div>
</x-ui.card>
