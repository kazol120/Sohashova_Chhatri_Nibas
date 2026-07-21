<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-10 col-xl-12">
        <div class="card mb-4 shadow-sm">
          <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3">
            <div>
              <h5 class="card-title mb-0">Room Booking History</h5>
            </div>
          </div>
          <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3">
            <div style="width:22%;">
              <div v-if="isAdmin"   class="px-3 pt-3">
                <div class="d-flex mb-4">
                  <select
                      v-model="selectedGuest"
                      class="form-select"
                      @change="fetchRooms(1)"
                      style="max-width: 300px;">
                    <option value="">Select Guest</option>
                    <option
                      v-for="guest in guestNames"
                      :key="guest.full_name"
                      :value="guest.full_name">
                      {{ guest.full_name }}
                    </option>
                  </select>
                    <div class="ms-4  align-items-end">
                    <button class="btn btn-outline-secondary" @click="clearFilters">Clear</button>
                  </div>
                </div>
              </div>
            </div>
            <div>

          <div  v-if="isAdmin" class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3">
              <div>
                <div class="px-3 pt-3">
                  <div class="d-flex mb-4">
                    <div>
                        <label class="mb-2 text-black">Start Date</label>
                        <input class="form-control" type="date" v-model="startDate"  @change="fetchRooms(1)">
                    </div>
                    <div class="ms-4">
                      <label class="mb-2 text-black">End Date</label>
                        <input class="form-control" type="date" v-model="endDate"  @change="fetchRooms(1)">
                      </div>
                      <div class="ms-4 d-flex align-items-end">
                        <button class="btn btn-outline-secondary" @click="clearFilters">Clear</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="card-body">
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
              <div class="d-flex align-items-center gap-2">
                <label class="small text-muted mb-0">Rows:</label>
                <select
                  class="form-select form-select-sm"
                  style="width: 90px"
                  v-model.number="perPage"
                >
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
                  placeholder="Search room / floor / name / phone..."
                  v-model="search"
                />
              </div>
            </div>

            <div class="table-responsive">
              <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th style="width: 70px">Sl</th>
                    <th style="width: 130px">Image</th>
                    <th style="width: 160px">Name</th>
                    <th style="width: 180px">Floor</th>
                    <th style="width: 180px">Rooms</th>
                    <th style="width: 180px">Room Price</th>
                    <th style="width: 140px">Total Amount</th>
                    <th style="width: 180px">Total Days & Amount</th>
                    <th style="width: 180px">Booking Date & Time</th>
                    <th style="width: 140px">Check In</th>
                    <th style="width: 140px">Check Out</th>
                    <th style="width: 180px">Email</th>
                    <th style="width: 150px">Father Name</th>
                    <th style="width: 150px">Mother Name</th>
                    <th style="width: 140px">Father NID</th>
                    <th style="width: 140px">Mother NID</th>
                    <th style="width: 140px">Phone</th>
                    <th style="width: 120px">Division</th>
                    <th style="width: 120px">District</th>
                    <th style="width: 120px">Thana</th>
                    <th style="width: 120px">Payment</th>
                  </tr>
                </thead>

                <tbody v-if="rooms.length">
                  <tr v-for="(r, idx) in rooms" :key="r.group_key">
                    <td>{{ from + idx + 1 }}</td>

                    <td>
                      <img
                        v-if="r.image"
                        :src="imageSrc(r.image)"
                        class="img-thumb"
                        alt="booking"
                      />
                      <span v-else class="text-muted small">No image</span>
                    </td>

                    <td>
                      <div class="fw-semibold">{{ r.full_name || "-" }}</div>
                    </td>

                    <td colspan="4">
                      <div v-if="r.room_items && r.room_items.length" class="booking-card">
                        <div
                          v-for="(item, i) in r.room_items"
                          :key="'row-' + r.id + '-' + i"
                          class="booking-row">
                          <div class="booking-col floor">
                            {{ item.floornumber || "-" }}
                          </div>

                          <div class="booking-col room">
                            <span class="room-badge">{{ item.roomnumber }}</span>
                          </div>

                          <div class="booking-col price">
                            ৳ {{ Number(item.price || 0).toFixed(2) }}
                          </div>
                        </div>

                        <div
                          v-if="r.room_items.length > 1"
                          class="booking-total">
                          Total: ৳ {{ Number(r.payment_amount_total || 0).toFixed(2) }}
                        </div>
                      </div>
                      <span v-else>-</span>
                    </td>

                    <td>
                      <div class="fw-semibold">
                        {{ getTotalDays(r.check_in, r.check_out) }} Days
                      </div>

                      <div class="fw-semibold text-success">
                        ৳ {{ Number(r.daybytotalamount || 0).toFixed(2) }}
                      </div>
                    </td>

                    <td>
                      <span class="fw-semibold">{{ formatDateTime(r.created_at) }}</span>
                    </td>

                    <td>
                      <span class="fw-semibold">{{ formatDate(r.check_in) }}</span>
                    </td>

                    <td>
                      <span class="fw-semibold">{{ formatDate(r.check_out) }}</span>
                    </td>

                    <td>
                      <span class="fw-semibold">{{ r.email || "-" }}</span>
                    </td>

                    <td>
                      <span class="fw-semibold">{{ r.father_name || "-" }}</span>
                    </td>

                    <td>
                      <span class="fw-semibold">{{ r.mother_name || "-" }}</span>
                    </td>

                    <td>
                      <span class="fw-semibold">{{ r.father_nid || "-" }}</span>
                    </td>

                    <td>
                      <span class="fw-semibold">{{ r.mother_nid || "-" }}</span>
                    </td>

                    <td>
                      <span class="fw-semibold">{{ r.phone || "-" }}</span>
                    </td>

                    <td>
                      <span class="fw-semibold">{{ r.division_name || "-" }}</span>
                    </td>

                    <td>
                      <span class="fw-semibold">{{ r.district_name || "-" }}</span>
                    </td>

                    <td>
                      <span class="fw-semibold">{{ r.thana_name || "-" }}</span>
                    </td>

                    <td>
                      <div v-if="r.pay_online" class="fw-semibold text-success">
                        {{ r.pay_online }}
                      </div>
                      <div v-else-if="r.pay_cash_in" class="fw-semibold text-primary">
                        {{ r.pay_cash_in }}
                      </div>
                      <div v-else>-</div>
                    </td>
                  </tr>
                </tbody>

                <tbody v-else>
                  <tr>
                    <td colspan="18" class="text-center py-4 text-muted">
                      <span v-if="loading">
                        <i class="fa fa-spinner fa-spin me-2"></i> Loading...
                      </span>
                      <span v-else>No booking history found</span>
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
                  @click="fetchRooms(currentPage - 1)"
                >
                  Previous
                </button>

                <button
                  class="btn btn-sm btn-secondary"
                  :disabled="currentPage >= totalPages || loading"
                  @click="fetchRooms(currentPage + 1)"
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
  name: "RoomBookingHistoryList",

  props: {
    isAdmin: {
      type: Boolean,
      default: false,
    },
  },

  data() {
    return {
      rooms: [],
      loading: false,
      search: "",
      perPage: 10,
      currentPage: 1,
      totalPages: 1,
      total: 0,
      from: 0,
      _t: null,

      startDate: "",
      endDate: "",
      selectedGuest: "",
      guestNames: [],
    };
  },

  computed: {
    url() {
      return this.$store.state.url;
    },
  },

  mounted() {
    this.fetchRooms(1);

    if (this.isAdmin) {
      this.loadGuestNames();
    }
  },

watch: {
  search() {
    clearTimeout(this._t);
    this._t = setTimeout(() => this.fetchRooms(1), 300);
  },
  perPage() {
    this.fetchRooms(1);
  },
},

  beforeUnmount() {
    clearTimeout(this._t);
  },

  methods: {
    async loadGuestNames() {
      try {
        const res = await axios.get(this.endpoint("get-select-guet"));
        if (res.data.status === "success") {
          this.guestNames = res.data.data || [];
        }
      } catch (error) {
        this.toast("Failed to load guest names.", "error");
      }
    },

    clearFilters() {
      this.startDate = "";
      this.endDate = "";
      this.selectedGuest = "";
      this.fetchRooms(1);
    },

    getTotalDays(checkIn, checkOut) {
      if (!checkIn || !checkOut) return 0;

      const start = new Date(checkIn);
      const end = new Date(checkOut);

      const diffTime = end - start;
      const diffDays = diffTime / (1000 * 60 * 60 * 24);

      return diffDays > 0 ? diffDays : 0;
    },

    toast(text, type = "success") {
      const bg =
        type === "success"
          ? "linear-gradient(to right, #00b09b, #96c93d)"
          : type === "warning"
          ? "linear-gradient(to right, #f59e0b, #fbbf24)"
          : "linear-gradient(to right, #ff5f6d, #ffc371)";

      Toastify({
        text: text || (type === "success" ? "Done " : "Something went wrong"),
        duration: 2500,
        close: true,
        gravity: "top",
        position: "right",
        backgroundColor: bg,
      }).showToast();
    },

    endpoint(path) {
      const base = this.url.endsWith("/") ? this.url.slice(0, -1) : this.url;
      const cleanPath = path.startsWith("/") ? path : `/${path}`;
      return `${base}${cleanPath}`;
    },

    imageSrc(path) {
      if (!path) return "";
      if (path.startsWith("http://") || path.startsWith("https://")) {
        return path;
      }
      const base = this.url.endsWith("/") ? this.url.slice(0, -1) : this.url;
      return `${base}/bookingsimage/${path}`;
    },

    formatDate(value) {
      if (!value) return "-";
      const d = new Date(value);
      if (isNaN(d.getTime())) return value;
      return d.toLocaleDateString("en-GB", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
      });
    },

    formatDateTime(value) {
      if (!value) return "-";

      const raw = String(value).trim();
      const normalized = raw.includes("T") ? raw : raw.replace(" ", "T");
      const dateObj = new Date(normalized);

      if (isNaN(dateObj.getTime())) {
        return "-";
      }

      return new Intl.DateTimeFormat("en-GB", {
        timeZone: "Asia/Dhaka",
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
        hour12: true,
      }).format(dateObj);
    },

    async fetchRooms(page = 1) {
  this.loading = true;

  try {
    const params = {
      page,
      per_page: this.perPage,
      search: this.search,
    };

    if (this.isAdmin) {
      params.start_date = this.startDate;
      params.end_date = this.endDate;
      params.selected_guest = this.selectedGuest;
    }

    const res = await axios.get(this.endpoint("get-room-booking"), {
      params,
    });

    this.rooms = res.data.data || [];
    this.currentPage = res.data.current_page || 1;
    this.totalPages = res.data.last_page || 1;
    this.total = res.data.total || 0;
    this.from = res.data.from ? res.data.from - 1 : 0;
  } catch (e) {
    console.error(e);
    this.toast("Failed to load booking history", "error");
  } finally {
    this.loading = false;
  }
},

    printTable() {
      window.print();
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

.form-control,
.form-select {
  border-radius: 8px;
  padding: 0.6rem 0.75rem;
  border: 1px solid #dce0e4;
}

.form-control:focus,
.form-select:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
}

.table td {
  vertical-align: top;
}

.table thead th {
  vertical-align: middle;
  text-align: center;
  font-size: 13px;
  font-weight: 700;
  color: #374151;
  background: #f3f4f6;
}

.table tbody td {
  padding-top: 14px;
  padding-bottom: 14px;
}

/* Booking Card */
.booking-card {
  width: 100%;
  background: #ffffff;
  border-radius: 14px;
  overflow: hidden;
  border: 1px solid #dbe2ea;
}

/* Each booking row */
.booking-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 16px;
  transition: all 0.2s ease;
}

.booking-row:not(:last-child) {
  border-bottom: 1px solid #edf2f7;
}

.booking-row:hover {
  background: #f9fafb;
}

/* Common column */
.booking-col {
  flex: 1;
  display: flex;
  align-items: center;
}

/* Floor */
.booking-col.floor {
  font-size: 14px;
  font-weight: 600;
  color: #6b7280;
  text-transform: capitalize;
}

/* Room */
.booking-col.room {
  justify-content: center;
}

/* Price */
.booking-col.price {
  justify-content: flex-end;
  font-size: 15px;
  font-weight: 700;
  color: #111827;
}

/* Premium room badge */
.room-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 58px;
  height: 34px;
  padding: 0 14px;
  border-radius: 10px;
  background: linear-gradient(135deg, #111827, #1f2937);
  color: #ff4d4f;
  font-size: 14px;
  font-weight: 700;
  letter-spacing: 0.4px;
  box-shadow: 0 5px 12px rgba(17, 24, 39, 0.18);
  border: 1px solid rgba(255, 255, 255, 0.06);
}

/* Total row */
.booking-total {
  padding: 10px 16px;
  border-top: 1px solid #dbe2ea;
  background: #f8fafc;
  font-size: 15px;
  font-weight: 700;
  color: #4b5563;
  text-align: left;
}

/* Old helper classes kept if used elsewhere */
.booking-stack {
  display: flex;
  flex-direction: column;
  gap: 10px;
  min-width: 110px;
}

.booking-line {
  min-height: 36px;
  display: flex;
  align-items: center;
}

.floor-text {
  display: inline-block;
  font-size: 14px;
  font-weight: 600;
  line-height: 1.35;
  color: #4b5563;
  text-transform: capitalize;
}

.room-chip {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 58px;
  height: 34px;
  padding: 0 12px;
  border-radius: 10px;
  background: linear-gradient(135deg, #111827, #1f2937);
  color: #ff4d4f;
  font-size: 14px;
  font-weight: 700;
  letter-spacing: 0.3px;
  box-shadow: 0 4px 10px rgba(17, 24, 39, 0.18);
  border: 1px solid rgba(255, 255, 255, 0.06);
}

.price-text {
  display: inline-block;
  font-size: 15px;
  font-weight: 700;
  color: #374151;
}

@media (max-width: 768px) {
  .booking-stack {
    min-width: 90px;
    gap: 8px;
  }

  .booking-line {
    min-height: 32px;
  }

  .room-chip,
  .room-badge {
    min-width: 50px;
    height: 30px;
    font-size: 13px;
    padding: 0 10px;
  }

  .floor-text,
  .price-text {
    font-size: 13px;
  }

  .booking-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 6px;
  }

  .booking-col {
    width: 100%;
    justify-content: space-between !important;
  }

  .booking-total {
    font-size: 14px;
    padding: 10px 14px;
  }
}
</style>