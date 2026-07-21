<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Backend\Supplier;
use App\Models\Backend\Product;
use App\Models\Backend\ProductPurchase;



class ProductStockController extends Controller
{
    public function index(){
    return view('backend.inventory.productstock');
   }

 public function getselectsupplier()
    {
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);
        return response()->json($suppliers);
    }

    public function getselectproduct()
    {
        $products = Product::orderBy('name')->get(['id', 'name', 'buy_price']);
        return response()->json($products);
    }


   public function store(Request $request)

    {

        $request->validate([
            'purchase_date' => 'required|date',
            'memo_number'   => 'nullable|string|max:255',
            'supplier_id'   => 'required|exists:suppliers,id',
            'product_id'    => 'required|exists:products,id',
            'product_name'  => 'required|string|max:255',
            'buy_price'     => 'required|numeric|min:0',
            'quantity'      => 'required|integer|min:1',
            'discount'      => 'nullable|numeric|min:0',
            'total_price'   => 'required|numeric|min:0',
        ]);
        $purchase = ProductPurchase::create([
            'purchase_date'         => $request->purchase_date,
            'memo_number'           => $request->memo_number,
            'supplier_id'           => $request->supplier_id,
            'product_id'            => $request->product_id,
            'product_name'          => $request->product_name,
            'single_price'          => $request->buy_price,
            'quantity'              => $request->quantity,
            'available_quantity'    => $request->quantity,
            'discount'              => $request->discount ?? 0,
            'total_price'           => $request->total_price,
            'total_price_available' => $request->total_price,
        ]);
        return response()->json([
            'data'    => $purchase,
        ], 201);
    }




public function QuantityStore(Request $request)
{
    $request->validate([
        'product_id' => 'required|exists:product_purchases,id',
        'quantity'   => 'required|integer|min:1',
    ]);

    $purchase = ProductPurchase::findOrFail($request->product_id);
    $addQty      = intval($request->quantity);
    $newQty      = intval($purchase->quantity) + $addQty;
    $newAvailQty = intval($purchase->available_quantity) + $addQty;
    $singlePrice = floatval($purchase->single_price);

    ProductPurchase::where('id', $request->product_id)->update([
        'quantity'              => $newQty,
        'available_quantity'    => $newAvailQty,
        'total_price'           => $singlePrice * $newQty,
        'total_price_available' => $singlePrice * $newAvailQty,
    ]);

    return response()->json([
        'message' => 'Quantity added successfully',
        'data'    => ProductPurchase::find($request->product_id),
    ], 200);
}




 public function getsupplier()
    {
        $suppliers = Supplier::select('id', 'name')
            ->orderBy('name', 'asc')
            ->get();
        return response()->json([
            'status' => 'success',
            'data'   => $suppliers,
        ]);
    }



   public function Productpurchaselist(Request $request)
    {
        $perPage    = $request->input('per_page', 50);
        $search     = $request->input('search', '');
        $startDate  = $request->input('start_date', '');
        $endDate    = $request->input('end_date', '');
        $supplierId = $request->input('supplier_id', '');
        $query = ProductPurchase::with('supplier')
    ->where('available_quantity', '>', 0)

    ->when($search, function ($q) use ($search) {
        $q->where('product_name', 'like', "%{$search}%")
          ->orWhere('memo_number', 'like', "%{$search}%");
    })
    ->when(!empty($supplierId), function ($q) use ($supplierId) {
        $q->where('supplier_id', $supplierId);
    })
    ->when(!empty($startDate) && !empty($endDate), function ($q) use ($startDate, $endDate) {
        $q->whereBetween('purchase_date', [$startDate, $endDate]);
    })
    ->when(!empty($startDate) && empty($endDate), function ($q) use ($startDate) {
        $q->whereDate('purchase_date', '>=', $startDate);
    })
    ->when(empty($startDate) && !empty($endDate), function ($q) use ($endDate) {
        $q->whereDate('purchase_date', '<=', $endDate);
    })
    ->orderBy('id', 'desc');
        $grandTotal          = (clone $query)->sum('total_price');
        $grandTotalAvailable = (clone $query)->sum('total_price_available');
        $grandTotalQuantity          = (clone $query)->sum('quantity');          // এটা যোগ করুন
$grandTotalAvailableQuantity = (clone $query)->sum('available_quantity'); 

        $productstock = $query->paginate($perPage);

      return response()->json([
    'status'                        => 'success',
    'productstock'                  => $productstock->items(),
    'total'                         => $productstock->total(),
    'from'                          => $productstock->firstItem() ?? 1,
    'per_page'                      => $productstock->perPage(),
    'last_page'                     => $productstock->lastPage(),
    'current_page'                  => $productstock->currentPage(),
    'grand_total'                   => $grandTotal,
    'grand_total_available'         => $grandTotalAvailable,
    'grand_total_quantity'          => $grandTotalQuantity,          // নতুন
    'grand_total_available_quantity'=> $grandTotalAvailableQuantity, // নতুন
]);
    }


      public function destroy($id)
    {
        $purchase = ProductPurchase::findOrFail($id);
        $purchase->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
    

}
