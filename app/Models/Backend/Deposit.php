<?php

namespace App\Models\Backend;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'date',
        'made_by',
        'note',
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
