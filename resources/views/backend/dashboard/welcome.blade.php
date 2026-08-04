@extends('backend.layouts.app')
@section("title") | {{$page_title}} @endsection
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row g-6 mb-6">
        
        <!-- Floor -->
        @role('admin')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ url('floor') }}">
                <div class="card">
                    <div class="card-body">
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
                    </div>
                </div>
            </a>
        </div>
        @endrole

        <!-- Total Rooms -->
        @role('admin')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ url('room-list') }}">
                <div class="card">
                    <div class="card-body">
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
                    </div>
                </div>
            </a>
        </div>
        @endrole

        <!-- Total Seats -->
        @role('admin|staffs')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('room-list.index') }}">
                <div class="card">
                    <div class="card-body">
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
                    </div>
                </div>
            </a>
        </div>
        @endrole

        <!-- Available Seats -->
        @role('admin|staffs')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('room-list.index') }}">
                <div class="card">
                    <div class="card-body">
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
                    </div>
                </div>
            </a>
        </div>
        @endrole

        <!-- Booked Seats -->
        @unlessrole('staffs')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ url('room-booking-history') }}">
                <div class="card">
                    <div class="card-body">
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
                    </div>
                </div>
            </a>
        </div>
        @endunlessrole

        <!-- Staffs -->
        @hasanyrole('admin|staffs')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ url('staffs') }}">
                <div class="card">
                    <div class="card-body">
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
                    </div>
                </div>
            </a>
        </div>
        @endhasanyrole

        <!-- Release History -->
        @role('admin')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('room-release.history') }}">
                <div class="card">
                    <div class="card-body">
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
                    </div>
                </div>
            </a>
        </div>
        @endrole

        <!-- This Month Rent Collection -->
        @role('admin|staffs')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('monthly-payments.index') }}">
                <div class="card">
                    <div class="card-body">
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
                    </div>
                </div>
            </a>
        </div>
        @endrole

        <!-- This Month Due Rent -->
        @role('admin|staffs')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('monthly-payments.index') }}">
                <div class="card">
                    <div class="card-body">
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
                    </div>
                </div>
            </a>
        </div>
        @endrole

        <!-- Today Meal Entries -->
        @role('admin|staffs')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('meals.index') }}">
                <div class="card">
                    <div class="card-body">
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
                    </div>
                </div>
            </a>
        </div>
        @endrole

        <!-- Today Expense -->
        @role('admin')
        <div class="col-sm-6 col-xl-3">
           <a href="{{ url('today-expense') }}">
                <div class="card">
                    <div class="card-body">
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
                    </div>
                </div>
            </a>
        </div>
        @endrole

        <!-- Today Product Distribution -->
        @role('admin')
        <div class="col-sm-6 col-xl-3">
           <a href="{{ url('today-product-distribution') }}">
                <div class="card">
                    <div class="card-body">
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
                    </div>
                </div>
            </a>
        </div>
        @endrole

    </div>
</div>
@endsection

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