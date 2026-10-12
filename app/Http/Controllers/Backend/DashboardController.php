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
        $isAdmin = $user->hasAnyRole(['admin', 'super-admin']);
        $isStaff = $user->hasRole('staffs');

        if ($isAdmin) {
            $data['roombookingcount'] = RoomBookingHistory::whereNull('today_check_out')->where('status', 1)->count();
            $data['pendingbookingcount'] = RoomBookingHistory::whereNull('today_check_out')->where('status', 0)->count();
            $data['releasehistorycount'] = RoomBookingHistory::whereNotNull('today_check_out')->count();
            $data['staffscount'] = Staffs::count();
        } else {
            $cleanPhone = preg_replace('/[^0-9]/', '', $user->phone ?? '');
            $data['pendingbookingcount'] = 0;
            $data['roombookingcount'] = RoomBookingHistory::whereNull('today_check_out')->where('status', 1)->where(function ($q) use ($user, $cleanPhone) {
                if (!empty($user->email)) {
                    $q->orWhere('email', $user->email);
                }
                if (!empty($cleanPhone)) {
                    $q->orWhere('phone', 'like', "%{$cleanPhone}%");
                }
            })->count();
            $data['releasehistorycount'] = RoomBookingHistory::whereNotNull('today_check_out')->where(function ($q) use ($user, $cleanPhone) {
                if (!empty($user->email)) {
                    $q->orWhere('email', $user->email);
                }
                if (!empty($cleanPhone)) {
                    $q->orWhere('phone', 'like', "%{$cleanPhone}%");
                }
            })->count();

            if ($isStaff) {
                $data['staffscount'] = Staffs::where(function ($q) use ($user) {
                    $q->where('email', $user->email)
                      ->orWhere('phone', $user->phone);
                })->count();
            } else {
                $data['staffscount'] = 0;
            }
        }

        // Student/Resident User Specific Data
        if (!$isAdmin && !$isStaff) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $user->phone ?? '');
            $cleanPhone10 = strlen($cleanPhone) >= 6 ? (strlen($cleanPhone) > 10 ? substr($cleanPhone, -10) : $cleanPhone) : '';

            $userBooking = RoomBookingHistory::with(['division', 'district', 'thana'])
                ->whereNull('today_check_out')
                ->where(function ($q) use ($user, $cleanPhone10) {
                    if (!empty($cleanPhone10)) {
                        $q->orWhereRaw("REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+', '') LIKE ?", ["%{$cleanPhone10}%"]);
                    }
                    if (!empty($user->phone)) {
                        $q->orWhere('phone', 'like', "%" . trim($user->phone) . "%");
                    }
                    if (!empty($user->email)) {
                        $q->orWhere('email', $user->email);
                    }
                    if (!empty($user->name)) {
                        $q->orWhere('full_name', $user->name);
                    }
                })->latest()->first();


            $data['userBooking'] = $userBooking;
            $userAllTimeMeals = app(\App\Services\MealService::class)->allTimeMealSummary($user->id);
            $data['myMealCount'] = $userAllTimeMeals->total_meal;

            $mealDepositInfo = app(\App\Services\MealService::class)->getUserMealDepositBalance($user->id);
            $data['mealDepositBalance']   = $mealDepositInfo['balance'];
            $data['mealDepositWarning']   = $mealDepositInfo['warning_message'];
            $data['mealDepositAlertType'] = $mealDepositInfo['alert_type'] ?? null;
            $data['isZeroDeposit']        = $mealDepositInfo['is_zero_deposit'];

            app(\App\Services\MealService::class)->ensureAutoMealGeneratedForUsers(collect([$user]), today());
            $todayMeal = Meal::where('user_id', $user->id)->whereDate('date', today())->first();
            $data['todayMealStatus'] = $todayMeal;

            $studentActiveCheckout = null;
            if (isset($userBooking) && $userBooking) {
                $studentActiveCheckout = \App\Models\Backend\ResidentCheckInOut::where('room_booking_history_id', $userBooking->id)
                    ->where('status', 0)
                    ->latest('check_out_date')
                    ->first();
            }
            if (!$studentActiveCheckout && $user) {
                $studentActiveCheckout = \App\Models\Backend\ResidentCheckInOut::where('user_id', $user->id)
                    ->where('status', 0)
                    ->latest('check_out_date')
                    ->first();
            }

            $isCurrentlyOutside = false;
            if ($studentActiveCheckout) {
                $checkoutDate = Carbon::parse($studentActiveCheckout->check_out_date)->startOfDay();
                if ($checkoutDate->lte(Carbon::today())) {
                    $isCurrentlyOutside = true;
                }
            }

            $isAdminTurnedOff = false;
            // Only consider admin-turned-off if NOT currently checked-out/outside and user HAS deposit and meal was turned off by admin
            $mealNoteLower = strtolower($todayMeal ? ($todayMeal->note ?? '') : '');
            $isCheckoutMealOff = str_contains($mealNoteLower, 'check') || str_contains($mealNoteLower, 'leave') || str_contains($mealNoteLower, 'বাড়ি') || str_contains($mealNoteLower, 'ছুটি');

            if (!$isCurrentlyOutside && !$isCheckoutMealOff && !$mealDepositInfo['is_zero_deposit'] && $todayMeal && $todayMeal->is_off) {
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
            $data['isAdminTurnedOff'] = $isAdminTurnedOff;

            // Check if user has active or upcoming/pending meal request
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
            $data['latestMealRequest'] = $latestMealReq;

            if ($userBooking) {
                $data['myTotalPaid'] = MonthlyPayment::where('room_booking_history_id', $userBooking->id)->sum('paid_amount');
                $data['myOutstandingDue'] = MonthlyPayment::where('room_booking_history_id', $userBooking->id)
                    ->where('status', '!=', 'paid')
                    ->sum('due_amount');

                // Resident electricity bill data
                $currentMonthStr = Carbon::now()->format('Y-m');
                $curElectPayment = MonthlyPayment::where('room_booking_history_id', $userBooking->id)
                    ->where('payment_month', $currentMonthStr)
                    ->first();
                $data['myCurrentMonthElectBill'] = $curElectPayment ? (float)($curElectPayment->electricity_bill ?? 0) : 0;
                $data['myTotalElectBill'] = (float) MonthlyPayment::where('room_booking_history_id', $userBooking->id)->sum('electricity_bill');
                $data['myElectPaymentsCount'] = MonthlyPayment::where('room_booking_history_id', $userBooking->id)->where('electricity_bill', '>', 0)->count();
            } else {
                $data['myTotalPaid'] = 0;
                $data['myOutstandingDue'] = 0;
                $data['myCurrentMonthElectBill'] = 0;
                $data['myTotalElectBill'] = 0;
                $data['myElectPaymentsCount'] = 0;
            }
        }

        $data['todayacheackin'] = RoomBookingHistory::where('status', 0)->whereDate('check_in', today())->count();
        $data['todaycheackout'] = RoomBookingHistory::whereDate('today_check_out', today())->count();

        // Resident In-Hostel vs Outside tracking lists & counts
        $outsideLogs = \App\Models\Backend\ResidentCheckInOut::where('status', 0)->latest('check_out_date')->get();
        $outsideBookingIds = $outsideLogs->pluck('room_booking_history_id')->filter()->toArray();
        $outsidePhones = $outsideLogs->pluck('phone')->filter()->map(function($p) {
            $c = preg_replace('/[^0-9]/', '', $p ?? '');
            return strlen($c) >= 10 ? substr($c, -10) : $c;
        })->toArray();

        $activeBookings = RoomBookingHistory::whereNull('today_check_out')
            ->whereNull('deleted_at')
            ->where('status', 1)
            ->orderBy('full_name', 'asc')
            ->get();

        $insideResidents = $activeBookings->reject(function($b) use ($outsideBookingIds, $outsidePhones) {
            if (in_array($b->id, $outsideBookingIds)) return true;
            $clean = preg_replace('/[^0-9]/', '', $b->phone ?? '');
            $p10 = strlen($clean) >= 10 ? substr($clean, -10) : $clean;
            if (!empty($p10) && in_array($p10, $outsidePhones)) return true;
            return false;
        })->map(function($b) {
            $floorRoomSeat = '';
            $items = is_string($b->floor_number_room_number_roomprice) 
                ? json_decode($b->floor_number_room_number_roomprice, true) 
                : $b->floor_number_room_number_roomprice;
            if (is_array($items) && !empty($items)) {
                $details = [];
                foreach ($items as $item) {
                    $f = $item['floornumber'] ?? '';
                    $r = $item['roomnumber'] ?? '';
                    $details[] = "Floor: {$f}, Room/Seat: {$r}";
                }
                $floorRoomSeat = implode(' | ', $details);
            } else {
                $floorRoomSeat = trim(($b->floornumber ?? '') . ' - ' . ($b->room_number ?? ''), ' -');
            }

            return [
                'id'              => $b->id,
                'name'            => $b->full_name,
                'phone'           => $b->phone,
                'email'           => $b->email,
                'floor_room_seat' => $floorRoomSeat ?: 'Hostel Seat',
                'check_in_date'   => $b->check_in ? Carbon::parse($b->check_in)->format('d M, Y') : '-',
                'guardian_phone'  => $b->guardian_number ?: ($b->parents_phone_number ?: '-'),
                'address'         => $b->present_address ?: '-',
            ];
        })->values();

        $outsideResidents = $outsideLogs->map(function($log) {
            $isOverdue = ($log->expected_return_date && Carbon::parse($log->expected_return_date)->isPast() && !Carbon::parse($log->expected_return_date)->isToday());
            return [
                'id'                   => $log->id,
                'booking_id'           => $log->room_booking_history_id,
                'name'                 => $log->resident_name,
                'phone'                => $log->phone,
                'floor_room_seat'      => $log->floor_room_seat ?: 'Hostel Seat',
                'check_out_date'       => $log->check_out_date ? Carbon::parse($log->check_out_date)->format('d M, Y (h:i A)') : '-',
                'expected_return_date' => $log->expected_return_date ? Carbon::parse($log->expected_return_date)->format('d M, Y') : 'অনির্দিষ্ট',
                'leave_reason'         => $log->leave_reason ?: 'ছুটি / বাড়ি যাওয়া',
                'emergency_contact'    => $log->emergency_contact ?: '-',
                'is_overdue'           => $isOverdue,
                'created_by'           => $log->created_by,
                'admin_notes'          => $log->admin_notes,
            ];
        })->values();

        $data['dashInsideCount']   = $insideResidents->count();
        $data['dashOutsideCount']  = $outsideResidents->count();
        $data['insideResidents']   = $insideResidents;
        $data['outsideResidents']  = $outsideResidents;

        $data['todayExpense'] = Expense::whereDate('created_at', Carbon::today())->sum('expense_amount');
        $currentMonth = Carbon::now('Asia/Dhaka')->format('Y-m');
        $data['thisMonthCollection'] = (float) MonthlyPayment::where('payment_month', $currentMonth)->sum('paid_amount');
        $data['thisMonthDue']        = (float) MonthlyPayment::where('payment_month', $currentMonth)->sum('due_amount');
        $usersWithMealRole = app(\App\Services\UserService::class)->usersWithRole();
        app(\App\Services\MealService::class)->ensureAutoMealGeneratedForUsers($usersWithMealRole, Carbon::today());
        $data['todayMealCount'] = $usersWithMealRole->count();

        // Upcoming Special Meals for resident dashboard alert
        $data['upcomingSpecialMeals'] = \App\Models\Backend\SpecialMeal::where('status', 1)
            ->whereDate('date', '>=', Carbon::today()->format('Y-m-d'))
            ->orderBy('date', 'asc')
            ->get();

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
