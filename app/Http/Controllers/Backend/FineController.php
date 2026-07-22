<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\FineService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FineController extends Controller
{
    protected $fineService;
    protected $userService;

    public function __construct(FineService $fineService, UserService $userService)
    {
        $this->fineService = $fineService;
        $this->userService = $userService;
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $data['page_title'] = 'Monthly Fine List';

        $selectedMonth = $request->month
            ? \Carbon\Carbon::parse($request->month)->format('Y-m')
            : now()->format('Y-m');

        if ($selectedMonth > now()->format('Y-m')) {
            $selectedMonth = now()->format('Y-m');
        }

        $data['selectedMonth'] = $selectedMonth;
        $data['fines'] = $this->fineService->monthlyWiseFineList($selectedMonth);
        $data['users'] = $this->userService->usersWithRole();

        return view('backend.fine.index', $data);
    }

    public function create(Request $request)
    {
        $data['page_title'] = 'Create Fine';
        $data['users'] = $this->userService->usersWithRole();
        return view('backend.fine.create', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'users'             => 'required|array|min:1',
            'users.*'           => 'exists:users,id',
            'date'              => 'required|date',
            'type'              => 'required|in:bazar_fine,other_fine',
            'amount'            => 'required|numeric|min:0',
            'note'              => 'nullable|string|max:255',
            'beneficiary_users' => 'nullable|array|max:2',
        ]);
        $request->merge(['imposed_by' => Auth::id()]);

        $response = $this->fineService->fineStore($request->all());

        if ($response['success']) {
            return redirect()->route('fines.index')->with('success', $response['message']);
        }

        return back()->withInput()->withErrors(['error' => $response['message']]);
    }

    public function destroy($id)
    {
        $this->fineService->fineDelete($id);
        return redirect()->back()->with('success', 'Fine deleted successfully');
    }
}
