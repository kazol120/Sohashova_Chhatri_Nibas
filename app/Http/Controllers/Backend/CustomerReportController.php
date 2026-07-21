<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Backend\RoomBookingHistory;
use App\Models\Backend\ProductDistribution;
use App\Models\Backend\Room;
use App\Models\Backend\RoomSeat;
use Carbon\Carbon;

class CustomerReportController extends Controller

{

      public function getroombooing(Request $request)
    {
        try {
            $today = now()->toDateString();

            $query = RoomBookingHistory::orderByDesc('created_at');

            if ($request->filter === 'today') {
                $query->whereDate('created_at', $today);

            } elseif ($request->filter === 'checkout_due') {
                $query->whereDate('check_out', '<', $today)
                      ->where('status', 0);

            } elseif ($request->filter === 'checkout_in') {
                $query->whereDate('check_in', $today)
                      ->where('status', 0);

            } elseif ($request->filter === 'checkout_list') {
                $query->whereDate('today_check_out', $today)
                      ->where('status', 1);
            }

            // 6 items per page
            $paginated = $query->paginate(6);

            $rooms = $paginated->getCollection()->map(function ($r) {
                $rawItems = $r->floor_number_room_number_roomprice ?? [];

                $roomItems = collect($rawItems)->map(fn($item) => [
                    'floornumber' => $item['floor_name'] ?? $item['floor_number'] ?? $item['floornumber'] ?? '-',
                    'roomnumber'  => $item['room_number'] ?? $item['roomnumber'] ?? $item['room_no'] ?? '-',
                    'price'       => $item['roomprice'] ?? $item['room_price'] ?? $item['price'] ?? 0,
                ])->toArray();

                $totalRoomAmount = collect($roomItems)->sum('price');

                $products = ProductDistribution::where('customer_id', $r->id)->get()
                    ->map(fn($p) => [
                        'product_name'      => $p->product_name,
                        'customer_quantity' => $p->customer_quantity,
                        'single_price'      => $p->single_price,
                        'total_price'       => $p->total_price_available,
                    ])->toArray();

                return [
                    'id'                   => $r->id,
                    'full_name'            => $r->full_name ?? '-',
                    'check_in'             => $r->check_in,
                    'check_out'            => $r->check_out,
                    'created_at'           => $r->created_at,
                    'daybytotalamount'     => $r->daybytotalamount ?? 0,
                    'payment_amount_total' => $r->payment_amount_total ?? 0,
                    'room_items'           => $roomItems,
                    'total_room_amount'    => $totalRoomAmount,
                    'products'             => $products,
                    'room_ids'             => collect($rawItems)->pluck('room_id')->filter()->toArray(),
                ];
            });

            return response()->json([
                'data' => $rooms,
                'pagination' => [
                    'total'        => $paginated->total(),
                    'current_page' => $paginated->currentPage(),
                    'last_page'    => $paginated->lastPage(),
                    'per_page'     => $paginated->perPage(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
            ], 500);
        }
    }




public function activeRoom(Request $request)
{
    try {
        $booking = RoomBookingHistory::find($request->id);
        if (!$booking) {
            return response()->json(['success' => false]);
        }

        $rawItems = $booking->floor_number_room_number_roomprice ?? [];
        foreach ($rawItems as $item) {
            $rn = $item['roomnumber'] ?? $item['room_number'] ?? null;
            if ($rn) {
                $parts = explode('-', $rn, 2);
                $roomNo = $parts[0] ?? '';
                $seatNo = $parts[1] ?? '';

                $room = Room::where('room_no', $roomNo)->first();
                if ($room) {
                    RoomSeat::where('room_id', $room->id)->where('seat_no', $seatNo)->update(['status' => 0]);
                    Room::syncRoomStatus($room->id);
                } else {
                    Room::where('room_no', $rn)->update(['status' => 0]);
                }
            }
        }
        $dodatydatetime = now(); //  today date + time
        //  RoomBookingHistory status = 1
        $booking->status = 1;
      $booking->today_check_out = now();
        $booking->save();

        return response()->json(['success' => true]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error'   => $e->getMessage(),
            'line'    => $e->getLine(),
        ], 500);
    }
}


    public function getproductdistributton()
    {
        $data = ProductDistribution::orderByDesc('created_at')->get();
        return response()->json(['data' => $data]);
    }
    

    public function index(){
        return view('backend.report.customer-report');
    }

    
}
