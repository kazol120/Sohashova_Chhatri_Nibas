<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-10 col-xl-12">
        <div class="card mb-4 shadow-sm">
          <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3">
            <div>
              <h5 class="card-title mb-0">Room Booking History </h5>
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
                    <th style="width: 130px">User Type</th>
                    <th style="width: 130px">Floor</th>
                    <th style="width: 100px">Room</th>
                    <th style="width: 120px">Seat</th>
                    <th style="width: 180px">Booking Date & Time</th>
                    <th style="width: 150px">Monthly Amount</th>
                    <th style="width: 150px">Development Fee</th>
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
                    <th style="width: 110px">Action</th>
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
                          <span class="badge bg-danger font-monospace px-2 py-1 fs-6 fw-bold">{{ getSeatNo(item.roomnumber) }}</span>
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

                    <td class="text-center">
                      <button
                        type="button"
                        class="btn btn-sm btn-primary fw-bold text-nowrap"
                        @click="printResidentForm(r)"
                        title="Print Form"
                      >
                        <i class="fa fa-print me-1"></i> Print
                      </button>
                    </td>
                  </tr>
                </tbody>

                <tbody v-else>
                  <tr>
                    <td :colspan="totalColumns" class="text-center py-4 text-muted">
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
    showNidColumn() {
      return this.rooms.some(r => this.isProfessional(r));
    },
    showFamilyColumns() {
      return this.rooms.some(r => this.isStudent(r)) || !this.rooms.some(r => this.isProfessional(r));
    },
    totalColumns() {
      let count = 17; // base columns
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
      const parts = String(str).split('-');
      return parts[0] || str;
    },

    getSeatNo(str) {
      if (!str) return '-';
      const parts = String(str).split('-');
      if (parts.length > 1) {
        return parts.slice(1).join('-');
      }
      return '-';
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

    printResidentForm(r) {
      const logoUrl = this.url + '/backend/img/logo.png';
      const userImgUrl = r.image ? this.imageSrc(r.image) : '';
      const roomNo = this.getRoomNo(r.roomnumber) || (r.room_number || '-');
      const seatNo = this.getSeatNo(r.roomnumber) || '-';
      const floorNo = r.floornumber || '-';
      const fullName = r.full_name || '-';
      const phone = r.phone || '-';
      const institutionName = r.institution_name || (r.workplace_name || '-');
      const fatherName = r.father_name || '-';
      const fatherPhone = r.father_phone || '-';
      const motherName = r.mother_name || '-';
      const motherPhone = r.mother_phone || '-';
      const address = r.address || '-';
      const thanaName = r.thana_name || '-';
      const districtName = r.district_name || '-';

      const html = `
        <!DOCTYPE html>
        <html lang="bn">
        <head>
          <meta charset="UTF-8">
          <title>শিক্ষার্থীর তথ্য - টি এস এস ভিলা</title>
          <style>
            @import url('https://fonts.googleapis.com/css2?family=Tiro+Bangla&family=Hind+Siliguri:wght@400;500;600;700&display=swap');
            
            * { box-sizing: border-box; margin: 0; padding: 0; }
            body {
              background: #ffffff;
              font-family: 'Hind Siliguri', 'Tiro Bangla', 'Segoe UI', Arial, sans-serif;
              padding: 10px;
              color: #000;
            }

            .paper-frame {
              background: #fffdf2;
              border: 3px solid #f472b6;
              border-radius: 16px;
              padding: 16px;
              position: relative;
              max-width: 800px;
              margin: 0 auto;
            }

            /* Top Header */
            .top-header {
              display: flex;
              align-items: center;
              justify-content: space-between;
              margin-bottom: 8px;
            }
            .brand-logo-title {
              display: flex;
              align-items: center;
              gap: 10px;
            }
            .brand-logo-title img {
              width: 55px;
              height: 55px;
              object-fit: contain;
            }
            .brand-name {
              font-size: 38px;
              font-weight: 800;
              color: #e11d48;
              font-family: 'Tiro Bangla', serif;
              line-height: 1;
            }
            .photo-box {
              width: 105px;
              height: 125px;
              border: 2px solid #334155;
              border-radius: 4px;
              display: flex;
              align-items: center;
              justify-content: center;
              overflow: hidden;
              background: #fff;
            }
            .photo-box img {
              width: 100%;
              height: 100%;
              object-fit: cover;
            }

            /* Banner Bar */
            .address-banner {
              background: #0e2680;
              color: #ffffff;
              text-align: center;
              padding: 6px 10px;
              font-size: 13px;
              font-weight: 500;
              border-radius: 4px;
              margin-bottom: 14px;
            }

            /* Room/Block/Floor Boxes */
            .room-meta-row {
              display: flex;
              justify-content: space-between;
              gap: 12px;
              margin-bottom: 16px;
            }
            .room-meta-box {
              flex: 1;
              border: 2px solid #10b981;
              border-radius: 6px;
              padding: 5px 10px;
              background: #fff;
              font-size: 14px;
              font-weight: 700;
              color: #065f46;
              display: flex;
              align-items: center;
              gap: 6px;
            }
            .room-meta-box span.val {
              font-weight: 700;
              color: #000;
            }

            /* Section Titles */
            .section-pill-title {
              display: table;
              margin: 0 auto 12px auto;
              background: #f97316;
              color: #ffffff;
              font-size: 18px;
              font-weight: 700;
              padding: 4px 28px;
              border-radius: 20px;
              box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }

            /* Form Rows (Purple Pills) */
            .form-pill-row {
              border: 2px solid #8b5cf6;
              border-radius: 20px;
              background: #ffffff;
              display: flex;
              overflow: hidden;
              margin-bottom: 10px;
              align-items: center;
              min-height: 38px;
            }
            .pill-lbl {
              background: #6b21a8;
              color: #ffffff;
              padding: 6px 14px;
              font-size: 14px;
              font-weight: 600;
              white-space: nowrap;
              display: flex;
              align-items: center;
            }
            .pill-val {
              padding: 6px 14px;
              font-size: 14px;
              font-weight: 600;
              color: #0f172a;
              flex-grow: 1;
            }
            .pill-split {
              border-left: 2px solid #8b5cf6;
              display: flex;
              align-items: center;
              flex-grow: 1;
            }

            /* Address Row */
            .address-grid {
              border: 2px solid #8b5cf6;
              border-radius: 20px;
              background: #ffffff;
              display: flex;
              flex-wrap: wrap;
              padding: 8px 14px;
              font-size: 14px;
              font-weight: 600;
              gap: 15px;
              margin-bottom: 14px;
            }

            /* Rules Box */
            .rules-container {
              background: #fef08a;
              border: 1.5px solid #f472b6;
              border-radius: 20px;
              padding: 12px 20px;
              margin-top: 10px;
              margin-bottom: 25px;
            }
            .rules-list {
              list-style: none;
              padding: 0;
              margin: 0;
            }
            .rules-list li {
              font-size: 12.5px;
              font-weight: 600;
              color: #1e293b;
              line-height: 1.6;
            }

            /* Signatures */
            .signature-row {
              display: flex;
              justify-content: space-between;
              align-items: flex-end;
              margin-top: 35px;
              padding: 0 20px 10px 20px;
            }
            .sig-box {
              text-align: center;
              min-width: 150px;
            }
            .sig-line {
              border-top: 1.5px solid #475569;
              margin-bottom: 4px;
              width: 100%;
            }
            .sig-text {
              font-size: 13px;
              font-weight: 700;
              color: #1e293b;
            }

            @media print {
              body { padding: 0; background: #fff; }
              .paper-frame { border-radius: 0; max-width: 100%; width: 100%; margin: 0; }
            }
          </style>
        </head>
        <body>
          <div class="paper-frame">
            <!-- Top Header -->
            <div class="top-header">
              <div class="brand-logo-title">
                <img src="${logoUrl}" alt="Logo" onerror="this.style.display='none'">
                <div class="brand-name">টি এস এস ভিলা</div>
              </div>
              <div class="photo-box">
                ${userImgUrl ? `<img src="${userImgUrl}" alt="Student Photo">` : '<span style="font-size:12px;color:#94a3b8">ছবি</span>'}
              </div>
            </div>

            <!-- Banner Bar -->
            <div class="address-banner">
              কলেজ রোড, নেসকো গেট সংলগ্ন, রংপুর। প্রয়োজনে: ০১৯৭৭২৭০৯২০ &nbsp; Gmail: tssvilla2026@gmail.com
            </div>

            <!-- Room Meta -->
            <div class="room-meta-row">
              <div class="room-meta-box">রুম নং: <span class="val">${roomNo}</span></div>
              <div class="room-meta-box">ব্লক নং: <span class="val">${seatNo}</span></div>
              <div class="room-meta-box">ফ্লোর নং: <span class="val">${floorNo}</span></div>
            </div>

            <!-- Section 1: Student Info -->
            <div class="section-pill-title">শিক্ষার্থীর তথ্য</div>

            <div class="form-pill-row">
              <div class="pill-lbl">শিক্ষার্থীর পূর্ণ নাম :</div>
              <div class="pill-val">${fullName}</div>
              <div class="pill-split">
                <div class="pill-lbl">মোবাইল নং :</div>
                <div class="pill-val">${phone}</div>
              </div>
            </div>

            <div class="form-pill-row">
              <div class="pill-lbl">অধ্যয়নরত শিক্ষা প্রতিষ্ঠানের নাম:</div>
              <div class="pill-val">${institutionName}</div>
            </div>

            <div class="form-pill-row">
              <div class="pill-lbl">পিতার নাম:</div>
              <div class="pill-val">${fatherName}</div>
              <div class="pill-split">
                <div class="pill-lbl">মোবাইল নং :</div>
                <div class="pill-val">${fatherPhone}</div>
              </div>
            </div>

            <div class="form-pill-row">
              <div class="pill-lbl">মাতার নাম:</div>
              <div class="pill-val">${motherName}</div>
              <div class="pill-split">
                <div class="pill-lbl">মোবাইল নং :</div>
                <div class="pill-val">${motherPhone}</div>
              </div>
            </div>

            <!-- Section 2: Address -->
            <div class="section-pill-title" style="margin-top: 14px;">স্থায়ী ঠিকানা</div>

            <div class="address-grid">
              <div>গ্রাম: <span style="font-weight:700">${address}</span></div>
              <div>থানা: <span style="font-weight:700">${thanaName}</span></div>
              <div>উপজেলা: <span style="font-weight:700">${thanaName}</span></div>
              <div>জেলা: <span style="font-weight:700">${districtName}</span></div>
            </div>

            <!-- Section 3: Rules -->
            <div class="section-pill-title" style="margin-top: 10px;">নিয়মাবলী</div>

            <div class="rules-container">
              <ul class="rules-list">
                <li>* মেসের ভাড়া ০৭ তারিখের মধ্যে পরিশোধ করতে হবে। ০৭ তারিখ পার হলে ২৪ ঘণ্টা পর পর ৫০ টাকা করে জরিমানা।</li>
                <li>* মেস ছাড়লে ০২ মাস পূর্বেই মেস কর্তৃপক্ষকে জানাতে হবে। অন্যথায় দুই মাসের ভাড়া দিয়ে মেস ছাড়তে হবে।</li>
                <li>* মাগরিবের আযানের পর মেসের বাহিরে থাকলে অভিভাবককে জানিয়ে দিতে হবে।</li>
                <li>* রুম চেঞ্জ করতে চাইলে ৫০০ টাকা জরিমানা প্রদান করতে হবে।</li>
                <li>* মেসের নিয়ম-কানুন মেনে চলতে হবে। কারও বিরুদ্ধে কোনো অভিযোগ আসলে এবং তা প্রমাণিত হলে সিট বাতিলসহ যেকোন ব্যবস্থা নেয়া অধিকার কর্তৃপক্ষ রাখে।</li>
              </ul>
            </div>

            <!-- Footers -->
            <div class="signature-row">
              <div class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-text">অনুমোদিত স্বাক্ষর</div>
              </div>
              <div class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-text">শিক্ষার্থীর স্বাক্ষর</div>
              </div>
            </div>

          </div>
        </body>
        </html>
      `;

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