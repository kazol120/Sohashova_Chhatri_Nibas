<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Backend\Deposit;
use App\Models\Backend\GuestMeal;
use App\Models\Backend\Meal;
use App\Models\Backend\MonthlyPayment;
use App\Models\Backend\RoomBookingHistory;
use App\Models\Backend\RoomChangeHistory;
use App\Models\Backend\Setting;
use App\Models\User;
use App\Services\SettingService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ResidentHistoryController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $status = $request->input('status', 'all'); // 'all', 'active', 'pending', 'released', 'deleted'

        if ($status === 'deleted' || $status === 'inactive') {
            $query = RoomBookingHistory::onlyTrashed()
                ->with(['floor', 'room', 'user', 'division', 'district', 'thana'])
                ->latest('deleted_at');
        } else {
            $query = RoomBookingHistory::with(['floor', 'room', 'user', 'division', 'district', 'thana'])
                ->latest('id');

            if ($status === 'active') {
                $query->whereNull('today_check_out')->where('status', 1);
            } elseif ($status === 'pending' || $status === 'deactive') {
                $query->whereNull('today_check_out')->where(function ($q) {
                    $q->where('status', 0)->orWhereNull('status');
                });
            } elseif ($status === 'released') {
                $query->whereNotNull('today_check_out');
            }
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('room_number', 'like', "%{$search}%")
                  ->orWhere('floornumber', 'like', "%{$search}%")
                  ->orWhere('floor_number_room_number_roomprice', 'like', "%{$search}%");
            });
        }

        $allBookings = $query->get();

        $residents = $allBookings->map(function ($booking) {
            return $this->formatResidentBasic($booking);
        });

        // Summary counters
        $totalCount    = RoomBookingHistory::count();
        $activeCount   = RoomBookingHistory::whereNull('today_check_out')->where('status', 1)->count();
        $pendingCount  = RoomBookingHistory::whereNull('today_check_out')->where(function ($q) {
            $q->where('status', 0)->orWhereNull('status');
        })->count();
        $releasedCount = RoomBookingHistory::whereNotNull('today_check_out')->count();
        $deletedCount  = RoomBookingHistory::onlyTrashed()->count();

        // Dynamic years: starts at 2026 and adds future years automatically as they arrive
        $currentYear = (int)date('Y');
        $startYear   = 2026;
        $endYear     = max($currentYear, 2026);
        $yearsList   = range($startYear, $endYear);

        return view('backend.resident_history.index', compact(
            'residents', 'search', 'status', 'totalCount', 'activeCount', 'pendingCount', 'releasedCount', 'deletedCount', 'yearsList'
        ));
    }

    public function show($id, Request $request)
    {
        $booking = RoomBookingHistory::withTrashed()->with(['floor', 'room', 'user', 'division', 'district', 'thana'])->findOrFail($id);
        $selectedYear = (int)$request->input('year', date('Y'));
        if ($selectedYear < 2026) {
            $selectedYear = 2026;
        }

        $fromMonth = 1;
        $toMonth   = 12;

        if ($request->has('from_month') || $request->has('to_month')) {
            $fromMonth = (int)$request->input('from_month', 1);
            $toMonth   = (int)$request->input('to_month', 12);
        } elseif ($request->has('month')) {
            $monthInput = $request->input('month');
            if ($monthInput !== 'all' && $monthInput !== 'range' && is_numeric($monthInput)) {
                $fromMonth = (int)$monthInput;
                $toMonth   = (int)$monthInput;
            }
        }

        if ($fromMonth < 1) $fromMonth = 1;
        if ($toMonth > 12) $toMonth = 12;
        if ($fromMonth > $toMonth) {
            $temp = $fromMonth;
            $fromMonth = $toMonth;
            $toMonth = $temp;
        }

        $residentData = $this->getFullResidentProfile($booking, $selectedYear, $fromMonth, $toMonth);

        if ($request->ajax() || $request->wantsJson() || $request->query('format') === 'json' || $request->header('X-Requested-With') === 'XMLHttpRequest' || str_contains($request->header('Accept', ''), 'json')) {
            return response()->json([
                'status' => true,
                'data'   => $residentData
            ]);
        }

        return view('backend.resident_history.show', compact('booking', 'residentData'));
    }

    public function printStatement($id, Request $request)
    {
        $booking = RoomBookingHistory::withTrashed()->with(['floor', 'room', 'user', 'division', 'district', 'thana'])->findOrFail($id);
        $selectedYear = (int)$request->input('year', date('Y'));
        if ($selectedYear < 2026) {
            $selectedYear = 2026;
        }

        $fromMonth = 1;
        $toMonth   = 12;

        if ($request->has('from_month') || $request->has('to_month')) {
            $fromMonth = (int)$request->input('from_month', 1);
            $toMonth   = (int)$request->input('to_month', 12);
        } elseif ($request->has('month')) {
            $monthInput = $request->input('month');
            if ($monthInput !== 'all' && $monthInput !== 'range' && is_numeric($monthInput)) {
                $fromMonth = (int)$monthInput;
                $toMonth   = (int)$monthInput;
            }
        }

        if ($fromMonth < 1) $fromMonth = 1;
        if ($toMonth > 12) $toMonth = 12;
        if ($fromMonth > $toMonth) {
            $temp = $fromMonth;
            $fromMonth = $toMonth;
            $toMonth = $temp;
        }

        $residentData = $this->getFullResidentProfile($booking, $selectedYear, $fromMonth, $toMonth);

        $webSettingRecord = Setting::where('slug', 'web_setting')->first();
        $webSetting = $webSettingRecord ? json_decode($webSettingRecord->contents, true) : [];

        return view('backend.resident_history.print', compact('booking', 'residentData', 'webSetting'));
    }

    private function formatResidentBasic($booking)
    {
        $phone = (string)$booking->phone;
        $rawPhone = preg_replace('/[^0-9]/', '', $phone);
        $cleanPhone = strlen($rawPhone) >= 10 ? '0' . substr($rawPhone, -10) : $rawPhone;

        $user = $booking->user ?? User::where('phone', $phone)->orWhere('phone', $cleanPhone)->first();

        // Room/Floor parsing
        $items = [];
        if (is_string($booking->floor_number_room_number_roomprice)) {
            $items = json_decode($booking->floor_number_room_number_roomprice, true) ?? [];
        } elseif (is_array($booking->floor_number_room_number_roomprice)) {
            $items = $booking->floor_number_room_number_roomprice;
        }

        $floor = collect($items)->pluck('floornumber')->filter()->unique()->implode(', ');
        if (!$floor) {
            $floor = $booking->floornumber ?? ($booking->floor->name ?? 'N/A');
        }

        $room = collect($items)->map(function ($i) {
            return \App\Models\Backend\RoomBookingHistory::parseRoomSeatLabel($i['roomnumber'] ?? '', $i['room_type'] ?? null, $i['seat_id'] ?? null);
        })->filter()->unique()->implode(', ');
        if (!$room) {
            $room = $booking->room_number ?? ($booking->room->room_number ?? 'N/A');
        }

        $seat = collect($items)->map(function ($i) {
            $lbl = \App\Models\Backend\RoomBookingHistory::parseRoomSeatLabel($i['roomnumber'] ?? '', $i['room_type'] ?? null, $i['seat_id'] ?? null);
            return (str_contains($lbl, '-') && preg_match('/-\d+$/', $lbl)) ? $lbl : '-';
        })->filter(fn($s) => $s !== '-')->implode(', ') ?: '-';

        $isDeleted = !empty($booking->deleted_at);
        $isReleased = !$isDeleted && !empty($booking->today_check_out);
        $joiningDate = $booking->check_in ? Carbon::parse($booking->check_in)->format('d M Y') : ($booking->created_at ? $booking->created_at->format('d M Y') : '-');
        $releaseDate = $booking->today_check_out ? Carbon::parse($booking->today_check_out)->format('d M Y') : null;
        $deletedDate = $booking->deleted_at ? Carbon::parse($booking->deleted_at)->format('d M Y') : null;

        $plainPassword = ($user && !empty($user->temp_password)) ? $user->temp_password : ($booking->password ?: '-');

        // Image
        $image = $booking->image;
        if (empty($image) && $user && !empty($user->user_image)) {
            $image = $user->user_image;
        }

        $isActive = !$isDeleted && !$isReleased && ((int)$booking->status === 1);
        $isPending = !$isDeleted && !$isReleased && ((int)$booking->status === 0 || is_null($booking->status));

        $statusLabel = 'Pending / Deactive';
        $statusClass = 'bg-warning text-dark';
        if ($isDeleted) {
            $statusLabel = 'Deleted / Inactive';
            $statusClass = 'bg-danger';
        } elseif ($isReleased) {
            $statusLabel = 'Released';
            $statusClass = 'bg-secondary';
        } elseif ($isActive) {
            $statusLabel = 'Active';
            $statusClass = 'bg-success';
        }

        return (object)[
            'id'             => $booking->id,
            'user_id'        => $user->id ?? null,
            'name'           => $booking->full_name ?: ($user->name ?? 'N/A'),
            'phone'          => $phone,
            'clean_phone'    => $cleanPhone,
            'email'          => $booking->email ?: ($user->email ?? '-'),
            'image'          => $image,
            'user_type'      => ucfirst(str_replace('_', ' ', $booking->user_type ?? 'Student')),
            'floor'          => $floor,
            'room'           => $room,
            'seat'           => $seat,
            'room_seat_full' => "রুম {$room}, সিট {$seat} ({$floor})",
            'monthly_amount' => (float)($booking->monthly_amount ?? 0),
            'joining_date'   => $joiningDate,
            'release_date'   => $releaseDate,
            'deleted_date'   => $deletedDate,
            'is_released'    => $isReleased,
            'is_deleted'     => $isDeleted,
            'is_active'      => $isActive,
            'is_pending'     => $isPending,
            'password'       => $plainPassword,
            'status_label'   => $statusLabel,
            'status_class'   => $statusClass,
        ];
    }

    private function getFullResidentProfile($booking, $selectedYear = 2026, $fromMonth = 1, $toMonth = 12)
    {
        $basic = $this->formatResidentBasic($booking);
        $phone = $basic->phone;
        $cleanPhone = $basic->clean_phone;
        $user = $booking->user ?? User::where('phone', $phone)->orWhere('phone', $cleanPhone)->first();
        $userId = $user->id ?? null;

        // Meal setting & rate
        $mealSetting = SettingService::getSettingContentBySlug('meal_setting');
        $mealRate = (float)($mealSetting['full'] ?? 65);

        // 1. Room Change History
        $roomChanges = RoomChangeHistory::where('room_booking_history_id', $booking->id)
            ->orWhere('phone', $phone)
            ->orWhere('phone', $cleanPhone)
            ->orderByDesc('id')
            ->get();

        // 2. Monthly Payments Ledger (All records)
        $allMonthlyPayments = MonthlyPayment::where('room_booking_history_id', $booking->id)
            ->orderBy('id', 'asc')
            ->get();

        // 3. Meal Consumption History
        $mealsQuery = Meal::query();
        if ($userId) {
            $mealsQuery->where('user_id', $userId);
        } else {
            $mealsQuery->where('id', 0);
        }
        $allMeals = $mealsQuery->orderBy('date', 'desc')->get();

        // 4. Meal deposits
        $mealDepositsQuery = Deposit::query();
        if ($userId) {
            $mealDepositsQuery->where('user_id', $userId);
        } else {
            $mealDepositsQuery->where('id', 0);
        }
        $mealDeposits = $mealDepositsQuery->get();

        // 4.1 Guest Meals
        $guestMealsQuery = GuestMeal::query();
        if ($userId) {
            $guestMealsQuery->where('user_id', $userId)->where('status', 1);
        } else {
            $guestMealsQuery->where('id', 0);
        }
        $allGuestMeals = $guestMealsQuery->orderBy('date', 'desc')->get();

        // 5. Build Comprehensive 12-Month Ledger for the selected Year
        $monthlyCostLedger = [];
        $monthsNameMap = [
            1  => 'January',
            2  => 'February',
            3  => 'March',
            4  => 'April',
            5  => 'May',
            6  => 'June',
            7  => 'July',
            8  => 'August',
            9  => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ];

        $checkInDate = $booking->check_in ? Carbon::parse($booking->check_in)->startOfDay() : ($booking->created_at ? Carbon::parse($booking->created_at)->startOfDay() : null);
        $checkOutDate = $booking->today_check_out ? Carbon::parse($booking->today_check_out)->endOfDay() : null;

        $totalYearRentBilled    = 0;
        $totalYearElectricity   = 0;
        $totalYearMealsCount    = 0;
        $totalYearMealCharge    = 0;
        $totalYearRoomChangeFee = 0;
        $totalYearCombinedCost  = 0;
        $totalYearPaid          = 0;
        $totalYearDue           = 0;

        for ($m = 1; $m <= 12; $m++) {
            $mPadded = str_pad($m, 2, '0', STR_PAD_LEFT);
            $mName = $monthsNameMap[$m];
            $mLabel = "{$mName} {$selectedYear}";
            $shortMonthKey = "{$selectedYear}-{$mPadded}";

            $monthStart = Carbon::createFromDate($selectedYear, $m, 1)->startOfMonth();
            $monthEnd   = Carbon::createFromDate($selectedYear, $m, 1)->endOfMonth();

            $isActiveInMonth = true;
            if ($checkInDate && $monthEnd->lt($checkInDate->copy()->startOfMonth())) {
                $isActiveInMonth = false;
            }
            if ($checkOutDate && $monthStart->gt($checkOutDate->copy()->endOfMonth())) {
                $isActiveInMonth = false;
            }

            // Find matching monthly payment record
            $mp = $allMonthlyPayments->first(function($item) use ($mName, $selectedYear, $shortMonthKey) {
                $payMonth = strtolower((string)$item->payment_month);
                return str_contains($payMonth, strtolower($mName)) || str_contains($payMonth, $shortMonthKey);
            });

            // Meals in this month
            $mealsInMonth = $allMeals->filter(function($meal) use ($selectedYear, $m) {
                if (empty($meal->date)) return false;
                $cDate = Carbon::parse($meal->date);
                return (int)$cDate->year === (int)$selectedYear && (int)$cDate->month === (int)$m;
            });

            // Guest meals in this month
            $guestMealsInMonth = $allGuestMeals->filter(function($gm) use ($selectedYear, $m) {
                if (empty($gm->date)) return false;
                $cDate = Carbon::parse($gm->date);
                return (int)$cDate->year === (int)$selectedYear && (int)$cDate->month === (int)$m;
            });

            $mFullMeals   = $mealsInMonth->where('full_meal', true)->count();
            $mOffDays     = $mealsInMonth->where('is_off', true)->count();
            $mGuestCount  = (float)$guestMealsInMonth->sum('meal_count');
            $mGuestCharge = (float)$guestMealsInMonth->sum('amount');
            $mMealCharge  = ($mFullMeals * $mealRate) + $mGuestCharge;
            $mTotalMealsCount = $mFullMeals + $mGuestCount;

            $regularMealDetails = $mealsInMonth->map(function($meal) use ($mealRate) {
                return [
                    'id'         => $meal->id,
                    'date'       => $meal->date ? Carbon::parse($meal->date)->format('d M Y') : '-',
                    'date_raw'   => $meal->date ? Carbon::parse($meal->date)->format('Y-m-d') : '',
                    'type'       => 'regular',
                    'full_meal'  => (bool)$meal->full_meal,
                    'is_off'     => (bool)$meal->is_off,
                    'status_txt' => $meal->full_meal ? 'Full Meal' : ($meal->is_off ? 'Off' : 'No Meal'),
                    'rate'       => $mealRate,
                    'amount'     => $meal->full_meal ? $mealRate : 0,
                    'note'       => $meal->note ?: '-',
                ];
            });

            $guestMealDetails = $guestMealsInMonth->map(function($gm) {
                $countFmt = (float)$gm->meal_count == (int)$gm->meal_count ? (int)$gm->meal_count : number_format($gm->meal_count, 1);
                return [
                    'id'         => 'gm-' . $gm->id,
                    'date'       => $gm->date ? Carbon::parse($gm->date)->format('d M Y') : '-',
                    'date_raw'   => $gm->date ? Carbon::parse($gm->date)->format('Y-m-d') : '',
                    'type'       => 'guest',
                    'full_meal'  => true,
                    'is_off'     => false,
                    'status_txt' => 'Guest Meal',
                    'rate'       => (float)$gm->amount,
                    'amount'     => (float)$gm->amount,
                    'note'       => "👥 মেহমান: {$gm->guest_name} ({$countFmt} টি মিল)" . ($gm->note ? " - {$gm->note}" : ''),
                ];
            });

            $mMealDetails = $regularMealDetails->concat($guestMealDetails)->sortByDesc('date_raw')->values();

            // Room changes in this month
            $roomChangesInMonth = $roomChanges->filter(function($rc) use ($selectedYear, $m) {
                if (empty($rc->created_at)) return false;
                $cDate = Carbon::parse($rc->created_at);
                return (int)$cDate->year === (int)$selectedYear && (int)$cDate->month === (int)$m;
            });

            $mRoomChangeCount = $roomChangesInMonth->count();
            $mRoomChangeFee   = (float)$roomChangesInMonth->sum('fee_amount');

            $mRoomChangeDetails = $roomChangesInMonth->map(function($rc) {
                return [
                    'id'       => $rc->id,
                    'date'     => $rc->created_at ? $rc->created_at->format('d M Y') : '-',
                    'old_room' => "{$rc->old_floor} - {$rc->old_room_seat}",
                    'new_room' => "{$rc->new_floor} - {$rc->new_room_seat}",
                    'fee'      => (float)$rc->fee_amount,
                    'status'   => $rc->payment_status ?: 'paid',
                    'remarks'  => $rc->remarks ?: '',
                ];
            })->values();

            // Room Rent & Electricity: Only show billed/payment amount if payment record exists for this month
            $mRent = $mp ? (float)$mp->amount : 0.00;
            $mElectricity = $mp ? (float)$mp->electricity_bill : 0.00;
            $mPrevDue = $mp ? (float)$mp->carried_forward_due : 0.00;
            
            // Total Combined Cost for this month
            $mTotalCost = $mRent + $mElectricity + $mMealCharge + $mRoomChangeFee + $mPrevDue;

            // Payments in this month
            $mRentPaid = $mp ? (float)$mp->paid_amount : 0.00;
            $mMealDepositInMonth = $mealDeposits->filter(function($dep) use ($selectedYear, $m) {
                if (empty($dep->created_at)) return false;
                $cDate = Carbon::parse($dep->created_at);
                return (int)$cDate->year === (int)$selectedYear && (int)$cDate->month === (int)$m;
            })->sum('amount');

            $mRoomChangePaid = $roomChangesInMonth->filter(function($rc) {
                return strtolower((string)$rc->payment_status) === 'paid';
            })->sum('fee_amount');

            $mTotalPaid = $mRentPaid + $mMealDepositInMonth + $mRoomChangePaid;
            $mDue = max(0, $mTotalCost - $mTotalPaid);

            $hasActivity = ($mp !== null) || ($mTotalMealsCount > 0) || ($mOffDays > 0) || ($mRoomChangeCount > 0) || ($mTotalCost > 0);

            // Accumulate Year Totals
            if ($hasActivity) {
                $totalYearRentBilled    += $mRent;
                $totalYearElectricity   += $mElectricity;
                $totalYearMealsCount    += $mTotalMealsCount;
                $totalYearMealCharge    += $mMealCharge;
                $totalYearRoomChangeFee += $mRoomChangeFee;
                $totalYearCombinedCost  += $mTotalCost;
                $totalYearPaid          += $mTotalPaid;
                $totalYearDue           += $mDue;
            }

            $currentYearNow = (int)date('Y');
            $currentMonthNow = (int)date('n');
            $isCurrentRunningMonth = ($selectedYear === $currentYearNow && (int)$m === $currentMonthNow);
            $isSelectedSingleMonth = ($fromMonth === $toMonth && (int)$m === (int)$fromMonth);

            $monthlyCostLedger[] = [
                'month_num'           => $mPadded,
                'month_name'          => $mName,
                'month_year_label'    => $mLabel,
                'room_rent'           => $mRent,
                'electricity_bill'    => $mElectricity,
                'meal_count'          => $mTotalMealsCount,
                'regular_meal_count'  => $mFullMeals,
                'guest_meal_count'    => $mGuestCount,
                'guest_meal_charge'   => $mGuestCharge,
                'meal_off_days'       => $mOffDays,
                'meal_rate'           => $mealRate,
                'meal_charge'         => $mMealCharge,
                'room_change_count'   => $mRoomChangeCount,
                'room_change_fee'     => $mRoomChangeFee,
                'room_change_details' => $mRoomChangeDetails,
                'meal_details'        => $mMealDetails,
                'prev_due'            => $mPrevDue,
                'total_cost'          => $mTotalCost,
                'total_paid'          => $mTotalPaid,
                'total_due'           => $mDue,
                'payment_method'      => $mp ? ($mp->payment_method ?: 'Cash') : '-',
                'trx_id'              => $mp ? ($mp->trx_id ?: '') : '',
                'has_activity'        => $hasActivity,
                'is_active_stay'      => $isActiveInMonth,
                'is_current_month'    => $isCurrentRunningMonth,
                'is_selected_month'   => $isSelectedSingleMonth,
                'status'              => $mDue <= 0 ? 'Paid' : ($mTotalPaid > 0 ? 'Partial' : 'Due'),
                'due_breakdown'       => [
                    'room_rent'         => $mRent,
                    'electricity_bill'  => $mElectricity,
                    'meal_count'        => $mFullMeals,
                    'meal_charge'       => $mMealCharge,
                    'meal_rate'         => $mealRate,
                    'room_change_count' => $mRoomChangeCount,
                    'room_change_fee'   => $mRoomChangeFee,
                    'prev_due'          => $mPrevDue,
                    'total_cost'        => $mTotalCost,
                    'total_paid'        => $mTotalPaid,
                    'net_due'           => $mDue,
                ],
            ];
        }

        // Filter month range
        $filteredLedger = array_values(array_filter($monthlyCostLedger, function($row) use ($fromMonth, $toMonth) {
            $mNum = (int)$row['month_num'];
            return $mNum >= $fromMonth && $mNum <= $toMonth;
        }));

        // Range Totals
        $rangeRentBilled    = (float)collect($filteredLedger)->sum('room_rent');
        $rangeElectricity   = (float)collect($filteredLedger)->sum('electricity_bill');
        $rangeMealsCount    = (int)collect($filteredLedger)->sum('meal_count');
        $rangeMealCharge    = (float)collect($filteredLedger)->sum('meal_charge');
        $rangeRoomChangeFee = (float)collect($filteredLedger)->sum('room_change_fee');
        $rangePrevDue       = (float)collect($filteredLedger)->sum('prev_due');
        $rangeCombinedCost  = (float)collect($filteredLedger)->sum('total_cost');

        $rangeLabel = ($fromMonth === $toMonth) 
            ? "{$monthsNameMap[$fromMonth]} {$selectedYear}"
            : "{$monthsNameMap[$fromMonth]} - {$monthsNameMap[$toMonth]} {$selectedYear}";

        // Lifetime summary totals
        $advancePaid = (float)($booking->advance_fee ?? 0);
        if ($advancePaid <= 0) {
            $items = is_string($booking->floor_number_room_number_roomprice)
                ? json_decode($booking->floor_number_room_number_roomprice, true)
                : $booking->floor_number_room_number_roomprice;
            if (is_array($items)) {
                foreach ($items as $it) {
                    $advancePaid += (float)($it['advance_price'] ?? 0);
                }
            }
        }
        $devFeePaid = (float)($booking->development_fee ?? 0);

        $specialMeals = \App\Models\Backend\SpecialMeal::where('status', 1)->get()->keyBy(function($item) {
            return Carbon::parse($item->date)->format('Y-m-d');
        });

        $activeMeals = $allMeals->filter(function($m) {
            return (bool)$m->full_meal && !(bool)$m->is_off;
        });

        $totalAllTimeRegularCount = $activeMeals->count();
        $totalAllTimeGuestCount   = (float)$allGuestMeals->sum('meal_count');
        $totalAllTimeMeals        = $totalAllTimeRegularCount + $totalAllTimeGuestCount;
        $totalAllTimeOff          = $allMeals->where('is_off', true)->count();
        $totalAllTimeMealDeposit  = (float)$mealDeposits->sum('amount');

        $totalAllTimeRegularMealCost = 0;
        foreach ($activeMeals as $m) {
            $mDate = $m->date ? Carbon::parse($m->date)->format('Y-m-d') : null;
            $spec = $mDate ? $specialMeals->get($mDate) : null;
            $fRate = $spec ? (float)$spec->full_rate : $mealRate;
            $totalAllTimeRegularMealCost += $fRate;
        }

        $totalAllTimeGuestMealCost = (float)$allGuestMeals->sum('amount');
        $totalAllTimeMealCost = $totalAllTimeRegularMealCost + $totalAllTimeGuestMealCost;

        $mealBalance = $totalAllTimeMealDeposit - $totalAllTimeMealCost;
        $grandTotalDeposited = $advancePaid + $devFeePaid + $mealBalance;

        return [
            'basic' => $basic,
            'personal' => [
                'full_name'        => $booking->full_name,
                'phone'            => $booking->phone,
                'email'            => $booking->email ?: ($user->email ?? '-'),
                'blood_group'      => $booking->blood_group ?: '-',
                'user_type'        => ucfirst(str_replace('_', ' ', $booking->user_type ?? 'Student')),
                'institution_name' => $booking->institution_name ?: '-',
                'education_level'  => $booking->education_level ?: '-',
                'education_class'  => $booking->education_class ?: '-',
                'workplace_name'   => $booking->workplace_name ?: '-',
                'nid'              => $booking->nid ?: '-',
                'father_name'      => $booking->father_name ?: '-',
                'father_phone'     => $booking->father_phone ?: '-',
                'father_nid'       => $booking->father_nid ?: '-',
                'mother_name'      => $booking->mother_name ?: '-',
                'mother_phone'     => $booking->mother_phone ?: '-',
                'mother_nid'       => $booking->mother_nid ?: '-',
                'address'          => $booking->address ?: '-',
                'division'         => $booking->division->name ?? '-',
                'district'         => $booking->district->name ?? '-',
                'thana'            => $booking->thana->name ?? '-',
                'admission_date'   => $booking->check_in ? Carbon::parse($booking->check_in)->format('d M Y') : '-',
                'release_date'     => $booking->today_check_out ? Carbon::parse($booking->today_check_out)->format('d M Y') : null,
                'deleted_date'     => $booking->deleted_at ? Carbon::parse($booking->deleted_at)->format('d M Y') : null,
                'is_deleted'       => !empty($booking->deleted_at),
                'created_at'       => $booking->created_at ? $booking->created_at->format('d M Y, h:i A') : '-',
                'password'         => $basic->password,
            ],
            'selected_year'      => $selectedYear,
            'from_month'         => $fromMonth,
            'to_month'           => $toMonth,
            'range_label'        => $rangeLabel,
            'room_movements'     => $roomChanges,
            'monthly_payments'   => $allMonthlyPayments,
            'monthly_cost_ledger'=> $filteredLedger,
            'range_totals' => [
                'rent_billed'     => $rangeRentBilled,
                'electricity'     => $rangeElectricity,
                'meals_count'     => $rangeMealsCount,
                'meal_charge'     => $rangeMealCharge,
                'room_change_fee' => $rangeRoomChangeFee,
                'prev_due'        => $rangePrevDue,
                'combined_cost'   => $rangeCombinedCost,
            ],
            'year_totals' => [
                'rent_billed'     => $totalYearRentBilled,
                'electricity'     => $totalYearElectricity,
                'meals_count'     => $totalYearMealsCount,
                'meal_charge'     => $totalYearMealCharge,
                'room_change_fee' => $totalYearRoomChangeFee,
                'combined_cost'   => $totalYearCombinedCost,
                'total_paid'      => $totalYearPaid,
                'total_due'       => $totalYearDue,
            ],
            'payment_summary' => [
                'total_billed'        => $allMonthlyPayments->sum('amount'),
                'total_paid'          => $allMonthlyPayments->sum('paid_amount'),
                'total_due'           => $allMonthlyPayments->sum('due_amount'),
                'advance_paid'        => $advancePaid,
                'dev_fee_paid'        => $devFeePaid,
                'has_dev_fee'         => ($devFeePaid > 0),
                'grand_total_deposit' => $grandTotalDeposited,
            ],
            'meal_summary' => [
                'total_full_meals'    => $totalAllTimeMeals,
                'regular_meals_count' => $totalAllTimeRegularCount,
                'guest_meals_count'   => $totalAllTimeGuestCount,
                'regular_meal_cost'   => $totalAllTimeRegularMealCost,
                'guest_meal_cost'     => $totalAllTimeGuestMealCost,
                'total_off_days'      => $totalAllTimeOff,
                'total_count'         => $totalAllTimeMeals,
                'meal_rate'           => $mealRate,
                'total_deposit'       => $totalAllTimeMealDeposit,
                'total_cost'          => $totalAllTimeMealCost,
                'balance'             => $mealBalance,
                'recent_meals'        => $allMeals->take(15),
            ]
        ];
    }
}
