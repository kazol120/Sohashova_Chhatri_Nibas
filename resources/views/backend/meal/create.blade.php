@extends('backend.layouts.app')

@section("title")
    | {{$page_title}}
@endsection

@push('style')
<style>
    .form-check-input:checked {
        background-color: #696cff !important;
        border-color: #696cff !important;
    }
    .form-check-input {
        width: 1.25rem;
        height: 1.25rem;
        cursor: pointer;
    }
    .meal-time-radio {
        width: 1.1rem;
        height: 1.1rem;
        cursor: pointer;
    }
    .half-options-card {
        transition: all 0.2s ease-in-out;
        border-color: #e0e0e0 !important;
        background-color: #ffffff;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
    }
    .select-none {
        user-select: none;
    }
    .table thead th {
        letter-spacing: 0.5px;
        font-size: 0.8rem;
    }
</style>
@endpush

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header border-bottom bg-light py-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center">
                                <h5 class="card-title mb-0 fw-bold text-dark me-3">
                                    <i class="ti ti-calendar-event me-2 text-primary"></i>{{$page_title}}
                                </h5>
                                @if($todaySummary['chef_meal_count'] > 0)
                                    <span class="badge bg-label-warning text-warning fw-semibold px-3 py-2 rounded-pill fs-7">
                                        🧑‍🍳 Chef Meal Configured: <strong class="text-dark ms-1">{{ $todaySummary['chef_meal_count'] }}</strong>
                                    </span>
                                @else
                                    <span class="badge bg-label-secondary px-3 py-2 rounded-pill fs-7">🧑‍🍳 No Chef Meal Configured</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="card-body pt-4">
                        <div id="ajaxAlertContainer"></div>

                        @if(isset($upcomingSpecialMeals) && $upcomingSpecialMeals->count() > 0)
                            @foreach($upcomingSpecialMeals as $sm)
                                <div class="card border-0 shadow-sm rounded-3 overflow-hidden text-white mb-4" style="background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%);">
                                    <div class="card-body p-3 p-md-3.5">
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-white text-primary p-2.5 me-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 46px; height: 46px; min-width: 46px;">
                                                    <i class="ti ti-star fs-4 text-warning"></i>
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
                                                    <h6 class="fw-bold text-white mb-0.5 fs-6">
                                                        {{ $sm->title ?: 'স্পেশাল মিল আয়োজন' }}
                                                    </h6>
                                                    <p class="mb-0 text-white-50 small">
                                                        রেগুলার মিল রেটের বদলে এই নির্দিষ্ট তারিখে স্পেশাল মিল রেট কার্যকর হবে।
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="text-end bg-white bg-opacity-10 p-2 px-3 rounded-3 border border-white border-opacity-25 ms-auto ms-md-0">
                                                <span class="text-white-50 small d-block" style="font-size: 11px;">স্পেশাল মিল রেট</span>
                                                <h5 class="mb-0 text-warning fw-bold font-monospace">
                                                    ৳ {{ number_format($sm->full_rate, 2) }}
                                                </h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        @if(isset($is_fallback) && $is_fallback)
                            <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                                <span class="alert-icon text-info me-2">
                                    <i class="ti ti-info-circle ti-xs"></i>
                                </span>
                                <div>
                                    ⚠️ Today's ({{ now()->format('Y-m-d') }}) meal data not found. Showing data from latest available date: <strong>{{ $used_date }}</strong>.
                                </div>
                            </div>
                        @endif

                        @if(isset($depositWarningMessage) && $depositWarningMessage)
                            <div class="alert alert-danger d-flex align-items-center justify-content-between flex-wrap gap-2 p-3 mb-4 shadow-sm border-danger" role="alert">
                                <div class="d-flex align-items-center">
                                    <span class="alert-icon text-danger me-2 fs-4">
                                        <i class="ti ti-alert-triangle"></i>
                                    </span>
                                    <div class="fw-semibold text-danger fs-6">
                                        {{ $depositWarningMessage }}
                                    </div>
                                </div>
                                <a href="{{ route('dashboard.my-meals') }}" class="btn btn-sm btn-danger fw-bold shadow-sm waves-effect waves-light">
                                    <i class="fa fa-wallet me-1"></i> Meal Deposit Recharge
                                </a>
                            </div>
                        @endif

                        @if(isset($dueWarningMessage) && $dueWarningMessage)
                            <div class="alert alert-danger d-flex align-items-center justify-content-between flex-wrap gap-2 p-3 mb-4 shadow-sm border-danger" role="alert">
                                <div class="d-flex align-items-center">
                                    <span class="alert-icon text-danger me-2 fs-4">
                                        <i class="ti ti-alert-circle"></i>
                                    </span>
                                    <div class="fw-semibold text-danger fs-6">
                                        {{ $dueWarningMessage }}
                                    </div>
                                </div>
                                <a href="{{ route('dashboard.my-payments') }}" class="btn btn-sm btn-danger fw-bold shadow-sm waves-effect waves-light">
                                    <i class="fa fa-mobile-alt me-1"></i> bKash / Pay Online
                                </a>
                            </div>
                        @endif

                        @if ($errors->any())
                            @foreach ($errors->all() as $error)
                                <div class="alert alert-warning mb-2" role="alert">{{ $error }}</div>
                            @endforeach
                        @endif

                        <div id="adminTurnedOffBannerWrapper">
                            @if(isset($isAdminTurnedOff) && $isAdminTurnedOff)
                                <div id="adminTurnedOffAlert" class="alert alert-danger border-danger d-flex align-items-center justify-content-between flex-wrap gap-3 p-3 mb-4 shadow-sm rounded-3" role="alert">
                                    <div class="d-flex align-items-center">
                                        <span class="alert-icon text-danger me-3 fs-3">
                                            <i class="fa fa-ban"></i>
                                        </span>
                                        <div>
                                            <h6 class="alert-heading mb-1 text-danger fw-bold fs-6">🚫 কর্তৃপক্ষ কর্তৃক আপনার মিল বন্ধ রাখা হয়েছে</h6>
                                            <div class="fw-medium text-dark fs-7">
                                                কর্তৃপক্ষ/এডমিন আপনার মিল বন্ধ করে দিয়েছেন। মিল পুনরায় চালু করার জন্য অনুগ্রহ করে মেস কর্তৃপক্ষের সাথে যোগাযোগ করুন.
                                            </div>
                                        </div>
                                    </div>
                                    <a href="tel:{{ $webSetting['phone'] ?? '01977270920' }}" class="btn btn-danger fw-bold shadow-sm waves-effect waves-light px-3 py-2">
                                        <i class="fa fa-phone-alt me-1"></i> মিল চালুর জন্য কল করুন ({{ $webSetting['phone'] ?? '01977270920' }})
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- Meal Cutoff / Helpline Banner -->
                        @if($isAdminOrStaff)
                            <!-- Admin View: Meal Cutoff Schedule Banner -->
                            <div class="alert bg-label-secondary border d-flex align-items-center justify-content-between flex-wrap gap-2 p-3 mb-4 shadow-sm rounded-3" role="alert">
                                <div class="d-flex align-items-center">
                                    <span class="alert-icon text-primary me-2 fs-4">
                                        <i class="fa fa-clock"></i>
                                    </span>
                                    <div>
                                        <strong class="text-dark">মিলের কাট-অফ সময়সূচি (Cutoff Schedule):</strong>
                                        <div class="small text-secondary mt-1">
                                            🌙 <strong>দৈনিক মিল অফ বা পরিবর্তনের সময়:</strong> সকাল ১০:০০ টা হতে রাত ০৮:০০ টা পর্যন্ত
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2" id="cutoffBadgeContainer">
                                    @if(isset($isMealCutoffPassed) && $isMealCutoffPassed)
                                        <span class="badge bg-danger text-white px-3 py-2 rounded-pill fs-7 shadow-sm">
                                            🚫 আজকের মিল পরিবর্তনের সময় সমাপ্ত
                                        </span>
                                    @else
                                        <span class="badge bg-success text-white px-3 py-2 rounded-pill fs-7 shadow-sm">
                                            ✅ মিল পরিবর্তন সময় চলমান
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @else
                            <!-- Single User (Resident) Contact Helpline Banner -->
                            <div class="alert bg-label-primary border border-primary border-opacity-25 d-flex align-items-center justify-content-between flex-wrap gap-3 p-3 mb-4 shadow-sm rounded-3" role="alert">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                                        <i class="fa fa-phone-volume fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="alert-heading mb-1 text-primary fw-bold fs-6">
                                            📢 মিল পরিবর্তন সংক্রান্ত জরুরি নোটিশ
                                        </h6>
                                        <div class="fw-medium text-dark small">
                                            মিল চালু (ON), মিল বন্ধ (OFF) কিংবা হাফ মিল (Half Meal) ইত্যাদি সংক্রান্ত যেকোনো পরিবর্তনের জন্য সরাসরি মেস এডমিন অথবা ম্যানেজারের সাথে যোগাযোগ করুন।
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center flex-wrap gap-2">
                                    <a href="tel:01977270920" class="btn btn-primary btn-sm fw-bold shadow-sm px-3 py-2">
                                        <i class="fa fa-phone-alt me-1"></i> Admin: 01977270920
                                    </a>
                                    @php
                                        $managerPhone = $webSetting['manager_phone'] ?? '';
                                    @endphp
                                    @if(!empty($managerPhone))
                                        <a href="tel:{{ $managerPhone }}" class="btn btn-success btn-sm fw-bold shadow-sm px-3 py-2">
                                            <i class="fa fa-phone-alt me-1"></i> Manager: {{ $managerPhone }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($isAdminOrStaff)
                            <!-- Summary Cards for Admin -->
                            <div class="row g-4 mb-5">
                                <div class="col-md-6 col-xl-3">
                                    <div class="card bg-label-primary border-0 shadow-none h-100">
                                        <div class="card-body text-center p-4">
                                            <div class="avatar mx-auto mb-2">
                                                <span class="avatar-initial rounded-circle bg-primary text-white fs-4">🍽️</span>
                                            </div>
                                            <h6 class="mb-1 text-primary fw-bold">Active Meals (মিল চালু)</h6>
                                            <h3 class="mb-1 fw-bold text-dark"><span id="summary_total_full_meal">{{ $todaySummary['total_active_meal'] ?? $todaySummary['total_full_meal'] }}</span> <span class="fs-6 text-muted">Members</span></h3>
                                            <div class="border-top pt-2 mt-2" id="summary_chef_meal_wrapper" style="{{ $todaySummary['chef_meal_count'] > 0 ? '' : 'display:none;' }}">
                                                <small class="d-block text-dark fw-semibold" id="summary_chef_meal">🧑‍🍳 Chef Meal: {{ $todaySummary['chef_meal_count'] }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-xl-3">
                                    <div class="card bg-label-success border-0 shadow-none h-100">
                                        <div class="card-body text-center p-4">
                                            <div class="avatar mx-auto mb-2">
                                                <span class="avatar-initial rounded-circle bg-success text-white fs-4">🌾</span>
                                            </div>
                                            <h6 class="mb-1 text-success fw-bold">Required Rice (চাল)</h6>
                                            <h3 class="mb-1 fw-bold text-dark"><span id="summary_rice_kg">{{ number_format($todaySummary['grand_total_rice'] / 1000, 2) }}</span> <span class="fs-6 text-muted">kg</span></h3>
                                            <div class="border-top pt-2 mt-2">
                                                <small class="d-block text-dark fw-semibold" id="summary_rice_gm">Total: {{ number_format($todaySummary['grand_total_rice']) }} gm</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-xl-3">
                                    <div class="card bg-label-warning border-0 shadow-none h-100">
                                        <div class="card-body text-center p-4">
                                            <div class="avatar mx-auto mb-2">
                                                <span class="avatar-initial rounded-circle bg-warning text-white fs-4">৳</span>
                                            </div>
                                            <h6 class="mb-2 text-warning fw-bold">Today's Meal Rate</h6>
                                            <div class="d-flex justify-content-center align-items-center gap-3 my-1">
                                                <div>
                                                    <span class="d-block text-muted fw-semibold" style="font-size: 0.78rem;">ফুল মিল (Full)</span>
                                                    <h4 class="mb-0 fw-bold text-dark">৳ {{ number_format($todaySummary['special_rate'], 2) }}</h4>
                                                </div>
                                                <div class="vr bg-warning opacity-50" style="height: 38px;"></div>
                                                <div>
                                                    <span class="d-block text-muted fw-semibold" style="font-size: 0.78rem;">হাফ মিল (Half)</span>
                                                    <h4 class="mb-0 fw-bold text-dark">৳ {{ number_format($todaySummary['special_half_rate'], 2) }}</h4>
                                                </div>
                                            </div>
                                            <div class="border-top pt-2 mt-2">
                                                @if($todaySummary['is_special'])
                                                    <span class="badge bg-warning text-dark fw-bold">⭐ {{ $todaySummary['special_title'] ?: 'Special Meal' }}</span>
                                                @else
                                                    <span class="badge bg-label-secondary fw-semibold">Regular Dynamic Rate</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-xl-3">
                                    <div class="card bg-label-danger border-0 shadow-none h-100">
                                        <div class="card-body text-center p-4">
                                            <div class="avatar mx-auto mb-2">
                                                <span class="avatar-initial rounded-circle bg-danger text-white fs-4">📊</span>
                                            </div>
                                            <h6 class="mb-1 text-danger fw-bold">Total Meal Cost</h6>
                                            <h3 class="mb-1 fw-bold text-danger"><span id="summary_total_cost">{{ number_format($todaySummary['grand_total_cost'], 2) }}</span> Tk</h3>
                                            <div class="border-top pt-2 mt-2">
                                                <small class="d-block text-muted">Daily Total Amount</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Admin Bulk Action Toolbar -->
                            <div class="card border border-primary border-opacity-25 shadow-sm mb-4 bg-light rounded-3">
                                <div class="card-body p-3">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                                <i class="ti ti-bolt fs-5"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark fs-7">এককালীন মিল নিয়ন্ত্রণ (Admin Bulk Controls)</h6>
                                                <small class="text-muted">এক ক্লিকে সকল মেম্বারের মিল চালু বা বন্ধ করুন</small>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                            <button type="button" class="btn btn-success fw-bold shadow-sm waves-effect waves-light bulk-meal-btn px-3 py-2" data-action="all_full">
                                                <i class="ti ti-check me-1"></i> একবারে সবার ফুল চালু (All Full ON)
                                            </button>
                                            <button type="button" class="btn btn-warning text-dark fw-bold shadow-sm waves-effect waves-light bulk-meal-btn px-3 py-2" data-action="all_half_day">
                                                <i class="ti ti-sun me-1"></i> ডে হাফ চালু (Day Half)
                                            </button>
                                            <button type="button" class="btn btn-info text-white fw-bold shadow-sm waves-effect waves-light bulk-meal-btn px-3 py-2" data-action="all_half_night">
                                                <i class="ti ti-moon me-1"></i> নাইট হাফ চালু (Night Half)
                                            </button>
                                            <button type="button" class="btn btn-danger fw-bold shadow-sm waves-effect waves-light bulk-meal-btn px-3 py-2" data-action="all_off">
                                                <i class="ti ti-ban me-1"></i> একবারে সবার মিল বন্ধ (All Meal OFF)
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Search & Status Filter Toolbar for Admin -->
                            <div class="card border border-light-subtle shadow-sm mb-3 bg-white rounded-3">
                                <div class="card-body p-3">
                                    <div class="row g-2 align-items-center justify-content-between">
                                        <div class="col-md-5 col-sm-12">
                                            <div class="input-group">
                                                <span class="input-group-text bg-light text-primary border-end-0">
                                                    <i class="ti ti-search fs-5"></i>
                                                </span>
                                                <input type="text" id="memberTableSearch" class="form-control border-start-0" placeholder="মেম্বারের নাম বা ফোন নম্বর খুঁজুন (Search by name or phone)...">
                                                <button class="btn btn-outline-secondary d-none" type="button" id="clearMemberSearch" title="Clear">
                                                    <i class="ti ti-x"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-sm-6 d-flex gap-1 flex-wrap">
                                            <div class="btn-group w-100" role="group">
                                                <button type="button" class="btn btn-sm btn-primary meal-filter-btn active fw-bold" data-filter="all">All (সবাই)</button>
                                                <button type="button" class="btn btn-sm btn-outline-success meal-filter-btn fw-bold" data-filter="on">ON (চালু)</button>
                                                <button type="button" class="btn btn-sm btn-outline-danger meal-filter-btn fw-bold" data-filter="off">OFF (বন্ধ)</button>
                                                <button type="button" class="btn btn-sm btn-outline-warning meal-filter-btn fw-bold" data-filter="half">Half</button>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6 d-flex align-items-center justify-content-md-end gap-2">
                                            <label for="tablePageSize" class="form-label mb-0 small text-muted text-nowrap">Show:</label>
                                            <select id="tablePageSize" class="form-select form-select-sm" style="width: 85px;">
                                                <option value="10">10</option>
                                                <option value="25" selected>25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                                <option value="all">All</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Single Resident / Student User Specific Summary Cards -->
                            @php
                                $singleUserMeal = $meals->firstWhere('user_id', auth()->id());
                                $isMealOff = $singleUserMeal ? (bool)$singleUserMeal->is_off : false;
                                $isMealHalf = $singleUserMeal ? (bool)$singleUserMeal->half_meal : false;
                                $isMealFull = !$isMealHalf && ($singleUserMeal ? (bool)$singleUserMeal->full_meal : false);
                                $singleNote = strtolower($singleUserMeal ? ($singleUserMeal->note ?? '') : '');

                                $fullRateVal = (float)$todaySummary['special_rate'];
                                $halfRateVal = (float)($todaySummary['special_half_rate'] ?? ($fullRateVal / 2));

                                $myStatusLabel = 'OFF (মিল বন্ধ)';
                                $myStatusBadgeClass = 'bg-label-danger text-danger';
                                $myActiveRateDisplay = '৳ ' . number_format($fullRateVal, 2);
                                $myRateTypeBadge = 'Regular Rate';
                                $myEffectiveCost = 0.00;

                                if (!$isMealOff && $isMealHalf) {
                                    if ($singleNote === 'night' || $singleNote === 'half_night') {
                                        $myStatusLabel = '🌙 হাফ মিল (Night)';
                                        $myStatusBadgeClass = 'bg-info text-white';
                                    } else {
                                        $myStatusLabel = '☀️ হাফ মিল (Day)';
                                        $myStatusBadgeClass = 'bg-warning text-dark';
                                    }
                                    $myActiveRateDisplay = '৳ ' . number_format($halfRateVal, 2);
                                    $myRateTypeBadge = 'হাফ রেট (Half Rate)';
                                    $myEffectiveCost = $halfRateVal;
                                } elseif (!$isMealOff && $isMealFull) {
                                    $myStatusLabel = '✅ ফুল মিল (Full ON)';
                                    $myStatusBadgeClass = 'bg-success text-white';
                                    $myActiveRateDisplay = '৳ ' . number_format($fullRateVal, 2);
                                    $myRateTypeBadge = 'ফুল রেট (Full Rate)';
                                    $myEffectiveCost = $fullRateVal;
                                }

                                $myDeposit = $userDepositMap[auth()->id()] ?? null;
                                $myDepositBalance = $myDeposit ? (float)$myDeposit['balance'] : 0.0;
                                $isZeroDeposit = $myDeposit ? (bool)$myDeposit['is_zero_deposit'] : ($myDepositBalance <= 0);
                            @endphp

                            <div class="row g-4 mb-4">
                                <!-- Card 1: My Today's Meal Status -->
                                <div class="col-md-4 col-sm-6">
                                    <div class="card bg-label-primary border-0 shadow-none h-100">
                                        <div class="card-body text-center p-4">
                                            <div class="avatar mx-auto mb-2">
                                                <span class="avatar-initial rounded-circle bg-primary text-white fs-4">🍽️</span>
                                            </div>
                                            <h6 class="mb-1 text-primary fw-bold">My Today's Meal (আজকের মিল)</h6>
                                            <h4 class="mb-1 fw-bold text-dark" id="resident_card_status_text">
                                                <span class="badge {{ $myStatusBadgeClass }} fs-6 px-2.5 py-1 rounded-pill">{{ $myStatusLabel }}</span>
                                            </h4>
                                            <div class="border-top pt-2 mt-2">
                                                <small class="text-muted">আপনার আজকের মিলের বর্তমান অবস্থা</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Card 2: Today's Dynamic Rate & Effective Cost -->
                                <div class="col-md-4 col-sm-6">
                                    <div class="card bg-label-warning border-0 shadow-none h-100">
                                        <div class="card-body text-center p-4">
                                            <div class="avatar mx-auto mb-2">
                                                <span class="avatar-initial rounded-circle bg-warning text-white fs-4">৳</span>
                                            </div>
                                            <h6 class="mb-2 text-warning fw-bold">Today's Meal Rate (আজকের মিল রেট)</h6>
                                            <div class="d-flex justify-content-center align-items-center gap-3 my-1">
                                                <div>
                                                    <span class="d-block text-muted fw-semibold" style="font-size: 0.78rem;">ফুল মিল (Full)</span>
                                                    <h4 class="mb-0 fw-bold text-dark">৳ {{ number_format($fullRateVal, 2) }}</h4>
                                                </div>
                                                <div class="vr bg-warning opacity-50" style="height: 38px;"></div>
                                                <div>
                                                    <span class="d-block text-muted fw-semibold" style="font-size: 0.78rem;">হাফ মিল (Half)</span>
                                                    <h4 class="mb-0 fw-bold text-dark">৳ {{ number_format($halfRateVal, 2) }}</h4>
                                                </div>
                                            </div>
                                            <div class="border-top pt-2 mt-2">
                                                <span class="badge bg-warning text-dark fw-bold" id="resident_card_rate_type">
                                                    {{ $isMealOff ? 'মিল বন্ধ (No Cost)' : ($todaySummary['is_special'] ? '⭐ Special Meal (' . $myRateTypeBadge . ': ' . $myActiveRateDisplay . ')' : 'আজকের খরচ: ' . $myActiveRateDisplay) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Card 3: Meal Deposit Balance -->
                                <div class="col-md-4 col-sm-12">
                                    <div class="card bg-label-success border-0 shadow-none h-100">
                                        <div class="card-body text-center p-4">
                                            <div class="avatar mx-auto mb-2">
                                                <span class="avatar-initial rounded-circle bg-success text-white fs-4">💳</span>
                                            </div>
                                            <h6 class="mb-1 text-success fw-bold">Deposit Balance (ডিপোজিট ব্যালেন্স)</h6>
                                            <h3 class="mb-1 fw-bold text-success" id="resident_card_balance_display">
                                                ৳ {{ number_format($myDepositBalance, 2) }}
                                            </h3>
                                            <div class="border-top pt-2 mt-2">
                                                @if($myDepositBalance > 0)
                                                    <span class="badge bg-success text-white fw-semibold">✅ বর্তমান ব্যালেন্স</span>
                                                @else
                                                    <span class="badge bg-danger text-white fw-semibold">🚫 ব্যালেন্স নেই</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Meal Table Layout with Instant Auto-Save -->
                        <div class="table-responsive rounded-3">
                            <table class="table table-hover align-middle mb-0" id="membersMealTable">
                                <thead class="table-light">
                                <tr>
                                    <th style="width: 60px;" class="fw-bold text-secondary text-uppercase">SL</th>
                                    <th class="fw-bold text-secondary text-uppercase">Member Name</th>
                                    <th class="fw-bold text-secondary text-uppercase">Phone</th>
                                    <th style="width: 260px;" class="text-center fw-bold text-secondary text-uppercase">
                                        HALF MEAL OPTION
                                    </th>
                                    <th style="width: 140px;" class="text-center fw-bold text-secondary text-uppercase">
                                        FULL MEAL
                                    </th>
                                    @if($isAdminOrStaff)
                                        <th style="width: 140px;" class="text-center fw-bold text-secondary text-uppercase">
                                            QUICK ACTION
                                        </th>
                                    @endif
                                </tr>
                                </thead>
                                <tbody>
                                @php
                                    $mealMap = collect($meals)->keyBy('user_id');
                                @endphp

                                @foreach($users as $key => $user)
                                    @php
                                        $meal = $mealMap->get($user->id);
                                        $isOff  = $meal ? (bool)$meal->is_off : false;
                                        $isHalf = $meal ? (bool)$meal->half_meal : false;
                                        $isFull = !$isHalf && ($meal ? (bool)$meal->full_meal : false);

                                        $mealNote = strtolower($meal ? ($meal->note ?? '') : '');
                                        $isHalfDay = $isHalf && ($mealNote === 'day' || $mealNote === 'half_day');
                                        $isHalfNight = $isHalf && ($mealNote === 'night' || $mealNote === 'half_night');

                                        if ($isHalf && !$isHalfDay && !$isHalfNight) {
                                            $isHalfDay = true;
                                        }

                                        $userDep = $userDepositMap[$user->id] ?? null;
                                        $isZeroDeposit = $userDep ? $userDep['is_zero_deposit'] : false;

                                        $pendingReq = \App\Models\Backend\MealRequest::where('user_id', $user->id)
                                            ->where('status', 0)
                                            ->latest()
                                            ->first();

                                        $userLeaveActive = \App\Models\Backend\ResidentCheckInOut::where('user_id', $user->id)
                                            ->whereDate('check_out_date', '<=', today())
                                            ->where('status', 0)
                                            ->latest('check_out_date')
                                            ->first();

                                        $userReturnedToday = \App\Models\Backend\ResidentCheckInOut::where('user_id', $user->id)
                                            ->where('status', 1)
                                            ->whereDate('check_in_date', today())
                                            ->latest('check_in_date')
                                            ->first();

                                        $isOutside = (bool)($userLeaveActive || ($isOff && (str_contains($mealNote, 'checked out') || str_contains($mealNote, 'leave'))));
                                        $isReturnedToday = (bool)($userReturnedToday || ($isOff && (str_contains($mealNote, 'checked in') || str_contains($mealNote, 'resumes tomorrow') || (str_contains($mealNote, 'check') && str_contains($mealNote, 'in')))));

                                        if (!$isOutside && !$isReturnedToday && $isOff && str_contains($mealNote, 'check')) {
                                            $isOutside = true;
                                        }

                                        $isOnLeave = $isOutside || $isReturnedToday;

                                        $isResident = !$isAdminOrStaff;
                                        // Admin is NEVER locked, only resident has restrictions
                                        $isRowLocked = $isResident && ($isOnLeave || $isZeroDeposit || $isOff || !$canSaveMeal || (isset($isMealCutoffPassed) && $isMealCutoffPassed) || (isset($isAdminTurnedOff) && $isAdminTurnedOff));
                                    @endphp

                                    <tr id="row_user_{{ $user->id }}" 
                                        class="member-meal-row {{ $isRowLocked ? 'opacity-75' : '' }}"
                                        data-name="{{ strtolower($user->name) }}"
                                        data-phone="{{ preg_replace('/[^0-9]/', '', $user->phone ?? '') }}">
                                        <td><span class="text-muted font-monospace row-sl-number">{{ str_pad($key + 1, 2, '0', STR_PAD_LEFT) }}</span></td>
                                        <td>
                                            <div class="fw-semibold text-heading d-flex align-items-center flex-wrap gap-1">
                                                {{ $user->name }}
                                                @if($isReturnedToday)
                                                    <span class="badge bg-success text-white ms-1 meal-status-badge" style="font-size: 0.65rem;" title="হোস্টেলে চেক-ইন সম্পন্ন (আগামীকাল থেকে মিল চালু)">
                                                        🟢 চেক-ইন (আগামীকাল থেকে মিল চালু)
                                                    </span>
                                                @elseif($isOutside)
                                                    <span class="badge bg-danger text-white ms-1 meal-status-badge" style="font-size: 0.65rem;" title="যতক্ষণ বাইরে থাকবেন চেক-আউট থাকায় মিল বন্ধ">
                                                        🚫 চেক-আউট (মিল বন্ধ)
                                                    </span>
                                                @elseif($pendingReq)
                                                    <span class="badge bg-warning text-dark ms-1 pending-badge-span" style="font-size: 0.65rem;" title="এডমিনের অনুমোদনের জন্য অপেক্ষমাণ">
                                                        ⏳ Pending Approval
                                                    </span>
                                                @elseif($isZeroDeposit)
                                                    <span class="badge bg-danger text-white ms-1 meal-status-badge" style="font-size: 0.65rem;" title="ডিপোজিট জমা না থাকায় সতর্কতা">
                                                        🚫 মিল অন করতে ডিপোজিট লাগবে
                                                    </span>
                                                @elseif($isOff)
                                                    <span class="badge bg-danger text-white ms-1 meal-status-badge" style="font-size: 0.65rem;">
                                                        🚫 মিল বন্ধ (Meal OFF)
                                                    </span>
                                                @elseif($isHalf && $isHalfDay)
                                                    <span class="badge bg-warning text-dark ms-1 meal-status-badge" style="font-size: 0.65rem;">
                                                        ☀️ হাফ মিল (Day)
                                                    </span>
                                                @elseif($isHalf && $isHalfNight)
                                                    <span class="badge bg-info text-white ms-1 meal-status-badge" style="font-size: 0.65rem;">
                                                        🌙 হাফ মিল (Night)
                                                    </span>
                                                @elseif($isFull && !$isOff)
                                                    <span class="badge bg-success text-white ms-1 meal-status-badge" style="font-size: 0.65rem;">
                                                        ✅ মিল চালু (Meal ON)
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td><small class="text-secondary">{{ $user->phone ?? '-' }}</small></td>

                                        <!-- HALF MEAL OPTION Column -->
                                        <td class="text-center align-middle cell-half {{ $isRowLocked ? 'pe-none opacity-50' : '' }}">
                                            <div class="d-inline-flex flex-column align-items-center">
                                                <div class="form-check d-flex align-items-center mb-0">
                                                    <input type="checkbox" id="half_{{ $user->id }}"
                                                           class="form-check-input meal-half-toggle cursor-pointer shadow-none"
                                                           data-userid="{{ $user->id }}"
                                                           {{ ($isHalf && !$isOff) ? 'checked' : '' }}
                                                           {{ $isRowLocked ? 'disabled' : '' }}>
                                                    <label class="form-check-label ms-2 text-secondary fw-semibold cursor-pointer select-none" for="half_{{ $user->id }}">
                                                        Half
                                                    </label>
                                                </div>

                                                <!-- Day / Night Selection Box -->
                                                <div id="half_options_{{ $user->id }}" class="half-options-card border rounded-3 p-2 bg-white shadow-sm mt-2 {{ ($isHalf && !$isOff) ? 'd-flex' : 'd-none' }} align-items-center gap-3">
                                                    <div class="form-check d-flex align-items-center mb-0">
                                                        <input class="form-check-input meal-time-radio cursor-pointer" type="radio" 
                                                               name="half_time_{{ $user->id }}" id="half_day_{{ $user->id }}" 
                                                               value="half_day" data-userid="{{ $user->id }}" 
                                                               {{ ($isHalfDay || (!$isHalfDay && !$isHalfNight)) ? 'checked' : '' }} 
                                                               {{ $isRowLocked ? 'disabled' : '' }}>
                                                        <label class="form-check-label ms-1 cursor-pointer fw-semibold fs-7 d-flex align-items-center select-none" for="half_day_{{ $user->id }}">
                                                            <span class="me-1">☀️</span> Day
                                                        </label>
                                                    </div>
                                                    <div class="form-check d-flex align-items-center mb-0">
                                                        <input class="form-check-input meal-time-radio cursor-pointer" type="radio" 
                                                               name="half_time_{{ $user->id }}" id="half_night_{{ $user->id }}" 
                                                               value="half_night" data-userid="{{ $user->id }}" 
                                                               {{ $isHalfNight ? 'checked' : '' }} 
                                                               {{ $isRowLocked ? 'disabled' : '' }}>
                                                        <label class="form-check-label ms-1 cursor-pointer fw-semibold fs-7 d-flex align-items-center select-none" for="half_night_{{ $user->id }}">
                                                            <span class="me-1">🌙</span> Night
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- FULL MEAL Column -->
                                        <td class="text-center align-middle cell-full {{ $isRowLocked ? 'pe-none opacity-50' : '' }}">
                                            <div class="form-check d-flex justify-content-center mb-0">
                                                <input type="checkbox" id="full_{{ $user->id }}"
                                                       class="form-check-input meal-full-toggle cursor-pointer shadow-none"
                                                       data-userid="{{ $user->id }}"
                                                       {{ ($isFull && !$isOff) ? 'checked' : '' }} 
                                                       {{ $isRowLocked ? 'disabled' : '' }}>
                                            </div>
                                        </td>

                                        @if($isAdminOrStaff)
                                            <!-- Admin Single User Action Column -->
                                            <td class="text-center align-middle cell-action">
                                                <div class="d-flex justify-content-center align-items-center gap-1">
                                                    @if($isOff)
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-success fw-bold px-2 py-1 row-quick-toggle-btn shadow-sm" 
                                                                id="quick_btn_{{ $user->id }}"
                                                                data-userid="{{ $user->id }}" 
                                                                data-action="turn_on" 
                                                                title="মিল পুনরায় চালু করুন">
                                                            <i class="ti ti-check me-1"></i>চালু করুন
                                                        </button>
                                                    @else
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-danger fw-bold px-2 py-1 row-quick-toggle-btn shadow-sm" 
                                                                id="quick_btn_{{ $user->id }}"
                                                                data-userid="{{ $user->id }}" 
                                                                data-action="turn_off" 
                                                                title="মিল বন্ধ করুন">
                                                            <i class="ti ti-ban me-1"></i>বন্ধ করুন
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach

                                <tr id="noMatchingMembersRow" class="d-none">
                                    <td colspan="{{ $isAdminOrStaff ? 6 : 5 }}" class="text-center py-5 text-muted">
                                        <i class="ti ti-search-off fs-1 text-secondary mb-2 d-block"></i>
                                        <h6 class="fw-bold text-secondary mb-1">কোনো মেম্বার পাওয়া যায়নি</h6>
                                        <p class="small text-muted mb-0">আপনার সার্চ করা নাম বা ফিল্টারের সাথে কোনো মেম্বার মেলেনি।</p>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Table Pagination & Record Count Footer -->
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-3 px-1 border-top mt-2" id="tablePaginationWrapper">
                            <div class="small text-muted fw-semibold" id="tableRecordCountInfo">
                                Showing <span id="pageShowingStart">1</span> to <span id="pageShowingEnd">25</span> of <span id="pageTotalRecords">{{ count($users) }}</span> members
                            </div>
                            <nav aria-label="Table pagination">
                                <ul class="pagination pagination-sm mb-0" id="tablePaginationList">
                                    <!-- Dynamic pagination items -->
                                </ul>
                            </nav>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Meal OFF Single Dropdown Modal -->
    <div class="modal fade" id="mealOffModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white py-3">
                    <h5 class="modal-header-title text-white mb-0 fw-bold">
                        <i class="fa fa-ban me-2"></i> Meal OFF Request (মিল বন্ধের নির্বাচন)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="modal_target_user_id">

                    <!-- Single Select Dropdown Field -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark fs-6">কত দিনের জন্য মিল বন্ধ রাখতে চান?</label>
                        <select id="modal_meal_option" class="form-select fw-bold border-danger text-danger py-2">
                            <option value="days_1" selected>🚫 আগামীকাল ১ দিন মিল বন্ধ (Tomorrow 1 Day OFF)</option>
                            @php
                                $bnNumbers = [
                                    1=>'১', 2=>'২', 3=>'৩', 4=>'৪', 5=>'৫', 6=>'৬', 7=>'৭', 8=>'৮', 9=>'৯', 10=>'১০',
                                    11=>'১১', 12=>'১২', 13=>'১৩', 14=>'১৪', 15=>'১৫', 16=>'১৬', 17=>'১৭', 18=>'১৮', 19=>'১৯', 20=>'২০',
                                    21=>'২১', 22=>'২২', 23=>'২৩', 24=>'২৪', 25=>'২৫', 26=>'২৬', 27=>'২৭', 28=>'২৮', 29=>'২৯', 30=>'৩০'
                                ];
                            @endphp
                            @for ($i = 2; $i <= 30; $i++)
                                @php
                                    $bnText = $bnNumbers[$i] ?? $i;
                                    $specialTag = '';
                                    if ($i == 7) $specialTag = ' / ১ সপ্তাহ';
                                    elseif ($i == 14) $specialTag = ' / ২ সপ্তাহ';
                                    elseif ($i == 21) $specialTag = ' / ৩ সপ্তাহ';
                                    elseif ($i == 30) $specialTag = ' / ১ মাস';
                                @endphp
                                <option value="days_{{ $i }}">🚫 আগামীকাল থেকে {{ $bnText }} দিন মিল বন্ধ{{ $specialTag }} (Tomorrow {{ $i }} Days OFF)</option>
                            @endfor
                        </select>
                    </div>

                    <!-- Live Date Summary Preview -->
                    <div id="modal_date_summary" class="p-3 bg-light-danger rounded border border-danger text-danger fs-7 fw-semibold mb-2">
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm fw-bold" data-bs-dismiss="modal">বাতিল</button>
                    <button type="button" id="confirmMealOffBtn" class="btn btn-danger btn-sm fw-bold shadow-sm">
                        <i class="fa fa-paper-plane me-1"></i> অনুরোধ পাঠান (Confirm OFF)
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const updateUrl = "{{ route('meals.update-status') }}";
            const liveStatusUrl = "{{ route('meals.live-status') }}";
            const csrfToken = "{{ csrf_token() }}";
            const isAdminOrStaff = {{ $isAdminOrStaff ? 'true' : 'false' }};
            let mealOffModalObj = null;

            // --- Realtime Search, Status Filtering & Dynamic Pagination ---
            let currentPage = 1;
            let currentFilter = 'all';
            let currentSearchTerm = '';
            let currentPageSize = 25;

            const searchInput = document.getElementById('memberTableSearch');
            const clearSearchBtn = document.getElementById('clearMemberSearch');
            const pageSizeSelect = document.getElementById('tablePageSize');
            const filterBtns = document.querySelectorAll('.meal-filter-btn');

            function getRowMealState(row) {
                const fullCb = row.querySelector('.meal-full-toggle');
                const halfCb = row.querySelector('.meal-half-toggle');
                const isFull = fullCb ? fullCb.checked : false;
                const isHalf = halfCb ? halfCb.checked : false;
                const isOff = !isFull && !isHalf;
                return { isFull, isHalf, isOff };
            }

            function filterAndPaginateTable() {
                const allRows = Array.from(document.querySelectorAll('#membersMealTable tbody tr.member-meal-row'));
                const noMatchRow = document.getElementById('noMatchingMembersRow');
                const showingStartEl = document.getElementById('pageShowingStart');
                const showingEndEl = document.getElementById('pageShowingEnd');
                const totalRecordsEl = document.getElementById('pageTotalRecords');
                const paginationList = document.getElementById('tablePaginationList');

                const term = (currentSearchTerm || '').trim().toLowerCase();

                // 1. Filter rows
                const matchingRows = allRows.filter(row => {
                    const name = (row.getAttribute('data-name') || '').toLowerCase();
                    const phone = (row.getAttribute('data-phone') || '');
                    const nameText = row.querySelector('.fw-semibold.text-heading')?.textContent?.toLowerCase() || '';

                    const matchesSearch = !term || name.includes(term) || phone.includes(term) || nameText.includes(term);
                    if (!matchesSearch) return false;

                    const state = getRowMealState(row);
                    if (currentFilter === 'on') return state.isFull;
                    if (currentFilter === 'off') return state.isOff;
                    if (currentFilter === 'half') return state.isHalf;
                    return true; // 'all'
                });

                const totalMatching = matchingRows.length;

                // 2. Handle 0 results
                if (totalMatching === 0) {
                    allRows.forEach(row => row.classList.add('d-none'));
                    if (noMatchRow) noMatchRow.classList.remove('d-none');
                    if (showingStartEl) showingStartEl.innerText = '0';
                    if (showingEndEl) showingEndEl.innerText = '0';
                    if (totalRecordsEl) totalRecordsEl.innerText = '0';
                    if (paginationList) paginationList.innerHTML = '';
                    return;
                }

                if (noMatchRow) noMatchRow.classList.add('d-none');

                // 3. Compute Pagination Boundaries
                const pageSize = currentPageSize === 'all' ? totalMatching : parseInt(currentPageSize, 10);
                const totalPages = Math.ceil(totalMatching / pageSize);

                if (currentPage > totalPages) currentPage = totalPages;
                if (currentPage < 1) currentPage = 1;

                const startIndex = (currentPage - 1) * pageSize;
                const endIndex = currentPageSize === 'all' ? totalMatching : Math.min(startIndex + pageSize, totalMatching);

                // 4. Update Row Visibility & Serial Numbers
                allRows.forEach(row => row.classList.add('d-none'));

                matchingRows.forEach((row, idx) => {
                    if (idx >= startIndex && idx < endIndex) {
                        row.classList.remove('d-none');
                        const slSpan = row.querySelector('.row-sl-number');
                        if (slSpan) {
                            slSpan.innerText = String(idx + 1).padStart(2, '0');
                        }
                    }
                });

                // 5. Update Record Counter
                if (showingStartEl) showingStartEl.innerText = String(startIndex + 1);
                if (showingEndEl) showingEndEl.innerText = String(endIndex);
                if (totalRecordsEl) totalRecordsEl.innerText = String(totalMatching);

                // 6. Render Pagination UI
                renderPaginationButtons(totalPages, totalMatching);
            }

            function renderPaginationButtons(totalPages, totalMatching) {
                const paginationList = document.getElementById('tablePaginationList');
                if (!paginationList) return;

                if (totalPages <= 1) {
                    paginationList.innerHTML = '';
                    return;
                }

                let html = '';

                // Prev Button
                html += `
                    <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="javascript:void(0);" data-page="${currentPage - 1}" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                `;

                // Page Number Buttons with Smart Windowing
                const maxButtons = 5;
                let startPage = Math.max(1, currentPage - 2);
                let endPage = Math.min(totalPages, startPage + maxButtons - 1);
                if (endPage - startPage < maxButtons - 1) {
                    startPage = Math.max(1, endPage - maxButtons + 1);
                }

                if (startPage > 1) {
                    html += `<li class="page-item"><a class="page-link" href="javascript:void(0);" data-page="1">1</a></li>`;
                    if (startPage > 2) {
                        html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                    }
                }

                for (let p = startPage; p <= endPage; p++) {
                    html += `
                        <li class="page-item ${p === currentPage ? 'active' : ''}">
                            <a class="page-link" href="javascript:void(0);" data-page="${p}">${p}</a>
                        </li>
                    `;
                }

                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) {
                        html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                    }
                    html += `<li class="page-item"><a class="page-link" href="javascript:void(0);" data-page="${totalPages}">${totalPages}</a></li>`;
                }

                // Next Button
                html += `
                    <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                        <a class="page-link" href="javascript:void(0);" data-page="${currentPage + 1}" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                `;

                paginationList.innerHTML = html;
            }

            // Pagination click delegation
            const paginationListEl = document.getElementById('tablePaginationList');
            if (paginationListEl) {
                paginationListEl.addEventListener('click', function (e) {
                    const link = e.target.closest('a.page-link');
                    if (!link) return;
                    e.preventDefault();
                    const targetPage = parseInt(link.getAttribute('data-page'), 10);
                    if (!isNaN(targetPage) && targetPage !== currentPage && targetPage >= 1) {
                        currentPage = targetPage;
                        filterAndPaginateTable();
                    }
                });
            }

            // Search input listener
            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    currentSearchTerm = this.value;
                    currentPage = 1;
                    if (clearSearchBtn) {
                        if (currentSearchTerm.length > 0) {
                            clearSearchBtn.classList.remove('d-none');
                        } else {
                            clearSearchBtn.classList.add('d-none');
                        }
                    }
                    filterAndPaginateTable();
                });
            }

            // Clear search button listener
            if (clearSearchBtn) {
                clearSearchBtn.addEventListener('click', function () {
                    if (searchInput) {
                        searchInput.value = '';
                        currentSearchTerm = '';
                    }
                    clearSearchBtn.classList.add('d-none');
                    currentPage = 1;
                    filterAndPaginateTable();
                    if (searchInput) searchInput.focus();
                });
            }

            // Status filter buttons listener
            filterBtns.forEach(btn => {
                btn.addEventListener('click', function () {
                    filterBtns.forEach(b => {
                        b.classList.remove('btn-primary', 'btn-success', 'btn-danger', 'btn-warning', 'active');
                        const f = b.getAttribute('data-filter');
                        if (f === 'all') b.classList.add('btn-outline-primary');
                        else if (f === 'on') b.classList.add('btn-outline-success');
                        else if (f === 'off') b.classList.add('btn-outline-danger');
                        else if (f === 'half') b.classList.add('btn-outline-warning');
                    });

                    this.classList.remove('btn-outline-primary', 'btn-outline-success', 'btn-outline-danger', 'btn-outline-warning');
                    const filter = this.getAttribute('data-filter');
                    currentFilter = filter;

                    if (filter === 'all') this.classList.add('btn-primary', 'active');
                    else if (filter === 'on') this.classList.add('btn-success', 'active');
                    else if (filter === 'off') this.classList.add('btn-danger', 'active');
                    else if (filter === 'half') this.classList.add('btn-warning', 'active');

                    currentPage = 1;
                    filterAndPaginateTable();
                });
            });

            // Page size dropdown listener
            if (pageSizeSelect) {
                pageSizeSelect.addEventListener('change', function () {
                    currentPageSize = this.value;
                    currentPage = 1;
                    filterAndPaginateTable();
                });
            }

            // Initialize table pagination and summary cards on start
            filterAndPaginateTable();
            recalculateSummaryLive();

            function syncLiveMealStatus() {
                fetch(liveStatusUrl + '?date={{ now()->format('Y-m-d') }}', {
                    headers: { 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(data => {
                    if (data && data.success && data.meals) {
                        Object.keys(data.meals).forEach(userId => {
                            const meal = data.meals[userId];
                            const row = document.getElementById('row_user_' + userId);
                            if (!row) return;

                            const fullCb = document.getElementById('full_' + userId);
                            const halfCb = document.getElementById('half_' + userId);
                            const halfOptions = document.getElementById('half_options_' + userId);
                            const dayRadio = document.getElementById('half_day_' + userId);
                            const nightRadio = document.getElementById('half_night_' + userId);
                            let statusBadge = row.querySelector('.meal-status-badge');
                            const nameDiv = row.querySelector('.fw-semibold.text-heading');

                            if (!statusBadge && nameDiv) {
                                statusBadge = document.createElement('span');
                                statusBadge.className = 'badge ms-1 meal-status-badge';
                                statusBadge.style.fontSize = '0.65rem';
                                nameDiv.appendChild(statusBadge);
                            }

                            if (meal.is_zero_deposit) {
                                if (fullCb) fullCb.checked = false;
                                if (halfCb) halfCb.checked = false;
                                if (halfOptions) {
                                    halfOptions.classList.add('d-none');
                                    halfOptions.classList.remove('d-flex');
                                }
                                if (statusBadge) {
                                    statusBadge.className = 'badge bg-danger text-white ms-1 meal-status-badge';
                                    statusBadge.innerHTML = '🚫 মিল অন করতে ডিপোজিট লাগবে';
                                }
                            } else if (meal.is_off) {
                                if (fullCb) fullCb.checked = false;
                                if (halfCb) halfCb.checked = false;
                                if (halfOptions) {
                                    halfOptions.classList.add('d-none');
                                    halfOptions.classList.remove('d-flex');
                                }
                                if (statusBadge) {
                                    const noteStr = (meal.note || '').toLowerCase();
                                    if (meal.is_returned_today || noteStr.includes('checked in') || noteStr.includes('resumes tomorrow') || (noteStr.includes('check') && noteStr.includes('in'))) {
                                        statusBadge.className = 'badge bg-success text-white ms-1 meal-status-badge';
                                        statusBadge.innerHTML = '🟢 চেক-ইন (আগামীকাল থেকে মিল চালু)';
                                        statusBadge.setAttribute('title', 'হোস্টেলে চেক-ইন সম্পন্ন (আগামীকাল থেকে মিল চালু)');
                                    } else if (meal.is_outside || meal.is_leave || noteStr.includes('leave') || noteStr.includes('check')) {
                                        statusBadge.className = 'badge bg-danger text-white ms-1 meal-status-badge';
                                        statusBadge.innerHTML = '🚫 চেক-আউট (মিল বন্ধ)';
                                        statusBadge.setAttribute('title', 'যতক্ষণ বাইরে থাকবেন চেক-আউট থাকায় মিল বন্ধ');
                                    } else {
                                        statusBadge.className = 'badge bg-danger text-white ms-1 meal-status-badge';
                                        statusBadge.innerHTML = '🚫 মিল বন্ধ (Meal OFF)';
                                    }
                                }
                            } else if (meal.half_meal) {
                                if (fullCb) fullCb.checked = false;
                                if (halfCb) halfCb.checked = true;
                                if (halfOptions) {
                                    halfOptions.classList.remove('d-none');
                                    halfOptions.classList.add('d-flex');
                                }
                                if (meal.is_half_night) {
                                    if (nightRadio) nightRadio.checked = true;
                                    if (statusBadge) {
                                        statusBadge.className = 'badge bg-info text-white ms-1 meal-status-badge';
                                        statusBadge.innerHTML = '🌙 হাফ মিল (Night)';
                                    }
                                } else {
                                    if (dayRadio) dayRadio.checked = true;
                                    if (statusBadge) {
                                        statusBadge.className = 'badge bg-warning text-dark ms-1 meal-status-badge';
                                        statusBadge.innerHTML = '☀️ হাফ মিল (Day)';
                                    }
                                }
                            } else if (meal.full_meal) {
                                if (fullCb) fullCb.checked = true;
                                if (halfCb) halfCb.checked = false;
                                if (halfOptions) {
                                    halfOptions.classList.add('d-none');
                                    halfOptions.classList.remove('d-flex');
                                }
                                if (statusBadge) {
                                    statusBadge.className = 'badge bg-success text-white ms-1 meal-status-badge';
                                    statusBadge.innerHTML = '✅ মিল চালু (Meal ON)';
                                }
                            }

                            // Update quick action button for admin
                            const quickBtn = document.getElementById('quick_btn_' + userId);
                            if (quickBtn) {
                                if (meal.is_off) {
                                    quickBtn.className = 'btn btn-sm btn-outline-success fw-bold px-2 py-1 row-quick-toggle-btn shadow-sm';
                                    quickBtn.setAttribute('data-action', 'turn_on');
                                    quickBtn.innerHTML = '<i class="ti ti-check me-1"></i>চালু করুন';
                                } else {
                                    quickBtn.className = 'btn btn-sm btn-outline-danger fw-bold px-2 py-1 row-quick-toggle-btn shadow-sm';
                                    quickBtn.setAttribute('data-action', 'turn_off');
                                    quickBtn.innerHTML = '<i class="ti ti-ban me-1"></i>বন্ধ করুন';
                                }
                            }
                        });

                        if (data.today_summary) {
                            const elMeal = document.getElementById('summary_total_full_meal');
                            const elChef = document.getElementById('summary_chef_meal');
                            const elChefWrapper = document.getElementById('summary_chef_meal_wrapper');
                            const elRiceKg = document.getElementById('summary_rice_kg');
                            const elRiceGm = document.getElementById('summary_rice_gm');
                            const elCost = document.getElementById('summary_total_cost');

                            if (elMeal && (data.today_summary.total_active_meal !== undefined || data.today_summary.total_full_meal !== undefined)) {
                                elMeal.innerText = data.today_summary.total_active_meal ?? data.today_summary.total_full_meal;
                            }
                            if (elChefWrapper && elChef) {
                                if (data.today_summary.chef_meal_count > 0) {
                                    elChefWrapper.style.display = '';
                                    elChef.innerText = '🧑‍🍳 Chef Meal: ' + data.today_summary.chef_meal_count;
                                } else {
                                    elChefWrapper.style.display = 'none';
                                }
                            }
                            if (elRiceKg && data.today_summary.grand_total_rice !== undefined) {
                                elRiceKg.innerText = (data.today_summary.grand_total_rice / 1000).toFixed(2);
                            }
                            if (elRiceGm && data.today_summary.grand_total_rice !== undefined) {
                                elRiceGm.innerText = 'Total: ' + Number(data.today_summary.grand_total_rice).toLocaleString() + ' gm';
                            }
                            if (elCost && data.today_summary.grand_total_cost !== undefined) {
                                elCost.innerText = Number(data.today_summary.grand_total_cost).toFixed(2);
                            }
                        }

                        recalculateSummaryLive();
                        if (currentFilter !== 'all') {
                            filterAndPaginateTable();
                        }
                    }
                })
                .catch(() => {});
            }

            // Real-time live background polling every 3.5 seconds and on tab focus
            setInterval(syncLiveMealStatus, 3500);
            window.addEventListener('focus', syncLiveMealStatus);
            document.addEventListener('visibilitychange', function() {
                if (document.visibilityState === 'visible') {
                    syncLiveMealStatus();
                }
            });

            function showAlert(message, type = 'success') {
                const container = document.getElementById('ajaxAlertContainer');
                if (!container) return;
                const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
                container.innerHTML = `
                    <div class="alert alert-${type} alert-dismissible d-flex align-items-center mb-4 shadow-sm" role="alert">
                        <i class="fa ${icon} me-2 fs-5"></i>
                        <div>${message}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
            }

            function updateModalSummary() {
                const optionVal = document.getElementById('modal_meal_option').value;
                const summaryBox = document.getElementById('modal_date_summary');
                if (!summaryBox) return;

                const todayStr = "{{ now()->format('Y-m-d') }}";
                const todayObj = new Date(todayStr + 'T00:00:00');
                const tomorrowObj = new Date(todayObj);
                tomorrowObj.setDate(todayObj.getDate() + 1);

                const options = { day: '2-digit', month: 'short', year: 'numeric' };

                if (optionVal.startsWith('days_')) {
                    const totalDays = parseInt(optionVal.replace('days_', '')) || 1;
                    const endDateObj = new Date(tomorrowObj);
                    endDateObj.setDate(tomorrowObj.getDate() + (totalDays - 1));

                    const startFormatted = tomorrowObj.toLocaleDateString('en-GB', options);
                    const endFormatted = endDateObj.toLocaleDateString('en-GB', options);
                    const bnDigits = {'0':'০','1':'১','2':'২','3':'৩','4':'৪','5':'৫','6':'৬','7':'৭','8':'৮','9':'৯'};
                    const bnTotalDays = String(totalDays).replace(/[0-9]/g, w => bnDigits[w] || w);

                    if (totalDays === 1) {
                        summaryBox.innerHTML = `🗓️ <strong>মিলের হিসাব</strong>: আগামীকাল <strong>${startFormatted}</strong> (১ দিন) আপনার মিল বন্ধ রাখার অনুরোধ এডমিনের কাছে পাঠানো হবে।`;
                    } else {
                        summaryBox.innerHTML = `🗓️ <strong>মিলের হিসাব</strong>: আগামীকাল <strong>${startFormatted}</strong> হতে <strong>${endFormatted}</strong> পর্যন্ত (মোট <strong>${bnTotalDays} দিন</strong>) আপনার মিল বন্ধ রাখার অনুরোধ এডমিনের কাছে পাঠানো হবে।`;
                    }
                }
            }

            const mealOptionEl = document.getElementById('modal_meal_option');
            if (mealOptionEl) {
                mealOptionEl.addEventListener('change', updateModalSummary);
            }

            function recalculateSummaryLive() {
                let fullCount = 0;
                let halfDayCount = 0;
                let halfNightCount = 0;

                document.querySelectorAll('tr[id^="row_user_"]').forEach(function (row) {
                    const fullCb = row.querySelector('.meal-full-toggle');
                    const halfCb = row.querySelector('.meal-half-toggle');

                    if (halfCb && halfCb.checked) {
                        const dayRadio = row.querySelector('input[value="half_day"]');
                        if (dayRadio && dayRadio.checked) {
                            halfDayCount++;
                        } else {
                            halfNightCount++;
                        }
                    } else if (fullCb && fullCb.checked) {
                        fullCount++;
                    }
                });

                const totalActive = fullCount + halfDayCount + halfNightCount;
                const chefCount = {{ (int)($todaySummary['chef_meal_count'] ?? 0) }};
                const fullRate = {{ (float)($todaySummary['special_rate'] ?? 30) }};
                const halfRate = {{ (float)($todaySummary['special_half_rate'] ?? (($todaySummary['special_rate'] ?? 30) / 2)) }};

                const morningRice = {{ (int)($mealSetting['morning_rice'] ?? 100) }};
                const dayRice     = {{ (int)($mealSetting['day_rice'] ?? 200) }};
                const nightRice   = {{ (int)($mealSetting['night_rice'] ?? 200) }};
                const fullRice    = morningRice + dayRice + nightRice;

                const memberRice = (fullCount * fullRice) + (halfDayCount * dayRice) + (halfNightCount * nightRice);
                const chefRice   = chefCount * fullRice;
                const totalRiceGm = memberRice + chefRice;

                const totalCost = (fullCount * fullRate) + ((halfDayCount + halfNightCount) * halfRate);

                const elMeal = document.getElementById('summary_total_full_meal');
                const elChef = document.getElementById('summary_chef_meal');
                const elChefWrapper = document.getElementById('summary_chef_meal_wrapper');
                const elRiceKg = document.getElementById('summary_rice_kg');
                const elRiceGm = document.getElementById('summary_rice_gm');
                const elCost = document.getElementById('summary_total_cost');

                if (elMeal) elMeal.innerText = totalActive;
                if (elChefWrapper && elChef) {
                    if (chefCount > 0) {
                        elChefWrapper.style.display = '';
                        elChef.innerText = '🧑‍🍳 Chef Meal: ' + chefCount;
                    } else {
                        elChefWrapper.style.display = 'none';
                    }
                }
                if (elRiceKg) elRiceKg.innerText = (totalRiceGm / 1000).toFixed(2);
                if (elRiceGm) elRiceGm.innerText = 'Total: ' + Number(totalRiceGm).toLocaleString() + ' gm';
                if (elCost) elCost.innerText = Number(totalCost).toFixed(2);

                // Update Single Resident Card if present
                const elResStatus = document.getElementById('resident_card_status_text');
                const elResRate = document.getElementById('resident_card_rate_display');
                const elResRateType = document.getElementById('resident_card_rate_type');

                if (elResStatus || elResRate) {
                    let myStatusHtml = '<span class="badge bg-label-danger text-danger fs-6 px-2.5 py-1 rounded-pill">OFF (মিল বন্ধ)</span>';
                    let myRateStr = '৳ 0.00';
                    let myRateType = 'মিল বন্ধ (No Cost)';

                    if (halfDayCount > 0) {
                        myStatusHtml = '<span class="badge bg-warning text-dark fs-6 px-2.5 py-1 rounded-pill">☀️ হাফ মিল (Day)</span>';
                        myRateStr = '৳ ' + Number(halfRate).toFixed(2);
                        myRateType = 'হাফ রেট (Half Rate)';
                    } else if (halfNightCount > 0) {
                        myStatusHtml = '<span class="badge bg-info text-white fs-6 px-2.5 py-1 rounded-pill">🌙 হাফ মিল (Night)</span>';
                        myRateStr = '৳ ' + Number(halfRate).toFixed(2);
                        myRateType = 'হাফ রেট (Half Rate)';
                    } else if (fullCount > 0) {
                        myStatusHtml = '<span class="badge bg-success text-white fs-6 px-2.5 py-1 rounded-pill">✅ ফুল মিল (Full ON)</span>';
                        myRateStr = '৳ ' + Number(fullRate).toFixed(2);
                        myRateType = 'ফুল রেট (Full Rate)';
                    }

                    if (elResStatus) elResStatus.innerHTML = myStatusHtml;
                    if (elResRate) elResRate.innerText = myRateStr;
                    if (elResRateType) {
                        const isSpecial = {{ $todaySummary['is_special'] ? 'true' : 'false' }};
                        if (isSpecial && (fullCount > 0 || halfDayCount > 0 || halfNightCount > 0)) {
                            elResRateType.innerText = '⭐ Special Meal (' + myRateType + ')';
                        } else {
                            elResRateType.innerText = myRateType;
                        }
                    }
                }
            }

            function sendMealUpdate(userId, mealType, startDate, totalDays) {
                const row = document.getElementById('row_user_' + userId);
                const fullCb = document.getElementById('full_' + userId);
                const halfCb = document.getElementById('half_' + userId);
                const halfOptions = document.getElementById('half_options_' + userId);

                let statusBadge = row ? row.querySelector('.meal-status-badge') : null;
                const nameDiv = row ? row.querySelector('.fw-semibold.text-heading') : null;
                if (!statusBadge && nameDiv) {
                    statusBadge = document.createElement('span');
                    statusBadge.className = 'badge ms-1 meal-status-badge';
                    statusBadge.style.fontSize = '0.65rem';
                    nameDiv.appendChild(statusBadge);
                }

                if (mealType === 'full') {
                    if (fullCb) fullCb.checked = true;
                    if (halfCb) halfCb.checked = false;
                    if (halfOptions) {
                        halfOptions.classList.add('d-none');
                        halfOptions.classList.remove('d-flex');
                    }
                    if (statusBadge) {
                        statusBadge.className = 'badge bg-success text-white ms-1 meal-status-badge';
                        statusBadge.innerHTML = '✅ মিল চালু (Meal ON)';
                    }
                } else if (mealType === 'half_day') {
                    if (fullCb) fullCb.checked = false;
                    if (halfCb) halfCb.checked = true;
                    if (halfOptions) {
                        halfOptions.classList.remove('d-none');
                        halfOptions.classList.add('d-flex');
                    }
                    const dayRadio = document.getElementById('half_day_' + userId);
                    if (dayRadio) dayRadio.checked = true;
                    if (statusBadge) {
                        statusBadge.className = 'badge bg-warning text-dark ms-1 meal-status-badge';
                        statusBadge.innerHTML = '☀️ হাফ মিল (Day)';
                    }
                } else if (mealType === 'half_night') {
                    if (fullCb) fullCb.checked = false;
                    if (halfCb) halfCb.checked = true;
                    if (halfOptions) {
                        halfOptions.classList.remove('d-none');
                        halfOptions.classList.add('d-flex');
                    }
                    const nightRadio = document.getElementById('half_night_' + userId);
                    if (nightRadio) nightRadio.checked = true;
                    if (statusBadge) {
                        statusBadge.className = 'badge bg-info text-white ms-1 meal-status-badge';
                        statusBadge.innerHTML = '🌙 হাফ মিল (Night)';
                    }
                } else if (mealType === 'off') {
                    if (fullCb) fullCb.checked = false;
                    if (halfCb) halfCb.checked = false;
                    if (halfOptions) {
                        halfOptions.classList.add('d-none');
                        halfOptions.classList.remove('d-flex');
                    }
                    if (statusBadge) {
                        const prevHtml = statusBadge.innerHTML || '';
                        if (prevHtml.includes('চেক-ইন')) {
                            statusBadge.className = 'badge bg-success text-white ms-1 meal-status-badge';
                            statusBadge.innerHTML = '🟢 চেক-ইন (আগামীকাল থেকে মিল চালু)';
                        } else if (prevHtml.includes('চেক-আউট') || prevHtml.includes('চেক')) {
                            statusBadge.className = 'badge bg-danger text-white ms-1 meal-status-badge';
                            statusBadge.innerHTML = '🚫 চেক-আউট (মিল বন্ধ)';
                        } else {
                            statusBadge.className = 'badge bg-danger text-white ms-1 meal-status-badge';
                            statusBadge.innerHTML = '🚫 মিল বন্ধ (Meal OFF)';
                        }
                    }
                }

                // Update row quick button state if present
                const quickBtn = document.getElementById('quick_btn_' + userId);
                if (quickBtn) {
                    if (mealType === 'off') {
                        quickBtn.className = 'btn btn-sm btn-outline-success fw-bold px-2 py-1 row-quick-toggle-btn shadow-sm';
                        quickBtn.setAttribute('data-action', 'turn_on');
                        quickBtn.innerHTML = '<i class="ti ti-check me-1"></i>চালু করুন';
                    } else {
                        quickBtn.className = 'btn btn-sm btn-outline-danger fw-bold px-2 py-1 row-quick-toggle-btn shadow-sm';
                        quickBtn.setAttribute('data-action', 'turn_off');
                        quickBtn.innerHTML = '<i class="ti ti-ban me-1"></i>বন্ধ করুন';
                    }
                }

                recalculateSummaryLive();
                if (currentFilter !== 'all') {
                    filterAndPaginateTable();
                }

                fetch(updateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        user_id: userId,
                        date: startDate || "{{ now()->format('Y-m-d') }}",
                        total_days: totalDays || 1,
                        meal_type: mealType
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');

                        if (data.today_summary) {
                            const elMeal = document.getElementById('summary_total_full_meal');
                            const elChef = document.getElementById('summary_chef_meal');
                            const elChefWrapper = document.getElementById('summary_chef_meal_wrapper');
                            const elRiceKg = document.getElementById('summary_rice_kg');
                            const elRiceGm = document.getElementById('summary_rice_gm');
                            const elCost = document.getElementById('summary_total_cost');

                            if (elMeal && (data.today_summary.total_active_meal !== undefined || data.today_summary.total_full_meal !== undefined)) {
                                elMeal.innerText = data.today_summary.total_active_meal ?? data.today_summary.total_full_meal;
                            }
                            if (elChefWrapper && elChef) {
                                if (data.today_summary.chef_meal_count > 0) {
                                    elChefWrapper.style.display = '';
                                    elChef.innerText = '🧑‍🍳 Chef Meal: ' + data.today_summary.chef_meal_count;
                                } else {
                                    elChefWrapper.style.display = 'none';
                                }
                            }
                            if (elRiceKg && data.today_summary.grand_total_rice !== undefined) {
                                elRiceKg.innerText = (data.today_summary.grand_total_rice / 1000).toFixed(2);
                            }
                            if (elRiceGm && data.today_summary.grand_total_rice !== undefined) {
                                elRiceGm.innerText = 'Total: ' + Number(data.today_summary.grand_total_rice).toLocaleString() + ' gm';
                            }
                            if (elCost && data.today_summary.grand_total_cost !== undefined) {
                                elCost.innerText = Number(data.today_summary.grand_total_cost).toFixed(2);
                            }
                        }
                    } else {
                        showAlert(data.message || 'মিলের স্ট্যাটাস আপডেট করা সম্ভব হয়নি।', 'danger');
                        recalculateSummaryLive();
                    }
                })
                .catch(err => {
                    showAlert('নেটওয়ার্ক সমস্যার কারণে মিল আপডেট হয়নি।', 'danger');
                });
            }

            // Single User Quick Action Button Click (চালু করুন / বন্ধ করুন)
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.row-quick-toggle-btn');
                if (!btn) return;
                const userId = btn.getAttribute('data-userid');
                const action = btn.getAttribute('data-action');
                if (action === 'turn_off') {
                    sendMealUpdate(userId, 'off', "{{ now()->format('Y-m-d') }}", 1);
                } else {
                    sendMealUpdate(userId, 'full', "{{ now()->format('Y-m-d') }}", 1);
                }
            });

            // 1. FULL MEAL Toggle Handler
            document.querySelectorAll('.meal-full-toggle').forEach(function (element) {
                element.addEventListener('change', function () {
                    const userId = this.getAttribute('data-userid');
                    if (this.checked) {
                        sendMealUpdate(userId, 'full', "{{ now()->format('Y-m-d') }}", 1);
                    } else {
                        sendMealUpdate(userId, 'off', "{{ now()->format('Y-m-d') }}", 1);
                    }
                });
            });

            // 2. HALF MEAL Toggle Handler
            document.querySelectorAll('.meal-half-toggle').forEach(function (element) {
                element.addEventListener('change', function () {
                    const userId = this.getAttribute('data-userid');
                    if (this.checked) {
                        const dayRadio = document.getElementById('half_day_' + userId);
                        const nightRadio = document.getElementById('half_night_' + userId);
                        let mealType = 'half_day';
                        if (nightRadio && nightRadio.checked) {
                            mealType = 'half_night';
                        } else if (dayRadio && dayRadio.checked) {
                            mealType = 'half_day';
                        }
                        sendMealUpdate(userId, mealType, "{{ now()->format('Y-m-d') }}", 1);
                    } else {
                        sendMealUpdate(userId, 'off', "{{ now()->format('Y-m-d') }}", 1);
                    }
                });
            });

            // 3. HALF MEAL Day / Night Radio Handler
            document.querySelectorAll('.meal-time-radio').forEach(function (radio) {
                radio.addEventListener('change', function () {
                    const userId = this.getAttribute('data-userid');
                    const halfCb = document.getElementById('half_' + userId);
                    if (halfCb && halfCb.checked) {
                        sendMealUpdate(userId, this.value, "{{ now()->format('Y-m-d') }}", 1);
                    }
                });
            });

            const bulkUpdateUrl = "{{ route('meals.bulk-update-status') }}";

            // 4. Admin Bulk Action Buttons (এক ক্লিকে সবার মিল চালু বা বন্ধ)
            document.querySelectorAll('.bulk-meal-btn').forEach(function (button) {
                button.addEventListener('click', function () {
                    const action = this.getAttribute('data-action');
                    let confirmTitle = '';
                    let confirmText = '';
                    let btnConfirmColor = '#696cff';

                    if (action === 'all_full') {
                        confirmTitle = 'সবার মিল ফুল চালু করতে চান?';
                        confirmText = 'সকল অ্যাক্টিভ মেম্বারের মিল আজকের জন্য ফুল চালু (ON) করা হবে।';
                        btnConfirmColor = '#28a745';
                    } else if (action === 'all_off') {
                        confirmTitle = 'সবার মিল বন্ধ করতে চান?';
                        confirmText = 'সকল মেম্বারের মিল আজকের জন্য বন্ধ (OFF) করা হবে।';
                        btnConfirmColor = '#dc3545';
                    } else if (action === 'all_half_day') {
                        confirmTitle = 'সবার মিল ডে হাফ চালু করতে চান?';
                        confirmText = 'সকল অ্যাক্টিভ মেম্বারের মিল আজকের জন্য ডে হাফ (Day Half) চালু করা হবে।';
                        btnConfirmColor = '#ffc107';
                    } else if (action === 'all_half_night') {
                        confirmTitle = 'সবার মিল নাইট হাফ চালু করতে চান?';
                        confirmText = 'সকল অ্যাক্টিভ মেম্বারের মিল আজকের জন্য নাইট হাফ (Night Half) চালু করা হবে।';
                        btnConfirmColor = '#17a2b8';
                    }

                    const executeBulkUpdate = function () {
                        // Apply instant visual update across all rows
                        document.querySelectorAll('tr[id^="row_user_"]').forEach(function (row) {
                            const fullCb = row.querySelector('.meal-full-toggle');
                            const halfCb = row.querySelector('.meal-half-toggle');
                            const dayRadio = row.querySelector('input[value="half_day"]');
                            const nightRadio = row.querySelector('input[value="half_night"]');
                            const halfOptions = row.querySelector('.half-options-card');
                            let statusBadge = row.querySelector('.meal-status-badge');
                            const nameDiv = row.querySelector('.fw-semibold.text-heading');
                            const rowQuickBtn = row.querySelector('.row-quick-toggle-btn');

                            // Skip if row is disabled/locked for zero deposit
                            if (fullCb && fullCb.disabled) {
                                return;
                            }

                            if (!statusBadge && nameDiv) {
                                statusBadge = document.createElement('span');
                                statusBadge.className = 'badge ms-1 meal-status-badge';
                                statusBadge.style.fontSize = '0.65rem';
                                nameDiv.appendChild(statusBadge);
                            }

                            if (action === 'all_full') {
                                if (fullCb) fullCb.checked = true;
                                if (halfCb) halfCb.checked = false;
                                if (halfOptions) {
                                    halfOptions.classList.add('d-none');
                                    halfOptions.classList.remove('d-flex');
                                }
                                if (statusBadge) {
                                    statusBadge.className = 'badge bg-success text-white ms-1 meal-status-badge';
                                    statusBadge.innerHTML = '✅ মিল চালু (Meal ON)';
                                }
                                if (rowQuickBtn) {
                                    rowQuickBtn.className = 'btn btn-sm btn-outline-danger fw-bold px-2 py-1 row-quick-toggle-btn shadow-sm';
                                    rowQuickBtn.setAttribute('data-action', 'turn_off');
                                    rowQuickBtn.innerHTML = '<i class="ti ti-ban me-1"></i>বন্ধ করুন';
                                }
                            } else if (action === 'all_off') {
                                if (fullCb) fullCb.checked = false;
                                if (halfCb) halfCb.checked = false;
                                if (halfOptions) {
                                    halfOptions.classList.add('d-none');
                                    halfOptions.classList.remove('d-flex');
                                }
                                if (statusBadge) {
                                    const prevHtml = statusBadge.innerHTML || '';
                                    if (prevHtml.includes('চেক-ইন')) {
                                        statusBadge.className = 'badge bg-success text-white ms-1 meal-status-badge';
                                        statusBadge.innerHTML = '🟢 চেক-ইন (আগামীকাল থেকে মিল চালু)';
                                    } else if (prevHtml.includes('চেক-আউট') || prevHtml.includes('চেক')) {
                                        statusBadge.className = 'badge bg-danger text-white ms-1 meal-status-badge';
                                        statusBadge.innerHTML = '🚫 চেক-আউট (মিল বন্ধ)';
                                    } else {
                                        statusBadge.className = 'badge bg-danger text-white ms-1 meal-status-badge';
                                        statusBadge.innerHTML = '🚫 মিল বন্ধ (Meal OFF)';
                                    }
                                }
                                if (rowQuickBtn) {
                                    rowQuickBtn.className = 'btn btn-sm btn-outline-success fw-bold px-2 py-1 row-quick-toggle-btn shadow-sm';
                                    rowQuickBtn.setAttribute('data-action', 'turn_on');
                                    rowQuickBtn.innerHTML = '<i class="ti ti-check me-1"></i>চালু করুন';
                                }
                            } else if (action === 'all_half_day') {
                                if (fullCb) fullCb.checked = false;
                                if (halfCb) halfCb.checked = true;
                                if (dayRadio) dayRadio.checked = true;
                                if (halfOptions) {
                                    halfOptions.classList.remove('d-none');
                                    halfOptions.classList.add('d-flex');
                                }
                                if (statusBadge) {
                                    statusBadge.className = 'badge bg-warning text-dark ms-1 meal-status-badge';
                                    statusBadge.innerHTML = '☀️ হাফ মিল (Day)';
                                }
                                if (rowQuickBtn) {
                                    rowQuickBtn.className = 'btn btn-sm btn-outline-danger fw-bold px-2 py-1 row-quick-toggle-btn shadow-sm';
                                    rowQuickBtn.setAttribute('data-action', 'turn_off');
                                    rowQuickBtn.innerHTML = '<i class="ti ti-ban me-1"></i>বন্ধ করুন';
                                }
                            } else if (action === 'all_half_night') {
                                if (fullCb) fullCb.checked = false;
                                if (halfCb) halfCb.checked = true;
                                if (nightRadio) nightRadio.checked = true;
                                if (halfOptions) {
                                    halfOptions.classList.remove('d-none');
                                    halfOptions.classList.add('d-flex');
                                }
                                if (statusBadge) {
                                    statusBadge.className = 'badge bg-info text-white ms-1 meal-status-badge';
                                    statusBadge.innerHTML = '🌙 হাফ মিল (Night)';
                                }
                                if (rowQuickBtn) {
                                    rowQuickBtn.className = 'btn btn-sm btn-outline-danger fw-bold px-2 py-1 row-quick-toggle-btn shadow-sm';
                                    rowQuickBtn.setAttribute('data-action', 'turn_off');
                                    rowQuickBtn.innerHTML = '<i class="ti ti-ban me-1"></i>বন্ধ করুন';
                                }
                            }
                        });

                        recalculateSummaryLive();
                        if (currentFilter !== 'all') {
                            filterAndPaginateTable();
                        }

                        // Send Bulk Update to Server
                        fetch(bulkUpdateUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                action: action,
                                date: "{{ now()->format('Y-m-d') }}"
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showAlert(data.message, 'success');
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'সফল!',
                                        text: data.message,
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                }
                                if (data.today_summary) {
                                    const elMeal = document.getElementById('summary_total_full_meal');
                                    const elChef = document.getElementById('summary_chef_meal');
                                    const elChefWrapper = document.getElementById('summary_chef_meal_wrapper');
                                    const elRiceKg = document.getElementById('summary_rice_kg');
                                    const elRiceGm = document.getElementById('summary_rice_gm');
                                    const elCost = document.getElementById('summary_total_cost');

                                    if (elMeal) elMeal.innerText = data.today_summary.total_full_meal;
                                    if (elRiceKg) elRiceKg.innerText = (data.today_summary.grand_total_rice / 1000).toFixed(2);
                                    if (elRiceGm) elRiceGm.innerText = 'Total: ' + Number(data.today_summary.grand_total_rice).toLocaleString() + ' gm';
                                    if (elCost) elCost.innerText = Number(data.today_summary.grand_total_cost).toFixed(2);
                                }
                            } else {
                                showAlert(data.message || 'এককালীন মিল আপডেট করা সম্ভব হয়নি।', 'danger');
                            }
                        })
                        .catch(err => {
                            showAlert('নেটওয়ার্ক সমস্যার কারণে এককালীন মিল আপডেট সম্পন্ন হয়নি।', 'danger');
                        });
                    };

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: confirmTitle,
                            text: confirmText,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: btnConfirmColor,
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'হ্যাঁ, নিশ্চিত করুন',
                            cancelButtonText: 'বাতিল'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                executeBulkUpdate();
                            }
                        });
                    } else {
                        if (confirm(confirmTitle + '\n' + confirmText)) {
                            executeBulkUpdate();
                        }
                    }
                });
            });

            const confirmBtn = document.getElementById('confirmMealOffBtn');
            if (confirmBtn) {
                confirmBtn.addEventListener('click', function () {
                    const userId = document.getElementById('modal_target_user_id').value;
                    const optionVal = document.getElementById('modal_meal_option').value;

                    let sendType = 'off';
                    let sendStartDate = "{{ now()->addDay()->format('Y-m-d') }}";
                    let sendTotalDays = 1;

                    if (optionVal.startsWith('days_')) {
                        sendType = 'off';
                        sendStartDate = "{{ now()->addDay()->format('Y-m-d') }}";
                        sendTotalDays = parseInt(optionVal.replace('days_', '')) || 1;
                    }

                    if (mealOffModalObj) {
                        mealOffModalObj.hide();
                    }
                    sendMealUpdate(userId, sendType, sendStartDate, sendTotalDays);
                });
            }
        });
    </script>
@endpush
