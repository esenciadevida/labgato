<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interpretation_resource_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interpretation_resource_id')->constrained()->cascadeOnDelete()->name('ir_category_resource_fk');
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['interpretation_resource_id', 'category_id'], 'ir_category_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interpretation_resource_category');
    }
};
