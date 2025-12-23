<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Qualification;
use App\Models\Question;


class QualificationController extends Controller
{
    // Create Qualification
    public function store(Request $req)
    {
        $req->validate([
            'Name' => 'required',
           'QuestionID' => 'required'

        ]);
           if (!Question::where('id', $req->QuestionID)->exists()) {
        return response()->json([
            'success' => false,
            'message' => 'Selected question does not exist'
        ], 422);
    }

        $qualification = Qualification::create([
            'name'                           => $req->Name,
            'question_id'                    => $req->QuestionID,
            'logical_operator'               => $req->LogicalOperator,
            'number_of_required_conditions'   => $req->NumberOfRequiredConditions,
            'is_active'                      => $req->IsActive,
            'pre_codes'                      => $req->PreCodes,
            'order'                          => $req->Order
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Qualification created',
            'data' => $qualification
        ]);
    }

    // List All
    public function index()
    {
        return response()->json(Qualification::all());
    }

    // View Single
    public function show($id)
    {
        $q = Qualification::find($id);

        if (!$q) {
            return response()->json(['error' => 'Not found'], 404);
        }

        return response()->json($q);
    }

    // Update
    public function update(Request $req, $id)
    {
        $q = Qualification::find($id);

        if (!$q) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $q->update([
            'name'                           => $req->Name ?? $q->name,
            'question_id'                    => $req->QuestionID ?? $q->question_id,
            'logical_operator'               => $req->LogicalOperator ?? $q->logical_operator,
            'number_of_required_conditions'   => $req->NumberOfRequiredConditions ?? $q->number_of_required_conditions,
            'is_active'                      => $req->IsActive ?? $q->is_active,
            'pre_codes'                      => $req->PreCodes ?? $q->pre_codes,
            'order'                          => $req->Order ?? $q->order
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Qualification updated',
            'data' => $q
        ]);
    }

    // Soft Delete
    public function destroy($id)
    {
        $q = Qualification::find($id);

        if (!$q) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $q->delete();

        return response()->json([
            'success' => true,
            'message' => 'Qualification soft deleted'
        ]);
    }

    // Restore Soft Deleted
}
