@extends('layouts.main.app')

@section('page-title', 'Tickets')

@section('css-content')
<!-- DataTables Buttons CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.7.1/css/buttons.dataTables.min.css">

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<!--Filepond -->
<link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
<link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet" />
<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.13.3/css/selectize.default.min.css">
<link rel="stylesheet" href="{{asset('public/dist/css/tickets.css')}}">

<style>
/* Custom table styling */
.table-container {
    margin-top: 20px;
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
    padding: 12px 10px;
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
</style>

@endsection

@section('breadcrumb-menu')
<li class="breadcrumb-item active">Tickets</li>
<li class="breadcrumb-item" id="departmentName">{{ $departmentName }}</li>
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
        <button class="btn tickets-action-btn-transparent" id="exportToExcelBtn" title="Export to Excel">
            <img src="{{asset('public/img/icons/excel.png')}}" alt="Export to Excel" height="24">
        </button>
    </div>
    <div id="ticketView" class="">
        <ul class="nav nav-tabs tickets-nav-tabs" id="ticketsTablesTabMenu" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link tickets-tab-link active" id="tickets-main-tab-link" type="button"
                    data-toggle="tab" data-target="#ticketsMainTabPanel" aria-controls="ticketsMainTabPanel"
                    aria-selected="true">
                    <i class="fas fa-bars"></i> Pending Tickets
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
                                    <table class="table table-hover table-striped tickets-main-table data-table"
                                        id="ticketsAllTable" style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>Sl No</th>
                                                <th>Ticket No</th>
                                                <th>Requester Name</th>
                                                <th>Requester Dept</th>
                                                <th>Subject</th>
                                                <th>Created Date</th>
                                                <th>Assigned On</th>
                                                <th>Age</th>
                                                <th>Team Name</th>
                                                <th>Assigned To</th>
                                                <th>Task Level Status</th>
                                                <th>Progress</th>
                                                <th width="150px;">Last Work Update</th>
                                                <th>Created By</th>
                                                <th style="display: none">Category</th>
                                                <th style="display: none">Subcategory</th>
                                                <th style="display: none">Item Type</th>
                                                <th style="display: none">Item</th>

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
    </div>
    {{-- End Tab Content for Tickets Listing Table --}}
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
            <div class="modal-header">Filter
                <button class="close" data-dismiss="modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-6 mb-2-5">
                        <input type="text" class="form-control" id="filterTicketNo" name="" placeholder="Ticket No">
                    </div>
                    <div class="col-sm-6 mb-2-5">
                        <input type="text" class="form-control" id="filterTicketSubject" name="" placeholder="Subject">
                    </div>
                    <div class="col-sm-6 mb-2-5">
                        <input type="text" class="form-control" id="filterTicketDescripton" name=""
                            placeholder="Description">
                    </div>
                    <div class="col-sm-6 mb-2-5">
                        <select id="userNameInput" name="userNameInput" placeholder="Requester Name">
                        </select>
                    </div>
                    <div class="col-sm-6 mb-2-5">
                        <select class="department" id="departmentInput" name="departmentInput"
                            placeholder="Searching Dept">
                        </select>
                    </div>
                    <div class="col-sm-6 mb-2-5">
                        <select name="teamfilterTasks" id="teamfilterTasks" class="form-control">
                            <option value="">Team</option>
                            @foreach ($teams as $item)
                            <option value="{{ $item['teamName'] }}">{{ $item['teamName'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 mb-2-5">
                        <select name="technicianInput" id="technicianInput" class="technician">
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
                        <select id="statusSelect" name="status" multiple>
                            <option value="New">New</option>
                            <option value="Open">Open</option>
                            <!-- <option value="Closed">Closed</option> -->
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


<!-- END Modal dialogs with forms -->


@section('js-content')
<!-- DataTables Buttons JS -->
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js"></script>
<script>
const ticketDetailsURL = '{{url("tickets/ticket")}}';
const taskDetailsURL = '{{url("tickets/task")}}';
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.13.3/js/standalone/selectize.min.js"></script>
<!-- Flatpickr JavaScript -->
<script src="{{asset('public/dist/js/tickets.js')}}"></script>
<script>
$(function() {

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

    var departmentName = '{{ $departmentName }}';

    var table = $('#ticketsAllTable').DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        ordering: false,
        responsive: true,
        dom: "<'row'<'col-sm-12'>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        ajax: {
            url: "{{ route('pending.tickets') }}",
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
                d.description = $('#filterTicketDescripton').val();
                d.teamId = $('#teamfilterTasks').val();
                d.createdBy = $('#createdBy').val();
            }
        },
        buttons: [{
            extend: 'excel',
            title: function() {
                return 'Pending Tickets - ' + departmentName;
            },
            exportOptions: {
                columns: function(idx, data, node) {
                    // Export only visible columns and the last four hidden columns
                    var visibleColumnIndexes = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11,
                        12, 13
                    ];
                    var hiddenColumnIndexes = [14, 15, 16, 17];
                    if (visibleColumnIndexes.includes(idx) || hiddenColumnIndexes
                        .includes(idx)) {
                        return true;
                    }
                    return false;
                },
                format: {
                    body: function(data, row, column, node) {

                        if (column === 1) {
                            if (typeof data === 'string' && data.includes('<a ')) {
                                // Extract ticket number from anchor tag
                                var ticketNumberMatch = data.match(
                                    /<a [^>]*>([^<]+)<\/a>/);
                                if (ticketNumberMatch && ticketNumberMatch[1]) {
                                    return ticketNumberMatch[1];
                                } else {
                                    // If no match is found, return the original data
                                    return data;
                                }
                            } else {
                                // If it's not HTML content or doesn't contain anchor tag
                                return data;
                            }
                        } else if (column === 2) {
                            if (typeof data === 'string' && data.includes('title=')) {
                                // Extract title attribute from HTML content
                                var titleStart = data.indexOf('title="') +
                                    7; // Adding 7 to skip 'title="'
                                var titleEnd = data.indexOf('"', titleStart);
                                var extractedTitle = data.substring(titleStart,
                                    titleEnd);
                                return extractedTitle;
                            } else {
                                // If it's not HTML content or doesn't contain title attribute
                                return data;
                            }

                        } else if (column ===
                            0) { // Column 2 in a zero-based index system
                            var tempElement = document.createElement('div');
                            tempElement.innerHTML = data;
                            // Extract text content
                            return tempElement.textContent.trim();

                        } else {
                            return data;
                        }
                    }
                }
            }
        }, 'copy', 'pdf', 'csv', ],
        columns: [{
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                className: 'text-font-0'
            },
            {
                data: 'ticketNumber',
                name: 'id',
                className: 'text-font-0'
            },
            {
                data: 'USER_NAME',
                name: 'requester',
                className: 'text-font-0'
            },
            {
                data: 'DEPARTMENT_CODE',
                name: 'department',
                className: 'text-font-0',

            },
            {
                data: 'SUBJECT',
                name: 'subject',
                className: 'text-font-0',

            },
            {
                data: 'CREATED_ON',
                name: 'requestedOn',
                className: 'text-font-0'
            },
            {
                data: 'ASSIGNED_ON',
                name: 'ASSGINED_ON',
                className: 'text-font-0'
            },
            {
                data: 'AGE',
                name: 'AGE',
                className: 'text-font-0'
            },
            {
                data: 'TEAM_NAME',
                name: 'TEAM_NAME',
                className: 'text-font-0'
            },
            {
                data: 'TECHNICIAN_NAME',
                name: 'TECHNICIAN_NAME',
                className: 'text-font-0'
            },
            {
                data: 'STATUS',
                name: 'STATUS',
                className: 'text-font-0'
            },
            {
                data: 'PROGRESS',
                name: 'PROGRESS',
                className: 'text-font-0'
            },
            {
                data: 'LAST_UPDATE',
                name: 'LAST_UPDATE',
                className: 'text-font-0'
            },
            {
                data: 'CREATED_BY',
                name: 'CREATED_BY',
                className: 'text-font-0'
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
                data: 'ITEM_TYPE',
                name: 'ITEM_TYPE',
                className: 'text-font-0 d-none'
            },
            {
                data: 'ITEM',
                name: 'ITEM',
                className: 'text-font-0 d-none'
            },
        ],
    });

    table.on('draw', function() {
        var info = table.page.info();
        var totalTickets = info.recordsTotal;

        // Update the total tickets badge
        $('#totalTicketsBadge').text(totalTickets);
    });


    $("#filterBtnTasks").on('click', function() {

        table.page.len(-1).draw();

        $('#ticketsAllTable').DataTable().ajax.reload(null, false);

        $('#filterTicketsModal').modal('hide');
    });

    $("#clearBtnTasks").on('click', function() {

        $('#filterTicketNo').val('');
        $('#userNameInput')[0].selectize.clear();
        $('#oldTicketInput').val('');
        $('#departmentInput')[0].selectize.clear();
        $('#technicianInput')[0].selectize.clear();
        $('#modeInput').val('');
        $('#requestedFromInput').val('');
        $('#requestedToInput').val('');
        $('#statusSelect')[0].selectize.clear();
        $('#progressSelect')[0].selectize.clear();
        $('#filterTicketSubject').val('');
        $('#filterTicketDescripton').val('');
        $('#teamfilterTasks').val('');

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
</script>
@endsection