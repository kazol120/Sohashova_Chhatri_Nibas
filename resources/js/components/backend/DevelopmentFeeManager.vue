<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-12">
        <div class="card mb-4 shadow-sm border-0">
          <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3 bg-light border-bottom">
            <h5 class="card-title mb-0 text-primary fw-bold">
              <i class="ti ti-cash me-2"></i>Development Fee Manager (উন্নয়ন ফি ব্যবস্থাপনা)
            </h5>

            <div class="d-flex align-items-center gap-2">
              <button
                class="btn btn-sm"
                :class="activeFilter === 'all' ? 'btn-primary' : 'btn-outline-primary'"
                @click="setFilter('all')"
              >
                All Residents
              </button>

              <button
                class="btn btn-sm"
                :class="activeFilter === 'unpaid' ? 'btn-warning' : 'btn-outline-warning'"
                @click="setFilter('unpaid')"
              >
                <i class="ti ti-alert-circle me-1"></i>Unpaid (বাকি)
              </button>

              <button
                class="btn btn-sm"
                :class="activeFilter === 'paid' ? 'btn-success' : 'btn-outline-success'"
                @click="setFilter('paid')"
              >
                <i class="ti ti-check me-1"></i>Paid (পরিশোধিত)
              </button>
            </div>
          </div>

          <div class="card-body mt-3">
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
              <div class="d-flex align-items-center gap-2">
                <label class="small text-muted mb-0">Rows:</label>
                <select class="form-select form-select-sm" style="width: 90px" v-model.number="perPage">
                  <option :value="5">5</option>
                  <option :value="10">10</option>
                  <option :value="20">20</option>
                  <option :value="50">50</option>
                </select>
              </div>

              <div class="d-flex gap-2 align-items-center">
                <input
                  type="text"
                  class="form-control form-control-sm"
                  style="width: 280px"
                  placeholder="Search resident / phone / room..."
                  v-model="search"
                />
              </div>
            </div>

            <div class="table-responsive">
              <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th style="width: 50px">Sl</th>
                    <th style="width: 200px">Resident Name</th>
                    <th style="width: 140px">Phone</th>
                    <th style="width: 140px">Floor</th>
                    <th style="width: 140px">Room - Seat</th>
                    <th style="width: 160px">Development Fee Status</th>
                    <th style="width: 180px" class="text-center">Action</th>
                  </tr>
                </thead>

                <tbody v-if="residents.length">
                  <tr v-for="(r, idx) in residents" :key="r.id">
                    <td>{{ from + idx + 1 }}</td>
                    <td>
                      <div class="fw-bold text-dark">{{ r.full_name || "-" }}</div>
                      <small class="text-muted">{{ r.user_type || 'Student' }}</small>
                    </td>
                    <td>
                      <span class="fw-semibold text-secondary">{{ r.phone || "-" }}</span>
                    </td>
                    <td>
                      <span class="fw-semibold text-dark">{{ r.floornumber || "-" }}</span>
                    </td>
                    <td>
                      <span class="badge bg-primary px-2 py-1 font-monospace fs-6">{{ r.roomnumber || "-" }}</span>
                    </td>
                    <td>
                      <span v-if="r.is_paid" class="badge bg-success px-2 py-1 fs-6">
                        <i class="ti ti-check me-1"></i> Paid (৳ {{ formatCurrency(r.development_fee) }})
                      </span>
                      <span v-else class="badge bg-warning text-dark px-2 py-1 fs-6">
                        <i class="ti ti-clock me-1"></i> Unpaid (৳ 0)
                      </span>
                    </td>
                    <td class="text-center">
                      <div class="d-flex justify-content-center gap-2">
                        <button
                          v-if="!r.is_paid"
                          class="btn btn-sm btn-success fw-bold"
                          @click="openPayModal(r)"
                        >
                          <i class="ti ti-cash me-1"></i> Collect Fee
                        </button>

                        <button
                          v-if="r.is_paid"
                          class="btn btn-sm btn-outline-primary fw-bold"
                          @click="printReceiptModal(r)"
                        >
                          <i class="ti ti-printer me-1"></i> Print Receipt
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>

                <tbody v-else>
                  <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                      <span v-if="loading">
                        <i class="fa fa-spinner fa-spin me-2"></i> Loading data...
                      </span>
                      <span v-else>No residents found for this status</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
              <div class="small text-muted">
                Total: {{ total }} | Page: {{ currentPage }} / {{ totalPages }}
              </div>

              <div class="d-flex align-items-center gap-2">
                <button
                  class="btn btn-sm btn-secondary"
                  :disabled="currentPage <= 1 || loading"
                  @click="fetchResidents(currentPage - 1)"
                >
                  Previous
                </button>

                <button
                  class="btn btn-sm btn-secondary"
                  :disabled="currentPage >= totalPages || loading"
                  @click="fetchResidents(currentPage + 1)"
                >
                  Next
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Collect Fee Modal -->
    <div
      class="modal fade"
      id="payDevFeeModal"
      tabindex="-1"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
          <div class="modal-header bg-success text-white py-3">
            <h5 class="modal-title text-white fw-bold">
              <i class="ti ti-cash me-2"></i>Collect Development Fee (উন্নয়ন ফি সংগ্রহ)
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body p-4" v-if="selectedResident">
            <div class="alert alert-light border border-success d-flex align-items-center gap-3 mb-3">
              <div>
                <h6 class="mb-1 fw-bold text-dark">{{ selectedResident.full_name }}</h6>
                <div class="small text-muted">
                  Phone: <strong>{{ selectedResident.phone }}</strong> | Room: <strong>{{ selectedResident.roomnumber }}</strong>
                </div>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold">Development Fee Amount (উন্নয়ন ফি টাকা) <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text fw-bold">৳</span>
                <input
                  type="number"
                  class="form-control form-control-lg fw-bold text-success"
                  v-model.number="payAmount"
                  placeholder="3000"
                />
              </div>
            </div>
          </div>

          <div class="modal-footer bg-light py-3">
            <button type="button" class="btn btn-secondary fw-semibold" data-bs-dismiss="modal">
              Cancel
            </button>
            <button
              type="button"
              class="btn btn-success fw-bold px-4"
              :disabled="!payAmount || submitting"
              @click="submitPayFee"
            >
              <span v-if="submitting"><i class="fa fa-spinner fa-spin me-1"></i> Saving...</span>
              <span v-else><i class="ti ti-check me-1"></i> Confirm & Collect</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Printable Receipt Modal -->
    <div
      class="modal fade"
      id="receiptModal"
      tabindex="-1"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0">
          <div class="modal-header bg-dark text-white py-2 px-3">
            <h6 class="modal-title text-white mb-0 fw-bold">
              <i class="ti ti-printer me-2"></i>Development Fee Receipt (উন্নয়ন ফি মানি রিসিট)
            </h6>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body p-4" id="printableReceiptArea" v-if="receiptResident">
            <!-- Receipt Template Box -->
            <div class="receipt-box p-4 border border-2 border-dark rounded-3 bg-white" style="font-family: 'Segoe UI', Arial, sans-serif;">
              <div class="text-center border-bottom pb-3 mb-3">
                <h3 class="fw-bold mb-1" style="color: #033364;">সোহাসভা ছাত্রী নিবাস</h3>
                <div class="small text-muted">জাহাজ কোম্পানী মোড়, রংপুর | ফোন: 01891151713</div>
                <div class="badge bg-dark fs-6 px-3 py-1 mt-2 text-uppercase">DEVELOPMENT FEE MONEY RECEIPT</div>
              </div>

              <div class="d-flex justify-content-between align-items-center mb-3 small text-muted">
                <div>Receipt No: <strong>#DEV-{{ receiptResident.id }}</strong></div>
                <div>Date: <strong>{{ formatDate(receiptResident.created_at) }}</strong></div>
              </div>

              <table class="table table-sm table-bordered align-middle mb-4">
                <tbody>
                  <tr>
                    <th style="width: 30%" class="bg-light">Resident Name:</th>
                    <td class="fw-bold">{{ receiptResident.full_name }}</td>
                  </tr>
                  <tr>
                    <th class="bg-light">Phone Number:</th>
                    <td>{{ receiptResident.phone }}</td>
                  </tr>
                  <tr>
                    <th class="bg-light">Father / Guardian Phone:</th>
                    <td>{{ receiptResident.father_phone || receiptResident.mother_phone || '-' }}</td>
                  </tr>
                  <tr>
                    <th class="bg-light">Floor & Room-Seat:</th>
                    <td>
                      <strong>{{ receiptResident.floornumber || 'Floor' }}</strong> | Room-Seat: <strong>{{ receiptResident.roomnumber }}</strong>
                    </td>
                  </tr>
                  <tr>
                    <th class="bg-light">Payment Purpose:</th>
                    <td class="fw-bold text-primary">Development Fee (উন্নয়ন ফি - এককালীন)</td>
                  </tr>
                  <tr>
                    <th class="bg-light">Amount Paid:</th>
                    <td class="fw-bold fs-5 text-success">৳ {{ formatCurrency(receiptResident.development_fee) }}</td>
                  </tr>
                  <tr>
                    <th class="bg-light">Payment Status:</th>
                    <td>
                      <span class="badge bg-success px-3 py-1 fs-6">PAID / পরিশোধিত</span>
                    </td>
                  </tr>
                </tbody>
              </table>

              <div class="d-flex justify-content-between align-items-end mt-5 pt-4">
                <div class="text-center" style="width: 200px;">
                  <div style="border-top: 1px solid #000; padding-top: 4px;" class="small fw-bold">
                    Resident Signature
                  </div>
                </div>

                <div class="text-center" style="width: 200px;">
                  <div style="border-top: 1px solid #000; padding-top: 4px;" class="small fw-bold">
                    Authorized Signature
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-footer bg-light py-2">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary fw-bold" @click="printReceiptNow">
              <i class="ti ti-printer me-1"></i> Print Receipt Now
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
  name: "DevelopmentFeeManager",

  data() {
    return {
      residents: [],
      loading: false,
      search: "",
      activeFilter: "all", // all, unpaid, paid
      perPage: 10,
      currentPage: 1,
      totalPages: 1,
      total: 0,
      from: 0,
      _t: null,

      selectedResident: null,
      payAmount: 3000,
      submitting: false,
      payModalInstance: null,

      receiptResident: null,
      receiptModalInstance: null,
    };
  },

  computed: {
    url() {
      return this.$store.state.url;
    },
  },

  mounted() {
    this.fetchResidents(1);
  },

  watch: {
    search() {
      clearTimeout(this._t);
      this._t = setTimeout(() => this.fetchResidents(1), 300);
    },
    perPage() {
      this.fetchResidents(1);
    },
  },

  beforeUnmount() {
    clearTimeout(this._t);
  },

  methods: {
    formatCurrency(val) {
      if (val === null || val === undefined || isNaN(val)) return "0";
      return Number(val).toLocaleString("en-US");
    },

    formatDate(d) {
      if (!d) return new Date().toLocaleDateString("en-GB");
      const date = new Date(d);
      return date.toLocaleDateString("en-GB");
    },

    toast(text, type = "success") {
      const bg =
        type === "success"
          ? "linear-gradient(to right, #00b09b, #96c93d)"
          : type === "warning"
          ? "linear-gradient(to right, #f59e0b, #fbbf24)"
          : "linear-gradient(to right, #ff5f6d, #ffc371)";

      Toastify({
        text: text || "Done",
        duration: 2500,
        close: true,
        gravity: "top",
        position: "right",
        backgroundColor: bg,
      }).showToast();
    },

    endpoint(path) {
      const base = this.url.endsWith("/") ? this.url.slice(0, -1) : this.url;
      const cleanPath = path.startsWith("/") ? path : `/${path}`;
      return `${base}${cleanPath}`;
    },

    setFilter(filterType) {
      this.activeFilter = filterType;
      this.fetchResidents(1);
    },

    async fetchResidents(page = 1) {
      this.loading = true;
      try {
        const res = await axios.get(this.endpoint("admin/development-fees/get"), {
          params: {
            page,
            per_page: this.perPage,
            search: this.search,
            filter: this.activeFilter,
          },
        });

        this.residents = res.data.data || [];
        this.currentPage = res.data.current_page || 1;
        this.totalPages = res.data.last_page || 1;
        this.total = res.data.total || 0;
        this.from = res.data.from ? res.data.from - 1 : 0;
      } catch (e) {
        console.error(e);
        this.toast("Failed to load residents data", "error");
      } finally {
        this.loading = false;
      }
    },

    openPayModal(resident) {
      this.selectedResident = resident;
      this.payAmount = resident.default_fee || 3000;

      const modalEl = document.getElementById("payDevFeeModal");
      if (modalEl && window.bootstrap) {
        this.payModalInstance = window.bootstrap.Modal.getOrCreateInstance(modalEl);
        this.payModalInstance.show();
      }
    },

    async submitPayFee() {
      if (!this.selectedResident || !this.payAmount) return;

      this.submitting = true;
      try {
        const res = await axios.post(
          this.endpoint(`admin/development-fees/${this.selectedResident.id}/pay`),
          {
            amount: this.payAmount,
          }
        );

        if (res.data.success) {
          this.toast(res.data.message || "Development fee saved successfully!");
          if (this.payModalInstance) {
            this.payModalInstance.hide();
          }
          this.fetchResidents(this.currentPage);
        } else {
          this.toast(res.data.message || "Failed to record development fee.", "error");
        }
      } catch (e) {
        console.error(e);
        this.toast("Failed to record fee.", "error");
      } finally {
        this.submitting = false;
      }
    },

    printReceiptModal(resident) {
      this.receiptResident = resident;

      const modalEl = document.getElementById("receiptModal");
      if (modalEl && window.bootstrap) {
        this.receiptModalInstance = window.bootstrap.Modal.getOrCreateInstance(modalEl);
        this.receiptModalInstance.show();
      }
    },

    printReceiptNow() {
      const content = document.getElementById("printableReceiptArea").innerHTML;
      const printWindow = window.open("", "_blank", "width=800,height=600");
      printWindow.document.write(`
        <html>
          <head>
            <title>Development Fee Receipt</title>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
            <style>
              body { padding: 20px; font-family: 'Segoe UI', Arial, sans-serif; }
              @media print {
                body { padding: 0; }
                .btn { display: none !important; }
              }
            </style>
          </head>
          <body onload="window.print(); window.close();">
            ${content}
          </body>
        </html>
      `);
      printWindow.document.close();
    },
  },
};
</script>

<style scoped>
.form-control,
.form-select {
  border-radius: 8px;
  padding: 0.55rem 0.75rem;
  border: 1px solid #dce0e4;
}

.table td {
  vertical-align: middle;
}

.table thead th {
  vertical-align: middle;
  text-align: center;
  font-size: 13px;
  font-weight: 700;
  color: #374151;
  background: #f3f4f6;
}
</style>
