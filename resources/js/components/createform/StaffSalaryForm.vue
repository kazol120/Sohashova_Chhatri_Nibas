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
                        <div class="d-flex justify-content-between flex-wrap gap-2">
                          <div>
                            <div class="fw-bold text-primary fs-6">{{ addedStaff.name }}</div>
                            <span class="badge bg-success-subtle text-success">Selected</span>
                          </div>
                          <div>
                            <span
                              class="badge"
                              :class="fullSummary.full_paid ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'"
                            >
                              {{ fullSummary.full_paid ? 'Paid' : 'Unpaid' }}
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
                            <strong>Monthly Salary:</strong> {{ formatAmount(advanceSummary.monthly_salary) }}
                          </div>
                          <div class="col-md-3 text-warning">
                            <strong>Total Advance:</strong>
                            {{ fullSummary.full_paid ? formatAmount(advanceSummary.total_advance) : formatAmount(fullSummary.total_advance) }}
                          </div>
                          <div class="col-md-3 text-info">
                            <strong>Advance Count:</strong> {{ advanceSummary.advance_count }} / 3
                          </div>
                          <div class="col-md-3 text-success">
                            <strong>Remaining Salary:</strong> {{ formatAmount(advanceSummary.remaining_salary) }}
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <template v-if="addedStaff">

                  <!--
                    LOGIC:
                    - fullSummary.full_paid = true  → Full salary already paid this month
                        → Show ADVANCE section only (locked out from full payment)
                    - fullSummary.full_paid = false → Full salary NOT yet paid
                        → Show FULL SALARY section only (advance is locked until full paid)
                  -->

                  <!-- ADVANCE SALARY — shown only when full salary IS already paid -->
                  <div v-if="fullSummary.full_paid" class="col-12">
                    <div class="section-card dt-card">
                      <div class="dt-label text-success">
                        <i class="fa fa-money-bill me-1"></i> Advance Salary Payment
                        <small class="d-block text-muted mt-1">For {{ currentMonthLabel }}</small>
                      </div>

                      <div class="row g-3">
                        <div class="col-md-6">
                          <label class="form-label">Date <span class="req">*</span></label>
                          <input
                            type="date"
                            class="form-control"
                            v-model="advanceForm.payment_date"
                          />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label">Amount <span class="req">*</span></label>
                          <input
                            type="number"
                            step="0.01"
                            min="0"
                            class="form-control"
                            v-model="advanceForm.amount"
                            placeholder="Enter advance amount"
                          />
                        </div>
                        <div class="col-12">
                          <label class="form-label">Note</label>
                          <textarea
                            class="form-control"
                            rows="2"
                            v-model="advanceForm.note"
                            placeholder="Optional note"
                          ></textarea>
                        </div>
                      </div>

                      <div class="salary-mini-info my-3">
                        <div class="mini-line">
                          <span>Advance Count</span>
                          <strong>{{ advanceSummary.advance_count }} / 3</strong>
                        </div>
                        <div class="mini-line">
                          <span>Remaining Salary</span>
                          <strong>{{ formatAmount(advanceSummary.remaining_salary) }}</strong>
                        </div>
                      </div>

                      <button
                        type="button"
                        class="btn btn-success w-100"
                        @click="saveAdvance"
                        :disabled="savingAdvance || advanceSummary.advance_count >= 3"
                      >
                        <span v-if="savingAdvance" class="spinner-border spinner-border-sm me-2"></span>
                        {{ savingAdvance ? 'Saving...' : 'Save Advance' }}
                      </button>
                    </div>
                  </div>

                  <!-- FULL SALARY — shown only when full salary is NOT yet paid -->
                  <div v-else class="col-12">
                    <div class="section-card dt-card">
                      <div class="dt-label text-primary">
                        <i class="fa fa-wallet me-1"></i> Full Salary Payment
                        <small class="d-block text-muted mt-1">For {{ previousMonthLabel }}</small>
                      </div>

                      <div class="row g-3">
                        <div class="col-md-6">
                          <label class="form-label">Date <span class="req">*</span></label>
                          <input
                            type="date"
                            class="form-control"
                            v-model="fullForm.payment_date"
                          />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label">Monthly Salary</label>
                          <input
                            type="text"
                            class="form-control"
                            :value="formatAmount(fullSummary.monthly_salary)"
                            readonly
                          />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label">Total Advance</label>
                          <input
                            type="text"
                            class="form-control"
                            :value="formatAmount(fullSummary.total_advance)"
                            readonly
                          />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label">Net Payable Salary</label>
                          <input
                            type="text"
                            class="form-control fw-bold"
                            :value="formatAmount(fullSummary.net_payable)"
                            readonly
                          />
                        </div>
                        <div class="col-12">
                          <label class="form-label">Note</label>
                          <textarea
                            class="form-control"
                            rows="2"
                            v-model="fullForm.note"
                            placeholder="Optional note"
                          ></textarea>
                        </div>
                      </div>

                      <button
                        type="button"
                        class="btn btn-primary w-100 mt-3"
                        @click="saveFull"
                        :disabled="savingFull"
                      >
                        <span v-if="savingFull" class="spinner-border spinner-border-sm me-2"></span>
                        {{ savingFull ? 'Paying...' : 'Pay Full Salary' }}
                      </button>
                    </div>
                  </div>

                  <!-- History -->
                  <div class="col-12">
                    <div class="section-card">
                      <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="fw-bold">Salary Payment History</div>
                        <small class="text-muted">
                          Advance: {{ currentMonthLabel }} | Full: {{ previousMonthLabel }}
                        </small>
                      </div>

                      <div class="table-responsive" v-if="history.length">
                        <table class="table table-sm align-middle mb-0">
                          <thead>
                            <tr>
                              <th>Date</th>
                              <th>Salary For</th>
                              <th>Type</th>
                              <th>Amount</th>
                              <th>Note</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="item in history" :key="item.id">
                              <td>{{ item.payment_date }}</td>
                              <td>{{ formatMonthYear(item.salary_month, item.salary_year) }}</td>
                              <td>
                                <span
                                  class="badge"
                                  :class="item.payment_type === 'advance'
                                    ? 'bg-warning-subtle text-warning'
                                    : 'bg-success-subtle text-success'">
                                  {{ item.payment_type }}
                                </span>
                              </td>
                              <td>{{ formatAmount(item.amount) }}</td>
                              <td>{{ item.note || '-' }}</td>
                            </tr>
                          </tbody>
                        </table>
                      </div>

                      <div v-else class="text-muted small">
                        No salary history found for this period.
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
      const d = new Date();
      return d.toLocaleString("en-US", { month: "long", year: "numeric" });
    },
    previousMonthLabel() {
      const d = new Date();
      d.setMonth(d.getMonth() - 1);
      return d.toLocaleString("en-US", { month: "long", year: "numeric" });
    },
  },

  data() {
    return {
      savingAdvance: false,
      savingFull: false,

      staffSearch: "",
      searchResults: [],
      showDropdown: false,
      selectedStaff: null,
      addedStaff: null,

      advanceSummary: {
        monthly_salary: 0,
        total_advance: 0,
        advance_count: 0,
        remaining_salary: 0,
        salary_month: null,
        salary_year: null,
      },

      fullSummary: {
        monthly_salary: 0,
        total_advance: 0,
        net_payable: 0,
        full_paid: false,
        salary_month: null,
        salary_year: null,
      },

      advanceForm: {
        staff_id: null,
        payment_date: "",
        amount: "",
        note: "",
      },

      fullForm: {
        staff_id: null,
        payment_date: "",
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

      this.staffSearch = "";
      this.searchResults = [];
      this.showDropdown = false;
      this.selectedStaff = null;
      this.addedStaff = null;
      this.history = [];

      this.advanceSummary = {
        monthly_salary: 0,
        total_advance: 0,
        advance_count: 0,
        remaining_salary: 0,
        salary_month: null,
        salary_year: null,
      };

      this.fullSummary = {
        monthly_salary: 0,
        total_advance: 0,
        net_payable: 0,
        full_paid: false,
        salary_month: null,
        salary_year: null,
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
        note: "",
      };
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

      try {
        const res = await axios.get(`${this.url}summary/${this.addedStaff.id}`);

        this.advanceSummary = res.data.advance_summary || {
          monthly_salary: 0,
          total_advance: 0,
          advance_count: 0,
          remaining_salary: 0,
          salary_month: null,
          salary_year: null,
        };

        this.fullSummary = res.data.full_summary || {
          monthly_salary: 0,
          total_advance: 0,
          net_payable: 0,
          full_paid: false,
          salary_month: null,
          salary_year: null,
        };
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

      try {
        this.savingAdvance = true;

        const res = await axios.post(`${this.url}advance`, this.advanceForm);

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
        this.toast("Full salary date is required", "error");
        return;
      }

      try {
        this.savingFull = true;

        const res = await axios.post(`${this.url}full`, this.fullForm);

        this.toast(res.data.message || "Full salary paid successfully");
        this.fullForm.note = "";

        await this.loadSummary();
        await this.loadHistory();

        this.$emit("created");
      } catch (error) {
        this.toast(error?.response?.data?.message || "Failed to pay full salary", "error");
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