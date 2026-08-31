@props([
    'course',
    'assessments',
    'action' => '',
    'method' => 'POST',
    'submitLabel' => 'Save Course',
])

@php
    $oldValue = fn (string $field, $default = null) => old($field, $course?->{$field} ?? $default);

    $globalSetting = fn (string $key, mixed $default) => setting($key, $default);

    $existingLinks = $course?->relationLoaded('assessments')
        ? $course->assessments->keyBy('id')
        : $course?->assessments->keyBy('id');

    $oldRows = collect(old('assessments', []));

    $initialRows = $assessments->map(function (App\Models\Assessment $assessment) use ($oldRows, $existingLinks) {
        $oldRow = $oldRows->firstWhere('assessment_id', $assessment->id);
        $link = $existingLinks?->get($assessment->id);

        return [
            'assessment_id' => $assessment->id,
            'title' => $assessment->title,
            'status' => $assessment->status,
            'include' => (bool) ($oldRow['include'] ?? false) || $link !== null,
            'required' => (bool) ($oldRow['required'] ?? $link?->pivot?->required ?? true),
            'sort_order' => (int) ($oldRow['sort_order'] ?? $link?->pivot?->sort_order ?? 0),
        ];
    })->values()->all();

    $toggle = fn (string $name, bool $default = true) => old($name) !== null
        ? old($name)
        : (bool) ($course?->{$name} ?? $default);
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6" x-data="{
    rows: [],
    init() {
        this.rows = JSON.parse(this.$root.dataset.rows || '[]');
    },
    reorder() {
        let order = 1;
        this.rows.forEach(row => {
            if (!row.include) return;
            row.sort_order = order++;
        });
    },
    get included() { return this.rows.filter(r => r.include); },
    get hasRequired() { return this.included.some(r => r.required); },
}" data-rows="{{ json_encode($initialRows) }}">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <x-ui.card>
        <x-form.section title="Details" description="Identity and publishing status of the course.">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-form.label for="title" required>Title</x-form.label>
                    <x-ui.input id="title" name="title" value="{{ $oldValue('title') }}" placeholder="Clinical Chemistry Refresher" :error="$errors->first('title')" aria-required="true" />
                </div>

                <div>
                    <x-form.label for="slug">Slug</x-form.label>
                    <x-ui.input id="slug" name="slug" value="{{ $oldValue('slug') }}" placeholder="auto-generated from title" :error="$errors->first('slug')" />
                </div>

                <div>
                    <x-form.label for="status" required>Status</x-form.label>
                    <x-ui.select id="status" name="status" :error="$errors->first('status')">
                        @foreach(\App\Models\Course::STATUSES as $statusOption)
                            <option value="{{ $statusOption }}" @selected($oldValue('status', 'draft') === $statusOption)>{{ ucfirst($statusOption) }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <div>
                    <x-form.label for="sort_order">Sort order</x-form.label>
                    <x-ui.input type="number" id="sort_order" name="sort_order" min="0" max="32000" value="{{ $oldValue('sort_order', 0) }}" :error="$errors->first('sort_order')" />
                </div>

                <div class="sm:col-span-2">
                    <x-form.label for="summary">Summary</x-form.label>
                    <x-ui.input id="summary" name="summary" value="{{ $oldValue('summary') }}" placeholder="Short description shown on course cards" :error="$errors->first('summary')" />
                </div>

                <div class="sm:col-span-2">
                    <x-form.label for="description">Description</x-form.label>
                    <x-ui.textarea id="description" name="description" rows="5" :error="$errors->first('description')">{{ $oldValue('description') }}</x-ui.textarea>
                </div>
            </div>
        </x-form.section>
    </x-ui.card>

    <x-ui.card>
        <x-form.section title="Assessments" description="Link the assessments that make up this course. Required assessments must be passed to earn the course certificate.">
            @error('assessments')
                <p class="mb-3 rounded-lg border border-danger-300 bg-danger-50 px-3 py-2 text-sm text-danger-700 dark:border-danger-700 dark:bg-danger-900/30 dark:text-danger-300">{{ $message }}</p>
            @enderror

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            <th class="pb-2 pr-3">Include</th>
                            <th class="pb-2 pr-3">Assessment</th>
                            <th class="pb-2 pr-3">Status</th>
                            <th class="pb-2 pr-3">Required</th>
                            <th class="pb-2 w-20 text-right">Order</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(row, index) in rows" :key="row.assessment_id">
                            <tr class="border-t border-slate-100 dark:border-slate-800">
                                <td class="py-2 pr-3 align-middle">
                                    <input type="checkbox" x-model="row.include" class="rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600" aria-label="Include assessment">
                                    <input type="hidden" :name="'assessments[' + index + '][assessment_id]'" :value="row.assessment_id">
                                    <input type="hidden" :name="'assessments[' + index + '][include]'" :value="row.include ? 1 : 0">
                                </td>
                                <td class="py-2 pr-3 align-middle font-medium text-slate-700 dark:text-slate-200" x-text="row.title"></td>
                                <td class="py-2 pr-3 align-middle capitalize text-slate-500 dark:text-slate-400" x-text="row.status"></td>
                                <td class="py-2 pr-3 align-middle">
                                    <input type="checkbox" x-model="row.required" :name="'assessments[' + index + '][required]'" :value="1" x-bind:disabled="!row.include"
                                           class="rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600"
                                           aria-label="Required assessment">
                                    <input type="hidden" :name="'assessments[' + index + '][sort_order]'" :value="row.sort_order">
                                </td>
                                <td class="py-2 text-right align-middle">
                                    <button type="button" class="rounded border border-slate-300 px-1.5 py-0.5 text-xs text-slate-500 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-800"
                                            x-show="row.include" x-on:click="reorder()" title="Renumber by current order">Renumber</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="mt-3 flex items-center justify-between rounded-lg bg-slate-50 px-4 py-2.5 text-sm dark:bg-slate-800/60">
                <span class="text-slate-600 dark:text-slate-300">Course covers <strong class="tabular-nums" x-text="included.length"></strong> assessment(s).</span>
                <span class="font-semibold" x-show="rows.length > 0"
                      :class="hasRequired ? 'text-emerald-600 dark:text-emerald-400' : 'text-danger-600 dark:text-danger-400'"
                      x-text="hasRequired ? '\u2713 ready' : '\u2717 mark at least one as required'"></span>
            </div>
        </x-form.section>
    </x-ui.card>

    <x-ui.card>
        <x-form.section title="Rules" description="How completion is earned and what is awarded.">
            <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-2">
                <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                    <input type="hidden" name="certificate_enabled" value="0">
                    <input type="checkbox" name="certificate_enabled" value="1" @checked((bool) $toggle('certificate_enabled', true)) class="rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600">
                    Issue course certificates
                </label>
                <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                    <input type="hidden" name="certificate_requires_all" value="0">
                    <input type="checkbox" name="certificate_requires_all" value="1" @checked((bool) $toggle('certificate_requires_all', true)) class="rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600">
                    Require passes on all required assessments
                </label>
            </div>
        </x-form.section>
    </x-ui.card>

    <x-ui.card>
        <x-form.section title="Certificate" description="Award a certificate on course completion. The colors and details below are locked to this course; leave a field blank to inherit the global design for that field.">
            @if($course?->exists)
                <div class="mb-4 flex flex-wrap items-center justify-end gap-3">
                    <x-ui.button href="{{ route('admin.courses.certificate-preview', $course) }}" variant="secondary" size="sm" target="_blank">
                        Preview certificate
                    </x-ui.button>
                </div>
            @endif
            <div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-form.label for="cert_body_text">Body Text</x-form.label>
                    <x-ui.textarea id="cert_body_text" name="cert_body_text" rows="3" :error="$errors->first('cert_body_text')" placeholder="Blank = use global template body text">{{ $oldValue('cert_body_text') }}</x-ui.textarea>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">System variables like {course}, {assessment}, {name}, {date} are replaced when the certificate is generated.</p>
                </div>

                <div class="sm:col-span-2">
                    <x-form.label for="cert_disclaimer_text">Disclaimer Text</x-form.label>
                    <x-ui.textarea id="cert_disclaimer_text" name="cert_disclaimer_text" rows="2" :error="$errors->first('cert_disclaimer_text')" placeholder="Blank = use global template disclaimer">{{ $oldValue('cert_disclaimer_text') }}</x-ui.textarea>
                </div>

                <div>
                    <x-form.label for="cert_signatory_name">Signatory Name</x-form.label>
                    <x-ui.input id="cert_signatory_name" name="cert_signatory_name" value="{{ $oldValue('cert_signatory_name') }}" placeholder="Blank = {{ $globalSetting('certificate.signatory_name', 'Program Director') }}" :error="$errors->first('cert_signatory_name')" />
                </div>

                <div>
                    <x-form.label for="cert_signatory_title">Signatory Title</x-form.label>
                    <x-ui.input id="cert_signatory_title" name="cert_signatory_title" value="{{ $oldValue('cert_signatory_title') }}" placeholder="Blank = {{ $globalSetting('certificate.signatory_title', 'Program Director, LabGOTO') }}" :error="$errors->first('cert_signatory_title')" />
                </div>

                <div>
                    <x-form.label for="cert_primary_color">Primary Color</x-form.label>
                    <div class="mt-1 flex items-center gap-2">
                        <input type="color" id="cert_primary_color_picker" value="{{ $oldValue('cert_primary_color', $globalSetting('certificate.primary_color', '#1e3a8a')) }}" class="h-9 w-14 cursor-pointer rounded border border-slate-300 dark:border-slate-600">
                        <x-ui.input id="cert_primary_color" name="cert_primary_color" value="{{ $oldValue('cert_primary_color') }}" placeholder="{{ $globalSetting('certificate.primary_color', '#1e3a8a') }}" class="flex-1 font-mono text-xs" :error="$errors->first('cert_primary_color')" />
                    </div>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Blank hex code = inherit the global color.</p>
                </div>

                <div>
                    <x-form.label for="cert_secondary_color">Secondary Color</x-form.label>
                    <div class="mt-1 flex items-center gap-2">
                        <input type="color" id="cert_secondary_color_picker" value="{{ $oldValue('cert_secondary_color', $globalSetting('certificate.secondary_color', '#d4af37')) }}" class="h-9 w-14 cursor-pointer rounded border border-slate-300 dark:border-slate-600">
                        <x-ui.input id="cert_secondary_color" name="cert_secondary_color" value="{{ $oldValue('cert_secondary_color') }}" placeholder="{{ $globalSetting('certificate.secondary_color', '#d4af37') }}" class="flex-1 font-mono text-xs" :error="$errors->first('cert_secondary_color')" />
                    </div>
                </div>

                <div>
                    <x-form.label for="cert_show_logo">Show Logo</x-form.label>
                    <x-ui.select id="cert_show_logo" name="cert_show_logo" :error="$errors->first('cert_show_logo')">
                        <option value="" @selected($oldValue('cert_show_logo') === null || $oldValue('cert_show_logo') === '')>Inherit global default</option>
                        <option value="1" @selected($oldValue('cert_show_logo') == '1')>Show</option>
                        <option value="0" @selected($oldValue('cert_show_logo') == '0')>Hide</option>
                    </x-ui.select>
                </div>

                <div>
                    <x-form.label for="cert_show_qr">Show QR Code</x-form.label>
                    <x-ui.select id="cert_show_qr" name="cert_show_qr" :error="$errors->first('cert_show_qr')">
                        <option value="" @selected($oldValue('cert_show_qr') === null || $oldValue('cert_show_qr') === '')>Inherit global default</option>
                        <option value="1" @selected($oldValue('cert_show_qr') == '1')>Show</option>
                        <option value="0" @selected($oldValue('cert_show_qr') == '0')>Hide</option>
                    </x-ui.select>
                </div>
            </div>
        </x-form.section>
    </x-ui.card>

    <div class="flex items-center justify-end gap-3">
        <x-ui.button href="{{ route('admin.courses.index') }}" variant="ghost">
            Cancel
        </x-ui.button>
        <x-ui.button type="submit" x-bind:disabled="!hasRequired">
            {{ $submitLabel }}
        </x-ui.button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sync = (pickerId, inputId) => {
        const picker = document.getElementById(pickerId);
        const input = document.getElementById(inputId);
        if (!picker || !input) return;
        picker.addEventListener('input', () => { input.value = picker.value; });
        input.addEventListener('input', () => {
            if (/^#[0-9a-fA-F]{6}$/.test(input.value)) picker.value = input.value;
        });
    };
    sync('cert_primary_color_picker', 'cert_primary_color');
    sync('cert_secondary_color_picker', 'cert_secondary_color');
});
</script>