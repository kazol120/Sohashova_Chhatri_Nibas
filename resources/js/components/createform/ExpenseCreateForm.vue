<template>
  <Teleport to="body">
    <transition name="modal-fade">
      <div v-if="show" class="backdrop" @click.self="emitClose">
        <div class="modal-wrap">
          <div class="modal-box" role="dialog" aria-modal="true">

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
                  <span class="modal-subtitle">Record a new expense entry</span>
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

                <div class="field-group">
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

                <div class="field-group">
                  <label class="field-label">Category <span class="req-star">*</span></label>
                  <div class="input-wrapper">
                    <span class="input-icon">
                      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 6h16M4 12h8M4 18h12"/>
                      </svg>
                    </span>
                    <select
                      v-model="form.expense_category"
                      class="field-input"
                      :class="{ 'is-error': errors.expense_category }">
                      <option value="" disabled>Select category</option>
                      <option
                        v-for="category in categories"
                        :key="category.id"
                        :value="category.id">
                        {{ category.name }}
                      </option>
                    </select>
                    <span class="select-arrow">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M6 9l6 6 6-6"/>
                      </svg>
                    </span>
                  </div>
                  <span v-if="errors.expense_category" class="error-msg">{{ errors.expense_category }}</span>
                </div>

                <div class="field-group">
                  <label class="field-label">Expense Note <span class="req-star">*</span></label>
                  <div class="input-wrapper textarea-wrapper">
                    <span class="input-icon textarea-icon">
                      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                      </svg>
                    </span>
                    <textarea
                      v-model="form.expense_note"
                      class="field-input field-textarea"
                      :class="{ 'is-error': errors.expense_note }"
                      placeholder="Describe the expense..."
                      rows="3"
                    ></textarea>
                  </div>
                  <span v-if="errors.expense_note" class="error-msg">{{ errors.expense_note }}</span>
                </div>

                <div class="field-group">
                  <label class="field-label">Expense Amount <span class="req-star">*</span></label>
                  <div class="input-wrapper">
                    <span class="input-icon currency-sign">৳</span>
                    <input
                      type="number"
                      v-model="form.expense_amount"
                      class="field-input amount-input"
                      :class="{ 'is-error': errors.expense_amount }"
                      placeholder="0.00"
                      min="0"
                      step="0.01"
                    />
                  </div>
                  <span v-if="errors.expense_amount" class="error-msg">{{ errors.expense_amount }}</span>
                </div>

                <div class="summary-card" v-if="form.expense_amount > 0">
                  <div class="summary-row">
                    <span class="summary-label">Total Amount</span>
                    <span class="summary-amount">৳ {{ Number(form.expense_amount).toFixed(2) }}</span>
                  </div>
                  <div class="summary-row" v-if="categoryLabel">
                    <span class="summary-label">Category</span>
                    <span class="summary-badge">{{ categoryLabel }}</span>
                  </div>
                </div>

              </div>

              <div class="modal-footer">
                <span class="req-note"><span class="req-star">*</span> Required fields</span>
                <div class="footer-actions">
                  <button type="button" class="btn-cancel" @click="emitClose">Cancel</button>
                  <button type="submit" class="btn-submit" :disabled="saving">
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
  computed: {
    url() {
      return this.$store.state.url;
    },

    categoryLabel() {
      const found = this.categories.find(
        item => String(item.id) === String(this.form.expense_category)
      );
      return found ? found.name : "";
    },
  },

  data() {
    return {
      saving: false,
      errors: {},
      categories: [],
      form: {
        date: new Date().toISOString().split("T")[0],
        expense_category: "",
        expense_note: "",
        expense_amount: "",
      },
    };
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
      this.form = {
        date: new Date().toISOString().split("T")[0],
        expense_category: "",
        expense_note: "",
        expense_amount: "",
      };
    },

    async getCategories() {
      try {
        const res = await axios.get(this.url + "expense-categories-list");
        if (res.data.status === "success") {
          this.categories = res.data.data || [];
        }
      } catch (error) {
        this.toast("Failed to load categories.", "error");
      }
    },

    validate() {
      this.errors = {};
      if (!this.form.date) this.errors.date = "Date is required.";
      if (!this.form.expense_category) this.errors.expense_category = "Category is required.";
      if (!this.form.expense_note.trim()) this.errors.expense_note = "Expense note is required.";
      if (!this.form.expense_amount || Number(this.form.expense_amount) <= 0) {
        this.errors.expense_amount = "Enter a valid amount.";
      }
      return Object.keys(this.errors).length === 0;
    },

    async submit() {
      if (!this.validate()) return;

      this.saving = true;
      try {
        const res = await axios.post(this.url + "expenses", {
          date: this.form.date,
          expense_note: this.form.expense_note,
          expense_category: this.form.expense_category,
          expense_amount: this.form.expense_amount,
        });

        if (res.data.status === "success") {
          this.$emit("created", res.data.expense);
          this.emitClose();
        } else {
          this.toast(res.data.message || "Something went wrong.", "error");
        }
      } catch (err) {
        if (err.response?.data?.errors) {
          const laravelErrors = err.response.data.errors;
          this.errors = Object.fromEntries(
            Object.entries(laravelErrors).map(([k, v]) => [k, v[0]])
          );
          this.toast("Please fix the errors.", "error");
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

.modal-wrap { width: 100%; max-width: 480px; }

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
  padding: 20px 24px;
  border-bottom: 1px solid #f0f4f8;
  background: #fafbfc;
}
.header-left { display: flex; align-items: center; gap: 12px; }
.header-icon {
  width: 44px;
  height: 44px;
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
  width: 36px;
  height: 36px;
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
  padding: 22px 24px;
  max-height: 60vh;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}

.field-group { display: flex; flex-direction: column; gap: 5px; }
.field-label { font-size: 13px; font-weight: 600; color: #374151; }
.req-star { color: #ef4444; margin-left: 2px; }

.input-wrapper { position: relative; display: flex; align-items: center; }
.textarea-wrapper { align-items: flex-start; }

.input-icon {
  position: absolute;
  left: 12px;
  color: #9ca3af;
  display: flex;
  align-items: center;
  pointer-events: none;
  z-index: 1;
}
.textarea-icon { top: 11px; }
.currency-sign { font-size: 15px; font-weight: 700; color: #4b5563; }

.select-arrow {
  position: absolute;
  right: 12px;
  color: #9ca3af;
  pointer-events: none;
  display: flex;
  align-items: center;
}

.field-input {
  width: 100%;
  padding: 10px 14px 10px 38px;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  font-size: 14px;
  font-family: 'DM Sans', sans-serif;
  color: #1e293b;
  background: #fff;
  transition: all 0.15s;
  outline: none;
  appearance: none;
}
.field-input:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
}
.field-input.is-error {
  border-color: #ef4444;
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}
.field-textarea { resize: vertical; min-height: 80px; }
.amount-input { font-size: 16px; font-weight: 600; }
.error-msg { font-size: 12px; color: #ef4444; font-weight: 500; }

.summary-card {
  background: linear-gradient(135deg, #f0fdf4, #dcfce7);
  border: 1.5px solid #86efac;
  border-radius: 12px;
  padding: 12px 16px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.summary-row { display: flex; align-items: center; justify-content: space-between; }
.summary-label { font-size: 13px; color: #166534; font-weight: 500; }
.summary-amount { font-size: 20px; font-weight: 700; color: #15803d; letter-spacing: -0.5px; }
.summary-badge {
  font-size: 11px;
  font-weight: 600;
  background: #22c55e;
  color: #fff;
  padding: 2px 10px;
  border-radius: 20px;
  text-transform: capitalize;
}

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
  padding: 9px 20px;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
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
  padding: 9px 22px;
  border: none;
  border-radius: 10px;
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
  .modal-body { padding: 16px; }
  .modal-footer { flex-direction: column; gap: 10px; align-items: stretch; }
  .footer-actions { justify-content: flex-end; }
}
</style>