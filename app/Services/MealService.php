<?php

namespace App\Services;

use App\Models\Backend\Deposit;
use App\Models\Backend\Meal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MealService
{
    public function todayOrPreviousMealStatus($date)
    {
        $todayMeals = Meal::with('user', 'madeBy')
            ->whereDate('date', $date)
            ->get();

        if ($todayMeals->count() > 0) {
            return [
                'meals' => $todayMeals,
                'used_date' => $date,
                'is_fallback' => false,
            ];
        }

        $latestPreviousDate = Meal::whereDate('date', '<', $date)->max('date');

        if ($latestPreviousDate) {
            $previousMeals = Meal::with('user', 'madeBy')
                ->whereDate('date', $latestPreviousDate)
                ->get();

            return [
                'meals' => $previousMeals,
                'used_date' => $latestPreviousDate,
                'is_fallback' => true,
            ];
        }

        return [
            'meals' => collect(),
            'used_date' => $date,
            'is_fallback' => false,
        ];
    }

    public function todayMealWithPreviousFallback($date)
    {
        $date = \Carbon\Carbon::parse($date)->format('Y-m-d');

        $users = app(UserService::class)->user();
        $mealData = collect();
        $usedDate = $date;
        $isFallback = false;

        foreach ($users as $user) {
            $meal = Meal::where('user_id', $user->id)
                ->whereDate('date', '<=', $date)
                ->orderByRaw("CASE WHEN date = ? THEN 0 ELSE 1 END", [$date])
                ->orderBy('date', 'desc')
                ->first();

            if ($meal) {
                $mealData->push($meal);

                if ($meal->date != $date) {
                    $isFallback = true;
                    $usedDate = $meal->date;
                }
            }
        }

        return [
            'meals' => $mealData,
            'used_date' => $usedDate,
            'is_fallback' => $isFallback
        ];
    }

    public function todayMeal($date)
    {
        return Meal::with('user', 'madeBy')->whereDate('date', $date)->get();
    }

    public function monthlyMeal($month, $year)
    {
        return Meal::with('user', 'madeBy')->whereMonth('date', $month)->whereYear('date', $year)->get();
    }

    public function monthlyMealByUser($selectedMonth, $userId)
    {
        $date = Carbon::parse($selectedMonth);
        return Meal::where('user_id', $userId)
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->orderBy('date', 'desc')
            ->get();
    }

    public function mealStore(Request $request)
    {
        try {
            DB::beginTransaction();

            if (!$request->has('meal') || !is_array($request->meal)) {
                return [
                    'success' => false,
                    'message' => 'No meal data found.',
                ];
            }

            foreach ($request->meal as $userId => $mealData) {
                $isOff = !empty($mealData['is_off']) ? 1 : 0;
                Meal::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'date' => $request->date,
                    ],
                    [
                        'half_meal' => ($isOff ? 0 : (!empty($mealData['half_meal']) ? 1 : 0)),
                        'full_meal' => ($isOff ? 0 : (!empty($mealData['full_meal']) ? 1 : 0)),
                        'is_off'    => $isOff,
                        'made_by'   => auth()->id(),
                        'note'      => !empty($mealData['note']) ? $mealData['note'] : null,
                    ]
                );
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Meals saved successfully.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function todayMealSummary($date = null)
    {
        $date = $date ? Carbon::parse($date)->format('Y-m-d') : now()->format('Y-m-d');

        $meals = Meal::whereDate('date', $date)->get();
        $mealSetting = app(SettingService::class)->getSettingContentBySlug('meal_setting');
        $chefMealCount = $mealSetting['Chef_take_meal_number'] ?? 0;
        $chefMealCount = (int)$chefMealCount;

        $dayRate    = (float)($mealSetting['half'] ?? 35);
        $nightRate  = (float)($mealSetting['full'] ?? 65);

        $morningRiceGram = (int)($mealSetting['morning_rice'] ?? 100);
        $dayRiceGram     = (int)($mealSetting['day_rice'] ?? 200);
        $nightRiceGram   = (int)($mealSetting['night_rice'] ?? 200);

        $totalFullMeal      = (int)$meals->where('full_meal', 1)->count();
        $totalDayHalfMeal   = (int)$meals->where('half_meal', 1)->where('note', 'day')->count();
        $totalNightHalfMeal = (int)$meals->where('half_meal', 1)->where('note', 'night')->count();

        $memberMorningRice = ($totalFullMeal + $totalNightHalfMeal) * $morningRiceGram;
        $memberDayRice     = ($totalFullMeal + $totalDayHalfMeal) * $dayRiceGram;
        $memberNightRice   = ($totalFullMeal + $totalNightHalfMeal) * $nightRiceGram;

        $chefMorningRice = $chefMealCount * $morningRiceGram;
        $chefDayRice     = $chefMealCount * $dayRiceGram;
        $chefNightRice   = $chefMealCount * $nightRiceGram;

        $totalFullCost = $totalFullMeal * $nightRate;
        $totalHalfCost = ($totalDayHalfMeal * $dayRate) + ($totalNightHalfMeal * $dayRate);

        $grandTotalMemberRice = $memberMorningRice + $memberDayRice + $memberNightRice;
        $grandTotalChefRice   = $chefMorningRice + $chefDayRice + $chefNightRice;

        return [
            'date' => $date,
            'total_full_meal'       => $totalFullMeal,
            'total_half_meal'       => $totalDayHalfMeal + $totalNightHalfMeal,
            'chef_meal_count'       => $chefMealCount,
            'stat_morning_meal'     => $totalFullMeal + $totalNightHalfMeal + $chefMealCount,
            'stat_day_meal'         => $totalFullMeal + $totalDayHalfMeal + $chefMealCount,
            'stat_night_meal'       => $totalFullMeal + $totalNightHalfMeal + $chefMealCount,
            'morning_member_rice'   => $memberMorningRice,
            'morning_chef_rice'     => $chefMorningRice,
            'stat_morning_rice'     => $memberMorningRice + $chefMorningRice,
            'day_member_rice'       => $memberDayRice,
            'day_chef_rice'         => $chefDayRice,
            'stat_day_rice'         => $memberDayRice + $chefDayRice,
            'night_member_rice'     => $memberNightRice,
            'night_chef_rice'         => $chefNightRice,
            'stat_night_rice'       => $memberNightRice + $chefNightRice,
            'total_half_cost'       => $totalHalfCost,
            'total_full_cost'       => $totalFullCost,
            'grand_total_cost'        => $totalHalfCost + $totalFullCost,
            'grand_total_member_rice' => $grandTotalMemberRice,
            'grand_total_chef_rice'   => $grandTotalChefRice,
            'grand_total_rice'        => $grandTotalMemberRice + $grandTotalChefRice,
        ];
    }

    public function mealTime()
    {
        $mealSetting = app(SettingService::class)->getSettingContentBySlug('meal_setting');
        $time = $mealSetting['meal_change_time'] ?? '08:00';

        return Carbon::today('+6')->setTimeFromTimeString($time);
    }

    public function mealStatusSummary($users, $meals)
    {
        $mealMap = collect($meals)->keyBy('user_id');

        $totalMember = collect($users)->count();
        $totalDayHalf = 0;
        $totalNightHalf = 0;
        $totalFull = 0;
        $totalMealOn = 0;
        $totalOff = 0;

        foreach ($users as $user) {
            $meal = $mealMap->get($user->id);

            $isHalf = (int)($meal->half_meal ?? 0);
            $isFull = (int)($meal->full_meal ?? 0);
            $note   = $meal->note ?? '';

            if ($isFull) {
                $totalFull++;
            }

            if ($isHalf) {
                if ($note === 'day') {
                    $totalDayHalf++;
                } elseif ($note === 'night') {
                    $totalNightHalf++;
                }
            }

            if ($isHalf || $isFull) {
                $totalMealOn++;
            } else {
                $totalOff++;
            }
        }

        return [
            'total_member'     => $totalMember,
            'total_meal_on'    => $totalMealOn,
            'total_day_half'   => $totalDayHalf,
            'total_night_half' => $totalNightHalf,
            'total_full'       => $totalFull,
            'total_off'        => $totalOff,
        ];
    }

    public function monthlyMealHistory($selectedMonth, $users)
    {
        if ($selectedMonth > now()->format('Y-m')) {
            $selectedMonth = now()->format('Y-m');
        }

        $startDate = Carbon::parse($selectedMonth . '-01')->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::parse($selectedMonth . '-01')->endOfMonth()->format('Y-m-d');

        $mealSetting = app(SettingService::class)->getSettingContentBySlug('meal_setting');

        $dayRate = (float)($mealSetting['half'] ?? 35);
        $nightRate = (float)($mealSetting['full'] ?? 65);

        $mealSummary = Meal::selectRaw('
            user_id,
            SUM(half_meal) as half_total,
            SUM(full_meal) as full_total
        ')
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $depositSummary = Deposit::selectRaw('
            user_id,
            SUM(amount) as deposit_total
        ')
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $mealSummaryByUser = collect($users)->mapWithKeys(function ($user) use (
            $mealSummary,
            $depositSummary,
            $dayRate,
            $nightRate
        ) {
            $halfTotal = (int)optional($mealSummary->get($user->id))->half_total;
            $fullTotal = (int)optional($mealSummary->get($user->id))->full_total;
            $depositAmount = (float)optional($depositSummary->get($user->id))->deposit_total;

            $totalMeal = $halfTotal + $fullTotal;
            $mealCost = ($halfTotal * $dayRate) + ($fullTotal * $nightRate);
            $balance = $depositAmount - $mealCost;

            return [
                $user->id => (object)[
                    'user_id' => $user->id,
                    'half_total' => $halfTotal,
                    'full_total' => $fullTotal,
                    'total_meal' => $totalMeal,
                    'meal_cost' => $mealCost,
                    'deposit_amount' => $depositAmount,
                    'balance' => $balance,
                ]
            ];
        });

        return [
            'selectedMonth' => $selectedMonth,
            'half_rate' => $dayRate,
            'full_rate' => $nightRate,
            'mealSummaryByUser' => $mealSummaryByUser,
            'summary' => [
                'total_member' => collect($users)->count(),
                'total_half' => $mealSummaryByUser->sum('half_total'),
                'total_full' => $mealSummaryByUser->sum('full_total'),
                'total_meal' => $mealSummaryByUser->sum('total_meal'),
                'total_meal_cost' => $mealSummaryByUser->sum('meal_cost'),
                'total_deposit' => $mealSummaryByUser->sum('deposit_amount'),
                'total_balance' => $mealSummaryByUser->sum('balance'),
            ],
        ];
    }

    public function monthlyHistory($selectedMonth)
    {
        if (empty($selectedMonth) || $selectedMonth > now()->format('Y-m')) {
            $selectedMonth = now()->format('Y-m');
        }

        $startDate = Carbon::parse($selectedMonth . '-01')->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::parse($selectedMonth . '-01')->endOfMonth()->format('Y-m-d');

        $mealSetting = app(SettingService::class)->getSettingContentBySlug('meal_setting');

        $halfRate = (float)($mealSetting['half'] ?? 35);
        $fullRate = (float)($mealSetting['full'] ?? 65);

        $mealSummary = Meal::selectRaw('
            SUM(half_meal) as half_total,
            SUM(full_meal) as full_total
        ')
            ->whereBetween('date', [$startDate, $endDate])
            ->first();

        $depositSummary = Deposit::selectRaw('SUM(amount) as deposit_total')
            ->whereBetween('date', [$startDate, $endDate])
            ->first();

        $halfTotal = (int)($mealSummary->half_total ?? 0);
        $fullTotal = (int)($mealSummary->full_total ?? 0);
        $depositAmount = (float)($depositSummary->deposit_total ?? 0);

        $totalMeal = $halfTotal + $fullTotal;
        $mealCost = ($halfTotal * $halfRate) + ($fullTotal * $fullRate);
        $balance = $depositAmount - $mealCost;

        return [
            'selectedMonth' => $selectedMonth,
            'half_rate' => $halfRate,
            'full_rate' => $fullRate,
            'summary' => [
                'total_half' => $halfTotal,
                'total_full' => $fullTotal,
                'total_meal' => $totalMeal,
                'total_meal_cost' => $mealCost,
                'total_deposit' => $depositAmount,
                'total_balance' => $balance,
            ],
        ];
    }

    public function singleUserMealHistory($selectedMonth, $user)
    {
        if ($selectedMonth > now()->format('Y-m')) {
            $selectedMonth = now()->format('Y-m');
        }

        $startDate = Carbon::parse($selectedMonth . '-01')->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::parse($selectedMonth . '-01')->endOfMonth()->format('Y-m-d');

        $mealSetting = app(SettingService::class)->getSettingContentBySlug('meal_setting');
        $dayRate   = (float)($mealSetting['half'] ?? 35);
        $nightRate = (float)($mealSetting['full'] ?? 65);

        $mealData = Meal::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw("
            SUM(CASE WHEN half_meal = 1 AND note = 'day' THEN 1 ELSE 0 END) as day_half_total,
            SUM(CASE WHEN half_meal = 1 AND note = 'night' THEN 1 ELSE 0 END) as night_half_total,
            SUM(full_meal) as full_total
        ")
            ->first();

        $depositTotal = Deposit::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        $dayHalfTotal   = (int)($mealData->day_half_total ?? 0);
        $nightHalfTotal = (int)($mealData->night_half_total ?? 0);
        $fullTotal      = (int)($mealData->full_total ?? 0);

        $totalHalf = $dayHalfTotal + $nightHalfTotal;
        $totalMeal = $totalHalf + $fullTotal;

        $dayHalfCost   = $dayHalfTotal * $dayRate;
        $nightHalfCost = $nightHalfTotal * $dayRate;
        $fullCost      = $fullTotal * $nightRate;

        $mealCost = $dayHalfCost + $nightHalfCost + $fullCost;
        $balance = $depositTotal - $mealCost;

        return (object)[
            'selectedMonth'    => $selectedMonth,
            'user_id'          => $user->id,
            'user_name'        => $user->name,
            'half_rate'        => $dayRate,
            'full_rate'        => $nightRate,
            'day_half_total'   => $dayHalfTotal,
            'night_half_total' => $nightHalfTotal,
            'half_total'       => $totalHalf,
            'full_total'       => $fullTotal,
            'total_meal'       => $totalMeal,
            'meal_cost'        => $mealCost,
            'deposit_total'    => $depositTotal,
            'balance'          => $balance,
        ];
    }

    public function allTimeMealSummary($userId)
    {
        $mealSetting = app(SettingService::class)->getSettingContentBySlug('meal_setting');
        $dayRate   = (float)($mealSetting['half'] ?? 35);
        $nightRate = (float)($mealSetting['full'] ?? 65);

        $mealData = Meal::where('user_id', $userId)
            ->selectRaw("
            SUM(CASE WHEN half_meal = 1 AND note = 'day' THEN 1 ELSE 0 END) as day_half_total,
            SUM(CASE WHEN half_meal = 1 AND note = 'night' THEN 1 ELSE 0 END) as night_half_total,
            SUM(full_meal) as full_total
        ")
            ->first();

        $dayHalfTotal   = (int)($mealData->day_half_total ?? 0);
        $nightHalfTotal = (int)($mealData->night_half_total ?? 0);
        $fullTotal      = (int)($mealData->full_total ?? 0);

        $totalHalf = $dayHalfTotal + $nightHalfTotal;
        $totalMeal = $totalHalf + $fullTotal;

        $dayHalfCost   = $dayHalfTotal * $dayRate;
        $nightHalfCost = $nightHalfTotal * $dayRate;
        $fullCost      = $fullTotal * $nightRate;

        $mealCost = $dayHalfCost + $nightHalfCost + $fullCost;

        return (object)[
            'half_rate' => $dayRate,
            'full_rate' => $nightRate,
            'half_total' => $totalHalf,
            'full_total' => $fullTotal,
            'total_meal' => $totalMeal,
            'meal_cost' => $mealCost,
        ];
    }

    public function toggleMealOffStatus($userId, $date = null, $isOff = 1)
    {
        $date = $date ? Carbon::parse($date)->format('Y-m-d') : now()->format('Y-m-d');
        $isOff = $isOff ? 1 : 0;

        $meal = Meal::updateOrCreate(
            [
                'user_id' => $userId,
                'date'    => $date,
            ],
            [
                'is_off'    => $isOff,
                'half_meal' => $isOff ? 0 : 0,
                'full_meal' => $isOff ? 0 : 1,
                'made_by'   => auth()->id() ?? $userId,
                'note'      => $isOff ? 'Meal turned OFF by user' : 'Meal turned ON by user',
            ]
        );

        return $meal;
    }

    public function getUserMealDepositBalance($userId, $selectedMonth = null)
    {
        $selectedMonth = $selectedMonth ?: now()->format('Y-m');
        $user = \App\Models\User::find($userId);

        if (!$user) {
            return [
                'deposit_total' => 0,
                'meal_cost' => 0,
                'balance' => 0,
                'is_zero_deposit' => false,
                'warning_message' => null,
            ];
        }

        $singleHistory = $this->singleUserMealHistory($selectedMonth, $user);

        $depositTotal = (float) $singleHistory->deposit_total;
        $mealCost     = (float) $singleHistory->meal_cost;
        $balance      = (float) $singleHistory->balance;

        // Check if user has ANY deposit or meal consumption history ever
        $hasAnyDepositHistory = Deposit::where('user_id', $userId)->exists();
        $hasAnyMealHistory    = Meal::where('user_id', $userId)->where(function($q) {
            $q->where('full_meal', '>', 0)->orWhere('half_meal', '>', 0);
        })->exists();

        $isNewUser = (!$hasAnyDepositHistory && !$hasAnyMealHistory);
        $isZero    = ($hasAnyDepositHistory || $hasAnyMealHistory) && ($balance <= 0);
        $warningMessage = null;
        $alertType      = null;

        if ($isNewUser) {
            $alertType      = 'new_user_notice';
            $warningMessage = "📌 মেসের মিল সার্ভিস চালু করতে এবং মিল ডিপোজিট জমা দিতে অনুগ্রহ করে এডমিন এর সাথে যোগাযোগ করুন।";
        } elseif ($isZero) {
            $alertType      = 'zero_deposit_warning';
            $warningMessage = "⚠️ আপনার মেল ডিপোজিট (Meal Deposit) ব্যালেন্স ৳ " . number_format($balance, 2) . " টাকা! সার্ভিস চালু রাখতে অনুগ্রহ করে মেল ডিপোজিট রিচার্জ/প্রদান করুন এবং মেল চালু করতে এডমিন এর সাথে যোগাযোগ করুন।";
        }

        return [
            'deposit_total'   => $depositTotal,
            'meal_cost'       => $mealCost,
            'balance'         => $balance,
            'is_zero_deposit' => $isZero,
            'is_new_user'     => $isNewUser,
            'alert_type'      => $alertType,
            'warning_message' => $warningMessage,
        ];


    }

    public function ensureAutoMealGeneratedForUsers($users, $date = null)
    {
        $date = $date ? Carbon::parse($date)->format('Y-m-d') : now()->format('Y-m-d');
        $authId = auth()->id() ?? 1;

        foreach ($users as $user) {
            // Check if user has an active checked-in booking (status = 0 and check_in <= date)
            $cleanPhone = preg_replace('/[^0-9]/', '', $user->phone ?? '');
            $cleanPhone10 = strlen($cleanPhone) >= 6 ? (strlen($cleanPhone) > 10 ? substr($cleanPhone, -10) : $cleanPhone) : '';

            $hasActiveCheckIn = \App\Models\Backend\RoomBookingHistory::where('status', 0)
                ->whereDate('check_in', '<=', $date)
                ->where(function ($q) use ($user, $cleanPhone10) {
                    if (!empty($cleanPhone10)) {
                        $q->whereRaw("REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+', '') LIKE ?", ["%{$cleanPhone10}%"]);
                    } elseif (!empty($user->email)) {
                        $q->where('email', $user->email);
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })->exists();

            if ($hasActiveCheckIn) {
                // Check if user has an approved MealRequest covering $date
                $activeOffReq = \App\Models\Backend\MealRequest::where('user_id', $user->id)
                    ->where('status', 1)
                    ->whereDate('date', '<=', $date)
                    ->where(function($q) use ($date) {
                        $q->whereDate('end_date', '>=', $date)
                          ->orWhereNull('end_date');
                    })
                    ->latest()
                    ->first();

                $nowTime = now()->format('H:i');
                $isDayShiftPassed = $nowTime >= '16:00';
                $isNightShiftPassed = $nowTime >= '23:00';

                // Check if a Meal OFF period or shift OFF just expired
                $expiredOffReq = \App\Models\Backend\MealRequest::where('user_id', $user->id)
                    ->where('status', 1)
                    ->where(function($q) use ($date, $isDayShiftPassed, $isNightShiftPassed) {
                        $q->whereDate('end_date', '<', $date);
                        if ($isDayShiftPassed) {
                            $q->orWhere(function($sub) use ($date) {
                                $sub->whereDate('date', $date)->where('request_type', 'half_night');
                            });
                        }
                        if ($isNightShiftPassed) {
                            $q->orWhere(function($sub) use ($date) {
                                $sub->whereDate('date', $date)->where('request_type', 'half_day');
                            });
                        }
                    })
                    ->latest()
                    ->first();

                if ($expiredOffReq) {
                    $alreadyCreatedResume = \App\Models\Backend\MealRequest::where('user_id', $user->id)
                        ->whereDate('date', '>=', $date)
                        ->where('admin_note', 'LIKE', '%Auto Resume%')
                        ->exists();

                    if (!$alreadyCreatedResume) {
                        \App\Models\Backend\MealRequest::create([
                            'user_id'       => $user->id,
                            'date'          => $date,
                            'end_date'      => $date,
                            'total_days'    => 1,
                            'request_type'  => 'full',
                            'status'        => 0, // Pending Admin approval for auto-resume
                            'user_notified' => 0,
                            'admin_note'    => 'Auto Resume: সময়সীমা/মেয়াদ শেষে স্বয়ংক্রিয়ভাবে মিল চালুর অনুমতি',
                        ]);
                    }
                }

                $depositInfo = $this->getUserMealDepositBalance($user->id);
                $isZeroDeposit = $depositInfo['is_zero_deposit'];

                $meal = Meal::where('user_id', $user->id)->whereDate('date', $date)->first();

                if ($activeOffReq) {
                    $hMeal = 0; $fMeal = 0; $isO = 0; $nNote = null;
                    if ($activeOffReq->request_type === 'off') {
                        $isO = 1; $nNote = 'Meal OFF (Approved Request)';
                    } elseif ($activeOffReq->request_type === 'half_day') {
                        $hMeal = 1; $nNote = 'day';
                    } elseif ($activeOffReq->request_type === 'half_night') {
                        $hMeal = 1; $nNote = 'night';
                    } elseif ($activeOffReq->request_type === 'full') {
                        $fMeal = 1; $nNote = 'Full Meal';
                    }

                    if (!$meal) {
                        Meal::create([
                            'user_id'   => $user->id,
                            'date'      => $date,
                            'half_meal' => $hMeal,
                            'full_meal' => $fMeal,
                            'is_off'    => $isO,
                            'made_by'   => $authId,
                            'note'      => $nNote,
                        ]);
                    } else {
                        $meal->update([
                            'half_meal' => $hMeal,
                            'full_meal' => $fMeal,
                            'is_off'    => $isO,
                            'note'      => $nNote,
                        ]);
                    }
                } else {
                    if (!$meal) {
                        if ($isZeroDeposit) {
                            Meal::create([
                                'user_id'   => $user->id,
                                'date'      => $date,
                                'half_meal' => 0,
                                'full_meal' => 0,
                                'is_off'    => 1,
                                'made_by'   => $authId,
                                'note'      => 'Auto Meal OFF (Zero Deposit)',
                            ]);
                        } else {
                            Meal::create([
                                'user_id'   => $user->id,
                                'date'      => $date,
                                'half_meal' => 0,
                                'full_meal' => 1,
                                'is_off'    => 0,
                                'made_by'   => $authId,
                                'note'      => 'Auto Full Meal',
                            ]);
                        }
                    } else {
                        if ($isZeroDeposit && str_contains($meal->note ?? '', 'Auto')) {
                            $meal->update([
                                'half_meal' => 0,
                                'full_meal' => 0,
                                'is_off'    => 1,
                                'note'      => 'Auto Meal OFF (Zero Deposit)',
                            ]);
                        } elseif (!$isZeroDeposit && $meal->is_off && $meal->note === 'Auto Meal OFF (Zero Deposit)') {
                            $meal->update([
                                'half_meal' => 0,
                                'full_meal' => 1,
                                'is_off'    => 0,
                                'note'      => 'Auto Full Meal',
                            ]);
                        }
                    }
                }
            }
        }
    }

    public function updateUserMealStatus($userId, $date, $mealType)
    {
        $date = $date ? Carbon::parse($date)->format('Y-m-d') : now()->format('Y-m-d');
        $authId = auth()->id() ?? $userId;

        $halfMeal = 0;
        $fullMeal = 0;
        $isOff    = 0;
        $note     = null;

        if ($mealType === 'full') {
            $fullMeal = 1;
            $note = 'Full Meal';
        } elseif ($mealType === 'half_day') {
            $halfMeal = 1;
            $note = 'day';
        } elseif ($mealType === 'half_night') {
            $halfMeal = 1;
            $note = 'night';
        } elseif ($mealType === 'off') {
            $isOff = 1;
            $note = 'Meal Turned OFF';
        }

        return Meal::updateOrCreate(
            [
                'user_id' => $userId,
                'date'    => $date,
            ],
            [
                'half_meal' => $halfMeal,
                'full_meal' => $fullMeal,
                'is_off'    => $isOff,
                'made_by'   => $authId,
                'note'      => $note,
            ]
        );
    }
}
