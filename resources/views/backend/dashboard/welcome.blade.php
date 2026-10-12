@extends('backend.layouts.app')
@section("title") | {{$page_title}} @endsection
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row g-6 mb-6">
        
        <!-- RESIDENT / STUDENT USER SPECIFIC STAT CARDS -->
        @unlessrole('admin|super-admin|staffs|manager')
        @php
            $studentActiveCheckout = null;
            if (isset($userBooking) && $userBooking) {
                $studentActiveCheckout = \App\Models\Backend\ResidentCheckInOut::where('room_booking_history_id', $userBooking->id)
                    ->where('status', 0)
                    ->latest('check_out_date')
                    ->first();
            }
            if (!$studentActiveCheckout && Auth::check()) {
                $studentActiveCheckout = \App\Models\Backend\ResidentCheckInOut::where('user_id', Auth::id())
                    ->where('status', 0)
                    ->latest('check_out_date')
                    ->first();
            }
            
            $isCurrentlyOutside = false;
            $isUpcomingCheckout = false;
            if ($studentActiveCheckout) {
                $checkoutDate = \Carbon\Carbon::parse($studentActiveCheckout->check_out_date)->startOfDay();
                $todayDate = \Carbon\Carbon::today();
                if ($checkoutDate->lte($todayDate)) {
                    $isCurrentlyOutside = true;
                } else {
                    $isUpcomingCheckout = true;
                }
            }

            $residentPhoneForCheckout = $userBooking->phone ?? (Auth::user()->phone ?? '');
            $residentNameForCheckout = $userBooking->full_name ?? (Auth::user()->name ?? 'শিক্ষার্থী');
        @endphp
        
        <div class="col-12 mb-2">
            <div class="card border-0 shadow-sm p-3 bg-white" style="border-left: 5px solid #6366f1 !important;">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            <h5 class="fw-bold mb-0 text-dark">
                                <i class="fa fa-user-circle text-primary me-1"></i>Resident Dashboard (শিক্ষার্থী ড্যাশবোর্ড)
                            </h5>
                            @if($isCurrentlyOutside)
                                <span class="badge bg-warning text-dark border border-warning px-2.5 py-1 fw-bold rounded-pill small">
                                    🟡 বর্তমানে বাইরে / ছুটিতে আছেন
                                </span>
                            @elseif($isUpcomingCheckout)
                                <span class="badge bg-info text-dark border border-info px-2.5 py-1 fw-bold rounded-pill small">
                                    📅 আসন্ন ছুটি: {{ \Carbon\Carbon::parse($studentActiveCheckout->check_out_date)->format('d M, Y') }}
                                </span>
                            @else
                                <span class="badge bg-success text-white px-2.5 py-1 fw-bold rounded-pill small">
                                    🟢 হোস্টেলে উপস্থিত
                                </span>
                            @endif
                        </div>
                        <small class="text-muted d-block mb-1">📌 <strong>নির্দেশাবলী:</strong> কনফার্ম বুকিং ডকুমেন্টটি প্রিন্ট করে এডমিন এর কাছে জমা দিয়ে আপনার বুকিং রসিদ বুঝে নিন।</small>
                        <small class="text-dark fw-semibold"><i class="fa fa-phone-alt me-1 text-success"></i> যেকোনো তথ্যের জন্য এই নম্বরে যোগাযোগ করুন: <a href="tel:01977270920" class="text-primary fw-bold text-decoration-none">01977270920</a></small>
                    </div>
                    
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        @if($isCurrentlyOutside)
                            <a href="{{ route('check-in-out.index') }}" class="btn btn-success fw-bold waves-effect waves-light shadow-sm">
                                <i class="fa fa-door-open me-1"></i> হোস্টেলে ফিরে এসেছি (Check In)
                            </a>
                        @elseif($isUpcomingCheckout)
                            {{-- Don't show check out button when upcoming check out is already registered --}}
                        @else
                            <a href="{{ route('check-in-out.index') }}" class="btn btn-warning text-dark fw-bold waves-effect waves-light shadow-sm">
                                <i class="fa fa-walking me-1"></i> বাড়ি যাওয়া / Check Out
                            </a>
                        @endif

                        @if(isset($userBooking) && $userBooking)
                            <button type="button" onclick="printResidentDocument()" class="btn btn-primary fw-bold waves-effect waves-light shadow-sm">
                                <i class="fa fa-print me-2"></i> Confirm Booking Document
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if($isCurrentlyOutside)
        <div class="col-12 mb-3">
            <div class="alert alert-warning border-warning shadow-sm rounded-3 p-3.5 mb-0 d-flex align-items-center justify-content-between flex-wrap gap-3" style="background: #fffbeb; border: 2px solid #f59e0b;">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-warning text-dark p-2.5 d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; min-width: 48px;">
                        <i class="fa fa-walking fs-4"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            <span class="badge bg-warning text-dark fw-bold px-2.5 py-1 rounded-pill">🟡 বর্তমানে বাইরে / ছুটিতে আছেন</span>
                            <span class="text-muted small">চেক-আউট: <strong>{{ $studentActiveCheckout->check_out_date ? $studentActiveCheckout->check_out_date->format('d M, Y') : '' }}</strong></span>
                            @if($studentActiveCheckout->expected_return_date)
                                <span class="text-primary small fw-semibold">| সম্ভাব্য ফেরার তারিখ: <strong>{{ \Carbon\Carbon::parse($studentActiveCheckout->expected_return_date)->format('d M, Y') }}</strong></span>
                            @endif
                        </div>
                        <div class="fw-medium text-dark small">
                            কারণ: <strong>{{ $studentActiveCheckout->leave_reason ?: 'বাড়ি যাওয়া' }}</strong>
                            @if($studentActiveCheckout->emergency_contact)
                                | জরুরি যোগাযোগ: <strong>{{ $studentActiveCheckout->emergency_contact }}</strong>
                            @endif
                        </div>
                        <small class="d-block text-danger fw-semibold mt-1">
                            <i class="fa fa-utensils me-1"></i> চেক-আউট থাকায় আপনার মিল (Meal) স্বয়ংক্রিয়ভাবে বন্ধ আছে। হোস্টেলে ফিরে চেক-ইন করলে পরদিন থেকে মিল পুনরায় স্বয়ংক্রিয়ভাবে চালু হবে।
                        </small>
                    </div>
                </div>
                <button type="button" class="btn btn-success fw-bold px-4 py-2 rounded-3 shadow-sm d-inline-flex align-items-center gap-2" onclick="handleStudentCheckInFromDash()">
                    <i class="fa fa-door-open"></i>
                    <span>হোস্টেলে ফিরে এসেছি (Check In Now)</span>
                </button>
            </div>
        </div>
        @elseif($isUpcomingCheckout)
        <div class="col-12 mb-3">
            <div class="alert alert-info border-info shadow-sm rounded-3 p-3.5 mb-0 d-flex align-items-center justify-content-between flex-wrap gap-3" style="background: #eff6ff; border: 2px solid #3b82f6;">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary text-white p-2.5 d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; min-width: 48px;">
                        <i class="fa fa-calendar-alt fs-4"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            <span class="badge bg-primary text-white fw-bold px-2.5 py-1 rounded-pill">📅 আসন্ন ছুটি / চেক-আউট এন্ট্রি</span>
                            <span class="text-dark small">চেক-আউট তারিখ: <strong>{{ $studentActiveCheckout->check_out_date ? $studentActiveCheckout->check_out_date->format('d M, Y') : '' }}</strong></span>
                            @if($studentActiveCheckout->expected_return_date)
                                <span class="text-primary small fw-semibold">| সম্ভাব্য ফেরার তারিখ: <strong>{{ \Carbon\Carbon::parse($studentActiveCheckout->expected_return_date)->format('d M, Y') }}</strong></span>
                            @endif
                        </div>
                        <div class="text-muted small">
                            <span class="text-success fw-bold">🟢 আপনি বর্তমানে হোস্টেলে উপস্থিত আছেন।</span> আপনার ছুটি নির্ধারিত তারিখ ({{ $studentActiveCheckout->check_out_date->format('d M, Y') }}) থেকে কার্যকর হবে।
                        </div>
                        <small class="d-block text-primary fw-semibold mt-1">
                            <i class="fa fa-utensils me-1"></i> চেক-আউট তারিখ থেকে মিল স্বয়ংক্রিয়ভাবে বন্ধ থাকবে এবং হোস্টেলে ফিরে চেক-ইন করলে আবার চালু হবে।
                        </small>
                    </div>
                </div>
            </div>
        </div>
        @endif


        @if(isset($upcomingSpecialMeals) && $upcomingSpecialMeals->count() > 0)
            @foreach($upcomingSpecialMeals as $sm)
                <div class="col-12 mb-3">
                    <div class="card border-0 shadow-sm rounded-3 overflow-hidden text-white" style="background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%);">
                        <div class="card-body p-3 p-md-4">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-white text-primary p-3 me-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 52px; height: 52px; min-width: 52px;">
                                        <i class="ti ti-star fs-3 text-warning"></i>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                            <span class="badge bg-warning text-dark fw-bold px-2.5 py-1 rounded-pill" style="font-size: 11px;">
                                                <i class="ti ti-sparkles me-1"></i> স্পেশাল মিল আয়োজন
                                            </span>
                                            <span class="badge bg-white text-dark fw-bold px-2.5 py-1 rounded-pill font-monospace" style="font-size: 11px;">
                                                <i class="ti ti-calendar me-1 text-primary"></i> {{ \Carbon\Carbon::parse($sm->date)->format('d M Y (l)') }}
                                            </span>
                                        </div>
                                        <h5 class="fw-bold text-white mb-1">
                                            {{ $sm->title ?: 'স্পেশাল মিল আয়োজন' }}
                                        </h5>
                                        <p class="mb-0 text-white-50 small">
                                            রেগুলার মিল রেটের বদলে এই তারিখে স্পেশাল মিল রেট কার্যকর হবে।
                                        </p>
                                    </div>
                                </div>
                                <div class="text-end bg-white bg-opacity-10 p-2.5 px-3 rounded-3 border border-white border-opacity-25 ms-auto ms-md-0">
                                    <span class="text-white-50 small d-block mb-1">স্পেশাল মিল রেট</span>
                                    <h4 class="mb-0 text-warning fw-bold font-monospace">
                                        ৳ {{ number_format($sm->full_rate, 2) }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif

        @if(session('success'))
            <div class="col-12 mb-3">
                <div class="alert alert-success alert-dismissible d-flex align-items-center justify-content-between p-3 mb-0 shadow-sm border-success rounded-3" role="alert">
                    <div class="d-flex align-items-center">
                        <span class="alert-icon text-success me-3 fs-3">
                            <i class="fa fa-check-circle"></i>
                        </span>
                        <div>
                            <h6 class="alert-heading mb-1 text-success fw-bold fs-6">📌 মিল অনুরোধ সফলভাবে পাঠানো হয়েছে</h6>
                            <div class="fw-medium text-dark fs-7">{{ session('success') }}</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        @php
            $todayStr = \Carbon\Carbon::today()->format('Y-m-d');
            $currentHour = (int) \Carbon\Carbon::now()->format('H');

            // Auto mark past or expired notifications as notified in DB so they clear automatically:
            // 1. Any request where date/end_date < today
            // 2. Any 'half_night' (Lunch OFF) request for today when current time >= 16:00 (4:00 PM)
            \App\Models\Backend\MealRequest::where('user_id', auth()->id())
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

            $userMealNotifs = \App\Models\Backend\MealRequest::where('user_id', auth()->id())
                ->whereIn('status', [1, 2])
                ->where('user_notified', 0)
                ->get();

            $mealTypeLabels = [
                'full'       => 'মিল চালু (Meal ON)',
                'off'        => 'মিল বন্ধ (Meal OFF)',
            ];
        @endphp

        @foreach($userMealNotifs as $un)
            <div class="col-12 mb-3" id="mealNotifBox_{{ $un->id }}">
                <div class="alert alert-{{ $un->status == 1 ? 'success' : 'danger' }} alert-dismissible d-flex align-items-center justify-content-between p-3 mb-0 shadow-sm border-{{ $un->status == 1 ? 'success' : 'danger' }} rounded-3" role="alert">
                    <div class="d-flex align-items-center">
                        <span class="alert-icon text-{{ $un->status == 1 ? 'success' : 'danger' }} me-3 fs-3">
                            <i class="fa fa-{{ $un->status == 1 ? 'check-circle' : 'times-circle' }}"></i>
                        </span>
                        <div>
                            <h6 class="alert-heading mb-1 text-{{ $un->status == 1 ? 'success' : 'danger' }} fw-bold fs-6">
                                {{ $un->status == 1 ? '✅ মিল অনুরোধ অনুমোদিত হয়েছে' : '❌ মিল অনুরোধ প্রত্যাখ্যাত হয়েছে' }}
                            </h6>
                            <div class="fw-medium text-dark fs-7">
                                আপনার <strong>{{ \Carbon\Carbon::parse($un->date)->format('d-M-Y') }}</strong> {{ $un->end_date && $un->total_days > 1 ? "হতে " . \Carbon\Carbon::parse($un->end_date)->format('d-M-Y') . " পর্যন্ত" : "" }} তারিখের <strong>'{{ $mealTypeLabels[$un->request_type] ?? $un->request_type }}'</strong> অনুরোধটি এডমিন কর্তৃক {{ $un->status == 1 ? 'অনুমোদিত' : 'প্রত্যাখ্যাত' }} হয়েছে।
                                @if($un->status == 1)
                                    @if($un->request_type == 'off')
                                        @php
                                            $resumeDate = \Carbon\Carbon::parse($un->end_date ?: $un->date)->addDay()->format('d-M-Y');
                                        @endphp
                                        <br><span class="text-success fw-bold mt-1 d-inline-block"><i class="fa fa-info-circle me-1"></i> <strong>{{ $resumeDate }}</strong> তারিখ থেকে আপনার মিল স্বয়ংক্রিয়ভাবে পুনরায় চালু থাকবে।</span>
                                    @elseif($un->request_type == 'full')
                                        <br><span class="text-success fw-bold mt-1 d-inline-block"><i class="fa fa-check-circle me-1"></i> আপনার মিল চালু করা হয়েছে।</span>
                                    @endif
                                @endif
                                @if($un->admin_note)
                                    <br><small class="text-muted">নোট: {{ $un->admin_note }}</small>
                                @endif
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"
                            onclick="dismissMealNotif({{ $un->id }})"></button>
                </div>
            </div>
        @endforeach
        @if(!auth()->user()->hasAnyRole(['admin', 'staffs']) && isset($isAdminTurnedOff) && $isAdminTurnedOff && !$isCurrentlyOutside)
        <div class="col-12 mb-3" id="adminMealOffNoticeBanner">
            <div class="alert alert-danger border-danger d-flex align-items-center justify-content-between flex-wrap gap-2 p-3 mb-0 shadow-sm rounded-3" role="alert">
                <div class="d-flex align-items-center">
                    <span class="alert-icon text-danger me-3 fs-3">
                        <i class="fa fa-ban"></i>
                    </span>
                    <div>
                        <h6 class="alert-heading mb-1 text-danger fw-bold fs-6">🚫 কর্তৃপক্ষ কর্তৃক আপনার মিল বন্ধ রাখা হয়েছে</h6>
                        <div class="fw-medium text-dark fs-7">
                            কর্তৃপক্ষ/এডমিন আপনার মিল বন্ধ করে দিয়েছেন। মিল পুনরায় চালু করার জন্য অনুগ্রহ করে মেস কর্তৃপক্ষের সাথে যোগাযোগ করুন।
                        </div>
                    </div>
                </div>
                <a href="tel:{{ $webSetting['phone'] ?? '01977270920' }}" class="btn btn-danger fw-bold shadow-sm waves-effect waves-light px-3 py-2">
                    <i class="fa fa-phone-alt me-1"></i> মিল চালুর জন্য কল করুন ({{ $webSetting['phone'] ?? '01977270920' }})
                </a>
            </div>
        </div>
        @endif

        @if(!auth()->user()->hasAnyRole(['admin', 'staffs']) && isset($mealDepositWarning) && $mealDepositWarning && (!isset($isAdminTurnedOff) || !$isAdminTurnedOff))
        <div class="col-12 mb-3" id="mealNoticeBanner">
            <div class="alert alert-info border-info d-flex align-items-center justify-content-between flex-wrap gap-2 p-3 mb-0 shadow-sm rounded-3" role="alert">
                <div class="d-flex align-items-center">
                    <span class="alert-icon text-info me-3 fs-3">
                        <i class="fa fa-info-circle"></i>
                    </span>
                    <div>
                        <h6 class="alert-heading mb-1 text-info fw-bold fs-6">📌 মিল সার্ভিস সংক্রান্ত তথ্য (Meal Service Information)</h6>
                        <div class="fw-medium text-dark fs-7" id="mealNoticeMessage">
                            {{ $mealDepositWarning }}
                        </div>
                    </div>
                </div>
                <a href="tel:{{ $webSetting['phone'] ?? '01977270920' }}" class="btn btn-sm btn-info fw-bold shadow-sm waves-effect waves-light px-3 py-2">
                    <i class="fa fa-phone-alt me-1"></i> এডমিন এর সাথে যোগাযোগ করুন
                </a>
            </div>
        </div>
        @endif


        @php
            $bookingItems = [];
            if (isset($userBooking) && $userBooking) {
                if (is_string($userBooking->floor_number_room_number_roomprice)) {
                    $bookingItems = json_decode($userBooking->floor_number_room_number_roomprice, true) ?? [];
                } elseif (is_array($userBooking->floor_number_room_number_roomprice)) {
                    $bookingItems = $userBooking->floor_number_room_number_roomprice;
                }
            }
            
            $bFloors = collect($bookingItems)->pluck('floornumber')->filter()->unique()->implode(', ');
            if (!$bFloors && isset($userBooking) && $userBooking) {
                $bFloors = $userBooking->floornumber ?? '-';
            }

            $bRooms = collect($bookingItems)->map(function($i) {
                $rStr = trim((string)($i['roomnumber'] ?? ''));
                if (preg_match('/^(\d+)\-Block\s+([A-Za-z])(\-(\d+))?$/i', $rStr, $m)) {
                    return "{$m[1]}-Block " . strtoupper($m[2]);
                }
                if (preg_match('/^([A-Za-z]+)[\-_](\d+)/', $rStr, $m)) {
                    return ((int)$m[2]) . '-Block ' . strtoupper($m[1]);
                }
                return $rStr ?: '-';
            })->filter()->unique()->implode(', ');
            if (!$bRooms && isset($userBooking) && $userBooking) {
                $bRooms = $userBooking->room_number ?? $userBooking->roomnumber ?? '-';
            }

            $bSeats = collect($bookingItems)->map(function($i) {
                $rStr = trim((string)($i['roomnumber'] ?? ''));
                if (preg_match('/^(\d+)\-Block\s+([A-Za-z])(\-(\d+))?$/i', $rStr, $m)) {
                    return $rStr;
                }
                if (preg_match('/^([A-Za-z]+)[\-_](\d+)(.*)$/', $rStr, $m)) {
                    $let = strtoupper($m[1]);
                    $num = (int)$m[2];
                    $sub = '';
                    if (preg_match('/(\d+)$/', $m[3], $sm)) {
                        $sub = '-' . $sm[1];
                    }
                    return "{$num}-Block {$let}{$sub}";
                }
                return $rStr ?: '-';
            })->filter()->implode(', ');
            if (!$bSeats && isset($userBooking) && $userBooking) {
                $bSeats = '-';
            }

            $bAdvanceTotal = collect($bookingItems)->sum(function($i) {
                return (float)($i['advance_price'] ?? 0);
            });
            if ($bAdvanceTotal <= 0 && isset($userBooking) && isset($userBooking->advance_fee) && is_numeric($userBooking->advance_fee)) {
                $bAdvanceTotal = (float)$userBooking->advance_fee;
            }
            $bDevFee = isset($userBooking) ? (float)($userBooking->development_fee ?? 0) : 0;
            $bTotalInitialFee = $bAdvanceTotal + $bDevFee;
        @endphp

        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 position-relative overflow-hidden border-0 shadow-sm" style="border-top: 4px solid #6366f1 !important;">
                <div class="card-body pb-2">
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <div class="content-left">
                            <span class="text-heading fw-semibold">My Booking Status</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2 text-primary fw-bold">{{ $userBooking ? 'Active Resident' : 'No Booking' }}</h4>
                            </div>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                               <i class="fa fa-user-check"></i>
                            </span>
                        </div>
                    </div>
                    @if($userBooking)
                    <div class="bg-light p-2 rounded-2 mb-2 border">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted"><i class="fa fa-building text-primary me-1"></i> Floor:</span>
                            <span class="fw-bold text-dark">{{ $bFloors ?: '-' }}</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted"><i class="fa fa-door-open text-primary me-1"></i> Room/Section:</span>
                            <span class="fw-bold text-dark">{{ $bRooms ?: '-' }}</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-2 pb-1 border-bottom">
                            <span class="text-muted"><i class="fa fa-bed text-primary me-1"></i> Seat No:</span>
                            <span class="badge bg-label-danger font-monospace fw-bold">{{ $bSeats ?: '-' }}</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted"><i class="fa fa-shield-alt text-success me-1"></i> Advance Deposit:</span>
                            <span class="fw-bold text-dark">৳ {{ number_format($bAdvanceTotal) }}</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted"><i class="fa fa-cogs text-warning me-1"></i> Development Fee:</span>
                            <span class="fw-bold text-dark">৳ {{ number_format($bDevFee) }}</span>
                        </div>
                        <div class="d-flex justify-content-between small pt-1 border-top mt-1">
                            <span class="fw-bold text-primary"><i class="fa fa-calculator me-1"></i> Total Booking Fee:</span>
                            <span class="fw-bold text-primary">৳ {{ number_format($bTotalInitialFee) }}</span>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="mb-0 text-primary fw-bold">
                            <i class="fa fa-tag me-1"></i> ৳ {{ number_format($userBooking->monthly_amount ?? 0) }}/month
                        </small>
                    </div>
                    @else
                    <small class="mb-0 text-muted">No Active Booking Record</small>
                    @endif
                </div>
            </div>
        </div>

        <!-- My Payments Card -->
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('dashboard.my-payments') }}" class="text-decoration-none">
                <div class="card h-100 position-relative overflow-hidden border-0 shadow-sm" style="border-top: 4px solid #10b981 !important;">
                    <div class="card-body pb-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">My Payments</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2 text-success">৳ {{ number_format($myTotalPaid ?? 0) }}</h4>
                                </div>
                                <small class="mb-0 text-muted">Rent Payment History</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-success">
                                   <i class="fa fa-wallet"></i>
                                </span>
                            </div>
                        </div>
                        <div id="chart-mypayments" class="mt-2" style="min-height: 45px;"></div>
                    </div>
                </div>
            </a>
        </div>

        <!-- My Electricity Bill Card -->
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('dashboard.my-electricity-bills') }}" class="text-decoration-none">
                <div class="card h-100 position-relative overflow-hidden border-0 shadow-sm" style="border-top: 4px solid #f59e0b !important; cursor: pointer;">
                    <div class="card-body pb-2">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading fw-semibold text-warning">
                                    <i class="ti ti-bolt text-warning"></i> Electricity Bills
                                </span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2 text-warning fw-bold">
                                        ⚡ ৳ {{ number_format($myTotalElectBill ?? 0, 2) }}
                                    </h4>
                                </div>
                                <small class="mb-0 text-muted">
                                    বর্তমান মাস: <strong class="text-dark">৳ {{ number_format($myCurrentMonthElectBill ?? 0, 2) }}</strong>
                                </small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-warning">
                                   <i class="ti ti-bolt fs-3 text-warning"></i>
                                </span>
                            </div>
                        </div>
                        <div class="mt-2">
                            <span class="badge bg-label-warning text-dark w-100 py-1.5 fw-bold d-flex align-items-center justify-content-center gap-1">
                                <i class="ti ti-receipt"></i> মাসের বিদ্যুৎ বিল বিবরণী দেখুন
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>


        <!-- My Meal Deposit & History Card -->
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('dashboard.my-meals') }}" class="text-decoration-none">
                <div class="card h-100 position-relative overflow-hidden border-0 shadow-sm" style="border-top: 4px solid #ff9f43 !important;">
                    <div class="card-body pb-2">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading fw-semibold text-warning">Meal Deposit Balance</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2 {{ (isset($mealDepositBalance) && $mealDepositBalance < 0) ? 'text-danger' : 'text-warning' }} fw-bold">
                                        ৳ {{ number_format($mealDepositBalance ?? 0, 2) }}
                                    </h4>
                                </div>
                                <small class="mb-0 text-muted">Total Meals: {{ number_format($myMealCount ?? 0) }}</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-warning">
                                   <i class="fa fa-utensils"></i>
                                </span>
                            </div>
                        </div>
                        <div class="mt-2">
                            <span class="badge bg-label-warning w-100 py-1"><i class="fa fa-wallet me-1"></i> View Deposit & History</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Daily Meal Status / Setting Card -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 position-relative overflow-hidden border-0 shadow-sm" style="border-top: 4px solid #00cfdd !important;">
                <div class="card-body pb-2">
                    <div class="d-flex align-items-start justify-content-between mb-1">
                        <div class="content-left">
                            <span class="text-heading fw-semibold">Today's Meal (আজকের মিল)</span>
                            <div class="my-1" id="todayMealBadgeWrap">
                                @if(isset($todayMealStatus) && $todayMealStatus->is_off)
                                    <span class="badge bg-danger fs-6 px-2 py-1"><i class="fa fa-ban me-1"></i> Meal OFF (মিল বন্ধ)</span>
                                @elseif(isset($todayMealStatus) && $todayMealStatus->half_meal)
                                    @if($todayMealStatus->note == 'night' || $todayMealStatus->note == 'half_night')
                                        <span class="badge bg-warning text-dark fs-6 px-2 py-1" title="রাতের মিল চালু / দুপুরের মিল বন্ধ"><i class="fa fa-moon me-1"></i> Night Half (রাত চালু / দুপুর বন্ধ)</span>
                                    @else
                                        <span class="badge bg-info text-white fs-6 px-2 py-1" title="দুপুরের মিল চালু / রাতের মিল বন্ধ"><i class="fa fa-sun me-1"></i> Day Half (দুপুর চালু / রাত বন্ধ)</span>
                                    @endif
                                @else
                                    <span class="badge bg-success fs-6 px-2 py-1"><i class="fa fa-check-circle me-1"></i> Full Meal (অটো চালু)</span>
                                @endif
                            </div>

                            <small class="mb-0 text-muted">Auto generated daily</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-info">
                               <i class="fa fa-calendar-alt"></i>
                            </span>
                        </div>
                    </div>
                    <div class="mt-2" id="todayMealActionBtnWrap">
                        @if(isset($isAdminTurnedOff) && $isAdminTurnedOff && !$isCurrentlyOutside)
                            <a href="tel:{{ $webSetting['phone'] ?? '01977270920' }}" class="btn btn-sm btn-danger w-100 fw-bold shadow-sm waves-effect">
                                <i class="fa fa-phone-alt me-1"></i> মিল চালুর জন্য কল করুন
                            </a>
                        @else
                            <a href="{{ route('meals.create') }}" class="btn btn-sm btn-outline-info w-100 fw-bold shadow-sm waves-effect">
                                <i class="fa fa-cog me-1"></i> Change / Turn OFF Meal
                            </a>
                        @endif
                        <small class="d-block text-muted text-center mt-1" style="font-size: 0.72rem;">
                            ☀️ লাঞ্চ কাটঅফ: ১০:০০ AM | 🌙 ডিনার: ০৪:০০ PM
                        </small>
                    </div>

                    <div id="todayMealOffResumeWrap">
                        @if(isset($latestMealRequest) && $latestMealRequest)
                            @php
                                $endDate = $latestMealRequest->end_date ?: $latestMealRequest->date;
                                $resumeDate = \Carbon\Carbon::parse($endDate)->addDay()->format('d-M-Y');
                                $daysText = $latestMealRequest->request_type == 'off'
                                    ? 'আগামী ' . ($latestMealRequest->total_days ?? 1) . ' দিনের বন্ধের অনুরোধ'
                                    : 'মিল চালুর অনুরোধ';
                            @endphp
                            <div class="mt-2 p-2 bg-light-warning rounded border border-warning text-dark text-start" style="font-size: 0.74rem;">
                                <div class="fw-bold text-danger mb-1 d-flex align-items-center justify-content-between">
                                    <span><i class="fa fa-calendar-times me-1"></i> {{ $daysText }}</span>
                                    @if($latestMealRequest->status == 0)
                                        <span class="badge bg-warning text-dark px-1 py-0" style="font-size: 0.62rem;">অপেক্ষমাণ</span>
                                    @else
                                        <span class="badge bg-success text-white px-1 py-0" style="font-size: 0.62rem;">অনুমোদিত</span>
                                    @endif
                                </div>
                                <div class="text-primary fw-semibold" style="font-size: 0.73rem;">
                                    <i class="fa fa-sync-alt me-1 text-primary"></i> 🔄 <strong>{{ $resumeDate }}</strong> তারিখ থেকে অটো মিল চালু
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- My Complaints Card -->
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('complaints.index') }}" class="text-decoration-none">
                <div class="card h-100 position-relative overflow-hidden border-0 shadow-sm" style="border-top: 4px solid #ef4444 !important;">
                    <div class="card-body pb-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading text-danger fw-semibold">My Complaints</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2 text-danger">{{ \App\Models\Backend\Complaint::where('user_id', Auth::id())->count() }}</h4>
                                </div>
                                <small class="mb-0 text-muted">Submit & View Issues</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-danger">
                                   <i class="fa fa-exclamation-triangle"></i>
                                </span>
                            </div>
                        </div>
                        <div id="chart-complaint" class="mt-2" style="min-height: 45px;"></div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Notice Board (জরুরি বিজ্ঞপ্তি) Card -->
        @php
            $activeNotices = \App\Models\Backend\ResidentNotice::where('status', 1)->latest()->get();
            $activeNoticesCount = $activeNotices->count();
            $latestSingleNotice = $activeNotices->first();
        @endphp
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('dashboard.resident-notices') }}" class="text-decoration-none">
                <div class="card h-100 position-relative overflow-hidden border-0 shadow-sm" style="border-top: 4px solid #6366f1 !important; cursor: pointer;">
                    <div class="card-body pb-2">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading fw-semibold" style="color: #4f46e5;">Notice Board (বিজ্ঞপ্তি)</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2 fw-bold" style="color: #4f46e5;">{{ $activeNoticesCount }} টি</h4>
                                </div>
                                <small class="mb-0 text-muted">
                                    @if($latestSingleNotice)
                                        সর্বশেষ: {{ \Illuminate\Support\Str::limit($latestSingleNotice->title, 18) }}
                                    @else
                                        জরুরি বিজ্ঞপ্তি সমূহ
                                    @endif
                                </small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded" style="background: #eef2ff; color: #4f46e5;">
                                   <i class="fa fa-bullhorn"></i>
                                </span>
                            </div>
                        </div>
                        <div class="mt-2">
                            <span class="btn btn-sm w-100 py-1 fw-bold shadow-none d-block text-center" style="background: #e0e7ff; color: #3730a3; border: 1px solid #c7d2fe;">
                                <i class="fa fa-list-alt me-1"></i> সকল বিজ্ঞপ্তি দেখুন
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Student Check In / Check Out Card -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 position-relative overflow-hidden border-0 shadow-sm" style="border-top: 4px solid {{ $isCurrentlyOutside ? '#f59e0b' : ($isUpcomingCheckout ? '#3b82f6' : '#10b981') }} !important;">
                <div class="card-body pb-2">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading fw-semibold {{ $isCurrentlyOutside ? 'text-warning' : ($isUpcomingCheckout ? 'text-primary' : 'text-success') }}">
                                Check In / Out
                            </span>
                            <div class="my-1">
                                @if($isCurrentlyOutside)
                                    <span class="badge bg-warning text-dark fs-6 px-2.5 py-1">
                                        <i class="fa fa-walking me-1"></i> Outside / On Leave
                                    </span>
                                @elseif($isUpcomingCheckout)
                                    <span class="badge bg-info text-dark fs-6 px-2.5 py-1">
                                        <i class="fa fa-calendar-alt me-1"></i> Upcoming Leave
                                    </span>
                                @else
                                    <span class="badge bg-success fs-6 px-2.5 py-1">
                                        <i class="fa fa-home me-1"></i> In Hostel
                                    </span>
                                @endif
                            </div>
                            <small class="mb-0 text-muted">
                                @if($isCurrentlyOutside)
                                    Check-out: {{ $studentActiveCheckout->check_out_date ? $studentActiveCheckout->check_out_date->format('d M, Y') : '' }}
                                @elseif($isUpcomingCheckout)
                                    Starts: {{ $studentActiveCheckout->check_out_date ? $studentActiveCheckout->check_out_date->format('d M, Y') : '' }}
                                @else
                                    Present in Hostel
                                @endif
                            </small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded {{ $isCurrentlyOutside ? 'bg-label-warning text-warning' : ($isUpcomingCheckout ? 'bg-label-info text-info' : 'bg-label-success text-success') }}">
                               <i class="fa {{ $isCurrentlyOutside ? 'fa-door-closed' : ($isUpcomingCheckout ? 'fa-calendar-check' : 'fa-door-open') }}"></i>
                            </span>
                        </div>
                    </div>
                    <div class="mt-2">
                        @if($isCurrentlyOutside)
                            <a href="{{ route('check-in-out.index') }}" class="btn btn-sm btn-success w-100 fw-bold shadow-sm py-1">
                                <i class="fa fa-door-open me-1"></i> Check In Now
                            </a>
                        @elseif($isUpcomingCheckout)
                            <span class="badge bg-label-info text-info w-100 py-1.5 fw-bold">আসন্ন ছুটি নির্ধারিত</span>
                        @else
                            <a href="{{ route('check-in-out.index') }}" class="btn btn-sm btn-outline-warning text-dark w-100 fw-bold shadow-sm py-1">
                                <i class="fa fa-walking me-1"></i> Check Out Entry
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @endunlessrole



        <!-- ADMIN & STAFF STAT CARDS -->
        <!-- Floor -->
        @role('admin|super-admin')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ url('floor') }}">
                <div class="card h-100 position-relative overflow-hidden">
                    <div class="card-body pb-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Floor</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ $floorscount }}</h4>
                                </div>
                                <small class="mb-0 text-muted">Total Floors</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-primary">
                                  <i class="fa fa-building"></i>
                                </span>
                            </div>
                        </div>
                        <div id="chart-floor" class="mt-2" style="min-height: 45px;"></div>
                    </div>
                </div>
            </a>
        </div>
        @endrole

        <!-- Total Rooms -->
        @role('admin|super-admin')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ url('room-list') }}">
                <div class="card h-100 position-relative overflow-hidden">
                    <div class="card-body pb-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Total Rooms</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ $roomcount }}</h4>
                                </div>
                                <small class="mb-0 text-muted">Total Rooms</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-danger">
                                <i class="fa fa-bed"></i>
                                </span>
                            </div>
                        </div>
                        <div id="chart-room" class="mt-2" style="min-height: 45px;"></div>
                    </div>
                </div>
            </a>
        </div>
        @endrole

        <!-- Total Seats -->
        @role('admin|super-admin|staffs')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('room-list.index') }}?seat_status=with_seats">
                <div class="card h-100 position-relative overflow-hidden">
                    <div class="card-body pb-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Total Seats</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2 text-primary">{{ $totalseatcount }}</h4>
                                </div>
                                <small class="mb-0 text-muted">Hostel Capacity</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-primary">
                                  <i class="fa fa-th-large"></i>
                                </span>
                            </div>
                        </div>
                        <div id="chart-totalseat" class="mt-2" style="min-height: 45px;"></div>
                    </div>
                </div>
            </a>
        </div>
        @endrole

        <!-- Available Seats -->
        @role('admin|super-admin|staffs')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('room-list.index') }}?seat_status=0">
                <div class="card h-100 position-relative overflow-hidden">
                    <div class="card-body pb-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Available Seats</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2 text-info">{{ $availableseatcount }}</h4>
                                </div>
                                <small class="mb-0 text-muted">Free Seats</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-info">
                                  <i class="fa fa-check-circle"></i>
                                </span>
                            </div>
                        </div>
                        <div id="chart-availseat" class="mt-2" style="min-height: 45px;"></div>
                    </div>
                </div>
            </a>
        </div>
        @endrole

        <!-- Booked Seats for Admin -->
        @role('admin|super-admin')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('room-list.index') }}?seat_status=1">
                <div class="card h-100 position-relative overflow-hidden">
                    <div class="card-body pb-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Booked Seats</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2 text-success">{{ $roombookingcount }}</h4>
                                </div>
                                <small class="mb-0 text-muted">Active Resident Bookings</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-success">
                                   <i class="fa fa-user-check"></i>
                                </span>
                            </div>
                        </div>
                        <div id="chart-bookedseat" class="mt-2" style="min-height: 45px;"></div>
                    </div>
                </div>
            </a>
        </div>
        @endrole

        <!-- Pending Bookings for Admin -->
        @role('admin|super-admin')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ url('room-booking-history') }}">
                <div class="card h-100 position-relative overflow-hidden" style="border-top: 3px solid #f59e0b;">
                    <div class="card-body pb-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Pending Bookings</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2 text-warning">{{ $pendingbookingcount }}</h4>
                                </div>
                                <small class="mb-0 text-muted">Awaiting Approval</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-warning">
                                   <i class="fa fa-clock"></i>
                                </span>
                            </div>
                        </div>
                        <div id="chart-pendingbooking" class="mt-2" style="min-height: 45px;"></div>
                    </div>
                </div>
            </a>
        </div>
        @endrole

        <!-- Check In / Check Out Status Cards for Admin -->
        @hasanyrole('admin|super-admin|staffs|manager')
        <!-- Currently in Hostel -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 position-relative overflow-hidden border-0 shadow-sm cursor-pointer" 
                 role="button"
                 data-bs-toggle="modal" 
                 data-bs-target="#inHostelListModal"
                 style="border-top: 4px solid #10b981 !important; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;"
                 onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 20px rgba(16, 185, 129, 0.15)';"
                 onmouseout="this.style.transform='none'; this.style.boxShadow='none';">
                <div class="card-body pb-2">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading fw-semibold text-success">In Hostel</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2 text-success fw-bold">{{ $dashInsideCount ?? 0 }}</h4>
                            </div>
                            <small class="mb-0 text-muted">Currently In Hostel</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-success">
                               <i class="fa fa-home"></i>
                            </span>
                        </div>
                    </div>
                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-outline-success w-100 py-1 fw-semibold d-flex align-items-center justify-content-center gap-1 shadow-none" data-bs-toggle="modal" data-bs-target="#inHostelListModal">
                            <i class="fa fa-door-open me-1"></i> View In Hostel List ({{ $dashInsideCount ?? 0 }})
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Currently Checked Out (Outside) -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 position-relative overflow-hidden border-0 shadow-sm cursor-pointer" 
                 role="button"
                 data-bs-toggle="modal" 
                 data-bs-target="#outsideListModal"
                 style="border-top: 4px solid #f59e0b !important; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;"
                 onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 20px rgba(245, 158, 11, 0.15)';"
                 onmouseout="this.style.transform='none'; this.style.boxShadow='none';">
                <div class="card-body pb-2">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading fw-semibold text-warning">Outside / On Leave</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2 text-warning fw-bold">{{ $dashOutsideCount ?? 0 }}</h4>
                            </div>
                            <small class="mb-0 text-muted">Currently Checked Out</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-warning">
                               <i class="fa fa-walking"></i>
                            </span>
                        </div>
                    </div>
                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-outline-warning text-dark w-100 py-1 fw-semibold d-flex align-items-center justify-content-center gap-1 shadow-none" data-bs-toggle="modal" data-bs-target="#outsideListModal">
                            <i class="fa fa-door-closed me-1"></i> View Outside List ({{ $dashOutsideCount ?? 0 }})
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endhasanyrole


        <!-- Staffs -->
        @hasanyrole('admin|super-admin|staffs|manager')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ url('staffs') }}">
                <div class="card h-100 position-relative overflow-hidden">
                    <div class="card-body pb-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Staffs</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ $staffscount }}</h4>
                                </div>
                                <small class="mb-0 text-muted">Total Employees</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="fa fa-users"></i>
                                </span>
                            </div>
                        </div>
                        <div id="chart-staff" class="mt-2" style="min-height: 45px;"></div>
                    </div>
                </div>
            </a>
        </div>
        @endhasanyrole

        <!-- Release History -->
        @role('admin|super-admin')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('room-release.history') }}">
                <div class="card h-100 position-relative overflow-hidden">
                    <div class="card-body pb-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Release History</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ $releasehistorycount }}</h4>
                                </div>
                                <small class="mb-0 text-muted">Checkout & Release</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-warning">
                                   <i class="fa fa-user-slash"></i>
                                </span>
                            </div>
                        </div>
                        <div id="chart-release" class="mt-2" style="min-height: 45px;"></div>
                    </div>
                </div>
            </a>
        </div>
        @endrole

       
        <!-- Today Meal Entries -->
        @role('admin|super-admin|staffs')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('meals.index') }}">
                <div class="card h-100 position-relative overflow-hidden">
                    <div class="card-body pb-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Today Meal Count</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ number_format($todayMealCount) }}</h4>
                                </div>
                                <small class="mb-0 text-muted">{{ \Carbon\Carbon::today()->format('d M Y') }}</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-warning">
                                   <i class="fa fa-utensils"></i>
                                </span>
                            </div>
                        </div>
                        <div id="chart-meal" class="mt-2" style="min-height: 45px;"></div>
                    </div>
                </div>
            </a>
        </div>
        @endrole

        <!-- Complaints Card for Admin -->
        @role('admin|super-admin|staffs')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('complaints.index') }}" class="text-decoration-none">
                <div class="card h-100 position-relative overflow-hidden border-0 shadow-sm" style="border-top: 4px solid #ef4444 !important;">
                    <div class="card-body pb-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading text-danger fw-semibold">Complaints (অভিযোগ)</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2 text-danger">{{ \App\Models\Backend\Complaint::where('status', 0)->count() }}</h4>
                                </div>
                                <small class="mb-0 text-muted">Pending Complaints</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-danger">
                                   <i class="fa fa-bell"></i>
                                </span>
                            </div>
                        </div>
                        <div id="chart-admin-complaint" class="mt-2" style="min-height: 45px;"></div>
                    </div>
                </div>
            </a>
        </div>
        @endrole

        <!-- Today Expense -->
        @role('admin|super-admin')
        <div class="col-sm-6 col-xl-3">
           <a href="{{ url('today-expense') }}">
                <div class="card h-100 position-relative overflow-hidden">
                    <div class="card-body pb-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Today Expense </span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">৳ {{ number_format($todayExpense) }}</h4>
                                </div>
                                <small class="mb-0 text-muted">{{ \Carbon\Carbon::today()->format('d M Y') }}</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-success">
                                 <i class="fa fa-money-bill-wave"></i>  
                                </span>
                            </div>
                        </div>
                        <div id="chart-expense" class="mt-2" style="min-height: 45px;"></div>
                    </div>
                </div>
            </a>
        </div>
        @endrole

        <!-- Today Product Distribution -->
        @role('admin|super-admin')
        <div class="col-sm-6 col-xl-3">
           <a href="{{ url('today-product-distribution') }}">
                <div class="card h-100 position-relative overflow-hidden">
                    <div class="card-body pb-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Today Product Distribution </span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ number_format($todayproductdistribution) }}</h4>
                                </div>
                                <small class="mb-0 text-muted">{{ \Carbon\Carbon::today()->format('d M Y') }}</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-success">
                                   <i class="fa fa-dolly"></i>
                                </span>
                            </div>
                        </div>
                        <div id="chart-product" class="mt-2" style="min-height: 45px;"></div>
                    </div>
                </div>
            </a>
        </div>
        @endrole

    </div>
</div>

@unlessrole('admin|super-admin|staffs|manager')
<!-- RESIDENT NOTICE BOARD MODAL -->
<div class="modal fade notranslate" translate="no" id="residentNoticeBoardModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header py-3 px-4 text-white" style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); border-radius: 16px 16px 0 0;">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2 mb-0">
                    <i class="fa fa-bullhorn"></i>
                    <span>জরুরি নোটিশ বোর্ড (Notice Board)</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
                @if(isset($activeNoticesCount) && $activeNoticesCount > 0)
                    <div class="d-flex flex-column gap-3">
                        @foreach($activeNotices as $idx => $notice)
                            <div class="card border rounded-3 shadow-none p-3 mb-0" style="border-left: 5px solid {{ $notice->priority == 'urgent' ? '#ef4444' : ($notice->priority == 'important' ? '#f59e0b' : '#4f46e5') }} !important; background: {{ $idx === 0 ? '#f8faff' : '#ffffff' }};">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                                    <div>
                                        <h6 class="fw-bold mb-1 text-dark fs-6">{{ $notice->title ?: 'জরুরি বিজ্ঞপ্তি' }}</h6>
                                        <div class="d-flex align-items-center gap-2">
                                            @if($idx === 0)
                                                <span class="badge bg-primary rounded-pill px-2 py-0.5" style="font-size: 11px;">
                                                    <i class="fa fa-sparkles me-1"></i> সর্বশেষ বিজ্ঞপ্তি
                                                </span>
                                            @endif
                                            <span class="text-muted small">
                                                <i class="fa fa-calendar-alt me-1"></i> {{ \Carbon\Carbon::parse($notice->created_at)->format('d M, Y (h:i A)') }}
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        @if($notice->priority == 'urgent')
                                            <span class="badge bg-danger">অতি জরুরি</span>
                                        @elseif($notice->priority == 'important')
                                            <span class="badge bg-warning text-dark">গুরুত্বপূর্ণ</span>
                                        @else
                                            <span class="badge bg-secondary">সাধারণ</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-secondary small mt-2" style="white-space: pre-line; line-height: 1.6; font-size: 13.5px;">
                                    {{ $notice->notice }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="fa fa-bell-slash fs-1 text-muted opacity-50 d-block mb-3"></i>
                        <h6>বর্তমানে কোনো নোটিশ নেই</h6>
                        <p class="small mb-0">কর্তৃপক্ষ নতুন নোটিশ দিলে এখানে স্বয়ংক্রিয়ভাবে দেখতে পাবেন।</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endunlessrole

@hasanyrole('admin|super-admin|staffs|manager')
<!-- Modal: Currently In Hostel List -->
<div class="modal fade" id="inHostelListModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-label-success text-success py-3 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar avatar-sm bg-success text-white rounded d-flex align-items-center justify-content-center">
                        <i class="fa fa-home"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-success mb-0">বর্তমানে হোস্টেলে অবস্থানরত রেসিডেন্ট তালিকা (Currently In Hostel)</h5>
                        <small class="text-muted">হোস্টেলে উপস্থিত মোট রেসিডেন্ট সংখ্যা: <strong class="text-success">{{ $dashInsideCount ?? count($insideResidents ?? []) }} জন</strong></small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                <!-- Search & Actions Bar -->
                <div class="row g-2 mb-3 align-items-center">
                    <div class="col-md-7">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa fa-search text-muted"></i></span>
                            <input type="text" id="inHostelSearchInput" class="form-control bg-light border-start-0 ps-0" placeholder="নাম, মোবাইল নম্বর বা রুম/সিট লিখে খুঁজুন..." onkeyup="filterInHostelTable()">
                        </div>
                    </div>
                    <div class="col-md-5 text-md-end">
                        <span class="badge bg-label-success py-2 px-3 fw-semibold">
                            <i class="fa fa-users me-1"></i> মোট রেসিডেন্ট: {{ $dashInsideCount ?? count($insideResidents ?? []) }} জন
                        </span>
                    </div>
                </div>

                <!-- Residents Table -->
                <div class="table-responsive border rounded-3">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>রেসিডেন্টের নাম</th>
                                <th>মোবাইল নম্বর</th>
                                <th>রুম ও সিট নম্বর</th>
                                <th>হোস্টেলে প্রবেশের তারিখ</th>
                                <th>অভিভাবক / ইমারজেন্সি</th>
                                <th class="text-center">স্ট্যাটাস</th>
                            </tr>
                        </thead>
                        <tbody id="inHostelTableBody">
                            @forelse($insideResidents ?? [] as $index => $res)
                            <tr>
                                <td class="fw-semibold text-muted">{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm bg-label-success text-success rounded-circle me-2 fw-bold d-flex align-items-center justify-content-center">
                                            {{ strtoupper(substr($res['name'] ?? 'R', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $res['name'] }}</div>
                                            @if(!empty($res['email']))
                                                <small class="text-muted">{{ $res['email'] }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="tel:{{ $res['phone'] }}" class="fw-semibold text-primary text-decoration-none d-inline-flex align-items-center gap-1">
                                        <i class="fa fa-phone fs-7"></i> {{ $res['phone'] }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-label-info border border-info-subtle px-2 py-1">
                                        {{ $res['floor_room_seat'] }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-medium text-dark">
                                        <i class="fa fa-calendar-check text-success me-1"></i> {{ $res['check_in_date'] }}
                                    </div>
                                </td>
                                <td>
                                    @if($res['guardian_phone'] && $res['guardian_phone'] !== '-')
                                        <a href="tel:{{ $res['guardian_phone'] }}" class="text-muted small text-decoration-none">
                                            <i class="fa fa-phone-alt text-danger me-1"></i>{{ $res['guardian_phone'] }}
                                        </a>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success text-white px-2.5 py-1.5 rounded-pill shadow-xs">
                                        <i class="fa fa-check-circle me-1"></i> হোস্টেলে উপস্থিত
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fa fa-user-slash fs-2 d-block mb-2 text-muted"></i>
                                    বর্তমানে কোনো রেসিডেন্ট তথ্য পাওয়া যায়নি।
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div id="inHostelNoResults" class="text-center py-4 text-muted d-none">
                    <i class="fa fa-search fs-2 d-block mb-2 text-muted"></i>
                    কোনো রেসিডেন্টের তথ্যের সাথে মিল পাওয়া যায়নি।
                </div>
            </div>
            <div class="modal-footer bg-light py-2 border-top d-flex justify-content-between">
                <a href="{{ route('check-in-out.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fa fa-list me-1"></i> চেক-ইন / আউট ব্যবস্থাপনা পেজ
                </a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">বন্ধ করুন</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Currently Outside / On Leave List -->
<div class="modal fade" id="outsideListModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-label-warning text-warning py-3 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar avatar-sm bg-warning text-white rounded d-flex align-items-center justify-content-center">
                        <i class="fa fa-walking"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-warning mb-0">বর্তমানে বাইরে / ছুটিতে থাকা রেসিডেন্ট তালিকা (Outside / On Leave)</h5>
                        <small class="text-muted">বর্তমানে বাইরে মোট রেসিডেন্ট সংখ্যা: <strong class="text-warning">{{ $dashOutsideCount ?? count($outsideResidents ?? []) }} জন</strong></small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                <!-- Search & Actions Bar -->
                <div class="row g-2 mb-3 align-items-center">
                    <div class="col-md-7">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa fa-search text-muted"></i></span>
                            <input type="text" id="outsideSearchInput" class="form-control bg-light border-start-0 ps-0" placeholder="নাম, মোবাইল নম্বর বা ছুটির কারণ লিখে খুঁজুন..." onkeyup="filterOutsideTable()">
                        </div>
                    </div>
                    <div class="col-md-5 text-md-end">
                        <span class="badge bg-label-warning text-dark py-2 px-3 fw-semibold">
                            <i class="fa fa-walking me-1"></i> বাইরে আছেন: {{ $dashOutsideCount ?? count($outsideResidents ?? []) }} জন
                        </span>
                    </div>
                </div>

                <!-- Outside Residents Table -->
                <div class="table-responsive border rounded-3">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>রেসিডেন্টের নাম</th>
                                <th>মোবাইল নম্বর</th>
                                <th>রুম ও সিট</th>
                                <th>হোস্টেল ত্যাগের সময়</th>
                                <th>প্রত্যাশিত ফেরার তারিখ</th>
                                <th>ছুটির কারণ</th>
                                <th>জরুরি নম্বর</th>
                                <th class="text-center">স্ট্যাটাস</th>
                                <th class="text-center" style="width: 130px;">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody id="outsideTableBody">
                            @forelse($outsideResidents ?? [] as $index => $res)
                            <tr class="table-warning bg-opacity-10">
                                <td class="fw-semibold text-muted">{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm bg-label-warning text-warning rounded-circle me-2 fw-bold d-flex align-items-center justify-content-center">
                                            {{ strtoupper(substr($res['name'] ?? 'R', 0, 1)) }}
                                        </div>
                                        <div class="fw-bold text-dark">{{ $res['name'] }}</div>
                                    </div>
                                </td>
                                <td>
                                    <a href="tel:{{ $res['phone'] }}" class="fw-semibold text-primary text-decoration-none d-inline-flex align-items-center gap-1">
                                        <i class="fa fa-phone fs-7"></i> {{ $res['phone'] }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1">
                                        {{ $res['floor_room_seat'] }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-medium text-dark">
                                        <i class="fa fa-clock text-warning me-1"></i> {{ $res['check_out_date'] }}
                                    </div>
                                </td>
                                <td>
                                    @if($res['is_overdue'])
                                        <div class="fw-bold text-danger">{{ $res['expected_return_date'] }}</div>
                                        <span class="badge bg-danger px-1.5 py-0.5" style="font-size: 10px;">⏰ Overdue</span>
                                    @else
                                        <div class="fw-medium text-dark">{{ $res['expected_return_date'] }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="small fw-medium text-dark">{{ $res['leave_reason'] }}</div>
                                    @if(!empty($res['admin_notes']))
                                        <small class="text-muted d-block" title="{{ $res['admin_notes'] }}">
                                            <i class="fa fa-info-circle me-1"></i>{{ \Illuminate\Support\Str::limit($res['admin_notes'], 25) }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    @if($res['emergency_contact'] && $res['emergency_contact'] !== '-')
                                        <a href="tel:{{ $res['emergency_contact'] }}" class="text-danger small text-decoration-none">
                                            <i class="fa fa-phone-alt me-1"></i>{{ $res['emergency_contact'] }}
                                        </a>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($res['is_overdue'])
                                        <span class="badge bg-danger text-white px-2.5 py-1 rounded-pill">
                                            <i class="fa fa-exclamation-triangle me-1"></i> সময় পার হয়েছে
                                        </span>
                                    @else
                                        <span class="badge bg-warning text-dark px-2.5 py-1 rounded-pill fw-bold">
                                            <i class="fa fa-walking me-1"></i> বাইরে আছেন
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-success py-1 px-2 fw-semibold shadow-xs" onclick="handleAdminCheckInFromModal({{ $res['id'] }}, '{{ addslashes($res['name']) }}')">
                                        <i class="fa fa-sign-in-alt me-1"></i> চেক-ইন করান
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">
                                    <i class="fa fa-user-check fs-2 d-block mb-2 text-success"></i>
                                    বর্তমানে কোনো রেসিডেন্ট বাইরে বা ছুটিতে নেই। সবাই হোস্টেলে উপস্থিত আছেন।
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div id="outsideNoResults" class="text-center py-4 text-muted d-none">
                    <i class="fa fa-search fs-2 d-block mb-2 text-muted"></i>
                    কোনো রেসিডেন্টের তথ্যের সাথে মিল পাওয়া যায়নি।
                </div>
            </div>
            <div class="modal-footer bg-light py-2 border-top d-flex justify-content-between">
                <a href="{{ route('check-in-out.index') }}?status=outside" class="btn btn-outline-warning text-dark btn-sm">
                    <i class="fa fa-list me-1"></i> সকল চেক-আউট হিস্ট্রি পেজ
                </a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">বন্ধ করুন</button>
            </div>
        </div>
    </div>
</div>
@endhasanyrole

<style type="text/css">
.modal {
    z-index: 1085 !important;
}
.modal-backdrop {
    z-index: 1075 !important;
}
#residentNoticeBoardModal {
    z-index: 99999 !important;
}
#residentNoticeBoardModal .modal-dialog {
    z-index: 100000 !important;
    position: relative !important;
}
.card {
    transition: transform 0.2s, box-shadow 0.2s;
    border-radius: 10px;
}
.card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(99,102,241,0.15);
}
.card:hover h4 {
    color: #6366f1;
    transform: scale(1.05);
    display: inline-block;
    transition: color 0.2s, transform 0.2s;
}
.card:hover .avatar-initial {
    background-color: #6366f1 !important;
    color: white !important;
}
</style>

<!-- ApexCharts Script for Mini Sparklines on ALL Cards -->
<script src="{{ asset('backend/vendor/libs/apex-charts/apexcharts.js') }}"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const createSparkline = (selector, data, color) => {
        const el = document.querySelector(selector);
        if (!el) return;
        const options = {
            chart: {
                type: 'area',
                height: 45,
                sparkline: { enabled: true },
                animations: { enabled: true }
            },
            stroke: { curve: 'smooth', width: 2 },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.05,
                    stops: [0, 90, 100]
                }
            },
            series: [{ name: 'Count', data: data }],
            colors: [color],
            tooltip: { enabled: false }
        };
        new ApexCharts(el, options).render();
    };

    const rentData = {!! $rentTrends ?? '[]' !!};
    const expenseData = {!! $expenseTrends ?? '[]' !!};
    const mealData = {!! $mealTrends ?? '[]' !!};
    const productData = {!! $productTrends ?? '[]' !!};

    createSparkline("#chart-mybooking", [1, 1, 1, 1, 1, 1, 1], "#6366f1");
    createSparkline("#chart-mypayments", [5000, 5000, 5000, 5000, 5000, 5000, 5000], "#28c76f");
    createSparkline("#chart-mymeals", [2, 3, 2, 3, 2, 3, 2], "#ff9f43");
    createSparkline("#chart-createmeal", [1, 2, 1, 2, 1, 2, 1], "#00cfdd");

    createSparkline("#chart-floor", [1, 2, 2, 3, 3, 3, 3], "#6366f1");
    createSparkline("#chart-room", [60, 62, 65, 65, 68, 68, 68], "#ea5455");
    createSparkline("#chart-totalseat", [70, 72, 75, 78, 78, 78, 78], "#7367f0");
    createSparkline("#chart-availseat", [78, 77, 76, 76, 76, 76, 76], "#00cfdd");
    createSparkline("#chart-bookedseat", [0, 1, 2, 2, 2, 2, 2], "#28c76f");
    createSparkline("#chart-staff", [1, 2, 2, 3, 3, 3, 3], "#28c76f");
    createSparkline("#chart-release", [0, 0, 1, 1, 1, 1, 1], "#ff9f43");

    createSparkline("#chart-rent", rentData.length ? rentData : [10, 25, 18, 40, 35, 60, 80], "#28c76f");
    createSparkline("#chart-due", [5, 12, 8, 15, 10, 22, 18], "#ea5455");
    createSparkline("#chart-meal", mealData.length ? mealData : [40, 50, 45, 60, 55, 70, 65], "#ff9f43");
    createSparkline("#chart-expense", expenseData.length ? expenseData : [12, 18, 14, 25, 20, 30, 28], "#7367f0");
    createSparkline("#chart-product", productData.length ? productData : [2, 5, 3, 8, 6, 12, 10], "#00cfdd");
});
</script>

<script>
window.userBookingData = @json($userBooking ?? null);
window.userProfileData = @json(auth()->user());

function printResidentDocument() {
    var r = window.userBookingData;
    var u = window.userProfileData || {};

    if (!r) {
        alert('No active booking record found!');
        return;
    }

    var logoUrl = window.location.origin + '/logo/logoimage (2).png';
    var userImgUrl = r.image ? (window.location.origin + '/bookingsimage/' + r.image) : (u.avatar_url || '');

    var roomItems = [];
    if (typeof r.floor_number_room_number_roomprice === 'string') {
        try { roomItems = JSON.parse(r.floor_number_room_number_roomprice); } catch(e){}
    } else if (Array.isArray(r.floor_number_room_number_roomprice)) {
        roomItems = r.floor_number_room_number_roomprice;
    }

    function getRoomNo(str) {
        if (!str) return '-';
        var s = String(str).trim();
        var m = s.match(/^(\d+)\-Block\s+([A-Za-z])/i);
        if (m) return m[1] + '-Block ' + m[2].toUpperCase();
        var m2 = s.match(/^([A-Za-z]+)[\-_](\d+)/);
        if (m2) return parseInt(m2[2], 10) + '-Block ' + m2[1].toUpperCase();
        return s;
    }
    function getSeatNo(str) {
        if (!str) return '-';
        var s = String(str).trim();
        if (/^\d+\-Block\s+[A-Za-z]/i.test(s)) return s;
        var m = s.match(/^([A-Za-z]+)[\-_](\d+)(.*)$/);
        if (m) {
            var let = m[1].toUpperCase();
            var num = parseInt(m[2], 10);
            var sub = '';
            var sm = m[3].match(/(\d+)$/);
            if (sm) sub = '-' + sm[1];
            return num + '-Block ' + let + sub;
        }
        return s;
    }

    var roomNo = roomItems.length
        ? roomItems.map(function(i){ return getRoomNo(i.roomnumber); }).join(', ')
        : (getRoomNo(r.roomnumber) || r.room_number || '-');
    var seatNo = roomItems.length
        ? roomItems.map(function(i){ return getSeatNo(i.roomnumber); }).join(', ')
        : (getSeatNo(r.roomnumber) || '-');
    var floorNo = roomItems.length
        ? Array.from(new Set(roomItems.map(function(i){ return i.floornumber; }))).filter(Boolean).join(', ')
        : (r.floornumber || '-');

    var fullName = r.full_name || u.name || '-';
    var phone = r.phone || u.phone || '-';
    var address = r.address || u.address || '-';
    var thanaName = (r.thana && r.thana.name) ? r.thana.name : (r.thana_name || '-');
    var districtName = (r.district && r.district.name) ? r.district.name : (r.district_name || '-');
    var bookingDate = r.created_at
        ? new Date(r.created_at).toLocaleString('bn-BD', { dateStyle: 'long', timeStyle: 'short' })
        : '-';

    var userType = (r.user_type || u.user_type || '').toLowerCase();
    var isProf = userType.includes('professional') || userType.includes('job') || userType.includes('passenger');

    var docTitle = isProf ? 'কর্মজীবীর তথ্য - টি এস এস ভিলা' : 'শিক্ষার্থীর তথ্য - টি এস এস ভিলা';
    var sectionTitleText = isProf ? 'কর্মজীবীর তথ্য' : 'শিক্ষার্থীর তথ্য';
    var signatureLabelText = isProf ? 'কর্মজীবীর স্বাক্ষর' : 'শিক্ষার্থীর স্বাক্ষর';

    var institutionName = r.institution_name || u.institution_name || '-';
    var workplaceName = r.workplace_name || u.workplace_name || '-';
    var fatherName = r.father_name || u.father_name || '-';
    var fatherPhone = r.father_phone || u.father_phone || '-';
    var motherName = r.mother_name || u.mother_name || '-';
    var motherPhone = r.mother_phone || u.mother_phone || '-';
    var email = r.email || u.email || '-';
    var nid = r.nid || u.nid || '-';

    var infoSectionHtml = '';
    if (isProf) {
        infoSectionHtml = `
          <div class="form-pill-row">
            <div class="pill-lbl">কর্মজীবীর পূর্ণ নাম :</div>
            <div class="pill-val">${fullName}</div>
            <div class="pill-right">
              <div class="pill-lbl">মোবাইল নং :</div>
              <div class="pill-val">${phone}</div>
            </div>
          </div>

          <div class="form-pill-row">
            <div class="pill-lbl">কর্মপ্রতিষ্ঠানের নাম:</div>
            <div class="pill-val">${workplaceName}</div>
            <div class="pill-right">
              <div class="pill-lbl">NID নম্বর :</div>
              <div class="pill-val">${nid}</div>
            </div>
          </div>

          <div class="form-pill-row">
            <div class="pill-lbl">ইমেইল ঠিকানা:</div>
            <div class="pill-val">${email}</div>
            <div class="pill-right">
              <div class="pill-lbl">পেশা/টাইপ:</div>
              <div class="pill-val">${r.user_type || u.user_type || 'Working Professional'}</div>
            </div>
          </div>

          ${(fatherName !== '-' || motherName !== '-') ? `
          <div class="form-pill-row">
            <div class="pill-lbl">পিতার নাম:</div>
            <div class="pill-val">${fatherName}</div>
            <div class="pill-right">
              <div class="pill-lbl">মাতার নাম:</div>
              <div class="pill-val">${motherName}</div>
            </div>
          </div>
          ` : ''}
        `;
    } else {
        infoSectionHtml = `
          <div class="form-pill-row">
            <div class="pill-lbl">শিক্ষার্থীর পূর্ণ নাম :</div>
            <div class="pill-val">${fullName}</div>
            <div class="pill-right">
              <div class="pill-lbl">মোবাইল নং :</div>
              <div class="pill-val">${phone}</div>
            </div>
          </div>

          <div class="form-pill-row">
            <div class="pill-lbl">অধ্যয়নরত শিক্ষা প্রতিষ্ঠানের নাম:</div>
            <div class="pill-val">${institutionName}</div>
          </div>

          <div class="form-pill-row">
            <div class="pill-lbl">পিতার নাম:</div>
            <div class="pill-val">${fatherName}</div>
            <div class="pill-right">
              <div class="pill-lbl">মোবাইল নং :</div>
              <div class="pill-val">${fatherPhone}</div>
            </div>
          </div>

          <div class="form-pill-row">
            <div class="pill-lbl">মাতার নাম:</div>
            <div class="pill-val">${motherName}</div>
            <div class="pill-right">
              <div class="pill-lbl">মোবাইল নং :</div>
              <div class="pill-val">${motherPhone}</div>
            </div>
          </div>
        `;
    }

    function getAdvanceDepositAmount(b) {
      if (!b) return 0;
      if (roomItems && roomItems.length) {
        var sum = 0;
        roomItems.forEach(function(item) {
          sum += Number(item.advance_price || item.original_advance_price || item.price || 0);
        });
        if (sum > 0) return sum;
      }
      if (b.advance_fee !== null && b.advance_fee !== undefined && Number(b.advance_fee) > 0) {
        return Number(b.advance_fee);
      }
      if (b.roomprice !== null && b.roomprice !== undefined && Number(b.roomprice) > 0) {
        return Number(b.roomprice);
      }
      return Number(b.monthly_amount || 0);
    }

    function getDevFeeAmount(b) {
      if (!b) return 0;
      if (b.development_fee === null || b.development_fee === undefined) return 0;
      return Number(b.development_fee);
    }

    function formatCurrency(val) {
      if (val === null || val === undefined || isNaN(val)) return '0';
      return Number(val).toLocaleString('en-US');
    }

    var advance = getAdvanceDepositAmount(r);
    var devFee = getDevFeeAmount(r);
    var totalAmount = advance + devFee;

    var devFeeDocHtml = devFee > 0 ? `
        <div style="display: flex; justify-content: space-between; font-size: 14.5px; padding: 3px 0;">
          <span style="color: #b45309; font-weight: 700;">Development Fee (One-time):</span>
          <span style="font-weight: 800; color: #d97706;">+ ৳ ${formatCurrency(devFee)}</span>
        </div>
        <hr style="border-top: 1.5px dashed #f59e0b; margin: 6px 0; opacity: 0.7;">
    ` : '';

    var feeSectionHtml = `
        <div class="section-title" style="margin-top: 10px; margin-bottom: 8px;">পেমেন্ট ও ফি বিবরণী</div>
        <div style="background-color: #fdf8e6; border: 2px solid #f59e0b; border-radius: 10px; padding: 10px 18px; margin-bottom: 10px;">
          <div style="display: flex; justify-content: space-between; font-size: 14.5px; padding: 3px 0;">
            <span style="color: #555; font-weight: 700;">Advance Deposit / Room Price:</span>
            <span style="font-weight: 800; color: #111;">৳ ${formatCurrency(advance)}</span>
          </div>
          ${devFeeDocHtml}
          <div style="display: flex; justify-content: space-between; font-size: 16px; font-weight: 800; padding-top: 2px;">
            <span style="color: #111;">Total Amount Payable:</span>
            <span style="color: #059669; font-size: 18px;">৳ ${formatCurrency(totalAmount)}</span>
          </div>
        </div>
    `;

    var html = `
        <!DOCTYPE html>
        <html lang="bn">
        <head>
          <meta charset="UTF-8">
          <title>${docTitle}</title>
          <style>
            @import url('https://fonts.googleapis.com/css2?family=Tiro+Bangla&family=Hind+Siliguri:wght@400;500;600;700;800&display=swap');
            @page { size: A4 portrait; margin: 5mm; }
            * { box-sizing: border-box; margin: 0; padding: 0; }
            html, body {
              height: 100%; width: 100%; background: #fff;
              font-family: 'Hind Siliguri', 'Tiro Bangla', sans-serif;
              padding: 0; margin: 0; color: #000;
              -webkit-print-color-adjust: exact; print-color-adjust: exact;
            }
            .paper-frame {
              background: linear-gradient(165deg, #fef9e7 0%, #fef3cd 50%, #fef9e7 100%);
              border: 4px solid #27ae60; border-radius: 10px;
              padding: 18px 22px 16px 22px; box-sizing: border-box;
              height: calc(297mm - 10mm); min-height: calc(297mm - 10mm);
              display: flex; flex-direction: column; justify-content: space-between;
              position: relative; page-break-inside: avoid;
            }
            .paper-frame::before {
              content: ''; position: absolute; inset: 4px;
              border: 1.5px solid #f39c12; border-radius: 7px; pointer-events: none;
            }
            .main-content-wrap { display: flex; flex-direction: column; flex-grow: 1; justify-content: space-evenly; }
            .top-header { display: flex; align-items: center; justify-content: center; gap: 18px; margin-bottom: 10px; padding: 0 4px; }
            .logo-wrap img { width: 78px; height: 78px; object-fit: contain; }
            .brand-center { text-align: center; flex: 1; }
            .brand-name { font-size: 46px; font-weight: 900; color: #c0392b; font-family: 'Tiro Bangla', serif; line-height: 1; letter-spacing: 1px; }
            .photo-box { width: 110px; height: 125px; border: 2px solid #2c3e50; border-radius: 4px; display: flex; align-items: center; justify-content: center; overflow: hidden; background: #f0f0f0; flex-shrink: 0; }
            .photo-box img { width: 100%; height: 100%; object-fit: cover; }
            .photo-placeholder { font-size: 11px; color: #888; text-align: center; }
            .address-banner { background: #1a237e; color: #fff; text-align: center; padding: 9px 14px; font-size: 13.5px; font-weight: 600; border-radius: 5px; margin-bottom: 12px; letter-spacing: 0.2px; }
            .room-meta-row { display: flex; gap: 12px; margin-bottom: 12px; }
            .room-meta-box { flex: 1; border: 2px solid #27ae60; border-radius: 6px; padding: 8px 12px; background: #fff; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 5px; }
            .room-meta-box .lbl { color: #1a5c2e; font-weight: 700; white-space: nowrap; }
            .room-meta-box .val { color: #000; font-weight: 800; font-size: 14.5px; }
            .section-title-container { display: flex; align-items: center; justify-content: space-between; border-bottom: 2.5px solid #f39c12; padding-bottom: 3px; margin: 10px 0 8px 0; }
            .title-side-dummy { flex: 1; }
            .section-title-container .section-title { text-align: center; font-size: 21.5px; font-weight: 800; color: #e74c3c; font-family: 'Tiro Bangla', serif; letter-spacing: 0.3px; flex: 2; margin: 0; padding-bottom: 0; border-bottom: none; }
            .booking-date-right-box { flex: 1; text-align: right; font-size: 13px; font-weight: 700; white-space: nowrap; }
            .booking-date-right-box .lbl { color: #1a5c2e; font-weight: 800; }
            .booking-date-right-box .val { color: #000; font-weight: 800; }
            .section-title { text-align: center; font-size: 21.5px; font-weight: 800; color: #e74c3c; font-family: 'Tiro Bangla', serif; border-bottom: 2.5px solid #f39c12; padding-bottom: 3px; margin: 10px 0 8px 0; letter-spacing: 0.3px; }
            .form-pill-row { border: 2px solid #8e44ad; border-radius: 25px; background: #fff; display: flex; overflow: hidden; margin-bottom: 10px; min-height: 42px; align-items: stretch; }
            .pill-lbl { background: #7b1fa2; color: #fff; padding: 9px 18px; font-size: 14px; font-weight: 700; white-space: nowrap; display: flex; align-items: center; border-right: 2px solid #8e44ad; flex-shrink: 0; }
            .pill-val { padding: 9px 18px; font-size: 14.5px; font-weight: 600; color: #111; flex-grow: 1; display: flex; align-items: center; }
            .pill-right { border-left: 2px solid #8e44ad; display: flex; align-items: stretch; flex-shrink: 0; }
            .address-row { border: 2px solid #8e44ad; border-radius: 25px; background: #fff; display: flex; flex-wrap: wrap; padding: 11px 20px; font-size: 14px; font-weight: 600; gap: 10px 30px; margin-bottom: 10px; align-items: center; min-height: 42px; }
            .akey { color: #7b1fa2; font-weight: 700; }
            .rules-box { background: #fffde7; border: 2px solid #f48fb1; border-radius: 12px; padding: 14px 20px; margin-top: 4px; margin-bottom: 10px; }
            .rules-list { list-style: none; padding: 0; margin: 0; }
            .rules-list li { font-size: 13px; font-weight: 600; color: #1a1a1a; line-height: 1.8; border-bottom: 1px dashed #f8bbd0; padding-bottom: 4px; margin-bottom: 4px; }
            .rules-list li:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
            .signature-row { display: flex; justify-content: space-between; align-items: flex-end; margin-top: auto; padding: 16px 36px 8px 36px; }
            .sig-box { text-align: center; min-width: 150px; }
            .sig-line { border-top: 1.5px solid #2c3e50; margin-bottom: 5px; }
            .sig-text { font-size: 14px; font-weight: 700; color: #1a1a1a; }
            @media print {
              html, body { height: 100% !important; margin: 0 !important; padding: 0 !important; background: #fff !important; }
              .paper-frame { height: calc(297mm - 10mm) !important; min-height: calc(297mm - 10mm) !important; border-radius: 0; box-sizing: border-box; page-break-inside: avoid !important; }
            }
          </style>
        </head>
        <body>
          <div class="paper-frame">
            <div class="main-content-wrap">
              <div class="top-header">
                <div class="logo-wrap">
                  <img src="${logoUrl}" alt="Logo" onerror="this.style.display='none'">
                </div>
                <div class="brand-center">
                  <div class="brand-name">টি এস এস ভিলা</div>
                </div>
                <div class="photo-box">
                  ${userImgUrl ? `<img src="${userImgUrl}" alt="Photo">` : `<div class="photo-placeholder">ছবি<br>Photo</div>`}
                </div>
              </div>
              <div class="address-banner">
                কলেজ রোড , নেসকো গেট সংলগ্ন , রংপুর &nbsp;|&nbsp; প্রয়োজনে: ০১৯৭৭২৭০৯২০ &nbsp;|&nbsp; Gmail: tssvilla2026@gmail.com
              </div>
              <div class="room-meta-row">
                <div class="room-meta-box"><span class="lbl">রুম নং:</span>&nbsp;<span class="val">${roomNo}</span></div>
                <div class="room-meta-box"><span class="lbl">ব্লক নং:</span>&nbsp;<span class="val">${seatNo}</span></div>
                <div class="room-meta-box"><span class="lbl">ফ্লোর নং:</span>&nbsp;<span class="val">${floorNo}</span></div>
              </div>
              <div class="section-title-container">
                <div class="title-side-dummy"></div>
                <div class="section-title">${sectionTitleText}</div>
                <div class="booking-date-right-box">
                  <span class="lbl">বুকিং তারিখ:</span> <span class="val">${bookingDate}</span>
                </div>
              </div>
              ${infoSectionHtml}


              <div class="section-title">স্থায়ী ঠিকানা</div>
              <div class="address-row">
                <span><span class="akey">গ্রাম/রাস্তা:</span> ${address}</span>
                <span><span class="akey">থানা:</span> ${thanaName}</span>
                <span><span class="akey">উপজেলা:</span> ${thanaName}</span>
                <span><span class="akey">জেলা:</span> ${districtName}</span>
              </div>
              <div class="section-title">নিয়মাবলী</div>


              <div class="rules-box">
                ${isProf ? `
                <ul class="rules-list">
                  <li>* মেসের ভাড়া ০৭ তারিখের মধ্যে পরিশোধ করতে হবে।</li>
                  <li>* মেস ছাড়লে ০২ মাস পূর্বেই মেস কর্তৃপক্ষকে জানাতে হবে। অন্যথায় দুই মাসের ভাড়া দিয়ে মেস ছাড়তে হবে।</li>
                  <li>* রুম চেঞ্জ করতে চাইলে ৫০০ টাকা জরিমানা প্রদান করতে হবে।</li>
                  <li>* মেসের নিয়ম-কানুন মেনে চলতে হবে। কারো বিরুদ্ধে কোনো অভিযোগ আসলে এবং তা প্রমাণিত হলে সিট বাতিলসহ যেকোনো ব্যবস্থা নেয়ার অধিকার কর্তৃপক্ষ রাখে।</li>
                </ul>
                ` : `
                <ul class="rules-list">
                  <li>* মেসের ভাড়া ০৭ তারিখের মধ্যে পরিশোধ করতে হবে।</li>
                  <li>* মেস ছাড়লে ০২ মাস পূর্বেই মেস কর্তৃপক্ষকে জানাতে হবে। অন্যথায় দুই মাসের ভাড়া দিয়ে মেস ছাড়তে হবে।</li>
                  <li>* মাগরিবের আযানের পর মেসের বাহিরে থাকলে অভিভাবককে জানিয়ে দিতে হবে।</li>
                  <li>* রুম চেঞ্জ করতে চাইলে ৫০০ টাকা জরিমানা প্রদান করতে হবে।</li>
                  <li>* মেসের নিয়ম-কানুন মেনে চলতে হবে। কারো বিরুদ্ধে কোনো অভিযোগ আসলে এবং তা প্রমাণিত হলে সিট বাতিলসহ যেকোনো ব্যবস্থা নেয়ার অধিকার কর্তৃপক্ষ রাখে।</li>
                </ul>
                `}
              </div>
            </div>
            <div class="signature-row">
              <div class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-text">অনুমোদিত স্বাক্ষর</div>
              </div>
              <div class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-text">${signatureLabelText}</div>
              </div>
            </div>
          </div>
        </body>
        </html>
    `;

    var win = window.open('', '_blank');
    if (win) {
        win.document.write(html);
        win.document.close();
        setTimeout(function() {
            win.focus();
            win.print();
        }, 400);
    } else {
        alert('Please allow popups for this site to print document.');
    }
}

function dismissMealNotif(id) {

    fetch('/meal-requests/' + id + '/dismiss-user-notif', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    }).then(res => res.json())
    .then(data => {
        const box = document.getElementById('mealNotifBox_' + id);
        if (box) box.remove();
    });
}

@unlessrole('admin|super-admin|staffs|manager')
document.addEventListener('DOMContentLoaded', function() {
    function checkDashboardUserNotifsLive() {
        fetch("{{ route('meal-requests.check-user-notifs') }}", {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Live notice banner update / real-time removal when admin activates meal
                const noticeBanner = document.getElementById('mealNoticeBanner');
                if (data.warning_message) {
                    if (noticeBanner) {
                        const msgDiv = document.getElementById('mealNoticeMessage');
                        if (msgDiv) msgDiv.innerText = data.warning_message;
                    } else {
                        const container = document.querySelector('.container-xxl .row.g-6');
                        if (container) {
                            const newBanner = document.createElement('div');
                            newBanner.className = 'col-12 mb-3';
                            newBanner.id = 'mealNoticeBanner';
                            newBanner.innerHTML = `
                                <div class="alert alert-info border-info d-flex align-items-center justify-content-between flex-wrap gap-2 p-3 mb-0 shadow-sm rounded-3" role="alert">
                                    <div class="d-flex align-items-center">
                                        <span class="alert-icon text-info me-3 fs-3">
                                            <i class="fa fa-info-circle"></i>
                                        </span>
                                        <div>
                                            <h6 class="alert-heading mb-1 text-info fw-bold fs-6">📌 মিল সার্ভিস সংক্রান্ত তথ্য (Meal Service Information)</h6>
                                            <div class="fw-medium text-dark fs-7" id="mealNoticeMessage">
                                                ${data.warning_message}
                                            </div>
                                        </div>
                                    </div>
                                    <a href="tel:01977270920" class="btn btn-sm btn-info fw-bold shadow-sm waves-effect waves-light px-3 py-2">
                                        <i class="fa fa-phone-alt me-1"></i> এডমিন এর সাথে যোগাযোগ করুন
                                    </a>
                                </div>
                            `;
                            container.prepend(newBanner);
                        }
                    }
                } else {
                    if (noticeBanner) noticeBanner.remove();
                }

                // Live Admin Turned OFF Banner & Action Button update / real-time removal
                const adminBanner = document.getElementById('adminMealOffNoticeBanner');
                const actionBtnWrap = document.getElementById('todayMealActionBtnWrap');
                const mealCreateUrl = "{{ route('meals.create') }}";
                const phoneNum = "{{ $webSetting['phone'] ?? '01977270920' }}";

                if (data.is_admin_turned_off) {
                    if (!adminBanner) {
                        const container = document.querySelector('.container-xxl .row.g-6');
                        if (container) {
                            const newAdminBanner = document.createElement('div');
                            newAdminBanner.className = 'col-12 mb-3';
                            newAdminBanner.id = 'adminMealOffNoticeBanner';
                            newAdminBanner.innerHTML = `
                                <div class="alert alert-danger border-danger d-flex align-items-center justify-content-between flex-wrap gap-2 p-3 mb-0 shadow-sm rounded-3" role="alert">
                                    <div class="d-flex align-items-center">
                                        <span class="alert-icon text-danger me-3 fs-3">
                                            <i class="fa fa-ban"></i>
                                        </span>
                                        <div>
                                            <h6 class="alert-heading mb-1 text-danger fw-bold fs-6">🚫 কর্তৃপক্ষ কর্তৃক আপনার মিল বন্ধ রাখা হয়েছে</h6>
                                            <div class="fw-medium text-dark fs-7">
                                                কর্তৃপক্ষ/এডমিন আপনার মিল বন্ধ করে দিয়েছেন। মিল পুনরায় চালু করার জন্য অনুগ্রহ করে মেস কর্তৃপক্ষের সাথে যোগাযোগ করুন।
                                            </div>
                                        </div>
                                    </div>
                                    <a href="tel:${phoneNum}" class="btn btn-danger fw-bold shadow-sm waves-effect waves-light px-3 py-2">
                                        <i class="fa fa-phone-alt me-1"></i> মিল চালুর জন্য কল করুন (${phoneNum})
                                    </a>
                                </div>
                            `;
                            container.prepend(newAdminBanner);
                        }
                    }
                    if (actionBtnWrap) {
                        actionBtnWrap.innerHTML = `
                            <a href="tel:${phoneNum}" class="btn btn-sm btn-danger w-100 fw-bold shadow-sm waves-effect">
                                <i class="fa fa-phone-alt me-1"></i> মিল চালুর জন্য কল করুন
                            </a>
                            <small class="d-block text-muted text-center mt-1" style="font-size: 0.72rem;">
                                ☀️ লাঞ্চ কাটঅফ: ১০:০০ AM | 🌙 ডিনার: ০৪:০০ PM
                            </small>
                        `;
                    }
                } else {
                    if (adminBanner) adminBanner.remove();
                    if (actionBtnWrap && actionBtnWrap.innerHTML.includes('মিল চালুর জন্য কল করুন')) {
                        actionBtnWrap.innerHTML = `
                            <a href="${mealCreateUrl}" class="btn btn-sm btn-outline-info w-100 fw-bold shadow-sm waves-effect">
                                <i class="fa fa-cog me-1"></i> Change / Turn OFF Meal
                            </a>
                            <small class="d-block text-muted text-center mt-1" style="font-size: 0.72rem;">
                                ☀️ লাঞ্চ কাটঅফ: ১০:০০ AM | 🌙 ডিনার: ০৪:০০ PM
                            </small>
                        `;
                    }
                }

                // Live Today's Meal Badge update
                const badgeWrap = document.getElementById('todayMealBadgeWrap');
                if (badgeWrap && data.today_meal_badge_text) {
                    badgeWrap.innerHTML = `<span class="badge ${data.today_meal_badge_class} fs-6 px-2 py-1"><i class="fa ${data.today_meal_badge_icon} me-1"></i> ${data.today_meal_badge_text}</span>`;
                }

                // Live Auto Resume Info Box update
                const resumeWrap = document.getElementById('todayMealOffResumeWrap');
                if (resumeWrap) {
                    if (data.latest_meal_req) {
                        const req = data.latest_meal_req;
                        const badgeColor = req.status == 1 ? 'bg-success text-white' : 'bg-warning text-dark';
                        resumeWrap.innerHTML = `
                            <div class="mt-2 p-2 bg-light-warning rounded border border-warning text-dark text-start" style="font-size: 0.74rem;">
                                <div class="fw-bold text-danger mb-1 d-flex align-items-center justify-content-between">
                                    <span><i class="fa fa-calendar-times me-1"></i> ${req.days_text}</span>
                                    <span class="badge ${badgeColor} px-1 py-0" style="font-size: 0.62rem;">${req.status_text}</span>
                                </div>
                                <div class="text-primary fw-semibold" style="font-size: 0.73rem;">
                                    <i class="fa fa-sync-alt me-1 text-primary"></i> 🔄 <strong>${req.resume_date}</strong> তারিখ থেকে অটো মিল চালু
                                </div>
                            </div>
                        `;
                    } else {
                        resumeWrap.innerHTML = '';
                    }
                }

                // User notifications modal/alerts
                if (data.has_notifs && data.notifications) {
                    const container = document.querySelector('.container-xxl .row.g-6');
                    if (container) {
                        data.notifications.forEach(notif => {
                            const alertType = notif.status == 1 ? 'success' : 'danger';
                            const icon = notif.status == 1 ? 'check-circle' : 'times-circle';

                            const notifDiv = document.createElement('div');
                            notifDiv.className = 'col-12 mb-3';
                            notifDiv.id = 'mealNotifBox_' + notif.id;
                            notifDiv.innerHTML = `
                                <div class="alert alert-${alertType} alert-dismissible d-flex align-items-center justify-content-between p-3 mb-0 shadow-sm border-${alertType} rounded-3" role="alert">
                                    <div class="d-flex align-items-center">
                                        <span class="alert-icon text-${alertType} me-3 fs-3">
                                            <i class="fa fa-${icon}"></i>
                                        </span>
                                        <div>
                                            <h6 class="alert-heading mb-1 text-${alertType} fw-bold fs-6">${notif.title}</h6>
                                            <div class="fw-medium text-dark fs-7">${notif.message}</div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" onclick="dismissMealNotif(${notif.id})"></button>
                                </div>
                            `;
                            container.prepend(notifDiv);
                        });
                    }
                }
            }
        })

        .catch(err => console.log('Dashboard live notif poll skipped', err));
    }
    setInterval(checkDashboardUserNotifsLive, 5000);

    // Move notice modal to body to prevent backdrop stacking issues in Sneat layout
    const resNoticeModal = document.getElementById('residentNoticeBoardModal');
    if (resNoticeModal && resNoticeModal.parentElement !== document.body) {
        document.body.appendChild(resNoticeModal);
    }
});

function handleStudentCheckInFromDash() {
    Swal.fire({
        title: 'Check In Confirmation',
        text: 'আপনি কি নিশ্চিত যে হোস্টেলে ফিরে এসেছেন এবং Check In করতে চান? চেক-ইন সম্পন্ন হলে পরদিন থেকে আপনার মিল পুনরায় স্বয়ংক্রিয়ভাবে চালু হবে। (Are you sure you want to Check In? Meal will resume from tomorrow)',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Check In Now',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('phone', '{{ $residentPhoneForCheckout }}');

            fetch("{{ route('resident.check-in') }}", {
                method: "POST",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Welcome Back!',
                        text: data.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Error!', data.message || 'Check-in could not be processed.', 'error');
                }
            })
            .catch(() => {
                Swal.fire('Error!', 'Server connection error.', 'error');
            });
        }
    });
}
@endunlessrole

function filterInHostelTable() {
    const input = document.getElementById('inHostelSearchInput');
    if (!input) return;
    const filter = input.value.toLowerCase().trim();
    const rows = document.querySelectorAll('#inHostelTableBody tr');
    let visibleCount = 0;

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(filter)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const noRes = document.getElementById('inHostelNoResults');
    if (noRes) {
        noRes.classList.toggle('d-none', visibleCount > 0);
    }
}

function filterOutsideTable() {
    const input = document.getElementById('outsideSearchInput');
    if (!input) return;
    const filter = input.value.toLowerCase().trim();
    const rows = document.querySelectorAll('#outsideTableBody tr');
    let visibleCount = 0;

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(filter)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const noRes = document.getElementById('outsideNoResults');
    if (noRes) {
        noRes.classList.toggle('d-none', visibleCount > 0);
    }
}

function handleAdminCheckInFromModal(id, name) {
    Swal.fire({
        title: 'হোস্টেলে প্রবেশ নিশ্চিতকরণ',
        text: `'${name}' কি হোস্টেলে ফিরে এসেছেন? চেক-ইন সম্পন্ন হলে পরদিন থেকে মিল পুনরায় স্বয়ংক্রিয়ভাবে চালু হবে।`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'হ্যাঁ, চেক-ইন সম্পন্ন করুন',
        cancelButtonText: 'বাতিল'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/check-in-out/${id}/check-in-admin`, {
                method: "POST",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Content-Type": "application/json"
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'সফল!',
                        text: data.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('ত্রুটি!', data.message || 'চেক-ইন ব্যর্থ হয়েছে।', 'error');
                }
            })
            .catch(() => {
                Swal.fire('ত্রুটি!', 'সার্ভার যোগাযোগে সমস্যা হয়েছে।', 'error');
            });
        }
    });
}
</script>
@endsection