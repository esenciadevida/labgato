<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('group')->nullable()->after('type');
            $table->string('label')->nullable()->after('group');
            $table->text('description')->nullable()->after('label');
            $table->json('options')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['group', 'label', 'description', 'options']);
        });
    }
};
