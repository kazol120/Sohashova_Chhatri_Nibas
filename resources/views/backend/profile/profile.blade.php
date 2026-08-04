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
                    <div class="user-profile-header d-flex flex-column flex-sm-row text-sm-start text-center p-4">
                        <div class="flex-shrink-0 mx-sm-0 mx-auto">
                            <img id="imagePreview" style="width: 110px; height: 110px; object-fit: cover;" src="{{ $user->avatar_url }}" alt="user image" class="d-block h-auto rounded-circle user-profile-img border border-3 border-primary" />
                        </div>
                        <div class="flex-grow-1 mt-3 mt-sm-2 ms-sm-4">
                            <div class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start flex-md-row flex-column gap-4">
                                <div class="user-profile-info">
                                    <h4 class="mb-2 fw-bold">{{$user->name}}</h4>
                                    <ul class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-3">
                                        <li class="list-inline-item d-flex gap-1">
                                            <i class="ti ti-color-swatch text-primary"></i> {{ $user->roles->pluck('name')->implode(', ') }}
                                        </li>
                                        <li class="list-inline-item d-flex gap-1"><i class="ti ti-map-pin text-danger"></i> {{$user->address}}</li>
                                        <li class="list-inline-item d-flex gap-1">
                                            <i class="ti ti-calendar text-info"></i> {{\Carbon\Carbon::parse($user->created_at)->format('d M, Y')}}
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
                                <i class="ti ti-user text-heading"></i>
                                <span class="fw-medium mx-2 text-heading">Full Name:</span> <span>{{$user->name}}</span>
                            </li>
                            <li class="d-flex align-items-center mb-3">
                                <i class="fas fa-{{$user->status == 1 ? 'check' : 'times'}} text-heading"></i>
                                <span class="fw-medium mx-2 text-heading">Status:</span> <span>{{$user->status == 1 ? 'Active' : 'Deactivated'}}</span>
                            </li>
                            <li class="d-flex align-items-center mb-3">
                                <i class="ti ti-crown text-heading"></i>
                                <span class="fw-medium mx-2 text-heading">Role:</span> <span>{{ $user->roles->pluck('name')->implode(', ') }}</span>
                            </li>
                            <li class="d-flex align-items-center mb-3">
                                <i class="ti ti-map-pin text-heading"></i>
                                <span class="fw-medium mx-2 text-heading">Address:</span> <span>{{$user->address}}</span>
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
                
                @hasanyrole('admin|staffs')
                <!-- ADMIN / STAFF PROFILE UPDATE FORM -->
                <div class="card mb-4">
                    <h5 class="card-header">Profile Information Update</h5>
                    <div class="card-body">
                        <form action="{{route('profile')}}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">Name <code>*</code></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" required name="name" value="{{$user->name}}" id="name" placeholder="Name">
                            </div>
                            @error("name")
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone <code>*</code></label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" required name="phone" value="{{$user->phone}}" id="phone" placeholder="Phone">
                            </div>
                            @error("phone")
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <code>*</code></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" required name="email" value="{{$user->email}}" id="email" placeholder="Email">
                            </div>
                            @error("email")
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="mb-3">
                                <label for="address" class="form-label">Address <code>*</code></label>
                                <input type="text" class="form-control @error('address') is-invalid @enderror" required name="address" value="{{$user->address}}" id="address" placeholder="Address">
                            </div>
                            @error("address")
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="mb-3">
                                <label for="userImage" class="form-label">Profile Image</label>
                                <input type="file" class="form-control @error('user_image') is-invalid @enderror" name="user_image" id="userImage">
                                <small>Image Size 100x100</small>
                            </div>
                            @error("user_image")
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <button type="submit" class="btn btn-primary waves-effect waves-light">Submit</button>
                        </form>
                    </div>
                </div>
                @else
                <!-- RESIDENT / STUDENT MULTI-STEP PROFILE WIZARD FORM -->
                <div class="card mb-4 shadow-sm border-0">
                    <div class="card-header bg-label-primary d-flex align-items-center justify-content-between py-3">
                        <h5 class="mb-0 fw-bold text-primary">
                            <i class="fa fa-user-edit me-2"></i>Resident Profile Update
                        </h5>
                        <span class="badge bg-primary rounded-pill">Room Booking Record Sync</span>
                    </div>

                    <!-- Step Wizard Navigation Indicator -->
                    <div class="px-4 pt-3 pb-2 border-bottom bg-light">
                        <div class="d-flex align-items-center justify-content-around">
                            <div id="step-indicator-1" class="step-badge active-step text-primary fw-bold">
                                <span class="badge rounded-circle bg-primary me-2">1</span> Step 1: Personal & Contact Info
                            </div>
                            <div class="text-muted"><i class="fa fa-chevron-right fs-7"></i></div>
                            <div id="step-indicator-2" class="step-badge text-muted fw-semibold">
                                <span class="badge rounded-circle bg-secondary me-2">2</span> Step 2: Guardian & Workplace Details
                            </div>
                        </div>
                    </div>

                    <div class="card-body pt-4">
                        <form action="{{route('profile')}}" method="post" enctype="multipart/form-data" id="residentProfileForm">
                            @csrf
                            
                            <!-- STEP 1: Personal & Contact Details -->
                            <div id="wizard-step-1">
                                <h6 class="text-uppercase fw-bold text-primary mb-3"><i class="fa fa-id-card me-1"></i> Step 1: Personal Information</h6>
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label fw-semibold">Full Name <code>*</code></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" required name="name" value="{{ old('name', $user->name) }}" id="name" placeholder="Full Name">
                                        @error("name") <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="phone" class="form-label fw-semibold">Phone Number <code>*</code></label>
                                        <input type="text" class="form-control @error('phone') is-invalid @enderror" required name="phone" value="{{ old('phone', $user->phone) }}" id="phone" placeholder="Phone Number">
                                        @error("phone") <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="email" class="form-label fw-semibold">Email Address <code>*</code></label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" required name="email" value="{{ old('email', $user->email) }}" id="email" placeholder="Email Address">
                                        @error("email") <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="nid" class="form-label fw-semibold">NID / Birth Certificate No</label>
                                        <input type="text" class="form-control" name="nid" value="{{ old('nid', $booking->nid ?? '') }}" id="nid" placeholder="NID or Birth Reg Number">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="user_type" class="form-label fw-semibold">User Type</label>
                                        <select name="user_type" id="user_type" class="form-select">
                                            <option value="Student" {{ (old('user_type', $booking->user_type ?? '') == 'Student') ? 'selected' : '' }}>Student (শিক্ষার্থী)</option>
                                            <option value="Working Professional" {{ (old('user_type', $booking->user_type ?? '') == 'Working Professional') ? 'selected' : '' }}>Working Professional (চাকুরিজীবী)</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="userImage" class="form-label fw-semibold">Profile Photo</label>
                                        <input type="file" class="form-control" name="user_image" id="userImage">
                                        <small class="text-muted">Max size: 4MB (JPG, PNG, WEBP)</small>
                                    </div>

                                    <div class="col-12">
                                        <label for="address" class="form-label fw-semibold">Present Address</label>
                                        <textarea class="form-control" name="address" id="address" rows="2" placeholder="Full Present Address">{{ old('address', $user->address) }}</textarea>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-4">
                                    <button type="button" class="btn btn-primary px-4 fw-bold waves-effect waves-light" onclick="goToStep(2)">
                                        Next Step (পরবর্তী ধাপ) <i class="fa fa-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- STEP 2: Guardian & Institution Details -->
                            <div id="wizard-step-2" style="display: none;">
                                <h6 class="text-uppercase fw-bold text-success mb-3"><i class="fa fa-users me-1"></i> Step 2: Guardian & Institution / Workplace</h6>
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="father_name" class="form-label fw-semibold">Father's Name</label>
                                        <input type="text" class="form-control" name="father_name" value="{{ old('father_name', $booking->father_name ?? '') }}" id="father_name" placeholder="Father's Name">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="father_phone" class="form-label fw-semibold">Father's Phone Number</label>
                                        <input type="text" class="form-control" name="father_phone" value="{{ old('father_phone', $booking->father_phone ?? '') }}" id="father_phone" placeholder="Father's Phone">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="mother_name" class="form-label fw-semibold">Mother's Name</label>
                                        <input type="text" class="form-control" name="mother_name" value="{{ old('mother_name', $booking->mother_name ?? '') }}" id="mother_name" placeholder="Mother's Name">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="mother_phone" class="form-label fw-semibold">Mother's Phone Number</label>
                                        <input type="text" class="form-control" name="mother_phone" value="{{ old('mother_phone', $booking->mother_phone ?? '') }}" id="mother_phone" placeholder="Mother's Phone">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="institution_name" class="form-label fw-semibold">Institution Name (শিক্ষাপ্রতিষ্ঠান)</label>
                                        <input type="text" class="form-control" name="institution_name" value="{{ old('institution_name', $booking->institution_name ?? '') }}" id="institution_name" placeholder="College / University Name">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="workplace_name" class="form-label fw-semibold">Workplace Name (কর্মস্থল)</label>
                                        <input type="text" class="form-control" name="workplace_name" value="{{ old('workplace_name', $booking->workplace_name ?? '') }}" id="workplace_name" placeholder="Office / Workplace Name">
                                    </div>

                                    <div class="col-12">
                                        <label for="education_and_workplace" class="form-label fw-semibold">Education & Workplace Details</label>
                                        <textarea class="form-control" name="education_and_workplace" id="education_and_workplace" rows="2" placeholder="Educational qualification or job details">{{ old('education_and_workplace', $booking->education_and_workplace ?? '') }}</textarea>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-outline-secondary px-4 fw-bold waves-effect" onclick="goToStep(1)">
                                        <i class="fa fa-arrow-left me-2"></i> Previous (আগের ধাপ)
                                    </button>
                                    <button type="submit" class="btn btn-success px-4 fw-bold waves-effect waves-light">
                                        <i class="fa fa-check-circle me-1"></i> Submit Profile (সংরক্ষণ করুন)
                                    </button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>

                <script>
                function goToStep(step) {
                    if (step === 2) {
                        document.getElementById('wizard-step-1').style.display = 'none';
                        document.getElementById('wizard-step-2').style.display = 'block';
                        
                        document.getElementById('step-indicator-1').classList.remove('text-primary', 'fw-bold');
                        document.getElementById('step-indicator-1').classList.add('text-muted');
                        document.getElementById('step-indicator-1').querySelector('.badge').classList.replace('bg-primary', 'bg-secondary');

                        document.getElementById('step-indicator-2').classList.remove('text-muted');
                        document.getElementById('step-indicator-2').classList.add('text-primary', 'fw-bold');
                        document.getElementById('step-indicator-2').querySelector('.badge').classList.replace('bg-secondary', 'bg-primary');
                    } else {
                        document.getElementById('wizard-step-2').style.display = 'none';
                        document.getElementById('wizard-step-1').style.display = 'block';
                        
                        document.getElementById('step-indicator-2').classList.remove('text-primary', 'fw-bold');
                        document.getElementById('step-indicator-2').classList.add('text-muted');
                        document.getElementById('step-indicator-2').querySelector('.badge').classList.replace('bg-primary', 'bg-secondary');

                        document.getElementById('step-indicator-1').classList.remove('text-muted');
                        document.getElementById('step-indicator-1').classList.add('text-primary', 'fw-bold');
                        document.getElementById('step-indicator-1').querySelector('.badge').classList.replace('bg-secondary', 'bg-primary');
                    }
                }
                </script>
                @endhasanyrole

            </div>
        </div>
        <!--/ User Profile Content -->
    </div>
    <!-- / Content -->
@endsection
