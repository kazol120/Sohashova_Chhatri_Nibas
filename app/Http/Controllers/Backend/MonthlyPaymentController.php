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

        // Sync future dues for each booking in current paginator collection
        foreach ($paginator->getCollection() as $pRow) {
            if ($pRow->room_booking_history_id) {
                self::syncFutureDues($pRow->room_booking_history_id);
            }
        }

        // Re-fetch fresh model instances after sync
        $mapped = $paginator->getCollection()->map(function ($row) {
            $freshRow = MonthlyPayment::find($row->id) ?? $row;
            $booking  = $freshRow->booking;
            $items    = [];
            if ($booking) {
                $items = is_string($booking->floor_number_room_number_roomprice)
                    ? (json_decode($booking->floor_number_room_number_roomprice, true) ?? [])
                    : ($booking->floor_number_room_number_roomprice ?? []);
            }
            $c = collect($items);

            return [
                'id'                      => $freshRow->id,
                'room_booking_history_id' => $freshRow->room_booking_history_id,
                'payment_month'           => $freshRow->payment_month,
                'amount'                  => $freshRow->amount,
                'carried_forward_due'     => $freshRow->carried_forward_due ?? 0,
                'paid_amount'             => $freshRow->paid_amount,
                'due_amount'              => $freshRow->due_amount,
                'payment_method'          => $freshRow->payment_method,
                'trx_id'                  => $freshRow->trx_id,
                'note'                    => $freshRow->note,
                'status'                  => $freshRow->status,
                'received_by'             => $freshRow->received_by,
                'created_at'              => $freshRow->created_at ? $freshRow->created_at->format('Y-m-d H:i:s') : '',
                'full_name'               => $booking->full_name ?? '-',
                'phone'                   => $booking->phone ?? '-',
                'email'                   => $booking->email ?? '',
                'roomnumber'              => $c->pluck('roomnumber')->filter()->implode(', '),
                'floornumber'             => $c->pluck('floornumber')->filter()->unique()->implode(', '),
                'due_date'                => $freshRow->payment_month . '-05',
            ];
        });

        $paginator->setCollection($mapped);

        return response()->json($paginator);
    }

    public function generateBills(Request $request)
    {
        $targetMonth = $request->input('month', Carbon::now()->format('Y-m'));
        $targetCarbon = Carbon::parse($targetMonth . '-01');

        $startOfMonth = $targetCarbon->copy()->startOfMonth()->toDateString();
        $endOfMonth   = $targetCarbon->copy()->endOfMonth()->toDateString();

        // Active guest bookings (status = 0)
        $bookings = RoomBookingHistory::where('status', 0)
            ->get()
            ->filter(function ($booking) use ($startOfMonth, $endOfMonth) {
                if (!$booking->check_in) return true;

                try {
                    $cIn = Carbon::parse($booking->check_in)->toDateString();
                    $cOut = $booking->check_out ? Carbon::parse($booking->check_out)->toDateString() : '9999-12-31';
                    return ($cIn <= $endOfMonth) && ($cOut >= $startOfMonth);
                } catch (\Throwable $e) {
                    return true;
                }
            });

        $generatedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($bookings as $booking) {
                $totalMonthlyRent = (float) ($booking->monthly_amount ?? 0);

                if ($totalMonthlyRent <= 0) {
                    $items = is_string($booking->floor_number_room_number_roomprice)
                        ? (json_decode($booking->floor_number_room_number_roomprice, true) ?? [])
                        : ($booking->floor_number_room_number_roomprice ?? []);

                    $totalMonthlyRent = collect($items)->sum(function ($item) {
                        return (float) ($item['price'] ?? $item['roomprice'] ?? 0);
                    });
                }

                if ($totalMonthlyRent <= 0) {
                    continue;
                }

                // Determine start month for this booking based on check_in
                $startMonthCarbon = $targetCarbon->copy();
                if ($booking->check_in) {
                    try {
                        $checkInCarbon = Carbon::parse($booking->check_in)->startOfMonth();
                        if ($checkInCarbon->lessThan($targetCarbon)) {
                            $startMonthCarbon = $checkInCarbon;
                        }
                    } catch (\Throwable $e) {
                        // Keep targetCarbon if parse fails
                    }
                }

                // Loop through all months from startMonthCarbon up to targetCarbon
                $currCarbon = $startMonthCarbon->copy();
                while ($currCarbon->lessThanOrEqualTo($targetCarbon)) {
                    $mStr = $currCarbon->format('Y-m');

                    $existingPayment = MonthlyPayment::where('room_booking_history_id', $booking->id)
                        ->where('payment_month', $mStr)
                        ->first();

                    if ($existingPayment) {
                        // If bill was generated before but unpaid, sync amount with monthly_amount
                        if ((float) $existingPayment->paid_amount == 0 && (float) $existingPayment->amount != $totalMonthlyRent) {
                            $carriedForwardDue = (float) $existingPayment->carried_forward_due;
                            $existingPayment->update([
                                'amount'     => $totalMonthlyRent,
                                'due_amount' => $totalMonthlyRent + $carriedForwardDue,
                            ]);
                            $generatedCount++;
                        }
                    } else {
                        // Calculate carried forward due from previous unpaid months
                        $carriedForwardDue = (float) MonthlyPayment::where('room_booking_history_id', $booking->id)
                            ->where('payment_month', '<', $mStr)
                            ->whereIn('status', ['pending', 'partial', 'overdue'])
                            ->sum('due_amount');

                        $totalDue = $totalMonthlyRent + $carriedForwardDue;

                        MonthlyPayment::create([
                            'room_booking_history_id' => $booking->id,
                            'payment_month'           => $mStr,
                            'amount'                  => $totalMonthlyRent,
                            'carried_forward_due'     => $carriedForwardDue,
                            'paid_amount'             => 0,
                            'due_amount'              => $totalDue,
                            'payment_method'          => 'unpaid',
                            'status'                  => $carriedForwardDue > 0 ? 'overdue' : 'pending',
                        ]);

                        $generatedCount++;
                    }

                    $currCarbon->addMonth();
                }
            }

            DB::commit();
            return response()->json([
                'status'  => true,
                'message' => "Successfully generated/updated {$generatedCount} billing records up to {$targetMonth}.",
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

            // Sync all subsequent months' carried forward dues & total dues
            self::syncFutureDues($payment->room_booking_history_id);

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

    public static function syncFutureDues($bookingId)
    {
        $payments = MonthlyPayment::where('room_booking_history_id', $bookingId)
            ->orderBy('payment_month', 'asc')
            ->get();

        $runningPrevDue = 0;

        foreach ($payments as $index => $pay) {
            if ($index === 0) {
                $runningPrevDue = (float) $pay->due_amount;
                continue;
            }

            $totalMonthlyRent = (float) $pay->amount;
            $alreadyPaid      = (float) ($pay->paid_amount ?? 0);

            $carriedForward = $runningPrevDue;
            $totalBill      = $totalMonthlyRent + $carriedForward;
            $newDue         = $totalBill - $alreadyPaid;
            if ($newDue < 0.01) $newDue = 0;

            $status = ($newDue <= 0) ? 'paid' : (($alreadyPaid > 0) ? 'partial' : ($carriedForward > 0 ? 'overdue' : 'pending'));

            $pay->update([
                'carried_forward_due' => $carriedForward,
                'due_amount'          => $newDue,
                'status'              => $status,
            ]);

            $runningPrevDue = $newDue;
        }
    }
}
