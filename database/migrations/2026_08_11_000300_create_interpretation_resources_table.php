<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interpretation_resources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->longText('content');
            $table->text('clinical_context')->nullable();
            $table->json('related_analytes')->nullable();
            $table->text('mandatory_disclaimer');
            $table->string('author_name')->nullable();
            $table->string('featured_image')->nullable();
            $table->unsignedInteger('reading_time_minutes')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('published_at');
        });

        if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'])) {
            Schema::table('interpretation_resources', function (Blueprint $table) {
                $table->fullText(['name', 'short_description', 'clinical_context'], 'interpretation_resources_ft');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('interpretation_resources');
    }
};
