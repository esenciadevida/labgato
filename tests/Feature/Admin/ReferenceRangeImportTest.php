<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\ReferenceRange;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ReferenceRangeImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $viewerAnalyst;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolePermissionSeeder::class, SettingsSeeder::class]);

        $this->superAdmin = User::factory()->create(['is_active' => true]);
        $this->superAdmin->roles()->attach(
            Role::where('slug', 'super-administrator')->firstOrFail()
        );

        $this->viewerAnalyst = User::factory()->create(['is_active' => true]);
        $this->viewerAnalyst->roles()->attach(
            Role::where('slug', 'viewer-analyst')->firstOrFail()
        );
    }

    private function csvUpload(string $filename, string $mime = 'text/csv'): UploadedFile
    {
        return new UploadedFile(
            database_path('csv/'.$filename),
            $filename,
            $mime,
            null,
            true
        );
    }

    private function uploadFromString(string $content, string $filename = 'data.csv'): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($path, $content);

        return new UploadedFile($path, $filename, 'text/csv', null, true);
    }

    public function test_import_form_is_accessible_with_permission(): void
    {
        $this->actingAs($this->superAdmin)
            ->get('/admin/reference-ranges/import')
            ->assertStatus(200);
    }

    public function test_import_form_is_blocked_without_permission(): void
    {
        $this->actingAs($this->viewerAnalyst)
            ->get('/admin/reference-ranges/import')
            ->assertStatus(403);
    }

    public function test_template_download_returns_csv(): void
    {
        $this->actingAs($this->superAdmin)
            ->get('/admin/reference-ranges/import/template')
            ->assertHeaderContains('Content-Type', 'text/csv')
            ->assertDownload('reference_ranges_template.csv');
    }

    public function test_uploading_valid_csv_shows_preview_with_correct_counts(): void
    {
        $this->actingAs($this->superAdmin)
            ->post('/admin/reference-ranges/import/preview', [
                'file' => $this->csvUpload('reference_ranges_sample.csv'),
            ])
            ->assertSee('2 valid')
            ->assertSee('1 error');
    }

    public function test_invalid_rows_are_flagged_with_error_messages(): void
    {
        $this->actingAs($this->superAdmin)
            ->post('/admin/reference-ranges/import/preview', [
                'file' => $this->csvUpload('reference_ranges_invalid.csv'),
            ])
            ->assertSee('Rows with errors')
            ->assertSee('Invalid sex value');
    }

    public function test_confirming_import_creates_reference_ranges(): void
    {
        $this->actingAs($this->superAdmin)
            ->post('/admin/reference-ranges/import/preview', [
                'file' => $this->csvUpload('reference_ranges_sample.csv'),
            ])
            ->assertSessionHas('import.valid_rows');

        $this->actingAs($this->superAdmin)
            ->post('/admin/reference-ranges/import', [
                'confirm' => 1,
            ]);

        $this->assertDatabaseHas('reference_ranges', [
            'name' => 'Hemoglobin - Adult Male',
        ]);
    }

    public function test_imported_ranges_default_to_draft_status(): void
    {
        $this->actingAs($this->superAdmin)
            ->post('/admin/reference-ranges/import/preview', [
                'file' => $this->csvUpload('reference_ranges_sample.csv'),
            ])
            ->assertSessionHas('import.status', 'draft');

        $this->actingAs($this->superAdmin)
            ->post('/admin/reference-ranges/import', [
                'confirm' => 1,
            ]);

        $this->assertDatabaseHas('reference_ranges', [
            'status' => 'draft',
        ]);
    }

    public function test_skip_duplicates_does_not_create_duplicates(): void
    {
        ReferenceRange::factory()->create(['name' => 'Test Analyte', 'analyte_name' => 'Test']);

        $this->actingAs($this->superAdmin)
            ->post('/admin/reference-ranges/import/preview', [
                'file' => $this->csvUpload('reference_ranges_duplicate.csv'),
            ]);

        $this->actingAs($this->superAdmin)
            ->post('/admin/reference-ranges/import', [
                'confirm' => 1,
                'duplicate_handling' => 'skip',
            ]);

        $this->assertDatabaseHas('reference_ranges', [
            'name' => 'Test Analyte',
        ]);

        $this->assertDatabaseCount('reference_ranges', 1);
    }

    public function test_update_existing_updates_records(): void
    {
        $existing = ReferenceRange::factory()->create([
            'name' => 'Old Name',
            'analyte_name' => 'Old Analyte',
            'unit' => 'mg/dL',
            'lower_limit' => 10,
            'upper_limit' => 20,
        ]);

        $this->actingAs($this->superAdmin)
            ->post('/admin/reference-ranges/import/preview', [
                'file' => $this->csvUpload('reference_ranges_update.csv'),
                'duplicate_handling' => 'update',
            ]);

        $this->actingAs($this->superAdmin)
            ->post('/admin/reference-ranges/import', [
                'confirm' => 1,
            ]);

        $this->assertDatabaseHas('reference_ranges', [
            'id' => $existing->id,
            'name' => 'New Name',
            'unit' => 'g/L',
            'lower_limit' => 13.8,
            'upper_limit' => 17.2,
        ]);
    }

    public function test_non_csv_files_are_rejected(): void
    {
        $this->actingAs($this->superAdmin)
            ->post('/admin/reference-ranges/import/preview', [
                'file' => UploadedFile::fake()->create('report.xlsx', 100),
            ])
            ->assertSessionHasErrors('file');
    }

    public function test_missing_required_columns_returns_a_clear_error(): void
    {
        $this->actingAs($this->superAdmin)
            ->post('/admin/reference-ranges/import/preview', [
                'file' => $this->uploadFromString("analyte_name,unit\n\"Sodium\",\"mmol/L\"\n"),
            ])
            ->assertRedirect('/admin/reference-ranges/import')
            ->assertSessionHas('error');
    }

    public function test_empty_csv_returns_a_clear_error(): void
    {
        $this->actingAs($this->superAdmin)
            ->post('/admin/reference-ranges/import/preview', [
                'file' => $this->uploadFromString(''),
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('file');
    }

    public function test_utf8_bom_in_header_is_tolerated(): void
    {
        $this->actingAs($this->superAdmin)
            ->post('/admin/reference-ranges/import/preview', [
                'file' => $this->uploadFromString(
                    "\xEF\xBB\xBFname,analyte_name,unit\n\"Sodium\",\"Sodium\",\"mmol/L\"\n"
                ),
            ])
            ->assertSee('1 valid')
            ->assertSee('Sodium');
    }

    public function test_confirming_without_an_upload_session_redirects_back(): void
    {
        $this->actingAs($this->superAdmin)
            ->post('/admin/reference-ranges/import', [
                'confirm' => 1,
            ])
            ->assertRedirect('/admin/reference-ranges/import')
            ->assertSessionHas('error');
    }

    public function test_import_is_audit_logged(): void
    {
        $this->actingAs($this->superAdmin)
            ->post('/admin/reference-ranges/import/preview', [
                'file' => $this->csvUpload('reference_ranges_sample.csv'),
            ])
            ->assertSessionHas('import.valid_rows');

        $this->actingAs($this->superAdmin)
            ->post('/admin/reference-ranges/import', [
                'confirm' => 1,
            ]);

        $this->assertTrue(
            AuditLog::where('action', 'import reference ranges')
                ->where('changes->imported', '>', 0)
                ->exists()
        );
    }
}
