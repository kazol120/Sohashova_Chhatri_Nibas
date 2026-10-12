<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Backend\RoomBookingHistory;
use App\Models\Backend\MonthlyPayment;
use Carbon\Carbon;

class MyPaymentController extends Controller
{
    /**
     * Helper to get accurate user bookings by phone (or email fallback)
     */
    private function getUserBookings($user)
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $user->phone ?? '');
        $cleanPhone10 = strlen($cleanPhone) >= 6 ? (strlen($cleanPhone) > 10 ? substr($cleanPhone, -10) : $cleanPhone) : '';

        return RoomBookingHistory::where(function ($q) use ($user, $cleanPhone10) {
            if (!empty($cleanPhone10)) {
                $q->whereRaw("REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+', '') LIKE ?", ["%{$cleanPhone10}%"]);
            } elseif (!empty($user->email)) {
                $q->where('email', $user->email);
            } else {
                $q->whereRaw('1 = 0');
            }
        })->get();
    }

    /**
     * Show logged in user's own payment history (Frontend look)
     */
    public function index()
    {
        $user = Auth::user();

        // Find booking by phone (or email fallback)
        $bookings = $this->getUserBookings($user);

        $bookingIds = $bookings->pluck('id');

        // Get all monthly payments (new to old)
        $payments = MonthlyPayment::whereIn('room_booking_history_id', $bookingIds)
            ->with(['booking'])
            ->orderByDesc('payment_month')
            ->get()
            ->map(function ($row) {
                $booking = $row->booking;
                $items = is_string($booking->floor_number_room_number_roomprice)
                    ? (json_decode($booking->floor_number_room_number_roomprice, true) ?? [])
                    : ($booking->floor_number_room_number_roomprice ?? []);
                $c = collect($items);

                // actual due calculate
                $due    = (float) $row->due_amount;
                $paid   = (float) ($row->paid_amount ?? 0);
                $total  = (float) ($row->amount ?? 0) + (float) ($row->carried_forward_due ?? 0);
                if ($due <= 0 && $paid <= 0) {
                    $due = $total;
                }

                return [
                    'id'             => $row->id,
                    'payment_month'  => $row->payment_month,
                    'amount'         => $row->amount,
                    'paid_amount'    => $row->paid_amount ?? 0,
                    'due_amount'     => $due,
                    'status'         => $row->status,
                    'payment_method' => $row->payment_method,
                    'note'           => $row->note,
                    'received_by'    => $row->received_by,
                    'created_at'     => $row->created_at->format('d M Y'),
                    'roomnumber'     => $c->pluck('roomnumber')->filter()->implode(', '),
                    'floornumber'    => $c->pluck('floornumber')->filter()->unique()->implode(', '),
                    'full_name'      => $booking->full_name ?? '-',
                ];
            });

        // Summary calculations
        $totalBilled  = $payments->sum('amount');
        $totalPaid    = $payments->sum('paid_amount');
        $totalDue     = max(0, $totalBilled - $totalPaid);
        $currentMonth = Carbon::now()->format('Y-m');

        $currentMonthPayment = $payments->firstWhere('payment_month', $currentMonth);

        // Meal System Data
        $selectedMonth = Carbon::now()->format('Y-m');
        $mealService = app(\App\Services\MealService::class);
        $depositService = app(\App\Services\DepositService::class);
        $fineService = app(\App\Services\FineService::class);

        $mealSummary = $mealService->singleUserMealHistory($selectedMonth, $user);
        $mealsList = $mealService->monthlyMealByUser($selectedMonth, $user->id);
        $depositData = $depositService->getUserMonthlyHistory($user->id, $selectedMonth);
        $finesList = $fineService->fineByUser($selectedMonth, $user->id);

        return view('Frontend.page.my-payment', compact(
            'payments', 'totalBilled', 'totalPaid', 'totalDue',
            'currentMonthPayment', 'user'
        ));
    }

    /**
     * Show logged in user's own payment history (Dashboard / Backend layout)
     */
    public function guestIndex()
    {
        $user = Auth::user();

        // Find booking by phone (or email fallback)
        $bookings = $this->getUserBookings($user);

        $bookingIds = $bookings->pluck('id');

        // Get all monthly payments (new to old)
        $payments = MonthlyPayment::whereIn('room_booking_history_id', $bookingIds)
            ->with(['booking'])
            ->orderByDesc('payment_month')
            ->get()
            ->map(function ($row) {
                $booking = $row->booking;
                $items = is_string($booking->floor_number_room_number_roomprice)
                    ? (json_decode($booking->floor_number_room_number_roomprice, true) ?? [])
                    : ($booking->floor_number_room_number_roomprice ?? []);
                $c = collect($items);

                // Combine Rent + Electricity bill for total month bill and due calculation
                $rentAmount = (float) ($row->amount ?? 0);
                $electBill  = (float) ($row->electricity_bill ?? 0);
                $prevDue    = (float) ($row->carried_forward_due ?? 0);
                $paid       = (float) ($row->paid_amount ?? 0);
                $monthTotal = $rentAmount + $electBill + $prevDue;

                $due = (float) $row->due_amount;
                if ($due <= 0 && $paid <= 0) {
                    $due = $monthTotal;
                }

                return [
                    'id'                  => $row->id,
                    'payment_month'       => $row->payment_month,
                    'amount'              => $rentAmount,
                    'electricity_bill'    => $electBill,
                    'total_month_bill'    => $rentAmount + $electBill,
                    'carried_forward_due' => $prevDue,
                    'paid_amount'         => $paid,
                    'due_amount'          => $due,
                    'status'              => $row->status,
                    'payment_method'      => $row->payment_method,
                    'note'                => $row->note,
                    'received_by'         => $row->received_by,
                    'created_at'          => $row->created_at->format('d M Y'),
                    'roomnumber'          => $c->pluck('roomnumber')->filter()->implode(', '),
                    'floornumber'         => $c->pluck('floornumber')->filter()->unique()->implode(', '),
                    'full_name'           => $booking->full_name ?? '-',
                ];
            });

        // Summary calculations (Rent + Electricity Bill combined)
        $totalBilled      = $payments->sum('total_month_bill');
        $totalElectricity = $payments->sum('electricity_bill');
        $totalPaid        = $payments->sum('paid_amount');
        $totalDue         = max(0, $payments->sum('due_amount'));
        $currentMonth     = Carbon::now()->format('Y-m');

        $currentMonthPayment = $payments->firstWhere('payment_month', $currentMonth);

        return view('backend.monthly_payment.guest_payments', compact(
            'payments', 'totalBilled', 'totalElectricity', 'totalPaid', 'totalDue',
            'currentMonthPayment', 'user'
        ));
    }

    /**
     * Show logged in user's own meal history (Dashboard / Backend layout)
     */
    public function guestMeals(Request $request)
    {
        $user = Auth::user();
        $selectedMonth = $request->get('month', Carbon::now()->format('Y-m'));

        $mealService = app(\App\Services\MealService::class);
        $depositService = app(\App\Services\DepositService::class);
        $fineService = app(\App\Services\FineService::class);

        $startDate = Carbon::parse($selectedMonth . '-01')->startOfMonth();
        $endDate   = Carbon::parse($selectedMonth . '-01')->endOfMonth();

        $mealService->ensureAutoMealGeneratedForUsers([$user], min($endDate->toDateString(), now()->format('Y-m-d')));

        $mealSetting = app(\App\Services\SettingService::class)->getSettingContentBySlug('meal_setting');
        $fullRate = (float)($mealSetting['full'] ?? 30);
        $halfRate = (float)($mealSetting['half'] ?? ($fullRate / 2));

        $specialMeals = \App\Models\Backend\SpecialMeal::whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('status', 1)
            ->get()
            ->keyBy(function($item) {
                return Carbon::parse($item->date)->format('Y-m-d');
            });

        $meals = \App\Models\Backend\Meal::where('user_id', $user->id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy('date');

        $depositTotal = (float) \App\Models\Backend\Deposit::where('user_id', $user->id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->sum('amount');

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
                        $dayNote = $spec->title ? "⭐ {$spec->title} (৳ {$dailyRate}) Special Meal" : "⭐ Special Meal (৳ {$dailyRate})";
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
                'note'           => $dayNote,
                'is_meal_on'     => $isMealOn,
                'day_cost'       => $dayCost,
            ];

            $current->addDay();
        }

        $guestMeals = \App\Models\Backend\GuestMeal::where('user_id', $user->id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('status', 1)
            ->orderBy('date', 'asc')
            ->get();

        $totalGuestMealsCount = (float) $guestMeals->sum('meal_count');
        $totalGuestMealsAmount = (float) $guestMeals->sum('amount');

        $totalCostWithGuest = $totalCost + $totalGuestMealsAmount;
        $balance = $depositTotal - $totalCostWithGuest;

        $mealSummary = (object) [
            'total_meal'            => $totalFullCount + ($totalHalfCount * 0.5) + $totalGuestMealsCount,
            'total_full_count'      => $totalFullCount,
            'total_half_count'      => $totalHalfCount,
            'meal_cost'             => $totalCostWithGuest,
            'guest_meals'           => $guestMeals,
            'total_guest_count'     => $totalGuestMealsCount,
            'total_guest_amount'    => $totalGuestMealsAmount,
            'deposit_total'         => $depositTotal,
            'balance'               => $balance,
        ];

        $depositData = $depositService->getUserMonthlyHistory($user->id, $selectedMonth);
        $finesList = $fineService->fineByUser($selectedMonth, $user->id);

        return view('backend.monthly_payment.guest_meals', compact(
            'user', 'mealSummary', 'depositData', 'finesList', 'selectedMonth', 'days', 'fullRate', 'halfRate', 'totalActiveDays', 'totalOffDays', 'totalCost', 'depositTotal', 'balance', 'guestMeals', 'totalGuestMealsCount', 'totalGuestMealsAmount', 'totalFullCount', 'totalHalfCount'
        ));
    }

    /**
     * Show logged in user's own electricity bills (Dashboard / Backend layout)
     */
    public function guestElectricityBills(Request $request)
    {
        $user = Auth::user();

        // Find booking by phone (or email fallback)
        $bookings = $this->getUserBookings($user);
        $userBooking = $bookings->first();
        $bookingIds = $bookings->pluck('id');

        $selectedYear = (int)$request->input('year', date('Y'));
        if ($selectedYear < 2026) $selectedYear = 2026;

        $fromMonth = 1;
        $toMonth   = 12;

        if ($request->has('from_month') || $request->has('to_month')) {
            $fromMonth = (int)$request->input('from_month', 1);
            $toMonth   = (int)$request->input('to_month', 12);
        } elseif ($request->has('month')) {
            $monthInput = $request->input('month');
            if ($monthInput !== 'all' && is_numeric($monthInput)) {
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

        // Fetch monthly payments for this resident in the selected year
        $payments = MonthlyPayment::whereIn('room_booking_history_id', $bookingIds)
            ->where('payment_month', 'like', "{$selectedYear}-%")
            ->get()
            ->keyBy('payment_month');

        $currentMonthStr = Carbon::now()->format('Y-m');
        $monthlyLedger = [];
        $totalElectricity = 0;
        $totalPaidElectricity = 0;
        $activeBillCount = 0;

        for ($m = $fromMonth; $m <= $toMonth; $m++) {
            $mPad = str_pad($m, 2, '0', STR_PAD_LEFT);
            $ym = "{$selectedYear}-{$mPad}";
            $payment = $payments->get($ym);

            $carbonDate = Carbon::parse("{$selectedYear}-{$mPad}-01");
            $electBill = $payment ? (float)($payment->electricity_bill ?? 0) : 0;
            $status = $payment ? $payment->status : ($carbonDate->isFuture() ? 'upcoming' : 'no_record');
            $paymentDate = $payment && $payment->created_at ? $payment->created_at->format('d M Y') : '—';
            $isCurrentMonth = ($ym === $currentMonthStr);

            if ($electBill > 0) {
                $activeBillCount++;
                if ($status === 'paid') {
                    $totalPaidElectricity += $electBill;
                }
            }
            $totalElectricity += $electBill;

            $monthlyLedger[] = [
                'month_num'          => $mPad,
                'month_name'         => $carbonDate->format('F'),
                'month_year_label'   => $carbonDate->format('F Y'),
                'electricity_bill'   => $electBill,
                'payment_date'       => $paymentDate,
                'status'             => $status,
                'is_current_month'   => $isCurrentMonth,
                'has_bill'           => ($electBill > 0),
            ];
        }

        $allTimeElectricity = (float) MonthlyPayment::whereIn('room_booking_history_id', $bookingIds)->sum('electricity_bill');
        $currentMonthPayment = $payments->get($currentMonthStr);
        $currentMonthBill = $currentMonthPayment ? (float)($currentMonthPayment->electricity_bill ?? 0) : 0;

        $rangeLabel = ($fromMonth === $toMonth) 
            ? Carbon::parse("{$selectedYear}-" . str_pad($fromMonth, 2, '0', STR_PAD_LEFT) . "-01")->format('F Y')
            : "Jan - Dec {$selectedYear}";

        $yearsList = range(2026, max((int)date('Y'), 2026));

        return view('backend.monthly_payment.guest_electricity_bills', compact(
            'user', 'userBooking', 'monthlyLedger', 'totalElectricity', 'totalPaidElectricity', 'activeBillCount',
            'allTimeElectricity', 'currentMonthBill', 'selectedYear', 'fromMonth', 'toMonth', 'rangeLabel', 'yearsList'
        ));
    }
}
