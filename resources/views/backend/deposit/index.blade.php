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
                    <table class="table table-bordered table-striped align-middle mb-0" id="depositListTable">
                        <thead class="table-dark">
                        <tr>
                            <th style="width: 60px;" class="text-center">SL</th>
                            <th>Member Name (শিক্ষার্থীর নাম)</th>
                            <th>Phone (মোবাইল)</th>
                            <th class="text-center">Deposit Date (জমার তারিখ)</th>
                            <th class="text-center" style="width: 200px;">Total Deposit (মোট জমা)</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($deposits as $deposit)
                            @php
                                $uName = $deposit->user->name ?? 'N/A';
                                $uPhone = $deposit->user->phone ?? '-';
                                $historyJson = json_encode($deposit->history);
                            @endphp
                            <tr class="deposit-data-row"
                                data-name="{{ strtolower($uName) }}"
                                data-phone="{{ preg_replace('/[^0-9]/', '', $uPhone) }}">
                                <td class="text-center fw-bold"><span class="row-sl-number">{{ $loop->iteration }}</span></td>
                                <td>
                                    <a href="javascript:void(0)" class="fw-bold text-primary" onclick='openDepositModal("{{ addslashes($uName) }}", "{{ addslashes($uPhone) }}", {{ $deposit->total_deposit }}, {{ $historyJson }})'>
                                        {{ $uName }}
                                    </a>
                                </td>
                                <td>
                                    <span class="font-monospace fw-semibold">{{ $uPhone }}</span>
                                </td>
                                <td class="text-center">
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary fw-semibold px-2.5 py-1 rounded-pill shadow-none"
                                            onclick='openDepositModal("{{ addslashes($uName) }}", "{{ addslashes($uPhone) }}", {{ $deposit->total_deposit }}, {{ $historyJson }})'
                                            title="ক্লিক করে এই মাসের সমস্ত ডিপোজিটের তারিখ ও ইতিহাস দেখুন">
                                        <i class="ti ti-calendar me-1"></i>{{ $deposit->last_deposit_date }}
                                        @if($deposit->deposit_count > 1)
                                            <span class="badge bg-primary ms-1" style="font-size: 10px;">{{ $deposit->deposit_count }}টি জমা</span>
                                        @endif
                                    </button>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $deposit->total_deposit < 0 ? 'warning text-dark' : 'success' }} fs-6 px-2.5 py-1.5 font-monospace">
                                        ৳ {{ number_format($deposit->total_deposit, 2) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyDepositDefaultRow">
                                <td colspan="5" class="text-center py-4 text-muted">কোনো ডিপোজিট পাওয়া যায়নি (No deposit found)</td>
                            </tr>
                        @endforelse

                        <tr id="noMatchingDepositRow" class="d-none">
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="ti ti-search-off fs-1 text-secondary mb-2 d-block"></i>
                                <h6 class="fw-bold text-secondary mb-1">কোনো মেম্বার পাওয়া যায়নি</h6>
                                <p class="small text-muted mb-0">আপনার সার্চ করা নাম বা ফোন নম্বরের সাথে কোনো ডিপোজিট মেলেনি।</p>
                            </td>
                        </tr>
                        </tbody>
                        <tfoot>
                        <tr class="table-secondary fw-bold">
                            <td colspan="4" class="text-end fs-6">Grand Total (সর্বমোট জমা):</td>
                            <td class="text-center fs-6 text-{{ $summary['total_deposit'] < 0 ? 'warning' : 'success' }}">
                                ৳ {{ number_format($summary['total_deposit'], 2) }}
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Table Pagination & Record Count Footer -->
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-3 px-1 border-top mt-2" id="tablePaginationWrapper">
                    <div class="small text-muted fw-semibold" id="tableRecordCountInfo">
                        Showing <span id="pageShowingStart">1</span> to <span id="pageShowingEnd">{{ count($deposits) > 25 ? 25 : count($deposits) }}</span> of <span id="pageTotalRecords">{{ count($deposits) }}</span> members
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

    <!-- Deposit History Breakdown Modal -->
    <div class="modal fade" id="depositHistoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title text-white fw-bold mb-0">
                        <i class="ti ti-receipt me-2"></i><span id="modalUserName"></span> - ডিপোজিট হিস্ট্রি বিবরণী
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- User Summary Header -->
                    <div class="d-flex flex-wrap justify-content-between align-items-center bg-light p-3 rounded-3 mb-3 border">
                        <div>
                            <div class="fw-bold text-dark fs-5" id="modalUserTitle">-</div>
                            <div class="text-muted small"><i class="ti ti-phone me-1"></i><span id="modalUserPhone">-</span> | মাস: <strong>{{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}</strong></div>
                        </div>
                        <div class="text-end mt-2 mt-md-0">
                            <span class="text-muted small d-block">এই মাসের মোট জমা:</span>
                            <span class="badge bg-success fs-5 px-3 py-1.5 fw-bold font-monospace" id="modalUserTotal">৳ 0.00</span>
                        </div>
                    </div>

                    <!-- History Table -->
                    <h6 class="fw-bold text-dark mb-2"><i class="ti ti-list-check me-1 text-primary"></i> এই মাসের ডিপোজিটের তারিখ ও কিস্তিসমূহ:</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;" class="text-center">#</th>
                                    <th>ডিপোজিটের তারিখ (Date)</th>
                                    <th>এন্ট্রি করার সময় (Created Time)</th>
                                    <th class="text-end">টাকার পরিমাণ (Amount)</th>
                                    <th>নোট (Note)</th>
                                    <th>রিসিভকারী (Made By)</th>
                                </tr>
                            </thead>
                            <tbody id="modalHistoryBody">
                                <!-- Populated via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-3">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">বন্ধ করুন</button>
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
        function openDepositModal(userName, userPhone, totalDeposit, history) {
            document.getElementById('modalUserName').innerText = userName;
            document.getElementById('modalUserTitle').innerText = userName;
            document.getElementById('modalUserPhone').innerText = userPhone;
            document.getElementById('modalUserTotal').innerText = '৳ ' + Number(totalDeposit).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

            let tbody = document.getElementById('modalHistoryBody');
            tbody.innerHTML = '';

            if (!history || history.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">কোনো ডিপোজিটের রেকর্ড পাওয়া যায়নি।</td></tr>';
            } else {
                history.forEach((item, index) => {
                    let tr = document.createElement('tr');
                    const isRefund = Number(item.amount) < 0;
                    const amountFormatted = isRefund 
                        ? `- ৳ ${Number(Math.abs(item.amount)).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`
                        : `৳ ${Number(item.amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                    const amountClass = isRefund ? 'text-danger' : 'text-success';
                    const refundBadge = isRefund ? `<span class="badge bg-label-danger ms-1" style="font-size: 10px;"><i class="ti ti-cash-refund me-1"></i>ফেরত</span>` : '';

                    tr.innerHTML = `
                        <td class="text-center fw-bold text-muted">${index + 1}</td>
                        <td><strong class="text-primary"><i class="ti ti-calendar-event me-1"></i>${item.date}</strong></td>
                        <td><small class="text-muted"><i class="ti ti-clock me-1"></i>${item.created_at}</small></td>
                        <td class="text-end fw-bold ${amountClass} fs-6">${amountFormatted} ${refundBadge}</td>
                        <td>${item.note && item.note !== '-' ? item.note : '<span class="text-muted">-</span>'}</td>
                        <td><span class="badge bg-label-info">${item.made_by}</span></td>
                    `;
                    tbody.appendChild(tr);
                });
            }

            var myModal = new bootstrap.Modal(document.getElementById('depositHistoryModal'));
            myModal.show();
        }

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

            // Client-side Search and Pagination for Deposit List
            let currentPage = 1;
            let currentPageSize = '25';
            let currentSearchTerm = '';

            const searchInput = document.getElementById('memberTableSearch');
            const clearSearchBtn = document.getElementById('clearMemberSearch');
            const pageSizeSelect = document.getElementById('tablePageSize');

            function filterAndPaginateTable() {
                const allRows = Array.from(document.querySelectorAll('#depositListTable tbody tr.deposit-data-row'));
                const noMatchRow = document.getElementById('noMatchingDepositRow');
                const defaultEmptyRow = document.getElementById('emptyDepositDefaultRow');
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
