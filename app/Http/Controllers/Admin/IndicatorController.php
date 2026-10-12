<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndicatorRequest;
use App\Models\Indicator;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class IndicatorController extends Controller
{
    public const CATEGORIES = ['baik' => 'Baik', 'cukup' => 'Cukup', 'perlu_latihan' => 'Perlu latihan'];

    public function index(): View
    {
        return view('admin.indicators.index', [
            'indicators' => Indicator::withCount('sessionResults')->orderBy('sort_order')->get(),
        ]);
    }

    public function edit(Indicator $indicator): View
    {
        return view('admin.indicators.form', [
            'indicator' => $indicator->load('feedbackTemplates'),
            'categories' => self::CATEGORIES,
        ]);
    }

    /**
     * Kode indikator tidak dapat diubah agar kolom ekspor/analisis tetap konsisten.
     */
    public function update(IndicatorRequest $request, Indicator $indicator): RedirectResponse
    {
        $indicator->update($request->safe()->only(['name', 'description', 'sort_order', 'is_active']));

        foreach (array_keys(self::CATEGORIES) as $category) {
            $data = $request->validated('feedback')[$category] ?? null;

            if ($data === null) {
                continue;
            }

            $indicator->feedbackTemplates()->updateOrCreate(
                ['category' => $category],
                [
                    'feedback_text' => $data['feedback_text'] ?? '',
                    'recommendation_text' => $data['recommendation_text'] ?? null,
                ]
            );
        }

        return redirect()->route('admin.indicators.index')->with('status', 'Indikator berhasil diperbarui.');
    }
}
