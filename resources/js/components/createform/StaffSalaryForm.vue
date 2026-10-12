<template>
  <Teleport to="body">
    <transition name="slide-fade">
      <div v-if="show" class="xmask" @click.self="emitClose">
        <div class="xwrap">
          <div class="xbox shadow-lg" role="dialog" aria-modal="true">

            <!-- Header -->
            <div class="xhead d-flex justify-content-between align-items-center px-4 py-3 border-bottom">
              <div class="d-flex align-items-center gap-2">
                <div class="head-icon">
                  <i class="fa fa-user-clock"></i>
                </div>
                <div>
                  <h5 class="mb-0 fw-bold text-dark">Staff Salary</h5>
                </div>
              </div>
              <button type="button" class="btn-close-custom" @click="emitClose">
                <i class="fa fa-times"></i>
              </button>
            </div>

            <div class="xbody custom-scrollbar p-4">
              <div class="row g-4">

                <!-- Staff Search -->
                <div class="col-12">
                  <div class="section-card">
                    <label class="form-label fw-semibold">Select Staff <span class="req">*</span></label>

                    <div class="d-flex align-items-start gap-2">
                      <div class="medicine-search-container flex-grow-1 position-relative">
                        <input
                          ref="staffInput"
                          type="search"
                          v-model="staffSearch"
                          @input="searchStaff"
                          @focus="onInputFocus"
                          @blur="onInputBlur"
                          class="form-control"
                          placeholder="Search by name / employee id / phone"
                          autocomplete="off"
                        />

                        <!-- Dropdown teleported to body to escape overflow clipping -->
                        <Teleport to="body">
                          <div
                            v-if="showDropdown && searchResults.length"
                            class="staff-dropdown-teleport"
                            :style="dropdownStyle"
                          >
                            <div
                              v-for="staff in searchResults"
                              :key="staff.id"
                              class="staff-dropdown-item"
                              @mousedown.prevent="selectStaff(staff)"
                            >
                              <div class="staff-avatar">{{ firstChar(staff.name) }}</div>
                              <div class="flex-grow-1">
                                <div class="fw-semibold">{{ staff.name }}</div>
                                <small class="text-muted">
                                  {{ staff.employee_id || 'N/A' }} · {{ staff.phone || 'N/A' }}
                                </small>
                              </div>
                            </div>
                          </div>
                        </Teleport>
                      </div>

                      <button
                        type="button"
                        class="btn btn-success btn-sm px-3 h-100"
                        @click="addStaff"
                        :disabled="!selectedStaff"
                      >
                        <i class="fa fa-plus me-1"></i> Add
                      </button>
                    </div>

                    <!-- Selected Staff Summary -->
                    <div v-if="addedStaff" class="selected-staff-card mt-3">
                      <div class="staff-avatar lg">{{ firstChar(addedStaff.name) }}</div>
                      <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                          <div>
                            <div class="fw-bold text-primary fs-6">{{ addedStaff.name }}</div>
                            <span class="badge bg-success-subtle text-success">Selected</span>
                          </div>
                          
                          <!-- Salary Month Selector -->
                          <div class="d-flex align-items-center gap-2">
                            <label class="form-label mb-0 fw-bold small text-muted text-nowrap">
                              <i class="fa fa-calendar-alt me-1 text-primary"></i>বেতনের মাস:
                            </label>
                            <input
                              type="month"
                              class="form-control form-control-sm fw-bold border-primary"
                              style="width: 170px;"
                              v-model="selectedSalaryMonth"
                              @change="onSalaryMonthChange"
                            />
                            <span
                              class="badge px-3 py-2 fs-7 fw-bold"
                              :class="salarySummary.is_paid ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'"
                            >
                              {{ currentMonthLabel }}: {{ salarySummary.is_paid ? 'Paid (পরিশোধিত)' : 'Unpaid (বকেয়া)' }}
                            </span>
                          </div>
                        </div>

                        <div class="row mt-3 small gy-2">
                          <div class="col-md-3">
                            <strong>Employee ID:</strong> {{ addedStaff.employee_id || 'N/A' }}
                          </div>
                          <div class="col-md-3">
                            <strong>Department:</strong> {{ addedStaff.department || 'N/A' }}
                          </div>
                          <div class="col-md-3">
                            <strong>Designation:</strong> {{ addedStaff.designation || 'N/A' }}
                          </div>
                          <div class="col-md-3">
                            <strong>Joining Date:</strong> {{ addedStaff.joining_date || 'N/A' }}
                          </div>

                          <div class="col-md-3 text-dark">
                            <strong>Monthly Salary:</strong> {{ formatAmount(salarySummary.monthly_salary) }}
                          </div>
                          <div class="col-md-3 text-danger">
                            <strong>Total Advance Balance (মোট বাকি অগ্রিম):</strong>
                            <span class="badge bg-danger-subtle text-danger fs-7 ms-1">
                              {{ formatAmount(salarySummary.total_advance_balance) }}
                            </span>
                          </div>
                          <div class="col-md-3 text-info">
                            <strong>Advance Count:</strong> {{ salarySummary.advance_count }} Times
                          </div>
                          <div class="col-md-3 text-success">
                            <strong>{{ salarySummary.is_paid ? 'Status:' : 'Net Payable Now:' }}</strong> 
                            <span :class="salarySummary.is_paid ? 'badge bg-success-subtle text-success ms-1' : 'fw-bold text-success'">
                              {{ salarySummary.is_paid ? 'Paid (পরিশোধিত)' : formatAmount(salarySummary.net_payable) }}
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <template v-if="addedStaff">

                  <!-- Mode Selection Tabs (when selected month is NOT yet paid) -->
                  <div v-if="!salarySummary.is_paid" class="col-12">
                    <div class="d-flex gap-2 p-1.5 bg-light rounded-3 border">
                      <button
                        type="button"
                        class="btn flex-fill py-2 fw-bold d-flex align-items-center justify-content-center gap-2 transition"
                        :class="activeMode === 'full' ? 'btn-primary shadow-sm' : 'btn-light text-muted'"
                        @click="activeMode = 'full'"
                      >
                        <i class="fa fa-wallet"></i>
                        <span>{{ currentMonthLabel }} বেতন পরিশোধ (Pay Salary)</span>
                      </button>
                      <button
                        type="button"
                        class="btn flex-fill py-2 fw-bold d-flex align-items-center justify-content-center gap-2 transition"
                        :class="activeMode === 'advance' ? 'btn-warning shadow-sm text-dark' : 'btn-light text-muted'"
                        @click="activeMode = 'advance'"
                      >
                        <i class="fa fa-money-bill-wave"></i>
                        <span>অগ্রিম প্রদান (Pay Advance)</span>
                      </button>
                    </div>
                  </div>

                  <!-- ADVANCE SALARY CARD -->
                  <div v-if="salarySummary.is_paid || activeMode === 'advance'" class="col-12">
                    <div class="section-card dt-card border-success" style="background: #fcfdfd;">
                      <div class="dt-label text-success d-flex justify-content-between align-items-center">
                        <div>
                          <i class="fa fa-money-bill-wave me-1"></i> Advance Salary Payment (অগ্রিম প্রদান)
                          <small class="d-block text-muted mt-1">
                            এই স্টাফের জন্য যেকোনো পরিমাণ অগ্রিম প্রদান করুন (এটি স্টাফের মোট অগ্রিম ব্যালেন্সে যুক্ত হবে)।
                          </small>
                        </div>
                        <span class="badge bg-success-subtle text-success">Advance Mode</span>
                      </div>

                      <div class="row g-3">
                        <div class="col-md-6">
                          <label class="form-label fw-semibold">Payment Date (টাকা দেওয়ার তারিখ) <span class="req">*</span></label>
                          <input
                            type="date"
                            class="form-control"
                            v-model="advanceForm.payment_date"
                          />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label fw-semibold">Advance Amount (অগ্রিমের টাকা) <span class="req">*</span></label>
                          <input
                            type="number"
                            step="0.01"
                            min="1"
                            class="form-control fw-bold border-success"
                            v-model="advanceForm.amount"
                            placeholder="অগ্রিমের পরিমাণ লিখুন (যেমন: ৫০০০ বা ৯০০০)"
                          />
                        </div>
                        <div class="col-12">
                          <label class="form-label fw-semibold">Note / মন্তব্য</label>
                          <textarea
                            class="form-control"
                            rows="2"
                            v-model="advanceForm.note"
                            placeholder="অগ্রিমের কারণ বা মন্তব্য (ঐচ্ছিক)"
                          ></textarea>
                        </div>
                      </div>

                      <div class="salary-mini-info my-3 p-3 bg-light rounded-3 border">
                        <div class="mini-line d-flex justify-content-between mb-1">
                          <span class="text-muted">Current Advance Balance (বর্তমানে অপরিশোধিত অগ্রিম):</span>
                          <strong class="text-danger">{{ formatAmount(salarySummary.total_advance_balance) }}</strong>
                        </div>
                        <div class="mini-line d-flex justify-content-between">
                          <span class="text-muted">Total Advance After this Entry (নতুন অগ্রিম যোগ হওয়ার পর):</span>
                          <strong class="text-warning">
                            {{ formatAmount(parseFloat(salarySummary.total_advance_balance || 0) + parseFloat(advanceForm.amount || 0)) }}
                          </strong>
                        </div>
                      </div>

                      <button
                        type="button"
                        class="btn btn-success w-100 fw-bold py-2.5 shadow-sm fs-6"
                        @click="saveAdvance"
                        :disabled="savingAdvance"
                      >
                        <span v-if="savingAdvance" class="spinner-border spinner-border-sm me-2"></span>
                        <i v-else class="fa fa-check me-1"></i>
                        {{ savingAdvance ? 'Saving...' : `Save Advance (${formatAmount(advanceForm.amount || 0)})` }}
                      </button>
                    </div>
                  </div>

                  <!-- FULL / NET SALARY CARD -->
                  <div v-else-if="!salarySummary.is_paid && activeMode === 'full'" class="col-12">
                    <div class="section-card dt-card border-primary" style="background: #fcfdff;">
                      <div class="dt-label text-primary d-flex justify-content-between align-items-center">
                        <div>
                          <i class="fa fa-wallet me-1"></i> Salary Payment (বেতন পরিশোধ)
                          <small class="d-block text-muted mt-1">For <strong>{{ currentMonthLabel }}</strong></small>
                        </div>
                        <span class="badge bg-primary-subtle text-primary">Salary Mode</span>
                      </div>

                      <div class="row g-3">
                        <div class="col-md-6">
                          <label class="form-label fw-semibold">Payment Date (পরিশোধের তারিখ) <span class="req">*</span></label>
                          <input
                            type="date"
                            class="form-control"
                            v-model="fullForm.payment_date"
                          />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label fw-semibold">Monthly Salary (মূল বেতন)</label>
                          <input
                            type="text"
                            class="form-control fw-bold"
                            :value="formatAmount(salarySummary.monthly_salary)"
                            readonly
                          />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label fw-semibold text-danger">
                            <i class="fa fa-exclamation-circle me-1"></i>মোট বকেয়া অগ্রিম (Total Advance Balance)
                          </label>
                          <input
                            type="text"
                            class="form-control text-danger fw-bold bg-danger-subtle"
                            :value="formatAmount(salarySummary.total_advance_balance)"
                            readonly
                          />
                        </div>
                        <div class="col-md-6">
                          <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-bold text-primary mb-0">
                              এই মাসে অগ্রিম কর্তন (Deduct Advance) <span class="req">*</span>
                            </label>
                            <small class="text-muted">Max: {{ formatAmount(maxDeductible) }}</small>
                          </div>
                          <input
                            type="number"
                            step="0.01"
                            min="0"
                            :max="maxDeductible"
                            class="form-control fw-bold border-primary shadow-sm"
                            v-model="fullForm.advance_deduct_amount"
                            placeholder="অগ্রিম কর্তনের পরিমাণ লিখুন"
                          />
                        </div>

                        <!-- Live Calculation Cards -->
                        <div class="col-md-6">
                          <label class="form-label fw-bold text-success">
                            <i class="fa fa-hand-holding-usd me-1"></i>হাতে প্রদেয় নিট বেতন (Net Payable to Staff)
                          </label>
                          <input
                            type="text"
                            class="form-control fw-bold text-success fs-5 bg-success-subtle border-success"
                            :value="formatAmount(calculatedNetPayable)"
                            readonly
                          />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label fw-bold text-warning">
                            <i class="fa fa-hourglass-half me-1"></i>পরবর্তী মাসের জন্য বাকি অগ্রিম (Remaining Advance)
                          </label>
                          <input
                            type="text"
                            class="form-control fw-bold text-warning fs-5 bg-warning-subtle border-warning"
                            :value="formatAmount(calculatedRemainingAdvance)"
                            readonly
                          />
                        </div>

                        <div class="col-12">
                          <label class="form-label fw-semibold">Note / মন্তব্য</label>
                          <textarea
                            class="form-control"
                            rows="2"
                            v-model="fullForm.note"
                            placeholder="বেতন সংক্রান্ত কোনো মন্তব্য থাকলে লিখুন (ঐচ্ছিক)"
                          ></textarea>
                        </div>
                      </div>

                      <button
                        type="button"
                        class="btn btn-primary w-100 mt-3 fw-bold py-2.5 shadow-sm fs-6"
                        @click="saveFull"
                        :disabled="savingFull"
                      >
                        <span v-if="savingFull" class="spinner-border spinner-border-sm me-2"></span>
                        <i v-else class="fa fa-check me-1"></i>
                        {{ savingFull ? 'Paying...' : `Pay Salary for ${currentMonthLabel} (${formatAmount(calculatedNetPayable)})` }}
                      </button>
                    </div>
                  </div>

                  <!-- History -->
                  <div class="col-12">
                    <div class="section-card">
                      <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="fw-bold"><i class="fa fa-history me-1 text-primary"></i> Salary Payment History</div>
                        <small class="text-muted">
                          Selected Month: <strong>{{ currentMonthLabel }}</strong>
                        </small>
                      </div>

                      <div class="table-responsive" v-if="history.length">
                        <table class="table table-sm align-middle mb-0">
                          <thead class="table-light">
                            <tr>
                              <th>Date</th>
                              <th>Salary Month</th>
                              <th>Payment Type</th>
                              <th>Amount & Adjustment (টাকা ও অগ্রিম হিসাব)</th>
                              <th>Note</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="item in history" :key="item.id">
                              <td class="text-nowrap text-muted">{{ item.payment_date }}</td>
                              <td class="text-nowrap"><strong>{{ formatMonthYear(item.salary_month, item.salary_year) }}</strong></td>
                              <td>
                                <span
                                  class="badge"
                                  :class="item.payment_type === 'advance'
                                    ? 'bg-warning-subtle text-warning'
                                    : item.payment_type === 'net_payable'
                                      ? 'bg-primary-subtle text-primary'
                                      : 'bg-success-subtle text-success'">
                                  <i class="fa me-1" :class="item.payment_type === 'advance' ? 'fa-money-bill-wave' : 'fa-wallet'"></i>
                                  {{ item.payment_type === 'advance' ? 'Advance (অগ্রিম)' : (item.payment_type === 'net_payable' ? 'Net Salary (বেতন)' : 'Full Salary') }}
                                </span>
                              </td>
                              <td>
                                <!-- Advance Item -->
                                <template v-if="item.payment_type === 'advance'">
                                  <div v-if="item.advance_adjusted > 0">
                                    <span class="fw-bold text-danger fs-6">{{ formatAmount(item.remaining_amount) }}</span>
                                    <span class="badge bg-danger-subtle text-danger ms-1" style="font-size: 10px;">বকেয়া বাকি</span>
                                    <div class="text-muted" style="font-size: 11.5px;">
                                      মূল অগ্রিম: {{ formatAmount(item.original_amount) }}
                                      <span class="text-danger fw-semibold">(-{{ formatAmount(item.advance_adjusted) }} সমন্বয় হয়েছে)</span>
                                    </div>
                                  </div>
                                  <div v-else>
                                    <span class="fw-bold text-dark fs-6">{{ formatAmount(item.amount) }}</span>
                                    <span class="badge bg-warning-subtle text-dark ms-1" style="font-size: 10px;">সম্পূর্ণ বাকি</span>
                                  </div>
                                </template>

                                <!-- Salary Item -->
                                <template v-else>
                                  <div>
                                    <span class="fw-bold text-success fs-6">{{ formatAmount(item.amount) }}</span>
                                    <span class="badge bg-success-subtle text-success ms-1" style="font-size: 10px;">হাতে প্রাপ্ত নিট বেতন</span>
                                  </div>
                                  <div v-if="item.advance_deducted > 0" class="text-muted" style="font-size: 11.5px;">
                                    মূল বেতন: {{ formatAmount(item.gross_salary) }}
                                    <span class="text-danger fw-semibold">(-{{ formatAmount(item.advance_deducted) }} অগ্রিম কর্তন)</span>
                                  </div>
                                </template>
                              </td>
                              <td>{{ item.note || '-' }}</td>
                            </tr>
                          </tbody>
                        </table>
                      </div>

                      <div v-else class="text-muted small">
                        No salary history found for this staff.
                      </div>
                    </div>
                  </div>
                </template>
              </div>
            </div>

            <!-- Footer -->
            <div class="xfoot px-4 py-3 border-top d-flex justify-content-between align-items-center">
              <small class="text-muted"><span class="req">*</span> Required fields</small>
              <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary px-4" @click="emitClose">
                  Cancel
                </button>
              </div>
            </div>

          </div>
        </div>
      </div>
    </transition>
  </Teleport>
</template>


<script>
import axios from "axios";
import Toastify from "toastify-js";
import "toastify-js/src/toastify.css";

export default {
  name: "StaffSalaryModal",

  props: {
    show: { type: Boolean, default: false },
  },

  emits: ["close", "created"],

  computed: {
    url() {
      return this.$store.state.url;
    },
    currentMonthLabel() {
      if (this.selectedSalaryMonth) {
        const [year, month] = this.selectedSalaryMonth.split("-");
        const d = new Date(parseInt(year), parseInt(month) - 1, 1);
        return d.toLocaleString("en-US", { month: "long", year: "numeric" });
      }
      const d = new Date();
      return d.toLocaleString("en-US", { month: "long", year: "numeric" });
    },
    maxDeductible() {
      const salary = parseFloat(this.salarySummary?.monthly_salary || 0);
      const totalAdv = parseFloat(this.salarySummary?.total_advance_balance || 0);
      return Math.min(salary, totalAdv);
    },
    calculatedNetPayable() {
      const salary = parseFloat(this.salarySummary?.monthly_salary || 0);
      const deduct = parseFloat(this.fullForm?.advance_deduct_amount || 0);
      return Math.max(0, salary - Math.min(salary, Math.max(0, deduct)));
    },
    calculatedRemainingAdvance() {
      const totalAdv = parseFloat(this.salarySummary?.total_advance_balance || 0);
      const deduct = parseFloat(this.fullForm?.advance_deduct_amount || 0);
      return Math.max(0, totalAdv - Math.min(totalAdv, Math.max(0, deduct)));
    },
  },

  data() {
    const today = new Date().toISOString().slice(0, 10);
    const thisMonth = new Date().toISOString().slice(0, 7);

    return {
      savingAdvance: false,
      savingFull: false,
      activeMode: "full", // 'full' or 'advance'
      selectedSalaryMonth: thisMonth,

      staffSearch: "",
      searchResults: [],
      showDropdown: false,
      selectedStaff: null,
      addedStaff: null,

      salarySummary: {
        salary_month: null,
        salary_year: null,
        monthly_salary: 0,
        total_advance_balance: 0,
        advance_count: 0,
        net_payable: 0,
        is_paid: false,
        default_deduct_amount: 0,
      },

      advanceForm: {
        staff_id: null,
        payment_date: today,
        amount: "",
        note: "",
      },

      fullForm: {
        staff_id: null,
        payment_date: today,
        advance_deduct_amount: 0,
        note: "",
      },

      history: [],
      dropdownStyle: {},
    };
  },

  watch: {
    show(v) {
      document.body.style.overflow = v ? "hidden" : "";
      if (v) this.resetForm();
    },
  },

  beforeUnmount() {
    document.body.style.overflow = "";
  },

  methods: {
    emitClose() {
      this.$emit("close");
    },

    firstChar(name) {
      return name ? name.charAt(0).toUpperCase() : "S";
    },

    formatAmount(value) {
      const number = parseFloat(value || 0);
      return `${number.toFixed(2)} ৳`;
    },

    formatMonthYear(month, year) {
      if (!month || !year) return "-";
      const d = new Date(year, month - 1);
      return d.toLocaleString("en-US", { month: "long", year: "numeric" });
    },

    resetForm() {
      const today = new Date().toISOString().slice(0, 10);
      const thisMonth = new Date().toISOString().slice(0, 7);

      this.activeMode = "full";
      this.selectedSalaryMonth = thisMonth;
      this.staffSearch = "";
      this.searchResults = [];
      this.showDropdown = false;
      this.selectedStaff = null;
      this.addedStaff = null;
      this.history = [];

      this.salarySummary = {
        salary_month: null,
        salary_year: null,
        monthly_salary: 0,
        total_advance_balance: 0,
        advance_count: 0,
        net_payable: 0,
        is_paid: false,
        default_deduct_amount: 0,
      };

      this.advanceForm = {
        staff_id: null,
        payment_date: today,
        amount: "",
        note: "",
      };

      this.fullForm = {
        staff_id: null,
        payment_date: today,
        advance_deduct_amount: 0,
        note: "",
      };
    },

    onSalaryMonthChange() {
      if (this.addedStaff) {
        this.loadSummary();
      }
    },

    onInputFocus() {
      this.updateDropdownPosition();
      if (this.searchResults.length) this.showDropdown = true;
    },

    onInputBlur() {
      setTimeout(() => { this.showDropdown = false; }, 150);
    },

    updateDropdownPosition() {
      this.$nextTick(() => {
        const input = this.$refs.staffInput;
        if (!input) return;
        const rect = input.getBoundingClientRect();
        this.dropdownStyle = {
          position: 'fixed',
          top: `${rect.bottom + 6}px`,
          left: `${rect.left}px`,
          width: `${rect.width}px`,
          zIndex: 99999,
        };
      });
    },

    async searchStaff() {
      const q = this.staffSearch.trim();

      if (!q) {
        this.searchResults = [];
        this.showDropdown = false;
        this.selectedStaff = null;
        return;
      }

      try {
        const res = await axios.get(`${this.url}search-staff`, {
          params: { query: q },
        });

        this.searchResults = res.data;
        this.updateDropdownPosition();
        this.showDropdown = true;
      } catch (e) {
        console.error("Search staff error:", e);
      }
    },

    selectStaff(staff) {
      this.selectedStaff = staff;
      this.staffSearch = staff.name;
      this.showDropdown = false;
    },

    async addStaff() {
      if (!this.selectedStaff) return;

      this.addedStaff = this.selectedStaff;
      this.advanceForm.staff_id = this.selectedStaff.id;
      this.fullForm.staff_id = this.selectedStaff.id;

      await this.loadSummary();
      await this.loadHistory();
    },

    async loadSummary() {
      if (!this.addedStaff) return;

      const [yearStr, monthStr] = (this.selectedSalaryMonth || '').split('-');
      const year = yearStr ? parseInt(yearStr) : new Date().getFullYear();
      const month = monthStr ? parseInt(monthStr) : (new Date().getMonth() + 1);

      try {
        const res = await axios.get(`${this.url}summary/${this.addedStaff.id}`, {
          params: { month, year }
        });

        this.salarySummary = res.data || {
          salary_month: month,
          salary_year: year,
          monthly_salary: 0,
          total_advance_balance: 0,
          advance_count: 0,
          net_payable: 0,
          is_paid: false,
          default_deduct_amount: 0,
        };

        if (!this.salarySummary.is_paid) {
          this.fullForm.advance_deduct_amount = this.salarySummary.default_deduct_amount || 0;
        }

        if (this.salarySummary.is_paid) {
          this.activeMode = "advance";
        }
      } catch (error) {
        console.error(error);
        this.toast("Failed to load salary summary", "error");
      }
    },

    async loadHistory() {
      if (!this.addedStaff) return;

      try {
        const res = await axios.get(`${this.url}history/${this.addedStaff.id}`);
        this.history = res.data;
      } catch (error) {
        console.error(error);
      }
    },

    async saveAdvance() {
      if (!this.advanceForm.staff_id) {
        this.toast("Please select staff first", "error");
        return;
      }

      if (!this.advanceForm.payment_date) {
        this.toast("Advance date is required", "error");
        return;
      }

      if (!this.advanceForm.amount || Number(this.advanceForm.amount) <= 0) {
        this.toast("Advance amount is required", "error");
        return;
      }

      const [yearStr, monthStr] = (this.selectedSalaryMonth || '').split('-');
      const year = yearStr ? parseInt(yearStr) : new Date().getFullYear();
      const month = monthStr ? parseInt(monthStr) : (new Date().getMonth() + 1);

      try {
        this.savingAdvance = true;

        const payload = {
          ...this.advanceForm,
          salary_month: month,
          salary_year: year,
        };

        const res = await axios.post(`${this.url}advance`, payload);

        this.toast(res.data.message || "Advance salary saved successfully");
        this.advanceForm.amount = "";
        this.advanceForm.note = "";

        await this.loadSummary();
        await this.loadHistory();

        this.$emit("created");
      } catch (error) {
        this.toast(error?.response?.data?.message || "Failed to save advance", "error");
      } finally {
        this.savingAdvance = false;
      }
    },

    async saveFull() {
      if (!this.fullForm.staff_id) {
        this.toast("Please select staff first", "error");
        return;
      }

      if (!this.fullForm.payment_date) {
        this.toast("Salary date is required", "error");
        return;
      }

      const [yearStr, monthStr] = (this.selectedSalaryMonth || '').split('-');
      const year = yearStr ? parseInt(yearStr) : new Date().getFullYear();
      const month = monthStr ? parseInt(monthStr) : (new Date().getMonth() + 1);

      try {
        this.savingFull = true;

        const payload = {
          ...this.fullForm,
          salary_month: month,
          salary_year: year,
          advance_deduct_amount: parseFloat(this.fullForm.advance_deduct_amount || 0),
        };

        const res = await axios.post(`${this.url}full`, payload);

        this.toast(res.data.message || "Salary paid successfully");
        this.fullForm.note = "";

        await this.loadSummary();
        await this.loadHistory();

        this.$emit("created");
      } catch (error) {
        this.toast(error?.response?.data?.message || "Failed to pay salary", "error");
      } finally {
        this.savingFull = false;
      }
    },

    toast(text, type = "success") {
      Toastify({
        text,
        duration: 3000,
        gravity: "top",
        position: "right",
        style: {
          background:
            type === "success"
              ? "linear-gradient(to right, #22c55e, #16a34a)"
              : "linear-gradient(to right, #ef4444, #dc2626)",
        },
      }).showToast();
    },
  },
};
</script>

<style scoped>
.xmask {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.45);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.xwrap {
  width: 100%;
  max-width: 800px;
}

.xbox {
  background: #fff;
  border-radius: 18px;
  overflow: hidden;
}

.xbody {
  max-height: 75vh;
  overflow-y: auto;
}

.section-card {
  border: 1px solid #eef2f7;
  border-radius: 16px;
  padding: 18px;
  background: #fff;
}

.dt-card {
  min-height: 100%;
}

.req {
  color: #dc2626;
}

.head-icon {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  background: #ecfdf5;
  color: #16a34a;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-close-custom {
  border: 0;
  background: #f8fafc;
  width: 38px;
  height: 38px;
  border-radius: 10px;
}

.staff-dropdown-teleport {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  background: #fff;
  max-height: 260px;
  overflow-y: auto;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
}

.staff-dropdown-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  cursor: pointer;
}

.staff-dropdown-item:hover {
  background: #f8fafc;
}

.staff-avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: #dbeafe;
  color: #1d4ed8;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
}

.staff-avatar.lg {
  width: 46px;
  height: 46px;
}

.selected-staff-card {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  padding: 16px;
  border-radius: 14px;
  background: #f8fafc;
  border: 1px solid #e5e7eb;
}

.dt-label {
  font-weight: 700;
  margin-bottom: 14px;
}

.salary-mini-info {
  border: 1px dashed #dbeafe;
  background: #f8fafc;
  border-radius: 12px;
  padding: 12px;
}

.mini-line {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  font-size: 14px;
}

.mini-line + .mini-line {
  margin-top: 8px;
}

.btn-outline-secondary,
.btn-primary,
.btn-success {
  border-radius: 10px;
}

.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.2s ease;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(8px);
}
</style>