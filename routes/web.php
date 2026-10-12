<?php

use App\Http\Controllers\Admin\CaseController as AdminCaseController;
use App\Http\Controllers\Admin\ClassroomController as AdminClassroomController;
use App\Http\Controllers\Admin\IndicatorController as AdminIndicatorController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResearchController;
use App\Http\Controllers\SimulationController;
use App\Models\CaseModel;
use App\Models\Indicator;
use App\Models\SessionIndicatorResult;
use App\Models\SimulationSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::model('case', CaseModel::class);

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    $data = [];

    if ($user->hasRole('student')) {
        $data['activeSession'] = SimulationSession::where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->with(['case', 'currentScenario'])
            ->latest('last_activity_at')
            ->first();

        $data['totalCases'] = CaseModel::where('is_active', true)->count();

        $data['completedCount'] = SimulationSession::where('user_id', $user->id)
            ->where('status', 'completed')
            ->distinct('case_id')
            ->count('case_id');

        $data['inProgressCount'] = SimulationSession::where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->count();
    }

    return view('dashboard', $data);
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/guide', function () {
        return view('guide');
    })->name('guide');
});

Route::get('/progress', function () {
    $user = auth()->user();

    $cases = CaseModel::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get();

    $completedCaseIds = SimulationSession::where('user_id', $user->id)
        ->where('status', 'completed')
        ->pluck('case_id')
        ->unique();

    $completedSessionIds = SimulationSession::where('user_id', $user->id)
        ->where('status', 'completed')
        ->pluck('id');

    $indicators = Indicator::where('is_active', true)->orderBy('sort_order')->get();

    $indicatorAverages = SessionIndicatorResult::whereIn('session_id', $completedSessionIds)
        ->select('indicator_id', DB::raw('SUM(raw_score) as raw'), DB::raw('SUM(max_score) as max'))
        ->groupBy('indicator_id')
        ->get()
        ->mapWithKeys(fn ($row) => [$row->indicator_id => $row->max > 0 ? round($row->raw / $row->max * 100) : 0]);

    return view('progress', [
        'cases' => $cases,
        'completedCaseIds' => $completedCaseIds,
        'indicators' => $indicators,
        'indicatorAverages' => $indicatorAverages,
    ]);
})->middleware(['auth', 'role:student'])->name('progress');

Route::middleware(['auth', 'role:student'])->prefix('simulations')->name('simulations.')->group(function () {
    Route::get('/', [SimulationController::class, 'index'])->name('index');
    Route::get('/cases/{case}/briefing', [SimulationController::class, 'briefing'])->name('briefing');
    Route::post('/cases/{case}/start', [SimulationController::class, 'start'])->name('start');
    Route::get('/{session}', [SimulationController::class, 'show'])->name('show');
    Route::post('/{session}/investigate/{item}', [SimulationController::class, 'investigate'])->name('investigate');
    Route::post('/{session}/hint', [SimulationController::class, 'hint'])->name('hint');
    Route::post('/{session}/decision', [SimulationController::class, 'submitDecision'])->name('decision');
    Route::get('/{session}/consequence', [SimulationController::class, 'consequence'])->name('consequence');
    Route::get('/{session}/reflection', [SimulationController::class, 'reflection'])->name('reflection');
    Route::post('/{session}/reflection', [SimulationController::class, 'storeReflection'])->name('reflection.store');
    Route::get('/{session}/feedback', [SimulationController::class, 'feedback'])->name('feedback');
    Route::get('/{session}/journey', [SimulationController::class, 'journey'])->name('journey');
});

Route::middleware(['auth', 'role:researcher|admin'])->prefix('research')->name('research.')->group(function () {
    Route::get('/', [ResearchController::class, 'index'])->name('index');
    Route::get('/export', [ResearchController::class, 'export'])->name('export');
    Route::get('/sessions/{session}', [ResearchController::class, 'show'])->name('show');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('users/import', [AdminUserController::class, 'importForm'])->name('users.import.form');
    Route::post('users/import', [AdminUserController::class, 'import'])->name('users.import');
    Route::resource('users', AdminUserController::class)->except('show');
    Route::resource('classrooms', AdminClassroomController::class)->except('show');
    Route::get('cases', [AdminCaseController::class, 'index'])->name('cases.index');
    Route::get('cases/{case}/edit', [AdminCaseController::class, 'edit'])->name('cases.edit');
    Route::put('cases/{case}', [AdminCaseController::class, 'update'])->name('cases.update');
    Route::get('indicators', [AdminIndicatorController::class, 'index'])->name('indicators.index');
    Route::get('indicators/{indicator}/edit', [AdminIndicatorController::class, 'edit'])->name('indicators.edit');
    Route::put('indicators/{indicator}', [AdminIndicatorController::class, 'update'])->name('indicators.update');
});

require __DIR__.'/auth.php';

