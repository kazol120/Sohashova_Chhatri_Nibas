@extends('backend.layouts.app')
@section('title', 'My Payment History')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row g-6 mb-6">
    <!-- Total Billed Card -->
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading fw-semibold">Total Billed</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2 text-primary">৳{{ number_format($totalBilled, 2) }}</h4>
              </div>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="ti ti-receipt fs-4"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Total Paid Card -->
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading fw-semibold">Total Paid</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2 text-success">৳{{ number_format($totalPaid, 2) }}</h4>
              </div>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-success">
                <i class="ti ti-circle-check fs-4"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Total Due Card -->
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading fw-semibold">Total Due</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2 text-danger">৳{{ number_format($totalDue, 2) }}</h4>
              </div>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-danger">
                <i class="ti ti-alert-circle fs-4"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Total Months Card -->
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading fw-semibold">Total Months</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2 text-warning">{{ $payments->count() }}</h4>
              </div>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-warning">
                <i class="ti ti-calendar fs-4"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Current Month Highlight -->
  @if($currentMonthPayment)
  <div class="card mb-6 border border-2 border-primary bg-label-primary">
    <div class="card-body">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
          <p class="text-primary fw-bold mb-1" style="font-size:13px; text-transform:uppercase; letter-spacing:.05em;">
            <i class="ti ti-calendar-event me-1"></i> Current Month Status
          </p>
          <h5 class="mb-0 fw-bold text-dark">
            {{ \Carbon\Carbon::parse($currentMonthPayment['payment_month'] . '-01')->format('F Y') }}
          </h5>
        </div>
        <div class="text-end">
          @if($currentMonthPayment['status'] === 'paid')
            <span class="badge bg-success px-3 py-2 fs-6"><i class="ti ti-circle-check me-1"></i> Fully Paid</span>
          @elseif($currentMonthPayment['status'] === 'partial')
            <span class="badge bg-warning px-3 py-2 fs-6"><i class="ti ti-alert-triangle me-1"></i> Partial - Remaining: ৳{{ number_format($currentMonthPayment['due_amount'], 2) }}</span>
          @else
            <span class="badge bg-danger px-3 py-2 fs-6"><i class="ti ti-alert-circle me-1"></i> Overdue: ৳{{ number_format($currentMonthPayment['due_amount'], 2) }}</span>
          @endif
        </div>
        <div class="d-flex gap-4 flex-wrap">
          <div>
            <small class="text-muted d-block">Rent</small>
            <strong class="text-dark">৳{{ number_format($currentMonthPayment['amount'], 2) }}</strong>
          </div>
          <div>
            <small class="text-muted d-block">Paid</small>
            <strong class="text-success">৳{{ number_format($currentMonthPayment['paid_amount'], 2) }}</strong>
          </div>
          <div>
            <small class="text-muted d-block">Due</small>
            <strong class="text-danger">৳{{ number_format($currentMonthPayment['due_amount'], 2) }}</strong>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endif

  <!-- Meal System Summary & History for Resident -->
  <div class="row g-6 mb-6">
    <div class="col-md-12 col-xl-4">
      <div class="card h-100 border border-2 border-success bg-label-success">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-4">
            <h5 class="card-title mb-0 fw-bold text-dark"><i class="ti ti-salad me-2 text-success"></i>Meal Account Overview</h5>
            <span class="badge bg-success">{{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}</span>
          </div>
          <div class="row g-4 text-center">
            <div class="col-4">
              <small class="text-muted d-block mb-1">Total Meals</small>
              <h4 class="mb-0 fw-bold text-dark">{{ $mealSummary->total_meal }}</h4>
            </div>
            <div class="col-4">
              <small class="text-muted d-block mb-1">Meal Cost</small>
              <h4 class="mb-0 fw-bold text-danger">৳{{ number_format($mealSummary->meal_cost, 2) }}</h4>
            </div>
            <div class="col-4">
              <small class="text-muted d-block mb-1">Meal Deposit</small>
              <h4 class="mb-0 fw-bold text-success">৳{{ number_format($mealSummary->deposit_total, 2) }}</h4>
            </div>
          </div>
          <hr class="my-3">
          <div class="d-flex align-items-center justify-content-between">
            <span class="fw-semibold text-secondary">Outstanding Balance:</span>
            <span class="badge bg-{{ $mealSummary->balance < 0 ? 'danger' : 'success' }} fs-6 px-3 py-2 rounded-pill">
              ৳{{ number_format($mealSummary->balance, 2) }}
            </span>
          </div>
        </div>
      </div>
    </div>
    
    <div class="col-md-12 col-xl-8">
      <div class="card h-100">
        <div class="card-header border-bottom py-3 d-flex align-items-center justify-content-between">
          <h5 class="card-title mb-0 fw-bold text-dark">
            <i class="ti ti-history me-2 text-primary"></i>Recent Meals (This Month)
          </h5>
        </div>
        <div class="table-responsive text-nowrap" style="max-height: 180px; overflow-y: auto;">
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
                  <td class="ps-4">{{ \Carbon\Carbon::parse($meal->date)->format('d M Y') }}</td>
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
                  <td colspan="4" class="text-center py-4 text-muted">No meals recorded this month.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Payment List Table -->
  <div class="card">
    <div class="card-header border-bottom d-flex align-items-center justify-content-between py-3">
      <h5 class="card-title mb-0 fw-bold text-dark">
        <i class="ti ti-list-check me-2 text-primary"></i> My Payment History
      </h5>
    </div>
    
    @if($payments->isEmpty())
      <div class="card-body text-center py-5">
        <h5 class="fw-bold mb-1 text-muted">No payment history found</h5>
        <p class="text-muted">No monthly rent bills have been generated for your booking yet.</p>
      </div>
    @else
      <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr class="text-muted small fw-bold text-uppercase">
              <th class="ps-4">Sl</th>
              <th>Month</th>
              <th>Room & Seat</th>
              <th class="text-end">Rent</th>
              <th class="text-end text-success">Paid</th>
              <th class="text-end text-danger">Due</th>
              <th class="text-center">Status</th>
              <th>Payment Log & Note</th>
            </tr>
          </thead>
          <tbody>
            @foreach($payments as $idx => $pay)
            <tr>
              <td class="ps-4 text-muted fw-semibold">{{ $idx + 1 }}</td>
              <td>
                <span class="badge bg-label-info fw-bold px-3 py-2">
                  {{ \Carbon\Carbon::parse($pay['payment_month'] . '-01')->format('F Y') }}
                </span>
              </td>
              <td>
                <div class="fw-semibold text-dark">{{ $pay['roomnumber'] ?: '—' }}</div>
                <small class="text-muted">{{ $pay['floornumber'] }}</small>
              </td>
              <td class="text-end fw-bold text-dark">৳{{ number_format($pay['amount'], 2) }}</td>
              <td class="text-end fw-bold text-success">৳{{ number_format($pay['paid_amount'], 2) }}</td>
              <td class="text-end fw-bold text-danger">
                @if($pay['paid_amount'] > 0)
                  ৳{{ number_format($pay['due_amount'], 2) }}
                @else
                  <span class="text-muted">৳0.00</span>
                @endif
              </td>
              <td class="text-center">
                @if($pay['status'] === 'paid')
                  <span class="badge bg-label-success px-3 py-2 rounded-pill fw-bold">Paid</span>
                @elseif($pay['status'] === 'partial')
                  <span class="badge bg-label-warning px-3 py-2 rounded-pill fw-bold">Partial</span>
                @elseif($pay['status'] === 'overdue')
                  <span class="badge bg-label-danger px-3 py-2 rounded-pill fw-bold">Overdue</span>
                @else
                  <span class="badge bg-label-secondary px-3 py-2 rounded-pill fw-bold">Pending</span>
                @endif
              </td>
              <td>
                @if($pay['note'])
                  <div class="small text-muted" style="max-width: 250px; overflow: hidden; text-overflow: ellipsis;" title="{{ $pay['note'] }}">
                    <i class="ti ti-notes me-1"></i> {{ collect(explode("\n", $pay['note']))->last() }}
                  </div>
                @endif
                @if($pay['received_by'])
                  <div class="small text-muted mt-1">
                    <i class="ti ti-user-check me-1 text-success"></i>{{ $pay['received_by'] }}
                  </div>
                @endif
                @if(!$pay['note'] && !$pay['received_by'])
                  <span class="text-muted small">—</span>
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
    <div class="card-footer bg-light border-top text-center py-3">
      <small class="text-muted">
        <i class="ti ti-info-circle me-1"></i> For any payment queries or updates, please contact the administrator.
      </small>
    </div>
  </div>
</div>
@endsection
