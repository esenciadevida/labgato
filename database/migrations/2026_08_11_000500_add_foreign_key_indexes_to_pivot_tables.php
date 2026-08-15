<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $pivotIndexes = [
            'calculator_category' => 'category_id',
            'calculator_tag' => 'tag_id',
            'reference_range_category' => 'category_id',
            'reference_range_tag' => 'tag_id',
            'interpretation_resource_category' => 'category_id',
            'interpretation_resource_tag' => 'tag_id',
            'knowledge_resource_category' => 'category_id',
            'knowledge_resource_tag' => 'tag_id',
        ];

        foreach ($pivotIndexes as $table => $column) {
            Schema::table($table, function (Blueprint $table) use ($column) {
                $table->index($column);
            });
        }

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        $pivotIndexes = [
            'calculator_category' => 'category_id',
            'calculator_tag' => 'tag_id',
            'reference_range_category' => 'category_id',
            'reference_range_tag' => 'tag_id',
            'interpretation_resource_category' => 'category_id',
            'interpretation_resource_tag' => 'tag_id',
            'knowledge_resource_category' => 'category_id',
            'knowledge_resource_tag' => 'tag_id',
        ];

        foreach ($pivotIndexes as $table => $column) {
            Schema::table($table, function (Blueprint $table) use ($column) {
                $table->dropIndex([$column]);
            });
        }

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });
    }
};
