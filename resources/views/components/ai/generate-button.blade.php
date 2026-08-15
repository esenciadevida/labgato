@props([
    'useCase' => '',
    'label' => 'Generate with AI',
    'target' => '',
    'titleField' => '',
    'outlineField' => '',
    'sourceField' => '',
    'minSourceChars' => 0,
    'confirmOverwrite' => true,
])

<livewire:admin.ai.ai-generate
    :use-case="$useCase"
    :label="$label"
    :target="$target"
    :title-field="$titleField"
    :outline-field="$outlineField"
    :source-field="$sourceField"
    :min-source-chars="$minSourceChars"
    :confirm-overwrite="$confirmOverwrite"
/>
