<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reference_ranges', function (Blueprint $table) {
            $table->string('parameter_key')->nullable()->after('analyte_name')->index();
        });
    }

    public function down(): void
    {
        Schema::table('reference_ranges', function (Blueprint $table) {
            $table->dropColumn('parameter_key');
        });
    }
};
