<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="card mb-4 shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center py-3">
        <h5 class="card-title mb-0">Product List</h5>
        <button class="btn btn-primary" type="button" @click="openCreateFromComponent">
          <i class="ti ti-plus me-1"></i> Add Product
        </button>
      </div>

      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <select class="form-select form-select-sm" style="width:90px" v-model.number="perPage">
            <option :value="5">5</option>
            <option :value="10">10</option>
            <option :value="20">20</option>
            <option :value="50">50</option>
          </select>

          <input
            type="text"
            class="form-control form-control-sm"
            style="width:240px"
            placeholder="Search by name..."
            v-model="search"
          />
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
                <th>Action</th>
              </tr>
            </thead>

            <tbody v-if="products.length">
              <tr v-for="(s, idx) in products" :key="s.id">
                <td>{{ from + idx + 1 }}</td>
                <td>{{ s.date || "-" }}</td>
                <td>{{ s.brand?.name || "-" }}</td>
                <td>{{ s.brand_category?.name || "-" }}</td>
                <td>{{ s.name || "-" }}</td>
                <td>৳ {{ s.buy_price || "-" }}</td>
                <td>
                  <button class="btn btn-sm btn-primary me-1" @click="openEditModal(s)">
                    <i class="ti ti-edit"></i>
                  </button>
                  <button class="btn btn-sm btn-danger" @click="openDeleteModal(s)">
                    <i class="ti ti-trash"></i>
                  </button>
                </td>
              </tr>
            </tbody>

            <tbody v-else>
              <tr>
                <td colspan="7" class="text-center py-4">
                  <span v-if="loading"><i class="fa fa-spinner fa-spin"></i> Loading...</span>
                  <span v-else>No product found</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
          <div class="small text-muted">
            Total: {{ total }} | Page: {{ currentPage }} / {{ totalPages }}
          </div>

          <div>
            <button class="btn btn-sm btn-secondary me-1" :disabled="currentPage <= 1" @click="getproduct(currentPage - 1)">
              Previous
            </button>
            <button class="btn btn-sm btn-secondary" :disabled="currentPage >= totalPages" @click="getproduct(currentPage + 1)">
              Next
            </button>
          </div>
        </div>
      </div>
    </div>

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
            <div class="xbox">
              <div class="xhead d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Edit Product</h5>
                <button type="button" class="btn btn-sm btn-light" @click="closeEditModal">
                  <i class="fa fa-times"></i>
                </button>
              </div>

              <form @submit.prevent="updateproduct">
                <div class="xbody">
                  <div class="row g-3">

                    <div class="col-md-6">
                      <label class="form-label fw-semibold">Date <code class="req">*</code></label>
                      <input type="date" class="form-control" v-model="form.date">
                      <small v-if="errors.date" class="text-danger">{{ errors.date }}</small>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label fw-semibold">Select Brand <code class="req">*</code></label>
                      <select class="form-select" v-model="form.brand_id" @change="onBrandChange">
                        <option value="">Select Brand</option>
                        <option v-for="b in brands" :key="b.id" :value="b.id">
                          {{ b.name }}
                        </option>
                      </select>
                      <small v-if="errors.brand_id" class="text-danger">{{ errors.brand_id }}</small>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label fw-semibold">Update Brand Name</label>
                      <input
                        type="text"
                        class="form-control"
                        v-model="form.brand_name"
                        placeholder="Brand name">
                      <small class="text-muted">এই input update করলে selected brand table-এর name update হবে</small>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label fw-semibold">Select Category <code class="req">*</code></label>
                      <select class="form-select" v-model="form.brand_category_id" @change="onCategoryChange">
                        <option value="">Select Category</option>
                        <option v-for="c in categories" :key="c.id" :value="c.id">
                          {{ c.name }}
                        </option>
                      </select>
                      <small v-if="errors.brand_category_id" class="text-danger">{{ errors.brand_category_id }}</small>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label fw-semibold">Update Category Name</label>
                      <input
                        type="text"
                        class="form-control"
                        v-model="form.brand_category_name"
                        placeholder="Category name">
                      <small class="text-muted">এই input update করলে selected category table-এর name update হবে</small>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label fw-semibold">Product Name <code class="req">*</code></label>
                      <input type="text" class="form-control" v-model="form.name">
                      <small v-if="errors.name" class="text-danger">{{ errors.name }}</small>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label fw-semibold">Buy Price <code class="req">*</code></label>
                      <div class="input-group">
                        <span class="input-group-text">৳</span>
                        <input type="number" class="form-control" v-model.number="form.buy_price" min="0" step="0.01">
                      </div>
                      <small v-if="errors.buy_price" class="text-danger">{{ errors.buy_price }}</small>
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
            <div class="xbox xsmall">
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
      products: [],
      brands: [],
      categories: [],
      loading: false,
      search: "",
      perPage: 10,
      currentPage: 1,
      totalPages: 1,
      total: 0,
      from: 0,

      edit: { open: false },
      del: { open: false, item: null },

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
    this.getproduct(1);
    this.loadBrands();
    this.loadCategories();
  },

  watch: {
    search() {
      clearTimeout(this._t);
      this._t = setTimeout(() => this.getproduct(1), 300);
    },

    perPage() {
      this.getproduct(1);
    },
  },

  methods: {
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

    async getproduct(page = 1) {
      this.loading = true;

      try {
        const res = await axios.get(`${this.url}get-product`, {
          params: {
            page: page,
            per_page: this.perPage,
            search: this.search,
          },
        });

        this.products = res.data.data || [];
        this.currentPage = res.data.current_page || 1;
        this.totalPages = res.data.last_page || 1;
        this.total = res.data.total || 0;
        this.from = res.data.from ? res.data.from - 1 : 0;
      } catch (e) {
        this.toast(e?.response?.data?.message || "Failed to load products", "error");
      } finally {
        this.loading = false;
      }
    },

    async loadBrands() {
      try {
        const res = await axios.get(`${this.url}get-select-brand`);
        this.brands = Array.isArray(res.data) ? res.data : res.data.data || [];
      } catch {
        this.toast("Failed to load brands", "error");
      }
    },

    async loadCategories() {
      try {
        const res = await axios.get(`${this.url}select-brand-category`);
        this.categories = Array.isArray(res.data) ? res.data : res.data.data || [];
      } catch {
        this.toast("Failed to load categories", "error");
      }
    },

    onBrandChange() {
      const selectedBrand = this.brands.find(
        (b) => String(b.id) === String(this.form.brand_id)
      );

      this.form.brand_name = selectedBrand ? selectedBrand.name : "";
    },

    onCategoryChange() {
      const selectedCategory = this.categories.find(
        (c) => String(c.id) === String(this.form.brand_category_id)
      );

      this.form.brand_category_name = selectedCategory ? selectedCategory.name : "";
    },

    openCreateFromComponent() {
      this.showCreateModal = true;
    },

    async handleCreated() {
      this.showCreateModal = false;
      await this.getproduct(this.currentPage);
      await this.loadBrands();
      await this.loadCategories();
    },

    async openEditModal(s) {
      this.errors = {};

      await this.loadBrands();
      await this.loadCategories();

      this.form = {
        id: s.id,
        date: s.date || "",
        brand_id: s.brand_id || "",
        brand_name: s.brand?.name || "",
        brand_category_id: s.brand_category_id || "",
        brand_category_name: s.brand_category?.name || "",
        name: s.name || "",
        buy_price: s.buy_price || "",
        sell_price: s.sell_price || "",
      };

      this.edit.open = true;
    },

    closeEditModal() {
      this.edit.open = false;
      this.errors = {};

      this.form = {
        id: null,
        date: "",
        brand_id: "",
        brand_name: "",
        brand_category_id: "",
        brand_category_name: "",
        name: "",
        buy_price: "",
        sell_price: "",
      };
    },

    validateUpdate() {
      this.errors = {};

      if (!this.form.date) this.errors.date = "Date is required";
      if (!this.form.brand_id) this.errors.brand_id = "Brand is required";
      if (!this.form.brand_category_id) this.errors.brand_category_id = "Category is required";
      if (!this.form.name) this.errors.name = "Product name is required";
      if (!this.form.buy_price && this.form.buy_price !== 0) {
        this.errors.buy_price = "Buy price is required";
      }

      return Object.keys(this.errors).length === 0;
    },

    async updateproduct() {
      if (!this.validateUpdate()) {
        this.toast("Please check required fields", "warning");
        return;
      }

      this.savingEdit = true;

      try {
        const fd = new FormData();

        fd.append("date", this.form.date);
        fd.append("brand_id", this.form.brand_id);
        fd.append("brand_name", this.form.brand_name);
        fd.append("brand_category_id", this.form.brand_category_id);
        fd.append("brand_category_name", this.form.brand_category_name);
        fd.append("name", this.form.name);
        fd.append("buy_price", this.form.buy_price);
        fd.append("sell_price", this.form.sell_price || "");

        const res = await axios.post(
          `${this.url}product-update-data/${this.form.id}`,
          fd
        );

        this.toast(res.data?.message || "Updated successfully", "success");

        this.closeEditModal();

        await this.loadBrands();
        await this.loadCategories();
        await this.getproduct(this.currentPage);
      } catch (e) {
        const data = e?.response?.data;

        if (e?.response?.status === 422 && data?.errors) {
          Object.keys(data.errors).forEach((k) => {
            this.errors[k] = Array.isArray(data.errors[k])
              ? data.errors[k][0]
              : data.errors[k];
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
        const res = await axios.delete(`${this.url}product-delete/${this.del.item.id}`);

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