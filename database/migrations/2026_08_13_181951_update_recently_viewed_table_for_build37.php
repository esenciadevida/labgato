<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('recently_vieweds');

        Schema::create('recently_viewed', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('resource_type');
            $table->unsignedBigInteger('resource_id');
            $table->timestamp('viewed_at');
            $table->unique(['user_id', 'resource_type', 'resource_id']);
            $table->index(['user_id', 'viewed_at']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recently_viewed');
    }
};
