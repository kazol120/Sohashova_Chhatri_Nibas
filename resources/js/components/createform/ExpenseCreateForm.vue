<template>
  <Teleport to="body">
    <transition name="modal-fade">
      <div v-if="show" class="backdrop" @click.self="emitClose">
        <div class="modal-wrap">
          <div class="modal-box" role="dialog" aria-modal="true">

            <!-- Modal Header -->
            <div class="modal-header">
              <div class="header-left">
                <div class="header-icon">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="5" width="20" height="14" rx="3"/>
                    <path d="M2 10h20"/>
                  </svg>
                </div>
                <div>
                  <h5 class="modal-title">Add Expense</h5>
                  <span class="modal-subtitle">Select category to enter amount</span>
                </div>
              </div>
              <button class="close-btn" @click="emitClose" aria-label="Close">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
              </button>
            </div>

            <form @submit.prevent="submit">
              <div class="modal-body custom-scrollbar">

                <!-- Date Field -->
                <div class="field-group mb-3">
                  <label class="field-label">Date <span class="req-star">*</span></label>
                  <div class="input-wrapper">
                    <span class="input-icon">
                      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                        <path d="M16 2v4M8 2v4M3 10h18"/>
                      </svg>
                    </span>
                    <input
                      type="date"
                      v-model="form.date"
                      class="field-input"
                      :class="{ 'is-error': errors.date }"
                    />
                  </div>
                  <span v-if="errors.date" class="error-msg">{{ errors.date }}</span>
                </div>

                <!-- Select Category Dropdown -->
                <div class="field-group mb-3">
                  <label class="field-label">Select Expense Category <span class="req-star">*</span></label>
                  <div class="input-wrapper">
                    <span class="input-icon">
                      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 6h16M4 12h8M4 18h12"/>
                      </svg>
                    </span>
                    <select
                      v-model="selectedCategoryInput"
                      class="field-input field-select"
                      @change="handleCategoryChoose"
                    >
                      <option value="" disabled selected>-- Choose Category to Add Amount --</option>
                      <option
                        v-for="cat in availableCategories"
                        :key="cat.id"
                        :value="cat.id"
                      >
                        + {{ cat.name }}
                      </option>
                    </select>
                  </div>
                  <small class="text-muted text-hint">
                    Select a category above (e.g. Electricity Bill, Water Bill) to open its amount field.
                  </small>
                </div>

                <!-- Added Categories & Amount Sections -->
                <div v-if="form.items.length > 0" class="added-sections-wrap mb-3">
                  <label class="field-label mb-2">Selected Categories & Amounts:</label>
                  
                  <div
                    v-for="(item, index) in form.items"
                    :key="item.expense_category"
                    class="category-amount-card"
                  >
                    <div class="card-top-bar">
                      <div class="cat-badge">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                          <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                          <line x1="7" y1="7" x2="7.01" y2="7"/>
                        </svg>
                        <strong>{{ getCategoryName(item.expense_category) }}</strong>
                      </div>
                      <button
                        type="button"
                        class="btn-remove-cat"
                        @click="removeCategory(index)"
                        title="Remove Category"
                      >
                        ✕ Remove
                      </button>
                    </div>

                    <div class="card-inputs-grid">
                      <!-- Amount Field -->
                      <div class="field-group">
                        <label class="field-sublabel">Expense Amount (৳) <span class="req-star">*</span></label>
                        <div class="input-wrapper">
                          <span class="input-icon currency-sign">৳</span>
                          <input
                            type="number"
                            v-model.number="item.expense_amount"
                            class="field-input amount-input"
                            :class="{ 'is-error': getItemError(index, 'expense_amount') }"
                            placeholder="Enter amount..."
                            min="0"
                            step="0.01"
                          />
                        </div>
                        <span v-if="getItemError(index, 'expense_amount')" class="error-msg">
                          {{ getItemError(index, 'expense_amount') }}
                        </span>
                      </div>

                      <!-- Note Field (Optional) -->
                      <div class="field-group">
                        <label class="field-sublabel">Note <span class="optional-tag">(Optional)</span></label>
                        <input
                          type="text"
                          v-model="item.expense_note"
                          class="field-input"
                          placeholder="Note (optional)"
                        />
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Empty State Prompt -->
                <div v-else class="empty-prompt-card mb-3">
                  <div class="empty-icon">💡</div>
                  <div class="empty-text">Please select an expense category from the dropdown above to enter amount.</div>
                </div>

                <!-- Summary Table & Grand Total -->
                <div v-if="form.items.length > 0" class="summary-table-box mb-2">
                  <div class="summary-title">Summary</div>
                  <table class="table table-sm table-borderless mb-0 summary-table">
                    <thead>
                      <tr>
                        <th>Category</th>
                        <th>Note</th>
                        <th class="text-end">Amount</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="item in form.items" :key="'sum-'+item.expense_category">
                        <td><span class="fw-semibold text-dark">{{ getCategoryName(item.expense_category) }}</span></td>
                        <td><span class="text-muted small">{{ item.expense_note || '—' }}</span></td>
                        <td class="text-end fw-bold">৳ {{ (parseFloat(item.expense_amount) || 0).toFixed(2) }}</td>
                      </tr>
                    </tbody>
                    <tfoot>
                      <tr class="border-top">
                        <th colspan="2" class="text-end text-success">Total Amount:</th>
                        <th class="text-end text-success fs-6">৳ {{ grandTotal.toFixed(2) }}</th>
                      </tr>
                    </tfoot>
                  </table>
                </div>

              </div>

              <!-- Modal Footer -->
              <div class="modal-footer">
                <span class="req-note"><span class="req-star">*</span> Amount required for selected categories</span>
                <div class="footer-actions">
                  <button type="button" class="btn-cancel" @click="emitClose">Cancel</button>
                  <button type="submit" class="btn-submit" :disabled="saving || form.items.length === 0">
                    <span v-if="saving" class="spinner"></span>
                    <svg v-else width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                      <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                      <polyline points="17 21 17 13 7 13 7 21"/>
                      <polyline points="7 3 7 8 15 8"/>
                    </svg>
                    {{ saving ? 'Saving...' : 'Save Expense' }}
                  </button>
                </div>
              </div>
            </form>

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
  name: "ExpenseCreateForm",

  props: {
    show: { type: Boolean, default: false },
  },

  emits: ["close", "created"],

  data() {
    return {
      saving: false,
      errors: {},
      categories: [],
      selectedCategoryInput: "",
      form: {
        date: new Date().toISOString().split("T")[0],
        items: [],
      },
    };
  },

  computed: {
    url() {
      return this.$store.state.url;
    },

    availableCategories() {
      const selectedIds = new Set(this.form.items.map(i => String(i.expense_category)));
      return this.categories.filter(c => !selectedIds.has(String(c.id)));
    },

    grandTotal() {
      return this.form.items.reduce((sum, i) => {
        const val = parseFloat(i.expense_amount);
        return sum + (isNaN(val) ? 0 : val);
      }, 0);
    },
  },

  watch: {
    show(val) {
      document.body.style.overflow = val ? "hidden" : "";
      if (val) {
        this.resetForm();
        this.getCategories();
      }
    },
  },

  beforeUnmount() {
    document.body.style.overflow = "";
  },

  methods: {
    emitClose() {
      this.$emit("close");
    },

    resetForm() {
      this.errors = {};
      this.selectedCategoryInput = "";
      this.form = {
        date: new Date().toISOString().split("T")[0],
        items: [],
      };
    },

    handleCategoryChoose() {
      if (!this.selectedCategoryInput) return;

      const catId = this.selectedCategoryInput;
      // Add row for this category if not already added
      const exists = this.form.items.some(i => String(i.expense_category) === String(catId));
      if (!exists) {
        this.form.items.push({
          expense_category: catId,
          expense_note: "",
          expense_amount: "",
        });
      }

      // Reset dropdown input
      this.selectedCategoryInput = "";
    },

    removeCategory(index) {
      this.form.items.splice(index, 1);
    },

    getCategoryName(id) {
      const found = this.categories.find(c => String(c.id) === String(id));
      return found ? found.name : "Category";
    },

    getItemError(index, field) {
      return this.errors[`items.${index}.${field}`] || "";
    },

    async getCategories() {
      try {
        const base = this.url.endsWith("/") ? this.url : `${this.url}/`;
        const res = await axios.get(`${base}expense-categories-list`);
        if (res.data.status === "success") {
          this.categories = res.data.data || [];
        }
      } catch (error) {
        this.toast("Failed to load categories.", "error");
      }
    },

    validate() {
      this.errors = {};
      if (!this.form.date) {
        this.errors.date = "Date is required.";
      }

      if (!this.form.items || this.form.items.length === 0) {
        this.toast("Select at least one category above.", "warning");
        return false;
      }

      let valid = true;
      this.form.items.forEach((item, index) => {
        if (!item.expense_amount || Number(item.expense_amount) <= 0) {
          this.errors[`items.${index}.expense_amount`] = "Enter valid amount.";
          valid = false;
        }
      });

      return valid && Object.keys(this.errors).length === 0;
    },

    async submit() {
      if (!this.validate()) return;

      this.saving = true;
      try {
        const base = this.url.endsWith("/") ? this.url : `${this.url}/`;
        const payload = {
          date: this.form.date,
          expenses: this.form.items.map(item => ({
            expense_category: item.expense_category,
            expense_note: item.expense_note || null,
            expense_amount: parseFloat(item.expense_amount),
          })),
        };

        const res = await axios.post(`${base}expenses`, payload);

        if (res.data.status === "success") {
          this.$emit("created", res.data.expenses);
          this.emitClose();
        } else {
          this.toast(res.data.message || "Something went wrong.", "error");
        }
      } catch (err) {
        if (err.response?.data?.errors) {
          this.toast("Please fix the errors in the form.", "error");
        } else {
          this.toast("Server error. Please try again.", "error");
        }
      } finally {
        this.saving = false;
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
              ? "linear-gradient(135deg, #10b981, #059669)"
              : type === "warning"
              ? "linear-gradient(135deg, #f59e0b, #d97706)"
              : "linear-gradient(135deg, #ef4444, #dc2626)",
          borderRadius: "10px",
          fontWeight: "500",
          fontSize: "14px",
        },
      }).showToast();
    },
  },
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap');

* { box-sizing: border-box; }

.backdrop {
  position: fixed;
  inset: 0;
  background: rgba(2, 8, 20, 0.55);
  backdrop-filter: blur(4px);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  font-family: 'DM Sans', sans-serif;
}

.modal-wrap { width: 100%; max-width: 580px; }

.modal-box {
  background: #fff;
  border-radius: 20px;
  overflow: hidden;
  box-shadow:
    0 0 0 1px rgba(0,0,0,0.05),
    0 24px 64px rgba(0,0,0,0.18);
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 18px 24px;
  border-bottom: 1px solid #f0f4f8;
  background: #fafbfc;
}
.header-left { display: flex; align-items: center; gap: 12px; }
.header-icon {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  background: linear-gradient(135deg, #dbeafe, #bfdbfe);
  color: #1d4ed8;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.modal-title {
  margin: 0;
  font-size: 16px;
  font-weight: 700;
  color: #0f172a;
  letter-spacing: -0.2px;
}
.modal-subtitle {
  font-size: 12px;
  color: #94a3b8;
  display: block;
  margin-top: 2px;
}

.close-btn {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  border: 1px solid #e8ecf0;
  background: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #64748b;
  transition: all 0.15s;
}
.close-btn:hover {
  background: #fee2e2;
  border-color: #fecaca;
  color: #dc2626;
}

.modal-body {
  padding: 20px 24px;
  max-height: 65vh;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
}
.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}

.field-group { display: flex; flex-direction: column; gap: 4px; }
.field-label { font-size: 13px; font-weight: 700; color: #374151; }
.field-sublabel { font-size: 12px; font-weight: 600; color: #475569; }
.req-star { color: #ef4444; margin-left: 2px; }
.optional-tag { color: #94a3b8; font-weight: 400; font-size: 11px; margin-left: 4px; }
.text-hint { font-size: 11px; margin-top: 3px; }

.empty-prompt-card {
  background: #f8fafc;
  border: 1.5px dashed #cbd5e1;
  border-radius: 12px;
  padding: 20px;
  text-align: center;
}
.empty-icon { font-size: 24px; margin-bottom: 4px; }
.empty-text { font-size: 13px; color: #64748b; font-weight: 500; }

.added-sections-wrap {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.category-amount-card {
  background: #ffffff;
  border: 1.5px solid #3b82f6;
  border-radius: 14px;
  padding: 14px;
  box-shadow: 0 4px 14px rgba(59, 130, 246, 0.08);
  animation: cardSlide 0.2s ease-out;
}
@keyframes cardSlide {
  from { opacity: 0; transform: translateY(-8px); }
  to { opacity: 1; transform: translateY(0); }
}

.card-top-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
  padding-bottom: 8px;
  border-bottom: 1px solid #f1f5f9;
}

.cat-badge {
  display: flex;
  align-items: center;
  font-size: 14px;
  color: #1e40af;
}

.btn-remove-cat {
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #dc2626;
  font-size: 11px;
  font-weight: 600;
  padding: 3px 10px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.15s;
}
.btn-remove-cat:hover {
  background: #fee2e2;
  border-color: #fca5a5;
}

.card-inputs-grid {
  display: grid;
  grid-template-columns: 140px 1fr;
  gap: 12px;
}

.input-wrapper { position: relative; display: flex; align-items: center; }

.input-icon {
  position: absolute;
  left: 12px;
  color: #9ca3af;
  display: flex;
  align-items: center;
  pointer-events: none;
  z-index: 1;
}
.currency-sign { font-size: 14px; font-weight: 700; color: #4b5563; }

.field-input {
  width: 100%;
  padding: 8px 12px 8px 36px;
  border: 1.5px solid #e2e8f0;
  border-radius: 9px;
  font-size: 13px;
  font-family: 'DM Sans', sans-serif;
  color: #1e293b;
  background: #fff;
  transition: all 0.15s;
  outline: none;
}
.field-select {
  padding-left: 36px;
}
.field-input:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
}
.field-input.is-error {
  border-color: #ef4444;
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}
.amount-input { font-weight: 700; font-size: 14px; color: #0f172a; }
.error-msg { font-size: 11px; color: #ef4444; font-weight: 500; }

.summary-table-box {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 12px 14px;
}
.summary-title {
  font-size: 12px;
  font-weight: 700;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 6px;
}
.summary-table th { font-size: 11px; color: #64748b; }
.summary-table td { font-size: 13px; }

.modal-footer {
  padding: 14px 24px;
  border-top: 1px solid #f0f4f8;
  background: #fafbfc;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.req-note { font-size: 12px; color: #94a3b8; }
.footer-actions { display: flex; gap: 10px; }

.btn-cancel {
  padding: 9px 18px;
  border: 1.5px solid #e2e8f0;
  border-radius: 9px;
  background: #fff;
  font-size: 13px;
  font-weight: 600;
  color: #64748b;
  cursor: pointer;
  font-family: 'DM Sans', sans-serif;
  transition: all 0.15s;
}
.btn-cancel:hover { border-color: #94a3b8; color: #374151; }

.btn-submit {
  padding: 9px 20px;
  border: none;
  border-radius: 9px;
  background: linear-gradient(135deg, #2563eb, #1d4ed8);
  font-size: 13px;
  font-weight: 600;
  color: #fff;
  cursor: pointer;
  font-family: 'DM Sans', sans-serif;
  display: flex;
  align-items: center;
  gap: 7px;
  transition: all 0.15s;
  box-shadow: 0 2px 8px rgba(37, 99, 235, 0.35);
}
.btn-submit:hover:not(:disabled) {
  background: linear-gradient(135deg, #1d4ed8, #1e40af);
  box-shadow: 0 4px 16px rgba(37, 99, 235, 0.45);
  transform: translateY(-1px);
}
.btn-submit:disabled { opacity: 0.65; cursor: not-allowed; transform: none; }

.spinner {
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255,255,255,0.35);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.modal-fade-enter-active,
.modal-fade-leave-active { transition: opacity 0.2s ease; }
.modal-fade-enter-active .modal-wrap,
.modal-fade-leave-active .modal-wrap {
  transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.2s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to { opacity: 0; }
.modal-fade-enter-from .modal-wrap,
.modal-fade-leave-to .modal-wrap {
  transform: translateY(28px) scale(0.97);
  opacity: 0;
}

@media (max-width: 520px) {
  .card-inputs-grid { grid-template-columns: 1fr; }
  .modal-body { padding: 16px; }
  .modal-footer { flex-direction: column; gap: 10px; align-items: stretch; }
  .footer-actions { justify-content: flex-end; }
}
</style>