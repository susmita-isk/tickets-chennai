@extends('layouts.main.app')

@section('page-title', 'Tickets')

@section('css-content')
<!-- DataTables Buttons CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.7.1/css/buttons.dataTables.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<!-- Filepond -->
<link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
<link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet" />
<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.13.3/css/selectize.default.min.css">
<link rel="stylesheet" href="{{asset('public/dist/css/tickets.css')}}">

<style>
.table {
    border-collapse: collapse;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    width: 100%;
    table-layout: auto;
}

.table th,
.table td {
    padding: 8px;
    text-align: left;
    white-space: nowrap;
    border-bottom: 1px solid #ddd;
}

.table-container {
    overflow-x: hidden;
    width: 100%;
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

.tickets-main-table {
    border: 1px solid #ccc;
}

.sidebar-mini.sidebar-collapse .main-sidebar,
.sidebar-mini.sidebar-collapse .main-sidebar::before {
    margin-left: 0;
    width: 2.2rem;
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

/* Footer styling */
footer {
    position: fixed;
    bottom: 0;
    width: 100%;
    background-color: #f8f9fa;
    text-align: center;
    padding: 10px;
    box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    /* Ensure the footer stays on top */
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
        <button class="btn tickets-action-btn" id="ticketsTableFilterBtn" data-target="#filterTicketsModal"
            data-toggle="modal" title="Filter Tickets">
            <img src="{{asset('public/img/icons/filter.png')}}" alt="">
        </button>
        <button class="btn tickets-action-btn-transparent" id="exportToExcelBtn">
            <img src="{{asset('public/img/icons/excel.png')}}" alt="Export to Excel" height="24">
        </button>
    </div>
    <div id="ticketView" class="">
        <ul class="nav nav-tabs tickets-nav-tabs" id="ticketsTablesTabMenu" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link tickets-tab-link active" id="tickets-main-tab-link" type="button"
                    data-toggle="tab" data-target="#ticketsMainTabPanel" aria-controls="ticketsMainTabPanel"
                    aria-selected="true">
                    <i class="fas fa-bars"></i> Feedback Report
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
                                <div class="table-responsive" style="overflow-x: hidden;">
                                    <table class="table table-hover table-striped tickets-main-table"
                                        id="ticketsAllTable" style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>Ticket No</th>
                                                <th>Team</th>
                                                <th class="d-none">Created On</th>
                                                <th class="d-none">Assigned On</th>
                                                <th class="d-none">Subject</th>
                                                <th class="d-none">Category</th>
                                                <th class="d-none">Subcategory</th>
                                                <th class="d-none">Effort</th>
                                                <th>Technician</th>
                                                <th>Closed On</th>
                                                <th class="d-none">Closed By</th>
                                                <th>User Name</th>
                                                <th>Feedback On</th>
                                                <th>Feedback Point</th>
                                                <th>Feedback Remarks</th>
                                                <th class="d-none">Status</th>
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
<!-- Filter Tickets Modal -->
<div class="modal tickets-modal fade" id="filterTicketsModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">Filter</div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-6 mb-2-5">
                        <input type="text" class="form-control" id="filterTicketNo" name="" placeholder="Ticket No">
                    </div>
                    <div class="col-sm-6 mb-2-5">
                        <select id="userNameInput" name="userNameInput" placeholder="Requester Name"></select>
                    </div>
                    <div class="col-sm-6 mb-2-5">
                        <select class="department" id="departmentInput" name="departmentInput"
                            placeholder="Searching Dept"></select>
                    </div>
                    <div class="col-sm-6 mb-2-5">
                        <select name="technicianInput" id="technicianInput" class="technician"></select>
                    </div>
                    <div class="col-sm-6 mb-2-5">
                        <input type="text" class="form-control datepicker" id="requestedFromInput"
                            name="requestedFromInput" placeholder="From Date">
                    </div>
                    <div class="col-sm-6 mb-2-5">
                        <input type="text" class="form-control datepicker" id="requestedToInput" name="requestedToInput"
                            placeholder="To Date">
                    </div>
                    <div class="col-sm-6 mb-2-5">
                        <select name="teamfilter" id="teamfilter" multiple>
                            <option value="">Team</option>
                            @foreach ($teams as $item)
                            <option value="{{ $item['teamName'] }}">{{ $item['teamName'] }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>
            </div>
            <div class="modal-footer text-right">
                <button class="btn tickets-modal-submit-btn mr-2" id="applyFilterBtn">Apply</button>
                <button class="btn tickets-modal-submit-btn mr-2" id="resetFilterBtn">Reset</button>
            </div>
        </div>
    </div>
</div>


@section('js-content')
<!-- DataTables Buttons JS -->
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js"></script>
<script>
const ticketDetailsURL = '{{url("tickets/ticket")}}';
const taskDetailsURL = '{{url("tickets/task")}}';
</script>
<script src="{{asset('public/dist/js/tickets.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.13.3/js/standalone/selectize.min.js"></script>
<!-- Flatpickr JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
$(function() {

    $('[data-toggle="tooltip"]').tooltip();

    flatpickr(".datepicker", {
        dateFormat: "d-M-Y"
    });

    $('#teamfilter').selectize({
        plugins: ['remove_button'],
        delimiter: ',',
        persist: false,
        create: false,
        valueField: 'id',
        labelField: 'text',
        searchField: ['text'],
        placeholder: 'Choose Team',
    });

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



    var table = $('#ticketsAllTable').DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        ordering: true,
        responsive: true,
        buttons: [
            'copy', // Button to copy data to clipboard
            {
                extend: 'excel', // Export to Excel
                title: function() {
                    return 'Feedback Report';
                }
            },
            {
                extend: 'pdf', // Export to PDF
                title: function() {
                    return 'Feedback Report';
                }
            },
            {
                extend: 'csv', // Export to CSV
                title: function() {
                    return 'Feedback Report';
                }
            }
        ],
        dom: "<'row'<'col-sm-12'>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        ajax: {
            url: "{{ route('feedback.report') }}",
            data: function(d) {

                d.ticketNo = $('#filterTicketNo').val();
                d.userName = $('#userNameInput').val();
                d.department = $('#departmentInput').val();
                d.technician = $('#technicianInput').val();
                d.requestedFrom = $('#requestedFromInput').val();
                d.requestedTo = $('#requestedToInput').val();
                d.feedbackpoint = $('#feedbackPoint').val();
                d.teamId = $('#teamfilter').val();
            }
        },
        columns: [{
                data: 'ticketNumber',
                name: 'ticketNumber',
                className: 'text-font-0'
            },
            {
                data: 'TEAM_NAME',
                name: 'TEAM_NAME',
                className: 'text-font-0'
            },
            {
                data: 'CREATED_ON',
                name: 'CREATED_ON',
                className: 'text-font-0 d-none'
            },
            {
                data: 'ASSIGNED_ON',
                name: 'ASSIGNED_ON',
                className: 'text-font-0 d-none'
            },
            {
                data: 'SUBJECT',
                name: 'SUBJECT',
                className: 'text-font-0 d-none'
            },
            {
                data: 'CATEGORY',
                name: 'CATEGORY',
                className: 'text-font-0 d-none'
            },
            {
                data: 'SUB_CATEGORY',
                name: 'SUB_CATEGORY',
                className: 'text-font-0 d-none'
            },
            {
                data: 'EFFORT',
                name: 'EFFORT',
                className: 'text-font-0 d-none'
            },
            {
                data: 'TECHNICIAN_NAME',
                name: 'TECHNICIAN_NAME',
                className: 'text-font-0'
            },
            {
                data: 'CLOSED_ON',
                name: 'CLOSED_ON',
                className: 'text-font-0'
            },
            {
                data: 'CLOSED_BY',
                name: 'CLOSED_BY',
                className: 'text-font-0 d-none'
            },
            {
                data: 'USER_NAME',
                name: 'USER_NAME',
                className: 'text-font-0'
            },
            {
                data: 'FEEDBACK_ON',
                name: 'FEEDBACK_ON',
                className: 'text-font-0'
            },
            {
                data: 'FEEDBACK_POINT',
                name: 'FEEDBACK_POINT',
                className: 'text-font-0'
            },
            {
                data: 'FEEDBACK_REMARKS',
                name: 'FEEDBACK_REMARKS',
                className: 'text-font-0'
            },
            {
                data: 'STATUS',
                name: 'STATUS',
                className: 'text-font-0 d-none'
            }
        ],
    });

    table.on('draw', function() {
        var info = table.page.info();
        var totalTickets = info.recordsTotal;

        // Update the total tickets badge
        $('#totalTicketsBadge').text(totalTickets);
    });


    $("#applyFilterBtn").on('click', function() {

        table.page.len(-1).draw();

        $('#ticketsAllTable').DataTable().ajax.reload(null, false);

        $('#filterTicketsModal').modal('hide');


    });

    $("#resetFilterBtn").on('click', function() {

        $('#filterTicketNo').val('');
        $('#userNameInput')[0].selectize.clear();
        $('#departmentInput')[0].selectize.clear();
        $('#technicianInput')[0].selectize.clear();
        $('#requestedFromInput').val('');
        $('#requestedToInput').val('');
        $('#feedbackPoint').val('');
        $('#teamfilter')[0].selectize.clear();

        $('#ticketsAllTable').DataTable().ajax.reload(null, false);

        table.page.len(10).draw();

        $('#filterTicketsModal').modal('hide');


    });


    $("#exportToExcelBtn").on('click', function() {
        // Attach a handler to the draw event
        $('#ticketsAllTable').on('draw.dt', function() {
            // Check if the table is fully drawn
            if (!$(this).DataTable().page.info().incomplete_render) {
                // Trigger the DataTables Excel export.
                $(this).DataTable().button('.buttons-excel').trigger();
                // Remove the draw event handler to prevent it from being called multiple times
                $('#ticketsAllTable').off('draw.dt');
            }
        });

        // Set the page length of the table to display all records and redraw the table
        table.page.len(-1).draw();
    });



});
</script>
@endsection