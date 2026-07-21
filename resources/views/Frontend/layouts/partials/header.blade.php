<section>

<!-- ===================== HEADER START ===================== -->
<div class="nsm-header-wrap" id="nsmHeaderWrap">

  <!-- Announcement / Marquee bar -->
  @php
    $latestNotice = \App\Models\Frontend\Notice::latest()->first();
    $noticeText = $latestNotice ? $latestNotice->notice :'';
  @endphp
  <div class="nsm-marquee-bar">
    <marquee behavior="scroll" direction="left" scrollamount="6"
             class="nsm-marquee-text" style="cursor:pointer;"
             onmouseover="this.stop();" onmouseout="this.start();">
      {{ $noticeText }}
    </marquee>
  </div>

  <!-- Main header row -->
  <header class="nsm-main-header">
    <div class="nsm-header-inner">

      <!-- LEFT : desktop nav -->
      <div class="nsm-nav-left d-none d-lg-flex">
        <a href="{{ url('/') }}" class="nsm-menu-item item-home {{ request()->is('/') ? 'active' : '' }}">
          <i class="bi bi-house-door-fill"></i>
          <span>Home</span>
        </a>
        <a href="{{ url('/#portfolioBg') }}" class="nsm-menu-item item-gallery">
          <i class="bi bi-images"></i>
          <span>Gallery</span>
        </a>
        <a href="{{ url('/#advantagesSection') }}" class="nsm-menu-item item-advantages">
          <i class="bi bi-shield-check"></i>
          <span>Advantages</span>
        </a>
        <a href="{{ url('booking-now') }}" class="nsm-menu-item item-booking {{ request()->is('booking-now') ? 'active' : '' }}">
          <i class="bi bi-calendar-check"></i>
          <span>Booking Now</span>
        </a>
        <a href="{{ url('guest-support') }}" class="nsm-menu-item item-help {{ request()->is('guest-support') ? 'active' : '' }}">
          <i class="bi bi-headset"></i>
          <span>Help Desk</span>
        </a>
      </div>

      <!-- LEFT : mobile hamburger -->
      <div class="nsm-nav-left d-flex d-lg-none">
        <button class="btn p-0 border-0 shadow-none" type="button"
                data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu">
          <i class="bi bi-list fs-2 text-dark"></i>
        </button>
      </div>

      <!-- CENTER : logo -->
      <a href="{{ url('/') }}" class="nsm-logo-center text-decoration-none">
        <img src="{{ asset('logo/logoimage (2).png') }}" alt="Logo">
      </a>

      <!-- RIGHT : login / phone -->
      <div class="nsm-nav-right">
        <div class="lang-switcher-container d-none d-lg-inline-flex">
          <select id="customLangSelector" class="form-select form-select-sm">
            <option value="bn">বাংলা</option>
            <option value="en">English</option>
          </select>
        </div>
        @guest
        <a href="#" class="nsm-right-item" data-bs-toggle="modal" data-bs-target="#loginModal">
          <i class="bi bi-lock"></i>
          <span class="d-none d-lg-inline">Login</span>
        </a>
        @else
        <a href="{{ route('dashboard') }}" class="nsm-right-item position-relative" style="display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 38px; border-radius: 50%; background: #e6f1fb; border: 1.5px solid #033364; margin-right: 6px;">
          <i class="bi bi-person-fill" style="font-size: 18px; color: #033364;"></i>
          <span class="position-absolute bg-success border border-white rounded-circle" style="width: 10px; height: 10px; bottom: 0; right: 0;"></span>
        </a>
        @endguest
            <a href="tel:+8801713558866" class="right-item">
        <i class="bi bi-telephone"></i>
        <span class="nav-link-min phone-text">+8801713-555221</span>
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
  <div class="offcanvas-header border-bottom py-3">
    <div class="d-flex align-items-center gap-2">
      <div style="width:32px;height:32px;border-radius:8px;background:#033364;display:flex;align-items:center;justify-content:center;">
        <i class="bi bi-building text-white" style="font-size:17px;"></i>
      </div>
      <h5 class="offcanvas-title mb-0" id="mobileMenuLabel">Menu</h5>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  {{-- Body --}}
  <div class="offcanvas-body p-3">
    <!-- Mobile Language Selector -->
    <div class="mb-4 px-2">
      <label class="form-label small text-muted fw-bold mb-2" style="letter-spacing: .06em; text-transform: uppercase;">Language / ভাষা</label>
      <select id="mobileLangSelector" class="form-select" style="border: 1.5px solid #033364; color: #033364; font-weight: 700; border-radius: 10px; padding: 8px 12px; font-size: 14px;">
        <option value="bn">বাংলা</option>
        <option value="en">English</option>
      </select>
    </div>
    <p style="font-size:11px;font-weight:500;color:#9ca3af;letter-spacing:.06em;text-transform:uppercase;margin:8px 0 6px 8px;">Navigate</p>

    <a href="{{ url('/') }}" class="off-item d-flex align-items-center gap-3 p-2 rounded-3 text-decoration-none mb-1" style="color:inherit;">
      <span style="width:34px;height:34px;border-radius:8px;background:#e2ebf4;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="bi bi-house-door-fill" style="font-size:17px;color:#033364;"></i>
      </span>
      <div class="flex-grow-1">
        <p class="mb-0" style="font-size:14px;font-weight:500;">Home</p>
      </div>
      <i class="bi bi-chevron-right text-muted" style="font-size:14px;"></i>
    </a>

    <a href="{{ url('/#portfolioBg') }}" class="off-item d-flex align-items-center gap-3 p-2 rounded-3 text-decoration-none mb-1" style="color:inherit;">
      <span style="width:34px;height:34px;border-radius:8px;background:#e6f1fb;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="bi bi-images" style="font-size:17px;color:#185fa5;"></i>
      </span>
      <div class="flex-grow-1">
        <p class="mb-0" style="font-size:14px;font-weight:500;">Gallery</p>
      </div>
      <i class="bi bi-chevron-right text-muted" style="font-size:14px;"></i>
    </a>

    <a href="{{ url('/#advantagesSection') }}" class="off-item d-flex align-items-center gap-3 p-2 rounded-3 text-decoration-none mb-1" style="color:inherit;">
      <span style="width:34px;height:34px;border-radius:8px;background:#eaf3de;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="bi bi-shield-check" style="font-size:17px;color:#3b6d11;"></i>
      </span>
      <div class="flex-grow-1">
        <p class="mb-0" style="font-size:14px;font-weight:500;">Advantages</p>
      </div>
      <i class="bi bi-chevron-right text-muted" style="font-size:14px;"></i>
    </a>

    <a href="{{ url('booking-now') }}" class="off-item d-flex align-items-center gap-3 p-2 rounded-3 text-decoration-none mb-1" style="color:inherit;">
      <span style="width:34px;height:34px;border-radius:8px;background:#faeeda;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="bi bi-calendar-check" style="font-size:17px;color:#854f0b;"></i>
      </span>
      <div class="flex-grow-1">
        <p class="mb-0" style="font-size:14px;font-weight:500;">Booking now</p>
      </div>
      <i class="bi bi-chevron-right text-muted" style="font-size:14px;"></i>
    </a>

    <a href="{{ url('guest-support') }}" class="off-item d-flex align-items-center gap-3 p-2 rounded-3 text-decoration-none mb-3" style="color:inherit;">
      <span style="width:34px;height:34px;border-radius:8px;background:#fbeaf0;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="bi bi-headset" style="font-size:17px;color:#993556;"></i>
      </span>
      <div class="flex-grow-1">
        <p class="mb-0" style="font-size:14px;font-weight:500;">Help line</p>
      </div>
      <i class="bi bi-chevron-right text-muted" style="font-size:14px;"></i>
    </a>

    @auth
    <a href="{{ route('dashboard') }}" class="off-item d-flex align-items-center gap-3 p-2 rounded-3 text-decoration-none mb-1" style="color:inherit;">
      <span style="width:34px;height:34px;border-radius:8px;background:#dbeafe;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="bi bi-speedometer2" style="font-size:17px;color:#1d4ed8;"></i>
      </span>
      <div class="flex-grow-1">
        <p class="mb-0" style="font-size:14px;font-weight:500;">Dashboard</p>
      </div>
      <i class="bi bi-chevron-right text-muted" style="font-size:14px;"></i>
    </a>
    <a href="{{ route('logout') }}" class="off-item d-flex align-items-center gap-3 p-2 rounded-3 text-decoration-none mb-3" style="color:inherit;"
       onclick="event.preventDefault(); document.getElementById('logout-form-mobile').submit();">
      <span style="width:34px;height:34px;border-radius:8px;background:#fee2e2;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="bi bi-box-arrow-right" style="font-size:17px;color:#dc2626;"></i>
      </span>
      <div class="flex-grow-1">
        <p class="mb-0" style="font-size:14px;font-weight:500;color:#dc2626;">Logout</p>
      </div>
    </a>
    <form id="logout-form-mobile" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
    @endauth

    {{-- Contact card --}}
    <div class="p-3 rounded-3 bg-light border">
      <p class="mb-1" style="font-size:13px;font-weight:500;">Need assistance?</p>
      <p class="mb-2 text-muted" style="font-size:12px;">Our team is available around the clock.</p>
      <a href="{{ url('guest-support') }}" style="font-size:13px;font-weight:500;color:#033364;text-decoration:none;">
        <i class="bi bi-telephone me-1"></i> Contact us
      </a>
    </div>

  </div>
</div>
<!-- ===================== Mobile Offcanvas Menu End ===================== -->

<style>
/* ===================== HEADER CSS (fully responsive) ===================== */
:root{
    --nsm-header-height: 96px; /* JS will overwrite this at runtime; fallback only */
}

.nsm-header-wrap *{ box-sizing: border-box; }
.nsm-header-wrap{
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 1030;
    display: flex;
    flex-direction: column;   /* marquee on top, main header below */
    margin: 0;
    padding: 0;
}

/* ---- Marquee bar ---- */
.nsm-marquee-bar{
    width: 100%;
    background: #f8f9fa;
    padding: 4px 0;
    margin: 0;
    overflow: hidden;
}
.nsm-marquee-text{
    display: block;
    width: 100%;
    color: #212529;
    font-size: 14px;
}

/* ---- Main header ---- */
.nsm-main-header{
    width: 100%;
    background: #ffffff;
    box-shadow: 0 1px 6px rgba(0,0,0,.08);
    margin: 0;
    padding: 0;
}

.nsm-header-inner{
    max-width: 1320px;
    margin: 0 auto;
    padding: 8px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

/* ---- Left nav (desktop) ---- */
.nsm-nav-left{
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 1 1 0;
    min-width: 0;
}
.nsm-menu-item{
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border: 1.5px solid #033364;
    border-radius: 8px;
    text-decoration: none;
    font-size: 15px;
    font-weight: 700;
    white-space: nowrap;
    flex-shrink: 0;
    transition: all 0.2s ease-in-out;
    background-color: #ffffff;
    color: #033364;
}

.nsm-menu-item i {
    color: #033364 !important;
    font-size: 16px;
    transition: color 0.2s ease-in-out;
}

.nsm-menu-item:hover,
.nsm-menu-item.active {
    background-color: #033364 !important;
    border-color: #033364 !important;
    color: #ffffff !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(3, 51, 100, 0.2);
}

.nsm-menu-item:hover i,
.nsm-menu-item.active i {
    color: #ffffff !important;
}

/* ---- Center logo ---- */
.nsm-logo-center{
    flex: 0 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
}
.nsm-logo-center img{
    height: 90px;
    width: auto;
    object-fit: contain;
    transition: height .2s ease;
    position:relative;
    left: 125px;

}

/* ---- Right side ---- */
.nsm-nav-right{
    flex: 1 1 0;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 16px;
    min-width: 0;
}
.nsm-right-item{
    display: flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    color: #222;
    font-size: 13px;
    white-space: nowrap;
}
.nsm-right-item i{
    font-size: 19px;
}

.nsm-right-item span{
    font-size: 16px;
}



/* ---- Push page content below fixed header (JS-driven, fallback here) ---- */
body{
    padding-top: var(--nsm-header-height);
}

/* =============== RESPONSIVE BREAKPOINTS =============== */

/* Tablet */



/* Mobile: icon-only feel, no crowding */
@media (max-width: 991.98px){
  .mobile-right-wrap{
    gap: 6px;
  }
  .mobile-right-wrap .right-item{
    padding: 6px 8px;
  }
  .nsm-logo-center img {
    height: 59px;
    width: auto;
    object-fit: contain;
    transition: height .2s ease;
    position:relative;
    left:-88px;
  
  .mobile-right-wrap .right-item[data-bs-target="#loginModal"] .nav-link-min{
    display: none;
  }
}
.hompagesolution
 {
    position: relative;
    top: 5px;
}

}

/* Phone number text — icon-only start kore 480px thekei, 360px porjonto na wait kore */
@media (max-width: 480px){
  .mobile-right-wrap .phone-text{
    display: none;
  }
  .mobile-right-wrap .right-item{
    padding: 6px 7px;
  }
    .nsm-logo-center img {
    height: 59px;
    width: auto;
    object-fit: contain;
    transition: height .2s ease;
     position:relative;
    left:-88px;
  

  .mobile-right-wrap .right-item[data-bs-target="#loginModal"] .nav-link-min{
    display: none;
  }
}
.hompagesolution
 {
    position: relative;
    top: 5px;
}

}

/* Extra small: sob spacing aro tight */
@media (max-width: 360px){
  .mobile-right-wrap{ gap: 4px; }
  .mobile-right-wrap .right-item i{ font-size: 16px; }

    .nsm-logo-center img {
    height: 59px;
    width: auto;
    object-fit: contain;
    transition: height .2s ease;
 position:relative;
    left:-88px;

  /* LOGIN — text hide, icon thakbe */
  .mobile-right-wrap .right-item[data-bs-target="#loginModal"] .nav-link-min{
    display: none;
  }
}
.hompagesolution

 {
    position: relative;
    top: 5px;
}

}
/* Hide logo on intermediate screens (Nest Hub etc) to prevent overlapping */
@media (min-width: 992px) and (max-width: 1200px) {
  .nsm-logo-center {
    display: none !important;
  }
}
</style>


<script>
document.querySelectorAll('.off-item').forEach(item => {
    item.addEventListener('click', function () {
        const offcanvasElement = document.getElementById('mobileMenu');
        const bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement);
        if (bsOffcanvas) {
            bsOffcanvas.hide();
        }
    });
});

(function () {
    function adjustHeaderOffset() {
        const headerWrap = document.getElementById('nsmHeaderWrap');
        if (!headerWrap) return;
        const height = headerWrap.offsetHeight;
        document.documentElement.style.setProperty('--nsm-header-height', height + 'px');
    }

    window.addEventListener('load', adjustHeaderOffset);
    window.addEventListener('resize', adjustHeaderOffset);

    // marquee/font load hobar por o height change hote pare, tai ektu delay diye abar check
    setTimeout(adjustHeaderOffset, 300);
})();

document.addEventListener('DOMContentLoaded', function () {
    const navLinks = document.querySelectorAll('.nsm-nav-left .nsm-menu-item');
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