<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyPaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_booking_history_id',
        'monthly_payment_id',
        'payment_month',
        'rent_paid',
        'carried_due_paid',
        'electricity_bill_paid',
        'total_collected',
        'payment_method',
        'trx_id',
        'note',
        'payment_date',
        'received_by',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'rent_paid' => 'decimal:2',
        'carried_due_paid' => 'decimal:2',
        'electricity_bill_paid' => 'decimal:2',
        'total_collected' => 'decimal:2',
    ];

    public function booking()
    {
        return $this->belongsTo(RoomBookingHistory::class, 'room_booking_history_id');
    }

    public function monthlyPayment()
    {
        return $this->belongsTo(MonthlyPayment::class, 'monthly_payment_id');
    }
}
