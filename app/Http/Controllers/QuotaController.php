<?php

namespace App\Http\Controllers;
use App\Models\Quota;
use App\Models\Qualification;
use Illuminate\Http\Request;

class QuotaController extends Controller
{
   public function index()
{
    return response()->json([
        'success' => true,
        'message' => 'Quota list fetched successfully',
        'data'    => Quota::all()
    ], 200);
}


    /**
     * Create new Quota
     */
    // public function store(Request $request)
    // {
    //     $data = $request->validate([
    //         'name'        => 'required|string|max:255',
    //         'quota'       => 'required|integer|min:0',
    //         'is_active'   => 'boolean',
    //         'conditions'  => 'nullable|array',
    //         'conditions.*.QuestionID' => 'required|integer',
    //         'conditions.*.PreCodes'   => 'nullable|array',
    //     ]);

    //     $quota = Quota::create($data);

    //     return response()->json($quota, 201);
    // }

    public function store(Request $request)
{
    $data = $request->validate([
        'name'        => 'required|string|max:255',
        'quota'       => 'required|integer|min:0',
        //'survey_id'   => 'required|integer',
        'is_active'   => 'boolean',
        'conditions' => 'required|array',

        // 🔥 CHANGE HERE
        'conditions.*.qualification_id' => 'required|exists:qualifications,id',
        'conditions.*.PreCodes'         => 'nullable|array',
    ]);

    $finalConditions = [];

    foreach ($data['conditions'] as $cond) {

        $qualification = Qualification::find($cond['qualification_id']);

        $finalConditions[] = [
            'qualification_id' => $qualification->id,
            'question_id'      => $qualification->question_id, // ✅ SAME QUESTION
            'pre_codes'        => $cond['PreCodes'] ?? $qualification->pre_codes,
        ];
    }

    $quota = Quota::create([
        'name'       => $data['name'],
        'quota'      => $data['quota'],
      //  'survey_id'  => $data['survey_id'],
        'is_active'  => $data['is_active'] ?? true,
        'conditions' => $finalConditions,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Quota created using Qualification questions',
        'data'    => $quota
    ], 201);
}

    /**
     * Show a single quota
     */
    public function show($id)
    {
        $quota = Quota::findOrFail($id);    
        return response()->json($quota);
    }

    /**
     * Update Quota
     */
    public function update(Request $request, $id)
    {
        $quota = Quota::findOrFail($id);
 
        $data = $request->validate([
            'name'        => 'string|max:255',
            'quota'       => 'integer|min:0',
            'is_active'   => 'boolean',
            'conditions'  => 'nullable|array',
            'conditions.*.QuestionID' => 'required_with:conditions|integer',
            'conditions.*.PreCodes'   => 'nullable|array',
        ]);

        $quota->update($data);

        return response()->json($quota);
    }

    /**
     * Soft Delete (moves to trash)
     */
    public function destroy($id)
    {
        $quota = Quota::findOrFail($id);
        $quota->delete(); // soft delete
        return response()->json(['message' => 'Quota soft deleted']);
    }

    /**
     * Show trashed items
     */
    public function trashed()
    {
        return Quota::onlyTrashed()->get();
    }

    /**
     * Restore soft deleted
     */
    public function restore($id)
    {
        $quota = Quota::onlyTrashed()->findOrFail($id);
        $quota->restore();
        return response()->json(['message' => 'Quota restored']);
    }

    

}
