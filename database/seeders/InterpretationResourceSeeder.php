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
                'author_name' => 'Labgoto Team',
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
                'author_name' => 'Labgoto Team',
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
                'author_name' => 'Labgoto Team',
                'reading_time_minutes' => 5,
            ],
            [
                'slug' => 'anion-gap-acid-base-interpretation',
                'name' => 'Anion Gap and Acid-Base Interpretation',
                'short_description' => 'Use the anion gap, delta ratio, and compensation rules to interpret metabolic acid-base disorders.',
                'content' => "# Anion Gap and Acid-Base Interpretation\n\nThe anion gap (AG = Na - Cl - HCO3) separates metabolic acidoses that accumulate unmeasured anions from those that do not.\n\n## Gap classification\n\n- **High anion gap acidosis**: lactate, ketoacids, renal failure (urate/sulfate/phosphate), toxins (methanol, ethylene glycol, salicylate).\n- **Normal (hyperchloremic) anion gap acidosis**: GI bicarbonate loss (diarrhea), renal tubular acidosis, early renal disease, acetazolamide; urine anion gap distinguishes renal (positive) from GI (negative) ammonium handling.\n- **Delta ratio**: AG/24 - HCO3 — a ratio near 1-2 suggests a simple high-gap acidosis; a ratio under 1 suggests a coexisting hyperchloremic acidosis; over 2 suggests a coexisting alkalosis.\n\n## Respiration\n\nHypocapnia is the normal compensation for metabolic acidosis (expected pCO2 ~ 1.5 x HCO3 + 8). Failure to compensate suggests a mixed disorder. \n\n## Correction\n\nHypoalbuminemia lowers the gap (albumin is a negatively charged protein); add 2.5 to the gap for every 1 g/dL albumin below 4.0 g/dL.",
                'clinical_context' => 'Acid-base interpretation requires simultaneous sodium, chloride, bicarbonate, and an assessment of the respiratory response. The anion gap and delta ratio localize the anion responsible for the acidosis.',
                'related_analytes' => ['Sodium', 'Chloride', 'CO2', 'Albumin', 'Lactate', 'Osmolality'],
                'mandatory_disclaimer' => self::DEFAULT_DISCLAIMER,
                'author_name' => 'Labgoto Team',
                'reading_time_minutes' => 8,
            ],
            [
                'slug' => 'hyperkalemia-diagnostic-approach',
                'name' => 'Hyperkalemia: A Diagnostic Approach',
                'short_description' => 'Interpreting an elevated serum potassium: pseudohyperkalemia, redistribution, and renal causes.',
                'content' => "# Hyperkalemia: A Diagnostic Approach\n\nConfirm before acting: potassium above the upper reference on one sample has important preanalytic causes.\n\n## Exclude pseudohyperkalemia\n\n- **Hemolysis** in the sample falsely raises potassium.\n- **Delayed separation** or prolonged tourniquet with fist clenching raises potassium.\n- **Thrombocytosis** (platelet count above 500 x10^9/L) releases potassium during clotting.\n- **Leukocytosis** with very high WBC releases potassium in vitro.\n\n## Mechanisms\n\n- **Redistribution**: acidosis shifts potassium out of cells; beta-blockade, insulin deficiency, digoxin toxicity, and tissue breakdown (rhabdomyolysis, tumor lysis) also redistribute.\n- **Renal retention**: acute or chronic kidney disease, type 4 RTA (hypoaldosteronism, ACE inhibitors/ARBs, potassium-sparing diuretics, NSAIDs, calcineurin inhibitors).\n- **Excess load**: potassium supplements, salt substitutes, massive transfusion.\n\n## Decision points\n\nPotassium above 5.5 mEq/L is hyperkalemia; above 6.0 mEq/L (or any level with ECG changes) is a medical emergency. Always correlate with ECG findings and chronical trends.",
                'clinical_context' => 'Serum potassium is tightly regulated. A single elevated value must first exclude preanalytic artifact, then be interpreted through redistribution versus renal excretion, with severity tied to the ECG.',
                'related_analytes' => ['Potassium', 'Creatinine', 'Sodium', 'Chloride', 'Glucose'],
                'mandatory_disclaimer' => self::DEFAULT_DISCLAIMER,
                'author_name' => 'Labgoto Team',
                'reading_time_minutes' => 7,
            ],
            [
                'slug' => 'hypercalcemia-hypocalcemia-profiles',
                'name' => 'Calcium Disorders: Hyper- and Hypocalcemia Profiles',
                'short_description' => 'Interpreting total calcium with albumin correction, PTH, and vitamin D in calcium disorders.',
                'content' => "# Calcium Disorders: Hyper- and Hypocalcemia Profiles\n\nTotal calcium must be corrected for albumin before interpretation.\n\n## Correction\n\nFor every 1 g/dL the albumin is below 4.0 g/dL, add ~0.8 mg/dL to the measured total calcium. In critical illness, ionized calcium is preferred.\n\n## Hypercalcemia\n\n- Most common outpatients: **primary hyperparathyroidism** (high or inappropriately normal PTH with high calcium).\n- Most common inpatients: **malignancy** (PTHrP, bone disease) — suppressed PTH.\n- Other: vitamin D/calcium excess, thiazides, immobilization, sarcoidosis (related to excess calcitriol), thyrotoxicosis.\n- Severe (> 14 mg/dL or < 12 mg/dL with symptoms) is an emergency.\n\n## Hypocalcemia\n\n- Causes: hypoalbuminemia (corrected value usually normal), chronic kidney disease (low calcitriol, hyperphosphatemia), hypoparathyroidism, vitamin D deficiency, magnesium deficiency, pancreatitis (calcium deposition).\n- With a low PTH: hypoparathyroidism; with a high PTH: a target-organ disorder (vitamin D, Mg, kidney) should be sought.\n\n## Key checks\n\nFasting sample, albumin, phosphate, magnesium, PTH, vitamin D (25-OH and 1,25-OH), and renal function together classify the disorder.",
                'clinical_context' => 'Calcium is protein-bound, filtered by the kidney, and regulated by PTH and vitamin D. Interpretation couples the corrected total calcium with PTH and phosphate to separate parathyroid from non-parathyroid causes.',
                'related_analytes' => ['Calcium', 'Albumin', 'Phosphate', 'Magnesium', 'PTH', 'Creatinine'],
                'mandatory_disclaimer' => self::DEFAULT_DISCLAIMER,
                'author_name' => 'Labgoto Team',
                'reading_time_minutes' => 8,
            ],
            [
                'slug' => 'microcytic-anemia-iron-vs-thalassemia',
                'name' => 'Microcytic Anemia: Iron Deficiency vs Thalassemia',
                'short_description' => 'Distinguish iron deficiency from thalassemia trait using iron studies, RDW, and Mentzer index.',
                'content' => "# Microcytic Anemia: Iron Deficiency vs Thalassemia\n\nLow MCV (below 80 fL) with anemia is the classic microcytic picture. The two leading causes need different workups.\n\n## Iron deficiency anemia\n\n- **Ferritin low** (< 12-30 ng/mL) is diagnostic of absent iron stores.\n- TIBC elevated, transferrin saturation low (< 16%), iron low.\n- RDW usually high (anisocytosis), Mentzer index (MCV/RBC) above 13.\n\n## Thalassemia trait\n\n- Ferritin and iron studies typically normal or only mildly reduced.\n- RDW normal or near normal, Mentzer index below 13.\n- Platelets often elevated; target cells and basophilic stippling on the film.\n- Confirm with hemoglobin electrophoresis (elevated HbA2 in beta-thalassemia trait; alpha-thalassemia trait appears with a normal pattern).\n\n## Mixed picture\n\nIn inflammation, ferritin can be normal despite true deficiency because it is an acute-phase protein; a low transferrin saturation with elevated CRP is consistent with the anemia of chronic disease versus combined deficiency.\n\n## Key checks\n\nMCV, RBC, hemoglobin, RDW, ferritin, TIBC, iron, transferrin saturation, and, when indicated, hemoglobin electrophoresis.",
                'clinical_context' => 'Low MCV anemia is common; iron deficiency dominates and must be distinguished from thalassemia trait before committing a patient to supplemental iron or family screening.',
                'related_analytes' => ['Hemoglobin', 'MCV', 'RDW', 'Iron', 'TIBC', 'Ferritin', 'Transferrin Saturation'],
                'mandatory_disclaimer' => self::DEFAULT_DISCLAIMER,
                'author_name' => 'Labgoto Team',
                'reading_time_minutes' => 7,
            ],
            [
                'slug' => 'thyroid-function-pattern-interpretation',
                'name' => 'Thyroid Function Pattern Interpretation',
                'short_description' => 'Interpret TSH and free T4 combinations: primary, secondary, and subclinical thyroid disease.',
                'content' => "# Thyroid Function Pattern Interpretation\n\nTSH is the most sensitive first-line test; free T4 (and occasionally free T3) interprets the pattern.\n\n## Patterns\n\n- **Low TSH + high free T4**: primary hyperthyroidism (Graves disease, toxic nodule, thyroiditis release phase).\n- **Low TSH + normal free T4**: subclinical hyperthyroidism — confirm with free T3 and consideration of T3 thyrotoxicosis.\n- **High TSH + low free T4**: primary hypothyroidism (autoimmune Hashimoto thyroiditis is the most common cause).\n- **High TSH + normal free T4**: subclinical hypothyroidism.\n- **Low/normal TSH + low free T4**: secondary (central) hypothyroidism — pituitary pathology; obtain a pituitary profile.\n\n## Caveats\n\n- **Pregnancy**: physiologic changes raise total T4 and lower TSH in trimester 1; use trimesterspecific (or total T4) references.\n- **Medications**: amiodarone, lithium, and biotin interfere with thyroid assays; biotin causes falsely low TSH on many platforms.\n- **Non-thyroidal illness**: critical illness can suppress TSH and lower T3/T4 (low T3 syndrome) without intrinsic disease.\n\n## Key checks\n\nTSH, free T4, free T3, and — in the right setting — TPO and thyroglobulin antibodies for autoimmune disease.",
                'clinical_context' => 'Thyroid patterns are inferred from the TSH-to-free-T4 relationship. Recognizing primary, central, and non-thyroidal patterns prevents misdiagnosis in critical illness and pregnancy.',
                'related_analytes' => ['TSH', 'Free T4', 'Free T3', 'TPO Antibody'],
                'mandatory_disclaimer' => self::DEFAULT_DISCLAIMER,
                'author_name' => 'Labgoto Team',
                'reading_time_minutes' => 7,
            ],
            [
                'slug' => 'lipid-panel-cardiovascular-risk',
                'name' => 'Lipid Panel and Cardiovascular Risk Interpretation',
                'short_description' => 'Read total cholesterol, LDL, HDL, and triglycerides in the context of ASCVD risk categories.',
                'content' => "# Lipid Panel and Cardiovascular Risk Interpretation\n\nThe lipid panel is a risk marker, and its interpretation is anchored to LDL and non-HDL cholesterol.\n\n## Core categories (mg/dL)\n\n- **Total cholesterol**: below 200 desirable; 200-239 borderline high; 240 or above high.\n- **LDL**: below 100 optimal; 100-129 near optimal; 130-159 borderline; 160-189 high; 190 or above very high.\n- **HDL**: below 40 (men) / 50 (women) is low; 60 or above is protective.\n- **Triglycerides**: below 150 normal; 150-199 borderline; 200-499 high; 500 or above very high (pancreatitis risk).\n\n## Calculation limits\n\nFriedewald LDL = Total cholesterol - HDL - (TG/5) is valid only when triglycerides are below 400 mg/dL; use direct LDL or an extended equation when triglycerides are higher.\n\n## Derived measures\n\n- **Non-HDL** = total cholesterol - HDL; target of LDL + 30, and less influenced by non-fasting variation.\n- **TC/HDL ratio** above 5 (men) / 4.5 (women) marks elevated risk.\n\n## Clinical integration\n\nAbsolute ASCVD risk calculators integrate age, diabetes, smoking, and blood pressure with the LDL/non-HDL to set statin intensity. Repeat fasting confirmation is needed before changing therapy when triglycerides are borderline elevated.",
                'clinical_context' => 'Lipid results stratify cardiovascular risk and guide statin therapy. Fasting status, lab measurement, and the 2018 ACC/AHA risk framework determine how the numbers are read.',
                'related_analytes' => ['Total Cholesterol', 'LDL', 'HDL', 'Triglycerides', 'Non-HDL'],
                'mandatory_disclaimer' => self::DEFAULT_DISCLAIMER,
                'author_name' => 'Labgoto Team',
                'reading_time_minutes' => 6,
            ],
            [
                'slug' => 'prolonged-pt-aptt-interpretation',
                'name' => 'Prolonged PT and APTT: Factor Deficiency vs Inhibitor',
                'short_description' => 'Stepwise interpretation of prolonged PT/APTT with mixing studies and factor assays.',
                'content' => "# Prolonged PT and APTT: Factor Deficiency vs Inhibitor\n\nA prolonged PT or APTT begins with a checklist: repeat on a correctly filled citrate tube (double-spun, platelet poor).\n\n## Pattern localization\n\n- **Prolonged PT only**: low factor VII — early warfarin effect, vitamin K deficiency, or liver disease; factor VII has the shortest half-life.\n- **Prolonged APTT only**: factors VIII, IX, XI, XII, also heparin contamination and lupus anticoagulant; factor VIII is the classic cause (classic hemophilia A).\n- **Both prolonged**: common pathway factors II, V, X, or fibrinogen — DIC, liver disease, vitamin K deficiency, warfarin, or a combined deficiency.\n\n## Mixing study\n\nA 1:1 mix with pooled normal plasma corrects a factor deficiency immediately; persistent prolongation indicates an inhibitor. Incubate for 1-2 hours to uncover time-dependent inhibitors (factor VIII inhibitors).\n\n## Factor assays\n\nIf the mix corrects, factor assays localize the deficiency. A negative mixing (non-correcting APTT) on a heparin-contaminated sample shows a reversed pattern with an elevated thrombin time; run a lupus anticoagulant panel when no factor-specific inhibitor is found.\n\n## Quick flags\n\nThrombin time distinguishes fibrinogen (prolonged TT and reptilase) from heparin effect (prolonged TT, normal reptilase). Platelets, D-dimer, and fibrinogen screen for DIC.",
                'clinical_context' => 'Prolonged PT/APTT results are common; a logical sequence of repeat sampling, mixing, and factor assays separates inherited deficiency from acquired inhibitor or DIC.',
                'related_analytes' => ['PT', 'INR', 'APTT', 'Fibrinogen', 'D-Dimer', 'Platelets'],
                'mandatory_disclaimer' => self::DEFAULT_DISCLAIMER,
                'author_name' => 'Labgoto Team',
                'reading_time_minutes' => 8,
            ],
            [
                'slug' => 'leukocytosis-thrombocytopenia-cbc-patterns',
                'name' => 'CBC Pattern Approach: Leukocytosis and Thrombocytopenia',
                'short_description' => 'Interpret elevated white counts, left shift, blasts, and low platelets from the complete blood count.',
                'content' => "# CBC Pattern Approach: Leukocytosis and Thrombocytopenia\n\nPattern recognition on the CBC is the fastest route to a differential diagnosis.\n\n## Leukocytosis\n\n- **Neutrophilia with left shift** (raised bands/metamyelocytes, toxic granulation, Dohle bodies) suggests acute bacterial infection or inflammation.\n- **Lymphocytosis**: reactive (viral, pertussis, stress) versus chronic lymphoproliferative disease; look for smudge cells and persistent elevation.\n- **Eosinophilia** (above ~0.5 x10^9/L or 6%): allergy, parasites, drug reaction, or eosinophilic disorders.\n- **Blasts**: any circulating blast is abnormal and requires urgent hematology review — denotes acute leukemia or myelodysplasia until proven otherwise.\n- **NRBCs**: correct the WBC when nucleated red cells exceed ~10 per 100 WBC.\n\n## Thrombocytopenia\n\n- Below 150 x10^9/L. First exclude EDTA-induced pseudothrombocytopenia (platelet clumps on the film) and repeat in citrate.\n- **Decreased production**: marrow disorder; look at the three cell lines together (pancytopenia points to marrow failure).\n- **Increased destruction**: immune (ITP), DIC (with low fibrinogen, raised D-dimer, prolonged PT/APTT), TTP/HUS (with schistocytes and hemolysis), hypersplenism.\n- **Dilutional**: massive transfusion.\n\n## Key checks\n\nWBC with differential, platelets, reticulocyte count, blood film morphology, and — when indicated — D-dimer, fibrinogen, LDH, and a peripheral smear review.",
                'clinical_context' => 'Interpreting high or low counts hinges on the blood film and the relationships between cell lines. Pattern-based reading directs confirmatory testing and hematology referral.',
                'related_analytes' => ['WBC', 'Neutrophils', 'Lymphocytes', 'Eosinophils', 'Platelets', 'Reticulocytes'],
                'mandatory_disclaimer' => self::DEFAULT_DISCLAIMER,
                'author_name' => 'Labgoto Team',
                'reading_time_minutes' => 8,
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
                ->whereIn('slug', [
                    'anemia-indices-interpreting-the-cbc',
                    'microcytic-anemia-iron-vs-thalassemia',
                    'leukocytosis-thrombocytopenia-cbc-patterns',
                ])
                ->get()
                ->each(fn (InterpretationResource $resource) => $resource->categories()->syncWithoutDetaching([$hematology->id]));
        }

        if ($clinicalChemistry) {
            InterpretationResource::query()
                ->whereIn('slug', [
                    'interpreting-elevated-liver-enzymes',
                    'hyponatremia-practical-interpretation',
                    'anion-gap-acid-base-interpretation',
                    'hyperkalemia-diagnostic-approach',
                    'hypercalcemia-hypocalcemia-profiles',
                ])
                ->get()
                ->each(fn (InterpretationResource $resource) => $resource->categories()->syncWithoutDetaching([$clinicalChemistry->id]));
        }

        $coagulation = Category::query()->where('slug', 'coagulation')->where('is_active', true)->first();
        if ($coagulation) {
            InterpretationResource::query()
                ->whereIn('slug', ['prolonged-pt-aptt-interpretation'])
                ->get()
                ->each(fn (InterpretationResource $resource) => $resource->categories()->syncWithoutDetaching([$coagulation->id]));
        }

        $thyroid = Category::query()->where('slug', 'thyroid-function')->where('is_active', true)->first();
        if ($thyroid) {
            InterpretationResource::query()
                ->whereIn('slug', ['thyroid-function-pattern-interpretation'])
                ->get()
                ->each(fn (InterpretationResource $resource) => $resource->categories()->syncWithoutDetaching([$thyroid->id]));
        }

        $lipid = Category::query()->where('slug', 'lipid-panel')->where('is_active', true)->first();
        if ($lipid) {
            InterpretationResource::query()
                ->whereIn('slug', ['lipid-panel-cardiovascular-risk'])
                ->get()
                ->each(fn (InterpretationResource $resource) => $resource->categories()->syncWithoutDetaching([$lipid->id]));
        }
    }
}
