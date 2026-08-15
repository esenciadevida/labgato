<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interpretation_resources', function (Blueprint $table) {
            $table->text('mandatory_disclaimer')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('interpretation_resources', function (Blueprint $table) {
            $table->text('mandatory_disclaimer')->nullable(false)->change();
        });
    }
};
