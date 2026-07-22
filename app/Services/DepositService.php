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

        return Deposit::selectRaw('
            user_id,
            SUM(amount) as total_deposit
        ')
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('user_id')
            ->with(['user:id,name,phone'])
            ->latest('user_id')
            ->get();
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

            DB::commit();

            return [
                'success' => true,
                'message' => 'Deposit created successfully.',
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
