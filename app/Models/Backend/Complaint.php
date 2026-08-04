<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Backend\RoomBookingHistory;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'room_booking_history_id',
        'complaint_text',
        'status',
        'admin_note'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function booking()
    {
        return $this->belongsTo(RoomBookingHistory::class, 'room_booking_history_id');
    }
}
