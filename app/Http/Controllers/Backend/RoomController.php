<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Backend\Room;
use App\Models\Backend\Floor;
use App\Models\Backend\RoomBookingHistory;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class RoomController extends Controller
{
    public function index(){
        return view('backend.room.view'); 
    }

    public function roomsByFloor(Floor $floor)
    {
        $rooms = Room::where('floor_id', $floor->id)
            ->whereNull('status')
            ->select('id','floor_id','room_no','status')
            ->orderBy('room_no')
            ->get();

        return response()->json(['data' => $rooms]);
    }


    public function activeRoom(Request $request)
        {
            $room = Room::find($request->id);

            if (!$room) {
                return response()->json(['success' => false]);
            }

            $room->status = 0;
            $room->save();

            return response()->json([
                'success' => true
            ]);
        }



    public function getRooms(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $search  = trim($request->get('search', ''));

        $today = Carbon::today('Asia/Dhaka')->toDateString();
        $expiredBookings = RoomBookingHistory::whereDate('check_out', '<', $today)->get();
        $expiredRoomNumbers = [];

        foreach ($expiredBookings as $booking) {
            $roomData = $booking->floor_number_room_number_roomprice;

            if (!is_array($roomData)) {
                $roomData = json_decode($roomData, true);
            }
            if (is_array($roomData)) {
                foreach ($roomData as $item) {
                    if (!empty($item['roomnumber'])) {
                        $expiredRoomNumbers[] = (string) $item['roomnumber'];
                    }
                }
            }
        }
        $expiredRoomNumbers = array_unique($expiredRoomNumbers);
        $q = Room::query()->with(['floor:id,name', 'seats']);

        if ($search !== '') {
            $q->where(function ($qq) use ($search) {
                $qq->where('room_no', 'like', "%{$search}%")
                   ->orWhereHas('floor', function ($fq) use ($search) {
                       $fq->where('name', 'like', "%{$search}%");
                   });
            });
        }
        $rooms = $q->orderBy('id', 'desc')->paginate($perPage);

        $rooms->getCollection()->transform(function ($room) use ($expiredRoomNumbers) {
            $raw = $room->getRawOriginal('image');
            $images = [];

            if ($raw) {
                $decoded = json_decode($raw, true);
                $images = is_array($decoded) ? $decoded : [];
            }
            $room->all_image_urls = array_map(
                fn($img) => asset('room_image/' . $img),
                $images
            );
            $room->can_active_show = (
                (int) $room->status === 1 &&
                in_array((string) $room->room_no, $expiredRoomNumbers)
            );

            return $room;
        });
        return response()->json($rooms);
    }


    public function store(Request $request)

        {
            $data = $request->validate([
                'floor_id'          => ['required', 'exists:floors,id'],
                'room_id'           => ['required', 'exists:rooms,id'],
                'acstatus'          => 'required|in:1,2',
                'price'             => ['required', 'numeric', 'min:0'],
                'room_size'         => ['required', 'string', 'max:50'],
                'max_people'        => ['nullable'],
                'breakfast'         => ['required', Rule::in(['yes','no'])],
                'attached_bathroom' => ['required', Rule::in(['yes','no'])],
                'room_type'         => ['required', Rule::in(['Singel','Doubel'])],
                'ac_status'         => ['required', Rule::in(['Ac','Non Ac'])],
                'windows'           => ['required', Rule::in(['yes','no'])],
                'balcony'           => ['required', Rule::in(['yes','no'])],
                'images'            => ['required', 'array', 'min:1'],
                'images.*'          => ['file', 'mimes:jpg,jpeg,png,webp,gif,svg,jfif,heic,heif,avif,bmp', 'max:9096'],
            ]);

            $expected = ((int)$request->acstatus === 1) ? 'Ac' : 'Non Ac';
            if ($request->ac_status !== $expected) {
                return response()->json([
                    'message' => 'Room type mismatch',
                    'errors'  => ['acstatus' => ['Invalid selection']],
                ], 422);
            }

            $room = Room::where('id', $data['room_id'])
                ->where('floor_id', $data['floor_id'])
                ->whereNull('status')
                ->firstOrFail();
            $imageNames = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $img) {
                    $name = time() . '_' . uniqid() . '.' . $img->getClientOriginalExtension();
                    $img->move(public_path('room_image'), $name);
                    $imageNames[] = $name;
                }
            }
            $room->update([
                'price'             => $data['price'],
                'room_size'         => $data['room_size'],
                'max_people'        => $data['max_people'] ?? null,
                'breakfast'         => $data['breakfast'],
                'attached_bathroom' => $data['attached_bathroom'],
                'room_type'         => $data['room_type'],
                'ac_status'         => $data['ac_status'],
                'windows'           => $data['windows'],
                'balcony'           => $data['balcony'],
                'acstatus'          => $data['acstatus'],
                'image'             => $imageNames, 
                'status'            => 0,
            ]);

            $seats = json_decode($request->input('seats', '[]'), true);
            if (empty($seats)) {
                $seats[] = [
                    'seat_no' => 'Seat-A',
                    'price' => $data['price'],
                    'advance_price' => 0
                ];
            }

            $keepIds = [];
            foreach ($seats as $s) {
                $seat = $room->seats()->updateOrCreate(
                    ['id' => $s['id'] ?? null],
                    [
                        'seat_no' => $s['seat_no'] ?? 'Seat-A',
                        'price' => $s['price'] ?? $data['price'],
                        'advance_price' => $s['advance_price'] ?? 0,
                    ]
                );
                $keepIds[] = $seat->id;
            }
            $room->seats()->whereNotIn('id', $keepIds)->delete();
            Room::syncRoomStatus($room->id);

            return response()->json(['message' => 'Room saved successfully']);
        }

   
    public function update(Request $request, Room $room)

    {
        $data = $request->validate([
            'price'             => ['required', 'numeric', 'min:0'],
            'room_size'         => ['required', 'string', 'max:50'],
            'max_people'        => ['nullable'],
            'acstatus'          => ['required', 'integer', 'in:1,2'],
            'breakfast'         => ['required', Rule::in(['yes','no'])],
            'attached_bathroom' => ['required', Rule::in(['yes','no'])],
            'room_type'         => ['required', Rule::in(['Singel','Doubel'])],
            'ac_status'         => ['required', Rule::in(['Ac','Non Ac'])],
            'windows'           => ['required', Rule::in(['yes','no'])],
            'balcony'           => ['required', Rule::in(['yes','no'])],
            'new_images.*'      => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif,svg,jfif,heic,heif,avif,bmp', 'max:9096'],
            'keep_images'       => ['nullable', 'array'],
        ]);

        $imageNames = $request->input('keep_images', []);
        if ($request->hasFile('new_images')) {
            foreach ($request->file('new_images') as $img) {
                $name = time() . '_' . uniqid() . '.' . $img->getClientOriginalExtension();
                $img->move(public_path('room_image'), $name);
                $imageNames[] = $name;
            }
        }

        $room->price             = $data['price'];
        $room->room_size         = $data['room_size'];
        $room->max_people        = $data['max_people'] ?? null;
        $room->breakfast         = $data['breakfast'];
        $room->attached_bathroom = $data['attached_bathroom'];
        $room->room_type         = $data['room_type'];
        $room->ac_status         = $data['ac_status'];
        $room->windows           = $data['windows'];
        $room->balcony           = $data['balcony'];
        $room->acstatus          = $data['acstatus'];  
        $room->image             = $imageNames;        
        $room->save();

        $seats = json_decode($request->input('seats', '[]'), true);
        if (empty($seats)) {
            $seats[] = [
                'seat_no' => 'Seat-A',
                'price' => $data['price'],
                'advance_price' => 0
            ];
        }

        $keepIds = [];
        foreach ($seats as $s) {
            $seat = $room->seats()->updateOrCreate(
                ['id' => $s['id'] ?? null],
                [
                    'seat_no' => $s['seat_no'] ?? 'Seat-A',
                    'price' => $s['price'] ?? $data['price'],
                    'advance_price' => $s['advance_price'] ?? 0,
                ]
            );
            $keepIds[] = $seat->id;
        }
        $room->seats()->whereNotIn('id', $keepIds)->delete();
        Room::syncRoomStatus($room->id);

        return response()->json(['message' => 'Room updated successfully']);
    }

    /* ========================
       Helper: public upload + filename only
       ========================= */


    private function uploadRoomImageToPublic(Request $request, ?string $oldFilename): string
    {
        $dir = public_path('room_image');
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        if ($oldFilename) {
            $oldPath = $dir . DIRECTORY_SEPARATOR . $oldFilename;
            if (File::exists($oldPath)) File::delete($oldPath);
        }

        $file = $request->file('image');
        $name = (string) Str::uuid() . '.' . $file->getClientOriginalExtension();
        $file->move($dir, $name);
        return $name; 
    }


  public function destroy(Room $room)
{
    if ($room->image) {
        $images = is_array($room->image) ? $room->image : json_decode($room->image, true);
        
        if (is_array($images)) {
            foreach ($images as $img) {
                $path = public_path('room_image/' . $img);
                if (File::exists($path)) File::delete($path);
            }
        }
    }

    $room->delete();
    return response()->json(['message' => 'Room deleted']);
}


}
