<template>
  <Teleport to="body">
    <transition name="slide-fade">
     <div v-if="show && !brandModal.open && !categoryModal.open" class="xmask" @click.self="emitClose">
        <div class="xwrap">
          <div class="xbox" role="dialog" aria-modal="true">
            <div class="xhead d-flex justify-content-between align-items-center">
              <h5 class="mb-0">Create Product</h5>
              <button type="button" class="btn btn-sm btn-light" @click="emitClose">
                <i class="fa fa-times"></i>
              </button>
            </div>

            <form @submit.prevent="submit">
              <div class="xbody">
                <div class="row g-3">

                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Date <code class="req">*</code></label>
                    <input type="date" class="form-control" :class="{ 'is-invalid': errors.date }" v-model="form.date">
                    <small v-if="errors.date" class="text-danger d-block mt-1">{{ errors.date }}</small>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Brand <code class="req">*</code></label>
                    <div class="input-group">
                      <select class="form-select" :class="{ 'is-invalid': errors.brand_id }" v-model="form.brand_id">
                        <option value="">Select Brand</option>
                        <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
                      </select>

                      <button type="button" class="btn btn-primary" @click.stop="openBrandModal">
                        <i class="fa fa-plus"></i>
                      </button>
                    </div>
                    <small v-if="errors.brand_id" class="text-danger d-block mt-1">{{ errors.brand_id }}</small>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Category <code class="req">*</code></label>
                    <div class="input-group">
                      <select class="form-select" :class="{ 'is-invalid': errors.brand_category_id }" v-model="form.brand_category_id">
                        <option value="">Select Category</option>
                        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                      </select>

                      <button type="button" class="btn btn-primary" @click.stop="openCategoryModal">
                        <i class="fa fa-plus"></i>
                      </button>
                    </div>
                    <small v-if="errors.brand_category_id" class="text-danger d-block mt-1">{{ errors.brand_category_id }}</small>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Product Name <code class="req">*</code></label>
                    <input
                      type="text"
                      class="form-control"
                      :class="{ 'is-invalid': errors.name }"
                      v-model="form.name"
                      placeholder="Enter product name">
                    <small v-if="errors.name" class="text-danger d-block mt-1">{{ errors.name }}</small>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Buy Price <code class="req">*</code></label>
                    <div class="input-group">
                      <span class="input-group-text">৳</span>
                      <input
                        type="number"
                        class="form-control"
                        :class="{ 'is-invalid': errors.buy_price }"
                        v-model.number="form.buy_price"
                        placeholder="0.00"
                        min="0"
                        step="0.01">
                    </div>
                    <small v-if="errors.buy_price" class="text-danger d-block mt-1">{{ errors.buy_price }}</small>
                  </div>
                </div>
              </div>
              <div class="xfoot d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary" @click="emitClose">Cancel</button>
                <button type="submit" class="btn btn-success" :disabled="saving">
                  <span v-if="saving"><i class="fa fa-spinner fa-spin me-1"></i> Saving...</span>
                  <span v-else><i class="fa fa-save me-1"></i> Submit</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </transition>
  </Teleport>

  <!-- Brand Modal -->
  <Teleport to="body">
    <transition name="slide-fade">
      <div v-if="brandModal.open" class="xmask child-mask" @click.self="closeBrandModal">
        <div class="xwrap">
          <div class="xbox small-box">
            <div class="xhead d-flex justify-content-between align-items-center">
              <h5 class="mb-0">Create Brand</h5>
              <button type="button" class="btn btn-sm btn-light" @click="closeBrandModal">
                <i class="fa fa-times"></i>
              </button>
            </div>

            <form @submit.prevent="createBrand">
              <div class="xbody">
                <label class="form-label fw-semibold">Brand Name <code class="req">*</code></label>
                <input
                  type="text"
                  class="form-control"
                  :class="{ 'is-invalid': brandModal.errors.name }"
                  v-model="brandModal.name"
                  placeholder="Enter brand name">
                <small v-if="brandModal.errors.name" class="text-danger d-block mt-1">
                  {{ brandModal.errors.name }}
                </small>
              </div>

              <div class="xfoot d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary" @click="closeBrandModal">Cancel</button>
                <button type="submit" class="btn btn-success" :disabled="brandModal.saving">
                  <span v-if="brandModal.saving"><i class="fa fa-spinner fa-spin me-1"></i> Saving...</span>
                  <span v-else><i class="fa fa-save me-1"></i> Save</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </transition>
  </Teleport>

  <!-- Category Modal -->
  <Teleport to="body">
    <transition name="slide-fade">
      <div v-if="categoryModal.open" class="xmask child-mask" @click.self="closeCategoryModal">
        <div class="xwrap">
          <div class="xbox small-box">
            <div class="xhead d-flex justify-content-between align-items-center">
              <h5 class="mb-0">Create Category</h5>
              <button type="button" class="btn btn-sm btn-light" @click="closeCategoryModal">
                <i class="fa fa-times"></i>
              </button>
            </div>

            <form @submit.prevent="createCategory">
              <div class="xbody">
                <label class="form-label fw-semibold">Category Name <code class="req">*</code></label>
                <input
                  type="text"
                  class="form-control"
                  :class="{ 'is-invalid': categoryModal.errors.name }"
                  v-model="categoryModal.name"
                  placeholder="Enter category name">
                <small v-if="categoryModal.errors.name" class="text-danger d-block mt-1">
                  {{ categoryModal.errors.name }}
                </small>
              </div>

              <div class="xfoot d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary" @click="closeCategoryModal">Cancel</button>
                <button type="submit" class="btn btn-success" :disabled="categoryModal.saving">
                  <span v-if="categoryModal.saving"><i class="fa fa-spinner fa-spin me-1"></i> Saving...</span>
                  <span v-else><i class="fa fa-save me-1"></i> Save</span>
                </button>
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
  name: "ProductCreateModal",

  props: {
    show: { type: Boolean, default: false },
  },

  emits: ["close", "created"],

  data() {
    return {
      brands: [],
      categories: [],
      saving: false,
      errors: {},

      form: {
        date: new Date().toISOString().split("T")[0],
        brand_id: "",
        brand_category_id: "",
        name: "",
        buy_price: "",
        sell_price: "",
      },

      brandModal: {
        open: false,
        name: "",
        saving: false,
        errors: {},
      },

      categoryModal: {
        open: false,
        name: "",
        saving: false,
        errors: {},
      },
    };
  },

  computed: {
    url() {
      return this.$store.state.url;
    },
  },

  watch: {
    show(v) {
      document.body.style.overflow = v ? "hidden" : "";
      if (v) {
        this.resetForm();
        this.loadBrands();
        this.loadCategories();
      }
    },
  },

  beforeUnmount() {
    document.body.style.overflow = "";
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

    emitClose() {
      if (this.brandModal.open || this.categoryModal.open) return;
      this.$emit("close");
    },

    resetForm() {
      this.errors = {};
      this.form = {
        date: new Date().toISOString().split("T")[0],
        brand_id: "",
        brand_category_id: "",
        name: "",
        buy_price: "",
        sell_price: "",
      };
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
        this.toast("Failed to load category", "error");
      }
    },

 openBrandModal() {
  this.brandModal.open = true;
  this.brandModal.name = "";
  this.brandModal.errors = {};
},

    closeBrandModal() {
      this.brandModal.open = false;
      this.brandModal.name = "";
      this.brandModal.errors = {};
    },

    async createBrand() {
      this.brandModal.errors = {};

      if (!this.brandModal.name) {
        this.brandModal.errors.name = "Brand name is required";
        return;
      }

      this.brandModal.saving = true;

      try {
        const res = await axios.post(`${this.url}new-brand-create`, {
          name: this.brandModal.name,
        });

        const brand = res.data.data || res.data;

        this.brands.push(brand);
        this.form.brand_id = brand.id;

        this.toast(res.data.message || "Brand created successfully", "success");
        this.closeBrandModal();
      } catch (e) {
        const data = e?.response?.data;

        if (e?.response?.status === 422 && data?.errors) {
          Object.keys(data.errors).forEach((k) => {
            this.brandModal.errors[k] = Array.isArray(data.errors[k])
              ? data.errors[k][0]
              : data.errors[k];
          });
        }

        this.toast(data?.message || "Brand create failed", "error");
      } finally {
        this.brandModal.saving = false;
      }
    },

    openCategoryModal() {
      this.categoryModal.open = true;
      this.categoryModal.name = "";
      this.categoryModal.errors = {};
    },

    closeCategoryModal() {
      this.categoryModal.open = false;
      this.categoryModal.name = "";
      this.categoryModal.errors = {};
    },

    async createCategory() {
      this.categoryModal.errors = {};

      if (!this.categoryModal.name) {
        this.categoryModal.errors.name = "Category name is required";
        return;
      }

      this.categoryModal.saving = true;

      try {
        const res = await axios.post(`${this.url}brand-category-create`, {
          name: this.categoryModal.name,
        });

        const category = res.data.data || res.data;

        this.categories.push(category);
        this.form.brand_category_id = category.id;

        this.toast(res.data.message || "Category created successfully", "success");
        this.closeCategoryModal();
      } catch (e) {
        const data = e?.response?.data;

        if (e?.response?.status === 422 && data?.errors) {
          Object.keys(data.errors).forEach((k) => {
            this.categoryModal.errors[k] = Array.isArray(data.errors[k])
              ? data.errors[k][0]
              : data.errors[k];
          });
        }

        this.toast(data?.message || "Category create failed", "error");
      } finally {
        this.categoryModal.saving = false;
      }
    },

    validate() {
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

    async submit() {
      if (!this.validate()) {
        this.toast("Please check required fields", "warning");
        return;
      }

      this.saving = true;

      try {
        const fd = new FormData();
        fd.append("date", this.form.date);
        fd.append("brand_id", this.form.brand_id);
        fd.append("brand_category_id", this.form.brand_category_id);
        fd.append("name", this.form.name);
        fd.append("buy_price", this.form.buy_price);
        fd.append("sell_price", this.form.sell_price || "");

        const res = await axios.post(`${this.url}product-store`, fd);

        this.toast(res.data?.message || "Product created successfully", "success");
        this.$emit("created");
        this.emitClose();
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
          this.toast(data?.message || "Create failed", "error");
        }
      } finally {
        this.saving = false;
      }
    },
  },
};
</script>

<style scoped>
.xmask {
  position: fixed;
  inset: 0;
  z-index: 30000;
  background: rgba(0,0,0,0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  overflow-y: auto;
}

.child-mask {
  z-index: 31000;
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
  border-radius: 16px;
  box-shadow: 0 18px 55px rgba(0,0,0,0.25);
  border: 1px solid rgba(0,0,0,0.06);
  overflow: hidden;
}

.small-box {
  width: min(96vw, 480px);
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
  border: 1px solid #d7dde5;
  padding: 10px 12px;
  box-shadow: none;
}

.form-control {
  border-radius: 10px;
}

.form-select {
  border-radius: 10px 0 0 10px;
}

.input-group .btn {
  border-radius: 0 10px 10px 0;
}

.form-control:focus,
.form-select:focus {
  border-color: #6f63f6;
  box-shadow: 0 0 0 0.2rem rgba(111,99,246,0.12);
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