<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-12">
        <div class="card mb-4 shadow-sm">
          <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3 bg-light">
            <h5 class="card-title mb-0 text-primary fw-bold">
              <i class="ti ti-arrows-exchange me-2"></i>Change Room / Seat Manager (রুম ও সিট পরিবর্তন)
            </h5>

            <!-- NAV TABS -->
            <ul class="nav nav-pills card-header-pills">
              <li class="nav-item">
                <button
                  class="nav-link fw-bold"
                  :class="{ active: activeTab === 'residents' }"
                  @click="switchTab('residents')"
                >
                  <i class="ti ti-users me-1"></i> Active Residents
                </button>
              </li>
              <li class="nav-item">
                <button
                  class="nav-link fw-bold"
                  :class="{ active: activeTab === 'history' }"
                  @click="switchTab('history')"
                >
                  <i class="ti ti-history me-1"></i> Change History & Fees (৳ 500)
                </button>
              </li>
            </ul>
          </div>

          <!-- TAB 1: ACTIVE RESIDENTS -->
          <div class="card-body mt-3" v-if="activeTab === 'residents'">
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
              <div class="d-flex align-items-center gap-2">
                <label class="small text-muted mb-0">Rows:</label>
                <select class="form-select form-select-sm" style="width: 90px" v-model.number="perPage">
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
                  style="width: 280px"
                  placeholder="Search guest name / phone / room..."
                  v-model="search"
                />
              </div>
            </div>

            <div class="table-responsive">
              <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th style="width: 60px">Sl</th>
                    <th style="width: 200px">Resident Name</th>
                    <th style="width: 140px">Phone</th>
                    <th style="width: 140px">Current Floor</th>
                    <th style="width: 140px">Current Room</th>
                    <th style="width: 140px">Current Seat</th>
                    <th style="width: 140px">Monthly Amount</th>
                    <th style="width: 160px" class="text-center">Action</th>
                  </tr>
                </thead>

                <tbody v-if="residents.length">
                  <tr v-for="(r, idx) in residents" :key="r.id">
                    <td>{{ from + idx + 1 }}</td>
                    <td>
                      <div class="fw-bold text-dark">{{ r.full_name || "-" }}</div>
                      <small class="text-muted">{{ r.user_type || 'Student' }}</small>
                    </td>
                    <td>
                      <span class="fw-semibold text-secondary">{{ r.phone || "-" }}</span>
                    </td>
                    <td>
                      <span class="fw-semibold text-dark">{{ r.floornumber || "-" }}</span>
                    </td>
                    <td>
                      <span class="badge bg-primary px-2 py-1 font-monospace fs-6">{{ getRoomNo(r.roomnumber) }}</span>
                    </td>
                    <td>
                      <span class="badge bg-danger px-2 py-1 font-monospace fs-6">{{ getSeatNo(r.roomnumber) }}</span>
                    </td>
                    <td>
                      <span class="fw-bold text-success">৳ {{ formatCurrency(r.monthly_amount) }}</span>
                    </td>
                    <td class="text-center">
                      <button
                        class="btn btn-sm btn-outline-primary fw-bold"
                        @click="openChangeModal(r)"
                      >
                        <i class="ti ti-arrows-exchange me-1"></i> Change Room/Seat
                      </button>
                    </td>
                  </tr>
                </tbody>

                <tbody v-else>
                  <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                      <span v-if="loading">
                        <i class="fa fa-spinner fa-spin me-2"></i> Loading active residents...
                      </span>
                      <span v-else>No active residents found</span>
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
                  @click="fetchResidents(currentPage - 1)"
                >
                  Previous
                </button>

                <button
                  class="btn btn-sm btn-secondary"
                  :disabled="currentPage >= totalPages || loading"
                  @click="fetchResidents(currentPage + 1)"
                >
                  Next
                </button>
              </div>
            </div>
          </div>

          <!-- TAB 2: CHANGE HISTORY & FEES LOG -->
          <div class="card-body mt-3" v-if="activeTab === 'history'">
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
              <div class="fw-bold text-dark fs-6">
                <i class="ti ti-file-analytics me-1 text-primary"></i> Room Change & Fee Records
              </div>

              <div class="d-flex gap-2 align-items-center">
                <input
                  type="text"
                  class="form-control form-control-sm"
                  style="width: 280px"
                  placeholder="Search history by name / phone / seat..."
                  v-model="historiesSearch"
                />
              </div>
            </div>

            <div class="table-responsive">
              <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th style="width: 50px">Sl</th>
                    <th style="width: 170px">Resident</th>
                    <th style="width: 160px">From (Old)</th>
                    <th style="width: 160px">To (New)</th>
                    <th style="width: 120px">Change Fee</th>
                    <th style="width: 130px">Payment Method</th>
                    <th style="width: 110px">Status</th>
                    <th style="width: 140px">Date & Time</th>
                  </tr>
                </thead>

                <tbody v-if="histories.length">
                  <tr v-for="(h, idx) in histories" :key="h.id">
                    <td>{{ (historiesPage - 1) * 10 + idx + 1 }}</td>
                    <td>
                      <div class="fw-bold text-dark">{{ h.resident_name }}</div>
                      <small class="text-muted">{{ h.phone }}</small>
                    </td>
                    <td>
                      <small class="text-secondary d-block">{{ h.old_floor || '-' }}</small>
                      <span class="badge bg-secondary px-2 py-1">{{ h.old_room_seat || '-' }}</span>
                      <small class="d-block text-muted">৳ {{ formatCurrency(h.old_monthly_amount) }}</small>
                    </td>
                    <td>
                      <small class="text-primary d-block font-weight-bold">{{ h.new_floor || '-' }}</small>
                      <span class="badge bg-success px-2 py-1">{{ h.new_room_seat || '-' }}</span>
                      <small class="d-block text-success fw-bold">৳ {{ formatCurrency(h.new_monthly_amount) }}</small>
                    </td>
                    <td>
                      <span class="fw-bold text-primary fs-6">৳ {{ formatCurrency(h.fee_amount) }}</span>
                    </td>
                    <td>
                      <span class="badge bg-label-info text-capitalize">{{ h.payment_method || 'Cash' }}</span>
                    </td>
                    <td>
                      <span
                        class="badge"
                        :class="h.payment_status === 'paid' ? 'bg-success' : 'bg-warning text-dark'"
                      >
                        {{ h.payment_status === 'paid' ? 'Paid' : 'Unpaid' }}
                      </span>
                    </td>
                    <td>
                      <small class="text-dark fw-semibold">{{ formatDate(h.created_at) }}</small>
                    </td>
                  </tr>
                </tbody>

                <tbody v-else>
                  <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                      <span v-if="historiesLoading">
                        <i class="fa fa-spinner fa-spin me-2"></i> Loading history records...
                      </span>
                      <span v-else>No room change records found</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
              <div class="small text-muted">
                Page: {{ historiesPage }} / {{ historiesTotalPages }}
              </div>

              <div class="d-flex align-items-center gap-2">
                <button
                  class="btn btn-sm btn-secondary"
                  :disabled="historiesPage <= 1 || historiesLoading"
                  @click="fetchHistories(historiesPage - 1)"
                >
                  Previous
                </button>

                <button
                  class="btn btn-sm btn-secondary"
                  :disabled="historiesPage >= historiesTotalPages || historiesLoading"
                  @click="fetchHistories(historiesPage + 1)"
                >
                  Next
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal for Changing Room/Seat -->
    <div
      class="modal fade"
      id="changeRoomModal"
      tabindex="-1"
      aria-hidden="true"
      ref="changeModalRef"
    >
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0">
          <div class="modal-header bg-primary text-white py-3">
            <h5 class="modal-title text-white fw-bold">
              <i class="ti ti-arrows-exchange me-2"></i>Change Room & Seat
            </h5>
            <button
              type="button"
              class="btn-close btn-close-white"
              data-bs-dismiss="modal"
              aria-label="Close"
            ></button>
          </div>

          <div class="modal-body p-4" v-if="selectedResident">
            <!-- Current Allocation Alert -->
            <div class="alert alert-info border-info d-flex align-items-center gap-3 mb-4">
              <div class="fs-2 text-info"><i class="ti ti-user-check"></i></div>
              <div>
                <h6 class="mb-1 fw-bold text-dark">{{ selectedResident.full_name }} ({{ selectedResident.phone }})</h6>
                <div class="small text-secondary">
                  <strong>Current:</strong> {{ selectedResident.floornumber || 'Floor' }} | Room: <strong>{{ getRoomNo(selectedResident.roomnumber) }}</strong> | Seat: <strong>{{ getSeatNo(selectedResident.roomnumber) }}</strong> (৳ {{ formatCurrency(selectedResident.monthly_amount) }})
                </div>
              </div>
            </div>

            <!-- Target Selection Form -->
            <div class="row g-3">
              <!-- Select New Floor -->
              <div class="col-md-4">
                <label class="form-label fw-bold">1. Select New Floor <span class="text-danger">*</span></label>
                <select class="form-select" v-model="selectedFloorId" @change="onFloorChange">
                  <option value="">-- Choose Floor --</option>
                  <option v-for="fl in availableTree" :key="fl.id" :value="fl.id">
                    {{ fl.name }}
                  </option>
                </select>
              </div>

              <!-- Select New Room -->
              <div class="col-md-4">
                <label class="form-label fw-bold">2. Select New Room <span class="text-danger">*</span></label>
                <select
                  class="form-select"
                  v-model="selectedRoomId"
                  :disabled="!filteredRooms.length"
                  @change="onRoomChange"
                >
                  <option value="">-- Choose Room --</option>
                  <option v-for="rm in filteredRooms" :key="rm.id" :value="rm.id">
                    Room {{ rm.room_no }}
                  </option>
                </select>
              </div>

              <!-- Select New Seat -->
              <div class="col-md-4">
                <label class="form-label fw-bold">3. Select Vacant Seat <span class="text-danger">*</span></label>
                <select
                  class="form-select"
                  v-model="selectedSeatId"
                  :disabled="!filteredSeats.length"
                >
                  <option value="">-- Choose Seat --</option>
                  <option v-for="st in filteredSeats" :key="st.id" :value="st.id">
                    Seat {{ st.seat_no }} (৳ {{ formatCurrency(st.price) }})
                  </option>
                </select>
              </div>
            </div>

            <!-- ROOM CHANGE FEE FIELD -->
            <div class="row g-3 mt-1">
              <div class="col-md-12">
                <label class="form-label fw-bold">Change Fee (৳) <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text fw-bold bg-white text-primary">৳</span>
                  <input
                    type="number"
                    class="form-control fw-bold fs-5 text-primary"
                    v-model.number="feeAmount"
                    min="0"
                    placeholder="500"
                  />
                </div>
              </div>
            </div>

            <!-- Preview Transfer Details -->
            <div v-if="selectedSeatObj" class="alert alert-success border-success mt-4 mb-0">
              <div class="fw-bold text-success mb-1">
                <i class="ti ti-check me-1"></i> New Allocation Summary:
              </div>
              <div class="fs-6 text-dark">
                <strong>New Location:</strong> {{ selectedFloorObj?.name }} &rarr; Room <strong>{{ selectedRoomObj?.room_no }}</strong> &rarr; Seat <strong>{{ selectedSeatObj?.seat_no }}</strong><br>
                <strong>New Monthly Rate:</strong> ৳ {{ formatCurrency(selectedSeatObj?.price) }} | <strong>Room Change Fee:</strong> ৳ {{ formatCurrency(feeAmount) }}
              </div>
            </div>
          </div>

          <div class="modal-footer bg-light py-3">
            <button type="button" class="btn btn-secondary fw-semibold" data-bs-dismiss="modal">
              Cancel
            </button>
            <button
              type="button"
              class="btn btn-primary fw-bold"
              :disabled="!selectedSeatId || submitting"
              @click="submitRoomChange"
            >
              <span v-if="submitting"><i class="fa fa-spinner fa-spin me-1"></i> Transferring...</span>
              <span v-else><i class="ti ti-check me-1"></i> Confirm Room Change (৳ {{ feeAmount }})</span>
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
  name: "RoomChangeManager",

  data() {
    return {
      activeTab: "residents", // 'residents' or 'history'
      residents: [],
      loading: false,
      search: "",
      perPage: 10,
      currentPage: 1,
      totalPages: 1,
      total: 0,
      from: 0,
      _t: null,
      _ht: null,

      availableTree: [],
      selectedResident: null,
      selectedFloorId: "",
      selectedRoomId: "",
      selectedSeatId: "",

      // Room Change Fee & Payment Fields
      feeAmount: 500,
      paymentMethod: "Cash",
      paymentStatus: "paid",
      remarks: "",

      submitting: false,
      modalInstance: null,

      // History Tab Data
      histories: [],
      historiesLoading: false,
      historiesPage: 1,
      historiesTotalPages: 1,
      historiesSearch: "",
    };
  },

  computed: {
    url() {
      return this.$store.state.url;
    },

    selectedFloorObj() {
      if (!this.selectedFloorId) return null;
      return this.availableTree.find(f => String(f.id) === String(this.selectedFloorId)) || null;
    },

    filteredRooms() {
      if (!this.selectedFloorObj) return [];
      return this.selectedFloorObj.rooms_list || [];
    },

    selectedRoomObj() {
      if (!this.selectedRoomId) return null;
      return this.filteredRooms.find(r => String(r.id) === String(this.selectedRoomId)) || null;
    },

    filteredSeats() {
      if (!this.selectedRoomObj) return [];
      return this.selectedRoomObj.available_seats || [];
    },

    selectedSeatObj() {
      if (!this.selectedSeatId) return null;
      return this.filteredSeats.find(s => String(s.id) === String(this.selectedSeatId)) || null;
    },
  },

  mounted() {
    this.fetchResidents(1);
    this.fetchAvailableTree();
  },

  watch: {
    search() {
      clearTimeout(this._t);
      this._t = setTimeout(() => this.fetchResidents(1), 300);
    },
    perPage() {
      this.fetchResidents(1);
    },
    historiesSearch() {
      clearTimeout(this._ht);
      this._ht = setTimeout(() => this.fetchHistories(1), 300);
    },
  },

  beforeUnmount() {
    clearTimeout(this._t);
    clearTimeout(this._ht);
  },

  methods: {
    switchTab(tab) {
      this.activeTab = tab;
      if (tab === "history" && !this.histories.length) {
        this.fetchHistories(1);
      }
    },

    formatCurrency(val) {
      if (val === null || val === undefined || isNaN(val)) return "0";
      return Number(val).toLocaleString("en-US");
    },

    formatDate(dtStr) {
      if (!dtStr) return "-";
      const d = new Date(dtStr);
      if (isNaN(d.getTime())) return dtStr;
      return d.toLocaleString("en-GB", {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
        hour12: true,
      });
    },

    getRoomNo(str) {
      if (!str) return "-";
      const parts = String(str).split("-");
      return parts[0] || str;
    },

    getSeatNo(str) {
      if (!str) return "-";
      const parts = String(str).split("-");
      if (parts.length > 1) {
        return parts.slice(1).join("-");
      }
      return "-";
    },

    toast(text, type = "success") {
      const bg =
        type === "success"
          ? "linear-gradient(to right, #00b09b, #96c93d)"
          : type === "warning"
          ? "linear-gradient(to right, #f59e0b, #fbbf24)"
          : "linear-gradient(to right, #ff5f6d, #ffc371)";

      Toastify({
        text: text || "Done",
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

    async fetchResidents(page = 1) {
      this.loading = true;
      try {
        const res = await axios.get(this.endpoint("active-bookings"), {
          params: {
            page,
            per_page: this.perPage,
            search: this.search,
          },
        });

        this.residents = res.data.data || [];
        this.currentPage = res.data.current_page || 1;
        this.totalPages = res.data.last_page || 1;
        this.total = res.data.total || 0;
        this.from = res.data.from ? res.data.from - 1 : 0;
      } catch (e) {
        console.error(e);
        this.toast("Failed to load active residents", "error");
      } finally {
        this.loading = false;
      }
    },

    async fetchHistories(page = 1) {
      this.historiesLoading = true;
      try {
        const res = await axios.get(this.endpoint("room-change-histories"), {
          params: {
            page,
            per_page: 10,
            search: this.historiesSearch,
          },
        });

        this.histories = res.data.data || [];
        this.historiesPage = res.data.current_page || 1;
        this.historiesTotalPages = res.data.last_page || 1;
      } catch (e) {
        console.error(e);
        this.toast("Failed to load room change history", "error");
      } finally {
        this.historiesLoading = false;
      }
    },

    async fetchAvailableTree() {
      try {
        const res = await axios.get(this.endpoint("available-seats-tree"));
        if (res.data.status === "success") {
          this.availableTree = res.data.data || [];
        }
      } catch (e) {
        console.error(e);
      }
    },

    openChangeModal(resident) {
      this.selectedResident = resident;
      this.selectedFloorId = "";
      this.selectedRoomId = "";
      this.selectedSeatId = "";

      // Reset Fee controls to default
      this.feeAmount = 500;
      this.paymentMethod = "Cash";
      this.paymentStatus = "paid";
      this.remarks = "";

      this.fetchAvailableTree();

      const modalEl = document.getElementById("changeRoomModal");
      if (modalEl && window.bootstrap) {
        this.modalInstance = window.bootstrap.Modal.getOrCreateInstance(modalEl);
        this.modalInstance.show();
      }
    },

    onFloorChange() {
      this.selectedRoomId = "";
      this.selectedSeatId = "";
    },

    onRoomChange() {
      this.selectedSeatId = "";
    },

    async submitRoomChange() {
      if (!this.selectedResident || !this.selectedSeatId) return;

      this.submitting = true;
      try {
        const res = await axios.post(
          this.endpoint(`bookings/${this.selectedResident.id}/change-room-seat`),
          {
            new_seat_id: this.selectedSeatId,
            fee_amount: this.feeAmount,
            payment_method: this.paymentMethod,
            payment_status: this.paymentStatus,
            remarks: this.remarks,
          }
        );

        if (res.data.success) {
          this.toast(res.data.message || "Room & Seat changed successfully!");
          if (this.modalInstance) {
            this.modalInstance.hide();
          }
          this.fetchResidents(this.currentPage);
          this.fetchAvailableTree();
          if (this.activeTab === "history") {
            this.fetchHistories(1);
          }
        } else {
          this.toast(res.data.message || "Failed to change room.", "error");
        }
      } catch (e) {
        console.error(e);
        const msg = e.response?.data?.message || "Failed to change room/seat.";
        this.toast(msg, "error");
      } finally {
        this.submitting = false;
      }
    },
  },
};
</script>

<style scoped>
.form-control,
.form-select {
  border-radius: 8px;
  padding: 0.55rem 0.75rem;
  border: 1px solid #dce0e4;
}

.table td {
  vertical-align: middle;
}

.table thead th {
  vertical-align: middle;
  text-align: center;
  font-size: 13px;
  font-weight: 700;
  color: #374151;
  background: #f3f4f6;
}
</style>
