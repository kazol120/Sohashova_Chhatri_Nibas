<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Home') | সোহাশোভা ছাত্রী নিবাস</title>
  <link rel="stylesheet" href="{{ asset('frontend/style.css') }}">
 <link rel="shortcut icon" href="{{ asset('logo/logoimage (2).png') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Anton&family=Tagesschrift&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  
  @stack('styles')
</head>
<body>

    @include('Frontend.layouts.partials.header')
    <main>
      @yield('content')
    </main>

    @include('Frontend.layouts.partials.login-modal')

    @include('Frontend.layouts.partials.footer')


    @include('Frontend.layouts.partials.booking-offcanvas')
    
    <!-- Hidden Google Translate Element -->
    <div id="google_translate_element" style="display:none !important; visibility:hidden !important; height:0 !important; width:0 !important; overflow:hidden !important;"></div>
    
    <script type="text/javascript">
      function googleTranslateElementInit() {
        new google.translate.TranslateElement({
          pageLanguage: 'auto',
          includedLanguages: 'en,bn',
          autoDisplay: false
        }, 'google_translate_element');
      }
    </script>
    <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

    <!-- Language Selector Sync Script -->
    <script type="text/javascript">
      document.addEventListener('DOMContentLoaded', function () {
        const desktopSelector = document.getElementById('customLangSelector');
        const mobileSelector = document.getElementById('mobileLangSelector');
        
        function translatePage(lang) {
          const googleSelect = document.querySelector('.goog-te-combo');
          if (googleSelect) {
            let targetValue = lang;
            if (lang === 'bn') {
              const options = Array.from(googleSelect.options);
              const hasBn = options.some(opt => opt.value === 'bn');
              targetValue = hasBn ? 'bn' : '';
            }
            googleSelect.value = targetValue;
            googleSelect.dispatchEvent(new Event('change'));
          } else {
            setTimeout(function() {
              translatePage(lang);
            }, 150);
          }
        }
        
        const savedLang = localStorage.getItem('selectedLang') || 'bn';
        if (desktopSelector) desktopSelector.value = savedLang;
        if (mobileSelector) mobileSelector.value = savedLang;
        
        if (savedLang !== 'bn') {
          setTimeout(function() {
            translatePage(savedLang);
          }, 800);
        }
        
        function handleLangChange(lang) {
          localStorage.setItem('selectedLang', lang);
          if (desktopSelector) desktopSelector.value = lang;
          if (mobileSelector) mobileSelector.value = lang;
          translatePage(lang);
        }
        
        if (desktopSelector) {
          desktopSelector.addEventListener('change', function () {
            handleLangChange(this.value);
          });
        }
        if (mobileSelector) {
          mobileSelector.addEventListener('change', function () {
            handleLangChange(this.value);
          });
        }
      });
    </script>
    
    <script src="{{ asset('frontend/main.js') }}" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')

</body>
</body>
</html>
