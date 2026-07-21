<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_id',
        'brand_category_id',
        'name',
        'buy_price',
       
        'date',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function brandCategory()
    {
        return $this->belongsTo(BrandCategory::class, 'brand_category_id');
    }

}
