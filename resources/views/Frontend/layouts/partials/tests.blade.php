<div class="floating-support">
    <button type="button" class="support-btn" onclick="openroombookingModal()">
        <i class="bi bi-calendar-check"></i>
        <span>Book Room</span>
    </button>
</div>

<!-- Global Booking Loader -->
<div id="bookingLoader" class="booking-loader-overlay d-none">
  <div class="booking-loader-spinner-ring"></div>
</div>

<!-- ===================== BOOKING LEFT OFFCANVAS ===================== -->
<div id="bookingCanvas" class="offcanvas offcanvas-end booking-canvas" tabindex="-1" aria-labelledby="bookingCanvasLabel" style="width: 250px;">
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
  <div class="bk-block mb-6 w-100 h-100">
    <div class="container-fluid">
      <div class="row justify-content-center">
        <div class="col-12">
          <div class="seat-grid-card">
           @foreach(($floors ?? collect()) as $floor)
            <div class="mb-4 floor-block">
              <h6 class="fw-bold text-center mb-3">{{ $floor->name }}</h6>
              <div class="seat-wrapper">
                <div class="col-12 text-center d-flex flex-wrap justify-content-center gap-3">
                  @foreach(($floor->rooms ?? collect()) as $room)
                    <div
                      class="seat room-box {{ $room->status == 1 ? 'is-disabled' : '' }}"
                      data-room="{{ $room->room_no }}"
                      data-price="{{ (int) $room->price }}"
                      data-status="{{ $room->status }}"
                      data-room-type="{{ $room->room_type }}"
                      data-ac-status="{{ $room->ac_status }}">
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
            @endforeach
          </div>
        </div> 
      </div>
    </div>

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

        <div class="input-group">
          <!-- fixed prefix -->
          <span class="input-group-text">+88</span>

          <!-- user input -->
          <input 
            type="tel" 
            class="form-control" 
            id="phone" 
            name="phone"
            placeholder=""
            autocomplete="off"
          >
        </div>

        <div id="phoneStatus" class="form-text"></div>
        </div>

          <div class="col-12">
            <label class="form-label">Image <span class="req"></span></label>
            <input type="file" class="form-control" id="guestImage" name="image" accept="image/*">
          </div>

          <div class="col-12">
            <label class="form-label">Image Show <span class="req">*</span></label>
            <div class="img-preview" id="imgPreviewBox">
              <img id="imgPreview" alt="Preview" style="display:none; max-width:100%; height:auto;">
              <div class="hint" id="imgHint">Upload an image to preview here</div>
            </div>
          </div>

          <!-- Check-in -->
          <div class="col-6">
            <label class="form-label">Check-in *</label>
            <input type="text" id="bkCheckinDisplay" class="form-control" placeholder="DD-MM-YYYY" readonly >
            <input type="hidden" id="bkCheckin" name="check_in"  class="hiddendisplay">
          </div>

          <!-- Check-out -->
          <div class="col-6">
            <label class="form-label">Check-out *</label>
            <input type="text" id="bkCheckoutDisplay" class="form-control" placeholder="DD-MM-YYYY" readonly >
            <input type="hidden" id="bkCheckout" name="check_out"  class="hiddendisplay">
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
          <input type="text" class="form-control" id="password" name="password" placeholder="password" >         
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
        <h6 class="bk-title"><i class="bi bi-credit-card"></i> Payment Option</h6>
<div class="total-amount">
    <label class="" for="payOnline">
        <div>
            <div class="amouont">Total <span id="grandTotal">0</span> Tk</div>
        </div>
    </label>
</div>

        <div class="pay-grid">
          <label class="pay-option active" for="payOnline">
            <input type="radio" name="payment" id="payOnline" value="online" checked>
            <div>
              <div class="p-title">Pay Online</div>
              <div class="p-sub">bKash / Nagad / Card. Booking confirm after verify.</div>
            </div>
          </label>

          <label class="pay-option " for="payCash">
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

          <div class="hint mt-2">We'll verify your payment and confirm the booking.</div>
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


<style type="text/css">
/* ===== ROOM BOX ===== */
.total-amount{
  position:relative;
  left:94px;
  bottom:9px;
  font-weight:bold;
}
.total-amount .amouont{
  positiion:relative;
  font-size:24px;
}
.room-box {
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
  transition: background .15s ease, border-color .15s ease, color .15s ease;
  flex: 0 0 auto;
}
.room-box:hover {
  border-color: #bfbfbf;
}
.room-box.is-selected {
  background: #0aa84f;
  border-color: #0aa84f;
  color: #fff;
}
.room-box.is-disabled {
  opacity: .45;
  cursor: not-allowed;
  pointer-events: none;
}
.hiddendisplay {
  display: none;
}

/* ===== FLOATING BUTTON ===== */
.floating-support {
  position: fixed;
  right: 0;
  top: 50%;
  transform: translateY(-50%);
  z-index: 9999;
}
.support-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  border: none;
  padding: 14px 18px;
  background: linear-gradient(135deg, #0a8f4d, #13b364);
  color: #fff;
  font-size: 16px;
  font-weight: 600;
  box-shadow: 0 10px 25px rgba(0, 0, 0, .2);
  cursor: pointer;
  transition: all .3s ease;
  border-radius: 12px 0 0 12px;
}
.support-btn i {
  font-size: 20px;
}
.support-btn:hover {
  transform: translateX(-6px);
  box-shadow: 0 15px 35px rgba(0, 0, 0, .25);
  background: linear-gradient(135deg, #0c7e45, #0fa65a);
}

/* ===== OFFCANVAS ===== */
.booking-canvas,
.offcanvas.offcanvas-end.booking-canvas {
  overflow: hidden;
  width: 420px !important;
}
.booking-canvas-head {
  position: sticky;
  top: 0;
  z-index: 50;
  background: #fff;
  border-bottom: 1px solid #e9ecef;
  flex-shrink: 0;
}
.booking-canvas-body {
  padding: 0 !important;
}

/* ===== SEAT GRID WRAP ===== */
#seatGridWrap {
  display: flex;
  flex-direction: column;
  height: calc(100vh - 78px);
  overflow: hidden;
  box-sizing: border-box;
}
#seatGridWrap .bk-block {
  flex: 1 1 auto;
  overflow-y: auto;
  overflow-x: hidden;
  -webkit-overflow-scrolling: touch;
  padding: 18px 12px 20px !important;
  box-sizing: border-box;
}

/* ===== SEAT GRID CARD ===== */
.seat-grid-card {
  width: 100%;
  padding: 20px 10px 10px !important;
  border-radius: 16px;
}
.floor-block {
  width: 100%;
}
.floor-block:first-child {
  margin-top: 8px !important;
  padding-top: 8px !important;
}
.seat-wrapper {
  width: 100%;
}
.seat-wrapper > .col-12 {
  width: 100%;
  margin: 0 auto;
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  align-items: flex-start;
  align-content: flex-start;
  gap: 12px !important;
  max-width: 420px;
}

/* ===== STICKY BOOKING BAR ===== */
.bk-sticky {
  position: sticky;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 30;
  flex-shrink: 0;
  display: none;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  background: #0a9a4f;
  color: #fff;
  border-radius: 18px 18px 0 0;
  padding: 14px 16px;
  box-shadow: 0 -4px 20px rgba(0, 0, 0, .15);
  margin: 0 !important;
}
.bk-sticky-left {
  min-width: 0;
  flex: 1 1 auto;
}
.bk-sticky-right {
  display: flex;
  align-items: center;
  gap: 10px;
  flex: 0 0 auto;
}
.bk-count {
  font-size: 16px;
  font-weight: 700;
  line-height: 1.2;
}
.bk-sub {
  font-size: 13px;
  line-height: 1.3;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 100%;
}
.bk-total {
  font-size: 18px;
  font-weight: 800;
  white-space: nowrap;
}
.bk-btn {
  border: none;
  min-width: 95px;
  padding: 10px 14px;
  border-radius: 12px;
  background: #fff;
  color: #0a8f4d;
  font-weight: 700;
  cursor: pointer;
}
.bk-btn:disabled {
  opacity: .5;
  cursor: not-allowed;
}

/* ===== TABLET ===== */
@media (max-width: 991.98px) {
  .booking-canvas,
  .offcanvas.offcanvas-end.booking-canvas {
    width: 380px !important;
  }
  #seatGridWrap .bk-block {
    padding: 18px 10px 20px !important;
  }
  .seat-grid-card {
    padding: 20px 8px 10px !important;
  }
  .seat-wrapper > .col-12 {
    max-width: 360px;
    gap: 12px !important;
  }
  .room-box {
    width: 54px;
    height: 54px;
    font-size: 17px;
  }
}

/* ===== MOBILE ===== */
@media (max-width: 768px) {
  .floating-support {
    right: 0;
    top: 55%;
  }
  .support-btn {
    padding: 12px 14px;
    font-size: 14px;
  }
  .support-btn i {
    font-size: 18px;
  }
  .booking-canvas,
  .offcanvas.offcanvas-end.booking-canvas {
    width: 100vw !important;
    max-width: 100vw !important;
  }
  #seatGridWrap .bk-block {
    padding: 20px 8px 20px !important;
  }
  .seat-grid-card {
    padding: 22px 6px 10px !important;
  }
  .floor-block:first-child {
    margin-top: 10px !important;
    padding-top: 10px !important;
  }
  .seat-wrapper > .col-12 {
    max-width: 300px;
    gap: 10px !important;
  }
  .room-box {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    font-size: 16px;
  }
  .bk-sticky {
    padding: 12px 16px;
    border-radius: 16px 16px 0 0;
    gap: 10px;
  }
  .bk-count {
    font-size: 15px;
  }
  .bk-sub {
    font-size: 12px;
  }
  .bk-total {
    font-size: 16px;
  }
  .bk-btn {
    min-width: 90px;
    padding: 9px 14px;
    border-radius: 12px;
  }
}

/* ===== SMALL MOBILE ===== */
@media (max-width: 480px) {
  #seatGridWrap .bk-block {
    padding: 20px 6px 20px !important;
  }
  .seat-grid-card {
    padding: 24px 4px 10px !important;
  }
  .floor-block:first-child {
    margin-top: 12px !important;
    padding-top: 12px !important;
  }
  .seat-wrapper > .col-12 {
    max-width: 272px;
    gap: 8px !important;
  }
  .room-box {
    width: 42px;
    height: 42px;
    border-radius: 11px;
    font-size: 15px;
  }
  .bk-sticky {
    padding: 10px 12px;
    gap: 10px;
    border-radius: 14px 14px 0 0;
  }
  .bk-sticky-right {
    gap: 8px;
  }
  .bk-total {
    font-size: 15px;
  }
  .bk-btn {
    min-width: 86px;
    padding: 8px 12px;
  }
}

/* ===== HOVER INFO ===== */
.room-box {
  position: relative;
  overflow: visible;
}
.room-hover-info {
  position: absolute;
  left: 50%;
  bottom: calc(100% + 10px);
  transform: translateX(-50%);
  min-width: 130px;
  max-width: 170px;
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
  z-index: 99999;
  box-shadow: 0 10px 24px rgba(0, 0, 0, .18);
  transition: opacity .18s ease, visibility .18s ease, transform .18s ease;
}
.room-hover-info::after {
  content: "";
  position: absolute;
  top: 100%;
  left: 50%;
  transform: translateX(-50%);
  border-width: 6px;
  border-style: solid;
  border-color: rgba(0, 0, 0, 0.88) transparent transparent transparent;
}
.room-box:hover .room-hover-info {
  opacity: 1;
  visibility: visible;
  transform: translateX(-50%) translateY(-2px);
}
.room-hover-info strong {
  font-weight: 700;
  color: #fff;
}
@media (max-width: 768px) {
  .room-hover-info {
    min-width: 120px;
    font-size: 11px;
    padding: 7px 9px;
    bottom: calc(100% + 8px);
  }
}
@media (max-width: 480px) {
  .room-hover-info {
    min-width: 112px;
    font-size: 10.5px;
    padding: 6px 8px;
  }
}

/* ===== BOOKING LOADER ===== */
.booking-loader-overlay {
  position: fixed;
  inset: 0;
  z-index: 999999;

  /* Background blur + dark overlay */
  background: rgba(15, 23, 42, 0.35);
  backdrop-filter: blur(3px);

  display: flex;
  align-items: center;
  justify-content: center;
}

.booking-loader-overlay.d-none {
  display: none !important;
}

/* MAIN CIRCLE LOADER */
.booking-loader-spinner-ring {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  position: relative;
  animation: bookingLoaderRotate 1.1s linear infinite;
  background:
    conic-gradient(
      from 0deg,
      #0a9a4f 0deg 260deg,
      transparent 260deg 360deg
    );
  -webkit-mask: radial-gradient(circle, transparent 52px, #000 53px);
  mask: radial-gradient(circle, transparent 52px, #000 53px);
}

.booking-loader-spinner-ring::before {
  content: "";
  position: absolute;
  inset: 0;
  border-radius: 50%;
  background:
    radial-gradient(circle at 50% 8px, #0a9a4f 0 4px, transparent 4.5px),
    radial-gradient(circle at 65% 12px, #0a9a4f 0 4px, transparent 4.5px),
    radial-gradient(circle at 80% 22px, #0a9a4f 0 4px, transparent 4.5px),
    radial-gradient(circle at 92% 36px, #0a9a4f 0 4px, transparent 4.5px);
  pointer-events: none;
}

/* ROTATION */
@keyframes bookingLoaderRotate {
  100% {
    transform: rotate(360deg);
  }
}

/* Disable scroll while loading */
body.booking-loading {
  overflow: hidden !important;
}
</style>



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

    /* ─── LOADER HELPERS ─── */
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
        if (type === 'success') { bg = '#dcfce7'; color = '#166534'; border = '#86efac'; }
        else if (type === 'warning') { bg = '#ffedd5'; color = '#9a3412'; border = '#fdba74'; }
        else if (type === 'danger') { bg = '#fee2e2'; color = '#991b1b'; border = '#fca5a5'; }
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

    setTimeout(() => {
        if (redirectUrl) {
            window.location.href = redirectUrl;
        } else {
            window.location.reload();
        }
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
                    selected.set(room, { floornumber: floor, roomnumber: room, price: price });
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
        if (isOnline) {
            els.trx?.setAttribute('required', 'required');
            els.payMethod?.setAttribute('required', 'required');
        } else {
            els.trx?.removeAttribute('required');
            els.payMethod?.removeAttribute('required');
            if (els.trx) els.trx.value = '';
        }
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
                if (els.imgPreview) { els.imgPreview.src = ''; els.imgPreview.style.display = 'none'; }
                if (els.imgHint) els.imgHint.style.display = 'block';
                return;
            }
            const url = URL.createObjectURL(file);
            if (els.imgPreview) { els.imgPreview.src = url; els.imgPreview.style.display = 'block'; }
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
                    if (dates[0] && els.bkCheckin) { els.bkCheckin.value = toYMD(dates[0]); syncSticky(); }
                }
            });
        }
        if (els.bkCheckoutDisplay) {
            flatpickr(els.bkCheckoutDisplay, {
                dateFormat: 'd-m-Y',
                minDate: 'today',
                onChange(dates) {
                    if (dates[0] && els.bkCheckout) { els.bkCheckout.value = toYMD(dates[0]); syncSticky(); }
                }
            });
        }
    }

    function bindLocationDropdowns() {
        fetch('/divisions')
            .then(r => r.json())
            .then(data => {
                if (!els.division) return;
                els.division.innerHTML = '<option value="">Select Division</option>';
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.name;
                    els.division.appendChild(option);
                });
            });
        els.division?.addEventListener('change', function () {
            fetch(`/divisions/${this.value}/districts`)
                .then(r => r.json())
                .then(data => {
                    if (!els.district) return;
                    els.district.innerHTML = '<option value="">Select District</option>';
                    if (els.thana) els.thana.innerHTML = '<option value="">Select Thana</option>';
                    data.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.id;
                        option.textContent = item.name;
                        els.district.appendChild(option);
                    });
                });
        });
        els.district?.addEventListener('change', function () {
            fetch(`/districts/${this.value}/thanas`)
                .then(r => r.json())
                .then(data => {
                    if (!els.thana) return;
                    els.thana.innerHTML = '<option value="">Select Thana</option>';
                    data.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.id;
                        option.textContent = item.name;
                        els.thana.appendChild(option);
                    });
                });
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
            if (statusEl) { statusEl.textContent = ''; statusEl.className = 'form-text'; }
            guestHasAccount = false;
            existingImageUrl = null;
            existingImageName = null;
            const fileInput = document.getElementById('guestImage');
            if (fileInput) fileInput.setAttribute('required', 'required');
            const hiddenImg = document.getElementById('existingImageInput');
            if (hiddenImg) hiddenImg.value = '';
            if (val.length < 11) return;
            if (statusEl) { statusEl.textContent = '🔍 Searching...'; statusEl.className = 'form-text text-muted'; }
            timer = setTimeout(() => {
                fetch(`/guest/by-phone/${encodeURIComponent(val)}`)
                    .then(r => r.json())
                    .then(data => {
                        if (!data.found) {
                            guestHasAccount = false;
                            if (statusEl) { statusEl.textContent = '✅ New guest – fill in the form.'; statusEl.className = 'form-text text-success'; }
                            return;
                        }
                        guestHasAccount = !!data.has_account;
                        if (statusEl) {
                            statusEl.textContent = data.has_account
                                ? '✅ Guest found! Account exists. Please enter password.'
                                : '✅ Guest found! Info auto-filled.';
                            statusEl.className = 'form-text text-success';
                        }
                        const setVal = (id, value) => { const el = document.getElementById(id); if (el) el.value = value || ''; };
                        setVal('fullname', data.full_name);
                        setVal('guestEmail', data.email);
                        setVal('nid', data.nid);
                        if (data.image_url && data.image_name) {
                            existingImageUrl = data.image_url;
                            existingImageName = data.image_name;
                            const img = document.getElementById('imgPreview');
                            const hint = document.getElementById('imgHint');
                            if (img) { img.src = data.image_url; img.style.display = 'block'; }
                            if (hint) hint.style.display = 'none';
                            if (fileInput) fileInput.removeAttribute('required');
                            let hiddenImg = document.getElementById('existingImageInput');
                            if (!hiddenImg) {
                                hiddenImg = document.createElement('input');
                                hiddenImg.type = 'hidden';
                                hiddenImg.id = 'existingImageInput';
                                hiddenImg.name = 'existing_image';
                                document.getElementById('bookingForm').appendChild(hiddenImg);
                            }
                            hiddenImg.value = data.image_name;
                        }
                        const divEl = document.getElementById('division');
                        const disEl = document.getElementById('district');
                        const thaEl = document.getElementById('thana');
                        if (divEl && data.division_id) {
                            divEl.value = data.division_id;
                            divEl.dispatchEvent(new Event('change'));
                            setTimeout(() => {
                                if (disEl && data.district_id) {
                                    disEl.value = data.district_id;
                                    disEl.dispatchEvent(new Event('change'));
                                    setTimeout(() => { if (thaEl && data.thana_id) thaEl.value = data.thana_id; }, 800);
                                }
                            }, 800);
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        if (statusEl) { statusEl.textContent = '⚠️ Error checking phone.'; statusEl.className = 'form-text text-danger'; }
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
            input.classList.remove('is-invalid');
            input.setCustomValidity('');
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
        if (els.guestImage && !els.guestImage.files.length && !document.getElementById('existingImageInput')?.value) {
            showMsg('warning', `
                <div style="display:flex; gap:12px; align-items:flex-start;">
                    <div style="font-size:22px; line-height:1;">🖼️</div>
                    <div>
                        <div style="font-size:15px; font-weight:700; margin-bottom:6px;">Guest Image Required</div>
                        <div style="font-size:13px; line-height:1.6;">Please upload a valid guest image to proceed</div>
                    </div>
                </div>
            `);
            scrollToFormMsg();
            return;
        }
        if (!els.bkCheckin?.value || !els.bkCheckout?.value) {
            if (els.bkCheckinDisplay) {
                els.bkCheckinDisplay.style.cssText = '';
                els.bkCheckinDisplay.classList.remove('is-invalid');
                els.bkCheckinDisplay.setCustomValidity('');
            }

            if (els.bkCheckoutDisplay) {
                els.bkCheckoutDisplay.style.cssText = '';
                els.bkCheckoutDisplay.classList.remove('is-invalid');
                els.bkCheckoutDisplay.setCustomValidity('');
            }

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

        // 4. ROOM CHECK
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

        // 5. PAYMENT CHECK
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

        const formData = new FormData(els.bookingForm);

        fetch(els.bookingForm.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value || '',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(async res => {
            let data = {};
            try {
                data = await res.json();
            } catch (e) {
                data = { status: false, message: 'Invalid server response' };
            }

            return { ok: res.ok, data };
        })
        .then(({ ok, data }) => {
            isSubmitting = false;
            hideLoader();

            if (els.confirmBtn) {
                els.confirmBtn.disabled = false;
                els.confirmBtn.innerHTML = 'Confirm Booking <i class="bi bi-check2-circle ms-1"></i>';
            }

            if (ok && data.status) {
                const offcanvas = window.bootstrap?.Offcanvas?.getOrCreateInstance(els.bookingCanvas);

                showSuccessAlert(data.message || 'Booking successfully!', data.redirect_url);

                if (offcanvas) {
                    setTimeout(() => {
                        offcanvas.hide();
                    }, 300);
                }

                return;
            }

            showMsg('danger', `
                <div style="display:flex; gap:12px; align-items:flex-start;">
                    <div style="font-size:22px; line-height:1;">⚠️</div>
                    <div>
                        <div style="font-size:15px; font-weight:700; margin-bottom:6px;">Booking failed</div>
                        <div style="font-size:13px; line-height:1.6;">${data.message || 'Please try again.'}</div>
                    </div>
                </div>
            `);
            scrollToFormMsg();
        })
        .catch(error => {
            console.error(error);
            isSubmitting = false;
            hideLoader();

            if (els.confirmBtn) {
                els.confirmBtn.disabled = false;
                els.confirmBtn.innerHTML = 'Confirm Booking <i class="bi bi-check2-circle ms-1"></i>';
            }

            showMsg('danger', `
                <div style="display:flex; gap:12px; align-items:flex-start;">
                    <div style="font-size:22px; line-height:1;">🌐</div>
                    <div>
                        <div style="font-size:15px; font-weight:700; margin-bottom:6px;">Network error</div>
                        <div style="font-size:13px; line-height:1.6;">Please try again.</div>
                    </div>
                </div>
            `);
            scrollToFormMsg();
        });
    });
}
function scrollToFormMsg() {

    setTimeout(() => {
        els.formMsg?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 100);
    
}

    bindOpenCanvas();
    bindLocationDropdowns();
    bindRooms();
    bindFormToggle();
    bindImagePreview();
    bindPayment();
    bindDatepickers();
    bindAjaxSubmit();
    syncSticky();
    bindPhoneAutofill();
});
</script>