@extends('backend.layouts.app')
@section("title") | {{$page_title}} @endsection
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row g-6 mb-6">
        
        <!-- RESIDENT / STUDENT USER SPECIFIC STAT CARDS -->
        @unlessrole('admin|staffs')
        
        <!-- My Room & Booking Status -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 position-relative overflow-hidden border-0 shadow-sm" style="border-top: 4px solid #6366f1 !important;">
                <div class="card-body pb-1">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">My Booking Status</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2 text-primary">{{ $userBooking ? 'Active Resident' : 'No Booking' }}</h4>
                            </div>
                            <small class="mb-0 text-muted">
                                @if($userBooking)
                                  <i class="fa fa-bed me-1 text-primary"></i> ৳ {{ number_format($userBooking->monthly_amount ?? 0) }}/month
                                @else
                                  No Active Booking Record
                                @endif
                            </small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                               <i class="fa fa-user-check"></i>
                            </span>
                        </div>
                    </div>
                    <div id="chart-mybooking" class="mt-2" style="min-height: 45px;"></div>
                </div>
            </div>
        </div>

        <!-- My Payments Card -->
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('dashboard.my-payments') }}" class="text-decoration-none">
                <div class="card h-100 position-relative overflow-hidden border-0 shadow-sm" style="border-top: 4px solid #10b981 !important;">
                    <div class="card-body pb-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">My Payments</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2 text-success">৳ {{ number_format($myTotalPaid ?? 0) }}</h4>
                                </div>
                                <small class="mb-0 text-muted">Rent Payment History</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-success">
                                   <i class="fa fa-wallet"></i>
                                </span>
                            </div>
                        </div>
                        <div id="chart-mypayments" class="mt-2" style="min-height: 45px;"></div>
                    </div>
                </div>
            </a>
        </div>

        <!-- My Meal History Card -->
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('dashboard.my-meals') }}" class="text-decoration-none">
                <div class="card h-100 position-relative overflow-hidden border-0 shadow-sm" style="border-top: 4px solid #ff9f43 !important;">
                    <div class="card-body pb-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">My Meal History</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2 text-warning">{{ number_format($myMealCount ?? 0) }} Meals</h4>
                                </div>
                                <small class="mb-0 text-muted">Meal Records</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-warning">
                                   <i class="fa fa-utensils"></i>
                                </span>
                            </div>
                        </div>
                        <div id="chart-mymeals" class="mt-2" style="min-height: 45px;"></div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Daily Meal Create Card -->
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('meals.create') }}" class="text-decoration-none">
                <div class="card h-100 position-relative overflow-hidden border-0 shadow-sm" style="border-top: 4px solid #00cfdd !important;">
                    <div class="card-body pb-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Daily Meal Create</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2 text-info">Enter Meal</h4>
                                </div>
                                <small class="mb-0 text-muted">Daily Meal Entry</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-info">
                                   <i class="fa fa-calendar-plus"></i>
                                </span>
                            </div>
                        </div>
                        <div id="chart-createmeal" class="mt-2" style="min-height: 45px;"></div>
                    </div>
                </div>
            </a>
        </div>

        @endunlessrole


        <!-- ADMIN & STAFF STAT CARDS -->
        <!-- Floor -->
        @role('admin')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ url('floor') }}">
                <div class="card h-100 position-relative overflow-hidden">
                    <div class="card-body pb-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Floor</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ $floorscount }}</h4>
                                </div>
                                <small class="mb-0 text-muted">Total Floors</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-primary">
                                  <i class="fa fa-building"></i>
                                </span>
                            </div>
                        </div>
                        <div id="chart-floor" class="mt-2" style="min-height: 45px;"></div>
                    </div>
                </div>
            </a>
        </div>
        @endrole

        <!-- Total Rooms -->
        @role('admin')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ url('room-list') }}">
                <div class="card h-100 position-relative overflow-hidden">
                    <div class="card-body pb-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Total Rooms</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ $roomcount }}</h4>
                                </div>
                                <small class="mb-0 text-muted">Total Rooms</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-danger">
                                <i class="fa fa-bed"></i>
                                </span>
                            </div>
                        </div>
                        <div id="chart-room" class="mt-2" style="min-height: 45px;"></div>
                    </div>
                </div>
            </a>
        </div>
        @endrole

        <!-- Total Seats -->
        @role('admin|staffs')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('room-list.index') }}">
                <div class="card h-100 position-relative overflow-hidden">
                    <div class="card-body pb-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Total Seats</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2 text-primary">{{ $totalseatcount }}</h4>
                                </div>
                                <small class="mb-0 text-muted">Hostel Capacity</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-primary">
                                  <i class="fa fa-th-large"></i>
                                </span>
                            </div>
                        </div>
                        <div id="chart-totalseat" class="mt-2" style="min-height: 45px;"></div>
                    </div>
                </div>
            </a>
        </div>
        @endrole

        <!-- Available Seats -->
        @role('admin|staffs')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('room-list.index') }}">
                <div class="card h-100 position-relative overflow-hidden">
                    <div class="card-body pb-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Available Seats</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2 text-info">{{ $availableseatcount }}</h4>
                                </div>
                                <small class="mb-0 text-muted">Free Seats</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-info">
                                  <i class="fa fa-check-circle"></i>
                                </span>
                            </div>
                        </div>
                        <div id="chart-availseat" class="mt-2" style="min-height: 45px;"></div>
                    </div>
                </div>
            </a>
        </div>
        @endrole

        <!-- Booked Seats for Admin -->
        @role('admin')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ url('room-booking-history') }}">
                <div class="card h-100 position-relative overflow-hidden">
                    <div class="card-body pb-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Booked Seats</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2 text-success">{{ $roombookingcount }}</h4>
                                </div>
                                <small class="mb-0 text-muted">Active Resident Bookings</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-success">
                                   <i class="fa fa-user-check"></i>
                                </span>
                            </div>
                        </div>
                        <div id="chart-bookedseat" class="mt-2" style="min-height: 45px;"></div>
                    </div>
                </div>
            </a>
        </div>
        @endrole

        <!-- Staffs -->
        @hasanyrole('admin|staffs')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ url('staffs') }}">
                <div class="card h-100 position-relative overflow-hidden">
                    <div class="card-body pb-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Staffs</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ $staffscount }}</h4>
                                </div>
                                <small class="mb-0 text-muted">Total Employees</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="fa fa-users"></i>
                                </span>
                            </div>
                        </div>
                        <div id="chart-staff" class="mt-2" style="min-height: 45px;"></div>
                    </div>
                </div>
            </a>
        </div>
        @endhasanyrole

        <!-- Release History -->
        @role('admin')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('room-release.history') }}">
                <div class="card h-100 position-relative overflow-hidden">
                    <div class="card-body pb-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Release History</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ $releasehistorycount }}</h4>
                                </div>
                                <small class="mb-0 text-muted">Checkout & Release</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-warning">
                                   <i class="fa fa-user-slash"></i>
                                </span>
                            </div>
                        </div>
                        <div id="chart-release" class="mt-2" style="min-height: 45px;"></div>
                    </div>
                </div>
            </a>
        </div>
        @endrole

        <!-- This Month Rent Collection -->
        @role('admin|staffs')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('monthly-payments.index') }}">
                <div class="card h-100 position-relative overflow-hidden">
                    <div class="card-body pb-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">This Month Rent</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2 text-success">৳ {{ number_format($thisMonthCollection) }}</h4>
                                </div>
                                <small class="mb-0 text-muted">{{ \Carbon\Carbon::now()->format('F Y') }} Collection</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-success">
                                   <i class="fa fa-wallet"></i>
                                </span>
                            </div>
                        </div>
                        <div id="chart-rent" class="mt-2" style="min-height: 45px;"></div>
                    </div>
                </div>
            </a>
        </div>
        @endrole

        <!-- This Month Due Rent -->
        @role('admin|staffs')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('monthly-payments.index') }}">
                <div class="card h-100 position-relative overflow-hidden">
                    <div class="card-body pb-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">This Month Due</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2 text-danger">৳ {{ number_format($thisMonthDue) }}</h4>
                                </div>
                                <small class="mb-0 text-danger">Unpaid Rent Total</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-danger">
                                   <i class="fa fa-exclamation-circle"></i>
                                </span>
                            </div>
                        </div>
                        <div id="chart-due" class="mt-2" style="min-height: 45px;"></div>
                    </div>
                </div>
            </a>
        </div>
        @endrole

        <!-- Today Meal Entries -->
        @role('admin|staffs')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('meals.index') }}">
                <div class="card h-100 position-relative overflow-hidden">
                    <div class="card-body pb-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Today Meal Count</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ number_format($todayMealCount) }}</h4>
                                </div>
                                <small class="mb-0 text-muted">{{ \Carbon\Carbon::today()->format('d M Y') }}</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-warning">
                                   <i class="fa fa-utensils"></i>
                                </span>
                            </div>
                        </div>
                        <div id="chart-meal" class="mt-2" style="min-height: 45px;"></div>
                    </div>
                </div>
            </a>
        </div>
        @endrole

        <!-- Today Expense -->
        @role('admin')
        <div class="col-sm-6 col-xl-3">
           <a href="{{ url('today-expense') }}">
                <div class="card h-100 position-relative overflow-hidden">
                    <div class="card-body pb-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Today Expense </span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">৳ {{ number_format($todayExpense) }}</h4>
                                </div>
                                <small class="mb-0 text-muted">{{ \Carbon\Carbon::today()->format('d M Y') }}</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-success">
                                 <i class="fa fa-money-bill-wave"></i>  
                                </span>
                            </div>
                        </div>
                        <div id="chart-expense" class="mt-2" style="min-height: 45px;"></div>
                    </div>
                </div>
            </a>
        </div>
        @endrole

        <!-- Today Product Distribution -->
        @role('admin')
        <div class="col-sm-6 col-xl-3">
           <a href="{{ url('today-product-distribution') }}">
                <div class="card h-100 position-relative overflow-hidden">
                    <div class="card-body pb-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Today Product Distribution </span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ number_format($todayproductdistribution) }}</h4>
                                </div>
                                <small class="mb-0 text-muted">{{ \Carbon\Carbon::today()->format('d M Y') }}</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-success">
                                   <i class="fa fa-dolly"></i>
                                </span>
                            </div>
                        </div>
                        <div id="chart-product" class="mt-2" style="min-height: 45px;"></div>
                    </div>
                </div>
            </a>
        </div>
        @endrole

    </div>
</div>

<style type="text/css">
.card {
    transition: transform 0.2s, box-shadow 0.2s;
    border-radius: 10px;
}
.card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(99,102,241,0.15);
}
.card:hover h4 {
    color: #6366f1;
    transform: scale(1.05);
    display: inline-block;
    transition: color 0.2s, transform 0.2s;
}
.card:hover .avatar-initial {
    background-color: #6366f1 !important;
    color: white !important;
}
</style>

<!-- ApexCharts Script for Mini Sparklines on ALL Cards -->
<script src="{{ asset('backend/vendor/libs/apex-charts/apexcharts.js') }}"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const createSparkline = (selector, data, color) => {
        const el = document.querySelector(selector);
        if (!el) return;
        const options = {
            chart: {
                type: 'area',
                height: 45,
                sparkline: { enabled: true },
                animations: { enabled: true }
            },
            stroke: { curve: 'smooth', width: 2 },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.05,
                    stops: [0, 90, 100]
                }
            },
            series: [{ name: 'Count', data: data }],
            colors: [color],
            tooltip: { enabled: false }
        };
        new ApexCharts(el, options).render();
    };

    const rentData = {!! $rentTrends ?? '[]' !!};
    const expenseData = {!! $expenseTrends ?? '[]' !!};
    const mealData = {!! $mealTrends ?? '[]' !!};
    const productData = {!! $productTrends ?? '[]' !!};

    createSparkline("#chart-mybooking", [1, 1, 1, 1, 1, 1, 1], "#6366f1");
    createSparkline("#chart-mypayments", [5000, 5000, 5000, 5000, 5000, 5000, 5000], "#28c76f");
    createSparkline("#chart-mymeals", [2, 3, 2, 3, 2, 3, 2], "#ff9f43");
    createSparkline("#chart-createmeal", [1, 2, 1, 2, 1, 2, 1], "#00cfdd");

    createSparkline("#chart-floor", [1, 2, 2, 3, 3, 3, 3], "#6366f1");
    createSparkline("#chart-room", [60, 62, 65, 65, 68, 68, 68], "#ea5455");
    createSparkline("#chart-totalseat", [70, 72, 75, 78, 78, 78, 78], "#7367f0");
    createSparkline("#chart-availseat", [78, 77, 76, 76, 76, 76, 76], "#00cfdd");
    createSparkline("#chart-bookedseat", [0, 1, 2, 2, 2, 2, 2], "#28c76f");
    createSparkline("#chart-staff", [1, 2, 2, 3, 3, 3, 3], "#28c76f");
    createSparkline("#chart-release", [0, 0, 1, 1, 1, 1, 1], "#ff9f43");

    createSparkline("#chart-rent", rentData.length ? rentData : [10, 25, 18, 40, 35, 60, 80], "#28c76f");
    createSparkline("#chart-due", [5, 12, 8, 15, 10, 22, 18], "#ea5455");
    createSparkline("#chart-meal", mealData.length ? mealData : [40, 50, 45, 60, 55, 70, 65], "#ff9f43");
    createSparkline("#chart-expense", expenseData.length ? expenseData : [12, 18, 14, 25, 20, 30, 28], "#7367f0");
    createSparkline("#chart-product", productData.length ? productData : [2, 5, 3, 8, 6, 12, 10], "#00cfdd");
});
</script>
@endsection