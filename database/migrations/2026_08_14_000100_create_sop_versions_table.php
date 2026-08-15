<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sop_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sop_id')->constrained('sops')->cascadeOnDelete();
            $table->string('version_number', 50);
            $table->string('document_code', 100)->nullable();
            $table->longText('purpose')->nullable();
            $table->longText('scope')->nullable();
            $table->longText('procedure_content')->nullable();
            $table->longText('quality_control')->nullable();
            $table->longText('safety_information')->nullable();
            $table->longText('references')->nullable();
            $table->longText('content')->nullable();
            $table->string('change_summary', 500)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->nullable();

            $table->index(['sop_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop_versions');
    }
};
