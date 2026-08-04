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
        <a class="nav-link dropdown-toggle hide-arrow btn btn-text-secondary btn-icon rounded-pill position-relative"
           href="javascript:void(0);"
           data-bs-toggle="dropdown"
           aria-expanded="false"
           title="Resident Complaints">
          <i class="ti ti-bell ti-md text-danger"></i>
          @if($pendingComplaintsCount > 0)
            <span class="badge bg-danger rounded-pill badge-notifications position-absolute top-0 start-100 translate-middle p-1 border border-light" style="font-size: 0.65rem;">
                {{ $pendingComplaintsCount }}
            </span>
          @endif
        </a>
        <ul class="dropdown-menu dropdown-menu-end py-0 shadow-lg" style="width: 320px; right: 0 !important; left: auto !important;">
          <li class="dropdown-menu-header border-bottom py-3 px-3 bg-label-danger rounded-top">
            <div class="d-flex align-items-center justify-content-between">
              <h6 class="mb-0 text-danger fw-bold"><i class="ti ti-alert-triangle me-2"></i>Complaints (অভিযোগ)</h6>
              <span class="badge bg-danger rounded-pill">{{ $pendingComplaintsCount }} New</span>
            </div>
          </li>
          <li class="dropdown-notifications-list scrollable-container" style="max-height: 280px; overflow-y: auto;">
            <ul class="list-group list-group-flush">
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
      
      <!-- Style Switcher -->
      <li class="nav-item dropdown-style-switcher dropdown me-2 me-xl-0">
        <a class="nav-link btn btn-text-secondary btn-icon rounded-pill dropdown-toggle hide-arrow"
           href="javascript:void(0);"
           data-bs-toggle="dropdown"
           aria-expanded="false">
          <i class="ti ti-md ti-sun"></i>
        </a>
        <ul class="dropdown-menu dropdown-menu-end dropdown-styles">
          <li>
            <a class="dropdown-item" href="javascript:void(0);" data-theme="light">
              <span class="align-middle"><i class="ti ti-sun ti-md me-3"></i>Light</span>
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="javascript:void(0);" data-theme="dark">
              <span class="align-middle"><i class="ti ti-moon-stars ti-md me-3"></i>Dark</span>
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="javascript:void(0);" data-theme="system">
              <span class="align-middle"><i class="ti ti-device-desktop-analytics ti-md me-3"></i>System</span>
            </a>
          </li>
        </ul>
      </li>
      <!-- / Style Switcher -->

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
            <div class="d-grid px-2 pt-2 pb-1">
              <a class="btn btn-sm btn-danger d-flex align-items-center justify-content-center"
                 href="{{ route('logout') }}"
                 onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <span class="align-middle me-2">Logout</span>
                <i class="ti ti-logout ti-14px"></i>
              </a>
              <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
              </form>
            </div>
          </li>
        </ul>
      </li>
      <!-- / User Dropdown -->
    </ul>
  </div>
  <!-- Search Small Screens -->
  <div class="navbar-search-wrapper search-input-wrapper d-none">
    <input type="text" class="form-control search-input container-xxl border-0" placeholder="Search..." aria-label="Search..." />
    <i class="ti ti-x search-toggler cursor-pointer"></i>
  </div>
</nav>

<!-- Robust Dropdown Toggle Script Fallback -->
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
