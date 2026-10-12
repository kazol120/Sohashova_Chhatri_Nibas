<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Backend\Floor;
use App\Models\Backend\Room;
use App\Models\Backend\Staffs;

use App\Models\Frontend\ResidenceOverview;
use App\Models\Frontend\Gallery;
use App\Models\Frontend\Slider;
use App\Models\Backend\RoomBookingHistory;


class HomeController extends Controller

    {


    public function homepage()

        {
       
            // Ensure pending booking seats (status = 0) are kept vacant (status = 0) until Admin approves
            $pendingBookings = RoomBookingHistory::where('status', 0)->get();
            foreach ($pendingBookings as $pBooking) {
                $roomsJson = $pBooking->floor_number_room_number_roomprice;
                $roomsArr  = is_string($roomsJson) ? json_decode($roomsJson, true) : $roomsJson;
                if (is_array($roomsArr)) {
                    foreach ($roomsArr as $rItem) {
                        $seatId     = $rItem['seat_id'] ?? null;
                        $seatString = $rItem['roomnumber'] ?? ($rItem['seat_no'] ?? null);
                        $sObj       = null;
                        if ($seatId) {
                            $sObj = \App\Models\Backend\RoomSeat::find($seatId);
                        }
                        if (!$sObj && $seatString) {
                            $parts = explode('-', $seatString, 2);
                            $rNo   = trim($parts[0] ?? '');
                            $sNo   = trim($parts[1] ?? $seatString);
                            $sObj  = \App\Models\Backend\RoomSeat::whereHas('room', function($q) use ($rNo) {
                                $q->where('room_no', $rNo);
                            })->where(function($q) use ($sNo, $seatString) {
                                $q->where('seat_no', $sNo)->orWhere('seat_no', $seatString);
                            })->first();
                        }
                        if ($sObj && (int)$sObj->status === 1) {
                            $hasApproved = RoomBookingHistory::where('status', 1)
                                ->whereNull('today_check_out')
                                ->where('floor_number_room_number_roomprice', 'like', "%{$seatString}%")
                                ->exists();
                            if (!$hasApproved) {
                                $sObj->update(['status' => 0]);
                                Room::syncRoomStatus($sObj->room_id);
                            }
                        }
                    }
                }
            }

            $floors = Floor::with(['rooms.seats'])->get();
            foreach ($floors as $floor) {
                $vacantSeats = 0;
                $seatPrices  = collect();
                foreach ($floor->rooms as $room) {
                    Room::syncRoomStatus($room->id);
                    foreach ($room->seats as $seat) {
                        if ((int) $seat->status === 0) {
                            $vacantSeats++;
                        }
                        if ((float) $seat->price > 0) {
                            $seatPrices->push((float) $seat->price);
                        }
                    }
                }
                $floor->available_seats_count = $vacantSeats;
                $singlePrice = $seatPrices->first() ?: 3000;
                $floor->formatted_price = '৳ ' . number_format($singlePrice);
            }
            $data['floors'] = $floors;

            $data['galleries'] = Gallery::latest()->get();
            $data['sliders'] = Slider::where('status', 1)->latest()->get();
            $data['residenceOverviews'] = ResidenceOverview::latest()->get();
            $data['hostelRules'] = \App\Models\HostelRule::orderBy('id', 'asc')->get();
            $data['bookinguest'] = RoomBookingHistory::count();
            $data['roomcount'] = Room::count();
            $data['staffscount'] = Staffs::count();
            
            $activeStudents = RoomBookingHistory::latest()->take(3)->get();
            if ($activeStudents->isEmpty()) {
                $activeStudents = RoomBookingHistory::all();
            }
            $data['activeStudents'] = $activeStudents;

            return view('Frontend.page.home',$data);
        }

        

public function bookingpage(Request $request)
{
    $checkin  = $request->checkin;
    $checkout = $request->checkout;

    $allRooms = Room::all();
    foreach ($allRooms as $r) {
        Room::syncRoomStatus($r->id);
    }

    $bookedRoomNumbers = [];

    if (!empty($checkin) && !empty($checkout)) {
        $bookingHistories = RoomBookingHistory::where('status', 1)
            ->where(function ($q) use ($checkin, $checkout) {
                $q->where('check_in', '<', $checkout)
                  ->where('check_out', '>', $checkin);
            })
            ->get();

        foreach ($bookingHistories as $history) {
            $roomsJson = $history->floor_number_room_number_roomprice;

            $rooms = is_string($roomsJson)
                ? json_decode($roomsJson, true)
                : $roomsJson;

            if (is_array($rooms)) {
                foreach ($rooms as $room) {
                    if (!empty($room['roomnumber'])) {
                        $bookedRoomNumbers[] = (string) $room['roomnumber'];
                    }
                }
            }
        }

        $bookedRoomNumbers = array_values(array_unique($bookedRoomNumbers));
    }

    $query = Room::with(['floor', 'seats'])
        ->where('status', 0)
        ->whereHas('seats', function ($q) {
            $q->where('status', 0);
        });

    if (!empty($bookedRoomNumbers)) {
        $query->whereNotIn('room_no', $bookedRoomNumbers);
    }

    $filters = [];

    if ($request->boolean('ac')) {
        $filters[] = 1;
    }

    if ($request->boolean('nonac')) {
        $filters[] = 2;
    }

    if (!empty($filters)) {
        $query->whereIn('acstatus', $filters);
    }

    $data['rooms']             = $query->get();
    $data['checkin']           = $checkin;
    $data['checkout']          = $checkout;
    $data['bookedRoomNumbers'] = $bookedRoomNumbers;

    return view('Frontend.page.bookingnow', $data);
}

public function floorRooms(Request $request, $id)
{
    $data['floor'] = Floor::findOrFail($id);

    $allRooms = Room::where('floor_id', $id)->get();
    foreach ($allRooms as $r) {
        Room::syncRoomStatus($r->id);
    }

    $query = Room::with(['floor', 'seats'])
        ->where('floor_id', $id)
        ->where('status', 0)
        ->whereHas('seats', function ($q) {
            $q->where('status', 0);
        });

    $filters = [];

    if ($request->boolean('ac')) {
        $filters[] = 1; 
    }

    if ($request->boolean('nonac')) {
        $filters[] = 2; 
    }

    if (!empty($filters)) {
        $query->whereIn('acstatus', $filters);
    }

    $data['rooms'] = $query->get();

    return view('Frontend.page.floor_rooms', $data);
}







    public function getLiveBookedSeats()
    {
        // 1. Approved bookings (status = 1) -> Red / Already Booked
        $approvedBookings = RoomBookingHistory::where('status', 1)
            ->whereNull('today_check_out')
            ->get();
        $approvedSeatNames = [];

        foreach ($approvedBookings as $history) {
            $roomsJson = $history->floor_number_room_number_roomprice;
            $rooms = is_string($roomsJson) ? json_decode($roomsJson, true) : $roomsJson;
            if (is_array($rooms)) {
                foreach ($rooms as $room) {
                    if (!empty($room['roomnumber'])) {
                        $approvedSeatNames[] = (string) $room['roomnumber'];
                    }
                }
            }
        }

        $directBookedSeats = \App\Models\Backend\RoomSeat::with('room')
            ->where('status', 1)
            ->get();

        foreach ($directBookedSeats as $s) {
            if ($s->room) {
                $name = $s->room->room_no . '-' . $s->seat_no;
                $approvedSeatNames[] = $name;
            }
        }

        $approvedSeatNames = array_values(array_unique($approvedSeatNames));

        // 2. Pending bookings (status = 0) -> Orange/Yellow / Booking Processing
        $pendingBookings = RoomBookingHistory::where('status', 0)
            ->whereNull('today_check_out')
            ->get();
        $pendingSeatNames = [];

        foreach ($pendingBookings as $pHistory) {
            $roomsJson = $pHistory->floor_number_room_number_roomprice;
            $rooms = is_string($roomsJson) ? json_decode($roomsJson, true) : $roomsJson;
            if (is_array($rooms)) {
                foreach ($rooms as $room) {
                    if (!empty($room['roomnumber'])) {
                        $seatName = (string) $room['roomnumber'];
                        if (!in_array($seatName, $approvedSeatNames)) {
                            $pendingSeatNames[] = $seatName;
                        }
                    }
                }
            }
        }

        $pendingSeatNames = array_values(array_unique($pendingSeatNames));

        return response()->json([
            'success'        => true,
            'approved_seats' => $approvedSeatNames,
            'pending_seats'  => $pendingSeatNames,
            'booked_seats'   => $approvedSeatNames
        ]);
    }

    public function HelpDesk()

        {
            return view('Frontend.page.helpdesk');
            
        }


}
