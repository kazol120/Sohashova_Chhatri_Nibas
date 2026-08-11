<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\MealService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MealController extends Controller
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
        $data['page_title'] = 'Meal Management';
        $data['users'] = $this->userService->usersWithRole();
        $data['selectedDate'] = $request->date ? Carbon::parse($request->date)->format('Y-m-d') : now()->format('Y-m-d');

        $mealResult = $this->mealService->todayOrPreviousMealStatus($data['selectedDate']);

        $data['meals'] = $mealResult['meals'];
        $data['used_date'] = $mealResult['used_date'];
        $data['is_fallback'] = $mealResult['is_fallback'];
        $data['summary'] = $this->mealService->mealStatusSummary($data['users'], $data['meals']);

        return view('backend.meal.index', $data);
    }

    public function create()
    {
        $data['page_title'] = 'Today Meal Create';
        $currentUser = auth()->user();
        $data['canSaveMeal'] = true;
        $data['dueWarningMessage'] = null;

        $data['depositWarningMessage'] = null;

        if (!$currentUser->hasRole('admin') && !$currentUser->hasRole('staffs')) {
            $data['users'] = collect([$currentUser]);

            $depositInfo = $this->mealService->getUserMealDepositBalance($currentUser->id);
            if ($depositInfo['is_zero_deposit']) {
                $data['depositWarningMessage'] = $depositInfo['warning_message'];
            }

            $cleanPhone = preg_replace('/[^0-9]/', '', $currentUser->phone ?? '');
            $cleanPhone10 = strlen($cleanPhone) >= 6 ? (strlen($cleanPhone) > 10 ? substr($cleanPhone, -10) : $cleanPhone) : '';

            $userBooking = \App\Models\Backend\RoomBookingHistory::where('status', 0)
                ->where(function ($q) use ($currentUser, $cleanPhone10) {
                    if (!empty($cleanPhone10)) {
                        $q->whereRaw("REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+', '') LIKE ?", ["%{$cleanPhone10}%"]);
                    } elseif (!empty($currentUser->email)) {
                        $q->where('email', $currentUser->email);
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })->latest()->first();

            if ($userBooking) {
                $outstandingDue = \App\Models\Backend\MonthlyPayment::where('room_booking_history_id', $userBooking->id)
                    ->where('status', '!=', 'paid')
                    ->sum('due_amount');

                if ($outstandingDue > 0) {
                    $data['canSaveMeal'] = false;
                    $data['dueWarningMessage'] = "⚠️ আপনার ভাড়ার বকেয়া (Outstanding Balance: ৳ " . number_format($outstandingDue) . ") রয়েছে। মিল পরিবর্তন করতে অনুগ্রহ করে আগে বকেয়া পরিশোধ করুন।";
                }
            }
        } else {
            $data['users'] = $this->userService->usersWithRole();
        }

        $nowTime = now()->format('H:i');
        $data['isLunchCutoffPassed'] = $nowTime >= '10:00';
        $data['isDinnerCutoffPassed'] = $nowTime >= '16:00';
        $data['isAdminOrStaff'] = $currentUser->hasRole('admin') || $currentUser->hasRole('staffs');

        $mealResult = $this->mealService->todayOrPreviousMealStatus(now());
        $data['meals'] = $mealResult['meals'];
        $data['used_date'] = $mealResult['used_date'];
        $data['is_fallback'] = $mealResult['is_fallback'];
        $data['todaySummary'] = $this->mealService->todayMealSummary(now());
        return view('backend.meal.create', $data);
    }

    public function mealHistory(Request $request)
    {
        $data['page_title'] = 'Meal History';
        $data['users'] = $this->userService->usersWithRole();

        $selectedMonth = $request->month
            ? Carbon::parse($request->month . '-01')->format('Y-m')
            : now()->format('Y-m');

        $mealHistory = $this->mealService->monthlyMealHistory($selectedMonth, $data['users']);

        $data['selectedMonth'] = $mealHistory['selectedMonth'];
        $data['half_rate'] = $mealHistory['half_rate'];
        $data['full_rate'] = $mealHistory['full_rate'];
        $data['mealSummaryByUser'] = $mealHistory['mealSummaryByUser'];
        $data['summary'] = $mealHistory['summary'];

        return view('backend.meal.meal-history', $data);
    }

    public function store(Request $request)
    {
        $currentUser = auth()->user();
        if (!$currentUser->hasRole('admin') && !$currentUser->hasRole('staffs')) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $currentUser->phone ?? '');
            $cleanPhone10 = strlen($cleanPhone) >= 6 ? (strlen($cleanPhone) > 10 ? substr($cleanPhone, -10) : $cleanPhone) : '';

            $userBooking = \App\Models\Backend\RoomBookingHistory::where('status', 0)
                ->where(function ($q) use ($currentUser, $cleanPhone10) {
                    if (!empty($cleanPhone10)) {
                        $q->whereRaw("REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+', '') LIKE ?", ["%{$cleanPhone10}%"]);
                    } elseif (!empty($currentUser->email)) {
                        $q->where('email', $currentUser->email);
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })->latest()->first();

            if ($userBooking) {
                $outstandingDue = \App\Models\Backend\MonthlyPayment::where('room_booking_history_id', $userBooking->id)
                    ->where('status', '!=', 'paid')
                    ->sum('due_amount');

                if ($outstandingDue > 0) {
                    return redirect()->back()->with('error', 'আপনার ভাড়ার বকেয়া রয়েছে (৳ ' . number_format($outstandingDue) . ')। বকেয়া পরিশোধ না করা পর্যন্ত মিল সেভ করা যাবে না।');
                }
            }
        }

        $request->validate([
            'date' => 'required|date',
            'meal' => 'required|array',
            'meal.*.half_meal' => 'nullable|in:0,1',
            'meal.*.full_meal' => 'nullable|in:0,1',
        ]);
        
        $result = $this->mealService->mealStore($request);
        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }
        return redirect()->back()->with('success', 'Today meal saved successfully.');
    }

    public function toggleMealOff(Request $request)
    {
        $request->validate([
            'date'   => 'nullable|date',
            'is_off' => 'required|boolean',
            'user_id'=> 'nullable|exists:users,id',
        ]);

        $currentUser = auth()->user();
        $targetUserId = ($currentUser->hasRole('admin') || $currentUser->hasRole('staffs')) && $request->filled('user_id')
            ? $request->user_id
            : $currentUser->id;

        $date = $request->date ?: now()->format('Y-m-d');
        $isOff = $request->is_off ? 1 : 0;

        if (!$currentUser->hasRole('admin') && !$currentUser->hasRole('staffs')) {
            $todayStr = now()->format('Y-m-d');
            if ($date < $todayStr) {
                $msg = 'অতীতের (বিগত দিনের) মিলের তথ্য পরিবর্তন করা সম্ভব নয়।';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return redirect()->back()->with('error', $msg);
            }
            if ($date === $todayStr && now()->format('H:i') >= '16:00') {
                $msg = 'দুঃখিত, আজ মিল বন্ধ বা চালু করার সময় (বিকাল ৪:০০ টা) পার হয়ে গেছে।';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return redirect()->back()->with('error', $msg);
            }
        }

        $meal = $this->mealService->toggleMealOffStatus($targetUserId, $date, $isOff);

        $statusText = $isOff ? 'বন্ধ' : 'চালু';
        $message = "{$date} তারিখের মিল সফলভাবে {$statusText} করা হয়েছে।";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'is_off'  => $isOff,
                'meal'    => $meal,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    public function updateMealStatus(Request $request)
    {
        $request->validate([
            'date'       => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:date',
            'total_days' => 'nullable|integer|min:1|max:60',
            'meal_type'  => 'required|in:full,half_day,half_night,off',
            'user_id'    => 'nullable|exists:users,id',
        ]);

        $currentUser = auth()->user();
        $targetUserId = ($currentUser->hasRole('admin') || $currentUser->hasRole('staffs')) && $request->filled('user_id')
            ? $request->user_id
            : $currentUser->id;

        $startDate = $request->date ?: now()->format('Y-m-d');
        $totalDays = (int)($request->total_days ?: 1);
        $endDate = $request->end_date ?: \Carbon\Carbon::parse($startDate)->addDays($totalDays - 1)->format('Y-m-d');
        $mealType = $request->meal_type;

        $labels = [
            'full'       => 'ফুল মিল (Full Meal)',
            'half_day'   => 'দিনের হাফ মিল (দুপুরের মিল চালু / রাতের মিল বন্ধ)',
            'half_night' => 'রাতের হাফ মিল (রাতের মিল চালু / দুপুরের মিল বন্ধ)',
            'off'        => 'মিল বন্ধ (Meal OFF)',
        ];
        $statusLabel = $labels[$mealType] ?? 'আপডেট';

        if (!$currentUser->hasRole('admin') && !$currentUser->hasRole('staffs')) {
            $todayStr = now()->format('Y-m-d');
            if ($startDate < $todayStr) {
                $msg = 'অতীতের (বিগত দিনের) মিলের তথ্য পরিবর্তন করা সম্ভব নয়।';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return redirect()->back()->with('error', $msg);
            }

            $nowTime = now()->format('H:i');
            if ($startDate === $todayStr) {
                if ($mealType === 'half_day' && $nowTime >= '10:00') {
                    $msg = 'দুঃখিত, আজ দিনের (লাঞ্চ) মিল পরিবর্তন করার সময় (সকাল ১০:০০ টা) পার হয়ে গেছে।';
                    if ($request->wantsJson() || $request->ajax()) {
                        return response()->json(['success' => false, 'message' => $msg], 422);
                    }
                    return redirect()->back()->with('error', $msg);
                }
                if (in_array($mealType, ['half_night', 'full', 'off']) && $nowTime >= '16:00') {
                    $msg = 'দুঃখিত, আজ রাতের (ডিনার) মিল বা পুরো মিল অফ/অন করার সময় (বিকাল ৪:০০ টা) পার হয়ে গেছে।';
                    if ($request->wantsJson() || $request->ajax()) {
                        return response()->json(['success' => false, 'message' => $msg], 422);
                    }
                    return redirect()->back()->with('error', $msg);
                }
            }

            $depositInfo = $this->mealService->getUserMealDepositBalance($targetUserId);
            if ($depositInfo['is_zero_deposit']) {
                $msg = 'আপনার মেল ডিপোজিট ব্যালেন্স শূন্য বা অপ্রতুল। মেল চালু বা পরিবর্তন করতে ডিপোজিট রিচার্জ করুন।';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return redirect()->back()->with('error', $msg);
            }

            // Create/Update Meal Request for Admin Approval with Date Range
            $mealReq = \App\Models\Backend\MealRequest::updateOrCreate(
                [
                    'user_id' => $targetUserId,
                    'date'    => $startDate,
                ],
                [
                    'end_date'      => $endDate,
                    'total_days'    => $totalDays,
                    'request_type'  => $mealType,
                    'status'        => 0, // Pending Admin Approval
                    'user_notified' => 0,
                    'admin_note'    => null,
                ]
            );

            $rangeText = $totalDays > 1 ? "({$startDate} হতে {$endDate} পর্যন্ত মোট {$totalDays} দিন)" : "({$startDate})";
            $msg = "{$rangeText} '{$statusLabel}' অনুরোধটি এডমিনের অনুমোদনের জন্য পাঠানো হয়েছে। এডমিন কনফার্ম করলে আপডেট দেখতে পাবেন।";

            session()->flash('success', $msg);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success'      => true,
                    'message'      => $msg,
                    'is_pending'   => true,
                    'meal_type'    => $mealType,
                    'redirect_url' => route('dashboard'),
                ]);
            }
            return redirect()->route('dashboard')->with('success', $msg);
        }

        // Direct update for Admin/Staff across date range
        $cStart = \Carbon\Carbon::parse($startDate);
        $cEnd = \Carbon\Carbon::parse($endDate);
        for ($d = $cStart->copy(); $d->lte($cEnd); $d->addDay()) {
            $this->mealService->updateUserMealStatus($targetUserId, $d->format('Y-m-d'), $mealType);
        }

        $rangeText = $totalDays > 1 ? "({$startDate} হতে {$endDate} পর্যন্ত মোট {$totalDays} দিন)" : "({$startDate})";
        $message = "{$rangeText} মিল সফলভাবে '{$statusLabel}' হিসেবে সেভ করা হয়েছে।";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'   => true,
                'message'   => $message,
                'meal_type' => $mealType,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    public function mealRequestsIndex(Request $request)
    {
        $data['page_title'] = "Meal Change Requests (মিল পরিবর্তনের অনুরোধসমূহ)";
        $query = \App\Models\Backend\MealRequest::with('user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $data['mealRequests'] = $query->paginate(15);
        return view('backend.meal.requests', $data);
    }

    public function approveMealRequest($id)
    {
        $mealReq = \App\Models\Backend\MealRequest::findOrFail($id);
        $mealReq->status = 1; // Approved
        $mealReq->user_notified = 0; // Show confirmation message to user
        $mealReq->save();

        // Perform actual meal status update in DB across date range
        $startDate = \Carbon\Carbon::parse($mealReq->date);
        $endDate = $mealReq->end_date ? \Carbon\Carbon::parse($mealReq->end_date) : $startDate;

        for ($d = $startDate->copy(); $d->lte($endDate); $d->addDay()) {
            $this->mealService->updateUserMealStatus($mealReq->user_id, $d->format('Y-m-d'), $mealReq->request_type);
        }

        $labels = [
            'full'       => 'ফুল মিল (Full Meal)',
            'half_day'   => 'দিনের হাফ মিল',
            'half_night' => 'রাতের হাফ মিল',
            'off'        => 'মিল বন্ধ (Meal OFF)',
        ];
        $statusLabel = $labels[$mealReq->request_type] ?? $mealReq->request_type;
        $rangeInfo = $mealReq->total_days > 1 ? "({$mealReq->date} হতে {$mealReq->end_date} মোট {$mealReq->total_days} দিন)" : "({$mealReq->date})";

        $msg = "ইউজার {$mealReq->user->name}-এর {$rangeInfo} '{$statusLabel}' অনুরোধটি সফলভাবে অনুমোদন করা হয়েছে।";

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return redirect()->back()->with('success', $msg);
    }

    public function rejectMealRequest(Request $request, $id)
    {
        $mealReq = \App\Models\Backend\MealRequest::findOrFail($id);
        $mealReq->status = 2; // Rejected
        $mealReq->user_notified = 0; // Show rejection message to user
        if ($request->filled('admin_note')) {
            $mealReq->admin_note = $request->admin_note;
        }
        $mealReq->save();

        $msg = "ইউজার {$mealReq->user->name}-এর মিল অনুরোধটি প্রত্যাখ্যান করা হয়েছে।";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return redirect()->back()->with('success', $msg);
    }

    public function checkUserMealNotifs()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'has_notifs' => false, 'notifications' => []]);
        }

        $todayStr = \Carbon\Carbon::today()->format('Y-m-d');
        $currentHour = (int) \Carbon\Carbon::now()->format('H');

        // Auto mark past or expired notifications as notified in DB
        \App\Models\Backend\MealRequest::where('user_id', $user->id)
            ->where('user_notified', 0)
            ->where(function($q) use ($todayStr, $currentHour) {
                $q->where(function($sub) use ($todayStr) {
                    $sub->whereNotNull('end_date')->where('end_date', '<', $todayStr);
                })->orWhere(function($sub) use ($todayStr) {
                    $sub->whereNull('end_date')->where('date', '<', $todayStr);
                });

                if ($currentHour >= 16) {
                    $q->orWhere(function($sub) use ($todayStr) {
                        $sub->where('request_type', 'half_night')->where('date', '<=', $todayStr);
                    });
                }
            })
            ->update(['user_notified' => 1]);

        // Newly approved (status 1) or rejected (status 2) requests
        $newNotifs = \App\Models\Backend\MealRequest::where('user_id', $user->id)
            ->whereIn('status', [1, 2])
            ->where('user_notified', 0)
            ->get();

        $mealTypeLabels = [
            'full'       => 'ফুল মিল (Full Meal)',
            'half_day'   => 'দিনের হাফ মিল',
            'half_night' => 'রাতের হাফ মিল',
            'off'        => 'মিল বন্ধ (Meal OFF)',
        ];

        $notifications = [];
        foreach ($newNotifs as $un) {
            $dateStr = \Carbon\Carbon::parse($un->date)->format('d-M-Y');
            $rangeStr = ($un->end_date && $un->total_days > 1) 
                ? "হতে " . \Carbon\Carbon::parse($un->end_date)->format('d-M-Y') . " পর্যন্ত" 
                : "";
            $typeLabel = $mealTypeLabels[$un->request_type] ?? $un->request_type;

            $notifMessage = "আপনার <strong>{$dateStr}</strong> {$rangeStr} তারিখের <strong>'{$typeLabel}'</strong> অনুরোধটি এডমিন কর্তৃক " . ($un->status == 1 ? 'অনুমোদিত' : 'প্রত্যাখ্যাত') . " হয়েছে।";

            if ($un->status == 1) {
                if ($un->request_type == 'off') {
                    $resumeDate = \Carbon\Carbon::parse($un->end_date ?: $un->date)->addDay()->format('d-M-Y');
                    $notifMessage .= "<br><span class='text-success fw-bold mt-1 d-inline-block'><i class='fa fa-info-circle me-1'></i> <strong>{$resumeDate}</strong> তারিখ থেকে আপনার মিল স্বয়ংক্রিয়ভাবে পুনরায় চালু থাকবে।</span>";
                } elseif ($un->request_type == 'half_night') {
                    $notifMessage .= "<br><span class='text-success fw-bold mt-1 d-inline-block'><i class='fa fa-sun me-1'></i> ☀️ দুপুরের মিল বন্ধ রাখা হয়েছে। আজ রাত থেকে রাতের মিল চালু থাকবে।</span>";
                } elseif ($un->request_type == 'half_day') {
                    $resumeDate = \Carbon\Carbon::parse($un->date)->addDay()->format('d-M-Y');
                    $notifMessage .= "<br><span class='text-success fw-bold mt-1 d-inline-block'><i class='fa fa-moon me-1'></i> 🌙 রাতের মিল বন্ধ রাখা হয়েছে। <strong>{$resumeDate}</strong> তারিখ থেকে পুনরায় স্বাভাবিক মিল চালু থাকবে।</span>";
                }
            }
            if ($un->admin_note) {
                $notifMessage .= "<br><small class='text-muted'>নোট: {$un->admin_note}</small>";
            }

            $notifications[] = [
                'id'           => $un->id,
                'status'       => $un->status, // 1 = approved, 2 = rejected
                'title'        => $un->status == 1 ? '✅ মিল অনুরোধ অনুমোদিত হয়েছে' : '❌ মিল অনুরোধ প্রত্যাখ্যাত হয়েছে',
                'message'      => $notifMessage,
                'request_type' => $un->request_type,
                'date'         => $un->date,
            ];

            // Mark as notified so it displays once
            $un->user_notified = 1;
            $un->save();
        }

        $pendingCount = \App\Models\Backend\MealRequest::where('user_id', $user->id)
            ->where('status', 0)
            ->count();

        $depositInfo = $this->mealService->getUserMealDepositBalance($user->id);
        $todayMealStatus = \App\Models\Backend\Meal::where('user_id', $user->id)->whereDate('date', today())->first();

        $isMealOff = ($depositInfo['balance'] <= 0) || ($todayMealStatus && $todayMealStatus->is_off);

        $todayMealBadgeText = 'Full Meal (অটো চালু)';
        $todayMealBadgeClass = 'bg-success';
        $todayMealBadgeIcon = 'fa-check-circle';

        if ($isMealOff) {
            $todayMealBadgeText = 'Meal OFF (বন্ধ)';
            $todayMealBadgeClass = 'bg-danger';
            $todayMealBadgeIcon = 'fa-ban';
        } elseif ($todayMealStatus && $todayMealStatus->half_meal) {
            $todayMealBadgeText = 'Half Meal (হাফ)';
            $todayMealBadgeClass = 'bg-info';
            $todayMealBadgeIcon = 'fa-sun';
        }

        return response()->json([
            'success'               => true,
            'has_notifs'            => count($notifications) > 0,
            'notifications'         => $notifications,
            'pending_count'         => $pendingCount,
            'deposit_balance'       => $depositInfo['balance'],
            'warning_message'       => $depositInfo['warning_message'],
            'is_meal_off'           => $isMealOff,
            'today_meal_badge_text'  => $todayMealBadgeText,
            'today_meal_badge_class' => $todayMealBadgeClass,
            'today_meal_badge_icon'  => $todayMealBadgeIcon,
        ]);
    }


    public function checkPendingMealRequests()
    {
        $pendingCount = \App\Models\Backend\MealRequest::where('status', 0)->count();
        $latestRequests = \App\Models\Backend\MealRequest::with('user')
            ->where('status', 0)
            ->latest()
            ->take(5)
            ->get()
            ->map(function($r) {
                $labels = [
                    'full'       => 'Full Meal',
                    'half_day'   => 'Day Half',
                    'half_night' => 'Night Half',
                    'off'        => 'Meal OFF',
                ];
                $daysText = ($r->total_days && $r->total_days > 1) ? " ({$r->total_days} Days)" : "";
                $reqLabel = ($labels[$r->request_type] ?? $r->request_type) . $daysText;
                $dateRangeText = ($r->end_date && $r->total_days > 1) 
                    ? "{$r->date} to {$r->end_date}"
                    : $r->date;

                return [
                    'id'           => $r->id,
                    'user_name'    => $r->user->name ?? 'Resident',
                    'request_type' => $reqLabel,
                    'date'         => $dateRangeText,
                    'total_days'   => $r->total_days ?? 1,
                    'time_ago'     => $r->created_at->diffForHumans(),
                    'initial'      => strtoupper(substr($r->user->name ?? 'R', 0, 1)),
                ];
            });

        return response()->json([
            'count'    => $pendingCount,
            'requests' => $latestRequests,
        ]);
    }

    public function dismissUserMealNotification($id)
    {
        $mealReq = \App\Models\Backend\MealRequest::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();
        if ($mealReq) {
            $mealReq->user_notified = 1;
            $mealReq->save();
        }
        return response()->json(['success' => true]);
    }
}
