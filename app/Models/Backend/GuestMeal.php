<?php

namespace App\Models\Backend;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GuestMeal extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'guest_meals';

    protected $fillable = [
        'user_id',
        'booking_id',
        'date',
        'guest_name',
        'meal_count',
        'amount',
        'note',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date'       => 'date',
        'meal_count' => 'decimal:2',
        'amount'     => 'decimal:2',
        'status'     => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function booking()
    {
        return $this->belongsTo(RoomBookingHistory::class, 'booking_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
