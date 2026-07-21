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

        $today = now();
        $advanceMonth = $today->month;
        $advanceYear  = $today->year;
        $fullPeriod = $today->copy()->subMonth();
        $fullMonth  = $fullPeriod->month;
        $fullYear   = $fullPeriod->year;
        $currentAdvanceTotal = StaffSalaryPayment::where('staff_id', $staff->id)
            ->where('salary_month', $advanceMonth)
            ->where('salary_year', $advanceYear)
            ->where('payment_type', 'advance')
            ->sum('amount');
        $currentAdvanceCount = StaffSalaryPayment::where('staff_id', $staff->id)
            ->where('salary_month', $advanceMonth)
            ->where('salary_year', $advanceYear)
            ->where('payment_type', 'advance')
            ->count();
        $previousAdvanceTotal = StaffSalaryPayment::where('staff_id', $staff->id)
            ->where('salary_month', $fullMonth)
            ->where('salary_year', $fullYear)
            ->where('payment_type', 'advance')
            ->sum('amount');
        $fullPaid = StaffSalaryPayment::where('staff_id', $staff->id)
            ->where('salary_month', $fullMonth)
            ->where('salary_year', $fullYear)
            ->whereIn('payment_type', ['full', 'net_payable'])
            ->exists();

        return response()->json([
            'advance_summary' => [
                'salary_month'     => $advanceMonth,
                'salary_year'      => $advanceYear,
                'monthly_salary'   => (float) $staff->salary,
                'total_advance'    => (float) $currentAdvanceTotal,
                'advance_count'    => $currentAdvanceCount,
                'remaining_salary' => max(0, (float) $staff->salary - (float) $currentAdvanceTotal),
            ],
            'full_summary' => [
                'salary_month'   => $fullMonth,
                'salary_year'    => $fullYear,
                'monthly_salary' => (float) $staff->salary,
                'total_advance'  => (float) $previousAdvanceTotal,
                'net_payable'    => max(0, (float) $staff->salary - (float) $previousAdvanceTotal),
                'full_paid'      => $fullPaid, 
            ],
        ]);
    }

    public function storeAdvance(Request $request)
    {
        $validated = $request->validate([
            'staff_id'     => ['required', 'exists:staffs,id'],
            'payment_date' => ['required', 'date'],
            'amount'       => ['required', 'numeric', 'min:1'],
            'note'         => ['nullable', 'string'],
        ]);

        $staff = Staffs::findOrFail($validated['staff_id']);
        $date  = Carbon::parse($validated['payment_date']);

        $month = $date->month;
        $year  = $date->year;

        $advanceCount = StaffSalaryPayment::where('staff_id', $staff->id)
            ->where('salary_month', $month)
            ->where('salary_year', $year)
            ->where('payment_type', 'advance')
            ->count();

        if ($advanceCount >= 3) {
            return response()->json([
                'message' => 'This staff already received advance salary 3 times this month.'
            ], 422);
        }

        //  full  net_payable  advance block
        $fullPaid = StaffSalaryPayment::where('staff_id', $staff->id)
            ->where('salary_month', $month)
            ->where('salary_year', $year)
            ->whereIn('payment_type', ['full', 'net_payable'])
            ->exists();

        if ($fullPaid) {
            return response()->json([
                'message' => 'Full salary already paid for this month. Advance not allowed now.'
            ], 422);
        }

        $totalAdvance = StaffSalaryPayment::where('staff_id', $staff->id)
            ->where('salary_month', $month)
            ->where('salary_year', $year)
            ->where('payment_type', 'advance')
            ->sum('amount');

        $newAdvanceTotal = (float) $totalAdvance + (float) $validated['amount'];

        if ($newAdvanceTotal > (float) $staff->salary) {
            return response()->json([
                'message' => 'Advance amount exceeds monthly salary.'
            ], 422);
        }

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
            'message' => 'Advance salary saved successfully.',
            'payment' => $payment,
        ]);
    }

    public function storeFull(Request $request)
    {
        $validated = $request->validate([
            'staff_id'     => ['required', 'exists:staffs,id'],
            'payment_date' => ['required', 'date'],
            'note'         => ['nullable', 'string'],
        ]);

        $staff       = Staffs::findOrFail($validated['staff_id']);
        $paymentDate = Carbon::parse($validated['payment_date']);
        $salaryPeriod = $paymentDate->copy()->subMonth();
        $month       = $salaryPeriod->month;
        $year        = $salaryPeriod->year;

        $fullPaid = StaffSalaryPayment::where('staff_id', $staff->id)
            ->where('salary_month', $month)
            ->where('salary_year', $year)
            ->whereIn('payment_type', ['full', 'net_payable'])
            ->exists();

        if ($fullPaid) {
            return response()->json([
                'message' => 'Full salary already paid for the target month.'
            ], 422);
        }

        $totalAdvance = StaffSalaryPayment::where('staff_id', $staff->id)
            ->where('salary_month', $month)
            ->where('salary_year', $year)
            ->where('payment_type', 'advance')
            ->sum('amount');

        $netPayable = max(0, (float) $staff->salary - (float) $totalAdvance);

        $payment = StaffSalaryPayment::create([
            'staff_id'     => $staff->id,
            'salary_month' => $month,
            'salary_year'  => $year,
            'payment_type' => $totalAdvance > 0 ? 'net_payable' : 'full',
            'amount'       => $netPayable,
            'payment_date' => $validated['payment_date'],
            'note'         => $validated['note'] ?? null,
            'created_by'   => Auth::id(),
            'status'       => 1,
        ]);

        return response()->json([
            'message'        => 'Full salary paid successfully.',
            'payment'        => $payment,
            'salary_month'   => $month,
            'salary_year'    => $year,
            'monthly_salary' => (float) $staff->salary,
            'total_advance'  => (float) $totalAdvance,
            'net_payable'    => (float) $netPayable,
        ]);
    }


    public function history(Request $request, $staffId)
    {
        $today = now();
        $currentMonth = $today->month;
        $currentYear  = $today->year;
        $previousMonthDate = $today->copy()->subMonth();
        $previousMonth     = $previousMonthDate->month;
        $previousYear      = $previousMonthDate->year;
        $fullPaid = StaffSalaryPayment::where('staff_id', $staffId)
            ->where('salary_month', $previousMonth)
            ->where('salary_year', $previousYear)
            ->whereIn('payment_type', ['full', 'net_payable'])
            ->exists();
        $query = StaffSalaryPayment::with('staff:id,name,employee_id')
            ->where('staff_id', $staffId);
        if ($fullPaid) {
            $query->where('salary_month', $currentMonth)
                  ->where('salary_year', $currentYear)
                  ->where('payment_type', 'advance');
        } else {
            $query->where('salary_month', $previousMonth)
                  ->where('salary_year', $previousYear);
        }
        $items = $query->orderBy('payment_date', 'asc')
                       ->orderBy('id', 'asc')
                       ->get();
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
    $payments = StaffSalaryPayment::with([
            'staff:id,name,employee_id,phone,salary',
            'user:id,name',
        ])
        ->where(function ($q) use ($groups) {
            foreach ($groups as $g) {
                $q->orWhere(function ($sub) use ($g) {
                    $sub->where('staff_id', $g->staff_id)
                        ->where('salary_month', $g->salary_month)
                        ->where('salary_year', $g->salary_year);
                });
            }
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
        ->orderBy('payment_date', 'asc')
        ->orderBy('id', 'asc')
        ->get();

    $grouped = $payments->groupBy(fn($p) => $p->staff_id . '-' . $p->salary_month . '-' . $p->salary_year);
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
            'payment_date' => $r->payment_date ? $r->payment_date->format('Y-m-d') : null,
            'payment_type' => $r->payment_type,
            'amount'       => (float) $r->amount,
            'note'         => $r->note,
            'created_by'   => $r->user ? $r->user->name : 'N/A',
        ])->values();
        $monthlySalary = $first->staff ? (float) $first->staff->salary : 0;
        $advanceTotal  = $rows->where('payment_type', 'advance')->sum(fn($r) => (float) $r->amount);
        $displayTypes  = $rows->pluck('payment_type')->unique()->values();
        $displayAmount = max(0, $monthlySalary - $advanceTotal);

        return [
            'group_key'     => $key,
            'staff_id'      => $g->staff_id,
            'salary_month'  => $g->salary_month,
            'salary_year'   => $g->salary_year,
            'month_label'   => $monthLabel,
                'create_at_date' => $first->created_at ? $first->created_at->format('d-m-Y') : '—', 

            'staff'         => $first->staff ? [
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