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
use App\Http\Controllers\SurveyCampaignApiController;
use App\Http\Controllers\PanelController;

// Route::post('/profile_save', [ProfileController::class, 'saveProfile']);
// Route::get('/available-surveys', [ClicksController::class, 'getAvailableSurvey']);
// Route::post('/click-post', [ClicksController::class, 'clickPost']);
// Route::post('/survey/save', [SurveyController::class, 'saveSurvey']);
// Route::get('/survey/{id}', [SurveyController::class, 'viewSurvey']);
// Route::get('/surveys', [SurveyController::class, 'listSurveys']);
// Route::delete('/survey/delete/{id}', [SurveyController::class, 'deleteSurvey']);
// Route::get('/income', [ProfileController::class, 'getIncome']);
// Route::get('/education', [ProfileController::class, 'getEducation']);
// Route::post('/qualification', [QualificationController::class, 'store']);
// Route::get('/qualification', [QualificationController::class, 'index']);
// Route::get('/qualification/{id}', [QualificationController::class, 'show']);
// Route::put('/qualification/{id}', [QualificationController::class, 'update']);
// Route::delete('/qualification/{id}', [QualificationController::class, 'destroy']);

// Route::prefix('quotas')->group(function () {

//     Route::get('/', [QuotaController::class, 'index']);        // List all
//     Route::post('/', [QuotaController::class, 'store']);       // Create
//     Route::get('/{id}', [QuotaController::class, 'show']);     // Show
//     Route::put('/{id}', [QuotaController::class, 'update']);   // Update
//     Route::delete('/{id}', [QuotaController::class, 'destroy']); // Soft Delete




    

//     // Soft Delete Extras
// });

// //  

// // List





// Route::get('/suppliers', [SupplierApiController::class, 'index']);
// Route::post('/suppliers/store', [SupplierApiController::class, 'store']);
// Route::get('/suppliers/show/{id}', [SupplierApiController::class, 'show']);
// Route::post('/suppliers/update/{id}', [SupplierApiController::class, 'update']);

// // Soft delete
// Route::delete('/suppliers/delete/{id}', [SupplierApiController::class, 'delete']);



// // Public Routes
// Route::post('/register', [AuthController::class, 'register']);
// Route::post('/login',    [AuthController::class, 'login']);

// Route::middleware('auth:sanctum')->group(function () {
//     Route::post('/profile_save', [ProfileController::class, 'saveProfile']);
//     Route::post('/logout', [AuthController::class, 'logout']);

//     Route::get('/profile', function(Request $request) {
//         return $request->user();
//     });
// });
// new  design code 


   Route::get('/countries', [SurveyCampaignApiController::class, 'country']);
    Route::get('/languages', [SurveyCampaignApiController::class, 'language']);
 Route::get('/panel-providers', [PanelController::class, 'getAllPanels']);
  Route::get('/survey/question-options', [SurveyCampaignApiController::class, 'getQuestionOptions']);

  Route::get(
    '/campaigns/{campaignId}/panels',
    [PanelController::class, 'getCampaignPanels']
);
Route::prefix('survey/campaigns')->group(function () {

    /* =========================
     * STATIC ROUTES (FIRST)
     * ========================= */
    Route::get('campaign_show', [SurveyCampaignApiController::class, 'show']);
    Route::get('trash/list', [SurveyCampaignApiController::class, 'trash']);

    /* =========================
     * FINAL SUBMIT / UPDATE (MOST SPECIFIC)
     * ========================= */
    Route::post(
        '{campaignId}/final-submit',
        [PanelController::class, 'finalSubmit']
    );

    Route::put(
        '{campaignId}/panels/{panelProviderId}/final-update',
        [PanelController::class, 'finalUpdate']
    );

    /* =========================
     * PANEL ROUTES
     * ========================= */
    Route::post(
        '{campaignId}/panels',
        [PanelController::class, 'storePanels']
    );

    Route::put(
        '{campaignId}/panels/{providerId}',
        [PanelController::class, 'updatePanel']
    );

    /* =========================
     * CAMPAIGN CORE
     * ========================= */
    Route::get('/', [SurveyCampaignApiController::class, 'index']);
    Route::post('/', [SurveyCampaignApiController::class, 'storeBasics']);

    Route::post('{id}/redirects', [SurveyCampaignApiController::class, 'storeRedirects']);
    Route::get('{id}/review', [SurveyCampaignApiController::class, 'review']);
    Route::post('{id}/launch', [SurveyCampaignApiController::class, 'launch']);

    Route::delete('{id}', [SurveyCampaignApiController::class, 'destroy']);
    Route::post('{id}/restore', [SurveyCampaignApiController::class, 'restore']);
    Route::delete('{id}/force', [SurveyCampaignApiController::class, 'forceDelete']);
});
