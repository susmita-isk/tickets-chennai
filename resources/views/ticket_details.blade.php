@extends('layouts.main.app')

@section('page-title', 'Ticket Details')

@section('css-content')
<link rel="stylesheet" href="{{asset('public/dist/css/ticket_details.css')}}">
<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
.sidebar-mini.sidebar-collapse .main-sidebar,
.sidebar-mini.sidebar-collapse .main-sidebar::before {
    margin-left: 0;
    width: 2.2rem;
}
</style>
@endsection


@section('breadcrumb-menu')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"></li>
        <li class="breadcrumb-item">
            <a href="#"
                onclick="document.referrer ? window.location.href = document.referrer : window.location.href = '{{ route('tickets') }}'">
                Tickets
            </a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">{{ request('ticketNumber') }}</li>
    </ol>
</nav>
@endsection

@section('page-content')
<div class="container-fluid">
    <div id="actionBtnsContainer">
        <button class="btn tickets-action-btn" id="filterTicketsModalBtn" data-target="#filterTicketTasksModal"
            data-toggle="modal" title="Filter" style="display:none;">
            <img src="{{asset('public/img/icons/filter.png')}}" alt="Filter Categories">
        </button>
        <button class="btn tickets-action-btn" id="addTaskModalBtn" data-target="#addTaskModal" data-toggle="modal"
            title="Add Task" style="display:none;">
            <i class="fas fa-plus"></i> Add
        </button>
    </div>
    <div id="ticketLabelsContainer">
        <i class="far fa-envelope"></i>
        <span class="badge badge-light-gray">{{ $data['priority'] ?? ''}}</span>
        @if(is_null($data['technician']))
        <span class="badge badge-tkts-danger">Not Assigned</span>
        @else
        @if($data['status'] == 'Completed' || $data['status'] == 'Closed')
        @if($data['slaBreach'] == 'N')
        <span class="badge badge-tkts-success">{{ $data['progress'] ?? '' }}</span>
        @else
        <span class="badge badge-tkts-danger">{{ $data['progress'] ?? '' }}</span>
        @endif
        @else
        <span class="badge badge-tkts-danger">{{ $data['progress'] ?? '' }}</span>
        @endif
        @endif



        @if($data['status'] == 'Completed' || $data['status'] == 'Closed')
        Time Left
        @else
        Time Left:
        <div id="countdown"></div>
        @endif

    </div>
    <div id="tabsContainer">
        <ul class="nav nav-tabs tickets-nav-tabs" id="ticketDetailsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link tickets-tab-link active" id="ticketDetailsTab" type="button" data-toggle="tab"
                    data-target="#ticketDetailsTabPane" aria-controls="ticketDetailsTabPane" aria-selected="true">
                    <i class="fas fa-list"></i> Details
                </button>
            </li>
            @if($data['taskNo'] == 0)
            <li class="nav-item" role="presentation">
                <button class="nav-link tickets-tab-link" id="ticketTasksTab" type="button" data-toggle="tab"
                    data-target="#ticketTasksTabPane" aria-controls="ticketTasksTabPane">
                    <i class="fas fa-list"></i> Tasks
                </button>
            </li>
            @endif
        </ul>
        <div class="tab-content" id="ticketDetailsTabContent">
            {{-- Ticket Details Tab Pane --}}
            <div class="tab-pane fade show active" id="ticketDetailsTabPane" role="tabpanel"
                aria-labelledby="ticketDetailsTab">
                <div id="ticketDetailsContainer" class="card">
                    <div class="card-body p-3">
                        <div id="ticketMainDetailsHeader" class="mb-3">
                            <h2 id="ticketSubject" style="color: #3498db;">
                                {{ $data['subject'] }} &nbsp;&nbsp;
                                @if($hasActiveAttachments)
                                <i class="fas fa-link" style="color: green;cursor: pointer;"
                                    onClick="fetchAttachmentsAndAppendToModal({{ $data['ticketId'] }}, '{{ $data['ticketNumber'] }}',{{ $data['ticketId'] }})"></i>
                                @endif
                            </h2>
                            <div> By <span id="headerRequesterName"
                                    style="color: #2ecc71;">{{ $data['requester'] ?? '' }}</span>
                                <span id="headerRequesterDesignation"></span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-mobile-alt mr-2" style="color: #f39c12;"></i>
                                <span id="requesterMobile" class="mr-3" style="color: #333;">Mobile Number</span>
                                <i class="far fa-envelope mr-2" style="color: #e74c3c;"></i>
                                <span id="requesterEmail" style="color: #333;">Email Address</span>
                            </div>
                            <div id="description" style="margin-top: 10px; color: #555;">
                                Description: {{$data['description'] ?? ''}}
                            </div>
                        </div>
                    </div>
                </div>


                <div id="ticketDetailsCardsContainer">
                    <div class="row">
                        <div class="col-sm-6 mb-3">
                            <div class="card ticket-details-card">
                                <div class="card-header">
                                    <h5 class="card-title">Assignment</h5>
                                    <button class="ticket-details-card-header-btn"
                                        onclick="assign({{  $data['ticketId'] }})">
                                        <img src="{{asset('public/img/icons/edit-btn.png')}}" alt="Assign" height="24">
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-6"><strong>Mode</strong></div>
                                        <div class="col-6">{{ $data['mode']??''}}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-6"><strong>Technician</strong></div>
                                        <div class="col-6">{{ $data['technician']??''}}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-6"><strong>Created By</strong></div>
                                        <div class="col-6">{{ $data['createdBy']??''}}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-6"><strong>Created On</strong></div>
                                        <div class="col-6">{{ $data['createdOn']??''}}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-6"><strong>Assigned By</strong></div>
                                        <div class="col-6">{{ $data['assignedBy']??''}}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-6"><strong>Assigned On</strong></div>
                                        <div class="col-6">{{ $data['assignedOn']??''}}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-6"><strong>Closed By</strong></div>
                                        <div class="col-6">{{ $data['closedBy']??''}}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-6"><strong>Closed On</strong></div>
                                        <div class="col-6">{{ $data['closedOn']??''}}</div>
                                    </div>
                                    <!-- <div class="row">
                                        <div class="col-6"><strong>Due By</strong></div>
                                        <div class="col-6">{{ $data['dueDate']??''}}</div>
                                    </div> -->
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="card ticket-details-card">
                                <div class="card-header">
                                    <h5 class="card-title">Categorization</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <strong>Team</strong>
                                        </div>
                                        <div class="col-6">{{ $data['team']??''}}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <strong>Ticket Type</strong>
                                        </div>
                                        <div class="col-6">{{ $data['ticketType']??''}}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <strong>Category</strong>
                                        </div>
                                        <div class="col-6">{{ $data['category']??''}}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <strong>Subcategory</strong>
                                        </div>
                                        <div class="col-6">{{ $data['subCategory']??''}}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <strong>Item Type</strong>
                                        </div>
                                        <div class="col-6">{{ $data['itemType']??''}}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <strong>Item</strong>
                                        </div>
                                        <div class="col-6">{{ $data['item']??''}}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <strong>Asset</strong>
                                        </div>
                                        <div class="col-6">{{ $data['trust']??''}}-{{ $data['asset']??''}}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Feedback Container -->
                        <div class="col-sm-6 mb-3">
                            <div class="card ticket-details-card">
                                <div class="card-header">
                                    <div class="row">
                                        <h5 class="col-5 card-title">Feedback</h5>
                                        <div class="col-6 text-right">{{ $data['feedbackDate']??''}}</div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3 mx-auto">
                                        @if(!empty($data['feedbackPoint']) && $data['feedbackPoint'] > 0)
                                        @for ($i = 0; $i < $data['feedbackPoint']; $i++) <i class="fa fa-star"
                                            style="color: #d38f1b;"></i>
                                            @endfor
                                            @else
                                            {{ $data['feedbackPoint'] ?? '' }}
                                            @endif
                                    </div>
                                    <div class="row mb-3">
                                        <!-- <div class="col-6">
                                            <strong>Remarks</strong>
                                        </div> -->
                                        <div class="col-6">{{ $data['feedbackRemarks']??''}}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Feedback Container -->



                        <div class="col-sm-6 mb-3">
                            <div class="card ticket-details-card">
                                <div class="card-header">
                                    <div class="row">
                                        <h5 class="col-5 card-title">Ticket Assignment</h5>
                                        <div class="col-6 text-right">{{ $data['feedbackDate']??''}}</div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3 mx-auto">
                                        <ul>

                                            @forEach($logUpdates as $logUpdate )
                                            @if($logUpdate->ALLOCATED_TO!= null)
                                            <li class="mb-2">Assigned to <strong>{{ $logUpdate->ALLOCATED_TO}}</strong>
                                                on {{ $logUpdate->ALLOCATED_ON}}</li>
                                            @endif
                                            @endforeach
                                        </ul>
                                    </div>
                                    <div class="row mb-3">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Tagged Tickets List --}}
                    <!-- Tagged Tickets List -->

                    <!-- <div class="card ticket-details-card" id="taggedTicketsCard">
                        <div class="card-header text-center">Tagged Tickets</div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="taggedTicketsTable" class="taskTable table">
                                    <thead>
                                        <tr>
                                            <th>Tickets ID</th>
                                            <th>Requester Name</th>
                                            <th>Requested On</th>
                                            <th>Subject</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>  -->


                    {{-- Updates on Ticket --}}
                    <div id="updatesContainer">
                        <div class="row my-2">
                            <div class="col-md-8">
                                <h3 id="updatesHeader">Updates</h3>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 mb-3">
                                <select name="updates" id="updatesForTicketID" class="form-select custom-select">
                                    <option value="{{ $data['ticketId'] }}">{{ $data['ticketNumber'] }}</option>
                                    @foreach($tasks as $task)
                                    <option value="{{ $task['TICKET_ID'] }}">
                                        {{ $task['TICKET_NO'] }}-{{ $task['TASK_NO'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div id="updatesBody">

                        </div>

                    </div>
                </div>
            </div>

            {{-- Ticket Tasks Tab Pane --}}
            <div class="tab-pane tickets-tab-pane fade" id="ticketTasksTabPane" role="tabpanel"
                aria-labelledby="ticketTasksTab">
                <div class="tickets-tab-pane-content">
                    <div class="row">
                        <div class="col-12">
                            <div class="table-container mt-2">
                                <div class="table-responsive">
                                    <table class="table table-hover tickets-main-table" id="ticketTasksTable"
                                        style="width: 100%">
                                        <thead>
                                            <tr>
                                                <th>Task ID</th>
                                                <th>Subject</th>
                                                <th>Assigned On</th>
                                                <th>Closed On</th>
                                                <th>Assigned To</th>
                                                <th>Progress</th>
                                                <th>Amount</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Table body content will be dynamically populated here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
</div>

<!-- Begin Modals -->
{{-- Begin Assign Request Modal --}}
<!-- Begin Assign Request Modal -->
<div class="modal tickets-modal fade" id="assignRequestModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Assign Request</h6>
                <button class="close" data-dismiss="modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="assignTicketForm">
                    @csrf
                    <input type="hidden" name="ticketId" id="ticketIdForAssignment">
                    <div class="row">
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
                                <label for="assign_ticket_sub_type">Subcategory</label>
                                <select id="subCategoryAssign" name="subcategoryId" class="form-control">

                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="assign_ticket_team">Item</label>
                                <select id="itemTypeAssign" name="itemTypeId" class="form-control">

                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="assign_ticket_team">Sub Item</label>
                                <select id="itemAssign" name="itemId" class="form-control">

                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="assign_ticket_technician">Technician</label>
                                <select id="technicianAssign" name="technicianId" class="form-control" required>

                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="assign_ticket_due_by">Due By</label>
                                <input type="text" class="form-control" id="datepicker" name="dueDate"
                                    placeholder="Chhose Due Date" disabled>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="categorize_task_asset_id" class="control-label mb-1">Asset ID</label>
                                <input type="text" class="form-control" id="assetIdAssign" name="assetId"
                                    placeholder="Asset ID">
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
                                </select>
                            </div>
                        </div>
                        <div class="col-12 text-center mb-3">
                            <button type="submit" class="btn tickets-modal-submit-btn">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
{{-- End Assign Request Modal --}}

{{-- Begin Filter Tasks Modal --}}
<div class="modal tickets-modal fade" id="filterTicketTasksModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                Filter
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-6 mb-2-5">
                        <input type="text" class="form-control" id="filterTicketNo" name="" placeholder="Ticket No">
                    </div>
                    <div class="col-sm-6 mb-2-5">
                        <select class="form-control employeeFilter" id="userNameInput" name="userNameInput"
                            placeholder="Request Name">
                        </select>
                    </div>
                    <div class="col-sm-6 mb-2-5">
                        <input type="text" class="form-control" id="oldTicketInput" name="oldTicketInput"
                            placeholder="Old Ticket No">
                    </div>
                    <div class="col-sm-6 mb-2-5">
                        <select class="form-control department" id="departmentInput" name="departmentInput"
                            placeholder="Searching Dept">
                        </select>
                    </div>
                    <div class="col-sm-6 mb-2-5">
                        <select name="technicianInput" id="technicianInput" class="form-control technician">
                        </select>
                    </div>
                    <div class="col-sm-6 mb-2-5">
                        <input type="text" class="form-control datepicker" id="requestedFromInput"
                            name="requestedFromInput" placeholder="Requested On (From)">
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
                        </select>
                    </div>
                    <div class="col-sm-6 mb-2-5">
                        <input type="text" class="form-control datepicker" id="requestedToInput" name="requestedToInput"
                            placeholder="Requested On (To)">
                    </div>
                    <div class="col-sm-6 mb-2-5">
                        <select class="form-control" id="statusSelect" name="status">
                            <option value="">Choose Status</option>
                            <option value="New">New</option>
                            <option value="OPEN">Open</option>
                            <option value="Completed">Completed</option>
                            <option value="CLOSED">Closed</option>
                        </select>
                    </div>
                    <div class="col-sm-6 mb-2-5">
                        <select class="form-control" id="progressSelect" name="progress">
                            <option value="">Choose Progress</option>
                            <option value="New">New</option>
                            <option value="In Progress">In Progress</option>
                            <option value="On Hold">On Hold</option>
                            <option value="Reopened">Reopened</option>
                            <option value="Cancelled">Cancelled</option>
                            <option value="Transferred">Transferred</option>
                            <option value="Deferred">Deferred</option>
                            <option value="Resolved">Resolved</option>
                        </select>

                    </div>

                    <div class="col-sm-12 mb-3 text-right">
                        <button type="submit" class="btn tickets-modal-submit-btn" id="filterBtnTasks">Apply</button>
                        <button type="reset" class="btn tickets-modal-submit-btn" id="clearBtnTasks">Clear All</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- End Filter Tasks Modal --}}

{{-- Begin Add Task Modal --}}
<div class="modal tickets-modal fade" id="addTaskModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Add Task</h6>
                <button class="close" data-dismiss="modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="addTask">
                    @csrf
                    <input type="hidden" name="ticketId" value="{{ $data['ticketId'] }}">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="task_subject">Subject</label>
                                <input type="text" name="subject" class="form-control" id="task_subject"
                                    placeholder="Enter subject" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="task_description">Description</label>
                                <textarea name="description" class="form-control" id="task_description"
                                    rows="4"></textarea>
                            </div>
                        </div>
                        <div class="col-12 mb-3 text-center">
                            <button type="submit" class="btn tickets-modal-submit-btn">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
{{-- End Add Task Modal --}}

{{-- Begin Edit Task Modal --}}
<div class="modal tickets-modal fade" id="editTicketTaskModal">
    <div class="modal-dialog tickets-modal-ml">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Edit Task</h6>
                <span id="edit-task-status-header">Close</span>
                <button class="close" data-dismiss="modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="editTicketTask">
                    @csrf
                    <input type="hidden" id="edit_task_id" name="ticketId">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="edit_task_type">Subject</label>
                                <input type="text" class="form-control" id="edit_task_subject" name="subject"
                                    placeholder="Subject">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="edit_task_description">Description</label>
                                <textarea name="description" id="edit_task_description" rows="4"
                                    class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="taskTeamName">Team</label>
                                <select name="taskTeamName" id="taskTeamName" class="form-control">
                                    <option value="">Select Team</option>
                                    @foreach ($teams as $item)
                                    <option value="{{ $item['teamId'] }}">{{ $item['teamName'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="edit_task_technician">Technician</label>
                                <select name="technician" id="edit_task_technician" class="form-control" required>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="taskTicketType">Ticket Type</label>
                                <select name="taskTicketType" id="taskTicketType" class="form-control" required>
                                    <option value="">Please Select</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="edit_task_category">Category</label>
                                <select name="category" id="edit_task_category" class="form-control">
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="edit_task_sub_category">Subcategory</label>
                                <select name="subcategory" id="edit_task_sub_category" class="form-control">
                                    <option value="">Request</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="edit_task_cost">Cost</label>
                                <input type="number" name="cost" id="edit_task_cost" placeholder="0.00"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="row">
                                <div class="col-12">
                                    <label for="edit_task_item_type">Item</label>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <select name="itemType" id="edit_task_item_type" class="form-control">
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <select name="item" id="edit_task_item" class="form-control">
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- <div class="col-sm-6">
                        <div class="form-group">
                            <label for="edit_task_status">Status</label>
                            <select name="" id="edit_task_status" class="form-control">
                                <option value="">Please Select</option>
                            </select>
                        </div>
                    </div> --}}
                        <div class="col-sm-6">
                            <div class="row">
                                <div class="col-12">
                                    <label for="edit_task_asset_trust">Asset ID</label>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <select class="form-control trust" name="trust_code" id="trust">
                                            <option value="">Choose Trust</option>

                                        </select>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="assetId" id="assetid"
                                            placeholder="Asset ID">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 text-center mb-2">
                            <button type="submit" class="btn tickets-modal-submit-btn">Assign</button>
                        </div>
                    </div>
                    <form>
            </div>
        </div>
    </div>
</div>
{{-- End Edit Task Modal --}}

{{-- Begin Tag Task Modal --}}
<div class="modal tickets-modal fade" id="tagTaskToTicketModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                Tag Task - <span id="taskIdToTag"></span>
                <button class="close" data-dismiss="modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form action="" method="post" accept-charset="utf-8">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="task_tagged_ticket_id">Ticket ID</label>
                                {{-- Use select2 for enabling searching --}}
                                <select name="" id="task_tagged_ticket_id" class="form-control">
                                    <option value="">20231202554</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 mb-2 text-center">
                            <button type="submit" class="btn tickets-modal-submit-btn">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
{{-- End Tag Task Modal --}}

<!-- Attachment Modal -->
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
            <div class="modal-body attachment-modal-body">
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

<!-- End Modals -->
@endsection

@section('js-content')
<script src="{{asset('public/dist/js/ticket_details.js')}}"></script>
<!-- Flatpickr JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
$(function() {

    var dueDate = @json($data['dueDate']);
    var sla = @json($data['sla']);

    if (sla) {
        // Start the countdown for a specific date
        startCountdown(sla, "countdown");
    }


    flatpickr(".datepicker", {
        dateFormat: "d-M-Y"
    });

    $.ajax({
        url: '{{ route("employee.get") }}',
        method: 'POST', // Change to POST
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        data: {
            // Include any data you want to send with the POST request
            employeeId: '{{ $data["employeeId"] }}',
            // ...
        },
        success: function(response) {
            // Handle the success response


            $('#headerRequesterDesignation').text('( ' + response[0].designation + ' - ' + response[
                0].departmentName + ' )');
            $('#requesterEmail').text(response[0].emailId);
            $('#requesterMobile').text(response[0].mobile);


        },
        error: function(error) {
            // Handle the error response
            console.error('Error fetching data:', error);
        }
    });


    /* ---------------------------------------------------------------------------------- */

    $('.technician').select2({
        placeholder: 'Technician Name',
        dropdownParent: '#assignTicketModal',
        ajax: {
            url: '{{ route("technicians.get") }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    search: params.term,
                    type: 'public'
                };
            },
            processResults: function(data) {
                var options = data.map(function(technician) {
                    return {
                        id: technician
                            .EMPLOYEE_ID, // Adjust with your actual technician ID column if Blank means selection is not happeining
                        text: technician
                            .USER_NAME, // Adjust with your actual technician name column
                    };
                });

                return {
                    results: options
                };
            },
        },
    });

    /* ---------------------------------------------------------------------------------- */

    $("#addTask").submit(function(e) {

        //prevent Default functionality
        e.preventDefault();

        var formData = new FormData(this);

        // Disable submit button before sending the request
        $("#addTask").find(':submit').prop('disabled', true);

        $.ajax({
            method: "post",
            url: '{{route("task.add")}}',
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
                        message: 'Task Added'

                    });

                    $(':input', '#addTask')
                        .not(':button, :submit, :reset, :hidden')
                        .val('')
                        .prop('checked', false)
                        .prop('selected', false);
                }
                else{
                    iziToast.show({

                        title: 'Error',
                        position: 'topRight',
                        color: 'red',
                        message: 'Ticket Already Closed !!'

                    });

                    $(':input', '#addTask')
                        .not(':button, :submit, :reset, :hidden')
                        .val('')
                        .prop('checked', false)
                        .prop('selected', false);
                }

                // Enable submit button after receiving the response
                $("#addTask").find(':submit').prop('disabled', false);

                $("#addTaskModal").modal("hide");


                $('.taskTable').DataTable().ajax.reload(null, false);
                $('#ticketTasksTable').DataTable().ajax.reload(null, false);

            }
        });

    });

    /* ---------------------------------------------------------------------------------- */

    var table = $('.taskTable').DataTable({
        processing: true,
        serverSide: true,
        paging: false,
        dom: "<'row'<'col-sm-12'>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        ajax: {
            url: "{{ route('tasks') }}",
            data: function(d) {

                d.ticketId = '{{ request('
                ticketId ') }}'

            }
        },
        columns: [{
                data: 'ticketNumber',
                name: 'id',
                className: 'text-bold'
            },
            {
                data: 'requester',
                name: 'requester',
                className: 'text-bold'
            },
            {
                data: 'requestedOn',
                name: 'requestedOn',
                className: 'text-bold'
            },
            {
                data: 'subject',
                name: 'subject',
                className: 'text-bold'
            },


        ]
    });

    /* ---------------------------------------------------------------------------------- */

    var ticketTasksTable = $('#ticketTasksTable').DataTable({
        processing: true,
        serverSide: true,
        paging: false,
        dom: "<'row'<'col-sm-12'>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        ajax: {
            url: "{{ route('tasks') }}",
            data: function(d) {

                d.ticketId = '{{ request("ticketId") }}';
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

            }
        },
        columns: [{
                data: 'ticketNumber',
                name: 'id',
                className: 'text-bold'
            },
            {
                data: 'subject',
                name: 'subject',
                className: 'text-bold'
            },
            {
                data: 'assignedOn',
                name: 'assignedOn',
                className: 'text-bold'
            },
            {
                data: 'closedOn',
                name: 'closedOn',
                className: 'text-bold'
            },
            {
                data: 'assignedTo',
                name: 'assignedTo',
                className: 'text-bold'
            },
            {
                data: 'status',
                name: 'status',
                className: 'text-bold'
            },
            {
                data: 'amount',
                name: 'amount',
                className: 'text-bold'
            },
            {
                data: 'action',
                name: 'action',
                className: 'text-bold'
            },


        ]
    });

    $("#filterBtnTasks").on('click', function() {

        $('#ticketTasksTable').DataTable().ajax.reload(null, false);

        $('#filterTicketTasksModal').modal('hide');


    });

    $("#clearBtnTasks").on('click', function() {

        $('#filterTicketNo').val('');
        $('#userNameInput').val(null).trigger('change');
        $('#oldTicketInput').val('');
        $('#departmentInput').val(null).trigger('change');
        $('#technicianInput').val(null).trigger('change');
        $('#modeInput').val('');
        $('#requestedFromInput').val('');
        $('#requestedToInput').val('');
        $('#statusSelect').val('');
        $('#progressSelect').val('');

        $('#ticketTasksTable').DataTable().ajax.reload(null, false);

        $('#filterTicketTasksModal').modal('hide');


    });

    /* ---------------------------------------------------------------------------------- */


    $('#updatesForTicketID').on('change', function() {
        // Get the selected value
        var selectedTicketId = $(this).val();

        // Send an AJAX request to get updates for the selected ticket
        $.ajax({
            type: 'GET',
            url: '{{ route("tasks.updates") }}', // Replace with the actual URL for your AJAX endpoint
            data: {
                ticketId: selectedTicketId
            },
            success: function(data) {
                // Clear existing updates
                $('#updatesBody').empty();

                // Check if data is an array or has an array-like structure
                if (Array.isArray(data['data']) || (typeof data['data'] === 'object' &&
                        data['data'] !== null && 'forEach' in data['data'])) {
                    // Iterate through the updates and append HTML
                    data['data'].forEach(function(update) {
                        // Handle null value for update.reason
                        var reasonHtml = (update.reason !== null) ?
                            '<p class="update-message text-small">' + update
                            .reason + '</p>' : '';

                        // Your update HTML generation here, including reasonHtml
                        var updateHtml = '<div class="update-card">' +
                            '<div class="row"><div class="col-md-6  update-in-progress">' +
                            update.status +
                            '</div>' +
                            '<div class="col-md-6 text-right update-text">' +
                            '<span class="update-technician-name">' +
                            update.technician + ' on ' + update.logDate +
                            '</span></div></div>' +
                            '<p class="update-for-detail mt-1">' + update
                            .description + '&nbsp; &nbsp;' + (update
                                .updateAttachment ?
                                '<a href="' + update.updateAttachment +
                                '" target="_blank">' +
                                '<i class="fas fa-link" style="color : green;"></i></a>' :
                                '') + '</p>' +
                            reasonHtml +
                            '</div>' + '<br>'; // Adding a line break here

                        $('#updatesBody').append(updateHtml);
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
    });

    $('#updatesForTicketID').trigger('change');

    /* ---------------------------------------------------------------------------------- */

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
    }).on('select2:select', function(e) {
        var categoryId = e.params.data.id;

        // Subcategory Dropdown Setup
        $('.subcategory').select2({
            placeholder: 'Subcategory Name',
            ajax: {
                url: '{{ route("subcategories.get") }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        categoryId: categoryId,
                        search: params.term,
                        type: 'public'
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
        }).on('select2:select', function(e) {
            var subcategoryId = e.params.data.id;

            // Items Dropdown Setup
            $('.item').select2({
                placeholder: 'Item Type',
                ajax: {
                    url: '{{ route("items.get") }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            subcategoryId: subcategoryId,
                            search: params.term,
                            type: 'public'
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
            }).on('select2:select', function(e) {
                var itemId = e.params.data.id;

                // Subitems Dropdown Setup
                $('.subitem').select2({
                    placeholder: 'Item Name',
                    ajax: {
                        url: '{{ route("subitems.get") }}',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                itemTypeId: itemId,
                                search: params.term,
                                type: 'public'
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
            });
        });
    });

    $('.technician').select2({
        placeholder: 'Technician Name',
        ajax: {
            url: '{{ route("technicians.get") }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    search: params.term,
                    type: 'public'
                };
            },
            processResults: function(data) {
                var options = data.map(function(technician) {
                    return {
                        id: technician
                            .EMPLOYEE_ID, // Adjust with your actual technician ID column
                        text: technician
                            .USER_NAME, // Adjust with your actual technician name column
                    };
                });

                return {
                    results: options
                };
            },
        },
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
                        text: trust
                            .trustName, // Adjust with your actual trust name column
                    };
                });

                return {
                    results: options
                };
            },
        },
    });



    /* ---------------------------------------------------------------------------------- */

    $("#assignTicketForm").submit(function(e) {

        //prevent Default functionality
        e.preventDefault();

        var formData = new FormData(this);

        formData.append('ticketId', @json($data['ticketId']));

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

                    // Reload the page
                    window.location.reload();
                }

                $('#assignRequestModal').modal('hide');

                $('.category').val(null).trigger('change');
                $('.subcategory').val(null).trigger('change');
                $('.item').val(null).trigger('change');
                $('.subitem').val(null).trigger('change');
                $('.technician').val(null).trigger('change');

                $('.taskTable').DataTable().ajax.reload(null, false);
                $('#ticketTasksTable').DataTable().ajax.reload(null, false);

            }
        });

    });

    $('.subcategoryFilter').select2({
        placeholder: 'Subcategory Name',
        ajax: {
            url: '{{ route("subcategories.get") }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    search: params.term,
                    type: 'public'
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
                    type: 'public'
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
                    type: 'public'
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

    // Initialize Select2
    $('.employeeFilter').select2({
        placeholder: 'Requester Name',
        ajax: {
            url: '{{ route("employees.get") }}',
            dataType: 'json',
            delay: 250, // add a delay if needed
            data: function(params) {
                return {
                    search: params.term,
                    type: 'public'
                };
            },
            processResults: function(data) {
                // Map the data to the format expected by Select2
                var options = data.map(function(employee) {
                    return {
                        id: employee.hrEmployeeID,
                        text: employee.employeeName + ' (' + employee.hrEmployeeID +
                            ')',
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

    // Initialize Select2
    $('.department').select2({
        placeholder: 'Department Name',
        ajax: {
            url: '{{ route("departments.get") }}',
            dataType: 'json',
            delay: 250, // add a delay if needed
            data: function(params) {
                return {
                    search: params.term,
                    type: 'public'
                };
            },
            processResults: function(data) {
                // Map the data to the format expected by Select2
                var options = data.map(function(department) {
                    return {
                        id: department.deptCode,
                        text: department.deptName,
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

    $("#editTicketTask").submit(function(e) {

        //prevent Default functionality
        e.preventDefault();

        var formData = new FormData(this);
        formData.append("taskTeamName", $('#taskTeamName option:selected').text());

        $.ajax({
            method: "post",
            url: '{{route("task.update")}}',
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
                        message: 'Task Updated'

                    });

                    $(':input', '#editTicketTask')
                        .not(':button, :submit, :reset, :hidden')
                        .val('')
                        .prop('checked', false)
                        .prop('selected', false);
                }

                $('#editTicketTaskModal').modal('hide');

                $('.category').val(null).trigger('change');
                $('.subcategory').val(null).trigger('change');
                $('.item').val(null).trigger('change');
                $('.subitem').val(null).trigger('change');
                $('.technician').val(null).trigger('change');
                $('.trust').val(null).trigger('change');

                $('.taskTable').DataTable().ajax.reload(null, false);
                $('#ticketTasksTable').DataTable().ajax.reload(null, false);

            }
        });

    });


    $('#taskTeamName').change(function(e, value) {
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
                $('#edit_task_technician').empty();

                // Add a placeholder option
                $('#edit_task_technician').append(
                    '<option value="">Select Technician</option>');

                // Append each technician to the edit_task_technician element
                technicians.forEach(function(technician) {
                    $('#edit_task_technician').append('<option value="' + technician
                        .EMPLOYEE_ID + '">' + technician.USER_NAME + '</option>'
                    );
                });

                $('#edit_task_technician').val(value)
            },
            error: function(xhr, status, error) {
                // Handle errors here
                console.error(xhr, status, error);
            }
        });
    });

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
                    $('#subCategoryAssign').append('<option value="' +
                        subcategory
                        .subCategoryId + '">' + subcategory
                        .subCategoryName +
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
                        .itemTypeId + '">' + item.itemTypeName + '</option>'
                    );
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
                var subitems =
                    data; // Adjust this based on your actual response structure

                // Clear previous content if needed
                $('#itemAssign').empty();

                // Add a placeholder option
                $('#itemAssign').append('<option value="">Sub Item</option>');

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



    // Assuming there's a change event listener for the category selection
    $('#edit_task_category').on('change', function(e, value) {

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
                $('#edit_task_sub_category').empty();

                $('#edit_task_sub_category').append(
                    '<option value="">Select a Subcategory</option>');

                // Append each subcategory to the subcategoryAssign element
                subcategories.forEach(function(subcategory) {
                    $('#edit_task_sub_category').append('<option value="' +
                        subcategory.subCategoryId + '">' + subcategory
                        .subCategoryName + '</option>');
                });

                $('#edit_task_sub_category').val(value);
            },
            error: function(xhr, status, error) {
                // Handle errors here
                console.error(xhr, status, error);
            }
        });
    });

    // Assuming there's a change event listener for the subcategory selection
    $('#edit_task_sub_category').on('change', function(e, value) {

        console.log(value);

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
                $('#edit_task_item_type').empty();

                // Add a placeholder option
                $('#edit_task_item_type').append('<option value="">Item Type</option>');

                // Append each item option to the item selection
                data.forEach(function(item) {
                    $('#edit_task_item_type').append('<option value="' + item
                        .itemTypeId + '">' + item.itemTypeName + '</option>'
                    );
                });

                $('#edit_task_item_type').val(value);
            },
            error: function(xhr, status, error) {
                // Handle errors here
                console.error(xhr, status, error);
            }
        });
    });

    // Assuming there's a change event listener for the item selection
    $('#edit_task_item_type').on('change', function(e, value) {

        console.log(value);

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
                var subitems =
                    data; // Adjust this based on your actual response structure

                // Clear previous content if needed
                $('#edit_task_item').empty();

                // Add a placeholder option
                $('#edit_task_item').append('<option value="">Sub Item</option>');

                // Append each subitem to the subItemAssign element
                subitems.forEach(function(subitem) {
                    $('#edit_task_item').append('<option value="' + subitem
                        .itemId +
                        '">' + subitem.itemName + '</option>');
                });

                $('#edit_task_item').val(value);
            },
            error: function(xhr, status, error) {
                // Handle errors here
                console.error(xhr, status, error);
            }
        });
    });

    $('#taskTeamName').change(function(e, value) {

        var taskTeamName = $('#taskTeamName').val();
        $.ajax({
            url: '{{ route("get.ticket.type") }}',
            dataType: 'json',
            delay: 250,
            data: {
                teamName: taskTeamName
            },
            success: function(response) {
                // Assuming the response is an array of technician objects
                var taskTypes =
                    response; // Adjust this based on your actual response structure

                // Clear previous content if needed
                $('#taskTicketType').empty();

                // Add a placeholder option
                $('#taskTicketType').append(
                    '<option value="">Please Select</option>');

                // Append each technician to the technicianAssign element
                taskTypes.forEach(function(taskType) {
                    $('#taskTicketType').append('<option value="' + taskType
                        .TASK_TYPE_ID + '">' +
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





});

function edit(ticketId) {
    $("#edit_task_id").val(ticketId);

    category = '';
    subCategory = '';
    itemType = '';
    item = '';

    // Make an AJAX request to fetch already assigned details
    $.ajax({
        type: "GET",
        url: "{{ route('assignment.details', ['ticketId' => ':ticketId']) }}".replace(':ticketId',
            ticketId),
        data: {
            id: ticketId
        },
        success: function(response) {

            // Set ticketId value
            $('#ticketIdForAssignment').val(response.TICKET_ID);
            var taskTicketType = response.TASK_TYPE_ID;

            // Set technicianId value
            $('#edit_task_technician').val(response.TECHNICIAN_ID);
            var technicianId = response.TECHNICIAN_ID;

            function setDropdownValueByText(dropdown, text) {
                dropdown.find('option').each(function() {
                    if ($(this).text() === text) {
                        $(this).prop('selected', true);
                        dropdown.trigger('change', [technicianId]); // Trigger the change event
                        return false; // Break the loop
                    }
                });
            }

            // Set teamName value based on the label
            setDropdownValueByText($('#taskTeamName'), response.TEAM_NAME);

            setTimeout(function() {
                $('#taskTicketType').val(response.TASK_TYPE_ID);
            }, 1000);

            category = response.CATEGORY_ID;
            subCategory = response.SUB_CATEGORY_ID;
            itemType = response.ITEM_TYPE_ID;
            item = response.ITEM_ID;

            $('#edit_task_subject').val(response.SUBJECT);
            $('#edit_task_description').val(response.DESCRIPTION);
            $('#edit_task_cost').val(response.COST);
            $('#edit_task_asset_trust').val(response.ASSET_ID);
            $('#trust').val(response.TRUST_CODE);
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
            $('#edit_task_category').empty();

            $('#edit_task_category').append('<option value="">Select a Category</option>');

            // Append each category to the edit_task_category element
            categories.forEach(function(category) {
                $('#edit_task_category').append('<option value="' + category.categoryId +
                    '">' +
                    category.categoryName + '</option>');
            });

            $('#edit_task_category').val(category).trigger('change', [subCategory]);

            $('#edit_task_sub_category').val(subCategory).trigger('change', [itemType]);

            $('#edit_task_item_type').val(itemType).trigger('change', [item]);



        },
        error: function(xhr, status, error) {
            // Handle errors here
            console.error(xhr, status, error);
        }
    });


    $("#editTicketTaskModal").modal('show');
}

function assign(id) {

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

            // Set technicianId value
            $('select[name="technicianId"]').val(response.TECHNICIAN_ID);

            if (response.DUE_DATE != '') {
                // Set dueDate value
                flatpickr("#datepicker", {
                    // other options...
                    defaultDate: response.DUE_DATE,
                    dateFormat: "d-M-Y"
                });

            }

            $('#categoryAssign').val(response.CATEGORY_ID);

            category = response.CATEGORY_ID;
            subCategory = response.SUB_CATEGORY_ID;
            itemType = response.ITEM_TYPE_ID;
            item = response.ITEM_ID;

            $('#assetIdAssign').val(response.ASSET_ID);
            $('#log_ticket_call_mode').val(response.MODE);
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


    $('#ticketIdForAssignment').val(id);

    $('#assignRequestModal').modal('show');

}

function isHoliday(date) {
    return holidays.includes(date.toISOString().split('T')[0]); // Check if the date is a holiday
}
var holidays = @json($holidayList);

function startCountdown(sla, elementId) {

    // pass the holidays as an array

    var progress = @json($data['progress']);
    var sla = @json($data['sla']);
    var ticketId = @json($data['ticketId']);
    var ticketNumber = @json($data['ticketNumber']);

    // Update the countdown every 1 second
    var x = setInterval(function() {

            // var nowDate = new Date('2024-12-05 13:00:00');
            var nowDate = new Date();

            var workingStart = new Date(nowDate); // Clone the current date
            workingStart.setHours(10, 0, 0, 0); // Set to 10:00 AM today

            var workingEnd = new Date(nowDate); // Clone the current date
            workingEnd.setHours(18, 0, 0, 0);

            // Get today's date and time
            var now = nowDate.getTime();
            let timeConsumed = 0;

            // Get current hours in 24-hour format
            var currentHours = nowDate.getHours();

            if (progress === 'On Hold' || isHoliday(nowDate) || nowDate < workingStart || nowDate > workingEnd) {

                fetch(`/tickets/tickets-view/${ticketId}/${ticketNumber}/get-time-left`)
                    .then(response => response.json())
                    .then(data => {
                        const totalTimeConsumed = data; // Assuming the API returns this
                        const timeLeft = (sla * 60) - totalTimeConsumed; // SLA in minutes

                        const element = document.getElementById(elementId);

                        if (timeLeft >= 1) {
                            const hours = Math.floor(timeLeft / 60);
                            const minutes = Math.floor(timeLeft % 60);

                            element.innerHTML = `${hours}h ${minutes}m`;
                        } else {
                            element.innerHTML = "EXPIRED";
                            element.classList.add("badge", "badge-tkts-danger");
                        }
                    })
                    .catch(error => {
                        console.error("Error fetching time left:");
                    })
                    .finally(() => {
                        clearInterval(x); // Clear the timer if any
                    });

            } else {
                if (progress === 'Open' || progress === 'In Progress' || progress === 'Reopened') {
                    fetch(`/tickets/tickets-view/${ticketId}/${ticketNumber}/get-time-left`)
                        .then(response => response.json())
                        .then(data => {
                            const totalTimeConsumed = data; // Assuming the API returns this
                            console.log("Time ", totalTimeConsumed);
                            const timeLeft = (sla * 60) - totalTimeConsumed; // SLA in minutes
                            // alert(totalTimeConsumed);
                            const element = document.getElementById(elementId);

                            if (timeLeft > 0) {
                                const hours = Math.floor(timeLeft / 60);
                                const minutes = Math.floor(timeLeft % 60);

                                element.innerHTML = `${hours}h ${minutes}m`;
                            } else {
                                element.innerHTML = "EXPIRED";
                                element.classList.add("badge", "badge-tkts-danger");
                            }
                        })
                        .catch(error => {
                            console.error("Error fetching time left:", error);
                        });
                }
            }

        },
        6000);
}


function fetchAttachmentsAndAppendToModal(ticketId, ticketNumber, attachmentTicketId) {

    var attachmentTicketId = ticketId;
    // $('#attachmentModal').modal('show');

    // Replace this with your actual API endpoint to fetch attachments
    return fetch(`/tickets/tickets-view/${ticketId}/${ticketNumber}/attachments/${attachmentTicketId}`)
        .then((response) => response.json())
        .then((data) => {
            const attachments = data.data;

            // Assuming you have a modal with ID 'attachmentModal'
            const modal = document.getElementById('attachmentModal');
            const modalBody = modal.querySelector('.attachment-modal-body');

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
</script>

@endsection