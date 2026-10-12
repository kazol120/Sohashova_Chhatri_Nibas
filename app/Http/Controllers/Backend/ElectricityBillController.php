<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Backend\MonthlyPayment;
use App\Models\Backend\RoomBookingHistory;
use App\Models\Backend\Floor;
use App\Models\Backend\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ElectricityBillController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'checkUserStatus']);
    }

    /**
     * Display Resident List & Electricity Bill Dashboard
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $filter = $request->input('filter', $request->input('status', 'all'));
        
        $currentYear = (int) Carbon::now()->format('Y');
        $currentMonthNum = (int) Carbon::now()->format('m');

        // Check if month parameter was passed as YYYY-MM string or integer
        $monthInput = $request->input('month');
        if (!empty($monthInput) && str_contains($monthInput, '-')) {
            $parts = explode('-', $monthInput);
            $selectedYear = (int) ($parts[0] ?? $currentYear);
            $selectedMonthNum = (int) ($parts[1] ?? $currentMonthNum);
        } else {
            $selectedYear = (int) $request->input('year', $currentYear);
            $selectedMonthNum = (int) $request->input('month', $currentMonthNum);
        }

        if ($selectedMonthNum < 1 || $selectedMonthNum > 12) {
            $selectedMonthNum = $currentMonthNum;
        }

        if ($selectedYear < 2020) {
            $selectedYear = $currentYear;
        }

        $selectedMonthStr = sprintf('%04d-%02d', $selectedYear, $selectedMonthNum);
        $currentMonthStr = $selectedMonthStr;

        // Dynamic Years List: from 2026 up to max(currentYear + 1, selectedYear)
        $minYear = 2026;
        $maxYear = max($currentYear + 1, $selectedYear);
        $yearsList = range($minYear, $maxYear);

        // Full Months List (January to December)
        $monthsList = [
            1  => 'January',
            2  => 'February',
            3  => 'March',
            4  => 'April',
            5  => 'May',
            6  => 'June',
            7  => 'July',
            8  => 'August',
            9  => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ];

        if ($filter === 'deleted' || $filter === 'inactive') {
            $query = RoomBookingHistory::onlyTrashed()
                ->with(['floor', 'room', 'user', 'division', 'district', 'thana'])
                ->latest('deleted_at');
        } else {
            $query = RoomBookingHistory::with(['floor', 'room', 'user', 'division', 'district', 'thana'])
                ->latest('id');

            if ($filter === 'active') {
                $query->whereNull('today_check_out')->where('status', 1);
            } elseif ($filter === 'pending') {
                $query->whereNull('today_check_out')->where(function($q) {
                    $q->where('status', 0)->orWhereNull('status');
                });
            } elseif ($filter === 'has_email') {
                $query->whereNotNull('email')->where('email', '!=', '');
            } elseif ($filter === 'no_email') {
                $query->where(function($q) {
                    $q->whereNull('email')->orWhere('email', '');
                });
            } elseif ($filter === 'released') {
                $query->whereNotNull('today_check_out');
            } elseif ($filter === 'has_bill') {
                $bookingIdsWithBills = MonthlyPayment::where('payment_month', $selectedMonthStr)
                    ->where('electricity_bill', '>', 0)
                    ->pluck('room_booking_history_id')
                    ->filter()
                    ->unique();
                $query->whereIn('id', $bookingIdsWithBills);
            }
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('floor_number_room_number_roomprice', 'like', "%{$search}%");
            });
        }

        $allBookings = $query->get();

        // Preload MonthlyPayments for all retrieved bookings to calculate electricity stats efficiently
        $bookingIds = $allBookings->pluck('id')->filter()->values();
        $allPayments = MonthlyPayment::whereIn('room_booking_history_id', $bookingIds)->get()->groupBy('room_booking_history_id');

        $residents = $allBookings->map(function ($booking) use ($allPayments, $selectedMonthStr) {
            $bPayments = $allPayments->get($booking->id, collect());
            
            $currentMonthPayment = $bPayments->firstWhere('payment_month', $selectedMonthStr);
            $currentMonthBill = $currentMonthPayment ? (float)($currentMonthPayment->electricity_bill ?? 0) : 0;
            $lifetimeBill = (float)$bPayments->sum('electricity_bill');

            return $this->formatResidentBasic($booking, $currentMonthBill, $lifetimeBill);
        });

        // Summary Counters
        $totalCount     = RoomBookingHistory::count();
        $activeCount    = RoomBookingHistory::whereNull('today_check_out')->where('status', 1)->count();
        $pendingCount   = RoomBookingHistory::whereNull('today_check_out')->where(function($q) {
            $q->where('status', 0)->orWhereNull('status');
        })->count();
        $hasEmailCount  = RoomBookingHistory::whereNotNull('email')->where('email', '!=', '')->count();
        $noEmailCount   = RoomBookingHistory::where(function($q) {
            $q->whereNull('email')->orWhere('email', '');
        })->count();
        $releasedCount  = RoomBookingHistory::whereNotNull('today_check_out')->count();
        $deletedCount   = RoomBookingHistory::onlyTrashed()->count();

        // Non-deleted booking IDs to exclude deleted residents from general summary card totals
        $nonDeletedBookingIds = RoomBookingHistory::pluck('id')->filter()->toArray();

        // Electricity Bill Totals for Selected Month & All-Time
        $totalCurrentMonthElectricity = (float) MonthlyPayment::where('payment_month', $selectedMonthStr)
            ->whereIn('room_booking_history_id', $nonDeletedBookingIds)
            ->sum('electricity_bill');

        $totalAllTimeElectricity = (float) MonthlyPayment::whereIn('room_booking_history_id', $nonDeletedBookingIds)
            ->sum('electricity_bill');

        $totalBillPayersCount = MonthlyPayment::where('payment_month', $selectedMonthStr)
            ->where('electricity_bill', '>', 0)
            ->whereIn('room_booking_history_id', $nonDeletedBookingIds)
            ->distinct('room_booking_history_id')
            ->count('room_booking_history_id');

        return view('backend.electricity_bill.index', compact(
            'residents', 'search', 'filter', 'totalCount', 'activeCount', 'pendingCount', 'hasEmailCount', 'noEmailCount', 'releasedCount', 'deletedCount',
            'totalCurrentMonthElectricity', 'totalAllTimeElectricity', 'totalBillPayersCount', 'yearsList', 'monthsList', 'selectedYear', 'selectedMonthNum', 'selectedMonthStr', 'currentMonthStr'
        ));
    }

    /**
     * Show detailed monthly electricity ledger for a resident (JSON modal or view)
     */
    public function show($id, Request $request)
    {
        $booking = RoomBookingHistory::withTrashed()->with(['floor', 'room', 'user', 'division', 'district', 'thana'])->findOrFail($id);
        $selectedYear = (int)$request->input('year', date('Y'));
        if ($selectedYear < 2026) {
            $selectedYear = 2026;
        }

        $fromMonth = 1;
        $toMonth   = 12;

        if ($request->has('from_month') || $request->has('to_month')) {
            $fromMonth = (int)$request->input('from_month', 1);
            $toMonth   = (int)$request->input('to_month', 12);
        } elseif ($request->has('month')) {
            $monthInput = $request->input('month');
            if ($monthInput !== 'all' && is_numeric($monthInput)) {
                $fromMonth = (int)$monthInput;
                $toMonth   = (int)$monthInput;
            }
        }

        if ($fromMonth < 1) $fromMonth = 1;
        if ($toMonth > 12) $toMonth = 12;
        if ($fromMonth > $toMonth) {
            $temp = $fromMonth;
            $fromMonth = $toMonth;
            $toMonth = $temp;
        }

        // Sync all dues and statuses first
        MonthlyPaymentController::syncFutureDues($booking->id);

        // Fetch monthly payments for this resident in the selected year
        $payments = MonthlyPayment::where('room_booking_history_id', $booking->id)
            ->where('payment_month', 'like', "{$selectedYear}-%")
            ->get()
            ->keyBy('payment_month');

        $currentMonthStr = Carbon::now()->format('Y-m');
        $monthlyLedger = [];
        $rangeTotalElectricity = 0;
        $rangeTotalRent = 0;
        $rangeTotalPaid = 0;

        for ($m = $fromMonth; $m <= $toMonth; $m++) {
            $mPad = str_pad($m, 2, '0', STR_PAD_LEFT);
            $ym = "{$selectedYear}-{$mPad}";
            $payment = $payments->get($ym);

            $carbonDate = Carbon::parse("{$selectedYear}-{$mPad}-01");
            $electBill = $payment ? (float)($payment->electricity_bill ?? 0) : 0;
            $rentAmount = $payment ? (float)$payment->amount : (float)($booking->monthly_amount ?? 0);
            $paidAmount = $payment ? (float)$payment->paid_amount : 0;
            $isPaid = false;
            $isPartial = false;
            if ($payment) {
                if ($payment->status === 'paid' || ($payment->paid_amount > 0 && $payment->due_amount <= 0)) {
                    $isPaid = true;
                } elseif ($payment->status === 'partial' || $payment->paid_amount > 0) {
                    $isPartial = true;
                }
            }

            if ($isPaid) {
                $electStatus = 'paid';
            } elseif ($isPartial) {
                $electStatus = 'partial';
            } elseif ($electBill > 0) {
                $electStatus = 'pending';
            } else {
                $electStatus = $payment ? ($payment->status ?: 'no_record') : ($carbonDate->isFuture() ? 'upcoming' : 'no_record');
            }
            $paymentMethod = ($payment && $paidAmount > 0) ? ($payment->payment_method ?: 'Cash') : '—';
            
            // Payment date only if actually paid or partially paid
            $paymentDate = '—';
            if ($payment && ($isPaid || $isPartial)) {
                if (!empty($payment->note) && preg_match('/\[(.*?)\]\s*Collected/i', $payment->note, $mArr)) {
                    $paymentDate = trim($mArr[1]);
                } else {
                    $paymentDate = $payment->updated_at ? $payment->updated_at->format('d M Y') : ($payment->created_at ? $payment->created_at->format('d M Y') : '—');
                }
            }

            $note = $payment ? ($payment->note ?? '') : '';

            $isCurrentMonth = ($ym === $currentMonthStr);
            $hasActivity = ($electBill > 0 || $paidAmount > 0);

            $rangeTotalElectricity += $electBill;
            $rangeTotalRent += ($payment ? $rentAmount : 0);
            $rangeTotalPaid += $paidAmount;

            $monthlyLedger[] = [
                'month_num'          => $mPad,
                'month_name'         => $carbonDate->format('F'),
                'month_year_label'   => $carbonDate->format('F Y'),
                'electricity_bill'   => $electBill,
                'room_rent'          => $rentAmount,
                'paid_amount'        => $paidAmount,
                'payment_method'     => $paymentMethod,
                'payment_date'       => $paymentDate,
                'status'             => $electStatus,
                'note'               => $note,
                'is_current_month'   => $isCurrentMonth,
                'has_activity'       => $hasActivity,
            ];
        }

        $residentBasic = $this->formatResidentBasic($booking);

        $rangeLabel = ($fromMonth === $toMonth) 
            ? Carbon::parse("{$selectedYear}-" . str_pad($fromMonth, 2, '0', STR_PAD_LEFT) . "-01")->format('F Y')
            : "Jan - Dec {$selectedYear}";

        $data = [
            'basic' => $residentBasic,
            'selected_year' => $selectedYear,
            'range_label' => $rangeLabel,
            'monthly_ledger' => $monthlyLedger,
            'range_totals' => [
                'total_electricity' => $rangeTotalElectricity,
                'total_rent'        => $rangeTotalRent,
                'total_paid'        => $rangeTotalPaid,
            ],
        ];

        if ($request->ajax() || $request->input('format') === 'json') {
            return response()->json([
                'status' => true,
                'data' => $data,
            ]);
        }

        return $this->printStatement($id, $request);
    }

    /**
     * Print Electricity Bill Statement for Resident
     */
    public function printStatement($id, Request $request)
    {
        $booking = RoomBookingHistory::withTrashed()->with(['floor', 'room', 'user', 'division', 'district', 'thana'])->findOrFail($id);
        $selectedYear = (int)$request->input('year', date('Y'));
        if ($selectedYear < 2026) $selectedYear = 2026;

        $fromMonth = 1;
        $toMonth   = 12;

        if ($request->has('from_month') || $request->has('to_month')) {
            $fromMonth = (int)$request->input('from_month', 1);
            $toMonth   = (int)$request->input('to_month', 12);
        } elseif ($request->has('month')) {
            $monthInput = $request->input('month');
            if ($monthInput !== 'all' && is_numeric($monthInput)) {
                $fromMonth = (int)$monthInput;
                $toMonth   = (int)$monthInput;
            }
        }

        if ($fromMonth < 1) $fromMonth = 1;
        if ($toMonth > 12) $toMonth = 12;
        if ($fromMonth > $toMonth) {
            $temp = $fromMonth;
            $fromMonth = $toMonth;
            $toMonth = $temp;
        }

        // Fetch monthly payments for this resident in the selected year
        $payments = MonthlyPayment::where('room_booking_history_id', $booking->id)
            ->where('payment_month', 'like', "{$selectedYear}-%")
            ->get()
            ->keyBy('payment_month');

        $monthlyLedger = [];
        $totalElectricity = 0;

        for ($m = $fromMonth; $m <= $toMonth; $m++) {
            $mPad = str_pad($m, 2, '0', STR_PAD_LEFT);
            $ym = "{$selectedYear}-{$mPad}";
            $payment = $payments->get($ym);

            $carbonDate = Carbon::parse("{$selectedYear}-{$mPad}-01");
            $electBill = $payment ? (float)($payment->electricity_bill ?? 0) : 0;
            $paidAmount = $payment ? (float)$payment->paid_amount : 0;
            $isPaid = false;
            $isPartial = false;
            if ($payment) {
                if ($payment->status === 'paid' || ($payment->paid_amount > 0 && $payment->due_amount <= 0)) {
                    $isPaid = true;
                } elseif ($payment->status === 'partial' || $payment->paid_amount > 0) {
                    $isPartial = true;
                }
            }

            if ($isPaid) {
                $electStatus = 'paid';
            } elseif ($isPartial) {
                $electStatus = 'partial';
            } elseif ($electBill > 0) {
                $electStatus = 'pending';
            } else {
                $electStatus = $payment ? ($payment->status ?: 'no_record') : ($carbonDate->isFuture() ? 'upcoming' : 'no_record');
            }
            
            $paymentDate = '—';
            if ($payment && ($isPaid || $isPartial)) {
                if (!empty($payment->note) && preg_match('/\[(.*?)\]\s*Collected/i', $payment->note, $mArr)) {
                    $paymentDate = trim($mArr[1]);
                } else {
                    $paymentDate = $payment->updated_at ? $payment->updated_at->format('d M Y') : ($payment->created_at ? $payment->created_at->format('d M Y') : '—');
                }
            }

            $totalElectricity += $electBill;

            $monthlyLedger[] = [
                'month_num'          => $mPad,
                'month_name'         => $carbonDate->format('F'),
                'month_year_label'   => $carbonDate->format('F Y'),
                'electricity_bill'   => $electBill,
                'payment_date'       => $paymentDate,
                'status'             => $electStatus,
            ];
        }

        $resident = $this->formatResidentBasic($booking);

        $rangeLabel = ($fromMonth === $toMonth) 
            ? Carbon::parse("{$selectedYear}-" . str_pad($fromMonth, 2, '0', STR_PAD_LEFT) . "-01")->format('F Y')
            : "Jan - Dec {$selectedYear}";

        return view('backend.electricity_bill.print', compact('booking', 'resident', 'monthlyLedger', 'totalElectricity', 'rangeLabel', 'selectedYear'));
    }

    /**
     * AJAX: Get active residents who do NOT yet have an electricity bill for the given month
     */
    public function getResidentsForBilling(Request $request)
    {
        $month = $request->input('month'); // e.g. "2026-10"

        if (!$month || !preg_match('/^\d{4}-\d{2}$/', $month)) {
            return response()->json(['status' => false, 'message' => 'Invalid month format.'], 422);
        }

        // Active residents only (not released, not deleted)
        $activeBookings = RoomBookingHistory::with(['floor', 'room'])
            ->whereNull('today_check_out')
            ->where('status', 1)
            ->latest('id')
            ->get();

        // IDs that already have a bill record for this month
        $alreadyBilled = MonthlyPayment::whereIn('room_booking_history_id', $activeBookings->pluck('id'))
            ->where('payment_month', $month)
            ->whereNotNull('electricity_bill')
            ->where('electricity_bill', '>', 0)
            ->pluck('room_booking_history_id')
            ->toArray();

        // All lifetime electricity bills per resident
        $lifetimeBills = MonthlyPayment::whereIn('room_booking_history_id', $activeBookings->pluck('id'))
            ->selectRaw('room_booking_history_id, SUM(electricity_bill) as total')
            ->groupBy('room_booking_history_id')
            ->pluck('total', 'room_booking_history_id');

        $residents = $activeBookings
            ->filter(fn($b) => !in_array($b->id, $alreadyBilled))
            ->values()
            ->map(function ($booking) use ($lifetimeBills) {
                $basic = $this->formatResidentBasic($booking, 0, (float)($lifetimeBills[$booking->id] ?? 0));
                return [
                    'id'               => $booking->id,
                    'name'             => $basic->name,
                    'phone'            => $basic->phone,
                    'room_seat_full'   => $basic->room_seat_full,
                    'user_type'        => $basic->user_type,
                    'image'            => $basic->image,
                    'lifetime_bill'    => $basic->lifetime_electricity_bill,
                ];
            });

        return response()->json([
            'status'    => true,
            'month'     => $month,
            'residents' => $residents,
        ]);
    }

    /**
     * POST: Bulk create electricity bills for multiple residents for a given month
     * Skips residents whose amount is empty/zero.
     */
    public function bulkCreate(Request $request)
    {
        $month = $request->input('month');
        $bills = $request->input('bills', []); // array of [{booking_id, amount}]

        if (!$month || !preg_match('/^\d{4}-\d{2}$/', $month)) {
            return response()->json(['status' => false, 'message' => 'Invalid month.'], 422);
        }

        if (empty($bills) || !is_array($bills)) {
            return response()->json(['status' => false, 'message' => 'No bill data provided.'], 422);
        }

        $created = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            foreach ($bills as $bill) {
                $bookingId = (int)($bill['booking_id'] ?? 0);
                $amount    = isset($bill['amount']) ? trim($bill['amount']) : '';

                // Skip if amount is empty or zero
                if ($bookingId <= 0 || $amount === '' || (float)$amount <= 0) {
                    $skipped++;
                    continue;
                }

                // Confirm booking exists and is active
                $booking = RoomBookingHistory::whereNull('today_check_out')
                    ->where('status', 1)
                    ->find($bookingId);

                if (!$booking) {
                    $skipped++;
                    continue;
                }

                // Skip if already has a bill for this month (double-check)
                $existing = MonthlyPayment::where('room_booking_history_id', $bookingId)
                    ->where('payment_month', $month)
                    ->first();

                if ($existing && $existing->electricity_bill > 0) {
                    $skipped++;
                    continue;
                }

                if ($existing) {
                    // Update only electricity_bill on existing record
                    $existing->electricity_bill = (float)$amount;
                    $existing->save();
                } else {
                    // Create a new minimal MonthlyPayment record for electricity only
                    MonthlyPayment::create([
                        'room_booking_history_id' => $bookingId,
                        'payment_month'           => $month,
                        'amount'                  => (float)($booking->monthly_amount ?? 0),
                        'electricity_bill'        => (float)$amount,
                        'paid_amount'             => 0,
                        'due_amount'              => (float)($booking->monthly_amount ?? 0) + (float)$amount,
                        'status'                  => 'pending',
                        'received_by'             => auth()->id(),
                    ]);
                }

                $created++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'status'  => true,
            'message' => "Successfully created {$created} electricity bills." . ($skipped > 0 ? " ({$skipped} skipped)" : ''),
            'created' => $created,
            'skipped' => $skipped,
        ]);
    }

    /**
     * Update or set a single resident's electricity bill for a given month
     */
    public function updateSingle(Request $request)
    {
        $request->validate([
            'booking_id'       => 'required|exists:room_booking_histories,id',
            'electricity_bill' => 'required|numeric|min:0',
            'month'            => 'nullable|string|max:7',
        ]);

        $bookingId = $request->input('booking_id');
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $amount = max(0, (float) $request->input('electricity_bill'));

        DB::beginTransaction();
        try {
            $booking = RoomBookingHistory::findOrFail($bookingId);

            $payment = MonthlyPayment::where('room_booking_history_id', $bookingId)
                ->where('payment_month', $month)
                ->first();

            if ($payment) {
                $payment->electricity_bill = $amount;
                $payment->save();
            } else {
                $totalMonthlyRent = (float) ($booking->monthly_amount ?? 0);
                if ($totalMonthlyRent <= 0) {
                    $items = is_string($booking->floor_number_room_number_roomprice)
                        ? (json_decode($booking->floor_number_room_number_roomprice, true) ?? [])
                        : ($booking->floor_number_room_number_roomprice ?? []);
                    $totalMonthlyRent = collect($items)->sum(function ($item) {
                        return (float) ($item['price'] ?? $item['roomprice'] ?? 0);
                    });
                }

                $payment = MonthlyPayment::create([
                    'room_booking_history_id' => $bookingId,
                    'payment_month'           => $month,
                    'amount'                  => $totalMonthlyRent,
                    'electricity_bill'        => $amount,
                    'carried_forward_due'     => 0,
                    'paid_amount'             => 0,
                    'due_amount'              => $totalMonthlyRent + $amount,
                    'payment_method'          => 'unpaid',
                    'status'                  => 'pending',
                ]);
            }

            // Sync all dues and carried forward balances
            MonthlyPaymentController::syncFutureDues($bookingId);

            // Re-fetch lifetime electricity for this booking
            $lifetimeElectricity = (float) MonthlyPayment::where('room_booking_history_id', $bookingId)->sum('electricity_bill');

            DB::commit();

            return response()->json([
                'status'               => true,
                'message'              => 'বিদ্যুৎ বিল সফলভাবে আপডেট করা হয়েছে!',
                'booking_id'           => $bookingId,
                'current_month_bill'   => $amount,
                'lifetime_bill'        => $lifetimeElectricity,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Failed to update electricity bill: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper to format resident information cleanly
     */
    private function formatResidentBasic($booking, $currentMonthBill = 0, $lifetimeBill = 0)
    {
        $items = [];
        if ($booking->floor_number_room_number_roomprice) {
            $items = is_string($booking->floor_number_room_number_roomprice)
                ? (json_decode($booking->floor_number_room_number_roomprice, true) ?? [])
                : ($booking->floor_number_room_number_roomprice ?? []);
        }
        $c = collect($items);

        $formattedRooms = $c->map(function ($item) {
            return $this->formatSeatBadgeItem($item);
        })->filter(function($val) {
            return $val && $val !== '-';
        })->implode(', ');

        if (empty($formattedRooms)) {
            $formattedRooms = $booking->room_number ?? $booking->roomnumber ?? '-';
        }

        $floor = $c->pluck('floornumber')->filter()->unique()->implode(', ');
        if (empty($floor)) {
            $floor = $booking->floornumber ?? '';
        }

        $roomSeatFull = 'রুম ' . ($formattedRooms ?: '-') . ($floor ? " ({$floor})" : '');

        $isDeleted = $booking->trashed();
        $isReleased = !$isDeleted && !empty($booking->today_check_out);
        $isPending = !$isDeleted && !$isReleased && empty($booking->status);
        $isActive = !$isDeleted && !$isReleased && !empty($booking->status);

        $statusLabel = $isDeleted ? 'Deleted' : ($isReleased ? 'Released' : ($isPending ? 'Pending' : 'Active'));

        return (object) [
            'id'                     => $booking->id,
            'name'                   => $booking->full_name ?? ($booking->name ?? 'Resident'),
            'phone'                  => $booking->phone ?? '—',
            'login_code'             => $booking->password ?? ($booking->user->temp_password ?? '123456'),
            'user_type'              => ucfirst($booking->user_type ?? 'student'),
            'email'                  => $booking->email ?? '—',
            'image'                  => $booking->image ?? null,
            'room_seat_full'         => $roomSeatFull,
            'room'                   => $formattedRooms ?: '—',
            'floor'                  => $floor ?: '—',
            'monthly_amount'         => (float)($booking->monthly_amount ?? 0),
            'admission_date'         => $booking->created_at ? $booking->created_at->format('d M Y') : '—',
            'is_active'              => $isActive,
            'is_released'            => $isReleased,
            'is_deleted'             => $isDeleted,
            'is_pending'             => $isPending,
            'status_label'           => $statusLabel,
            'current_month_bill'     => (float)$currentMonthBill,
            'lifetime_electricity_bill' => (float)$lifetimeBill,
        ];
    }

    private function formatSeatBadgeItem($item)
    {
        if (empty($item)) {
            return '-';
        }

        $raw = is_array($item) ? trim($item['roomnumber'] ?? '') : trim($item->roomnumber ?? '');
        if (empty($raw)) {
            return '-';
        }

        $rType = strtolower(is_array($item) ? ($item['room_type'] ?? '') : ($item->room_type ?? ''));
        $isDouble = str_contains($rType, 'double') || str_contains($rType, 'doubel');

        if (preg_match('/^([A-Za-z]+)[\-_](\d+)[\-_]Block(?:[\-_](\d+))?/i', $raw, $m)) {
            $blockLetter = strtoupper($m[1]);
            $roomNum = $m[2];
            $seatSub = $m[3] ?? '';

            if ($isDouble && $seatSub) {
                return "{$roomNum}-Block {$blockLetter}-{$seatSub}";
            }
            return "{$roomNum}-Block {$blockLetter}";
        }

        if (preg_match('/^(\d+)[\-_]Block(?:[\-_]([A-Za-z]+))?(?:[\-_](\d+))?/i', $raw, $m)) {
            $roomNum = $m[1];
            $blockLetter = !empty($m[2]) ? strtoupper($m[2]) : '';
            $seatSub = $m[3] ?? '';

            $blockLabel = $blockLetter ? "Block {$blockLetter}" : "Block";

            if ($isDouble && $seatSub) {
                return "{$roomNum}-{$blockLabel}-{$seatSub}";
            }
            return "{$roomNum}-{$blockLabel}";
        }

        if (!$isDouble && preg_match('/[\-_][12]$/', $raw)) {
            return preg_replace('/[\-_][12]$/', '', $raw);
        }

        return $raw;
    }
}
