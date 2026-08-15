<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('search_queries', function (Blueprint $table) {
            $table->index('results_count');
        });
    }

    public function down(): void
    {
        Schema::table('search_queries', function (Blueprint $table) {
            $table->dropIndex(['results_count']);
        });
    }
};
