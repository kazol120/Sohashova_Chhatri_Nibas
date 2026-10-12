@extends('backend.layouts.app')
@section('title', 'Check In / Check Out')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    @php
        $residentName = $booking->full_name ?? ($user->name ?? 'Student');
        $residentPhone = $booking->phone ?? ($user->phone ?? '');
    @endphp

    @php
        $isCurrentlyOutside = false;
        $isUpcomingCheckout = false;
        if ($activeCheckout) {
            $checkoutDate = \Carbon\Carbon::parse($activeCheckout->check_out_date)->startOfDay();
            $todayDate = \Carbon\Carbon::today();
            if ($checkoutDate->lte($todayDate)) {
                $isCurrentlyOutside = true;
            } else {
                $isUpcomingCheckout = true;
            }
        }
    @endphp

    <!-- Page Header & Action Button -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="ti ti-door-exit text-warning fs-2"></i>
                <span>Check In / Check Out</span>
            </h4>
            <p class="text-muted mb-0 small">Record and track hostel leave departure and return history</p>
        </div>
        <div>
            @if($isCurrentlyOutside)
                <button type="button" class="btn btn-success fw-bold shadow-sm d-flex align-items-center gap-2" onclick="handleStudentCheckIn()">
                    <i class="ti ti-door-enter fs-5"></i>
                    <span>Check In Now</span>
                </button>
            @else
                <button type="button" class="btn btn-warning text-dark fw-bold shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#studentLeaveModal">
                    <i class="ti ti-door-exit fs-5"></i>
                    <span>Check Out</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Active Status Card / Banner -->
    @if($isCurrentlyOutside)
        <div class="card border-0 shadow-sm rounded-4 mb-4 text-white overflow-hidden" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-white text-warning p-3 d-flex align-items-center justify-content-center shadow" style="width: 58px; height: 58px;">
                            <i class="ti ti-walking fs-2"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-white text-dark fw-bold px-2.5 py-1 rounded-pill">🟡 Currently Outside / On Leave</span>
                            </div>
                            <h5 class="fw-bold text-white mb-1">
                                Checked out on {{ $activeCheckout->check_out_date ? $activeCheckout->check_out_date->format('d M, Y') : '' }}
                            </h5>
                            <p class="mb-1 text-white-50 small">
                                Reason: <strong>{{ $activeCheckout->leave_reason ?: 'Going Home' }}</strong>
                                @if($activeCheckout->expected_return_date)
                                    | Expected Return: <strong>{{ \Carbon\Carbon::parse($activeCheckout->expected_return_date)->format('d M, Y') }}</strong>
                                @endif
                                @if($activeCheckout->emergency_contact)
                                    | Emergency Contact: <strong>{{ $activeCheckout->emergency_contact }}</strong>
                                @endif
                            </p>
                            <small class="d-block text-white fw-semibold bg-dark bg-opacity-25 px-2.5 py-1 rounded-2">
                                <i class="ti ti-salad me-1"></i> চেক-আউট থাকায় মিল বন্ধ আছে। হোস্টেলে ফিরে চেক-ইন করলে পরদিন থেকে মিল পুনরায় চালু হবে। (Meal will resume from the next day after check in)
                            </small>
                        </div>
                    </div>
                    <button type="button" class="btn btn-light text-success fw-bold px-4 py-2.5 rounded-3 shadow d-flex align-items-center gap-2" onclick="handleStudentCheckIn()">
                        <i class="ti ti-door-enter fs-4"></i>
                        <span>Check In Now</span>
                    </button>
                </div>
            </div>
        </div>
    @elseif($isUpcomingCheckout)
        <div class="card border-0 shadow-sm rounded-4 mb-4 text-white overflow-hidden" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-white text-primary p-3 d-flex align-items-center justify-content-center shadow" style="width: 58px; height: 58px;">
                            <i class="ti ti-calendar-event fs-2"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-white text-primary fw-bold px-2.5 py-1 rounded-pill">📅 Upcoming Leave Scheduled</span>
                            </div>
                            <h5 class="fw-bold text-white mb-1">
                                Scheduled Check-Out Date: {{ $activeCheckout->check_out_date ? $activeCheckout->check_out_date->format('d M, Y') : '' }}
                            </h5>
                            <p class="mb-1 text-white-50 small">
                                You are currently inside the hostel. Check-out will be effective from {{ $activeCheckout->check_out_date ? $activeCheckout->check_out_date->format('d M, Y') : '' }}.
                            </p>
                            <small class="d-block text-white fw-semibold bg-dark bg-opacity-25 px-2.5 py-1 rounded-2">
                                <i class="ti ti-salad me-1"></i> Meal will be turned OFF starting on checkout date and will resume upon check in.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
            <div class="card-body p-4 text-white">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-white text-success p-3 d-flex align-items-center justify-content-center shadow" style="width: 58px; height: 58px;">
                            <i class="ti ti-home-check fs-2"></i>
                        </div>
                        <div>
                            <span class="badge bg-white text-success fw-bold px-2.5 py-1 rounded-pill mb-1">🟢 Currently In Hostel</span>
                            <h5 class="fw-bold text-white mb-1">You are currently present in the hostel</h5>
                            <p class="mb-0 text-white-50 small">Please submit a check-out entry before leaving for home or vacation.</p>
                        </div>
                    </div>
                    <button type="button" class="btn btn-warning text-dark fw-bold px-4 py-2.5 rounded-3 shadow d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#studentLeaveModal">
                        <i class="ti ti-door-exit fs-4"></i>
                        <span>Check Out Entry</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- History Data Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header py-3 bg-white border-bottom d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                <i class="ti ti-history text-primary"></i>
                <span>Leave & Check In/Out History (Total: {{ $myLogs->total() }})</span>
            </h6>
        </div>
        {{-- Desktop Table View --}}
        <div class="d-none d-md-block table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0">
                <thead style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); color: #ffffff;">
                    <tr class="small text-white fw-bold text-uppercase">
                        <th style="width: 50px;" class="text-white">SL</th>
                        <th class="text-white">Check-Out Date</th>
                        <th class="text-white">Expected Return</th>
                        <th class="text-white">Check-In Date</th>
                        <th class="text-white">Reason</th>
                        <th class="text-white">Emergency Contact</th>
                        <th class="text-white text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @forelse($myLogs as $idx => $log)
                    <tr>
                        <td class="text-dark fw-bold">{{ $myLogs->firstItem() + $idx }}</td>
                        <td>
                            <div class="fw-bold text-dark">
                                {{ $log->check_out_date ? $log->check_out_date->format('d M, Y') : '-' }}
                            </div>
                        </td>

                        <td>
                            @if($log->expected_return_date)
                                <div class="fw-semibold text-dark">
                                    {{ \Carbon\Carbon::parse($log->expected_return_date)->format('d M, Y') }}
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td>
                            @if($log->check_in_date)
                                <div class="fw-bold text-success">
                                    {{ $log->check_in_date->format('d M, Y') }}
                                </div>
                            @else
                                <span class="badge px-2.5 py-1 fw-bold" style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a;">Not Returned Yet</span>
                            @endif
                        </td>

                        <td>
                            <span class="fw-medium text-dark">{{ $log->leave_reason ?: 'Going Home' }}</span>
                        </td>

                        <td>
                            <span class="text-dark small fw-semibold">{{ $log->emergency_contact ?: '-' }}</span>
                        </td>

                        <td class="text-center">
                            @if($log->status == 0)
                                @php
                                    $isLogFuture = \Carbon\Carbon::parse($log->check_out_date)->startOfDay()->gt(\Carbon\Carbon::today());
                                @endphp
                                @if($isLogFuture)
                                    <span class="badge px-3 py-1.5 rounded-pill fw-bold" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">
                                        <i class="ti ti-calendar-time me-1"></i> Upcoming
                                    </span>
                                @else
                                    <span class="badge px-3 py-1.5 rounded-pill fw-bold" style="background: #fef9c3; color: #854d0e; border: 1px solid #fde047;">
                                        <i class="ti ti-walking me-1"></i> Checked Out
                                    </span>
                                @endif
                            @else
                                <span class="badge px-3 py-1.5 rounded-pill fw-bold" style="background: #dcfce7; color: #166534; border: 1px solid #86efac;">
                                    <i class="ti ti-check me-1"></i> Checked In
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="ti ti-door-off fs-1 text-muted opacity-50 d-block mb-2"></i>
                            <h6>No check-out records found</h6>
                            <p class="small mb-0">Click the "Check Out" button above when going on leave.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Card View (Distinct Div Blocks with High-Contrast Colors) --}}
        <div class="d-md-none p-2 p-sm-3" style="background: #f1f5f9;">
            @forelse($myLogs as $idx => $log)
            <div class="card shadow-sm mb-3.5 rounded-4 overflow-hidden border-0" style="box-shadow: 0 4px 12px rgba(0,0,0,0.07) !important;">
                <div class="py-2.5 px-3 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff;">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary text-white rounded-circle fw-bold" style="width:24px; height:24px; display:inline-flex; align-items:center; justify-content:center; font-size:11px;">{{ $myLogs->firstItem() + $idx }}</span>
                        <span class="fw-bold text-white fs-7">
                            <i class="ti ti-door-exit me-1 text-warning"></i>
                            {{ $log->check_out_date ? $log->check_out_date->format('d M, Y') : '-' }}
                        </span>
                    </div>
                    <div>
                        @if($log->status == 0)
                            @php
                                $isLogFuture = \Carbon\Carbon::parse($log->check_out_date)->startOfDay()->gt(\Carbon\Carbon::today());
                            @endphp
                            @if($isLogFuture)
                                <span class="badge px-2.5 py-1 rounded-pill fw-bold" style="background: #38bdf8; color: #0f172a; font-size:0.72rem;">Upcoming</span>
                            @else
                                <span class="badge px-2.5 py-1 rounded-pill fw-bold" style="background: #eab308; color: #000000; font-size:0.72rem;">Checked Out</span>
                            @endif
                        @else
                            <span class="badge px-2.5 py-1 rounded-pill fw-bold" style="background: #22c55e; color: #ffffff; font-size:0.72rem;">Checked In</span>
                        @endif
                    </div>
                </div>

                <div class="card-body p-3 bg-white">
                    <div class="row g-2 mb-2 text-start">
                        <div class="col-6">
                            <div class="p-2.5 rounded-3 h-100" style="background: #eff6ff; border: 1.5px solid #bfdbfe;">
                                <span class="d-block fw-bold" style="color: #1e40af; font-size: 11px;">প্রত্যাশিত ফেরা:</span>
                                <span class="fw-bold text-dark fs-7 mt-1 d-block">
                                    {{ $log->expected_return_date ? \Carbon\Carbon::parse($log->expected_return_date)->format('d M, Y') : '-' }}
                                </span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2.5 rounded-3 h-100" style="background: #f0fdf4; border: 1.5px solid #bbf7d0;">
                                <span class="d-block fw-bold" style="color: #15803d; font-size: 11px;">আসল চেক-ইন:</span>
                                <span class="fw-bold text-dark fs-7 mt-1 d-block">
                                    {{ $log->check_in_date ? $log->check_in_date->format('d M, Y') : 'Not Yet' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="p-2.5 rounded-3 mt-2.5" style="background: #f8fafc; border: 1.5px solid #e2e8f0;">
                        <div class="text-dark small"><strong class="text-secondary">ছুটির কারণ:</strong> {{ $log->leave_reason ?: 'Going Home' }}</div>
                        @if($log->emergency_contact)
                            <div class="text-dark small mt-1"><strong class="text-secondary">জরুরি যোগাযোগ:</strong> {{ $log->emergency_contact }}</div>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-5 text-muted bg-white rounded-3">
                <i class="ti ti-door-off fs-1 text-muted opacity-50 d-block mb-2"></i>
                <h6>No check-out records found</h6>
                <p class="small mb-0">Click the "Check Out" button above when going on leave.</p>
            </div>
            @endforelse
        </div>

        @if($myLogs->hasPages())
        <div class="card-footer py-3 bg-white border-top d-flex align-items-center justify-content-between">
            <small class="text-muted">Showing {{ $myLogs->firstItem() }} to {{ $myLogs->lastItem() }} of {{ $myLogs->total() }} entries</small>
            <div>{{ $myLogs->links() }}</div>
        </div>
        @endif
    </div>

</div>

<!-- Student Leave / Check-Out Modal -->
<div class="modal fade" id="studentLeaveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header py-3 px-4 text-white" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 16px 16px 0 0;">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2 mb-0">
                    <i class="ti ti-door-exit fs-3"></i>
                    <span>Leave & Check-Out Entry</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="studentLeaveForm">
                @csrf
                <input type="hidden" name="phone" value="{{ $residentPhone }}">
                <div class="modal-body p-4">
                    <div class="alert alert-warning border-0 rounded-3 p-3 mb-3 d-flex align-items-start gap-2" style="background: #fffbeb;">
                        <i class="ti ti-info-circle fs-4 text-warning mt-1"></i>
                        <div class="small text-dark">
                            <div>Resident: <strong>{{ $residentName }}</strong> ({{ $residentPhone }})</div>
                            <div>Please select your departure date, expected return date, and emergency guardian number.</div>
                            <div class="mt-1.5 text-danger fw-semibold">
                                <i class="ti ti-salad me-1"></i> <strong>Meal Policy:</strong> Meals will be automatically turned OFF from your check-out date. When you return and Check In, meals will resume automatically.
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Check-Out Date <span class="text-danger">*</span></label>
                            <input type="date" name="check_out_date" id="leave_check_out_date" value="{{ date('Y-m-d') }}" class="form-control rounded-2" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Expected Return Date</label>
                            <input type="date" name="expected_return_date" id="leave_expected_return_date" class="form-control rounded-2">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Leave Reason / Destination <span class="text-danger">*</span></label>
                            <input type="text" name="leave_reason" id="leave_leave_reason" placeholder="e.g. Going Home, Exam Leave..." class="form-control rounded-2" value="Going Home" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Emergency / Guardian Contact <span class="text-danger">*</span></label>
                            <input type="tel" name="emergency_contact" id="leave_emergency_contact" value="{{ $booking->guardian_number ?? ($booking->parents_phone_number ?? '') }}" placeholder="017xxxxxxxx" class="form-control rounded-2" required minlength="10" maxlength="20">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2.5 px-4 d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-secondary px-4 rounded-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning fw-bold px-4 text-dark rounded-2 shadow-sm" id="btnSubmitLeave">
                        <i class="ti ti-check me-1"></i> Confirm Check-Out
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.modal {
    z-index: 1085 !important;
}
.modal-backdrop {
    z-index: 1075 !important;
}
#studentLeaveModal {
    z-index: 99999 !important;
}
#studentLeaveModal .modal-dialog {
    z-index: 100000 !important;
    position: relative !important;
}
</style>

@push('script')

<script>
document.addEventListener('DOMContentLoaded', function () {
    var leaveModal = document.getElementById('studentLeaveModal');
    if (leaveModal && leaveModal.parentElement !== document.body) {
        document.body.appendChild(leaveModal);
    }

    var leaveForm = document.getElementById('studentLeaveForm');
    if (leaveForm) {
        leaveForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var btn = document.getElementById('btnSubmitLeave');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Submitting...';

            var formData = new FormData(this);

            fetch("{{ route('resident.check-out') }}", {
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
                        timer: 2200,
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
});

function handleStudentCheckIn() {
    Swal.fire({
        title: 'Check In Confirmation',
        text: 'আপনি কি নিশ্চিত যে হোস্টেলে ফিরে এসেছেন এবং Check In করতে চান? চেক-ইন সম্পন্ন হলে পরদিন থেকে আপনার মিল পুনরায় স্বয়ংক্রিয়ভাবে চালু হবে। (Are you sure you want to Check In? Meal will resume from tomorrow)',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Check In Now',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            var formData = new FormData();
            formData.append('phone', '{{ $residentPhone }}');

            fetch("{{ route('resident.check-in') }}", {
                method: "POST",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Welcome Back!',
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
            .catch(() => {
                Swal.fire('Error!', 'Server connection error.', 'error');
            });
        }
    });
}
</script>
@endpush
@endsection
