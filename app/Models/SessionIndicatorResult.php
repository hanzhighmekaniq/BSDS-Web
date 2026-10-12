<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionIndicatorResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'indicator_id',
        'raw_score',
        'max_score',
        'category',
    ];

    protected function casts(): array
    {
        return [
            'raw_score' => 'integer',
            'max_score' => 'integer',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(SimulationSession::class, 'session_id');
    }

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(Indicator::class);
    }
}
