<?php
/*
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Survey;

class SurveyController extends Controller
{
    // -----------------------------------------------
    // 1. Save OR Update Survey (Main PMS Logic)
    // -----------------------------------------------
    public function saveSurvey(Request $request)
    {
        // Validate required items
        $request->validate([
            'SurveyName'         => 'required',
            'Quota'              => 'required|integer',
            'ClientCPI'          => 'nullable|numeric',
            'ClientSurveyLiveURL'=> 'nullable|string',
            'StudyTypeID'        => 'nullable|string'
        ]);

        // Create or Update Logic
        $survey = Survey::updateOrCreate(
            [
                'provider_survey_id' => $request->StudyTypeID ?? null  // UNIQUE IDENTIFIER
            ],
            [
                'survey_name'         => $request->SurveyName,
                'quota_required'      => $request->Quota,
                'country_language_id' => $request->CountryLanguageID,
                'cpi'                 => $request->ClientCPI,
                'status'              => $request->SurveyStatusCode ?? '01',
                'live_url'            => $request->ClientSurveyLiveURL,
                'test_url'            => $request->TestRedirectURL,
                'incidence'           => $request->BidIncidence,
                'settings'            => $request->all()
            ]
        );

        // -----------------------------------------------
        // Response
        // -----------------------------------------------
        return response()->json([
            'success' => true,
            'message' => $survey->wasRecentlyCreated
                ? 'Survey Created Successfully!'
                : 'Survey Updated Successfully!',
            'data'    => $survey
        ]);
    }

    // -----------------------------------------------
    // 2. VIEW SINGLE SURVEY
    // -----------------------------------------------
    public function viewSurvey($id)
    {
        $survey = Survey::find($id);

        if (!$survey) {
            return response()->json([
                'success' => false,
                'message' => 'Survey not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $survey
        ]);
    }

    // -----------------------------------------------
    // 3. LIST ALL SURVEYS
    // -----------------------------------------------
    public function listSurveys()
    {
        return response()->json([
            'success' => true,
            'data'    => Survey::latest()->get()
        ]);
    }
    public function deleteSurvey($id)
{
    $survey = Survey::find($id);

    if (!$survey) {
        return response()->json([
            'success' => false,
            'message' => 'Survey not found'
        ], 404);
    }

    $survey->delete(); // SOFT DELETE

    return response()->json([
        'success' => true,
        'message' => 'Survey soft deleted successfully'
    ]);
}
}
*/



namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Survey;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;


class SurveyController extends Controller
{
    // -----------------------------------------------
    // SAVE / UPDATE SURVEY (PMS DECIDES SUPPLIER)
    // -----------------------------------------------
  public function saveSurvey(Request $request)
{
    // ✅ Fake supplier calls only for local/testing
    if (app()->environment(['local', 'testing'])) {
        Http::fake([
            '*' => Http::response(['status' => 'ok'], 200)
        ]);
    }

    $request->validate([
        'SurveyName' => 'required|string',
        'Quota'      => 'required|integer|min:1',
    ]);

    return DB::transaction(function () use ($request) {

        $clientQuota = (int) $request->Quota;
        $remaining   = $clientQuota;

        // PMS decides availability
        $sources = [
            'internal'      => 50,
            'cint'          => 1000,
            'eklavvya'      => 500,
            'purespectrum'  => 800,
        ];

        $allocation = [
            'internal_quota'     => 0,
            'cint_quota'         => 0,
            'eklavvya_quota'     => 0,
            'purespectrum_quota' => 0,
        ];

        foreach ($sources as $source => $available) {
            if ($remaining <= 0) break;

            $take = min($remaining, $available);
            $allocation[$source . '_quota'] = $take;
            $remaining -= $take;
        }

        if ($remaining > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Quota not available with PMS or suppliers'
            ], 422);
        }

        // 🔹 Find survey safely
        $survey = !empty($request->StudyTypeID)
            ? Survey::where('provider_survey_id', $request->StudyTypeID)->first()
            : null;

        $data = array_merge([
            'provider_survey_id' => $request->StudyTypeID,
            'survey_name'        => $request->SurveyName,
            'quota_required'     => $clientQuota,
            'country_language_id'=> $request->CountryLanguageID,
            'cpi'                => $request->ClientCPI,
            'status'             => $request->SurveyStatusCode ?? '01',
            'live_url'           => $request->ClientSurveyLiveURL,
            'test_url'           => $request->TestRedirectURL,
            'incidence'          => $request->BidIncidence,
            'settings'           => $request->all()
        ], $allocation);

        $isCreated = false;

        if ($survey) {
            $survey->update($data);
        } else {
            $survey = Survey::create($data);
            $isCreated = true;
        }

        // 🔹 Supplier calls (safe even without URLs)
        if ($allocation['cint_quota'] > 0) {
            $this->sendToCint($survey, $allocation['cint_quota']);
        }
        if ($allocation['eklavvya_quota'] > 0) {
            $this->sendToEklavvya($survey, $allocation['eklavvya_quota']);
        }
        if ($allocation['purespectrum_quota'] > 0) {
            $this->sendToPureSpectrum($survey, $allocation['purespectrum_quota']);
        }

        return response()->json([
            'success' => true,
            'message' => $isCreated
                ? 'Survey Created Successfully!'
                : 'Survey Updated Successfully!',
            'quota_given_to_client' => $clientQuota,
            'data' => $survey
        ]);
    });
}

    // -----------------------------------------------
    // SUPPLIER API CALLS
    // -----------------------------------------------
   private function sendToCint(Survey $survey, int $quota)
{
    Http::post(
        config('services.cint.url', 'http://dummy-url'),
        ['survey_id' => $survey->id, 'quota' => $quota]
    );
}

private function sendToEklavvya(Survey $survey, int $quota)
{
    Http::post(
        config('services.eklavvya.url', 'http://dummy-url'),
        ['survey_id' => $survey->id, 'quota' => $quota]
    );
}

private function sendToPureSpectrum(Survey $survey, int $quota)
{
    Http::post(
        config('services.purespectrum.url', 'http://dummy-url'),
        ['survey_id' => $survey->id, 'quota' => $quota]
    );
}

    // -----------------------------------------------
    // VIEW / LIST / DELETE (UNCHANGED)
    // -----------------------------------------------
    public function viewSurvey($id)
    {
        $survey = Survey::find($id);

        if (!$survey) {
            return response()->json([
                'success' => false,
                'message' => 'Survey not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $survey
        ]);
    }

    public function listSurveys()
    {
        return response()->json([
            'success' => true,
            'data' => Survey::latest()->get()
        ]);
    }

    public function deleteSurvey($id)
    {
        $survey = Survey::find($id);

        if (!$survey) {
            return response()->json([
                'success' => false,
                'message' => 'Survey not found'
            ], 404);
        }

        $survey->delete();

        return response()->json([
            'success' => true,
            'message' => 'Survey soft deleted successfully'
        ]);
    }
}
