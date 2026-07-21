<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Backend\MonthlyPayment;
use App\Models\Backend\RoomBookingHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MonthlyPaymentController extends Controller
{
    public function index()
    {
        return view('backend.monthly_payment.monthly_payment');
    }

    public function getPayments(Request $request)
    {
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        $search = trim($request->get('search', ''));
        $perPage = (int) $request->get('per_page', 10);

        $query = MonthlyPayment::with(['booking' => function ($q) {
            $q->with(['division', 'district', 'thana']);
        }])
        ->where('payment_month', $month);

        if ($search !== '') {
            $query->whereHas('booking', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $paginator = $query->orderBy('id', 'desc')->paginate($perPage);

        $mapped = $paginator->getCollection()->map(function ($row) {
            $booking = $row->booking;
            $items = [];
            if ($booking) {
                $items = is_string($booking->floor_number_room_number_roomprice)
                    ? (json_decode($booking->floor_number_room_number_roomprice, true) ?? [])
                    : ($booking->floor_number_room_number_roomprice ?? []);
            }
            $c = collect($items);

            return [
                'id'                      => $row->id,
                'room_booking_history_id' => $row->room_booking_history_id,
                'payment_month'           => $row->payment_month,
                'amount'                  => $row->amount,
                'carried_forward_due'     => $row->carried_forward_due ?? 0,
                'paid_amount'             => $row->paid_amount,
                'due_amount'              => $row->due_amount,
                'payment_method'          => $row->payment_method,
                'trx_id'                  => $row->trx_id,
                'note'                    => $row->note,
                'status'                  => $row->status,
                'received_by'             => $row->received_by,
                'created_at'              => $row->created_at->format('Y-m-d H:i:s'),
                'full_name'               => $booking->full_name ?? '-',
                'phone'                   => $booking->phone ?? '-',
                'email'                   => $booking->email ?? '',
                'roomnumber'              => $c->pluck('roomnumber')->filter()->implode(', '),
                'floornumber'             => $c->pluck('floornumber')->filter()->unique()->implode(', '),
                'due_date'                => $row->payment_month . '-05',
            ];
        });

        $paginator->setCollection($mapped);

        return response()->json($paginator);
    }

    public function generateBills(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));

        $startOfMonth = Carbon::parse($month . '-01')->startOfMonth()->toDateString();
        $endOfMonth = Carbon::parse($month . '-01')->endOfMonth()->toDateString();

        $bookings = RoomBookingHistory::where('status', 0)
            ->where('check_in', '<=', $endOfMonth)
            ->where('check_out', '>=', $startOfMonth)
            ->get();

        $generatedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($bookings as $booking) {
                $exists = MonthlyPayment::where('room_booking_history_id', $booking->id)
                    ->where('payment_month', $month)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $items = is_string($booking->floor_number_room_number_roomprice)
                    ? (json_decode($booking->floor_number_room_number_roomprice, true) ?? [])
                    : ($booking->floor_number_room_number_roomprice ?? []);
                
                $totalMonthlyRent = collect($items)->sum(function ($item) {
                    return (float) ($item['price'] ?? 0);
                });

                if ($totalMonthlyRent <= 0) {
                    continue;
                }

                // আগের মাসগুলোর মোট বকেয়া (due) carry forward করো
                $carriedForwardDue = (float) MonthlyPayment::where('room_booking_history_id', $booking->id)
                    ->where('payment_month', '<', $month)
                    ->whereIn('status', ['pending', 'partial', 'overdue'])
                    ->sum('due_amount');

                $totalDue = $totalMonthlyRent + $carriedForwardDue;

                MonthlyPayment::create([
                    'room_booking_history_id' => $booking->id,
                    'payment_month'           => $month,
                    'amount'                  => $totalMonthlyRent,
                    'carried_forward_due'     => $carriedForwardDue,
                    'paid_amount'             => 0,
                    'due_amount'              => $totalDue,
                    'payment_method'          => 'unpaid',
                    'status'                  => $carriedForwardDue > 0 ? 'overdue' : 'pending',
                ]);

                $generatedCount++;
            }

            DB::commit();
            return response()->json([
                'status'  => true,
                'message' => "Successfully generated {$generatedCount} bills for {$month}.",
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function collectPayment(Request $request)
    {
        $request->validate([
            'id'                => 'required|exists:monthly_payments,id',
            'amount_to_collect' => 'required|numeric|min:1',
            'payment_method'    => 'required|string|max:50',
            'trx_id'            => 'nullable|string|max:100',
            'note'              => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $payment = MonthlyPayment::findOrFail($request->id);
            if ($payment->status === 'paid') {
                throw new \Exception("This bill is already fully paid.");
            }

            $amountToCollect  = (float) $request->amount_to_collect;
            $totalRent        = (float) $payment->amount;
            $carriedForward   = (float) ($payment->carried_forward_due ?? 0);
            $alreadyPaid      = (float) ($payment->paid_amount ?? 0);

            // আসল total due = এই মাসের rent + আগের বকেয়া
            $totalBill = $totalRent + $carriedForward;

            // DB-তে due_amount যদি সঠিকভাবে set না থাকে (0 কিন্তু কিছুই দেওয়া হয়নি)
            $actualDue = (float) $payment->due_amount;
            if ($actualDue <= 0 && $alreadyPaid <= 0) {
                $actualDue = $totalBill;
                // DB fix করে দিই
                $payment->due_amount = $totalBill;
            }

            if ($amountToCollect > $actualDue) {
                throw new \Exception("Collected amount cannot exceed the remaining due amount of ৳" . number_format($actualDue, 2));
            }

            $newPaidTotal = $alreadyPaid + $amountToCollect;
            $newDueTotal  = $totalBill - $newPaidTotal;
            if ($newDueTotal < 0.01) {
                $newDueTotal = 0;
            }

            $user = Auth::user();
            $receivedBy = $user ? ($user->name . ' (ID: ' . $user->id . ')') : 'Admin';

            // Append transaction details to the note field history log
            $dateTimeStr = Carbon::now('Asia/Dhaka')->format('d-m-Y g:i A');
            $trxText     = $request->trx_id ? " (Trx: {$request->trx_id})" : "";
            $customNote  = $request->note ? " - Note: {$request->note}" : "";
            $logEntry    = "[{$dateTimeStr}] Collected ৳{$amountToCollect} via {$request->payment_method}{$trxText}{$customNote}";
            $updatedNote = trim(($payment->note ? $payment->note . "\n" : "") . $logEntry);

            $payment->update([
                'paid_amount'    => $newPaidTotal,
                'due_amount'     => $newDueTotal,
                'status'         => ($newDueTotal <= 0) ? 'paid' : 'partial',
                'payment_method' => $request->payment_method,
                'trx_id'         => $request->trx_id,
                'note'           => $updatedNote,
                'received_by'    => $receivedBy,
            ]);

            DB::commit();
            return response()->json([
                'status'  => true,
                'message' => 'Payment collected successfully!',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
