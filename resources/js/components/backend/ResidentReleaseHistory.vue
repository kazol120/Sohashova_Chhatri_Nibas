<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
      <div class="col-12">
        <div class="card mb-4 shadow-sm border-0">
          
          <!-- Header -->
          <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3 bg-light-orange-gradient">
            <h5 class="card-title mb-0 fw-bold text-dark-orange">
              <i class="ti ti-history me-2"></i> Resident Checkout & Release History
            </h5>
          </div>

          <!-- Stats Cards -->
          <div class="card-body bg-light-gray-50 border-bottom py-3">
            <div class="row g-3">
              <div class="col-sm-6">
                <div class="d-flex align-items-center p-3 bg-white rounded shadow-sm hover-grow transition-all">
                  <div class="icon-avatar bg-info-light me-3">
                    <i class="ti ti-user-minus text-info fs-3"></i>
                  </div>
                  <div>
                    <h6 class="mb-0 text-muted fs-6">Checked-out Residents</h6>
                    <h4 class="mb-0 fw-bold text-dark mt-1">{{ pagination.total }}</h4>
                  </div>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="d-flex align-items-center p-3 bg-white rounded shadow-sm hover-grow transition-all">
                  <div class="icon-avatar bg-danger-light me-3">
                    <i class="ti ti-wallet text-danger fs-3"></i>
                  </div>
                  <div>
                    <h6 class="mb-0 text-muted fs-6">Total Outstanding Due</h6>
                    <h4 class="mb-0 fw-bold text-danger mt-1">৳ {{ totalOutstandingDue.toFixed(2) }}</h4>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Search -->
          <div class="card-header border-bottom py-3">
            <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between">
              <div>
                <h6 class="text-muted mb-0 fw-bold">All Historical Checkouts</h6>
              </div>

              <!-- Search Input -->
              <div class="search-box">
                <div class="input-group input-group-sm" style="max-width: 250px;">
                  <span class="input-group-text bg-white border-end-0">
                    <i class="ti ti-search text-muted"></i>
                  </span>
                  <input 
                    type="text" 
                    class="form-control border-start-0 ps-1 rounded-end" 
                    placeholder="Search by Name/Phone..." 
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
                <thead class="bg-orange-header text-white">
                  <tr>
                    <th class="text-center" style="width: 60px;">SL</th>
                    <th>Resident Name</th>
                    <th>Contact</th>
                    <th class="text-center">Floor / Room / Seat</th>
                    <th class="text-center">Check-In Date</th>
                    <th class="text-center">Checkout Date</th>
                    <th class="text-center">Due Status (বকেয়া)</th>
                    <th class="text-center">Action</th>
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
                            class="rounded-circle border border-2 border-warning"
                            style="width: 38px; height: 38px; object-fit: cover;"
                          />
                          <div 
                            v-else 
                            class="avatar-fallback rounded-circle bg-warning text-white fw-bold text-center border border-2 border-warning"
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
                    <td class="fw-semibold text-muted">{{ r.phone }}</td>
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
                    <td class="text-center text-muted fw-semibold">{{ formatDate(r.check_in) }}</td>
                    <td class="text-center text-muted fw-semibold">
                      {{ formatDateTime(r.today_check_out || r.check_out) }}
                    </td>
                    <td class="text-center">
                      <div v-if="r.due_amount > 0">
                        <span class="badge bg-danger text-white fw-bold px-2 py-1">
                          ৳ {{ Number(r.due_amount).toLocaleString() }}
                        </span>
                        <div class="small text-muted mt-1" style="font-size: 10px;">
                          <span v-if="r.monthly_due > 0">মাসিক: ৳ {{ Number(r.monthly_due).toLocaleString() }}</span>
                          <span v-if="r.monthly_due > 0 && r.penalty_due > 0"> | </span>
                          <span v-if="r.penalty_due > 0">জরিমানা: ৳ {{ Number(r.penalty_due).toLocaleString() }}</span>
                        </div>
                      </div>
                      <span v-else class="badge bg-success text-white fw-bold px-2 py-1">
                        <i class="ti ti-check me-1"></i> ৳ 0 (পরিশোধিত)
                      </span>
                    </td>
                    <td class="text-center">
                      <div class="d-flex gap-2 justify-content-center">
                        <button 
                          class="btn btn-sm btn-success fw-bold"
                          title="শিক্ষার্থীকে পুনরায় সিট বুকিং দিন (Re-Book)"
                          @click="openRebookModal(r)"
                        >
                          <i class="ti ti-refresh me-1"></i> Re-Book
                        </button>
                        <a 
                          v-if="r.user_id" 
                          :href="'/meal-history/resident-detail/' + r.user_id" 
                          target="_blank"
                          class="btn btn-sm btn-outline-info fw-bold"
                          title="শিক্ষার্থীর সম্পূর্ণ মিল তালিকা (Meal History) দেখুন"
                        >
                          <i class="ti ti-eye"></i>
                        </a>
                        <button 
                          class="btn btn-sm btn-outline-primary fw-bold"
                          title="Print Clearance Slip"
                          @click="printReleaseSlip(r)"
                        >
                          <i class="ti ti-printer me-1"></i> Print Slip
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>

                <tbody v-else>
                  <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                      <div v-if="loading">
                        <div class="spinner-border spinner-border-sm text-warning me-2" role="status"></div>
                        <span>Loading release history...</span>
                      </div>
                      <div v-else class="py-4">
                        <i class="ti ti-folder-off fs-1 text-muted"></i>
                        <h6 class="mt-2 text-muted fw-semibold">No release history records found</h6>
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
                  :class="page === pagination.current_page ? 'btn-warning text-white fw-bold' : 'btn-outline-secondary'"
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

    <!-- Re-Book Modal for Released Resident -->
    <div class="modal fade" id="releaseRebookModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" v-if="rebookTarget">
          <div class="modal-header bg-success text-white py-3">
            <h5 class="modal-title fw-bold text-white">
              <i class="ti ti-user-check me-2"></i> Re-Book & Restore Student
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
                  রুম নং: {{ room.room_no }} (ফাঁকা সিট: {{ room.available_seats.length }})
                </option>
              </select>
            </div>

            <div class="mb-3" v-if="selectedRoomId">
              <label class="form-label fw-bold">সিট নির্বাচন করুন <span class="text-danger">*</span></label>
              <select class="form-select" v-model="selectedSeatId" @change="onSeatChange">
                <option value="">-- সিট বাছাই করুন --</option>
                <option v-for="seat in availableSeats" :key="seat.id" :value="seat.id">
                  সিট নং: {{ seat.seat_no }} - ভাড়া: ৳ {{ Number(seat.price || 0).toLocaleString() }} (এডভান্স: ৳ {{ Number(seat.advance_price || 0).toLocaleString() }})
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
  </div>
</template>

<script>
import axios from "axios";
import Toastify from "toastify-js";
import "toastify-js/src/toastify.css";

export default {
  name: "ResidentReleaseHistory",
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
  },
  data() {
    return {
      history: [],
      loading: false,
      search: "",
      searchTimeout: null,
      totalOutstandingDue: 0,
      pagination: {
        total: 0,
        current_page: 1,
        last_page: 1,
        per_page: 10,
      },
      rebookTarget: null,
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
    async fetchData(page = 1) {
      this.loading = true;
      try {
        const params = {
          page: page,
          per_page: this.pagination.per_page,
          search: this.search,
        };

        const res = await axios.get(`${this.url}released-bookings`, { params });
        this.history = res.data.data || [];
        this.pagination = {
          total: res.data.total || 0,
          current_page: res.data.current_page || 1,
          last_page: res.data.last_page || 1,
          per_page: res.data.per_page || 10,
        };

        // Fetch overall outstanding due sum
        if (this.search === "") {
          this.calculateOutstandingDue();
        }

      } catch (e) {
        this.toast("Failed to load release history", "error");
      } finally {
        this.loading = false;
      }
    },

    async calculateOutstandingDue() {
      try {
        const res = await axios.get(`${this.url}released-bookings`, {
          params: { page: 1, per_page: 1000 }
        });
        const allData = res.data.data || [];
        this.totalOutstandingDue = allData.reduce((sum, item) => sum + Number(item.due_amount || 0), 0);
      } catch (e) {
        console.error("Error calculating dues:", e);
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

    formatDate(d) {
      if (!d) return "-";
      const datePart = d.split("T")[0];
      const parts = datePart.split("-");
      return parts.length === 3 ? `${parts[2]}-${parts[1]}-${parts[0]}` : datePart;
    },

    formatDateTime(dt) {
      if (!dt) return "-";
      const [date, time] = String(dt).split("T");
      const parts = date.split("-");
      const formatted = parts.length === 3 ? `${parts[2]}-${parts[1]}-${parts[0]}` : date;
      return `${formatted} ${time ? time.substring(0, 5) : ""}`;
    },

    toast(text, type = "success") {
      Toastify({
        text,
        duration: 3000,
        gravity: "top",
        position: "right",
        style: {
          background: type === "success"
            ? "linear-gradient(to right, #198754, #198754)"
            : "linear-gradient(to right, #dc3545, #dc3545)",
        },
      }).showToast();
    },

    endpoint(path) {
      const base = (this.url || window.location.origin || "").endsWith("/")
        ? (this.url || window.location.origin).slice(0, -1)
        : (this.url || window.location.origin || "");
      const cleanPath = path.startsWith("/") ? path : `/${path}`;
      return `${base}${cleanPath}`;
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

      const modalEl = document.getElementById("releaseRebookModal");
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
          this.toast(res.data.message || "বুকিং সফলভাবে Re-Book & Confirm করা হয়েছে!", "success");
          
          const modalEl = document.getElementById("releaseRebookModal");
          if (modalEl) {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
          }

          this.fetchData(this.pagination.current_page || 1);
        } else {
          this.toast(res.data.message || "রি-বুক করতে ব্যর্থ হয়েছে!", "error");
        }
      } catch (err) {
        console.error(err);
        const msg = (err.response && err.response.data && err.response.data.message)
          ? err.response.data.message
          : "রি-বুক করতে সমস্যা হয়েছে!";
        this.toast(msg, "error");
      } finally {
        this.submitting = false;
      }
    },

    printReleaseSlip(r) {
      const guestName = r.full_name || 'Guest';
      const isNoticeFulfilled = r.will_leave === 1 && r.is_notice_fulfilled;
      const totalAdv = Number(r.total_advance_deposit || 0);
      const fineVal = Number(r.monthly_amount || 0) * 2;

      const uType = (r.user_type || 'student').toLowerCase().trim();
      let nameLabel = 'শিক্ষার্থীর নাম:';
      if (uType === 'student') {
        nameLabel = 'শিক্ষার্থীর নাম:';
      } else if (uType === 'working professional' || uType === 'jobholder' || uType === 'job_holder') {
        nameLabel = 'চাকরিজীবীর নাম:';
      }

      let noticeText = isNoticeFulfilled
        ? `০২ মাসের অগ্রিম নোটিশ নিয়ম সফলভাবে সম্পন্ন হয়েছে (${r.notice_days_elapsed || 60} দিন)`
        : (r.will_leave === 1 ? `নোটিশ দেওয়া হয়েছে ${r.notice_days_elapsed || 0} দিন (৬০ দিন / ২ মাস পূর্ণ হয়নি)` : 'কোনো অগ্রিম নোটিশ দেওয়া হয়নি');

      let fineRowHtml = "";
      let advRowHtml = "";

      if (isNoticeFulfilled) {
        advRowHtml = `
          <tr>
            <th>এডভান্স জমা ফেরত:</th>
            <td class="text-success fw-bold">৳ ${totalAdv.toLocaleString()} (মেস ছাড়ার কারণে ফেরত দেওয়া হলো)</td>
          </tr>
        `;
      } else {
        fineRowHtml = `
          <tr>
            <th>জরুরি মেস ছাড়ার জরিমানা:</th>
            <td class="text-danger fw-bold">৳ ${fineVal.toLocaleString()} (২ মাসের ভাড়া জরিমানা)</td>
          </tr>
        `;
        if (totalAdv > 0) {
          const deductedAdv = Math.min(totalAdv, fineVal);
          advRowHtml = `
            <tr>
              <th>এডভান্স থেকে কর্তন:</th>
              <td class="text-danger fw-bold">৳ ${deductedAdv.toLocaleString()} (জরিমানা হিসেবে কেটে নেওয়া হয়েছে)</td>
            </tr>
          `;
        }
      }
      const mealDepTotal = Number(r.meal_deposit_total || 0);
      const mealCostTotal = Number(r.meal_cost || 0);
      const mealBal = Number(r.meal_deposit_balance || 0);

      let mealBalanceText = "";
      let mealBalanceClass = "text-muted";
      if (mealBal > 0) {
        mealBalanceText = `৳ ${mealBal.toLocaleString()} (মেম্বারকে ফেরত প্রদানযোগ্য)`;
        mealBalanceClass = "text-success fw-bold";
      } else if (mealBal < 0) {
        mealBalanceText = `৳ ${Math.abs(mealBal).toLocaleString()} (মেম্বারের কাছে বকেয়া)`;
        mealBalanceClass = "text-danger fw-bold";
      } else {
        mealBalanceText = `৳ 0 (কোনো বকেয়া বা ফেরত নেই)`;
      }

      const mealRowHtml = `
        <tr>
          <th>মিল ডিপোজিট জমা:</th>
          <td>৳ ${mealDepTotal.toLocaleString()}</td>
        </tr>
        <tr>
          <th>মিল বাবদ মোট খরচ:</th>
          <td>৳ ${mealCostTotal.toLocaleString()}</td>
        </tr>
        <tr>
          <th>মিলের চূড়ান্ত ব্যালেন্স হিসাব:</th>
          <td class="${mealBalanceClass}">${mealBalanceText}</td>
        </tr>
      `;

      const floornumber = (r.room_items || []).map(i => i.floornumber).filter(Boolean).join(', ') || r.floornumber || '-';
      const roomnumber = (r.room_items || []).map(i => {
        const roomDigit = (i.roomnumber || '').split('-')[0];
        const badgePart = this.formatSeatBadge(i);
        return badgePart && badgePart !== '-' ? `${roomDigit}-${badgePart}` : roomDigit;
      }).filter(Boolean).join(', ') || r.roomnumber || r.room_number || '-';
      const checkoutDateStr = r.today_check_out || r.check_out || new Date().toISOString();

      const printDate = new Date().toLocaleDateString('bn-BD', {
        year: 'numeric', month: 'long', day: 'numeric'
      });
      const htmlContent = `
        <!DOCTYPE html>
        <html>
        <head>
          <title>Seat Release & Checkout Clearance - টি এস এস ভিলা (TSS Villa)</title>
          <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
          <style>
            body { font-family: 'Segoe UI', Arial, sans-serif; padding: 25px; background: #fff; color: #222; }
            .receipt-card { max-width: 750px; margin: auto; padding: 30px; border: 2px solid #033364; border-radius: 12px; }
            .header-title { color: #033364; font-weight: 800; }
            .table-details th { font-weight: 600; color: #444; width: 40%; }
            .table-details td { font-weight: 700; color: #111; }
            .badge-notice { background-color: ${isNoticeFulfilled ? '#198754' : '#dc3545'}; color: #fff; font-size: 13px; font-weight: 700; padding: 5px 12px; border-radius: 6px; }
            @media print {
              body { padding: 0; }
              .receipt-card { border: 2px solid #000 !important; max-width: 100%; border-radius: 0; padding: 20px; }
              .no-print { display: none !important; }
            }
          </style>
        </head>
        <body>
          <div class="receipt-card">
            <div class="d-flex justify-content-between align-items-center mb-4 no-print">
              <button class="btn btn-outline-secondary btn-sm fw-bold" onclick="window.close()">✕ বন্ধ করুন</button>
              <button class="btn btn-primary btn-sm px-4 fw-bold" style="background: #033364; border-color: #033364;" onclick="window.print()"><i class="bi bi-printer"></i> রিলিজ স্লিপ প্রিন্ট করুন</button>
            </div>

            <div class="text-center border-bottom pb-3 mb-4">
              <h2 class="header-title mb-1">টি এস এস ভিলা (TSS Villa)</h2>
              <p class="text-muted mb-1 small">কলেজ রোড, নেসকো গেট সংলগ্ন, রংপুর</p>
              <span class="badge bg-primary text-white fs-6 px-4 py-2 mt-1" style="background: #033364 !important;">মেস ত্যাগ ও সিট রিলিজ ছাড়পত্র (Seat Release Clearance Slip)</span>
            </div>

            <table class="table table-bordered align-middle table-details mb-4">
              <tbody>
                <tr>
                  <th>${nameLabel}</th>
                  <td>${guestName} (${r.user_type || 'student'})</td>
                </tr>
                <tr>
                  <th>যোগাযোগ (ফোন):</th>
                  <td>${r.phone || '-'}</td>
                </tr>
                <tr>
                  <th>ফ্লোর / রুম / সিট নং:</th>
                  <td>${floornumber} / ${roomnumber}</td>
                </tr>
                <tr>
                  <th>মেসে যোগদানের তারিখ (Check-in):</th>
                  <td>${this.formatDate(r.check_in)}</td>
                </tr>
                <tr>
                  <th>মেস ত্যাগের তারিখ (Check-out):</th>
                  <td>${this.formatDate(checkoutDateStr)}</td>
                </tr>
                <tr>
                  <th>০২ মাসের নোটিশ অবস্থা:</th>
                  <td><span class="badge-notice">${noticeText}</span></td>
                </tr>
                <tr>
                  <th>মাসিক রুম ভাড়া:</th>
                  <td>৳ ${Number(r.monthly_amount || 0).toLocaleString()}</td>
                </tr>
                ${fineRowHtml}
                ${advRowHtml}
                ${mealRowHtml}
              </tbody>
            </table>

            <div class="d-flex justify-content-between align-items-end mt-5 pt-4">
              <div class="text-center" style="width: 200px;">
                <div class="border-top border-dark pt-1 fw-bold small">রেসিডেন্টের স্বাক্ষর</div>
              </div>
              <div class="text-center" style="width: 200px;">
                <div class="border-top border-dark pt-1 fw-bold small">মেস কর্তৃপক্ষের স্বাক্ষর</div>
              </div>
            </div>

            <div class="text-center text-muted small mt-4 pt-2 border-top">
              প্রিন্টের সময়: ${printDate} | টি এস এস ভিলা (TSS Villa) মেস ম্যানেজমেন্ট সিস্টেম
            </div>
          </div>
        </body>
        </html>
      `;

      const printWindow = window.open('', '_blank', 'width=900,height=800');
      if (printWindow) {
        printWindow.document.open();
        printWindow.document.write(htmlContent);
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => {
          printWindow.print();
        }, 400);
      } else {
        let iframe = document.getElementById("release-history-print-iframe");
        if (!iframe) {
          iframe = document.createElement("iframe");
          iframe.id = "release-history-print-iframe";
          iframe.style.position = "fixed";
          iframe.style.right = "0";
          iframe.style.bottom = "0";
          iframe.style.width = "0px";
          iframe.style.height = "0px";
          iframe.style.border = "none";
          document.body.appendChild(iframe);
        }
        const doc = iframe.contentWindow.document;
        doc.open();
        doc.write(htmlContent);
        doc.close();
        setTimeout(() => {
          iframe.contentWindow.focus();
          iframe.contentWindow.print();
        }, 500);
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
  },
};
</script>

<style scoped>
.bg-light-orange-gradient {
  background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
  border-bottom: 2px solid #ffb74d;
}
.text-dark-orange {
  color: #e65100;
}
.bg-orange-header {
  background: #f59e0b;
}
.icon-avatar {
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
}
.bg-info-light {
  background-color: rgba(13, 202, 240, 0.15);
}
.bg-danger-light {
  background-color: rgba(220, 53, 69, 0.15);
}
.bg-success-light {
  background-color: rgba(25, 135, 84, 0.15);
}
.bg-secondary-light {
  background-color: #f1f3f5;
}
.bg-light-gray-50 {
  background-color: #fafbfc;
}
.avatar-fallback {
  background-color: #f59e0b;
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
.hover-row {
  cursor: default;
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
