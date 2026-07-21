<?php
namespace App\Http\Controllers\Backend;
use App\Http\Controllers\Controller;
use App\Models\Backend\ExpenseType;
use Illuminate\Http\Request;

class ExpenseTypeController extends Controller
{
    public function index(){
        return view('backend.expense.expensetype');
    }



  public function getexpensetype(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $search  = $request->get('search');

        $query = ExpenseType::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $expenseTypes = $query->latest()->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $expenseTypes->items(),
            'current_page' => $expenseTypes->currentPage(),
            'last_page' => $expenseTypes->lastPage(),
            'total' => $expenseTypes->total(),
            'per_page' => $expenseTypes->perPage(),
            'from' => $expenseTypes->firstItem() ?? 0,
        ]);
    }


  public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:expense_types,name'],
        ]);

        $type = ExpenseType::create([
            'name' => trim($validated['name']),
        ]);

        return response()->json([
            'status'   => 'success',
            'message'  => 'Expense type created successfully.',
            'category' => $type,
        ], 201);
    }







 public function update(Request $request, $id)
    {
        $expenseType = ExpenseType::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:expense_types,name,' . $expenseType->id],
        ]);

        $expenseType->update([
            'name' => trim($validated['name']),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Expense type updated successfully.',
            'data' => $expenseType,
        ]);
    }

    public function destroy($id)
    {
        $expenseType = ExpenseType::findOrFail($id);
        $expenseType->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Expense type deleted successfully.',
        ]);
    }
}