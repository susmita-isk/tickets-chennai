@extends('layouts.main.app')

@section('page-title', 'Task Details')

@section('css-content')
<link rel="stylesheet" href="{{asset('public/dist/css/task_details.css')}}">
<style>
.sidebar-mini.sidebar-collapse .main-sidebar,
.sidebar-mini.sidebar-collapse .main-sidebar::before {
    margin-left: 0;
    width: 2.2rem;
}
</style>
@endsection

@section('breadcrumb-menu')
<li class="breadcrumb-item active">Tickets</li>
<li class="breadcrumb-item">Application Support</li>
<li class="breadcrumb-item">Request</li>
<li class="breadcrumb-item">230809288</li>
@endsection

@section('page-content')
<div class="container-fluid">
    <div id="detailsContainer">
        <div id="detailsLinkTop">
            <button class="nav-link tickets-tab-link active" id="ticketDetailsTab" type="button" aria-selected="true">
                <i class="fas fa-list"></i> Details
            </button>
        </div>
        <div id="taskDetailsContainer">
            <div id="taskDetailsHeader" class="mb-3">
                <h2 id="taskSubject" class="mb-2">

                </h2>
                <div> <span id="headerRequesterName"></span>
                    <span id="headerRequesterDesignation"></span>
                </div>
                <div>
                    <i class="fas fa-mobile-alt"></i> <span id="requesterMobile" class="mr-2"></span>
                    <i class="far fa-envelope"></i> <span id="requesterEmail"></span>
                </div>
            </div>
            <div id="taskDetailsCardsContainer">
                {{-- Details Cards --}}
                <div class="row">
                    <div class="col-sm-6 mb-3">
                        <div class="card task-details-card h-100">
                            <div class="card-header">
                                <h5 class="card-title">Assignment</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6 mb-3"><strong>Task Type</strong></div>
                                    <div class="col-6 mb-3">Request</div>
                                    <div class="col-6 mb-3"><strong>Task Sub Type</strong></div>
                                    <div class="col-6 mb-3">Standard</div>
                                    <div class="col-6 mb-3"><strong>Technician</strong></div>
                                    <div class="col-6 mb-3">Kanhu Chran Gouda</div>
                                    <div class="col-6 mb-3"><strong>Created On</strong></div>
                                    <div class="col-6 mb-3">03-Aug-2023 10:55 AM</div>
                                    <div class="col-6 mb-2"><strong>Due By</strong></div>
                                    <div class="col-6 mb-2">03-Aug-2023 10:55 PM</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <div class="card task-details-card h-100">
                            <div class="card-header">
                                <h5 class="card-title">Categorization</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6 mb-3"><strong>Category</strong></div>
                                    <div class="col-6 mb-3">Production</div>
                                    <div class="col-6 mb-3"><strong>Subcategory</strong></div>
                                    <div class="col-6 mb-3">Function Support</div>
                                    <div class="col-6 mb-3"><strong>Item</strong></div>
                                    <div class="col-6 mb-3">Online Donation (Website Module)</div>
                                    <div class="col-6 mb-3"><strong>Asset ID</strong></div>
                                    <div class="col-6 mb-3">??</div>
                                    <div class="col-6 mb-2"><strong>Amount</strong></div>
                                    <div class="col-6 mb-2">0.00</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Task Updates --}}
                <div id="updatesContainer" class="mt-4">
                    <h3 id="updatesHeader">Updates</h3>
                    <div class="row">
                        <div class="col-sm-5 mb-2">
                            <span id="taskIdForUpdates">235456781</span>
                        </div>
                    </div>
                    <div id="updatesBody">
                        <h4 class="update-for-detail">

                        </h4>
                        <div class="update-text">
                            <span class="update-technician-name">Kanhu Chran Gouda</span> on 09-Aug-2023 02:20 PM (In
                            Progress)
                        </div>
                        <p class="update-message text-small">We have credited the account.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Begin modals --}}

{{-- End modals --}}
@endsection

@section('js-content')
<script src="{{asset('public/dist/js/task_details.js')}}"></script>
@endsection