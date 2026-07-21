<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'brand_category_id',
    ];


        public function category()
        {
            return $this->belongsTo(BrandCategory::class, 'brand_category_id');
        }
   
}
