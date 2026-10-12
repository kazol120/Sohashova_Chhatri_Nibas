<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Backend\ResidentCheckInOut;
use App\Models\Backend\RoomBookingHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ResidentCheckInOutController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['storeResident', 'checkInResident', 'getResidentStatus']);
    }

    /**
     * Check In / Check Out management (Admin & Resident)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();

        // 1. If Resident / Student User:
        if ($user && !$user->hasRole('admin') && !$user->hasRole('super-admin') && !$user->hasRole('staffs') && !$user->hasRole('manager')) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $user->phone ?? '');
            $last10 = substr($cleanPhone, -10);

            $booking = RoomBookingHistory::whereNull('today_check_out')
                ->whereNull('deleted_at')
                ->where(function ($q) use ($user, $last10) {
                    if (!empty($user->email)) $q->orWhere('email', $user->email);
                    if (!empty($last10)) $q->orWhere('phone', 'like', "%{$last10}%");
                })
                ->latest()
                ->first();

            $activeCheckout = null;
            if ($booking) {
                $activeCheckout = ResidentCheckInOut::where('room_booking_history_id', $booking->id)
                    ->where('status', 0)
                    ->latest('check_out_date')
                    ->first();
            } elseif ($user) {
                $activeCheckout = ResidentCheckInOut::where('user_id', $user->id)
                    ->where('status', 0)
                    ->latest('check_out_date')
                    ->first();
            }

            $query = ResidentCheckInOut::query();
            if ($booking) {
                $query->where('room_booking_history_id', $booking->id);
            } else {
                $query->where('user_id', $user->id);
            }

            $myLogs = $query->orderBy('check_out_date', 'desc')->paginate(15);

            return view('backend.check_in_out.user_index', compact('booking', 'user', 'activeCheckout', 'myLogs'));
        }

        // 2. Admin & Staff summary statistics
        $totalActiveResidents = RoomBookingHistory::whereNull('today_check_out')
            ->whereNull('deleted_at')
            ->count();


        $currentlyOutsideCount = ResidentCheckInOut::where('status', 0)->whereDate('check_out_date', '<=', $today)->count();
        $currentlyInsideCount = max(0, $totalActiveResidents - $currentlyOutsideCount);
        $returnedTodayCount = ResidentCheckInOut::where('status', 1)
            ->whereDate('check_in_date', $today)
            ->count();
        $overdueCount = ResidentCheckInOut::where('status', 0)
            ->whereDate('check_out_date', '<=', $today)
            ->whereNotNull('expected_return_date')
            ->whereDate('expected_return_date', '<', $today)
            ->count();

        // 2. Query logs — show one row per resident (latest record per booking session only)
        $latestIdsSub = DB::table('resident_check_in_outs')
            ->selectRaw('MAX(id) as id')
            ->groupBy('room_booking_history_id');

        $query = ResidentCheckInOut::with(['booking', 'user'])
            ->whereIn('id', $latestIdsSub);

        if ($request->filled('status')) {
            if ($request->status === 'outside') {
                $query->where('status', 0);
            } elseif ($request->status === 'returned' || $request->status === 'inside') {
                $query->where('status', 1);
            } elseif ($request->status === 'overdue') {
                $query->where('status', 0)
                      ->whereNotNull('expected_return_date')
                      ->whereDate('expected_return_date', '<', $today);
            }
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('resident_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('floor_room_seat', 'like', "%{$search}%")
                  ->orWhere('leave_reason', 'like', "%{$search}%")
                  ->orWhere('emergency_contact', 'like', "%{$search}%");
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('check_out_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('check_out_date', '<=', $request->to_date);
        }

        // Order: Currently outside (status 0) first, then latest check_out_date
        $logs = $query->orderBy('status', 'asc')
                      ->orderBy('check_out_date', 'desc')
                      ->paginate(20)
                      ->withQueryString();

        // Active resident list for manual checkout modal
        $activeBookings = RoomBookingHistory::whereNull('today_check_out')
            ->whereNull('deleted_at')
            ->orderBy('full_name', 'asc')
            ->get();

        // Mark all unseen logs as seen by admin when viewing this page
        ResidentCheckInOut::where('is_seen_by_admin', false)->update(['is_seen_by_admin' => true]);

        return view('backend.check_in_out.index', compact(
            'logs',
            'totalActiveResidents',
            'currentlyOutsideCount',
            'currentlyInsideCount',
            'returnedTodayCount',
            'overdueCount',
            'activeBookings'
        ));
    }

    /**
     * Store checkout entry by Admin
     */
    public function storeAdmin(Request $request)
    {
        $request->validate([
            'booking_id'           => 'required|exists:room_booking_histories,id',
            'check_out_date'       => 'required',
            'expected_return_date' => 'nullable|date',
            'leave_reason'         => 'nullable|string|max:255',
            'emergency_contact'    => 'required|string|min:10|max:20',
            'admin_notes'          => 'nullable|string|max:500',
        ], [
            'emergency_contact.required' => 'অভিভাবক বা জরুরি যোগাযোগের নম্বর আবশ্যক (Emergency contact number is required)।',
            'emergency_contact.min'      => 'অনুগ্রহ করে সঠিক মোবাইল নম্বর প্রদান করুন।',
        ]);

        $booking = RoomBookingHistory::findOrFail($request->booking_id);


        // Check if already checked out
        $existing = ResidentCheckInOut::where('room_booking_history_id', $booking->id)
            ->where('status', 0)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => "শিক্ষার্থী '{$booking->full_name}' ইতিমধ্যে চেক আউট অবস্থায় আছেন।"
            ], 422);
        }

        // Parse Floor Room Seat
        $floorRoomSeat = $this->getFloorRoomSeatString($booking);

        // Find associated user if any
        $user = User::where('phone', $booking->phone)
            ->orWhere('email', $booking->email)
            ->first();

        $checkOutDateTime = Carbon::parse($request->check_out_date);

        $record = ResidentCheckInOut::create([
            'user_id'                 => $user ? $user->id : null,
            'room_booking_history_id' => $booking->id,
            'resident_name'           => $booking->full_name,
            'phone'                   => $booking->phone,
            'floor_room_seat'         => $floorRoomSeat,
            'check_out_date'          => $checkOutDateTime,
            'expected_return_date'    => $request->expected_return_date ? Carbon::parse($request->expected_return_date)->toDateString() : null,
            'leave_reason'            => $request->leave_reason ?: 'বাড়ি যাওয়া / ছুটি',
            'emergency_contact'       => $request->emergency_contact ?: ($booking->guardian_number ?? $booking->parents_phone_number),
            'status'                  => 0, // checked out
            'is_seen_by_admin'        => true,
            'created_by'              => 'admin',
            'admin_notes'             => $request->admin_notes,
        ]);

        // Auto Meal OFF from check_out_date
        if ($user) {
            $cDate = $checkOutDateTime->format('Y-m-d');
            \App\Models\Backend\Meal::where('user_id', $user->id)
                ->whereDate('date', '>=', $cDate)
                ->where(function($q) {
                    $q->where('note', 'like', '%Auto%')
                      ->orWhereNull('note')
                      ->orWhere('note', 'Auto Full Meal');
                })
                ->update([
                    'half_meal' => 0,
                    'full_meal' => 0,
                    'is_off'    => 1,
                    'note'      => 'Auto Meal OFF (Checked Out / Leave)',
                ]);
        }

        return response()->json([
            'success' => true,
            'message' => "শিক্ষার্থী '{$booking->full_name}' এর চেক-আউট সফলভাবে এন্ট্রি হয়েছে (মিল স্বয়ংক্রিয়ভাবে বন্ধ করা হয়েছে)।"
        ]);
    }

    /**
     * Mark Check In / Return by Admin
     */
    public function checkInAdmin(Request $request, $id)
    {
        $record = ResidentCheckInOut::findOrFail($id);

        if ($record->status == 1) {
            return response()->json([
                'success' => false,
                'message' => 'এই শিক্ষার্থী ইতিমধ্যে হোস্টেলে ফিরে এসেছেন।'
            ], 422);
        }

        $checkInDateTime = $request->filled('check_in_date') 
            ? Carbon::parse($request->check_in_date) 
            : Carbon::now();

        $record->update([
            'check_in_date'    => $checkInDateTime,
            'status'           => 1, // checked in
            'admin_notes'      => $request->admin_notes ?: $record->admin_notes,
            'is_seen_by_admin' => true,
        ]);

        // Auto Meal remains OFF on check-in day, resumes from tomorrow
        $user = $record->user ?? \App\Models\User::find($record->user_id);
        if (!$user && $record->phone) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $record->phone);
            $last10 = substr($cleanPhone, -10);
            $user = \App\Models\User::where('phone', 'like', "%{$last10}%")->first();
        }
        if ($user) {
            $todayDate = $checkInDateTime->format('Y-m-d');
            $todayMeal = \App\Models\Backend\Meal::where('user_id', $user->id)->whereDate('date', $todayDate)->first();
            if (!$todayMeal) {
                \App\Models\Backend\Meal::create([
                    'user_id'   => $user->id,
                    'date'      => $todayDate,
                    'half_meal' => 0,
                    'full_meal' => 0,
                    'is_off'    => 1,
                    'made_by'   => auth()->id() ?? 1,
                    'note'      => 'Auto Meal OFF (Checked Out / Leave)',
                ]);
            } else {
                $todayMeal->update([
                    'half_meal' => 0,
                    'full_meal' => 0,
                    'is_off'    => 1,
                    'note'      => 'Auto Meal OFF (Checked Out / Leave)',
                ]);
            }
            app(\App\Services\MealService::class)->ensureAutoMealGeneratedForUsers(collect([$user]), $todayDate);
        }

        return response()->json([
            'success' => true,
            'message' => "শিক্ষার্থী '{$record->resident_name}' এর চেক-ইন সফলভাবে সম্পন্ন হয়েছে (পরদিন থেকে মিল পুনরায় স্বয়ংক্রিয়ভাবে চালু হবে)।"
        ]);
    }

    /**
     * Delete log record by Admin
     */
    public function destroy($id)
    {
        $record = ResidentCheckInOut::findOrFail($id);
        $record->delete();

        return response()->json([
            'success' => true,
            'message' => 'রেকর্ডটি সফলভাবে মুছে ফেলা হয়েছে।'
        ]);
    }

    /**
     * Top Navbar API - Check pending / active checkouts & return count + latest items
     */
    public function apiCheckPending()
    {
        $unseenCount = ResidentCheckInOut::where('is_seen_by_admin', false)->count();
        $currentlyOutsideCount = ResidentCheckInOut::where('status', 0)->count();

        $recentLogs = ResidentCheckInOut::latest('updated_at')
            ->take(6)
            ->get()
            ->map(function ($log) {
                return [
                    'id'                   => $log->id,
                    'resident_name'        => $log->resident_name,
                    'phone'                => $log->phone,
                    'floor_room_seat'      => $log->floor_room_seat ?: 'হোস্টেল সীট',
                    'check_out_date_human' => $log->check_out_date ? $log->check_out_date->diffForHumans() : '',
                    'check_out_date_fmt'   => $log->check_out_date ? $log->check_out_date->format('d M, h:i A') : '',
                    'expected_return_fmt'  => $log->expected_return_date ? Carbon::parse($log->expected_return_date)->format('d M, Y') : null,
                    'leave_reason'         => $log->leave_reason ?: 'বাড়ি যাওয়া',
                    'status'               => $log->status,
                    'is_seen_by_admin'     => $log->is_seen_by_admin,
                    'created_by'           => $log->created_by,
                ];
            });

        return response()->json([
            'success'                => true,
            'unseen_count'           => $unseenCount,
            'currently_outside_count'=> $currentlyOutsideCount,
            'recent_logs'            => $recentLogs,
        ]);
    }

    /**
     * Mark notification as seen
     */
    public function markSeen(Request $request, $id = null)
    {
        if ($id && $id !== 'all') {
            ResidentCheckInOut::where('id', $id)->update(['is_seen_by_admin' => true]);
        } else {
            ResidentCheckInOut::where('is_seen_by_admin', false)->update(['is_seen_by_admin' => true]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Student Store Check Out from Portal
     */
    public function storeResident(Request $request)
    {
        $request->validate([
            'check_out_date'       => 'required',
            'expected_return_date' => 'nullable|date',
            'leave_reason'         => 'required|string|max:255',
            'emergency_contact'    => 'required|string|min:10|max:20',
            'phone'                => 'nullable|string',
        ], [
            'emergency_contact.required' => 'অভিভাবক বা জরুরি যোগাযোগের নম্বর আবশ্যক (Emergency contact number is required)।',
            'emergency_contact.min'      => 'অনুগ্রহ করে সঠিক মোবাইল নম্বর প্রদান করুন।',
            'leave_reason.required'      => 'বাড়ি যাওয়ার কারণ উল্লেখ করুন।',
        ]);


        $booking = $this->resolveCurrentResidentBooking($request);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'কোনো সক্রিয় রেসিডেন্ট একাউন্ট খুঁজে পাওয়া যায়নি।'
            ], 404);
        }

        // Check if already checked out
        $existing = ResidentCheckInOut::where('room_booking_history_id', $booking->id)
            ->where('status', 0)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'আপনি ইতিমধ্যে চেক আউট (বাইরে) অবস্থায় আছেন।'
            ], 422);
        }

        $user = Auth::user();
        $floorRoomSeat = $this->getFloorRoomSeatString($booking);
        $checkOutDateTime = Carbon::parse($request->check_out_date);

        $record = ResidentCheckInOut::create([
            'user_id'                 => $user ? $user->id : null,
            'room_booking_history_id' => $booking->id,
            'resident_name'           => $booking->full_name,
            'phone'                   => $booking->phone,
            'floor_room_seat'         => $floorRoomSeat,
            'check_out_date'          => $checkOutDateTime,
            'expected_return_date'    => $request->expected_return_date ? Carbon::parse($request->expected_return_date)->toDateString() : null,
            'leave_reason'            => $request->leave_reason,
            'emergency_contact'       => $request->emergency_contact ?: ($booking->guardian_number ?? $booking->parents_phone_number),
            'status'                  => 0, // checked out
            'is_seen_by_admin'        => false,
            'created_by'              => 'resident',
        ]);

        // Auto Meal OFF from check_out_date
        if ($user) {
            $cDate = $checkOutDateTime->format('Y-m-d');
            \App\Models\Backend\Meal::where('user_id', $user->id)
                ->whereDate('date', '>=', $cDate)
                ->where(function($q) {
                    $q->where('note', 'like', '%Auto%')
                      ->orWhereNull('note')
                      ->orWhere('note', 'Auto Full Meal');
                })
                ->update([
                    'half_meal' => 0,
                    'full_meal' => 0,
                    'is_off'    => 1,
                    'note'      => 'Auto Meal OFF (Checked Out / Leave)',
                ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'আপনার চেক আউট সফলভাবে সম্পন্ন হয়েছে (মিল স্বয়ংক্রিয়ভাবে বন্ধ করা হয়েছে)। শুভ যাত্রা!',
            'data'    => $record,
        ]);
    }

    /**
     * Student Mark Check In from Portal
     */
    public function checkInResident(Request $request)
    {
        $booking = $this->resolveCurrentResidentBooking($request);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'কোনো সক্রিয় রেসিডেন্ট একাউন্ট খুঁজে পাওয়া যায়নি।'
            ], 404);
        }

        $record = ResidentCheckInOut::where('room_booking_history_id', $booking->id)
            ->where('status', 0)
            ->latest('check_out_date')
            ->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'আপনার কোনো সক্রিয় চেক-আউট পাওয়া যায়নি। আপনি ইতিমধ্যে হোস্টেলে উপস্থিত আছেন।'
            ], 422);
        }

        $record->update([
            'check_in_date'    => Carbon::now(),
            'status'           => 1, // returned
            'is_seen_by_admin' => false,
        ]);

        // Auto Meal remains OFF on check in day, resumes from tomorrow
        $user = Auth::user() ?? ($record->user ?? \App\Models\User::find($record->user_id));
        if (!$user && $booking->phone) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $booking->phone);
            $last10 = substr($cleanPhone, -10);
            $user = \App\Models\User::where('phone', 'like', "%{$last10}%")->first();
        }
        if ($user) {
            $todayDate = Carbon::today()->format('Y-m-d');
            $todayMeal = \App\Models\Backend\Meal::where('user_id', $user->id)->whereDate('date', $todayDate)->first();
            if (!$todayMeal) {
                \App\Models\Backend\Meal::create([
                    'user_id'   => $user->id,
                    'date'      => $todayDate,
                    'half_meal' => 0,
                    'full_meal' => 0,
                    'is_off'    => 1,
                    'made_by'   => $user->id,
                    'note'      => 'Auto Meal OFF (Checked Out / Leave)',
                ]);
            } else {
                $todayMeal->update([
                    'half_meal' => 0,
                    'full_meal' => 0,
                    'is_off'    => 1,
                    'note'      => 'Auto Meal OFF (Checked Out / Leave)',
                ]);
            }
            app(\App\Services\MealService::class)->ensureAutoMealGeneratedForUsers(collect([$user]), $todayDate);
        }

        return response()->json([
            'success' => true,
            'message' => 'হোস্টেলে ফিরে আসায় স্বাগতম! আপনার চেক ইন সফল হয়েছে (পরদিন থেকে মিল পুনরায় স্বয়ংক্রিয়ভাবে চালু হবে)।',
            'data'    => $record,
        ]);
    }

    /**
     * Get single resident's current check in/out state & history
     */
    public function getResidentStatus(Request $request)
    {
        $booking = $this->resolveCurrentResidentBooking($request);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'No resident record found'
            ], 404);
        }

        $activeCheckout = ResidentCheckInOut::where('room_booking_history_id', $booking->id)
            ->where('status', 0)
            ->latest('check_out_date')
            ->first();

        $isCurrentlyOutside = false;
        $isUpcoming = false;
        if ($activeCheckout) {
            if (\Carbon\Carbon::parse($activeCheckout->check_out_date)->startOfDay()->lte(\Carbon\Carbon::today())) {
                $isCurrentlyOutside = true;
            } else {
                $isUpcoming = true;
            }
        }

        $history = ResidentCheckInOut::where('room_booking_history_id', $booking->id)
            ->latest('check_out_date')
            ->take(10)
            ->get();

        return response()->json([
            'success'               => true,
            'is_checked_out'        => $isCurrentlyOutside,
            'is_upcoming_checkout'  => $isUpcoming,
            'active_checkout'       => $activeCheckout,
            'history'               => $history,
        ]);
    }

    /**
     * Helper to resolve active booking
     */
    protected function resolveCurrentResidentBooking(Request $request)
    {
        $user = Auth::user();
        $phone = $request->phone;

        if ($user) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $user->phone ?? '');
            $last10 = substr($cleanPhone, -10);

            $booking = RoomBookingHistory::whereNull('today_check_out')
                ->whereNull('deleted_at')
                ->where(function ($q) use ($user, $last10) {
                    if (!empty($user->email)) $q->orWhere('email', $user->email);
                    if (!empty($last10)) $q->orWhere('phone', 'like', "%{$last10}%");
                })
                ->latest()
                ->first();

            if ($booking) return $booking;
        }

        if (!empty($phone)) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
            $last10 = substr($cleanPhone, -10);

            return RoomBookingHistory::whereNull('today_check_out')
                ->whereNull('deleted_at')
                ->where('phone', 'like', "%{$last10}%")
                ->latest()
                ->first();
        }

        return null;
    }

    /**
     * Helper to format Floor, Room, Seat string
     */
    protected function getFloorRoomSeatString($booking)
    {
        $items = [];
        if (is_string($booking->floor_number_room_number_roomprice)) {
            $items = json_decode($booking->floor_number_room_number_roomprice, true) ?? [];
        } elseif (is_array($booking->floor_number_room_number_roomprice)) {
            $items = $booking->floor_number_room_number_roomprice;
        }

        if (!empty($items)) {
            $details = [];
            foreach ($items as $item) {
                $f = $item['floornumber'] ?? '';
                $r = $item['roomnumber'] ?? '';
                $details[] = "Floor: {$f}, Room/Seat: {$r}";
            }
            return implode(' | ', $details);
        }

        $floor = $booking->floornumber ?? ($booking->floor->name ?? '');
        $room = $booking->room_number ?? ($booking->room->room_number ?? '');
        return trim("{$floor} - {$room}", " -");
    }

    /**
     * Get single resident's complete Check In / Check Out leave history for Modal popup
     */
    public function getResidentHistory(Request $request, $id)
    {
        $log = ResidentCheckInOut::findOrFail($id);

        $query = ResidentCheckInOut::query();
        if ($log->room_booking_history_id) {
            // Scope strictly to this booking session only — avoids cross-session duplicates
            $query->where('room_booking_history_id', $log->room_booking_history_id);
        } elseif ($log->phone) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $log->phone);
            $last10 = substr($cleanPhone, -10);
            $query->where('phone', 'like', "%{$last10}%");
        } else {
            $query->where('id', $log->id);
        }

        $history = $query->orderBy('check_out_date', 'desc')->get();

        $totalLeaves = $history->count();
        $completedLeaves = $history->where('status', 1)->count();
        $activeLeave = $history->firstWhere('status', 0);

        return response()->json([
            'success'          => true,
            'resident_name'    => $log->resident_name,
            'phone'            => $log->phone,
            'floor_room_seat'  => $log->floor_room_seat ?: 'Hostel Seat',
            'total_leaves'     => $totalLeaves,
            'completed_leaves' => $completedLeaves,
            'has_active_leave' => $activeLeave ? true : false,
            'logs'             => $history->map(function($item) {
                $isFuture = $item->status == 0 && Carbon::parse($item->check_out_date)->startOfDay()->gt(Carbon::today());
                return [
                    'id'                   => $item->id,
                    'check_out_date'       => $item->check_out_date ? $item->check_out_date->format('d M, Y') : '-',
                    'expected_return_date' => $item->expected_return_date ? Carbon::parse($item->expected_return_date)->format('d M, Y') : '-',
                    'check_in_date'        => $item->check_in_date ? $item->check_in_date->format('d M, Y') : null,
                    'leave_reason'         => $item->leave_reason ?: 'Going Home',
                    'emergency_contact'    => $item->emergency_contact ?: '-',
                    'status'               => $item->status,
                    'is_future'            => $isFuture,
                    'created_by'           => $item->created_by === 'resident' ? 'Resident' : 'Admin',
                    'admin_notes'          => $item->admin_notes,
                ];
            })
        ]);
    }
}
