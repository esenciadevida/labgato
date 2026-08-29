<?php

namespace Database\Seeders;

use App\Models\Sop;
use App\Services\MasterContentImporter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class MasterSopSeeder extends Seeder
{
    private const SEED_FILES = [
        'labgoto_hematology_sops.json',
        'labgoto_manual_cbc_sop.json',
        'labgoto_manual_reticulocyte_count_sop.json',
        'labgoto_peripheral_blood_film_sop.json',
        'labgoto_d_dimer_sop.json',
        'labgoto_thick_blood_film_sop.json',
        'labgoto_thin_blood_film_sop.json',
        'labgoto_esr_sop.json',
        'labgoto_pcv_sop.json',
        'labgoto_sickling_test_sop.json',
        'labgoto_pt_inr_sop.json',
        'labgoto_aptt_sop.json',
        'labgoto_urinalysis_chemical_sop.json',
        'labgoto_urine_microscopy_sop.json',
        'labgoto_urine_culture_sop.json',
        'labgoto_gram_staining_sop.json',
        'labgoto_ziehl_neelsen_sop.json',
        'labgoto_culture_sensitivity_sop.json',
        'labgoto_kirby_bauer_sop.json',
        'labgoto_mcfarland_sop.json',
        'labgoto_hanging_drop_sop.json',
        'labgoto_catalase_sop.json',
        'labgoto_coagulase_test_sop.json',
        'labgoto_oxidase_test_sop.json',
        'labgoto_abo_grouping_sop.json',
        'labgoto_rh_typing_sop.json',
        'labgoto_dat_sop.json',
        'labgoto_iat_sop.json',
        'labgoto_crossmatching_sop.json',
        'labgoto_antibody_screening_sop.json',
        'labgoto_blood_storage_sop.json',
        'labgoto_transfusion_reaction_sop.json',
        'labgoto_glucose_sop.json',
        'labgoto_urea_sop.json',
        'labgoto_creatinine_sop.json',
        'labgoto_electrolytes_sop.json',
        'labgoto_lft_sop.json',
        'labgoto_lipid_profile_sop.json',
        'labgoto_hba1c_sop.json',
        'labgoto_thyroid_panel_sop.json',
        'labgoto_uric_acid_sop.json',
    ];

    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $importer = app(MasterContentImporter::class);

        Sop::query()->delete();

        $total = 0;

        foreach (self::SEED_FILES as $file) {
            $path = base_path('dump/'.$file);

            if (! File::exists($path)) {
                $this->command->warn("SOP seed file not found: {$path}");

                continue;
            }

            $records = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR) ?? [];
            $importer->importSops($records);
            $total += count($records);

            $this->command->info(sprintf('Seeded %d SOP records from %s.', count($records), $file));
        }

        $this->command->info("SOP seeding complete. Total records: {$total}.");
    }
}