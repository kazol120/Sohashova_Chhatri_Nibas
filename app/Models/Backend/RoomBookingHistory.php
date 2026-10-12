<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomBookingHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'floor_id',
        'room_id',
        'image',
        'full_name',
        'user_type',
        'institution_name',
        'education_level',
        'education_class',
        'workplace_name',
        'father_name',
        'mother_name',
        'email',
        'blood_group',
        'phone',
        'nid',
        'mother_nid',
        'father_nid',
        'father_phone',
        'mother_phone',
        'division_id',
        'district_id',
        'thana_id',
        'address',
        'pay_cash_in',
        'pay_online',
        'check_in',
        'check_out',
        'status',
        'roomprice',
        'monthly_amount',
        'development_fee',
        'advance_fee',
        'password',
        'floor_number_room_number_roomprice',
        'today_check_out',
        'will_leave',
        'notice_date',
        'is_seen'
    ];




    protected $casts = [
        'check_in'  => 'date',
        'check_out' => 'date',
        'today_check_out' => 'date',
        'notice_date' => 'date',
        'floor_number_room_number_roomprice' => 'array',
        'monthly_amount' => 'float',
        'development_fee' => 'float',
        'advance_fee' => 'float',
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

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'phone', 'phone');
    }

    public static function parseRoomSeatLabel($rawRoomNumber, $roomType = null, $seatId = null)
    {
        $raw = trim((string)$rawRoomNumber);
        if ($raw === '') return '-';

        $block = 'A';
        $roomNum = 1;
        $seatNum = '';
        $isDouble = false;

        $rType = strtolower(trim((string)$roomType));

        // room_type is primary source of truth
        $roomTypeKnown = false;
        if (str_contains($rType, 'single') || str_contains($rType, 'singel')) {
            $isDouble = false; // Single — force no suffix
            $roomTypeKnown = true;
        } elseif (str_contains($rType, 'double') || str_contains($rType, 'doubel')) {
            $isDouble = true;
            $roomTypeKnown = true;
        }

        $seatModel = null;
        if (!empty($seatId)) {
            $seatModel = RoomSeat::find($seatId);
        }

        // Only check seat count when room_type is unclear
        if (!$roomTypeKnown && $seatModel && $seatModel->room) {
            if ($seatModel->room->seats()->count() > 1) {
                $isDouble = true;
            }
        }

        if (preg_match('/^([A-Za-z]+)[\-_](\d+)(?:[\-_]Block)?(?:[\-_](\d+))?$/i', $raw, $m)) {
            $block = strtoupper($m[1]);
            $roomNum = (int)$m[2];
            if (!empty($m[3]) && (int)$m[3] > 0) {
                $seatNum = (int)$m[3];
                if (!$roomTypeKnown && $seatNum >= 2) $isDouble = true;
            }
        } elseif (preg_match('/^(\d+)[\-_ ]*Block[\-_ ]*([A-Za-z]+)(?:[\-_ ]*(\d+))?$/i', $raw, $m)) {
            $roomNum = (int)$m[1];
            $block = strtoupper($m[2]);
            if (!empty($m[3]) && (int)$m[3] > 0) {
                $seatNum = (int)$m[3];
                if (!$roomTypeKnown && $seatNum >= 2) $isDouble = true;
            }
        } elseif (preg_match('/^Block[\-_ ]*([A-Za-z]+)[\-_ ]*(\d+)(?:[\-_ ]*(\d+))?$/i', $raw, $m)) {
            $block = strtoupper($m[1]);
            $roomNum = (int)$m[2];
            if (!empty($m[3]) && (int)$m[3] > 0) {
                $seatNum = (int)$m[3];
                if (!$roomTypeKnown && $seatNum >= 2) $isDouble = true;
            }
        } elseif (preg_match('/^(\d+)[\-_]([A-Za-z]+)(?:[\-_](\d+))?$/i', $raw, $m)) {
            $roomNum = (int)$m[1];
            $block = strtoupper($m[2]);
            if (!empty($m[3]) && (int)$m[3] > 0) {
                $seatNum = (int)$m[3];
                if (!$roomTypeKnown && $seatNum >= 2) $isDouble = true;
            }
        } else {
            return $raw;
        }

        if (empty($seatNum) && $seatModel && !empty($seatModel->seat_no)) {
            $seatNum = (int)$seatModel->seat_no;
        }

        // Single room — never show seat suffix
        if (!$isDouble) {
            return "{$roomNum}-Block {$block}";
        }
        // Double room — show seat suffix if available
        if ($seatNum) {
            return "{$roomNum}-Block {$block}-{$seatNum}";
        }
        return "{$roomNum}-Block {$block}";
    }

    public function getFormattedRoomSeatAttribute()
    {
        $items = [];
        if (is_string($this->floor_number_room_number_roomprice)) {
            $items = json_decode($this->floor_number_room_number_roomprice, true) ?? [];
        } elseif (is_array($this->floor_number_room_number_roomprice)) {
            $items = $this->floor_number_room_number_roomprice;
        }

        $floor = collect($items)->pluck('floornumber')->filter()->unique()->implode(', ');
        if (!$floor) {
            $floor = $this->floornumber ?? ($this->floor->name ?? '');
        }

        $formattedList = [];
        foreach ($items as $item) {
            $lbl = self::parseRoomSeatLabel($item['roomnumber'] ?? '', $item['room_type'] ?? null, $item['seat_id'] ?? null);
            if ($lbl && $lbl !== '-') {
                $formattedList[] = $lbl;
            }
        }

        if (empty($formattedList)) {
            $fallback = $this->room_number ?? ($this->room->room_number ?? '');
            if ($fallback) {
                $formattedList[] = self::parseRoomSeatLabel($fallback);
            }
        }

        $roomStr = implode(', ', array_unique($formattedList));
        if ($floor && $roomStr) {
            return "{$roomStr} ({$floor})";
        }
        return $roomStr ?: 'N/A';
    }



}
