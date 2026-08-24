<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_category_id')->constrained()->cascadeOnDelete();
            $table->string('question_type')->default('text');
            $table->text('stem');
            $table->text('case_text')->nullable();
            $table->string('image_path')->nullable();
            $table->string('image_alt')->nullable();
            $table->string('difficulty')->default('intermediate');
            $table->text('explanation')->nullable();
            $table->string('reference')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['question_category_id', 'difficulty', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
