<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\ResidentOtpMail;
use App\Models\Backend\Otp;
use App\Models\Backend\RoomBookingHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class ResidentAuthController extends Controller
{
    /**
     * Public Resident Overview page triggered by Header Phone Search
     */
    public function publicOverview(Request $request)
    {
        $phone = trim($request->input('phone', ''));
        if (empty($phone)) {
            return redirect()->route('home')->with('warning', 'অনুগ্রহ করে মোবাইল নম্বর লিখুন।');
        }

        $phoneVariants = $this->getPhoneVariants($phone);
        if (empty($phoneVariants)) {
            return redirect()->route('home')->with('warning', 'সঠিক মোবাইল নম্বর দিন (যেমন: 017xxxxxxxx)');
        }

        // Find Active & Approved Booking (status = 1 and today_check_out is null)
        $userBooking = RoomBookingHistory::with(['division', 'district', 'thana', 'floor', 'room'])
            ->where('status', 1)
            ->whereNull('today_check_out')
            ->where(function ($q) use ($phoneVariants) {
                foreach ($phoneVariants as $p) {
                    $q->orWhere('phone', $p);
                }
            })
            ->latest('id')
            ->first();

        if (!$userBooking) {
            return redirect()->route('home')->with('warning', 'এই মোবাইল নম্বরে (' . htmlspecialchars($phone) . ') কোনো সক্রিয়/অনুমোদিত (Active) শিক্ষার্থীর তথ্য পাওয়া যায়নি।');
        }

        // Find User
        $user = User::where(function ($q) use ($phoneVariants) {
            foreach ($phoneVariants as $p) {
                $q->orWhere('phone', $p);
            }
        })->first();

        if (!$user) {
            $user = $this->ensureUserFromBooking($userBooking);
        }

        // Calculate Meal Info & Notifications
        $myMealCount = 0;
        $mealDepositBalance = 0;
        $mealDepositWarning = null;
        $isZeroDeposit = false;
        $todayMeal = null;
        $isAdminTurnedOff = false;
        $userNotifications = collect();

        if ($user) {
            $myMealCount = \App\Models\Backend\Meal::where('user_id', $user->id)->count();
            if (class_exists(\App\Services\MealService::class)) {
                $mealDepositInfo = app(\App\Services\MealService::class)->getUserMealDepositBalance($user->id);
                $mealDepositBalance = $mealDepositInfo['balance'] ?? 0;
                $mealDepositWarning = $mealDepositInfo['warning_message'] ?? null;
                $isZeroDeposit = $mealDepositInfo['is_zero_deposit'] ?? false;
                app(\App\Services\MealService::class)->ensureAutoMealGeneratedForUsers(collect([$user]), today());
            }
            $todayMeal = \App\Models\Backend\Meal::where('user_id', $user->id)->whereDate('date', today())->first();

            $activeCheckout = null;
            if (isset($userBooking) && $userBooking) {
                $activeCheckout = \App\Models\Backend\ResidentCheckInOut::where('room_booking_history_id', $userBooking->id)
                    ->where('status', 0)
                    ->latest('check_out_date')
                    ->first();
            }
            $isCurrentlyOutside = false;
            if ($activeCheckout) {
                $checkoutDate = Carbon::parse($activeCheckout->check_out_date)->startOfDay();
                if ($checkoutDate->lte(Carbon::today())) {
                    $isCurrentlyOutside = true;
                }
            }

            // Admin Turned Off Calculation
            $mealNoteLower = strtolower($todayMeal ? ($todayMeal->note ?? '') : '');
            $isCheckoutMealOff = str_contains($mealNoteLower, 'check') || str_contains($mealNoteLower, 'leave') || str_contains($mealNoteLower, 'বাড়ি') || str_contains($mealNoteLower, 'ছুটি');

            if (!$isCurrentlyOutside && !$isCheckoutMealOff && !$isZeroDeposit && $todayMeal && $todayMeal->is_off) {
                if ($todayMeal->note === 'Admin Turned OFF' || (str_contains($todayMeal->note ?? '', 'Admin') && !str_contains($todayMeal->note ?? '', 'Zero Deposit')) || str_contains($todayMeal->note ?? '', 'কর্তৃপক্ষ')) {
                    $isAdminTurnedOff = true;
                } elseif ($todayMeal->made_by && $todayMeal->made_by != $user->id) {
                    $isAdminTurnedOff = true;
                } else {
                    $userReq = \App\Models\Backend\MealRequest::where('user_id', $user->id)
                        ->where('date', '<=', today()->format('Y-m-d'))
                        ->where(function($q) {
                            $q->whereNull('end_date')->orWhere('end_date', '>=', today()->format('Y-m-d'));
                        })
                        ->where('status', 1)
                        ->first();
                    if (!$userReq) {
                        $isAdminTurnedOff = true;
                    }
                }
            }

            if (class_exists(\App\Models\Backend\MealRequestNotification::class)) {
                $userNotifications = \App\Models\Backend\MealRequestNotification::where('user_id', $user->id)
                    ->where('is_read', 0)
                    ->get();
            }
        }

        // Payment & Complaints Info
        $myTotalPaid = 0;
        $myOutstandingDue = 0;
        $complaintsCount = 0;

        $myCurrentMonthElectBill = 0;
        $myTotalElectBill = 0;
        $myElectPaymentsCount = 0;

        if ($userBooking) {
            $myTotalPaid = \App\Models\Backend\MonthlyPayment::where('room_booking_history_id', $userBooking->id)->sum('paid_amount');
            $myOutstandingDue = \App\Models\Backend\MonthlyPayment::where('room_booking_history_id', $userBooking->id)
                ->where('status', '!=', 'paid')
                ->sum('due_amount');
            $complaintsCount = \App\Models\Backend\Complaint::where('room_booking_history_id', $userBooking->id)->count();

            // Electricity Bills
            $currentMonthStr = Carbon::now()->format('Y-m');
            $curElectPayment = \App\Models\Backend\MonthlyPayment::where('room_booking_history_id', $userBooking->id)
                ->where('payment_month', $currentMonthStr)
                ->first();
            $myCurrentMonthElectBill = $curElectPayment ? (float)($curElectPayment->electricity_bill ?? 0) : 0;
            $myTotalElectBill = (float) \App\Models\Backend\MonthlyPayment::where('room_booking_history_id', $userBooking->id)->sum('electricity_bill');
            $myElectPaymentsCount = \App\Models\Backend\MonthlyPayment::where('room_booking_history_id', $userBooking->id)->where('electricity_bill', '>', 0)->count();
        }

        // Special Meals (Only for active non-checked-out residents)
        $isActiveResident = $userBooking && empty($userBooking->today_check_out) && empty($userBooking->deleted_at);
        $upcomingSpecialMeals = collect();
        if ($isActiveResident) {
            $upcomingSpecialMeals = \App\Models\Backend\SpecialMeal::where('status', 1)
                ->whereDate('date', '>=', Carbon::today()->format('Y-m-d'))
                ->orderBy('date', 'asc')
                ->get();
        }

        // Notices
        $noticesCount = 0;
        $latestNotice = null;
        if (class_exists(\App\Models\Backend\ResidentNotice::class)) {
            $noticesQuery = \App\Models\Backend\ResidentNotice::where('status', 1);
            $noticesCount = $noticesQuery->count();
            $latestNotice = $noticesQuery->latest('id')->first();
        } elseif (class_exists(\App\Models\Frontend\Notice::class)) {
            $noticesQuery = \App\Models\Frontend\Notice::where('status', 1);
            $noticesCount = $noticesQuery->count();
            $latestNotice = $noticesQuery->latest('id')->first();
        }

        if ($request->ajax() || $request->wantsJson()) {
            $isHalfMeal = $todayMeal && (bool)$todayMeal->half_meal;
            $mealNote = strtolower($todayMeal ? ($todayMeal->note ?? '') : '');
            $isHalfNight = $isHalfMeal && ($mealNote === 'night' || $mealNote === 'half_night');

            $badgeText = '✅ Full Meal (অটো চালু)';
            $badgeClass = 'bg-success';
            if ($todayMeal && $todayMeal->is_off) {
                $badgeText = '🛑 মিল বন্ধ (OFF)';
                $badgeClass = 'bg-danger';
            } elseif ($isHalfMeal) {
                if ($isHalfNight) {
                    $badgeText = '🌙 হাফ মিল (Night)';
                    $badgeClass = 'bg-info text-white';
                } else {
                    $badgeText = '☀️ হাফ মিল (Day)';
                    $badgeClass = 'bg-warning text-dark';
                }
            } elseif ($isZeroDeposit) {
                $badgeText = '🚫 ডিপোজিট নেই';
                $badgeClass = 'bg-danger';
            }

            return response()->json([
                'success'               => true,
                'meal_count'            => $myMealCount,
                'deposit_balance'       => number_format($mealDepositBalance, 2),
                'deposit_warning'       => $mealDepositWarning,
                'is_admin_turned_off'   => $isAdminTurnedOff,
                'today_meal_badge_text'  => $badgeText,
                'today_meal_badge_class' => $badgeClass,
            ]);
        }

        return view('Frontend.page.resident_overview', [
            'searchPhone'             => $phone,
            'userBooking'             => $userBooking,
            'residentUser'            => $user,
            'myMealCount'             => $myMealCount,
            'mealDepositBalance'      => $mealDepositBalance,
            'mealDepositWarning'      => $mealDepositWarning,
            'isZeroDeposit'           => $isZeroDeposit,
            'todayMeal'               => $todayMeal,
            'isAdminTurnedOff'        => $isAdminTurnedOff,
            'userNotifications'       => $userNotifications,
            'myTotalPaid'             => $myTotalPaid,
            'myOutstandingDue'        => $myOutstandingDue,
            'myCurrentMonthElectBill' => $myCurrentMonthElectBill,
            'myTotalElectBill'        => $myTotalElectBill,
            'myElectPaymentsCount'    => $myElectPaymentsCount,
            'complaintsCount'         => $complaintsCount,
            'upcomingSpecialMeals'    => $upcomingSpecialMeals,
            'noticesCount'            => $noticesCount,
            'latestNotice'            => $latestNotice,
        ]);
    }

    /**
     * Search resident by phone number and return preview profile + booking summary
     */
    public function searchPhone(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $phoneVariants = $this->getPhoneVariants($request->phone);
        if (empty($phoneVariants)) {
            return response()->json([
                'success' => false,
                'message' => 'সঠিক মোবাইল নম্বর দিন (যেমন: 017xxxxxxxx)',
            ], 422);
        }

        // Search in RoomBookingHistory for ACTIVE & APPROVED booking (status = 1 and today_check_out is null)
        $booking = RoomBookingHistory::where('status', 1)
            ->whereNull('today_check_out')
            ->where(function ($q) use ($phoneVariants) {
                foreach ($phoneVariants as $p) {
                    $q->orWhere('phone', $p);
                }
            })
            ->latest('id')
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'এই মোবাইল নম্বরে কোনো সক্রিয়/অনুমোদিত (Active) শিক্ষার্থীর বুকিং পাওয়া যায়নি।',
            ], 404);
        }

        // Search User
        $user = User::where(function ($q) use ($phoneVariants) {
            foreach ($phoneVariants as $p) {
                $q->orWhere('phone', $p);
            }
        })->first();

        // If user doesn't exist, ensure user exists for login
        if (!$user) {
            $user = $this->ensureUserFromBooking($booking);
        }

        // Parse booking details
        $bookingData = $this->extractBookingSummary($booking, $user);
        $rawEmail = $user->email ?? ($booking->email ?? '');
        $hasRealEmail = !empty($rawEmail) && !str_ends_with($rawEmail, '@tssvilla.local') && filter_var($rawEmail, FILTER_VALIDATE_EMAIL);

        return response()->json([
            'success' => true,
            'resident' => [
                'name'              => $user->name ?? $user->full_name ?? $booking->full_name ?? 'Resident',
                'phone'             => $booking->phone ?? $user->phone ?? $request->phone,
                'email'             => $hasRealEmail ? $rawEmail : '',
                'email_masked'      => $hasRealEmail ? $this->maskEmail($rawEmail) : '',
                'has_email'         => (bool)$hasRealEmail,
                'avatar'            => $this->getAvatarUrl($user, $booking),
                'user_type'         => $booking->user_type ?? 'Student',
                'status_label'      => $bookingData['status_label'],
                'floor'             => $bookingData['floor'],
                'room'              => $bookingData['room'],
                'seat'              => $bookingData['seat'],
                'monthly_amount'    => $bookingData['monthly_amount'],
                'advance_fee'       => $bookingData['advance_fee'],
                'development_fee'   => $bookingData['development_fee'],
                'total_booking_fee' => $bookingData['total_booking_fee'],
            ]
        ]);
    }

    /**
     * Password login for resident
     */
    public function loginPassword(Request $request)
    {
        $request->validate([
            'phone'    => 'required|string',
            'password' => 'required|string',
        ]);

        $phoneVariants = $this->getPhoneVariants($request->phone);
        $user = User::where(function ($q) use ($phoneVariants) {
            foreach ($phoneVariants as $p) {
                $q->orWhere('phone', $p);
            }
        })->first();

        // If user not found, try to sync from RoomBookingHistory
        if (!$user) {
            $booking = RoomBookingHistory::where(function ($q) use ($phoneVariants) {
                foreach ($phoneVariants as $p) {
                    $q->orWhere('phone', $p);
                }
            })->latest('id')->first();

            if ($booking) {
                $user = $this->ensureUserFromBooking($booking);
            }
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'ব্যবহারকারী পাওয়া যায়নি। মোবাইল নম্বর চেক করুন।',
            ], 404);
        }

        // If user has active approved booking, ensure status is active
        $hasActiveBooking = RoomBookingHistory::where(function ($q) use ($phoneVariants) {
            foreach ($phoneVariants as $p) {
                $q->orWhere('phone', $p);
            }
        })->where('status', 1)->whereNull('today_check_out')->exists();

        if ($hasActiveBooking && (int)$user->status === 0) {
            $user->status = 1;
            $user->save();
        }

        // Check account status
        if ((int)$user->status === 0) {
            return response()->json([
                'success' => false,
                'message' => 'আপনার অ্যাকাউন্টটি নিষ্ক্রিয় করা আছে। অনুগ্রহ করে অ্যাডমিনের সাথে যোগাযোগ করুন।',
            ], 403);
        }

        // Verify password
        $passwordMatches = Hash::check($request->password, $user->password);

        // Fallback: check if stored plain temp_password or booking password matches, then re-hash
        if (!$passwordMatches) {
            if ($user->temp_password && $user->temp_password === $request->password) {
                $user->password = Hash::make($request->password);
                $user->save();
                $passwordMatches = true;
            } else {
                $booking = RoomBookingHistory::where('phone', $user->phone)->latest('id')->first();
                if ($booking && $booking->password && $booking->password === $request->password) {
                    $user->password = Hash::make($request->password);
                    $user->temp_password = $request->password;
                    $user->save();
                    $passwordMatches = true;
                }
            }
        }

        if (!$passwordMatches) {
            return response()->json([
                'success' => false,
                'message' => 'ভুল পাসওয়ার্ড! সঠিক পাসওয়ার্ড দিন অথবা ওটিপি দিয়ে লগইন করুন।',
            ], 422);
        }

        // Authenticate user
        Auth::login($user, $request->boolean('remember', true));
        $request->session()->regenerate();

        return response()->json([
            'success'      => true,
            'message'      => 'লগইন সফল হয়েছে!',
            'redirect_url' => $request->input('redirect_to') ?: route('dashboard'),
        ]);
    }

    /**
     * Send OTP to resident email for login
     */
    public function sendLoginOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $phoneVariants = $this->getPhoneVariants($request->phone);
        $user = User::where(function ($q) use ($phoneVariants) {
            foreach ($phoneVariants as $p) {
                $q->orWhere('phone', $p);
            }
        })->first();

        $booking = null;
        if (!$user) {
            $booking = RoomBookingHistory::where(function ($q) use ($phoneVariants) {
                foreach ($phoneVariants as $p) {
                    $q->orWhere('phone', $p);
                }
            })->latest('id')->first();

            if ($booking) {
                $user = $this->ensureUserFromBooking($booking);
            }
        } else {
            $booking = RoomBookingHistory::where('phone', $user->phone)->latest('id')->first();
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'ব্যবহারকারী পাওয়া যায়নি।',
            ], 404);
        }

        $email = $user->email ?? ($booking->email ?? null);
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'success' => false,
                'message' => 'আপনার অ্যাকাউন্টে কোনো ইমেইল যুক্ত নেই। পাসওয়ার্ড দিয়ে লগইন করুন অথবা অ্যাডমিনের সাথে যোগাযোগ করুন।',
            ], 422);
        }

        // Generate 6 digit OTP
        $otpCode = (string)random_int(100000, 999999);

        Otp::where('email', $email)->orWhere('user_id', $user->id)->delete();
        Otp::create([
            'user_id'    => $user->id,
            'email'      => $email,
            'otp'        => $otpCode,
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        // Send Email
        try {
            Mail::to($email)->send(new ResidentOtpMail([
                'resident_name' => $user->name ?? $user->full_name ?? ($booking->full_name ?? 'Resident'),
                'otp'           => $otpCode,
                'type'          => 'login',
            ]));
        } catch (\Exception $e) {
            Log::error('Resident Login OTP Mail Error: ' . $e->getMessage());
            // In local/offline environment, still allow OTP to be tested
        }

        return response()->json([
            'success'      => true,
            'message'      => 'আপনার নিবন্ধিত ইমেইলে ৬ ডিজিটের ওটিপি কোড পাঠানো হয়েছে।',
            'email_masked' => $this->maskEmail($email),
        ]);
    }

    /**
     * Verify Login OTP
     */
    public function verifyLoginOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'otp'   => 'required|string|size:6',
        ]);

        $phoneVariants = $this->getPhoneVariants($request->phone);
        $user = User::where(function ($q) use ($phoneVariants) {
            foreach ($phoneVariants as $p) {
                $q->orWhere('phone', $p);
            }
        })->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'ব্যবহারকারী পাওয়া যায়নি।',
            ], 404);
        }

        $email = $user->email;
        $otpRecord = Otp::where(function ($q) use ($user, $email) {
            $q->where('user_id', $user->id);
            if ($email) {
                $q->orWhere('email', $email);
            }
        })
        ->where('otp', $request->otp)
        ->latest('id')
        ->first();

        if (!$otpRecord) {
            return response()->json([
                'success' => false,
                'message' => 'ভুল ওটিপি কোড! অনুগ্রহ করে আবার চেষ্টা করুন।',
            ], 422);
        }

        if ($otpRecord->expires_at && Carbon::now()->greaterThan($otpRecord->expires_at)) {
            $otpRecord->delete();
            return response()->json([
                'success' => false,
                'message' => 'ওটিপি কোডের মেয়াদ শেষ হয়ে গেছে। পুনরায় ওটিপি পাঠান।',
            ], 422);
        }

        // Clean up OTP
        $otpRecord->delete();

        // If user has active approved booking, ensure status is active
        $hasActiveBooking = RoomBookingHistory::where(function ($q) use ($phoneVariants) {
            foreach ($phoneVariants as $p) {
                $q->orWhere('phone', $p);
            }
        })->where('status', 1)->whereNull('today_check_out')->exists();

        if ($hasActiveBooking && (int)$user->status === 0) {
            $user->status = 1;
            $user->save();
        }

        // Login user
        Auth::login($user, true);
        $request->session()->regenerate();

        return response()->json([
            'success'      => true,
            'message'      => 'ওটিপি যাচাই ও লগইন সফল হয়েছে!',
            'redirect_url' => route('dashboard'),
        ]);
    }

    /**
     * Send OTP for Forgot Password
     */
    public function sendForgotOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $phoneVariants = $this->getPhoneVariants($request->phone);
        $user = User::where(function ($q) use ($phoneVariants) {
            foreach ($phoneVariants as $p) {
                $q->orWhere('phone', $p);
            }
        })->first();

        $booking = null;
        if (!$user) {
            $booking = RoomBookingHistory::where(function ($q) use ($phoneVariants) {
                foreach ($phoneVariants as $p) {
                    $q->orWhere('phone', $p);
                }
            })->latest('id')->first();

            if ($booking) {
                $user = $this->ensureUserFromBooking($booking);
            }
        } else {
            $booking = RoomBookingHistory::where('phone', $user->phone)->latest('id')->first();
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'ব্যবহারকারী পাওয়া যায়নি।',
            ], 404);
        }

        $email = $user->email ?? ($booking->email ?? null);
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL) || str_ends_with($email, '@tssvilla.local')) {
            return response()->json([
                'success'   => false,
                'has_email' => false,
                'message'   => 'আপনার অ্যাকাউন্টে কোনো ইমেইল যুক্ত নেই! ওটিপি ফোনে পাঠানো যায় না, শুধুমাত্র ইমেইলে পাঠানো হয়। অনুগ্রহ করে অ্যাডমিনের সাথে যোগাযোগ করুন।',
            ], 422);
        }

        $otpCode = (string)random_int(100000, 999999);

        Otp::where('email', $email)->orWhere('user_id', $user->id)->delete();
        Otp::create([
            'user_id'    => $user->id,
            'email'      => $email,
            'otp'        => $otpCode,
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        try {
            Mail::to($email)->send(new ResidentOtpMail([
                'resident_name' => $user->name ?? $user->full_name ?? ($booking->full_name ?? 'Resident'),
                'otp'           => $otpCode,
                'type'          => 'forgot_password',
            ]));
        } catch (\Exception $e) {
            Log::error('Resident Forgot Password OTP Mail Error: ' . $e->getMessage());
        }

        return response()->json([
            'success'      => true,
            'has_email'    => true,
            'message'      => 'পাসওয়ার্ড রিসেটের ওটিপি সফলভাবে আপনার ইমেইলে পাঠানো হয়েছে।',
            'email_masked' => $this->maskEmail($email),
        ]);
    }

    /**
     * Reset Password with OTP
     */
    public function resetPasswordWithOtp(Request $request)
    {
        $request->validate([
            'phone'            => 'required|string',
            'otp'              => 'required|string|size:6',
            'new_password'     => 'required|string|min:6|max:6',
            'confirm_password' => 'required|string|same:new_password',
        ], [
            'new_password.min'      => 'পাসওয়ার্ড অবশ্যই ৬ অক্ষরের হতে হবে।',
            'new_password.max'      => 'পাসওয়ার্ড ৬ অক্ষরের বেশি হতে পারবে না।',
            'confirm_password.same' => 'কনফার্ম পাসওয়ার্ড মিলছে না।',
        ]);

        $phoneVariants = $this->getPhoneVariants($request->phone);
        $user = User::where(function ($q) use ($phoneVariants) {
            foreach ($phoneVariants as $p) {
                $q->orWhere('phone', $p);
            }
        })->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'ব্যবহারকারী পাওয়া যায়নি।',
            ], 404);
        }

        $email = $user->email;
        $otpRecord = Otp::where(function ($q) use ($user, $email) {
            $q->where('user_id', $user->id);
            if ($email) {
                $q->orWhere('email', $email);
            }
        })
        ->where('otp', $request->otp)
        ->latest('id')
        ->first();

        if (!$otpRecord) {
            return response()->json([
                'success' => false,
                'message' => 'ভুল ওটিপি কোড! অনুগ্রহ করে আবার চেষ্টা করুন।',
            ], 422);
        }

        if ($otpRecord->expires_at && Carbon::now()->greaterThan($otpRecord->expires_at)) {
            $otpRecord->delete();
            return response()->json([
                'success' => false,
                'message' => 'ওটিপি কোডের মেয়াদ শেষ হয়ে গেছে। পুনরায় ওটিপি পাঠান।',
            ], 422);
        }

        // Update password in User table
        $user->password = Hash::make($request->new_password);
        $user->temp_password = $request->new_password;
        $user->save();

        // Also update in RoomBookingHistory across all phone variants
        RoomBookingHistory::where(function ($q) use ($phoneVariants) {
            foreach ($phoneVariants as $p) {
                $q->orWhere('phone', $p);
            }
        })->update([
            'password' => $request->new_password
        ]);

        // Clean up OTP
        $otpRecord->delete();

        // Auto login
        Auth::login($user, true);
        $request->session()->regenerate();

        return response()->json([
            'success'      => true,
            'message'      => 'পাসওয়ার্ড সফলভাবে পরিবর্তন করা হয়েছে এবং লগইন সম্পন্ন হয়েছে!',
            'redirect_url' => route('dashboard'),
        ]);
    }

    /**
     * Helpers
     */
    private function getPhoneVariants($phone)
    {
        $raw = preg_replace('/[^0-9]/', '', (string)$phone);
        if (strlen($raw) < 8) {
            return [];
        }

        $last10 = substr($raw, -10);
        $variants = [
            $raw,
            '0' . $last10,
            '+880' . $last10,
            '880' . $last10,
            $last10,
        ];

        return array_values(array_unique($variants));
    }

    private function ensureUserFromBooking(RoomBookingHistory $booking)
    {
        $phone = $booking->phone;
        $email = $booking->email ?: 'user_' . substr(preg_replace('/[^0-9]/', '', $phone), -10) . '@tssvilla.local';

        $user = User::where('email', $email)->orWhere('phone', $phone)->first();
        if (!$user) {
            $password = $booking->password ?: '123456';
            $user = User::create([
                'name'          => $booking->full_name ?? 'Resident',
                'full_name'     => $booking->full_name ?? 'Resident',
                'email'         => $email,
                'phone'         => $phone,
                'status'        => 1,
                'password'      => Hash::make($password),
                'temp_password' => $password,
                'user_image'    => $booking->image ?? null,
                'image'         => $booking->image ?? null,
            ]);

            try {
                if (class_exists(Role::class)) {
                    $role = Role::firstOrCreate(['name' => 'User']);
                    $user->assignRole($role);
                }
            } catch (\Exception $e) {}
        }

        return $user;
    }

    private function extractBookingSummary(?RoomBookingHistory $booking, ?User $user)
    {
        if (!$booking) {
            return [
                'status_label'      => 'User Account',
                'floor'             => '-',
                'room'              => '-',
                'seat'              => '-',
                'monthly_amount'    => 0,
                'advance_fee'       => 0,
                'development_fee'   => 0,
                'total_booking_fee' => 0,
            ];
        }

        $items = [];
        if (is_string($booking->floor_number_room_number_roomprice)) {
            $items = json_decode($booking->floor_number_room_number_roomprice, true) ?? [];
        } elseif (is_array($booking->floor_number_room_number_roomprice)) {
            $items = $booking->floor_number_room_number_roomprice;
        }

        $floor = collect($items)->pluck('floornumber')->filter()->unique()->implode(', ');
        if (!$floor) {
            $floor = $booking->floornumber ?? ($booking->floor->name ?? '-');
        }

        $room = collect($items)->map(function ($i) {
            return \App\Models\Backend\RoomBookingHistory::parseRoomSeatLabel($i['roomnumber'] ?? '', $i['room_type'] ?? null, $i['seat_id'] ?? null);
        })->filter()->unique()->implode(', ');
        if (!$room) {
            $room = $booking->room_number ?? ($booking->room->room_number ?? '-');
        }

        $seat = collect($items)->map(function ($i) {
            $lbl = \App\Models\Backend\RoomBookingHistory::parseRoomSeatLabel($i['roomnumber'] ?? '', $i['room_type'] ?? null, $i['seat_id'] ?? null);
            return (str_contains($lbl, '-') && preg_match('/-\d+$/', $lbl)) ? $lbl : '-';
        })->filter(fn($s) => $s !== '-')->implode(', ') ?: '-';

        $monthlyAmount = (float)($booking->monthly_amount ?? 0);
        $advanceFee = (float)($booking->advance_fee ?? 0);
        if ($advanceFee <= 0) {
            $advanceFee = (float)collect($items)->sum(fn($i) => (float)($i['advance_price'] ?? 0));
        }
        $devFee = (float)($booking->development_fee ?? 0);
        $totalFee = $advanceFee + $devFee;

        $statusLabel = 'Active Resident';
        if ($booking->today_check_out) {
            $statusLabel = 'Checked Out';
        } elseif ((int)$booking->status === 0) {
            $statusLabel = 'Pending Booking';
        }

        return [
            'status_label'      => $statusLabel,
            'floor'             => $floor ?: '-',
            'room'              => $room ?: '-',
            'seat'              => $seat ?: '-',
            'monthly_amount'    => $monthlyAmount,
            'advance_fee'       => $advanceFee,
            'development_fee'   => $devFee,
            'total_booking_fee' => $totalFee,
        ];
    }

    private function getAvatarUrl(?User $user, ?RoomBookingHistory $booking)
    {
        if ($user && method_exists($user, 'getAvatarUrlAttribute')) {
            $url = $user->avatar_url;
            if ($url) return $url;
        }

        $img = $user->user_image ?? ($user->image ?? ($booking->image ?? null));
        if ($img) {
            if (file_exists(public_path('storage/user/' . $img))) {
                return asset('storage/user/' . $img);
            }
            if (file_exists(public_path('bookingsimage/' . $img))) {
                return asset('bookingsimage/' . $img);
            }
            if (file_exists(public_path('image/' . $img))) {
                return asset('image/' . $img);
            }
        }

        return asset('image/user.png');
    }

    private function maskEmail(?string $email)
    {
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return '';
        }

        $parts = explode('@', $email);
        $name = $parts[0];
        $domain = $parts[1] ?? '';

        $len = strlen($name);
        if ($len <= 2) {
            $maskedName = substr($name, 0, 1) . '*';
        } elseif ($len <= 4) {
            $maskedName = substr($name, 0, 1) . str_repeat('*', $len - 1);
        } else {
            $maskedName = substr($name, 0, 2) . str_repeat('*', $len - 3) . substr($name, -1);
        }

        return $maskedName . '@' . $domain;
    }
}
