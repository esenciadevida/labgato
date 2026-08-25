<?php

namespace App\Console\Commands;

use App\Models\Question;
use Illuminate\Console\Command;

class LinkQuestionImages extends Command
{
    protected $signature = 'questions:link-images';

    protected $description = 'Attach reliable Wikimedia Commons image source URLs to seeded image questions by matching stem fragments.';

    private const MAP = [
        "bull's-eye" => ['https://upload.wikimedia.org/wikipedia/commons/thumb/c/c4/Target_Cells%2C_Peripheral_Blood_Smear_%2839144139915%29.jpg/1280px-Target_Cells%2C_Peripheral_Blood_Smear_%2839144139915%29.jpg', 'Wikimedia Commons', 'CC BY 2.0'],
        'slit- or mouth-shaped' => ['https://upload.wikimedia.org/wikipedia/commons/8/83/Poikilocytes_-_Red_blood_cell_types.jpg', 'Wikimedia Commons', 'CC BY 4.0'],
        'crescent-shaped' => ['https://upload.wikimedia.org/wikipedia/commons/thumb/a/a6/Sickle_cell_anemia_smear.jpg/1280px-Sickle_cell_anemia_smear.jpg', 'Wikimedia Commons', 'CC BY-SA 4.0'],
        'fragmented, helmet' => ['https://upload.wikimedia.org/wikipedia/commons/8/83/Poikilocytes_-_Red_blood_cell_types.jpg', 'Wikimedia Commons', 'CC BY 4.0'],
        'lacking central pallor' => ['https://upload.wikimedia.org/wikipedia/commons/d/d9/Investigation_of_hereditary_spherocytosis_red_blood_cell_shapes.jpg', 'Wikimedia Commons', 'CC BY-SA 4.0'],
        '6 nuclear lobes' => ['https://upload.wikimedia.org/wikipedia/commons/5/51/Hypersegmented_neutrophil.png', 'Wikimedia Commons', 'Public Domain'],
        'casts containing white' => ['https://upload.wikimedia.org/wikipedia/commons/thumb/f/f7/WBC_cast_in_urine.jpg/1280px-WBC_cast_in_urine.jpg', 'Wikimedia Commons', 'CC BY-SA 4.0'],
        'cocci arranged in clusters' => ['https://upload.wikimedia.org/wikipedia/commons/thumb/1/16/Staphylococcus_aureus_Gram_stain.jpg/1280px-Staphylococcus_aureus_Gram_stain.jpg', 'Wikimedia Commons', 'CC BY-SA 4.0'],
        'lancet-shaped' => ['https://upload.wikimedia.org/wikipedia/commons/thumb/d/d4/Gram_stain_of_Streptococcus_pneumoniae.jpg/1280px-Gram_stain_of_Streptococcus_pneumoniae.jpg', 'Wikimedia Commons', 'CC0'],
        'delicate ring forms' => ['https://upload.wikimedia.org/wikipedia/commons/c/c6/Plasmodium_falciparum_%28malaria%29_parasite_in_blood.jpg', 'Wikimedia Commons', 'Public Domain'],
        'pear-shaped flagellate' => ['https://upload.wikimedia.org/wikipedia/commons/2/21/Giardia_intestinalis_-_trophozoite.jpg', 'Wikimedia Commons', 'CC BY-SA 4.0'],
        'pseudohyphae and blastoconidia' => ['https://upload.wikimedia.org/wikipedia/commons/thumb/d/db/Candida_albicans_-_Gram_stain.jpg/1280px-Candida_albicans_-_Gram_stain.jpg', 'Wikimedia Commons', 'CC BY-SA 4.0'],
        'acid-fast bacilli' => ['https://upload.wikimedia.org/wikipedia/commons/thumb/e/e9/ZN_of_MTB.jpg/1280px-ZN_of_MTB.jpg', 'Wikimedia Commons', 'CC BY-SA 4.0'],
        'clear halo' => ['https://upload.wikimedia.org/wikipedia/commons/thumb/6/6f/Cryptococcus_neoformans_using_a_light_India_ink_staining_preparation_PHIL_3771_lores.jpg/1280px-Cryptococcus_neoformans_using_a_light_India_ink_staining_preparation_PHIL_3771_lores.jpg', 'Wikimedia Commons', 'Public Domain'],
        'Gram-negative diplococci within neutrophils' => ['https://upload.wikimedia.org/wikipedia/commons/thumb/9/97/Neisseria_gonorrhoeae_bacteria_%28Gram_stain%29.jpg/1280px-Neisseria_gonorrhoeae_bacteria_%28Gram_stain%29.jpg', 'Wikimedia Commons', 'CC BY 4.0'],
        'envelope-shaped (octahedral) crystals' => ['https://upload.wikimedia.org/wikipedia/commons/1/1f/Calcium_oxalate_crystals_in_urine.jpg', 'Wikimedia Commons', 'Public Domain'],
        'rod-shaped, azurophilic inclusion' => ['https://upload.wikimedia.org/wikipedia/commons/0/0e/Myeloblast_with_Auer_Rod_smear_2009-11-23_cropped.jpg', 'Wikimedia Commons', 'CC BY-SA 3.0'],
        'bilobed cell with large orange-red granules' => ['https://upload.wikimedia.org/wikipedia/commons/thumb/0/0f/Eosinophil_blood_smear.JPG/1280px-Eosinophil_blood_smear.JPG', 'Wikimedia Commons', 'CC BY-SA 4.0'],
        'terminal round spores' => ['https://upload.wikimedia.org/wikipedia/commons/f/fe/C_tetani_gram_stain.jpg', 'Wikimedia Commons', 'Public Domain'],
        'coffin-lid appearance' => ['https://upload.wikimedia.org/wikipedia/commons/a/a4/Struvite_crystals_%28urine%29_-_Str%C3%BCvit_kristalleri_%28idrar%29_-_01.png', 'Wikimedia Commons', 'CC BY-SA 3.0'],
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
