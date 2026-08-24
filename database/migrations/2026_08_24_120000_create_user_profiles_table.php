<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('display_name')->nullable();
            $table->string('country')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('professional_status')->nullable();

            // Student fields
            $table->string('institution')->nullable();
            $table->string('programme')->nullable();
            $table->string('level', 150)->nullable();
            $table->integer('graduation_year')->nullable();

            // Professional fields
            $table->string('employer')->nullable();
            $table->string('job_title')->nullable();
            $table->integer('years_of_experience')->nullable();
            $table->string('specialization')->nullable();

            // Privacy controls
            $table->boolean('show_country')->default(true);
            $table->boolean('show_institution')->default(true);
            $table->boolean('show_on_leaderboard')->default(true);
            $table->boolean('profile_completed')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
