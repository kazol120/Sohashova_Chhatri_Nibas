<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-10 col-xl-12">
        <div class="card mb-4 shadow-sm">
          <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3">
            <div>
              <h5 class="card-title mb-0">Residence Overview</h5>
              <small class="text-muted">Manage homepage overview section info and images</small>
            </div>
            <button class="btn btn-primary" type="button" @click="openCreateFromComponent">
              <i class="ti ti-plus me-1"></i> Add Overview
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
                  placeholder="Search by title..."
                  v-model="search"
                  @keyup.enter="fetchOverviews(1)"
                />
              </div>
            </div>

            <div class="table-responsive">
              <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th style="width:160px">Title</th>
                    <th>Description</th>
                    <th style="width:180px">Back Image</th>
                    <th style="width:180px">Front Image</th>
                    <th style="width:140px">Actions</th>
                  </tr>
                </thead>

                <tbody v-if="overviews.length">
                  <tr v-for="(s, idx) in overviews" :key="s.id">
                    <td>
                      <div class="fw-semibold">{{ s.title || "-" }}</div>
                    </td>
                    <td>
                      <div class="text-truncate-2">{{ s.description || "-" }}</div>
                    </td>
                    <td>
                      <img v-if="s.img_back" :src="backImageSrc(s.img_back)" class="img-thumb" alt="back" />
                      <span v-else class="text-muted small">No image</span>
                    </td>
                    <td>
                      <img v-if="s.img_front" :src="frontImageSrc(s.img_front)" class="img-thumb" alt="front" />
                      <span v-else class="text-muted small">No image</span>
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
                    <td colspan="5" class="text-center py-4 text-muted">
                      <span v-if="loading"><i class="fa fa-spinner fa-spin me-2"></i>Loading...</span>
                      <span v-else>No overviews found</span>
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
                  @click="fetchOverviews(currentPage - 1)"
                >
                  Previous
                </button>
                <button
                  class="btn btn-sm btn-secondary"
                  :disabled="currentPage >= totalPages || loading"
                  @click="fetchOverviews(currentPage + 1)"
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
    <ResidenceOverviewModal
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
                  <h5 class="mb-0">Edit Residence Overview</h5>
                  <small class="text-muted">Update title, description and images</small>
                </div>
                <button type="button" class="btn btn-sm btn-light" @click="closeEditModal">
                  <i class="fa fa-times"></i>
                </button>
              </div>

              <form @submit.prevent="updateOverview">
                <div class="xbody">
                  <div class="row g-3">
                    <div class="col-12">
                      <label class="form-label fw-semibold">
                        Title <code class="req">*</code>
                      </label>
                      <input
                        type="text"
                        class="form-control"
                        v-model="edit.form.title"
                        placeholder="Enter overview title"
                      />
                      <small v-if="errors.title" class="text-danger d-block mt-1">{{ errors.title }}</small>
                    </div>

                    <div class="col-12">
                      <label class="form-label fw-semibold">Description</label>
                      <textarea
                        class="form-control"
                        rows="4"
                        v-model="edit.form.description"
                        placeholder="Write short description..."
                      ></textarea>
                      <small v-if="errors.description" class="text-danger d-block mt-1">{{ errors.description }}</small>
                    </div>

                    <!-- Back Image -->
                    <div class="col-md-6">
                      <label class="form-label fw-semibold">
                        Back Image <small class="text-muted">(optional)</small>
                      </label>
                      <div class="upload-card-wrapper">
                        <label class="upload-card" for="edit_img_back_input">
                          <input
                            id="edit_img_back_input"
                            type="file"
                            class="d-none"
                            accept="image/*"
                            @change="onEditImageChange($event, 'img_back')"
                          />
                          <template v-if="edit.previewBack || edit.form.old_back_url">
                            <img
                              :src="edit.previewBack || edit.form.old_back_url"
                              class="upload-preview-img"
                              alt="Back Preview"
                            />
                            <div class="upload-overlay">
                              <span class="change-btn">
                                <i class="fa fa-camera me-1"></i> Change Photo
                              </span>
                            </div>
                          </template>
                          <template v-else>
                            <div class="upload-placeholder-content">
                              <div class="icon-circle mb-2">
                                <i class="fa fa-cloud-upload-alt text-primary fs-4"></i>
                              </div>
                              <span class="fw-bold text-dark">Upload Photo</span>
                              <small class="text-muted">Click to browse files</small>
                            </div>
                          </template>
                        </label>
                      </div>
                      <small v-if="errors.img_back" class="text-danger d-block mt-1">{{ errors.img_back }}</small>
                    </div>

                    <!-- Front Image -->
                    <div class="col-md-6">
                      <label class="form-label fw-semibold">
                        Front Image <small class="text-muted">(optional)</small>
                      </label>
                      <div class="upload-card-wrapper">
                        <label class="upload-card" for="edit_img_front_input">
                          <input
                            id="edit_img_front_input"
                            type="file"
                            class="d-none"
                            accept="image/*"
                            @change="onEditImageChange($event, 'img_front')"
                          />
                          <template v-if="edit.previewFront || edit.form.old_front_url">
                            <img
                              :src="edit.previewFront || edit.form.old_front_url"
                              class="upload-preview-img"
                              alt="Front Preview"
                            />
                            <div class="upload-overlay">
                              <span class="change-btn">
                                <i class="fa fa-camera me-1"></i> Change Photo
                              </span>
                            </div>
                          </template>
                          <template v-else>
                            <div class="upload-placeholder-content">
                              <div class="icon-circle mb-2">
                                <i class="fa fa-cloud-upload-alt text-primary fs-4"></i>
                              </div>
                              <span class="fw-bold text-dark">Upload Photo</span>
                              <small class="text-muted">Click to browse files</small>
                            </div>
                          </template>
                        </label>
                      </div>
                      <small v-if="errors.img_front" class="text-danger d-block mt-1">{{ errors.img_front }}</small>
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
                <h5 class="mb-0 text-danger">Delete Residence Overview</h5>
                <button type="button" class="btn btn-sm btn-light" @click="closeDelete">
                  <i class="fa fa-times"></i>
                </button>
              </div>
              <div class="xbody">
                <div class="alert alert-warning mb-0">
                  Are you sure you want to delete this overview:
                  <b>{{ del.item?.title }}</b> ?
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
import ResidenceOverviewModal from "../../components/createform/ResidenceOverviewModal.vue";

export default {
  name: "ResidenceOverviewList",
  components: { ResidenceOverviewModal },

  data() {
    return {
      showCreateModal: false,
      overviews: [],
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
          title: "",
          description: "",
          img_back: null,
          img_front: null,
          old_back_url: "",
          old_front_url: "",
        },
        previewBack: "",
        previewFront: "",
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
    this.fetchOverviews(1);
  },

  watch: {
    search() {
      clearTimeout(this._t);
      this._t = setTimeout(() => this.fetchOverviews(1), 300);
    },
    perPage() {
      this.fetchOverviews(1);
    },
    showCreateModal() { this.syncBodyOverflow(); },
    "edit.open"()     { this.syncBodyOverflow(); },
    "del.open"()      { this.syncBodyOverflow(); },
  },

  beforeUnmount() {
    clearTimeout(this._t);
    this.cleanEditPreview();
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

    backImageSrc(path) {
      if (!path) return "";
      return `${this.url}residence_back_image/${path}`;
    },

    frontImageSrc(path) {
      if (!path) return "";
      return `${this.url}residence_front_image/${path}`;
    },

    async fetchOverviews(page = 1) {
      this.loading = true;
      try {
        const res = await axios.get(`${this.url}residence-overview-get`, {
          params: { page, per_page: this.perPage, search: this.search },
        });
        this.overviews    = res.data.data         || [];
        this.currentPage  = res.data.current_page || 1;
        this.totalPages   = res.data.last_page    || 1;
        this.total        = res.data.total        || 0;
        this.from         = res.data.from         || 1;
      } catch (e) {
        console.error(e);
        this.toast("Failed to load overviews", "error");
      } finally {
        this.loading = false;
      }
    },

    openCreateFromComponent() {
      this.showCreateModal = true;
    },

    async handleCreated() {
      this.showCreateModal = false;
      await this.fetchOverviews(this.currentPage);
    },

    openEditModal(s) {
      this.errors = {};
      this.cleanEditPreview();
      this.edit.form = {
        id:            s.id,
        title:         s.title || "",
        description:   s.description || "",
        img_back:      null,
        img_front:     null,
        old_back_url:  s.img_back ? this.backImageSrc(s.img_back) : "",
        old_front_url: s.img_front ? this.frontImageSrc(s.img_front) : "",
      };
      this.edit.open = true;
    },

    closeEditModal() {
      this.edit.open = false;
      this.errors    = {};
      this.cleanEditPreview();
    },

    cleanEditPreview() {
      if (this.edit.previewBack) URL.revokeObjectURL(this.edit.previewBack);
      if (this.edit.previewFront) URL.revokeObjectURL(this.edit.previewFront);
      this.edit.previewBack = "";
      this.edit.previewFront = "";
    },

    onEditImageChange(e, type) {
      const file = e.target?.files?.[0] || null;
      if (type === "img_back") {
        this.edit.form.img_back = file;
        this.errors.img_back = null;
        if (this.edit.previewBack) URL.revokeObjectURL(this.edit.previewBack);
        this.edit.previewBack = file ? URL.createObjectURL(file) : "";
      } else {
        this.edit.form.img_front = file;
        this.errors.img_front = null;
        if (this.edit.previewFront) URL.revokeObjectURL(this.edit.previewFront);
        this.edit.previewFront = file ? URL.createObjectURL(file) : "";
      }
    },

    async updateOverview() {
      this.savingEdit = true;
      try {
        const fd = new FormData();
        fd.append("title", this.edit.form.title);
        fd.append("description", this.edit.form.description);
        if (this.edit.form.img_back) fd.append("img_back", this.edit.form.img_back);
        if (this.edit.form.img_front) fd.append("img_front", this.edit.form.img_front);

        const res = await axios.post(
          `${this.url}residence-overview-update/${this.edit.form.id}`,
          fd,
          { headers: { "Content-Type": "multipart/form-data" } }
        );

        this.toast(res.data?.message || "Updated successfully.", "success");
        this.closeEditModal();
        await this.fetchOverviews(this.currentPage);
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
        const res = await axios.delete(`${this.url}residence-overview-delete/${this.del.item.id}`);
        this.toast(res.data?.message || "Deleted ✅", "success");
        this.closeDelete();
        const willBeEmpty = this.overviews.length === 1 && this.currentPage > 1;
        const page = willBeEmpty ? this.currentPage - 1 : this.currentPage;
        await this.fetchOverviews(page);
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
  width: 90px;
  height: 60px;
  object-fit: cover;
  border-radius: 10px;
  border: 1px solid #e5e7eb;
  background: #fff;
}

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

.upload-card-wrapper {
  width: 100%;
}
.upload-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border: 2px dashed #d1d5db;
  border-radius: 12px;
  padding: 20px;
  background: #f9fafb;
  cursor: pointer;
  height: 160px;
  position: relative;
  overflow: hidden;
  transition: border-color 0.2s;
}
.upload-card:hover {
  border-color: #3b82f6;
}
.upload-preview-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  position: absolute;
  inset: 0;
}
.upload-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.2s;
}
.upload-card:hover .upload-overlay {
  opacity: 1;
}
.change-btn {
  color: #fff;
  font-weight: 600;
  font-size: 0.875rem;
}
.upload-placeholder-content {
  text-align: center;
}
.icon-circle {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: #eff6ff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.text-truncate-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
}

.slide-fade-enter-active,
.slide-fade-leave-active { transition: all .18s ease; }
.slide-fade-enter-from,
.slide-fade-leave-to { opacity: 0; transform: translateY(10px) scale(.98); }
</style>
