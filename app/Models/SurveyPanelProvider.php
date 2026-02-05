<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SurveyPanelProvider extends Model
{
    use SoftDeletes;

    protected $table = 'survey_panel_providers';

    protected $fillable = ['name','panel_id',
        'country_id',
        'success_url',
        'terminate_url',
        'overquota_url',
        'quality_fail_url',
        'status'];

    protected $dates = ['deleted_at'];

    public function campaignPanels()
    {
        return $this->hasMany(SurveyCampaignPanel::class, 'panel_provider_id');
    }
    // SurveyPanelProvider.php
public function country()
{
    return $this->belongsTo(Country::class, 'country_id');
}

}

