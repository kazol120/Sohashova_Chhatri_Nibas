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
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDepositModal">
                    <i class="ti ti-plus me-1"></i> Add Deposit
                </button>
            </div>

            <div class="card-body">

                <form action="{{ route('deposits.index') }}" method="GET" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Select Month</label>
                        <input type="month"
                               name="month"
                               class="form-control"
                               max="{{ now()->format('Y-m') }}"
                               value="{{ request('month', $selectedMonth ?? now()->format('Y-m')) }}">
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            Search
                        </button>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <a href="{{ route('deposits.index') }}" class="btn btn-secondary w-100">
                            Reset
                        </a>
                    </div>
                </form>

                <div class="alert alert-info">
                    Showing deposit list for
                    <strong>{{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}</strong>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border-primary h-100">
                            <div class="card-body text-center">
                                <h6 class="mb-2">Total Member</h6>
                                <h4 class="mb-0">{{ $summary['total_member'] }}</h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card border-{{ $summary['total_deposit'] < 0 ? 'warning' : 'success' }} h-100">
                            <div class="card-body text-center">
                                <h6 class="mb-2">Total Deposit</h6>
                                <h4 class="mb-0 text-{{ $summary['total_deposit'] < 0 ? 'warning' : 'success' }}">
                                    {{ number_format($summary['total_deposit'], 2) }}
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-dark">
                        <tr>
                            <th style="width: 70px;">SL</th>
                            <th>Member Name</th>
                            <th>Phone</th>
                            <th class="text-center" style="width: 180px;">Total Deposit</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($deposits as $deposit)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <a href="{{ route('deposit.history', ['user_id' => $deposit->user_id, 'selectedMonth' => $selectedMonth]) }}">
                                        {{ $deposit->user->name ?? '-' }}
                                    </a>
                                </td>
                                <td>{{ $deposit->user->phone ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $deposit->total_deposit < 0 ? 'warning text-dark' : 'success' }}">
                                        {{ number_format($deposit->total_deposit, 2) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No deposit found</td>
                            </tr>
                        @endforelse
                        </tbody>
                        <tfoot>
                        <tr class="table-secondary fw-bold">
                            <td colspan="3" class="text-end">Grand Total</td>
                            <td class="text-center text-{{ $summary['total_deposit'] < 0 ? 'warning' : 'dark' }}">
                                {{ number_format($summary['total_deposit'], 2) }}
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <!-- Add Deposit Modal -->
    <div class="modal fade" id="addDepositModal" tabindex="-1" aria-labelledby="addDepositModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="depositForm" action="{{ route('deposits.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addDepositModalLabel">Create Deposit</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        {{-- User --}}
                        <div class="mb-3">
                            <label class="form-label">Select User <code>*</code></label>
                            <select name="user_id" class="select2-modal form-select" style="width: 100%" required>
                                <option value="">Choose User</option>
                                @foreach($users as $user)
                                    <option value="{{$user->id}}">
                                        {{$user->name}} ({{$user->phone}})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Amount --}}
                        <div class="mb-3">
                            <label class="form-label">Deposit Amount <code>*</code></label>
                            <input type="number" step="0.01" name="amount" class="form-control" placeholder="Enter amount" required>
                        </div>

                        {{-- Date --}}
                        <div class="mb-3">
                            <label class="form-label">Date <code>*</code></label>
                            <input type="date" name="date" class="form-control" max="{{ now()->format('Y-m-d') }}" value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button id="submitBtn" class="btn btn-primary" type="submit">Save Deposit</button>
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
            $('#addDepositModal').on('shown.bs.modal', function () {
                $('.select2-modal').select2({
                    dropdownParent: $('#addDepositModal'),
                    placeholder: 'Select user',
                    allowClear: true
                });
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
