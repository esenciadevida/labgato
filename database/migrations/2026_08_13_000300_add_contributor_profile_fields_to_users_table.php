<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('profile_slug')->nullable()->unique()->after('name');
            $table->text('bio')->nullable()->after('profile_slug');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['profile_slug']);
            $table->dropColumn(['profile_slug', 'bio']);
        });
    }
};
