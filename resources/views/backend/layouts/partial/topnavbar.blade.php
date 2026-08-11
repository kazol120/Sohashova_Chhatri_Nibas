@php
    $user = auth()->user();

    if ($user && $user->status == 2 && !empty($user->user_image)) {
        $avatarUrl = asset('staff_images/' . $user->user_image);
    } else {
        $avatarUrl = $user
            ? $user->avatar_url
            : asset('image/user.png');
    }
@endphp
<style>
  .layout-navbar .navbar-dropdown.dropdown-user .dropdown-menu {
    right: 0 !important;
    left: auto !important;
    transform: none !important;
  }
</style>

<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
  <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
    <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
      <i class="ti ti-menu-2 ti-md"></i>
    </a>
  </div>

  <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
    <ul class="navbar-nav flex-row align-items-center ms-auto">
      
      @role('admin|staffs')
      <!-- Complaints Notification Bell -->
      @php
          $pendingComplaintsCount = \App\Models\Backend\Complaint::where('status', 0)->count();
          $latestComplaints = \App\Models\Backend\Complaint::with(['user', 'booking'])->where('status', 0)->latest()->take(5)->get();
      @endphp
      <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-2 me-xl-1">
        <a class="nav-link dropdown-toggle hide-arrow btn btn-text-secondary btn-icon rounded-pill position-relative d-inline-flex align-items-center justify-content-center"
           href="javascript:void(0);"
           data-bs-toggle="dropdown"
           aria-expanded="false"
           title="Resident Complaints"
           style="position: relative; width: 40px; height: 40px; padding: 0;">
          <i class="ti ti-bell ti-md text-danger"></i>
          <span id="complaintBellBadge" class="badge bg-danger rounded-pill position-absolute" style="top: 2px; right: 2px; font-size: 0.65rem; padding: 2px 5px; line-height: 1; border: 1.5px solid #fff; {{ $pendingComplaintsCount > 0 ? '' : 'display: none;' }}">
              {{ $pendingComplaintsCount }}
          </span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end py-0 shadow-lg" style="width: 320px; right: 0 !important; left: auto !important;">
          <li class="dropdown-menu-header border-bottom py-3 px-3 bg-label-danger rounded-top">
            <div class="d-flex align-items-center justify-content-between">
              <h6 class="mb-0 text-danger fw-bold"><i class="ti ti-alert-triangle me-2"></i>Complaints (অভিযোগ)</h6>
              <span id="complaintHeaderBadge" class="badge bg-danger rounded-pill">{{ $pendingComplaintsCount }} New</span>
            </div>
          </li>
          <li class="dropdown-notifications-list scrollable-container" style="max-height: 280px; overflow-y: auto;">
            <ul class="list-group list-group-flush" id="complaintListGroup">
              @forelse($latestComplaints as $c)
              <li class="list-group-item list-group-item-action dropdown-notifications-item p-3">
                <a href="{{ route('complaints.index') }}" class="text-decoration-none text-dark d-block">
                  <div class="d-flex align-items-start">
                    <div class="flex-shrink-0 me-3">
                      <div class="avatar avatar-sm">
                        <span class="avatar-initial rounded-circle bg-label-danger fw-bold">
                          {{ strtoupper(substr($c->user->name ?? 'R', 0, 1)) }}
                        </span>
                      </div>
                    </div>
                    <div class="flex-grow-1">
                      <h6 class="mb-1 fw-bold fs-7">{{ $c->user->name ?? 'Resident' }}</h6>
                      <p class="mb-1 text-muted fs-8 text-truncate" style="max-width: 200px;">{{ $c->complaint_text }}</p>
                      <small class="text-muted fs-9"><i class="ti ti-clock me-1"></i>{{ $c->created_at->diffForHumans() }}</small>
                    </div>
                  </div>
                </a>
              </li>
              @empty
              <li class="list-group-item text-center py-4 text-muted fs-7">
                No new pending complaints.
              </li>
              @endforelse
            </ul>
          </li>
          <li class="dropdown-menu-footer border-top p-2 text-center bg-light rounded-bottom">
            <a href="{{ route('complaints.index') }}" class="btn btn-primary btn-sm w-100 fw-bold">
              View All Complaints (সকল অভিযোগ দেখুন)
            </a>
          </li>
        </ul>
      </li>
      @endrole

      @role('admin|staffs')
      <!-- Meal Notification Bell -->
      @php
          $pendingMealRequestsCount = \App\Models\Backend\MealRequest::where('status', 0)->count();
          $latestMealRequests = \App\Models\Backend\MealRequest::with('user')->where('status', 0)->latest()->take(5)->get();
          $mealTypeLabels = [
              'full'       => 'Full Meal',
              'half_day'   => 'Day Half (দুপুর চালু / রাত বন্ধ)',
              'half_night' => 'Night Half (রাত চালু / দুপুর বন্ধ)',
              'off'        => 'Meal OFF (মিল বন্ধ)',
          ];
      @endphp
      <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-2 me-xl-1">
        <a class="nav-link dropdown-toggle hide-arrow btn btn-text-secondary btn-icon rounded-pill position-relative d-inline-flex align-items-center justify-content-center"
           href="javascript:void(0);"
           data-bs-toggle="dropdown"
           aria-expanded="false"
           title="Meal Change Requests"
           style="position: relative; width: 40px; height: 40px; padding: 0;">
          <i class="ti ti-cup ti-md text-warning"></i>
          <span id="mealBellBadge" class="badge bg-warning text-dark rounded-pill position-absolute" style="top: 2px; right: 2px; font-size: 0.65rem; padding: 2px 5px; line-height: 1; border: 1.5px solid #fff; {{ $pendingMealRequestsCount > 0 ? '' : 'display: none;' }}">
              {{ $pendingMealRequestsCount }}
          </span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end py-0 shadow-lg" style="width: 320px; right: 0 !important; left: auto !important;">
          <li class="dropdown-menu-header border-bottom py-3 px-3 bg-label-warning rounded-top">
            <div class="d-flex align-items-center justify-content-between">
              <h6 class="mb-0 text-warning fw-bold"><i class="ti ti-cup me-2"></i>Meal Requests (মিল অনুরোধ)</h6>
              <span id="mealHeaderBadge" class="badge bg-warning text-dark rounded-pill">{{ $pendingMealRequestsCount }} New</span>
            </div>
          </li>
          <li class="dropdown-notifications-list scrollable-container" style="max-height: 280px; overflow-y: auto;">
            <ul class="list-group list-group-flush" id="mealListGroup">
              @forelse($latestMealRequests as $mr)
              <li class="list-group-item list-group-item-action dropdown-notifications-item p-3">
                <a href="{{ route('meal-requests.index') }}" class="text-decoration-none text-dark d-block">
                  <div class="d-flex align-items-start">
                    <div class="flex-shrink-0 me-3">
                      <div class="avatar avatar-sm">
                        <span class="avatar-initial rounded-circle bg-label-warning text-warning fw-bold">
                          {{ strtoupper(substr($mr->user->name ?? 'R', 0, 1)) }}
                        </span>
                      </div>
                    </div>
                    <div class="flex-grow-1">
                      <h6 class="mb-1 fw-bold fs-7">{{ $mr->user->name ?? 'Resident' }}</h6>
                      <p class="mb-1 text-dark fs-8 text-truncate" style="max-width: 200px;">
                        Request: <strong class="text-warning">{{ $mealTypeLabels[$mr->request_type] ?? $mr->request_type }}</strong> ({{ $mr->date }})
                      </p>
                      <small class="text-muted fs-9"><i class="ti ti-clock me-1"></i>{{ $mr->created_at->diffForHumans() }}</small>
                    </div>
                  </div>
                </a>
              </li>
              @empty
              <li class="list-group-item text-center py-4 text-muted fs-7">
                No new pending meal requests.
              </li>
              @endforelse
            </ul>
          </li>
          <li class="dropdown-menu-footer border-top p-2 text-center bg-light rounded-bottom">
            <a href="{{ route('meal-requests.index') }}" class="btn btn-warning btn-sm w-100 fw-bold text-dark">
              View All Meal Requests (সকল অনুরোধ দেখুন)
            </a>
          </li>
        </ul>
      </li>
      @endrole

      @role('admin|staffs')
      <!-- Room Booking Notification Bell -->
      @php
          $pendingBookingsCount = \App\Models\Backend\RoomBookingHistory::where('status', 0)->where('is_seen', 0)->count();
          $latestBookings = \App\Models\Backend\RoomBookingHistory::where('status', 0)->where('is_seen', 0)->latest('id')->take(10)->get();
      @endphp
      <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-2 me-xl-1">
        <a class="nav-link dropdown-toggle hide-arrow btn btn-text-secondary btn-icon rounded-pill position-relative d-inline-flex align-items-center justify-content-center"
           href="javascript:void(0);"
           data-bs-toggle="dropdown"
           aria-expanded="false"
           title="New Room Bookings"
           style="position: relative; width: 40px; height: 40px; padding: 0;">
          <i class="ti ti-bed ti-md text-info"></i>
          <span id="bookingBellBadge" class="badge bg-info text-white rounded-pill position-absolute" style="top: 2px; right: 2px; font-size: 0.65rem; padding: 2px 5px; line-height: 1; border: 1.5px solid #fff; {{ $pendingBookingsCount > 0 ? '' : 'display: none;' }}">
              {{ $pendingBookingsCount }}
          </span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end py-0 shadow-lg" style="width: 330px; right: 0 !important; left: auto !important;">
          <li class="dropdown-menu-header border-bottom py-3 px-3 bg-label-info rounded-top">
            <div class="d-flex align-items-center justify-content-between">
              <h6 class="mb-0 text-info fw-bold"><i class="ti ti-bed me-2"></i>Room Bookings (নতুন বুকিং)</h6>
              <span id="bookingHeaderBadge" class="badge bg-info text-white rounded-pill">{{ $pendingBookingsCount }} New</span>
            </div>
          </li>
          <li class="dropdown-notifications-list scrollable-container" style="max-height: 320px; overflow-y: auto;">
            <ul class="list-group list-group-flush" id="bookingListGroup">
              @forelse($latestBookings as $b)
              <li class="list-group-item list-group-item-action dropdown-notifications-item p-3">
                <a href="{{ route('bookings.mark-seen', $b->id) }}" class="text-decoration-none text-dark d-block">
                  <div class="d-flex align-items-start">
                    <div class="flex-shrink-0 me-3">
                      <div class="avatar avatar-sm">
                        <span class="avatar-initial rounded-circle bg-label-info text-info fw-bold">
                          {{ strtoupper(substr($b->full_name ?? 'G', 0, 1)) }}
                        </span>
                      </div>
                    </div>
                    <div class="flex-grow-1">
                      <h6 class="mb-1 fw-bold fs-7">{{ $b->full_name ?? 'Guest' }}</h6>
                      <p class="mb-1 text-dark fs-8 text-truncate" style="max-width: 210px;">
                        Phone: <strong>{{ $b->phone ?? 'N/A' }}</strong>
                      </p>
                      <small class="text-muted fs-9"><i class="ti ti-clock me-1"></i>{{ $b->created_at ? $b->created_at->diffForHumans() : 'Recently' }}</small>
                    </div>
                  </div>
                </a>
              </li>
              @empty
              <li class="list-group-item text-center py-4 text-muted fs-7">
                No new pending room bookings.
              </li>
              @endforelse
            </ul>
          </li>
          <li class="dropdown-menu-footer border-top p-2 text-center bg-light rounded-bottom">
            <a href="{{ url('/room-booking-history') }}" class="btn btn-info btn-sm w-100 fw-bold text-white">
              View All Bookings (সকল বুকিং দেখুন)
            </a>
          </li>

        </ul>
      </li>

      @endrole

      <!-- User Dropdown -->


      <li class="nav-item navbar-dropdown dropdown-user dropdown position-relative">
        <a class="nav-link dropdown-toggle hide-arrow p-0"
           href="javascript:void(0);"
           data-bs-toggle="dropdown"
           aria-expanded="false">
          <div class="avatar avatar-online">
            <img src="{{ $avatarUrl }}"
             alt="User Avatar"
             class="rounded-circle"
             style="width: 40px; height: 40px; object-fit: cover;" />
          </div>
        </a>
        <ul class="dropdown-menu dropdown-menu-end" style="right: 0 !important; left: auto !important; margin-top: 8px;">
          <li>
            <a class="dropdown-item mt-0" href="{{ route('profile') }}">
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-2">
                  <div class="avatar avatar-online">
                    <img src="{{ $avatarUrl }}"
                     alt="User Avatar"
                     class="rounded-circle"
                     style="width: 40px; height: 40px; object-fit: cover;" />
                  </div>
                </div>
                <div class="flex-grow-1">
                  <h6 class="mb-0 fw-bold">{{ $user ? $user->name : 'User' }}</h6>
                  <small class="text-muted">{{ $user && $user->roles ? $user->roles->pluck('name')->implode(', ') : '' }}</small>
                </div>
              </div>
            </a>
          </li>
          <li>
            <div class="dropdown-divider my-1 mx-n2"></div>
          </li>
          <li>
            <a class="dropdown-item" href="{{ route('profile') }}">
              <i class="ti ti-user me-3 ti-md"></i><span class="align-middle">My Profile</span>
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="{{ route('change-password') }}">
              <i class="ti ti-lock me-3 ti-md"></i><span class="align-middle">Change Password</span>
            </a>
          </li>
          <li>
            <div class="dropdown-divider my-1 mx-n2"></div>
          </li>
          <li>
            <div class="d-grid px-2 pt-2 pb-1">
              <a class="btn btn-sm btn-danger d-flex align-items-center justify-content-center"
                 href="{{ route('logout') }}"
                 onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="ti ti-logout me-2"></i><span class="align-middle">Log Out</span>
              </a>
              <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
              </form>
            </div>
          </li>
        </ul>
      </li>
      <!--/ User Dropdown -->
    </ul>
  </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dropdownToggles = document.querySelectorAll('#layout-navbar [data-bs-toggle="dropdown"]');
    dropdownToggles.forEach(function (toggle) {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const parent = this.closest('.dropdown');
            const menu = parent ? parent.querySelector('.dropdown-menu') : null;
            
            // Close other open dropdowns first
            document.querySelectorAll('#layout-navbar .dropdown-menu.show').forEach(function (m) {
                if (m !== menu) m.classList.remove('show');
            });
            document.querySelectorAll('#layout-navbar [data-bs-toggle="dropdown"].show').forEach(function (t) {
                if (t !== toggle) t.classList.remove('show');
            });

            if (menu) {
                menu.classList.toggle('show');
                this.classList.toggle('show');
            }
        });
    });
    document.addEventListener('click', function (e) {
        if (!e.target.closest('#layout-navbar .dropdown')) {
            document.querySelectorAll('#layout-navbar .dropdown-menu.show').forEach(function (m) {
                m.classList.remove('show');
            });
            document.querySelectorAll('#layout-navbar [data-bs-toggle="dropdown"].show').forEach(function (t) {
                t.classList.remove('show');
            });
        }
    });
});
</script>

@if(auth()->check())
<script>
document.addEventListener('DOMContentLoaded', function () {
    var lastPendingCount = {{ $pendingComplaintsCount ?? 0 }};

    function playChimeSound() {
        try {
            var ctx = new (window.AudioContext || window.webkitAudioContext)();
            var osc1 = ctx.createOscillator();
            var osc2 = ctx.createOscillator();
            var gain = ctx.createGain();

            osc1.type = 'sine';
            osc2.type = 'sine';

            osc1.frequency.setValueAtTime(587.33, ctx.currentTime);
            osc1.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.15);

            osc2.frequency.setValueAtTime(880, ctx.currentTime + 0.15);
            osc2.frequency.exponentialRampToValueAtTime(1174.66, ctx.currentTime + 0.35);

            gain.gain.setValueAtTime(0.35, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.6);

            osc1.connect(gain);
            osc2.connect(gain);
            gain.connect(ctx.destination);

            osc1.start(ctx.currentTime);
            osc1.stop(ctx.currentTime + 0.2);
            osc2.start(ctx.currentTime + 0.15);
            osc2.stop(ctx.currentTime + 0.6);
        } catch (e) {}
    }

    function checkLiveComplaints() {
        fetch("{{ route('complaints.check-pending') }}", {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                var bellBadge = document.getElementById('complaintBellBadge');
                var headerBadge = document.getElementById('complaintHeaderBadge');
                var listGroup = document.getElementById('complaintListGroup');

                if (data.count > 0) {
                    if (bellBadge) {
                        bellBadge.innerText = data.count;
                        bellBadge.style.display = 'inline-block';
                    }
                    if (headerBadge) {
                        headerBadge.innerText = data.count + ' New';
                    }
                } else {
                    if (bellBadge) bellBadge.style.display = 'none';
                    if (headerBadge) headerBadge.innerText = '0 New';
                }

                if (data.count > lastPendingCount) {
                    playChimeSound();
                }
                lastPendingCount = data.count;

                if (listGroup && data.complaints) {
                    if (data.complaints.length === 0) {
                        listGroup.innerHTML = '<li class="list-group-item text-center py-4 text-muted fs-7">No new pending complaints.</li>';
                    } else {
                        var html = '';
                        data.complaints.forEach(function(c) {
                            html += '<li class="list-group-item list-group-item-action dropdown-notifications-item p-3">' +
                                '<a href="{{ route("complaints.index") }}" class="text-decoration-none text-dark d-block">' +
                                '<div class="d-flex align-items-start">' +
                                '<div class="flex-shrink-0 me-3">' +
                                '<div class="avatar avatar-sm">' +
                                '<span class="avatar-initial rounded-circle bg-label-danger fw-bold">' + c.initial + '</span>' +
                                '</div></div>' +
                                '<div class="flex-grow-1">' +
                                '<h6 class="mb-1 fw-bold fs-7">' + c.user_name + '</h6>' +
                                '<p class="mb-1 text-muted fs-8 text-truncate" style="max-width: 200px;">' + c.complaint_text + '</p>' +
                                '<small class="text-muted fs-9"><i class="ti ti-clock me-1"></i>' + c.time_ago + '</small>' +
                                '</div></div></a></li>';
                        });
                        listGroup.innerHTML = html;
                    }
                }
            })
            .catch(function(err) { console.log('Live polling error:', err); });
    }

    setInterval(checkLiveComplaints, 7000);

    // Live Polling & Distinct Sound for Meal Requests
    var lastMealPendingCount = {{ \App\Models\Backend\MealRequest::where('status', 0)->count() }};
    window.sharedAudioCtx = null;

    function playMealChimeSound() {
        try {
            if (!window.sharedAudioCtx) {
                window.sharedAudioCtx = new (window.AudioContext || window.webkitAudioContext)();
            }
            if (window.sharedAudioCtx.state === 'suspended') {
                window.sharedAudioCtx.resume();
            }
            var now = window.sharedAudioCtx.currentTime;

            var osc1 = window.sharedAudioCtx.createOscillator();
            var gain1 = window.sharedAudioCtx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(880, now);
            gain1.gain.setValueAtTime(0.5, now);
            gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
            osc1.connect(gain1);
            gain1.connect(window.sharedAudioCtx.destination);
            osc1.start(now);
            osc1.stop(now + 0.35);

            var osc2 = window.sharedAudioCtx.createOscillator();
            var gain2 = window.sharedAudioCtx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(1174.66, now + 0.15);
            gain2.gain.setValueAtTime(0.6, now + 0.15);
            gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.6);
            osc2.connect(gain2);
            gain2.connect(window.sharedAudioCtx.destination);
            osc2.start(now + 0.15);
            osc2.stop(now + 0.6);
        } catch(e) { console.log('Audio error:', e); }
    }

    document.addEventListener('click', function() {
        if (window.sharedAudioCtx && window.sharedAudioCtx.state === 'suspended') {
            window.sharedAudioCtx.resume();
        }
    });

    function checkLiveMealRequests() {
        fetch("{{ route('meal-requests.check-pending') }}", {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                var bellBadge = document.getElementById('mealBellBadge');
                var headerBadge = document.getElementById('mealHeaderBadge');
                var listGroup = document.getElementById('mealListGroup');

                if (data.count > 0) {
                    if (bellBadge) {
                        bellBadge.innerText = data.count;
                        bellBadge.style.display = 'inline-block';
                    }
                    if (headerBadge) {
                        headerBadge.innerText = data.count + ' New';
                    }
                } else {
                    if (bellBadge) bellBadge.style.display = 'none';
                    if (headerBadge) headerBadge.innerText = '0 New';
                }

                if (data.count > lastMealPendingCount) {
                    playMealChimeSound();
                }
                lastMealPendingCount = data.count;

                if (listGroup && data.requests) {
                    if (data.requests.length === 0) {
                        listGroup.innerHTML = '<li class="list-group-item text-center py-4 text-muted fs-7">No new pending meal requests.</li>';
                    } else {
                        var html = '';
                        data.requests.forEach(function(mr) {
                            html += '<li class="list-group-item list-group-item-action dropdown-notifications-item p-3">' +
                                '<a href="{{ route("meal-requests.index") }}" class="text-decoration-none text-dark d-block">' +
                                '<div class="d-flex align-items-start">' +
                                '<div class="flex-shrink-0 me-3">' +
                                '<div class="avatar avatar-sm">' +
                                '<span class="avatar-initial rounded-circle bg-label-warning text-warning fw-bold">' + mr.initial + '</span>' +
                                '</div></div>' +
                                '<div class="flex-grow-1">' +
                                '<h6 class="mb-1 fw-bold fs-7">' + mr.user_name + '</h6>' +
                                '<p class="mb-1 text-dark fs-8 text-truncate" style="max-width: 200px;">Request: <strong class="text-warning">' + mr.request_type + '</strong> (' + mr.date + ')</p>' +
                                '<small class="text-muted fs-9"><i class="ti ti-clock me-1"></i>' + mr.time_ago + '</small>' +
                                '</div></div></a></li>';
                        });
                        listGroup.innerHTML = html;
                    }
                }
            })
            .catch(function(err) { console.log('Meal live polling error:', err); });
    }

    checkLiveMealRequests();
    setInterval(checkLiveMealRequests, 5000);

    // Live Polling & Sound Toast Notification for New Room Bookings
    function playBookingChimeSound() {
        try {
            if (!window.sharedAudioCtx) {
                window.sharedAudioCtx = new (window.AudioContext || window.webkitAudioContext)();
            }
            if (window.sharedAudioCtx.state === 'suspended') {
                window.sharedAudioCtx.resume();
            }
            var now = window.sharedAudioCtx.currentTime;

            var notes = [523.25, 659.25, 783.99, 1046.50];
            notes.forEach(function(freq, index) {
                var osc = window.sharedAudioCtx.createOscillator();
                var gain = window.sharedAudioCtx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(freq, now + (index * 0.12));
                gain.gain.setValueAtTime(0.5, now + (index * 0.12));
                gain.gain.exponentialRampToValueAtTime(0.001, now + (index * 0.12) + 0.35);
                osc.connect(gain);
                gain.connect(window.sharedAudioCtx.destination);
                osc.start(now + (index * 0.12));
                osc.stop(now + (index * 0.12) + 0.35);
            });
        } catch(e) { console.log('Booking chime audio error:', e); }
    }

    function checkLiveBookings() {
        var rawStored = localStorage.getItem('admin_last_seen_booking_id');
        var lastSeenId = rawStored ? parseInt(rawStored, 10) : 0;
        var queryUrl = "{{ route('bookings.check-pending') }}?last_id=" + lastSeenId;

        fetch(queryUrl, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                var bellBadge = document.getElementById('bookingBellBadge');
                var headerBadge = document.getElementById('bookingHeaderBadge');
                var listGroup = document.getElementById('bookingListGroup');

                // 1. Update Notification Bell Badge & Header Counter
                if (data.count > 0) {
                    if (bellBadge) {
                        bellBadge.innerText = data.count;
                        bellBadge.style.display = 'inline-block';
                    }
                    if (headerBadge) {
                        headerBadge.innerText = data.count + ' New';
                    }
                } else {
                    if (bellBadge) bellBadge.style.display = 'none';
                    if (headerBadge) headerBadge.innerText = '0 New';
                }

                // 2. Render List Group HTML dynamically with up to 10 latest bookings
                if (listGroup && data.bookings) {
                    if (data.bookings.length === 0) {
                        listGroup.innerHTML = '<li class="list-group-item text-center py-4 text-muted fs-7">No new pending room bookings.</li>';
                    } else {
                        var html = '';
                        data.bookings.forEach(function(b) {
                            html += '<li class="list-group-item list-group-item-action dropdown-notifications-item p-3">' +
                                '<a href="' + b.view_url + '" class="text-decoration-none text-dark d-block">' +
                                '<div class="d-flex align-items-start">' +
                                '<div class="flex-shrink-0 me-3">' +
                                '<div class="avatar avatar-sm">' +
                                '<span class="avatar-initial rounded-circle bg-label-info text-info fw-bold">' + b.initial + '</span>' +
                                '</div></div>' +
                                '<div class="flex-grow-1">' +
                                '<h6 class="mb-1 fw-bold fs-7">' + b.full_name + '</h6>' +
                                '<p class="mb-1 text-dark fs-8 text-truncate" style="max-width: 210px;">Phone: <strong>' + b.phone + '</strong></p>' +
                                '<small class="text-muted fs-9"><i class="ti ti-clock me-1"></i>' + b.time_ago + '</small>' +
                                '</div></div></a></li>';
                        });
                        listGroup.innerHTML = html;
                    }
                }

                // 3. Play Chime Sound Alert for New Unseen Room Bookings (No Pop-up Modal)
                if (data.latest_id) {
                    if (rawStored === null) {
                        localStorage.setItem('admin_last_seen_booking_id', data.latest_id);
                    } else if (data.latest_id > lastSeenId && data.bookings && data.bookings.length > 0) {
                        localStorage.setItem('admin_last_seen_booking_id', data.latest_id);
                        playBookingChimeSound();
                    }
                }
            })
            .catch(function(err) { console.log('Booking live polling error:', err); });

    }

    checkLiveBookings();
    setInterval(checkLiveBookings, 5000);
});


</script>
@endif

