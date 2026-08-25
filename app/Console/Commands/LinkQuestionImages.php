<?php

namespace App\Console\Commands;

use App\Models\Question;
use Illuminate\Console\Command;

class LinkQuestionImages extends Command
{
    protected $signature = 'questions:link-images';

    protected $description = 'Attach remote Wikimedia source URLs to seeded image questions by matching stem fragments.';

    private const MAP = [
        "bull's-eye" => ['https://commons.wikimedia.org/wiki/Special:FilePath/Target%20Cells,%20Peripheral%20Blood%20Smear%20(39144139915).jpg?width=1200', 'Wikimedia Commons', 'CC'],
        'slit- or mouth-shaped' => ['https://commons.wikimedia.org/wiki/Special:FilePath/Poikilocytes%20-%20Red%20blood%20cell%20types.jpg?width=1200', 'Wikimedia Commons', 'CC BY 4.0'],
        'crescent-shaped' => ['https://commons.wikimedia.org/wiki/Special:FilePath/Sickle%20cell%20anemia%20smear.jpg?width=1200', 'Wikimedia Commons', 'CC BY-SA 4.0'],
        'fragmented, helmet' => ['https://commons.wikimedia.org/wiki/Special:FilePath/Bloodsmear%20with%20typical%20schistocytes%20in%20TTP%20marked%20in%20blue%201.tif?width=1200', 'Wikimedia Commons', 'CC'],
        'lacking central pallor' => ['https://commons.wikimedia.org/wiki/Special:FilePath/Investigation%20of%20hereditary%20spherocytosis%20red%20blood%20cell%20shapes.jpg?width=1200', 'Wikimedia Commons', 'CC'],
        '6 nuclear lobes' => ['https://commons.wikimedia.org/wiki/Special:FilePath/HypersegmentedNeutrophils.jpg?width=1200', 'Wikimedia Commons', 'CC BY-SA 4.0'],
        'casts containing white' => ['https://commons.wikimedia.org/wiki/Special:FilePath/WBC%20cast%20in%20urine.jpg?width=1200', 'Wikimedia Commons', 'CC BY-SA 4.0'],
        'cocci arranged in clusters' => ['https://commons.wikimedia.org/wiki/Special:FilePath/Staphylococcus%20aureus%20Gram%20stain.jpg?width=1200', 'Wikimedia Commons', 'CC BY-SA 4.0'],
        'lancet-shaped' => ['https://commons.wikimedia.org/wiki/Special:FilePath/Gram%20stain%20of%20Streptococcus%20pneumoniae.jpg?width=1200', 'Wikimedia Commons', 'CC'],
        'delicate ring forms' => ['https://commons.wikimedia.org/wiki/Special:FilePath/Plasmodium%20falciparum%20(malaria)%20parasite%20in%20blood.jpg?width=1200', 'Wikimedia Commons', 'CC'],
        'pear-shaped flagellate' => ['https://commons.wikimedia.org/wiki/Special:FilePath/Giardia%20intestinalis%20-%20trophozoite.jpg?width=1200', 'Wikimedia Commons', 'CC'],
        'pseudohyphae and blastoconidia' => ['https://commons.wikimedia.org/wiki/Special:FilePath/Candida%20albicans%20-%20Gram%20stain.jpg?width=1200', 'Wikimedia Commons', 'CC BY-SA 4.0'],
        'acid-fast bacilli' => ['https://commons.wikimedia.org/wiki/Special:FilePath/ZN%20of%20MTB.jpg?width=1200', 'Wikimedia Commons', 'CC BY-SA 4.0'],
        'clear halo' => ['https://commons.wikimedia.org/wiki/Special:FilePath/Cryptococcus%20neoformans%20using%20a%20light%20India%20ink%20staining%20preparation%20PHIL%203771%20lores.jpg?width=1200', 'CDC PHIL via Wikimedia Commons', 'Public Domain'],
    ];

    public function handle(): int
    {
        $linked = 0;

        foreach (self::MAP as $fragment => [$url, $source, $license]) {
            $linked += Question::where('stem', 'like', "%{$fragment}%")
                ->update([
                    'image_source_url' => $url,
                    'image_source' => $source,
                    'image_license' => $license,
                ]);
        }

        $this->info("Linked {$linked} question(s) to required images.");

        return self::SUCCESS;
    }
}
