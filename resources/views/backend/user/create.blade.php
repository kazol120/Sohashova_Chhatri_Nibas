@extends('backend.layouts.app')
@section("title") | {{$page_title}} @endsection

@push('style')
    <link rel="stylesheet" href="{{asset('backend')}}/vendor/libs/select2/select2.css" />
    <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
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
                                      <a href="{{route('user.index')}}" class="btn btn-primary create-new waves-effect waves-light">
                                        <span>
                                            <i class="ti ti-arrow-left me-1"></i>
                                            <span class="d-none d-sm-inline-block">Back</span>

                                        </span>
                                      </a>
                                  </div>
                              </div>
                          </div>
                          <form action="{{route('user.store')}}" method="post">
                              @csrf
                              <div class="card-body">
                                  @if ($errors->any())
                                      @foreach ($errors->all() as $error)
                                          <div class="alert alert-warning" role="alert">{{ $error }}</div>
                                      @endforeach
                                  @endif

                                  <div class="mb-3">
                                      <label for="permission" class="form-label">Name <code>*</code></label>
                                      <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"  id="name" placeholder="User Name" required>
                                      @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                      @enderror
                                  </div>
                                  <div class="mb-3">
                                      <label for="permission" class="form-label">Phone <code>*</code></label>
                                      <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"  id="phone" placeholder="User Phone" required>
                                      @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                      @enderror
                                  </div>
                                  <div class="mb-3">
                                      <label for="permission" class="form-label">Email <code>*</code></label>
                                      <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"  id="email" placeholder="User Email" required>
                                      @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                      @enderror
                                      <code>This email is login username and password is 1 to 8</code>
                                  </div>
                                  <div class="mb-3">
                                      <label for="select2Basic" class="form-label">Roles </label>
                                      <select name="roles" id="rolesSelect" class="select2 form-select form-select-lg @error('roles') is-invalid @enderror" data-allow-clear="true">
                                          <option value="">Choose One</option>
                                          @foreach($roles as $role)
                                              <option value="{{$role->name}}">{{$role->name}}</option>
                                          @endforeach
                                      </select>
                                      @error('roles')
                                      <div class="invalid-feedback">{{ $message }}</div>
                                      @enderror
                                  </div>
                                  <div class="mb-3">
                                      <label for="select2Basic" class="form-label">Permissions</label>
                                      <select name="permissions[]" multiple id="permissionsSelect" class="select2 form-select form-select-lg @error('permissions') is-invalid @enderror" data-allow-clear="true">
                                          <option value="">Choose One</option>
                                          @foreach($permissions as $permission)
                                              <option value="{{$permission->name}}">{{$permission->name}}</option>
                                          @endforeach
                                      </select>
                                      @error('permissions')
                                      <div class="invalid-feedback">{{ $message }}</div>
                                      @enderror
                                  </div>
                                  <div class="mb-3">
                                    <label class="text-bold text-uppercase">Status</label>
                                    <input
                                        data-toggle="toggle"
                                        data-onstyle="success"
                                        data-offstyle="danger"
                                        data-on="Active"
                                        data-off="Deactivated"
                                        data-width="100%"
                                        type="checkbox"
                                        name="status"
                                        id="statusToggle"
                                    >
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
    <script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#rolesSelect').select2({
                placeholder: 'Select a role',
                allowClear: true
            });

            $('#permissionsSelect').select2({
                placeholder: 'Select permissions',
                allowClear: true
            });
        });

    </script>
@endpush
