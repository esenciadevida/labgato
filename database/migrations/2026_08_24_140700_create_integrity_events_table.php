<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integrity_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('event_type');
            $table->integer('question_number')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['exam_attempt_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integrity_events');
    }
};
