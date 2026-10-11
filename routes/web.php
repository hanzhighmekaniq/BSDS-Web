<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SimulationController;
use App\Models\CaseModel;
use Illuminate\Support\Facades\Route;

Route::model('case', CaseModel::class);

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:student'])->prefix('simulations')->name('simulations.')->group(function () {
    Route::get('/', [SimulationController::class, 'index'])->name('index');
    Route::post('/cases/{case}/start', [SimulationController::class, 'start'])->name('start');
    Route::get('/{session}', [SimulationController::class, 'show'])->name('show');
    Route::post('/{session}/decision', [SimulationController::class, 'submitDecision'])->name('decision');
    Route::get('/{session}/reflection', [SimulationController::class, 'reflection'])->name('reflection');
    Route::post('/{session}/reflection', [SimulationController::class, 'storeReflection'])->name('reflection.store');
    Route::get('/{session}/journey', [SimulationController::class, 'journey'])->name('journey');
});

require __DIR__.'/auth.php';

