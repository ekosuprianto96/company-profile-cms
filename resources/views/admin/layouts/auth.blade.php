<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Star Admin2 </title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="{{ asset('assets/admin/assets/vendors/feather/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/assets/vendors/ti-icons/css/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/assets/vendors/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/assets/vendors/typicons/typicons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/assets/vendors/simple-line-icons/css/simple-line-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="{{ asset('assets/admin/assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/admin/assets/js/select.dataTables.min.css') }}">
    <!-- End plugin css for this page -->
    <link rel="stylesheet" href="{{ asset('assets/admin/assets/vendors/select2-bootstrap-theme/select2-bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/assets/vendors/select2/select2.min.css') }}">
    <!-- inject:css -->
    <link rel="stylesheet" href="{{ asset('assets/admin/assets/css/style.css') }}">
    <!-- endinject -->
    <link rel="shortcut icon" href="{{ asset('assets/admin/assets/images/favicon.png') }}" />

    {{-- Remixicon --}}
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet" />

    <script src="{{ asset('assets/admin/assets/vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="{{ asset('assets/admin/assets/vendors/select2/select2.min.js') }}"></script>

  </head>
  <body>
    
    @yield('content')

    <!-- container-scroller -->
    <!-- plugins:js -->
    <script src="{{ asset('assets/admin/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <script src="{{ asset('assets/admin/assets/vendors/chart.js/chart.umd.js') }}"></script>
    <script src="{{ asset('assets/admin/assets/vendors/progressbar.js/progressbar.min.js') }}"></script>
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="{{ asset('assets/admin/assets/js/off-canvas.js') }}"></script>
    <script src="{{ asset('assets/admin/assets/js/template.js') }}"></script>
    <script src="{{ asset('assets/admin/assets/js/settings.js') }}"></script>
    <script src="{{ asset('assets/admin/assets/js/hoverable-collapse.js') }}"></script>
    <script src="{{ asset('assets/admin/assets/js/todolist.js') }}"></script>
    <!-- endinject -->
    <!-- Custom js for this page-->
    <script src="{{ asset('assets/admin/assets/js/jquery.cookie.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/admin/assets/js/dashboard.js') }}"></script>
    <!-- <script src="assets/js/Chart.roundedBarCharts.js"></script> -->
    <!-- End custom js for this page-->
  </body>
</html>