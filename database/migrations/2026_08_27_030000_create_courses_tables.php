<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Courses bundle assessments into a learning program. Completing all
     * required assessments (with a passing score) earns a course completion
     * certificate. Certificate settings mirror the per-assessment overrides
     * so courses can have their own template text, colors and signatory.
     */
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->string('status')->default('draft');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('certificate_enabled')->default(true);
            $table->boolean('certificate_requires_all')->default(true);
            $table->text('cert_body_text')->nullable();
            $table->text('cert_disclaimer_text')->nullable();
            $table->string('cert_primary_color', 20)->nullable();
            $table->string('cert_secondary_color', 20)->nullable();
            $table->string('cert_signatory_name', 150)->nullable();
            $table->string('cert_signatory_title', 150)->nullable();
            $table->boolean('cert_show_logo')->nullable();
            $table->boolean('cert_show_qr')->nullable();
            $table->timestamps();
        });

        Schema::create('course_assessment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->boolean('required')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['course_id', 'assessment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_assessment');
        Schema::dropIfExists('courses');
    }
};