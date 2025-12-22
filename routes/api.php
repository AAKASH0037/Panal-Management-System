<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\QualificationController;
use App\Http\Controllers\QuotaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SupplierApiController;
use App\Http\Controllers\ClicksController;

// Route::post('/profile_save', [ProfileController::class, 'saveProfile']);
Route::get('/available-surveys', [ClicksController::class, 'getAvailableSurvey']);
Route::post('/click-post', [ClicksController::class, 'clickPost']);
Route::post('/survey/save', [SurveyController::class, 'saveSurvey']);
Route::get('/survey/{id}', [SurveyController::class, 'viewSurvey']);
Route::get('/surveys', [SurveyController::class, 'listSurveys']);
Route::delete('/survey/delete/{id}', [SurveyController::class, 'deleteSurvey']);
Route::get('/income', [ProfileController::class, 'getIncome']);
Route::get('/education', [ProfileController::class, 'getEducation']);
Route::post('/qualification', [QualificationController::class, 'store']);
Route::get('/qualification', [QualificationController::class, 'index']);
Route::get('/qualification/{id}', [QualificationController::class, 'show']);
Route::put('/qualification/{id}', [QualificationController::class, 'update']);
Route::delete('/qualification/{id}', [QualificationController::class, 'destroy']);

Route::prefix('quotas')->group(function () {

    Route::get('/', [QuotaController::class, 'index']);        // List all
    Route::post('/', [QuotaController::class, 'store']);       // Create
    Route::get('/{id}', [QuotaController::class, 'show']);     // Show
    Route::put('/{id}', [QuotaController::class, 'update']);   // Update
    Route::delete('/{id}', [QuotaController::class, 'destroy']); // Soft Delete




    

    // Soft Delete Extras
});

//  

// List





Route::get('/suppliers', [SupplierApiController::class, 'index']);
Route::post('/suppliers/store', [SupplierApiController::class, 'store']);
Route::get('/suppliers/show/{id}', [SupplierApiController::class, 'show']);
Route::post('/suppliers/update/{id}', [SupplierApiController::class, 'update']);

// Soft delete
Route::delete('/suppliers/delete/{id}', [SupplierApiController::class, 'delete']);



// Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/profile_save', [ProfileController::class, 'saveProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/profile', function(Request $request) {
        return $request->user();
    });
});
