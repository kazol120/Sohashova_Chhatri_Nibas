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
{{--                                <button onclick="changeStatus({{$user->id}})" class="btn btn-{{$user->status == 1 ? 'primary' : 'warning'}}">--}}
{{--                                    <i class="fas fa-{{$user->status == 1 ? 'check' : 'times'}} me-1"></i>{{$user->status == 1 ? 'Active' : 'Deactivated'}}--}}
{{--                                </button>--}}
{{--                                profile-status-update--}}
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
                            <div class="mb-3">
                                <label for="coverImage" class="form-label">Cover Image</label>
                                <input type="file" class="form-control @error('cover_image') is-invalid @enderror" name="cover_image" id="coverImage">
                                <small>Image Size 1693x376</small>
                            </div>
                              @error("cover_image")
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
    <!-- status change modal -->
    <!-- Modal -->
    <div class="modal modal-top fade" id="modalTop" tabindex="-1">
        <div class="modal-dialog modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTopTitle">Change Status?</h5>
                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h4>Are you sure to change status</h4>
            </div>
            <div class="modal-footer">
                <form action="{{route('user-status-update')}}" method="post">
                    @csrf
                    <input type="hidden" name="user_id" id="userId">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                        NO
                    </button>
                    <button type="submit" class="btn btn-primary">YES!</button>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script src="{{url('/')}}/backend/js/pages-profile.js"></script>
    <script>
        document.getElementById('userImage').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('imagePreview');
            if (file) {
            const reader = new FileReader();

            reader.onload = function(e) {
                const img = new Image();
                img.src = e.target.result;

                img.onload = function() {
                    // Create a canvas element to resize the image
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');

                    // Set desired width and height
                    const width = 110;
                    const height = 110;
                    canvas.width = width;
                    canvas.height = height;

                    // Draw the image onto the canvas, resizing it
                    ctx.drawImage(img, 0, 0, width, height);

                    // Convert the canvas content to a data URL and set it as the preview source
                    preview.src = canvas.toDataURL('image/png');
                };
            };

            reader.readAsDataURL(file); // Read the file as a data URL
        }
        });
    </script>
    <script>
        document.getElementById('coverImage').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('coverImagePreview');

        if (file) {
            const reader = new FileReader();

            reader.onload = function(e) {
                const img = new Image();
                img.src = e.target.result;

                img.onload = function() {
                    // Create a canvas element to resize the image
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');

                    // Set desired width and height
                    const width = 995;
                    const height = 250;
                    canvas.width = width;
                    canvas.height = height;

                    // Draw the image onto the canvas, resizing it
                    ctx.drawImage(img, 0, 0, width, height);

                    // Convert the canvas content to a data URL and set it as the preview source
                    preview.src = canvas.toDataURL('image/png');
                };
            };

            reader.readAsDataURL(file); // Read the file as a data URL
        }
    });
</script>
    <script>
        function changeStatus($userId)
        {
            $('#modalTop').modal('show');
            $('#userId').val($userId)
        }
    </script>
@endpush
