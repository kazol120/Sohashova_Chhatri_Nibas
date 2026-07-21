<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-10 col-xl-12">
        <div class="card mb-4 shadow-sm">

          <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3">
            <h5 class="card-title mb-0">Product List</h5>
            <button class="btn btn-primary" type="button" @click="openCreateFromComponent">
              <i class="ti ti-plus me-1"></i> Add Product
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
                  @keyup.enter="getproduct(1)"
                />
              </div>
            </div>

            <div class="table-responsive">
              <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th>Sl</th>
                    <th>Date</th>
                    <th>Brand</th>
                    <th>Category</th>
                    <th>Name</th>
                    <th>Buy Price</th>
                    <!-- <th>Sell Price</th> -->
                    <th>Action</th>
                  </tr>
                </thead>

                <tbody v-if="products.length">
                  <tr v-for="(s, idx) in products" :key="s.id">
                    <td class="fw-semibold">{{ from + idx + 1 }}</td>
                    <td class="fw-semibold">{{ s.date || "-" }}</td>
                    <td class="fw-semibold">{{ s.brand?.name || "-" }}</td>
                    <td class="fw-semibold">{{ s.brand_category?.name || "-" }}</td>
                    <td class="fw-semibold">{{ s.name || "-" }}</td>
                    <td class="fw-semibold">৳ {{ s.buy_price || "-" }}</td>
                    <!-- <td class="fw-semibold">৳ {{ s.sell_price || "-" }}</td> -->
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
                    <td colspan="8" class="text-center py-4 text-muted">
                      <span v-if="loading">
                        <i class="fa fa-spinner fa-spin me-2"></i>Loading...
                      </span>
                      <span v-else>No product found</span>
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
                  @click="getproduct(currentPage - 1)"
                >Previous</button>
                <button
                  class="btn btn-sm btn-secondary"
                  :disabled="currentPage >= totalPages || loading"
                  @click="getproduct(currentPage + 1)"
                >Next</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- CREATE MODAL -->
    <ProductCreateForm
      :show="showCreateModal"
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
                <h5 class="mb-0">Edit Product</h5>
                <button type="button" class="btn btn-sm btn-light" @click="closeEditModal">
                  <i class="fa fa-times"></i>
                </button>
              </div>

              <form @submit.prevent="updateproduct">
                <div class="xbody">
                  <div class="row g-3">
                    <!-- Date -->
                    <div class="col-md-6">
                      <label class="form-label fw-semibold">Date <code class="req">*</code></label>
                      <input
                        type="date"
                        class="form-control"
                        :class="{ 'is-invalid': errors.date }"
                        v-model="form.date"
                      />
                      <small v-if="errors.date" class="text-danger d-block mt-1">{{ errors.date }}</small>
                    </div>
                  <!-- Brand Name Update -->
                    <div class="col-md-6">
                      <label class="form-label fw-semibold">
                        Brand Name
                      </label>
                      <input
                        type="text"
                        class="form-control"
                        v-model="form.brand_name"
                        placeholder="Enter brand name"
                      />
                    </div>

                    <!-- Category Name Update -->
                    <div class="col-md-6">
                      <label class="form-label fw-semibold">
                        Category Name
                      </label>
                      <input
                        type="text"
                        class="form-control"
                        v-model="form.brand_category_name"
                        placeholder="Enter category name"
                      />
                    </div>
                    <!-- Product Name -->
                    <div class="col-md-6">
                      <label class="form-label fw-semibold">Product Name <code class="req">*</code></label>
                      <input
                        type="text"
                        class="form-control"
                        :class="{ 'is-invalid': errors.name }"
                        v-model="form.name"
                        placeholder="Enter product name"/>
                      <small v-if="errors.name" class="text-danger d-block mt-1">{{ errors.name }}</small>
                    </div>

                    
                    <!-- Buy Price -->
                    <div class="col-md-6">
                      <label class="form-label fw-semibold">Buy Price <code class="req">*</code></label>
                      <div class="input-group">
                        <span class="input-group-text">৳</span>
                        <input
                          type="number"
                          class="form-control"
                          :class="{ 'is-invalid': errors.buy_price }"
                          v-model.number="form.buy_price"
                          placeholder="0.00" min="0" step="0.01"
                        />
                      </div>
                      <small v-if="errors.buy_price" class="text-danger d-block mt-1">{{ errors.buy_price }}</small>
                    </div>
              
                 <!--<div class="col-md-6">
                      <label class="form-label fw-semibold">Sell Price <code class="req">*</code></label>
                      <div class="input-group">
                        <span class="input-group-text">৳</span>
                        <input
                          type="number"
                          class="form-control"
                          :class="{ 'is-invalid': errors.sell_price }"
                          v-model.number="form.sell_price"
                          placeholder="0.00" min="0" step="0.01"
                        />
                      </div>
                    </div> -->

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
                <h5 class="mb-0 text-danger">Delete Product</h5>
                <button type="button" class="btn btn-sm btn-light" @click="closeDelete">
                  <i class="fa fa-times"></i>
                </button>
              </div>
              <div class="xbody">
                <div class="alert alert-warning mb-0">
                  Are you sure you want to delete <b>{{ del.item?.name }}</b>?
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
import ProductCreateForm from "../../components/createform/ProductCreateForm.vue";

export default {
  name: "ProductList",
  components: { ProductCreateForm },

  data() {
    return {
      showCreateModal: false,
      products:        [],
      brands:          [],
      categories:      [],
      loadingCategories: false,
      loading:         false,
      search:          "",
      perPage:         10,
      currentPage:     1,
      totalPages:      1,
      total:           0,
      from:            0,
      edit:            { open: false },
      del:             { open: false, item: null },

      // ─── Edit form (top-level) ───
      form: {
        id: null,
        date: "",
        brand_id: "",
        brand_name: "",
        brand_category_id: "",
        brand_category_name: "",
        name: "",
        buy_price: "",
        sell_price: "",
      },

      savingEdit:   false,
      savingDelete: false,
      errors:       {},
      _t:           null,
    };
  },

  computed: {
    url() { return this.$store.state.url; },
  },

  mounted() {
    this.getproduct(1);
    this.loadBrands();
  },

  watch: {
    search() {
      clearTimeout(this._t);
      this._t = setTimeout(() => this.getproduct(1), 300);
    },
    perPage()           { this.getproduct(1); },
    showCreateModal()   { this.syncBodyOverflow(); },
    "edit.open"()       { this.syncBodyOverflow(); },
    "del.open"()        { this.syncBodyOverflow(); },
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
        type === "success" ? "linear-gradient(to right, #00b09b, #96c93d)"
        : type === "warning" ? "linear-gradient(to right, #f59e0b, #fbbf24)"
        : "linear-gradient(to right, #ff5f6d, #ffc371)";
      Toastify({ text: text || "Something went wrong", duration: 2500, close: true,
        gravity: "top", position: "right", backgroundColor: bg }).showToast();
    },

    // ─── Product List ────────────────────────────────────────
    async getproduct(page = 1) {
      this.loading = true;
      try {
        const res = await axios.get(`${this.url}get-product`, {
          params: { page, per_page: this.perPage, search: this.search },
        });
        this.products    = res.data.data         || [];
        this.currentPage = res.data.current_page  || 1;
        this.totalPages  = res.data.last_page      || 1;
        this.total       = res.data.total          || 0;
        this.from        = res.data.from ? res.data.from - 1 : 0;
      } catch (e) {
        this.toast(e?.response?.data?.message || "Failed to load products", "error");
      } finally {
        this.loading = false;
      }
    },

    // ─── Brands dropdown ────────────────────────────────────
    async loadBrands() {
      try {
        const res = await axios.get(`${this.url}get-select-brand`);
        this.brands = Array.isArray(res.data) ? res.data : (res.data.data || []);
      } catch {
        this.toast("Failed to load brands", "error");
      }
    },

    // ─── Brand change → load categories ─────────────────────
    async onBrandChange() {
      this.form.brand_category_id = "";
      this.categories = [];
      if (!this.form.brand_id) return;

      this.loadingCategories = true;
      try {
        const res = await axios.get(
          `${this.url}select-brand-category/${this.form.brand_id}`
        );
        this.categories = Array.isArray(res.data) ? res.data : (res.data.data || []);
      } catch {
        this.toast("Failed to load categories", "error");
      } finally {
        this.loadingCategories = false;
      }
    },

    // ─── Create ──────────────────────────────────────────────
    openCreateFromComponent() { this.showCreateModal = true; },

    async handleCreated() {
      this.showCreateModal = false;
      await this.getproduct(this.currentPage);
    },

    // ─── Edit ────────────────────────────────────────────────
   async openEditModal(s) {
  this.errors = {};

  this.form = {
    id: s.id,
    date: s.date || "",

    // hidden id, select  brand hidden
    brand_id: s.brand_id || "",
    brand_category_id: s.brand_category_id || "",

    // input এ current name show 
    brand_name: s.brand?.name || "",
    brand_category_name: s.brand_category?.name || "",

    name: s.name || "",
    buy_price: s.buy_price || "",
    sell_price: s.sell_price || "",
  };

  this.edit.open = true;
},

    closeEditModal() {
      this.edit.open = false;
      this.errors    = {};
      this.form = {
        id: null, date: "", brand_id: "",
        brand_category_id: "", name: "", buy_price: "", sell_price: "",
      };
      this.categories = [];
    },

    async updateproduct() {
      this.errors    = {};
      this.savingEdit = true;
      try {
        const fd = new FormData();
        fd.append("date",              this.form.date);
        fd.append("brand_id", this.form.brand_id);
        fd.append("brand_name", this.form.brand_name);
        fd.append("brand_category_id", this.form.brand_category_id);
        fd.append("brand_category_name", this.form.brand_category_name);
        fd.append("name",              this.form.name);
        fd.append("buy_price",         this.form.buy_price);
        fd.append("sell_price",        this.form.sell_price);

        const res = await axios.post(
          `${this.url}product-update-data/${this.form.id}`, fd
        );

        this.toast(res.data?.message || "Updated successfully", "success");
        this.closeEditModal();
        await this.getproduct(this.currentPage);
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

    // ─── Delete ──────────────────────────────────────────────
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
          `${this.url}product-delete/${this.del.item.id}`
        );
        this.toast(res.data?.message || "Deleted successfully", "success");
        this.closeDelete();
        const willBeEmpty = this.products.length === 1 && this.currentPage > 1;
        await this.getproduct(willBeEmpty ? this.currentPage - 1 : this.currentPage);
      } catch (e) {
        this.toast(e?.response?.data?.message || "Delete failed", "error");
      } finally {
        this.savingDelete = false;
      }
    },
  },
};
</script>

<style scoped>

/* ── Backdrop ── */
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

/* ── Centering wrapper ── */
.xwrap {
  width: 100%;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* ── Modal box ── */
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

/* ── Header ── */
.xhead {
  padding: 16px 20px;
  border-bottom: 1px solid #eef2f7;
  background: #fff;
}

/* ── Body ── */
.xbody {
  padding: 20px;
  max-height: calc(100vh - 190px);
  overflow: auto;
}

/* ── Footer ── */
.xfoot {
  padding: 14px 20px;
  border-top: 1px solid #eef2f7;
  background: #fff;
}

/* ── Required star ── */
.req {
  color: #dc3545;
}

/* ── Inputs ── */
.form-control,
.form-select {
  border-radius: 10px;
  border: 1px solid #d7dde5;
  padding: 10px 12px;
  box-shadow: none;
}

.form-control:focus,
.form-select:focus {
  border-color: #6f63f6;
  box-shadow: 0 0 0 0.2rem rgba(111, 99, 246, 0.12);
}

/* ── Transition ── */
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