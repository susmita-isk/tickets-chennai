<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{csrf_token()}}">
    <title>@yield('page-title') - Tickets</title>

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

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{asset('public/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{asset('public/plugins/datatables-responsive/css/responsive.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{asset('public/plugins/datatables-buttons/css/buttons.bootstrap4.min.css')}}">

    <!-- Tempus Dominus - Bootstrap 4 DateTime Picker CSS -->
    <link rel="stylesheet"
        href="{{asset('public/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css')}}">

    <!-- Date Range Picker CSS -->
    <link rel="stylesheet" href="{{asset('public/plugins/daterangepicker/daterangepicker.css')}}">

    <!-- Toastr Alerts CSS -->
    <link rel="stylesheet" href="{{asset('public/plugins/toastr/toastr.min.css')}}">

    <!-- Select 2 CSS -->
    <link rel="stylesheet" href="{{asset('public/plugins/select2/css/select2.min.css')}}">
    <link rel="stylesheet" href="{{asset('public/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css')}}">

    <!-- Theme style -->
    <link rel="stylesheet" href="{{asset('public/dist/css/adminlte.min.css')}}">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{asset('public/dist/css/custom.css')}}">
    <!-- Other styles by page -->

    <!-- Toast -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/css/iziToast.min.css" />

    <style>
    .main-footer {
        position: fixed;
        bottom: 0;
        width: 100%;
        margin-top: 150px;
        z-index: 9999999999;
        padding: 12px 47px;
    }

    .content-wrapper>.content {
        padding-bottom: 6.5rem !important;
    }

    .dropdown-menu {
        min-width: 13rem;
    }

    .changePwBtn {
        font-size: 16px;
        padding: 4px 20px;
        border: 1px solid transparent;
    }

    /* Tooltip container style */
    .custom-tooltip {
        display: none;
        /* Initially hide the tooltip */
        position: absolute;
        background-color: rgba(0, 0, 0, 0.7);
        /* Dark background */
        color: #fff;
        /* White text */
        padding: 10px;
        border-radius: 5px;
        font-size: 12px;
        z-index: 1000;
    }

    /* Show the tooltip when hovering over the star */
    .star-icon:hover+.custom-tooltip {
        display: block;
        /* Show the tooltip on hover */
    }

    /* Position the tooltip */
    .star-icon {
        position: relative;
    }

    .custom-tooltip {
        position: absolute;
        top: 100%;
        /* Position below the star */
        left: 0;
        margin-top: 5px;
        white-space: nowrap;
        /* Prevent text wrapping */
    }
    </style>

    @yield('css-content')
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-light tkts-navbar-wrapper">
            <!-- Default Left Navbar from AdminLTE, customized -->
            <ul class="navbar-nav tickets-header-navbar w-100">
                <li class="nav-item tickets-header-nav-item">
                    <a class="nav-link d-inline-block" id="ticketsHamburgerMenuBtn" data-widget="pushmenu" href="#"
                        role="button">
                        <i class="fas fa-bars"></i>
                    </a>
                    <img src="{{asset('public/img/header-logo.png')}}" id="tktsHeaderLogo" alt="Tickets Header Logo"
                        width="90" height="auto" style="display: none;">
                </li>

                <!-- Options for logged-in user -->
                <li class="nav-item tickets-header-nav-item dropdown">
                    <a href="#" class="nav-link d-flex align-items-center" data-toggle="dropdown">
                        <img src="{{asset('public/img/icons/assign-ticket.png')}}" width="24">
                        <span class="d-inline-block ml-2">{{ auth()->user()->USER_NAME }}</span>
                        <i class="fas fa-angle-down ml-2"></i>
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
                        <button class="btn tickets-action-btn-transparent changePwBtn" id="changePwBtn"
                            data-target="#changePwModal" data-toggle="modal">
                            <i class="fa fa-lock"></i>
                            <p class="mb-0 drop-text d-inline-block">Change Password</p>
                        </button>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </li>

            </ul>
        </nav>
        <!-- /. Navbar !-->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4 sidebar-tickets" id="tktsMainSidebarMenu">
            <!-- Brand Logo -->
            <a href="{{url('home')}}" class="brand-link d-block" id="ticketsSidebarLogoLink">
            </a>
            <nav class="mt-2 tickets-sidebar-menu">
                <ul class="nav nav-sidebar flex-column align-content-center">
                    <!-- <li class="nav-item">
                        <a href="{{url('dashboard')}}" class="nav-link">
                            <i class="nav-icon fas fa-chart-line fa-lg"></i> 
                            <span class="tickets-sidebar-link-text" style="font-size: 18px;">Dashboard</span>
                        </a>
                    </li> -->
                    @php
                    $List = getParentmenu();
                    @endphp
                    @foreach($List as $val)
                    @php
                    $userMenu = userMenu();
                    @endphp
                    @if(in_array($val->LINK_CODE,$userMenu))
                    <li class="nav-item">
                        <a href="@if($val->LINK_PAGE){{ route($val->LINK_PAGE) }}@endif" class="nav-link">
                            <i class="nav-icon {{$val->ICON}} "></i>
                            <span class="tickets-sidebar-link-text"
                                style="font-size: 18px;">{{$val->LINK_DISPLAY}}</span>
                        </a>
                    </li>
                    @endif
                    @endforeach
                </ul>
            </nav>
        </aside>
        <!-- End Main Sidebar Container -->

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="tickets-content-header-container">
                        <div class="row">
                            <div class="col-6">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item">
                                        <a href="{{url('home')}}">
                                            <img src="{{asset('public/img/icons/home.png')}}" alt="Home Page Icon"
                                                height="18" class="breadcrumb-header-img">
                                            &nbsp;Home
                                        </a>
                                    </li>
                                    <!-- <li class="breadcrumb-item active">Tickets</li> -->
                                    @yield('breadcrumb-menu')
                                </ol>
                            </div>
                            <div class="col-6">
                                @yield('total-page')
                            </div><!-- /.col -->
                        </div><!-- /.row -->
                    </div>
                </div><!-- /.container-fluid -->
            </div>
            <!-- /.content-header -->

            <!-- Main content of page -->
            <div class="content">
                @yield('page-content')
            </div>
            <!-- /. End main content of page -->
        </div>
        <!-- END Content Wrapper -->

        <!-- Main Footer -->
        <footer class="main-footer">
            <!-- To the right -->
            <div class="float-right d-none d-sm-inline">
                ISKCON Bangalore
            </div>
            <!-- Default to the left -->
            <span id="footer-year"><?= date('Y') ?></span> <span id="footerAppName">Tickets</span>
        </footer>
        <!-- END Main Footer -->

    </div>

    <!-- Change Password Modal -->
    <div class="modal tickets-modal fade" id="changePwModal">
        <div class="modal-dialog modal-sm">
            <div class="modal-content changePw-modal">
                <div class="modal-header text-center">Change Password</div>
                <div class="modal-body">
                    <div class="row">
                        <form action="{{ route('update.password') }}" method="POST" id="passwordChangeForm">
                            @csrf
                            <div class="col-md-12">
                                @if (session('status'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('status') }}
                                </div>
                                @elseif (session('error'))
                                <div class="alert alert-danger" role="alert">
                                    {{ session('error') }}
                                </div>
                                @endif

                                <div class="row justify-content-center">
                                    <div class="col-md-12 mb-3">
                                        <label for="oldPasswordInput" class="form-label">Old
                                            Password</label>
                                        <input name="old_password" type="password"
                                            class="form-control cus-form @error('old_password') is-invalid @enderror"
                                            id="oldPasswordInput" placeholder="Old Password">
                                        @error('old_password')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="newPasswordInput" class="form-label">New
                                            Password</label>
                                        <input name="new_password" type="password"
                                            class="form-control cus-form @error('new_password') is-invalid @enderror"
                                            id="newPasswordInput" placeholder="New Password">
                                        @error('new_password')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="confirmNewPasswordInput" class="form-label">Confirm New
                                            Password</label>
                                        <input name="new_password_confirmation" type="password"
                                            class="form-control cus-form" id="confirmNewPasswordInput"
                                            placeholder="Confirm New Password">
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit"
                                            class="btn btn-small rounded-pill btn-success pw-submit-btn">Submit</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- REQUIRED SCRIPTS -->

    <!-- jQuery -->
    <script src="{{asset('public/plugins/jquery/jquery.min.js')}}"></script>
    <!-- Bootstrap 4.6 -->
    <script src="{{asset('public/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>

    <!-- Moment JS -->
    <script src="{{asset('public/plugins/moment/moment.min.js')}}"></script>

    <!-- DateRangePicker -->
    <script src="{{asset('public/plugins/daterangepicker/daterangepicker.js')}}"></script>

    <!-- Tempus Dominus - Bootstrap DateTimePicker -->
    <script src="{{asset('public/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js')}}"></script>

    <!-- JQuery Validation Plugin -->
    <script src="{{asset('public/plugins/jquery-validation/jquery.validate.min.js')}}"></script>
    <script src="{{asset('public/plugins/jquery-validation/additional-methods.min.js')}}"></script>

    <!-- Toastr Alerts JS -->
    <script src="{{asset('public/plugins/toastr/toastr.min.js')}}"></script>

    <!-- Toast -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/js/iziToast.min.js"></script>

    <!-- DataTables JS -->
    <script src="{{asset('public/plugins/datatables/jquery.dataTables.min.js')}}"></script>

    <!-- Select2 JS -->
    <script src="{{asset('public/plugins/select2/js/select2.min.js')}}"></script>

    <!-- AdminLTE App -->
    <script src="{{asset('public/dist/js/adminlte.min.js')}}"></script>
    <!-- <script src="{{asset('public/dist/js/demo.js')}}"></script> -->

    {{-- For including any other custom JS files by page --}}
    <script>
    $(document).ready(function() {
        $('#ticketsHamburgerMenuBtn').click();

        $.fn.dataTable.ext.errMode = 'none';
    });
    $(document).ready(function($) {
        $("#passwordChangeForm").validate({
            rules: {
                old_password: {
                    required: true,
                },
                new_password: {
                    required: true,
                },
                new_password_confirmation: {
                    required: true,
                    equalTo: "#newPasswordInput"
                }
            },
            messages: {
                old_password: {
                    required: "Please Provide Old Password",
                },
                new_password: {
                    required: "Enter Your New Password",
                },
                new_password_confirmation: {
                    required: "Please confirm your new password",
                    equalTo: "Passwords do not match"
                }
            },
            submitHandler: function(form, ev) {
                ev.preventDefault();
                $('.pw-submit-btn').prop('disabled', true);
                $.ajax({
                    url: form.action,
                    method: form.method,
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                            'content') // Include CSRF token
                    },
                    data: $(form).serialize(),
                    success: function(data) {
                        if (data.error == false) {
                            iziToast.show({
                                title: 'Success',
                                position: 'topRight',
                                color: '#9cd5a9', // Set the color to your desired color
                                message: "Password changed successfully !",
                            });

                            $('.pw-submit-btn').prop('disabled', false);
                            $('#passwordChangeForm').find('input').val('');
                            // toastr.success(data.msg);

                            $('#changePwModal').modal('hide');
                        }
                    },
                    error: function(jqXHR, textStatus, err) {
                        iziToast.show({
                            title: 'Error',
                            message: "Old Password doesn't match !",
                            position: 'topRight',
                            color: '#f27474' // Error color
                        });
                        $('.pw-submit-btn').prop('disabled', false);
                    }
                });
            }
        });
    });
    </script>
    @yield('js-content')
</body>

</html>