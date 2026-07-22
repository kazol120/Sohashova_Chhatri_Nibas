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

                    <form action="{{ route('meals.store') }}" method="post">
                        @csrf
                        <div class="card-body pt-4">
                            @if(isset($is_fallback) && $is_fallback)
                                <div class="alert alert-info d-flex align-items-center" role="alert">
                                    <span class="alert-icon text-info me-2">
                                        <i class="ti ti-info-circle ti-xs"></i>
                                    </span>
                                    <div>
                                        ⚠️ Today's ({{ now()->format('Y-m-d') }}) meal data not found. Showing data from latest available date: <strong>{{ $used_date }}</strong>.
                                    </div>
                                </div>
                            @endif

                            @if ($errors->any())
                                @foreach ($errors->all() as $error)
                                    <div class="alert alert-warning mb-2" role="alert">{{ $error }}</div>
                                @endforeach
                            @endif

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

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <input type="hidden" name="date" class="form-control"
                                           value="{{ old('date', now()->format('Y-m-d')) }}" required>
                                </div>
                            </div>

                            <div class="table-responsive border rounded">
                                <table class="table table-hover table-striped align-middle mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th style="width: 70px;" class="fw-bold text-secondary text-uppercase">SL</th>
                                        <th class="fw-bold text-secondary text-uppercase">Member Name</th>
                                        <th class="fw-bold text-secondary text-uppercase">Phone</th>
                                        <th style="width: 240px;" class="text-center fw-bold text-secondary text-uppercase">Half Meal Option</th>
                                        <th style="width: 140px;" class="text-center fw-bold text-secondary text-uppercase">Full Meal</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @php
                                        $mealMap = collect($meals)->keyBy('user_id');
                                    @endphp

                                    @foreach($users as $key => $user)
                                        @php
                                            $meal = $mealMap->get($user->id);
                                            $isHalfChecked = old("meal.$user->id.half_meal", $meal->half_meal ?? 0);
                                            $currentNote = old("meal.$user->id.note", $meal->note ?? '');
                                        @endphp

                                        <tr>
                                            <td><span class="text-muted font-monospace">{{ str_pad($key + 1, 2, '0', STR_PAD_LEFT) }}</span></td>
                                            <td>
                                                <div class="fw-semibold text-heading">{{ $user->name }}</div>
                                            </td>
                                            <td><small class="text-secondary">{{ $user->phone ?? '-' }}</small></td>

                                            <td class="text-center bg-light-evaluating">
                                                <div class="d-inline-block">
                                                    <input type="hidden" name="meal[{{ $user->id }}][half_meal]" value="0">
                                                    <div class="form-check d-flex justify-content-center mb-0">
                                                        <input type="checkbox" id="half_{{ $user->id }}"
                                                               class="form-check-input border-secondary setup-checkbox-md"
                                                               name="meal[{{ $user->id }}][half_meal]" value="1"
                                                            {{ $isHalfChecked ? 'checked' : '' }}>
                                                        <label class="form-check-label ms-2 d-none d-md-inline text-muted" for="half_{{ $user->id }}">Half</label>
                                                    </div>
                                                </div>

                                                <div id="session_container_{{ $user->id }}"
                                                     class="mt-2 p-2 bg-white border rounded shadow-sm d-flex justify-content-center gap-3"
                                                     style="display: {{ $isHalfChecked ? 'flex !important' : 'none !important' }}; max-width: 200px; margin: 0 auto;">
                                                    <div class="form-check form-check-inline mb-0">
                                                        <input class="form-check-input" type="radio"
                                                               name="meal[{{ $user->id }}][note]"
                                                               id="day_{{ $user->id }}"
                                                               value="day"
                                                            {{ $currentNote == 'day' ? 'checked' : '' }}>
                                                        <label class="form-check-label text-xs fw-medium text-secondary" for="day_{{ $user->id }}">☀️ Day</label>
                                                    </div>
                                                    <div class="form-check form-check-inline mb-0">
                                                        <input class="form-check-input" type="radio"
                                                               name="meal[{{ $user->id }}][note]"
                                                               id="night_{{ $user->id }}"
                                                               value="night"
                                                            {{ $currentNote == 'night' ? 'checked' : '' }}>
                                                        <label class="form-check-label text-xs fw-medium text-secondary" for="night_{{ $user->id }}">🌙 Night</label>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="text-center">
                                                <input type="hidden" name="meal[{{ $user->id }}][full_meal]" value="0">
                                                <div class="form-check d-flex justify-content-center mb-0">
                                                    <input type="checkbox" id="full_{{ $user->id }}"
                                                           class="form-check-input border-secondary setup-checkbox-md"
                                                           name="meal[{{ $user->id }}][full_meal]" value="1"
                                                        {{ old("meal.$user->id.full_meal", $meal->full_meal ?? 0) ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if($canSaveMeal)
                                <div class="row mt-4">
                                    <div class="d-grid gap-2 col-lg-4 mx-auto">
                                        <button id="saveMealBtn" class="btn btn-primary btn-lg waves-effect waves-light shadow-sm"
                                                type="submit">
                                            <i class="ti ti-device-floppy me-1"></i>Save Meal
                                        </button>
                                    </div>
                                </div>
                            @endif

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const btn = document.getElementById("saveMealBtn");
            if (!btn) return;

            const endTime = btn.getAttribute("data-endtime") ? btn.getAttribute("data-endtime").replace(' ', 'T') : null;
            if (!endTime) return;

            function updateCountdown() {
                const now = new Date();
                const target = new Date(endTime);
                const diff = target - now;

                if (diff <= 0) {
                    btn.style.display = 'none';
                    return;
                }

                const hours = Math.floor(diff / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                btn.innerText = `Save Meal (${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')})`;
            }

            updateCountdown();
            setInterval(updateCountdown, 1000);
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll('input[id^="half_"]').forEach(function (halfCheckbox) {
                const userId = halfCheckbox.id.split('_')[1];
                const fullCheckbox = document.getElementById('full_' + userId);
                const sessionContainer = document.getElementById('session_container_' + userId);

                if (halfCheckbox) {
                    halfCheckbox.addEventListener('change', function () {
                        if (this.checked) {
                            if (fullCheckbox) fullCheckbox.checked = false;
                            sessionContainer.style.setProperty('display', 'flex', 'important');
                        } else {
                            sessionContainer.style.setProperty('display', 'none', 'important');
                            document.querySelectorAll(`input[name="meal[${userId}][note]"]`).forEach(radio => radio.checked = false);
                        }
                    });
                }

                if (fullCheckbox) {
                    fullCheckbox.addEventListener('change', function () {
                        if (this.checked) {
                            halfCheckbox.checked = false;
                            sessionContainer.style.setProperty('display', 'none', 'important');
                            document.querySelectorAll(`input[name="meal[${userId}][note]"]`).forEach(radio => radio.checked = false);
                        }
                    });
                }
            });
        });
    </script>
@endpush
