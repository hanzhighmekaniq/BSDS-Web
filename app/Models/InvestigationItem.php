<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestigationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'scenario_id',
        'title',
        'type',
        'content',
        'is_relevant',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_relevant' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scenario(): BelongsTo
    {
        return $this->belongsTo(Scenario::class, 'scenario_id');
    }
}
