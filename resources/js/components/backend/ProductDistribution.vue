<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
      <div class="col-12">
        <div class="card mb-4 shadow-sm">

          <!-- Header -->
          <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3">
            <h5 class="card-title mb-0">
              {{ todayOnly ? 'Today Product Distribution List' : 'Product Distribution List' }}
            </h5>
            <button class="btn btn-primary" type="button" @click="showCreateModal = true">
              <i class="ti ti-plus me-1"></i> Add Product Distribution
            </button>
          </div>

          <!-- Date Filter + Print -->
          <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3">
             <div class="d-flex flex-wrap gap-3 align-items-end">
              <template v-if="!todayOnly">
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
              </template>
            </div>
            <div>
              <button class="btn btn-primary" type="button" @click="printTable">
                <i class="ti ti-printer me-1"></i> Print
              </button>
            </div>
          </div>

          <!-- Customer Filter -->
          <div v-if="!todayOnly" class="px-3 pt-3">
            <div class="d-flex mb-4">
              <select
                v-model="selectedSupplier"
                class="form-select"
                style="max-width: 300px;"
                @change="fetchproductstock(1)">
                <option value="">All Customers</option>
                <option v-for="s in suppliers" :key="s.id" :value="s.id">
                  {{ s.full_name }}
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
                    <th>Floor</th>
                    <th>Room</th>
                    <th>Guest Name</th>
                    <th>Product Name</th>
                    <!-- <th>Price</th> -->
                    <th>Quantity</th>
                    <th>Total Price</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="loading">
                    <td colspan="7" class="text-center py-5 text-muted">
                      <i class="fa fa-spinner fa-spin me-2"></i>Loading...
                    </td>
                  </tr>
                  <tr v-else-if="productstock.length === 0">
                    <td colspan="7" class="text-center py-5 text-muted">No records found</td>
                  </tr>

                  <template v-else>
                  <tr v-for="(item, index) in productstock" :key="item.id">
                  <td>{{ from + index }}</td>
                  <td>{{ item.purchase_date }}</td>
                  <td>{{ item.floor_name || '—' }}</td>
                  <td>{{ item.room_no || '—' }}</td>
                  <td>{{ item.customer_name || '—' }}</td>
                  <td>{{ item.product_names || '—' }}</td>
                  <!-- <td>{{ item.product_price_details || '—' }}</td> -->
                  <td class="text-uppercase fw-semibold">{{ item.total_quantity }}</td>
                  <td>{{ parseFloat(item.total_price_available || 0).toFixed(2) }} ৳</td>
                  <td>
                    <button class="btn btn-sm btn-danger" @click="openDeleteModal(item)">
                    <i class="ti ti-trash"></i>
                    </button>
                  </td>
                </tr>
                </template>
                </tbody>
             <tfoot>
                <tr class="table-dark">
                <td colspan="6" class="text-end fw-bold">Grand Total :</td>
                <td class="fw-bold text-warning">{{ grandTotalQuantity }}</td>
                <td class="fw-bold text-warning">{{ parseFloat(grandTotal || 0).toFixed(2) }} ৳</td>
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

    <!-- DELETE MODAL -->
    <div v-if="delOpen" class="modal-overlay" @click.self="closeDeleteModal">
      <div class="modal-box">
        <div class="modal-box-head d-flex justify-content-between align-items-center">
          <h5 class="mb-0 text-danger"><i class="ti ti-trash me-2"></i>Delete Sale</h5>
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
    <productdistributionCreateForm
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
import productdistributionCreateForm from "../../components/createform/productdistributionCreateForm.vue";
export default {
  name: "ManageSaleList",
  components: { productdistributionCreateForm },
  computed: {
    url() {
      return this.$store.state.url;
    },
  },
  data() {
    return {
      grandTotalQuantity: 0,
      productstock:     [],
      suppliers:        [],
      loading:          false,
      search:           '',
      perPage:          50,
      total:            0,
      from:             1,
      currentPage:      1,
      totalPages:       1,
      grandTotal:       0,
      showCreateModal:  false,
      startDate:        '',
      endDate:          '',
      selectedSupplier: '',
      delOpen:          false,
      delItem:          null,
      savingDelete:     false,
      todayOnly: false, 
    };
  },
  mounted() {
    if (window.location.pathname.includes('today-product')) { 
    this.todayOnly = true;
    }
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
        const res = await axios.get(`${this.url}get-select-customer`);
        this.suppliers = res.data.data || [];
      } catch {
        this.toast("Failed to load customers.", "error");
      }
    },
async fetchproductstock(page = 1) {
  this.loading = true;
  try {
    const endpoint = this.todayOnly
      ? `${this.url}today-product-distribution-list`  // today
      : `${this.url}product-districbution-list`;       // all

    const res = await axios.get(endpoint, {
      params: {
        page,
        per_page:    this.perPage,
        search:      this.search,
        start_date:  this.startDate,
        end_date:    this.endDate,
        supplier_id: this.selectedSupplier,
      },
    });
    this.productstock       = res.data.productstock || [];
    this.total              = res.data.total        || 0;
    this.from               = res.data.from         ?? 1;
    this.currentPage        = res.data.current_page || 1;
    this.totalPages         = res.data.last_page    || 1;
    this.grandTotal         = parseFloat(res.data.grand_total || 0);
    this.grandTotalQuantity = res.data.grand_total_quantity || 0;
  } catch {
    this.toast('Failed to load sale list.', 'error');
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
        await axios.delete(`${this.url}customerlist-delete/${this.delItem.id}`);
        this.toast('Sale deleted successfully.');
        this.closeDeleteModal();
        this.fetchproductstock(this.currentPage);
      } catch {
        this.toast('Delete failed.', 'error');
      } finally {
        this.savingDelete = false;
      }
    },
    // ── Print ──
  printTable() {
    const fromIndex = this.from; 
    const rows = this.productstock.map((item, index) => `
      <tr>
        <td class="text-center">${fromIndex + index}</td>
        <td class="text-center">${item.purchase_date || '—'}</td>
        <td>${item.floor_name || '—'}</td>
        <td class="text-center">${item.room_no || '—'}</td>
        <td>${item.customer_name || '—'}</td>
        <td>${item.product_names || '—'}</td>
       
        <td class="text-center">${item.total_quantity || 0}</td>
        <td class="text-end">${parseFloat(item.total_price_available || 0).toFixed(2)} ৳</td>
      </tr>
    `).join('');
      const totalRow = `
        <tr class="grand-total-row">
          <td colspan="6" class="text-end fw-bold">Grand Total :</td>
          <td class="text-center fw-bold">${this.grandTotalQuantity || 0}</td>
          <td class="text-end fw-bold">${parseFloat(this.grandTotal || 0).toFixed(2)} ৳</td>
        </tr>
      `;
      const html = `
        <!DOCTYPE html>
        <html>
        <head>
          <title>Product Distribution Report</title>
          <style>
            @page { size: A4 landscape; margin: 10mm; }
            * { box-sizing: border-box; margin: 0; padding: 0; }
            body { font-family: Arial, sans-serif; font-size: 10px; color: #000; background: #fff; }
            h2 { text-align: center; margin-bottom: 4px; font-size: 16px; font-weight: 700; }
            p.sub { text-align: center; margin-bottom: 12px; font-size: 12px; font-weight: 400;}
            table { width: 100%; border-collapse: collapse; table-layout: fixed; }
            th, td {
              border: 1px solid #999;
              padding: 5px 6px;
              vertical-align: middle;
              word-break: break-word;
            }
            th { background: #e9e9e9; font-weight: 700; text-align: center; }
            tbody tr:nth-child(even) td { background: #f9f9f9; }
            .text-center { text-align: center !important; }
            .text-end { text-align: right !important; }
            .fw-bold { font-weight: 700; }
            .grand-total-row td {
              font-weight: 700;
              background: #f1f1f1 !important;
            }
            /*  Column width  Realtion- SL  */
            col.sl-col    { width: 4%; }
            col.date-col  { width: 9%; }
            col.floor-col { width: 11%; }
            col.room-col  { width: 7%; }
            col.guest-col { width: 13%; }
            col.prod-col  { width: 16%; }
            col.price-col { width: 22%; }
            col.qty-col   { width: 5%; }
            col.total-col { width: 13%; }
            @media print {
              thead { display: table-header-group; }
              tr { page-break-inside: avoid; }
            }
          </style>
        </head>
        <body>
          <h2>Product Distribution Report</h2>
           <p class="sub">Printed: ${new Date().toLocaleString()}</p>
          <table>
            <colgroup>
              <col class="sl-col">
              <col class="date-col">
              <col class="floor-col">
              <col class="room-col">
              <col class="guest-col">
              <col class="prod-col">
              <col class="qty-col">
              <col class="total-col">
            </colgroup>
            <thead>
              <tr>
                <th>SL</th>
                <th>Date</th>
                <th>Floor</th>
                <th>Room</th>
                <th>Guest Name</th>
                <th>Product Name</th>
                <th>Qty</th>
                <th>Total Price</th>
              </tr>
            </thead>
            <tbody>
              ${rows}
              ${totalRow}
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
</style>