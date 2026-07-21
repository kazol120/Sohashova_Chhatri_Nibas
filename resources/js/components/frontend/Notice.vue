<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-10 col-xl-12">
        <div class="card mb-4 shadow-sm">
          <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3">
            <div>
              <h5 class="card-title mb-0">Notice Board</h5>
              <small class="text-muted">Manage marquee scrolling notices</small>
            </div>

            <button class="btn btn-primary" type="button" @click="openCreateFromComponent">
              <i class="ti ti-plus me-1"></i> Add Notice
            </button>
          </div>

          <div class="card-body">
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
              <div class="d-flex align-items-center gap-2">
                <label class="small text-muted mb-0">Rows:</label>
                <select class="form-select form-select-sm" style="width:90px" v-model.number="perPage">
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
                  style="width: 240px;"
                  placeholder="Search notices..."
                  v-model="search"
                  @keyup.enter="fetchNotices(1)"
                />
              </div>
            </div>

            <div class="table-responsive">
              <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th style="width:70px">Sl</th>
                    <th>Notice Text</th>
                    <th style="width:140px">Actions</th>
                  </tr>
                </thead>

                <tbody v-if="notices.length">
                  <tr v-for="(n, idx) in notices" :key="n.id">
                    <td>{{ from + idx }}</td>
                    <td>
                      <span class="text-wrap">{{ n.notice }}</span>
                    </td>
                    <td>
                      <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-primary" @click="openEditModal(n)">
                          <i class="ti ti-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" @click="openDeleteModal(n)">
                          <i class="ti ti-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>

                <tbody v-else>
                  <tr>
                    <td colspan="3" class="text-center py-4 text-muted">
                      <span v-if="loading"><i class="fa fa-spinner fa-spin me-2"></i>Loading...</span>
                      <span v-else>No notices found</span>
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
                  @click="fetchNotices(currentPage - 1)"
                >
                  Previous
                </button>
                <button
                  class="btn btn-sm btn-secondary"
                  :disabled="currentPage >= totalPages || loading"
                  @click="fetchNotices(currentPage + 1)"
                >
                  Next
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- CREATE MODAL COMPONENT -->
    <NoticeCreate
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
                <h5 class="mb-0">Edit Notice</h5>
                <button type="button" class="btn btn-sm btn-light" @click="closeEditModal">
                  <i class="fa fa-times"></i>
                </button>
              </div>

              <form @submit.prevent="updateNotice">
                <div class="xbody">
                  <div class="row g-3">
                    <div class="col-12">
                      <label class="form-label">Notice Text <code class="text-danger">*</code></label>
                      <textarea
                        class="form-control"
                        rows="4"
                        placeholder="Enter notice text..."
                        v-model="edit.form.notice"
                      ></textarea>
                      <small v-if="errors.notice" class="text-danger d-block mt-1">{{ errors.notice }}</small>
                    </div>
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
        <div v-if="del.open" class="xmask" @click.self="closeDelete">
          <div class="xwrap">
            <div class="xbox xsmall" role="dialog" aria-modal="true">
              <div class="xhead d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-danger">Delete Notice</h5>
                <button type="button" class="btn btn-sm btn-light" @click="closeDelete">
                  <i class="fa fa-times"></i>
                </button>
              </div>

              <div class="xbody">
                <div class="alert alert-warning mb-0">
                  Are you sure you want to delete this notice (ID: <b>{{ del.item?.id }}</b>) ?
                </div>
              </div>

              <div class="xfoot d-flex justify-content-end gap-2">
                <button class="btn btn-outline-secondary" type="button" @click="closeDelete">Cancel</button>
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
import NoticeCreate from "../../components/createform/NoticeCreate.vue";

export default {
  name: "NoticeList",
  components: { NoticeCreate },

  data() {
    return {
      showCreateModal: false,
      notices: [],
      loading: false,
      search: "",
      perPage: 10,
      currentPage: 1,
      totalPages: 1,
      total: 0,
      from: 1,
      edit: {
        open: false,
        form: { id: null, notice: "" },
      },
      del: { open: false, item: null },
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
    this.fetchNotices(1);
  },

  watch: {
    search() {
      clearTimeout(this._t);
      this._t = setTimeout(() => this.fetchNotices(1), 300);
    },
    perPage() {
      this.fetchNotices(1);
    },
    showCreateModal() { this.syncBodyOverflow(); },
    "edit.open"()     { this.syncBodyOverflow(); },
    "del.open"()      { this.syncBodyOverflow(); },
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
        text: text || (type === "success" ? "Done ✅" : "Something went wrong ❌"),
        duration: 2500,
        close: true,
        gravity: "top",
        position: "right",
        backgroundColor: bg,
      }).showToast();
    },

    async fetchNotices(page = 1) {
      this.loading = true;
      try {
        const res = await axios.get(`${this.url}notice-get`, {
          params: { page, per_page: this.perPage, search: this.search },
        });
        this.notices     = res.data.data         || [];
        this.currentPage = res.data.current_page || 1;
        this.totalPages  = res.data.last_page    || 1;
        this.total       = res.data.total        || 0;
        this.from        = res.data.from         || 1;
      } catch (e) {
        console.error(e);
        this.toast("Failed to load notices", "error");
      } finally {
        this.loading = false;
      }
    },

    openCreateFromComponent() {
      this.showCreateModal = true;
    },

    async handleCreated() {
      this.showCreateModal = false;
      await this.fetchNotices(this.currentPage);
    },

    openEditModal(n) {
      this.errors = {};
      this.edit.form = {
        id:     n.id,
        notice: n.notice,
      };
      this.edit.open = true;
    },

    closeEditModal() {
      this.edit.open = false;
      this.errors = {};
    },

    async updateNotice() {
      this.savingEdit = true;
      try {
        const res = await axios.post(
          `${this.url}notice-update/${this.edit.form.id}`,
          { notice: this.edit.form.notice }
        );

        this.toast(res.data?.message || "Updated ✅", "success");
        this.closeEditModal();
        await this.fetchNotices(this.currentPage);
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

    openDeleteModal(n) {
      this.del.item = n;
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
        const res = await axios.delete(`${this.url}notice-delete/${this.del.item.id}`);
        this.toast(res.data?.message || "Deleted", "success");
        this.closeDelete();
        const willBeEmpty = this.notices.length === 1 && this.currentPage > 1;
        const page = willBeEmpty ? this.currentPage - 1 : this.currentPage;
        await this.fetchNotices(page);
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
.xmask {
  position: fixed;
  inset: 0;
  z-index: 20000;
  background: rgba(0,0,0,.55);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  overflow-y: auto;
}
.xwrap { width: 100%; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
.xbox {
  width: min(94vw, 630px);
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 18px 55px rgba(0,0,0,.25);
  border: 1px solid rgba(0,0,0,.06);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}
.xbox.xsmall { width: min(94vw, 520px); }
.xhead { padding: 14px 16px; border-bottom: 1px solid #eef2f7; background: #fff; }
.xbody { padding: 16px; max-height: calc(100vh - 190px); overflow: auto; }
.xfoot { padding: 12px 16px; border-top: 1px solid #eef2f7; background: #fff; }

.slide-fade-enter-active,
.slide-fade-leave-active { transition: all .18s ease; }
.slide-fade-enter-from,
.slide-fade-leave-to { opacity: 0; transform: translateY(10px) scale(.98); }
</style>
