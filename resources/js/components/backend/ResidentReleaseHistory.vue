<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
      <div class="col-12">
        <div class="card mb-4 shadow-sm border-0">
          
          <!-- Header -->
          <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3 bg-light-orange-gradient">
            <h5 class="card-title mb-0 fw-bold text-dark-orange">
              <i class="ti ti-history me-2"></i> Resident Checkout & Release History
            </h5>
          </div>

          <!-- Stats Cards -->
          <div class="card-body bg-light-gray-50 border-bottom py-3">
            <div class="row g-3">
              <div class="col-sm-6">
                <div class="d-flex align-items-center p-3 bg-white rounded shadow-sm hover-grow transition-all">
                  <div class="icon-avatar bg-info-light me-3">
                    <i class="ti ti-user-minus text-info fs-3"></i>
                  </div>
                  <div>
                    <h6 class="mb-0 text-muted fs-6">Checked-out Residents</h6>
                    <h4 class="mb-0 fw-bold text-dark mt-1">{{ pagination.total }}</h4>
                  </div>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="d-flex align-items-center p-3 bg-white rounded shadow-sm hover-grow transition-all">
                  <div class="icon-avatar bg-danger-light me-3">
                    <i class="ti ti-wallet text-danger fs-3"></i>
                  </div>
                  <div>
                    <h6 class="mb-0 text-muted fs-6">Total Outstanding Due</h6>
                    <h4 class="mb-0 fw-bold text-danger mt-1">৳ {{ totalOutstandingDue.toFixed(2) }}</h4>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Search -->
          <div class="card-header border-bottom py-3">
            <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between">
              <div>
                <h6 class="text-muted mb-0 fw-bold">All Historical Checkouts</h6>
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
                    <th class="text-center">Check-In Date</th>
                    <th class="text-center">Checkout Date</th>
                    <th class="text-center">Outstanding Due</th>
                    <th class="text-center">Action</th>
                  </tr>
                </thead>

                <tbody v-if="!loading && history.length">
                  <tr v-for="(r, idx) in history" :key="r.id" class="transition-all hover-row">
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
                    <td class="text-center text-muted fw-semibold">{{ formatDate(r.check_in) }}</td>
                    <td class="text-center text-muted fw-semibold">
                      {{ formatDateTime(r.today_check_out || r.check_out) }}
                    </td>
                    <td class="text-center">
                      <span v-if="r.due_amount > 0" class="badge bg-danger-light text-danger fw-bold border border-danger px-3 py-1 shadow-sm">
                        ৳ {{ r.due_amount.toFixed(2) }} (Due)
                      </span>
                      <span v-else class="badge bg-success-light text-success fw-bold border border-success px-3 py-1 shadow-sm">
                        ৳ 0.00 (No Due)
                      </span>
                    </td>
                    <td class="text-center">
                      <button 
                        class="btn btn-sm btn-outline-primary fw-bold"
                        title="Print Clearance Slip"
                        @click="printReleaseSlip(r)"
                      >
                        <i class="ti ti-printer me-1"></i> Print Slip
                      </button>
                    </td>
                  </tr>
                </tbody>

                <tbody v-else>
                  <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                      <div v-if="loading">
                        <div class="spinner-border spinner-border-sm text-warning me-2" role="status"></div>
                        <span>Loading release history...</span>
                      </div>
                      <div v-else class="py-4">
                        <i class="ti ti-folder-off fs-1 text-muted"></i>
                        <h6 class="mt-2 text-muted fw-semibold">No release history records found</h6>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div v-if="history.length && pagination.last_page > 1" class="pagination-footer border-top bg-light-50">
              <div class="pagination-info text-muted">
                Showing entries {{ (pagination.current_page - 1) * pagination.per_page + 1 }} to 
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
import Toastify from "toastify-js";
import "toastify-js/src/toastify.css";

export default {
  name: "ResidentReleaseHistory",
  computed: {
    url() {
      return this.$store.state.url;
    },
  },
  data() {
    return {
      history: [],
      loading: false,
      search: "",
      searchTimeout: null,
      totalOutstandingDue: 0,
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
          search: this.search,
        };

        const res = await axios.get(`${this.url}released-bookings`, { params });
        this.history = res.data.data || [];
        this.pagination = {
          total: res.data.total || 0,
          current_page: res.data.current_page || 1,
          last_page: res.data.last_page || 1,
          per_page: res.data.per_page || 10,
        };

        // Fetch overall outstanding due sum
        if (this.search === "") {
          this.calculateOutstandingDue();
        }

      } catch (e) {
        this.toast("Failed to load release history", "error");
      } finally {
        this.loading = false;
      }
    },

    async calculateOutstandingDue() {
      try {
        const res = await axios.get(`${this.url}released-bookings`, {
          params: { page: 1, per_page: 1000 }
        });
        const allData = res.data.data || [];
        this.totalOutstandingDue = allData.reduce((sum, item) => sum + Number(item.due_amount || 0), 0);
      } catch (e) {
        console.error("Error calculating dues:", e);
      }
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

    formatDate(d) {
      if (!d) return "-";
      const datePart = d.split("T")[0];
      const parts = datePart.split("-");
      return parts.length === 3 ? `${parts[2]}-${parts[1]}-${parts[0]}` : datePart;
    },

    formatDateTime(dt) {
      if (!dt) return "-";
      const [date, time] = String(dt).split("T");
      const parts = date.split("-");
      const formatted = parts.length === 3 ? `${parts[2]}-${parts[1]}-${parts[0]}` : date;
      return `${formatted} ${time ? time.substring(0, 5) : ""}`;
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

    printReleaseSlip(r) {
      const isNoticeFulfilled = r.will_leave === 1 && r.is_notice_fulfilled;
      const totalAdv = Number(r.total_advance_deposit || 0);
      const fineVal = Number(r.monthly_amount || 0) * 2;

      const uType = (r.user_type || 'student').toLowerCase().trim();
      let nameLabel = 'আবাসিকের নাম:';
      if (uType === 'student') {
        nameLabel = 'ছাত্রীর নাম:';
      } else if (uType === 'working professional' || uType === 'jobholder' || uType === 'job_holder') {
        nameLabel = 'চাকরিজীবীর নাম:';
      }

      let noticeText = isNoticeFulfilled
        ? `০২ মাসের অগ্রিম নোটিশ নিয়ম সফলভাবে সম্পন্ন হয়েছে (${r.notice_days_elapsed} দিন)`
        : (r.will_leave === 1 ? `নোটিশ দেওয়া হয়েছে ${r.notice_days_elapsed} দিন (৬০ দিন / ২ মাস পূর্ণ হয়নি)` : 'কোনো অগ্রিম নোটিশ দেওয়া হয়নি');

      let fineRowHtml = "";
      let advRowHtml = "";

      if (isNoticeFulfilled) {
        advRowHtml = `
          <tr>
            <th>এডভান্স জমা ফেরত:</th>
            <td class="text-success">৳ ${totalAdv.toLocaleString()} (মেস ছাড়ার কারণে ফেরত দেওয়া হলো)</td>
          </tr>
        `;
      } else {
        fineRowHtml = `
          <tr>
            <th>জরুরি মেস ছাড়ার জরিমানা:</th>
            <td class="text-danger">৳ ${fineVal.toLocaleString()} (২ মাসের ভাড়া জরিমানা)</td>
          </tr>
        `;

        if (totalAdv > 0) {
          const deductedAdv = Math.min(totalAdv, fineVal);
          advRowHtml = `
            <tr>
              <th>এডভান্স থেকে কর্তন:</th>
              <td class="text-danger">৳ ${deductedAdv.toLocaleString()} (জরিমানা হিসেবে কেটে নেওয়া হয়েছে)</td>
            </tr>
          `;
        }
      }

      const floornumber = (r.room_items || []).map(i => i.floornumber).filter(Boolean).join(', ') || r.floornumber || '-';
      const roomnumber = (r.room_items || []).map(i => i.roomnumber).filter(Boolean).join(', ') || r.roomnumber || r.room_number || '-';
      const checkoutDateStr = r.today_check_out || r.check_out || new Date().toISOString();

      const printDate = new Date().toLocaleDateString('bn-BD', {
        year: 'numeric', month: 'long', day: 'numeric'
      });

      const htmlContent = `
        <!DOCTYPE html>
        <html>
        <head>
          <title>Seat Release & Checkout Clearance - টি এস এস ভিলা</title>
          <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
          <style>
            body { font-family: 'Segoe UI', Arial, sans-serif; padding: 25px; background: #fff; color: #222; }
            .receipt-card { max-width: 720px; margin: auto; padding: 30px; border: 2px solid #033364; border-radius: 10px; }
            .header-title { color: #033364; font-weight: 800; }
            .table-details th { font-weight: 600; color: #444; width: 40%; }
            .table-details td { font-weight: 700; color: #111; }
            .badge-notice { background-color: ${isNoticeFulfilled ? '#198754' : '#dc3545'}; color: #fff; font-size: 13px; font-weight: 700; padding: 4px 12px; border-radius: 4px; }
            @media print {
              body { padding: 0; }
              .receipt-card { border: 2px solid #000 !important; max-width: 100%; border-radius: 0; padding: 20px; }
              .no-print { display: none !important; }
            }
          </style>
        </head>
        <body>
          <div class="receipt-card">
            <div class="d-flex justify-content-between align-items-center mb-3 no-print">
              <button class="btn btn-secondary btn-sm" onclick="window.close()">Close</button>
              <button class="btn btn-primary btn-sm px-4 fw-bold" onclick="window.print()"><i class="bi bi-printer"></i> Print Slip</button>
            </div>

            <div class="text-center border-bottom pb-3 mb-3">
              <h3 class="header-title mb-1">টি এস এস ভিলা</h3>
              <p class="text-muted mb-1 small">কলেজ রোড, নেসকো গেট সংলগ্ন, রংপুর</p>
              <span class="badge bg-primary text-white fs-6 px-3 py-1">মেস ত্যাগ ও সিট রিলিজ ছাড়পত্র (Seat Release Clearance Slip)</span>
            </div>

            <table class="table table-bordered align-middle table-details mb-3">
              <tbody>
                <tr>
                  <th>${nameLabel}</th>
                  <td>${r.full_name} (${r.user_type || 'student'})</td>
                </tr>
                <tr>
                  <th>যোগাযোগ (ফোন):</th>
                  <td>${r.phone || '-'}</td>
                </tr>
                <tr>
                  <th>ফ্লোর / রুম / সিট নং:</th>
                  <td>${floornumber} / ${roomnumber}</td>
                </tr>
                <tr>
                  <th>মেসে যোগদানের তারিখ (Check-in):</th>
                  <td>${this.formatDate(r.check_in)}</td>
                </tr>
                <tr>
                  <th>মেস ত্যাগের তারিখ (Check-out):</th>
                  <td>${this.formatDate(checkoutDateStr)}</td>
                </tr>
                <tr>
                  <th>০২ মাসের নোটিশ অবস্থা:</th>
                  <td><span class="badge-notice">${noticeText}</span></td>
                </tr>
                <tr>
                  <th>মাসিক রুম ভাড়া:</th>
                  <td>৳ ${Number(r.monthly_amount || 0).toLocaleString()}</td>
                </tr>
                ${fineRowHtml}
                ${advRowHtml}
              </tbody>
            </table>

            <div class="d-flex justify-content-between align-items-end mt-5 pt-4 border-top">
              <div class="text-center" style="width: 200px;">
                <div class="border-top border-dark pt-1 fw-bold small">রেসিডেন্টের স্বাক্ষর</div>
              </div>
              <div class="text-center" style="width: 200px;">
                <div class="border-top border-dark pt-1 fw-bold small">মেস কর্তৃপক্ষের স্বাক্ষর</div>
              </div>
            </div>

            <div class="text-center text-muted small mt-4 pt-2 border-top">
              প্রিন্টের সময়: ${printDate} | টি এস এস ভিলা মেস ম্যানেজমেন্ট সিস্টেম
            </div>
          </div>
        </body>
        </html>
      `;

      let iframe = document.getElementById("release-history-print-iframe");
      if (!iframe) {
        iframe = document.createElement("iframe");
        iframe.id = "release-history-print-iframe";
        iframe.style.position = "absolute";
        iframe.style.width = "0px";
        iframe.style.height = "0px";
        iframe.style.border = "none";
        document.body.appendChild(iframe);
      }

      const doc = iframe.contentWindow.document;
      doc.open();
      doc.write(htmlContent);
      doc.close();

      setTimeout(() => {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
      }, 500);
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
.bg-danger-light {
  background-color: rgba(220, 53, 69, 0.15);
}
.bg-success-light {
  background-color: rgba(25, 135, 84, 0.15);
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
.hover-row {
  cursor: default;
}
.hover-row:hover {
  background-color: #fffaf0 !important;
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
