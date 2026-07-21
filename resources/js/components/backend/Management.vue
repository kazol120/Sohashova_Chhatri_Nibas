<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
      <div class="col-12">
        <div class="card mb-4 shadow-sm">

          <div class="card-header d-flex justify-content-between align-items-center py-3">
            <h5 class="card-title mb-0">Management List</h5>
            <button class="btn btn-primary" type="button" @click="showCreateModal = true">
              <i class="ti ti-plus me-1"></i> Add Management
            </button>
          </div>

          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div class="d-flex align-items-center gap-2">
                <label class="small text-muted mb-0">Rows:</label>
                <select class="form-select form-select-sm" style="width:90px" v-model.number="perPage">
                  <option :value="10">10</option>
                  <option :value="20">20</option>
                  <option :value="50">50</option>
                </select>
              </div>
              <input
                type="text"
                class="form-control form-control-sm"
                style="width:240px"
                placeholder="Search by name..."
                v-model="search"
                @keyup.enter="getData(1)"
              />
            </div>

            <div class="table-responsive">
              <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th style="width:55px">Sl</th>
                    <th>Name</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="loading">
                    <td colspan="3" class="text-center py-4">
                      <i class="fa fa-spinner fa-spin me-2"></i>Loading...
                    </td>
                  </tr>
                  <tr v-else-if="list.length === 0">
                    <td colspan="3" class="text-center py-4 text-muted">No records found</td>
                  </tr>
                  <tr v-else v-for="(item, idx) in list" :key="item.id">
                    <td>{{ from + idx }}</td>
                    <td>{{ item.name }}</td>
                    <td>
                      <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-primary" @click="openEdit(item)">
                          <i class="ti ti-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" @click="openDelete(item)">
                          <i class="ti ti-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
              <div class="small text-muted">
                Total: {{ total }} | Page: {{ currentPage }} / {{ totalPages }}
              </div>
              <div class="d-flex gap-2">
                <button class="btn btn-sm btn-secondary"
                  :disabled="currentPage <= 1 || loading"
                  @click="getData(currentPage - 1)">Previous</button>
                <button class="btn btn-sm btn-secondary"
                  :disabled="currentPage >= totalPages || loading"
                  @click="getData(currentPage + 1)">Next</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- CREATE MODAL -->
    <ManagementCreateForm
      :show="showCreateModal"
      @close="showCreateModal = false"
      @created="handleCreated"
    />

    <!-- EDIT MODAL -->
    <Teleport to="body">
      <transition name="slide-fade">
        <div v-if="edit.open" class="xmask" @click.self="closeEdit">
          <div class="xwrap">
            <div class="xbox">
              <div class="xhead d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Edit Management</h5>
                <button class="btn btn-sm btn-light" @click="closeEdit">
                  <i class="fa fa-times"></i>
                </button>
              </div>
              <div class="xbody">
                <div class="mb-3">
                  <label class="form-label">Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" v-model="form.name" placeholder="Enter name" />
                </div>
              </div>
              <div class="xfoot d-flex justify-content-end gap-2">
                <button class="btn btn-outline-secondary" @click="closeEdit">Cancel</button>
                <button class="btn btn-success" :disabled="saving" @click="updateData">
                  <span v-if="saving"><i class="fa fa-spinner fa-spin me-1"></i> Updating...</span>
                  <span v-else>Update</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </Teleport>

    <!-- DELETE MODAL -->
    <Teleport to="body">
      <transition name="slide-fade">
        <div v-if="del.open" class="xmask" @click.self="closeDelete">
          <div class="xwrap">
            <div class="xbox xsmall">
              <div class="xhead d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-danger">Delete Management</h5>
                <button class="btn btn-sm btn-light" @click="closeDelete">
                  <i class="fa fa-times"></i>
                </button>
              </div>
              <div class="xbody">
                <div class="alert alert-warning mb-0">
                  Are you sure you want to delete <b>{{ del.item?.name }}</b>?
                </div>
              </div>
              <div class="xfoot d-flex justify-content-end gap-2">
                <button class="btn btn-outline-secondary" @click="closeDelete">Cancel</button>
                <button class="btn btn-danger" :disabled="savingDelete" @click="confirmDelete">
                  <span v-if="savingDelete"><i class="fa fa-spinner fa-spin me-1"></i> Deleting...</span>
                  <span v-else>Yes, Delete</span>
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
import ManagementCreateForm from "../../components/createform/ManagementCreateForm.vue";

export default {
  name: "ManagementList",
  components: { ManagementCreateForm },
  computed: {
    url() { return this.$store.state.url; },
  },
  data() {
    return {
      list: [], loading: false,
      search: '', perPage: 10,
      total: 0, from: 1,
      currentPage: 1, totalPages: 1,
      showCreateModal: false,
      edit: { open: false },
      del: { open: false, item: null },
      form: { id: null, name: '' },
      saving: false, savingDelete: false,
    };
  },
  mounted() { this.getData(1); },
  watch: {
    search() {
      clearTimeout(this._t);
      this._t = setTimeout(() => this.getData(1), 300);
    },
    perPage() { this.getData(1); },
  },
  methods: {
    toast(text, type = "success") {
      Toastify({
        text, duration: 3000, gravity: "top", position: "right",
        style: {
          background: type === "success"
            ? "linear-gradient(to right, #22c55e, #16a34a)"
            : "linear-gradient(to right, #ef4444, #dc2626)"
        },
      }).showToast();
    },
    async getData(page = 1) {
      this.loading = true;
      try {
        const res = await axios.get(`${this.url}get-management`, {
          params: { page, per_page: this.perPage, search: this.search },
        });
        this.list        = res.data.data         || [];
        this.total       = res.data.total        || 0;
        this.from        = res.data.from         ?? 1;
        this.currentPage = res.data.current_page || 1;
        this.totalPages  = res.data.last_page    || 1;
      } catch {
        this.toast('Failed to load data.', 'error');
      } finally {
        this.loading = false;
      }
    },
    handleCreated() {
      this.showCreateModal = false;
      this.getData(1);
    },
    openEdit(item) {
      this.form = { id: item.id, name: item.name };
      this.edit.open = true;
    },
    closeEdit() {
      this.edit.open = false;
      this.form = { id: null, name: '' };
    },
    async updateData() {
      this.saving = true;
      try {
        const res = await axios.post(`${this.url}management-update/${this.form.id}`, this.form);
        this.toast(res.data?.message || 'Updated successfully');
        this.closeEdit();
        this.getData(this.currentPage);
      } catch {
        this.toast('Update failed.', 'error');
      } finally {
        this.saving = false;
      }
    },
    openDelete(item) {
      this.del.item = item;
      this.del.open = true;
    },
    closeDelete() {
      this.del.open = false;
      this.del.item = null;
    },
    async confirmDelete() {
      this.savingDelete = true;
      try {
        const res = await axios.delete(`${this.url}management-delete/${this.del.item.id}`);
        this.toast(res.data?.message || 'Deleted successfully');
        this.closeDelete();
        this.getData(this.currentPage);
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
.xmask { position:fixed; inset:0; z-index:20000; background:rgba(0,0,0,0.55); display:flex; align-items:center; justify-content:center; padding:16px; }
.xwrap { width:100%; min-height:100vh; display:flex; align-items:center; justify-content:center; }
.xbox { width:min(96vw,500px); background:#fff; border-radius:18px; box-shadow:0 18px 55px rgba(0,0,0,0.25); overflow:hidden; }
.xbox.xsmall { width:min(94vw,450px); }
.xhead { padding:16px 20px; border-bottom:1px solid #eef2f7; }
.xbody { padding:20px; }
.xfoot { padding:14px 20px; border-top:1px solid #eef2f7; background:#fafafa; }
.slide-fade-enter-active, .slide-fade-leave-active { transition:all 0.18s ease; }
.slide-fade-enter-from, .slide-fade-leave-to { opacity:0; transform:translateY(10px) scale(0.98); }
</style>