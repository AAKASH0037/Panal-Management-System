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

        // Client
        'quota_required',
        'quota_completed',

        // PMS decision fields
        'internal_quota',
        'cint_quota',
        'eklavvya_quota',
        'purespectrum_quota',

        'internal_completed',
        'cint_completed',
        'eklavvya_completed',
        'purespectrum_completed',

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
     public function quotas()
    {
        return $this->hasMany(Quota::class, 'survey_id');
    }
}

