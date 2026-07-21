<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
      <div class="col-12">
        <div class="card mb-4 shadow-sm">

          <!-- Header -->
          <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3">
            <h5 class="card-title mb-0"> Guest Report</h5>
            <button class="btn btn-primary" type="button" @click="printTable">
              <i class="ti ti-printer me-1"></i> Print
            </button>
          </div>

          <!-- Buttons -->
          <div class="card-header">
            <div class="d-flex flex-wrap gap-3 align-items-center">
              <div>
                <h6 class="mb-1 text-muted">All Guest List</h6>
                <button class="btn btn-outline-warning btn-sm" @click="loadGuests('')">
                  <i class="ti ti-list me-1"></i> Load All
                </button>
              </div>
              <div>
                <h6 class="mb-1 text-muted">Check IN</h6>
                <button class="btn btn-outline-success btn-sm" @click="loadGuests('checkout_in')">
                  <i class="ti ti-login me-1"></i> Load Check IN
                </button>
              </div>
              <div>
                <h6 class="mb-1 text-muted">Checkout List</h6>
                <button class="btn btn-outline-info btn-sm" @click="loadGuests('checkout_list')">
                  <i class="ti ti-clipboard-list me-1"></i> Load Checkout
                </button>
              </div>
              <div>
                <h6 class="mb-1 text-muted">Checkout Due</h6>
                <button class="btn btn-outline-danger btn-sm" @click="loadGuests('checkout_due')">
                  <i class="ti ti-door-exit me-1"></i> Load Due
                </button>
              </div>
            </div>
          </div>

          <!-- Table -->
     <div class="card-body">
  <div class="table-responsive" id="printArea">
    <table class="table table-bordered table-hover align-middle text-center">
      <thead style="background:#f59e0b; color:#fff;">
        <tr>
          <th>SL</th>
          <th>Name</th>
          <th>Floor</th>
          <th>Room</th>
          <th>Seat</th>
          <th>Booking Fee</th>
          <th>Booking Date & Time</th>
          <th>Product Name</th>
          <th>Qty</th>
          <th>Single Price</th>
          <th>Total Price</th>
          <th>Total Booking Fee + Product Distribution</th>
          <th class="no-print">Action</th>
        </tr>
      </thead>

      <tbody v-if="!loading && rooms.length">
        <template v-for="(r, idx) in rooms" :key="r.id">

          <!-- No Product row -->
          <tr v-if="!r.products || r.products.length === 0">
            <td class="fw-bold">{{ idx + 1 }}</td>
            <td class="fw-semibold">{{ r.full_name || '-' }}</td>

            <td colspan="3">
              <div v-if="r.room_items && r.room_items.length">
                <table class="table table-bordered mb-0 room-inner-table">
                  <tbody>
                    <tr v-for="(item, i) in r.room_items" :key="i">
                      <td class="text-muted fw-semibold">{{ item.floornumber }}</td>
                      <td>
                        <span class="room-badge">{{ (item.roomnumber || '').split('-')[0] }}</span>
                      </td>
                      <td>
                        <span class="badge bg-info text-white fw-bold" style="font-size: 11px; padding: 5px 8px;">
                          {{ (item.roomnumber || '').split('-')[1] || '-' }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <span v-else>-</span>
            </td>

            <td>
              <div class="text-success fw-bold">
                ৳ {{ Number(r.payment_amount_total || r.daybytotalamount || 0).toFixed(2) }}
              </div>
            </td>

            <td>{{ formatDateTime(r.created_at) }}</td>

            <td colspan="4" class="text-muted">No Product</td>

            <td>
              <div class="text-success fw-bold mb-2">৳ {{ getTotalAmount(r) }}</div>
            </td>

            <td class="no-print">
              <button v-if="filter === 'checkout_due'" class="btn btn-success btn-sm" @click="activeAndPrint(r)">
                <i class="ti ti-check me-1"></i> Active
              </button>

              <button v-else class="btn btn-outline-primary btn-sm" @click="printSingleRow(r)">
                <i class="ti ti-printer me-1"></i> Print
              </button>
            </td>
          </tr>

          <!-- With Product rows -->
          <template v-else v-for="(p, pi) in r.products" :key="r.id + '-p-' + pi">
            <tr>
              <td v-if="pi === 0" :rowspan="r.products.length + 1" class="fw-bold">
                {{ idx + 1 }}
              </td>

              <td v-if="pi === 0" :rowspan="r.products.length + 1" class="fw-semibold">
                {{ r.full_name || '-' }}
              </td>

              <td v-if="pi === 0" :rowspan="r.products.length + 1" colspan="3">
                <div v-if="r.room_items && r.room_items.length">
                  <table class="table table-bordered mb-0 room-inner-table">
                    <tbody>
                      <tr v-for="(item, i) in r.room_items" :key="i">
                        <td class="text-muted fw-semibold">{{ item.floornumber }}</td>
                        <td>
                          <span class="room-badge">{{ (item.roomnumber || '').split('-')[0] }}</span>
                        </td>
                        <td>
                          <span class="badge bg-info text-white fw-bold" style="font-size: 11px; padding: 5px 8px;">
                            {{ (item.roomnumber || '').split('-')[1] || '-' }}
                          </span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <span v-else>-</span>
              </td>
                <td v-if="pi === 0" :rowspan="r.products.length + 1">
                  <div class="text-success fw-bold">
                    ৳ {{ Number(r.payment_amount_total || r.daybytotalamount || 0).toFixed(2) }}
                  </div>
                </td>

                <td v-if="pi === 0" :rowspan="r.products.length + 1">
                  {{ formatDateTime(r.created_at) }}
                </td>

                <td class="fw-semibold">{{ p.product_name }}</td>
                <td class="text-info fw-bold">{{ p.customer_quantity }}</td>
                <td>৳ {{ Number(p.single_price || 0).toFixed(2) }}</td>
                <td class="text-success fw-bold">
                  ৳ {{ Number(p.total_price || 0).toFixed(2) }}
                </td>

                <td v-if="pi === 0" :rowspan="r.products.length + 1" class="text-success fw-bold align-middle">
                  ৳ {{ getTotalAmount(r) }}
                </td>

                <td v-if="pi === 0" :rowspan="r.products.length + 1" class="no-print align-middle">
                  <button v-if="filter === 'checkout_due'" class="btn btn-success btn-sm" @click="activeAndPrint(r)">
                    <i class="ti ti-check me-1"></i> Active
                  </button>

                  <button v-else class="btn btn-outline-primary btn-sm" @click="printSingleRow(r)">
                    <i class="ti ti-printer me-1"></i> Print
                  </button>
                    </td>
                  </tr>
                </template>

                <!-- Product Grand Total row -->
                <tr v-if="r.products && r.products.length > 0" style="background:#fff8e1;">
                  <td colspan="3" class="text-end fw-bold text-warning">
                    Product Grand Total:
                  </td>
                  <td class="text-success fw-bold">
                    ৳ {{ r.products.reduce((s, p) => s + Number(p.total_price || 0), 0).toFixed(2) }}
                  </td>
                </tr>
              </template>
             </tbody>
              <tbody v-else>
              <tr>
                <td colspan="13" class="text-center py-5 text-muted">
                  <span v-if="loading">
                    <i class="fa fa-spinner fa-spin me-2"></i> Loading...
                  </span>
                  <span v-else>Click the button to view data</span>
                </td>
              </tr>
              </tbody>
              </table>
            </div>

            <div class="pagination-footer">
            <div class="pagination-info">
              Total: {{ pagination.total }} | Page: {{ pagination.current_page }} / {{ pagination.last_page }}
            </div>

            <div class="pagination-actions">
              <button
                class="btn btn-secondary"
                :disabled="pagination.current_page <= 1"
                @click="previousPage"
              >
                Previous
              </button>

              <button
                class="btn btn-secondary"
                :disabled="pagination.current_page >= pagination.last_page"
                @click="nextPage"
              >
                Next
              </button>
            </div>
          </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import Toastify from "toastify-js";
import "toastify-js/src/toastify.css";

export default {
  name: "GuestReport",
  computed: {
    url() {
      return this.$store.state.url;
    },
  },
  data() {
    return {
      rooms: [],
      loading: false,
      filter: '',

          pagination: {
  total: 0,
  current_page: 1,
  last_page: 1,
  per_page: 6,
},


    };


  },
  mounted() {
    const params = new URLSearchParams(window.location.search);
    const filter = params.get('filter');
    if (filter) {
      this.loadGuests(filter);
    }
  },
  methods: {
    goToPage(page) {
  if (page < 1 || page > this.pagination.last_page) return;
  this.loadGuests(this.filter, page);
},

nextPage() {
  if (this.pagination.current_page < this.pagination.last_page) {
    this.goToPage(this.pagination.current_page + 1);
  }
},

previousPage() {
  if (this.pagination.current_page > 1) {
    this.goToPage(this.pagination.current_page - 1);
  }
},

   async loadGuests(filter = '', page = 1) {
  this.filter = filter;
  this.loading = true;

  try {
    const params = {
      page: page,
    };

    if (filter) {
      params.filter = filter;
    }

    const res = await axios.get(`${this.url}get-roombooking`, { params });

    this.rooms = res.data.data || [];

    this.pagination = res.data.pagination || {
      total: 0,
      current_page: 1,
      last_page: 1,
      per_page: 6,
    };

  } catch (e) {
    this.toast("Failed to load guest list", "error");
  } finally {
    this.loading = false;
  }
},


    async activeAndPrint(r) {
      this.printSingleRow(r);
      try {
        const res = await axios.post(`${this.url}admin/rooms/active`, { id: r.id });
        if (res.data.success) {
          this.rooms = this.rooms.filter(room => room.id !== r.id);
          this.toast("Room activated successfully!", "success");
        }
      } catch (e) {
        this.toast("Failed to activate room", "error");
      }
    },
    //  Room Total (Booking Fee) +  Product Total
    getTotalAmount(r) {
      const roomTotal = r.room_items
        ? r.room_items.reduce((s, i) => s + Number(i.price || 0), 0)
        : 0;
      const productTotal = r.products
        ? r.products.reduce((s, p) => s + Number(p.total_price || 0), 0)
        : 0;
      return (roomTotal + productTotal).toFixed(2);
    },

    printSingleRow(r) {
      let roomRows = '';

      if (r.room_items && r.room_items.length) {
        let grandRoomTotal = 0;
        const floorSpanMap = {};

        r.room_items.forEach((item) => {
          const floor = item.floornumber || '-';
          if (floorSpanMap[floor] === undefined) floorSpanMap[floor] = 0;
          floorSpanMap[floor]++;
        });

        const floorRendered = {};

        r.room_items.forEach((item, index) => {
          const fee = Number(item.price || 0);
          grandRoomTotal += fee;

          const floor = item.floornumber || '-';
          let floorCell = '';

          if (!floorRendered[floor]) {
            floorRendered[floor] = true;
            floorCell = `<td rowspan="${floorSpanMap[floor]}" style="vertical-align:middle;">${floor}</td>`;
          }

          const roomPart = (item.roomnumber || '').split('-')[0];
          const seatPart = (item.roomnumber || '').split('-')[1] || '-';

          roomRows += `
            <tr>
              ${floorCell}
              <td><strong style="background:#1e293b;color:#fff;padding:2px 10px;border-radius:4px;">${roomPart}</strong></td>
              <td><strong style="background:#0ea5e9;color:#fff;padding:2px 10px;border-radius:4px;">${seatPart}</strong></td>
              <td>৳ ${fee.toFixed(2)}</td>
            </tr>`;
        });

        roomRows += `
          <tr style="background:#fff8e1;">
            <td colspan="3" style="font-weight:bold;text-align:right;">Grand Total:</td>
            <td style="font-weight:bold;color:#16a34a;">৳ ${grandRoomTotal.toFixed(2)}</td>
          </tr>`;
      } else {
        roomRows = '<tr><td colspan="4" style="text-align:center;color:#888;">-</td></tr>';
      }

      let productSection = '';
  let productGrandTotal = 0;

  if (r.products && r.products.length > 0) {
    let productRows = '';

    r.products.forEach(p => {
      productGrandTotal += Number(p.total_price || 0);

      productRows += `
        <tr>
          <td>${p.product_name}</td>
          <td>${p.customer_quantity}</td>
          <td>৳ ${Number(p.single_price || 0).toFixed(2)}</td>
          <td>৳ ${Number(p.total_price || 0).toFixed(2)}</td>
        </tr>`;
    });

    productRows += `
      <tr style="background:#fff8e1;">
        <td colspan="3" style="font-weight:bold;text-align:right;">Product Grand Total:</td>
        <td style="font-weight:bold;color:#16a34a;">৳ ${productGrandTotal.toFixed(2)}</td>
      </tr>`;

    productSection = `
      <div class="section-title">📦 Product Distribution Info</div>
      <table>
        <thead>
          <tr>
            <th>Product Name</th>
            <th>Qty</th>
            <th>Single Price</th>
            <th>Total Price</th>
          </tr>
        </thead>
        <tbody>${productRows}</tbody>
      </table>`;
  }

  const grandTotal = this.getTotalAmount(r);

  const html = `<!DOCTYPE html>
  <html>
  <head>
    <title>Guest Receipt</title>
    <style>
      @page { size: A4 portrait; margin: 15mm; }
      * { box-sizing: border-box; margin: 0; padding: 0; }
      body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
      .header { text-align: center; margin-bottom: 16px; padding-bottom: 10px; border-bottom: 2px solid #f59e0b; }
      .header h2 { font-size: 18px; font-weight: bold; color: #f59e0b; }
      .header p { font-size: 11px; color: #666; margin-top: 4px; }
      .section-title { font-weight: bold; margin: 14px 0 6px; color: #f59e0b; font-size: 12px; border-left: 3px solid #f59e0b; padding-left: 6px; }
      table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
      th, td { border: 1px solid #ddd; padding: 6px 10px; text-align: center; }
      th { background: #f59e0b !important; color: #fff !important; font-weight: bold; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      .info-table td:first-child { font-weight: bold; background: #fff8e1; text-align: left; width: 40%; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      .info-table td { text-align: left; }
      .grand-total-row { background: #f0fdf4 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    </style>
  </head>

  <body>

    <div class="header">
    <h2>
        <img src="/logo/images (3).png"
             style="height:50px;width:auto;vertical-align:middle;margin-right:10px;">
        Guest Checkout Receipt
    </h2>      <p>Printed: ${new Date().toLocaleString()}</p>
    </div>
    <div class="section-title">👤 Guest Info</div>
    <table class="info-table">
      <tr><td>Name</td><td>${r.full_name || '-'}</td></tr>
    </table>

    <div class="section-title">🏠 Room Info</div>
    <table>
      <thead>
        <tr>
          <th>Floor</th>
          <th>Room</th>
          <th>Seat</th>
          <th>Booking Fee</th>
        </tr>
      </thead>
      <tbody>${roomRows}</tbody>
    </table>

    ${productSection}

    <table>
      <tr class="grand-total-row">
        <td style="font-weight:bold;font-size:14px;text-align:right;">💰 Grand Total (Room + Product Distribution):</td>
        <td style="font-weight:bold;font-size:14px;color:#16a34a;">৳ ${grandTotal}</td>
      </tr>
    </table>

    <script>
      window.onload = function() {
        window.print();
        window.onafterprint = function() { window.close(); };
      };
    <\/script>
  </body>
  </html>`;

  const win = window.open("", "_blank");
  win.document.write(html);
  win.document.close();
},

    getTotalDays(checkIn, checkOut) {
      if (!checkIn || !checkOut) return 0;
      const a = new Date(checkIn);
      const b = new Date(checkOut);
      const diff = Math.ceil((b - a) / (1000 * 60 * 60 * 24));
      return diff > 0 ? diff : 1;
    },
    formatDate(d) {
      if (!d) return '-';
      const parts = String(d).split('T')[0].split('-');
      return parts.length === 3 ? `${parts[2]}-${parts[1]}-${parts[0]}` : d;
    },
    formatDateTime(dt) {
      if (!dt) return '-';
      const [date, time] = String(dt).split('T');
      const parts = date.split('-');
      const formatted = parts.length === 3 ? `${parts[2]}-${parts[1]}-${parts[0]}` : date;
      return `${formatted} ${time ? time.substring(0, 5) : ''}`;
    },
    printTable() {
      const el = document.getElementById("printArea");
      if (!el) return;
      const html = `<!DOCTYPE html>
      <html>
      <head>
        <title>All Guest Report</title>
        <style>
          @page { size: A4 landscape; margin: 12mm; }
          * { box-sizing: border-box; margin: 0; padding: 0; }
          body { font-family: Arial, sans-serif; font-size: 9px; color: #333; }
          table { width: 100%; border-collapse: collapse; }
          th, td { border: 1px solid #ccc; padding: 4px 5px; text-align: center; vertical-align: middle; }
          thead th { background: #f59e0b !important; color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
          .fw-bold { font-weight: bold; }
          .fw-semibold { font-weight: 600; }
          .text-success { color: #16a34a !important; }
          .text-info { color: #0ea5e9 !important; }
          .text-muted { color: #888 !important; }
          .room-badge { background: #1e293b; color: #fff; padding: 1px 5px; border-radius: 3px; font-weight: bold; }
          .no-print { display: none !important; }
        </style>
      </head>
      <body>
        <div style="text-align:center;margin-bottom:10px;">
          <h2 style="font-size:15px;font-weight:bold;">All Guest Report</h2>
          <p style="font-size:10px;color:#666;">Printed: ${new Date().toLocaleString()}</p>
        </div>
        ${el.innerHTML}
        <script>
          window.onload = function() {
            window.print();
            window.onafterprint = function() { window.close(); };
          };
        <\/script>
      </body>
      </html>`;
      const win = window.open("", "_blank");
      win.document.write(html);
      win.document.close();
    },
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
  },
};
</script>

<style scoped>
.table th, .table td { vertical-align: middle; white-space: nowrap; }
.room-badge {
  background: #1e293b;
  color: #fff;
  border-radius: 5px;
  padding: 2px 8px;
  font-size: 0.78rem;
  font-weight: 700;
}

.booking-row { display: flex; align-items: center; gap: 6px; padding: 2px 0; }
.no-print { display: table-cell; }
@media print { .no-print { display: none !important; } }
.room-inner-table{
  background:#fff;
  border-radius:8px;
  overflow:hidden;
}
.room-inner-table td{
  padding:10px !important;
  vertical-align:middle !important;
  border:1px solid #e5e7eb !important;
}
.room-grand-total-row{
  background:#f8fafc !important;
}
.room-badge{
  background:#1e293b;
  color:#fff;
  border-radius:6px;
  padding:4px 10px;
  font-size:13px;
  font-weight:700;
  display:inline-block;
}

.pagination-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 18px 22px;
  border-top: 1px solid #eee;
  background: #fff;
}

.pagination-info {
  color: #9ca3af;
  font-size: 14px;
  font-weight: 600;
}

.pagination-actions {
  display: flex;
  gap: 8px;
}

.pagination-actions .btn {
  padding: 10px 22px;
  font-weight: 700;
  border-radius: 4px;
}

.pagination-actions .btn:disabled {
  background: #c7c9d1;
  border-color: #c7c9d1;
  color: #fff;
  opacity: 1;
}
</style>