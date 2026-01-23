<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SurveyCampaign extends Model
{
    use SoftDeletes;

    protected $table = 'survey_campaigns';

    protected $fillable = [
         'campaignName',
        'country_id',
        'language_id',
        'loi',
        'ir',
        'total_completes',
        'status'
    ];

    protected $dates = ['deleted_at'];
  public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id');
    }
    public function panels()
    {
        return $this->hasMany(SurveyCampaignPanel::class, 'campaign_id');
    }

    public function redirect()
    {
        return $this->hasOne(SurveyCampaignRedirect::class, 'campaign_id');
    }

public function quotas()
{
    return $this->hasMany(SurveyCampaignQuota::class, 'campaign_id');
}


}
