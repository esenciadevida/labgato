<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('launch_settings', function (Blueprint $table) {
            $table->id();
            $table->datetime('launch_at')->nullable();
            $table->string('timezone')->default('Africa/Lagos');
            $table->boolean('auto_activate')->default(true);
            $table->boolean('registration_open')->default(true);
            $table->boolean('challenge_open')->default(true);
            $table->string('results_status')->default('locked');
            $table->string('answer_review_status')->default('locked');
            $table->string('leaderboard_status')->default('locked');
            $table->string('main_site_status')->default('locked');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('launch_settings');
    }
};
