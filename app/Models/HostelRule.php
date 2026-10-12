<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HostelRule extends Model
{
    use HasFactory;

    protected $table = 'hostel_rules';

    protected $fillable = [
        'icon',
        'description',
    ];
}
