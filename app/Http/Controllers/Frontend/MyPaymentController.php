<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Backend\RoomBookingHistory;
use App\Models\Backend\MonthlyPayment;
use Carbon\Carbon;

class MyPaymentController extends Controller
{
    /**
     * Show logged in user's own payment history (Frontend look)
     */
    public function index()
    {
        $user = Auth::user();

        // Find booking by email or phone
        $bookings = RoomBookingHistory::where('email', $user->email)
            ->orWhere('phone', $user->phone)
            ->get();

        $bookingIds = $bookings->pluck('id');

        // Get all monthly payments (new to old)
        $payments = MonthlyPayment::whereIn('room_booking_history_id', $bookingIds)
            ->with(['booking'])
            ->orderByDesc('payment_month')
            ->get()
            ->map(function ($row) {
                $booking = $row->booking;
                $items = is_string($booking->floor_number_room_number_roomprice)
                    ? (json_decode($booking->floor_number_room_number_roomprice, true) ?? [])
                    : ($booking->floor_number_room_number_roomprice ?? []);
                $c = collect($items);

                // actual due calculate
                $due    = (float) $row->due_amount;
                $paid   = (float) ($row->paid_amount ?? 0);
                $total  = (float) ($row->amount ?? 0) + (float) ($row->carried_forward_due ?? 0);
                if ($due <= 0 && $paid <= 0) {
                    $due = $total;
                }

                return [
                    'id'             => $row->id,
                    'payment_month'  => $row->payment_month,
                    'amount'         => $row->amount,
                    'paid_amount'    => $row->paid_amount ?? 0,
                    'due_amount'     => $due,
                    'status'         => $row->status,
                    'payment_method' => $row->payment_method,
                    'note'           => $row->note,
                    'received_by'    => $row->received_by,
                    'created_at'     => $row->created_at->format('d M Y'),
                    'roomnumber'     => $c->pluck('roomnumber')->filter()->implode(', '),
                    'floornumber'    => $c->pluck('floornumber')->filter()->unique()->implode(', '),
                    'full_name'      => $booking->full_name ?? '-',
                ];
            });

        // Summary calculations
        $totalBilled  = $payments->sum('amount');
        $totalPaid    = $payments->sum('paid_amount');
        $totalDue     = $payments->where('status', '!=', 'paid')->sum('due_amount');
        $currentMonth = Carbon::now()->format('Y-m');

        $currentMonthPayment = $payments->firstWhere('payment_month', $currentMonth);

        // Meal System Data
        $selectedMonth = Carbon::now()->format('Y-m');
        $mealService = app(\App\Services\MealService::class);
        $depositService = app(\App\Services\DepositService::class);
        $fineService = app(\App\Services\FineService::class);

        $mealSummary = $mealService->singleUserMealHistory($selectedMonth, $user);
        $mealsList = $mealService->monthlyMealByUser($selectedMonth, $user->id);
        $depositData = $depositService->getUserMonthlyHistory($user->id, $selectedMonth);
        $finesList = $fineService->fineByUser($selectedMonth, $user->id);

        return view('Frontend.page.my-payment', compact(
            'payments', 'totalBilled', 'totalPaid', 'totalDue',
            'currentMonthPayment', 'user'
        ));
    }

    /**
     * Show logged in user's own payment history (Dashboard / Backend layout)
     */
    public function guestIndex()
    {
        $user = Auth::user();

        // Find booking by email or phone
        $bookings = RoomBookingHistory::where('email', $user->email)
            ->orWhere('phone', $user->phone)
            ->get();

        $bookingIds = $bookings->pluck('id');

        // Get all monthly payments (new to old)
        $payments = MonthlyPayment::whereIn('room_booking_history_id', $bookingIds)
            ->with(['booking'])
            ->orderByDesc('payment_month')
            ->get()
            ->map(function ($row) {
                $booking = $row->booking;
                $items = is_string($booking->floor_number_room_number_roomprice)
                    ? (json_decode($booking->floor_number_room_number_roomprice, true) ?? [])
                    : ($booking->floor_number_room_number_roomprice ?? []);
                $c = collect($items);

                // actual due calculate
                $due    = (float) $row->due_amount;
                $paid   = (float) ($row->paid_amount ?? 0);
                $total  = (float) ($row->amount ?? 0) + (float) ($row->carried_forward_due ?? 0);
                if ($due <= 0 && $paid <= 0) {
                    $due = $total;
                }

                return [
                    'id'             => $row->id,
                    'payment_month'  => $row->payment_month,
                    'amount'         => $row->amount,
                    'paid_amount'    => $row->paid_amount ?? 0,
                    'due_amount'     => $due,
                    'status'         => $row->status,
                    'payment_method' => $row->payment_method,
                    'note'           => $row->note,
                    'received_by'    => $row->received_by,
                    'created_at'     => $row->created_at->format('d M Y'),
                    'roomnumber'     => $c->pluck('roomnumber')->filter()->implode(', '),
                    'floornumber'    => $c->pluck('floornumber')->filter()->unique()->implode(', '),
                    'full_name'      => $booking->full_name ?? '-',
                ];
            });

        // Summary calculations
        $totalBilled  = $payments->sum('amount');
        $totalPaid    = $payments->sum('paid_amount');
        $totalDue     = $payments->where('status', '!=', 'paid')->sum('due_amount');
        $currentMonth = Carbon::now()->format('Y-m');

        $currentMonthPayment = $payments->firstWhere('payment_month', $currentMonth);

        return view('backend.monthly_payment.guest_payments', compact(
            'payments', 'totalBilled', 'totalPaid', 'totalDue',
            'currentMonthPayment', 'user'
        ));
    }

    /**
     * Show logged in user's own meal history (Dashboard / Backend layout)
     */
    public function guestMeals(Request $request)
    {
        $user = Auth::user();
        $selectedMonth = $request->get('month', Carbon::now()->format('Y-m'));

        $mealService = app(\App\Services\MealService::class);
        $depositService = app(\App\Services\DepositService::class);
        $fineService = app(\App\Services\FineService::class);

        $mealSummary = $mealService->singleUserMealHistory($selectedMonth, $user);
        $mealsList = $mealService->monthlyMealByUser($selectedMonth, $user->id);
        $depositData = $depositService->getUserMonthlyHistory($user->id, $selectedMonth);
        $finesList = $fineService->fineByUser($selectedMonth, $user->id);

        return view('backend.monthly_payment.guest_meals', compact(
            'user', 'mealSummary', 'mealsList', 'depositData', 'finesList', 'selectedMonth'
        ));
    }
}
