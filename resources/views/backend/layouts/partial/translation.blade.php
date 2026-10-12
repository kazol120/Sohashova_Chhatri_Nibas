<!-- ===== Google Translate (Hidden Element) ===== -->
<div id="google_translate_element" style="position:absolute;top:-9999px;left:-9999px;width:1px;height:1px;overflow:hidden;opacity:0;pointer-events:none;"></div>

<style>
/* Hide Google Translate top banner, toolbar, popups and tooltips */
.goog-te-banner-frame.skiptranslate,
.goog-te-banner-frame,
iframe.skiptranslate,
iframe[class*="skiptranslate"],
iframe[class*="goog"],
iframe[src*="translate.google"],
.VIpgJd-Z1Dxnd-O96Twb-Lg2fx,
#goog-gt-tt,
.goog-te-balloon-frame,
.goog-te-gadget,
.goog-te-spinner-pos,
.goog-tooltip,
#google_translate_element {
  display: none !important;
  visibility: hidden !important;
  height: 0 !important;
  width: 0 !important;
  opacity: 0 !important;
  pointer-events: none !important;
}

html, body {
  top: 0px !important;
  margin-top: 0px !important;
  padding-top: 0px !important;
  position: static !important;
}

.goog-text-highlight {
  background-color: transparent !important;
  box-shadow: none !important;
}

/* Custom styles for Language toggle button */
#btn-language-toggle {
  border-width: 1.5px;
  user-select: none;
}
#btn-language-toggle:hover {
  transform: translateY(-1px);
}
</style>

<script type="text/javascript">
  function googleTranslateElementInit() {
    new google.translate.TranslateElement({
      pageLanguage: 'en',
      includedLanguages: 'en,bn',
      autoDisplay: false
    }, 'google_translate_element');
  }
</script>
<script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

<script type="text/javascript">
(function() {
  /* ---- Kill Google Translate Banner & Layout shifts ---- */
  function killBanner() {
    var hide = [
      '.goog-te-banner-frame', 'iframe.skiptranslate',
      'iframe[class*="goog"]', 'iframe[src*="translate.google"]',
      '.VIpgJd-Z1Dxnd-O96Twb-Lg2fx', '#goog-gt-tt', '.goog-te-balloon-frame',
      '.goog-te-spinner-pos', '.goog-tooltip'
    ];
    hide.forEach(function(s) {
      document.querySelectorAll(s).forEach(function(el) {
        el.style.setProperty('display', 'none', 'important');
        el.style.setProperty('height', '0', 'important');
        el.style.setProperty('visibility', 'hidden', 'important');
      });
    });
    if (document.body) {
      document.body.style.setProperty('top', '0px', 'important');
      document.body.style.setProperty('margin-top', '0px', 'important');
      document.body.style.setProperty('position', 'static', 'important');
    }
    if (document.documentElement) {
      document.documentElement.style.setProperty('top', '0px', 'important');
      document.documentElement.style.setProperty('margin-top', '0px', 'important');
      document.documentElement.style.setProperty('position', 'static', 'important');
    }
  }

  killBanner();
  setInterval(killBanner, 300);
  document.addEventListener('DOMContentLoaded', killBanner);
  if (window.MutationObserver) {
    new MutationObserver(killBanner).observe(document.documentElement, { childList: true, subtree: true });
  }

  /* ---- Read Language from Cookie ---- */
  function getLang() {
    var m = document.cookie.match(/(?:^|;)\s*googtrans=([^;]*)/);
    if (m) {
      var v = decodeURIComponent(m[1]);
      if (v.indexOf('/bn') !== -1) return 'bn';
    }
    return 'en';
  }

  /* ---- Set Language Cookie ---- */
  function setCookieLang(lang) {
    var val = '/en/' + lang;
    document.cookie = 'googtrans=' + val + '; path=/';
    document.cookie = 'googtrans=' + val + '; path=/; domain=' + location.hostname;
    var parts = location.hostname.split('.');
    if (parts.length > 1) {
      document.cookie = 'googtrans=' + val + '; path=/; domain=.' + parts.slice(-2).join('.');
    }
  }

  /* ---- Update Button Label & Body Class ---- */
  function updateBtn(lang) {
    var el = document.getElementById('lang-toggle-label');
    if (el) {
      el.textContent = (lang === 'bn') ? 'English' : 'বাংলা';
    }
    if (document.body) {
      document.body.classList.remove('lang-bn', 'lang-en');
      document.body.classList.add('lang-' + lang);
    }
  }

  /* ---- Apply via combo without full page reload if possible ---- */
  function applyViaCombo(lang, cb) {
    var combo = document.querySelector('select.goog-te-combo');
    if (combo) {
      if (combo.value !== lang) {
        combo.value = lang;
        combo.dispatchEvent(new Event('change'));
      }
      [200, 500, 1000, 1500].forEach(function(t) { setTimeout(killBanner, t); });
      if (cb) setTimeout(cb, 200);
      return true;
    }
    return false;
  }

  /* ---- Main Toggle Function globally accessible ---- */
  window.toggleAppLanguage = function() {
    var current = getLang();
    var next = (current === 'bn') ? 'en' : 'bn';

    // 1. Update cookie
    setCookieLang(next);

    // 2. Update button label & body class
    updateBtn(next);

    // 3. Try combo
    if (applyViaCombo(next)) {
      setTimeout(function() {
        location.reload();
      }, 300);
      return;
    }

    // 4. Retry combo or reload
    var tries = 0;
    var iv = setInterval(function() {
      tries++;
      if (applyViaCombo(next) || tries > 8) {
        clearInterval(iv);
        location.reload();
      }
    }, 150);
  };

  /* ---- On page load: restore language from cookie ---- */
  document.addEventListener('DOMContentLoaded', function() {
    var lang = getLang();
    updateBtn(lang);

    if (lang === 'bn') {
      var tries = 0;
      var iv = setInterval(function() {
        tries++;
        var combo = document.querySelector('select.goog-te-combo');
        if (combo) {
          if (combo.value !== 'bn') {
            combo.value = 'bn';
            combo.dispatchEvent(new Event('change'));
          }
          [200, 500, 1000, 1500].forEach(function(t) { setTimeout(killBanner, t); });
          clearInterval(iv);
        } else if (tries > 20) {
          clearInterval(iv);
        }
      }, 200);
    }
  });
})();
</script>
