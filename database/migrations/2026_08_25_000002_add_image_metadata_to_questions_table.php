<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->string('image_source')->nullable()->after('image_alt');
            $table->string('image_license')->nullable()->after('image_source');
            $table->text('image_attribution')->nullable()->after('image_license');
            $table->string('image_source_url')->nullable()->after('image_attribution');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['image_source', 'image_license', 'image_attribution', 'image_source_url']);
        });
    }
};
