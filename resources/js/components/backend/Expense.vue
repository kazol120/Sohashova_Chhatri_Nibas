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
          <div>
            <button class="btn btn-primary" type="button" @click="printTable">
              <i class="ti ti-printer me-1"></i> Print
            </button>
          </div>

        </div>
          <!-- Category Select Filter -->
          <div v-if="!todayOnly"  class="px-3 pt-3">
            <div class="d-flex mb-4">
              <select
                v-model="form.selected_category"
                class="form-select"
                style="max-width: 300px;"
                @change="fetchExpenses(1)">
                <option value="">All Expense</option>
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

            <!-- Table -->
            <div class="table-responsive">
              <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th style="width:55px">Sl</th>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Expense Note</th>
                    <th>Expense Amount</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="loading">
                    <td colspan="6" class="text-center py-5 text-muted">
                      <i class="fa fa-spinner fa-spin me-2"></i>Loading...
                    </td>
                  </tr>
                  <tr v-else-if="expenses.length === 0">
                    <td colspan="6" class="text-center py-5 text-muted">No records found</td>
                  </tr>
                  <template v-else>
                    <tr v-for="(item, index) in expenses" :key="item.id">
                      <td>{{ from + index }}</td>
                      <td>{{ item.date }}</td>
                      <td>{{ item.expensetype?.name }}</td>
                      <td>{{ item.expense_note }}</td>
                      <td>{{ parseFloat(item.expense_amount || 0).toFixed(2) }} ৳</td>
                      <td>
                        <div class="d-flex gap-1">
                          <button class="btn btn-sm btn-primary" @click="openEditModal(item)">
                            <i class="ti ti-edit"></i>
                          </button>
                          <button class="btn btn-sm btn-danger" @click="openDeleteModal(item)">
                            <i class="ti ti-trash"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                  </template>
                </tbody>
                <!--  Footer Grand Total - all page  fixed -->
                <tfoot>
                  <tr class="table-dark">
                    <td colspan="4" class="text-end fw-bold">Total Amount :</td>
                    <td class="fw-bold text-warning">{{ parseFloat(grandTotal || 0).toFixed(2) }} ৳</td>
                    <td></td>
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
              <label class="form-label fw-semibold">Expense Note</label>
              <input type="text" class="form-control" v-model="editForm.expense_note" :class="{ 'is-invalid': errors.expense_note }" />
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
            Are you sure you want to delete: <strong>{{ delItem?.expense_note }}</strong>?
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
      :base-url="url"
      :categories="categories"
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

  computed: {
    url() {
      return this.$store.state.url;
    },
  },
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
      todayOnly: false,
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
        const res = await axios.get("/get-select-expense");
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
    const endpoint = this.todayOnly 
      ? `${this.url}today-expense-list`
      : `${this.url}expense-list`;

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
        const res = await axios.get(`${this.url}expense-type-list`);
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
  const rows = this.expenses.map((item, index) => `
    <tr>
      <td>${this.from + index}</td>
      <td>${item.date || '—'}</td>
      <td>${item.expensetype?.name || '—'}</td>
      <td>${item.expense_note || '—'}</td>
      <td>${parseFloat(item.expense_amount || 0).toFixed(2)} ৳</td>
    </tr>
  `).join('');
  const totalRow = `
    <tr>
      <td colspan="4" style="text-align:right; font-weight:bold; background:black; color:black;">Total Amount :</td>
      <td style="font-weight:bold; background:black; color:#black;">${parseFloat(this.grandTotal || 0).toFixed(2)} ৳</td>
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
          th, td { border: 1px solid #999; padding: 7px 9px; text-align: left; }
          th { background: #e9e9e9; font-weight: bold; }
          tr:nth-child(even) td { background: #f9f9f9; }
        </style>
      </head>
      <body>
        <h2>Expense Report</h2>
       <p class="sub">Printed: ${new Date().toLocaleString()}</p>
        <table>
          <thead>
            <tr>
              <th>Sl</th>
              <th>Date</th>
              <th>Category</th>
              <th>Expense Note</th>
              <th>Amount</th>
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
      this.toast('Expense added successfully.');
    },

    openEditModal(item) {
      this.errors   = {};
      this.editForm = {
        id:               item.id,
        date:             item.date,
        expense_category: item.expense_category,
        expense_note:     item.expense_note,
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
        await axios.put(`${this.url}expense-update/${this.editForm.id}`, this.editForm);
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
        await axios.delete(`${this.url}expense-delete/${this.delItem.id}`);
        this.toast('Expense deleted successfully.');
        this.closeDeleteModal();
        this.fetchExpenses(this.currentPage);
      } catch {
        this.toast('Delete failed.', 'error');
      } finally {
        this.savingDelete = false;
      }
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