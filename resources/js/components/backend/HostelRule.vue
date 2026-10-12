<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
      <div class="col-12">
        <div class="card mb-4 shadow-sm border-0">
          <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3 bg-white border-bottom">
            <div>
              <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="ti ti-list-check me-2"></i>নিয়মাবলী (Hostel Rules)
              </h5>
              <small class="text-muted">হোমপেজের নিয়মাবলী সেকশনের আইকন ও টেক্সট পরিচালনা করুন</small>
            </div>
            <button class="btn btn-primary d-flex align-items-center gap-1" type="button" @click="openCreateModal">
              <i class="ti ti-plus fs-5"></i>
              <span>+ নতুন নিয়ম যোগ করুন</span>
            </button>
          </div>

          <div class="card-body pt-4">
            <!-- Filter Bar -->
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
              <div class="d-flex align-items-center gap-2">
                <label class="small text-muted mb-0">Rows:</label>
                <select class="form-select form-select-sm" style="width:90px" v-model.number="perPage" @change="fetchRules(1)">
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
                  style="width: 260px;"
                  placeholder="খুঁজুন (Search rules)..."
                  v-model="search"
                  @keyup.enter="fetchRules(1)"
                />
                <button class="btn btn-sm btn-outline-secondary" @click="fetchRules(1)">
                  <i class="ti ti-search"></i>
                </button>
              </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
              <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th style="width:60px" class="text-center">#</th>
                    <th style="width:100px" class="text-center">আইকন (Icon)</th>
                    <th>নিয়মের বিবরণ (Rule Description)</th>
                    <th style="width:120px" class="text-center">অ্যাকশন</th>
                  </tr>
                </thead>
                <tbody v-if="rules.length">
                  <tr v-for="(rule, idx) in rules" :key="rule.id">
                    <td class="text-center fw-bold text-muted">{{ from + idx }}</td>
                    <td class="text-center">
                      <div class="icon-preview-box mx-auto">
                        <i :class="rule.icon || 'ti ti-file-text'"></i>
                      </div>
                    </td>
                    <td class="fw-medium text-dark" style="font-size: 0.95rem; line-height: 1.6;">
                      {{ rule.description }}
                    </td>
                    <td class="text-center">
                      <div class="d-flex justify-content-center gap-2">
                        <button class="btn btn-sm btn-outline-primary" title="এডিট করুন" @click="openEditModal(rule)">
                          <i class="ti ti-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" title="ডিলিট করুন" @click="openDeleteModal(rule)">
                          <i class="ti ti-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
                <tbody v-else>
                  <tr>
                    <td colspan="4" class="text-center py-5 text-muted">
                      <span v-if="loading"><i class="ti ti-loader fa-spin me-2"></i>লোড হচ্ছে...</span>
                      <span v-else>কোনো নিয়ম পাওয়া যায়নি। নতুন নিয়ম যোগ করতে উপরের বাটনে ক্লিক করুন।</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 pt-2 border-top" v-if="total > 0">
              <div class="small text-muted">
                Showing {{ from }} to {{ to }} of {{ total }} entries
              </div>
              <ul class="pagination pagination-sm mb-0">
                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                  <a class="page-link" href="javascript:void(0)" @click.prevent="goToPage(currentPage - 1)">Previous</a>
                </li>
                <li
                  v-for="p in totalPages"
                  :key="p"
                  class="page-item"
                  :class="{ active: p === currentPage }"
                >
                  <a class="page-link" href="javascript:void(0)" @click.prevent="goToPage(p)">{{ p }}</a>
                </li>
                <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                  <a class="page-link" href="javascript:void(0)" @click.prevent="goToPage(currentPage + 1)">Next</a>
                </li>
              </ul>
            </div>

          </div>
        </div>
      </div>
    </div>

    <!-- VUE TELEPORT MODAL (Clean, Fast & Guaranteed to open) -->
    <Teleport to="body">
      <transition name="modal-fade">
        <div v-if="isModalOpen" class="custom-modal-backdrop" @click.self="closeModal">
          <div class="custom-modal-dialog">
            <div class="custom-modal-content">
              
              <!-- Modal Header -->
              <div class="custom-modal-header d-flex justify-content-between align-items-center bg-primary text-white px-4 py-3">
                <h5 class="modal-title mb-0 text-white fw-bold d-flex align-items-center gap-2">
                  <i :class="isEditMode ? 'ti ti-edit' : 'ti ti-plus'"></i>
                  <span>{{ isEditMode ? 'নিয়ম সম্পাদনা করুন (Edit Rule)' : 'নতুন নিয়ম যোগ করুন (Add Rule)' }}</span>
                </h5>
                <button type="button" class="btn-close-custom" @click="closeModal">
                  <i class="ti ti-x"></i>
                </button>
              </div>

              <!-- Modal Form -->
              <form @submit.prevent="submitForm">
                <div class="custom-modal-body p-4">
                  
                  <!-- 1. Icon Input & Quick Selector -->
                  <div class="mb-3">
                    <label class="form-label fw-bold text-dark d-flex justify-content-between align-items-center mb-2">
                      <span>১. আইকন নির্বাচন করুন (Icon) <span class="text-danger">*</span></span>
                      <span class="badge bg-light text-primary border px-2 py-1" v-if="formData.icon">
                        সিলেক্টেড: <i :class="formData.icon" class="ms-1 fs-6 align-middle"></i>
                      </span>
                    </label>

                    <!-- Icon Text Input -->
                    <div class="input-group mb-2">
                      <span class="input-group-text bg-light">
                        <i :class="formData.icon || 'ti ti-icons'" class="fs-5 text-primary"></i>
                      </span>
                      <input
                        type="text"
                        class="form-control"
                        v-model="formData.icon"
                        placeholder="যেমন: ti ti-calendar বা bi bi-calendar"
                        required
                      />
                    </div>

                    <!-- Preset Icons Palette -->
                    <div class="preset-icons-palette p-3 bg-light rounded-3 border">
                      <small class="text-muted d-block mb-2 fw-semibold">সহজে আইকন বেছে নিন (Quick Pick):</small>
                      <div class="d-flex flex-wrap gap-2">
                        <button
                          type="button"
                          v-for="item in presetIcons"
                          :key="item.icon"
                          class="btn-icon-item"
                          :class="{ 'active': formData.icon === item.icon }"
                          @click="formData.icon = item.icon"
                          :title="item.label"
                        >
                          <i :class="item.icon"></i>
                        </button>
                      </div>
                    </div>
                  </div>

                  <!-- 2. Rule Description Input -->
                  <div class="mb-2">
                    <label class="form-label fw-bold text-dark mb-1">
                      ২. নিয়মের বিবরণ / লেখা (Rule Description) <span class="text-danger">*</span>
                    </label>
                    <textarea
                      class="form-control"
                      rows="4"
                      v-model="formData.description"
                      placeholder="এখানে নিয়মের বিবরণ লিখুন..."
                      required
                      style="font-size: 0.95rem; line-height: 1.6;"
                    ></textarea>
                  </div>

                </div>

                <!-- Modal Footer -->
                <div class="custom-modal-footer d-flex justify-content-end gap-2 bg-light px-4 py-3 border-top">
                  <button type="button" class="btn btn-outline-secondary px-3" @click="closeModal">বাতিল</button>
                  <button type="submit" class="btn btn-primary px-4" :disabled="saving">
                    <span v-if="saving"><i class="ti ti-loader fa-spin me-1"></i>সংরক্ষণ হচ্ছে...</span>
                    <span v-else>{{ isEditMode ? 'আপডেট করুন' : 'সংরক্ষণ করুন' }}</span>
                  </button>
                </div>
              </form>

            </div>
          </div>
        </div>
      </transition>
    </Teleport>

  </div>
</template>

<script>
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
  name: 'HostelRule',
  data() {
    return {
      rules: [],
      loading: false,
      saving: false,
      search: '',
      perPage: 10,
      currentPage: 1,
      total: 0,
      from: 1,
      to: 1,
      totalPages: 1,
      isModalOpen: false,
      isEditMode: false,
      editId: null,
      formData: {
        icon: 'ti ti-calendar',
        description: '',
      },
      presetIcons: [
        { icon: 'ti ti-calendar', label: 'ক্যালেন্ডার / তারিখ' },
        { icon: 'ti ti-pig-money', label: 'জামানত / জমানো টাকা' },
        { icon: 'ti ti-cash', label: 'ভাড়া / ফি / পেমেন্ট' },
        { icon: 'ti ti-wallet', label: 'ওয়ালেট' },
        { icon: 'ti ti-sunset', label: 'মাগরিব / সন্ধ্যা' },
        { icon: 'ti ti-moon', label: 'রাত / সময়' },
        { icon: 'ti ti-arrows-exchange', label: 'রুম চেঞ্জ / পরিবর্তন' },
        { icon: 'ti ti-bolt', label: 'বিদ্যুৎ / ওয়াইফাই' },
        { icon: 'ti ti-wifi', label: 'ওয়াইফাই' },
        { icon: 'ti ti-door', label: 'রুম / সিট' },
        { icon: 'ti ti-bed', label: 'বেড / সিট' },
        { icon: 'ti ti-alert-triangle', label: 'সতর্কতা / নোটিশ' },
        { icon: 'ti ti-shield-check', label: 'নিরাপত্তা / নিয়ম' },
        { icon: 'ti ti-clock', label: 'সময়সীমা' },
        { icon: 'ti ti-user-check', label: 'শৃঙ্খলা' },
        { icon: 'ti ti-phone', label: 'অভিভাবক / যোগাযোগ' },
        { icon: 'ti ti-file-text', label: 'নথিপত্র' },
        { icon: 'ti ti-sparkles', label: 'পরিষ্কার-পরিচ্ছন্নতা' },
      ],
    };
  },
  mounted() {
    this.fetchRules(1);
  },
  methods: {
    async fetchRules(page = 1) {
      this.loading = true;
      try {
        const res = await axios.get('/hostel-rules-get', {
          params: {
            page: page,
            per_page: this.perPage,
            search: this.search,
          },
        });
        const data = res.data;
        this.rules = data.data || [];
        this.currentPage = data.current_page || 1;
        this.total = data.total || 0;
        this.from = data.from || 1;
        this.to = data.to || this.rules.length;
        this.totalPages = data.last_page || 1;
      } catch (err) {
        console.error('Error fetching rules:', err);
      } finally {
        this.loading = false;
      }
    },
    goToPage(p) {
      if (p >= 1 && p <= this.totalPages) {
        this.fetchRules(p);
      }
    },
    openCreateModal() {
      this.isEditMode = false;
      this.editId = null;
      this.formData = {
        icon: 'ti ti-calendar',
        description: '',
      };
      this.isModalOpen = true;
    },
    openEditModal(rule) {
      this.isEditMode = true;
      this.editId = rule.id;
      this.formData = {
        icon: rule.icon || 'ti ti-file-text',
        description: rule.description || '',
      };
      this.isModalOpen = true;
    },
    closeModal() {
      this.isModalOpen = false;
    },
    async submitForm() {
      if (!this.formData.description.trim()) {
        Swal.fire({
          icon: 'warning',
          title: 'সতর্কতা',
          text: 'অনুগ্রহ করে নিয়মের বিবরণ লিখুন!',
        });
        return;
      }

      this.saving = true;
      try {
        if (this.isEditMode) {
          await axios.post(`/hostel-rules-update/${this.editId}`, this.formData);
          Swal.fire({
            icon: 'success',
            title: 'সফল!',
            text: 'নিয়মটি সফলভাবে আপডেট করা হয়েছে।',
            timer: 1500,
            showConfirmButton: false,
          });
        } else {
          await axios.post('/hostel-rules-store', this.formData);
          Swal.fire({
            icon: 'success',
            title: 'সফল!',
            text: 'নতুন নিয়ম সফলভাবে যোগ করা হয়েছে।',
            timer: 1500,
            showConfirmButton: false,
          });
        }
        this.closeModal();
        this.fetchRules(this.currentPage);
      } catch (err) {
        console.error('Save error:', err);
        Swal.fire({
          icon: 'error',
          title: 'ভুল হয়েছে',
          text: err.response?.data?.message || 'সংরক্ষণে সমস্যা হয়েছে। আবার চেষ্টা করুন।',
        });
      } finally {
        this.saving = false;
      }
    },
    openDeleteModal(rule) {
      Swal.fire({
        title: 'আপনি কি নিশ্চিত?',
        text: 'এই নিয়মটি মুছে ফেলা হবে!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'হ্যাঁ, মুছে ফেলুন',
        cancelButtonText: 'বাতিল',
      }).then(async (result) => {
        if (result.isConfirmed) {
          try {
            await axios.delete(`/hostel-rules-delete/${rule.id}`);
            Swal.fire({
              icon: 'success',
              title: 'মুছে ফেলা হয়েছে!',
              text: 'নিয়মটি সফলভাবে মুছে ফেলা হয়েছে।',
              timer: 1500,
              showConfirmButton: false,
            });
            this.fetchRules(this.currentPage);
          } catch (err) {
            console.error('Delete error:', err);
            Swal.fire({
              icon: 'error',
              title: 'ভুল হয়েছে',
              text: 'মুছে ফেলতে সমস্যা হয়েছে।',
            });
          }
        }
      });
    },
  },
};
</script>

<style scoped>
.icon-preview-box {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  background: #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.35rem;
  color: #0b2b4d;
  border: 1px solid #e2e8f0;
}

/* Modal Backdrop & Container */
.custom-modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.65);
  backdrop-filter: blur(2px);
  z-index: 99999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  overflow-y: auto;
}

.custom-modal-dialog {
  width: 100%;
  max-width: 560px;
  margin: auto;
}

.custom-modal-content {
  background: #ffffff;
  border-radius: 16px;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.btn-close-custom {
  background: transparent;
  border: none;
  color: #ffffff;
  font-size: 1.4rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0.85;
  transition: opacity 0.2s ease;
}
.btn-close-custom:hover {
  opacity: 1;
}

.btn-icon-item {
  width: 42px;
  height: 42px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  border-radius: 10px;
  border: 1px solid #dee2e6;
  background: #ffffff;
  color: #334155;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-icon-item:hover {
  background: #e2e8f0;
  color: #0f172a;
  transform: translateY(-2px);
}

.btn-icon-item.active {
  background: #0b2b4d !important;
  color: #ffffff !important;
  border-color: #0b2b4d !important;
  box-shadow: 0 4px 10px rgba(11, 43, 77, 0.3);
}

/* Modal Transition */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
  transform: scale(0.96) translateY(-10px);
}
</style>
