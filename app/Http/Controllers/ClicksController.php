<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Survey;

use App\Models\Clicks;
use App\Models\Quota;
use Illuminate\Support\Str;

class ClicksController extends Controller
{
 public function getAvailableSurvey(Request $request)
    {
        // ✅ GET params validation
        $request->validate([
            'age' => 'required|integer',
            'gender' => 'required|in:male,female',
        ]);

        // GET se values
        $age = (string) $request->query('age');
        $genderPrecode = $request->query('gender') === 'male' ? '1' : '2';

        // 🔹 Active quotas + survey
        $quotas = Quota::where('is_active', true)
            ->with('survey')
            ->get();

        $validSurveys = [];

        foreach ($quotas as $quota) {

            if (!$quota->survey || $quota->survey->status !== 'live') {
                continue;
            }

            $ageMatched = false;
            $genderMatched = false;

            foreach ($quota->conditions as $condition) {

                // AGE
                if (
                    isset($condition['QuestionID']) &&
                    $condition['QuestionID'] == 42 &&
                    in_array($age, $condition['PreCodes'])
                ) {
                    $ageMatched = true;
                }

                // GENDER
                if (
                    isset($condition['QuestionID']) &&
                    $condition['QuestionID'] == 43 &&
                    in_array($genderPrecode, $condition['PreCodes'])
                ) {
                    $genderMatched = true;
                }
            }

            if ($ageMatched && $genderMatched) {
                $validSurveys[$quota->survey->id] = $quota->survey;
            }
        }

        return response()->json([
            'status' => true,
            'count' => count($validSurveys),
            'data' => array_values($validSurveys)
        ]);
    }
      public function clickPost(Request $request)
    {
        // ✅ Validation
        $request->validate([
            'survey_id' => 'required|integer',
            'quota_id'  => 'required|integer',
            'u_id'   => 'required|integer',
        ]);

        // 🔹 Fetch quota
        $quota = Quota::where('id', $request->quota_id)
            ->where('is_active', true)
            ->first();

        if (!$quota) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid quota'
            ], 422);
        }

        // 🔹 Safety check: quota belongs to same survey
        if ($quota->survey_id != $request->survey_id) {
            return response()->json([
                'status' => false,
                'message' => 'Quota does not belong to given survey'
            ], 422);
        }

        // 🔹 Generate unique uu_id
        $uuId = 'CLK-' . now()->format('YmdHis') . '-' . Str::uuid();

        // 🔹 Save click
        $click = Clicks::create([
            'survey_id' => $request->survey_id,
            'quota_id'  => $request->quota_id,
            'u_id'   => $request->u_id,
            'uu_id'     => $uuId,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Click recorded successfully',
            'data' => $click
        ]);
    }
}

