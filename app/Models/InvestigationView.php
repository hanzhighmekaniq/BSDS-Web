<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestigationView extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'scenario_id',
        'investigation_item_id',
        'viewed_at',
    ];

    protected function casts(): array
    {
        return [
            'viewed_at' => 'datetime',
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

    public function item(): BelongsTo
    {
        return $this->belongsTo(InvestigationItem::class, 'investigation_item_id');
    }
}
