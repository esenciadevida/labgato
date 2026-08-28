<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Optional per-assessment certificate template overrides. Every column is
     * nullable so the assessment can inherit the global certificate template
     * settings (certificate.* keys) when left unset.
     */
    public function up(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->text('cert_body_text')->nullable()->after('certificate_enabled');
            $table->text('cert_disclaimer_text')->nullable()->after('cert_body_text');
            $table->string('cert_primary_color', 20)->nullable()->after('cert_disclaimer_text');
            $table->string('cert_secondary_color', 20)->nullable()->after('cert_primary_color');
            $table->string('cert_signatory_name', 150)->nullable()->after('cert_secondary_color');
            $table->string('cert_signatory_title', 150)->nullable()->after('cert_signatory_name');
            $table->boolean('cert_show_logo')->nullable()->after('cert_signatory_title');
            $table->boolean('cert_show_qr')->nullable()->after('cert_show_logo');
        });
    }

    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropColumn([
                'cert_body_text',
                'cert_disclaimer_text',
                'cert_primary_color',
                'cert_secondary_color',
                'cert_signatory_name',
                'cert_signatory_title',
                'cert_show_logo',
                'cert_show_qr',
            ]);
        });
    }
};