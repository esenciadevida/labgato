<?php

namespace App\Services;

use App\Models\ReferenceRange;
use Illuminate\Http\UploadedFile;

class ReferenceRangeImportService
{
    public const REQUIRED_COLUMNS = ['name', 'analyte_name', 'unit'];

    public const OPTIONAL_COLUMNS = [
        'parameter_key', 'specimen_type', 'methodology', 'population_group', 'age_min', 'age_max',
        'sex', 'si_unit', 'lower_limit', 'upper_limit', 'critical_low',
        'critical_high', 'conversion_factor', 'source_citation',
    ];

    public function parseAndValidate(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        if (! $handle) {
            return ['valid' => [], 'errors' => [], 'total' => 0, 'header_error' => 'The CSV file could not be opened.'];
        }

        $headers = fgetcsv($handle, 0, ',');
        if ($headers === false || $headers === [null] || $headers === []) {
            fclose($handle);

            return ['valid' => [], 'errors' => [], 'total' => 0, 'header_error' => 'The CSV file is empty or could not be read.'];
        }

        $columnMap = $this->mapHeaders($headers);

        $missing = array_diff(self::REQUIRED_COLUMNS, array_keys($columnMap));
        if (! empty($missing)) {
            fclose($handle);

            return [
                'valid' => [],
                'errors' => [],
                'total' => 0,
                'header_error' => 'Required column(s) missing: '.implode(', ', $missing).'. Please use the provided template.',
            ];
        }

        $valid = [];
        $errors = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $rowNumber++;

            if ($row === [null] || $row === []) {
                continue;
            }

            $rowData = $this->parseRow($row, $columnMap);

            if ($rowData['skip']) {
                continue;
            }

            $validationErrors = $this->validateRow($rowData);
            if ($validationErrors) {
                $errors[$rowNumber] = [$validationErrors];
            } else {
                $valid[$rowNumber] = $rowData;
            }
        }

        fclose($handle);

        return [
            'valid' => $valid,
            'errors' => $errors,
            'total' => count($valid) + count($errors),
            'header_error' => null,
        ];
    }

    private function mapHeaders(array $headers): array
    {
        $expected = array_merge(self::REQUIRED_COLUMNS, self::OPTIONAL_COLUMNS);

        $map = [];
        foreach ($headers as $index => $header) {
            $normalized = strtolower(trim((string) $header));

            if ($index === 0) {
                $normalized = strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', $normalized)));
            }

            if (in_array($normalized, $expected, true)) {
                $map[$normalized] = $index;
            }
        }

        return $map;
    }

    private function parseRow(array $row, array $columnMap): array
    {
        $expected = array_merge(self::REQUIRED_COLUMNS, self::OPTIONAL_COLUMNS);

        $data = [];
        foreach ($expected as $field) {
            $data[$field] = isset($columnMap[$field]) ? $this->safeGetCell($row, $columnMap[$field]) : null;
        }

        $data['skip'] = empty(trim($data['name'] ?? '')) && empty(trim($data['analyte_name'] ?? ''));

        return $data;
    }

    private function safeGetCell(array $row, int $index): ?string
    {
        if (! isset($row[$index])) {
            return null;
        }

        $value = trim((string) $row[$index]);

        return $value === '' ? null : $value;
    }

    private function validateRow(array $rowData): ?string
    {
        $name = (string) ($rowData['name'] ?? '');
        $analyteName = (string) ($rowData['analyte_name'] ?? '');
        $unit = (string) ($rowData['unit'] ?? '');
        $sex = (string) ($rowData['sex'] ?? '');

        if (empty(trim($name))) {
            return 'Name is required.';
        }

        if (empty(trim($analyteName))) {
            return 'Analyte name is required.';
        }

        if (empty(trim($unit))) {
            return 'Unit is required.';
        }

        if (! in_array($sex, ['', 'any', 'male', 'female'], true)) {
            return "Invalid sex value \"{$sex}\". Allowed values: any, male, female.";
        }

        $parameterKey = (string) ($rowData['parameter_key'] ?? '');

        if ($parameterKey !== '' && ! preg_match('/^[a-z][a-z0-9_]*$/', $parameterKey)) {
            return 'Parameter key must be snake_case.';
        }

        foreach (['age_min', 'age_max', 'lower_limit', 'upper_limit', 'critical_low', 'critical_high', 'conversion_factor'] as $field) {
            $value = $rowData[$field] ?? '';

            if ($value !== null && $value !== '' && ! is_numeric($value)) {
                return ucfirst(str_replace('_', ' ', $field)).' must be numeric.';
            }
        }

        $lowerLimit = $rowData['lower_limit'] ?? '';
        $upperLimit = $rowData['upper_limit'] ?? '';

        if ($lowerLimit !== '' && $upperLimit !== '' && (float) $lowerLimit > (float) $upperLimit) {
            return 'Lower limit must not be greater than upper limit.';
        }

        return null;
    }

    public function import(array $validRows, string $status, string $duplicateHandling): array
    {
        $status = in_array($status, ['draft', 'published'], true) ? $status : 'draft';
        $duplicateHandling = in_array($duplicateHandling, ['skip', 'update'], true) ? $duplicateHandling : 'skip';

        $imported = 0;
        $skipped = 0;
        $updated = 0;

        foreach ($validRows as $rowData) {
            $name = $rowData['name'];
            $analyteName = $rowData['analyte_name'];
            $parameterKey = $rowData['parameter_key'] ?? null;
            $specimenType = $rowData['specimen_type'] ?? null;
            $methodology = $rowData['methodology'] ?? null;
            $populationGroup = $rowData['population_group'] ?? null;
            $ageMin = $rowData['age_min'] ?? null;
            $ageMax = $rowData['age_max'] ?? null;
            $sex = $rowData['sex'] ?? null;
            $unit = $rowData['unit'];
            $siUnit = $rowData['si_unit'] ?? null;
            $lowerLimit = $rowData['lower_limit'] ?? null;
            $upperLimit = $rowData['upper_limit'] ?? null;
            $criticalLow = $rowData['critical_low'] ?? null;
            $criticalHigh = $rowData['critical_high'] ?? null;
            $conversionFactor = $rowData['conversion_factor'] ?? null;
            $sourceCitation = $rowData['source_citation'] ?? null;

            $existing = ReferenceRange::where('analyte_name', $analyteName)
                ->when($sex, fn ($q) => $q->where('sex', $sex))
                ->first();

            $slug = $this->generateSlug($name);

            if ($existing) {
                if ($duplicateHandling === 'skip') {
                    $skipped++;

                    continue;
                }

                if ($duplicateHandling === 'update') {
                    $existing->name = $name;
                    $existing->analyte_name = $analyteName;
                    $existing->parameter_key = $parameterKey;
                    $existing->specimen_type = $specimenType;
                    $existing->methodology = $methodology;
                    $existing->population_group = $populationGroup;
                    $existing->age_min = $ageMin;
                    $existing->age_max = $ageMax;
                    $existing->sex = $sex;
                    $existing->unit = $unit;
                    $existing->si_unit = $siUnit;
                    $existing->lower_limit = $lowerLimit;
                    $existing->upper_limit = $upperLimit;
                    $existing->critical_low = $criticalLow;
                    $existing->critical_high = $criticalHigh;
                    $existing->conversion_factor = $conversionFactor;
                    $existing->source_citation = $sourceCitation;
                    $existing->status = $status;
                    $existing->save();
                    $updated++;

                    continue;
                }
            }

            $range = new ReferenceRange([
                'name' => $name,
                'slug' => $slug,
                'analyte_name' => $analyteName,
                'parameter_key' => $parameterKey,
                'specimen_type' => $specimenType,
                'methodology' => $methodology,
                'population_group' => $populationGroup,
                'age_min' => $ageMin,
                'age_max' => $ageMax,
                'sex' => $sex,
                'unit' => $unit,
                'si_unit' => $siUnit,
                'lower_limit' => $lowerLimit,
                'upper_limit' => $upperLimit,
                'critical_low' => $criticalLow,
                'critical_high' => $criticalHigh,
                'conversion_factor' => $conversionFactor,
                'source_citation' => $sourceCitation,
                'status' => $status,
            ]);

            $range->save();
            $imported++;
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'updated' => $updated,
        ];
    }

    private function generateSlug(string $name): string
    {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', strtolower($name)));

        return trim($slug, '-');
    }
}
