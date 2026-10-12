

{{-- ===== FOOTER ===== --}}
<footer class="footer-section">
  <div class="container">
    <div class="footer-grid">

      {{-- Col 1: Brand --}}
      <div class="footer-brand">
        <div class="footer-logo">
          <span class="footer-logo-main notranslate site-name-text" data-lang-bn="টি এস এস ভিলা" data-lang-en="TSS Villa">{{ $wsTitle ?? 'টি এস এস ভিলা' }}</span>
          <span class="footer-logo-sub">STUDENT &amp; GUEST HOSTEL</span>
        </div>
        <p class="footer-desc">
          নিরাপদ ও শান্ত পরিবেশে শিক্ষার্থীদের জন্য আধুনিক সুযোগ-সুবিধা সম্পন্ন একটি আদর্শ আবাসিক নিবাস।
        </p>
        <div class="footer-socials">
          <a href="#" class="footer-social-btn" title="Facebook">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
          </a>
          <a href="#" class="footer-social-btn" title="Instagram">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>
          </a>
          <a href="tel:{{ $wsPhone ?? '' }}" class="footer-social-btn" title="Phone">
            <i class="bi bi-telephone-fill"></i>
          </a>
        </div>
      </div>

      {{-- Col 2: Services --}}
      <div class="footer-col">
        <h4 class="footer-col-title">Services</h4>
        <ul class="footer-links">
          <li><a href="{{ url('/') }}">Free High-Speed Wifi</a></li>
          <li><a href="{{ url('/booking-now') }}">Easy Online Booking</a></li>
          <li><a href="{{ url('/') }}">Healthy Meal System</a></li>
          <li><a href="{{ url('/') }}">24/7 CCTV &amp; Security</a></li>
        </ul>
        <ul class="footer-links">
          <li><a href="{{ url('/') }}">Clean &amp; Hygiene Rooms</a></li>
          <li><a href="{{ url('/') }}">Uninterrupted Electricity</a></li>
          <li><a href="{{ url('/guest-support') }}">24/7 Help &amp; Support</a></li>
        </ul>
      </div>

      {{-- Col 3: Quick Links --}}
      <div class="footer-col">
        <h4 class="footer-col-title">Quick Links</h4>
        <ul class="footer-links">
          <li><a href="{{ url('/') }}">Home</a></li>
          <li><a href="{{ url('/#portfolioBg') }}">Gallery</a></li>
          <li><a href="{{ url('/#advantagesSection') }}">Advantages</a></li>
          <li><a href="{{ url('/booking-now') }}">Booking Now</a></li>
          <li><a href="{{ url('/guest-support') }}">Help &amp; Support</a></li>
        </ul>
      </div>

      {{-- Col 4: Contact --}}
      <div class="footer-col">
        <h4 class="footer-col-title">Have Questions?</h4>
        <ul class="footer-contact">
          <li>
            <span class="footer-contact-icon">
              <i class="bi bi-geo-alt-fill"></i>
            </span>
            <span>{{ $wsAddress ?? 'রংপুর, বাংলাদেশ' }}</span>
          </li>
          <li>
            <span class="footer-contact-icon">
              <i class="bi bi-telephone-fill"></i>
            </span>
            <span>+88{{ $wsPhone ?? '' }}</span>
          </li>
          <li>
            <span class="footer-contact-icon">
              <i class="bi bi-envelope-fill"></i>
            </span>
            <span>{{ $wsEmail ?? '' }}</span>
          </li>
        </ul>
      </div>

    </div>
  </div>

  {{-- Bottom Bar --}}
  <div class="footer-bottom">
    <div class="container">
      <p>Copyright &copy; {{ date('Y') }} <strong>{{ $wsTitle ?? 'TSS Villa' }}</strong>. All rights reserved | Powered by <a href="https://nsmlimited.com/" target="_blank" rel="noopener noreferrer" style="color: inherit; text-decoration: underline; font-weight: bold;">NSM LIMITED RANGPUR</a></p>
    </div>
  </div>

</footer>
