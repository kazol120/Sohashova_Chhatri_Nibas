<style>
/* =========================================================
   PROFESSIONAL SIDEBAR MENU STYLING
   ========================================================= */
.bg-menu-theme {
    background-color: #ffffff !important;
}

/* Section Header Labels */
.bg-menu-theme .menu-header .menu-header-text {
    color: #475569 !important;
    font-weight: 800 !important;
    font-size: 0.75rem !important;
    letter-spacing: 1px !important;
    text-transform: uppercase !important;
}

/* Top-level menu link (normal state) */
.bg-menu-theme .menu-inner > .menu-item > .menu-link {
    color: #1e293b !important;
    font-weight: 600 !important;
    font-size: 0.93rem !important;
    border-radius: 0.5rem !important;
    margin: 2px 0.75rem !important;
    padding: 0.65rem 1rem !important;
    transition: all 0.2s ease-in-out !important;
}

/* Top-level menu link hover state */
.bg-menu-theme .menu-inner > .menu-item:not(.active) > .menu-link:hover {
    background-color: #f1f5f9 !important;
    color: #4f46e5 !important;
}

.bg-menu-theme .menu-inner > .menu-item:not(.active) > .menu-link:hover .menu-icon {
    color: #4f46e5 !important;
}

/* Top-level active parent menu item (e.g. Room Management open) */
.bg-menu-theme .menu-inner > .menu-item.active > .menu-link,
.bg-menu-theme .menu-inner > .menu-item.open > .menu-link.menu-toggle {
    background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%) !important;
    color: #ffffff !important;
    font-weight: 700 !important;
    border-radius: 0.5rem !important;
    box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35) !important;
}

.bg-menu-theme .menu-inner > .menu-item.active > .menu-link .menu-icon,
.bg-menu-theme .menu-inner > .menu-item.active > .menu-link i,
.bg-menu-theme .menu-inner > .menu-item.active > .menu-link div,
.bg-menu-theme .menu-inner > .menu-item.open > .menu-link.menu-toggle .menu-icon,
.bg-menu-theme .menu-inner > .menu-item.open > .menu-link.menu-toggle i,
.bg-menu-theme .menu-inner > .menu-item.open > .menu-link.menu-toggle div {
    color: #ffffff !important;
}

/* Submenu container */
.bg-menu-theme .menu-sub {
    padding-left: 0.5rem !important;
    margin-top: 0.25rem !important;
    margin-bottom: 0.25rem !important;
}

/* Submenu normal link */
.bg-menu-theme .menu-sub .menu-item .menu-link {
    color: #334155 !important;
    font-weight: 600 !important;
    font-size: 0.9rem !important;
    padding: 0.55rem 1rem 0.55rem 2.2rem !important;
    margin: 2px 0.75rem !important;
    border-radius: 0.4rem !important;
    transition: all 0.2s ease-in-out !important;
}

/* Submenu hover state */
.bg-menu-theme .menu-sub .menu-item:not(.active) .menu-link:hover {
    background-color: #e0e7ff !important;
    color: #3730a3 !important;
    font-weight: 700 !important;
}

/* Submenu ACTIVE state (Solves the white text on light background issue!) */
.bg-menu-theme .menu-sub .menu-item.active > .menu-link {
    background: linear-gradient(135deg, #059669 0%, #10b981 100%) !important;
    color: #ffffff !important;
    font-weight: 700 !important;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35) !important;
}

.bg-menu-theme .menu-sub .menu-item.active > .menu-link div,
.bg-menu-theme .menu-sub .menu-item.active > .menu-link span,
.bg-menu-theme .menu-sub .menu-item.active > .menu-link i {
    color: #ffffff !important;
}

/* Bullet icon for submenu */
.bg-menu-theme .menu-sub .menu-item .menu-link::before {
    background-color: #94a3b8 !important;
}
.bg-menu-theme .menu-sub .menu-item.active .menu-link::before {
    background-color: #ffffff !important;
    box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.5) !important;
}
</style>

          <ul class="menu-inner py-2">
            <!-- SECTION 1: DASHBOARD -->
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text fw-bold text-primary" style="letter-spacing: 0.8px; font-size: 0.72rem;">DASHBOARD</span>
            </li>
            <li class="menu-item dashboard-menu {{ Route::currentRouteNamed('dashboard') ? 'active' : '' }}">
                <a href="{{route('dashboard')}}" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-smart-home"></i>
                    <div data-i18n="Dashboard" class="fs-5">Dashboard</div>
                </a>
            </li>

            <!-- Resident My Payments & Meals -->
            @unlessrole('admin|super-admin|staffs|manager')
            <li class="menu-item {{ Route::currentRouteNamed('dashboard.my-payments') ? 'active' : '' }}">
                <a href="{{ route('dashboard.my-payments') }}" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-wallet"></i>
                    <div data-i18n="My Payments" class="fs-5">My Payments</div>
                </a>
            </li>
            <li class="menu-item {{ Route::currentRouteNamed('dashboard.my-electricity-bills') ? 'active' : '' }}">
                <a href="{{ route('dashboard.my-electricity-bills') }}" class="menu-link text-warning fw-semibold">
                    <i class="menu-icon tf-icons ti ti-bolt text-warning"></i>
                    <div data-i18n="Electricity Bills" class="fs-5 text-warning">Electricity Bills</div>
                </a>
            </li>
            <li class="menu-item {{ Route::currentRouteNamed('dashboard.my-meals') ? 'active' : '' }}">
                <a href="{{ route('dashboard.my-meals') }}" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-salad"></i>
                    <div data-i18n="My Meal History" class="fs-5">My Meal History</div>
                </a>
            </li>
            <li class="menu-item {{ Route::currentRouteNamed('guest-meals.*') ? 'active' : '' }}">
                <a href="{{ route('guest-meals.index') }}" class="menu-link text-primary fw-semibold">
                    <i class="menu-icon tf-icons ti ti-users text-primary"></i>
                    <div data-i18n="Guest Meals" class="fs-5">Guest Meals</div>
                </a>
            </li>
            <li class="menu-item {{ Route::currentRouteNamed('meals.create') ? 'active' : '' }}">
                <a href="{{ route('meals.create') }}" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-calendar-event"></i>
                    <div data-i18n="Daily Meal Setting" class="fs-5">Daily Meal Setting</div>
                </a>
            </li>
            <li class="menu-item {{ Route::currentRouteNamed('complaints.index') ? 'active' : '' }}">
                <a href="{{ route('complaints.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-alert-triangle text-danger"></i>
                    <div data-i18n="My Complaints" class="fs-5 text-danger fw-semibold">My Complaints</div>
                </a>
            </li>
            <li class="menu-item {{ Route::currentRouteNamed('check-in-out.index') ? 'active' : '' }}">
                <a href="{{ route('check-in-out.index') }}" class="menu-link text-warning fw-semibold">
                    <i class="menu-icon tf-icons ti ti-door-exit text-warning"></i>
                    <div data-i18n="Check In / Out" class="fs-5 text-warning">Check In / Out</div>
                </a>
            </li>
            @endunlessrole


            <!-- SECTION 2: CORE MANAGEMENT -->
            @role('admin|super-admin|staffs|manager')
            <li class="menu-header small text-uppercase mt-3">
                <span class="menu-header-text fw-bold text-primary" style="letter-spacing: 0.8px; font-size: 0.72rem;">CORE MANAGEMENT</span>
            </li>

            {{-- Floor --}}
            <li class="menu-item {{ Route::currentRouteNamed('floor.index') ? 'active open' : '' }}">
              <a href="javascript:void(0)" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-building-community"></i>
                <div data-i18n="Floor" class="fs-5">Floor</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item {{ Route::currentRouteNamed('floor.index') ? 'active' : '' }}">
                  <a href="{{ route('floor.index') }}" class="menu-link">
                    <div class="fs-5">Floor List</div>
                  </a>
                </li>
              </ul>
            </li>

            {{-- Room --}}
            <li class="menu-item {{ Route::currentRouteNamed('room-list.index') || Request::is('room-booking-history') || Route::currentRouteNamed('room-release.index') || Route::currentRouteNamed('room-release.history') || Route::currentRouteNamed('room-deleted.history') || Route::currentRouteNamed('room-change.index') ? 'active open' : '' }}">
              <a href="javascript:void(0)" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-bed"></i>
                <div data-i18n="Room" class="fs-5">Room Management</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item {{ Route::currentRouteNamed('room-list.index') ? 'active' : '' }}">
                  <a href="{{ route('room-list.index') }}" class="menu-link">
                    <div class="fs-5">Room List</div>
                  </a>
                </li>
                <li class="menu-item {{ Request::is('room-booking-history') ? 'active' : '' }}">
                  <a href="{{ url('room-booking-history') }}" class="menu-link">
                    <div class="fs-5">Booking History</div>
                  </a>
                </li>
                <li class="menu-item {{ Route::currentRouteNamed('room-change.index') ? 'active' : '' }}">
                  <a href="{{ route('room-change.index') }}" class="menu-link">
                    <div class="fs-5">Change Room/Seat</div>
                  </a>
                </li>
                <li class="menu-item {{ Route::currentRouteNamed('room-release.index') ? 'active' : '' }}">
                  <a href="{{ route('room-release.index') }}" class="menu-link">
                    <div class="fs-5">Release Seat</div>
                  </a>
                </li>
                <li class="menu-item {{ Route::currentRouteNamed('room-release.history') ? 'active' : '' }}">
                  <a href="{{ route('room-release.history') }}" class="menu-link">
                    <div class="fs-5">Release History</div>
                  </a>
                </li>
                @role('admin|super-admin')
                <li class="menu-item {{ Route::currentRouteNamed('room-deleted.history') ? 'active' : '' }}">
                  <a href="{{ route('room-deleted.history') }}" class="menu-link">
                    <div class="fs-5">Deleted History</div>
                  </a>
                </li>
                @endrole
              </ul>
            </li>

            {{-- Resident Passwords & Email Credentials (Admin Only) --}}
            @role('admin|super-admin')
            <li class="menu-item {{ Route::currentRouteNamed('resident-credentials.index') ? 'active' : '' }}">
              <a href="{{ route('resident-credentials.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-key"></i>
                <div class="fs-5">Resident Passwords</div>
              </a>
            </li>
            @endrole

            {{-- Resident 360 Lifetime History --}}
            <li class="menu-item {{ Route::currentRouteNamed('resident-history.index') ? 'active' : '' }}">
              <a href="{{ route('resident-history.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-id-badge-2"></i>
                <div class="fs-5">Resident Full History</div>
              </a>
            </li>

            {{-- Check In / Check Out --}}
            <li class="menu-item {{ Route::currentRouteNamed('check-in-out.index') ? 'active' : '' }}">
              <a href="{{ route('check-in-out.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-door-exit text-warning"></i>
                <div class="fs-5 d-flex align-items-center justify-content-between w-100">
                    <span>Check In / Out</span>
                    @php $currentlyOutsideCount = \App\Models\Backend\ResidentCheckInOut::where('status', 0)->count(); @endphp
                    @if($currentlyOutsideCount > 0)
                        <span class="badge bg-warning text-dark rounded-pill px-2 py-0.5 ms-2" style="font-size: 0.75rem;">{{ $currentlyOutsideCount }}</span>
                    @endif
                </div>
              </a>
            </li>


            {{-- Booking Room Button Status (Admin Only) --}}
            @role('admin|super-admin')
            <li class="menu-item {{ Route::currentRouteNamed('booking-button.index') ? 'active' : '' }}">
              <a href="{{ route('booking-button.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-toggle-right text-primary"></i>
                <div class="fs-5">Booking Room Button Status</div>
              </a>
            </li>
            @endrole

            {{-- Monthly Payments & Electricity Bills --}}
            <li class="menu-item {{ Route::currentRouteNamed('monthly-payments.index') || Route::currentRouteNamed('electricity-bills.index') ? 'active open' : '' }}">
              <a href="javascript:void(0)" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-cash-banknote"></i>
                <div data-i18n="Monthly Payments" class="fs-5">Monthly Payments</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item {{ Route::currentRouteNamed('monthly-payments.index') ? 'active' : '' }}">
                  <a href="{{ route('monthly-payments.index') }}" class="menu-link">
                    <div class="fs-5">Payments Collection</div>
                  </a>
                </li>
                <li class="menu-item {{ Route::currentRouteNamed('electricity-bills.index') ? 'active' : '' }}">
                  <a href="{{ route('electricity-bills.index') }}" class="menu-link text-warning fw-semibold">
                    <div class="fs-5"><i class="ti ti-bolt me-1 text-warning"></i>Electricity Bills</div>
                  </a>
                </li>
              </ul>
            </li>
            @endrole

            <!-- SECTION 3: OPERATIONS -->
            @role('admin|super-admin|staffs|manager')
            <li class="menu-header small text-uppercase mt-3">
                <span class="menu-header-text fw-bold text-primary" style="letter-spacing: 0.8px; font-size: 0.72rem;">OPERATIONS</span>
            </li>

            {{-- Complaints Management --}}
            <li class="menu-item {{ Route::currentRouteNamed('complaints.index') ? 'active' : '' }}">
              <a href="{{ route('complaints.index') }}" class="menu-link text-danger">
                <i class="menu-icon tf-icons ti ti-alert-triangle text-danger"></i>
                <div class="fs-5 fw-semibold d-flex align-items-center justify-content-between w-100">
                    <span>Complaints List</span>
                    @php $pendingCount = \App\Models\Backend\Complaint::where('status', 0)->count(); @endphp
                    @if($pendingCount > 0)
                        <span class="badge bg-danger rounded-pill px-2 py-1 ms-2" style="font-size: 0.75rem;">{{ $pendingCount }}</span>
                    @endif
                </div>
              </a>
            </li>



            {{-- Meal Management --}}
            <li class="menu-item {{ Route::currentRouteNamed('meals.index') || Route::currentRouteNamed('meals.create') || Route::currentRouteNamed('meal-history') || Route::currentRouteNamed('deposits.index') || Route::currentRouteNamed('deposits.create') || Route::currentRouteNamed('deposit-refunds.*') || Route::currentRouteNamed('guest-meals.*') || (Route::currentRouteNamed('setting.index') && request()->slug === 'meal_setting') ? 'active open' : '' }}">
              <a href="javascript:void(0)" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-soup"></i>
                <div class="fs-5">Meal Management</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item {{ Route::currentRouteNamed('meals.create') ? 'active' : '' }}">
                  <a href="{{ route('meals.create') }}" class="menu-link">
                    <div class="fs-5">Daily Meal Setting</div>
                  </a>
                </li>
                <li class="menu-item {{ Route::currentRouteNamed('meals.index') ? 'active' : '' }}">
                  <a href="{{ route('meals.index') }}" class="menu-link">
                    <div class="fs-5">Daily Meal List</div>
                  </a>
                </li>
                <li class="menu-item {{ Route::currentRouteNamed('guest-meals.index') || Route::currentRouteNamed('guest-meals.create') || Route::currentRouteNamed('guest-meals.edit') ? 'active' : '' }}">
                  <a href="{{ route('guest-meals.index') }}" class="menu-link text-primary fw-semibold">
                    <div class="fs-5"><i class="ti ti-users me-1"></i>Guest Meal</div>
                  </a>
                </li>
                <li class="menu-item {{ Route::currentRouteNamed('meal-history') ? 'active' : '' }}">
                  <a href="{{ route('meal-history') }}" class="menu-link">
                    <div class="fs-5">Meal History</div>
                  </a>
                </li>
                <li class="menu-item {{ Route::currentRouteNamed('deposits.index') || Route::currentRouteNamed('deposits.create') ? 'active' : '' }}">
                  <a href="{{ route('deposits.index') }}" class="menu-link">
                    <div class="fs-5">Meal Deposits</div>
                  </a>
                </li>
                <li class="menu-item {{ Route::currentRouteNamed('deposit-refunds.*') ? 'active' : '' }}">
                  <a href="{{ route('deposit-refunds.index') }}" class="menu-link text-danger fw-semibold">
                    <div class="fs-5"><i class="ti ti-cash-refund me-1 text-danger"></i>Deposit Refund (ফেরত)</div>
                  </a>
                </li>
                <li class="menu-item {{ Route::currentRouteNamed('meal-requests.index') ? 'active' : '' }}">
                  <a href="{{ route('meal-requests.index') }}" class="menu-link">
                    <div class="fs-5">Meal Requests</div>
                  </a>
                </li>
                @role('admin|super-admin')
                <li class="menu-item {{ Route::currentRouteNamed('setting.index') && request()->slug === 'meal_setting' ? 'active' : '' }}">
                  <a href="{{ route('setting.index', 'meal_setting') }}" class="menu-link">
                    <div class="fs-5">Meal Rate Setting</div>
                  </a>
                </li>
                @endrole
              </ul>
            </li>

            {{-- Expense --}}
            <li class="menu-item {{ Route::currentRouteNamed('expense-type.index') || Route::currentRouteNamed('expense.index') ? 'active open' : '' }}">
              <a href="javascript:void(0)" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-receipt-tax"></i>
                <div data-i18n="Expense" class="fs-5">Expense</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item {{ Route::currentRouteNamed('expense-type.index') ? 'active' : '' }}">
                  <a href="{{ route('expense-type.index') }}" class="menu-link">
                    <div class="fs-5">Expense Type</div>
                  </a>
                </li>
                <li class="menu-item {{ Route::currentRouteNamed('expense.index') ? 'active' : '' }}">
                  <a href="{{ route('expense.index') }}" class="menu-link">
                    <div class="fs-5">Expense</div>
                  </a>
                </li>
              </ul>
            </li>

            {{-- Inventory --}}
            <li class="menu-item {{ Route::currentRouteNamed('brand-category.index') || Route::currentRouteNamed('brand.index') || Route::currentRouteNamed('product.index') || Route::currentRouteNamed('supplier.index') || Route::currentRouteNamed('product-stock.index') || Route::currentRouteNamed('manage-sale.index') ? 'active open' : '' }}">
              <a href="javascript:void(0)" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-packages"></i>
                <div data-i18n="Inventory" class="fs-5">Inventory</div>
              </a>
              <ul class="menu-sub">
              <li class="menu-item {{ Route::currentRouteNamed('product.index') ? 'active' : '' }}">
                  <a href="{{ route('product.index') }}" class="menu-link">
                    <div class="fs-5">Product</div>
                  </a>
              </li>
              <li class="menu-item {{ Route::currentRouteNamed('supplier.index') ? 'active' : '' }}">
                  <a href="{{ route('supplier.index') }}" class="menu-link">
                    <div class="fs-5">Supplier</div>
                  </a>
                </li>
                <li class="menu-item {{ Route::currentRouteNamed('product-stock.index') ? 'active' : '' }}">
                  <a href="{{ route('product-stock.index') }}" class="menu-link">
                    <div class="fs-5">Product Stock</div>
                  </a>
                </li>
                <li class="menu-item {{ Route::currentRouteNamed('product-distribution.index') ? 'active' : '' }}">
                  <a href="{{ route('product-distribution.index') }}" class="menu-link">
                    <div class="fs-5">Product Distribution</div>
                  </a>
                </li>
              </ul>
            </li>
            @endrole

            <!-- SECTION 4: HR & REPORTS -->
            @role('admin|super-admin|staffs|manager')
            <li class="menu-header small text-uppercase mt-3">
                <span class="menu-header-text fw-bold text-primary" style="letter-spacing: 0.8px; font-size: 0.72rem;">HR & REPORTS</span>
            </li>

            {{-- Staffs --}}
            <li class="menu-item {{ Route::currentRouteNamed('staffs.index') || Route::currentRouteNamed('staffs-attendance.index') || Route::currentRouteNamed('staff-salary.index') ? 'active open' : '' }}">
              <a href="javascript:void(0)" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-id-badge-2"></i>
                <div data-i18n="Staffs" class="fs-5">Staffs</div>
              </a>
              <ul class="menu-sub">
                @role('admin|super-admin')
                <li class="menu-item {{ Route::currentRouteNamed('staffs.index') ? 'active' : '' }}">
                  <a href="{{ route('staffs.index') }}" class="menu-link">
                    <div class="fs-5">Staffs List</div>
                  </a>
                </li>
                @endrole
                <li class="menu-item {{ Route::currentRouteNamed('staffs-attendance.index') ? 'active' : '' }}">
                  <a href="{{ route('staffs-attendance.index') }}" class="menu-link">
                    <div class="fs-5">Staff Attendance</div>
                  </a>
                </li>
                @role('admin|super-admin')
                <li class="menu-item {{ Route::currentRouteNamed('staff-salary.index') ? 'active' : '' }}">
                  <a href="{{ route('staff-salary.index') }}" class="menu-link">
                    <div class="fs-5">Staff Salary</div>
                  </a>
                </li>
                @endrole
              </ul>
            </li>

            {{-- Report --}}
            <li class="menu-item 
                {{ Route::currentRouteNamed('report.index') 
                || Route::currentRouteNamed('customer-report.index') 
                ? 'active open' : '' }}">
                <a href="javascript:void(0)" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-chart-pie-2"></i>
                    <div data-i18n="Report" class="fs-5">Report</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item {{ Route::currentRouteNamed('report.index') ? 'active' : '' }}">
                        <a href="{{ route('report.index') }}" class="menu-link">
                            <div class="fs-5">Management Report</div>
                        </a>
                    </li>
                </ul>
            </li>

            {{-- Emergency Notice (Outside Frontend) --}}
            <li class="menu-item {{ Route::currentRouteNamed('resident-notices.index') ? 'active' : '' }}">
                <a href="{{ route('resident-notices.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-bell-ringing"></i>
                    <div data-i18n="Emergency Notice" class="fs-5">Emergency Notice</div>
                </a>
            </li>

            {{-- Frontend --}}
            <li class="menu-item {{ Route::currentRouteNamed('notice.index') || Route::currentRouteNamed('residence-overview.index') || Route::currentRouteNamed('gallery.index') || Route::currentRouteNamed('slider.index') || Route::currentRouteNamed('hostel-rules.index') ? 'active open' : '' }}">
                <a href="javascript:void(0)" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-world"></i>
                    <div data-i18n="Frontend" class="fs-5">Frontend</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item {{ Route::currentRouteNamed('notice.index') ? 'active' : '' }}">
                        <a href="{{ route('notice.index') }}" class="menu-link">
                            <div class="fs-5">Notice (Marquee)</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Route::currentRouteNamed('residence-overview.index') ? 'active' : '' }}">
                        <a href="{{ route('residence-overview.index') }}" class="menu-link">
                            <div class="fs-5">Residence Overview</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Route::currentRouteNamed('gallery.index') ? 'active' : '' }}">
                        <a href="{{ route('gallery.index') }}" class="menu-link">
                            <div class="fs-5">Your Gallery</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Route::currentRouteNamed('slider.index') ? 'active' : '' }}">
                        <a href="{{ route('slider.index') }}" class="menu-link">
                            <div class="fs-5">Manage Sliders</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Route::currentRouteNamed('hostel-rules.index') ? 'active' : '' }}">
                        <a href="{{ route('hostel-rules.index') }}" class="menu-link">
                            <div class="fs-5">নিয়মাবলী (Hostel Rules)</div>
                        </a>
                    </li>
                </ul>
            </li>
            @endrole

            <!-- SECTION 5: ADMINISTRATION -->
            @canany(['user-index','user-create','user-edit','user-delete','setting-index','setting-create','setting-edit','setting-delete','permission-index','permission-create','permission-edit','permission-delete','role-index','role-create','role-edit','role-delete'])
            <li class="menu-header small text-uppercase mt-3">
                <span class="menu-header-text fw-bold text-primary" style="letter-spacing: 0.8px; font-size: 0.72rem;">ADMINISTRATION</span>
            </li>
            @endcanany

            @canany(['user-index','user-create','user-edit','user-delete'])
            <li class="menu-item {{ Route::currentRouteNamed('user.index') || Route::currentRouteNamed('user.create') || Route::currentRouteNamed('user.edit') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-users"></i>
                    <div data-i18n="User" class="fs-5"> User</div>
                </a>
                <ul class="menu-sub">
                    @can('user-create')
                        <li class="menu-item {{Route::currentRouteNamed('user.create') ? 'active' : ''}}">
                            <a href="{{route('user.create')}}" class="menu-link">
                                <div data-i18n="Add New" class="fs-5">Add New</div>
                            </a>
                        </li>
                    @endcan
                    @canany(['user-index', 'user-edit', 'user-delete'])
                        <li class="menu-item {{Route::currentRouteNamed('user.index') || Route::currentRouteNamed('user.edit') ? 'active' : ''}}">
                            <a href="{{route('user.index')}}" class="menu-link">
                                <div data-i18n="User list" class="fs-5">User List</div>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
            @endcanany

            @canany(['setting-index','setting-create','setting-edit','setting-delete'])
            <li class="menu-item {{ Request::is('backend/setting/*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-settings"></i>
                    <div data-i18n="User" class="fs-5">Basic Setting</div>
                </a>
                <ul class="menu-sub">
                    @canany(['setting-create','setting-index', 'setting-edit', 'setting-delete'])
                        <li class="menu-item {{ Request::segment(3) == 'web_setting' ? 'active' : '' }}">
                            <a href="{{ route('setting.index', 'web_setting') }}" class="menu-link">
                                <div data-i18n="Setting list" class="fs-5">Web Setting</div>
                            </a>
                        </li>
                        <li class="menu-item {{ Request::segment(3) == 'logo_setting' ? 'active' : '' }}">
                            <a href="{{ route('setting.index', 'logo_setting') }}" class="menu-link">
                                <div data-i18n="Setting list" class="fs-5">Logo Setting</div>
                            </a>
                        </li>
                        <li class="menu-item {{ Request::segment(3) == 'app_setting' ? 'active' : '' }}">
                            <a href="{{ route('setting.index', 'app_setting') }}" class="menu-link">
                                <div data-i18n="App Setting" class="fs-5">App Setting</div>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
            @endcanany

            @canany(['permission-index','permission-create','permission-edit','permission-delete','role-index','role-create','role-edit','role-delete'])
            <li class="menu-item {{ Route::currentRouteNamed('permission.index') || Route::currentRouteNamed('permission.create') || Route::currentRouteNamed('permission.edit') || Route::currentRouteNamed('role.index') || Route::currentRouteNamed('role.create') || Route::currentRouteNamed('role.edit') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-lock"></i>
                    <div data-i18n="Confidential" class="fs-5">Confidential</div>
                </a>
                <ul class="menu-sub">
                    @canany(['role-index','role-create','role-edit','role-delete'])
                        <li class="menu-item {{Route::currentRouteNamed('role.index') || Route::currentRouteNamed('role.create') || Route::currentRouteNamed('role.edit') ? 'active' : ''}}">
                            <a href="{{route('role.index')}}" class="menu-link">
                                <div data-i18n="Role" class="fs-5">Role</div>
                            </a>
                        </li>
                    @endcan
                    @canany(['permission-index','permission-create','permission-edit','permission-delete'])
                        <li class="menu-item {{Route::currentRouteNamed('permission.index') || Route::currentRouteNamed('permission.create') || Route::currentRouteNamed('permission.edit') ? 'active' : ''}}">
                            <a href="{{route('permission.index')}}" class="menu-link">
                                <div data-i18n="Permission" class="fs-5">Permission</div>
                            </a>
                        </li>
                    @endcanany
                </ul>
            </li>
            @endcanany

        </ul>

  <style>
  #layout-menu {
    height: 100vh;
    overflow: hidden;
    background: #ffffff !important;
    box-shadow: 2px 0 12px rgba(0, 0, 0, 0.04);
  }

  #layout-menu .menu-inner {
    height: 100%;
    overflow-y: auto;
    overflow-x: hidden;
    background: #ffffff !important;
    padding-bottom: 25px;
  }

  /* Custom Sleek Scrollbar */
  #layout-menu .menu-inner::-webkit-scrollbar {
    width: 5px;
  }
  #layout-menu .menu-inner::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
  }
  #layout-menu .menu-inner::-webkit-scrollbar-track {
    background: transparent;
  }

  /* Menu Header Titles */
  .menu-header-text {
    color: #6366f1 !important;
    font-weight: 700 !important;
    letter-spacing: 0.9px !important;
  }

  /* Menu Link Hover & Transitions */
  .menu-item .menu-link {
    transition: all 0.22s ease-in-out !important;
    border-radius: 8px !important;
    margin: 2px 10px !important;
    width: auto !important;
  }

  .menu-item:not(.active) .menu-link:hover {
    background: #f1f5f9 !important;
    color: #4f46e5 !important;
    transform: translateX(3px);
  }

  /* Active Menu Item Pill Glow */
  .menu-item.active > .menu-link {
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%) !important;
    color: #ffffff !important;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.28) !important;
    font-weight: 600 !important;
  }

  .menu-item.active > .menu-link .menu-icon {
    color: #ffffff !important;
  }

  /* Submenu Bullet Styling */
  .menu-sub .menu-link {
    padding-left: 2.2rem !important;
  }

  .menu-sub .menu-item.active > .menu-link {
    background: #eeefef !important;
    color: #4f46e5 !important;
    font-weight: 700 !important;
    box-shadow: none !important;
  }
  </style>
