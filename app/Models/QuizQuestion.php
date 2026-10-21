<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    public const KEYS = ['A', 'B', 'C', 'D'];

    protected $fillable = ['quiz_id', 'question', 'options', 'answer', 'sort_order'];

    protected function casts(): array
    {
        return ['options' => 'array'];
    }
}
