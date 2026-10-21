<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Materi & quiz untuk siswa.
 */
class LearningController extends Controller
{
    public function materials(): View
    {
        return view('learning.materials', [
            'materials' => Material::where('is_published', true)->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function material(Material $material): View
    {
        abort_unless($material->is_published, 404);

        return view('learning.material', [
            'material' => $material,
            'quizzes' => $material->quizzes()->where('is_published', true)->get(),
        ]);
    }

    public function quizzes(Request $request): View
    {
        return view('learning.quizzes', [
            'quizzes' => Quiz::where('is_published', true)->withCount('questions')->with('material')->latest()->get(),
            'best' => $request->user()->quizAttempts()->selectRaw('quiz_id, MAX(score) as score')->groupBy('quiz_id')->pluck('score', 'quiz_id'),
        ]);
    }

    public function quiz(Quiz $quiz): View
    {
        abort_unless($quiz->is_published, 404);

        return view('learning.quiz', ['quiz' => $quiz->load('questions')]);
    }

    public function submit(Request $request, Quiz $quiz): RedirectResponse
    {
        abort_unless($quiz->is_published, 404);

        $questions = $quiz->questions;
        abort_if($questions->isEmpty(), 404);

        $answers = $request->validate([
            'answers' => ['required', 'array'],
            'answers.*' => [Rule::in(QuizQuestion::KEYS)],
        ])['answers'];

        $correct = $questions->filter(fn ($q) => ($answers[$q->id] ?? null) === $q->answer)->count();

        $attempt = $quiz->attempts()->create([
            'user_id' => $request->user()->id,
            'correct' => $correct,
            'total' => $questions->count(),
            'score' => (int) round($correct / $questions->count() * 100),
            'answers' => $questions->mapWithKeys(fn ($q) => [$q->id => $answers[$q->id] ?? null])->all(),
        ]);

        return redirect()->route('quizzes.result', [$quiz, $attempt]);
    }

    public function result(Request $request, Quiz $quiz, int $attempt): View
    {
        $attempt = $quiz->attempts()->where('user_id', $request->user()->id)->findOrFail($attempt);

        return view('learning.result', ['quiz' => $quiz->load('questions'), 'attempt' => $attempt]);
    }
}
