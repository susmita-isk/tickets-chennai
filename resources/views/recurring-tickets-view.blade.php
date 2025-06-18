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
    overflow-x: none !important;
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
<li class="breadcrumb-item active">
    <a href="{{ route('assign.tickets') }}">Tickets</a>
</li>
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
       
    </div>
    <div id="ticketView" class="">
        <ul class="nav nav-tabs tickets-nav-tabs" id="ticketsTablesTabMenu" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link tickets-tab-link active" id="tickets-main-tab-link" type="button"
                    data-toggle="tab" data-target="#ticketsMainTabPanel" aria-controls="ticketsMainTabPanel"
                    aria-selected="true">
                    <i class="fas fa-bars"></i> Recurring Tickets
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
                                        id="recurringTicketsTable" style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>Recurring ID</th>
                                                <th>Requester Name</th>                                                
                                                <th>Subject</th>
                                                <th>Team Name</th>
                                                <th>Frequency</th>
                                                <th>Weekday</th>
                                                <th>Start Date</th>
                                                <th>Recurring Till</th>
                                                <th>Scheduled On</th>
                                                <th>Scheduled By</th>
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

<!-- Confirmation Modal for Removing Technician -->
<div class="modal tickets-modal fade" id="recurringStatusModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-sm">
            <div class="modal-header">Confirmation</div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col">
                        <input type="hidden" name="recurringId" id="recurringId">
                        <div class="confirmation-text" id="deactiveText">
                            <!-- Are you sure you want to Deactivate this technician? -->
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-7"></div>
                    <div class="col d-flex justify-content-between">
                        <button class="btn tickets-modal-submit-btn" id="modal-active-btn">Yes</button>
                        <button class="btn tickets-modal-submit-btn" id="modal-inactive-btn">No</button>
                    </div>
                </div>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.13.3/js/standalone/selectize.min.js"></script>
<!-- Flatpickr JavaScript -->
<script src="{{asset('public/dist/js/tickets.js')}}"></script>
<script>
$(function() {

    $('[data-toggle="tooltip"]').tooltip();

   

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


    var departmentName = '{{ $departmentName }}';

    var table = $('#recurringTicketsTable').DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        ordering: false,
        responsive: true,
        dom: "<'row'<'col-sm-12'>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        ajax: {
            url: "{{ route('recurring.tickets.view') }}",
            data: function(d) {

            }
        },

        columns: [{
                data: 'RECURRING_ID',
                name: 'RECURRING_ID',
                className: 'text-font-0'
            },
            {
                data: 'USER_NAME',
                name: 'USER_NAME',
                className: 'text-font-0'
            },
            {
                data: 'SUBJECT',
                name: 'SUBJECT',
                className: 'text-font-0',
            },
            {
                data: 'TEAM_NAME',
                name: 'TEAM_NAME',
                className: 'text-font-0'
            },
            {
                data: 'FREQUENCY',
                name: 'FREQUENCY',
                className: 'text-font-0'
            },
            {
                data: 'WEEKDAY',
                name: 'WEEKDAY',
                className: 'text-font-0'
            },
            {
                data: 'START_DATE',
                name: 'START_DATE',
                className: 'text-font-0'
            },
            {
                data: 'RECURRING_TILL',
                name: 'RECURRING_TILL',
                className: 'text-font-0'
            },
            {
                data: 'CREATED_ON',
                name: 'CREATED_ON',
                className: 'text-font-0'
            },
            {
                data: 'CREATED_BY',
                name: 'CREATED_BY',
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
       
    });

    table.on('draw', function() {
        var info = table.page.info();
        var totalTickets = info.recordsTotal;

        // Update the total tickets badge
        $('#totalTicketsBadge').text(totalTickets);
    });


});



$("#assignTicketForm").submit(function(e) {

    //prevent Default functionality
    e.preventDefault();
    $('#assignBtn').prop('disabled', true);

    var formData = new FormData(this);
    formData.append("teamName", $('#teamName option:selected').text());

    $.ajax({
        method: "post",
        url: '{{route("assign.self.tickets")}}',
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

                $('#recurringTicketsTable').DataTable().ajax.reload(null, false);
            } else if (data['successCode'] == 0) {
                iziToast.show({

                    title: 'Error',
                    position: 'topRight',
                    color: 'red',
                    message: 'Ticket Already Assigned'
                });
                $('#assignBtn').prop('disabled', false);
            }

            $('.technician').val(null).trigger('change');
        }
        // error: function() {
        //     $('#assignBtn').prop('disabled', false);
        // }
    });

});

$('#modal-active-btn').on('click', function() {
    var recurringId = $('#recurringId').val();

    $.ajax({
        url: '{{route("recurring.status")}}',
        method: 'get',
        dataType: 'json',
        data: {
            recurringId: recurringId,
        },
        success: function(data) {
            if (data.error == false) {
                iziToast.show({
                    title: 'Success',
                    position: 'topRight',
                    color: 'green',
                    message: data.msg
                });
                $('#recurringStatusModal').modal('hide');
                $('#recurringTicketsTable').DataTable().ajax.reload(null, false);
            } else {
                toastr.error(data.msg);
            }
        },
        error: function(jqXHR, textStatus, err) {
            toastr.error("Error !! Please try again", '', {
                closeButton: true
            });
        }
    });

});

$('#modal-inactive-btn').on('click', function() {
    $('#recurringTicketsTable').DataTable().ajax.reload(null, false);
    $('#recurringStatusModal').modal('hide');
});


function assignSelfTicket(id, ticketNumber) {

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

        },
        error: function(xhr, status, error) {
            // Handle errors here
            console.error(xhr.responseText);
        }
    });


    $('#assignTicketForm').find('input').val('');
    $('#assignTicketForm').find('select').val('');

    $('#ticketIdForAssignment').val(id);

    $('#ticketIdToAssign').html(ticketNumber)


    $('#assignTicketModal').modal('show');


}

function recurringStatus(id, status) {

    var isChecked = status === 'Y'; // Check its state

    var Msgtext = isChecked ? 'Inactive' : 'Active';
    var txt = 'You want to ' + Msgtext + ' this schedule';
    $('#deactiveText').text(txt);

    $('#recurringId').val(id);

    $('#recurringStatusModal').modal({
        backdrop: 'static',
        show: true
    });
}

</script>
@endsection