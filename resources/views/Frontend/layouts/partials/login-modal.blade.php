<!-- ===================== LOGIN MODAL ===================== -->
<div class="modal fade login-modal" id="loginModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content login-modal-content">

      <button type="button" class="login-close" data-bs-dismiss="modal" aria-label="Close">
        <i class="bi bi-x-lg"></i>
      </button>

      <div class="login-grid">
        <!-- LEFT (Visual) -->
        <div class="login-visual">
          <div class="login-brand">
            <div class="login-logo">
              <img src="{{ asset('logo/logoimage (2).png') }}" alt="Sohashova Chhatri Nibas Logo">
            </div>
            <div>
              <div class="login-brand-title">সোহাশোভা ছাত্রী নিবাস</div>
              <div class="login-brand-sub">Safe · Secure · Homely</div>
            </div>
          </div>

          <div class="login-hero">
            <h2>স্বাগতম — সোহাশোভায়</h2>
            <p>নিরাপদ, পরিষ্কার ও বাড়ির মতো পরিবেশে আপনাকে স্বাগত জানাই। লগইন করুন এবং আপনার বুকিং ম্যানেজ করুন।</p>
          </div>

          <div class="login-foot">© {{ date('Y') }} সোহাশোভা ছাত্রী নিবাস · সকল অধিকার সংরক্ষিত</div>
        </div>

        <!-- RIGHT -->
        <div class="login-panel">
          <div class="login-card">
            <div class="login-card-logo">
              <img src="{{ asset('logo/logoimage (2).png') }}" alt="Logo">
            </div>
            <h3>Login</h3>
            <p>আপনার অ্যাকাউন্টে সাইন ইন করুন</p>

            <form id="formAuthentication" class="mb-3" action="{{ route('login') }}" method="POST">
              @csrf

              <div class="l-field">
                <label for="login">Email or Phone</label>
                <input
                  type="text"
                  class="l-input @error('login') is-invalid @enderror"
                  id="login"
                  name="login"
                  value="{{ old('login') }}"
                  placeholder="Enter your email or phone"
                  autofocus
                  required
                />
                @error('login')
                  <span class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                  </span>
                @enderror
              </div>

              <div class="l-field">
                <div class="l-row">
                  <label for="password">Password</label>
                  <a class="l-link" href="{{ route('password.request') }}">
                    Forgot Password?
                  </a>
                </div>

                <input
                  type="password"
                  class="l-input @error('password') is-invalid @enderror"
                  id="password"
                  name="password"
                  placeholder="••••••••"
                  required
                />

                @error('password')
                  <span class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                  </span>
                @enderror
              </div>

              <button class="l-btn" type="submit">
                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
              </button>
            </form>

          </div>
        </div>

      </div>
    </div>
  </div>
</div>
<!-- ===================== /LOGIN MODAL ===================== -->

@push('scripts')
<script>
  document.addEventListener("DOMContentLoaded", () => {
    const hasLoginErrors = {{ ($errors->has('login') || $errors->has('password')) ? 'true' : 'false' }};
    if (hasLoginErrors && window.bootstrap) {
      const el = document.getElementById('loginModal');
      if (el) new bootstrap.Modal(el).show();
    }
  });
</script>
@endpush