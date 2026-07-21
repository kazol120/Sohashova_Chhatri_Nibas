<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Backend\BrandCategory;
use App\Models\Backend\Brand;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller
{




    public function index(){

        return view('backend.inventory.brand');
    }




   


    public function getselectbrandCategory()
    {
        $brands = BrandCategory::select('id', 'name')
            ->orderBy('name', 'asc')
            ->get();
        return response()->json([
            'status' => 'success',
            'data'   => $brands,
        ]);
    }

        public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name',
        ]);

        $brand = Brand::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Brand created successfully',
            'data'    => $brand,
        ]);
    }



public function getBrand(Request $request)
{
    $perPage = (int) $request->get('per_page', 10);
    $search = trim($request->get('search', ''));

    $query = Brand::with('category');

    if ($search !== '') {
        $query->where('name', 'like', "%{$search}%")
              ->orWhereHas('category', function ($q) use ($search) {
                  $q->where('name', 'like', "%{$search}%");
              });
    }

    $data = $query->latest()->paginate($perPage);

    return response()->json($data);
}

    public function update(Request $request ,$id)
{
    $brand = Brand::findOrFail($id);

    $request->validate([
        'name' => 'required|string|max:255|unique:brands,name,' . $id,
       
    ]);

    $brand->update([
        'name' => $request->name,
       
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Brand updated successfully.',
        'data' => $brand,
    ]);
}

    
   
   public function destroy($id)
{
    $brand = Brand::findOrFail($id);
    $brand->delete();

    return response()->json([
        'status' => true,
        'message' => 'Brand deleted successfully.',
    ]);
}




}
