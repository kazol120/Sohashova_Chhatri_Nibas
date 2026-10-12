<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-10 col-xl-12">
        <div class="card mb-4 shadow-sm">
          <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3">
            <div>
              <h5 class="card-title mb-0"> Today Room Booking History</h5>
            </div>
          </div>
          <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3">
            <div style="width:22%;">
              <div v-if="isAdmin" class="px-3 pt-3">
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
                  <div class="ms-4 align-items-end">
                    <button class="btn btn-outline-secondary" @click="clearFilters">Clear</button>
                  </div>
                </div>
              </div>
            </div>
            <div>
              <div v-if="isAdmin" class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3">
                <div>
                  <div class="px-3 pt-3">
                    <div class="d-flex mb-4">
                      <div>
                        <label class="mb-2 text-black">Start Date</label>
                        <input class="form-control" type="date" v-model="startDate" @change="fetchRooms(1)">
                      </div>
                      <div class="ms-4">
                        <label class="mb-2 text-black">End Date</label>
                        <input class="form-control" type="date" v-model="endDate" @change="fetchRooms(1)">
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
                  class="form-select form-select-sm" style="width: 90px" v-model.number="perPage">
                  <option :value="5">5</option>
                  <option :value="10">10</option>
                  <option :value="20">20</option>
                  <option :value="50">50</option>
                </select>
              </div>
              <div class="d-flex flex-column align-items-end gap-2">
                <button
                  v-if="!isAdmin"
                  type="button"
                  class="btn btn-primary btn-sm fw-bold"
                  @click="printTable"
                  title="Confirm Booking Document"
                >
                  <i class="fa fa-print me-1"></i> Confirm Booking Document
                </button>
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
                    <th style="width: 130px">User Type</th>
                    <th style="width: 130px">Floor</th>
                    <th style="width: 100px">Room</th>
                    <th style="width: 120px">Seat</th>
                    <th style="width: 180px">Booking Date & Time</th>
                    <th style="width: 150px">Monthly Amount</th>
                    <th style="width: 150px">Development Fee</th>
                    <th style="width: 140px">Check In</th>
                    <th style="width: 140px">Check Out</th>
                    <th style="width: 180px">Email</th>
                    <th v-if="showFamilyColumns" style="width: 160px">Institution Name</th>
                    <th v-if="showFamilyColumns" style="width: 140px">Education System</th>
                    <th v-if="showFamilyColumns" style="width: 140px">Class / Semester</th>
                    <th v-if="showFamilyColumns" style="width: 150px">Father Name</th>
                    <th v-if="showFamilyColumns" style="width: 150px">Mother Name</th>
                    <th v-if="showFamilyColumns" style="width: 140px">Father NID</th>
                    <th v-if="showFamilyColumns" style="width: 140px">NID / Mother NID</th>
                    <th v-if="showFamilyColumns" style="width: 140px">Father Phone</th>
                    <th v-if="showFamilyColumns" style="width: 140px">Mother Phone</th>
                    <th v-if="showNidColumn" style="width: 160px">Workplace Name</th>
                    <th v-if="showNidColumn" style="width: 140px">NID</th>
                    <th style="width: 140px">Phone</th>
                    <th style="width: 120px">Division</th>
                    <th style="width: 120px">District</th>
                    <th style="width: 120px">Thana</th>
                    <th style="width: 160px">Address</th>
                    <th style="width: 120px">Payment</th>
                    <th class="sticky-action-col text-center" style="width: 150px">Action</th>
                  </tr>
                </thead>
                <tbody v-if="rooms.length">
                  <tr v-for="(r, idx) in rooms" :key="r.group_key || idx">
                    <td>{{ from + idx + 1 }}</td>
                    <td>
                      <img v-if="r.image" :src="imageSrc(r.image)" class="img-thumb" alt="booking" />
                      <span v-else class="text-muted small">No image</span>
                    </td>

                    <td>
                      <div class="fw-semibold">{{ r.full_name || "-" }}</div>
                    </td>

                    <td>
                      <span class="badge bg-label-secondary fw-semibold">{{ r.user_type || 'Student' }}</span>
                    </td>

                    <td>
                      <div v-if="r.room_items && r.room_items.length">
                        <div v-for="(item, i) in r.room_items" :key="'f-' + r.id + '-' + i" class="py-1 fw-semibold text-dark">
                          {{ item.floornumber || "-" }}
                        </div>
                      </div>
                      <span v-else>-</span>
                    </td>

                    <td>
                      <div v-if="r.room_items && r.room_items.length">
                        <div v-for="(item, i) in r.room_items" :key="'r-' + r.id + '-' + i" class="py-1">
                          <span class="badge bg-primary font-monospace px-2 py-1 fs-6 fw-bold">{{ getRoomNo(item.roomnumber) }}</span>
                        </div>
                      </div>
                      <span v-else>-</span>
                    </td>

                    <td>
                      <div v-if="r.room_items && r.room_items.length">
                        <div v-for="(item, i) in r.room_items" :key="'s-' + r.id + '-' + i" class="py-1">
                          <span class="badge bg-danger font-monospace px-2 py-1 fs-6 fw-bold">{{ getSeatNo(item.roomnumber, item.room_type, item.seat_no) }}</span>
                        </div>
                      </div>
                      <span v-else>-</span>
                    </td>

                    <td>
                      <span class="fw-semibold">{{ formatDateTime(r.created_at) }}</span>
                    </td>

                    <td>
                      <span class="fw-bold text-success">৳ {{ formatCurrency(r.monthly_amount) }}</span>
                    </td>

                    <td>
                      <span v-if="r.development_fee !== null && r.development_fee !== undefined && Number(r.development_fee) > 0" class="fw-bold text-warning">
                        ৳ {{ formatCurrency(r.development_fee) }}
                      </span>
                      <span v-else-if="r.development_fee !== null && r.development_fee !== undefined" class="fw-semibold text-muted">
                        ৳ {{ formatCurrency(r.development_fee) }}
                      </span>
                      <span v-else class="text-muted">-</span>
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

                    <td v-if="showFamilyColumns">
                      <span class="fw-semibold">{{ isStudent(r) ? (r.institution_name || '-') : '—' }}</span>
                    </td>

                    <td v-if="showFamilyColumns">
                      <span class="fw-semibold">{{ isStudent(r) ? (r.education_level || '-') : '—' }}</span>
                    </td>

                    <td v-if="showFamilyColumns">
                      <span class="fw-semibold">{{ isStudent(r) ? (r.education_class || '-') : '—' }}</span>
                    </td>

                    <td v-if="showFamilyColumns">
                      <span class="fw-semibold">{{ isStudent(r) ? (r.father_name || '-') : '—' }}</span>
                    </td>

                    <td v-if="showFamilyColumns">
                      <span class="fw-semibold">{{ isStudent(r) ? (r.mother_name || '-') : '—' }}</span>
                    </td>

                    <td v-if="showFamilyColumns">
                      <span class="fw-semibold">{{ isStudent(r) ? (r.father_nid || '-') : '—' }}</span>
                    </td>

                    <td v-if="showFamilyColumns">
                      <span class="fw-semibold">{{ isStudent(r) ? (r.mother_nid || '-') : '—' }}</span>
                    </td>

                    <td v-if="showFamilyColumns">
                      <span class="fw-semibold">{{ isStudent(r) ? (r.father_phone || '-') : '—' }}</span>
                    </td>

                    <td v-if="showFamilyColumns">
                      <span class="fw-semibold">{{ isStudent(r) ? (r.mother_phone || '-') : '—' }}</span>
                    </td>

                    <td v-if="showNidColumn">
                      <span class="fw-semibold">{{ isProfessional(r) ? (r.workplace_name || "-") : "—" }}</span>
                    </td>

                    <td v-if="showNidColumn">
                      <span class="fw-semibold">{{ isProfessional(r) ? (r.nid || "-") : "—" }}</span>
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
                      <span class="fw-semibold">{{ r.address || "-" }}</span>
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

                    <td class="sticky-action-col text-center">
                      <button
                        type="button"
                        class="btn btn-sm btn-primary fw-bold text-nowrap"
                        @click="printResidentForm(r)"
                        title="Confirm Booking Document"
                      >
                        <i class="fa fa-print me-1"></i> Confirm Booking Document
                      </button>
                    </td>
                  </tr>
                </tbody>

                <tbody v-else>
                  <tr>
                    <td colspan="32" class="text-center py-4 text-muted">
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
  name: "TodayRoomBookingHistoryList",

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
    showNidColumn() {
      return this.rooms.some(r => this.isProfessional(r));
    },
    showFamilyColumns() {
      return this.rooms.some(r => this.isStudent(r)) || !this.rooms.some(r => this.isProfessional(r));
    },
    totalColumns() {
      let count = 17;
      if (this.showFamilyColumns) count += 9;
      if (this.showNidColumn) count += 2;
      return count;
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
    formatCurrency(val) {
      if (val === null || val === undefined || isNaN(val)) return '0';
      return Number(val).toLocaleString('en-US');
    },

    getRoomNo(str) {
      if (!str) return '-';
      const s = String(str).trim();
      const m1 = s.match(/^([A-Za-z]+)[\-_](\d+)/);       // A-4
      if (m1) return parseInt(m1[2], 10) || m1[2];
      const m2 = s.match(/^(\d+)[\-_ ]*(?:Block)?[\-_ ]*[A-Za-z]/i); // 4-Block-A or 4-A
      if (m2) return parseInt(m2[1], 10) || m2[1];
      return s.split('-')[0] || s;
    },

    getSeatNo(str, roomType, seatNo) {
      if (!str) return '-';
      const s = String(str).trim();
      const rType = (roomType || '').toLowerCase();
      const isSingle = rType.includes('single') || rType.includes('singel');
      const isDouble = rType.includes('double') || rType.includes('doubel');

      let block = null;
      let seatSub = seatNo ? String(seatNo) : null;

      const m1 = s.match(/^\d+[\-_ ]*Block[\-_ ]*([A-Za-z]+)(?:[\-_ ]*(\d+))?/i);
      if (m1) {
        block = m1[1].toUpperCase();
        if (!seatSub && m1[2]) seatSub = m1[2];
      }
      if (!block) {
        const m2 = s.match(/^(?:Block[\-_ ]*)?([A-Za-z]+)[\-_ ]*(\d+)(?:[\-_ ]*(\d+))?/i);
        if (m2) {
          block = m2[1].toUpperCase();
          if (!seatSub && m2[3]) seatSub = m2[3];
        }
      }
      if (!block) {
        const m3 = s.match(/^\d+[\-_]([A-Za-z]+)(?:[\-_](\d+))?/i);
        if (m3) {
          block = m3[1].toUpperCase();
          if (!seatSub && m3[2]) seatSub = m3[2];
        }
      }
      if (!block) return s;
      if (isSingle) return `Block ${block}`;
      if (isDouble && seatSub) return `Block ${block}-${seatSub}`;
      if (isDouble) return `Block ${block}`;
      if (seatSub) return `Block ${block}-${seatSub}`;
      return `Block ${block}`;
    },

    isStudent(r) {
      if (!r || !r.user_type) return true;
      return r.user_type.toLowerCase() === 'student';
    },

    isProfessional(r) {
      if (!r || !r.user_type) return false;
      return r.user_type.toLowerCase() === 'working professional';
    },

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
      if (isNaN(dateObj.getTime())) return "-";
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

    getAdvanceDepositAmount(r) {
      if (!r) return 0;
      if (r.room_items && r.room_items.length) {
        let sum = 0;
        r.room_items.forEach(item => {
          const p = Number(item.advance_price ?? item.original_advance_price ?? item.price ?? 0);
          sum += p;
        });
        if (sum > 0) return sum;
      }
      if (r.advance_fee !== null && r.advance_fee !== undefined && Number(r.advance_fee) > 0) {
        return Number(r.advance_fee);
      }
      if (r.roomprice !== null && r.roomprice !== undefined && Number(r.roomprice) > 0) {
        return Number(r.roomprice);
      }
      return Number(r.monthly_amount || 0);
    },

    getDevFeeAmount(r) {
      if (!r) return 0;
      if (r.development_fee === null || r.development_fee === undefined) return 0;
      return Number(r.development_fee || 0);
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

        const res = await axios.get(this.endpoint("today-get-room-booking"), {
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
      if (this.rooms && this.rooms.length > 0) {
        this.printResidentForm(this.rooms[0]);
      } else {
        this.toast("প্রিন্ট করার মতো কোনো বুকিং ডাটা পাওয়া যায়নি", "warning");
      }
    },

    printResidentForm(r) {
      const logoUrl = window.location.origin + '/logo/logoimage (2).png';
      const userImgUrl = r.image ? this.imageSrc(r.image) : '';
      const roomNo = (r.room_items && r.room_items.length)
        ? r.room_items.map(i => this.getRoomNo(i.roomnumber)).join(', ')
        : (this.getRoomNo(r.roomnumber) || r.room_number || '-');
      const seatNo = (r.room_items && r.room_items.length)
        ? r.room_items.map(i => this.getSeatNo(i.roomnumber)).join(', ')
        : (this.getSeatNo(r.roomnumber) || '-');
      const floorNo = (r.room_items && r.room_items.length)
        ? [...new Set(r.room_items.map(i => i.floornumber))].filter(Boolean).join(', ')
        : (r.floornumber || '-');
      const fullName = r.full_name || '-';
      const phone = r.phone || '-';
      const address = r.address || '-';
      const thanaName = r.thana_name || '-';
      const districtName = r.district_name || '-';
      const bookingDate = r.created_at
        ? new Date(r.created_at).toLocaleString('bn-BD', { dateStyle: 'long', timeStyle: 'short' })
        : (r.booking_date || '-');

      const isProf = r.user_type && (
        r.user_type.toLowerCase().includes('professional') ||
        r.user_type.toLowerCase().includes('job') ||
        r.user_type.toLowerCase().includes('passenger')
      );

      const advance = this.getAdvanceDepositAmount(r);
      const devFee = this.getDevFeeAmount(r);
      const totalAmount = advance + devFee;

      const devFeeDocHtml = devFee > 0 ? `
        <div style="display: flex; justify-content: space-between; font-size: 14.5px; padding: 3px 0;">
          <span style="color: #b45309; font-weight: 700;">Development Fee (One-time):</span>
          <span style="font-weight: 800; color: #d97706;">+ ৳ ${this.formatCurrency(devFee)}</span>
        </div>
        <hr style="border-top: 1.5px dashed #f59e0b; margin: 6px 0; opacity: 0.7;">
      ` : '';

      const feeSectionHtml = `
        <div class="section-title" style="margin-top: 10px; margin-bottom: 8px;">পেমেন্ট ও ফি বিবরণী</div>
        <div style="background-color: #fdf8e6; border: 2px solid #f59e0b; border-radius: 10px; padding: 10px 18px; margin-bottom: 10px;">
          <div style="display: flex; justify-content: space-between; font-size: 14.5px; padding: 3px 0;">
            <span style="color: #555; font-weight: 700;">Advance Deposit / Room Price:</span>
            <span style="font-weight: 800; color: #111;">৳ ${this.formatCurrency(advance)}</span>
          </div>
          ${devFeeDocHtml}
          <div style="display: flex; justify-content: space-between; font-size: 16px; font-weight: 800; padding-top: 2px;">
            <span style="color: #111;">Total Amount Payable:</span>
            <span style="color: #059669; font-size: 18px;">৳ ${this.formatCurrency(totalAmount)}</span>
          </div>
        </div>
      `;

      const docTitle = isProf ? 'কর্মজীবীর তথ্য - টি এস এস ভিলা' : 'শিক্ষার্থীর তথ্য - টি এস এস ভিলা';
      const sectionTitleText = isProf ? 'কর্মজীবীর তথ্য' : 'শিক্ষার্থীর তথ্য';
      const signatureLabelText = isProf ? 'কর্মজীবীর স্বাক্ষর' : 'শিক্ষার্থীর স্বাক্ষর';

      const institutionName = r.institution_name || '-';
      const fatherName = r.father_name || '-';
      const fatherPhone = r.father_phone || '-';
      const motherName = r.mother_name || '-';
      const motherPhone = r.mother_phone || '-';

      let infoSectionHtml = '';
      if (isProf) {
        infoSectionHtml = `
          <div class="form-pill-row">
            <div class="pill-lbl">কর্মজীবীর পূর্ণ নাম :</div>
            <div class="pill-val">${fullName}</div>
            <div class="pill-right">
              <div class="pill-lbl">মোবাইল নং :</div>
              <div class="pill-val">${phone}</div>
            </div>
          </div>

          <div class="form-pill-row">
            <div class="pill-lbl">কর্মপ্রতিষ্ঠানের নাম:</div>
            <div class="pill-val">${r.workplace_name || '-'}</div>
            <div class="pill-right">
              <div class="pill-lbl">NID নম্বর :</div>
              <div class="pill-val">${r.nid || '-'}</div>
            </div>
          </div>

          ${(fatherName !== '-' || motherName !== '-') ? `
          <div class="form-pill-row">
            <div class="pill-lbl">পিতার নাম:</div>
            <div class="pill-val">${fatherName}</div>
            <div class="pill-right">
              <div class="pill-lbl">মোবাইল নং :</div>
              <div class="pill-val">${fatherPhone}</div>
            </div>
          </div>
          <div class="form-pill-row">
            <div class="pill-lbl">মাতার নাম:</div>
            <div class="pill-val">${motherName}</div>
            <div class="pill-right">
              <div class="pill-lbl">মোবাইল নং :</div>
              <div class="pill-val">${motherPhone}</div>
            </div>
          </div>
          ` : ''}
        `;
      } else {
        infoSectionHtml = `
          <div class="form-pill-row">
            <div class="pill-lbl">শিক্ষার্থীর পূর্ণ নাম :</div>
            <div class="pill-val">${fullName}</div>
            <div class="pill-right">
              <div class="pill-lbl">মোবাইল নং :</div>
              <div class="pill-val">${phone}</div>
            </div>
          </div>

          <div class="form-pill-row">
            <div class="pill-lbl">অধ্যয়নরত শিক্ষা প্রতিষ্ঠানের নাম:</div>
            <div class="pill-val">${institutionName}</div>
          </div>

          <div class="form-pill-row">
            <div class="pill-lbl">পিতার নাম:</div>
            <div class="pill-val">${fatherName}</div>
            <div class="pill-right">
              <div class="pill-lbl">মোবাইল নং :</div>
              <div class="pill-val">${fatherPhone}</div>
            </div>
          </div>

          <div class="form-pill-row">
            <div class="pill-lbl">মাতার নাম:</div>
            <div class="pill-val">${motherName}</div>
            <div class="pill-right">
              <div class="pill-lbl">মোবাইল নং :</div>
              <div class="pill-val">${motherPhone}</div>
            </div>
          </div>
        `;
      }

      const html = `
        <!DOCTYPE html>
        <html lang="bn">
        <head>
          <meta charset="UTF-8">
          <title>${docTitle}</title>
          <style>
            @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700;800&family=Tiro+Bangla&display=swap');
            @page {
              size: A4 portrait;
              margin: 5mm;
            }

            * { box-sizing: border-box; margin: 0; padding: 0; }

            html, body {
              background: #fff;
              font-family: 'Hind Siliguri', 'Tiro Bangla', sans-serif;
              padding: 0;
              margin: 0;
              color: #000;
              -webkit-print-color-adjust: exact;
              print-color-adjust: exact;
            }

            /* ======= OUTER FRAME ======= */
            .paper-frame {
              background: linear-gradient(165deg, #fef9e7 0%, #fef3cd 50%, #fef9e7 100%);
              border: 4px solid #27ae60;
              border-radius: 10px;
              padding: 16px 20px 14px 20px;
              box-sizing: border-box;
              position: relative;
              min-height: 275mm;
              display: flex;
              flex-direction: column;
              justify-content: space-between;
              page-break-inside: avoid;
            }
            .paper-frame::before {
              content: '';
              position: absolute;
              inset: 4px;
              border: 1.5px solid #f39c12;
              border-radius: 7px;
              pointer-events: none;
            }

            /* ======= HEADER ======= */
            .top-header {
              display: flex;
              align-items: center;
              justify-content: center;
              gap: 16px;
              margin-bottom: 8px;
              padding: 0 4px;
            }
            .logo-wrap img { width: 72px; height: 72px; object-fit: contain; }
            .brand-center { text-align: center; flex: 1; }
            .brand-name {
              font-size: 46px;
              font-weight: 900;
              color: #c0392b;
              font-family: 'Tiro Bangla', serif;
              line-height: 1;
              letter-spacing: 1px;
            }
            .photo-box {
              width: 105px;
              height: 120px;
              border: 2px solid #2c3e50;
              border-radius: 4px;
              display: flex;
              align-items: center;
              justify-content: center;
              overflow: hidden;
              background: #f0f0f0;
              flex-shrink: 0;
            }
            .photo-box img { width: 100%; height: 100%; object-fit: cover; }
            .photo-placeholder { font-size: 11px; color: #888; text-align: center; }

            /* ======= ADDRESS BANNER ======= */
            .address-banner {
              background: #1a237e;
              color: #fff;
              text-align: center;
              padding: 8px 12px;
              font-size: 13px;
              font-weight: 600;
              border-radius: 5px;
              margin-bottom: 10px;
              letter-spacing: 0.2px;
            }

            /* ======= ROOM META ======= */
            .room-meta-row {
              display: flex;
              gap: 12px;
              margin-bottom: 10px;
            }
            .room-meta-box {
              flex: 1;
              border: 2px solid #27ae60;
              border-radius: 6px;
              padding: 7px 12px;
              background: #fff;
              font-size: 13.5px;
              font-weight: 600;
              display: flex;
              align-items: center;
              gap: 5px;
            }
            .room-meta-box .lbl { color: #1a5c2e; font-weight: 700; white-space: nowrap; }
            .room-meta-box .val { color: #000; font-weight: 800; font-size: 14px; }

            /* ======= SECTION TITLES ======= */
            .section-title-container {
              display: flex;
              align-items: center;
              justify-content: space-between;
              border-bottom: 2.5px solid #f39c12;
              padding-bottom: 3px;
              margin: 10px 0 8px 0;
            }
            .title-side-dummy {
              flex: 1;
            }
            .section-title-container .section-title {
              text-align: center;
              font-size: 21.5px;
              font-weight: 800;
              color: #e74c3c;
              font-family: 'Tiro Bangla', serif;
              letter-spacing: 0.3px;
              flex: 2;
              margin: 0;
              padding-bottom: 0;
              border-bottom: none;
            }
            .booking-date-right-box {
              flex: 1;
              text-align: right;
              font-size: 13px;
              font-weight: 700;
              white-space: nowrap;
            }
            .booking-date-right-box .lbl {
              color: #1a5c2e;
              font-weight: 800;
            }
            .booking-date-right-box .val {
              color: #000;
              font-weight: 800;
            }

            .section-title {
              text-align: center;
              font-size: 21px;
              font-weight: 800;
              color: #e74c3c;
              font-family: 'Tiro Bangla', serif;
              border-bottom: 2.5px solid #f39c12;
              padding-bottom: 3px;
              margin: 10px 0 8px 0;
              letter-spacing: 0.3px;
            }

            /* ======= PILL ROWS ======= */
            .form-pill-row {
              border: 2px solid #8e44ad;
              border-radius: 25px;
              background: #fff;
              display: flex;
              overflow: hidden;
              margin-bottom: 8px;
              min-height: 40px;
              align-items: stretch;
            }
            .pill-lbl {
              background: #7b1fa2;
              color: #fff;
              padding: 8px 16px;
              font-size: 13.5px;
              font-weight: 700;
              white-space: nowrap;
              display: flex;
              align-items: center;
              border-right: 2px solid #8e44ad;
              flex-shrink: 0;
            }
            .pill-val {
              padding: 8px 16px;
              font-size: 14px;
              font-weight: 600;
              color: #111;
              flex-grow: 1;
              display: flex;
              align-items: center;
            }
            .pill-right {
              border-left: 2px solid #8e44ad;
              display: flex;
              align-items: stretch;
              flex-shrink: 0;
            }

            /* ======= ADDRESS ======= */
            .address-row {
              border: 2px solid #8e44ad;
              border-radius: 25px;
              background: #fff;
              display: flex;
              flex-wrap: wrap;
              padding: 10px 18px;
              font-size: 13.5px;
              font-weight: 600;
              gap: 10px 28px;
              margin-bottom: 8px;
              align-items: center;
              min-height: 40px;
            }
            .akey { color: #7b1fa2; font-weight: 700; }

            /* ======= RULES ======= */
            .rules-box {
              background: #fffde7;
              border: 2px solid #f48fb1;
              border-radius: 12px;
              padding: 12px 18px;
              margin-top: 2px;
              margin-bottom: 6px;
            }
            .rules-list { list-style: none; padding: 0; margin: 0; }
            .rules-list li {
              font-size: 12.5px;
              font-weight: 600;
              color: #1a1a1a;
              line-height: 1.75;
              border-bottom: 1px dashed #f8bbd0;
              padding-bottom: 3px;
              margin-bottom: 3px;
            }
            .rules-list li:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }

            /* ======= SIGNATURE ======= */
            .signature-row {
              display: flex;
              justify-content: space-between;
              align-items: flex-end;
              margin-top: auto;
              padding: 10px 32px 6px 32px;
            }
            .sig-box { text-align: center; min-width: 140px; }
            .sig-line { border-top: 1.5px solid #2c3e50; margin-bottom: 5px; }
            .sig-text { font-size: 13.5px; font-weight: 700; color: #1a1a1a; }

            @media print {
              html, body {
                background: #fff;
              }
              .paper-frame {
                border-radius: 0;
                box-sizing: border-box;
                page-break-inside: avoid !important;
                page-break-after: avoid !important;
                page-break-before: avoid !important;
              }
            }
          </style>
        </head>
        <body>
          <div class="paper-frame">

            <!-- HEADER -->
            <div class="top-header">
              <div class="logo-wrap">
                <img src="${logoUrl}" alt="Logo" onerror="this.style.display='none'">
              </div>
              <div class="brand-center">
                <div class="brand-name">টি এস এস ভিলা</div>
              </div>
              <div class="photo-box">
                ${userImgUrl
                  ? `<img src="${userImgUrl}" alt="Photo">`
                  : `<div class="photo-placeholder">ছবি<br>Photo</div>`}
              </div>
            </div>

            <!-- BANNER -->
            <div class="address-banner">
              কলেজ রোড , নেসকো গেট সংলগ্ন , রংপুর &nbsp;|&nbsp; প্রয়োজনে: ০১৯৭৭২৭০৯২০ &nbsp;|&nbsp; Gmail: tssvilla2026@gmail.com
            </div>

            <!-- ROOM / BLOCK / FLOOR -->
            <div class="room-meta-row">
              <div class="room-meta-box"><span class="lbl">রুম নং:</span>&nbsp;<span class="val">${roomNo}</span></div>
              <div class="room-meta-box"><span class="lbl">ব্লক নং:</span>&nbsp;<span class="val">${seatNo}</span></div>
              <div class="room-meta-box"><span class="lbl">ফ্লোর নং:</span>&nbsp;<span class="val">${floorNo}</span></div>
            </div>

            <!-- SECTION: INFO HEADER WITH BOOKING DATE ON RIGHT -->
            <div class="section-title-container">
              <div class="title-side-dummy"></div>
              <div class="section-title">${sectionTitleText}</div>
              <div class="booking-date-right-box">
                <span class="lbl">বুকিং তারিখ:</span> <span class="val">${bookingDate}</span>
              </div>
            </div>

            ${infoSectionHtml}

            <!-- SECTION: ADDRESS -->
            <div class="section-title">স্থায়ী ঠিকানা</div>

            <div class="address-row">
              <span><span class="akey">গ্রাম/রাস্তা:</span> ${address}</span>
              <span><span class="akey">থানা:</span> ${thanaName}</span>
              <span><span class="akey">উপজেলা:</span> ${thanaName}</span>
              <span><span class="akey">জেলা:</span> ${districtName}</span>
            </div>

            <!-- SECTION: RULES -->
            <div class="section-title">নিয়মাবলী</div>

            <div class="rules-box">
              ${isProf ? `
              <ul class="rules-list">
                <li>* মেসের ভাড়া ০৭ তারিখের মধ্যে পরিশোধ করতে হবে।</li>
                <li>* মেস ছাড়লে ০২ মাস পূর্বেই মেস কর্তৃপক্ষকে জানাতে হবে। অন্যথায় দুই মাসের ভাড়া দিয়ে মেস ছাড়তে হবে।</li>
                <li>* রুম চেঞ্জ করতে চাইলে ৫০০ টাকা জরিমানা প্রদান করতে হবে।</li>
                <li>* মেসের নিয়ম-কানুন মেনে চলতে হবে। কারো বিরুদ্ধে কোনো অভিযোগ আসলে এবং তা প্রমাণিত হলে সিট বাতিলসহ যেকোনো ব্যবস্থা নেয়ার অধিকার কর্তৃপক্ষ রাখে।</li>
              </ul>
              ` : `
              <ul class="rules-list">
                <li>* মেসের ভাড়া ০৭ তারিখের মধ্যে পরিশোধ করতে হবে।</li>
                <li>* মেস ছাড়লে ০২ মাস পূর্বেই মেস কর্তৃপক্ষকে জানাতে হবে। অন্যথায় দুই মাসের ভাড়া দিয়ে মেস ছাড়তে হবে।</li>
                <li>* মাগরিবের আযানের পর মেসের বাহিরে থাকলে অভিভাবককে জানিয়ে দিতে হবে।</li>
                <li>* রুম চেঞ্জ করতে চাইলে ৫০০ টাকা জরিমানা প্রদান করতে হবে।</li>
                <li>* মেসের নিয়ম-কানুন মেনে চলতে হবে। কারো বিরুদ্ধে কোনো অভিযোগ আসলে এবং তা প্রমাণিত হলে সিট বাতিলসহ যেকোনো ব্যবস্থা নেয়ার অধিকার কর্তৃপক্ষ রাখে।</li>
              </ul>
              `}
            </div>

            <!-- SIGNATURE -->
            <div class="signature-row">
              <div class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-text">অনুমোদিত স্বাক্ষর</div>
              </div>
              <div class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-text">${signatureLabelText}</div>
              </div>
            </div>

          </div>
        </body>
        </html>
      `;

      if (typeof window.printOrDownloadPdf === 'function') {
        window.printOrDownloadPdf(html, 'Today_Booking_Document');
        return;
      }

      let iframe = document.getElementById("print-resident-iframe");
      if (!iframe) {
        iframe = document.createElement("iframe");
        iframe.id = "print-resident-iframe";
        iframe.style.position = "absolute";
        iframe.style.width = "0";
        iframe.style.height = "0";
        iframe.style.border = "none";
        document.body.appendChild(iframe);
      }

      const doc = iframe.contentWindow.document;
      doc.open();
      doc.write(html);
      doc.close();

      setTimeout(() => {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
      }, 400);
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
  border: 1px solid #d1d5db;
}

.form-control:focus,
.form-select:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}

.table th {
  font-size: 0.75rem;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: #374151;
  background: #f9fafb;
  white-space: nowrap;
}

.table td {
  font-size: 0.875rem;
  color: #111827;
  vertical-align: middle;
  white-space: nowrap;
}

.badge {
  font-weight: 500;
  border-radius: 6px;
}

.sticky-action-col {
  position: sticky;
  right: 0;
  background: #fff;
  z-index: 2;
  box-shadow: -2px 0 6px rgba(0, 0, 0, 0.05);
}
</style>