<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierApiController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => true,
            'data' => Supplier::all() // auto exclude deleted
        ]);
    }

    public function store(Request $request)
    {
        $supplier = Supplier::create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Supplier Added',
            'data' => $supplier
        ]);
    }

    public function show($id)
    {
        return response()->json([
            'status' => true,
            'data' => Supplier::findOrFail($id)
        ]);
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Supplier Updated',
            'data' => $supplier
        ]);
    }

    // SOFT DELETE
    public function delete($id)
    {
        Supplier::findOrFail($id)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Supplier Soft Deleted'
        ]);
    }

    // View trashed suppliers
    public function trashed()
    {
        return response()->json([
            'status' => true,
            'data' => Supplier::onlyTrashed()->get()
        ]);
    }

    // Restore supplier
    public function restore($id)
    {
        Supplier::onlyTrashed()->findOrFail($id)->restore();

        return response()->json([
            'status' => true,
            'message' => 'Supplier Restored'
        ]);
    }

    // Permanent delete
    public function forceDelete($id)
    {
        Supplier::onlyTrashed()->findOrFail($id)->forceDelete();
                                      
        return response()->json([
            'status' => true,
            'message' => 'Supplier Permanently Deleted'
        ]);
    }

    public function status($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->status = $supplier->status ? 0 : 1;
        $supplier->save();

        return response()->json([
            'status' => true,
            'current_status' => $supplier->status
        ]);
    }
}
