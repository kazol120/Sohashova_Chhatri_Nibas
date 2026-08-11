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

  @if(isset($mealSummary) && ($mealSummary->deposit_total <= 0 || $mealSummary->balance <= 0))
    <div class="alert alert-danger d-flex align-items-center justify-content-between flex-wrap gap-2 p-3 mb-4 shadow-sm border-danger rounded-3" role="alert">
      <div class="d-flex align-items-center">
        <span class="alert-icon text-danger me-3 fs-3">
          <i class="ti ti-alert-triangle"></i>
        </span>
        <div>
          <h6 class="alert-heading mb-1 text-danger fw-bold fs-6">মেল ডিপোজিট বকেয়া/জিরো অ্যালার্ট (Meal Deposit Alert)</h6>
          <div class="fw-medium text-dark fs-7">
            ⚠️ আপনার মেল ডিপোজিট (Meal Deposit) ব্যালেন্স ৳ {{ number_format($mealSummary->balance, 2) }} টাকা! সার্ভিস সক্রিয় রাখতে অনুগ্রহ করে মেল ডিপোজিট জমা দিন এবং মেল চালু করতে এডমিন এর সাথে যোগাযোগ করুন।
          </div>
        </div>
      </div>
    </div>
  @endif

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
            <th class="text-center">Meal Status (মিল স্ট্যাটাস)</th>
            <th class="text-center">Half Meal</th>
            <th class="text-center">Full Meal</th>
            <th>Note</th>
            <th class="text-center">Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($mealsList as $meal)
            <tr class="{{ $meal->is_off ? 'table-light text-muted' : '' }}">
              <td class="ps-4 fw-semibold">{{ \Carbon\Carbon::parse($meal->date)->format('d M Y (D)') }}</td>
              <td class="text-center">
                @if($meal->is_off)
                  <span class="badge bg-danger px-3 py-1 fs-7">🚫 Meal OFF (মিল বন্ধ)</span>
                @else
                  <span class="badge bg-success px-3 py-1 fs-7">✅ Meal ON (চালু)</span>
                @endif
              </td>
              <td class="text-center">
                @if($meal->half_meal && !$meal->is_off)
                  <span class="badge bg-label-info">{{ $meal->note == 'day' ? '☀️ Day' : '🌙 Night' }}</span>
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>
              <td class="text-center">
                @if($meal->full_meal && !$meal->is_off)
                  <span class="badge bg-label-success">Yes</span>
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>
              <td>{{ $meal->note ?: '—' }}</td>
              <td class="text-center">
                <div class="dropdown">
                  <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle waves-effect" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ti ti-settings me-1"></i> Change Status
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <form action="{{ route('meals.update-status') }}" method="POST">
                        @csrf
                        <input type="hidden" name="date" value="{{ $meal->date }}">
                        <input type="hidden" name="meal_type" value="full">
                        <button type="submit" class="dropdown-item text-success fw-semibold">
                          <i class="ti ti-check me-2"></i> 🟢 Full Meal (ফুল মিল)
                        </button>
                      </form>
                    </li>
                    <li>
                      <form action="{{ route('meals.update-status') }}" method="POST">
                        @csrf
                        <input type="hidden" name="date" value="{{ $meal->date }}">
                        <input type="hidden" name="meal_type" value="half_day">
                        <button type="submit" class="dropdown-item text-info fw-semibold">
                          <i class="ti ti-sun me-2"></i> ☀️ Half Day (দিনের হাফ)
                        </button>
                      </form>
                    </li>
                    <li>
                      <form action="{{ route('meals.update-status') }}" method="POST">
                        @csrf
                        <input type="hidden" name="date" value="{{ $meal->date }}">
                        <input type="hidden" name="meal_type" value="half_night">
                        <button type="submit" class="dropdown-item text-primary fw-semibold">
                          <i class="ti ti-moon me-2"></i> 🌙 Half Night (রাতের হাফ)
                        </button>
                      </form>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                      <form action="{{ route('meals.update-status') }}" method="POST">
                        @csrf
                        <input type="hidden" name="date" value="{{ $meal->date }}">
                        <input type="hidden" name="meal_type" value="off">
                        <button type="submit" class="dropdown-item text-danger fw-bold">
                          <i class="ti ti-ban me-2"></i> 🚫 Meal OFF (মিল বন্ধ)
                        </button>
                      </form>
                    </li>
                  </ul>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center py-4 text-muted">No meals recorded for this month.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
