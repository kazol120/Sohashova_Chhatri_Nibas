<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Backend\Floor;
use App\Models\Backend\Room;
use App\Models\Backend\RoomSeat;
use App\Models\Backend\RoomBookingHistory;
use App\Models\Backend\Staffs;
use App\Models\Backend\Expense;
use App\Models\Backend\ProductDistribution;
use App\Models\Backend\MonthlyPayment;
use App\Models\Backend\Meal;
use Carbon\Carbon;
use Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function home()
    {
        $data['page_title'] = "Dashboard";
        $data['floorscount'] = Floor::count();
        $data['roomcount'] = Room::count();
        $data['totalseatcount'] = RoomSeat::count();
        $data['availableseatcount'] = RoomSeat::where('status', 0)->count();
        $data['bookedseatcount'] = RoomSeat::where('status', 1)->count();
        $data['availPercent'] = $data['totalseatcount'] > 0 ? round(($data['availableseatcount'] / $data['totalseatcount']) * 100) : 0;

        $user = Auth::user();
        if ($user->hasRole('admin')) {
            $data['roombookingcount'] = RoomBookingHistory::where('status', 0)->count();
            $data['releasehistorycount'] = RoomBookingHistory::where('status', 1)->count();
            $data['staffscount'] = Staffs::count();
        } else {
            $cleanPhone = preg_replace('/[^0-9]/', '', $user->phone ?? '');
            $data['roombookingcount'] = RoomBookingHistory::where('status', 0)->where(function ($q) use ($user, $cleanPhone) {
                if (!empty($user->email)) {
                    $q->orWhere('email', $user->email);
                }
                if (!empty($cleanPhone)) {
                    $q->orWhere('phone', 'like', "%{$cleanPhone}%");
                }
            })->count();
            $data['releasehistorycount'] = RoomBookingHistory::where('status', 1)->where(function ($q) use ($user, $cleanPhone) {
                if (!empty($user->email)) {
                    $q->orWhere('email', $user->email);
                }
                if (!empty($cleanPhone)) {
                    $q->orWhere('phone', 'like', "%{$cleanPhone}%");
                }
            })->count();

            if ($user->hasRole('staffs')) {
                $data['staffscount'] = Staffs::where(function ($q) use ($user) {
                    $q->where('email', $user->email)
                      ->orWhere('phone', $user->phone);
                })->count();
            } else {
                $data['staffscount'] = 0;
            }
        }

        // Student/Resident User Specific Data
        if (!$user->hasRole('admin') && !$user->hasRole('staffs')) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $user->phone ?? '');
            $cleanPhone10 = strlen($cleanPhone) >= 6 ? (strlen($cleanPhone) > 10 ? substr($cleanPhone, -10) : $cleanPhone) : '';

            $userBooking = RoomBookingHistory::with(['division', 'district', 'thana'])->where('status', 0)
                ->where(function ($q) use ($user, $cleanPhone10) {
                    if (!empty($cleanPhone10)) {
                        $q->whereRaw("REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+', '') LIKE ?", ["%{$cleanPhone10}%"]);
                    } elseif (!empty($user->email)) {
                        $q->where('email', $user->email);
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })->latest()->first();

            $data['userBooking'] = $userBooking;
            $data['myMealCount'] = Meal::where('user_id', $user->id)->count();

            $mealDepositInfo = app(\App\Services\MealService::class)->getUserMealDepositBalance($user->id);
            $data['mealDepositBalance'] = $mealDepositInfo['balance'];
            $data['mealDepositWarning'] = $mealDepositInfo['is_zero_deposit'] ? $mealDepositInfo['warning_message'] : null;

            app(\App\Services\MealService::class)->ensureAutoMealGeneratedForUsers(collect([$user]), today());
            $data['todayMealStatus']   = Meal::where('user_id', $user->id)->whereDate('date', today())->first();

            if ($userBooking) {
                $data['myTotalPaid'] = MonthlyPayment::where('room_booking_history_id', $userBooking->id)->sum('paid_amount');
                $data['myOutstandingDue'] = MonthlyPayment::where('room_booking_history_id', $userBooking->id)
                    ->where('status', '!=', 'paid')
                    ->sum('due_amount');
            } else {
                $data['myTotalPaid'] = 0;
                $data['myOutstandingDue'] = 0;
            }
        }

        $data['todayacheackin'] = RoomBookingHistory::where('status', 0)->whereDate('check_in', today())->count();
        $data['todaycheackout'] = RoomBookingHistory::whereDate('today_check_out', today())->count();

        $data['todayExpense'] = Expense::whereDate('created_at', Carbon::today())->sum('expense_amount');
        $data['thisMonthCollection'] = MonthlyPayment::whereMonth('created_at', Carbon::now()->month)
                                                    ->whereYear('created_at', Carbon::now()->year)
                                                    ->sum('paid_amount');
        $data['thisMonthDue'] = MonthlyPayment::whereMonth('created_at', Carbon::now()->month)
                                             ->whereYear('created_at', Carbon::now()->year)
                                             ->sum('due_amount');
        $data['todayMealCount'] = Meal::whereDate('date', Carbon::today())->count();

        $data['todayproductdistribution'] = ProductDistribution::whereDate('purchase_date', today())
            ->get()
            ->groupBy(function ($item) {
                return $item->purchase_date . '_' . $item->floor_id . '_' . $item->room_id . '_' . $item->customer_id;
            })
            ->count();

        // 7-day trends data for mini sparkline graphs
        $last7Days = collect(range(6, 0))->map(function($daysAgo) {
            return Carbon::today()->subDays($daysAgo);
        });

        $rentTrends = $last7Days->map(function($date) {
            return (float) MonthlyPayment::whereDate('created_at', $date)->sum('paid_amount');
        })->values()->toArray();

        $expenseTrends = $last7Days->map(function($date) {
            return (float) Expense::whereDate('created_at', $date)->sum('expense_amount');
        })->values()->toArray();

        $mealTrends = $last7Days->map(function($date) {
            return (int) Meal::whereDate('date', $date)->count();
        })->values()->toArray();

        $productTrends = $last7Days->map(function($date) {
            return (int) ProductDistribution::whereDate('purchase_date', $date)->count();
        })->values()->toArray();

        $data['rentTrends'] = json_encode($rentTrends);
        $data['expenseTrends'] = json_encode($expenseTrends);
        $data['mealTrends'] = json_encode($mealTrends);
        $data['productTrends'] = json_encode($productTrends);

        return view('backend.dashboard.welcome', $data);
    }
}
