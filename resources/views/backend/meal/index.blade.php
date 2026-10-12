@extends('backend.layouts.app')

@section("title")
    | {{$page_title}}
@endsection

@push('style')
    <link rel="stylesheet" href="{{asset('backend')}}/vendor/libs/datatables-bs5/datatables.bootstrap5.css" />
    <link rel="stylesheet" href="{{asset('backend')}}/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css" />
    <link rel="stylesheet" href="{{asset('backend')}}/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css" />
@endpush

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card shadow-sm border-0">
            <div class="card-header border-bottom d-flex flex-wrap justify-content-between align-items-center bg-light py-3 gap-2">
                <h5 class="card-title mb-0 fw-bold text-dark">
                    <i class="ti ti-history me-2 text-primary"></i>{{$page_title}}
                </h5>
                <div>
                    <a href="{{ route('meals.print', ['date' => $selectedDate]) }}" target="_blank" class="btn btn-dark fw-bold shadow-sm">
                        <i class="ti ti-printer me-1"></i> প্রিন্ট / PDF রিপোর্ট সেভ করুন
                    </a>
                </div>
            </div>

            <div class="card-body pt-4">

                <form action="{{ route('meals.index') }}" method="GET" class="row g-3 mb-4 items-stretch">
                    <div class="col-md-4 col-sm-6">
                        <label class="form-label fw-medium text-secondary">Select Date</label>
                        <input type="date"
                               name="date"
                               class="form-control"
                               max="{{ date('Y-m-d') }}"
                               value="{{ request('date', $selected_date ?? now()->format('Y-m-d')) }}">
                    </div>

                    <div class="col-md-2 col-sm-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100 shadow-sm">
                            <i class="ti ti-search me-1"></i>Search
                        </button>
                    </div>

                    <div class="col-md-2 col-sm-3 d-flex align-items-end">
                        <a href="{{ route('meals.index') }}" class="btn btn-secondary w-100 shadow-sm">
                            <i class="ti ti-refresh me-1"></i>Reset
                        </a>
                    </div>

                    <div class="col-md-4 col-sm-12 d-flex align-items-end justify-content-md-end">
                        <a href="{{ route('meals.print', ['date' => $selectedDate]) }}" target="_blank" class="btn btn-outline-dark fw-bold shadow-sm w-100">
                            <i class="ti ti-printer me-1 text-primary"></i> আজকের মিল শিট প্রিন্ট করুন
                        </a>
                    </div>
                </form>

                <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                    <span class="alert-icon text-info me-2">
                        <i class="ti ti-info-circle ti-xs"></i>
                    </span>
                    <div>
                        Showing meal status for <strong class="text-dark">{{ $selectedDate }}</strong>
                    </div>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-xl col-md-4 col-sm-6">
                        <div class="card bg-label-secondary border-0 shadow-none text-center p-3 h-100">
                            <h6 class="mb-1 text-secondary fw-semibold text-xs">Total Member</h6>
                            <h3 class="mb-0 fw-bold text-dark">{{ $summary['total_member'] }}</h3>
                        </div>
                    </div>

                    <div class="col-xl col-md-4 col-sm-6">
                        <div class="card bg-label-success border-0 shadow-none text-center p-3 h-100">
                            <h6 class="mb-1 text-success fw-semibold text-xs">Full Meal On</h6>
                            <h3 class="mb-0 fw-bold text-dark" id="index_summary_full">{{ $summary['total_full_on'] ?? $summary['total_full'] }}</h3>
                        </div>
                    </div>

                    <div class="col-xl col-md-4 col-sm-6">
                        <div class="card bg-label-warning border-0 shadow-none text-center p-3 h-100">
                            <h6 class="mb-1 text-warning fw-semibold text-xs">Half Meal On</h6>
                            <h3 class="mb-0 fw-bold text-dark" id="index_summary_half">{{ $summary['total_half_on'] ?? 0 }}</h3>
                            <div class="small text-muted mt-1" id="index_summary_half_detail">
                                ☀️ {{ $summary['total_day_half'] ?? 0 }} Day | 🌙 {{ $summary['total_night_half'] ?? 0 }} Night
                            </div>
                        </div>
                    </div>

                    <div class="col-xl col-md-6 col-sm-6">
                        <div class="card bg-label-info border-0 shadow-none text-center p-3 h-100">
                            <h6 class="mb-1 text-info fw-semibold text-xs"><i class="ti ti-users me-1"></i>Guest Meals</h6>
                            <h3 class="mb-0 fw-bold text-dark" id="index_summary_guest">{{ (float)($totalGuestMealsCount ?? 0) == (int)($totalGuestMealsCount ?? 0) ? (int)($totalGuestMealsCount ?? 0) : ($totalGuestMealsCount ?? 0) }}</h3>
                            <div class="small text-muted mt-1" id="index_summary_guest_amount">
                                কর্তন: ৳ {{ number_format($totalGuestMealsAmount ?? 0, 0) }}
                            </div>
                        </div>
                    </div>

                    <div class="col-xl col-md-6 col-sm-6">
                        <div class="card bg-label-danger border-0 shadow-none text-center p-3 h-100">
                            <h6 class="mb-1 text-danger fw-semibold text-xs">Total Off</h6>
                            <h3 class="mb-0 fw-bold text-dark" id="index_summary_off">{{ $summary['total_off'] }}</h3>
                        </div>
                    </div>
                </div>

                @php
                    $mealMap = collect($meals)->keyBy('user_id');
                @endphp

                <!-- Search & Page Size Toolbar -->
                <div class="card border border-light-subtle shadow-sm mb-3 bg-white rounded-3">
                    <div class="card-body p-3">
                        <div class="row g-2 align-items-center justify-content-between">
                            <div class="col-md-6 col-sm-12">
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-primary border-end-0">
                                        <i class="ti ti-search fs-5"></i>
                                    </span>
                                    <input type="text" id="memberTableSearch" class="form-control border-start-0" placeholder="মেম্বারের নাম বা ফোন নম্বর খুঁজুন (Search by name or phone)...">
                                    <button class="btn btn-outline-secondary d-none" type="button" id="clearMemberSearch" title="Clear">
                                        <i class="ti ti-x"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 d-flex align-items-center justify-content-md-end gap-2 ms-auto">
                                <label for="tablePageSize" class="form-label mb-0 small text-muted text-nowrap">Show:</label>
                                <select id="tablePageSize" class="form-select form-select-sm" style="width: 85px;">
                                    <option value="10">10</option>
                                    <option value="25" selected>25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                    <option value="all">All</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive border rounded">
                    <table class="table table-hover table-striped align-middle mb-0" id="mealManagementTable">
                        <thead class="table-light">
                        <tr>
                            <th style="width: 60px;" class="fw-bold text-secondary text-uppercase">SL</th>
                            <th class="fw-bold text-secondary text-uppercase">Member Name</th>
                            <th class="fw-bold text-secondary text-uppercase">Phone</th>
                            <th class="fw-bold text-secondary text-uppercase">Floor</th>
                            <th class="fw-bold text-secondary text-uppercase text-center">Room No</th>
                            <th class="fw-bold text-secondary text-uppercase text-center">Seat No</th>
                            <th class="fw-bold text-secondary text-uppercase text-center">Booking</th>
                            <th class="text-center fw-bold text-secondary text-uppercase" style="width: 150px;">Meal Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($users as $key => $user)
                            @php
                                $meal = $mealMap->get($user->id);
                                $isOff = $meal ? (bool)$meal->is_off : false;
                                $isFull = $meal && $meal->full_meal && !$isOff;
                                $isHalf = $meal && $meal->half_meal && !$isOff;
                                $mealNote = strtolower($meal ? ($meal->note ?? '') : '');
                                $isHalfDay = $isHalf && ($mealNote === 'day' || $mealNote === 'half_day');
                                $isHalfNight = $isHalf && ($mealNote === 'night' || $mealNote === 'half_night');
                            @endphp
                            <tr id="row_user_{{ $user->id }}"
                                class="meal-status-data-row"
                                data-name="{{ strtolower($user->name) }}"
                                data-phone="{{ preg_replace('/[^0-9]/', '', $user->phone ?? '') }}">
                                <td><span class="text-muted font-monospace row-sl-number">{{ str_pad($key + 1, 2, '0', STR_PAD_LEFT) }}</span></td>
                                <td>
                                    <div class="fw-semibold text-heading">{{ $user->name }}</div>
                                </td>
                                <td><small class="text-secondary font-monospace">{{ $user->phone ?? '-' }}</small></td>

                                <td>
                                    @if($user->booking_floor && $user->booking_floor !== '-')
                                        <span class="badge bg-label-info fw-bold">{{ $user->booking_floor }}</span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    @if($user->booking_room && $user->booking_room !== '-')
                                        <span class="badge bg-label-primary font-monospace px-2.5 py-1 fs-6 fw-bold">{{ $user->booking_room }}</span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    @if($user->booking_seat && $user->booking_seat !== '-')
                                        <span class="badge bg-label-danger font-monospace px-2.5 py-1 fs-6 fw-bold">{{ $user->booking_seat }}</span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    @if(isset($user->active_booking) && $user->active_booking)
                                        <span class="badge bg-label-success fw-semibold">#{{ $user->active_booking->id }} Active</span>
                                    @else
                                        <span class="badge bg-label-secondary small">No Booking</span>
                                    @endif
                                </td>

                                <td class="text-center" id="cell_meal_status_{{ $user->id }}">
                                    @if($isFull)
                                        <span class="badge bg-label-success px-2.5 py-1 fw-bold"><i class="ti ti-check me-1"></i>Full Meal (ON)</span>
                                    @elseif($isHalf && $isHalfNight)
                                        <span class="badge bg-label-info px-2.5 py-1 fw-bold">🌙 Half (Night)</span>
                                    @elseif($isHalf)
                                        <span class="badge bg-label-warning text-dark px-2.5 py-1 fw-bold">☀️ Half (Day)</span>
                                    @elseif(str_contains($mealNote, 'checked in') || str_contains($mealNote, 'resumes tomorrow') || (str_contains($mealNote, 'check') && str_contains($mealNote, 'in')))
                                        <span class="badge bg-label-success px-2.5 py-1 fw-bold" title="হোস্টেলে চেক-ইন সম্পন্ন (আগামীকাল থেকে মিল চালু হবে)"><i class="ti ti-door-enter me-1"></i>চেক-ইন (আগামীকাল থেকে মিল চালু)</span>
                                    @elseif(str_contains($mealNote, 'leave') || str_contains($mealNote, 'checked out') || str_contains($mealNote, 'check'))
                                        <span class="badge bg-label-danger px-2.5 py-1 fw-bold" title="যতক্ষণ বাইরে থাকবেন চেক-আউট থাকায় মিল বন্ধ"><i class="ti ti-plane-departure me-1"></i>চেক-আউট (মিল বন্ধ)</span>
                                    @elseif(str_contains($mealNote, 'zero deposit'))
                                        <span class="badge bg-label-danger px-2.5 py-1 fw-bold" title="ডিপোজিট জমা না থাকায় মিল বন্ধ"><i class="ti ti-alert-triangle me-1"></i>Zero Deposit</span>
                                    @else
                                        <span class="badge bg-label-danger px-2.5 py-1 fw-bold"><i class="ti ti-x me-1"></i>Meal OFF</span>
                                    @endif

                                    @if(isset($guestMealsByUser[$user->id]) && $guestMealsByUser[$user->id]->isNotEmpty())
                                        @foreach($guestMealsByUser[$user->id] as $gm)
                                            <div class="mt-1">
                                                <span class="badge bg-label-info px-2 py-0.5 fw-bold" style="font-size: 0.72rem;" title="গেস্ট মিল: {{ $gm->guest_name }} (৳{{ number_format($gm->amount, 0) }})">
                                                    <i class="ti ti-users me-1"></i>Guest Meal: {{ (float)$gm->meal_count == (int)$gm->meal_count ? (int)$gm->meal_count : $gm->meal_count }} টি ({{ $gm->guest_name }})
                                                </span>
                                            </div>
                                        @endforeach
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyMealManagementDefaultRow">
                                <td colspan="8" class="text-center text-muted py-4">কোনো মেম্বার পাওয়া যায়নি (No users found)</td>
                            </tr>
                        @endforelse

                        <tr id="noMatchingMealRow" class="d-none">
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="ti ti-search-off fs-1 text-secondary mb-2 d-block"></i>
                                <h6 class="fw-bold text-secondary mb-1">কোনো মেম্বার পাওয়া যায়নি</h6>
                                <p class="small text-muted mb-0">আপনার সার্চ করা নাম বা ফোন নম্বরের সাথে কোনো মেম্বার মেলেনি।</p>
                            </td>
                        </tr>
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td colspan="4" class="text-end text-dark">সর্বমোট মেম্বার ও মিলের সারসংক্ষেপ (Total Summary):</td>
                                <td colspan="4" class="text-start">
                                    <span class="badge bg-label-primary fs-7 me-1">মোট সদস্য: {{ count($users) }} জন</span>
                                    <span class="badge bg-label-success fs-7 me-1">ফুল চালু: {{ $summary['total_full_on'] ?? $summary['total_full'] }} টি</span>
                                    <span class="badge bg-label-warning text-dark fs-7 me-1">হাফ চালু: {{ $summary['total_half_on'] ?? 0 }} টি</span>
                                    <span class="badge bg-label-info fs-7 me-1"><i class="ti ti-users me-1"></i>গেস্ট মিল: {{ (float)($totalGuestMealsCount ?? 0) == (int)($totalGuestMealsCount ?? 0) ? (int)($totalGuestMealsCount ?? 0) : ($totalGuestMealsCount ?? 0) }} টি</span>
                                    <span class="badge bg-label-danger fs-7 me-1">মিল বন্ধ: {{ $summary['total_off'] }} জন</span>
                                    <span class="badge bg-label-dark fs-7">সর্বমোট মিল: {{ ($summary['total_full_on'] ?? $summary['total_full']) + (($summary['total_half_on'] ?? 0) * 0.5) + ($totalGuestMealsCount ?? 0) }} টি</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Table Pagination & Record Count Footer -->
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-3 px-1 border-top mt-2" id="tablePaginationWrapper">
                    <div class="small text-muted fw-semibold" id="tableRecordCountInfo">
                        Showing <span id="pageShowingStart">1</span> to <span id="pageShowingEnd">{{ count($users) > 25 ? 25 : count($users) }}</span> of <span id="pageTotalRecords">{{ count($users) }}</span> members
                    </div>
                    <nav aria-label="Table pagination">
                        <ul class="pagination pagination-sm mb-0" id="tablePaginationList">
                            <!-- Dynamic pagination items -->
                        </ul>
                    </nav>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{asset('backend')}}/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
    <script src="{{asset('backend')}}/vendor/libs/datatables-bs5/custom-datatable5.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Client-side Search and Pagination for Meal Management
            let currentPage = 1;
            let currentPageSize = '25';
            let currentSearchTerm = '';

            const searchInput = document.getElementById('memberTableSearch');
            const clearSearchBtn = document.getElementById('clearMemberSearch');
            const pageSizeSelect = document.getElementById('tablePageSize');

            function filterAndPaginateTable() {
                const allRows = Array.from(document.querySelectorAll('#mealManagementTable tbody tr.meal-status-data-row'));
                const noMatchRow = document.getElementById('noMatchingMealRow');
                const defaultEmptyRow = document.getElementById('emptyMealManagementDefaultRow');
                const showingStartEl = document.getElementById('pageShowingStart');
                const showingEndEl = document.getElementById('pageShowingEnd');
                const totalRecordsEl = document.getElementById('pageTotalRecords');
                const paginationList = document.getElementById('tablePaginationList');

                if (allRows.length === 0) {
                    if (showingStartEl) showingStartEl.innerText = '0';
                    if (showingEndEl) showingEndEl.innerText = '0';
                    if (totalRecordsEl) totalRecordsEl.innerText = '0';
                    if (paginationList) paginationList.innerHTML = '';
                    return;
                }

                const term = (currentSearchTerm || '').trim().toLowerCase();

                // Filter rows
                const matchingRows = allRows.filter(row => {
                    const name = (row.getAttribute('data-name') || '').toLowerCase();
                    const phone = (row.getAttribute('data-phone') || '');
                    const nameText = row.querySelector('.fw-semibold.text-heading')?.textContent?.toLowerCase() || '';
                    return !term || name.includes(term) || phone.includes(term) || nameText.includes(term);
                });

                const totalMatching = matchingRows.length;

                if (totalMatching === 0) {
                    allRows.forEach(row => row.classList.add('d-none'));
                    if (noMatchRow) noMatchRow.classList.remove('d-none');
                    if (defaultEmptyRow) defaultEmptyRow.classList.add('d-none');
                    if (showingStartEl) showingStartEl.innerText = '0';
                    if (showingEndEl) showingEndEl.innerText = '0';
                    if (totalRecordsEl) totalRecordsEl.innerText = '0';
                    if (paginationList) paginationList.innerHTML = '';
                    return;
                }

                if (noMatchRow) noMatchRow.classList.add('d-none');
                if (defaultEmptyRow) defaultEmptyRow.classList.add('d-none');

                const pageSize = currentPageSize === 'all' ? totalMatching : parseInt(currentPageSize, 10);
                const totalPages = Math.ceil(totalMatching / pageSize);

                if (currentPage > totalPages) currentPage = totalPages;
                if (currentPage < 1) currentPage = 1;

                const startIndex = (currentPage - 1) * pageSize;
                const endIndex = currentPageSize === 'all' ? totalMatching : Math.min(startIndex + pageSize, totalMatching);

                allRows.forEach(row => row.classList.add('d-none'));

                matchingRows.forEach((row, idx) => {
                    if (idx >= startIndex && idx < endIndex) {
                        row.classList.remove('d-none');
                        const slSpan = row.querySelector('.row-sl-number');
                        if (slSpan) {
                            slSpan.innerText = String(idx + 1).padStart(2, '0');
                        }
                    }
                });

                if (showingStartEl) showingStartEl.innerText = String(startIndex + 1);
                if (showingEndEl) showingEndEl.innerText = String(endIndex);
                if (totalRecordsEl) totalRecordsEl.innerText = String(totalMatching);

                renderPaginationButtons(totalPages, totalMatching);
            }

            function renderPaginationButtons(totalPages, totalMatching) {
                const paginationList = document.getElementById('tablePaginationList');
                if (!paginationList) return;

                if (totalPages <= 1) {
                    paginationList.innerHTML = '';
                    return;
                }

                let html = '';

                // Prev Button
                html += `
                    <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="javascript:void(0);" data-page="${currentPage - 1}" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                `;

                const maxButtons = 5;
                let startPage = Math.max(1, currentPage - 2);
                let endPage = Math.min(totalPages, startPage + maxButtons - 1);
                if (endPage - startPage < maxButtons - 1) {
                    startPage = Math.max(1, endPage - maxButtons + 1);
                }

                if (startPage > 1) {
                    html += `<li class="page-item"><a class="page-link" href="javascript:void(0);" data-page="1">1</a></li>`;
                    if (startPage > 2) {
                        html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                    }
                }

                for (let p = startPage; p <= endPage; p++) {
                    html += `
                        <li class="page-item ${p === currentPage ? 'active' : ''}">
                            <a class="page-link" href="javascript:void(0);" data-page="${p}">${p}</a>
                        </li>
                    `;
                }

                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) {
                        html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                    }
                    html += `<li class="page-item"><a class="page-link" href="javascript:void(0);" data-page="${totalPages}">${totalPages}</a></li>`;
                }

                // Next Button
                html += `
                    <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                        <a class="page-link" href="javascript:void(0);" data-page="${currentPage + 1}" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                `;

                paginationList.innerHTML = html;
            }

            // Pagination click listener
            const paginationListEl = document.getElementById('tablePaginationList');
            if (paginationListEl) {
                paginationListEl.addEventListener('click', function (e) {
                    const link = e.target.closest('a.page-link');
                    if (!link) return;
                    e.preventDefault();
                    const targetPage = parseInt(link.getAttribute('data-page'), 10);
                    if (!isNaN(targetPage) && targetPage !== currentPage && targetPage >= 1) {
                        currentPage = targetPage;
                        filterAndPaginateTable();
                    }
                });
            }

            // Search input listener
            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    currentSearchTerm = this.value;
                    currentPage = 1;
                    if (clearSearchBtn) {
                        if (currentSearchTerm.length > 0) {
                            clearSearchBtn.classList.remove('d-none');
                        } else {
                            clearSearchBtn.classList.add('d-none');
                        }
                    }
                    filterAndPaginateTable();
                });
            }

            // Clear search button listener
            if (clearSearchBtn) {
                clearSearchBtn.addEventListener('click', function () {
                    if (searchInput) {
                        searchInput.value = '';
                        currentSearchTerm = '';
                    }
                    clearSearchBtn.classList.add('d-none');
                    currentPage = 1;
                    filterAndPaginateTable();
                    if (searchInput) searchInput.focus();
                });
            }

            // Page size dropdown listener
            if (pageSizeSelect) {
                pageSizeSelect.addEventListener('change', function () {
                    currentPageSize = this.value;
                    currentPage = 1;
                    filterAndPaginateTable();
                });
            }

            // Initialize table pagination
            filterAndPaginateTable();

            // Real-time live background sync without page reload (every 4 seconds)
            function syncLiveMealManagementStatus() {
                fetch('{{ route("meals.live-status") }}?date={{ $selectedDate }}', {
                    headers: { 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(data => {
                    if (data && data.success && data.meals) {
                        Object.keys(data.meals).forEach(userId => {
                            const meal = data.meals[userId];
                            const cell = document.getElementById('cell_meal_status_' + userId);
                            if (!cell) return;

                            let statusHtml = '';
                            if (meal.is_returned_today || noteStr.includes('checked in') || noteStr.includes('resumes tomorrow') || (noteStr.includes('check') && noteStr.includes('in'))) {
                                statusHtml = '<span class="badge bg-label-success px-2.5 py-1 fw-bold" title="হোস্টেলে চেক-ইন সম্পন্ন (আগামীকাল থেকে মিল চালু হবে)"><i class="ti ti-door-enter me-1"></i>চেক-ইন (আগামীকাল থেকে মিল চালু)</span>';
                            } else if (meal.is_outside || meal.is_leave || noteStr.includes('leave') || noteStr.includes('check')) {
                                statusHtml = '<span class="badge bg-label-danger px-2.5 py-1 fw-bold" title="যতক্ষণ বাইরে থাকবেন চেক-আউট থাকায় মিল বন্ধ"><i class="ti ti-plane-departure me-1"></i>চেক-আউট (মিল বন্ধ)</span>';
                            } else if (meal.is_zero_deposit) {
                                statusHtml = '<span class="badge bg-label-danger px-2.5 py-1 fw-bold" title="ডিপোজিট জমা না থাকায় মিল বন্ধ"><i class="ti ti-alert-triangle me-1"></i>Zero Deposit</span>';
                            } else if (meal.full_meal) {
                                statusHtml = '<span class="badge bg-label-success px-2.5 py-1 fw-bold"><i class="ti ti-check me-1"></i>Full Meal (ON)</span>';
                            } else if (meal.half_meal) {
                                if (meal.is_half_night) {
                                    statusHtml = '<span class="badge bg-label-info px-2.5 py-1 fw-bold">🌙 Half (Night)</span>';
                                } else {
                                    statusHtml = '<span class="badge bg-label-warning text-dark px-2.5 py-1 fw-bold">☀️ Half (Day)</span>';
                                }
                            } else {
                                statusHtml = '<span class="badge bg-label-danger px-2.5 py-1 fw-bold"><i class="ti ti-x me-1"></i>Meal OFF</span>';
                            }

                            if (meal.guest_meals && meal.guest_meals.length > 0) {
                                meal.guest_meals.forEach(gm => {
                                    statusHtml += `
                                        <div class="mt-1">
                                            <span class="badge bg-label-info px-2 py-0.5 fw-bold" style="font-size: 0.72rem;" title="গেস্ট মিল: ${gm.guest_name} (৳${gm.amount || 0})">
                                                <i class="ti ti-users me-1"></i>Guest Meal: ${gm.meal_count} টি (${gm.guest_name})
                                            </span>
                                        </div>
                                    `;
                                });
                            }

                            cell.innerHTML = statusHtml;
                        });

                        if (data.today_summary) {
                            const sumFull = document.getElementById('index_summary_full');
                            const sumHalf = document.getElementById('index_summary_half');
                            const sumHalfDet = document.getElementById('index_summary_half_detail');
                            const sumGuest = document.getElementById('index_summary_guest');
                            const sumGuestAmount = document.getElementById('index_summary_guest_amount');
                            const sumOff = document.getElementById('index_summary_off');

                            if (sumFull) sumFull.innerText = data.today_summary.total_full_meal;
                            if (sumHalf) sumHalf.innerText = data.today_summary.total_half_meal;
                            if (sumHalfDet) sumHalfDet.innerText = `☀️ ${data.today_summary.half_day_count || 0} Day | 🌙 ${data.today_summary.half_night_count || 0} Night`;
                            if (sumGuest && data.total_guest_meals !== undefined) sumGuest.innerText = data.total_guest_meals;
                            if (sumGuestAmount && data.total_guest_amount !== undefined) sumGuestAmount.innerText = `কর্তন: ৳ ${Number(data.total_guest_amount).toLocaleString('en-US')}`;
                            if (sumOff) sumOff.innerText = Object.values(data.meals).filter(m => m.is_off).length;
                        }
                    }
                })
                .catch(() => {});
            }

            setInterval(syncLiveMealManagementStatus, 4000);
        });
    </script>
@endpush
