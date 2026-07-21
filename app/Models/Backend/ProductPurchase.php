<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'product_id',
        'product_name',
        'single_price',
        'quantity',
        'available_quantity',
        'discount',
        'total_price',
        'total_price_available',
        'purchase_date',
        'memo_number',
        'customer_id',
    ];

    // Supplier relation
    
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}