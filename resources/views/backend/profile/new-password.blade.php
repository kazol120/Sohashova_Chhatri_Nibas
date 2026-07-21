@extends('backend.layouts.app')
@section("title") | {{$page_title}} @endsection

@push('style')
    <script src="{{url('/')}}/backend/vendor/css/pages/page-profile.css"></script>
@endpush
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="py-3 mb-4"> {{$page_title}}</h4>
        <!-- Header -->
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="user-profile-header-banner">
                        <img id="coverImagePreview" style="width: 995px; height: 250px" src="{{asset("storage/user/$user->cover_image")}}" alt="Banner image" class="rounded-top" />
                    </div>
                    <div class="user-profile-header d-flex flex-column flex-sm-row text-sm-start text-center mb-4">
                        <div class="flex-shrink-0 mt-n2 mx-sm-0 mx-auto">
                            <img id="imagePreview" style="width: 110px; height: 110px" src="{{asset("storage/user/$user->user_image")}}" alt="user image" class="d-block h-auto ms-0 ms-sm-4 rounded user-profile-img" />
                        </div>
                        <div class="flex-grow-1 mt-3 mt-sm-5">
                            <div
                                class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-4 flex-md-row flex-column gap-4">
                                <div class="user-profile-info">
                                    <h4>{{$user->name}}</h4>
                                    <ul
                                        class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-2">
                                        <li class="list-inline-item d-flex gap-1">
                                            <i class="ti ti-color-swatch"></i> {{ $user->roles->pluck('name')->implode(', ') }}
                                        </li>
                                        <li class="list-inline-item d-flex gap-1"><i class="ti ti-map-pin"></i> {{$user->address}}</li>
                                        <li class="list-inline-item d-flex gap-1">
                                            <i class="ti ti-calendar"></i> {{\Carbon\Carbon::parse($user->created_at)->format('d M,Y')}}
                                        </li>
                                    </ul>
                                </div>
                                <button class="btn btn-{{$user->status == 1 ? 'primary' : 'warning'}}">
                                    <i class="fas fa-{{$user->status == 1 ? 'check' : 'times'}} me-1"></i>{{$user->status == 1 ? 'Active' : 'Deactivated'}}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/ Header -->

        <!-- User Profile Content -->
        <div class="row">
            <div class="col-xl-4 col-lg-5 col-md-5">
                <!-- About User -->
                <div class="card mb-4">
                    <div class="card-body">
                        <small class="card-text text-uppercase">About</small>
                        <ul class="list-unstyled mb-4 mt-3">
                            <li class="d-flex align-items-center mb-3">
                                <i class="ti ti-user text-heading"></i
                                ><span class="fw-medium mx-2 text-heading">Full Name:</span> <span>{{$user->name}}</span>
                            </li>
                            <li class="d-flex align-items-center mb-3">
                                <i class="fas fa-{{$user->status == 1 ? 'check' : 'times'}} text-heading"></i
                                ><span class="fw-medium mx-2 text-heading">Status:</span> <span>{{$user->status == 1 ? 'Active' : 'Deactivated'}}</span>
                            </li>
                            <li class="d-flex align-items-center mb-3">
                                <i class="ti ti-crown text-heading"></i
                                ><span class="fw-medium mx-2 text-heading">Role:</span> <span>{{ $user->roles->pluck('name')->implode(', ') }}</span>
                            </li>
                            <li class="d-flex align-items-center mb-3">
                                <i class="ti ti-map-pin text-heading"></i
                                ><span class="fw-medium mx-2 text-heading">Address:</span> <span>{{$user->address}}</span>
                            </li>
                        </ul>
                        <small class="card-text text-uppercase">Contacts</small>
                        <ul class="list-unstyled mb-4 mt-3">
                            <li class="d-flex align-items-center mb-3">
                                <i class="ti ti-phone-call"></i><span class="fw-medium mx-2 text-heading">Contact:</span>
                                <span>{{$user->phone}}</span>
                            </li>
                            <li class="d-flex align-items-center mb-3">
                                <i class="ti ti-mail"></i><span class="fw-medium mx-2 text-heading">Email:</span>
                                <span>{{$user->email}}</span>
                            </li>
                        </ul>

                    </div>
                </div>
            </div>
            <div class="col-xl-8 col-lg-7 col-md-7">
                <!-- Activity Timeline -->
                <div class="card mb-4">
                    <h5 class="card-header">Change Password</h5>
                    <div class="card-body">
                        <form action="{{route('password-update')}}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">New Password</label>
                                <input type="password" class="form-control  @error('new_password') is-invalid @enderror" name="new_password" id="new_password" placeholder="New Password">
                                @error('new_password')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="name" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control @error('confirm_password') is-invalid @enderror" name="confirm_password" id="confirm_password" placeholder="Confirm Password">
                                @error('confirm_password')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary waves-effect waves-light">Submit</button>
                        </form>
                    </div>
                </div>
                <!--/ Activity Timeline -->
            </div>
        </div>
        <!--/ User Profile Content -->
    </div>
    <!-- / Content -->
@endsection
@push('script')
    <script src="{{url('/')}}/backend/js/pages-profile.js"></script>

@endpush
