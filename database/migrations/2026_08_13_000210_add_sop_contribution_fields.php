<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sops', function (Blueprint $table) {
            $table->longText('purpose')->nullable()->after('content');
            $table->longText('scope')->nullable()->after('purpose');
            $table->longText('procedure_content')->nullable()->after('scope');
            $table->longText('quality_control')->nullable()->after('procedure_content');
            $table->longText('safety_information')->nullable()->after('quality_control');
            $table->longText('references')->nullable()->after('safety_information');
        });
    }

    public function down(): void
    {
        Schema::table('sops', function (Blueprint $table) {
            $table->dropColumn([
                'purpose',
                'scope',
                'procedure_content',
                'quality_control',
                'safety_information',
                'references',
            ]);
        });
    }
};
