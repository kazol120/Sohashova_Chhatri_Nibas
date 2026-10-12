@extends('backend.layouts.app')
@section('title', 'Check In / Check Out Management')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Page Header & Action Buttons -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="ti ti-door-exit text-warning fs-2"></i>
                <span>Check In / Check Out Tracking</span>
            </h4>
            <p class="text-muted mb-0 small">Record and track hostel leave departure and return history</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-primary fw-bold shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#manualCheckoutModal">
                <i class="ti ti-plus fs-5"></i>
                <span>Manual Check-Out Entry</span>
            </button>
        </div>
    </div>

    <!-- 4 Summary Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('check-in-out.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-3 h-100" style="border-top: 4px solid #6366f1 !important; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small d-block mb-1">Active Residents</span>
                                <h3 class="fw-bold mb-0 text-dark">{{ $totalActiveResidents }}</h3>
                            </div>
                            <div class="avatar avatar-md bg-label-primary rounded-circle p-2 d-flex align-items-center justify-content-center">
                                <i class="ti ti-users fs-3 text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('check-in-out.index') }}?status=inside" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-3 h-100" style="border-top: 4px solid #10b981 !important; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-success small d-block mb-1 fw-semibold">🟢 Currently In Hostel</span>
                                <h3 class="fw-bold mb-0 text-success">{{ $currentlyInsideCount }}</h3>
                            </div>
                            <div class="avatar avatar-md bg-label-success rounded-circle p-2 d-flex align-items-center justify-content-center">
                                <i class="ti ti-home-check fs-3 text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('check-in-out.index') }}?status=outside" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-3 h-100" style="border-top: 4px solid #f59e0b !important; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-warning small d-block mb-1 fw-semibold">🟡 Outside / On Leave</span>
                                <h3 class="fw-bold mb-0 text-warning">{{ $currentlyOutsideCount }}</h3>
                            </div>
                            <div class="avatar avatar-md bg-label-warning rounded-circle p-2 d-flex align-items-center justify-content-center">
                                <i class="ti ti-walking fs-3 text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('check-in-out.index') }}?status=overdue" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-3 h-100" style="border-top: 4px solid #ef4444 !important; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-danger small d-block mb-1 fw-semibold">⏰ Overdue Return</span>
                                <h3 class="fw-bold mb-0 text-danger">{{ $overdueCount }}</h3>
                            </div>
                            <div class="avatar avatar-md bg-label-danger rounded-circle p-2 d-flex align-items-center justify-content-center">
                                <i class="ti ti-alert-circle fs-3 text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('check-in-out.index') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Status Filter</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Records (সকল রেকর্ড)</option>
                        <option value="outside" {{ request('status') === 'outside' ? 'selected' : '' }}>🟡 Currently Outside (ছুটিতে আছেন)</option>
                        <option value="inside" {{ (request('status') === 'inside' || request('status') === 'returned') ? 'selected' : '' }}>🟢 Currently In Hostel / Returned (হোস্টেলে আছেন)</option>
                        <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>⏰ Overdue Return (দেরি হয়েছে)</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Search (Name, Phone, Seat)</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="ti ti-search text-muted"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search name or mobile number...">
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1">From Date</label>
                    <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control form-control-sm">
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1">To Date</label>
                    <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control form-control-sm">
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm fw-bold w-100">
                        <i class="ti ti-filter me-1"></i> Filter
                    </button>
                    @if(request()->hasAny(['status', 'search', 'from_date', 'to_date']))
                        <a href="{{ route('check-in-out.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                            <i class="ti ti-refresh"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Main Data Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header py-3 bg-white border-bottom d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                <i class="ti ti-list-details text-primary"></i>
                <span>Check In / Check Out History (Total: {{ $logs->total() }})</span>
            </h6>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Resident Info</th>
                        <th>Check-Out Date</th>
                        <th>Expected Return</th>
                        <th>Check-In Date</th>
                        <th>Reason / Note</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th class="text-center" style="width: 140px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $index => $log)
                    @php
                        $isOverdue = ($log->status == 0 && $log->expected_return_date && \Carbon\Carbon::parse($log->expected_return_date)->isPast() && !\Carbon\Carbon::parse($log->expected_return_date)->isToday());
                    @endphp
                    <tr class="{{ $log->status == 0 ? 'table-warning bg-opacity-10' : '' }}">
                        <td>{{ $logs->firstItem() + $index }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-2.5">
                                    <span class="avatar-initial rounded-circle {{ $log->status == 0 ? 'bg-label-warning text-warning' : 'bg-label-success text-success' }} fw-bold">
                                        {{ strtoupper(substr($log->resident_name ?? 'R', 0, 1)) }}
                                    </span>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark fs-7">{{ $log->resident_name }}</div>
                                    <div class="text-muted small">
                                        <i class="ti ti-phone me-1"></i>{{ $log->phone }}
                                    </div>
                                    <span class="badge bg-light text-primary border border-primary-subtle px-1.5 py-0.5 mt-0.5" style="font-size: 10.5px;">
                                        {{ $log->floor_room_seat ?: 'Hostel Seat' }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="fw-semibold text-dark">
                                {{ $log->check_out_date ? $log->check_out_date->format('d M, Y') : '-' }}
                            </div>
                        </td>

                        <td>
                            @if($log->expected_return_date)
                                <div class="fw-semibold {{ $isOverdue ? 'text-danger' : 'text-dark' }}">
                                    {{ \Carbon\Carbon::parse($log->expected_return_date)->format('d M, Y') }}
                                </div>
                                @if($isOverdue)
                                    <span class="badge bg-danger px-1.5 py-0.5" style="font-size: 10px;">⏰ Overdue</span>
                                @elseif($log->status == 0)
                                    <small class="text-muted">
                                        ({{ \Carbon\Carbon::parse($log->expected_return_date)->diffForHumans() }})
                                    </small>
                                @endif
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>

                        <td>
                            @if($log->check_in_date)
                                <div class="fw-semibold text-success">
                                    {{ $log->check_in_date->format('d M, Y') }}
                                </div>
                            @else
                                <span class="badge bg-label-secondary px-2 py-1 small">Not Returned Yet</span>
                            @endif
                        </td>

                        <td>
                            <div class="fw-medium text-dark text-wrap" style="max-width: 180px;">
                                {{ $log->leave_reason ?: 'Going Home' }}
                            </div>
                            @if($log->emergency_contact)
                                <small class="text-muted d-block mt-0.5">
                                    <i class="ti ti-phone-call text-danger me-1"></i>Emergency: {{ $log->emergency_contact }}
                                </small>
                            @endif
                            @if($log->admin_notes)
                                <small class="text-primary d-block mt-0.5" title="{{ $log->admin_notes }}">
                                    <i class="ti ti-notes me-1"></i>{{ \Illuminate\Support\Str::limit($log->admin_notes, 25) }}
                                </small>
                            @endif
                        </td>

                        <td>
                            @if($log->status == 0)
                                @php
                                    $isLogFuture = \Carbon\Carbon::parse($log->check_out_date)->startOfDay()->gt(\Carbon\Carbon::today());
                                @endphp
                                @if($isLogFuture)
                                    <span class="badge bg-label-info text-info px-2.5 py-1.5 fw-bold rounded-pill">
                                        <i class="ti ti-calendar-time me-1"></i> Upcoming Leave
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark px-2.5 py-1.5 fw-bold rounded-pill">
                                        <i class="ti ti-walking me-1"></i> Currently Outside
                                    </span>
                                @endif
                            @elseif($log->status == 1)
                                <span class="badge bg-success text-white px-2.5 py-1.5 fw-bold rounded-pill">
                                    <i class="ti ti-check me-1"></i> Checked In
                                </span>
                            @else
                                <span class="badge bg-secondary px-2 py-1 rounded-pill">Cancelled</span>
                            @endif
                        </td>

                        <td>
                            @if($log->created_by === 'resident')
                                <span class="badge bg-label-info px-2 py-1" style="font-size: 11px;">
                                    <i class="ti ti-user me-1"></i> Resident
                                </span>
                            @else
                                <span class="badge bg-label-primary px-2 py-1" style="font-size: 11px;">
                                    <i class="ti ti-shield me-1"></i> Admin
                                </span>
                            @endif
                        </td>

                        <td class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <button type="button" class="btn btn-sm btn-info fw-bold px-2 py-1 shadow-sm d-flex align-items-center" onclick="openResidentHistoryModal({{ $log->id }})" title="View Full Leave History">
                                    <i class="ti ti-eye fs-6"></i>
                                </button>
                                @if($log->status == 0)
                                    <button type="button" class="btn btn-sm btn-success fw-bold px-2.5 py-1 shadow-sm d-flex align-items-center gap-1" onclick="openCheckInModal({{ $log->id }}, '{{ addslashes($log->resident_name) }}')" title="Record Check In">
                                        <i class="ti ti-login fs-6"></i>
                                        <span>Check In</span>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="ti ti-door-off fs-1 text-muted opacity-50 d-block mb-2"></i>
                            <h6>No check in / check out records found</h6>
                            <p class="small mb-0">Click "Manual Check-Out Entry" button above to add a new leave entry.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="card-footer py-3 bg-white border-top d-flex align-items-center justify-content-between">
            <small class="text-muted">
                Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} entries
            </small>
            <div>
                {{ $logs->links() }}
            </div>
        </div>
        @endif
    </div>

</div>

<!-- Modal 1: Manual Check-Out Entry by Admin -->
<div class="modal fade" id="manualCheckoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header py-3 px-4 text-white" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 16px 16px 0 0;">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2 mb-0">
                    <i class="ti ti-door-exit fs-3"></i>
                    <span>Manual Check-Out Entry</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="manualCheckoutForm">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold text-dark">Select Resident <span class="text-danger">*</span></label>
                            <select name="booking_id" id="booking_id_select" class="form-select" required>
                                <option value="">-- Select Active Resident --</option>
                                @foreach($activeBookings as $ab)
                                    <option value="{{ $ab->id }}" data-phone="{{ $ab->phone }}" data-guardian="{{ $ab->guardian_number ?? $ab->parents_phone_number }}">
                                        {{ $ab->full_name }} — {{ $ab->phone }} ({{ $ab->room_number ?? 'Seat' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Check-Out Date <span class="text-danger">*</span></label>
                            <input type="date" name="check_out_date" id="check_out_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Expected Return Date</label>
                            <input type="date" name="expected_return_date" id="expected_return_date" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Leave Reason / Destination</label>
                            <input type="text" name="leave_reason" id="leave_reason" placeholder="e.g. Going Home, Exam Leave, Medical..." class="form-control" value="Going Home">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Emergency Guardian Contact <span class="text-danger">*</span></label>
                            <input type="text" name="emergency_contact" id="emergency_contact" placeholder="017xxxxxxxx" class="form-control" required minlength="10" maxlength="20">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold text-dark">Admin Notes / Remarks (Optional)</label>
                            <textarea name="admin_notes" id="admin_notes" rows="2" placeholder="Enter any additional notes if needed..." class="form-control"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2.5 px-4 d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning fw-bold px-4 text-dark shadow-sm" id="btnSubmitCheckout">
                        <i class="ti ti-check me-1"></i> Confirm Check-Out
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Check-In Confirmation by Admin -->
<div class="modal fade" id="checkInModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header py-3 px-4 text-white" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 16px 16px 0 0;">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2 mb-0">
                    <i class="ti ti-door-enter fs-3"></i>
                    <span>Confirm Check-In</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="checkInForm">
                @csrf
                <input type="hidden" id="checkin_log_id" name="log_id">
                <div class="modal-body p-4">
                    <div class="alert alert-success border-0 rounded-3 p-3 mb-3 d-flex align-items-center gap-2">
                        <i class="ti ti-user-check fs-3"></i>
                        <div>
                            Resident: <strong id="checkin_resident_name" class="fs-6"></strong><br>
                            <small class="text-muted">Checking in will update the resident status to "Currently In Hostel".</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Check-In Date <span class="text-danger">*</span></label>
                        <input type="date" name="check_in_date" id="check_in_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-bold text-dark">Notes / Remarks (Optional)</label>
                        <textarea name="admin_notes" id="checkin_admin_notes" rows="2" placeholder="Enter remarks if any..." class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2.5 px-4 d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-bold px-4 shadow-sm" id="btnSubmitCheckin">
                        <i class="ti ti-check me-1"></i> Confirm Check-In
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 3: Resident Full Check In / Out History Modal -->
<div class="modal fade" id="residentHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header py-3 px-4 text-white" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); border-radius: 16px 16px 0 0;">
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar avatar-sm bg-white text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm">
                        <i class="ti ti-history fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" id="hist_resident_title">Resident Leave History</h5>
                        <small class="text-white-50" id="hist_resident_subtitle"></small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Summary Stats Pills -->
                <div class="row g-3 mb-4" id="hist_stats_row">
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small d-block">Total Check-Out Entries</span>
                                <h4 class="fw-bold mb-0 text-dark" id="hist_total_count">0</h4>
                            </div>
                            <span class="badge bg-label-primary p-2.5 rounded-circle"><i class="ti ti-door-exit fs-3"></i></span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small d-block">Completed Check-Ins</span>
                                <h4 class="fw-bold mb-0 text-success" id="hist_completed_count">0</h4>
                            </div>
                            <span class="badge bg-label-success p-2.5 rounded-circle"><i class="ti ti-check fs-3"></i></span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small d-block">Current Status</span>
                                <div id="hist_current_status_badge" class="mt-1">
                                    <span class="badge bg-success">In Hostel</span>
                                </div>
                            </div>
                            <span class="badge bg-label-info p-2.5 rounded-circle"><i class="ti ti-user-check fs-3"></i></span>
                        </div>
                    </div>
                </div>

                <!-- History Table -->
                <div class="table-responsive border rounded-3 text-nowrap">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 45px;">#</th>
                                <th>Check-Out Date</th>
                                <th>Expected Return</th>
                                <th>Check-In Date</th>
                                <th>Reason / Note</th>
                                <th>Emergency Contact</th>
                                <th>Status</th>
                                <th>Created By</th>
                            </tr>
                        </thead>
                        <tbody id="hist_table_body">
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <span class="spinner-border spinner-border-sm me-1"></span> Loading history...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light py-2.5 px-4 d-flex justify-content-end">
                <button type="button" class="btn btn-secondary px-4 rounded-2" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Auto populate emergency phone on resident select
    var bookingSelect = document.getElementById('booking_id_select');
    if (bookingSelect) {
        bookingSelect.addEventListener('change', function () {
            var selectedOpt = this.options[this.selectedIndex];
            var guardian = selectedOpt.getAttribute('data-guardian') || selectedOpt.getAttribute('data-phone') || '';
            var emerField = document.getElementById('emergency_contact');
            if (emerField && guardian) {
                emerField.value = guardian;
            }
        });
    }

    // Submit Manual Checkout
    var checkoutForm = document.getElementById('manualCheckoutForm');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var btn = document.getElementById('btnSubmitCheckout');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Submitting...';

            var formData = new FormData(this);

            fetch("{{ route('check-in-out.store-admin') }}", {
                method: "POST",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="ti ti-check me-1"></i> Confirm Check-Out';
                if (data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: data.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Error!', data.message || 'Check-out could not be processed.', 'error');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="ti ti-check me-1"></i> Confirm Check-Out';
                Swal.fire('Error!', 'Server connection error.', 'error');
            });
        });
    }

    // Submit Check-In
    var checkInForm = document.getElementById('checkInForm');
    if (checkInForm) {
        checkInForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var logId = document.getElementById('checkin_log_id').value;
            var btn = document.getElementById('btnSubmitCheckin');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Submitting...';

            var formData = new FormData(this);

            fetch("{{ url('/check-in-out') }}/" + logId + "/check-in-admin", {
                method: "POST",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="ti ti-check me-1"></i> Confirm Check-In';
                if (data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: data.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Error!', data.message || 'Check-in could not be processed.', 'error');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="ti ti-check me-1"></i> Confirm Check-In';
                Swal.fire('Error!', 'Server connection error.', 'error');
            });
        });
    }
});

function openCheckInModal(id, name) {
    document.getElementById('checkin_log_id').value = id;
    document.getElementById('checkin_resident_name').innerText = name;
    var myModal = new bootstrap.Modal(document.getElementById('checkInModal'));
    myModal.show();
}

function openResidentHistoryModal(id) {
    var modalEl = document.getElementById('residentHistoryModal');
    var myModal = bootstrap.Modal.getOrCreateInstance(modalEl);
    
    document.getElementById('hist_resident_title').innerText = 'Loading History...';
    document.getElementById('hist_resident_subtitle').innerText = '';
    document.getElementById('hist_total_count').innerText = '0';
    document.getElementById('hist_completed_count').innerText = '0';
    document.getElementById('hist_table_body').innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted"><span class="spinner-border spinner-border-sm me-1"></span> Loading records...</td></tr>';
    
    myModal.show();

    fetch("{{ url('/check-in-out') }}/" + id + "/history")
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('hist_resident_title').innerText = data.resident_name + ' — Leave History';
                document.getElementById('hist_resident_subtitle').innerText = (data.phone || '') + (data.floor_room_seat ? ' (' + data.floor_room_seat + ')' : '');
                document.getElementById('hist_total_count').innerText = data.total_leaves;
                document.getElementById('hist_completed_count').innerText = data.completed_leaves;
                
                var statusBadge = document.getElementById('hist_current_status_badge');
                if (data.has_active_leave) {
                    statusBadge.innerHTML = '<span class="badge bg-warning text-dark fw-bold px-2 py-1 rounded-pill"><i class="ti ti-walking me-1"></i> Currently Outside</span>';
                } else {
                    statusBadge.innerHTML = '<span class="badge bg-success text-white fw-bold px-2 py-1 rounded-pill"><i class="ti ti-home me-1"></i> Currently In Hostel</span>';
                }

                if (!data.logs || data.logs.length === 0) {
                    document.getElementById('hist_table_body').innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">No check in / check out history found for this resident.</td></tr>';
                    return;
                }

                var html = '';
                data.logs.forEach(function(item, idx) {
                    var statusHtml = '';
                    if (item.status == 0) {
                        if (item.is_future) {
                            statusHtml = '<span class="badge bg-label-info text-info px-2 py-1 rounded-pill fw-semibold"><i class="ti ti-calendar-time me-1"></i> Upcoming</span>';
                        } else {
                            statusHtml = '<span class="badge bg-warning text-dark px-2 py-1 rounded-pill fw-bold"><i class="ti ti-walking me-1"></i> Checked Out</span>';
                        }
                    } else {
                        statusHtml = '<span class="badge bg-success text-white px-2 py-1 rounded-pill fw-bold"><i class="ti ti-check me-1"></i> Checked In</span>';
                    }

                    var checkInDateHtml = item.check_in_date 
                        ? '<span class="text-success fw-semibold">' + item.check_in_date + '</span>' 
                        : '<span class="badge bg-label-secondary px-2 py-0.5 small">Not Returned</span>';

                    var notesHtml = item.admin_notes ? '<small class="text-muted d-block mt-0.5"><i class="ti ti-notes me-1"></i>' + item.admin_notes + '</small>' : '';

                    html += '<tr>' +
                        '<td>' + (idx + 1) + '</td>' +
                        '<td><span class="fw-semibold text-dark">' + item.check_out_date + '</span></td>' +
                        '<td><span class="text-dark">' + item.expected_return_date + '</span></td>' +
                        '<td>' + checkInDateHtml + '</td>' +
                        '<td><div class="text-wrap" style="max-width: 180px;">' + item.leave_reason + notesHtml + '</div></td>' +
                        '<td><span class="text-muted small fw-semibold">' + item.emergency_contact + '</span></td>' +
                        '<td>' + statusHtml + '</td>' +
                        '<td><span class="badge ' + (item.created_by === 'Resident' ? 'bg-label-info' : 'bg-label-primary') + ' px-2 py-0.5" style="font-size: 11px;">' + item.created_by + '</span></td>' +
                    '</tr>';
                });

                document.getElementById('hist_table_body').innerHTML = html;
            } else {
                document.getElementById('hist_table_body').innerHTML = '<tr><td colspan="8" class="text-center py-4 text-danger">Failed to load history records.</td></tr>';
            }
        })
        .catch(() => {
            document.getElementById('hist_table_body').innerHTML = '<tr><td colspan="8" class="text-center py-4 text-danger">Server connection error.</td></tr>';
        });
}
</script>
@endpush
@endsection
