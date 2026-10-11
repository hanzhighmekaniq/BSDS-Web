<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scenario extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_id',
        'step_code',
        'title',
        'story_text',
        'customer_dialogue',
        'is_ending',
    ];

    protected function casts(): array
    {
        return [
            'is_ending' => 'boolean',
        ];
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(CaseModel::class, 'case_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(ScenarioOption::class, 'scenario_id');
    }

    public function investigationItems(): HasMany
    {
        return $this->hasMany(InvestigationItem::class, 'scenario_id');
    }

    public function decisionLogs(): HasMany
    {
        return $this->hasMany(DecisionLog::class, 'scenario_id');
    }
}
