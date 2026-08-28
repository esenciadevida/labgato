<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasIndex('exam_attempts', ['assessment_id', 'status'])) {
            Schema::table('exam_attempts', function (Blueprint $table) {
                $table->index(['assessment_id', 'status']);
            });
        }

        if (! Schema::hasIndex('exam_attempts', ['user_id', 'status'])) {
            Schema::table('exam_attempts', function (Blueprint $table) {
                $table->index(['user_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('exam_attempts', ['assessment_id', 'status'])) {
            Schema::table('exam_attempts', function (Blueprint $table) {
                $table->dropIndex(['assessment_id', 'status']);
            });
        }

        if (Schema::hasIndex('exam_attempts', ['user_id', 'status'])) {
            Schema::table('exam_attempts', function (Blueprint $table) {
                $table->dropIndex(['user_id', 'status']);
            });
        }
    }
};