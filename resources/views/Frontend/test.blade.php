@extends('Frontend.layouts.app')
@section('title', 'Home')
@section('content')
  
<section>
  <div class="slider" id="slider">
    <div class="slides" id="slides">
      <div class="slide"><img src="Slider/149886715.jpg" alt=""></div>
      <div class="slide"><img src="Slider/387397942.jpg" alt=""></div>
      <div class="slide"><img src="Slider/premium_photo.jfif" alt=""></div>
      <div class="slide"><img src="Slider/373ae656.avif" alt=""></div>
    </div>
    <button class="s-nav s-prev" type="button">‹</button>
    <button class="s-nav s-next" type="button">›</button>
  </div>
</section>

<section class="hero-search-wrap">
  <div class="container">
    <div class="hero-search-bar">
      <div class="hs-field" id="checkinBox">
        <span class="hs-dot"></span>
        <div class="hs-field-inner">
          <small>Check in</small>
          <input type="text" id="checkin" class="date-input" placeholder="Select date" readonly>
        </div>
      </div>
      <div class="hs-field" id="checkoutBox">
        <div class="hs-field-inner">
          <small>Check out</small>
          <input type="text" id="checkout" class="date-input" placeholder="Select date" readonly>
        </div>
      </div>
      <div class="hs-right">
        <button class="hs-btn" type="button">SEARCH</button>
      </div>
    </div>
  </div>
</section>

<section class="pkg-section">
  <div class="container">
    <h2 class="pkg-title">Popular Packages</h2>
    <!-- GRID -->
    <div class="row g-4" id="packagesGrid">
      <div class="col-lg-4 col-md-6 pkg-item">
        <div class="pkg-card">
          <div class="pkg-media">
            <img src="packageimage/download (2).jfif" alt="Nepal">
            <div class="pkg-meta">
              <span><i class="bi bi-people"></i> 2 People</span>
            </div>
          </div>
          <div class="pkg-body">
            <h3 class="pkg-name">First Floor</h3>
            <div class="d-flex justify-content-between align-items-center">
            <div class="text-start">
              <div class="pkg-price">BDT ROOM 6</div>
              <div class="pkg-sub">Per person</div>
            </div>
            <div>
               <a href="{{ url('booking-now') }}" class="btn btn-primary btn-sm">
                   Details
              </a>
            </div>
          </div>
          </div>
        </div>
      </div>

      <div class="col-lg-4 col-md-6 pkg-item">
        <div class="pkg-card">
          <div class="pkg-media">
            <img src="packageimage/download (3).jfif" alt="Indonesia">
            <div class="pkg-meta">
              <span><i class="bi bi-people"></i> 2 People</span>
            </div>
          </div>
          <div class="pkg-body ">
            <h3 class="pkg-name">Second Floor</h3>
          <div class="d-flex justify-content-between align-items-center">
            <div class="text-start">
              <div class="pkg-price">BDT ROOM 7</div>
              <div class="pkg-sub">Per person</div>
            </div>
            <div>
              <a href="{{ url('booking-now') }}" class="btn btn-primary btn-sm">
                Details
              </a>
            </div>
          </div>
          </div>
        </div>
      </div>

      <div class="col-lg-4 col-md-6 pkg-item">
        <div class="pkg-card">
          <div class="pkg-media">
            <img src="packageimage/download (4).jfif" alt="Malaysia">
            <div class="pkg-meta">
              <span><i class="bi bi-people"></i> 2-8 People</span>
            </div>
          </div>
          <div class="pkg-body">
            <h3 class="pkg-name">Third Floor</h3>
                <div class="d-flex justify-content-between align-items-center">
              <div class="text-start">
                <div class="pkg-price">BDT ROOM 6</div>
                <div class="pkg-sub">Per person</div>
              </div>
              <div>
                 <a href="{{ url('booking-now') }}" class="btn btn-primary btn-sm">
                   Details
              </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-4 col-md-6 pkg-item">
        <div class="pkg-card">
          <div class="pkg-media">
            <img src="packageimage/frist.jpg" alt="Singapore">
            <div class="pkg-meta">
       
              <span><i class="bi bi-people"></i> 2 People</span>
            </div>
          </div>
          <div class="pkg-body">
            <h3 class="pkg-name">Fourth Floor</h3>
             <div class="d-flex justify-content-between align-items-center">
              <div class="text-start">
                <div class="pkg-price">BDT ROOM 8</div>
                <div class="pkg-sub">Per person</div>
              </div>
              <div>
                 <a href="{{ url('booking-now') }}" class="btn btn-primary btn-sm">
                   Details
              </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-4 col-md-6 pkg-item">
        <div class="pkg-card">
          <div class="pkg-media">
            <img src="packageimage/hotel room with beachfront view.jpg.webp" alt="Thailand">
            <div class="pkg-meta">
        
              <span><i class="bi bi-people"></i>Fifth Floor</span>
            </div>
          </div>
          <div class="pkg-body">
            <h3 class="pkg-name">Fifth Floor</h3>
               <div class="d-flex justify-content-between align-items-center">
              <div class="text-start">
                <div class="pkg-price">BDT ROOM 6</div>
                <div class="pkg-sub">Per person</div>
              </div>
              <div>
                 <a href="{{ url('booking-now') }}" class="btn btn-primary btn-sm">
                   Details
              </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-4 col-md-6 pkg-item">
        <div class="pkg-card">
          <div class="pkg-media">
            <img src="packageimage/images (14).jfif" alt="Dubai">
            <div class="pkg-meta">
             
              <span><i class="bi bi-people"></i> 2 People</span>
            </div>
          </div>
          <div class="pkg-body">
            <h3 class="pkg-name">Sixth Floor</h3>
               <div class="d-flex justify-content-between align-items-center">
              <div class="text-start">
                <div class="pkg-price">BDT ROOM 7</div>
                <div class="pkg-sub">Per person</div>
              </div>
              <div>
                 <a href="{{ url('booking-now') }}" class="btn btn-primary btn-sm">
                   Details
              </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ✅ Add more cards below (7th, 8th, 9th...) -->
      <div class="col-lg-4 col-md-6 pkg-item">
        <div class="pkg-card">
          <div class="pkg-media">
            <img src="packageimage/images (14).jfif" alt="Vietnam">
            <div class="pkg-meta">
             
              <span><i class="bi bi-people"></i> 2 People</span>
            </div>
          </div>
          <div class="pkg-body">
            <h3 class="pkg-name">Seventh Floor</h3>
               <div class="d-flex justify-content-between align-items-center">
              <div class="text-start">
                <div class="pkg-price">BDT ROOM 3</div>
                <div class="pkg-sub">Per person</div>
              </div>
              <div>
                 <a href="{{ url('booking-now') }}" class="btn btn-primary btn-sm">
                   Details
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-4 col-md-6 pkg-item">
        <div class="pkg-card">
          <div class="pkg-media">
            <img src="packageimage/interior-hotel-bedroom-260nw-2496648857.webp" alt="Maldives">
            <div class="pkg-meta">
             
              <span><i class="bi bi-people"></i> 2 People</span>
            </div>
          </div>
          <div class="pkg-body">
            <h3 class="pkg-name">Eighth Floor</h3>
               <div class="d-flex justify-content-between align-items-center">
              <div class="text-start">
                <div class="pkg-price">BDT ROOM 7</div>
                <div class="pkg-sub">Per person</div>
              </div>
              <div>
                 <a href="{{ url('booking-now') }}" class="btn btn-primary btn-sm">
                   Details
              </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="pkg-pagination-wrap">
      <div class="pkg-pagination" id="pkgPagination"></div>
    </div>

  </div>
</section>



<section class="about-wrap">
  <div class="container">
    <div class="about-grid">
      <div class="about-content">
        <h2 class="about-title">Relax in Our Place</h2>
        <h5 class="about-subtitle">Welcome to Hotel Sadman<br>Rangpur</h5>
        <p>This beautiful and unique five-star standard property is ideally locatedin the central business area of Rangpur.</p>
        <p>Hotel Sadman Rangpur offers luxuriously furnished guest rooms,multi-cuisine restaurants, a Café Lounge, Bar, the city’s most beautiful Rooftop Restaurant with terrace, world-class Banquet & Conference Hall,Gym, Beauty Salon, and Laundry services.
        </p>
        <p>Through innovative programs and thoughtful amenities, we provide guests with exceptional services to enhance their stay and ensure they leavefeeling refreshed, relaxed, and satisfied.
        </p>
      </div>
      <div class="about-media">
        <div class="img-back">
          <img src="Relax_image/royal-suite-rangpur-gallery-1-700x466.jpg" alt="Room" width="100%">
        </div>
        <div class="img-front">
          <img src="Relax_image/m-5449713.webp" alt="Hotel">
        </div>
      </div>
    </div>
  </div>
</section>


<section class="portfolio-bg" id="portfolioBg">
  <div class="portfolio-overlay">
    <div class="container text-center">
      <h2 class="pf-title">Our Bed</h2>
      <p class="pf-sub">
        Check out our recent logo vectorization services. We strive to provide you
        with excellent work at all times so that you can feel confident to rely on
        us for any future projects.
      </p>
      <div class="pf-stage">
        <div class="pf-track" id="pfTrack">
          <div class="pf-card"><img src="ouerimagebed/download (2).jfif" alt="Portfolio 1"></div>
          <div class="pf-card"><img src="ouerimagebed/download (3).jfif" alt="Portfolio 2"></div>
          <div class="pf-card"><img src="ouerimagebed/download (4).jfif" alt="Portfolio 3"></div>
          <div class="pf-card"><img src="ouerimagebed/frist.jpg" alt="Portfolio 4"></div>
          <div class="pf-card"><img src="ouerimagebed/hotel room with beachfront view.jpg.webp" alt="Portfolio 5"></div>
          <div class="pf-card"><img src="ouerimagebed/images (14).jfif" alt="Portfolio 6"></div>
          <div class="pf-card"><img src="ouerimagebed/images (15).jfif" alt="Portfolio 7"></div>
          <div class="pf-card"><img src="ouerimagebed/kid.webp" alt="Portfolio 8"></div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
