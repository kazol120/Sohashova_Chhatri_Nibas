<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
      <div class="col-12">
        <div class="card mb-4 shadow-sm">

          <!-- Header -->
          <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3">
            <h5 class="card-title mb-0">Profit / Loss Report</h5>
            <button class="btn btn-primary" type="button" @click="printTable">
              <i class="ti ti-printer me-1"></i> Print
            </button>
          </div>

          <!-- Filter -->
          <div class="card-header">
            <div class="d-flex align-items-end gap-3">
              <div>
                <label class="mb-2 text-black">Select Year</label>
                <select class="form-select" v-model="selectedYear" @change="loadProfitReport">
                  <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
                </select>
              </div>
              <button class="btn btn-outline-secondary" @click="clearFilter">Clear</button>
            </div>
          </div>

          <!-- Product Stock Table -->
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <h6 class="fw-bold mb-0" style="color:#f59e0b;">Product Stock Report</h6>
            </div>
            <div class="table-responsive">
              <table class="table table-bordered table-hover align-middle text-center">
                <thead style="background:#f59e0b; color:#fff;">
                  <tr>
                    <th style="width:60px">SL</th>
                    <th>Current Stock Quantity</th>
                    <th>Total Stock Amount</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>1</td>
                    <td class="fw-semibold">{{ stockData.total_qty }}</td>
                    <td class="fw-bold text-info">
                      {{ parseFloat(stockData.total_amount || 0).toFixed(2) }} ৳
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Profit / Loss Table -->
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <h6 class="fw-bold mb-0" style="color:#f59e0b;">Profit / Loss Report</h6>
              <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm"
                  :class="profitViewMode === 'monthly' ? 'btn-warning' : 'btn-outline-warning'"
                  @click="setProfitMode('monthly')">Monthly</button>
                <button type="button" class="btn btn-sm"
                  :class="profitViewMode === 'yearly' ? 'btn-warning' : 'btn-outline-warning'"
                  @click="setProfitMode('yearly')">Yearly</button>
              </div>
            </div>

            <div class="table-responsive" id="printArea">
              <table class="table table-bordered table-hover align-middle text-center">
                <thead style="background:#f59e0b; color:#fff;">
                  <tr>
                    <th rowspan="2" style="width:120px; vertical-align:middle;">{{ profitViewMode === 'monthly' ? 'Month' : 'Year' }}</th>
                    <!-- Income -->
                    <th colspan="4" style="background:#16a34a; color:#fff;">আয় (Income)</th>
                    <!-- Cost -->
                    <th colspan="4" style="background:#dc2626; color:#fff;">ব্যয় (Expense)</th>
                    <!-- Result -->
                    <th rowspan="2" style="vertical-align:middle; background:#1d4ed8; color:#fff;">Profit / Loss</th>
                  </tr>
                  <tr>
                    <th style="background:#dcfce7; color:#166534;">Room Booking</th>
                    <th style="background:#dcfce7; color:#166534;">Monthly Rent</th>
                    <th style="background:#dcfce7; color:#166534;">Product Sales</th>
                    <th style="background:#bbf7d0; color:#166534; font-weight:700;">Total Income</th>
                    <th style="background:#fee2e2; color:#991b1b;">General Expense</th>
                    <th style="background:#fee2e2; color:#991b1b;">Staff Salary</th>
                    <th style="background:#fee2e2; color:#991b1b;">Product Purchase</th>
                    <th style="background:#fecaca; color:#991b1b; font-weight:700;">Total Cost</th>
                  </tr>
                </thead>

                <tbody v-if="!profitLoading && profitRows.length">
                  <template v-for="row in profitRows" :key="row.label">
                    <tr :class="profitViewMode === 'yearly' ? 'clickable-cell' : ''"
                        @click="profitViewMode === 'yearly' ? toggleYearExpand(row.label) : null">
                      <td class="fw-bold">
                        {{ row.label }}
                        <span v-if="profitViewMode === 'yearly' && row.monthly_breakdown && row.monthly_breakdown.length" class="ms-1 text-warning">
                          {{ expandedYear === row.label ? '⌃' : '⌄' }}
                        </span>
                      </td>
                      <!-- Income -->
                      <td class="text-success fw-semibold">{{ formatAmount(row.room_booking) }}</td>
                      <td class="text-success fw-semibold">{{ formatAmount(row.monthly_payment) }}</td>
                      <td class="text-success fw-semibold">{{ formatAmount(row.product_sales) }}</td>
                      <td class="fw-bold" style="background:#f0fdf4;">{{ formatAmount(row.total_income) }}</td>
                      <!-- Cost -->
                      <td class="text-danger fw-semibold">{{ formatAmount(row.expense) }}</td>
                      <td class="text-danger fw-semibold">{{ formatAmount(row.salary) }}</td>
                      <td class="text-danger fw-semibold">{{ formatAmount(row.product_purchase) }}</td>
                      <td class="fw-bold" style="background:#fff1f2;">{{ formatAmount(row.total_cost) }}</td>
                      <!-- Profit/Loss -->
                      <td class="fw-bold">
                        <span class="badge px-3 py-2" style="font-size:0.82rem;"
                          :class="row.profit_loss >= 0 ? 'bg-success' : 'bg-danger'">
                          {{ row.profit_loss >= 0 ? '▲ Profit' : '▼ Loss' }}
                          {{ formatAbsAmount(row.profit_loss) }}
                        </span>
                      </td>
                    </tr>

                    <!-- Yearly monthly breakdown -->
                    <tr v-if="profitViewMode === 'yearly' && expandedYear === row.label" :key="row.label + '-expand'">
                      <td colspan="10" class="p-0">
                        <div class="detail-box-profit">
                          <div class="detail-title">📅 {{ row.label }} - Monthly Breakdown</div>
                          <table class="table table-sm table-bordered mb-0 text-center">
                            <thead style="background:#fff8e1;">
                              <tr>
                                <th>Month</th>
                                <th style="color:#16a34a;">Room Booking</th>
                                <th style="color:#16a34a;">Monthly Rent</th>
                                <th style="color:#16a34a;">Product Sales</th>
                                <th style="color:#166534; font-weight:700;">Total Income</th>
                                <th style="color:#dc2626;">Expense</th>
                                <th style="color:#dc2626;">Salary</th>
                                <th style="color:#dc2626;">Product Purchase</th>
                                <th style="color:#991b1b; font-weight:700;">Total Cost</th>
                                <th>Profit / Loss</th>
                              </tr>
                            </thead>
                            <tbody v-if="row.monthly_breakdown && row.monthly_breakdown.length">
                              <tr v-for="m in row.monthly_breakdown" :key="m.month">
                                <td class="fw-bold">{{ m.month }} {{ row.label }}</td>
                                <td class="text-success">{{ formatAmount(m.room_booking) }}</td>
                                <td class="text-success">{{ formatAmount(m.monthly_payment) }}</td>
                                <td class="text-success">{{ formatAmount(m.product_sales) }}</td>
                                <td class="fw-bold" style="background:#f0fdf4;">{{ formatAmount(m.total_income) }}</td>
                                <td class="text-danger">{{ formatAmount(m.expense) }}</td>
                                <td class="text-danger">{{ formatAmount(m.salary) }}</td>
                                <td class="text-danger">{{ formatAmount(m.product_purchase) }}</td>
                                <td class="fw-bold" style="background:#fff1f2;">{{ formatAmount(m.total_cost) }}</td>
                                <td>
                                  <span class="badge px-2 py-1"
                                    :class="m.profit_loss >= 0 ? 'bg-success' : 'bg-danger'">
                                    {{ m.profit_loss >= 0 ? '▲' : '▼' }}
                                    {{ formatAbsAmount(m.profit_loss) }}
                                  </span>
                                </td>
                              </tr>
                            </tbody>
                            <tbody v-else>
                              <tr><td colspan="10" class="text-center text-muted">No data</td></tr>
                            </tbody>
                          </table>
                        </div>
                      </td>
                    </tr>
                  </template>

                  <!-- Grand Total -->
                  <tr style="background:#fff8e1;" class="fw-bold">
                    <td>Grand Total</td>
                    <td class="text-success">{{ formatAmount(profitGrand.room_booking) }}</td>
                    <td class="text-success">{{ formatAmount(profitGrand.monthly_payment) }}</td>
                    <td class="text-success">{{ formatAmount(profitGrand.product_sales) }}</td>
                    <td style="background:#f0fdf4;">{{ formatAmount(profitGrand.total_income) }}</td>
                    <td class="text-danger">{{ formatAmount(profitGrand.expense) }}</td>
                    <td class="text-danger">{{ formatAmount(profitGrand.salary) }}</td>
                    <td class="text-danger">{{ formatAmount(profitGrand.product_purchase) }}</td>
                    <td style="background:#fff1f2;">{{ formatAmount(profitGrand.total_cost) }}</td>
                    <td>
                      <span class="badge px-3 py-2" style="font-size:0.82rem;"
                        :class="profitGrand.profit_loss >= 0 ? 'bg-success' : 'bg-danger'">
                        {{ profitGrand.profit_loss >= 0 ? '▲ Profit' : '▼ Loss' }}
                        {{ formatAbsAmount(profitGrand.profit_loss) }}
                      </span>
                    </td>
                  </tr>
                </tbody>

                <tbody v-else>
                  <tr>
                    <td colspan="10" class="text-center py-5 text-muted">
                      <span v-if="profitLoading"><i class="fa fa-spinner fa-spin me-2"></i>Loading...</span>
                      <span v-else>No data found</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
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
  name: "ProfitLossReport",

  computed: {
    url() {
      return this.$store.state.url;
    },
    profitGrand() {
      return this.profitRows.reduce(
        (acc, row) => {
          acc.room_booking     += row.room_booking     || 0;
          acc.monthly_payment  += row.monthly_payment  || 0;
          acc.product_sales    += row.product_sales    || 0;
          acc.total_income     += row.total_income     || 0;
          acc.expense          += row.expense          || 0;
          acc.salary           += row.salary           || 0;
          acc.product_purchase += row.product_purchase || 0;
          acc.total_cost       += row.total_cost       || 0;
          acc.profit_loss      += row.profit_loss      || 0;
          return acc;
        },
        { room_booking: 0, monthly_payment: 0, product_sales: 0, total_income: 0,
          expense: 0, salary: 0, product_purchase: 0, total_cost: 0, profit_loss: 0 }
      );
    },
  },

  data() {
    const currentYear = new Date().getFullYear();
    return {
      profitRows: [],
      profitLoading: false,
      profitViewMode: 'monthly',
      availableYears: [currentYear],
      selectedYear: currentYear,
      expandedYear: "",
      stockData: {},
      stockLoading: false
    };
  },

  mounted() {
    this.loadAvailableYears();
    this.loadProductStock();
  },

  methods: {
    async loadAvailableYears() {
      try {
        const res = await axios.get(`${this.url}get-available-years`);
        this.availableYears = res.data.years || [new Date().getFullYear()];
        this.selectedYear = this.availableYears[0];
      } catch (e) {
        this.availableYears = [new Date().getFullYear()];
      }
      this.loadProfitReport();
    },

    async loadProductStock() {
      this.stockLoading = true;
      try {
        const res = await axios.get('/get-productstock');
        this.stockData = res.data;
      } catch (e) {
        console.error(e);
      } finally {
        this.stockLoading = false;
      }
    },

    async loadProfitReport() {
      this.profitLoading = true;
      this.expandedYear = "";
      try {
        const params = this.profitViewMode === 'monthly'
          ? { mode: 'monthly', year: this.selectedYear }
          : { mode: 'yearly' };
        const res = await axios.get(`${this.url}get-profit-loss-report`, { params });
        this.profitRows = res.data.data || [];
      } catch (e) {
        this.toast("Failed to load profit/loss report", "error");
      } finally {
        this.profitLoading = false;
      }
    },

    setProfitMode(mode) {
      this.profitViewMode = mode;
      this.loadProfitReport();
    },

    clearFilter() {
      this.selectedYear = new Date().getFullYear();
      this.loadProfitReport();
    },

    toggleYearExpand(label) {
      this.expandedYear = this.expandedYear === label ? "" : label;
    },

    formatAmount(value) {
      return `${Number(value || 0).toFixed(2)} ৳`;
    },

    formatAbsAmount(value) {
      return `${Math.abs(Number(value || 0)).toFixed(2)} ৳`;
    },

    printTable() {
      const printContents = document.getElementById("printArea").innerHTML;
      const html = `<!DOCTYPE html>
<html>
<head>
  <title>Profit Loss Report</title>
  <style>
    @page { size: A4 landscape; margin: 12mm; }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: Arial, sans-serif; font-size: 10px; color: #333; }
    .print-header { text-align: center; margin-bottom: 12px; }
    .print-header h2 { font-size: 16px; font-weight: bold; margin-bottom: 3px; }
    .print-header p { font-size: 11px; color: #666; }
    table { width: 100%; border-collapse: collapse; margin-top: 8px; }
    th, td { border: 1px solid #ccc; padding: 5px 6px; text-align: center; }
    thead th { font-weight: bold; font-size: 10px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .text-success { color: #16a34a !important; font-weight: 600; }
    .text-danger  { color: #dc2626 !important; font-weight: 600; }
    .fw-bold { font-weight: bold; }
    .fw-semibold { font-weight: 600; }
    .detail-box-profit { display: none !important; }
    .badge { display: inline-block !important; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .bg-success { background: #22c55e !important; }
    .bg-danger  { background: #ef4444 !important; }
  </style>
</head>
<body>
  <div class="print-header">
    <h2>Profit / Loss Report</h2>
    <p>
      ${this.profitViewMode === 'monthly' ? 'Year: ' + this.selectedYear : 'All Years Summary'}
      &nbsp;|&nbsp; Mode: ${this.profitViewMode === 'monthly' ? 'Monthly' : 'Yearly'}
      &nbsp;|&nbsp; Printed: ${new Date().toLocaleString()}
    </p>
  </div>
  ${printContents}
  <script>
    window.onload = function() {
      window.print();
      window.onafterprint = function() { window.close(); };
    };
  <\/script>
</body>
</html>`;

      const win = window.open("", "_blank");
      win.document.write(html);
      win.document.close();
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
.form-select {
  border-radius: 8px;
  padding: .58rem .75rem;
  border: 1px solid #dce0e4;
}
.form-select:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 .22rem rgba(13,110,253,.12);
}
.table th,
.table td {
  vertical-align: middle;
  white-space: nowrap;
}
.clickable-cell {
  cursor: pointer;
}
.clickable-cell:hover {
  background: #fffbeb;
}
.detail-box-profit {
  background: #fffbeb;
  padding: 12px;
  border-left: 3px solid #f59e0b;
}
.detail-title {
  font-weight: 600;
  text-align: left;
  margin-bottom: 8px;
}
</style>