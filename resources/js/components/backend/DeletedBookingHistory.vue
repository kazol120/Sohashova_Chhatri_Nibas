<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
      <div class="col-12">
        <div class="card mb-4 shadow-sm border-0">
          
          <!-- Header -->
          <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3 bg-light-danger-gradient">
            <h5 class="card-title mb-0 fw-bold text-danger">
              <i class="ti ti-trash me-2"></i> Deleted Booking History (ডিলেট হিস্টোরি)
            </h5>
            <span class="badge bg-danger fs-7 px-3 py-2">
              <i class="ti ti-archive me-1"></i> Trashed Bookings
            </span>
          </div>

          <!-- Stats Cards -->
          <div class="card-body bg-light-gray-50 border-bottom py-3">
            <div class="row g-3">
              <div class="col-sm-6 col-md-4">
                <div class="d-flex align-items-center p-3 bg-white rounded shadow-sm hover-grow transition-all">
                  <div class="icon-avatar bg-danger-light me-3">
                    <i class="ti ti-user-x text-danger fs-3"></i>
                  </div>
                  <div>
                    <h6 class="mb-0 text-muted fs-6">Total Deleted Records</h6>
                    <h4 class="mb-0 fw-bold text-dark mt-1">{{ pagination.total }}</h4>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Search & Filter Bar -->
          <div class="card-header border-bottom py-3">
            <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between">
              <div>
                <h6 class="text-muted mb-0 fw-bold">All Deleted Guest Bookings</h6>
                <small class="text-muted">এখান থেকে মুছে ফেলা বুকিং পুনরায় সিট বুকিং বা স্থায়ীভাবে ডিলিট করতে পারবেন</small>
              </div>

              <!-- Search Input -->
              <div class="search-box">
                <div class="input-group input-group-sm" style="max-width: 280px;">
                  <span class="input-group-text bg-white border-end-0">
                    <i class="ti ti-search text-muted"></i>
                  </span>
                  <input 
                    type="text" 
                    class="form-control border-start-0 ps-1 rounded-end" 
                    placeholder="Search by Name, Phone, Room..." 
                    v-model="search"
                    @input="onSearchInput"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Table -->
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="bg-danger-header text-white">
                  <tr>
                    <th class="text-center" style="width: 50px;">SL</th>
                    <th>Resident Name</th>
                    <th>Contact</th>
                    <th class="text-center">Previous Seat</th>
                    <th class="text-center">Deleted At</th>
                    <th>Permanent Address</th>
                    <th class="text-center" style="min-width: 200px;">Action</th>
                  </tr>
                </thead>

                <tbody v-if="!loading && history.length">
                  <tr v-for="(r, idx) in history" :key="r.id" class="transition-all hover-row">
                    <td class="text-center fw-semibold text-muted">
                      {{ (pagination.current_page - 1) * pagination.per_page + idx + 1 }}
                    </td>
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="avatar-wrapper me-2">
                          <img 
                            v-if="r.image" 
                            :src="'/bookingsimage/' + r.image" 
                            alt="Avatar" 
                            class="rounded-circle border border-2 border-danger"
                            style="width: 38px; height: 38px; object-fit: cover;"
                          />
                          <div 
                            v-else 
                            class="avatar-fallback rounded-circle bg-danger text-white fw-bold text-center border border-2 border-danger"
                            style="width: 38px; height: 38px; line-height: 34px;"
                          >
                            {{ r.full_name ? r.full_name.charAt(0).toUpperCase() : '?' }}
                          </div>
                        </div>
                        <div>
                          <div class="fw-bold text-dark">{{ r.full_name }}</div>
                          <span class="badge bg-secondary-light text-muted uppercase-badge" style="font-size: 10px;">{{ r.user_type || 'student' }}</span>
                        </div>
                      </div>
                    </td>
                    <td>
                      <div class="fw-semibold text-dark">{{ r.phone }}</div>
                      <small v-if="r.email" class="text-muted">{{ r.email }}</small>
                    </td>
                    <td class="text-center">
                      <div v-if="r.room_items && r.room_items.length" class="d-inline-flex flex-column gap-1">
                        <div v-for="(item, i) in r.room_items" :key="i" class="d-inline-flex align-items-center gap-1 justify-content-center">
                          <span class="badge bg-light text-dark fw-bold text-uppercase" style="border: 1px solid #ddd; font-size: 11px;">
                            {{ item.floornumber }}
                          </span>
                          <span class="badge bg-dark text-white fw-bold" style="font-size: 11px;">
                            {{ (item.roomnumber || '').split('-')[0] }}
                          </span>
                          <span class="badge bg-info text-white fw-bold" style="font-size: 11px;">
                            {{ formatSeatBadge(item) }}
                          </span>
                        </div>
                      </div>
                      <span v-else class="text-muted">-</span>
                    </td>
                    <td class="text-center text-muted fw-semibold" style="font-size: 12px;">
                      {{ formatDateTime(r.deleted_at_str || r.deleted_at) }}
                    </td>
                    <td>
                      <div class="small text-muted" style="max-width: 220px; line-height: 1.3;">
                        <span v-if="r.thana_name">{{ r.thana_name }}, </span>
                        <span v-if="r.district_name">{{ r.district_name }}</span>
                        <div v-if="r.address" class="text-truncate">{{ r.address }}</div>
                      </div>
                    </td>
                    <td class="text-center text-nowrap">
                      <div class="d-flex gap-1 justify-content-center flex-nowrap">
                        <!-- Details Button -->
                        <button 
                          class="btn btn-sm btn-outline-info fw-bold"
                          title="সম্পূর্ণ বিবরণ দেখুন"
                          @click="openDetailsModal(r)"
                        >
                          <i class="ti ti-eye me-1"></i> বিবরণ
                        </button>

                        <!-- Restore / Re-Book Button -->
                        <button 
                          class="btn btn-sm btn-success fw-bold"
                          title="পুনরায় সিট বরাদ্দ ও বুকিং কনফার্ম করুন"
                          @click="openRebookModal(r)"
                        >
                          <i class="ti ti-refresh me-1"></i> Re-Book
                        </button>

                        <!-- Force Delete Button -->
                        <button 
                          class="btn btn-sm btn-outline-danger fw-bold"
                          title="স্থায়ীভাবে মুছে ফেলুন (Permanently Delete)"
                          @click="openPermanentDeleteModal(r)"
                        >
                          <i class="ti ti-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>

                <tbody v-else>
                  <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                      <div v-if="loading">
                        <div class="spinner-border spinner-border-sm text-danger me-2" role="status"></div>
                        <span>Loading deleted bookings...</span>
                      </div>
                      <div v-else class="py-4">
                        <i class="ti ti-trash-off fs-1 text-muted"></i>
                        <h6 class="mt-2 text-muted fw-semibold">কোনো মুছে ফেলা বুকিং রেকর্ড পাওয়া যায়নি</h6>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div v-if="history.length && pagination.last_page > 1" class="pagination-footer border-top bg-light-50">
              <div class="pagination-info text-muted">
                Showing entries {{ (pagination.current_page - 1) * pagination.per_page + 1 }} to 
                {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }} of {{ pagination.total }}
              </div>
              <div class="pagination-actions">
                <button
                  class="btn btn-outline-secondary btn-sm"
                  :disabled="pagination.current_page <= 1"
                  @click="goToPage(pagination.current_page - 1)"
                >
                  Previous
                </button>
                <button
                  v-for="page in pagination.last_page"
                  :key="page"
                  class="btn btn-sm mx-1"
                  :class="page === pagination.current_page ? 'btn-danger text-white fw-bold' : 'btn-outline-secondary'"
                  @click="goToPage(page)"
                >
                  {{ page }}
                </button>
                <button
                  class="btn btn-outline-secondary btn-sm"
                  :disabled="pagination.current_page >= pagination.last_page"
                  @click="goToPage(pagination.current_page + 1)"
                >
                  Next
                </button>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>

    <!-- 1. Details Modal -->
    <div class="modal fade" id="deletedBookingDetailsModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" v-if="selectedBooking">
          <div class="modal-header bg-light-danger py-3">
            <h5 class="modal-title fw-bold text-danger">
              <i class="ti ti-user me-2"></i> {{ selectedBooking.full_name }} - বুকিং বিস্তারিত
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <div class="row g-3">
              <div class="col-md-6">
                <div class="p-3 bg-light rounded border">
                  <h6 class="fw-bold text-dark border-bottom pb-2 mb-2">ব্যক্তিগত তথ্য</h6>
                  <p class="mb-1"><strong>নাম:</strong> {{ selectedBooking.full_name }}</p>
                  <p class="mb-1"><strong>মোবাইল:</strong> {{ selectedBooking.phone }}</p>
                  <p class="mb-1"><strong>ইমেইল:</strong> {{ selectedBooking.email || '-' }}</p>
                  <p class="mb-1"><strong>রক্তের গ্রুপ:</strong> {{ selectedBooking.blood_group || '-' }}</p>
                  <p class="mb-1"><strong>ইউজার টাইপ:</strong> <span class="badge bg-primary text-uppercase">{{ selectedBooking.user_type || 'student' }}</span></p>
                  <p class="mb-1"><strong>শিক্ষা প্রতিষ্ঠান/কর্মস্থল:</strong> {{ selectedBooking.institution_name || selectedBooking.workplace_name || '-' }}</p>
                  <p class="mb-1"><strong>জাতীয় পরিচয়পত্র (NID):</strong> {{ selectedBooking.nid || '-' }}</p>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 bg-light rounded border">
                  <h6 class="fw-bold text-dark border-bottom pb-2 mb-2">অভিভাবক ও ঠিকানা</h6>
                  <p class="mb-1"><strong>পিতার নাম:</strong> {{ selectedBooking.father_name || '-' }}</p>
                  <p class="mb-1"><strong>পিতার ফোন:</strong> {{ selectedBooking.father_phone || '-' }}</p>
                  <p class="mb-1"><strong>মাতার নাম:</strong> {{ selectedBooking.mother_name || '-' }}</p>
                  <p class="mb-1"><strong>মাতার ফোন:</strong> {{ selectedBooking.mother_phone || '-' }}</p>
                  <p class="mb-1"><strong>বিভাগ / জেলা / থানা:</strong> {{ selectedBooking.division_name || '-' }} / {{ selectedBooking.district_name || '-' }} / {{ selectedBooking.thana_name || '-' }}</p>
                  <p class="mb-1"><strong>গ্রাম/ঠিকানা:</strong> {{ selectedBooking.address || '-' }}</p>
                </div>
              </div>
              <div class="col-12">
                <div class="p-3 bg-light rounded border">
                  <h6 class="fw-bold text-dark border-bottom pb-2 mb-2">পূর্বের রুম ও ফি এর তথ্য</h6>
                  <div class="row">
                    <div class="col-sm-6">
                      <p class="mb-1"><strong>ফ্লোর:</strong> {{ selectedBooking.floornumber || '-' }}</p>
                      <p class="mb-1"><strong>রুম ও সিট:</strong> {{ formatBookingRoomSeat(selectedBooking) }}</p>
                    </div>
                    <div class="col-sm-6">
                      <p class="mb-1"><strong>মাসিক ভাড়া:</strong> ৳ {{ Number(selectedBooking.monthly_amount || 0).toLocaleString() }}</p>
                      <p class="mb-1"><strong>এডভান্স জমা:</strong> ৳ {{ Number(getAdvanceDepositAmount(selectedBooking)).toLocaleString() }}</p>
                      <p class="mb-1"><strong>ডেভেলপমেন্ট ফি:</strong> ৳ {{ Number(selectedBooking.development_fee || 0).toLocaleString() }}</p>
                      <p class="mb-1 fw-bold text-success border-top pt-1 mt-1"><strong>মোট জমা (এডভান্স + ডেভেলপমেন্ট):</strong> ৳ {{ (Number(getAdvanceDepositAmount(selectedBooking)) + Number(selectedBooking.development_fee || 0)).toLocaleString() }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer bg-light py-2">
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">বন্ধ করুন</button>
            <button type="button" class="btn btn-success btn-sm fw-bold" @click="proceedToRebook(selectedBooking)">
              <i class="ti ti-refresh me-1"></i> এই গেস্টকে Re-Book করুন
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- 2. Re-Book / Restore Modal -->
    <div class="modal fade" id="rebookModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" v-if="rebookTarget">
          <div class="modal-header bg-success text-white py-3">
            <h5 class="modal-title fw-bold text-white">
              <i class="ti ti-user-check me-2"></i> Re-Book & Restore Guest
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <div class="alert alert-info py-2 px-3 mb-3 small">
              <strong>শিক্ষার্থী:</strong> {{ rebookTarget.full_name }} ({{ rebookTarget.phone }})
            </div>

            <!-- Option to select new seat -->
            <div class="mb-3">
              <label class="form-label fw-bold">ফ্লোর নির্বাচন করুন <span class="text-danger">*</span></label>
              <select class="form-select" v-model="selectedFloorId" @change="onFloorChange">
                <option value="">-- ফ্লোর বাছাই করুন --</option>
                <option v-for="floor in availableFloors" :key="floor.id" :value="floor.id">
                  {{ floor.name }}
                </option>
              </select>
            </div>

            <div class="mb-3" v-if="selectedFloorId">
              <label class="form-label fw-bold">রুম নির্বাচন করুন <span class="text-danger">*</span></label>
              <select class="form-select" v-model="selectedRoomId" @change="onRoomChange">
                <option value="">-- রুম বাছাই করুন --</option>
                <option v-for="room in availableRooms" :key="room.id" :value="room.id">
                  রুম নং: {{ formatRoomNo(room.room_no) }} (ফাঁকা সিট: {{ room.available_seats.length }})
                </option>
              </select>
            </div>

            <div class="mb-3" v-if="selectedRoomId">
              <label class="form-label fw-bold">সিট নির্বাচন করুন <span class="text-danger">*</span></label>
              <select class="form-select" v-model="selectedSeatId" @change="onSeatChange">
                <option value="">-- সিট বাছাই করুন --</option>
                <option v-for="seat in availableSeats" :key="seat.id" :value="seat.id">
                  সিট নং: {{ formatSeatName(seat, selectedRoomObj) }} - ভাড়া: ৳ {{ Number(seat.price || 0).toLocaleString() }} (এডভান্স: ৳ {{ Number(seat.advance_price || 0).toLocaleString() }})
                </option>
              </select>
            </div>

            <div v-if="selectedSeatObj" class="p-3 bg-light rounded border border-2 border-success mb-3 shadow-sm">
              <div class="d-flex justify-content-between mb-2 small">
                <span class="text-muted fw-semibold">মাসিক সিট ভাড়া:</span>
                <strong class="text-dark">৳ {{ Number(selectedSeatObj.price || 0).toLocaleString() }}</strong>
              </div>
              <div class="d-flex justify-content-between mb-2">
                <span class="text-dark fw-semibold">এডভান্স জমা:</span>
                <strong class="text-primary fs-6">৳ {{ Number(selectedSeatObj.advance_price || 0).toLocaleString() }}</strong>
              </div>
              <div class="d-flex justify-content-between mb-2">
                <span class="text-dark fw-semibold">ডেভেলপমেন্ট ফি:</span>
                <strong class="text-warning fs-6">৳ {{ Number(rebookDevFee || 0).toLocaleString() }}</strong>
              </div>
              <hr class="my-2 border-dark-subtle">
              <div class="d-flex justify-content-between align-items-center pt-1">
                <span class="fw-bold text-success fs-6">
                  <i class="ti ti-calculator me-1"></i> সর্বমোট প্রদেয় টাকা (Total):
                </span>
                <strong class="text-success fs-5">
                  ৳ {{ Number(Number(selectedSeatObj.advance_price || 0) + Number(rebookDevFee || 0)).toLocaleString() }}
                </strong>
              </div>
            </div>

            <div v-else-if="!loadingSeats && availableFloors.length === 0" class="alert alert-warning small py-2">
              বর্তমানে কোনো খালি সিট নেই।
            </div>

          </div>
          <div class="modal-footer bg-light py-2 justify-content-between">
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">✕ বাতিল</button>
            <button 
              type="button" 
              class="btn btn-success btn-sm px-4 fw-bold" 
              :disabled="submitting || !selectedSeatId"
              @click="confirmRebook"
            >
              <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
              <i v-else class="ti ti-check me-1"></i> নিশ্চিত ও বুকিং একটিভ করুন
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- 3. Permanent Delete Modal -->
    <div class="modal fade" id="permanentDeleteModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" v-if="deleteTarget">
          <div class="modal-header bg-danger text-white py-3">
            <h5 class="modal-title fw-bold text-white">
              <i class="ti ti-alert-triangle me-2"></i> স্থায়ীভাবে মুছে ফেলার সতর্কতা
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4 text-center">
            <i class="ti ti-trash-x text-danger fs-1 mb-2 d-block"></i>
            <h6 class="fw-bold text-dark mb-2">আপনি কি নিশ্চিত যে এই রেকর্ডটি সম্পূর্ণ মুছে ফেলবেন?</h6>
            <p class="text-muted small mb-0">
              গেস্ট: <strong>{{ deleteTarget.full_name }}</strong> ({{ deleteTarget.phone }})<br>
              <span class="text-danger fw-bold">সতর্কতা: একবার স্থায়ীভাবে মুছে ফেললে এই ডাটা আর কখনো পুনরুদ্ধার করা সম্ভব হবে না!</span>
            </p>
          </div>
          <div class="modal-footer bg-light py-2 justify-content-between">
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">বাতিল</button>
            <button 
              type="button" 
              class="btn btn-danger btn-sm px-4 fw-bold" 
              :disabled="submitting"
              @click="confirmPermanentDelete"
            >
              <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
              <i v-else class="ti ti-trash me-1"></i> হ্যাঁ, স্থায়ীভাবে ডিলিট করুন
            </button>
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
  name: "DeletedBookingHistory",
  computed: {
    url() {
      return this.$store.state.url || "/";
    },
    availableRooms() {
      if (!this.selectedFloorId) return [];
      const floor = this.availableFloors.find(f => f.id === this.selectedFloorId);
      return floor ? floor.rooms_list || [] : [];
    },
    availableSeats() {
      if (!this.selectedRoomId) return [];
      const room = this.availableRooms.find(r => r.id === this.selectedRoomId);
      return room ? room.available_seats || [] : [];
    },
    selectedRoomObj() {
      if (!this.selectedRoomId) return null;
      return this.availableRooms.find(r => r.id === this.selectedRoomId) || null;
    },
  },
  data() {
    return {
      history: [],
      loading: false,
      search: "",
      searchTimeout: null,
      pagination: {
        total: 0,
        current_page: 1,
        last_page: 1,
        per_page: 10,
      },
      selectedBooking: null,
      rebookTarget: null,
      deleteTarget: null,
      availableFloors: [],
      selectedFloorId: "",
      selectedRoomId: "",
      selectedSeatId: "",
      selectedSeatObj: null,
      rebookDevFee: 3000,
      loadingSeats: false,
      submitting: false,
    };
  },
  mounted() {
    this.fetchData();
  },
  methods: {
    endpoint(path) {
      const base = (this.url || window.location.origin || "").endsWith("/")
        ? (this.url || window.location.origin).slice(0, -1)
        : (this.url || window.location.origin || "");
      const cleanPath = path.startsWith("/") ? path : `/${path}`;
      return `${base}${cleanPath}`;
    },

    async fetchData(page = 1) {
      this.loading = true;
      try {
        const params = {
          page: page,
          per_page: this.pagination.per_page,
          search: this.search,
        };

        const res = await axios.get(this.endpoint("deleted-bookings"), { params });
        this.history = res.data.data || [];
        this.pagination = {
          total: res.data.total || 0,
          current_page: res.data.current_page || 1,
          last_page: res.data.last_page || 1,
          per_page: res.data.per_page || 10,
        };
      } catch (e) {
        this.toast("Failed to load deleted bookings", "error");
      } finally {
        this.loading = false;
      }
    },

    onSearchInput() {
      if (this.searchTimeout) clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        this.fetchData(1);
      }, 300);
    },

    goToPage(page) {
      if (page < 1 || page > this.pagination.last_page) return;
      this.fetchData(page);
    },

    formatDateTime(dt) {
      if (!dt) return "-";
      const [date, time] = String(dt).split("T");
      const d = date || dt.split(" ")[0];
      const t = time ? time.substring(0, 5) : (dt.split(" ")[1] || "");
      const parts = d.split("-");
      const formatted = parts.length === 3 ? `${parts[2]}-${parts[1]}-${parts[0]}` : d;
      return `${formatted} ${t}`;
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

    openDetailsModal(r) {
      this.selectedBooking = r;
      const modalEl = document.getElementById("deletedBookingDetailsModal");
      if (modalEl) {
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
      }
    },

    proceedToRebook(r) {
      const detailsModalEl = document.getElementById("deletedBookingDetailsModal");
      if (detailsModalEl) {
        const modal = bootstrap.Modal.getInstance(detailsModalEl);
        if (modal) modal.hide();
      }
      this.openRebookModal(r);
    },

    async openRebookModal(r) {
      this.rebookTarget = r;
      this.selectedFloorId = "";
      this.selectedRoomId = "";
      this.selectedSeatId = "";
      this.selectedSeatObj = null;
      this.rebookDevFee = (r.development_fee !== null && r.development_fee !== undefined && Number(r.development_fee) > 0)
        ? Number(r.development_fee)
        : 3000;

      // Fetch available seats tree
      this.loadingSeats = true;
      try {
        const res = await axios.get(this.endpoint("available-seats-tree"));
        if (res.data.status === "success") {
          this.availableFloors = res.data.data || [];
        }
      } catch (e) {
        console.error("Failed to load available seats", e);
      } finally {
        this.loadingSeats = false;
      }

      const modalEl = document.getElementById("rebookModal");
      if (modalEl) {
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
      }
    },

    onFloorChange() {
      this.selectedRoomId = "";
      this.selectedSeatId = "";
      this.selectedSeatObj = null;
    },

    onRoomChange() {
      this.selectedSeatId = "";
      this.selectedSeatObj = null;
    },

    onSeatChange() {
      if (!this.selectedSeatId) {
        this.selectedSeatObj = null;
        return;
      }
      this.selectedSeatObj = this.availableSeats.find(s => s.id === this.selectedSeatId) || null;
    },

    async confirmRebook() {
      if (!this.rebookTarget || !this.selectedSeatId) {
        this.toast("অনুগ্রহ করে একটি সিট নির্বাচন করুন!", "warning");
        return;
      }

      this.submitting = true;
      try {
        const payload = {
          seat_id: this.selectedSeatId,
          development_fee: this.rebookDevFee,
        };

        const res = await axios.post(this.endpoint(`bookings/${this.rebookTarget.id}/restore`), payload);
        if (res.data.success) {
          this.toast(res.data.message || "বুকিং সফলভাবে Restore & Confirm করা হয়েছে!", "success");
          
          const modalEl = document.getElementById("rebookModal");
          if (modalEl) {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
          }

          this.fetchData(this.pagination.current_page || 1);
        } else {
          this.toast(res.data.message || "রিস্টোর করতে ব্যর্থ হয়েছে!", "error");
        }
      } catch (err) {
        console.error(err);
        const msg = (err.response && err.response.data && err.response.data.message)
          ? err.response.data.message
          : "রিস্টোর করতে সমস্যা হয়েছে!";
        this.toast(msg, "error");
      } finally {
        this.submitting = false;
      }
    },

    openPermanentDeleteModal(r) {
      this.deleteTarget = r;
      const modalEl = document.getElementById("permanentDeleteModal");
      if (modalEl) {
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
      }
    },

    async confirmPermanentDelete() {
      if (!this.deleteTarget) return;

      this.submitting = true;
      try {
        const res = await axios.delete(this.endpoint(`bookings/${this.deleteTarget.id}/force-delete`));
        if (res.data.success) {
          this.toast(res.data.message || "স্থায়ীভাবে মুছে ফেলা হয়েছে!", "success");
          
          const modalEl = document.getElementById("permanentDeleteModal");
          if (modalEl) {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
          }

          this.fetchData(this.pagination.current_page || 1);
        } else {
          this.toast(res.data.message || "মুছে ফেলতে ব্যর্থ হয়েছে!", "error");
        }
      } catch (err) {
        console.error(err);
        const msg = (err.response && err.response.data && err.response.data.message)
          ? err.response.data.message
          : "স্থায়ীভাবে মুছতে ব্যর্থ হয়েছে!";
        this.toast(msg, "error");
      } finally {
        this.submitting = false;
      }
    },

    formatSeatBadge(item) {
      if (!item || !item.roomnumber) return '-';
      const rType = String(item.room_type || '').toLowerCase();
      const isDouble = rType.includes('double') || rType.includes('doubel');

      const raw = String(item.roomnumber).trim();
      const parts = raw.split('-');
      
      if (parts.length > 1) {
        const restParts = parts.slice(1);
        if (!isDouble) {
          if (restParts.length > 1 && /^\d+$/.test(restParts[restParts.length - 1])) {
            return restParts.slice(0, -1).join('-');
          }
        }
        return restParts.join('-');
      }

      if (!isDouble && /[\-_ ]\d+$/.test(raw)) {
        return raw.replace(/[\-_ ]\d+$/, '');
      }

      return raw;
    },

    formatBookingRoomSeat(r) {
      if (!r) return '-';
      if (r.room_items && r.room_items.length) {
        return r.room_items.map(i => {
          const roomDigit = (i.roomnumber || '').split('-')[0];
          const badgePart = this.formatSeatBadge(i);
          return badgePart && badgePart !== '-' ? `${roomDigit}-${badgePart}` : roomDigit;
        }).filter(Boolean).join(', ');
      }

      const raw = String(r.roomnumber || r.room_number || '').trim();
      if (!raw) return '-';

      const rType = String(r.room_type || r.type || '').toLowerCase();
      const isDouble = rType.includes('double') || rType.includes('doubel');

      const m1 = raw.match(/^([A-Za-z]+)[\-_](\d+)[\-_]Block(?:[\-_](\d+))?/i);
      if (m1) {
        const blockLetter = m1[1].toUpperCase();
        const roomNum = m1[2];
        const seatSub = m1[3] || '';
        if (isDouble && seatSub) {
          return `${roomNum}-Block ${blockLetter}-${seatSub}`;
        }
        return `${roomNum}-Block ${blockLetter}`;
      }

      const m2 = raw.match(/^(\d+)[\-_]Block(?:[\-_]([A-Za-z]+))?(?:[\-_](\d+))?/i);
      if (m2) {
        const roomNum = m2[1];
        const blockLetter = m2[2] ? m2[2].toUpperCase() : '';
        const seatSub = m2[3] || '';
        const blockLabel = blockLetter ? `Block ${blockLetter}` : 'Block';
        if (isDouble && seatSub) {
          return `${roomNum}-${blockLabel}-${seatSub}`;
        }
        return `${roomNum}-${blockLabel}`;
      }

      if (!isDouble && /[\-_][12]$/.test(raw)) {
        return raw.replace(/[\-_][12]$/, '');
      }

      return raw;
    },

    formatRoomNo(roomNo) {
      if (!roomNo) return '-';
      const rNo = String(roomNo).trim();
      const m1 = rNo.match(/^([A-Za-z]+)[\-_](\d+)$/);
      if (m1) {
        return `${parseInt(m1[2], 10)}-Block ${m1[1].toUpperCase()}`;
      }
      const m2 = rNo.match(/^(\d+)[\-_ ]*Block[\-_ ]*([A-Za-z]+)/i);
      if (m2) {
        return `${parseInt(m2[1], 10)}-Block ${m2[2].toUpperCase()}`;
      }
      return rNo;
    },

    formatSeatName(seat, room) {
      if (!seat || !seat.seat_no) return '-';
      const sNo = String(seat.seat_no).trim();

      const rType = String(room?.room_type || room?.type || room?.room_name || '').toLowerCase();
      const isSingleType = rType.includes('single') || rType.includes('singel');
      const totalSeatsCount = room?.seats ? room.seats.length : (room?.available_seats ? room.available_seats.length : 0);
      const isSingle = isSingleType || totalSeatsCount === 1;

      if (isSingle) {
        if (/[\-_][12]$/.test(sNo)) {
          return sNo.replace(/[\-_][12]$/, '');
        }
      }

      return sNo;
    },

    toast(text, type = "success") {
      Toastify({
        text,
        duration: 3500,
        gravity: "top",
        position: "right",
        style: {
          background: type === "success"
            ? "linear-gradient(to right, #198754, #198754)"
            : (type === "warning" ? "linear-gradient(to right, #f59e0b, #f59e0b)" : "linear-gradient(to right, #dc3545, #dc3545)"),
        },
      }).showToast();
    },
  },
};
</script>

<style scoped>
.bg-light-danger-gradient {
  background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
  border-bottom: 2px solid #feb2b2;
}
.bg-danger-header {
  background: #e53e3e;
}
.icon-avatar {
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
}
.bg-danger-light {
  background-color: rgba(220, 53, 69, 0.15);
}
.bg-light-danger {
  background-color: #fff5f5;
}
.bg-light-success {
  background-color: #f0fff4;
}
.bg-secondary-light {
  background-color: #f1f3f5;
}
.bg-light-gray-50 {
  background-color: #fafbfc;
}
.avatar-fallback {
  background-color: #e53e3e;
  display: flex;
  align-items: center;
  justify-content: center;
}
.hover-grow {
  transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}
.hover-grow:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(0,0,0,0.08) !important;
}
.transition-all {
  transition: all 0.25s ease-in-out;
}
.uppercase-badge {
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.hover-row:hover {
  background-color: #fffaf0 !important;
}
.pagination-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px 20px;
}
.pagination-actions {
  display: flex;
  align-items: center;
}
</style>
