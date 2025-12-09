<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Qualification extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'question_id',
        'logical_operator',
        'number_of_required_conditions',
        'is_active',
        'pre_codes',
        'order'
    ];

    protected $casts = [
        'pre_codes' => 'array',
        'is_active' => 'boolean'
    ];
}
