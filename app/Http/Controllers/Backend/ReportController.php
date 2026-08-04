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
use App\Models\Backend\RoomChangeHistory;
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

        if ($mode === 'monthly') {
            $year = (int) ($request->year ?? date('Y'));

            $calcRoomBookingAdvance = function ($bookings) {
                return (float) $bookings->sum(function ($row) {
                    $items = is_string($row->floor_number_room_number_roomprice)
                        ? (json_decode($row->floor_number_room_number_roomprice, true) ?? [])
                        : ($row->floor_number_room_number_roomprice ?? []);

                    $noticeDate = $row->notice_date ? \Carbon\Carbon::parse($row->notice_date) : null;
                    $checkoutDate = $row->today_check_out ? \Carbon\Carbon::parse($row->today_check_out) : ($row->check_out ? \Carbon\Carbon::parse($row->check_out) : now());
                    $noticeDaysElapsed = $noticeDate ? (int) $noticeDate->diffInDays($checkoutDate) : 0;
                    $isNoticeFulfilled = $row->will_leave == 1 && $noticeDaysElapsed >= 60;

                    return collect($items)->sum(function ($i) use ($row, $isNoticeFulfilled) {
                        if ($row->status == 1 && !$isNoticeFulfilled) {
                            return (float)($i['advance_price'] ?? 0);
                        }
                        return (float)($i['original_advance_price'] ?? $i['advance_price'] ?? 0);
                    });
                });
            };

            foreach ($months as $num => $name) {
                // ====== INCOME ======
                $bookingsInMonth = RoomBookingHistory::whereYear('check_in', $year)
                    ->whereMonth('check_in', $num)->get();

                $roomBooking = $calcRoomBookingAdvance($bookingsInMonth);

                $monthlyPayment = (float) MonthlyPayment::whereYear('created_at', $year)
                    ->whereMonth('created_at', $num)->sum('paid_amount');

                $productSales   = (float) ProductDistribution::whereYear('created_at', $year)
                    ->whereMonth('created_at', $num)->sum('total_price_available');

                $roomChangeFee  = (float) RoomChangeHistory::whereYear('created_at', $year)
                    ->whereMonth('created_at', $num)->sum('fee_amount');

                $totalIncome = $roomBooking + $monthlyPayment + $productSales + $roomChangeFee;

                // ====== COST ======
                $expense         = (float) Expense::whereYear('created_at', $year)
                    ->whereMonth('created_at', $num)->sum('expense_amount');

                $salary          = (float) StaffSalaryPayment::whereYear('created_at', $year)
                    ->whereMonth('created_at', $num)->sum('amount');

                $productPurchase = (float) ProductPurchase::whereYear('created_at', $year)
                    ->whereMonth('created_at', $num)->sum('total_price');

                $refundedBookings = RoomBookingHistory::where('status', 1)
                    ->where('will_leave', 1)
                    ->whereNotNull('notice_date')
                    ->where(function ($query) use ($year, $num) {
                        $query->where(function ($q) use ($year, $num) {
                            $q->whereYear('today_check_out', $year)->whereMonth('today_check_out', $num);
                        })->orWhere(function ($q) use ($year, $num) {
                            $q->whereNull('today_check_out')
                                ->whereYear('check_out', $year)
                                ->whereMonth('check_out', $num);
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

                $totalCost = $expense + $salary + $productPurchase + $advanceRefund;

                $rows[] = [
                    'label'            => $name . ' ' . $year,
                    'room_booking'     => $roomBooking,
                    'monthly_payment'  => $monthlyPayment,
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
            }
        } else {
            $currentYear = (int) date('Y');

            $oldestYear = min(
                (int) (RoomBookingHistory::min(DB::raw('YEAR(check_in)')) ?: $currentYear),
                (int) (Expense::min(DB::raw('YEAR(created_at)')) ?: $currentYear),
                (int) (StaffSalaryPayment::min(DB::raw('YEAR(created_at)')) ?: $currentYear),
                (int) (ProductDistribution::min(DB::raw('YEAR(created_at)')) ?: $currentYear),
                (int) (ProductPurchase::min(DB::raw('YEAR(created_at)')) ?: $currentYear),
                (int) (RoomChangeHistory::min(DB::raw('YEAR(created_at)')) ?: $currentYear),
            );

            $calcRoomBookingAdvance = function ($bookings) {
                return (float) $bookings->sum(function ($row) {
                    $items = is_string($row->floor_number_room_number_roomprice)
                        ? (json_decode($row->floor_number_room_number_roomprice, true) ?? [])
                        : ($row->floor_number_room_number_roomprice ?? []);

                    $noticeDate = $row->notice_date ? \Carbon\Carbon::parse($row->notice_date) : null;
                    $checkoutDate = $row->today_check_out ? \Carbon\Carbon::parse($row->today_check_out) : ($row->check_out ? \Carbon\Carbon::parse($row->check_out) : now());
                    $noticeDaysElapsed = $noticeDate ? (int) $noticeDate->diffInDays($checkoutDate) : 0;
                    $isNoticeFulfilled = $row->will_leave == 1 && $noticeDaysElapsed >= 60;

                    return collect($items)->sum(function ($i) use ($row, $isNoticeFulfilled) {
                        if ($row->status == 1 && !$isNoticeFulfilled) {
                            return (float)($i['advance_price'] ?? 0);
                        }
                        return (float)($i['original_advance_price'] ?? $i['advance_price'] ?? 0);
                    });
                });
            };

            foreach (range($currentYear, $oldestYear) as $year) {
                // ====== INCOME ======
                $bookingsInYear = RoomBookingHistory::whereYear('check_in', $year)->get();
                $roomBooking    = $calcRoomBookingAdvance($bookingsInYear);

                $monthlyPayment = (float) MonthlyPayment::whereYear('created_at', $year)->sum('paid_amount');
                $productSales   = (float) ProductDistribution::whereYear('created_at', $year)->sum('total_price_available');
                $roomChangeFee  = (float) RoomChangeHistory::whereYear('created_at', $year)->sum('fee_amount');
                $totalIncome    = $roomBooking + $monthlyPayment + $productSales + $roomChangeFee;

                // ====== COST ======
                $expense         = (float) Expense::whereYear('created_at', $year)->sum('expense_amount');
                $salary          = (float) StaffSalaryPayment::whereYear('created_at', $year)->sum('amount');
                $productPurchase = (float) ProductPurchase::whereYear('created_at', $year)->sum('total_price');

                $refundedBookingsYear = RoomBookingHistory::where('status', 1)
                    ->where('will_leave', 1)
                    ->whereNotNull('notice_date')
                    ->where(function ($query) use ($year) {
                        $query->whereYear('today_check_out', $year)
                            ->orWhere(function ($q) use ($year) {
                                $q->whereNull('today_check_out')->whereYear('check_out', $year);
                            });
                    })->get()->filter(function ($row) {
                        $noticeDate = \Carbon\Carbon::parse($row->notice_date);
                        $checkoutDate = $row->today_check_out ? \Carbon\Carbon::parse($row->today_check_out) : ($row->check_out ? \Carbon\Carbon::parse($row->check_out) : now());
                        return (int) $noticeDate->diffInDays($checkoutDate) >= 60;
                    });

                $advanceRefund = (float) $refundedBookingsYear->sum(function ($row) {
                    $items = is_string($row->floor_number_room_number_roomprice)
                        ? (json_decode($row->floor_number_room_number_roomprice, true) ?? [])
                        : ($row->floor_number_room_number_roomprice ?? []);
                    $adv = collect($items)->sum(function ($i) {
                        return (float)($i['original_advance_price'] ?? $i['advance_price'] ?? 0);
                    });
                    return $adv > 0 ? $adv : ((float)($row->monthly_amount ?? 0) * 2);
                });

                $totalCost = $expense + $salary + $productPurchase + $advanceRefund;

                if (
                    $roomBooking == 0 && $monthlyPayment == 0 && $productSales == 0 && $roomChangeFee == 0
                    && $expense == 0 && $salary == 0 && $productPurchase == 0 && $advanceRefund == 0
                ) continue;

                // Monthly breakdown
                $monthlyBreakdown = [];
                foreach ($months as $num => $name) {
                    $mBookings = RoomBookingHistory::whereYear('check_in', $year)->whereMonth('check_in', $num)->get();
                    $mRoomBooking = $calcRoomBookingAdvance($mBookings);

                    $mMonthlyPayment = (float) MonthlyPayment::whereYear('created_at', $year)->whereMonth('created_at', $num)->sum('paid_amount');
                    $mProductSales   = (float) ProductDistribution::whereYear('created_at', $year)->whereMonth('created_at', $num)->sum('total_price_available');
                    $mRoomChangeFee  = (float) RoomChangeHistory::whereYear('created_at', $year)->whereMonth('created_at', $num)->sum('fee_amount');
                    $mTotalIncome    = $mRoomBooking + $mMonthlyPayment + $mProductSales + $mRoomChangeFee;

                    $mExpense         = (float) Expense::whereYear('created_at', $year)->whereMonth('created_at', $num)->sum('expense_amount');
                    $mSalary          = (float) StaffSalaryPayment::whereYear('created_at', $year)->whereMonth('created_at', $num)->sum('amount');
                    $mProductPurchase = (float) ProductPurchase::whereYear('created_at', $year)->whereMonth('created_at', $num)->sum('total_price');

                    $mRefundedBookings = RoomBookingHistory::where('status', 1)
                        ->where('will_leave', 1)
                        ->whereNotNull('notice_date')
                        ->where(function ($query) use ($year, $num) {
                            $query->where(function ($q) use ($year, $num) {
                                $q->whereYear('today_check_out', $year)->whereMonth('today_check_out', $num);
                            })->orWhere(function ($q) use ($year, $num) {
                                $q->whereNull('today_check_out')
                                    ->whereYear('check_out', $year)
                                    ->whereMonth('check_out', $num);
                            });
                        })->get()->filter(function ($row) {
                            $noticeDate = \Carbon\Carbon::parse($row->notice_date);
                            $checkoutDate = $row->today_check_out ? \Carbon\Carbon::parse($row->today_check_out) : ($row->check_out ? \Carbon\Carbon::parse($row->check_out) : now());
                            return (int) $noticeDate->diffInDays($checkoutDate) >= 60;
                        });

                    $mAdvanceRefund = (float) $mRefundedBookings->sum(function ($row) {
                        $items = is_string($row->floor_number_room_number_roomprice)
                            ? (json_decode($row->floor_number_room_number_roomprice, true) ?? [])
                            : ($row->floor_number_room_number_roomprice ?? []);
                        $adv = collect($items)->sum(function ($i) {
                            return (float)($i['original_advance_price'] ?? $i['advance_price'] ?? 0);
                        });
                        return $adv > 0 ? $adv : ((float)($row->monthly_amount ?? 0) * 2);
                    });

                    $mTotalCost       = $mExpense + $mSalary + $mProductPurchase + $mAdvanceRefund;

                    if (
                        $mRoomBooking == 0 && $mMonthlyPayment == 0 && $mProductSales == 0 && $mRoomChangeFee == 0
                        && $mExpense == 0 && $mSalary == 0 && $mProductPurchase == 0 && $mAdvanceRefund == 0
                    ) continue;

                    $monthlyBreakdown[] = [
                        'month'            => $name,
                        'room_booking'     => $mRoomBooking,
                        'monthly_payment'  => $mMonthlyPayment,
                        'product_sales'    => $mProductSales,
                        'room_change_fee'  => $mRoomChangeFee,
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
                    'room_change_fee'   => $roomChangeFee,
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

        $roomChangeYears = RoomChangeHistory::selectRaw('YEAR(created_at) as year')
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

        // ====== INCOME ======
        // 1. Room Booking Advance
        $bookingsInMonth = RoomBookingHistory::whereYear('check_in', $year)
            ->whereMonth('check_in', $month)
            ->get();

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

                if ($totalAdv > 0) {
                    $itemsList[] = [
                        'id'            => $row->id,
                        'name'          => $row->full_name ?? $row->name ?? 'N/A',
                        'phone'         => $row->phone ?? 'N/A',
                        'check_in'      => $row->check_in ? Carbon::parse($row->check_in)->format('d M Y') : 'N/A',
                        'advance_price' => $totalAdv,
                    ];
                }
            }
            return $itemsList;
        };

        $roomBookingsList = $calcRoomBookingAdvanceItems($bookingsInMonth);
        $totalRoomBooking = (float) collect($roomBookingsList)->sum('advance_price');

        // 2. Monthly Payments
        $monthlyPaymentsList = MonthlyPayment::with('booking')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->get()
            ->map(function ($mp) {
                return [
                    'id'             => $mp->id,
                    'resident_name'  => $mp->booking->full_name ?? $mp->booking->name ?? 'N/A',
                    'phone'          => $mp->booking->phone ?? 'N/A',
                    'payment_month'  => $mp->payment_month ?? $mp->months_name ?? 'N/A',
                    'paid_amount'    => (float) $mp->paid_amount,
                    'payment_method' => $mp->payment_method ?? 'N/A',
                    'date'           => $mp->created_at ? $mp->created_at->format('d M Y') : 'N/A',
                ];
            });
        $totalMonthlyPayment = (float) $monthlyPaymentsList->sum('paid_amount');

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

        $totalIncome = $totalRoomBooking + $totalMonthlyPayment + $totalProductSales + $totalRoomChangeFee;

        // ====== COSTS ======
        // 5. General Expenses
        $expenseList = Expense::with('expensetype')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
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
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
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

        // 7. Product Purchase
        $purchaseList = ProductPurchase::with('supplier')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->get()
            ->map(function ($pur) {
                return [
                    'id'            => $pur->id,
                    'memo_number'   => $pur->memo_number ?? 'N/A',
                    'supplier_name' => $pur->supplier->name ?? 'N/A',
                    'product_name'  => $pur->product_name ?? 'N/A',
                    'quantity'      => $pur->quantity ?? 1,
                    'total_price'   => (float) $pur->total_price,
                    'date'          => $pur->purchase_date ? Carbon::parse($pur->purchase_date)->format('d M Y') : ($pur->created_at ? $pur->created_at->format('d M Y') : 'N/A'),
                ];
            });
        $totalProductPurchase = (float) $purchaseList->sum('total_price');

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

        $totalCost = $totalExpense + $totalSalary + $totalProductPurchase + $totalAdvanceRefund;

        return response()->json([
            'status' => true,
            'period' => $monthName . ' ' . $year,
            'year'   => $year,
            'month'  => $month,
            'income' => [
                'room_bookings'    => $roomBookingsList,
                'monthly_payments' => $monthlyPaymentsList,
                'product_sales'    => $productSalesList,
                'room_change_fees' => $roomChangeList,
                'total_income'     => $totalIncome,
            ],
            'cost' => [
                'general_expenses'  => $expenseList,
                'staff_salaries'    => $salaryList,
                'product_purchases' => $purchaseList,
                'advance_refunds'   => $advanceRefundList,
                'total_cost'        => $totalCost,
            ],
            'profit_loss' => $totalIncome - $totalCost,
        ]);
    }
}
