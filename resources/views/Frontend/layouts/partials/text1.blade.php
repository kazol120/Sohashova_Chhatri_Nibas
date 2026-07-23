<style>
  /* ====== Room UI ====== */
  .room-box{
    width: 56px;
    height: 56px;
    border-radius: 14px;
    border: 2px solid #d9d9d9;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    cursor: pointer;
    user-select: none;
    transition: transform .08s ease, background .15s ease, border-color .15s ease;
    position: relative;
    overflow: visible;
  }

  .room-box:hover{
    transform: translateY(-1px);
    border-color: #bfbfbf;
  }

  /* Selected (Green) */
  .room-box.is-selected{
    background: #0aa84f;     
    border-color: #0aa84f;
    color: #fff;
  }

  .room-box.is-disabled{
    opacity: .45;
    cursor: not-allowed;
    pointer-events: none;
  }

  .hiddendisplay{
    display:none;
  }

  /* ===== Hover Info ===== */
  .room-hover-info{
    position: absolute;
    left: 50%;
    bottom: calc(100% + 10px);
    transform: translateX(-50%);
    min-width: 130px;
    background: #0a8f4d;
    color: #fff;
    padding: 8px 10px;
    border-radius: 10px;
    font-size: 12px;
    line-height: 1.45;
    text-align: left;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    z-index: 9999;
    box-shadow: 0 10px 24px rgba(0,0,0,.18);
    transition: opacity .18s ease, visibility .18s ease, transform .18s ease;
  }

  .room-hover-info::after{
    content: "";
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    border-width: 6px;
    border-style: solid;
    border-color: rgba(0,0,0,0.88) transparent transparent transparent;
  }

  .room-box:hover .room-hover-info{
    opacity: 1;
    visibility: visible;
    transform: translateX(-50%) translateY(-2px);
  }

  .room-hover-info strong{
    font-weight: 700;
  }

  /* ===== Total Amount Right Side ===== */
  .payment-head-row{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    margin-bottom:12px;
    flex-wrap:wrap;
  }

  .payment-head-row .bk-title{
    margin-bottom:0;
  }

  .total-amountsss{
    margin-left:auto;
    text-align:right;
    font-weight:700;
    line-height:1.2;
  }

  .total-amountsss .amount-label{
    display:block;
    font-size:13px;
    color:#6b7280;
    margin-bottom:2px;
  }

  .total-amountsss .amouont{
    font-size:24px;
    font-weight:800;
    color:#0a8f4d;
  }

  @media (max-width: 576px){
    .payment-head-row{
      align-items:flex-start;
    }
    .total-amountsss{
      width:100%;
      text-align:right;
    }
    .total-amountsss .amouont{
      font-size:22px;
    }
  }
</style>

<!-- ===================== BOOKING LEFT OFFCANVAS ===================== -->
<div id="bookingCanvas" class="offcanvas offcanvas-end booking-canvas" tabindex="-1" aria-labelledby="bookingCanvasLabel">
  <div class="offcanvas-header booking-canvas-head">
    <div>
      <h5 class="offcanvas-title m-0" id="bookingCanvasLabel">
        <i class="bi bi-journal-check"></i> Confirm Booking 
      </h5>
      <div class="small text-muted">Select room(s) → see total → click Booking</div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>

  <!-- ===================== ROOM GRID (MULTI SELECT) ===================== -->
  <div class="offcanvas-body booking-canvas-body bk-seat-center" id="seatGridWrap">
    <div class="bk-block mb-3 w-100">
      <div class="container-fluid" style="position: relative; top: 70px;">
        <div class="row justify-content-center">
          <div class="col-12">
            <div class="seat-grid-card">
              <div class="seat-wrapper">
                <div class="col-12 text-center d-flex flex-wrap justify-content-center gap-3">
                  @foreach($rooms as $room)
                    <div
                      class="seat room-box {{ $room->status == 1 ? 'is-disabled' : '' }}"
                      data-room="{{ $room->room_no }}"
                      data-price="{{ (int)$room->price }}"
                      data-status="{{ $room->status }}"
                      data-room-type="{{ $room->room_type }}"
                      data-ac-status="{{ $room->ac_status }}"
                      title="">
                      {{ $room->room_no }}
                      <div class="room-hover-info">
                        <div><strong>Room Type:</strong> {{ $room->room_type }}</div>
                        <div><strong>Ac Status:</strong> {{ $room->ac_status }}</div>
                        <div><strong>Price:</strong> {{ $room->price }}</div>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sticky bar -->
        <div class="bk-sticky" id="stickyBar" style="display:none;">
          <div class="bk-sticky-left">
            <div class="bk-count"><span id="selectedCount">0</span> Room(s) selected</div>
            <div class="bk-sub" id="selectedListText"></div>
          </div>
          <div class="bk-sticky-right">
            <div class="bk-total">৳<span id="totalAmount">0</span></div>
            <button class="bk-btn" id="openBookingBtn" type="button" disabled>Booking</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ===================== BOOKING FORM (HIDDEN) ===================== -->
  <div class="offcanvas-body booking-canvas-body d-none" id="openbooking">
    <!-- Message Box -->
    <div id="formMsg" class="alert d-none rounded-4 mb-3" role="alert"></div>

    <div class="bk-form-head">
      <div class="bk-form-title">
        <i class="bi bi-pencil-square"></i> Booking Form
        <small class="text-muted d-block">Selected rooms + guest info + payment</small>
      </div>
      <button type="button" class="bk-form-close" id="closeBookingBtn" aria-label="Close form">&times;</button>
    </div>
    
    <form id="bookingForm" method="POST" action="{{ route('room-booking-history.store') }}" enctype="multipart/form-data" novalidate>
      @csrf

      <!-- Selected rooms + total -->
      <div class="bk-block mb-3">
        <h6 class="bk-title"><i class="bi bi-grid-3x3-gap"></i> Selected Room(s)</h6>
        <div class="bk-summary">
          <div class="bk-s-row">
            <div class="bk-s-item">
              <span>Rooms:</span>
              <b id="finalRooms">—</b>
            </div>
            <div class="bk-s-item price">
              <span>Total:</span>
              <b>৳<span id="finalTotal">0</span></b>
            </div>
          </div>
        </div>
        <input type="hidden" id="selectedSeatsInput" name="rooms" value="">
        <input type="hidden" id="selectedTotalInput" name="total" value="">
      </div>

      <!-- Guest Info -->
      <div class="bk-block mb-3">
        <h6 class="bk-title"><i class="bi bi-person-badge"></i> Guest Information</h6>
        <div class="row g-3">

          <div class="col-12">
            <label class="form-label">Phone <span class="req">*</span></label>
            <input type="tel" class="form-control" id="phone" name="phone" placeholder="+8801XXXXXXXXX"  autocomplete="off">
            <div id="phoneStatus" class="form-text"></div>
          </div>

          <div class="col-12">
            <label class="form-label">Image <span class="req">*</span></label>
            <input type="file" class="form-control" id="guestImage" name="image" accept="image/*" >
          </div>

          <div class="col-12">
            <label class="form-label">Image Show <span class="req">*</span></label>
            <div class="img-preview" id="imgPreviewBox">
              <img id="imgPreview" alt="Preview" style="display:none; max-width:100%; height:auto;">
              <div class="hint" id="imgHint">Upload an image to preview here</div>
            </div>
          </div>

          <!-- Check-in -->
      
        <!-- Check-in -->
          <div class="col-6">
              <label class="form-label">Check-in *</label>
              <input type="text" id="bkCheckinDisplay" class="form-control" placeholder="DD-MM-YYYY" readonly>
              <input type="hidden" id="bkCheckin" name="check_in" class="hiddendisplay">
          </div>

          <!-- Check-out -->
          <div class="col-6">
              <label class="form-label">Check-out *</label>
              <input type="text" id="bkCheckoutDisplay" class="form-control" placeholder="DD-MM-YYYY" readonly>
              <input type="hidden" id="bkCheckout" name="check_out" class="hiddendisplay">
          </div>

          <!-- Other Guest Information -->
          <div class="col-12">
            <label class="form-label">Full Name <span class="req">*</span></label>
            <input type="text" class="form-control" id="fullname" name="full_name" placeholder="Your name" >
          </div>

          <div class="col-12">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" id="guestEmail" name="email" placeholder="you@email.com">
          </div>

          <div class="col-12">
              <label class="form-label">Password</label>
              <input type="text" class="form-control" id="bookingPassword" name="password" placeholder="password" autocomplete="off"> 
        </div>

          <div class="col-12">
            <label class="form-label">NID / Passport (optional)</label>
            <input type="text" class="form-control" id="nid" name="nid" placeholder="NID/Passport number">
          </div>
          <!-- Division -->
           <div class="col-12">
            <label class="form-label">Division</label>
            <select class="form-select" id="division" name="division_id">
                <option value="">Select Division</option>
            </select>
          </div>
          <!-- District Dropdown -->
          <div class="col-12">
            <label class="form-label">District</label>
            <select class="form-select" id="district" name="district_id">
                <option value="">Select District</option>
            </select>
          </div>
          <!-- Thana Dropdown -->
          <div class="col-12">
            <label class="form-label">Thana</label>
            <select class="form-select" id="thana" name="thana_id">
                <option value="">Select Thana</option>
            </select>
          </div>
          <!-- Permanent Address Field -->
          <div class="col-12 mt-2">
            <label class="form-label">Permanent Address / স্থায়ী ঠিকানা <code>*</code></label>
            <input type="text" class="form-control" id="address" name="address" placeholder="Enter permanent address (Village/Road, Post Office, House No)" required>
          </div>
        </div>
      </div>
      <!-- Payment Options -->
      <div class="bk-block mb-3">
        <div class="payment-head-row">
          <h6 class="bk-title"><i class="bi bi-credit-card"></i> Payment Option</h6>
          <div class="total-amountsss">
            <span class="amount-label">Total Amount</span>
            <div class="amouont">৳<span id="grandTotal">0</span></div>
          </div>
        </div>
        <div class="pay-grid">
          <label class="pay-option active" for="payOnline">
            <input type="radio" name="payment" id="payOnline" value="online" checked>
            <div>
              <div class="p-title">Pay Online</div>
              <div class="p-sub">bKash / Nagad / Card. Booking confirm after verify.</div>
            </div>
          </label>
          <label class="pay-option" for="payCash">
            <input type="radio" name="payment" id="payCash" value="cash">
            <div>
              <div class="p-title">Pay Cash In</div>
              <div class="p-sub">Pay at hotel reception during check-in.</div>
            </div>
          </label>
        </div>
        <div id="onlineFields" class="mt-3">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Payment Method <span class="req">*</span></label>
              <select class="form-select" id="payMethod" name="pay_method">
                <option value="bKash" selected>bKash</option>
                <option value="Nagad">Nagad</option>
                <option value="Card">Card</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Transaction ID <span class="req">*</span></label>
              <input type="text" class="form-control" id="trx" name="trx" placeholder="Enter Transaction ID">
            </div>
          </div>
          <div class="hint mt-2">We’ll verify your payment and confirm the booking.</div>
        </div>
        <div id="cashFields" class="mt-3" style="display:none;">
          <div class="alert alert-warning mb-0 rounded-4">
            <strong>Pay at hotel:</strong> Please arrive on time. Booking may be cancelled if no-show.
          </div>
        </div>
      </div>
      <div class="d-grid gap-2">
        <button class="btn btn-success rounded-4 py-2 fw-bold" type="submit" id="confirmBtn">
          Confirm Booking <i class="bi bi-check2-circle ms-1"></i>
        </button>
        <button class="btn btn-outline-secondary rounded-4 py-2 fw-bold" type="button" id="backToSeatsBtn">
          Back
        </button>
      </div>
    </form>
  </div>
</div>


<script>

    document.getElementById('phone').addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, '');
    if (this.value.length > 11) {
        this.value = this.value.slice(0, 11);
    }
});

window.openroombookingModal = function () {
    const canvasEl = document.getElementById('bookingCanvas');
    if (!canvasEl || typeof bootstrap === 'undefined') return;
    const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(canvasEl);
    offcanvas.show();
};

document.addEventListener('DOMContentLoaded', function () {

  const $ = (s, p = document) => p.querySelector(s);
  const $$ = (s, p = document) => [...p.querySelectorAll(s)];

    const els = {
        openBookingCanvasBtn: $('#openBookingCanvasBtn'),
        seatWrap: $('#seatGridWrap'),
        stickyBar: $('#stickyBar'),
        selectedCount: $('#selectedCount'),
        selectedListText: $('#selectedListText'),
        totalAmount: $('#totalAmount'),
        finalRooms: $('#finalRooms'),
        finalTotal: $('#finalTotal'),
        selectedSeatsInput: $('#selectedSeatsInput'),
        selectedTotalInput: $('#selectedTotalInput'),
        formMsg: $('#formMsg'),
        guestImage: $('#guestImage'),
        imgPreview: $('#imgPreview'),
        imgHint: $('#imgHint'),
        bkCheckinDisplay: $('#bkCheckinDisplay'),
        bkCheckoutDisplay: $('#bkCheckoutDisplay'),
        bkCheckin: $('#bkCheckin'),
        bkCheckout: $('#bkCheckout'),
        payOnline: $('#payOnline'),
        payCash: $('#payCash'),
        onlineFields: $('#onlineFields'),
        cashFields: $('#cashFields'),
        trx: $('#trx'),
        payMethod: $('#payMethod'),
        closeBookingBtn: $('#closeBookingBtn'),
        openBookingBtn: $('#openBookingBtn'),
        openbooking: $('#openbooking'),
        backToSeatsBtn: $('#backToSeatsBtn'),
        bookingForm: $('#bookingForm'),
        confirmBtn: $('#confirmBtn'),
        bookingCanvas: $('#bookingCanvas'),
        paymentAmount: $('#payment_amount'),
        division: $('#division'),
        district: $('#district'),
        thana: $('#thana'),
        password: $('#password'),
        bookingLoader: $('#bookingLoader'),
    };

    let selected = new Map();
    let existingImageUrl = null;
    let existingImageName = null;
    let guestHasAccount = false;

    function showLoader() {
        if (els.bookingLoader) {
            els.bookingLoader.classList.remove('d-none');
        }
        document.body.classList.add('booking-loading');
    }

    function hideLoader() {
        if (els.bookingLoader) {
            els.bookingLoader.classList.add('d-none');
        }
        document.body.classList.remove('booking-loading');
    }

    function showMsg(type, html) {
        if (!els.formMsg) return;

        let bg = '#eef2ff', color = '#1e293b', border = '#cbd5e1';
        if (type === 'success') {
            bg = '#dcfce7'; color = '#166534'; border = '#86efac';
        } else if (type === 'warning') {
            bg = '#ffedd5'; color = '#9a3412'; border = '#fdba74';
        } else if (type === 'danger') {
            bg = '#fee2e2'; color = '#991b1b'; border = '#fca5a5';
        }

        els.formMsg.classList.remove('d-none');
        els.formMsg.style.background = bg;
        els.formMsg.style.color = color;
        els.formMsg.style.border = `1px solid ${border}`;
        els.formMsg.style.borderRadius = '14px';
        els.formMsg.style.padding = '14px 16px';
        els.formMsg.style.marginBottom = '14px';
        els.formMsg.style.boxShadow = '0 8px 20px rgba(0,0,0,0.08)';
        els.formMsg.innerHTML = html;
    }

    function hideMsg() {
        if (!els.formMsg) return;
        els.formMsg.classList.add('d-none');
        els.formMsg.innerHTML = '';
        els.formMsg.removeAttribute('style');
    }

function showSuccessAlert(message, redirectUrl) {
    const old = document.getElementById('bkSuccessAlert');
    if (old) old.remove();

    const alertEl = document.createElement('div');
    alertEl.id = 'bkSuccessAlert';
    alertEl.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 99999;
        background: #0a9f4b;
        color: #fff;
        border-radius: 16px;
        padding: 16px 22px;
        font-size: 15px;
        font-weight: 700;
        box-shadow: 0 10px 30px rgba(0,0,0,0.16);
        min-width: 280px;
    `;
    alertEl.innerHTML = `✅ ${message}`;
    document.body.appendChild(alertEl);

    const finalRedirect = (redirectUrl && redirectUrl.trim() !== '')
        ? redirectUrl
        : '/backend/dashboard';

    setTimeout(() => {
        window.location.href = finalRedirect;
    }, 1800);
}
    function bindOpenCanvas() {
        els.openBookingCanvasBtn?.addEventListener('click', function () {
            if (!els.bookingCanvas || typeof bootstrap === 'undefined') return;
            const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(els.bookingCanvas);
            offcanvas.show();
        });
    }

    function getSelectedRoomPayload() {
        return [...selected.values()].map(item => ({
            floornumber: item.floornumber,
            roomnumber: item.roomnumber,
            price: Number(item.price)
        }));
    }

    function syncSticky() {
        const roomPayload = getSelectedRoomPayload();
        const rooms = roomPayload.map(item => item.roomnumber);
        const total = roomPayload.reduce((sum, item) => sum + Number(item.price || 0), 0);

        const checkIn = new Date(els.bkCheckin?.value);
        const checkOut = new Date(els.bkCheckout?.value);

        const days = (els.bkCheckin?.value && els.bkCheckout?.value)
            ? Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24))
            : 1;

        const grandTotal = total * (days > 0 ? days : 1);

        if (els.selectedCount) els.selectedCount.textContent = rooms.length;
        if (els.selectedListText) els.selectedListText.textContent = rooms.length ? rooms.join(', ') : '';
        if (els.totalAmount) els.totalAmount.textContent = grandTotal;
        if (els.finalRooms) els.finalRooms.textContent = rooms.length ? rooms.join(', ') : '—';
        if (els.finalTotal) els.finalTotal.textContent = grandTotal;
        if (els.selectedSeatsInput) els.selectedSeatsInput.value = JSON.stringify(roomPayload);
        if (els.selectedTotalInput) els.selectedTotalInput.value = grandTotal;
        if (els.paymentAmount) els.paymentAmount.value = grandTotal;
        if (els.stickyBar) els.stickyBar.style.display = rooms.length ? 'flex' : 'none';
        if (els.openBookingBtn) els.openBookingBtn.disabled = rooms.length === 0;

        const grandTotalEl = document.getElementById('grandTotal');
        if (grandTotalEl) grandTotalEl.textContent = grandTotal.toLocaleString();
    }

    function bindRooms() {
        $$('.room-box').forEach(el => {
            el.addEventListener('click', () => {
                const room = el.dataset.room;
                const floor = el.dataset.floor || '';
                const price = parseFloat(el.dataset.price || '0');

                if (selected.has(room)) {
                    selected.delete(room);
                    el.classList.remove('is-selected');
                } else {
                    selected.set(room, {
                        floornumber: floor,
                        roomnumber: room,
                        price: price
                    });
                    el.classList.add('is-selected');
                }

                syncSticky();
            });
        });
    }

    function togglePaymentUI() {
        const isOnline = !!els.payOnline?.checked;

        if (els.onlineFields) els.onlineFields.style.display = isOnline ? 'block' : 'none';
        if (els.cashFields) els.cashFields.style.display = isOnline ? 'none' : 'block';

    
    }

    function bindPayment() {
        els.payOnline?.addEventListener('change', togglePaymentUI);
        els.payCash?.addEventListener('change', togglePaymentUI);
        togglePaymentUI();
    }

    function bindFormToggle() {
        els.openBookingBtn?.addEventListener('click', () => {
            hideMsg();
            els.seatWrap?.classList.add('d-none');
            els.openbooking?.classList.remove('d-none');
        });

        els.backToSeatsBtn?.addEventListener('click', () => {
            hideMsg();
            els.openbooking?.classList.add('d-none');
            els.seatWrap?.classList.remove('d-none');
        });

        els.closeBookingBtn?.addEventListener('click', () => {
            hideMsg();
            els.openbooking?.classList.add('d-none');
            els.seatWrap?.classList.remove('d-none');
        });
    }

    function bindImagePreview() {
        els.guestImage?.addEventListener('change', e => {
            const file = e.target.files?.[0];

            if (!file) {
                if (els.imgPreview) {
                    els.imgPreview.src = '';
                    els.imgPreview.style.display = 'none';
                }
                if (els.imgHint) els.imgHint.style.display = 'block';
                return;
            }

            const url = URL.createObjectURL(file);

            if (els.imgPreview) {
                els.imgPreview.src = url;
                els.imgPreview.style.display = 'block';
            }
            if (els.imgHint) els.imgHint.style.display = 'none';
        });
    }

    function toYMD(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    function bindDatepickers() {
        if (!window.flatpickr) return;

        if (els.bkCheckinDisplay) {
            flatpickr(els.bkCheckinDisplay, {
                dateFormat: 'd-m-Y',
                minDate: 'today',
                onChange(dates) {
                    if (dates[0] && els.bkCheckin) {
                        els.bkCheckin.value = toYMD(dates[0]);
                        syncSticky();
                    }
                }
            });
        }

        if (els.bkCheckoutDisplay) {
            flatpickr(els.bkCheckoutDisplay, {
                dateFormat: 'd-m-Y',
                minDate: 'today',
                onChange(dates) {
                    if (dates[0] && els.bkCheckout) {
                        els.bkCheckout.value = toYMD(dates[0]);
                        syncSticky();
                    }
                }
            });
        }
    }

    async function loadDivisions(selectedDivisionId = null) {
        if (!els.division) return;

        const res = await fetch('/divisions');
        const data = await res.json();

        els.division.innerHTML = '<option value="">Select Division</option>';

        data.forEach(item => {
            const option = document.createElement('option');
            option.value = String(item.id);
            option.textContent = item.name;
            els.division.appendChild(option);
        });

        if (selectedDivisionId) {
            els.division.value = String(selectedDivisionId);
        }
    }

    async function loadDistricts(divisionId, selectedDistrictId = null) {
        const districtEl = document.getElementById('district');
        const thanaEl = document.getElementById('thana');

        if (!districtEl) return;

        districtEl.innerHTML = '<option value="">Select District</option>';
        if (thanaEl) {
            thanaEl.innerHTML = '<option value="">Select Thana</option>';
        }

        if (!divisionId) return;

        const res = await fetch(`/divisions/${divisionId}/districts`);
        const data = await res.json();

        data.forEach(item => {
            const option = document.createElement('option');
            option.value = String(item.id);
            option.textContent = item.name;
            districtEl.appendChild(option);
        });

        if (selectedDistrictId) {
            districtEl.value = String(selectedDistrictId);
        }
    }

    async function loadThanas(districtId, selectedThanaId = null) {
        const thanaEl = document.getElementById('thana');
        if (!thanaEl) return;

        thanaEl.innerHTML = '<option value="">Select Thana</option>';

        if (!districtId) return;

        const res = await fetch(`/districts/${districtId}/thanas`);
        const data = await res.json();

        data.forEach(item => {
            const option = document.createElement('option');
            option.value = String(item.id);
            option.textContent = item.name;
            thanaEl.appendChild(option);
        });

        if (selectedThanaId) {
            thanaEl.value = String(selectedThanaId);
        }
    }

    async function bindLocationDropdowns() {
        await loadDivisions();

        els.division?.addEventListener('change', async function () {
            await loadDistricts(this.value);
        });

        els.district?.addEventListener('change', async function () {
            await loadThanas(this.value);
        });
    }

    function bindPhoneAutofill() {
        const phoneEl = document.getElementById('phone');
        const statusEl = document.getElementById('phoneStatus');
        if (!phoneEl) return;

        let timer;
        phoneEl.addEventListener('input', function () {
            clearTimeout(timer);
            const val = this.value.trim();

            if (statusEl) {
                statusEl.textContent = '';
                statusEl.className = 'form-text';
            }
            guestHasAccount = false;
            existingImageUrl = null;
            existingImageName = null;
            const fileInput = document.getElementById('guestImage');
            if (fileInput) fileInput.setAttribute('required', 'required');
            const hiddenImg = document.getElementById('existingImageInput');
            if (hiddenImg) hiddenImg.value = '';
            if (val.length < 11) return;
            if (statusEl) {
                statusEl.textContent = 'Searching...';
                statusEl.className = 'form-text text-muted';
            }
            timer = setTimeout(() => {
                fetch(`/guest/by-phone/${encodeURIComponent(val)}`)
                    .then(r => r.json())
                    .then(async data => {
                        if (!data.found) {
                            guestHasAccount = false;
                            if (statusEl) {
                                statusEl.textContent = '✅ New guest – fill in the form.';
                                statusEl.className = 'form-text text-success';
                            }
                            return;
                        }

                        guestHasAccount = !!data.has_account;

                        if (statusEl) {
                            statusEl.textContent = data.has_account
                                ? '✅ Guest found! Account exists. Please enter password.'
                                : '✅ Guest found! Info auto-filled.';
                            statusEl.className = 'form-text text-success';
                        }

                        const setVal = (id, value) => {
                            const el = document.getElementById(id);
                            if (el) el.value = value || '';
                        };

                        setVal('fullname', data.full_name);
                        setVal('guestEmail', data.email);
                        setVal('nid', data.nid);
                        setVal('address', data.address);

                        if (data.image_url && data.image_name) {
                            existingImageUrl = data.image_url;
                            existingImageName = data.image_name;

                            const img = document.getElementById('imgPreview');
                            const hint = document.getElementById('imgHint');

                            if (img) {
                                img.src = data.image_url;
                                img.style.display = 'block';
                            }

                            if (hint) hint.style.display = 'none';
                            if (fileInput) fileInput.removeAttribute('required');

                            let hiddenExistingImg = document.getElementById('existingImageInput');
                            if (!hiddenExistingImg) {
                                hiddenExistingImg = document.createElement('input');
                                hiddenExistingImg.type = 'hidden';
                                hiddenExistingImg.id = 'existingImageInput';
                                hiddenExistingImg.name = 'existing_image';
                                document.getElementById('bookingForm').appendChild(hiddenExistingImg);
                            }
                            hiddenExistingImg.value = data.image_name;
                        }

                        if (data.division_id) {
                            await loadDivisions(data.division_id);
                            await loadDistricts(data.division_id, data.district_id);

                            if (data.district_id) {
                                await loadThanas(data.district_id, data.thana_id);
                            }
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        if (statusEl) {
                            statusEl.textContent = '⚠️ Error checking phone.';
                            statusEl.className = 'form-text text-danger';
                        }
                    });
            }, 500);
        });
    }


  function bindAjaxSubmit() {
      let isSubmitting = false;

      els.bookingForm?.addEventListener('submit', function (e) {
          e.preventDefault();
          if (isSubmitting) return;
          hideMsg();
          document.querySelectorAll('#bookingForm .form-control, #bookingForm .form-select').forEach(input => {
              input.style.cssText = '';
          });

      const passwordValue = document.querySelector('#bookingForm input[name="password"]')?.value.trim() || '';

        if (!passwordValue) {
            showMsg('warning', `
                <div style="display:flex; gap:12px; align-items:flex-start;">
                    <div style="font-size:22px; line-height:1;">🔑</div>
                    <div>
                        <div style="font-size:15px; font-weight:700; margin-bottom:6px;">Password Required</div>
                        <div style="font-size:13px; line-height:1.6;">Please enter your password to complete the booking</div>
                    </div>
                </div>
            `);
            scrollToFormMsg();
            return;
        }
        if (!els.bkCheckin?.value || !els.bkCheckout?.value) {
            showMsg('warning', `
                <div style="display:flex; gap:12px; align-items:flex-start;">
                    <div style="font-size:22px; line-height:1;">📅</div>
                    <div>
                        <div style="font-size:15px; font-weight:700; margin-bottom:6px;">Date Selection Required</div>
                        <div style="font-size:13px; line-height:1.6;">
                            ${!els.bkCheckin?.value ? ' Please select a Check-in date.<br>' : ''}
                            ${!els.bkCheckout?.value ? ' Please select a Check-out date.' : ''}
                        </div>
                    </div>
                </div>
            `);
            scrollToFormMsg();
            return;
        }
        if (els.guestImage && !els.guestImage.files.length && !document.getElementById('existingImageInput')?.value) {
            showMsg('warning', `
                <div style="display:flex; gap:12px; align-items:flex-start;">
                    <div style="font-size:22px; line-height:1;">🖼️</div>
                    <div>
                      <div style="font-size:15px; font-weight:700; margin-bottom:6px;">Guest Image Required</div>
                      <div style="font-size:13px; line-height:1.6;">Please upload a valid guest image to proceed.</div>
                    </div>
                </div>
            `);
            scrollToFormMsg();
            return;
        }
        if (!els.selectedSeatsInput?.value || els.selectedSeatsInput.value === '[]') {
            showMsg('warning', `
                <div style="display:flex; gap:12px; align-items:flex-start;">
                    <div style="font-size:22px; line-height:1;">🛏️</div>
                    <div>
                        <div style="font-size:15px; font-weight:700; margin-bottom:6px;">No room selected</div>
                        <div style="font-size:13px; line-height:1.6;">Please select at least one room.</div>
                    </div>
                </div>
            `);
            scrollToFormMsg();
            return;
        }
        if (els.payOnline?.checked) {
            if (!els.payMethod?.value.trim() || !els.trx?.value.trim()) {
                showMsg('warning', `
                    <div style="display:flex; gap:12px; align-items:flex-start;">
                        <div style="font-size:22px; line-height:1;">💳</div>
                        <div>
                            <div style="font-size:15px; font-weight:700; margin-bottom:6px;">Payment information missing</div>
                            <div style="font-size:13px; line-height:1.6;">Please select payment method and enter transaction ID.</div>
                        </div>
                    </div>
                `);
                scrollToFormMsg();
                return;
            }
        }
        isSubmitting = true;
        showLoader();
        if (els.confirmBtn) {
            els.confirmBtn.disabled = true;
            els.confirmBtn.innerHTML = 'Processing...';
        }

        if (ok && data.status) {

    const offcanvas = window.bootstrap?.Offcanvas?.getOrCreateInstance(els.bookingCanvas);

    if (offcanvas) {

        showSuccessAlert(
            data.message || 'Booking successfully!',
            data.redirect_url
        );

        setTimeout(() => {
            offcanvas.hide();
        }, 300);

    } else {

        showSuccessAlert(
            data.message || 'Booking successfully!',
            data.redirect_url
        );
    }
}
    });
}

function scrollToFormMsg() {
    setTimeout(() => {
        els.formMsg?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 100);
}
    (async function init() {
        bindOpenCanvas();
        await bindLocationDropdowns();
        bindRooms();
        bindFormToggle();
        bindImagePreview();
        bindPayment();
        bindDatepickers();
        bindAjaxSubmit();
        syncSticky();
        bindPhoneAutofill();
    })();
});
</script>