@extends('layouts.app')
@section('content')
@if (\Session::has('success'))
    <div class="alert alert-success">
        <ul>
            <li>{!! \Session::get('success') !!}</li>
        </ul>
    </div>
@endif
    <div class="container-xxl flex-grow-1 container-p-y">
              <!-- Basic Layout -->
              <div class="row">
                <div class="col-xl mb-12">
                  <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                      <h5 class="mb-0">Create project form</h5>
                      <small class="text-muted float-end">Default label</small>
                    </div>
                    <div class="card-body">
                      <form action="{{ route('project.store') }}" method="POST">
                         @csrf
                        <div class="mb-12">
                          <label class="form-label" for="basic-default-fullname">project Name</label>
                          <input type="text" class="form-control"  name="projectname" placeholder="Name">
                        </div>
                
                        <div class="mb-12">
                          <label class="form-label" for="client-select">Client</label>
                          <select class="form-control" name="client_id">
                              <option value="">Select Client</option>
                              @foreach($clientselect as $selectlist)
                                  <option value="{{ $selectlist->id }}">{{ $selectlist->name }}</option>
                              @endforeach
                          </select>
                      </div>

        
                  
                        <button type="submit" class="btn btn-primary">Submit</button>
                      </form>
                    </div>
                  </div>
                </div>
            </div>
        </div>

@endsection