@extends('layouts.app')
@section('content')
    <!-- Register Card -->
    <div class="card">
        <div class="card-body">
            <!-- Logo -->
            <div class="app-brand justify-content-center mb-4 mt-2">
                <a href="{{route('home')}}l" class="app-brand-link gap-2">
                  <span class="app-brand-logo demo">

                  </span>
                    <span class="app-brand-text demo text-body fw-bold ms-1">Vuexy</span>
                </a>
            </div>
            <!-- /Logo -->
            <h4 class="mb-1 pt-2">Adventure starts here 🚀</h4>
            <p class="mb-4">Make your app management easy and fun!</p>

            <form id="formAuthentication" class="mb-3" method="POST" action="{{ route('register') }}">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="Enter your name" autofocus/>
                    @error('name')
                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="Enter your email" autofocus/>
                    @error('email')
                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="mb-3 form-password-toggle">
                    <label class="form-label" for="password">Password</label>
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
                    <label class="form-label" for="password">Confirm Password</label>
                    <div class="input-group input-group-merge">
                        <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="terms-conditions" name="terms" />
                        <label class="form-check-label" for="terms-conditions">
                            I agree to
                            <a href="javascript:void(0);">privacy policy & terms</a>
                        </label>
                    </div>
                </div>
                <button class="btn btn-primary d-grid w-100">Sign up</button>
            </form>

            <p class="text-center">
                <span>Already have an account?</span>
                <a href="{{route('login')}}">
                    <span>Sign in instead</span>
                </a>
            </p>
        </div>
    </div>
    <!-- Register Card -->

@endsection
