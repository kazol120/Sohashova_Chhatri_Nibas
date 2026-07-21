<template>
  <Teleport to="body">
    <transition name="slide-fade">
      <div v-if="show" class="xmask" @click.self="emitClose">
        <div class="xwrap">
          <div class="xbox shadow-lg" role="dialog" aria-modal="true">

            <!-- Header -->
            <div class="xhead d-flex justify-content-between align-items-center px-4 py-3 border-bottom">
              <div class="d-flex align-items-center gap-2">
                <div class="head-icon">
                  <i class="fa fa-user-plus"></i>
                </div>
                <div>
                  <h5 class="mb-0 fw-bold text-dark">Create New Staff</h5>
                </div>
              </div>
              <button type="button" class="btn-close-custom" @click="emitClose">
                <i class="fa fa-times"></i>
              </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit">
              <div class="xbody custom-scrollbar p-4">
                <div class="row g-4">

                  <!-- LEFT COLUMN -->
                  <div class="col-12 col-lg-6">
                    <div class="section-card">
                      <div class="section-card-title">
                        <i class="fa fa-id-card me-2"></i> Personal Info
                      </div>

                      <div class="row g-3 mb-3">
                        <div class="col-5">
                        <label class="form-label fw-semibold">Employee ID <span class="req">*</span></label>
                        <input type="text" class="form-control" :class="{'is-invalid': errors.employee_id}"
                          v-model="form.employee_id" placeholder="e.g. EMP-001" required>
                        <div class="invalid-feedback">{{ errors.employee_id }}</div>
                      </div>

                      <div class="col-7">
                        <label class="form-label fw-semibold">Full Name <span class="req">*</span></label>
                        <input type="text" class="form-control" :class="{'is-invalid': errors.name}"
                          v-model="form.name" placeholder="Full name" required>
                        <div class="invalid-feedback">{{ errors.name }}</div>
                      </div>
                      </div>

                      <div class="row g-3 mb-3">
                        <div class="col-6">
                          <label class="form-label fw-semibold">Phone <span class="req">*</span></label>
                          <input type="text" class="form-control" :class="{'is-invalid': errors.phone}"
                            v-model="form.phone" placeholder="+880..." required>
                          <div class="invalid-feedback">{{ errors.phone }}</div>
                        </div>
                        <div class="col-6">
                          <label class="form-label fw-semibold">Gender</label>
                          <select class="form-select" v-model="form.gender">
                            <option value="">Select</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                          </select>
                        </div>
                      </div>
                     


                      <div class="mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" class="form-control" :class="{'is-invalid': errors.email}"
                          v-model="form.email" placeholder="email@example.com">
                        <div class="invalid-feedback">{{ errors.email }}</div>
                      </div>

                       <div class="mb-3">
                        <label class="form-label fw-semibold">Password <span class="req">*</span></label>
                          <input type="text" class="form-control" :class="{'is-invalid': errors.password}"
                            v-model="form.password" placeholder="password" required>
                          <div class="invalid-feedback">{{ errors.password }}</div>
                      </div>
                      

                      <div class="row g-3 mb-3">
                        <div class="col-6">
                          <label class="form-label fw-semibold">Date of Birth</label>
                          <input type="date" class="form-control" v-model="form.date_of_birth">
                        </div>
                        <div class="col-6">
                          <label class="form-label fw-semibold">NID / Passport</label>
                          <input type="text" class="form-control" v-model="form.nid_passport" placeholder="NID no.">
                        </div>
                      </div>

                      <!-- Photo Upload -->
                      <div class="mb-0">
                        <label class="form-label fw-semibold">Staff Photo</label>
                        <div class="image-upload-wrapper border rounded position-relative"
                          :class="{'has-image': preview}">
                          <template v-if="preview">
                            <img :src="preview" class="preview-img" />
                            <div class="upload-overlay">
                              <input type="file" id="staffPhoto" class="d-none"
                                accept="image/*" @change="onImageChange" />
                              <label for="staffPhoto" class="btn btn-sm btn-light shadow-sm">
                                <i class="fa fa-camera me-1"></i> Change
                              </label>
                            </div>
                          </template>
                          <template v-else>
                            <input type="file" id="staffPhoto" class="d-none"
                              accept="image/*" @change="onImageChange" />
                            <label for="staffPhoto" class="upload-placeholder w-100 d-flex flex-column align-items-center justify-content-center">
                              <div class="upload-icon-wrap mb-2">
                                <i class="fa fa-cloud-upload-alt"></i>
                              </div>
                              <span class="fw-semibold text-dark">Click to Upload Photo</span>
                              <small class="text-muted">JPG, PNG, WEBP — max 5MB</small>
                            </label>
                          </template>
                        </div>
                      </div>

                    </div>
                  </div>

                  <!-- RIGHT COLUMN -->
                  <div class="col-12 col-lg-6">
                    <div class="section-card">
                      <div class="section-card-title">
                        <i class="fa fa-map-marker-alt me-2"></i> Address & Job Info
                      </div>

                      <div class="mb-3">
                        <label class="form-label fw-semibold">Division</label>
                        <select v-model="form.division_id" @change="loadDistricts" class="form-select">
                          <option value="">Select Division</option>
                          <option v-for="d in divisions" :key="d.id" :value="d.id">{{ d.name }}</option>
                        </select>
                      </div>

                      <div class="mb-3">
                        <label class="form-label fw-semibold">District</label>
                        <select v-model="form.district_id" @change="loadThanas" class="form-select"
                          :disabled="!form.division_id">
                          <option value="">Select District</option>
                          <option v-for="d in districts" :key="d.id" :value="d.id">{{ d.name }}</option>
                        </select>
                      </div>

                      <div class="mb-3">
                        <label class="form-label fw-semibold">Thana</label>
                        <select v-model="form.thana_id" class="form-select"
                          :disabled="!form.district_id">
                          <option value="">Select Thana</option>
                          <option v-for="t in thanas" :key="t.id" :value="t.id">{{ t.name }}</option>
                        </select>
                      </div>

                      <div class="mb-3">
                        <label class="form-label fw-semibold">Permanent Address</label>
                        <textarea class="form-control" v-model="form.permanent_address"
                          rows="2" placeholder="Full address..."></textarea>
                      </div>

                      <div class="row g-3 mb-3">
                        <div class="col-6">
                          <label class="form-label fw-semibold">Designation</label>
                          <select class="form-select" v-model="form.designation">
                            <option value="">Select</option>
                            <option value="Manager">Manager</option>
                            <option value="Receptionist">Receptionist</option>
                            <option value="Cleaner">Cleaner</option>
                            <option value="Chef">Chef</option>
                          </select>
                        </div>
                        <div class="col-6">
                          <label class="form-label fw-semibold">Department</label>
                          <select class="form-select" v-model="form.department">
                            <option value="">Select</option>
                            <option value="Front Desk">Front Desk</option>
                            <option value="Housekeeping">Housekeeping</option>
                            <option value="Security">Security</option>
                            <option value="Kitchen">Kitchen</option>
                          </select>
                        </div>
                      </div>

                      <div class="row g-3 mb-3">
                        <div class="col-6">
                          <label class="form-label fw-semibold">Salary (BDT)</label>
                          <input type="number" class="form-control" v-model="form.salary" placeholder="0.00">
                        </div>
                        <div class="col-6">
                          <label class="form-label fw-semibold">Shift Time</label>
                          <select class="form-select" v-model="form.shift_time">
                            <option value="">Select</option>
                            <option value="Morning">Morning</option>
                            <option value="Evening">Evening</option>
                            <option value="Night">Night</option>
                          </select>
                        </div>
                      </div>
                      <div class="mb-3">
                        <label class="form-label fw-semibold">Joining Date</label>
                        <input type="date" class="form-control" v-model="form.joining_date">
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Footer -->
              <div class="xfoot px-4 py-3 border-top d-flex justify-content-between align-items-center">
                <small class="text-muted"><span class="req">*</span> Required fields</small>
                <div class="d-flex gap-2">
                  <button type="button" class="btn btn-outline-secondary px-4" @click="emitClose">
                    Cancel
                  </button>
                  <button type="submit" class="btn btn-submit px-4 d-flex align-items-center gap-2"
                    :disabled="saving">
                    <span v-if="saving" class="spinner-border spinner-border-sm"></span>
                    <i v-else class="fa fa-save"></i>
                    {{ saving ? 'Saving...' : 'Save Staff' }}
                  </button>
                </div>
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
  name: "StaffCreateModal",
  props: {
    show: { type: Boolean, default: false },
  },
  emits: ["close", "created"],
  computed: {
    url() {
      return this.$store.state.url;
    },
  },
  data() {
    return {
      divisions: [],
      districts: [],
      thanas:    [],
      preview:   "",
      saving:    false,
      errors:    {},
      form:      this.emptyForm(),
    };
  },
  watch: {
    show(v) {
      document.body.style.overflow = v ? "hidden" : "";
      if (v) {
        this.resetForm();
        this.loadDivisions();
      }
    },
  },
  beforeUnmount() {
    document.body.style.overflow = "";
    if (this.preview) URL.revokeObjectURL(this.preview);
  },
  methods: {
    emptyForm() {
      return {
        employee_id:"",
        name:"",
        phone:"",
        email:"",
        gender:"",
        date_of_birth:"",
        nid_passport:"",
        division_id:"",
        district_id:"",
        thana_id:"",
        permanent_address:"",
        designation:"",
        department:"",
        salary:"",
        joining_date:"",
        shift_time:"",
        password:"",
        photo:null,
      };
    },
    resetForm() {
      this.errors    = {};
      this.districts = [];
      this.thanas    = [];
      this.form      = this.emptyForm();
      if (this.preview) URL.revokeObjectURL(this.preview);
      this.preview = "";
    },
    toast(text, type = "success") {
      const bg = {
        success: "linear-gradient(to right,#00b09b,#96c93d)",
        warning: "linear-gradient(to right,#f59e0b,#fbbf24)",
        error:   "linear-gradient(to right,#ff5f6d,#ffc371)",
      }[type] || "";
      Toastify({ text, duration: 3000, close: true, gravity: "top",
        position: "right", backgroundColor: bg }).showToast();
    },
    emitClose() { this.$emit("close"); },
    async loadDivisions() {
      try {
        const res = await axios.get(this.url + "divisions");
        this.divisions = res.data || [];
      } catch (e) { console.error(e); }
    },

    async loadDistricts() {
      this.districts        = [];
      this.thanas           = [];
      this.form.district_id = "";
      this.form.thana_id    = "";
      if (!this.form.division_id) return;
      try {
        const res = await axios.get(this.url + `divisions/${this.form.division_id}/districts`);
        this.districts = res.data || [];
      } catch (e) { console.error(e); }
    },

    async loadThanas() {
      this.thanas        = [];
      this.form.thana_id = "";
      if (!this.form.district_id) return;
      try {
        const res = await axios.get(this.url + `districts/${this.form.district_id}/thanas`);
        this.thanas = res.data || [];
      } catch (e) { console.error(e); }
    },

    onImageChange(e) {
      const file = e.target?.files?.[0] || null;
      this.form.photo = file;
      if (this.preview) URL.revokeObjectURL(this.preview);
      this.preview = file ? URL.createObjectURL(file) : "";
    },

    validate() {
      this.errors = {};
      if (!this.form.employee_id) this.errors.employee_id = "Employee ID is required";
      if (!this.form.name)        this.errors.name        = "Name is required";
      if (!this.form.phone)       this.errors.phone       = "Phone is required";
      if (!this.form.password)       this.errors.password  = "password is required";
      if (this.form.email && !/\S+@\S+\.\S+/.test(this.form.email))
        this.errors.email = "Invalid email format";
      return Object.keys(this.errors).length === 0;
    },

    async submit() {
      if (!this.validate()) {
        this.toast("Please fix validation errors", "warning");
        return;
      }
      this.saving = true;
      try {
        const fd = new FormData();
        Object.entries(this.form).forEach(([k, v]) => {
          if (v !== null && v !== "") fd.append(k, v);
        });

        const res = await axios.post(this.url + "staffs/store", fd, {
          headers: { "Content-Type": "multipart/form-data" },
        });

        this.toast(res.data?.message || "Staff saved ✅", "success");
        this.$emit("created");
        this.emitClose();
      } catch (e) {
        const data = e?.response?.data;
        if (e?.response?.status === 422 && data?.errors) {
          Object.keys(data.errors).forEach(k => {
            this.errors[k] = Array.isArray(data.errors[k])
              ? data.errors[k][0] : data.errors[k];
          });
          this.toast(data?.message || "Validation error", "error");
        } else {
          this.toast(data?.message || "Save failed", "error");
        }
      } finally {
        this.saving = false;
      }
    },
  },
};
</script>

<style scoped>
.xmask {
  position: fixed; inset: 0; z-index: 30000;
  background: rgba(0,0,0,.6);
  display: flex; align-items: center; justify-content: center; padding: 16px;
}
.xwrap {
  width: 100%;
  display: flex; align-items: center; justify-content: center;
}
.xbox {
  width: min(96vw, 920px);
  background: #fff;
  border-radius: 16px;
  display: flex; flex-direction: column;
  overflow: hidden;
}
.xhead { background: #f8f9fa; }

.head-icon {
  width: 40px; height: 40px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: 16px;
}
.btn-close-custom {
  width: 34px; height: 34px;
  border: 1px solid #dee2e6; border-radius: 8px;
  background: #fff; color: #666; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: all 0.2s;
}
.btn-close-custom:hover { background: #dc3545; color: #fff; border-color: #dc3545; }

.custom-scrollbar { max-height: calc(100vh - 210px); overflow-y: auto; }
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #dee2e6; border-radius: 4px; }

.section-card { background: #f8f9fa; border-radius: 12px; padding: 20px; height: 100%; }
.section-card-title {
  font-size: 13px; font-weight: 700;
  text-transform: uppercase; letter-spacing: 1px;
  color: #6c757d; margin-bottom: 18px;
  padding-bottom: 10px; border-bottom: 1px solid #dee2e6;
}

.form-control, .form-select {
  border-radius: 8px; border: 1.5px solid #e0e0e0;
  padding: 0.55rem 0.8rem; font-size: 14px;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.form-control:focus, .form-select:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102,126,234,0.12);
}
.form-label { font-size: 13px; margin-bottom: 5px; color: #444; }
.req { color: #dc3545; font-style: normal; }

.image-upload-wrapper {
  background: #f8f9fb; min-height: 160px;
  display: flex; align-items: center; justify-content: center;
  border: 2px dashed #dee2e6 !important;
  border-radius: 10px !important;
  overflow: hidden; transition: all 0.2s; cursor: pointer;
}
.image-upload-wrapper:hover { border-color: #667eea !important; background: #f1f0ff; }
.image-upload-wrapper.has-image { border-style: solid !important; }
.preview-img { width: 100%; height: 160px; object-fit: cover; display: block; }
.upload-placeholder { height: 160px; cursor: pointer; padding: 20px; }
.upload-icon-wrap {
  width: 52px; height: 52px; background: #e8e8ff;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 22px; color: #667eea; transition: transform 0.2s;
}
.upload-placeholder:hover .upload-icon-wrap { transform: translateY(-4px); }
.upload-overlay {
  position: absolute; inset: 0;
  background: rgba(0,0,0,0.3);
  display: flex; align-items: center; justify-content: center;
  opacity: 0; transition: opacity 0.2s;
}
.image-upload-wrapper:hover .upload-overlay { opacity: 1; }

.xfoot { background: #f8f9fa; }
.btn-submit {
  background: linear-gradient(135deg, #667eea, #764ba2);
  border: none; color: #fff; border-radius: 8px;
  font-weight: 600; transition: opacity 0.2s;
}
.btn-submit:hover { opacity: 0.9; color: #fff; }
.btn-submit:disabled { opacity: 0.6; }

.slide-fade-enter-active, .slide-fade-leave-active { transition: all .25s ease; }
.slide-fade-enter-from, .slide-fade-leave-to { opacity: 0; transform: translateY(20px); }
</style>