<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reference_ranges', function (Blueprint $table) {
            $table->date('last_verified_at')->nullable()->after('source_citation');
        });

        Schema::table('sops', function (Blueprint $table) {
            $table->date('last_verified_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('reference_ranges', function (Blueprint $table) {
            $table->dropColumn('last_verified_at');
        });

        Schema::table('sops', function (Blueprint $table) {
            $table->dropColumn('last_verified_at');
        });
    }
};
