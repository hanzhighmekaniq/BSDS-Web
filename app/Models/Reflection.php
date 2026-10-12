<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reflection extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'q1_reason',
        'q2_hardest_decision',
        'q3_unexpected_consequence',
        'q4_future_action',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(SimulationSession::class, 'session_id');
    }
}
