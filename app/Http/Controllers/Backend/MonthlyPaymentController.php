<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Backend\MonthlyPayment;
use App\Models\Backend\MonthlyPaymentTransaction;
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

        $query = MonthlyPayment::whereHas('booking', function ($q) {
            $q->where('status', 1)->whereNull('today_check_out');
        })
        ->with(['booking' => function ($q) {
            $q->with(['division', 'district', 'thana']);
        }])
        ->where('payment_month', $month);

        if ($search !== '') {
            $query->whereHas('booking', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
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
            $formattedRooms = [];
            if ($booking) {
                $items = is_string($booking->floor_number_room_number_roomprice)
                    ? (json_decode($booking->floor_number_room_number_roomprice, true) ?? [])
                    : ($booking->floor_number_room_number_roomprice ?? []);

                foreach ($items as $it) {
                    $lbl = RoomBookingHistory::parseRoomSeatLabel($it['roomnumber'] ?? '', $it['room_type'] ?? null, $it['seat_id'] ?? null);
                    if ($lbl && $lbl !== '-') {
                        $formattedRooms[] = $lbl;
                    }
                }
            }
            $c = collect($items);
            $roomStr = implode(', ', array_unique($formattedRooms));
            if (empty($roomStr)) {
                $roomStr = $c->pluck('roomnumber')->filter()->implode(', ');
            }

            $allHistory = [];
            $dueBreakdown = [];
            $allReceiptLogs = [];
            if ($freshRow->room_booking_history_id) {
                $historyRows = MonthlyPayment::where('room_booking_history_id', $freshRow->room_booking_history_id)
                    ->where('payment_month', '<=', $freshRow->payment_month)
                    ->orderBy('payment_month', 'asc')
                    ->get();

                foreach ($historyRows as $h) {
                    $monthTotalBill = (float) $h->amount + (float) ($h->electricity_bill ?? 0);
                    $monthOwnDue = max(0, $monthTotalBill - (float) $h->paid_amount);
                    $allHistory[] = [
                        'id'               => $h->id,
                        'payment_month'    => $h->payment_month,
                        'amount'           => (float) $h->amount,
                        'electricity_bill' => (float) ($h->electricity_bill ?? 0),
                        'total_bill'       => $monthTotalBill,
                        'paid_amount'      => (float) $h->paid_amount,
                        'due_amount'       => $monthOwnDue,
                        'status'           => $h->status,
                        'note'             => $h->note,
                    ];

                    if (!empty($h->note)) {
                        $lines = explode("\n", $h->note);
                        foreach ($lines as $line) {
                            $tLine = trim($line);
                            if ($tLine) {
                                $monthName = \Carbon\Carbon::parse($h->payment_month . '-01')->format('F Y');
                                $dateTime = '';
                                $colAmount = (float) $h->paid_amount;
                                $method = 'Cash';
                                $trx = '';

                                if (preg_match('/\[(.*?)\]\s*Collected\s*৳([\d\.]+)\s*via\s*([^\(\-]+)(?:\(Trx:\s*([^\)]+)\))?/i', $tLine, $m)) {
                                    $dateTime  = trim($m[1]);
                                    $colAmount = (float) $m[2];
                                    $method    = trim($m[3]);
                                    $trx       = isset($m[4]) ? trim($m[4]) : '';
                                } else {
                                    $dateTime = $freshRow->created_at ? $freshRow->created_at->format('d-m-Y g:i A') : '';
                                }

                                $parsedTransactions[] = [
                                    'month'    => $monthName,
                                    'datetime' => $dateTime,
                                    'amount'   => $colAmount,
                                    'method'   => $method,
                                    'trx'      => $trx,
                                    'raw'      => $tLine,
                                ];

                                $allReceiptLogs[] = "<strong>[{$monthName}]</strong> " . $tLine;
                            }
                        }
                    }
                }

                $dueBreakdown = array_values(array_filter($allHistory, function ($item) {
                    return $item['due_amount'] > 0;
                }));
            }

            return [
                'id'                      => $freshRow->id,
                'room_booking_history_id' => $freshRow->room_booking_history_id,
                'payment_month'           => $freshRow->payment_month,
                'amount'                  => $freshRow->amount,
                'electricity_bill'        => (float) ($freshRow->electricity_bill ?? 0),
                'carried_forward_due'     => $freshRow->carried_forward_due ?? 0,
                'paid_amount'             => $freshRow->paid_amount,
                'due_amount'              => $freshRow->due_amount,
                'payment_method'          => $freshRow->payment_method,
                'trx_id'                  => $freshRow->trx_id,
                'note'                    => $freshRow->note,
                'status'                  => $freshRow->status,
                'received_by'             => $freshRow->received_by,
                'created_at'              => $freshRow->created_at ? $freshRow->created_at->format('Y-m-d H:i:s') : '',
                'updated_at'              => $freshRow->updated_at ? $freshRow->updated_at->format('Y-m-d H:i:s') : '',
                'full_name'               => $booking->full_name ?? '-',
                'phone'                   => $booking->phone ?? '-',
                'email'                   => $booking->email ?? '',
                'roomnumber'              => $roomStr,
                'floornumber'             => $c->pluck('floornumber')->filter()->unique()->implode(', '),
                'due_date'                => $freshRow->payment_month . '-05',
                'due_breakdown'           => $dueBreakdown,
                'all_history'             => $allHistory,
                'all_receipt_logs'        => $allReceiptLogs,
                'parsed_transactions'     => $parsedTransactions ?? [],
            ];
        });

        $paginator->setCollection($mapped);

        $allMonthPayments = MonthlyPayment::whereHas('booking', function ($q) {
            $q->where('status', 1)->whereNull('today_check_out');
        })
        ->where('payment_month', $month)
        ->get();

        $totalRent        = (float) $allMonthPayments->sum('amount');
        $totalElectricity = (float) $allMonthPayments->sum('electricity_bill');
        $totalDue         = (float) $allMonthPayments->sum('due_amount');
        
        // Sum of all cash collected in this billing month (including previous months' carried forward dues collected in this month)
        $txCollected      = (float) MonthlyPaymentTransaction::whereHas('booking', function ($q) {
            $q->where('status', 1)->whereNull('today_check_out');
        })
        ->where('payment_month', $month)
        ->sum('total_collected');

        $monthPaidSum     = (float) $allMonthPayments->sum('paid_amount');
        $totalCollectedInHand = $txCollected > 0 ? $txCollected : $monthPaidSum;

        $stats = [
            'total_collected_in_hand' => $totalCollectedInHand,
            'total_rent'              => $totalRent,
            'total_electricity'       => $totalElectricity,
            'total_current_bill'      => $totalRent + $totalElectricity,
            'total_due'               => $totalDue,
            'total_residents'         => $allMonthPayments->count(),
            'paid_count'              => $allMonthPayments->where('status', 'paid')->count(),
            'partial_count'           => $allMonthPayments->where('status', 'partial')->count(),
            'pending_count'           => $allMonthPayments->whereIn('status', ['pending', 'overdue'])->count(),
        ];

        return response()->json([
            'data'       => $mapped,
            'pagination' => [
                'total'        => $paginator->total(),
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
            ],
            'stats'      => $stats,
        ]);
    }

    public function generateBills(Request $request)
    {
        $targetMonth = $request->input('month', Carbon::now()->format('Y-m'));
        $targetCarbon = Carbon::parse($targetMonth . '-01');

        $startOfMonth = $targetCarbon->copy()->startOfMonth()->toDateString();
        $endOfMonth   = $targetCarbon->copy()->endOfMonth()->toDateString();

        // Delete any un-paid monthly payment records for inactive or pending (status != 1) or checked-out residents
        MonthlyPayment::whereHas('booking', function ($q) {
            $q->where('status', '!=', 1)->orWhereNotNull('today_check_out');
        })->where('paid_amount', 0)->delete();

        // All active guest bookings currently residing in the hostel (where status is 1 and today_check_out is null)
        $bookings = RoomBookingHistory::where('status', 1)->whereNull('today_check_out')->get();

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
                        $existingPayment->update([
                            'amount' => $totalMonthlyRent,
                        ]);
                        $generatedCount++;
                    }
                } else {
                    MonthlyPayment::create([
                        'room_booking_history_id' => $booking->id,
                        'payment_month'           => $targetMonth,
                        'amount'                  => $totalMonthlyRent,
                        'carried_forward_due'     => 0,
                        'paid_amount'             => 0,
                        'due_amount'              => $totalMonthlyRent,
                        'payment_method'          => 'unpaid',
                        'status'                  => 'pending',
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
            'electricity_bill'  => 'nullable|numeric|min:0',
            'payment_method'    => 'required|string|max:50',
            'trx_id'            => 'nullable|string|max:100',
            'note'              => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $payment = MonthlyPayment::findOrFail($request->id);
            $bookingId = $payment->room_booking_history_id;

            // If electricity bill was passed, update it on the current payment record first
            if ($request->has('electricity_bill')) {
                $payment->electricity_bill = max(0, (float) $request->electricity_bill);
                $payment->save();
            }

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

                $monthlyBillTotal  = (float) $bill->amount + (float) ($bill->electricity_bill ?? 0);
                $currentPaid       = (float) ($bill->paid_amount ?? 0);
                $neededForThisBill = max(0, $monthlyBillTotal - $currentPaid);

                if ($neededForThisBill <= 0) continue;

                $payForThisBill = min($remainingCollection, $neededForThisBill);
                $newPaidTotal   = $currentPaid + $payForThisBill;
                $newDueTotal    = max(0, $monthlyBillTotal - $newPaidTotal);

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

            // Create individual transaction receipt record for cash collection accounting
            MonthlyPaymentTransaction::create([
                'room_booking_history_id' => $bookingId,
                'monthly_payment_id'      => $payment->id,
                'payment_month'           => $payment->payment_month,
                'total_collected'         => $amountToCollect,
                'electricity_bill_paid'   => max(0, (float) ($request->electricity_bill ?? 0)),
                'payment_method'          => $request->payment_method ?? 'Cash',
                'trx_id'                  => $request->trx_id,
                'note'                    => $request->note,
                'payment_date'            => Carbon::now('Asia/Dhaka'),
                'received_by'             => $receivedBy,
            ]);

            // Sync all subsequent months' carried forward dues & total dues
            self::syncFutureDues($bookingId);

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
            $totalMonthlyRent = (float) $pay->amount;
            $electricityBill  = (float) ($pay->electricity_bill ?? 0);
            $alreadyPaid      = (float) ($pay->paid_amount ?? 0);

            // Carried forward due is the exact cumulative unpaid balance from preceding months
            $carriedForward = ($index === 0) ? 0 : $runningPrevDue;

            $totalBill = $totalMonthlyRent + $electricityBill + $carriedForward;
            $newDue    = max(0, $totalBill - $alreadyPaid);

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
