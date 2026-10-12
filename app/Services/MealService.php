<?php

namespace App\Services;

use App\Models\Backend\Deposit;
use App\Models\Backend\GuestMeal;
use App\Models\Backend\Meal;
use App\Models\Backend\SpecialMeal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MealService
{
    public function getMealRatesForDate($date)
    {
        $formattedDate = Carbon::parse($date)->format('Y-m-d');
        $special = SpecialMeal::whereDate('date', $formattedDate)->where('status', 1)->first();
        $mealSetting = app(SettingService::class)->getSettingContentBySlug('meal_setting');
        $defaultFull = (float)($mealSetting['full'] ?? 30);
        $defaultHalf = (float)($mealSetting['half'] ?? ($defaultFull / 2));

        if ($special) {
            return [
                'is_special' => true,
                'title'      => $special->title,
                'full'       => (float)$special->full_rate,
                'half'       => (float)($special->half_rate ?? ($special->full_rate / 2)),
            ];
        }

        return [
            'is_special' => false,
            'title'      => null,
            'full'       => $defaultFull,
            'half'       => $defaultHalf,
        ];
    }
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
                        'half_meal' => 0,
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

        $rates = $this->getMealRatesForDate($date);
        $dailyFullRate = (float)$rates['full'];
        $dailyHalfRate = (float)($rates['half'] ?? ($dailyFullRate / 2));

        $morningRiceGram = (int)($mealSetting['morning_rice'] ?? 100);
        $dayRiceGram     = (int)($mealSetting['day_rice'] ?? 200);
        $nightRiceGram   = (int)($mealSetting['night_rice'] ?? 200);

        $totalFullMeal = (int)$meals->where('full_meal', 1)->where('is_off', 0)->count();
        $halfMeals = $meals->where('half_meal', 1)->where('is_off', 0);
        $totalHalfMeal = (int)$halfMeals->count();

        $halfDayCount = (int)$halfMeals->filter(function($m) {
            $note = strtolower($m->note ?? '');
            return $note === 'day' || $note === 'half_day';
        })->count();

        $halfNightCount = (int)$halfMeals->filter(function($m) {
            $note = strtolower($m->note ?? '');
            return $note === 'night' || $note === 'half_night';
        })->count();

        $memberMorningRice = $totalFullMeal * $morningRiceGram;
        $memberDayRice     = ($totalFullMeal + $halfDayCount) * $dayRiceGram;
        $memberNightRice   = ($totalFullMeal + $halfNightCount) * $nightRiceGram;

        $chefMorningRice = $chefMealCount * $morningRiceGram;
        $chefDayRice     = $chefMealCount * $dayRiceGram;
        $chefNightRice   = $chefMealCount * $nightRiceGram;

        $totalFullCost = $totalFullMeal * $dailyFullRate;
        $totalHalfCost = $totalHalfMeal * $dailyHalfRate;
        $grandTotalCost = $totalFullCost + $totalHalfCost;

        $grandTotalMemberRice = $memberMorningRice + $memberDayRice + $memberNightRice;
        $grandTotalChefRice   = $chefMorningRice + $chefDayRice + $chefNightRice;

        return [
            'date'                    => $date,
            'is_special'              => $rates['is_special'],
            'special_title'           => $rates['title'],
            'special_rate'            => $dailyFullRate,
            'special_half_rate'       => $dailyHalfRate,
            'total_full_meal'         => $totalFullMeal,
            'total_half_meal'         => $totalHalfMeal,
            'total_active_meal'       => $totalFullMeal + $totalHalfMeal,
            'half_day_count'          => $halfDayCount,
            'half_night_count'        => $halfNightCount,
            'chef_meal_count'         => $chefMealCount,
            'stat_morning_meal'       => $totalFullMeal + $chefMealCount,
            'stat_day_meal'           => $totalFullMeal + $halfDayCount + $chefMealCount,
            'stat_night_meal'         => $totalFullMeal + $halfNightCount + $chefMealCount,
            'morning_member_rice'     => $memberMorningRice,
            'morning_chef_rice'       => $chefMorningRice,
            'stat_morning_rice'       => $memberMorningRice + $chefMorningRice,
            'day_member_rice'         => $memberDayRice,
            'day_chef_rice'           => $chefDayRice,
            'stat_day_rice'           => $memberDayRice + $chefDayRice,
            'night_member_rice'       => $memberNightRice,
            'night_chef_rice'         => $chefNightRice,
            'stat_night_rice'         => $memberNightRice + $chefNightRice,
            'total_half_cost'         => $totalHalfCost,
            'total_full_cost'         => $totalFullCost,
            'grand_total_cost'        => $grandTotalCost,
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
        $totalFullOn = 0;
        $totalHalfOn = 0;
        $totalHalfDay = 0;
        $totalHalfNight = 0;
        $totalOff = 0;

        foreach ($users as $user) {
            $meal = $mealMap->get($user->id);

            $isFull = (int)($meal->full_meal ?? 0);
            $isHalf = (int)($meal->half_meal ?? 0);
            $isOff  = (int)($meal->is_off ?? 0);
            $note   = strtolower($meal->note ?? '');

            if ($isFull && !$isOff) {
                $totalFullOn++;
            } elseif ($isHalf && !$isOff) {
                $totalHalfOn++;
                if ($note === 'night' || $note === 'half_night') {
                    $totalHalfNight++;
                } else {
                    $totalHalfDay++;
                }
            } else {
                $totalOff++;
            }
        }

        return [
            'total_member'     => $totalMember,
            'total_meal_on'    => $totalFullOn + $totalHalfOn,
            'total_full_on'    => $totalFullOn,
            'total_half_on'    => $totalHalfOn,
            'total_day_half'   => $totalHalfDay,
            'total_night_half' => $totalHalfNight,
            'total_full'       => $totalFullOn,
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

        $this->ensureAutoMealGeneratedForUsers($users, min($endDate, now()->format('Y-m-d')));

        $mealSetting = app(SettingService::class)->getSettingContentBySlug('meal_setting');
        $defaultFullRate = (float)($mealSetting['full'] ?? 30);
        $defaultHalfRate = (float)($mealSetting['half'] ?? ($defaultFullRate / 2));

        $specialMeals = SpecialMeal::whereBetween('date', [$startDate, $endDate])
            ->where('status', 1)
            ->get()
            ->keyBy(function($item) {
                return Carbon::parse($item->date)->format('Y-m-d');
            });

        $userMeals = Meal::whereBetween('date', [$startDate, $endDate])
            ->get()
            ->groupBy('user_id');

        $mealSummary = Meal::selectRaw('
            user_id,
            SUM(full_meal) as full_total,
            SUM(half_meal) as half_total
        ')
            ->whereBetween('date', [$startDate, $endDate])
            ->where('is_off', 0)
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

        $guestMealSummary = GuestMeal::selectRaw('
            user_id,
            SUM(meal_count) as guest_meal_count,
            SUM(amount) as guest_meal_total
        ')
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', 1)
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $mealSummaryByUser = collect($users)->mapWithKeys(function ($user) use (
            $mealSummary,
            $depositSummary,
            $guestMealSummary,
            $defaultFullRate,
            $defaultHalfRate,
            $userMeals,
            $specialMeals
        ) {
            $fullTotal = (int)optional($mealSummary->get($user->id))->full_total;
            $halfTotal = (int)optional($mealSummary->get($user->id))->half_total;
            $depositAmount = (float)optional($depositSummary->get($user->id))->deposit_total;
            $guestMealCount = (float)optional($guestMealSummary->get($user->id))->guest_meal_count;
            $guestMealCost = (float)optional($guestMealSummary->get($user->id))->guest_meal_total;

            $totalMeal = $fullTotal + ($halfTotal * 0.5) + $guestMealCount;

            $uMeals = $userMeals->get($user->id, collect());
            $mealCost = 0;
            if ($uMeals->isNotEmpty()) {
                foreach ($uMeals as $m) {
                    if (!$m->is_off) {
                        $mDate = Carbon::parse($m->date)->format('Y-m-d');
                        $spec = $specialMeals->get($mDate);
                        if ($m->full_meal) {
                            $fRate = $spec ? (float)$spec->full_rate : $defaultFullRate;
                            $mealCost += $fRate;
                        } elseif ($m->half_meal) {
                            $hRate = $spec ? (float)($spec->half_rate ?? ($spec->full_rate / 2)) : $defaultHalfRate;
                            $mealCost += $hRate;
                        }
                    }
                }
            } else {
                $mealCost = ($fullTotal * $defaultFullRate) + ($halfTotal * $defaultHalfRate);
            }

            $totalCostWithGuest = $mealCost + $guestMealCost;
            $balance = $depositAmount - $totalCostWithGuest;

            return [
                $user->id => (object)[
                    'user_id'          => $user->id,
                    'half_total'       => $halfTotal,
                    'full_total'       => $fullTotal,
                    'guest_meal_count' => $guestMealCount,
                    'guest_meal_cost'  => $guestMealCost,
                    'total_meal'       => $totalMeal,
                    'meal_cost'        => $totalCostWithGuest,
                    'deposit_amount'   => $depositAmount,
                    'balance'          => $balance,
                ]
            ];
        });

        return [
            'selectedMonth'     => $selectedMonth,
            'half_rate'         => $defaultHalfRate,
            'full_rate'         => $defaultFullRate,
            'mealSummaryByUser' => $mealSummaryByUser,
            'summary' => [
                'total_member'     => collect($users)->count(),
                'total_half'       => $mealSummaryByUser->sum('half_total'),
                'total_full'       => $mealSummaryByUser->sum('full_total'),
                'total_guest_meal' => $mealSummaryByUser->sum('guest_meal_count'),
                'total_guest_cost' => $mealSummaryByUser->sum('guest_meal_cost'),
                'total_meal'       => $mealSummaryByUser->sum('total_meal'),
                'total_meal_cost'  => $mealSummaryByUser->sum('meal_cost'),
                'total_deposit'    => $mealSummaryByUser->sum('deposit_amount'),
                'total_balance'    => $mealSummaryByUser->sum('balance'),
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
        $fullRate = (float)($mealSetting['full'] ?? 30);
        $halfRate = (float)($mealSetting['half'] ?? ($fullRate / 2));

        $mealSummary = Meal::selectRaw('SUM(full_meal) as full_total, SUM(half_meal) as half_total')
            ->whereBetween('date', [$startDate, $endDate])
            ->where('is_off', 0)
            ->first();

        $depositSummary = Deposit::selectRaw('SUM(amount) as deposit_total')
            ->whereBetween('date', [$startDate, $endDate])
            ->first();

        $guestMealSummary = GuestMeal::selectRaw('SUM(meal_count) as guest_meal_count, SUM(amount) as guest_meal_total')
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', 1)
            ->first();

        $fullTotal = (int)($mealSummary->full_total ?? 0);
        $halfTotal = (int)($mealSummary->half_total ?? 0);
        $depositAmount = (float)($depositSummary->deposit_total ?? 0);
        $guestMealCount = (float)($guestMealSummary->guest_meal_count ?? 0);
        $guestMealCost = (float)($guestMealSummary->guest_meal_total ?? 0);

        $totalMeal = $fullTotal + ($halfTotal * 0.5) + $guestMealCount;
        $mealCost = ($fullTotal * $fullRate) + ($halfTotal * $halfRate) + $guestMealCost;
        $balance = $depositAmount - $mealCost;

        return [
            'selectedMonth' => $selectedMonth,
            'half_rate'     => $halfRate,
            'full_rate'     => $fullRate,
            'summary' => [
                'total_half'       => $halfTotal,
                'total_full'       => $fullTotal,
                'total_guest_meal' => $guestMealCount,
                'total_guest_cost' => $guestMealCost,
                'total_meal'       => $totalMeal,
                'total_meal_cost'  => $mealCost,
                'total_deposit'    => $depositAmount,
                'total_balance'    => $balance,
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
        $defaultFullRate = (float)($mealSetting['full'] ?? 30);
        $defaultHalfRate = (float)($mealSetting['half'] ?? ($defaultFullRate / 2));

        $specialMeals = SpecialMeal::whereBetween('date', [$startDate, $endDate])
            ->where('status', 1)
            ->get()
            ->keyBy(function($item) {
                return Carbon::parse($item->date)->format('Y-m-d');
            });

        $userMeals = Meal::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $mealData = Meal::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('is_off', 0)
            ->selectRaw("SUM(full_meal) as full_total, SUM(half_meal) as half_total")
            ->first();

        $depositTotal = Deposit::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        $guestMealData = GuestMeal::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', 1)
            ->selectRaw("SUM(meal_count) as guest_meal_count, SUM(amount) as guest_meal_total")
            ->first();

        $guestMealCount = (float)($guestMealData->guest_meal_count ?? 0);
        $guestMealCost = (float)($guestMealData->guest_meal_total ?? 0);

        $fullTotal = (int)($mealData->full_total ?? 0);
        $halfTotal = (int)($mealData->half_total ?? 0);

        $dayHalfTotal = 0;
        $nightHalfTotal = 0;
        $mealCost = 0;

        if ($userMeals->isNotEmpty()) {
            foreach ($userMeals as $m) {
                if (!$m->is_off) {
                    $mDate = Carbon::parse($m->date)->format('Y-m-d');
                    $spec = $specialMeals->get($mDate);
                    if ($m->full_meal) {
                        $fRate = $spec ? (float)$spec->full_rate : $defaultFullRate;
                        $mealCost += $fRate;
                    } elseif ($m->half_meal) {
                        $hRate = $spec ? (float)($spec->half_rate ?? ($spec->full_rate / 2)) : $defaultHalfRate;
                        $mealCost += $hRate;
                        $mNote = strtolower($m->note ?? '');
                        if ($mNote === 'night' || $mNote === 'half_night') {
                            $nightHalfTotal++;
                        } else {
                            $dayHalfTotal++;
                        }
                    }
                }
            }
        } else {
            $mealCost = ($fullTotal * $defaultFullRate) + ($halfTotal * $defaultHalfRate);
        }

        $totalMeal = $fullTotal + ($halfTotal * 0.5) + $guestMealCount;
        $totalMealCost = $mealCost + $guestMealCost;
        $balance = $depositTotal - $totalMealCost;

        return (object)[
            'selectedMonth'    => $selectedMonth,
            'user_id'          => $user->id,
            'user_name'        => $user->name,
            'half_rate'        => $defaultHalfRate,
            'full_rate'        => $defaultFullRate,
            'day_half_total'   => $dayHalfTotal,
            'night_half_total' => $nightHalfTotal,
            'half_total'       => $halfTotal,
            'full_total'       => $fullTotal,
            'guest_meal_count' => $guestMealCount,
            'guest_meal_cost'  => $guestMealCost,
            'total_meal'       => $totalMeal,
            'meal_cost'        => $totalMealCost,
            'deposit_total'    => $depositTotal,
            'balance'          => $balance,
        ];
    }

    public function allTimeMealSummary($userId)
    {
        $mealSetting = app(SettingService::class)->getSettingContentBySlug('meal_setting');
        $fullRate = (float)($mealSetting['full'] ?? 30);
        $halfRate = (float)($mealSetting['half'] ?? ($fullRate / 2));

        $mealData = Meal::where('user_id', $userId)
            ->where('is_off', 0)
            ->selectRaw("SUM(full_meal) as full_total, SUM(half_meal) as half_total")
            ->first();

        $guestMealData = GuestMeal::where('user_id', $userId)
            ->where('status', 1)
            ->selectRaw("SUM(meal_count) as guest_meal_count, SUM(amount) as guest_meal_total")
            ->first();

        $guestMealCount = (float)($guestMealData->guest_meal_count ?? 0);
        $guestMealCost = (float)($guestMealData->guest_meal_total ?? 0);

        $fullTotal = (int)($mealData->full_total ?? 0);
        $halfTotal = (int)($mealData->half_total ?? 0);
        $totalMeal = $fullTotal + ($halfTotal * 0.5) + $guestMealCount;
        $mealCost = ($fullTotal * $fullRate) + ($halfTotal * $halfRate) + $guestMealCost;

        return (object)[
            'half_rate'        => $halfRate,
            'full_rate'        => $fullRate,
            'half_total'       => $halfTotal,
            'full_total'       => $fullTotal,
            'guest_meal_count' => $guestMealCount,
            'guest_meal_cost'  => $guestMealCost,
            'total_meal'       => $totalMeal,
            'meal_cost'        => $mealCost,
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

        $isExistingUser = ($hasAnyDepositHistory || $hasAnyMealHistory);
        $isZero         = ($balance <= 0);

        $warningMessage = null;
        $alertType      = null;

        if ($isZero) {
            $alertType = 'meal_notice';
            if ($isExistingUser) {
                $warningMessage = "📌 মেসের মিল সার্ভিস চালু করতে এবং মিল ডিপোজিট জমা দিতে অনুগ্রহ করে এডমিন এর সাথে যোগাযোগ করুন। আপনার ডিপোজিট ব্যালেন্স ৳ " . number_format($balance, 2) . " টাকা।";
            } else {
                $warningMessage = "📌 মেসের মিল সার্ভিস চালু করতে এবং মিল ডিপোজিট জমা দিতে অনুগ্রহ করে এডমিন এর সাথে যোগাযোগ করুন।";
            }
        }

        return [
            'deposit_total'   => $depositTotal,
            'meal_cost'       => $mealCost,
            'balance'         => $balance,
            'is_zero_deposit' => $isZero,
            'is_existing'     => $isExistingUser,
            'alert_type'      => $alertType,
            'warning_message' => $warningMessage,
        ];


    }

    public function ensureAutoMealGeneratedForUsers($users, $date = null)
    {
        $targetDate = $date ? Carbon::parse($date)->format('Y-m-d') : now()->format('Y-m-d');
        $authId = auth()->id() ?? 1;

        $activeBookings = \App\Models\Backend\RoomBookingHistory::whereNull('today_check_out')
            ->whereDate('check_in', '<=', $targetDate)
            ->get();

        foreach ($users as $user) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $user->phone ?? '');
            $cleanPhone10 = strlen($cleanPhone) >= 6 ? (strlen($cleanPhone) > 10 ? substr($cleanPhone, -10) : $cleanPhone) : '';

            $userBooking = $user->active_booking ?? null;
            if (!$userBooking) {
                $userBooking = $activeBookings->first(function ($b) use ($cleanPhone10) {
                    if (!empty($cleanPhone10)) {
                        $bPhone = preg_replace('/[^0-9]/', '', $b->phone ?? '');
                        $bPhone10 = strlen($bPhone) >= 6 ? (strlen($bPhone) > 10 ? substr($bPhone, -10) : $bPhone) : '';
                        if (!empty($bPhone10) && $bPhone10 === $cleanPhone10) {
                            return true;
                        }
                    }
                    return false;
                });
            }

            if ($userBooking) {
                $bookingCheckIn = Carbon::parse($userBooking->check_in)->format('Y-m-d');
                $monthStart = Carbon::parse($targetDate)->startOfMonth()->format('Y-m-d');
                $syncStartDate = max($bookingCheckIn, $monthStart);

                // User's deposits in this month / up to target date
                $userDeposits = Deposit::where('user_id', $user->id)
                    ->whereYear('date', Carbon::parse($targetDate)->year)
                    ->whereMonth('date', Carbon::parse($targetDate)->month)
                    ->get();

                $firstDepositDate = $userDeposits->min('date');

                $cStart = Carbon::parse($syncStartDate);
                $cEnd = Carbon::parse($targetDate);

                for ($curr = $cStart->copy(); $curr->lte($cEnd); $curr->addDay()) {
                    $loopDate = $curr->format('Y-m-d');

                    // 1. Check if user is on leave / checked out on $loopDate
                    $activeLeave = \App\Models\Backend\ResidentCheckInOut::where(function($q) use ($user, $userBooking) {
                        if ($userBooking) {
                            $q->where('room_booking_history_id', $userBooking->id);
                        } else {
                            $q->where('user_id', $user->id);
                        }
                    })
                    ->whereDate('check_out_date', '<=', $loopDate)
                    ->where(function($q) use ($loopDate) {
                        $q->where(function($sub) use ($loopDate) {
                            $sub->where('status', 1)
                                ->whereDate('check_in_date', '>=', $loopDate);
                        })->orWhere(function($sub) {
                            $sub->where('status', 0);
                        });
                    })
                    ->latest('check_out_date')
                    ->first();

                    // 2. Check if user has an approved MealRequest covering $loopDate
                    $activeOffReq = \App\Models\Backend\MealRequest::where('user_id', $user->id)
                        ->where('status', 1)
                        ->whereDate('date', '<=', $loopDate)
                        ->where(function($q) use ($loopDate) {
                            $q->whereDate('end_date', '>=', $loopDate)
                              ->orWhereNull('end_date');
                        })
                        ->latest()
                        ->first();

                    $meal = Meal::where('user_id', $user->id)->whereDate('date', $loopDate)->first();

                    // Check if deposit exists on or before $loopDate
                    $hasDepositOnLoopDate = $firstDepositDate && ($loopDate >= $firstDepositDate);

                    if ($activeLeave) {
                        $isReturnedToday = ($activeLeave->status == 1 && $activeLeave->check_in_date && \Carbon\Carbon::parse($activeLeave->check_in_date)->toDateString() === $loopDate);
                        $leaveNote = $isReturnedToday ? 'Auto Meal OFF (Checked In - Resumes Tomorrow)' : 'Auto Meal OFF (Checked Out / Leave)';

                        if (!$meal) {
                            Meal::create([
                                'user_id'   => $user->id,
                                'date'      => $loopDate,
                                'half_meal' => 0,
                                'full_meal' => 0,
                                'is_off'    => 1,
                                'made_by'   => $authId,
                                'note'      => $leaveNote,
                            ]);
                        } else {
                            $meal->update([
                                'half_meal' => 0,
                                'full_meal' => 0,
                                'is_off'    => 1,
                                'note'      => $leaveNote,
                            ]);
                        }
                    } elseif ($activeOffReq) {
                        $fMeal = 0; $hMeal = 0; $isO = 0; $nNote = null;
                        if ($activeOffReq->request_type === 'off') {
                            $isO = 1; $nNote = 'Meal OFF (Approved Request)';
                        } elseif ($activeOffReq->request_type === 'full') {
                            $fMeal = 1; $nNote = 'Full Meal';
                        } elseif ($activeOffReq->request_type === 'half_day' || $activeOffReq->request_type === 'day') {
                            $hMeal = 1; $nNote = 'day';
                        } elseif ($activeOffReq->request_type === 'half_night' || $activeOffReq->request_type === 'night') {
                            $hMeal = 1; $nNote = 'night';
                        }

                        if (!$meal) {
                            Meal::create([
                                'user_id'   => $user->id,
                                'date'      => $loopDate,
                                'half_meal' => $hMeal,
                                'full_meal' => $fMeal,
                                'is_off'    => $isO,
                                'made_by'   => $authId,
                                'note'      => $nNote,
                            ]);
                        } else {
                            if (str_contains($meal->note ?? '', 'Auto') || empty($meal->note)) {
                                $meal->update([
                                    'half_meal' => $hMeal,
                                    'full_meal' => $fMeal,
                                    'is_off'    => $isO,
                                    'note'      => $nNote,
                                ]);
                            }
                        }
                    } elseif (!$hasDepositOnLoopDate) {
                        // Before deposit date, meal is OFF due to Zero Deposit
                        if (!$meal) {
                            Meal::create([
                                'user_id'   => $user->id,
                                'date'      => $loopDate,
                                'half_meal' => 0,
                                'full_meal' => 0,
                                'is_off'    => 1,
                                'made_by'   => $authId,
                                'note'      => 'Auto Meal OFF (Zero Deposit)',
                            ]);
                        } else {
                            if (str_contains($meal->note ?? '', 'Auto') || empty($meal->note)) {
                                $meal->update([
                                    'half_meal' => 0,
                                    'full_meal' => 0,
                                    'is_off'    => 1,
                                    'note'      => 'Auto Meal OFF (Zero Deposit)',
                                ]);
                            }
                        }
                    } else {
                        // From deposit date onwards, meal is ON as Full Meal
                        if (!$meal) {
                            Meal::create([
                                'user_id'   => $user->id,
                                'date'      => $loopDate,
                                'half_meal' => 0,
                                'full_meal' => 1,
                                'is_off'    => 0,
                                'made_by'   => $authId,
                                'note'      => 'Auto Full Meal',
                            ]);
                        } else {
                            if (str_contains($meal->note ?? '', 'Auto') || str_contains($meal->note ?? '', 'Zero Deposit') || empty($meal->note) || $meal->is_off == 1) {
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
        } elseif ($mealType === 'off') {
            $isOff = 1;
            $note = (auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('staffs')))
                ? 'Admin Turned OFF'
                : 'Meal Turned OFF';
        } elseif ($mealType === 'half_day' || $mealType === 'day') {
            $halfMeal = 1;
            $note = 'day';
        } elseif ($mealType === 'half_night' || $mealType === 'night') {
            $halfMeal = 1;
            $note = 'night';
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
