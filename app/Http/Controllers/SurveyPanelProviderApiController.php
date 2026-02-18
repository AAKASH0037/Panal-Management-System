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
   // dd("jdj");
   $validated = $this->validateRequest($request);
//dd($validated);
    DB::transaction(function () use ($validated) {
        $this->saveProvider($validated);
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
   private function validateRequest(Request $request): array
{

  // dd("mkfhjhj");
    return $request->validate([

        // BASIC SECTION
        'basic.name'     => 'required|string|max:255',
        'basic.panelId'  => 'required|string|max:255',
        'basic.region'   => 'required|exists:regions,id',

        // REDIRECT SECTION
        'redirects.successUrl'     => 'required|string',
        'redirects.terminateUrl'   => 'required|string',
        'redirects.overquotaUrl'   => 'required|string',
        'redirects.qualityFailUrl' => 'required|string',
    ]);
}


    // FINAL SAVE
 private function saveProvider(array $validated): void
{
  //  dd($validated);
    SurveyPanelProvider::create([
        // BASIC
        'name'      => $validated['basic']['name'],
        'panel_id'  => $validated['basic']['panelId'],
        'region_id' => $validated['basic']['region'],

        // REDIRECTS
        'success_url'      => $validated['redirects']['successUrl'],
        'terminate_url'    => $validated['redirects']['terminateUrl'],
        'overquota_url'    => $validated['redirects']['overquotaUrl'],
        'quality_fail_url' => $validated['redirects']['qualityFailUrl'],

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

public function individualProvider($provider)
{
    $provider = SurveyPanelProvider::find($provider);

    if (!$provider) {
        return response()->json([
            'status' => false,
            'message' => 'Provider not found'
        ], 404);
    }

    return response()->json([
        'basic' => [
            'name'    => $provider->name,
            'panelId' => $provider->panel_id,
            'region'  => $provider->region_id,  // ✅ Only ID
        ],
        'redirects' => [
            'successUrl'     => $provider->success_url,
            'terminateUrl'   => $provider->terminate_url,
            'overquotaUrl'   => $provider->overquota_url,
            'qualityFailUrl' => $provider->quality_fail_url,
        ],
        'status' => $provider->status
    ], 200);
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
   //    dd("jdhj");
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
        'name'      => $request->input('basic.name'),
        'panel_id'  => $request->input('basic.panelId'),
        'region_id' => $request->input('basic.region'),

        // REDIRECTS
        'success_url'      => $request->input('redirects.successUrl'),
        'terminate_url'    => $request->input('redirects.terminateUrl'),
        'overquota_url'    => $request->input('redirects.overquotaUrl'),
        'quality_fail_url' => $request->input('redirects.qualityFailUrl'),
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
