<div class="space-y-6" x-data="questionForm()" data-initial-options="{{ json_encode(array_values($options)) }}" data-initial-correct="{{ $correctIndex }}" data-initial-license="{{ $question->image_license }}">

    <x-ui.card title="Question details">
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <x-form.label for="question_category_id">Subject</x-form.label>
                <select
                    id="question_category_id"
                    name="question_category_id"
                    required
                    class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-card focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/25 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                >
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected($question->question_category_id === $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <x-form.error for="question_category_id" />
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <x-form.label for="question_type">Type</x-form.label>
                    <select id="question_type" name="question_type"
                        x-model="type"
                        class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm shadow-card focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/25 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                        @foreach(\App\Models\Question::TYPES as $typeOption)
                            <option value="{{ $typeOption }}" @selected($question->question_type === $typeOption)>{{ ucfirst(str_replace('_', ' ', $typeOption)) }}</option>
                        @endforeach
                    </select>
                    <x-form.error for="question_type" />
                </div>

                <div>
                    <x-form.label for="difficulty">Difficulty</x-form.label>
                    <select id="difficulty" name="difficulty"
                        class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm shadow-card focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/25 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                        @foreach(\App\Models\Question::DIFFICULTIES as $difficultyOption)
                            <option value="{{ $difficultyOption }}" @selected($question->difficulty === $difficultyOption)>{{ ucfirst($difficultyOption) }}</option>
                        @endforeach
                    </select>
                    <x-form.error for="difficulty" />
                </div>

                <div>
                    <x-form.label for="status">Status</x-form.label>
                    <select id="status" name="status"
                        class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm shadow-card focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/25 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                        @foreach(\App\Models\Question::STATUSES as $statusOption)
                            <option value="{{ $statusOption }}" @selected($question->status === $statusOption)>{{ ucfirst($statusOption) }}</option>
                        @endforeach
                    </select>
                    <x-form.error for="status" />
                </div>
            </div>

            <div class="sm:col-span-2">
                <x-form.label for="stem">
                    <span x-text="type === 'case' ? 'Case scenario' : 'Stem'">Stem</span> <span class="text-danger-500">*</span>
                </x-form.label>
                <textarea
                    id="stem"
                    name="stem"
                    rows="4"
                    required
                    maxlength="5000"
                    placeholder="Enter the question stem or case scenario..."
                    class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-card focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/25 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                >{{ old('stem', $question->stem) }}</textarea>
                <x-form.error for="stem" />
            </div>

            <div class="sm:col-span-2">
                <x-form.label for="image">Question image <span class="text-xs font-normal text-slate-400">(optional unless the type is Image/Text &amp; Image)</span></x-form.label>

                @if($question->image_path)
                    <div class="mt-2 flex items-start gap-4" x-show="!removingImage && !previewSrc" x-cloak>
                        <img src="{{ $question->image_url }}" alt="{{ $question->image_alt ?? 'Current question image' }}" class="h-28 w-auto rounded-lg border border-slate-200 object-contain dark:border-slate-700">
                        <label class="inline-flex cursor-pointer select-none items-center gap-2 rounded-lg border border-danger-300 bg-danger-50 px-3 py-2 text-sm font-medium text-danger-700 transition-colors hover:bg-danger-100 dark:border-danger-700 dark:bg-danger-900/20 dark:text-danger-400">
                            <input type="checkbox" name="remove_image" value="1" x-model="removingImage" class="rounded border-danger-400 text-danger-600 focus:ring-danger-500">
                            Remove image
                        </label>
                    </div>
                @endif

                <input
                    type="file"
                    id="image"
                    name="image"
                    accept=".jpg,.jpeg,.png,.webp"
                    @change="onFileSelected($event)"
                    class="mt-2 block w-full cursor-pointer rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-card file:mr-3 file:cursor-pointer file:rounded-md file:border-0 file:bg-primary-50 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-primary-600 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/25 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:file:bg-slate-700 dark:file:text-slate-200"
                >
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">JPG, PNG or WebP. Max 5MB.</p>

                <img src="" alt="Preview of selected image" x-show="previewSrc" x-bind:src="previewSrc" class="mt-3 h-40 w-auto rounded-lg border border-slate-200 object-contain dark:border-slate-700" x-cloak>

                <x-form.error for="image" />

                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <x-form.label for="image_source">Image source</x-form.label>
                        <x-ui.input id="image_source" name="image_source" placeholder="CDC PHIL, Wikimedia Commons or Original"
                            value="{{ old('image_source', $question->image_source) }}" />
                        <x-form.error for="image_source" />
                    </div>
                    <div>
                        <x-form.label for="image_license">Image license</x-form.label>
                        <select id="image_license" name="image_license" x-model="license"
                            class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-card focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/25 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                            <option value="">Choose a license</option>
                            @foreach(\App\Models\Question::IMAGE_LICENSES as $licenseOption)
                                <option value="{{ $licenseOption }}" @selected($question->image_license === $licenseOption)>{{ $licenseOption }}</option>
                            @endforeach
                        </select>
                        <x-form.error for="image_license" />
                    </div>
                    <div class="sm:col-span-2" x-show="requiresAttributionField()" x-cloak>
                        <x-form.label for="image_attribution">Image attribution</x-form.label>
                        <textarea
                            id="image_attribution"
                            name="image_attribution"
                            rows="2"
                            maxlength="1000"
                            placeholder="e.g. By User:Mikrograf, CC BY-SA 4.0"
                            class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-card focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/25 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                        >{{ old('image_attribution', $question->image_attribution) }}</textarea>
                        <x-form.error for="image_attribution" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-form.label for="image_source_url">Image source URL</x-form.label>
                        <x-ui.input id="image_source_url" name="image_source_url" type="url" placeholder="https://..."
                            value="{{ old('image_source_url', $question->image_source_url) }}" />
                        <x-form.error for="image_source_url" />
                    </div>
                </div>
            </div>

            <div class="sm:col-span-2">
                <x-form.label for="explanation">Explanation</x-form.label>
                <textarea
                    id="explanation"
                    name="explanation"
                    rows="3"
                    maxlength="5000"
                    placeholder="Optional rationale shown after results are released..."
                    class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-card focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/25 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                >{{ old('explanation', $question->explanation) }}</textarea>
                <x-form.error for="explanation" />
            </div>
        </div>
    </x-ui.card>

    <x-ui.card title="Answer options">
        <div class="space-y-3">
            <template x-for="(option, index) in options" :key="index">
                <div class="flex items-center gap-3">
                    <span class="w-7 shrink-0 text-center text-sm font-semibold text-slate-500 dark:text-slate-400" x-text="String.fromCharCode(65 + parseInt(index))"></span>
                    <input
                        type="text"
                        name="options[]"
                        x-model="options[index]"
                        required
                        maxlength="1000"
                        placeholder="Option text..."
                        class="block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-card focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/25 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                    >
                    <label
                        class="flex shrink-0 cursor-pointer items-center gap-1.5 rounded-lg border px-3 py-2 text-xs font-medium transition-colors"
                        :class="correct === index
                            ? 'border-success-500 bg-success-50 text-success-700 dark:bg-success-900/30 dark:text-success-400'
                            : 'border-slate-300 bg-white text-slate-500 hover:border-success-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400'"
                    >
                        <input
                            type="radio"
                            name="correct_index"
                            :value="index"
                            :checked="correct === index"
                            @change="correct = index"
                            class="sr-only"
                        >
                        <svg x-show="correct === index" class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Correct
                    </label>
                    <button
                        type="button"
                        @click="remove(index)"
                        x-show="options.length > 2"
                        class="shrink-0 rounded-lg border border-slate-300 p-2 text-slate-400 transition-colors hover:border-danger-300 hover:text-danger-500 focus:outline-none dark:border-slate-700"
                        :aria-label="'Remove option ' + (parseInt(index) + 1)"
                        title="Remove option"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </template>

            <button
                type="button"
                @click="add()"
                x-show="options.length < 6"
                class="inline-flex items-center gap-2 rounded-lg border border-dashed border-slate-300 px-4 py-2 text-sm font-medium text-slate-500 transition-colors hover:border-primary-400 hover:text-primary-600 dark:border-slate-600 dark:text-slate-400 dark:hover:text-primary-400"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add option
            </button>

            <x-form.error for="options" />
        </div>
    </x-ui.card>

    <div class="flex items-center justify-end gap-3">
        <x-ui.button href="{{ route('admin.questions.index') }}" variant="secondary">Cancel</x-ui.button>
        <x-ui.button type="submit">{{ isset($question->id) ? 'Update question' : 'Create question' }}</x-ui.button>
    </div>
</div>

<script>
    function questionForm() {
        return {
            type: 'text',
            options: [],
            correct: 0,
            license: '',
            previewSrc: '',
            removingImage: false,
            init() {
                const root = this.$el;
                this.options = JSON.parse(root.dataset.initialOptions || '["",""]');
                this.correct = parseInt(root.dataset.initialCorrect || '0', 10);
                this.license = root.dataset.initialLicense || '';
                const typeSelect = root.querySelector('#question_type');
                if (typeSelect) {
                    this.type = typeSelect.value;
                }
            },
            usesImage() {
                return ['image', 'text_image'].includes(this.type);
            },
            requiresAttributionField() {
                return String(this.license).includes('CC');
            },
            add() {
                if (this.options.length < 6) {
                    this.options.push('');
                }
            },
            remove(index) {
                if (this.options.length <= 2) {
                    return;
                }
                this.options.splice(index, 1);
                if (this.correct >= this.options.length) {
                    this.correct = 0;
                } else if (this.correct > index) {
                    this.correct = this.correct - 1;
                }
            },
            onFileSelected(event) {
                const input = event.target;
                if (! input.files || ! input.files[0]) {
                    this.previewSrc = '';
                    return;
                }
                this.removingImage = false;
                this.previewSrc = URL.createObjectURL(input.files[0]);
            },
        };
    }
</script>
