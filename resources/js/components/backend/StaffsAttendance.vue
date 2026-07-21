<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
      <div class="col-12">
        <div class="card mb-4 shadow-sm">

          <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3">
            <h5 class="card-title mb-0">Staffs Attendance</h5>
            <button class="btn btn-primary" type="button" @click="openCreateFromComponent">
              <i class="ti ti-plus me-1"></i> Add Attendance
            </button>
          </div>

          <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3">
            <div>
              <div class="px-3 pt-3">
                <div class="d-flex mb-4">
                  <div>
                    <label class="mb-2 text-black">Start Date</label>
                    <input class="form-control" type="date" v-model="startDate" @change="filterData">
                  </div>
                  <div class="ms-4">
                    <label class="mb-2 text-black">End Date</label>
                    <input class="form-control" type="date" v-model="endDate" @change="filterData">
                  </div>
                  <div class="ms-4 d-flex align-items-end">
                    <button class="btn btn-outline-secondary" @click="clearFilters">Clear</button>
                  </div>
                </div>
              </div>
            </div>
            <div>
              <button class="btn btn-primary" type="button" @click="printTable">
              <i class="ti ti-printer me-1"></i> Print
              </button>
            </div>
          </div>

          <div class="px-3 pt-3">
            <div class="d-flex mb-4">
              <select
                v-model="form.selected_staff"
                class="form-select"
                style="max-width: 300px;"
                @change="fetchStaffs(1)">
                <option value="">All Staff</option>
                <option
                  v-for="staff in staffNames"
                  :key="staff.id"
                  :value="staff.id">
                  {{ staff.name }}
                </option>
                </select>
                 <div class="ms-4  align-items-end">
                <button class="btn btn-outline-secondary" @click="clearFiltersStafflist">Clear</button>
              </div>
            </div>
          </div>

          <div class="card-body">
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
              <div class="d-flex align-items-center gap-2">
                <label class="small text-muted mb-0">Rows:</label>
                <select class="form-select form-select-sm" style="width:90px" v-model.number="perPage">
                  <option :value="50">50</option>
                  <option :value="100">100</option>
                  <option :value="200">200</option>
                  <option :value="500">500</option>
                </select>
              </div>
              <input
                type="text"
                class="form-control form-control-sm"
                style="width:300px"
                placeholder="Search employee id / name / phone..."
                v-model="search"
                @keyup.enter="fetchStaffs(1)"
              />
            </div>
            <div class="table-responsive">
              <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th style="width:55px">Sl</th>
                    <th style="width:180px">Staff Name</th>
                    <th style="width:220px">Date</th>
                    <th style="width:220px">IN Time</th>
                    <th style="width:220px">Out Time</th>
                    <th style="width:220px">Hours</th>
                    <th style="width:55px">Action</th>
                  </tr>
                </thead>
                <tbody v-if="staffs.length">
                  <tr v-for="(r, idx) in staffs" :key="r.id">
                    <td>{{ from + idx }}</td>
                    <td>{{ r.staff?.name || "-" }}</td>
                    <td>{{ formatOnlyDate(r.start_datetime) }}</td>
                    <td>{{ formatOnlyTime(r.start_datetime) }}</td>
                    <td>{{ formatOnlyTime(r.end_datetime) }}</td>
                    <td>{{ formateonlyhouer(r.start_datetime, r.end_datetime) }}</td>
                    <td>
                      <div class="d-flex gap-1 align-items-center">
                        <button class="btn btn-sm btn-primary" @click="openEditModal(r)">
                          <i class="ti ti-edit"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
                <tbody v-else>
                  <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                      <span v-if="loading"><i class="fa fa-spinner fa-spin me-2"></i>Loading...</span>
                      <span v-else>No staffs attendance found</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
              <div class="small text-muted">
                Total: {{ total }} | Page: {{ currentPage }} / {{ totalPages }}
              </div>
              <div class="d-flex gap-2">
                <button class="btn btn-sm btn-secondary" :disabled="currentPage <= 1 || loading" @click="fetchStaffs(currentPage - 1)">Previous</button>
                <button class="btn btn-sm btn-secondary" :disabled="currentPage >= totalPages || loading" @click="fetchStaffs(currentPage + 1)">Next</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ═══════════════ EDIT MODAL ═══════════════ -->
    <div v-if="editOpen" class="modal-overlay" @click.self="closeEditModal">
      <div class="modal-box">
        <div class="modal-box-head d-flex justify-content-between align-items-center">
          <h5 class="mb-0"><i class="ti ti-edit me-2"></i>Edit Attendance</h5>
          <button type="button" class="btn-close" @click="closeEditModal"></button>
        </div>

        <form @submit.prevent="updateAttendance">
          <div class="modal-box-body">
            <div class="row g-3">

              <!-- Start Time Card -->
              <div class="col-12 col-md-6">
                <div class="time-card start-card">
                  <div class="time-card-header start-header">
                    <i class="ti ti-player-play-filled me-2"></i>Start Time
                  </div>
                  <div class="time-card-body">
                    <div class="mb-3">
                      <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                      <input type="date" class="form-control" v-model="editForm.start_date" required />
                    </div>
                    <div class="mb-2">
                      <label class="form-label fw-semibold">Time <span class="text-danger">*</span></label>
                      <div class="time-display-box" @click="openTimePicker('start')">
                        {{ formatDisplayTime(editForm.start_hour, editForm.start_minute, editForm.start_ampm) }}
                      </div>
                    </div>
                    <div class="datetime-preview start-preview" v-if="editForm.start_date">
                      {{ previewDateTime(editForm.start_date, editForm.start_hour, editForm.start_minute, editForm.start_ampm) }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- End Time Card -->
              <div class="col-12 col-md-6">
                <div class="time-card end-card">
                  <div class="time-card-header end-header">
                    <i class="ti ti-player-stop-filled me-2"></i>End Time
                  </div>
                  <div class="time-card-body">
                    <div class="mb-3">
                      <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                      <input type="date" class="form-control" v-model="editForm.end_date" required />
                    </div>
                    <div class="mb-2">
                      <label class="form-label fw-semibold">Time <span class="text-danger">*</span></label>
                      <div class="time-display-box" @click="openTimePicker('end')">
                        {{ formatDisplayTime(editForm.end_hour, editForm.end_minute, editForm.end_ampm) }}
                      </div>
                    </div>
                    <div class="datetime-preview end-preview" v-if="editForm.end_date">
                      {{ previewDateTime(editForm.end_date, editForm.end_hour, editForm.end_minute, editForm.end_ampm) }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-box-foot d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-secondary" @click="closeEditModal">Cancel</button>
            <button type="submit" class="btn btn-success" :disabled="savingEdit">
              <span v-if="savingEdit"><i class="fa fa-spinner fa-spin me-1"></i> Updating...</span>
              <span v-else><i class="ti ti-device-floppy me-1"></i> Update</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ═══════════════ TIME PICKER POPUP ═══════════════ -->
    <div v-if="timePicker.open" class="modal-overlay tp-overlay" @click.self="cancelTimePicker">
      <div class="time-picker-box">
        <div class="time-picker-title">Select time</div>
        <div class="time-picker-body">

          <!-- Hours -->
          <div class="scroll-col" ref="hourCol">
            <div
              v-for="h in hours12"
              :key="'h'+h"
              class="scroll-item"
              :class="{ active: timePicker.hour === h }"
              @click="timePicker.hour = h"
            >{{ String(h).padStart(2,'0') }}</div>
          </div>
          <div class="time-sep">:</div>
          <!-- Minutes -->
          <div class="scroll-col" ref="minCol">
            <div
              v-for="m in minutesList"
              :key="'m'+m"
              class="scroll-item"
              :class="{ active: timePicker.minute === m }"
              @click="timePicker.minute = m"
            >{{ String(m).padStart(2,'0') }}</div>
          </div>
          <!-- AM/PM -->
          <div class="scroll-col ampm-col">
            <div
              class="scroll-item"
              :class="{ active: timePicker.ampm === 'AM' }"
              @click="timePicker.ampm = 'AM'"
            >AM</div>
            <div
              class="scroll-item"
              :class="{ active: timePicker.ampm === 'PM' }"
              @click="timePicker.ampm = 'PM'"
            >PM</div>
          </div>
        </div>

        <div class="time-picker-foot">
          <button type="button" class="btn btn-outline-secondary btn-sm px-4" @click="cancelTimePicker">Cancel</button>
          <button type="button" class="btn btn-primary btn-sm px-4" @click="saveTimePicker">Save</button>
        </div>
      </div>
    </div>

    <StaffsAttendanceForm
      :show="showCreateModal"
      :base-url="url"
      @close="showCreateModal = false"
      @created="handleCreated"
    />
  </div>
</template>

<script>
import axios from "axios";
import Toastify from "toastify-js";
import "toastify-js/src/toastify.css";
import StaffsAttendanceForm from "../../components/createform/StaffsAttendanceForm.vue";

export default {
  name: "StaffList",
  components: { StaffsAttendanceForm },
  data() {
    return {
      staffNames: [],
      showCreateModal: false,
      staffs: [],
      loading: false,
      search: "",
      perPage: 50,
      startDate: "",
      endDate: "",
      currentPage: 1,
      totalPages: 1,
      total: 0,
      from: 1,
      form: {
        selected_staff: "",
      },
      editOpen: false,
      savingEdit: false,
      editForm: {
        id: null,
        start_date: "",
        start_hour: 12,
        start_minute: 0,
        start_ampm: "AM",
        end_date: "",
        end_hour: 12,
        end_minute: 0,
        end_ampm: "AM",
      },
      // Time Picker
      timePicker: {
        open: false,
        target: "start",
        hour: 12,
        minute: 0,
        ampm: "AM",
      },
      hours12: [1,2,3,4,5,6,7,8,9,10,11,12],
      minutesList: Array.from({ length: 60 }, (_, i) => i),
    };
  },

  computed: {
    url() {
      return this.$store.state.url;
    },
  },

  mounted() {
    this.fetchStaffs(1);
    this.getsatffname();
  },

  watch: {
    search() {
      clearTimeout(this._t);
      this._t = setTimeout(() => this.fetchStaffs(1), 300);
    },
    perPage() {
      this.fetchStaffs(1);
    },
  },

  methods: {


printTable() {
  const rows = this.staffs.map((r, idx) => `
    <tr>
      <td>${this.from + idx}</td>
      <td>${r.staff?.name || '-'}</td>
      <td>${this.formatOnlyDate(r.start_datetime)}</td>
      <td>${this.formatOnlyTime(r.start_datetime)}</td>
      <td>${this.formatOnlyTime(r.end_datetime)}</td>
      <td>${this.formateonlyhouer(r.start_datetime, r.end_datetime)}</td>
    </tr>
  `).join('');

  const html = `
    <!DOCTYPE html>
    <html>
    <head>
      <title>Staff Attendance</title>
      <style>
        @page { size: A4 portrait; margin: 15mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h2 { text-align: center; margin-bottom: 14px; font-size: 16px; }
        p.sub { text-align: center; margin-bottom: 12px; font-size: 12px; font-weight: 400;}
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 7px 9px; text-align: left; }
        th { background: #e9e9e9; font-weight: bold; }
        tr:nth-child(even) td { background: #f9f9f9; }
      </style>
    </head>
    <body>
      <h2>Staffs Attendance</h2>
      <p class="sub">Printed: ${new Date().toLocaleString()}</p>
      <table>
        <thead>
          <tr>
            <th>Sl</th>
            <th>Staff Name</th>
            <th>Date</th>
            <th>IN Time</th>
            <th>Out Time</th>
            <th>Hours</th>
          </tr>
        </thead>
        <tbody>${rows}</tbody>
      </table>
    </body>
    </html>
  `;
    const old = document.getElementById('print-iframe');
    if (old) old.remove();
    const iframe = document.createElement('iframe');
    iframe.id = 'print-iframe';
    iframe.style.cssText = 'position:fixed;top:0;left:0;width:0;height:0;border:none;visibility:hidden;';
    document.body.appendChild(iframe);
    iframe.contentDocument.open();
    iframe.contentDocument.write(html);
    iframe.contentDocument.close();
    iframe.onload = () => {
      setTimeout(() => {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
      }, 300);
    };
  },
    openCreateFromComponent() {
      this.showCreateModal = true;
    },

    handleCreated() {
      this.showCreateModal = false;
      this.fetchStaffs(1);
    },

    filterData() {
      this.fetchStaffs(1);
    },
    clearFiltersStafflist() {
    
      this.form.selected_staff = "";
      this.fetchStaffs(1);
    },


    clearFilters() {
      this.startDate = "";
      this.endDate = "";
      this.search = "";
      this.form.selected_staff = "";
      this.fetchStaffs(1);
    },

    toast(text, type = "success") {
      const bg =
        type === "success"
          ? "linear-gradient(to right,#00b09b,#96c93d)"
          : type === "warning"
          ? "linear-gradient(to right,#f59e0b,#fbbf24)"
          : "linear-gradient(to right,#ff5f6d,#ffc371)";
      Toastify({ text, duration: 2500, close: true, gravity: "top", position: "right", backgroundColor: bg }).showToast();
    },

    async fetchStaffs(page = 1) {
      this.loading = true;
      try {
        const res = await axios.get('/staffs-get-attendance', {
          params: {
            page,
            per_page: this.perPage,
            search: this.search,
            start_date: this.startDate,
            end_date: this.endDate,
            staff_id: this.form.selected_staff, 
          }
        });

        this.staffs      = res.data.data         || [];
        this.total       = res.data.total        || 0;
        this.currentPage = res.data.current_page || 1;
        this.totalPages  = res.data.last_page    || 1;
        this.from        = res.data.from         || 1;

      } catch (e) {
        this.toast("Failed to load.", "error");
      } finally {
        this.loading = false;
      }
    },

    async getsatffname() {
      try {
        const res = await axios.get("/get-select-staff");
        if (res.data.status === "success") {
          this.staffNames = res.data.data || [];
        }
      } catch (error) {
        this.toast("Failed to load staff names.", "error");
      }
    },

    // ── Edit Modal ────────────────────────────────────
    openEditModal(r) {
      const parse = (dt) => {
        if (!dt) return { date: "", hour: 12, minute: 0, ampm: "AM" };
        const normalized = dt.replace("T", " ");
        const [date, timeFull] = normalized.split(" ");
        let [h, m] = (timeFull || "00:00").split(":").map(Number);
        const ampm = h >= 12 ? "PM" : "AM";
        h = h % 12 || 12;
        return { date, hour: h, minute: m, ampm };
      };

      const s = parse(r.start_datetime);
      const e = parse(r.end_datetime);

      this.editForm = {
        id:           r.id,
        start_date:   s.date,
        start_hour:   s.hour,
        start_minute: s.minute,
        start_ampm:   s.ampm,
        end_date:     e.date,
        end_hour:     e.hour,
        end_minute:   e.minute,
        end_ampm:     e.ampm,
      };
      this.editOpen = true;
    },

    closeEditModal() {
      this.editOpen = false;
    },

    // ── Time Picker ───────────────────────────────────
    openTimePicker(target) {
      this.timePicker.target = target;
      this.timePicker.hour   = this.editForm[`${target}_hour`];
      this.timePicker.minute = this.editForm[`${target}_minute`];
      this.timePicker.ampm   = this.editForm[`${target}_ampm`];
      this.timePicker.open   = true;

      this.$nextTick(() => {
        this.scrollActiveIntoView();
      });
    },

    scrollActiveIntoView() {
      const hourCol = this.$refs.hourCol;
      const minCol  = this.$refs.minCol;
      if (hourCol) {
        const activeH = hourCol.querySelector('.scroll-item.active');
        if (activeH) activeH.scrollIntoView({ block: 'center', behavior: 'smooth' });
      }
      if (minCol) {
        const activeM = minCol.querySelector('.scroll-item.active');
        if (activeM) activeM.scrollIntoView({ block: 'center', behavior: 'smooth' });
      }
    },

    saveTimePicker() {
      const t = this.timePicker.target;
      this.editForm[`${t}_hour`]   = this.timePicker.hour;
      this.editForm[`${t}_minute`] = this.timePicker.minute;
      this.editForm[`${t}_ampm`]   = this.timePicker.ampm;
      this.timePicker.open = false;
    },

    cancelTimePicker() {
      this.timePicker.open = false;
    },

    // ── Update ────────────────────────────────────────
    async updateAttendance() {
      this.savingEdit = true;
      try {
        const to24 = (h, m, ampm) => {
          let hour = h;
          if (ampm === "AM" && h === 12) hour = 0;
          if (ampm === "PM" && h !== 12) hour = h + 12;
          return `${String(hour).padStart(2, "0")}:${String(m).padStart(2, "0")}:00`;
        };

        const payload = {
          start_datetime: `${this.editForm.start_date} ${to24(this.editForm.start_hour, this.editForm.start_minute, this.editForm.start_ampm)}`,
          end_datetime:   `${this.editForm.end_date} ${to24(this.editForm.end_hour, this.editForm.end_minute, this.editForm.end_ampm)}`,
        };

        await axios.put(`${this.url}staffs-attendance/${this.editForm.id}`, payload);
        this.toast("Attendance updated successfully");
        this.closeEditModal();
        this.fetchStaffs(this.currentPage);
      } catch (e) {
        const msg = e.response?.data?.errors
          ? Object.values(e.response.data.errors).flat().join(", ")
          : "Update failed";
        this.toast(msg, "error");
      } finally {
        this.savingEdit = false;
      }
    },

    // ── Helpers ───────────────────────────────────────
    formatDisplayTime(h, m, ampm) {
      return `${h}:${String(m).padStart(2, "0")} ${ampm}`;
    },

    previewDateTime(date, h, m, ampm) {
      if (!date) return "";
      return `${date} ${h}:${String(m).padStart(2, "0")} ${ampm}`;
    },

    formatOnlyDate(dateTime) {
      if (!dateTime) return "-";
      const datePart = dateTime.includes("T") ? dateTime.split("T")[0] : dateTime.split(" ")[0];
      const [year, month, day] = datePart.split("-");
      return `${day}-${month}-${year}`;
    },

    formatOnlyTime(dateTime) {
      if (!dateTime) return "-";
      const timePart = dateTime.includes("T")
        ? dateTime.split("T")[1].split(".")[0]
        : dateTime.split(" ")[1];
      let [hours, minutes] = timePart.split(":").map(Number);
      const ampm = hours >= 12 ? "PM" : "AM";
      hours = hours % 12 || 12;
      return `${hours}:${String(minutes).padStart(2, "0")} ${ampm}`;
    },

    formateonlyhouer(startTime, endTime) {
      if (!startTime || !endTime) return "-";
      const start = new Date(startTime.replace(" ", "T"));
      const end   = new Date(endTime.replace(" ", "T"));
      const totalMinutes = Math.floor((end - start) / (1000 * 60));
      if (totalMinutes < 0) return "-";
      const hours   = Math.floor(totalMinutes / 60);
      const minutes = totalMinutes % 60;
      if (hours === 0)   return `${minutes} min`;
      if (minutes === 0) return `${hours} hr`;
      return `${hours} hr ${minutes} min`;
    },



  },
};
</script>

<style scoped>
.form-control,
.form-select {
  border-radius: 8px;
  padding: .58rem .75rem;
  border: 1px solid #dce0e4;
}
.form-control:focus,
.form-select:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 .22rem rgba(13,110,253,.12);
}

/* ── Modal Overlay ── */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,.45);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}
.tp-overlay {
  z-index: 10000;
}

/* ── Edit Modal Box ── */
.modal-box {
  background: #fff;
  border-radius: 12px;
  width: 100%;
  max-width: 640px;
  box-shadow: 0 8px 32px rgba(0,0,0,.18);
  overflow: hidden;
}
.modal-box-head {
  padding: 16px 20px;
  border-bottom: 1px solid #eee;
  background: #f8f9fa;
}
.modal-box-body {
  padding: 20px;
}
.modal-box-foot {
  padding: 14px 20px;
  border-top: 1px solid #eee;
  background: #f8f9fa;
}

/* ── Time Cards ── */
.time-card {
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid #e0e0e0;
  height: 100%;
}
.time-card-header {
  padding: 10px 16px;
  font-weight: 600;
  font-size: 14px;
  display: flex;
  align-items: center;
}
.start-header {
  background-color: #e8f8f0;
  color: #1a7a4a;
  border-bottom: 1px solid #c3e6d4;
}
.end-header {
  background-color: #fdecea;
  color: #c0392b;
  border-bottom: 1px solid #f5c6c2;
}
.time-card-body {
  padding: 16px;
  background: #fff;
}

/* ── Time Display Box (clickable) ── */
.time-display-box {
  border: 1px solid #dce0e4;
  border-radius: 8px;
  padding: .58rem .75rem;
  cursor: pointer;
  background: #fff;
  font-size: 15px;
  user-select: none;
  transition: border-color .2s;
}
.time-display-box:hover {
  border-color: #0d6efd;
  background: #f8fbff;
}

/* ── DateTime Preview ── */
.datetime-preview {
  font-size: 13px;
  padding: 6px 12px;
  border-radius: 6px;
  display: inline-block;
  margin-top: 8px;
}
.start-preview {
  background: #e8f8f0;
  color: #1a7a4a;
}
.end-preview {
  background: #fdecea;
  color: #c0392b;
}

/* ── Time Picker Popup ── */
.time-picker-box {
  background: #fff;
  border-radius: 16px;
  width: 300px;
  box-shadow: 0 8px 40px rgba(0,0,0,.22);
  overflow: hidden;
}
.time-picker-title {
  text-align: center;
  font-weight: 600;
  padding: 16px;
  border-bottom: 1px solid #eee;
  font-size: 15px;
  color: #333;
}
.time-picker-body {
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding: 12px 8px;
  gap: 2px;
}
.time-sep {
  font-size: 24px;
  font-weight: 700;
  color: #555;
  padding: 8px 2px 0;
  align-self: flex-start;
}

/* ── Scroll Columns ── */
.scroll-col {
  display: flex;
  flex-direction: column;
  align-items: center;
  max-height: 200px;
  overflow-y: auto;
  width: 72px;
  scrollbar-width: none;
}
.scroll-col::-webkit-scrollbar {
  display: none;
}
.ampm-col {
  width: 60px;
  max-height: 200px;
  justify-content: center;
  padding-top: 10px;
}
.scroll-item {
  padding: 8px 10px;
  border-radius: 8px;
  font-size: 18px;
  font-weight: 500;
  cursor: pointer;
  width: 100%;
  text-align: center;
  color: #bbb;
  transition: all .15s ease;
  flex-shrink: 0;
}
.scroll-item.active {
  background: #f0f0f0;
  color: #111;
  font-weight: 700;
}
.scroll-item:hover {
  background: #f5f5f5;
  color: #444;
}
.time-picker-foot {
  display: flex;
  justify-content: space-between;
  padding: 14px 20px;
  border-top: 1px solid #eee;
  background: #fafafa;
}
</style>