<template>
  <Teleport to="body">
    <transition name="slide-fade">
      <div v-if="show" class="xmask" @click.self="emitClose">
        <div class="xwrap">
          <div class="xbox" role="dialog" aria-modal="true">

            <!-- Header -->
            <div class="xhead d-flex justify-content-between align-items-center">
              <h5 class="mb-0"> Product Purchase </h5>
              <button type="button" class="btn btn-sm btn-light" @click="emitClose">
                <i class="fa fa-times"></i>
              </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit">
              <div class="xbody">
                <div class="row g-3">

                  <!-- Purchase Date -->
                  <div class="col-md-4">
                    <label class="form-label fw-semibold">
                      Date <code class="req">*</code>
                    </label>
                    <input
                      type="date"
                      class="form-control"
                      :class="{ 'is-invalid': errors.purchase_date }"
                      v-model="form.purchase_date"
                    />
                    <small v-if="errors.purchase_date" class="text-danger d-block mt-1">
                      {{ errors.purchase_date }}
                    </small>
                  </div>

                  <!-- Memo Number -->
                  <div class="col-md-4">
                    <label class="form-label fw-semibold">
                      Memo Number 
                    </label>
                    <input
                      type="text"
                      class="form-control"
                      :class="{ 'is-invalid': errors.memo_number }"
                      v-model="form.memo_number"
                      placeholder="Enter memo number"/>
                  </div>

                  <!-- Supplier -->
                  <div class="col-md-4">
                    <label class="form-label fw-semibold">
                      Supplier Name <code class="req">*</code>
                    </label>
                    <select
                      class="form-select"
                      :class="{ 'is-invalid': errors.supplier_id }"
                      v-model="form.supplier_id">
                      <option value="">Select Supplier</option>
                      <option v-for="s in suppliers" :key="s.id" :value="s.id">
                        {{ s.name }}
                      </option>
                    </select>
                    <small v-if="errors.supplier_id" class="text-danger d-block mt-1">
                      {{ errors.supplier_id }}
                    </small>
                  </div>

                  <!-- Product Select -->
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Product Name</label>
                    <select
                      class="form-select"
                      v-model="selectedProductId"
                      :disabled="loadingProducts"
                      @change="onProductSelect">
                      <option value="">
                        {{ loadingProducts ? 'Loading...' : 'Select Product to Add' }}
                      </option>
                      <option v-for="p in products" :key="p.id" :value="p.id">
                        {{ p.name }}
                      </option>
                    </select>
                  </div>
                </div>

                <!-- Product Purchase List Table -->
                <div v-if="productpurchaselist.length > 0" class="purchase-wrapper mt-4">
                  <div class="table-responsive">
                    <table class="table purchase-table align-middle">
                      <thead>
                        <tr>
                          <th style="width: 45px;">SN</th>
                          <th style="width: 160px;">PRODUCT</th>
                          <th style="width: 120px;">UNIT PRICE</th>
                          <th style="width: 110px;">QUANTITY</th>
                          <th style="width: 120px;">TOTAL</th>
                          <th style="width: 70px;">ACTION</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(item, index) in productpurchaselist" :key="index">
                          <td>{{ index + 1 }}</td>
                          <td class="text-uppercase fw-semibold">
                            {{ item.product_name }}
                          </td>
                          <!-- Unit Price -->
                          <td>
                            <input
                              type="number"
                              class="form-control purchase-input"
                              v-model.number="item.buy_price"
                              min="0"
                              @input="calculateRowTotal(item)"
                            />
                          </td>
                          <!-- Quantity -->
                          <td>
                            <input
                              type="number"
                              class="form-control purchase-input"
                              v-model.number="item.quantity"
                              min="1"
                              @input="calculateRowTotal(item)"
                            />
                          </td>
                          <!-- Discount -->
                      
                          <!-- Total -->
                          <td>
                            <input
                              type="text"
                              class="form-control purchase-input"
                              :value="item.total_price"
                              readonly
                            />
                          </td>
                          <!-- Delete -->
                          <td class="text-center">
                            <button
                              type="button"
                              class="btn btn-danger btn-sm delete-btn"
                              @click="removeRow(index)">
                              <i class="fa fa-trash"></i>
                            </button>
                          </td>
                        </tr>

                        <!-- Grand Total Row -->
                        <tr class="summary-row">
                          <td colspan="4" class="text-end fw-bold">GRAND TOTAL</td>
                          <td>
                            <input
                              type="text"
                              class="form-control purchase-input fw-bold"
                              :value="grandTotal"
                              readonly
                            />
                          </td>
                          <td></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center text-muted py-4 mt-3 border rounded">
                  <i class="fa fa-shopping-cart fa-2x mb-2 d-block"></i>
                  No product added yet. Select a product from above.
                </div>
              </div>
              <!-- Footer -->
              <div class="xfoot d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary" @click="emitClose">
                  Cancel
                </button>
                <button
                  type="submit"
                  class="btn btn-success"
                  :disabled="saving || productpurchaselist.length === 0">
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
      suppliers: [],
      products: [],
      loadingProducts: false,
      saving: false,
      errors: {},
      selectedProductId: "",
      productpurchaselist: [],
      form: {
        purchase_date: new Date().toISOString().split("T")[0],
        memo_number: "",
        supplier_id: "",
      },
    };
  },

  computed: {
    url() {
      return this.$store.state.url;
    },

    grandTotal() {
      return this.productpurchaselist
        .reduce((sum, item) => sum + (parseFloat(item.total_price) || 0), 0)
        .toFixed(2);
    },
  },

  watch: {
    show(v) {
      document.body.style.overflow = v ? "hidden" : "";
      if (v) {
        this.resetForm();
        this.loadSuppliers();
        this.loadProducts();
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
      this.errors = {};
      this.products = [];
      this.productpurchaselist = [];
      this.selectedProductId = "";
      this.form = {
        purchase_date: new Date().toISOString().split("T")[0],
        memo_number: "",
        supplier_id: "",
      };
    },

    async loadSuppliers() {
      try {
        const res = await axios.get(`${this.url}select-supplier`);
        this.suppliers = Array.isArray(res.data) ? res.data : (res.data.data || []);
      } catch {
        this.toast("Failed to load suppliers", "error");
      }
    },

    async loadProducts() {
      this.loadingProducts = true;
      try {
        const res = await axios.get(`${this.url}get-select-product`);
        this.products = Array.isArray(res.data) ? res.data : (res.data.data || []);
      } catch {
        this.toast("Failed to load products", "error");
      } finally {
        this.loadingProducts = false;
      }
    },

    onProductSelect() {
      if (!this.selectedProductId) return;

      const product = this.products.find(p => p.id == this.selectedProductId);
      if (!product) return;

      const exists = this.productpurchaselist.find(i => i.product_id == product.id);
      if (exists) {
        this.toast("Product already added in list", "warning");
        this.selectedProductId = "";
        return;
      }

      this.productpurchaselist.push({
        product_id:   product.id,
        product_name: product.name,
        buy_price:    product.buy_price || 0,
        quantity:     1,
        discount:     0,
        total_price:  product.buy_price || 0,
      });

      this.selectedProductId = "";
    },

    // buy_price × quantity - discount = total_price
    calculateRowTotal(item) {
      const price    = parseFloat(item.buy_price) || 0;
      const qty      = parseFloat(item.quantity)  || 0;
      const discount = parseFloat(item.discount)  || 0;
      const subtotal = price * qty;
      // discount  subtotal 
      item.total_price = Math.max(subtotal - discount, 0).toFixed(2);
    },

    removeRow(index) {
      this.productpurchaselist.splice(index, 1);
    },

    validate() {
      this.errors = {};
      if (!this.form.purchase_date) this.errors.purchase_date = "Date is required";
      if (!this.form.supplier_id)   this.errors.supplier_id   = "Supplier is required";
      if (this.productpurchaselist.length === 0) {
        this.toast("Please add at least one product", "warning");
        return false;
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
        const requests = this.productpurchaselist.map(item => {
          const fd = new FormData();
          fd.append("purchase_date",         this.form.purchase_date);
          fd.append("memo_number",           this.form.memo_number);
          fd.append("supplier_id",           this.form.supplier_id);
          fd.append("product_id",            item.product_id);
          fd.append("product_name",          item.product_name);
          fd.append("buy_price",             item.buy_price);
          fd.append("quantity",              item.quantity);
          fd.append("discount",              item.discount);
          fd.append("total_price",           item.total_price);
          fd.append("available_quantity",    item.quantity);
          fd.append("total_price_available", item.total_price);
          return axios.post(`${this.url}product-purchase-stock-store`, fd);
        });
        await Promise.all(requests);
        this.toast("Product purchase saved successfully", "success");
        this.$emit("created");
        this.emitClose();
      } catch (e) {
        const data = e?.response?.data;
        if (e?.response?.status === 422 && data?.errors) {
          Object.keys(data.errors).forEach(k => {
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
  width: min(96vw, 980px); background: #fff;
  border-radius: 16px; box-shadow: 0 18px 55px rgba(0,0,0,0.25);
  border: 1px solid rgba(0,0,0,0.06); overflow: hidden;
}
.xhead { padding: 16px 20px; border-bottom: 1px solid #eef2f7; background: #fff; }
.xbody { padding: 20px; max-height: calc(100vh - 190px); overflow: auto; }
.xfoot { padding: 14px 20px; border-top: 1px solid #eef2f7; background: #fff; }
.req { color: #dc3545; }
.form-control, .form-select {
  border-radius: 10px; border: 1px solid #d7dde5;
  padding: 8px 12px; box-shadow: none;
}
.form-control:focus, .form-select:focus {
  border-color: #6f63f6;
  box-shadow: 0 0 0 0.2rem rgba(111,99,246,0.12);
}
.purchase-input { max-width: 120px; }
.purchase-table thead th {
  background: #f8f9fa; font-size: 12px;
  letter-spacing: 0.05em; color: #6c757d;
}
.summary-row { background: #f0fdf4; font-weight: 600; }
.delete-btn { width: 32px; height: 32px; padding: 0; }
.slide-fade-enter-active, .slide-fade-leave-active { transition: all 0.18s ease; }
.slide-fade-enter-from, .slide-fade-leave-to { opacity: 0; transform: translateY(10px) scale(0.98); }
</style>