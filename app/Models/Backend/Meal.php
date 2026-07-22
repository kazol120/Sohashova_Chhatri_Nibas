<?php

namespace App\Models\Backend;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'half_meal',
        'full_meal',
        'made_by',
        'note',
    ];
    protected $casts = [
        'half_meal' => 'boolean',
        'full_meal' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function madeBy()
    {
        return $this->belongsTo(User::class, 'made_by');
    }
}
