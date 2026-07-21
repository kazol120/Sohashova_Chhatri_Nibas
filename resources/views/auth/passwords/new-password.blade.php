@extends('layouts.app')
@section('content')
    <!-- Login -->
    <div class="card">
        <div class="card-body">
            <!-- Logo -->
            <div class="app-brand justify-content-center mb-4 mt-2">
                <a href="{{route('home')}}" class="app-brand-link gap-2">
                    <span class="app-brand-text demo text-body fw-bold ms-1">Vuexy</span>
                </a>
            </div>
            <!-- /Logo -->
            <h4 class="mb-1 pt-2">Change your password</h4>
            <form class="mb-3" action="{{route('update-password')}}" method="post">
                @csrf
                <div class="mb-3 form-password-toggle">
                    <div class="d-flex justify-content-between">
                        <label class="form-label" for="password">Password</label>
                    </div>
                    <div class="input-group input-group-merge">
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password"/>
                        @error('password')
                        <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
                <div class="mb-3 form-password-toggle">
                    <div class="d-flex justify-content-between">
                        <label class="form-label" for="password">Confirm Password</label>
                    </div>
                    <div class="input-group input-group-merge">
                        <input type="password" class="form-control @error('confirm_password') is-invalid @enderror" id="confirm_password" name="confirm_password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password"/>
                        @error('confirm_password')
                        <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary d-grid w-100">Submit</button>
                </div>
            </form>
            <p class="text-center">
                <span>New on our platform?</span>
                <a href="{{route('register')}}">
                    <span>Create an account</span>
                </a>
            </p>

        </div>
    </div>
    <!-- /Register -->
@endsection
