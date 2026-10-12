<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
      <div class="col-12">
        <div class="card mb-4 shadow-sm border-0">
          <!-- Card Title & Overview -->
          <div class="card-header d-flex flex-wrap gap-3 justify-content-between align-items-center py-4 bg-light">
            <div>
              <h4 class="card-title mb-1 text-primary fw-bold">
                <i class="ti ti-report-money me-1"></i> Monthly Payments
              </h4>
              <p class="text-muted mb-0 small">Manage guest monthly rents, generate billing invoices, and keep track of pending, partial or overdue dues.</p>
            </div>
            <!-- Action Buttons -->
            <div class="d-flex gap-2">
              <button 
                class="btn btn-primary d-flex align-items-center gap-2 px-4 py-2 shadow-sm"
                @click="confirmGenerateBills"
                :disabled="generating">
                <span v-if="generating" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                <i v-else class="ti ti-refresh fs-5"></i>
                <span>Generate Bills</span>
              </button>
            </div>
          </div>

          <!-- Summary Metrics Cards -->
          <div class="card-body pb-0 pt-4">
            <div class="row g-3 mb-2">
              <!-- Card 1: Total Cash Collected In Hand -->
              <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-none h-100" style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); border-radius: 12px; border-left: 5px solid #2e7d32 !important;">
                  <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                      <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                        মোট আদায় (হাতে পাওয়া ক্যাশ)
                      </span>
                      <h4 class="mb-0 fw-bold text-success font-monospace">
                        ৳ {{ Number(stats.total_collected_in_hand || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                      </h4>
                      <small class="text-muted fw-semibold" style="font-size: 0.72rem;">
                        <i class="ti ti-check me-1 text-success"></i>বকেয়া ও চলতি মাস সহ সংগৃহীত
                      </small>
                    </div>
                    <div class="rounded-circle p-2 bg-white text-success shadow-sm d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                      <i class="ti ti-wallet fs-3"></i>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Card 2: Current Month Total Bill (Rent + Electricity) -->
              <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-none h-100" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border-radius: 12px; border-left: 5px solid #1976d2 !important;">
                  <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                      <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                        চলতি মাসের মোট বিল
                      </span>
                      <h4 class="mb-0 fw-bold text-primary font-monospace">
                        ৳ {{ Number(stats.total_current_bill || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                      </h4>
                      <small class="text-muted fw-semibold" style="font-size: 0.72rem;">
                        ভাড়া: ৳ {{ Number(stats.total_rent || 0).toFixed(0) }} + ⚡ ৳ {{ Number(stats.total_electricity || 0).toFixed(0) }}
                      </small>
                    </div>
                    <div class="rounded-circle p-2 bg-white text-primary shadow-sm d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                      <i class="ti ti-receipt-2 fs-3"></i>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Card 3: Total Outstanding Due -->
              <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-none h-100" style="background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%); border-radius: 12px; border-left: 5px solid #c62828 !important;">
                  <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                      <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                        সর্বমোট অবশিষ্ট বকেয়া
                      </span>
                      <h4 class="mb-0 fw-bold text-danger font-monospace">
                        ৳ {{ Number(stats.total_due || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                      </h4>
                      <small class="text-muted fw-semibold" style="font-size: 0.72rem;">
                        <i class="ti ti-alert-circle me-1 text-danger"></i>মোট অবশিষ্ট বকেয়া ব্যালেন্স
                      </small>
                    </div>
                    <div class="rounded-circle p-2 bg-white text-danger shadow-sm d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                      <i class="ti ti-clock-exclamation fs-3"></i>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Card 4: Residents Collection Status -->
              <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-none h-100" style="background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%); border-radius: 12px; border-left: 5px solid #7b1fa2 !important;">
                  <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                      <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                        পেমেন্ট স্ট্যাটাস সামারি
                      </span>
                      <div class="d-flex align-items-center gap-1 my-1">
                        <span class="badge bg-success py-1 px-2" style="font-size: 0.75rem;">{{ stats.paid_count || 0 }} Paid</span>
                        <span class="badge bg-warning text-dark py-1 px-2" style="font-size: 0.75rem;">{{ stats.partial_count || 0 }} Partial</span>
                        <span class="badge bg-secondary py-1 px-2" style="font-size: 0.75rem;">{{ stats.pending_count || 0 }} Pending</span>
                      </div>
                      <small class="text-muted fw-semibold" style="font-size: 0.72rem;">
                        মোট শিক্ষার্থী: {{ stats.total_residents || 0 }} জন
                      </small>
                    </div>
                    <div class="rounded-circle p-2 bg-white shadow-sm d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; color: #7b1fa2;">
                      <i class="ti ti-users fs-3"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Filters Section -->
          <div class="card-body pt-2">
            <div class="row g-3 align-items-end mb-4 bg-light p-3 rounded-3">
              <!-- Month Filter -->
              <div class="col-12 col-md-3">
                <label class="form-label fw-semibold text-dark">Select Billing Month</label>
                <input 
                  type="month" 
                  class="form-control form-control-md border-2" 
                  v-model="selectedMonth" 
                  @change="fetchPayments(1)"
                />
              </div>

              <!-- Search Input -->
              <div class="col-12 col-md-4">
                <label class="form-label fw-semibold text-dark">Search Resident (Name or Phone)</label>
                <div class="input-group">
                  <span class="input-group-text bg-white border-2 border-end-0">
                    <i class="ti ti-search text-muted"></i>
                  </span>
                  <input 
                    type="text" 
                    class="form-control form-control-md border-2 border-start-0" 
                    placeholder="Search by Name or Phone..." 
                    v-model="search"
                    @input="onSearchInput"
                  />
                </div>
              </div>

              <!-- Rows Selection -->
              <div class="col-12 col-md-2">
                <label class="form-label fw-semibold text-dark">Rows Per Page</label>
                <select 
                  class="form-select form-select-md border-2" 
                  v-model.number="perPage"
                  @change="fetchPayments(1)"
                >
                  <option :value="10">10 Rows</option>
                  <option :value="20">20 Rows</option>
                  <option :value="50">50 Rows</option>
                  <option :value="100">100 Rows</option>
                </select>
              </div>

              <!-- Reset Button -->
              <div class="col-12 col-md-3 text-md-end">
                <button class="btn btn-outline-secondary btn-md w-100" @click="resetFilters">
                  <i class="ti ti-clear-all me-1"></i> Reset Filters
                </button>
              </div>
            </div>

            <!-- Loader -->
            <div v-if="loading" class="text-center py-5">
              <div class="spinner-border text-primary fs-3" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p class="mt-2 text-muted fw-semibold">Fetching monthly rent list...</p>
            </div>

            <!-- Empty State -->
            <div v-else-if="payments.length === 0" class="text-center py-5 bg-white rounded-3 border-2 border-dashed">
              <div style="font-size: 55px; filter: grayscale(0.2);">💸</div>
              <h5 class="mt-3 fw-bold text-dark">No billing records found</h5>
              <p class="text-muted">No monthly rent invoices exist for "{{ formatMonthYear(selectedMonth) }}". Click "Generate Bills" to create them.</p>
            </div>

            <!-- Billing Grid Table -->
            <div v-else class="table-responsive border rounded-3 bg-white">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr class="text-uppercase text-dark small fw-bold">
                    <th class="ps-3 py-3" style="width: 60px">Sl</th>
                    <th class="py-3">Guest Information</th>
                    <th class="py-3">Room & Seat</th>
                    <th class="py-3 text-center">Month</th>
                    <th class="py-3 text-end">Rent</th>
                    <th class="py-3 text-end text-success">Paid</th>
                    <th class="py-3 text-end text-danger">Total Due</th>
                    <!-- <th class="py-3 text-center">Bill Date</th> -->
                    <th class="py-3 text-center">Status</th>
                    <th class="py-3">Payment Log</th>
                    <th class="py-3 text-center pe-3" style="width: 190px">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(pay, idx) in payments" :key="pay.id">
                    <td class="ps-3 py-3 fw-semibold text-muted">{{ (pagination.current_page - 1) * pagination.per_page + idx + 1 }}</td>
                    <td class="py-3">
                      <div class="d-flex flex-column">
                        <span class="fw-bold text-dark fs-6">{{ pay.full_name }}</span>
                        <span class="text-muted small"><i class="ti ti-phone me-1 text-primary"></i>{{ pay.phone }}</span>
                      </div>
                    </td>
                    <td class="py-3">
                      <div class="d-flex flex-column">
                        <span class="fw-semibold text-dark">{{ pay.roomnumber }}</span>
                        <span class="text-muted small"><i class="ti ti-layers-half me-1"></i>{{ pay.floornumber }}</span>
                      </div>
                    </td>
                    <td class="py-3 text-center">
                      <span class="badge bg-label-info fw-bold">{{ formatMonthYear(pay.payment_month) }}</span>
                    </td>
                    <td class="py-3 text-end fw-bold text-dark">
                      <div>৳ {{ Number(pay.amount).toFixed(2) }}</div>
                      <div v-if="Number(pay.electricity_bill) > 0" class="badge bg-label-warning text-dark font-monospace mt-1" style="font-size: 0.72rem;">
                        + ⚡ ৳ {{ Number(pay.electricity_bill).toFixed(2) }}
                      </div>
                    </td>
                    <td class="py-3 text-end fw-bold text-success">৳ {{ Number(pay.paid_amount || 0).toFixed(2) }}</td>
                    <td class="py-3 text-end fw-bold">
                      <div v-if="getDueAmount(pay) > 0" class="d-flex flex-column align-items-end">
                        <span class="text-danger fs-6">
                          ৳ {{ Number(getDueAmount(pay)).toFixed(2) }}
                        </span>
                        <div v-if="pay.due_breakdown && pay.due_breakdown.length > 0" class="mt-1">
                          <button
                            type="button"
                            class="btn btn-outline-danger btn-sm py-0 px-2 fw-semibold"
                            style="font-size: 0.73rem;"
                            @click="openDueBreakdownModal(pay)"
                            title="মাসিক বকেয়ার বিবরণ দেখুন"
                          >
                            <i class="ti ti-list-details me-1"></i>বকেয়া বিবরণ
                          </button>
                        </div>
                      </div>
                      <span v-else class="text-success">৳ 0.00</span>
                    </td>
                   <!--  <td class="py-3 text-center">
                      <span class="text-muted small">{{ formatDate(pay.due_date) }}</span>
                    </td> -->
                    <td class="py-3 text-center">
                      <!-- PAID -->
                      <span v-if="pay.status === 'paid'" class="badge bg-success px-3 py-2 rounded-2 fs-7 fw-bold">
                        <i class="ti ti-circle-check me-1 fs-6"></i> Paid
                      </span>
                      <!-- PARTIAL -->
                      <span v-else-if="pay.status === 'partial'" class="badge bg-warning text-dark px-3 py-2 rounded-2 fs-7 fw-bold">
                        <i class="ti ti-adjustments-horizontal me-1 fs-6"></i> Partial
                      </span>
                      <!-- OVERDUE -->
                      <span v-else-if="isOverdue(pay.due_date)" class="badge bg-danger px-3 py-2 rounded-2 fs-7 fw-bold animate-pulse">
                        <i class="ti ti-alert-triangle me-1 fs-6"></i> Overdue
                      </span>
                      <!-- PENDING -->
                      <span v-else class="badge bg-secondary px-3 py-2 rounded-2 fs-7 fw-bold text-white">
                        <i class="ti ti-clock me-1 fs-6"></i> Pending
                      </span>
                    </td>
                    <td class="py-3" style="max-width: 250px;">
                      <div v-if="pay.note" class="text-muted small text-truncate-custom" :title="pay.note">
                        {{ getLatestLog(pay.note) }}
                      </div>
                      <div v-if="pay.received_by" class="small mt-1" style="color: #6c757d;">
                        <i class="ti ti-user-check me-1 text-success"></i>{{ cleanReceivedBy(pay.received_by) }}
                      </div>
                      <span v-if="!pay.note && !pay.received_by" class="text-muted small">—</span>
                    </td>
                    <td class="py-3 text-center pe-3">
                      <div class="d-flex gap-2 justify-content-center">
                        <!-- Collect Payment Button -->
                        <button 
                          v-if="pay.status !== 'paid' && getDueAmount(pay) > 0.01"
                          class="btn btn-success btn-sm px-3 d-flex align-items-center gap-1 shadow-sm"
                          @click="openCollectModal(pay)"
                        >
                          <i class="ti ti-wallet fs-6"></i> Collect
                        </button>
                        <!-- Print Receipt Button -->
                        <button 
                          v-if="Number(pay.paid_amount || 0) > 0"
                          class="btn btn-outline-primary btn-sm px-3 d-flex align-items-center gap-1"
                          @click="printReceipt(pay)"
                        >
                          <i class="ti ti-printer fs-6"></i> Receipt
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination Controls -->
            <div v-if="payments.length > 0" class="d-flex flex-wrap justify-content-between align-items-center mt-4 bg-light p-3 rounded-3">
              <div class="small text-muted fw-semibold">
                Showing {{ payments.length }} of {{ pagination.total }} records | Page {{ pagination.current_page }} of {{ pagination.last_page }}
              </div>
              <nav aria-label="Page navigation">
                <ul class="pagination pagination-md mb-0">
                  <!-- Previous Button -->
                  <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                    <button class="page-link shadow-none" @click="fetchPayments(pagination.current_page - 1)">
                      <i class="ti ti-chevron-left"></i> Previous
                    </button>
                  </li>
                  <!-- Page numbers -->
                  <li 
                    v-for="page in pagination.last_page" 
                    :key="page" 
                    class="page-item" 
                    :class="{ active: pagination.current_page === page }"
                  >
                    <button class="page-link shadow-none" @click="fetchPayments(page)">{{ page }}</button>
                  </li>
                  <!-- Next Button -->
                  <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                    <button class="page-link shadow-none" @click="fetchPayments(pagination.current_page + 1)">
                      Next <i class="ti ti-chevron-right"></i>
                    </button>
                  </li>
                </ul>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- COLLECT PAYMENT MODAL -->` 
    <div 
      class="modal fade show" 
      id="collectModal" 
      tabindex="-1" 
      style="display: block; background: rgba(0, 0, 0, 0.5);" 
      v-if="showModal"
      role="dialog"
    >
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg rounded-4">
          <div class="modal-header bg-success text-white py-3 rounded-top-4">
            <h5 class="modal-title fw-bold text-white"><i class="ti ti-cash me-1"></i> Collect Monthly Rent</h5>
            <button type="button" class="btn-close btn-close-white" @click="closeCollectModal" aria-label="Close"></button>
          </div>
          <form @submit.prevent="submitCollectPayment">
            <div class="modal-body py-4">
              <!-- Guest Overview -->
              <div class="alert alert-success d-flex flex-column gap-1 mb-4 rounded-3 border-0">
                <div class="d-flex justify-content-between">
                  <span class="fw-bold text-dark">{{ activePayment.full_name }}</span>
                  <span class="fw-bold text-success">Monthly Rent: ৳ {{ Number(activePayment.amount).toFixed(2) }}</span>
                </div>
                <div class="small text-muted d-flex justify-content-between mt-1 border-top pt-2">
                  <span>Room: {{ activePayment.roomnumber }}</span>
                  <span>Month: {{ formatMonthYear(activePayment.payment_month) }}</span>
                </div>
                <!-- Electricity Bill in Overview if entered -->
                <div v-if="Number(form.electricity_bill) > 0" class="small text-primary fw-bold d-flex justify-content-between mt-1 border-top pt-2">
                  <span>⚡ Electricity Bill (বিদ্যুৎ বিল):</span>
                  <span>৳ {{ Number(form.electricity_bill).toFixed(2) }}</span>
                </div>
                <!-- Carry Forward Due -->
                <div v-if="Number(activePayment.carried_forward_due) > 0" class="small text-warning fw-bold d-flex justify-content-between mt-1 border-top pt-2">
                  <span>⚠ Carry Forward Due:</span>
                  <span>+ ৳ {{ Number(activePayment.carried_forward_due).toFixed(2) }}</span>
                </div>
                <!-- Total Payable Bill -->
                <div class="small fw-bold d-flex justify-content-between mt-1 border-top pt-2 text-dark">
                  <span>Total Payable Bill:</span>
                  <span>৳ {{ calculatedModalTotalBill.toFixed(2) }}</span>
                </div>
                <div class="small d-flex justify-content-between mt-1 border-top pt-2">
                  <span class="text-success fw-bold">Paid So Far:</span>
                  <span class="text-success fw-bold">৳ {{ Number(activePayment.paid_amount || 0).toFixed(2) }}</span>
                </div>
                <div v-if="remainingAfterCollection > 0" class="small text-danger fw-bold d-flex justify-content-between mt-1 border-top pt-2">
                  <span>🔴 Remaining Due after this payment:</span>
                  <span>৳ {{ remainingAfterCollection.toFixed(2) }}</span>
                </div>
              </div>

              <!-- Electricity Bill Input -->
              <div class="mb-3">
                <label class="form-label fw-semibold text-dark d-flex justify-content-between">
                  <span>Electricity Bill / বিদ্যুৎ বিল (৳)</span>
                  <span class="text-muted small">Optional</span>
                </label>
                <div class="input-group">
                  <span class="input-group-text fw-bold text-primary"><i class="ti ti-bolt me-1"></i> ৳</span>
                  <input 
                    type="number" 
                    class="form-control border-2" 
                    v-model.number="form.electricity_bill"
                    step="0.01"
                    min="0"
                    placeholder="0.00"
                  />
                </div>
                <div class="form-text text-muted small mt-1">বিদ্যুৎ বিলের পরিমাণ লিখুন (এটি রিসিট ও রেকর্ডে সংরক্ষিত থাকবে)।</div>
              </div>

              <!-- Amount to Collect -->
              <div class="mb-3">
                <label class="form-label fw-semibold text-dark">Amount to Collect <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text fw-bold">৳</span>
                  <input 
                    type="number" 
                    class="form-control border-2" 
                    v-model.number="form.amount_to_collect"
                    step="0.01"
                    :min="1"
                    :max="Math.max(1, calculatedModalDue)"
                    required
                  />
                </div>
                <div class="form-text text-muted small mt-1">Default is set to the full due amount. You can change it for partial payments.</div>
              </div>

              <!-- Payment Method Selector -->
              <div class="mb-3">
                <label class="form-label fw-semibold text-dark">Payment Method <span class="text-danger">*</span></label>
                <select class="form-select border-2" v-model="form.payment_method" required>
                  <option value="Cash">Cash</option>
                  <option value="Bkash">bKash</option>
                  <option value="Nagad">Nagad</option>
                  <option value="Rocket">Rocket</option>
                  <option value="Bank Transfer">Bank Transfer</option>
                </select>
              </div>

              <!-- Transaction ID -->
              <div class="mb-3" v-if="form.payment_method !== 'Cash'">
                <label class="form-label fw-semibold text-dark">Transaction ID (Optional)</label>
                <input 
                  type="text" 
                  class="form-control border-2" 
                  placeholder="Enter Mobile Banking TrxID..." 
                  v-model="form.trx_id"
                />
              </div>

              <!-- Short Note / Comments -->
              <!--
              <div class="mb-3">
                <label class="form-label fw-semibold text-dark">Short Note / Comments (Optional)</label>
                <textarea 
                  class="form-control border-2" 
                  rows="2" 
                  placeholder="Any payment references or comments..." 
                  v-model="form.note"
                ></textarea>
              </div>
              -->
              
            </div>
            <div class="modal-footer bg-light rounded-bottom-4 py-3">
              <button type="button" class="btn btn-outline-secondary px-4" @click="closeCollectModal">Cancel</button>
              <button type="submit" class="btn btn-success px-4" :disabled="submitting">
                <span v-if="submitting" class="spinner-border spinner-border-sm me-1" role="status"></span>
                Confirm Collect
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- GENERATE BILLS CONFIRMATION MODAL -->
    <div 
      class="modal fade show" 
      id="generateConfirmModal" 
      tabindex="-1" 
      style="display: block; background: rgba(0, 0, 0, 0.55); z-index: 1060;" 
      v-if="showGenerateModal"
      role="dialog"
    >
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
          <!-- Header -->
          <div class="modal-header bg-primary text-white py-3">
            <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
              <i class="ti ti-file-invoice fs-4"></i> Generate Monthly Bills
            </h5>
            <button type="button" class="btn-close btn-close-white" @click="closeGenerateModal" aria-label="Close"></button>
          </div>
          
          <!-- Body -->
          <div class="modal-body text-center py-4 px-4">
            <div class="mb-3">
              <div class="d-inline-flex align-items-center justify-content-center bg-light-primary text-primary rounded-circle p-3 shadow-sm" style="width: 75px; height: 75px; background: #e0f2fe;">
                <i class="ti ti-refresh text-primary fs-1"></i>
              </div>
            </div>
            
            <h5 class="fw-bold text-dark mb-2">Confirm Bill Generation</h5>
            <p class="text-muted mb-3 fs-6">
              You are about to generate monthly rent bills for all active residents for:
            </p>
            
            <div class="p-3 bg-light rounded-3 border mb-2">
              <div class="badge bg-primary fs-6 px-3 py-2">
                📅 {{ formatMonthYear(selectedMonth) }}
              </div>
              <p class="small text-muted mb-0 mt-2">
                This will calculate the monthly rent for each active resident and sync their due balances.
              </p>
            </div>
          </div>

          <!-- Footer -->
          <div class="modal-footer bg-light py-3 px-4 justify-content-center gap-2 border-0">
            <button type="button" class="btn btn-outline-secondary px-4 py-2 fw-semibold" @click="closeGenerateModal">
              Cancel
            </button>
            <button type="button" class="btn btn-primary px-4 py-2 fw-bold d-flex align-items-center gap-2 shadow-sm" @click="executeGenerateBills" :disabled="generating">
              <span v-if="generating" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
              <i v-else class="ti ti-check fs-5"></i>
              <span>Yes, Generate Bills</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- MONTHLY DUE BREAKDOWN MODAL -->
    <div 
      class="modal fade show" 
      id="dueBreakdownModal" 
      tabindex="-1" 
      style="display: block; background: rgba(0, 0, 0, 0.5);" 
      v-if="showBreakdownModal"
      role="dialog"
    >
      <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;" role="document">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
          <div class="modal-header bg-danger text-white py-3 px-4">
            <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2 mb-0" style="font-size: 1rem;">
              <i class="ti ti-file-invoice"></i>
              <span>মাসভিত্তিক বকেয়ার বিবরণ (Due Breakdown)</span>
            </h5>
            <button type="button" class="btn-close btn-close-white" @click="closeDueBreakdownModal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4" v-if="activeBreakdownPayment">
            <!-- Resident Info Banner -->
            <div class="bg-light p-3 rounded-3 mb-3 border">
              <div class="d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0">{{ activeBreakdownPayment.full_name }}</h6>
                <span class="badge bg-primary font-monospace">{{ activeBreakdownPayment.roomnumber }}</span>
              </div>
              <div class="small text-muted mt-1">
                <i class="ti ti-phone me-1 text-primary"></i>{{ activeBreakdownPayment.phone }}
              </div>
            </div>

            <!-- Due Breakdown List Table -->
            <div class="table-responsive border rounded-3 overflow-hidden mb-3">
              <table class="table table-sm table-striped align-middle mb-0" style="font-size: 0.85rem;">
                <thead class="table-dark">
                  <tr>
                    <th class="py-2 ps-3">মাস (Month)</th>
                    <th class="py-2 text-end">ভাড়া (Rent)</th>
                    <th class="py-2 text-end">জমা (Paid)</th>
                    <th class="py-2 text-end pe-3">বকেয়া (Due)</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in activeBreakdownPayment.due_breakdown" :key="item.id">
                    <td class="ps-3 py-2 fw-semibold text-dark">{{ formatMonthYear(item.payment_month) }}</td>
                    <td class="text-end py-2 text-dark">
                      <div>৳ {{ Number(item.amount).toFixed(2) }}</div>
                      <div v-if="Number(item.electricity_bill) > 0" class="text-muted small" style="font-size: 0.72rem;">
                        (বিদ্যুৎ বিল: ৳ {{ Number(item.electricity_bill).toFixed(0) }})
                      </div>
                    </td>
                    <td class="text-end py-2 text-success fw-bold">
                      ৳ {{ (Number(item.paid_amount || 0)).toFixed(2) }}
                    </td>
                    <td class="text-end py-2 pe-3 fw-bold text-danger">
                      ৳ {{ Math.max(0, (Number(item.amount) + Number(item.electricity_bill || 0)) - Number(item.paid_amount || 0)).toFixed(2) }}
                    </td>
                  </tr>
                </tbody>
                <tfoot class="table-light border-top">
                  <tr>
                    <th colspan="3" class="ps-3 py-2 fw-bold text-dark">সর্বমোট বকেয়া (Total Due):</th>
                    <th class="text-end py-2 pe-3 fw-extrabold text-danger fs-6">
                      ৳ {{ getDueAmount(activeBreakdownPayment).toFixed(2) }}
                    </th>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
          <div class="modal-footer bg-light py-2 px-3 justify-content-end">
            <button type="button" class="btn btn-secondary btn-sm fw-bold px-3" @click="closeDueBreakdownModal">
              ✕ বন্ধ করুন (Close)
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import axios from "axios";
import Toastify from "toastify-js";
import "toastify-js/src/toastify.css";

export default {
  name: "MonthlyPaymentList",
  data() {
    return {
      url: window.location.origin + "/backend/",
      payments: [],
      selectedMonth: new Date().toISOString().substring(0, 7),
      search: "",
      perPage: 10,
      loading: false,
      generating: false,
      submitting: false,
      searchTimeout: null,

      // Modal management
      showModal: false,
      showGenerateModal: false,
      showBreakdownModal: false,
      activePayment: null,
      activeBreakdownPayment: null,
      form: {
        amount_to_collect: 0,
        electricity_bill: 0,
        payment_method: "Cash",
        trx_id: "",
        note: ""
      },

      pagination: {
        total: 0,
        current_page: 1,
        last_page: 1,
        per_page: 10
      },

      stats: {
        total_collected_in_hand: 0,
        total_rent: 0,
        total_electricity: 0,
        total_current_bill: 0,
        total_due: 0,
        total_residents: 0,
        paid_count: 0,
        partial_count: 0,
        pending_count: 0
      }
    };
  },
  computed: {
    // Total payable calculation for the collect modal (Rent + Carry Forward Due)
    calculatedModalTotalBill() {
      if (!this.activePayment) return 0;
      const rent = Number(this.activePayment.amount || 0);
      const carry = Number(this.activePayment.carried_forward_due || 0);
      const elec = Number(this.form.electricity_bill || 0);
      return rent + carry + elec;
    },
    // Net due for the collect modal (Total - Paid So Far)
    calculatedModalDue() {
      if (!this.activePayment) return 0;
      const paid = Number(this.activePayment.paid_amount || 0);
      const due = this.calculatedModalTotalBill - paid;
      return due > 0 ? due : 0;
    },
    // Live preview of remaining balance after this collection
    remainingAfterCollection() {
      if (!this.activePayment) return 0;
      const due = this.calculatedModalDue;
      const collecting = Number(this.form.amount_to_collect) || 0;
      const remaining = due - collecting;
      return remaining > 0.009 ? remaining : 0;
    }
  },
  created() {
    this.fetchPayments(1);
  },
  methods: {
    toast(text, type = "success") {
      const bg = type === "success" ? "#0a8f4d" : "#dc3545";
      Toastify({
        text: text,
        duration: 3000,
        close: true,
        gravity: "top",
        position: "right",
        backgroundColor: bg,
        stopOnFocus: true
      }).showToast();
    },

    async fetchPayments(page = 1) {
      this.loading = true;
      try {
        const res = await axios.get(`${window.location.origin}/monthly-payments/get`, {
          params: {
            month: this.selectedMonth,
            search: this.search,
            per_page: this.perPage,
            page: page
          }
        });
        this.payments = res.data.data;
        this.pagination = {
          total: res.data.pagination?.total || res.data.total || 0,
          current_page: res.data.pagination?.current_page || res.data.current_page || 1,
          last_page: res.data.pagination?.last_page || res.data.last_page || 1,
          per_page: res.data.pagination?.per_page || res.data.per_page || 10
        };
        this.stats = res.data.stats || {
          total_collected_in_hand: 0,
          total_rent: 0,
          total_electricity: 0,
          total_current_bill: 0,
          total_due: 0,
          total_residents: 0,
          paid_count: 0,
          partial_count: 0,
          pending_count: 0
        };
      } catch (err) {
        console.error(err);
        this.toast("Failed to load monthly payment list", "error");
      } finally {
        this.loading = false;
      }
    },

    onSearchInput() {
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        this.fetchPayments(1);
      }, 500);
    },

    resetFilters() {
      this.selectedMonth = new Date().toISOString().substring(0, 7);
      this.search = "";
      this.perPage = 10;
      this.fetchPayments(1);
    },

    confirmGenerateBills() {
      this.showGenerateModal = true;
    },

    closeGenerateModal() {
      this.showGenerateModal = false;
    },

    async executeGenerateBills() {
      this.generating = true;
      try {
        const res = await axios.post(`${window.location.origin}/monthly-payments/generate`, {
          month: this.selectedMonth
        });
        if (res.data.status) {
          this.toast(res.data.message, "success");
          this.closeGenerateModal();
          this.fetchPayments(1);
        } else {
          this.toast(res.data.message || "Failed to generate bills", "error");
        }
      } catch (err) {
        console.error(err);
        this.toast(err.response?.data?.message || "Failed to generate billing records", "error");
      } finally {
        this.generating = false;
      }
    },

    openDueBreakdownModal(payment) {
      this.activeBreakdownPayment = payment;
      this.showBreakdownModal = true;
    },

    closeDueBreakdownModal() {
      this.showBreakdownModal = false;
      this.activeBreakdownPayment = null;
    },

    openCollectModal(payment) {
      this.activePayment = payment;
      const elec = Number(payment.electricity_bill || 0);
      
      this.form = {
        amount_to_collect: this.getDueAmount(payment),
        electricity_bill: elec,
        payment_method: "Cash",
        trx_id: "",
        note: ""
      };
      this.showModal = true;
    },

    onElectricityBillChange() {
      this.form.amount_to_collect = this.calculatedModalDue;
    },

    closeCollectModal() {
      this.showModal = false;
      this.activePayment = null;
    },

    async submitCollectPayment() {
      this.submitting = true;
      try {
        const res = await axios.post(`${window.location.origin}/monthly-payments/collect`, {
          id: this.activePayment.id,
          amount_to_collect: this.form.amount_to_collect,
          electricity_bill: this.form.electricity_bill,
          payment_method: this.form.payment_method,
          trx_id: this.form.trx_id,
          note: this.form.note
        });

        if (res.data.status) {
          this.toast(res.data.message || "Payment collected successfully!", "success");
          this.closeCollectModal();
          this.fetchPayments(this.pagination.current_page);
        } else {
          this.toast(res.data.message || "Failed to collect payment", "error");
        }
      } catch (err) {
        console.error(err);
        this.toast(err.response?.data?.message || "Collection submission failed", "error");
      } finally {
        this.submitting = false;
      }
    },

    isOverdue(dueDateStr) {
      if (!dueDateStr) return false;
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      const dueDate = new Date(dueDateStr);
      dueDate.setHours(0, 0, 0, 0);
      return today > dueDate;
    },

    formatDate(dateStr) {
      if (!dateStr) return "-";
      const parts = dateStr.split("-");
      if (parts.length === 3) {
        return `${parts[2]}/${parts[1]}/${parts[0]}`;
      }
      return dateStr;
    },

    formatMonthYear(monthStr) {
      if (!monthStr) return "-";
      const parts = monthStr.split("-");
      if (parts.length !== 2) return monthStr;
      const year = parts[0];
      const monthNames = [
        "January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
      ];
      const mIdx = parseInt(parts[1], 10) - 1;
      return `${monthNames[mIdx]} ${year}`;
    },

    getLatestLog(noteStr) {
      if (!noteStr) return "";
      const lines = noteStr.split("\n");
      return lines[lines.length - 1] || "";
    },

    cleanReceivedBy(str) {
      if (!str) return "Admin";
      return str.replace(/\s*\([^)]*\)/g, "").trim();
    },

    getDueAmount(pay) {
      if (!pay) return 0;
      if (pay.status === 'paid') return 0;
      const due = Number(pay.due_amount);
      const paid = Number(pay.paid_amount || 0);
      const rent = Number(pay.amount || 0);
      const elec = Number(pay.electricity_bill || 0);
      const carry = Number(pay.carried_forward_due || 0);
      const total = rent + elec + carry;
      
      if (pay.status !== 'paid' && due === 0 && paid === 0) {
        return total;
      }
      if (due > 0) return due;
      const remaining = total - paid;
      return remaining > 0.01 ? remaining : 0;
    },

    printReceipt(pay) {
      if (!pay) return;
      const formattedMonth = this.formatMonthYear(pay.payment_month);
      
      let transactions = (pay.parsed_transactions && pay.parsed_transactions.length > 0)
        ? pay.parsed_transactions
        : [];

      if (transactions.length === 0 && pay.note) {
        const lines = typeof pay.note === 'string' ? pay.note.split('\n') : (Array.isArray(pay.note) ? pay.note : []);
        lines.forEach(line => {
          line = line.trim();
          if (!line) return;
          const matchFull = line.match(/^\[([^\]]+)\]\s*\[([^\]]+)\]\s*Collected\s*৳\s*([0-9.]+)\s*via\s*([^(]+)(?:\(([^)]+)\))?/i);
          const matchSimple = line.match(/^\[([^\]]+)\]\s*Collected\s*৳\s*([0-9.]+)\s*via\s*([^(]+)(?:\(([^)]+)\))?/i);
          if (matchFull) {
            transactions.push({
              month: matchFull[1].trim(),
              datetime: matchFull[2].trim(),
              amount: parseFloat(matchFull[3]) || 0,
              method: matchFull[4].trim(),
              trx: matchFull[5] ? matchFull[5].replace(/TRX:\s*/i, '').trim() : ''
            });
          } else if (matchSimple) {
            transactions.push({
              month: formattedMonth,
              datetime: matchSimple[1].trim(),
              amount: parseFloat(matchSimple[2]) || 0,
              method: matchSimple[3].trim(),
              trx: matchSimple[4] ? matchSimple[4].replace(/TRX:\s*/i, '').trim() : ''
            });
          }
        });
      }

      let latestDatetime = "";
      if (transactions.length > 0) {
        const latestTx = transactions[transactions.length - 1];
        latestDatetime = latestTx.datetime;
      }

      const rentAmount = Number(pay.amount || 0);
      const electricityAmount = Number(pay.electricity_bill || 0);
      
      let previousMonthsBreakdown = [];
      let calculatedCarriedForward = 0;

      if (pay.all_history && pay.all_history.length > 0) {
        const prevItems = pay.all_history.filter(item => item.payment_month < pay.payment_month);
        
        prevItems.forEach(item => {
          let monthName = this.formatMonthYear(item.payment_month);
          let monthDue = Number(item.due_amount || 0);
          let elecBill = Number(item.electricity_bill || 0);
          
          let paidInLatest = 0;
          if (latestDatetime) {
            const txs = transactions.filter(t => t.month === monthName && t.datetime === latestDatetime);
            paidInLatest = txs.reduce((sum, t) => sum + Number(t.amount), 0);
          }
          
          let originalDue = monthDue + paidInLatest;
          
          if (originalDue > 0) {
            let elecText = elecBill > 0 ? ` (বিদ্যুৎ: ৳ ${elecBill.toFixed(0)})` : '';
            previousMonthsBreakdown.push({
              month: monthName + elecText,
              amount: originalDue
            });
            calculatedCarriedForward += originalDue;
          }
        });
      }

      const totalBill = rentAmount + calculatedCarriedForward + electricityAmount;
      const dueAmount = Number(pay.due_amount || 0);
      
      let totalPaidSoFar = (dueAmount <= 0) ? totalBill : Math.max(0, totalBill - dueAmount);
      let remainingDue = Math.max(0, totalBill - totalPaidSoFar);

      const rawDate = (pay.updated_at || pay.created_at || '').substring(0, 10);
      const printDate = rawDate || new Date().toISOString().substring(0, 10);

      const htmlContent = `
        <!DOCTYPE html>
        <html>
          <head>
            <meta charset="utf-8">
            <title>Money Receipt - #${pay.id} - ${pay.full_name || 'Resident'}</title>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
            <style>
              @page { size: A4 portrait; margin: 12mm 15mm; }
              * { box-sizing: border-box; }
              body { font-family: 'Segoe UI', Arial, sans-serif; padding: 20px; background-color: #f8fafc; color: #222; }
              .receipt-card { max-width: 720px; margin: auto; background: #fff; padding: 25px 30px; border-radius: 8px; border: 1.5px solid #94a3b8; box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
              .receipt-header { border-bottom: 2px solid #033364; padding-bottom: 12px; margin-bottom: 15px; }
              .table-details { font-size: 0.86rem; margin-bottom: 10px !important; }
              .table-details th, .table-details td { padding: 5px 10px !important; }
              .table-details th { font-weight: 600; color: #334155; width: 45%; }
              .table-details td { font-weight: 700; color: #0f172a; }
              .badge-paid { color: #16a34a; font-size: 0.82rem; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase; }
              .badge-partial { color: #d97706; font-size: 0.82rem; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase; }
              @media print {
                body { background-color: #fff; padding: 0; }
                .receipt-card { border: 1.5px solid #000 !important; box-shadow: none; max-width: 100%; padding: 20px; page-break-inside: avoid; }
                .no-print-btn { display: none !important; }
              }
            </style>
          </head>
          <body>
            <div class="receipt-card">
              <div class="d-flex justify-content-between align-items-center mb-3 no-print-btn">
                <button class="btn btn-secondary btn-sm" onclick="window.close()">Close Window</button>
                <button class="btn btn-primary btn-sm px-4 fw-bold" onclick="window.print()">Click to Print Receipt</button>
              </div>

              <!-- Header Info -->
              <div class="receipt-header text-center">
                <h3 class="fw-bold mb-1" style="color: #033364;">টি এস এস ভিলা (TSS Villa)</h3>
                <div class="fw-semibold text-dark mb-1" style="font-size: 0.88rem;">কলেজ রোড, নেসকো গেট সংলগ্ন, রংপুর | মোবাইল: +8801977270920</div>
                <div class="d-flex justify-content-center align-items-center gap-3 mt-2">
                  <span class="text-muted fw-semibold" style="font-size: 0.85rem;">মাসিক ভাড়া আদায় রিসিট (Monthly Rent Receipt)</span>
                  ${(dueAmount <= 0)
                    ? '<span class="badge-paid">PAYMENT FULLY PAID</span>'
                    : '<span class="badge-partial">PARTIALLY PAID</span>'}
                </div>
              </div>

              <!-- Invoice Metadata -->
              <div class="row mb-3 pb-2 border-bottom">
                <div class="col-6">
                  <small class="text-muted d-block">Invoice No / রিসিট নং: <strong class="text-dark">#RENT-INV-${pay.id}</strong></small>
                </div>
                <div class="col-6 text-end">
                  <small class="text-muted d-block">Date / তারিখ: <strong class="text-dark">${printDate}</strong></small>
                </div>
              </div>

              <!-- Resident & Room Details -->
              <h6 class="fw-bold mb-2 border-bottom pb-1 text-primary" style="font-size: 0.90rem;">Resident Details (শিক্ষার্থীর বিবরণ)</h6>
              <table class="table table-sm table-borderless table-details mb-3">
                <tr>
                  <th>Resident Name (শিক্ষার্থীর নাম):</th>
                  <td>${pay.full_name || '-'}</td>
                </tr>
                <tr>
                  <th>Phone Number (মোবাইল নম্বর):</th>
                  <td>${pay.phone || '-'}</td>
                </tr>
                <tr>
                  <th>Room & Seat No (রুম ও সিট):</th>
                  <td>${pay.roomnumber || '-'} (${pay.floornumber || '-'})</td>
                </tr>
                <tr>
                  <th>Billing Month (ভাড়ার মাস):</th>
                  <td>${formattedMonth}</td>
                </tr>
              </table>

              <!-- Financial Summary -->
              <h6 class="fw-bold mb-2 border-bottom pb-1 text-primary" style="font-size: 0.90rem;">Financial Summary (হিসাবের বিবরণ)</h6>
              <table class="table table-sm table-bordered table-details mb-3">
                <tbody>
                  <tr>
                    <th>Monthly Rent (মাসিক ভাড়া):</th>
                    <td class="text-end font-monospace">৳ ${rentAmount.toFixed(2)}</td>
                  </tr>
                  ${electricityAmount > 0 ? `
                  <tr class="table-info">
                    <th>Electricity Bill (বিদ্যুৎ বিল):</th>
                    <td class="text-end font-monospace fw-bold">৳ ${electricityAmount.toFixed(2)}</td>
                  </tr>` : ''}
                  ${previousMonthsBreakdown.map(b => `
                  <tr class="table-warning">
                    <th>${b.month} (বকেয়া):</th>
                    <td class="text-end text-danger font-monospace">৳ ${b.amount.toFixed(2)}</td>
                  </tr>`).join('')}
                  ${(calculatedCarriedForward > 0 || electricityAmount > 0) ? `
                  <tr class="table-light">
                    <th>Total Payable Bill (সর্বমোট দেয় বিল):</th>
                    <td class="text-end fw-bold font-monospace">৳ ${totalBill.toFixed(2)}</td>
                  </tr>` : ''}
                  <tr class="table-success">
                    <th>Amount Paid So Far (সর্বমোট আদায়):</th>
                    <td class="text-end text-success font-monospace fw-bold">৳ ${totalPaidSoFar.toFixed(2)}</td>
                  </tr>
                  ${remainingDue > 0.009 ? `
                  <tr class="table-danger">
                    <th>Remaining Due Balance (অবশিষ্ট বকেয়া):</th>
                    <td class="text-end text-danger font-monospace fw-bold">৳ ${remainingDue.toFixed(2)}</td>
                  </tr>` : ''}
                </tbody>
              </table>

              <!-- Received By & Signatures -->
              <div class="mt-4 pt-2">
                <div class="d-flex justify-content-between align-items-end">
                  <div class="small fw-semibold text-muted">
                    Received By / আদায়কারী: <strong class="text-dark">${this.cleanReceivedBy(pay.received_by)}</strong>
                  </div>

                  <div class="text-center" style="width: 220px;">
                    <div style="border-top: 1.5px solid #000; padding-top: 4px;" class="small fw-bold">
                      অফিস কর্তৃপক্ষের স্বাক্ষর
                    </div>
                  </div>
                </div>
              </div>

              <div class="text-center text-muted small mt-4 pt-3 border-top">
                টি এস এস ভিলা মেসে থাকার জন্য ধন্যবাদ।
              </div>
            </div>

            <script>
              window.onload = function() {
                window.print();
              };
            <\/script>
          </body>
        </html>
      `;
      let iframe = document.getElementById("monthlyReceiptPrintIframe");
      if (!iframe) {
        iframe = document.createElement("iframe");
        iframe.id = "monthlyReceiptPrintIframe";
        iframe.style.position = "fixed";
        iframe.style.right = "0";
        iframe.style.bottom = "0";
        iframe.style.width = "0px";
        iframe.style.height = "0px";
        iframe.style.border = "0";
        document.body.appendChild(iframe);
      }
      const doc = iframe.contentWindow.document;
      doc.open();
      doc.write(htmlContent);
      doc.close();

      setTimeout(() => {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
      }, 300);
    },

    exportPaymentsCSV() {
      if (!this.payments || this.payments.length === 0) {
        this.toast("এক্সপোর্ট করার মতো কোনো ডাটা পাওয়া যায়নি", "warning");
        return;
      }
      const headers = ["SL", "Resident Name", "Phone", "Month", "Total Amount", "Paid Amount", "Due Amount", "Status", "Payment Method", "TRX ID", "Received By"];
      const rows = this.payments.map((p, index) => [
        index + 1,
        p.user_name || (p.booking ? p.booking.full_name : ''),
        p.user_phone || (p.booking ? p.booking.phone : ''),
        p.month_name || p.month_year || this.selectedMonth,
        p.total_amount || 0,
        p.paid_amount || 0,
        p.due_amount || 0,
        p.status_text || (p.due_amount <= 0 ? 'PAID' : (p.paid_amount > 0 ? 'PARTIAL' : 'UNPAID')),
        p.payment_method || '-',
        p.transaction_id || '-',
        this.cleanReceivedBy(p.received_by)
      ]);
      const filename = `Monthly_Payments_${this.selectedMonth || 'All'}.csv`;
      this.downloadCSV(filename, headers, rows);
    },

    downloadCSV(filename, headers, rows) {
      let csvContent = "\uFEFF";
      csvContent += headers.map(h => `"${String(h).replace(/"/g, '""')}"`).join(",") + "\n";
      rows.forEach(row => {
        csvContent += row.map(cell => `"${String(cell ?? '').replace(/"/g, '""')}"`).join(",") + "\n";
      });
      const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
      const link = document.createElement("a");
      link.href = URL.createObjectURL(blob);
      link.setAttribute("download", filename);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    },
  }
};
</script>

<style scoped>
.bg-label-info {
  background-color: #e0f2fe !important;
  color: #0369a1 !important;
}
.fs-7 {
  font-size: 0.825rem;
}
.animate-pulse {
  animation: pulse 1.8s infinite;
}
.text-truncate-custom {
  max-width: 100%;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  display: block;
}
@keyframes pulse {
  0% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.8;
    transform: scale(0.97);
  }
  100% {
    opacity: 1;
    transform: scale(1);
  }
}
</style>
