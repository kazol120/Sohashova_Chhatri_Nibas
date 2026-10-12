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
        <div class="card shadow-sm border-0">
            <div class="card-header bg-light border-bottom d-flex flex-wrap justify-content-between align-items-center py-3 gap-2">
                <h5 class="card-title mb-0 fw-bold text-dark d-flex align-items-center">
                    <i class="ti ti-cash-refund me-2 text-danger fs-4"></i>{{$page_title}}
                </h5>
                <div>
                    <button type="button" class="btn btn-danger fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addRefundModal">
                        <i class="ti ti-plus me-1"></i> নতুন ডিপোজিট ফেরত দিন (Add Refund)
                    </button>
                </div>
            </div>

            <div class="card-body pt-4">

                <!-- Month Filter Form -->
                <form action="{{ route('deposit-refunds.index') }}" method="GET" class="row g-3 mb-4 align-items-end">
                    <div class="col-md-4 col-sm-6">
                        <label class="form-label fw-medium text-secondary">মাস নির্বাচন করুন (Select Month)</label>
                        <input type="month"
                               name="month"
                               class="form-control"
                               max="{{ now()->format('Y-m') }}"
                               value="{{ request('month', $selectedMonth ?? now()->format('Y-m')) }}">
                    </div>

                    <div class="col-md-2 col-sm-3">
                        <button type="submit" class="btn btn-primary w-100 shadow-sm">
                            <i class="ti ti-search me-1"></i>Search
                        </button>
                    </div>

                    <div class="col-md-2 col-sm-3">
                        <a href="{{ route('deposit-refunds.index') }}" class="btn btn-secondary w-100 shadow-sm">
                            <i class="ti ti-refresh me-1"></i>Reset
                        </a>
                    </div>
                </form>

                <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                    <span class="alert-icon text-danger me-2">
                        <i class="ti ti-info-circle ti-xs"></i>
                    </span>
                    <div>
                        দেখানো হচ্ছে <strong>{{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}</strong> মাসের মিল ডিপোজিট ফেরতের বিবরণী।
                    </div>
                </div>

                <!-- Stat Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4 col-sm-6">
                        <div class="card bg-label-primary border-0 shadow-none text-center p-3 h-100">
                            <h6 class="mb-1 text-primary fw-semibold text-xs text-uppercase">Total Refund Members</h6>
                            <h3 class="mb-0 fw-bold text-dark">{{ $totalRefundMembers }} জন</h3>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6">
                        <div class="card bg-label-warning border-0 shadow-none text-center p-3 h-100">
                            <h6 class="mb-1 text-warning fw-semibold text-xs text-uppercase">Total Refund Count</h6>
                            <h3 class="mb-0 fw-bold text-dark">{{ $totalRefundCount }} বার</h3>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12">
                        <div class="card bg-label-danger border-0 shadow-none text-center p-3 h-100">
                            <h6 class="mb-1 text-danger fw-semibold text-xs text-uppercase">Total Refund Amount</h6>
                            <h3 class="mb-0 fw-bold text-danger font-monospace">
                                ৳ {{ number_format($totalRefundAmount, 2) }}
                            </h3>
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
                                    <input type="text" id="refundTableSearch" class="form-control border-start-0" placeholder="মেম্বারের নাম বা ফোন নম্বর দিয়ে খুঁজুন (Search by name or phone)...">
                                    <button class="btn btn-outline-secondary d-none" type="button" id="clearRefundSearch" title="Clear">
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

                <!-- Refunds Table -->
                <div class="table-responsive border rounded">
                    <table class="table table-bordered table-hover align-middle mb-0" id="refundListTable">
                        <thead class="table-dark">
                        <tr>
                            <th style="width: 50px;" class="text-center">SL</th>
                            <th>Member Name (মেম্বারের নাম)</th>
                            <th>Phone (মোবাইল)</th>
                            <th class="text-center">Room / Seat (সিট নং)</th>
                            <th class="text-center">Refund Date (ফেরতের তারিখ)</th>
                            <th class="text-center" style="width: 170px;">Refund Amount (ফেরতের পরিমাণ)</th>
                            <th>Reason / Note (কারণ / নোট)</th>
                            <th class="text-center">Processed By (প্রদানকারী)</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($refunds as $refund)
                            @php
                                $uName = $refund->user->name ?? 'N/A';
                                $uPhone = $refund->user->phone ?? '-';
                                $amount = abs($refund->amount);
                            @endphp
                            <tr class="refund-data-row"
                                data-name="{{ strtolower($uName) }}"
                                data-phone="{{ preg_replace('/[^0-9]/', '', $uPhone) }}">
                                <td class="text-center fw-bold"><span class="row-sl-number">{{ $loop->iteration }}</span></td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $uName }}</div>
                                </td>
                                <td>
                                    <span class="font-monospace text-secondary fw-semibold">{{ $uPhone }}</span>
                                </td>
                                <td class="text-center">
                                    @if(!empty($refund->user->booking_room) && $refund->user->booking_room !== '-')
                                        <span class="badge bg-label-primary font-monospace">{{ $refund->user->booking_room }}</span>
                                        @if(!empty($refund->user->booking_seat) && $refund->user->booking_seat !== '-')
                                            <span class="badge bg-label-danger font-monospace ms-1">{{ $refund->user->booking_seat }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-label-secondary font-monospace fw-semibold">
                                        <i class="ti ti-calendar me-1"></i>{{ \Carbon\Carbon::parse($refund->date)->format('d M, Y') }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-danger fs-6 px-2.5 py-1 fw-bold font-monospace shadow-sm">
                                        - ৳ {{ number_format($amount, 2) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-dark">{{ $refund->note ?: '-' }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-label-info">{{ $refund->madeBy->name ?? 'Admin' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyRefundDefaultRow">
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="ti ti-cash-off fs-1 text-secondary mb-2 d-block"></i>
                                    <h6 class="fw-bold text-secondary mb-1">এই মাসে কোনো ডিপোজিট ফেরত নেই</h6>
                                    <p class="small text-muted mb-0">কোনো মেম্বারকে টাকা ফেরত দিলে উপরের "নতুন ডিপোজিট ফেরত দিন" বাটনে ক্লিক করে এন্ট্রি করুন।</p>
                                </td>
                            </tr>
                        @endforelse

                        <tr id="noMatchingRefundRow" class="d-none">
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="ti ti-search-off fs-1 text-secondary mb-2 d-block"></i>
                                <h6 class="fw-bold text-secondary mb-1">কোনো রেকর্ড পাওয়া যায়নি</h6>
                                <p class="small text-muted mb-0">আপনার সার্চ করা নাম বা ফোন নম্বরের সাথে কোনো রিফান্ড তথ্য মেলেনি।</p>
                            </td>
                        </tr>
                        </tbody>
                        <tfoot>
                        <tr class="table-danger fw-bold">
                            <td colspan="5" class="text-end fs-6 text-dark">Grand Total (সর্বমোট ফেরতকৃত টাকা):</td>
                            <td class="text-center fs-6 text-danger font-monospace">
                                - ৳ {{ number_format($totalRefundAmount, 2) }}
                            </td>
                            <td colspan="2"></td>
                        </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Table Pagination & Record Count Footer -->
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-3 px-1 border-top mt-2" id="tablePaginationWrapper">
                    <div class="small text-muted fw-semibold" id="tableRecordCountInfo">
                        Showing <span id="pageShowingStart">1</span> to <span id="pageShowingEnd">{{ count($refunds) > 25 ? 25 : count($refunds) }}</span> of <span id="pageTotalRecords">{{ count($refunds) }}</span> records
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

    <!-- Add Refund Modal -->
    <div class="modal fade" id="addRefundModal" tabindex="-1" aria-labelledby="addRefundModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <form id="refundForm" action="{{ route('deposit-refunds.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-danger text-white py-3">
                        <h5 class="modal-title text-white fw-bold mb-0" id="addRefundModalLabel">
                            <i class="ti ti-cash-refund me-2"></i>ডিপোজিট ফেরত এন্ট্রি (Add Deposit Refund)
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        {{-- Select Resident --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">মেম্বার নির্বাচন করুন (Select Resident) <span class="text-danger">*</span></label>
                            <select name="user_id" id="refund_user_id" class="select2-refund form-select" style="width: 100%" required>
                                <option value="">-- মেম্বার বেছে নিন --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">
                                        {{ $user->name }} ({{ $user->phone ?? 'No phone' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Dynamic Resident Balance Info Box --}}
                        <div id="userBalanceBox" class="card bg-light border p-3 mb-3 d-none">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted small">মোট ডিপোজিট জমা:</span>
                                <strong class="text-success font-monospace" id="displayDepositTotal">৳ 0.00</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted small">বর্তমান মিল খরচ:</span>
                                <strong class="text-danger font-monospace" id="displayMealCost">৳ 0.00</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                <span class="fw-bold text-dark small">সর্বোচ্চ ফেরতযোগ্য ব্যালেন্স:</span>
                                <strong class="fs-5 text-primary font-monospace" id="displayAvailableBalance">৳ 0.00</strong>
                            </div>
                        </div>

                        {{-- Refund Amount --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">ফেরতের পরিমাণ (Refund Amount ৳) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-danger fw-bold">৳</span>
                                <input type="number" step="0.01" min="1" name="amount" id="refundAmountInput" class="form-control font-monospace fs-5 fw-bold" placeholder="যেমন: 500" required>
                            </div>
                            <div class="form-text text-muted" id="amountHelperText">যে পরিমাণ টাকা মেম্বারকে ফেরত দেওয়া হচ্ছে।</div>
                        </div>

                        {{-- Refund Date --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">ফেরতের তারিখ (Refund Date) <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" max="{{ now()->format('Y-m-d') }}" value="{{ now()->format('Y-m-d') }}" required>
                        </div>

                        {{-- Note / Reason --}}
                        <div class="mb-2">
                            <label class="form-label fw-bold text-dark">ফেরতের কারণ বা নোট (Note / Reason)</label>
                            <input type="text" name="note" class="form-control" placeholder="যেমন: ছাত্রীর জরুরি প্রয়োজনে বা মেস ছাড়ার কারণে ফেরত" value="Meal Deposit Refund (ডিপোজিট ফেরত)">
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2 px-3">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">বাতিল করুন</button>
                        <button id="submitRefundBtn" class="btn btn-danger btn-sm fw-bold px-3 shadow-sm" type="submit">
                            <i class="ti ti-check me-1"></i> কনফার্ম ফেরত দিন
                        </button>
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
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize Select2 in Modal
            if ($('.select2-refund').length) {
                $('.select2-refund').select2({
                    dropdownParent: $('#addRefundModal'),
                    placeholder: '-- মেম্বার বেছে নিন --',
                    allowClear: true
                });
            }

            // Fetch Resident Balance on User Selection
            let currentMaxRefund = 0;
            $('#refund_user_id').on('change', function () {
                const userId = $(this).val();
                const balanceBox = document.getElementById('userBalanceBox');
                const depositTotalEl = document.getElementById('displayDepositTotal');
                const mealCostEl = document.getElementById('displayMealCost');
                const availableBalanceEl = document.getElementById('displayAvailableBalance');
                const amountInput = document.getElementById('refundAmountInput');
                const helperText = document.getElementById('amountHelperText');

                if (!userId) {
                    if (balanceBox) balanceBox.classList.add('d-none');
                    currentMaxRefund = 0;
                    return;
                }

                fetch(`{{ url('deposit-refunds/user-balance') }}/${userId}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.success) {
                            if (balanceBox) balanceBox.classList.remove('d-none');
                            if (depositTotalEl) depositTotalEl.innerText = `৳ ${data.formatted_deposit}`;
                            if (mealCostEl) mealCostEl.innerText = `৳ ${data.formatted_cost}`;
                            if (availableBalanceEl) availableBalanceEl.innerText = `৳ ${data.formatted_balance}`;
                            
                            currentMaxRefund = Math.max(0, data.balance);
                            if (amountInput) {
                                amountInput.max = currentMaxRefund;
                            }

                            if (data.balance <= 0) {
                                if (helperText) {
                                    helperText.innerHTML = `<span class="text-danger fw-bold"><i class="ti ti-alert-triangle me-1"></i> সতর্কবার্তা: এই মেম্বারের কোনো অতিরিক্ত ডিপোজিট ব্যালেন্স নেই (ব্যালেন্স ৳ ${data.formatted_balance})।</span>`;
                                }
                            } else {
                                if (helperText) {
                                    helperText.innerHTML = `<span class="text-success fw-bold"><i class="ti ti-check me-1"></i> সর্বোচ্চ ফেরতযোগ্য ব্যালেন্স: ৳ ${data.formatted_balance}</span>`;
                                }
                            }
                        }
                    })
                    .catch(() => {});
            });

            // Client-side Search and Pagination for Refunds Table
            let currentPage = 1;
            let currentPageSize = '25';
            let currentSearchTerm = '';

            const searchInput = document.getElementById('refundTableSearch');
            const clearSearchBtn = document.getElementById('clearRefundSearch');
            const pageSizeSelect = document.getElementById('tablePageSize');

            function filterAndPaginateRefundTable() {
                const allRows = Array.from(document.querySelectorAll('#refundListTable tbody tr.refund-data-row'));
                const noMatchRow = document.getElementById('noMatchingRefundRow');
                const defaultEmptyRow = document.getElementById('emptyRefundDefaultRow');
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

                const matchingRows = allRows.filter(row => {
                    const name = (row.getAttribute('data-name') || '').toLowerCase();
                    const phone = (row.getAttribute('data-phone') || '');
                    return !term || name.includes(term) || phone.includes(term);
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

                // Prev
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

                // Next
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
                        filterAndPaginateRefundTable();
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
                    filterAndPaginateRefundTable();
                });
            }

            // Clear search
            if (clearSearchBtn) {
                clearSearchBtn.addEventListener('click', function () {
                    if (searchInput) {
                        searchInput.value = '';
                        currentSearchTerm = '';
                    }
                    clearSearchBtn.classList.add('d-none');
                    currentPage = 1;
                    filterAndPaginateRefundTable();
                    if (searchInput) searchInput.focus();
                });
            }

            // Page size dropdown listener
            if (pageSizeSelect) {
                pageSizeSelect.addEventListener('change', function () {
                    currentPageSize = this.value;
                    currentPage = 1;
                    filterAndPaginateRefundTable();
                });
            }

            // Initialize table
            filterAndPaginateRefundTable();
        });
    </script>
@endpush
