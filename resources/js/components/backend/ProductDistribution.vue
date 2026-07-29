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
                    <th>Seat</th>
                    <th>Guest Name</th>
                    <th>Product Name</th>
                    <th>Unit Price</th>
                    <th>Quantity</th>
                    <th>Total Price</th>
                    <th style="width:80px">Action</th>
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
                      <td>{{ item.floor_name || '—' }}</td>
                      <td>{{ item.room_no || '—' }}</td>
                      <td>
                        <span v-if="item.seat_no" class="badge bg-primary">Seat {{ item.seat_no }}</span>
                        <span v-else class="text-muted">—</span>
                      </td>
                      <td class="fw-semibold">{{ item.customer_name || '—' }}</td>

                      <!-- Product Name Column -->
                      <td>
                        <div v-if="item.items && item.items.length" class="d-flex flex-column gap-1">
                          <div v-for="sub in item.items" :key="'name-'+sub.product_name" class="fw-semibold text-dark">
                            {{ sub.product_name }}
                          </div>
                        </div>
                        <span v-else>{{ item.product_names || '—' }}</span>
                      </td>

                      <!-- Unit Price Column -->
                      <td>
                        <div v-if="item.items && item.items.length" class="d-flex flex-column gap-1">
                          <div v-for="sub in item.items" :key="'unit-'+sub.product_name">
                            {{ sub.single_price }} ৳
                          </div>
                        </div>
                        <span v-else-if="item.single_price">{{ item.single_price }} ৳</span>
                        <span v-else>—</span>
                      </td>

                      <!-- Quantity Column -->
                      <td>
                        <div v-if="item.items && item.items.length" class="d-flex flex-column gap-1">
                          <div v-for="sub in item.items" :key="'qty-'+sub.product_name" class="fw-bold text-primary">
                            {{ sub.quantity }} pcs
                          </div>
                          <div v-if="item.items.length > 1" class="small text-muted border-top pt-1 fw-bold">
                            Total: {{ item.total_quantity }} pcs
                          </div>
                        </div>
                        <span v-else class="fw-bold text-primary">{{ item.total_quantity }} pcs</span>
                      </td>

                      <!-- Total Price Column -->
                      <td>
                        <div v-if="item.items && item.items.length" class="d-flex flex-column gap-1">
                          <div v-for="sub in item.items" :key="'price-'+sub.product_name" class="fw-bold text-dark">
                            {{ sub.total_price }} ৳
                          </div>
                          <div v-if="item.items.length > 1" class="small text-success border-top pt-1 fw-bold">
                            Total: {{ parseFloat(item.total_price_available || 0).toFixed(2) }} ৳
                          </div>
                        </div>
                        <span v-else class="fw-bold">{{ parseFloat(item.total_price_available || 0).toFixed(2) }} ৳</span>
                      </td>

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
                    <td colspan="8" class="text-end fw-bold">Grand Total :</td>
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
          <h5 class="mb-0 text-danger"><i class="ti ti-trash me-2"></i>Delete Distribution</h5>
          <button type="button" class="btn-close" @click="closeDeleteModal"></button>
        </div>
        <div class="modal-box-body">
          <div class="alert alert-warning mb-0">
            Are you sure you want to delete distribution for:
            <strong>{{ delItem?.customer_name || 'Guest' }} ({{ delItem?.product_names || 'Products' }})</strong>?
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
      todayOnly:        false, 
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
        const base = this.url.endsWith('/') ? this.url : `${this.url}/`;
        const res = await axios.get(`${base}get-select-customer`);
        this.suppliers = res.data.data || [];
      } catch {
        this.toast("Failed to load customers.", "error");
      }
    },
    async fetchproductstock(page = 1) {
      this.loading = true;
      try {
        const base = this.url.endsWith('/') ? this.url : `${this.url}/`;
        const endpoint = this.todayOnly
          ? `${base}today-product-distribution-list`  // today
          : `${base}product-districbution-list`;       // all

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
        this.toast('Failed to load product distribution list.', 'error');
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
        const base = this.url.endsWith('/') ? this.url : `${this.url}/`;
        await axios.delete(`${base}customerlist-delete/${this.delItem.id}`);
        this.toast('Product distribution deleted successfully.');
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
      const rows = this.productstock.map((item, index) => {
        let prodNamesHtml = item.product_names || '—';
        let unitPricesHtml = item.single_price ? item.single_price + ' ৳' : '—';
        let qtysHtml = (item.total_quantity || 0) + ' pcs';
        let pricesHtml = parseFloat(item.total_price_available || 0).toFixed(2) + ' ৳';

        if (item.items && item.items.length) {
          const totalItems = item.items.length;
          prodNamesHtml = item.items.map((sub, i) => `<div class="sub-item ${i < totalItems - 1 ? 'has-border' : ''}">${sub.product_name}</div>`).join('');
          unitPricesHtml = item.items.map((sub, i) => `<div class="sub-item ${i < totalItems - 1 ? 'has-border' : ''}">${sub.single_price} ৳</div>`).join('');
          qtysHtml = item.items.map((sub, i) => `<div class="sub-item ${i < totalItems - 1 ? 'has-border' : ''}">${sub.quantity} pcs</div>`).join('');
          pricesHtml = item.items.map((sub, i) => `<div class="sub-item ${i < totalItems - 1 ? 'has-border' : ''}">${sub.total_price} ৳</div>`).join('');
        }

        return `
          <tr>
            <td class="text-center fw-semibold">${fromIndex + index}</td>
            <td class="text-center">${item.purchase_date || '—'}</td>
            <td>${item.floor_name || '—'}</td>
            <td class="text-center">${item.room_no || '—'}</td>
            <td class="text-center">${item.seat_no ? 'Seat ' + item.seat_no : '—'}</td>
            <td class="fw-semibold">${item.customer_name || '—'}</td>
            <td>${prodNamesHtml}</td>
            <td class="text-end fw-semibold">${unitPricesHtml}</td>
            <td class="text-center fw-semibold text-primary">${qtysHtml}</td>
            <td class="text-end fw-bold">${pricesHtml}</td>
          </tr>
        `;
      }).join('');

      const totalRow = `
        <tr class="grand-total-row">
          <td colspan="8" class="text-end fw-bold" style="font-size: 12px;">Grand Total :</td>
          <td class="text-center fw-bold" style="font-size: 12px; background: #e2e8f0 !important;">${this.grandTotalQuantity || 0} pcs</td>
          <td class="text-end fw-bold" style="font-size: 12px; background: #e2e8f0 !important;">${parseFloat(this.grandTotal || 0).toFixed(2)} ৳</td>
        </tr>
      `;

      const html = `
        <!DOCTYPE html>
        <html>
        <head>
          <title>Product Distribution Report - টি এস এস ভিলা</title>
          <style>
            @page { size: A4 landscape; margin: 10mm; }
            * { box-sizing: border-box; margin: 0; padding: 0; }
            body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 11px; color: #1e293b; background: #fff; line-height: 1.5; }
            
            .header-container {
              text-align: center;
              margin-bottom: 16px;
              border-bottom: 2px solid #0f172a;
              padding-bottom: 10px;
            }
            .header-title {
              font-size: 24px;
              font-weight: 800;
              color: #0f172a;
              margin-bottom: 3px;
              letter-spacing: 0.5px;
            }
            .header-address {
              font-size: 12px;
              color: #475569;
              margin-bottom: 8px;
              font-weight: 500;
            }
            .report-badge {
              display: inline-block;
              background: #0f172a;
              color: #ffffff;
              padding: 4px 18px;
              border-radius: 20px;
              font-size: 11px;
              font-weight: 700;
              letter-spacing: 0.5px;
            }
            .print-meta {
              font-size: 10px;
              color: #64748b;
              margin-top: 6px;
            }

            table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-top: 12px; }
            th, td {
              border: 1px solid #cbd5e1;
              padding: 8px 10px;
              vertical-align: middle;
              word-break: break-word;
            }
            th {
              background: #f1f5f9 !important;
              color: #0f172a !important;
              font-weight: 700;
              text-align: center;
              font-size: 11px;
              padding: 10px 8px;
              border: 1px solid #cbd5e1 !important;
            }
            tbody tr:nth-child(even) td { background: #f8fafc; }
            
            .sub-item {
              padding: 5px 0;
              line-height: 1.5;
            }
            .sub-item.has-border {
              border-bottom: 1px dashed #cbd5e1;
            }

            .text-center { text-align: center !important; }
            .text-end { text-align: right !important; }
            .fw-bold { font-weight: 700 !important; }
            .fw-semibold { font-weight: 600 !important; }
            .text-primary { color: #2563eb !important; }
            
            .grand-total-row td {
              font-weight: 700;
              background: #f1f5f9 !important;
              border-top: 2px solid #0f172a !important;
              border-bottom: 2px solid #0f172a !important;
              padding: 10px 8px !important;
            }

            col.sl-col    { width: 4%; }
            col.date-col  { width: 9%; }
            col.floor-col { width: 10%; }
            col.room-col  { width: 6%; }
            col.seat-col  { width: 8%; }
            col.guest-col { width: 15%; }
            col.prod-col  { width: 20%; }
            col.unit-col  { width: 9%; }
            col.qty-col   { width: 6%; }
            col.total-col { width: 13%; }

            .signature-area {
              margin-top: 45px;
              display: flex;
              justify-content: space-between;
              padding: 0 40px;
              page-break-inside: avoid;
            }
            .sig-line {
              border-top: 1.5px dashed #475569;
              width: 200px;
              text-align: center;
              padding-top: 6px;
              font-size: 11px;
              font-weight: 600;
              color: #334155;
            }

            @media print {
              thead { display: table-header-group; }
              tr { page-break-inside: avoid; }
            }
          </style>
        </head>
        <body>
          <div class="header-container">
            <div class="header-title">টি এস এস ভিলা</div>
            <div class="header-address">কলেজ রোড, নেসকো গেট সংলগ্ন, রংপুর</div>
            <div class="report-badge">প্রোডাক্ট ডিস্ট্রিবিউশন রিপোর্ট (Product Distribution Report)</div>
            <div class="print-meta">প্রিন্টের তারিখ: ${new Date().toLocaleString('bn-BD', { dateStyle: 'medium', timeStyle: 'short' })}</div>
          </div>

          <table>
            <colgroup>
              <col class="sl-col">
              <col class="date-col">
              <col class="floor-col">
              <col class="room-col">
              <col class="seat-col">
              <col class="guest-col">
              <col class="prod-col">
              <col class="unit-col">
              <col class="qty-col">
              <col class="total-col">
            </colgroup>
            <thead>
              <tr>
                <th>SL</th>
                <th>Date</th>
                <th>Floor</th>
                <th>Room</th>
                <th>Seat</th>
                <th>Guest Name</th>
                <th>Product Name</th>
                <th>Unit Price</th>
                <th>Qty</th>
                <th>Total Price</th>
              </tr>
            </thead>
            <tbody>
              ${rows}
              ${totalRow}
            </tbody>
          </table>

          <div class="signature-area">
            <div class="sig-line">প্রস্তুতকারীর স্বাক্ষর</div>
            <div class="sig-line">ম্যানেজার / কর্তৃপক্ষের স্বাক্ষর</div>
          </div>
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