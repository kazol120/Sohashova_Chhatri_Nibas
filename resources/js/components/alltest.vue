<script type="text/javascript">

  // app/Http/Controllers/Api/FloorController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Floor;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FloorController extends Controller
{
  public function store(Request $request)
  {
    $validated = $request->validate([
      'name'  => 'required|string|max:255',
      'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
      'rooms' => 'required', // JSON string
    ]);

    $rooms = json_decode($request->rooms, true);

    if (!is_array($rooms) || count($rooms) < 1) {
      return response()->json(['message' => 'Rooms list is required.'], 422);
    }

    // ✅ sanitize + unique in request
    $rooms = array_values(array_unique(array_map('intval', $rooms)));
    foreach ($rooms as $r) {
      if ($r < 1) return response()->json(['message' => 'Invalid room number.'], 422);
    }

    return DB::transaction(function () use ($request, $rooms) {

      // ✅ upload image
      $path = null;
      if ($request->hasFile('image')) {
        $path = $request->file('image')->store('floors', 'public');
      }

      // ✅ create floor
      $floor = Floor::create([
        'name'  => $request->name,
        'image' => $path, // store path in DB
      ]);

      // ✅ create rooms
      $insert = [];
      foreach ($rooms as $roomNo) {
        $insert[] = [
          'floor_id' => $floor->id,
          'room_no'  => $roomNo,
          'created_at' => now(),
          'updated_at' => now(),
        ];
      }

      Room::insert($insert);

      return response()->json([
        'message' => 'Floor created successfully',
        'floor' => $floor,
        'rooms' => $rooms,
      ], 201);
    });
  }
}



async submitForm() {
  this.isLoading = true;

  try {
    const fd = new FormData();
    fd.append("name", this.form.name);
    if (this.form.image) fd.append("image", this.form.image);
    fd.append("rooms", JSON.stringify(this.roomList));

    const res = await axios.post(this.url + "api/floors", fd, {
      headers: { "Content-Type": "multipart/form-data" },
    });

    // success
    console.log(res.data);
    this.clearRooms();
    this.form.name = "";
    this.form.image = null;
    this.imagePreview = "";

    // optional: redirect
    // window.location.href = this.url + "injury-title-view";

  } catch (e) {
    console.error(e?.response?.data || e);
  } finally {
    this.isLoading = false;
  }
}


</script>