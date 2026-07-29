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
            <!-- Generate Bills Button -->
            <div>
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

          <!-- Filters Section -->
          <div class="card-body pt-4">
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
                    <td class="py-3 text-end fw-bold text-dark">৳ {{ Number(pay.amount).toFixed(2) }}</td>
                    <td class="py-3 text-end fw-bold text-success">৳ {{ Number(pay.paid_amount || 0).toFixed(2) }}</td>
                    <td class="py-3 text-end fw-bold">
                      <span v-if="getDueAmount(pay) > 0" class="text-danger">
                        ৳ {{ Number(getDueAmount(pay)).toFixed(2) }}
                      </span>
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
                          v-if="pay.status !== 'paid'"
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
                <!-- Carry Forward Due -->
                <div v-if="Number(activePayment.carried_forward_due) > 0" class="small text-warning fw-bold d-flex justify-content-between mt-1 border-top pt-2">
                  <span>⚠ Carry Forward Due:</span>
                  <span>+ ৳ {{ Number(activePayment.carried_forward_due).toFixed(2) }}</span>
                </div>
                <div class="small d-flex justify-content-between mt-1 border-top pt-2">
                  <span class="text-success fw-bold">Paid So Far:</span>
                  <span class="text-success fw-bold">৳ {{ Number(activePayment.paid_amount || 0).toFixed(2) }}</span>
                </div>
                <div v-if="remainingAfterCollection > 0" class="small text-danger fw-bold d-flex justify-content-between mt-1">
                  <span>🔴 Remaining Due after this payment:</span>
                  <span>৳ {{ remainingAfterCollection.toFixed(2) }}</span>
                </div>
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
                    :max="Math.max(1, Number(getDueAmount(activePayment)))"
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
      activePayment: null,
      form: {
        amount_to_collect: 0,
        payment_method: "Cash",
        trx_id: "",
        note: ""
      },

      pagination: {
        total: 0,
        current_page: 1,
        last_page: 1,
        per_page: 10
      }
    };
  },
  computed: {
    // Live preview of remaining balance after this collection
    remainingAfterCollection() {
      if (!this.activePayment) return 0;
      const due = this.getDueAmount(this.activePayment);
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

    openCollectModal(payment) {
      this.activePayment = payment;
      this.form = {
        amount_to_collect: this.getDueAmount(payment),
        payment_method: "Cash",
        trx_id: "",
        note: ""
      };
      this.showModal = true;
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
      const due = Number(pay.due_amount);
      const paid = Number(pay.paid_amount || 0);
      const total = Number(pay.amount || 0);
      if (pay.status !== 'paid' && due === 0 && paid === 0) {
        return total;
      }
      if (due > 0) return due;
      const remaining = total - paid;
      return remaining > 0 ? remaining : 0;
    },

    printReceipt(pay) {
      const formattedMonth = this.formatMonthYear(pay.payment_month);
      const logLines = pay.note ? pay.note.split("\n") : [];
      let logHtml = "";
      if (logLines.length > 0) {
        logHtml = `
          <h6 class="fw-bold mb-2 border-bottom pb-1 text-primary">Payment Transactions Log (আদায়ের বিবরণ)</h6>
          <div class="mb-4">
        `;
        logLines.forEach(line => {
          logHtml += `<div class="p-2 mb-1 rounded bg-light border small text-dark">${line}</div>`;
        });
        logHtml += `</div>`;
      }

      const carriedForward = Number(pay.carried_forward_due || 0);
      const rentAmount = Number(pay.amount || 0);
      const totalBill = rentAmount + carriedForward;
      const paidAmount = Number(pay.paid_amount || 0);
      const dueAmount = Number(pay.due_amount || 0);

      const htmlContent = `
        <!DOCTYPE html>
        <html>
          <head>
            <title>Money Receipt - টি এস এস ভিলা</title>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
            <style>
              body { font-family: 'Segoe UI', Arial, sans-serif; padding: 25px; background-color: #f9f9f9; color: #222; }
              .receipt-card { max-width: 680px; margin: auto; background: #fff; padding: 35px; border-radius: 12px; border: 2px solid #033364; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
              .receipt-header { border-bottom: 2px solid #033364; padding-bottom: 15px; margin-bottom: 20px; }
              .table-details th { font-weight: 600; color: #444; width: 45%; }
              .table-details td { font-weight: 700; color: #111; }
              .badge-paid { background-color: #198754; color: #fff; font-size: 14px; font-weight: 800; padding: 5px 15px; border-radius: 4px; text-transform: uppercase; }
              .badge-partial { background-color: #ffc107; color: #000; font-size: 14px; font-weight: 800; padding: 5px 15px; border-radius: 4px; text-transform: uppercase; }
              @media print {
                body { background-color: #fff; padding: 0; }
                .receipt-card { border: 2px solid #000 !important; box-shadow: none; max-width: 100%; padding: 25px; }
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
                <h3 class="fw-bold mb-1" style="color: #033364;">টি এস এস ভিলা</h3>
                <div class="fw-semibold text-dark mb-1">কলেজ রোড, নেসকো গেট সংলগ্ন, রংপুর</div>
                <div class="small text-muted mb-2">মোবাইল: +8801977270920</div>
                <div class="d-flex justify-content-center align-items-center gap-2 mt-2">
                  <span class="badge bg-primary px-3 py-2 fs-6">মাসিক ভাড়া আদায় রিসিট (Monthly Rent Receipt)</span>
                  ${pay.status === 'paid'
                    ? '<span class="badge-paid">Payment Fully Paid</span>'
                    : '<span class="badge-partial">Partially Paid</span>'}
                </div>
              </div>

              <!-- Invoice Metadata -->
              <div class="row mb-3 pb-2 border-bottom">
                <div class="col-6">
                  <small class="text-muted d-block">Invoice No / রিসিট নং:</small>
                  <strong class="text-dark">#RENT-INV-${pay.id}</strong>
                </div>
                <div class="col-6 text-end">
                  <small class="text-muted d-block">Date / তারিখ:</small>
                  <strong class="text-dark">${pay.created_at ? pay.created_at.substring(0, 10) : ''}</strong>
                </div>
              </div>

              <!-- Resident & Room Details -->
              <h6 class="fw-bold mb-2 border-bottom pb-1 text-primary">Resident Details (বোর্ডারের বিবরণ)</h6>
              <table class="table table-sm table-borderless table-details mb-3">
                <tr>
                  <th>Resident Name (বোর্ডারের নাম):</th>
                  <td>${pay.full_name}</td>
                </tr>
                <tr>
                  <th>Phone Number (মোবাইল নম্বর):</th>
                  <td>${pay.phone}</td>
                </tr>
                <tr>
                  <th>Room & Seat No (রুম ও সিট):</th>
                  <td>${pay.roomnumber} (${pay.floornumber})</td>
                </tr>
                <tr>
                  <th>Billing Month (ভাড়ার মাস):</th>
                  <td>${formattedMonth}</td>
                </tr>
              </table>

              <!-- Financial Summary -->
              <h6 class="fw-bold mb-2 border-bottom pb-1 text-primary">Financial Summary (হিসাবের বিবরণ)</h6>
              <table class="table table-sm table-bordered table-details mb-3">
                <tbody>
                  <tr>
                    <th>Monthly Rent (মাসিক ভাড়া):</th>
                    <td class="text-end">৳ ${rentAmount.toFixed(2)}</td>
                  </tr>
                  ${carriedForward > 0 ? `
                  <tr class="table-warning">
                    <th>Previous Carried Forward Due (আগের মাসের বকেয়া):</th>
                    <td class="text-end text-danger font-monospace">৳ ${carriedForward.toFixed(2)}</td>
                  </tr>
                  <tr class="table-light">
                    <th>Total Payable Bill (সর্বমোট দেয় বিল):</th>
                    <td class="text-end fw-bold">৳ ${totalBill.toFixed(2)}</td>
                  </tr>` : ''}
                  <tr class="table-success">
                    <th>Amount Paid So Far (সর্বমোট আদায়):</th>
                    <td class="text-end text-success font-monospace fw-bold">৳ ${(pay.status === 'paid' && carriedForward > 0 && paidAmount < totalBill) ? totalBill.toFixed(2) : paidAmount.toFixed(2)}</td>
                  </tr>
                  <tr class="${dueAmount > 0 ? 'table-danger' : 'table-light'}">
                    <th>Remaining Due Balance (অবশিষ্ট বকেয়া):</th>
                    <td class="text-end ${dueAmount > 0 ? 'text-danger' : 'text-success'} font-monospace fw-bold">
                      ৳ ${dueAmount.toFixed(2)}
                    </td>
                  </tr>
                </tbody>
              </table>


              <!-- Received By & Signatures -->
              <div class="mt-3 pt-2">
                <div class="small fw-semibold text-muted mb-4">
                  Received By / আদায়কারী: <strong class="text-dark">${this.cleanReceivedBy(pay.received_by)}</strong>
                </div>

                <div class="d-flex justify-content-end align-items-end mt-4 pt-2">
                  <div class="text-center" style="width: 220px;">
                    <div style="border-top: 1px solid #000; padding-top: 4px;" class="small fw-bold">
                      অফিস কর্তৃপক্ষের স্বাক্ষর
                    </div>
                  </div>
                </div>
              </div>

              <div class="text-center text-muted small mt-4 pt-3 border-top">
                টি এস এস ভিলা মেসে থাকার জন্য ধন্যবাদ।
              </div>
            </div>
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
    }
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
