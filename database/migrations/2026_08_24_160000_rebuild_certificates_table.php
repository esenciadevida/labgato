<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The BUILD 50-era certificates table predates the final certificate
     * design and has never held rows (the feature ships in this build),
     * so it is recreated with the tiered schema: verification codes,
     * tiers, rank snapshots, lazy PDF storage and revocation audit fields.
     * Pass-mark eligibility reuses assessments.pass_percentage.
     */
    public function up(): void
    {
        Schema::dropIfExists('certificates');

        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_attempt_id')->constrained()->cascadeOnDelete()->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->string('certificate_number')->unique();
            $table->string('verification_code')->unique()->index();
            $table->string('type');
            $table->decimal('score_percent', 5, 2);
            $table->integer('rank_at_issue')->nullable();
            $table->string('pdf_path')->nullable();
            $table->datetime('issued_at');
            $table->datetime('revoked_at')->nullable();
            $table->string('revoke_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
