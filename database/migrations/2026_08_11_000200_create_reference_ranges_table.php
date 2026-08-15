<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reference_ranges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->string('analyte_name');
            $table->string('specimen_type')->nullable();
            $table->string('methodology')->nullable();
            $table->string('population_group')->nullable();
            $table->decimal('age_min', 8, 4)->nullable();
            $table->decimal('age_max', 8, 4)->nullable();
            $table->string('sex')->default('any');
            $table->string('unit');
            $table->decimal('lower_limit', 12, 4)->nullable();
            $table->decimal('upper_limit', 12, 4)->nullable();
            $table->decimal('critical_low', 12, 4)->nullable();
            $table->decimal('critical_high', 12, 4)->nullable();
            $table->decimal('conversion_factor', 12, 6)->nullable();
            $table->text('source_citation')->nullable();
            $table->date('effective_date')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamps();

            $table->index('analyte_name');
            $table->index('status');
            $table->index('published_at');
        });

        if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'])) {
            Schema::table('reference_ranges', function (Blueprint $table) {
                $table->fullText(['name', 'analyte_name', 'short_description'], 'reference_ranges_ft');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reference_ranges');
    }
};
