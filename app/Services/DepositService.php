<?php

namespace App\Services;

use App\Models\Backend\Deposit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepositService
{
    public function depositList()
    {
        return Deposit::latest()->with('user', 'madeBy')->get();
    }

    public function monthlyDepositList($selectedMonth = null)
    {
        $selectedMonth = $selectedMonth ?: now()->format('Y-m');

        if ($selectedMonth > now()->format('Y-m')) {
            $selectedMonth = now()->format('Y-m');
        }

        $startDate = Carbon::parse($selectedMonth . '-01')->startOfMonth()->format('Y-m-d');
        $endDate   = Carbon::parse($selectedMonth . '-01')->endOfMonth()->format('Y-m-d');

        $deposits = Deposit::with(['user:id,name,phone', 'madeBy:id,name'])
            ->whereBetween('date', [$startDate, $endDate])
            ->latest('date')
            ->latest('id')
            ->get();

        // Get active non-deleted, non-checked-out booking phone numbers & emails
        $activeBookings = \App\Models\Backend\RoomBookingHistory::whereNull('today_check_out')->get(['phone', 'email']);
        $validPhones = $activeBookings->pluck('phone')->map(function($p) {
            $c = preg_replace('/[^0-9]/', '', (string)$p);
            return strlen($c) >= 10 ? substr($c, -10) : $c;
        })->filter()->unique()->toArray();
        $validEmails = $activeBookings->pluck('email')->filter(function($e) {
            return !empty(trim((string)$e));
        })->unique()->toArray();

        // Group by user_id and keep only active non-checked-out, non-deleted residents
        $grouped = $deposits->groupBy('user_id')->map(function ($items, $userId) use ($validPhones, $validEmails) {
            $first = $items->first();
            $user = $first->user ?? null;
            if (!$user) return null;

            // If user status is explicitly 0 (deactivated due to deleted booking)
            if (isset($user->status) && (int)$user->status === 0) {
                return null;
            }

            // Check if user has an active booking in room_booking_histories
            $uPhone = preg_replace('/[^0-9]/', '', (string)$user->phone);
            $uLast10 = strlen($uPhone) >= 10 ? substr($uPhone, -10) : $uPhone;

            $hasActiveBooking = (!empty($uLast10) && in_array($uLast10, $validPhones)) || 
                                (!empty($user->email) && in_array($user->email, $validEmails));
            if (!$hasActiveBooking) {
                return null;
            }

            $totalDeposit = (float)$items->sum('amount');
            $latestDate = $items->max('date');
            $depositCount = $items->count();

            // Format items list for popup
            $history = $items->map(function ($item) {
                return [
                    'id'         => $item->id,
                    'amount'     => (float)$item->amount,
                    'date'       => $item->date ? Carbon::parse($item->date)->format('d M Y') : '-',
                    'created_at' => $item->created_at ? $item->created_at->timezone('Asia/Dhaka')->format('d M Y, h:i A') : ($item->date ? Carbon::parse($item->date)->format('d M Y') : '-'),
                    'note'       => $item->note ?? '-',
                    'made_by'    => $item->madeBy->name ?? 'Admin',
                ];
            })->values();

            return (object)[
                'user_id'          => $userId,
                'user'             => $user,
                'total_deposit'    => $totalDeposit,
                'last_deposit_date'=> $latestDate ? Carbon::parse($latestDate)->format('d M Y') : '-',
                'deposit_count'    => $depositCount,
                'history'          => $history,
            ];
        })->filter()->values();

        return $grouped;
    }

    public function depositUserBy($month, $year, $userId)
    {
        return Deposit::where('user_id', $userId)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->with('user', 'madeBy')
            ->get();
    }

    public function depositCreate($request)
    {
        try {
            DB::beginTransaction();

            Deposit::create([
                'user_id' => $request->user_id,
                'amount' => $request->amount,
                'date' => $request->date,
                'made_by' => auth()->id(),
            ]);

            $user = User::find($request->user_id);
            if ($user) {
                $todayDate = today()->format('Y-m-d');
                $depositDate = $request->date ? Carbon::parse($request->date)->format('Y-m-d') : $todayDate;

                // 1. Run auto meal generator for this user
                app(MealService::class)->ensureAutoMealGeneratedForUsers(collect([$user]), $todayDate);

                // 2. Explicitly ensure today's meal is set to Full Meal ON
                $todayMeal = \App\Models\Backend\Meal::where('user_id', $user->id)->whereDate('date', $todayDate)->first();
                if ($todayMeal) {
                    $todayMeal->update([
                        'half_meal' => 0,
                        'full_meal' => 1,
                        'is_off'    => 0,
                        'made_by'   => auth()->id() ?? 1,
                        'note'      => 'Auto Full Meal',
                    ]);
                } else {
                    \App\Models\Backend\Meal::create([
                        'user_id'   => $user->id,
                        'date'      => $todayDate,
                        'half_meal' => 0,
                        'full_meal' => 1,
                        'is_off'    => 0,
                        'made_by'   => auth()->id() ?? 1,
                        'note'      => 'Auto Full Meal',
                    ]);
                }

                // If deposit date was a past/future date, ensure that meal is also ON
                if ($depositDate !== $todayDate) {
                    $depMeal = \App\Models\Backend\Meal::where('user_id', $user->id)->whereDate('date', $depositDate)->first();
                    if ($depMeal && ($depMeal->is_off || str_contains($depMeal->note ?? '', 'Zero Deposit'))) {
                        $depMeal->update([
                            'half_meal' => 0,
                            'full_meal' => 1,
                            'is_off'    => 0,
                            'note'      => 'Auto Full Meal',
                        ]);
                    }
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'ডিপোজিট সফলভাবে জমা হয়েছে এবং স্বয়ংক্রিয়ভাবে ফুল মিল (Full Meal) চালু করা হয়েছে।',
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Failed to create deposit: ' . $e->getMessage(),
            ];
        }
    }

    public function depositDelete($id)
    {
        try {
            DB::beginTransaction();
            $deposit = $this->depositById($id);
            $deposit->delete();
            DB::commit();
            return [
                'success' => true,
                'message' => 'Deposit deleted successfully.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Failed to delete deposit: ' . $e->getMessage(),
            ];
        }
    }

    public function depositById($id)
    {
        return Deposit::with('user', 'madeBy')->findOrFail($id);
    }

    public function depositDetails($id,$selectedMonth = null)
    {
        return Deposit::with('user', 'madeBy')->where('user_id',$id)->latest()->get();
    }

    public function getUserMonthlyHistory($userId, $selectedMonth)
    {
        $user = User::findOrFail($userId);

        $date = \Carbon\Carbon::parse($selectedMonth);
        $mealSummary = app(MealService::class)->singleUserMealHistory($selectedMonth, $user);
        
        $history = Deposit::where('user_id', $userId)
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->orderBy('date', 'desc')
            ->get();

        $total_positive = $history->where('amount', '>', 0)->sum('amount');
        $total_negative = $history->where('amount', '<', 0)->sum('amount');
        $balance = $history->sum('amount') - $mealSummary->meal_cost;

        return [
            'user'           => $user,
            'history'        => $history,
            'summary'        => [
                'total_deposit'  => $total_positive,
                'total_credit'   => abs($total_negative),
                'total_meal_cost' => $mealSummary->meal_cost,
                'balance'        => $balance,
                'month_name'     => $date->format('F Y')
            ],
        ];
    }

    public function monthlyDeposit($selectedMonth = null)
    {
        $selectedMonth = $selectedMonth ?: now()->format('Y-m');

        if ($selectedMonth > now()->format('Y-m')) {
            $selectedMonth = now()->format('Y-m');
        }

        $startDate = Carbon::parse($selectedMonth . '-01')->startOfMonth()->format('Y-m-d');
        $endDate   = Carbon::parse($selectedMonth . '-01')->endOfMonth()->format('Y-m-d');

        return Deposit::whereBetween('date', [$startDate, $endDate])->with('user:id,name,phone')->latest()->get();
    }

    public function allTimeDepositByUser($userId)
    {
        return Deposit::where('user_id', $userId)
            ->where('amount', '>', 0)
            ->sum('amount');
    }
}
