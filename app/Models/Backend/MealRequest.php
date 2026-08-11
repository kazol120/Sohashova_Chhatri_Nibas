<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class MealRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'end_date',
        'total_days',
        'request_type',
        'status',
        'user_notified',
        'admin_note',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
