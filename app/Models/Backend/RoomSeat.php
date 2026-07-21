<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomSeat extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'seat_no',
        'price',
        'advance_price',
        'status',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id', 'id');
    }
}
