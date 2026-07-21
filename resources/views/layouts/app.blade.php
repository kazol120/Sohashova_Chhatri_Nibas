<!doctype html>
@php
$baseurl = url('/');
@endphp
<html
    lang="en"
    class="light-style layout-wide customizer-hide"
    dir="ltr"
    data-theme="theme-default"
    data-assets-path="{{$baseurl.'/'.'backend/'}}"
    data-template="vertical-menu-template">
<head>
<meta charset="utf-8"/>
<meta name="viewport"
  content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"/>

<title>{{ config('app.name', 'Laravel') }}</title>

<meta name="description" content=""/>

<!-- Favicon -->
<link rel="icon" type="image/x-icon" href="{{asset("favicon.png")}}"/>

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap" rel="stylesheet"/>

<!-- Icons -->
<link rel="stylesheet" href="{{$baseurl}}/backend/vendor/fonts/fontawesome.css"/>
<link rel="stylesheet" href="{{$baseurl}}/backend/vendor/fonts/tabler-icons.css"/>
<link rel="stylesheet" href="{{$baseurl}}/backend/vendor/fonts/flag-icons.css"/>

<!-- Core CSS -->
<link rel="stylesheet" href="{{$baseurl}}/backend/vendor/css/rtl/core.css" class="template-customizer-core-css"/>
<link rel="stylesheet" href="{{$baseurl}}/backend/css/demo.css"/>
<link rel="stylesheet" href="{{$baseurl}}/backend/vendor/css/pages/page-auth.css"/>
 <!-- alert css -->
 <link rel="stylesheet" href="{{$baseurl.'/backend/vendor/libs/sweetalert2/sweetalert2.css'}}" />
<!-- Helpers -->
<script src="{{$baseurl}}/backend/vendor/js/helpers.js"></script>
<!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
<!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
<script src="{{$baseurl}}/backend/vendor/js/template-customizer.js"></script>
<!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<script src="{{$baseurl}}/backend/js/config.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel="stylesheet" type="text/css" href="registrationcss.style.css">
   

</head>

<body>
<!-- Content -->

<div class="container-xxl">
<div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner py-4">

        @yield('content')

    </div>
</div>
</div>

<script src="{{$baseurl}}/backend/vendor/libs/jquery/jquery.js"></script>
<script src="{{$baseurl}}/backend/vendor/js/bootstrap.js"></script>

<!-- Main JS -->
<script src="{{$baseurl}}/backend/js/main.js"></script>

@stack('script')
<!-- alert js -->
<script src="{{$baseurl.'/backend/vendor/libs/sweetalert2/sweetalert2.js'}}"></script>
<script src="{{$baseurl.'/backend/js/extended-ui-sweetalert2.js'}}"></script>
<script src="{{$baseurl.'/js/app.js'}}"></script>




<!-- alert fire -->
  @if(session()->has('success'))
      <script>
          document.addEventListener('DOMContentLoaded', function () {
              Swal.fire({
                  title: 'Success!',
                  text: '{{ session('success') }}',
                  icon: 'success',
                  timer: 2000,
                  timerProgressBar: true,
                  showConfirmButton: false // Hides the "OK" button

              });
          });
      </script>
  @endif
  @if(session()->has('error'))
      <script>
          document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                title: 'Error!',
                text: '{{ session('error') }}',
                icon: 'error',
                showConfirmButton: true,
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6', // Optional: Customize the color of the OK button
                showCancelButton: true,
                cancelButtonText: 'Cancel',
                cancelButtonColor: '#d33',
            });
          });
      </script>
  @endif
  @if(session()->has('warning'))
      <script>
          document.addEventListener('DOMContentLoaded', function () {
              Swal.fire({
                  title: 'warning!',
                  text: '{{ session('warning') }}',
                  icon: 'warning',
                  showConfirmButton: true,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#3085d6', // Optional: Customize the color of the OK button
                    showCancelButton: true,
                    cancelButtonText: 'Cancel',
                    cancelButtonColor: '#d33',

              });
          });
      </script>
  @endif
</body>
</html>
