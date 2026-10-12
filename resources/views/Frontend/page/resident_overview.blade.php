@extends('Frontend.layouts.app')
@section('title', 'Resident Dashboard Overview - ' . ($userBooking->full_name ?? ($residentUser->name ?? 'শিক্ষার্থী')))

@section('content')
<section class="resident-overview-section py-4" style="background-color: #f4f7fb; min-height: 85vh; padding-top: 110px !important;">
  <div class="container" style="max-width: 1240px;">

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
          $bFloors = $userBooking->floornumber ?? ($userBooking->floor->name ?? '-');
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
          $bRooms = $userBooking->room_number ?? ($userBooking->room->room_number ?? '-');
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

      $bAdvanceTotal = (float)collect($bookingItems)->sum(function($i) {
          return (float)($i['advance_price'] ?? 0);
      });
      if ($bAdvanceTotal <= 0 && isset($userBooking) && isset($userBooking->advance_fee) && is_numeric($userBooking->advance_fee)) {
          $bAdvanceTotal = (float)$userBooking->advance_fee;
      }
      $bDevFee = isset($userBooking) ? (float)($userBooking->development_fee ?? 0) : 0;
      $bTotalInitialFee = $bAdvanceTotal + $bDevFee;
      $monthlyRent = isset($userBooking) ? (float)($userBooking->monthly_amount ?? 0) : 0;

      $residentName = $userBooking->full_name ?? ($residentUser->name ?? 'Resident');
      $rawPhone = preg_replace('/[^0-9]/', '', (string)($userBooking->phone ?? ($residentUser->phone ?? $searchPhone)));
      $residentPhone = strlen($rawPhone) >= 10 ? '0' . substr($rawPhone, -10) : $rawPhone;

      $activeCheckout = null;
      if (isset($userBooking) && $userBooking) {
          $activeCheckout = \App\Models\Backend\ResidentCheckInOut::where('room_booking_history_id', $userBooking->id)
              ->where('status', 0)
              ->latest('check_out_date')
              ->first();
      }

      $isCurrentlyOutside = false;
      $isUpcomingCheckout = false;
      if ($activeCheckout) {
          $checkoutDate = \Carbon\Carbon::parse($activeCheckout->check_out_date)->startOfDay();
          $todayDate = \Carbon\Carbon::today();
          if ($checkoutDate->lte($todayDate)) {
              $isCurrentlyOutside = true;
          } else {
              $isUpcomingCheckout = true;
          }
      }
    @endphp

    <!-- Top Resident Summary Banner -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden" style="background: #ffffff;">
      <div class="card-body p-3 p-md-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
          
          <div>
            <div class="d-flex align-items-center gap-2 mb-1.5 flex-wrap">
              <span class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle" style="width: 28px; height: 28px; font-size: 14px;">
                <i class="bi bi-person-fill"></i>
              </span>
              <h5 class="fw-bold text-dark mb-0" style="font-size: 1.15rem;">
                Resident Dashboard (শিক্ষার্থী ড্যাশবোর্ড) - <span class="text-primary">{{ $residentName }}</span>
              </h5>
              @if($isCurrentlyOutside)
                <span class="badge bg-warning text-dark border border-warning px-2.5 py-1 fw-bold rounded-pill small">
                  🟡 বর্তমানে বাইরে / ছুটিতে
                </span>
              @elseif($isUpcomingCheckout)
                <span class="badge bg-info text-dark border border-info px-2.5 py-1 fw-bold rounded-pill small">
                  📅 আসন্ন ছুটি: {{ \Carbon\Carbon::parse($activeCheckout->check_out_date)->format('d M, Y') }}
                </span>
              @else
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 fw-bold rounded-pill small">
                  🟢 {{ $userBooking->today_check_out ? 'Checked Out' : 'Active Resident' }}
                </span>
              @endif
            </div>

            <div class="text-muted small ps-md-4">
              <div class="mb-1">
                📌 <strong>নির্দেশাবলী:</strong> কনফার্ম বুকিং ডকুমেন্টটি প্রিন্ট করে এডমিন এর কাছে জমা দিয়ে আপনার বুকিং রসিদ বুঝে নিন।
              </div>
              <div>
                📞 <strong>যেকোনো তথ্যের জন্য এই নম্বরে যোগাযোগ করুন:</strong> 
                <a href="tel:{{ $webSetting['phone'] ?? '01977270920' }}" class="text-decoration-none fw-bold text-primary">
                  {{ $webSetting['phone'] ?? '01977270920' }}
                </a>
              </div>
            </div>
          </div>

          <div class="text-md-end flex-shrink-0 d-flex flex-wrap gap-2">
            @if($isCurrentlyOutside)
              <button type="button" class="btn btn-success fw-bold px-3.5 py-2 rounded-3 shadow-sm d-inline-flex align-items-center gap-2" onclick="handleResidentCheckIn()">
                <i class="bi bi-door-open-fill"></i>
                <span>হোস্টেলে ফিরে এসেছি (Check In)</span>
              </button>
            @elseif($isUpcomingCheckout)
              {{-- আসন্ন চেক-আউট এন্ট্রি থাকায় চেক-আউট বাটন দেখানো হবে না --}}
            @else
              <button type="button" class="btn btn-warning text-dark fw-bold px-3.5 py-2 rounded-3 shadow-sm d-inline-flex align-items-center gap-2" onclick="triggerResidentLogin('{{ $residentPhone }}', 'বাড়ি যাওয়া / Check Out', '{{ route('check-in-out.index') }}')">
                <i class="bi bi-door-closed-fill"></i>
                <span>বাড়ি যাওয়া / Check Out</span>
              </button>
            @endif
            <a href="{{ route('resident.notice-board') }}" class="btn btn-outline-danger fw-bold px-3 py-2 rounded-3 shadow-sm d-inline-flex align-items-center gap-2 bg-white">
              <i class="bi bi-bell-fill text-danger"></i>
              <span>জরুরি বিজ্ঞপ্তি</span>
            </a>
            <button type="button" class="btn btn-primary fw-bold px-3.5 py-2 rounded-3 shadow-sm d-inline-flex align-items-center gap-2" onclick="triggerResidentLogin('{{ $residentPhone }}', 'Confirm Booking Document')" style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); border: none;">
              <i class="bi bi-printer-fill"></i>
              <span>Confirm Booking Document</span>
            </button>
          </div>

        </div>
      </div>
    </div>

    <!-- Active Check-Out Alert Banner for Resident -->
    @if($isCurrentlyOutside)
      <div class="alert alert-warning border-warning shadow-sm rounded-4 p-3.5 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3" style="background: #fffbeb; border: 2px solid #f59e0b;">
        <div class="d-flex align-items-center gap-3">
          <div class="rounded-circle bg-warning text-dark p-2.5 d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; min-width: 48px;">
            <i class="bi bi-door-closed-fill fs-3"></i>
          </div>
          <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
              <span class="badge bg-warning text-dark fw-bold px-2.5 py-1 rounded-pill">🟡 বর্তমানে বাইরে / ছুটিতে আছেন</span>
              <span class="text-muted small">চেক-আউট: <strong>{{ $activeCheckout->check_out_date ? $activeCheckout->check_out_date->format('d M, Y') : '' }}</strong></span>
              @if($activeCheckout->expected_return_date)
                <span class="text-primary small fw-semibold">| সম্ভাব্য ফেরার তারিখ: <strong>{{ \Carbon\Carbon::parse($activeCheckout->expected_return_date)->format('d M, Y') }}</strong></span>
              @endif
            </div>
            <div class="fw-medium text-dark small">
              কারণ: <strong>{{ $activeCheckout->leave_reason ?: 'বাড়ি যাওয়া' }}</strong>
              @if($activeCheckout->emergency_contact)
                | জরুরি যোগাযোগ: <strong>{{ $activeCheckout->emergency_contact }}</strong>
              @endif
            </div>
            <small class="d-block text-danger fw-semibold mt-1">
              <i class="bi bi-egg-fried me-1"></i> চেক-আউট থাকায় আপনার মিল (Meal) স্বয়ংক্রিয়ভাবে বন্ধ আছে। হোস্টেলে ফিরে চেক-ইন করলে পরদিন থেকে মিল পুনরায় স্বয়ংক্রিয়ভাবে চালু হবে।
            </small>
          </div>
        </div>
        <button type="button" class="btn btn-success fw-bold px-4 py-2 rounded-3 shadow-sm d-inline-flex align-items-center gap-2" onclick="handleResidentCheckIn()">
          <i class="bi bi-door-open-fill"></i>
          <span>হোস্টেলে ফিরে এসেছি (Check In Now)</span>
        </button>
      </div>
    @elseif($isUpcomingCheckout)
      <div class="alert alert-info border-info shadow-sm rounded-4 p-3.5 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3" style="background: #eff6ff; border: 2px solid #3b82f6;">
        <div class="d-flex align-items-center gap-3">
          <div class="rounded-circle bg-primary text-white p-2.5 d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; min-width: 48px;">
            <i class="bi bi-calendar-event-fill fs-3"></i>
          </div>
          <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
              <span class="badge bg-primary text-white fw-bold px-2.5 py-1 rounded-pill">📅 আসন্ন ছুটি / চেক-আউট এন্ট্রি</span>
              <span class="text-dark small">চেক-আউট তারিখ: <strong>{{ $activeCheckout->check_out_date ? $activeCheckout->check_out_date->format('d M, Y') : '' }}</strong></span>
              @if($activeCheckout->expected_return_date)
                <span class="text-primary small fw-semibold">| সম্ভাব্য ফেরার তারিখ: <strong>{{ \Carbon\Carbon::parse($activeCheckout->expected_return_date)->format('d M, Y') }}</strong></span>
              @endif
            </div>
            <div class="text-muted small">
              <span class="text-success fw-bold">🟢 আপনি বর্তমানে হোস্টেলে উপস্থিত আছেন।</span> আপনার ছুটি নির্ধারিত তারিখ ({{ $activeCheckout->check_out_date->format('d M, Y') }}) থেকে কার্যকর হবে।
            </div>
            <small class="d-block text-primary fw-semibold mt-1">
              <i class="bi bi-egg-fried me-1"></i> চেক-আউট তারিখ থেকে মিল স্বয়ংক্রিয়ভাবে বন্ধ থাকবে এবং হোস্টেলে ফিরে চেক-ইন করলে আবার চালু হবে।
            </small>
          </div>
        </div>
      </div>
    @endif


    <!-- Upcoming Special Meal Notice Banner for Active Residents -->
    @if(isset($upcomingSpecialMeals) && $upcomingSpecialMeals->count() > 0)
      @foreach($upcomingSpecialMeals as $sm)
        <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden text-white" style="background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%);">
          <div class="card-body p-3 p-md-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
              <div class="d-flex align-items-center">
                <div class="rounded-circle bg-white text-primary p-3 me-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 52px; height: 52px; min-width: 52px;">
                  <i class="bi bi-star-fill fs-4 text-warning"></i>
                </div>
                <div>
                  <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                    <span class="badge bg-warning text-dark fw-bold px-2.5 py-1 rounded-pill" style="font-size: 11px;">
                      ✨ স্পেশাল মিল আয়োজন
                    </span>
                    <span class="badge bg-white text-dark fw-bold px-2.5 py-1 rounded-pill font-monospace" style="font-size: 11px;">
                      📅 {{ \Carbon\Carbon::parse($sm->date)->format('d M Y (l)') }}
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
      @endforeach
    @endif

    <!-- User Notifications (Meal Request Updates, if any) -->
    @if(isset($userNotifications) && count($userNotifications) > 0)
      @foreach($userNotifications as $un)
        <div class="alert alert-warning border-warning shadow-sm rounded-3 p-3 mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: #fffbeb; border: 1.5px solid #f59e0b;">
          <div class="d-flex align-items-center">
            <span class="alert-icon text-white me-3 d-inline-flex align-items-center justify-content-center rounded-3" style="width: 38px; height: 38px; background: #f59e0b; flex-shrink: 0;">
              <i class="bi bi-bell-fill fs-5"></i>
            </span>
            <div>
              <h6 class="alert-heading mb-1 text-dark fw-bold fs-6">📢 মিল রিকোয়েস্ট আপডেট</h6>
              <div class="fw-medium text-dark small">
                আপনার <strong>{{ \Carbon\Carbon::parse($un->meal_date)->format('d M, Y') }}</strong> তারিখের মিল বন্ধের রিকোয়েস্ট এডমিন কর্তৃক 
                @if($un->action == 'approved')
                  <span class="badge bg-success">অনুমোদিত (Approved)</span> হয়েছে।
                @elseif($un->action == 'rejected')
                  <span class="badge bg-danger">বাতিল (Rejected)</span> করা হয়েছে।
                @endif
                @if($un->admin_note)
                  <br><small class="text-muted">নোট: {{ $un->admin_note }}</small>
                @endif
              </div>
            </div>
          </div>
        </div>
      @endforeach
    @endif

    <!-- Admin Meal Off Notice Banner -->
    @if(isset($isAdminTurnedOff) && $isAdminTurnedOff && !$isCurrentlyOutside)
      <div class="alert alert-danger border-danger d-flex align-items-center justify-content-between flex-wrap gap-2 p-3 mb-3 shadow-sm" style="background: #fff5f5; border: 1.5px solid #ef4444; border-radius: 12px;" role="alert">
        <div class="d-flex align-items-center">
          <span class="alert-icon text-white me-3 d-inline-flex align-items-center justify-content-center rounded-3" style="width: 40px; height: 40px; background: #dc2626; flex-shrink: 0;">
            <i class="bi bi-slash-circle-fill fs-5"></i>
          </span>
          <div>
            <h6 class="alert-heading mb-1 text-danger fw-bold fs-6">🚫 কর্তৃপক্ষ কর্তৃক আপনার মিল বন্ধ রাখা হয়েছে</h6>
            <div class="fw-medium text-dark small">
              কর্তৃপক্ষ/এডমিন আপনার মিল বন্ধ করে দিয়েছেন। মিল পুনরায় চালু করার জন্য অনুগ্রহ করে মেস কর্তৃপক্ষের সাথে যোগাযোগ করুন।
            </div>
          </div>
        </div>
        <a href="tel:{{ $webSetting['phone'] ?? '01977270920' }}" class="btn btn-danger fw-bold shadow-sm px-3 py-2 text-white rounded-3">
          <i class="bi bi-telephone-fill me-1"></i> মিল চালুর জন্য কল করুন ({{ $webSetting['phone'] ?? '01977270920' }})
        </a>
      </div>
    @endif

    <!-- Meal Service Information & Deposit Warning Banner (Matching User Dashboard) -->
    @if(isset($mealDepositWarning) && $mealDepositWarning && (!isset($isAdminTurnedOff) || !$isAdminTurnedOff))
      <div class="alert alert-info border-info d-flex align-items-center justify-content-between flex-wrap gap-2 p-3 mb-3 shadow-sm" style="background: #e0f7fa; border: 1.5px solid #00bcd4; border-radius: 12px;" role="alert">
        <div class="d-flex align-items-center">
          <span class="alert-icon text-white me-3 d-inline-flex align-items-center justify-content-center rounded-3" style="width: 40px; height: 40px; background: #00bcd4; flex-shrink: 0;">
            <i class="bi bi-info-circle-fill fs-5"></i>
          </span>
          <div>
            <h6 class="alert-heading mb-1 fw-bold fs-6" style="color: #00838f !important;">📌 মিল সার্ভিস সংক্রান্ত তথ্য (Meal Service Information)</h6>
            <div class="fw-medium text-dark small" id="mealNoticeMessage">
              {{ $mealDepositWarning }}
            </div>
          </div>
        </div>
        <a href="tel:{{ $webSetting['phone'] ?? '01977270920' }}" class="btn fw-bold shadow-sm px-3.5 py-2 text-white rounded-3" style="background: #00acc1; border: none;">
          <i class="bi bi-telephone-fill me-1"></i> এডমিন এর সাথে যোগাযোগ করুন
        </a>
      </div>
    @endif

    <!-- Logged in vs Public Preview Notice Banner -->
    @auth
      <div class="alert alert-success border-0 rounded-3 shadow-sm py-2.5 px-3 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background-color: #ecfdf5; color: #065f46;">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-check-circle-fill text-success fs-5"></i>
          <span class="small fw-semibold">
            স্বাগতম <strong>{{ auth()->user()->name ?? ($userBooking->full_name ?? 'শিক্ষার্থী') }}</strong>! আপনি লগইন অবস্থায় আছেন। যেকোনো কার্ডে ক্লিক করে সরাসরি বিস্তারিত দেখতে পারেন।
          </span>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-success fw-bold px-3 rounded-pill">
          <i class="bi bi-speedometer2 me-1"></i> ড্যাশবোর্ডে প্রবেশ করুন
        </a>
      </div>
    @else
      <div class="alert alert-info border-0 rounded-3 shadow-sm py-2.5 px-3 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background-color: #e8f3ff; color: #033364;">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-info-circle-fill text-primary fs-5"></i>
          <span class="small fw-semibold">
            আপনি পাবলিক প্রিভিউ মোডে আছেন। বিস্তারিত স্টেটমেন্ট দেখতে বা মিল/পেমেন্ট পরিচালনা করতে কার্ডে ক্লিক করে <strong>পাসওয়ার্ড দিয়ে লগইন করুন</strong>।
          </span>
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary fw-bold px-3 rounded-pill" onclick="triggerResidentLogin('{{ $residentPhone }}', 'Direct Login', '{{ route('dashboard') }}')">
          <i class="bi bi-box-arrow-in-right me-1"></i> লগইন করুন
        </button>
      </div>
    @endauth

    <!-- 5 Main Resident Dashboard Cards -->
    <div class="row g-3 g-md-4">
      
      <!-- CARD 1: My Booking Status -->
      <div class="col-12 col-sm-6 col-lg-3">
        @auth
          <a href="{{ route('dashboard') }}" class="text-decoration-none text-dark d-block h-100">
        @endauth
        <div class="card h-100 border-0 shadow-sm rounded-4 resident-card-hover" @guest onclick="triggerResidentLogin('{{ $residentPhone }}', 'My Booking Status', '{{ route('dashboard') }}')" @endguest style="background: #ffffff; cursor: pointer; transition: all 0.25s ease; border-top: 4px solid #4f46e5 !important;">
          <div class="card-body p-3.5">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div>
                <p class="text-muted small fw-bold mb-1" style="font-size: 0.8rem;">My Booking Status</p>
                <h5 class="fw-bold text-primary mb-0" style="color: #4f46e5 !important;">Active Resident</h5>
              </div>
              <div class="p-2 rounded-3 bg-indigo-subtle text-indigo" style="background: #eef2ff; color: #4f46e5;">
                <i class="bi bi-person-badge-fill fs-5"></i>
              </div>
            </div>

            <div class="booking-mini-details p-2.5 rounded-3 bg-light border mt-3" style="font-size: 0.82rem;">
              <div class="d-flex justify-content-between py-1 border-bottom">
                <span class="text-muted"><i class="bi bi-building me-1"></i> Floor:</span>
                <strong class="text-dark">{{ $bFloors }}</strong>
              </div>
              <div class="d-flex justify-content-between py-1 border-bottom">
                <span class="text-muted"><i class="bi bi-door-open me-1"></i> Room/Section:</span>
                <strong class="text-primary">{{ $bRooms }}</strong>
              </div>
              <div class="d-flex justify-content-between py-1 border-bottom">
                <span class="text-muted"><i class="bi bi-layout-split me-1"></i> Seat No:</span>
                <span class="badge bg-danger font-monospace px-1.5 py-0.5">{{ $bSeats }}</span>
              </div>
              <div class="d-flex justify-content-between py-1 border-bottom">
                <span class="text-muted">🛡️ Advance Deposit:</span>
                <strong class="text-dark">৳ {{ number_format($bAdvanceTotal, 0) }}</strong>
              </div>
              <div class="d-flex justify-content-between py-1 border-bottom">
                <span class="text-muted">⚙️ Development Fee:</span>
                <strong class="text-warning">৳ {{ number_format($bDevFee, 0) }}</strong>
              </div>
              <div class="d-flex justify-content-between py-1 border-bottom">
                <span class="text-muted">📑 Total Booking Fee:</span>
                <strong class="text-primary">৳ {{ number_format($bTotalInitialFee, 0) }}</strong>
              </div>
              <div class="d-flex justify-content-between pt-1.5 text-success fw-bold">
                <span>🏷️ মাসিক ভাড়া:</span>
                <span>৳ {{ number_format($monthlyRent, 0) }}/month</span>
              </div>
            </div>

          </div>
        </div>
        @auth
          </a>
        @endauth
      </div>

      <!-- CARD 2: My Payments -->
      <div class="col-12 col-sm-6 col-lg-3">
        @auth
          <a href="{{ route('dashboard.my-payments') }}" class="text-decoration-none text-dark d-block h-100">
        @endauth
        <div class="card h-100 border-0 shadow-sm rounded-4 resident-card-hover" @guest onclick="triggerResidentLogin('{{ $residentPhone }}', 'My Payments', '{{ route('dashboard.my-payments') }}')" @endguest style="background: #ffffff; cursor: pointer; transition: all 0.25s ease; border-top: 4px solid #10b981 !important;">
          <div class="card-body p-3.5 d-flex flex-column justify-content-between">
            <div>
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                  <p class="text-muted small fw-bold mb-1" style="font-size: 0.8rem;">My Payments</p>
                  <h4 class="fw-bold text-success mb-0">৳ {{ number_format($myTotalPaid, 0) }}</h4>
                </div>
                <div class="p-2 rounded-3 bg-success-subtle text-success" style="background: #ecfdf5; color: #10b981;">
                  <i class="bi bi-wallet2 fs-5"></i>
                </div>
              </div>
              <p class="text-muted small mb-0 mt-2">Rent Payment History</p>
            </div>

            <div class="pt-4">
              <div class="p-2.5 rounded-3 bg-light border text-center">
                <span class="text-success fw-semibold small d-inline-flex align-items-center gap-1">
                  <i class="bi bi-eye"></i> বিস্তারিত রসিদ দেখতে ক্লিক করুন
                </span>
              </div>
            </div>

          </div>
        </div>
        @auth
          </a>
        @endauth
      </div>

      <!-- CARD: Electricity Bills -->
      <div class="col-12 col-sm-6 col-lg-3">
        @auth
          <a href="{{ route('dashboard.my-electricity-bills') }}" class="text-decoration-none text-dark d-block h-100">
        @endauth
        <div class="card h-100 border-0 shadow-sm rounded-4 resident-card-hover" @guest onclick="triggerResidentLogin('{{ $residentPhone }}', 'Electricity Bills', '{{ route('dashboard.my-electricity-bills') }}')" @endguest style="background: #ffffff; cursor: pointer; transition: all 0.25s ease; border-top: 4px solid #f59e0b !important;">
          <div class="card-body p-3.5 d-flex flex-column justify-content-between">
            <div>
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                  <p class="text-muted small fw-bold mb-1" style="font-size: 0.8rem;">Electricity Bills (বিদ্যুৎ বিল)</p>
                  <h4 class="fw-bold mb-0 text-warning">⚡ ৳ {{ number_format($myTotalElectBill, 0) }}</h4>
                </div>
                <div class="p-2 rounded-3" style="background: #fffbeb; color: #d97706;">
                  <i class="bi bi-lightning-charge-fill fs-5"></i>
                </div>
              </div>
              <p class="text-muted small mb-0 mt-2">বর্তমান মাস: <strong class="text-dark">৳ {{ number_format($myCurrentMonthElectBill, 0) }}</strong></p>
            </div>

            <div class="pt-4">
              <div class="p-2.5 rounded-3 bg-light border text-center">
                <span class="fw-semibold small d-inline-flex align-items-center gap-1" style="color: #d97706 !important;">
                  <i class="bi bi-receipt"></i> মাসের বিল দেখতে ক্লিক করুন
                </span>
              </div>
            </div>

          </div>
        </div>
        @auth
          </a>
        @endauth
      </div>

      <!-- CARD 3: Meal Deposit Balance -->
      <div class="col-12 col-sm-6 col-lg-3">
        @auth
          <a href="{{ route('dashboard.my-meals') }}" class="text-decoration-none text-dark d-block h-100">
        @endauth
        <div class="card h-100 border-0 shadow-sm rounded-4 resident-card-hover" @guest onclick="triggerResidentLogin('{{ $residentPhone }}', 'Meal Deposit Balance', '{{ route('dashboard.my-meals') }}')" @endguest style="background: #ffffff; cursor: pointer; transition: all 0.25s ease; border-top: 4px solid #f97316 !important;">
          <div class="card-body p-3.5 d-flex flex-column justify-content-between">
            <div>
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                  <p class="text-muted small fw-bold mb-1" style="font-size: 0.8rem;">Meal Deposit Balance</p>
                  <h4 class="fw-bold mb-0" style="color: #ea580c;" id="overviewDepositBalance">৳ {{ number_format($mealDepositBalance, 2) }}</h4>
                </div>
                <div class="p-2 rounded-3" style="background: #fff7ed; color: #ea580c;">
                  <i class="bi bi-cup-hot-fill fs-5"></i>
                </div>
              </div>
              <p class="text-muted small mb-0 mt-2">Total Meals: <strong id="overviewMealCount">{{ $myMealCount }}</strong></p>
            </div>

            <div class="pt-3">
              <button type="button" class="btn btn-sm w-100 py-2 rounded-3 fw-bold shadow-none" style="background: #ffedd5; color: #c2410c; border: 1px solid #fed7aa;">
                <i class="bi bi-folder2-open me-1"></i> View Deposit & History
              </button>
            </div>

          </div>
        </div>
        @auth
          </a>
        @endauth
      </div>

      <!-- CARD 4: Today's Meal -->
      <div class="col-12 col-sm-6 col-lg-3">
        @auth
          <a href="{{ route('dashboard.my-meals') }}" class="text-decoration-none text-dark d-block h-100">
        @endauth
        <div class="card h-100 border-0 shadow-sm rounded-4 resident-card-hover" @guest onclick="triggerResidentLogin('{{ $residentPhone }}', 'Today\'s Meal', '{{ route('dashboard.my-meals') }}')" @endguest style="background: #ffffff; cursor: pointer; transition: all 0.25s ease; border-top: 4px solid #06b6d4 !important;">
          <div class="card-body p-3.5 d-flex flex-column justify-content-between">
            <div>
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                  <p class="text-muted small fw-bold mb-1" style="font-size: 0.8rem;">Today's Meal (আজকের মিল)</p>
                  @php
                    $isHalfMeal = $todayMeal && (bool)$todayMeal->half_meal;
                    $mealNote = strtolower($todayMeal ? ($todayMeal->note ?? '') : '');
                    $isHalfNight = $isHalfMeal && ($mealNote === 'night' || $mealNote === 'half_night');
                  @endphp
                  <div id="overviewTodayMealBadgeWrap">
                    @if($todayMeal && $todayMeal->is_off)
                      <span class="badge bg-danger px-2.5 py-1 fw-bold">🛑 মিল বন্ধ (OFF)</span>
                    @elseif($isHalfMeal)
                      @if($isHalfNight)
                        <span class="badge bg-info text-white px-2.5 py-1 fw-bold">🌙 হাফ মিল (Night)</span>
                      @else
                        <span class="badge bg-warning text-dark px-2.5 py-1 fw-bold">☀️ হাফ মিল (Day)</span>
                      @endif
                    @elseif($isZeroDeposit)
                      <span class="badge bg-danger px-2.5 py-1 fw-bold">🚫 ডিপোজিট নেই</span>
                    @else
                      <span class="badge bg-success px-2.5 py-1 fw-bold">✅ Full Meal (অটো চালু)</span>
                    @endif
                  </div>
                </div>
                <div class="p-2 rounded-3" style="background: #ecfeff; color: #0891b2;">
                  <i class="bi bi-calendar-check-fill fs-5"></i>
                </div>
              </div>
              <p class="text-muted small mb-0 mt-2">Auto generated daily</p>
            </div>

            <div class="pt-3">
              <button type="button" class="btn btn-sm w-100 py-2 rounded-3 fw-bold mb-2 shadow-none" style="background: #cffafe; color: #0e7490; border: 1px solid #a5f3fc;">
                <i class="bi bi-gear-fill me-1"></i> Change / Turn OFF Meal
              </button>
              <div class="text-muted" style="font-size: 0.72rem;">
                <span>☀️ লাঞ্চ কাটঅফ: ১০:০০ AM</span> | <span>🌙 ডিনার: ০৪:০০ PM</span>
              </div>
            </div>

          </div>
        </div>
        @auth
          </a>
        @endauth
      </div>

      <!-- CARD 5: My Complaints -->
      <div class="col-12 col-sm-6 col-lg-3">
        @auth
          <a href="{{ route('dashboard') }}" class="text-decoration-none text-dark d-block h-100">
        @endauth
        <div class="card h-100 border-0 shadow-sm rounded-4 resident-card-hover" @guest onclick="triggerResidentLogin('{{ $residentPhone }}', 'My Complaints', '{{ route('dashboard') }}')" @endguest style="background: #ffffff; cursor: pointer; transition: all 0.25s ease; border-top: 4px solid #ef4444 !important;">
          <div class="card-body p-3.5 d-flex flex-column justify-content-between">
            <div>
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                  <p class="text-muted small fw-bold mb-1" style="font-size: 0.8rem;">My Complaints</p>
                  <h4 class="fw-bold text-danger mb-0">{{ $complaintsCount }}</h4>
                </div>
                <div class="p-2 rounded-3 bg-danger-subtle text-danger" style="background: #fef2f2; color: #ef4444;">
                  <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                </div>
              </div>
              <p class="text-muted small mb-0 mt-2">Submit & View Issues</p>
            </div>

            <div class="pt-3">
              <div class="p-2 rounded-3 bg-light border text-center">
                <span class="text-danger fw-semibold small d-inline-flex align-items-center gap-1">
                  <i class="bi bi-chat-left-dots"></i> অভিযোগ জানাতে ক্লিক করুন
                </span>
              </div>
            </div>

          </div>
        </div>
        @auth
          </a>
        @endauth
      </div>

      <!-- CARD 6: Notice Board (জরুরি বিজ্ঞপ্তি) -->
      <div class="col-12 col-sm-6 col-lg-3">
        @auth
          <a href="{{ route('dashboard.resident-notices') }}" class="text-decoration-none text-dark d-block h-100">
        @else
          <a href="javascript:void(0)" onclick="triggerResidentLogin('{{ $residentPhone }}', 'Notice Board', '{{ route('dashboard.resident-notices') }}')" class="text-decoration-none text-dark d-block h-100">
        @endauth
          <div class="card h-100 border-0 shadow-sm rounded-4 resident-card-hover" style="background: #ffffff; cursor: pointer; transition: all 0.25s ease; border-top: 4px solid #6366f1 !important;">
            <div class="card-body p-3.5 d-flex flex-column justify-content-between">
              <div>
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <div>
                    <p class="text-muted small fw-bold mb-1" style="font-size: 0.8rem;">Notice Board (বিজ্ঞপ্তি)</p>
                    <h4 class="fw-bold mb-0" style="color: #4f46e5;">{{ $noticesCount ?? 0 }} টি</h4>
                  </div>
                  <div class="p-2 rounded-3" style="background: #eef2ff; color: #4f46e5;">
                    <i class="bi bi-bell-fill fs-5"></i>
                  </div>
                </div>
                <p class="text-muted small mb-0 mt-2">
                  @if(isset($latestNotice) && $latestNotice)
                    সর্বশেষ: <strong class="text-dark">{{ \Illuminate\Support\Str::limit($latestNotice->title, 26) }}</strong>
                  @else
                    জরুরি বিজ্ঞপ্তি ও হিস্ট্রি
                  @endif
                </p>
              </div>

              <div class="pt-3">
                <span class="btn btn-sm w-100 py-2 rounded-3 fw-bold shadow-none d-block text-center" style="background: #e0e7ff; color: #3730a3; border: 1px solid #c7d2fe;">
                  <i class="bi bi-folder2-open me-1"></i> সকল বিজ্ঞপ্তি দেখুন
                </span>
              </div>

            </div>
          </div>
        </a>
      </div>

    </div>

  </div>
</section>

<!-- Student Check-Out Modal (বাড়ি যাওয়া / চেক-আউট এন্ট্রি) -->
<div class="modal fade" id="studentCheckoutModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
      <div class="modal-header py-3 px-4 text-white" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 16px 16px 0 0;">
        <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2 mb-0">
          <i class="bi bi-door-closed-fill fs-4"></i>
          <span>বাড়ি যাওয়া / চেক-আউট এন্ট্রি (Leave & Check-Out)</span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="studentCheckoutForm">
        @csrf
        <input type="hidden" name="phone" value="{{ $residentPhone }}">
        <div class="modal-body p-4">
          <div class="alert alert-warning border-0 rounded-3 p-3 mb-3 d-flex align-items-start gap-2" style="background: #fffbeb;">
            <i class="bi bi-info-circle-fill fs-4 text-warning mt-1"></i>
            <div class="small text-dark">
              <div>শিক্ষার্থী: <strong>{{ $residentName }}</strong> ({{ $residentPhone }})</div>
              <div>হোস্টেল ছাড়ার আগে যাওয়ার সময় ও সম্ভাব্য ফেরার তারিখ এন্ট্রি করুন।</div>
              <div class="mt-1.5 text-danger fw-semibold">
                <i class="bi bi-egg-fried me-1"></i> <strong>মিল সংক্রান্ত নিয়ম:</strong> চেক-আউট তারিখ থেকে মিল (Meal) স্বয়ংক্রিয়ভাবে বন্ধ থাকবে। আবার যেদিন হোস্টেলে ফিরে এসে চেক-ইন করবেন, সেদিন থেকে আপনার মিল পুনরায় চালু হয়ে যাবে।
              </div>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-bold text-dark small">চেক-আউট তারিখ (Check-out Date) <span class="text-danger">*</span></label>
              <input type="date" name="check_out_date" id="std_check_out_date" value="{{ date('Y-m-d') }}" class="form-control rounded-3" required>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-bold text-dark small">সম্ভাব্য ফেরার তারিখ (Expected Return Date)</label>
              <input type="date" name="expected_return_date" id="std_expected_return_date" class="form-control rounded-3">
            </div>

            <div class="col-md-6">
              <label class="form-label fw-bold text-dark small">বাড়ি যাওয়ার কারণ / গন্তব্য <span class="text-danger">*</span></label>
              <input type="text" name="leave_reason" id="std_leave_reason" placeholder="যেমন: বাড়ি যাচ্ছি, পরীক্ষার ছুটি, চিকিৎসা..." class="form-control rounded-3" value="বাড়ি যাচ্ছি" required>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-bold text-dark small">জরুরি যোগাযোগের নম্বর (অভিভাবক) <span class="text-danger">*</span></label>
              <input type="tel" name="emergency_contact" id="std_emergency_contact" value="{{ $userBooking->guardian_number ?? ($userBooking->parents_phone_number ?? '') }}" placeholder="017xxxxxxxx" class="form-control rounded-3" required minlength="10" maxlength="20">
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light py-2.5 px-4 d-flex justify-content-between align-items-center">
          <button type="button" class="btn btn-secondary px-4 rounded-3" data-bs-dismiss="modal">বাতিল</button>
          <button type="submit" class="btn btn-warning fw-bold px-4 text-dark rounded-3 shadow-sm" id="btnSubmitStdCheckout">
            <i class="bi bi-check-circle-fill me-1"></i> চেক-আউট নিশ্চিত করুন
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
  .resident-card-hover:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important;
  }
</style>

@push('scripts')
<script>
  // If user reloads/refreshes the resident overview page, redirect to home page immediately
  (function() {
    let isReload = false;
    if (window.performance) {
      if (performance.navigation && performance.navigation.type === 1) {
        isReload = true;
      } else {
        const navEntries = performance.getEntriesByType('navigation');
        if (navEntries.length > 0 && navEntries[0].type === 'reload') {
          isReload = true;
        }
      }
    }
    if (isReload) {
      window.location.replace("{{ route('home') }}");
    }
  })();

  document.addEventListener('DOMContentLoaded', () => {
    // Clear header search input so it remains clean
    const searchInput = document.getElementById('nsmHeaderPhoneSearch');
    if (searchInput) {
      searchInput.value = '';
    }

    // Submit Student Checkout Form
    var stdCheckoutForm = document.getElementById('studentCheckoutForm');
    if (stdCheckoutForm) {
      stdCheckoutForm.addEventListener('submit', function (e) {
        e.preventDefault();
        var btn = document.getElementById('btnSubmitStdCheckout');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> সাবমিট হচ্ছে...';

        var formData = new FormData(this);

        fetch("{{ route('resident.check-out') }}", {
          method: "POST",
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
          },
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          btn.disabled = false;
          btn.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> চেক-আউট নিশ্চিত করুন';
          if (data.success) {
            Swal.fire({
              title: 'শুভ যাত্রা!',
              text: data.message,
              icon: 'success',
              timer: 2500,
              showConfirmButton: false
            }).then(() => {
              window.location.reload();
            });
          } else {
            Swal.fire('ত্রুটি!', data.message || 'চেক-আউট সম্পন্ন করা যায়নি।', 'error');
          }
        })
        .catch(err => {
          btn.disabled = false;
          btn.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> চেক-আউট নিশ্চিত করুন';
          Swal.fire('ত্রুটি!', 'সার্ভারে সংযোগে সমস্যা হয়েছে।', 'error');
        });
      });
    }
  });

  function openStudentCheckoutModal() {
    var modalEl = document.getElementById('studentCheckoutModal');
    if (modalEl) {
      var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
      modal.show();
    }
  }

  function handleResidentCheckIn() {
    Swal.fire({
      title: 'Check In Confirmation (হোস্টেলে ফিরে এসেছেন?)',
      text: 'আপনি কি নিশ্চিত যে আপনি হোস্টেলে ফিরে এসেছেন এবং Check-In করতে চান? চেক-ইন সম্পন্ন হলে পরদিন থেকে আপনার মিল পুনরায় স্বয়ংক্রিয়ভাবে চালু হবে।',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#10b981',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'হ্যাঁ, আমি ফিরে এসেছি',
      cancelButtonText: 'না'
    }).then((result) => {
      if (result.isConfirmed) {
        var formData = new FormData();
        formData.append('phone', '{{ $residentPhone }}');

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
              title: 'স্বাগতম!',
              text: data.message,
              icon: 'success',
              timer: 2000,
              showConfirmButton: false
            }).then(() => {
              window.location.reload();
            });
          } else {
            Swal.fire('ত্রুটি!', data.message || 'চেক-ইন সম্পন্ন করা যায়নি।', 'error');
          }
        })
        .catch(() => {
          Swal.fire('ত্রুটি!', 'সার্ভারে সংযোগে সমস্যা হয়েছে।', 'error');
        });
      }
    });
  }

  function triggerResidentLogin(phone, actionName, redirectTo) {
    const modalEl = document.getElementById('loginModal');
    if (!modalEl) return;

    // Open Modal
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();

    // Pre-fill Phone number in login form
    const loginInput = document.getElementById('login') || document.getElementById('stdLogin');
    if (loginInput) {
      loginInput.value = phone;
    }

    // Set redirect destination url
    const redirectInput = document.getElementById('loginRedirectTo');
    if (redirectInput) {
      redirectInput.value = redirectTo || '';
    }

    // Set info message
    if (typeof showLoginInfoNotice === 'function') {
      showLoginInfoNotice(actionName ? `"${actionName}" এর জন্য পাসওয়ার্ড দিয়ে লগইন করুন।` : 'লগইন করুন।');
    }

    // Focus password field
    setTimeout(() => {
      const pwdInput = document.getElementById('password') || document.getElementById('stdPassword');
      if (pwdInput) pwdInput.focus();
    }, 400);
  }

  // Real-time live background polling for overview page (Every 3 seconds & on tab focus)
  function syncResidentOverviewLive() {
    fetch(window.location.href, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    .then(res => res.json())
    .then(data => {
      if (data && data.success) {
        const badgeWrap = document.getElementById('overviewTodayMealBadgeWrap');
        if (badgeWrap && data.today_meal_badge_text) {
          badgeWrap.innerHTML = `<span class="badge ${data.today_meal_badge_class} px-2.5 py-1 fw-bold">${data.today_meal_badge_text}</span>`;
        }
        if (data.deposit_balance !== undefined) {
          const depEl = document.getElementById('overviewDepositBalance');
          if (depEl) depEl.innerText = '৳ ' + data.deposit_balance;
        }
        if (data.meal_count !== undefined) {
          const countEl = document.getElementById('overviewMealCount');
          if (countEl) countEl.innerText = data.meal_count;
        }
      }
    })
    .catch(() => {});
  }

  setInterval(syncResidentOverviewLive, 3000);
  window.addEventListener('focus', syncResidentOverviewLive);
  document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'visible') {
      syncResidentOverviewLive();
    }
  });
</script>
@endpush
@endsection

