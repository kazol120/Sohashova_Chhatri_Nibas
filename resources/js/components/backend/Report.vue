<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
      <div class="col-12">
        <div class="card mb-4 shadow-sm">

          <!-- Top Header -->
          <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3 border-bottom">
            <div class="d-flex align-items-center gap-2">
              <i class="ti ti-chart-pie-2 text-primary fs-3"></i>
              <div>
                <h5 class="card-title mb-0 fw-bold">Profit / Loss Report (লাভ-ক্ষতি রিপোর্ট)</h5>
                <small class="text-muted">দৈনিক, মাসিক ও বাৎসরিক পূর্ণাঙ্গ আর্থিক আয়-ব্যয় বিবরণী</small>
              </div>
            </div>
            <div class="d-flex gap-2">
              <button class="btn btn-success btn-sm px-3 shadow-sm fw-semibold" type="button" @click="exportProfitReportCSV">
                <i class="fa fa-file-excel me-1"></i> Export Excel
              </button>
              <button class="btn btn-primary btn-sm px-3 shadow-sm fw-semibold" type="button" @click="printTable">
                <i class="ti ti-printer me-1"></i> Print
              </button>
            </div>
          </div>

          <!-- Mode Selection & Filters -->
          <div class="card-header bg-light py-3 border-bottom">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
              
              <!-- 3 Tab Buttons (Daily, Monthly, Yearly) -->
              <div class="btn-group shadow-sm" role="group">
                <button type="button" class="btn px-4 fw-bold"
                  :class="profitViewMode === 'daily' ? 'btn-primary' : 'btn-outline-primary'"
                  @click="setProfitMode('daily')">
                  <i class="ti ti-calendar-event me-1"></i> Daily (দৈনিক)
                </button>
                <button type="button" class="btn px-4 fw-bold"
                  :class="profitViewMode === 'monthly' ? 'btn-primary' : 'btn-outline-primary'"
                  @click="setProfitMode('monthly')">
                  <i class="ti ti-calendar-month me-1"></i> Monthly (মাসিক)
                </button>
                <button type="button" class="btn px-4 fw-bold"
                  :class="profitViewMode === 'yearly' ? 'btn-primary' : 'btn-outline-primary'"
                  @click="setProfitMode('yearly')">
                  <i class="ti ti-calendar me-1"></i> Yearly (বাৎসরিক)
                </button>
              </div>

              <!-- Filter Controls depending on Mode -->
              <div class="d-flex flex-wrap align-items-center gap-2">
                
                <!-- Daily Filter Controls -->
                <template v-if="profitViewMode === 'daily'">
                  <div class="d-flex align-items-center gap-1">
                    <label class="small text-muted fw-bold mb-0">From:</label>
                    <input type="date" class="form-control form-control-sm" v-model="filterStartDate" style="width: 145px;" />
                  </div>
                  <div class="d-flex align-items-center gap-1">
                    <label class="small text-muted fw-bold mb-0">To:</label>
                    <input type="date" class="form-control form-control-sm" v-model="filterEndDate" style="width: 145px;" />
                  </div>
                  <button class="btn btn-primary btn-sm px-3 fw-bold" @click="loadProfitReport">
                    <i class="ti ti-filter me-1"></i> Filter
                  </button>
                  <button class="btn btn-outline-secondary btn-sm" @click="clearDailyFilter" v-if="filterStartDate || filterEndDate">
                    <i class="ti ti-rotate-clockwise me-1"></i> Reset
                  </button>
                </template>

                <!-- Monthly Filter Controls -->
                <template v-else-if="profitViewMode === 'monthly'">
                  <div class="d-flex align-items-center gap-2">
                    <label class="small text-muted fw-bold mb-0">Select Year:</label>
                    <select class="form-select form-select-sm" v-model="selectedYear" @change="loadProfitReport" style="width: 130px;">
                      <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
                    </select>
                  </div>
                  <button class="btn btn-outline-secondary btn-sm" @click="clearFilter">Reset</button>
                </template>

                <!-- Yearly Filter Controls -->
                <template v-else-if="profitViewMode === 'yearly'">
                  <div class="d-flex align-items-center gap-2">
                    <label class="small text-muted fw-bold mb-0">Select Year:</label>
                    <select class="form-select form-select-sm" v-model="selectedYearlyFilter" @change="loadProfitReport" style="width: 145px;">
                      <option value="all">All Years (সকল)</option>
                      <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
                    </select>
                  </div>
                  <button class="btn btn-outline-secondary btn-sm" @click="clearYearlyFilter">Reset</button>
                </template>

              </div>

            </div>
          </div>

          <!-- Summary Highlights Cards -->
          <div class="card-body pb-0 pt-3">
            <div class="row g-3">
              <div class="col-12 col-md-4">
                <div class="d-flex align-items-center p-3 rounded-3 shadow-none border" style="background: #f0fdf4; border-color: #86efac !important;">
                  <div class="avatar avatar-md me-3 rounded-3 d-flex align-items-center justify-content-center" style="background: #22c55e; color: #fff;">
                    <i class="ti ti-arrow-down-left fs-3"></i>
                  </div>
                  <div>
                    <span class="text-muted d-block small fw-bold text-uppercase">Total Income (সর্বমোট আয়)</span>
                    <h4 class="mb-0 fw-bold font-monospace text-success">{{ formatAmount(profitGrand.total_income) }}</h4>
                  </div>
                </div>
              </div>

              <div class="col-12 col-md-4">
                <div class="d-flex align-items-center p-3 rounded-3 shadow-none border" style="background: #fff1f2; border-color: #fca5a5 !important;">
                  <div class="avatar avatar-md me-3 rounded-3 d-flex align-items-center justify-content-center" style="background: #ef4444; color: #fff;">
                    <i class="ti ti-arrow-up-right fs-3"></i>
                  </div>
                  <div>
                    <span class="text-muted d-block small fw-bold text-uppercase">Total Cost (সর্বমোট ব্যয়)</span>
                    <h4 class="mb-0 fw-bold font-monospace text-danger">{{ formatAmount(profitGrand.total_cost) }}</h4>
                  </div>
                </div>
              </div>

              <div class="col-12 col-md-4">
                <div class="d-flex align-items-center p-3 rounded-3 shadow-none border" 
                  :style="profitGrand.profit_loss >= 0 
                    ? 'background: #eff6ff; border-color: #93c5fd !important;' 
                    : 'background: #fff1f2; border-color: #fca5a5 !important;'">
                  <div class="avatar avatar-md me-3 rounded-3 d-flex align-items-center justify-content-center" 
                    :style="profitGrand.profit_loss >= 0 ? 'background: #3b82f6; color: #fff;' : 'background: #dc2626; color: #fff;'">
                    <i :class="profitGrand.profit_loss >= 0 ? 'ti ti-trending-up fs-3' : 'ti ti-trending-down fs-3'"></i>
                  </div>
                  <div>
                    <span class="text-muted d-block small fw-bold text-uppercase">Net Result (নিট ফলাফল)</span>
                    <h4 class="mb-0 fw-bold font-monospace" :class="profitGrand.profit_loss >= 0 ? 'text-primary' : 'text-danger'">
                      {{ profitGrand.profit_loss >= 0 ? '▲ Profit ' : '▼ Loss ' }} {{ formatAbsAmount(profitGrand.profit_loss) }}
                    </h4>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Profit / Loss Main Table -->
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <h6 class="fw-bold mb-0 text-dark">
                <span v-if="profitViewMode === 'daily'">📅 Daily Statement Breakdown (দৈনিক বিবরণী)</span>
                <span v-else-if="profitViewMode === 'monthly'">📆 Monthly Statement Breakdown for {{ selectedYear }} (মাসিক বিবরণী)</span>
                <span v-else>🗓️ Yearly Comparative Statement (বাৎসরিক বিবরণী)</span>
              </h6>
              <span class="small text-muted">Showing {{ profitRows.length }} rows</span>
            </div>

            <div class="border rounded-3 shadow-xs" id="printArea">
              <table class="table table-bordered table-hover align-middle text-center mb-0 statement-table">
                <thead style="background:#033364; color:#fff;">
                  <tr>
                    <th rowspan="2" style="width: 9%; vertical-align:middle; background: #0f172a; color: #fff;">
                      <span v-if="profitViewMode === 'daily'">Date<br><small class="fw-normal text-light opacity-75">(তারিখ)</small></span>
                      <span v-else-if="profitViewMode === 'monthly'">Month<br><small class="fw-normal text-light opacity-75">(মাস)</small></span>
                      <span v-else>Year<br><small class="fw-normal text-light opacity-75">(বছর)</small></span>
                    </th>
                    <!-- Income Columns -->
                    <th colspan="6" style="background:#15803d; color:#fff; font-size: 0.85rem; padding: 5px 2px;">🟢 আয় (INCOME)</th>
                    <!-- Expense Columns -->
                    <th colspan="4" style="background:#b91c1c; color:#fff; font-size: 0.85rem; padding: 5px 2px;">🔴 ব্যয় (EXPENSE)</th>
                    <!-- Result -->
                    <th rowspan="2" style="width: 9%; vertical-align:middle; background:#1e40af; color:#fff;">Net Result<br><small class="fw-normal text-light opacity-75">(লাভ / ক্ষতি)</small></th>
                    <!-- Action -->
                    <th rowspan="2" style="width: 5%; vertical-align:middle; background:#475569; color:#fff;">Action<br><small class="fw-normal text-light opacity-75">(প্রিন্ট)</small></th>
                  </tr>
                  <tr class="sub-headers">
                    <th style="width: 7.5%; background:#f0fdf4; color:#166534;">Advance<br>Booking</th>
                    <th style="width: 7.5%; background:#f0fdf4; color:#166534;">Development<br>Fee</th>
                    <th style="width: 7.5%; background:#f0fdf4; color:#166534;">Monthly<br>Rent</th>
                    <th style="width: 7.5%; background:#f0fdf4; color:#166534;">Electricity<br>Bill</th>
                    <th style="width: 7%; background:#f0fdf4; color:#166534;">Room Change<br>Fee</th>
                    <th style="width: 8.5%; background:#dcfce7; color:#14532d; font-weight:800;">Total<br>Income</th>
                    <th style="width: 7.5%; background:#fff1f2; color:#991b1b;">General<br>Expense</th>
                    <th style="width: 7.5%; background:#fff1f2; color:#991b1b;">Staff<br>Salary</th>
                    <th style="width: 7.5%; background:#fff1f2; color:#991b1b;">Advance<br>Refund</th>
                    <th style="width: 8.5%; background:#fee2e2; color:#7f1d1d; font-weight:800;">Total<br>Cost</th>
                  </tr>
                </thead>

                <tbody v-if="!profitLoading && profitRows.length">
                  <template v-for="row in profitRows" :key="row.label">
                    <tr :class="profitViewMode === 'yearly' ? 'clickable-cell' : ''"
                        @click="profitViewMode === 'yearly' ? toggleYearExpand(row.label) : null">
                      <td class="fw-bold text-dark">
                        <span class="num-val text-dark">{{ row.label }}</span>
                        <span v-if="profitViewMode === 'yearly' && row.monthly_breakdown && row.monthly_breakdown.length" class="ms-1 text-primary">
                          {{ expandedYear === row.label ? '▲' : '▼' }}
                        </span>
                      </td>
                      <!-- Income -->
                      <td class="text-success"><span class="num-val">{{ formatAmount(row.room_booking) }}</span></td>
                      <td class="text-success"><span class="num-val">{{ formatAmount(row.development_fee) }}</span></td>
                      <td class="text-success"><span class="num-val">{{ formatAmount(row.monthly_payment) }}</span></td>
                      <td class="text-success"><span class="num-val">{{ formatAmount(row.electricity_bill) }}</span></td>
                      <td class="text-success"><span class="num-val">{{ formatAmount(row.room_change_fee) }}</span></td>
                      <td style="background:#f0fdf4; color: #15803d;"><span class="total-num-val">{{ formatAmount(row.total_income) }}</span></td>
                      <!-- Cost -->
                      <td class="text-danger"><span class="num-val">{{ formatAmount(row.expense) }}</span></td>
                      <td class="text-danger"><span class="num-val">{{ formatAmount(row.salary) }}</span></td>
                      <td class="text-danger"><span class="num-val">{{ formatAmount(row.advance_refund) }}</span></td>
                      <td style="background:#fff1f2; color: #b91c1c;"><span class="total-num-val">{{ formatAmount(row.total_cost) }}</span></td>
                      <!-- Profit/Loss -->
                      <td class="p-1">
                        <span class="badge px-2 py-1 font-monospace d-inline-block text-truncate fw-bold" style="font-size:0.80rem; max-width: 100%; border-radius: 4px;"
                          :class="row.profit_loss >= 0 ? 'bg-success' : 'bg-danger'">
                          {{ row.profit_loss >= 0 ? '▲ ' : '▼ ' }} {{ formatAbsAmount(row.profit_loss) }}
                        </span>
                      </td>
                      <!-- Action -->
                      <td class="text-center p-1" @click.stop>
                        <button v-if="profitViewMode === 'daily'"
                          class="btn btn-xs btn-outline-primary shadow-xs px-2 py-1"
                          :disabled="loadingDetail === row.date"
                          @click="fetchAndPrintDailyDetail(row.date, row.label)"
                          title="Print Daily Details">
                          <i v-if="loadingDetail === row.date" class="fa fa-spinner fa-spin"></i>
                          <i v-else class="ti ti-printer fs-6"></i>
                        </button>
                        <button v-else-if="profitViewMode === 'monthly'"
                          class="btn btn-xs btn-outline-primary shadow-xs px-2 py-1"
                          :disabled="loadingDetail === row.label"
                          @click="fetchAndPrintMonthlyDetail(row.label)"
                          title="Print Monthly Details">
                          <i v-if="loadingDetail === row.label" class="fa fa-spinner fa-spin"></i>
                          <i v-else class="ti ti-printer fs-6"></i>
                        </button>
                        <span v-else class="text-muted small">Expand</span>
                      </td>
                    </tr>

                    <!-- Yearly monthly breakdown -->
                    <tr v-if="profitViewMode === 'yearly' && expandedYear === row.label" :key="row.label + '-expand'">
                      <td colspan="13" class="p-0">
                        <div class="detail-box-profit">
                          <div class="detail-title text-primary fw-bold">📅 {{ row.label }} - Month-by-Month Statement Breakdown</div>
                          <table class="table table-sm table-bordered mb-0 text-center bg-white statement-table">
                            <thead style="background:#f1f5f9;">
                              <tr class="sub-headers">
                                <th style="width:9%;">Month</th>
                                <th style="width:7.5%; color:#16a34a;">Advance Booking</th>
                                <th style="width:7.5%; color:#16a34a;">Development Fee</th>
                                <th style="width:7.5%; color:#16a34a;">Monthly Rent</th>
                                <th style="width:7.5%; color:#16a34a;">Electricity Bill</th>
                                <th style="width:7%; color:#16a34a;">Room Change</th>
                                <th style="width:8.5%; color:#14532d; font-weight:800; background:#dcfce7;">Total Income</th>
                                <th style="width:7.5%; color:#dc2626;">Expense</th>
                                <th style="width:7.5%; color:#dc2626;">Salary</th>
                                <th style="width:7.5%; color:#dc2626;">Advance Refund</th>
                                <th style="width:8.5%; color:#7f1d1d; font-weight:800; background:#fee2e2;">Total Cost</th>
                                <th style="width:9%;">Profit / Loss</th>
                                <th style="width:5%;">Action</th>
                              </tr>
                            </thead>
                            <tbody v-if="row.monthly_breakdown && row.monthly_breakdown.length">
                              <tr v-for="m in row.monthly_breakdown" :key="m.month">
                                <td class="fw-bold">{{ m.month }} {{ row.label }}</td>
                                <td class="text-success"><span class="num-val">{{ formatAmount(m.room_booking) }}</span></td>
                                <td class="text-success"><span class="num-val">{{ formatAmount(m.development_fee) }}</span></td>
                                <td class="text-success"><span class="num-val">{{ formatAmount(m.monthly_payment) }}</span></td>
                                <td class="text-success"><span class="num-val">{{ formatAmount(m.electricity_bill) }}</span></td>
                                <td class="text-success"><span class="num-val">{{ formatAmount(m.room_change_fee) }}</span></td>
                                <td style="background:#f0fdf4; color:#14532d;"><span class="total-num-val">{{ formatAmount(m.total_income) }}</span></td>
                                <td class="text-danger"><span class="num-val">{{ formatAmount(m.expense) }}</span></td>
                                <td class="text-danger"><span class="num-val">{{ formatAmount(m.salary) }}</span></td>
                                <td class="text-danger"><span class="num-val">{{ formatAmount(m.advance_refund) }}</span></td>
                                <td style="background:#fff1f2; color:#7f1d1d;"><span class="total-num-val">{{ formatAmount(m.total_cost) }}</span></td>
                                <td class="p-1">
                                  <span class="badge px-2 py-1 font-monospace fw-bold"
                                    :class="m.profit_loss >= 0 ? 'bg-success' : 'bg-danger'" style="font-size:0.78rem;">
                                    {{ m.profit_loss >= 0 ? '▲ ' : '▼ ' }}{{ formatAbsAmount(m.profit_loss) }}
                                  </span>
                                </td>
                                <td class="p-1">
                                  <button class="btn btn-xs btn-outline-primary shadow-xs px-2 py-1"
                                    :disabled="loadingDetail === (m.month + ' ' + row.label)"
                                    @click="fetchAndPrintMonthlyDetail(m.month + ' ' + row.label)">
                                    <i v-if="loadingDetail === (m.month + ' ' + row.label)" class="fa fa-spinner fa-spin"></i>
                                    <i v-else class="ti ti-printer fs-6"></i>
                                  </button>
                                </td>
                              </tr>
                            </tbody>
                            <tbody v-else>
                              <tr><td colspan="13" class="text-center text-muted">No monthly records</td></tr>
                            </tbody>
                          </table>
                        </div>
                      </td>
                    </tr>
                  </template>

                  <!-- Grand Total Row -->
                  <tr style="background:#f8fafc;" class="fw-bold border-top border-2">
                    <td class="text-dark font-monospace" style="font-size:0.88rem;">TOTAL</td>
                    <td class="text-success"><span class="total-num-val">{{ formatAmount(profitGrand.room_booking) }}</span></td>
                    <td class="text-success"><span class="total-num-val">{{ formatAmount(profitGrand.development_fee) }}</span></td>
                    <td class="text-success"><span class="total-num-val">{{ formatAmount(profitGrand.monthly_payment) }}</span></td>
                    <td class="text-success"><span class="total-num-val">{{ formatAmount(profitGrand.electricity_bill) }}</span></td>
                    <td class="text-success"><span class="total-num-val">{{ formatAmount(profitGrand.room_change_fee) }}</span></td>
                    <td style="background:#dcfce7; color: #14532d;"><span class="total-num-val" style="font-size: 0.94rem;">{{ formatAmount(profitGrand.total_income) }}</span></td>
                    <td class="text-danger"><span class="total-num-val">{{ formatAmount(profitGrand.expense) }}</span></td>
                    <td class="text-danger"><span class="total-num-val">{{ formatAmount(profitGrand.salary) }}</span></td>
                    <td class="text-danger"><span class="total-num-val">{{ formatAmount(profitGrand.advance_refund) }}</span></td>
                    <td style="background:#fee2e2; color: #7f1d1d;"><span class="total-num-val" style="font-size: 0.94rem;">{{ formatAmount(profitGrand.total_cost) }}</span></td>
                    <td class="p-1">
                      <span class="badge px-2 py-1 font-monospace fw-bold" style="font-size:0.84rem;"
                        :class="profitGrand.profit_loss >= 0 ? 'bg-success' : 'bg-danger'">
                        {{ profitGrand.profit_loss >= 0 ? '▲ ' : '▼ ' }}{{ formatAbsAmount(profitGrand.profit_loss) }}
                      </span>
                    </td>
                    <td style="background:#f8fafc;">-</td>
                  </tr>
                </tbody>

                <tbody v-else>
                  <tr>
                    <td colspan="13" class="text-center py-5 text-muted">
                      <span v-if="profitLoading"><i class="fa fa-spinner fa-spin me-2"></i>Loading report data...</span>
                      <span v-else-if="profitViewMode === 'daily' && !filterStartDate">
                        <i class="ti ti-calendar-search fs-2 d-block mb-2 text-primary"></i>
                        <span class="fw-semibold text-dark">দৈনিক রিপোর্ট দেখতে উপরে "From" এবং "To" তারিখ সিলেক্ট করে <span class="badge bg-primary px-2 py-1">Filter</span> বাটনে ক্লিক করুন।</span>
                      </span>
                      <span v-else>কোনো তথ্য পাওয়া যায়নি (No records found for this period)</span>
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
  name: "Report",

  data() {
    const currentYear = new Date().getFullYear() >= 2026 ? new Date().getFullYear() : 2026;
    const todayStr = new Date().toISOString().substring(0, 10);

    return {
      profitRows: [],
      profitLoading: false,
      profitViewMode: 'daily',
      availableYears: [currentYear],
      selectedYear: currentYear,
      selectedYearlyFilter: 'all',
      filterStartDate: todayStr,
      filterEndDate: todayStr,
      expandedYear: "",
      loadingDetail: null,
    };
  },

  computed: {
    url() {
      return this.$store.state.url;
    },
    profitGrand() {
      return this.profitRows.reduce(
        (acc, row) => {
          acc.room_booking     += Number(row.room_booking     || 0);
          acc.development_fee  += Number(row.development_fee  || 0);
          acc.monthly_payment  += Number(row.monthly_payment  || 0);
          acc.electricity_bill += Number(row.electricity_bill || 0);
          acc.room_change_fee  += Number(row.room_change_fee  || 0);
          acc.total_income     += Number(row.total_income     || 0);
          acc.expense          += Number(row.expense          || 0);
          acc.salary           += Number(row.salary           || 0);
          acc.advance_refund   += Number(row.advance_refund   || 0);
          acc.total_cost       += Number(row.total_cost       || 0);
          acc.profit_loss      += Number(row.profit_loss      || 0);
          return acc;
        },
        { room_booking: 0, development_fee: 0, monthly_payment: 0, electricity_bill: 0, room_change_fee: 0, total_income: 0,
          expense: 0, salary: 0, advance_refund: 0, total_cost: 0, profit_loss: 0 }
      );
    },
  },

  mounted() {
    this.loadAvailableYears();
    this.loadProfitReport();
  },

  methods: {
    async loadAvailableYears() {
      try {
        const res = await axios.get(`${this.url}get-available-years`);
        this.availableYears = res.data.years || [2026];
        this.selectedYear = this.availableYears[0];
      } catch (e) {
        this.availableYears = [2026];
      }
    },

    setProfitMode(mode) {
      this.profitViewMode = mode;
      if (mode === 'daily') {
        if (!this.filterStartDate) {
          const todayStr = new Date().toISOString().substring(0, 10);
          this.filterStartDate = todayStr;
          this.filterEndDate = todayStr;
        }
      }
      this.loadProfitReport();
    },

    clearDailyFilter() {
      const todayStr = new Date().toISOString().substring(0, 10);
      this.filterStartDate = todayStr;
      this.filterEndDate = todayStr;
      this.loadProfitReport();
    },

    clearFilter() {
      this.selectedYear = this.availableYears[0] || 2026;
      this.loadProfitReport();
    },

    clearYearlyFilter() {
      this.selectedYearlyFilter = 'all';
      this.loadProfitReport();
    },

    async loadProfitReport() {
      this.expandedYear = "";
      let params = { mode: this.profitViewMode };

      if (this.profitViewMode === 'daily') {
        if (!this.filterStartDate && !this.filterEndDate) {
          this.toast("অনুগ্রহ করে শুরুর তারিখ (From Date) নির্বাচন করুন", "warning");
          return;
        }
        if (this.filterStartDate && !this.filterEndDate) {
          this.filterEndDate = this.filterStartDate;
        } else if (!this.filterStartDate && this.filterEndDate) {
          this.filterStartDate = this.filterEndDate;
        }
        params.start_date = this.filterStartDate;
        params.end_date = this.filterEndDate;
      } else if (this.profitViewMode === 'monthly') {
        params.year = this.selectedYear;
      } else if (this.profitViewMode === 'yearly') {
        if (this.selectedYearlyFilter && this.selectedYearlyFilter !== 'all') {
          params.year = this.selectedYearlyFilter;
        }
      }

      this.profitLoading = true;
      try {
        const res = await axios.get(`${this.url}get-profit-loss-report`, { params });
        this.profitRows = res.data.data || [];
        if (this.profitViewMode === 'yearly' && this.selectedYearlyFilter && this.selectedYearlyFilter !== 'all') {
          this.expandedYear = String(this.selectedYearlyFilter);
        }
      } catch (e) {
        this.toast("Failed to load profit/loss report", "error");
      } finally {
        this.profitLoading = false;
      }
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

    // Detailed statement for Daily
    async fetchAndPrintDailyDetail(dateStr, label) {
      this.loadingDetail = dateStr;
      try {
        const res = await axios.get(`${this.url}get-daily-detail-report`, {
          params: { date: dateStr }
        });

        if (res.data && res.data.status) {
          this.printDetailedReportWindow(res.data, `Daily Itemized Statement - ${label}`);
        } else {
          this.toast("Failed to load daily details", "error");
        }
      } catch (e) {
        console.error(e);
        this.toast("Error loading details for " + label, "error");
      } finally {
        this.loadingDetail = null;
      }
    },

    // Detailed statement for Monthly
    async fetchAndPrintMonthlyDetail(rowLabel) {
      let year = this.selectedYear;
      let monthName = "";

      if (typeof rowLabel === 'string') {
        const parts = rowLabel.trim().split(' ');
        if (parts.length >= 2) {
          monthName = parts[0];
          year = parseInt(parts[1]) || this.selectedYear;
        } else {
          monthName = parts[0];
        }
      }

      const monthsMap = {
        'Jan': 1, 'Feb': 2, 'Mar': 3, 'Apr': 4,
        'May': 5, 'Jun': 6, 'Jul': 7, 'Aug': 8,
        'Sep': 9, 'Oct': 10, 'Nov': 11, 'Dec': 12
      };

      const monthNum = monthsMap[monthName] || 1;

      this.loadingDetail = rowLabel;
      try {
        const res = await axios.get(`${this.url}get-monthly-detail-report`, {
          params: { year: year, month: monthNum }
        });

        if (res.data && res.data.status) {
          this.printDetailedReportWindow(res.data, `Monthly Itemized Statement - ${rowLabel}`);
        } else {
          this.toast("Failed to load monthly details", "error");
        }
      } catch (e) {
        console.error(e);
        this.toast("Error loading details for " + rowLabel, "error");
      } finally {
        this.loadingDetail = null;
      }
    },

    printDetailedReportWindow(data, statementTitle) {
      const inc = data.income || {};
      const cost = data.cost || {};
      const netPL = data.profit_loss || 0;

      const formatVal = (v) => `${Number(v || 0).toFixed(2)} ৳`;

      const buildTable = (title, headers, rows, totalKey, totalLabel) => {
        let rowsHtml = '';
        if (rows && rows.length > 0) {
          rows.forEach((r, idx) => {
            rowsHtml += `<tr><td>${idx + 1}</td>`;
            headers.forEach(h => {
              let val = r[h.key] !== undefined ? r[h.key] : 'N/A';
              if (h.isAmount) val = formatVal(val);
              rowsHtml += `<td class="${h.align || 'text-center'}">${val}</td>`;
            });
            rowsHtml += `</tr>`;
          });
        } else {
          rowsHtml = `<tr><td colspan="${headers.length + 1}" class="empty-msg">No records found for this period</td></tr>`;
        }

        const totalVal = rows ? rows.reduce((s, item) => s + (parseFloat(item[totalKey]) || 0), 0) : 0;

        return `
          <div class="section-card mb-4">
            <div class="section-title-sub">${title}</div>
            <table>
              <thead>
                <tr>
                  <th style="width:40px;">SL</th>
                  ${headers.map(h => `<th>${h.label}</th>`).join('')}
                </tr>
              </thead>
              <tbody>
                ${rowsHtml}
              </tbody>
              <tfoot>
                <tr class="table-subtotal">
                  <td colspan="${headers.length}">Subtotal ${totalLabel}</td>
                  <td class="text-right fw-bold">${formatVal(totalVal)}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        `;
      };

      const roomBookingHtml = buildTable(
        '1. Advance Booking & Development Fees (অগ্রিম বুকিং ও উন্নয়ন ফি)',
        [
          { key: 'name', label: 'Name', align: 'text-left' },
          { key: 'phone', label: 'Phone' },
          { key: 'check_in', label: 'Check-In Date' },
          { key: 'advance_price', label: 'Advance Fee', isAmount: true, align: 'text-right' },
          { key: 'development_fee', label: 'Development Fee', isAmount: true, align: 'text-right' },
          { key: 'total_booking_fee', label: 'Total Amount', isAmount: true, align: 'text-right' }
        ],
        inc.room_bookings,
        'total_booking_fee',
        'Advance & Development Fees'
      );

      const monthlyPaymentHtml = buildTable(
        '2. Monthly Rent Receipts (মাসিক ভাড়া)',
        [
          { key: 'resident_name', label: 'Name', align: 'text-left' },
          { key: 'phone', label: 'Phone' },
          { key: 'payment_month', label: 'Payment Month' },
          { key: 'payment_method', label: 'Method' },
          { key: 'date', label: 'Date' },
          { key: 'paid_amount', label: 'Paid Amount', isAmount: true, align: 'text-right' }
        ],
        inc.monthly_payments,
        'paid_amount',
        'Monthly Rent'
      );

      const electricityBillHtml = buildTable(
        '3. Electricity Bills (বিদ্যুৎ বিল)',
        [
          { key: 'resident_name', label: 'Name', align: 'text-left' },
          { key: 'phone', label: 'Phone' },
          { key: 'payment_month', label: 'Payment Month' },
          { key: 'payment_method', label: 'Method' },
          { key: 'date', label: 'Date' },
          { key: 'bill_amount', label: 'Bill Amount', isAmount: true, align: 'text-right' }
        ],
        inc.electricity_bills,
        'bill_amount',
        'Electricity Bill'
      );

      const roomChangeHtml = buildTable(
        '4. Room Change Fees (রুম পরিবর্তন ফি)',
        [
          { key: 'resident_name', label: 'Resident Name', align: 'text-left' },
          { key: 'old_room', label: 'Old Room' },
          { key: 'new_room', label: 'New Room' },
          { key: 'date', label: 'Date' },
          { key: 'fee_amount', label: 'Fee Amount', isAmount: true, align: 'text-right' }
        ],
        inc.room_change_fees,
        'fee_amount',
        'Room Change Fee'
      );

      const expenseHtml = buildTable(
        '5. General Expenses (সাধারণ খরচ)',
        [
          { key: 'category', label: 'Category', align: 'text-left' },
          { key: 'note', label: 'Note / Description', align: 'text-left' },
          { key: 'date', label: 'Date' },
          { key: 'expense_amount', label: 'Amount', isAmount: true, align: 'text-right' }
        ],
        cost.general_expenses,
        'expense_amount',
        'General Expenses'
      );

      const salaryHtml = buildTable(
        '6. Staff Salary Payments (স্টাফদের বেতন)',
        [
          { key: 'staff_name', label: 'Name', align: 'text-left' },
          { key: 'designation', label: 'Designation' },
          { key: 'salary_month', label: 'Salary Month' },
          { key: 'payment_date', label: 'Date' },
          { key: 'amount', label: 'Salary Paid', isAmount: true, align: 'text-right' }
        ],
        cost.staff_salaries,
        'amount',
        'Staff Salary'
      );

      const refundHtml = buildTable(
        '7. Advance Refunds (এডভান্স অফার রিফান্ড)',
        [
          { key: 'name', label: 'Name', align: 'text-left' },
          { key: 'phone', label: 'Phone' },
          { key: 'checkout_date', label: 'Checkout Date' },
          { key: 'refund_amount', label: 'Refund Amount', isAmount: true, align: 'text-right' }
        ],
        cost.advance_refunds,
        'refund_amount',
        'Advance Refund'
      );

      const html = `<!DOCTYPE html>
<html>
<head>
  <title>${statementTitle || 'Profit Loss Statement'}</title>
  <style>
    @page { size: A4 portrait; margin: 10mm; }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 11px; color: #1e293b; background: #fff; line-height: 1.4; padding: 10px; }
    .header-box { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 10px; margin-bottom: 15px; }
    .header-box h1 { font-size: 20px; font-weight: 800; color: #0f172a; letter-spacing: 0.5px; margin-bottom: 2px; }
    .header-box h3 { font-size: 14px; font-weight: 600; color: #475569; margin-bottom: 4px; }
    .header-box p { font-size: 11px; color: #64748b; }
    
    .section-banner-income { background: #15803d; color: #fff; padding: 6px 12px; font-size: 13px; font-weight: 700; border-radius: 4px; margin-top: 15px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
    .section-banner-cost { background: #b91c1c; color: #fff; padding: 6px 12px; font-size: 13px; font-weight: 700; border-radius: 4px; margin-top: 20px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
    
    .section-card { margin-bottom: 12px; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; page-break-inside: avoid; }
    .section-title-sub { background: #f8fafc; font-weight: 700; font-size: 11px; padding: 6px 10px; border-bottom: 1px solid #e2e8f0; color: #334155; }
    
    table { width: 100%; border-collapse: collapse; font-size: 10px; }
    th, td { border: 1px solid #cbd5e1; padding: 4px 6px; }
    thead th { background: #f1f5f9; color: #334155; font-weight: 700; text-align: center; }
    .text-center { text-align: center; }
    .text-left { text-align: left; }
    .text-right { text-align: right; }
    .fw-bold { font-weight: 700; }
    .empty-msg { font-style: italic; color: #94a3b8; padding: 8px; text-align: center; }
    
    .table-subtotal { background: #f8fafc; font-weight: 700; }
    
    .summary-card { background: #0f172a; color: #fff; padding: 14px 18px; border-radius: 8px; margin-top: 25px; page-break-inside: avoid; }
    .summary-grid { display: flex; justify-content: space-between; align-items: center; font-size: 12px; }
    .summary-item { text-align: center; }
    .summary-item .label { font-size: 11px; color: #94a3b8; margin-bottom: 3px; }
    .summary-item .val { font-size: 15px; font-weight: 700; }
    .profit-badge { background: #22c55e; color: #fff; padding: 4px 12px; border-radius: 4px; font-size: 14px; font-weight: 800; }
    .loss-badge { background: #ef4444; color: #fff; padding: 4px 12px; border-radius: 4px; font-size: 14px; font-weight: 800; }
    
    .footer-signatures { display: flex; justify-content: space-between; margin-top: 40px; page-break-inside: avoid; }
    .sig-box { width: 180px; text-align: center; border-top: 1px dashed #64748b; padding-top: 5px; font-weight: 600; font-size: 10px; color: #475569; }

    @media print {
      body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
  </style>
</head>
<body>
  <div class="header-box">
    <h1>টি এস এস ভিলা (TSS Villa)</h1>
    <h3>${statementTitle || 'Profit & Loss Detailed Statement'}</h3>
    <p>Statement Period: <strong>${data.period}</strong> &nbsp;|&nbsp; Printed On: ${new Date().toLocaleString()}</p>
  </div>

  <!-- INCOME SECTION -->
  <div class="section-banner-income">
    <span>🟢 (A) আয়ের হিসাব (INCOME DETAILS)</span>
    <span>Total Income: ${formatVal(inc.total_income)}</span>
  </div>
  ${roomBookingHtml}
  ${monthlyPaymentHtml}
  ${electricityBillHtml}
  ${roomChangeHtml}

  <!-- EXPENSE SECTION -->
  <div class="section-banner-cost">
    <span>🔴 (B) ব্যয়ের হিসাব (EXPENSE / COST DETAILS)</span>
    <span>Total Cost: ${formatVal(cost.total_cost)}</span>
  </div>
  ${expenseHtml}
  ${salaryHtml}
  ${refundHtml}

  <!-- SUMMARY BOX -->
  <div class="summary-card">
    <div class="summary-grid">
      <div class="summary-item">
        <div class="label">Total Income</div>
        <div class="val" style="color: #4ade80;">${formatVal(inc.total_income)}</div>
      </div>
      <div class="summary-item">
        <div class="label">Total Cost</div>
        <div class="val" style="color: #f87171;">${formatVal(cost.total_cost)}</div>
      </div>
      <div class="summary-item">
        <div class="label">Net Result</div>
        <div class="${netPL >= 0 ? 'profit-badge' : 'loss-badge'}">
          ${netPL >= 0 ? '▲ Profit: ' : '▼ Loss: '} ${formatVal(Math.abs(netPL))}
        </div>
      </div>
    </div>
  </div>

  <div class="footer-signatures">
    <div class="sig-box">Prepared By (প্রস্তুতকারী)</div>
    <div class="sig-box">Audited By (যাচাইকারী)</div>
    <div class="sig-box">Authorized Signature (কর্তৃপক্ষের স্বাক্ষর)</div>
  </div>

  <script>
    window.onload = function() {
      window.print();
      window.onafterprint = function() { window.close(); };
    };
  <\/script>
</body>
</html>`;

      const win = window.open("", "_blank");
      if (win) {
        win.document.write(html);
        win.document.close();
      } else {
        alert("Pop-up window print blocked by browser. Please allow pop-ups for this site.");
      }
    },

    printTable() {
      const printContents = document.getElementById("printArea").innerHTML;
      const modeTitle = this.profitViewMode === 'daily' 
        ? `Daily Report (${this.filterStartDate} to ${this.filterEndDate})`
        : (this.profitViewMode === 'monthly' ? `Monthly Report (${this.selectedYear})` : 'Yearly Report (All Years)');

      const html = `<!DOCTYPE html>
<html>
<head>
  <title>Profit Loss Report - ${modeTitle}</title>
  <style>
    @page { size: A4 landscape; margin: 10mm; }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: Arial, sans-serif; font-size: 10px; color: #333; padding: 10px; }
    .print-header { text-align: center; margin-bottom: 12px; }
    .print-header h2 { font-size: 18px; font-weight: bold; margin-bottom: 3px; color: #033364; }
    .print-header p { font-size: 11px; color: #666; }
    table { width: 100%; border-collapse: collapse; margin-top: 8px; }
    th, td { border: 1px solid #ccc; padding: 4px 5px; text-align: center; }
    thead th { font-weight: bold; font-size: 10px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .text-success { color: #16a34a !important; font-weight: 600; }
    .text-danger  { color: #dc2626 !important; font-weight: 600; }
    .fw-bold { font-weight: bold; }
    .fw-semibold { font-weight: 600; }
    .detail-box-profit { display: none !important; }
    .badge { display: inline-block !important; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .bg-success { background: #22c55e !important; }
    .bg-danger  { background: #ef4444 !important; }
  </style>
</head>
<body>
  <div class="print-header">
    <h2>টি এস এস ভিলা (TSS Villa)</h2>
    <h4>Profit / Loss Statement (${modeTitle})</h4>
    <p>Printed: ${new Date().toLocaleString()}</p>
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
      if (win) {
        win.document.write(html);
        win.document.close();
      } else {
        alert("Pop-up window print blocked by browser. Please allow pop-ups for this site.");
      }
    },

    toast(text, type = "success") {
      Toastify({
        text,
        duration: 3000,
        gravity: "top",
        position: "right",
      }).showToast();
    },

    exportProfitReportCSV() {
      if (!this.profitRows || this.profitRows.length === 0) {
        this.toast("এক্সপোর্ট করার মতো কোনো ডাটা পাওয়া যায়নি", "warning");
        return;
      }
      const labelCol = this.profitViewMode === 'daily' ? 'Date' : (this.profitViewMode === 'monthly' ? 'Month' : 'Year');
      const headers = [
        labelCol,
        "Advance Booking Fee",
        "Development Fee",
        "Monthly Rent",
        "Electricity Bill",
        "Room Change Fee",
        "Total Income",
        "General Expense",
        "Staff Salary",
        "Advance Refund",
        "Total Cost",
        "Profit / Loss"
      ];
      const rows = this.profitRows.map(r => [
        r.label,
        r.room_booking || 0,
        r.development_fee || 0,
        r.monthly_payment || 0,
        r.electricity_bill || 0,
        r.room_change_fee || 0,
        r.total_income || 0,
        r.expense || 0,
        r.salary || 0,
        r.advance_refund || 0,
        r.total_cost || 0,
        r.profit_loss || 0
      ]);

      // Add Grand Total Row
      rows.push([
        "GRAND TOTAL",
        this.profitGrand.room_booking,
        this.profitGrand.development_fee,
        this.profitGrand.monthly_payment,
        this.profitGrand.electricity_bill,
        this.profitGrand.room_change_fee,
        this.profitGrand.total_income,
        this.profitGrand.expense,
        this.profitGrand.salary,
        this.profitGrand.advance_refund,
        this.profitGrand.total_cost,
        this.profitGrand.profit_loss
      ]);

      const filename = `Profit_Loss_Report_${this.profitViewMode}_${new Date().toISOString().substring(0,10)}.csv`;
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
  },
};
</script>

<style scoped>
.form-select, .form-control {
  border-radius: 6px;
  border: 1px solid #dce0e4;
}
.form-select:focus, .form-control:focus {
  border-color: #033364;
  box-shadow: 0 0 0 .15rem rgba(3, 51, 100, 0.15);
}
.statement-table {
  width: 100% !important;
  table-layout: fixed;
  border-collapse: collapse;
}
.statement-table th,
.statement-table td {
  vertical-align: middle;
  padding: 6px 3px !important;
  font-size: 0.82rem;
  line-height: 1.25;
}
.statement-table thead th {
  white-space: normal;
  word-wrap: break-word;
}
.sub-headers th {
  font-size: 0.76rem !important;
  font-weight: 700;
  padding: 5px 2px !important;
  line-height: 1.2;
}
.num-val {
  white-space: nowrap;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  font-size: 0.82rem;
  font-weight: 700;
  letter-spacing: -0.2px;
}
.total-num-val {
  white-space: nowrap;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  font-size: 0.88rem;
  font-weight: 800;
  letter-spacing: -0.2px;
}
.clickable-cell {
  cursor: pointer;
}
.clickable-cell:hover {
  background: #f8fafc;
}
.detail-box-profit {
  background: #f8fafc;
  padding: 10px;
  border-left: 4px solid #033364;
}
.detail-title {
  font-weight: 700;
  font-size: 0.90rem;
  text-align: left;
  margin-bottom: 6px;
}
.btn-xs {
  padding: 0.25rem 0.45rem;
  font-size: 0.78rem;
  border-radius: 0.25rem;
}
.bg-label-info {
  background-color: #e0f2fe !important;
  color: #0369a1 !important;
}
.fs-7 {
  font-size: 0.82rem;
}
</style>