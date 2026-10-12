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
            <small class="text-muted d-block">Monthly Rent</small>
            <strong class="text-dark">৳{{ number_format($currentMonthPayment['amount'], 2) }}</strong>
          </div>
          <div>
            <small class="text-warning d-block">⚡ Electricity Bill</small>
            <strong class="text-warning fw-bold">৳{{ number_format($currentMonthPayment['electricity_bill'] ?? 0, 2) }}</strong>
          </div>
          @if(isset($currentMonthPayment['carried_forward_due']) && $currentMonthPayment['carried_forward_due'] > 0)
          <div>
            <small class="text-danger d-block">Prev. Due (জের)</small>
            <strong class="text-danger">+৳{{ number_format($currentMonthPayment['carried_forward_due'], 2) }}</strong>
          </div>
          @endif
          <div>
            <small class="text-muted d-block">Paid</small>
            <strong class="text-success">৳{{ number_format($currentMonthPayment['paid_amount'], 2) }}</strong>
          </div>
          <div>
            <small class="text-muted d-block">Total Due</small>
            <strong class="text-danger">৳{{ number_format($currentMonthPayment['due_amount'], 2) }}</strong>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endif

  <!-- Payment List Table -->
  <div class="card">
    <div class="card-header border-bottom d-flex align-items-center justify-content-between py-3 flex-wrap gap-2">
      <h5 class="card-title mb-0 fw-bold text-dark">
        <i class="ti ti-list-check me-2 text-primary"></i> My Payment History
      </h5>
      <a href="{{ route('dashboard.my-electricity-bills') }}" class="btn btn-sm btn-outline-warning text-dark fw-bold">
        <i class="ti ti-bolt me-1 text-warning"></i> বিদ্যুৎ বিল স্টেটমেন্ট দেখুন
      </a>
    </div>
    
    @if($payments->isEmpty())
      <div class="card-body text-center py-5">
        <h5 class="fw-bold mb-1 text-muted">No payment history found</h5>
        <p class="text-muted">No monthly rent bills have been generated for your booking yet.</p>
      </div>
    @else
      {{-- Desktop Table View --}}
      <div class="d-none d-md-block table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr class="text-muted small fw-bold text-uppercase">
              <th class="ps-4">Sl</th>
              <th>Month</th>
              <th>Room & Seat</th>
              <th class="text-end">Rent</th>
              <th class="text-center text-warning">Elect. Bill</th>
              <th class="text-end text-danger">Prev. Due</th>
              <th class="text-end text-success">Paid</th>
              <th class="text-end text-danger">Net Due</th>
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
              <td class="text-center">
                @if($pay['electricity_bill'] > 0)
                  <span class="badge bg-warning text-dark fw-bold px-2 py-1">
                    ⚡ ৳ {{ number_format($pay['electricity_bill'], 2) }}
                  </span>
                @else
                  <span class="text-muted small">৳0.00</span>
                @endif
              </td>
              <td class="text-end fw-semibold">
                @if(isset($pay['carried_forward_due']) && $pay['carried_forward_due'] > 0)
                  <span class="text-danger fw-bold">+৳{{ number_format($pay['carried_forward_due'], 2) }}</span>
                @else
                  <span class="text-muted">৳0.00</span>
                @endif
              </td>
              <td class="text-end fw-bold text-success">৳{{ number_format($pay['paid_amount'], 2) }}</td>
              <td class="text-end fw-bold text-danger">
                @if($pay['status'] === 'paid')
                  <span class="text-muted">৳0.00</span>
                @else
                  ৳{{ number_format($pay['due_amount'], 2) }}
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

      {{-- Mobile Card View (No Horizontal Scroll) --}}
      <div class="d-md-none p-2 p-sm-3">
        @foreach($payments as $idx => $pay)
        <div class="card border shadow-sm mb-3 rounded-3 overflow-hidden">
          <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
              <span class="badge bg-primary rounded-circle" style="width:24px; height:24px; display:inline-flex; align-items:center; justify-content:center; font-size:11px;">{{ $idx + 1 }}</span>
              <span class="fw-bold text-dark fs-6">{{ \Carbon\Carbon::parse($pay['payment_month'] . '-01')->format('F Y') }}</span>
            </div>
            <div>
              @if($pay['status'] === 'paid')
                <span class="badge bg-label-success px-2.5 py-1 rounded-pill fw-bold">Paid</span>
              @elseif($pay['status'] === 'partial')
                <span class="badge bg-label-warning px-2.5 py-1 rounded-pill fw-bold">Partial</span>
              @elseif($pay['status'] === 'overdue')
                <span class="badge bg-label-danger px-2.5 py-1 rounded-pill fw-bold">Overdue</span>
              @else
                <span class="badge bg-label-secondary px-2.5 py-1 rounded-pill fw-bold">Pending</span>
              @endif
            </div>
          </div>
          <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between text-muted small pb-2 mb-2 border-bottom">
              <span><i class="ti ti-door me-1"></i><strong>{{ $pay['roomnumber'] ?: '—' }}</strong></span>
              <span><i class="ti ti-building me-1"></i>{{ $pay['floornumber'] }}</span>
            </div>

            <div class="row g-2 text-start">
              <div class="col-6">
                <div class="p-2 bg-light rounded-2">
                  <small class="text-muted d-block">Monthly Rent</small>
                  <span class="fw-bold text-dark">৳{{ number_format($pay['amount'], 2) }}</span>
                </div>
              </div>
              <div class="col-6">
                <div class="p-2 bg-light rounded-2">
                  <small class="text-muted d-block">Electricity Bill</small>
                  @if($pay['electricity_bill'] > 0)
                    <span class="fw-bold text-warning">⚡ ৳{{ number_format($pay['electricity_bill'], 2) }}</span>
                  @else
                    <span class="text-muted">৳0.00</span>
                  @endif
                </div>
              </div>
              <div class="col-6">
                <div class="p-2 bg-light rounded-2">
                  <small class="text-muted d-block">Prev. Due (জের)</small>
                  @if(isset($pay['carried_forward_due']) && $pay['carried_forward_due'] > 0)
                    <span class="fw-bold text-danger">+৳{{ number_format($pay['carried_forward_due'], 2) }}</span>
                  @else
                    <span class="text-muted">৳0.00</span>
                  @endif
                </div>
              </div>
              <div class="col-6">
                <div class="p-2 bg-light rounded-2">
                  <small class="text-muted d-block">Paid Amount</small>
                  <span class="fw-bold text-success">৳{{ number_format($pay['paid_amount'], 2) }}</span>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
              <span class="fw-bold text-dark">Net Due (বকেয়া):</span>
              <span class="fw-bolder fs-5 {{ $pay['status'] === 'paid' ? 'text-success' : 'text-danger' }}">
                {{ $pay['status'] === 'paid' ? '৳0.00' : '৳' . number_format($pay['due_amount'], 2) }}
              </span>
            </div>

            @if($pay['note'] || $pay['received_by'])
            <div class="mt-2 pt-2 border-top small text-muted">
              @if($pay['note'])
                <div><i class="ti ti-notes me-1"></i> {{ collect(explode("\n", $pay['note']))->last() }}</div>
              @endif
              @if($pay['received_by'])
                <div class="mt-1"><i class="ti ti-user-check me-1 text-success"></i> Received by: {{ $pay['received_by'] }}</div>
              @endif
            </div>
            @endif
          </div>
        </div>
        @endforeach
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
