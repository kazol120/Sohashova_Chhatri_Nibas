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
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{

public function index(){
    return view('backend.report.report');
}

public function profitLossReport(Request $request)
{
    $mode = $request->mode ?? 'monthly';

    $months = [
        1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',
        5=>'May',6=>'Jun',7=>'Jul',8=>'Aug',
        9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec',
    ];

    $rows = [];

    if ($mode === 'monthly') {
        $year = (int) ($request->year ?? date('Y'));

        foreach ($months as $num => $name) {
            // ====== INCOME ======
            // 1) Advance booking fee from floor_number_room_number_roomprice
            $bookingsInMonth = RoomBookingHistory::whereYear('check_in', $year)
                ->whereMonth('check_in', $num)->get();

            $roomBooking = (float) $bookingsInMonth->sum(function($row) {
                $items = is_string($row->floor_number_room_number_roomprice)
                    ? (json_decode($row->floor_number_room_number_roomprice, true) ?? [])
                    : ($row->floor_number_room_number_roomprice ?? []);
                $advSum = collect($items)->sum(function($i) {
                    return (float)($i['original_advance_price'] ?? $i['advance_price'] ?? 0);
                });
                return $advSum > 0 ? $advSum : (float)($row->monthly_amount ?? 0);
            });

            $monthlyPayment = (float) MonthlyPayment::whereYear('created_at', $year)
                ->whereMonth('created_at', $num)->sum('paid_amount');

            $productSales   = (float) ProductDistribution::whereYear('created_at', $year)
                ->whereMonth('created_at', $num)->sum('total_price_available');

            $totalIncome = $roomBooking + $monthlyPayment + $productSales;

            // ====== COST ======
            $expense         = (float) Expense::whereYear('created_at', $year)
                ->whereMonth('created_at', $num)->sum('expense_amount');

            $salary          = (float) StaffSalaryPayment::whereYear('created_at', $year)
                ->whereMonth('created_at', $num)->sum('amount');

            $productPurchase = (float) ProductPurchase::whereYear('created_at', $year)
                ->whereMonth('created_at', $num)->sum('total_price');

            // Advance Refund for residents checked out in this month with 2 months notice fulfilled
            $refundedBookings = RoomBookingHistory::where('status', 1)
                ->where('will_leave', 1)
                ->whereNotNull('notice_date')
                ->where(function($query) use ($year, $num) {
                    $query->where(function($q) use ($year, $num) {
                        $q->whereYear('today_check_out', $year)->whereMonth('today_check_out', $num);
                    })->orWhere(function($q) use ($year, $num) {
                        $q->whereNull('today_check_out')
                          ->whereYear('check_out', $year)
                          ->whereMonth('check_out', $num);
                    });
                })->get()->filter(function($row) {
                    $noticeDate = \Carbon\Carbon::parse($row->notice_date);
                    $checkoutDate = $row->today_check_out ? \Carbon\Carbon::parse($row->today_check_out) : ($row->check_out ? \Carbon\Carbon::parse($row->check_out) : now());
                    return (int) $noticeDate->diffInDays($checkoutDate) >= 60;
                });

            $advanceRefund = (float) $refundedBookings->sum(function($row) {
                $items = is_string($row->floor_number_room_number_roomprice)
                    ? (json_decode($row->floor_number_room_number_roomprice, true) ?? [])
                    : ($row->floor_number_room_number_roomprice ?? []);
                $adv = collect($items)->sum(function($i) {
                    return (float)($i['original_advance_price'] ?? $i['advance_price'] ?? 0);
                });
                return $adv > 0 ? $adv : ((float)($row->monthly_amount ?? 0) * 2);
            });

            $totalCost = $expense + $salary + $productPurchase + $advanceRefund;

            $rows[] = [
                'label'            => $name . ' ' . $year,
                'room_booking'     => $roomBooking,
                'monthly_payment'  => $monthlyPayment,
                'product_sales'    => $productSales,
                'total_income'     => $totalIncome,
                'expense'          => $expense,
                'salary'           => $salary,
                'product_purchase' => $productPurchase,
                'advance_refund'   => $advanceRefund,
                'total_cost'       => $totalCost,
                'profit_loss'      => $totalIncome - $totalCost,
            ];
        }

    } else {
        $currentYear = (int) date('Y');

        $oldestYear = min(
            (int) (RoomBookingHistory::min(DB::raw('YEAR(check_in)')) ?: $currentYear),
            (int) (Expense::min(DB::raw('YEAR(created_at)')) ?: $currentYear),
            (int) (StaffSalaryPayment::min(DB::raw('YEAR(created_at)')) ?: $currentYear),
            (int) (ProductDistribution::min(DB::raw('YEAR(created_at)')) ?: $currentYear),
            (int) (ProductPurchase::min(DB::raw('YEAR(created_at)')) ?: $currentYear),
        );

        foreach (range($currentYear, $oldestYear) as $year) {
            // ====== INCOME ======
            $bookingsInYear = RoomBookingHistory::whereYear('check_in', $year)->get();
            $roomBooking = (float) $bookingsInYear->sum(function($row) {
                $items = is_string($row->floor_number_room_number_roomprice)
                    ? (json_decode($row->floor_number_room_number_roomprice, true) ?? [])
                    : ($row->floor_number_room_number_roomprice ?? []);
                $advSum = collect($items)->sum(function($i) {
                    return (float)($i['original_advance_price'] ?? $i['advance_price'] ?? 0);
                });
                return $advSum > 0 ? $advSum : (float)($row->monthly_amount ?? 0);
            });

            $monthlyPayment = (float) MonthlyPayment::whereYear('created_at', $year)->sum('paid_amount');
            $productSales   = (float) ProductDistribution::whereYear('created_at', $year)->sum('total_price_available');
            $totalIncome    = $roomBooking + $monthlyPayment + $productSales;

            // ====== COST ======
            $expense         = (float) Expense::whereYear('created_at', $year)->sum('expense_amount');
            $salary          = (float) StaffSalaryPayment::whereYear('created_at', $year)->sum('amount');
            $productPurchase = (float) ProductPurchase::whereYear('created_at', $year)->sum('total_price');

            $refundedBookingsYear = RoomBookingHistory::where('status', 1)
                ->where('will_leave', 1)
                ->whereNotNull('notice_date')
                ->where(function($query) use ($year) {
                    $query->whereYear('today_check_out', $year)
                          ->orWhere(function($q) use ($year) {
                              $q->whereNull('today_check_out')->whereYear('check_out', $year);
                          });
                })->get()->filter(function($row) {
                    $noticeDate = \Carbon\Carbon::parse($row->notice_date);
                    $checkoutDate = $row->today_check_out ? \Carbon\Carbon::parse($row->today_check_out) : ($row->check_out ? \Carbon\Carbon::parse($row->check_out) : now());
                    return (int) $noticeDate->diffInDays($checkoutDate) >= 60;
                });

            $advanceRefund = (float) $refundedBookingsYear->sum(function($row) {
                $items = is_string($row->floor_number_room_number_roomprice)
                    ? (json_decode($row->floor_number_room_number_roomprice, true) ?? [])
                    : ($row->floor_number_room_number_roomprice ?? []);
                $adv = collect($items)->sum(function($i) {
                    return (float)($i['original_advance_price'] ?? $i['advance_price'] ?? 0);
                });
                return $adv > 0 ? $adv : ((float)($row->monthly_amount ?? 0) * 2);
            });

            $totalCost = $expense + $salary + $productPurchase + $advanceRefund;

            if ($roomBooking == 0 && $monthlyPayment == 0 && $productSales == 0
                && $expense == 0 && $salary == 0 && $productPurchase == 0 && $advanceRefund == 0) continue;

            // Monthly breakdown
            $monthlyBreakdown = [];
            foreach ($months as $num => $name) {
                $mBookings = RoomBookingHistory::whereYear('check_in', $year)->whereMonth('check_in', $num)->get();
                $mRoomBooking = (float) $mBookings->sum(function($row) {
                    $items = is_string($row->floor_number_room_number_roomprice)
                        ? (json_decode($row->floor_number_room_number_roomprice, true) ?? [])
                        : ($row->floor_number_room_number_roomprice ?? []);
                    $advSum = collect($items)->sum(function($i) {
                        return (float)($i['original_advance_price'] ?? $i['advance_price'] ?? 0);
                    });
                    return $advSum > 0 ? $advSum : (float)($row->monthly_amount ?? 0);
                });

                $mMonthlyPayment = (float) MonthlyPayment::whereYear('created_at', $year)->whereMonth('created_at', $num)->sum('paid_amount');
                $mProductSales   = (float) ProductDistribution::whereYear('created_at', $year)->whereMonth('created_at', $num)->sum('total_price_available');
                $mTotalIncome    = $mRoomBooking + $mMonthlyPayment + $mProductSales;

                $mExpense         = (float) Expense::whereYear('created_at', $year)->whereMonth('created_at', $num)->sum('expense_amount');
                $mSalary          = (float) StaffSalaryPayment::whereYear('created_at', $year)->whereMonth('created_at', $num)->sum('amount');
                $mProductPurchase = (float) ProductPurchase::whereYear('created_at', $year)->whereMonth('created_at', $num)->sum('total_price');

                $mRefundedBookings = RoomBookingHistory::where('status', 1)
                    ->where('will_leave', 1)
                    ->whereNotNull('notice_date')
                    ->where(function($query) use ($year, $num) {
                        $query->where(function($q) use ($year, $num) {
                            $q->whereYear('today_check_out', $year)->whereMonth('today_check_out', $num);
                        })->orWhere(function($q) use ($year, $num) {
                            $q->whereNull('today_check_out')
                              ->whereYear('check_out', $year)
                              ->whereMonth('check_out', $num);
                        });
                    })->get()->filter(function($row) {
                        $noticeDate = \Carbon\Carbon::parse($row->notice_date);
                        $checkoutDate = $row->today_check_out ? \Carbon\Carbon::parse($row->today_check_out) : ($row->check_out ? \Carbon\Carbon::parse($row->check_out) : now());
                        return (int) $noticeDate->diffInDays($checkoutDate) >= 60;
                    });

                $mAdvanceRefund = (float) $mRefundedBookings->sum(function($row) {
                    $items = is_string($row->floor_number_room_number_roomprice)
                        ? (json_decode($row->floor_number_room_number_roomprice, true) ?? [])
                        : ($row->floor_number_room_number_roomprice ?? []);
                    $adv = collect($items)->sum(function($i) {
                        return (float)($i['original_advance_price'] ?? $i['advance_price'] ?? 0);
                    });
                    return $adv > 0 ? $adv : ((float)($row->monthly_amount ?? 0) * 2);
                });

                $mTotalCost       = $mExpense + $mSalary + $mProductPurchase + $mAdvanceRefund;

                if ($mRoomBooking == 0 && $mMonthlyPayment == 0 && $mProductSales == 0
                    && $mExpense == 0 && $mSalary == 0 && $mProductPurchase == 0 && $mAdvanceRefund == 0) continue;

                $monthlyBreakdown[] = [
                    'month'            => $name,
                    'room_booking'     => $mRoomBooking,
                    'monthly_payment'  => $mMonthlyPayment,
                    'product_sales'    => $mProductSales,
                    'total_income'     => $mTotalIncome,
                    'expense'          => $mExpense,
                    'salary'           => $mSalary,
                    'product_purchase' => $mProductPurchase,
                    'advance_refund'   => $mAdvanceRefund,
                    'total_cost'       => $mTotalCost,
                    'profit_loss'      => $mTotalIncome - $mTotalCost,
                ];
            }

            $rows[] = [
                'label'             => (string) $year,
                'room_booking'      => $roomBooking,
                'monthly_payment'   => $monthlyPayment,
                'product_sales'     => $productSales,
                'total_income'      => $totalIncome,
                'expense'           => $expense,
                'salary'            => $salary,
                'product_purchase'  => $productPurchase,
                'advance_refund'    => $advanceRefund,
                'total_cost'        => $totalCost,
                'profit_loss'       => $totalIncome - $totalCost,
                'monthly_breakdown' => $monthlyBreakdown,
            ];
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
    $currentYear = (int) date('Y');

    $bookingYears = RoomBookingHistory::selectRaw('YEAR(check_in) as year')
        ->whereNotNull('check_in')
        ->groupBy('year')
        ->pluck('year')
        ->map(fn($y) => (int)$y)
        ->toArray();

    $expenseYears = Expense::selectRaw('YEAR(created_at) as year')
        ->groupBy('year')
        ->pluck('year')
        ->map(fn($y) => (int)$y)
        ->toArray();

    $salaryYears = StaffSalaryPayment::selectRaw('YEAR(created_at) as year')
        ->groupBy('year')
        ->pluck('year')
        ->map(fn($y) => (int)$y)
        ->toArray();

    $productDistributionYears = ProductDistribution::selectRaw('YEAR(created_at) as year')
        ->groupBy('year')
        ->pluck('year')
        ->map(fn($y) => (int)$y)
        ->toArray();

    $productPurchaseYears = ProductPurchase::selectRaw('YEAR(created_at) as year')
        ->groupBy('year')
        ->pluck('year')
        ->map(fn($y) => (int)$y)
        ->toArray();

    $allYears = array_unique(array_merge(
        [$currentYear],
        $bookingYears,
        $expenseYears,
        $salaryYears,
        $productDistributionYears,
        $productPurchaseYears
    ));

    rsort($allYears);

    return response()->json([
        'status' => true,
        'years'  => array_values($allYears),
    ]);
}


}
