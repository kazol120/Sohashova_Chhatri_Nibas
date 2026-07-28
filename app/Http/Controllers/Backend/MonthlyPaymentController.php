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

                // Check if payment record already exists for this exact target month
                $existingPayment = MonthlyPayment::where('room_booking_history_id', $booking->id)
                    ->where('payment_month', $targetMonth)
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
                    // Calculate carried forward due from existing previous unpaid months
                    $carriedForwardDue = (float) MonthlyPayment::where('room_booking_history_id', $booking->id)
                        ->where('payment_month', '<', $targetMonth)
                        ->whereIn('status', ['pending', 'partial', 'overdue'])
                        ->sum('due_amount');

                    $totalDue = $totalMonthlyRent + $carriedForwardDue;

                    MonthlyPayment::create([
                        'room_booking_history_id' => $booking->id,
                        'payment_month'           => $targetMonth,
                        'amount'                  => $totalMonthlyRent,
                        'carried_forward_due'     => $carriedForwardDue,
                        'paid_amount'             => 0,
                        'due_amount'              => $totalDue,
                        'payment_method'          => 'unpaid',
                        'status'                  => $carriedForwardDue > 0 ? 'overdue' : 'pending',
                    ]);

                    $generatedCount++;
                }

                // Sync all future dues
                self::syncFutureDues($booking->id);
            }

            DB::commit();
            return response()->json([
                'status'  => true,
                'message' => "Successfully generated {$generatedCount} bills for {$targetMonth}.",
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
            $bookingId = $payment->room_booking_history_id;

            // Make sure all dues are synced up to date before collecting
            self::syncFutureDues($bookingId);
            $payment->refresh();

            if ($payment->status === 'paid' || (float) $payment->due_amount <= 0) {
                throw new \Exception("This bill is already fully paid.");
            }

            $amountToCollect = (float) $request->amount_to_collect;
            $actualDue       = (float) $payment->due_amount;

            if ($amountToCollect > $actualDue + 0.01) {
                throw new \Exception("Collected amount cannot exceed the remaining due amount of ৳" . number_format($actualDue, 2));
            }

            $user = Auth::user();
            $receivedBy = $user ? $user->name : 'Admin';
            $dateTimeStr = Carbon::now('Asia/Dhaka')->format('d-m-Y g:i A');
            $trxText     = $request->trx_id ? " (Trx: {$request->trx_id})" : "";
            $customNote  = $request->note ? " - Note: {$request->note}" : "";

            // FIFO Payment Distribution: Fetch all unpaid or partial bills up to the target payment month (oldest first)
            $unpaidBills = MonthlyPayment::where('room_booking_history_id', $bookingId)
                ->where('payment_month', '<=', $payment->payment_month)
                ->where(function ($q) {
                    $q->whereIn('status', ['pending', 'partial', 'overdue'])
                      ->orWhere('due_amount', '>', 0);
                })
                ->orderBy('payment_month', 'asc')
                ->get();

            $remainingCollection = $amountToCollect;

            foreach ($unpaidBills as $bill) {
                if ($remainingCollection <= 0) break;

                $billDue = (float) $bill->due_amount;
                if ($billDue <= 0) continue;

                $payForThisBill = min($remainingCollection, $billDue);
                $newPaidTotal   = (float) ($bill->paid_amount ?? 0) + $payForThisBill;
                $newDueTotal    = $billDue - $payForThisBill;
                if ($newDueTotal < 0.01) {
                    $newDueTotal = 0;
                }

                $logEntry    = "[{$dateTimeStr}] Collected ৳{$payForThisBill} via {$request->payment_method}{$trxText}{$customNote}";
                $updatedNote = trim(($bill->note ? $bill->note . "\n" : "") . $logEntry);

                $bill->update([
                    'paid_amount'    => $newPaidTotal,
                    'due_amount'     => $newDueTotal,
                    'status'         => ($newDueTotal <= 0) ? 'paid' : 'partial',
                    'payment_method' => $request->payment_method,
                    'trx_id'         => $request->trx_id,
                    'note'           => $updatedNote,
                    'received_by'    => $receivedBy,
                ]);

                $remainingCollection -= $payForThisBill;
            }

            // Sync all subsequent months' carried forward dues & total dues
            self::syncFutureDues($bookingId);

            DB::commit();
            return response()->json([
                'status'  => true,
                'message' => 'Payment collected and applied to oldest dues successfully!',
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
            $totalMonthlyRent = (float) $pay->amount;
            $alreadyPaid      = (float) ($pay->paid_amount ?? 0);

            if ($index === 0) {
                $runningPrevDue = (float) $pay->due_amount;
                $pay->update([
                    'carried_forward_due' => 0,
                ]);
            } else {
                $existingCarriedForward = (float) ($pay->carried_forward_due ?? 0);
                $carriedForward = ($existingCarriedForward > 0 && $alreadyPaid > 0)
                    ? $existingCarriedForward
                    : $runningPrevDue;

                $totalBill = $totalMonthlyRent + $carriedForward;
                $newDue    = $totalBill - $alreadyPaid;
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
}
