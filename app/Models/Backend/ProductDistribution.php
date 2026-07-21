<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDistribution extends Model
{
    use HasFactory;

     protected $fillable = [
        'floor_id',
        'room_id',
        'purchase_id',
        'customer_id',
        'supplier_id',
        'product_name',
        'single_price',
        'total_price_available',
        'purchase_date',
        'customer_quantity',
        'memo_number',
    ];

     public function customer()
    {
        return $this->belongsTo(RoomBookingHistory::class, 'customer_id');
    }


    public function floors()
    {
        return $this->belongsTo(Floor::class, 'floor_id');
    }



    public function rooms()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }


}
