<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SurveyCampaign;

class CampaignReviewApiController extends Controller
{
    public function review($id)
    {
        $campaign = SurveyCampaign::with('panels')->findOrFail($id);

        $panelData = [];
        $totalCompletes = 0;

        foreach ($campaign->panels as $panel) {

            $panelData[] = [
                'panel_provider_id' => $panel->panel_provider_id,
                'target_completes'  => (int) $panel->target_completes,
                'achieved_completes'=> (int) $panel->achieved_completes,
                'cpi'               => (float) $panel->cpi,
                'entry_url'         => $panel->entry_url,
                'status'            => $panel->status,
            ];

            $totalCompletes += $panel->target_completes;
        }

        return response()->json([
            'campaign_details' => [
                'name'         => $campaign->campaignName,
                'country'      => $campaign->country,
                'loi'          => $campaign->loi,
                'ir'           => $campaign->ir,
                'total_target' => $campaign->total_completes ?? $totalCompletes
            ],

            'panel_allocation' => $panelData,

            'summary' => [
                'total_completes' => $totalCompletes
            ]
        ]);
    }

    public function launch($id)
    {
        $campaign = SurveyCampaign::with('panels')->findOrFail($id);

        if (
            empty($campaign->campaignName) ||
            empty($campaign->country) ||
            empty($campaign->loi) ||
            empty($campaign->ir) ||
            $campaign->panels->count() === 0
        ) {
            return response()->json([
                'message' => 'Campaign is incomplete'
            ], 422);
        }

        $campaign->update([
            'status'      => 'live',
            'launched_at' => now()
        ]);

        return response()->json([
            'message' => 'Campaign launched successfully'
        ]);
    }
}
