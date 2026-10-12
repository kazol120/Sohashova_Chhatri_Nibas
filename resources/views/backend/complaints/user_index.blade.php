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
                    <textarea name="complaint_text" id="complaint_text" rows="4" class="form-control @error('complaint_text') is-invalid @enderror" required></textarea>
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
        {{-- Desktop Table View --}}
        <div class="d-none d-md-block table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0">
                <thead style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); color: #ffffff;">
                    <tr class="small text-white fw-bold text-uppercase">
                        <th style="width: 50px;" class="text-white">SL</th>
                        <th class="text-white">Complaint Text (অভিযোগের বিবরণ)</th>
                        <th class="text-white">Submitted Date (তারিখ)</th>
                        <th class="text-white">Status (অবস্থা)</th>
                        <th class="text-white">Admin Response / Note (এডমিন ফিডব্যাক)</th>
                    </tr>
                </thead>
                <tbody class="bg-white" id="userComplaintsTableBody">
                    @forelse($complaints as $key => $complaint)
                    <tr>
                        <td class="text-dark fw-bold">{{ $complaints->firstItem() + $key }}</td>
                        <td style="white-space: normal; min-width: 250px;">
                            <div class="p-2.5 rounded-3 border text-wrap fs-7 text-dark fw-medium" style="background: #f8fafc; border-color: #cbd5e1 !important;">
                                {{ $complaint->complaint_text }}
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $complaint->created_at->format('d M, Y') }}</div>
                            <small class="text-secondary">{{ $complaint->created_at->format('h:i A') }}</small>
                        </td>
                        <td>
                            @if($complaint->status == 1)
                                <span class="badge px-3 py-2 rounded-pill fw-bold" style="background: #dcfce7; color: #166534; border: 1px solid #86efac;"><i class="fa fa-check-circle me-1"></i> Accepted & Resolved</span>
                            @else
                                <span class="badge px-3 py-2 rounded-pill fw-bold" style="background: #fef9c3; color: #854d0e; border: 1px solid #fde047;"><i class="fa fa-clock me-1"></i> Pending Review</span>
                            @endif
                        </td>
                        <td>
                            @if($complaint->admin_note)
                                <div class="p-2 rounded-2 fw-semibold fs-7" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;"><i class="fa fa-comment-dots me-1"></i> {{ $complaint->admin_note }}</div>
                            @else
                                <span class="text-secondary small fw-medium">Awaiting Admin Response</span>
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

        {{-- Mobile Card View (Distinct Div Blocks with High-Contrast Colors) --}}
        <div class="d-md-none p-2 p-sm-3" id="userComplaintsMobileList" style="background: #f1f5f9;">
            @forelse($complaints as $key => $complaint)
            <div class="card shadow-sm mb-3 rounded-4 overflow-hidden border-0" style="box-shadow: 0 4px 12px rgba(0,0,0,0.07) !important;">
                <div class="py-2.5 px-3 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff;">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary text-white rounded-circle fw-bold" style="width:24px; height:24px; display:inline-flex; align-items:center; justify-content:center; font-size:11px;">{{ $complaints->firstItem() + $key }}</span>
                        <span class="small text-white fw-bold">
                            {{ $complaint->created_at->format('d M, Y h:i A') }}
                        </span>
                    </div>
                    <div>
                        @if($complaint->status == 1)
                            <span class="badge px-2.5 py-1 rounded-pill fw-bold" style="background: #22c55e; color: #ffffff;"><i class="fa fa-check-circle me-1"></i> Resolved</span>
                        @else
                            <span class="badge px-2.5 py-1 rounded-pill fw-bold" style="background: #eab308; color: #000000;"><i class="fa fa-clock me-1"></i> Pending</span>
                        @endif
                    </div>
                </div>
                <div class="card-body p-3 bg-white">
                    <div class="p-3 rounded-3 border text-dark fs-7 fw-medium mb-2.5" style="background: #f8fafc; border-color: #cbd5e1 !important; line-height: 1.5;">
                        {{ $complaint->complaint_text }}
                    </div>

                    @if($complaint->admin_note)
                    <div class="p-2.5 rounded-3 small border" style="background: #ecfdf5; border-color: #a7f3d0; color: #065f46;">
                        <strong class="d-block mb-1" style="color: #047857;"><i class="fa fa-comment-dots me-1"></i> এডমিন উত্তর:</strong>
                        <span class="fw-semibold">{{ $complaint->admin_note }}</span>
                    </div>
                    @else
                    <div class="small text-secondary fw-semibold">
                        <i class="fa fa-hourglass-start me-1"></i> এডমিন পর্যালোচনার অপেক্ষায়...
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center py-5 text-muted bg-white rounded-3">
                <i class="fa fa-inbox fs-1 d-block mb-2 text-secondary"></i>
                আপনি কোনো অভিযোগ জমা দেননি।
            </div>
            @endforelse
        </div>

        <div class="card-footer py-3">
            {{ $complaints->links() }}
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function pollComplaintsLive() {
        fetch("{{ route('complaints.user-check') }}")
            .then(res => res.json())
            .then(data => {
                if (!data.success || !data.complaints) return;

                let tbodyHtml = '';
                let mobileHtml = '';

                data.complaints.forEach((c, index) => {
                    const statusBadgeDesktop = c.status === 1 
                        ? `<span class="badge px-3 py-2 rounded-pill fw-bold" style="background: #dcfce7; color: #166534; border: 1px solid #86efac;"><i class="fa fa-check-circle me-1"></i> Accepted & Resolved</span>`
                        : `<span class="badge px-3 py-2 rounded-pill fw-bold" style="background: #fef9c3; color: #854d0e; border: 1px solid #fde047;"><i class="fa fa-clock me-1"></i> Pending Review</span>`;

                    const statusBadgeMobile = c.status === 1 
                        ? `<span class="badge px-2.5 py-1 rounded-pill fw-bold" style="background: #22c55e; color: #ffffff;"><i class="fa fa-check-circle me-1"></i> Resolved</span>`
                        : `<span class="badge px-2.5 py-1 rounded-pill fw-bold" style="background: #eab308; color: #000000;"><i class="fa fa-clock me-1"></i> Pending</span>`;

                    const adminNoteDesktop = c.admin_note 
                        ? `<div class="p-2 rounded-2 fw-semibold fs-7" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;"><i class="fa fa-comment-dots me-1"></i> ${c.admin_note}</div>`
                        : `<span class="text-secondary small fw-medium">Awaiting Admin Response</span>`;

                    const adminNoteMobile = c.admin_note
                        ? `<div class="p-2.5 rounded-3 small border" style="background: #ecfdf5; border-color: #a7f3d0; color: #065f46;"><strong class="d-block mb-1" style="color: #047857;"><i class="fa fa-comment-dots me-1"></i> এডমিন উত্তর:</strong><span class="fw-semibold">${c.admin_note}</span></div>`
                        : `<div class="small text-secondary fw-semibold"><i class="fa fa-hourglass-start me-1"></i> এডমিন পর্যালোচনার অপেক্ষায়...</div>`;

                    tbodyHtml += `
                        <tr>
                            <td class="text-dark fw-bold">${index + 1}</td>
                            <td style="white-space: normal; min-width: 250px;">
                                <div class="p-2.5 rounded-3 border text-wrap fs-7 text-dark fw-medium" style="background: #f8fafc; border-color: #cbd5e1 !important;">
                                    ${c.complaint_text}
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">${c.date_formatted}</div>
                                <small class="text-secondary">${c.time_formatted}</small>
                            </td>
                            <td>${statusBadgeDesktop}</td>
                            <td>${adminNoteDesktop}</td>
                        </tr>
                    `;

                    mobileHtml += `
                        <div class="card shadow-sm mb-3 rounded-4 overflow-hidden border-0" style="box-shadow: 0 4px 12px rgba(0,0,0,0.07) !important;">
                            <div class="py-2.5 px-3 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff;">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary text-white rounded-circle fw-bold" style="width:24px; height:24px; display:inline-flex; align-items:center; justify-content:center; font-size:11px;">${index + 1}</span>
                                    <span class="small text-white fw-bold">${c.date_formatted} ${c.time_formatted}</span>
                                </div>
                                <div>${statusBadgeMobile}</div>
                            </div>
                            <div class="card-body p-3 bg-white">
                                <div class="p-3 rounded-3 border text-dark fs-7 fw-medium mb-2.5" style="background: #f8fafc; border-color: #cbd5e1 !important; line-height: 1.5;">
                                    ${c.complaint_text}
                                </div>
                                ${adminNoteMobile}
                            </div>
                        </div>
                    `;
                });

                const tbody = document.getElementById('userComplaintsTableBody');
                if (tbody && tbodyHtml) {
                    tbody.innerHTML = tbodyHtml;
                }

                const mobileContainer = document.getElementById('userComplaintsMobileList');
                if (mobileContainer && mobileHtml) {
                    mobileContainer.innerHTML = mobileHtml;
                }
            })
            .catch(err => console.error('Complaints live poll error:', err));
    }

    // Run immediately & poll every 3 seconds
    pollComplaintsLive();
    setInterval(pollComplaintsLive, 3000);
});
</script>
@endsection
