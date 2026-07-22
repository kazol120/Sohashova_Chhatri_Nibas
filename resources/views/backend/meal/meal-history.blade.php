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
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">{{$page_title}}</h5>
            </div>

            <div class="card-body">

                <form action="{{ route('meal-history') }}" method="GET" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Select Month</label>
                        <input type="month"
                               name="month"
                               class="form-control"
                               max="{{ now()->format('Y-m') }}"
                               value="{{ request('month', $selectedMonth ?? now()->format('Y-m')) }}">
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            Search
                        </button>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <a href="{{ route('meals.index') }}" class="btn btn-secondary w-100">
                            Reset
                        </a>
                    </div>
                </form>

                <div class="alert alert-info">
                    Showing meal history for
                    <strong>{{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}</strong>
                    <br>
                    Half Rate: <strong>{{ number_format($half_rate, 2) }}</strong> |
                    Full Rate: <strong>{{ number_format($full_rate, 2) }}</strong>
                </div>

                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card border-primary h-100">
                            <div class="card-body text-center">
                                <h6 class="mb-2">Total Member</h6>
                                <h4 class="mb-0">{{ $summary['total_member'] }}</h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border-success h-100">
                            <div class="card-body text-center">
                                <h6 class="mb-2">Total Half Meal</h6>
                                <h4 class="mb-0">{{ $summary['total_half'] }}</h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border-warning h-100">
                            <div class="card-body text-center">
                                <h6 class="mb-2">Total Full Meal</h6>
                                <h4 class="mb-0">{{ $summary['total_full'] }}</h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border-info h-100">
                            <div class="card-body text-center">
                                <h6 class="mb-2">Grand Total Meal</h6>
                                <h4 class="mb-0">{{ $summary['total_meal'] }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-dark">
                        <tr>
                            <th style="width: 70px;">SL</th>
                            <th>Member Name</th>
                            <th>Phone</th>
                            <th class="text-center">Half</th>
                            <th class="text-center">Full</th>
                            <th class="text-center">Meal</th>
                            <th class="text-center">Cost</th>
                            <th class="text-center">Deposit</th>
                            <th class="text-center">Balance</th>
                        </tr>
                        </thead>

                        <tbody>
                        @forelse($users as $key => $user)
                            @php
                                $userMeal = $mealSummaryByUser[$user->id] ?? (object) [
                                    'half_total' => 0,
                                    'full_total' => 0,
                                    'total_meal' => 0,
                                    'meal_cost' => 0,
                                    'deposit_amount' => 0,
                                    'balance' => 0,
                                ];

                                $balance = $userMeal->balance ?? 0;
                            @endphp

                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->phone ?? '-' }}</td>
                                <td class="text-center">{{ $userMeal->half_total }}</td>
                                <td class="text-center">{{ $userMeal->full_total }}</td>
                                <td class="text-center fw-semibold">{{ $userMeal->total_meal }}</td>
                                <td class="text-center">{{ number_format($userMeal->meal_cost, 2) }}</td>
                                <td class="text-center">{{ number_format($userMeal->deposit_amount, 2) }}</td>
                                <td class="text-center">
                                    @if($balance < 0)
                                        <span class="badge bg-danger">{{ number_format($balance, 2) }}</span>
                                    @elseif($balance > 0)
                                        <span class="badge bg-success">{{ number_format($balance, 2) }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ number_format($balance, 2) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">No users found</td>
                            </tr>
                        @endforelse
                        </tbody>

                        <tfoot>
                        <tr class="table-secondary fw-bold">
                            <td colspan="3" class="text-end">Grand Total</td>
                            <td class="text-center">{{ $summary['total_half'] ?? 0 }}</td>
                            <td class="text-center">{{ $summary['total_full'] ?? 0 }}</td>
                            <td class="text-center">{{ $summary['total_meal'] ?? 0 }}</td>
                            <td class="text-center">{{ number_format($summary['total_meal_cost'] ?? 0, 2) }}</td>
                            <td class="text-center">{{ number_format($summary['total_deposit'] ?? 0, 2) }}</td>
                            <td class="text-center">
                                @php
                                    $grandBalance = $summary['total_balance'] ?? 0;
                                @endphp

                                @if($grandBalance < 0)
                                    <span class="badge bg-danger">{{ number_format($grandBalance, 2) }}</span>
                                @elseif($grandBalance > 0)
                                    <span class="badge bg-success">{{ number_format($grandBalance, 2) }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ number_format($grandBalance, 2) }}</span>
                                @endif
                            </td>
                        </tr>
                        </tfoot>
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
