<!-- ===================== LOGIN MODAL ===================== -->
<div class="modal fade login-modal" id="loginModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content login-modal-content">

      <button type="button" class="login-close" data-bs-dismiss="modal" aria-label="Close">
        <i class="bi bi-x-lg"></i>
      </button>

      <div class="login-panel">
        <div class="login-card">
          <div class="login-card-header">
            <div class="login-card-logo">
              <img src="{{ $wsLogo }}" alt="TSS Villa Logo">
            </div>
            <h3 class="notranslate site-name-text" data-lang-bn="টি এস এস ভিলা" data-lang-en="TSS Villa">টি এস এস ভিলা</h3>
            <p>আপনার অ্যাকাউন্টে সাইন ইন করুন</p>
          </div>

          <form id="formAuthentication" class="login-form" action="{{ route('login') }}" method="POST">
            @csrf

            <!-- EMAIL OR PHONE FIELD -->
            <div class="l-field">
              <label for="login" class="l-label">ইমেইল অথবা ফোন নম্বর</label>
              <div class="l-input-group">
                <span class="l-input-icon"><i class="bi bi-person-circle"></i></span>
                <input
                  type="text"
                  class="l-input @error('login') is-invalid @enderror"
                  id="login"
                  name="login"
                  value="{{ old('login') }}"
                  placeholder="example@mail.com / 017xxxxxxxx"
                  autofocus
                  required
                />
              </div>
              @error('login')
                <span class="invalid-feedback d-block mt-1" role="alert">
                  <strong>{{ $message }}</strong>
                </span>
              @enderror
            </div>

            <!-- PASSWORD FIELD -->
            <div class="l-field">
              <div class="l-row">
                <label for="password" class="l-label">পাসওয়ার্ড</label>
                @if (Route::has('password.request'))
                  <a class="l-link" href="{{ route('password.request') }}">
                    পাসওয়ার্ড ভুলে গেছেন?
                  </a>
                @endif
              </div>

              <div class="l-input-group">
                <span class="l-input-icon"><i class="bi bi-lock"></i></span>
                <input
                  type="password"
                  class="l-input @error('password') is-invalid @enderror"
                  id="password"
                  name="password"
                  placeholder="••••••••"
                  required
                />
                <button type="button" class="l-pwd-toggle" onclick="toggleLoginPassword()" title="পাসওয়ার্ড দেখুন/লুকান" aria-label="Toggle password visibility">
                  <i class="bi bi-eye" id="loginPwdToggleIcon"></i>
                </button>
              </div>
              @error('password')
                <span class="invalid-feedback d-block mt-1" role="alert">
                  <strong>{{ $message }}</strong>
                </span>
              @enderror
            </div>

            <!-- REMEMBER ME -->
            <div class="l-field l-row2">
              <label class="l-check">
                <input type="checkbox" name="remember" id="remember" class="l-checkbox" {{ old('remember') ? 'checked' : '' }}>
                <span class="l-check-box"><i class="bi bi-check-lg"></i></span>
                <span class="l-mini">আমাকে মনে রাখুন</span>
              </label>
            </div>

            <!-- SUBMIT BUTTON -->
            <button class="l-btn" type="submit">
              <span>লগইন করুন</span>
              <i class="bi bi-arrow-right-circle-fill"></i>
            </button>
          </form>

        </div>
      </div>

    </div>
  </div>
</div>
<!-- ===================== /LOGIN MODAL ===================== -->

@push('scripts')
<script>
  function toggleLoginPassword() {
    const pwdInput = document.getElementById('password');
    const toggleIcon = document.getElementById('loginPwdToggleIcon');
    if (!pwdInput || !toggleIcon) return;
    if (pwdInput.type === 'password') {
      pwdInput.type = 'text';
      toggleIcon.classList.remove('bi-eye');
      toggleIcon.classList.add('bi-eye-slash');
    } else {
      pwdInput.type = 'password';
      toggleIcon.classList.remove('bi-eye-slash');
      toggleIcon.classList.add('bi-eye');
    }
  }

  document.addEventListener("DOMContentLoaded", () => {
    const hasLoginErrors = {{ ($errors->has('login') || $errors->has('password')) ? 'true' : 'false' }};
    if (hasLoginErrors && window.bootstrap) {
      const el = document.getElementById('loginModal');
      if (el) new bootstrap.Modal(el).show();
    }
  });
</script>
@endpush