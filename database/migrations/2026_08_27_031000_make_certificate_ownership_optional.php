<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Certificates were previously tied 1:1 to an exam attempt. Course
     * completion certificates have no attempt (and no single assessment),
     * so both ownership columns become nullable and a `course_id` is added.
     *
     * The table is rebuilt by hand instead of ->change() so the migration
     * behaves identically on MySQL and the SQLite :memory: test database.
     * All constraints/indexes are dropped on the live table BEFORE the
     * rename, because both engines keep those names (as originally defined)
     * after a rename and would collide with the freshly created table.
     */
    public function up(): void
    {
        $columns = [
            'id', 'exam_attempt_id', 'user_id', 'assessment_id',
            'certificate_number', 'verification_code', 'type',
            'score_percent', 'rank_at_issue', 'pdf_path', 'issued_at',
            'revoked_at', 'revoke_reason', 'created_at', 'updated_at',
        ];

        Schema::table('certificates', function (Blueprint $table) {
            $table->dropForeign(['exam_attempt_id']);
            $table->dropForeign(['user_id']);
            $table->dropForeign(['assessment_id']);
        });

        foreach (['certificates_exam_attempt_id_unique', 'certificates_certificate_number_unique', 'certificates_verification_code_unique'] as $index) {
            if (Schema::hasIndex('certificates', $index)) {
                Schema::table('certificates', fn (Blueprint $table) => $table->dropUnique($index));
            }
        }

        Schema::rename('certificates', 'certificates_prev');

        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_attempt_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('certificate_number')->unique();
            $table->string('verification_code')->unique()->index();
            $table->string('type');
            $table->decimal('score_percent', 5, 2);
            $table->integer('rank_at_issue')->nullable();
            $table->string('pdf_path')->nullable();
            $table->datetime('issued_at');
            $table->datetime('revoked_at')->nullable();
            $table->string('revoke_reason')->nullable();
            $table->timestamps();
        });

        $rows = DB::table('certificates_prev')->select($columns)->get();

        foreach ($rows as $row) {
            DB::table('certificates')->insert((array) $row);
        }

        Schema::dropIfExists('certificates_prev');
    }

    public function down(): void
    {
        $columns = [
            'id', 'exam_attempt_id', 'user_id', 'assessment_id',
            'certificate_number', 'verification_code', 'type',
            'score_percent', 'rank_at_issue', 'pdf_path', 'issued_at',
            'revoked_at', 'revoke_reason', 'created_at', 'updated_at',
        ];

        Schema::table('certificates', function (Blueprint $table) {
            $table->dropForeign(['exam_attempt_id']);
            $table->dropForeign(['user_id']);
            $table->dropForeign(['assessment_id']);
            $table->dropForeign(['course_id']);
        });

        foreach (['certificates_exam_attempt_id_unique', 'certificates_certificate_number_unique', 'certificates_verification_code_unique'] as $index) {
            if (Schema::hasIndex('certificates', $index)) {
                Schema::table('certificates', fn (Blueprint $table) => $table->dropUnique($index));
            }
        }

        Schema::rename('certificates', 'certificates_prev');

        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_attempt_id')->constrained()->cascadeOnDelete()->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->string('certificate_number')->unique();
            $table->string('verification_code')->unique()->index();
            $table->string('type');
            $table->decimal('score_percent', 5, 2);
            $table->integer('rank_at_issue')->nullable();
            $table->string('pdf_path')->nullable();
            $table->datetime('issued_at');
            $table->datetime('revoked_at')->nullable();
            $table->string('revoke_reason')->nullable();
            $table->timestamps();
        });

        $rows = DB::table('certificates_prev')->select($columns)->get();

        foreach ($rows as $row) {
            DB::table('certificates')->insert((array) $row);
        }

        Schema::dropIfExists('certificates_prev');
    }
};