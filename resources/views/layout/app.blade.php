<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>{{ $title ?? 'PERCEPAT' }}</title>
    <!-- loader-->
    <link href="{{ asset('vendor/assets/css/pace.min.css') }}" rel="stylesheet" />
    <script src="{{ asset('vendor/assets/js/pace.min.js') }}"></script>
    <!--favicon-->
    <link rel="icon" href="{{ asset('vendor/assets/images/favicon.ico') }}" type="image/x-icon">
    <!-- Vector CSS -->
    <link href="{{ asset('vendor/assets/plugins/vectormap/jquery-jvectormap-2.0.2.css') }}" rel="stylesheet" />
    <!-- simplebar CSS-->
    <link href="{{ asset('vendor/assets/plugins/simplebar/css/simplebar.css') }}" rel="stylesheet" />
    <!-- Bootstrap core CSS-->
    <link href="{{ asset('vendor/assets/css/bootstrap.min.css') }}" rel="stylesheet" />
    <!-- animate CSS-->
    <link href="{{ asset('vendor/assets/css/animate.css') }}" rel="stylesheet" type="text/css" />
    <!-- Icons CSS-->
    <link href="{{ asset('vendor/assets/css/icons.css') }}" rel="stylesheet" type="text/css" />
    <!-- Sidebar CSS-->
    <link href="{{ asset('vendor/assets/css/sidebar-menu.css') }}" rel="stylesheet" />
    <!-- Custom Style-->
    <link href="{{ asset('vendor/assets/css/app-style.css') }}" rel="stylesheet" />
    {{-- Datatables --}}
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.24/r-2.2.7/datatables.min.css"/>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @stack('styles')
</head>

<body class="bg-theme bg-theme1">

    <!-- Start wrapper-->
    <div id="wrapper">

        @include('layout.sidebar')

        @include('layout.topbar')

        <div class="clearfix"></div>

        <div class="content-wrapper">
            <div class="container-fluid">

                @section('content')
                    <div class="row col-12 mt-3">
                        <h2>{{ $header ?? '' }}</h2>
                    </div>
                @show

            <!--start overlay-->
            <div class="overlay toggle-menu"></div>
            <!--end overlay-->

            </div>
            <!-- End container-fluid-->

        </div>
        <!--End content-wrapper-->
        <!--Start Back To Top Button-->
        <a href="javaScript:void();" class="back-to-top"><i class="fa fa-angle-double-up"></i> </a>
        <!--End Back To Top Button-->

        <!--Start footer-->
        <footer class="footer">
            <div class="container">
                <div class="text-center">
                    Copyright © {{ now()->year == '2021' ? '2021' : '2021 - '.now()->year }} <a href="https://bbpommataram.id" target="_blank">BBPOM Mataram</a>
                </div>
            </div>
        </footer>
        <!--End footer-->

        <!--start color switcher-->
        <div class="right-sidebar">
            <div class="switcher-icon">
                <i class="zmdi zmdi-settings"></i>
            </div>
            <div class="right-sidebar-content">

                <p class="mb-0">Gaussion Texture</p>
                <hr>

                <ul class="switcher">
                    <li id="theme1"></li>
                    <li id="theme2"></li>
                    <li id="theme3"></li>
                    <li id="theme4"></li>
                    <li id="theme5"></li>
                    <li id="theme6"></li>
                </ul>

                <p class="mb-0">Gradient Background</p>
                <hr>

                <ul class="switcher">
                    <li id="theme7"></li>
                    <li id="theme8"></li>
                    <li id="theme9"></li>
                    <li id="theme10"></li>
                    <li id="theme11"></li>
                    <li id="theme12"></li>
                    <li id="theme13"></li>
                    <li id="theme14"></li>
                    <li id="theme15"></li>
                </ul>

            </div>
        </div>
        <!--end color switcher-->

    </div>
    <!--End wrapper-->

    <!-- Bootstrap core JavaScript-->
    <script src="{{ asset('vendor/assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/assets/js/popper.min.js') }}"></script>
    <script src="{{ asset('vendor/assets/js/bootstrap.min.js') }}"></script>

    <!-- simplebar js -->
    <script src="{{ asset('vendor/assets/plugins/simplebar/js/simplebar.js') }}"></script>
    <!-- sidebar-menu js -->
    <script src="{{ asset('vendor/assets/js/sidebar-menu.js') }}"></script>
    <!-- loader scripts -->
    {{-- <script src="{{ asset('vendor/assets/js/jquery.loading-indicator.js') }}"></script> --}}
    <!-- Custom scripts -->
    <script src="{{ asset('vendor/assets/js/app-script.js') }}"></script>
    <!-- Chart js -->
    <script src="{{ asset('vendor/assets/plugins/Chart.js/Chart.min.js') }}"></script>
    {{-- Datatables --}}
    <script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.10.24/r-2.2.7/datatables.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    @stack('scripts')

    <script src="{{ asset('vendor/sweetalert/sweetalert.all.js') }}"></script>
</body>

</html>