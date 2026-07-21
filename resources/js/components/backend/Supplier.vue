<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-10 col-xl-12">
        <div class="card mb-4 shadow-sm">
          <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3">
            <div>
              <h5 class="card-title mb-0">Supplier List</h5>
            </div>

            <button class="btn btn-primary" type="button" @click="openCreateFromComponent">
              <i class="ti ti-plus me-1"></i> Add Supplier
            </button>
          </div>

          <div class="card-body">
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
              <div class="d-flex align-items-center gap-2">
                <label class="small text-muted mb-0">Rows:</label>
                <select class="form-select form-select-sm" style="width: 90px" v-model.number="perPage">
                  <option :value="5">5</option>
                  <option :value="10">10</option>
                  <option :value="20">20</option>
                  <option :value="50">50</option>
                </select>
              </div>

              <div class="d-flex gap-2 align-items-center">
                <input
                  type="text"
                  class="form-control form-control-sm"
                  style="width: 240px"
                  placeholder="Search by name..."
                  v-model="search"
                  @keyup.enter="getsupplierdata(1)"
                />
              </div>
            </div>

            <div class="table-responsive">
              <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th style="width: 160px">Sl</th>
                    <th style="width: 160px">Name</th>
                    <th style="width: 160px">Action</th>
                  </tr>
                </thead>

                <tbody v-if="suppliers.length">
                  <tr v-for="(s, idx) in suppliers" :key="s.id">
                    <td>
                      <div class="fw-semibold">
                        {{ from + idx + 1 }}
                      </div>
                    </td>

                    <td>
                      <div class="fw-semibold">
                        {{ s.name || "-" }}
                      </div>
                    </td>

                    <td>
                      <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-primary" @click="openEditModal(s)">
                          <i class="ti ti-edit"></i>
                        </button>

                        <button class="btn btn-sm btn-danger" @click="openDeleteModal(s)">
                          <i class="ti ti-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>

                <tbody v-else>
                  <tr>
                    <td colspan="3" class="text-center py-4 text-muted">
                      <span v-if="loading">
                        <i class="fa fa-spinner fa-spin me-2"></i>Loading...
                      </span>
                      <span v-else>No supplier found</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
              <div class="small text-muted">
                Total: {{ total }} | Page: {{ currentPage }} / {{ totalPages }}
              </div>

              <div class="d-flex align-items-center gap-2">
                <button
                  class="btn btn-sm btn-secondary"
                  :disabled="currentPage <= 1 || loading"
                  @click="getsupplierdata(currentPage - 1)"
                >
                  Previous
                </button>

                <button
                  class="btn btn-sm btn-secondary"
                  :disabled="currentPage >= totalPages || loading"
                  @click="getsupplierdata(currentPage + 1)"
                >
                  Next
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- CREATE MODAL -->
    <SupplierCreateForm
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
            <div class="xbox" role="dialog" aria-modal="true">
              <div class="xhead d-flex justify-content-between align-items-center">
                <div>
                  <h5 class="mb-0">Edit Supplier</h5>
                </div>

                <button type="button" class="btn btn-sm btn-light" @click="closeEditModal">
                  <i class="fa fa-times"></i>
                </button>
              </div>

              <form @submit.prevent="updatesupplier">
                <div class="xbody">
                  <div class="row g-3">
                    <div class="col-12">
                      <label class="form-label fw-semibold">
                        Name <code class="req">*</code>
                      </label>
                      <input
                        type="text"
                        class="form-control"
                        v-model="edit.form.name"
                        placeholder="Enter supplier name"
                      />
                      <small v-if="errors.name" class="text-danger d-block mt-1">{{ errors.name }}</small>
                    </div>
                  </div>
                </div>

                <div class="xfoot d-flex justify-content-end gap-2">
                  <button type="button" class="btn btn-outline-secondary" @click="closeEditModal">
                    Cancel
                  </button>
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
        <div v-if="del.open" class="xmask" @click.self="closeDelete">
          <div class="xwrap">
            <div class="xbox xsmall" role="dialog" aria-modal="true">
              <div class="xhead d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-danger">Delete Supplier</h5>
                <button type="button" class="btn btn-sm btn-light" @click="closeDelete">
                  <i class="fa fa-times"></i>
                </button>
              </div>

              <div class="xbody">
                <div class="alert alert-warning mb-0">
                  Are you sure you want to delete this supplier
                  <b>{{ del.item?.name }}</b> ?
                </div>
              </div>

              <div class="xfoot d-flex justify-content-end gap-2">
                <button class="btn btn-outline-secondary" type="button" @click="closeDelete">
                  Cancel
                </button>
                <button class="btn btn-danger" type="button" :disabled="savingDelete" @click="confirmDelete">
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
import SupplierCreateForm from "../../components/createform/SupplierCreateForm.vue";

export default {
  name: "SupplierList",
  components: { SupplierCreateForm },

  data() {
    return {
      showCreateModal: false,
      suppliers: [],
      loading: false,
      search: "",
      perPage: 10,
      currentPage: 1,
      totalPages: 1,
      total: 0,
      from: 0,
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

  computed: {
    url() {
      return this.$store.state.url;
    },
  },

  mounted() {
    this.getsupplierdata(1);
  },

  watch: {
    search() {
      clearTimeout(this._t);
      this._t = setTimeout(() => this.getsupplierdata(1), 300);
    },
    perPage() {
      this.getsupplierdata(1);
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
      const anyOpen = this.showCreateModal || this.edit.open || this.del.open;
      document.body.style.overflow = anyOpen ? "hidden" : "";
    },

    toast(text, type = "success") {
      const bg =
        type === "success"
          ? "linear-gradient(to right, #00b09b, #96c93d)"
          : type === "warning"
          ? "linear-gradient(to right, #f59e0b, #fbbf24)"
          : "linear-gradient(to right, #ff5f6d, #ffc371)";

      Toastify({
        text: text || "Something went wrong",
        duration: 2500,
        close: true,
        gravity: "top",
        position: "right",
        backgroundColor: bg,
      }).showToast();
    },

    async getsupplierdata(page = 1) {
      this.loading = true;
      try {
        const res = await axios.get(`${this.url}get-supplier`, {
          params: {
            page,
            per_page: this.perPage,
            search: this.search,
          },
        });

        this.suppliers = res.data.data || [];
        this.currentPage = res.data.current_page || 1;
        this.totalPages = res.data.last_page || 1;
        this.total = res.data.total || 0;
        this.from = res.data.from ? res.data.from - 1 : 0;
      } catch (e) {
        console.error(e);
        this.toast(e?.response?.data?.message || "Failed to load supplier list", "error");
      } finally {
        this.loading = false;
      }
    },

    openCreateFromComponent() {
      this.showCreateModal = true;
    },

    async handleCreated() {
      this.showCreateModal = false;
      await this.getsupplierdata(this.currentPage);
    },

    openEditModal(s) {
      this.errors = {};
      this.edit.form = {
        id: s.id,
        name: s.name || "",
      };
      this.edit.open = true;
    },

    closeEditModal() {
      this.edit.open = false;
      this.errors = {};
      this.edit.form = {
        id: null,
        name: "",
      };
    },

    async updatesupplier() {
      this.savingEdit = true;
      try {
        const fd = new FormData();
        fd.append("name", this.edit.form.name);

        const res = await axios.post(
          `${this.url}supplier-update-data/${this.edit.form.id}`,
          fd
        );

        this.toast(res.data?.message || "Updated successfully", "success");
        this.closeEditModal();
        await this.getsupplierdata(this.currentPage);
      } catch (e) {
        const data = e?.response?.data;
        if (e?.response?.status === 422 && data?.errors) {
          Object.keys(data.errors).forEach((k) => {
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

    openDeleteModal(s) {
      this.del.item = s;
      this.del.open = true;
    },

    closeDelete() {
      this.del.open = false;
      this.del.item = null;
    },

    async confirmDelete() {
      if (!this.del.item?.id) return;

      this.savingDelete = true;
      try {
        const res = await axios.delete(
          `${this.url}supplier-delete/${this.del.item.id}`
        );

        this.toast(res.data?.message || "Deleted successfully", "success");
        this.closeDelete();

        const willBeEmpty = this.suppliers.length === 1 && this.currentPage > 1;
        const page = willBeEmpty ? this.currentPage - 1 : this.currentPage;
        await this.getsupplierdata(page);
      } catch (e) {
        const data = e?.response?.data;
        console.error(e);
        this.toast(data?.message || "Delete failed", "error");
      } finally {
        this.savingDelete = false;
      }
    },
  },
};
</script>

<style scoped>
.img-thumb {
  width: 140px;
  height: 90px;
  object-fit: cover;
  border-radius: 10px;
  border: 1px solid #e5e7eb;
  background: #fff;
}

.text-truncate-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.xmask {
  position: fixed;
  inset: 0;
  z-index: 20000;
  background: rgba(0, 0, 0, 0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  overflow-y: auto;
}

.xwrap {
  width: 100%;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
}

.xbox {
  width: min(96vw, 760px);
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 18px 55px rgba(0, 0, 0, 0.25);
  border: 1px solid rgba(0, 0, 0, 0.06);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.xbox.xsmall {
  width: min(94vw, 520px);
}

.xhead {
  padding: 16px 20px;
  border-bottom: 1px solid #eef2f7;
  background: #fff;
}

.xbody {
  padding: 20px;
  max-height: calc(100vh - 190px);
  overflow: auto;
}

.xfoot {
  padding: 14px 20px;
  border-top: 1px solid #eef2f7;
  background: #fff;
}

.req {
  color: #dc3545;
}

.form-control,
textarea {
  border-radius: 10px;
  border: 1px solid #d7dde5;
  padding: 10px 12px;
  box-shadow: none;
}

.form-control:focus,
textarea:focus {
  border-color: #6f63f6;
  box-shadow: 0 0 0 0.2rem rgba(111, 99, 246, 0.12);
}

.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.18s ease;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(10px) scale(0.98);
}
</style>