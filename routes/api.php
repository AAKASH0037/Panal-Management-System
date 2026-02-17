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
use App\Http\Controllers\CampaignReviewApiController;
use App\Http\Controllers\SurveyPanelProviderApiController;

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
   Route::get('/campaigns/{id}/review', [CampaignReviewApiController::class, 'review']);
Route::post('/campaigns/{id}/launch', [CampaignReviewApiController::class, 'launch']);

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

  Route::patch(
        '{campaignId}/panels/{panelProviderId}/status',
        [PanelController::class, 'togglePanelStatus']
    );

    Route::post(
        '{campaignId}/final-submit',
        [PanelController::class, 'finalSubmit']
    );

    Route::put(
        '{campaignId}/panels/{panelProviderId}/final-update',
        [PanelController::class, 'finalUpdate']
    );
Route::delete(
    '{campaignId}/panels/{panelProviderId}',
    [PanelController::class, 'finalDelete']
);
    /* =========================
     * PANEL ROUTES
     * ========================= */
   



  

    /* =========================
     * CAMPAIGN CORE
     * ========================= */
    Route::get('/basic/{id}', [SurveyCampaignApiController::class, 'basicCampaignShow']);
    Route::get('/', [SurveyCampaignApiController::class, 'index']);
    Route::post('/', [SurveyCampaignApiController::class, 'storeBasics']);
    Route::delete('{id}', [SurveyCampaignApiController::class, 'destroy']);
    Route::post('{id}/restore', [SurveyCampaignApiController::class, 'restore']);
    Route::delete('{id}/force', [SurveyCampaignApiController::class, 'forceDelete']);
});
// work   in   panel  provider  
Route::post(
    '/panel-provider/save',
    [SurveyPanelProviderApiController::class, 'store']
);
Route::put('/survey-panel-providers/{provider}', [SurveyPanelProviderApiController::class, 'update']);
Route::get('/regions', [SurveyPanelProviderApiController::class, 'allRegion']);
// routes/api.php
Route::get('/panel-providers_all', [SurveyPanelProviderApiController::class, 'index']);
Route::delete('/survey-panel-providers/{provider}', [SurveyPanelProviderApiController::class, 'deletePanel']);


