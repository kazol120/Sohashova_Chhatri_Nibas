<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\MealService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MealController extends Controller
{
    protected $mealService;
    protected $userService;

    public function __construct(MealService $mealService, UserService $userService)
    {
        $this->mealService = $mealService;
        $this->userService = $userService;
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $data['page_title'] = 'Meal Management';
        $data['users'] = $this->userService->usersWithRole();
        $data['selectedDate'] = $request->date ? Carbon::parse($request->date)->format('Y-m-d') : now()->format('Y-m-d');

        $activeBookings = \App\Models\Backend\RoomBookingHistory::whereNull('today_check_out')->get();
        $allRooms = \App\Models\Backend\Room::pluck('room_no', 'id')->toArray();
        $allSeats = \App\Models\Backend\RoomSeat::pluck('seat_no', 'id')->toArray();

        foreach ($data['users'] as $user) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $user->phone ?? '');
            $cleanPhone10 = strlen($cleanPhone) >= 10 ? substr($cleanPhone, -10) : $cleanPhone;

            $booking = $user->active_booking ?? null;
            if (!$booking) {
                $booking = $activeBookings->first(function($b) use ($cleanPhone10) {
                    $bClean = preg_replace('/[^0-9]/', '', $b->phone ?? '');
                    $bClean10 = strlen($bClean) >= 10 ? substr($bClean, -10) : $bClean;
                    return !empty($cleanPhone10) && !empty($bClean10) && $cleanPhone10 === $bClean10;
                });
            }

            $floors = [];
            $rooms = [];
            $seats = [];

            if ($booking) {
                $user->active_booking = $booking;
                $roomsJson = $booking->floor_number_room_number_roomprice;
                $roomsArr = is_string($roomsJson) ? json_decode($roomsJson, true) : $roomsJson;
                if (is_array($roomsArr)) {
                    foreach ($roomsArr as $rItem) {
                        if (!empty($rItem['floornumber'])) {
                            $floors[] = $rItem['floornumber'];
                        }

                        if (!empty($rItem['roomnumber'])) {
                            $lbl = \App\Models\Backend\RoomBookingHistory::parseRoomSeatLabel($rItem['roomnumber'] ?? '', $rItem['room_type'] ?? null, $rItem['seat_id'] ?? null);
                            $rooms[] = $lbl;
                            if (str_contains($lbl, '-') && preg_match('/-\d+$/', $lbl)) {
                                $seats[] = $lbl;
                            }
                        }
                    }
                }
            }

            $user->booking_floor = !empty($floors) ? implode(', ', array_unique($floors)) : '-';
            $user->booking_room = !empty($rooms) ? implode(', ', array_unique($rooms)) : '-';
            $user->booking_seat = !empty($seats) ? implode(', ', array_unique($seats)) : '-';
        }

        $mealResult = $this->mealService->todayOrPreviousMealStatus($data['selectedDate']);

        $data['meals'] = $mealResult['meals'];
        $data['used_date'] = $mealResult['used_date'];
        $data['is_fallback'] = $mealResult['is_fallback'];
        $data['summary'] = $this->mealService->mealStatusSummary($data['users'], $data['meals']);

        $guestMeals = \App\Models\Backend\GuestMeal::whereDate('date', $data['selectedDate'])
            ->where('status', 1)
            ->get();
        $data['guestMealsByUser'] = $guestMeals->groupBy('user_id');
        $data['totalGuestMealsCount'] = (float) $guestMeals->sum('meal_count');
        $data['totalGuestMealsAmount'] = (float) $guestMeals->sum('amount');

        return view('backend.meal.index', $data);
    }

    public function printDailyMeals(Request $request)
    {
        $selectedDate = $request->date ? Carbon::parse($request->date)->format('Y-m-d') : now()->format('Y-m-d');
        $users = $this->userService->usersWithRole();

        // Ensure auto meals generated for selected date
        $this->mealService->ensureAutoMealGeneratedForUsers($users, $selectedDate);

        $activeBookings = \App\Models\Backend\RoomBookingHistory::whereNull('today_check_out')->get();
        $allRooms = \App\Models\Backend\Room::pluck('room_no', 'id')->toArray();
        $allSeats = \App\Models\Backend\RoomSeat::pluck('seat_no', 'id')->toArray();

        foreach ($users as $user) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $user->phone ?? '');
            $cleanPhone10 = strlen($cleanPhone) >= 10 ? substr($cleanPhone, -10) : $cleanPhone;

            $booking = $user->active_booking ?? null;
            if (!$booking) {
                $booking = $activeBookings->first(function($b) use ($cleanPhone10) {
                    $bClean = preg_replace('/[^0-9]/', '', $b->phone ?? '');
                    $bClean10 = strlen($bClean) >= 10 ? substr($bClean, -10) : $bClean;
                    return !empty($cleanPhone10) && !empty($bClean10) && $cleanPhone10 === $bClean10;
                });
            }

            $floors = [];
            $rooms = [];
            $seats = [];

            if ($booking) {
                $user->active_booking = $booking;
                $roomsJson = $booking->floor_number_room_number_roomprice;
                $roomsArr = is_string($roomsJson) ? json_decode($roomsJson, true) : $roomsJson;
                if (is_array($roomsArr)) {
                    foreach ($roomsArr as $rItem) {
                        if (!empty($rItem['floornumber'])) {
                            $floors[] = $rItem['floornumber'];
                        }

                        if (!empty($rItem['roomnumber'])) {
                            $lbl = \App\Models\Backend\RoomBookingHistory::parseRoomSeatLabel($rItem['roomnumber'] ?? '', $rItem['room_type'] ?? null, $rItem['seat_id'] ?? null);
                            $rooms[] = $lbl;
                            if (str_contains($lbl, '-') && preg_match('/-\d+$/', $lbl)) {
                                $seats[] = $lbl;
                            }
                        }
                    }
                }
            }

            $user->booking_floor = !empty($floors) ? implode(', ', array_unique($floors)) : '-';
            $user->booking_room = !empty($rooms) ? implode(', ', array_unique($rooms)) : '-';
            $user->booking_seat = !empty($seats) ? implode(', ', array_unique($seats)) : '-';
        }

        $mealResult = $this->mealService->todayOrPreviousMealStatus($selectedDate);
        $meals = $mealResult['meals'];
        $summary = $this->mealService->mealStatusSummary($users, $meals);

        $rates = $this->mealService->getMealRatesForDate($selectedDate);
        $fullRate = (float)$rates['full'];
        $halfRate = (float)($rates['half'] ?? ($fullRate / 2));
        $isSpecial = $rates['is_special'];
        $specialTitle = $rates['title'];

        $totalFullOn = (int)($summary['total_full_on'] ?? $summary['total_full'] ?? 0);
        $totalHalfOn = (int)($summary['total_half_on'] ?? 0);
        $totalActiveMeals = $totalFullOn + ($totalHalfOn * 0.5);
        $totalDailyCost = ($totalFullOn * $fullRate) + ($totalHalfOn * $halfRate);

        $guestMeals = \App\Models\Backend\GuestMeal::whereDate('date', $selectedDate)
            ->where('status', 1)
            ->get();
        $guestMealsByUser = $guestMeals->groupBy('user_id');
        $totalGuestMealsCount = (float) $guestMeals->sum('meal_count');
        $totalGuestMealsAmount = (float) $guestMeals->sum('amount');

        $webSetting = app(\App\Services\SettingService::class)->getSettingContentBySlug('web_setting');

        $data = [
            'page_title'            => 'দৈনিক মিল ম্যানেজমেন্ট শিট - ' . Carbon::parse($selectedDate)->format('d M Y'),
            'selectedDate'          => $selectedDate,
            'dateFormatted'         => Carbon::parse($selectedDate)->format('d F, Y (l)'),
            'users'                 => $users,
            'meals'                 => $meals,
            'summary'               => $summary,
            'fullRate'              => $fullRate,
            'halfRate'              => $halfRate,
            'isSpecial'             => $isSpecial,
            'specialTitle'          => $specialTitle,
            'totalActiveMeals'      => $totalActiveMeals,
            'totalDailyCost'        => $totalDailyCost,
            'guestMealsByUser'      => $guestMealsByUser,
            'totalGuestMealsCount'  => $totalGuestMealsCount,
            'totalGuestMealsAmount' => $totalGuestMealsAmount,
            'webSetting'            => $webSetting,
        ];

        return view('backend.meal.print', $data);
    }

    public function create()
    {
        $data['page_title'] = 'Today Meal Create';
        $currentUser = auth()->user();
        $data['canSaveMeal'] = true;
        $data['dueWarningMessage'] = null;

        $data['depositWarningMessage'] = null;

        if (!$currentUser->hasRole('admin') && !$currentUser->hasRole('staffs')) {
            $data['users'] = collect([$currentUser]);

            $depositInfo = $this->mealService->getUserMealDepositBalance($currentUser->id);
            if ($depositInfo['is_zero_deposit']) {
                $data['depositWarningMessage'] = $depositInfo['warning_message'];
            }

            $cleanPhone = preg_replace('/[^0-9]/', '', $currentUser->phone ?? '');
            $cleanPhone10 = strlen($cleanPhone) >= 6 ? (strlen($cleanPhone) > 10 ? substr($cleanPhone, -10) : $cleanPhone) : '';

            $userBooking = \App\Models\Backend\RoomBookingHistory::whereNull('today_check_out')
                ->where(function ($q) use ($currentUser, $cleanPhone10) {
                    if (!empty($cleanPhone10)) {
                        $q->whereRaw("REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+', '') LIKE ?", ["%{$cleanPhone10}%"]);
                    } elseif (!empty($currentUser->email)) {
                        $q->where('email', $currentUser->email);
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })->latest()->first();

            if ($userBooking) {
                $outstandingDue = \App\Models\Backend\MonthlyPayment::where('room_booking_history_id', $userBooking->id)
                    ->where('status', '!=', 'paid')
                    ->sum('due_amount');

                if ($outstandingDue > 0) {
                    $data['canSaveMeal'] = false;
                    $data['dueWarningMessage'] = "⚠️ আপনার ভাড়ার বকেয়া (Outstanding Balance: ৳ " . number_format($outstandingDue) . ") রয়েছে। মিল পরিবর্তন করতে অনুগ্রহ করে আগে বকেয়া পরিশোধ করুন।";
                }
            }

            $todayMeal = \App\Models\Backend\Meal::where('user_id', $currentUser->id)->whereDate('date', today())->first();
            $data['isAdminTurnedOff'] = false;
            // Only set admin turned off if user HAS deposit (not zero deposit) and meal is off
            if (!$depositInfo['is_zero_deposit'] && $todayMeal && $todayMeal->is_off) {
                if ($todayMeal->note === 'Admin Turned OFF' || (str_contains($todayMeal->note ?? '', 'Admin') && !str_contains($todayMeal->note ?? '', 'Zero Deposit')) || str_contains($todayMeal->note ?? '', 'কর্তৃপক্ষ')) {
                    $data['isAdminTurnedOff'] = true;
                } elseif ($todayMeal->made_by && $todayMeal->made_by != $currentUser->id) {
                    $data['isAdminTurnedOff'] = true;
                } else {
                    $userReq = \App\Models\Backend\MealRequest::where('user_id', $currentUser->id)
                        ->where('date', '<=', today()->format('Y-m-d'))
                        ->where(function($q) {
                            $q->whereNull('end_date')->orWhere('end_date', '>=', today()->format('Y-m-d'));
                        })
                        ->where('status', 1)
                        ->first();
                    if (!$userReq) {
                        $data['isAdminTurnedOff'] = true;
                    }
                }
            }
        } else {
            $data['isAdminTurnedOff'] = false;
            $data['users'] = $this->userService->usersWithRole();
        }

        // Ensure auto meals are synced for all users based on their deposit status
        $this->mealService->ensureAutoMealGeneratedForUsers($data['users'], today());

        $userDepositMap = [];
        foreach ($data['users'] as $u) {
            $dep = $this->mealService->getUserMealDepositBalance($u->id);
            $userDepositMap[$u->id] = [
                'balance'         => $dep['balance'],
                'is_zero_deposit' => $dep['is_zero_deposit'],
                'deposit_total'   => $dep['deposit_total'],
            ];
        }
        $data['userDepositMap'] = $userDepositMap;

        $nowTime = now()->format('H:i');
        $data['isMealCutoffPassed'] = ($nowTime < '10:00' || $nowTime >= '20:00');
        $data['isAdminOrStaff'] = $currentUser->hasRole('admin') || $currentUser->hasRole('staffs');

        $mealResult = $this->mealService->todayOrPreviousMealStatus(now());
        $data['meals'] = $mealResult['meals'];
        $data['used_date'] = $mealResult['used_date'];
        $data['is_fallback'] = $mealResult['is_fallback'];
        $data['todaySummary'] = $this->mealService->todayMealSummary(now());
        $data['mealSetting'] = app(\App\Services\SettingService::class)->getSettingContentBySlug('meal_setting');
        $data['upcomingSpecialMeals'] = \App\Models\Backend\SpecialMeal::where('status', 1)
            ->whereDate('date', '>=', now()->format('Y-m-d'))
            ->orderBy('date', 'asc')
            ->get();
        return view('backend.meal.create', $data);
    }

    public function mealHistory(Request $request)
    {
        $data['page_title'] = 'Meal History';
        $data['users'] = $this->userService->usersWithRole();

        $selectedMonth = $request->month
            ? Carbon::parse($request->month . '-01')->format('Y-m')
            : now()->format('Y-m');

        $mealHistory = $this->mealService->monthlyMealHistory($selectedMonth, $data['users']);

        $data['selectedMonth'] = $mealHistory['selectedMonth'];
        $data['half_rate'] = $mealHistory['half_rate'];
        $data['full_rate'] = $mealHistory['full_rate'];
        $data['mealSummaryByUser'] = $mealHistory['mealSummaryByUser'];
        $data['summary'] = $mealHistory['summary'];

        return view('backend.meal.meal-history', $data);
    }

    public function userMealHistoryDetail(Request $request)
    {
        $userId = $request->get('user_id');
        $selectedMonth = $request->get('month', Carbon::now('Asia/Dhaka')->format('Y-m'));

        $user = \App\Models\User::find($userId);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }

        $startDate = Carbon::parse($selectedMonth . '-01')->startOfMonth();
        $endDate   = Carbon::parse($selectedMonth . '-01')->endOfMonth();

        $this->mealService->ensureAutoMealGeneratedForUsers([$user], min($endDate->toDateString(), now()->format('Y-m-d')));

        $mealSetting = app(\App\Services\SettingService::class)->getSettingContentBySlug('meal_setting');
        $fullRate = (float)($mealSetting['full'] ?? 30);
        $halfRate = (float)($mealSetting['half'] ?? ($fullRate / 2));

        $specialMeals = \App\Models\Backend\SpecialMeal::whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('status', 1)
            ->get()
            ->keyBy(function($item) {
                return Carbon::parse($item->date)->format('Y-m-d');
            });

        $meals = \App\Models\Backend\Meal::where('user_id', $userId)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy('date');

        $days = [];
        $totalActiveDays = 0;
        $totalOffDays = 0;
        $totalFullCount = 0;
        $totalHalfCount = 0;
        $totalCost = 0;

        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $dateStr = $current->toDateString();
            $meal = $meals->get($dateStr);
            $spec = $specialMeals->get($dateStr);

            $dailyRate = $spec ? (float)$spec->full_rate : $fullRate;
            $dailyHalfRate = $spec ? (float)($spec->half_rate ?? ($spec->full_rate / 2)) : $halfRate;

            $full = $meal ? (int)$meal->full_meal : 0;
            $half = $meal ? (int)$meal->half_meal : 0;
            $isOff = $meal ? (bool)$meal->is_off : false;
            $note = $meal ? ($meal->note ?? '') : '';
            $lowerNote = strtolower($note);

            $isFuture = $current->gt(today());
            $isFullOn = !$isFuture && !$isOff && ($full > 0);
            $isHalfOn = !$isFuture && !$isOff && ($half > 0);
            $isMealOn = $isFullOn || $isHalfOn;

            $dayMealCount = 0;
            $dayCost = 0;
            $mealType = 'off';

            if ($isFullOn) {
                $totalActiveDays++;
                $totalFullCount++;
                $dayMealCount = 1;
                $dayCost = $dailyRate;
                $mealType = 'full';
            } elseif ($isHalfOn) {
                $totalActiveDays++;
                $totalHalfCount++;
                $dayMealCount = 0.5;
                $dayCost = $dailyHalfRate;
                $mealType = ($lowerNote === 'night' || $lowerNote === 'half_night') ? 'half_night' : 'half_day';
            } else {
                $totalOffDays++;
                $dayMealCount = 0;
                $dayCost = 0;
                $mealType = 'off';
            }

            $totalCost += $dayCost;

            $dayNote = '';
            if (!$isFuture) {
                if ($isMealOn) {
                    if ($spec) {
                        $dayNote = $spec->title ? "⭐ {$spec->title} Special Meal" : "⭐ Special Meal";
                    } elseif ($isHalfOn) {
                        $dayNote = ($mealType === 'half_night') ? 'Half Meal (Night)' : 'Half Meal (Day)';
                    } elseif ($isFullOn) {
                        $dayNote = 'Full Meal';
                    }
                } else {
                    if (!empty($note)) {
                        if (str_contains($lowerNote, 'checked in') || str_contains($lowerNote, 'resumes tomorrow') || (str_contains($lowerNote, 'check') && str_contains($lowerNote, 'in'))) {
                            $dayNote = 'হোস্টেলে চেক-ইন সম্পন্ন (আগামীকাল থেকে মিল চালু)';
                        } elseif (str_contains($lowerNote, 'leave') || str_contains($lowerNote, 'checked out') || str_contains($lowerNote, 'check')) {
                            $dayNote = 'চেক-আউট / বাইরে আছেন (মিল বন্ধ)';
                        } elseif (str_contains($lowerNote, 'zero deposit')) {
                            $dayNote = 'নো ডিপোজিট (Zero Deposit)';
                        } else {
                            $dayNote = $note;
                        }
                    } else {
                        $dayNote = 'মিল বন্ধ (Meal OFF)';
                    }
                }
            }

            $days[] = [
                'date'           => $dateStr,
                'date_formatted' => $current->format('d M Y'),
                'day_name'       => $current->format('D'),
                'full'           => $isFullOn ? 1 : 0,
                'half'           => $isHalfOn ? 1 : 0,
                'is_half'        => $isHalfOn,
                'meal_type'      => $mealType,
                'meal_count'     => $dayMealCount,
                'is_off'         => !$isMealOn,
                'note'           => $dayNote,
                'is_meal_on'     => $isMealOn,
                'day_cost'       => $dayCost,
            ];

            $current->addDay();
        }

        return response()->json([
            'status' => true,
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'phone' => $user->phone ?? '-',
            ],
            'month'           => $selectedMonth,
            'month_formatted' => $startDate->format('F Y'),
            'full_rate'       => $fullRate,
            'half_rate'       => $halfRate,
            'summary' => [
                'total_days'  => count($days),
                'active_days' => $totalActiveDays,
                'off_days'    => $totalOffDays,
                'total_full'  => $totalFullCount,
                'total_half'  => $totalHalfCount,
                'total_meals' => $totalFullCount + ($totalHalfCount * 0.5),
                'total_cost'  => $totalCost,
            ],
            'days' => $days,
        ]);
    }

    public function residentMealDetailView($userId, Request $request)
    {
        $selectedMonth = $request->get('month', Carbon::now('Asia/Dhaka')->format('Y-m'));

        $user = \App\Models\User::findOrFail($userId);

        $startDate = Carbon::parse($selectedMonth . '-01')->startOfMonth();
        $endDate   = Carbon::parse($selectedMonth . '-01')->endOfMonth();

        $this->mealService->ensureAutoMealGeneratedForUsers([$user], min($endDate->toDateString(), now()->format('Y-m-d')));

        $mealSetting = app(\App\Services\SettingService::class)->getSettingContentBySlug('meal_setting');
        $fullRate = (float)($mealSetting['full'] ?? 30);
        $halfRate = (float)($mealSetting['half'] ?? ($fullRate / 2));

        $specialMeals = \App\Models\Backend\SpecialMeal::whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('status', 1)
            ->get()
            ->keyBy(function($item) {
                return Carbon::parse($item->date)->format('Y-m-d');
            });

        $meals = \App\Models\Backend\Meal::where('user_id', $userId)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy('date');

        $depositTotal = (float) \App\Models\Backend\Deposit::where('user_id', $userId)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->sum('amount');

        $guestMeals = \App\Models\Backend\GuestMeal::where('user_id', $userId)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('status', 1)
            ->orderBy('date', 'asc')
            ->get();

        $guestMealsByDate = $guestMeals->groupBy(function($item) {
            return Carbon::parse($item->date)->format('Y-m-d');
        });

        $totalGuestMealsCount = (float) $guestMeals->sum('meal_count');
        $totalGuestMealsAmount = (float) $guestMeals->sum('amount');

        $days = [];
        $totalActiveDays = 0;
        $totalOffDays = 0;
        $totalFullCount = 0;
        $totalHalfCount = 0;
        $totalCost = 0;

        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $dateStr = $current->toDateString();
            $meal = $meals->get($dateStr);
            $spec = $specialMeals->get($dateStr);
            $dayGuestList = $guestMealsByDate->get($dateStr, collect());

            $dailyRate = $spec ? (float)$spec->full_rate : $fullRate;
            $dailyHalfRate = $spec ? (float)($spec->half_rate ?? ($spec->full_rate / 2)) : $halfRate;

            $full = $meal ? (int)$meal->full_meal : 0;
            $half = $meal ? (int)$meal->half_meal : 0;
            $isOff = $meal ? (bool)$meal->is_off : false;
            $note = $meal ? ($meal->note ?? '') : '';
            $lowerNote = strtolower($note);

            $isFuture = $current->gt(today());
            $isFullOn = !$isFuture && !$isOff && ($full > 0);
            $isHalfOn = !$isFuture && !$isOff && ($half > 0);
            $isMealOn = $isFullOn || $isHalfOn;

            $dayMealCount = 0;
            $dayCost = 0;
            $mealType = 'off';

            if ($isFullOn) {
                $totalActiveDays++;
                $totalFullCount++;
                $dayMealCount = 1;
                $dayCost = $dailyRate;
                $mealType = 'full';
            } elseif ($isHalfOn) {
                $totalActiveDays++;
                $totalHalfCount++;
                $dayMealCount = 0.5;
                $dayCost = $dailyHalfRate;
                $mealType = ($lowerNote === 'night' || $lowerNote === 'half_night') ? 'half_night' : 'half_day';
            } else {
                $totalOffDays++;
                $dayMealCount = 0;
                $dayCost = 0;
                $mealType = 'off';
            }

            $totalCost += $dayCost;

            // Day note construction
            $dayNotes = [];
            if (!$isFuture) {
                if ($isMealOn) {
                    if ($spec) {
                        $dayNotes[] = $spec->title ? "⭐ {$spec->title} (৳ {$dailyRate}) Special Meal" : "⭐ Special Meal (৳ {$dailyRate})";
                    } elseif ($isHalfOn) {
                        $dayNotes[] = ($mealType === 'half_night') ? 'Half Meal (Night)' : 'Half Meal (Day)';
                    } elseif ($isFullOn) {
                        $dayNotes[] = 'Full Meal';
                    }
                } else {
                    if (!empty($note)) {
                        if (str_contains($lowerNote, 'checked in') || str_contains($lowerNote, 'resumes tomorrow') || (str_contains($lowerNote, 'check') && str_contains($lowerNote, 'in'))) {
                            $dayNotes[] = 'হোস্টেলে চেক-ইন সম্পন্ন (আগামীকাল থেকে মিল চালু)';
                        } elseif (str_contains($lowerNote, 'leave') || str_contains($lowerNote, 'checked out') || str_contains($lowerNote, 'check')) {
                            $dayNotes[] = 'চেক-আউট / বাইরে আছেন (মিল বন্ধ)';
                        } elseif (str_contains($lowerNote, 'zero deposit')) {
                            $dayNotes[] = 'নো ডিপোজিট (Zero Deposit)';
                        } else {
                            $dayNotes[] = $note;
                        }
                    } else {
                        $dayNotes[] = 'মিল বন্ধ (Meal OFF)';
                    }
                }
                if ($dayGuestList->isNotEmpty()) {
                    foreach ($dayGuestList as $gm) {
                        $gCountFormatted = (float)$gm->meal_count == (int)$gm->meal_count ? (int)$gm->meal_count : number_format($gm->meal_count, 1);
                        $dayNotes[] = "👥 মেহমান: {$gm->guest_name} ({$gCountFormatted} টি - ৳ " . number_format($gm->amount, 0) . ")";
                    }
                }
            }

            $days[] = (object) [
                'date'           => $dateStr,
                'date_formatted' => $current->format('d M Y'),
                'day_name'       => $current->format('l'),
                'full'           => $isFullOn ? 1 : 0,
                'half'           => $isHalfOn ? 1 : 0,
                'is_half'        => $isHalfOn,
                'meal_type'      => $mealType,
                'meal_count'     => $dayMealCount,
                'is_off'         => !$isMealOn,
                'note'           => implode(' | ', $dayNotes),
                'is_meal_on'     => $isMealOn,
                'day_cost'       => $dayCost,
                'guest_meals'    => $dayGuestList,
            ];

            $current->addDay();
        }

        $totalCostWithGuest = $totalCost + $totalGuestMealsAmount;
        $balance = $depositTotal - $totalCostWithGuest;

        // Fetch Resident's Floor / Room / Block Information
        $cleanPhone = preg_replace('/[^0-9]/', '', $user->phone ?? '');
        $cleanPhone10 = strlen($cleanPhone) >= 6 ? (strlen($cleanPhone) > 10 ? substr($cleanPhone, -10) : $cleanPhone) : '';

        $userBooking = \App\Models\Backend\RoomBookingHistory::where(function($q) use ($user, $cleanPhone10) {
                if (!empty($cleanPhone10)) {
                    $q->where('phone', 'like', "%{$cleanPhone10}%");
                }
                if (!empty($user->email)) {
                    $q->orWhere('email', $user->email);
                }
            })
            ->orderByRaw("CASE WHEN today_check_out IS NULL THEN 0 ELSE 1 END")
            ->latest('id')
            ->first();

        $roomInfoStr = '-';
        if ($userBooking) {
            $items = [];
            if ($userBooking->floor_number_room_number_roomprice) {
                $items = is_string($userBooking->floor_number_room_number_roomprice)
                    ? (json_decode($userBooking->floor_number_room_number_roomprice, true) ?? [])
                    : ($userBooking->floor_number_room_number_roomprice ?? []);
            }
            $c = collect($items);
            $formattedRooms = $c->map(function ($item) {
                $raw = is_array($item) ? trim($item['roomnumber'] ?? '') : trim($item->roomnumber ?? '');
                if (empty($raw)) return null;
                $rType = strtolower(is_array($item) ? ($item['room_type'] ?? '') : ($item->room_type ?? ''));
                $isDouble = str_contains($rType, 'double') || str_contains($rType, 'doubel');

                if (preg_match('/^([A-Za-z]+)[\-_](\d+)[\-_]Block(?:[\-_](\d+))?/i', $raw, $m)) {
                    $blockLetter = strtoupper($m[1]);
                    $roomNum = $m[2];
                    $seatSub = $m[3] ?? '';
                    return ($isDouble && $seatSub) ? "{$roomNum}-Block {$blockLetter}-{$seatSub}" : "{$roomNum}-Block {$blockLetter}";
                }
                if (preg_match('/^(\d+)[\-_]Block(?:[\-_]([A-Za-z]+))?(?:[\-_](\d+))?/i', $raw, $m)) {
                    $roomNum = $m[1];
                    $blockLetter = !empty($m[2]) ? strtoupper($m[2]) : '';
                    $seatSub = $m[3] ?? '';
                    $blockLabel = $blockLetter ? "Block {$blockLetter}" : "Block";
                    return ($isDouble && $seatSub) ? "{$roomNum}-{$blockLabel}-{$seatSub}" : "{$roomNum}-{$blockLabel}";
                }
                if (!$isDouble && preg_match('/[\-_][12]$/', $raw)) {
                    return preg_replace('/[\-_][12]$/', '', $raw);
                }
                return $raw;
            })->filter()->implode(', ');

            if (empty($formattedRooms)) {
                $formattedRooms = $userBooking->room_number ?? $userBooking->roomnumber ?? '-';
            }

            $floor = $c->pluck('floornumber')->filter()->unique()->implode(', ');
            if (empty($floor)) {
                $floor = $userBooking->floornumber ?? '';
            }

            $roomInfoStr = ($floor ? "{$floor} | " : '') . "রুম/সিট: {$formattedRooms}";
        }

        $data = [
            'page_title'            => $user->name . ' - Monthly Meal Statement',
            'user'                  => $user,
            'roomInfoStr'           => $roomInfoStr,
            'selectedMonth'         => $selectedMonth,
            'monthFormatted'        => $startDate->format('F Y'),
            'fullRate'              => $fullRate,
            'halfRate'              => $halfRate,
            'depositTotal'          => $depositTotal,
            'totalMealCostOnly'     => $totalCost,
            'guestMeals'            => $guestMeals,
            'totalGuestMealsCount'  => $totalGuestMealsCount,
            'totalGuestMealsAmount' => $totalGuestMealsAmount,
            'totalCost'             => $totalCostWithGuest,
            'balance'               => $balance,
            'totalActiveDays'       => $totalActiveDays,
            'totalOffDays'          => $totalOffDays,
            'totalFullCount'        => $totalFullCount,
            'totalHalfCount'        => $totalHalfCount,
            'totalMeals'            => $totalFullCount + ($totalHalfCount * 0.5) + $totalGuestMealsCount,
            'days'                  => $days,
        ];

        return view('backend.meal.resident-meal-detail', $data);
    }

    public function store(Request $request)
    {
        $currentUser = auth()->user();
        if (!$currentUser->hasRole('admin') && !$currentUser->hasRole('staffs')) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $currentUser->phone ?? '');
            $cleanPhone10 = strlen($cleanPhone) >= 6 ? (strlen($cleanPhone) > 10 ? substr($cleanPhone, -10) : $cleanPhone) : '';

            $userBooking = \App\Models\Backend\RoomBookingHistory::whereNull('today_check_out')
                ->where(function ($q) use ($currentUser, $cleanPhone10) {
                    if (!empty($cleanPhone10)) {
                        $q->whereRaw("REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+', '') LIKE ?", ["%{$cleanPhone10}%"]);
                    } elseif (!empty($currentUser->email)) {
                        $q->where('email', $currentUser->email);
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })->latest()->first();

            if ($userBooking) {
                $outstandingDue = \App\Models\Backend\MonthlyPayment::where('room_booking_history_id', $userBooking->id)
                    ->where('status', '!=', 'paid')
                    ->sum('due_amount');

                if ($outstandingDue > 0) {
                    return redirect()->back()->with('error', 'আপনার ভাড়ার বকেয়া রয়েছে (৳ ' . number_format($outstandingDue) . ')। বকেয়া পরিশোধ না করা পর্যন্ত মিল সেভ করা যাবে না।');
                }
            }
        }

        $request->validate([
            'date' => 'required|date',
            'meal' => 'required|array',
            'meal.*.half_meal' => 'nullable|in:0,1',
            'meal.*.full_meal' => 'nullable|in:0,1',
        ]);
        
        $result = $this->mealService->mealStore($request);
        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }
        return redirect()->back()->with('success', 'Today meal saved successfully.');
    }

    public function toggleMealOff(Request $request)
    {
        $request->validate([
            'date'   => 'nullable|date',
            'is_off' => 'required|boolean',
            'user_id'=> 'nullable|exists:users,id',
        ]);

        $currentUser = auth()->user();
        $targetUserId = ($currentUser->hasRole('admin') || $currentUser->hasRole('staffs')) && $request->filled('user_id')
            ? $request->user_id
            : $currentUser->id;

        $date = $request->date ?: now()->format('Y-m-d');
        $isOff = $request->is_off ? 1 : 0;

        if (!$currentUser->hasRole('admin') && !$currentUser->hasRole('staffs')) {
            $todayStr = now()->format('Y-m-d');
            if ($date < $todayStr) {
                $msg = 'অতীতের (বিগত দিনের) মিলের তথ্য পরিবর্তন করা সম্ভব নয়।';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return redirect()->back()->with('error', $msg);
            }
            if ($date === $todayStr && now()->format('H:i') >= '16:00') {
                $msg = 'দুঃখিত, আজ মিল বন্ধ বা চালু করার সময় (বিকাল ৪:০০ টা) পার হয়ে গেছে।';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return redirect()->back()->with('error', $msg);
            }
        }

        $meal = $this->mealService->toggleMealOffStatus($targetUserId, $date, $isOff);

        $statusText = $isOff ? 'বন্ধ' : 'চালু';
        $message = "{$date} তারিখের মিল সফলভাবে {$statusText} করা হয়েছে।";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'is_off'  => $isOff,
                'meal'    => $meal,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    public function getLiveMealStatus(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $parsedDate = \Carbon\Carbon::parse($date);

        $mealResult = $this->mealService->todayOrPreviousMealStatus($parsedDate);
        $todaySummary = $this->mealService->todayMealSummary($parsedDate);
        $guestMeals = \App\Models\Backend\GuestMeal::whereDate('date', $parsedDate->format('Y-m-d'))
            ->where('status', 1)
            ->get();
        $guestMealsByUser = $guestMeals->groupBy('user_id');

        $mealsData = [];
        foreach ($mealResult['meals'] as $m) {
            $isOff = (bool)$m->is_off;
            $isFull = (bool)$m->full_meal;
            $isHalf = (bool)$m->half_meal;
            $mealNote = strtolower($m->note ?? '');
            $isHalfDay = $isHalf && ($mealNote === 'day' || $mealNote === 'half_day');
            $isHalfNight = $isHalf && ($mealNote === 'night' || $mealNote === 'half_night');
            if ($isHalf && !$isHalfDay && !$isHalfNight) {
                $isHalfDay = true;
            }

            $dep = $this->mealService->getUserMealDepositBalance($m->user_id);

            $isReturnedToday = (bool)(str_contains($mealNote, 'checked in') || str_contains($mealNote, 'resumes tomorrow') || (str_contains($mealNote, 'check') && str_contains($mealNote, 'in')));
            $isOutside = (bool)(!$isReturnedToday && (str_contains($mealNote, 'checked out') || str_contains($mealNote, 'leave') || str_contains($mealNote, 'check')));

            $userGuestList = $guestMealsByUser->get($m->user_id, collect())->map(function($gm) {
                return [
                    'id'         => $gm->id,
                    'guest_name' => $gm->guest_name,
                    'meal_count' => (float)$gm->meal_count == (int)$gm->meal_count ? (int)$gm->meal_count : (float)$gm->meal_count,
                    'amount'     => (float)$gm->amount,
                    'note'       => $gm->note,
                ];
            });

            $mealsData[$m->user_id] = [
                'is_off'            => $isOff,
                'full_meal'         => $isFull && !$isOff,
                'half_meal'         => $isHalf && !$isOff,
                'is_half_day'       => $isHalfDay && !$isOff,
                'is_half_night'     => $isHalfNight && !$isOff,
                'note'              => $m->note,
                'is_leave'          => $isOutside || $isReturnedToday,
                'is_outside'        => $isOutside,
                'is_returned_today' => $isReturnedToday,
                'is_zero_deposit'   => $dep['is_zero_deposit'],
                'guest_meals'       => $userGuestList,
            ];
        }

        return response()->json([
            'success'            => true,
            'date'               => $date,
            'today_summary'      => $todaySummary,
            'total_guest_meals'  => (float)$guestMeals->sum('meal_count'),
            'total_guest_amount' => (float)$guestMeals->sum('amount'),
            'meals'              => $mealsData,
        ]);
    }

    public function updateMealStatus(Request $request)
    {
        $request->validate([
            'date'       => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:date',
            'total_days' => 'nullable|integer|min:1|max:60',
            'meal_type'  => 'required|in:full,off,half_day,half_night',
            'user_id'    => 'nullable|exists:users,id',
        ]);

        $currentUser = auth()->user();
        $targetUserId = ($currentUser->hasRole('admin') || $currentUser->hasRole('staffs')) && $request->filled('user_id')
            ? $request->user_id
            : $currentUser->id;

        $startDate = $request->date ?: now()->format('Y-m-d');
        $totalDays = (int)($request->total_days ?: 1);
        $endDate = $request->end_date ?: \Carbon\Carbon::parse($startDate)->addDays($totalDays - 1)->format('Y-m-d');
        $mealType = in_array($request->meal_type, ['full', 'off', 'half_day', 'half_night']) ? $request->meal_type : 'full';

        $labels = [
            'full'       => 'ফুল মিল (Full Meal ON)',
            'half_day'   => 'হাফ মিল - দিন (Half Meal - Day)',
            'half_night' => 'হাফ মিল - রাত (Half Meal - Night)',
            'off'        => 'মিল বন্ধ (Meal OFF)',
        ];
        $statusLabel = $labels[$mealType] ?? 'আপডেট';

        if (!$currentUser->hasRole('admin') && !$currentUser->hasRole('staffs')) {
            $todayStr = now()->format('Y-m-d');
            if ($startDate < $todayStr) {
                $msg = 'অতীতের (বিগত দিনের) মিলের তথ্য পরিবর্তন করা সম্ভব নয়।';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return redirect()->back()->with('error', $msg);
            }

            $nowTime = now()->format('H:i');
            if ($nowTime < '10:00' || $nowTime >= '20:00') {
                $msg = 'দুঃখিত, মিল বন্ধ বা পরিবর্তন করার নির্ধারিত সময় (সকাল ১০:০০ টা থেকে রাত ০৮:০০ টা) সমাপ্ত।';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return redirect()->back()->with('error', $msg);
            }

            $depositInfo = $this->mealService->getUserMealDepositBalance($targetUserId);
            if ($depositInfo['is_zero_deposit']) {
                $msg = 'আপনার মেল ডিপোজিট ব্যালেন্স শূন্য বা অপ্রতুল। মেল চালু বা পরিবর্তন করতে ডিপোজিট রিচার্জ করুন।';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return redirect()->back()->with('error', $msg);
            }

            // Create/Update Meal Request for Admin Approval with Date Range
            $mealReq = \App\Models\Backend\MealRequest::updateOrCreate(
                [
                    'user_id' => $targetUserId,
                    'date'    => $startDate,
                ],
                [
                    'end_date'      => $endDate,
                    'total_days'    => $totalDays,
                    'request_type'  => $mealType,
                    'status'        => 0, // Pending Admin Approval
                    'user_notified' => 0,
                    'admin_note'    => null,
                ]
            );

            $rangeText = $totalDays > 1 ? "({$startDate} হতে {$endDate} পর্যন্ত মোট {$totalDays} দিন)" : "({$startDate})";
            $msg = "{$rangeText} '{$statusLabel}' অনুরোধটি এডমিনের অনুমোদনের জন্য পাঠানো হয়েছে। এডমিন কনফার্ম করলে আপডেট দেখতে পাবেন।";

            session()->flash('success', $msg);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success'      => true,
                    'message'      => $msg,
                    'is_pending'   => true,
                    'meal_type'    => $mealType,
                    'redirect_url' => route('dashboard'),
                ]);
            }
            return redirect()->route('dashboard')->with('success', $msg);
        }

        // Direct update for Admin/Staff across date range
        $cStart = \Carbon\Carbon::parse($startDate);
        $cEnd = \Carbon\Carbon::parse($endDate);
        for ($d = $cStart->copy(); $d->lte($cEnd); $d->addDay()) {
            $this->mealService->updateUserMealStatus($targetUserId, $d->format('Y-m-d'), $mealType);
        }

        $rangeText = $totalDays > 1 ? "({$startDate} হতে {$endDate} পর্যন্ত মোট {$totalDays} দিন)" : "({$startDate})";
        $message = "{$rangeText} মিল সফলভাবে '{$statusLabel}' হিসেবে সেভ করা হয়েছে।";

        $todaySummary = $this->mealService->todayMealSummary($startDate);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'       => true,
                'message'       => $message,
                'meal_type'     => $mealType,
                'today_summary' => [
                    'total_full_meal'  => $todaySummary['total_full_meal'] ?? 0,
                    'chef_meal_count'  => $todaySummary['chef_meal_count'] ?? 0,
                    'grand_total_rice' => $todaySummary['grand_total_rice'] ?? 0,
                    'grand_total_cost' => $todaySummary['grand_total_cost'] ?? 0,
                ],
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    public function bulkUpdateMealStatus(Request $request)
    {
        $request->validate([
            'action' => 'required|in:all_off,all_full,all_half_day,all_half_night',
            'date'   => 'nullable|date',
        ]);

        $currentUser = auth()->user();
        if (!$currentUser->hasRole('admin') && !$currentUser->hasRole('staffs')) {
            return response()->json(['success' => false, 'message' => 'এই অ্যাকশন সম্পাদন করার অনুমতি নেই।'], 403);
        }

        $date = $request->date ?: now()->format('Y-m-d');
        $action = $request->action;

        $users = $this->userService->usersWithRole();
        $updatedCount = 0;

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            foreach ($users as $u) {
                $dep = $this->mealService->getUserMealDepositBalance($u->id);
                // If turning on, check if zero deposit
                if ($action === 'all_full' || $action === 'all_half_day' || $action === 'all_half_night') {
                    if ($dep['is_zero_deposit']) {
                        continue; // Skip zero deposit users
                    }
                }

                $mealType = 'off';
                if ($action === 'all_full') {
                    $mealType = 'full';
                } elseif ($action === 'all_half_day') {
                    $mealType = 'half_day';
                } elseif ($action === 'all_half_night') {
                    $mealType = 'half_night';
                }

                $this->mealService->updateUserMealStatus($u->id, $date, $mealType);
                $updatedCount++;
            }
            \Illuminate\Support\Facades\DB::commit();

            $actionLabels = [
                'all_off'          => 'সকল সক্রিয় সদস্যের মিল একবারে বন্ধ (OFF) করা হয়েছে',
                'all_full'         => 'সকল সদস্যের মিল একবারে ফুল চালু (ON) করা হয়েছে',
                'all_half_day'     => 'সকল সদস্যের মিল একবারে ডে হাফ (Day Half) চালু করা হয়েছে',
                'all_half_night'   => 'সকল সদস্যের মিল একবারে নাইট হাফ (Night Half) চালু করা হয়েছে',
            ];

            $todaySummary = $this->mealService->todayMealSummary($date);

            return response()->json([
                'success'       => true,
                'message'       => ($actionLabels[$action] ?? 'আপডেট সম্পন্ন') . " (মোট {$updatedCount} জন)।",
                'action'        => $action,
                'today_summary' => [
                    'total_full_meal'  => $todaySummary['total_full_meal'] ?? 0,
                    'chef_meal_count'  => $todaySummary['chef_meal_count'] ?? 0,
                    'grand_total_rice' => $todaySummary['grand_total_rice'] ?? 0,
                    'grand_total_cost' => $todaySummary['grand_total_cost'] ?? 0,
                ],
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['success' => false, 'message' => 'ত্রুটি: ' . $e->getMessage()], 500);
        }
    }

    public function mealRequestsIndex(Request $request)
    {
        $data['page_title'] = "Meal Change Requests (মিল পরিবর্তনের অনুরোধসমূহ)";
        $query = \App\Models\Backend\MealRequest::with('user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $data['mealRequests'] = $query->paginate(15);
        return view('backend.meal.requests', $data);
    }

    public function approveMealRequest($id)
    {
        $mealReq = \App\Models\Backend\MealRequest::findOrFail($id);
        $mealReq->status = 1; // Approved
        $mealReq->user_notified = 0; // Show confirmation message to user
        $mealReq->save();

        // Perform actual meal status update in DB across date range
        $startDate = \Carbon\Carbon::parse($mealReq->date);
        $endDate = $mealReq->end_date ? \Carbon\Carbon::parse($mealReq->end_date) : $startDate;

        $mType = ($mealReq->request_type === 'off') ? 'off' : 'full';

        for ($d = $startDate->copy(); $d->lte($endDate); $d->addDay()) {
            $this->mealService->updateUserMealStatus($mealReq->user_id, $d->format('Y-m-d'), $mType);
        }

        $labels = [
            'full' => 'মিল চালু (Meal ON)',
            'off'  => 'মিল বন্ধ (Meal OFF)',
        ];
        $statusLabel = $labels[$mType] ?? $mType;
        $rangeInfo = $mealReq->total_days > 1 ? "({$mealReq->date} হতে {$mealReq->end_date} মোট {$mealReq->total_days} দিন)" : "({$mealReq->date})";

        $msg = "ইউজার {$mealReq->user->name}-এর {$rangeInfo} '{$statusLabel}' অনুরোধটি সফলভাবে অনুমোদন করা হয়েছে।";

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return redirect()->back()->with('success', $msg);
    }

    public function rejectMealRequest(Request $request, $id)
    {
        $mealReq = \App\Models\Backend\MealRequest::findOrFail($id);
        $mealReq->status = 2; // Rejected
        $mealReq->user_notified = 0; // Show rejection message to user
        if ($request->filled('admin_note')) {
            $mealReq->admin_note = $request->admin_note;
        }
        $mealReq->save();

        $msg = "ইউজার {$mealReq->user->name}-এর মিল অনুরোধটি প্রত্যাখ্যান করা হয়েছে।";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return redirect()->back()->with('success', $msg);
    }

    public function checkUserMealNotifs()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'has_notifs' => false, 'notifications' => []]);
        }

        if ($user->hasRole('admin') || $user->hasRole('staffs')) {
            return response()->json([
                'success'               => true,
                'has_notifs'            => false,
                'notifications'         => [],
                'pending_count'         => 0,
                'deposit_balance'       => 0,
                'warning_message'       => null,
                'is_meal_off'           => false,
                'today_meal_badge_text'  => null,
                'today_meal_badge_class' => null,
                'today_meal_badge_icon'  => null,
            ]);
        }

        $todayStr = \Carbon\Carbon::today()->format('Y-m-d');
        $currentHour = (int) \Carbon\Carbon::now()->format('H');

        // Auto mark past or expired notifications as notified in DB
        \App\Models\Backend\MealRequest::where('user_id', $user->id)
            ->where('user_notified', 0)
            ->where(function($q) use ($todayStr) {
                $q->where(function($sub) use ($todayStr) {
                    $sub->whereNotNull('end_date')->where('end_date', '<', $todayStr);
                })->orWhere(function($sub) use ($todayStr) {
                    $sub->whereNull('end_date')->where('date', '<', $todayStr);
                });
            })
            ->update(['user_notified' => 1]);

        // Newly approved (status 1) or rejected (status 2) requests
        $newNotifs = \App\Models\Backend\MealRequest::where('user_id', $user->id)
            ->whereIn('status', [1, 2])
            ->where('user_notified', 0)
            ->get();

        $mealTypeLabels = [
            'full'       => 'মিল চালু (Meal ON)',
            'off'        => 'মিল বন্ধ (Meal OFF)',
        ];

        $notifications = [];
        foreach ($newNotifs as $un) {
            $dateStr = \Carbon\Carbon::parse($un->date)->format('d-M-Y');
            $rangeStr = ($un->end_date && $un->total_days > 1) 
                ? "হতে " . \Carbon\Carbon::parse($un->end_date)->format('d-M-Y') . " পর্যন্ত" 
                : "";
            $typeLabel = $mealTypeLabels[$un->request_type] ?? $un->request_type;

            $notifMessage = "আপনার <strong>{$dateStr}</strong> {$rangeStr} তারিখের <strong>'{$typeLabel}'</strong> অনুরোধটি এডমিন কর্তৃক " . ($un->status == 1 ? 'অনুমোদিত' : 'প্রত্যাখ্যাত') . " হয়েছে।";

            if ($un->status == 1) {
                if ($un->request_type == 'off') {
                    $resumeDate = \Carbon\Carbon::parse($un->end_date ?: $un->date)->addDay()->format('d-M-Y');
                    $notifMessage .= "<br><span class='text-success fw-bold mt-1 d-inline-block'><i class='fa fa-info-circle me-1'></i> <strong>{$resumeDate}</strong> তারিখ থেকে আপনার মিল স্বয়ংক্রিয়ভাবে পুনরায় চালু থাকবে।</span>";
                } elseif ($un->request_type == 'full') {
                    $notifMessage .= "<br><span class='text-success fw-bold mt-1 d-inline-block'><i class='fa fa-check-circle me-1'></i> আপনার মিল চালু করা হয়েছে।</span>";
                }
            }
            if ($un->admin_note) {
                $notifMessage .= "<br><small class='text-muted'>নোট: {$un->admin_note}</small>";
            }

            $notifications[] = [
                'id'           => $un->id,
                'status'       => $un->status, // 1 = approved, 2 = rejected
                'title'        => $un->status == 1 ? '✅ মিল অনুরোধ অনুমোদিত হয়েছে' : '❌ মিল অনুরোধ প্রত্যাখ্যাত হয়েছে',
                'message'      => $notifMessage,
                'request_type' => $un->request_type,
                'date'         => $un->date,
            ];

            // Mark as notified so it displays once
            $un->user_notified = 1;
            $un->save();
        }

        $pendingCount = \App\Models\Backend\MealRequest::where('user_id', $user->id)
            ->where('status', 0)
            ->count();

        $depositInfo = $this->mealService->getUserMealDepositBalance($user->id);
        $todayMealStatus = \App\Models\Backend\Meal::where('user_id', $user->id)->whereDate('date', today())->first();

        $isMealOff = ($depositInfo['balance'] <= 0) || ($todayMealStatus && $todayMealStatus->is_off);

        $activeCheckout = \App\Models\Backend\ResidentCheckInOut::where('user_id', $user->id)
            ->where('status', 0)
            ->latest('check_out_date')
            ->first();
        $isCurrentlyOutside = false;
        if ($activeCheckout) {
            $checkoutDate = Carbon::parse($activeCheckout->check_out_date)->startOfDay();
            if ($checkoutDate->lte(Carbon::today())) {
                $isCurrentlyOutside = true;
            }
        }

        $mealNoteLower = strtolower($todayMealStatus ? ($todayMealStatus->note ?? '') : '');
        $isCheckoutMealOff = str_contains($mealNoteLower, 'check') || str_contains($mealNoteLower, 'leave') || str_contains($mealNoteLower, 'বাড়ি') || str_contains($mealNoteLower, 'ছুটি');

        $isAdminTurnedOff = false;
        if (!$isCurrentlyOutside && !$isCheckoutMealOff && !$depositInfo['is_zero_deposit'] && $todayMealStatus && $todayMealStatus->is_off) {
            if ($todayMealStatus->note === 'Admin Turned OFF' || (str_contains($todayMealStatus->note ?? '', 'Admin') && !str_contains($todayMealStatus->note ?? '', 'Zero Deposit')) || str_contains($todayMealStatus->note ?? '', 'কর্তৃপক্ষ')) {
                $isAdminTurnedOff = true;
            } elseif ($todayMealStatus->made_by && $todayMealStatus->made_by != $user->id) {
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

        $todayMealBadgeText = 'Full Meal (অটো চালু)';
        $todayMealBadgeClass = 'bg-success';
        $todayMealBadgeIcon = 'fa-check-circle';

        if ($isMealOff) {
            $noteLower = strtolower($todayMealStatus ? ($todayMealStatus->note ?? '') : '');
            if (str_contains($noteLower, 'checked in') || str_contains($noteLower, 'resumes tomorrow') || (str_contains($noteLower, 'check') && str_contains($noteLower, 'in'))) {
                $todayMealBadgeText = '🟢 চেক-ইন (আগামীকাল থেকে মিল চালু)';
                $todayMealBadgeClass = 'bg-success text-white';
                $todayMealBadgeIcon = 'fa-door-open';
            } elseif (str_contains($noteLower, 'leave') || str_contains($noteLower, 'checked out') || str_contains($noteLower, 'check')) {
                $todayMealBadgeText = '🚫 চেক-আউট (মিল বন্ধ)';
                $todayMealBadgeClass = 'bg-danger';
                $todayMealBadgeIcon = 'fa-plane-departure';
            } else {
                $todayMealBadgeText = 'Meal OFF (মিল বন্ধ)';
                $todayMealBadgeClass = 'bg-danger';
                $todayMealBadgeIcon = 'fa-ban';
            }
        } elseif ($todayMealStatus && $todayMealStatus->half_meal) {
            if ($todayMealStatus->note === 'night' || $todayMealStatus->note === 'half_night') {
                $todayMealBadgeText = 'Night Half (রাত চালু / দুপুর বন্ধ)';
                $todayMealBadgeClass = 'bg-warning text-dark';
                $todayMealBadgeIcon = 'fa-moon';
            } else {
                $todayMealBadgeText = 'Day Half (দুপুর চালু / রাত বন্ধ)';
                $todayMealBadgeClass = 'bg-info text-white';
                $todayMealBadgeIcon = 'fa-sun';
            }
        }

        $latestMealReq = \App\Models\Backend\MealRequest::where('user_id', $user->id)
            ->where(function($q) {
                $q->where('status', 0)
                  ->orWhere(function($sub) {
                      $sub->where('status', 1)
                          ->whereDate('end_date', '>=', today()->format('Y-m-d'));
                  });
            })
            ->latest()
            ->first();

        $latestMealReqData = null;
        if ($latestMealReq) {
            $endDate = $latestMealReq->end_date ?: $latestMealReq->date;
            $resumeDate = Carbon::parse($endDate)->addDay()->format('d-M-Y');
            $daysText = $latestMealReq->request_type == 'off' 
                ? 'আগামী ' . ($latestMealReq->total_days ?? 1) . ' দিনের বন্ধের অনুরোধ'
                : 'মিল চালুর অনুরোধ';

            $latestMealReqData = [
                'id'          => $latestMealReq->id,
                'status'      => $latestMealReq->status,
                'status_text' => $latestMealReq->status == 1 ? 'অনুমোদিত' : 'অপেক্ষমাণ',
                'days_text'   => $daysText,
                'resume_date' => $resumeDate,
            ];
        }

        $todaySummary = $this->mealService->todayMealSummary(now());

        return response()->json([
            'success'               => true,
            'has_notifs'            => count($notifications) > 0,
            'notifications'         => $notifications,
            'pending_count'         => $pendingCount,
            'deposit_balance'       => $depositInfo['balance'],
            'warning_message'       => $depositInfo['warning_message'],
            'is_meal_off'           => $isMealOff,
            'is_admin_turned_off'   => $isAdminTurnedOff,
            'today_meal_badge_text'  => $todayMealBadgeText,
            'today_meal_badge_class' => $todayMealBadgeClass,
            'today_meal_badge_icon'  => $todayMealBadgeIcon,
            'latest_meal_req'       => $latestMealReqData,
            'today_summary'         => [
                'total_full_meal'  => $todaySummary['total_full_meal'] ?? 0,
                'chef_meal_count'  => $todaySummary['chef_meal_count'] ?? 0,
                'grand_total_rice' => $todaySummary['grand_total_rice'] ?? 0,
                'grand_total_cost' => $todaySummary['grand_total_cost'] ?? 0,
            ],
        ]);
    }


    public function checkPendingMealRequests()
    {
        $pendingCount = \App\Models\Backend\MealRequest::where('status', 0)->count();
        $latestRequests = \App\Models\Backend\MealRequest::with('user')
            ->where('status', 0)
            ->latest()
            ->take(5)
            ->get()
            ->map(function($r) {
                $labels = [
                    'full'       => 'Full Meal',
                    'half_day'   => 'Day Half',
                    'half_night' => 'Night Half',
                    'off'        => 'Meal OFF',
                ];
                $daysText = ($r->total_days && $r->total_days > 1) ? " ({$r->total_days} Days)" : "";
                $reqLabel = ($labels[$r->request_type] ?? $r->request_type) . $daysText;
                $dateRangeText = ($r->end_date && $r->total_days > 1) 
                    ? "{$r->date} to {$r->end_date}"
                    : $r->date;

                return [
                    'id'           => $r->id,
                    'user_name'    => $r->user->name ?? 'Resident',
                    'request_type' => $reqLabel,
                    'date'         => $dateRangeText,
                    'total_days'   => $r->total_days ?? 1,
                    'time_ago'     => $r->created_at->diffForHumans(),
                    'initial'      => strtoupper(substr($r->user->name ?? 'R', 0, 1)),
                ];
            });

        return response()->json([
            'count'    => $pendingCount,
            'requests' => $latestRequests,
        ]);
    }

    public function dismissUserMealNotification($id)
    {
        $mealReq = \App\Models\Backend\MealRequest::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();
        if ($mealReq) {
            $mealReq->user_notified = 1;
            $mealReq->save();
        }
        return response()->json(['success' => true]);
    }

    public function show($id)
    {
        return redirect()->route('meals.index');
    }

    public function edit($id)
    {
        return redirect()->route('meals.index');
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('meals.index');
    }

    public function destroy($id)
    {
        return redirect()->route('meals.index');
    }
}

