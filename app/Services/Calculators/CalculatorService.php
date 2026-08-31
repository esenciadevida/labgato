<?php

namespace App\Services\Calculators;

use App\Contracts\CalculatorInterface;
use App\Models\Calculator;
use App\Services\Calculators\Engines\AbsoluteEosinophilCountCalculator;
use App\Services\Calculators\Engines\AbsoluteLymphocyteCountCalculator;
use App\Services\Calculators\Engines\AlbuminGlobulinRatioCalculator;
use App\Services\Calculators\Engines\AncCalculator;
use App\Services\Calculators\Engines\AnionGapCalculator;
use App\Services\Calculators\Engines\AntibioticDilutionCalculator;
use App\Services\Calculators\Engines\ApriCalculator;
use App\Services\Calculators\Engines\AtherogenicIndexPlasmaCalculator;
use App\Services\Calculators\Engines\BacterialConcentrationCalculator;
use App\Services\Calculators\Engines\BasalMetabolicRateCalculator;
use App\Services\Calculators\Engines\CalciumPhosphateProductCalculator;
use App\Services\Calculators\Engines\BicarbonateGapCalculator;
use App\Services\Calculators\Engines\BilirubinRatioCalculator;
use App\Services\Calculators\Engines\BmiCalculator;
use App\Services\Calculators\Engines\BodySurfaceAreaCalculator;
use App\Services\Calculators\Engines\BunCreatinineRatioCalculator;
use App\Services\Calculators\Engines\BunUreaConverterCalculator;
use App\Services\Calculators\Engines\CfuPerMlCalculator;
use App\Services\Calculators\Engines\CkdEpi2009Calculator;
use App\Services\Calculators\Engines\CkdEpi2021Calculator;
use App\Services\Calculators\Engines\CockcroftGaultCalculator;
use App\Services\Calculators\Engines\ColonyCountCalculator;
use App\Services\Calculators\Engines\CorrectedCalciumCalculator;
use App\Services\Calculators\Engines\CorrectedChlorideCalculator;
use App\Services\Calculators\Engines\CorrectedReticulocyteCountCalculator;
use App\Services\Calculators\Engines\CorrectedSodiumCalculator;
use App\Services\Calculators\Engines\CorrectedTotalProteinCalculator;
use App\Services\Calculators\Engines\CorrectedWbcCalculator;
use App\Services\Calculators\Engines\CorrectedPhenytoinCalculator;
use App\Services\Calculators\Engines\DeltaGapCalculator;
use App\Services\Calculators\Engines\DeltaRatioCalculator;
use App\Services\Calculators\Engines\DeRitisRatioCalculator;
use App\Services\Calculators\Engines\EnglandFraserIndexCalculator;
use App\Services\Calculators\Engines\EstimatedAverageGlucoseCalculator;
use App\Services\Calculators\Engines\Fib4Calculator;
use App\Services\Calculators\Engines\FractionalExcretionOfPotassiumCalculator;
use App\Services\Calculators\Engines\FractionalExcretionOfSodiumCalculator;
use App\Services\Calculators\Engines\FractionalExcretionOfUreaCalculator;
use App\Services\Calculators\Engines\FractionalExcretionOfUricAcidCalculator;
use App\Services\Calculators\Engines\FreeWaterClearanceCalculator;
use App\Services\Calculators\Engines\GlobulinCalculator;
use App\Services\Calculators\Engines\GlucoseUnitConversionCalculator;
use App\Services\Calculators\Engines\GreenKingIndexCalculator;
use App\Services\Calculators\Engines\HomaBetaCalculator;
use App\Services\Calculators\Engines\HomaIrCalculator;
use App\Services\Calculators\Engines\LdlFriedewaldCalculator;
use App\Services\Calculators\Engines\LdlHdlRatioCalculator;
use App\Services\Calculators\Engines\LdlMartinHopkinsCalculator;
use App\Services\Calculators\Engines\LdlSampsonCalculator;
use App\Services\Calculators\Engines\McFarlandTurbidityCalculator;
use App\Services\Calculators\Engines\MchCalculator;
use App\Services\Calculators\Engines\MchcCalculator;
use App\Services\Calculators\Engines\McvCalculator;
use App\Services\Calculators\Engines\MeldThreeCalculator;
use App\Services\Calculators\Engines\MentzerIndexCalculator;
use App\Services\Calculators\Engines\MicDilutionCalculator;
use App\Services\Calculators\Engines\MonocyteLymphocyteRatioCalculator;
use App\Services\Calculators\Engines\NafldFibrosisScoreCalculator;
use App\Services\Calculators\Engines\NeutrophilLymphocyteRatioCalculator;
use App\Services\Calculators\Engines\NonHdlCholesterolCalculator;
use App\Services\Calculators\Engines\OsmolalGapCalculator;
use App\Services\Calculators\Engines\OsmolarClearanceCalculator;
use App\Services\Calculators\Engines\PlateletLymphocyteRatioCalculator;
use App\Services\Calculators\Engines\QuickiCalculator;
use App\Services\Calculators\Engines\RbcIndicesCalculator;
use App\Services\Calculators\Engines\RdwCalculator;
use App\Services\Calculators\Engines\RdwIndexCalculator;
use App\Services\Calculators\Engines\RenalFailureIndexCalculator;
use App\Services\Calculators\Engines\ReticulocyteIndexCalculator;
use App\Services\Calculators\Engines\ReticulocyteProductionIndexCalculator;
use App\Services\Calculators\Engines\SerialDilutionCalculator;
use App\Services\Calculators\Engines\SerumOsmolalityCalculator;
use App\Services\Calculators\Engines\ShineLalIndexCalculator;
use App\Services\Calculators\Engines\SmearPlateletEstimateCalculator;
use App\Services\Calculators\Engines\SrivastavaIndexCalculator;
use App\Services\Calculators\Engines\StrongIonDifferenceCalculator;
use App\Services\Calculators\Engines\SystemicImmuneInflammationIndexCalculator;
use App\Services\Calculators\Engines\TotalCholesterolHdlRatioCalculator;
use App\Services\Calculators\Engines\TriglycerideHdlRatioCalculator;
use App\Services\Calculators\Engines\UrineAnionGapCalculator;
use App\Services\Calculators\Engines\WintersFormulaCalculator;
use App\Services\ReferenceRangeService;
use App\Support\NumberFormatter;

class CalculatorService
{
    private const ALLOWED_ENGINES = [
        CorrectedWbcCalculator::class => 'Corrected WBC for Nucleated RBCs Calculator',
        AncCalculator::class => 'Absolute Neutrophil Count Calculator',
        AbsoluteLymphocyteCountCalculator::class => 'Absolute Lymphocyte Count Calculator',
        AbsoluteEosinophilCountCalculator::class => 'Absolute Eosinophil Count Calculator',
        RbcIndicesCalculator::class => 'RBC Indices Calculator',
        McvCalculator::class => 'MCV Calculator',
        MchCalculator::class => 'MCH Calculator',
        MchcCalculator::class => 'MCHC Calculator',
        RdwCalculator::class => 'RDW-CV Calculator',
        ReticulocyteProductionIndexCalculator::class => 'Reticulocyte Production Index',
        CorrectedReticulocyteCountCalculator::class => 'Corrected Reticulocyte Count Calculator',
        ReticulocyteIndexCalculator::class => 'Reticulocyte Index Calculator',
        MentzerIndexCalculator::class => 'Mentzer Index Calculator',
        RdwIndexCalculator::class => 'RDW Index Calculator',
        EnglandFraserIndexCalculator::class => 'England & Fraser Index Calculator',
        GreenKingIndexCalculator::class => 'Green & King Index Calculator',
        SrivastavaIndexCalculator::class => 'Srivastava Index Calculator',
        ShineLalIndexCalculator::class => 'Shine & Lal Index Calculator',
        PlateletLymphocyteRatioCalculator::class => 'Platelet-to-Lymphocyte Ratio Calculator',
        NeutrophilLymphocyteRatioCalculator::class => 'Neutrophil-to-Lymphocyte Ratio Calculator',
        MonocyteLymphocyteRatioCalculator::class => 'Monocyte-to-Lymphocyte Ratio Calculator',
        SystemicImmuneInflammationIndexCalculator::class => 'Systemic Immune-Inflammation Index Calculator',
        AnionGapCalculator::class => 'Anion Gap Calculator',
        DeltaGapCalculator::class => 'Delta Gap Calculator',
        DeltaRatioCalculator::class => 'Delta Ratio Calculator',
        CorrectedSodiumCalculator::class => 'Corrected Sodium for Hyperglycemia Calculator',
        CorrectedCalciumCalculator::class => 'Corrected Calcium Calculator',
        CkdEpi2021Calculator::class => 'eGFR (CKD-EPI 2021) Calculator',
        CkdEpi2009Calculator::class => 'eGFR (CKD-EPI 2009) Calculator',
        CockcroftGaultCalculator::class => 'Creatinine Clearance (Cockcroft-Gault) Calculator',
        BunCreatinineRatioCalculator::class => 'BUN/Creatinine Ratio Calculator',
        FractionalExcretionOfSodiumCalculator::class => 'Fractional Excretion of Sodium (FENa) Calculator',
        FractionalExcretionOfUreaCalculator::class => 'Fractional Excretion of Urea (FEUrea) Calculator',
        FractionalExcretionOfPotassiumCalculator::class => 'Fractional Excretion of Potassium (FEK) Calculator',
        FractionalExcretionOfUricAcidCalculator::class => 'Fractional Excretion of Uric Acid (FEUA) Calculator',
        RenalFailureIndexCalculator::class => 'Renal Failure Index Calculator',
        FreeWaterClearanceCalculator::class => 'Free Water Clearance Calculator',
        OsmolarClearanceCalculator::class => 'Osmolar Clearance Calculator',
        SerumOsmolalityCalculator::class => 'Estimated Serum Osmolality Calculator',
        OsmolalGapCalculator::class => 'Osmolal Gap Calculator',
        UrineAnionGapCalculator::class => 'Urine Anion Gap Calculator',
        StrongIonDifferenceCalculator::class => 'Strong Ion Difference Calculator',
        CorrectedChlorideCalculator::class => 'Corrected Chloride Calculator',
        BicarbonateGapCalculator::class => 'Bicarbonate Gap Calculator',
        DeRitisRatioCalculator::class => 'AST/ALT Ratio (De Ritis Ratio) Calculator',
        AlbuminGlobulinRatioCalculator::class => 'Albumin/Globulin Ratio Calculator',
        GlobulinCalculator::class => 'Globulin Calculator',
        CorrectedTotalProteinCalculator::class => 'Corrected Total Protein Calculator',
        BilirubinRatioCalculator::class => 'Bilirubin Ratio Calculator',
        ApriCalculator::class => 'APRI Score Calculator',
        Fib4Calculator::class => 'FIB-4 Index Calculator',
        NafldFibrosisScoreCalculator::class => 'NAFLD Fibrosis Score Calculator',
        LdlFriedewaldCalculator::class => 'LDL Cholesterol (Friedewald) Calculator',
        LdlMartinHopkinsCalculator::class => 'LDL Cholesterol (Martin-Hopkins) Calculator',
        LdlSampsonCalculator::class => 'LDL Cholesterol (Sampson) Calculator',
        NonHdlCholesterolCalculator::class => 'Non-HDL Cholesterol Calculator',
        TotalCholesterolHdlRatioCalculator::class => 'Total Cholesterol/HDL Ratio Calculator',
        LdlHdlRatioCalculator::class => 'LDL/HDL Ratio Calculator',
        TriglycerideHdlRatioCalculator::class => 'Triglyceride/HDL Ratio Calculator',
        AtherogenicIndexPlasmaCalculator::class => 'Atherogenic Index of Plasma (AIP) Calculator',
        BunUreaConverterCalculator::class => 'BUN/Urea Converter',
        HomaIrCalculator::class => 'HOMA-IR Calculator',
        HomaBetaCalculator::class => 'HOMA-Beta Calculator',
        QuickiCalculator::class => 'QUICKI Calculator',
        EstimatedAverageGlucoseCalculator::class => 'Estimated Average Glucose (eAG) Calculator',
        GlucoseUnitConversionCalculator::class => 'Glucose Unit Conversion Calculator',
        BmiCalculator::class => 'Body Mass Index (BMI) Calculator',
        BodySurfaceAreaCalculator::class => 'Body Surface Area (BSA) Calculator',
        BasalMetabolicRateCalculator::class => 'Basal Metabolic Rate (BMR) Calculator',
        McFarlandTurbidityCalculator::class => 'McFarland Turbidity to CFU/mL Calculator',
        CfuPerMlCalculator::class => 'CFU/mL Calculator',
        ColonyCountCalculator::class => 'Colony Count Calculator',
        SerialDilutionCalculator::class => 'Serial Dilution Calculator',
        BacterialConcentrationCalculator::class => 'Bacterial Concentration (OD600) Calculator',
        AntibioticDilutionCalculator::class => 'Antibiotic Dilution Calculator',
        MicDilutionCalculator::class => 'MIC Dilution Calculator',
        WintersFormulaCalculator::class => 'Winters Formula (Anticipated PaCO2) Calculator',
        MeldThreeCalculator::class => 'MELD 3.0 Calculator',
        CalciumPhosphateProductCalculator::class => 'Calcium-Phosphate Product Calculator',
        CorrectedPhenytoinCalculator::class => 'Corrected Phenytoin Calculator',
        SmearPlateletEstimateCalculator::class => 'Smear Platelet Estimate Calculator',
    ];

    private const ENGINE_TYPE = 'php_class';

    public function __construct(
        private readonly ReferenceRangeService $referenceRanges,
    ) {}

    public function availableEngines(): array
    {
        return self::ALLOWED_ENGINES;
    }

    public function engineLabel(string $engineReference): string
    {
        return self::ALLOWED_ENGINES[$engineReference] ?? $engineReference;
    }

    public function resolveEngine(Calculator $calculator): CalculatorInterface
    {
        if ($calculator->engine !== self::ENGINE_TYPE) {
            throw new \InvalidArgumentException('Unsupported engine type: '.$calculator->engine);
        }

        $engineClass = $this->resolveEngineClass($calculator);

        if ($engineClass === null) {
            throw new \InvalidArgumentException('Calculator engine is not registered.');
        }

        $engine = app($engineClass);

        if (! $engine instanceof CalculatorInterface) {
            throw new \InvalidArgumentException('Calculator engine does not implement CalculatorInterface.');
        }

        return $engine;
    }

    private function resolveEngineClass(Calculator $calculator): ?string
    {
        if ($calculator->engine === self::ENGINE_TYPE) {
            return isset(self::ALLOWED_ENGINES[$calculator->engine_reference])
                ? $calculator->engine_reference
                : null;
        }

        foreach ([$calculator->engine_reference, $calculator->engine] as $reference) {
            if (isset(self::ALLOWED_ENGINES[$reference])) {
                return $reference;
            }

            $class = array_search($reference, self::ALLOWED_ENGINES, true);

            if ($class !== false) {
                return $class;
            }
        }

        return null;
    }

    public function validateInputs(array $inputSchema, array $inputs): array
    {
        $errors = [];

        foreach ($inputSchema as $field) {
            $key = $field['key'] ?? null;
            $label = $field['label'] ?? $key;

            if ($key === null) {
                continue;
            }

            $value = $inputs[$key] ?? null;
            $isRequired = (bool) ($field['required'] ?? false);
            $isEmpty = $value === null || $value === '';

            if ($isRequired && $isEmpty) {
                $errors[$key][] = "{$label} is required.";

                continue;
            }

            if ($isEmpty) {
                continue;
            }

            $type = $field['type'] ?? 'text';

            if ($type === 'number' && ! is_numeric($value)) {
                $errors[$key][] = "{$label} must be a number.";

                continue;
            }

            if ($type === 'select' && isset($field['options']) && is_array($field['options'])) {
                $allowed = $this->allowedSelectValues($field['options']);
                if (! in_array((string) $value, $allowed)) {
                    $errors[$key][] = "{$label} contains an invalid selection.";
                }
            }

            if (is_numeric($value)) {
                $number = (float) $value;

                if (isset($field['min']) && is_numeric($field['min']) && $number < (float) $field['min']) {
                    $errors[$key][] = "{$label} must be at least {$field['min']}.";
                }

                if (isset($field['max']) && is_numeric($field['max']) && $number > (float) $field['max']) {
                    $errors[$key][] = "{$label} must be at most {$field['max']}.";
                }
            }
        }

        return $errors;
    }

    private function allowedSelectValues(array $options): array
    {
        return array_map(
            static fn ($option): string => is_array($option) ? (string) ($option['value'] ?? '') : (string) $option,
            $options,
        );
    }

    public function run(Calculator $calculator, array $inputs): array
    {
        $inputSchema = is_array($calculator->input_schema) ? $calculator->input_schema : [];
        $outputSchema = is_array($calculator->output_schema) ? $calculator->output_schema : [];

        $errors = $this->validateInputs($inputSchema, $inputs);

        if (! empty($errors)) {
            return ['outputs' => [], 'errors' => $errors];
        }

        $engineInputs = [];

        foreach ($inputSchema as $field) {
            $key = $field['key'] ?? null;

            if ($key === null) {
                continue;
            }

            if (isset($inputs[$key]) && $inputs[$key] !== '') {
                $engineInputs[$key] = $inputs[$key];
            }
        }

        try {
            $engine = $this->resolveEngine($calculator);
            $rawOutputs = $engine->calculate($engineInputs);
        } catch (\Throwable $e) {
            return ['outputs' => [], 'errors' => ['_calculation' => ['Unable to calculate a result.']]];
        }

        $outputs = [];

        foreach ($outputSchema as $field) {
            $key = $field['key'] ?? null;

            if ($key === null || ! array_key_exists($key, $rawOutputs)) {
                continue;
            }

            $rawValue = (float) $rawOutputs[$key];
            $formatter = app(NumberFormatter::class);
            $value = $formatter->format($rawValue, [
                'rounding' => $field['rounding'] ?? 'none',
                'precision' => $field['precision'] ?? 0,
            ]);

            $outputs[$key] = [
                'label' => $field['label'] ?? $key,
                'unit' => $field['unit'] ?? null,
                'value' => $value,
                'reference' => $this->resolveReference($field, $rawValue),
            ];
        }

        return ['outputs' => $outputs, 'errors' => []];
    }

    private function resolveReference(array $field, float $value): ?array
    {
        $parameterKey = $field['reference_key'] ?? null;

        if (! is_string($parameterKey) || $parameterKey === '') {
            return null;
        }

        $range = $this->referenceRanges->forParameter($parameterKey);

        if ($range === null) {
            return null;
        }

        return [
            'display' => $range->referenceDisplay(),
            'flag' => $this->referenceRanges->evaluate($value, (string) ($field['unit'] ?? ''), $range),
            'url' => $range->published_at !== null
                ? route('public.reference-ranges.show', $range->slug)
                : null,
        ];
    }
}
