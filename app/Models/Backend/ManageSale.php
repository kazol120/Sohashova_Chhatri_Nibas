<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManageSale extends Model
{
    use HasFactory;

    protected $fillable = [

        'purchase_id',
        'product_name',
        'single_price',
        'available_quantity',
        'total_price_available',
        'purchase_date',
        'customer_quantity',
        'customer_id',
        'supplier_id',
    ];

      public function customer() 
    {
        return $this->belongsTo(RoomBookingHistory::class, 'customer_id');
    }

    public function supplier() 
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

}
