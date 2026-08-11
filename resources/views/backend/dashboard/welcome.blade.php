@extends('backend.layouts.app')
@section("title") | {{$page_title}} @endsection
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row g-6 mb-6">
        
        <!-- RESIDENT / STUDENT USER SPECIFIC STAT CARDS -->
        @unlessrole('admin|staffs')
        
        <div class="col-12 mb-2">
            <div class="card border-0 shadow-sm p-3 bg-white" style="border-left: 5px solid #6366f1 !important;">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark">
                            <i class="fa fa-user-circle text-primary me-2"></i>Resident Dashboard (বোর্ডার ড্যাশবোর্ড)
                        </h5>
                        <small class="text-muted d-block mb-1">📌 <strong>নির্দেশাবলী:</strong> কনফার্ম বুকিং ডকুমেন্টটি প্রিন্ট করে এডমিন এর কাছে জমা দিয়ে আপনার বুকিং রসিদ বুঝে নিন।</small>
                        <small class="text-dark fw-semibold"><i class="fa fa-phone-alt me-1 text-success"></i> যেকোনো তথ্যের জন্য এই নম্বরে যোগাযোগ করুন: <a href="tel:01977270920" class="text-primary fw-bold text-decoration-none">01977270920</a></small>
                    </div>
                    @if(isset($userBooking) && $userBooking)
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" onclick="printResidentDocument()" class="btn btn-primary fw-bold waves-effect waves-light shadow-sm">
                            <i class="fa fa-print me-2"></i> Confirm Booking Document
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>

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
                'full'       => 'ফুল মিল (Full Meal)',
                'half_day'   => 'দিনের হাফ মিল',
                'half_night' => 'রাতের হাফ মিল',
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
                                    @elseif($un->request_type == 'half_night')
                                        <br><span class="text-success fw-bold mt-1 d-inline-block"><i class="fa fa-sun me-1"></i> ☀️ দুপুরের মিল বন্ধ রাখা হয়েছে। আজ রাত থেকে রাতের মিল চালু থাকবে।</span>
                                    @elseif($un->request_type == 'half_day')
                                        @php
                                            $resumeDate = \Carbon\Carbon::parse($un->date)->addDay()->format('d-M-Y');
                                        @endphp
                                        <br><span class="text-success fw-bold mt-1 d-inline-block"><i class="fa fa-moon me-1"></i> 🌙 রাতের মিল বন্ধ রাখা হয়েছে। <strong>{{ $resumeDate }}</strong> তারিখ থেকে পুনরায় স্বাভাবিক মিল চালু থাকবে।</span>
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

        @if(isset($mealDepositWarning) && $mealDepositWarning)
        @php
            $isNewUserNotice = ($mealDepositAlertType ?? '') === 'new_user_notice';
            $alertClass      = $isNewUserNotice ? 'alert-info border-info' : 'alert-danger border-danger';
            $iconClass       = $isNewUserNotice ? 'text-info fa-info-circle' : 'text-danger fa-exclamation-triangle';
            $titleText       = $isNewUserNotice ? '📌 মিল সার্ভিস সংক্রান্ত তথ্য (Meal Service Information)' : 'মেল ডিপোজিট বকেয়া/জিরো অ্যালার্ট (Meal Deposit Alert)';
            $titleColor      = $isNewUserNotice ? 'text-info' : 'text-danger';
            $btnClass        = $isNewUserNotice ? 'btn-info' : 'btn-danger';
        @endphp
        <div class="col-12 mb-3">
            <div class="alert {{ $alertClass }} d-flex align-items-center justify-content-between flex-wrap gap-2 p-3 mb-0 shadow-sm rounded-3" role="alert">
                <div class="d-flex align-items-center">
                    <span class="alert-icon {{ $titleColor }} me-3 fs-3">
                        <i class="fa {{ $isNewUserNotice ? 'fa-info-circle' : 'fa-exclamation-triangle' }}"></i>
                    </span>
                    <div>
                        <h6 class="alert-heading mb-1 {{ $titleColor }} fw-bold fs-6">{{ $titleText }}</h6>
                        <div class="fw-medium text-dark fs-7">
                            {{ $mealDepositWarning }}
                        </div>
                    </div>
                </div>
                <a href="{{ route('dashboard.my-meals') }}" class="btn btn-sm {{ $btnClass }} fw-bold shadow-sm waves-effect waves-light px-3 py-2">
                    <i class="fa fa-utensils me-1"></i> মেল ডিপোজিট হিস্ট্রি / রিচার্জ
                </a>
            </div>
        </div>
        @endif

        
        <!-- My Room & Booking Status -->
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
                $parts = explode('-', $i['roomnumber'] ?? '');
                return $parts[0] ?? ($i['roomnumber'] ?? '');
            })->filter()->implode(', ');
            if (!$bRooms && isset($userBooking) && $userBooking) {
                $bRooms = $userBooking->room_number ?? $userBooking->roomnumber ?? '-';
            }

            $bSeats = collect($bookingItems)->map(function($i) {
                $parts = explode('-', $i['roomnumber'] ?? '');
                return count($parts) > 1 ? implode('-', array_slice($parts, 1)) : '-';
            })->filter()->implode(', ');
            if (!$bSeats && isset($userBooking) && $userBooking) {
                $bSeats = '-';
            }
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
                        <div class="d-flex justify-content-between small">
                            <span class="text-muted"><i class="fa fa-bed text-primary me-1"></i> Seat No:</span>
                            <span class="badge bg-label-danger font-monospace fw-bold">{{ $bSeats ?: '-' }}</span>
                        </div>
                    </div>
                    <small class="mb-0 text-primary fw-bold">
                        <i class="fa fa-tag me-1"></i> ৳ {{ number_format($userBooking->monthly_amount ?? 0) }}/month
                    </small>
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
                            <div class="my-1">
                                @if((isset($mealDepositAlertType) && $mealDepositAlertType === 'new_user_notice') || (isset($todayMealStatus) && $todayMealStatus->is_off))
                                    <span class="badge bg-danger fs-6 px-2 py-1"><i class="fa fa-ban me-1"></i> Meal OFF (বন্ধ)</span>
                                @elseif(isset($todayMealStatus) && $todayMealStatus->half_meal)
                                    <span class="badge bg-info fs-6 px-2 py-1"><i class="fa fa-sun me-1"></i> Half Meal (হাফ)</span>
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
                    <div class="mt-2">
                        <a href="{{ route('meals.create') }}" class="btn btn-sm btn-outline-info w-100 fw-bold shadow-sm waves-effect">
                            <i class="fa fa-cog me-1"></i> Change / Turn OFF Meal
                        </a>
                        <small class="d-block text-muted text-center mt-1" style="font-size: 0.72rem;">
                            ☀️ লাঞ্চ কাটঅফ: ১০:০০ AM | 🌙 ডিনার: ০৪:০০ PM
                        </small>
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


        @endunlessrole


        <!-- ADMIN & STAFF STAT CARDS -->
        <!-- Floor -->
        @role('admin')
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
        @role('admin')
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
        @role('admin|staffs')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('room-list.index') }}">
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
        @role('admin|staffs')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('room-list.index') }}">
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
        @role('admin')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ url('room-booking-history') }}">
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

        <!-- Staffs -->
        @hasanyrole('admin|staffs')
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
        @role('admin')
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

        <!-- This Month Rent Collection -->
        @role('admin|staffs')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('monthly-payments.index') }}">
                <div class="card h-100 position-relative overflow-hidden">
                    <div class="card-body pb-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">This Month Rent</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2 text-success">৳ {{ number_format($thisMonthCollection) }}</h4>
                                </div>
                                <small class="mb-0 text-muted">{{ \Carbon\Carbon::now()->format('F Y') }} Collection</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-success">
                                   <i class="fa fa-wallet"></i>
                                </span>
                            </div>
                        </div>
                        <div id="chart-rent" class="mt-2" style="min-height: 45px;"></div>
                    </div>
                </div>
            </a>
        </div>
        @endrole

        <!-- This Month Due Rent -->
        @role('admin|staffs')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('monthly-payments.index') }}">
                <div class="card h-100 position-relative overflow-hidden">
                    <div class="card-body pb-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">This Month Due</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2 text-danger">৳ {{ number_format($thisMonthDue) }}</h4>
                                </div>
                                <small class="mb-0 text-danger">Unpaid Rent Total</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-danger">
                                   <i class="fa fa-exclamation-circle"></i>
                                </span>
                            </div>
                        </div>
                        <div id="chart-due" class="mt-2" style="min-height: 45px;"></div>
                    </div>
                </div>
            </a>
        </div>
        @endrole

        <!-- Today Meal Entries -->
        @role('admin|staffs')
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
        @role('admin|staffs')
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
        @role('admin')
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
        @role('admin')
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

<style type="text/css">
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
        var parts = String(str).split('-');
        return parts[0] || str;
    }
    function getSeatNo(str) {
        if (!str) return '-';
        var parts = String(str).split('-');
        return parts.length > 1 ? parts.slice(1).join('-') : '-';
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
    var signatureLabelText = isProf ? 'বোর্ডারের স্বাক্ষর' : 'শিক্ষার্থীর স্বাক্ষর';

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
            if (data.success && data.has_notifs && data.notifications) {
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
        })
        .catch(err => console.log('Dashboard live notif poll skipped', err));
    }
    setInterval(checkDashboardUserNotifsLive, 5000);
});
</script>
@endsection