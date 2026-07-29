<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
      <div class="col-12">
        <div class="card mb-4 shadow-sm border-0">
          
          <!-- Header -->
          <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3 bg-light-orange-gradient">
            <h5 class="card-title mb-0 fw-bold text-dark-orange">
              <i class="ti ti-door-exit me-2"></i> Resident Seat Release & Checkout
            </h5>
          </div>

          <!-- Stats Cards -->
          <div class="card-body bg-light-gray-50 border-bottom py-3">
            <div class="row g-3">
              <div class="col-sm-6 col-md-4">
                <div class="d-flex align-items-center p-3 bg-white rounded shadow-sm hover-grow transition-all">
                  <div class="icon-avatar bg-info-light me-3">
                    <i class="ti ti-users text-info fs-3"></i>
                  </div>
                  <div>
                    <h6 class="mb-0 text-muted fs-6">Active Residents</h6>
                    <h4 class="mb-0 fw-bold text-dark mt-1">{{ totalActive }}</h4>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-md-4">
                <div class="d-flex align-items-center p-3 bg-white rounded shadow-sm hover-grow transition-all">
                  <div class="icon-avatar bg-success-light me-3">
                    <i class="ti ti-home-check text-success fs-3"></i>
                  </div>
                  <div>
                    <h6 class="mb-0 text-muted fs-6">Staying</h6>
                    <h4 class="mb-0 fw-bold text-dark mt-1">{{ stayingCount }}</h4>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-md-4">
                <div class="d-flex align-items-center p-3 bg-white rounded shadow-sm hover-grow transition-all">
                  <div class="icon-avatar bg-warning-light me-3">
                    <i class="ti ti-calendar-off text-warning fs-3"></i>
                  </div>
                  <div>
                    <h6 class="mb-0 text-muted fs-6">2 Months Notice Given</h6>
                    <h4 class="mb-0 fw-bold text-dark mt-1">{{ leavingSoonCount }}</h4>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Filters & Search -->
          <div class="card-header border-bottom py-3">
            <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between">
              
              <!-- Tab Filters -->
              <div class="d-flex gap-2">
                <button 
                  class="btn btn-sm rounded-pill transition-all" 
                  :class="filter === 'all' ? 'btn-warning text-white fw-bold px-3 shadow-sm' : 'btn-outline-warning text-dark px-3'"
                  @click="changeFilter('all')"
                >
                  All Active
                </button>
                <button 
                  class="btn btn-sm rounded-pill transition-all" 
                  :class="filter === 'staying' ? 'btn-success text-white fw-bold px-3 shadow-sm' : 'btn-outline-success text-dark px-3'"
                  @click="changeFilter('staying')"
                >
                  Staying
                </button>
                <button 
                  class="btn btn-sm rounded-pill transition-all" 
                  :class="filter === 'leaving' ? 'btn-danger text-white fw-bold px-3 shadow-sm' : 'btn-outline-danger text-dark px-3'"
                  @click="changeFilter('leaving')"
                >
                  2 Months Notice Given
                </button>
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
                    <th class="text-center">Booking Date</th>
                    <th class="text-center">Notice Status</th>
                    <th class="text-center" style="width: 300px;">Actions</th>
                  </tr>
                </thead>

                <tbody v-if="!loading && residents.length">
                  <tr v-for="(r, idx) in residents" :key="r.id" class="transition-all hover-row">
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
                            {{ (item.roomnumber || '').split('-').slice(1).join('-') || '-' }}
                          </span>
                        </div>
                      </div>
                      <span v-else class="text-muted">-</span>
                    </td>
                    <td class="text-center text-muted fw-semibold">{{ formatDate(r.created_at) }}</td>
                    <td class="text-center">
                      <template v-if="r.will_leave === 1">
                        <span v-if="r.is_notice_fulfilled" class="badge bg-success text-white fw-bold border-0 px-3 py-1 shadow-sm">
                          <i class="ti ti-calendar-check me-1"></i> Notice Fulfilled ({{ r.notice_days_elapsed }} Days)
                        </span>
                        <span v-else class="badge bg-warning text-white fw-bold border-0 px-3 py-1 shadow-sm">
                          <i class="ti ti-clock me-1"></i> 2 Months Notice ({{ r.notice_days_elapsed }} Days Ago)
                        </span>
                      </template>
                      <span v-else class="badge bg-secondary text-white fw-bold border-0 px-3 py-1 shadow-sm">
                        <i class="ti ti-circle-check me-1"></i> Staying (No Notice)
                      </span>
                    </td>
                    <td class="text-center">
                      <div class="d-flex gap-2 justify-content-center">
                        <!-- If staying -->
                        <template v-if="r.will_leave !== 1">
                          <button 
                            class="btn btn-outline-warning btn-sm hover-orange fw-semibold px-2 py-1"
                            @click="confirmScheduleLeave(r)"
                            title="Schedule 2 months advance notice"
                          >
                            <i class="ti ti-calendar-off me-1"></i> Give 2 Months Notice
                          </button>
                          <button 
                            class="btn btn-outline-danger btn-sm hover-red fw-semibold px-2 py-1"
                            @click="confirmInstantRelease(r)"
                            title="Release seat immediately"
                          >
                            <i class="ti ti-door-exit me-1"></i> Instant Release
                          </button>
                        </template>

                        <!-- If scheduled to leave -->
                        <template v-else>
                          <button 
                            class="btn btn-outline-success btn-sm hover-green fw-semibold px-2 py-1"
                            @click="confirmCancelLeave(r)"
                            title="Cancel leave notice"
                          >
                            <i class="ti ti-refresh me-1"></i> Cancel Notice
                          </button>
                          <button 
                            class="btn btn-danger btn-sm fw-semibold px-2 py-1 shadow-sm"
                            @click="confirmInstantRelease(r, true)"
                            title="Confirm checkout and release seat"
                          >
                            <i class="ti ti-circle-check-filled me-1"></i> Confirm Checkout
                          </button>
                        </template>
                      </div>
                    </td>
                  </tr>
                </tbody>

                <tbody v-else>
                  <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                      <div v-if="loading">
                        <div class="spinner-border spinner-border-sm text-warning me-2" role="status"></div>
                        <span>Loading active residents...</span>
                      </div>
                      <div v-else class="py-4">
                        <i class="ti ti-folder-off fs-1 text-muted"></i>
                        <h6 class="mt-2 text-muted fw-semibold">No active residents found</h6>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div v-if="residents.length && pagination.last_page > 1" class="pagination-footer border-top bg-light-50">
              <div class="pagination-info text-muted">
                Showing residents {{ (pagination.current_page - 1) * pagination.per_page + 1 }} to 
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
  </div>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import Toastify from "toastify-js";
import "toastify-js/src/toastify.css";

export default {
  name: "ResidentReleaseManager",
  computed: {
    url() {
      return this.$store.state.url;
    },
  },
  data() {
    return {
      residents: [],
      loading: false,
      filter: "all",
      search: "",
      searchTimeout: null,
      totalActive: 0,
      stayingCount: 0,
      leavingSoonCount: 0,
      pagination: {
        total: 0,
        current_page: 1,
        last_page: 1,
        per_page: 10,
      },
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
          filter: this.filter,
          search: this.search,
        };

        const res = await axios.get(`${this.url}active-bookings`, { params });
        this.residents = res.data.data || [];
        this.pagination = {
          total: res.data.total || 0,
          current_page: res.data.current_page || 1,
          last_page: res.data.last_page || 1,
          per_page: res.data.per_page || 10,
        };

        // If it is the first page of "all" filter, we can calculate stats
        if (this.filter === "all" && this.search === "") {
          this.totalActive = this.pagination.total;
          this.stayingCount = this.residents.filter(r => r.will_leave === 0).length; // simple client side count approximation
          this.leavingSoonCount = this.residents.filter(r => r.will_leave === 1).length;
        } else {
          // fetch counts if not full
          this.fetchCounts();
        }

      } catch (e) {
        this.toast("Failed to load active residents", "error");
      } finally {
        this.loading = false;
      }
    },

    async fetchCounts() {
      try {
        const res = await axios.get(`${this.url}active-bookings`, {
          params: { page: 1, per_page: 1000, filter: "all" }
        });
        const allData = res.data.data || [];
        this.totalActive = allData.length;
        this.stayingCount = allData.filter(r => r.will_leave === 0).length;
        this.leavingSoonCount = allData.filter(r => r.will_leave === 1).length;
      } catch (e) {
        console.error("Error fetching stats:", e);
      }
    },

    changeFilter(newFilter) {
      this.filter = newFilter;
      this.fetchData(1);
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

    // Confirm Schedule 2 Months Leave Notice
    confirmScheduleLeave(r) {
      Swal.fire({
        title: "Give 2 Months Leave Notice?",
        text: `Are you sure you want to record a 2-month advance leave notice for ${r.full_name}?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#f59e0b",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Yes, Record 2 Months Notice",
        cancelButtonText: "Cancel"
      }).then(async (result) => {
        if (result.isConfirmed) {
          try {
            const res = await axios.post(`${this.url}bookings/${r.id}/schedule-leave`);
            if (res.data.success) {
              this.toast(res.data.message || "Notice recorded successfully!", "success");
              this.fetchData(this.pagination.current_page);
            } else {
              this.toast(res.data.message || "Action failed", "error");
            }
          } catch (e) {
            this.toast("Server error occurred.", "error");
          }
        }
      });
    },

    // Confirm Cancel Leave Notice
    confirmCancelLeave(r) {
      Swal.fire({
        title: "Cancel 2 Months Leave Notice",
        text: `Are you sure you want to cancel the leave notice for ${r.full_name}?`,
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#198754",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Yes, Cancel Notice",
        cancelButtonText: "No, Keep Notice"
      }).then(async (result) => {
        if (result.isConfirmed) {
          try {
            const res = await axios.post(`${this.url}bookings/${r.id}/cancel-leave`);
            if (res.data.success) {
              this.toast(res.data.message || "Notice cancelled successfully!", "success");
              this.fetchData(this.pagination.current_page);
            } else {
              this.toast(res.data.message || "Action failed", "error");
            }
          } catch (e) {
            this.toast("Server error occurred.", "error");
          }
        }
      });
    },

    // Confirm Instant Release or Checkout
    confirmInstantRelease(r, isScheduledCheckout = false) {
      const isNoticeFulfilled = r.will_leave === 1 && r.is_notice_fulfilled;
      const totalAdv = Number(r.total_advance_deposit || 0);
      const fineVal = Number(r.monthly_amount || 0) * 2;
      const remainingAdvAfterFine = Math.max(0, totalAdv - fineVal);
      const netDueAfterAdv = fineVal > totalAdv ? fineVal - totalAdv : 0;

      let title = "Confirm Release & Checkout";
      let htmlContent = "";
      let iconType = "warning";

      if (!isNoticeFulfilled) {
        iconType = "error";
        title = "⚠️ 2 Months Notice Requirement Not Met!";
        const noticeStatusText = r.will_leave === 1 
          ? `নোটিশ দেওয়ার বয়স ${r.notice_days_elapsed} দিন (৬০ দিন / ২ মাস পূর্ণ হয়নি)` 
          : "কোনো অগ্রিম নোটিশ দেওয়া হয়নি";

        let advAdjustmentSummary = "";
        if (totalAdv > 0) {
          if (totalAdv >= fineVal) {
            const remainingText = remainingAdvAfterFine > 0 ? ` (অবশিষ্ট এডভান্স জমা থাকবে: ৳ ${remainingAdvAfterFine.toLocaleString()})` : '';
            advAdjustmentSummary = `<li class="text-warning mt-1"><strong>এডভান্স থেকে কেটে নেওয়া হবে:</strong> ৳ ${fineVal.toLocaleString()}${remainingText}</li>`;
          } else {
            advAdjustmentSummary = `<li class="text-danger mt-1"><strong>এডভান্স থেকে কেটে নেওয়া হবে:</strong> ৳ ${totalAdv.toLocaleString()} (সম্পূর্ণ কেটে নেওয়া হবে)</li>
            <li class="text-danger"><strong>অবশিষ্ট ২ মাসের জরিমানা বকেয়া:</strong> ৳ ${netDueAfterAdv.toLocaleString()}</li>`;
          }
        } else {
          advAdjustmentSummary = `<li class="text-muted mt-1"><strong>এডভান্স জমা:</strong> ৳ 0 (কোনো এডভান্স জমা নেই)</li>`;
        }

        htmlContent = `
          <div class="text-start fs-6 p-3 rounded" style="background-color: #fff5f5; border: 1px solid #feb2b2;">
            <p class="text-danger fw-bold mb-2">মেস ছাড়ার ০২ মাস পূর্বে মেস কর্তৃপক্ষকে জানানো হয়নি!</p>
            <ul class="mb-2 text-dark ps-3" style="font-size: 14px; line-height: 1.6;">
              <li><strong>আবাসিকের নাম:</strong> ${r.full_name}</li>
              <li><strong>নোটিশ অবস্থা:</strong> ${noticeStatusText}</li>
              <li><strong>বর্তমান এডভান্স জমা:</strong> ৳ ${totalAdv.toLocaleString()}</li>
              <li class="text-danger"><strong>জরুরি মেস ছাড়ার জরিমানা (২ মাসের ভাড়া):</strong> ৳ ${fineVal.toLocaleString()}</li>
              ${advAdjustmentSummary}
            </ul>
            <p class="text-muted mb-0 small">কনফার্ম করলে এডভান্স (advance_price) থেকে জরিমানা কেটে নিয়ে সিট রিলিজ ও চেকআউট সম্পন্ন করা হবে। আপনি কি রাজি আছেন?</p>
          </div>
        `;
      } else {
        iconType = "success";
        title = "Confirm Seat Release & Checkout";
        htmlContent = `
          <div class="text-start fs-6 p-3 rounded" style="background-color: #f0fff4; border: 1px solid #9ae6b4;">
            <p class="text-success fw-bold mb-2">০২ মাসের অগ্রিম নোটিশ নিয়ম সফলভাবে সম্পন্ন হয়েছে (${r.notice_days_elapsed} দিন)।</p>
            <ul class="mb-2 text-dark ps-3" style="font-size: 14px; line-height: 1.6;">
              <li><strong>আবাসিকের নাম:</strong> ${r.full_name}</li>
              <li class="text-success"><strong>বাড়তি জরিমানা:</strong> কোনো জরিমানা প্রযোজ্য নয়</li>
              <li class="text-primary"><strong>এডভান্স জমা ফেরত দেওয়া হবে:</strong> ৳ ${totalAdv.toLocaleString()}</li>
            </ul>
            <p class="text-muted mb-0 small">এডভান্স জমা টাকা ফেরত প্রদান সাপেক্ষে সিট রিলিজ ও চেকআউট সম্পন্ন করা হবে। আপনি কি কনফার্ম করতে চান?</p>
          </div>
        `;
      }

      Swal.fire({
        title: title,
        html: htmlContent,
        icon: iconType,
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#6c757d",
        confirmButtonText: isNoticeFulfilled ? "Yes, Confirm Checkout" : "Yes, Deduct Advance & Checkout",
        cancelButtonText: "Cancel"
      }).then(async (result) => {
        if (result.isConfirmed) {
          try {
            const res = await axios.post(`${this.url}bookings/${r.id}/instant-release`);
            if (res.data.success) {
              this.toast(res.data.message || "Checkout completed successfully!", "success");
              this.fetchData(this.pagination.current_page);
            } else {
              this.toast(res.data.message || "Action failed", "error");
            }
          } catch (e) {
            this.toast("Server error occurred.", "error");
          }
        }
      });
    },

    formatDate(d) {
      if (!d) return "-";
      const datePart = d.split("T")[0];
      const parts = datePart.split("-");
      return parts.length === 3 ? `${parts[2]}-${parts[1]}-${parts[0]}` : datePart;
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
.bg-success-light {
  background-color: rgba(25, 135, 84, 0.15);
}
.bg-warning-light {
  background-color: rgba(245, 158, 11, 0.15);
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
.animated-badge {
  animation: pulse 2s infinite;
}
@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4);
  }
  70% {
    box-shadow: 0 0 0 6px rgba(245, 158, 11, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(245, 158, 11, 0);
  }
}
.hover-row {
  cursor: default;
}
.hover-row:hover {
  background-color: #fffaf0 !important;
}
.hover-orange:hover {
  background-color: #ff9800 !important;
  color: #fff !important;
  border-color: #ff9800 !important;
}
.hover-red:hover {
  background-color: #dc3545 !important;
  color: #fff !important;
  border-color: #dc3545 !important;
}
.hover-green:hover {
  background-color: #198754 !important;
  color: #fff !important;
  border-color: #198754 !important;
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
