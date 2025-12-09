<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quota extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'quota',
        'is_active',
        'conditions',
    ];

    protected $casts = [
        'quota' => 'integer',
        'is_active' => 'boolean',
        'conditions' => 'array',
    ];
}
