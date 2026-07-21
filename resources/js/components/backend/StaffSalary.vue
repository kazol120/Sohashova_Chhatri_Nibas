<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
      <div class="col-12">
        <div class="card mb-4 shadow-sm">

          <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3">
            <h5 class="card-title mb-0">Staffs Salary Data Table</h5>
            <button class="btn btn-primary" type="button" @click="openCreateFromComponent">
              <i class="ti ti-plus me-1"></i> Add Staff Salary
            </button>
          </div>

          <!-- Date Filter + Print Button -->
          <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3">
            <div>
              <div class="px-3 pt-3">
                <div class="d-flex mb-4">
                  <div>
                    <label class="mb-2 text-black">Start Date</label>
                    <input class="form-control" type="date" v-model="startDate" @change="filterData">
                  </div>

                  <div class="ms-4">
                    <label class="mb-2 text-black">End Date</label>
                    <input class="form-control" type="date" v-model="endDate" @change="filterData">
                  </div>
                  
                  <div class="ms-4 d-flex align-items-end">
                    <button class="btn btn-outline-secondary" @click="clearFilters">Clear</button>
                  </div>
                </div>
              </div>
            </div>

            <div>
              <button class="btn btn-primary" type="button" @click="printTable">
                <i class="ti ti-printer me-1"></i> Print
              </button>
            </div>
          </div>

          <!-- Staff Select Dropdown -->
          <div class="px-3 pt-3">
            <div class="d-flex mb-4">
              <select
                v-model="form.selected_staff"
                class="form-select"
                style="max-width: 300px;"
                @change="staffsalary(1)">
                <option value="">All Staff</option>
                <option
                  v-for="staff in staffNames"
                  :key="staff.id"
                  :value="staff.id">
                  {{ staff.name }}
                </option>
              </select>
              <div class="ms-4 d-flex align-items-end">
                <button class="btn btn-outline-secondary" @click="clearStaffFilter">Clear</button>
              </div>
            </div>
          </div>

          <div class="card-body">
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
              <div class="d-flex align-items-center gap-2">
                <label class="small text-muted mb-0">Rows:</label>
                <select class="form-select form-select-sm" style="width:90px" v-model.number="perPage">
                  <option :value="5">5</option>
                  <option :value="10">10</option>
                  <option :value="20">20</option>
                  <option :value="50">50</option>
                </select>
              </div>
              <input
                type="text"
                class="form-control form-control-sm"
                style="width:300px"
                placeholder="Search employee id / name / phone..."
                v-model="search"
                @keyup.enter="staffsalary(1)"
              />
            </div>

            <div class="table-responsive">
              <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th style="width:55px">Sl</th>
                    <th>Staff Name</th>
                    
                    <th>Salary Month</th>
                        <th>date</th>
                    <th>Payment Type</th>
                    <th>Amount</th>
                    <th>Created By</th>
                    <th>Total Amount</th>
                  </tr>
                </thead>
                <tbody v-if="staffs.length">
                  <template v-for="(item, index) in staffs" :key="item.group_key">
                    <tr class="staff-main-row" @click="toggleExpand(item.group_key)" style="cursor:pointer">
                      <td class="text-muted small">{{ from + index }}</td>
                      <td>
                        <div class="fw-semibold">{{ item.staff ? item.staff.name : '—' }}</div>
                      </td>
                      <td v-if="!expanded.includes(item.group_key)">
                        <div class="fw-semibold">{{ item.month_label }}</div>
                      </td>
                      <td v-else></td>
                      <td v-if="!expanded.includes(item.group_key)">
                      <div class="fw-semibold">{{ item.create_at_date }}</div>
                      </td>
                      <td v-else></td>
                      <td v-if="!expanded.includes(item.group_key)">
                        <span
                          v-for="(type, ti) in item.payment_types"
                          :key="ti"
                          class="badge me-1"
                          :class="type === 'advance'
                            ? 'bg-warning-subtle text-warning'
                            : type === 'net_payable'
                              ? 'bg-info-subtle text-info'
                              : 'bg-success-subtle text-success'">
                          {{ type === 'advance' ? 'Advance' : type === 'net_payable' ? 'Net Payable' : 'Full' }}
                        </span>
                      </td>
                      <td v-else></td>
                      <td class="text-muted small">{{ item.payments.length }} payment(s)</td>
                      <td class="text-muted small">{{ item.created_by }}</td>
                      <td class="fw-bold text-primary">
                        {{ formatAmount(item.total_amount) }}
                        <i class="fa ms-1 small" :class="expanded.includes(item.group_key) ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                      </td>
                    </tr>
                   <template v-if="expanded.includes(item.group_key)">
                    <tr v-for="(p, pi) in item.payments" :key="'p-' + pi" class="payment-detail-row">
                      <td class="text-muted small ps-4">↳</td>
                      <td class="small text-muted">{{ p.payment_date }}</td>
                      <td class="small">{{ item.month_label }}</td>
                      <td class="small text-muted">{{ p.payment_date }}</td>  <!-- এখানে date -->
                      <td>
                        <span class="badge" :class="p.payment_type === 'advance'
                          ? 'bg-warning-subtle text-warning'
                          : p.payment_type === 'net_payable'
                            ? 'bg-info-subtle text-info'
                            : 'bg-success-subtle text-success'">
                          {{ p.payment_type === 'advance' ? 'Advance' : p.payment_type === 'net_payable' ? 'Net Payable' : 'Full' }}
                        </span>
                      </td>
                      <td class="fw-semibold">{{ formatAmount(p.amount) }}</td>
                      <td class="small text-muted">{{ p.created_by }}</td>
                      <td class="small text-muted">{{ p.note || '—' }}</td>
                    </tr>
                  </template>
                  </template>
                </tbody>

                <tbody v-else>
                  <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                      <span v-if="loading"><i class="fa fa-spinner fa-spin me-2"></i>Loading...</span>
                      <span v-else>No salary records found</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
              <div class="small text-muted">
                Total: {{ total }} | Page: {{ currentPage }} / {{ totalPages }}
              </div>
              <div class="d-flex gap-2">
                <button class="btn btn-sm btn-secondary" :disabled="currentPage <= 1 || loading" @click="staffsalary(currentPage - 1)">Previous</button>
                <button class="btn btn-sm btn-secondary" :disabled="currentPage >= totalPages || loading" @click="staffsalary(currentPage + 1)">Next</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <StaffSalaryForm
      :show="showCreateModal"
      :base-url="url"
      @close="showCreateModal = false"
      @created="handleCreated"
    />
  </div>
</template>

<script>
import axios from "axios";
import Toastify from "toastify-js";
import "toastify-js/src/toastify.css";
import StaffSalaryForm from "../../components/createform/StaffSalaryForm.vue";

export default {
  name: "StaffSalaryList",
  components: { StaffSalaryForm },

  computed: {
    url() {
      return this.$store.state.url;
    },
  },

  data() {
    return {
      showCreateModal: false,
      staffs: [],
      staffNames: [],
      loading: false,
      search: "",
      perPage: 10,
      startDate: "",
      endDate: "",
      currentPage: 1,
      totalPages: 1,
      total: 0,
      from: 1,
      expanded: [],

      // Staff filter
      form: {
        selected_staff: "",
      },
    };
  },

  mounted() {
    this.staffsalary(1);
    this.getsatffname(); //  Staff name list load
  },

  watch: {
    search() {
      clearTimeout(this._t);
      this._t = setTimeout(() => this.staffsalary(1), 300);
    },
    perPage() {
      this.staffsalary(1);
    },
  },

  methods: {

  formatOnlyDate(dateTime) {
    if (!dateTime) return "-";
    const datePart = dateTime.includes("T") ? dateTime.split("T")[0] : dateTime.split(" ")[0];
    const [year, month, day] = datePart.split("-");
    return `${day}-${month}-${year}`;
  },

    openCreateFromComponent() {
      this.showCreateModal = true;
    },

    handleCreated() {
      this.showCreateModal = false;
      this.staffsalary(1);
    },

    filterData() {
      this.staffsalary(1);
    },

    clearFilters() {
      this.startDate = "";
      this.endDate = "";
      this.search = "";
      this.form.selected_staff = "";
      this.staffsalary(1);
    },

    clearStaffFilter() {
      this.form.selected_staff = "";
      this.staffsalary(1);
    },

    toggleExpand(groupKey) {
      const idx = this.expanded.indexOf(groupKey);
      if (idx === -1) {
        this.expanded.push(groupKey);
      } else {
        this.expanded.splice(idx, 1);
      }
    },

    formatAmount(value) {
      const number = parseFloat(value || 0);
      return `${number.toFixed(2)} ৳`;
    },

    //  Staff name dropdown load - same route as attendance page
    async getsatffname() {
      try {
        const res = await axios.get("/get-select-staff");
        if (res.data.status === "success") {
          this.staffNames = res.data.data || [];
        }
      } catch (error) {
        this.toast("Failed to load staff names.", "error");
      }
    },

  async staffsalary(page = 1) {
  this.loading = true;
  try {
    const res = await axios.get(`${this.url}staff-salary-get`, {
      params: {
        page,
        per_page:   this.perPage,
        search:     this.search,
        start_date: this.startDate,
        end_date:   this.endDate,
        staff_id:   this.form.selected_staff,
      },
    });
    console.log("Response:", res.data);
    this.staffs      = res.data.data         || [];
    this.currentPage = res.data.current_page || 1;
    this.totalPages  = res.data.last_page    || 1;
    this.total       = res.data.total        || 0;
    this.from        = res.data.from         || 1;
  } catch (e) {
    console.log("Error:", e?.response?.data); 
    this.toast("Failed to load salary records", "error");
  } finally {
    this.loading = false;
  }
},

printTable() {
  let sl = 0;
  const rows = this.staffs.map((item, index) => {
    sl++;
    const paymentTypes = item.payment_types.map(type =>
      type === 'advance' ? 'Advance' : type === 'net_payable' ? 'Net Payable' : 'Full'
    ).join(', ');

    const staffName = item.staff ? item.staff.name : '—';
    const employeeId = item.staff ? (item.staff.employee_id || '') : '';
    let html = `
      <tr style="background:#f5f5f5; font-weight:bold;">
        <td>${this.from + index}</td>
        <td>${staffName}${employeeId }</td>
        <td>${item.month_label || '—'}</td>
        <td>${paymentTypes}</td>
        <td>${item.payments.length} payment(s)</td>
        <td>${item.created_by || '—'}</td>
        <td><strong>${this.formatAmount(item.total_amount)}</strong></td>
      </tr>
    `;
    //  Detail rows (expanded payment  print show)
    item.payments.forEach(p => {
      const pType = p.payment_type === 'advance' ? 'Advance'
        : p.payment_type === 'net_payable' ? 'Net Payable' : 'Full';
      html += `
        <tr style="background:#fafbff;">
          <td style="color:#aaa; padding-left:20px;">↳</td>
          <td style="color:#888; font-size:11px;">${p.payment_date || '—'}</td>
          <td style="font-size:11px;">${item.month_label}</td>
          <td><span style="background:#fff3e0;color:#e65100;padding:2px 8px;border-radius:4px;font-size:11px;">${pType}</span></td>
          <td style="font-weight:600;">${this.formatAmount(p.amount)}</td>
          <td style="color:#888;font-size:11px;">${p.created_by || '—'}</td>
          <td style="color:#888;font-size:11px;">${p.note || '—'}</td>
        </tr>
      `;
    });
    return html;
  }).join('');
  const html = `
    <!DOCTYPE html>
    <html>
    <head>
      <title>Staff Salary Report</title>
      <style>
        @page { size: A4 portrait; margin: 15mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h2 { text-align: center; margin-bottom: 6px; font-size: 16px; }
        p.sub { text-align: center; margin-bottom: 12px; font-size: 12px; font-weight: 400;}
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #e9e9e9; font-weight: bold; }
        small { color: #888; font-size: 10px; }
      </style>
    </head>
    <body>
      <h2>Staff Salary Report</h2>
     <p class="sub">Printed: ${new Date().toLocaleString()}</p>
      <table>
        <thead>
          <tr>
            <th>Sl</th>
            <th>Staff Name</th>
            <th>Salary Month</th>
            <th>Payment Type</th>
            <th>Payments</th>
            <th>Created By</th>
            <th>Total Amount</th>
          </tr>
        </thead>
        <tbody>${rows}</tbody>
      </table>
    </body>
    </html>
  `;
    const old = document.getElementById('print-iframe');
    if (old) old.remove();
    const iframe = document.createElement('iframe');
    iframe.id = 'print-iframe';
    iframe.style.cssText = 'position:fixed;top:0;left:0;width:0;height:0;border:none;visibility:hidden;';
    document.body.appendChild(iframe);
    iframe.contentDocument.open();
    iframe.contentDocument.write(html);
    iframe.contentDocument.close();

    iframe.onload = () => {
      setTimeout(() => {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
      }, 300);
    };
  },
  toast(text, type = "success") {
      Toastify({
        text,
        duration: 3000,
        gravity: "top",
        position: "right",
        style: {
          background: type === "success"
            ? "linear-gradient(to right, #22c55e, #16a34a)"
            : "linear-gradient(to right, #ef4444, #dc2626)",
        },
      }).showToast();
    },
  },
};
</script>
<style scoped>
.form-control,
.form-select {
  border-radius: 8px;
  padding: .58rem .75rem;
  border: 1px solid #dce0e4;
}
.form-control:focus,
.form-select:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 .22rem rgba(13,110,253,.12);
}
.staff-main-row:hover {
  background: #f8fafc;
}
.payment-detail-row {
  background: #fafbff;
  border-left: 3px solid #e0e7ff;
}
.payment-detail-row td {
  font-size: 0.85rem;
}
</style>