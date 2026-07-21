<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Backend\District;

class Thana extends Model
{
    use HasFactory;


    protected $fillable = ['name', 'district_id'];
        

    public function district()
    {
        return $this->belongsTo(District::class);
    }
    
}