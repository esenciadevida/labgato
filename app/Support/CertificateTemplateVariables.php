<?php

namespace App\Support;

use App\Models\Certificate;

class CertificateTemplateVariables
{
    /**
     * Available template variables and their descriptions.
     */
    public static array $available = [
        '{assessment}' => 'Assessment title',
        '{name}' => 'Certificate holder full name',
        '{first_name}' => 'Certificate holder first name',
        '{date}' => 'Issue date (e.g. August 26, 2026)',
        '{number}' => 'Certificate number',
        '{type}' => 'Certificate type (Participation / Achievement / Distinction)',
        '{rank}' => 'Rank at issue',
    ];

    /**
     * Sample values used for the admin preview (no real certificate needed).
     */
    public static array $sample = [
        '{assessment}' => 'Hematology Proficiency Exam',
        '{name}' => 'Maria Santos',
        '{first_name}' => 'Maria',
        '{date}' => 'August 26, 2026',
        '{number}' => 'LGT-2026-00142',
        '{type}' => 'Achievement',
        '{rank}' => '#3',
    ];

    /**
     * Resolve variable values from a real Certificate model.
     */
    public static function resolve(Certificate $certificate): array
    {
        $holderName = $certificate->user?->profile?->display_name
            ?? $certificate->user?->name
            ?? 'Participant';

        $firstName = explode(' ', $holderName)[0] ?? $holderName;

        return [
            '{assessment}' => $certificate->assessment?->title ?? 'Assessment',
            '{name}' => $holderName,
            '{first_name}' => $firstName,
            '{date}' => $certificate->issued_at?->format('F j, Y') ?? now()->format('F j, Y'),
            '{number}' => $certificate->certificate_number,
            '{type}' => ucfirst($certificate->type),
            '{rank}' => $certificate->rank_at_issue !== null ? '#' . $certificate->rank_at_issue : '',
        ];
    }

    /**
     * Replace {variable} placeholders in text with values.
     *
     * @param  string  $text  Text containing placeholders
     * @param  array<string,string>  $values  Variable values keyed by placeholder
     */
    public static function replace(string $text, array $values): string
    {
        return str_replace(array_keys($values), array_values($values), $text);
    }

    /**
     * Build a variables reference string for admin tooltip/help.
     */
    public static function helpText(): string
    {
        $lines = [];
        foreach (self::$available as $var => $desc) {
            $lines[] = "{$var} -- {$desc}";
        }

        return implode("\n", $lines);
    }
}
