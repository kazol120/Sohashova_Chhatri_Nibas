<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-12">
        <div class="card mb-4 shadow-sm">
          <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center py-3 bg-light">
            <h5 class="card-title mb-0 text-primary fw-bold">
              <i class="ti ti-arrows-exchange me-2"></i>Change Room / Seat Manager (রুম ও সিট পরিবর্তন)
            </h5>

            <!-- NAV TABS -->
            <ul class="nav nav-pills card-header-pills">
              <li class="nav-item">
                <button
                  class="nav-link fw-bold"
                  :class="{ active: activeTab === 'residents' }"
                  @click="switchTab('residents')"
                >
                  <i class="ti ti-users me-1"></i> Active Residents
                </button>
              </li>
              <li class="nav-item">
                <button
                  class="nav-link fw-bold"
                  :class="{ active: activeTab === 'history' }"
                  @click="switchTab('history')"
                >
                  <i class="ti ti-history me-1"></i> Change History & Fees (৳ 500)
                </button>
              </li>
            </ul>
          </div>

          <!-- TAB 1: ACTIVE RESIDENTS -->
          <div class="card-body mt-3" v-if="activeTab === 'residents'">
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
              <div class="d-flex align-items-center gap-2">
                <label class="small text-muted mb-0">Rows:</label>
                <select class="form-select form-select-sm" style="width: 90px" v-model.number="perPage">
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
                  style="width: 280px"
                  placeholder="Search guest name / phone / room..."
                  v-model="search"
                />
              </div>
            </div>

            <div class="table-responsive">
              <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th style="width: 60px">Sl</th>
                    <th style="width: 200px">Resident Name</th>
                    <th style="width: 140px">Phone</th>
                    <th style="width: 140px">Current Floor</th>
                    <th style="width: 140px">Current Room</th>
                    <th style="width: 140px">Current Seat</th>
                    <th style="width: 140px">Monthly Amount</th>
                    <th style="width: 160px" class="text-center">Action</th>
                  </tr>
                </thead>

                <tbody v-if="residents.length">
                  <tr v-for="(r, idx) in residents" :key="r.id">
                    <td>{{ from + idx + 1 }}</td>
                    <td>
                      <div class="fw-bold text-dark">{{ r.full_name || "-" }}</div>
                      <small class="text-muted">{{ r.user_type || 'Student' }}</small>
                    </td>
                    <td>
                      <span class="fw-semibold text-secondary">{{ r.phone || "-" }}</span>
                    </td>
                    <td>
                      <span class="fw-semibold text-dark">{{ r.floornumber || "-" }}</span>
                    </td>
                    <td>
                      <span class="badge bg-primary px-2 py-1 font-monospace fs-6">{{ getRoomNo(r.roomnumber) }}</span>
                    </td>
                    <td>
                      <span class="badge bg-danger px-2 py-1 font-monospace fs-6">{{ getSeatNo(r) }}</span>
                    </td>
                    <td>
                      <span class="fw-bold text-success">৳ {{ formatCurrency(r.monthly_amount) }}</span>
                    </td>
                    <td class="text-center">
                      <div class="d-flex align-items-center justify-content-center gap-1">
                        <button
                          class="btn btn-sm btn-outline-primary fw-bold text-nowrap"
                          @click="openChangeModal(r)"
                          title="Change Room / Seat"
                        >
                          <i class="ti ti-arrows-exchange me-1"></i> Change
                        </button>
                        <button
                          class="btn btn-sm btn-outline-info fw-bold text-nowrap"
                          @click="openResidentHistoryModal(r)"
                          title="View Room Change History"
                        >
                          <i class="ti ti-history me-1"></i> History
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>

                <tbody v-else>
                  <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                      <span v-if="loading">
                        <i class="fa fa-spinner fa-spin me-2"></i> Loading active residents...
                      </span>
                      <span v-else>No active residents found</span>
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
                  @click="fetchResidents(currentPage - 1)"
                >
                  Previous
                </button>

                <button
                  class="btn btn-sm btn-secondary"
                  :disabled="currentPage >= totalPages || loading"
                  @click="fetchResidents(currentPage + 1)"
                >
                  Next
                </button>
              </div>
            </div>
          </div>

          <!-- TAB 2: CHANGE HISTORY & FEES LOG -->
          <div class="card-body mt-3" v-if="activeTab === 'history'">
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
              <div class="fw-bold text-dark fs-6">
                <i class="ti ti-file-analytics me-1 text-primary"></i> Room Change & Fee Records
              </div>

              <div class="d-flex gap-2 align-items-center">
                <input
                  type="text"
                  class="form-control form-control-sm"
                  style="width: 280px"
                  placeholder="Search history by name / phone / seat..."
                  v-model="historiesSearch"
                />
              </div>
            </div>

            <div class="table-responsive">
              <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th style="width: 50px">Sl</th>
                    <th style="width: 170px">Resident</th>
                    <th style="width: 160px">From (Old)</th>
                    <th style="width: 160px">To (New)</th>
                    <th style="width: 120px">Change Fee</th>
                    <th style="width: 130px">Payment Method</th>
                    <th style="width: 110px">Status</th>
                    <th style="width: 140px">Date & Time</th>
                    <th style="width: 80px" class="text-center">Receipt</th>
                  </tr>
                </thead>

                <tbody v-if="histories.length">
                  <tr v-for="(h, idx) in histories" :key="h.id">
                    <td>{{ (historiesPage - 1) * 10 + idx + 1 }}</td>
                    <td>
                      <div class="fw-bold text-dark">{{ h.resident_name }}</div>
                      <small class="text-muted">{{ h.phone }}</small>
                    </td>
                    <td>
                      <small class="text-secondary d-block">{{ h.old_floor || '-' }}</small>
                      <span class="badge bg-secondary px-2 py-1">{{ h.old_room_seat || '-' }}</span>
                      <small class="d-block text-muted">৳ {{ formatCurrency(h.old_monthly_amount) }}</small>
                    </td>
                    <td>
                      <small class="text-primary d-block font-weight-bold">{{ h.new_floor || '-' }}</small>
                      <span class="badge bg-success px-2 py-1">{{ h.new_room_seat || '-' }}</span>
                      <small class="d-block text-success fw-bold">৳ {{ formatCurrency(h.new_monthly_amount) }}</small>
                    </td>
                    <td>
                      <span class="fw-bold text-primary fs-6">৳ {{ formatCurrency(h.fee_amount) }}</span>
                    </td>
                    <td>
                      <span class="badge bg-label-info text-capitalize">{{ h.payment_method || 'Cash' }}</span>
                    </td>
                    <td>
                      <span
                        class="badge"
                        :class="h.payment_status === 'paid' ? 'bg-success' : 'bg-warning text-dark'"
                      >
                        {{ h.payment_status === 'paid' ? 'Paid' : 'Unpaid' }}
                      </span>
                    </td>
                    <td>
                      <small class="text-dark fw-semibold">{{ formatDate(h.created_at) }}</small>
                    </td>
                    <td class="text-center">
                      <button
                        class="btn btn-sm btn-outline-dark fw-semibold text-nowrap"
                        @click="openReceiptModal(h)"
                        title="Print Money Receipt"
                      >
                        <i class="ti ti-printer me-1"></i> Print
                      </button>
                    </td>
                  </tr>
                </tbody>

                <tbody v-else>
                  <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                      <span v-if="historiesLoading">
                        <i class="fa fa-spinner fa-spin me-2"></i> Loading history records...
                      </span>
                      <span v-else>No room change records found</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
              <div class="small text-muted">
                Page: {{ historiesPage }} / {{ historiesTotalPages }}
              </div>

              <div class="d-flex align-items-center gap-2">
                <button
                  class="btn btn-sm btn-secondary"
                  :disabled="historiesPage <= 1 || historiesLoading"
                  @click="fetchHistories(historiesPage - 1)"
                >
                  Previous
                </button>

                <button
                  class="btn btn-sm btn-secondary"
                  :disabled="historiesPage >= historiesTotalPages || historiesLoading"
                  @click="fetchHistories(historiesPage + 1)"
                >
                  Next
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal for Changing Room/Seat -->
    <div
      class="modal fade"
      id="changeRoomModal"
      tabindex="-1"
      aria-hidden="true"
      ref="changeModalRef"
    >
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0">
          <div class="modal-header bg-primary text-white py-3">
            <h5 class="modal-title text-white fw-bold">
              <i class="ti ti-arrows-exchange me-2"></i>Change Room & Seat
            </h5>
            <button
              type="button"
              class="btn-close btn-close-white"
              data-bs-dismiss="modal"
              aria-label="Close"
            ></button>
          </div>

          <div class="modal-body p-4" v-if="selectedResident">
            <!-- Current Allocation Alert -->
            <div class="alert alert-info border-info d-flex align-items-center gap-3 mb-4">
              <div class="fs-2 text-info"><i class="ti ti-user-check"></i></div>
              <div>
                <h6 class="mb-1 fw-bold text-dark">{{ selectedResident.full_name }} ({{ selectedResident.phone }})</h6>
                <div class="small text-secondary">
                  <strong>Current:</strong> {{ selectedResident.floornumber || 'Floor' }} | Room: <strong>{{ getRoomNo(selectedResident.roomnumber) }}</strong> | Seat: <strong>{{ getSeatNo(selectedResident) }}</strong> (৳ {{ formatCurrency(selectedResident.monthly_amount) }})
                </div>
              </div>
            </div>

            <!-- Target Selection Form -->
            <div class="row g-3">
              <!-- Select New Floor -->
              <div class="col-md-4">
                <label class="form-label fw-bold">1. Select New Floor <span class="text-danger">*</span></label>
                <select class="form-select" v-model="selectedFloorId" @change="onFloorChange">
                  <option value="">-- Choose Floor --</option>
                  <option v-for="fl in availableTree" :key="fl.id" :value="fl.id">
                    {{ fl.name }}
                  </option>
                </select>
              </div>

              <!-- Select New Room -->
              <div class="col-md-4">
                <label class="form-label fw-bold">2. Select New Room <span class="text-danger">*</span></label>
                <select
                  class="form-select"
                  v-model="selectedRoomId"
                  :disabled="!filteredRooms.length"
                  @change="onRoomChange"
                >
                  <option value="">-- Choose Room --</option>
                  <option v-for="rm in filteredRooms" :key="rm.id" :value="rm.id">
                    Room {{ rm.room_no }}
                  </option>
                </select>
              </div>

              <!-- Select New Seat -->
              <div class="col-md-4">
                <label class="form-label fw-bold">3. Select Vacant Seat <span class="text-danger">*</span></label>
                <select
                  class="form-select"
                  v-model="selectedSeatId"
                  :disabled="!filteredSeats.length"
                >
                  <option value="">-- Choose Seat --</option>
                  <option v-for="st in filteredSeats" :key="st.id" :value="st.id">
                    Seat {{ st.seat_no }} (৳ {{ formatCurrency(st.price) }})
                  </option>
                </select>
              </div>
            </div>

            <!-- ROOM CHANGE FEE FIELD -->
            <div class="row g-3 mt-1">
              <div class="col-md-12">
                <label class="form-label fw-bold">Change Fee (৳) <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text fw-bold bg-white text-primary">৳</span>
                  <input
                    type="number"
                    class="form-control fw-bold fs-5 text-primary"
                    v-model.number="feeAmount"
                    min="0"
                    placeholder="500"
                  />
                </div>
              </div>
            </div>

            <!-- Preview Transfer Details -->
            <div v-if="selectedSeatObj" class="alert alert-success border-success mt-4 mb-0">
              <div class="fw-bold text-success mb-1">
                <i class="ti ti-check me-1"></i> New Allocation Summary:
              </div>
              <div class="fs-6 text-dark">
                <strong>New Location:</strong> {{ selectedFloorObj?.name }} &rarr; Room <strong>{{ selectedRoomObj?.room_no }}</strong> &rarr; Seat <strong>{{ selectedSeatObj?.seat_no }}</strong><br>
                <strong>New Monthly Rate:</strong> ৳ {{ formatCurrency(selectedSeatObj?.price) }} | <strong>Room Change Fee:</strong> ৳ {{ formatCurrency(feeAmount) }}
              </div>
            </div>
          </div>

          <div class="modal-footer bg-light py-3">
            <button type="button" class="btn btn-secondary fw-semibold" data-bs-dismiss="modal">
              Cancel
            </button>
            <button
              type="button"
              class="btn btn-primary fw-bold"
              :disabled="!selectedSeatId || submitting"
              @click="submitRoomChange"
            >
              <span v-if="submitting"><i class="fa fa-spinner fa-spin me-1"></i> Transferring...</span>
              <span v-else><i class="ti ti-check me-1"></i> Confirm Room Change (৳ {{ feeAmount }})</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL: INDIVIDUAL RESIDENT CHANGE HISTORY -->
    <div
      class="modal fade"
      id="residentHistoryModal"
      tabindex="-1"
      aria-labelledby="residentHistoryModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0">
          <div class="modal-header bg-info-light py-3 border-bottom">
            <div class="d-flex align-items-center gap-2">
              <div class="avatar avatar-sm bg-info text-white rounded-circle d-flex align-items-center justify-content-center">
                <i class="ti ti-history fs-5"></i>
              </div>
              <div>
                <h5 class="modal-title fw-bold text-dark mb-0" id="residentHistoryModalLabel">
                  {{ historyModalResident?.full_name || 'Resident' }}'s Room Change History
                </h5>
                <small class="text-muted">Phone: {{ historyModalResident?.phone || '-' }} | Current: {{ historyModalResident?.floornumber || '-' }} ({{ historyModalResident?.roomnumber || '-' }})</small>
              </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body p-3 p-md-4">
            <!-- Summary Stats Pill -->
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center p-3 mb-3 bg-light rounded-3 border">
              <div>
                <span class="text-muted small">Total Changes:</span>
                <span class="badge bg-primary fs-6 ms-1">{{ residentHistories.length }} Times</span>
              </div>
              <div>
                <span class="text-muted small">Total Change Fees Paid:</span>
                <span class="badge bg-success fs-6 ms-1">৳ {{ formatCurrency(totalResidentFees) }}</span>
              </div>
            </div>

            <!-- Loader -->
            <div v-if="residentHistoriesLoading" class="text-center py-5 text-muted">
              <i class="fa fa-spinner fa-spin fs-3 text-primary mb-2"></i>
              <div>Loading change history...</div>
            </div>

            <!-- History List Table -->
            <div v-else-if="residentHistories.length" class="table-responsive">
              <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th style="width: 45px">#</th>
                    <th>Previous (Old)</th>
                    <th>New (To)</th>
                    <th>Change Fee</th>
                    <th>Date & Time</th>
                    <th style="width: 80px" class="text-center">Receipt</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(h, idx) in residentHistories" :key="h.id">
                    <td class="fw-bold text-center text-muted">{{ residentHistories.length - idx }}</td>
                    <td>
                      <div class="small text-muted">{{ h.old_floor || '-' }}</div>
                      <span class="badge bg-secondary font-monospace px-2 py-0.5">{{ h.old_room_seat || '-' }}</span>
                      <div class="small text-muted mt-0.5" v-if="h.old_monthly_amount">৳ {{ formatCurrency(h.old_monthly_amount) }}/mo</div>
                    </td>
                    <td>
                      <div class="small text-primary fw-semibold">{{ h.new_floor || '-' }}</div>
                      <span class="badge bg-success font-monospace px-2 py-0.5">{{ h.new_room_seat || '-' }}</span>
                      <div class="small text-success fw-bold mt-0.5" v-if="h.new_monthly_amount">৳ {{ formatCurrency(h.new_monthly_amount) }}/mo</div>
                    </td>
                    <td>
                      <div class="fw-bold text-primary">৳ {{ formatCurrency(h.fee_amount) }}</div>
                      <span class="badge bg-success px-2 py-0.5" style="font-size: 0.72rem;">{{ h.payment_status || 'Paid' }}</span>
                    </td>
                    <td>
                      <div class="small fw-semibold text-dark">{{ formatDate(h.created_at) }}</div>
                      <small class="text-muted">{{ h.payment_method || 'Cash' }}</small>
                    </td>
                    <td class="text-center">
                      <button
                        class="btn btn-sm btn-outline-dark fw-semibold text-nowrap"
                        @click="openReceiptModal(h)"
                        title="Print Money Receipt"
                      >
                        <i class="ti ti-printer me-1"></i> Print
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-5 text-muted">
              <i class="ti ti-mood-smile fs-1 text-secondary mb-2 d-block"></i>
              <h6 class="fw-bold text-dark">No room changes recorded</h6>
              <p class="small mb-0">This resident has not changed their room or seat yet.</p>
            </div>
          </div>

          <div class="modal-footer bg-light py-2">
            <button type="button" class="btn btn-secondary fw-semibold btn-sm" data-bs-dismiss="modal">
              Close
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- PRINTABLE ROOM CHANGE MONEY RECEIPT MODAL -->
    <div
      class="modal fade"
      id="roomChangeReceiptModal"
      tabindex="-1"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0">
          <div class="modal-header bg-dark text-white py-2 px-3">
            <h6 class="modal-title text-white mb-0 fw-bold">
              <i class="ti ti-printer me-2"></i>Room & Seat Change Money Receipt (রুম ও সিট পরিবর্তন মানি রিসিট)
            </h6>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body p-4" id="printableRoomChangeReceiptArea" v-if="receiptData">
            <!-- Receipt Template Box -->
            <div class="receipt-box p-4 border border-2 border-dark rounded-3 bg-white" style="font-family: 'Segoe UI', Arial, sans-serif;">
              <div class="text-center border-bottom pb-3 mb-3">
                <h3 class="fw-bold mb-1" style="color: #033364;">টি এস এস ভিলা</h3>
                <div class="small text-dark fw-semibold">কলেজ রোড, নেসকোগেট সংলগ্ন, রংপুর | ফোন: +8801977270920</div>
                <div class="badge bg-dark fs-6 px-3 py-1 mt-2 text-uppercase">ROOM & SEAT CHANGE MONEY RECEIPT</div>
              </div>

              <div class="d-flex justify-content-between align-items-center mb-3 small text-muted">
                <div>Receipt No: <strong>#CHG-{{ receiptData.id }}</strong></div>
                <div>Date & Time: <strong>{{ formatDate(receiptData.created_at) }}</strong></div>
              </div>

              <table class="table table-sm table-bordered align-middle mb-4">
                <tbody>
                  <tr>
                    <th style="width: 32%" class="bg-light">Resident Name:</th>
                    <td class="fw-bold fs-6">{{ receiptData.resident_name || '-' }}</td>
                  </tr>
                  <tr>
                    <th class="bg-light">Phone Number:</th>
                    <td>{{ receiptData.phone || '-' }}</td>
                  </tr>
                  <tr>
                    <th class="bg-light">Previous Location (পূর্বের রুম):</th>
                    <td>
                      <span class="text-muted">{{ receiptData.old_floor || '-' }} &rarr; Room-Seat: <strong>{{ receiptData.old_room_seat || '-' }}</strong></span>
                      <span class="text-muted small ms-2" v-if="receiptData.old_monthly_amount">(মাসিক ভাড়া: ৳ {{ formatCurrency(receiptData.old_monthly_amount) }})</span>
                    </td>
                  </tr>
                  <tr class="table-success">
                    <th class="bg-success-subtle text-dark fw-bold">New Location (নতুন রুম ও সিট):</th>
                    <td class="fw-bold text-dark">
                      <span class="text-primary">{{ receiptData.new_floor || '-' }}</span> &rarr; Room-Seat: <strong class="badge bg-success fs-6 font-monospace px-2 py-0.5">{{ receiptData.new_room_seat || '-' }}</strong>
                      <span class="text-success small ms-2" v-if="receiptData.new_monthly_amount">(নতুন মাসিক ভাড়া: ৳ {{ formatCurrency(receiptData.new_monthly_amount) }})</span>
                    </td>
                  </tr>
                  <tr>
                    <th class="bg-light">Payment Purpose:</th>
                    <td class="fw-bold text-primary">Room & Seat Change Fee (রুম ও সিট পরিবর্তন ফি)</td>
                  </tr>
                  <tr>
                    <th class="bg-light">Change Fee Paid (প্রদত্ত ফি):</th>
                    <td class="fw-bold fs-5 text-success">৳ {{ formatCurrency(receiptData.fee_amount) }}</td>
                  </tr>
                  <tr>
                    <th class="bg-light">Payment Status & Method:</th>
                    <td>
                      <span class="badge bg-success px-3 py-1 fs-6 me-2">PAID / পরিশোধিত</span>
                      <span class="badge bg-secondary px-2 py-1 fs-6 text-capitalize">{{ receiptData.payment_method || 'Cash' }}</span>
                    </td>
                  </tr>
                  <tr v-if="receiptData.remarks">
                    <th class="bg-light">Remarks / মন্তব্য:</th>
                    <td>{{ receiptData.remarks }}</td>
                  </tr>
                </tbody>
              </table>

              <!-- Rules Section -->
              <div class="mt-4 pt-2 border-top">
                <div class="d-inline-block bg-danger text-white fw-bold px-3 py-1 rounded-pill mb-2" style="font-size: 13px;">
                  নিয়মাবলী
                </div>
                <ul class="list-unstyled mb-3" style="font-size: 12.5px; line-height: 1.8; color: #1f2937;">
                  <li>❖ প্রতি মাসের ভাড়া ঐ মাসের ৭ তারিখের মধ্যে দিতে হবে।</li>
                  <li>❖ সীট ছাড়তে চাইলে অবশ্যই ২ মাস আগে লিখিত জানাতে হবে। অন্যথায় ২ মাসের ভাড়া দিয়ে মেস ছাড়তে হবে।</li>
                  <li>❖ লাইট/ফ্যান/দেয়াল সহ অন্যান্য জিনিস নষ্ট করলে ক্ষতিপূরণ দিতে হবে।</li>
                </ul>
              </div>

              <div class="d-flex justify-content-between align-items-end mt-4 pt-3">
                <div class="text-center" style="width: 180px;">
                  <div style="border-top: 1px dashed #666; padding-top: 4px;" class="small fw-semibold">
                    শিক্ষার্থীর স্বাক্ষর
                  </div>
                </div>
                <div class="text-center" style="width: 200px;">
                  <div style="border-top: 1px solid #000; padding-top: 4px;" class="small fw-bold">
                    অফিস কর্তৃপক্ষের স্বাক্ষর
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-footer bg-light py-2">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary fw-bold" @click="printReceiptNow">
              <i class="ti ti-printer me-1"></i> Print Receipt Now
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
  name: "RoomChangeManager",

  data() {
    return {
      activeTab: "residents", // 'residents' or 'history'
      residents: [],
      loading: false,
      search: "",
      perPage: 10,
      currentPage: 1,
      totalPages: 1,
      total: 0,
      from: 0,
      _t: null,
      _ht: null,

      availableTree: [],
      selectedResident: null,
      selectedFloorId: "",
      selectedRoomId: "",
      selectedSeatId: "",

      // Room Change Fee & Payment Fields
      feeAmount: 500,
      paymentMethod: "Cash",
      paymentStatus: "paid",
      remarks: "",

      submitting: false,
      modalInstance: null,

      // Individual Resident History Modal Data
      historyModalResident: null,
      residentHistories: [],
      residentHistoriesLoading: false,
      historyModalInstance: null,

      // Receipt Print Data
      receiptData: null,
      receiptModalInstance: null,

      // History Tab Data
      histories: [],
      historiesLoading: false,
      historiesPage: 1,
      historiesTotalPages: 1,
      historiesSearch: "",
    };
  },

  computed: {
    totalResidentFees() {
      return this.residentHistories.reduce((sum, h) => sum + Number(h.fee_amount || 0), 0);
    },
    url() {
      return this.$store.state.url;
    },

    selectedFloorObj() {
      if (!this.selectedFloorId) return null;
      return this.availableTree.find(f => String(f.id) === String(this.selectedFloorId)) || null;
    },

    filteredRooms() {
      if (!this.selectedFloorObj) return [];
      return this.selectedFloorObj.rooms_list || [];
    },

    selectedRoomObj() {
      if (!this.selectedRoomId) return null;
      return this.filteredRooms.find(r => String(r.id) === String(this.selectedRoomId)) || null;
    },

    filteredSeats() {
      if (!this.selectedRoomObj) return [];
      return this.selectedRoomObj.available_seats || [];
    },

    selectedSeatObj() {
      if (!this.selectedSeatId) return null;
      return this.filteredSeats.find(s => String(s.id) === String(this.selectedSeatId)) || null;
    },
  },

  mounted() {
    this.fetchResidents(1);
    this.fetchAvailableTree();
  },

  watch: {
    search() {
      clearTimeout(this._t);
      this._t = setTimeout(() => this.fetchResidents(1), 300);
    },
    perPage() {
      this.fetchResidents(1);
    },
    historiesSearch() {
      clearTimeout(this._ht);
      this._ht = setTimeout(() => this.fetchHistories(1), 300);
    },
  },

  beforeUnmount() {
    clearTimeout(this._t);
    clearTimeout(this._ht);
  },

  methods: {
    switchTab(tab) {
      this.activeTab = tab;
      if (tab === "history" && !this.histories.length) {
        this.fetchHistories(1);
      }
    },

    formatCurrency(val) {
      if (val === null || val === undefined || isNaN(val)) return "0";
      return Number(val).toLocaleString("en-US");
    },

    formatDate(dtStr) {
      if (!dtStr) return "-";
      const d = new Date(dtStr);
      if (isNaN(d.getTime())) return dtStr;
      return d.toLocaleString("en-GB", {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
        hour12: true,
      });
    },

    getRoomNo(str) {
      if (!str) return '-';
      const s = String(str).trim();
      const m1 = s.match(/^([A-Za-z]+)[\-_](\d+)/);
      if (m1) return parseInt(m1[2], 10) || m1[2];
      const m2 = s.match(/^(\d+)[\-_ ]*(?:Block)?[\-_ ]*[A-Za-z]/i);
      if (m2) return parseInt(m2[1], 10) || m2[1];
      return s.split('-')[0] || s;
    },

    getSeatNo(r, roomType, seatNo) {
      if (!r) return '-';

      let str = r;
      let rType = roomType || '';
      let sNo = seatNo;

      if (typeof r === 'object' && r !== null) {
        let items = r.room_items || [];
        if (!items.length && r.floor_number_room_number_roomprice) {
          try {
            items = typeof r.floor_number_room_number_roomprice === 'string'
              ? JSON.parse(r.floor_number_room_number_roomprice)
              : r.floor_number_room_number_roomprice;
          } catch(e) {}
        }
        if (items && items.length) {
          return items.map(i => this.parseSeatItem(i)).join(', ');
        }
        str = r.roomnumber || r.room_number || '';
        rType = r.room_type || roomType || '';
        sNo = r.seat_no || seatNo;
      }

      return this.parseSeatItem({ roomnumber: str, room_type: rType, seat_no: sNo });
    },

    parseSeatItem(i) {
      if (!i) return '-';
      const rStr = String(i.roomnumber || i.room_number || '').trim();
      const rType = String(i.room_type || '').toLowerCase();

      const isSingle = rType.includes('single') || rType.includes('singel');
      const isDouble = rType.includes('double') || rType.includes('doubel');

      let block = null;
      let seatSub = (i.seat_no !== null && i.seat_no !== undefined && String(i.seat_no).trim() !== '') ? String(i.seat_no).trim() : null;

      let m = rStr.match(/^([A-Za-z]+)[\-_]\d+[\-_]Block[\-_](\d+)/i);
      if (m) {
        block = m[1].toUpperCase();
        if (!seatSub) seatSub = m[2];
      }

      if (!block) {
        m = rStr.match(/^\d+[\-_]Block[\-_]([A-Za-z]+)(?:[\-_](\d+))?/i);
        if (m) {
          block = m[1].toUpperCase();
          if (!seatSub && m[2]) seatSub = m[2];
        }
      }

      if (!block) {
        m = rStr.match(/^([A-Za-z]+)[\-_]\d+(?:[\-_](\d+))?/i);
        if (m) {
          block = m[1].toUpperCase();
          if (!seatSub && m[2]) seatSub = m[2];
        }
      }

      if (!block) return rStr || '-';

      if (isSingle || !isDouble) {
        return `Block ${block}`;
      }

      if (seatSub) {
        return `Block ${block}-${seatSub}`;
      }

      return `Block ${block}`;
    },

    toast(text, type = "success") {
      const bg =
        type === "success"
          ? "linear-gradient(to right, #00b09b, #96c93d)"
          : type === "warning"
          ? "linear-gradient(to right, #f59e0b, #fbbf24)"
          : "linear-gradient(to right, #ff5f6d, #ffc371)";

      Toastify({
        text: text || "Done",
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

    async fetchResidents(page = 1) {
      this.loading = true;
      try {
        const res = await axios.get(this.endpoint("active-bookings"), {
          params: {
            page,
            per_page: this.perPage,
            search: this.search,
          },
        });

        this.residents = res.data.data || [];
        this.currentPage = res.data.current_page || 1;
        this.totalPages = res.data.last_page || 1;
        this.total = res.data.total || 0;
        this.from = res.data.from ? res.data.from - 1 : 0;
      } catch (e) {
        console.error(e);
        this.toast("Failed to load active residents", "error");
      } finally {
        this.loading = false;
      }
    },

    async fetchHistories(page = 1) {
      this.historiesLoading = true;
      try {
        const res = await axios.get(this.endpoint("room-change-histories"), {
          params: {
            page,
            per_page: 10,
            search: this.historiesSearch,
          },
        });

        this.histories = res.data.data || [];
        this.historiesPage = res.data.current_page || 1;
        this.historiesTotalPages = res.data.last_page || 1;
      } catch (e) {
        console.error(e);
        this.toast("Failed to load room change history", "error");
      } finally {
        this.historiesLoading = false;
      }
    },

    async fetchAvailableTree() {
      try {
        const res = await axios.get(this.endpoint("available-seats-tree"));
        if (res.data.status === "success") {
          this.availableTree = res.data.data || [];
        }
      } catch (e) {
        console.error(e);
      }
    },

    openChangeModal(resident) {
      this.selectedResident = resident;
      this.selectedFloorId = "";
      this.selectedRoomId = "";
      this.selectedSeatId = "";

      // Reset Fee controls to default
      this.feeAmount = 500;
      this.paymentMethod = "Cash";
      this.paymentStatus = "paid";
      this.remarks = "";

      this.fetchAvailableTree();

      const modalEl = document.getElementById("changeRoomModal");
      if (modalEl && window.bootstrap) {
        this.modalInstance = window.bootstrap.Modal.getOrCreateInstance(modalEl);
        this.modalInstance.show();
      }
    },

    async openResidentHistoryModal(resident) {
      this.historyModalResident = resident;
      this.residentHistories = [];
      this.residentHistoriesLoading = true;

      const modalEl = document.getElementById("residentHistoryModal");
      if (modalEl && window.bootstrap) {
        this.historyModalInstance = window.bootstrap.Modal.getOrCreateInstance(modalEl);
        this.historyModalInstance.show();
      }

      try {
        const res = await axios.get(this.endpoint("room-change-histories"), {
          params: {
            booking_id: resident.id,
            phone: resident.phone,
            per_page: 50,
          },
        });
        this.residentHistories = res.data.data || [];
      } catch (e) {
        console.error(e);
        this.toast("Failed to load resident room change history", "error");
      } finally {
        this.residentHistoriesLoading = false;
      }
    },

    onFloorChange() {
      this.selectedRoomId = "";
      this.selectedSeatId = "";
    },

    onRoomChange() {
      this.selectedSeatId = "";
    },

    async submitRoomChange() {
      if (!this.selectedResident || !this.selectedSeatId) return;

      this.submitting = true;
      try {
        const res = await axios.post(
          this.endpoint(`bookings/${this.selectedResident.id}/change-room-seat`),
          {
            new_seat_id: this.selectedSeatId,
            fee_amount: this.feeAmount,
            payment_method: this.paymentMethod,
            payment_status: this.paymentStatus,
            remarks: this.remarks,
          }
        );

        if (res.data.success) {
          this.toast(res.data.message || "Room & Seat changed successfully!");
          if (this.modalInstance) {
            this.modalInstance.hide();
          }
          this.fetchResidents(this.currentPage);
          this.fetchAvailableTree();
          if (this.activeTab === "history") {
            this.fetchHistories(1);
          }

          // Open Receipt Modal & Print
          if (res.data.change_history) {
            this.openReceiptModal(res.data.change_history);
            setTimeout(() => {
              this.printReceiptNow();
            }, 600);
          }
        } else {
          this.toast(res.data.message || "Failed to change room.", "error");
        }
      } catch (e) {
        console.error(e);
        const msg = e.response?.data?.message || "Failed to change room/seat.";
        this.toast(msg, "error");
      } finally {
        this.submitting = false;
      }
    },

    openReceiptModal(data) {
      this.receiptData = data;
      const modalEl = document.getElementById("roomChangeReceiptModal");
      if (modalEl && window.bootstrap) {
        this.receiptModalInstance = window.bootstrap.Modal.getOrCreateInstance(modalEl);
        this.receiptModalInstance.show();
      }
    },

    printReceiptNow() {
      const content = document.getElementById("printableRoomChangeReceiptArea")?.innerHTML;
      if (!content) return;
      const html = `
        <!DOCTYPE html>
        <html>
          <head>
            <title>Room Change Receipt - টি এস এস ভিলা</title>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
            <style>
              body { padding: 25px; font-family: 'Segoe UI', Arial, sans-serif; background: #fff; }
              .receipt-box { border: 2px solid #000 !important; border-radius: 8px; padding: 25px; }
              @media print {
                body { padding: 0; }
                .receipt-box { border: 2px solid #000 !important; }
              }
            </style>
          </head>
          <body>
            ${content}
          </body>
        </html>
      `;

      if (typeof window.printOrDownloadPdf === 'function') {
        const guestName = (this.receiptData && this.receiptData.resident_name) ? this.receiptData.resident_name : 'Guest';
        window.printOrDownloadPdf(html, `Room_Change_Receipt_${guestName}`);
        return;
      }

      let iframe = document.getElementById("roomChangePrintIframe");
      if (!iframe) {
        iframe = document.createElement("iframe");
        iframe.id = "roomChangePrintIframe";
        iframe.style.position = "fixed";
        iframe.style.right = "0";
        iframe.style.bottom = "0";
        iframe.style.width = "0px";
        iframe.style.height = "0px";
        iframe.style.border = "0";
        document.body.appendChild(iframe);
      }
      const doc = iframe.contentWindow.document;
      doc.open();
      doc.write(html);
      doc.close();
      iframe.contentWindow.focus();
      setTimeout(() => {
        iframe.contentWindow.print();
      }, 500);
    },
  },
};
</script>

<style scoped>
.form-control,
.form-select {
  border-radius: 8px;
  padding: 0.55rem 0.75rem;
  border: 1px solid #dce0e4;
}

.table td {
  vertical-align: middle;
}

.table thead th {
  vertical-align: middle;
  text-align: center;
  font-size: 13px;
  font-weight: 700;
  color: #374151;
  background: #f3f4f6;
}
</style>
