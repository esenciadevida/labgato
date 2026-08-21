<?php

namespace App\Http\Requests\Member;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return match ($this->route('type')) {
            'sop' => (new StoreSopContributionRequest)->rules(),
            'knowledge' => (new StoreKnowledgeContributionRequest)->rules(),
            'interpretation' => (new StoreInterpretationContributionRequest)->rules(),
            default => [],
        };
    }
}
