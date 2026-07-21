<?php
namespace App\Models\Backend;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'floor_id',
        'room_no',
        'room_size',
        'breakfast',
        'attached_bathroom',
        'max_people',
        'balcony',
        'ac_status',
        'price',
        'windows',
        'room_type',
        'max_discount',
        'status',
        'image',
        'acstatus',
    ];

    //  image -> array cast
    protected $casts = [
        'image' => 'array',
    ];

    public function floor()
    {
        return $this->belongsTo(Floor::class, 'floor_id', 'id');
    }

    protected $appends = ['image_url'];


    public function bookingHistories()
{
    return $this->hasMany(RoomBookingHistory::class, 'room_id', 'id');
}


    public function getImageUrlAttribute()
    {

    if (!$this->image) return null;
    $images = is_array($this->image) ? $this->image : json_decode($this->image, true);
    if (empty($images)) return null;
    return asset('room_image/' . $images[0]);
    
    }

public function getAllImageUrlsAttribute()
    {

    if (!$this->image) return [];
    $images = is_array($this->image) ? $this->image : json_decode($this->image, true);
    if (empty($images)) return [];
    return array_map(fn($img) => asset('room_image/' . $img), $images);

    }

    public function seats()
    {
        return $this->hasMany(RoomSeat::class, 'room_id', 'id');
    }

    public static function syncRoomStatus($roomId)
    {
        $room = self::find($roomId);
        if ($room) {
            $totalSeats = $room->seats()->count();
            $bookedSeats = $room->seats()->where('status', 1)->count();
            if ($totalSeats > 0 && $bookedSeats === $totalSeats) {
                $room->update(['status' => 1]); // Fully booked
            } else {
                $room->update(['status' => 0]); // Has vacant seats
            }
        }
    }
}