<?php

namespace App\Support;

use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\QuestionOption;

/**
 * A curated bank of real medical-laboratory-science MCQs used by the exam
 * engine seeders. Each entry: [difficulty, stem, options[4], correctIndex, explanation].
 */
class ExamQuestionBank
{
    public static function seed(): void
    {
        static::prunePlaceholders();

        foreach (static::bank() as $slug => $questions) {
            $category = QuestionCategory::where('slug', $slug)->first();

            if ($category === null) {
                continue;
            }

            foreach ($questions as [$difficulty, $stem, $options, $correct, $explanation]) {
                $question = Question::firstOrCreate(['stem' => $stem], [
                    'question_category_id' => $category->id,
                    'question_type' => 'text',
                    'difficulty' => $difficulty,
                    'status' => 'active',
                    'explanation' => $explanation,
                ]);

                foreach (['A', 'B', 'C', 'D'] as $index => $label) {
                    QuestionOption::firstOrCreate(
                        ['question_id' => $question->id, 'label' => $label],
                        [
                            'text' => $options[$index],
                            'is_correct' => $index === $correct,
                            'sort_order' => $index,
                        ]
                    );
                }
            }
        }
    }

    private static function prunePlaceholders(): void
    {
        Question::query()
            ->where('stem', 'like', 'Demo %')
            ->orWhere('stem', 'like', 'Smoke %')
            ->delete();
    }

    /** @return array<string, array<int, array{0:string,1:string,2:list<string>,3:int,4:string}>> */
    public static function bank(): array
    {
        return [
            'hematology' => [
                ['easy', 'What is the normal reference range for adult male hemoglobin?', ['14-18 g/dL', '12-16 g/dL', '11-14 g/dL', '15-20 g/dL'], 0, 'Adult male hemoglobin is typically 14-18 g/dL.'],
                ['easy', 'An MCV above 100 fL is classified as which type of anemia?', ['Microcytic', 'Macrocytic', 'Normocytic', 'Hemolytic'], 1, 'MCV > 100 fL is macrocytic.'],
                ['easy', 'Which anticoagulant is used for a complete blood count (CBC)?', ['Sodium citrate', 'EDTA', 'Heparin', 'Oxalate'], 1, 'EDTA chelates calcium and preserves cell morphology for CBC.'],
                ['intermediate', 'Which cell is the direct precursor of platelets?', ['Promyelocyte', 'Megakaryocyte', 'Metamyelocyte', 'Monoblast'], 1, 'Platelets are formed from megakaryocytes in the bone marrow.'],
                ['intermediate', 'Target cells (codocytes) on a peripheral smear are most commonly associated with which condition?', ['Iron deficiency', 'Thalassemia', 'Acute leukemia', 'Polycythemia'], 1, 'Codocytes occur with thalassemia and liver disease.'],
                ['intermediate', 'Which red cell index is used to classify anemia by average red cell size?', ['MCH', 'MCHC', 'MCV', 'RDW'], 2, 'Mean corpuscular volume (MCV) is the average red cell size.'],
                ['advanced', 'A low ferritin with low serum iron and high total iron-binding capacity (TIBC) is most consistent with which condition?', ['Anemia of chronic disease', 'Iron deficiency anemia', 'Hemochromatosis', 'Sideroblastic anemia'], 1, 'Iron deficiency shows low ferritin/iron and high TIBC.'],
                ['advanced', 'A Schilling test is used to evaluate absorption of which vitamin?', ['Vitamin K', 'Folic acid', 'Vitamin B12', 'Vitamin D'], 2, 'The Schilling test evaluates intestinal absorption of vitamin B12.'],
                ['advanced', 'A reticulocyte count is most likely to be elevated in which scenario?', ['Increased marrow response', 'Aplastic anaemia', 'Irradiated marrow', 'Leukostasis'], 0, 'Reticulocytes rise when the marrow responds, e.g. after haemorrhage or haemolysis.'],
                ['easy', 'What is the approximate normal lifespan of a red blood cell?', ['30 days', '60 days', '120 days', '200 days'], 2, 'Red blood cells survive about 120 days.'],
                ['intermediate', 'Which stain is used to perform a reticulocyte count?', ['Wright stain', 'New methylene blue', 'Gram stain', 'Prussian blue'], 1, 'Reticulocytes are counted with a supravital stain such as new methylene blue.'],
                ['advanced', 'An elevated RDW indicates what about the red cell population?', ['Increased size variation (anisocytosis)', 'Uniform normochromia', 'Absolute macrocytosis only', 'Decreased cell count'], 0, 'RDW quantifies variation in red cell size (anisocytosis).'],
            ],
            'clinical-chemistry' => [
                ['easy', 'What is the reference range for fasting blood glucose?', ['30-50 mg/dL', '70-100 mg/dL', '130-160 mg/dL', '180-220 mg/dL'], 1, 'Fasting plasma glucose is 70-100 mg/dL.'],
                ['easy', 'Which electrolyte is the most abundant cation in extracellular fluid?', ['Potassium', 'Calcium', 'Sodium', 'Magnesium'], 2, 'Sodium is the major extracellular cation.'],
                ['easy', 'The anion gap is calculated as which formula (in mEq/L)?', ['Na - (Cl + HCO3)', 'Na + Cl - HCO3', 'K + (Cl - HCO3)', 'Cl - (Na + HCO3)'], 0, 'Anion gap = sodium minus (chloride + bicarbonate).'],
                ['intermediate', 'Which two enzymes are most commonly elevated in cholestatic liver disease?', ['ALT and AST', 'ALP and GGT', 'CK and LDH', 'Amylase and lipase'], 1, 'Alkaline phosphatase and gamma-glutamyl transferase rise with cholestasis.'],
                ['intermediate', 'Creatinine clearance is used to estimate which physiologic parameter?', ['Liver function', 'Glomerular filtration rate', 'Tubular secretion', 'Renal blood flow'], 1, 'Creatinine clearance estimates the GFR.'],
                ['intermediate', 'HbA1c reflects average glycemic control over approximately what period?', ['2 days', '2 weeks', '2-3 months', '1 year'], 2, 'Glycated hemoglobin reflects blood glucose over the prior 2-3 months.'],
                ['advanced', 'Which cardiac biomarker rises earliest and is most specific after myocardial injury?', ['AST', 'LDH', 'Troponin', 'Myoglobin'], 2, 'Troponin I/T are the most specific and rise early after myocardial injury.'],
                ['advanced', 'A low serum albumin with a normal total protein most strongly suggests what?', ['Renal protein loss only', 'A monoclonal gammopathy', 'Reduced hepatic synthesis', 'Excess dietary protein'], 2, 'Low albumin with normal total protein points to decreased hepatic synthesis.'],
                ['advanced', 'The osmolal gap is most useful for detecting poisoning with which agent?', ['Acetaminophen', 'Ethylene glycol', 'Theophylline', 'Iron'], 1, 'An elevated osmolal gap suggests ethylene glycol or methanol ingestion.'],
                ['easy', 'What is the reference range for serum sodium?', ['120-130 mmol/L', '135-145 mmol/L', '150-160 mmol/L', '90-100 mmol/L'], 1, 'Serum sodium is approximately 135-145 mmol/L.'],
                ['intermediate', 'Which is the principal buffer system in blood?', ['Phosphate buffer', 'Bicarbonate buffer', 'Protein buffer', 'Ammonia buffer'], 1, 'The bicarbonate/carbonic acid system is the main blood buffer.'],
                ['advanced', 'Which formula estimates plasma osmolality (mmol/kg)?', ['2Na + glucose/18 + BUN/2.8', 'Na + K + Cl', '2(Na + K) - Cl', 'BUN/18 + glucose/2.8'], 0, 'Estimated osmolality = 2Na + glucose/18 + BUN/2.8.'],
            ],
            'microbiology' => [
                ['easy', 'Gram-positive cocci arranged in clusters are most consistent with which organism?', ['Streptococcus', 'Staphylococcus', 'Neisseria', 'Escherichia'], 1, 'Staphylococci appear as gram-positive cocci in clusters.'],
                ['easy', 'Which specimen is required to diagnose pulmonary tuberculosis?', ['Sputum', 'Urine', 'CSF', 'Stool'], 0, 'Pulmonary TB is diagnosed from sputum for acid-fast bacilli.'],
                ['easy', 'Which class of antibiotics inhibits bacterial cell wall synthesis?', ['Beta-lactams', 'Aminoglycosides', 'Tetracylines', 'Macrolides'], 0, 'Beta-lactam antibiotics (penicillins, cephalosporins) block cell wall synthesis.'],
                ['intermediate', 'On MacConkey agar, which organism typically produces pink (lactose-fermenting) colonies?', ['Salmonella', 'Shigella', 'Escherichia coli', 'Proteus'], 2, 'E. coli ferments lactose and appears pink on MacConkey.'],
                ['intermediate', 'Which staining technique shows gram-negative bacteria as pink/red?', ['Ziehl-Neelsen', 'Gram stain', 'India ink', 'Wright stain'], 1, 'Gram-negative bacteria stain pink/red with the Gram stain.'],
                ['intermediate', 'MALDI-TOF mass spectrometry is primarily used to do what in microbiology?', ['Quantify antibiotic levels', 'Identify organisms by protein profile', 'Detect antigen only', 'Measure bacterial growth rate'], 1, 'MALDI-TOF identifies bacteria by their protein mass spectrum.'],
                ['advanced', 'Which is the causative agent of syphilis?', ['Treponema pallidum', 'Borrelia burgdorferi', 'Chlamydia trachomatis', 'Mycoplasma pneumoniae'], 0, 'Syphilis is caused by the spirochete Treponema pallidum.'],
                ['advanced', 'A positive beta-lactamase (cefinase) test on an isolated organism indicates what?', ['Resistance to vancomycin', 'Penicillin resistance', 'Intrinsic gram-negative nature', 'Beta-hemolytic activity'], 1, 'Positive beta-lactamase indicates penicillin resistance.'],
                ['advanced', 'Which rapid test detects acid-fast bacilli and is commonly used for TB?', ['Gram stain', 'Ziehl-Neelsen stain', 'India ink', 'KOH prep'], 1, 'The Ziehl-Neelsen (acid-fast) stain detects mycobacteria.'],
                ['easy', 'Which technique is used to demonstrate bacterial motility?', ['Hanging-drop preparation', 'Gram stain', 'Capsule stain', 'India ink'], 0, 'The hanging-drop preparation assesses bacterial motility.'],
                ['intermediate', 'Which of the following is catalase-positive?', ['Streptococcus', 'Staphylococcus', 'Clostridium', 'Enterococcus'], 1, 'Staphylococci are catalase-positive; streptococci are catalase-negative.'],
                ['advanced', 'Which organism produces a spore and is anaerobic?', ['Staphylococcus aureus', 'Clostridium perfringens', 'Escherichia coli', 'Neisseria meningitidis'], 1, 'Clostridium species are spore-forming anaerobes.'],
            ],
            'immunology' => [
                ['easy', 'Which immunoglobulin is the most abundant antibody in serum?', ['IgA', 'IgM', 'IgG', 'IgE'], 2, 'IgG is the most abundant immunoglobulin in serum.'],
                ['easy', 'Which immunoglobulin crosses the placenta to protect the fetus?', ['IgG', 'IgA', 'IgM', 'IgE'], 0, 'IgG crosses the placenta.'],
                ['easy', 'Which immunoglobulin is the first produced during a primary immune response?', ['IgG', 'IgA', 'IgM', 'IgE'], 2, 'IgM is the first antibody produced.'],
                ['intermediate', 'Which cell type produces antibodies?', ['Helper T cell', 'Cytotoxic T cell', 'Plasma cell', 'Natural killer cell'], 2, 'Plasma cells (from B cells) secrete antibodies.'],
                ['intermediate', 'Which immunoglobulin mediates allergic (type I) reactions?', ['IgG', 'IgA', 'IgM', 'IgE'], 3, 'IgE mediates immediate hypersensitivity reactions.'],
                ['intermediate', 'A positive PPD (Mantoux) skin test is an example of which hypersensitivity type?', ['Type I', 'Type II', 'Type III', 'Type IV'], 3, 'The Mantoux test is a type IV delayed hypersensitivity reaction.'],
                ['advanced', 'Which cells directly kill virus-infected cells?', ['CD8 cytotoxic T cells', 'CD4 helper T cells', 'Plasma cells', 'Regulatory T cells'], 0, 'CD8 cytotoxic T lymphocytes kill infected cells.'],
                ['advanced', 'Which complement component is most abundant and serves as the junction of the pathways?', ['C1', 'C3', 'C5', 'C9'], 1, 'C3 is the most abundant and is where all complement pathways converge.'],
                ['advanced', 'Which antinuclear antibody pattern is most characteristic of systemic lupus erythematosus?', ['Speckled', 'Homogeneous', 'Nucleolar', 'Centromere'], 1, 'A homogeneous ANA pattern is commonly seen in SLE.'],
                ['easy', 'Which immunoglobulin is primarily responsible for mucosal immunity?', ['IgG', 'IgA', 'IgM', 'IgE'], 1, 'Secretory IgA defends mucosal surfaces.'],
                ['intermediate', 'Which immunoglobulin is the first to rise in a primary immune response?', ['IgG', 'IgA', 'IgM', 'IgE'], 2, 'IgM appears first in a primary response.'],
                ['advanced', 'IgE is most directly associated with which type of hypersensitivity?', ['Type I', 'Type II', 'Type III', 'Type IV'], 0, 'IgE mediates type I (immediate) hypersensitivity.'],
            ],
            'blood-banking-transfusion' => [
                ['easy', 'Which blood group is the universal red cell donor?', ['AB positive', 'A negative', 'O negative', 'B positive'], 2, 'O negative red cells lack A, B, and Rh antigens.'],
                ['easy', 'Which blood group is the universal plasma donor?', ['AB', 'O', 'A', 'B'], 0, 'AB plasma contains no anti-A or anti-B antibodies.'],
                ['easy', 'An antibody screen (indirect antiglobulin test) primarily detects what?', ['In vivo sensitization', 'Clinically significant alloantibodies', 'Complement activation only', 'Platelet function'], 1, 'The antibody screen detects clinically significant red cell alloantibodies.'],
                ['intermediate', 'Which antibody is the classic cold-reactive autoantibody?', ['Anti-I (IgM)', 'Anti-K (IgG)', 'Anti-D (IgG)', 'Anti-e (IgG)'], 0, 'Cold autoantibodies are usually IgM anti-I.'],
                ['intermediate', 'Which Kell system antigen is the most immunogenic?', ['k', 'K (Kell1)', 'Kpa', 'Jsa'], 1, 'The K (Kell1) antigen is the most immunogenic of the Kell system.'],
                ['intermediate', 'What does a positive direct antiglobulin test (DAT) indicate?', ['In vitro sensitization only', 'In vivo red cell sensitization', 'A negative antibody screen', 'An antibody in the reagent'], 1, 'A positive DAT shows the red cells are coated with antibody/complement in vivo.'],
                ['advanced', 'Which test confirms that patient serum causes red cell agglutination through complement at 37C?', ['Cold panel', 'Anti-human globulin test', 'Coombs control', 'Saline agglutination'], 1, 'IgG alloantibodies are detected with the antiglobulin (Coombs) phase.'],
                ['advanced', 'Anti-D prophylaxis (RhoGAM) is given to an Rh-negative mother to prevent what?', ['Hemolytic disease of the fetus/newborn', 'ABO incompatibility', 'Kell sensitization', 'Febrile reactions'], 0, 'Anti-D prevents maternal isoimmunization and HDN in subsequent Rh-positive pregnancies.'],
                ['advanced', 'A discrepancy in forward and reverse blood grouping is most often explained by?', ['ABO antibodies', 'A weak/missing antigen or interfering antibody', 'Platelet clumping', 'Cold complement'], 1, 'Blood group discrepancies arise from weak antigens or unexpected antibodies.'],
                ['easy', 'Which antigen is the major Rh antigen?', ['C', 'D', 'E', 'c'], 1, 'The D antigen is the major Rh (Rh-positive) antigen.'],
                ['intermediate', 'The antiglobulin (Coombs) test is used to detect what?', ['Complement only', 'Antibody or complement bound to red cells', 'Platelet antibodies', 'ABO forward discrepancy'], 1, 'The antiglobulin test detects red-cell-bound antibody or complement.'],
                ['advanced', 'Why is anti-D still a leading cause of haemolytic disease of the newborn?', ['It is IgM', 'It is a potent IgG that crosses the placenta', 'It is not immunogenic', 'It is a cold antibody'], 1, 'Anti-D is a potent IgG that crosses the placenta and sensitises fetal red cells.'],
            ],
            'other' => [
                ['easy', 'What is the normal specific gravity range of urine?', ['1.005-1.030', '1.050-1.080', '1.000-1.001', '1.040-1.060'], 0, 'Normal urine specific gravity is about 1.005-1.030.'],
                ['easy', 'Which tube top (additive) is used for coagulation studies?', ['Lavender (EDTA)', 'Light blue (citrate)', 'Green (heparin)', 'Red (no additive)'], 1, 'Sodium citrate (light blue) is used for coagulation testing.'],
                ['easy', 'How many blood culture sets are generally recommended for an adult?', ['One from one site', 'Two from two separate sites', 'Four from one site', 'Three from a single draw'], 1, 'Two sets from two separate venipuncture sites are recommended.'],
                ['intermediate', 'Westgard rules are applied in the laboratory for what purpose?', ['Calibration of instruments only', 'Statistical quality control', 'Specimen labeling', 'Results reporting'], 1, 'Westgard rules flag out-of-control analytical runs.'],
                ['intermediate', 'Which step is critical to prevent specimen contamination during collection?', ['Lysis of cells', 'Aseptic technique', 'Centrifugation', 'Refrigerating before clotting'], 1, 'Aseptic technique prevents contamination and hemolysis.'],
                ['intermediate', 'Which molarity unit is defined as moles of solute per liter of solution?', ['Molality', 'Molarity', 'Normality', 'Formality'], 1, 'Molarity is moles of solute per liter of solution.'],
                ['advanced', 'A mixing study that fails to correct a prolonged PT/PTT suggests?', ['A factor deficiency', 'An inhibitor present', 'A reagent error', 'Platelet dysfunction'], 1, 'Failure to correct on mixing indicates an inhibitor.'],
                ['advanced', 'Which method is used to confirm a specific factor inhibitor in hemophilia A?', ['Bethesda assay', 'D-dimer', 'Wells score', 'Fibrinogen'], 0, 'The Bethesda assay quantifies factor VIII inhibitors.'],
                ['advanced', 'A 95% reference interval from a healthy population is computed using which method?', ['Mean +/- 1 SD', '2.5th-97.5th percentile', 'Median +/- 1.96 SD of log data', 'Mode +/- 2 SD'], 1, 'Reference intervals are typically the 2.5th-97.5th percentile.'],
                ['easy', 'Which term means a test is performed immediately on an urgent request?', ['Batch', 'Stat', 'Fasting', 'Timed'], 1, 'A "stat" test is carried out immediately.'],
                ['intermediate', 'What does a "fasting" specimen require from the patient?', ['Exercise before collection', 'No food eaten for a set period', 'Extra hydration', 'Blood drawn from a vein only'], 1, 'A fasting specimen requires the patient to abstain from food for the specified time.'],
                ['advanced', 'A Westgard 1_2s rule is violated when what occurs?', ['One control exceeds 2 SD', 'Two controls exceed 1 SD', 'Four controls exceed 1 SD', 'A trending run'], 0, 'The 1_2s rule flags a single control exceeding 2 standard deviations.'],
            ],
        ];
    }
}
