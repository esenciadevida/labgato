<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\InterpretationResource;
use Illuminate\Database\Seeder;

class InterpretationResourceSeeder extends Seeder
{
    private const DEFAULT_DISCLAIMER = 'Educational Interpretation Only: This content is for educational purposes and explains common laboratory result patterns. It does not constitute medical advice or diagnosis. Always interpret laboratory results in the context of the patient\'s clinical condition and consult with a qualified healthcare professional.';

    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $resources = [
            [
                'slug' => 'interpreting-elevated-liver-enzymes',
                'name' => 'Interpreting Elevated Liver Enzymes',
                'short_description' => 'A practical guide to interpreting elevated ALT, AST, ALP, and GGT results.',
                'content' => "# Interpreting Elevated Liver Enzymes\n\nElevated liver enzymes are a common finding and require a structured approach.\n\n## Key patterns\n\n- **Hepatocellular pattern**: Marked elevation of ALT and AST.\n- **Cholestatic pattern**: Elevation of ALP and GGT out of proportion to transaminases.\n- **Mixed pattern**: Features of both hepatocellular and cholestatic injury.\n\n## Next steps\n\nCorrelate the ratio and degree of elevation with the clinical presentation, medications, and additional testing such as viral serology and imaging.",
                'clinical_context' => 'Elevated liver enzymes may reflect hepatic injury, biliary obstruction, medication effects, or extrahepatic sources. Interpretation always requires correlation with the full clinical picture.',
                'related_analytes' => ['ALT', 'AST', 'ALP', 'GGT', 'Bilirubin'],
                'mandatory_disclaimer' => self::DEFAULT_DISCLAIMER,
                'author_name' => 'Labgoto Education Team',
                'reading_time_minutes' => 6,
            ],
            [
                'slug' => 'anemia-indices-interpreting-the-cbc',
                'name' => 'Anemia Indices: Interpreting the CBC',
                'short_description' => 'Use MCV, MCH, MCHC, and RDW to classify anemias from a complete blood count.',
                'content' => "# Anemia Indices: Interpreting the CBC\n\nThe red cell indices help classify anemia by cell size and hemoglobin content.\n\n## Classification\n\n- **Microcytic anemia**: Low MCV with low hemoglobin.\n- **Normocytic anemia**: Normal MCV with low hemoglobin.\n- **Macrocytic anemia**: Elevated MCV with low hemoglobin.\n\n## Red cell distribution width\n\nA high RDW suggests a mixed population of red cells and can point toward early deficiency states.",
                'clinical_context' => 'Anemia is a laboratory diagnosis that requires integration with symptoms, iron studies, and additional markers to determine the underlying cause.',
                'related_analytes' => ['Hemoglobin', 'Hematocrit', 'MCV', 'MCH', 'MCHC', 'RDW'],
                'mandatory_disclaimer' => self::DEFAULT_DISCLAIMER,
                'author_name' => 'Labgoto Education Team',
                'reading_time_minutes' => 7,
            ],
            [
                'slug' => 'hyponatremia-practical-interpretation',
                'name' => 'Hyponatremia: A Practical Interpretation Guide',
                'short_description' => 'Approach to interpreting low serum sodium, including osmolarity-based classification.',
                'content' => "# Hyponatremia: A Practical Interpretation Guide\n\nHyponatremia is the most common electrolyte disturbance encountered in laboratory medicine.\n\n## Classify by osmolality\n\n- **Hypotonic hyponatremia**: True low sodium, further split by volume status.\n- **Isotonic hyponatremia**: Often due to hyperlipidemia or hyperproteinemia.\n- **Hypertonic hyponatremia**: Seen with hyperglycemia or other osmoles.\n\n## Key checks\n\nConfirm the result, review the clinical volume status, and correlate with paired electrolytes.",
                'clinical_context' => 'Interpretation of serum sodium requires assessment of volume status, osmolality, and concurrent electrolyte results.',
                'related_analytes' => ['Sodium', 'Potassium', 'Chloride', 'Osmolality', 'Glucose'],
                'mandatory_disclaimer' => self::DEFAULT_DISCLAIMER,
                'author_name' => 'Labgoto Education Team',
                'reading_time_minutes' => 5,
            ],
        ];

        foreach ($resources as $data) {
            InterpretationResource::firstOrCreate(
                ['slug' => $data['slug']],
                [
                    ...$data,
                    'status' => 'published',
                    'published_at' => now(),
                ]
            );
        }

        $hematology = Category::query()->where('slug', 'hematology')->where('is_active', true)->first();
        $clinicalChemistry = Category::query()->where('slug', 'clinical-chemistry')->where('is_active', true)->first();

        if ($hematology) {
            InterpretationResource::query()
                ->whereIn('slug', ['anemia-indices-interpreting-the-cbc'])
                ->get()
                ->each(fn (InterpretationResource $resource) => $resource->categories()->syncWithoutDetaching([$hematology->id]));
        }

        if ($clinicalChemistry) {
            InterpretationResource::query()
                ->whereIn('slug', ['interpreting-elevated-liver-enzymes', 'hyponatremia-practical-interpretation'])
                ->get()
                ->each(fn (InterpretationResource $resource) => $resource->categories()->syncWithoutDetaching([$clinicalChemistry->id]));
        }
    }
}
