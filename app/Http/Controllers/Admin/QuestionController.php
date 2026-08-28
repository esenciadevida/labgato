<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQuestionRequest;
use App\Http\Requests\Admin\UpdateQuestionRequest;
use App\Models\Question;
use App\Models\QuestionCategory;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $categoryId = $request->query('category');
        $difficulty = $request->query('difficulty');
        $type = $request->query('type');
        $status = $request->query('status');

        $baseQuery = Question::query()
            ->withCount('options')
            ->when($search !== '', fn ($q) => $q->where('stem', 'like', "%{$search}%"))
            ->when($categoryId, fn ($q) => $q->where('question_category_id', $categoryId))
            ->when($difficulty, fn ($q) => $q->where('difficulty', $difficulty))
            ->when($type, fn ($q) => $q->where('question_type', $type))
            ->when($status, fn ($q) => $q->where('status', $status));

        $questions = (clone $baseQuery)
            ->with(['category'])
            ->latest('updated_at')
            ->paginate(config('labgoto.pagination.per_page'))
            ->withQueryString();

        $stats = [
            'total' => Question::count(),
            'active' => Question::where('status', 'active')->count(),
            'draft' => Question::where('status', 'draft')->count(),
            'archived' => Question::where('status', 'archived')->count(),
        ];

        $categoryCounts = QuestionCategory::query()
            ->withCount('questions')
            ->orderBy('sort_order')
            ->get();

        $batchProgress = $this->batchProgress();

        return view('admin.questions.index', compact(
            'questions',
            'search',
            'categoryId',
            'difficulty',
            'type',
            'status',
            'stats',
            'categoryCounts',
            'batchProgress',
        ));
    }

    /**
     * Progress per seeded JSON batch: how many of the batch's questions have
     * been activated (reviewed) vs. still draft.
     *
     * @return list<array{label: string, total: int, activated: int, percent: int}>
     */
    private function batchProgress(): array
    {
        $files = glob(database_path('data/questions/*.json')) ?: [];

        if ($files === []) {
            return [];
        }

        $activeStems = Question::query()
            ->where('status', 'active')
            ->pluck('stem')
            ->flip();

        $progress = [];

        foreach ($files as $file) {
            $items = json_decode((string) file_get_contents($file), true) ?: [];
            $stems = array_values(array_filter(array_column($items, 'stem')));

            if ($stems === []) {
                continue;
            }

            $activated = collect($stems)->filter(fn ($stem) => isset($activeStems[$stem]))->count();

            if ($activated >= count($stems)) {
                continue;
            }

            $progress[] = [
                'label' => pathinfo($file, PATHINFO_FILENAME),
                'total' => count($stems),
                'activated' => (int) $activated,
                'percent' => (int) round($activated / count($stems) * 100),
            ];
        }

        return $progress;
    }

    public function create()
    {
        return view('admin.questions.create', [
            'question' => new Question([
                'question_type' => 'text',
                'difficulty' => 'intermediate',
                'status' => 'draft',
            ]),
            'categories' => $this->categoriesForSelect(),
            'options' => ['', '', '', ''],
            'correctIndex' => 0,
        ]);
    }

    public function store(StoreQuestionRequest $request)
    {
        $question = Question::create($this->questionAttributes($request));

        $this->storeImage($request, $question);
        $this->syncOptions($question, $request->input('options'), (int) $request->input('correct_index'));

        app(AuditLogService::class)->log(
            'create question',
            Question::class,
            $question->id,
            ['stem' => str($question->stem)->limit(80)],
            auth()->id()
        );

        return redirect()
            ->route('admin.questions.index')
            ->with('success', 'Question created.');
    }

    public function show(Question $question)
    {
        $question->load(['category', 'options']);

        return view('admin.questions.show', [
            'question' => $question,
        ]);
    }

    public function edit(Question $question)
    {
        $question->load(['category', 'options']);

        $options = [];
        $correctIndex = 0;

        foreach ($question->options as $index => $option) {
            $options[] = $option->text;

            if ($option->is_correct) {
                $correctIndex = $index;
            }
        }

        return view('admin.questions.edit', [
            'question' => $question,
            'categories' => $this->categoriesForSelect(),
            'options' => $options ?: ['', ''],
            'correctIndex' => $correctIndex,
        ]);
    }

    public function update(UpdateQuestionRequest $request, Question $question)
    {
        $previousImage = $question->image_path;

        $attributes = $this->questionAttributes($request);

        if ($request->boolean('remove_image')) {
            $attributes['image_path'] = null;
            $attributes['image_alt'] = null;
        }

        $question->update($attributes);

        if ($request->boolean('remove_image') && $previousImage !== null) {
            Storage::disk('public')->delete($previousImage);
        }

        if ($request->hasFile('image')) {
            if ($previousImage !== null) {
                Storage::disk('public')->delete($previousImage);
            }

            $this->storeImage($request, $question);
        }

        $this->syncOptions($question, $request->input('options'), (int) $request->input('correct_index'));

        app(AuditLogService::class)->log(
            'update question',
            Question::class,
            $question->id,
            ['stem' => str($question->stem)->limit(80)],
            auth()->id()
        );

        return redirect()
            ->route('admin.questions.index')
            ->with('success', 'Question updated.');
    }

    public function archive(Question $question)
    {
        $question->update(['status' => 'archived']);

        return redirect()
            ->back()
            ->with('success', 'Question archived.');
    }

    public function restore(Question $question)
    {
        $question->update(['status' => 'active']);

        return redirect()
            ->back()
            ->with('success', 'Question restored to active.');
    }

    public function destroy(Request $request, Question $question)
    {
        if ($question->usedInExams()) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'This question has been used in an examination. Archive it instead.'
                );
        }

        if ($question->image_path !== null) {
            Storage::disk('public')->delete($question->image_path);
        }

        $question->options()->delete();
        $question->delete();

        app(AuditLogService::class)->log(
            'delete question',
            Question::class,
            $question->id,
            [],
            auth()->id()
        );

        return redirect()
            ->route('admin.questions.index')
            ->with('success', 'Question deleted.');
    }

    public function bulk(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', 'in:archive,activate,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $action = $validated['action'];
        $affected = 0;
        $blocked = 0;

        Question::whereIn('id', $validated['ids'])
            ->orderBy('id')
            ->chunkById(100, function ($questions) use ($action, &$affected, &$blocked) {
                foreach ($questions as $question) {
                    if ($action === 'archive') {
                        $question->update(['status' => 'archived']);
                        $affected++;
                    } elseif ($action === 'activate') {
                        $question->update(['status' => 'active']);
                        $affected++;
                    } else {
                        if ($question->usedInExams()) {
                            $blocked++;

                            continue;
                        }

                        if ($question->image_path !== null) {
                            Storage::disk('public')->delete($question->image_path);
                        }

                        $question->options()->delete();
                        $question->delete();
                        $affected++;
                    }
                }
            });

        $messages = [
            'archive' => "{$affected} question(s) archived.",
            'activate' => "{$affected} question(s) activated.",
            'delete' => "{$affected} question(s) deleted.".($blocked > 0 ? " {$blocked} skipped because they have been used in an examination." : ''),
        ];

        return redirect()
            ->back()
            ->with('success', $messages[$action]);
    }

    private function questionAttributes(StoreQuestionRequest $request): array
    {
        return [
            'question_category_id' => (int) $request->input('question_category_id'),
            'question_type' => $request->input('question_type'),
            'difficulty' => $request->input('difficulty'),
            'status' => $request->input('status'),
            'stem' => $request->input('stem'),
            'explanation' => $request->filled('explanation') ? $request->input('explanation') : null,
            'image_source' => $request->filled('image_source') ? $request->input('image_source') : null,
            'image_license' => $request->filled('image_license') ? $request->input('image_license') : null,
            'image_attribution' => $request->filled('image_attribution') ? $request->input('image_attribution') : null,
            'image_source_url' => $request->filled('image_source_url') ? $request->input('image_source_url') : null,
            'image_search' => $request->filled('image_search') ? $request->input('image_search') : null,
            'image_spec' => $request->filled('image_spec') ? $request->input('image_spec') : null,
        ];
    }

    private function storeImage(StoreQuestionRequest $request, Question $question): void
    {
        if (! $request->hasFile('image')) {
            return;
        }

        $file = $request->file('image');
        $filename = uniqid('', true).'.'.strtolower($file->getClientOriginalExtension() ?: 'png');

        $path = $file->storeAs("questions/{$question->id}", $filename, 'public');

        $question->forceFill([
            'image_path' => $path,
            'image_alt' => $request->input('image_alt'),
        ])->save();
    }

    private function syncOptions(Question $question, array $options, int $correctIndex): void
    {
        $question->options()->delete();

        $saved = collect();

        foreach (array_values($options) as $index => $text) {
            $text = trim((string) $text);

            if ($text === '') {
                continue;
            }

            $saved->push($question->options()->create([
                'label' => chr(65 + $index),
                'text' => $text,
                'is_correct' => $index === $correctIndex,
                'sort_order' => $index,
            ]));
        }

        // Defensive: never persist a question with zero correct answers.
        if ($saved !== [] && ! $saved->contains(fn ($option) => $option->is_correct)) {
            $saved->first()->update(['is_correct' => true]);
        }
    }

    private function categoriesForSelect()
    {
        return QuestionCategory::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
