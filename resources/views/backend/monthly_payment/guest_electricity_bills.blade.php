@extends('backend.layouts.app')
@section('title', 'My Electricity Bills (বিদ্যুৎ বিল বিবরণী)')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

  <!-- Header Banner -->
  <div class="card border-0 shadow-sm mb-4 bg-white" style="border-left: 5px solid #f59e0b !important; border-radius: 12px;">
    <div class="card-body p-3 p-md-4">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
          <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
            <span class="p-2 rounded-circle bg-warning bg-opacity-15 text-warning d-inline-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
              <i class="ti ti-bolt fs-2"></i>
            </span>
            <span>বিদ্যুৎ বিল বিবরণী ও স্টেটমেন্ট (My Electricity Bills)</span>
          </h4>
          <p class="text-muted small mb-0 ps-md-5">
            মাসভিত্তিক আপনার বিদ্যুৎ বিল, পেমেন্টের তারিখ ও স্ট্যাটাস দেখুন।
          </p>
        </div>

        @if($userBooking)
        <div>
          <a href="{{ route('electricity-bills.print', array_merge([$userBooking->id], request()->all())) }}" 
             target="_blank" class="btn btn-primary fw-semibold px-3 py-2 shadow-sm d-inline-flex align-items-center gap-1.5"
             style="background: #033364; border-color: #033364;">
            <i class="ti ti-printer"></i>
            <span>প্রিন্ট স্টেটমেন্ট (Print Statement)</span>
          </a>
        </div>
        @endif
      </div>
    </div>
  </div>

  <!-- Summary Metric Cards -->
  <div class="row g-3 g-md-4 mb-4">
    
    <!-- Card 1: Current Month Bill -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 h-100" style="border-top: 4px solid #f59e0b !important;">
        <div class="card-body p-3.5">
          <div class="d-flex align-items-start justify-content-between">
            <div>
              <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.75rem;">বর্তমান মাসের বিল ({{ \Carbon\Carbon::now()->format('M Y') }})</span>
              <h3 class="mb-0 my-1 fw-bolder text-warning">
                ⚡ ৳ {{ number_format($currentMonthBill, 2) }}
              </h3>
              <small class="text-muted">Current Month Electricity</small>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-warning">
                <i class="ti ti-bolt fs-3"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Card 2: Total Electricity in Range -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 h-100" style="border-top: 4px solid #3b82f6 !important;">
        <div class="card-body p-3.5">
          <div class="d-flex align-items-start justify-content-between">
            <div>
              <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.75rem;">মোট বিল ({{ $rangeLabel }})</span>
              <h3 class="mb-0 my-1 fw-bolder text-primary">
                ৳ {{ number_format($totalElectricity, 2) }}
              </h3>
              <small class="text-muted">Filtered Range Total</small>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="ti ti-receipt fs-3"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Card 3: All-Time Lifetime Bill -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 h-100" style="border-top: 4px solid #10b981 !important;">
        <div class="card-body p-3.5">
          <div class="d-flex align-items-start justify-content-between">
            <div>
              <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.75rem;">সর্বমোট পরিশোধিত বিদ্যুৎ বিল</span>
              <h3 class="mb-0 my-1 fw-bolder text-success">
                ৳ {{ number_format($allTimeElectricity, 2) }}
              </h3>
              <small class="text-muted">Lifetime Electricity Total</small>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-success">
                <i class="ti ti-circle-check fs-3"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Card 4: Billed Months Count -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 h-100" style="border-top: 4px solid #6366f1 !important;">
        <div class="card-body p-3.5">
          <div class="d-flex align-items-start justify-content-between">
            <div>
              <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.75rem;">বিল প্রাপ্ত মাসের সংখ্যা</span>
              <h3 class="mb-0 my-1 fw-bolder text-indigo" style="color: #4f46e5;">
                {{ $activeBillCount }} টি মাস
              </h3>
              <small class="text-muted">Months with Bill Record</small>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded" style="background: #eef2ff; color: #4f46e5;">
                <i class="ti ti-calendar-event fs-3"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- Filter Controls -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3 bg-light rounded-3">
      <form method="GET" action="{{ route('dashboard.my-electricity-bills') }}" class="row g-2 align-items-center">
        
        <div class="col-12 col-md-auto d-flex align-items-center gap-2">
          <i class="ti ti-filter text-primary"></i>
          <span class="fw-bold small text-dark">ফিল্টার করুন:</span>
        </div>

        <!-- Year Selector -->
        <div class="col-6 col-md-3">
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-white">সাল (Year):</span>
            <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
              @foreach($yearsList as $yr)
                <option value="{{ $yr }}" {{ $yr == $selectedYear ? 'selected' : '' }}>{{ $yr }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <!-- Month Selector -->
        <div class="col-6 col-md-4">
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-white">মাস (Month):</span>
            <select name="month" class="form-select form-select-sm" onchange="this.form.submit()">
              <option value="all" {{ !request()->has('month') || request('month') === 'all' ? 'selected' : '' }}>সকল মাস (All Months)</option>
              <option value="1" {{ request('month') == '1' ? 'selected' : '' }}>01 - January</option>
              <option value="2" {{ request('month') == '2' ? 'selected' : '' }}>02 - February</option>
              <option value="3" {{ request('month') == '3' ? 'selected' : '' }}>03 - March</option>
              <option value="4" {{ request('month') == '4' ? 'selected' : '' }}>04 - April</option>
              <option value="5" {{ request('month') == '5' ? 'selected' : '' }}>05 - May</option>
              <option value="6" {{ request('month') == '6' ? 'selected' : '' }}>06 - June</option>
              <option value="7" {{ request('month') == '7' ? 'selected' : '' }}>07 - July</option>
              <option value="8" {{ request('month') == '8' ? 'selected' : '' }}>08 - August</option>
              <option value="9" {{ request('month') == '9' ? 'selected' : '' }}>09 - September</option>
              <option value="10" {{ request('month') == '10' ? 'selected' : '' }}>10 - October</option>
              <option value="11" {{ request('month') == '11' ? 'selected' : '' }}>11 - November</option>
              <option value="12" {{ request('month') == '12' ? 'selected' : '' }}>12 - December</option>
            </select>
          </div>
        </div>

        <!-- Filter Submit Button -->
        <div class="col-12 col-md-auto d-flex gap-2 ms-auto">
          <button type="submit" class="btn btn-sm btn-primary px-3">
            <i class="ti ti-search me-1"></i> দেখুন
          </button>
          @if(request()->has('month') && request('month') !== 'all')
            <a href="{{ route('dashboard.my-electricity-bills', ['year' => $selectedYear]) }}" class="btn btn-sm btn-outline-secondary" title="রিসেট">
              <i class="ti ti-rotate-clockwise"></i>
            </a>
          @endif
        </div>

      </form>
    </div>
  </div>

  <!-- Monthly Electricity Bill Table -->
  <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
        <i class="ti ti-bolt text-warning fs-4"></i>
        <span>মাসিক বিদ্যুৎ বিল স্টেটমেন্ট — <span class="text-primary">{{ $rangeLabel }}</span></span>
      </h5>
      <span class="badge bg-label-warning text-dark px-3 py-1.5 rounded-pill font-monospace fw-bold">
        Year: {{ $selectedYear }}
      </span>
    </div>

    <div class="table-responsive text-nowrap">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr class="small text-muted fw-bold text-uppercase">
            <th class="ps-4" style="width: 60px;">#</th>
            <th>বিলের মাস (MONTH & YEAR)</th>
            <th class="text-center">বিদ্যুৎ বিল (ELECTRICITY BILL)</th>
            <th>পেমেন্ট তারিখ (PAYMENT DATE)</th>
            <th class="text-center">স্ট্যাটাস (STATUS)</th>
          </tr>
        </thead>
        <tbody>
          @forelse($monthlyLedger as $idx => $item)
          <tr style="{{ $item['is_current_month'] ? 'background-color: #f0fdf4;' : ($item['has_bill'] ? 'background-color: #fffdf5;' : '') }}">
            <td class="ps-4 text-muted fw-bold">{{ $idx + 1 }}</td>
            
            <td class="fw-bold text-dark">
              {{ $item['month_year_label'] }}
              @if($item['is_current_month'])
                <span class="badge bg-success text-white ms-2 px-2 py-0.5 rounded-pill shadow-xs" style="font-size: 0.72rem; font-weight: 600;">
                  <i class="ti ti-circle-filled text-white me-1" style="font-size: 6px;"></i>Active Month
                </span>
              @endif
            </td>

            <td class="text-center">
              @if($item['electricity_bill'] > 0)
                <span class="badge bg-warning text-dark px-3 py-1.5 fs-7 fw-bold shadow-xs border border-warning">
                  ⚡ ৳ {{ number_format($item['electricity_bill'], 2) }}
                </span>
              @else
                <span class="text-muted small">৳ 0.00</span>
              @endif
            </td>

            <td class="text-muted">
              {{ $item['payment_date'] }}
            </td>

            <td class="text-center">
              @if($item['status'] === 'paid')
                <span class="badge bg-label-success px-3 py-1.5 rounded-pill fw-bold">
                  ✅ Paid
                </span>
              @elseif($item['status'] === 'partial')
                <span class="badge bg-label-warning text-dark px-3 py-1.5 rounded-pill fw-bold">
                  ⏳ Partial
                </span>
              @elseif($item['status'] === 'pending')
                <span class="badge bg-label-danger px-3 py-1.5 rounded-pill fw-bold">
                  ❌ Pending
                </span>
              @else
                <span class="text-muted">—</span>
              @endif
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="text-center py-5 text-muted">
              কোনো বিল রেকর্ড পাওয়া যায়নি।
            </td>
          </tr>
          @endforelse
        </tbody>

        @if(count($monthlyLedger) > 0)
        <tfoot>
          <tr class="table-warning fw-bold" style="border-top: 2px solid #f59e0b;">
            <td colspan="2" class="ps-4 text-dark fs-6">
              <i class="ti ti-calculator me-1 text-warning"></i> সর্বমোট বিদ্যুৎ বিল (Total Electricity Bill - {{ $rangeLabel }}):
            </td>
            <td class="text-center py-3">
              <span class="badge bg-warning text-dark px-3.5 py-2 fs-6 fw-bolder shadow-sm border border-dark">
                ⚡ ৳ {{ number_format($totalElectricity, 2) }}
              </span>
            </td>
            <td colspan="2" class="text-muted small text-end pe-4">
              Electricity Bill Summary Statement
            </td>
          </tr>
        </tfoot>
        @endif
      </table>
    </div>

    <div class="card-footer bg-light border-top d-flex justify-content-between align-items-center py-3 px-4">
      <small class="text-muted">
        <i class="ti ti-info-circle me-1"></i> বিদ্যুৎ বিল সংক্রান্ত কোনো তথ্যের জন্য এডমিন বা মেস ম্যানেজারের সাথে যোগাযোগ করুন।
      </small>
      @if($userBooking)
      <a href="{{ route('electricity-bills.print', array_merge([$userBooking->id], request()->all())) }}" target="_blank" class="btn btn-sm btn-outline-primary fw-semibold">
        <i class="ti ti-printer me-1"></i> প্রিন্ট কপি ডাউনলোড / প্রিন্ট
      </a>
      @endif
    </div>

  </div>

</div>
@endsection
