<template>
  <Teleport to="body">
    <transition name="slide-fade">
      <div v-if="show" class="xmask" @click.self="emitClose">
        <div class="xwrap">
          <div class="xbox" role="dialog" aria-modal="true">

            <!-- Header -->
            <div class="xhead d-flex justify-content-between align-items-center">
              <h5 class="mb-0">Create Product</h5>
              <button type="button" class="btn btn-sm btn-light" @click="emitClose">
                <i class="fa fa-times"></i>
              </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit">
              <div class="xbody">
                <div class="row g-3">

                  <!-- Date -->
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">
                      Date <code class="req">*</code>
                    </label>
                    <input
                      type="date"
                      class="form-control"
                      :class="{ 'is-invalid': errors.date }"
                      v-model="form.date"/>
                    <small v-if="errors.date" class="text-danger d-block mt-1">
                      {{ errors.date }}
                    </small>
                  </div>

                  <!-- Brand -->
                <div class="col-md-6">
                  <label class="form-label fw-semibold">
                    Brand <code class="req">*</code>
                  </label>

                  <div class="input-group">
                    <select
                      class="form-select"
                      :class="{ 'is-invalid': errors.brand_id }"
                      v-model="form.brand_id"
                      @change="onBrandChange">

                      <option value="">Select Brand</option>
                      <option v-for="b in brands" :key="b.id" :value="b.id">
                        {{ b.name }}
                      </option>
                    </select>

                    <!-- PLUS BUTTON -->
                    <button
                      type="button"
                      class="btn btn-primary"
                      @click="openBrandModal">
                      <i class="fa fa-plus"></i>
                    </button>
                  </div>

                  <small v-if="errors.brand_id" class="text-danger d-block mt-1">
                    {{ errors.brand_id }}
                  </small>
                </div>

                  <!-- Brand Category (Brand select করলে load হবে) -->
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">
                      Brand Category <code class="req">*</code>
                    </label>
                    <select
                      class="form-select"
                      :class="{ 'is-invalid': errors.brand_category_id }"
                      v-model="form.brand_category_id"
                      :disabled="!form.brand_id || loadingCategories">
                      <option value="">
                        {{ loadingCategories ? 'Loading...' : 'Select Category' }}
                      </option>
                      <option v-for="c in categories" :key="c.id" :value="c.id">
                        {{ c.name }}
                      </option>
                    </select>
                    <small v-if="errors.brand_category_id" class="text-danger d-block mt-1">
                      {{ errors.brand_category_id }}
                    </small>
                    <small
                      v-if="form.brand_id && !loadingCategories && categories.length === 0"
                      class="text-muted d-block mt-1">
                      <i class="fa fa-info-circle me-1"></i> No category found for this brand.
                    </small>
                  </div>

                  <!-- Product Name -->
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">
                      Product Name <code class="req">*</code>
                    </label>
                    <input
                      type="text"
                      class="form-control"
                      :class="{ 'is-invalid': errors.name }"
                      v-model="form.name"
                      placeholder="Enter product name"
                    />
                    <small v-if="errors.name" class="text-danger d-block mt-1">
                      {{ errors.name }}
                    </small>
                  </div>

                  <!-- buy_price -->
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">
                      Buy price <code class="req">*</code>
                    </label>
                    <div class="input-group">
                      <span class="input-group-text">৳</span>
                      <input
                        type="number"
                        class="form-control"
                        :class="{ 'is-invalid': errors.buy_price }"
                        v-model.number="form.buy_price"
                        placeholder="0.00"
                        min="0"
                        step="0.01"
                      />
                    </div>
                    <small v-if="errors.buy_price" class="text-danger d-block mt-1">
                      {{ errors.buy_price }}
                    </small>
                  </div>
                <!-- sell price -->
               <!--    <div class="col-md-6">
                    <label class="form-label fw-semibold">
                      Sell price <code class="req">*</code>
                    </label>
                    <div class="input-group">
                      <span class="input-group-text">৳</span>
                      <input
                        type="number"
                        class="form-control"
                        :class="{ 'is-invalid': errors.sell_price }"
                        v-model.number="form.sell_price"
                        placeholder="0.00"
                        min="0"
                        step="0.01"
                      />
                    </div>
                  </div> -->
                </div>
              </div>

              <!-- Footer -->
              <div class="xfoot d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary" @click="emitClose">
                  Cancel
                </button>
                <button type="submit" class="btn btn-success" :disabled="saving">
                  <span v-if="saving">
                    <i class="fa fa-spinner fa-spin me-1"></i> Saving...
                  </span>
                  <span v-else>
                    <i class="fa fa-save me-1"></i> Submit
                  </span>
                </button>
              </div>
            </form>

          </div>
        </div>
      </div>
    </transition>
  </Teleport>
</template>

  <Teleport to="body">
      <transition name="slide-fade">
          <div v-if="edit.open" class="xmask" @click.self="closeEditModal">
          <div class="xwrap">
            <div class="xbox" role="dialog" aria-modal="true">
              <div class="xhead d-flex justify-content-between align-items-center">
                <h5 class="mb-0"> Create Barnd</h5>
                <button type="button" class="btn btn-sm btn-light" @click="closeEditModal">
                  <i class="fa fa-times"></i>
                </button>
              </div>

              <form @submit.prevent="updateproduct">
                <div class="xbody">
                  <div class="row g-3">
                    <!-- Date -->
                    <!-- Product Name -->
                    <div class="col-md-6">
                      <label class="form-label fw-semibold">brand <code class="req">*</code></label>
                      <input
                        type="text"
                        class="form-control"
                        :class="{ 'is-invalid': errors.name }"
                        v-model="form.name"
                        placeholder="Enter product name"/>
                      <small v-if="errors.name" class="text-danger d-block mt-1">{{ errors.name }}</small>
                    </div>
                  </div>
                </div>

                <div class="xfoot d-flex justify-content-end gap-2">
                  <button type="button" class="btn btn-outline-secondary" @click="closeModal">Cancel</button>
                  <button type="submit" class="btn btn-success" :disabled="saving">
                    <span v-if="saving"><i class="fa fa-spinner fa-spin me-1"></i> save...</span>
                    <span v-else><i class="ti ti-device-floppy me-1"></i> save</span>
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </transition>
  </Teleport>

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
      loadingCategories: false,
      saving: false,
      errors: {},
      form: {
        date:               new Date().toISOString().split("T")[0],
        brand_id:           "",
        brand_category_id:  "",
        name:               "",
        buy_price:              "",
        sell_price:              "",
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
      this.$emit("close");
    },

    resetForm() {
      this.errors     = {};
      this.categories = [];
      this.form = {
        date:              new Date().toISOString().split("T")[0],
        brand_id:          "",
        brand_category_id: "",
        name:              "",
        buy_price:             "",
        sell_price:             "",
      };
    },

    // All  Brand load 
    async loadBrands() {
      try {
        const res = await axios.get(`${this.url}get-select-brand`);
        this.brands = Array.isArray(res.data) ? res.data : (res.data.data || []);
      } catch {
        this.toast("Failed to load brands", "error");
      }
    },

    // Brand change  Brand  Category load 
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

    validate() {
      this.errors = {};
      if (!this.form.date)              this.errors.date              = "Date is required";
      if (!this.form.brand_id)          this.errors.brand_id          = "Brand is required";
      if (!this.form.brand_category_id) this.errors.brand_category_id = "Category is required";
      if (!this.form.name)              this.errors.name              = "Product name is required";
      if (!this.form.buy_price && this.form.buy_price !== 0)
        this.errors.buy_price             = "buy_price is required";


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
        fd.append("date",               this.form.date);
        fd.append("brand_id",           this.form.brand_id);
        fd.append("brand_category_id",  this.form.brand_category_id);
        fd.append("name",               this.form.name);
        fd.append("buy_price",              this.form.buy_price);
        fd.append("sell_price",              this.form.sell_price);

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
  position: fixed; inset: 0; z-index: 30000;
  background: rgba(0,0,0,0.55);
  display: flex; align-items: center; justify-content: center;
  padding: 16px; overflow-y: auto;
}
.xwrap {
  width: 100%; min-height: 100vh;
  display: flex; align-items: center; justify-content: center;
}
.xbox {
  width: min(96vw, 760px); background: #fff;
  border-radius: 16px; box-shadow: 0 18px 55px rgba(0,0,0,0.25);
  border: 1px solid rgba(0,0,0,0.06); overflow: hidden;
}
.xhead { padding: 16px 20px; border-bottom: 1px solid #eef2f7; background: #fff; }
.xbody { padding: 20px; max-height: calc(100vh - 190px); overflow: auto; }
.xfoot { padding: 14px 20px; border-top: 1px solid #eef2f7; background: #fff; }
.req { color: #dc3545; }
.form-control, .form-select {
  border-radius: 10px; border: 1px solid #d7dde5;
  padding: 10px 12px; box-shadow: none;
}
.form-control:focus, .form-select:focus {
  border-color: #6f63f6;
  box-shadow: 0 0 0 0.2rem rgba(111,99,246,0.12);
}
.slide-fade-enter-active, .slide-fade-leave-active { transition: all 0.18s ease; }
.slide-fade-enter-from, .slide-fade-leave-to { opacity: 0; transform: translateY(10px) scale(0.98); }
</style>
<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Backend\BrandCategory;
use App\Models\Backend\Brand;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller
{

    public function index(){

        return view('backend.inventory.brand');
    }


    public function getselectbrandCategory()
    {
        $brands = BrandCategory::select('id', 'name')
            ->orderBy('name', 'asc')
            ->get();
        return response()->json([
            'status' => 'success',
            'data'   => $brands,
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name',
            'brand_category_id' => 'required|integer|exists:brand_categories,id',
        ]);

        $brand = Brand::create([
            'name' => $request->name,
            'brand_category_id' => $request->brand_category_id,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Brand created successfully',
            'data'    => $brand,
        ]);
    }


public function getBrand(Request $request)
{
    $perPage = (int) $request->get('per_page', 10);
    $search = trim($request->get('search', ''));

    $query = Brand::with('category');

    if ($search !== '') {
        $query->where('name', 'like', "%{$search}%")
              ->orWhereHas('category', function ($q) use ($search) {
                  $q->where('name', 'like', "%{$search}%");
              });
    }

    $data = $query->latest()->paginate($perPage);

    return response()->json($data);
}

    public function update(Request $request ,$id)
{
    $brand = Brand::findOrFail($id);

    $request->validate([
        'name' => 'required|string|max:255|unique:brands,name,' . $id,
        'brand_category_id' => 'required|integer|exists:brand_categories,id',
    ]);

    $brand->update([
        'name' => $request->name,
        'brand_category_id' => $request->brand_category_id,
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Brand updated successfully.',
        'data' => $brand,
    ]);
}

    
   
   public function destroy($id)
{
    $brand = Brand::findOrFail($id);
    $brand->delete();

    return response()->json([
        'status' => true,
        'message' => 'Brand deleted successfully.',
    ]);
}




}
