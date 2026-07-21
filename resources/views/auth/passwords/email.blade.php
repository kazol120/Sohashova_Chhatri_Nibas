@extends('layouts.app')
@section('content')
    <!-- Forgot Password -->
    <div class="card">
        <div class="card-body">
            <!-- Logo -->
            <div class="app-brand justify-content-center mb-4 mt-2">
                <a href="{{route('home')}}" class="app-brand-link gap-2">
                  <span class="app-brand-logo demo">
                  </span>
                    <span class="app-brand-text demo text-body fw-bold">Vuexy</span>
                </a>
            </div>
            <!-- /Logo -->
            <h4 class="mb-1 pt-2">Forgot Password? 🔒</h4>
            <p class="mb-4">Enter your email and we'll send you instructions to reset your password</p>
            <form id="formAuthentication" class="mb-3" method="get" action="{{ route('send-otp') }}">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="Enter your email" autofocus/>
                    @error('email')
                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <button class="btn btn-primary d-grid w-100">Send OTP</button>
            </form>
            <div class="text-center">
                <a href="{{route('login')}}" class="d-flex align-items-center justify-content-center">
                    <i class="ti ti-chevron-left scaleX-n1-rtl"></i>
                    Back to login
                </a>
            </div>
        </div>
    </div>
    <!-- /Forgot Password -->
@endsection
