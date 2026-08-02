<template>
  <Teleport to="body">
    <transition name="slide-fade">
      <div v-if="show" class="xmask" @click.self="emitClose">
        <div class="xwrap">
          <div class="xbox shadow-lg border-0" role="dialog" aria-modal="true">
            <div class="xhead d-flex justify-content-between align-items-center bg-light px-4 py-3 border-bottom">
              <div>
                <h5 class="mb-0 fw-bold text-dark">Create New Room</h5>
              </div>
              <button type="button" class="btn btn-sm btn-light border" @click="emitClose">
                <i class="fa fa-times"></i>
              </button>
            </div>

            <form @submit.prevent="submit">
              <div class="xbody p-4 custom-scrollbar">
                <div class="row g-4">

                  <div class="col-12 col-lg-6">

                    <div class="mb-3">
                      <label class="form-label fw-semibold">Floor Select <code class="req">*</code></label>
                      <select class="form-select border-2-focus" v-model.number="form.floor_id" required>
                        <option value="">Select Floor</option>
                        <option v-for="f in floors" :key="f.id" :value="f.id">{{ f.name }}</option>
                      </select>
                      <small v-if="errors.floor_id" class="text-danger small">{{ errors.floor_id }}</small>
                    </div>

                    <div class="mb-3">
                      <label class="form-label fw-semibold">Room Select <code class="req">*</code></label>
                      <select class="form-select border-2-focus" v-model.number="form.room_id" :disabled="!form.floor_id" required>
                        <option value="">Select Room</option>
                        <option v-for="r in rooms" :key="r.id" :value="r.id">Room {{ r.room_no }}</option>
                      </select>
                      <small class="text-muted small mt-1 d-block" v-if="form.floor_id && rooms.length === 0">
                        <i class="fa fa-info-circle me-1"></i> No available rooms for this floor.
                      </small>
                    </div>

                    <div class="mb-3">
                      <label class="form-label fw-semibold">Price <code class="req">*</code></label>
                      <div class="input-group">
                        <span class="input-group-text">৳</span>
                        <input type="number" class="form-control border-2-focus" v-model.number="form.price" min="0" required />
                      </div>
                    </div>

                    <div class="mb-3">
                      <label class="form-label fw-semibold">Windows <code class="req">*</code></label>
                      <select class="form-select border-2-focus" v-model="form.windows" required>
                        <option value="">Select</option>
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                      </select>
                    </div>
                  </div>

                  <div class="col-12 col-lg-6">
                    <div class="mb-3">
                      <label class="form-label fw-semibold">
                        Room Images <code class="req">*</code>
                        <span class="text-muted fw-normal small ms-1">(Multiple select)</span>
                      </label>

                      <!-- Upload Area -->
                      <div class="image-upload-wrapper border rounded shadow-sm position-relative"
                           :class="{ 'has-images': previews.length > 0 }">

                        <!-- No image selected -->
                        <template v-if="previews.length === 0">
                          <input type="file" id="roomUpload" class="d-none" accept="image/*" multiple @change="onImageChange" />
                          <label for="roomUpload" class="upload-placeholder-content w-100 d-flex flex-column align-items-center justify-content-center cursor-pointer">
                            <div class="icon-circle mb-2">
                              <i class="fa fa-cloud-upload-alt text-primary fs-4"></i>
                            </div>
                            <span class="fw-bold text-dark">Upload Photos</span>
                            <small class="text-muted">Click to browse — multiple images allowed</small>
                          </label>
                        </template>

                        <!-- Images selected - show grid -->
                        <template v-else>
                          <div class="preview-grid p-2">
                            <div
                              v-for="(src, i) in previews"
                              :key="i"
                              class="preview-item position-relative"
                            >
                              <img :src="src" class="preview-img" />
                              <button
                                type="button"
                                class="remove-btn"
                                @click="removeImage(i)"
                                title="Remove"
                              >
                                <i class="fa fa-times"></i>
                              </button>
                              <span v-if="i === 0" class="main-badge">Main</span>
                            </div>

                            <!-- Add more button -->
                            <div class="preview-item add-more-box">
                              <input type="file" :id="`addMore`" class="d-none" accept="image/*" multiple @change="onAddMore" />
                              <label :for="`addMore`" class="add-more-label cursor-pointer d-flex flex-column align-items-center justify-content-center h-100">
                                <i class="fa fa-plus text-primary fs-5"></i>
                                <small class="text-muted mt-1">Add More</small>
                              </label>
                            </div>
                          </div>

                          <div class="px-2 pb-2">
                            <small class="text-success fw-semibold">
                              <i class="fa fa-check-circle me-1"></i>{{ form.images.length }} image(s) selected
                            </small>
                          </div>
                        </template>
                      </div>

                      <small v-if="errors.images" class="text-danger small d-block mt-1">{{ errors.images }}</small>
                    </div>

                    <div class="mb-3">
                      <label class="form-label fw-semibold">Room Type <code class="req">*</code></label>
                      <select class="form-select border-2-focus" v-model="form.room_type" required>
                        <option value="">Select Room Type</option>
                        <option value="Seat A - Single Room">Seat A - Single Room</option>
                        <option value="Seat A - Double Room">Seat A - Double Room</option>
                        <option value="Seat B - Single Room">Seat B - Single Room</option>
                        <option value="Seat B - Double Room">Seat B - Double Room</option>
                      </select>
                    </div>
                  </div>
                  <!-- Seats Management Section -->
                  <div class="col-12 border-top pt-4 mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <h6 class="fw-bold text-dark mb-0">
                        <i class="fa fa-bed me-1 text-primary"></i> Seats Configuration (সিট সমূহের বিবরণ)
                      </h6>
                      <button type="button" class="btn btn-sm btn-primary" @click="addSeat" :disabled="form.room_type.includes('Single') || form.room_type === 'Singel'">
                        <i class="fa fa-plus me-1"></i> Add Seat
                      </button>
                    </div>

                    <div class="table-responsive">
                      <table class="table table-bordered table-sm align-middle text-center">
                        <thead class="table-light">
                          <tr>
                            <th>Seat Name/No *</th>
                            <th>Monthly Rent (৳) *</th>
                            <th>Advance Deposit (৳)</th>
                            <th style="width: 50px;">Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="(seat, idx) in form.seats" :key="idx">
                            <td>
                              <input type="text" class="form-control form-control-sm text-center" v-model="seat.seat_no" placeholder="e.g. Seat A" required />
                            </td>
                            <td>
                              <input type="number" class="form-control form-control-sm text-center" v-model.number="seat.price" min="0" placeholder="Monthly rent" required />
                            </td>
                            <td>
                              <input type="number" class="form-control form-control-sm text-center" v-model.number="seat.advance_price" min="0" placeholder="Advance deposit" />
                            </td>
                            <td>
                              <button type="button" class="btn btn-sm btn-danger btn-xs py-1 px-2" @click="removeSeat(idx)" :disabled="form.seats.length <= 1">
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

              <div class="xfoot px-4 py-3 bg-light border-top d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-dark px-4" @click="emitClose">Cancel</button>
                <button type="submit" class="btn btn-success px-4 d-flex align-items-center" :disabled="saving">
                  <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
                  <span>{{ saving ? 'Saving...' : 'Submit Room' }}</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </transition>
  </Teleport>
</template>

<script>
import axios from "axios";
import Toastify from "toastify-js";
import "toastify-js/src/toastify.css";

export default {
  name: "RoomCreateModal",

  props: {
    show: { type: Boolean, default: false },
  },

  emits: ["close", "created"],

  data() {
    return {
      floors: [],
      rooms: [],
      form: this.emptyForm(),
      previews: [],   
      saving: false,
      errors: {},
    };
  },

  computed: {
    url() {
      return this.$store.state.url;
    },
  },

  watch: {
    show(v) {
      document.body.style.overflow = v ? "hidden" : "";
      if (v) {
        this.resetForm();
        this.loadFloors();
      }
    },

    "form.price"(newVal) {
      if (this.form.seats) {
        this.form.seats.forEach(seat => {
          seat.price = newVal;
        });
      }
    },

    "form.room_type"(newVal) {
      if (!newVal) return;
      let prefix = "Seat A";
      if (newVal.includes("Seat B")) {
        prefix = "Seat B";
      }
      if (newVal.includes("Single") || newVal === "Singel") {
        this.form.seats = [
          { seat_no: prefix, price: this.form.price || "", advance_price: 0 }
        ];
      } else if (newVal.includes("Double") || newVal === "Doubel") {
        this.form.seats = [
          { seat_no: `${prefix}-1`, price: this.form.price || "", advance_price: 0 },
          { seat_no: `${prefix}-2`, price: this.form.price || "", advance_price: 0 }
        ];
      }
    },

    "form.floor_id"(v) {
      this.form.room_id = "";
      this.rooms = [];
      if (v) this.loadRoomsByFloor(v);
    },
  },

  beforeUnmount() {
    document.body.style.overflow = "";
    this.clearPreviews();
  },

  methods: {
    emptyForm() {
      return {
        floor_id: "",
        room_id: "",
        price: "",
        max_people: "",
        room_type: "",
        windows: "",
        images: [],
        seats: [
          { seat_no: "Seat A", price: "", advance_price: 0 }
        ],
      };
    },

    clearPreviews() {
      this.previews.forEach(url => URL.revokeObjectURL(url));
      this.previews = [];
    },

    resetForm() {
      this.errors = {};
      this.rooms = [];
      this.clearPreviews();
      this.form = this.emptyForm();
    },

    toast(text, type = "success") {
      const bg =
        type === "success"
          ? "linear-gradient(to right, #00b09b, #96c93d)"
          : type === "warning"
          ? "linear-gradient(to right, #f59e0b, #fbbf24)"
          : "linear-gradient(to right, #ff5f6d, #ffc371)";

      Toastify({
        text: text || (type === "success" ? "Done ✅" : "Something went wrong ❌"),
        duration: 2500,
        close: true,
        gravity: "top",
        position: "right",
        backgroundColor: bg,
      }).showToast();
    },

    emitClose() {
      this.$emit("close");
    },

    async loadFloors() {
      try {
        const res = await axios.get(this.url + "floors/all");
        this.floors = res.data.data || [];
      } catch (e) {
        this.toast("Failed to load floors", "error");
      }
    },

    async loadRoomsByFloor(floorId) {
      try {
        const res = await axios.get(this.url + `rooms/by-floor/${floorId}`);
        this.rooms = res.data.data || [];
      } catch (e) {
        this.toast("Failed to load rooms", "error");
      }
    },

    // ✅ প্রথমবার image select
    onImageChange(e) {
      const files = Array.from(e.target?.files || []);
      if (!files.length) return;

      this.clearPreviews();
      this.form.images = files;
      this.previews = files.map(f => URL.createObjectURL(f));
      this.errors.images = null;

      // input reset করো যাতে same file আবার select করা যায়
      e.target.value = "";
    },

    // ✅ আরও image add করা
    onAddMore(e) {
      const files = Array.from(e.target?.files || []);
      if (!files.length) return;

      this.form.images = [...this.form.images, ...files];
      const newPreviews = files.map(f => URL.createObjectURL(f));
      this.previews = [...this.previews, ...newPreviews];
      this.errors.images = null;

      e.target.value = "";
    },

    // ✅ নির্দিষ্ট image remove করা
    removeImage(index) {
      URL.revokeObjectURL(this.previews[index]);
      this.form.images.splice(index, 1);
      this.previews.splice(index, 1);
    },

    validate() {
      this.errors = {};
      if (!this.form.floor_id) this.errors.floor_id = "Floor is required";
      if (!this.form.room_id) this.errors.room_id = "Room is required";
      if (this.form.price === "" || this.form.price === null) this.errors.price = "Price is required";
      if (!this.form.room_type) this.errors.room_type = "Room type is required";
      if (!this.form.windows) this.errors.windows = "Windows is required";
      if (!this.form.images.length) this.errors.images = "At least one image is required"; // ✅
      if (!this.form.seats || !this.form.seats.length) {
        this.errors.seats = "At least one seat is required";
      } else {
        this.form.seats.forEach((seat, idx) => {
          if (!seat.seat_no) this.errors[`seat_no_${idx}`] = "Seat name is required";
          if (seat.price === "" || seat.price === null) this.errors[`seat_price_${idx}`] = "Seat price is required";
        });
      }
      return Object.keys(this.errors).length === 0;
    },

    async submit() {
      if (!this.validate()) {
        this.toast("Please check required fields", "warning");
        return;
      }

      this.saving = true;
      try {
        const fd = new FormData();
        fd.append("floor_id", this.form.floor_id);
        fd.append("room_id", this.form.room_id);
        fd.append("price", this.form.price);
        fd.append("max_people", this.form.max_people || "");
        fd.append("room_type", this.form.room_type);
        fd.append("windows", this.form.windows);
        fd.append("seats", JSON.stringify(this.form.seats));

        // ✅ Multiple images
        this.form.images.forEach((img) => {
          fd.append("images[]", img);
        });

        const res = await axios.post(
          this.url + "room-store",
          fd,
          { headers: { "Content-Type": "multipart/form-data" } }
        );

        this.toast(res.data?.message || "Saved ✅", "success");
        this.$emit("created");
        this.emitClose();
      } catch (e) {
        const data = e?.response?.data;
        if (e?.response?.status === 422 && data?.errors) {
          Object.keys(data.errors).forEach((k) => {
            this.errors[k] = Array.isArray(data.errors[k]) ? data.errors[k][0] : data.errors[k];
          });
          this.toast(data?.message || "Validation error", "error");
        } else {
          this.toast(data?.message || "Save failed", "error");
        }
      } finally {
        this.saving = false;
      }
    },

    addSeat() {
      this.form.seats.push({
        seat_no: "Seat-" + String.fromCharCode(65 + this.form.seats.length),
        price: this.form.price || "",
        advance_price: 0
      });
    },

    removeSeat(index) {
      if (this.form.seats.length > 1) {
        this.form.seats.splice(index, 1);
      }
    },
  },
};
</script>

<style scoped>
.xbox {
  width: min(96vw, 880px);
  background: #fff;
  border-radius: 16px;
  display: flex;
  flex-direction: column;
}

.custom-scrollbar {
  max-height: calc(100vh - 200px);
  overflow-y: auto;
}
.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e0e0e0;
  border-radius: 10px;
}

/* ✅ Upload wrapper */
.image-upload-wrapper {
  background: #f8f9fb;
  min-height: 180px;
  display: flex;
  flex-direction: column;
  border: 2px dashed #dce0e4 !important;
  transition: all 0.3s ease;
  border-radius: 10px;
  overflow: hidden;
}

.image-upload-wrapper:hover {
  background: #f1f4f9;
  border-color: #696cff !important;
}

.image-upload-wrapper.has-images {
  border-style: solid !important;
  border-color: #c3e6cb !important;
  background: #f8fff9;
}

/* ✅ Preview Grid */
.preview-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.preview-item {
  width: 80px;
  height: 72px;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #dee2e6;
  position: relative;
  background: #f0f0f0;
}

.preview-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.remove-btn {
  position: absolute;
  top: 2px;
  right: 2px;
  width: 20px;
  height: 20px;
  background: rgba(220, 53, 69, 0.85);
  border: none;
  border-radius: 50%;
  color: #fff;
  font-size: 9px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  padding: 0;
  line-height: 1;
}

.remove-btn:hover {
  background: #dc3545;
}

.main-badge {
  position: absolute;
  bottom: 2px;
  left: 2px;
  background: rgba(13, 110, 253, 0.85);
  color: #fff;
  font-size: 8px;
  padding: 1px 5px;
  border-radius: 4px;
  font-weight: 600;
}

/* ✅ Add More Box */
.add-more-box {
  border: 2px dashed #c0c9d4 !important;
  background: #f8f9fb;
  cursor: pointer;
}

.add-more-box:hover {
  border-color: #696cff !important;
  background: #f0f0ff;
}

.add-more-label {
  width: 100%;
  height: 100%;
  cursor: pointer;
}

/* ✅ Placeholder */
.upload-placeholder-content {
  height: 180px;
  cursor: pointer;
}

.icon-circle {
  width: 50px;
  height: 50px;
  background: #e7e7ff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.2s ease;
}

.upload-placeholder-content:hover .icon-circle {
  transform: translateY(-5px);
}

.form-control, .form-select {
  border-radius: 8px;
  border: 1px solid #dce0e4;
  padding: 0.6rem 0.75rem;
}

.border-2-focus:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.08);
}

.border-dashed { border-style: dashed !important; }
.bg-light-subtle { background-color: #f8f9fa !important; }

.xmask {
  position: fixed; inset: 0; z-index: 30000;
  background: rgba(0,0,0,.55);
  display: flex; align-items: center; justify-content: center; padding: 16px;
}

.xwrap {
  width: 100%;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
}

.req { color: #dc3545; }
.cursor-pointer { cursor: pointer; }

.slide-fade-enter-active,
.slide-fade-leave-active { transition: all .2s ease; }
.slide-fade-enter-from,
.slide-fade-leave-to { opacity: 0; transform: translateY(15px); }
</style>