<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Backend\Expense;
use App\Models\Backend\StaffSalaryPayment;
use App\Models\Backend\RoomBookingHistory;
use App\Models\Backend\ProductDistribution;
use App\Models\Backend\ProductPurchase;
use App\Models\Backend\MonthlyPayment;
use App\Models\Backend\MonthlyPaymentTransaction;
use App\Models\Backend\RoomChangeHistory;
use App\Models\Backend\Deposit;
use App\Models\Backend\GuestMeal;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{

    public function index()
    {
        return view('backend.report.report');
    }

    public function profitLossReport(Request $request)
    {
        $mode = $request->mode ?? 'monthly';
        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec',
        ];

        $rows = [];

        $calcBookingFees = function ($bookings) {
            $totalAdvance = 0;
            $totalDevFee = 0;

            foreach ($bookings as $row) {
                $items = is_string($row->floor_number_room_number_roomprice)
                    ? (json_decode($row->floor_number_room_number_roomprice, true) ?? [])
                    : ($row->floor_number_room_number_roomprice ?? []);

                $noticeDate = $row->notice_date ? \Carbon\Carbon::parse($row->notice_date) : null;
                $checkoutDate = $row->today_check_out ? \Carbon\Carbon::parse($row->today_check_out) : ($row->check_out ? \Carbon\Carbon::parse($row->check_out) : now());
                $noticeDaysElapsed = $noticeDate ? (int) $noticeDate->diffInDays($checkoutDate) : 0;
                $isNoticeFulfilled = $row->will_leave == 1 && $noticeDaysElapsed >= 60;

                $adv = collect($items)->sum(function ($i) use ($row, $isNoticeFulfilled) {
                    if ($row->status == 1 && !$isNoticeFulfilled) {
                        return (float)($i['advance_price'] ?? 0);
                    }
                    return (float)($i['original_advance_price'] ?? $i['advance_price'] ?? 0);
                });

                if ($adv <= 0 && isset($row->advance_fee) && is_numeric($row->advance_fee)) {
                    $adv = (float) $row->advance_fee;
                }

                $dev = (float) ($row->development_fee ?? 0);

                $totalAdvance += $adv;
                $totalDevFee += $dev;
            }

            return [
                'advance'         => $totalAdvance,
                'development_fee' => $totalDevFee,
                'total'           => $totalAdvance + $totalDevFee,
            ];
        };

        $calcDailyMetrics = function ($dayStr) use ($calcBookingFees) {
            // Prioritize check_in date (fallback to created_at if check_in is null) so each booking is assigned strictly to its check-in day without overlap
            $bookingsOnDay = RoomBookingHistory::where(function($q) use ($dayStr) {
                $q->whereDate('check_in', $dayStr)
                  ->orWhere(function($sub) use ($dayStr) {
                      $sub->whereNull('check_in')->whereDate('created_at', $dayStr);
                  });
            })->get();

            $bookingFees = $calcBookingFees($bookingsOnDay);
            $roomBooking = $bookingFees['advance'];
            $devFee = $bookingFees['development_fee'];

            $txItems = MonthlyPaymentTransaction::whereDate('payment_date', $dayStr)->get();
            if ($txItems->count() > 0) {
                $monthlyPayment  = max(0, (float) $txItems->sum(function($t) { return (float)$t->total_collected - (float)($t->electricity_bill_paid ?? 0); }));
                $electricityBill = (float) $txItems->sum(function($t) { return (float)($t->electricity_bill_paid ?? 0); });
            } else {
                $mpItems = MonthlyPayment::whereDate('created_at', $dayStr)->get();
                $monthlyPayment  = max(0, (float) $mpItems->sum(function($m) { return (float)$m->paid_amount - (float)($m->electricity_bill ?? 0); }));
                $electricityBill = (float) MonthlyPayment::whereDate('created_at', $dayStr)->where('electricity_bill', '>', 0)->sum('electricity_bill');
            }

            $productSales   = 0;
            $roomChangeFee  = (float) RoomChangeHistory::whereDate('created_at', $dayStr)->sum('fee_amount');

            $totalIncome = $roomBooking + $devFee + $monthlyPayment + $electricityBill + $roomChangeFee;

            // ====== COST ======
            $expense         = (float) Expense::where(function($q) use ($dayStr) {
                $q->whereDate('date', $dayStr)
                  ->orWhere(function($sub) use ($dayStr) {
                      $sub->whereNull('date')->whereDate('created_at', $dayStr);
                  });
            })->sum('expense_amount');
            $salary          = (float) StaffSalaryPayment::where(function($q) use ($dayStr) {
                $q->whereDate('payment_date', $dayStr)
                  ->orWhere(function($sub) use ($dayStr) {
                      $sub->whereNull('payment_date')->whereDate('created_at', $dayStr);
                  });
            })->sum('amount');
            $productPurchase = 0;

            $refundedBookings = RoomBookingHistory::where('status', 1)
                ->where('will_leave', 1)
                ->whereNotNull('notice_date')
                ->where(function ($query) use ($dayStr) {
                    $query->whereDate('today_check_out', $dayStr)
                        ->orWhere(function ($q) use ($dayStr) {
                            $q->whereNull('today_check_out')
                                ->whereDate('check_out', $dayStr);
                        });
                })->get()->filter(function ($row) {
                    $noticeDate = \Carbon\Carbon::parse($row->notice_date);
                    $checkoutDate = $row->today_check_out ? \Carbon\Carbon::parse($row->today_check_out) : ($row->check_out ? \Carbon\Carbon::parse($row->check_out) : now());
                    return (int) $noticeDate->diffInDays($checkoutDate) >= 60;
                });

            $advanceRefund = (float) $refundedBookings->sum(function ($row) {
                $items = is_string($row->floor_number_room_number_roomprice)
                    ? (json_decode($row->floor_number_room_number_roomprice, true) ?? [])
                    : ($row->floor_number_room_number_roomprice ?? []);
                $adv = collect($items)->sum(function ($i) {
                    return (float)($i['original_advance_price'] ?? $i['advance_price'] ?? 0);
                });
                return $adv > 0 ? $adv : ((float)($row->monthly_amount ?? 0) * 2);
            });

            $totalCost = $expense + $salary + $advanceRefund;

            return [
                'room_booking'     => $roomBooking,
                'development_fee'  => $devFee,
                'monthly_payment'  => $monthlyPayment,
                'electricity_bill' => $electricityBill,
                'product_sales'    => $productSales,
                'room_change_fee'  => $roomChangeFee,
                'total_income'     => $totalIncome,
                'expense'          => $expense,
                'salary'           => $salary,
                'product_purchase' => $productPurchase,
                'advance_refund'   => $advanceRefund,
                'total_cost'       => $totalCost,
                'profit_loss'      => $totalIncome - $totalCost,
            ];
        };

        if ($mode === 'daily') {
            $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth()->startOfDay();
            $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfMonth()->endOfDay();

            if ($startDate->gt($endDate)) {
                $temp = $startDate;
                $startDate = $endDate;
                $endDate = $temp;
            }

            $period = \Carbon\CarbonPeriod::create($startDate, $endDate);

            foreach ($period as $date) {
                $dayStr = $date->format('Y-m-d');
                $dayDisplay = $date->format('d M Y');
                $metrics = $calcDailyMetrics($dayStr);
                $metrics['date']  = $dayStr;
                $metrics['label'] = $dayDisplay;
                $rows[] = $metrics;
            }
        } elseif ($mode === 'monthly') {
            $year = (int) ($request->year ?? date('Y'));

            foreach ($months as $num => $name) {
                $mStart = Carbon::create($year, $num, 1)->startOfDay();
                $mEnd = Carbon::create($year, $num, 1)->endOfMonth()->endOfDay();
                $mPeriod = \Carbon\CarbonPeriod::create($mStart, $mEnd);

                $mAcc = [
                    'room_booking' => 0, 'development_fee' => 0, 'monthly_payment' => 0,
                    'electricity_bill' => 0, 'product_sales' => 0, 'room_change_fee' => 0,
                    'total_income' => 0, 'expense' => 0, 'salary' => 0, 'product_purchase' => 0,
                    'advance_refund' => 0, 'total_cost' => 0, 'profit_loss' => 0
                ];

                foreach ($mPeriod as $date) {
                    $d = $calcDailyMetrics($date->format('Y-m-d'));
                    foreach ($d as $k => $v) {
                        if (isset($mAcc[$k]) && $k !== 'electricity_bill' && $k !== 'monthly_payment') {
                            $mAcc[$k] += $v;
                        }
                    }
                }

                $targetMonthStr = sprintf('%04d-%02d', $year, $num);

                // Monthly Rent Payment & Electricity Bill directly from collections in that month
                $monthTxs = MonthlyPaymentTransaction::whereYear('payment_date', $year)
                    ->whereMonth('payment_date', $num)
                    ->get();
                if ($monthTxs->count() > 0) {
                    $mAcc['monthly_payment']  = max(0, (float) $monthTxs->sum(function($t) { return (float)$t->total_collected - (float)($t->electricity_bill_paid ?? 0); }));
                    $mAcc['electricity_bill'] = (float) $monthTxs->sum(function($t) { return (float)($t->electricity_bill_paid ?? 0); });
                } else {
                    $mAcc['monthly_payment']  = max(0, (float) MonthlyPayment::where('payment_month', $targetMonthStr)->get()->sum(function($m) { return (float)$m->paid_amount - (float)($m->electricity_bill ?? 0); }));
                    $mAcc['electricity_bill'] = (float) MonthlyPayment::where('payment_month', $targetMonthStr)->sum('electricity_bill');
                }

                $mAcc['total_income'] = $mAcc['room_booking'] + $mAcc['development_fee'] + $mAcc['monthly_payment'] + $mAcc['electricity_bill'] + $mAcc['room_change_fee'];
                $mAcc['profit_loss']  = $mAcc['total_income'] - $mAcc['total_cost'];

                $mAcc['label'] = $name . ' ' . $year;
                $rows[] = $mAcc;
            }
        } else {
            $filterYear = $request->year;
            if ($filterYear && $filterYear !== 'all') {
                $yearsToIterate = [(int) $filterYear];
            } else {
                $currentYear = max(2026, (int) date('Y'));
                $oldestYear = max(2026, min(
                    (int) (RoomBookingHistory::whereYear('check_in', '>=', 2026)->min(DB::raw('YEAR(check_in)')) ?: 2026),
                    (int) (Expense::whereYear('created_at', '>=', 2026)->min(DB::raw('YEAR(created_at)')) ?: 2026),
                    (int) (StaffSalaryPayment::whereYear('created_at', '>=', 2026)->min(DB::raw('YEAR(created_at)')) ?: 2026),
                    (int) (RoomChangeHistory::whereYear('created_at', '>=', 2026)->min(DB::raw('YEAR(created_at)')) ?: 2026),
                ));
                $yearsToIterate = range($currentYear, $oldestYear);
            }

            foreach ($yearsToIterate as $year) {
                $yAcc = [
                    'room_booking' => 0, 'development_fee' => 0, 'monthly_payment' => 0,
                    'electricity_bill' => 0, 'product_sales' => 0, 'room_change_fee' => 0,
                    'total_income' => 0, 'expense' => 0, 'salary' => 0, 'product_purchase' => 0,
                    'advance_refund' => 0, 'total_cost' => 0, 'profit_loss' => 0
                ];

                $monthlyBreakdown = [];
                foreach ($months as $num => $name) {
                    $mStart = Carbon::create($year, $num, 1)->startOfDay();
                    $mEnd = Carbon::create($year, $num, 1)->endOfMonth()->endOfDay();
                    $mPeriod = \Carbon\CarbonPeriod::create($mStart, $mEnd);

                    $mAcc = [
                        'room_booking' => 0, 'development_fee' => 0, 'monthly_payment' => 0,
                        'electricity_bill' => 0, 'product_sales' => 0, 'room_change_fee' => 0,
                        'total_income' => 0, 'expense' => 0, 'salary' => 0, 'product_purchase' => 0,
                        'advance_refund' => 0, 'total_cost' => 0, 'profit_loss' => 0
                    ];

                    foreach ($mPeriod as $date) {
                        $d = $calcDailyMetrics($date->format('Y-m-d'));
                        foreach ($d as $k => $v) {
                            if (isset($mAcc[$k])) {
                                $mAcc[$k] += $v;
                            }
                        }
                    }

                    foreach ($mAcc as $k => $v) {
                        if (isset($yAcc[$k])) {
                            $yAcc[$k] += $v;
                        }
                    }

                    if (
                        $mAcc['room_booking'] == 0 && $mAcc['development_fee'] == 0 && $mAcc['monthly_payment'] == 0 && $mAcc['electricity_bill'] == 0 && $mAcc['room_change_fee'] == 0
                        && $mAcc['expense'] == 0 && $mAcc['salary'] == 0 && $mAcc['advance_refund'] == 0
                    ) continue;

                    $mAcc['month'] = $name;
                    $monthlyBreakdown[] = $mAcc;
                }

                if (
                    $yAcc['room_booking'] == 0 && $yAcc['development_fee'] == 0 && $yAcc['monthly_payment'] == 0 && $yAcc['electricity_bill'] == 0 && $yAcc['room_change_fee'] == 0
                    && $yAcc['expense'] == 0 && $yAcc['salary'] == 0 && $yAcc['advance_refund'] == 0
                ) continue;

                $yAcc['label'] = (string) $year;
                $yAcc['monthly_breakdown'] = $monthlyBreakdown;
                $rows[] = $yAcc;
            }
        }

        return response()->json([
            'status' => true,
            'mode'   => $mode,
            'data'   => $rows,
        ]);
    }

    public function getproductStock()
    {
        $data = ProductPurchase::selectRaw('
            SUM(available_quantity) as total_qty,
            SUM(total_price_available) as total_amount
        ')->first();

        return response()->json([
            'total_qty'    => $data->total_qty ?? 0,
            'total_amount' => $data->total_amount ?? 0
        ]);
    }

    public function availableYears()
    {
        $currentYear = max(2026, (int) date('Y'));

        $bookingYears = RoomBookingHistory::selectRaw('YEAR(check_in) as year')
            ->whereNotNull('check_in')
            ->groupBy('year')
            ->pluck('year')
            ->map(fn($y) => (int)$y)
            ->filter(fn($y) => $y >= 2026)
            ->toArray();

        $expenseYears = Expense::selectRaw('YEAR(COALESCE(date, created_at)) as year')
            ->groupBy('year')
            ->pluck('year')
            ->map(fn($y) => (int)$y)
            ->filter(fn($y) => $y >= 2026)
            ->toArray();

        $salaryYears = StaffSalaryPayment::selectRaw('YEAR(COALESCE(payment_date, created_at)) as year')
            ->groupBy('year')
            ->pluck('year')
            ->map(fn($y) => (int)$y)
            ->filter(fn($y) => $y >= 2026)
            ->toArray();

        $productDistributionYears = ProductDistribution::selectRaw('YEAR(created_at) as year')
            ->groupBy('year')
            ->pluck('year')
            ->map(fn($y) => (int)$y)
            ->filter(fn($y) => $y >= 2026)
            ->toArray();

        $productPurchaseYears = ProductPurchase::selectRaw('YEAR(created_at) as year')
            ->groupBy('year')
            ->pluck('year')
            ->map(fn($y) => (int)$y)
            ->filter(fn($y) => $y >= 2026)
            ->toArray();

        $roomChangeYears = RoomChangeHistory::selectRaw('YEAR(created_at) as year')
            ->groupBy('year')
            ->pluck('year')
            ->map(fn($y) => (int)$y)
            ->filter(fn($y) => $y >= 2026)
            ->toArray();

        $monthlyPaymentYears = MonthlyPayment::selectRaw('SUBSTRING(payment_month, 1, 4) as year')
            ->whereNotNull('payment_month')
            ->groupBy('year')
            ->pluck('year')
            ->map(fn($y) => (int)$y)
            ->filter(fn($y) => $y >= 2026)
            ->toArray();

        $txYears = MonthlyPaymentTransaction::selectRaw('YEAR(payment_date) as year')
            ->whereNotNull('payment_date')
            ->groupBy('year')
            ->pluck('year')
            ->map(fn($y) => (int)$y)
            ->filter(fn($y) => $y >= 2026)
            ->toArray();

        $rangeYears = range(2026, $currentYear);

        $allYears = array_unique(array_merge(
            $rangeYears,
            $bookingYears,
            $monthlyPaymentYears,
            $txYears,
            $expenseYears,
            $salaryYears,
            $productDistributionYears,
            $productPurchaseYears,
            $roomChangeYears
        ));

        rsort($allYears);

        return response()->json([
            'status' => true,
            'years'  => array_values($allYears),
        ]);
    }

    public function getMonthlyDetailReport(Request $request)
    {
        $year = (int) ($request->year ?? date('Y'));
        $month = (int) ($request->month ?? date('n'));

        $monthName = date('M', mktime(0, 0, 0, $month, 10));

        // 1. Room Booking Advance & Development Fee
        $bookingsInMonth = RoomBookingHistory::where(function($q) use ($year, $month) {
            $q->where(function($sub) use ($year, $month) {
                $sub->whereYear('check_in', $year)->whereMonth('check_in', $month);
            })->orWhere(function($sub) use ($year, $month) {
                $sub->whereYear('created_at', $year)->whereMonth('created_at', $month);
            });
        })->get();

        $calcRoomBookingAdvanceItems = function ($bookings) {
            $itemsList = [];
            foreach ($bookings as $row) {
                $items = is_string($row->floor_number_room_number_roomprice)
                    ? (json_decode($row->floor_number_room_number_roomprice, true) ?? [])
                    : ($row->floor_number_room_number_roomprice ?? []);

                $noticeDate = $row->notice_date ? \Carbon\Carbon::parse($row->notice_date) : null;
                $checkoutDate = $row->today_check_out ? \Carbon\Carbon::parse($row->today_check_out) : ($row->check_out ? \Carbon\Carbon::parse($row->check_out) : now());
                $noticeDaysElapsed = $noticeDate ? (int) $noticeDate->diffInDays($checkoutDate) : 0;
                $isNoticeFulfilled = $row->will_leave == 1 && $noticeDaysElapsed >= 60;

                $totalAdv = collect($items)->sum(function ($i) use ($row, $isNoticeFulfilled) {
                    if ($row->status == 1 && !$isNoticeFulfilled) {
                        return (float)($i['advance_price'] ?? 0);
                    }
                    return (float)($i['original_advance_price'] ?? $i['advance_price'] ?? 0);
                });

                if ($totalAdv <= 0 && isset($row->advance_fee) && is_numeric($row->advance_fee)) {
                    $totalAdv = (float) $row->advance_fee;
                }

                $devFee = (float) ($row->development_fee ?? 0);
                $totalBookingFee = $totalAdv + $devFee;

                if ($totalBookingFee > 0) {
                    $itemsList[] = [
                        'id'                => $row->id,
                        'name'              => $row->full_name ?? $row->name ?? 'N/A',
                        'phone'             => $row->phone ?? 'N/A',
                        'check_in'          => $row->check_in ? Carbon::parse($row->check_in)->format('d M Y') : ($row->created_at ? $row->created_at->format('d M Y') : 'N/A'),
                        'advance_price'     => $totalAdv,
                        'development_fee'   => $devFee,
                        'total_booking_fee' => $totalBookingFee,
                    ];
                }
            }
            return $itemsList;
        };

        $roomBookingsList = $calcRoomBookingAdvanceItems($bookingsInMonth);
        $totalRoomBooking = (float) collect($roomBookingsList)->sum('total_booking_fee');

        // 2. Monthly Payments & Electricity Bills
        $targetMonthStr = sprintf('%04d-%02d', $year, $month);

        $txRows = MonthlyPaymentTransaction::with(['booking', 'monthlyPayment'])
            ->whereYear('payment_date', $year)
            ->whereMonth('payment_date', $month)
            ->get();

        $monthlyPaymentsList = [];

        if ($txRows->count() > 0) {
            foreach ($txRows as $tx) {
                $monthStr = $tx->payment_month ? Carbon::parse($tx->payment_month . '-01')->format('F Y') : 'N/A';
                $residentName = $tx->booking->full_name ?? $tx->booking->name ?? 'N/A';
                $phone = $tx->booking->phone ?? 'N/A';
                $dateStr = $tx->payment_date ? $tx->payment_date->format('d M Y h:i A') : ($tx->created_at ? $tx->created_at->format('d M Y') : 'N/A');
                $rentPaid = max(0, (float) ($tx->total_collected ?? 0) - (float) ($tx->electricity_bill_paid ?? 0));

                if ($rentPaid > 0) {
                    $monthlyPaymentsList[] = [
                        'id'             => $tx->id,
                        'resident_name'  => $residentName,
                        'phone'          => $phone,
                        'payment_month'  => $monthStr,
                        'paid_amount'    => $rentPaid,
                        'payment_method' => $tx->payment_method ?? 'Cash',
                        'date'           => $dateStr,
                    ];
                }
            }
        } else {
            $mpRows = MonthlyPayment::with('booking')
                ->where(function ($q) use ($targetMonthStr) {
                    $q->where('payment_month', $targetMonthStr)
                        ->orWhere('payment_month', 'like', $targetMonthStr . '%');
                })
                ->where('paid_amount', '>', 0)
                ->get();

            foreach ($mpRows as $mp) {
                $residentName = $mp->booking->full_name ?? $mp->booking->name ?? 'N/A';
                $phone = $mp->booking->phone ?? 'N/A';
                $dateStr = $mp->created_at ? $mp->created_at->format('d M Y') : 'N/A';
                $paymentMonthStr = $mp->payment_month ?? $mp->months_name ?? 'N/A';
                $rent = (float) ($mp->paid_amount ?? 0);

                if ($rent > 0) {
                    $monthlyPaymentsList[] = [
                        'id'             => $mp->id,
                        'resident_name'  => $residentName,
                        'phone'          => $phone,
                        'payment_month'  => $paymentMonthStr,
                        'paid_amount'    => $rent,
                        'payment_method' => $mp->payment_method ?? 'N/A',
                        'date'           => $dateStr,
                    ];
                }
            }
        }
        $totalMonthlyPayment = (float) collect($monthlyPaymentsList)->sum('paid_amount');

        // Electricity Bills for the month from Electricity Bill Module
        $elecRows = MonthlyPayment::with('booking')
            ->where(function ($q) use ($targetMonthStr) {
                $q->where('payment_month', $targetMonthStr)
                    ->orWhere('payment_month', 'like', $targetMonthStr . '%');
            })
            ->where('electricity_bill', '>', 0)
            ->get();

        $electricityBillsList = [];
        foreach ($elecRows as $mp) {
            $residentName = $mp->booking->full_name ?? $mp->booking->name ?? 'N/A';
            $phone = $mp->booking->phone ?? 'N/A';
            $dateStr = $mp->updated_at ? $mp->updated_at->format('d M Y') : ($mp->created_at ? $mp->created_at->format('d M Y') : 'N/A');
            $paymentMonthStr = $mp->payment_month ?? 'N/A';
            $elec = (float) ($mp->electricity_bill ?? 0);

            if ($elec > 0) {
                $electricityBillsList[] = [
                    'id'             => $mp->id,
                    'resident_name'  => $residentName,
                    'phone'          => $phone,
                    'payment_month'  => $paymentMonthStr,
                    'bill_amount'    => $elec,
                    'payment_method' => 'Electricity Bill Module',
                    'date'           => $dateStr,
                ];
            }
        }
        $totalElectricityBill = (float) collect($electricityBillsList)->sum('bill_amount');

        // 3. Product Sales
        $productSalesList = ProductDistribution::with('customer')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->get()
            ->map(function ($ps) {
                return [
                    'id'            => $ps->id,
                    'memo_number'   => $ps->memo_number ?? 'N/A',
                    'customer_name' => $ps->customer->full_name ?? $ps->customer->name ?? $ps->customer_name ?? 'N/A',
                    'product_name'  => $ps->product_name ?? 'N/A',
                    'quantity'      => $ps->customer_quantity ?? 1,
                    'amount'        => (float) $ps->total_price_available,
                    'date'          => $ps->created_at ? $ps->created_at->format('d M Y') : 'N/A',
                ];
            });
        $totalProductSales = (float) $productSalesList->sum('amount');

        // 4. Room Change Fee
        $roomChangeList = RoomChangeHistory::with('booking')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->get()
            ->map(function ($rc) {
                return [
                    'id'            => $rc->id,
                    'resident_name' => $rc->resident_name ?? $rc->booking->full_name ?? 'N/A',
                    'phone'         => $rc->phone ?? 'N/A',
                    'old_room'      => ($rc->old_floor ? $rc->old_floor . ' - ' : '') . ($rc->old_room_seat ?? 'N/A'),
                    'new_room'      => ($rc->new_floor ? $rc->new_floor . ' - ' : '') . ($rc->new_room_seat ?? 'N/A'),
                    'fee_amount'    => (float) $rc->fee_amount,
                    'date'          => $rc->created_at ? $rc->created_at->format('d M Y') : 'N/A',
                ];
            });
        $totalRoomChangeFee = (float) $roomChangeList->sum('fee_amount');

        $totalIncome = $totalRoomBooking + $totalMonthlyPayment + $totalElectricityBill + $totalRoomChangeFee;

        // ====== COSTS ======
        // 6. General Expenses
        $expenseList = Expense::with('expensetype')
            ->where(function($q) use ($year, $month) {
                $q->whereYear('date', $year)->whereMonth('date', $month)
                  ->orWhere(function($sub) use ($year, $month) {
                      $sub->whereNull('date')->whereYear('created_at', $year)->whereMonth('created_at', $month);
                  });
            })
            ->get()
            ->map(function ($ex) {
                return [
                    'id'             => $ex->id,
                    'category'       => $ex->expensetype->name ?? $ex->expense_category ?? 'General',
                    'note'           => $ex->expense_note ?? 'N/A',
                    'expense_amount' => (float) $ex->expense_amount,
                    'date'           => $ex->date ? Carbon::parse($ex->date)->format('d M Y') : ($ex->created_at ? $ex->created_at->format('d M Y') : 'N/A'),
                ];
            });
        $totalExpense = (float) $expenseList->sum('expense_amount');

        // 7. Staff Salary
        $salaryList = StaffSalaryPayment::with('staff')
            ->where(function($q) use ($year, $month) {
                $q->whereYear('payment_date', $year)->whereMonth('payment_date', $month)
                  ->orWhere(function($sub) use ($year, $month) {
                      $sub->whereNull('payment_date')->whereYear('created_at', $year)->whereMonth('created_at', $month);
                  });
            })
            ->get()
            ->map(function ($sal) {
                return [
                    'id'           => $sal->id,
                    'staff_name'   => $sal->staff->name ?? 'N/A',
                    'designation'  => $sal->staff->designation ?? 'Staff',
                    'salary_month' => $sal->salary_month ? date('F', mktime(0, 0, 0, $sal->salary_month, 10)) : 'N/A',
                    'amount'       => (float) $sal->amount,
                    'payment_date' => $sal->payment_date ? Carbon::parse($sal->payment_date)->format('d M Y') : ($sal->created_at ? $sal->created_at->format('d M Y') : 'N/A'),
                ];
            });
        $totalSalary = (float) $salaryList->sum('amount');

        // 8. Advance Refund
        $refundedBookings = RoomBookingHistory::where('status', 1)
            ->where('will_leave', 1)
            ->whereNotNull('notice_date')
            ->where(function ($query) use ($year, $month) {
                $query->where(function ($q) use ($year, $month) {
                    $q->whereYear('today_check_out', $year)->whereMonth('today_check_out', $month);
                })->orWhere(function ($q) use ($year, $month) {
                    $q->whereNull('today_check_out')
                        ->whereYear('check_out', $year)
                        ->whereMonth('check_out', $month);
                });
            })->get()->filter(function ($row) {
                $noticeDate = \Carbon\Carbon::parse($row->notice_date);
                $checkoutDate = $row->today_check_out ? \Carbon\Carbon::parse($row->today_check_out) : ($row->check_out ? \Carbon\Carbon::parse($row->check_out) : now());
                return (int) $noticeDate->diffInDays($checkoutDate) >= 60;
            });

        $advanceRefundList = $refundedBookings->map(function ($row) {
            $items = is_string($row->floor_number_room_number_roomprice)
                ? (json_decode($row->floor_number_room_number_roomprice, true) ?? [])
                : ($row->floor_number_room_number_roomprice ?? []);
            $adv = collect($items)->sum(function ($i) {
                return (float)($i['original_advance_price'] ?? $i['advance_price'] ?? 0);
            });
            $refundAmount = $adv > 0 ? $adv : ((float)($row->monthly_amount ?? 0) * 2);
            $checkoutDateStr = $row->today_check_out ? Carbon::parse($row->today_check_out)->format('d M Y') : ($row->check_out ? Carbon::parse($row->check_out)->format('d M Y') : 'N/A');

            return [
                'id'            => $row->id,
                'name'          => $row->full_name ?? $row->name ?? 'N/A',
                'phone'         => $row->phone ?? 'N/A',
                'checkout_date' => $checkoutDateStr,
                'refund_amount' => (float) $refundAmount,
            ];
        })->values();

        $totalAdvanceRefund = (float) collect($advanceRefundList)->sum('refund_amount');

        $totalCost = $totalExpense + $totalSalary + $totalAdvanceRefund;

        return response()->json([
            'status' => true,
            'period' => $monthName . ' ' . $year,
            'year'   => $year,
            'month'  => $month,
            'income' => [
                'room_bookings'     => $roomBookingsList,
                'monthly_payments'  => $monthlyPaymentsList,
                'electricity_bills' => $electricityBillsList,
                'room_change_fees'  => $roomChangeList,
                'total_income'      => $totalIncome,
            ],
            'cost' => [
                'general_expenses' => $expenseList,
                'staff_salaries'   => $salaryList,
                'advance_refunds'  => $advanceRefundList,
                'total_cost'       => $totalCost,
            ],
            'profit_loss' => $totalIncome - $totalCost,
        ]);
    }

    public function getDailyDetailReport(Request $request)
    {
        $dateStr = $request->date ? Carbon::parse($request->date)->format('Y-m-d') : date('Y-m-d');
        $displayDate = Carbon::parse($dateStr)->format('d M Y');

        // ====== INCOME ======
        // 1. Room Booking Advance & Development Fee
        $bookingsOnDay = RoomBookingHistory::where(function($q) use ($dateStr) {
            $q->whereDate('check_in', $dateStr)
              ->orWhereDate('created_at', $dateStr);
        })->get();

        $calcRoomBookingAdvanceItems = function ($bookings) {
            $itemsList = [];
            foreach ($bookings as $row) {
                $items = is_string($row->floor_number_room_number_roomprice)
                    ? (json_decode($row->floor_number_room_number_roomprice, true) ?? [])
                    : ($row->floor_number_room_number_roomprice ?? []);

                $noticeDate = $row->notice_date ? \Carbon\Carbon::parse($row->notice_date) : null;
                $checkoutDate = $row->today_check_out ? \Carbon\Carbon::parse($row->today_check_out) : ($row->check_out ? \Carbon\Carbon::parse($row->check_out) : now());
                $noticeDaysElapsed = $noticeDate ? (int) $noticeDate->diffInDays($checkoutDate) : 0;
                $isNoticeFulfilled = $row->will_leave == 1 && $noticeDaysElapsed >= 60;

                $totalAdv = collect($items)->sum(function ($i) use ($row, $isNoticeFulfilled) {
                    if ($row->status == 1 && !$isNoticeFulfilled) {
                        return (float)($i['advance_price'] ?? 0);
                    }
                    return (float)($i['original_advance_price'] ?? $i['advance_price'] ?? 0);
                });

                if ($totalAdv <= 0 && isset($row->advance_fee) && is_numeric($row->advance_fee)) {
                    $totalAdv = (float) $row->advance_fee;
                }

                $devFee = (float) ($row->development_fee ?? 0);
                $totalBookingFee = $totalAdv + $devFee;

                if ($totalBookingFee > 0) {
                    $itemsList[] = [
                        'id'                => $row->id,
                        'name'              => $row->full_name ?? $row->name ?? 'N/A',
                        'phone'             => $row->phone ?? 'N/A',
                        'check_in'          => $row->check_in ? Carbon::parse($row->check_in)->format('d M Y') : ($row->created_at ? $row->created_at->format('d M Y') : 'N/A'),
                        'advance_price'     => $totalAdv,
                        'development_fee'   => $devFee,
                        'total_booking_fee' => $totalBookingFee,
                    ];
                }
            }
            return $itemsList;
        };

        $roomBookingsList = $calcRoomBookingAdvanceItems($bookingsOnDay);
        $totalRoomBooking = (float) collect($roomBookingsList)->sum('total_booking_fee');

        // 2. Monthly Payments & Electricity Bills
        $txDaily = MonthlyPaymentTransaction::with(['booking', 'monthlyPayment'])
            ->whereDate('payment_date', $dateStr)
            ->get();

        $dailyMonthlyPaymentsList = [];

        if ($txDaily->count() > 0) {
            foreach ($txDaily as $tx) {
                $monthStr = $tx->payment_month ? Carbon::parse($tx->payment_month . '-01')->format('F Y') : 'N/A';
                $residentName = $tx->booking->full_name ?? $tx->booking->name ?? 'N/A';
                $phone = $tx->booking->phone ?? 'N/A';
                $dateFormatted = $tx->payment_date ? $tx->payment_date->format('d M Y h:i A') : 'N/A';
                $rentPaid = max(0, (float) ($tx->total_collected ?? 0) - (float) ($tx->electricity_bill_paid ?? 0));

                if ($rentPaid > 0) {
                    $dailyMonthlyPaymentsList[] = [
                        'id'             => $tx->id,
                        'resident_name'  => $residentName,
                        'phone'          => $phone,
                        'payment_month'  => $monthStr,
                        'paid_amount'    => $rentPaid,
                        'payment_method' => $tx->payment_method ?? 'Cash',
                        'date'           => $dateFormatted,
                    ];
                }
            }
        } else {
            $dailyMpRows = MonthlyPayment::with('booking')
                ->whereDate('created_at', $dateStr)
                ->where('paid_amount', '>', 0)
                ->get();

            foreach ($dailyMpRows as $mp) {
                $residentName = $mp->booking->full_name ?? $mp->booking->name ?? 'N/A';
                $phone = $mp->booking->phone ?? 'N/A';
                $dateFormatted = $mp->created_at ? $mp->created_at->format('d M Y h:i A') : 'N/A';
                $paymentMonthStr = $mp->payment_month ?? $mp->months_name ?? 'N/A';
                $rent = (float) ($mp->paid_amount ?? 0);

                if ($rent > 0) {
                    $dailyMonthlyPaymentsList[] = [
                        'id'             => $mp->id,
                        'resident_name'  => $residentName,
                        'phone'          => $phone,
                        'payment_month'  => $paymentMonthStr,
                        'paid_amount'    => $rent,
                        'payment_method' => $mp->payment_method ?? 'N/A',
                        'date'           => $dateFormatted,
                    ];
                }
            }
        }
        $totalMonthlyPayment = (float) collect($dailyMonthlyPaymentsList)->sum('paid_amount');

        // Electricity Bills entered/created on that day in Electricity Bill Module (by created_at)
        $dailyElecRows = MonthlyPayment::with('booking')
            ->whereDate('created_at', $dateStr)
            ->where('electricity_bill', '>', 0)
            ->get();

        $dailyElectricityBillsList = [];
        foreach ($dailyElecRows as $mp) {
            $residentName = $mp->booking->full_name ?? $mp->booking->name ?? 'N/A';
            $phone = $mp->booking->phone ?? 'N/A';
            $dateFormatted = $mp->created_at ? $mp->created_at->format('d M Y h:i A') : 'N/A';
            $paymentMonthStr = $mp->payment_month ?? 'N/A';
            $elec = (float) ($mp->electricity_bill ?? 0);

            if ($elec > 0) {
                $dailyElectricityBillsList[] = [
                    'id'             => $mp->id,
                    'resident_name'  => $residentName,
                    'phone'          => $phone,
                    'payment_month'  => $paymentMonthStr,
                    'bill_amount'    => $elec,
                    'payment_method' => 'Electricity Bill Module',
                    'date'           => $dateFormatted,
                ];
            }
        }
        $totalElectricityBill = (float) collect($dailyElectricityBillsList)->sum('bill_amount');

        // 3. Room Change Fees
        $roomChangeList = RoomChangeHistory::whereDate('created_at', $dateStr)
            ->get()
            ->map(function ($rc) {
                return [
                    'id'         => $rc->id,
                    'user_id'    => $rc->user_id,
                    'fee_amount' => (float) $rc->fee_amount,
                    'note'       => $rc->reason ?? 'Room/Seat Change',
                    'date'       => $rc->created_at ? $rc->created_at->format('d M Y') : 'N/A',
                ];
            });
        $totalRoomChangeFee = (float) $roomChangeList->sum('fee_amount');

        $totalIncome = $totalRoomBooking + $totalMonthlyPayment + $totalElectricityBill + $totalRoomChangeFee;

        // ====== COSTS ======
        // 5. General Expenses
        $expenseList = Expense::with('expensetype')
            ->where(function($q) use ($dateStr) {
                $q->whereDate('date', $dateStr)
                  ->orWhere(function($sub) use ($dateStr) {
                      $sub->whereNull('date')->whereDate('created_at', $dateStr);
                  });
            })
            ->get()
            ->map(function ($ex) {
                return [
                    'id'             => $ex->id,
                    'category'       => $ex->expensetype->name ?? $ex->expense_category ?? 'General',
                    'note'           => $ex->expense_note ?? 'N/A',
                    'expense_amount' => (float) $ex->expense_amount,
                    'date'           => $ex->date ? Carbon::parse($ex->date)->format('d M Y') : ($ex->created_at ? $ex->created_at->format('d M Y') : 'N/A'),
                ];
            });
        $totalExpense = (float) $expenseList->sum('expense_amount');

        // 6. Staff Salary
        $salaryList = StaffSalaryPayment::with('staff')
            ->where(function($q) use ($dateStr) {
                $q->whereDate('payment_date', $dateStr)
                  ->orWhere(function($sub) use ($dateStr) {
                      $sub->whereNull('payment_date')->whereDate('created_at', $dateStr);
                  });
            })
            ->get()
            ->map(function ($sal) {
                return [
                    'id'           => $sal->id,
                    'staff_name'   => $sal->staff->name ?? 'N/A',
                    'designation'  => $sal->staff->designation ?? 'Staff',
                    'salary_month' => $sal->salary_month ? date('F', mktime(0, 0, 0, $sal->salary_month, 10)) : 'N/A',
                    'amount'       => (float) $sal->amount,
                    'payment_date' => $sal->payment_date ? Carbon::parse($sal->payment_date)->format('d M Y') : ($sal->created_at ? $sal->created_at->format('d M Y') : 'N/A'),
                ];
            });
        $totalSalary = (float) $salaryList->sum('amount');

        // 7. Advance Refund
        $refundedBookings = RoomBookingHistory::where('status', 1)
            ->where('will_leave', 1)
            ->whereNotNull('notice_date')
            ->where(function ($query) use ($dateStr) {
                $query->whereDate('today_check_out', $dateStr)
                    ->orWhere(function ($q) use ($dateStr) {
                        $q->whereNull('today_check_out')
                            ->whereDate('check_out', $dateStr);
                    });
            })->get()->filter(function ($row) {
                $noticeDate = \Carbon\Carbon::parse($row->notice_date);
                $checkoutDate = $row->today_check_out ? \Carbon\Carbon::parse($row->today_check_out) : ($row->check_out ? \Carbon\Carbon::parse($row->check_out) : now());
                return (int) $noticeDate->diffInDays($checkoutDate) >= 60;
            });

        $advanceRefundList = $refundedBookings->map(function ($row) {
            $items = is_string($row->floor_number_room_number_roomprice)
                ? (json_decode($row->floor_number_room_number_roomprice, true) ?? [])
                : ($row->floor_number_room_number_roomprice ?? []);
            $adv = collect($items)->sum(function ($i) {
                return (float)($i['original_advance_price'] ?? $i['advance_price'] ?? 0);
            });
            $refundAmount = $adv > 0 ? $adv : ((float)($row->monthly_amount ?? 0) * 2);
            $checkoutDateStr = $row->today_check_out ? Carbon::parse($row->today_check_out)->format('d M Y') : ($row->check_out ? Carbon::parse($row->check_out)->format('d M Y') : 'N/A');

            return [
                'id'            => $row->id,
                'name'          => $row->full_name ?? $row->name ?? 'N/A',
                'phone'         => $row->phone ?? 'N/A',
                'checkout_date' => $checkoutDateStr,
                'refund_amount' => (float) $refundAmount,
            ];
        })->values();

        $totalAdvanceRefund = (float) collect($advanceRefundList)->sum('refund_amount');

        $totalCost = $totalExpense + $totalSalary + $totalAdvanceRefund;

        return response()->json([
            'status' => true,
            'period' => $displayDate,
            'date'   => $dateStr,
            'income' => [
                'room_bookings'     => $roomBookingsList,
                'monthly_payments'  => $dailyMonthlyPaymentsList,
                'electricity_bills' => $dailyElectricityBillsList,
                'room_change_fees'  => $roomChangeList,
                'total_income'      => $totalIncome,
            ],
            'cost' => [
                'general_expenses' => $expenseList,
                'staff_salaries'   => $salaryList,
                'advance_refunds'  => $advanceRefundList,
                'total_cost'       => $totalCost,
            ],
            'profit_loss' => $totalIncome - $totalCost,
        ]);
    }
}
