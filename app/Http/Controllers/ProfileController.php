<?php

namespace App\Http\Controllers;

use App\Models\Income;
use Illuminate\Http\Request;
use App\Models\Education;
use App\Models\Profile;

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


    public function saveProfile(Request $request)
    {
 
        $request->validate([
            'age'           => 'required|integer|min:1',
            'gender'        => 'required|in:male,female,other',
            'state'         => 'required|string|max:255',
            'education'     =>  'required|integer',
            'income'        => 'required|integer',
            'zipcode'       => 'nullable|max:10',
            "city"          => 'nullable|string|max:100',
            "country"       => 'nullable|string|max:100',
        ]);

        $user = $request->user(); 

       
        $profile = Profile::updateOrCreate(
            ['u_id' => $user->id], 
            [
                'age'           => $request->age,
                'gender'        => $request->gender,
                'state'         => $request->state,
                'education'     => $request->education,
                'income'        => $request->income,
                'zip_code'       => $request->zipcode,
                'city'          => $request->city,
                'country'       => $request->country,
            ]
        );

        return response()->json([
            'status'  => true,
            'message' => 'Profile saved successfully',
            'data'    => $profile
        ]);
    }
}
