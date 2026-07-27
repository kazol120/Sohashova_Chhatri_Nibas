<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Backend\ExpenseType;
use App\Models\Backend\Expense;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;


class ExpenseController extends Controller
{

    public function index(){
        return view('backend.expense.expense');
    }


 



    public function getAll()
    {
        $categories = ExpenseType::select('id', 'name')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $categories,
        ]);
    }







    public function getexpensecategory(){

           $categories = ExpenseType::select('id', 'name')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $categories,
        ]);
    }



    public function store(Request $request)
    {
        // Support bulk/multiple expenses submission
        if ($request->has('expenses') && is_array($request->expenses)) {
            $validated = $request->validate([
                'date'                        => ['required', 'date'],
                'expenses'                    => ['required', 'array', 'min:1'],
                'expenses.*.expense_category' => ['required', 'exists:expense_types,id'],
                'expenses.*.expense_note'     => ['nullable', 'string', 'max:1000'],
                'expenses.*.expense_amount'   => ['required', 'numeric', 'min:0.01'],
            ]);

            $createdExpenses = [];
            \Illuminate\Support\Facades\DB::transaction(function () use ($validated, &$createdExpenses) {
                foreach ($validated['expenses'] as $item) {
                    $createdExpenses[] = Expense::create([
                        'date'             => $validated['date'],
                        'expense_category' => $item['expense_category'],
                        'expense_note'     => $item['expense_note'] ?? null,
                        'expense_amount'   => $item['expense_amount'],
                    ]);
                }
            });

            return response()->json([
                'status'   => 'success',
                'message'  => 'Expenses saved successfully.',
                'expenses' => $createdExpenses,
            ]);
        }

        // Single expense submission fallback
        $validated = $request->validate([
            'date'             => ['required', 'date'],
            'expense_category' => ['required', 'exists:expense_types,id'],
            'expense_note'     => ['nullable', 'string', 'max:1000'],
            'expense_amount'   => ['required', 'numeric', 'min:0.01'],
        ]);

        $expense = Expense::create([
            'date'             => $validated['date'],
            'expense_category' => $validated['expense_category'],
            'expense_note'     => $validated['expense_note'] ?? null,
            'expense_amount'   => $validated['expense_amount'],
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Expense saved successfully.',
            'expense' => $expense,
        ]);
    }


   public function getexpenselilst(Request $request)
    {
        $perPage    = $request->input('per_page', 10);
        $search     = $request->input('search', '');
        $startDate  = $request->input('start_date', '');
        $endDate    = $request->input('end_date', '');
        $categoryId = $request->input('category_id', '');

        $query = Expense::with('expensetype')
            ->when($search, function ($q) use ($search) {
                $q->where('expense_note', 'like', "%{$search}%");
            })
            ->when(!empty($categoryId), function ($q) use ($categoryId) {
                $q->where('expense_category', $categoryId);
            })
            ->when(!empty($startDate) && !empty($endDate), function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate]);
            })
            ->when(!empty($startDate) && empty($endDate), function ($q) use ($startDate) {
                $q->whereDate('date', '>=', $startDate);
            })
            ->when(empty($startDate) && !empty($endDate), function ($q) use ($endDate) {
                $q->whereDate('date', '<=', $endDate);
            })
            ->orderBy('id', 'desc');

        $grandTotal = (clone $query)->sum('expense_amount');

        $expenses = $query->paginate($perPage);

        return response()->json([
            'status'       => 'success',
            'expenses'     => $expenses->items(),
            'total'        => $expenses->total(),
            'from'         => $expenses->firstItem() ?? 1,
            'per_page'     => $expenses->perPage(),
            'last_page'    => $expenses->lastPage(),
            'current_page' => $expenses->currentPage(),
            'grand_total'  => $grandTotal, 
        ]);
    }


    public function getTodayExpenses(Request $request) {
    $perPage = $request->input('per_page', 50);
    $search  = $request->input('search', '');

    $query = Expense::with('expensetype')
        ->whereDate('date', Carbon::today())
        ->when($search, function ($q) use ($search) {
            $q->where('expense_note', 'like', "%{$search}%");
        })
        ->orderBy('id', 'desc');

    $grandTotal = (clone $query)->sum('expense_amount');
    $expenses   = $query->paginate($perPage);

    return response()->json([
        'status'       => 'success',
        'expenses'     => $expenses->items(),
        'total'        => $expenses->total(),
        'from'         => $expenses->firstItem() ?? 1,
        'per_page'     => $expenses->perPage(),
        'last_page'    => $expenses->lastPage(),
        'current_page' => $expenses->currentPage(),
        'grand_total'  => $grandTotal,
    ]);
}



     public function update(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);

        $validated = $request->validate([
            'date'             => ['required', 'date'],
            'expense_category' => ['required', 'exists:expense_types,id'],
            'expense_note'     => ['nullable', 'string', 'max:1000'],
            'expense_amount'   => ['required', 'numeric', 'min:0.01'],
        ]);

        $expense->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Expense updated successfully.',
            'expense' => $expense->load('expensetype'),
        ]);
    }


    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Expense deleted successfully.',
        ]);
    }




}
