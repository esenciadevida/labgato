<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sops', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->longText('content');
            $table->string('department')->nullable();
            $table->string('version')->nullable();
            $table->string('author_name')->nullable();
            $table->date('reviewed_date')->nullable();
            $table->text('mandatory_disclaimer')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('published_at');
            $table->index('department');
        });

        if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'])) {
            Schema::table('sops', function (Blueprint $table) {
                $table->fullText(['name', 'short_description', 'content'], 'sops_ft');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sops');
    }
};
