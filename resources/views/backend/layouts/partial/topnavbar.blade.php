@php
    $user = auth()->user();
    $avatarUrl = $user ? $user->avatar_url : asset('image/user.png');
@endphp

<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
  <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
    <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
      <i class="ti ti-menu-2 ti-md"></i>
    </a>
  </div>

  <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
    <ul class="navbar-nav flex-row align-items-center ms-auto">
      
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
      <li class="nav-item navbar-dropdown dropdown-user dropdown">
        <a class="nav-link dropdown-toggle hide-arrow p-0"
           href="javascript:void(0);"
           data-bs-toggle="dropdown"
           aria-expanded="false">
          <div class="avatar avatar-online">
            <img src="{{ $avatarUrl }}" alt="User Avatar" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;" />
          </div>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li>
            <a class="dropdown-item mt-0" href="{{ route('profile') }}">
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-2">
                  <div class="avatar avatar-online">
                    <img src="{{ $avatarUrl }}" alt="User Avatar" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;" />
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
