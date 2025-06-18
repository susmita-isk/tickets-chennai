<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <title>Login</title>

    <!-- Bootstrap 5.3 CSS -->
    <link rel="stylesheet" href="{{asset('public/lib/bootstrap/css/bootstrap.min.css')}}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&family=Roboto:wght@400;700&&family=Vidaloka&display=swap" rel="stylesheet">

    <!-- Custom Login Page CSS -->
    <link rel="stylesheet" href="{{asset('public/dist/css/login.css')}}">

    @yield('css-content')

    <!-- jQuery 3.7 -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

</head>

<body>
    @yield('main-content')


    <!-- Bootstrap 5.3 JS -->
    <script src="{{asset('public/lib/bootstrap/js/bootstrap.min.js')}}"></script>

    @yield('js-content')
</body>

</html>