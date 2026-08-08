<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
      <div class="col-12">
        <div class="card mb-4 shadow-sm">

          <!-- Header -->
          <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3">
            <h5 class="card-title mb-0">Profit / Loss Report </h5>
            <div class="d-flex gap-2">
              <button class="btn btn-success" type="button" @click="exportProfitReportCSV">
                <i class="fa fa-file-excel me-1"></i> Export Excel
              </button>
              <button class="btn btn-primary" type="button" @click="printTable">
                <i class="ti ti-printer me-1"></i> Print
              </button>
            </div>
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
                    <th colspan="5" style="background:#16a34a; color:#fff;">আয় (Income)</th>
                    <!-- Cost -->
                    <th colspan="5" style="background:#dc2626; color:#fff;">ব্যয় (Expense)</th>
                    <!-- Result -->
                    <th rowspan="2" style="vertical-align:middle; background:#1d4ed8; color:#fff;">Profit / Loss</th>
                    <!-- Action -->
                    <th rowspan="2" style="vertical-align:middle; background:#475569; color:#fff;">Action</th>
                  </tr>
                  <tr>
                    <th style="background:#dcfce7; color:#166534;">advance booking fee</th>
                    <th style="background:#dcfce7; color:#166534;">Monthly Rent</th>
                    <th style="background:#dcfce7; color:#166534;">Product Sales</th>
                    <th style="background:#dcfce7; color:#166534;">Room Change Fee</th>
                    <th style="background:#bbf7d0; color:#166534; font-weight:700;">Total Income</th>
                    <th style="background:#fee2e2; color:#991b1b;">General Expense</th>
                    <th style="background:#fee2e2; color:#991b1b;">Staff Salary</th>
                    <th style="background:#fee2e2; color:#991b1b;">Product Purchase</th>
                    <th style="background:#fee2e2; color:#991b1b;">Advance Refund</th>
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
                      <td class="text-success fw-semibold">{{ formatAmount(row.room_change_fee) }}</td>
                      <td class="fw-bold" style="background:#f0fdf4;">{{ formatAmount(row.total_income) }}</td>
                      <!-- Cost -->
                      <td class="text-danger fw-semibold">{{ formatAmount(row.expense) }}</td>
                      <td class="text-danger fw-semibold">{{ formatAmount(row.salary) }}</td>
                      <td class="text-danger fw-semibold">{{ formatAmount(row.product_purchase) }}</td>
                      <td class="text-danger fw-semibold">{{ formatAmount(row.advance_refund) }}</td>
                      <td class="fw-bold" style="background:#fff1f2;">{{ formatAmount(row.total_cost) }}</td>
                      <!-- Profit/Loss -->
                      <td class="fw-bold">
                        <span class="badge px-3 py-2" style="font-size:0.82rem;"
                          :class="row.profit_loss >= 0 ? 'bg-success' : 'bg-danger'">
                          {{ row.profit_loss >= 0 ? '▲ Profit' : '▼ Loss' }}
                          {{ formatAbsAmount(row.profit_loss) }}
                        </span>
                      </td>
                      <!-- Action -->
                      <td class="text-center" @click.stop>
                        <button v-if="profitViewMode === 'monthly'"
                          class="btn btn-xs btn-outline-primary shadow-sm px-2 py-1"
                          :disabled="loadingDetail === row.label"
                          @click="fetchAndPrintDetail(row.label)">
                          <i v-if="loadingDetail === row.label" class="fa fa-spinner fa-spin me-1"></i>
                          <i v-else class="ti ti-printer me-1"></i> Details Print
                        </button>
                        <span v-else class="text-muted" style="font-size: 0.8rem;">Expand for Monthly</span>
                      </td>
                    </tr>

                    <!-- Yearly monthly breakdown -->
                    <tr v-if="profitViewMode === 'yearly' && expandedYear === row.label" :key="row.label + '-expand'">
                      <td colspan="13" class="p-0">
                        <div class="detail-box-profit">
                          <div class="detail-title">📅 {{ row.label }} - Monthly Breakdown</div>
                          <table class="table table-sm table-bordered mb-0 text-center">
                            <thead style="background:#fff8e1;">
                              <tr>
                                <th>Month</th>
                                <th style="color:#16a34a;">advance booking fee </th>
                                <th style="color:#16a34a;">Monthly Rent</th>
                                <th style="color:#16a34a;">Product Sales</th>
                                <th style="color:#16a34a;">Room Change Fee</th>
                                <th style="color:#166534; font-weight:700;">Total Income</th>
                                <th style="color:#dc2626;">Expense</th>
                                <th style="color:#dc2626;">Salary</th>
                                <th style="color:#dc2626;">Product Purchase</th>
                                <th style="color:#dc2626;">Advance Refund</th>
                                <th style="color:#991b1b; font-weight:700;">Total Cost</th>
                                <th>Profit / Loss</th>
                                <th>Action</th>
                              </tr>
                            </thead>
                            <tbody v-if="row.monthly_breakdown && row.monthly_breakdown.length">
                              <tr v-for="m in row.monthly_breakdown" :key="m.month">
                                <td class="fw-bold">{{ m.month }} {{ row.label }}</td>
                                <td class="text-success">{{ formatAmount(m.room_booking) }}</td>
                                <td class="text-success">{{ formatAmount(m.monthly_payment) }}</td>
                                <td class="text-success">{{ formatAmount(m.product_sales) }}</td>
                                <td class="text-success">{{ formatAmount(m.room_change_fee) }}</td>
                                <td class="fw-bold" style="background:#f0fdf4;">{{ formatAmount(m.total_income) }}</td>
                                <td class="text-danger">{{ formatAmount(m.expense) }}</td>
                                <td class="text-danger">{{ formatAmount(m.salary) }}</td>
                                <td class="text-danger">{{ formatAmount(m.product_purchase) }}</td>
                                <td class="text-danger">{{ formatAmount(m.advance_refund) }}</td>
                                <td class="fw-bold" style="background:#fff1f2;">{{ formatAmount(m.total_cost) }}</td>
                                <td>
                                  <span class="badge px-2 py-1"
                                    :class="m.profit_loss >= 0 ? 'bg-success' : 'bg-danger'">
                                    {{ m.profit_loss >= 0 ? '▲' : '▼' }}
                                    {{ formatAbsAmount(m.profit_loss) }}
                                  </span>
                                </td>
                                <td>
                                  <button class="btn btn-xs btn-outline-primary shadow-sm px-2 py-1"
                                    :disabled="loadingDetail === (m.month + ' ' + row.label)"
                                    @click="fetchAndPrintDetail(m.month + ' ' + row.label)">
                                    <i v-if="loadingDetail === (m.month + ' ' + row.label)" class="fa fa-spinner fa-spin me-1"></i>
                                    <i v-else class="ti ti-printer me-1"></i> Details Print
                                  </button>
                                </td>
                              </tr>
                            </tbody>
                            <tbody v-else>
                              <tr><td colspan="13" class="text-center text-muted">No data</td></tr>
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
                    <td class="text-success">{{ formatAmount(profitGrand.room_change_fee) }}</td>
                    <td style="background:#f0fdf4;">{{ formatAmount(profitGrand.total_income) }}</td>
                    <td class="text-danger">{{ formatAmount(profitGrand.expense) }}</td>
                    <td class="text-danger">{{ formatAmount(profitGrand.salary) }}</td>
                    <td class="text-danger">{{ formatAmount(profitGrand.product_purchase) }}</td>
                    <td class="text-danger">{{ formatAmount(profitGrand.advance_refund) }}</td>
                    <td style="background:#fff1f2;">{{ formatAmount(profitGrand.total_cost) }}</td>
                    <td>
                      <span class="badge px-3 py-2" style="font-size:0.82rem;"
                        :class="profitGrand.profit_loss >= 0 ? 'bg-success' : 'bg-danger'">
                        {{ profitGrand.profit_loss >= 0 ? '▲ Profit' : '▼ Loss' }}
                        {{ formatAbsAmount(profitGrand.profit_loss) }}
                      </span>
                    </td>
                    <td style="background:#fff8e1;">-</td>
                  </tr>
                </tbody>

                <tbody v-else>
                  <tr>
                    <td colspan="13" class="text-center py-5 text-muted">
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
  name: "Report",

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
          acc.room_change_fee  += row.room_change_fee  || 0;
          acc.total_income     += row.total_income     || 0;
          acc.expense          += row.expense          || 0;
          acc.salary           += row.salary           || 0;
          acc.product_purchase += row.product_purchase || 0;
          acc.advance_refund   += row.advance_refund   || 0;
          acc.total_cost       += row.total_cost       || 0;
          acc.profit_loss      += row.profit_loss      || 0;
          return acc;
        },
        { room_booking: 0, monthly_payment: 0, product_sales: 0, room_change_fee: 0, total_income: 0,
          expense: 0, salary: 0, product_purchase: 0, advance_refund: 0, total_cost: 0, profit_loss: 0 }
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
      stockLoading: false,
      loadingDetail: null,
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

    async fetchAndPrintDetail(rowLabel) {
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
          this.printMonthlyDetailedReportWindow(res.data);
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

    printMonthlyDetailedReportWindow(data) {
      const inc = data.income || {};
      const cost = data.cost || {};
      const netPL = data.profit_loss || 0;

      const formatVal = (v) => `${Number(v || 0).toFixed(2)} ৳`;

      // Helper table generator
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

      // 1. Room Booking Advance Table
      const roomBookingHtml = buildTable(
        '1. Advance Booking Fees (অগ্রিম বুকিং ফি)',
        [
          { key: 'name', label: 'Name', align: 'text-left' },
          { key: 'phone', label: 'Phone' },
          { key: 'check_in', label: 'Check-In Date' },
          { key: 'advance_price', label: 'Advance Amount', isAmount: true, align: 'text-right' }
        ],
        inc.room_bookings,
        'advance_price',
        'Advance Booking'
      );

      // 2. Monthly Rent Table
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

      // 3. Product Sales Table
      const productSalesHtml = buildTable(
        '3. Product Sales (পণ্য বিক্রি)',
        [
          { key: 'memo_number', label: 'Memo No' },
          { key: 'customer_name', label: 'Name', align: 'text-left' },
          { key: 'product_name', label: 'Product Name', align: 'text-left' },
          { key: 'quantity', label: 'Qty' },
          { key: 'date', label: 'Date' },
          { key: 'amount', label: 'Sales Amount', isAmount: true, align: 'text-right' }
        ],
        inc.product_sales,
        'amount',
        'Product Sales'
      );

      // 4. Room Change Fee Table
      const roomChangeHtml = buildTable(
        '4. Room Change Fees (রুম পরিবর্তন ফি)',
        [
          { key: 'resident_name', label: 'Name', align: 'text-left' },
          { key: 'phone', label: 'Phone' },
          { key: 'old_room', label: 'Old Room / Seat', align: 'text-left' },
          { key: 'new_room', label: 'New Room / Seat', align: 'text-left' },
          { key: 'date', label: 'Date' },
          { key: 'fee_amount', label: 'Fee Amount', isAmount: true, align: 'text-right' }
        ],
        inc.room_change_fees,
        'fee_amount',
        'Room Change Fee'
      );

      // 5. General Expenses Table
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

      // 6. Staff Salary Table
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

      // 7. Product Purchases Table
      const purchaseHtml = buildTable(
        '7. Product Purchases (পণ্য ক্রয়)',
        [
          { key: 'memo_number', label: 'Memo No' },
          { key: 'supplier_name', label: 'Supplier Name', align: 'text-left' },
          { key: 'product_name', label: 'Product Name', align: 'text-left' },
          { key: 'quantity', label: 'Qty' },
          { key: 'date', label: 'Date' },
          { key: 'total_price', label: 'Purchase Amount', isAmount: true, align: 'text-right' }
        ],
        cost.product_purchases,
        'total_price',
        'Product Purchases'
      );

      // 8. Advance Refund Table
      const refundHtml = buildTable(
        '8. Advance Refunds (এডভান্স অফার রিফান্ড)',
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
  <title>Monthly Itemized Profit Loss Statement - ${data.period}</title>
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
    <h1>TSS Villa - Sohashova Chhatri Nibas</h1>
    <h3>Monthly Itemized Profit & Loss Statement</h3>
    <p>Statement Period: <strong>${data.period}</strong> &nbsp;|&nbsp; Printed On: ${new Date().toLocaleString()}</p>
  </div>

  <!-- INCOME SECTION -->
  <div class="section-banner-income">
    <span>🟢 (A) আয়ের হিসাব (INCOME DETAILS)</span>
    <span>Total Income: ${formatVal(inc.total_income)}</span>
  </div>
  ${roomBookingHtml}
  ${monthlyPaymentHtml}
  ${productSalesHtml}
  ${roomChangeHtml}

  <!-- EXPENSE SECTION -->
  <div class="section-banner-cost">
    <span>🔴 (B) ব্যয়ের হিসাব (EXPENSE / COST DETAILS)</span>
    <span>Total Cost: ${formatVal(cost.total_cost)}</span>
  </div>
  ${expenseHtml}
  ${salaryHtml}
  ${purchaseHtml}
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
      }).showToast();
    },

    exportProfitReportCSV() {
      if (!this.profitRows || this.profitRows.length === 0) {
        this.toast("এক্সপোর্ট করার মতো কোনো ডাটা পাওয়া যায়নি", "warning");
        return;
      }
      const headers = [
        this.profitViewMode === 'monthly' ? 'Month' : 'Year',
        "Advance Booking Fee",
        "Monthly Rent",
        "Product Sales",
        "Room Change Fee",
        "Total Income",
        "General Expense",
        "Staff Salary",
        "Product Purchase",
        "Advance Refund",
        "Total Cost",
        "Profit / Loss"
      ];
      const rows = this.profitRows.map(r => [
        r.label,
        r.booking_fee || 0,
        r.monthly_rent || 0,
        r.product_sales || 0,
        r.room_change_fee || 0,
        r.total_income || 0,
        r.general_expense || 0,
        r.staff_salary || 0,
        r.product_purchase || 0,
        r.advance_refund || 0,
        r.total_cost || 0,
        r.net_profit || 0
      ]);
      const filename = `Profit_Loss_Report_${this.profitViewMode}_${this.selectedYear || 'All'}.csv`;
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
.btn-xs {
  padding: 0.25rem 0.5rem;
  font-size: 0.75rem;
  border-radius: 0.2rem;
}
</style>