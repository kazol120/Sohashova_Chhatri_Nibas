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

    $q = Floor::with(['rooms' => function ($query) {
            $query->orderBy('room_no', 'asc');
        }])
        ->orderByDesc('id');

    if ($search !== '') {
        $q->where('name', 'like', '%' . $search . '%');
    }

    $floors = $q->paginate($perPage);

    $floors->getCollection()->transform(function ($f) {
        $f->image_url = $this->imageUrl($f->image);
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
    $rooms = array_values(array_unique(array_map('intval', $rooms)));
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
            if (!$roomNo || $roomNo < 1) continue;

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
