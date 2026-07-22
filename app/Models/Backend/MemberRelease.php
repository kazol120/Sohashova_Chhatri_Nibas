<?php

namespace App\Models\Backend;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberRelease extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'release_date',
        'settlement_status',
        'closing_note',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
