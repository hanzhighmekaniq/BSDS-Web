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
        'scenario_id',
        'option_id',
        'confidence_level',
        'investigations_viewed',
    ];

    protected function casts(): array
    {
        return [
            'investigations_viewed' => 'array',
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
