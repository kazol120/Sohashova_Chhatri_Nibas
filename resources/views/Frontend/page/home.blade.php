@extends('Frontend.layouts.app')
@section('title', 'Home')
@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<!-- এই লাইনটি না থাকলে আইকন আসবে না -->
<div class="hompagesolution">

<!-- ═══════════════ HERO SECTION (MODERN BANNER & SLIDER) ═══════════════ -->
<style>
  .hero-modern-section {
    background: radial-gradient(circle at 10% 15%, #bbf0ff 0%, #dff4fd 25%, #f4fafe 55%, #eef7fc 100%);
    padding: 70px 0 80px 0;
    position: relative;
    overflow: hidden;
  }
  .hero-title-main {
    font-size: 3rem;
    font-weight: 800;
    color: #033364;
    margin-bottom: 12px;
    letter-spacing: -0.5px;
    line-height: 1.2;
  }
  .hero-subtitle-main {
    font-size: 1.05rem;
    color: #64748b;
    margin-bottom: 28px;
    line-height: 1.6;
  }
  .hero-feat-row {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 18px;
  }
  .hero-feat-icon {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #e0f2fe;
    color: #0284c7;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
    box-shadow: 0 4px 10px rgba(2, 132, 199, 0.12);
  }
  .hero-feat-text {
    font-size: 15.5px;
    font-weight: 600;
    color: #1e293b;
    line-height: 1.4;
  }
  .hero-book-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #033364;
    color: #ffffff !important;
    font-weight: 700;
    font-size: 16px;
    padding: 13px 38px;
    border-radius: 12px;
    border: none;
    box-shadow: 0 8px 22px rgba(3, 51, 100, 0.28);
    text-decoration: none !important;
    transition: all 0.3s ease;
    margin-top: 10px;
  }
  .hero-book-btn:hover {
    background: #022244;
    box-shadow: 0 12px 28px rgba(3, 51, 100, 0.38);
    transform: translateY(-2px);
    color: #ffffff !important;
  }
  .hero-slider-card {
    border-radius: 28px;
    border: 8px solid #ffffff;
    box-shadow: 0 20px 45px rgba(3, 51, 100, 0.12);
    overflow: hidden;
    position: relative;
    background: #033364;
    max-width: 560px;
    height: 420px;
    margin: 0 auto;
  }
  .hero-slider-card .carousel,
  .hero-slider-card .carousel-inner,
  .hero-slider-card .carousel-item {
    height: 100% !important;
    width: 100% !important;
    overflow: hidden;
  }
  .hero-slider-img {
    width: 100%;
    height: 100% !important;
    min-height: 100%;
    object-fit: cover;
    object-position: center;
    display: block;
  }
  @media (max-width: 768px) {
    .hero-title-main {
      font-size: 2.2rem;
    }
    .hero-slider-card {
      height: 290px;
      border-radius: 20px;
      border-width: 5px;
    }
  }
  .hero-carousel-control {
    width: 42px;
    height: 42px;
    background: rgba(3, 51, 100, 0.55);
    backdrop-filter: blur(6px);
    border-radius: 50%;
    top: 50%;
    transform: translateY(-50%);
    opacity: 0.85;
    transition: all 0.3s ease;
    border: 2px solid rgba(255, 255, 255, 0.8);
  }
  .hero-carousel-control:hover {
    background: rgba(3, 51, 100, 0.9);
    opacity: 1;
    transform: translateY(-50%) scale(1.08);
  }
  .hero-carousel-control.carousel-control-prev {
    left: 15px;
  }
  .hero-carousel-control.carousel-control-next {
    right: 15px;
  }
</style>

<section class="hero-modern-section notranslate" translate="no">
  <div class="container">
    <div class="row align-items-center g-5">
      <!-- Left Info Column -->
      <div class="col-lg-6 col-md-12">
        <h1 class="hero-title-main notranslate site-name-text" data-lang-bn="টি এস এস ভিলা" data-lang-en="TSS Villa">
          টি এস এস ভিলা
        </h1>
        <p class="hero-subtitle-main">
          নিরাপদ ও আরামদায়ক আবাসন | রংপুরের প্রাণকেন্দ্রে শিক্ষার্থীদের জন্য একটি আদর্শ নিবাস
        </p>

        <!-- 4 Highlights -->
        <div class="hero-feature-list mb-4">
          <div class="hero-feat-row">
            <div class="hero-feat-icon">
              <i class="bi bi-shield-check"></i>
            </div>
            <span class="hero-feat-text">নিরাপদ ও শান্ত পরিবেশ (সিসিটিভি দ্বারা নিয়ন্ত্রিত)</span>
          </div>

          <div class="hero-feat-row">
            <div class="hero-feat-icon">
              <i class="bi bi-wifi"></i>
            </div>
            <span class="hero-feat-text">হাই-স্পিড ওয়াই-ফাই এবং বিদ্যুৎ সুবিধা</span>
          </div>

          <div class="hero-feat-row">
            <div class="hero-feat-icon">
              <i class="bi bi-cup-hot"></i>
            </div>
            <span class="hero-feat-text">নিয়মিত সুস্বাদু ও স্বাস্থ্যকর খাবার</span>
          </div>

          <div class="hero-feat-row">
            <div class="hero-feat-icon">
              <i class="bi bi-stars"></i>
            </div>
            <span class="hero-feat-text">পরিষ্কার-পরিচ্ছন্ন রুম এবং ২৪ ঘণ্টা ক্লিনার সাপোর্ট</span>
          </div>
        </div>

        <div>
          <a href="{{ route('booking-now.bookingpage') }}" class="btn hero-book-btn">
            বুকিং করুন।
          </a>
        </div>
      </div>

      <!-- Right Slider Column -->
      <div class="col-lg-6 col-md-12 text-center">
        <div class="hero-slider-card">
          <div id="heroBannerCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="4000">
            <div class="carousel-inner">
              @if(isset($sliders) && count($sliders) > 0)
                @foreach($sliders as $idx => $slider)
                  <div class="carousel-item {{ $idx === 0 ? 'active' : '' }}">
                    <img src="{{ asset('slider_image/' . $slider->image) }}" class="hero-slider-img" alt="{{ $slider->title ?? 'TSS Villa' }}">
                  </div>
                @endforeach
              @else
                <div class="carousel-item active">
                  <img src="{{ asset('slider_image/1786877533_6a81965d71d51.png') }}" class="hero-slider-img" alt="TSS Villa">
                </div>
              @endif
            </div>

            @if(isset($sliders) && count($sliders) > 1)
              <button class="carousel-control-prev hero-carousel-control" type="button" data-bs-target="#heroBannerCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
              </button>
              <button class="carousel-control-next hero-carousel-control" type="button" data-bs-target="#heroBannerCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
              </button>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</section>



<style>
  .pro-floor-card {
    background: #ffffff;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 10px 30px rgba(3, 51, 100, 0.05);
    transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
  }
  .pro-floor-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(3, 51, 100, 0.12);
    border-color: #cbd5e1;
  }
  .pro-floor-media {
    position: relative;
    height: 220px;
    overflow: hidden;
    background: #033364;
  }
  .pro-floor-media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
  }
  .pro-floor-card:hover .pro-floor-media img {
    transform: scale(1.08);
  }
  .pro-floor-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(3, 51, 100, 0.5) 0%, transparent 60%);
  }
  .pro-status-badge {
    position: absolute;
    top: 15px;
    left: 15px;
    backdrop-filter: blur(8px);
    font-size: 12px;
    font-weight: 700;
    padding: 6px 14px;
    border-radius: 30px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  }
  .pro-floor-body {
    padding: 24px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
  }
  .pro-chip {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #475569;
    font-size: 12px;
    font-weight: 600;
    padding: 5px 12px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
  }
  .pro-view-btn {
    background: linear-gradient(135deg, #033364 0%, #064e8a 100%);
    color: #ffffff !important;
    border-radius: 12px;
    font-weight: 700;
    font-size: 0.9rem;
    padding: 10px 20px;
    border: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 14px rgba(3, 51, 100, 0.2);
    text-decoration: none !important;
  }
  .pro-view-btn:hover {
    background: linear-gradient(135deg, #022244 0%, #033364 100%);
    box-shadow: 0 6px 20px rgba(3, 51, 100, 0.3);
    transform: translateX(2px);
  }
</style>

<!-- ═══════════════ POPULAR PACKAGES (FLOORS SECTION) ═══════════════ -->
<section class="pkg-section" id="floorSection" style="background: #f8fafc !important; padding: 85px 0;">
  <div class="container">
    <div class="text-center mb-5">
      <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-2 rounded-pill mb-2" style="background: #e0f2fe; color: #033364 !important; font-size: 13px;">
        <i class="bi bi-building me-1"></i> আমাদের ফ্লোরসমূহ
      </span>
      <h2 class="pkg-title" style="font-weight: 800; font-size: 2.2rem; color: #033364; margin-bottom: 12px;">Popular Packages</h2>
      <p class="text-muted" style="font-size: 1rem; max-width: 650px; margin: 0 auto;">আমাদের প্রতিটি ফ্লোরে রয়েছে সুসজ্জিত ও নিরাপদ কক্ষসমূহ। আপনার সুবিধাজনক ফ্লোরটি নির্বাচন করুন।</p>
    </div>

    <!-- GRID -->
    <div class="row g-4" id="packagesGrid">
      @foreach($floors as $floor)
      <div class="col-lg-4 col-md-6 pkg-item">
        <div class="pro-floor-card">
          <!-- Card Image Header -->
          <div class="pro-floor-media">
            <img src="{{ asset('floor_image/'.$floor->image) }}" alt="{{ $floor->name }}" class="img-fluid">
            <div class="pro-floor-overlay"></div>
            <span class="pro-status-badge text-white" style="background: rgba(16, 185, 129, 0.9);">
              <i class="bi bi-door-open-fill me-1"></i> {{ $floor->available_seats_count ?? $floor->rooms->count() }} Seats Available
            </span>
          </div>

          <!-- Card Content Body -->
          <div class="pro-floor-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h3 class="fw-extrabold text-dark mb-0" style="color: #033364 !important; font-size: 1.35rem; font-weight: 800;">{{ $floor->name }}</h3>
            </div>
            
            <p class="text-muted small mb-3" style="line-height: 1.5; font-size: 0.85rem;">
              সুরক্ষিত ও মনোরম পরিবেশে নিরবচ্ছিন্ন স্টাডি ও আবাসনের সুবিধা।
            </p>

            <!-- Feature Chips -->
            <div class="d-flex flex-wrap gap-2 mb-4">
              <span class="pro-chip"><i class="bi bi-wifi text-primary"></i> ওয়াইফাই</span>
              <span class="pro-chip"><i class="bi bi-shield-check text-success"></i> সিসিটিভি</span>
              <span class="pro-chip"><i class="bi bi-lightning-charge text-warning"></i> 24/7 বিদ্যুৎ</span>
            </div>

            <!-- Footer Action Row -->
            <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-auto">
              <div class="d-flex align-items-center gap-1.5 text-muted small" style="font-weight: 600;">
                <i class="bi bi-check-circle-fill text-success fs-6"></i>
                <span>সুসজ্জিত ইউনিট</span>
              </div>
              <a href="{{ url('floor/'.$floor->id.'/rooms') }}" class="pro-view-btn d-inline-flex align-items-center gap-1.5">
                <span>View Details</span>
                <i class="bi bi-arrow-right"></i>
              </a>
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

<!-- ═══════════════ নিয়মাবলী (RULES & REGULATIONS SECTION) ═══════════════ -->
<style>
  .rules-notice-section {
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%) !important;
    padding: 85px 0;
  }
  .rules-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
  }
  @media (max-width: 991px) {
    .rules-grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }
  @media (max-width: 640px) {
    .rules-grid {
      grid-template-columns: 1fr;
    }
  }
  .rule-card {
    background: #ffffff;
    border-radius: 18px;
    padding: 22px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    border: 1px solid #f1f5f9;
    transition: all 0.3s ease;
    min-height: 90px;
  }
  .rule-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(3, 51, 100, 0.08);
  }
  .rule-icon-box {
    width: 50px;
    height: 50px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
  }
  .rule-icon-box i {
    font-size: 24px;
    line-height: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }
  .rule-text {
    font-size: 14px;
    font-weight: 600;
    color: #1e293b;
    line-height: 1.5;
  }
</style>

<section class="rules-notice-section notranslate" translate="no">
  <div class="container">
    <!-- Title Badge -->
    <div class="text-center mb-5">
      <span class="d-inline-flex align-items-center gap-2 px-5 py-2.5 text-white fw-bold fs-3 shadow-sm notranslate" translate="no" style="background: linear-gradient(135deg, #033364 0%, #064e8a 100%); border-radius: 50px; letter-spacing: 0.5px; border: 3px solid #ffffff; min-width: 240px; box-shadow: 0 10px 25px rgba(3,51,100,0.25) !important;">
        <i class="bi bi-journal-check me-1"></i> নিয়মাবলী
      </span>
      <p class="mt-3 text-muted" style="font-size: 1rem; max-width: 600px; margin: 0 auto;">ছাত্রী নিবাসের শৃঙ্খলা ও নিরাপত্তা বজায় রাখতে অনুগ্রহ করে নিম্নোক্ত নিয়মসমূহ অনুসরণ করুন</p>
    </div>
    
    <!-- Rules Grid -->
    <div class="rules-grid notranslate" translate="no">
      @if(isset($hostelRules) && count($hostelRules) > 0)
        @php
          $ruleThemes = [
            ['bg' => 'rgba(16, 185, 129, 0.12)', 'color' => '#059669', 'icon' => 'bi bi-calendar2-check-fill'],
            ['bg' => 'rgba(245, 158, 11, 0.12)', 'color' => '#d97706', 'icon' => 'bi bi-piggy-bank-fill'],
            ['bg' => 'rgba(139, 92, 246, 0.12)', 'color' => '#7c3aed', 'icon' => 'bi bi-sunset-fill'],
            ['bg' => 'rgba(14, 165, 233, 0.12)', 'color' => '#0284c7', 'icon' => 'bi bi-arrow-left-right'],
            ['bg' => 'rgba(6, 182, 212, 0.12)', 'color' => '#0891b2', 'icon' => 'bi bi-lightning-charge-fill'],
            ['bg' => 'rgba(99, 102, 241, 0.12)', 'color' => '#4f46e5', 'icon' => 'bi bi-cash-stack'],
            ['bg' => 'rgba(20, 184, 166, 0.12)', 'color' => '#0d9488', 'icon' => 'bi bi-door-open-fill'],
            ['bg' => 'rgba(244, 63, 94, 0.12)', 'color' => '#e11d48', 'icon' => 'bi bi-exclamation-triangle-fill'],
            ['bg' => 'rgba(3, 51, 100, 0.12)', 'color' => '#033364', 'icon' => 'bi bi-shield-exclamation'],
          ];
        @endphp
        @foreach($hostelRules as $idx => $rule)
          @php $theme = $ruleThemes[$idx % count($ruleThemes)]; @endphp
          <div class="rule-card">
            <div class="rule-icon-box" style="background: {{ $theme['bg'] }}; color: {{ $theme['color'] }};">
              <i class="{{ $rule->icon ?: $theme['icon'] }}"></i>
            </div>
            <div class="rule-text">{!! $rule->description !!}</div>
          </div>
        @endforeach
      @else
        <div class="rule-card">
          <div class="rule-icon-box" style="background: rgba(16, 185, 129, 0.12); color: #059669;"><i class="bi bi-calendar2-check-fill"></i></div>
          <div class="rule-text">মেসের ভাড়া <strong>০৭ তারিখের</strong> মধ্যে পরিশোধ করতে হবে।</div>
        </div>
        
        <div class="rule-card">
          <div class="rule-icon-box" style="background: rgba(245, 158, 11, 0.12); color: #d97706;"><i class="bi bi-piggy-bank-fill"></i></div>
          <div class="rule-text">অগ্রিম দুই মাসের টাকা জামানত দিতে হবে । মেস ছাড়লে <strong>০২ মাস পূর্বেই</strong> মেস কর্তৃপক্ষকে জানাতে হবে। অন্যথায় দুই মাসের ভাড়া দিয়ে মেস ছাড়তে হবে।</div>
        </div>

        <div class="rule-card">
          <div class="rule-icon-box" style="background: rgba(139, 92, 246, 0.12); color: #7c3aed;"><i class="bi bi-sunset-fill"></i></div>
          <div class="rule-text"><strong>মাগরিবের আযানের পর</strong> মেসের বাহিরে থাকলে মেস অভিভাবককে জানিয়ে দিতে হবে।</div>
        </div>

        <div class="rule-card">
          <div class="rule-icon-box" style="background: rgba(14, 165, 233, 0.12); color: #0284c7;"><i class="bi bi-arrow-left-right"></i></div>
          <div class="rule-text">রুম চেঞ্জ করতে চাইলে <strong>৫০০ টাকা</strong> প্রদান করতে হবে।</div>
        </div>

        <div class="rule-card">
          <div class="rule-icon-box" style="background: rgba(6, 182, 212, 0.12); color: #0891b2;"><i class="bi bi-lightning-charge-fill"></i></div>
          <div class="rule-text">ওয়াইফাই, ক্লিনার, বিদ্যুৎ - ভাড়ার সাথে ইনক্লুড (সংযুক্ত) থাকবে ।</div>
        </div>

        <div class="rule-card">
          <div class="rule-icon-box" style="background: rgba(99, 102, 241, 0.12); color: #4f46e5;"><i class="bi bi-cash-stack"></i></div>
          <div class="rule-text">উন্নয়ন ফি ৩০০০/- (এককালীন) ।</div>
        </div>

        <div class="rule-card">
          <div class="rule-icon-box" style="background: rgba(20, 184, 166, 0.12); color: #0d9488;"><i class="bi bi-door-open-fill"></i></div>
          <div class="rule-text">একক সিট ভাড়া ৩০০০/- টাকা, দুই সিট ভাড়া ২৫০০/- টাকা ।</div>
        </div>

        <div class="rule-card">
          <div class="rule-icon-box" style="background: rgba(244, 63, 94, 0.12); color: #e11d48;"><i class="bi bi-exclamation-triangle-fill"></i></div>
          <div class="rule-text">সকল কিছু পরিবর্তন, পরিবর্ধন, যে কোন মুহূর্তে কর্তৃপক্ষ সিদ্ধান্ত নিতে পারিবে ।</div>
        </div>

        <div class="rule-card">
          <div class="rule-icon-box" style="background: rgba(3, 51, 100, 0.12); color: #033364;"><i class="bi bi-shield-exclamation"></i></div>
          <div class="rule-text">মেসের নিয়ম-কানুন মেনে চলতে হবে। কারো বিরুদ্ধে কোনো অভিযোগ আসলে এবং তা প্রমাণিত হলে সিট বাতিলসহ যেকোনো ব্যবস্থা নেয়ার অধিকার কর্তৃপক্ষ রাখে।</div>
        </div>
      @endif
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
        <h2 class="about-title notranslate site-name-text" data-lang-bn="টি এস এস ভিলা | মেধার বিকাশ | সমৃদ্ধ ভবিষ্যৎ" data-lang-en="TSS Villa | Intellectual Growth | Bright Future">টি এস এস ভিলা | মেধার বিকাশ | সমৃদ্ধ ভবিষ্যৎ</h2>
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



<section class="gallery-section" id="portfolioBg">
  <div class="container text-center">
    <div class="gallery-header text-center">
      <h2 class="gallery-title" style="font-weight: 800; font-size: 32px; color: #033364; margin-bottom: 10px;">Our Gallery</h2>
      <p class="gallery-sub" style="font-size: 15px; color: #6b7280; max-width: 700px; margin: 0 auto 30px;">
        <span class="notranslate site-name-text" data-lang-bn="টি এস এস ভিলার" data-lang-en="TSS Villa's">টি এস এস ভিলার</span> সুসজ্জিত, আরামদায়ক ও আধুনিক কক্ষগুলোর একটি ঝলক।
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
          <i class="bi bi-basket" style="font-size: 40px; color: #033364;"></i>
        </div>
        <h3 class="advantage-name">Laundry Service</h3>
        <p class="advantage-desc">Convenient laundry facilities for cleanliness.</p>
      </div>

    </div>
  </div>
</section>

<!-- ═══════════════ HOW IT WORKS (BOOKING STEPS) ═══════════════ -->
<style>
  .steps-section {
    background: #ffffff;
    padding: 85px 0;
  }
  .step-card {
    background: #f8fafc;
    border-radius: 20px;
    padding: 35px 25px;
    border: 1px solid #e2e8f0;
    text-align: center;
    position: relative;
    transition: all 0.35s ease;
    height: 100%;
  }
  .step-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(3, 51, 100, 0.08);
    border-color: #cbd5e1;
    background: #ffffff;
  }
  .step-badge {
    position: absolute;
    top: -15px;
    left: 50%;
    transform: translateX(-50%);
    background: linear-gradient(135deg, #033364 0%, #064e8a 100%);
    color: #ffffff;
    font-weight: 800;
    font-size: 14px;
    padding: 6px 18px;
    border-radius: 30px;
    box-shadow: 0 4px 12px rgba(3, 51, 100, 0.25);
    border: 2px solid #ffffff;
  }
  .step-icon-wrap {
    width: 70px;
    height: 70px;
    margin: 15px auto 20px;
    border-radius: 50%;
    background: #e0f2fe;
    color: #0284c7;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
    transition: all 0.3s ease;
  }
  .step-card:hover .step-icon-wrap {
    background: #033364;
    color: #ffffff;
    transform: scale(1.1);
  }
  .step-title {
    font-size: 1.25rem;
    font-weight: 800;
    color: #033364;
    margin-bottom: 12px;
  }
  .step-desc {
    color: #64748b;
    font-size: 14.5px;
    line-height: 1.6;
    margin-bottom: 0;
  }
</style>

<section class="steps-section">
  <div class="container">
    <div class="text-center mb-5">
      <span class="badge px-3 py-2 rounded-pill mb-2 fw-bold" style="background: #e0f2fe; color: #033364; font-size: 13px;">
        <i class="bi bi-diagram-3 me-1"></i> সহজ ও দ্রুত বুকিং
      </span>
      <h2 style="font-weight: 800; font-size: 2.2rem; color: #033364; margin-bottom: 12px;">কীভাবে একটি সিট বুক করবেন?</h2>
      <p class="text-muted" style="font-size: 1rem; max-width: 600px; margin: 0 auto;">মাত্র ৩টি সহজ ধাপে আপনার কাঙ্ক্ষিত সিট নিশ্চিত করুন</p>
    </div>

    <div class="row g-4">
      <div class="col-lg-4 col-md-6">
        <div class="step-card">
          <div class="step-badge">ধাপ ০১</div>
          <div class="step-icon-wrap">
            <i class="bi bi-search"></i>
          </div>
          <h3 class="step-title">রুম ও সিট পছন্দ করুন</h3>
          <p class="step-desc">আমাদের বিভিন্ন ফ্লোরের সুসজ্জিত সিঙ্গেল বা ডাবল সিটের রুমগুলোর বিস্তারিত ও সুযোগ-সুবিধা দেখে পছন্দ করুন।</p>
        </div>
      </div>

      <div class="col-lg-4 col-md-6">
        <div class="step-card">
          <div class="step-badge">ধাপ ০২</div>
          <div class="step-icon-wrap">
            <i class="bi bi-pencil-square"></i>
          </div>
          <h3 class="step-title">অনলাইনে বুকিং দিন</h3>
          <p class="step-desc">খুব সহজেই 'বুকিং করুন' বাটনে চাপ দিয়ে আপনার নাম ও যোগাযোগের তথ্য প্রদান করে রিকোয়েস্ট জমা দিন।</p>
        </div>
      </div>

      <div class="col-lg-4 col-md-6">
        <div class="step-card">
          <div class="step-badge">ধাপ ০৩</div>
          <div class="step-icon-wrap">
            <i class="bi bi-patch-check-fill"></i>
          </div>
          <h3 class="step-title">সিট নিশ্চিত করুন</h3>
          <p class="step-desc">কর্তৃপক্ষ আপনার আবেদন যাচাই করে বুকিং নিশ্চিত করবে এবং আপনি নির্দিষ্ট তারিখে সুন্দর পরিবেশে উঠতে পারবেন।</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════ TESTIMONIALS (STUDENT & GUARDIAN REVIEWS) ═══════════════ -->
<style>
  .reviews-section {
    background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
    padding: 85px 0;
  }
  .review-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 30px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 10px 30px rgba(3, 51, 100, 0.04);
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
  }
  .review-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 40px rgba(3, 51, 100, 0.09);
    border-color: #cbd5e1;
  }
  .review-stars {
    color: #f59e0b;
    font-size: 16px;
    margin-bottom: 15px;
  }
  .review-text {
    font-size: 14.5px;
    color: #334155;
    line-height: 1.7;
    margin-bottom: 20px;
    flex-grow: 1;
    font-style: italic;
  }
  .reviewer-info {
    display: flex;
    align-items: center;
    gap: 14px;
    padding-top: 15px;
    border-top: 1px solid #f1f5f9;
  }
  .reviewer-name {
    font-weight: 800;
    font-size: 16px;
    color: #033364;
    margin-bottom: 3px;
    display: flex;
    align-items: center;
    gap: 6px;
  }
  .reviewer-role {
    font-size: 13px;
    color: #64748b;
    margin-bottom: 0;
  }
</style>

<section class="reviews-section">
  <div class="container">
    <div class="text-center mb-5">
      <span class="badge px-3 py-2 rounded-pill mb-2 fw-bold" style="background: #fef3c7; color: #d97706; font-size: 13px;">
        <i class="bi bi-star-fill me-1"></i> সন্তুষ্টি ও আস্থা
      </span>
      <h2 style="font-weight: 800; font-size: 2.2rem; color: #033364; margin-bottom: 12px;">শিক্ষার্থীদের মতামত</h2>
      <p class="text-muted" style="font-size: 1rem; max-width: 600px; margin: 0 auto;">আমাদের মেস পরিবারের শিক্ষার্থীদের মূল্যবান অভিজ্ঞতা</p>
    </div>

    <div class="row g-4">
      @php
        $presetReviews = [
          "পড়াশোনার জন্য এখানকার পরিবেশ অসাধারণ। হাই-স্পিড ওয়াই-ফাই এবং শান্ত পরিবেশ থাকায় মনোযোগ দিয়ে পড়াশোনা করা যায়। খাবারের মানও খুব ভালো।",
          "নিরাপত্তা ও শৃঙ্খলার দিক থেকে মেসটি অনেক চমৎকার। সার্বক্ষণিক সিসিটিভি নজরদারি এবং পরিচ্ছন্ন পরিবেশ থাকায় সম্পূর্ণ নিশ্চিন্ত থাকা যায়।",
          "অনলাইন সিস্টেমের মাধ্যমে মিল অন-অফ এবং যাবতীয় হিসাব দেখার ব্যবস্থা অনেক ভালো লেগেছে। যেকোনো প্রয়োজনে দ্রুত আন্তরিক সাপোর্ট পাওয়া যায়।"
        ];
      @endphp

      @if(isset($activeStudents) && count($activeStudents) > 0)
        @foreach($activeStudents as $sIdx => $student)
          @php
            $reviewQuote = $presetReviews[$sIdx % count($presetReviews)];
            $collegeName = $student->institution_name ?: ($student->education_class ?: 'শিক্ষার্থী');
          @endphp
          <div class="col-lg-4 col-md-6">
            <div class="review-card">
              <div class="review-stars">
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i>
              </div>
              <p class="review-text">"{{ $reviewQuote }}"</p>
              <div class="reviewer-info">
                <div>
                  <div class="reviewer-name">
                    {{ ucwords($student->full_name) }}
                    <i class="bi bi-patch-check-fill text-primary fs-6" title="ভেরিফাইড শিক্ষার্থী"></i>
                  </div>
                  <div class="reviewer-role">{{ ucwords($collegeName) }} (শিক্ষার্থী)</div>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      @else
        <div class="col-lg-4 col-md-6">
          <div class="review-card">
            <div class="review-stars">
              <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
            </div>
            <p class="review-text">"পড়াশোনার জন্য এখানকার পরিবেশ অসাধারণ। হাই-স্পিড ওয়াই-ফাই এবং শান্ত পরিবেশ থাকায় মনোযোগ দিয়ে পড়াশোনা করা যায়।"</p>
            <div class="reviewer-info">
              <div>
                <div class="reviewer-name">তানজিলা আক্তার <i class="bi bi-patch-check-fill text-primary fs-6"></i></div>
                <div class="reviewer-role">বেগম রোকেয়া বিশ্ববিদ্যালয় (শিক্ষার্থী)</div>
              </div>
            </div>
          </div>
        </div>
      @endif
    </div>
  </div>
</section>

<!-- ═══════════════ FAQ SECTION ═══════════════ -->
<style>
  .faq-section {
    background: #ffffff;
    padding: 85px 0;
  }
  .faq-accordion .accordion-item {
    border: 1px solid #e2e8f0;
    border-radius: 14px !important;
    margin-bottom: 14px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.02);
  }
  .faq-accordion .accordion-button {
    font-weight: 700;
    font-size: 16px;
    color: #033364;
    background: #ffffff;
    padding: 18px 22px;
  }
  .faq-accordion .accordion-button:not(.collapsed) {
    background: #f0fdf4;
    color: #033364;
    box-shadow: none;
    border-bottom: 1px solid #e2e8f0;
  }
  .faq-accordion .accordion-body {
    padding: 20px 22px;
    color: #475569;
    font-size: 15px;
    line-height: 1.7;
    background: #fafafa;
  }
</style>

<section class="faq-section">
  <div class="container" style="max-width: 900px;">
    <div class="text-center mb-5">
      <span class="badge px-3 py-2 rounded-pill mb-2 fw-bold" style="background: #ede9fe; color: #7c3aed; font-size: 13px;">
        <i class="bi bi-question-circle me-1"></i> সাধারণ প্রশ্নোত্তর
      </span>
      <h2 style="font-weight: 800; font-size: 2.2rem; color: #033364; margin-bottom: 12px;">সচরাচর জিজ্ঞাসিত প্রশ্নাবলী (FAQ)</h2>
      <p class="text-muted" style="font-size: 1rem; margin: 0 auto;">হোস্টেলে ওঠা ও পরিচালনা সংক্রান্ত প্রয়োজনীয় তথ্যাবলী</p>
    </div>

    <div class="accordion faq-accordion" id="hostelFaqAccordion">
      <div class="accordion-item">
        <h2 class="accordion-header" id="faqHeading1">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse1" aria-expanded="true" aria-controls="faqCollapse1">
            <i class="bi bi-patch-question me-2 text-primary"></i> সিট বুকিং করতে কী কী তথ্যের প্রয়োজন হয়?
          </button>
        </h2>
        <div id="faqCollapse1" class="accordion-collapse collapse show" aria-labelledby="faqHeading1" data-bs-parent="#hostelFaqAccordion">
          <div class="accordion-body">
            সিট বুকিং করার জন্য আপনার নাম, মোবাইল নম্বর, শিক্ষাপ্রতিষ্ঠানের নাম এবং একটি জাতীয় পরিচয়পত্র (NID) বা জন্ম সনদের কপি প্রয়োজন হবে।
          </div>
        </div>
      </div>

      <div class="accordion-item">
        <h2 class="accordion-header" id="faqHeading2">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse2" aria-expanded="false" aria-controls="faqCollapse2">
            <i class="bi bi-patch-question me-2 text-primary"></i> মেসের মিল সিস্টেম কীভাবে পরিচালিত হয়?
          </button>
        </h2>
        <div id="faqCollapse2" class="accordion-collapse collapse" aria-labelledby="faqHeading2" data-bs-parent="#hostelFaqAccordion">
          <div class="accordion-body">
            প্রতিদিন স্বাস্থ্যসম্মত ৩ বেলা মিল পরিবেশন করা হয়। ইউজার নিজস্ব ড্যাশবোর্ড থেকে সকাল ১০:০০ টা হতে রাত ০৮:০০ টার মধ্যে প্রয়োজন অনুযায়ী মিল বন্ধ বা চালু করার অনুরোধ পাঠাতে পারেন।
          </div>
        </div>
      </div>

      <div class="accordion-item">
        <h2 class="accordion-header" id="faqHeading3">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse3" aria-expanded="false" aria-controls="faqCollapse3">
            <i class="bi bi-patch-question me-2 text-primary"></i> মেসে ওঠার জন্য কি অগ্রিম জামানত দিতে হয়?
          </button>
        </h2>
        <div id="faqCollapse3" class="accordion-collapse collapse" aria-labelledby="faqHeading3" data-bs-parent="#hostelFaqAccordion">
          <div class="accordion-body">
            হ্যাঁ, মেসের নিয়ম অনুযায়ী অগ্রিম জামানত ও এককালীন উন্নয়ন ফি জমা দিতে হয়। মেস ছাড়ার কমপক্ষে ২ মাস পূর্বে কর্তৃপক্ষকে অবহিত করতে হয়।
          </div>
        </div>
      </div>

      <div class="accordion-item">
        <h2 class="accordion-header" id="faqHeading4">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse4" aria-expanded="false" aria-controls="faqCollapse4">
            <i class="bi bi-patch-question me-2 text-primary"></i> নিরাপত্তা ব্যবস্থা কেমন এবং কখন প্রবেশ করতে হয়?
          </button>
        </h2>
        <div id="faqCollapse4" class="accordion-collapse collapse" aria-labelledby="faqHeading4" data-bs-parent="#hostelFaqAccordion">
          <div class="accordion-body">
            সম্পূর্ণ ভবনটি সার্বক্ষণিক সিসিটিভি ক্যামেরা দ্বারা নিয়ন্ত্রিত। মাগরিবের আযানের পূর্বে মেসে প্রবেশ করতে হয়, অন্যথায় বিশেষ প্রয়োজনে অভিভাবকের অনুমতি প্রয়োজন হয়।
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════ CTA BANNER SECTION ═══════════════ -->
<style>
  .cta-modern-banner {
    background: linear-gradient(135deg, #033364 0%, #064e8a 100%);
    border-radius: 26px;
    padding: 50px 40px;
    color: #ffffff;
    box-shadow: 0 20px 50px rgba(3, 51, 100, 0.25);
    position: relative;
    overflow: hidden;
  }
  .cta-modern-banner::before {
    content: "";
    position: absolute;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
    top: -100px;
    right: -100px;
  }
</style>

<section class="py-5" style="background: #f8fafc;">
  <div class="container">
    <div class="cta-modern-banner text-center text-lg-start">
      <div class="row align-items-center g-4">
        <div class="col-lg-8">
          <span class="badge bg-white text-dark fw-bold px-3 py-1.5 rounded-pill mb-3" style="font-size: 13px;">
            <i class="bi bi-headset me-1 text-primary"></i> সার্বক্ষণিক সহায়তা ও তথ্য
          </span>
          <h2 class="text-white fw-extrabold mb-2" style="font-weight: 800; font-size: 2.2rem;">আপনার সিট বুকিং বা যেকোনো তথ্যের জন্য যোগাযোগ করুন</h2>
          <p class="text-white-50 mb-0 fs-6">আমাদের প্রতিনিধি আপনাকে যেকোনো তথ্য দিতে এবং সিট বুকিং করতে সার্বক্ষণিক সহযোগিতা করবে।</p>
        </div>
        <div class="col-lg-4 text-lg-end text-center">
          <div class="d-flex flex-wrap gap-2 justify-content-lg-end justify-content-center">
            <a href="tel:{{ $webSetting['phone'] ?? '01977270920' }}" class="btn btn-warning fw-bold px-4 py-2.5 rounded-pill shadow-sm" style="font-size: 15px;">
              <i class="bi bi-telephone-fill me-1"></i> কল করুন
            </a>
            <a href="{{ route('booking-now.bookingpage') }}" class="btn btn-outline-light fw-bold px-4 py-2.5 rounded-pill shadow-sm" style="font-size: 15px;">
              <i class="bi bi-door-open me-1"></i> সিট বুক করুন
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

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