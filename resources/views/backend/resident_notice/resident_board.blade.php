@extends(auth()->check() ? 'backend.layouts.app' : 'Frontend.layouts.app')
@section('title', 'Notice Board (বিজ্ঞপ্তি তালিকা) - সোহাসভা ছাত্রী নিবাস')

@section('content')
<div class="notranslate {{ auth()->check() ? 'container-xxl flex-grow-1 container-p-y' : 'container py-5' }}" translate="no" style="{{ !auth()->check() ? 'padding-top: 110px !important; min-height: 85vh;' : '' }}">
    
    <!-- Top Header Banner -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden" style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);">
        <div class="card-body p-4 text-white">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-white text-primary p-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px; min-width: 56px; color: #4f46e5 !important;">
                        <i class="fa fa-bullhorn fs-3"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            <span class="badge bg-warning text-dark fw-bold px-2.5 py-1 rounded-pill small">
                                📢 Notice Board
                            </span>
                            <span class="badge bg-white bg-opacity-25 text-white fw-semibold px-2.5 py-1 rounded-pill small">
                                হোস্টেল নোটিশ ও জরুরি বিজ্ঞপ্তি
                            </span>
                        </div>
                        <h4 class="fw-bold text-white mb-1">নোটিশ বোর্ড (Resident Notice Board)</h4>
                        <p class="mb-0 text-white-50 small">হোস্টেল কর্তৃপক্ষের সকল সাধারণ ও জরুরি নোটিশের বিস্তারিত তালিকা</p>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ auth()->check() ? route('dashboard') : url()->previous() }}" class="btn btn-light fw-bold px-3.5 py-2 rounded-3 shadow-sm d-inline-flex align-items-center gap-2" style="color: #4f46e5;">
                        <i class="fa fa-arrow-left"></i>
                        <span>ড্যাশবোর্ডে ফিরে যান</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white" style="border-left: 4px solid #4f46e5 !important;">
                <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-bold d-block mb-1">মোট বিজ্ঞপ্তি</span>
                        <h3 class="fw-bold mb-0 text-primary" style="color: #4f46e5 !important;">{{ $totalActiveCount }} টি</h3>
                    </div>
                    <div class="rounded-3 p-2.5 text-primary" style="background: #eef2ff;">
                        <i class="fa fa-list-alt fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white" style="border-left: 4px solid #ef4444 !important;">
                <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-bold d-block mb-1">জরুরি বিজ্ঞপ্তি</span>
                        <h3 class="fw-bold mb-0 text-danger">{{ $urgentCount }} টি</h3>
                    </div>
                    <div class="rounded-3 p-2.5 text-danger" style="background: #fef2f2;">
                        <i class="fa fa-exclamation-triangle fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white" style="border-left: 4px solid #f59e0b !important;">
                <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-bold d-block mb-1">গুরুত্বপূর্ণ নোটিশ</span>
                        <h3 class="fw-bold mb-0 text-warning">{{ $importantCount }} টি</h3>
                    </div>
                    <div class="rounded-3 p-2.5 text-warning" style="background: #fffbeb;">
                        <i class="fa fa-bell fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white" style="border-left: 4px solid #10b981 !important;">
                <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-bold d-block mb-1">সাধারণ নোটিশ</span>
                        <h3 class="fw-bold mb-0 text-success">{{ $normalCount }} টি</h3>
                    </div>
                    <div class="rounded-3 p-2.5 text-success" style="background: #ecfdf5;">
                        <i class="fa fa-info-circle fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-3 p-md-4">
            <form action="{{ url()->current() }}" method="GET" class="row g-3 align-items-center">
                <div class="col-12 col-md-6 col-lg-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-primary">
                            <i class="fa fa-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="বিজ্ঞপ্তির শিরোনাম বা বিষয় খুঁজুন..." value="{{ request('search') }}">
                        @if(request('search'))
                            <a href="{{ url()->current() }}" class="btn btn-outline-secondary" title="Clear Search">
                                <i class="fa fa-times"></i>
                            </a>
                        @endif
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-7 d-flex flex-wrap align-items-center justify-content-md-end gap-2">
                    <div class="btn-group" role="group">
                        <a href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->except('priority', 'page'), ['priority' => ''])) }}" 
                           class="btn btn-sm {{ !request('priority') ? 'btn-primary fw-bold active' : 'btn-outline-primary' }}">
                            সবাই (All)
                        </a>
                        <a href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->except('priority', 'page'), ['priority' => 'urgent'])) }}" 
                           class="btn btn-sm {{ request('priority') === 'urgent' ? 'btn-danger fw-bold active' : 'btn-outline-danger' }}">
                            🚨 জরুরি (Urgent)
                        </a>
                        <a href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->except('priority', 'page'), ['priority' => 'important'])) }}" 
                           class="btn btn-sm {{ request('priority') === 'important' ? 'btn-warning fw-bold active text-dark' : 'btn-outline-warning text-dark' }}">
                            ⚠️ গুরুত্বপূর্ণ (Important)
                        </a>
                        <a href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->except('priority', 'page'), ['priority' => 'normal'])) }}" 
                           class="btn btn-sm {{ request('priority') === 'normal' ? 'btn-success fw-bold active' : 'btn-outline-success' }}">
                            📌 সাধারণ (Normal)
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Main Notice List / Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="card-header bg-white py-3.5 px-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 1.05rem;">
                <i class="fa fa-newspaper text-primary"></i>
                <span>বিজ্ঞপ্তি সমূহের তালিকা (Notice List)</span>
            </h5>
            <span class="badge bg-light text-secondary border px-3 py-1.5 rounded-pill small">
                মোট দেখানো হচ্ছে: {{ $notices->total() }} টি
            </span>
        </div>

        <div class="card-body p-0">
            @if($notices->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="noticeBoardTable">
                        <thead class="bg-light text-muted" style="border-bottom: 2px solid #e2e8f0; font-size: 0.84rem;">
                            <tr>
                                <th style="width: 70px;" class="text-center">ক্র.নং</th>
                                <th style="min-width: 250px;">বিজ্ঞপ্তির শিরোনাম</th>
                                <th style="min-width: 380px;">বিজ্ঞপ্তির বিবরণ</th>
                                <th style="width: 170px;" class="text-center">প্রকাশের সময়</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 0.9rem;">
                            @foreach($notices as $key => $notice)
                                @php
                                    $pBadgeClass = 'bg-success text-white';
                                    $pLabel = '📌 সাধারণ নোটিশ';
                                    if ($notice->priority === 'urgent') {
                                        $pBadgeClass = 'bg-danger text-white';
                                        $pLabel = '🚨 জরুরি বিজ্ঞপ্তি';
                                    } elseif ($notice->priority === 'important') {
                                        $pBadgeClass = 'bg-warning text-dark';
                                        $pLabel = '⚠️ গুরুত্বপূর্ণ নোটিশ';
                                    }
                                @endphp
                                <tr>
                                    <td class="text-center font-monospace fw-bold text-muted">
                                        {{ str_pad($notices->firstItem() + $key, 2, '0', STR_PAD_LEFT) }}
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <div class="fw-bold text-dark fs-6" style="line-height: 1.35;">
                                                {{ $notice->title ?: 'জরুরি বিজ্ঞপ্তি' }}
                                            </div>
                                            <div>
                                                <span class="badge {{ $pBadgeClass }} px-2 py-0.5 rounded-pill" style="font-size: 0.72rem;">
                                                    {{ $pLabel }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-dark" style="line-height: 1.5; word-break: break-word;">
                                            {!! nl2br(e($notice->notice)) !!}
                                        </div>
                                    </td>
                                    <td class="text-center text-muted small">
                                        <div class="fw-semibold text-dark">
                                            <i class="fa fa-calendar-alt text-secondary me-1"></i>
                                            {{ $notice->created_at ? $notice->created_at->format('d M, Y') : '-' }}
                                        </div>
                                        <div class="text-muted" style="font-size: 0.76rem;">
                                            <i class="fa fa-clock text-secondary me-1"></i>
                                            {{ $notice->created_at ? $notice->created_at->format('h:i A') : '' }}
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                @if($notices->hasPages())
                    <div class="d-flex justify-content-between align-items-center p-3 px-4 border-top bg-light flex-wrap gap-2">
                        <small class="text-muted fw-semibold">
                            Showing {{ $notices->firstItem() }} to {{ $notices->lastItem() }} of {{ $notices->total() }} notices
                        </small>
                        <div>
                            {{ $notices->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                @endif

            @else
                <div class="text-center py-5">
                    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center p-4 mb-3 text-secondary" style="width: 70px; height: 70px;">
                        <i class="fa fa-bell-slash fs-2"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">কোনো বিজ্ঞপ্তি পাওয়া যায়নি</h5>
                    <p class="text-muted small mb-3">বর্তমানে আপনার অনুসন্ধানের সাথে মিলে এমন কোনো নোটিশ প্রকাশিত হয়নি।</p>
                    @if(request('search') || request('priority'))
                        <a href="{{ url()->current() }}" class="btn btn-sm btn-outline-primary fw-bold px-3 rounded-pill">
                            <i class="fa fa-sync-alt me-1"></i> ফিল্টার রিসেট করুন
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

</div>

<!-- Notice Full View Modal -->
<div class="modal fade" id="noticeDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header py-3 px-4 text-white" id="modalHeaderBg" style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa fa-bullhorn fs-5"></i>
                    <h5 class="modal-title fw-bold text-white mb-0" id="modalNoticeTitle">বিজ্ঞপ্তির বিস্তারিত</h5>
                </div>
                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-white">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pb-3 mb-3 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <span id="modalNoticeBadge" class="badge bg-danger px-3 py-1.5 rounded-pill fw-bold" style="font-size: 0.8rem;">🚨 জরুরি বিজ্ঞপ্তি</span>
                    </div>
                    <div class="text-muted small">
                        <i class="fa fa-calendar-alt me-1 text-primary"></i> <span id="modalNoticeDate">-</span>
                        <span class="mx-1">|</span>
                        <i class="fa fa-clock me-1 text-primary"></i> <span id="modalNoticeTime">-</span>
                    </div>
                </div>

                <div class="notice-detail-content text-dark p-3 rounded-3 bg-light border" id="modalNoticeBody" style="font-size: 0.95rem; line-height: 1.7; min-height: 140px; white-space: pre-line;">
                </div>
            </div>
            <div class="modal-footer bg-light py-2.5 px-4 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary btn-sm fw-bold px-3" onclick="window.print()">
                    <i class="fa fa-print me-1"></i> প্রিন্ট করুন
                </button>
                <button type="button" class="btn btn-secondary btn-sm fw-bold px-4" data-bs-dismiss="modal">
                    বন্ধ করুন
                </button>
            </div>
        </div>
    </div>
</div>

@push('script')
<script>
function openNoticeDetailModal(data) {
    if (!data) return;

    document.getElementById('modalNoticeTitle').innerText = data.title || 'জরুরি বিজ্ঞপ্তি';
    document.getElementById('modalNoticeBadge').className = 'badge ' + (data.badgeClass || 'bg-primary') + ' px-3 py-1.5 rounded-pill fw-bold';
    document.getElementById('modalNoticeBadge').innerText = data.badgeLabel || 'নোটিশ';
    document.getElementById('modalNoticeDate').innerText = data.date || '-';
    document.getElementById('modalNoticeTime').innerText = data.time || '';

    const bodyEl = document.getElementById('modalNoticeBody');
    if (bodyEl) {
        // If html content, render html, otherwise text
        bodyEl.innerHTML = data.notice || '';
    }

    const modalEl = document.getElementById('noticeDetailModal');
    if (modalEl) {
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
}
</script>
@endpush
@endsection
