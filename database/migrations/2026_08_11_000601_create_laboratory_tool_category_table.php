<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laboratory_tool_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laboratory_tool_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['laboratory_tool_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laboratory_tool_category');
    }
};
