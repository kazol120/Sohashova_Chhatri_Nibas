@extends('backend.layouts.app')
@section("title") | {{$page_title}} @endsection

@push('style')
    <link rel="stylesheet" href="{{asset('backend')}}/vendor/libs/select2/select2.css" />
@endpush
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
              <!-- Basic Layout -->
              <div class="row">
                  <div class="col-md-12">
                      <div class="card mb-4">
                          <div class="card-header d-flex justify-content-between align-items-center">
                              <!-- Title -->
                              <h5 class="card-title mb-0">{{$page_title}}</h5>
                              <!-- Button Group -->
                              <div class="dt-action-buttons">
                                  <div class="dt-buttons btn-group">
                                      <!-- Create Button -->
                                      <a href="{{route('role.index')}}" class="btn btn-primary create-new waves-effect waves-light">
                                        <span>
                                            <i class="ti ti-arrow-left me-1"></i>
                                            <span class="d-none d-sm-inline-block">Back</span>

                                        </span>
                                      </a>
                                  </div>
                              </div>
                          </div>
                          <form action="{{route('role.store')}}" method="post">
                              @csrf
                              <div class="card-body">
                                  @if ($errors->any())
                                      @foreach ($errors->all() as $error)
                                          <div class="alert alert-warning" role="alert">{{ $error }}</div>
                                      @endforeach
                                  @endif

                                  <div class="mb-3">
                                      <label for="permission" class="form-label">Role Name <code>*</code></label>
                                      <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"  id="permission" placeholder="Role Name" required>
                                      @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                      @enderror
                                  </div>
                                  <div class="row mt-3">
                                      <div class="d-grid gap-2 col-lg-4 mx-auto">
                                          <button class="btn btn-primary btn-lg waves-effect waves-light" type="submit">Save</button>
                                      </div>
                                  </div>
                              </div>
                          </form>
                      </div>
                  </div>
            </div>
        </div>

@endsection
@push('script')
    <script src="{{asset('backend')}}/vendor/libs/select2/select2.js"></script>
    <script src="{{asset('backend')}}/js/forms-selects.js"></script>
@endpush
