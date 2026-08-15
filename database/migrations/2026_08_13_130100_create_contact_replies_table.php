<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_replies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contact_message_id')->index();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->text('reply');
            $table->timestamps();

            $table->foreign('contact_message_id')->references('id')->on('contact_messages')->cascadeOnDelete();
            $table->foreign('admin_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_replies');
    }
};
