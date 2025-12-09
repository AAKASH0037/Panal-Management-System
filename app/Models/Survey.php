<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Survey extends Model
{
    use SoftDeletes;
    protected $table = 'surveys';

    protected $fillable = [
        'provider_survey_id',
        'survey_name',
        'quota_required',
        'quota_completed',
        'country_language_id',
        'cpi',
        'status',
        'live_url',
        'test_url',
        'incidence',
        'settings'
    ];

    protected $casts = [
        'settings' => 'array'
    ];
}

