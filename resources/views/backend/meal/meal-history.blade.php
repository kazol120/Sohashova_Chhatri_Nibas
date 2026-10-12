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
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">{{$page_title}}</h5>
            </div>

            <div class="card-body">

                <form action="{{ route('meal-history') }}" method="GET" class="row g-3 mb-4">
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
                        <a href="{{ route('meals.index') }}" class="btn btn-secondary w-100">
                            Reset
                        </a>
                    </div>
                </form>

                <div class="alert alert-info d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        Showing meal history for
                        <strong>{{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}</strong>
                        <br>
                        Full Meal Rate: <strong>৳ {{ number_format($full_rate, 2) }}</strong> | Half Meal Rate: <strong>৳ {{ number_format($half_rate, 2) }}</strong>
                    </div>
                    <div>
                        <a href="{{ route('setting.index', 'meal_setting') }}" class="btn btn-sm btn-outline-primary fw-bold shadow-sm rounded-pill px-3">
                            <i class="ti ti-edit me-1"></i> মিলের রেট ও স্পেশাল মিল শিডিউল
                        </a>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card border-primary h-100">
                            <div class="card-body text-center">
                                <h6 class="mb-2">Total Member</h6>
                                <h4 class="mb-0">{{ $summary['total_member'] }}</h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border-success h-100">
                            <div class="card-body text-center">
                                <h6 class="mb-2">Total Meals</h6>
                                <h4 class="mb-0">{{ (float)($summary['total_meal'] ?? 0) == (int)($summary['total_meal'] ?? 0) ? (int)($summary['total_meal'] ?? 0) : number_format($summary['total_meal'], 1) }}</h4>
                                @if(($summary['total_half'] ?? 0) > 0)
                                    <small class="text-muted d-block mt-1">({{ $summary['total_full'] ?? 0 }} Full, {{ $summary['total_half'] ?? 0 }} Half)</small>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border-warning h-100">
                            <div class="card-body text-center">
                                <h6 class="mb-2">Total Meal Cost</h6>
                                <h4 class="mb-0">৳ {{ number_format($summary['total_meal_cost'] ?? 0, 2) }}</h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border-info h-100">
                            <div class="card-body text-center">
                                <h6 class="mb-2">Total Deposit</h6>
                                <h4 class="mb-0">৳ {{ number_format($summary['total_deposit'] ?? 0, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
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

                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle mb-0" id="mealHistoryTable">
                        <thead class="table-dark">
                        <tr>
                            <th style="width: 70px;">SL</th>
                            <th>Member Name</th>
                            <th>Phone</th>
                            <th class="text-center">Total Meals</th>
                            <th class="text-center">Meal Cost</th>
                            <th class="text-center">Deposit</th>
                            <th class="text-center">Balance</th>
                            <th class="text-center" style="width: 110px;">Action</th>
                        </tr>
                        </thead>

                        <tbody>
                        @forelse($users as $key => $user)
                            @php
                                $userMeal = $mealSummaryByUser[$user->id] ?? (object) [
                                    'full_total' => 0,
                                    'half_total' => 0,
                                    'total_meal' => 0,
                                    'meal_cost' => 0,
                                    'deposit_amount' => 0,
                                    'balance' => 0,
                                ];

                                $balance = $userMeal->balance ?? 0;
                                $userTotalMeals = (float)($userMeal->total_meal ?? 0);
                            @endphp

                            <tr class="meal-history-data-row"
                                data-name="{{ strtolower($user->name) }}"
                                data-phone="{{ preg_replace('/[^0-9]/', '', $user->phone ?? '') }}">
                                <td><span class="row-sl-number">{{ $key + 1 }}</span></td>
                                <td class="fw-bold text-dark">{{ $user->name }}</td>
                                <td>{{ $user->phone ?? '-' }}</td>
                                <td class="text-center fw-bold">
                                    {{ $userTotalMeals == (int)$userTotalMeals ? (int)$userTotalMeals : number_format($userTotalMeals, 1) }}
                                    @if(($userMeal->half_total ?? 0) > 0)
                                        <small class="text-muted d-block fw-normal" style="font-size: 11px;">({{ $userMeal->full_total }} Full, {{ $userMeal->half_total }} Half)</small>
                                    @endif
                                </td>
                                <td class="text-center fw-bold">৳ {{ number_format($userMeal->meal_cost, 2) }}</td>
                                <td class="text-center">৳ {{ number_format($userMeal->deposit_amount, 2) }}</td>
                                <td class="text-center">
                                    @if($balance < 0)
                                        <span class="badge bg-danger">৳ {{ number_format($balance, 2) }}</span>
                                    @elseif($balance > 0)
                                        <span class="badge bg-success">৳ {{ number_format($balance, 2) }}</span>
                                    @else
                                        <span class="badge bg-secondary">৳ {{ number_format($balance, 2) }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('meal-history.resident-detail', $user->id) }}?month={{ request('month', $selectedMonth ?? now()->format('Y-m')) }}" 
                                       class="btn btn-outline-primary btn-sm px-2 py-1 shadow-sm" 
                                       title="সম্পূর্ণ মিল তালিকা দেখুন"
                                       target="_blank">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyMealHistoryDefaultRow">
                                <td colspan="8" class="text-center py-4 text-muted">কোনো মেম্বার পাওয়া যায়নি (No users found)</td>
                            </tr>
                        @endforelse

                        <tr id="noMatchingMealHistoryRow" class="d-none">
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="ti ti-search-off fs-1 text-secondary mb-2 d-block"></i>
                                <h6 class="fw-bold text-secondary mb-1">কোনো মেম্বার পাওয়া যায়নি</h6>
                                <p class="small text-muted mb-0">আপনার সার্চ করা নাম বা ফোন নম্বরের সাথে কোনো মেম্বার মেলেনি।</p>
                            </td>
                        </tr>
                        </tbody>

                        <tfoot>
                        <tr class="table-secondary fw-bold">
                            <td colspan="3" class="text-end">Grand Total</td>
                            <td class="text-center">{{ $summary['total_meal'] ?? 0 }}</td>
                            <td class="text-center">৳ {{ number_format($summary['total_meal_cost'] ?? 0, 2) }}</td>
                            <td class="text-center">৳ {{ number_format($summary['total_deposit'] ?? 0, 2) }}</td>
                            <td class="text-center">
                                @php
                                    $grandBalance = $summary['total_balance'] ?? 0;
                                @endphp

                                @if($grandBalance < 0)
                                    <span class="badge bg-danger">৳ {{ number_format($grandBalance, 2) }}</span>
                                @elseif($grandBalance > 0)
                                    <span class="badge bg-success">৳ {{ number_format($grandBalance, 2) }}</span>
                                @else
                                    <span class="badge bg-secondary">৳ {{ number_format($grandBalance, 2) }}</span>
                                @endif
                            </td>
                            <td></td>
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
            // Client-side Search and Pagination for Meal History
            let currentPage = 1;
            let currentPageSize = '25';
            let currentSearchTerm = '';

            const searchInput = document.getElementById('memberTableSearch');
            const clearSearchBtn = document.getElementById('clearMemberSearch');
            const pageSizeSelect = document.getElementById('tablePageSize');

            function filterAndPaginateTable() {
                const allRows = Array.from(document.querySelectorAll('#mealHistoryTable tbody tr.meal-history-data-row'));
                const noMatchRow = document.getElementById('noMatchingMealHistoryRow');
                const defaultEmptyRow = document.getElementById('emptyMealHistoryDefaultRow');
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
                    const nameText = row.querySelector('td:nth-child(2)')?.textContent?.toLowerCase() || '';
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
                            slSpan.innerText = String(idx + 1);
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
        });
    </script>
@endpush
