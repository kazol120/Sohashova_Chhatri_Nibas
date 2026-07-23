@extends('backend.layouts.app')
@section("title") | {{$page_title}} @endsection
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row g-6 mb-6">
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
                                <small class="mb-0"></small>
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

        @role('admin')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ url('room-list') }}">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Room</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ $roomcount }}</h4>
                                </div>
                                <small class="mb-0"></small>
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
                                <small class="mb-0"></small>
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


        @hasanyrole('admin|HotelGuest')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ url('room-booking-history') }}">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Room Booking History</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ $roombookingcount }}</h4>
                                </div>
                                <small class="mb-0"></small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-success">
                                   <i class="fa fa-calendar"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @endhasanyrole

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
                                <small class="mb-0">Checkout & Release</small>
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

        @role('HotelGuest')
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('dashboard.my-payments') }}">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">My Payments</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">Rent History</h4>
                                </div>
                                <small class="mb-0"></small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-primary">
                                   <i class="fa fa-wallet"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('dashboard.my-meals') }}">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Meal History</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">Meal Records</h4>
                                </div>
                                <small class="mb-0"></small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-success">
                                   <i class="fa fa-utensils"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @endrole

        
        @role('admin')
        <div class="col-sm-6 col-xl-3">
           <a href="{{ url('customer-report') }}?filter=checkout_in">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Today check In </span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ $todayacheackin }}</h4>
                                </div>
                                <small class="mb-0 text-muted">{{ \Carbon\Carbon::today()->format('d M Y') }}</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-success">
                                  <i class="fa fa-calendar-check"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @endrole


        @role('admin')
        <div class="col-sm-6 col-xl-3">
           <a href="{{ url('customer-report') }}?filter=checkout_list">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Today check Out </span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ $todaycheackout }}</h4>
                                </div>
                                <small class="mb-0 text-muted">{{ \Carbon\Carbon::today()->format('d M Y') }}</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-success">
                                  <i class="fa fa-calendar-times"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @endrole

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
}
.card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(99,102,241,0.15);
}
.card:hover h4 {
    color: #6366f1;
    transform: scale(1.1);
    display: inline-block;
    transition: color 0.2s, transform 0.2s;
}
.card:hover .avatar-initial {
    background-color: #6366f1 !important;
    color: white !important;
}

</style>