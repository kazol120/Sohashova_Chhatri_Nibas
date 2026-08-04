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

        return view('backend.dashboard.welcome', $data);
    }
}
