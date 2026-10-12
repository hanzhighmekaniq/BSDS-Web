<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->renameColumn('difficulty', 'level'); // foundation, developing, challenge
            $table->string('role_title')->nullable()->after('description');
            $table->text('briefing_text')->nullable()->after('role_title');
            $table->unsignedSmallInteger('sort_order')->default(0)->after('is_active');
        });

        Schema::table('scenarios', function (Blueprint $table) {
            $table->string('scenario_type')->default('main')->after('step_code'); // main, branch, remedial, ending
            $table->boolean('is_start')->default(false)->after('scenario_type');
            $table->unsignedSmallInteger('decision_no')->nullable()->after('is_start');
            $table->unsignedTinyInteger('max_investigations')->default(2)->after('is_ending');
            $table->text('new_problem_text')->nullable()->after('customer_dialogue');
            $table->unsignedSmallInteger('sort_order')->default(0)->after('max_investigations');
        });

        Schema::table('scenario_options', function (Blueprint $table) {
            $table->string('consequence_type')->default('neutral')->after('consequence_text'); // positive, neutral, negative
            $table->string('outcome_tag')->nullable()->after('consequence_type');
            $table->unsignedSmallInteger('sort_order')->default(0)->after('outcome_tag');
            $table->dropColumn('score_weights');
        });

        Schema::table('investigation_items', function (Blueprint $table) {
            $table->boolean('is_relevant')->default(false)->after('content');
            $table->unsignedSmallInteger('sort_order')->default(0)->after('is_relevant');
        });

        Schema::table('simulation_sessions', function (Blueprint $table) {
            $table->string('mode')->default('practice')->after('case_id'); // practice, assessment
            $table->unsignedSmallInteger('attempt_no')->default(1)->after('mode');
            $table->unsignedInteger('duration_seconds')->nullable()->after('completed_at');
            $table->string('final_outcome')->nullable()->after('duration_seconds');
            $table->timestamp('last_activity_at')->nullable()->after('final_outcome');
            $table->index(['user_id', 'case_id', 'status']);
        });

        Schema::table('decision_logs', function (Blueprint $table) {
            $table->unsignedSmallInteger('step_order')->default(1)->after('session_id');
            $table->timestamp('presented_at')->nullable()->after('confidence_level');
            $table->timestamp('answered_at')->nullable()->after('presented_at');
            $table->unsignedInteger('time_spent_seconds')->nullable()->after('answered_at');
            $table->string('consequence_type')->nullable()->after('time_spent_seconds');
            $table->string('confidence_level')->nullable()->change();
            $table->dropColumn('investigations_viewed');
            $table->unique(['session_id', 'step_order']);
        });

        Schema::table('reflections', function (Blueprint $table) {
            $table->renameColumn('q2_unexpected_consequence', 'q3_unexpected_consequence');
            $table->renameColumn('q3_future_action', 'q4_future_action');
        });

        Schema::table('reflections', function (Blueprint $table) {
            $table->text('q2_hardest_decision')->nullable()->after('q1_reason');
            $table->unique('session_id');
        });

        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('academic_year');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
            $table->string('nis')->unique();
            $table->unsignedSmallInteger('absence_no')->nullable();
            $table->timestamps();
        });

        Schema::create('hints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scenario_id')->constrained('scenarios')->cascadeOnDelete();
            $table->unsignedTinyInteger('level');
            $table->text('hint_text');
            $table->timestamps();
            $table->unique(['scenario_id', 'level']);
        });

        Schema::create('hint_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('simulation_sessions')->cascadeOnDelete();
            $table->foreignId('scenario_id')->constrained('scenarios')->cascadeOnDelete();
            $table->foreignId('hint_id')->constrained('hints')->cascadeOnDelete();
            $table->timestamp('used_at');
            $table->unique(['session_id', 'hint_id']);
        });

        Schema::create('investigation_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('simulation_sessions')->cascadeOnDelete();
            $table->foreignId('scenario_id')->constrained('scenarios')->cascadeOnDelete();
            $table->foreignId('investigation_item_id')->constrained('investigation_items')->cascadeOnDelete();
            $table->timestamp('viewed_at');
            $table->unique(['session_id', 'investigation_item_id']);
        });

        Schema::create('indicators', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('option_indicator_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('option_id')->constrained('scenario_options')->cascadeOnDelete();
            $table->foreignId('indicator_id')->constrained('indicators')->cascadeOnDelete();
            $table->unsignedTinyInteger('score'); // 0-2
            $table->unique(['option_id', 'indicator_id']);
        });

        Schema::create('session_indicator_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('simulation_sessions')->cascadeOnDelete();
            $table->foreignId('indicator_id')->constrained('indicators')->cascadeOnDelete();
            $table->unsignedSmallInteger('raw_score');
            $table->unsignedSmallInteger('max_score');
            $table->string('category'); // baik, cukup, perlu_latihan
            $table->timestamps();
            $table->unique(['session_id', 'indicator_id']);
        });

        Schema::create('feedback_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indicator_id')->constrained('indicators')->cascadeOnDelete();
            $table->string('category'); // baik, cukup, perlu_latihan
            $table->text('feedback_text');
            $table->text('recommendation_text')->nullable();
            $table->timestamps();
            $table->unique(['indicator_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback_templates');
        Schema::dropIfExists('session_indicator_results');
        Schema::dropIfExists('option_indicator_scores');
        Schema::dropIfExists('indicators');
        Schema::dropIfExists('investigation_views');
        Schema::dropIfExists('hint_usages');
        Schema::dropIfExists('hints');
        Schema::dropIfExists('student_profiles');
        Schema::dropIfExists('classrooms');

        Schema::table('reflections', function (Blueprint $table) {
            $table->dropUnique(['session_id']);
            $table->dropColumn('q2_hardest_decision');
        });

        Schema::table('reflections', function (Blueprint $table) {
            $table->renameColumn('q3_unexpected_consequence', 'q2_unexpected_consequence');
            $table->renameColumn('q4_future_action', 'q3_future_action');
        });

        Schema::table('decision_logs', function (Blueprint $table) {
            $table->dropUnique(['session_id', 'step_order']);
            $table->json('investigations_viewed')->nullable();
            $table->dropColumn(['step_order', 'presented_at', 'answered_at', 'time_spent_seconds', 'consequence_type']);
        });

        Schema::table('simulation_sessions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'case_id', 'status']);
            $table->dropColumn(['mode', 'attempt_no', 'duration_seconds', 'final_outcome', 'last_activity_at']);
        });

        Schema::table('investigation_items', function (Blueprint $table) {
            $table->dropColumn(['is_relevant', 'sort_order']);
        });

        Schema::table('scenario_options', function (Blueprint $table) {
            $table->json('score_weights')->nullable();
            $table->dropColumn(['consequence_type', 'outcome_tag', 'sort_order']);
        });

        Schema::table('scenarios', function (Blueprint $table) {
            $table->dropColumn(['scenario_type', 'is_start', 'decision_no', 'max_investigations', 'new_problem_text', 'sort_order']);
        });

        Schema::table('cases', function (Blueprint $table) {
            $table->dropColumn(['role_title', 'briefing_text', 'sort_order']);
            $table->renameColumn('level', 'difficulty');
        });
    }
};
