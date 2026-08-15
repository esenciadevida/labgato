<?php

namespace App\Livewire\Admin\Ai;

use App\Exceptions\Ai\AiDisabledException;
use App\Exceptions\Ai\AiException;
use App\Services\Ai\AiContentService;
use App\Support\AiErrorMessage;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Throwable;

class AiGenerate extends Component
{
    public string $useCase = '';

    public string $target = '';

    public string $titleField = '';

    public string $outlineField = '';

    public string $sourceField = '';

    public string $label = 'Generate with AI';

    public int $minSourceChars = 0;

    public bool $confirmOverwrite = true;

    public bool $loading = false;

    public string $error = '';

    public string $generated = '';

    public function mount(
        string $useCase,
        string $target,
        string $label = 'Generate with AI',
        string $titleField = '',
        string $outlineField = '',
        string $sourceField = '',
        int $minSourceChars = 0,
        bool $confirmOverwrite = true,
    ): void {
        $this->useCase = $useCase;
        $this->target = $target;
        $this->label = $label;
        $this->titleField = $titleField;
        $this->outlineField = $outlineField;
        $this->sourceField = $sourceField;
        $this->minSourceChars = $minSourceChars;
        $this->confirmOverwrite = $confirmOverwrite;
    }

    public function generate(string $title = '', string $outline = '', string $source = ''): void
    {
        $this->error = '';
        $this->generated = '';

        $user = auth()->user();

        if (! $user || ! $user->hasPermission('ai.use')) {
            $this->error = 'You do not have permission to use AI features.';

            return;
        }

        if (! (bool) config('labgoto.ai.enabled', false) || ! (bool) config('labgoto.features.ai', false)) {
            $this->error = AiErrorMessage::for(new AiDisabledException('AI features are currently disabled by the administrator.'));

            return;
        }

        $this->loading = true;

        try {
            $response = app(AiContentService::class)->generate(
                $this->useCase,
                [
                    'title' => $title,
                    'outline' => $outline,
                    'content' => $source,
                ],
                $user,
            );

            $this->generated = $response->content;
            $this->dispatch('ai-generated', target: $this->target, content: $this->generated);
        } catch (AiException $e) {
            $this->error = AiErrorMessage::for($e);
        } catch (Throwable $e) {
            Log::warning('Unexpected AI generation failure.', [
                'use_case' => $this->useCase,
                'message' => $e->getMessage(),
            ]);
            $this->error = AiErrorMessage::for($e);
        } finally {
            $this->loading = false;
        }
    }

    public function render()
    {
        return view('livewire.admin.ai.generate');
    }
}
