<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Backend\RoomBookingHistory;
use App\Models\Backend\ProductPurchase;
use App\Models\Backend\ManageSale;

class ManageSaleController extends Controller
{

    public function index(){

        return view('backend.inventory.productdistribution');
    }

 public function getselectcustomer()
    {
        $customers = RoomBookingHistory::where('status', 0)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'phone'])
            ->groupBy('full_name')
            ->map(function($group) {
                return $group->last(); 
            })
            ->values();
        return response()->json($customers);
    }
 
    /**
   
     */
    public function getselectproductSale()
    {

        $products = ProductPurchase::with('supplier')->where('available_quantity', '>', 0)
            ->orderBy('id')
            ->get(['id', 'product_name', 'single_price', 'available_quantity', 'total_price_available', 'memo_number','supplier_id']);
        return response()->json($products);
    }
 
    /**
     * POST product-sale-store
     */



    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'       => 'required|exists:room_booking_histories,id',
            'supplier_id'       => 'required|exists:product_purchases,supplier_id', 
            'purchase_date'     => 'required|date',
            'memo_number'       => 'nullable|string|max:100',
            'product_id'        => 'required|exists:product_purchases,id',
            'single_price'      => 'required|numeric|min:0',
            'customer_quantity' => 'required|integer|min:1',
            'total_price'       => 'required|numeric|min:0',
        ]);

        $purchase = ProductPurchase::findOrFail($validated['product_id']);

        if ($purchase->available_quantity < $validated['customer_quantity']) {
            return response()->json([
                'message' => "Insufficient stock for: {$purchase->product_name}. Available: {$purchase->available_quantity}",
            ], 422);
        }

        $saleAmount = floatval($validated['single_price']) * intval($validated['customer_quantity']);
        $purchase->available_quantity    = $purchase->available_quantity - $validated['customer_quantity'];
        $purchase->total_price_available = $purchase->total_price_available - $saleAmount;
        $purchase->customer_id           = $validated['customer_id'];
        $purchase->supplier_id           = $validated['supplier_id'];
        $purchase->save();

        $manageproductsale = new ManageSale();
        $manageproductsale->purchase_date         = $validated['purchase_date'];
        $manageproductsale->purchase_id           = $purchase->id;
        $manageproductsale->product_name          = $purchase->product_name;
        $manageproductsale->customer_id           = $validated['customer_id'];  
        $manageproductsale->supplier_id           = $validated['supplier_id'];  
        $manageproductsale->single_price          = $validated['single_price'];
        $manageproductsale->customer_quantity     = $validated['customer_quantity'];
        $manageproductsale->total_price_available = $validated['single_price'] * $validated['customer_quantity'];
        $manageproductsale->save();

        return response()->json([
            'message'                => 'Product sale created successfully',
            'product_id'             => $purchase->id,
            'available_quantity'     => $purchase->available_quantity,
            'total_price_available'  => $purchase->total_price_available,
        ], 201);
    }



      public function customersalelist(Request $request)
    {
        $perPage    = $request->input('per_page', 50);
        $search     = $request->input('search', '');
        $startDate  = $request->input('start_date', '');
        $endDate    = $request->input('end_date', '');
        $supplierId = $request->input('supplier_id', '');

        $query = ManageSale::with(['customer','supplier'])
            ->when($search, function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%");
            })
            ->when(!empty($supplierId), function ($q) use ($supplierId) {
                $q->where('customer_id', $supplierId); //  supplier_id → customer_id
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

        $grandTotal          = (clone $query)->sum('total_price_available');
        $grandTotalAvailable = (clone $query)->sum('total_price_available');
        $productstock        = $query->paginate($perPage);

        return response()->json([
            'status'                => 'success',
            'productstock'          => $productstock->items(),
            'total'                 => $productstock->total(),
            'from'                  => $productstock->firstItem() ?? 1,
            'per_page'              => $productstock->perPage(),
            'last_page'             => $productstock->lastPage(),
            'current_page'          => $productstock->currentPage(),
            'grand_total'           => $grandTotal,
            'grand_total_available' => $grandTotalAvailable,
        ]);
    }

    public function getcustomer()
    {
        $suppliers = RoomBookingHistory::select('id', 'full_name')
            ->orderBy('full_name', 'asc')
            ->get()
            ->groupBy('full_name')
            ->map(function ($group) {
                return $group->last();
            })
            ->values();

        return response()->json([
            'status' => 'success',
            'data'   => $suppliers,
        ]);
    }
    

    public function destroy($id)
    {
        $sale = ManageSale::findOrFail($id);
        $sale->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }

}
