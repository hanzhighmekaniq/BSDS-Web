<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DecisionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'step_order',
        'scenario_id',
        'option_id',
        'confidence_level',
        'presented_at',
        'answered_at',
        'time_spent_seconds',
        'consequence_type',
    ];

    protected function casts(): array
    {
        return [
            'step_order' => 'integer',
            'presented_at' => 'datetime',
            'answered_at' => 'datetime',
            'time_spent_seconds' => 'integer',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(SimulationSession::class, 'session_id');
    }

    public function scenario(): BelongsTo
    {
        return $this->belongsTo(Scenario::class, 'scenario_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(ScenarioOption::class, 'option_id');
    }
}
