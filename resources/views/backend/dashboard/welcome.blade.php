@extends('backend.layouts.app')
@section("title") | {{$page_title}} @endsection
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row g-6 mb-6">
        
        <!-- RESIDENT / STUDENT USER SPECIFIC STAT CARDS -->
        @unlessrole('admin|staffs')
        
        <div class="col-12 mb-2">
            <div class="card border-0 shadow-sm p-3 bg-white" style="border-left: 5px solid #6366f1 !important;">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark">
                            <i class="fa fa-user-circle text-primary me-2"></i>Resident Dashboard (বোর্ডার ড্যাশবোর্ড)
                        </h5>
                        <small class="text-muted">Welcome, <strong>{{ auth()->user()->name }}</strong>! Manage your hostel booking & profile.</small>
                    </div>
                    @if(isset($userBooking) && $userBooking)
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" onclick="printResidentDocument()" class="btn btn-primary fw-bold waves-effect waves-light shadow-sm">
                            <i class="fa fa-print me-2"></i> Confirm Booking Document
                        </button>
                        <button type="button" onclick="printResidentIdCard()" class="btn btn-info fw-bold waves-effect waves-light shadow-sm">
                            <i class="fa fa-id-card me-2"></i> Print ID Card
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
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

        <!-- My Complaints Card -->
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('complaints.index') }}" class="text-decoration-none">
                <div class="card h-100 position-relative overflow-hidden border-0 shadow-sm" style="border-top: 4px solid #ef4444 !important;">
                    <div class="card-body pb-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading text-danger fw-semibold">My Complaints</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2 text-danger">{{ \App\Models\Backend\Complaint::where('user_id', Auth::id())->count() }}</h4>
                                </div>
                                <small class="mb-0 text-muted">Submit & View Issues</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-danger">
                                   <i class="fa fa-exclamation-triangle"></i>
                                </span>
                            </div>
                        </div>
                        <div id="chart-complaint" class="mt-2" style="min-height: 45px;"></div>
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

        <!-- Complaints Card for Admin -->
        @role('admin|staffs')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('complaints.index') }}" class="text-decoration-none">
                <div class="card h-100 position-relative overflow-hidden border-0 shadow-sm" style="border-top: 4px solid #ef4444 !important;">
                    <div class="card-body pb-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading text-danger fw-semibold">Complaints (অভিযোগ)</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2 text-danger">{{ \App\Models\Backend\Complaint::where('status', 0)->count() }}</h4>
                                </div>
                                <small class="mb-0 text-muted">Pending Complaints</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-danger">
                                   <i class="fa fa-bell"></i>
                                </span>
                            </div>
                        </div>
                        <div id="chart-admin-complaint" class="mt-2" style="min-height: 45px;"></div>
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

<script>
window.userBookingData = @json($userBooking ?? null);
window.userProfileData = @json(auth()->user());

function printResidentDocument() {
    var r = window.userBookingData;
    var u = window.userProfileData || {};

    if (!r) {
        alert('No active booking record found!');
        return;
    }

    var logoUrl = window.location.origin + '/logo/logoimage (2).png';
    var userImgUrl = r.image ? (window.location.origin + '/bookingsimage/' + r.image) : (u.avatar_url || '');

    var roomItems = [];
    if (typeof r.floor_number_room_number_roomprice === 'string') {
        try { roomItems = JSON.parse(r.floor_number_room_number_roomprice); } catch(e){}
    } else if (Array.isArray(r.floor_number_room_number_roomprice)) {
        roomItems = r.floor_number_room_number_roomprice;
    }

    function getRoomNo(str) {
        if (!str) return '-';
        var parts = String(str).split('-');
        return parts[0] || str;
    }
    function getSeatNo(str) {
        if (!str) return '-';
        var parts = String(str).split('-');
        return parts.length > 1 ? parts.slice(1).join('-') : '-';
    }

    var roomNo = roomItems.length
        ? roomItems.map(function(i){ return getRoomNo(i.roomnumber); }).join(', ')
        : (getRoomNo(r.roomnumber) || r.room_number || '-');
    var seatNo = roomItems.length
        ? roomItems.map(function(i){ return getSeatNo(i.roomnumber); }).join(', ')
        : (getSeatNo(r.roomnumber) || '-');
    var floorNo = roomItems.length
        ? Array.from(new Set(roomItems.map(function(i){ return i.floornumber; }))).filter(Boolean).join(', ')
        : (r.floornumber || '-');

    var fullName = r.full_name || u.name || '-';
    var phone = r.phone || u.phone || '-';
    var address = r.address || u.address || '-';
    var thanaName = (r.thana && r.thana.name) ? r.thana.name : (r.thana_name || '-');
    var districtName = (r.district && r.district.name) ? r.district.name : (r.district_name || '-');
    var bookingDate = r.created_at
        ? new Date(r.created_at).toLocaleString('bn-BD', { dateStyle: 'long', timeStyle: 'short' })
        : '-';

    var userType = (r.user_type || u.user_type || '').toLowerCase();
    var isProf = userType.includes('professional') || userType.includes('job') || userType.includes('passenger');

    var docTitle = isProf ? 'কর্মজীবীর তথ্য - টি এস এস ভিলা' : 'শিক্ষার্থীর তথ্য - টি এস এস ভিলা';
    var sectionTitleText = isProf ? 'কর্মজীবীর তথ্য' : 'শিক্ষার্থীর তথ্য';
    var signatureLabelText = isProf ? 'বোর্ডারের স্বাক্ষর' : 'শিক্ষার্থীর স্বাক্ষর';

    var institutionName = r.institution_name || u.institution_name || '-';
    var workplaceName = r.workplace_name || u.workplace_name || '-';
    var fatherName = r.father_name || u.father_name || '-';
    var fatherPhone = r.father_phone || u.father_phone || '-';
    var motherName = r.mother_name || u.mother_name || '-';
    var motherPhone = r.mother_phone || u.mother_phone || '-';
    var email = r.email || u.email || '-';
    var nid = r.nid || u.nid || '-';

    var infoSectionHtml = '';
    if (isProf) {
        infoSectionHtml = `
          <div class="form-pill-row">
            <div class="pill-lbl">কর্মজীবীর পূর্ণ নাম :</div>
            <div class="pill-val">${fullName}</div>
            <div class="pill-right">
              <div class="pill-lbl">মোবাইল নং :</div>
              <div class="pill-val">${phone}</div>
            </div>
          </div>

          <div class="form-pill-row">
            <div class="pill-lbl">কর্মপ্রতিষ্ঠানের নাম:</div>
            <div class="pill-val">${workplaceName}</div>
            <div class="pill-right">
              <div class="pill-lbl">NID নম্বর :</div>
              <div class="pill-val">${nid}</div>
            </div>
          </div>

          <div class="form-pill-row">
            <div class="pill-lbl">ইমেইল ঠিকানা:</div>
            <div class="pill-val">${email}</div>
            <div class="pill-right">
              <div class="pill-lbl">পেশা/টাইপ:</div>
              <div class="pill-val">${r.user_type || u.user_type || 'Working Professional'}</div>
            </div>
          </div>

          ${(fatherName !== '-' || motherName !== '-') ? `
          <div class="form-pill-row">
            <div class="pill-lbl">পিতার নাম:</div>
            <div class="pill-val">${fatherName}</div>
            <div class="pill-right">
              <div class="pill-lbl">মাতার নাম:</div>
              <div class="pill-val">${motherName}</div>
            </div>
          </div>
          ` : ''}
        `;
    } else {
        infoSectionHtml = `
          <div class="form-pill-row">
            <div class="pill-lbl">শিক্ষার্থীর পূর্ণ নাম :</div>
            <div class="pill-val">${fullName}</div>
            <div class="pill-right">
              <div class="pill-lbl">মোবাইল নং :</div>
              <div class="pill-val">${phone}</div>
            </div>
          </div>

          <div class="form-pill-row">
            <div class="pill-lbl">অধ্যয়নরত শিক্ষা প্রতিষ্ঠানের নাম:</div>
            <div class="pill-val">${institutionName}</div>
          </div>

          <div class="form-pill-row">
            <div class="pill-lbl">পিতার নাম:</div>
            <div class="pill-val">${fatherName}</div>
            <div class="pill-right">
              <div class="pill-lbl">মোবাইল নং :</div>
              <div class="pill-val">${fatherPhone}</div>
            </div>
          </div>

          <div class="form-pill-row">
            <div class="pill-lbl">মাতার নাম:</div>
            <div class="pill-val">${motherName}</div>
            <div class="pill-right">
              <div class="pill-lbl">মোবাইল নং :</div>
              <div class="pill-val">${motherPhone}</div>
            </div>
          </div>
        `;
    }

    var html = `
        <!DOCTYPE html>
        <html lang="bn">
        <head>
          <meta charset="UTF-8">
          <title>${docTitle}</title>
          <style>
            @import url('https://fonts.googleapis.com/css2?family=Tiro+Bangla&family=Hind+Siliguri:wght@400;500;600;700;800&display=swap');
            @page { size: A4 portrait; margin: 5mm; }
            * { box-sizing: border-box; margin: 0; padding: 0; }
            html, body {
              height: 100%; width: 100%; background: #fff;
              font-family: 'Hind Siliguri', 'Tiro Bangla', sans-serif;
              padding: 0; margin: 0; color: #000;
              -webkit-print-color-adjust: exact; print-color-adjust: exact;
            }
            .paper-frame {
              background: linear-gradient(165deg, #fef9e7 0%, #fef3cd 50%, #fef9e7 100%);
              border: 4px solid #27ae60; border-radius: 10px;
              padding: 18px 22px 16px 22px; box-sizing: border-box;
              height: calc(297mm - 10mm); min-height: calc(297mm - 10mm);
              display: flex; flex-direction: column; justify-content: space-between;
              position: relative; page-break-inside: avoid;
            }
            .paper-frame::before {
              content: ''; position: absolute; inset: 4px;
              border: 1.5px solid #f39c12; border-radius: 7px; pointer-events: none;
            }
            .main-content-wrap { display: flex; flex-direction: column; flex-grow: 1; justify-content: space-evenly; }
            .top-header { display: flex; align-items: center; justify-content: center; gap: 18px; margin-bottom: 10px; padding: 0 4px; }
            .logo-wrap img { width: 78px; height: 78px; object-fit: contain; }
            .brand-center { text-align: center; flex: 1; }
            .brand-name { font-size: 46px; font-weight: 900; color: #c0392b; font-family: 'Tiro Bangla', serif; line-height: 1; letter-spacing: 1px; }
            .photo-box { width: 110px; height: 125px; border: 2px solid #2c3e50; border-radius: 4px; display: flex; align-items: center; justify-content: center; overflow: hidden; background: #f0f0f0; flex-shrink: 0; }
            .photo-box img { width: 100%; height: 100%; object-fit: cover; }
            .photo-placeholder { font-size: 11px; color: #888; text-align: center; }
            .address-banner { background: #1a237e; color: #fff; text-align: center; padding: 9px 14px; font-size: 13.5px; font-weight: 600; border-radius: 5px; margin-bottom: 12px; letter-spacing: 0.2px; }
            .room-meta-row { display: flex; gap: 12px; margin-bottom: 12px; }
            .room-meta-box { flex: 1; border: 2px solid #27ae60; border-radius: 6px; padding: 8px 12px; background: #fff; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 5px; }
            .room-meta-box .lbl { color: #1a5c2e; font-weight: 700; white-space: nowrap; }
            .room-meta-box .val { color: #000; font-weight: 800; font-size: 14.5px; }
            .section-title-container { display: flex; align-items: center; justify-content: space-between; border-bottom: 2.5px solid #f39c12; padding-bottom: 3px; margin: 10px 0 8px 0; }
            .title-side-dummy { flex: 1; }
            .section-title-container .section-title { text-align: center; font-size: 21.5px; font-weight: 800; color: #e74c3c; font-family: 'Tiro Bangla', serif; letter-spacing: 0.3px; flex: 2; margin: 0; padding-bottom: 0; border-bottom: none; }
            .booking-date-right-box { flex: 1; text-align: right; font-size: 13px; font-weight: 700; white-space: nowrap; }
            .booking-date-right-box .lbl { color: #1a5c2e; font-weight: 800; }
            .booking-date-right-box .val { color: #000; font-weight: 800; }
            .section-title { text-align: center; font-size: 21.5px; font-weight: 800; color: #e74c3c; font-family: 'Tiro Bangla', serif; border-bottom: 2.5px solid #f39c12; padding-bottom: 3px; margin: 10px 0 8px 0; letter-spacing: 0.3px; }
            .form-pill-row { border: 2px solid #8e44ad; border-radius: 25px; background: #fff; display: flex; overflow: hidden; margin-bottom: 10px; min-height: 42px; align-items: stretch; }
            .pill-lbl { background: #7b1fa2; color: #fff; padding: 9px 18px; font-size: 14px; font-weight: 700; white-space: nowrap; display: flex; align-items: center; border-right: 2px solid #8e44ad; flex-shrink: 0; }
            .pill-val { padding: 9px 18px; font-size: 14.5px; font-weight: 600; color: #111; flex-grow: 1; display: flex; align-items: center; }
            .pill-right { border-left: 2px solid #8e44ad; display: flex; align-items: stretch; flex-shrink: 0; }
            .address-row { border: 2px solid #8e44ad; border-radius: 25px; background: #fff; display: flex; flex-wrap: wrap; padding: 11px 20px; font-size: 14px; font-weight: 600; gap: 10px 30px; margin-bottom: 10px; align-items: center; min-height: 42px; }
            .akey { color: #7b1fa2; font-weight: 700; }
            .rules-box { background: #fffde7; border: 2px solid #f48fb1; border-radius: 12px; padding: 14px 20px; margin-top: 4px; margin-bottom: 10px; }
            .rules-list { list-style: none; padding: 0; margin: 0; }
            .rules-list li { font-size: 13px; font-weight: 600; color: #1a1a1a; line-height: 1.8; border-bottom: 1px dashed #f8bbd0; padding-bottom: 4px; margin-bottom: 4px; }
            .rules-list li:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
            .signature-row { display: flex; justify-content: space-between; align-items: flex-end; margin-top: auto; padding: 16px 36px 8px 36px; }
            .sig-box { text-align: center; min-width: 150px; }
            .sig-line { border-top: 1.5px solid #2c3e50; margin-bottom: 5px; }
            .sig-text { font-size: 14px; font-weight: 700; color: #1a1a1a; }
            @media print {
              html, body { height: 100% !important; margin: 0 !important; padding: 0 !important; background: #fff !important; }
              .paper-frame { height: calc(297mm - 10mm) !important; min-height: calc(297mm - 10mm) !important; border-radius: 0; box-sizing: border-box; page-break-inside: avoid !important; }
            }
          </style>
        </head>
        <body>
          <div class="paper-frame">
            <div class="main-content-wrap">
              <div class="top-header">
                <div class="logo-wrap">
                  <img src="${logoUrl}" alt="Logo" onerror="this.style.display='none'">
                </div>
                <div class="brand-center">
                  <div class="brand-name">টি এস এস ভিলা</div>
                </div>
                <div class="photo-box">
                  ${userImgUrl ? `<img src="${userImgUrl}" alt="Photo">` : `<div class="photo-placeholder">ছবি<br>Photo</div>`}
                </div>
              </div>
              <div class="address-banner">
                কলেজ রোড , নেসকো গেট সংলগ্ন , রংপুর &nbsp;|&nbsp; প্রয়োজনে: ০১৯৭৭২৭০৯২০ &nbsp;|&nbsp; Gmail: tssvilla2026@gmail.com
              </div>
              <div class="room-meta-row">
                <div class="room-meta-box"><span class="lbl">রুম নং:</span>&nbsp;<span class="val">${roomNo}</span></div>
                <div class="room-meta-box"><span class="lbl">ব্লক নং:</span>&nbsp;<span class="val">${seatNo}</span></div>
                <div class="room-meta-box"><span class="lbl">ফ্লোর নং:</span>&nbsp;<span class="val">${floorNo}</span></div>
              </div>
              <div class="section-title-container">
                <div class="title-side-dummy"></div>
                <div class="section-title">${sectionTitleText}</div>
                <div class="booking-date-right-box">
                  <span class="lbl">বুকিং তারিখ:</span> <span class="val">${bookingDate}</span>
                </div>
              </div>
              ${infoSectionHtml}
              <div class="section-title">স্থায়ী ঠিকানা</div>
              <div class="address-row">
                <span><span class="akey">গ্রাম/রাস্তা:</span> ${address}</span>
                <span><span class="akey">থানা:</span> ${thanaName}</span>
                <span><span class="akey">উপজেলা:</span> ${thanaName}</span>
                <span><span class="akey">জেলা:</span> ${districtName}</span>
              </div>
              <div class="section-title">নিয়মাবলী</div>
              <div class="rules-box">
                ${isProf ? `
                <ul class="rules-list">
                  <li>* মেসের ভাড়া ০৭ তারিখের মধ্যে পরিশোধ করতে হবে।</li>
                  <li>* মেস ছাড়লে ০২ মাস পূর্বেই মেস কর্তৃপক্ষকে জানাতে হবে। অন্যথায় দুই মাসের ভাড়া দিয়ে মেস ছাড়তে হবে।</li>
                  <li>* রুম চেঞ্জ করতে চাইলে ৫০০ টাকা জরিমানা প্রদান করতে হবে।</li>
                  <li>* মেসের নিয়ম-কানুন মেনে চলতে হবে। কারো বিরুদ্ধে কোনো অভিযোগ আসলে এবং তা প্রমাণিত হলে সিট বাতিলসহ যেকোনো ব্যবস্থা নেয়ার অধিকার কর্তৃপক্ষ রাখে।</li>
                </ul>
                ` : `
                <ul class="rules-list">
                  <li>* মেসের ভাড়া ০৭ তারিখের মধ্যে পরিশোধ করতে হবে।</li>
                  <li>* মেস ছাড়লে ০২ মাস পূর্বেই মেস কর্তৃপক্ষকে জানাতে হবে। অন্যথায় দুই মাসের ভাড়া দিয়ে মেস ছাড়তে হবে।</li>
                  <li>* মাগরিবের আযানের পর মেসের বাহিরে থাকলে অভিভাবককে জানিয়ে দিতে হবে।</li>
                  <li>* রুম চেঞ্জ করতে চাইলে ৫০০ টাকা জরিমানা প্রদান করতে হবে।</li>
                  <li>* মেসের নিয়ম-কানুন মেনে চলতে হবে। কারো বিরুদ্ধে কোনো অভিযোগ আসলে এবং তা প্রমাণিত হলে সিট বাতিলসহ যেকোনো ব্যবস্থা নেয়ার অধিকার কর্তৃপক্ষ রাখে।</li>
                </ul>
                `}
              </div>
            </div>
            <div class="signature-row">
              <div class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-text">অনুমোদিত স্বাক্ষর</div>
              </div>
              <div class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-text">${signatureLabelText}</div>
              </div>
            </div>
          </div>
        </body>
        </html>
    `;

    var win = window.open('', '_blank');
    if (win) {
        win.document.write(html);
        win.document.close();
        setTimeout(function() {
            win.focus();
            win.print();
        }, 400);
    } else {
        alert('Please allow popups for this site to print document.');
    }
}

function printResidentIdCard() {
    var r = window.userBookingData;
    var u = window.userProfileData || {};
    if (!r) {
        alert('No active booking record found!');
        return;
    }

    var logoUrl = window.location.origin + '/logo/logoimage (2).png';
    var userImgUrl = r.image ? (window.location.origin + '/bookingsimage/' + r.image) : (u.avatar_url || '');

    var roomItems = [];
    if (typeof r.floor_number_room_number_roomprice === 'string') {
        try { roomItems = JSON.parse(r.floor_number_room_number_roomprice); } catch(e){}
    } else if (Array.isArray(r.floor_number_room_number_roomprice)) {
        roomItems = r.floor_number_room_number_roomprice;
    }

    function getRoomNo(str) {
        if (!str) return '-';
        var parts = String(str).split('-');
        return parts[0] || str;
    }
    function getSeatNo(str) {
        if (!str) return '-';
        var parts = String(str).split('-');
        return parts.length > 1 ? parts.slice(1).join('-') : '-';
    }

    var roomNo = roomItems.length
        ? roomItems.map(function(i){ return getRoomNo(i.roomnumber); }).join(', ')
        : (getRoomNo(r.roomnumber) || r.room_number || '-');
    var seatNo = roomItems.length
        ? roomItems.map(function(i){ return getSeatNo(i.roomnumber); }).join(', ')
        : (getSeatNo(r.roomnumber) || '-');
    var floorNo = roomItems.length
        ? Array.from(new Set(roomItems.map(function(i){ return i.floornumber; }))).filter(Boolean).join(', ')
        : (r.floornumber || '-');

    var fullName = r.full_name || u.name || '-';
    var phone = r.phone || u.phone || '-';
    var nid = r.nid || u.nid || '-';
    var userType = r.user_type || u.user_type || 'Student';

    var qrText = encodeURIComponent(`TSS VILLA | Name: ${fullName} | Phone: ${phone} | Room: ${roomNo} | Seat: ${seatNo}`);
    var qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=110x110&data=${qrText}`;

    var html = `
        <!DOCTYPE html>
        <html lang="bn">
        <head>
          <meta charset="UTF-8">
          <title>Resident ID Card - ${fullName}</title>
          <style>
            @import url('https://fonts.googleapis.com/css2?family=Tiro+Bangla&family=Hind+Siliguri:wght@400;500;600;700;800&display=swap');
            @page { size: A4 portrait; margin: 10mm; }
            * { box-sizing: border-box; margin: 0; padding: 0; }
            body {
              background: #f4f6f9;
              font-family: 'Hind Siliguri', 'Tiro Bangla', sans-serif;
              display: flex; justify-content: center; align-items: center;
              min-height: 100vh; padding: 20px;
            }
            .id-card-frame {
              width: 86mm; height: 135mm; background: #ffffff;
              border: 3px solid #1a237e; border-radius: 12px;
              box-shadow: 0 8px 25px rgba(0,0,0,0.15); overflow: hidden;
              position: relative; display: flex; flex-direction: column;
              justify-content: space-between; page-break-inside: avoid;
            }
            .id-header { background: linear-gradient(135deg, #1a237e 0%, #283593 100%); color: #fff; padding: 10px 8px; text-align: center; }
            .id-header img { width: 42px; height: 42px; object-fit: contain; margin-bottom: 2px; }
            .id-header h2 { font-size: 20px; font-weight: 800; font-family: 'Tiro Bangla', serif; color: #fff; margin: 0; line-height: 1.1; }
            .id-header p { font-size: 10px; color: #e0e0e0; margin: 0; letter-spacing: 0.5px; }
            .id-body { padding: 10px 12px; text-align: center; flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: space-between; }
            .photo-container { width: 90px; height: 105px; border: 3px solid #1a237e; border-radius: 8px; overflow: hidden; background: #eef2f5; margin-bottom: 6px; box-shadow: 0 3px 8px rgba(0,0,0,0.1); }
            .photo-container img { width: 100%; height: 100%; object-fit: cover; }
            .photo-placeholder { font-size: 10px; color: #888; display: flex; height: 100%; align-items: center; justify-content: center; text-align: center; }
            .resident-name { font-size: 16px; font-weight: 800; color: #1a237e; margin-bottom: 2px; line-height: 1.2; }
            .type-badge { display: inline-block; background: #e8eaf6; color: #1a237e; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 12px; margin-bottom: 8px; border: 1px solid #c5cae9; }
            .meta-grid { width: 100%; background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 6px; padding: 6px 8px; font-size: 11px; margin-bottom: 6px; text-align: left; }
            .meta-row { display: flex; justify-content: space-between; margin-bottom: 3px; }
            .meta-row:last-child { margin-bottom: 0; }
            .m-lbl { color: #555; font-weight: 600; }
            .m-val { color: #000; font-weight: 700; }
            .room-badges { display: flex; gap: 4px; width: 100%; justify-content: center; margin-bottom: 6px; }
            .r-badge { flex: 1; background: #1a237e; color: #fff; font-size: 10px; font-weight: 700; padding: 4px 2px; border-radius: 4px; text-align: center; }
            .r-badge.room { background: #2e7d32; }
            .r-badge.seat { background: #c62828; }
            .qr-section { display: flex; align-items: center; justify-content: space-between; width: 100%; padding: 0 4px; }
            .qr-img { width: 45px; height: 45px; }
            .sig-box { text-align: center; }
            .sig-line { border-top: 1px solid #333; width: 70px; margin-bottom: 2px; }
            .sig-lbl { font-size: 9px; font-weight: 700; color: #333; }
            .id-footer { background: #1a237e; color: #fff; font-size: 8.5px; text-align: center; padding: 5px; font-weight: 600; }
            @media print {
              body { background: #fff !important; padding: 0 !important; }
              .id-card-frame { box-shadow: none !important; margin: auto; }
            }
          </style>
        </head>
        <body>
          <div class="id-card-frame">
            <div class="id-header">
              <img src="${logoUrl}" alt="Logo" onerror="this.style.display='none'">
              <h2>টি এস এস ভিলা</h2>
              <p>TSS VILLA RESIDENT ID CARD</p>
            </div>
            <div class="id-body">
              <div class="photo-container">
                ${userImgUrl ? `<img src="${userImgUrl}" alt="Photo">` : `<div class="photo-placeholder">PHOTO</div>`}
              </div>
              <div class="resident-name">${fullName}</div>
              <div class="type-badge">${userType}</div>
              <div class="room-badges">
                <div class="r-badge">ফ্লোর: ${floorNo}</div>
                <div class="r-badge room">রুম: ${roomNo}</div>
                <div class="r-badge seat">সিট: ${seatNo}</div>
              </div>
              <div class="meta-grid">
                <div class="meta-row"><span class="m-lbl">মোবাইল:</span><span class="m-val">${phone}</span></div>
                <div class="meta-row"><span class="m-lbl">NID:</span><span class="m-val">${nid}</span></div>
              </div>
              <div class="qr-section">
                <img src="${qrUrl}" alt="QR" class="qr-img">
                <div class="sig-box">
                  <div class="sig-line"></div>
                  <div class="sig-lbl">অনুমোদিত স্বাক্ষর</div>
                </div>
              </div>
            </div>
            <div class="id-footer">
              কলেজ রোড , নেসকো গেট সংলগ্ন , রংপুর | হেল্পলাইন: ০১৯৭৭২৭০৯২০
            </div>
          </div>
        </body>
        </html>
    `;

    var win = window.open('', '_blank');
    if (win) {
        win.document.write(html);
        win.document.close();
        setTimeout(function() {
            win.focus();
            win.print();
        }, 400);
    }
}
</script>
@endsection