<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Daftar akun tidak lagi meminta NIS.
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->string('nis')->nullable()->change();
        });

        Schema::table('cases', function (Blueprint $table) {
            $table->string('category', 50)->nullable()->after('level');
        });

        foreach ([
            'CASE-01' => 'penanganan_keluhan',
            'CASE-02' => 'pelanggan_tidak_puas',
            'CASE-03' => 'kesalahan_pelayanan',
            'CASE-04' => 'prioritas_pelanggan',
        ] as $code => $category) {
            DB::table('cases')->where('code', $code)->update(['category' => $category]);
        }

        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('summary')->nullable();
            $table->longText('content');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('material_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->text('question');
            $table->json('options'); // ['A' => '...', 'B' => '...', 'C' => '...', 'D' => '...']
            $table->char('answer', 1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('correct');
            $table->unsignedSmallInteger('total');
            $table->unsignedTinyInteger('score');
            $table->json('answers');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('quiz_questions');
        Schema::dropIfExists('quizzes');
        Schema::dropIfExists('materials');

        Schema::table('cases', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
