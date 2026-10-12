@extends('backend.layouts.app')
@section('title', 'Booking Button Status')

@push('style')
    <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center rounded-3 shadow-sm mb-4" role="alert">
                    <i class="ti ti-circle-check fs-4 me-2"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="border-top: 4px solid #033364 !important;">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="ti ti-toggle-right text-primary fs-4"></i>
                        Booking Button Status (বুকিং বাটন সেটিং)
                    </h5>
                    <span class="badge {{ (string)$status === '1' ? 'bg-success' : 'bg-danger' }} px-3 py-2 fs-7 fw-bold">
                        {{ (string)$status === '1' ? 'Active (চালু)' : 'Deactive (বন্ধ)' }}
                    </span>
                </div>

                <form action="{{ route('booking-button.update') }}" method="POST">
                    @csrf
                    <div class="card-body p-4">
                        <p class="text-muted small mb-4">
                            📌 এই সেটিংসের মাধ্যমে আপনি ফ্রন্টএন্ড ওয়েবসাইটে দৃশ্যমান ভাসমান <strong>"Book Room"</strong> বাটনটি চালু বা বন্ধ রাখতে পারবেন।
                        </p>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark mb-2 d-block">
                                Booking Button Status
                            </label>
                            
                            <div class="mt-1">
                                <input type="hidden" name="booking_button_status" value="0">
                                <input
                                    data-toggle="toggle"
                                    data-onstyle="success"
                                    data-offstyle="danger"
                                    data-on="Active"
                                    data-off="Deactivated"
                                    data-width="100%"
                                    type="checkbox"
                                    name="booking_button_status"
                                    value="1"
                                    id="booking_button_status"
                                    {{ (string)$status === '1' ? 'checked' : '' }}
                                >
                            </div>
                        </div>

                        <div class="alert alert-info border-0 rounded-3 mb-0 small">
                            <i class="ti ti-info-circle me-1"></i> <strong>দ্রষ্টব্য:</strong> 
                            স্ট্যাটাস 'Deactive' করা হলে ফ্রন্টএন্ড ওয়েবসাইটের "Book Room" ভাসমান বাটনটি হাইড থাকবে, ফলে নতুন ব্যবহারকারীরা অনলাইনে রুম বুকিং করতে পারবে না।
                        </div>
                    </div>

                    <div class="card-footer bg-light py-3 px-4 border-top">
                        <div class="d-grid gap-2 col-lg-6 mx-auto">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold waves-effect waves-light shadow-sm">
                                Save
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection

@push('script')
    <script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
@endpush

