<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScenarioOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'scenario_id',
        'next_scenario_id',
        'label',
        'option_text',
        'consequence_text',
        'score_weights',
    ];

    protected function casts(): array
    {
        return [
            'score_weights' => 'array',
        ];
    }

    public function scenario(): BelongsTo
    {
        return $this->belongsTo(Scenario::class, 'scenario_id');
    }

    public function nextScenario(): BelongsTo
    {
        return $this->belongsTo(Scenario::class, 'next_scenario_id');
    }

    public function decisionLogs(): HasMany
    {
        return $this->hasMany(DecisionLog::class, 'option_id');
    }
}
