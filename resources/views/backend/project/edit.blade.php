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
                      <h5 class="mb-0">project update</h5>
                      <small class="text-muted float-end">Default label</small>
                    </div>
                    <div class="card-body">
                      <form action="{{ route('project.update', $projectdata->id) }}" method="POST">
                         @csrf
                          @method('PUT')
                        <div class="mb-12">
                          <label class="form-label" for="basic-default-fullname">project name</label>
                          <input type="text" class="form-control"  name="name"  value="{{$projectdata->name}}" placeholder="Name">
                        </div>
                        <button type="submit" class="btn btn-primary">Update</button>
                      </form>
                    </div>
                  </div>
                </div>
            </div>
        </div>

@endsection