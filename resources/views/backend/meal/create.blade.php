@extends('backend.layouts.app')

@section("title")
    | {{$page_title}}
@endsection

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

                        <!-- Meal Cutoff Schedule Banner -->
                        <div class="alert bg-label-secondary border d-flex align-items-center justify-content-between flex-wrap gap-2 p-3 mb-4 shadow-sm" role="alert">
                            <div class="d-flex align-items-center">
                                <span class="alert-icon text-primary me-2 fs-4">
                                    <i class="fa fa-clock"></i>
                                </span>
                                <div>
                                    <strong class="text-dark">আজকের মিল পরিবর্তন/অফের সময়সূচি (Cutoff Schedule):</strong>
                                    <div class="small text-secondary mt-1">
                                        ☀️ <strong>লাঞ্চ কাট-অফ:</strong> সকাল ১০:০০ টা পর্যন্ত &nbsp;|&nbsp;
                                        🌙 <strong>ডিনার ও মিল অফ কাট-অফ:</strong> বিকাল ০৪:০০ টা পর্যন্ত
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                @if(isset($isLunchCutoffPassed) && $isLunchCutoffPassed)
                                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fs-7 shadow-sm">
                                        ⏰ লাঞ্চ সময় (১০:০০ টা) সমাপ্ত
                                    </span>
                                @endif
                                @if(isset($isDinnerCutoffPassed) && $isDinnerCutoffPassed)
                                    <span class="badge bg-danger text-white px-3 py-2 rounded-pill fs-7 shadow-sm">
                                        🚫 ডিনার সময় (০৪:০০ টা) সমাপ্ত
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Summary Cards -->
                        <div class="row g-4 mb-5">
                            <div class="col-md-6 col-xl-3">
                                <div class="card bg-label-info border-0 shadow-none h-100">
                                    <div class="card-body text-center p-4">
                                        <div class="avatar mx-auto mb-2">
                                            <span class="avatar-initial rounded-circle bg-info text-white fs-4">☀️</span>
                                        </div>
                                        <h6 class="mb-1 text-info fw-bold">Morning Shift</h6>
                                        <h3 class="mb-1 fw-bold text-dark">{{ $todaySummary['stat_morning_meal'] }} <span class="fs-6 text-muted">Meals</span></h3>
                                        <div class="border-top pt-2 mt-2">
                                            <small class="d-block text-dark fw-semibold">Rice: {{ number_format($todaySummary['stat_morning_rice']) }} gm</small>
                                            @if($todaySummary['chef_meal_count'] > 0)
                                                <span class="text-xs text-muted d-block mt-1">
                                                    M: {{ $todaySummary['morning_member_rice'] }}g | C: {{ $todaySummary['morning_chef_rice'] }}g
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-xl-3">
                                <div class="card bg-label-primary border-0 shadow-none h-100">
                                    <div class="card-body text-center p-4">
                                        <div class="avatar mx-auto mb-2">
                                            <span class="avatar-initial rounded-circle bg-primary text-white fs-4">🌤️</span>
                                        </div>
                                        <h6 class="mb-1 text-primary fw-bold">Day Shift</h6>
                                        <h3 class="mb-1 fw-bold text-dark">{{ $todaySummary['stat_day_meal'] }} <span class="fs-6 text-muted">Meals</span></h3>
                                        <div class="border-top pt-2 mt-2">
                                            <small class="d-block text-dark fw-semibold">Rice: {{ number_format($todaySummary['stat_day_rice']) }} gm</small>
                                            @if($todaySummary['chef_meal_count'] > 0)
                                                <span class="text-xs text-muted d-block mt-1">
                                                    M: {{ $todaySummary['day_member_rice'] }}g | C: {{ $todaySummary['day_chef_rice'] }}g
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-xl-3">
                                <div class="card bg-label-success border-0 shadow-none h-100">
                                    <div class="card-body text-center p-4">
                                        <div class="avatar mx-auto mb-2">
                                            <span class="avatar-initial rounded-circle bg-success text-white fs-4">🌙</span>
                                        </div>
                                        <h6 class="mb-1 text-success fw-bold">Night Shift</h6>
                                        <h3 class="mb-1 fw-bold text-dark">{{ $todaySummary['stat_night_meal'] }} <span class="fs-6 text-muted">Meals</span></h3>
                                        <div class="border-top pt-2 mt-2">
                                            <small class="d-block text-dark fw-semibold">Rice: {{ number_format($todaySummary['stat_night_rice']) }} gm</small>
                                            @if($todaySummary['chef_meal_count'] > 0)
                                                <span class="text-xs text-muted d-block mt-1">
                                                    M: {{ $todaySummary['night_member_rice'] }}g | C: {{ $todaySummary['night_chef_rice'] }}g
                                                </span>
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
                                        <h6 class="mb-1 text-danger fw-bold">Grand Total</h6>
                                        <h3 class="mb-1 fw-bold text-danger">{{ number_format($todaySummary['grand_total_cost'], 2) }} Tk</h3>
                                        <div class="border-top pt-2 mt-2">
                                            <small class="d-block text-dark fw-bold">Total Rice: {{ number_format($todaySummary['grand_total_rice']) }} gm</small>
                                            <span class="badge bg-danger text-white mt-1 mb-1 px-2 py-0">
                                                {{ number_format($todaySummary['grand_total_rice'] / 1000, 2) }} kg
                                            </span>
                                            @if($todaySummary['chef_meal_count'] > 0)
                                                <div class="d-flex justify-content-center gap-2 text-xs text-muted pt-1 border-top border-light">
                                                    <span>Member: {{ number_format($todaySummary['grand_total_member_rice']) }}g</span>
                                                    <span>|</span>
                                                    <span class="text-warning fw-semibold">Chef: {{ number_format($todaySummary['grand_total_chef_rice']) }}g</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Original Meal Table Layout with Instant Auto-Save & Dynamic Red/Blue Status -->
                        <div class="table-responsive border rounded">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                <tr>
                                    <th style="width: 70px;" class="fw-bold text-secondary text-uppercase">SL</th>
                                    <th class="fw-bold text-secondary text-uppercase">Member Name</th>
                                    <th class="fw-bold text-secondary text-uppercase">Phone</th>
                                    <th style="width: 160px;" class="text-center fw-bold text-danger text-uppercase">MEAL OFF (মিল বন্ধ)</th>
                                    <th style="width: 260px;" class="text-center fw-bold text-secondary text-uppercase">HALF MEAL OPTION</th>
                                    <th style="width: 160px;" class="text-center fw-bold text-primary text-uppercase">FULL MEAL</th>
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
                                        $isFull = $meal ? (bool)$meal->full_meal : false;
                                        $isHalf = $meal ? (bool)$meal->half_meal : false;
                                        $note   = $meal ? $meal->note : '';

                                        $pendingReq = \App\Models\Backend\MealRequest::where('user_id', $user->id)
                                            ->where('status', 0)
                                            ->latest()
                                            ->first();

                                        $isRowLocked = $isOff || $isHalf || !$canSaveMeal || (isset($isLunchCutoffPassed) && $isLunchCutoffPassed);
                                    @endphp

                                    <tr id="row_user_{{ $user->id }}" class="{{ $isRowLocked ? 'opacity-75' : '' }}">
                                        <td><span class="text-muted font-monospace">{{ str_pad($key + 1, 2, '0', STR_PAD_LEFT) }}</span></td>
                                        <td>
                                            <div class="fw-semibold text-heading d-flex align-items-center flex-wrap gap-1">
                                                {{ $user->name }}
                                                @if($pendingReq)
                                                    <span class="badge bg-warning text-dark ms-1" style="font-size: 0.65rem;" title="এডমিনের অনুমোদনের জন্য অপেক্ষমাণ">
                                                        ⏳ Pending Approval
                                                    </span>
                                                @elseif($isOff)
                                                    <span class="badge bg-danger text-white ms-1" style="font-size: 0.65rem;">
                                                        🚫 Full Meal OFF
                                                    </span>
                                                @elseif($isHalf && $note == 'night')
                                                    <span class="badge bg-label-danger text-danger ms-1" style="font-size: 0.65rem;">
                                                        ☀️ দুপুরের মিল বন্ধ
                                                    </span>
                                                @elseif($isHalf && $note == 'day')
                                                    <span class="badge bg-label-info text-info ms-1" style="font-size: 0.65rem;">
                                                        🌙 রাতের মিল বন্ধ
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td><small class="text-secondary">{{ $user->phone ?? '-' }}</small></td>

                                        <!-- MEAL OFF (Red Switch) -->
                                        <td class="text-center cell-off {{ $isOff ? 'bg-light-danger' : '' }} {{ $isRowLocked ? 'pe-none opacity-50' : '' }}">
                                            <div class="form-check form-switch d-flex justify-content-center mb-0">
                                                <input type="checkbox" id="off_{{ $user->id }}"
                                                       class="form-check-input bg-danger border-danger setup-checkbox-md meal-action-toggle cursor-pointer"
                                                       data-userid="{{ $user->id }}" data-mealtype="off"
                                                       {{ $isOff ? 'checked' : '' }} {{ $isRowLocked ? 'disabled' : '' }}>
                                                <label class="form-check-label ms-2 text-danger fw-bold cursor-pointer" id="off_label_{{ $user->id }}" for="off_{{ $user->id }}">
                                                    {{ $isOff ? 'FULL OFF' : 'OFF' }}
                                                </label>
                                            </div>
                                        </td>

                                        <!-- HALF MEAL OPTION -->
                                        <td class="text-center cell-half {{ $isRowLocked ? 'pe-none opacity-50' : '' }}">
                                            <div class="form-check d-flex justify-content-center mb-0">
                                                <input type="checkbox" id="half_{{ $user->id }}"
                                                       class="form-check-input border-info setup-checkbox-md meal-action-toggle cursor-pointer"
                                                       data-userid="{{ $user->id }}" data-mealtype="half"
                                                       {{ ($isHalf && !$isOff) ? 'checked' : '' }} {{ $isRowLocked ? 'disabled' : '' }}>
                                                <label class="form-check-label ms-2 text-info fw-bold cursor-pointer" for="half_{{ $user->id }}">Half</label>
                                            </div>

                                            <div id="session_container_{{ $user->id }}"
                                                 class="mt-2 p-2 bg-white border rounded shadow-sm justify-content-center gap-3"
                                                 style="display: {{ ($isHalf && !$isOff) ? 'flex' : 'none' }}; max-width: 220px; margin: 0 auto;">
                                                 <div class="form-check form-check-inline mb-0 {{ $isRowLocked ? 'pe-none opacity-50' : '' }}">
                                                     <input class="form-check-input meal-action-toggle" type="radio"
                                                            name="half_note_{{ $user->id }}" id="day_{{ $user->id }}"
                                                            data-userid="{{ $user->id }}" data-mealtype="half_day"
                                                            value="day" {{ ($isHalf && $note == 'day' && !$isOff) ? 'checked' : '' }} {{ $isRowLocked ? 'disabled' : '' }}>
                                                     <label class="form-check-label text-xs fw-bold cursor-pointer" for="day_{{ $user->id }}">☀️ Day</label>
                                                 </div>
                                                 <div class="form-check form-check-inline mb-0 {{ $isRowLocked ? 'pe-none opacity-50' : '' }}">
                                                     <input class="form-check-input meal-action-toggle" type="radio"
                                                            name="half_note_{{ $user->id }}" id="night_{{ $user->id }}"
                                                            data-userid="{{ $user->id }}" data-mealtype="half_night"
                                                            value="night" {{ ($isHalf && $note == 'night' && !$isOff) ? 'checked' : '' }} {{ $isRowLocked ? 'disabled' : '' }}>
                                                     <label class="form-check-label text-xs fw-bold cursor-pointer" for="night_{{ $user->id }}">🌙 Night</label>
                                                 </div>
                                            </div>
                                        </td>

                                        <!-- FULL MEAL (Blue Checkbox) -->
                                        <td class="text-center cell-full {{ $isRowLocked ? 'pe-none opacity-50' : '' }}">
                                            <div class="form-check d-flex justify-content-center mb-0">
                                                <input type="checkbox" id="full_{{ $user->id }}"
                                                       class="form-check-input bg-primary border-primary setup-checkbox-md meal-action-toggle cursor-pointer"
                                                       data-userid="{{ $user->id }}" data-mealtype="full"
                                                       {{ ($isFull && !$isOff) ? 'checked' : '' }} {{ $isRowLocked ? 'disabled' : '' }}>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
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
                        <label class="form-label fw-bold text-dark fs-6">কোন্ ধরনের বা কত দিনের মিল বন্ধ রাখতে চান?</label>
                        <select id="modal_meal_option" class="form-select fw-bold border-danger text-danger py-2">
                            <option value="today_day">☀️ আজকের দুপুরের মিল বন্ধ (Today Day Shift OFF)</option>
                            <option value="today_night">🌙 আজকের রাতের মিল বন্ধ (Today Night Shift OFF)</option>
                            <option value="days_1" selected>🚫 আগামীকাল ১ দিন মিল বন্ধ (Tomorrow 1 Day OFF)</option>
                            <option value="days_2">🚫 আগামীকাল থেকে ২ দিন মিল বন্ধ (Tomorrow 2 Days OFF)</option>
                            <option value="days_3">🚫 আগামীকাল থেকে ৩ দিন মিল বন্ধ (Tomorrow 3 Days OFF)</option>
                            <option value="days_5">🚫 আগামীকাল থেকে ৫ দিন মিল বন্ধ (Tomorrow 5 Days OFF)</option>
                            <option value="days_7">🚫 আগামীকাল থেকে ৭ দিন / ১ সপ্তাহ (Tomorrow 7 Days OFF)</option>
                            <option value="days_10">🚫 আগামীকাল থেকে ১০ দিন মিল বন্ধ (Tomorrow 10 Days OFF)</option>
                            <option value="days_15">🚫 আগামীকাল থেকে ১৫ দিন মিল বন্ধ (Tomorrow 15 Days OFF)</option>
                            <option value="days_30">🚫 আগামীকাল থেকে ১ মাস / ৩০ দিন (Tomorrow 30 Days OFF)</option>
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
            const csrfToken = "{{ csrf_token() }}";
            let mealOffModalObj = null;

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
                const todayFormatted = todayObj.toLocaleDateString('en-GB', options);

                if (optionVal === 'today_day') {
                    summaryBox.innerHTML = `☀️ <strong>মিলের হিসাব</strong>: আজকের (<strong>${todayFormatted}</strong>) <strong>দুপুরের মিল বন্ধ</strong> রাখার অনুরোধ এডমিনের কাছে পাঠানো হবে (রাতের মিল চালু থাকবে)।`;
                } else if (optionVal === 'today_night') {
                    summaryBox.innerHTML = `🌙 <strong>মিলের হিসাব</strong>: আজকের (<strong>${todayFormatted}</strong>) <strong>রাতের মিল বন্ধ</strong> রাখার অনুরোধ এডমিনের কাছে পাঠানো হবে (দুপুরের মিল চালু থাকবে)।`;
                } else if (optionVal.startsWith('days_')) {
                    const totalDays = parseInt(optionVal.replace('days_', '')) || 1;
                    const endDateObj = new Date(tomorrowObj);
                    endDateObj.setDate(tomorrowObj.getDate() + (totalDays - 1));

                    const startFormatted = tomorrowObj.toLocaleDateString('en-GB', options);
                    const endFormatted = endDateObj.toLocaleDateString('en-GB', options);

                    if (totalDays === 1) {
                        summaryBox.innerHTML = `🗓️ <strong>মিলের হিসাব</strong>: আগামীকাল <strong>${startFormatted}</strong> (১ দিন) আপনার পুরো দিনের মিল বন্ধ রাখার অনুরোধ এডমিনের কাছে পাঠানো হবে।`;
                    } else {
                        summaryBox.innerHTML = `🗓️ <strong>মিলের হিসাব</strong>: আগামীকাল <strong>${startFormatted}</strong> হতে <strong>${endFormatted}</strong> পর্যন্ত (মোট <strong>${totalDays} দিন</strong>) আপনার পুরো দিনের মিল বন্ধ রাখার অনুরোধ এডমিনের কাছে পাঠানো হবে।`;
                    }
                }
            }

            document.getElementById('modal_meal_option').addEventListener('change', updateModalSummary);

            function showPendingAlert(message) {
                const container = document.getElementById('ajaxAlertContainer');
                if (!container) return;
                container.innerHTML = `
                    <div class="alert alert-success alert-dismissible d-flex align-items-center justify-content-between p-3 mb-4 shadow-sm border-success rounded-3" role="alert">
                        <div class="d-flex align-items-center">
                            <span class="alert-icon text-success me-3 fs-3">
                                <i class="fa fa-check-circle"></i>
                            </span>
                            <div>
                                <h6 class="alert-heading mb-1 text-success fw-bold fs-6">📌 মিল অনুরোধ সফলভাবে পাঠানো হয়েছে</h6>
                                <div class="fw-medium text-dark fs-7">${message}</div>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                container.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            function showConfirmedAlert(title, message, status) {
                const container = document.getElementById('ajaxAlertContainer');
                if (!container) return;
                const alertType = status == 1 ? 'success' : 'danger';
                const icon = status == 1 ? 'check-circle' : 'times-circle';

                container.innerHTML = `
                    <div class="alert alert-${alertType} alert-dismissible d-flex align-items-center justify-content-between p-3 mb-4 shadow-sm border-${alertType} rounded-3" role="alert">
                        <div class="d-flex align-items-center">
                            <span class="alert-icon text-${alertType} me-3 fs-3">
                                <i class="fa fa-${icon}"></i>
                            </span>
                            <div>
                                <h6 class="alert-heading mb-1 text-${alertType} fw-bold fs-6">${title}</h6>
                                <div class="fw-medium text-dark fs-7">${message}</div>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                container.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            function sendMealUpdate(userId, mealType, startDate, totalDays) {
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
                        if (data.is_pending) {
                            showPendingAlert(data.message);

                            // Dynamically show pending badge on the user's row live without reload!
                            const userRow = document.getElementById('row_user_' + userId);
                            if (userRow) {
                                let badgeSpan = userRow.querySelector('.pending-badge-span');
                                if (!badgeSpan) {
                                    const nameDiv = userRow.querySelector('.fw-semibold.text-heading');
                                    if (nameDiv) {
                                        badgeSpan = document.createElement('span');
                                        badgeSpan.className = 'badge bg-warning text-dark ms-1 pending-badge-span';
                                        badgeSpan.style.fontSize = '0.65rem';
                                        badgeSpan.title = 'এডমিনের অনুমোদনের জন্য অপেক্ষমাণ';
                                        badgeSpan.innerHTML = '⏳ Pending Approval';
                                        nameDiv.appendChild(badgeSpan);
                                    }
                                }
                            }
                            return;
                        }
                        showAlert(data.message, 'success');

                        const offCheckbox = document.getElementById('off_' + userId);
                        const halfCheckbox = document.getElementById('half_' + userId);
                        const fullCheckbox = document.getElementById('full_' + userId);
                        const sessionContainer = document.getElementById('session_container_' + userId);

                        if (mealType === 'off') {
                            if (offCheckbox) offCheckbox.checked = true;
                            if (halfCheckbox) { halfCheckbox.checked = false; halfCheckbox.disabled = true; }
                            if (fullCheckbox) { fullCheckbox.checked = false; fullCheckbox.disabled = true; }
                            if (sessionContainer) sessionContainer.style.setProperty('display', 'none', 'important');
                        } else if (mealType === 'full') {
                            if (offCheckbox) offCheckbox.checked = false;
                            if (halfCheckbox) { halfCheckbox.checked = false; halfCheckbox.disabled = false; }
                            if (fullCheckbox) { fullCheckbox.checked = true; fullCheckbox.disabled = false; }
                            if (sessionContainer) sessionContainer.style.setProperty('display', 'none', 'important');
                        } else if (mealType === 'half_day' || mealType === 'half_night') {
                            if (offCheckbox) offCheckbox.checked = false;
                            if (halfCheckbox) { halfCheckbox.checked = true; halfCheckbox.disabled = false; }
                            if (fullCheckbox) { fullCheckbox.checked = false; fullCheckbox.disabled = false; }
                            if (sessionContainer) sessionContainer.style.setProperty('display', 'flex', 'important');
                        }
                    } else {
                        showAlert(data.message || 'মিলের স্ট্যাটাস আপডেট করা সম্ভব হয়নি।', 'danger');
                    }
                })
                .catch(err => {
                    showAlert('নেটওয়ার্ক সমস্যার কারণে মিল আপডেট হয়নি।', 'danger');
                });
            }

            // Auto poll for admin confirmations live every 5 seconds without page reload!
            function checkUserNotifsLive() {
                fetch("{{ route('meal-requests.check-user-notifs') }}", {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.has_notifs && data.notifications) {
                        data.notifications.forEach(notif => {
                            showConfirmedAlert(notif.title, notif.message, notif.status);

                            // Dynamically update pending badges to approved/rejected live!
                            document.querySelectorAll('.pending-badge-span').forEach(el => {
                                if (notif.status == 1) {
                                    el.className = 'badge bg-success text-white ms-1 pending-badge-span';
                                    el.innerHTML = '✅ Approved';
                                } else {
                                    el.className = 'badge bg-danger text-white ms-1 pending-badge-span';
                                    el.innerHTML = '❌ Rejected';
                                }
                            });
                        });
                    }
                })
                .catch(err => console.log('Live user notif polling skipped', err));
            }
            setInterval(checkUserNotifsLive, 5000);

            document.querySelectorAll('.meal-action-toggle').forEach(function (element) {
                element.addEventListener('change', function () {
                    const userId = this.getAttribute('data-userid');
                    const mealTypeAttr = this.getAttribute('data-mealtype');
                    const offCheckbox = document.getElementById('off_' + userId);
                    const halfCheckbox = document.getElementById('half_' + userId);
                    const fullCheckbox = document.getElementById('full_' + userId);
                    const nightRadio = document.getElementById('night_' + userId);

                    if (mealTypeAttr === 'off') {
                        if (offCheckbox.checked) {
                            document.getElementById('modal_target_user_id').value = userId;
                            updateModalSummary();
                            if (!mealOffModalObj) {
                                mealOffModalObj = new bootstrap.Modal(document.getElementById('mealOffModal'));
                            }
                            mealOffModalObj.show();
                        } else {
                            sendMealUpdate(userId, 'full', "{{ now()->format('Y-m-d') }}", 1);
                        }
                    } else if (mealTypeAttr === 'half' || mealTypeAttr === 'half_day' || mealTypeAttr === 'half_night') {
                        let targetType = 'full';
                        if (mealTypeAttr === 'half' && !halfCheckbox.checked) {
                            targetType = 'full';
                        } else {
                            const isNight = nightRadio && nightRadio.checked;
                            targetType = isNight ? 'half_night' : 'half_day';
                        }
                        sendMealUpdate(userId, targetType, "{{ now()->format('Y-m-d') }}", 1);
                    } else if (mealTypeAttr === 'full') {
                        let targetType = fullCheckbox.checked ? 'full' : 'off';
                        if (targetType === 'off') {
                            document.getElementById('modal_target_user_id').value = userId;
                            updateModalSummary();
                            if (!mealOffModalObj) {
                                mealOffModalObj = new bootstrap.Modal(document.getElementById('mealOffModal'));
                            }
                            mealOffModalObj.show();
                        } else {
                            sendMealUpdate(userId, 'full', "{{ now()->format('Y-m-d') }}", 1);
                        }
                    }
                });
            });

            // Confirm Meal OFF Modal Button Handler
            const confirmBtn = document.getElementById('confirmMealOffBtn');
            if (confirmBtn) {
                confirmBtn.addEventListener('click', function () {
                    const userId = document.getElementById('modal_target_user_id').value;
                    const optionVal = document.getElementById('modal_meal_option').value;

                    let sendType = 'off';
                    let sendStartDate = "{{ now()->format('Y-m-d') }}";
                    let sendTotalDays = 1;

                    if (optionVal === 'today_day') {
                        sendType = 'half_night'; // Day off, night active
                        sendStartDate = "{{ now()->format('Y-m-d') }}";
                        sendTotalDays = 1;
                    } else if (optionVal === 'today_night') {
                        sendType = 'half_day'; // Night off, day active
                        sendStartDate = "{{ now()->format('Y-m-d') }}";
                        sendTotalDays = 1;
                    } else if (optionVal.startsWith('days_')) {
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

            function checkCutoffStatusLive() {
                const now = new Date();
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const currentTimeStr = `${hours}:${minutes}`;

                const isLunchCutoff = currentTimeStr >= '10:00';
                const isDinnerCutoff = currentTimeStr >= '16:00';

                if (currentTimeStr < '10:00') {
                    // Morning of a new day - automatically unlock inputs live without reload
                    document.querySelectorAll('.cell-off, .cell-half, .cell-full').forEach(function(cell) {
                        cell.classList.remove('pe-none', 'opacity-50');
                    });
                } else if (isDinnerCutoff) {
                    document.querySelectorAll('.meal-action-toggle').forEach(function(input) {
                        input.disabled = true;
                        if (input.parentElement) input.parentElement.classList.add('pe-none', 'opacity-50');
                    });
                    document.querySelectorAll('.cell-off, .cell-half, .cell-full').forEach(function(cell) {
                        cell.classList.add('pe-none', 'opacity-50');
                    });
                } else if (isLunchCutoff) {
                    document.querySelectorAll('[data-mealtype="half_day"]').forEach(function(input) {
                        input.disabled = true;
                        if (input.parentElement) input.parentElement.classList.add('pe-none', 'opacity-50');
                    });
                }
            }

            checkCutoffStatusLive();
            setInterval(checkCutoffStatusLive, 30000);
        });
    </script>
@endpush
