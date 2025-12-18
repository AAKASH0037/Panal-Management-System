<?php

namespace App\Http\Controllers;
use App\Models\Income;
use Illuminate\Http\Request;
use App\Models\Education;

class ProfileController extends Controller
{
     public function getEducation()
    {
        $education = Education::select('id', 'education')->get();

        return response()->json([
            'status' => true,
            'message' => 'Education list fetched successfully',
            'data' => $education
        ]);
    }
     public function getIncome()
    {
        $income = Income::select('id', 'income')->get();

        return response()->json([
            'status' => true,
            'message' => 'Income list fetched successfully',
            'data' => $income
        ]);
    }
}
