<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['sops', 'knowledge_resources', 'interpretation_resources'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->foreignId('contributed_by')->nullable()->after('updated_by')->constrained('users')->nullOnDelete();
                $table->text('rejection_reason')->nullable()->after('contributed_by');

                $table->index('contributed_by');
            });
        }
    }

    public function down(): void
    {
        foreach (['sops', 'knowledge_resources', 'interpretation_resources'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropIndex(['contributed_by']);
                $table->dropColumn(['contributed_by', 'rejection_reason']);
            });
        }
    }
};
