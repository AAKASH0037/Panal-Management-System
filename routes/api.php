<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SurveyController;

Route::post('/survey/save', [SurveyController::class, 'saveSurvey']);
Route::get('/survey/{id}', [SurveyController::class, 'viewSurvey']);
Route::get('/surveys', [SurveyController::class, 'listSurveys']);

// Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/profile', function(Request $request) {
        return $request->user();
    });
});
