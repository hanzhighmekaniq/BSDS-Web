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
        Schema::create('cases', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('title');
            $table->text('description');
            $table->string('difficulty')->default('medium');
            $table->integer('estimated_time');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('scenarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->cascadeOnDelete();
            $table->string('step_code');
            $table->string('title');
            $table->text('story_text');
            $table->text('customer_dialogue')->nullable();
            $table->boolean('is_ending')->default(false);
            $table->timestamps();
        });

        Schema::create('investigation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scenario_id')->constrained('scenarios')->cascadeOnDelete();
            $table->string('title');
            $table->string('type'); // sop, receipt, stock, supervisor
            $table->text('content');
            $table->timestamps();
        });

        Schema::create('scenario_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scenario_id')->constrained('scenarios')->cascadeOnDelete();
            $table->foreignId('next_scenario_id')->nullable()->constrained('scenarios')->nullOnDelete();
            $table->string('label'); // A, B, C
            $table->text('option_text');
            $table->text('consequence_text');
            $table->json('score_weights')->nullable();
            $table->timestamps();
        });

        Schema::create('simulation_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('case_id')->constrained('cases')->cascadeOnDelete();
            $table->foreignId('current_scenario_id')->nullable()->constrained('scenarios')->nullOnDelete();
            $table->string('status')->default('in_progress'); // in_progress, completed
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('decision_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('simulation_sessions')->cascadeOnDelete();
            $table->foreignId('scenario_id')->constrained('scenarios')->cascadeOnDelete();
            $table->foreignId('option_id')->constrained('scenario_options')->cascadeOnDelete();
            $table->string('confidence_level'); // unsure, fairly_confident, confident, very_confident
            $table->json('investigations_viewed')->nullable();
            $table->timestamps();
        });

        Schema::create('reflections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('simulation_sessions')->cascadeOnDelete();
            $table->text('q1_reason');
            $table->text('q2_unexpected_consequence');
            $table->text('q3_future_action');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reflections');
        Schema::dropIfExists('decision_logs');
        Schema::dropIfExists('simulation_sessions');
        Schema::dropIfExists('scenario_options');
        Schema::dropIfExists('investigation_items');
        Schema::dropIfExists('scenarios');
        Schema::dropIfExists('cases');
    }
};
