<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_resource_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('knowledge_resource_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['knowledge_resource_id', 'category_id'], 'kr_category_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_resource_category');
    }
};
