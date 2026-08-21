<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContributionType;
use App\Enums\ResourceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectContributionRequest;
use App\Http\Requests\Member\StoreInterpretationContributionRequest;
use App\Http\Requests\Member\StoreKnowledgeContributionRequest;
use App\Http\Requests\Member\StoreSopContributionRequest;
use App\Notifications\ContributionApprovedNotification;
use App\Notifications\ContributionRejectedNotification;
use App\Services\AuditLogService;
use App\Services\ContributionService;
use App\Support\RichTextSanitizer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContributionReviewController extends Controller
{
    public function index(Request $request)
    {
        $type = trim((string) $request->query('type'));

        $contributions = app(ContributionService::class)->pendingContributions($type);

        return view('admin.contributions.index', compact('contributions', 'type'));
    }

    public function show(Request $request, string $type, int $id)
    {
        $contributionType = $this->contributionType($type);
        $resource = $this->findPendingContribution($contributionType, $id);

        $content = RichTextSanitizer::render((string) $resource->content);

        $resource->load(['categories', 'tags', 'contributedBy']);

        return view('admin.contributions.review', compact('contributionType', 'resource', 'content', 'type'));
    }

    public function approve(Request $request, string $type, int $id)
    {
        $contributionType = $this->contributionType($type);
        $resource = $this->findPendingContribution($contributionType, $id);

        $validated = $request->validate($this->approveRules($contributionType));

        $data = $this->buildData($contributionType, $validated);

        $resource->update([
            ...$data,
            'status' => ResourceStatus::PUBLISHED,
            'published_at' => now(),
            'updated_by' => $request->user()->id,
            'seo_title' => $validated['seo_title'] ?? null,
            'seo_description' => $validated['seo_description'] ?? null,
        ]);

        $resource->categories()->sync($validated['categories'] ?? []);
        $resource->tags()->sync($validated['tags'] ?? []);

        app(AuditLogService::class)->logWithSubject(
            'contributions.approved',
            $resource,
            ['status' => ResourceStatus::PUBLISHED->value, 'published_at' => $resource->published_at->toDateTimeString()],
            $request
        );

        if ($resource->contributedBy) {
            $resource->contributedBy->notify(new ContributionApprovedNotification($contributionType, $resource));
        }

        return redirect()
            ->route('admin.contributions.index')
            ->with('success', "Contribution \"{$resource->name}\" approved and published.");
    }

    public function reject(RejectContributionRequest $request, string $type, int $id)
    {
        $contributionType = $this->contributionType($type);
        $resource = $this->findPendingContribution($contributionType, $id);

        $resource->update([
            'status' => ResourceStatus::REJECTED,
            'rejection_reason' => trim($request->validated('rejection_reason')),
            'updated_by' => $request->user()->id,
        ]);

        app(AuditLogService::class)->logWithSubject(
            'contributions.rejected',
            $resource,
            ['status' => ResourceStatus::REJECTED->value, 'rejection_reason' => $resource->rejection_reason],
            $request
        );

        if ($resource->contributedBy) {
            $resource->contributedBy->notify(
                new ContributionRejectedNotification($contributionType, $resource, $resource->rejection_reason)
            );
        }

        return redirect()
            ->route('admin.contributions.index')
            ->with('success', "Contribution \"{$resource->name}\" rejected.");
    }

    private function contributionType(string $type): ContributionType
    {
        $contributionType = ContributionType::resolve($type);

        if ($contributionType === null) {
            abort(404);
        }

        return $contributionType;
    }

    private function findPendingContribution(ContributionType $type, int $id)
    {
        $resource = $type->model()::findOrFail($id);

        if ($resource->status !== ResourceStatus::UNDER_REVIEW || $resource->contributed_by === null) {
            abort(403, 'This contribution is not pending review.');
        }

        return $resource;
    }

    private function approveRules(ContributionType $type): array
    {
        return [
            ...$this->typeRules($type),
            'categories' => ['nullable', 'array'],
            'categories.*' => ['integer', Rule::exists('categories', 'id')],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', Rule::exists('tags', 'id')],
            'seo_title' => ['nullable', 'string', 'max:70'],
            'seo_description' => ['nullable', 'string', 'max:160'],
        ];
    }

    private function typeRules(ContributionType $type): array
    {
        return match ($type) {
            ContributionType::SOP => (new StoreSopContributionRequest)->rules(),
            ContributionType::KNOWLEDGE => (new StoreKnowledgeContributionRequest)->rules(),
            ContributionType::INTERPRETATION => (new StoreInterpretationContributionRequest)->rules(),
        };
    }

    private function buildData(ContributionType $type, array $validated): array
    {
        $allowed = match ($type) {
            ContributionType::SOP => [
                'name', 'short_description', 'purpose', 'scope',
                'procedure_content', 'quality_control', 'safety_information', 'references',
            ],
            ContributionType::KNOWLEDGE => [
                'name', 'short_description', 'content', 'learning_objectives', 'topic_category', 'difficulty_level',
            ],
            ContributionType::INTERPRETATION => [
                'name', 'short_description', 'content', 'clinical_context',
            ],
        };

        $data = collect($allowed)
            ->mapWithKeys(fn (string $field) => [$field => $validated[$field] ?? null])
            ->all();

        if ($type === ContributionType::SOP) {
            $data['content'] = $this->composeSopContent($data);
        }

        return $data;
    }

    private function composeSopContent(array $data): string
    {
        $sections = [
            'purpose' => 'Purpose',
            'scope' => 'Scope',
            'procedure_content' => 'Procedure',
            'quality_control' => 'Quality Control',
            'safety_information' => 'Safety Information',
            'references' => 'References',
        ];

        $parts = [];

        foreach ($sections as $field => $heading) {
            $value = trim((string) ($data[$field] ?? ''));

            if ($value !== '') {
                $parts[] = "## {$heading}\n\n{$value}";
            }
        }

        return implode("\n\n", $parts);
    }
}
