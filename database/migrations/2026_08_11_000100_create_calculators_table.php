<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calculators', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->string('formula_display')->nullable();
            $table->json('input_schema');
            $table->json('output_schema');
            $table->string('engine')->default('php_class');
            $table->string('engine_reference');
            $table->text('interpretation_notes')->nullable();
            $table->text('references')->nullable();
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
            Schema::table('calculators', function (Blueprint $table) {
                $table->fullText(['name', 'short_description']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('calculators');
    }
};
