@extends('backend.layouts.app')

@section("title")
    | {{$page_title}}
@endsection

@push('style')
    <link rel="stylesheet" href="{{asset('backend')}}/vendor/libs/select2/select2.css"/>
@endpush

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-md-12">

                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">{{$page_title}}</h5>

                        <a href="{{route('deposits.index')}}" class="btn btn-primary">
                            <i class="ti ti-arrow-left me-1"></i> Back
                        </a>
                    </div>

                    <form id="depositForm" action="{{route('deposits.store')}}" method="POST">
                        @csrf

                        <div class="card-body">

                            @if ($errors->any())
                                @foreach ($errors->all() as $error)
                                    <div class="alert alert-warning">{{ $error }}</div>
                                @endforeach
                            @endif

                            <div class="mb-3">
                                <label class="form-label">Select User <code>*</code></label>
                                <select name="user_id"
                                        class="select2 form-select @error('user_id') is-invalid @enderror"
                                        required>
                                    <option value="">Choose User</option>
                                    @foreach($users as $user)
                                        <option value="{{$user->id}}">
                                            {{$user->name}} ({{$user->phone}})
                                        </option>
                                    @endforeach
                                </select>

                                @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Deposit Amount <code>*</code></label>
                                <input type="number"
                                       step="0.01"
                                       name="amount"
                                       class="form-control @error('amount') is-invalid @enderror"
                                       placeholder="Enter amount"
                                       required>

                                @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Date <code>*</code></label>
                                <input type="date"
                                       name="date"
                                       class="form-control @error('date') is-invalid @enderror"
                                       max="{{ now()->format('Y-m-d') }}"
                                       value="{{ now()->format('Y-m-d') }}"
                                       required>

                                @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row mt-3">
                                <div class="d-grid gap-2 col-lg-4 mx-auto">
                                    <button id="submitBtn" class="btn btn-primary btn-lg" type="submit">
                                        Save Deposit
                                    </button>
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

    <script>
        $(document).ready(function () {

            $('.select2').select2({
                placeholder: 'Select user',
                allowClear: true
            });

            let isSubmitting = false;

            $('#depositForm').on('submit', function (e) {

                if (isSubmitting) {
                    e.preventDefault();
                    return false;
                }

                isSubmitting = true;

                let btn = $('#submitBtn');

                btn.prop('disabled', true);
                btn.html('<span class="spinner-border spinner-border-sm"></span> Saving...');
            });

        });
    </script>
@endpush
