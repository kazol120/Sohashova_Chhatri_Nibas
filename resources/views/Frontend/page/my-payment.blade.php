@extends('Frontend.layouts.app')
@section('title', 'My Payments')

@push('styles')
<style>
  .pay-hero {
    background: linear-gradient(135deg, #033364 0%, #185fa5 60%, #2196f3 100%);
    padding: 56px 0 80px;
    margin-bottom: -40px;
  }
  .pay-hero h2 {
    color: #fff;
    font-size: 2rem;
    font-weight: 800;
  }
  .pay-hero p {
    color: rgba(255,255,255,0.8);
    font-size: 1rem;
  }
  .pay-card {
    border-radius: 18px;
    border: none;
    box-shadow: 0 4px 24px rgba(3,51,100,0.10);
    background: #fff;
  }
  .summary-card {
    border-radius: 14px;
    padding: 22px 24px;
    color: #fff;
    display: flex;
    flex-direction: column;
    gap: 4px;
  }
  .summary-card .label { font-size: 13px; opacity: 0.85; font-weight: 600; }
  .summary-card .amount { font-size: 1.7rem; font-weight: 800; }
  .sc-blue   { background: linear-gradient(135deg, #033364, #185fa5); }
  .sc-green  { background: linear-gradient(135deg, #0a8f4d, #2ecc71); }
  .sc-red    { background: linear-gradient(135deg, #c0392b, #e74c3c); }
  .sc-orange { background: linear-gradient(135deg, #e67e22, #f39c12); }

  .badge-paid     { background: #d4edda; color: #155724; }
  .badge-partial  { background: #fff3cd; color: #856404; }
  .badge-overdue  { background: #f8d7da; color: #721c24; }
  .badge-pending  { background: #e2e3e5; color: #383d41; }
  .pay-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    display: inline-block;
  }
  .month-label {
    background: #e0f2fe;
    color: #0369a1;
    border-radius: 8px;
    padding: 3px 10px;
    font-size: 12px;
    font-weight: 700;
    display: inline-block;
  }
  .log-text {
    font-size: 12px;
    color: #6c757d;
    max-width: 220px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
  .no-record {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
  }
  .no-record .icon { font-size: 56px; margin-bottom: 12px; }
  .table > :not(caption) > * > * { padding: 14px 12px; }
  .this-month-card {
    border-radius: 14px;
    border: 2px solid #033364;
    background: #f0f6ff;
    padding: 20px 24px;
  }
</style>
@endpush

@section('content')
<div class="pay-hero">
  <div class="container">
    <div class="d-flex align-items-center gap-3 mb-2">
      <div style="width:52px;height:52px;border-radius:14px;background:rgba(255,255,255,0.18);display:flex;align-items:center;justify-content:center;">
        <i class="bi bi-wallet2" style="font-size:26px;color:#fff;"></i>
      </div>
      <div>
        <h2 class="mb-0">My Payments</h2>
        <p class="mb-0">{{ $user->name ?? '' }} — Monthly rent statement</p>
      </div>
    </div>
  </div>
</div>

<div class="container pb-5">

  {{-- Summary Cards --}}
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="summary-card sc-blue">
        <span class="label"><i class="bi bi-receipt me-1"></i> Total Billed</span>
        <span class="amount">৳{{ number_format($totalBilled, 0) }}</span>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="summary-card sc-green">
        <span class="label"><i class="bi bi-check-circle me-1"></i> Total Paid</span>
        <span class="amount">৳{{ number_format($totalPaid, 0) }}</span>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="summary-card sc-red">
        <span class="label"><i class="bi bi-exclamation-circle me-1"></i> Total Due</span>
        <span class="amount">৳{{ number_format($totalDue, 0) }}</span>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="summary-card sc-orange">
        <span class="label"><i class="bi bi-calendar3 me-1"></i> Total Months</span>
        <span class="amount">{{ $payments->count() }}</span>
      </div>
    </div>
  </div>

  {{-- Current Month Highlight --}}
  @if($currentMonthPayment)
  <div class="this-month-card mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
      <div>
        <p class="text-primary fw-bold mb-1" style="font-size:13px; text-transform:uppercase; letter-spacing:.05em;">
          <i class="bi bi-calendar-check me-1"></i> Current Month Status
        </p>
        <h5 class="mb-0 fw-bold text-dark">
          {{ \Carbon\Carbon::parse($currentMonthPayment['payment_month'] . '-01')->format('F Y') }}
        </h5>
      </div>
      <div class="text-end">
        @if($currentMonthPayment['status'] === 'paid')
          <span class="pay-badge badge-paid"><i class="bi bi-check-circle-fill me-1"></i> Fully Paid</span>
        @elseif($currentMonthPayment['status'] === 'partial')
          <span class="pay-badge badge-partial"><i class="bi bi-hourglass-split me-1"></i> Partial — Due: ৳{{ number_format($currentMonthPayment['due_amount'], 2) }}</span>
        @else
          <span class="pay-badge badge-overdue"><i class="bi bi-alarm me-1"></i> Outstanding — Due: ৳{{ number_format($currentMonthPayment['due_amount'], 2) }}</span>
        @endif
      </div>
      <div class="d-flex gap-4 flex-wrap">
        <div>
          <small class="text-muted d-block">Monthly Rent</small>
          <strong>৳{{ number_format($currentMonthPayment['amount'], 2) }}</strong>
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
  @endif

  {{-- Payment History Table --}}
  <div class="pay-card">
    <div class="p-4 border-bottom d-flex align-items-center justify-content-between">
      <h5 class="mb-0 fw-bold text-dark">
        <i class="bi bi-clock-history me-2 text-primary"></i> Payment History
      </h5>
      <span class="badge bg-primary rounded-pill px-3">{{ $payments->count() }} Months</span>
    </div>

    @if($payments->isEmpty())
      <div class="no-record">
        <div class="icon">💸</div>
        <h5 class="fw-bold">No payment records found</h5>
        <p class="text-muted">No monthly rent bills have been generated for your booking yet.</p>
      </div>
    @else
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr class="small fw-bold text-uppercase text-muted">
            <th class="ps-4">#</th>
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
              <span class="month-label">
                {{ \Carbon\Carbon::parse($pay['payment_month'] . '-01')->format('M Y') }}
              </span>
            </td>
            <td>
              <div class="fw-semibold text-dark" style="font-size:14px;">{{ $pay['roomnumber'] ?: '—' }}</div>
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
                <span class="pay-badge badge-paid">✅ Paid</span>
              @elseif($pay['status'] === 'partial')
                <span class="pay-badge badge-partial">🟡 Partial</span>
              @elseif($pay['status'] === 'overdue')
                <span class="pay-badge badge-overdue">🔴 Overdue</span>
              @else
                <span class="pay-badge badge-pending">⏳ Pending</span>
              @endif
            </td>
            <td>
              @if($pay['note'])
                <div class="log-text" title="{{ $pay['note'] }}">
                  <i class="bi bi-chat-text me-1 text-muted"></i>
                  {{ collect(explode("\n", $pay['note']))->last() }}
                </div>
              @endif
              @if($pay['received_by'])
                <div class="small text-muted mt-1">
                  <i class="bi bi-person-check me-1 text-success"></i>{{ $pay['received_by'] }}
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

    {{-- Footer note --}}
    <div class="p-3 border-top bg-light rounded-bottom text-center">
      <small class="text-muted">
        <i class="bi bi-info-circle me-1"></i>
        Please contact the administrator for any payment queries or discrepancies.
      </small>
    </div>
  </div>
</div>
@endsection
