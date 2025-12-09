<?php

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
}
