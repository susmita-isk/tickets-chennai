@extends('layouts.main.app')

@section('page-title', 'Tickets')

@section('css-content')
<!-- DataTables Buttons CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.7.1/css/buttons.dataTables.min.css">

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<!--Filepond -->
<link href="{{asset('public/dist/css/filepond.css')}}" rel="stylesheet" />
<link href="{{asset('public/dist/css/filepond-plugin-image-preview.css')}}" rel="stylesheet" />
<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.13.3/css/selectize.default.min.css">

<link rel="stylesheet" href="{{asset('public/dist/css/tickets.css')}}">

<style>
/* Custom table styling */
.table-container {
    margin-top: 20px;
}

table thead tr th {
    padding: 12px 10px !important;
}

.table-responsive {
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
}

.table th,
.table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.table th {
    background-color: #f8f9fa;
    font-weight: bold;
}

.table tbody tr:hover {
    background-color: #f4f4f4;
}

.text-font-0 {
    font-weight: 0 !important;
    font-size: 13px !important;
}

/* Pagination styling */
.pagination {
    margin-top: 20px;
    list-style: none;
    display: flex;
    justify-content: center;
    align-items: center;
}

.pagination li {
    margin: 0 5px;
    cursor: pointer;
}

.pagination li.active {
    background-color: #007bff;
    color: #fff;
    border-radius: 50%;
    padding: 8px;
}

.pagination li a {
    text-decoration: none;
    color: #007bff;
    transition: all 0.3s ease;
}

.pagination li a:hover {
    color: #0056b3;
}

.table-responsive {
    overflow-x: hidden;
}

.tickets-main-table {
    border: 1px solid #ccc;
    width: 100% !important;
}

.modal-body {
    max-height: 800px;
    /* Adjust max-height as needed */
    overflow-y: auto;
    /* Enable vertical scrolling */
}

.selectize-control.single .selectize-input,
.selectize-dropdown.single {
    border-radius: 18px;
    font-size: 0.875rem;
    box-shadow: 0px 0px 6px 0px #00000040;
    border-color: #b8b8b8;
}

.selectize-input {
    border-radius: 18px;
    font-size: 0.875rem;
    box-shadow: 0px 0px 6px 0px #00000040;
    border-color: #b8b8b8;
}

.inline-button-group {
    display: inline-block;
}

.inline-button-group button {
    margin-right: 5px;
    /* Adjust as needed for spacing */
}

.sidebar-mini.sidebar-collapse .main-sidebar,
.sidebar-mini.sidebar-collapse .main-sidebar::before {
    margin-left: 0;
    width: 2.2rem;
}
</style>

@endsection

@section('breadcrumb-menu')
<li class="breadcrumb-item active">Tickets</li>
<li class="breadcrumb-item">{{ $departmentName }}</li>
@endsection

@section('total-page')
<div class="float-right">
    Total Tickets
    <span class="badge badge-shadowed ml-1" id="totalTicketsBadge"></span>
</div>
@endsection

@section('page-content')

@php
$permission = permission();
@endphp
@if(in_array(Route::currentRouteName(),$permission))

<div class="container-fluid">
    <div class="row mt-2">
        <div class="col">

        </div>
    </div>
    <div class="" id="actionBtnsContainer">
        <button class="btn tickets-action-btn" id="ticketsTableClearFilterBtn" title="Filter Tickets">
            Clear Filter
        </button>
        <button class="btn tickets-action-btn" id="ticketsTableFilterBtn" data-target="#filterTicketsModal"
            data-toggle="modal" title="Filter Tickets">
            <img src="{{asset('public/img/icons/filter.png')}}" alt="">
        </button>
        <button class="btn tickets-action-btn-transparent" id="exportToExcelBtn" data-target="#exportToExcelModal"
            data-toggle="modal" style="display: none;" title="Export to Excel">
            <img src="{{asset('public/img/icons/excel.png')}}" alt="Export to Excel" height="24">
        </button>
        <button class="btn tickets-action-btn" id="tasksTableFilterBtn" data-target="#filterTasksModal"
            data-toggle="modal" style="display: none;" title="Filter Tasks">
            <img src="{{asset('public/img/icons/filter.png')}}" alt="">
        </button>
        <button class="btn tickets-action-btn" id="logTicketBtn" data-target="#logNewTicketModal" data-toggle="modal"
            title="Add New Ticket">
            <i class="fas fa-plus"></i> Add
        </button>

        <div class="custom-control custom-switch tickets-custom-switch" id="toggleContainer">
            <?php if (!(userRoleName() == 'User')): ?>
            <input type="checkbox" class="custom-control-input" id="ticketsTasksViewToggleBtn">
            <label class="custom-control-label tickets-custom-control-label" for="ticketsTasksViewToggleBtn"
                title="Toggle Tickets/Tasks View"></label>
            <?php endif; ?>
        </div>
    </div>
    <?php if (!(userRoleName() == 'Engineer')): ?>
    <div id="ticketView" class="">
        <ul class="nav nav-tabs tickets-nav-tabs" id="ticketsTablesTabMenu" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link tickets-tab-link active" id="tickets-main-tab-link" type="button"
                    data-toggle="tab" data-target="#ticketsMainTabPanel" aria-controls="ticketsMainTabPanel"
                    aria-selected="true">
                    <i class="fas fa-bars"></i> Tickets
                </button>
            </li>
        </ul>
        {{-- Begin Tab Content for Tickets Listing Table --}}
        <div class="tab-content">
            <div class="tab-pane tickets-tab-pane fade show active" id="ticketsMainTabPanel" role="tabpanel"
                aria-labelledby="tickets-main-tab-link">
                <div class="tickets-tab-pane-content">
                    <div class="row">
                        <div class="col">
                            <div class="table-container">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped tickets-main-table"
                                        id="ticketsAllTable">
                                        <thead>
                                            <tr>
                                                <th width="110px">Ticket Number</th>
                                                <th width="150px">Subject</th>
                                                <th width="10px"><i class="fas fa-link"></i></th>
                                                <th width="130px">Requester Name</th>
                                                <th width="150px">Requested On</th>
                                                <th width="50px">Department</th>
                                                <th width="100px">Assigned To</th>
                                                <th width="100px">Created By</th>
                                                <th width="30px">Progress</th>
                                                <th width="10px">Team</th>
                                                <th width="10px">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                    <div class="d-flex justify-content-center">
                                        <ul class="pagination mt-3" id="pagination">

                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- End Tab Content for Tickets Listing Table --}}
    </div>
    <?php endif; ?>
    <div id="taskView" class="" style="display:none;">
        <ul class="nav nav-tabs tickets-nav-tabs" id="tasksTablesTabMenu" role="tablist">
            
            <li class="nav-item" role="presentation">
                <button class="nav-link tickets-tab-link active" id="myTasksLink" type="button" data-toggle="tab"
                    data-target="#myTasksTableTab" aria-controls="myTasksTableTab" aria-selected="true">
                    <i class="fas fa-list"></i> My Tasks
                </button>
            </li>
            
            <?php if (!(userRoleName() == 'Engineer')): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link tickets-tab-link" id="allTasksLink" type="button" data-toggle="tab"
                    data-target="#allTasksTableTab" aria-controls="allTasksTableTab">
                    <i class="fas fa-list"></i> All Tasks
                </button>
            </li>
            <?php endif; ?>
            
        </ul>
        {{-- Begin Tab Content for Tasks Tables --}}
        <div class="tab-content">
            {{-- For My Tickets / Tasks --}}
            <div class="tab-pane tickets-tab-pane fade show active" id="myTasksTableTab">
                <div class="tickets-tab-pane-content">
                    <div class="row">
                        <div class="col">
                            <div class="table-container">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped tickets-main-table" id="myTasksTable"
                                        style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th width="110px">Ticket Number</th>
                                                <th width="150px">Subject</th>
                                                <th width="10px">Link </th>
                                                <th width="130px">Requester Name</th>
                                                <th width="150px">Requested On</th>
                                                <th width="50px">Department</th>
                                                <th width="100px">Assigned To</th>
                                                <th width="30px">Progress</th>
                                                <th width="10px">Team</th>
                                                <th width="10px">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Your table rows go here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            {{-- For All Tasks --}}
            <div class="tab-pane tickets-tab-pane fade allTaskTablePadding" id="allTasksTableTab">
                <div class="tickets-tab-pane-content">
                    <div class="row">
                        <div class="col">
                            <div class="table-container">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped tickets-main-table data-table"
                                        id="allTasksTable" style="width :100%">
                                        <thead>
                                            <tr>
                                                <th width="100px;">Ticket Number</th>
                                                <th>Link </th>
                                                <th>Created By</th>
                                                <th width="110px;">Requester Name</th>
                                                <th width="100px;">Requested On</th>
                                                <th width="10px;">Team</th>
                                                <th width="30px;">Department</th>
                                                <th style="display: none;">Team</th>
                                                <th style="display: none;">Call Mode</th>
                                                <th style="display: none;">Priority</th>
                                                <th width="150px;">Subject</th>
                                                <th style="display: none;">Description</th>
                                                <th width="80px;">Technician</th>
                                                <th width="15px;">Assigned On</th>
                                                <th style="display: none;">Ticket Type</th>
                                                <th style="display: none;">Pending Time</th>
                                                <th style="display: none;">Status</th>
                                                <th width="30px;">Progress</th>
                                                <th style="display: none;">Category</th>
                                                <th style="display: none;">Subcategory</th>
                                                <th style="display: none;">Item</th>
                                                <th style="display: none;">Item Type</th>
                                                <th style="display: none;">Asset ID</th>
                                                <th style="display: none;">Work Update</th>
                                                <th style="display: none;">Effort</th>
                                                <th style="display: none;">Cost</th>
                                                <th width="120px;">Resolved On</th>
                                                <th width="30px;">Age</th>
                                                <th width="30px;">Points</th>
                                                <th width="30px;">Action</th>
                                                <th style="display: none;">SLA Breach</th>

                                                <!-- <th style="display: none;">Full Subject</th> -->
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- End Tab Content for Tasks Tables --}}
    </div>
    
</div>

@else
<div class="box-body text-center mt-4">
    <div class="row">
        <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
            Oops! You don't have permission to access this page.
        </div>
    </div>
</div>
@endif

@endsection

<!-- BEGIN Modal dialogs with forms -->

<!-- Filter Tickets Modal -->
<div class="modal tickets-modal fade" id="filterTicketsModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filter</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-6 mb-2-5">
                        <input type="text" class="form-control" id="filterTicketNo" name="" placeholder="Ticket No">
                    </div>
                    <div class="col-sm-6 mb-2-5">
                        <?php if (!(userRoleName() == 'User')): ?>
                        <select id="userNameInput" name="userNameInput" placeholder="Requester Name"></select>
                        <?php else: ?>
                        <select id="userNameInput" name="userNameInput" placeholder="Requester Name" disabled>
                            <option value="{{ $userEmpId }}">{{ $userName }}</option>
                        </select>
                        <?php endif ?>
                    </div>
                    <div class="col-sm-6 mb-2-5">
                        <input type="text" class="form-control" id="filterTicketSubject" maxlength="100" name=""
                            placeholder="Subject">
                    </div>
                    <div class="col-sm-6 mb-2-5">
                        <input type="text" class="form-control" id="filterTicketAsset" name="" placeholder="Asset Id">
                    </div>
                    <div class="col-sm-6 mb-2-5">
                        <input type="text" class="form-control" id="filterTicketDesc" maxlength="100" name=""
                            placeholder="Description">
                    </div>
                    <div class="col-sm-6 mb-2-5">
                        <select name="teamfilter" id="teamfilter" class="form-control">
                            <option value="">Team</option>
                            @foreach ($teams as $item)
                            <option value="{{ $item['teamName'] }}">{{ $item['teamName'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-6 mb-2-5">
                        <select class="department" id="departmentInput" name="departmentInput"
                            placeholder="Searching Dept"></select>
                    </div>
                    <div class="col-sm-6 mb-2-5">
                        <select name="technicianInput" id="technicianInput" class="technician"></select>
                    </div>

                    <div class="col-sm-6 mb-2-5">
                        <select name="" id="modeInput" class="form-control">
                            <option value="">Select Mode</option>
                            <option value="eMail">eMail</option>
                            <option value="Phone">Phone</option>
                            <option value="Form">Form</option>
                            <option value="Event">Event</option>
                            <option value="Sprint">Sprint</option>
                            <option value="Web">Web</option>
                            <option value="App">App</option>
                        </select>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <select name="category" id="categoryFilter" class="form-control category">
                        </select>
                    </div>
                    <div class="col-sm-6 mb-2-5">
                        <input type="text" class="form-control datepicker" id="requestedFromInput"
                            name="requestedFromInput" placeholder="Requested On (From)">
                    </div>
                    <div class="col-sm-6 mb-3">
                        <select name="subcategory" id="subCategoryFilter" class="form-control subcategoryFilter">
                        </select>
                    </div>
                    <div class="col-sm-6 mb-2-5">
                        <input type="text" class="form-control datepicker" id="requestedToInput" name="requestedToInput"
                            placeholder="Requested On (To)">
                    </div>
                    <div class="col-sm-6 mb-3">
                        <select name="itemType" id="itemTypeFilter" class="form-control itemFilter">
                        </select>
                    </div>
                    <div class="col-sm-6 mb-2-5">
                        <select id="statusSelect" name="status" multiple>
                            @foreach($statuses as $val)
                            <option value="{{$val->STATUS}}">{{$val->STATUS}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <select name="item" id="itemNameFilter" class="form-control subitemFilter">
                        </select>
                    </div>
                    <div class="col-sm-6 mb-2-5">
                        <select id="progressSelect" name="progress" multiple>
                            <option value="">Choose Progress</option>
                        </select>
                    </div>
                    <div class="col-sm-6 mb-2-5">
                        <input type="text" class="form-control" id="createdBy" name="createdBy"
                            placeholder="Created By">
                    </div>

                    <div class="col text-right">
                        <button type="button" class="btn tickets-modal-submit-btn" id="filterBtnTasks">Apply</button>
                        <button type="reset" class="btn tickets-modal-submit-btn mr-2" id="clearBtnTasks">Clear
                            All</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- / Filter Tickets Modal-->

<!-- Add/Log Ticket Modal -->
<div class="modal tickets-modal fade" id="logNewTicketModal">
    <div class="modal-dialog  modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                Log a Ticket
                <button class="close" data-dismiss="modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="logNewTicketForm" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="ml-4 col-md-4">
                            <input type="radio" id="ticketRadioBtn" name="selectionType" value="ticketRadioBtn" checked>
                            <label for="ticketRadioBtn">Ticket</label><br>
                        </div>
                        <div class="col-md-2">
                            <input type="radio" id="templateRadioBtn" name="selectionType" value="templateRadioBtn">
                            <label for="templateRadioBtn">Template</label><br>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="log_ticket_user_name" class="mb-1">User</label>
                                        <?php if (!(userRoleName() == 'User')): ?>
                                        <select name="employeeId" id="employee"
                                            class="js-example-basic-single form-control"
                                            style="width: 350px; height : 200px;" required>
                                            <option value="">User Name</option>
                                        </select>
                                        <?php else: ?>
                                        <select id="selectedEmployeeId" name="selectedEmployee"
                                            class="js-example-basic-single form-control" disabled>
                                            <option value="{{ $userEmpId }}">{{ $userName }} ({{ $userEmpId }})</option>
                                        </select>
                                        <?php endif ?>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="log_ticket_team" class="mb-1">Team</label>
                                        <select name="teamName" id="log_ticket_team" class="form-control" required>
                                            <option value="">Select Team</option>
                                            @foreach ($teams as $item)
                                            <option value="{{ $item['teamName'] }}">{{ $item['teamName'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="user-info-card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-sm-8">
                                            <div class="user-info-item">
                                                <i class="fas fa-user"></i>
                                                <span id="employeeName" class="text-bold"></span>
                                            </div>
                                            <div class="user-info-item">
                                                <i class="fas fa-suitcase"></i>
                                                <span id="departmentName"></span>
                                            </div>
                                            <div class="user-info-item">
                                                <i class="fas fa-envelope"></i>
                                                <span id="emailId"></span>
                                            </div>
                                            <div class="user-info-item">
                                                <i class="fas fa-phone"></i>
                                                <span id="mobile"></span>
                                                <span id="code" class="d-none"></span>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <img id="user-photo"
                                                src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAV0lEQVR42mP8/vYtDwAB/8E+pxYAAAAASUVORK5CYII="
                                                alt="White Image" class="w-50 h-5">
                                        </div>

                                    </div>
                                    <!-- Add more information sections as needed -->
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="log_ticket_call_mode" class="mb-1">Call Mode</label>
                                <select name="mode" id="log_ticket_call_mode" class="form-control" required>
                                    <option value="">Select Mode</option>
                                    <option value="eMail">eMail</option>
                                    <option value="Phone">Phone</option>
                                    <option value="Form">Form</option>
                                    <option value="Event">Event</option>
                                    <option value="Sprint">Sprint</option>
                                    <option value="Web">Web</option>
                                    <option value="Engineer">Engineer</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="log_ticket_priority" class="mb-1">Priority</label>
                                <select name="priority" id="log_ticket_priority" class="form-control" required>
                                    <option value="">Select Priority</option>
                                    <option value="High">High</option>
                                    <option value="Medium">Medium</option>
                                    <option value="Low">Low</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="log_ticket_subject" class="mb-1">Subject</label>
                                <input type="text" name="subject" id="log_ticket_subject" maxlength="100"
                                    class="form-control" placeholder="[Dept] [Center] Issue or Request" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="log_ticket_description" class="mb-1">Description</label>
                                <textarea name="description" id="log_ticket_description" rows="4"
                                    class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="form-group">
                                <label for="attachment" class="mr-3">Attachment</label>
                                <input type="file" name="attached_files[]" id="attachment" multiple>
                            </div>
                        </div>
                    </div>

                    <div class="row ticketSection" style="display: block;">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="frequency" class="mb-1">Frequency</label>
                                    <select name="frequency" id="frequency" class="form-control">
                                        <option value="">Select Frequency</option>
                                        @foreach ($frequencies as $frequency)
                                        <option value="{{ $frequency }}" {{ $frequency == 'Once' ? 'selected' : '' }}>
                                            {{ $frequency }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4" id="weekday-section" style="display: none;">
                                    <label for="weekday" class="mb-1">Weekday</label>
                                    <select name="weekday" id="weekday" class="form-control">
                                        <option value="">Select Weekday</option>
                                        @foreach ($weekdays as $val)
                                        <option value="{{ $val->WEEKDAY }}">
                                            {{ $val->WEEKDAY }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4" id="monthly-section" style="display: none;">
                                    <label for="start_date" class="mb-1">Start Date</label>
                                    <input type="text" class="form-control datepicker" id="start_date" name="start_date"
                                        placeholder="Start Date (From)">
                                </div>
                                <div class="col-md-4 frequency-section" style="display: none;">
                                    <label for="recurring_till" class="mb-1">Recurring Till</label>
                                    <input type="text" class="form-control datepicker" id="recurring_till"
                                        name="recurring_till" placeholder="Recurring Till (To)">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row templateSection" style="display: none;">
                        <div class="col-6 mb-3 ">
                            <label for="templateName" class="mb-1">Template</label>
                            <select name="templateName" id="templateName" class="form-control">
                                <option value="">Select Template</option>
                                @foreach ($templates as $val)
                                <option value={{ $val->TEMPLATE_ID }}>{{ $val->TEMPLATE_NAME }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="add_subject_text" name="add_subject_text"
                                placeholder="Append Text" style="display:none;">
                        </div>
                        <div class="col-md-8 mt-1">
                            <h6 id="task-header" style="display:none;">Task List</h6>
                            <ul class="role-list" id="tasksSection">
                            </ul>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 mb-2 mt-4 text-center">
                            <button type="submit" class="btn tickets-modal-submit-btn" id="Logtckt">Log Ticket</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- / END Add/Log Ticket Modal -->

<!-- Edit Ticket Details Modal -->
<div class="modal tickets-modal fade" id="editTicketDetailsModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                Ticket No - <span id="ticketIdToEdit"></span>
                <button class="close" data-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="editTicketForm">
                    @csrf
                    <input type="hidden" name="ticketId" id="ticketId">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="log_ticket_user_name" class="mb-1">User</label>
                                        <select name="employeeId" id="employeeEdit"
                                            class="js-example-basic-single1 form-control"
                                            style="width: 350px; height : 200px;" placeholder="Username">
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="teamEdit" class="mb-1">Team</label>
                                        <select name="teamName" id="teamEdit" class="form-control" required>
                                            <option value="">Select Team</option>
                                            @foreach ($teams as $item)
                                            <option value="{{ $item['teamName'] }}">{{ $item['teamName'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="user-info-card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-sm-8">
                                            <div class="user-info-item">
                                                <i class="fas fa-user"></i>
                                                <span id="employeeNameEdit" class="text-bold"></span>
                                            </div>
                                            <div class="user-info-item">
                                                <i class="fas fa-suitcase"></i>
                                                <span id="departmentNameEdit"></span>
                                            </div>
                                            <div class="user-info-item">
                                                <i class="fas fa-envelope"></i>
                                                <span id="emailIdEdit"></span>
                                            </div>
                                            <div class="user-info-item">
                                                <i class="fas fa-phone"></i>
                                                <span id="mobileEdit"></span>
                                                <span id="code" class="d-none"></span>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <img id="user-photo-edit"
                                                src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAV0lEQVR42mP8/vYtDwAB/8E+pxYAAAAASUVORK5CYII="
                                                alt="White Image" class="w-50 h-5">
                                        </div>

                                    </div>
                                    <!-- Add more information sections as needed -->
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="mode" class="mb-1">Call Mode</label>
                                <select name="mode" id="modeEdit" class="form-control" required>
                                    <option value="">Select Mode</option>
                                    <option value="eMail">eMail</option>
                                    <option value="Phone">Phone</option>
                                    <option value="Form">Form</option>
                                    <option value="Event">Event</option>
                                    <option value="Sprint">Sprint</option>
                                    <option value="Web">Web</option>
                                    <option value="Engineer">Engineer</option>
                                    <option value="App">App</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="log_ticket_priority" class="mb-1">Priority</label>
                                <select name="priority" id="priorityEdit" class="form-control" required>
                                    <option value="">Select Priority</option>
                                    <option value="High">High</option>
                                    <option value="Medium">Medium</option>
                                    <option value="Low">Low</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="log_ticket_subject" class="mb-1">Subject</label>
                                <input type="text" name="subject" id="subjectEdit" class="form-control"
                                    placeholder="[Dept] [Center] Issue or Request" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="log_ticket_description" class="mb-1">Description</label>
                                <textarea name="description" id="descriptionEdit" rows="4"
                                    class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="" class="mr-3">Added Attachments</label>
                                <div id="attachmentContainer">

                                </div>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="form-group">
                                <label for="attachment" class="mr-3">New Attachments</label>
                                <input type="file" name="attached_files_update[]" id="attachment-update"
                                    class="my-pond-update" multiple>
                            </div>
                        </div>
                        <div class="col-12 mb-2 text-center">
                            <button class="btn tickets-modal-submit-btn">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- / END Edit Ticket Details Modal -->

<!-- Assign Ticket Modal -->
<div class="modal tickets-modal fade" id="assignTicketModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                Assign Ticket - <span id="ticketIdToAssign"></span>
                <button class="close" data-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="assignTicketForm">
                    @csrf
                    <input type="hidden" name="ticketId" id="ticketIdForAssignment">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="teamName">Team</label>
                                <select name="teamName" id="teamName" class="form-control" required>
                                    <option value="">Please Select</option>
                                    @foreach ($teams as $item)
                                    <option value={{ $item['teamId'] }}>{{ $item['teamName'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="assign_ticket_task_type">Category</label>
                                <select id="categoryAssign" name="categoryId" class="form-control"
                                    placeholder="Select Category">
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="taskType">Ticket Type</label>
                                <select name="taskType" id="taskType" class="form-control" required>
                                    <option value="">Please Select</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="assign_ticket_sub_type">Subcategory</label>
                                <select id="subCategoryAssign" name="subcategoryId" class="form-control">

                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="assign_ticket_technician">Technician</label>
                                <select id="technicianAssign" name="technicianId" class="form-control" required>
                                    <option value="">Select Technician</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="assign_ticket_team">Item Type</label>
                                        <select id="itemTypeAssign" name="itemTypeId" class="form-control">

                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="assign_ticket_team">Item</label>
                                        <select id="itemAssign" name="itemId" class="form-control">

                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3"></div>
                        <div class="col-12 text-center mt-3">
                            <button type="submit" class="btn tickets-modal-submit-btn" id="assignBtn">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- END Assign Ticket Modal -->

<!-- Filter Tasks Modal -->
<div class="modal tickets-modal fade" id="filterTasksModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">Filter
                <button class="close" data-dismiss="modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-6 mb-3">
                        <input type="text" class="form-control" id="filterTicketId" placeholder="Ticket No"
                            name="ticketId">
                    </div>
                    <div class="col-sm-6 mb-3">
                        <select id="filterTaskRequestName" class="form-control employeeFilter"
                            placeholder="Requester Name">
                        </select>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <input type="text" class="form-control" id="filterTasksSubject" name="filterTasksSubject"
                            placeholder="Subject">
                    </div>
                    <div class="col-sm-6 mb-3">
                        <input type="text" class="form-control" id="filterTasksAsset" name="filterTasksAsset"
                            placeholder="Asset Id">
                    </div>
                    <div class="col-sm-6 mb-3">
                        <input type="text" class="form-control" id="filterTasksDesc" name="filterTasksDesc"
                            placeholder="Description">
                    </div>
                    <div class="col-sm-6 mb-2-5">
                        <select name="teamfilterTasks" id="teamfilterTasks" multiple>
                            <option value="">Team</option>
                            @foreach ($teams as $item)
                            <option value="{{ $item['teamName'] }}">{{ $item['teamName'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 mb-2-5">
                        <select class="department" id="filterTasksdepartmentInput" name="filterTasksdepartmentInput"
                            placeholder="Searching Dept"></select>
                    </div>
                    <div class="col-sm-6 mb-2-5">
                        <select name="technicianTasks" id="technicianTasks" class="technician">
                        </select>
                    </div>
                    <div class="col-sm-6 mb-2-5">
                        <select name="filterTasksMode" id="filterTasksMode" class="form-control">
                            <option value="">Select Mode</option>
                            <option value="eMail">eMail</option>
                            <option value="Phone">Phone</option>
                            <option value="Form">Form</option>
                            <option value="Event">Event</option>
                            <option value="Sprint">Sprint</option>
                            <option value="Web">Web</option>
                            <option value="App">App</option>
                        </select>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <select name="category" id="filterTaskCategory" class="form-control category">
                        </select>
                    </div>
                    <div class="col-sm-6 mb-2-5">
                        <input type="text" class="form-control datepicker" id="filterTasksrequestedFromInput"
                            name="filterTasksrequestedFromInput" placeholder="Requested On (From)">
                    </div>
                    <div class="col-sm-6 mb-3">
                        <select name="subcategory" id="filterTaskSubCategory" class="form-control subcategoryFilter">
                        </select>
                    </div>
                    <div class="col-sm-6 mb-2-5">
                        <input type="text" class="form-control datepicker" id="filterTasksrequestedToInput"
                            name="filterTasksrequestedToInput" placeholder="Requested On (To)">
                    </div>
                    <div class="col-sm-6 mb-3">
                        <select name="itemType" id="filterTaskItemType" class="form-control itemFilter">
                        </select>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <select id="statusTaskSelect" name="status" multiple>
                            <option value="">Choose Status</option>
                            <option value="New">New</option>
                            <option value="Open">Open</option>
                            <option value="Completed">Completed</option>
                            <option value="Closed">Closed</option>
                        </select>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <select name="item" id="filterTaskItem" class="form-control subitemFilter">
                        </select>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <select id="progressTaskSelect" name="progress" multiple>
                            <option value="">Choose Progress</option>
                        </select>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <input type="text" class="form-control" id="filterTasksCreatedBy" name="filterTasksCreatedBy"
                            placeholder="Created By">
                    </div>
                    <div class="col-12 mb-2 text-right">
                        <button type="button" class="btn tickets-modal-submit-btn" id="filterTaskBtn">Apply</button>
                        <button type="reset" class="btn tickets-modal-submit-btn mr-2" id="resetTaskBtn">Clear
                            All</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END Filter Tasks Modal -->

<!-- Modal for Work Updates -->
<div class="modal fade tickets-modal" id="workUpdatesModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                Work updates for : &nbsp; <span id="workUpdateTaskId"> </span>
                <button class="close" data-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="workUpdatesForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" class="form-control" id="ticketIdStatus" name="ticketId"
                        placeholder="Ticket No">
                    <div class="row">
                        <div class="col-12" id="statusUpdateDiv" style="display: none;">
                            <div class="form-group">
                                <label for="work_update_status" class="mb-1">Status</label>
                                <select name="status" id="work_update_status" class="form-control"
                                    onchange="checkStatus()">
                                    <option value="">Choose Status</option>
                                    @foreach($openProgresses as $val)
                                    <option value="{{$val->PROGRESS}}" data-transferred="{{ $val->IS_TRANSFERRED }}" >{{$val->PROGRESS}}</option>
                                    @endforeach
                                    <!-- <option value="On Hold">On Hold</option>
                                    <option value="Release">Release</option> -->
                                </select>
                            </div>
                        </div>
                        <div class="col-12" id="onHoldReason" style="display: none;">
                            <div class="form-group">
                                <label for="work_update" class="mb-1">Reason</label>
                                <select class="form-control" id="onhold" name="onholdReason">
                                    <option value="">Choose Reason</option>
                                    <option value="Approval Pending">Approval Pending</option>
                                    <option value="User Not In Station">User Not In Station</option>
                                    <option value="User Not Reachable">User Not Reachable</option>
                                    <option value="Internal Dependency">Internal Dependency</option>
                                    <option value="External Dependency">External Dependency</option>
                                    <option value="Under Observation">Under Observation</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12" id="teamSelectionDiv" style="display: none;">
                            <div class="form-group">
                                <label for="statusUpdateTteamName">Team</label>
                                <select name="statusUpdateTteamName" id="statusUpdateTteamName" class="form-control">
                                    <option value="">Please Select</option>
                                    @foreach ($teams as $item)
                                    <option value={{ $item['teamId'] }}>{{ $item['teamName'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="assign_ticket_technician">Technician</label>
                                <select id="statusUpdateTechnician" name="statusUpdateTechnician" class="form-control">
                                    <option value="">Select Technician</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <textarea name="remarks" id="work_update_remarks" class="form-control" rows="4"
                                    placeholder="Remarks..." required></textarea>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="work_update_file_upload">Upload File</label>
                                <input type="file" name="file" id="work_update_file_upload">
                            </div>
                        </div>

                        <div class="col-12 mb-3 text-center">
                            <button type="submit" id="workUpdateBtn"
                                class="btn tickets-modal-submit-btn">Submit</button>
                        </div>
                    </div>
                </form>
                <!-- For work update history cards -->
                <div id="workUpdateHistoryContainer"></div>
            </div>
        </div>
    </div>
</div>
<!-- END Modal for Work Updates -->

<!-- Modal for Categorizing Tasks -->
<div class="modal fade tickets-modal" id="categorizeTaskModal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                Categorize - <span id="categorizeTaskId"></span>
                <button class="close" data-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="categorizeTaskForm">
                    @csrf
                    <input type="hidden" class="form-control" id="ticketIdCategorize" name="ticketId">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="gorm-group">
                                <label for="categorize_task_category" class="control-label mb-1">Category</label>
                                <select name="category" id="categoryCategorize" class="form-control ">
                                    <option value="">Select a Category</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="categorize_task_sub_category" class="control-label mb-1">Subcategory</label>
                                <select name="subcategory" id="subCategoryCategorize" class="form-control">
                                    <option value="">Select a Subcategory</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="categorize_task_item" class="control-label mb-1">Item Type</label>
                                <select name="itemType" id="itemTypeCategorize" class="form-control">
                                    <option value="">Select a Item Type</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="categorize_task_item_type" class="control-label mb-1">Item</label>
                                <select name="item" id="itemCategorize" class="form-control">
                                    <option value="">Select a Item</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group" id="assetIdField">
                                <!-- <label for="assetIdCategorize" class="control-label mb-1">Asset ID</label>
                                <input type="text" name="assetId" id="assetIdCategorize" class="form-control"
                                    placeholder="Enter Asset ID"> -->
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3"></div>
                        <div class="col-sm-12 mb-3 text-center">
                            <button type="submit" class="btn tickets-modal-submit-btn">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- END Modal for Categorizing Tasks -->


<!-- Modal for Closing Tasks -->
<div class="modal fade tickets-modal" id="closeTaskModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                Close Task - <span id="taskIdToClose"></span>
                <button class="close" data-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="closeTaskForm">
                    @csrf
                    <input type="hidden" class="form-control" id="ticketIdClose" name="ticketId">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="close_task_status" class="mb-1">Status</label>
                                <select class="form-control" id="statusCategorize" name="status" required>
                                    <option value="">Choose Status</option>
                                    @foreach($progresses as $val)
                                     <option value="{{$val->PROGRESS}}">{{$val->PROGRESS}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="close_task_remarks" class="mb-1">Remarks</label>
                                <textarea name="remarks" id="close_task_remarks" rows="4" class="form-control"
                                    required></textarea>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="close_task_effort" class="mb-1">Effort</label>
                                <input type="number" name="effort" id="close_task_effort" class="form-control"
                                    placeholder="Minutes" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="close_task_cost" class="mb-1">Cost</label>
                                <input type="number" class="form-control" id="close_task_cost" name="cost">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="close_task_file">Upload File</label>
                                <input type="file" name="file" id="close_task_file">
                            </div>
                        </div>
                        <div class="col-12 text-center mb-2">
                            <button type="submit" id="closeTaskBtId"
                                class="btn tickets-modal-submit-btn">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- END Modal for Closing Tasks -->

<!-- Modal for Cancel Tasks -->
<div class="modal fade tickets-modal" id="cancelTaskModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                Cancel Task - <span id="taskIdToCancel"></span>
                <button class="close" data-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="cancelTaskForm">
                    @csrf
                    <input type="hidden" class="form-control" id="ticketIdCancel" name="ticketId">
                    <div class="row">
                        <div class="col-12" style="display:none;">
                            <div class="form-group">
                                <label for="close_task_status" class="mb-1">Status</label>
                                <select class="form-control" id="statusCancel" name="status" required>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="close_task_remarks" class="mb-1">Remarks</label>
                                <textarea name="remarks" id="cancel_task_remarks" rows="4" class="form-control"
                                    required></textarea>
                            </div>
                        </div>
                        <div class="col-12 text-center mb-2">
                            <button type="submit" id="cancelTaskBtId"
                                class="btn tickets-modal-submit-btn">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- END Modal for Cancel Tasks -->

<!-- Modal for Release Tickets -->
<div class="modal fade tickets-modal" id="releaseTicketsModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                Release Ticket - <span id="ticketNoRelease"></span>
                <button class="close" data-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="releaseTicketsForm">
                    @csrf
                    <input type="hidden" class="form-control" id="ticketIdRelease" name="ticketId">
                    <div class="row">
                        <div class="col-12" style="display:none;">
                            <div class="form-group">
                                <label for="close_task_status" class="mb-1">Status</label>
                                <select class="form-control" id="releaseStatus" name="status">
                                    <option value="Release">Release</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="close_task_remarks" class="mb-1">Remarks</label>
                                <textarea name="remarks" id="release_ticket_remarks" rows="4" class="form-control"
                                    required></textarea>
                            </div>
                        </div>
                        <div class="col-12 text-center mb-2">
                            <button type="submit" id="releaseTicketsBtn"
                                class="btn tickets-modal-submit-btn">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- END Modal for Release Tickets -->

<!-- Modal for Reopening the Tasks -->
<div class="modal fade tickets-modal" id="reopenTaskModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                Reopen Task - <span id="taskIdToreopen"></span>
                <button class="close" data-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="reopenTaskForm">
                    @csrf
                    <input type="hidden" class="form-control" id="ticketIdreopen" name="ticketId">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="reopen_task_status" class="mb-1">Status</label>
                                <select class="form-control" id="statusReopen" name="status" required>
                                    <option value="Reopened">Reopened</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="reopen_task_remarks" class="mb-1">Remarks</label>
                                <textarea name="remarks" id="reopen_task_remarks" rows="4" class="form-control"
                                    required></textarea>
                            </div>
                        </div>
                        <div class="col-12 text-center mb-2">
                            <button type="submit" class="btn tickets-modal-submit-btn">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- END Modal for Reopening the Tasks -->

<!-- Begin Modal for Excel Export (All Tasks) -->
<div class="modal tickets-modal fade" id="exportToExcelModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                Excel <button class="close" data-dismiss="modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <input type="text" class="form-control datepicker" id="excelTicketFromDate"
                                name="excelFromDate" placeholder="From Date (DD-MM-YY)">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <input type="text" class="form-control datepicker" id="excelTicketToDate" name="excelToDate"
                                placeholder="To Date (DD-MM-YY)">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <select id="excelTicketStatus" name="excelStatus" multiple>
                                <option value="">Choose Status</option>
                                <option value="New">New</option>
                                <option value="Open">Open</option>
                                <option value="Completed">Completed</option>
                                <option value="Closed">Closed</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <select id="excelTicketProgress" name="excelProgress" multiple>
                                <option value="">Choose Progress</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 text-center mb-2">
                        <button type="button" class="btn tickets-modal-submit-btn mr-2"
                            id="excelExportBtn">Submit</button>
                        <button type="button" class="btn tickets-modal-submit-btn"
                            id="excelExportBtnClear">Clear</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Begin Modal for Excel Export (All Tasks) -->

<!-- Modal -->
<div class="modal fade" id="attachmentModal" tabindex="-1" role="dialog" aria-labelledby="attachmentModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attachmentModalLabel">Attachment Preview</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="file" class="filepond" multiple />
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- END Modal dialogs with forms -->


@section('js-content')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!--Filepond -->
<script src="{{asset('public/dist/js/filepond-plugin-image-preview.js')}}"></script>
<script src="{{asset('public/dist/js/filepond-plugin-file-validate-type.js')}}"></script>
<script src="{{asset('public/dist/js/filepond.min.js')}}"></script>
<script src="{{asset('public/dist/js/filepond.jquery.js')}}"></script>
<!-- Flatpickr JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<!-- Viewer.js JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.10.1/viewer.min.js" crossorigin="anonymous"></script>
<!-- DataTables Buttons JS -->
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.13.3/js/standalone/selectize.min.js"></script>
<script>
const ticketDetailsURL = '{{url("tickets/ticket")}}';
const taskDetailsURL = '{{url("tickets/task")}}';
</script>
<script src="{{asset('public/dist/js/tickets.js')}}"></script>
<script>
$(function() {

    // When the filter button is clicked
    $('#ticketsTableClearFilterBtn').click(function() {
        // Trigger the click event of the clear button
        $('#clearBtnTasks').click();
    });

    // Clear the file input when the modal is hidden
    $('#logNewTicketModal').on('hidden.bs.modal', function() {
        $('#attachment').val('');
    });

    // Clear the file input when the modal is shown
    $('#logNewTicketModal').on('shown.bs.modal', function() {
        $('#attachment').val('');
    });

    // Clear the file input when the modal is hidden
    $('#editTicketDetailsModal').on('hidden.bs.modal', function() {
        $('#attachment-update').val('');
    });

    // Clear the file input when the modal is shown
    $('#editTicketDetailsModal').on('shown.bs.modal', function() {
        $('#attachment-update').val('');
    });


    $('[data-toggle="tooltip"]').tooltip();

    $('#technicianInput').selectize({
        plugins: ['remove_button'],
        delimiter: ',',
        persist: false,
        create: false,
        valueField: 'id',
        labelField: 'text',
        searchField: ['text'],
        placeholder: 'Technician Name',

        // Append the processed results to the dropdown
        onInitialize: function() {
            var selectize = this;
            $.ajax({
                url: '{{ route("technicians.get") }}',
                type: 'GET',
                dataType: 'json',
                data: {
                    type: 'public'
                },
                success: function(res) {
                    // Clear existing options
                    selectize.clearOptions();

                    // Add new options from the response data
                    for (var i = 0; i < res.length; i++) {
                        selectize.addOption({
                            id: res[i]
                                .EMPLOYEE_ID, // Adjust with your actual technician ID column
                            text: res[i]
                                .USER_NAME // Adjust with your actual technician name column
                        });
                    }

                    // Refresh options to reflect the changes
                    selectize.refreshOptions();
                }
            });
        }
    });

    $('#technicianTasks').selectize({
        plugins: ['remove_button'],
        delimiter: ',',
        persist: false,
        create: false,
        valueField: 'id',
        labelField: 'text',
        searchField: ['text'],
        placeholder: 'Technician Name',

        // Append the processed results to the dropdown
        onInitialize: function() {
            var selectize = this;
            $.ajax({
                url: '{{ route("technicians.get") }}',
                type: 'GET',
                dataType: 'json',
                data: {
                    type: 'public'
                },
                success: function(res) {
                    // Clear existing options
                    selectize.clearOptions();

                    // Add new options from the response data
                    for (var i = 0; i < res.length; i++) {
                        selectize.addOption({
                            id: res[i]
                                .EMPLOYEE_ID, // Adjust with your actual technician ID column
                            text: res[i]
                                .USER_NAME // Adjust with your actual technician name column
                        });
                    }

                    // Refresh options to reflect the changes
                    selectize.refreshOptions();
                }
            });
        }
    });

    $('#departmentInput').selectize({
        plugins: ['remove_button'],
        delimiter: ',',
        persist: false,
        create: false,
        valueField: 'id',
        labelField: 'text',
        searchField: ['text'],
        placeholder: 'Department Name',
        // Append the processed results to the dropdown
        onInitialize: function() {
            var selectize = this;
            $.ajax({
                url: '{{ route("departments.get") }}',
                type: 'GET',
                dataType: 'json',
                data: {
                    type: 'public'
                },
                success: function(res) {
                    // Clear existing options
                    selectize.clearOptions();

                    // Add new options from the response data
                    for (var i = 0; i < res.length; i++) {
                        selectize.addOption({
                            id: res[i]
                                .deptCode, // Adjust with your actual technician ID column
                            text: res[i]
                                .deptName // Adjust with your actual technician name column
                        });
                    }

                    // Refresh options to reflect the changes
                    selectize.refreshOptions();
                }
            });
        }
    });

    $('#filterTasksdepartmentInput').selectize({
        plugins: ['remove_button'],
        delimiter: ',',
        persist: false,
        create: false,
        valueField: 'id',
        labelField: 'text',
        searchField: ['text'],
        placeholder: 'Department Name',
        // Append the processed results to the dropdown
        onInitialize: function() {
            var selectize = this;
            $.ajax({
                url: '{{ route("departments.get") }}',
                type: 'GET',
                dataType: 'json',
                data: {
                    type: 'public'
                },
                success: function(res) {
                    // Clear existing options
                    selectize.clearOptions();

                    // Add new options from the response data
                    for (var i = 0; i < res.length; i++) {
                        selectize.addOption({
                            id: res[i]
                                .deptCode, // Adjust with your actual technician ID column
                            text: res[i]
                                .deptName // Adjust with your actual technician name column
                        });
                    }

                    // Refresh options to reflect the changes
                    selectize.refreshOptions();
                }
            });
        }
    });

    var userName = '{{ $userName }}';
    var userEmpId = '{{ $userEmpId }}';

    $('#userNameInput').selectize({
        plugins: ['remove_button'],
        delimiter: ',',
        persist: false,
        create: false,
        valueField: 'id',
        labelField: 'text',
        searchField: ['text'],
        placeholder: 'Requester Name',
        // Append the processed results to the dropdown
        onInitialize: function() {
            var selectize = this;
            $.ajax({
                url: '{{ route("employees.get") }}',
                type: 'GET',
                dataType: 'json',
                data: {
                    type: 'public'
                },
                success: function(res) {
                    // Clear existing options
                    selectize.clearOptions();

                    // Add new options from the response data
                    for (var i = 0; i < res.length; i++) {
                        selectize.addOption({
                            id: res[i]
                                .hrEmployeeID, // Adjust with your actual technician ID column
                            text: res[i]
                                .employeeName // Adjust with your actual technician name column
                        });
                    }
                    // Check if userName is provided (for non-admin users)
                    if (userName != '') {
                        // Set userName as the selected option
                        selectize.addOption({
                            id: userName,
                            text: userName
                        });
                        selectize.setValue(userEmpId); // Set the selected value
                    }

                    // Refresh options to reflect the changes
                    selectize.refreshOptions();
                }
            });
        }
    });


    $('#statusSelect').selectize({
        plugins: ['remove_button'],
        delimiter: ',',
        persist: false,
        create: false,
        valueField: 'id',
        labelField: 'text',
        searchField: ['text'],
        placeholder: 'Choose Status',
    });
    $('#statusTaskSelect').selectize({
        plugins: ['remove_button'],
        delimiter: ',',
        persist: false,
        create: false,
        valueField: 'id',
        labelField: 'text',
        searchField: ['text'],
        placeholder: 'Choose Status',
    });
    $('#teamfilterTasks').selectize({
        plugins: ['remove_button'],
        delimiter: ',',
        persist: false,
        create: false,
        valueField: 'id',
        labelField: 'text',
        searchField: ['text'],
        placeholder: 'Choose Team',
    });

    $('#excelTicketStatus').selectize({
        plugins: ['remove_button'],
        delimiter: ',',
        persist: false,
        create: false,
        valueField: 'id',
        labelField: 'text',
        searchField: ['text'],
        placeholder: 'Choose Status',

    });

    // Initialize Flatpickr with dateFormat option
    datepicker = flatpickr("#datepicker", {
        dateFormat: "d-M-Y",
        minDate: "today"
    });

    flatpickr(".datepicker", {
        dateFormat: "d-M-Y"
    });

    var from_date = $("#excelTicketFromDate").flatpickr({
        dateFormat: 'd-M-Y', // Correct Flatpickr format
        onChange: function(selectedDates, dateStr, instance) {
            if (dateStr) {
                to_date.set("minDate", dateStr); // Set minimum date for to_date
            } else {
                to_date.set("minDate", null); // Clear minimum date for to_date
            }
        }
    });

    var to_date = $("#excelTicketToDate").flatpickr({
        dateFormat: 'd-M-Y', // Correct Flatpickr format
        onChange: function(selectedDates, dateStr, instance) {
            if (dateStr) {
                from_date.set("maxDate", dateStr); // Set maximum date for from_date
            } else {
                from_date.set("maxDate", null); // Clear maximum date for from_date
            }
        }
    });

    var start_date = $("#start_date").flatpickr({
        dateFormat: 'd-M-Y', // Correct Flatpickr format
        // defaultDate: firstDate, // Pre-set date
        onChange: function(selectedDates, dateStr, instance) {
            if (dateStr) {
                recurring_till.set("minDate", dateStr); // Set minimum date for recurring_till
            } else {
                recurring_till.set("minDate", null); // Clear minimum date for recurring_till
            }
        }
    });

    var recurring_till = $("#recurring_till").flatpickr({
        dateFormat: 'd-M-Y', // Correct Flatpickr format
        // defaultDate: lastDate, // Pre-set date
        onChange: function(selectedDates, dateStr, instance) {
            if (dateStr) {
                start_date.set("maxDate", dateStr); // Set maximum date for start_date
            } else {
                start_date.set("maxDate", null); // Clear maximum date for start_date
            }
        }
    });


    $('.category').select2({
        placeholder: 'Category Name',
        ajax: {
            url: '{{ route("categories.get") }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    search: params.term,
                    type: 'public'
                };
            },
            processResults: function(data) {
                var options = data.map(function(category) {
                    return {
                        id: category.categoryId,
                        text: category.categoryName,
                    };
                });

                return {
                    results: options
                };
            },
        },
    });

    /* ---------------------------------------------------------------------------------- */

    FilePond.setOptions({
        allowMultiple: true,
        server: {
            process: (fieldName, file, metadata, load, error, progress, abort, transfer, options) => {
                const formData = new FormData();
                formData.append(fieldName, file, file.name);

                const request = new XMLHttpRequest();
                let url;

                if (options && options.isUpdate) {
                    // Use update route
                    url = '{{ route("store.img.update") }}';
                } else {
                    // Use store route
                    url = '{{ route("store.img") }}';
                }
                request.open('POST', url);
                request.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');

                request.upload.onprogress = (e) => {
                    progress(e.lengthComputable, e.loaded, e.total);
                };

                request.onload = function() {
                    if (request.status >= 200 && request.status < 300) {
                        load(request.responseText);
                    } else {
                        error('oh no');
                    }
                };

                request.send(formData);

                return {
                    abort: () => {
                        request.abort();
                        abort();
                    },
                };
            },
            revert: (uniqueFileId, load, error, options) => {
                const request = new XMLHttpRequest();

                let url;
                if (options && options.isUpdate) {
                    // Use delete update route
                    url = `{{ route('delete.img.update') }}/${uniqueFileId}`;
                } else {
                    // Use delete store route
                    url = `{{ route('delete.img') }}/${uniqueFileId}`;
                }
                request.open('DELETE', url);
                request.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');

                request.onload = function() {
                    if (request.status === 200) {
                        console.log('DELETE request successful');
                        load();
                    } else {
                        console.error('DELETE request failed');
                        error();
                    }
                };

                request.send();
            },
        },
    });

    FilePond.registerPlugin(FilePondPluginFileValidateType);
    FilePond.registerPlugin(FilePondPluginImagePreview);


    // Initialize FilePond on an element with the class 'my-pond'
    const pond = $('.my-pond').filepond({
        allowImageCrop: true,
    });


    // Initialize Select2
    $('.js-example-basic-single').select2({
        minimumInputLength: 3,
        placeholder: 'Requester Name',
        dropdownParent: '#logNewTicketModal',
        ajax: {
            url: '{{ route("employees.get") }}',
            dataType: 'json',
            delay: 250, // add a delay if needed
            data: function(params) {
                return {
                    emp_name: params.term,
                    type: 'public'
                };
            },
            processResults: function(data) {
                // Map the data to the format expected by Select2
                var options = data.map(function(employee) {
                    return {
                        id: employee.hrEmployeeID,
                        text: employee.employeeName + ' (' + employee.hrEmployeeID + ')',
                        // You can include other properties if needed
                    };
                });

                return {
                    results: options
                };
            },
            // Additional AJAX parameters go here if needed
        }
    });

    /* ---------------------------------------------------------------------------------- */


    // Initialize Select2
    $('.employeeFilter').select2({
        placeholder: 'Requester Name',
        ajax: {
            url: '{{ route("employees.get") }}',
            dataType: 'json',
            delay: 250, // add a delay if needed
            data: function(params) {
                return {
                    emp_name: params.term,
                    type: 'public'
                };
            },
            processResults: function(data) {
                // Map the data to the format expected by Select2
                var options = data.map(function(employee) {
                    return {
                        id: employee.hrEmployeeID,
                        text: employee.employeeName + ' (' + employee.hrEmployeeID + ')',
                        // You can include other properties if needed
                    };
                });

                return {
                    results: options
                };
            },
            // Additional AJAX parameters go here if needed
        }
    });

    /* ---------------------------------------------------------------------------------- */

    // Initialize Select2
    $('.js-example-basic-single1').select2({
        placeholder: 'Request Name',
        dropdownParent: '#editTicketDetailsModal',
        ajax: {
            url: '{{ route("employees.get") }}',
            dataType: 'json',
            delay: 250, // add a delay if needed
            data: function(params) {
                return {
                    emp_name: params.term,
                    type: 'public'
                };
            },
            processResults: function(data) {
                // Map the data to the format expected by Select2
                var options = data.map(function(employee) {
                    return {
                        id: employee.hrEmployeeID,
                        text: employee.employeeName + ' (' + employee.hrEmployeeID + ')',
                        // You can include other properties if needed
                    };
                });

                return {
                    results: options
                };
            },
            // Additional AJAX parameters go here if needed
        }
    });

    /* ---------------------------------------------------------------------------------- */

    var $eventSelect = $('.js-example-basic-single');

    $eventSelect.on("change", function(e) {
        // Get the selected value
        var employeeId = $(this).val();

        $.ajax({
            url: '{{ route("employee.get") }}',
            method: 'POST', // Change to POST
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: {
                // Include any data you want to send with the POST request
                employeeId: employeeId,
            },
            success: function(response) {

                // Handle the success response
                $('#employeeName').text(response[0].employeeName);
                $('#departmentName').text(response[0].departmentName);
                $('#emailId').text(response[0].emailId);
                $('#mobile').text(response[0].mobile);
                $('#code').text(response[0].department_code);

                // Construct the imageUrl by concatenating photoURL and photoName
                var imageUrl = response[0].photoURL + response[0].photoName;

                // Set the imageUrl as the source of the image
                $('#user-photo').attr('src', imageUrl);

            },
            error: function(error) {
                // Handle the error response
                console.error('Error fetching data:', error);
            }
        });

    });

    /* ---------------------------------------------------------------------------------- */

    var $eventSelect1 = $('.js-example-basic-single1');

    $eventSelect1.on("change", function(e) {
        // Get the selected value
        var employeeId = $(this).val();

        // Access the selected option's departmentCode

        $.ajax({
            url: '{{ route("employee.get") }}',
            method: 'POST', // Change to POST
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: {
                // Include any data you want to send with the POST request
                employeeId: employeeId,
                // ...
            },
            success: function(response) {
                // Handle the success response

                $('#employeeNameEdit').text(response[0].employeeName);
                $('#departmentNameEdit').text(response[0].departmentName);
                $('#emailIdEdit').text(response[0].emailId);
                $('#mobileEdit').text(response[0].mobile);
                $('#codeEdit').text(response[0].department_code);

                // Construct the imageUrl by concatenating photoURL and photoName
                var imageUrl = response[0].photoURL + response[0].photoName;

                // Set the imageUrl as the source of the image
                $('#user-photo-edit').attr('src', imageUrl);
            },
            error: function(error) {
                // Handle the error response
                console.error('Error fetching data:', error);
            }
        });

    });

    /* ---------------------------------------------------------------------------------- */

    // Initialize Select2


    /* ---------------------------------------------------------------------------------- */


    // Assuming there's a change event listener for the category selection
    $('#categoryAssign').on('change', function(e, value) {

        var categoryId = $(this).val(); // Get the selected category ID

        // Make the AJAX request for subcategories based on the selected category
        $.ajax({
            url: '{{ route("subcategories.get") }}',
            dataType: 'json',
            delay: 250,
            data: {
                categoryId: categoryId,
            },
            success: function(response) {

                // Assuming the response is an array of subcategory objects
                var subcategories =
                    response; // Adjust this based on your actual response structure

                // Clear previous content if needed
                $('#subCategoryAssign').empty();

                $('#subCategoryAssign').append(
                    '<option value="">Select a Subcategory</option>');

                // Append each subcategory to the subcategoryAssign element
                subcategories.forEach(function(subcategory) {
                    $('#subCategoryAssign').append('<option value="' + subcategory
                        .subCategoryId + '">' + subcategory.subCategoryName +
                        '</option>');
                });

                $('#subCategoryAssign').val(value);
            },
            error: function(xhr, status, error) {
                // Handle errors here
                console.error(xhr, status, error);
            }
        });
    });

    // Assuming there's a change event listener for the subcategory selection
    $('#subCategoryAssign').on('change', function(e, value) {

        var subcategoryId = $(this).val(); // Get the selected subcategory ID

        // Make the AJAX request for items based on the selected subcategory
        $.ajax({
            url: '{{ route("items.get") }}',
            dataType: 'json',
            delay: 250,
            data: {
                subcategoryId: subcategoryId,
                type: 'public'
            },
            success: function(data) {
                // Clear previous content if needed
                $('#itemTypeAssign').empty();

                // Add a placeholder option
                $('#itemTypeAssign').append('<option value="">Item Type</option>');

                // Append each item option to the item selection
                data.forEach(function(item) {
                    $('#itemTypeAssign').append('<option value="' + item
                        .itemTypeId + '">' + item.itemTypeName + '</option>');
                });

                $('#itemTypeAssign').val(value);
            },
            error: function(xhr, status, error) {
                // Handle errors here
                console.error(xhr, status, error);
            }
        });
    });

    // Assuming there's a change event listener for the item selection
    $('#itemTypeAssign').on('change', function(e, value) {

        var itemId = $(this).val(); // Get the selected item ID

        // Make the AJAX request for subitems based on the selected item
        $.ajax({
            url: '{{ route("subitems.get") }}',
            dataType: 'json',
            delay: 250,
            data: {
                itemTypeId: itemId,
            },
            success: function(data) {
                // Assuming the response is an array of subitem objects
                var subitems = data; // Adjust this based on your actual response structure

                // Clear previous content if needed
                $('#itemAssign').empty();

                // Add a placeholder option
                $('#itemAssign').append('<option value="">Subitem Type</option>');

                // Append each subitem to the subItemAssign element
                subitems.forEach(function(subitem) {
                    $('#itemAssign').append('<option value="' + subitem.itemId +
                        '">' + subitem.itemName + '</option>');
                });

                $('#itemAssign').val(value);
            },
            error: function(xhr, status, error) {
                // Handle errors here
                console.error(xhr, status, error);
            }
        });
    });

    // Asset Id Field Selection depends on the Item Type
    // $('#itemTypeCategorize').on('change', function(e, value) {
    //     var itemTypeId = $('#itemTypeCategorize').val(); // Get the selected item ID
    //     var assetsId = '';
    //     showassets(itemTypeId,assetsId);
    //     // Make the AJAX request for subitems based on the selected item
    // });
    
    $('.subcategoryFilter').select2({
        placeholder: 'Subcategory Name',
        ajax: {
            url: '{{ route("subcategories.get") }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    search: params.term,
                    type: 'public',
                    categoryId: $('#categoryFilter').val()
                };
            },
            processResults: function(data) {
                var options = data.map(function(subcategory) {
                    return {
                        id: subcategory.subCategoryId,
                        text: subcategory.subCategoryName,
                    };
                });

                return {
                    results: options
                };
            },
        }
    });

    // Items Dropdown Setup
    $('.itemFilter').select2({
        placeholder: 'Item Type',
        ajax: {
            url: '{{ route("items.get") }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    search: params.term,
                    type: 'public',
                    categoryId: $('#categoryFilter').val(),
                    subcategoryId: $('#subCategoryFilter').val(),
                };
            },
            processResults: function(data) {
                var options = data.map(function(item) {
                    return {
                        id: item.itemTypeId,
                        text: item.itemTypeName,
                    };
                });

                return {
                    results: options
                };
            },
        }
    });


    $('.subitemFilter').select2({
        placeholder: 'Item Name',
        ajax: {
            url: '{{ route("subitems.get") }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    search: params.term,
                    type: 'public',
                    itemTypeId: $('#itemTypeFilter').val(),
                };
            },
            processResults: function(data) {
                var options = data.map(function(subitem) {
                    return {
                        id: subitem.itemId,
                        text: subitem.itemName,
                    };
                });

                return {
                    results: options
                };
            },
        }
    });

    $('#teamName').change(function(e, value) {
        var teamId = $(this).val();
        $.ajax({
            url: '{{ route("technicians.get") }}',
            dataType: 'json',
            delay: 250,
            data: {
                teamId: teamId
            }, // Pass the teamId as a parameter
            success: function(response) {
                // Assuming the response is an array of technician objects
                var technicians =
                    response; // Adjust this based on your actual response structure

                // Clear previous content if needed
                $('#technicianAssign').empty();

                // Add a placeholder option
                $('#technicianAssign').append(
                    '<option value="">Select Technician</option>');

                // Append each technician to the technicianAssign element
                technicians.forEach(function(technician) {
                    $('#technicianAssign').append('<option value="' + technician
                        .EMPLOYEE_ID + '">' + technician.USER_NAME + '</option>'
                    );
                });

                $('#technicianAssign').val(value)
            },
            error: function(xhr, status, error) {
                // Handle errors here
                console.error(xhr, status, error);
            }
        });
    });

    $('#statusUpdateTteamName').change(function(e, value) {
        var teamId = $(this).val();
        $.ajax({
            url: '{{ route("technicians.get") }}',
            dataType: 'json',
            delay: 250,
            data: {
                teamId: teamId
            }, // Pass the teamId as a parameter
            success: function(response) {
                // Assuming the response is an array of technician objects
                var technicians =
                    response; // Adjust this based on your actual response structure

                // Clear previous content if needed
                $('#statusUpdateTechnician').empty();

                // Add a placeholder option
                $('#statusUpdateTechnician').append(
                    '<option value="">Select Technician</option>');

                // Append each technician to the technicianAssign element
                technicians.forEach(function(technician) {
                    $('#statusUpdateTechnician').append('<option value="' + technician
                        .EMPLOYEE_ID + '">' + technician.USER_NAME + '</option>'
                    );
                });

                $('#statusUpdateTechnician').val(value)
            },
            error: function(xhr, status, error) {
                // Handle errors here
                console.error(xhr, status, error);
            }
        });
    });



    $('.trust').select2({
        placeholder: 'Trust',
        ajax: {
            url: '{{route("trusts.get")}}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    search: params.term,
                    type: 'public'
                };
            },
            processResults: function(data) {
                var options = data.map(function(trust) {
                    return {
                        id: trust.trustCode, // Adjust with your actual trust ID column
                        text: trust.trustName, // Adjust with your actual trust name column
                    };
                });

                return {
                    results: options
                };
            },
        },
    });




    /* ---------------------------------------------------------------------------------- */

    $('#logNewTicketForm').on('hidden.bs.modal', function(e) {

        $('.filepond--action-revert-item-processing').click();

    });


    /* ---------------------------------------------------------------------------------- */


    var table = $('#ticketsAllTable').DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        dataType: "json",
        order: [],
        responsive: true,

        dom: "<'row'<'col-sm-12'>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        ajax: {
            url: "{{ route('tickets') }}",
            data: function(d) {

                d.id = "{{request('id')}}";
                d.ticketNo = $('#filterTicketNo').val();
                d.userName = $('#userNameInput').val();
                d.oldTicket = $('#oldTicketInput').val();
                d.department = $('#departmentInput').val();
                d.technician = $('#technicianInput').val();
                d.mode = $('#modeInput').val();
                d.requestedFrom = $('#requestedFromInput').val();
                d.requestedTo = $('#requestedToInput').val();
                d.status = $('#statusSelect').val();
                d.progress = $('#progressSelect').val();
                d.subject = $('#filterTicketSubject').val();
                d.description = $('#filterTicketDesc').val();
                d.asset = $('#filterTicketAsset').val();
                d.teamId = $('#teamfilter').val();
                d.createdBy = $('#createdBy').val();

                d.category = $('#categoryFilter').val();
                d.subcategory = $('#subCategoryFilter').val();
                d.item = $('#itemNameFilter').val();
                d.itemType = $('#itemTypeFilter').val();
            }
        },
        columns: [{
                data: 'ticketNumber',
                name: 'ticketNumber',
                className: 'text-font-0',
            },
            {
                data: 'SUBJECT',
                name: 'SUBJECT',
                className: 'text-font-0',
                render: function(data, type, row) {
                    // Use trimmed_subject for display and full SUBJECT for the tooltip
                    let trimmedSubject = row.trimmed_subject + (data.length > 50 ? '...' : '');

                    return `<span title="${data}" data-toggle="tooltip">${trimmedSubject}</span>`;
                }
            },
            {
                data: 'attachment',
                name: 'attachment',
                className: 'text-font-0'
            },
            {
                data: 'USER_NAME',
                name: 'USER_NAME',
                className: 'text-font-0'
            },
            {
                data: 'CREATED_ON',
                name: 'CREATED_ON',
                className: 'text-font-0'
            },
            {
                data: 'DEPARTMENT_NAME',
                name: 'DEPARTMENT_NAME',
                className: 'text-font-0'
            },
            {
                data: 'TECHNICIAN_NAME',
                name: 'TECHNICIAN_NAME',
                className: 'text-font-0'
            },
            {
                data: 'CREATED_BY',
                name: 'CREATED_BY',
                className: 'text-font-0'
            },
            {
                data: 'PROGRESS',
                name: 'PROGRESS',
                className: 'text-font-0'
            },
            {
                data: 'TEAM_NAME',
                name: 'TEAM_NAME',
                className: 'text-font-0'
            },
            {
                data: 'action',
                name: 'action',
                orderable: true,
                searchable: true,
                className: 'text-center'
            },
        ],
        rowCallback: function(row, data) {
            if (data.IS_RELEASED === 'Y') {
                $(row).css('color', '#FFAD62');
            } else {
                $(row).css('color', '#343a40');
            }
        },


    }).on('error.dt', function(e, settings, techNote, message) {
        console.log('DataTables error: ', message);
        // Prevent default alert behavior
        e.preventDefault();
    });

    table.on('draw', function() {
        var info = table.page.info();
        var totalTickets = info.recordsTotal;
        
        // Update the total tickets badge
        $('#totalTicketsBadge').text(totalTickets);
       
    });

    $("#filterBtnTasks").on('click', function() {
        // table.page(0).draw('page');
        $('#ticketsAllTable').DataTable().ajax.reload(null, false);
        $('#filterTicketsModal').modal('hide');
    });

    $("#clearBtnTasks").on('click', function() {
        var userName = "{{ $userName }}";

        $('#filterTicketNo').val('');
        // If userName is empty, clear the selectize input
        if (!userName) {
            $('#userNameInput')[0].selectize.clear();
        }
        $('#oldTicketInput').val('');
        $('#departmentInput')[0].selectize.clear();
        $('#technicianInput')[0].selectize.clear();
        $('#modeInput').val('');
        $('#requestedFromInput').val('');
        $('#requestedToInput').val('');
        $('#statusSelect')[0].selectize.clear();
        $('#progressSelect')[0].selectize.clear();
        $('#filterTicketSubject').val('');
        $('#filterTicketDesc').val('');
        $('#filterTicketAsset').val('');
        $('#teamfilter').val('');
        $('#createdBy').val('');
        $('#categoryFilter').val(null).trigger('change');
        $('#subCategoryFilter').val(null).trigger('change');
        $('#itemNameFilter').val(null).trigger('change');
        $('#itemTypeFilter').val(null).trigger('change');

        // $('#ticketsAllTable').DataTable().ajax.reload(null, false);

        table.page.len(10).draw();

        $('#filterTicketsModal').modal('hide');
    });


    /* ---------------------------------------------------------------------------------- */

    $("#logNewTicketForm").submit(function(e) {

        //prevent Default functionality
        e.preventDefault();
        $('#Logtckt').prop('disabled', true);
        var formData = new FormData(this);

        // formData.append("teamName", $('#log_ticket_team option:selected').text());

        // Disable the submit button to prevent multiple submissions
        var submitButton = $(this).find(':submit');
        submitButton.prop('disabled', true);

        formData.append("selectedEmployee", $('#selectedEmployeeId').val());
        formData.append("employeeName", $('#employeeName').text());
        formData.append("employeeMail", $('#emailId').text());
        formData.append("departmentName", $('#departmentName').text());
        formData.append("code", $('#code').text());

        // var selectedTasks = [];
        $('#tasksSection input[name="link_code[]"]:checked').each(function() {
            formData.append("tasks[]", $(this).val());
        });

        // formData.append("selectedTasks", JSON.stringify(selectedTasks));

        $.ajax({
            method: "post",
            url: '{{route("tickets.create")}}',
            dataType: 'json',
            contentType: false,
            processData: false,
            cache: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: formData,
            success: function(data) {

                if (data['successCode'] == 1) {
                    iziToast.show({
                        title: 'Success',
                        position: 'topRight',
                        color: '#9cd5a9', // Set the color to your desired color
                        message: 'Ticket Logged'
                    });
                    $('#Logtckt').prop('disabled', false);
                    $(':input', '#logNewTicketForm')
                        .not(':button, :submit, :reset, :hidden')
                        .val('')
                        .prop('checked', false)
                        .prop('selected', false);

                    $("#logNewTicketModal").modal("hide");
                    $('.templateSection').css('display', 'none');
                    $('#task-header').css('display', 'none');

                    $('#tasksSection').empty();
                    $('#add_subject_text').val('');

                    $('#logNewTicketForm').find('input').val('');
                    $('#logNewTicketForm').find('select').val('');
                    $('#employee').text('');

                    $('.filepond--action-revert-item-processing').click();

                    // $eventSelect.val(null).trigger('change');

                    $('#employeeName').text('');
                    $('#departmentName').text('');
                    $('#emailId').text('');
                    $('#mobile').text('');
                    $('#code').text('');
                    $('#user-photo').attr('src',
                        'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAV0lEQVR42mP8/vYtDwAB/8E+pxYAAAAASUVORK5CYII='
                    );

                    $('#ticketsAllTable').DataTable().ajax.reload(null, false);
                    $('#allTasksTable').DataTable().ajax.reload(null, false);
                    $('#myTasksTable').DataTable().ajax.reload(null, false);

                }
                else{
                    iziToast.error({
                        title: 'Error',
                        position: 'topRight',
                        message: data['message']
                    });
                   
                }

                // Ensure the default radio button is checked
                $('#ticketRadioBtn').prop('checked', true);

                const frequencySection = document.querySelector('.frequency-section');
                frequencySection.style.display = 'none';
                $('#weekday-section').css('display', 'none');
                $('#monthly-section').css('display', 'none');
                // Reset the dropdown to 'Once'
                $('#frequency').val('Once');
                $('#recurring_till').val('');
                $('#weekday').val('');
                $('#start_date').val('');

                const ticketSection = document.querySelector('.ticketSection');
                ticketSection.style.display = 'block';

                // Enable the submit button after the response
                submitButton.prop('disabled', false);

            },
            error: function(xhr, status, error) {
                if (xhr.status === 422 && xhr.responseJSON.errors) {
                    // Loop through validation errors and show them
                    $.each(xhr.responseJSON.errors, function(key, messages) {
                        iziToast.error({
                            title: 'Error',
                            position: 'topRight',
                            message: messages[0]  // Show the first error message for each field
                        });
                    });
                } else {
                    // Show error message here
                    iziToast.error({
                        title: 'Error',
                        position: 'topRight',
                        message: 'Something went wrong'
                    });
                }
                $('#Logtckt').prop('disabled', false);
                // Enable the submit button after the response
                submitButton.prop('disabled', false);

            }
        });

    });

    /* ---------------------------------------------------------------------------------- */

    $("#editTicketForm").submit(function(e) {

        //prevent Default functionality
        e.preventDefault();

        var formData = new FormData(this);

        formData.append("employeeName", $('#employeeEdit option:selected').text());
        formData.append("departmentName", $('#departmentNameEdit').text());
        formData.append("code", $('#codeEdit').text());
        // formData.append("teamName", $('#teamEdit option:selected').text());

        $.ajax({
            method: "post",
            url: '{{route("tickets.update")}}',
            dataType: 'json',
            contentType: false,
            processData: false,
            cache: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: formData,
            success: function(data) {

                if (data['successCode'] == 1) {
                    iziToast.show({

                        title: 'Success',
                        position: 'topRight',
                        color: 'green',
                        message: 'Ticket Updated'

                    });

                    $(':input', '#editTicketForm')
                        .not(':button, :submit, :reset, :hidden')
                        .val('')
                        .prop('checked', false)
                        .prop('selected', false);
                }

                $('#editTicketDetailsModal').modal('hide');

                $eventSelect1.val(null).trigger('change');


                $('#employeeNameEdit').text('');
                $('#departmentNameEdit').text('');
                $('#emailIdEdit').text('');
                $('#mobileEdit').text('');
                $('#codeEdit').text('');
                $('#teamEdit').val('');


                $('#ticketsAllTable').DataTable().ajax.reload(null, false);
                $('#allTasksTable').DataTable().ajax.reload(null, false);
                $('#myTasksTable').DataTable().ajax.reload(null, false);

            }
        });

    });

    /* ---------------------------------------------------------------------------------- */

    $("#assignTicketForm").submit(function(e) {

        //prevent Default functionality
        e.preventDefault();
        $('#assignBtn').prop('disabled', true);

        var formData = new FormData(this);
        formData.append("teamName", $('#teamName option:selected').text());

        $.ajax({
            method: "post",
            url: '{{route("tickets.assign")}}',
            dataType: 'json',
            contentType: false,
            processData: false,
            cache: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: formData,
            success: function(data) {
                if (data['successCode'] == 1) {
                    iziToast.show({

                        title: 'Success',
                        position: 'topRight',
                        color: 'green',
                        message: 'Ticket Assigned'
                    });

                    $(':input', '#assignTicketForm')
                        .not(':button, :submit, :reset, :hidden')
                        .val('')
                        .prop('checked', false)
                        .prop('selected', false);

                    $('#assignBtn').prop('disabled', false);

                    $('#assignTicketModal').modal('hide');
                    $('#assignTicketForm').find('input').val('');
                    $('#assignTicketForm').find('select').val('');

                    $('#ticketsAllTable').DataTable().ajax.reload(null, false);
                    $('#myTasksTable').DataTable().ajax.reload(null, false);
                    $('#allTasksTable').DataTable().ajax.reload(null, false);
                }

                $('.category').val(null).trigger('change');
                $('.subcategory').val(null).trigger('change');
                $('.item').val(null).trigger('change');
                $('.subitem').val(null).trigger('change');
                $('.technician').val(null).trigger('change');
            }
            // error: function() {
            //     $('#assignBtn').prop('disabled', false);
            // }
        });

    });
    /* ---------------------------------------------------------------------------------- */


    var myTasksTable = $('#myTasksTable').DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        order: [],
      

        // order: [
        //     [4, 'desc']
        // ],
        dom: "<'row'<'col-sm-12'>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        ajax: {
            url: "{{ route('user.tickets') }}",
            data: function(d) {

                d.id = "{{request('id')}}";
                d.ticketNo = $('#filterTicketId').val(); // Updated to use the correct ID
                d.userName = $('#filterTaskRequestName').val();
                d.subject = $('#filterTasksSubject').val();
                d.description = $('#filterTasksDesc').val();

                d.department = $('#filterTasksdepartmentInput').val();
                d.asset = $('#filterTasksAsset').val();
                d.mode = $('#filterTasksMode').val();
                d.teamId = $('#teamfilterTasks').val();
                d.requestedFrom = $('#filterTasksrequestedFromInput').val();
                d.technician = $('#technicianTasks').val();
                d.requestedTo = $('#filterTasksrequestedToInput').val();
                d.category = $('#filterTaskCategory').val();
                d.item = $('#filterTaskItem').val();
                d.subcategory = $('#filterTaskSubCategory').val();
                d.itemType = $('#filterTaskItemType').val();
                d.status = $('#statusTaskSelect').val();
                d.progress = $('#progressTaskSelect').val();
                d.createdBy = $('#filterTasksCreatedBy').val();
            }
        },
        columns: [{
                data: 'ticketNumber',
                name: 'ticketNumber',
                className: 'text-font-0'
            },
            {
                data: 'SUBJECT',
                name: 'SUBJECT',
                className: 'text-font-0'
            },
            {
                data: 'attachment',
                name: 'attachment',
                className: 'text-font-0'
            },
            {
                data: 'USER_NAME',
                name: 'USER_NAME',
                className: 'text-font-0'
            },
            {
                data: 'CREATED_ON',
                name: 'CREATED_ON',
                className: 'text-font-0'
            },
            {
                data: 'DEPARTMENT_NAME',
                name: 'DEPARTMENT_NAME',
                className: 'text-font-0'
            },
            {
                data: 'TECHNICIAN_NAME',
                name: 'TECHNICIAN_NAME',
                className: 'text-font-0'
            },
            {
                data: 'PROGRESS',
                name: 'PROGRESS',
                className: 'text-font-0'
            },
            {
                data: 'TEAM_NAME',
                name: 'TEAM_NAME',
                className: 'text-font-0'
            },
            {
                data: 'action',
                name: 'action',
                orderable: true,
                searchable: true,
                className: 'text-center'
            },
        ]
    });

    $('#myTasksTable').on('draw.dt', function () {
        let isEngineer = "<?php echo userRoleName(); ?>" === "Engineer";
        if (isEngineer) {
            if ($.fn.DataTable.isDataTable('#myTasksTable')) {
                var myTasksInfo = myTasksTable.page.info(); // Ensure myTasksTable exists
                var totalMyTickets = myTasksInfo.recordsTotal; // Correct reference to myTasksInfo
                $('#totalTicketsBadge').text(totalMyTickets);
            }
        }
        
    });


    $("#resetTaskBtn").on('click', function() {

        $('#filterTicketId').val(''); // Updated to use the correct ID
        $('#filterTaskRequestName').val(null).trigger('change');
        $('#filterTasksSubject').val('');
        $('#filterTasksDesc').val('');
        $('#filterTasksdepartmentInput').val(null).trigger('change');
        $('#filterTasksAsset').val('');
        $('#filterTasksMode').val('');
        $('#teamfilterTasks')[0].selectize.clear();
        $('#filterTasksrequestedFromInput').val('');
        $('#technicianTasks')[0].selectize.clear();
        $('#filterTasksrequestedToInput').val('');
        $('#filterTaskCategory').val(null).trigger('change');
        $('#filterTaskItem').val(null).trigger('change');
        $('#filterTaskSubCategory').val(null).trigger('change');
        $('#filterTaskItemType').val(null).trigger('change');
        $('#statusTaskSelect')[0].selectize.clear();
        $('#progressTaskSelect')[0].selectize.clear();
        $('#filterTasksCreatedBy').val('');

        $('#myTasksTable').DataTable().ajax.reload(null, false);
        $('#allTasksTable').DataTable().ajax.reload(null, false);


        $('#filterTasksModal').modal('hide');


    }).on('error.dt', function(e, settings, techNote, message) {
        console.log('DataTables error: ', message);
        // Prevent default alert behavior
        e.preventDefault();
    });

    /* ---------------------------------------------------------------------------------- */

    var allTasksTable = $('#allTasksTable').DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        order: [],
        // order: [
        //     [4, 'desc']
        // ], // Order by CREATED_ON column in descending order
        dom: 'Bfrtip',
        searching: false, // Turn off the search
        buttons: [{
                extend: 'copy',
                exportOptions: {
                    columns: [0, 20, 3, 4, 5, 6, 7, 8, 9, 10, 11,
                        12
                    ] // Specify the column indices you want to include
                }
            },
            {
                extend: 'excel',
                title: function() {
                    return 'Tickets Dump ';
                },
                exportOptions: {
                    columns: function(idx, data, node) {
                        //Export only visible columns and the last four hidden columns
                        var visibleColumnIndexes = [0, 2, 3, 4, 5, 6, 10, 12, 13, 17,
                            26, 27,
                            28
                        ];
                        var hiddenColumnIndexes = [7, 8, 9, 11, 14, 15, 16, 18, 19, 20,
                            21, 22,
                            23, 24, 25, 30
                        ];

                        if (visibleColumnIndexes.includes(idx) || hiddenColumnIndexes
                            .includes(idx)) {
                            return true;
                        }
                        return false;
                    }
                }
            },
            {
                extend: 'pdf',
                exportOptions: {
                    columns: [0, 21, 2] // Specify the column indices you want to include
                }
            },
            {
                extend: 'csv',
                exportOptions: {
                    columns: [0, 21, 2] // Specify the column indices you want to include
                }
            }
        ],
        ajax: {
            url: "{{ route('all.tickets') }}",
            data: function(d) {

                d.id = "{{request('id')}}";
                d.ticketNo = $('#filterTicketId').val(); // Updated to use the correct ID
                d.userName = $('#filterTaskRequestName').val();
                d.subject = $('#filterTasksSubject').val();
                d.description = $('#filterTasksDesc').val();
                d.department = $('#filterTasksdepartmentInput').val();
                d.asset = $('#filterTasksAsset').val();
                d.mode = $('#filterTasksMode').val();
                d.teamId = $('#teamfilterTasks').val();
                d.requestedFrom = $('#filterTasksrequestedFromInput').val();
                d.technician = $('#technicianTasks').val();
                d.requestedTo = $('#filterTasksrequestedToInput').val();
                d.category = $('#filterTaskCategory').val();
                d.item = $('#filterTaskItem').val();
                d.subcategory = $('#filterTaskSubCategory').val();
                d.itemType = $('#filterTaskItemType').val();
                d.status = $('#statusTaskSelect').val();
                d.progress = $('#progressTaskSelect').val();
                d.createdBy = $('#filterTasksCreatedBy').val();

                d.excelFromDate = $('#excelTicketFromDate').val();
                d.excelToDate = $('#excelTicketToDate').val();
                d.excelStatus = $('#excelTicketStatus').val();
                d.excelProgress = $('#excelTicketProgress').val();
            }
        },
        columns: [{
                data: 'ticketNumber',
                name: 'ticketNumber',
                className: 'text-font-0'
            },
            {
                data: 'attachment',
                name: 'attachment',
                className: 'text-font-0'
            },
            {
                data: 'CREATED_BY',
                name: 'CREATED_BY',
                className: 'text-font-0'
            },
            {
                data: 'USER_NAME',
                name: 'USER_NAME',
                className: 'text-font-0'
            },
            {
                data: 'CREATED_ON',
                name: 'CREATED_ON',
                className: 'text-font-0'
            },
            {
                data: 'TEAM_NAME',
                name: 'TEAM_NAME',
                className: 'text-font-0'
            },
            {
                data: 'DEPARTMENT_NAME',
                name: 'DEPARTMENT_NAME',
                className: 'text-font-0'
            },
            {
                data: 'TEAM_NAME',
                name: 'TEAM_NAME',
                className: 'text-font-0 d-none'
            },
            {
                data: 'MODE',
                name: 'MODE',
                className: 'text-font-0 d-none'
            },
            {
                data: 'PRIORITY',
                name: 'PRIORITY',
                className: 'text-font-0 d-none'
            },
            {
                data: 'SUBJECT',
                name: 'SUBJECT',
                className: 'text-font-0',
            },
            {
                data: 'DESCRIPTION',
                name: 'DESCRIPTION',
                className: 'text-font-0 d-none'
            },
            {
                data: 'TECHNICIAN_NAME',
                name: 'TECHNICIAN_NAME',
                className: 'text-font-0'
            },
            {
                data: 'ASSIGNED_ON',
                name: 'ASSIGNED_ON',
                className: 'text-font-0'
            },
            {
                data: 'ticketType',
                name: 'ticketType',
                className: 'text-font-0 d-none'
            },
            {
                data: 'pendingTime',
                name: 'pendingTime',
                className: 'text-font-0 d-none'
            },
            {
                data: 'STATUS',
                name: 'STATUS',
                className: 'text-font-0 d-none'
            },
            {
                data: 'PROGRESS',
                name: 'PROGRESS',
                className: 'text-font-0'
            },
            {
                data: 'categoryName',
                name: 'categoryName',
                className: 'text-font-0 d-none'
            },
            {
                data: 'subCatName',
                name: 'subCatName',
                className: 'text-font-0 d-none'
            },
            {
                data: 'itemName',
                name: 'itemName',
                className: 'text-font-0 d-none'
            },
            {
                data: 'itemTypeName',
                name: 'itemTypeName',
                className: 'text-font-0 d-none'
            },
            {
                data: 'ASSET_ID',
                name: 'ASSET_ID',
                className: 'text-font-0 d-none'
            },
            {
                data: 'lastWorkUpdates',
                name: 'lastWorkUpdates',
                className: 'text-font-0 d-none'
            },
            {
                data: 'EFFORT',
                name: 'EFFORT',
                className: 'text-font-0 d-none'
            },
            {
                data: 'COST',
                name: 'COST',
                className: 'text-font-0 d-none'
            },
            {
                data: 'CLOSED_ON',
                name: 'CLOSED_ON',
                className: 'text-font-0'
            },
            {
                data: 'AGE',
                name: 'AGE',
                className: 'text-font-0'
            },
            {
                data: 'POINTS',
                name: 'POINTS',
                className: 'text-font-0'
            },
            {
                data: 'action',
                name: 'action',
                orderable: true,
                searchable: true,
                className: 'text-center'
            },
            {
                data: 'slaBreach',
                name: 'slaBreach',
                className: 'text-font-0 d-none'
            },

        ]
    }).on('error.dt', function(e, settings, techNote, message) {
        console.log('DataTables error: ', message);
        // Prevent default alert behavior
        e.preventDefault();
    });



    $("#filterTaskBtn").on('click', function() {

        myTasksTable.page(0).draw('page');
        allTasksTable.page(0).draw('page');

        $('#filterTasksModal').modal('hide');

    });

    $("#excelExportBtn").on('click', function() {

        var allTasksTable = $('#allTasksTable').DataTable();

        // Hide the modal
        $('#exportToExcelModal').modal('hide');

        // Use a flag to track if the export has been triggered
        var exportTriggered = true;

        // Listen
        // for the draw event
        allTasksTable.on('draw.dt', function() {
            // Check if the export has not been triggered yet and if the table is fully drawn
            if (exportTriggered) {
                // Introduce a delay before triggering the Excel export button.
                setTimeout(function() {
                        // Trigger the Excel export button
                        allTasksTable.button('.buttons-excel').trigger();
                    },
                    500
                ); // Adjust the delay time (in milliseconds) as needed to ensure the draw is complete

                // Remove the event listener to prevent multiple exports
                allTasksTable.off('draw.dt');
            }
        });
        allTasksTable.page.len(-1).draw(); // -1 sets the page length to "all"
    });

    $("#excelExportBtnClear").on('click', function() {

        $('#excelTicketFromDate').val('');
        $('#excelTicketToDate').val('');
        $('#excelTicketStatus')[0].selectize.clear();
        $('#excelTicketProgress')[0].selectize.clear();
        $('#technicianInput').val('');
        $('#exportToExcelModal').modal('hide');
        $('#allTasksTable').DataTable().ajax.reload(null, false);
    });


    /* ---------------------------------------------------------------------------------- */

    // Submit the form
    $("#workUpdatesForm").submit(function(e) {
        // Prevent Default functionality
        e.preventDefault();
        $('#workUpdateBtn').prop('disabled', true);

        var formData = new FormData(this);
        // formData.append('file', $('input[name=file]')[0].files[0]);
        formData.append("teamName", $('#statusUpdateTteamName option:selected').text());
        // console.log(formData);

        $.ajax({
            method: "post",
            url: '{{route("status.update")}}',
            dataType: 'json',
            contentType: false,
            processData: false,
            cache: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: formData,
            success: function(data) {
                if (data['successCode'] == 1) {
                    iziToast.show({
                        title: 'Success',
                        position: 'topRight',
                        color: 'green',
                        message: 'Status Updated'
                    });

                    $(':input', '#workUpdatesForm')
                        .not(':button, :submit, :reset, :hidden')
                        .val('')
                        .prop('checked', false)
                        .prop('selected', false);

                    $('#workUpdateBtn').prop('disabled', false);
                    $('#workUpdatesModal').modal('hide');

                    $('#workUpdatesForm').find('input').val('');
                    $('#workUpdatesForm').find('select').val('');

                    $('#myTasksTable').DataTable().ajax.reload(null, false);
                    $('#allTasksTable').DataTable().ajax.reload(null, false);
                    $('#ticketsAllTable').DataTable().ajax.reload(null, false);

                } else {
                    iziToast.show({
                        title: 'Error',
                        position: 'topRight',
                        color: 'red',
                        message: 'Please Try Again'
                    });
                    $('#workUpdateBtn').prop('disabled', false);
                }


            }
        });

    });

    $('#releaseTicketsForm').submit(function(e) {
        // Prevent Default functionality
        e.preventDefault();
        $('#releaseTicketsBtn').prop('disabled', true);

        var formData = new FormData(this);
        // formData.append('file', $('input[name=file]')[0].files[0]);
        // console.log(formData);

        $.ajax({
            method: "post",
            url: '{{route("status.update")}}',
            dataType: 'json',
            contentType: false,
            processData: false,
            cache: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: formData,
            success: function(data) {
                if (data['successCode'] == 1) {
                    iziToast.show({
                        title: 'Success',
                        position: 'topRight',
                        color: 'green',
                        message: 'Ticket Released'
                    });

                    $(':input', '#releaseTicketsForm')
                        .not(':button, :submit, :reset, :hidden')
                        .val('')
                        .prop('checked', false)
                        .prop('selected', false);

                    $('#releaseTicketsBtn').prop('disabled', false);
                    $('#releaseTicketsModal').modal('hide');

                    $('#releaseTicketsForm').find('input').val('');

                    $('#myTasksTable').DataTable().ajax.reload(null, false);
                    $('#allTasksTable').DataTable().ajax.reload(null, false);
                    $('#ticketsAllTable').DataTable().ajax.reload(null, false);

                } else {
                    iziToast.show({
                        title: 'Error',
                        position: 'topRight',
                        color: 'red',
                        message: 'Please Try Again'
                    });
                    $('#releaseTicketsBtn').prop('disabled', false);
                }


            }
        });

    });


    // Attach the checkStatus function to the onchange event of work_update_status
    $("#work_update_status").on("change", checkStatus);

    /* ---------------------------------------------------------------------------------- */

    $("#categorizeTaskForm").submit(function(e) {
        // Prevent Default functionality
        e.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            method: "post",
            url: '{{route("status.categorize")}}',
            dataType: 'json',
            contentType: false,
            processData: false,
            cache: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: formData,
            success: function(data) {
                if (data['successCode'] == 1) {
                    iziToast.show({
                        title: 'Success',
                        position: 'topRight',
                        color: 'green',
                        message: 'Status Updated'
                    });

                    $(':input', '#categorizeTaskForm')
                        .not(':button, :submit, :reset, :hidden')
                        .val('')
                        .prop('checked', false)
                        .prop('selected', false);

                    $('#categorizeTaskModal').modal('hide');

                    $('#myTasksTable').DataTable().ajax.reload(null, false);
                    $('#allTasksTable').DataTable().ajax.reload(null, false);

                    $('#categorizeTaskForm').find('select').val('');
                    $('#categorizeTaskForm').find('input').val('');

                    
                }

            }
        });

    });

    $("#closeTaskForm").submit(function(e) {

        // Prevent Default functionality
        e.preventDefault();
        $('#closeTaskBtId').prop('disabled', true);

        var formData = new FormData(this);

        $.ajax({
            method: "post",
            url: '{{route("ticket.close")}}',
            dataType: 'json',
            contentType: false,
            processData: false,
            cache: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: formData,
            success: function(data) {

                if (data['successCode'] == 1) {
                    iziToast.show({
                        title: 'Success',
                        position: 'topRight',
                        color: 'green',
                        message: data.message
                    });

                    $(':input', '#closeTaskForm')
                        .not(':button, :submit, :reset, :hidden')
                        .val('')
                        .prop('checked', false)
                        .prop('selected', false);
                    $('#closeTaskBtId').prop('disabled', false);
                    $('#closeTaskModal').modal('hide');
                    $('#closeTaskForm').find('select').val('');
                    $('#closeTaskForm').find('input').val('');

                    $('#myTasksTable').DataTable().ajax.reload(null, false);
                    $('#allTasksTable').DataTable().ajax.reload(null, false);
                    $('#ticketsAllTable').DataTable().ajax.reload(null, false);

                } else {
                    iziToast.show({
                        title: 'Error',
                        position: 'topRight',
                        color: 'red',
                        message: data.message
                    });
                    $('#closeTaskBtId').prop('disabled', false);
                }


            }
        });

    });

    $("#cancelTaskForm").submit(function(e) {
        // Prevent Default functionality
        e.preventDefault();
        $('#cancelTaskBtId').prop('disabled', true);

        var formData = new FormData(this);

        $.ajax({
            method: "post",
            url: '{{route("ticket.close")}}',
            dataType: 'json',
            contentType: false,
            processData: false,
            cache: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: formData,
            success: function(data) {

                if (data['successCode'] == 1) {
                    iziToast.show({
                        title: 'Success',
                        position: 'topRight',
                        color: 'green',
                        message: data.message
                    });

                    $(':input', '#cancelTaskForm')
                        .not(':button, :submit, :reset, :hidden')
                        .val('')
                        .prop('checked', false)
                        .prop('selected', false);
                    $('#cancelTaskBtId').prop('disabled', false);
                    $('#cancelTaskModal').modal('hide');
                    // $('#cancelTaskForm').find('select').val('');
                    $('#cancelTaskForm').find('input').val('');

                    $('#myTasksTable').DataTable().ajax.reload(null, false);
                    $('#allTasksTable').DataTable().ajax.reload(null, false);
                    $('#ticketsAllTable').DataTable().ajax.reload(null, false);

                } else {
                    iziToast.show({
                        title: 'Error',
                        position: 'topRight',
                        color: 'red',
                        message: data.message
                    });
                    $('#cancelTaskBtId').prop('disabled', false);
                }


            }
        });

    });

    $("#reopenTaskForm").submit(function(e) {


        // Prevent Default functionality
        e.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            method: "post",
            url: '{{route("ticket.reopen")}}',
            dataType: 'json',
            contentType: false,
            processData: false,
            cache: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: formData,
            success: function(data) {

                if (data['successCode'] == 1) {
                    iziToast.show({
                        title: 'Success',
                        position: 'topRight',
                        color: 'green',
                        message: data.message
                    });

                    $(':input', '#reopenTaskForm')
                        .not(':button, :submit, :reset, :hidden')
                        .val('')
                        .prop('checked', false)
                        .prop('selected', false);
                }

                $('#reopenTaskModal').modal('hide');

                $('#myTasksTable').DataTable().ajax.reload(null, false);
                $('#allTasksTable').DataTable().ajax.reload(null, false);
                $('#ticketsAllTable').DataTable().ajax.reload(null, false);
            }
        });

    });

});

$('#teamName').change(function(e, value) {

    var teamName = $(this).val();
    $.ajax({
        url: '{{ route("get.ticket.type") }}',
        dataType: 'json',
        delay: 250,
        data: {
            teamName: teamName
        },
        success: function(response) {
            // Assuming the response is an array of technician objects
            var taskTypes = response; // Adjust this based on your actual response structure

            // Clear previous content if needed
            $('#taskType').empty();

            // Add a placeholder option
            $('#taskType').append(
                '<option value="">Please Select</option>');

            // Append each technician to the technicianAssign element
            taskTypes.forEach(function(taskType) {
                $('#taskType').append('<option value="' + taskType.TASK_TYPE_ID + '">' +
                    taskType.DISPLAY_NAME + '</option>'
                );
            });

            $('#taskType').val(value)
        },
        error: function(xhr, status, error) {
            // Handle errors here
            console.error(xhr, status, error);
        }
    });

});


function pagination(links) {

    $('#pagination').empty();

    $.each(links, function(index, val) {

        $('#pagination').append('<li class="page-item" data-url="' + val.url +
            '"><a class="page-link" href="javascript:void(0);"></a></li>');
        $('#pagination li:last-child .page-link').text(val.label);

    });
}

function edit(id) {

    $.ajax({
        method: "post",
        url: '{{route("ticket.get")}}',
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        data: {
            ticketId: id
        },
        success: function(data) {

            if (data['successCode'] == 1) {

                $('#ticketId').val(data?.data[0]?.ticketId);

                $.ajax({
                    url: '{{ route("employee.get") }}',
                    method: 'POST', // Change to POST
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        // Include any data you want to send with the POST request
                        employeeId: data?.data[0]?.employeeId,
                        // ...
                    },
                    success: function(response) {
                        // Handle the success response

                        $('#employeeNameEdit').text(response[0].employeeName);
                        $('#departmentNameEdit').text(response[0].departmentName);
                        $('#emailIdEdit').text(response[0].emailId);
                        $('#mobileEdit').text(response[0].mobile);

                        // Construct the imageUrl by concatenating photoURL and photoName
                        var imageUrl = response[0].photoURL + response[0].photoName;

                        // Set the imageUrl as the source of the image
                        $('#user-photo-edit').attr('src', imageUrl);

                    },
                    error: function(error) {
                        // Handle the error response
                        console.error('Error fetching data:', error);
                    }
                });

                var technicianId = data?.data[0]?.technician;
                // Function to set dropdown value based on label
                function setDropdownValueByText(dropdown, text) {
                    dropdown.find('option').each(function() {
                        if ($(this).text() === text) {
                            $(this).prop('selected', true);
                            dropdown.trigger('change', [
                                technicianId
                            ]); // Trigger the change event
                            return false; // Break the loop
                        }
                    });
                }
                // Set teamName value based on the label
                setDropdownValueByText($('#teamEdit'), data?.data[0]?.team);

                $('#modeEdit').val(data?.data[0]?.mode);
                $('#priorityEdit').val(data?.data[0]?.priority);
                $('#descriptionEdit').val(data?.data[0]?.description);
                $('#subjectEdit').val(data?.data[0]?.subject);
                $('#ticketIdToEdit').text(data?.data[0]?.ticketNumber);
                $('#employeeEdit').val(data?.data[0]?.employeeId).trigger('change');

                // Check if the mode is 'App' and disable the #modeEdit field
                if (data?.data[0]?.mode === 'App') {
                    $('#modeEdit').prop('disabled', true); // Disable the field
                } else {
                    $('#modeEdit').prop('disabled', false); // Enable the field if not 'App'
                }

                // Assuming you have a container element with the ID "attachmentContainer"
                var attachmentContainer = $('#attachmentContainer');

                attachmentContainer.empty();

                // Check if data and data.attachments are not null or undefined
                if (data?.data?.attachments) {
                    // Create a row container
                    var rowDiv = $('<div>').addClass('row');

                    // Iterate over the attachments array using $.each()
                    $.each(data.data.attachments, function(index, attachment) {
                        // Create a div with Bootstrap column classes
                        var attachmentDiv = $('<div>').css({
                            'margin-bottom': '15px'
                        });

                        // Create a span to hold badge and removeBtn
                        var attachmentSpan = $('<span>').addClass(
                            'col-md-12 w-auto badge-uploaded-file d-flex align-items-center'
                        );

                        // Create badge element
                        var badge = $('<span>').text(attachment.ATTACHMENT).addClass(
                            'badge mr-2');

                        // Create removeBtn element with onclick function
                        var removeBtn = $('<span>').addClass(
                            'remove-file-btn btn btn-danger btn-sm').text('×').on(
                            'click',
                            function() {
                                // Call the removeAttachment function passing attachment ID
                                removeAttachment(attachment.ATTACHMENT_ID);
                            });

                        // Append badge and removeBtn to the span
                        attachmentSpan.append(badge, removeBtn);

                        // Append the span to the div
                        attachmentDiv.append(attachmentSpan);

                        // Append the div to the row
                        rowDiv.append(attachmentDiv);

                        // After every third item, append the row to the container and create a new row
                        if ((index + 1) % 3 === 0) {
                            attachmentContainer.append(rowDiv);
                            rowDiv = $('<div>').addClass('row');
                        }
                    });

                    // Append any remaining items in the rowDiv to the container
                    if (rowDiv.children().length > 0) {
                        attachmentContainer.append(rowDiv);
                    }
                }

                $('#editTicketDetailsModal').modal('show');
            }

        }
    });

}
// Function to remove attachment by ID
function removeAttachment(attachmentId) {
    // Add your logic to remove the attachment using the provided ID

    return fetch(`attachments/remove/${attachmentId}`)
        .then((response) => response.json())
        .then((data) => {

            iziToast.info({
                title: 'Success',
                message: 'File Removed',
                position: 'topRight',
                color: 'green'
            });

            // Assuming you have a container element with the ID "attachmentContainer"
            var attachmentContainer = $('#attachmentContainer');

            attachmentContainer.empty();

            // Check if data and data.attachments are not null or undefined
            if (data?.data?.attachments) {
                // Iterate over the attachments array using $.each()
                $.each(data.data.attachments, function(index, attachment) {
                    // Your code to handle each attachment
                    // For example, create HTML elements and append them to the container

                    // Create a div to wrap badge with margins
                    var attachmentDiv = $('<div>').css({
                        'margin': '5px',
                        'padding': '10px'
                    });

                    // Create a span to hold badge and removeBtn
                    var attachmentSpan = $('<span>').addClass('w-auto badge-uploaded-file');

                    // Create badge element
                    var badge = $('<span>').text(attachment.ATTACHMENT);

                    // Create removeBtn element with onclick function
                    var removeBtn = $('<span>').addClass('remove-file-btn').text('×').on('click',
                        function() {
                            // Call the removeAttachment function passing attachment ID
                            removeAttachment(attachment.ATTACHMENT_ID);
                        });


                    // Append badge and removeBtn to the span
                    attachmentSpan.append(badge, removeBtn);

                    // Append the span to the div
                    attachmentDiv.append(attachmentSpan);

                    // Append the div to the container
                    attachmentContainer.append(attachmentDiv);
                });
            }


        })
        .catch((error) => {
            console.error('Error:', error);
            return [];
        });
}

function assign(id, ticketNumber) {

    category = '';
    subCategory = '';
    itemType = '';
    item = '';

    // Make an AJAX request to fetch already assigned details
    $.ajax({
        type: "GET",
        url: "{{ route('assignment.details', ['ticketId' => ':ticketId']) }}".replace(':ticketId', id),
        data: {
            id: id
        },
        success: function(response) {

            // Set ticketId value
            $('#ticketIdForAssignment').val(response.TICKET_ID);

            var technicianId = response.TECHNICIAN_ID;
            var taskTypeId = response.TASK_TYPE_ID;

            // Function to set dropdown value based on label
            function setDropdownValueByText(dropdown, text) {
                dropdown.find('option').each(function() {
                    if ($(this).text() === text) {
                        $(this).prop('selected', true);
                        dropdown.trigger('change', [
                            technicianId
                        ]); // Trigger the change event
                        return false; // Break the loop
                    }
                });
            }

            // Set teamName value based on the label
            setDropdownValueByText($('#teamName'), response.TEAM_NAME);

            setTimeout(function() {
                $('#taskType').val(response.TASK_TYPE_ID);
            }, 1000);

            // Set dueDate value
            flatpickr("#datepicker", {
                // other options...
                enableTime: true,
                // Use 12-hour format with AM/PM
                time_24hr: false,
                defaultDate: response.DUE_DATE,
                dateFormat: "d-M-Y h:i K"

            });

            $('#categoryAssign').val(response.CATEGORY_ID);

            category = response.CATEGORY_ID;
            subCategory = response.SUB_CATEGORY_ID;
            itemType = response.ITEM_TYPE_ID;
            item = response.ITEM_ID;

        },
        error: function(xhr, status, error) {
            // Handle errors here
            console.error(xhr.responseText);
        }
    });
    $.ajax({
        url: '{{ route("categories.get") }}',
        dataType: 'json',
        delay: 250,
        data: function(params) {
            return {
                search: params.term,
                type: 'public'
            };
        },
        success: function(response) {

            // Assuming the response is an array of category objects
            var categories = response // Adjust this based on your actual response structure

            // Clear previous content if needed
            $('#categoryAssign').empty();

            $('#categoryAssign').append('<option value="">Select a Category</option>');

            // Append each category to the categoryAssign element
            categories.forEach(function(category) {
                $('#categoryAssign').append('<option value="' + category.categoryId + '">' +
                    category.categoryName + '</option>');
            });

            $('#categoryAssign').val(category).trigger('change', [subCategory]);

            $('#subCategoryAssign').val(subCategory).trigger('change', [itemType]);

            $('#itemTypeAssign').val(itemType).trigger('change', [item]);



        },
        error: function(xhr, status, error) {
            // Handle errors here
            console.error(xhr, status, error);
        }
    });

    $('#assignTicketForm').find('input').val('');
    $('#assignTicketForm').find('select').val('');

    $('#ticketIdForAssignment').val(id);

    $('#ticketIdToAssign').html(ticketNumber)

    $('#assignTicketModal').modal('show');

}

function fetchAttachmentsAndAppendToModal(ticketId) {
    // Replace this with your actual API endpoint to fetch attachments
    return fetch(`attachments/${ticketId}`)
        .then((response) => response.json())
        .then((data) => {
            const attachments = data.data;

            // Assuming you have a modal with ID 'attachmentModal'
            const modal = document.getElementById('attachmentModal');
            const modalBody = modal.querySelector('.modal-body');

            // Clear existing content in the modal body
            modalBody.innerHTML = '';

            if (attachments.length === 0) {
                // If no attachments, show iziToast message
                iziToast.info({
                    title: 'Info',
                    message: 'No files attached.',
                    position: 'topRight',
                    color: 'green'
                });
            } else {

                // Append each attachment to the modal body
                attachments.forEach((attachment) => {
                    // Create a container for each attachment
                    const attachmentContainer = document.createElement('div');

                    // Display attachment filename
                    var baseUrl = "{{ asset('public/attachments/') }}";

                    const filenameElement = document.createElement('div');
                    filenameElement.textContent = attachment.attachment;
                    attachmentContainer.appendChild(filenameElement);

                    // Create a "View" link
                    const viewLink = document.createElement('a');
                    viewLink.textContent = 'View';
                    viewLink.href = baseUrl + '/' + attachment
                        .attachment; // Replace with the actual URL for viewing
                    viewLink.target = '_blank'; // Open link in a new tab/window
                    attachmentContainer.appendChild(viewLink);

                    // Add a gap between the links
                    attachmentContainer.appendChild(document.createTextNode(' '));

                    // Create a "Download" link
                    const downloadLink = document.createElement('a');
                    downloadLink.textContent = 'Download';
                    downloadLink.href = baseUrl + '/' + attachment
                        .attachment; // Replace with the actual URL for downloading
                    downloadLink.download = attachment.attachment; // Set the download attribute
                    attachmentContainer.appendChild(downloadLink);

                    // Append the container to the modal body
                    modalBody.appendChild(attachmentContainer);

                    $('#attachmentModal').modal('show');



                });

            }

            return attachments;
        })
        .catch((error) => {
            console.error('Error fetching attachments:', error);
            return [];
        });
}

function checkStatus() {
    var status = $("#work_update_status").val();
    var onHoldReasonDiv = $("#onHoldReason");
    var onHoldSelect = $("#onhold");

    if (status === "On Hold") {
        onHoldReasonDiv.show(); // Show the div
        onHoldSelect.prop("required", true); // Add the required attribute
    } else {
        onHoldReasonDiv.hide(); // Hide the div
        onHoldSelect.prop("required", false); // Remove the required attribute
    }

    const statusSelect = document.getElementById('work_update_status');
    const selectedOption = statusSelect.options[statusSelect.selectedIndex];
    const isTransferred = selectedOption.getAttribute('data-transferred');

    const statusUpdateDiv = document.getElementById('teamSelectionDiv');
    if (isTransferred === 'Y') {
        statusUpdateDiv.style.display = 'block';
        // Make required the teamName select if transferred
        $('#statusUpdateTteamName').prop('required', true);
        $('#statusUpdateTechnician').prop('required', true);
    } else {
        statusUpdateDiv.style.display = 'none';
        $('#statusUpdateTteamName').val(''); // Clear the teamName select if not transferred
        $('#statusUpdateTechnician').val(''); // Clear the technician select if not transferred

        $('#statusUpdateTteamName').prop('required', false);
         $('#statusUpdateTechnician').prop('required', false);
    }

}

function statusUpdate(ticketId, ticketNo, ticketStatus) {
    $('#ticketIdStatus').val(ticketId)
    $('#workUpdateTaskId').html(ticketNo)

    // ticketStatus = String(ticketStatus);
    const statusDiv = document.getElementById('statusUpdateDiv');

    const statusSelect = document.getElementById('work_update_status');

    // Show the status dropdown if the ticketStatus is 'Open'
    if (ticketStatus.toLowerCase() === 'open') {
        statusDiv.style.display = 'block'; // Show the div
        statusSelect.setAttribute('required', 'required');
    } else {
        statusDiv.style.display = 'none'; // Hide the div if status is not 'Open'
        statusSelect.removeAttribute('required'); // Remove the required attribute
    }

    // Ajax request for updates
    $.ajax({
        type: 'GET',
        url: '{{ route("tasks.updates") }}', // Replace with the actual URL for your AJAX endpoint
        data: {
            ticketId: ticketId
        },
        success: function(data) {
            // Clear existing updates
            $('#workUpdateHistoryContainer').empty();

            // Check if data is an array or has an array-like structure
            if (Array.isArray(data['data']) || (typeof data['data'] === 'object' && data[
                        'data'] !== null &&
                    'forEach' in data['data'])) {
                // Iterate through the updates and append HTML
                data['data'].forEach(function(update) {
                    // Handle null value for update.reason
                    var reasonHtml = (update.reason !== null) ?
                        '<p class="update-message text-small">' + update.reason +
                        '</p>' : '';

                    // Your update HTML generation here, including reasonHtml
                    var updateHtml = '<div class="update-card">' +
                        '<h5 class="update-for-detail">' +
                        '[' + update.technician + '] ' + update.description +
                        '</h5>' +
                        '<div class="update-text">' +
                        '<span class="update-technician-name">' + update.technician +
                        '</span> on ' +
                        update.logDate +
                        '<span class="update-in-progress"> (' + update.status +
                        ')</span>' +
                        '</div>' +
                        reasonHtml +
                        '</div>';

                    // Append the update HTML to the workUpdateHistoryContainer
                    $('#workUpdateHistoryContainer').append(updateHtml);
                });
            } else {
                console.error('Invalid data format:', data);
            }
        },
        error: function(xhr, status, error) {
            // Handle errors
            console.error(xhr.responseText);
        }
    });

    $('#workUpdatesModal').modal('show');
}

function releaseTicket(ticketId, ticketNo, ticketStatus) {
    $('#releaseTicketsModal').modal('show');

    $('#ticketIdRelease').val(ticketId);
    $('#ticketNoRelease').html(ticketNo);

    // Ajax request for updates
    $.ajax({
        type: 'GET',
        url: '{{ route("tasks.updates") }}', // Replace with the actual URL for your AJAX endpoint
        data: {
            ticketId: ticketId
        },
        success: function(data) {           
        },
        error: function(xhr, status, error) {
            // Handle errors
            console.error(xhr.responseText);
        }
    });
    
}

function categorize(ticketId) {

    category = '';
    subCategory = '';
    itemType = '';
    item = ''; 
    assetsId ='';
    // Make an AJAX request to fetch already assigned details
    $.ajax({
        type: "GET",
        url: "{{ route('assignment.details', ['ticketId' => ':ticketId']) }}".replace(':ticketId',
            ticketId),
        data: {
            id: ticketId
        },
        success: function(response) {

            $('#categoryCategorize').val(response.CATEGORY_ID);

            category = response.CATEGORY_ID;
            subCategory = response.SUB_CATEGORY_ID;
            itemType = response.ITEM_TYPE_ID;
            item = response.ITEM_ID;
            
            $('#categorizeTaskId').html(response.TICKET_NO);
            
            if(response.ASSET_ID != null && response.ASSET_ID != '')
            {
                assetsId = (response.TRUST_CODE || '') + '-' + (response.ASSET_ID || '');
            }else{
                assetsId = '';
            }
            
        },
        error: function(xhr, status, error) {
            // Handle errors here
            console.error(xhr.responseText);
        }
    });

    $.ajax({
        url: '{{ route("categories.get") }}',
        dataType: 'json',
        delay: 250,
        data: function(params) {
            return {
                search: params.term,
                type: 'public'
            };
        },
        success: function(response) {

            // Assuming the response is an array of category objects
            var categories = response // Adjust this based on your actual response structure

            // Clear previous content if needed
            $('#categoryCategorize').empty();

            $('#categoryCategorize').append('<option value="">Select a Category</option>');

            // Append each category to the categoryCategorize element
            categories.forEach(function(category) {
                $('#categoryCategorize').append('<option value="' + category
                    .categoryId + '">' +
                    category.categoryName + '</option>');
            });

            $('#categoryCategorize').val(category);
            // $('#subCategoryCategorize').val(subCategory).trigger('change');
            // $('#itemTypeCategorize').val(itemType).trigger('change');

            showSubcategory(category,subCategory);
            showItemType(subCategory,itemType);
            showItem(itemType,item);
            showassets(itemType,assetsId);
        },
        error: function(xhr, status, error) {
            // Handle errors here
            console.error(xhr, status, error);
        }
    });

    $('#ticketIdCategorize').val(ticketId);
    $('#categorizeTaskModal').modal('show');    
}

function showSubcategory(categoryId,subId)
{
    // Make the AJAX request for subcategories based on the selected category
    $.ajax({
        url: '{{ route("subcategories.get") }}',
        dataType: 'json',
        delay: 250,
        data: {
            categoryId: categoryId,
        },
        success: function(response) {
     
            // Assuming the response is an array of subcategory objects
            var subcategories =
                response; // Adjust this based on your actual response structure

            // Clear previous content if needed
            $('#subCategoryCategorize').empty();                

            $('#subCategoryCategorize').append(
                '<option value="">Select a Subcategory</option>');

            // Append each subcategory to the subcategoryCategorize element
            subcategories.forEach(function(subcategory) {
                if(subId == subcategory.subCategoryId)
                {
                  var sel = 'selected';  
                }else{
                  var sel = '';  
                }
                $('#subCategoryCategorize').append('<option value="' +
                    subcategory.subCategoryId + '" '+sel+'>' + subcategory
                    .subCategoryName + '</option>');

            });

            if(subId == '')
            {
                $('#itemTypeCategorize').empty();
                $('#itemCategorize').empty();
                $('#assetIdField').val('');
            }
        },
        error: function(xhr, status, error) {
            // Handle errors here
            console.error(xhr, status, error);
        }
    });
    
}

function showItemType(subcategoryId,itemTypeId)
{
    $.ajax({
        url: '{{ route("items.get") }}',
        dataType: 'json',
        delay: 250,
        data: {
            subcategoryId: subcategoryId,
            type: 'public'
        },
        success: function(data) {
            // Clear previous content if needed
            $('#itemTypeCategorize').empty();

            // Add a placeholder option
            $('#itemTypeCategorize').append('<option value="">Select a Item Type</option>');

            // Append each item option to the item selection
            data.forEach(function(item) {
                if(itemTypeId == item.itemTypeId)
                {
                  var sel = 'selected';  
                }else{
                  var sel = '';  
                }
                $('#itemTypeCategorize').append('<option value="' + item
                    .itemTypeId + '" '+sel+'>' + item.itemTypeName + '</option>');
            });

            // $('#itemTypeCategorize').val(value);
            // $('#itemTypeCategorize').val(value).trigger('change');
        },
        error: function(xhr, status, error) {
            // Handle errors here
            console.error(xhr, status, error);
        }
    });
   
}
function showItem(itemTypeId,item)
{
    $.ajax({
        url: '{{ route("subitems.get") }}',
        dataType: 'json',
        delay: 250,
        data: {
            itemTypeId: itemTypeId,
        },
        success: function(data) {
            // Assuming the response is an array of subitem objects
            var subitems = data; // Adjust this based on your actual response structure

            // Clear previous content if needed
            $('#itemCategorize').empty();

            // Add a placeholder option
            $('#itemCategorize').append('<option value="">Select a Item</option>');

            // Append each subitem to the subItemCategorize element
            subitems.forEach(function(subitem) {
                if(item == subitem.itemId)
                {
                  var sel = 'selected';  
                }else{
                  var sel = '';  
                }
                $('#itemCategorize').append('<option value="' + subitem.itemId +
                    '" '+sel+'>' + subitem.itemName + '</option>');
            });

            // $('#itemCategorize').val(value);
        },
        error: function(xhr, status, error) {
            // Handle errors here
            console.error(xhr, status, error);
        }
    });
}
$('#subCategoryCategorize').on('change', function(e, value) {
    var subcategoryId = $(this).val();
    var itemTypeId = '';
    showItemType(subcategoryId,itemTypeId);
});
$('#categoryCategorize').on('change', function(e, value) {
    var categoryId = $(this).val(); // Get the selected category ID
    var subId = ''; // Get the selected category ID
    showSubcategory(categoryId,subId);
});
 $('#itemTypeCategorize').on('change', function(e, value) {
    var itemTypeId = $(this).val(); // Get the selected item ID

    var assetsId = '';
    showItem(itemTypeId,assetsId);
    showassets(itemTypeId,assetsId);

});

function getAssets(assetsId) {
    var ticketId = $('#ticketIdCategorize').val(); // Get the selected status

    $.ajax({
        url: '{{ route("get.user.assets") }}',
        method: 'GET',      
        data: {
            ticketId: ticketId,
        },
        success: function(response) {

            var assetDropdown = $('#assetIdCategorize');
            assetDropdown.empty().append('<option value="">Select Asset ID</option>');

            if (response.data && response.data.length > 0) {
                $.each(response.data, function(index, asset) {
                    let assetValue = `${asset.assetTrustCode}-${asset.assetNumber}`;
                    assetDropdown.append(`
                        <option value="${assetValue}" ${assetsId === assetValue ? 'selected' : ''}>
                            ${assetValue}
                        </option>
                    `);
                });
            } else {
                console.log(response.message || 'No assets found.');
            }
        },
        error: function(xhr) {
            console.log("Error occurred: " + xhr.responseText);
        }
    });
}

function showassets(itemTypeId,assetsId)
{
    $.ajax({
        url: '{{ route("asset.selection") }}',
        dataType: 'json',
        type: 'GET',
        data: {
            itemTypeId: itemTypeId,
        },
        success: function(response) {
            var assetField = $('#assetIdField'); // Container for Asset ID field
            assetField.empty(); // Clear current content
            if (response.data && response.data.length > 0) { 
                let isRequired = response.data[0].isAssetRequired === 'Y' ? 'required' : '';
                if (response.data[0].isAssetSelection === 'Y') {
                    // Create a select dropdown
                    assetField.html(`
                    <label for="assetIdCategorize" class="control-label mb-1">Asset ID</label>
                    <select name="assetId" id="assetIdCategorize" class="form-control" ${isRequired}>
                        <option value="">Select Asset ID</option>
                        <!-- Add dynamic options here if available -->
                    </select>
                `);
                    // Call getAssets to populate the dropdown
                    getAssets(assetsId);
                } else {
                    // Create a text input
                    assetField.html(`
                        <label for="assetIdCategorize" class="control-label mb-1">Asset ID</label>
                        <input type="text" name="assetId" id="assetIdCategorize" class="form-control" placeholder="Trust Code-Asset No" value="${assetsId ? assetsId : ''}" ${isRequired}>
                    `);
                }
            }
        },
        error: function(xhr, status, error) {
            // Handle errors here
            console.error(xhr, status, error);
        }
    });
}

function closeTask(ticketId, ticketNo) {

    // Clear Status dropdown
    $('#statusCategorize').val('');

    // Clear Remarks textarea
    $('#close_task_remarks').val('');

    // Clear Effort input
    $('#close_task_effort').val('');

    // Clear Cost input
    $('#close_task_cost').val('');

    // Clear File input
    $('#close_task_file').val('');

    $('#taskIdToClose').html(ticketNo);

    $('#ticketIdClose').val(ticketId);

    $('#closeTaskModal').modal('show');
}

function cancelTask(ticketId, ticketNo) {
    $('#taskIdToCancel').html(ticketNo);

    $('#ticketIdCancel').val(ticketId);

    $('#cancelTaskModal').modal('show');
}

function reopenTask(ticketId, ticketNo) {

    // Clear Status dropdown
    $('#statusReopen').val('');

    // Clear Remarks textarea
    $('#reopen_task_remarks').val('');

    // Clear Effort input
    $('#reopen_task_effort').val('');

    // Clear Cost input
    $('#reopen_task_cost').val('');

    // Clear File input
    $('#reopen_task_file').val('');

    $('#taskIdToreopen').html(ticketNo);

    $('#ticketIdreopen').val(ticketId);

    $('#reopenTaskModal').modal('show');
}

</script>
<script>
document.getElementById('close_task_effort').addEventListener('input', function(e) {
    if (this.value.length > 4) {
        this.value = this.value.slice(0, 4); // Trim to max length of 4
    }
});
// Dynamic Progress Option
$(document).ready(function() {

    var progressSelect = $('#progressSelect').selectize({
        plugins: ['remove_button'],
        delimiter: ',',
        persist: false,
        create: false,
        valueField: 'value',
        labelField: 'text',
        searchField: ['text'],
        placeholder: 'Choose Progress',
        //closeAfterSelect: true
    });


    // Get the selectize instance
    var selectizeInstance = progressSelect[0].selectize;

    $('#statusSelect').on('change', function() {
        var statuses = $('#statusSelect').val(); // Get the selected status
        // alert(status);
        $.ajax({
            url: '{{ route("get.progress.option") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: {
                status: statuses,
            },
            success: function(response) {

                selectizeInstance.clear(); // Clears selected value
                selectizeInstance.clearOptions(); // Clears all the options

                // Convert the HTML response to jQuery elements for easy parsing
                $(response).each(function() {
                    var optionValue = $(this).val(); // Get option value
                    var optionText = $(this).text(); // Get option text

                    // Add the new options to Selectize
                    selectizeInstance.addOption({
                        value: optionValue,
                        text: optionText,

                    });               
                });

                // Refresh the Selectize dropdown
                selectizeInstance.refreshOptions(false);

            },
            error: function(xhr) {
                console.log("Error occurred: " + xhr.responseText);
            }
        });
    });



});
    
$(document).ready(function() {

    var progressTaskSelect = $('#progressTaskSelect').selectize({
        plugins: ['remove_button'],
        delimiter: ',',
        persist: false,
        create: false,
        valueField: 'value',
        labelField: 'text',
        searchField: ['text'],
        placeholder: 'Choose Progress',
    });

    // Get the selectize instance
    var selectizeInstance = progressTaskSelect[0].selectize;

    $('#statusTaskSelect').on('change', function() {
        var statuses = $('#statusTaskSelect').val(); // Get the selected status
        // alert(status);
        $.ajax({
            url: '{{ route("get.progress.option") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: {
                status: statuses,
            },
            success: function(response) {

                selectizeInstance.clear(); // Clears selected value
                selectizeInstance.clearOptions(); // Clears all the options

                // Convert the HTML response to jQuery elements for easy parsing
                $(response).each(function() {
                    var optionValue = $(this).val(); // Get option value
                    var optionText = $(this).text(); // Get option text

                    // Add the new options to Selectize
                    selectizeInstance.addOption({
                        value: optionValue,
                        text: optionText
                    });
                });
                // Refresh the Selectize dropdown
                selectizeInstance.refreshOptions(false);
            },
            error: function(xhr) {
                console.log("Error occurred: " + xhr.responseText);
            }
        });
    });
});
$(document).ready(function() {

    var progressSelect = $('#excelTicketProgress').selectize({
        plugins: ['remove_button'],
        delimiter: ',',
        persist: false,
        create: false,
        valueField: 'value',
        labelField: 'text',
        searchField: ['text'],
        placeholder: 'Choose Progress',
    });

    // Get the selectize instance
    var selectizeInstance = progressSelect[0].selectize;

    $('#excelTicketStatus').on('change', function() {
        var statuses = $('#excelTicketStatus').val(); // Get the selected status
        // alert(status);
        $.ajax({
            url: '{{ route("get.progress.option") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: {
                status: statuses,
            },
            success: function(response) {

                selectizeInstance.clear(); // Clears selected value
                selectizeInstance.clearOptions(); // Clears all the options

                // Convert the HTML response to jQuery elements for easy parsing
                $(response).each(function() {
                    var optionValue = $(this).val(); // Get option value
                    var optionText = $(this).text(); // Get option text

                    // Add the new options to Selectize
                    selectizeInstance.addOption({
                        value: optionValue,
                        text: optionText
                    });
                });
                // Refresh the Selectize dropdown
                selectizeInstance.refreshOptions(false);
            },
            error: function(xhr) {
                console.log("Error occurred: " + xhr.responseText);
            }
        });
    });
});

$(document).ready(function() {
    var selectedEmployeeId = $('#selectedEmployeeId').val(); // Get selected employee ID

    if(selectedEmployeeId) {
         // Call the API to fetch personal details
        $.ajax({
            url: '{{ route("employee.get") }}',
            method: 'POST', // Change to POST
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: {
                // Include any data you want to send with the POST request
                employeeId: selectedEmployeeId,
            },

            success: function(response) {

                if (Array.isArray(response) && response.length > 0) {
                    const data = response[0];
                    $('#employeeName').text(data.employeeName || '');
                    $('#departmentName').text(data.departmentName || '');
                    $('#emailId').text(data.emailId || '');
                    $('#mobile').text(data.mobile || '');
                    $('#code').text(data.department_code || '');

                    // Construct the imageUrl by concatenating photoURL and photoName
                    var imageUrl = data.photoURL + data.photoName;

                    // Set the imageUrl as the source of the image
                    $('#user-photo').attr('src', imageUrl);
                } else {
                    // Optionally clear fields if no data returned
                    $('#employeeName').text('');
                    $('#departmentName').text('');
                    $('#emailId').text('');
                    $('#mobile').text('');
                    $('#code').text('');

                    console.warn('No employee data found for selected ID.');
                }

                // Set the imageUrl as the source of the image
                $('#user-photo').attr('src', '');

            },
            error: function(error) {
                // Handle the error response
                console.error('Error fetching data:', error);
            }
        });
    }

});
</script>
<script>
const ticketRadio = document.getElementById('ticketRadioBtn');
const templateRadio = document.getElementById('templateRadioBtn');

const templateSection = document.querySelector('.templateSection');
const ticketSection = document.querySelector('.ticketSection');

const tasksSection = document.getElementById('tasksSection');

ticketRadio.addEventListener('change', function() {
    if (this.checked) {
        templateSection.style.display = 'none';
        ticketSection.style.display = 'block';
        tasksSection.innerHTML = '';
        $('#templateName').val('');
        $('#log_ticket_subject').val('');
        $('#frequency').val('Once');
        $('#add_subject_text').val('');
    }
});

templateRadio.addEventListener('change', function() {
    if (this.checked) {
        templateSection.style.display = 'block';
        tasksSection.innerHTML = '';
        $('#templateName').val('');
        $('#task-header').css('display', 'none');
        $('#add_subject_text').css('display', 'none');
        $('#log_ticket_subject').val('');
        $('#add_subject_text').val('');

        ticketSection.style.display = 'none';
        $('#frequency').val('Once');
        $('#recurring_till').val('');
        $('#weekday').val('');
        $('#start_date').val('');

        const frequencySection = document.querySelector('.frequency-section');
        frequencySection.style.display = 'none';
        $('#weekday-section').css('display', 'none');
        $('#monthly-section').css('display', 'none');

    }
});


$('#templateName').change(function(e, value) {
    var templateId = $(this).val();
    $('#log_ticket_subject').val($('#templateName option:selected').text());
    $.ajax({
        url: '{{ route("get.ticket.tasks") }}',
        dataType: 'json',
        delay: 250,
        data: {
            templateId: templateId
        }, // Pass the teamId as a parameter
        success: function(response) {
            // Clear previous content if needed
            $('#tasksSection').empty();
            if (response && response.length > 0) {

                $('#task-header').css('display', 'block');
                $('#add_subject_text').css('display', 'block');

                response.forEach(function(task) {
                    var taskHtml = `
                    <li class="parent-menu">
                        <label class="cus-container">
                            <input type="checkbox" class="parent-checkbox" name="link_code[]" value="${task.TASK_NAME}" checked>
                            <span class="checkmark"></span>
                            ${task.TASK_NAME}
                        </label>
                    </li>
                `;
                    $('#tasksSection').append(taskHtml);
                });
            } else {
                // Hide the task header if no tasks are available
                $('#task-header').css('display', 'none');
                $('#add_subject_text').css('display', 'none');
            }

        },
        error: function(xhr, status, error) {
            // Handle errors here
            console.error(xhr, status, error);
        }
    });
});

$('#frequency').change(function(e, value) {
    const frequencyValue = $('#frequency').val();
    const frequencySection = document.querySelector('.frequency-section');

    const recurringTillField = document.getElementById('recurring_till');
    const weekdayField = document.getElementById('weekday');
    const startDateField = document.getElementById('start_date');

    if (frequencyValue === 'Once') {
        frequencySection.style.display = 'none';
        $('#weekday-section').css('display', 'none');
        $('#monthly-section').css('display', 'none');

        recurringTillField.removeAttribute('required');
        weekdayField.removeAttribute('required');
        startDateField.removeAttribute('required');

        $('#recurring_till').val('');
        $('#weekday').val('');
        $('#start_date').val('');
    }
    if (frequencyValue === 'Daily') {
        frequencySection.style.display = 'block';
        $('#weekday-section').css('display', 'none');
        $('#monthly-section').css('display', 'none');

        recurringTillField.setAttribute('true', 'required');
        weekdayField.removeAttribute('required');
        startDateField.removeAttribute('required');

        $('#recurring_till').val('');
        $('#weekday').val('');
        $('#start_date').val('');
    }
    if (frequencyValue === 'Weekly') {
        frequencySection.style.display = 'block';
        $('#weekday-section').css('display', 'block');
        $('#monthly-section').css('display', 'none');

        recurringTillField.setAttribute('required', 'required');
        weekdayField.setAttribute('required', 'required');
        startDateField.removeAttribute('required');

        $('#recurring_till').val('');
        $('#weekday').val('');
        $('#start_date').val('');
    }
    if (frequencyValue === 'Monthly') {
        frequencySection.style.display = 'block';
        $('#weekday-section').css('display', 'none');
        $('#monthly-section').css('display', 'block');

        startDateField.setAttribute('required', 'required');
        recurringTillField.setAttribute('required', 'required');
        weekdayField.removeAttribute('required');

        $('#recurring_till').val('');
        $('#weekday').val('');
        $('#start_date').val('');
    }
});

$(document).ready(function() {
    // Initially remove the required attribute as 'Ticket' is checked by default
    $('#templateName').prop('required', false);

    // Add change event listener for radio buttons
    // $('input[name="selectionType"]').on('change', function() {
    //     if ($('#templateRadioBtn').is(':checked')) {
    //         // If 'Template' radio button is selected, make the select required
    //         $('#templateName').prop('required', true);
    //     } else {
    //         // Otherwise, remove the required attribute
    //         $('#templateName').prop('required', false);
    //     }
    // });
});
</script>
<script>
    let isEngineer = "<?php echo userRoleName(); ?>" === "Engineer";
    let toggleBtn = $("#ticketsTasksViewToggleBtn");

    // If Engineer, ensure the toggle is checked and show tasks view
    if (isEngineer) {
        toggleBtn.prop("checked", true); // Ensure toggle is checked
        $("#toggleContainer").css('display','none'); // Disable the toggle
        $("#ticketView").fadeOut('fast', function () {
            $("#taskView").fadeIn('fast');
        });
        $("#ticketView").hide();
        $("#taskView").show();
        $("#tasksTableFilterBtn").show();

        $("#ticketsTableFilterBtn").hide();
        $("#ticketsTableClearFilterBtn").hide();
        $("#logTicketBtn").hide();        
    }

</script>
@endsection