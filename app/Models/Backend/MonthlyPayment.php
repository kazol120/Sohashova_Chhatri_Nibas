<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_booking_history_id',
        'payment_month',
        'amount',
        'carried_forward_due',
        'paid_amount',
        'due_amount',
        'months_to_extend',
        'payment_method',
        'trx_id',
        'note',
        'status',
        'received_by',
    ];

    public function booking()
    {
        return $this->belongsTo(RoomBookingHistory::class, 'room_booking_history_id');
    }
}
