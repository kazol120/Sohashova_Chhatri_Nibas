<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ResidentCheckInOut extends Model
{
    use HasFactory;

    protected $table = 'resident_check_in_outs';

    protected $fillable = [
        'user_id',
        'room_booking_history_id',
        'resident_name',
        'phone',
        'floor_room_seat',
        'check_out_date',
        'expected_return_date',
        'check_in_date',
        'leave_reason',
        'emergency_contact',
        'status',
        'is_seen_by_admin',
        'created_by',
        'admin_notes',
    ];

    protected $casts = [
        'check_out_date'       => 'datetime',
        'expected_return_date' => 'date',
        'check_in_date'        => 'datetime',
        'is_seen_by_admin'     => 'boolean',
        'status'               => 'integer',
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
