<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomChangeHistory extends Model
{
    use HasFactory;

    protected $table = 'room_change_histories';

    protected $fillable = [
        'room_booking_history_id',
        'resident_name',
        'phone',
        'old_floor',
        'old_room_seat',
        'new_floor',
        'new_room_seat',
        'old_monthly_amount',
        'new_monthly_amount',
        'fee_amount',
        'payment_method',
        'payment_status',
        'remarks',
        'changed_by',
    ];

    protected $casts = [
        'old_monthly_amount' => 'float',
        'new_monthly_amount' => 'float',
        'fee_amount'         => 'float',
    ];

    public function booking()
    {
        return $this->belongsTo(RoomBookingHistory::class, 'room_booking_history_id');
    }
}
