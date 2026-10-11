<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CaseModel extends Model
{
    use HasFactory;

    protected $table = 'cases';

    protected $fillable = [
        'code',
        'title',
        'description',
        'difficulty',
        'estimated_time',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'estimated_time' => 'integer',
        ];
    }

    public function scenarios(): HasMany
    {
        return $this->hasMany(Scenario::class, 'case_id');
    }

    public function simulationSessions(): HasMany
    {
        return $this->hasMany(SimulationSession::class, 'case_id');
    }

    public function initialScenario(): ?Scenario
    {
        return $this->scenarios()->where('step_code', 'S1')->first()
            ?? $this->scenarios()->orderBy('id')->first();
    }
}
