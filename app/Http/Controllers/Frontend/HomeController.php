<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Backend\Floor;
use App\Models\Backend\Room;
use App\Models\Backend\Staffs;

use App\Models\Frontend\ResidenceOverview;
use App\Models\Frontend\Gallery;
use App\Models\Backend\RoomBookingHistory;


class HomeController extends Controller

    {


    public function homepage()

        {
       
            $data['floors'] = Floor::with(['rooms' => function ($q) { 
                $q->select('id', 'floor_id', 'room_no', 'price', 'status', 'room_type', 'ac_status')
                  ->with('seats');
            }])->withCount(['rooms as rooms_count' => function ($q) { $q->where('status', 0);}])->get();

            $data['galleries'] = Gallery::latest()->get();
            $data['residenceOverviews'] = ResidenceOverview::latest()->get();
            $data['bookinguest'] = RoomBookingHistory::count();
            $data['roomcount'] = Room::count();
            $data['staffscount'] = Staffs::count();

            return view('Frontend.page.home',$data);
        }

        

public function bookingpage(Request $request)
{
    $checkin  = $request->checkin;
    $checkout = $request->checkout;

    $bookedRoomNumbers = [];

    if (!empty($checkin) && !empty($checkout)) {
    $bookingHistories = RoomBookingHistory::where(function ($q) use ($checkin, $checkout) {
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

    $query = Room::with(['floor', 'seats'])->where('status', 0);

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
            $query = Room::with(['floor', 'seats'])
                ->where('status', 0)
                ->where('floor_id', $id);
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







    public function HelpDesk()

        {
            return view('Frontend.page.helpdesk');
            
        }


}
