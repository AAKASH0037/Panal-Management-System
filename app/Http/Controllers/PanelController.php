<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\SurveyCampaign;
use App\Models\SurveyCampaignPanel;
use App\Models\SurveyPanelProvider;
use App\Models\SurveyQualificationQuestion;
use App\Models\SurveyCampaignQuota;
class PanelController extends Controller
{
     public function storePanels(Request $request, $campaignId)
    {
        $campaign = SurveyCampaign::findOrFail($campaignId);

        $validated = $request->validate([
            'panels' => 'required|array',
            'panels.*.panel_provider_id' => 'required|exists:survey_panel_providers,id',
            'panels.*.target_completes' => 'required|integer|min:1',//// Max Complete
            'panels.*.cpi' => 'required|numeric|min:0',
            'panels.*.entry_url' => 'required|url', 
        ]);

        DB::transaction(function () use ($campaign, $validated) {

            // Soft delete old panels
            $campaign->panels()->delete();

            foreach ($validated['panels'] as $panel) {
                SurveyCampaignPanel::create([   
                    'campaign_id' => $campaign->id,
                    'panel_provider_id' => $panel['panel_provider_id'],
                    'target_completes' => $panel['target_completes'],
                    'cpi' => $panel['cpi'],
                    'entry_url' => $panel['entry_url'],     
                    'status' => 'active'
                ]);
            }
        });

        return response()->json([
            'status' => true,
            'message' => 'Panels saved'
        ]);
    }

public function updatePanel(Request $request, $campaignId, $providerId)
{
    // 🔹 OLD panel find (old providerId from URL)
    $panel = SurveyCampaignPanel::where('campaign_id', $campaignId)
        ->where('panel_provider_id', $providerId)
        ->whereNull('deleted_at')
        ->firstOrFail();

    // 🔹 Payload extract (support panels[0] OR flat)
    $data = $request->has('panels')
        ? $request->input('panels.0')
        : $request->all();

    // 🔹 Validation
    $validated = validator($data, [
        'panel_provider_id' => 'sometimes|exists:survey_panel_providers,id',
        'target_completes'  => 'sometimes|integer|min:1',
        'cpi'               => 'sometimes|numeric|min:0',
        'entry_url'         => 'sometimes|url',
        'status'            => 'sometimes|in:active,paused'
    ])->validate();

    // 🔒 OPTIONAL: prevent duplicate provider in same campaign
    if (
        isset($validated['panel_provider_id']) &&
        $validated['panel_provider_id'] != $providerId &&
        SurveyCampaignPanel::where('campaign_id', $campaignId)
            ->where('panel_provider_id', $validated['panel_provider_id'])
            ->whereNull('deleted_at')
            ->exists()
    ) {
        return response()->json([
            'status' => false,
            'message' => 'This panel provider already exists in this campaign'
        ], 422);
    }

    // 🔹 Update only provided fields
    $panel->update($validated);

    return response()->json([
        'status' => true,
        'message' => 'Panel updated successfully',
        'data' => $panel
    ]);
}



  public function getAllPanels()
{
       $panels = SurveyPanelProvider::select('id', 'name')->get();

    return response()->json([
        'status' => true,
        'count' => $panels->count(),
        'data' => $panels
    ]);
} 
 public function finalSubmit(Request $request, int $campaignId)
    {
      //dd("jj");
      $request->validate([
    'panel.panel_provider_id' => 'required|exists:survey_panel_providers,id',
    'panel.target_completes'  => 'required|integer|min:1',
    'panel.cpi'               => 'required|numeric|min:0',
    'panel.entry_url'         => 'required|url',

    'qualifications' => 'array',
    'qualifications.*.qs_id' => 'required|integer',
    'qualifications.*.option_ids' => 'required|array',

    'quotas' => 'array',
    'quotas.*.quota_name' => 'required|string',
    'quotas.*.target' => 'required|integer|min:1',

    'quotas.*.conditions' => 'required|array|min:1',
    'quotas.*.conditions.*.qs_id' => 'required|integer',
    'quotas.*.conditions.*.opt_id' => 'required|integer',

    'skip' => 'boolean'
]);

   //d("hh");
        DB::transaction(function () use ($request, $campaignId) {

            /* 1️⃣ SAVE / UPDATE PANEL (ALWAYS) */
            $panel = $this->savePanel(
                $campaignId,
                $request->panel,
                $request->boolean('skip')
            );

            /* 2️⃣ IF SKIP → STOP HERE */
            if ($request->boolean('skip')) {
                SurveyCampaign::where('id', $campaignId)
                    ->update(['status' => 'panel_configured']);
                return;
            }

            /* 3️⃣ SAVE QUALIFICATIONS */
            $this->saveQualifications(
                $campaignId,
                $panel->panel_provider_id,
                $request->qualifications ?? []
            );

            /* 4️⃣ SAVE QUOTAS */
          //dd("hh");
            $this->saveQuotas(
                $campaignId,
                $panel->panel_provider_id,
                $request->quotas ?? []
            );

            SurveyCampaign::where('id', $campaignId)
                ->update(['status' => 'configured']);
        });

        return response()->json([
            'status'  => true,
            'message' => $request->boolean('skip')
                ? 'Panel saved successfully (Qualification & quota skipped)'
                : 'Panel, qualification & quota saved successfully'
        ]);
    }

    /* =========================================
     * SAVE PANEL (PRIVATE)
     * ========================================= */
   private function savePanel(int $campaignId, array $panelData, bool $skip = false)
{
    return SurveyCampaignPanel::create([
        'campaign_id'       => $campaignId,
        'panel_provider_id' => $panelData['panel_provider_id'],
        'target_completes'  => $panelData['target_completes'],
        'cpi'               => $panelData['cpi'],
        'entry_url'         => $panelData['entry_url'],
        'status'            => 'active',
        'is_skipped'        => $skip ? 1 : 0
    ]);
}


    /* =========================================
     * SAVE QUALIFICATIONS (PRIVATE)
     * ========================================= */
    private function saveQualifications(
        int $campaignId,
        int $panelProviderId,
        array $qualifications
    ) {
        // DB::table('survey_campaign_qualifications')
        //     ->where('campaign_id', $campaignId)
        //     ->where('panel_provider_id', $panelProviderId)
        //     ->delete();

        foreach ($qualifications as $q) {
            foreach ($q['option_ids'] as $optId) {
                DB::table('survey_campaign_qualifications')->insert([
                    'campaign_id'        => $campaignId,
                    'panel_provider_id'  => $panelProviderId,
                    'qs_id'              => $q['qs_id'],
                    'opt_id'             => $optId,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }
        }
    }

    /* =========================================
     * SAVE QUOTAS (PRIVATE)
     * ========================================= */
private function saveQuotas(
    int $campaignId,
    int $panelProviderId,
    array $quotas
) {
    // SurveyCampaignQuota::where('campaign_id', $campaignId)
    //     ->where('panel_provider_id', $panelProviderId)
    //     ->delete();

    foreach ($quotas as $quota) {
        foreach ($quota['conditions'] as $condition) {
            SurveyCampaignQuota::create([
                'campaign_id'       => $campaignId,
                'panel_provider_id' => $panelProviderId,
                'qs_id'             => $condition['qs_id'],
                'opt_id'            => $condition['opt_id'],
                'quota_name'        => $quota['quota_name'], // 👈 parent level
                'target'            => $quota['target'],     // 👈 parent level
            ]);
        }
    }
}


}


