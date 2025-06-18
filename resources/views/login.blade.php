@extends('layouts.login.app')

@section('main-content')
<div class="container-fluid login-container px-0">
    <div class="row g-0">
        <div class="col-md-6 p-5 login-section-left">
            <div id="hero-img-container">
                <img src="{{asset('public/img/hero-img.png')}}" alt="A technician working on an office computer" id="tickets_hero_image" width="400" height="auto">
                <div class="office-floor-image-component"></div>
            </div>
        </div>
        <div class="col-md-6 login-section-right">
            <div class="mt-4 mb-2 p-2 text-center">
                <img src="{{asset('public/img/login_logo.png')}}" alt="Main Logo" id="login_page_logo" width="250" height="auto">
            </div>
            <div class="login-form-container">
                <h2 class="mb-5 p-2 text-center" id="loginFormHeading">Login</h2>
                @if(session('message'))
                <div class="alert alert-danger alert-dismissible mb-3" role="alert">
                    <button class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    {{session('message')}}
                </div>
                @endif
                @if ($errors->any())
                <div class="alert alert-danger alert-dismissible mb-3">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                <form action="" method="post" accept-charset="utf-8">
                    @csrf
                    <div class="row form-input-container mb-5">
                        <div class="col-sm-12 form-group pos-relative">
                            <input type="text" name="login_id" id="login_id" class="form-control login-form-input" required>
                            <span class="login-label initial">Login ID</span>
                        </div>
                    </div>
                    <div class="row form-input-container mb-4">
                        <div class="col-sm-12 form-group pos-relative">
                            <input type="password" name="password" id="password" class="form-control login-form-input" required>
                            <span class="login-label initial">Password</span>
                        </div>
                    </div>
                    <div class="row mt-5">
                        <div class="col-sm-12 text-center">
                            <button type="submit" class="" id="ticketsLoginBtn">Sign in</button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

@endsection

@section('js-content')
<script>
    $(document).ready(function() {
        $('.tickets_login_form_label').css('visibility', 'hidden');

        $('.login-form-input').on('focusin', function() {
            $(this).closest('.form-input-container').find('.login-label').removeClass('initial').addClass('input-filled');
        });

        $('.login-form-input').on('focusout', function() {
            if (!$(this).val())
                $(this).closest('.form-input-container').find('.login-label').removeClass('input-filled').addClass('initial');
        });
    });
</script>
@endsection