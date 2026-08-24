<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('type')->default('challenge');
            $table->string('status')->default('draft');
            $table->integer('duration_minutes')->default(150);
            $table->integer('total_questions')->default(100);
            $table->datetime('opens_at')->nullable();
            $table->datetime('closes_at')->nullable();
            $table->integer('max_attempts')->default(1);
            $table->boolean('allow_practice')->default(false);
            $table->boolean('shuffle_questions')->default(true);
            $table->boolean('shuffle_options')->default(true);
            $table->boolean('fullscreen_required')->default(true);
            $table->decimal('pass_percentage', 5, 2)->nullable();
            $table->boolean('certificate_enabled')->default(true);
            $table->string('results_status')->default('locked');
            $table->string('answer_review_status')->default('locked');
            $table->string('leaderboard_status')->default('locked');
            $table->timestamps();

            $table->index('status');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
