<?php

namespace Tests\Feature;

use App\Models\Quiz;
use App\Models\User;
use Database\Seeders\LearningSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LearningTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_reads_material_and_quiz_is_scored_on_server(): void
    {
        $this->seed(LearningSeeder::class);
        $student = User::factory()->create();
        $student->assignRole(Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']));

        $quiz = Quiz::first();
        $questions = $quiz->questions;

        $this->actingAs($student)->get(route('materials.index'))->assertOk()->assertSee('Dasar Pelayanan Pelanggan');
        $this->get(route('quizzes.show', $quiz))->assertOk()->assertDontSee('"answer"', false);

        // Jawab soal pertama benar, sisanya salah.
        $answers = $questions->mapWithKeys(fn ($q, $i) => [$q->id => $i === 0 ? $q->answer : ($q->answer === 'A' ? 'B' : 'A')])->all();

        $response = $this->post(route('quizzes.submit', $quiz), ['answers' => $answers]);

        $attempt = $student->quizAttempts()->first();
        $response->assertRedirect(route('quizzes.result', [$quiz, $attempt->id]));
        $this->assertSame(1, $attempt->correct);
        $this->assertSame(round(100 / $questions->count()), (float) $attempt->score);

        $this->get(route('quizzes.result', [$quiz, $attempt->id]))->assertOk()->assertSee('1 dari');
    }

    public function test_admin_creates_quiz_with_questions(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']));

        $this->actingAs($admin)->post(route('admin.quizzes.store'), [
            'title' => 'Quiz Baru',
            'is_published' => 1,
            'questions' => [
                ['question' => 'Apa?', 'options' => ['A' => '1', 'B' => '2', 'C' => '3', 'D' => '4'], 'answer' => 'C'],
            ],
        ])->assertRedirect(route('admin.quizzes.index'));

        $quiz = Quiz::where('title', 'Quiz Baru')->first();
        $this->assertSame('C', $quiz->questions->first()->answer);
    }
}
