<?php

namespace App\Models\Backed;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelManagem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];
}
