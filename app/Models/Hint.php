<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hint extends Model
{
    use HasFactory;

    protected $fillable = [
        'scenario_id',
        'level',
        'hint_text',
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
        ];
    }

    public function scenario(): BelongsTo
    {
        return $this->belongsTo(Scenario::class, 'scenario_id');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(HintUsage::class, 'hint_id');
    }
}
