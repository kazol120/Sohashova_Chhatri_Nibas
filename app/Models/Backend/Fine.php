<?php

namespace App\Models\Backend;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fine extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'date',
        'reason',
        'imposed_by',
        'replace_user_id',
        'type'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function imposedBy()
    {
        return $this->belongsTo(User::class, 'imposed_by');
    }

    public function replaceUser()
    {
        return $this->belongsTo(User::class, 'replace_user_id');
    }
}
