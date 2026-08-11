@extends('backend.layouts.app')

@section("title")
    | {{$page_title}}
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header border-bottom bg-light py-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <h5 class="card-title mb-0 fw-bold text-dark">
                                <i class="ti ti-cup me-2 text-warning"></i>{{$page_title}}
                            </h5>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('meal-requests.index') }}" class="btn btn-outline-secondary {{ !request()->filled('status') ? 'active' : '' }}">All</a>
                                <a href="{{ route('meal-requests.index', ['status' => 0]) }}" class="btn btn-outline-warning {{ request('status') === '0' ? 'active' : '' }}">Pending</a>
                                <a href="{{ route('meal-requests.index', ['status' => 1]) }}" class="btn btn-outline-success {{ request('status') === '1' ? 'active' : '' }}">Approved</a>
                                <a href="{{ route('meal-requests.index', ['status' => 2]) }}" class="btn btn-outline-danger {{ request('status') === '2' ? 'active' : '' }}">Rejected</a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body pt-4">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible d-flex align-items-center mb-4 shadow-sm" role="alert">
                                <i class="fa fa-check-circle me-2 fs-5 text-success"></i>
                                <div>{{ session('success') }}</div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible d-flex align-items-center mb-4 shadow-sm" role="alert">
                                <i class="fa fa-exclamation-triangle me-2 fs-5 text-danger"></i>
                                <div>{{ session('error') }}</div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div id="meal_requests_table_container">
                            <div class="table-responsive border rounded">
                                <table class="table table-hover table-striped align-middle mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th style="width: 60px;">SL</th>
                                        <th>Resident Name</th>
                                        <th>Phone</th>
                                        <th>Date</th>
                                        <th>Requested Meal Status</th>
                                        <th>Requested Time</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center" style="width: 200px;">Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @php
                                        $labels = [
                                            'full'       => 'Full Meal (মিল চালু)',
                                            'half_day'   => '☀️ Day Half Meal',
                                            'half_night' => '🌙 Night Half Meal',
                                            'off'        => 'Meal OFF (মিল বন্ধ)',
                                        ];
                                    @endphp

                                    @forelse($mealRequests as $key => $mr)
                                        <tr>
                                            <td><span class="text-muted font-monospace">{{ str_pad($key + 1, 2, '0', STR_PAD_LEFT) }}</span></td>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $mr->user->name ?? 'Resident' }}</div>
                                            </td>
                                            <td><small class="text-secondary">{{ $mr->user->phone ?? '-' }}</small></td>
                                            <td>
                                                <span class="badge bg-dark fs-7 px-2 py-1"><i class="ti ti-calendar me-1"></i>{{ \Carbon\Carbon::parse($mr->date)->format('d-M-Y') }}</span>
                                                @if($mr->end_date && $mr->total_days > 1)
                                                    <div class="mt-1">
                                                        <small class="text-danger fw-bold"><i class="ti ti-arrow-right me-1"></i>{{ \Carbon\Carbon::parse($mr->end_date)->format('d-M-Y') }}</small>
                                                        <br><span class="badge bg-danger text-white mt-1" style="font-size: 0.72rem;">মোট {{ $mr->total_days }} দিন</span>
                                                    </div>
                                                @else
                                                    <div class="mt-1">
                                                        <span class="badge bg-label-secondary text-secondary" style="font-size: 0.72rem;">১ দিন (Single Day)</span>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                @if($mr->request_type == 'off')
                                                    @php
                                                        $nextDateStr = \Carbon\Carbon::parse($mr->end_date ?: $mr->date)->addDay()->format('d-M-Y');
                                                    @endphp
                                                    <div class="d-inline-flex flex-column align-items-start">
                                                        <span class="badge bg-danger fs-7 px-3 py-2 shadow-sm">
                                                            <i class="fa fa-ban me-1"></i> Meal OFF (মিল বন্ধ)
                                                        </span>
                                                        @if($mr->total_days > 1)
                                                            <small class="text-danger fw-bold mt-1">📌 মোট {{ $mr->total_days }} দিনের বন্ধের অনুরোধ</small>
                                                        @else
                                                            <small class="text-dark fw-semibold mt-1">📌 ১ দিনের বন্ধের অনুরোধ</small>
                                                        @endif
                                                        <small class="text-success fw-bold mt-1"><i class="ti ti-refresh me-1"></i>🔄 {{ $nextDateStr }} তারিখ থেকে অটো মিল চালু</small>
                                                    </div>
                                                @elseif($mr->request_type == 'full')
                                                    <span class="badge bg-primary fs-7 px-3 py-2 shadow-sm">
                                                        <i class="fa fa-check-circle me-1"></i> Full Meal (মিল চালু)
                                                    </span>
                                                @elseif($mr->request_type == 'half_day')
                                                    @php
                                                        $nextDateStr = \Carbon\Carbon::parse($mr->date)->addDay()->format('d-M-Y');
                                                    @endphp
                                                    <div class="d-inline-flex flex-column align-items-start">
                                                        <span class="badge bg-info fs-7 px-3 py-2 shadow-sm">
                                                            ☀️ Day Half Meal (দুপুরের মিল চালু / রাতের মিল বন্ধ)
                                                        </span>
                                                        <small class="text-success fw-bold mt-1"><i class="ti ti-refresh me-1"></i>🔄 {{ $nextDateStr }} তারিখ থেকে স্বাভাবিক মিল চালু</small>
                                                    </div>
                                                @elseif($mr->request_type == 'half_night')
                                                    <div class="d-inline-flex flex-column align-items-start">
                                                        <span class="badge bg-info fs-7 px-3 py-2 shadow-sm">
                                                            🌙 Night Half Meal (রাতের মিল চালু / দুপুরের মিল বন্ধ)
                                                        </span>
                                                        <small class="text-success fw-bold mt-1"><i class="ti ti-check me-1"></i>☀️ দুপুরের মিল বন্ধ, আজ রাত থেকে রাতের মিল চালু</small>
                                                    </div>
                                                @else
                                                    <span class="badge bg-info fs-7 px-3 py-2">
                                                        {{ $labels[$mr->request_type] ?? $mr->request_type }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td><small class="text-muted">{{ $mr->created_at->diffForHumans() }}</small></td>
                                            <td class="text-center">
                                                @if($mr->status == 0)
                                                    <span class="badge bg-warning text-dark px-3 py-2">
                                                        <i class="fa fa-clock me-1"></i> Pending
                                                    </span>
                                                @elseif($mr->status == 1)
                                                    <span class="badge bg-success px-3 py-2">
                                                        <i class="fa fa-check me-1"></i> Approved
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary px-3 py-2">
                                                        <i class="fa fa-times me-1"></i> Rejected
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($mr->status == 0)
                                                    <div class="d-flex justify-content-center gap-2">
                                                        <!-- Approve Button -->
                                                        <form action="{{ route('meal-requests.approve', $mr->id) }}" method="POST" class="d-inline admin-meal-req-form">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success fw-bold shadow-sm">
                                                                <i class="fa fa-check me-1"></i> Accept
                                                            </button>
                                                        </form>

                                                        <!-- Reject Button -->
                                                        <form action="{{ route('meal-requests.reject', $mr->id) }}" method="POST" class="d-inline admin-meal-req-form">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                <i class="fa fa-times me-1"></i> Reject
                                                            </button>
                                                        </form>
                                                    </div>
                                                @else
                                                    <span class="text-muted fs-8">Completed</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5 text-muted">
                                                <i class="ti ti-check-circle fs-2 d-block mb-2 text-secondary"></i>
                                                No meal requests found.
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                {{ $mealRequests->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function refreshRequestsTable() {
                fetch(window.location.href, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newTable = doc.getElementById('meal_requests_table_container');
                    const currentTable = document.getElementById('meal_requests_table_container');
                    if (newTable && currentTable) {
                        currentTable.innerHTML = newTable.innerHTML;
                    }
                })
                .catch(err => console.log('Live poll skipped', err));
            }

            document.addEventListener('submit', function(e) {
                if (e.target && e.target.classList.contains('admin-meal-req-form')) {
                    e.preventDefault();
                    const form = e.target;
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) submitBtn.disabled = true;

                    fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        refreshRequestsTable();
                    })
                    .catch(err => {
                        if (submitBtn) submitBtn.disabled = false;
                    });
                }
            });

            // Live auto-poll new requests every 10 seconds without full page reload
            setInterval(refreshRequestsTable, 10000);
        });
    </script>
@endpush
