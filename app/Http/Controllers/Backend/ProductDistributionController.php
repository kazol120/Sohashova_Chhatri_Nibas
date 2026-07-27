<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Backend\RoomBookingHistory;
use App\Models\Backend\ProductPurchase;
use App\Models\Backend\ManageSale;
use App\Models\Backend\Floor;
use App\Models\Backend\Room;
use App\Models\Backend\RoomSeat;
use App\Models\Backend\ProductDistribution;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;



class ProductDistributionController extends Controller
{

   public function floorSelect()
    {
        $floors = Floor::orderBy('id', 'desc')->get(['id', 'name']);
        return response()->json($floors);
    }

    public function floorRoomSelect($floor_id)
    {
        // শুধু সেই rooms দেখাবে যেগুলোর কমপক্ষে ১টি seat booked (status=1) আছে
        $rooms = Room::where('floor_id', $floor_id)
            ->whereHas('seats', function ($q) {
                $q->where('status', 1); // শুধু booked seat আছে এমন rooms
            })
            ->orderBy('room_no', 'asc')
            ->get(['id', 'floor_id', 'room_no']);
        return response()->json($rooms);
    }

    // Get seats for a specific room — শুধু booked (status=1) seats দেখাবে
    public function roomSeatsSelect($room_id)
    {
        $seats = RoomSeat::where('room_id', $room_id)
            ->where('status', 1) // শুধু booked/active seats
            ->orderBy('seat_no', 'asc')
            ->get(['id', 'room_id', 'seat_no', 'price', 'status']);
        return response()->json($seats);
    }

    // Get all customers (active bookings) in a specific room
    // room_booking_histories তে room_id নেই, JSON field এ roomnumber আছে
    public function roomCustomersSelect($room_id)
    {
        // Room এর room_no বের করি
        $room = Room::find($room_id);
        if (!$room) {
            return response()->json(['status' => false, 'data' => []]);
        }

        $roomNo = $room->room_no;

        // JSON field এ roomnumber এ room_no দিয়ে match করি (status=1 = active)
        $customers = RoomBookingHistory::where('status', 1)
            ->where(function ($q) use ($roomNo) {
                // JSON array এর মধ্যে roomnumber LIKE room_no% খুঁজি
                $q->where('floor_number_room_number_roomprice', 'like', '%"roomnumber":"' . $roomNo . '-%')
                  ->orWhere('floor_number_room_number_roomprice', 'like', '%"roomnumber": "' . $roomNo . '-%');
            })
            ->orderBy('full_name', 'asc')
            ->get(['id', 'full_name', 'phone']);

        return response()->json([
            'status' => true,
            'data'   => $customers,
        ]);
    }

    // Seat select করলে সেই seat এর active customer দেখাবে
    public function seatCustomersSelect($seat_id)
    {
        $seat = RoomSeat::find($seat_id);
        if (!$seat) {
            return response()->json(['status' => false, 'data' => [], 'message' => 'Seat not found']);
        }

        $room = Room::find($seat->room_id);
        if (!$room) {
            return response()->json(['status' => false, 'data' => [], 'message' => 'Room not found']);
        }

        // Booking এ roomnumber format: "roomNo-seatNo"
        $pattern = $room->room_no . '-' . $seat->seat_no;

        $customers = RoomBookingHistory::where('status', 1)
            ->where(function ($q) use ($pattern) {
                $q->where('floor_number_room_number_roomprice', 'like', '%"roomnumber":"' . $pattern . '"%')
                  ->orWhere('floor_number_room_number_roomprice', 'like', '%"roomnumber": "' . $pattern . '"%');
            })
            ->orderBy('full_name', 'asc')
            ->get(['id', 'full_name', 'phone']);

        return response()->json([
            'status'   => true,
            'data'     => $customers,
            'seat_no'  => $seat->seat_no,
            'room_no'  => $room->room_no,
            'pattern'  => $pattern,
        ]);
    }

    public function roomCustomerAutoLoad($room_number)
    {
        $customers = RoomBookingHistory::orderBy('id', 'desc')->get();

        $matchedCustomer = null;

        foreach ($customers as $customer) {
            $rooms = $customer->floor_number_room_number_roomprice;

            if (is_string($rooms)) {
                $rooms = json_decode($rooms, true);
            }

            if (!is_array($rooms)) {
                continue;
            }

            foreach ($rooms as $room) {
                if (isset($room['roomnumber']) && (string) $room['roomnumber'] === (string) $room_number) {
                    $matchedCustomer = [
                        'id'        => $customer->id,
                        'full_name' => $customer->full_name,
                    ];
                    break 2;
                }
            }
        }

        if (!$matchedCustomer) {
            return response()->json([
                'status'  => false,
                'message' => 'No customer found for this room',
                'data'    => null,
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => $matchedCustomer,
        ]);
    }

  public function store(Request $request)
{
    $validated = $request->validate([
        'customer_id'       => 'nullable|exists:room_booking_histories,id',
        'supplier_id'       => 'nullable',
        'room_id'           => 'required|exists:rooms,id',
        'floor_id'          => 'required|exists:floors,id',
        'seat_id'           => 'nullable|exists:room_seats,id',
        'purchase_date'     => 'required|date',
        'memo_number'       => 'nullable|string|max:100',
        'product_id'        => 'required|exists:product_purchases,product_id',
        'single_price'      => 'required|numeric|min:0',
        'customer_quantity' => 'required|integer|min:1',
        'total_price'       => 'required|numeric|min:0',
    ]);

    return DB::transaction(function () use ($validated) {

            $productId = $validated['product_id'];
            $needQty   = (int) $validated['customer_quantity'];

            $totalAvailable = ProductPurchase::where('product_id', $productId)
                    ->where('available_quantity', '>', 0)
                    ->lockForUpdate()
                    ->sum('available_quantity');

                if ($totalAvailable < $needQty) {
                    return response()->json([
                        'message' => "Insufficient stock. Available: {$totalAvailable}",
                    ], 422);
                }
                $purchases = ProductPurchase::where('product_id', $productId)
                    ->where('available_quantity', '>', 0)
                    ->orderBy('id', 'asc')
                    ->lockForUpdate()
                    ->get();
                $remainingNeed = $needQty;
                $lastDistribution = null;

                foreach ($purchases as $purchase) {

                    if ($remainingNeed <= 0) {
                        break;
                    }

                    $availableQty = (int) $purchase->available_quantity;
                    $takeQty = min($availableQty, $remainingNeed);
                    $saleAmount = $validated['single_price'] * $takeQty;

                    // stock update
                    $purchase->available_quantity = $availableQty - $takeQty;
                    $purchase->total_price_available = max(0, ($purchase->total_price_available ?? 0) - $saleAmount);
                    $purchase->save();

                    // distribution save
                    $lastDistribution = ProductDistribution::create([
                        'purchase_date'         => $validated['purchase_date'],
                        'memo_number'           => $validated['memo_number'] ?? null,
                        'purchase_id'           => $purchase->id,
                        'product_name'          => $purchase->product_name,
                        'floor_id'              => $validated['floor_id'],
                        'room_id'               => $validated['room_id'],
                        'seat_id'               => $validated['seat_id'] ?? null,
                        'customer_id'           => $validated['customer_id'] ?? null,
                        'supplier_id'           => $purchase->supplier_id,
                        'single_price'          => $validated['single_price'],
                        'customer_quantity'     => $takeQty,
                        'total_price_available' => $saleAmount,
                    ]);

                    $remainingNeed -= $takeQty;
                }

                $newTotalAvailable = ProductPurchase::where('product_id', $productId)
                    ->sum('available_quantity');

                return response()->json([
                    'status'                => true,
                    'message'               => 'Product distribution created successfully',
                    'data'                  => $lastDistribution,
                    'product_id'            => $productId,
                    'available_quantity'    => $newTotalAvailable,
                ], 201);
            });
        }


   public function index(){
        return view('backend.inventory.productdistribution');
    }


    public function getselectproductSale()
    {
        $products = ProductPurchase::selectRaw('
                product_id as id,
                product_id,
                MIN(product_name) as product_name,
                MIN(single_price) as single_price,
                SUM(available_quantity) as available_quantity,
                SUM(total_price_available) as total_price_available,
                MIN(memo_number) as memo_number,
                MIN(supplier_id) as supplier_id
            ')
            ->where('available_quantity', '>', 0)
            ->groupBy('product_id')
            ->orderBy('product_name')
            ->get();

        return response()->json($products);
    }


    public function todayDistributionList(Request $request)
{
    $perPage    = $request->input('per_page', 50);
    $search     = $request->input('search', '');
    $supplierId = $request->input('supplier_id', '');

    $query = ProductDistribution::with(['customer', 'floors', 'rooms', 'seat'])
        ->whereDate('purchase_date', Carbon::today())
        ->when($search, function ($q) use ($search) {
            $q->where('product_name', 'like', "%{$search}%");
        })
        ->when(!empty($supplierId), function ($q) use ($supplierId) {
            $q->where('customer_id', $supplierId);
        })
        ->orderBy('purchase_date', 'desc')
        ->orderBy('id', 'desc');

    $allRows = $query->get();
    $grouped = $allRows
        ->groupBy(function ($item) {
            return $item->purchase_date . '_' . $item->floor_id . '_' . $item->room_id . '_' . $item->seat_id . '_' . $item->customer_id;
        })
        ->values()
        ->map(function ($group) {
            $first = $group->first();
            $productGroups = $group->groupBy('product_name');
            $productNames = $productGroups->keys()->implode(', ');
            $productPriceDetails = $productGroups->map(function ($items, $productName) {
                $total = $items->sum('total_price_available');
                return $productName . '=' . number_format($total, 2);
            })->values()->implode(', ');
            return [
                'id'                    => $first->id,
                'purchase_date'         => $first->purchase_date,
                'floor_id'              => $first->floor_id,
                'room_id'               => $first->room_id,
                'seat_id'               => $first->seat_id,
                'customer_id'           => $first->customer_id,
                'floor_name'            => optional($first->floors)->name,
                'room_no'               => optional($first->rooms)->room_no,
                'seat_no'               => optional($first->seat)->seat_no,
                'customer_name'         => optional($first->customer)->full_name,
                'product_names'         => $productNames,
                'product_price_details' => $productPriceDetails,
                'total_quantity'        => $group->sum('customer_quantity'),
                'total_price_available' => $group->sum('total_price_available'),
            ];
        });

    $total       = $grouped->count();
    $currentPage = (int) $request->input('page', 1);
    $offset      = ($currentPage - 1) * $perPage;
    $pagedItems  = $grouped->slice($offset, $perPage)->values();

    return response()->json([
        'status'               => 'success',
        'productstock'         => $pagedItems,
        'total'                => $total,
        'from'                 => $total > 0 ? $offset + 1 : 0,
        'per_page'             => (int) $perPage,
        'last_page'            => (int) ceil($total / $perPage),
        'current_page'         => $currentPage,
        'grand_total_quantity' => $grouped->sum('total_quantity'),
        'grand_total'          => $grouped->sum('total_price_available'),
    ]);
}



    public function customerdistributionlist(Request $request)
    {
        $perPage    = $request->input('per_page', 50);
        $search     = $request->input('search', '');
        $startDate  = $request->input('start_date', '');
        $endDate    = $request->input('end_date', '');
        $supplierId = $request->input('supplier_id', '');

        $query = ProductDistribution::with(['customer', 'floors', 'rooms', 'seat'])
            ->when($search, function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%");
            })
            ->when(!empty($supplierId), function ($q) use ($supplierId) {
                $q->where('customer_id', $supplierId);
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
            ->orderBy('purchase_date', 'desc')
            ->orderBy('id', 'desc');

        $allRows = $query->get();
        $grouped = $allRows
    ->groupBy(function ($item) {
        return $item->purchase_date . '_' . $item->floor_id . '_' . $item->room_id . '_' . $item->seat_id . '_' . $item->customer_id;
    })
    ->values()
    ->map(function ($group) {
        $first = $group->first();
        $productGroups = $group->groupBy('product_name');
        $productNames = $productGroups->keys()->implode(', ');
        $productPriceDetails = $productGroups->map(function ($items, $productName) {
            $total = $items->sum('total_price_available');
            return $productName . '=' . number_format($total, 2);
        })->values()->implode(', ');
        return [
            'id'                    => $first->id,
            'purchase_date'         => $first->purchase_date,
            'floor_id'              => $first->floor_id,
            'room_id'               => $first->room_id,
            'seat_id'               => $first->seat_id,
            'customer_id'           => $first->customer_id,
            'floor_name'            => optional($first->floors)->name,
            'room_no'               => optional($first->rooms)->room_no,
            'seat_no'               => optional($first->seat)->seat_no,
            'customer_name'         => optional($first->customer)->full_name,
            'product_names'         => $productNames,
            'product_price_details' => $productPriceDetails,
            'total_quantity'        => $group->sum('customer_quantity'),
            'total_price_available' => $group->sum('total_price_available'),
        ];
    });

        $total = $grouped->count();
        $currentPage = (int) $request->input('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $pagedItems = $grouped->slice($offset, $perPage)->values();

        return response()->json([
            'status'               => 'success',
            'productstock'         => $pagedItems,
            'total'                => $total,
            'from'                 => $total > 0 ? $offset + 1 : 0,
            'per_page'             => (int) $perPage,
            'last_page'            => (int) ceil($total / $perPage),
            'current_page'         => $currentPage,
            'grand_total_quantity' => $grouped->sum('total_quantity'),
            'grand_total'          => $grouped->sum('total_price_available'),
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
        $productdistributiondelete = ProductDistribution::findOrFail($id);
        $productdistributiondelete->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
