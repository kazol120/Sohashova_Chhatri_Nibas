@extends('backend.layouts.app')
@section("title") | Resident Full History & Profile @endsection

@push('style')
<style>
  .res-table th {
    font-size: 0.82rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 700;
  }
  .avatar-sm {
    width: 42px;
    height: 42px;
    object-fit: cover;
    border-radius: 50%;
    border: 2px solid #033364;
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
  .modal-tab-btn.active {
    background: #033364 !important;
    color: #ffffff !important;
    border-color: #033364 !important;
  }
  .modal-tab-btn {
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.88rem;
    padding: 7px 14px;
    color: #334155;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    margin-right: 4px;
    margin-bottom: 4px;
  }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  
  <!-- Header & Title -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
      <h4 class="fw-bold mb-1" style="color: #033364;">
        <i class="ti ti-id-badge-2 text-primary me-2"></i>Resident Full History & Profile
      </h4>
      <p class="text-muted mb-0 small">
        View comprehensive room movements, monthly costs, meal statements, and full ledger summaries from admission to date.
      </p>
    </div>
  </div>

  <!-- Stat Cards -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm stat-card h-100" style="border-left: 4px solid #033364 !important;">
        <div class="card-body d-flex align-items-center justify-content-between p-3">
          <div>
            <span class="text-muted fw-semibold small">Total Registered</span>
            <h3 class="mb-0 fw-bold mt-1 text-dark">{{ $totalCount }}</h3>
          </div>
          <div class="avatar avatar-md bg-light-primary rounded p-2 text-center">
            <i class="ti ti-users text-primary fs-3"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm stat-card h-100" style="border-left: 4px solid #10b981 !important;">
        <div class="card-body d-flex align-items-center justify-content-between p-3">
          <div>
            <span class="text-muted fw-semibold small">Active Residents</span>
            <h3 class="mb-0 fw-bold mt-1 text-success">{{ $activeCount }}</h3>
          </div>
          <div class="avatar avatar-md bg-light-success rounded p-2 text-center">
            <i class="ti ti-user-check text-success fs-3"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm stat-card h-100" style="border-left: 4px solid #64748b !important;">
        <div class="card-body d-flex align-items-center justify-content-between p-3">
          <div>
            <span class="text-muted fw-semibold small">Released Residents</span>
            <h3 class="mb-0 fw-bold mt-1 text-secondary">{{ $releasedCount }}</h3>
          </div>
          <div class="avatar avatar-md bg-light-secondary rounded p-2 text-center">
            <i class="ti ti-user-x text-secondary fs-3"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm stat-card h-100" style="border-left: 4px solid #ef4444 !important;">
        <div class="card-body d-flex align-items-center justify-content-between p-3">
          <div>
            <span class="text-muted fw-semibold small">Deleted History</span>
            <h3 class="mb-0 fw-bold mt-1 text-danger">{{ $deletedCount }}</h3>
          </div>
          <div class="avatar avatar-md bg-light-danger rounded p-2 text-center">
            <i class="ti ti-trash text-danger fs-3"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Filters & Search Form -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
      <form method="GET" action="{{ route('resident-history.index') }}" class="row g-2 align-items-center">
        <!-- Search Input -->
        <div class="col-12 col-md-4">
          <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="ti ti-search text-muted"></i></span>
            <input type="text" name="search" class="form-control border-start-0 ps-0" 
                   placeholder="Search by resident name, mobile, room number or floor..." 
                   value="{{ request('search') }}">
          </div>
        </div>

        <!-- Status Filter Buttons -->
        <div class="col-12 col-md-6">
          <div class="btn-group w-100 flex-wrap" role="group">
            <a href="{{ route('resident-history.index', array_merge(request()->except('status'), ['status' => 'all'])) }}" 
               class="btn btn-sm {{ $status === 'all' ? 'btn-primary fw-bold' : 'btn-outline-primary' }}">
              All ({{ $totalCount }})
            </a>
            <a href="{{ route('resident-history.index', array_merge(request()->except('status'), ['status' => 'active'])) }}" 
               class="btn btn-sm {{ $status === 'active' ? 'btn-success fw-bold' : 'btn-outline-success' }}">
              Active ({{ $activeCount }})
            </a>
            @if(isset($pendingCount) && $pendingCount > 0)
            <a href="{{ route('resident-history.index', array_merge(request()->except('status'), ['status' => 'pending'])) }}" 
               class="btn btn-sm {{ ($status === 'pending' || $status === 'deactive') ? 'btn-warning fw-bold text-dark' : 'btn-outline-warning text-dark' }}">
              Pending ({{ $pendingCount }})
            </a>
            @endif
            <a href="{{ route('resident-history.index', array_merge(request()->except('status'), ['status' => 'released'])) }}" 
               class="btn btn-sm {{ $status === 'released' ? 'btn-secondary fw-bold' : 'btn-outline-secondary' }}">
              Released ({{ $releasedCount }})
            </a>
            <a href="{{ route('resident-history.index', array_merge(request()->except('status'), ['status' => 'deleted'])) }}" 
               class="btn btn-sm {{ ($status === 'deleted' || $status === 'inactive') ? 'btn-danger fw-bold' : 'btn-outline-danger' }}">
              Deleted ({{ $deletedCount }})
            </a>
          </div>
        </div>

        <!-- Submit & Reset -->
        <div class="col-12 col-md-2 d-flex gap-2">
          <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
            <i class="ti ti-filter me-1"></i> Filter
          </button>
          @if(request()->has('search') || request()->has('status'))
            <a href="{{ route('resident-history.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
              <i class="ti ti-rotate-clockwise"></i>
            </a>
          @endif
        </div>
      </form>
    </div>
  </div>

  <!-- Resident List Table -->
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0 fw-bold" style="color: #033364;">
        <i class="ti ti-list-details me-2"></i>Resident List & History Dashboard
      </h5>
      <span class="badge bg-label-primary px-3 py-2 rounded-pill font-monospace" style="font-size: 0.85rem;">
        Total Displayed: {{ count($residents) }}
      </span>
    </div>

    <div class="table-responsive text-nowrap">
      <table class="table table-hover align-middle res-table mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-3" style="width: 50px;">#</th>
            <th>Resident Profile & Name</th>
            <th>Phone & Login ID</th>
            <th>Room & Seat Details</th>
            <th>Admission Date</th>
            <th>Status</th>
            <th class="text-center pe-3" style="width: 170px;">History & Print</th>
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
                  <img src="{{ asset('bookingsimage/' . $resident->image) }}" alt="Avatar" class="avatar-sm">
                @elseif(!empty($resident->image) && file_exists(public_path($resident->image)))
                  <img src="{{ asset($resident->image) }}" alt="Avatar" class="avatar-sm">
                @else
                  <div class="avatar avatar-sm bg-label-primary rounded-circle d-flex align-items-center justify-content-center text-primary fw-bold" style="width:42px;height:42px;">
                    {{ mb_substr($resident->name, 0, 1) }}
                  </div>
                @endif
                <div>
                  <div class="fw-bold text-dark fs-6">{{ $resident->name }}</div>
                  <div class="text-muted small">
                    <span class="badge bg-label-secondary" style="font-size: 0.72rem;">{{ $resident->user_type }}</span>
                    @if(!empty($resident->email) && $resident->email !== '-')
                      <span class="ms-1 text-truncate d-inline-block" style="max-width: 140px;" title="{{ $resident->email }}"><i class="ti ti-mail small"></i> {{ $resident->email }}</span>
                    @endif
                  </div>
                </div>
              </div>
            </td>

            <!-- Phone & Password -->
            <td>
              <div class="fw-semibold text-dark font-monospace">{{ $resident->phone }}</div>
              <div class="small text-muted">
                Login Code: <span class="fw-bold text-primary font-monospace">{{ $resident->password }}</span>
              </div>
            </td>

            <!-- Room & Seat -->
            <td>
              <div class="fw-bold text-dark">{{ $resident->room_seat_full }}</div>
              <div class="small text-muted">
                Monthly Rent: ৳ {{ number_format($resident->monthly_amount, 2) }}
              </div>
            </td>

            <!-- Joining Date -->
            <td>
              <div class="fw-semibold text-dark"><i class="ti ti-calendar-event me-1 text-primary"></i>{{ $resident->joining_date }}</div>
              @if($resident->is_deleted)
                <div class="small text-danger"><i class="ti ti-trash me-1"></i>Deleted: {{ $resident->deleted_date }}</div>
              @elseif($resident->is_released)
                <div class="small text-secondary"><i class="ti ti-door-exit me-1"></i>Released: {{ $resident->release_date }}</div>
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
                  <i class="ti ti-door-exit me-1"></i>Released
                </span>
              @elseif($resident->is_active)
                <span class="badge history-badge-active px-2.5 py-1.5 rounded-pill fw-semibold">
                  <i class="ti ti-circle-filled text-success me-1" style="font-size: 8px;"></i>Active
                </span>
              @else
                <span class="badge px-2.5 py-1.5 rounded-pill fw-semibold" style="border: 1px solid #fcd34d; color: #b45309; background-color: #fffbeb;">
                  <i class="ti ti-clock me-1"></i>Pending / Deactive
                </span>
              @endif
            </td>

            <!-- Actions -->
            <td class="text-center pe-3">
              <div class="d-flex justify-content-center gap-1.5">
                <button type="button" class="btn btn-sm btn-primary fw-semibold px-2.5 py-1.5 shadow-sm"
                        onclick="loadResidentDetails({{ $resident->id }})" title="View Full History & Ledger">
                  <i class="ti ti-file-text me-1"></i> Full History
                </button>
                <a href="{{ route('resident-history.print', $resident->id) }}" target="_blank" 
                   class="btn btn-sm btn-outline-secondary px-2 py-1.5" title="Print Statement / PDF">
                  <i class="ti ti-printer"></i>
                </a>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" class="text-center py-5 text-muted">
              <div class="mb-2"><i class="ti ti-user-search fs-1 text-secondary"></i></div>
              <h6 class="text-muted fw-semibold">No resident records found</h6>
              <p class="small text-muted mb-0">Please adjust your search filter and try again.</p>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- 360° Resident Details Modal -->
<div class="modal fade" id="resident360Modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow-lg">
      <!-- Modal Header -->
      <div class="modal-header text-white" style="background: linear-gradient(135deg, #033364 0%, #054a8e 100%);">
        <div class="d-flex align-items-center gap-3">
          <div id="modalAvatarBox"></div>
          <div>
            <h5 class="modal-title text-white fw-bold mb-0" id="modalResidentName">Loading Resident Profile...</h5>
            <div class="small opacity-85" id="modalResidentSub">Please wait...</div>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Year & Month Filter Bar inside Modal -->
      <div class="bg-white p-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2 flex-wrap">
          <span class="fw-bold text-dark small"><i class="ti ti-calendar me-1 text-primary"></i>Filter:</span>
          
          <!-- Year Selector -->
          <div class="d-flex align-items-center gap-1">
            <span class="small fw-semibold text-muted">Year:</span>
            <select class="form-select form-select-sm" id="modalFilterYear" style="width: 105px;" onchange="refreshResidentModal()">
              @foreach($yearsList as $yr)
                <option value="{{ $yr }}" {{ $yr == (int)date('Y') ? 'selected' : '' }}>Year {{ $yr }}</option>
              @endforeach
            </select>
          </div>

          <!-- Month Selector -->
          <div class="d-flex align-items-center gap-1">
            <span class="small fw-semibold text-muted">Month:</span>
            <select class="form-select form-select-sm" id="modalFilterMonthMode" style="width: 175px;" onchange="refreshResidentModal()">
              <option value="all">All Months (12 Months)</option>
              <option value="1">01 - January</option>
              <option value="2">02 - February</option>
              <option value="3">03 - March</option>
              <option value="4">04 - April</option>
              <option value="5">05 - May</option>
              <option value="6">06 - June</option>
              <option value="7">07 - July</option>
              <option value="8">08 - August</option>
              <option value="9" selected>09 - September</option>
              <option value="10">10 - October</option>
              <option value="11">11 - November</option>
              <option value="12">12 - December</option>
            </select>
          </div>

          <button type="button" class="btn btn-sm btn-primary" onclick="refreshResidentModal()">
            <i class="ti ti-refresh me-1"></i> Apply Filter
          </button>
        </div>

        <div id="modalPrintBtnBox"></div>
      </div>

      <!-- Modal Nav Tabs -->
      <div class="modal-header bg-light py-2 px-3 border-bottom d-flex flex-wrap gap-1 align-items-center">
        <ul class="nav nav-pills flex-wrap" id="residentDetailTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="modal-tab-btn active" id="tab-cost-ledger-btn" data-bs-toggle="pill" data-bs-target="#tab-cost-ledger" type="button" role="tab">
              <i class="ti ti-receipt-2 me-1 text-success"></i> Monthly Cost & Ledger
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="modal-tab-btn" id="tab-overview-btn" data-bs-toggle="pill" data-bs-target="#tab-overview" type="button" role="tab">
              <i class="ti ti-user-circle me-1"></i> Basic Info & Profile
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="modal-tab-btn" id="tab-summary-btn" data-bs-toggle="pill" data-bs-target="#tab-summary" type="button" role="tab">
              <i class="ti ti-calculator me-1"></i> Lifetime Financial Summary
            </button>
          </li>
        </ul>
      </div>

      <!-- Modal Body -->
      <div class="modal-body p-4">
        <div class="tab-content" id="residentDetailTabContent">
          
          <!-- TAB 1: MONTHLY ALL-IN-ONE COST LEDGER (NEW) -->
          <div class="tab-pane fade show active" id="tab-cost-ledger" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
              <h6 class="fw-bold text-dark mb-0">
                <i class="ti ti-table me-1 text-primary"></i>Monthly Expenses, Meals & Room Change Statement
              </h6>
            </div>

            <div class="table-responsive">
              <table class="table table-bordered table-hover align-middle table-sm" id="monthlyCostTable">
                <thead class="table-light">
                  <tr>
                    <th>Month & Year</th>
                    <th class="text-end">Room Rent</th>
                    <th class="text-end">Electricity Bill</th>
                    <th class="text-center">Meals & Cost</th>
                    <th class="text-center">Room Change Fee</th>
                    <th class="text-end">Previous Due</th>
                    <th class="text-end bg-light-warning fw-bold text-dark fs-6">Total Cost</th>
                  </tr>
                </thead>
                <tbody id="monthlyCostTbody">
                  <!-- Loaded via AJAX -->
                </tbody>
              </table>
            </div>
          </div>

          <!-- TAB 2: OVERVIEW -->
          <div class="tab-pane fade" id="tab-overview" role="tabpanel">
            <div class="row g-3">
              <!-- Basic Details Card -->
              <div class="col-12 col-md-6">
                <div class="card border border-light-subtle shadow-none h-100">
                  <div class="card-header bg-light py-2 px-3 fw-bold text-primary">
                    <i class="ti ti-id me-1"></i> Personal & Institutional Details
                  </div>
                  <div class="card-body p-3">
                    <table class="table table-sm table-borderless mb-0">
                      <tr>
                        <td class="text-muted" style="width: 140px;">Resident Name:</td>
                        <td class="fw-bold text-dark" id="ov-name">-</td>
                      </tr>
                      <tr>
                        <td class="text-muted">Mobile Number:</td>
                        <td class="fw-bold text-dark font-monospace" id="ov-phone">-</td>
                      </tr>
                      <tr>
                        <td class="text-muted">Email Address:</td>
                        <td class="text-dark" id="ov-email">-</td>
                      </tr>
                      <tr>
                        <td class="text-muted">Login Password:</td>
                        <td><span class="badge bg-label-primary font-monospace fw-bold fs-6" id="ov-password">-</span></td>
                      </tr>
                      <tr>
                        <td class="text-muted">Blood Group:</td>
                        <td class="fw-bold text-danger" id="ov-blood">-</td>
                      </tr>
                      <tr>
                        <td class="text-muted">User Type:</td>
                        <td class="text-dark" id="ov-user-type">-</td>
                      </tr>
                      <tr>
                        <td class="text-muted">Institution / Workplace:</td>
                        <td class="text-dark" id="ov-institution">-</td>
                      </tr>
                      <tr>
                        <td class="text-muted">Class / Semester:</td>
                        <td class="text-dark" id="ov-class">-</td>
                      </tr>
                      <tr>
                        <td class="text-muted">NID:</td>
                        <td class="font-monospace text-dark" id="ov-nid">-</td>
                      </tr>
                    </table>
                  </div>
                </div>
              </div>

              <!-- Family & Address Card -->
              <div class="col-12 col-md-6">
                <div class="card border border-light-subtle shadow-none h-100">
                  <div class="card-header bg-light py-2 px-3 fw-bold text-primary">
                    <i class="ti ti-home-heart me-1"></i> Guardian & Permanent Address
                  </div>
                  <div class="card-body p-3">
                    <table class="table table-sm table-borderless mb-0">
                      <tr>
                        <td class="text-muted" style="width: 140px;">Father's Name:</td>
                        <td class="fw-bold text-dark" id="ov-father-name">-</td>
                      </tr>
                      <tr>
                        <td class="text-muted">Father's Phone:</td>
                        <td class="font-monospace text-dark" id="ov-father-phone">-</td>
                      </tr>
                      <tr>
                        <td class="text-muted">Mother's Name:</td>
                        <td class="fw-bold text-dark" id="ov-mother-name">-</td>
                      </tr>
                      <tr>
                        <td class="text-muted">Mother's Phone:</td>
                        <td class="font-monospace text-dark" id="ov-mother-phone">-</td>
                      </tr>
                      <tr>
                        <td class="text-muted">Division & District:</td>
                        <td class="text-dark" id="ov-region">-</td>
                      </tr>
                      <tr>
                        <td class="text-muted">Thana & Address:</td>
                        <td class="text-dark" id="ov-address">-</td>
                      </tr>
                      <tr>
                        <td class="text-muted">Admission Date:</td>
                        <td class="fw-bold text-success" id="ov-admission">-</td>
                      </tr>
                      <tr>
                        <td class="text-muted">Release Date:</td>
                        <td class="fw-bold text-secondary" id="ov-release">-</td>
                      </tr>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- TAB 3: FINANCIAL SUMMARY -->
          <div class="tab-pane fade" id="tab-summary" role="tabpanel">
            <div class="card border border-light-subtle shadow-none">
              <div class="card-body p-4">
                <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">
                  <i class="ti ti-report-money text-success me-2"></i>Resident Lifetime Financial Overview
                </h5>
                <div class="row g-3">
                  <!-- Box 1: Meal Summary -->
                  <div class="col-12 col-md-6">
                    <div class="border rounded-3 p-3 bg-light-subtle h-100">
                      <div class="fw-bold text-primary mb-2 small border-bottom pb-1">
                        🍱 Meal Summary
                      </div>
                      <div class="d-flex justify-content-between py-1.5 small">
                        <span class="text-muted">Total Meal Deposit:</span>
                        <strong class="text-dark fs-6" id="fin-meal-deposit">৳ 0.00</strong>
                      </div>
                      <div class="d-flex justify-content-between py-1.5 small">
                        <span class="text-muted">(-) Total Meal Deduction:</span>
                        <strong class="text-danger fs-6" id="fin-meal-cost">- ৳ 0.00</strong>
                      </div>
                      <div class="d-flex justify-content-between py-2 mt-2 border-top" style="background:#f0fdf4; border-radius: 4px; padding-left: 8px; padding-right: 8px;">
                        <span class="fw-bold text-success">= Current Meal Balance:</span>
                        <strong class="text-success fs-6" id="fin-meal-balance">৳ 0.00</strong>
                      </div>
                    </div>
                  </div>

                  <!-- Box 2: Grand Total Summary -->
                  <div class="col-12 col-md-6">
                    <div class="border rounded-3 p-3 bg-light-subtle h-100">
                      <div class="fw-bold text-primary mb-2 small border-bottom pb-1">
                        💰 Grand Total Summary
                      </div>
                      <div class="d-flex justify-content-between py-1.5 small">
                        <span class="text-muted">Advance Deposit:</span>
                        <strong class="text-dark fs-6" id="fin-advance">৳ 0.00</strong>
                      </div>
                      <div class="justify-content-between py-1.5 small d-none" id="fin-devfee-row">
                        <span class="text-muted">Development Fee:</span>
                        <strong class="text-dark fs-6" id="fin-devfee">৳ 0.00</strong>
                      </div>
                      <div class="d-flex justify-content-between py-1.5 small">
                        <span class="text-muted">Current Meal Balance:</span>
                        <strong class="text-success fs-6" id="fin-meal-balance-copy">৳ 0.00</strong>
                      </div>
                      <div class="d-flex justify-content-between py-2.5 mt-2 bg-primary text-white rounded-2 px-3 align-items-center">
                        <span class="fw-bold small">= Grand Total Paid:</span>
                        <strong class="fs-5 text-white" id="fin-grand-total">৳ 0.00</strong>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- Modal Footer -->
      <div class="modal-footer bg-light py-2 px-3">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
</div>
</div>

<div id="breakdownModalContainer"></div>
@endsection

@push('script')
<script>
let currentResidentId = null;

function loadResidentDetails(id) {
  currentResidentId = id;

  try {
    if (window.jQuery && typeof $('#resident360Modal').modal === 'function') {
      $('#resident360Modal').modal('show');
    } else {
      const modalEl = document.getElementById('resident360Modal');
      const bsModal = (window.bootstrap && bootstrap.Modal) ? (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl)) : null;
      if (bsModal) {
        bsModal.show();
      } else {
        window.location.href = `{{ url('/resident-history') }}/${id}`;
        return;
      }
    }
  } catch (e) {
    console.error(e);
    window.location.href = `{{ url('/resident-history') }}/${id}`;
    return;
  }

  refreshResidentModal();
}

function refreshResidentModal() {
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

  // Reset basic texts
  document.getElementById('modalResidentName').innerText = 'Loading...';
  document.getElementById('modalResidentSub').innerText = 'Please wait...';
  document.getElementById('modalAvatarBox').innerHTML = '<div class="spinner-border spinner-border-sm text-light"></div>';

  fetch(`{{ url('/resident-history') }}/${currentResidentId}?format=json&year=${year}&from_month=${fromMonth}&to_month=${toMonth}`, {
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
    .then(async res => {
      let data = {};
      try {
        data = await res.json();
      } catch (e) {
        throw new Error('JSON parsing failed: ' + res.status);
      }
      return data;
    })
    .then(res => {
      if (!res.status || !res.data) {
        alert('Information not found');
        return;
      }
      const data = res.data;
      const b = data.basic;
      const p = data.personal;

      function setEl(id, txt) {
        const el = document.getElementById(id);
        if (el) el.innerText = txt || '-';
      }

      // Header Info
      setEl('modalResidentName', b.name);
      setEl('modalResidentSub', `Phone: ${b.phone} | ${b.room_seat_full} | Status: ${b.status_label}`);
      
      const printBtnBox = document.getElementById('modalPrintBtnBox');
      if (printBtnBox) {
        printBtnBox.innerHTML = `
          <a href="{{ url('/resident-history') }}/${b.id}/print?year=${year}&from_month=${fromMonth}&to_month=${toMonth}" target="_blank" class="btn btn-sm btn-dark">
            <i class="ti ti-printer me-1"></i> Print Statement
          </a>
        `;
      }

      const avBox = document.getElementById('modalAvatarBox');
      if (avBox) {
        if (b.image) {
          avBox.innerHTML = `<img src="/bookingsimage/${b.image}" class="avatar-sm" onerror="this.src='/${b.image}'">`;
        } else {
          avBox.innerHTML = `<div class="avatar avatar-sm bg-white text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold fs-5" style="width:42px;height:42px;">${b.name.charAt(0)}</div>`;
        }
      }

      let currentCostLedger = data.monthly_cost_ledger || [];
      window.currentCostLedger = currentCostLedger;

      const costTbody = document.getElementById('monthlyCostTbody');
      if (costTbody) {
        costTbody.innerHTML = '';
        if (currentCostLedger && currentCostLedger.length > 0) {
          currentCostLedger.forEach(item => {
            const mealBtn = item.meal_count > 0 
              ? `<button type="button" class="btn btn-sm btn-outline-info py-0.5 px-1.5 fw-semibold" onclick="showMealList('${item.month_num}')" title="View Meals"><i class="ti ti-soup me-1"></i>${item.meal_count} Meals (৳ ${item.meal_charge.toLocaleString()})</button>`
              : `<span class="text-muted small">0 Meals (৳ 0)</span>`;

            const rcBtn = item.room_change_fee > 0
              ? `<button type="button" class="btn btn-sm btn-outline-warning py-0.5 px-1.5 fw-bold text-dark" onclick="showRoomChanges('${item.month_num}')" title="View Room Changes"><i class="ti ti-bed me-1"></i>৳ ${item.room_change_fee.toLocaleString()} (${item.room_change_count} times)</button>`
              : `<span class="text-muted small">-</span>`;

            const isActiveMonth = (item.is_current_month || item.is_selected_month);
            const activeBadge = isActiveMonth
              ? `<span class="badge bg-success text-white ms-2 px-2 py-0.5 rounded-pill shadow-xs" style="font-size: 0.72rem; font-weight: 600; vertical-align: middle;"><i class="ti ti-circle-filled text-white me-1" style="font-size: 6px;"></i>Active</span>`
              : '';

            costTbody.innerHTML += `
              <tr style="${isActiveMonth ? 'background-color: #f0fdf4;' : (item.has_activity ? 'background-color: #fafcff;' : '')}">
                <td class="fw-bold text-dark">
                  ${item.month_year_label} ${activeBadge}
                </td>
                <td class="text-end">৳ ${item.room_rent.toLocaleString()}</td>
                <td class="text-end">৳ ${item.electricity_bill.toLocaleString()}</td>
                <td class="text-center">${mealBtn}</td>
                <td class="text-center">${rcBtn}</td>
                <td class="text-end">৳ ${item.prev_due.toLocaleString()}</td>
                <td class="text-end bg-light-warning fw-bold text-dark fs-6">৳ ${item.total_cost.toLocaleString()}</td>
              </tr>
            `;
          });

          // Add Total row for the selected range at the bottom of the table
          if (data.range_totals) {
            costTbody.innerHTML += `
              <tr class="table-primary fw-bold" style="border-top: 2px solid #033364;">
                <td class="text-dark">
                  <i class="ti ti-calculator me-1 text-primary"></i>Total Cost (${data.range_label || 'Selected Range'}):
                </td>
                <td class="text-end">৳ ${(data.range_totals.rent_billed || 0).toLocaleString()}</td>
                <td class="text-end">৳ ${(data.range_totals.electricity || 0).toLocaleString()}</td>
                <td class="text-center">${data.range_totals.meals_count || 0} Meals (৳ ${(data.range_totals.meal_charge || 0).toLocaleString()})</td>
                <td class="text-center">${data.range_totals.room_change_fee > 0 ? '৳ ' + data.range_totals.room_change_fee.toLocaleString() : '-'}</td>
                <td class="text-end">৳ ${(data.range_totals.prev_due || 0).toLocaleString()}</td>
                <td class="text-end bg-light-warning text-dark fs-6 fw-bold">৳ ${(data.range_totals.combined_cost || 0).toLocaleString()}</td>
              </tr>
            `;
          }

        } else {
          costTbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-3">No cost ledger records found.</td></tr>`;
        }
      }

      // Tab 2: Overview
      setEl('ov-name', p.full_name);
      setEl('ov-phone', p.phone);
      setEl('ov-email', p.email);
      setEl('ov-password', p.password);
      setEl('ov-blood', p.blood_group);
      setEl('ov-user-type', p.user_type);
      setEl('ov-institution', p.institution_name || p.workplace_name);
      setEl('ov-class', p.education_class);
      setEl('ov-nid', p.nid);

      setEl('ov-father-name', p.father_name);
      setEl('ov-father-phone', p.father_phone);
      setEl('ov-mother-name', p.mother_name);
      setEl('ov-mother-phone', p.mother_phone);
      setEl('ov-region', `${p.division || '-'} / ${p.district || '-'}`);
      setEl('ov-address', `${p.thana || '-'}, ${p.address || '-'}`);
      setEl('ov-admission', p.admission_date);
      setEl('ov-release', p.release_date || 'Active');

      // Financial Summary
      setEl('fin-advance', `৳ ${(data.payment_summary.advance_paid || 0).toLocaleString()}`);
      
      const devFeeRow = document.getElementById('fin-devfee-row');
      const devFee = Number(data.payment_summary.dev_fee_paid) || 0;
      if (devFee > 0) {
        if (devFeeRow) {
          devFeeRow.classList.remove('d-none');
          devFeeRow.classList.add('d-flex');
        }
        setEl('fin-devfee', `৳ ${devFee.toLocaleString()}`);
      } else {
        if (devFeeRow) {
          devFeeRow.classList.remove('d-flex');
          devFeeRow.classList.add('d-none');
        }
      }

      setEl('fin-meal-deposit', `৳ ${(data.meal_summary.total_deposit || 0).toLocaleString()}`);
      
      const totalMealsCount = data.meal_summary.total_count || data.meal_summary.total_full_meals || 0;
      const regMealsCount = data.meal_summary.regular_meals_count || 0;
      const guestMealsCount = data.meal_summary.guest_meals_count || 0;
      const mealCost = data.meal_summary.total_cost || 0;
      
      let mealDeductText = `- ৳ ${mealCost.toLocaleString()} (${totalMealsCount} Meals)`;
      if (guestMealsCount > 0) {
        mealDeductText = `- ৳ ${mealCost.toLocaleString()} (${totalMealsCount} Meals: ${regMealsCount} Reg + ${guestMealsCount} Guest)`;
      }
      setEl('fin-meal-cost', mealDeductText);
      
      const mealBalanceEl = document.getElementById('fin-meal-balance');
      const mBalance = data.meal_summary.balance || 0;
      if (mealBalanceEl) {
        mealBalanceEl.innerText = `৳ ${mBalance.toLocaleString()}`;
        if (mBalance > 0) {
          mealBalanceEl.className = 'text-success fw-bold fs-6';
        } else if (mBalance < 0) {
          mealBalanceEl.className = 'text-danger fw-bold fs-6';
        } else {
          mealBalanceEl.className = 'text-muted fw-bold fs-6';
        }
      }

      setEl('fin-meal-balance-copy', `৳ ${mBalance.toLocaleString()}`);
      setEl('fin-grand-total', `৳ ${(data.payment_summary.grand_total_deposit || 0).toLocaleString()}`);
    })
    .catch(err => {
      console.error('Resident profile load error:', err);
      alert('Error loading information: ' + err.message);
    });
}

function showDueBreakdown(monthNum) {
  const ledger = window.currentCostLedger || [];
  const row = ledger.find(r => r.month_num == monthNum);
  if (!row) return;
  const bd = row.due_breakdown;
  const html = `
    <div class="modal fade show" id="dueBreakdownModal" tabindex="-1" style="display: block; background: rgba(0,0,0,0.55); z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-header bg-danger text-white py-2.5 px-3">
            <h6 class="modal-title text-white fw-bold mb-0">
              <i class="ti ti-calculator me-1"></i> ${row.month_year_label} - Expense & Due Breakdown
            </h6>
            <button type="button" class="btn-close btn-close-white" onclick="closeBreakdownModal('dueBreakdownModal')"></button>
          </div>
          <div class="modal-body p-3">
            <table class="table table-bordered table-sm mb-3">
              <thead class="table-light">
                <tr><th>Expense Item</th><th class="text-end">Amount</th></tr>
              </thead>
              <tbody>
                <tr>
                  <td>🏠 Room Rent:</td>
                  <td class="text-end fw-bold">৳ ${bd.room_rent.toLocaleString()}</td>
                </tr>
                <tr>
                  <td>⚡ Electricity Bill:</td>
                  <td class="text-end fw-bold">৳ ${bd.electricity_bill.toLocaleString()}</td>
                </tr>
                <tr>
                  <td>🍽️ Meal Charge (${bd.meal_count} Meals × ৳ ${bd.meal_rate}):</td>
                  <td class="text-end fw-bold text-info">৳ ${bd.meal_charge.toLocaleString()}</td>
                </tr>
                ${bd.room_change_fee > 0 ? `
                <tr class="table-warning">
                  <td>🔄 Room Change Fee (${bd.room_change_count} times):</td>
                  <td class="text-end fw-bold text-danger">৳ ${bd.room_change_fee.toLocaleString()}</td>
                </tr>` : ''}
                ${bd.prev_due > 0 ? `
                <tr>
                  <td>⏳ Previous Due:</td>
                  <td class="text-end fw-bold text-warning">৳ ${bd.prev_due.toLocaleString()}</td>
                </tr>` : ''}
                <tr class="table-secondary fw-bold">
                  <td>➕ Total Incurred Cost:</td>
                  <td class="text-end text-dark fs-6">৳ ${bd.total_cost.toLocaleString()}</td>
                </tr>
                <tr class="table-success fw-bold">
                  <td>➖ Total Paid:</td>
                  <td class="text-end text-success fs-6">- ৳ ${bd.total_paid.toLocaleString()}</td>
                </tr>
                <tr class="${bd.net_due > 0 ? 'table-danger' : 'table-success'} fw-bold">
                  <td class="fs-6">🔴 Net Outstanding Due:</td>
                  <td class="text-end text-danger fs-5">৳ ${bd.net_due.toLocaleString()}</td>
                </tr>
              </tbody>
            </table>
            <div class="text-end">
              <button type="button" class="btn btn-sm btn-secondary" onclick="closeBreakdownModal('dueBreakdownModal')">
                <i class="ti ti-x me-1"></i> Close
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  `;
  document.getElementById('breakdownModalContainer').innerHTML = html;
}

function showMealList(monthNum) {
  const ledger = window.currentCostLedger || [];
  const row = ledger.find(r => r.month_num == monthNum);
  if (!row) return;
  let mealsHtml = '';
  if (row.meal_details && row.meal_details.length > 0) {
    row.meal_details.forEach((m, i) => {
      let statusBadge = '';
      if (m.type === 'guest') {
        statusBadge = '<span class="badge bg-warning text-dark"><i class="ti ti-users me-1"></i>Guest Meal</span>';
      } else if (m.full_meal) {
        statusBadge = '<span class="badge bg-success">Full Meal</span>';
      } else {
        statusBadge = '<span class="badge bg-secondary">Off</span>';
      }

      mealsHtml += `
        <tr class="${m.type === 'guest' ? 'table-warning' : ''}">
          <td class="text-center">${i+1}</td>
          <td class="fw-semibold">${m.date}</td>
          <td>${statusBadge}</td>
          <td class="text-end fw-bold text-danger">৳ ${m.amount}</td>
          <td>${m.note || '-'}</td>
        </tr>
      `;
    });
  } else {
    mealsHtml = '<tr><td colspan="5" class="text-center text-muted py-3">No meals recorded for this month.</td></tr>';
  }

  const guestCountText = (row.guest_meal_count && row.guest_meal_count > 0) 
    ? `<small class="text-muted fw-normal">(Regular: ${row.regular_meal_count || 0} + Guest: ${row.guest_meal_count})</small>` 
    : '';

  const html = `
    <div class="modal fade show" id="mealBreakdownModal" tabindex="-1" style="display: block; background: rgba(0,0,0,0.55); z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-header bg-primary text-white py-2.5 px-3">
            <h6 class="modal-title text-white fw-bold mb-0">
              <i class="ti ti-soup me-1"></i> ${row.month_year_label} - Meal Consumption & Charges List
            </h6>
            <button type="button" class="btn-close btn-close-white" onclick="closeBreakdownModal('mealBreakdownModal')"></button>
          </div>
          <div class="modal-body p-3">
            <div class="d-flex justify-content-between flex-wrap gap-2 mb-2 small bg-light p-2 rounded">
              <span>Total Meals: <strong class="text-primary fs-6">${row.meal_count}</strong> ${guestCountText}</span>
              <span>Meal Rate: <strong>৳ ${row.meal_rate}</strong></span>
              <span>Total Meal Cost: <strong class="text-danger fs-6">৳ ${row.meal_charge.toLocaleString()}</strong></span>
            </div>
            <div class="table-responsive" style="max-height: 280px;">
              <table class="table table-bordered table-sm mb-0">
                <thead class="table-light">
                  <tr>
                    <th class="text-center">#</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th class="text-end">Charge</th>
                    <th>Note</th>
                  </tr>
                </thead>
                <tbody>${mealsHtml}</tbody>
              </table>
            </div>
            <div class="text-end mt-3">
              <button type="button" class="btn btn-sm btn-secondary" onclick="closeBreakdownModal('mealBreakdownModal')">
                <i class="ti ti-x me-1"></i> Close
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  `;
  document.getElementById('breakdownModalContainer').innerHTML = html;
}

function showRoomChanges(monthNum) {
  const ledger = window.currentCostLedger || [];
  const row = ledger.find(r => r.month_num == monthNum);
  if (!row) return;
  let rcHtml = '';
  if (row.room_change_details && row.room_change_details.length > 0) {
    row.room_change_details.forEach((rc, i) => {
      rcHtml += `
        <tr>
          <td class="text-center">${i+1}</td>
          <td>${rc.date}</td>
          <td>${rc.old_room} &rarr; <strong class="text-success">${rc.new_room}</strong></td>
          <td class="text-end fw-bold text-danger">৳ ${rc.fee.toLocaleString()}</td>
          <td><span class="badge bg-label-primary">${rc.status}</span> ${rc.remarks ? '(' + rc.remarks + ')' : ''}</td>
        </tr>
      `;
    });
  } else {
    rcHtml = '<tr><td colspan="5" class="text-center text-muted py-3">No room change records for this month.</td></tr>';
  }

  const html = `
    <div class="modal fade show" id="rcBreakdownModal" tabindex="-1" style="display: block; background: rgba(0,0,0,0.55); z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-header bg-warning text-dark py-2.5 px-3">
            <h6 class="modal-title fw-bold mb-0">
              <i class="ti ti-bed me-1"></i> ${row.month_year_label} - Room Change Details & Fees
            </h6>
            <button type="button" class="btn-close" onclick="closeBreakdownModal('rcBreakdownModal')"></button>
          </div>
          <div class="modal-body p-3">
            <div class="table-responsive" style="max-height: 250px;">
              <table class="table table-bordered table-sm mb-0">
                <thead class="table-light">
                  <tr>
                    <th class="text-center">#</th>
                    <th>Date</th>
                    <th>Room Change</th>
                    <th class="text-end">Fee</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>${rcHtml}</tbody>
              </table>
            </div>
            <div class="text-end mt-3">
              <button type="button" class="btn btn-sm btn-secondary" onclick="closeBreakdownModal('rcBreakdownModal')">
                <i class="ti ti-x me-1"></i> Close
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  `;
  document.getElementById('breakdownModalContainer').innerHTML = html;
}

function closeBreakdownModal(modalId) {
  const el = document.getElementById(modalId);
  if (el) el.remove();
}
</script>
@endpush
