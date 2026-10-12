@extends('backend.layouts.app')
@section("title") | Resident Full History - {{ $residentData['personal']['full_name'] }} @endsection

@php
    $b = $residentData['basic'];
    $p = $residentData['personal'];
    $rMoves = $residentData['room_movements'];
    $mPayments = $residentData['monthly_payments'];
    $pSummary = $residentData['payment_summary'];
    $mSummary = $residentData['meal_summary'];
    $mCostLedger = $residentData['monthly_cost_ledger'] ?? [];
    $yTotals = $residentData['year_totals'] ?? [];
    $selYear = $residentData['selected_year'] ?? date('Y');
@endphp

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

  <!-- Header & Back Button -->
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
      <a href="{{ route('resident-history.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
        <i class="ti ti-arrow-left me-1"></i> Back to Resident List
      </a>
      <h4 class="fw-bold mb-0 text-primary">
        <i class="ti ti-id-badge-2 me-2"></i>{{ $p['full_name'] }} - Full History & Ledger
      </h4>
    </div>
    <div class="d-flex gap-2 align-items-center flex-wrap">
      <form method="GET" action="{{ route('resident-history.show', $booking->id) }}" class="d-flex gap-2 align-items-center flex-wrap">
        <select name="year" class="form-select form-select-sm" style="width: 110px;" onchange="this.form.submit()">
          @foreach(range(2026, max((int)date('Y'), 2026)) as $y)
            <option value="{{ $y }}" {{ (int)$selYear === (int)$y ? 'selected' : '' }}>Year {{ $y }}</option>
          @endforeach
        </select>

        <span class="small fw-semibold text-muted">From:</span>
        <select name="from_month" class="form-select form-select-sm" style="width: 120px;" onchange="this.form.submit()">
          @foreach([1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'] as $num => $mName)
            <option value="{{ $num }}" {{ (int)($residentData['from_month'] ?? 1) === (int)$num ? 'selected' : '' }}>{{ $num }} - {{ $mName }}</option>
          @endforeach
        </select>

        <span class="small fw-semibold text-muted">To:</span>
        <select name="to_month" class="form-select form-select-sm" style="width: 120px;" onchange="this.form.submit()">
          @foreach([1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'] as $num => $mName)
            <option value="{{ $num }}" {{ (int)($residentData['to_month'] ?? 12) === (int)$num ? 'selected' : '' }}>{{ $num }} - {{ $mName }}</option>
          @endforeach
        </select>
      </form>
      <a href="{{ route('resident-history.print', $booking->id) }}?year={{ $selYear }}&from_month={{ $residentData['from_month'] ?? 1 }}&to_month={{ $residentData['to_month'] ?? 12 }}" target="_blank" class="btn btn-dark btn-sm">
        <i class="ti ti-printer me-1"></i> Print Statement
      </a>
    </div>
  </div>

  <!-- Top Resident Card -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
      <div class="row align-items-center">
        <div class="col-auto">
          @if(!empty($b->image) && file_exists(public_path('bookingsimage/' . $b->image)))
            <img src="{{ asset('bookingsimage/' . $b->image) }}" class="rounded-circle border" style="width: 75px; height: 75px; object-fit: cover;">
          @else
            <div class="avatar avatar-xl bg-label-primary rounded-circle d-flex align-items-center justify-content-center text-primary fw-bold fs-3" style="width: 75px; height: 75px;">
              {{ mb_substr($p['full_name'], 0, 1) }}
            </div>
          @endif
        </div>
        <div class="col">
          <h4 class="fw-bold mb-1 text-dark">{{ $p['full_name'] }}</h4>
          <div class="d-flex flex-wrap gap-2 text-muted small">
            <span><i class="ti ti-phone text-primary"></i> {{ $p['phone'] }}</span>
            <span>|</span>
            <span><i class="ti ti-mail text-primary"></i> {{ $p['email'] }}</span>
            <span>|</span>
            <span><i class="ti ti-bed text-primary"></i> <strong>{{ $b->room_seat_full }}</strong></span>
            <span>|</span>
            <span><i class="ti ti-calendar text-primary"></i> Admission Date: <strong>{{ $p['admission_date'] }}</strong></span>
          </div>
        </div>
        <div class="col-auto text-end">
          @if($b->is_deleted)
            <span class="badge bg-label-danger px-3 py-2 fs-6 rounded-pill"><i class="ti ti-trash me-1"></i>Deleted</span>
          @elseif($b->is_released)
            <span class="badge bg-label-secondary px-3 py-2 fs-6 rounded-pill">Released</span>
          @else
            <span class="badge bg-label-success px-3 py-2 fs-6 rounded-pill">Active</span>
          @endif
          <div class="mt-2 small text-muted">
            Login Password: <span class="badge bg-label-primary font-monospace fw-bold">{{ $p['password'] }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Detailed Sections -->
  <div class="row g-4">
    <!-- Section 1: Overview Details -->
    <div class="col-lg-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-light py-3 fw-bold text-primary">
          <i class="ti ti-user me-1"></i> Personal & Institutional Details
        </div>
        <div class="card-body p-3">
          <table class="table table-sm table-borderless">
            <tr><td class="text-muted" style="width: 150px;">Blood Group:</td><td class="fw-bold text-danger">{{ $p['blood_group'] }}</td></tr>
            <tr><td class="text-muted">User Type:</td><td>{{ $p['user_type'] }}</td></tr>
            <tr><td class="text-muted">Institution / Workplace:</td><td>{{ $p['institution_name'] }}</td></tr>
            <tr><td class="text-muted">Class / Semester:</td><td>{{ $p['education_class'] }}</td></tr>
            <tr><td class="text-muted">NID:</td><td class="font-monospace">{{ $p['nid'] }}</td></tr>
            <tr><td class="text-muted">Father's Name & Phone:</td><td>{{ $p['father_name'] }} ({{ $p['father_phone'] }})</td></tr>
            <tr><td class="text-muted">Mother's Name & Phone:</td><td>{{ $p['mother_name'] }} ({{ $p['mother_phone'] }})</td></tr>
            <tr><td class="text-muted">Address:</td><td>{{ $p['address'] }}, {{ $p['thana'] }}, {{ $p['district'] }}</td></tr>
          </table>
        </div>
      </div>
    </div>

    <!-- Section 2: Lifetime Financial Summary -->
    <div class="col-lg-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-light py-3 fw-bold text-success">
          <i class="ti ti-calculator me-1"></i> Lifetime Financial Summary
        </div>
        <div class="card-body p-3">
          <div class="row g-3">
            <!-- Box 1: Meal Summary -->
            <div class="col-12">
              <div class="border rounded-3 p-3 bg-light-subtle">
                <div class="fw-bold text-primary mb-2 small border-bottom pb-1">
                  🍱 Meal Summary
                </div>
                <div class="d-flex justify-content-between py-1 small">
                  <span class="text-muted">Total Meal Deposit:</span>
                  <strong class="text-dark">৳ {{ number_format($mSummary['total_deposit'], 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between py-1 small">
                  <span class="text-muted">(-) Total Meal Deduction ({{ $mSummary['total_count'] ?? $mSummary['total_full_meals'] }} Meals{{ !empty($mSummary['guest_meals_count']) && $mSummary['guest_meals_count'] > 0 ? ': ' . ($mSummary['regular_meals_count'] ?? 0) . ' Reg + ' . ($mSummary['guest_meals_count'] ?? 0) . ' Guest' : '' }}):</span>
                  <strong class="text-danger">- ৳ {{ number_format($mSummary['total_cost'] ?? 0, 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between py-1.5 mt-1 border-top" style="background:#f0fdf4; border-radius: 4px; padding-left: 6px; padding-right: 6px;">
                  <span class="fw-bold text-success">= Current Meal Balance:</span>
                  <strong class="text-success fs-6">৳ {{ number_format($mSummary['balance'] ?? 0, 2) }}</strong>
                </div>
              </div>
            </div>

            <!-- Box 2: Grand Total Summary -->
            <div class="col-12">
              <div class="border rounded-3 p-3 bg-light-subtle">
                <div class="fw-bold text-primary mb-2 small border-bottom pb-1">
                  💰 Grand Total Summary
                </div>
                <div class="d-flex justify-content-between py-1 small">
                  <span class="text-muted">Advance Deposit:</span>
                  <strong class="text-dark">৳ {{ number_format($pSummary['advance_paid'], 2) }}</strong>
                </div>
                @if(!empty($pSummary['dev_fee_paid']) && $pSummary['dev_fee_paid'] > 0)
                <div class="d-flex justify-content-between py-1 small">
                  <span class="text-muted">Development Fee:</span>
                  <strong class="text-dark">৳ {{ number_format($pSummary['dev_fee_paid'], 2) }}</strong>
                </div>
                @endif
                <div class="d-flex justify-content-between py-1 small">
                  <span class="text-muted">Current Meal Balance:</span>
                  <strong class="text-success">৳ {{ number_format($mSummary['balance'] ?? 0, 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between py-2 mt-2 bg-primary text-white rounded-2 px-3 align-items-center">
                  <span class="fw-bold small">= Grand Total Paid:</span>
                  <strong class="fs-5 text-white">৳ {{ number_format($pSummary['grand_total_deposit'] ?? 0, 2) }}</strong>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Section 3: Monthly All-in-One Cost & Expense Ledger -->
    <div class="col-12">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
          <span class="fw-bold"><i class="ti ti-calculator me-1"></i> Monthly Expense Ledger (Year {{ $selYear }})</span>
        </div>
        <div class="table-responsive">
          <table class="table table-hover table-bordered mb-0 align-middle">
            <thead class="table-light">
              <tr>
                <th>Month</th>
                <th class="text-end">Room Rent</th>
                <th class="text-end">Electricity Bill</th>
                <th class="text-center">Meals & Charge</th>
                <th class="text-center">Room Change Fee</th>
                <th class="text-end">Previous Due</th>
                <th class="text-end table-active fw-bold">Total Cost</th>
              </tr>
            </thead>
            <tbody>
              @forelse($mCostLedger as $row)
              @php 
                $isActiveM = (!empty($row['is_current_month']) || !empty($row['is_selected_month']));
              @endphp
              <tr style="{{ $isActiveM ? 'background-color: #f0fdf4;' : '' }}">
                <td class="fw-bold">
                  {{ $row['month_year_label'] }}
                  @if($isActiveM)
                    <span class="badge bg-success text-white ms-2 px-2 py-0.5 rounded-pill shadow-xs" style="font-size: 0.72rem; font-weight: 600; vertical-align: middle;"><i class="ti ti-circle-filled text-white me-1" style="font-size: 6px;"></i>Active</span>
                  @endif
                </td>
                <td class="text-end">৳ {{ number_format($row['room_rent'], 2) }}</td>
                <td class="text-end">৳ {{ number_format($row['electricity_bill'], 2) }}</td>
                <td class="text-center">{{ $row['meal_count'] }} Meals (৳ {{ number_format($row['meal_charge'], 2) }})</td>
                <td class="text-center">{{ $row['room_change_fee'] > 0 ? '৳ ' . number_format($row['room_change_fee'], 2) : '-' }}</td>
                <td class="text-end">৳ {{ number_format($row['prev_due'], 2) }}</td>
                <td class="text-end fw-bold table-active fs-6">৳ {{ number_format($row['total_cost'], 2) }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center text-muted py-3">No records found.</td>
              </tr>
              @endforelse
              @if(!empty($residentData['range_totals']))
              @php $rt = $residentData['range_totals']; @endphp
              <tr class="table-primary fw-bold" style="border-top: 2px solid #033364;">
                <td class="text-dark">Total Cost ({{ $residentData['range_label'] ?? 'Selected Range' }}):</td>
                <td class="text-end">৳ {{ number_format($rt['rent_billed'] ?? 0, 2) }}</td>
                <td class="text-end">৳ {{ number_format($rt['electricity'] ?? 0, 2) }}</td>
                <td class="text-center">{{ $rt['meals_count'] ?? 0 }} Meals (৳ {{ number_format($rt['meal_charge'] ?? 0, 2) }})</td>
                <td class="text-center">{{ ($rt['room_change_fee'] ?? 0) > 0 ? '৳ ' . number_format($rt['room_change_fee'], 2) : '-' }}</td>
                <td class="text-end">৳ {{ number_format($rt['prev_due'] ?? 0, 2) }}</td>
                <td class="text-end bg-light-warning fw-bold fs-6 text-dark">৳ {{ number_format($rt['combined_cost'] ?? 0, 2) }}</td>
              </tr>
              @endif
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Section 4: Room Movements -->
    <div class="col-12">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-light py-3 fw-bold text-dark">
          <i class="ti ti-bed me-1"></i> Room & Seat Change History
        </div>
        <div class="table-responsive">
          <table class="table table-hover table-bordered mb-0">
            <thead class="table-light">
              <tr>
                <th>Date</th>
                <th>Old Room & Seat</th>
                <th>New Room & Seat</th>
                <th>Rent Difference</th>
                <th>Change Fee</th>
                <th>Remarks</th>
              </tr>
            </thead>
            <tbody>
              @forelse($rMoves as $rm)
              <tr>
                <td>{{ $rm->created_at ? $rm->created_at->format('d M Y') : '-' }}</td>
                <td>{{ $rm->old_floor }} - {{ $rm->old_room_seat }}</td>
                <td class="fw-bold text-success">{{ $rm->new_floor }} - {{ $rm->new_room_seat }}</td>
                <td>৳ {{ number_format($rm->old_monthly_amount, 2) }} &rarr; ৳ {{ number_format($rm->new_monthly_amount, 2) }}</td>
                <td>৳ {{ number_format($rm->fee_amount, 2) }}</td>
                <td>{{ $rm->remarks ?: '-' }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="6" class="text-center text-muted py-3">No room change history found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>

</div>
@endsection
