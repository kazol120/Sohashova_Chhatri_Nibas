<?php

namespace App\Models\Frontend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResidenceOverview extends Model
{
    use HasFactory;

    protected $table = 'residence_overviews';

    protected $fillable = ['title', 'description', 'img_back', 'img_front'];
}
