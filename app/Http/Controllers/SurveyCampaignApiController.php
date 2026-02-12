<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\SurveyCampaign;
use App\Models\SurveyCampaignPanel;
use App\Models\SurveyCampaignRedirect;
use App\Models\SurveyPanelProvider;
use App\Models\Country;
use App\Models\Language;
use App\Models\SurveyQualificationQuestion;

class SurveyCampaignApiController extends Controller
{
    /* =====================================================
     * LIST CAMPAIGNS
     * ===================================================== */
    public function index()
    {
        return response()->json([
            'status' => true,
            'data' => SurveyCampaign::latest()->get()
        ]);
    }

    /* =====================================================
     * CREATE CAMPAIGN (BASICS)
     * ===================================================== */
public function storeBasics(Request $request)
{
    $validated = $request->validate([
        'id' => 'nullable|integer|exists:survey_campaigns,id',
        'campaignName' => 'nullable|string|max:255',
        'country_id' => 'required|integer|exists:countries,id',
        'language_id' => 'required|integer|exists:languages,id',
        'loi' => 'required|integer|min:1',
        'ir' => 'required|integer|min:1|max:100',
        'total_completes' => 'required|integer|min:1',
    ]);

    // ✅ Check using request()->filled()
    if ($request->filled('id')) {

        $campaign = SurveyCampaign::findOrFail($request->id);

        // id ko update data me include mat karo
        unset($validated['id']);

        $campaign->update($validated);

        $message = 'Campaign updated successfully';

    } else {

        $campaign = SurveyCampaign::create([
            ...$validated,
            'status' => 'draft',
        ]);

        $message = 'Campaign created successfully';
    }

    return response()->json([
        'status' => true,
        'message' => $message,
        'data' => $campaign
    ]);
}



    /* =====================================================
     * ADD / UPDATE PANELS
     * ===================================================== */
   

    /* =====================================================
     * SAVE REDIRECT URLS
     * ===================================================== */
    public function storeRedirects(Request $request, $campaignId)
    {
        $validated = $request->validate([
            'success_url' => 'required|url',
            'terminate_url' => 'required|url',
            'overquota_url' => 'required|url',
        ]);

        SurveyCampaignRedirect::updateOrCreate(
            ['campaign_id' => $campaignId],
            $validated
        );

        return response()->json([
            'status' => true,
            'message' => 'Redirects saved'
        ]);
    }

    /* =====================================================
     * REVIEW CAMPAIGN
     * ===================================================== */
    public function review($campaignId)
    {
        
        $campaign = SurveyCampaign::with(['panels.provider', 'redirect'])
            ->findOrFail($campaignId);

        return response()->json([
            'status' => true,
            'data' => $campaign,
            'summary' => [
                'allocated_completes' => $campaign->panels->sum('target_completes'),
                'remaining_completes' => $campaign->total_completes -
                    $campaign->panels->sum('target_completes'),
                'total_cost' => $campaign->panels->sum(
                    fn ($p) => $p->target_completes * $p->cpi
                )
            ]
        ]);
    }

    /* =====================================================
     * LAUNCH CAMPAIGN
     * ===================================================== */
    public function launch($id)
    {
        $campaign = SurveyCampaign::findOrFail($id);

        if ($campaign->panels()->count() === 0 || !$campaign->redirect) {
            return response()->json([
                'status' => false,
                'message' => 'Campaign incomplete'
            ], 422);
        }

        $campaign->update(['status' => 'active']);

        return response()->json([
            'status' => true,
            'message' => 'Campaign launched'
        ]);
    }

    /* =====================================================
     * SHOW SINGLE CAMPAIGN
     * ===================================================== */

public function show(Request $request)
{
    $campaignId = $request->query('campaign_id'); // GET param

    if (!$campaignId) {
        return response()->json([
            'success' => false,
            'message' => 'campaign_id is required'
        ], 422);
    }

    $campaign = SurveyCampaign::with([
        'country:id,name',
        'language:id,name',
        'panels.provider',
        'redirect'
    ])->find($campaignId);

    if (!$campaign) {
        return response()->json([
            'success' => false,
            'message' => 'Survey not found'
        ], 404);
    }

    return response()->json([
        'success' => true,
        'data' => [
            'id' => $campaign->id,
            'name' => $campaign->name,
            'loi' => $campaign->loi,
            'ir' => $campaign->ir,
            'total_completes' => $campaign->total_completes,
            'status' => $campaign->status,
            'country' => $campaign->country?->name,
            'language' => $campaign->language?->name,
        ]
    ]);
}

    /* =====================================================
     * SOFT DELETE
     * ===================================================== */
    public function destroy($campaignId)
    {
        SurveyCampaign::findOrFail($campaignId)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Campaign moved to trash'
        ]);
    }

    /* =====================================================
     * TRASH LIST
     * ===================================================== */
    public function trash()
    {
        return response()->json([
            'status' => true,
            'data' => SurveyCampaign::onlyTrashed()->get()
        ]);
    }

    /* =====================================================
     * RESTORE
     * ===================================================== */
    public function restore($campaignId)
    {
        SurveyCampaign::withTrashed()->findOrFail($campaignId)->restore();

        return response()->json([
            'status' => true,
            'message' => 'Campaign restored'
        ]);
    }

    /* =====================================================
     * FORCE DELETE
     * ===================================================== */ 
    public function forceDelete($campaignId)
    {
        SurveyCampaign::withTrashed()->findOrFail($campaignId)->forceDelete();

        return response()->json([
            'status' => true,
            'message' => 'Campaign permanently deleted'
        ]);
    }
    public function language()
    {
        return response()->json([
            'status' => true,
            'data' => Language::all()
        ]);
    }public function country()
    {
        return response()->json([
            'status' => true,
            'data' => Country::all()
        ]);
    }

    public function getQuestionOptions()
    {
       //d('API HIT');
        $questions = SurveyQualificationQuestion::with([
                'options' => function ($q) {
                    $q->whereNull('deleted_at')
                    ->select('opt_id', 'qs_id', 'option_value');
                }
            ])
            ->whereNull('deleted_at')
            ->select('qs_id', 'question', 'label', 'type')
            ->get();

        $data = $questions->map(function ($q) {
            return [
                'qs_id' => $q->qs_id,
                'question' => $q->label ?? $q->question, // ✅ FIX
                'type' => $q->type,
                'options' => $q->options->map(function ($opt) {
                    return [
                        'opt_id' => $opt->opt_id,   
                        'option_value' => $opt->option_value
                    ];
                })->values()
            ];
        })->values();

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }



}   



