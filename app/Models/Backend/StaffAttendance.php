<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Backend\Staffs;

class StaffAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'start_datetime',
        'end_datetime',
    ];

protected $casts = [
    'start_datetime' => 'datetime:Y-m-d H:i:s',
    'end_datetime'   => 'datetime:Y-m-d H:i:s',
];

    public function staff()
    {
        return $this->belongsTo(Staffs::class, 'staff_id');
    }
}