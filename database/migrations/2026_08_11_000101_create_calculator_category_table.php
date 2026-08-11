<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calculator_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calculator_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['calculator_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calculator_category');
    }
};
