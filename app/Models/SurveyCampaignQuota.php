<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SurveyCampaignQuota extends Model
{
    use SoftDeletes;

    protected $table = 'survey_campaign_quotas';

    protected $fillable = [
        'campaign_id',
        'panel_provider_id',
        'qs_id',
        'opt_id',
        'quota_name',
        'target'
    ];

    protected $dates = ['deleted_at'];

    public function campaign()
    {
        return $this->belongsTo(SurveyCampaign::class, 'campaign_id');
    }

    public function panel()
    {
        return $this->belongsTo(
            SurveyCampaignPanel::class,
            'panel_provider_id',
            'panel_provider_id'
        );
    }
}
