<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Backend\Deposit;
use App\Models\User;
use App\Services\MealService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepositRefundController extends Controller
{
    protected $mealService;
    protected $userService;

    public function __construct(MealService $mealService, UserService $userService)
    {
        $this->mealService = $mealService;
        $this->userService = $userService;
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $data['page_title'] = 'Deposit Refund (মিল ডিপোজিট ফেরত)';

        $selectedMonth = $request->month
            ? Carbon::parse($request->month . '-01')->format('Y-m')
            : now()->format('Y-m');

        $data['selectedMonth'] = $selectedMonth;

        $startDate = Carbon::parse($selectedMonth . '-01')->startOfMonth()->format('Y-m-d');
        $endDate   = Carbon::parse($selectedMonth . '-01')->endOfMonth()->format('Y-m-d');

        // Fetch refund records (amount < 0)
        $refunds = Deposit::with(['user:id,name,phone,email,status', 'madeBy:id,name'])
            ->where('amount', '<', 0)
            ->whereBetween('date', [$startDate, $endDate])
            ->latest('date')
            ->latest('id')
            ->get();

        // Attach room booking information for residents
        $activeBookings = \App\Models\Backend\RoomBookingHistory::whereNull('today_check_out')->get();
        $allRooms = \App\Models\Backend\Room::pluck('room_no', 'id')->toArray();
        $allSeats = \App\Models\Backend\RoomSeat::pluck('seat_no', 'id')->toArray();

        foreach ($refunds as $refund) {
            $user = $refund->user;
            if (!$user) continue;

            $cleanPhone = preg_replace('/[^0-9]/', '', $user->phone ?? '');
            $cleanPhone10 = strlen($cleanPhone) >= 10 ? substr($cleanPhone, -10) : $cleanPhone;

            $booking = $activeBookings->first(function($b) use ($cleanPhone10) {
                $bClean = preg_replace('/[^0-9]/', '', $b->phone ?? '');
                $bClean10 = strlen($bClean) >= 10 ? substr($bClean, -10) : $bClean;
                return !empty($cleanPhone10) && !empty($bClean10) && $cleanPhone10 === $bClean10;
            });

            $floors = [];
            $rooms = [];
            $seats = [];

            if ($booking) {
                $roomsJson = $booking->floor_number_room_number_roomprice;
                $roomsArr = is_string($roomsJson) ? json_decode($roomsJson, true) : $roomsJson;
                if (is_array($roomsArr)) {
                    foreach ($roomsArr as $rItem) {
                        if (!empty($rItem['floornumber'])) {
                            $floors[] = $rItem['floornumber'];
                        }

                        if (!empty($rItem['roomnumber'])) {
                            $lbl = \App\Models\Backend\RoomBookingHistory::parseRoomSeatLabel($rItem['roomnumber'] ?? '', $rItem['room_type'] ?? null, $rItem['seat_id'] ?? null);
                            $rooms[] = $lbl;
                            if (str_contains($lbl, '-') && preg_match('/-\d+$/', $lbl)) {
                                $seats[] = $lbl;
                            }
                        }
                    }
                }
            }

            $user->booking_floor = !empty($floors) ? implode(', ', array_unique($floors)) : '-';
            $user->booking_room = !empty($rooms) ? implode(', ', array_unique($rooms)) : '-';
            $user->booking_seat = !empty($seats) ? implode(', ', array_unique($seats)) : '-';
        }

        $data['refunds'] = $refunds;
        $data['totalRefundAmount'] = (float) abs($refunds->sum('amount'));
        $data['totalRefundCount'] = $refunds->count();
        $data['totalRefundMembers'] = $refunds->pluck('user_id')->unique()->count();

        // Get all active users with roles for refund creation modal
        $data['users'] = $this->userService->usersWithRole();

        return view('backend.deposit.refund_index', $data);
    }

    public function getUserBalance($userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $balanceInfo = $this->mealService->getUserMealDepositBalance($userId);

        return response()->json([
            'success'          => true,
            'user_id'          => $user->id,
            'name'             => $user->name,
            'phone'            => $user->phone ?? '-',
            'deposit_total'    => (float) $balanceInfo['deposit_total'],
            'meal_cost'        => (float) $balanceInfo['meal_cost'],
            'balance'          => (float) $balanceInfo['balance'],
            'formatted_balance'=> number_format($balanceInfo['balance'], 2),
            'formatted_deposit'=> number_format($balanceInfo['deposit_total'], 2),
            'formatted_cost'   => number_format($balanceInfo['meal_cost'], 2),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount'  => 'required|numeric|min:1',
            'date'    => 'required|date',
            'note'    => 'nullable|string|max:255',
        ]);

        $user = User::findOrFail($request->user_id);
        $refundAmount = (float) abs($request->amount);

        // Check user's current available meal deposit balance
        $balanceInfo = $this->mealService->getUserMealDepositBalance($user->id);
        $currentBalance = (float) $balanceInfo['balance'];

        if ($refundAmount > $currentBalance) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'amount' => "মেম্বার '{$user->name}'-এর বর্তমান অবশিষ্ট ডিপোজিট ব্যালেন্স ৳ " . number_format($currentBalance, 2) . "। এর চেয়ে বেশি টাকা (৳ " . number_format($refundAmount, 2) . ") ফেরত দেওয়া সম্ভব নয়।"
                ]);
        }

        try {
            DB::beginTransaction();

            $noteText = $request->note ? trim($request->note) : 'Meal Deposit Refund (ডিপোজিট ফেরত)';

            Deposit::create([
                'user_id' => $user->id,
                'amount'  => -1 * $refundAmount,
                'date'    => $request->date,
                'made_by' => auth()->id(),
                'note'    => $noteText,
            ]);

            DB::commit();

            return redirect()->route('deposit-refunds.index')
                ->with('success', "মেম্বার '{$user->name}'-কে সফলভাবে ৳ " . number_format($refundAmount, 2) . " ডিপোজিট ফেরত (Refund) প্রদান করা হয়েছে।");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors(['error' => 'ফেরত প্রদান ব্যর্থ হয়েছে: ' . $e->getMessage()]);
        }
    }
}
