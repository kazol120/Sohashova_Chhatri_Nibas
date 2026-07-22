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
                Meal::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'date' => $request->date,
                    ],
                    [
                        'half_meal' => !empty($mealData['half_meal']) ? 1 : 0,
                        'full_meal' => !empty($mealData['full_meal']) ? 1 : 0,
                        'made_by' => auth()->id(),
                        'note' => !empty($mealData['note']) ? $mealData['note'] : null,
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
}
