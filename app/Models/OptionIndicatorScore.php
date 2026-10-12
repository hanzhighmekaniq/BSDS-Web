<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OptionIndicatorScore extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'option_id',
        'indicator_id',
        'score',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
        ];
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(ScenarioOption::class, 'option_id');
    }

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(Indicator::class);
    }
}
