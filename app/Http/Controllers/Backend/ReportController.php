<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Backend\Expense;
use App\Models\Backend\StaffSalaryPayment;
use App\Models\Backend\RoomBookingHistory;
use App\Models\Backend\ProductDistribution;
use App\Models\Backend\ProductPurchase;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{

public function index(){
    return view('backend.report.report');
}

// public function profitLossReport(Request $request)
// {
//     $mode = $request->mode ?? 'monthly'; 
//     $months = [
//         1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',
//         5=>'May',6=>'Jun',7=>'Jul',8=>'Aug',
//         9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec',
//     ];
//     $rows = [];
//     if ($mode === 'monthly') {
//         $year = $request->year ?? date('Y');
//         foreach ($months as $num => $name) {
//             $booking = (float) RoomBookingHistory::whereYear('check_in', $year)
//                 ->whereMonth('check_in', $num)->sum('daybytotalamount');

//             $expense = (float) Expense::whereYear('created_at', $year)
//                 ->whereMonth('created_at', $num)->sum('expense_amount');

//             $salary  = (float) StaffSalaryPayment::whereYear('created_at', $year)
//                 ->whereMonth('created_at', $num)->sum('amount');

//             $cost    = $expense + $salary;

//             $rows[] = [
//                 'label'       => $name . ' ' . $year,
//                 'booking'     => $booking,
//                 'expense'     => $expense,
//                 'salary'      => $salary,
//                 'total_cost'  => $cost,
//                 'profit_loss' => $booking - $cost,
//             ];
//         }

//     } else {
//     $currentYear = (int) date('Y');

//     $oldestYear = min(
//         (int) (RoomBookingHistory::min(DB::raw('YEAR(check_in)')) ?: $currentYear),
//         (int) (Expense::min(DB::raw('YEAR(created_at)')) ?: $currentYear),
//         (int) (StaffSalaryPayment::min(DB::raw('YEAR(created_at)')) ?: $currentYear),
//     );

//     $months = [
//         1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',
//         5=>'May',6=>'Jun',7=>'Jul',8=>'Aug',
//         9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec',
//     ];

//     foreach (range($currentYear, $oldestYear) as $year) {
//         $booking = (float) RoomBookingHistory::whereYear('check_in', $year)->sum('daybytotalamount');
//         $expense = (float) Expense::whereYear('created_at', $year)->sum('expense_amount');
//         $salary  = (float) StaffSalaryPayment::whereYear('created_at', $year)->sum('amount');
//         $cost    = $expense + $salary;

//         if ($booking == 0 && $expense == 0 && $salary == 0) continue;

//         $monthlyBreakdown = [];
//         foreach ($months as $num => $name) {
//             $mBooking = (float) RoomBookingHistory::whereYear('check_in', $year)
//                 ->whereMonth('check_in', $num)->sum('daybytotalamount');
//             $mExpense = (float) Expense::whereYear('created_at', $year)
//                 ->whereMonth('created_at', $num)->sum('expense_amount');
//             $mSalary  = (float) StaffSalaryPayment::whereYear('created_at', $year)
//                 ->whereMonth('created_at', $num)->sum('amount');
//             $mCost    = $mExpense + $mSalary;

//             if ($mBooking == 0 && $mExpense == 0 && $mSalary == 0) continue;

//             $monthlyBreakdown[] = [
//                 'month'       => $name,
//                 'booking'     => $mBooking,
//                 'expense'     => $mExpense,
//                 'salary'      => $mSalary,
//                 'total_cost'  => $mCost,
//                 'profit_loss' => $mBooking - $mCost,
//             ];
//         }

//         $rows[] = [
//             'label'             => (string) $year,
//             'booking'           => $booking,
//             'expense'           => $expense,
//             'salary'            => $salary,
//             'total_cost'        => $cost,
//             'profit_loss'       => $booking - $cost,
//             'monthly_breakdown' => $monthlyBreakdown,
//         ];
//     }
// }
//     return response()->json([
//         'status' => true,
//         'mode'   => $mode,
//         'data'   => $rows,
//     ]);
// }



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
            $booking = (float) RoomBookingHistory::whereYear('check_in', $year)
                ->whereMonth('check_in', $num)->sum('daybytotalamount');

            $expense = (float) Expense::whereYear('created_at', $year)
                ->whereMonth('created_at', $num)->sum('expense_amount');

            $salary = (float) StaffSalaryPayment::whereYear('created_at', $year)
                ->whereMonth('created_at', $num)->sum('amount');

            $product = (float) ProductDistribution::whereYear('created_at', $year)
                ->whereMonth('created_at', $num)->sum('total_price_available');

            $cost = $expense + $salary + $product;

            $rows[] = [
                'label'       => $name . ' ' . $year,
                'booking'     => $booking,
                'expense'     => $expense,
                'salary'      => $salary,
                'product'     => $product,
                'total_cost'  => $cost,
                'profit_loss' => $booking - $cost,
            ];
        }

    } else {
        $currentYear = (int) date('Y');

        $oldestYear = min(
            (int) (RoomBookingHistory::min(DB::raw('YEAR(check_in)')) ?: $currentYear),
            (int) (Expense::min(DB::raw('YEAR(created_at)')) ?: $currentYear),
            (int) (StaffSalaryPayment::min(DB::raw('YEAR(created_at)')) ?: $currentYear),
            (int) (ProductDistribution::min(DB::raw('YEAR(created_at)')) ?: $currentYear),
        );

        foreach (range($currentYear, $oldestYear) as $year) {
            $booking = (float) RoomBookingHistory::whereYear('check_in', $year)->sum('daybytotalamount');
            $expense = (float) Expense::whereYear('created_at', $year)->sum('expense_amount');
            $salary  = (float) StaffSalaryPayment::whereYear('created_at', $year)->sum('amount');
            $product = (float) ProductDistribution::whereYear('created_at', $year)->sum('total_price_available');
            $cost    = $expense + $salary + $product;

            if ($booking == 0 && $expense == 0 && $salary == 0 && $product == 0) continue;

            // Monthly breakdown
            $monthlyBreakdown = [];
            foreach ($months as $num => $name) {
                $mBooking = (float) RoomBookingHistory::whereYear('check_in', $year)->whereMonth('check_in', $num)->sum('daybytotalamount');
                $mExpense = (float) Expense::whereYear('created_at', $year)->whereMonth('created_at', $num)->sum('expense_amount');
                $mSalary  = (float) StaffSalaryPayment::whereYear('created_at', $year)->whereMonth('created_at', $num)->sum('amount');
                $mProduct = (float) ProductDistribution::whereYear('created_at', $year)->whereMonth('created_at', $num)->sum('total_price_available');
                $mCost    = $mExpense + $mSalary + $mProduct;

                if ($mBooking == 0 && $mExpense == 0 && $mSalary == 0 && $mProduct == 0) continue;

                $monthlyBreakdown[] = [
                    'month'       => $name,
                    'booking'     => $mBooking,
                    'expense'     => $mExpense,
                    'salary'      => $mSalary,
                    'product'     => $mProduct,
                    'total_cost'  => $mCost,
                    'profit_loss' => $mBooking - $mCost,
                ];
            }

            $rows[] = [
                'label'             => (string) $year,
                'booking'           => $booking,
                'expense'           => $expense,
                'salary'            => $salary,
                'product'           => $product,
                'total_cost'        => $cost,
                'profit_loss'       => $booking - $cost,
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

    $porductdistribution = ProductDistribution::selectRaw('YEAR(created_at) as year')
        ->groupBy('year')
        ->pluck('year')
        ->map(fn($y) => (int)$y)
        ->toArray();


    $allYears = array_unique(array_merge(
        [$currentYear],
        $bookingYears,
        $expenseYears,
        $salaryYears,
        $porductdistribution
    ));

    rsort($allYears); 

    return response()->json([
        'status' => true,
        'years'  => array_values($allYears),
    ]);
}


}

