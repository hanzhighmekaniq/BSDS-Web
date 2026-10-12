<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SimulationSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'case_id',
        'mode',
        'attempt_no',
        'current_scenario_id',
        'status',
        'started_at',
        'completed_at',
        'duration_seconds',
        'final_outcome',
        'last_activity_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'attempt_no' => 'integer',
            'duration_seconds' => 'integer',
        ];
    }

    public function isPractice(): bool
    {
        return $this->mode === 'practice';
    }

    public function hintUsages(): HasMany
    {
        return $this->hasMany(HintUsage::class, 'session_id');
    }

    public function investigationViews(): HasMany
    {
        return $this->hasMany(InvestigationView::class, 'session_id');
    }

    public function indicatorResults(): HasMany
    {
        return $this->hasMany(SessionIndicatorResult::class, 'session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(CaseModel::class, 'case_id');
    }

    public function currentScenario(): BelongsTo
    {
        return $this->belongsTo(Scenario::class, 'current_scenario_id');
    }

    public function decisionLogs(): HasMany
    {
        return $this->hasMany(DecisionLog::class, 'session_id');
    }

    public function reflection(): HasOne
    {
        return $this->hasOne(Reflection::class, 'session_id');
    }
}
