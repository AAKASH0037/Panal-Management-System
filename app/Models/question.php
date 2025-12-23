<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['question_text', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}

