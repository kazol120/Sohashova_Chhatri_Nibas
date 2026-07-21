<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
      <div class="col-12">
        <div class="card mb-4 shadow-sm">
        <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3">
          <h5 class="card-title mb-0">
            {{ userRole === 'admin' ? 'Staffs List' : 'Staffs' }}
          </h5>
          <button
            v-if="userRole === 'admin'"
            class="btn btn-primary"
            type="button"
            @click="openCreateFromComponent">
            <i class="ti ti-plus me-1"></i> Add Staff
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
              <input
                type="text"
                class="form-control form-control-sm"
                style="width:300px"
                placeholder="Search employee id / name / phone / email..."
                v-model="search"
                @keyup.enter="fetchStaffs(1)"
              />
            </div>

            <div class="table-responsive">
              <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th style="width:55px">Sl</th>
                    <th style="width:90px">Photo</th>
                    <th style="width:130px">Employee ID</th>
                    <th style="width:160px">Name</th>
                    <th style="width:120px">Phone</th>
                    <th style="width:175px">Email</th>
                    <th style="width:140px">NID / Passport</th>
                    <th style="width:90px">Gender</th>
                    <th style="width:115px">DOB</th>
                    <th style="width:110px">Division</th>
                    <th style="width:110px">District</th>
                    <th style="width:110px">Thana</th>
                    <th style="width:170px">Address</th>
                    <th style="width:130px">Designation</th>
                    <th style="width:130px">Department</th>
                    <th style="width:100px">Salary</th>
                    <th style="width:120px">Joining</th>
                    <th style="width:105px">Shift</th>
                    <th style="width:110px">Actions</th>
                  </tr>
                </thead>
                <tbody v-if="staffs.length">
                  <tr v-for="(r, idx) in staffs" :key="r.id">
                    <td>{{ from + idx }}</td>
                    <td>
                      <img v-if="r.image_url" :src="r.image_url" class="img-thumb" alt="photo" @error="onImgError($event)" />
                      <div v-else class="no-img"><i class="fa fa-user"></i></div>
                    </td>
                    <td>{{ r.employee_id       || "-" }}</td>
                    <td>{{ r.name              || "-" }}</td>
                    <td>{{ r.phone             || "-" }}</td>
                    <td>{{ r.email             || "-" }}</td>
                    <td>{{ r.nid_passport      || "-" }}</td>
                    <td>{{ r.gender            || "-" }}</td>
                    <td>{{ r.date_of_birth     || "-" }}</td>
                    <td>{{ r.division?.name    || "-" }}</td>
                    <td>{{ r.district?.name    || "-" }}</td>
                    <td>{{ r.thana?.name       || "-" }}</td>
                    <td>{{ r.permanent_address || "-" }}</td>
                    <td>{{ r.designation       || "-" }}</td>
                    <td>{{ r.department        || "-" }}</td>
                    <td>{{ r.salary            || "-" }}</td>
                    <td>{{ r.joining_date      || "-" }}</td>
                    <td>{{ r.shift_time        || "-" }}</td>
                    <td>
                      <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-primary" @click="openEditModal(r)" title="Edit">
                          <i class="ti ti-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" @click="openDeleteModal(r)" title="Delete">
                          <i class="ti ti-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
                <tbody v-else>
                  <tr>
                    <td colspan="19" class="text-center py-5 text-muted">
                      <span v-if="loading"><i class="fa fa-spinner fa-spin me-2"></i>Loading...</span>
                      <span v-else>No staffs found</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
              <div class="small text-muted">Total: {{ total }} | Page: {{ currentPage }} / {{ totalPages }}</div>
              <div class="d-flex gap-2">
                <button class="btn btn-sm btn-secondary" :disabled="currentPage <= 1 || loading" @click="fetchStaffs(currentPage - 1)">Previous</button>
                <button class="btn btn-sm btn-secondary" :disabled="currentPage >= totalPages || loading" @click="fetchStaffs(currentPage + 1)">Next</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- CREATE -->
    <StaffsCreate
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
            <div class="xbox" role="dialog" aria-modal="true">

              <div class="xhead d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="ti ti-edit me-2"></i>Edit Staff</h5>
                <button type="button" class="btn-close" @click="closeEditModal"></button>
              </div>

              <form @submit.prevent="updateStaff">
                <div class="xbody custom-scrollbar">
                  <div class="row g-4">

                    <!-- LEFT -->
                    <div class="col-12 col-lg-6">
                      <div class="scard">
                        <div class="scard-title"><i class="fa fa-id-card me-2"></i>Personal Info</div>

                        <div class="row g-3 mb-3">
                              <div class="col-6">
                          <label class="form-label fw-semibold">Employee ID <span class="req">*</span></label>
                          <input type="text" class="form-control" :class="{'is-invalid': errors.employee_id}"
                            v-model="edit.form.employee_id" required />
                          <div class="invalid-feedback">{{ errors.employee_id }}</div>
                        </div>
                        <div class="col-6">
                           <label class="form-label fw-semibold">Full Name <span class="req">*</span></label>
                          <input type="text" class="form-control" :class="{'is-invalid': errors.name}"
                            v-model="edit.form.name" required />
                          <div class="invalid-feedback">{{ errors.name }}</div>
                        </div>
                        </div>
                        <div class="row g-3 mb-3">
                          <div class="col-6">
                            <label class="form-label fw-semibold">Phone <span class="req">*</span></label>
                            <input type="text" class="form-control" :class="{'is-invalid': errors.phone}"
                              v-model="edit.form.phone" required />
                            <div class="invalid-feedback">{{ errors.phone }}</div>
                          </div>
                          <div class="col-6">
                            <label class="form-label fw-semibold">Gender</label>
                            <select class="form-select" v-model="edit.form.gender">
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
                            v-model="edit.form.email" />
                          <div class="invalid-feedback">{{ errors.email }}</div>
                        </div>
                        <div class="mb-3">
                          <label class="form-label fw-semibold">Password <span class="req">*</span></label>
                          <input type="text" class="form-control" :class="{'is-invalid': errors.password}"
                            v-model="edit.form.password" required />
                          <div class="invalid-feedback">{{ errors.password }}</div>
                        </div>
                        <div class="row g-3 mb-3">
                          <div class="col-6">
                            <label class="form-label fw-semibold">Date of Birth</label>
                            <input type="date" class="form-control" v-model="edit.form.date_of_birth" />
                          </div>
                          <div class="col-6">
                            <label class="form-label fw-semibold">NID / Passport</label>
                            <input type="text" class="form-control" v-model="edit.form.nid_passport" />
                          </div>
                        </div>
                        <div class="mb-0">
                          <label class="form-label fw-semibold">Staff Photo</label>
                          <div
                            class="photo-box"
                            :class="{'photo-box--has': edit.preview || edit.form.image_url}"
                            @click="$refs.photoInput.click()">
                            <img
                              v-if="edit.preview || edit.form.image_url"
                              :src="edit.preview || edit.form.image_url"
                              class="photo-preview"
                              @error="onImgError($event)"/>
                            <div v-if="edit.preview || edit.form.image_url" class="photo-overlay">
                              <i class="fa fa-camera me-1"></i> Change Photo
                            </div>
                            <div v-else class="photo-placeholder">
                              <div class="photo-icon"><i class="fa fa-cloud-upload-alt"></i></div>
                              <span class="fw-semibold mt-2">Click to Upload</span>
                              <small class="text-muted">JPG, PNG, WEBP — max 5MB</small>
                            </div>
                          </div>
                          <input
                            ref="photoInput"
                            type="file"
                            class="d-none"
                            accept="image/jpeg,image/png,image/webp,image/jfif"
                            @change="onEditImageChange"
                          />
                          <div class="text-danger small mt-1" v-if="errors.photo">{{ errors.photo }}</div>
                        </div>
                      </div>
                    </div>
                    <!-- RIGHT -->
                    <div class="col-12 col-lg-6">
                      <div class="scard">
                        <div class="scard-title"><i class="fa fa-briefcase me-2"></i>Job & Address Info</div>

                        <div class="mb-3">
                          <label class="form-label fw-semibold">Division</label>
                          <select class="form-select" v-model="edit.form.division_id" @change="loadDistricts">
                            <option value="">Select Division</option>
                            <option v-for="d in divisions" :key="d.id" :value="d.id">{{ d.name }}</option>
                          </select>
                        </div>

                        <div class="mb-3">
                          <label class="form-label fw-semibold">District</label>
                          <select class="form-select" v-model="edit.form.district_id" @change="loadThanas"
                            :disabled="!edit.form.division_id">
                            <option value="">Select District</option>
                            <option v-for="d in districts" :key="d.id" :value="d.id">{{ d.name }}</option>
                          </select>
                        </div>

                        <div class="mb-3">
                          <label class="form-label fw-semibold">Thana</label>
                          <select class="form-select" v-model="edit.form.thana_id"
                            :disabled="!edit.form.district_id">
                            <option value="">Select Thana</option>
                            <option v-for="t in thanas" :key="t.id" :value="t.id">{{ t.name }}</option>
                          </select>
                        </div>

                        <div class="mb-3">
                          <label class="form-label fw-semibold">Permanent Address</label>
                          <textarea class="form-control" v-model="edit.form.permanent_address" rows="3"></textarea>
                        </div>

                        <div class="row g-3 mb-3">
                          <div class="col-6">
                            <select class="form-select" v-model="edit.form.designation">
                              <option value="">Select</option>
                              <option value="Manager">Manager</option>
                              <option value="Receptionist">Receptionist</option>
                              <option value="Cleaner">Cleaner</option>
                              <option value="Chef">Chef</option>
                            </select>
                          </div>
                          <div class="col-6">
                            <label class="form-label fw-semibold">Department</label>
                            <select class="form-select" v-model="edit.form.department">
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
                            <input type="number" step="0.01" class="form-control" v-model="edit.form.salary" />
                            <div class="text-danger small" v-if="errors.salary">{{ errors.salary }}</div>
                          </div>
                          <div class="col-6">
                            <label class="form-label fw-semibold">Shift Time</label>
                            <select class="form-select" v-model="edit.form.shift_time">
                              <option value="">Select</option>
                              <option value="Morning">Morning</option>
                              <option value="Evening">Evening</option>
                              <option value="Night">Night</option>
                            </select>
                          </div>
                        </div>

                        <div class="mb-0">
                          <label class="form-label fw-semibold">Joining Date</label>
                          <input type="date" class="form-control" v-model="edit.form.joining_date" />
                        </div>
                      </div>
                    </div>

                  </div>
                </div>

                <div class="xfoot d-flex justify-content-end gap-2">
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
                <h5 class="mb-0 text-danger"><i class="ti ti-trash me-2"></i>Delete Staff</h5>
                <button type="button" class="btn btn-sm btn-light" @click="closeDelete">
                  <i class="fa fa-times"></i>
                </button>
              </div>
              <div class="xbody">
                <div class="alert alert-warning mb-0">
                  Are you sure you want to permanently delete this staff member?
                </div>
              </div>
              <div class="xfoot d-flex justify-content-end gap-2">
                <button class="btn btn-outline-secondary" type="button" @click="closeDelete">Cancel</button>
                <button class="btn btn-danger" type="button" :disabled="savingDelete" @click="confirmDelete">
                  <span v-if="savingDelete"><i class="fa fa-spinner fa-spin me-1"></i> Deleting...</span>
                  <span v-else><i class="ti ti-trash me-1"></i> Yes, Delete</span>
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
import StaffsCreate from "../../components/createform/StaffsCreate.vue";

export default {
  name: "StaffList",
  components: { StaffsCreate },

  data() {
    return {
      showCreateModal: false,
      staffs: [],
      loading: false
      ,
    userRole: "",
      search: "",
      perPage: 10,
      currentPage: 1,
      totalPages: 1,
      total: 0,
      from: 1,
      divisions: [],
      districts: [],
      thanas: [],
      edit: {
        open: false,
        form: {
          id: null,
          employee_id: "",
          name: "",
          phone: "",
          email: "",
          nid_passport: "",
          gender: "",
          date_of_birth: "",
          division_id: "",
          district_id: "",
          thana_id: "",
          permanent_address: "",
          designation: "",
          department: "",
          salary: "",
          joining_date: "",
          shift_time: "",
          photo: null,
          image_url: "",
        },
        preview: "",
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
    this.fetchStaffs(1);
    this.loadDivisions();
    this.getUserRole();
  },

  watch: {
    search() {
      clearTimeout(this._t);
      this._t = setTimeout(() => this.fetchStaffs(1), 300);
    },
    perPage()         { this.fetchStaffs(1); },
    showCreateModal() { this.syncBodyOverflow(); },
    "edit.open"()     { this.syncBodyOverflow(); },
    "del.open"()      { this.syncBodyOverflow(); },
  },

  beforeUnmount() {
    clearTimeout(this._t);
    this.cleanPreview();
    document.body.style.overflow = "";
  },

  methods: {

async getUserRole() {

  try {

    const res = await axios.get(`${this.url}get-user-role`);

    this.userRole = res.data.role;

  } catch (e) {

    console.error(e);

  }
},

    syncBodyOverflow() {
      document.body.style.overflow =
        (this.showCreateModal || this.edit.open || this.del.open) ? "hidden" : "";
    },

    toast(text, type = "success") {
      const bg =
        type === "success" ? "linear-gradient(to right,#00b09b,#96c93d)"
        : type === "warning" ? "linear-gradient(to right,#f59e0b,#fbbf24)"
        : "linear-gradient(to right,#ff5f6d,#ffc371)";
      Toastify({ text, duration: 2500, close: true, gravity: "top", position: "right", backgroundColor: bg }).showToast();
    },

    onImgError(e) {
      e.target.style.display = "none";
    },

    async fetchStaffs(page = 1) {
      this.loading = true;
      try {
        const res = await axios.get(`${this.url}staffs-get`, {
          params: { page, per_page: this.perPage, search: this.search },
        });
        this.staffs      = res.data.data         || [];
        this.currentPage = res.data.current_page || 1;
        this.totalPages  = res.data.last_page    || 1;
        this.total       = res.data.total        || 0;
        this.from        = res.data.from         || 1;
      } catch (e) {
        console.error(e);
        this.toast("Failed to load staffs", "error");
      } finally {
        this.loading = false;
      }
    },

    async loadDivisions() {
      try {
        const res = await axios.get(`${this.url}divisions`);
        this.divisions = res.data || [];
      } catch (e) { console.error(e); }
    },

    async loadDistricts() {
      this.edit.form.district_id = "";
      this.edit.form.thana_id   = "";
      this.districts = [];
      this.thanas    = [];
      if (!this.edit.form.division_id) return;
      try {
        const res = await axios.get(`${this.url}divisions/${this.edit.form.division_id}/districts`);
        this.districts = res.data || [];
      } catch (e) { console.error(e); }
    },

    async loadThanas() {
      this.edit.form.thana_id = "";
      this.thanas = [];
      if (!this.edit.form.district_id) return;
      try {
        const res = await axios.get(`${this.url}districts/${this.edit.form.district_id}/thanas`);
        this.thanas = res.data || [];
      } catch (e) { console.error(e); }
    },

    openCreateFromComponent() { this.showCreateModal = true; },

    async handleCreated() {
      this.showCreateModal = false;
      await this.fetchStaffs(this.currentPage);
    },

    openEditModal(r) {
      this.errors = {};
      this.cleanPreview();
      this.edit.form = {
        id: r.id,
        employee_id: r.employee_id || "",
        name: r.name || "",
        phone: r.phone || "",
        email: r.email || "",
        password: r.password || "",
        nid_passport: r.nid_passport || "",
        gender: r.gender || "",
        date_of_birth: r.date_of_birth || "",
        division_id: r.division_id || "",
        district_id: r.district_id || "",
        thana_id: r.thana_id || "",
        permanent_address: r.permanent_address || "",
        designation: r.designation || "",
        department: r.department || "",
        salary: r.salary || "",
        joining_date: r.joining_date || "",
        shift_time: r.shift_time || "",
        photo: null,
        image_url: r.image_url || "",
      };
      this.edit.preview = "";
      this.edit.open    = true;

      if (this.edit.form.division_id) {
        axios.get(`${this.url}divisions/${this.edit.form.division_id}/districts`)
          .then(res => { this.districts = res.data || []; })
          .catch(console.error);
      }
      if (this.edit.form.district_id) {
        axios.get(`${this.url}districts/${this.edit.form.district_id}/thanas`)
          .then(res => { this.thanas = res.data || []; })
          .catch(console.error);
      }
    },

    closeEditModal() {
      this.edit.open = false;
      this.errors    = {};
      this.cleanPreview();
    },

    cleanPreview() {
      if (this.edit.preview?.startsWith("blob:")) {
        URL.revokeObjectURL(this.edit.preview);
      }
      this.edit.preview = "";
    },

    onEditImageChange(e) {
      const file = e.target?.files?.[0];
      if (!file) return;
      if (file.size > 5 * 1024 * 1024) {
        this.errors.photo = "Image must be less than 5MB";
        e.target.value = "";
        return;
      }
      this.errors.photo    = null;
      this.edit.form.photo = file;
      this.cleanPreview();
      this.edit.preview    = URL.createObjectURL(file);
      e.target.value       = "";
    },

    removePhoto() {
      this.cleanPreview();
      this.edit.form.photo     = null;
      this.edit.form.image_url = "";
      if (this.$refs.photoInput) this.$refs.photoInput.value = "";
    },

    async updateStaff() {
      this.savingEdit = true;
      this.errors     = {};
      try {
        const fd = new FormData();
        fd.append("employee_id",       this.edit.form.employee_id || "");
        fd.append("name",              this.edit.form.name || "");
        fd.append("phone",             this.edit.form.phone || "");
        fd.append("email",             this.edit.form.email || "");
        fd.append("password",             this.edit.form.password || "");
        fd.append("nid_passport",      this.edit.form.nid_passport || "");
        fd.append("gender",            this.edit.form.gender || "");
        fd.append("date_of_birth",     this.edit.form.date_of_birth || "");
        fd.append("division_id",       this.edit.form.division_id || "");
        fd.append("district_id",       this.edit.form.district_id || "");
        fd.append("thana_id",          this.edit.form.thana_id || "");
        fd.append("permanent_address", this.edit.form.permanent_address || "");
        fd.append("designation",       this.edit.form.designation || "");
        fd.append("department",        this.edit.form.department || "");
        fd.append("salary",            this.edit.form.salary || "");
        fd.append("joining_date",      this.edit.form.joining_date || "");
        fd.append("shift_time",        this.edit.form.shift_time || "");
        if (this.edit.form.photo instanceof File) {
          fd.append("photo", this.edit.form.photo);
        }

        const res = await axios.post(
          `${this.url}staffs-update/${this.edit.form.id}`,
          fd,
          { headers: { "Content-Type": "multipart/form-data" } }
        );

        this.toast(res.data?.message || "Updated successfully", "success");
        this.closeEditModal();
        await this.fetchStaffs(this.currentPage);
      } catch (e) {
        const data = e?.response?.data;
        if (e?.response?.status === 422 && data?.errors) {
          Object.keys(data.errors).forEach(k => {
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
        const res = await axios.delete(`${this.url}staffs-delete/${this.del.item.id}`);
        this.toast(res.data?.message || "Staff deleted successfully", "success");
        this.closeDelete();
        const willBeEmpty = this.staffs.length === 1 && this.currentPage > 1;
        await this.fetchStaffs(willBeEmpty ? this.currentPage - 1 : this.currentPage);
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
  width: 78px; height: 54px; object-fit: cover;
  border-radius: 8px; border: 1px solid #e5e7eb;
}
.no-img {
  width: 78px; height: 54px; border-radius: 8px;
  background: #f1f3f5; border: 1px solid #e5e7eb;
  display: flex; align-items: center; justify-content: center;
  color: #adb5bd; font-size: 1.2rem;
}
.xmask {
  position: fixed; inset: 0; z-index: 20000;
  background: rgba(0,0,0,.55);
  display: flex; align-items: flex-start; justify-content: center;
  padding: 24px 16px; overflow-y: auto;
}
.xwrap { width: 100%; display: flex; justify-content: center; }
.xbox {
  width: min(96vw, 1000px); background: #fff;
  border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,.28);
  overflow: hidden; display: flex; flex-direction: column;
}
.xbox.xsmall { width: min(94vw, 520px); }
.xhead { padding: 16px 20px; border-bottom: 1px solid #eef2f7; background: #fff; flex-shrink: 0; }
.xbody { padding: 20px; overflow-y: auto; max-height: calc(100vh - 160px); }
.xfoot { padding: 14px 20px; border-top: 1px solid #eef2f7; background: #fafafa; flex-shrink: 0; }
.scard { border: 1px solid #e9ecef; border-radius: 12px; padding: 18px; }
.scard-title {
  font-size: .8rem; font-weight: 700; text-transform: uppercase;
  letter-spacing: .05em; color: #6c757d;
  margin-bottom: 14px; padding-bottom: 10px; border-bottom: 1px solid #f0f0f0;
}
.req { color: #dc3545; }
.form-control, .form-select {
  border-radius: 8px; padding: .58rem .75rem; border: 1px solid #dce0e4;
}
.form-control:focus, .form-select:focus {
  border-color: #0d6efd; box-shadow: 0 0 0 .22rem rgba(13,110,253,.12);
}
.photo-box {
  position: relative; border: 2px dashed #ced4da; border-radius: 12px;
  background: #f8f9fa; cursor: pointer; overflow: hidden;
  min-height: 190px; transition: border-color .2s;
}
.photo-box:hover { border-color: #0d6efd; }
.photo-box--has { border-style: solid; border-color: #dee2e6; }
.photo-preview { width: 100%; height: 190px; object-fit: cover; display: block; }
.photo-overlay {
  position: absolute; inset: 0; background: rgba(0,0,0,.32);
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-weight: 600; font-size: .95rem;
  opacity: 0; transition: opacity .2s;
}
.photo-box:hover .photo-overlay { opacity: 1; }
.photo-placeholder {
  min-height: 190px; display: flex; flex-direction: column;
  align-items: center; justify-content: center; padding: 20px;
}
.photo-icon {
  width: 54px; height: 54px; border-radius: 50%;
  background: #e9ecef; display: flex; align-items: center; justify-content: center;
  font-size: 1.4rem; color: #6c757d;
}
.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #ddd; border-radius: 10px; }
.slide-fade-enter-active, .slide-fade-leave-active { transition: all .2s ease; }
.slide-fade-enter-from, .slide-fade-leave-to { opacity: 0; transform: translateY(12px) scale(.98); }
</style>