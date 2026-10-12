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
          <thead style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); color: #ffffff;">
            <tr class="small fw-bold text-uppercase text-white">
              <th class="ps-4 text-white">Sl</th>
              <th class="text-white">Month</th>
              <th class="text-white">Room & Seat</th>
              <th class="text-end text-white">Rent</th>
              <th class="text-center text-warning">Elect. Bill</th>
              <th class="text-end text-danger-subtle" style="color: #fca5a5 !important;">Prev. Due</th>
              <th class="text-end text-success-subtle" style="color: #86efac !important;">Paid</th>
              <th class="text-end text-warning-subtle" style="color: #fde047 !important;">Net Due</th>
              <th class="text-center text-white">Status</th>
              <th class="text-white">Payment Log & Note</th>
            </tr>
          </thead>
          <tbody class="bg-white">
            @foreach($payments as $idx => $pay)
            <tr>
              <td class="ps-4 text-dark fw-bold">{{ $idx + 1 }}</td>
              <td>
                <span class="badge px-3 py-2 fw-bold" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">
                  {{ \Carbon\Carbon::parse($pay['payment_month'] . '-01')->format('F Y') }}
                </span>
              </td>
              <td>
                <div class="fw-bold text-dark">{{ $pay['roomnumber'] ?: '—' }}</div>
                <small class="text-secondary fw-semibold">{{ $pay['floornumber'] }}</small>
              </td>
              <td class="text-end fw-bold text-dark">৳{{ number_format($pay['amount'], 2) }}</td>
              <td class="text-center">
                @if($pay['electricity_bill'] > 0)
                  <span class="badge px-2.5 py-1.5 fw-bold" style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a;">
                    ⚡ ৳ {{ number_format($pay['electricity_bill'], 2) }}
                  </span>
                @else
                  <span class="text-secondary fw-medium small">৳0.00</span>
                @endif
              </td>
              <td class="text-end fw-bold">
                @if(isset($pay['carried_forward_due']) && $pay['carried_forward_due'] > 0)
                  <span class="text-danger fw-bold">+৳{{ number_format($pay['carried_forward_due'], 2) }}</span>
                @else
                  <span class="text-secondary fw-medium">৳0.00</span>
                @endif
              </td>
              <td class="text-end fw-bold" style="color: #15803d;">৳{{ number_format($pay['paid_amount'], 2) }}</td>
              <td class="text-end fw-bolder" style="color: #dc2626;">
                @if($pay['status'] === 'paid')
                  <span class="text-success fw-bold">৳0.00</span>
                @else
                  ৳{{ number_format($pay['due_amount'], 2) }}
                @endif
              </td>
              <td class="text-center">
                @if($pay['status'] === 'paid')
                  <span class="badge px-3 py-2 rounded-pill fw-bold" style="background: #dcfce7; color: #166534; border: 1px solid #86efac;">Paid</span>
                @elseif($pay['status'] === 'partial')
                  <span class="badge px-3 py-2 rounded-pill fw-bold" style="background: #fef9c3; color: #854d0e; border: 1px solid #fde047;">Partial</span>
                @elseif($pay['status'] === 'overdue')
                  <span class="badge px-3 py-2 rounded-pill fw-bold" style="background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5;">Overdue</span>
                @else
                  <span class="badge px-3 py-2 rounded-pill fw-bold" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;">Pending</span>
                @endif
              </td>
              <td>
                @if($pay['note'])
                  <div class="small text-dark fw-medium" style="max-width: 250px; overflow: hidden; text-overflow: ellipsis;" title="{{ $pay['note'] }}">
                    <i class="ti ti-notes me-1 text-primary"></i> {{ collect(explode("\n", $pay['note']))->last() }}
                  </div>
                @endif
                @if($pay['received_by'])
                  <div class="small text-success fw-semibold mt-1">
                    <i class="ti ti-user-check me-1"></i>{{ $pay['received_by'] }}
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

      {{-- Mobile Card View (Distinct Div Blocks with High-Contrast Colors) --}}
      <div class="d-md-none p-2 p-sm-3" style="background: #f1f5f9;">
        @foreach($payments as $idx => $pay)
        <div class="card shadow-sm mb-3.5 rounded-4 overflow-hidden border-0" style="box-shadow: 0 4px 14px rgba(0,0,0,0.08) !important;">
          
          {{-- Header Div --}}
          <div class="py-2.5 px-3 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff;">
            <div class="d-flex align-items-center gap-2">
              <span class="badge bg-primary text-white rounded-circle fw-bold" style="width:24px; height:24px; display:inline-flex; align-items:center; justify-content:center; font-size:11px;">{{ $idx + 1 }}</span>
              <span class="fw-bold text-white fs-6">{{ \Carbon\Carbon::parse($pay['payment_month'] . '-01')->format('F Y') }}</span>
            </div>
            <div>
              @if($pay['status'] === 'paid')
                <span class="badge px-2.5 py-1 rounded-pill fw-bold" style="background: #22c55e; color: #ffffff;">✅ Paid</span>
              @elseif($pay['status'] === 'partial')
                <span class="badge px-2.5 py-1 rounded-pill fw-bold" style="background: #eab308; color: #000000;">⏳ Partial</span>
              @elseif($pay['status'] === 'overdue')
                <span class="badge px-2.5 py-1 rounded-pill fw-bold" style="background: #ef4444; color: #ffffff;">🔴 Overdue</span>
              @else
                <span class="badge px-2.5 py-1 rounded-pill fw-bold" style="background: #64748b; color: #ffffff;">Pending</span>
              @endif
            </div>
          </div>

          {{-- Body Div --}}
          <div class="card-body p-3 bg-white">
            
            {{-- Room & Floor Div --}}
            <div class="p-2.5 rounded-3 mb-3 d-flex align-items-center justify-content-between" style="background: #f8fafc; border: 1.5px solid #e2e8f0;">
              <div>
                <small class="text-secondary d-block fw-semibold" style="font-size:11px;">রুম ও সিট (Room & Seat)</small>
                <span class="fw-bold text-dark fs-6"><i class="ti ti-door me-1 text-primary"></i>{{ $pay['roomnumber'] ?: '—' }}</span>
              </div>
              <div class="text-end">
                <small class="text-secondary d-block fw-semibold" style="font-size:11px;">ফ্লোর (Floor)</small>
                <span class="fw-bold text-dark fs-6"><i class="ti ti-building me-1 text-primary"></i>{{ $pay['floornumber'] }}</span>
              </div>
            </div>

            {{-- 4 Separate Distinct Metric Divs --}}
            <div class="row g-2 text-start">
              
              {{-- 1. Monthly Rent Div --}}
              <div class="col-6">
                <div class="p-2.5 rounded-3 h-100" style="background: #eff6ff; border: 1.5px solid #bfdbfe;">
                  <span class="d-block fw-bold" style="color: #1e40af; font-size: 11px;">মাসিক ভাড়া (Rent)</span>
                  <div class="fw-bolder mt-1" style="color: #1e3a8a; font-size: 15px;">৳{{ number_format($pay['amount'], 2) }}</div>
                </div>
              </div>

              {{-- 2. Electricity Bill Div --}}
              <div class="col-6">
                <div class="p-2.5 rounded-3 h-100" style="background: #fffbeb; border: 1.5px solid #fde68a;">
                  <span class="d-block fw-bold" style="color: #b45309; font-size: 11px;">⚡ বিদ্যুৎ বিল (Elect.)</span>
                  <div class="fw-bolder mt-1" style="color: #92400e; font-size: 15px;">
                    @if($pay['electricity_bill'] > 0)
                      ৳{{ number_format($pay['electricity_bill'], 2) }}
                    @else
                      ৳0.00
                    @endif
                  </div>
                </div>
              </div>

              {{-- 3. Prev Due Div --}}
              <div class="col-6">
                <div class="p-2.5 rounded-3 h-100" style="background: #fef2f2; border: 1.5px solid #fecaca;">
                  <span class="d-block fw-bold" style="color: #b91c1c; font-size: 11px;">পূর্বের জের (Prev Due)</span>
                  <div class="fw-bolder mt-1" style="color: #991b1b; font-size: 15px;">
                    @if(isset($pay['carried_forward_due']) && $pay['carried_forward_due'] > 0)
                      +৳{{ number_format($pay['carried_forward_due'], 2) }}
                    @else
                      ৳0.00
                    @endif
                  </div>
                </div>
              </div>

              {{-- 4. Paid Amount Div --}}
              <div class="col-6">
                <div class="p-2.5 rounded-3 h-100" style="background: #f0fdf4; border: 1.5px solid #bbf7d0;">
                  <span class="d-block fw-bold" style="color: #15803d; font-size: 11px;">পরিশোধ (Paid)</span>
                  <div class="fw-bolder mt-1" style="color: #166534; font-size: 15px;">৳{{ number_format($pay['paid_amount'], 2) }}</div>
                </div>
              </div>

            </div>

            {{-- Net Due Highlighted Div --}}
            <div class="p-3 rounded-3 mt-3 d-flex justify-content-between align-items-center" style="background: #fff1f2; border: 2px solid #fca5a5;">
              <div>
                <span class="d-block fw-bold" style="color: #9f1239; font-size: 13px;">মোট বকেয়া (Net Due):</span>
                <small style="color: #be123c; font-size: 11px;">বর্তমান মাস সহ সর্বমোট</small>
              </div>
              <div class="text-end">
                <span class="fw-bolder fs-4" style="color: #e11d48;">
                  {{ $pay['status'] === 'paid' ? '৳0.00' : '৳' . number_format($pay['due_amount'], 2) }}
                </span>
              </div>
            </div>

            {{-- Notes & Received By Div (if any) --}}
            @if($pay['note'] || $pay['received_by'])
            <div class="p-2.5 rounded-3 mt-2.5" style="background: #f8fafc; border: 1px dashed #cbd5e1;">
              @if($pay['note'])
                <div class="text-dark small"><i class="ti ti-notes me-1 text-primary"></i> <strong>নোট:</strong> {{ collect(explode("\n", $pay['note']))->last() }}</div>
              @endif
              @if($pay['received_by'])
                <div class="mt-1 small" style="color: #15803d;"><i class="ti ti-user-check me-1"></i> <strong>রিসিভড:</strong> {{ $pay['received_by'] }}</div>
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
