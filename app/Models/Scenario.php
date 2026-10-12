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
        'scenario_type',
        'is_start',
        'decision_no',
        'title',
        'story_text',
        'customer_dialogue',
        'new_problem_text',
        'is_ending',
        'max_investigations',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_start' => 'boolean',
            'is_ending' => 'boolean',
            'decision_no' => 'integer',
            'max_investigations' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function hints(): HasMany
    {
        return $this->hasMany(Hint::class, 'scenario_id')->orderBy('level');
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
