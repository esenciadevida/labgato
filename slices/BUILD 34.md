labgato/
├── database/
│   └── seeders/
│       ├── ReferenceRangeDataSeeder.php
│       └── DatabaseSeeder.php
├── app/
│   └── Models/
│       └── ReferenceRange.php
└── tests/
    └── Feature/
        └── PublicSite/
            └── ReferenceRangeDataTest.php

IMPLEMENTATION PROMPT ID: LABGATO-BUILD34-REFERENCE-DATA

CONTEXT:
LabGato's Reference Ranges module needs real, accurate laboratory reference intervals. This build populates a comprehensive dataset of standard clinical reference values with demographic variations, units, SI conversions, and critical values.

OBJECTIVE:
Create a ReferenceRangeDataSeeder that populates ~45 real reference ranges across major lab panels, assign categories, and verify the public pages display them correctly.

EXISTING ARCHITECTURE:
- reference_ranges table with analyte_name, demographic fields, unit, si_unit, limits, critical values, conversion_factor, source_citation, last_verified_at.
- Categories and tags system.
- Public reference range pages with conversion toggle and critical highlighting.
- Status lifecycle (draft/published).

FILES TO INSPECT:
- app/Models/ReferenceRange.php
- database/seeders/ReferenceRangeSeeder.php
- database/seeders/DatabaseSeeder.php
- app/Models/Category.php
- database/migrations/ (reference_ranges table structure)

FILES TO CREATE:
- database/seeders/ReferenceRangeDataSeeder.php
- tests/Feature/PublicSite/ReferenceRangeDataTest.php

FILES TO MODIFY:
- database/seeders/DatabaseSeeder.php

TASK 1: ENSURE CATEGORIES EXIST

In the seeder, ensure these categories exist (create if missing):
- Hematology
- Clinical Chemistry
- Lipid Panel
- Thyroid Function
- Coagulation
- Metabolic

Use firstOrCreate by slug.

TASK 2: CREATE THE DATA SEEDER

Create database/seeders/ReferenceRangeDataSeeder.php.

Use firstOrCreate by slug to make it idempotent. Set status='published', published_at=now, last_verified_at='2026-08-01', effective_date='2026-08-01'.

Source citation for all: "Tietz Textbook of Clinical Chemistry and Molecular Diagnostics; standard clinical laboratory reference intervals."

Populate the following reference ranges:

=== HEMATOLOGY (Whole Blood) ===

WBC:
- name: "WBC (White Blood Cells) - Adult", slug: wbc-adult, analyte: WBC
- specimen: Whole Blood, population: Adult, sex: any
- unit: ×10³/µL, si_unit: ×10⁹/L, conv: 1.0
- range: 4.5 - 11.0, critical_low: 2.0, critical_high: 30.0

RBC Male:
- name: "RBC (Red Blood Cells) - Adult Male", slug: rbc-adult-male, analyte: RBC
- sex: male, unit: ×10⁶/µL, si_unit: ×10¹²/L, conv: 1.0
- range: 4.7 - 6.1

RBC Female:
- name: "RBC (Red Blood Cells) - Adult Female", slug: rbc-adult-female, analyte: RBC
- sex: female, range: 4.2 - 5.4

Hemoglobin Male:
- name: "Hemoglobin - Adult Male", slug: hemoglobin-adult-male, analyte: Hemoglobin
- sex: male, unit: g/dL, si_unit: g/L, conv: 10
- range: 13.8 - 17.2, critical_low: 7.0, critical_high: 20.0

Hemoglobin Female:
- slug: hemoglobin-adult-female, sex: female, range: 12.1 - 15.1

Hematocrit Male:
- name: "Hematocrit - Adult Male", slug: hematocrit-adult-male, analyte: Hematocrit
- sex: male, unit: %, range: 40.7 - 50.3, critical_low: 20, critical_high: 60

Hematocrit Female:
- slug: hematocrit-adult-female, sex: female, range: 36.1 - 44.3

Platelets:
- name: "Platelets - Adult", slug: platelets-adult, analyte: Platelets
- sex: any, unit: ×10³/µL, si_unit: ×10⁹/L, conv: 1.0
- range: 150 - 400, critical_low: 50, critical_high: 1000

MCV:
- name: "MCV (Mean Corpuscular Volume) - Adult", slug: mcv-adult, analyte: MCV
- unit: fL, range: 80 - 100

MCH:
- name: "MCH (Mean Corpuscular Hemoglobin) - Adult", slug: mch-adult, analyte: MCH
- unit: pg, range: 27 - 33

MCHC:
- name: "MCHC - Adult", slug: mchc-adult, analyte: MCHC
- unit: g/dL, range: 32 - 36

RDW:
- name: "RDW (Red Cell Distribution Width) - Adult", slug: rdw-adult, analyte: RDW
- unit: %, range: 11.5 - 14.5

=== CLINICAL CHEMISTRY (Serum) ===

Glucose Fasting:
- name: "Glucose (Fasting) - Adult", slug: glucose-fasting-adult, analyte: Glucose
- specimen: Serum, unit: mg/dL, si_unit: mmol/L, conv: 0.0555
- range: 70 - 99, critical_low: 40, critical_high: 500

BUN:
- name: "BUN (Blood Urea Nitrogen) - Adult", slug: bun-adult, analyte: BUN
- unit: mg/dL, si_unit: mmol/L, conv: 0.357
- range: 7 - 20

Creatinine Male:
- name: "Creatinine - Adult Male", slug: creatinine-adult-male, analyte: Creatinine
- sex: male, unit: mg/dL, si_unit: µmol/L, conv: 88.4
- range: 0.74 - 1.35

Creatinine Female:
- slug: creatinine-adult-female, sex: female, range: 0.59 - 1.04

Sodium:
- name: "Sodium - Adult", slug: sodium-adult, analyte: Sodium
- unit: mEq/L, si_unit: mmol/L, conv: 1.0
- range: 136 - 145, critical_low: 120, critical_high: 160

Potassium:
- name: "Potassium - Adult", slug: potassium-adult, analyte: Potassium
- unit: mEq/L, si_unit: mmol/L, conv: 1.0
- range: 3.5 - 5.0, critical_low: 2.5, critical_high: 6.5

Chloride:
- name: "Chloride - Adult", slug: chloride-adult, analyte: Chloride
- unit: mEq/L, si_unit: mmol/L, conv: 1.0
- range: 98 - 107

CO2 Bicarbonate:
- name: "CO2 (Bicarbonate) - Adult", slug: co2-bicarbonate-adult, analyte: CO2
- unit: mEq/L, si_unit: mmol/L, conv: 1.0
- range: 23 - 29

Calcium:
- name: "Calcium - Adult", slug: calcium-adult, analyte: Calcium
- unit: mg/dL, si_unit: mmol/L, conv: 0.2495
- range: 8.5 - 10.2, critical_low: 6.0, critical_high: 13.0

Total Protein:
- name: "Total Protein - Adult", slug: total-protein-adult, analyte: Total Protein
- unit: g/dL, si_unit: g/L, conv: 10
- range: 6.3 - 7.9

Albumin:
- name: "Albumin - Adult", slug: albumin-adult, analyte: Albumin
- unit: g/dL, si_unit: g/L, conv: 10
- range: 3.5 - 5.0

Total Bilirubin:
- name: "Bilirubin (Total) - Adult", slug: bilirubin-total-adult, analyte: Bilirubin
- unit: mg/dL, si_unit: µmol/L, conv: 17.1
- range: 0.1 - 1.2

ALP:
- name: "ALP (Alkaline Phosphatase) - Adult", slug: alp-adult, analyte: ALP
- unit: U/L, range: 44 - 147

AST:
- name: "AST (Aspartate Aminotransferase) - Adult", slug: ast-adult, analyte: AST
- unit: U/L, range: 10 - 40

ALT:
- name: "ALT (Alanine Aminotransferase) - Adult", slug: alt-adult, analyte: ALT
- unit: U/L, range: 7 - 56

=== LIPID PANEL (Serum) ===

Total Cholesterol:
- name: "Total Cholesterol - Adult", slug: total-cholesterol-adult, analyte: Total Cholesterol
- unit: mg/dL, si_unit: mmol/L, conv: 0.02586
- range: 0 - 200 (desirable; lower_limit: null, upper_limit: 200)

LDL:
- name: "LDL Cholesterol - Adult", slug: ldl-cholesterol-adult, analyte: LDL
- unit: mg/dL, si_unit: mmol/L, conv: 0.02586
- range: 0 - 100 (optimal; lower_limit: null, upper_limit: 100)

HDL Male:
- name: "HDL Cholesterol - Adult Male", slug: hdl-cholesterol-adult-male, analyte: HDL
- sex: male, unit: mg/dL, si_unit: mmol/L, conv: 0.02586
- range: 40 - null (lower_limit: 40, upper_limit: null)

HDL Female:
- slug: hdl-cholesterol-adult-female, sex: female, range: 50 - null

Triglycerides:
- name: "Triglycerides - Adult", slug: triglycerides-adult, analyte: Triglycerides
- unit: mg/dL, si_unit: mmol/L, conv: 0.01129
- range: 0 - 150 (lower_limit: null, upper_limit: 150)

=== THYROID FUNCTION (Serum) ===

TSH:
- name: "TSH (Thyroid Stimulating Hormone) - Adult", slug: tsh-adult, analyte: TSH
- unit: mIU/L, range: 0.4 - 4.0

Free T4:
- name: "Free T4 (Thyroxine) - Adult", slug: free-t4-adult, analyte: Free T4
- unit: ng/dL, si_unit: pmol/L, conv: 12.87
- range: 0.8 - 1.8

Free T3:
- name: "Free T3 (Triiodothyronine) - Adult", slug: free-t3-adult, analyte: Free T3
- unit: pg/mL, si_unit: pmol/L, conv: 1.536
- range: 2.3 - 4.2

=== COAGULATION (Plasma) ===

PT:
- name: "PT (Prothrombin Time) - Adult", slug: pt-adult, analyte: PT
- specimen: Plasma, unit: seconds, range: 11.0 - 13.5

INR:
- name: "INR - Adult", slug: inr-adult, analyte: INR
- specimen: Plasma, unit: ratio, range: 0.8 - 1.1

aPTT:
- name: "aPTT (Activated Partial Thromboplastin Time) - Adult", slug: aptt-adult, analyte: aPTT
- specimen: Plasma, unit: seconds, range: 25 - 35

=== METABOLIC / OTHER (Serum) ===

Uric Acid Male:
- name: "Uric Acid - Adult Male", slug: uric-acid-adult-male, analyte: Uric Acid
- sex: male, unit: mg/dL, si_unit: µmol/L, conv: 59.48
- range: 3.4 - 7.0

Uric Acid Female:
- slug: uric-acid-adult-female, sex: female, range: 2.4 - 6.0

HbA1c:
- name: "HbA1c (Glycated Hemoglobin) - Adult", slug: hba1c-adult, analyte: HbA1c
- specimen: Whole Blood, unit: %, si_unit: mmol/mol, conv: 10.93
- range: 4.0 - 5.6

TASK 3: CATEGORY ASSIGNMENT

After creating each reference range, attach the appropriate category:
- WBC, RBC, Hemoglobin, Hematocrit, Platelets, MCV, MCH, MCHC, RDW → Hematology
- Glucose, BUN, Creatinine, Sodium, Potassium, Chloride, CO2, Calcium, Protein, Albumin, Bilirubin, ALP, AST, ALT → Clinical Chemistry
- Cholesterol, LDL, HDL, Triglycerides → Lipid Panel
- TSH, Free T4, Free T3 → Thyroid Function
- PT, INR, aPTT → Coagulation
- Uric Acid, HbA1c → Metabolic

TASK 4: REGISTER IN DATABASE SEEDER

Modify database/seeders/DatabaseSeeder.php:
- Call ReferenceRangeDataSeeder after categories and reference range structure are set up.
- Ensure it runs after the CategorySeeder (so categories exist).

TASK 5: VERIFICATION TEST

Create tests/Feature/PublicSite/ReferenceRangeDataTest.php:
- Test that the reference ranges listing page displays real analytes (e.g., Hemoglobin, Glucose).
- Test that a reference range detail page shows the correct range values.
- Test that demographic variations are grouped (e.g., Hemoglobin Male and Female).
- Test that critical values are present for applicable analytes.
- Test that the conversion toggle appears when si_unit and conversion_factor are set.
- Test that the count of published reference ranges is at least 40.

TASK 6: RUN AND VERIFY

Run:
php artisan db:seed --class=ReferenceRangeDataSeeder

Then verify:
- Browse /reference-ranges and confirm real analytes appear.
- Open a detail page (e.g., Hemoglobin) and confirm Male/Female variations display.
- Test the SI unit conversion toggle.
- Confirm critical values are highlighted.

DATA ACCURACY NOTES:
- These are standard, widely-cited clinical reference intervals for educational purposes.
- The existing disclaimer handles the "varies by laboratory" caveat.
- Conversion factors are standard (e.g., glucose mg/dL × 0.0555 = mmol/L).
- Critical values are common laboratory panic values.
- Do NOT fabricate unusual or non-standard values.

DEFINITION OF DONE:
- ReferenceRangeDataSeeder populates ~45 real reference ranges.
- Demographic variations exist (male/female for applicable analytes).
- Units, SI units, and conversion factors are set.
- Critical values are set where applicable.
- Categories are assigned.
- Seeder is idempotent (firstOrCreate by slug).
- Public pages display the data correctly.
- Conversion toggle works.
- Verification tests pass.