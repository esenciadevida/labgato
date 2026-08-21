<?php

namespace App\Http\Controllers\Member;

use App\Enums\ContributionType;
use App\Enums\ResourceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Member\StoreInterpretationContributionRequest;
use App\Http\Requests\Member\StoreKnowledgeContributionRequest;
use App\Http\Requests\Member\StoreSopContributionRequest;
use App\Http\Requests\Member\UpdateContributionRequest;
use App\Notifications\NewContributionSubmittedNotification;
use App\Services\AuditLogService;
use App\Services\ContributionService;
use App\Support\AdminNotifier;
use App\Support\RichTextSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ContributionController extends Controller
{
    public function index(Request $request)
    {
        $type = trim((string) $request->query('type'));
        $status = trim((string) $request->query('status'));

        $contributions = app(ContributionService::class)
            ->memberContributions($request->user()->id, $type, $status);

        return view('member.contributions.index', compact(
            'contributions',
            'type',
            'status',
        ));
    }

    public function create(Request $request)
    {
        $type = ContributionType::resolve($request->query('type'));

        if ($type === null) {
            abort(404);
        }

        return view('member.contributions.create', compact('type'));
    }

    public function store(Request $request, string $type)
    {
        $contributionType = $this->contributionType($type);

        $validated = $request->validate($this->rulesFor($contributionType));

        $status = $request->input('action') === 'submit'
            ? ResourceStatus::UNDER_REVIEW
            : ResourceStatus::DRAFT;

        $resource = $contributionType->model()::create([
            ...$this->buildData($contributionType, $validated),
            'slug' => $this->generateUniqueSlug($contributionType, $validated['name']),
            'status' => $status,
            'contributed_by' => $request->user()->id,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        $this->audit(
            $status === ResourceStatus::UNDER_REVIEW ? 'contributions.submitted' : 'contributions.created',
            $resource,
            $request
        );

        if ($status === ResourceStatus::UNDER_REVIEW) {
            $this->notifyAdminsOfNewSubmission($contributionType, $resource);
        }

        $message = $status === ResourceStatus::UNDER_REVIEW
            ? 'Your contribution has been submitted for review.'
            : 'Your contribution has been saved as a draft.';

        return redirect()
            ->route('account.contributions.show', ['type' => $type, 'id' => $resource->id])
            ->with('success', $message);
    }

    public function show(Request $request, string $type, int $id)
    {
        $contributionType = $this->contributionType($type);
        $resource = $this->findOwnedContribution($contributionType, $id, $request->user()->id);

        $content = $this->renderedContent($contributionType, $resource);

        return view('member.contributions.show', compact('contributionType', 'resource', 'content') + ['type' => $contributionType]);
    }

    public function edit(Request $request, string $type, int $id)
    {
        $contributionType = $this->contributionType($type);
        $resource = $this->findOwnedContribution($contributionType, $id, $request->user()->id);

        if (! $resource->status->canMemberEdit()) {
            abort(403, 'Contributions under review cannot be edited.');
        }

        return view('member.contributions.edit', compact('contributionType', 'resource') + ['type' => $contributionType]);
    }

    public function update(UpdateContributionRequest $request, string $type, int $id)
    {
        $contributionType = $this->contributionType($type);
        $resource = $this->findOwnedContribution($contributionType, $id, $request->user()->id);

        if (! $resource->status->canMemberEdit()) {
            abort(403, 'Contributions under review cannot be edited.');
        }

        $validated = $request->validated();

        $data = [
            ...$this->buildData($contributionType, $validated),
            'updated_by' => $request->user()->id,
        ];

        if ($validated['name'] !== $resource->name) {
            $data['slug'] = $this->generateUniqueSlug($contributionType, $validated['name'], $resource->id);
        }

        $resource->update($data);

        $this->audit('contributions.updated', $resource, $request);

        return redirect()
            ->route('account.contributions.show', ['type' => $type, 'id' => $resource->id])
            ->with('success', 'Your contribution has been updated.');
    }

    public function destroy(Request $request, string $type, int $id)
    {
        $contributionType = $this->contributionType($type);
        $resource = $this->findOwnedContribution($contributionType, $id, $request->user()->id);

        if (! $resource->status->canMemberDelete()) {
            abort(403, 'This contribution cannot be deleted in its current state.');
        }

        $this->audit('contributions.deleted', $resource, $request);

        $resource->delete();

        return redirect()
            ->route('account.contributions.index')
            ->with('success', 'Your contribution has been deleted.');
    }

    public function submit(Request $request, string $type, int $id)
    {
        $contributionType = $this->contributionType($type);
        $resource = $this->findOwnedContribution($contributionType, $id, $request->user()->id);

        if ($resource->status !== ResourceStatus::DRAFT) {
            abort(403, 'Only drafts can be submitted for review.');
        }

        $resource->update(['status' => ResourceStatus::UNDER_REVIEW]);

        $this->audit('contributions.submitted', $resource, $request);

        $this->notifyAdminsOfNewSubmission($contributionType, $resource);

        return redirect()
            ->route('account.contributions.show', ['type' => $type, 'id' => $resource->id])
            ->with('success', 'Your contribution has been submitted for review.');
    }

    public function withdraw(Request $request, string $type, int $id)
    {
        $contributionType = $this->contributionType($type);
        $resource = $this->findOwnedContribution($contributionType, $id, $request->user()->id);

        if (! $resource->status->canMemberWithdraw()) {
            abort(403, 'Only contributions under review can be withdrawn.');
        }

        $resource->update(['status' => ResourceStatus::DRAFT]);

        $this->audit('contributions.withdrawn', $resource, $request);

        return redirect()
            ->route('account.contributions.show', ['type' => $type, 'id' => $resource->id])
            ->with('success', 'Your contribution has been withdrawn to drafts.');
    }

    public function resubmit(Request $request, string $type, int $id)
    {
        $contributionType = $this->contributionType($type);
        $resource = $this->findOwnedContribution($contributionType, $id, $request->user()->id);

        if ($resource->status !== ResourceStatus::REJECTED) {
            abort(403, 'Only rejected contributions can be resubmitted.');
        }

        $resource->update([
            'status' => ResourceStatus::UNDER_REVIEW,
            'rejection_reason' => null,
        ]);

        $this->audit('contributions.resubmitted', $resource, $request);

        return redirect()
            ->route('account.contributions.show', ['type' => $type, 'id' => $resource->id])
            ->with('success', 'Your contribution has been resubmitted for review.');
    }

    private function contributionType(string $type): ContributionType
    {
        $contributionType = ContributionType::resolve($type);

        if ($contributionType === null) {
            abort(404);
        }

        return $contributionType;
    }

    private function findOwnedContribution(ContributionType $type, int $id, int $userId)
    {
        $resource = $type->model()::findOrFail($id);

        if ((int) $resource->contributed_by !== $userId) {
            abort(403, 'You do not have access to this contribution.');
        }

        return $resource;
    }

    private function rulesFor(ContributionType $type): array
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

    private function generateUniqueSlug(ContributionType $type, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;

        while ($this->slugExists($type, $slug, $ignoreId)) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function slugExists(ContributionType $type, string $slug, ?int $ignoreId): bool
    {
        $query = $type->model()::query()->where('slug', $slug);

        if ($ignoreId !== null) {
            $query->whereKeyNot($ignoreId);
        }

        return $query->exists();
    }

    private function renderedContent(ContributionType $type, $resource): string
    {
        return RichTextSanitizer::render((string) $resource->content);
    }

    private function audit(string $action, $resource, Request $request): void
    {
        app(AuditLogService::class)->logWithSubject($action, $resource, ['status' => $resource->status], $request);
    }

    private function notifyAdminsOfNewSubmission($contributionType, $resource): void
    {
        AdminNotifier::notifyAdminsWithPermission(
            'contributions.review',
            new NewContributionSubmittedNotification($contributionType, $resource)
        );
    }
}
