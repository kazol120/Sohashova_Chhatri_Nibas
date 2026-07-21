<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Backend\Brand;
use App\Models\Backend\BrandCategory;
use App\Models\Backend\Product;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{

    public function index(){

         return view('backend.inventory.product');
    }
    public function SelectGetBrand()
    {
        $brands = Brand::orderBy('name')->get(['id', 'name']);
        return response()->json($brands);
    }



    public function SelectGetBrandcategory()
    {
        $categories = BrandCategory::orderBy('name')->get(['id', 'name']);
        return response()->json($categories);
    }


    public function store(Request $request)
    {
        $request->validate([
            'date'              => 'required|date',
            'brand_id'          => 'required|exists:brands,id',
            'brand_category_id' => 'required|exists:brand_categories,id',
            'name'              => 'required|string|max:255',
            'buy_price'             => 'required|numeric|min:0',
            'sell_price'             => 'nullable|numeric|min:0',
        ]);
        $product = Product::create([
            'date'              => $request->date,
            'brand_id'          => $request->brand_id,
            'brand_category_id' => $request->brand_category_id,
            'name'              => $request->name,
            'buy_price'         => $request->buy_price,
            'sell_price'        => $request->sell_price,
        ]);
        return response()->json([
            'message' => 'Product created successfully',
            'data'    => $product->load(['brand', 'brandCategory']),
        ], 201);
    }

    public function getproductlist(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $search  = trim($request->get('search', ''));

        $query = Product::with(['brand', 'brandCategory']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('brand', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('brandCategory', function ($q3) use ($search) {
                      $q3->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $data = $query->latest()->paginate($perPage);
        return response()->json($data);
    }


    public function update(Request $request, $id)
{
    $request->validate([
        'date'                => 'required|date',
        'brand_id'            => 'required|exists:brands,id',
        'brand_category_id'   => 'required|exists:brand_categories,id',
        'name'                => 'required|string|max:255',
        'buy_price'           => 'required|numeric|min:0',
        'sell_price'          => 'nullable|numeric|min:0',
        'brand_name'          => 'nullable|string|max:255',
        'brand_category_name' => 'nullable|string|max:255',
    ]);

    DB::transaction(function () use ($request, $id) {

        $product = Product::findOrFail($id);

        if ($request->filled('brand_name')) {
            Brand::where('id', $request->brand_id)->update([
                'name' => $request->brand_name,
            ]);
        }

        if ($request->filled('brand_category_name')) {
            BrandCategory::where('id', $request->brand_category_id)->update([
                'name' => $request->brand_category_name,
            ]);
        }

        $product->update([
            'date'              => $request->date,
            'brand_id'          => $request->brand_id,
            'brand_category_id' => $request->brand_category_id,
            'name'              => $request->name,
            'buy_price'         => $request->buy_price,
            'sell_price'        => $request->sell_price,
        ]);
    });

    return response()->json([
        'status'  => true,
        'message' => 'Product, brand and category updated successfully',
    ]);
}

    // Product delete
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Product deleted successfully',
        ]);
    }
    

}
