<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Backend\District;


class Division extends Model
{

    use HasFactory;
    protected $fillable = ['name'];

    
    
    public function districts()
    {
        return $this->hasMany(District::class);
    }

}