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
   
  public function getAllPanels()
{
       $panels = SurveyPanelProvider::select('id', 'name')->get();

    return response()->json([
        'status' => true,
        'count' => $panels->count(),
        'data' => $panels
    ]);
} 


public function getCampaignPanels($campaignId)
{
    $panels = SurveyCampaignPanel::with('provider')
        ->where('campaign_id', $campaignId)
        ->whereNull('deleted_at')
        ->get()
        ->map(function ($panel) {
            return [
                'id' => $panel->id,
                'campaign_id' => $panel->campaign_id,
                'panel_provider_id' => $panel->panel_provider_id,
                'max_completes' => $panel->is_auto ? 'Auto' : $panel->target_completes,
                'achieved_completes' => $panel->achieved_completes,
                'cpi' => $panel->cpi,
                'skip'=> $panel->skip,
                'status' => $panel->status,
                'provider' => $panel->provider,

            ];
        });

    return response()->json([
        'status' => true,
        'count'  => $panels->count(),
        'data'   => $panels
    ]);
}






public function finalSubmit(Request $request, int $campaignId)
{
    $request->validate([
        'panel.panel_provider_id' => 'required|exists:survey_panel_providers,id',
        'panel.target_completes'  => 'nullable',
        'panel.cpi'               => 'required|numeric|min:0',
        'panel.entry_url'         => 'required|url',

        'qualifications' => 'nullable|array',
        'qualifications.*.qs_id' => 'required|integer',
        'qualifications.*.option_ids' => 'required|array',

        'quotas' => 'nullable|array',
        'quotas.*.quota_name' => 'required|string',
        'quotas.*.target' => 'required|integer|min:1',
        'quotas.*.conditions' => 'nullable|array|min:1',
        'quotas.*.conditions.*.qs_id' => 'required|integer',
        'quotas.*.conditions.*.opt_id' => 'required|integer',

        'skip' => 'boolean'
    ]);

    $skip = $request->boolean('skip');

    DB::transaction(function () use ($request, $campaignId, $skip) {

        // ✅ PANEL SAVE (Skip pass kar rahe hain)
        $panel = $this->savePanel(
            $campaignId,
            $request->panel,
            $skip
        );

        // ✅ IF SKIP TRUE → qualification & quota save nahi hoga
        if ($skip) {
            SurveyCampaign::where('id', $campaignId)
                ->update(['status' => 'panel_configured']);
            return;
        }

        // ✅ SAVE QUALIFICATIONS
        if (!empty($request->qualifications)) {
            $this->saveQualifications(
                $campaignId,
                $panel->panel_provider_id,
                $request->qualifications
            );
        }

        // ✅ SAVE QUOTAS
        if (!empty($request->quotas)) {
            $this->saveQuotas(
                $campaignId,
                $panel->panel_provider_id,
                $request->quotas
            );
        }

        SurveyCampaign::where('id', $campaignId)
            ->update(['status' => 'configured']);
    });

    return response()->json([
        'status'  => true,
        'message' => $skip
            ? 'Panel saved successfully (Qualification & quota skipped)'
            : 'Panel, qualification & quota saved successfully'
    ]);
}



        /* =========================================
        * SAVE PANEL (PRIVATE)
        * ========================================= */
    //    private function savePanel(int $campaignId, array $panelData, bool $skip = false)
    // {
    //     return SurveyCampaignPanel::create([
    //         'campaign_id'       => $campaignId,
    //         'panel_provider_id' => $panelData['panel_provider_id'],
    //         'target_completes'  => $panelData['target_completes'],
    //         'cpi'               => $panelData['cpi'],
    //         'entry_url'         => $panelData['entry_url'],
    //         'status'            => 'active',
    //         'is_skipped'        => $skip ? 1 : 0
    //     ]);
    // }

  private function savePanel(int $campaignId, array $panelData, bool $skip = false)
{
    $isAuto = isset($panelData['target_completes']) &&
        ($panelData['target_completes'] === 'Auto' || empty($panelData['target_completes']));

    if ($isAuto) {
        $targetCompletes = $this->calculateAutoTarget(
            $campaignId,
            (float) $panelData['cpi']
        );
    } else {
        $targetCompletes = (int) $panelData['target_completes'];
    }

    return SurveyCampaignPanel::updateOrCreate(
        [
            'campaign_id'       => $campaignId,
            'panel_provider_id' => $panelData['panel_provider_id'],
        ],
        [
            'target_completes'   => $targetCompletes,
            'is_auto'            => $isAuto ? 1 : 0,
            'cpi'                => $panelData['cpi'],
            'entry_url'          => $panelData['entry_url'],
            'status'             => 'active',
            'achieved_completes' => 0,
            'skip'               => $skip ? 1 : 0  // ✅ SAVE HERE
        ]
    );
}




    private function calculateAutoTarget(int $campaignId, float $currentPanelCpi): int
    {
        $campaign = SurveyCampaign::findOrFail($campaignId);

        // 🔹 All panels (including current one)
        $panels = SurveyCampaignPanel::where('campaign_id', $campaignId)
            ->whereNull('deleted_at')
            ->get();

        // Total CPI (existing panels + current)
        $totalCpi = $panels->sum('cpi') + $currentPanelCpi;

        if ($totalCpi <= 0) {
            return 1;
        }

        // 🔹 Weight based allocation
        $weight = $currentPanelCpi / $totalCpi;

        $autoTarget = floor($campaign->total_completes * $weight);

        // 🔹 Minimum 1 complete
        return max(1, (int) $autoTarget);
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

 public function finalUpdate(
    Request $request,
    int $campaignId,
    int $panelProviderId
) {
    $request->validate([
        'panel.target_completes' => 'sometimes',
        'panel.cpi'              => 'sometimes|numeric|min:0',
        'panel.entry_url'        => 'sometimes|url',
        'skip'                   => 'sometimes|boolean',
    ]);

    // 🔒 READ ONCE
    $skip = (bool) $request->input('skip');

    DB::transaction(function () use ($request, $campaignId, $panelProviderId, $skip) {

        if ($request->filled('panel')) {
            $this->updatePanelData(
                $campaignId,
                $panelProviderId,
                $request->panel
            );
        }

        if ($skip) {
            SurveyCampaign::where('id', $campaignId)
                ->update(['status' => 'panel_configured']);
            return;
        }

        if ($request->filled('qualifications')) {
            $this->updateQualifications(
                $campaignId,
                $panelProviderId,
                $request->qualifications
            );
        }

        if ($request->filled('quotas')) {
            $this->updateQuotas(
                $campaignId,
                $panelProviderId,
                $request->quotas
            );
        }

        SurveyCampaign::where('id', $campaignId)
            ->update(['status' => 'configured']);
    });

    // ✅ USE SAME VARIABLE
    return response()->json([
        'status'  => true,
        'message' => $skip
            ? 'Panel updated successfully (Qualification & quota skipped)'
            : 'Panel, qualification & quota updated successfully'
    ]);
}


// private function updatePanelData(
//     int $campaignId,
//     int $panelProviderId,
//     array $panel
// ) {
//     // Restore all soft-deleted records first
//     SurveyCampaignPanel::withTrashed()
//         ->where('campaign_id', $campaignId)
//         ->where('panel_provider_id', $panelProviderId)
//         ->whereNotNull('deleted_at')
//         ->restore();

//     // Update ALL matching records (including duplicates)
//     $updated = SurveyCampaignPanel::where('campaign_id', $campaignId)
//         ->where('panel_provider_id', $panelProviderId)
//         ->update([
//             'target_completes' => $panel['target_completes'],
//             'cpi'              => $panel['cpi'],
//             'entry_url'        => $panel['entry_url'],
//             'updated_at'       => now(),
//         ]);

//     if ($updated === 0) {
//         throw new \Exception(
//             "No panels found for campaign_id={$campaignId} and panel_provider_id={$panelProviderId}"
//         );
//     }

//     return $updated; // number of rows updated
// }


private function updatePanelData(
    int $campaignId,
    int $panelProviderId,
    array $panel
) {
    // 🔹 Restore soft-deleted panels (if any)
    SurveyCampaignPanel::withTrashed()
        ->where('campaign_id', $campaignId)
        ->where('panel_provider_id', $panelProviderId)
        ->whereNotNull('deleted_at')
        ->restore();

    // 🔹 AUTO detect
    $isAuto = isset($panel['target_completes']) &&
        ($panel['target_completes'] === 'Auto' || empty($panel['target_completes']));

    // 🔹 Calculate target completes
    if ($isAuto) {
        $targetCompletes = $this->calculateAutoTarget(
            $campaignId,
            (float) $panel['cpi']
        );
    } else {
        $targetCompletes = (int) $panel['target_completes'];
    }

    // 🔹 UPDATE panel and capture affected rows
    $updated = SurveyCampaignPanel::where('campaign_id', $campaignId)
        ->where('panel_provider_id', $panelProviderId)
        ->update([
            'target_completes' => $targetCompletes,
            'is_auto'          => $isAuto ? 1 : 0,
            'cpi'              => $panel['cpi'],
            'entry_url'        => $panel['entry_url'],
            'updated_at'       => now(),
        ]);

    // 🔹 Safety check
    if ($updated === 0) {
        throw new \Exception(
            "No panels found for campaign_id={$campaignId} and panel_provider_id={$panelProviderId}"
        );
    }

    return $updated; // number of rows updated
}




private function updateQualifications(
    int $campaignId,
    int $panelProviderId,
    array $qualifications
) {
    foreach ($qualifications as $q) {
        foreach ($q['option_ids'] as $optId) {

            DB::table('survey_campaign_qualifications')->updateOrInsert(
                [
                    'campaign_id'       => $campaignId, 
                    'panel_provider_id' => $panelProviderId,
                    'qs_id'             => $q['qs_id'],
                    'opt_id'            => $optId, // ✅ integer value
                ],
                [
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

        }
    }
}


private function updateQuotas(
    int $campaignId,
    int $panelProviderId,
    array $quotas
) {
    DB::transaction(function () use ($campaignId, $panelProviderId, $quotas) {

        foreach ($quotas as $quota) {
            foreach ($quota['conditions'] as $condition) {

                SurveyCampaignQuota::updateOrCreate(
                    [
                        'campaign_id'       => $campaignId,
                        'panel_provider_id' => $panelProviderId,
                        'quota_name'        => $quota['quota_name'],
                        'qs_id'             => $condition['qs_id'],
                        'opt_id'            => $condition['opt_id'],
                    ],
                    [
                        'target' => $quota['target'],
                    ]
                );

            }
        }
    });
}

public function finalDelete(
    Request $request,
    int $campaignId,
    int $panelProviderId
) {
    $skip = $request->boolean('skip'); // ✅ get skip value

    DB::transaction(function () use ($campaignId, $panelProviderId, $skip) {

        // 1️⃣ Always delete panel
        SurveyCampaignPanel::where('campaign_id', $campaignId)
            ->where('panel_provider_id', $panelProviderId)
            ->delete();

        // 2️⃣ If skip = false → delete everything
        if (!$skip) {

            // Delete Qualifications
            SurveyQualificationQuestion::where('campaign_id', $campaignId)
                ->where('panel_provider_id', $panelProviderId)
                ->delete();

            // Delete Quotas
            SurveyCampaignQuota::where('campaign_id', $campaignId)
                ->where('panel_provider_id', $panelProviderId)
                ->delete();

            SurveyCampaign::where('id', $campaignId)
                ->update(['status' => 'deleted']);

        } else {

            // If skip = true → only panel deleted
            SurveyCampaign::where('id', $campaignId)
                ->update(['status' => 'panel_deleted']);
        }
    });

    return response()->json([
        'status'  => true,
        'message' => $skip
            ? 'Panel deleted successfully (Qualification & quota skipped)'
            : 'Panel and all related data deleted successfully'
    ]);
}

public function togglePanelStatus(
    int $campaignId,
    int $panelProviderId
) {
    $panels = SurveyCampaignPanel::where('campaign_id', $campaignId)
        ->where('panel_provider_id', $panelProviderId)
        ->whereNull('deleted_at')
        ->get();

    if ($panels->isEmpty()) {
        return response()->json([
            'status' => false,
            'message' => 'No panels found'
        ]);
    }

    foreach ($panels as $panel) {
        $newStatus = $panel->status === 'active' ? 'paused' : 'active';
        $panel->update([
            'status' => $newStatus
        ]);
    }

    return response()->json([
        'status' => true,
        'message' => 'All panels toggled successfully'
    ]);
}


}


