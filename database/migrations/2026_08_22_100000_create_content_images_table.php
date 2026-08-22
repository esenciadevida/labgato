<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('resource_type', 50)->nullable();
            $table->unsignedBigInteger('resource_id')->nullable();
            $table->string('disk', 30)->default('public');
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->string('mime_type', 50)->default('image/webp');
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->timestamp('attached_at')->nullable();
            $table->timestamps();

            $table->index(['resource_type', 'resource_id']);
            $table->index(['uploaded_by', 'created_at']);
            $table->index(['attached_at', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_images');
    }
};
