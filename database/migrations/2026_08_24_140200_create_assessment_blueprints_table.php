<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_blueprints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_category_id')->constrained()->cascadeOnDelete();
            $table->integer('question_count');
            $table->integer('easy_percent')->default(30);
            $table->integer('intermediate_percent')->default(50);
            $table->integer('advanced_percent')->default(20);
            $table->timestamps();

            $table->unique(['assessment_id', 'question_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_blueprints');
    }
};
