<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLES = [
        'knowledge_resources',
        'sops',
        'interpretation_resources',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('focus_keyword')->nullable()->after('seo_description');
                $table->string('reviewer_name')->nullable()->after('focus_keyword');
                $table->string('reviewer_credentials')->nullable()->after('reviewer_name');
                $table->date('last_reviewed_at')->nullable()->after('reviewer_credentials');
                $table->json('faq')->nullable()->after('last_reviewed_at');
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn(['focus_keyword', 'reviewer_name', 'reviewer_credentials', 'last_reviewed_at', 'faq']);
            });
        }
    }
};
