<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
      <div class="col-12">
        <div class="card mb-4 shadow-sm">

          <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3">
            <h5 class="card-title mb-0">Expense Type Data Table</h5>
            <button class="btn btn-primary" type="button" @click="openCreateFromComponent">
              <i class="ti ti-plus me-1"></i> Add Expense Type
            </button>
          </div>

          <div class="card-body">
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
              <div class="d-flex align-items-center gap-2">
                <label class="small text-muted mb-0">Rows:</label>
                <select
                  class="form-select form-select-sm"
                  style="width:90px"
                  v-model.number="perPage">
                  <option :value="5">5</option>
                  <option :value="10">10</option>
                  <option :value="20">20</option>
                  <option :value="50">50</option>
                </select>
              </div>

              <input
                type="text"
                class="form-control form-control-sm"
                style="width:300px"
                placeholder="Search expense type..."
                v-model="search"
                @keyup.enter="fetchExpenseTypes(1)"
              />
            </div>

            <div class="table-responsive">
              <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th style="width:55px">Sl</th>
                    <th>Name</th>
                    <th style="width:140px">Action</th>
                  </tr>
                </thead>

                <tbody v-if="expenseTypes.length">
                  <tr v-for="(item, index) in expenseTypes" :key="item.id">
                    <td>{{ from + index }}</td>
                    <td>{{ item.name }}</td>
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
                </tbody>

                <tbody v-else>
                  <tr>
                    <td colspan="3" class="text-center py-5 text-muted">
                      <span v-if="loading">
                        <i class="fa fa-spinner fa-spin me-2"></i>Loading...
                      </span>
                      <span v-else>No records found</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
              <div class="small text-muted">
                Total: {{ total }} | Page: {{ currentPage }} / {{ totalPages }}
              </div>
              <div class="d-flex gap-2">
                <button
                  class="btn btn-sm btn-secondary"
                  :disabled="currentPage <= 1 || loading"
                  @click="fetchExpenseTypes(currentPage - 1)">
                  Previous
                </button>
                <button
                  class="btn btn-sm btn-secondary"
                  :disabled="currentPage >= totalPages || loading"
                  @click="fetchExpenseTypes(currentPage + 1)">
                  Next
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- CREATE MODAL -->
    <ExpenseTypeForm
      :show="showCreateModal"
      :base-url="url"
      @close="showCreateModal = false"
      @created="handleCreated"
    />

    <!-- EDIT MODAL -->
    <Teleport to="body">
      <transition name="slide-fade">
        <div v-if="edit.open" class="xmask" @click.self="closeEditModal">
          <div class="xwrap">
            <div class="xbox xsmall" role="dialog" aria-modal="true">
              <div class="xhead d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="ti ti-edit me-2"></i>Edit Expense Type</h5>
                <button type="button" class="btn-close" @click="closeEditModal"></button>
              </div>

              <form @submit.prevent="updateExpenseType">
                <div class="xbody">
                  <div class="mb-3">
                    <label class="form-label fw-semibold">Name</label>
                    <input
                      type="text"
                      class="form-control"
                      v-model="edit.form.name"
                      :class="{ 'is-invalid': errors.name }"
                    />
                    <div class="invalid-feedback">{{ errors.name }}</div>
                  </div>
                </div>

                <div class="xfoot d-flex justify-content-end gap-2">
                  <button type="button" class="btn btn-outline-secondary" @click="closeEditModal">Cancel</button>
                  <button type="submit" class="btn btn-success" :disabled="savingEdit">
                    <span v-if="savingEdit"><i class="fa fa-spinner fa-spin me-1"></i> Updating...</span>
                    <span v-else><i class="ti ti-device-floppy me-1"></i> Update</span>
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </transition>
    </Teleport>

    <!-- DELETE MODAL -->
    <Teleport to="body">
      <transition name="slide-fade">
        <div v-if="del.open" class="xmask" @click.self="closeDeleteModal">
          <div class="xwrap">
            <div class="xbox xsmall" role="dialog" aria-modal="true">
              <div class="xhead d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-danger"><i class="ti ti-trash me-2"></i>Delete Expense Type</h5>
                <button type="button" class="btn-close" @click="closeDeleteModal"></button>
              </div>

              <div class="xbody">
                <div class="alert alert-warning mb-0">
                  Are you sure you want to delete <strong>{{ del.item?.name }}</strong>?
                </div>
              </div>

              <div class="xfoot d-flex justify-content-end gap-2">
                <button class="btn btn-outline-secondary" type="button" @click="closeDeleteModal">Cancel</button>
                <button class="btn btn-danger" type="button" :disabled="savingDelete" @click="confirmDelete">
                  <span v-if="savingDelete"><i class="fa fa-spinner fa-spin me-1"></i> Deleting...</span>
                  <span v-else><i class="ti ti-trash me-1"></i> Yes, Delete</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </Teleport>

  </div>
</template>

<script>
import axios from "axios";
import Toastify from "toastify-js";
import "toastify-js/src/toastify.css";
import ExpenseTypeForm from "../../components/createform/ExpenseTypeForm.vue";

export default {
  name: "ExpenseTypeList",
  components: { ExpenseTypeForm },

  computed: {
    url() {
      return this.$store.state.url;
    },
  },

  data() {
    return {
      showCreateModal: false,

      expenseTypes: [],
      loading: false,
      search: "",
      perPage: 10,
      currentPage: 1,
      totalPages: 1,
      total: 0,
      from: 1,

      edit: {
        open: false,
        form: {
          id: null,
          name: "",
        },
      },

      del: {
        open: false,
        item: null,
      },

      savingEdit: false,
      savingDelete: false,
      errors: {},
      _t: null,
    };
  },

  mounted() {
    this.fetchExpenseTypes(1);
  },

  watch: {
    search() {
      clearTimeout(this._t);
      this._t = setTimeout(() => this.fetchExpenseTypes(1), 300);
    },
    perPage() {
      this.fetchExpenseTypes(1);
    },
    showCreateModal() {
      this.syncBodyOverflow();
    },
    "edit.open"() {
      this.syncBodyOverflow();
    },
    "del.open"() {
      this.syncBodyOverflow();
    },
  },

  beforeUnmount() {
    clearTimeout(this._t);
    document.body.style.overflow = "";
  },

  methods: {
    syncBodyOverflow() {
      document.body.style.overflow =
        (this.showCreateModal || this.edit.open || this.del.open) ? "hidden" : "";
    },

    openCreateFromComponent() {
      this.showCreateModal = true;
    },

    handleCreated() {
      this.showCreateModal = false;
      this.fetchExpenseTypes(1);
    },

    async fetchExpenseTypes(page = 1) {
      this.loading = true;
      try {
        const res = await axios.get('/expense-categories', {
          params: {
            page,
            per_page: this.perPage,
            search: this.search,
          },
        });

        this.expenseTypes = res.data.data || [];
        this.currentPage = res.data.current_page || 1;
        this.totalPages = res.data.last_page || 1;
        this.total = res.data.total || 0;
        this.from = res.data.from || 1;
      } catch (e) {
        console.error(e);
        this.toast("Failed to load expense types", "error");
      } finally {
        this.loading = false;
      }
    },

    openEditModal(item) {
      this.errors = {};
      this.edit.form.id = item.id;
      this.edit.form.name = item.name;
      this.edit.open = true;
    },

    closeEditModal() {
      this.edit.open = false;
      this.edit.form.id = null;
      this.edit.form.name = "";
      this.errors = {};
    },

    async updateExpenseType() {
      this.savingEdit = true;
      this.errors = {};

      try {
        const res = await axios.put(`/expense-categories/${this.edit.form.id}`, {
          name: this.edit.form.name,
        });

        this.toast(res.data.message || "Updated successfully", "success");
        this.closeEditModal();
        await this.fetchExpenseTypes(this.currentPage);
      } catch (e) {
        const data = e?.response?.data;
        if (e?.response?.status === 422 && data?.errors) {
          Object.keys(data.errors).forEach(k => {
            this.errors[k] = Array.isArray(data.errors[k]) ? data.errors[k][0] : data.errors[k];
          });
          this.toast(data?.message || "Validation error", "error");
        } else {
          this.toast(data?.message || "Update failed", "error");
        }
      } finally {
        this.savingEdit = false;
      }
    },

    openDeleteModal(item) {
      this.del.item = item;
      this.del.open = true;
    },

    closeDeleteModal() {
      this.del.open = false;
      this.del.item = null;
    },

    async confirmDelete() {
      if (!this.del.item?.id) return;

      this.savingDelete = true;
      try {
        const res = await axios.delete(`/expense-categories/${this.del.item.id}`);
        this.toast(res.data.message || "Deleted successfully", "success");
        this.closeDeleteModal();

        const willBeEmpty = this.expenseTypes.length === 1 && this.currentPage > 1;
        await this.fetchExpenseTypes(willBeEmpty ? this.currentPage - 1 : this.currentPage);
      } catch (e) {
        this.toast(e?.response?.data?.message || "Delete failed", "error");
      } finally {
        this.savingDelete = false;
      }
    },

    toast(text, type = "success") {
      const bg =
        type === "success"
          ? "linear-gradient(to right,#00b09b,#96c93d)"
          : "linear-gradient(to right,#ff5f6d,#ffc371)";

      Toastify({
        text,
        duration: 2500,
        close: true,
        gravity: "top",
        position: "right",
        backgroundColor: bg,
      }).showToast();
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

/* modal */
.xmask {
  position: fixed;
  inset: 0;
  z-index: 20000;
  background: rgba(0,0,0,.55);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px 16px;
  overflow-y: auto;
}

.xwrap {
  width: 100%;
  min-height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
}

.xbox {
  width: min(94vw, 520px);
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0,0,0,.28);
  overflow: hidden;
}

.xsmall {
  width: min(94vw, 520px);
}
.xhead {
  padding: 16px 20px;
  border-bottom: 1px solid #eef2f7;
  background: #fff;
}
.xbody {
  padding: 20px;
}
.xfoot {
  padding: 14px 20px;
  border-top: 1px solid #eef2f7;
  background: #fafafa;
}

/* transition */
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all .2s ease;
}
.slide-fade-enter-from,
.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(12px) scale(.98);
}
</style>