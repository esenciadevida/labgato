<?php

namespace App\Livewire\PublicSite\Calculators;

use App\Models\Calculator;
use App\Services\Calculators\CalculatorService;
use Livewire\Component;

class CalculatorRunner extends Component
{
    public Calculator $calculator;

    public array $values = [];

    public array $outputs = [];

    public array $validationErrors = [];

    public bool $hasCalculated = false;

    public function mount(Calculator $calculator): void
    {
        $this->calculator = $calculator;
    }

    public function calculate(): void
    {
        $result = app(CalculatorService::class)->run($this->calculator, $this->values);

        $this->outputs = $result['outputs'];
        $this->validationErrors = $result['errors'];
        $this->hasCalculated = true;

        if (empty($this->validationErrors)) {
            $this->dispatch('labgato:calc-result', [
                'slug' => $this->calculator->slug,
                'name' => $this->calculator->name,
                'inputs' => $this->values,
                'outputs' => $this->outputs,
                'timestamp' => now()->toISOString(),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.public.calculators.calculator-runner');
    }
}
