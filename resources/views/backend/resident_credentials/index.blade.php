@extends('backend.layouts.app')
@section("title") | Resident Passwords & Login Credentials @endsection

@push('style')
<style>
  .cred-table th {
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
  .history-badge-pending {
    background-color: #fffbeb;
    color: #b45309;
    border: 1px solid #fcd34d;
  }
  .history-badge-deleted {
    background-color: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
  }
  .cred-pass-box {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #f8fafc;
    padding: 4px 10px;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
  }
  .cred-pass-text {
    font-family: monospace;
    font-size: 15px;
    font-weight: 700;
    color: #033364;
    letter-spacing: 1.5px;
    min-width: 65px;
  }
  .btn-icon-xs {
    padding: 2px 6px;
    font-size: 13px;
    line-height: 1;
    border-radius: 5px;
  }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  
  <!-- Header & Title -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
      <h4 class="fw-bold mb-1" style="color: #033364;">
        <i class="ti ti-key text-primary me-2"></i>Resident Passwords & Login Credentials
      </h4>
      <p class="text-muted small mb-0">View all residents' room, seat and login passwords, and send login credentials directly to their email.</p>
    </div>
    
    <div class="d-flex align-items-center gap-2">
      <!-- Bulk Send Button -->
      <button type="button" class="btn btn-primary fw-bold shadow-sm d-inline-flex align-items-center gap-1.5" onclick="triggerBulkEmailSend()">
        <i class="ti ti-mail-fast fs-5"></i>
        <span>Send Password to All via Email</span>
      </button>
    </div>
  </div>

  <!-- Stat Cards (Matching Resident Full History Design) -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm stat-card h-100" style="border-left: 4px solid #033364 !important;">
        <div class="card-body d-flex align-items-center justify-content-between p-3">
          <div>
            <span class="text-muted fw-semibold small">Total Residents</span>
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
      <div class="card border-0 shadow-sm stat-card h-100" style="border-left: 4px solid #0ea5e9 !important;">
        <div class="card-body d-flex align-items-center justify-content-between p-3">
          <div>
            <span class="text-muted fw-semibold small">Has Email</span>
            <h3 class="mb-0 fw-bold mt-1 text-info">{{ $hasEmailCount }}</h3>
          </div>
          <div class="avatar avatar-md bg-light-info rounded p-2 text-center">
            <i class="ti ti-mail-check text-info fs-3"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm stat-card h-100" style="border-left: 4px solid #ef4444 !important;">
        <div class="card-body d-flex align-items-center justify-content-between p-3">
          <div>
            <span class="text-muted fw-semibold small">No Email</span>
            <h3 class="mb-0 fw-bold mt-1 text-danger">{{ $noEmailCount }}</h3>
          </div>
          <div class="avatar avatar-md bg-light-danger rounded p-2 text-center">
            <i class="ti ti-mail-off text-danger fs-3"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Filters & Search Form Card -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
      <form method="GET" action="{{ route('resident-credentials.index') }}" id="credSearchForm" onsubmit="event.preventDefault(); return false;" class="row g-2 align-items-center">
        <!-- Search Input -->
        <div class="col-12 col-md-5">
          <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="ti ti-search text-muted"></i></span>
            <input type="text" name="search" id="credSearchInput" class="form-control border-start-0 ps-0" 
                   placeholder="Search by name, phone, room or seat..." 
                   value="{{ request('search') }}" autocomplete="off">
            @if(request()->has('search') || request()->has('filter'))
              <a href="{{ route('resident-credentials.index') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1" title="Reset Search">
                <i class="ti ti-rotate-clockwise"></i> Reset
              </a>
            @endif
          </div>
        </div>

        <!-- Filter Buttons Group -->
        <div class="col-12 col-md-7">
          <div class="btn-group w-100 flex-wrap" role="group">
            <a href="{{ route('resident-credentials.index', array_merge(request()->except('filter'), ['filter' => 'all'])) }}" 
               class="btn btn-sm {{ ($filter ?? 'all') === 'all' ? 'btn-primary fw-bold' : 'btn-outline-primary' }}">
              All ({{ $totalCount }})
            </a>
            <a href="{{ route('resident-credentials.index', array_merge(request()->except('filter'), ['filter' => 'active'])) }}" 
               class="btn btn-sm {{ ($filter ?? '') === 'active' ? 'btn-success fw-bold' : 'btn-outline-success' }}">
              Active ({{ $activeCount }})
            </a>
            @if(isset($pendingCount) && $pendingCount > 0)
            <a href="{{ route('resident-credentials.index', array_merge(request()->except('filter'), ['filter' => 'pending'])) }}" 
               class="btn btn-sm {{ ($filter ?? '') === 'pending' ? 'btn-warning fw-bold text-dark' : 'btn-outline-warning text-dark' }}">
              Pending ({{ $pendingCount }})
            </a>
            @endif
            <a href="{{ route('resident-credentials.index', array_merge(request()->except('filter'), ['filter' => 'has_email'])) }}" 
               class="btn btn-sm {{ ($filter ?? '') === 'has_email' ? 'btn-info fw-bold text-white' : 'btn-outline-info' }}">
              Has Email ({{ $hasEmailCount }})
            </a>
            <a href="{{ route('resident-credentials.index', array_merge(request()->except('filter'), ['filter' => 'no_email'])) }}" 
               class="btn btn-sm {{ ($filter ?? '') === 'no_email' ? 'btn-danger fw-bold' : 'btn-outline-danger' }}">
              No Email ({{ $noEmailCount }})
            </a>
            <a href="{{ route('resident-credentials.index', array_merge(request()->except('filter'), ['filter' => 'released'])) }}" 
               class="btn btn-sm {{ ($filter ?? '') === 'released' ? 'btn-secondary fw-bold' : 'btn-outline-secondary' }}">
              Released ({{ $releasedCount }})
            </a>
            <a href="{{ route('resident-credentials.index', array_merge(request()->except('filter'), ['filter' => 'deleted'])) }}" 
               class="btn btn-sm {{ ($filter ?? '') === 'deleted' || ($filter ?? '') === 'inactive' ? 'btn-danger fw-bold' : 'btn-outline-danger' }}">
              Deleted ({{ $deletedCount }})
            </a>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Resident Credentials Table Card -->
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0 fw-bold" style="color: #033364;">
        <i class="ti ti-list-details me-2"></i>Resident Passwords & Login Credentials List
      </h5>
      <span class="badge bg-label-primary px-3 py-2 rounded-pill font-monospace" id="totalDisplayedBadge" style="font-size: 0.85rem;">
        Total Displayed: {{ count($residents) }}
      </span>
    </div>

    <div class="table-responsive text-nowrap">
      <table class="table table-hover cred-table align-middle mb-0" id="residentCredTable">
        <thead class="table-light">
          <tr>
            <th class="ps-3" style="width: 50px;">#</th>
            <th>Resident Profile & Name</th>
            <th>Phone & Login ID</th>
            <th>Email Address</th>
            <th>Room & Seat Details</th>
            <th>Login Password</th>
            <th>Status</th>
            <th class="text-end pe-3">Email Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($residents as $index => $resident)
          <tr id="row-resident-{{ $resident->id }}">
            
            <!-- Index -->
            <td class="ps-3 fw-bold text-muted">{{ $loop->iteration }}</td>

            <!-- Resident Profile -->
            <td>
              <div class="d-flex align-items-center gap-2.5">
                @if(!empty($resident->image) && file_exists(public_path('storage/user/' . $resident->image)))
                  <img src="{{ asset('storage/user/' . $resident->image) }}" alt="Avatar" class="avatar-sm">
                @elseif(!empty($resident->image) && file_exists(public_path('bookingsimage/' . $resident->image)))
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
                  <span class="badge bg-label-secondary" style="font-size: 0.72rem;">{{ $resident->user_type }}</span>
                </div>
              </div>
            </td>

            <!-- Phone / Login ID -->
            <td>
              <span class="fw-bold font-monospace text-dark">{{ $resident->phone }}</span>
            </td>

            <!-- Email Address -->
            <td>
              @if($resident->has_email)
                <span class="text-dark fw-semibold font-monospace small">
                  <i class="ti ti-mail text-success me-1"></i>{{ $resident->email }}
                </span>
              @else
                <span class="badge bg-label-danger" style="font-size: 11px;">
                  <i class="ti ti-alert-triangle me-1"></i>No Email
                </span>
              @endif
            </td>

            <!-- Room & Seat -->
            <td>
              <div class="fw-bold text-dark">{{ $resident->room_seat }}</div>
            </td>

            <!-- Password with Eye Toggle & Edit -->
            <td>
              <div class="cred-pass-box">
                <span class="cred-pass-text" id="pass-display-{{ $resident->id }}" data-pass="{{ $resident->password }}" data-masked="true">••••••</span>
                
                <!-- Eye Toggle -->
                <button type="button" class="btn btn-sm btn-outline-secondary btn-icon-xs" onclick="togglePassVisibility('{{ $resident->id }}')" title="View Password">
                  <i class="ti ti-eye" id="eye-icon-{{ $resident->id }}"></i>
                </button>

                <!-- Edit Password -->
                <button type="button" class="btn btn-sm btn-outline-warning btn-icon-xs" onclick="openEditPasswordModal('{{ $resident->id }}', '{{ $resident->name }}', '{{ $resident->password }}')" title="Change Password">
                  <i class="ti ti-edit"></i>
                </button>
              </div>
            </td>

            <!-- Status Badge (Matching Resident History Design) -->
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
                <span class="badge history-badge-pending px-2.5 py-1.5 rounded-pill fw-semibold">
                  <i class="ti ti-clock me-1"></i>Pending
                </span>
              @endif
            </td>

            <!-- Send Email Button -->
            <td class="text-end pe-3">
              @if($resident->has_email)
                <button type="button" class="btn btn-sm btn-primary fw-bold d-inline-flex align-items-center gap-1.5 shadow-sm" id="btn-send-{{ $resident->id }}" onclick="sendCredentialEmail('{{ $resident->id }}', '{{ $resident->name }}', '{{ $resident->email }}')">
                  <i class="ti ti-send"></i>
                  <span>Send Email</span>
                </button>
              @else
                <button type="button" class="btn btn-sm btn-secondary opacity-50 d-inline-flex align-items-center gap-1" disabled title="Cannot send without email">
                  <i class="ti ti-mail-off"></i>
                  <span>No Email</span>
                </button>
              @endif
            </td>

          </tr>
          @empty
          <tr>
            <td colspan="8" class="text-center py-5 text-muted">
              <i class="ti ti-users-minus fs-1 d-block mb-2 text-secondary"></i>
              No resident records found.
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

  </div>

</div>

<!-- Modal: Edit Password -->
<div class="modal fade" id="modalEditPassword" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header bg-light py-3">
        <h5 class="modal-title fw-bold fs-6 mb-0 text-dark">
          <i class="ti ti-lock text-warning me-1.5"></i>Change Password
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-3.5">
        <p class="small text-muted mb-3" id="editPassResidentNameText"></p>
        <input type="hidden" id="editPassBookingId">
        <div class="mb-3">
          <label class="form-label fw-bold small">New Password (6 Characters) <code class="text-danger">*</code></label>
          <input type="password" id="inputNewPassModal" class="form-control text-center font-monospace fw-bold fs-5" maxlength="6" minlength="6" placeholder="------" oninput="if(this.value.length>6)this.value=this.value.slice(0,6);" />
          <small class="text-muted" style="font-size: 11px;">Enter exactly 6 characters password</small>
        </div>
        <button type="button" class="btn btn-primary w-100 fw-bold py-2 rounded-3" id="btnSavePasswordModal" onclick="saveNewPassword()">
          <i class="ti ti-check me-1"></i> Save Changes
        </button>
      </div>
    </div>
  </div>
</div>

@endsection

@push('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  // Toggle password display
  function togglePassVisibility(id) {
    const el = document.getElementById(`pass-display-${id}`);
    const icon = document.getElementById(`eye-icon-${id}`);
    if (!el || !icon) return;

    const isMasked = el.getAttribute('data-masked') === 'true';
    const pass = el.getAttribute('data-pass');

    if (isMasked) {
      el.innerText = pass;
      el.setAttribute('data-masked', 'false');
      icon.classList.remove('ti-eye');
      icon.classList.add('ti-eye-off');
    } else {
      el.innerText = '••••••';
      el.setAttribute('data-masked', 'true');
      icon.classList.remove('ti-eye-off');
      icon.classList.add('ti-eye');
    }
  }

  // Copy password to clipboard
  function copyPassword(text) {
    navigator.clipboard.writeText(text).then(() => {
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'Password copied: ' + text,
        showConfirmButton: false,
        timer: 2000
      });
    }).catch(() => {
      alert('Failed to copy password.');
    });
  }

  // Open Edit Password Modal
  function openEditPasswordModal(bookingId, residentName, currentPass) {
    document.getElementById('editPassBookingId').value = bookingId;
    document.getElementById('editPassResidentNameText').innerHTML = `Resident: <strong>${residentName}</strong>`;
    document.getElementById('inputNewPassModal').value = currentPass;
    const modalEl = document.getElementById('modalEditPassword');
    if (modalEl) new bootstrap.Modal(modalEl).show();
  }

  // Save new password via AJAX
  async function saveNewPassword() {
    const bookingId = document.getElementById('editPassBookingId').value;
    const newPass = document.getElementById('inputNewPassModal').value.trim();

    if (newPass.length !== 6) {
      Swal.fire({
        icon: 'warning',
        title: 'Invalid Input',
        text: 'Password must be exactly 6 characters.',
        confirmButtonText: 'OK'
      });
      return;
    }

    const btn = document.getElementById('btnSavePasswordModal');
    btn.disabled = true;
    btn.innerText = 'Saving...';

    try {
      const res = await fetch('{{ route("resident-credentials.update-password") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          booking_id: bookingId,
          password: newPass
        })
      });

      const data = await res.json();
      if (data.success) {
        // Update DOM
        const passEl = document.getElementById(`pass-display-${bookingId}`);
        if (passEl) {
          passEl.setAttribute('data-pass', newPass);
          if (passEl.getAttribute('data-masked') === 'false') {
            passEl.innerText = newPass;
          }
        }
        bootstrap.Modal.getInstance(document.getElementById('modalEditPassword')).hide();
        Swal.fire({
          icon: 'success',
          title: 'Success!',
          text: data.message,
          timer: 2200,
          showConfirmButton: false
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Failed',
          text: data.message || 'Unable to update password.'
        });
      }
    } catch (e) {
      Swal.fire({
        icon: 'error',
        title: 'Server Error',
        text: 'A connection error occurred.'
      });
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<i class="ti ti-check me-1"></i> Save Changes';
    }
  }

  // Send single credential email
  async function sendCredentialEmail(bookingId, residentName, email) {
    const result = await Swal.fire({
      title: 'Send Login Info?',
      html: `Send login credentials and password to <strong>${residentName}</strong> at (<code>${email}</code>)?`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Yes, Send Email',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#033364',
    });

    if (!result.isConfirmed) return;

    const btn = document.getElementById(`btn-send-${bookingId}`);
    if (btn) {
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Sending...';
    }

    try {
      const res = await fetch('{{ route("resident-credentials.send-single") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ booking_id: bookingId })
      });

      const data = await res.json();
      if (data.success) {
        Swal.fire({
          icon: 'success',
          title: 'Email Sent!',
          text: data.message,
          confirmButtonColor: '#033364'
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Failed',
          text: data.message || 'Unable to send email.'
        });
      }
    } catch (e) {
      Swal.fire({
        icon: 'error',
        title: 'Server Error',
        text: 'An error occurred while sending email. Please try again.'
      });
    } finally {
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = '<i class="ti ti-send"></i> <span>Send Email</span>';
      }
    }
  }

  // Bulk send credential email to all active residents
  async function triggerBulkEmailSend() {
    const result = await Swal.fire({
      title: 'Send to All Residents?',
      text: 'Login ID and passwords will be emailed to all active residents with email addresses.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, Send to All',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#033364',
    });

    if (!result.isConfirmed) return;

    Swal.fire({
      title: 'Sending Emails...',
      html: 'Please wait while emails are being processed...',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    try {
      const res = await fetch('{{ route("resident-credentials.send-bulk") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json'
        }
      });

      const data = await res.json();
      if (data.success) {
        Swal.fire({
          icon: 'success',
          title: 'Completed!',
          text: data.message,
          confirmButtonColor: '#033364'
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Failed',
          text: data.message || 'Unable to send emails.'
        });
      }
    } catch (e) {
      Swal.fire({
        icon: 'error',
        title: 'Server Error',
        text: 'A server error occurred. Please try again.'
      });
    }
  }

  // Live Instant Search on Input (No Page Reload)
  document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('credSearchInput');
    const searchForm = document.getElementById('credSearchForm');

    if (searchForm) {
      searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        return false;
      });
    }

    if (searchInput) {
      // Perform immediate initial filter if input already contains value
      if (searchInput.value) {
        const initialQuery = searchInput.value.toLowerCase().trim();
        const rows = document.querySelectorAll('#residentCredTable tbody tr');
        let visibleCount = 0;
        rows.forEach(row => {
          if (row.cells.length === 1) return;
          const text = row.textContent.toLowerCase();
          if (!initialQuery || text.includes(initialQuery)) {
            row.style.display = '';
            visibleCount++;
          } else {
            row.style.display = 'none';
          }
        });
        const totalBadge = document.getElementById('totalDisplayedBadge');
        if (totalBadge) {
          totalBadge.textContent = `Total Displayed: ${visibleCount}`;
        }
      }

      searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        const rows = document.querySelectorAll('#residentCredTable tbody tr');
        let visibleCount = 0;

        rows.forEach(row => {
          if (row.cells.length === 1) return;
          const text = row.textContent.toLowerCase();
          if (!query || text.includes(query)) {
            row.style.display = '';
            visibleCount++;
          } else {
            row.style.display = 'none';
          }
        });

        const totalBadge = document.getElementById('totalDisplayedBadge');
        if (totalBadge) {
          totalBadge.textContent = `Total Displayed: ${visibleCount}`;
        }
      });
    }
  });
</script>
@endpush
