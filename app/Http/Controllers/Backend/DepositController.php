<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\DepositService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DepositController extends Controller
{
    protected $depositService;
    protected $userService;

    public function __construct(DepositService $depositService, UserService $userService)
    {
        $this->depositService = $depositService;
        $this->userService = $userService;
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $data['page_title'] = 'Deposit List';

        $selectedMonth = $request->month
            ? Carbon::parse($request->month . '-01')->format('Y-m')
            : now()->format('Y-m');

        $data['selectedMonth'] = $selectedMonth;
        $data['deposits'] = $this->depositService->monthlyDepositList($selectedMonth);

        $data['summary'] = [
            'total_member' => $data['deposits']->count(),
            'total_deposit' => $data['deposits']->sum('total_deposit'),
        ];
        $data['users'] = $this->userService->usersWithRole();

        return view('backend.deposit.index', $data);
    }

    public function create()
    {
        $data['page_title'] = 'Create Deposit';
        $data['users'] = $this->userService->usersWithRole();
        return view('backend.deposit.create', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
        ]);

        $response = $this->depositService->depositCreate($request);

        if ($response['success']) {
            return redirect()->back()->with('success', $response['message']);
        }

        return back()->withInput()->withErrors(['error' => $response['message']]);
    }

    public function show($id)
    {
        $data['page_title'] = 'Deposit Details';
        $data['deposits'] = $this->depositService->depositDetails($id);
        $data['users'] = $this->userService->user();
        return view('backend.deposit.show', $data);
    }

    public function depositHistory(Request $request)
    {
        $request->validate([
            'user_id'       => 'required|exists:users,id',
            'selectedMonth' => 'required|date_format:Y-m'
        ]);

        $result = $this->depositService->getUserMonthlyHistory(
            $request->user_id,
            $request->selectedMonth
        );

        return view('backend.deposit.show', [
            'page_title' => 'Deposit History - ' . $result['user']->name,
            'user'       => $result['user'],
            'deposits'    => $result['history'],
            'summary'    => $result['summary'],
            'month'      => $request->selectedMonth
        ]);
    }

    public function destroy($id)
    {
        $this->depositService->depositDelete($id);
        return redirect()->back()->with('success', 'Deposit deleted successfully');
    }
}
