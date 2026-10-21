<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QuizController extends Controller
{
    public function index(): View
    {
        return view('admin.quizzes.index', [
            'quizzes' => Quiz::withCount(['questions', 'attempts'])->with('material')->latest()->get(),
        ]);
    }

    public function create(): View
    {
        return $this->form(new Quiz(['is_published' => true]));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->save($request, new Quiz);

        return redirect()->route('admin.quizzes.index')->with('status', 'Quiz berhasil dibuat.');
    }

    public function edit(Quiz $quiz): View
    {
        return $this->form($quiz->load('questions'));
    }

    public function update(Request $request, Quiz $quiz): RedirectResponse
    {
        $this->save($request, $quiz);

        return redirect()->route('admin.quizzes.index')->with('status', 'Quiz berhasil diperbarui.');
    }

    public function destroy(Quiz $quiz): RedirectResponse
    {
        $quiz->delete();

        return redirect()->route('admin.quizzes.index')->with('status', 'Quiz berhasil dihapus.');
    }

    private function form(Quiz $quiz): View
    {
        return view('admin.quizzes.form', [
            'quiz' => $quiz,
            'materials' => Material::orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    /**
     * Simpan quiz + seluruh soal. Soal lama diganti (skor attempt lama tetap tersimpan).
     */
    private function save(Request $request, Quiz $quiz): void
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'material_id' => ['nullable', 'integer', 'exists:materials,id'],
            'questions' => ['required', 'array', 'min:1', 'max:100'],
            'questions.*.question' => ['required', 'string', 'max:2000'],
            'questions.*.options' => ['required', 'array'],
            'questions.*.answer' => ['required', Rule::in(QuizQuestion::KEYS)],
        ] + collect(QuizQuestion::KEYS)->mapWithKeys(fn ($k) => ["questions.*.options.$k" => ['required', 'string', 'max:500']])->all());

        DB::transaction(function () use ($request, $quiz, $data) {
            $quiz->fill([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'material_id' => $data['material_id'] ?? null,
                'is_published' => $request->boolean('is_published'),
            ])->save();

            $quiz->questions()->delete();

            foreach (array_values($data['questions']) as $i => $q) {
                $quiz->questions()->create([
                    'question' => $q['question'],
                    'options' => array_intersect_key($q['options'], array_flip(QuizQuestion::KEYS)),
                    'answer' => $q['answer'],
                    'sort_order' => $i,
                ]);
            }
        });
    }
}
