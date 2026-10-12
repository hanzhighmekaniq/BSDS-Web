<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Indicator extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function optionScores(): HasMany
    {
        return $this->hasMany(OptionIndicatorScore::class);
    }

    public function feedbackTemplates(): HasMany
    {
        return $this->hasMany(FeedbackTemplate::class);
    }

    public function sessionResults(): HasMany
    {
        return $this->hasMany(SessionIndicatorResult::class);
    }
}
