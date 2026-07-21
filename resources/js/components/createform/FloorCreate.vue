<template>
  <Teleport to="body">
    <transition name="slide-fade">
      <div v-if="show" class="xmask" @click.self="emitClose">
        <div class="xwrap">
          <div class="xbox" role="dialog" aria-modal="true">
            <div class="xhead d-flex justify-content-between align-items-center">
              <h5 class="mb-0">Create Floor</h5>
              <button type="button" class="btn btn-sm btn-light" @click="emitClose">
                <i class="fa fa-times"></i>
              </button>
            </div>
            <form @submit.prevent="createFloor">
              <div class="xbody">
              
                <div class="row g-3">
                  <!-- Image -->
                <div class="col-12 col-md-6">
                  <label class="form-label">Image <code class="req">*</code></label>
                  <input type="file" class="form-control" accept="image/*" @change="onImageChange" required />
                  <small v-if="errors.image" class="text-danger d-block mt-1">{{ errors.image }}</small>

                  <div class="mt-2" v-if="preview">
                    <div class="preview-box">
                      <img :src="preview" alt="preview" />
                    </div>
                    <small class="text-muted d-block mt-1">Preview</small>
                  </div>
                </div>

                  <!-- Name -->
                  <div class="col-12 col-md-6">
                    <label class="form-label">Floor Name <code class="req">*</code></label>
                    <input
                      type="text"
                      v-model.trim="form.name"
                      class="form-control"
                      placeholder="e.g. 2nd Floor"
                      required
                    />
                    <small v-if="errors.name" class="text-danger d-block mt-1">{{ errors.name }}</small>
                    <small class="text-muted">Example: Ground Floor, 1st Floor, VIP Floor</small>
                  </div>

                  <!-- Rooms -->
                  <div class="col-12">
                    <label class="form-label">Add Room <code class="req">*</code></label>

                      <div class="d-flex flex-wrap gap-2 align-items-center">
                        <input
                          type="text"
                          v-model="roomInput"
                          class="form-control"
                          placeholder="e.g. 202"
                          style="max-width:220px;"
                          @keyup.enter="addRoom"
                        />
                        <button type="button" class="btn btn-primary" @click="addRoom">
                          <i class="ti ti-plus me-1"></i> Add
                        </button>
                        <button v-if="roomList.length" type="button" class="btn btn-outline-danger" @click="clearRooms">
                          Clear
                        </button>
                      </div>
                    <small v-if="errors.rooms" class="text-danger d-block mt-1">{{ errors.rooms }}</small>

                    <div class="text-warning small mt-2" v-if="duplicateMsg">
                      <i class="ti ti-alert-triangle me-1"></i> {{ duplicateMsg }}
                    </div>

                    <div class="rooms-box mt-3">
                      <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                        <label class="form-label mb-0">Rooms</label>
                        <span class="badge bg-label-primary" v-if="roomList.length">Total: {{ roomList.length }}</span>
                      </div>

                      <div class="row g-2">
                        <div class="col-6 col-md-3 col-lg-2" v-for="n in roomList" :key="n">
                          <div class="room-chip">
                            <span>Room {{ n }}</span>
                            <button type="button" class="btn btn-sm btn-danger" @click="removeRoom(n)">×</button>
                          </div>
                        </div>

                        <div class="col-12" v-if="roomList.length === 0">
                          <div class="empty-box">
                            <i class="ti ti-home-question me-1"></i>
                            No rooms added yet. Add room numbers above.
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                </div>
              </div>

              <div class="xfoot d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary" @click="emitClose">Cancel</button>

                <button type="submit" class="btn btn-success" :disabled="saving">
                  <span v-if="saving"><i class="fa fa-spinner fa-spin me-1"></i> Saving...</span>
                  <span v-else><i class="ti ti-device-floppy me-1"></i> Submit</span>
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
  name: "FloorCreateModal",

  props: {
    show: { type: Boolean, default: false },
  },
  emits: ["close", "created"],

  data() {
    return {
      form: { name: "", image: null },
      preview: "",
      roomInput: null,
      roomList: [],
      duplicateMsg: "",
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
      if (v) this.resetForm();
      document.body.style.overflow = v ? "hidden" : "";
    },
  },

  methods: {
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

    emitClose() {
      this.$emit("close");
    },

    resetForm() {
      this.errors = {};
      this.form = { name: "", image: null };
      this.roomInput = null;
      this.roomList = [];
      this.duplicateMsg = "";
      if (this.preview) URL.revokeObjectURL(this.preview);
      this.preview = "";
    },

    onImageChange(e) {
      const file = e.target?.files?.[0] || null;
      this.form.image = file;
      this.errors.image = null;
      if (this.preview) URL.revokeObjectURL(this.preview);
      this.preview = file ? URL.createObjectURL(file) : "";
    },

    addRoom() {
      const val = this.roomInput?.trim();
      if (!val || val < 1) return;

      if (this.roomList.includes(val)) {
        this.duplicateMsg = `Room ${val} already added`;
        this.roomInput = "";
        setTimeout(() => (this.duplicateMsg = ""), 1200);
        return;
      }

      this.roomList.push(val);
      this.roomList.sort((a, b) => a - b);
      this.roomInput = "";
      this.errors.rooms = null;
      this.duplicateMsg = "";
    },

    removeRoom(n) {
      this.roomList = this.roomList.filter((x) => x !== n);
    },

    clearRooms() {
      this.roomList = [];
      this.roomInput = null;
      this.duplicateMsg = "";
      this.errors.rooms = null;
    },

    validate() {
      this.errors = {};
      if (!this.form.name?.trim()) this.errors.name = "Floor name is required";
      if (!this.form.image) this.errors.image = "Image is required";
      if (!this.roomList.length) this.errors.rooms = "Please add at least 1 room";
      return Object.keys(this.errors).length === 0;
    },

    async createFloor() {
      if (!this.validate()) {
        this.toast("Please check required fields", "warning");
        return;
      }

      this.saving = true;
      try {
        const fd = new FormData();
        fd.append("name", this.form.name);
        fd.append("image", this.form.image);
        fd.append("rooms", JSON.stringify(this.roomList));

        const res = await axios.post(this.url + "floors-create", fd, {
          headers: { "Content-Type": "multipart/form-data" },
        });

        this.toast(res.data?.message || "Created", "success");
        this.$emit("created");
        this.emitClose();
      } catch (e) {
        const data = e?.response?.data;
        if (e?.response?.status === 422 && data?.errors) {
          Object.keys(data.errors).forEach((k) => {
            this.errors[k] = Array.isArray(data.errors[k])
              ? data.errors[k][0]
              : data.errors[k];
          });
          this.toast("Validation error. Please check fields.", "error");
        } else {
          this.toast(data?.message || "Create failed", "error");
        }
      } finally {
        this.saving = false;
      }
    },
  },
};
</script>

<style scoped>
/*  modal responsive like screenshot */
.xmask{
  position: fixed;
  inset: 0;
  z-index: 30000;
  background: rgba(0,0,0,.55);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  overflow-y: auto;
}

.xwrap{ width: 100%; min-height: 100vh; display:flex; align-items:center; justify-content:center; }

.xbox{
  width: min(96vw, 860px);
  background: #fff;
  border-radius: 14px;
  box-shadow: 0 18px 55px rgba(0,0,0,.25);
  border: 1px solid rgba(0,0,0,.06);
  overflow: hidden;
  display:flex;
  flex-direction: column;
}

.xhead{
  padding: 12px 16px;
  border-bottom: 1px solid #eef2f7;
  background: #fff;
}

.xbody{
  padding: 16px;
  max-height: calc(100vh - 190px);
  overflow: auto;
}

.xfoot{
  padding: 12px 16px;
  border-top: 1px solid #eef2f7;
  background: #fff;
}

.req{ color: #dc3545; }

/* preview */
.preview-box{
  width: 100%;
  max-width: 260px;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  overflow: hidden;
  background: #fff;
}
.preview-box img{
  width: 100%;
  height: 150px;
  object-fit: cover;
  display: block;
}

/* rooms area */
.rooms-box{
  border: 1px dashed #e5e7eb;
  border-radius: 12px;
  padding: 12px;
  background: #fafafa;
}

.room-chip{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:8px;
  padding:8px 10px;
  border:1px solid #e5e7eb;
  border-radius:12px;
  background:#fff;
  box-shadow: 0 1px 8px rgba(16,24,40,.04);
}
.room-chip span{
  font-size: 13px;
  font-weight: 600;
  color:#111827;
}
.empty-box{
  padding:12px;
  border: 1px dashed #d1d5db;
  border-radius: 12px;
  background: #fff;
  color:#6b7280;
}

/* animation */
.slide-fade-enter-active,
.slide-fade-leave-active{ transition: all .18s ease; }
.slide-fade-enter-from,
.slide-fade-leave-to{
  opacity: 0;
  transform: translateY(10px) scale(.98);
}

@media (max-width: 576px){
  .xmask{ padding: 10px; }
  .xbox{ width: 100%; border-radius: 12px; }
  .xbody{ max-height: calc(100vh - 170px); }
}
</style>
