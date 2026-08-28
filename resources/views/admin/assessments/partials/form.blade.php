@props([
    'assessment',
    'categories',
    'action' => '',
    'method' => 'POST',
    'submitLabel' => 'Save Assessment',
])

@php
    $oldValue = fn (string $field, $default = null) => old($field, $assessment?->{$field} ?? $default);

    $globalSetting = fn (string $key, mixed $default) => setting($key, $default);

    $existingBlueprints = $assessment?->relationLoaded('blueprints')
        ? $assessment->blueprints->keyBy('question_category_id')
        : $assessment?->blueprints->keyBy('question_category_id');

    $oldRows = collect(old('blueprints', []));

    $initialRows = $categories->map(function (App\Models\QuestionCategory $category) use ($oldRows, $existingBlueprints) {
        $oldRow = $oldRows->firstWhere('question_category_id', $category->id);
        $blueprint = $existingBlueprints?->get($category->id);

        return [
            'question_category_id' => $category->id,
            'name' => $category->name,
            'include' => (bool) ($oldRow['include'] ?? null) || $blueprint !== null,
            'question_count' => (int) ($oldRow['question_count'] ?? $blueprint?->question_count ?? 0),
            'easy' => (int) ($oldRow['easy_percent'] ?? $blueprint?->easy_percent ?? ((bool) ($oldRow['include'] ?? null) || $blueprint !== null ? 30 : 0)),
            'intermediate' => (int) ($oldRow['intermediate_percent'] ?? $blueprint?->intermediate_percent ?? ((bool) ($oldRow['include'] ?? null) || $blueprint !== null ? 40 : 0)),
            'advanced' => (int) ($oldRow['advanced_percent'] ?? $blueprint?->advanced_percent ?? ((bool) ($oldRow['include'] ?? null) || $blueprint !== null ? 30 : 0)),
        ];
    })->values()->all();

    $toggle = fn (string $name, bool $default = false) => old($name) !== null
        ? old($name)
        : match ($name) {
            'results_available' => $assessment?->results_status === 'available' || ($assessment === null && $default),
            'answer_review_available' => $assessment?->answer_review_status === 'available' || ($assessment === null && $default),
            'leaderboard_visible' => $assessment?->leaderboard_status === 'available' || ($assessment === null && $default),
            default => (bool) ($assessment?->{$name} ?? $default),
        };
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6" x-data="{
    rows: [],
    init() {
        this.rows = JSON.parse(this.$root.dataset.rows || '[]');
    },
    rowSum(row) {
        return (parseInt(row.easy) || 0) + (parseInt(row.intermediate) || 0) + (parseInt(row.advanced) || 0);
    },
    get included() { return this.rows.filter(r => r.include); },
    get grandTotal() { return this.included.reduce((sum, r) => sum + (parseInt(r.question_count) || 0), 0); },
    get rowsValid() { return this.included.length > 0 && this.included.every(r => this.rowSum(r) === 100); },
    get totalsValid() { return this.grandTotal >= 1; },
}" data-rows="{{ json_encode($initialRows) }}">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <x-ui.card>
        <x-form.section title="Details" description="Identity and timing of the assessment.">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-form.label for="title" required>Title</x-form.label>
                    <x-ui.input id="title" name="title" value="{{ $oldValue('title') }}" placeholder="August ASCPI Challenge" :error="$errors->first('title')" aria-required="true" />
                </div>

                <div>
                    <x-form.label for="slug">Slug</x-form.label>
                    <x-ui.input id="slug" name="slug" value="{{ $oldValue('slug') }}" placeholder="auto-generated from title" :error="$errors->first('slug')" />
                </div>

                <div>
                    <x-form.label for="type" required>Type</x-form.label>
                    <x-ui.select id="type" name="type" :error="$errors->first('type')">
                        @foreach(\App\Models\Assessment::TYPES as $typeOption)
                            <option value="{{ $typeOption }}" @selected($oldValue('type', 'challenge') === $typeOption)>{{ ucfirst($typeOption) }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <div class="sm:col-span-2">
                    <x-form.label for="description">Description</x-form.label>
                    <x-ui.textarea id="description" name="description" rows="3" :error="$errors->first('description')">{{ $oldValue('description') }}</x-ui.textarea>
                </div>

                <div>
                    <x-form.label for="status" required>Status</x-form.label>
                    <x-ui.select id="status" name="status" :error="$errors->first('status')">
                        @foreach(\App\Models\Assessment::STATUSES as $statusOption)
                            <option value="{{ $statusOption }}" @selected($oldValue('status', 'scheduled') === $statusOption)>{{ ucfirst($statusOption) }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <div>
                    <x-form.label for="duration_minutes" required>Duration (minutes)</x-form.label>
                    <x-ui.input type="number" id="duration_minutes" name="duration_minutes" min="10" max="600" value="{{ $oldValue('duration_minutes', 60) }}" :error="$errors->first('duration_minutes')" />
                </div>

                <div>
                    <x-form.label for="opens_at">Opens at</x-form.label>
                    <x-ui.input type="datetime-local" id="opens_at" name="opens_at" value="{{ $assessment?->opens_at?->format('Y-m-d\TH:i') ?? old('opens_at') }}" :error="$errors->first('opens_at')" />
                </div>

                <div>
                    <x-form.label for="closes_at">Closes at</x-form.label>
                    <x-ui.input type="datetime-local" id="closes_at" name="closes_at" value="{{ $assessment?->closes_at?->format('Y-m-d\TH:i') ?? old('closes_at') }}" :error="$errors->first('closes_at')" />
                </div>

                <div>
                    <x-form.label for="pass_percentage">Pass mark (%)</x-form.label>
                    <x-ui.input type="number" id="pass_percentage" name="pass_percentage" min="0" max="100" step="0.5" value="{{ $oldValue('pass_percentage', 70) }}" :error="$errors->first('pass_percentage')" />
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Score at or above this earns an achievement certificate.</p>
                </div>

                <div>
                    <x-form.label for="total_questions">Total questions (auto)</x-form.label>
                    <x-ui.input type="number" id="total_questions" name="total_questions" min="1" max="500" x-bind:value="grandTotal" readonly aria-readonly="true" />
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Auto-calculated from the blueprint counts below.</p>
                </div>
            </div>
        </x-form.section>
    </x-ui.card>

    <x-ui.card>
        <x-form.section title="Blueprint" description="How many questions to draw from each subject, and the difficulty split. Counts must add up to the total; each row's difficulty percentages must sum to 100.">
            @error('blueprints')
                <p class="mb-3 rounded-lg border border-danger-300 bg-danger-50 px-3 py-2 text-sm text-danger-700 dark:border-danger-700 dark:bg-danger-900/30 dark:text-danger-300">{{ $message }}</p>
            @enderror

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            <th class="pb-2 pr-3">Include</th>
                            <th class="pb-2 pr-3">Subject</th>
                            <th class="pb-2 pr-3 w-28">Count</th>
                            <th class="pb-2 pr-3 w-20">Easy %</th>
                            <th class="pb-2 pr-3 w-24">Interm. %</th>
                            <th class="pb-2 pr-3 w-20">Adv. %</th>
                            <th class="pb-2 w-16 text-right">Sum</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(row, index) in rows" :key="row.question_category_id">
                            <tr class="border-t border-slate-100 dark:border-slate-800">
                                <td class="py-2 pr-3 align-middle">
                                    <input type="checkbox" x-model="row.include" class="rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600" aria-label="Include subject">
                                    <input type="hidden" :name="'blueprints[' + index + '][question_category_id]'" :value="row.question_category_id">
                                    <input type="hidden" :name="'blueprints[' + index + '][include]'" :value="row.include ? 1 : 0">
                                </td>
                                <td class="py-2 pr-3 align-middle font-medium text-slate-700 dark:text-slate-200" x-text="row.name"></td>
                                <td class="py-2 pr-3 align-middle">
                                    <input type="number" min="0" max="500" x-model.number="row.question_count" :name="'blueprints[' + index + '][question_count]'" class="w-20 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100" :disabled="!row.include">
                                </td>
                                <td class="py-2 pr-3 align-middle">
                                    <input type="number" min="0" max="100" x-model.number="row.easy" :name="'blueprints[' + index + '][easy_percent]'" class="w-16 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100" :disabled="!row.include">
                                </td>
                                <td class="py-2 pr-3 align-middle">
                                    <input type="number" min="0" max="100" x-model.number="row.intermediate" :name="'blueprints[' + index + '][intermediate_percent]'" class="w-16 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100" :disabled="!row.include">
                                </td>
                                <td class="py-2 pr-3 align-middle">
                                    <input type="number" min="0" max="100" x-model.number="row.advanced" :name="'blueprints[' + index + '][advanced_percent]'" class="w-16 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100" :disabled="!row.include">
                                </td>
                                <td class="py-2 text-right align-middle font-semibold tabular-nums">
                                    <span x-text="rowSum(row)" :class="(!row.include || rowSum(row) === 100) ? 'text-emerald-600 dark:text-emerald-400' : 'text-danger-600 dark:text-danger-400'"></span>
                                    <span x-text="(!row.include || rowSum(row) === 100) ? '\u2713' : '\u2717'" :class="(!row.include || rowSum(row) === 100) ? 'text-emerald-600 dark:text-emerald-400' : 'text-danger-600 dark:text-danger-400'"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="mt-3 flex items-center justify-between rounded-lg bg-slate-50 px-4 py-2.5 text-sm dark:bg-slate-800/60">
                <span class="text-slate-600 dark:text-slate-300">
                    Blueprint total:
                    <strong class="tabular-nums" x-text="grandTotal"></strong>
                    questions
                </span>
                <span class="font-semibold" x-show="rows.length > 0"
                      :class="totalsValid ? 'text-emerald-600 dark:text-emerald-400' : 'text-danger-600 dark:text-danger-400'"
                      x-text="totalsValid ? '\u2713 ready' : '\u2717 set at least one question count'"></span>
            </div>
        </x-form.section>
    </x-ui.card>

    <x-ui.card>
        <x-form.section title="Rules &amp; Releases" description="Attempt rules, shuffling, and what participants may see after finishing.">
            <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach([
                    ['one_attempt_only', 'One attempt only', true],
                    ['allow_practice', 'Practice mode enabled', false],
                    ['shuffle_questions', 'Shuffle questions', true],
                    ['shuffle_options', 'Shuffle options', true],
                    ['fullscreen_required', 'Fullscreen required', false],
                    ['certificate_enabled', 'Issue certificates', true],
                    ['results_available', 'Results released', false],
                    ['answer_review_available', 'Answer review released', false],
                    ['leaderboard_visible', 'Leaderboard visible', false],
                ] as [$field, $label, $default])
                    <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                        <input type="hidden" name="{{ $field }}" value="0">
                        <input type="checkbox" name="{{ $field }}" value="1" @checked((bool) $toggle($field, $default)) class="rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600">
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </x-form.section>
    </x-ui.card>

    <x-ui.card>
        <x-form.section title="Certificate" description="Optional per-assessment certificate overrides. Leave fields blank to inherit the global certificate template settings.">
            <div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-form.label for="cert_body_text">Body Text</x-form.label>
                    <x-ui.textarea id="cert_body_text" name="cert_body_text" rows="3" :error="$errors->first('cert_body_text')" placeholder="Blank = use global template body text">{{ $oldValue('cert_body_text') }}</x-ui.textarea>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">System variables like {assessment}, {name}, {date} are replaced when the certificate is generated.</p>
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
        <x-ui.button href="{{ route('admin.assessments.index') }}" variant="ghost">
            Cancel
        </x-ui.button>
        <x-ui.button type="submit" x-bind:disabled="!rowsValid || !totalsValid">
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
