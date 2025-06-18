<!-- @extends('layouts.main.app') -->

@section('page-title', 'Change Password')

@section('page-content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card cus_card">
                <div class="card-header change_pw_header">{{ __('Change Password') }}</div>

                <form action="{{ route('update.password') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card-body">
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
                                        <label for="oldPasswordInput" class="form-label form-group">Old Password</label>
                                        <input name="old_password" type="password"
                                            class="form-control cus-form @error('old_password') is-invalid @enderror"
                                            id="oldPasswordInput" placeholder="Old Password">
                                        @error('old_password')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="newPasswordInput" class="form-label form-group">New Password</label>
                                        <input name="new_password" type="password"
                                            class="form-control cus-form @error('new_password') is-invalid @enderror"
                                            id="newPasswordInput" placeholder="New Password">
                                        @error('new_password')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="confirmNewPasswordInput" class="form-label form-group">Confirm New
                                            Password</label>
                                        <input name="new_password_confirmation" type="password"
                                            class="form-control cus-form" id="confirmNewPasswordInput"
                                            placeholder="Confirm New Password">
                                    </div>
                                    <div class="col-md-4 card-footer">
                                        <button class="btn btn-small rounded-pill custom-btn">Submit</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js-content')
@endsection