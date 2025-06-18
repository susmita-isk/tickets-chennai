@extends('layouts.dashboard.app')

@section('css-content')
{{-- Any styles specific to current page --}}
<link rel="stylesheet" href="{{asset('public/dist/css/index.css')}}">
<style>
.sidebar-mini.sidebar-collapse .main-sidebar,
.sidebar-mini.sidebar-collapse .main-sidebar::before {
    margin-left: 0;
    width: 2.2rem;
}
</style>
@endsection

@section('page-content')

<div class="container-fluid" id="main-page-container">
    <div id="departments-container">
        <div class="row p-3">

            @foreach ($departments as $item)

            <div class="col-sm-4 mb-3">
                {{-- For IT tickets --}}
                <!-- For IT tickets -->
                <div class="card tickets-card" id="{{ strtolower($item['departmentCode']) }}_service_desk_card">
                    <div class="card-header">
                        <h5 class="card-title">{{ $item['name'] }}</h5>
                    </div>
                    <div class="card-body">

                        <div class="row">
                            <div class="col-6 pt-2">
                                <img src="{{asset('public/img/'.$item['departmentCode'].'.png')}}"
                                    class="d-block mx-auto" alt="" style="max-height:160px;">
                            </div>
                            <div class="col-6 pt-2">
                                <h2 class="mt-2 tickets-card-category-header">Service</h2>
                                <a href="{{ route('tickets',['id' => $item['departmentId']]) }}"
                                    class="btn tickets-card-link">Open</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @endforeach

        </div>
    </div>
</div>

@endsection

@section('js-content')
@endsection