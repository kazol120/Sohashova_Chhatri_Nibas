<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Backend\BrandCategory;
use Illuminate\Support\Facades\Validator;



class BrandCategoryController extends Controller

{

     public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $menu = BrandCategory::create([
            'name' => $request->name
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Brand Category created successfully',
            'data' => $menu
        ]);
    }
    


    public function index(){
        return view('backend.inventory.brandcategory');
    }


    public function GetBrandCategory(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $search = trim($request->get('search', ''));

        $query = BrandCategory::query();

        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
                 
        }
        $data = $query->latest()->paginate($perPage);
        return response()->json($data);
    }

  public function update(Request $request, $id) {
    $brandcategoryupdate = BrandCategory::findOrFail($id);
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
    $brandcategoryupdate->name = $request->name;
    $brandcategoryupdate->save();
    return response()->json([
        'status'  => true,
        'message' => 'Brand Category updated successfully.',
        'data'    => $brandcategoryupdate,
    ]);
    }

    public function destroy($id){
        
        $brandcategorydelete = BrandCategory::findOrFail($id);
        $brandcategorydelete->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Brand Category deleted successfully.',
        ]);
    }




 
}
