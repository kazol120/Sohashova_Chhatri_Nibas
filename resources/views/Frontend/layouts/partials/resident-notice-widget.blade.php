<!-- ===================== RESIDENT NOTICE WIDGET & MODAL ===================== -->
<style>
  /* Floating Smart Notice Toast Card */
  .resident-notice-toast {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 1040;
    max-width: 380px;
    width: calc(100% - 32px);
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border-radius: 16px;
    box-shadow: 0 16px 40px -8px rgba(3, 51, 100, 0.22), 0 4px 14px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(3, 51, 100, 0.12);
    overflow: hidden;
    transform: translateY(120px) scale(0.92);
    opacity: 0;
    pointer-events: none;
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
  }

  .resident-notice-toast.active {
    transform: translateY(0) scale(1);
    opacity: 1;
    pointer-events: auto;
  }

  .resident-notice-toast-top {
    height: 4px;
    background: linear-gradient(90deg, #033364, #0d6efd, #00d2ff);
  }

  .resident-notice-toast.priority-urgent .resident-notice-toast-top {
    background: linear-gradient(90deg, #dc3545, #ff4757, #ffa502);
  }

  .resident-notice-toast.priority-important .resident-notice-toast-top {
    background: linear-gradient(90deg, #f59e0b, #fbbf24, #10b981);
  }

  .notice-bell-pulse {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: rgba(3, 51, 100, 0.08);
    color: #033364;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 20px;
    position: relative;
  }

  .resident-notice-toast.priority-urgent .notice-bell-pulse {
    background: rgba(220, 53, 69, 0.12);
    color: #dc3545;
  }

  .notice-bell-pulse::after {
    content: '';
    position: absolute;
    top: -2px;
    right: -2px;
    width: 10px;
    height: 10px;
    background: #dc3545;
    border-radius: 50%;
    border: 2px solid #fff;
    animation: noticePing 1.8s infinite;
  }

  @keyframes noticePing {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
    70% { transform: scale(1.1); box-shadow: 0 0 0 8px rgba(220, 53, 69, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
  }

  .resident-notice-toast-body {
    padding: 14px 16px;
  }

  .notice-toast-title {
    font-size: 15px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.3;
    margin-bottom: 4px;
  }

  .notice-toast-desc {
    font-size: 13px;
    color: #475569;
    line-height: 1.4;
    margin-bottom: 12px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  .btn-notice-view {
    background: #033364;
    color: #ffffff;
    font-size: 13px;
    font-weight: 600;
    padding: 6px 14px;
    border-radius: 8px;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
    cursor: pointer;
  }

  .btn-notice-view:hover {
    background: #022244;
    color: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(3, 51, 100, 0.25);
  }

  .btn-notice-close {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    border: none;
    background: #f1f5f9;
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    transition: all 0.2s ease;
    cursor: pointer;
  }

  .btn-notice-close:hover {
    background: #e2e8f0;
    color: #0f172a;
  }

  .notice-history-item-box {
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    padding: 10px 14px;
    margin-bottom: 8px;
  }

  @media (max-width: 576px) {
    .resident-notice-toast {
      bottom: 16px;
      right: 16px;
      left: 16px;
      width: auto;
      max-width: none;
    }
  }
</style>

<!-- Floating Smart Notice Alert Toast Card (Shows only when new notice exists) -->
<div id="residentNoticeToast" class="resident-notice-toast notranslate" translate="no" aria-live="polite">
  <div class="resident-notice-toast-top"></div>
  <div class="resident-notice-toast-body">
    <div class="d-flex align-items-start gap-3">
      <div class="notice-bell-pulse">
        <i class="bi bi-bell-fill"></i>
      </div>
      <div class="flex-grow-1">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <span class="badge bg-danger rounded-pill px-2 py-0.5" style="font-size: 10px;" id="toastBadge">
            জরুরি বিজ্ঞপ্তি
          </span>
          <button type="button" class="btn-notice-close" onclick="dismissResidentNoticeToast(true)" title="বন্ধ করুন">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
        <div class="notice-toast-title" id="toastTitle">
          জরুরি বিজ্ঞপ্তি
        </div>
        <div class="notice-toast-desc" id="toastSnippet">
          কর্তৃপক্ষের পক্ষ থেকে নতুন বিজ্ঞপ্তি প্রকাশ করা হয়েছে...
        </div>
        <div class="d-flex align-items-center justify-content-between mt-2 pt-1 border-top">
          <small class="text-muted" style="font-size: 11px;" id="toastTime">আজ</small>
          <button type="button" class="btn-notice-view" onclick="openResidentNoticeModal()" id="btnNoticeView">
            <span>বিজ্ঞপ্তি দেখুন</span>
            <i class="bi bi-arrow-right-short fs-6"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ===================== NOTICE MODAL WITH ORIGINAL LOGIN DESIGN ===================== -->
<div class="modal fade login-modal notranslate" translate="no" id="residentNoticeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content login-modal-content">

      <button type="button" class="login-close" data-bs-dismiss="modal" aria-label="Close">
        <i class="bi bi-x-lg"></i>
      </button>

      <div class="login-panel">
        
        <!-- STEP 1: LOGIN CARD (Exact Same Design as Website Login Modal) -->
        <div class="login-card" id="noticeLoginCard">
          <div class="login-card-header">
            <div class="login-card-logo">
              <img src="{{ $wsLogo ?? asset('image/logo.png') }}" alt="TSS Villa Logo">
            </div>
            <h3 class="notranslate site-name-text" data-lang-bn="টি এস এস ভিলা" data-lang-en="TSS Villa">টি এস এস ভিলা</h3>
            <p id="noticeLoginCardSubText">আপনার অ্যাকাউন্টে সাইন ইন করুন</p>
          </div>

          <!-- Inline Notice Error Alert -->
          <div id="noticeLoginErrorNotice" class="alert alert-danger py-2 px-3 small rounded-3 mb-3 d-none d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-circle-fill fs-6 flex-shrink-0"></i>
            <span id="noticeLoginErrorNoticeText" style="font-size: 12.5px;"></span>
          </div>

          <!-- Standalone Verification Form -->
          <form id="noticeLoginForm" onsubmit="handleNoticeModalLoginSubmit(event)" class="login-form" autocomplete="off">
            
            <!-- EMAIL OR PHONE FIELD -->
            <div class="l-field">
              <label for="noticeLoginInput" class="l-label">ইমেইল অথবা ফোন নম্বর</label>
              <div class="l-input-group">
                <span class="l-input-icon"><i class="bi bi-person-circle"></i></span>
                <input
                  type="text"
                  class="l-input"
                  id="noticeLoginInput"
                  placeholder="example@mail.com / 017xxxxxxxx"
                  autocomplete="off"
                  required
                />
              </div>
            </div>

            <!-- PASSWORD FIELD -->
            <div class="l-field">
              <div class="l-row">
                <label for="noticePasswordInput" class="l-label">পাসওয়ার্ড</label>
              </div>

              <div class="l-input-group">
                <span class="l-input-icon"><i class="bi bi-lock"></i></span>
                <input
                  type="password"
                  class="l-input"
                  id="noticePasswordInput"
                  placeholder="••••••••"
                  autocomplete="current-password"
                  required
                />
                <button type="button" class="l-pwd-toggle" onclick="toggleNoticePasswordVisibility()" title="পাসওয়ার্ড দেখুন/লুকান" aria-label="Toggle password visibility">
                  <i class="bi bi-eye" id="noticePwdToggleIcon"></i>
                </button>
              </div>
            </div>

            <!-- REMEMBER ME -->
            <div class="l-field l-row2">
              <label class="l-check">
                <input type="checkbox" id="noticeRemember" class="l-checkbox" checked>
                <span class="l-check-box"><i class="bi bi-check-lg"></i></span>
                <span class="l-mini">আমাকে মনে রাখুন</span>
              </label>
            </div>

            <!-- SUBMIT BUTTON -->
            <button class="l-btn" type="submit" id="btnNoticeLoginSubmit">
              <span id="btnNoticeLoginText">লগইন করুন</span>
              <i class="bi bi-arrow-right-circle-fill"></i>
            </button>
          </form>
        </div>

        <!-- STEP 2: IN-MODAL NOTICE LIST & HISTORY (Shown upon successful verification) -->
        <div class="login-card d-none" id="noticeBoardResultCard">
          <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
            <div class="d-flex align-items-center gap-2">
              <div class="login-card-logo mb-0" style="width: 42px; height: 42px;">
                <img src="{{ $wsLogo ?? asset('image/logo.png') }}" alt="TSS Villa Logo" style="width: 32px; height: 32px;">
              </div>
              <div>
                <h5 class="fw-bold text-dark mb-0" style="font-size: 16px;">জরুরি বিজ্ঞপ্তি বোর্ড</h5>
                <small class="text-muted" style="font-size: 12px;">স্বাগতম, <span id="noticeStudentName" class="text-primary fw-bold">শিক্ষার্থী</span></small>
              </div>
            </div>
            <span class="badge bg-success rounded-pill px-2.5 py-1 small">
              <i class="bi bi-shield-check me-1"></i>যাচাইকৃত
            </span>
          </div>

          <!-- Latest Notice -->
          <div class="mb-3 text-start">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="badge bg-primary px-2.5 py-1 rounded-pill small">
                <i class="bi bi-pin-angle-fill me-1"></i> সর্বশেষ বিজ্ঞপ্তি
              </span>
              <span class="small text-muted" id="noticeLatestDate"></span>
            </div>
            <div class="p-3 rounded-3 border bg-light">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <h6 class="fw-bold text-dark mb-0 fs-6" id="noticeLatestTitle"></h6>
                <span id="noticeLatestPriority"></span>
              </div>
              <div class="text-secondary small mb-0" id="noticeLatestContent" style="white-space: pre-wrap; line-height: 1.6; font-size: 13px;"></div>
            </div>
          </div>

          <!-- Notice History / Archive -->
          <div id="noticeHistorySection" class="mt-3 pt-2 border-top text-start">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="fw-bold text-dark mb-0 small">পূর্ববর্তী নোটিশসমূহ (Notice History)</h6>
              <span class="badge bg-light text-secondary border" id="noticeTotalCountBadge">০ টি নোটিশ</span>
            </div>
            <div id="noticeHistoryContainer" style="max-height: 200px; overflow-y: auto;"></div>
          </div>

          <div class="mt-3 pt-2 text-center">
            <button type="button" class="btn btn-secondary btn-sm px-4 rounded-3" data-bs-dismiss="modal">
              মডাল বন্ধ করুন
            </button>
          </div>
        </div>

      </div>

    </div>
  </div>
</div>

<script>
  window.residentNoticeState = {
    latestNoticeId: null,
    reopenTimer: null,
  };

  document.addEventListener('DOMContentLoaded', function () {
    initResidentNoticeCheck();
    // Live polling check every 15 seconds
    setInterval(initResidentNoticeCheck, 15000);

    // Modal show & close listeners
    const modalEl = document.getElementById('residentNoticeModal');
    if (modalEl) {
      modalEl.addEventListener('show.bs.modal', function () {
        dismissResidentNoticeToast(false);
      });

      modalEl.addEventListener('hidden.bs.modal', function () {
        resetResidentNoticeModalState();

        const seenNoticeId = localStorage.getItem('seen_resident_notice_id');
        const isUnread = !seenNoticeId || parseInt(seenNoticeId) !== parseInt(window.residentNoticeState.latestNoticeId);
        if (isUnread) {
          dismissResidentNoticeToast(true);
        } else {
          dismissResidentNoticeToast(false);
          const toastEl = document.getElementById('residentNoticeToast');
          if (toastEl) {
            toastEl.classList.remove('active');
            toastEl.style.display = 'none';
          }
        }
      });
    }
  });

  // 1. Check if new notice exists from API
  async function initResidentNoticeCheck() {
    try {
      const response = await fetch('/api/resident-notices/latest-alert');
      if (!response.ok) return;

      const data = await response.json();
      if (!data.has_notice) {
        dismissResidentNoticeToast(false);
        return;
      }

      const previousLatestId = window.residentNoticeState.latestNoticeId;
      window.residentNoticeState.latestNoticeId = data.id;

      const seenNoticeId = localStorage.getItem('seen_resident_notice_id');
      const isUnread = !seenNoticeId || parseInt(seenNoticeId) !== parseInt(data.id);

      // If a brand new notice is detected via live polling, reset seen state
      if (previousLatestId && parseInt(data.id) > parseInt(previousLatestId)) {
        localStorage.removeItem('seen_resident_notice_id');
      }

      const toastEl = document.getElementById('residentNoticeToast');

      // If already seen/read, make sure toast card is completely hidden
      if (!isUnread) {
        dismissResidentNoticeToast(false);
        if (toastEl) {
          toastEl.classList.remove('active');
          toastEl.style.display = 'none';
        }
        return;
      }

      // Populate toast elements
      const titleEl = document.getElementById('toastTitle');
      const snippetEl = document.getElementById('toastSnippet');
      const timeEl = document.getElementById('toastTime');
      const badgeEl = document.getElementById('toastBadge');

      if (titleEl) titleEl.innerText = data.title || 'জরুরি বিজ্ঞপ্তি';
      if (snippetEl) snippetEl.innerText = data.short_text || 'কর্তৃপক্ষের নতুন বিজ্ঞপ্তি দেখতে ক্লিক করুন...';
      if (timeEl) timeEl.innerText = data.published_at || 'আজ';

      if (toastEl) {
        toastEl.style.display = '';
        toastEl.classList.remove('priority-urgent', 'priority-important');
        if (data.priority === 'urgent') {
          toastEl.classList.add('priority-urgent');
          if (badgeEl) {
            badgeEl.className = 'badge bg-danger rounded-pill px-2 py-0.5';
            badgeEl.innerText = 'অতি জরুরি';
          }
        } else if (data.priority === 'important') {
          toastEl.classList.add('priority-important');
          if (badgeEl) {
            badgeEl.className = 'badge bg-warning text-dark rounded-pill px-2 py-0.5';
            badgeEl.innerText = 'গুরুত্বপূর্ণ';
          }
        } else {
          if (badgeEl) {
            badgeEl.className = 'badge bg-primary rounded-pill px-2 py-0.5';
            badgeEl.innerText = 'জরুরি বিজ্ঞপ্তি';
          }
        }

        // Show toast if unread, modal not currently open, and not currently in snooze
        const modalOpen = document.getElementById('residentNoticeModal')?.classList.contains('show');
        if (isUnread && !modalOpen && !toastEl.classList.contains('active') && !window.residentNoticeState.reopenTimer) {
          toastEl.classList.add('active');
        }
      }
    } catch (err) {
      console.warn('Resident notice alert check failed:', err);
    }
  }

  // 2. Dismiss Toast (Automatically re-appears after 3 seconds if not logged in and viewed)
  function dismissResidentNoticeToast(snooze = true) {
    const toastEl = document.getElementById('residentNoticeToast');
    if (toastEl) {
      toastEl.classList.remove('active');
    }

    if (window.residentNoticeState.reopenTimer) {
      clearTimeout(window.residentNoticeState.reopenTimer);
      window.residentNoticeState.reopenTimer = null;
    }

    if (snooze) {
      // Re-appear after exactly 3 seconds without needing page reload
      window.residentNoticeState.reopenTimer = setTimeout(function () {
        window.residentNoticeState.reopenTimer = null;
        const seenNoticeId = localStorage.getItem('seen_resident_notice_id');
        const isUnread = !seenNoticeId || parseInt(seenNoticeId) !== parseInt(window.residentNoticeState.latestNoticeId);

        const modalOpen = document.getElementById('residentNoticeModal')?.classList.contains('show');
        if (isUnread && !modalOpen && toastEl && window.residentNoticeState.latestNoticeId) {
          toastEl.style.display = '';
          toastEl.classList.add('active');
        }
      }, 3000);
    }
  }

  // 3. Open Resident Notice Modal (Shows login form inside modal)
  function openResidentNoticeModal(phone = '') {
    dismissResidentNoticeToast(false);
    const toastEl = document.getElementById('residentNoticeToast');
    if (toastEl) {
      toastEl.classList.remove('active');
    }

    const modalEl = document.getElementById('residentNoticeModal');
    if (!modalEl) return;

    resetResidentNoticeModalState();

    if (phone) {
      const loginInput = document.getElementById('noticeLoginInput');
      if (loginInput) loginInput.value = phone;
    }

    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
  }

  // Reset modal state
  function resetResidentNoticeModalState() {
    const loginCard = document.getElementById('noticeLoginCard');
    const resultCard = document.getElementById('noticeBoardResultCard');
    const loginInput = document.getElementById('noticeLoginInput');
    const passInput = document.getElementById('noticePasswordInput');
    const errNotice = document.getElementById('noticeLoginErrorNotice');

    if (loginInput) loginInput.value = '';
    if (passInput) passInput.value = '';
    if (errNotice) errNotice.classList.add('d-none');

    if (resultCard) resultCard.classList.add('d-none');
    if (loginCard) loginCard.classList.remove('d-none');
  }

  // Toggle Password Visibility
  function toggleNoticePasswordVisibility() {
    const pwdInput = document.getElementById('noticePasswordInput');
    const toggleIcon = document.getElementById('noticePwdToggleIcon');
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

  // 4. Handle Modal Login Form Submit (Verifies and displays notice board in modal without going to dashboard)
  async function handleNoticeModalLoginSubmit(e) {
    e.preventDefault();

    const loginVal = document.getElementById('noticeLoginInput').value.trim();
    const passVal = document.getElementById('noticePasswordInput').value;
    const errNotice = document.getElementById('noticeLoginErrorNotice');
    const errText = document.getElementById('noticeLoginErrorNoticeText');
    const btnSubmit = document.getElementById('btnNoticeLoginSubmit');
    const btnText = document.getElementById('btnNoticeLoginText');

    if (!loginVal || !passVal) return;

    if (errNotice) errNotice.classList.add('d-none');
    if (btnSubmit) btnSubmit.disabled = true;
    if (btnText) btnText.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> লগইন হচ্ছে...';

    try {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

      const response = await fetch('/api/resident-notices/verify', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken || '',
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          login: loginVal,
          password: passVal,
        }),
      });

      const result = await response.json();

      if (!response.ok || !result.success) {
        if (errNotice && errText) {
          errText.innerText = result.message || 'ভুল মোবাইল নম্বর অথবা পাসওয়ার্ড!';
          errNotice.classList.remove('d-none');
        }
        return;
      }

      // Hide login card, show Notice Board result card
      const loginCard = document.getElementById('noticeLoginCard');
      const resultCard = document.getElementById('noticeBoardResultCard');

      if (loginCard) loginCard.classList.add('d-none');
      if (resultCard) resultCard.classList.remove('d-none');

      document.getElementById('noticeStudentName').innerText = result.resident_name || 'শিক্ষার্থী';
      renderNoticeListInModal(result);

      // Permanently mark this notice as viewed in localStorage since resident successfully logged in and viewed
      const latestId = window.residentNoticeState.latestNoticeId || (result.latest ? result.latest.id : '1');
      localStorage.setItem('seen_resident_notice_id', String(latestId));

      // Completely hide and remove the floating notice toast card from the frontend
      dismissResidentNoticeToast(false);
      const toastEl = document.getElementById('residentNoticeToast');
      if (toastEl) {
        toastEl.classList.remove('active');
        toastEl.style.display = 'none';
      }

    } catch (err) {
      if (errNotice && errText) {
        errText.innerText = 'সার্ভারে সমস্যা হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।';
        errNotice.classList.remove('d-none');
      }
    } finally {
      if (btnSubmit) btnSubmit.disabled = false;
      if (btnText) btnText.innerText = 'লগইন করুন';
    }
  }

  // 5. Render Notices inside Modal
  function renderNoticeListInModal(data) {
    const latest = data.latest;
    const all = data.all || [];

    if (latest) {
      document.getElementById('noticeLatestTitle').innerText = latest.title || 'জরুরি বিজ্ঞপ্তি';
      document.getElementById('noticeLatestContent').innerText = latest.notice || '';
      document.getElementById('noticeLatestDate').innerHTML = `<i class="bi bi-calendar3 me-1"></i> ${latest.formatted_at || latest.date}`;

      const priorityBadge = document.getElementById('noticeLatestPriority');
      if (priorityBadge) {
        if (latest.priority === 'urgent') {
          priorityBadge.className = 'badge bg-danger rounded-pill px-2 py-0.5';
          priorityBadge.innerText = 'অতি জরুরি';
        } else if (latest.priority === 'important') {
          priorityBadge.className = 'badge bg-warning text-dark rounded-pill px-2 py-0.5';
          priorityBadge.innerText = 'গুরুত্বপূর্ণ';
        } else {
          priorityBadge.className = 'badge bg-secondary rounded-pill px-2 py-0.5';
          priorityBadge.innerText = 'সাধারণ';
        }
      }
    }

    const historySection = document.getElementById('noticeHistorySection');
    const historyContainer = document.getElementById('noticeHistoryContainer');
    const countBadge = document.getElementById('noticeTotalCountBadge');

    const previousNotices = all.slice(1);

    if (countBadge) {
      countBadge.innerText = `${all.length} টি নোটিশ`;
    }

    if (previousNotices.length > 0) {
      historySection.classList.remove('d-none');
      historyContainer.innerHTML = previousNotices.map((n) => `
        <div class="notice-history-item-box">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="fw-bold text-dark small">${escapeHtml(n.title || 'জরুরি বিজ্ঞপ্তি')}</span>
            <small class="text-muted" style="font-size: 11px;">
              <i class="bi bi-clock me-1"></i>${escapeHtml(n.formatted_at || n.date)}
            </small>
          </div>
          <p class="text-secondary small mb-0" style="white-space: pre-wrap; font-size: 12.5px; line-height: 1.5;">${escapeHtml(n.notice)}</p>
        </div>
      `).join('');
    } else {
      if (historySection) historySection.classList.add('d-none');
    }
  }

  function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
  }
</script>
