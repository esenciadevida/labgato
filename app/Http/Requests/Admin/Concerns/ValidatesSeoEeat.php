<?php

namespace App\Http\Requests\Admin\Concerns;

trait ValidatesSeoEeat
{
    protected function seoEeatRules(): array
    {
        return [
            'focus_keyword' => ['nullable', 'string', 'max:100'],
            'reviewer_name' => ['nullable', 'string', 'max:255'],
            'reviewer_credentials' => ['nullable', 'string', 'max:255'],
            'last_reviewed_at' => ['nullable', 'date', 'before_or_equal:today'],
            'faq' => ['nullable', 'array', 'max:10'],
            'faq.*.question' => ['nullable', 'string', 'max:500'],
            'faq.*.answer' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function cleanSeoEeatInput(): void
    {
        $faq = $this->input('faq');

        if (is_string($faq)) {
            $decoded = json_decode($faq, true);
            $faq = is_array($decoded) ? $decoded : null;
        }

        if (! is_array($faq)) {
            $this->merge(['faq' => null]);

            return;
        }

        $cleaned = array_values(array_filter(array_map(
            fn ($item) => is_array($item) ? [
                'question' => trim((string) ($item['question'] ?? '')),
                'answer' => trim((string) ($item['answer'] ?? '')),
            ] : null,
            $faq,
        ), fn (?array $item) => $item !== null && $item['question'] !== '' && $item['answer'] !== ''));

        $this->merge(['faq' => $cleaned === [] ? null : $cleaned]);
    }
}
