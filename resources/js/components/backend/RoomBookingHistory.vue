<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
      <div class="col-12">
        <div class="card mb-4 shadow-sm">
          <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3">
            <h5 class="card-title mb-0 fw-bold">Room Booking History</h5>
            <div class="d-flex align-items-center gap-2">
              <input
                type="text"
                class="form-control form-control-sm"
                style="width: 260px"
                placeholder="Search room / floor / name / phone..."
                v-model="search"
              />
            </div>
          </div>

          <div class="card-body">
            <div class="d-flex align-items-center gap-2 mb-3">
              <label class="small text-muted mb-0">Rows:</label>
              <select
                class="form-select form-select-sm"
                style="width: 90px"
                v-model.number="perPage">
                <option :value="5">5</option>
                <option :value="10">10</option>
                <option :value="20">20</option>
                <option :value="50">50</option>
              </select>
            </div>

            <div class="table-responsive">
              
              <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th style="width: 70px">Sl</th>
                    <th style="width: 130px">Image</th>
                    <th style="width: 160px">Name</th>
                    <th style="width: 130px">User Type</th>
                    <th style="width: 130px">Floor</th>
                    <th style="width: 100px">Room</th>
                    <th style="width: 120px">Seat</th>
                    <th style="width: 180px">Booking Date & Time</th>
                    <th style="width: 150px">Monthly Amount</th>
                    <th style="width: 150px">Development Fee</th>
                    <th style="width: 180px">Email</th>
                    <th v-if="showFamilyColumns" style="width: 160px">Institution Name</th>
                    <th v-if="showFamilyColumns" style="width: 140px">Education System</th>
                    <th v-if="showFamilyColumns" style="width: 140px">Class / Semester</th>
                    <th v-if="showFamilyColumns" style="width: 150px">Father Name</th>
                    <th v-if="showFamilyColumns" style="width: 150px">Mother Name</th>
                    <th v-if="showFamilyColumns" style="width: 140px">Father NID</th>
                    <th v-if="showFamilyColumns" style="width: 140px">NID / Mother NID</th>
                    <th v-if="showFamilyColumns" style="width: 140px">Father Phone</th>
                    <th v-if="showFamilyColumns" style="width: 140px">Mother Phone</th>
                    <th v-if="showNidColumn" style="width: 160px">Workplace Name</th>
                    <th v-if="showNidColumn" style="width: 140px">NID</th>
                    <th style="width: 140px">Phone</th>
                    <th style="width: 120px">Division</th>
                    <th style="width: 120px">District</th>
                    <th style="width: 120px">Thana</th>
                    <th style="width: 160px">Address</th>
                    <th style="width: 120px">Payment</th>
                    <th style="width: 110px">Action</th>
                  </tr>
                </thead>

                <tbody v-if="rooms.length">
                  <tr v-for="(r, idx) in rooms" :key="r.group_key">
                    <td>{{ from + idx + 1 }}</td>

                    <td>
                      <img
                        v-if="r.image"
                        :src="imageSrc(r.image)"
                        class="img-thumb"
                        alt="booking"
                      />
                      <span v-else class="text-muted small">No image</span>
                    </td>

                    <td>
                      <div class="fw-semibold">{{ r.full_name || "-" }}</div>
                    </td>

                    <td>
                      <span class="badge bg-label-secondary fw-semibold">{{ r.user_type || 'Student' }}</span>
                    </td>

                    <td>
                      <div v-if="r.room_items && r.room_items.length">
                        <div v-for="(item, i) in r.room_items" :key="'f-' + r.id + '-' + i" class="py-1 fw-semibold text-dark">
                          {{ item.floornumber || "-" }}
                        </div>
                      </div>
                      <span v-else>-</span>
                    </td>

                    <td>
                      <div v-if="r.room_items && r.room_items.length">
                        <div v-for="(item, i) in r.room_items" :key="'r-' + r.id + '-' + i" class="py-1">
                          <span class="badge bg-primary font-monospace px-2 py-1 fs-6 fw-bold">{{ getRoomNo(item.roomnumber) }}</span>
                        </div>
                      </div>
                      <span v-else>-</span>
                    </td>

                    <td>
                      <div v-if="r.room_items && r.room_items.length">
                        <div v-for="(item, i) in r.room_items" :key="'s-' + r.id + '-' + i" class="py-1">
                          <span class="badge bg-danger font-monospace px-2 py-1 fs-6 fw-bold">{{ getSeatNo(item.roomnumber) }}</span>
                        </div>
                      </div>
                      <span v-else>-</span>
                    </td>

                    <td>
                      <span class="fw-semibold">{{ formatDateTime(r.created_at) }}</span>
                    </td>

                    <td>
                      <span class="fw-bold text-success">৳ {{ formatCurrency(r.monthly_amount) }}</span>
                    </td>

                    <td>
                      <span v-if="r.development_fee !== null && r.development_fee !== undefined && Number(r.development_fee) > 0" class="fw-bold text-warning">
                        ৳ {{ formatCurrency(r.development_fee) }}
                      </span>
                      <span v-else-if="r.development_fee !== null && r.development_fee !== undefined" class="fw-semibold text-muted">
                        ৳ {{ formatCurrency(r.development_fee) }}
                      </span>
                      <span v-else class="text-muted">-</span>
                    </td>

                    <td>
                      <span class="fw-semibold">{{ r.email || "-" }}</span>
                    </td>

                    <td v-if="showFamilyColumns">
                      <span class="fw-semibold">{{ isStudent(r) ? (r.institution_name || '-') : '—' }}</span>
                    </td>

                    <td v-if="showFamilyColumns">
                      <span class="fw-semibold">{{ isStudent(r) ? (r.education_level || '-') : '—' }}</span>
                    </td>

                    <td v-if="showFamilyColumns">
                      <span class="fw-semibold">{{ isStudent(r) ? (r.education_class || '-') : '—' }}</span>
                    </td>

                    <td v-if="showFamilyColumns">
                      <span class="fw-semibold">{{ isStudent(r) ? (r.father_name || '-') : '—' }}</span>
                    </td>

                    <td v-if="showFamilyColumns">
                      <span class="fw-semibold">{{ isStudent(r) ? (r.mother_name || '-') : '—' }}</span>
                    </td>

                    <td v-if="showFamilyColumns">
                      <span class="fw-semibold">{{ isStudent(r) ? (r.father_nid || '-') : '—' }}</span>
                    </td>

                    <td v-if="showFamilyColumns">
                      <span class="fw-semibold">{{ isStudent(r) ? (r.mother_nid || '-') : '—' }}</span>
                    </td>

                    <td v-if="showFamilyColumns">
                      <span class="fw-semibold">{{ isStudent(r) ? (r.father_phone || '-') : '—' }}</span>
                    </td>

                    <td v-if="showFamilyColumns">
                      <span class="fw-semibold">{{ isStudent(r) ? (r.mother_phone || '-') : '—' }}</span>
                    </td>

                    <td v-if="showNidColumn">
                      <span class="fw-semibold">{{ isProfessional(r) ? (r.workplace_name || "-") : "—" }}</span>
                    </td>

                    <td v-if="showNidColumn">
                      <span class="fw-semibold">{{ isProfessional(r) ? (r.nid || "-") : "—" }}</span>
                    </td>

                    <td>
                      <span class="fw-semibold">{{ r.phone || "-" }}</span>
                    </td>

                    <td>
                      <span class="fw-semibold">{{ r.division_name || "-" }}</span>
                    </td>

                    <td>
                      <span class="fw-semibold">{{ r.district_name || "-" }}</span>
                    </td>

                    <td>
                      <span class="fw-semibold">{{ r.thana_name || "-" }}</span>
                    </td>

                    <td>
                      <span class="fw-semibold">{{ r.address || "-" }}</span>
                    </td>

                    <td>
                      <div v-if="r.pay_online" class="fw-semibold text-success">
                        {{ r.pay_online }}
                      </div>
                      <div v-else-if="r.pay_cash_in" class="fw-semibold text-primary">
                        {{ r.pay_cash_in }}
                      </div>
                      <div v-else>-</div>
                    </td>

                    <td class="text-center">
                      <div class="d-flex align-items-center justify-content-center gap-1">
                        <button
                          type="button"
                          class="btn btn-sm btn-primary fw-bold text-nowrap"
                          @click="printResidentForm(r)"
                          title="Document"
                        >
                          <i class="fa fa-print me-1"></i>Document
                        </button>
                        <button
                          type="button"
                          class="btn btn-sm btn-info fw-bold text-nowrap"
                          @click="printResidentIdCard(r)"
                          title="Print Resident ID Card"
                        >
                          <i class="fa fa-id-card me-1"></i> ID Card
                        </button>
                        <button
                          type="button"
                          class="btn btn-sm btn-success fw-bold text-nowrap"
                          @click="openBookingReceiptModal(r)"
                          title="Booking Money Receipt"
                        >
                          <i class="ti ti-receipt me-1"></i> Booking Receipt
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>

                <tbody v-else>
                  <tr>
                    <td :colspan="totalColumns" class="text-center py-4 text-muted">
                      <span v-if="loading">
                        <i class="fa fa-spinner fa-spin me-2"></i> Loading...
                      </span>
                      <span v-else>No booking history found</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
              <div class="small text-muted">
                Total: {{ total }} | Page: {{ currentPage }} / {{ totalPages }}
              </div>

              <div class="d-flex align-items-center gap-2">
                <button
                  class="btn btn-sm btn-secondary"
                  :disabled="currentPage <= 1 || loading"
                  @click="fetchRooms(currentPage - 1)"
                >
                  Previous
                </button>

                <button
                  class="btn btn-sm btn-secondary"
                  :disabled="currentPage >= totalPages || loading"
                  @click="fetchRooms(currentPage + 1)"
                >
                  Next
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Booking Money Receipt Modal -->
    <div class="modal fade" id="bookingReceiptModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0">
          <div class="modal-header bg-success text-white py-3">
            <h5 class="modal-title text-white fw-bold">
              <i class="ti ti-receipt me-2"></i>বুকিং মানি রসিদ (Booking Money Receipt)
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4" v-if="selectedReceiptBooking" id="receiptPrintArea">
            
            <!-- Pad Header -->
            <div class="text-center pb-3 mb-3 border-bottom border-2 border-success">
              <h2 class="fw-bold mb-1 text-success" style="font-size: 26px;">টি এস এস ভিলা (TSS Villa)</h2>
              <p class="mb-0 fw-semibold text-dark fs-7">কলেজ রোড, নেসকোগেট সংলগ্ন, রংপুর | ফোন: +8801977270920</p>
              <div class="mt-2">
                <span class="badge bg-success px-3 py-2 fs-6 text-uppercase fw-bold shadow-sm" style="letter-spacing: 0.5px;">
                  <i class="ti ti-receipt me-1"></i> বুকিং মানি রসিদ (BOOKING MONEY RECEIPT)
                </span>
              </div>
            </div>

            <!-- Receipt Meta Info -->
            <div class="d-flex justify-content-between align-items-center mb-3 bg-light p-2 rounded border">
              <div><small class="text-muted fw-bold">রসিদ নং (Receipt No):</small> <span class="fw-bold text-success">TSSB-{{ selectedReceiptBooking.id }}</span></div>
              <div><small class="text-muted fw-bold">তারিখ ও সময় (Date & Time):</small> <span class="fw-bold text-dark">{{ formatDateTime(selectedReceiptBooking.created_at) }}</span></div>
            </div>

            <!-- Guest & Room Info Table -->
            <div class="table-responsive mb-3">
              <table class="table table-sm table-bordered align-middle mb-0" style="font-size: 0.9rem;">
                <tbody>
                  <tr>
                    <td class="bg-light fw-bold text-muted" style="width: 25%;">গেস্টের নাম (Name):</td>
                    <td class="fw-bold text-dark" style="width: 25%;">{{ selectedReceiptBooking.full_name || '-' }}</td>
                    <td class="bg-light fw-bold text-muted" style="width: 25%;">মোবাইল (Phone):</td>
                    <td class="fw-bold text-dark" style="width: 25%;">{{ selectedReceiptBooking.phone || '-' }}</td>
                  </tr>
                  <tr>
                    <td class="bg-light fw-bold text-muted">প্রতিষ্ঠান/কর্মস্থল (Institution):</td>
                    <td colspan="3" class="fw-bold text-dark">{{ selectedReceiptBooking.institution_name || selectedReceiptBooking.workplace_name || '-' }}</td>
                  </tr>
                  <tr>
                    <td class="bg-light fw-bold text-muted">ঠিকানা (Address):</td>
                    <td colspan="3" class="fw-semibold text-dark">
                      {{ selectedReceiptBooking.address || '' }} {{ selectedReceiptBooking.thana_name ? ', ' + selectedReceiptBooking.thana_name : '' }} {{ selectedReceiptBooking.district_name ? ', ' + selectedReceiptBooking.district_name : '' }}
                    </td>
                  </tr>
                  <tr class="table-info">
                    <td class="fw-bold text-dark">তলা (Floor):</td>
                    <td class="fw-bold text-dark">{{ getSelectedFloorNo(selectedReceiptBooking) }}</td>
                    <td class="fw-bold text-dark">রুম & সিট (Room & Seat):</td>
                    <td class="fw-bold text-dark">
                      Room: <span class="badge bg-primary me-1">{{ getSelectedRoomNo(selectedReceiptBooking) }}</span>
                      Seat: <span class="badge bg-danger">{{ getSelectedSeatNo(selectedReceiptBooking) }}</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Fee Summary Card (Matching Attached Image Design!) -->
            <div class="p-3 mb-4 rounded-3 shadow-sm" style="background-color: #fdf8e6; border: 2px solid #f59e0b;">
              <div class="d-flex justify-content-between align-items-center py-1">
                <span class="fs-6 text-muted">Advance Deposit / Room Price:</span>
                <span class="fs-6 fw-bold text-dark">৳ {{ formatCurrency(getAdvanceDepositAmount(selectedReceiptBooking)) }}</span>
              </div>
              <div v-if="getDevFeeAmount(selectedReceiptBooking) > 0" class="d-flex justify-content-between align-items-center py-1">
                <span class="fs-6 text-warning-dark fw-semibold">Development Fee (One-time):</span>
                <span class="fs-6 fw-bold text-warning">+ ৳ {{ formatCurrency(getDevFeeAmount(selectedReceiptBooking)) }}</span>
              </div>
              <hr class="my-2" style="border-top: 2px dashed #f59e0b; opacity: 0.6;">
              <div class="d-flex justify-content-between align-items-center pt-1">
                <span class="fs-5 fw-bold text-dark">Total Amount Payable:</span>
                <span class="fs-4 fw-extrabold text-success">৳ {{ formatCurrency(calculateTotalAmountPayable(selectedReceiptBooking)) }}</span>
              </div>
            </div>


            <!-- Signatures -->
            <div class="d-flex justify-content-between align-items-end pt-4 mt-2">
              <div class="text-center" style="width: 180px;">
                <div class="border-bottom border-dark mb-1"></div>
                <small class="fw-bold text-dark">বোর্ডারের স্বাক্ষর</small>
              </div>
              <div class="text-center" style="width: 180px;">
                <div class="border-bottom border-dark mb-1"></div>
                <small class="fw-bold text-dark">কর্তৃপক্ষের স্বাক্ষর</small>
              </div>
            </div>


          </div>
          <div class="modal-footer bg-light py-2">
            <button type="button" class="btn btn-secondary btn-sm fw-bold" data-bs-dismiss="modal">
              <i class="ti ti-x me-1"></i> বন্ধ করুন (Close)
            </button>
            <button type="button" class="btn btn-success btn-sm fw-bold" @click="printBookingReceipt(selectedReceiptBooking)">
              <i class="ti ti-printer me-1"></i> প্রিন্ট করুন (Print Receipt)
            </button>
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
  name: "RoomBookingHistoryList",

  props: {
    isAdmin: {
      type: Boolean,
      default: false,
    },
  },

  data() {
    return {
      rooms: [],
      loading: false,
      search: "",
      perPage: 10,
      currentPage: 1,
      totalPages: 1,
      total: 0,
      from: 0,
      _t: null,

      startDate: "",
      endDate: "",
      selectedGuest: "",
      guestNames: [],
      selectedReceiptBooking: null,
    };
  },


  computed: {
    url() {
      return this.$store.state.url;
    },
    showNidColumn() {
      return this.rooms.some(r => this.isProfessional(r));
    },
    showFamilyColumns() {
      return this.rooms.some(r => this.isStudent(r)) || !this.rooms.some(r => this.isProfessional(r));
    },
    totalColumns() {
      let count = 17; // base columns
      if (this.showFamilyColumns) count += 9;
      if (this.showNidColumn) count += 2;
      return count;
    },
  },

  mounted() {
    this.fetchRooms(1);

    if (this.isAdmin) {
      this.loadGuestNames();
    }
  },

watch: {
  search() {
    clearTimeout(this._t);
    this._t = setTimeout(() => this.fetchRooms(1), 300);
  },
  perPage() {
    this.fetchRooms(1);
  },
},

  beforeUnmount() {
    clearTimeout(this._t);
  },

  methods: {
    openBookingReceiptModal(r) {
      this.selectedReceiptBooking = r;
      const modalEl = document.getElementById("bookingReceiptModal");
      if (modalEl) {
        const bsModal = new bootstrap.Modal(modalEl);
        bsModal.show();
      }
    },

    getAdvanceDepositAmount(r) {
      if (!r) return 0;
      if (r.room_items && r.room_items.length) {
        let sum = 0;
        r.room_items.forEach(item => {
          const p = Number(item.advance_price ?? item.original_advance_price ?? item.price ?? 0);
          sum += p;
        });
        if (sum > 0) return sum;
      }
      if (r.advance_fee !== null && r.advance_fee !== undefined && Number(r.advance_fee) > 0) {
        return Number(r.advance_fee);
      }
      if (r.roomprice !== null && r.roomprice !== undefined && Number(r.roomprice) > 0) {
        return Number(r.roomprice);
      }
      return Number(r.monthly_amount || 0);
    },

    getDevFeeAmount(r) {
      if (!r) return 0;
      if (r.development_fee === null || r.development_fee === undefined) return 0;
      return Number(r.development_fee);
    },

    calculateTotalAmountPayable(r) {
      if (!r) return 0;
      const advance = this.getAdvanceDepositAmount(r);
      const devFee = this.getDevFeeAmount(r);
      return advance + devFee;
    },

    getSelectedFloorNo(r) {
      if (!r) return '-';
      if (r.room_items && r.room_items.length) {
        return [...new Set(r.room_items.map(i => i.floornumber))].filter(Boolean).join(', ');
      }
      return r.floornumber || '-';
    },

    getSelectedRoomNo(r) {
      if (!r) return '-';
      if (r.room_items && r.room_items.length) {
        return r.room_items.map(i => this.getRoomNo(i.roomnumber)).join(', ');
      }
      return this.getRoomNo(r.roomnumber) || r.room_number || '-';
    },

    getSelectedSeatNo(r) {
      if (!r) return '-';
      if (r.room_items && r.room_items.length) {
        return r.room_items.map(i => this.getSeatNo(i.roomnumber)).join(', ');
      }
      return this.getSeatNo(r.roomnumber) || '-';
    },

    printBookingReceipt(r) {
      if (!r) return;
      const roomNo = this.getSelectedRoomNo(r);
      const seatNo = this.getSelectedSeatNo(r);
      const floorNo = this.getSelectedFloorNo(r);
      const fullName = r.full_name || '-';
      const phone = r.phone || '-';
      const address = (r.address || '') + (r.thana_name ? ', ' + r.thana_name : '') + (r.district_name ? ', ' + r.district_name : '');
      const institution = r.institution_name || r.workplace_name || '-';
      const bookingDate = this.formatDateTime(r.created_at);
      const advance = this.getAdvanceDepositAmount(r);
      const devFee = this.getDevFeeAmount(r);
      const totalAmount = advance + devFee;

      const devFeeRowHtml = devFee > 0 ? `
        <div style="display: flex; justify-content: space-between; font-size: 15px; padding: 4px 0;">
          <span style="color: #b45309; font-weight: 600;">Development Fee (One-time):</span>
          <span style="font-weight: bold; color: #d97706;">+ ৳ ${this.formatCurrency(devFee)}</span>
        </div>
      ` : '';

      const printHtml = `
        <!DOCTYPE html>
        <html>
        <head>
          <title>Booking Money Receipt - ${fullName}</title>
          <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
          <style>
            @media print {
              @page { size: A4 portrait; margin: 15mm; }
              body { font-family: 'Segoe UI', Arial, sans-serif; color: #111; }
              .no-print { display: none !important; }
            }
            body { padding: 25px; font-family: 'Segoe UI', Arial, sans-serif; background: #fff; }
            .receipt-card { max-width: 750px; margin: 0 auto; border: 2px solid #198754; padding: 25px; border-radius: 12px; }
            .pad-header { text-align: center; border-bottom: 2px solid #198754; padding-bottom: 12px; margin-bottom: 20px; }
            .fee-box { background-color: #fffdf5; border: 2px solid #f59e0b; border-radius: 12px; padding: 15px; margin-top: 20px; margin-bottom: 20px; }
            .sig-box { margin-top: 60px; display: flex; justify-content: space-between; }
            .sig-line { width: 180px; border-top: 1px solid #000; text-align: center; padding-top: 5px; font-weight: bold; font-size: 13px; }
          </style>
        </head>
        <body onload="window.print(); setTimeout(function(){ window.close(); }, 500);">
          <div class="receipt-card">
            <div class="pad-header">
              <h2 style="font-weight: 800; color: #198754; margin-bottom: 2px;">টি এস এস ভিলা (TSS Villa)</h2>
              <p style="margin: 0; font-size: 14px; font-weight: 600; color: #333;">কলেজ রোড, নেসকোগেট সংলগ্ন, রংপুর | ফোন: +8801977270920</p>
              <div style="margin-top: 10px;">
                <span class="badge bg-success fs-6 px-3 py-2 text-uppercase">বুকিং মানি রসিদ (BOOKING MONEY RECEIPT)</span>
              </div>
            </div>

            <div style="display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 14px; background: #f8f9fa; padding: 8px 12px; border-radius: 6px; border: 1px solid #ddd;">
              <div><strong>রসিদ নং:</strong> TSSB-${r.id}</div>
              <div><strong>তারিখ ও সময়:</strong> ${bookingDate}</div>
            </div>

            <table class="table table-bordered align-middle" style="font-size: 14px;">
              <tbody>
                <tr>
                  <td style="background: #f8f9fa; font-weight: bold; width: 25%;">গেস্টের নাম:</td>
                  <td style="font-weight: 600; width: 25%;">${fullName}</td>
                  <td style="background: #f8f9fa; font-weight: bold; width: 25%;">মোবাইল নং:</td>
                  <td style="font-weight: 600; width: 25%;">${phone}</td>
                </tr>
                <tr>
                  <td style="background: #f8f9fa; font-weight: bold;">প্রতিষ্ঠান/কর্মস্থল:</td>
                  <td colspan="3" style="font-weight: 600;">${institution}</td>
                </tr>
                <tr>
                  <td style="background: #f8f9fa; font-weight: bold;">ঠিকানা:</td>
                  <td colspan="3">${address || '-'}</td>
                </tr>
                <tr style="background-color: #e8f5e9;">
                  <td style="font-weight: bold;">তলা (Floor):</td>
                  <td style="font-weight: bold;">${floorNo}</td>
                  <td style="font-weight: bold;">রুম & সিট:</td>
                  <td style="font-weight: bold;">Room: ${roomNo} | Seat: ${seatNo}</td>
                </tr>
              </tbody>
            </table>

            <div class="fee-box">
              <div style="display: flex; justify-content: space-between; font-size: 15px; padding: 4px 0;">
                <span style="color: #555;">Advance Deposit / Room Price:</span>
                <span style="font-weight: bold; color: #111;">৳ ${this.formatCurrency(advance)}</span>
              </div>
              ${devFeeRowHtml}
              <hr style="border-top: 2px dashed #f59e0b; margin: 10px 0;">
              <div style="display: flex; justify-content: space-between; font-size: 18px; font-weight: bold; padding-top: 4px;">
                <span>Total Amount Payable:</span>
                <span style="color: #059669; font-size: 20px;">৳ ${this.formatCurrency(totalAmount)}</span>
              </div>
            </div>

            <div class="sig-box">
              <div class="sig-line">বোর্ডারের স্বাক্ষর</div>
              <div class="sig-line">কর্তৃপক্ষের স্বাক্ষর</div>
            </div>
          </div>
        </body>
        </html>
      `;

      const win = window.open('', '_blank', 'width=850,height=900');
      win.document.write(printHtml);
      win.document.close();
    },

    formatCurrency(val) {

      if (val === null || val === undefined || isNaN(val)) return '0';
      return Number(val).toLocaleString('en-US');
    },

    getRoomNo(str) {
      if (!str) return '-';
      const parts = String(str).split('-');
      return parts[0] || str;
    },

    getSeatNo(str) {
      if (!str) return '-';
      const parts = String(str).split('-');
      if (parts.length > 1) {
        return parts.slice(1).join('-');
      }
      return '-';
    },

    isStudent(r) {
      if (!r || !r.user_type) return true;
      return r.user_type.toLowerCase() === 'student';
    },

    isProfessional(r) {
      if (!r || !r.user_type) return false;
      return r.user_type.toLowerCase() === 'working professional';
    },

    async loadGuestNames() {
      try {
        const res = await axios.get(this.endpoint("get-select-guet"));
        if (res.data.status === "success") {
          this.guestNames = res.data.data || [];
        }
      } catch (error) {
        this.toast("Failed to load guest names.", "error");
      }
    },

    clearFilters() {
      this.startDate = "";
      this.endDate = "";
      this.selectedGuest = "";
      this.fetchRooms(1);
    },

    getTotalDays(checkIn, checkOut) {
      if (!checkIn || !checkOut) return 0;

      const start = new Date(checkIn);
      const end = new Date(checkOut);

      const diffTime = end - start;
      const diffDays = diffTime / (1000 * 60 * 60 * 24);

      return diffDays > 0 ? diffDays : 0;
    },

    toast(text, type = "success") {
      const bg =
        type === "success"
          ? "linear-gradient(to right, #00b09b, #96c93d)"
          : type === "warning"
          ? "linear-gradient(to right, #f59e0b, #fbbf24)"
          : "linear-gradient(to right, #ff5f6d, #ffc371)";

      Toastify({
        text: text || (type === "success" ? "Done " : "Something went wrong"),
        duration: 2500,
        close: true,
        gravity: "top",
        position: "right",
        backgroundColor: bg,
      }).showToast();
    },

    endpoint(path) {
      const base = this.url.endsWith("/") ? this.url.slice(0, -1) : this.url;
      const cleanPath = path.startsWith("/") ? path : `/${path}`;
      return `${base}${cleanPath}`;
    },

    imageSrc(path) {
      if (!path) return "";
      if (path.startsWith("http://") || path.startsWith("https://")) {
        return path;
      }
      const base = this.url.endsWith("/") ? this.url.slice(0, -1) : this.url;
      return `${base}/bookingsimage/${path}`;
    },

    formatDate(value) {
      if (!value) return "-";
      const d = new Date(value);
      if (isNaN(d.getTime())) return value;
      return d.toLocaleDateString("en-GB", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
      });
    },

    formatDateTime(value) {
      if (!value) return "-";

      const raw = String(value).trim();
      const normalized = raw.includes("T") ? raw : raw.replace(" ", "T");
      const dateObj = new Date(normalized);

      if (isNaN(dateObj.getTime())) {
        return "-";
      }

      return new Intl.DateTimeFormat("en-GB", {
        timeZone: "Asia/Dhaka",
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
        hour12: true,
      }).format(dateObj);
    },

    async fetchRooms(page = 1) {
  this.loading = true;

  try {
    const params = {
      page,
      per_page: this.perPage,
      search: this.search,
    };

    if (this.isAdmin) {
      params.start_date = this.startDate;
      params.end_date = this.endDate;
      params.selected_guest = this.selectedGuest;
    }

    const res = await axios.get(this.endpoint("get-room-booking"), {
      params,
    });

    this.rooms = res.data.data || [];
    this.currentPage = res.data.current_page || 1;
    this.totalPages = res.data.last_page || 1;
    this.total = res.data.total || 0;
    this.from = res.data.from ? res.data.from - 1 : 0;
  } catch (e) {
    console.error(e);
    this.toast("Failed to load booking history", "error");
  } finally {
    this.loading = false;
  }
},

    printTable() {
      if (this.rooms && this.rooms.length > 0) {
        this.printResidentForm(this.rooms[0]);
      } else {
        this.toast("প্রিন্ট করার মতো কোনো বুকিং ডাটা পাওয়া যায়নি", "warning");
      }
    },

    printResidentForm(r) {
      const logoUrl = window.location.origin + '/logo/logoimage (2).png';
      const userImgUrl = r.image ? this.imageSrc(r.image) : '';
      const roomNo = (r.room_items && r.room_items.length)
        ? r.room_items.map(i => this.getRoomNo(i.roomnumber)).join(', ')
        : (this.getRoomNo(r.roomnumber) || r.room_number || '-');
      const seatNo = (r.room_items && r.room_items.length)
        ? r.room_items.map(i => this.getSeatNo(i.roomnumber)).join(', ')
        : (this.getSeatNo(r.roomnumber) || '-');
      const floorNo = (r.room_items && r.room_items.length)
        ? [...new Set(r.room_items.map(i => i.floornumber))].filter(Boolean).join(', ')
        : (r.floornumber || '-');
      const fullName = r.full_name || '-';
      const phone = r.phone || '-';
      const address = r.address || '-';
      const thanaName = r.thana_name || '-';
      const districtName = r.district_name || '-';
      const bookingDate = r.created_at
        ? new Date(r.created_at).toLocaleString('bn-BD', { dateStyle: 'long', timeStyle: 'short' })
        : (r.booking_date || '-');

      const isProf = r.user_type && (
        r.user_type.toLowerCase().includes('professional') ||
        r.user_type.toLowerCase().includes('job') ||
        r.user_type.toLowerCase().includes('passenger')
      );

      const advance = this.getAdvanceDepositAmount(r);
      const devFee = this.getDevFeeAmount(r);
      const totalAmount = advance + devFee;

      const devFeeDocHtml = devFee > 0 ? `
        <div style="display: flex; justify-content: space-between; font-size: 14.5px; padding: 3px 0;">
          <span style="color: #b45309; font-weight: 700;">Development Fee (One-time):</span>
          <span style="font-weight: 800; color: #d97706;">+ ৳ ${this.formatCurrency(devFee)}</span>
        </div>
        <hr style="border-top: 1.5px dashed #f59e0b; margin: 6px 0; opacity: 0.7;">
      ` : '';

      const feeSectionHtml = `
        <div class="section-title" style="margin-top: 10px; margin-bottom: 8px;">পেমেন্ট ও ফি বিবরণী</div>
        <div style="background-color: #fdf8e6; border: 2px solid #f59e0b; border-radius: 10px; padding: 10px 18px; margin-bottom: 10px;">
          <div style="display: flex; justify-content: space-between; font-size: 14.5px; padding: 3px 0;">
            <span style="color: #555; font-weight: 700;">Advance Deposit / Room Price:</span>
            <span style="font-weight: 800; color: #111;">৳ ${this.formatCurrency(advance)}</span>
          </div>
          ${devFeeDocHtml}
          <div style="display: flex; justify-content: space-between; font-size: 16px; font-weight: 800; padding-top: 2px;">
            <span style="color: #111;">Total Amount Payable:</span>
            <span style="color: #059669; font-size: 18px;">৳ ${this.formatCurrency(totalAmount)}</span>
          </div>
        </div>
      `;


      const docTitle = isProf ? 'কর্মজীবীর তথ্য - টি এস এস ভিলা' : 'শিক্ষার্থীর তথ্য - টি এস এস ভিলা';
      const sectionTitleText = isProf ? 'কর্মজীবীর তথ্য' : 'শিক্ষার্থীর তথ্য';
      const signatureLabelText = isProf ? 'বোর্ডারের স্বাক্ষর' : 'শিক্ষার্থীর স্বাক্ষর';

      const institutionName = r.institution_name || '-';
      const fatherName = r.father_name || '-';
      const fatherPhone = r.father_phone || '-';
      const motherName = r.mother_name || '-';
      const motherPhone = r.mother_phone || '-';

      let infoSectionHtml = '';
      if (isProf) {
        infoSectionHtml = `
          <div class="form-pill-row">
            <div class="pill-lbl">কর্মজীবীর পূর্ণ নাম :</div>
            <div class="pill-val">${fullName}</div>
            <div class="pill-right">
              <div class="pill-lbl">মোবাইল নং :</div>
              <div class="pill-val">${phone}</div>
            </div>
          </div>

          <div class="form-pill-row">
            <div class="pill-lbl">কর্মপ্রতিষ্ঠানের নাম:</div>
            <div class="pill-val">${r.workplace_name || '-'}</div>
            <div class="pill-right">
              <div class="pill-lbl">NID নম্বর :</div>
              <div class="pill-val">${r.nid || '-'}</div>
            </div>
          </div>

          ${(fatherName !== '-' || motherName !== '-') ? `
          <div class="form-pill-row">
            <div class="pill-lbl">পিতার নাম:</div>
            <div class="pill-val">${fatherName}</div>
            <div class="pill-right">
              <div class="pill-lbl">মোবাইল নং :</div>
              <div class="pill-val">${fatherPhone}</div>
            </div>
          </div>
          <div class="form-pill-row">
            <div class="pill-lbl">মাতার নাম:</div>
            <div class="pill-val">${motherName}</div>
            <div class="pill-right">
              <div class="pill-lbl">মোবাইল নং :</div>
              <div class="pill-val">${motherPhone}</div>
            </div>
          </div>
          ` : ''}
        `;
      } else {
        infoSectionHtml = `
          <div class="form-pill-row">
            <div class="pill-lbl">শিক্ষার্থীর পূর্ণ নাম :</div>
            <div class="pill-val">${fullName}</div>
            <div class="pill-right">
              <div class="pill-lbl">মোবাইল নং :</div>
              <div class="pill-val">${phone}</div>
            </div>
          </div>

          <div class="form-pill-row">
            <div class="pill-lbl">অধ্যয়নরত শিক্ষা প্রতিষ্ঠানের নাম:</div>
            <div class="pill-val">${institutionName}</div>
          </div>

          <div class="form-pill-row">
            <div class="pill-lbl">পিতার নাম:</div>
            <div class="pill-val">${fatherName}</div>
            <div class="pill-right">
              <div class="pill-lbl">মোবাইল নং :</div>
              <div class="pill-val">${fatherPhone}</div>
            </div>
          </div>

          <div class="form-pill-row">
            <div class="pill-lbl">মাতার নাম:</div>
            <div class="pill-val">${motherName}</div>
            <div class="pill-right">
              <div class="pill-lbl">মোবাইল নং :</div>
              <div class="pill-val">${motherPhone}</div>
            </div>
          </div>
        `;
      }

      const html = `
        <!DOCTYPE html>
        <html lang="bn">
        <head>
          <meta charset="UTF-8">
          <title>${docTitle}</title>
          <style>
            @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700;800&family=Tiro+Bangla&display=swap');
            @page { size: A4 portrait; margin: 6mm; }
            * { box-sizing: border-box; margin: 0; padding: 0; }
            html, body {
              height: 100%;
              margin: 0;
              padding: 0;
              background: #fff;
              font-family: 'Hind Siliguri', 'Tiro Bangla', sans-serif;
              color: #000;
              -webkit-print-color-adjust: exact;
              print-color-adjust: exact;
            }

            .paper-frame {
              background: linear-gradient(165deg, #fef9e7 0%, #fef3cd 50%, #fef9e7 100%);
              border: 4px solid #27ae60;
              border-radius: 10px;
              padding: 22px 24px 20px 24px;
              box-sizing: border-box;
              position: relative;
              width: 100%;
              min-height: 282mm;
              display: flex;
              flex-direction: column;
              justify-content: space-between;
              page-break-inside: avoid;
            }
            .paper-frame::before {
              content: '';
              position: absolute;
              inset: 4px;
              border: 1.5px solid #f39c12;
              border-radius: 7px;
              pointer-events: none;
            }

            .main-content-wrap {
              display: flex;
              flex-direction: column;
              flex: 1;
            }

            .top-header {
              display: flex;
              align-items: center;
              justify-content: space-between;
              gap: 16px;
              margin-bottom: 12px;
              padding: 0 4px;
            }
            .logo-wrap img { width: 78px; height: 78px; object-fit: contain; }
            .brand-center { text-align: center; flex: 1; }
            .brand-name {
              font-size: 48px;
              font-weight: 900;
              color: #c0392b;
              font-family: 'Tiro Bangla', serif;
              line-height: 1;
              letter-spacing: 1px;
            }
            .photo-box {
              width: 110px;
              height: 125px;
              border: 2px solid #2c3e50;
              border-radius: 4px;
              display: flex;
              align-items: center;
              justify-content: center;
              overflow: hidden;
              background: #f0f0f0;
              flex-shrink: 0;
            }
            .photo-box img { width: 100%; height: 100%; object-fit: cover; }
            .photo-placeholder { font-size: 11px; color: #888; text-align: center; }

            .address-banner {
              background: #1a237e;
              color: #fff;
              text-align: center;
              padding: 9px 12px;
              font-size: 13.5px;
              font-weight: 600;
              border-radius: 5px;
              margin-bottom: 12px;
              letter-spacing: 0.2px;
            }

            .room-meta-row {
              display: flex;
              gap: 12px;
              margin-bottom: 12px;
            }
            .room-meta-box {
              flex: 1;
              border: 2px solid #27ae60;
              border-radius: 6px;
              padding: 8px 12px;
              background: #fff;
              font-size: 14px;
              font-weight: 600;
              display: flex;
              align-items: center;
              gap: 5px;
            }
            .room-meta-box .lbl { color: #1a5c2e; font-weight: 700; white-space: nowrap; }
            .room-meta-box .val { color: #000; font-weight: 800; font-size: 14.5px; }

            .section-title-container {
              display: flex;
              align-items: center;
              justify-content: space-between;
              border-bottom: 2.5px solid #f39c12;
              padding-bottom: 4px;
              margin: 12px 0 10px 0;
            }
            .title-side-dummy { flex: 1; }
            .section-title-container .section-title {
              text-align: center;
              font-size: 22px;
              font-weight: 800;
              color: #e74c3c;
              font-family: 'Tiro Bangla', serif;
              letter-spacing: 0.3px;
              flex: 2;
              margin: 0;
              padding-bottom: 0;
              border-bottom: none;
            }
            .booking-date-right-box {
              flex: 1;
              text-align: right;
              font-size: 13.5px;
              font-weight: 700;
              white-space: nowrap;
            }
            .booking-date-right-box .lbl { color: #1a5c2e; font-weight: 800; }
            .booking-date-right-box .val { color: #000; font-weight: 800; }

            .section-title {
              text-align: center;
              font-size: 21.5px;
              font-weight: 800;
              color: #e74c3c;
              font-family: 'Tiro Bangla', serif;
              border-bottom: 2.5px solid #f39c12;
              padding-bottom: 4px;
              margin: 12px 0 10px 0;
              letter-spacing: 0.3px;
            }

            .form-pill-row {
              border: 2px solid #8e44ad;
              border-radius: 25px;
              background: #fff;
              display: flex;
              overflow: hidden;
              margin-bottom: 10px;
              min-height: 44px;
              align-items: stretch;
            }
            .pill-lbl {
              background: #7b1fa2;
              color: #fff;
              padding: 9px 18px;
              font-size: 14px;
              font-weight: 700;
              white-space: nowrap;
              display: flex;
              align-items: center;
              border-right: 2px solid #8e44ad;
              flex-shrink: 0;
            }
            .pill-val {
              padding: 9px 18px;
              font-size: 14.5px;
              font-weight: 600;
              color: #111;
              flex-grow: 1;
              display: flex;
              align-items: center;
            }
            .pill-right {
              border-left: 2px solid #8e44ad;
              display: flex;
              align-items: stretch;
              flex-shrink: 0;
            }

            .address-row {
              border: 2px solid #8e44ad;
              border-radius: 25px;
              background: #fff;
              display: flex;
              flex-wrap: wrap;
              padding: 12px 20px;
              font-size: 14px;
              font-weight: 600;
              gap: 10px 30px;
              margin-bottom: 10px;
              align-items: center;
              min-height: 44px;
            }
            .akey { color: #7b1fa2; font-weight: 700; }

            .rules-box {
              background: #fffde7;
              border: 2px solid #f48fb1;
              border-radius: 12px;
              padding: 14px 20px;
              margin-top: 4px;
              margin-bottom: 8px;
            }
            .rules-list { list-style: none; padding: 0; margin: 0; }
            .rules-list li {
              font-size: 13.5px;
              font-weight: 600;
              color: #1a1a1a;
              line-height: 1.9;
              border-bottom: 1px dashed #f8bbd0;
              padding-bottom: 5px;
              margin-bottom: 5px;
            }
            .rules-list li:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }

            .signature-row {
              display: flex;
              justify-content: space-between;
              align-items: flex-end;
              margin-top: auto;
              padding: 20px 36px 10px 36px;
            }
            .sig-box { text-align: center; min-width: 150px; }
            .sig-line { border-top: 1.5px solid #2c3e50; margin-bottom: 5px; }
            .sig-text { font-size: 14px; font-weight: 700; color: #1a1a1a; }

            @media print {
              html, body {
                height: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
              }
              .paper-frame {
                height: calc(297mm - 12mm) !important;
                min-height: calc(297mm - 12mm) !important;
                border-radius: 0;
                box-sizing: border-box;
                page-break-inside: avoid !important;
              }
            }
          </style>
        </head>
        <body>
          <div class="paper-frame">
            <div class="main-content-wrap">
              <div class="top-header">
                <div class="logo-wrap">
                  <img src="${logoUrl}" alt="Logo" onerror="this.style.display='none'">
                </div>
                <div class="brand-center">
                  <div class="brand-name">টি এস এস ভিলা</div>
                </div>
                <div class="photo-box">
                  ${userImgUrl
                    ? `<img src="${userImgUrl}" alt="Photo">`
                    : `<div class="photo-placeholder">ছবি<br>Photo</div>`}
                </div>
              </div>

              <div class="address-banner">
                কলেজ রোড , নেসকো গেট সংলগ্ন , রংপুর &nbsp;|&nbsp; প্রয়োজনে: ০১৯৭৭২৭০৯২০ &nbsp;|&nbsp; Gmail: tssvilla2026@gmail.com
              </div>

              <div class="room-meta-row">
                <div class="room-meta-box"><span class="lbl">রুম নং:</span>&nbsp;<span class="val">${roomNo}</span></div>
                <div class="room-meta-box"><span class="lbl">ব্লক নং:</span>&nbsp;<span class="val">${seatNo}</span></div>
                <div class="room-meta-box"><span class="lbl">ফ্লোর নং:</span>&nbsp;<span class="val">${floorNo}</span></div>
              </div>

              <div class="section-title-container">
                <div class="title-side-dummy"></div>
                <div class="section-title">${sectionTitleText}</div>
                <div class="booking-date-right-box">
                  <span class="lbl">বুকিং তারিখ:</span> <span class="val">${bookingDate}</span>
                </div>
              </div>

              ${infoSectionHtml}

              <div class="section-title">স্থায়ী ঠিকানা</div>

              <div class="address-row">
                <span><span class="akey">গ্রাম/রাস্তা:</span> ${address}</span>
                <span><span class="akey">থানা:</span> ${thanaName}</span>
                <span><span class="akey">উপজেলা:</span> ${thanaName}</span>
                <span><span class="akey">জেলা:</span> ${districtName}</span>
              </div>

              <div class="section-title">নিয়মাবলী</div>



              <div class="rules-box">
                ${isProf ? `
                <ul class="rules-list">
                  <li>* মেসের ভাড়া ০৭ তারিখের মধ্যে পরিশোধ করতে হবে।</li>
                  <li>* মেস ছাড়লে ০২ মাস পূর্বেই মেস কর্তৃপক্ষকে জানাতে হবে। অন্যথায় দুই মাসের ভাড়া দিয়ে মেস ছাড়তে হবে।</li>
                  <li>* রুম চেঞ্জ করতে চাইলে ৫০০ টাকা জরিমানা প্রদান করতে হবে।</li>
                  <li>* মেসের নিয়ম-কানুন মেনে চলতে হবে। কারো বিরুদ্ধে কোনো অভিযোগ আসলে এবং তা প্রমাণিত হলে সিট বাতিলসহ যেকোনো ব্যবস্থা নেয়ার অধিকার কর্তৃপক্ষ রাখে।</li>
                </ul>
                ` : `
                <ul class="rules-list">
                  <li>* মেসের ভাড়া ০৭ তারিখের মধ্যে পরিশোধ করতে হবে।</li>
                  <li>* মেস ছাড়লে ০২ মাস পূর্বেই মেস কর্তৃপক্ষকে জানাতে হবে। অন্যথায় দুই মাসের ভাড়া দিয়ে মেস ছাড়তে হবে।</li>
                  <li>* মাগরিবের আযানের পর মেসের বাহিরে থাকলে অভিভাবককে জানিয়ে দিতে হবে।</li>
                  <li>* রুম চেঞ্জ করতে চাইলে ৫০০ টাকা জরিমানা প্রদান করতে হবে।</li>
                  <li>* মেসের নিয়ম-কানুন মেনে চলতে হবে। কারো বিরুদ্ধে কোনো অভিযোগ আসলে এবং তা প্রমাণিত হলে সিট বাতিলসহ যেকোনো ব্যবস্থা নেয়ার অধিকার কর্তৃপক্ষ রাখে।</li>
                </ul>
                `}
              </div>
            </div>

            <div class="signature-row">
              <div class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-text">অনুমোদিত স্বাক্ষর</div>
              </div>
              <div class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-text">${signatureLabelText}</div>
              </div>
            </div>
          </div>
        </body>
        </html>
      `;

      let iframe = document.getElementById("print-resident-iframe");
      if (!iframe) {
        iframe = document.createElement("iframe");
        iframe.id = "print-resident-iframe";
        iframe.style.position = "absolute";
        iframe.style.width = "0";
        iframe.style.height = "0";
        iframe.style.border = "none";
        document.body.appendChild(iframe);
      }

      const doc = iframe.contentWindow.document;
      doc.open();
      doc.write(html);
      doc.close();

      setTimeout(() => {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
      }, 400);
    },
   printResidentIdCard(r) {
  const logoUrl = window.location.origin + '/logo/logoimage (2).png';
  const userImgUrl = r.image ? (r.image.startsWith('http') ? r.image : window.location.origin + '/bookingsimage/' + r.image) : '';
  const roomNo = (r.room_items && r.room_items.length)
    ? r.room_items.map(i => this.getRoomNo(i.roomnumber)).join(', ')
    : (this.getRoomNo(r.roomnumber) || r.room_number || '-');
  const seatNo = (r.room_items && r.room_items.length)
    ? r.room_items.map(i => this.getSeatNo(i.roomnumber)).join(', ')
    : (this.getSeatNo(r.roomnumber) || '-');
  const floorNo = (r.room_items && r.room_items.length)
    ? [...new Set(r.room_items.map(i => i.floornumber))].filter(Boolean).join(', ')
    : (r.floornumber || '-');
  const fullName = (r.full_name || '-').trim();
  const phone = r.phone || '-';
  const userType = r.user_type || 'STUDENT';
  const idNo = String(r.id || '1001').padStart(4, '0');
  const checkIn = r.check_in ? String(r.check_in).slice(0, 10) : (r.created_at ? String(r.created_at).slice(0, 10) : '-');

  const html = `
    <!DOCTYPE html>
    <html lang="bn">
    <head>
      <meta charset="UTF-8">
      <title>Resident ID Card - ${fullName}</title>
      <style>
        @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@500;600;700&family=Montserrat:wght@500;600;700;800&display=swap');
        
        @page { size: A4 portrait; margin: 0; }
        * {
          box-sizing: border-box;
          margin: 0;
          padding: 0;
          -webkit-print-color-adjust: exact !important;
          print-color-adjust: exact !important;
          color-adjust: exact !important;
        }
        body {
          background: #e2e8f0;
          font-family: 'Montserrat', 'Hind Siliguri', sans-serif;
          display: flex;
          justify-content: center;
          align-items: center;
          min-height: 100vh;
          padding: 20px;
        }
        
        .id-card-frame {
          width: 86mm;
          height: 138mm;
          background: #f1f5f9 !important;
          border-radius: 20px;
          box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
          overflow: hidden;
          position: relative;
          display: flex;
          flex-direction: column;
          justify-content: space-between;
          page-break-inside: avoid;
          border: 1px solid #94a3b8;
        }

        /* Top Header Container (Dark Forest Green) */
        .header-bg {
          background: linear-gradient(180deg, #124d35 0%, #0a3222 100%) !important;
          position: relative;
          padding: 16px 12px 0 12px;
          text-align: center;
          height: 125px;
          color: #ffffff !important;
          border-bottom: 2px solid #b3883b;
        }
        
        /* Security Guilloche Mandala Overlay Pattern */
        .guilloche-bg {
          position: absolute;
          inset: 0;
          opacity: 0.15;
          background-image: radial-gradient(#10b981 1.5px, transparent 1.5px), radial-gradient(#34d399 1.5px, transparent 1.5px);
          background-size: 16px 16px;
          background-position: 0 0, 8px 8px;
          pointer-events: none;
        }

        .brand-row {
          display: flex;
          align-items: center;
          justify-content: center;
          gap: 10px;
          position: relative;
          z-index: 10;
        }
        .logo-img {
          width: 38px;
          height: 38px;
          object-fit: contain;
          background: #ffffff;
          padding: 2px;
          border-radius: 50%;
          border: 1.5px solid #d4af37;
          box-shadow: 0 3px 8px rgba(0,0,0,0.3);
        }
        .brand-text {
          text-align: left;
        }
        .brand-name {
          font-family: 'Hind Siliguri', sans-serif;
          font-size: 20px;
          font-weight: 700;
          color: #ffffff !important;
          line-height: 1.1;
        }
        .brand-sub {
          font-family: 'Hind Siliguri', sans-serif;
          font-size: 9.5px;
          color: #e2e8f0 !important;
          font-weight: 500;
          letter-spacing: 0.3px;
        }

        /* Profile Photo Section (Golden Double Ring) */
        .photo-wrapper {
          position: absolute;
          bottom: -50px;
          left: 50%;
          transform: translateX(-50%);
          z-index: 15;
        }
        .photo-circle {
          width: 96px;
          height: 96px;
          border-radius: 50%;
          border: 3px solid #d4af37 !important;
          outline: 2px solid #ffffff;
          overflow: hidden;
          background: #ffffff;
          box-shadow: 0 8px 20px rgba(0,0,0,0.3);
          display: flex;
          align-items: center;
          justify-content: center;
        }
        .photo-circle img {
          width: 100%;
          height: 100%;
          object-fit: cover;
        }
        .photo-placeholder {
          width: 100%;
          height: 100%;
          display: flex;
          align-items: center;
          justify-content: center;
          background: #e2e8f0;
        }

        /* Main Body Content (Brushed Metallic Texture) */
        .card-body {
          flex: 1;
          padding: 56px 16px 4px 16px;
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: space-between;
          z-index: 10;
          background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
          position: relative;
        }

        /* Security Watermark SVG on Body Right */
        .body-watermark {
          position: absolute;
          right: -10px;
          top: 40%;
          width: 140px;
          height: 140px;
          opacity: 0.12;
          pointer-events: none;
        }

        .name-section {
          text-align: center;
          margin-bottom: 6px;
          width: 100%;
        }
        .resident-name {
          font-size: 21px;
          font-weight: 800;
          color: #0f172a;
          text-transform: uppercase;
          letter-spacing: 1px;
          line-height: 1.1;
          margin-bottom: 2px;
        }
        .resident-type {
          font-size: 11px;
          font-weight: 700;
          color: #124d35;
          text-transform: uppercase;
          letter-spacing: 1.5px;
        }

        /* Info Grid with Icons matching the Image */
        .info-list {
          width: 100%;
          padding: 0 4px;
        }
        .info-row {
          display: flex;
          align-items: center;
          font-size: 12.5px;
          margin-bottom: 5px;
        }
        .info-row:last-child { margin-bottom: 0; }
        
        .info-lbl-group {
          width: 100px;
          display: flex;
          align-items: center;
          gap: 8px;
        }
        .info-icon {
          width: 20px;
          height: 20px;
          background: #124d35;
          border-radius: 5px;
          display: flex;
          align-items: center;
          justify-content: center;
          flex-shrink: 0;
          box-shadow: 0 2px 4px rgba(0,0,0,0.15);
        }
        .info-icon svg {
          fill: #ffffff;
          width: 12px;
          height: 12px;
        }
        .info-lbl {
          font-weight: 700;
          color: #0f172a;
          font-size: 12px;
        }
        .info-colon {
          width: 14px;
          text-align: center;
          font-weight: 800;
          color: #0f172a;
        }
        .info-val {
          flex: 1;
          font-weight: 800;
          font-size: 13px;
          color: #0f172a;
          text-align: left;
        }

        /* Signature Row */
        .sig-row {
          display: flex;
          justify-content: flex-end;
          width: 100%;
          padding: 0 4px;
          margin-top: 2px;
        }
        .sig-box { text-align: center; }
        .sig-line { border-top: 1.5px solid #0f172a; width: 95px; margin-bottom: 2px; }
        .sig-lbl { font-size: 9px; font-weight: 700; color: #334155; font-family: 'Hind Siliguri', sans-serif; }

        /* Footer Section (Rich Green Bar) */
        .footer-bg {
          position: relative;
          background: linear-gradient(180deg, #124d35 0%, #0a3222 100%) !important;
          padding: 9px 8px;
          text-align: center;
          border-top: 2px solid #d4af37;
        }
        .footer-content {
          position: relative;
          z-index: 10;
          font-size: 8.5px;
          font-weight: 600;
          color: #ffffff !important;
          font-family: 'Hind Siliguri', sans-serif;
          line-height: 1.2;
        }

        @media print {
          * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
          }
          body { background: #fff !important; padding: 0 !important; }
          .id-card-frame { box-shadow: none !important; border: 1px solid #94a3b8; }
        }
      </style>
    </head>
    <body>
      <div class="id-card-frame">
        
        <!-- Top Header -->
        <div class="header-bg">
          <div class="guilloche-bg"></div>
          <div class="brand-row">
            <img src="${logoUrl}" alt="Logo" class="logo-img" onerror="this.style.display='none'">
            <div class="brand-text">
              <div class="brand-name">টি এস এস ভিলা</div>
              <div class="brand-sub">ছাত্রী নিবাস ও হোস্টেল</div>
            </div>
          </div>

          <!-- Profile Photo Frame -->
          <div class="photo-wrapper">
            <div class="photo-circle">
              ${userImgUrl 
                ? `<img src="${userImgUrl}" alt="Photo" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';"><div class="photo-placeholder" style="display:none;"><svg width="42" height="42" viewBox="0 0 24 24" fill="#64748b"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg></div>` 
                : `<div class="photo-placeholder"><svg width="42" height="42" viewBox="0 0 24 24" fill="#64748b"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg></div>`}
            </div>
          </div>
        </div>

        <!-- Body Content -->
        <div class="card-body">
          <svg class="body-watermark" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="45" stroke="#124d35" stroke-width="0.5" fill="none" stroke-dasharray="1,1"/>
            <circle cx="50" cy="50" r="35" stroke="#124d35" stroke-width="0.5" fill="none"/>
            <path d="M50,5 L50,95 M5,50 L95,50" stroke="#124d35" stroke-width="0.3"/>
          </svg>

          <div class="name-section">
            <h2 class="resident-name">${fullName}</h2>
            <div class="resident-type">${userType}</div>
          </div>

          <div class="info-list">
            <!-- Id No -->
            <div class="info-row">
              <div class="info-lbl-group">
                <div class="info-icon"><svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3 3zm6 12H6v-1c0-2 4-3.1 6-3.1s6 1.1 6 3.1v1z"/></svg></div>
                <div class="info-lbl">Id No</div>
              </div>
              <div class="info-colon">:</div>
              <div class="info-val">TSS-${idNo}</div>
            </div>

            <!-- Phone -->
            <div class="info-row">
              <div class="info-lbl-group">
                <div class="info-icon"><svg viewBox="0 0 24 24"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg></div>
                <div class="info-lbl">Phone</div>
              </div>
              <div class="info-colon">:</div>
              <div class="info-val">${phone}</div>
            </div>

            <!-- Floor -->
            <div class="info-row">
              <div class="info-lbl-group">
                <div class="info-icon"><svg viewBox="0 0 24 24"><path d="M12 3L2 12h3v8h14v-8h3L12 3zm1 15h-2v-2h2v2zm0-4h-2v-2h2v2z"/></svg></div>
                <div class="info-lbl">Floor</div>
              </div>
              <div class="info-colon">:</div>
              <div class="info-val">${floorNo}</div>
            </div>

            <!-- Room No -->
            <div class="info-row">
              <div class="info-lbl-group">
                <div class="info-icon"><svg viewBox="0 0 24 24"><path d="M7 13c1.66 0 3-1.34 3-3S8.66 7 7 7s-3 1.34-3 3 1.34 3 3 3zm12-6h-8v10h2v-3h6v3h2V9c0-1.1-.9-2-2-2z"/></svg></div>
                <div class="info-lbl">Room No</div>
              </div>
              <div class="info-colon">:</div>
              <div class="info-val">${roomNo}</div>
            </div>

            <!-- Seat No -->
            <div class="info-row">
              <div class="info-lbl-group">
                <div class="info-icon"><svg viewBox="0 0 24 24"><path d="M4 18v3h2v-3h12v3h2v-3c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v8c0 1.1.9 2 2 2z"/></svg></div>
                <div class="info-lbl">Seat No</div>
              </div>
              <div class="info-colon">:</div>
              <div class="info-val">${seatNo}</div>
            </div>

            <!-- Join Date -->
            <div class="info-row">
              <div class="info-lbl-group">
                <div class="info-icon"><svg viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/></svg></div>
                <div class="info-lbl">Join Date</div>
              </div>
              <div class="info-colon">:</div>
              <div class="info-val">${checkIn}</div>
            </div>
          </div>

          <div class="sig-row">
            <div class="sig-box">
              <div class="sig-line"></div>
              <div class="sig-lbl">অনুমোদিত স্বাক্ষর</div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="footer-bg">
          <div class="footer-content">
            কলেজ রোড, নেসকো গেট সংলগ্ন, রংপুর | হেল্পলাইন: ০১৯৭৭২৭০৯২০
          </div>
        </div>

      </div>
    </body>
    </html>
  `;

  const win = window.open('', '_blank');
  if (win) {
    win.document.write(html);
    win.document.close();
    setTimeout(() => {
      win.focus();
      win.print();
    }, 400);
  }
},
    exportResidentsCSV() {
      if (!this.rooms || this.rooms.length === 0) {
        this.toast("এক্সপোর্ট করার মতো কোনো ডাটা পাওয়া যায়নি", "warning");
        return;
      }
      const headers = ["SL", "Full Name", "Phone", "Email", "NID", "User Type", "Room No", "Seat No", "Floor No", "Monthly Rent", "Check In", "Check Out", "Address", "District", "Thana"];
      const rows = this.rooms.map((r, index) => [
        index + 1,
        r.full_name || '',
        r.phone || '',
        r.email || '',
        r.nid || '',
        r.user_type || 'Student',
        (r.room_items && r.room_items.length) ? r.room_items.map(i => this.getRoomNo(i.roomnumber)).join(', ') : (this.getRoomNo(r.roomnumber) || r.room_number || ''),
        (r.room_items && r.room_items.length) ? r.room_items.map(i => this.getSeatNo(i.roomnumber)).join(', ') : (this.getSeatNo(r.roomnumber) || ''),
        (r.room_items && r.room_items.length) ? [...new Set(r.room_items.map(i => i.floornumber))].filter(Boolean).join(', ') : (r.floornumber || ''),
        r.monthly_amount || 0,
        r.check_in || '',
        r.check_out || '',
        r.address || '',
        r.district_name || '',
        r.thana_name || ''
      ]);
      this.downloadCSV("Resident_Bookings_List_" + new Date().toISOString().slice(0,10) + ".csv", headers, rows);
    },

    downloadCSV(filename, headers, rows) {
      let csvContent = "\uFEFF";
      csvContent += headers.map(h => `"${String(h).replace(/"/g, '""')}"`).join(",") + "\n";
      rows.forEach(row => {
        csvContent += row.map(cell => `"${String(cell ?? '').replace(/"/g, '""')}"`).join(",") + "\n";
      });
      const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
      const link = document.createElement("a");
      link.href = URL.createObjectURL(blob);
      link.setAttribute("download", filename);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    },
  },
};
</script>
<style scoped>
.img-thumb {
  width: 90px;
  height: 60px;
  object-fit: cover;
  border-radius: 10px;
  border: 1px solid #e5e7eb;
  background: #fff;
}

.form-control,
.form-select {
  border-radius: 8px;
  padding: 0.6rem 0.75rem;
  border: 1px solid #dce0e4;
}

.form-control:focus,
.form-select:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
}

.table td {
  vertical-align: top;
}

.table thead th {
  vertical-align: middle;
  text-align: center;
  font-size: 13px;
  font-weight: 700;
  color: #374151;
  background: #f3f4f6;
}

.table tbody td {
  padding-top: 14px;
  padding-bottom: 14px;
}

/* Booking Card */
.booking-card {
  width: 100%;
  background: #ffffff;
  border-radius: 14px;
  overflow: hidden;
  border: 1px solid #dbe2ea;
}

/* Each booking row */
.booking-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 16px;
  transition: all 0.2s ease;
}

.booking-row:not(:last-child) {
  border-bottom: 1px solid #edf2f7;
}

.booking-row:hover {
  background: #f9fafb;
}

/* Common column */
.booking-col {
  flex: 1;
  display: flex;
  align-items: center;
}

/* Floor */
.booking-col.floor {
  font-size: 14px;
  font-weight: 600;
  color: #6b7280;
  text-transform: capitalize;
}

/* Room */
.booking-col.room {
  justify-content: center;
}

/* Price */
.booking-col.price {
  justify-content: flex-end;
  font-size: 15px;
  font-weight: 700;
  color: #111827;
}

/* Premium room badge */
.room-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 58px;
  height: 34px;
  padding: 0 14px;
  border-radius: 10px;
  background: linear-gradient(135deg, #111827, #1f2937);
  color: #ff4d4f;
  font-size: 14px;
  font-weight: 700;
  letter-spacing: 0.4px;
  box-shadow: 0 5px 12px rgba(17, 24, 39, 0.18);
  border: 1px solid rgba(255, 255, 255, 0.06);
}

/* Total row */
.booking-total {
  padding: 10px 16px;
  border-top: 1px solid #dbe2ea;
  background: #f8fafc;
  font-size: 15px;
  font-weight: 700;
  color: #4b5563;
  text-align: left;
}

/* Old helper classes kept if used elsewhere */
.booking-stack {
  display: flex;
  flex-direction: column;
  gap: 10px;
  min-width: 110px;
}

.booking-line {
  min-height: 36px;
  display: flex;
  align-items: center;
}

.floor-text {
  display: inline-block;
  font-size: 14px;
  font-weight: 600;
  line-height: 1.35;
  color: #4b5563;
  text-transform: capitalize;
}

.room-chip {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 58px;
  height: 34px;
  padding: 0 12px;
  border-radius: 10px;
  background: linear-gradient(135deg, #111827, #1f2937);
  color: #ff4d4f;
  font-size: 14px;
  font-weight: 700;
  letter-spacing: 0.3px;
  box-shadow: 0 4px 10px rgba(17, 24, 39, 0.18);
  border: 1px solid rgba(255, 255, 255, 0.06);
}

.price-text {
  display: inline-block;
  font-size: 15px;
  font-weight: 700;
  color: #374151;
}

@media (max-width: 768px) {
  .booking-stack {
    min-width: 90px;
    gap: 8px;
  }

  .booking-line {
    min-height: 32px;
  }

  .room-chip,
  .room-badge {
    min-width: 50px;
    height: 30px;
    font-size: 13px;
    padding: 0 10px;
  }

  .floor-text,
  .price-text {
    font-size: 13px;
  }

  .booking-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 6px;
  }

  .booking-col {
    width: 100%;
    justify-content: space-between !important;
  }

  .booking-total {
    font-size: 14px;
    padding: 10px 14px;
  }
}
</style>