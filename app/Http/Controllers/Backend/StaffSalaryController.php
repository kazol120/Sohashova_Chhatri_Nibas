<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Backend\Staffs;
use App\Models\User;
use App\Models\Backend\StaffSalaryPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class StaffSalaryController extends Controller
{

    public function index()
    {
        return view('backend.staffs.staffsalary');
    }

    public function searchStaff(Request $request)
    {
        $query = trim((string) $request->input('query', ''));

        $staffs = Staffs::query()
            ->when($query !== '', function ($q) use ($query) {
                $q->where(function ($subQuery) use ($query) {
                    $subQuery->where('name', 'like', "%{$query}%")
                        ->orWhere('employee_id', 'like', "%{$query}%")
                        ->orWhere('phone', 'like', "%{$query}%");
                });
            })
            ->limit(20)
            ->get([
                'id',
                'employee_id',
                'name',
                'phone',
                'email',
                'designation',
                'department',
                'salary',
                'joining_date',
                'status',
            ]);

        return response()->json($staffs);
    }


    public function summary(Request $request, $staffId)
    {
        $staff = Staffs::findOrFail($staffId);

        $selectedMonth = (int) $request->input('month', now()->month);
        $selectedYear  = (int) $request->input('year', now()->year);

        // Total advance taken by this staff ever
        $totalAdvanceGiven = StaffSalaryPayment::where('staff_id', $staff->id)
            ->where('payment_type', 'advance')
            ->sum('amount');

        // Total advance deducted across previous salary payments
        $totalAdvanceDeducted = StaffSalaryPayment::where('staff_id', $staff->id)
            ->whereIn('payment_type', ['full', 'net_payable'])
            ->sum('advance_deducted');

        // Current unadjusted advance balance
        $unadjustedAdvanceBalance = max(0, (float) $totalAdvanceGiven - (float) $totalAdvanceDeducted);

        // Check if salary for selected month is already paid
        $paidRecord = StaffSalaryPayment::where('staff_id', $staff->id)
            ->where('salary_month', $selectedMonth)
            ->where('salary_year', $selectedYear)
            ->whereIn('payment_type', ['full', 'net_payable'])
            ->first();

        $advanceCount = StaffSalaryPayment::where('staff_id', $staff->id)
            ->where('payment_type', 'advance')
            ->count();

        if ($paidRecord) {
            return response()->json([
                'salary_month'              => $selectedMonth,
                'salary_year'               => $selectedYear,
                'monthly_salary'            => (float) $staff->salary,
                'total_advance_balance'     => (float) $unadjustedAdvanceBalance,
                'advance_count'             => $advanceCount,
                'is_paid'                   => true,
                'paid_amount'               => (float) $paidRecord->amount,
                'advance_deducted'          => (float) $paidRecord->advance_deducted,
                'net_payable'               => (float) $paidRecord->amount,
                'default_deduct_amount'     => 0,
            ]);
        }

        // Suggested deduction: up to monthly salary or total advance balance
        $suggestedDeduction = min((float) $staff->salary, (float) $unadjustedAdvanceBalance);
        $netPayable = max(0, (float) $staff->salary - (float) $suggestedDeduction);

        return response()->json([
            'salary_month'              => $selectedMonth,
            'salary_year'               => $selectedYear,
            'monthly_salary'            => (float) $staff->salary,
            'total_advance_balance'     => (float) $unadjustedAdvanceBalance,
            'advance_count'             => $advanceCount,
            'is_paid'                   => false,
            'paid_amount'               => 0,
            'advance_deducted'          => 0,
            'default_deduct_amount'     => (float) $suggestedDeduction,
            'net_payable'               => (float) $netPayable,
        ]);
    }

    public function storeAdvance(Request $request)
    {
        $validated = $request->validate([
            'staff_id'     => ['required', 'exists:staffs,id'],
            'payment_date' => ['required', 'date'],
            'salary_month' => ['required', 'integer', 'between:1,12'],
            'salary_year'  => ['required', 'integer'],
            'amount'       => ['required', 'numeric', 'min:1'],
            'note'         => ['nullable', 'string'],
        ]);

        $staff = Staffs::findOrFail($validated['staff_id']);
        $month = (int) $validated['salary_month'];
        $year  = (int) $validated['salary_year'];

        $targetMonthName = Carbon::createFromDate($year, $month, 1)->format('F Y');

        $payment = StaffSalaryPayment::create([
            'staff_id'     => $staff->id,
            'salary_month' => $month,
            'salary_year'  => $year,
            'payment_type' => 'advance',
            'amount'       => $validated['amount'],
            'payment_date' => $validated['payment_date'],
            'note'         => $validated['note'] ?? null,
            'created_by'   => Auth::id(),
            'status'       => 1,
        ]);

        return response()->json([
            'message' => "Advance of {$validated['amount']} ৳ added to advance balance successfully.",
            'payment' => $payment,
        ]);
    }

    public function storeFull(Request $request)
    {
        $validated = $request->validate([
            'staff_id'              => ['required', 'exists:staffs,id'],
            'payment_date'          => ['required', 'date'],
            'salary_month'          => ['required', 'integer', 'between:1,12'],
            'salary_year'           => ['required', 'integer'],
            'advance_deduct_amount' => ['nullable', 'numeric', 'min:0'],
            'note'                  => ['nullable', 'string'],
        ]);

        $staff = Staffs::findOrFail($validated['staff_id']);
        $month = (int) $validated['salary_month'];
        $year  = (int) $validated['salary_year'];

        $fullPaid = StaffSalaryPayment::where('staff_id', $staff->id)
            ->where('salary_month', $month)
            ->where('salary_year', $year)
            ->whereIn('payment_type', ['full', 'net_payable'])
            ->exists();

        $monthName = Carbon::createFromDate($year, $month, 1)->format('F Y');

        if ($fullPaid) {
            return response()->json([
                'message' => "Salary for {$monthName} is already paid."
            ], 422);
        }

        // Total advance taken ever
        $totalAdvanceGiven = StaffSalaryPayment::where('staff_id', $staff->id)
            ->where('payment_type', 'advance')
            ->sum('amount');

        // Total advance deducted in past salary payments
        $totalAdvanceDeducted = StaffSalaryPayment::where('staff_id', $staff->id)
            ->whereIn('payment_type', ['full', 'net_payable'])
            ->sum('advance_deducted');

        $unadjustedAdvanceBalance = max(0, (float) $totalAdvanceGiven - (float) $totalAdvanceDeducted);

        // Requested deduction amount
        $reqDeduct = isset($validated['advance_deduct_amount']) ? (float) $validated['advance_deduct_amount'] : $unadjustedAdvanceBalance;
        $actualDeduct = min((float) $staff->salary, min((float) $unadjustedAdvanceBalance, max(0, $reqDeduct)));

        $netPayable = max(0, (float) $staff->salary - (float) $actualDeduct);

        $payment = StaffSalaryPayment::create([
            'staff_id'         => $staff->id,
            'salary_month'     => $month,
            'salary_year'      => $year,
            'payment_type'     => $actualDeduct > 0 ? 'net_payable' : 'full',
            'amount'           => $netPayable,
            'advance_deducted' => $actualDeduct,
            'gross_salary'     => $staff->salary,
            'payment_date'     => $validated['payment_date'],
            'note'             => $validated['note'] ?? null,
            'created_by'       => Auth::id(),
            'status'           => 1,
        ]);

        $remainingAdvanceAfter = max(0, (float) $unadjustedAdvanceBalance - (float) $actualDeduct);

        return response()->json([
            'message'                  => "Salary for {$monthName} paid successfully. Net Paid: {$netPayable} ৳. Remaining Advance: {$remainingAdvanceAfter} ৳.",
            'payment'                  => $payment,
            'salary_month'             => $month,
            'salary_year'              => $year,
            'monthly_salary'           => (float) $staff->salary,
            'advance_deducted'         => (float) $actualDeduct,
            'net_payable'              => (float) $netPayable,
            'remaining_advance_balance'=> (float) $remainingAdvanceAfter,
        ]);
    }

    public function history(Request $request, $staffId)
    {
        $allPayments = StaffSalaryPayment::with('staff:id,name,employee_id')
            ->where('staff_id', $staffId)
            ->orderBy('salary_year', 'asc')
            ->orderBy('salary_month', 'asc')
            ->orderBy('payment_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Total advance deducted across all salary payments
        $totalDeductedPool = (float) $allPayments
            ->whereIn('payment_type', ['full', 'net_payable'])
            ->sum('advance_deducted');

        $remainingDeductPool = $totalDeductedPool;

        // Attribute adjustments to advance entries in FIFO order
        foreach ($allPayments as $payment) {
            if ($payment->payment_type === 'advance') {
                $orig = (float) $payment->amount;
                $deductedFromThis = min($orig, $remainingDeductPool);
                $remainingDeductPool -= $deductedFromThis;
                $rem = max(0, $orig - $deductedFromThis);

                $payment->original_amount = $orig;
                $payment->advance_adjusted = $deductedFromThis;
                $payment->remaining_amount = $rem;
            } else {
                $payment->gross_salary = (float) ($payment->gross_salary ?: ((float) $payment->amount + (float) ($payment->advance_deducted ?? 0)));
                $payment->advance_deducted = (float) ($payment->advance_deducted ?? 0);
                $payment->net_paid = (float) $payment->amount;
            }
        }

        // Return sorted latest first (limit 20)
        $items = $allPayments->sortByDesc(function ($p) {
            $pDate = $p->payment_date ? (is_string($p->payment_date) ? $p->payment_date : $p->payment_date->format('Y-m-d')) : '0000-00-00';
            return sprintf('%04d-%02d-%s-%010d', (int) $p->salary_year, (int) $p->salary_month, $pDate, (int) $p->id);
        })->values()->take(20);

        return response()->json($items);
    }

    public function getstaffsalary(Request $request)
    {
        $perPage   = (int) $request->input('per_page', 10);
        $page      = (int) $request->input('page', 1);
        $search    = trim((string) $request->input('search', ''));
        $startDate = $request->input('start_date', '');
        $endDate   = $request->input('end_date', '');
        $staffId   = $request->input('staff_id', '');

        $groupQuery = StaffSalaryPayment::query()
            ->select('staff_id', 'salary_month', 'salary_year')
            ->when($search !== '', function ($q) use ($search) {
                $q->whereHas('staff', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when(!empty($staffId), function ($q) use ($staffId) {  
                $q->where('staff_id', $staffId);                      
            })                                                        
            ->when(!empty($startDate), function ($q) use ($startDate) {
                $q->whereDate('payment_date', '>=', $startDate);
            })
            ->when(!empty($endDate), function ($q) use ($endDate) {
                $q->whereDate('payment_date', '<=', $endDate);
            })
            ->groupBy('staff_id', 'salary_month', 'salary_year')
            ->orderBy('salary_year', 'desc')
            ->orderBy('salary_month', 'desc')
            ->orderBy('staff_id', 'desc');

        $paginated = $groupQuery->paginate($perPage, ['staff_id', 'salary_month', 'salary_year'], 'page', $page);
        $groups = collect($paginated->items());

        $staffIds = $groups->pluck('staff_id')->unique()->filter()->values();

        // Fetch all payments for these staffs to compute exact FIFO adjustments
        $allStaffPayments = StaffSalaryPayment::with([
                'staff:id,name,employee_id,phone,salary',
                'user:id,name',
            ])
            ->whereIn('staff_id', $staffIds)
            ->orderBy('salary_year', 'asc')
            ->orderBy('salary_month', 'asc')
            ->orderBy('payment_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Calculate FIFO for each staff
        $paymentsByStaff = $allStaffPayments->groupBy('staff_id');
        $processedPayments = collect();

        foreach ($paymentsByStaff as $sId => $sPayments) {
            $deductPool = (float) $sPayments
                ->whereIn('payment_type', ['full', 'net_payable'])
                ->sum('advance_deducted');

            foreach ($sPayments as $p) {
                if ($p->payment_type === 'advance') {
                    $orig = (float) $p->amount;
                    $deductedFromThis = min($orig, $deductPool);
                    $deductPool -= $deductedFromThis;
                    $rem = max(0, $orig - $deductedFromThis);

                    $p->original_amount = $orig;
                    $p->advance_adjusted = $deductedFromThis;
                    $p->remaining_amount = $rem;
                } else {
                    $p->gross_salary = (float) ($p->gross_salary ?: ((float) $p->amount + (float) ($p->advance_deducted ?? 0)));
                    $p->advance_deducted = (float) ($p->advance_deducted ?? 0);
                    $p->net_paid = (float) $p->amount;
                }
                $processedPayments->push($p);
            }
        }

        $grouped = $processedPayments->groupBy(fn($p) => $p->staff_id . '-' . $p->salary_month . '-' . $p->salary_year);

        $items = $groups->map(function ($g) use ($grouped) {
            $key  = $g->staff_id . '-' . $g->salary_month . '-' . $g->salary_year;
            $rows = $grouped->get($key, collect());
            if ($rows->isEmpty()) return null;
            $first = $rows->first();
            $monthLabel = '';
            if ($g->salary_month && $g->salary_year) {
                $monthLabel = \Carbon\Carbon::createFromDate($g->salary_year, $g->salary_month, 1)
                    ->format('F Y');
            }
            $paymentLines = $rows->map(fn($r) => [
                'id'               => $r->id,
                'payment_date'     => $r->payment_date ? (is_string($r->payment_date) ? $r->payment_date : $r->payment_date->format('Y-m-d')) : null,
                'payment_type'     => $r->payment_type,
                'amount'           => (float) $r->amount,
                'original_amount'  => (float) ($r->original_amount ?? $r->amount),
                'advance_adjusted' => (float) ($r->advance_adjusted ?? 0),
                'remaining_amount' => (float) ($r->remaining_amount ?? $r->amount),
                'gross_salary'     => (float) ($r->gross_salary ?? 0),
                'advance_deducted' => (float) ($r->advance_deducted ?? 0),
                'note'             => $r->note,
                'created_by'       => $r->user ? $r->user->name : 'N/A',
            ])->values();
            $monthlySalary = $first->staff ? (float) $first->staff->salary : 0;
            $advanceTotal  = $rows->where('payment_type', 'advance')->sum(fn($r) => (float) $r->amount);
            $displayTypes  = $rows->pluck('payment_type')->unique()->values();
            $displayAmount = max(0, $monthlySalary - $advanceTotal);

            return [
                'group_key'      => $key,
                'staff_id'       => $g->staff_id,
                'salary_month'   => $g->salary_month,
                'salary_year'    => $g->salary_year,
                'month_label'    => $monthLabel,
                'create_at_date' => $first->created_at ? $first->created_at->format('d-m-Y') : '—', 
                'staff'          => $first->staff ? [
                    'id'             => $first->staff->id,
                    'name'           => $first->staff->name,
                    'employee_id'    => $first->staff->employee_id,
                    'phone'          => $first->staff->phone,
                    'monthly_salary' => (float) $first->staff->salary,
                ] : null,
                'payment_types'  => $displayTypes,
                'payments'       => $paymentLines,
                'display_amount' => $displayAmount,
                'total_amount'   => $monthlySalary,
                'created_by'     => $first->user ? $first->user->name : 'N/A',
            ];
        })->filter()->values();

        return response()->json([
            'data'         => $items,
            'current_page' => $paginated->currentPage(),
            'last_page'    => $paginated->lastPage(),
            'total'        => $paginated->total(),
            'from'         => $paginated->firstItem() ?? 1,
        ]);
    }
}