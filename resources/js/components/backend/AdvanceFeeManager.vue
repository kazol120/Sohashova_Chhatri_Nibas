<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-12">
        <div class="card mb-4 shadow-sm border-0">
          <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3 bg-light border-bottom">
            <h5 class="card-title mb-0 text-primary fw-bold">
              <i class="ti ti-wallet me-2"></i>Advance Fee Manager (এডভান্স ফি ব্যবস্থাপনা)
            </h5>

            <div class="d-flex align-items-center gap-2">
              <input
                type="text"
                class="form-control form-control-sm"
                style="width: 280px"
                placeholder="Search resident / phone / room..."
                v-model="search"
              />
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
                    <th style="width: 160px">Advance Fee Status</th>
                    <th style="width: 160px" class="text-center">Action</th>
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
                      <span class="badge bg-success px-2 py-1 fs-6">
                        <i class="ti ti-check me-1"></i> Paid (৳ {{ formatCurrency(r.advance_fee) }})
                      </span>
                    </td>
                    <td class="text-center">
                      <button
                        class="btn btn-sm btn-dark fw-semibold"
                        @click="printReceiptModal(r)"
                      >
                        <i class="ti ti-printer me-1"></i> Print Receipt
                      </button>
                    </td>
                  </tr>
                </tbody>

                <tbody v-else>
                  <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                      <span v-if="loading">
                        <i class="fa fa-spinner fa-spin me-2"></i> Loading data...
                      </span>
                      <span v-else>No residents found</span>
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

    <!-- Printable Receipt Modal -->
    <div
      class="modal fade"
      id="advReceiptModal"
      tabindex="-1"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0">
          <div class="modal-header bg-dark text-white py-2 px-3">
            <h6 class="modal-title text-white mb-0 fw-bold">
              <i class="ti ti-printer me-2"></i>Advance Fee Receipt (এডভান্স ফি মানি রিসিট)
            </h6>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body p-4" id="printableAdvReceiptArea" v-if="receiptResident">
            <!-- Receipt Template Box -->
            <div class="receipt-box p-4 border border-2 border-dark rounded-3 bg-white" style="font-family: 'Segoe UI', Arial, sans-serif;">
              <div class="text-center border-bottom pb-3 mb-3">
                <h3 class="fw-bold mb-1" style="color: #033364;">টি এস এস ভিলা</h3>
                <div class="small text-dark fw-semibold">কলেজ রোড, নেসকোগেট সংলগ্ন, রংপুর | ফোন: +8801977270920</div>
                <div class="badge bg-dark fs-6 px-3 py-1 mt-2 text-uppercase">ADVANCE FEE MONEY RECEIPT</div>
              </div>

              <div class="d-flex justify-content-between align-items-center mb-3 small text-muted">
                <div>Receipt No: <strong>#ADV-{{ receiptResident.id }}</strong></div>
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
                  <tr v-if="isStudent(receiptResident)">
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
                    <td class="fw-bold text-primary">Advance Fee (এডভান্স ফি)</td>
                  </tr>
                  <tr>
                    <th class="bg-light">Amount Paid:</th>
                    <td class="fw-bold fs-5 text-success">৳ {{ formatCurrency(receiptResident.advance_fee) }}</td>
                  </tr>
                  <tr>
                    <th class="bg-light">Payment Status:</th>
                    <td>
                      <span class="badge bg-success px-3 py-1 fs-6">PAID / পরিশোধিত</span>
                    </td>
                  </tr>
                </tbody>
              </table>

              <!-- Rules Section -->
              <div class="mt-4 pt-2 border-top">
                <div class="d-inline-block bg-danger text-white fw-bold px-3 py-1 rounded-pill mb-2" style="font-size: 13px;">
                  নিয়মাবলী
                </div>
                <ul class="list-unstyled mb-3" style="font-size: 12.5px; line-height: 1.8; color: #1f2937;">
                  <li>❖ প্রতি মাসের ভাড়া ঐ মাসের ৭ তারিখের মধ্যে দিতে হবে।</li>
                  <li>❖ সীট ছাড়তে চাইলে অবশ্যই ২ মাস আগে লিখিত জানাতে হবে। অন্যথায় ২ মাসের ভাড়া দিয়ে মেস ছাড়তে হবে।</li>
                  <li>❖ লাইট/ফ্যান/দেয়াল সহ অন্যান্য জিনিস নষ্ট করলে ক্ষতিপূরণ দিতে হবে।</li>
                </ul>
              </div>

              <div class="d-flex justify-content-end align-items-end mt-4 pt-3">
                <div class="text-center" style="width: 220px;">
                  <div style="border-top: 1px solid #000; padding-top: 4px;" class="small fw-bold">
                    অফিস কর্তৃপক্ষের স্বাক্ষর
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

export default {
  name: "AdvanceFeeManager",

  data() {
    return {
      residents: [],
      loading: false,
      search: "",
      perPage: 10,
      currentPage: 1,
      totalPages: 1,
      total: 0,
      from: 0,
      _t: null,

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

    isStudent(r) {
      if (!r || !r.user_type) return false;
      const u = String(r.user_type).toLowerCase().trim();
      return u === "student" || u === "ছাত্রী";
    },

    formatDate(d) {
      if (!d) return new Date().toLocaleDateString("en-GB");
      const date = new Date(d);
      return date.toLocaleDateString("en-GB");
    },

    endpoint(path) {
      const base = this.url.endsWith("/") ? this.url.slice(0, -1) : this.url;
      const cleanPath = path.startsWith("/") ? path : `/${path}`;
      return `${base}${cleanPath}`;
    },

    async fetchResidents(page = 1) {
      this.loading = true;
      try {
        const res = await axios.get(this.endpoint("advance-fees/get"), {
          params: {
            page,
            per_page: this.perPage,
            search: this.search,
          },
        });

        this.residents = res.data.data || [];
        this.currentPage = res.data.current_page || 1;
        this.totalPages = res.data.last_page || 1;
        this.total = res.data.total || 0;
        this.from = res.data.from ? res.data.from - 1 : 0;
      } catch (e) {
        console.error(e);
      } finally {
        this.loading = false;
      }
    },

    printReceiptModal(resident) {
      this.receiptResident = resident;

      const modalEl = document.getElementById("advReceiptModal");
      if (modalEl && window.bootstrap) {
        this.receiptModalInstance = window.bootstrap.Modal.getOrCreateInstance(modalEl);
        this.receiptModalInstance.show();
      }
    },

    printReceiptNow() {
      const content = document.getElementById("printableAdvReceiptArea").innerHTML;
      let iframe = document.getElementById("advReceiptPrintIframe");
      if (!iframe) {
        iframe = document.createElement("iframe");
        iframe.id = "advReceiptPrintIframe";
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
      doc.write(`
        <!DOCTYPE html>
        <html>
          <head>
            <title>Advance Fee Receipt - টি এস এস ভিলা</title>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
            <style>
              body { padding: 25px; font-family: 'Segoe UI', Arial, sans-serif; background: #fff; }
              .receipt-box { border: 2px solid #000 !important; border-radius: 8px; padding: 25px; }
              @media print {
                body { padding: 0; }
                .receipt-box { border: 2px solid #000 !important; }
              }
            </style>
          </head>
          <body>
            ${content}
          </body>
        </html>
      `);
      doc.close();
      setTimeout(() => {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
      }, 300);
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
