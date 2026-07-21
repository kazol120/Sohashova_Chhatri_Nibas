@extends('Frontend.layouts.app')
@section('title', 'Help Desk')

@section('content')

<div class="hompagesolution">
<section class="hero-search-wrap bookingpage" style="background-image: linear-gradient(rgba(3, 51, 100, 0.65), rgba(3, 51, 100, 0.65)), url('{{ asset('help_desk/help.png') }}'); display: flex; align-items: center; justify-content: center; text-align: center; padding-top: 140px !important; padding-bottom: 60px !important;">
  <div class="container text-white">
    <h1 style="font-size: 38px; font-weight: 800; text-shadow: 0 2px 12px rgba(0,0,0,0.4); margin: 0 auto 12px; color: #ffffff; max-width: 800px; line-height: 1.2;">Sohashova Chhatri Nibas Help</h1>
    <p style="font-size: 16px; font-weight: 400; opacity: 0.95; max-width: 650px; margin: 0 auto; text-shadow: 0 1px 6px rgba(0,0,0,0.3); line-height: 1.6; color: #e2e8f0;">
      Sohashova support request for customer support representations host common goal.
    </p>
  </div>
</section>

<section class="stepbar-wrap">
  <div class="stepbar"></div>
</section>

<section class="help-wrap py-4 py-lg-5">
  <div class="container">
    <div class="help-head">
      <div>
        <h2 class="help-title">Help & Customer Service</h2>
        <p class="help-sub">Booking, payment, room issues—সবকিছুর জন্য দ্রুত সাপোর্ট নিন।</p>
      </div>

      <div class="help-badges">
        <span class="help-badge">24/7 Front Desk</span>
        <span class="help-badge is-green">Fast Response</span>
      </div>
    </div>

    <div class="row g-3 g-lg-4 mt-2">
      <div class="col-md-6 col-lg-3">
        <a class="qcard" href="tel:+8801713558866">
          <div class="qicon"><i class="bi bi-telephone"></i></div>
          <div>
            <div class="qtitle">Call Now</div>
            <div class="qmeta">+880 1713-558-866</div>
          </div>
        </a>
      </div>

      <div class="col-md-6 col-lg-3">
        <a class="qcard" href="https://wa.me/8801713558866" target="_blank">
          <div class="qicon"><i class="bi bi-whatsapp"></i></div>
          <div>
            <div class="qtitle">WhatsApp</div>
            <div class="qmeta">Chat with support</div>
          </div>
        </a>
      </div>
    </div>

    <div class="row g-4 mt-2">
      <div class="col-lg-6">
        <div class="help-card">
          <h5 class="hcard-title"><i class="bi bi-grid"></i> Support Categories</h5>

          <div class="cat-grid mt-3">
            <div class="cat-item"><i class="bi bi-calendar-check"></i> Booking / Reservation</div>
            <div class="cat-item"><i class="bi bi-box-arrow-in-right"></i> Check-in / Check-out</div>
            <div class="cat-item"><i class="bi bi-credit-card"></i> Payment / Refund</div>
            <div class="cat-item"><i class="bi bi-broom"></i> Housekeeping</div>
            <div class="cat-item"><i class="bi bi-tools"></i> Maintenance (AC/Electric)</div>
            <div class="cat-item"><i class="bi bi-wifi"></i> WiFi / Internet</div>
            <div class="cat-item"><i class="bi bi-cup-hot"></i> Restaurant / Breakfast</div>
            <div class="cat-item"><i class="bi bi-truck"></i> Pickup / Transport</div>
            <div class="cat-item"><i class="bi bi-shield-check"></i> Security / Lost & Found</div>
            <div class="cat-item"><i class="bi bi-emoji-smile"></i> Complaint / Feedback</div>
          </div>

          <div class="alert alert-warning mt-3 mb-0 rounded-4">
            <strong>Emergency:</strong> জরুরি হলে সরাসরি কল করুন (+880 1713-558-866)
          </div>
        </div>

   
      </div>

      <div class="col-lg-6">
        <div class="help-card">
          <h5 class="hcard-title">
            <i class="bi bi-ticket-perforated"></i> Submit a Support Request Desk Email
          </h5>

          @if($errors->any())
            <div class="alert alert-danger mt-3">
              <ul class="mb-0">
                @foreach($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <form class="mt-3" action="{{ route('helpdesk.send') }}" method="POST">
            @csrf

            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">
                  Registered Booking Phone Number <span class="req">*</span>
                </label>
                <input
                  type="text"
                  name="phone"
                  class="form-control"
                  value="{{ old('phone') }}"
                  required
                >
              </div>

              <div class="col-12">
                <label class="form-label">Details <span class="req">*</span></label>
                <textarea
                  name="message"
                  class="form-control"
                  rows="4"
                  placeholder="Describe your issue..."
                  required
                >{{ old('message') }}</textarea>
              </div>

              <div class="col-12">
                <button class="btn btn-success w-100 py-2 rounded-4" type="submit">
                  Submit Request <i class="bi bi-send ms-1"></i>
                </button>
              </div>
            </div>
          </form>
        </div>

        <div class="help-card mt-4">
          <h5 class="hcard-title"><i class="bi bi-geo-alt"></i> Address & Hours</h5>
          <div class="mt-2">
            <div class="addr-row"><i class="bi bi-clock"></i> Front Desk: 24/7</div>
            <div class="addr-row"><i class="bi bi-geo"></i> Rangpur, Bangladesh</div>
            <div class="addr-row"><i class="bi bi-envelope"></i> support@hotel.com</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
</div>

@endsection

@push('scripts')
<style>
  .custom-top-toast {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 99999;
    min-width: 320px;
    max-width: 420px;
    background: #12b76a;
    color: #fff;
    border-radius: 14px;
    box-shadow: 0 12px 30px rgba(18, 183, 106, 0.28);
    padding: 16px 18px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 18px;
    font-weight: 700;
    transform: translateX(120%);
    opacity: 0;
    transition: all 0.35s ease;
  }

  .custom-top-toast.show {
    transform: translateX(0);
    opacity: 1;
  }

  .custom-top-toast.hide {
    transform: translateX(120%);
    opacity: 0;
  }

  .custom-top-toast .toast-icon {
    width: 26px;
    height: 26px;
    min-width: 26px;
    border-radius: 6px;
    background: rgba(255,255,255,0.15);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
  }

  @media (max-width: 576px) {
    .custom-top-toast {
      top: 14px;
      right: 14px;
      left: 14px;
      min-width: auto;
      max-width: none;
      font-size: 16px;
    }
  }
</style>

@if(session('success'))
<script>
(function () {
    function showTopToast(message) {
        const existing = document.getElementById('customTopToast');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.id = 'customTopToast';
        toast.className = 'custom-top-toast';
        toast.innerHTML = `
            <span class="toast-icon">✓</span>
            <span>${message}</span>
        `;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('show');
        }, 100);

        setTimeout(() => {
            toast.classList.remove('show');
            toast.classList.add('hide');
        }, 3000);

        setTimeout(() => {
            if (toast) toast.remove();
        }, 3600);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            showTopToast(@json(session('success')));
        });
    } else {
        showTopToast(@json(session('success')));
    }
})();
</script>
@endif

@if(session('error'))
<script>
(function () {
    function showTopErrorToast(message) {
        const existing = document.getElementById('customTopToast');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.id = 'customTopToast';
        toast.className = 'custom-top-toast';
        toast.style.background = '#ef4444';
        toast.style.boxShadow = '0 12px 30px rgba(239, 68, 68, 0.28)';
        toast.innerHTML = `
            <span class="toast-icon">✕</span>
            <span>${message}</span>
        `;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('show');
        }, 100);

        setTimeout(() => {
            toast.classList.remove('show');
            toast.classList.add('hide');
        }, 3000);

        setTimeout(() => {
            if (toast) toast.remove();
        }, 3600);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            showTopErrorToast(@json(session('error')));
        });
    } else {
        showTopErrorToast(@json(session('error')));
    }
})();
</script>
@endif
@endpush