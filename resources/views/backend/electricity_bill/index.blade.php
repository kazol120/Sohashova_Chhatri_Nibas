@extends('backend.layouts.app')
@section("title") | Electricity Bills & Resident History @endsection

@push('style')
<style>
  .elect-table th {
    font-size: 0.82rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 700;
  }
  .avatar-sm {
    width: 40px !important;
    height: 40px !important;
    min-width: 40px !important;
    max-width: 40px !important;
    max-height: 40px !important;
    object-fit: cover !important;
    border-radius: 50% !important;
    border: 2px solid #033364 !important;
  }
  .stat-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  .stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.06);
  }
  .history-badge-active {
    background-color: #dcfce7;
    color: #166534;
    border: 1px solid #86efac;
  }
  .history-badge-released {
    background-color: #f1f5f9;
    color: #475569;
    border: 1px solid #cbd5e1;
  }
  .history-badge-deleted {
    background-color: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
  }
  .badge-bolt-glow {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: #fff;
    font-weight: 700;
  }
  .modal-header-elect {
    background: linear-gradient(135deg, #033364 0%, #0f172a 100%);
    color: #ffffff;
  }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  
  <!-- Header & Title -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
      <h4 class="fw-bold mb-1" style="color: #033364;">
        <i class="ti ti-bolt text-warning me-2"></i>বিদ্যুৎ বিল তালিকা (Electricity Bills & History)
      </h4>
      <p class="text-muted mb-0 small">
        রেসিডেন্টদের মাসিক বিদ্যুৎ বিল আদায়ের রেকর্ড, মাসিক হিস্ট্রি ও সার্বিক লেজার স্টেটমেন্ট।
      </p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <button type="button" class="btn btn-sm btn-warning fw-semibold d-flex align-items-center gap-1 shadow-sm" onclick="openBulkBillModal()">
        <i class="ti ti-bolt"></i> ⚡ বিদ্যুৎ বিল তৈরি করুন
      </button>
      <a href="{{ route('monthly-payments.index') }}" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1 shadow-sm">
        <i class="ti ti-cash-banknote"></i> মাসিক ভাড়া কালেকশন
      </a>
    </div>
  </div>

  <!-- Stat Cards -->
  <div class="row g-3 mb-4">
    <!-- Total Registered -->
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm stat-card h-100" style="border-left: 4px solid #033364 !important;">
        <div class="card-body d-flex align-items-center justify-content-between p-3">
          <div>
            <span class="text-muted fw-semibold small">Total Registered</span>
            <h3 class="mb-0 fw-bold mt-1 text-dark">{{ $totalCount }}</h3>
          </div>
          <div class="avatar avatar-md bg-label-primary rounded p-2 text-center">
            <i class="ti ti-users text-primary fs-3"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Active Residents -->
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm stat-card h-100" style="border-left: 4px solid #10b981 !important;">
        <div class="card-body d-flex align-items-center justify-content-between p-3">
          <div>
            <span class="text-muted fw-semibold small">Active Residents</span>
            <h3 class="mb-0 fw-bold mt-1 text-success">{{ $activeCount }}</h3>
          </div>
          <div class="avatar avatar-md bg-label-success rounded p-2 text-center">
            <i class="ti ti-user-check text-success fs-3"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Current / Selected Month Electricity Bill -->
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm stat-card h-100" style="border-left: 4px solid #f59e0b !important;">
        <div class="card-body d-flex align-items-center justify-content-between p-3">
          <div>
            <span class="text-muted fw-semibold small">Monthly Electricity Bill</span>
            <h3 class="mb-0 fw-bold mt-1 text-warning">৳ {{ number_format($totalCurrentMonthElectricity, 2) }}</h3>
            <small class="text-muted">{{ \Carbon\Carbon::parse(($selectedMonthStr ?? date('Y-m')) . '-01')->format('F Y') }}</small>
          </div>
          <div class="avatar avatar-md bg-label-warning rounded p-2 text-center">
            <i class="ti ti-bolt text-warning fs-3"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- All-Time Electricity Bill & Selected Month Payers -->
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm stat-card h-100" style="border-left: 4px solid #8b5cf6 !important;">
        <div class="card-body d-flex align-items-center justify-content-between p-3">
          <div>
            <span class="text-muted fw-semibold small">All-Time Electricity Bill</span>
            <h3 class="mb-0 fw-bold mt-1 text-purple" style="color: #8b5cf6;">৳ {{ number_format($totalAllTimeElectricity, 2) }}</h3>
            <small class="text-muted">{{ $totalBillPayersCount }} জন বিল দিয়েছেন ({{ \Carbon\Carbon::parse(($selectedMonthStr ?? date('Y-m')) . '-01')->format('M Y') }})</small>
          </div>
          <div class="avatar avatar-md bg-label-secondary rounded p-2 text-center">
            <i class="ti ti-wallet text-secondary fs-3"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Filters & Search Form -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
      <form id="electricityFilterForm" method="GET" action="{{ route('electricity-bills.index') }}" class="row g-2 align-items-center">
        <!-- Month Selector Dropdown -->
        <div class="col-6 col-md-2">
          <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="ti ti-calendar text-muted"></i></span>
            <select name="month" class="form-select border-start-0 ps-0 fw-bold text-primary" onchange="this.form.submit()">
              @foreach(($monthsList ?? []) as $mCode => $mName)
                <option value="{{ $mCode }}" {{ (int)($selectedMonthNum ?? date('m')) === (int)$mCode ? 'selected' : '' }}>
                  {{ $mName }}
                </option>
              @endforeach
            </select>
          </div>
        </div>

        <!-- Year Selector Dropdown -->
        <div class="col-6 col-md-2">
          <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="ti ti-calendar-event text-muted"></i></span>
            <select name="year" class="form-select border-start-0 ps-0 fw-bold text-primary" onchange="this.form.submit()">
              @foreach(($yearsList ?? []) as $yr)
                <option value="{{ $yr }}" {{ (int)($selectedYear ?? date('Y')) === (int)$yr ? 'selected' : '' }}>
                  {{ $yr }}
                </option>
              @endforeach
            </select>
          </div>
        </div>

        <!-- Instant Auto Search Input -->
        <div class="col-12 col-md-3">
          <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="ti ti-search text-muted"></i></span>
            <input type="text" name="search" class="form-control border-start-0 ps-0" 
                   placeholder="Search name, phone, room..." 
                   value="{{ request('search') }}"
                   oninput="debounceElectricitySearch(this.form)">
          </div>
        </div>

        <!-- Status Filter Buttons -->
        <div class="col-12 col-md-4">
          <div class="btn-group w-100 flex-wrap" role="group">
            <a href="{{ route('electricity-bills.index', array_merge(request()->except('filter', 'status'), ['filter' => 'all'])) }}" 
               class="btn btn-sm {{ ($filter ?? 'all') === 'all' ? 'btn-primary fw-bold' : 'btn-outline-primary' }}">
              All ({{ $totalCount }})
            </a>
            <a href="{{ route('electricity-bills.index', array_merge(request()->except('filter', 'status'), ['filter' => 'active'])) }}" 
               class="btn btn-sm {{ ($filter ?? '') === 'active' ? 'btn-success fw-bold' : 'btn-outline-success' }}">
              Active ({{ $activeCount }})
            </a>
            <a href="{{ route('electricity-bills.index', array_merge(request()->except('filter', 'status'), ['filter' => 'pending'])) }}" 
               class="btn btn-sm {{ ($filter ?? '') === 'pending' ? 'btn-warning fw-bold text-dark' : 'btn-outline-warning text-dark' }}">
              Pending ({{ $pendingCount }})
            </a>
            <a href="{{ route('electricity-bills.index', array_merge(request()->except('filter', 'status'), ['filter' => 'released'])) }}" 
               class="btn btn-sm {{ ($filter ?? '') === 'released' ? 'btn-secondary fw-bold' : 'btn-outline-secondary' }}">
              Released ({{ $releasedCount }})
            </a>
            <a href="{{ route('electricity-bills.index', array_merge(request()->except('filter', 'status'), ['filter' => 'deleted'])) }}" 
               class="btn btn-sm {{ ($filter ?? '') === 'deleted' || ($filter ?? '') === 'inactive' ? 'btn-danger fw-bold' : 'btn-outline-danger' }}">
              Deleted ({{ $deletedCount }})
            </a>
          </div>
        </div>

        <!-- Reset Button -->
        <div class="col-12 col-md-1 text-end">
          @if(request()->has('search') || request()->has('filter') || request()->has('status') || request()->has('month') || request()->has('year'))
            <a href="{{ route('electricity-bills.index') }}" class="btn btn-outline-secondary btn-sm w-100" title="Reset Filters">
              <i class="ti ti-rotate-clockwise"></i> Reset
            </a>
          @endif
        </div>
      </form>
    </div>
  </div>

  <script>
    let electSearchTimer;
    function debounceElectricitySearch(form) {
      clearTimeout(electSearchTimer);
      electSearchTimer = setTimeout(() => {
        form.submit();
      }, 450);
    }
  </script>

  <!-- Resident List Table -->
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0 fw-bold" style="color: #033364;">
        <i class="ti ti-list-details me-2"></i>বিদ্যুৎ বিল বিবরণী ও রেসিডেন্ট তালিকা
      </h5>
      <span class="badge bg-label-primary px-3 py-2 rounded-pill font-monospace" style="font-size: 0.85rem;">
        Total Displayed: {{ count($residents) }}
      </span>
    </div>

    <div class="table-responsive text-nowrap">
      <table class="table table-hover align-middle elect-table mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-3" style="width: 50px;">#</th>
            <th>Resident Profile & Name</th>
            <th>Phone & Login ID</th>
            <th>Room & Seat Details</th>
            <th class="text-center">Monthly Bill ({{ \Carbon\Carbon::parse(($selectedMonthStr ?? date('Y-m')) . '-01')->format('M Y') }})</th>
            <th class="text-center">Lifetime Bill</th>
            <th>Status</th>
            <th class="text-center pe-3" style="width: 170px;">Bill History & Print</th>
          </tr>
        </thead>
        <tbody>
          @forelse($residents as $idx => $resident)
          <tr>
            <td class="ps-3 text-muted fw-bold">{{ $idx + 1 }}</td>
            
            <!-- Resident Profile -->
            <td>
              <div class="d-flex align-items-center gap-2.5">
                @if(!empty($resident->image) && file_exists(public_path('bookingsimage/' . $resident->image)))
                  <img src="{{ asset('bookingsimage/' . $resident->image) }}" alt="Avatar" class="avatar-sm shadow-sm">
                @elseif(!empty($resident->image) && file_exists(public_path('storage/user/' . $resident->image)))
                  <img src="{{ asset('storage/user/' . $resident->image) }}" alt="Avatar" class="avatar-sm shadow-sm">
                @else
                  <div class="avatar avatar-sm bg-label-primary rounded-circle d-flex align-items-center justify-content-center text-primary fw-bold" style="width:40px;height:40px;min-width:40px;">
                    {{ mb_substr($resident->name, 0, 1) }}
                  </div>
                @endif
                <div>
                  <div class="fw-bold text-dark fs-6">{{ $resident->name }}</div>
                  <div class="d-flex align-items-center gap-2 mt-0.5">
                    <span class="badge bg-label-info" style="font-size: 11px;">{{ $resident->user_type }}</span>
                    <span class="text-muted small" style="font-size: 11.5px;">{{ $resident->email }}</span>
                  </div>
                </div>
              </div>
            </td>

            <!-- Phone & Login -->
            <td>
              <div class="fw-bold text-dark font-monospace">{{ $resident->phone }}</div>
              <div class="small text-muted font-monospace" style="font-size: 11.5px;">
                Login Code: <strong class="text-primary">{{ $resident->login_code }}</strong>
              </div>
            </td>

            <!-- Room & Seat -->
            <td>
              <div class="fw-bold text-dark">{{ $resident->room_seat_full }}</div>
            </td>

            <!-- Current Month Bill -->
            <td class="text-center" id="col-current-bill-{{ $resident->id }}">
              <div class="d-flex align-items-center justify-content-center gap-1">
                @if($resident->current_month_bill > 0)
                  <button type="button" 
                          class="btn btn-sm btn-warning text-dark px-2.5 py-1.5 fs-7 fw-bold shadow-xs border border-warning d-flex align-items-center gap-1"
                          onclick="openSingleBillUpdateModal({{ $resident->id }}, '{{ addslashes($resident->name) }}', '{{ addslashes($resident->room_seat_full) }}', {{ $resident->current_month_bill }}, '{{ \Carbon\Carbon::now()->format('Y-m') }}', '{{ \Carbon\Carbon::now()->format('F Y') }}')"
                          title="বিদ্যুৎ বিল এডিট বা পরিবর্তন করতে ক্লিক করুন">
                    <span>⚡ ৳ {{ number_format($resident->current_month_bill, 2) }}</span>
                    <i class="ti ti-edit fs-7 ms-0.5"></i>
                  </button>
                @else
                  <button type="button" 
                          class="btn btn-sm btn-outline-warning text-dark px-2 py-1 fs-7 fw-semibold d-flex align-items-center gap-1"
                          onclick="openSingleBillUpdateModal({{ $resident->id }}, '{{ addslashes($resident->name) }}', '{{ addslashes($resident->room_seat_full) }}', 0, '{{ \Carbon\Carbon::now()->format('Y-m') }}', '{{ \Carbon\Carbon::now()->format('F Y') }}')"
                          title="চলতি মাসের বিদ্যুৎ বিল যুক্ত করুন">
                    <i class="ti ti-plus"></i> Add Bill
                  </button>
                @endif
              </div>
            </td>

            <!-- Lifetime Bill -->
            <td class="text-center" id="col-lifetime-bill-{{ $resident->id }}">
              @if($resident->lifetime_electricity_bill > 0)
                <span class="badge bg-label-warning text-dark px-2.5 py-1.5 fs-7 fw-bold">
                  ⚡ ৳ {{ number_format($resident->lifetime_electricity_bill, 2) }}
                </span>
              @else
                <span class="text-muted small">৳ 0.00</span>
              @endif
            </td>

            <!-- Status -->
            <td>
              @if($resident->is_deleted)
                <span class="badge history-badge-deleted px-2.5 py-1.5 rounded-pill fw-semibold">
                  <i class="ti ti-trash me-1"></i>Deleted
                </span>
              @elseif($resident->is_released)
                <span class="badge history-badge-released px-2.5 py-1.5 rounded-pill fw-semibold">
                  <i class="ti ti-check me-1"></i>Released
                </span>
              @elseif($resident->is_pending)
                <span class="badge px-2.5 py-1.5 rounded-pill fw-semibold" style="background-color:#fef08a;color:#854d0e;border:1px solid #fde047;">
                  <i class="ti ti-clock me-1"></i>Pending
                </span>
              @else
                <span class="badge history-badge-active px-2.5 py-1.5 rounded-pill fw-semibold">
                  <i class="ti ti-circle-filled text-success me-1" style="font-size: 8px;"></i>Active
                </span>
              @endif
            </td>

            <!-- Actions -->
            <td class="text-center pe-3">
              <div class="d-flex justify-content-center gap-1.5">
                <button type="button" class="btn btn-sm btn-primary fw-semibold px-2.5 py-1.5 shadow-sm d-flex align-items-center gap-1"
                        onclick="loadElectricityDetails({{ $resident->id }})" title="View Monthly Electricity Bill History">
                  <i class="ti ti-bolt text-warning"></i> Bill History
                </button>
                <a href="{{ route('electricity-bills.print', $resident->id) }}" target="_blank" 
                   class="btn btn-sm btn-outline-secondary px-2 py-1.5" title="Print Electricity Bill Statement">
                  <i class="ti ti-printer"></i>
                </a>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="8" class="text-center py-5 text-muted">
              <div class="mb-2"><i class="ti ti-user-search fs-1 text-secondary"></i></div>
              <h6 class="text-muted fw-semibold">কোনো রেসিডেন্ট রেকর্ড পাওয়া যায়নি</h6>
              <p class="small text-muted mb-0">সার্চ ফিল্টার পরিবর্তন করে আবার চেষ্টা করুন।</p>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- ========================================== -->
<!-- BULK ELECTRICITY BILL CREATE MODAL -->
<!-- ========================================== -->
<div class="modal fade" id="bulkBillCreateModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow-lg">

      <!-- Modal Header -->
      <div class="modal-header py-3 px-4" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
        <div class="d-flex align-items-center gap-3">
          <div style="width:42px;height:42px;background:rgba(255,255,255,0.2);border-radius:12px;display:flex;align-items:center;justify-content:center;">
            <i class="ti ti-bolt text-white fs-4"></i>
          </div>
          <div>
            <h5 class="modal-title fw-bold text-white mb-0">⚡ বিদ্যুৎ বিল তৈরি করুন</h5>
            <small class="text-white" style="opacity:0.85;">মাস নির্বাচন করুন এবং প্রতিটি রেসিডেন্টের বিল পরিমাণ দিন</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Month Selector Bar -->
      <div class="border-bottom px-4 py-3" style="background:#fffbeb;">
        <div class="row g-2 align-items-center">
          <div class="col-12 col-md-auto d-flex align-items-center gap-2">
            <i class="ti ti-calendar text-warning fs-5"></i>
            <span class="fw-bold text-dark">মাস নির্বাচন করুন:</span>
          </div>
          <div class="col-6 col-md-3">
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-white fw-semibold">বছর:</span>
              <select class="form-select form-select-sm" id="bulkBillYear">
                @foreach($yearsList as $yr)
                  <option value="{{ $yr }}" {{ $yr == date('Y') ? 'selected' : '' }}>{{ $yr }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-6 col-md-4">
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-white fw-semibold">মাস:</span>
              <select class="form-select form-select-sm" id="bulkBillMonth">
                <option value="01" {{ date('m') == '01' ? 'selected' : '' }}>01 - January</option>
                <option value="02" {{ date('m') == '02' ? 'selected' : '' }}>02 - February</option>
                <option value="03" {{ date('m') == '03' ? 'selected' : '' }}>03 - March</option>
                <option value="04" {{ date('m') == '04' ? 'selected' : '' }}>04 - April</option>
                <option value="05" {{ date('m') == '05' ? 'selected' : '' }}>05 - May</option>
                <option value="06" {{ date('m') == '06' ? 'selected' : '' }}>06 - June</option>
                <option value="07" {{ date('m') == '07' ? 'selected' : '' }}>07 - July</option>
                <option value="08" {{ date('m') == '08' ? 'selected' : '' }}>08 - August</option>
                <option value="09" {{ date('m') == '09' ? 'selected' : '' }}>09 - September</option>
                <option value="10" {{ date('m') == '10' ? 'selected' : '' }}>10 - October</option>
                <option value="11" {{ date('m') == '11' ? 'selected' : '' }}>11 - November</option>
                <option value="12" {{ date('m') == '12' ? 'selected' : '' }}>12 - December</option>
              </select>
            </div>
          </div>
          <div class="col-12 col-md-auto">
            <button type="button" class="btn btn-sm btn-warning fw-semibold d-flex align-items-center gap-1" onclick="loadResidentsForBilling()">
              <i class="ti ti-search"></i> রেসিডেন্ট লোড করুন
            </button>
          </div>
        </div>
      </div>

      <!-- Modal Body -->
      <div class="modal-body p-0">

        <!-- Info bar (shown after load) -->
        <div id="bulkBillInfoBar" class="px-4 py-2 d-none" style="background:#f0fdf4;border-bottom:1px solid #bbf7d0;">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <span class="fw-semibold text-success" id="bulkBillInfoText"></span>
            
            <div class="d-flex align-items-center gap-2">
              <div class="input-group input-group-sm" style="width: 250px;">
                <span class="input-group-text bg-white"><i class="ti ti-search text-muted"></i></span>
                <input type="text" id="bulkBillSearchInput" class="form-control" placeholder="Search resident..." onkeyup="filterBulkBillResidents()">
              </div>
              <span class="badge bg-warning text-dark px-3 py-1 fw-bold" id="bulkBillMonthLabel"></span>
            </div>
          </div>
        </div>

        <!-- Table -->
        <div class="table-responsive" style="max-height:55vh;overflow-y:auto;">
          <table class="table table-hover align-middle mb-0" id="bulkBillTable">
            <thead class="table-light sticky-top" style="z-index:1;">
              <tr class="small text-muted fw-bold text-uppercase">
                <th class="ps-4" style="width:50px;">#</th>
                <th>রেসিডেন্ট প্রোফাইল</th>
                <th>ফোন</th>
                <th>রুম</th>
                <th class="text-center">Lifetime Bill</th>
                <th class="text-center" style="width:200px;">বিদ্যুৎ বিল পরিমাণ (৳) <span class="text-danger">*</span></th>
              </tr>
            </thead>
            <tbody id="bulkBillTbody">
              <tr>
                <td colspan="6" class="text-center py-5 text-muted">
                  <i class="ti ti-calendar-search fs-1 text-secondary d-block mb-2"></i>
                  <span class="fw-semibold">উপরে মাস নির্বাচন করে "রেসিডেন্ট লোড করুন" বাটনে ক্লিক করুন।</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="modal-footer py-2 px-4" style="background:#fafafa;border-top:1px solid #e5e7eb;">
        <div class="d-flex align-items-center justify-content-between w-100 flex-wrap gap-2">
          <small class="text-muted">
            <i class="ti ti-info-circle me-1"></i>
            Amount ফাঁকা রাখলে সেই রেসিডেন্টের বিল তৈরি হবে না (skip)।
          </small>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">বাতিল করুন</button>
            <button type="button" class="btn btn-warning btn-sm fw-bold d-flex align-items-center gap-1" id="bulkBillSaveBtn" onclick="submitBulkBills()" disabled>
              <span id="bulkBillSaveSpinner" class="spinner-border spinner-border-sm d-none"></span>
              <i class="ti ti-bolt" id="bulkBillSaveIcon"></i>
              <span id="bulkBillSaveText">বিল সেভ করুন</span>
            </button>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- ========================================== -->
<!-- ELECTRICITY BILL 360 MODAL (LIKE RESIDENT HISTORY) -->
<!-- ========================================== -->
<div class="modal fade" id="electricityBillModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow-lg">
      
      <!-- Modal Header -->
      <div class="modal-header modal-header-elect py-3 px-4 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
          <div id="modalAvatarBox">
            <div class="spinner-border spinner-border-sm text-light"></div>
          </div>
          <div>
            <h5 class="modal-title fw-bold text-white mb-0" id="modalResidentName">Resident Name</h5>
            <small class="text-white-50" id="modalResidentSub">Phone: — | Room: — | Status: Active</small>
          </div>
        </div>
        <div class="d-flex align-items-center gap-2">
          <div id="modalPrintBtnBox"></div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      </div>

      <!-- Filter Controls Bar in Modal -->
      <div class="bg-light border-bottom px-4 py-2.5">
        <div class="row g-2 align-items-center">
          <div class="col-12 col-md-auto d-flex align-items-center gap-2">
            <i class="ti ti-calendar text-primary"></i>
            <span class="fw-bold small text-dark">Filter:</span>
          </div>

          <!-- Year Selection -->
          <div class="col-6 col-md-3">
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-white">Year:</span>
              <select class="form-select form-select-sm" id="modalFilterYear" onchange="refreshElectricityModal()">
                @foreach($yearsList as $yr)
                  <option value="{{ $yr }}" {{ $yr == (int)date('Y') ? 'selected' : '' }}>Year {{ $yr }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <!-- Month Mode -->
          <div class="col-6 col-md-4">
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-white">Month:</span>
              <select class="form-select form-select-sm" id="modalFilterMonthMode" onchange="refreshElectricityModal()">
                <option value="all" selected>All Months (12 Month)</option>
                <option value="1">01 - January</option>
                <option value="2">02 - February</option>
                <option value="3">03 - March</option>
                <option value="4">04 - April</option>
                <option value="5">05 - May</option>
                <option value="6">06 - June</option>
                <option value="7">07 - July</option>
                <option value="8">08 - August</option>
                <option value="9">09 - September</option>
                <option value="10">10 - October</option>
                <option value="11">11 - November</option>
                <option value="12">12 - December</option>
              </select>
            </div>
          </div>

          <!-- Apply Filter Button -->
          <div class="col-12 col-md-auto">
            <button type="button" class="btn btn-sm btn-primary d-flex align-items-center gap-1" onclick="refreshElectricityModal()">
              <i class="ti ti-refresh"></i> Apply Filter
            </button>
          </div>
        </div>
      </div>

      <!-- Modal Body -->
      <div class="modal-body p-4">
        
        <div class="d-flex align-items-center justify-content-between mb-3">
          <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
            <i class="ti ti-bolt text-warning fs-4"></i>
            Monthly Electricity Bill & Charges Statement
          </h6>
          <span class="badge bg-label-warning text-dark px-3 py-1 fs-7 fw-bold" id="modalRangeLabel">
            Selected Period
          </span>
        </div>

        <div class="table-responsive border rounded-3">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr class="small text-muted fw-bold text-uppercase">
                <th>MONTH & YEAR</th>
                <th class="text-center">ELECTRICITY BILL (৳)</th>
                <th>PAYMENT DATE</th>
                <th class="text-center">STATUS</th>
              </tr>
            </thead>
            <tbody id="electricityModalTbody">
              <tr>
                <td colspan="4" class="text-center py-4 text-muted">
                  <div class="spinner-border spinner-border-sm text-primary me-2"></div> Loading electricity bill records...
                </td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>

      <!-- Modal Footer -->
      <div class="modal-footer py-2 px-4 bg-light border-top d-flex justify-content-between">
        <small class="text-muted">Electricity Bill Management System</small>
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>

<!-- ========================================== -->
<!-- QUICK UPDATE SINGLE ELECTRICITY BILL MODAL -->
<!-- ========================================== -->
<div class="modal fade" id="singleBillUpdateModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      
      <!-- Header -->
      <div class="modal-header py-3 px-4 rounded-top-4" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
        <div class="d-flex align-items-center gap-2">
          <div style="width:36px;height:36px;background:rgba(255,255,255,0.2);border-radius:10px;display:flex;align-items:center;justify-content:center;">
            <i class="ti ti-bolt text-white fs-4"></i>
          </div>
          <div>
            <h5 class="modal-title fw-bold text-white mb-0">বিদ্যুৎ বিল আপডেট (Update Electricity Bill)</h5>
            <small class="text-white-50">চলতি মাসের বিদ্যুৎ বিল পরিবর্তন বা এন্ট্রি করুন</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Body Form -->
      <form id="singleBillUpdateForm" onsubmit="submitSingleBillUpdate(event)">
        @csrf
        <input type="hidden" id="singleBillBookingId" name="booking_id" value="">
        <input type="hidden" id="singleBillMonth" name="month" value="">

        <div class="modal-body p-4">
          <!-- Resident Info Alert -->
          <div class="alert alert-warning border-0 d-flex flex-column gap-1 mb-3 rounded-3" style="background: #fef3c7; color: #92400e;">
            <div class="d-flex justify-content-between align-items-center">
              <span class="fw-bold fs-6" id="singleBillResidentName">Resident Name</span>
              <span class="badge bg-warning text-dark fw-bold" id="singleBillMonthLabel">October 2026</span>
            </div>
            <div class="small text-muted border-top pt-1 mt-1" id="singleBillResidentRoom">
              Room & Seat Details
            </div>
          </div>

          <!-- Electricity Bill Input Field -->
          <div class="mb-3">
            <label class="form-label fw-bold text-dark">
              Electricity Bill Amount / বিদ্যুৎ বিল (৳) <span class="text-danger">*</span>
            </label>
            <div class="input-group input-group-lg">
              <span class="input-group-text bg-white text-warning fw-bold border-2">
                <i class="ti ti-bolt"></i> ৳
              </span>
              <input type="number" 
                     step="0.01" 
                     min="0" 
                     class="form-control border-2 fw-bold text-dark fs-5" 
                     id="singleBillAmountInput" 
                     name="electricity_bill" 
                     placeholder="0.00" 
                     required>
            </div>
            <div class="form-text text-muted small mt-1">
              <i class="ti ti-info-circle me-1 text-primary"></i>এই বিলটি আপডেট করলে শিক্ষার্থীর মাসিক ভাড়া ও সর্বমোট বকেয়া স্বয়ংক্রিয়ভাবে হিসাব হয়ে যাবে।
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="modal-footer py-2 px-4 bg-light border-top d-flex justify-content-between rounded-bottom-4">
          <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning text-dark fw-bold btn-sm px-4 shadow-sm" id="singleBillSubmitBtn">
            <i class="ti ti-check me-1"></i> Save & Update Bill
          </button>
        </div>
      </form>

    </div>
  </div>
</div>
@endsection

@push('script')
<script>
let singleBillModalInstance = null;

function openSingleBillUpdateModal(bookingId, name, room, currentBill, month, monthLabel) {
  document.getElementById('singleBillBookingId').value = bookingId;
  document.getElementById('singleBillMonth').value = month || '{{ \Carbon\Carbon::now()->format("Y-m") }}';
  document.getElementById('singleBillResidentName').innerText = name || 'Resident';
  document.getElementById('singleBillResidentRoom').innerText = room || '—';
  document.getElementById('singleBillMonthLabel').innerText = monthLabel || '{{ \Carbon\Carbon::now()->format("F Y") }}';
  document.getElementById('singleBillAmountInput').value = Number(currentBill || 0).toFixed(2);

  const modalEl = document.getElementById('singleBillUpdateModal');
  if (!singleBillModalInstance) {
    singleBillModalInstance = new bootstrap.Modal(modalEl);
  }
  singleBillModalInstance.show();
  setTimeout(() => {
    document.getElementById('singleBillAmountInput').focus();
    document.getElementById('singleBillAmountInput').select();
  }, 400);
}

function submitSingleBillUpdate(e) {
  e.preventDefault();
  const form = document.getElementById('singleBillUpdateForm');
  const submitBtn = document.getElementById('singleBillSubmitBtn');
  const bookingId = document.getElementById('singleBillBookingId').value;
  const billAmount = parseFloat(document.getElementById('singleBillAmountInput').value) || 0;
  const month = document.getElementById('singleBillMonth').value;

  submitBtn.disabled = true;
  submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Updating...';

  const formData = new FormData(form);

  fetch('{{ route("electricity-bills.update-single") }}', {
    method: 'POST',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json'
    },
    body: formData
  })
  .then(res => res.json())
  .then(res => {
    submitBtn.disabled = false;
    submitBtn.innerHTML = '<i class="ti ti-check me-1"></i> Save & Update Bill';

    if (res.status) {
      if (singleBillModalInstance) {
        singleBillModalInstance.hide();
      }

      // Update Current Month column cell in table directly
      const colEl = document.getElementById('col-current-bill-' + bookingId);
      if (colEl) {
        if (billAmount > 0) {
          colEl.innerHTML = `
            <div class="d-flex align-items-center justify-content-center gap-1">
              <button type="button" 
                      class="btn btn-sm btn-warning text-dark px-2.5 py-1.5 fs-7 fw-bold shadow-xs border border-warning d-flex align-items-center gap-1"
                      onclick="openSingleBillUpdateModal(${bookingId}, '${document.getElementById('singleBillResidentName').innerText.replace(/'/g, "\\'")}', '${document.getElementById('singleBillResidentRoom').innerText.replace(/'/g, "\\'")}', ${billAmount}, '${month}', '${document.getElementById('singleBillMonthLabel').innerText}')"
                      title="বিদ্যুৎ বিল এডিট বা পরিবর্তন করতে ক্লিক করুন">
                <span>⚡ ৳ ${billAmount.toFixed(2)}</span>
                <i class="ti ti-edit fs-7 ms-0.5"></i>
              </button>
            </div>
          `;
        } else {
          colEl.innerHTML = `
            <div class="d-flex align-items-center justify-content-center gap-1">
              <button type="button" 
                      class="btn btn-sm btn-outline-warning text-dark px-2 py-1 fs-7 fw-semibold d-flex align-items-center gap-1"
                      onclick="openSingleBillUpdateModal(${bookingId}, '${document.getElementById('singleBillResidentName').innerText.replace(/'/g, "\\'")}', '${document.getElementById('singleBillResidentRoom').innerText.replace(/'/g, "\\'")}', 0, '${month}', '${document.getElementById('singleBillMonthLabel').innerText}')"
                      title="চলতি মাসের বিদ্যুৎ বিল যুক্ত করুন">
                <i class="ti ti-plus"></i> Add Bill
              </button>
            </div>
          `;
        }
      }

      // Update Lifetime Bill cell
      const lifeEl = document.getElementById('col-lifetime-bill-' + bookingId);
      if (lifeEl && res.lifetime_bill !== undefined) {
        lifeEl.innerHTML = `
          <span class="badge bg-label-warning text-dark px-2.5 py-1.5 fs-7 fw-bold">
            ⚡ ৳ ${Number(res.lifetime_bill).toFixed(2)}
          </span>
        `;
      }

      // Show notification
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          icon: 'success',
          title: 'সফল হয়েছে!',
          text: res.message || 'বিদ্যুৎ বিল সফলভাবে আপডেট করা হয়েছে!',
          timer: 2000,
          showConfirmButton: false,
          toast: true,
          position: 'top-end'
        });
      } else {
        alert(res.message || 'বিদ্যুৎ বিল সফলভাবে আপডেট করা হয়েছে!');
      }
    } else {
      alert(res.message || 'Failed to update bill.');
    }
  })
  .catch(err => {
    submitBtn.disabled = false;
    submitBtn.innerHTML = '<i class="ti ti-check me-1"></i> Save & Update Bill';
    console.error(err);
    alert('An error occurred while updating the bill.');
  });
}
// =============================================
// BULK ELECTRICITY BILL CREATE MODAL JS
// =============================================
let bulkBillResidents = [];

function openBulkBillModal() {
  bulkBillResidents = [];
  document.getElementById('bulkBillTbody').innerHTML = `
    <tr>
      <td colspan="6" class="text-center py-5 text-muted">
        <i class="ti ti-calendar-search fs-1 text-secondary d-block mb-2"></i>
        <span class="fw-semibold">উপরে মাস নির্বাচন করে "রেসিডেন্ট লোড করুন" বাটনে ক্লিক করুন।</span>
      </td>
    </tr>`;
  document.getElementById('bulkBillInfoBar').classList.add('d-none');
  document.getElementById('bulkBillSaveBtn').disabled = true;

  const modalEl = document.getElementById('bulkBillCreateModal');
  const bsModal = (window.bootstrap && bootstrap.Modal)
    ? (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl))
    : null;
  if (bsModal) bsModal.show();
  else $('#bulkBillCreateModal').modal('show');
}

function loadResidentsForBilling() {
  const year  = document.getElementById('bulkBillYear').value;
  const month = document.getElementById('bulkBillMonth').value;
  const ym    = `${year}-${month}`;

  // Month label
  const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
  const monthLabel = `${monthNames[parseInt(month)-1]} ${year}`;

  document.getElementById('bulkBillTbody').innerHTML = `
    <tr><td colspan="6" class="text-center py-4 text-muted">
      <div class="spinner-border spinner-border-sm text-warning me-2"></div> লোড হচ্ছে...
    </td></tr>`;
  document.getElementById('bulkBillInfoBar').classList.add('d-none');
  document.getElementById('bulkBillSaveBtn').disabled = true;

  fetch(`{{ route('electricity-bills.residents-for-billing') }}?month=${ym}`, {
    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(r => r.json())
  .then(res => {
    if (!res.status) {
      document.getElementById('bulkBillTbody').innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">${res.message || 'Error loading data.'}</td></tr>`;
      return;
    }

    bulkBillResidents = res.residents || [];

    const infoBar = document.getElementById('bulkBillInfoBar');
    const infoText = document.getElementById('bulkBillInfoText');
    const monthLabelEl = document.getElementById('bulkBillMonthLabel');
    infoBar.classList.remove('d-none');
    monthLabelEl.textContent = `⚡ ${monthLabel}`;

    const tbody = document.getElementById('bulkBillTbody');

    if (bulkBillResidents.length === 0) {
      tbody.innerHTML = `<tr><td colspan="6" class="text-center py-5 text-muted">
        <i class="ti ti-circle-check fs-1 text-success d-block mb-2"></i>
        <span class="fw-semibold text-success">${monthLabel} মাসে সব Active রেসিডেন্টের বিদ্যুৎ বিল ইতিমধ্যে তৈরি হয়েছে।</span>
      </td></tr>`;
      infoText.textContent = '✅ সকল রেসিডেন্টের বিল তৈরি আছে।';
      infoText.className = 'fw-semibold text-success';
      document.getElementById('bulkBillSaveBtn').disabled = true;
      return;
    }

    infoText.textContent = `${bulkBillResidents.length} জন রেসিডেন্টের এখনো বিল তৈরি হয়নি — নিচে Amount দিন:`;
    infoText.className = 'fw-semibold text-dark';

    tbody.innerHTML = '';
    bulkBillResidents.forEach((r, idx) => {
      const lifetimeBadge = r.lifetime_bill > 0
        ? `<span class="badge bg-warning text-dark px-2 py-1 fw-bold">⚡ ৳ ${Number(r.lifetime_bill).toLocaleString()}</span>`
        : `<span class="text-muted small">৳ 0.00</span>`;

      const avatarHtml = r.image
        ? `<img src="/bookingsimage/${r.image}" class="avatar-sm shadow-sm" onerror="this.src='/storage/user/${r.image}'">`
        : `<div class="avatar avatar-sm bg-label-warning rounded-circle d-flex align-items-center justify-content-center fw-bold text-warning" style="width:38px;height:38px;min-width:38px;border:2px solid #f59e0b;">${r.name.charAt(0)}</div>`;

      tbody.innerHTML += `
        <tr class="bulk-bill-row">
          <td class="ps-4 text-muted fw-bold">${idx + 1}</td>
          <td>
            <div class="d-flex align-items-center gap-2">
              ${avatarHtml}
              <div>
                <div class="fw-bold text-dark">${r.name}</div>
                <span class="badge bg-label-info" style="font-size:10px;">${r.user_type}</span>
              </div>
            </div>
          </td>
          <td class="font-monospace text-dark fw-semibold">${r.phone}</td>
          <td class="text-muted">${r.room_seat_full}</td>
          <td class="text-center">${lifetimeBadge}</td>
          <td class="text-center">
            <div class="input-group input-group-sm" style="max-width:160px;margin:0 auto;">
              <span class="input-group-text bg-warning text-dark fw-bold">৳</span>
              <input
                type="number"
                class="form-control bulk-bill-amount-input text-center fw-bold"
                data-booking-id="${r.id}"
                placeholder="পরিমাণ..."
                min="0"
                step="0.01"
                style="border:1.5px solid #f59e0b;"
              />
            </div>
          </td>
        </tr>`;
    });

    document.getElementById('bulkBillSaveBtn').disabled = false;
  })
  .catch(err => {
    console.error(err);
    document.getElementById('bulkBillTbody').innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">ডেটা লোড করতে ব্যর্থ হয়েছে।</td></tr>`;
  });
}

function filterBulkBillResidents() {
  const input = document.getElementById("bulkBillSearchInput");
  const filter = input.value.toLowerCase();
  const tbody = document.getElementById("bulkBillTbody");
  const trs = tbody.getElementsByTagName("tr");

  for (let i = 0; i < trs.length; i++) {
    // Skip empty state/loading rows which don't have the bulk-bill-row class
    if (!trs[i].classList.contains('bulk-bill-row')) continue;
    
    const textContent = trs[i].textContent || trs[i].innerText;
    if (textContent.toLowerCase().indexOf(filter) > -1) {
      trs[i].style.display = "";
    } else {
      trs[i].style.display = "none";
    }
  }
}

function submitBulkBills() {
  const year  = document.getElementById('bulkBillYear').value;
  const month = document.getElementById('bulkBillMonth').value;
  const ym    = `${year}-${month}`;

  const inputs = document.querySelectorAll('.bulk-bill-amount-input');
  const bills  = [];
  inputs.forEach(input => {
    bills.push({
      booking_id: input.dataset.bookingId,
      amount:     input.value.trim(),
    });
  });

  const hasSomething = bills.some(b => b.amount !== '' && parseFloat(b.amount) > 0);
  if (!hasSomething) {
    alert('অন্তত একজন রেসিডেন্টের পরিমাণ দিন।');
    return;
  }

  // UI loading state
  const btn      = document.getElementById('bulkBillSaveBtn');
  const spinner  = document.getElementById('bulkBillSaveSpinner');
  const icon     = document.getElementById('bulkBillSaveIcon');
  const txt      = document.getElementById('bulkBillSaveText');
  btn.disabled   = true;
  spinner.classList.remove('d-none');
  icon.classList.add('d-none');
  txt.textContent = 'সেভ হচ্ছে...';

  fetch('{{ route("electricity-bills.bulk-create") }}', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': '{{ csrf_token() }}',
    },
    body: JSON.stringify({ month: ym, bills })
  })
  .then(r => r.json())
  .then(res => {
    spinner.classList.add('d-none');
    icon.classList.remove('d-none');
    txt.textContent = 'বিল সেভ করুন';
    btn.disabled = false;

    if (res.status) {
      // Show success
      document.getElementById('bulkBillTbody').innerHTML = `
        <tr>
          <td colspan="6" class="text-center py-5">
            <div style="font-size:48px;">✅</div>
            <h5 class="text-success fw-bold mt-2">${res.message}</h5>
            <p class="text-muted">পেজ রিফ্রেশ করলে আপডেট দেখতে পাবেন।</p>
          </td>
        </tr>`;
      document.getElementById('bulkBillInfoBar').classList.add('d-none');
      document.getElementById('bulkBillSaveBtn').disabled = true;

      // Auto reload after 2s
      setTimeout(() => { window.location.reload(); }, 2000);
    } else {
      alert('❌ ' + (res.message || 'কিছু একটা সমস্যা হয়েছে।'));
    }
  })
  .catch(err => {
    console.error(err);
    spinner.classList.add('d-none');
    icon.classList.remove('d-none');
    txt.textContent = 'বিল সেভ করুন';
    btn.disabled = false;
    alert('সার্ভার এরর। আবার চেষ্টা করুন।');
  });
}
// =============================================
// END BULK BILL JS
// =============================================

let currentResidentId = null;

function loadElectricityDetails(id) {
  currentResidentId = id;

  try {
    const modalEl = document.getElementById('electricityBillModal');
    const bsModal = (window.bootstrap && bootstrap.Modal) 
      ? (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl)) 
      : null;
    if (bsModal) {
      bsModal.show();
    } else {
      $('#electricityBillModal').modal('show');
    }
  } catch (e) {
    console.error(e);
    $('#electricityBillModal').modal('show');
  }

  refreshElectricityModal();
}

function refreshElectricityModal() {
  if (!currentResidentId) return;

  const year = document.getElementById('modalFilterYear') ? document.getElementById('modalFilterYear').value : '2026';
  const mode = document.getElementById('modalFilterMonthMode') ? document.getElementById('modalFilterMonthMode').value : 'all';
  let fromMonth = 1;
  let toMonth = 12;

  if (mode !== 'all' && !isNaN(mode)) {
    fromMonth = parseInt(mode);
    toMonth = parseInt(mode);
  } else {
    fromMonth = 1;
    toMonth = 12;
  }

  // Loading states
  document.getElementById('modalResidentName').innerText = 'Loading...';
  document.getElementById('modalResidentSub').innerText = 'Please wait...';
  document.getElementById('modalAvatarBox').innerHTML = '<div class="spinner-border spinner-border-sm text-light"></div>';
  document.getElementById('electricityModalTbody').innerHTML = `
    <tr><td colspan="4" class="text-center py-4 text-muted">
      <div class="spinner-border spinner-border-sm text-primary me-2"></div> Loading records...
    </td></tr>`;

  fetch(`{{ url('/electricity-bills') }}/${currentResidentId}?format=json&year=${year}&from_month=${fromMonth}&to_month=${toMonth}`, {
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(res => res.json())
  .then(res => {
    if (!res.status || !res.data) {
      alert('Information not found');
      return;
    }

    const data = res.data;
    const b = data.basic;

    document.getElementById('modalResidentName').innerText = b.name || 'Resident';
    document.getElementById('modalResidentSub').innerText = `Phone: ${b.phone} | ${b.room_seat_full} | Status: ${b.status_label}`;
    document.getElementById('modalRangeLabel').innerText = data.range_label || 'Selected Period';

    // Print Button
    const printBox = document.getElementById('modalPrintBtnBox');
    if (printBox) {
      printBox.innerHTML = `
        <a href="{{ url('/electricity-bills') }}/${b.id}/print?year=${year}&from_month=${fromMonth}&to_month=${toMonth}" target="_blank" class="btn btn-sm btn-light text-dark fw-semibold">
          <i class="ti ti-printer me-1"></i> Print Statement
        </a>
      `;
    }

    // Avatar Box
    const avBox = document.getElementById('modalAvatarBox');
    if (avBox) {
      if (b.image) {
        avBox.innerHTML = `<img src="/bookingsimage/${b.image}" class="avatar-sm" onerror="this.src='/storage/user/${b.image}'">`;
      } else {
        avBox.innerHTML = `<div class="avatar avatar-sm bg-white text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold fs-5" style="width:40px;height:40px;">${b.name.charAt(0)}</div>`;
      }
    }

    // Table Body
    const tbody = document.getElementById('electricityModalTbody');
    const ledger = data.monthly_ledger || [];

    if (ledger.length > 0) {
      tbody.innerHTML = '';
      ledger.forEach(item => {
        const isActiveMonth = item.is_current_month;
        const activeBadge = isActiveMonth
          ? `<span class="badge bg-success text-white ms-2 px-2 py-0.5 rounded-pill shadow-xs" style="font-size: 0.72rem; font-weight: 600;"><i class="ti ti-circle-filled text-white me-1" style="font-size: 6px;"></i>Active</span>`
          : '';

        const billBadge = item.electricity_bill > 0
          ? `<span class="badge bg-warning text-dark px-2.5 py-1.5 fs-7 fw-bold shadow-xs border border-warning">⚡ ৳ ${Number(item.electricity_bill).toLocaleString()}</span>`
          : `<span class="text-muted">৳ 0</span>`;

        let statusBadge = '<span class="text-muted">—</span>';
        if (item.status === 'paid') {
          statusBadge = '<span class="badge bg-label-success px-2.5 py-1 fs-7 fw-bold">✅ Paid</span>';
        } else if (item.status === 'partial') {
          statusBadge = '<span class="badge bg-label-warning text-dark px-2.5 py-1 fs-7 fw-bold">⏳ Partial</span>';
        } else if (item.status === 'pending' || item.status === 'overdue') {
          statusBadge = '<span class="badge bg-label-danger px-2.5 py-1 fs-7 fw-bold">❌ Pending</span>';
        } else if (item.status === 'upcoming') {
          statusBadge = '<span class="badge bg-label-secondary px-2 py-1 fs-7">Upcoming</span>';
        }

        tbody.innerHTML += `
          <tr style="${isActiveMonth ? 'background-color: #f0fdf4;' : (item.has_activity ? 'background-color: #fffdf5;' : '')}">
            <td class="fw-bold text-dark">
              ${item.month_year_label} ${activeBadge}
            </td>
            <td class="text-center">${billBadge}</td>
            <td class="small text-muted">${item.payment_date}</td>
            <td class="text-center">${statusBadge}</td>
          </tr>
        `;
      });

      // Total Row at bottom
      if (data.range_totals) {
        tbody.innerHTML += `
          <tr class="table-warning fw-bold" style="border-top: 2px solid #f59e0b;">
            <td class="text-dark">
              <i class="ti ti-calculator me-1 text-warning"></i> Total Electricity Bill (${data.range_label}):
            </td>
            <td class="text-center py-2.5">
              <span class="badge bg-warning text-dark px-3 py-2 fs-6 fw-bolder shadow-sm border border-dark">
                ⚡ ৳ ${Number(data.range_totals.total_electricity || 0).toLocaleString()}
              </span>
            </td>
            <td colspan="2" class="text-muted small text-end">Electricity Statement Summary</td>
          </tr>
        `;
      }
    } else {
      tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-4">No records found for selected period.</td></tr>`;
    }
  })
  .catch(err => {
    console.error(err);
    document.getElementById('electricityModalTbody').innerHTML = `<tr><td colspan="4" class="text-center text-danger py-4">Failed to load electricity bill data.</td></tr>`;
  });
}
</script>
@endpush
