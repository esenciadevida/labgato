<?php

/**
 * Builds per-domain question JSON files for the ASCPi MLS bank.
 *
 * Source: dump/questions/<domain>.php (each returns a list of compact items)
 * Output: database/data/questions/<domain>.json
 *
 * Compact item shape:
 *   ['case_text', 'stem', 'difficulty', 'correct-letter', o1, o2, o3, o4, 'explanation']
 *
 * Run: php database/data/questions/builder.php
 */

require __DIR__.'/../../../vendor/autoload.php';

$domains = [
    'Clinical Chemistry'    => __DIR__.'/../../../dump/questions/chem.php',
    'Hematology'            => __DIR__.'/../../../dump/questions/heme.php',
    'Microbiology'          => __DIR__.'/../../../dump/questions/micro.php',
    'Blood Banking/Transfusion' => __DIR__.'/../../../dump/questions/bb.php',
    'Immunology'            => __DIR__.'/../../../dump/questions/immun.php',
    'Urinalysis & Body Fluids' => __DIR__.'/../../../dump/questions/ua.php',
    'Laboratory Operations' => __DIR__.'/../../../dump/questions/labops.php',
    'Molecular Diagnostics' => __DIR__.'/../../../dump/questions/mol.php',
];

$outDir = __DIR__;

$STEM_FLAGS = ['except', 'false', 'not true', 'incorrect'];
$OPTION_FLAGS = ['above', 'of these'];
$VIGNETTE = ['patient', 'result', 'results', 'specimen', 'mg/dl', 'mmol', 'x10', 'smear', 'stain', 'culture', 'value', 'years'];

$totalCreated = 0;
$errors = [];
$warnings = [];

foreach ($domains as $category => $sourceFile) {
    if (! is_file($sourceFile)) {
        $errors[] = "Missing source: $sourceFile";

        continue;
    }

    $items = require $sourceFile;

    $questions = [];
    foreach ($items as $i => $item) {
        $loc = "[$category #$i]";
        if (count($item) !== 9) {
            $errors[] = "$loc expected 9 elements, got ".count($item);

            continue;
        }
        [$caseText, $stem, $difficulty, $correct, $o1, $o2, $o3, $o4, $explanation] = $item;

        $correct = strtoupper($correct);
        if (! preg_match('/^[A-D]$/', $correct)) {
            $errors[] = "$loc bad correct letter: $correct";

            continue;
        }

        $options = [$o1, $o2, $o3, $o4];

        // stem quality gates
        foreach ($STEM_FLAGS as $needle) {
            if (stripos((string) $stem, $needle) !== false) {
                $errors[] = "$loc stem contains banned phrase '$needle': ".mb_substr($stem, 0, 60);
            }
        }
        foreach ($OPTION_FLAGS as $needle) {
            foreach ($options as $oi => $opt) {
                if (stripos((string) $opt, $needle) !== false) {
                    $errors[] = "$loc option $oi contains banned phrase '$needle': $opt";
                }
            }
        }

        $stemWords = count(preg_split('/\s+/', trim((string) $stem)));
        $combinedWords = $stemWords + count(preg_split('/\s+/', trim((string) $caseText)));
        if ($combinedWords < 30) {
            $warnings[] = "$loc combined words ($combinedWords) below 30";
        }
        $context = false;
        foreach ($VIGNETTE as $k) {
            if (stripos((string) $stem, $k) !== false) {
                $context = true;

                break;
            }
        }
        if (! $context) {
            $warnings[] = "$loc stem has no clinical-context keyword";
        }
        foreach ($options as $oi => $opt) {
            $w = count(preg_split('/\s+/', trim((string) $opt)));
            if ($w < 2) {
                $warnings[] = "$loc option $oi too short ($w words): $opt";
            }
        }
        if (! in_array($difficulty, ['easy', 'intermediate', 'advanced'], true)) {
            $errors[] = "$loc bad difficulty: $difficulty";
        }

        $questions[] = [
            'category' => $category,
            'type' => 'case',
            'stem' => $stem,
            'case_text' => $caseText,
            'difficulty' => $difficulty,
            'correct' => $correct,
            'options' => $options,
            'explanation' => $explanation,
            'source_type' => 'original',
            'status' => 'active',
        ];
    }

    $target = $outDir.'/'.basename($sourceFile, '.php').'.json';
    file_put_contents($target, json_encode($questions, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);
    echo sprintf("%-28s %3d items -> %s\n", $category, count($questions), basename($target));
    $totalCreated += count($questions);
}

echo "TOTAL CREATED: $totalCreated\n";
if ($errors) {
    echo "ERRORS:\n".implode("\n", $errors)."\n";
    exit(1);
}
if ($warnings) {
    echo "WARNINGS:\n".implode("\n", array_slice($warnings, 0, 60))."\n";
    if (count($warnings) > 60) {
        echo "... ".count($warnings)." total warnings\n";
    }
}
exit(0);