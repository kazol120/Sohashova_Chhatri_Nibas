<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Backend\Floor;
use App\Models\Backend\Room;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class FloorController extends Controller
{


    public function all()
    {
        return response()->json([
            'data' => Floor::select('id', 'name')->orderBy('name')->get()
        ]);
    }


  
    private function imageUrl($filename)
    {
        if (!$filename) return null;
        return asset('floor_image/' . $filename);
    }


    public function index()
    {
        return view('backend.floor.create'); 
    }


public function floorget(Request $request)
{
    $perPage = (int) ($request->per_page ?? 10);
    $search  = trim((string) ($request->search ?? ''));

    // Fetch all active booking histories to determine booking status (processing vs confirmed)
    $activeBookings = DB::table('room_booking_histories')
        ->whereNull('deleted_at')
        ->whereNull('today_check_out')
        ->select('id', 'full_name', 'phone', 'floor_number_room_number_roomprice', 'status', 'check_in')
        ->get();

    $seatBookingMap = [];
    $roomBookingMap = [];

    foreach ($activeBookings as $bk) {
        $items = [];
        $raw = $bk->floor_number_room_number_roomprice;
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $items = $decoded;
            } elseif (is_string($decoded)) {
                $decoded2 = json_decode($decoded, true);
                if (is_array($decoded2)) {
                    $items = $decoded2;
                }
            }
        } elseif (is_array($raw)) {
            $items = $raw;
        }

        if (!is_array($items)) {
            $items = [];
        }

        $bStatus = ((int)$bk->status === 1) ? 'confirmed' : 'processing';

        foreach ($items as $item) {
            if (!is_array($item)) continue;
            $info = [
                'status'         => $bStatus,
                'occupant_name'  => $bk->full_name,
                'occupant_phone' => $bk->phone,
                'check_in'       => $bk->check_in,
                'booking_id'     => $bk->id,
            ];

            if (!empty($item['seat_id'])) {
                $seatBookingMap[$item['seat_id']] = $info;
            }
            if (!empty($item['room_id'])) {
                $roomBookingMap[$item['room_id']] = $info;
            }
            if (!empty($item['roomnumber'])) {
                $seatBookingMap[$item['roomnumber']] = $info;
            }
        }
    }

    $q = Floor::with(['rooms' => function ($query) {
            $query->with(['seats' => function($sq) {
                $sq->orderBy('seat_no', 'asc');
            }])->orderBy('room_no', 'asc');
        }])
        ->orderByDesc('id');

    if ($search !== '') {
        $q->where('name', 'like', '%' . $search . '%');
    }

    $floors = $q->paginate($perPage);

    $floors->getCollection()->transform(function ($f) use ($seatBookingMap, $roomBookingMap) {
        $f->image_url = $this->imageUrl($f->image);

        if ($f->rooms) {
            foreach ($f->rooms as $room) {
                if ($room->seats && count($room->seats)) {
                    foreach ($room->seats as $seat) {
                        $fullSeatKey = $room->room_no . '-' . $seat->seat_no;
                        $booking = $seatBookingMap[$seat->id] ?? ($seatBookingMap[$fullSeatKey] ?? null);

                        if ($booking) {
                            $seat->booking_status = $booking['status'];
                            $seat->occupant_name = $booking['occupant_name'];
                            $seat->occupant_phone = $booking['occupant_phone'];
                            $seat->check_in = $booking['check_in'];
                            $seat->booking_id = $booking['booking_id'];
                        } elseif ((int)$seat->status === 1) {
                            $seat->booking_status = 'confirmed';
                        } else {
                            $seat->booking_status = 'available';
                        }
                    }
                } else {
                    $booking = $roomBookingMap[$room->id] ?? ($seatBookingMap[$room->room_no] ?? null);
                    if ($booking) {
                        $room->booking_status = $booking['status'];
                        $room->occupant_name = $booking['occupant_name'];
                        $room->occupant_phone = $booking['occupant_phone'];
                    } elseif ((int)$room->status === 1) {
                        $room->booking_status = 'confirmed';
                    } else {
                        $room->booking_status = 'available';
                    }
                }
            }
        }

        return $f;
    });

    return response()->json($floors);
}





 public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name'  => 'required|string|max:255',
        'image' => 'nullable|file|mimes:jpg,jpeg,png,webp,gif,svg,jfif,heic,heif,avif,bmp|max:9096', 
        'rooms' => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }
    $rooms = json_decode($request->rooms, true);

    if (!is_array($rooms) || count($rooms) < 1) {
        return response()->json(['message' => 'Rooms list is required'], 422);
    }
    $rooms = array_values(array_unique(array_filter(array_map(function ($r) {
        return trim((string) $r);
    }, $rooms), function ($r) {
        return $r !== '';
    })));
    return DB::transaction(function () use ($request, $rooms) {
        $dir = public_path('floor_image');
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $ext = strtolower($file->getClientOriginalExtension());
            $filename = 'floor_' . time() . '_' . uniqid() . '.' . $ext;
            $file->move($dir, $filename);
        } else {
            $filename = null;
        }
        $floor = Floor::create([
            'name'  => $request->name,
            'image' => $filename,
        ]);

        $now = now();
        $insert = [];
        foreach ($rooms as $roomNo) {
            if ($roomNo === '') continue;

            $insert[] = [
                'floor_id'    => $floor->id,
                'room_no'     => $roomNo,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }
        Room::insert($insert);
        return response()->json([
            'message'   => 'Floor created successfully ',
            'floor_id'  => $floor->id,
            'image'     => $filename,
            'image_url' => $filename ? asset('floor_image/' . $filename) : null,
            'rooms'     => $rooms,
        ], 201);
    });
}


public function update(Request $request, $id)
{
    if ($request->has('rooms') && is_string($request->rooms)) {
        $request->merge([
            'rooms' => json_decode($request->rooms, true)
        ]);
    }
    $validator = Validator::make($request->all(), [
        'name'            => 'required|string|max:255',
        'image'           => 'nullable|file|mimes:jpg,jpeg,png,webp,gif,svg,jfif,heic,heif,avif,bmp|max:4096',
        'rooms'           => 'required|array',  
        'rooms.*.id'      => 'nullable|integer|exists:rooms,id', 
        'rooms.*.room_no' => 'required|string|max:255',  
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $floor = Floor::findOrFail($id);
    $floor->name = $request->name;

    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $imageName = 'floor_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('floor_image'), $imageName);
        $floor->image = $imageName;
    }

    $floor->save();
    $incomingRoomIds = collect($request->rooms)->pluck('id')->filter()->toArray();

    Room::where('floor_id', $id)->whereNotIn('id', $incomingRoomIds)->delete();

    foreach ($request->rooms as $roomData) {
        if (isset($roomData['id'])) {
            Room::where('id', $roomData['id'])->update(['room_no' => $roomData['room_no']]);
        } else {
            Room::create([
                'floor_id' => $id,
                'room_no'  => $roomData['room_no']
            ]);
        }
    }
    return response()->json(['message' => 'Floor updated successfully'], 200);
}


  
    public function destroy($id)
    {

        $floor = Floor::findOrFail($id);
        return DB::transaction(function () use ($floor) {
            if ($floor->image) {
                $path = public_path('floor_image/' . $floor->image);
                if (File::exists($path)) File::delete($path);
            }
            Room::where('floor_id', $floor->id)->delete();
            $floor->delete();
            return response()->json(['message' => 'Floor deleted successfully']);
        });

    }




public function storeMultipleRooms(Request $request)
{
    $validator = Validator::make($request->all(), [
        'floor_id' => 'required|exists:floors,id',
        'rooms' => 'required|array|min:1',
        'rooms.*' => 'required|string|max:50',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 422);
    }

    $floorId = $request->floor_id;

    $roomNumbers = collect($request->rooms)
        ->map(fn ($room) => trim($room))
        ->filter()
        ->unique()
        ->values();

    $existingRooms = Room::where('floor_id', $floorId)
        ->whereIn('room_no', $roomNumbers)
        ->pluck('room_no')
        ->toArray();

    if (!empty($existingRooms)) {
        return response()->json([
            'status' => false,
            'message' => 'Some rooms already exist for this floor.',
            'existing_rooms' => $existingRooms,
        ], 422);
    }

    DB::beginTransaction();

    try {
        $insertData = $roomNumbers->map(function ($roomNo) use ($floorId) {
            return [
                'floor_id' => $floorId,
                'room_no' => $roomNo,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();

        Room::insert($insertData);

        DB::commit();

        return response()->json([
            'status' => true,
            'message' => 'Rooms created successfully.',
        ]);
    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'status' => false,
            'message' => 'Failed to create rooms.',
            'error' => $e->getMessage(),
        ], 500);
    }
}




}
