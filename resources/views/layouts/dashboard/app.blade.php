<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <title>Home - Tickets</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    <!-- Google Font: Poppins and Roboto -->
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Roboto:ital,wght@0,400;0,700;1,400;1,700&display=swap"
        rel="stylesheet">



    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="{{asset('public/plugins/fontawesome-free/css/all.min.css')}}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{asset('public/dist/css/adminlte.min.css')}}">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{asset('public/dist/css/custom.css')}}">
    <!-- Other styles by page -->
    @yield('css-content')
</head>

<body>
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-light tkts-navbar-wrapper index-page-navbar-wrapper">
            <!-- Default Left Navbar from AdminLTE, customized -->
            <ul class="navbar-nav tickets-header-navbar w-100">
                <li class="nav-item tickets-header-nav-item">
                    <img src="{{asset('public/img/header-logo.png')}}" id="tktsHeaderLogo" alt="Tickets Header Logo"
                        width="90" height="auto">
                </li>

                <!-- Options for logged-in user -->
                <li class="nav-item tickets-header-nav-item dropdown">
                    <a href="#" class="nav-link" data-toggle="dropdown">
                        <i class="fas fa-user"></i> {{ auth()->user()->USER_NAME }}<i
                            class="ml-2 fas fa-chevron-down"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        {{-- <span class="dropdown-header">15 Notifications</span>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-envelope mr-2"></i> 4 new messages
                            <span class="float-right text-muted text-sm">3 mins</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-file mr-2"></i> 3 new reports
                            <span class="float-right text-muted text-sm">2 days</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a> --}}
                        <a class="btn btn-default btn-flat float-right btn-block logout-button" href="#"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fa fa-fw fa-power-off text-red"></i>
                            {{ __('Log out') }}
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </li>
            </ul>
        </nav>
        <!-- /. Navbar !-->

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper index-page-content-wrapper">
            <!-- Content Header (Page header) -->

            <!-- /.content-header -->

            <div class="content">
                @yield('page-content')
            </div>
        </div>
        <!-- END Content Wrapper -->

        <!-- Main Footer -->
        <footer class="main-footer index-page-footer">
            <!-- To the right -->
            <div class="float-right d-none d-sm-inline">
                ISKCON Bangalore
            </div>
            <!-- Default to the left -->
            <span id="footer-year">&copy; <?= date('Y') ?></span> <span id="footerAppName">Tickets</span>
        </footer>
        <!-- END Main Footer -->

    </div>

    <!-- REQUIRED SCRIPTS -->

    <!-- jQuery -->
    <!-- <script src="{{asset('public/plugins/jquery/jquery.min.js')}}"></script> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>



    <!-- Bootstrap 4.6 -->
    <script src="{{asset('public/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
    <!-- AdminLTE App -->
    <script src="{{asset('public/dist/js/adminlte.min.js')}}"></script>

    {{-- For including any other custom JS files by page --}}
    @yield('js-content')
</body>

</html>