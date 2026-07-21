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
                  <i class="fa fa-user-clock"></i>
                </div>
                <div>
                  <h5 class="mb-0 fw-bold text-dark">Staff Attendance</h5>
                  <small class="text-muted">Search staff and record attendance</small>
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

                  <!-- Staff Search -->
                  <div class="col-12">
                    <div class="section-card">
                      <label class="form-label fw-semibold">Select Staff <span class="req">*</span></label>
                      <div class="d-flex align-items-start gap-2">
                        <div class="medicine-search-container flex-grow-1">
                          <input
                            type="search"
                            v-model="staffSearch"
                            @input="searchStaff"
                            @focus="showDropdown = true"
                            class="form-control"
                            placeholder="Search staff name..."
                            autocomplete="off"
                          />
                          <div v-if="showDropdown && searchResults.length" class="staff-dropdown">
                            <div
                              v-for="staff in searchResults"
                              :key="staff.id"
                              class="staff-dropdown-item"
                              @mousedown.prevent="selectStaff(staff)"
                            >
                              <div class="staff-avatar">{{ staff.name.charAt(0) }}</div>
                              <span>{{ staff.name }}</span>
                            </div>
                          </div>
                        </div>

                        <button
                          type="button"
                          class="btn btn-success btn-sm px-3"
                          @click="addStaff"
                          :disabled="!selectedStaff"
                        >
                          <i class="fa fa-plus me-1"></i> Add
                        </button>
                      </div>

                      <div v-if="addedStaff" class="selected-staff-card mt-3">
                        <div class="staff-avatar lg">{{ addedStaff.name.charAt(0) }}</div>
                        <div>
                          <div class="fw-bold text-primary">{{ addedStaff.name }}</div>
                          <span class="badge bg-success-subtle text-success">Selected</span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <template v-if="addedStaff">

                    <!-- Start DateTime -->
                    <div class="col-12 col-lg-6">
                      <div class="section-card dt-card">
                        <div class="dt-label text-success">
                          <i class="fa fa-play-circle me-1"></i> Start Time
                        </div>

                        <div class="mb-3">
                          <label class="form-label">Date <span class="req">*</span></label>
                          <input
                            type="date"
                            class="form-control"
                            v-model="form.start_date"
                            required
                          />
                        </div>

                        <div class="mb-2">
                          <label class="form-label">Time <span class="req">*</span></label>
                          <div
                            class="time-picker-trigger form-control d-flex align-items-center justify-content-between"
                            @click="openTimePicker('start_time')"
                          >
                            <span :class="{ 'text-muted': !form.start_time }">
                              {{ form.start_time ? convertToAmPm(form.start_time) : 'Select time' }}
                            </span>
                            <i class="fa fa-clock-o"></i>
                          </div>
                        </div>

                        <div class="datetime-preview" v-if="form.start_date && form.start_time">
                          <i class="fa fa-clock-o me-1"></i>
                          {{ formatDisplay(form.start_date, form.start_time) }}
                        </div>
                      </div>
                    </div>

                    <!-- End DateTime -->
                    <div class="col-12 col-lg-6">
                      <div class="section-card dt-card">
                        <div class="dt-label text-danger">
                          <i class="fa fa-stop-circle me-1"></i> End Time
                        </div>

                        <div class="mb-3">
                          <label class="form-label">Date <span class="req">*</span></label>
                          <input
                            type="date"
                            class="form-control"
                            v-model="form.end_date"
                            required
                          />
                        </div>

                        <div class="mb-2">
                          <label class="form-label">Time <span class="req">*</span></label>
                          <div
                            class="time-picker-trigger form-control d-flex align-items-center justify-content-between"
                            @click="openTimePicker('end_time')"
                          >
                            <span :class="{ 'text-muted': !form.end_time }">
                              {{ form.end_time ? convertToAmPm(form.end_time) : 'Select time' }}
                            </span>
                            <i class="fa fa-clock-o"></i>
                          </div>
                        </div>

                        <div class="datetime-preview text-danger-emphasis" v-if="form.end_date && form.end_time">
                          <i class="fa fa-clock-o me-1"></i>
                          {{ formatDisplay(form.end_date, form.end_time) }}
                        </div>
                      </div>
                    </div>

                  </template>
                </div>
              </div>

              <!-- Footer -->
              <div class="xfoot px-4 py-3 border-top d-flex justify-content-between align-items-center">
                <small class="text-muted"><span class="req">*</span> Required fields</small>
                <div class="d-flex gap-2">
                  <button type="button" class="btn btn-outline-secondary px-4" @click="emitClose">
                    Cancel
                  </button>
                  <button
                    type="submit"
                    class="btn btn-submit px-4 d-flex align-items-center gap-2"
                    :disabled="saving || !addedStaff"
                  >
                    <span v-if="saving" class="spinner-border spinner-border-sm"></span>
                    <i v-else class="fa fa-save"></i>
                    {{ saving ? 'Saving...' : 'Save Attendance' }}
                  </button>
                </div>
              </div>
            </form>

          </div>
        </div>
      </div>
    </transition>

    <!-- Custom Time Picker -->
    <transition name="fade">
      <div v-if="showTimePicker" class="time-picker-overlay" @click.self="closeTimePicker">
        <div class="time-picker-modal">
          <div class="time-picker-title">Select time</div>

          <div class="picker-body">
            <div class="picker-col">
              <div
                v-for="hour in hours"
                :key="'h-' + hour"
                class="picker-item"
                :class="{ active: picker.hour === hour }"
                @click="picker.hour = hour"
              >
                {{ hour }}
              </div>
            </div>

            <div class="picker-separator">:</div>

            <div class="picker-col">
              <div
                v-for="minute in minutes"
                :key="'m-' + minute"
                class="picker-item"
                :class="{ active: picker.minute === minute }"
                @click="picker.minute = minute"
              >
                {{ minute }}
              </div>
            </div>

            <div class="picker-col ampm-col">
              <div
                v-for="period in periods"
                :key="'p-' + period"
                class="picker-item"
                :class="{ active: picker.period === period }"
                @click="picker.period = period"
              >
                {{ period }}
              </div>
            </div>
          </div>

          <div class="picker-actions">
            <button type="button" class="btn btn-light btn-sm px-3" @click="closeTimePicker">Cancel</button>
            <button type="button" class="btn btn-primary btn-sm px-3" @click="applyTimePicker">Save</button>
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
  name: "StaffAttendanceModal",

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
      saving: false,
      staffSearch: "",
      searchResults: [],
      showDropdown: false,
      selectedStaff: null,
      addedStaff: null,

      form: {
        staff_id: null,
        start_date: "",
        start_time: "",
        end_date: "",
        end_time: "",
      },

      showTimePicker: false,
      activeTimeField: "",

      picker: {
        hour: "01",
        minute: "00",
        period: "AM",
      },

      hours: ["01","02","03","04","05","06","07","08","09","10","11","12"],
      minutes: Array.from({ length: 60 }, (_, i) => String(i).padStart(2, "0")),
      periods: ["AM", "PM"],
    };
  },

  watch: {
    show(v) {
      document.body.style.overflow = v ? "hidden" : "";
      if (v) this.resetForm();
    },
  },

  beforeUnmount() {
    document.body.style.overflow = "";
  },

  methods: {
    emitClose() {
      this.$emit("close");
    },

      getEffectiveEndDate() {
    if (
      this.form.start_date &&
      this.form.end_date &&
      this.form.start_time &&
      this.form.end_time &&
      this.form.start_date === this.form.end_date &&
      this.form.end_time < this.form.start_time
    ) {
      const d = new Date(this.form.end_date);
      d.setDate(d.getDate() + 1);
      return d.toISOString().split("T")[0];
    }
    return this.form.end_date;
  },

    resetForm() {
      this.staffSearch = "";
      this.searchResults = [];
      this.showDropdown = false;
      this.selectedStaff = null;
      this.addedStaff = null;
      this.showTimePicker = false;
      this.activeTimeField = "";

      this.form = {
        staff_id: null,
        start_date: "",
        start_time: "",
        end_date: "",
        end_time: "",
      };

      this.picker = {
        hour: "01",
        minute: "00",
        period: "AM",
      };
    },

    async searchStaff() {
      const q = this.staffSearch.trim();
      if (!q) {
        this.searchResults = [];
        this.showDropdown = false;
        this.selectedStaff = null;
        return;
      }

      try {
        const res = await axios.get(`${this.url}staffs/search`, {
          params: { query: q },
        });
        this.searchResults = res.data;
        this.showDropdown = true;
        this.selectedStaff = null;
      } catch (e) {
        console.error(e);
      }
    },

    selectStaff(staff) {
      this.selectedStaff = staff;
      this.staffSearch = staff.name;
      this.showDropdown = false;
      this.searchResults = [];
    },

    addStaff() {
      if (!this.selectedStaff) return;

      this.addedStaff = this.selectedStaff;
      this.form.staff_id = this.selectedStaff.id;

      const now = new Date();
      const todayStr = now.toISOString().split("T")[0];
      const timeStr = `${String(now.getHours()).padStart(2, "0")}:${String(now.getMinutes()).padStart(2, "0")}`;

      this.form.start_date = todayStr;
      this.form.start_time = timeStr;
      this.form.end_date = todayStr;
      this.form.end_time = "";
    },

    openTimePicker(field) {
      this.activeTimeField = field;

      const existingTime = this.form[field];
      if (existingTime) {
        const parsed = this.parse24ToPicker(existingTime);
        this.picker.hour = parsed.hour;
        this.picker.minute = parsed.minute;
        this.picker.period = parsed.period;
      } else {
        this.picker.hour = "01";
        this.picker.minute = "00";
        this.picker.period = "AM";
      }

      this.showTimePicker = true;
    },

    closeTimePicker() {
      this.showTimePicker = false;
      this.activeTimeField = "";
    },

    applyTimePicker() {
      if (!this.activeTimeField) return;

      this.form[this.activeTimeField] = this.convertPickerTo24(
        this.picker.hour,
        this.picker.minute,
        this.picker.period
      );

      this.closeTimePicker();
    },

    parse24ToPicker(time24) {
      if (!time24) {
        return { hour: "01", minute: "00", period: "AM" };
      }

      let [hours, minutes] = time24.split(":");
      hours = parseInt(hours, 10);

      const period = hours >= 12 ? "PM" : "AM";
      let hour12 = hours % 12;
      if (hour12 === 0) hour12 = 12;

      return {
        hour: String(hour12).padStart(2, "0"),
        minute: minutes,
        period,
      };
    },

    convertPickerTo24(hour, minute, period) {
      let h = parseInt(hour, 10);

      if (period === "AM") {
        if (h === 12) h = 0;
      } else {
        if (h !== 12) h += 12;
      }

      return `${String(h).padStart(2, "0")}:${minute}`;
    },

    convertToAmPm(time24) {
      if (!time24) return "";

      let [hours, minutes] = time24.split(":");
      hours = parseInt(hours, 10);

      const ampm = hours >= 12 ? "PM" : "AM";
      let hour12 = hours % 12;
      if (hour12 === 0) hour12 = 12;

      return `${hour12}:${minutes} ${ampm}`;
    },

    combineDateTime(date, time) {
      if (!date || !time) return "";
      let [hours, minutes] = time.split(":");
      hours = parseInt(hours, 10);
      const period = hours >= 12 ? "PM" : "AM";
      let hour12 = hours % 12;
      if (hour12 === 0) hour12 = 12;
      const h = String(hour12).padStart(2, "0");
      return `${date} ${h}:${minutes} ${period}`;
    },

    formatDisplay(date, time) {
      if (!date || !time) return "";
      return `${date} ${this.convertToAmPm(time)}`;
    },

    isEndAfterStart() {
      const sd = this.form.start_date;
      const st = this.form.start_time;
      const ed = this.form.end_date;
      const et = this.form.end_time;

      const [sy, sm, sday] = sd.split("-").map(Number);
      const [sh, smin] = st.split(":").map(Number);

      const [ey, em, eday] = ed.split("-").map(Number);
      const [eh, emin] = et.split(":").map(Number);

      const startTotal = sy * 525600 + sm * 43800 + sday * 1440 + sh * 60 + smin;
      const endTotal   = ey * 525600 + em * 43800 + eday * 1440 + eh * 60 + emin;

      return endTotal > startTotal;
    },

async submit() {
  if (!this.addedStaff) return;

  if (
    !this.form.start_date ||
    !this.form.start_time ||
    !this.form.end_date ||
    !this.form.end_time
  ) {
    this.toast("Please fill in all date and time fields.", "error");
    return;
  }

  let startDate = this.form.start_date;
  let endDate = this.form.end_date;
  if (
    startDate === endDate &&
    this.form.end_time < this.form.start_time
  ) {
    const d = new Date(endDate);
    d.setDate(d.getDate() + 1);
    endDate = d.toISOString().split("T")[0];
  }

  const startDateTime = `${startDate} ${this.form.start_time}:00`;
  const endDateTime = `${endDate} ${this.form.end_time}:00`;

  this.saving = true;

  try {
    await axios.post(`${this.url}staff-attendance`, {
      staff_id: this.form.staff_id,
      start_datetime: startDateTime,
      end_datetime: endDateTime,
    });

  this.toast("Attendance saved successfully!", "success");
this.resetForm();
this.$emit("created");
this.emitClose();
  } catch (e) {
    const msg = e.response?.data?.message || "Something went wrong.";
    this.toast(msg, "error");
  } finally {
    this.saving = false;
  }
},

    toast(text, type = "success") {
      Toastify({
        text,
        duration: 3000,
        gravity: "top",
        position: "right",
        style: {
          background:
            type === "success"
              ? "linear-gradient(to right, #22c55e, #16a34a)"
              : "linear-gradient(to right, #ef4444, #dc2626)",
        },
      }).showToast();
    },
  },
};
</script>

<style scoped>
.xmask {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.45);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.xwrap {
  width: 100%;
  max-width: 1000px;
}

.xbox {
  background: #fff;
  border-radius: 18px;
  overflow: hidden;
}

.xbody {
  max-height: 70vh;
  overflow-y: auto;
}

.section-card {
  border: 1px solid #eef2f7;
  border-radius: 16px;
  padding: 18px;
  background: #fff;
}

.dt-card {
  min-height: 100%;
}

.req {
  color: #dc2626;
}

.head-icon {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  background: #ecfdf5;
  color: #16a34a;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-close-custom {
  border: 0;
  background: #f8fafc;
  width: 38px;
  height: 38px;
  border-radius: 10px;
}

.staff-dropdown {
  margin-top: 6px;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  background: #fff;
  max-height: 220px;
  overflow-y: auto;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
}

.staff-dropdown-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  cursor: pointer;
}

.staff-dropdown-item:hover {
  background: #f8fafc;
}

.staff-avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: #dbeafe;
  color: #1d4ed8;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
}

.staff-avatar.lg {
  width: 46px;
  height: 46px;
}

.selected-staff-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px;
  border-radius: 14px;
  background: #f8fafc;
  border: 1px solid #e5e7eb;
}

.dt-label {
  font-weight: 700;
  margin-bottom: 14px;
}

.datetime-preview {
  margin-top: 12px;
  padding: 10px 12px;
  border-radius: 12px;
  background: #f8fafc;
  font-weight: 600;
  font-size: 13px;
}

.time-picker-trigger {
  min-height: 44px;
  cursor: pointer;
  user-select: none;
  border-radius: 10px;
}

.time-picker-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.35);
  z-index: 10050;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
}

.time-picker-modal {
  width: 320px;
  background: #fff;
  border-radius: 18px;
  padding: 18px 16px 14px;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.18);
}

.time-picker-title {
  text-align: center;
  font-weight: 700;
  font-size: 16px;
  margin-bottom: 14px;
}

.picker-body {
  display: grid;
  grid-template-columns: 1fr 20px 1fr 1fr;
  gap: 8px;
  align-items: center;
}

.picker-col {
  height: 210px;
  overflow-y: auto;
  border-radius: 14px;
  background: #f8fafc;
  padding: 8px 0;
  scrollbar-width: none;
}

.picker-col::-webkit-scrollbar {
  display: none;
}

.picker-separator {
  text-align: center;
  font-size: 24px;
  font-weight: 700;
  color: #475569;
}

.picker-item {
  text-align: center;
  padding: 10px 8px;
  font-size: 18px;
  color: #94a3b8;
  cursor: pointer;
  border-radius: 10px;
  margin: 2px 6px;
  transition: all 0.2s ease;
}

.picker-item.active {
  background: #ffffff;
  color: #111827;
  font-weight: 700;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
}

.ampm-col .picker-item {
  font-size: 16px;
}

.picker-actions {
  display: flex;
  justify-content: space-between;
  margin-top: 16px;
}

.btn-submit {
  background: #16a34a;
  border-color: #16a34a;
  color: #fff;
}

.slide-fade-enter-active,
.slide-fade-leave-active,
.fade-enter-active,
.fade-leave-active {
  transition: all 0.2s ease;
}

.slide-fade-enter-from,
.slide-fade-leave-to,
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(8px);
}
</style>