<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\QualificationController;
Route::post('/survey/save', [SurveyController::class, 'saveSurvey']);
Route::get('/survey/{id}', [SurveyController::class, 'viewSurvey']);
Route::get('/surveys', [SurveyController::class, 'listSurveys']);
Route::delete('/survey/delete/{id}', [SurveyController::class, 'deleteSurvey']);


Route::post('/qualification', [QualificationController::class, 'store']);
Route::get('/qualification', [QualificationController::class, 'index']);
Route::get('/qualification/{id}', [QualificationController::class, 'show']);
Route::put('/qualification/{id}', [QualificationController::class, 'update']);
Route::delete('/qualification/{id}', [QualificationController::class, 'destroy']);



// Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/profile', function(Request $request) {
        return $request->user();
    });
});
