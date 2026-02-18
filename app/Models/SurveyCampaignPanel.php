<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SurveyCampaignPanel extends Model
{
    use SoftDeletes;

    protected $table = 'survey_campaign_panels';

    protected $fillable = [
        'campaign_id',
        'panel_provider_id',
        'target_completes',
            'is_auto', 
        'achieved_completes',
        'cpi',
        'skip' ,
         'entry_url',
        'status'
    ];

    protected $dates = ['deleted_at'];

    public function campaign()
    {
        return $this->belongsTo(SurveyCampaign::class, 'campaign_id');
    }

    public function provider()
    {
        return $this->belongsTo(SurveyPanelProvider::class, 'panel_provider_id');
    }
public function quotas()
{
    return $this->hasMany(
        SurveyCampaignQuota::class,
        'panel_provider_id',
        'panel_provider_id'
    );
}
}
