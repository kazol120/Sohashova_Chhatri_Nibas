<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-10 col-xl-12">
        <div class="card mb-4 shadow-sm">
          <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3">
            <div>
              <h5 class="card-title mb-0">Floor List </h5>
            </div>
            <button class="btn btn-primary" type="button" @click="openCreateFromComponent">
              <i class="ti ti-plus me-1"></i> Add Floor
            </button>
          </div>
          <div class="card-body">
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
              <div class="d-flex align-items-center gap-2">
                <label class="small text-muted mb-0">Rows:</label>
                <select class="form-select form-select-sm" style="width:90px" v-model.number="perPage">
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
                  style="width: 240px;"
                  placeholder="Search floor name..."
                  v-model="search"
                  @keyup.enter="fetchFloors(1)"
                />
              </div>
            </div>

            <!-- Status Color Legend Indicator -->
            <div class="floor-legend-card mb-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
              <div class="d-flex flex-wrap align-items-center gap-3 small">
                <span class="fw-bold text-dark"><i class="ti ti-info-circle text-primary me-1"></i>স্ট্যাটাস নির্দেশিকা:</span>
                <div class="d-flex align-items-center gap-1.5">
                  <span class="status-indicator available"></span>
                  <span class="text-secondary fw-semibold">খালি (Available)</span>
                </div>
                <div class="d-flex align-items-center gap-1.5">
                  <span class="status-indicator processing"></span>
                  <span class="text-warning fw-semibold" style="color: #b45309 !important;">⏳ প্রসেসিং (Processing)</span>
                </div>
                <div class="d-flex align-items-center gap-1.5">
                  <span class="status-indicator confirmed"></span>
                  <span class="text-danger fw-semibold">🔒 বুকিং কনফার্মড (Booked)</span>
                </div>
              </div>
              <div class="d-flex align-items-center gap-2 small">
                <span class="badge bg-light text-dark border px-2.5 py-1 fw-semibold"><i class="ti ti-user text-primary me-1"></i>Single Room (১ সিট)</span>
                <span class="badge bg-light text-dark border px-2.5 py-1 fw-semibold"><i class="ti ti-users text-warning me-1"></i>Double Room (২ সিট)</span>
              </div>
            </div>

            <div class="table-responsive">
              <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th style="width:60px" class="text-center">Sl</th>
                    <th style="width:120px" class="text-center">Image</th>
                    <th style="width:190px">Floor Details</th>
                    <th>Rooms & Seats</th>
                    <th style="width:150px" class="text-center">Actions</th>
                  </tr>
                </thead>
                <tbody v-if="floors.length">
                  <tr v-for="(f, idx) in floors" :key="f.id">
                    <td class="text-center fw-bold text-muted">{{ from + idx }}</td>
                    <td class="text-center">
                      <img v-if="f.image_url" :src="f.image_url" class="img-thumb" alt="floor" />
                      <span v-else class="text-muted small">No image</span>
                    </td>
                    <td>
                      <div class="fw-bold text-dark fs-6">{{ f.name }}</div>
                      <div class="small text-muted mt-1 fw-semibold">
                        <span class="text-primary font-monospace">{{ f.rooms ? f.rooms.length : 0 }} Rooms</span> · 
                        <span class="text-success font-monospace">{{ getTotalSeats(f) }} Beds</span>
                      </div>
                      <div class="mt-1.5 d-flex flex-wrap gap-1">
                        <span class="badge bg-light text-secondary border font-monospace" style="font-size: 11px;">
                          Single: {{ getSingleRoomCount(f) }}
                        </span>
                        <span class="badge bg-light text-secondary border font-monospace" style="font-size: 11px;">
                          Double: {{ getDoubleRoomCount(f) }} ({{ getDoubleSeatCount(f) }} Beds)
                        </span>
                      </div>
                      <div class="mt-1.5 d-flex flex-wrap gap-1">
                        <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace" style="font-size: 10.5px;">
                          খালি: {{ getAvailableSeatCount(f) }}
                        </span>
                        <span class="badge font-monospace" style="font-size: 10.5px; background: #fef08a; color: #78350f; border: 1px solid #d97706;">
                          প্রসেসিং: {{ getProcessingSeatCount(f) }}
                        </span>
                        <span class="badge font-monospace" style="font-size: 10.5px; background: #fecaca; color: #991b1b; border: 1px solid #dc2626;">
                          বুকিং: {{ getConfirmedSeatCount(f) }}
                        </span>
                      </div>
                    </td>
                    <td>
                      <div v-if="f.rooms && f.rooms.length">
                        <div class="floor-stats-strip mb-2.5 d-flex flex-wrap align-items-center justify-content-between gap-2">
                          <div class="d-flex flex-wrap align-items-center gap-2.5 small fw-semibold text-secondary">
                            <span><i class="ti ti-door text-primary me-1"></i>মোট রুম: <strong class="text-dark">{{ f.rooms.length }}</strong> টি</span>
                            <span class="text-muted">•</span>
                            <span><i class="ti ti-user text-info me-1"></i>সিঙ্গেল রুম: <strong class="text-dark">{{ getSingleRoomCount(f) }}</strong> টি ({{ getSingleSeatCount(f) }} সিট)</span>
                            <span class="text-muted">•</span>
                            <span><i class="ti ti-users text-warning me-1"></i>ডাবল রুম: <strong class="text-dark">{{ getDoubleRoomCount(f) }}</strong> টি ({{ getDoubleSeatCount(f) }} সিট)</span>
                            <span class="text-muted">•</span>
                            <span><i class="ti ti-bed text-success me-1"></i>সর্বমোট সিট: <strong class="text-dark">{{ getTotalSeats(f) }}</strong> টি</span>
                          </div>
                        </div>
                        <div class="room-wrap">
                          <template v-for="room in getSortedRooms(f.rooms)" :key="room.id">
                            <template v-if="room.seats && room.seats.length">
                              <div
                                v-for="seat in room.seats"
                                :key="seat.id"
                                class="room-box"
                                :class="getSeatStatusClass(seat)"
                                :title="getSeatTooltip(room, seat)"
                                @click="showSeatBookingInfo(room, seat)"
                              >
                                <span v-if="seat.booking_status === 'processing'" class="me-1" style="font-size: 11px;">⏳</span>
                                <span v-else-if="seat.booking_status === 'confirmed'" class="me-1" style="font-size: 11px;">🔒</span>
                                <span v-if="isDoubleRoom(room)" class="double-tag">Double</span>
                                <span>{{ formatSeatLabel(room.room_no, seat.seat_no, room.seats ? room.seats.length : 1, room) }}</span>
                              </div>
                            </template>
                            <div
                              v-else
                              class="room-box"
                              :class="getSeatStatusClass(room)"
                              :title="getSeatTooltip(room, room)"
                              @click="showSeatBookingInfo(room, room)"
                            >
                              <span v-if="room.booking_status === 'processing'" class="me-1" style="font-size: 11px;">⏳</span>
                              <span v-else-if="room.booking_status === 'confirmed'" class="me-1" style="font-size: 11px;">🔒</span>
                              <span v-if="isDoubleRoom(room)" class="double-tag">Double</span>
                              <span>{{ formatSeatLabel(room.room_no, null, 1) }}</span>
                            </div>
                          </template>
                        </div>
                      </div>
                      <span v-else class="text-muted small">No rooms</span>
                    </td>
                    <td>
                      <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-primary" @click="flooraddbutton(f)">
                          <i class="ti ti-plus me-1"></i>
                        </button>

                        <button class="btn btn-sm btn-primary" @click="openEditModal(f)">
                          <i class="ti ti-edit"></i>
                        </button>

                        <button class="btn btn-sm btn-danger" @click="openDeleteModal(f)">
                          <i class="ti ti-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
                <tbody v-else>
                  <tr>
                    <td colspan="5" class="text-center py-4 text-muted">
                      <span v-if="loading"><i class="fa fa-spinner fa-spin me-2"></i>Loading...</span>
                      <span v-else>No floors found</span>
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
                  @click="fetchFloors(currentPage - 1)"
                >
                  Previous
                </button>
                <button
                  class="btn btn-sm btn-secondary"
                  :disabled="currentPage >= totalPages || loading"
                  @click="fetchFloors(currentPage + 1)">
                  Next
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <FloorCreate
      :show="showCreateModal"
      :base-url="url"
      @close="showCreateModal = false"
      @created="handleCreated"
    />

    <!-- ROOM CREATE MODAL -->
    <Teleport to="body">
      <transition name="slide-fade">
        <div v-if="roomModal.open" class="xmask" @click.self="closeRoomModal">
          <div class="xwrap">
            <div class="xbox shadow-lg border-0" role="dialog" aria-modal="true">
              <div class="xhead d-flex justify-content-between align-items-center bg-light px-4 py-3 border-bottom">
                <div>
                  <h5 class="mb-0 fw-bold text-dark">Add Rooms</h5>
                  <small class="text-muted">
                    Floor: {{ roomModal.floor?.name || 'N/A' }}
                  </small>
                </div>
                <button type="button" class="btn-close" @click="closeRoomModal"></button>
              </div>

              <div class="xbody p-4">
                <div class="mb-3">
                  <label class="form-label fw-semibold">Add Room <span class="text-danger">*</span></label>
                  <div class="d-flex gap-2 flex-wrap">
                    <input
                      type="text"
                      class="form-control"
                      style="max-width: 220px;"
                      v-model.trim="roomModal.input"
                      placeholder="e.g. 202, g01"
                      @keyup.enter="addRoomChip"
                    />

                    <button class="btn btn-primary" type="button" @click="addRoomChip">
                      <i class="ti ti-plus me-1"></i> Add
                    </button>

                    <button class="btn btn-outline-danger" type="button" @click="clearRoomList">
                      Clear
                    </button>
                  </div>
                  <small v-if="errors.room_input" class="text-danger d-block mt-1">
                    {{ errors.room_input }}
                  </small>
                </div>

                <div class="room-chip-area">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>Rooms</strong>
                    <span class="badge bg-light text-primary">Total: {{ roomModal.list.length }}</span>
                  </div>

                  <div v-if="roomModal.list.length" class="room-chip-wrap">
                    <div v-for="(room, index) in roomModal.list" :key="index" class="room-chip">
                      <div>
                        <div class="small text-muted">Room</div>
                        <div class="fw-bold">{{ room }}</div>
                      </div>
                      <button type="button" class="remove-chip-btn" @click="removeRoomChip(index)">
                        ×
                      </button>
                    </div>
                  </div>

                  <div v-else class="text-muted small">
                    No rooms added yet.
                  </div>
                </div>
              </div>

              <div class="xfoot px-4 py-3 bg-light border-top d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-dark px-4" @click="closeRoomModal">Cancel</button>
                <button type="button" class="btn btn-success px-4 d-flex align-items-center" :disabled="savingRooms" @click="submitRooms">
                  <span v-if="savingRooms">
                    <span class="spinner-border spinner-border-sm me-2"></span> Saving...
                  </span>
                  <span v-else>
                    <i class="ti ti-device-floppy me-1"></i> Submit
                  </span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </Teleport>

    <!-- EDIT MODAL -->
    <Teleport to="body">
      <transition name="slide-fade">
        <div v-if="edit.open" class="xmask" @click.self="closeEditModal">
          <div class="xwrap">
            <div class="xbox shadow-lg border-0" role="dialog" aria-modal="true">
              <div class="xhead d-flex justify-content-between align-items-center bg-light px-4 py-3 border-bottom">
                <div>
                  <h5 class="mb-0 fw-bold text-dark">Edit Floor Details</h5>
                </div>
                <button type="button" class="btn-close" @click="closeEditModal"></button>
              </div>

              <form @submit.prevent="updateFloor">
                <div class="xbody p-4">
                  <div class="row g-4">
                    <div class="col-md-5">
                      <label class="form-label fw-semibold">Floor Visual</label>
                      <div class="image-upload-wrapper mb-2">
                        <div class="preview-box-modern shadow-sm border rounded overflow-hidden position-relative">
                          <img :src="edit.preview || edit.form.image_url || '/placeholder-floor.jpg'"
                               class="img-fluid w-100 object-fit-cover"
                               style="height: 180px;"
                               alt="Floor Preview" />
                          <div class="upload-overlay">
                            <input type="file" id="editImage" class="d-none" accept="image/*" @change="onEditImageChange" />
                            <label for="editImage" class="btn btn-sm btn-light shadow-sm">
                              <i class="ti ti-camera me-1"></i> Change Image
                            </label>
                          </div>
                        </div>
                      </div>
                      <small class="text-muted d-block text-center mt-2">JPG, PNG or WEBP. Max 4MB.</small>
                      <small v-if="errors.image" class="text-danger d-block text-center mt-1">{{ errors.image }}</small>
                    </div>

                    <div class="col-md-7">
                      <div class="mb-3">
                        <label class="form-label fw-semibold">Floor Name <span class="text-danger">*</span></label>
                        <input
                          type="text"
                          v-model.trim="edit.form.name"
                          class="form-control form-control-lg border-2-focus"
                          placeholder="e.g. VIP Floor"
                          required
                        />
                        <small v-if="errors.name" class="text-danger">{{ errors.name }}</small>
                      </div>

                      <div class="rooms-container border rounded p-3 bg-light-subtle">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                          <label class="form-label mb-0 fw-semibold text-primary">
                            <i class="ti ti-door-enter me-1"></i> Room Configuration
                          </label>
                          <span class="badge rounded-pill bg-primary" v-if="roomList.length">
                            {{ roomList.length }} Rooms
                          </span>
                        </div>

                        <!-- Add new room row in Edit Modal -->
                        <div class="d-flex gap-2 mb-3">
                          <input
                            type="text"
                            v-model="roomInput"
                            class="form-control form-control-sm"
                            placeholder="e.g. g01, 102"
                            @keyup.enter.prevent="addRoom"
                          />
                          <button type="button" class="btn btn-sm btn-primary text-nowrap" @click="addRoom">
                            <i class="ti ti-plus me-1"></i> Add Room
                          </button>
                        </div>
                        <small v-if="errors.rooms" class="text-danger d-block mb-2">{{ errors.rooms }}</small>

                        <div class="row g-2 scroll-y" style="max-height: 200px; overflow-x: hidden;">
                          <div class="col-6 col-sm-6" v-for="(room, index) in roomList" :key="room.id || index">
                            <div class="room-input-group d-flex align-items-center gap-1">
                              <input
                                type="text"
                                v-model="room.room_no"
                                class="form-control form-control-sm text-center fw-bold"
                                placeholder="Room No"
                              />
                              <button type="button" class="btn btn-sm btn-outline-danger p-1 border-0" @click="removeRoom(index)" title="Remove room" style="font-size: 1.1rem; line-height: 1;">
                                ×
                              </button>
                            </div>
                          </div>

                          <div class="col-12" v-if="roomList.length === 0">
                            <div class="p-3 text-center border border-dashed rounded bg-white">
                              <p class="text-muted small mb-0">No rooms assigned to this floor.</p>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="xfoot px-4 py-3 bg-light border-top d-flex justify-content-end gap-2">
                  <button type="button" class="btn btn-outline-dark px-4" @click="closeEditModal">Cancel</button>
                  <button type="submit" class="btn btn-success px-4 d-flex align-items-center" :disabled="savingEdit">
                    <span v-if="savingEdit">
                       <span class="spinner-border spinner-border-sm me-2"></span> Updating...
                    </span>
                    <span v-else>
                      <i class="ti ti-check me-1"></i> Save Changes
                    </span>
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </transition>
    </Teleport>

    <!-- DELETE MODAL -->
    <Teleport to="body">
      <transition name="slide-fade">
        <div v-if="del.open" class="xmask" @click.self="closeDelete">
          <div class="xwrap">
            <div class="xbox xsmall" role="dialog" aria-modal="true">
              <div class="xhead d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-danger">Delete Floor</h5>
                <button type="button" class="btn btn-sm btn-light" @click="closeDelete">
                  <i class="fa fa-times"></i>
                </button>
              </div>

              <div class="xbody">
                <div class="alert alert-warning mb-0">
                  Are you sure you want to delete: <b>{{ del.item?.name }}</b> ?
                </div>
              </div>

              <div class="xfoot d-flex justify-content-end gap-2">
                <button class="btn btn-outline-secondary" type="button" @click="closeDelete">Cancel</button>
                <button class="btn btn-danger" type="button" :disabled="savingDelete" @click="confirmDelete">
                  <span v-if="savingDelete"><i class="fa fa-spinner fa-spin me-1"></i> Deleting...</span>
                  <span v-else>Yes, Delete</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </Teleport>

  </div>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import Toastify from "toastify-js";
import "toastify-js/src/toastify.css";
import FloorCreate from "../../components/createform/FloorCreate.vue";

export default {
  name: "FloorList",
  components: { FloorCreate },

  data() {
    return {
      showCreateModal: false,
      floors: [],
      loading: false,
      search: "",
      perPage: 10,
      currentPage: 1,
      totalPages: 1,
      total: 0,
      from: 1,
      roomModal: {
        open: false,
        floor: null,
        input: "",
        list: [],
      },
      savingRooms: false,
      edit: {
        open: false,
        form: { id: null, name: "", image: null, image_url: "" },
        preview: "",
      },
      del: { open: false, item: null },
      savingEdit: false,
      savingDelete: false,
      roomList: [],
      roomInput: null,
      errors: {},
      _t: null,
    };
  },

  computed: {
    url() {
      return this.$store.state.url;
    },
  },

  mounted() {
    this.fetchFloors(1);
  },

  watch: {
    search() {
      clearTimeout(this._t);
      this._t = setTimeout(() => this.fetchFloors(1), 300);
    },
    perPage() {
      this.fetchFloors(1);
    },
    showCreateModal()      { this.syncBodyOverflow(); },
    "edit.open"()          { this.syncBodyOverflow(); },
    "del.open"()           { this.syncBodyOverflow(); },
    "roomModal.open"()     { this.syncBodyOverflow(); },
  },

  beforeUnmount() {
    clearTimeout(this._t);
    this.cleanEditPreview();
    document.body.style.overflow = "";
  },

  methods: {
    isDoubleRoom(room) {
      if (!room) return false;
      if (room.seats && room.seats.length > 1) return true;
      if (room.room_type && (room.room_type.toLowerCase().includes('double') || room.room_type.toLowerCase().includes('doubel'))) return true;
      return false;
    },

    getTotalSeats(floor) {
      if (!floor || !floor.rooms) return 0;
      return floor.rooms.reduce((acc, r) => {
        return acc + (r.seats && r.seats.length ? r.seats.length : 1);
      }, 0);
    },

    getSingleRoomCount(floor) {
      if (!floor || !floor.rooms) return 0;
      return floor.rooms.filter(r => !this.isDoubleRoom(r)).length;
    },

    getDoubleRoomCount(floor) {
      if (!floor || !floor.rooms) return 0;
      return floor.rooms.filter(r => this.isDoubleRoom(r)).length;
    },

    getSingleSeatCount(floor) {
      if (!floor || !floor.rooms) return 0;
      return floor.rooms.filter(r => !this.isDoubleRoom(r)).reduce((acc, r) => {
        return acc + (r.seats && r.seats.length ? r.seats.length : 1);
      }, 0);
    },

    getDoubleSeatCount(floor) {
      if (!floor || !floor.rooms) return 0;
      return floor.rooms.filter(r => this.isDoubleRoom(r)).reduce((acc, r) => {
        return acc + (r.seats && r.seats.length ? r.seats.length : 2);
      }, 0);
    },

    getAvailableSeatCount(floor) {
      if (!floor || !floor.rooms) return 0;
      let count = 0;
      floor.rooms.forEach(r => {
        if (r.seats && r.seats.length) {
          r.seats.forEach(s => {
            if (!s.booking_status || s.booking_status === 'available') count++;
          });
        } else {
          if (!r.booking_status || r.booking_status === 'available') count++;
        }
      });
      return count;
    },

    getProcessingSeatCount(floor) {
      if (!floor || !floor.rooms) return 0;
      let count = 0;
      floor.rooms.forEach(r => {
        if (r.seats && r.seats.length) {
          r.seats.forEach(s => {
            if (s.booking_status === 'processing') count++;
          });
        } else {
          if (r.booking_status === 'processing') count++;
        }
      });
      return count;
    },

    getConfirmedSeatCount(floor) {
      if (!floor || !floor.rooms) return 0;
      let count = 0;
      floor.rooms.forEach(r => {
        if (r.seats && r.seats.length) {
          r.seats.forEach(s => {
            if (s.booking_status === 'confirmed') count++;
          });
        } else {
          if (r.booking_status === 'confirmed') count++;
        }
      });
      return count;
    },

    getSeatStatusClass(item) {
      const status = item ? item.booking_status : 'available';
      if (status === 'confirmed') return 'room-status-confirmed';
      if (status === 'processing') return 'room-status-processing';
      return 'room-status-available';
    },

    getSeatTooltip(room, seat) {
      const status = (seat && seat.booking_status) ? seat.booking_status : 'available';
      const label = this.formatSeatLabel(room?.room_no, seat?.seat_no, room?.seats ? room.seats.length : 1, room);
      const name = seat && seat.occupant_name ? ` | শিক্ষার্থী: ${seat.occupant_name}` : '';
      const phone = seat && seat.occupant_phone ? ` | ফোন: ${seat.occupant_phone}` : '';

      if (status === 'confirmed') {
        return `[🔒 বুকিং কনফার্মড / অ্যাক্টিভ] ${label}${name}${phone}`;
      }
      if (status === 'processing') {
        return `[⏳ বুকিং প্রসেসিং চলছে] ${label}${name}${phone}`;
      }
      return `[খালি (Available)] ${label}`;
    },

    showSeatBookingInfo(room, seat) {
      const status = (seat && seat.booking_status) ? seat.booking_status : 'available';
      const label = this.formatSeatLabel(room?.room_no, seat?.seat_no, room?.seats ? room.seats.length : 1, room);

      if (status === 'available') {
        Swal.fire({
          icon: 'info',
          title: `<span class="text-primary">${label}</span>`,
          html: `
            <div class="text-start p-2 border rounded-3 bg-light">
              <div class="d-flex justify-content-between py-1 border-bottom">
                <span class="text-muted">স্ট্যাটাস:</span>
                <span class="badge bg-label-success fw-bold">খালি (Available)</span>
              </div>
              <div class="d-flex justify-content-between py-1 border-bottom">
                <span class="text-muted">রুম নম্বর:</span>
                <strong class="text-dark">${room.room_no}</strong>
              </div>
              <div class="d-flex justify-content-between py-1 border-bottom">
                <span class="text-muted">ব্লক / সিট:</span>
                <strong class="text-primary">${seat.seat_no || 'Single'}</strong>
              </div>
              <div class="d-flex justify-content-between pt-1">
                <span class="text-muted">মাসিক ভাড়া:</span>
                <strong class="text-success">৳ ${seat.price || room.price || '0'}</strong>
              </div>
            </div>
          `,
          confirmButtonText: 'ঠিক আছে',
        });
      } else {
        const isConfirmed = status === 'confirmed';
        Swal.fire({
          icon: isConfirmed ? 'success' : 'warning',
          title: `<span class="${isConfirmed ? 'text-danger' : 'text-warning'}">${label}</span>`,
          html: `
            <div class="text-start p-2 border rounded-3 ${isConfirmed ? 'bg-danger-subtle' : 'bg-warning-subtle'}">
              <div class="d-flex justify-content-between py-1 border-bottom">
                <span class="text-muted">বুকিং অবস্থা:</span>
                <span class="badge ${isConfirmed ? 'bg-danger' : 'bg-warning text-dark'} fw-bold">
                  ${isConfirmed ? '🔒 বুকিং কনফার্মড (Active)' : '⏳ বুকিং প্রসেসিং চলছে'}
                </span>
              </div>
              <div class="d-flex justify-content-between py-1 border-bottom">
                <span class="text-muted">শিক্ষার্থীর নাম:</span>
                <strong class="text-dark">${seat.occupant_name || 'N/A'}</strong>
              </div>
              <div class="d-flex justify-content-between py-1 border-bottom">
                <span class="text-muted">মোবাইল নম্বর:</span>
                <a href="tel:${seat.occupant_phone}" class="text-primary fw-bold text-decoration-none">${seat.occupant_phone || 'N/A'}</a>
              </div>
              ${seat.check_in ? `
              <div class="d-flex justify-content-between py-1 border-bottom">
                <span class="text-muted">ভর্তির তারিখ:</span>
                <strong class="text-dark">${seat.check_in}</strong>
              </div>` : ''}
              <div class="d-flex justify-content-between pt-1">
                <span class="text-muted">মাসিক ভাড়া:</span>
                <strong class="text-success">৳ ${seat.price || room.price || '0'}</strong>
              </div>
            </div>
          `,
          showCancelButton: true,
          confirmButtonText: '<i class="ti ti-user-check me-1"></i> হিস্ট্রি পেজে যান',
          cancelButtonText: 'বন্ধ করুন',
          confirmButtonColor: isConfirmed ? '#d33' : '#f59e0b',
        }).then((res) => {
          if (res.isConfirmed) {
            window.location.href = '/resident-history';
          }
        });
      }
    },

    syncBodyOverflow() {
      const anyOpen = this.showCreateModal || this.edit.open || this.del.open || this.roomModal.open;
      document.body.style.overflow = anyOpen ? "hidden" : "";
    },

    toast(text, type = "success") {
      const bg =
        type === "success"
          ? "linear-gradient(to right, #00b09b, #96c93d)"
          : type === "warning"
          ? "linear-gradient(to right, #f59e0b, #fbbf24)"
          : "linear-gradient(to right, #ff5f6d, #ffc371)";

      Toastify({
        text: text || (type === "success" ? "Done" : "Something went wrong"),
        duration: 2500,
        close: true,
        gravity: "top",
        position: "right",
        backgroundColor: bg,
      }).showToast();
    },

    async fetchFloors(page = 1) {
      this.loading = true;
      try {
        const res = await axios.get(`${this.url}floors-get`, {
          params: { page, per_page: this.perPage, search: this.search },
        });
        this.floors      = res.data.data         || [];
        this.currentPage = res.data.current_page || 1;
        this.totalPages  = res.data.last_page    || 1;
        this.total       = res.data.total        || 0;
        this.from        = res.data.from         || 1;
      } catch (e) {
        console.error(e);
        this.toast("Failed to load floors", "error");
      } finally {
        this.loading = false;
      }
    },

    openCreateFromComponent() {
      this.showCreateModal = true;
    },

    async handleCreated() {
      this.showCreateModal = false;
      await this.fetchFloors(this.currentPage);
    },

    // ROOM CREATE
    flooraddbutton(floor) {
      this.errors = {};
      this.roomModal.floor = floor;
      this.roomModal.input = "";
      this.roomModal.list  = [];
      this.roomModal.open  = true;
    },

    closeRoomModal() {
      this.roomModal.open  = false;
      this.roomModal.floor = null;
      this.roomModal.input = "";
      this.roomModal.list  = [];
      this.errors.room_input = null;
    },

    addRoomChip() {
      const value = (this.roomModal.input || "").trim();
      if (!value) {
        this.errors.room_input = "Room number is required";
        return;
      }
      if (this.roomModal.list.includes(value)) {
        this.toast("Room already added", "warning");
        return;
      }
      this.roomModal.list.push(value);
      this.roomModal.input = "";
      this.errors.room_input = null;
    },

    removeRoomChip(index) {
      this.roomModal.list.splice(index, 1);
    },

    clearRoomList() {
      this.roomModal.list  = [];
      this.roomModal.input = "";
      this.errors.room_input = null;
    },

    async submitRooms() {
      if (!this.roomModal.floor?.id) {
        this.toast("Floor not selected", "error");
        return;
      }
      if (!this.roomModal.list.length) {
        this.toast("Please add at least one room", "warning");
        return;
      }

      this.savingRooms = true;
      try {
        const payload = {
          floor_id: this.roomModal.floor.id,
          rooms:    this.roomModal.list,
        };

        const res = await axios.post(`${this.url}rooms-store-multiple`, payload);

        if (res.data.status) {
          this.toast(res.data.message || "Rooms created successfully", "success");
          this.closeRoomModal();
          await this.fetchFloors(this.currentPage);
        } else {
          this.toast(res.data.message || "Failed to create rooms", "error");
        }
      } catch (e) {
        console.error(e);
        const data = e?.response?.data;
        if (data?.existing_rooms?.length) {
          this.toast("Existing rooms: " + data.existing_rooms.join(", "), "error");
        } else {
          this.toast(data?.message || "Failed to create rooms", "error");
        }
      } finally {
        this.savingRooms = false;
      }
    },

    // EDIT
    openEditModal(floor) {
      this.errors = {};
      this.cleanEditPreview();
      this.edit.form = {
        id:        floor.id,
        name:      floor.name,
        image:     null,
        image_url: floor.image_url || "",
      };
      this.roomList = (floor.rooms || []).map((room) => ({
        id:      room.id,
        room_no: room.room_no,
      }));
      this.edit.open = true;
    },

    closeEditModal() {
      this.edit.open = false;
      this.errors    = {};
    },

    cleanEditPreview() {
      if (this.edit.preview) URL.revokeObjectURL(this.edit.preview);
      this.edit.preview = "";
    },

    onEditImageChange(e) {
      const file = e.target?.files?.[0] || null;
      this.edit.form.image = file;
      this.errors.image    = null;
      this.cleanEditPreview();
      this.edit.preview = file ? URL.createObjectURL(file) : "";
    },

    addRoom() {
      const val = String(this.roomInput || "").trim();
      if (!val) return;
      if (this.roomList.some((r) => String(r.room_no).trim().toLowerCase() === val.toLowerCase())) {
        this.toast(`Room "${val}" is already in the list`, "warning");
        return;
      }
      this.roomList.push({ id: null, room_no: val });
      this.roomInput    = "";
      this.errors.rooms = null;
    },

    removeRoom(index) {
      this.roomList.splice(index, 1);
    },

    validateEdit() {
      this.errors = {};
      if (!this.edit.form.name?.trim()) this.errors.name = "Floor name is required";
      if (this.roomList.length < 1)     this.errors.rooms = "At least one room must be added";
      return Object.keys(this.errors).length === 0;
    },

    async updateFloor() {
      if (!this.validateEdit()) {
        this.toast("Please check required fields", "warning");
        return;
      }

      this.savingEdit = true;
      try {
        const fd = new FormData();
        fd.append("name", this.edit.form.name);
        if (this.edit.form.image) fd.append("image", this.edit.form.image);
        fd.append("rooms", JSON.stringify(this.roomList));

        const res = await axios.post(
          `${this.url}floors-update/${this.edit.form.id}`,
          fd,
          { headers: { "Content-Type": "multipart/form-data" } }
        );

        this.toast(res.data?.message || "Updated successfully", "success");
        this.closeEditModal();
        await this.fetchFloors(this.currentPage);
      } catch (e) {
        const data = e?.response?.data;
        if (e?.response?.status === 422 && data?.errors) {
          Object.keys(data.errors).forEach((k) => {
            this.errors[k] = Array.isArray(data.errors[k]) ? data.errors[k][0] : data.errors[k];
          });
          this.toast("Validation error. Please check fields.", "error");
        } else {
          this.toast(data?.message || "Update failed", "error");
        }
      } finally {
        this.savingEdit = false;
      }
    },

    // DELETE
    openDeleteModal(f) {
      this.del.item = f;
      this.del.open = true;
    },

    closeDelete() {
      this.del.open = false;
      this.del.item = null;
    },

    async confirmDelete() {
      if (!this.del.item?.id) return;
      this.savingDelete = true;
      try {
        const res = await axios.delete(`${this.url}floors-delete/${this.del.item.id}`);
        this.toast(res.data?.message || "Floor deleted", "success");
        this.closeDelete();
        const willBeEmpty = this.floors.length === 1 && this.currentPage > 1;
        const page = willBeEmpty ? this.currentPage - 1 : this.currentPage;
        await this.fetchFloors(page);
      } catch (e) {
        const data = e?.response?.data;
        console.error(e);
        this.toast(data?.message || "Delete failed", "error");
      } finally {
        this.savingDelete = false;
      }
    },

    getSortedRooms(rooms) {
      if (!rooms || !rooms.length) return [];
      return [...rooms].sort((a, b) => {
        const rNoA = String(a.room_no || '').trim();
        const rNoB = String(b.room_no || '').trim();
        const mA = rNoA.match(/^([A-Za-z]+)[\-_](\d+)$/);
        const mB = rNoB.match(/^([A-Za-z]+)[\-_](\d+)$/);
        const letterA = mA ? mA[1].toUpperCase() : '';
        const letterB = mB ? mB[1].toUpperCase() : '';
        const numA = mA ? parseInt(mA[2], 10) : 0;
        const numB = mB ? parseInt(mB[2], 10) : 0;
        if (letterA !== letterB) return letterA.localeCompare(letterB);
        return numA - numB;
      });
    },

    formatSeatLabel(roomNo, seatNo, roomSeatsCount, roomObj) {
      if (!roomNo) return seatNo || '';
      const rNo = String(roomNo).trim();
      const sNo = seatNo ? String(seatNo).trim() : '';

      let blockLetter = null;
      let roomNum = null;

      // 1. Match A-15 or B-15 or E-1
      const m1 = rNo.match(/^([A-Za-z]+)[\-_](\d+)$/);
      if (m1) {
        blockLetter = m1[1].toUpperCase();
        roomNum = parseInt(m1[2], 10);
      }

      // 2. Match 15-Block B or 15-Block-B or 1-Block E
      if (!blockLetter) {
        const m2 = rNo.match(/^(\d+)[\-_ ]*Block[\-_ ]*([A-Za-z]+)/i);
        if (m2) {
          roomNum = parseInt(m2[1], 10);
          blockLetter = m2[2].toUpperCase();
        }
      }

      // 3. Match Block B-15 or Block-B-15
      if (!blockLetter) {
        const m3 = rNo.match(/^Block[\-_ ]*([A-Za-z]+)[\-_ ]*(\d+)/i);
        if (m3) {
          blockLetter = m3[1].toUpperCase();
          roomNum = parseInt(m3[2], 10);
        }
      }

      // 4. Check if seatNo has block (e.g. Block A or Block B-1)
      if (!blockLetter && sNo) {
        const ms = sNo.match(/Block[\-_ ]*([A-Za-z]+)/i);
        if (ms) {
          blockLetter = ms[1].toUpperCase();
        }
      }

      // 5. If no block letter was found:
      if (!blockLetter) {
        if (roomSeatsCount && roomSeatsCount > 1 && sNo) {
          const sm = sNo.match(/(\d+)$/);
          const seatSub = sm ? sm[1] : '';
          if (seatSub && seatSub !== rNo) {
            return `${rNo}-${seatSub}`;
          }
        }
        return rNo;
      }

      // When block letter is present:
      if (roomNum === null) {
        const numMatch = rNo.match(/(\d+)/);
        roomNum = numMatch ? parseInt(numMatch[1], 10) : rNo;
      }

      // Determine double from room_type first, then fallback to seat count
      const rawType = (roomObj?.room_type || '').toLowerCase();
      const isDouble = rawType.includes('double') || rawType.includes('doubel') || (roomSeatsCount && roomSeatsCount > 1);

      if (!isDouble) {
        // Single room — never show seat suffix
        return `${roomNum}-Block ${blockLetter}`;
      }

      // Double room — determine seat number (1 or 2)
      let seatSub = '1';
      const sm = sNo.match(/(\d+)$/);
      if (sm) {
        seatSub = sm[1];
      } else if (sNo.toLowerCase().includes('2') || sNo.toLowerCase().includes('b')) {
        seatSub = '2';
      }
      return `${roomNum}-Block ${blockLetter}-${seatSub}`;
    },

    getSeatStatusClass(item) {
      if (!item) return 'room-status-available';
      if (item.booking_status === 'confirmed') return 'room-status-confirmed';
      if (item.booking_status === 'processing') return 'room-status-processing';
      return 'room-status-available';
    },

    getSeatTooltip(room, seat) {
      const item = seat || room;
      const label = this.formatSeatLabel(room?.room_no, seat?.seat_no, room?.seats ? room.seats.length : 1, room);
      if (item?.booking_status === 'confirmed') {
        return `${label} - বুকিং কনফার্মড (${item.occupant_name || 'Booked'})`;
      }
      if (item?.booking_status === 'processing') {
        return `${label} - বুকিং প্রসেসিং চলছে (${item.occupant_name || 'Processing'})`;
      }
      return `${label} - খালি আছে (Available)`;
    },

    showSeatBookingInfo(room, seat) {
      const item = seat || room;
      const label = this.formatSeatLabel(room?.room_no, seat?.seat_no, room?.seats ? room.seats.length : 1, room);
      if (item?.booking_status === 'confirmed') {
        Swal.fire({
          icon: 'info',
          title: `<span style="color:#dc2626;">🔒 ${label} - বুকিং কনফার্মড</span>`,
          html: `
            <div style="text-align: left; padding: 10px; font-size: 14px; line-height: 1.8;">
              <p><strong>বোর্ডার নাম:</strong> ${item.occupant_name || 'N/A'}</p>
              <p><strong>মোবাইল নম্বর:</strong> ${item.occupant_phone || 'N/A'}</p>
              <p><strong>চেক-ইন ডেট:</strong> ${item.check_in || 'N/A'}</p>
              <p><strong>বুকিং স্ট্যাটাস:</strong> <span class="badge bg-success">Confirmed / Active</span></p>
            </div>
          `,
          confirmButtonText: 'ঠিক আছে',
          confirmButtonColor: '#0d6efd'
        });
      } else if (item?.booking_status === 'processing') {
        Swal.fire({
          icon: 'warning',
          title: `<span style="color:#d97706;">⏳ ${label} - বুকিং প্রসেসিং চলছে</span>`,
          html: `
            <div style="text-align: left; padding: 10px; font-size: 14px; line-height: 1.8;">
              <p><strong>আবেদনকারীর নাম:</strong> ${item.occupant_name || 'N/A'}</p>
              <p><strong>মোবাইল নম্বর:</strong> ${item.occupant_phone || 'N/A'}</p>
              <p><strong>চেক-ইন ডেট:</strong> ${item.check_in || 'N/A'}</p>
              <p><strong>বুকিং স্ট্যাটাস:</strong> <span class="badge bg-warning text-dark">Processing</span></p>
            </div>
          `,
          confirmButtonText: 'ঠিক আছে',
          confirmButtonColor: '#f59e0b'
        });
      } else {
        Swal.fire({
          icon: 'success',
          title: `<span style="color:#024080;">${label}</span>`,
          text: 'এই সিট/রুমটি খালি আছে (Available for Booking)',
          confirmButtonText: 'ঠিক আছে',
          confirmButtonColor: '#0d6efd'
        });
      }
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

.xmask {
  position: fixed;
  inset: 0;
  z-index: 20000;
  background: rgba(0,0,0,.55);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  overflow-y: auto;
}
.xwrap { width: 100%; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
.xbox {
  width: min(94vw, 900px);
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 18px 55px rgba(0,0,0,.25);
  border: 1px solid rgba(0,0,0,.06);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}
.xbox.xsmall { width: min(94vw, 520px); }
.xhead { padding: 14px 16px; border-bottom: 1px solid #eef2f7; background: #fff; }
.xbody { padding: 16px; max-height: calc(100vh - 190px); overflow: auto; }
.xfoot { padding: 12px 16px; border-top: 1px solid #eef2f7; background: #fff; }

.preview-box-modern {
  background: #f8f9fa;
  min-height: 180px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.upload-overlay {
  position: absolute;
  top: 0; left: 0; width: 100%; height: 100%;
  background: rgba(0,0,0,0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s ease;
}
.preview-box-modern:hover .upload-overlay { opacity: 1; }

.room-input-group { position: relative; }
.room-input-group .form-control {
  border-radius: 8px;
  padding-right: 25px;
  border: 1px solid #dee2e6;
}

.border-2-focus:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
}

.bg-light-subtle { background-color: #fcfcfd !important; }

.scroll-y::-webkit-scrollbar { width: 5px; }
.scroll-y::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }

.floor-legend-card {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 8px 14px;
}

.status-indicator {
  display: inline-block;
  width: 12px;
  height: 12px;
  border-radius: 3px;
}
.status-indicator.available {
  background: #ffffff;
  border: 1.5px solid #94a3b8;
}
.status-indicator.processing {
  background: #fde047;
  border: 1.5px solid #d97706;
}
.status-indicator.confirmed {
  background: #f87171;
  border: 1.5px solid #dc2626;
}

.floor-stats-strip {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 6px 12px;
}

.room-wrap {
  display: flex;
  flex-wrap: wrap;
  gap: 7px;
}

.room-box {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 5px 10px;
  border-radius: 7px;
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 0.1px;
  transition: all 0.15s ease-in-out;
  cursor: pointer;
  user-select: none;
}

/* 1. Available (খালি): Clean crisp slate card */
.room-box.room-status-available {
  background: #ffffff;
  border: 1.5px solid #cbd5e1;
  color: #1e293b;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
}
.room-box.room-status-available:hover {
  background: #f1f5f9;
  border-color: #64748b;
  color: #0f172a;
  transform: translateY(-1px);
  box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
}

/* 2. Processing (প্রসেসিং): Deeper vibrant amber */
.room-box.room-status-processing {
  background: #fef08a !important;
  border: 1.5px solid #d97706 !important;
  color: #78350f !important;
  font-weight: 700;
  box-shadow: 0 1px 3px rgba(217, 119, 6, 0.15);
}
.room-box.room-status-processing:hover {
  background: #fde047 !important;
  border-color: #b45309 !important;
  color: #451a03 !important;
  transform: translateY(-1px);
  box-shadow: 0 3px 8px rgba(217, 119, 6, 0.25);
}

/* 3. Confirmed (বুকিং কনফার্মড): Deeper rich rose/red */
.room-box.room-status-confirmed {
  background: #fecaca !important;
  border: 1.5px solid #dc2626 !important;
  color: #991b1b !important;
  font-weight: 700;
  box-shadow: 0 1px 3px rgba(220, 38, 38, 0.15);
}
.room-box.room-status-confirmed:hover {
  background: #fca5a5 !important;
  border-color: #b91c1c !important;
  color: #7f1d1d !important;
  transform: translateY(-1px);
  box-shadow: 0 3px 8px rgba(220, 38, 38, 0.25);
}

/* Double seat subtle badge */
.double-tag {
  display: inline-flex;
  align-items: center;
  font-size: 9.5px;
  font-weight: 700;
  background: #fef3c7;
  color: #b45309;
  border: 1px solid #fde68a;
  border-radius: 4px;
  padding: 1px 4px;
  margin-right: 5px;
  line-height: 1.1;
}

.room-chip-area {
  border: 1px dashed #d9d9d9;
  padding: 14px;
  border-radius: 14px;
  background: #fafafa;
}
.room-chip-wrap { display: flex; flex-wrap: wrap; gap: 10px; }
.room-chip {
  min-width: 120px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  background: #fff;
  border: 1px solid #e4e4e4;
  border-radius: 12px;
  padding: 10px 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.remove-chip-btn {
  width: 34px;
  height: 34px;
  border: 0;
  border-radius: 8px;
  background: #ff4d5a;
  color: #fff;
  font-size: 22px;
  cursor: pointer;
}

.slide-fade-enter-active,
.slide-fade-leave-active { transition: all .18s ease; }
.slide-fade-enter-from,
.slide-fade-leave-to { opacity: 0; transform: translateY(10px) scale(.98); }
</style>