<?php

namespace App\Livewire\PublicSite\Tools;

use App\Models\LaboratoryTool;
use App\Services\Tools\ToolService;
use Livewire\Component;

class ToolRunner extends Component
{
    public LaboratoryTool $tool;

    public array $values = [];

    public array $outputs = [];

    public array $validationErrors = [];

    public bool $hasCalculated = false;

    public function mount(LaboratoryTool $tool): void
    {
        $this->tool = $tool;
    }

    public function calculate(): void
    {
        $result = app(ToolService::class)->run($this->tool, $this->values);

        $this->outputs = $result['outputs'];
        $this->validationErrors = $result['errors'];
        $this->hasCalculated = true;
    }

    public function render()
    {
        return view('livewire.public.tools.tool-runner');
    }
}
