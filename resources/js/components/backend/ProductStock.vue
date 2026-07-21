<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
      <div class="col-12">
        <div class="card mb-4 shadow-sm">

          <!-- Header -->
          <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3">
            <h5 class="card-title mb-0">Product Stock Table</h5>
            <button class="btn btn-primary" type="button" @click="showCreateModal = true">
              <i class="ti ti-plus me-1"></i> Add Purchase
            </button>
          </div>

          <!-- Date Filter + Print -->
          <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3">
            <div class="d-flex flex-wrap gap-3 align-items-end">
              <div>
                <label class="mb-2 text-black">Start Date</label>
                <input class="form-control" type="date" v-model="startDate" @change="fetchproductstock(1)">
              </div>
              <div>
                <label class="mb-2 text-black">End Date</label>
                <input class="form-control" type="date" v-model="endDate" @change="fetchproductstock(1)">
              </div>
              <div>
                <button class="btn btn-outline-secondary" @click="clearFilters">Clear</button>
              </div>
            </div>
            <div>
              <button class="btn btn-primary" type="button" @click="printTable">
                <i class="ti ti-printer me-1"></i> Print
              </button>
            </div>
          </div>

          <!-- Supplier Filter -->
          <div class="px-3 pt-3">
            <div class="d-flex mb-4">
              <select
                v-model="selectedSupplier"
                class="form-select"
                style="max-width: 300px;"
                @change="fetchproductstock(1)">
                <option value="">All Suppliers</option>
                <option v-for="s in suppliers" :key="s.id" :value="s.id">
                  {{ s.name }}
                </option>
              </select>
              <div class="ms-4 d-flex align-items-end">
                <button class="btn btn-outline-secondary" @click="clearSupplier">Clear</button>
              </div>
            </div>
          </div>

          <div class="card-body">
            <!-- Rows + Search -->
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
              <div class="d-flex align-items-center gap-2">
                <label class="small text-muted mb-0">Rows:</label>
                <select class="form-select form-select-sm" style="width:90px" v-model.number="perPage">
                  <option :value="30">30</option>
                  <option :value="50">50</option>
                  <option :value="60">60</option>
                  <option :value="150">150</option>
                  <option :value="200">200</option>
                </select>
              </div>
              <input
                type="text"
                class="form-control form-control-sm"
                style="width:300px"
                placeholder="Search product name..."
                v-model="search"
                @keyup.enter="fetchproductstock(1)"
              />
            </div>

            <!-- Table -->
            <div class="table-responsive">
              <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th style="width:55px">Sl</th>
                    <th>Date</th>
                    <th>Memo Number</th>
                    <th>Supplier Name</th>
                    <th>Product Name</th>
                    <th>Unit Price</th>
                    <th>Quantity</th>
                    <th>Total Price</th>
                    <th>Available Qty</th>
                    <th>Available Amount</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="loading">
                    <td colspan="11" class="text-center py-5 text-muted">
                      <i class="fa fa-spinner fa-spin me-2"></i>Loading...
                    </td>
                  </tr>
                  <tr v-else-if="productstock.length === 0">
                    <td colspan="11" class="text-center py-5 text-muted">No records found</td>
                  </tr>
                  <template v-else>
                    <tr v-for="(item, index) in productstock" :key="item.id">
                      <td>{{ from + index }}</td>
                      <td>{{ item.purchase_date }}</td>
                      <td>{{ item.memo_number }}</td>
                      <td>{{ item.supplier?.name || '—' }}</td>
                      <td class="text-uppercase fw-semibold">{{ item.product_name }}</td>
                      <td>{{ parseFloat(item.single_price || 0).toFixed(2) }} ৳</td>
                      <td>{{ item.quantity }}</td>
                  
                      <td>{{ parseFloat(item.total_price || 0).toFixed(2) }} ৳</td>
                      <td>{{ item.available_quantity }}</td>
                      <td>{{ parseFloat(item.total_price_available || 0).toFixed(2) }} ৳</td>
                      <td>
                        <div class="d-flex gap-2 align-items-center">
                          <button class="btn btn-sm btn-danger" @click="openDeleteModal(item)">
                            <i class="ti ti-trash"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                  </template>
                </tbody>

              <tfoot>
                <tr class="table-dark">
                  <td colspan="6" class="text-end fw-bold">Current Stock Quantity :</td>
                  <td class="fw-bold text-warning">{{ grandTotalQuantity }}</td>
                  <td class="fw-bold text-warning">{{ parseFloat(grandTotal || 0).toFixed(2) }} ৳</td>
                  <td class="fw-bold text-warning">{{ grandTotalAvailableQuantity }}</td>
                  <td class="fw-bold text-warning">{{ parseFloat(grandTotalAvailable || 0).toFixed(2) }} ৳</td>
                  <td></td>
                </tr>
              </tfoot>
              </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
              <div class="small text-muted">
                Total: {{ total }} | Page: {{ currentPage }} / {{ totalPages }}
              </div>
              <div class="d-flex gap-2">
                <button
                  class="btn btn-sm btn-secondary"
                  :disabled="currentPage <= 1 || loading"
                  @click="fetchproductstock(currentPage - 1)">
                  Previous
                </button>
                <button
                  class="btn btn-sm btn-secondary"
                  :disabled="currentPage >= totalPages || loading"
                  @click="fetchproductstock(currentPage + 1)">
                  Next
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- ═══════════ DELETE MODAL ═══════════ -->
    <div v-if="delOpen" class="modal-overlay" @click.self="closeDeleteModal">
      <div class="modal-box">
        <div class="modal-box-head d-flex justify-content-between align-items-center">
          <h5 class="mb-0 text-danger"><i class="ti ti-trash me-2"></i>Delete Stock</h5>
          <button type="button" class="btn-close" @click="closeDeleteModal"></button>
        </div>
        <div class="modal-box-body">
          <div class="alert alert-warning mb-0">
            Are you sure you want to delete:
            <strong>{{ delItem?.product_name }}</strong>?
          </div>
        </div>
        <div class="modal-box-foot d-flex justify-content-end gap-2">
          <button class="btn btn-outline-secondary" type="button" @click="closeDeleteModal">Cancel</button>
          <button class="btn btn-danger" type="button" :disabled="savingDelete" @click="confirmDelete">
            <span v-if="savingDelete"><i class="fa fa-spinner fa-spin me-1"></i> Deleting...</span>
            <span v-else><i class="ti ti-trash me-1"></i> Yes, Delete</span>
          </button>
        </div>
      </div>
    </div>

    <!-- CREATE MODAL -->
    <ProductPurchaseCreateForm
      :show="showCreateModal"
      @close="showCreateModal = false"
      @created="handleCreated"
    />
  </div>
</template>

<script>
import axios from "axios";
import Toastify from "toastify-js";
import "toastify-js/src/toastify.css";
import ProductPurchaseCreateForm from "../../components/createform/ProductPurchaseCreateForm.vue";

export default {
  name: "ProductStockList",
  components: { ProductPurchaseCreateForm },

  computed: {
    url() {
      return this.$store.state.url;
    },
  },

  data() {
    return {
      grandTotalQuantity: 0,
      grandTotalAvailableQuantity: 0,
      grandTotalAvailable: 0,
      productstock:        [],
      suppliers:           [],
      loading:             false,
      search:              '',
      perPage:             50,
      total:               0,
      from:                1,
      currentPage:         1,
      totalPages:          1,
      grandTotal:          0,
      showCreateModal:     false,
      startDate:           '',
      endDate:             '',
      selectedSupplier:    '',
      // Delete
      delOpen:      false,
      delItem:      null,
      savingDelete: false,
      // Add Quantity Modal
      roomModal: {
        open:         false,
        product_id:   null,
        product_name: '',
        quantity:     '',
      },
      savequantity: false,
    };
  },

  mounted() {
    this.fetchproductstock(1);
    this.loadSuppliers();
  },

  watch: {
    search() {
      clearTimeout(this._t);
      this._t = setTimeout(() => this.fetchproductstock(1), 300);
    },
    perPage() {
      this.fetchproductstock(1);
    },
  },

  methods: {
    toast(text, type = "success") {
      Toastify({
        text,
        duration: 3000,
        gravity: "top",
        position: "right",
        style: {
          background: type === "success"
            ? "linear-gradient(to right, #22c55e, #16a34a)"
            : "linear-gradient(to right, #ef4444, #dc2626)",
        },
      }).showToast();
    },

    async loadSuppliers() {
      try {
        const res = await axios.get(`${this.url}get-select-supplier`);
        this.suppliers = res.data.data || [];
      } catch {
        this.toast("Failed to load suppliers.", "error");
      }
    },

    async fetchproductstock(page = 1) {
      this.loading = true;
      try {
        const res = await axios.get(`${this.url}product_purchase_list`, {
          params: {
            page,
            per_page:    this.perPage,
            search:      this.search,
            start_date:  this.startDate,
            end_date:    this.endDate,
            supplier_id: this.selectedSupplier,
          },
        });
        this.productstock        = res.data.productstock        || [];
        this.total               = res.data.total               || 0;
        this.from                = res.data.from                ?? 1;
        this.currentPage         = res.data.current_page        || 1;
        this.totalPages          = res.data.last_page           || 1;
          this.grandTotal                  = parseFloat(res.data.grand_total || 0);
        this.grandTotalAvailable         = parseFloat(res.data.grand_total_available || 0);
        this.grandTotalQuantity          = res.data.grand_total_quantity || 0;          // যোগ করুন
        this.grandTotalAvailableQuantity = res.data.grand_total_available_quantity || 0; // যোগ করুন
      } catch {
        this.toast('Failed to load stock list.', 'error');
      } finally {
        this.loading = false;
      }
    },

    clearFilters() {
      this.startDate        = '';
      this.endDate          = '';
      this.search           = '';
      this.selectedSupplier = '';
      this.fetchproductstock(1);
    },

    clearSupplier() {
      this.selectedSupplier = '';
      this.fetchproductstock(1);
    },

    handleCreated() {
      this.showCreateModal = false;
      this.fetchproductstock(1);
    },

    // ── Delete ───────────────────────────────────
    openDeleteModal(item) {
      this.delItem = { ...item };
      this.delOpen = true;
    },
    closeDeleteModal() {
      this.delOpen = false;
      this.delItem = null;
    },

    async confirmDelete() {
      this.savingDelete = true;
      try {
        await axios.delete(`${this.url}product-purchase-delete/${this.delItem.id}`);
        this.toast('Stock deleted successfully.');
        this.closeDeleteModal();
        this.fetchproductstock(this.currentPage);
      } catch {
        this.toast('Delete failed.', 'error');
      } finally {
        this.savingDelete = false;
      }
    },
    // ── Print ────────────────────────────────────
  async printTable() {
    try {
      const res = await axios.get(`${this.url}product_purchase_list`, {
        params: {
          page:        1,
          per_page:    99999,
          search:      this.search,
          start_date:  this.startDate,
          end_date:    this.endDate,
          supplier_id: this.selectedSupplier,
        },
      });

      const allData             = res.data.productstock || [];
      const grandTotal          = parseFloat(res.data.grand_total || 0);
      const grandTotalAvailable = parseFloat(res.data.grand_total_available || 0);
      const grandTotalQty       = res.data.grand_total_quantity || 0;
      const grandTotalAvailQty  = res.data.grand_total_available_quantity || 0;

      const rows = allData.map((item, index) => `
        <tr>
          <td>${index + 1}</td>
          <td>${item.purchase_date || '—'}</td>
          <td>${item.memo_number || '—'}</td>
          <td>${item.supplier?.name || '—'}</td>
          <td>${item.product_name || '—'}</td>
          <td>${parseFloat(item.single_price || 0).toFixed(2)} ৳</td>
          <td>${item.quantity}</td>
          <td>${parseFloat(item.total_price || 0).toFixed(2)} ৳</td>
          <td>${item.available_quantity}</td>
          <td>${parseFloat(item.total_price_available || 0).toFixed(2)} ৳</td>
        </tr>
      `).join('');
  const html = `
    <!DOCTYPE html>
    <html>
    <head>
      <title>Product Stock Report</title>
      <style>
        @page { size: A4 landscape; margin: 12mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 11px; }
        h2 { text-align: center; margin-bottom: 6px; font-size: 15px; }
         p.sub { text-align: center; margin-bottom: 12px; font-size: 12px; font-weight: 400;}
        table { width: 100%; border-collapse: collapse; }
        td { border: 1px solid #999; padding: 6px 8px; text-align: left; }
        .header-row td { background: #e9e9e9; font-weight: bold; }
        tr:nth-child(even) td { background: #f9f9f9; }
        .total-row td { background: black !important; color: black; font-weight: bold; }
        .total-row .label { color: black; text-align: right; }
      </style>
    </head>
    <body>
      <h2>Product Stock Report</h2>
       <p class="sub">Printed: ${new Date().toLocaleString()}</p>
      <table>
        <tbody>
          <tr class="header-row">
            <td>Sl</td>
            <td>Date</td>
            <td>Memo No</td>
            <td>Supplier</td>
            <td>Product</td>
            <td>Unit Price</td>
            <td>Qty</td>
            <td>Total Price</td>
            <td>Avail Qty</td>
            <td>Avail Amount</td>
          </tr>
          ${rows}
          <tr class="total-row">
            <td colspan="6" class="label">Current Stock :</td>
            <td>${grandTotalQty}</td>
            <td>${grandTotal.toFixed(2)} ৳</td>
            <td>${grandTotalAvailQty}</td>
            <td>${grandTotalAvailable.toFixed(2)} ৳</td>
          </tr>
        </tbody>
      </table>
    </body>
    </html>
  `;
      const old = document.getElementById('print-iframe');
      if (old) old.remove();
      const iframe = document.createElement('iframe');
      iframe.id = 'print-iframe';
      iframe.style.cssText = 'position:fixed;top:0;left:0;width:0;height:0;border:none;visibility:hidden;';
      document.body.appendChild(iframe);
      iframe.contentDocument.open();
      iframe.contentDocument.write(html);
      iframe.contentDocument.close();
      iframe.onload = () => {
        setTimeout(() => {
          iframe.contentWindow.focus();
          iframe.contentWindow.print();
        }, 300);
      };

    } catch {
      this.toast('Print failed.', 'error');
    }
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

/* ── Add Quantity Modal ── */
.xmask {
  position: fixed;
  inset: 0;
  z-index: 30000;
  background: rgba(0,0,0,0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
}
.xwrap {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}
.xbox {
  width: min(96vw, 460px);
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 18px 55px rgba(0,0,0,0.25);
  border: 1px solid rgba(0,0,0,0.06);
  overflow: hidden;
}
.xhead {
  padding: 16px 20px;
  border-bottom: 1px solid #eef2f7;
  background: #f8f9fa;
}
.xbody {
  padding: 20px;
}
.xfoot {
  padding: 14px 20px;
  border-top: 1px solid #eef2f7;
  background: #f8f9fa;
}

/* ── Delete Modal ── */
.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 99999;
  background: rgba(0,0,0,0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}
.modal-box {
  background: #fff;
  border-radius: 12px;
  width: 100%;
  max-width: 500px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
  overflow: hidden;
}
.modal-box-head {
  padding: 16px 20px;
  border-bottom: 1px solid #eef2f7;
  background: #fff;
}
.modal-box-body {
  padding: 20px;
  max-height: 65vh;
  overflow-y: auto;
}
.modal-box-foot {
  padding: 14px 20px;
  border-top: 1px solid #eef2f7;
  background: #fafafa;
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
