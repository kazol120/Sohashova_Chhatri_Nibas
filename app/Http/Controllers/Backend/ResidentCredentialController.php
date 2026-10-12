<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Mail\ResidentCredentialMail;
use App\Models\Backend\RoomBookingHistory;
use App\Models\Backend\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ResidentCredentialController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $filter = $request->input('filter', 'all'); // 'all', 'has_email', 'no_email'

        // Query based on filter
        if ($filter === 'deleted' || $filter === 'inactive') {
            $query = RoomBookingHistory::onlyTrashed()
                ->with(['floor', 'room', 'user'])
                ->latest('deleted_at');
        } elseif ($filter === 'released') {
            $query = RoomBookingHistory::with(['floor', 'room', 'user'])
                ->whereNotNull('today_check_out')
                ->latest('id');
        } else {
            $query = RoomBookingHistory::with(['floor', 'room', 'user'])
                ->whereNull('today_check_out')
                ->latest('id');

            if ($filter === 'active') {
                $query->where('status', 1);
            } elseif ($filter === 'pending') {
                $query->where(function ($q) {
                    $q->where('status', 0)->orWhereNull('status');
                });
            }
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('floor_number_room_number_roomprice', 'like', "%{$search}%");
            });
        }

        $allBookings = $query->get();

        // Map and prepare clean resident credential objects
        $residents = $allBookings->map(function ($booking) {
            $phone = $booking->phone;
            $rawPhone = preg_replace('/[^0-9]/', '', (string)$phone);
            $cleanPhone = strlen($rawPhone) >= 10 ? '0' . substr($rawPhone, -10) : $rawPhone;

            // Associated User
            $user = $booking->user ?? User::where('phone', $phone)->orWhere('phone', $cleanPhone)->first();

            $items = [];
            if (is_string($booking->floor_number_room_number_roomprice)) {
                $items = json_decode($booking->floor_number_room_number_roomprice, true) ?? [];
            } elseif (is_array($booking->floor_number_room_number_roomprice)) {
                $items = $booking->floor_number_room_number_roomprice;
            }

            $floor = collect($items)->pluck('floornumber')->filter()->unique()->implode(', ') ?: ($booking->floornumber ?? ($booking->floor->name ?? '-'));

            $room = collect($items)->map(function ($i) {
                return \App\Models\Backend\RoomBookingHistory::parseRoomSeatLabel($i['roomnumber'] ?? '', $i['room_type'] ?? null, $i['seat_id'] ?? null);
            })->filter()->unique()->implode(', ') ?: ($booking->room_number ?? '-');

            $seat = collect($items)->map(function ($i) {
                $lbl = \App\Models\Backend\RoomBookingHistory::parseRoomSeatLabel($i['roomnumber'] ?? '', $i['room_type'] ?? null, $i['seat_id'] ?? null);
                return (str_contains($lbl, '-') && preg_match('/-\d+$/', $lbl)) ? $lbl : '-';
            })->filter(fn($s) => $s !== '-')->implode(', ') ?: '-';

            $roomSeatFull = $booking->formatted_room_seat;

            // Email check
            $rawEmail = $booking->email ?? ($user->email ?? '');
            $hasRealEmail = !empty($rawEmail) && !str_ends_with($rawEmail, '@tssvilla.local') && filter_var($rawEmail, FILTER_VALIDATE_EMAIL);
            $email = $hasRealEmail ? $rawEmail : '';

            // Password resolution
            $plainPassword = ($user && !empty($user->temp_password)) ? $user->temp_password : ($booking->password ?: '123456');

            $isDeleted  = !empty($booking->deleted_at);
            $isReleased = !$isDeleted && !empty($booking->today_check_out);
            $isActive   = !$isDeleted && !$isReleased && ((int)$booking->status === 1);
            $isPending  = !$isDeleted && !$isReleased && ((int)$booking->status === 0 || is_null($booking->status));

            // Status
            $statusLabel = 'Pending';
            $statusBadge = 'bg-label-warning';
            if ($isDeleted) {
                $statusLabel = 'Deleted';
                $statusBadge = 'bg-label-danger';
            } elseif ($isReleased) {
                $statusLabel = 'Released';
                $statusBadge = 'bg-label-secondary';
            } elseif ($isActive) {
                $statusLabel = 'Active';
                $statusBadge = 'bg-label-success';
            }

            return (object)[
                'id'             => $booking->id,
                'user_id'        => $user->id ?? null,
                'name'           => $booking->full_name ?? ($user->name ?? 'শিক্ষার্থী'),
                'phone'          => $cleanPhone,
                'email'          => $email,
                'has_email'      => (bool)$hasRealEmail,
                'floor'          => $floor,
                'room'           => $room,
                'seat'           => $seat,
                'room_seat'      => $roomSeatFull,
                'password'       => $plainPassword,
                'user_type'      => $booking->user_type ?? 'Student',
                'is_active'      => $isActive,
                'is_pending'     => $isPending,
                'is_released'    => $isReleased,
                'is_deleted'     => $isDeleted,
                'status_label'   => $statusLabel,
                'status_badge'   => $statusBadge,
                'image'          => $booking->image ?? ($user->user_image ?? null),
            ];
        });

        // Filter by email in-memory if requested
        if ($filter === 'has_email') {
            $residents = $residents->where('has_email', true);
        } elseif ($filter === 'no_email') {
            $residents = $residents->where('has_email', false);
        }

        // Summary stats
        $activeStayBookings = RoomBookingHistory::whereNull('today_check_out')->get();
        $totalCount    = $activeStayBookings->count();
        $activeCount   = $activeStayBookings->where('status', 1)->count();
        $pendingCount  = $activeStayBookings->filter(fn($b) => (int)$b->status === 0 || is_null($b->status))->count();
        $releasedCount = RoomBookingHistory::whereNotNull('today_check_out')->count();
        $deletedCount  = RoomBookingHistory::onlyTrashed()->count();

        $hasEmailCount = $activeStayBookings->filter(function($b) {
            $em = trim($b->email ?? '');
            return !empty($em) && !str_ends_with($em, '@tssvilla.local') && filter_var($em, FILTER_VALIDATE_EMAIL);
        })->count();
        $noEmailCount = $totalCount - $hasEmailCount;

        return view('backend.resident_credentials.index', compact(
            'residents',
            'search',
            'filter',
            'totalCount',
            'activeCount',
            'pendingCount',
            'hasEmailCount',
            'noEmailCount',
            'releasedCount',
            'deletedCount'
        ));
    }

    /**
     * Send credential email to single resident
     */
    public function sendSingleEmail(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|integer|exists:room_booking_histories,id',
        ]);

        $booking = RoomBookingHistory::with(['floor', 'room'])->find($request->booking_id);
        if (!$booking) {
            return response()->json(['success' => false, 'message' => 'শিক্ষার্থীর তথ্য পাওয়া যায়নি।'], 404);
        }

        $user = User::where('phone', $booking->phone)->first();
        $rawEmail = $booking->email ?? ($user->email ?? '');
        $hasRealEmail = !empty($rawEmail) && !str_ends_with($rawEmail, '@tssvilla.local') && filter_var($rawEmail, FILTER_VALIDATE_EMAIL);

        if (!$hasRealEmail) {
            return response()->json([
                'success' => false,
                'message' => 'এই শিক্ষার্থীর কোনো ইমেইল ঠিকানা যুক্ত নেই! অনুগ্রহ করে আগে ইমেইল যুক্ত করুন।'
            ], 422);
        }

        $password = ($user && !empty($user->temp_password)) ? $user->temp_password : ($booking->password ?: '123456');
        $settingRow = Setting::where('slug', 'web_setting')->first();
        $settingData = $settingRow ? json_decode($settingRow->contents, true) : [];
        $adminPhone = $settingData['phone'] ?? '01977270920';

        $roomSeat = $booking->formatted_room_seat;

        try {
            Mail::to($rawEmail)->send(new ResidentCredentialMail([
                'resident_name' => $booking->full_name ?? ($user->name ?? 'শিক্ষার্থী'),
                'phone'         => $booking->phone,
                'email'         => $rawEmail,
                'password'      => $password,
                'room_seat'     => $roomSeat,
                'login_url'     => route('home'),
                'admin_phone'   => $adminPhone,
            ]));

            return response()->json([
                'success' => true,
                'message' => "{$booking->full_name} এর ইমেইলে ({$rawEmail}) সফলভাবে লগইন তথ্য ও পাসওয়ার্ড পাঠানো হয়েছে।"
            ]);
        } catch (\Exception $e) {
            Log::error('Resident Credential Mail Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'ইমেইল পাঠাতে সমস্যা হয়েছে: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send credential email to all active residents with email
     */
    public function sendBulkEmail(Request $request)
    {
        $bookings = RoomBookingHistory::where('status', 1)
            ->where(function($q) {
                $q->whereNull('today_check_out')->orWhere('today_check_out', 0);
            })
            ->get();

        $sentCount = 0;
        $skippedCount = 0;
        $settingRow = Setting::where('slug', 'web_setting')->first();
        $settingData = $settingRow ? json_decode($settingRow->contents, true) : [];
        $adminPhone = $settingData['phone'] ?? '01977270920';

        foreach ($bookings as $booking) {
            $user = User::where('phone', $booking->phone)->first();
            $rawEmail = $booking->email ?? ($user->email ?? '');
            $hasRealEmail = !empty($rawEmail) && !str_ends_with($rawEmail, '@tssvilla.local') && filter_var($rawEmail, FILTER_VALIDATE_EMAIL);

            if (!$hasRealEmail) {
                $skippedCount++;
                continue;
            }

            $password = ($user && !empty($user->temp_password)) ? $user->temp_password : ($booking->password ?: '123456');

            $roomSeat = $booking->formatted_room_seat;

            try {
                Mail::to($rawEmail)->send(new ResidentCredentialMail([
                    'resident_name' => $booking->full_name ?? ($user->name ?? 'শিক্ষার্থী'),
                    'phone'         => $booking->phone,
                    'email'         => $rawEmail,
                    'password'      => $password,
                    'room_seat'     => $roomSeat,
                    'login_url'     => route('home'),
                    'admin_phone'   => $adminPhone,
                ]));
                $sentCount++;
            } catch (\Exception $e) {
                Log::error("Bulk mail failed for {$rawEmail}: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => "মোট {$sentCount} জন শিক্ষার্থীর ইমেইলে সফলভাবে লগইন তথ্য পাঠানো হয়েছে!" . ($skippedCount > 0 ? " ({$skippedCount} জনের কোনো ইমেইল পাওয়া যায়নি)।" : '')
        ]);
    }

    /**
     * Update resident password directly from admin panel
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|integer|exists:room_booking_histories,id',
            'password'   => 'required|string|min:6|max:6',
        ], [
            'password.min' => 'পাসওয়ার্ড অবশ্যই ৬ অক্ষরের হতে হবে।',
            'password.max' => 'পাসওয়ার্ড ৬ অক্ষরের বেশি হতে পারবে না।',
        ]);

        $booking = RoomBookingHistory::find($request->booking_id);
        $newPassword = $request->password;

        $phone = $booking->phone;
        $raw = preg_replace('/[^0-9]/', '', (string)$phone);
        $last10 = strlen($raw) >= 10 ? substr($raw, -10) : $raw;
        $phoneVariants = array_unique([$phone, $raw, '0'.$last10, '+880'.$last10, '880'.$last10, '+88'.$last10, $last10]);

        // 1. Update in ALL matching RoomBookingHistory records
        RoomBookingHistory::where(function ($q) use ($phoneVariants) {
            foreach ($phoneVariants as $p) {
                $q->orWhere('phone', $p);
            }
        })->update([
            'password' => $newPassword
        ]);

        // 2. Update in User table (both hashed password and plain temp_password)
        $users = User::where(function ($q) use ($phoneVariants) {
            foreach ($phoneVariants as $p) {
                $q->orWhere('phone', $p);
            }
        })->get();

        foreach ($users as $user) {
            $user->password = Hash::make($newPassword);
            $user->temp_password = $newPassword;
            $user->save();
        }

        return response()->json([
            'success'  => true,
            'message'  => "{$booking->full_name} এর পাসওয়ার্ড সফলভাবে উভয় টেবিলে আপডেট করা হয়েছে!",
            'password' => $newPassword
        ]);
    }
}
