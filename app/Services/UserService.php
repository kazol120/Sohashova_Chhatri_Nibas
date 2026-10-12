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
        // Released bookings phones (where today_check_out is not null)
        $releasedBookingPhones = \App\Models\Backend\RoomBookingHistory::whereNotNull('today_check_out')
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

        return User::whereDoesntHave('roles', function($q) { $q->whereIn('name', ['admin', 'staffs']); })
            ->get()
            ->filter(function($user) use ($releasedBookingPhones) {
                $normPhone = substr(preg_replace('/[^0-9]/', '', $user->phone), -10);
                return in_array($normPhone, $releasedBookingPhones);
            })
            ->values();
    }

    public function usersWithRole()
    {
        // Active approved bookings (where today_check_out is null, status = 1, deleted_at is null)
        $activeBookings = \App\Models\Backend\RoomBookingHistory::whereNull('today_check_out')
            ->whereNull('deleted_at')
            ->where('status', 1)
            ->orderBy('id', 'asc')
            ->get();

        $usersCollection = collect();
        $allUsers = \App\Models\User::all();

        foreach ($activeBookings as $booking) {
            $rawPhone = preg_replace('/[^0-9]/', '', $booking->phone ?? '');
            $p10 = strlen($rawPhone) >= 10 ? substr($rawPhone, -10) : $rawPhone;

            $user = null;
            if (!empty($p10)) {
                $user = $allUsers->first(function($u) use ($p10) {
                    $uClean = preg_replace('/[^0-9]/', '', $u->phone ?? '');
                    $u10 = strlen($uClean) >= 10 ? substr($uClean, -10) : $uClean;
                    return !empty($u10) && $u10 === $p10;
                });
            }

            if (!$user) {
                $password = $booking->password ?: '123456';
                $email = $booking->email;
                if (!empty($email)) {
                    $emailExists = $allUsers->contains(function($u) use ($email) {
                        return !empty($u->email) && strtolower($u->email) === strtolower($email) && (int)$u->status === 1;
                    });
                    if ($emailExists) {
                        $email = null;
                    }
                }

                $user = \App\Models\User::create([
                    'name'          => $booking->full_name ?? 'Resident',
                    'email'         => $email,
                    'phone'         => $booking->phone,
                    'status'        => 1,
                    'password'      => bcrypt($password),
                    'temp_password' => $password,
                    'user_image'    => $booking->image ?? null,
                    'address'       => $booking->address ?? null,
                ]);

                try {
                    if (class_exists(\Spatie\Permission\Models\Role::class)) {
                        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'User']);
                        $user->assignRole($role);
                    }
                } catch (\Throwable $e) {}

                $allUsers->push($user);
            }

            $uInstance = clone $user;
            $uInstance->active_booking = $booking;
            $usersCollection->push($uInstance);
        }

        return $usersCollection;
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

    public function userCreate(Request $request)
    {
        $in = $request->except(['_token', 'roles', 'password']);

        $in['status'] = ($request->has('status') && ($request->status === 'on' || $request->status == 1 || $request->status === '1')) ? 1 : 1;

        if ($request->filled('password')) {
            $in['password'] = bcrypt($request->password);
            $in['temp_password'] = $request->password;
        }

        if ($request->hasFile('user_image')) {
            $image = $request->file('user_image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('storage/user');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            $image->move($destinationPath, $imageName);
            $in['user_image'] = $imageName;
        }

        $user = User::create($in);

        if ($request->filled('roles')) {
            $user->syncRoles($request->roles);
        }

        return $user;
    }

    public function userUpdate(Request $request, $id)
    {
        $user = $this->userById($id);
        $in = $request->except(['_token', 'roles', 'password']);

        if ($request->has('status')) {
            $in['status'] = ($request->status === 'on' || $request->status == 1 || $request->status === '1') ? 1 : 0;
        } else {
            $in['status'] = 0;
        }

        if ($request->filled('password')) {
            $in['password'] = bcrypt($request->password);
            $in['temp_password'] = $request->password;
        }

        if ($request->hasFile('user_image')) {
            if ($user->user_image && file_exists(public_path('storage/user/' . $user->user_image))) {
                @unlink(public_path('storage/user/' . $user->user_image));
            }
            $image = $request->file('user_image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('storage/user');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            $image->move($destinationPath, $imageName);
            $in['user_image'] = $imageName;
        }

        $user->update($in);

        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        return $user;
    }

    public function userDelete($id)
    {
        $user = $this->userById($id);
        if ($user->user_image && file_exists(public_path('storage/user/' . $user->user_image))) {
            @unlink(public_path('storage/user/' . $user->user_image));
        }
        return $user->delete();
    }
}
