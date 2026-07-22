@extends('backend.layouts.app')

@section("title")
    | Add Fine
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center border-bottom">
                <h5 class="mb-0">{{$page_title}}</h5>
                <a href="{{ route('fines.index') }}" class="btn btn-secondary btn-sm">
                    <i class="ti ti-arrow-left me-1"></i> Back to List
                </a>
            </div>

            <div class="card-body mt-4">
                <form action="{{ route('fines.store') }}" method="POST">
                    @csrf

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Entry Date</label>
                            <input type="date" name="date" value="{{ now()->format('Y-m-d') }}" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label d-block fw-bold mb-2">Entry Type</label>
                            <div class="form-check form-check-inline mt-1">
                                <input class="form-check-input" type="radio" name="type" id="type_bazar" value="bazar_fine" checked>
                                <label class="form-check-label" for="type_bazar">Bazar Fine</label>
                            </div>
                            <div class="form-check form-check-inline mt-1">
                                <input class="form-check-input" type="radio" name="type" id="type_other" value="other_fine">
                                <label class="form-check-label" for="type_other">Other Fine</label>
                            </div>
                        </div>

                        <hr class="my-2">

                        <div class="col-md-12">
                            <label class="form-label fw-bold text-danger" id="user_label">Fined Users (Those who will pay)</label>
                            <select name="users[]" class="form-select select2" multiple id="main_user_select" required>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->phone }})</option>
                                @endforeach
                            </select>
                            <div id="user_help_text" class="form-text text-info">Select users who are being fined.</div>
                        </div>

                        <div class="col-md-12" id="beneficiary_section">
                            <label class="form-label fw-bold text-success">Beneficiary Users (Those who did Bazar - Max 2)</label>
                            <select name="beneficiary_users[]" class="form-select select2" multiple id="beneficiary_select">
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->phone }})</option>
                                @endforeach
                            </select>
                            <div class="form-text text-success">This amount will be credited to these users.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Amount (Tk)</label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="amount" class="form-control form-control-lg" placeholder="0.00" required>
                                <span class="input-group-text">Tk</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Note / Description</label>
                            <input type="text" name="note" class="form-control form-control-lg" placeholder="Enter reason or details..." required>
                        </div>

                        <div class="col-12 mt-4 text-end">
                            <button type="reset" class="btn btn-label-secondary me-2">Reset</button>
                            <button type="submit" class="btn btn-primary btn-lg px-5">Save Entry</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            let $userSelect = $('#main_user_select').select2({
                placeholder: "Select users",
                allowClear: true
            });

            let $beneficiarySelect = $('#beneficiary_select').select2({
                placeholder: "Select up to 2 users",
                maximumSelectionLength: 2,
                allowClear: true
            });

            $('input[name="type"]').on('change', function() {
                const isBazar = $(this).val() === 'bazar_fine';

                if (isBazar) {
                    $('#beneficiary_section').removeClass('d-none');
                    $('#user_label').text('Fined Users (Bazar Fine)');
                    $('#beneficiary_select').prop('required', true);
                } else {
                    $('#beneficiary_section').addClass('d-none');
                    $('#user_label').text('Fined Users (Other Fine)');
                    $('#beneficiary_select').val(null).trigger('change').prop('required', false);
                }
            });
        });
    </script>
@endpush
