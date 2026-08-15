@props([
    'referenceRange' => null,
    'statuses' => [],
    'action' => '',
    'method' => 'POST',
    'submitLabel' => 'Save Reference Range',
    'showSaveAndCreate' => false,
])

@php
    $oldValue = fn (string $field) => old($field, $referenceRange?->{$field});
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <x-ui.card>
        <x-form.section
            title="Basic Information"
            description="Core details and publication status for this reference range."
        >
            <div>
                <x-form.label for="name" required>Name</x-form.label>
                <x-ui.input
                    id="name"
                    name="name"
                    value="{{ $oldValue('name') }}"
                    placeholder="Hemoglobin - Adult Male"
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
                        <option value="{{ $statusOption->value }}" @selected(old('status', $referenceRange?->status?->value ?? 'draft') === $statusOption->value)>
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
            title="Demographics"
            description="Who this reference range applies to. Variations of the same analyte are grouped on the public detail page."
        >
            <div>
                <x-form.label for="analyte_name" required>Analyte name</x-form.label>
                <x-ui.input
                    id="analyte_name"
                    name="analyte_name"
                    value="{{ $oldValue('analyte_name') }}"
                    placeholder="Hemoglobin"
                    :error="$errors->first('analyte_name')"
                    aria-required="true"
                />
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Shared by all demographic variations of the same analyte.</p>
            </div>

            <div>
                <x-form.label for="parameter_key">Parameter key</x-form.label>
                <x-ui.input
                    id="parameter_key"
                    name="parameter_key"
                    value="{{ $oldValue('parameter_key') }}"
                    placeholder="hemoglobin"
                    :error="$errors->first('parameter_key')"
                />
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Stable machine key used to link this range to calculator outputs (e.g. <code>mcv</code>, <code>anc</code>).</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-form.label for="specimen_type">Specimen type</x-form.label>
                    <x-ui.input id="specimen_type" name="specimen_type" value="{{ $oldValue('specimen_type') }}" placeholder="Whole Blood" :error="$errors->first('specimen_type')" />
                </div>

                <div>
                    <x-form.label for="methodology">Methodology</x-form.label>
                    <x-ui.input id="methodology" name="methodology" value="{{ $oldValue('methodology') }}" :error="$errors->first('methodology')" />
                </div>

                <div>
                    <x-form.label for="population_group">Population group</x-form.label>
                    <x-ui.input id="population_group" name="population_group" value="{{ $oldValue('population_group') }}" placeholder="Adult Male" :error="$errors->first('population_group')" />
                </div>

                <div>
                    <x-form.label for="sex" required>Sex</x-form.label>
                    <x-ui.select id="sex" name="sex">
                        @foreach(['any' => 'Any', 'male' => 'Male', 'female' => 'Female'] as $sexValue => $sexLabel)
                            <option value="{{ $sexValue }}" @selected(old('sex', $referenceRange?->sex ?? 'any') === $sexValue)>
                                {{ $sexLabel }}
                            </option>
                        @endforeach
                    </x-ui.select>
                    <x-form.error for="sex" />
                </div>

                <div>
                    <x-form.label for="age_min">Minimum age (years)</x-form.label>
                    <x-ui.input id="age_min" name="age_min" type="number" step="0.01" min="0" value="{{ $oldValue('age_min') }}" :error="$errors->first('age_min')" />
                </div>

                <div>
                    <x-form.label for="age_max">Maximum age (years)</x-form.label>
                    <x-ui.input id="age_max" name="age_max" type="number" step="0.01" min="0" value="{{ $oldValue('age_max') }}" :error="$errors->first('age_max')" />
                </div>
            </div>
            @error('age_min')
                <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
            @enderror
        </x-form.section>
    </x-ui.card>

    <x-ui.card>
        <x-form.section
            title="Values"
            description="Numeric limits and units. Limits must be logically ordered; critical values must fall outside the reference interval."
        >
            <div>
                <x-form.label for="unit" required>Unit</x-form.label>
                <x-ui.input id="unit" name="unit" value="{{ $oldValue('unit') }}" placeholder="g/dL" :error="$errors->first('unit')" aria-required="true" />
                @error('unit')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-form.label for="lower_limit">Lower limit</x-form.label>
                    <x-ui.input id="lower_limit" name="lower_limit" type="number" step="0.0001" value="{{ $oldValue('lower_limit') }}" :error="$errors->first('lower_limit')" />
                </div>

                <div>
                    <x-form.label for="upper_limit">Upper limit</x-form.label>
                    <x-ui.input id="upper_limit" name="upper_limit" type="number" step="0.0001" value="{{ $oldValue('upper_limit') }}" :error="$errors->first('upper_limit')" />
                </div>

                <div>
                    <x-form.label for="critical_low">Critical low</x-form.label>
                    <x-ui.input id="critical_low" name="critical_low" type="number" step="0.0001" value="{{ $oldValue('critical_low') }}" :error="$errors->first('critical_low')" />
                </div>

                <div>
                    <x-form.label for="critical_high">Critical high</x-form.label>
                    <x-ui.input id="critical_high" name="critical_high" type="number" step="0.0001" value="{{ $oldValue('critical_high') }}" :error="$errors->first('critical_high')" />
                </div>

                <div>
                    <x-form.label for="conversion_factor">Conversion factor</x-form.label>
                    <x-ui.input id="conversion_factor" name="conversion_factor" type="number" step="0.000001" value="{{ $oldValue('conversion_factor') }}" :error="$errors->first('conversion_factor')" />
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Multiplier used to convert the reported unit to another common unit.</p>
                </div>
            </div>
        </x-form.section>
    </x-ui.card>

    <x-ui.card>
        <x-form.section
            title="Metadata"
            description="Source citation and the date this range became effective."
        >
            <div>
                <x-form.label for="source_citation">Source citation</x-form.label>
                <x-ui.textarea id="source_citation" name="source_citation" rows="3">{{ $oldValue('source_citation') }}</x-ui.textarea>
                <x-form.error for="source_citation" />
            </div>

            <div>
                <x-form.label for="last_verified_at">Last Verified Date</x-form.label>
                <x-ui.input id="last_verified_at" name="last_verified_at" type="date" value="{{ $oldValue('last_verified_at') }}" :error="$errors->first('last_verified_at')" />
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">The date this data was last validated against a trusted source.</p>
            </div>

            <div>
                <x-form.label for="effective_date">Effective date</x-form.label>
                <x-ui.input id="effective_date" name="effective_date" type="date" value="{{ $oldValue('effective_date') }}" :error="$errors->first('effective_date')" />
            </div>
        </x-form.section>
    </x-ui.card>

    <x-ui.card>
        <x-form.section title="Taxonomy" description="Organize this reference range by category and tag.">
            <div>
                <x-form.label>Categories</x-form.label>
                <x-admin.resources.category-selector
                    :selected="old('categories', $referenceRange?->categories->pluck('id')->all() ?? [])"
                />
                @error('categories')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-form.label>Tags</x-form.label>
                <x-admin.resources.tag-input
                    :selected="old('tags', $referenceRange?->tags->pluck('id')->all() ?? [])"
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
                <x-ui.input
                    id="seo_title"
                    name="seo_title"
                    value="{{ $oldValue('seo_title') }}"
                    :error="$errors->first('seo_title')"
                    maxlength="70"
                />
            </div>

            <div>
                <x-form.label for="seo_description">SEO description</x-form.label>
                <x-ui.textarea id="seo_description" name="seo_description" rows="3">{{ $oldValue('seo_description') }}</x-ui.textarea>
                <x-form.error for="seo_description" />
            </div>
        </x-form.section>
    </x-ui.card>

    <div class="flex items-center justify-end gap-3">
        <x-ui.button href="{{ route('admin.reference-ranges.index') }}" variant="ghost">
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
