@extends('backend.layouts.app')

@section("title")
    | {{$page_title}}
@endsection

@push('style')
    <link rel="stylesheet" href="{{asset('backend')}}/vendor/libs/datatables-bs5/datatables.bootstrap5.css" />
    <link rel="stylesheet" href="{{asset('backend')}}/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css" />
    <link rel="stylesheet" href="{{asset('backend')}}/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css" />
@endpush

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card shadow-sm border-0">
            <div class="card-header border-bottom d-flex justify-content-between align-items-center bg-light py-3">
                <h5 class="card-title mb-0 fw-bold text-dark">
                    <i class="ti ti-history me-2 text-primary"></i>{{$page_title}}
                </h5>
            </div>

            <div class="card-body pt-4">

                <form action="{{ route('meals.index') }}" method="GET" class="row g-3 mb-4 items-stretch">
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-secondary">Select Date</label>
                        <input type="date"
                               name="date"
                               class="form-control"
                               max="{{ date('Y-m-d') }}"
                               value="{{ request('date', $selected_date ?? now()->format('Y-m-d')) }}">
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100 shadow-sm">
                            <i class="ti ti-search me-1"></i>Search
                        </button>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <a href="{{ route('meals.index') }}" class="btn btn-secondary w-100 shadow-sm">
                            <i class="ti ti-refresh me-1"></i>Reset
                        </a>
                    </div>
                </form>

                <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                    <span class="alert-icon text-info me-2">
                        <i class="ti ti-info-circle ti-xs"></i>
                    </span>
                    <div>
                        Showing meal status for <strong class="text-dark">{{ $selectedDate }}</strong>
                    </div>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-4 col-xl-2">
                        <div class="card bg-label-secondary border-0 shadow-none text-center p-3 h-100">
                            <h6 class="mb-1 text-secondary fw-semibold text-xs">Total Member</h6>
                            <h3 class="mb-0 fw-bold text-dark">{{ $summary['total_member'] }}</h3>
                        </div>
                    </div>

                    <div class="col-md-4 col-xl-2">
                        <div class="card bg-label-success border-0 shadow-none text-center p-3 h-100">
                            <h6 class="mb-1 text-success fw-semibold text-xs">Total Meal On</h6>
                            <h3 class="mb-0 fw-bold text-dark">{{ $summary['total_meal_on'] }}</h3>
                        </div>
                    </div>

                    <div class="col-md-4 col-xl-2">
                        <div class="card bg-label-info border-0 shadow-none text-center p-3 h-100">
                            <h6 class="mb-1 text-info fw-semibold text-xs">☀️ Day Half</h6>
                            <h3 class="mb-0 fw-bold text-dark">{{ $summary['total_day_half'] }}</h3>
                        </div>
                    </div>

                    <div class="col-md-4 col-xl-2">
                        <div class="card bg-label-dark border-0 shadow-none text-center p-3 h-100">
                            <h6 class="mb-1 text-dark fw-semibold text-xs">🌙 Night Half</h6>
                            <h3 class="mb-0 fw-bold text-dark">{{ $summary['total_night_half'] }}</h3>
                        </div>
                    </div>

                    <div class="col-md-4 col-xl-2">
                        <div class="card bg-label-warning border-0 shadow-none text-center p-3 h-100">
                            <h6 class="mb-1 text-warning fw-semibold text-xs">Total Full</h6>
                            <h3 class="mb-0 fw-bold text-dark">{{ $summary['total_full'] }}</h3>
                        </div>
                    </div>

                    <div class="col-md-4 col-xl-2">
                        <div class="card bg-label-danger border-0 shadow-none text-center p-3 h-100">
                            <h6 class="mb-1 text-danger fw-semibold text-xs">Total Off</h6>
                            <h3 class="mb-0 fw-bold text-dark">{{ $summary['total_off'] }}</h3>
                        </div>
                    </div>
                </div>

                @php
                    $mealMap = collect($meals)->keyBy('user_id');
                @endphp

                <div class="table-responsive border rounded">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th style="width: 70px;" class="fw-bold text-secondary text-uppercase">SL</th>
                            <th class="fw-bold text-secondary text-uppercase">Member Name</th>
                            <th class="fw-bold text-secondary text-uppercase">Phone</th>
                            <th class="text-center fw-bold text-secondary text-uppercase" style="width: 180px;">Half Option</th>
                            <th class="text-center fw-bold text-secondary text-uppercase" style="width: 140px;">Full Meal</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($users as $key => $user)
                            @php
                                $meal = $mealMap->get($user->id);
                            @endphp
                            <tr>
                                <td><span class="text-muted font-monospace">{{ str_pad($key + 1, 2, '0', STR_PAD_LEFT) }}</span></td>
                                <td>
                                    <div class="fw-semibold text-heading">{{ $user->name }}</div>
                                </td>
                                <td><small class="text-secondary">{{ $user->phone ?? '-' }}</small></td>

                                <td class="text-center">
                                    @if($meal && $meal->half_meal)
                                        <div class="d-flex flex-column align-items-center gap-1">
                                            <span class="badge bg-label-success px-2 py-1 fw-semibold">Yes</span>
                                            @if($meal->note == 'day')
                                                <span class="badge bg-label-info rounded-pill text-xs px-2 py-0">☀️ Day</span>
                                            @elseif($meal->note == 'night')
                                                <span class="badge bg-label-dark rounded-pill text-xs px-2 py-0">🌙 Night</span>
                                            @else
                                                <span class="badge bg-label-secondary rounded-pill text-xs px-2 py-0">-</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="badge bg-label-danger px-2 py-1">No</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    @if($meal && $meal->full_meal)
                                        <span class="badge bg-label-success px-2 py-1 fw-semibold">Yes</span>
                                    @else
                                        <span class="badge bg-label-danger px-2 py-1">No</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No users found</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{asset('backend')}}/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
    <script src="{{asset('backend')}}/vendor/libs/datatables-bs5/custom-datatable5.js"></script>
@endpush
