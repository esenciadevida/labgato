<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('attempt_number')->default(1);
            $table->string('mode')->default('official');
            $table->string('status')->default('in_progress');
            $table->json('question_ids')->nullable();
            $table->json('option_orders')->nullable();
            $table->datetime('started_at')->nullable();
            $table->datetime('ends_at')->nullable();
            $table->datetime('submitted_at')->nullable();
            $table->integer('score')->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->integer('time_spent_seconds')->nullable();
            $table->string('integrity_status')->default('normal');
            $table->timestamps();

            $table->unique(['user_id', 'assessment_id', 'attempt_number']);
            $table->index(['status', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_attempts');
    }
};
