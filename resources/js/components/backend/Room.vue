<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-10 col-xl-12">
        <div class="card mb-4 shadow-sm">
          <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3">
            <div>
              <h5 class="card-title mb-0">Room List</h5>
            </div>
            <button class="btn btn-primary" type="button" @click="openCreateFromComponent">
              <i class="ti ti-plus me-1"></i> Add Room
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
                  placeholder="Search room / floor..."
                  v-model="search"
                  @keyup.enter="getRooms(1)"
                />
              </div>
            </div>
            <div class="table-responsive">
              <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th style="width:70px">Sl</th>
                    <th style="width:130px">Image</th>
                    <th>Room</th>
                    <th style="width:180px">Floor</th>
                    <th style="width:110px">Room Size</th>
                    <th style="width:120px">Breakfast</th>
                    <th style="width:120px">Attached Bathroom</th>
                    <th style="width:120px">Balcony</th>
                    <th style="width:120px">Windows</th>
                    <th style="width:120px">Room Type</th>
                    <th style="width:120px">AC Status</th>
                    <th style="width:120px">Booking Status</th>
                    <th style="width:140px">Actions</th>
                  </tr>
                </thead>
                <tbody v-if="rooms.length">
                 <tr  v-for="(r, idx) in rooms" :key="r.id" :class="{ 'room-received': r.status == 1 }">
                    <td>{{ from + idx }}</td>
                    <td>
                      <img v-if="r.image_url" :src="r.image_url" class="img-thumb" alt="room" />
                      <span v-else class="text-muted small">No image</span>
                    </td>
                    <td>
                      <div class="fw-semibold">Room {{ r.room_no }}</div>
                      <div class="mt-1 d-flex flex-wrap gap-1">
                        <span v-for="s in r.seats" :key="s.id" class="badge" :class="s.status == 1 ? 'bg-danger' : 'bg-success'" style="font-size: 10px;">
                          {{ s.seat_no }} (৳{{ s.price }}<span v-if="Number(s.advance_price) > 0">| Adv: ৳{{ s.advance_price }}</span>)
                        </span>
                      </div>
                    </td>
                    <td><div class="fw-semibold">{{ r.floor?.name || "-" }}</div></td>
                    <td><span class="fw-semibold">{{ r.room_size ?? "-" }}</span></td>
                    <td><span class="fw-semibold">{{ r.breakfast ?? "-" }}</span></td>
                    <td><span class="fw-semibold">{{ r.attached_bathroom ?? "-" }}</span></td>
                    <td><span class="fw-semibold">{{ r.balcony ?? "-" }}</span></td>
                    <td><span class="fw-semibold">{{ r.windows ?? "-" }}</span></td>
                    <td><span class="fw-semibold">{{ r.room_type ?? "-" }}</span></td>
                    <td><span class="fw-semibold">{{ r.ac_status ?? "-" }}</span></td>
                  <td>
                    <span v-if="r.status == 0" class="fw-semibold text-success">
                      Available Room
                    </span>
                    <span v-else-if="r.status == 1" class="received-badge">
                      <i class="ti ti-alert-circle me-1"></i> Received Room
                    </span>
                    <span v-else>-</span>
                  </td>
                 <td>
                    <div class="d-flex gap-2 align-items-center">
                      <button 
                        v-if="r.status != 1"
                        class="btn btn-sm btn-primary" 
                        @click="openEditModal(r)">
                        <i class="ti ti-edit"></i>
                      </button>
                      <button 
                        v-if="r.status != 1"
                        class="btn btn-sm btn-danger" 
                        @click="openDeleteModal(r)">
                        <i class="ti ti-trash"></i>
                      </button>
                    </div>
                  </td>
                  </tr>
                </tbody>
                <tbody v-else>
                  <tr>
                    <td colspan="14" class="text-center py-4 text-muted">
                      <span v-if="loading"><i class="fa fa-spinner fa-spin me-2"></i>Loading...</span>
                      <span v-else>No rooms found</span>
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
                <button class="btn btn-sm btn-secondary" :disabled="currentPage <= 1 || loading" @click="getRooms(currentPage - 1)">Previous</button>
                <button class="btn btn-sm btn-secondary" :disabled="currentPage >= totalPages || loading" @click="getRooms(currentPage + 1)">Next</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- CREATE COMPONENT -->
    <RoomCreate
      :show="showCreateModal"
      :base-url="url"
      @close="showCreateModal = false"
      @created="handleCreated"
    />

    <!-- EDIT MODAL -->
    <Teleport to="body">
      <transition name="slide-fade">
        <div v-if="edit.open" class="xmask" @click.self="closeEditModal">
          <div class="xwrap">
            <div class="xbox shadow-lg border-0" role="dialog" aria-modal="true">
              <div class="xhead d-flex justify-content-between align-items-center bg-light px-4 py-3 border-bottom">
                <h5 class="mb-0 fw-bold">Edit Room</h5>
                <button type="button" class="btn-close" @click="closeEditModal"></button>
              </div>

              <form @submit.prevent="updateRoom">
                <div class="xbody p-4 custom-scrollbar" style="max-height:75vh;overflow-y:auto;">
                  <div class="row g-4">

                    <!-- LEFT -->
                    <div class="col-12 col-lg-6">
                      <div class="mb-3">
                        <label class="form-label fw-bold">Room No</label>
                        <input class="form-control bg-light" :value="edit.form.room_no" disabled />
                      </div>
                      <div class="mb-3">
                        <label class="form-label fw-bold">Price <code>*</code></label>
                        <input type="number" class="form-control" v-model.number="edit.form.price" required />
                        <small v-if="errors.price" class="text-danger">{{ errors.price }}</small>
                      </div>
                      <div class="mb-3">
                        <label class="form-label fw-bold">Room Size <code>*</code></label>
                        <input type="text" class="form-control" v-model.trim="edit.form.room_size" required />
                        <small v-if="errors.room_size" class="text-danger">{{ errors.room_size }}</small>
                      </div>
                      <div class="mb-3">
                        <label class="form-label fw-bold">Breakfast <code>*</code></label>
                        <select class="form-select" v-model="edit.form.breakfast" required>
                          <option value="">select</option>
                          <option value="yes">yes</option>
                          <option value="no">no</option>
                        </select>
                      </div>
                    </div>

                    <!-- RIGHT -->
                    <div class="col-12 col-lg-6">

                      <!--  MULTIPLE IMAGE SECTION -->
                      <div class="mb-4">
                        <label class="form-label fw-bold">
                          Room Images
                          <span class="text-muted fw-normal small ms-1"></span>
                        </label>

                        <!-- Image Grid -->
                        <div class="edit-image-grid mb-2">

                          <!--  images (DB  load ) -->
                          <div
                            v-for="(url, i) in edit.existingImages"
                            :key="'ex'+i"
                            class="edit-img-item position-relative"
                          >
                            <img :src="url" class="edit-img-preview" />
                            <button type="button" class="remove-btn" @click="removeExistingImage(i)" title="Remove">
                              <i class="fa fa-times"></i>
                            </button>
                            <span v-if="i === 0 && edit.newPreviews.length === 0" class="main-badge">Main</span>
                          </div>

                          <!--  selected images preview -->
                          <div
                            v-for="(src, i) in edit.newPreviews"
                            :key="'new'+i"
                            class="edit-img-item position-relative"
                          >
                            <img :src="src" class="edit-img-preview" />
                            <button type="button" class="remove-btn" @click="removeNewEditImage(i)" title="Remove">
                              <i class="fa fa-times"></i>
                            </button>
                            <span v-if="i === 0 && edit.existingImages.length === 0" class="main-badge">Main</span>
                          </div>

                          <!--  Add More Button -->
                          <div class="edit-img-item add-more-box">
                            <input type="file" id="editAddMore" class="d-none" accept="image/*" multiple @change="onEditAddMore" />
                            <label for="editAddMore" class="add-more-label d-flex flex-column align-items-center justify-content-center h-100 cursor-pointer">
                              <i class="fa fa-plus text-primary fs-5"></i>
                              <small class="text-muted mt-1" style="font-size:10px">Add</small>
                            </label>
                          </div>

                        </div>

                        <small class="text-muted">
                          <i class="fa fa-images me-1"></i>
                          {{ edit.existingImages.length + edit.newPreviews.length }} image(s) total
                        </small>
                      </div>
                      <!-- END IMAGE SECTION -->

                      <div class="row g-2">
                        <div class="col-6">
                          <label class="form-label small fw-bold text-uppercase">Attached Bathroom <code>*</code></label>
                          <select class="form-select" v-model="edit.form.attached_bathroom" required>
                            <option value="">select</option>
                            <option value="yes">yes</option>
                            <option value="no">no</option>
                          </select>
                        </div>
                        <div class="col-6">
                          <label class="form-label small fw-bold text-uppercase">Room Type <code>*</code></label>
                          <select class="form-select" v-model="edit.form.room_type" required>
                            <option value="">Select</option>
                            <option value="Singel">Single Room</option>
                            <option value="Doubel">Double Sharing Room</option>
                          </select>
                        </div>
                        <div class="col-6">
                          <label class="form-label small fw-bold text-uppercase">AC Status <code>*</code></label>
                          <select class="form-select" v-model.number="edit.form.acstatus" required>
                            <option value="">select</option>
                            <option :value="1">Ac</option>
                            <option :value="2">Non Ac</option>
                          </select>
                        </div>
                        <div class="col-6">
                          <label class="form-label small fw-bold text-uppercase">Windows <code>*</code></label>
                          <select class="form-select" v-model="edit.form.windows" required>
                            <option value="">select</option>
                            <option value="yes">yes</option>
                            <option value="no">no</option>
                          </select>
                        </div>
                        <div class="col-12 mt-2">
                          <label class="form-label small fw-bold text-uppercase">Balcony <code>*</code></label>
                          <select class="form-select" v-model="edit.form.balcony" required>
                            <option value="">select</option>
                            <option value="yes">yes</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <!-- Seats Edit Section -->
                    <div class="col-12 border-top pt-3 mt-3">
                      <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label small fw-bold text-uppercase mb-0">
                          <i class="fa fa-bed me-1 text-primary"></i> Seats Configuration (সিট সমূহের বিবরণ)
                        </label>
                        <button type="button" class="btn btn-sm btn-primary py-0 px-2" style="font-size:11px" @click="addEditSeat" :disabled="edit.form.room_type === 'Singel'">
                          <i class="fa fa-plus me-1"></i> Add Seat
                        </button>
                      </div>

                      <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle text-center mb-0" style="font-size: 12px;">
                          <thead class="table-light">
                            <tr>
                              <th>Seat Name/No *</th>
                              <th>Monthly Rent (৳) *</th>
                              <th>Advance Deposit (৳)</th>
                              <th style="width: 40px;">Action</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="(seat, idx) in edit.form.seats" :key="idx">
                              <td>
                                <input type="text" class="form-control form-control-sm text-center py-0" v-model="seat.seat_no" placeholder="e.g. Seat A" required />
                              </td>
                              <td>
                                <input type="number" class="form-control form-control-sm text-center py-0" v-model.number="seat.price" min="0" placeholder="Monthly rent" required />
                              </td>
                              <td>
                                <input type="number" class="form-control form-control-sm text-center py-0" v-model.number="seat.advance_price" min="0" placeholder="Advance deposit" />
                              </td>
                              <td>
                                <button type="button" class="btn btn-sm btn-danger btn-xs py-0 px-1" @click="removeEditSeat(idx)" :disabled="edit.form.seats && edit.form.seats.length <= 1">
                                  <i class="fa fa-trash"></i>
                                </button>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>

                  </div>
                </div>

                <div class="xfoot p-3 bg-light border-top d-flex justify-content-end gap-2">
                  <button type="button" class="btn btn-outline-secondary px-4" @click="closeEditModal">Cancel</button>
                  <button type="submit" class="btn btn-success px-4" :disabled="savingEdit">
                    <span v-if="savingEdit"><i class="fa fa-spinner fa-spin me-1"></i> Updating...</span>
                    <span v-else><i class="ti ti-device-floppy me-1"></i> Update</span>
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
                <h5 class="mb-0 text-danger">Delete Room</h5>
                <button type="button" class="btn btn-sm btn-light" @click="closeDelete">
                  <i class="fa fa-times"></i>
                </button>
              </div>
              <div class="xbody">
                <div class="alert alert-warning mb-0">
                  Are you sure you want to delete: <b>Room {{ del.item?.room_no }}</b> ?
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
import Toastify from "toastify-js";
import "toastify-js/src/toastify.css";
import RoomCreate from "../../components/createform/RoomCreate.vue";

export default {
  name: "RoomList",
  components: { RoomCreate },

  data() {
    return {
      showCreateModal: false,
      rooms: [],
      loading: false,
      search: "",
      perPage: 10,
      currentPage: 1,
      totalPages: 1,
      total: 0,
      from: 1,


      savingActive: false,
      isEditInitializing: false,

      edit: {
        open: false,
        form: {
          id: null,
          room_no: "",
          price: "",
          room_size: "",
          max_people: "",
          breakfast: "",
          attached_bathroom: "",
          room_type: "",
          ac_status: "",
          acstatus: "",
          windows: "",
          balcony: "",
          newImages: [],    //  file objects
          keepImages: [],   //  filenames 
          seats: [],
        },
        existingImages: [], // image URLs (show)
        newPreviews: [],    //image preview URLs
      },

      del: { open: false, item: null },
      savingEdit: false,
      savingDelete: false,
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
    this.getRooms(1);
  },

  watch: {
    search() {
      clearTimeout(this._t);
      this._t = setTimeout(() => this.getRooms(1), 300);
    },
    perPage() {
      this.getRooms(1);
    },
    showCreateModal() { this.syncBodyOverflow(); },
    "edit.open"()     { this.syncBodyOverflow(); },
    "del.open"()      { this.syncBodyOverflow(); },
    "edit.form.acstatus"(v) {
      if (v === 1) this.edit.form.ac_status = "Ac";
      else if (v === 2) this.edit.form.ac_status = "Non Ac";
      else this.edit.form.ac_status = "";
    },
    "edit.form.price"(newVal) {
      if (this.isEditInitializing) return;
      if (this.edit.form && this.edit.form.seats) {
        this.edit.form.seats.forEach(seat => {
          seat.price = newVal;
        });
      }
    },
    "edit.form.room_type"(newVal) {
      if (this.isEditInitializing) return;
      if (!this.edit.form || !this.edit.form.seats) return;
      if (newVal === "Singel") {
        if (this.edit.form.seats.length > 1) {
          this.edit.form.seats = this.edit.form.seats.slice(0, 1);
        }
        if (this.edit.form.seats[0] && !this.edit.form.seats[0].price) {
          this.edit.form.seats[0].price = this.edit.form.price;
        }
      } else if (newVal === "Doubel") {
        if (this.edit.form.seats.length === 1) {
          this.edit.form.seats.push({
            seat_no: "Seat-B",
            price: this.edit.form.price || "",
            advance_price: 0
          });
        }
        if (this.edit.form.seats[0] && !this.edit.form.seats[0].price) {
          this.edit.form.seats[0].price = this.edit.form.price;
        }
        if (this.edit.form.seats[1] && !this.edit.form.seats[1].price) {
          this.edit.form.seats[1].price = this.edit.form.price;
        }
      }
    },
  },

  beforeUnmount() {
    clearTimeout(this._t);
    this.cleanEditState();
    document.body.style.overflow = "";
  },

  methods: {



    syncBodyOverflow() {
      const anyOpen = this.showCreateModal || this.edit.open || this.del.open;
      document.body.style.overflow = anyOpen ? "hidden" : "";
    },

    toast(text, type = "success") {
      const bg =
        type === "success" ? "linear-gradient(to right, #00b09b, #96c93d)"
        : type === "warning" ? "linear-gradient(to right, #f59e0b, #fbbf24)"
        : "linear-gradient(to right, #ff5f6d, #ffc371)";
      Toastify({
        text: text || (type === "success" ? "Done" : "Something went wrong"),
        duration: 2500, close: true, gravity: "top", position: "right",
        backgroundColor: bg,
      }).showToast();
    },

    async getRooms(page = 1) {
      this.loading = true;
      try {
        const res = await axios.get(`${this.url}rooms-get`, {
          params: { page, per_page: this.perPage, search: this.search },
        });
        this.rooms       = res.data.data         || [];
        this.currentPage = res.data.current_page || 1;
        this.totalPages  = res.data.last_page    || 1;
        this.total       = res.data.total        || 0;
        this.from        = res.data.from         || 1;
      } catch (e) {
        this.toast("Failed to load rooms", "error");
      } finally {
        this.loading = false;
      }
    },

    openCreateFromComponent() {
      this.showCreateModal = true;
    },

    async handleCreated() {
      this.showCreateModal = false;
      await this.getRooms(this.currentPage);
    },

    //  Edit modal open - DB images load
    openEditModal(r) {
      this.isEditInitializing = true;
      this.errors = {};
      this.cleanEditState();

      const existingUrls = Array.isArray(r.all_image_urls)
        ? r.all_image_urls
        : (r.image_url ? [r.image_url] : []);

      const existingFilenames = Array.isArray(r.image)
        ? r.image
        : (r.image ? [r.image] : []);

      this.edit.existingImages = [...existingUrls];
      this.edit.newPreviews    = [];

      this.edit.form = {
        id:                r.id,
        room_no:           r.room_no,
        price:             r.price             ?? "",
        room_size:         r.room_size         ?? "",
        max_people:        r.max_people        ?? "",
        breakfast:         r.breakfast         ?? "",
        attached_bathroom: r.attached_bathroom ?? "",
        room_type:         r.room_type         ?? "",
        acstatus:          r.acstatus          ?? (r.ac_status === "Ac" ? 1 : r.ac_status === "Non Ac" ? 2 : ""),
        ac_status:         r.ac_status         ?? "",
        windows:           r.windows           ?? "",
        balcony:           r.balcony           ?? "",
        newImages:         [],
        keepImages:        [...existingFilenames],
        seats:             r.seats ? JSON.parse(JSON.stringify(r.seats)) : [
          { seat_no: "Seat-A", price: r.price || "", advance_price: 0 }
        ],
      };

      this.edit.open = true;
      this.$nextTick(() => {
        this.isEditInitializing = false;
      });
    },

    closeEditModal() {
      this.edit.open = false;
      this.errors = {};
      this.cleanEditState();
    },

    //  State clean
    cleanEditState() {
      this.edit.newPreviews.forEach(url => URL.revokeObjectURL(url));
      this.edit.newPreviews    = [];
      this.edit.existingImages = [];
      if (this.edit.form) {
        this.edit.form.newImages  = [];
        this.edit.form.keepImages = [];
      }
    },

    //  new image add (Add More button)
    onEditAddMore(e) {
      const files = Array.from(e.target?.files || []);
      if (!files.length) return;
      this.edit.form.newImages = [...this.edit.form.newImages, ...files];
      const newUrls = files.map(f => URL.createObjectURL(f));
      this.edit.newPreviews = [...this.edit.newPreviews, ...newUrls];
      e.target.value = "";
    },

    //  new image remove
    removeNewEditImage(index) {
      URL.revokeObjectURL(this.edit.newPreviews[index]);
      this.edit.newPreviews.splice(index, 1);
      this.edit.form.newImages.splice(index, 1);
    },

    //  old image remove
    removeExistingImage(index) {
      this.edit.existingImages.splice(index, 1);
      this.edit.form.keepImages.splice(index, 1);
    },

    //  Update submit
    async updateRoom() {
      this.savingEdit = true;
      try {
        const fd = new FormData();
        fd.append("price",             this.edit.form.price);
        fd.append("room_size",         this.edit.form.room_size);
        fd.append("max_people",        this.edit.form.max_people);
        fd.append("breakfast",         this.edit.form.breakfast);
        fd.append("attached_bathroom", this.edit.form.attached_bathroom);
        fd.append("room_type",         this.edit.form.room_type);
        fd.append("ac_status",         this.edit.form.ac_status);
        fd.append("windows",           this.edit.form.windows);
        fd.append("balcony",           this.edit.form.balcony);
        fd.append("acstatus",          this.edit.form.acstatus);
        fd.append("seats",             JSON.stringify(this.edit.form.seats));

        // old image
        this.edit.form.keepImages.forEach(name => {
          fd.append("keep_images[]", name);
        });

        // new  images
        this.edit.form.newImages.forEach(img => {
          fd.append("new_images[]", img);
        });

        const res = await axios.post(
          `${this.url}room-update/${this.edit.form.id}`,
          fd,
          { headers: { "Content-Type": "multipart/form-data" } }
        );
        this.toast(res.data?.message || "Updated", "success");
        this.closeEditModal();
        await this.getRooms(this.currentPage);
      } catch (e) {
        const data = e?.response?.data;
        if (e?.response?.status === 422 && data?.errors) {
          Object.keys(data.errors).forEach((k) => {
            this.errors[k] = Array.isArray(data.errors[k]) ? data.errors[k][0] : data.errors[k];
          });
          this.toast(data?.message || "Validation error", "error");
        } else {
          this.toast(data?.message || "Update failed", "error");
        }
      } finally {
        this.savingEdit = false;
      }
    },

    addEditSeat() {
      if (!this.edit.form.seats) {
        this.edit.form.seats = [];
      }
      this.edit.form.seats.push({
        seat_no: "Seat-" + String.fromCharCode(65 + this.edit.form.seats.length),
        price: this.edit.form.price || "",
        advance_price: 0
      });
    },

    removeEditSeat(index) {
      if (this.edit.form.seats && this.edit.form.seats.length > 1) {
        this.edit.form.seats.splice(index, 1);
      }
    },

    openDeleteModal(r) {
      this.del.item = r;
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
        const res = await axios.delete(`${this.url}room-delete/${this.del.item.id}`);
        this.toast(res.data?.message || "Room deleted", "success");
        this.closeDelete();
        const willBeEmpty = this.rooms.length === 1 && this.currentPage > 1;
        await this.getRooms(willBeEmpty ? this.currentPage - 1 : this.currentPage);
      } catch (e) {
        this.toast(e?.response?.data?.message || "Delete failed", "error");
      } finally {
        this.savingDelete = false;
      }
    },
  },
};
</script>

<style scoped>
.img-thumb {
  width: 90px; height: 60px;
  object-fit: cover; border-radius: 10px;
  border: 1px solid #e5e7eb; background: #fff;
}


/*  Edit image grid */
.edit-image-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  padding: 8px;
  background: #f8f9fb;
  border: 1px solid #dee2e6;
  border-radius: 10px;
  min-height: 90px;
}

.edit-img-item {
  width: 80px; height: 72px;
  border-radius: 8px; overflow: hidden;
  border: 1px solid #dee2e6;
  position: relative; background: #f0f0f0;
  flex-shrink: 0;
}

.edit-img-preview {
  width: 100%; height: 100%;
  object-fit: cover; display: block;
}

.remove-btn {
  position: absolute; top: 2px; right: 2px;
  width: 20px; height: 20px;
  background: rgba(220,53,69,.85);
  border: none; border-radius: 50%;
  color: #fff; font-size: 9px;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; padding: 0;
}
.remove-btn:hover { background: #dc3545; }

.main-badge {
  position: absolute; bottom: 2px; left: 2px;
  background: rgba(13,110,253,.85);
  color: #fff; font-size: 8px;
  padding: 1px 5px; border-radius: 4px; font-weight: 600;
}

.add-more-box {
  border: 2px dashed #c0c9d4 !important;
  background: #f8f9fb; cursor: pointer;
}
.add-more-box:hover { border-color: #696cff !important; background: #f0f0ff; }
.add-more-label { width: 100%; height: 100%; cursor: pointer; }

.xmask {
  position: fixed; inset: 0; z-index: 20000;
  background: rgba(0,0,0,.55);
  display: flex; align-items: center; justify-content: center;
  padding: 16px; overflow-y: auto;
}
.xwrap { width: 100%; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
.xbox {
  width: min(94vw, 980px); background: #fff;
  border-radius: 16px; box-shadow: 0 18px 55px rgba(0,0,0,.25);
  border: 1px solid rgba(0,0,0,.06);
  overflow: hidden; display: flex; flex-direction: column;
}
.xbox.xsmall { width: min(94vw, 520px); }
.xhead { padding: 14px 16px; border-bottom: 1px solid #eef2f7; background: #fff; }
.xbody { padding: 16px; max-height: calc(100vh - 190px); overflow: auto; }
.xfoot { padding: 12px 16px; border-top: 1px solid #eef2f7; background: #fff; }

.slide-fade-enter-active,
.slide-fade-leave-active { transition: all .18s ease; }
.slide-fade-enter-from,
.slide-fade-leave-to { opacity: 0; transform: translateY(10px) scale(.98); }

.form-control, .form-select {
  border-radius: 8px; padding: 0.6rem 0.75rem;
  border: 1px solid #dce0e4;
}
.form-control:focus, .form-select:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.25rem rgba(13,110,253,.1);
}

.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #ddd; border-radius: 10px; }
.cursor-pointer { cursor: pointer; }

.professional-modal {
  position: relative;
  max-width: 460px;
  width: 92%;
  border-radius: 18px;
  background: #fff;
  padding: 34px 28px 24px;
  box-shadow: 0 18px 55px rgba(0,0,0,0.28);
  border: 1px solid rgba(0,0,0,0.06);
}

.modal-top-icon {
  width: 58px;
  height: 58px;
  border-radius: 50%;
  background: #e9f9f0;
  color: #16a34a;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 26px;
  margin: 0 auto 16px;
}

.modal-close-btn {
  position: absolute;
  top: 14px;
  right: 14px;
  width: 38px;
  height: 38px;
  border: none;
  border-radius: 10px;
  background: #f1f3f5;
  color: #333;
  cursor: pointer;
}

.modal-content-area {
  text-align: center;
}

.modal-title {
  font-size: 22px;
  font-weight: 700;
  color: #111827;
  margin-bottom: 10px;
}

.modal-text {
  font-size: 15px;
  color: #4b5563;
  margin-bottom: 12px;
  line-height: 1.6;
}

.modal-text strong {
  color: #16a34a;
}

.modal-note {
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  color: #166534;
  padding: 10px 12px;
  border-radius: 12px;
  font-size: 13px;
}

.modal-actions {
  display: flex;
  justify-content: center;
  gap: 12px;
  margin-top: 24px;
}

.btn-cancel,
.btn-confirm {
  border: none;
  padding: 11px 20px;
  border-radius: 10px;
  font-weight: 700;
  cursor: pointer;
}

.btn-cancel {
  background: #f3f4f6;
  color: #374151;
}

.btn-confirm {
  background: #16a34a;
  color: #fff;
}

.btn-confirm:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

@media (max-width: 576px) {
  .professional-modal {
    width: 94%;
    padding: 30px 18px 20px;
  }

  .modal-actions {
    flex-direction: column;
  }

  .btn-cancel,
  .btn-confirm {
    width: 100%;
  }
}


/* 🔥 Received Room (status = 1) */
.room-received {
  background: #fff7f7; /* very soft red */
  border-left: 4px solid #dc2626; /* clean red line */
}

/* hover smooth */
.room-received:hover {
  background: #ffecec;
}

/* text normal রাখো */
.room-received td {
  color: #374151;
}

/* 🔥 badge style */
.received-badge {
  background: #fee2e2;
  color: #dc2626;
  padding: 4px 10px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  display: inline-block;
}
</style>