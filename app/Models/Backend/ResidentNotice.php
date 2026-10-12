<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ResidentNotice extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'notice',
        'priority',
        'status',
        'published_at',
        'created_by'
    ];

    protected $casts = [
        'status' => 'integer',
        'published_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
