@extends('backend.layouts.app')
@section("title") | {{$page_title}} @endsection
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="fa fa-comment-alt text-primary me-2"></i>My Complaints (আমার অভিযোগ)
            </h4>
            <span class="text-muted">Submit any hostel issues or complaints directly to management</span>
        </div>
    </div>

    <!-- Submit Complaint Card -->
    <div class="card mb-4 shadow-sm border-0" style="border-top: 4px solid #6366f1 !important;">
        <div class="card-header bg-label-primary py-3">
            <h5 class="mb-0 fw-bold text-primary"><i class="fa fa-plus-circle me-2"></i>Submit New Complaint (নতুন অভিযোগ জমা দিন)</h5>
        </div>
        <div class="card-body pt-4">
            <form action="{{ route('complaints.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="complaint_text" class="form-label fw-semibold text-dark">
                        Describe your issue/complaint (আপনার সমস্যা বা অভিযোগের বিবরণ লিখুন) <code>*</code>
                    </label>
                    <textarea name="complaint_text" id="complaint_text" rows="4" class="form-control @error('complaint_text') is-invalid @enderror" required placeholder="উদাহরণ: ৩য় তলার ২০৪ নম্বর রুমের ফ্যানটি কাজ করছে না, অনুগ্রহ করে টেকনিশিয়ান পাঠান।"></textarea>
                    @error('complaint_text')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary px-4 fw-bold waves-effect waves-light">
                        <i class="fa fa-paper-plane me-2"></i>Submit Complaint (অভিযোগ পাঠান)
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- My Submitted Complaints Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header py-3 bg-light">
            <h5 class="mb-0 fw-bold text-dark"><i class="fa fa-list-ul me-2 text-primary"></i>My Submitted Complaints History (প্রেরিত অভিযোগের তালিকা)</h5>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">SL</th>
                        <th>Complaint Text (অভিযোগের বিবরণ)</th>
                        <th>Submitted Date (তারিখ)</th>
                        <th>Status (অবস্থা)</th>
                        <th>Admin Response / Note (এডমিন ফিডব্যাক)</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($complaints as $key => $complaint)
                    <tr>
                        <td>{{ $complaints->firstItem() + $key }}</td>
                        <td style="white-space: normal; min-width: 250px;">
                            <div class="p-2 bg-light rounded border text-wrap fs-7 text-dark fw-medium">
                                {{ $complaint->complaint_text }}
                            </div>
                        </td>
                        <td>
                            <small class="text-dark fw-medium">{{ $complaint->created_at->format('d M, Y') }}</small><br>
                            <small class="text-muted">{{ $complaint->created_at->format('h:i A') }}</small>
                        </td>
                        <td>
                            @if($complaint->status == 1)
                                <span class="badge bg-label-success rounded-pill px-3 py-2 fs-7"><i class="fa fa-check-circle me-1"></i> Accepted & Resolved</span>
                            @else
                                <span class="badge bg-label-warning rounded-pill px-3 py-2 fs-7"><i class="fa fa-clock me-1"></i> Pending Review</span>
                            @endif
                        </td>
                        <td>
                            @if($complaint->admin_note)
                                <span class="text-success fw-semibold fs-7"><i class="fa fa-comment-dots me-1"></i> {{ $complaint->admin_note }}</span>
                            @else
                                <span class="text-muted fs-8">Awaiting Admin Response</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fa fa-inbox fs-1 d-block mb-2 text-secondary"></i>
                            আপনি কোনো অভিযোগ জমা দেননি।
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
