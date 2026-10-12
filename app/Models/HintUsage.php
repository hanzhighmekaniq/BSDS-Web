<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HintUsage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'scenario_id',
        'hint_id',
        'used_at',
    ];

    protected function casts(): array
    {
        return [
            'used_at' => 'datetime',
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

    public function hint(): BelongsTo
    {
        return $this->belongsTo(Hint::class, 'hint_id');
    }
}
