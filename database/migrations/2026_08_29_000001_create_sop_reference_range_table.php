<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sop_reference_range', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reference_range_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['sop_id', 'reference_range_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop_reference_range');
    }
};