<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'email', 'otp', 'expires_at'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

}
