<section>

<!-- ===================== HEADER START ===================== -->
<div class="nsm-header-wrap" id="nsmHeaderWrap">

  <!-- Announcement / Marquee Top Bar -->
  @php
    $latestNotice = \App\Models\Frontend\Notice::latest()->first();
    $noticeText = $latestNotice ? $latestNotice->notice : '';
  @endphp
  <div class="nsm-marquee-bar">
    <div class="nsm-marquee-inner d-flex align-items-center">
      <i class="bi bi-megaphone-fill text-white me-2.5" style="font-size: 14px; flex-shrink: 0;"></i>
      <marquee behavior="scroll" direction="left" scrollamount="6"
               class="nsm-marquee-text m-0" style="cursor:pointer;"
               onmouseover="this.stop();" onmouseout="this.start();">
        {{ $noticeText }}
      </marquee>
    </div>
  </div>

  <!-- Main Full-Width Header Row -->
  <header class="nsm-main-header">
    <div class="nsm-header-inner">

      <!-- LEFT : Brand Logo + Title + Tagline -->
      <div class="nsm-brand-area d-flex align-items-center gap-2">
        <!-- Mobile hamburger toggle button -->
        <button class="btn p-0 border-0 shadow-none d-lg-none me-2" type="button"
                data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu">
          <i class="bi bi-list fs-2" style="color: #033364;"></i>
        </button>

        <a href="{{ url('/') }}" class="nsm-brand-box d-flex align-items-center gap-2 gap-md-3 text-decoration-none">
          <img src="{{ $wsLogo ?? asset('image/logo.png') }}" alt="{{ $wsTitle ?? 'TSS Villa' }}" class="nsm-brand-logo">
          <div class="d-flex flex-column justify-content-center">
            <h4 class="nsm-brand-title mb-0">TSS Villa</h4>
            <span class="nsm-brand-tagline d-none d-sm-block">Comfortable Stay | Peaceful Life</span>
          </div>
        </a>
      </div>

      <!-- CENTER / MIDDLE : Desktop Nav Links (Centered) -->
      <div class="nsm-nav-center d-none d-lg-flex align-items-center justify-content-center">
        <!-- Home -->
        <div class="d-flex flex-column align-items-center">
          <a href="{{ url('/') }}" class="nsm-menu-item item-home {{ request()->is('/') ? 'active' : '' }}">
            <i class="bi bi-house-door-fill"></i>
            <span>Home</span>
          </a>
          @if(request()->is('/'))
            <span class="nsm-active-underline"></span>
          @endif
        </div>

        <!-- Gallery -->
        <a href="{{ url('/#portfolioBg') }}" class="nsm-menu-item item-gallery">
          <i class="bi bi-images"></i>
          <span>Gallery</span>
        </a>

        <!-- Advantages -->
        <a href="{{ url('/#advantagesSection') }}" class="nsm-menu-item item-advantages">
          <i class="bi bi-shield-check"></i>
          <span>Advantages</span>
        </a>

        <!-- Booking Now -->
        <a href="{{ url('booking-now') }}" class="nsm-menu-item item-booking {{ request()->is('booking-now') ? 'active' : '' }}">
          <i class="bi bi-calendar-check"></i>
          <span>Booking Now</span>
        </a>

        <!-- Help Desk -->
        <a href="{{ url('guest-support') }}" class="nsm-menu-item item-help {{ request()->is('guest-support') ? 'active' : '' }}">
          <i class="bi bi-headset"></i>
          <span>Help Desk</span>
        </a>
      </div>

      <!-- RIGHT : Search / Language / Login / Phone (Aligned Right) -->
      <div class="nsm-nav-right d-flex align-items-center justify-content-end">
        
        <!-- Resident Phone Search (Always Visible on Desktop, Auto Loads on 11 Digits) -->
        <div class="nsm-search-container position-relative d-none d-xl-flex align-items-center m-0">
          <div class="nsm-search-pill" id="nsmSearchPillWrap">
            <input 
              type="search" 
              id="nsmHeaderPhoneSearch"
              name="resident_phone_search_rand_{{ Str::random(8) }}" 
              value="" 
              class="form-control border-0 bg-transparent px-3.5 h-100 shadow-none" 
              placeholder="Search..." 
              maxlength="11"
              autocomplete="new-password"
              readonly
              onfocus="this.removeAttribute('readonly');"
              data-lpignore="true"
              data-form-type="other"
            />
            <div id="nsmSearchLoading" class="spinner-border spinner-border-sm text-primary me-3 d-none" role="status" style="width: 14px; height: 14px; flex-shrink: 0;">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
          <!-- Live Feedback Tooltip -->
          <div id="nsmSearchFeedback" class="nsm-search-feedback d-none">
            <span id="nsmSearchFeedbackText"></span>
          </div>
        </div>

        <!-- Language Switcher -->
        <div class="lang-switcher-container d-none d-md-inline-flex">
          <div class="d-flex align-items-center position-relative">
            <i class="bi bi-globe2 position-absolute text-muted" style="left: 14px; font-size: 14px; pointer-events: none;"></i>
            <select id="customLangSelector" class="form-select rounded-pill" style="padding-left: 36px !important; padding-right: 28px !important; border: 1.5px solid #cbd5e1; color: #0f172a; font-weight: 600; font-size: 14px; padding-top: 8px; padding-bottom: 8px; background-color: #ffffff; cursor: pointer;">
              <option value="en">English</option>
              <option value="bn">বাংলা</option>
            </select>
          </div>
        </div>

        <!-- Login Button -->
        @guest
        <a href="#" class="btn rounded-pill px-4 py-2 fw-bold d-inline-flex align-items-center gap-1.5 nsm-login-btn" data-bs-toggle="modal" data-bs-target="#loginModal">
          <i class="bi bi-person-fill" style="font-size: 16px;"></i>
          <span class="d-none d-sm-inline">Login</span>
        </a>
        @else
        <a href="{{ route('dashboard') }}" class="nsm-user-avatar position-relative" title="Dashboard">
          <i class="bi bi-person-fill" style="font-size: 20px; color: #033364;"></i>
          <span class="position-absolute bg-success border border-white rounded-circle" style="width: 10px; height: 10px; bottom: 0; right: 0;"></span>
        </a>
        @endguest

        <!-- Phone Number Button -->
        <a href="tel:{{ $wsPhone ?? '01977270920' }}" class="btn rounded-pill px-3 px-sm-4 py-2 text-white fw-bold d-inline-flex align-items-center gap-1.5 gap-sm-2 nsm-phone-btn">
          <i class="bi bi-telephone-fill" style="font-size: 14px;"></i>
          <span class="phone-text d-none d-sm-inline">+88{{ $wsPhone ?? '01977270920' }}</span>
          <span class="phone-text-mobile d-inline d-sm-none">Call</span>
        </a>

      </div>

    </div>
  </header>

</div>
<!-- ===================== HEADER END ===================== -->
</section>

<!-- ===================== Mobile Offcanvas Menu ===================== -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
  {{-- Header --}}
  <div class="offcanvas-header text-white py-3" style="background: linear-gradient(135deg, #033364 0%, #02172c 100%);">
    <div class="d-flex align-items-center gap-2">
      <div style="width:36px;height:36px;border-radius:8px;background:rgba(255,255,255,0.18);display:flex;align-items:center;justify-content:center;">
        <i class="bi bi-building text-white" style="font-size:18px;"></i>
      </div>
      <h5 class="offcanvas-title mb-0 text-white fw-bold" id="mobileMenuLabel">TSS Villa</h5>
    </div>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  {{-- Body --}}
  <div class="offcanvas-body p-3">
    <!-- Mobile Resident Search -->
    <div class="mb-3 px-1">
      <label class="form-label small text-muted fw-bold mb-1.5" style="letter-spacing: .06em; text-transform: uppercase; font-size: 11px;">Resident Search / শিক্ষার্থী সার্চ</label>
      <form action="{{ route('resident.overview') }}" method="GET">
        <div class="input-group" style="border: 1.5px solid #033364; border-radius: 10px; overflow: hidden;">
          <input type="tel" name="phone" value="" class="form-control border-0 px-3 py-2.5" placeholder="017xxxxxxxx" style="font-size: 14px; font-weight: 600;" required autocomplete="tel">
          <button class="btn btn-primary px-3 fw-bold" type="submit" style="background: #033364; border: none;">
            <i class="bi bi-search"></i>
          </button>
        </div>
      </form>
    </div>

    <!-- Mobile Language Selector -->
    <div class="mb-4 px-1">
      <label class="form-label small text-muted fw-bold mb-1.5" style="letter-spacing: .06em; text-transform: uppercase; font-size: 11px;">Language / ভাষা</label>
      <select id="mobileLangSelector" class="form-select" style="border: 1.5px solid #033364; color: #033364; font-weight: 700; border-radius: 10px; padding: 8px 12px; font-size: 14px;">
        <option value="en">English</option>
        <option value="bn">বাংলা</option>
      </select>
    </div>

    <p style="font-size:11px;font-weight:600;color:#9ca3af;letter-spacing:.06em;text-transform:uppercase;margin:8px 0 6px 4px;">Menu</p>

    <a href="{{ url('/') }}" class="off-item d-flex align-items-center gap-3 p-2.5 rounded-3 text-decoration-none mb-1" style="color:inherit;">
      <span style="width:36px;height:36px;border-radius:8px;background:#e2ebf4;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="bi bi-house-door-fill" style="font-size:18px;color:#033364;"></i>
      </span>
      <div class="flex-grow-1">
        <p class="mb-0" style="font-size:15px;font-weight:600;">Home</p>
      </div>
      <i class="bi bi-chevron-right text-muted" style="font-size:14px;"></i>
    </a>

    <a href="{{ url('/#portfolioBg') }}" class="off-item d-flex align-items-center gap-3 p-2.5 rounded-3 text-decoration-none mb-1" style="color:inherit;">
      <span style="width:36px;height:36px;border-radius:8px;background:#e6f1fb;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="bi bi-images" style="font-size:18px;color:#185fa5;"></i>
      </span>
      <div class="flex-grow-1">
        <p class="mb-0" style="font-size:15px;font-weight:600;">Gallery</p>
      </div>
      <i class="bi bi-chevron-right text-muted" style="font-size:14px;"></i>
    </a>

    <a href="{{ url('/#advantagesSection') }}" class="off-item d-flex align-items-center gap-3 p-2.5 rounded-3 text-decoration-none mb-1" style="color:inherit;">
      <span style="width:36px;height:36px;border-radius:8px;background:#eaf3de;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="bi bi-shield-check" style="font-size:18px;color:#3b6d11;"></i>
      </span>
      <div class="flex-grow-1">
        <p class="mb-0" style="font-size:15px;font-weight:600;">Advantages</p>
      </div>
      <i class="bi bi-chevron-right text-muted" style="font-size:14px;"></i>
    </a>

    <a href="{{ url('booking-now') }}" class="off-item d-flex align-items-center gap-3 p-2.5 rounded-3 text-decoration-none mb-1" style="color:inherit;">
      <span style="width:36px;height:36px;border-radius:8px;background:#faeeda;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="bi bi-calendar-check" style="font-size:18px;color:#854f0b;"></i>
      </span>
      <div class="flex-grow-1">
        <p class="mb-0" style="font-size:15px;font-weight:600;">Booking now</p>
      </div>
      <i class="bi bi-chevron-right text-muted" style="font-size:14px;"></i>
    </a>

    <a href="{{ url('guest-support') }}" class="off-item d-flex align-items-center gap-3 p-2.5 rounded-3 text-decoration-none mb-3" style="color:inherit;">
      <span style="width:36px;height:36px;border-radius:8px;background:#fbeaf0;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="bi bi-headset" style="font-size:18px;color:#993556;"></i>
      </span>
      <div class="flex-grow-1">
        <p class="mb-0" style="font-size:15px;font-weight:600;">Help line</p>
      </div>
      <i class="bi bi-chevron-right text-muted" style="font-size:14px;"></i>
    </a>

    @auth
    <a href="{{ route('dashboard') }}" class="off-item d-flex align-items-center gap-3 p-2.5 rounded-3 text-decoration-none mb-1" style="color:inherit;">
      <span style="width:36px;height:36px;border-radius:8px;background:#dbeafe;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="bi bi-speedometer2" style="font-size:18px;color:#1d4ed8;"></i>
      </span>
      <div class="flex-grow-1">
        <p class="mb-0" style="font-size:15px;font-weight:600;">Dashboard</p>
      </div>
      <i class="bi bi-chevron-right text-muted" style="font-size:14px;"></i>
    </a>
    <a href="{{ route('logout') }}" class="off-item d-flex align-items-center gap-3 p-2.5 rounded-3 text-decoration-none mb-3" style="color:inherit;"
       onclick="event.preventDefault(); document.getElementById('logout-form-mobile').submit();">
      <span style="width:36px;height:36px;border-radius:8px;background:#fee2e2;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="bi bi-box-arrow-right" style="font-size:18px;color:#dc2626;"></i>
      </span>
      <div class="flex-grow-1">
        <p class="mb-0" style="font-size:15px;font-weight:600;color:#dc2626;">Logout</p>
      </div>
    </a>
    <form id="logout-form-mobile" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
    @endauth

    {{-- Contact card --}}
    <div class="p-3.5 rounded-3 bg-light border">
      <p class="mb-1" style="font-size:14px;font-weight:700;">Need assistance?</p>
      <p class="mb-2 text-muted" style="font-size:12.5px;">Our team is available around the clock.</p>
      <a href="{{ url('guest-support') }}" style="font-size:13.5px;font-weight:700;color:#033364;text-decoration:none;">
        <i class="bi bi-telephone me-1"></i> Contact us
      </a>
    </div>

  </div>
</div>
<!-- ===================== Mobile Offcanvas Menu End ===================== -->

<style>
/* ===================== FULLY RESPONSIVE LUXURY HEADER CSS ===================== */
:root {
    --nsm-header-height: 110px;
}

.nsm-header-wrap * { box-sizing: border-box; }
.nsm-header-wrap {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 1030;
    display: flex;
    flex-direction: column;
    margin: 0;
    padding: 0;
}

/* ---- Marquee Top Bar ---- */
.nsm-marquee-bar {
    width: 100%;
    background: linear-gradient(90deg, #022040 0%, #033364 50%, #022040 100%);
    padding: 8px 0;
    margin: 0;
    overflow: hidden;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}
.nsm-marquee-inner {
    width: 100%;
    max-width: 1440px;
    margin: 0 auto;
    padding: 0 24px;
    display: flex;
    align-items: center;
}
.nsm-marquee-text {
    display: block;
    width: 100%;
    color: #f8fafc;
    font-size: 13.5px;
    font-weight: 500;
    letter-spacing: 0.3px;
}

/* ---- Main Header ---- */
.nsm-main-header {
    width: 100%;
    position: relative;
    background: linear-gradient(90deg, #dcf0fa 0%, #ffffff 12%, #ffffff 88%, #dcf0fa 100%);
    box-shadow: 0 4px 20px rgba(3, 51, 100, 0.06);
    border-bottom: 1px solid rgba(3, 51, 100, 0.06);
    margin: 0;
    padding: 12px 0;
    overflow: visible;
}

.nsm-header-inner {
    width: 100%;
    max-width: 1440px;
    margin: 0 auto;
    padding: 0 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    position: relative;
    z-index: 2;
}

/* ---- Header Sections ---- */
.nsm-brand-area {
    flex-shrink: 0;
    display: flex;
    align-items: center;
}

.nsm-nav-center {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 1;
    justify-content: center;
}

.nsm-nav-right {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-shrink: 0;
}

/* ---- Brand Box ---- */
.nsm-brand-box {
    flex-shrink: 0;
}
.nsm-brand-logo {
    width: 50px;
    height: 50px;
    object-fit: contain;
    transition: all 0.2s ease;
}
.nsm-brand-title {
    color: #033364;
    font-size: 22px;
    font-weight: 800;
    letter-spacing: -0.3px;
    line-height: 1.15;
    transition: all 0.2s ease;
}
.nsm-brand-tagline {
    font-size: 11.5px;
    color: #64748b;
    font-weight: 600;
    letter-spacing: 0.1px;
}

/* ---- Menu Items ---- */
.nsm-menu-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 12px;
    border: none;
    border-radius: 11px;
    text-decoration: none;
    font-size: 13.5px;
    font-weight: 700;
    white-space: nowrap;
    transition: all 0.2s ease-in-out;
    background-color: transparent;
    color: #0f172a;
}
.nsm-menu-item i {
    font-size: 15px;
    color: #033364;
    transition: color 0.2s ease-in-out;
}

/* Home / Active Item specific styling */
.nsm-menu-item.active,
.nsm-menu-item.item-home.active {
    background-color: #033364 !important;
    border: none !important;
    color: #ffffff !important;
    padding: 8px 18px !important;
    box-shadow: 0 4px 12px rgba(3, 51, 100, 0.16);
}
.nsm-menu-item.active i,
.nsm-menu-item.item-home.active i {
    color: #ffffff !important;
}

.nsm-menu-item:hover:not(.active) {
    color: #033364;
    background-color: rgba(3, 51, 100, 0.05);
}

.nsm-active-underline {
    width: 38px;
    height: 3px;
    background: #033364;
    border-radius: 4px;
    margin-top: 3px;
}

/* ---- Right Action Section ---- */
.nsm-search-container {
    position: relative;
}
.nsm-search-pill {
    width: 150px;
    height: 40px;
    background: #f1f5f9;
    border: 1.5px solid #e2e8f0;
    border-radius: 50rem;
    overflow: hidden;
    transition: all 0.25s ease;
    display: flex;
    align-items: center;
}
.nsm-search-pill:focus-within {
    background: #ffffff;
    border-color: #033364;
    box-shadow: 0 0 0 3px rgba(3, 51, 100, 0.08);
}
.nsm-search-pill.is-loading {
    border-color: #033364;
    background: #ffffff;
}
.nsm-search-pill.is-error {
    border-color: #ef4444 !important;
    background: #fef2f2 !important;
    animation: nsmShake 0.4s ease;
}
.nsm-search-pill.is-success {
    border-color: #10b981 !important;
    background: #ecfdf5 !important;
}
.nsm-search-pill input {
    font-size: 13.5px;
    font-weight: 600;
    color: #0f172a;
}
.nsm-search-pill input::placeholder {
    color: #94a3b8;
    font-size: 13px;
    font-weight: 500;
}

/* Floating feedback tooltip */
.nsm-search-feedback {
    position: absolute;
    top: calc(100% + 10px);
    left: 50%;
    transform: translateX(-50%);
    background: #dc2626;
    color: #ffffff;
    font-size: 12.5px;
    font-weight: 700;
    padding: 7px 16px;
    border-radius: 8px;
    white-space: nowrap;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
    z-index: 9999;
    pointer-events: none;
    transition: all 0.25s ease;
    display: flex;
    align-items: center;
    gap: 6px;
}
.nsm-search-feedback::before {
    content: '';
    position: absolute;
    top: -6px;
    left: 50%;
    transform: translateX(-50%);
    border-width: 0 6px 6px 6px;
    border-style: solid;
    border-color: transparent transparent #dc2626 transparent;
}
.nsm-search-feedback.error {
    background: #dc2626;
}
.nsm-search-feedback.error::before {
    border-color: transparent transparent #dc2626 transparent;
}
.nsm-search-feedback.success {
    background: #059669;
}
.nsm-search-feedback.success::before {
    border-color: transparent transparent #059669 transparent;
}
@keyframes nsmShake {
    0%, 100% { transform: translateX(0); }
    20%, 60% { transform: translateX(-4px); }
    40%, 80% { transform: translateX(4px); }
}

/* Language selector */
.lang-switcher-container select {
    border: 1.5px solid #cbd5e1 !important;
    color: #0f172a !important;
    font-weight: 600 !important;
    font-size: 13.5px !important;
    padding-top: 7px !important;
    padding-bottom: 7px !important;
    padding-left: 34px !important;
    padding-right: 26px !important;
    border-radius: 50rem !important;
    background-color: #ffffff !important;
}

/* Login Button */
.nsm-login-btn {
    border: 1.5px solid #cbd5e1;
    color: #033364;
    background: #ffffff;
    font-size: 13.5px;
    font-weight: 700;
    padding: 7px 18px;
    border-radius: 50rem;
    transition: all 0.2s;
}
.nsm-login-btn:hover {
    background: #033364;
    border-color: #033364;
    color: #ffffff;
}

/* User Avatar Logged-in */
.nsm-user-avatar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e6f1fb;
    border: 1.5px solid #033364;
}

/* Phone Button */
.nsm-phone-btn {
    background: #00827b;
    border: none;
    font-size: 13.5px;
    font-weight: 700;
    padding: 7px 16px;
    border-radius: 50rem;
    color: #ffffff;
    white-space: nowrap;
    transition: all 0.2s;
}
.nsm-phone-btn:hover {
    background: #006e68;
    color: #ffffff;
    transform: translateY(-1px);
}

/* Page content push */
body {
    padding-top: var(--nsm-header-height);
}

/* ===================== COMPREHENSIVE RESPONSIVE BREAKPOINTS ===================== */

/* Extra Large Screens (1200px to 1399px) */
@media (max-width: 1399.98px) {
    .nsm-header-inner, .nsm-marquee-inner {
        padding: 0 16px;
        gap: 12px;
    }
    .nsm-nav-center {
        gap: 6px;
    }
    .nsm-menu-item {
        padding: 7px 9px;
        font-size: 13px;
    }
    .nsm-search-pill {
        width: 135px;
    }
    .nsm-brand-title {
        font-size: 20px;
    }
    .nsm-brand-logo {
        width: 46px;
        height: 46px;
    }
}

/* Large Screens / Small Laptops (992px to 1199px) */
@media (max-width: 1199.98px) {
    .nsm-header-inner {
        padding: 0 14px;
        gap: 8px;
    }
    .nsm-nav-center {
        gap: 4px;
    }
    .nsm-menu-item {
        padding: 6px 8px;
        font-size: 12.5px;
        gap: 4px;
    }
    .nsm-menu-item.active,
    .nsm-menu-item.item-home.active {
        padding: 6px 12px !important;
    }
    .nsm-brand-title {
        font-size: 19px;
    }
    .nsm-brand-tagline {
        font-size: 10.5px;
    }
    .nsm-login-btn, .nsm-phone-btn {
        padding: 6px 12px;
        font-size: 12.5px;
    }
}

/* Tablets (768px to 991px) */
@media (max-width: 991.98px) {
    .nsm-header-inner {
        padding: 0 16px;
        justify-content: space-between;
    }
    .nsm-brand-title {
        font-size: 20px;
    }
    .nsm-brand-logo {
        width: 44px;
        height: 44px;
    }
    .nsm-nav-right {
        gap: 8px;
    }
    .nsm-phone-btn {
        padding: 6px 14px;
        font-size: 13px;
    }
    .nsm-login-btn {
        padding: 6px 14px;
        font-size: 13px;
    }
}

/* Mobile Devices (< 768px) */
@media (max-width: 767.98px) {
    .nsm-marquee-bar {
        padding: 6px 0;
    }
    .nsm-marquee-inner {
        padding: 0 14px;
    }
    .nsm-marquee-text {
        font-size: 12.5px;
    }
    .nsm-main-header {
        padding: 10px 0;
    }
    .nsm-header-inner {
        padding: 0 12px;
        gap: 8px;
    }
    .nsm-brand-title {
        font-size: 19px;
    }
    .nsm-brand-logo {
        width: 40px;
        height: 40px;
    }
    .nsm-brand-tagline {
        display: none !important;
    }
    .nsm-nav-right {
        gap: 6px;
    }
    .nsm-login-btn {
        padding: 6px 12px;
        font-size: 12.5px;
    }
    .nsm-phone-btn {
        padding: 6px 10px;
        font-size: 12.5px;
    }
}

/* Small Mobile (< 480px) */
@media (max-width: 479.98px) {
    .nsm-header-inner {
        padding: 0 8px;
        gap: 6px;
    }
    .nsm-brand-title {
        font-size: 17px;
    }
    .nsm-brand-logo {
        width: 36px;
        height: 36px;
    }
    .nsm-login-btn {
        padding: 5px 10px;
        font-size: 12px;
    }
    .nsm-phone-btn {
        padding: 5px 9px;
        font-size: 12px;
    }
}
</style>

<script>
(function(){
    function adjustHeaderOffset(){
        const wrap = document.getElementById('nsmHeaderWrap');
        if(!wrap) return;
        const h = wrap.offsetHeight;
        document.documentElement.style.setProperty('--nsm-header-height', h + 'px');
        document.body.style.paddingTop = h + 'px';
    }

    if(document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', adjustHeaderOffset);
    } else {
        adjustHeaderOffset();
    }

    window.addEventListener('resize', adjustHeaderOffset);
    window.addEventListener('load', adjustHeaderOffset);
    setTimeout(adjustHeaderOffset, 300);
})();

document.addEventListener('DOMContentLoaded', function () {
    // ---- Auto Live Search upon 11-digit input ----
    const phoneInput = document.getElementById('nsmHeaderPhoneSearch');
    const pillWrap = document.getElementById('nsmSearchPillWrap');
    const loadingSpinner = document.getElementById('nsmSearchLoading');
    const feedbackBox = document.getElementById('nsmSearchFeedback');
    const feedbackText = document.getElementById('nsmSearchFeedbackText');

    let feedbackTimer = null;

    function showFeedback(text, type = 'error') {
        if (!feedbackBox) return;
        clearTimeout(feedbackTimer);
        const iconHtml = type === 'success' 
            ? '<i class="bi bi-check-circle-fill" style="font-size: 14px;"></i> ' 
            : '<i class="bi bi-exclamation-triangle-fill" style="font-size: 14px;"></i> ';
        feedbackBox.innerHTML = iconHtml + '<span>' + text + '</span>';
        feedbackBox.className = 'nsm-search-feedback ' + type;
        feedbackBox.classList.remove('d-none');

        if (type === 'error') {
            pillWrap?.classList.add('is-error');
            setTimeout(() => pillWrap?.classList.remove('is-error'), 800);
        }

        feedbackTimer = setTimeout(() => {
            feedbackBox.classList.add('d-none');
        }, 3600);
    }

    function checkAndLoadResident(phoneVal) {
        const clean = phoneVal.replace(/[^0-9]/g, '');
        if (clean.length !== 11) return;

        loadingSpinner?.classList.remove('d-none');
        pillWrap?.classList.add('is-loading');

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                   || '{{ csrf_token() }}';

        fetch('{{ route("resident.search-phone") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ phone: clean })
        })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(({ status, body }) => {
            loadingSpinner?.classList.add('d-none');
            pillWrap?.classList.remove('is-loading');

            if (body.success) {
                pillWrap?.classList.add('is-success');
                showFeedback('শিক্ষার্থীর তথ্য পাওয়া গেছে! লোড হচ্ছে...', 'success');
                setTimeout(() => {
                    window.location.href = '{{ route("resident.overview") }}?phone=' + encodeURIComponent(clean);
                }, 400);
            } else {
                showFeedback(body.message || 'শিক্ষার্থীর তথ্য পাওয়া যায়নি / Not Found', 'error');
            }
        })
        .catch(err => {
            loadingSpinner?.classList.add('d-none');
            pillWrap?.classList.remove('is-loading');
            showFeedback('শিক্ষার্থীর তথ্য পাওয়া যায়নি / Not Found', 'error');
        });
    }

    if (phoneInput) {
        // Clear any unwanted browser autofill (e.g. saved emails) on page load and continuously
        const clearAutofill = () => {
            if (phoneInput.value && (!/^\d+$/.test(phoneInput.value) || phoneInput.value.includes('@'))) {
                phoneInput.value = '';
            }
        };

        clearAutofill();
        [50, 150, 300, 600, 1000, 2000].forEach(t => setTimeout(clearAutofill, t));

        phoneInput.addEventListener('input', function () {
            if (this.value.includes('@') || this.value.includes('.')) {
                this.value = '';
                return;
            }
            const clean = this.value.replace(/[^0-9]/g, '');
            if (this.value !== clean) {
                this.value = clean;
            }
            if (clean.length === 11) {
                checkAndLoadResident(clean);
            }
        });

        phoneInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const clean = this.value.replace(/[^0-9]/g, '');
                if (clean.length === 11) {
                    checkAndLoadResident(clean);
                } else {
                    showFeedback('১১ ডিজিটের নম্বর লিখুন (যেমন: 017xxxxxxxx)', 'error');
                }
            }
        });
    }

    // ---- Nav links & ScrollSpy ----
    const navLinks = document.querySelectorAll('.nsm-nav-center .nsm-menu-item');
    const isHomePage = window.location.pathname === '/' || window.location.pathname.endsWith('index.php');

    navLinks.forEach(link => {
        link.addEventListener('click', function () {
            const href = this.getAttribute('href');
            if (href.includes('#') && isHomePage) {
                navLinks.forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            }
        });
    });

    if (isHomePage) {
        const sections = [
            { id: 'portfolioBg', linkClass: 'item-gallery' },
            { id: 'advantagesSection', linkClass: 'item-advantages' }
        ];

        function syncScrollSpy() {
            let currentActive = null;
            const scrollPos = window.scrollY + 150;

            if (window.scrollY < 200) {
                navLinks.forEach(l => l.classList.remove('active'));
                document.querySelector('.item-home')?.classList.add('active');
                return;
            }

            sections.forEach(sec => {
                const el = document.getElementById(sec.id);
                if (el) {
                    const top = el.offsetTop;
                    const height = el.offsetHeight;
                    if (scrollPos >= top && scrollPos < top + height) {
                        currentActive = sec.linkClass;
                    }
                }
            });

            if (currentActive) {
                navLinks.forEach(l => l.classList.remove('active'));
                document.querySelector('.' + currentActive)?.classList.add('active');
            }
        }

        window.addEventListener('scroll', syncScrollSpy);
        window.addEventListener('load', syncScrollSpy);
    }
});
</script>