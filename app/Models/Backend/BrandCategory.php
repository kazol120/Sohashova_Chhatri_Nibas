<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandCategory extends Model
{
    use HasFactory;

     protected $fillable = [
        'name',
       
     ];


  public function brands()
    {
        return $this->hasMany(Brand::class, 'brand_category_id');
    }
    
}
