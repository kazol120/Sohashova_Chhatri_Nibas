<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomBookingHistory extends Model

{

    use HasFactory;

    protected $fillable = [
        'floor_id',
        'room_id',
        'image',
        'full_name',
        'user_type',
        'father_name',
        'mother_name',
        'email',
        'phone',
        'nid',
        'mother_nid',
        'father_nid',
        'division_id',
        'district_id',
        'thana_id',
        'pay_cash_in',
        'pay_online',
        'check_in',
        'check_out',
        'status',
        'roomprice',
        'payment_amount_total',
        'daybytotalamount',
        'password',
        'floor_number_room_number_roomprice',
        'today_check_out'
    ];



    protected $casts = [
        'check_in'  => 'date',
        'check_out' => 'date',
        'today_check_out' => 'date',
        'floor_number_room_number_roomprice' => 'array',
        'daybytotalamount' => 'float',
    ];

    public function floor(){
        return $this->belongsTo(Floor::class);
    }

    public function room(){
          return $this->belongsTo(Room::class);
    }


    public function thana(){
        return $this->belongsTo(Thana::class);
    }

    public function division(){
        return $this->belongsTo(Division::class);
    }

    public function district(){
        return $this->belongsTo(District::class);
    }



}
