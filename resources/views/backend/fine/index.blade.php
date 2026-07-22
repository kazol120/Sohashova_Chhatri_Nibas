@extends('backend.layouts.app')

@section("title")
    | {{$page_title}}
@endsection

@push('style')
    <link rel="stylesheet" href="{{asset('backend')}}/vendor/libs/datatables-bs5/datatables.bootstrap5.css" />
    <link rel="stylesheet" href="{{asset('backend')}}/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css" />
    <link rel="stylesheet" href="{{asset('backend')}}/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css" />
    <link rel="stylesheet" href="{{asset('backend')}}/vendor/libs/select2/select2.css"/>
@endpush

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">{{$page_title}}</h5>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFineModal">
                    <i class="ti ti-plus me-1"></i> Add New Fine
                </button>
            </div>

            <div class="card-body">
                <form action="{{ route('fines.index') }}" method="GET" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Filter by Month</label>
                        <input type="month" name="month" class="form-control"
                               max="{{ now()->format('Y-m') }}"
                               value="{{ $selectedMonth }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Search</button>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <a href="{{ route('fines.index') }}" class="btn btn-secondary w-100">Reset</a>
                    </div>
                </form>

                <div class="alert alert-info">
                    Showing fine records for <strong>{{ \Carbon\Carbon::parse($selectedMonth)->format('F Y') }}</strong>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-dark">
                        <tr>
                            <th style="width: 50px;">SL</th>
                            <th>Date</th>
                            <th>Fined User</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Beneficiary (Reward To)</th>
                            <th>Reason</th>
                            <th class="text-center">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($fines as $key => $fine)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($fine->date)->format('d M, Y') }}</td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-danger">{{ $fine->user->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($fine->type == 1)
                                        <span class="badge bg-label-primary">Bazar Fine</span>
                                    @else
                                        <span class="badge bg-label-warning">Other Fine</span>
                                    @endif
                                </td>
                                <td class="fw-bold text-dark">
                                    {{ number_format($fine->amount, 2) }} Tk
                                </td>
                                <td>
                                    @if($fine->replaceUser)
                                        <span class="text-success fw-bold">{{ $fine->replaceUser->name }}</span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ Str::limit($fine->reason, 30) }}</small>
                                </td>
                                <td class="text-center">
                                    <button type="button"
                                            data-id="{{ $fine->id }}"
                                            class="btn btn-danger btn-sm delete_button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteModal">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">No fine records found for this month.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this fine?
                </div>
                <div class="modal-footer">
                    <form action="{{ route('fines.destroy', 0) }}" method="POST" id="deleteForm">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Fine Modal -->
    <div class="modal fade" id="addFineModal" tabindex="-1" aria-labelledby="addFineModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="fineForm" action="{{ route('fines.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addFineModalLabel">Create Fine</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            {{-- Date --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Entry Date <code>*</code></label>
                                <input type="date" name="date" value="{{ now()->format('Y-m-d') }}" class="form-control" required>
                            </div>

                            {{-- Type --}}
                            <div class="col-md-6">
                                <label class="form-label d-block fw-bold mb-2">Entry Type</label>
                                <div class="form-check form-check-inline mt-1">
                                    <input class="form-check-input" type="radio" name="type" id="modal_type_bazar" value="bazar_fine" checked>
                                    <label class="form-check-label" for="modal_type_bazar">Bazar Fine</label>
                                </div>
                                <div class="form-check form-check-inline mt-1">
                                    <input class="form-check-input" type="radio" name="type" id="modal_type_other" value="other_fine">
                                    <label class="form-check-label" for="modal_type_other">Other Fine</label>
                                </div>
                            </div>

                            {{-- Fined Users --}}
                            <div class="col-md-12">
                                <label class="form-label fw-bold text-danger" id="modal_user_label">Fined Users (Those who will pay) <code>*</code></label>
                                <select name="users[]" class="form-select" multiple id="modal_main_user_select" style="width: 100%" required>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->phone }})</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Beneficiary Users --}}
                            <div class="col-md-12" id="modal_beneficiary_section">
                                <label class="form-label fw-bold text-success">Beneficiary Users (Those who did Bazar - Max 2) <code>*</code></label>
                                <select name="beneficiary_users[]" class="form-select" multiple id="modal_beneficiary_select" style="width: 100%" required>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->phone }})</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Amount --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Amount (Tk) <code>*</code></label>
                                <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" required>
                            </div>

                            {{-- Note --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Note / Description <code>*</code></label>
                                <input type="text" name="note" class="form-control" placeholder="Enter reason or details..." required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button id="submitFineBtn" class="btn btn-primary" type="submit">Save Entry</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('script')
    <script src="{{asset('backend')}}/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
    <script src="{{asset('backend')}}/vendor/libs/datatables-bs5/custom-datatable5.js"></script>
    <script src="{{asset('backend')}}/vendor/libs/select2/select2.js"></script>

    <script>
        $(document).ready(function () {
            $(document).on("click", '.delete_button', function () {
                var id = $(this).data('id');
                var url = '{{ route("fines.destroy", ":id") }}';
                url = url.replace(':id', id);
                $("#deleteForm").attr("action", url);
            });

            $('#addFineModal').on('shown.bs.modal', function () {
                $('#modal_main_user_select').select2({
                    dropdownParent: $('#addFineModal'),
                    placeholder: "Select users",
                    allowClear: true
                });

                $('#modal_beneficiary_select').select2({
                    dropdownParent: $('#addFineModal'),
                    placeholder: "Select up to 2 users",
                    maximumSelectionLength: 2,
                    allowClear: true
                });
            });

            $('input[name="type"]').on('change', function() {
                const isBazar = $(this).val() === 'bazar_fine';

                if (isBazar) {
                    $('#modal_beneficiary_section').removeClass('d-none');
                    $('#modal_user_label').text('Fined Users (Bazar Fine)');
                    $('#modal_beneficiary_select').prop('required', true);
                } else {
                    $('#modal_beneficiary_section').addClass('d-none');
                    $('#modal_user_label').text('Fined Users (Other Fine)');
                    $('#modal_beneficiary_select').val(null).trigger('change').prop('required', false);
                }
            });

            let isSubmitting = false;
            $('#fineForm').on('submit', function (e) {
                if (isSubmitting) {
                    e.preventDefault();
                    return false;
                }
                isSubmitting = true;
                let btn = $('#submitFineBtn');
                btn.prop('disabled', true);
                btn.html('<span class="spinner-border spinner-border-sm"></span> Saving...');
            });
        });
    </script>
@endpush
