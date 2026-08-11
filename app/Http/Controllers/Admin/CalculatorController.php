<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ResourceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCalculatorRequest;
use App\Http\Requests\Admin\UpdateCalculatorRequest;
use App\Models\Calculator;
use App\Services\AuditLogService;
use App\Services\Calculators\CalculatorService;
use Illuminate\Http\Request;

class CalculatorController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search'));
        $status = trim((string) $request->query('status'));
        $categoryId = $request->query('category');

        $calculators = Calculator::query()
            ->with(['categories', 'createdBy'])
            ->when($search !== '', fn ($query) => $query->search($search))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($categoryId, fn ($query) => $query->whereHas('categories', fn ($q) => $q->whereKey($categoryId)))
            ->orderByDesc('created_at')
            ->paginate(config('labgato.pagination.per_page'))
            ->withQueryString();

        return view('admin.calculators.index', compact('calculators', 'search', 'status', 'categoryId'));
    }

    public function create(CalculatorService $calculatorService)
    {
        $engines = $calculatorService->availableEngines();
        $statuses = ResourceStatus::cases();

        return view('admin.calculators.create', compact('engines', 'statuses'));
    }

    public function store(StoreCalculatorRequest $request)
    {
        $calculator = Calculator::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
            'published_at' => $request->input('status') === ResourceStatus::PUBLISHED->value ? now() : null,
        ]);

        $calculator->categories()->sync($request->input('categories', []));
        $calculator->tags()->sync($request->input('tags', []));

        app(AuditLogService::class)->logWithSubject('calculators.created', $calculator, $calculator->toArray(), $request);

        return redirect()->route('admin.calculators.index')->with('success', 'Calculator created successfully.');
    }

    public function edit(Calculator $calculator, CalculatorService $calculatorService)
    {
        $engines = $calculatorService->availableEngines();
        $statuses = ResourceStatus::cases();

        return view('admin.calculators.edit', compact('calculator', 'engines', 'statuses'));
    }

    public function update(UpdateCalculatorRequest $request, Calculator $calculator)
    {
        $targetStatus = ResourceStatus::from($request->input('status'));

        if ($calculator->status !== $targetStatus && ! $calculator->status->canTransitionTo($targetStatus)) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Status cannot change from {$calculator->status->label()} to {$targetStatus->label()}.");
        }

        $previousStatus = $calculator->status;

        $calculator->update([
            ...$request->validated(),
            'updated_by' => $request->user()->id,
            'published_at' => $targetStatus === ResourceStatus::PUBLISHED && ! $calculator->published_at ? now() : $calculator->published_at,
        ]);

        $calculator->categories()->sync($request->input('categories', []));
        $calculator->tags()->sync($request->input('tags', []));

        app(AuditLogService::class)->logWithSubject('calculators.updated', $calculator, $calculator->getChanges(), $request);

        if ($previousStatus !== ResourceStatus::PUBLISHED && $targetStatus === ResourceStatus::PUBLISHED) {
            app(AuditLogService::class)->logWithSubject('calculators.published', $calculator, ['status' => $targetStatus->value], $request);
        }

        if ($targetStatus === ResourceStatus::ARCHIVED) {
            app(AuditLogService::class)->logWithSubject('calculators.archived', $calculator, ['status' => $targetStatus->value], $request);
        }

        return redirect()->route('admin.calculators.index')->with('success', 'Calculator updated successfully.');
    }

    public function destroy(Request $request, Calculator $calculator)
    {
        $changes = ['name' => $calculator->name, 'slug' => $calculator->slug];
        $calculator->delete();

        app(AuditLogService::class)->logWithSubject('calculators.deleted', $calculator, $changes, $request);

        return redirect()->route('admin.calculators.index')->with('success', 'Calculator deleted successfully.');
    }
}
