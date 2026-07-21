<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Backend\Supplier;
use Illuminate\Support\Facades\Validator;



class SupplierContoller extends Controller

{

    public function index(){

        return view('backend.inventory.supplier');
    }


    public function store(Request $request)

    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $menu = Supplier::create([
            'name' => $request->name
        ]);

        return response()->json([
            'status' => true,
            'message' => 'supplier created successfully',
            'data' => $menu
        ]);
    }


  
      public function getsupplier(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $search = trim($request->get('search', ''));

        $query = Supplier::query();

        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
                 
        }

        $data = $query->latest()->paginate($perPage);

        return response()->json($data);
    }
    

   public function update(Request $request, $id) {
    $suplierdataupdate = Supplier::findOrFail($id);
    
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'message' => 'Validation error',
            'errors'  => $validator->errors(),
        ], 422);
    }

    $suplierdataupdate->name = $request->name;
    $suplierdataupdate->save();

    return response()->json([
        'status'  => true,
        'message' => 'Supplier updated successfully.',
        'data'    => $suplierdataupdate,
    ]);
}


    public function destroy($id){

        $supplierdatadelete = Supplier::findOrFail($id);

        $supplierdatadelete->delete();

        return response()->json([
            'status'  => true,
            'message' => 'supplier   deleted successfully.',
        ]);
    }




}
