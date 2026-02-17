<?php


namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SurveyPanelProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\region;

class SurveyPanelProviderApiController extends Controller
{
    /* =====================================
       🔥 ONLY PUBLIC API
    ===================================== */
    public function store(Request $request)
    {
        $this->validateRequest($request);

        DB::transaction(function () use ($request) {
            $this->saveProvider($request);
        });

        return response()->json([
            'status'  => true,
            'message' => 'Panel Provider saved successfully'
        ], 201);
    }

    /* =====================================
       🔒 PRIVATE FUNCTIONS
    ===================================== */

    // Step 1 + 2 + 3 validation
    private function validateRequest(Request $request): void
    {
        $request->validate([
            // BASIC
            'name'        => 'required|string',
            'panel_id'    => 'required|string',
            'region_id'  => 'required|exists:regions,id',

            // REDIRECTS
            'success_url'      => 'required|url',
            'terminate_url'    => 'required|url',
            'overquota_url'    => 'required|url',
            'quality_fail_url' => 'required|url',
        ]);
    }

    // FINAL SAVE
    private function saveProvider(Request $request): void
    {
        SurveyPanelProvider::create([
            'name'             => $request->name,        
            'panel_id'         => $request->panel_id,    
            'region_id'       => $request->region_id,  

            'success_url'      => $request->success_url,
            'terminate_url'    => $request->terminate_url,
            'overquota_url'    => $request->overquota_url,
            'quality_fail_url' => $request->quality_fail_url,

            'status' => 'active'
        ]);
    }

      public function allRegion()
{
    $regions = Region::select('id', 'region_name')->get();

    return response()->json([
        'status' => true,
        'data'   => $regions
    ]);
}
     public function index()
    {
       // dd("njfnj");
        $providers = SurveyPanelProvider::with('region')
            ->whereNull('deleted_at')
            ->get()
            ->map(function ($provider) {

                return [
                    'id'            => $provider->id,
                    'provider_name' => ucfirst($provider->name),
                    'panel_id'      => strtoupper($provider->panel_id),
                    'region'        => optional($provider->region)->region_name ?? 'Global',
                    'status'        => ucfirst($provider->status),
                    'api_health'    => $this->apiHealth($provider),
                ];
            });

        return response()->json([
            'status' => true,
            'data'   => $providers
        ]);
    }

    /* 🔒 PRIVATE HELPER */
    private function apiHealth($provider): string
    {
        // example logic (customize later)
        if ($provider->status !== 'active') {
            return 'Unknown';
        }

        // fake health check (can be replaced with real ping)
        return in_array($provider->name, ['PureSpectrum'])
            ? 'Degraded'
            : 'Healthy';
    }
    public function update(Request $request, SurveyPanelProvider $provider)
{
   // $this->validateRequest($request);

    DB::transaction(function () use ($request, $provider) {
        $this->updateProvider($request, $provider);
    });

    return response()->json([
        'status'  => true,
        'message' => 'Panel Provider updated successfully'
    ], 200);
}
private function updateProvider(Request $request, SurveyPanelProvider $provider): void
{
    $provider->update([
        'name'             => $request->name,
        'panel_id'         => $request->panel_id,
        'country_id'       => $request->country_id,

        'success_url'      => $request->success_url,
        'terminate_url'    => $request->terminate_url,
        'overquota_url'    => $request->overquota_url,  
        'quality_fail_url' => $request->quality_fail_url,
    ]);
}
 public function deletePanel($id)
{
    $provider = SurveyPanelProvider::find($id);

    if (! $provider) {
        return response()->json([
            'message' => 'Provider not found'
        ], 404);
    }

    $provider->delete();

    return response()->json([
        'message' => 'Provider deleted successfully'
    ], 200);
}



}
