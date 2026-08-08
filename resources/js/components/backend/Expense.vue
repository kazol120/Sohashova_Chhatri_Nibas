<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
      <div class="col-12">
        <div class="card mb-4 shadow-sm">

          <!-- Header -->
          <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3">
            <h5 class="card-title mb-0">
                {{ todayOnly ? 'Today Expense Data Table' : 'Expense Data Table' }}
            </h5>
            <button class="btn btn-primary" type="button" @click="openCreateFromComponent">
              <i class="ti ti-plus me-1"></i> Add Expense
            </button>
          </div>

          <!-- Date Filter + Print -->
          <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3">
            <div class="d-flex flex-wrap gap-3 align-items-end">
              <template v-if="!todayOnly">
                <div>
                  <label class="mb-2 text-black">Start Date</label>
                  <input class="form-control" type="date" v-model="startDate" @change="filterData">
                </div>
                <div>
                  <label class="mb-2 text-black">End Date</label>
                  <input class="form-control" type="date" v-model="endDate" @change="filterData">
                </div>
                <div>
                  <button class="btn btn-outline-secondary" @click="clearFilters">Clear</button>
                </div>
              </template>
            </div>
            <div class="d-flex gap-2">
              <button class="btn btn-success" type="button" @click="exportExpensesCSV">
                <i class="fa fa-file-excel me-1"></i> Export Excel
              </button>
              <button class="btn btn-primary" type="button" @click="printTable">
                <i class="ti ti-printer me-1"></i> Print
              </button>
            </div>
          </div>

          <!-- Category Select Filter -->
          <div v-if="!todayOnly" class="px-3 pt-3">
            <div class="d-flex mb-4">
              <select
                v-model="form.selected_category"
                class="form-select"
                style="max-width: 300px;"
                @change="fetchExpenses(1)">
                <option value="">All Expense Categories</option>
                <option v-for="cat in expenseTypes" :key="cat.id" :value="cat.id">
                  {{ cat.name }}
                </option>
              </select>
              <div class="ms-4 d-flex align-items-end">
                <button class="btn btn-outline-secondary" @click="clearCategory">Clear</button>
              </div>
            </div>
          </div>

          <div class="card-body">
            <!-- Rows + Search -->
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
              <div class="d-flex align-items-center gap-2">
                <label class="small text-muted mb-0">Rows:</label>
                <select class="form-select form-select-sm" style="width:90px" v-model.number="perPage">
                  <option :value="30">30</option>
                  <option :value="60">60</option>
                  <option :value="50">50</option>
                  <option :value="150">150</option>
                  <option :value="200">200</option>
                </select>
              </div>
              <input
                type="text"
                class="form-control form-control-sm"
                style="width:300px"
                placeholder="Search expense note..."
                v-model="search"
                @keyup.enter="fetchExpenses(1)"
              />
            </div>

            <!-- Grouped Table by Date -->
            <div class="table-responsive">
              <table class="table table-bordered align-middle">
                <thead class="table-light">
                  <tr>
                    <th style="width:55px" class="text-center">Sl</th>
                    <th style="width:130px">Date</th>
                    <th>Expense Category & Note Details</th>
                    <th style="width:160px" class="text-end">Total Amount</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="loading">
                    <td colspan="4" class="text-center py-5 text-muted">
                      <i class="fa fa-spinner fa-spin me-2"></i>Loading...
                    </td>
                  </tr>
                  <tr v-else-if="groupedExpenses.length === 0">
                    <td colspan="4" class="text-center py-5 text-muted">No records found</td>
                  </tr>
                  <template v-else>
                    <tr v-for="(group, gIdx) in groupedExpenses" :key="group.date">
                      <td class="text-center">{{ from + gIdx }}</td>
                      <td>
                        <div class="fw-bold text-dark">{{ group.date }}</div>
                        <small class="text-muted">{{ group.items.length }} Item(s)</small>
                      </td>
                      <td class="p-0">
                        <div class="expense-items-list">
                          <div
                            v-for="(item, idx) in group.items"
                            :key="item.id"
                            class="expense-item-row d-flex align-items-center justify-content-between p-2 px-3"
                            :class="{ 'border-bottom': idx < group.items.length - 1 }"
                          >
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                              <span class="badge bg-label-primary font-bold py-2 px-3">
                                {{ item.expensetype?.name || 'Category' }}
                              </span>
                              <span v-if="item.expense_note" class="text-secondary small ms-1">
                                <i class="ti ti-notes me-1"></i>{{ item.expense_note }}
                              </span>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                              <span class="fw-bold text-dark">৳ {{ parseFloat(item.expense_amount || 0).toFixed(2) }}</span>
                              <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-icon btn-outline-primary" @click="openEditModal(item)" title="Edit Item">
                                  <i class="ti ti-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-icon btn-outline-danger" @click="openDeleteModal(item)" title="Delete Item">
                                  <i class="ti ti-trash"></i>
                                </button>
                              </div>
                            </div>
                          </div>
                        </div>
                      </td>
                      <td class="text-end fw-bold text-success fs-6 pe-3">
                        ৳ {{ group.totalAmount.toFixed(2) }}
                      </td>
                    </tr>
                  </template>
                </tbody>
                <!--  Footer Grand Total -->
                <tfoot>
                  <tr class="table-dark">
                    <td colspan="3" class="text-end fw-bold">Grand Total Amount :</td>
                    <td class="text-end fw-bold text-warning fs-6 pe-3">৳ {{ parseFloat(grandTotal || 0).toFixed(2) }}</td>
                  </tr>
                </tfoot>
              </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
              <div class="small text-muted">
                Total: {{ total }} | Page: {{ currentPage }} / {{ totalPages }}
              </div>
              <div class="d-flex gap-2">
                <button
                  class="btn btn-sm btn-secondary"
                  :disabled="currentPage <= 1 || loading"
                  @click="fetchExpenses(currentPage - 1)">
                  Previous
                </button>
                <button
                  class="btn btn-sm btn-secondary"
                  :disabled="currentPage >= totalPages || loading"
                  @click="fetchExpenses(currentPage + 1)">
                  Next
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ═══════════════ EDIT MODAL ═══════════════ -->
    <div v-if="editOpen" class="modal-overlay" @click.self="closeEditModal">
      <div class="modal-box">
        <div class="modal-box-head d-flex justify-content-between align-items-center">
          <h5 class="mb-0"><i class="ti ti-edit me-2"></i>Edit Expense</h5>
          <button type="button" class="btn-close" @click="closeEditModal"></button>
        </div>
        <form @submit.prevent="updateExpense">
          <div class="modal-box-body">
            <div class="mb-3">
              <label class="form-label fw-semibold">Date</label>
              <input type="date" class="form-control" v-model="editForm.date" :class="{ 'is-invalid': errors.date }" />
              <div class="invalid-feedback">{{ errors.date }}</div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Expense Category</label>
              <select class="form-select" v-model="editForm.expense_category" :class="{ 'is-invalid': errors.expense_category }">
                <option value="" disabled>Select category</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
              </select>
              <div class="invalid-feedback">{{ errors.expense_category }}</div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Expense Note (Optional)</label>
              <input type="text" class="form-control" v-model="editForm.expense_note" :class="{ 'is-invalid': errors.expense_note }" placeholder="Note (optional)" />
              <div class="invalid-feedback">{{ errors.expense_note }}</div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Expense Amount</label>
              <input type="number" step="0.01" class="form-control" v-model="editForm.expense_amount" :class="{ 'is-invalid': errors.expense_amount }" />
              <div class="invalid-feedback">{{ errors.expense_amount }}</div>
            </div>
          </div>
          <div class="modal-box-foot d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-secondary" @click="closeEditModal">Cancel</button>
            <button type="submit" class="btn btn-success" :disabled="savingEdit">
              <span v-if="savingEdit"><i class="fa fa-spinner fa-spin me-1"></i> Updating...</span>
              <span v-else><i class="ti ti-device-floppy me-1"></i> Update</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ═══════════════ DELETE MODAL ═══════════════ -->
    <div v-if="delOpen" class="modal-overlay" @click.self="closeDeleteModal">
      <div class="modal-box">
        <div class="modal-box-head d-flex justify-content-between align-items-center">
          <h5 class="mb-0 text-danger"><i class="ti ti-trash me-2"></i>Delete Expense</h5>
          <button type="button" class="btn-close" @click="closeDeleteModal"></button>
        </div>
        <div class="modal-box-body">
          <div class="alert alert-warning mb-0">
            Are you sure you want to delete: <strong>{{ delItem?.expensetype?.name }} - {{ parseFloat(delItem?.expense_amount || 0).toFixed(2) }} ৳</strong>?
          </div>
        </div>
        <div class="modal-box-foot d-flex justify-content-end gap-2">
          <button class="btn btn-outline-secondary" type="button" @click="closeDeleteModal">Cancel</button>
          <button class="btn btn-danger" type="button" :disabled="savingDelete" @click="confirmDelete">
            <span v-if="savingDelete"><i class="fa fa-spinner fa-spin me-1"></i> Deleting...</span>
            <span v-else><i class="ti ti-trash me-1"></i> Yes, Delete</span>
          </button>
        </div>
      </div>
    </div>

    <!-- ═══════════════ CREATE FORM ═══════════════ -->
    <ExpenseCreateForm
      :show="showCreateModal"
      @close="showCreateModal = false"
      @created="handleCreated"
    />
  </div>
</template>

<script>
import axios from "axios";
import Toastify from "toastify-js";
import "toastify-js/src/toastify.css";
import ExpenseCreateForm from "../../components/createform/ExpenseCreateForm.vue";

export default {
  name: "ExpenseList",
  components: { ExpenseCreateForm },

  data() {
    return {
      expenses:        [],
      categories:      [],
      expenseTypes:    [],
      loading:         false,
      search:          '',
      perPage:         50,       
      total:           0,
      from:            1,
      currentPage:     1,
      totalPages:      1,
      grandTotal:      0,       
      showCreateModal: false,
      errors:          {},
      startDate:       '',
      endDate:         '',
      todayOnly:       false,
      form: {
        selected_category: '',
      },
      editOpen: false,
      editForm: {
        id:               null,
        date:             '',
        expense_category: '',
        expense_note:     '',
        expense_amount:   '',
      },
      savingEdit: false,
      delOpen:      false,
      delItem:      null,
      savingDelete: false,
    };
  },

  computed: {
    url() {
      return this.$store.state.url;
    },

    groupedExpenses() {
      if (!this.expenses || !this.expenses.length) return [];

      const groupsMap = new Map();

      this.expenses.forEach(item => {
        const key = item.date || 'No Date';
        if (!groupsMap.has(key)) {
          groupsMap.set(key, {
            date: key,
            items: [],
            totalAmount: 0,
          });
        }
        const grp = groupsMap.get(key);
        grp.items.push(item);
        grp.totalAmount += parseFloat(item.expense_amount || 0);
      });

      return Array.from(groupsMap.values());
    },
  },

  mounted() {
    if (window.location.pathname.includes('today-expense')) {
      this.todayOnly = true;
    }
    this.fetchExpenses(1);
    this.fetchCategories();
    this.getExpenseTypes();
  },

  watch: {
    search() {
      clearTimeout(this._t);
      this._t = setTimeout(() => this.fetchExpenses(1), 300);
    },
    perPage() {
      this.fetchExpenses(1);
    },
  },

  methods: {
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

    async getExpenseTypes() {
      try {
        const base = this.url.endsWith('/') ? this.url : `${this.url}/`;
        const res = await axios.get(`${base}get-select-expense`);
        if (res.data.status === "success") {
          this.expenseTypes = res.data.data || [];
        }
      } catch {
        this.toast("Failed to load expense types.", "error");
      }
    },

    async fetchExpenses(page = 1) {
      this.loading = true;
      try {
        const base = this.url.endsWith('/') ? this.url : `${this.url}/`;
        const endpoint = this.todayOnly 
          ? `${base}today-expense-list`
          : `${base}expense-list`;

        const res = await axios.get(endpoint, {
          params: {
            page,
            per_page:    this.perPage,
            search:      this.search,
            start_date:  this.startDate,
            end_date:    this.endDate,
            category_id: this.form.selected_category,
          },
        });
        this.expenses    = res.data.expenses     || [];
        this.total       = res.data.total        || 0;
        this.from        = res.data.from         ?? 1;
        this.currentPage = res.data.current_page || 1;
        this.totalPages  = res.data.last_page    || 1;
        this.grandTotal  = parseFloat(res.data.grand_total || 0);
      } catch {
        this.toast('Failed to load expenses.', 'error');
      } finally {
        this.loading = false;
      }
    },

    async fetchCategories() {
      try {
        const base = this.url.endsWith('/') ? this.url : `${this.url}/`;
        const res = await axios.get(`${base}expense-type-list`);
        this.categories = res.data.data || [];
      } catch {
        this.toast('Failed to load categories.', 'error');
      }
    },

    filterData() {
      this.fetchExpenses(1);
    },

    clearFilters() {
      this.startDate = '';
      this.endDate   = '';
      this.search    = '';
      this.form.selected_category = '';
      this.fetchExpenses(1);
    },

    clearCategory() {
      this.form.selected_category = '';
      this.fetchExpenses(1);
    },

    printTable() {
      const rows = this.groupedExpenses.map((group, index) => {
        const itemsHtml = group.items.map(item => `
          <div style="display:flex; justify-content:space-between; margin-bottom:4px; padding-bottom:4px; border-bottom:1px dashed #ddd;">
            <div>
              <strong>${item.expensetype?.name || '—'}</strong>
              ${item.expense_note ? `<span style="color:#666; font-size:11px;"> (${item.expense_note})</span>` : ''}
            </div>
            <div>৳ ${parseFloat(item.expense_amount || 0).toFixed(2)}</div>
          </div>
        `).join('');

        return `
          <tr>
            <td style="vertical-align:top; text-align:center;">${this.from + index}</td>
            <td style="vertical-align:top;"><strong>${group.date || '—'}</strong></td>
            <td style="vertical-align:top;">${itemsHtml}</td>
            <td style="vertical-align:top; text-align:right; font-weight:bold;">৳ ${group.totalAmount.toFixed(2)}</td>
          </tr>
        `;
      }).join('');

      const totalRow = `
        <tr>
          <td colspan="3" style="text-align:right; font-weight:bold; background:#111; color:#fff;">Grand Total Amount :</td>
          <td style="text-align:right; font-weight:bold; background:#111; color:#ffcc00;">৳ ${parseFloat(this.grandTotal || 0).toFixed(2)}</td>
        </tr>
      `;

      const html = `
        <!DOCTYPE html>
        <html>
        <head>
          <title>Expense Report</title>
          <style>
            @page { size: A4 portrait; margin: 15mm; }
            * { box-sizing: border-box; margin: 0; padding: 0; }
            body { font-family: Arial, sans-serif; font-size: 12px; }
            h2 { text-align: center; margin-bottom: 6px; font-size: 16px; }
            p.sub { text-align: center; margin-bottom: 12px; font-size: 12px; font-weight: 400;}
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #999; padding: 8px 10px; text-align: left; }
            th { background: #e9e9e9; font-weight: bold; }
            tr:nth-child(even) td { background: #fafafa; }
          </style>
        </head>
        <body>
          <h2>Expense Report</h2>
          <p class="sub">Printed: ${new Date().toLocaleString()}</p>
          <table>
            <thead>
              <tr>
                <th style="width:40px; text-align:center;">Sl</th>
                <th style="width:110px;">Date</th>
                <th>Expense Category & Note Details</th>
                <th style="width:130px; text-align:right;">Total Amount</th>
              </tr>
            </thead>
            <tbody>
              ${rows}
              ${totalRow}
            </tbody>
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

    openCreateFromComponent() {
      this.showCreateModal = true;
    },

    handleCreated() {
      this.showCreateModal = false;
      this.fetchExpenses(1);
      this.toast('Expenses saved successfully.');
    },

    openEditModal(item) {
      this.errors   = {};
      this.editForm = {
        id:               item.id,
        date:             item.date,
        expense_category: item.expense_category,
        expense_note:     item.expense_note || '',
        expense_amount:   item.expense_amount,
      };
      this.editOpen = true;
    },

    closeEditModal() {
      this.editOpen = false;
      this.errors   = {};
    },

    async updateExpense() {
      this.savingEdit = true;
      this.errors     = {};
      try {
        const base = this.url.endsWith('/') ? this.url : `${this.url}/`;
        await axios.put(`${base}expense-update/${this.editForm.id}`, this.editForm);
        this.toast('Expense updated successfully.');
        this.closeEditModal();
        this.fetchExpenses(this.currentPage);
      } catch (err) {
        if (err.response?.status === 422) {
          const raw = err.response.data.errors;
          Object.keys(raw).forEach(k => (this.errors[k] = raw[k][0]));
        } else {
          this.toast('Update failed.', 'error');
        }
      } finally {
        this.savingEdit = false;
      }
    },

    openDeleteModal(item) {
      this.delItem = { ...item };
      this.delOpen = true;
    },

    closeDeleteModal() {
      this.delOpen = false;
      this.delItem = null;
    },

    async confirmDelete() {
      this.savingDelete = true;
      try {
        const base = this.url.endsWith('/') ? this.url : `${this.url}/`;
        await axios.delete(`${base}expense-delete/${this.delItem.id}`);
        this.toast('Expense deleted successfully.');
        this.closeDeleteModal();
        this.fetchExpenses(this.currentPage);
      } finally {
        this.savingDelete = false;
      }
    },

    exportExpensesCSV() {
      if (!this.expenses || this.expenses.length === 0) {
        this.toast("এক্সপোর্ট করার মতো কোনো ডাটা পাওয়া যায়নি", "warning");
        return;
      }
      const headers = ["SL", "Expense Title / Items", "Category", "Amount", "Voucher No", "Expense Date"];
      const rows = this.expenses.map((ex, index) => [
        index + 1,
        ex.expense_title || ex.expense_name || '',
        ex.expense_type ? ex.expense_type.expense_type : (ex.category_name || ''),
        ex.expense_amount || 0,
        ex.voucher_no || '-',
        ex.expense_date || (ex.created_at ? ex.created_at.slice(0, 10) : '')
      ]);
      const filename = `Expense_Report_${new Date().toISOString().slice(0, 10)}.csv`;
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
.expense-items-list {
  background: #fff;
}
.expense-item-row {
  transition: background 0.15s;
}
.expense-item-row:hover {
  background: #f8fafc;
}
.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 99999;
  background: rgba(0,0,0,0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}
.modal-box {
  background: #fff;
  border-radius: 12px;
  width: 100%;
  max-width: 500px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
  overflow: hidden;
}
.modal-box-head {
  padding: 16px 20px;
  border-bottom: 1px solid #eef2f7;
  background: #fff;
}
.modal-box-body {
  padding: 20px;
  max-height: 65vh;
  overflow-y: auto;
}
.modal-box-foot {
  padding: 14px 20px;
  border-top: 1px solid #eef2f7;
  background: #fafafa;
}
</style>