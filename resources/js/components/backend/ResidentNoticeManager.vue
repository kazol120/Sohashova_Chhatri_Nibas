<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
      <div class="col-12">
        <div class="card mb-4 shadow-sm border-0">
          <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center py-3 border-bottom">
            <div class="d-flex align-items-center gap-2">
              <div class="avatar avatar-sm bg-label-primary rounded p-2 d-flex align-items-center justify-content-center">
                <i class="ti ti-bell-ringing fs-4"></i>
              </div>
              <div>
                <h5 class="card-title mb-0 fw-bold">জরুরি বিজ্ঞপ্তি ব্যবস্থাপনা (Notice Board)</h5>
                <small class="text-muted">বর্ডারদের জন্য জরুরি নোটিশ তৈরি, সম্পাদন ও প্রকাশনা নিয়ন্ত্রণ করুন</small>
              </div>
            </div>
            <button class="btn btn-primary shadow-sm" type="button" @click="openCreateModal">
              <i class="ti ti-plus me-1"></i> নতুন বিজ্ঞপ্তি যোগ করুন
            </button>
          </div>

          <div class="card-body pt-4">
            <!-- Filter & Search Controls -->
            <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-4">
              <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center gap-2">
                  <label class="small text-muted mb-0">প্রতি পেজে:</label>
                  <select class="form-select form-select-sm" style="width:80px" v-model.number="perPage" @change="fetchNotices(1)">
                    <option :value="5">৫</option>
                    <option :value="10">১০</option>
                    <option :value="20">২০</option>
                    <option :value="50">৫০</option>
                  </select>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <label class="small text-muted mb-0">স্ট্যাটাস:</label>
                  <select class="form-select form-select-sm" style="width:130px" v-model="statusFilter" @change="fetchNotices(1)">
                    <option value="">সকল স্ট্যাটাস</option>
                    <option value="1">সক্রিয় (Active)</option>
                    <option value="0">নিষ্ক্রিয় (Inactive)</option>
                  </select>
                </div>
              </div>

              <div class="d-flex gap-2 align-items-center">
                <div class="input-group input-group-sm" style="width: 280px;">
                  <span class="input-group-text bg-light border-end-0"><i class="ti ti-search text-muted"></i></span>
                  <input
                    type="text"
                    class="form-control border-start-0"
                    placeholder="বিজ্ঞপ্তি খুঁজুন..."
                    v-model="search"
                    @input="debounceSearch"
                  />
                  <button v-if="search" class="btn btn-outline-secondary" type="button" @click="clearSearch">
                    <i class="ti ti-x"></i>
                  </button>
                </div>
              </div>
            </div>

            <!-- Table -->
            <div class="table-responsive rounded border">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th style="width:60px" class="text-center">#</th>
                    <th style="min-width:180px">শিরোনাম (Title)</th>
                    <th style="min-width:320px">বিজ্ঞপ্তির বিবরণ (Notice Details)</th>
                    <th style="width:130px" class="text-center">গুরুত্ব (Priority)</th>
                    <th style="width:120px" class="text-center">স্ট্যাটাস</th>
                    <th style="width:160px">প্রকাশের তারিখ</th>
                    <th style="width:130px" class="text-center">অ্যাকশন</th>
                  </tr>
                </thead>

                <tbody v-if="loading">
                  <tr>
                    <td colspan="7" class="text-center py-5">
                      <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                      </div>
                      <div class="text-muted mt-2 small">বিজ্ঞপ্তি লোড হচ্ছে...</div>
                    </td>
                  </tr>
                </tbody>

                <tbody v-else-if="notices.length">
                  <tr v-for="(n, idx) in notices" :key="n.id">
                    <td class="text-center fw-semibold text-muted">{{ from + idx }}</td>
                    <td>
                      <div class="fw-bold text-dark">{{ n.title || 'জরুরি বিজ্ঞপ্তি' }}</div>
                      <span v-if="idx === 0 && pagination.current_page === 1" class="badge bg-label-info rounded-pill small mt-1">
                        <i class="ti ti-sparkles me-1"></i> সর্বশেষ বিজ্ঞপ্তি
                      </span>
                    </td>
                    <td style="min-width: 320px;">
                      <div class="notice-full-text text-dark" style="white-space: pre-line; word-break: break-word; line-height: 1.5; font-size: 0.92rem;">
                        {{ n.notice }}
                      </div>
                    </td>
                    <td class="text-center">
                      <span v-if="n.priority === 'urgent'" class="badge bg-danger shadow-xs">
                        <i class="ti ti-alert-triangle me-1"></i> অতি জরুরি
                      </span>
                      <span v-else-if="n.priority === 'important'" class="badge bg-warning text-dark">
                        <i class="ti ti-alert-circle me-1"></i> গুরুত্বপূর্ণ
                      </span>
                      <span v-else class="badge bg-label-secondary">
                        <i class="ti ti-info-circle me-1"></i> সাধারণ
                      </span>
                    </td>
                    <td class="text-center">
                      <div class="form-check form-switch d-inline-flex justify-content-center m-0">
                        <input
                          class="form-check-input cursor-pointer"
                          type="checkbox"
                          :checked="n.status == 1"
                          @change="toggleNoticeStatus(n)"
                          title="সক্রিয় / নিষ্ক্রিয় করুন"
                        />
                      </div>
                    </td>
                    <td class="small text-muted">
                      <div><i class="ti ti-calendar me-1"></i> {{ formatDate(n.created_at) }}</div>
                    </td>
                    <td class="text-center">
                      <div class="d-inline-flex gap-1">
                        <button
                          class="btn btn-sm btn-icon btn-label-primary shadow-xs"
                          @click="openEditModal(n)"
                          title="এডিট করুন"
                        >
                          <i class="ti ti-edit"></i>
                        </button>
                        <button
                          class="btn btn-sm btn-icon btn-label-danger shadow-xs"
                          @click="confirmDelete(n)"
                          title="মুছে ফেলুন"
                        >
                          <i class="ti ti-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>

                <tbody v-else>
                  <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                      <i class="ti ti-bell-off fs-1 text-muted opacity-50 d-block mb-2"></i>
                      <span>কোনো জরুরি বিজ্ঞপ্তি পাওয়া যায়নি</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div v-if="pagination.total > 0" class="d-flex flex-wrap justify-content-between align-items-center mt-3 pt-2">
              <div class="small text-muted">
                সর্বমোট <strong>{{ pagination.total }}</strong> টির মধ্যে <strong>{{ from }} - {{ to }}</strong> টি দেখাচ্ছে
              </div>
              <ul class="pagination pagination-sm mb-0">
                <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                  <a class="page-link" href="javascript:void(0)" @click="fetchNotices(pagination.current_page - 1)">
                    <i class="ti ti-chevron-left"></i>
                  </a>
                </li>
                <li
                  v-for="page in visiblePages"
                  :key="page"
                  class="page-item"
                  :class="{ active: pagination.current_page === page, disabled: page === '...' }"
                >
                  <a class="page-link" href="javascript:void(0)" @click="page !== '...' && fetchNotices(page)">
                    {{ page }}
                  </a>
                </li>
                <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                  <a class="page-link" href="javascript:void(0)" @click="fetchNotices(pagination.current_page + 1)">
                    <i class="ti ti-chevron-right"></i>
                  </a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create / Edit Modal -->
    <div
      class="modal fade"
      :class="{ 'show d-block': showModal }"
      tabindex="-1"
      style="background: rgba(0,0,0,0.5);"
      v-if="showModal"
      @click.self="closeModal"
    >
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-header bg-light py-3 border-bottom">
            <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
              <i class="ti" :class="isEdit ? 'ti-edit text-primary' : 'ti-plus text-success'"></i>
              <span>{{ isEdit ? 'বিজ্ঞপ্তি সম্পাদনা করুন' : 'নতুন জরুরি বিজ্ঞপ্তি তৈরি করুন' }}</span>
            </h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>

          <form @submit.prevent="submitForm">
            <div class="modal-body p-4">
              <div class="row g-3">
                <!-- Title -->
                <div class="col-md-8">
                  <label class="form-label fw-semibold">বিজ্ঞপ্তির শিরোনাম (Title) <span class="text-danger">*</span></label>
                  <input
                    type="text"
                    class="form-control"
                    placeholder="যেমন: জরুরি বিজ্ঞপ্তি / মেস বিল সংক্রান্ত..."
                    v-model="form.title"
                    required
                  />
                  <small v-if="errors.title" class="text-danger d-block mt-1">{{ errors.title[0] }}</small>
                </div>

                <!-- Priority -->
                <div class="col-md-4">
                  <label class="form-label fw-semibold">গুরুত্ব (Priority)</label>
                  <select class="form-select" v-model="form.priority">
                    <option value="urgent">🔴 অতি জরুরি (Urgent)</option>
                    <option value="important">🟡 গুরুত্বপূর্ণ (Important)</option>
                    <option value="normal">🔵 সাধারণ (Normal)</option>
                  </select>
                </div>

                <!-- Notice Text -->
                <div class="col-12">
                  <label class="form-label fw-semibold">বিজ্ঞপ্তির পূর্ণাঙ্গ বিবরণ (Notice Content) <span class="text-danger">*</span></label>
                  <textarea
                    class="form-control"
                    rows="6"
                    placeholder="বিজ্ঞপ্তির বিস্তারিত বিবরণ এখানে লিখুন..."
                    v-model="form.notice"
                    required
                  ></textarea>
                  <small v-if="errors.notice" class="text-danger d-block mt-1">{{ errors.notice[0] }}</small>
                </div>

                <!-- Status & Email Notification -->
                <div class="col-12">
                  <div class="form-check form-switch mt-2">
                    <input
                      class="form-check-input"
                      type="checkbox"
                      id="statusSwitch"
                      v-model="form.status"
                      :true-value="1"
                      :false-value="0"
                    />
                    <label class="form-check-label fw-semibold" for="statusSwitch">
                      সরাসরি ফ্রন্টএন্ডে বর্ডারদের জন্য প্রকাশ করুন (Active Status)
                    </label>
                  </div>
                  <small class="text-muted d-block ms-4">সক্রিয় রাখলে ফ্রন্টএন্ডে বর্ডারদের জন্য নতুন বিজ্ঞপ্তির স্মার্ট অ্যালার্ট প্রদর্শিত হবে।</small>
                </div>

                <div class="col-12" v-if="!isEdit">
                  <div class="form-check form-switch mt-1">
                    <input
                      class="form-check-input"
                      type="checkbox"
                      id="emailSwitch"
                      v-model="form.send_email"
                      :true-value="true"
                      :false-value="false"
                    />
                    <label class="form-check-label fw-semibold text-primary" for="emailSwitch">
                      <i class="ti ti-mail me-1"></i> সকল সক্রিয় শিক্ষার্থীর ইমেইলে নোটিফিকেশন পাঠান (Send Email Alert)
                    </label>
                  </div>
                  <small class="text-muted d-block ms-4">বিজ্ঞপ্তি প্রকাশের সাথে সাথে সকল বর্ডারের ইমেইলে স্বয়ংক্রিয় নোটিফিকেশন পৌঁছে যাবে।</small>
                </div>
              </div>
            </div>

            <div class="modal-footer bg-light py-3 border-top">
              <button type="button" class="btn btn-outline-secondary px-4" @click="closeModal">বাতিল</button>
              <button type="submit" class="btn btn-primary px-4 shadow-sm" :disabled="saving">
                <span v-if="saving"><i class="fa fa-spinner fa-spin me-1"></i> সংরক্ষণ হচ্ছে...</span>
                <span v-else><i class="ti ti-device-floppy me-1"></i> {{ isEdit ? 'আপডেট করুন' : 'সংরক্ষণ করুন' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import Swal from 'sweetalert2';
import Toastify from 'toastify-js';
import 'toastify-js/src/toastify.css';

export default {
  name: 'ResidentNoticeManager',

  data() {
    return {
      notices: [],
      loading: false,
      saving: false,
      search: '',
      statusFilter: '',
      perPage: 10,
      searchTimeout: null,

      pagination: {
        current_page: 1,
        last_page: 1,
        total: 0,
        per_page: 10,
      },
      from: 0,
      to: 0,

      showModal: false,
      isEdit: false,
      editId: null,

      form: {
        title: 'জরুরি বিজ্ঞপ্তি',
        notice: '',
        priority: 'normal',
        status: 1,
        send_email: true,
      },
      errors: {},
    };
  },

  computed: {
    visiblePages() {
      const current = this.pagination.current_page;
      const last = this.pagination.last_page;
      const delta = 2;
      const range = [];

      for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
        range.push(i);
      }

      if (current - delta > 2) {
        range.unshift('...');
      }
      if (current + delta < last - 1) {
        range.push('...');
      }

      range.unshift(1);
      if (last > 1) {
        range.push(last);
      }

      return range;
    },
  },

  mounted() {
    this.fetchNotices(1);
  },

  methods: {
    toast(msg, type = 'success') {
      Toastify({
        text: msg,
        duration: 3000,
        close: true,
        gravity: 'top',
        position: 'right',
        style: {
          background: type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6',
          borderRadius: '8px',
          fontWeight: '500',
        },
      }).showToast();
    },

    debounceSearch() {
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        this.fetchNotices(1);
      }, 350);
    },

    clearSearch() {
      this.search = '';
      this.fetchNotices(1);
    },

    async fetchNotices(page = 1) {
      this.loading = true;
      try {
        const res = await axios.get('/resident-notices-get', {
          params: {
            page: page,
            per_page: this.perPage,
            search: this.search,
            status: this.statusFilter,
          },
        });

        const data = res.data;
        this.notices = data.data || [];
        this.pagination.current_page = data.current_page || 1;
        this.pagination.last_page = data.last_page || 1;
        this.pagination.total = data.total || 0;
        this.pagination.per_page = data.per_page || this.perPage;
        this.from = data.from || 0;
        this.to = data.to || 0;
      } catch (err) {
        console.error(err);
        this.toast('বিজ্ঞপ্তি লোড করতে সমস্যা হয়েছে', 'error');
      } finally {
        this.loading = false;
      }
    },

    openCreateModal() {
      this.isEdit = false;
      this.editId = null;
      this.form = {
        title: 'জরুরি বিজ্ঞপ্তি',
        notice: '',
        priority: 'normal',
        status: 1,
        send_email: true,
      };
      this.errors = {};
      this.showModal = true;
    },

    openEditModal(item) {
      this.isEdit = true;
      this.editId = item.id;
      this.form = {
        title: item.title,
        notice: item.notice,
        priority: item.priority || 'normal',
        status: item.status !== undefined ? item.status : 1,
      };
      this.errors = {};
      this.showModal = true;
    },

    closeModal() {
      this.showModal = false;
      this.errors = {};
    },

    async submitForm() {
      this.saving = true;
      this.errors = {};

      try {
        let res;
        if (this.isEdit) {
          res = await axios.post(`/resident-notices-update/${this.editId}`, this.form);
        } else {
          res = await axios.post('/resident-notices-store', this.form);
        }

        if (res.data.success) {
          this.toast(res.data.message || 'বিজ্ঞপ্তি সফলভাবে সংরক্ষিত হয়েছে');
          this.closeModal();
          this.fetchNotices(this.isEdit ? this.pagination.current_page : 1);
        }
      } catch (err) {
        if (err.response && err.response.status === 422) {
          this.errors = err.response.data.errors || {};
        } else {
          this.toast('বিজ্ঞপ্তি সংরক্ষণ করা যায়নি', 'error');
        }
      } finally {
        this.saving = false;
      }
    },

    async toggleNoticeStatus(item) {
      try {
        const res = await axios.post(`/resident-notices-toggle-status/${item.id}`);
        if (res.data.success) {
          item.status = res.data.status;
          this.toast(res.data.message);
        }
      } catch (err) {
        this.toast('স্ট্যাটাস পরিবর্তন করা সম্ভব হয়নি', 'error');
      }
    },

    async confirmDelete(item) {
      const result = await Swal.fire({
        title: 'আপনি কি নিশ্চিত?',
        text: `"${item.title || 'এই বিজ্ঞপ্তি'}" মুছে ফেলতে চান?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'হ্যাঁ, মুছে ফেলুন',
        cancelButtonText: 'বাতিল',
      });

      if (result.isConfirmed) {
        try {
          const res = await axios.delete(`/resident-notices-delete/${item.id}`);
          if (res.data.success) {
            this.toast(res.data.message || 'বিজ্ঞপ্তি মুছে ফেলা হয়েছে');
            this.fetchNotices(this.pagination.current_page);
          }
        } catch (err) {
          this.toast('মুছে ফেলতে সমস্যা হয়েছে', 'error');
        }
      }
    },

    truncateText(text, length = 100) {
      if (!text) return '';
      return text.length > length ? text.substring(0, length) + '...' : text;
    },

    formatDate(dateStr) {
      if (!dateStr) return '';
      const d = new Date(dateStr);
      return d.toLocaleDateString('bn-BD', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
      }) + ' ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    },
  },
};
</script>

<style scoped>
.notice-preview {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 1.4;
}
.cursor-pointer {
  cursor: pointer;
}
</style>
