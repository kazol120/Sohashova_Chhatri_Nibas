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
                    <path d="M4 6h16M4 12h8M4 18h12"/>
                  </svg>
                </div>
                <div>
                  <h5 class="modal-title">Add Expense Category</h5>
                  <span class="modal-subtitle">Create a new expense category</span>
                </div>
              </div>
              <button class="close-btn" @click="emitClose" aria-label="Close">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
              </button>
            </div>

            <form @submit.prevent="submit">
              <div class="modal-body">
                <div class="field-group">
                  <label class="field-label">Category Name <span class="req-star">*</span></label>
                  <div class="input-wrapper">
                    <span class="input-icon">
                      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 6h16M4 12h8M4 18h12"/>
                      </svg>
                    </span>
                    <input
                      type="text"
                      v-model="form.name"
                      class="field-input"
                      :class="{ 'is-error': errors.name }"
                      placeholder="e.g. Office Supplies, Rent, Salary..."
                      autofocus
                    />
                  </div>
                  <span v-if="errors.name" class="error-msg">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <circle cx="12" cy="12" r="10"/>
                      <line x1="12" y1="8" x2="12" y2="12"/>
                      <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    {{ errors.name }}
                  </span>
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
                    {{ saving ? 'Saving...' : 'Save Category' }}
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
  name: "ExpenseCategoryModal",

  props: {
    show: { type: Boolean, default: false },
  },

  emits: ["close", "created"],

  computed: {
    url() {
      return this.$store.state.url;
    },
  },

  data() {
    return {
      saving: false,
      errors: {},
      form: {
        name: "",
      },
    };
  },

  watch: {
    show(val) {
      document.body.style.overflow = val ? "hidden" : "";
      if (val) this.resetForm();
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
      this.form = { name: "" };
    },

  validate() {
    this.errors = {};
      if (!this.form.name.trim()) {
        this.errors.name = "Category name is required.";
      }
      return Object.keys(this.errors).length === 0;
    },
    async submit() {
      if (!this.validate()) return;
      this.saving = true;
      try {
        const res = await axios.post('/expense-categories', {
          name: this.form.name.trim(),
        });
        if (res.data.status === 'success') {
          this.toast('Category saved successfully!', 'success');
          this.$emit('created', res.data.category);
          this.emitClose();
          this.resetForm();
        }
      } catch (err) {
        if (err.response?.data?.errors) {
          const laravelErrors = err.response.data.errors;
          this.errors = Object.fromEntries(
            Object.entries(laravelErrors).map(([k, v]) => [k, v[0]])
          );
        } else {
          this.toast(err.response?.data?.message || 'Server error. Please try again.', 'error');
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

.modal-wrap { width: 100%; max-width: 420px; }

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
  background: linear-gradient(135deg, #dbeafe, #bfdbfe); /* blue */
  color: #2563eb;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-title {
  margin: 0;
  font-size: 16px;
  font-weight: 700;
  color: #0f172a;
}

.modal-subtitle {
  font-size: 12px;
  color: #94a3b8;
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
}
.close-btn:hover {
  background: #fee2e2;
  color: #dc2626;
}

.modal-body {
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.field-group { display: flex; flex-direction: column; gap: 6px; }

.field-label {
  font-size: 13px;
  font-weight: 600;
  color: #374151;
}

.req-star { color: #ef4444; }

.input-wrapper { position: relative; }

.input-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
}

.field-input {
  width: 100%;
  padding: 11px 14px 11px 38px;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  font-size: 14px;
}
.field-input:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}
.field-input.is-error {
  border-color: #ef4444;
}

.error-msg {
  font-size: 12px;
  color: #ef4444;
}

.preview-card {
  background: linear-gradient(135deg, #eff6ff, #dbeafe);
  border: 1.5px solid #93c5fd;
  border-radius: 12px;
  padding: 12px 16px;
}

.preview-badge {
  background: #2563eb;
  color: #fff;
  padding: 5px 12px;
  border-radius: 20px;
  font-size: 13px;
}

.modal-footer {
  padding: 14px 24px;
  border-top: 1px solid #f0f4f8;
  display: flex;
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
  color: #64748b;
  cursor: pointer;
}

/* 🔥 UPDATED BLUE BUTTON */
.btn-submit {
  padding: 9px 22px;
  border: none;
  border-radius: 10px;
  background: linear-gradient(135deg, #2563eb, #1d4ed8);
  font-size: 13px;
  font-weight: 600;
  color: #fff;
  cursor: pointer;
}

/* hover */
.btn-submit:hover:not(:disabled) {
  background: linear-gradient(135deg, #1d4ed8, #1e40af);
}

.btn-submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.spinner {
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>