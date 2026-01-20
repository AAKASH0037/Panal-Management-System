<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SurveyCampaignRedirect extends Model
{
    use SoftDeletes;

    protected $table = 'survey_campaign_redirects';

    protected $fillable = [
        'campaign_id',
        'success_url',
        'terminate_url',
        'overquota_url'
    ];

    protected $dates = ['deleted_at'];

    public function campaign()
    {
        return $this->belongsTo(SurveyCampaign::class, 'campaign_id');
    }
}
