<?php

namespace App\Console\Commands;

use App\Models\Assessment;
use App\Models\Question;
use App\Models\QuestionCategory;
use Illuminate\Console\Command;

class RestructureAscp extends Command
{
    protected $signature = 'questions:restructure-ascp';

    protected $description = 'Restructure categories to ASCP content areas and rebuild the ASCPI blueprint';

    /**
     * Stems matching fragments are re-tagged into the given category slug.
     * Category-level moves (whole QC + Biosafety) are handled first.
     */
    private const STEM_MOVES = [
        'urinalysis-body-fluids' => [
            'normal specific gravity range of urine',
            'casts containing white cells',
            'envelope-shaped (octahedral) crystals',
            'Urine specific gravity measures',
            'nitroprusside reaction',
            'coffin-lid appearance',
            'Normal urine pH',
            'positive urine nitrite',
            'Bence-Jones proteinuria',
            'CSF white cell count',
            "Light's criteria",
        ],
        'laboratory-operations' => [
            'tube top (additive) is used for coagulation',
            'blood culture sets',
            'Westgard rules are applied',
            'prevent specimen contamination',
            'molarity unit is defined',
            'reference interval',
            'urgent request',
            'fasting',
        ],
        'hematology' => [
            'mixing study',
            'factor inhibitor',
        ],
    ];

    public function handle(): int
    {
        $labOps = QuestionCategory::firstOrCreate(
            ['slug' => 'laboratory-operations'],
            ['name' => 'Laboratory Operations', 'sort_order' => 6, 'is_active' => true]
        );
        $uaBf = QuestionCategory::firstOrCreate(
            ['slug' => 'urinalysis-body-fluids'],
            ['name' => 'Urinalysis & Body Fluids', 'sort_order' => 7, 'is_active' => true]
        );

        $moved = 0;

        // Wholesale merge of Quality Control + Biosafety into Laboratory Operations.
        foreach (['quality-control', 'biosafety'] as $slug) {
            $old = QuestionCategory::where('slug', $slug)->first();
            if ($old !== null) {
                $moved += Question::where('question_category_id', $old->id)->update(['question_category_id' => $labOps->id]);
            }
        }

        // Targeted re-tagging of the old "Other" items.
        foreach (self::STEM_MOVES as $targetSlug => $fragments) {
            $targetId = $targetSlug === 'hematology'
                ? QuestionCategory::where('slug', 'hematology')->value('id')
                : ($targetSlug === 'laboratory-operations' ? $labOps->id : $uaBf->id);

            if ($targetId === null) {
                continue;
            }

            foreach ($fragments as $fragment) {
                $moved += Question::where('stem', 'like', "%{$fragment}%")
                    ->update(['question_category_id' => $targetId]);
            }
        }

        // Drop now-empty legacy categories and a leftover empty "Other".
        foreach (['quality-control', 'biosafety', 'other'] as $slug) {
            $legacy = QuestionCategory::where('slug', $slug)->first();
            if ($legacy !== null && Question::where('question_category_id', $legacy->id)->doesntExist()) {
                $legacy->delete();
            }
        }

        $this->line("Moved {$moved} question(s) into ASCP content areas.");

        // Phase 2: rebuild the primary ASCPI assessment blueprint to ASCP weights.
        $assessment = Assessment::orderBy('id')->first();
        if ($assessment !== null) {
            $assessment->blueprints()->delete();

            $plan = [
                'chemistry' => 13,
                'hematology' => 13,
                'microbiology' => 11,
                'blood-banking-transfusion' => 7,
                'immunology' => 7,
                'laboratory-operations' => 4,
                'urinalysis-body-fluids' => 5,
            ];

            foreach ($plan as $slug => $count) {
                $catId = QuestionCategory::where('slug', $slug)->value('id');
                if ($catId === null) {
                    continue;
                }
                $assessment->blueprints()->create([
                    'question_category_id' => $catId,
                    'question_count' => $count,
                    'easy_percent' => 30,
                    'intermediate_percent' => 40,
                    'advanced_percent' => 30,
                ]);
            }

            $assessment->update(['total_questions' => array_sum($plan)]);
            $this->line(sprintf("Blueprint rebuilt for '%s' -> %d questions (ASCP weights).", $assessment->title, array_sum($plan)));
        }

        return self::SUCCESS;
    }
}
