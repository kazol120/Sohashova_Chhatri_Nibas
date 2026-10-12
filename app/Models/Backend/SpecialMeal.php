<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialMeal extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'title',
        'full_rate',
        'half_rate',
        'status',
    ];

    protected $casts = [
        'date'      => 'date:Y-m-d',
        'full_rate' => 'float',
        'half_rate' => 'float',
        'status'    => 'boolean',
    ];
}
