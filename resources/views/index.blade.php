@extends('layouts.dashboard.app')

@section('css-content')
{{-- Any styles specific to current page --}}
<link rel="stylesheet" href="{{asset('public/dist/css/index.css')}}">

@endsection

@section('page-content')

<div class="container-fluid" id="main-page-container">
    <div id="departments-container">
        <div class="row p-3">
            <div class="col-sm-4 mb-3">
                {{-- For IT tickets --}}
                <!-- For IT tickets -->
                <div class="card tickets-card" id="it_service_desk_card">
                    <div class="card-header">
                        <h5 class="card-title"> IT (Service Desk)</h5>
                    </div>
                    <div class="card-body">

                        <div class="row">
                            <div class="col-6 pt-2">
                                <img src="{{asset('public/img/it-support.png')}}" class="d-block mx-auto" alt="" style="max-height:160px;">
                            </div>
                            <div class="col-6 pt-2">
                                <h2 class="mt-2 tickets-card-category-header">Service</h2>
                                <a href="{{url('tickets/dept', [1])}}" class="btn tickets-card-link">Open</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4 mb-3">
                {{-- For SW (Application) Support --}}
                <!-- For SW (Application) Support -->
                <div class="card tickets-card" id="sw_service_desk_card">
                    <div class="card-header">
                        <h5 class="card-title"> SW (Application Support)</h5>
                    </div>
                    <div class="card-body">

                        <div class="row">
                            <div class="col-6 pt-2">
                                <img src="{{asset('public/img/sw-support.png')}}" class="d-block mx-auto" alt="" style="max-height:160px;">
                            </div>
                            <div class="col-6 pt-2">
                                <h2 class="mt-2 tickets-card-category-header">Service</h2>
                                <a href="{{url('tickets')}}" class="btn tickets-card-link">Open</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4 mb-3">
                {{-- For AD (Agile Development) --}}
                <!-- For AD (Agile Development) -->
                <div class="card tickets-card" id="agile_service_desk_card">
                    <div class="card-header">
                        <h5 class="card-title"> AD (Agile Development)</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 pt-2">
                                <img src="{{asset('public/img/agile-development.png')}}" class="d-block mx-auto" alt="" style="max-height:160px;">
                            </div>
                            <div class="col-6 pt-2">
                                <h2 class="mt-2 tickets-card-category-header">Service</h2>
                                <a href="{{url('tickets/dept')}}" class="btn tickets-card-link">Open</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4 mb-3">
                {{-- For HR Support (Service desk) --}}
                <!-- For HR Support (Service desk) -->
                <div class="card tickets-card" id="hr_service_desk_card">
                    <div class="card-header">
                        <h5 class="card-title"> HR (HR Service Desk) </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 pt-2">
                                <img src="{{asset('public/img/hr-service-desk.png')}}" class="d-block mx-auto" alt="" style="max-height:160px;">
                            </div>
                            <div class="col-6 pt-2">
                                <h2 class="mt-2 tickets-card-category-header">Service</h2>
                                <a href="{{url('tickets', [1])}}" class="btn tickets-card-link">Open</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4 mb-3">
                {{-- For Facilities --}}
                <!-- For Facilities -->
                <div class="card tickets-card" id="fa_service_desk_card">
                    <div class="card-header">
                        <h5 class="card-title"> FA (Facility) </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 pt-2">
                                <img src="{{asset('public/img/facility.png')}}" class="d-block mx-auto" alt="" style="max-height:160px;">
                            </div>
                            <div class="col-6 pt-2">
                                <h2 class="mt-2 tickets-card-category-header">Service</h2>
                                <a href="{{url('tickets')}}" class="btn tickets-card-link">Open</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js-content')
@endsection