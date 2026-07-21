@extends('Frontend.layouts.app')
@section('title', 'Home')
@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<!-- এই লাইনটি না থাকলে আইকন আসবে না -->
<div class="hompagesolution">

<section class="hero-wrap" style="background-color: #033364; padding: 60px 20px; text-align: center; color: white;">
  <div class="container">
    
    <div style="margin-bottom: 30px;">
        <h1 style="font-size: 32px; margin-bottom: 5px; color: #fff;">সোহাশোভা ছাত্রী নিবাস</h1>
        <p style="font-size: 16px; color: #d1d1d1;">নিরাপদ ও আরামদায়ক আবাসন | রংপুরের প্রাণকেন্দ্রে ছাত্রীদের জন্য একটি আদর্শ আবাস</p>
    </div>

    <form action="{{ route('booking-now.bookingpage') }}" method="GET">
      <div style=" padding: 25px; border-radius: 15px; display: inline-flex; gap: 15px; align-items: center; justify-content: center; flex-wrap: wrap; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
        <div class="hs-field" style="background: #f9f9f9; padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
          <div class="hs-field-inner">
            <small style="color: green; display: block; font-size: 12px;"></small>
            <input type="text" id="checkin" name="checkin" class="date-input" placeholder="চেক-ইন" value="{{ request('checkin') }}" readonly required style="border: none; background: transparent; outline: none; width: 120px;">
          </div>
        </div>

        <div class="hs-field" style="background: #f9f9f9; padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
          <div class="hs-field-inner">
            <small style="color: green; display: block; font-size: 12px;"></small>
            <input type="text" id="checkout" name="checkout" class="date-input" placeholder="চেক-আউট" value="{{ request('checkout') }}" readonly required style="border: none; background: transparent; outline: none; width: 120px;">
          </div>
        </div>

        <div class="hs-right">
          <button type="submit" class="hero-check-btn" style="padding: 14px 28px; background-color: #f59e0b; border: 2px solid #f59e0b; color: #02172c; font-weight: 800; cursor: pointer; border-radius: 8px; font-size: 15px; transition: all 0.25s ease-in-out;">উপলব্ধতা যাচাই করুন</button>
        </div>

      </div>
    </form>
  </div>
</section>

<section class="pkg-section" id="floorSection">
  <div class="container">
    <h2 class="pkg-title">Popular Packages</h2>
    <!-- GRID -->
    <div class="row g-4" id="packagesGrid">
      @foreach($floors as $floor)
      <div class="col-lg-4 col-md-6 pkg-item">
        <div class="pkg-card">
          <div class="pkg-media">
            <img src="{{ asset('floor_image/'.$floor->image) }}" alt="{{ $floor->name }}" class="img-fluid">
          </div>
          <div class="pkg-body">
            <h3 class="pkg-name">{{ $floor->name }}</h3>
            <div class="d-flex justify-content-between align-items-center">
            <div class="text-start">
              <div class="pkg-price">BDT ROOM {{ $floor->rooms_count}}</div>
              <div class="pkg-sub">Per person</div>
            </div>
            <div>
             <a href="{{ url('floor/'.$floor->id.'/rooms') }}" class="btn btn-primary btn-sm">
               View Details
              </a>

            </div>
          </div>
          </div>
        </div>
      </div>
      @endforeach
    </div>
    <div class="pkg-pagination-wrap">
      <div class="pkg-pagination" id="pkgPagination"></div>
    </div>
  </div>
</section>

@php
    $overview = $residenceOverviews->first();
@endphp
@if($overview)
<section class="about-wrap">
  <div class="container">
    <div class="about-grid">
      <div class="about-content">
        <h2 class="about-title">সোহাশোভা ছাত্রী নিবাস | মেধার বিকাশ | সমৃদ্ধ ভবিষ্যৎ</h2>
        <h5 class="about-subtitle">{{ $overview->title }}</h5>
        @foreach(preg_split("/\r\n|\n|\r/", $overview->description) as $line)
            @if(trim($line) !== '')
                <p>{{ trim($line) }}</p>
            @endif
        @endforeach
      </div>
      <div class="about-media">
        <div class="img-back">
          <img
            src="{{ asset('residence_back_image/' . $overview->img_back) }}"
            alt="{{ $overview->title }}"
            width="100%">
        </div>
        <div class="img-front">
          <img
            src="{{ asset('residence_front_image/' . $overview->img_front) }}"
            alt="{{ $overview->title }}">
        </div>
      </div>
    </div>
  </div>
</section>
@endif


{{-- Stats Counter Section --}}
<section class="stats-section">
  <div class="container">
    <div class="stats-grid">
      <div class="stat-item">
        <h3 class="stat-number" data-target="{{ $bookinguest }}">{{ $bookinguest }}</h3>
        <p class="stat-label"># of Happy Guests</p>
      </div>
      <div class="stat-item">
        <h3 class="stat-number" data-target="{{ $roomcount }}">{{ $roomcount }}</h3>
        <p class="stat-label"># of Rooms</p>
      </div>
      <div class="stat-item">
        <h3 class="stat-number" data-target="{{ $staffscount }}">{{ $staffscount }}</h3>
        <p class="stat-label"># of Staffs</p>
      </div>
      <div class="stat-item">
        <h3 class="stat-number" data-target="1">1</h3>
        <p class="stat-label"># of Destination</p>
      </div>
    </div>
  </div>
</section>


<section class="gallery-section" id="portfolioBg">
  <div class="container text-center">
    <div class="gallery-header text-center">
      <h2 class="gallery-title" style="font-weight: 800; font-size: 32px; color: #033364; margin-bottom: 10px;">Our Gallery</h2>
      <p class="gallery-sub" style="font-size: 15px; color: #6b7280; max-width: 700px; margin: 0 auto 30px;">
        A glimpse of the well-furnished, comfortable, and modern rooms at Sohashova Chhatri Nibas.
      </p>
    </div>

    <!-- Gallery Grid -->
    <div class="gallery-grid" id="galleryGrid">
      @foreach($galleries as $index => $gallery)
      <div class="gallery-item {{ $index >= 3 ? 'd-none additional-gallery-item' : '' }}">
        <div class="gallery-img-wrap">
          <img src="{{ asset('gallery_image/'.$gallery->image) }}" alt="Room {{ $index + 1 }}">
          <div class="gallery-overlay">
            <div class="gallery-zoom">
              <i class="bi bi-zoom-in"></i>
            </div>
          </div>
        </div>
      </div>
      @endforeach
    </div>

    <!-- View More Button -->
    @if(count($galleries) > 3)
    <div class="mt-4">
      <button id="viewMoreBtn" class="gallery-view-more-btn" data-expanded="false">View More</button>
    </div>
    @endif

  </div>
</section>

<section class="advantages-section" id="advantagesSection">
  <div class="container">
      <div class="services-header">
      <h2 class="services-title">Advantages</h2>
    </div>
    <div class="advantages-grid">
      
      <!-- 1. Security -->
      <div class="advantage-item">
        <div class="icon-wrapper">
          <i class="bi bi-shield-check" style="font-size: 40px; color: #033364;"></i>
        </div>
        <h3 class="advantage-name">24/7 Security</h3>
        <p class="advantage-desc">24/7 CCTV and round-the-clock security system.</p>
      </div>

      <!-- 2. Free Wifi -->
      <div class="advantage-item">
        <div class="icon-wrapper">
          <i class="bi bi-wifi" style="font-size: 40px; color: #033364;"></i>
        </div>
        <h3 class="advantage-name">High-Speed Wifi</h3>
        <p class="advantage-desc">High-speed internet for seamless connectivity.</p>
      </div>

      <!-- 3. Healthy Food -->
      <div class="advantage-item">
        <div class="icon-wrapper">
          <i class="bi bi-cup-hot" style="font-size: 40px; color: #033364;"></i>
        </div>
        <h3 class="advantage-name">Healthy Food</h3>
        <p class="advantage-desc">Nutritious and hygienic meal arrangements.</p>
      </div>

      <!-- 4. Study Zone -->
      <div class="advantage-item">
        <div class="icon-wrapper">
          <i class="bi bi-book" style="font-size: 40px; color: #033364;"></i>
        </div>
        <h3 class="advantage-name">Study Zone</h3>
        <p class="advantage-desc">Peaceful environment for focused study.</p>
      </div>

      <!-- 5. Laundry -->
     <div class="advantage-item">
    <div class="icon-wrapper">
      <!-- এটি ব্যবহার করুন, এটি কাজ করার কথা -->
<i class="bi bi-basket" style="font-size: 40px; color: #033364;"></i>
    </div>
    <h3 class="advantage-name">Laundry Service</h3>
    <p class="advantage-desc">Convenient laundry facilities for cleanliness.</p>
</div>

    </div>
  </div>
</section>
{{-- Hotel Services Section - portfolio-bg section এর পরে add করো --}}




</div>
@endsection

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>

document.addEventListener('DOMContentLoaded', function () {
    flatpickr("#checkin", {
        dateFormat: "Y-m-d",
        allowInput: false
    });
    flatpickr("#checkout", {
        dateFormat: "Y-m-d",
        allowInput: false
    });
    document.querySelectorAll('.hs-field').forEach(field => {
        field.addEventListener('click', function () {
            const input = this.querySelector('.date-input');
            if (input && input._flatpickr) {
                input._flatpickr.open();
            }
        });
    });
});

//relax palce

let aboutAnimated = false;
window.addEventListener('scroll', function () {
  const section = document.querySelector('.about-wrap');
  if (!section) return;
  const rect = section.getBoundingClientRect();
  if (rect.top < window.innerHeight - 100) {
    const content = document.querySelector('.about-content');
    if (content) content.classList.add('show');
    const backImg = document.querySelector('.img-back img');
    if (backImg) backImg.classList.add('show');
    setTimeout(function () {
      const frontImg = document.querySelector('.img-front');
      if (frontImg) frontImg.classList.add('show');
    }, 600);
  } else {
    const content = document.querySelector('.about-content');
    if (content) content.classList.remove('show');
    const backImg = document.querySelector('.img-back img');
    if (backImg) backImg.classList.remove('show');
    const frontImg = document.querySelector('.img-front');
    if (frontImg) frontImg.classList.remove('show');
  }
});

//staff count //
document.addEventListener("DOMContentLoaded", function () {
  const statsObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        document.querySelectorAll('.stat-number').forEach(function(counter) {
          const target = parseInt(counter.getAttribute('data-target'));
          let current = 0;
          counter.innerText = '0';
          const duration = 2000;
          const step = Math.ceil(target / (duration / 30));
          const timer = setInterval(function() {
            current = current + step;
            if (current >= target) {
              counter.innerText = target;
              clearInterval(timer);
            } else {
              counter.innerText = current;
            }
          }, 30);
        });
      } else {
        document.querySelectorAll('.stat-number').forEach(function(counter) {
          counter.innerText = counter.getAttribute('data-target');
        });
      }
    });
  }, { threshold: 0.1 });

  const statsSection = document.querySelector('.stats-section');
  if (statsSection) statsObserver.observe(statsSection);
});




//Explore Our Hotel Services //
document.addEventListener('DOMContentLoaded', function () {
  const serviceObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('animate-in');
      } else {
        entry.target.classList.remove('animate-in');
      }
    });
  }, { threshold: 0.1 });

  document.querySelectorAll('.service-item, .services-title').forEach((el, i) => {
    el.style.transitionDelay = (i * 0.12) + 's';
    serviceObserver.observe(el);
  });
});


</script>


<style type="text/css">
/*relax palce */
.about-content {
  opacity: 0;
  transform: translateX(-100px);
  transition: opacity 0.8s ease, transform 0.8s ease;
}

.about-content.show {
  opacity: 1;
  transform: translateX(0);
}

/* gallery secton*/
.img-back img {
  opacity: 0;
  transform: scale(0.8);
  transition: opacity 0.8s ease, transform 0.8s ease;
}

.img-front {
  opacity: 0;
  transform: translateY(40px);
  transition: opacity 0.8s ease 0.6s, transform 0.8s ease 0.6s;
}

.img-back img.show {
  opacity: 1;
  transform: scale(1);
}

.img-front.show {
  opacity: 1;
  transform: translateY(0);
}





.hs-field{
    cursor:pointer;
}


.services-title {
  opacity: 0;
  transform: translateY(-30px);
  transition: opacity 0.7s ease, transform 0.7s ease;
}
.services-title.animate-in {
  opacity: 1;
  transform: translateY(0);
}

.service-item {
  opacity: 0;
  transform: translateX(-80px) translateY(-40px);
  transition: opacity 0.6s ease, transform 0.6s ease;
}
.service-item.animate-in {
  opacity: 1;
  transform: translateX(0) translateY(0);
}

.hero-check-btn:hover {
  background-color: #ffffff !important;
  border-color: #ffffff !important;
  color: #033364 !important;
  box-shadow: 0 4px 15px rgba(245, 158, 11, 0.35);
  transform: translateY(-2px);
}

/* --- Scroll Reveal Animations --- */
.js-reveal-fade-up {
  opacity: 0;
  transform: translateY(40px);
  transition: opacity 0.8s cubic-bezier(0.25, 1, 0.5, 1), transform 0.8s cubic-bezier(0.25, 1, 0.5, 1);
}
.js-reveal-fade-up.active-reveal {
  opacity: 1;
  transform: translateY(0);
}

.js-reveal-fade-left {
  opacity: 0;
  transform: translateX(-40px);
  transition: opacity 0.8s cubic-bezier(0.25, 1, 0.5, 1), transform 0.8s cubic-bezier(0.25, 1, 0.5, 1);
}
.js-reveal-fade-left.active-reveal {
  opacity: 1;
  transform: translateX(0);
}

.js-reveal-scale-up {
  opacity: 0;
  transform: scale(0.95);
  transition: opacity 0.8s cubic-bezier(0.25, 1, 0.5, 1), transform 0.8s cubic-bezier(0.25, 1, 0.5, 1);
}
.js-reveal-scale-up.active-reveal {
  opacity: 1;
  transform: scale(1);
}
</style>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const viewMoreBtn = document.getElementById('viewMoreBtn');
    if (viewMoreBtn) {
      viewMoreBtn.addEventListener('click', function (e) {
        e.preventDefault();
        const additionalItems = document.querySelectorAll('.additional-gallery-item');
        const isExpanded = this.getAttribute('data-expanded') === 'true';
        
        if (isExpanded) {
          additionalItems.forEach(item => item.classList.add('d-none'));
          this.innerText = 'View More';
          this.setAttribute('data-expanded', 'false');
          // Scroll back to gallery grid smoothly
          document.getElementById('portfolioBg').scrollIntoView({ behavior: 'smooth' });
        } else {
          additionalItems.forEach(item => item.classList.remove('d-none'));
          this.innerText = 'View Less';
          this.setAttribute('data-expanded', 'true');
        }
      });
    }
    // Progressive Enhancement Scroll Reveal:
    // Only apply reveal animation classes via JavaScript so that if JavaScript has an error or is blocked,
    // the content remains perfectly visible!
    const pkgSection = document.getElementById('floorSection');
    const statsSection = document.querySelector('.stats-section');
    const gallerySection = document.getElementById('portfolioBg');

    if (pkgSection) pkgSection.classList.add('js-reveal-fade-up');
    if (statsSection) statsSection.classList.add('js-reveal-fade-left');
    if (gallerySection) gallerySection.classList.add('js-reveal-scale-up');

    const revealTargets = document.querySelectorAll('.js-reveal-fade-up, .js-reveal-fade-left, .js-reveal-scale-up');
    const revealObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('active-reveal');
        } else {
          entry.target.classList.remove('active-reveal');
        }
      });
    }, {
      threshold: 0.02
    });

    revealTargets.forEach(target => revealObserver.observe(target));
  });
</script>
@endpush