@extends('backend.layouts.app')
@section('title', 'My Meal History')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <!-- Month Filter Header -->
  <div class="card mb-4">
    <div class="card-body py-3">
      <form method="GET" action="{{ route('dashboard.my-meals') }}" class="row g-3 align-items-center">
        <div class="col-auto">
          <label class="col-form-label fw-bold"><i class="ti ti-calendar me-1"></i> Select Month:</label>
        </div>
        <div class="col-auto">
          <input type="month" name="month" value="{{ $selectedMonth }}" class="form-control" onchange="this.form.submit()">
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-primary"><i class="ti ti-filter me-1"></i> Filter</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Summary Cards -->
  <div class="row g-4 mb-4">
    <!-- Total Meals -->
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading fw-semibold">Total Meals</span>
              <div class="d-flex align-items-center my-1">
                <h3 class="mb-0 me-2 text-dark fw-bold">{{ $mealSummary->total_meal }}</h3>
              </div>
              <small class="text-muted">{{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}</small>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-info">
                <i class="ti ti-salad fs-3"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Meal Cost -->
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading fw-semibold">Meal Cost</span>
              <div class="d-flex align-items-center my-1">
                <h3 class="mb-0 me-2 text-danger fw-bold">৳{{ number_format($mealSummary->meal_cost, 2) }}</h3>
              </div>
              <small class="text-muted">Total meal charges</small>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-danger">
                <i class="ti ti-shopping-cart fs-3"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Meal Deposit -->
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading fw-semibold">Meal Deposit</span>
              <div class="d-flex align-items-center my-1">
                <h3 class="mb-0 me-2 text-success fw-bold">৳{{ number_format($mealSummary->deposit_total, 2) }}</h3>
              </div>
              <small class="text-muted">Deposited amount</small>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-success">
                <i class="ti ti-cash fs-3"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Meal Balance -->
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading fw-semibold">Outstanding Balance</span>
              <div class="d-flex align-items-center my-1">
                <h3 class="mb-0 me-2 {{ $mealSummary->balance < 0 ? 'text-danger' : 'text-success' }} fw-bold">
                  ৳{{ number_format($mealSummary->balance, 2) }}
                </h3>
              </div>
              <small class="text-muted">{{ $mealSummary->balance < 0 ? 'Due Amount' : 'Surplus Advance' }}</small>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-{{ $mealSummary->balance < 0 ? 'danger' : 'success' }}">
                <i class="ti ti-wallet fs-3"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Detailed Meal History Table -->
  <div class="card">
    <div class="card-header border-bottom py-3 d-flex align-items-center justify-content-between">
      <h5 class="card-title mb-0 fw-bold text-dark">
        <i class="ti ti-history me-2 text-primary"></i>Meal Breakdown ({{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }})
      </h5>
    </div>
    <div class="table-responsive text-nowrap">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr class="text-muted small fw-bold text-uppercase">
            <th class="ps-4">Date</th>
            <th class="text-center">Half Meal</th>
            <th class="text-center">Full Meal</th>
            <th>Note</th>
          </tr>
        </thead>
        <tbody>
          @forelse($mealsList as $meal)
            <tr>
              <td class="ps-4 fw-semibold">{{ \Carbon\Carbon::parse($meal->date)->format('d M Y (D)') }}</td>
              <td class="text-center">
                @if($meal->half_meal)
                  <span class="badge bg-label-info">{{ $meal->note == 'day' ? '☀️ Day' : '🌙 Night' }}</span>
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>
              <td class="text-center">
                @if($meal->full_meal)
                  <span class="badge bg-label-success">Yes</span>
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>
              <td>{{ $meal->note ?: '—' }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="text-center py-4 text-muted">No meals recorded for this month.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
