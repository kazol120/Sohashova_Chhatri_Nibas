@extends('backend.layouts.app')

@section("title")
    | {{$page_title}}
@endsection

@push('style')
    <link rel="stylesheet" href="{{asset('backend')}}/vendor/libs/datatables-bs5/datatables.bootstrap5.css"/>
    <link rel="stylesheet" href="{{asset('backend')}}/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css"/>
    <link rel="stylesheet" href="{{asset('backend')}}/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css"/>
    <link rel="stylesheet" href="{{asset('backend')}}/vendor/libs/select2/select2.css"/>
@endpush

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">{{$page_title}}</h5>
                <a href="{{ route('deposits.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i> Add Deposit </a>
            </div>

            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card border-danger">
                            <div class="card-body text-center">
                                <h6>Total Deposit</h6>
                                <h4>{{ number_format($summary['total_deposit'], 2) }} Tk</h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h6>Total Fine</h6>
                                <h4>{{ number_format($summary['total_credit'], 2) }} Tk</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h6>Total Meal Cost</h6>
                                <h4>{{ number_format($summary['total_meal_cost'], 2) }} Tk</h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h6>Net Balance</h6>
                                <h4>{{ number_format($summary['balance'], 2) }} Tk</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-dark">
                        <tr>
                            <th style="width: 70px;">SL</th>
                            <th>Date</th>
                            <th>Member Name</th>
                            <th>Phone</th>
                            <th class="text-center">Amount</th>
                            <th>Note</th>
                            <th>Created By</th>

                            <th class="text-center" style="width: 140px;">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($deposits as $deposit)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ \Carbon\Carbon::parse($deposit->date)->format('d M Y') }}</td>
                                <td>{{ $deposit->user->name ?? '-' }}</td>
                                <td>{{ $deposit->user->phone ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $deposit->amount < 0 ? 'warning' : 'success' }}">
                                        {{ number_format($deposit->amount, 2) }}
                                    </span>
                                </td>
                                <td>{{$deposit->note ?? ''}}</td>
                                <td>{{ $deposit->madeBy->name ?? '-' }}</td>

                                <td class="text-center">
                                    <button type="button" data-id="{{ $deposit->id }}" class="btn btn-danger btn-sm delete_button" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No deposit found</td>
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
                    Are you sure you want to delete this deposit?
                </div>
                <div class="modal-footer">
                    <form action="{{ route('deposits.destroy', 0) }}" method="POST" id="deleteForm">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
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
            $('.select2').select2({
                placeholder: 'Select user',
                allowClear: true
            });

            $(document).on("click", '.delete_button', function () {
                var id = $(this).data('id');
                var url = '{{ route("deposits.destroy", ":id") }}';
                url = url.replace(':id', id);
                $("#deleteForm").attr("action", url);
            });
        });
    </script>
@endpush
