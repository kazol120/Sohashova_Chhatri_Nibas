          <ul class="menu-inner py-1">
            <!-- Dashboards -->
              <li class="menu-item dashboard-menu {{ Route::currentRouteNamed('dashboard') ? 'active' : '' }}">
                  <a href="{{route('dashboard')}}" class="menu-link">
                      <i class="menu-icon tf-icons ti ti-smart-home"></i>
                      <div data-i18n="Dashboard" class="fs-5">Dashboard</div>
                  </a>
              </li>
              
              <!-- Resident My Payments & Meals -->
              @role('HotelGuest')
              <li class="menu-item {{ Route::currentRouteNamed('dashboard.my-payments') ? 'active' : '' }}">
                  <a href="{{ route('dashboard.my-payments') }}" class="menu-link">
                      <i class="menu-icon tf-icons ti ti-wallet"></i>
                      <div data-i18n="My Payments" class="fs-5">My Payments</div>
                  </a>
              </li>
              <li class="menu-item {{ Route::currentRouteNamed('dashboard.my-meals') ? 'active' : '' }}">
                  <a href="{{ route('dashboard.my-meals') }}" class="menu-link">
                      <i class="menu-icon tf-icons ti ti-salad"></i>
                      <div data-i18n="My Meal History" class="fs-5">My Meal History</div>
                  </a>
              </li>
              <li class="menu-item {{ Route::currentRouteNamed('meals.create') ? 'active' : '' }}">
                  <a href="{{ route('meals.create') }}" class="menu-link">
                      <i class="menu-icon tf-icons ti ti-calendar-event"></i>
                      <div data-i18n="Daily Meal Create" class="fs-5">Daily Meal Create</div>
                  </a>
              </li>
              @endrole
              @canany(['user-index','user-create','user-edit','user-delete'])
              <!-- User Management -->
              <li class="menu-header small">
                  <span class="menu-header-text" data-i18n="User Management" class="fs-5">User Management</span>
              </li>
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
              <!-- User Management -->
              <li class="menu-header small">
                  <span class="menu-header-text" data-i18n="Setting Management">Setting Management</span>
              </li>
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
                      @endcan
                  </ul>
              </li>
          @endcanany
          @canany(['permission-index','permission-create','permission-edit','permission-delete','role-index','role-create','role-edit','role-delete'])
              <!-- Confidential -->
              <li class="menu-header small">
                  <span class="menu-header-text" data-i18n="Confidential">Confidential</span>
              </li>
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

            
     

      {{-- Frontend --}}
      {{-- Frontend --}}
              @role('admin')
              <li class="menu-item {{ Route::currentRouteNamed('notice.index') || Route::currentRouteNamed('residence-overview.index') || Route::currentRouteNamed('gallery.index') ? 'active open' : '' }}">
                  <a href="javascript:void(0)" class="menu-link menu-toggle fs-5">
                      <i class="fas fa-clipboard-list" style="background-color: white; padding: 5px; border-radius: 50%;"></i>
                      <div data-i18n="prescription" class="ms-3">Frontend</div>
                  </a>
                  <ul class="menu-sub">
                      <li class="menu-item {{ Route::currentRouteNamed('notice.index') ? 'active' : '' }}">
                          <a href="{{ route('notice.index') }}" class="menu-link">
                              <div class="fs-5">Notice</div>
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
                  </ul>
              </li>
              @endrole





              {{-- Floor --}}
              @role('admin')
              <li class="menu-item {{ Route::currentRouteNamed('floor.index') ? 'active open' : '' }}">
                <a href="javascript:void(0)" class="menu-link menu-toggle fs-5">
                  <i class="fas fa-clipboard-list" style="background-color: white; padding: 5px; border-radius: 50%;"></i>
                  <div data-i18n="prescription" class="ms-3">Floor</div>
                </a>
                <ul class="menu-sub">
                  <li class="menu-item {{ Route::currentRouteNamed('floor.index') ? 'active' : '' }}">
                    <a href="{{ route('floor.index') }}" class="menu-link">
                      <div class="fs-5">Floor List</div>
                    </a>
                  </li>
                </ul>
              </li>
              @endrole

              {{-- Room --}}
              @role('admin')
              <li class="menu-item {{ Route::currentRouteNamed('room-list.index') || Request::is('room-booking-history') || Route::currentRouteNamed('room-release.index') || Route::currentRouteNamed('room-release.history') ? 'active open' : '' }}">
                <a href="javascript:void(0)" class="menu-link menu-toggle fs-5">
                  <i class="fas fa-clipboard-list" style="background-color: white; padding: 5px; border-radius: 50%;"></i>
                  <div data-i18n="prescription" class="ms-3">Room</div>
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
                </ul>
              </li>
              @endrole

              {{-- Monthly Payments --}}
              @role('admin')
              <li class="menu-item {{ Route::currentRouteNamed('monthly-payments.index') ? 'active open' : '' }}">
                <a href="javascript:void(0)" class="menu-link menu-toggle fs-5">
                  <i class="fas fa-money-bill-wave" style="background-color: white; padding: 5px; border-radius: 50%;"></i>
                  <div data-i18n="prescription" class="ms-3">Monthly Payments</div>
                </a>
                <ul class="menu-sub">
                  <li class="menu-item {{ Route::currentRouteNamed('monthly-payments.index') ? 'active' : '' }}">
                    <a href="{{ route('monthly-payments.index') }}" class="menu-link">
                      <div class="fs-5">Payments</div>
                    </a>
                  </li>
                </ul>
              </li>
              @endrole

              {{-- Meal Management --}}
              @role('admin')
              <li class="menu-item {{ Route::currentRouteNamed('meals.index') || Route::currentRouteNamed('meals.create') || Route::currentRouteNamed('meal-history') || Route::currentRouteNamed('deposits.index') || Route::currentRouteNamed('deposits.create') || Route::currentRouteNamed('fines.index') || Route::currentRouteNamed('fines.create') ? 'active open' : '' }}">
                <a href="javascript:void(0)" class="menu-link menu-toggle fs-5">
                  <i class="fas fa-utensils" style="background-color: white; padding: 5px; border-radius: 50%;"></i>
                  <div class="ms-3">Meal Management</div>
                </a>
                <ul class="menu-sub">
                  <li class="menu-item {{ Route::currentRouteNamed('meals.create') ? 'active' : '' }}">
                    <a href="{{ route('meals.create') }}" class="menu-link">
                      <div class="fs-5">Daily Meal Create</div>
                    </a>
                  </li>
                  <li class="menu-item {{ Route::currentRouteNamed('meals.index') ? 'active' : '' }}">
                    <a href="{{ route('meals.index') }}" class="menu-link">
                      <div class="fs-5">Daily Meal List</div>
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
                  <li class="menu-item {{ Route::currentRouteNamed('fines.index') || Route::currentRouteNamed('fines.create') ? 'active' : '' }}">
                    <a href="{{ route('fines.index') }}" class="menu-link">
                      <div class="fs-5">Meal Fines</div>
                    </a>
                  </li>
                </ul>
              </li>
              @endrole

              {{-- Staffs --}}
              @role('admin')
              <li class="menu-item {{ Route::currentRouteNamed('staffs.index') || Route::currentRouteNamed('staffs-attendance.index') || Route::currentRouteNamed('staff-salary.index') ? 'active open' : '' }}">
                <a href="javascript:void(0)" class="menu-link menu-toggle fs-5">
                  <i class="fas fa-clipboard-list" style="background-color: white; padding: 5px; border-radius: 50%;"></i>
                  <div data-i18n="prescription" class="ms-3">Staffs</div>
                </a>
                <ul class="menu-sub">
                  <li class="menu-item {{ Route::currentRouteNamed('staffs.index') ? 'active' : '' }}">
                    <a href="{{ route('staffs.index') }}" class="menu-link">
                      <div class="fs-5">Staffs List</div>
                    </a>
                  </li>
                  <li class="menu-item {{ Route::currentRouteNamed('staffs-attendance.index') ? 'active' : '' }}">
                    <a href="{{ route('staffs-attendance.index') }}" class="menu-link">
                      <div class="fs-5">Staff Attendance</div>
                    </a>
                  </li>
                  <li class="menu-item {{ Route::currentRouteNamed('staff-salary.index') ? 'active' : '' }}">
                    <a href="{{ route('staff-salary.index') }}" class="menu-link">
                      <div class="fs-5">Staff Salary</div>
                    </a>
                  </li>
                </ul>
              </li>
              @endrole
              {{-- Expense --}}
              @role('admin')
              <li class="menu-item {{ Route::currentRouteNamed('expense-type.index') || Route::currentRouteNamed('expense.index') ? 'active open' : '' }}">
                <a href="javascript:void(0)" class="menu-link menu-toggle fs-5">
                  <i class="fas fa-clipboard-list" style="background-color: white; padding: 5px; border-radius: 50%;"></i>
                  <div data-i18n="prescription" class="ms-3">Expense</div>
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
              @endrole

              @role('admin')
              <li class="menu-item {{ Route::currentRouteNamed('management.index') ? 'active open' : '' }}">
                <a href="javascript:void(0)" class="menu-link menu-toggle fs-5">
                  <i class="fas fa-clipboard-list" style="background-color: white; padding: 5px; border-radius: 50%;"></i>
                  <div data-i18n="prescription" class="ms-3">Managmect Product</div>
                </a>
                <ul class="menu-sub">
                  <li class="menu-item {{ Route::currentRouteNamed('management.index') ? 'active' : '' }}">
                    <a href="{{ route('management.index') }}" class="menu-link">
                      <div class="fs-5">Management</div>
                    </a>
                  </li>
                </ul>
              </li>
              @endrole
              {{-- Inventory --}}
              @role('admin')
              <li class="menu-item {{ Route::currentRouteNamed('brand-category.index') || Route::currentRouteNamed('brand.index') || Route::currentRouteNamed('product.index') || Route::currentRouteNamed('supplier.index') || Route::currentRouteNamed('product-stock.index') || Route::currentRouteNamed('manage-sale.index') ? 'active open' : '' }}">
                <a href="javascript:void(0)" class="menu-link menu-toggle fs-5">
                  <i class="fas fa-clipboard-list" style="background-color: white; padding: 5px; border-radius: 50%;"></i>
                  <div data-i18n="inventory" class="ms-3">Inventory</div>
                </a>
                <ul class="menu-sub">
             {{-- <li class="menu-item {{ Route::currentRouteNamed('brand-category.index') ? 'active' : '' }}">
                    <a href="{{ route('brand-category.index') }}" class="menu-link">
                      <div class="fs-5">Brand Category</div>
                    </a>
                  </li>
                  <li class="menu-item {{ Route::currentRouteNamed('brand.index') ? 'active' : '' }}">
                    <a href="{{ route('brand.index') }}" class="menu-link">
                      <div class="fs-5">Brand</div>
                    </a>
                  </li> --}}
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

            {{-- Restaurant & Bar --}}
             @role('admin')
              <li class="menu-item 
                  {{ Route::currentRouteNamed('report.index') 
                  || Route::currentRouteNamed('customer-report.index') 
                  ? 'active open' : '' }}">
                  <a href="javascript:void(0)" class="menu-link menu-toggle fs-5">
                      <i class="fas fa-clipboard-list" style="background-color: white; padding: 5px; border-radius: 50%;"></i>
                      <div data-i18n="prescription" class="ms-3">Report</div>
                  </a>
                  <ul class="menu-sub">
                      <li class="menu-item {{ Route::currentRouteNamed('report.index') ? 'active' : '' }}">
                          <a href="{{ route('report.index') }}" class="menu-link">
                              <div class="fs-5">Management Report</div>
                          </a>
                      </li>
                      <li class="menu-item {{ Route::currentRouteNamed('customer-report.index') ? 'active' : '' }}">
                          <a href="{{ route('customer-report.index') }}" class="menu-link">
                              <div class="fs-5">Guest Report</div>
                          </a>
                      </li>
                  </ul>
              </li>
              @endrole


          <li class="menu-item w-75">             
            <ul>
              <i class="ti ti-logout fs-7"></i>
              <a class="ms-2"  href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <small class="align-middle fs-5">Logout</small>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                  @csrf
                </form>
              </a>
              </ul> 
            </li>
        </ul>

  <style>
  #layout-menu {
    height: 100vh;
    overflow: hidden;
    background: #fff !important;
  }

  #layout-menu .menu-inner {
    height: 100%;
    overflow-y: auto;
    overflow-x: hidden;
    background: #fff !important;
  }

  </style>


