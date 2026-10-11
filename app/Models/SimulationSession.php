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
        'current_scenario_id',
        'status',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
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
