<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_daily_metrics', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->string('resource_type');
            $table->string('status');
            $table->unsignedInteger('total_count')->default(0);
            $table->unsignedInteger('new_count')->default(0);
            $table->unsignedInteger('search_volume')->default(0);
            $table->unsignedInteger('search_zero_results')->default(0);
            $table->unsignedInteger('ai_requests_total')->default(0);
            $table->unsignedInteger('ai_requests_failed')->default(0);
            $table->unsignedInteger('ai_tokens_used')->default(0);
            $table->timestamps();

            $table->unique(['date', 'resource_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_daily_metrics');
    }
};
