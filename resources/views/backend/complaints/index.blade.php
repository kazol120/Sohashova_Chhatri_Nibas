@extends('backend.layouts.app')
@section("title") | {{$page_title}} @endsection
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="fa fa-exclamation-triangle text-danger me-2"></i>Complaints Management (অভিযোগ তালিকা)
            </h4>
            <span class="text-muted">Manage resident complaints and resolve issues</span>
        </div>
        <div>
            <span class="badge bg-label-danger fs-6 px-3 py-2 rounded-pill">
                <i class="fa fa-bell me-1"></i> {{ \App\Models\Backend\Complaint::where('status', 0)->count() }} Pending Complaints
            </span>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form action="{{ route('complaints.index') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search resident name, phone or complaint..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Status (সকল অবস্থা)</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Pending (অপেক্ষমাণ)</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Accepted / Resolved (সমাধানকৃত)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('complaints.index') }}" class="btn btn-outline-secondary w-100">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Complaints Table -->
    <div class="card shadow-sm">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">SL</th>
                        <th>Resident Info (ছাত্রীর তথ্য)</th>
                        <th>Seat / Room / Floor (স্থান)</th>
                        <th>Complaint Details (অভিযোগের বিবরণ)</th>
                        <th>Date & Time (তারিখ ও সময়)</th>
                        <th>Status (অবস্থা)</th>
                        <th class="text-center">Action (পদক্ষেপ)</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($complaints as $key => $complaint)
                    <tr>
                        <td>{{ $complaints->firstItem() + $key }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-initial rounded-circle bg-label-primary fw-bold">
                                        {{ strtoupper(substr($complaint->user->name ?? 'R', 0, 1)) }}
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark">{{ $complaint->user->name ?? 'Resident' }}</h6>
                                    <small class="text-muted"><i class="fa fa-phone me-1 fs-8"></i>{{ $complaint->user->phone ?? 'N/A' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($complaint->booking)
                                @php
                                    $roomItems = $complaint->booking->floor_number_room_number_roomprice ?? [];
                                    $floors = [];
                                    $rooms = [];
                                    $seats = [];
                                    if(is_array($roomItems)) {
                                        foreach($roomItems as $item) {
                                            if(isset($item['floornumber'])) $floors[] = $item['floornumber'];
                                            if(isset($item['roomnumber'])) {
                                                $parts = explode('-', $item['roomnumber']);
                                                $rooms[] = $parts[0] ?? '';
                                                if(count($parts) > 1) $seats[] = $parts[1];
                                            }
                                        }
                                    }
                                    $floorStr = implode(', ', array_unique($floors)) ?: ($complaint->booking->floornumber ?? '-');
                                    $roomStr = implode(', ', array_unique($rooms)) ?: ($complaint->booking->roomnumber ?? '-');
                                    $seatStr = implode(', ', array_unique($seats)) ?: '-';
                                @endphp
                                <div class="lh-sm">
                                    <span class="badge bg-label-primary mb-1"><i class="fa fa-building me-1"></i>Floor: {{ $floorStr }}</span><br>
                                    <span class="badge bg-label-info"><i class="fa fa-bed me-1"></i>Room: {{ $roomStr }} | Seat: {{ $seatStr }}</span>
                                </div>
                            @else
                                <span class="badge bg-label-secondary">No Booking Link</span>
                            @endif
                        </td>
                        <td style="white-space: normal !important; word-break: break-word; min-width: 250px;">
                            <div class="p-2 bg-light rounded border fs-7 text-dark" style="white-space: normal !important; word-break: break-word;">
                                {{ $complaint->complaint_text }}
                            </div>
                            @if($complaint->admin_note)
                                <small class="text-success d-block mt-1" style="white-space: normal !important; word-break: break-word;"><i class="fa fa-comment-dots me-1"></i>Admin Note: {{ $complaint->admin_note }}</small>
                            @endif
                        </td>
                        <td>
                            <small class="text-dark fw-medium">{{ $complaint->created_at->timezone('Asia/Dhaka')->format('d M, Y') }}</small><br>
                            <small class="text-muted">{{ $complaint->created_at->timezone('Asia/Dhaka')->format('h:i A') }}</small>
                        </td>
                        <td>
                            @if($complaint->status == 1)
                                <span class="badge bg-label-success rounded-pill"><i class="fa fa-check-circle me-1"></i> Accepted / Resolved</span>
                            @else
                                <span class="badge bg-label-warning rounded-pill"><i class="fa fa-clock me-1"></i> Pending</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($complaint->status == 0)
                                <button type="button" class="btn btn-sm btn-success fw-bold waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#resolveModal{{ $complaint->id }}">
                                    <i class="fa fa-check me-1"></i> Accept & Resolve
                                </button>

                                <!-- Resolve Modal -->
                                <div class="modal fade" id="resolveModal{{ $complaint->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form action="{{ route('complaints.update-status', $complaint->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-header bg-success text-white py-3">
                                                    <h5 class="modal-title text-white fw-bold mb-0">Accept & Resolve Complaint</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-start">
                                                    <p class="fw-semibold text-dark mb-2">Mark this complaint as accepted and resolved?</p>
                                                    <div class="p-3 bg-light rounded mb-3 border text-start" style="white-space: normal !important; word-break: break-word;">
                                                        <div class="mb-2"><strong>Resident:</strong> {{ $complaint->user->name ?? 'Resident' }}</div>
                                                        <div><strong>Complaint:</strong> <span class="text-dark">{{ $complaint->complaint_text }}</span></div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Note for Resident (Optional):</label>
                                                        <textarea name="admin_note" rows="3" class="form-control" placeholder="e.g. Electrician sent, issue fixed!"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-success fw-bold">Confirm Accept & Resolve</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span class="text-success fw-semibold"><i class="fa fa-check-double me-1"></i>Resolved</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fa fa-inbox fs-1 d-block mb-2 text-secondary"></i>
                            No complaints found in system.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer py-3">
            {{ $complaints->links() }}
        </div>
    </div>
</div>
@endsection
