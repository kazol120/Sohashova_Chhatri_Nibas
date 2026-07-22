<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function user()
    {
        return $this->usersWithRole();
    }

    public function releaseList()
    {
        // Released bookings phones
        $releasedBookingPhones = \App\Models\Backend\RoomBookingHistory::where('status', 1)
            ->pluck('phone')
            ->map(function($p) {
                return substr(preg_replace('/[^0-9]/', '', $p), -10);
            })
            ->filter()
            ->unique()
            ->toArray();

        if (empty($releasedBookingPhones)) {
            return collect();
        }

        return User::role('HotelGuest')
            ->get()
            ->filter(function($user) use ($releasedBookingPhones) {
                $normPhone = substr(preg_replace('/[^0-9]/', '', $user->phone), -10);
                return in_array($normPhone, $releasedBookingPhones);
            })
            ->values();
    }

    public function usersWithRole()
    {
        // Active bookings phones
        $activeBookingPhones = \App\Models\Backend\RoomBookingHistory::where('status', 0)
            ->pluck('phone')
            ->map(function($p) {
                return substr(preg_replace('/[^0-9]/', '', $p), -10);
            })
            ->filter()
            ->unique()
            ->toArray();

        if (empty($activeBookingPhones)) {
            return collect();
        }

        return User::role('HotelGuest')
            ->where('status', 1)
            ->get()
            ->filter(function($user) use ($activeBookingPhones) {
                $normPhone = substr(preg_replace('/[^0-9]/', '', $user->phone), -10);
                return in_array($normPhone, $activeBookingPhones);
            })
            ->values();
    }

    public function usersWithoutRole()
    {
        return User::where('id', '>', 1)
            ->whereDoesntHave('roles')
            ->get();
    }

    public function userById($id)
    {
        return User::findOrFail($id);
    }

    public function userByPhone($phone)
    {
        // Normalize search phone to last 10 digits
        $normSearch = substr(preg_replace('/[^0-9]/', '', $phone), -10);
        
        $user = User::get()->filter(function($u) use ($normSearch) {
            $normPhone = substr(preg_replace('/[^0-9]/', '', $u->phone), -10);
            return $normPhone === $normSearch;
        })->first();

        if ($user) {
            return $user->makeHidden(['password', 'email_verified_at', 'temp_password']);
        }

        return null;
    }
}
