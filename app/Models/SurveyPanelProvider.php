<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SurveyPanelProvider extends Model
{
    use SoftDeletes;

    protected $table = 'survey_panel_providers';

    protected $fillable = ['name'];

    protected $dates = ['deleted_at'];

    public function campaignPanels()
    {
        return $this->hasMany(SurveyCampaignPanel::class, 'panel_provider_id');
    }
}
