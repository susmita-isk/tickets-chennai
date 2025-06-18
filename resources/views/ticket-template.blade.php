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
        <button class="btn tickets-action-btn" id="addTemplateBtn" data-target="#addsubcatTaskModal" data-toggle="modal"
            title="Add Ticket Template">
            <i class="fas fa-plus"></i> Add
        </button>
        <!-- <button class="btn tickets-action-btn" id="ticketsTableFilterBtn" data-target="#filterTicketsModal"
            data-toggle="modal" title="Filter Tickets">
            <img src="{{asset('public/img/icons/filter.png')}}" alt="">
        </button> -->
    </div>
    <div id="ticketView" class="">
        <ul class="nav nav-tabs tickets-nav-tabs" id="ticketsTablesTabMenu" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link tickets-tab-link active" id="tickets-main-tab-link" type="button"
                    data-toggle="tab" data-target="#ticketsMainTabPanel" aria-controls="ticketsMainTabPanel"
                    aria-selected="true">
                    <i class="fas fa-bars"></i> Ticket Template
                </button>
            </li>
        </ul>
        {{-- Begin Table Content for Ticket Template --}}
        <div class="tab-content">
            <div class="tab-pane tickets-tab-pane fade show active" id="ticketsMainTabPanel" role="tabpanel"
                aria-labelledby="tickets-main-tab-link">
                <div class="tickets-tab-pane-content">
                    <div class="row">
                        <div class="col">
                            <div class="table-container">
                                <div class="table-responsive" style="overflow-x: hidden;">
                                    <table class="table table-hover tickets-main-table" id="subTaskTable"
                                        style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>Sl No. </th>
                                                <th>Template Name </th>
                                                <th>Status</th>
                                                <th>Action</th>
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
    </div>
    {{-- End Tab Content for Ticket Template --}}
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

{{-- BEGIN Subcategory Task Modal --}}
<div class="modal tickets-modal fade" id="addsubcatTaskModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Ticket Template</h6>
                <button class="close" data-dismiss="modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="addsubcatTask">
                    @csrf
                    <input type="hidden" name="templateId" id="templateId">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="templateName">Template Name</label>
                                <input type="text" name="templateName" class="form-control" id="templateName"
                                    placeholder="Enter Template Name" required>
                            </div>
                        </div>
                        <div class="col-10">
                            <div class="form-group">
                                <label for="taskName">Task</label>
                                <input type="text" name="taskName" class="form-control" id="taskName"
                                    placeholder="Enter Task">
                            </div>
                        </div>
                        <div class="col-2 pl-0">
                            <button type="button" class="btn" id="addTaskButton" style="background: #51913b;border-radius: 20px;
                                margin-top: 31px; color: #fff;"></button>
                        </div>
                        <div class="col-12">
                            <!-- Task Table Section -->
                            <div class="table-container px-0">
                                <div class="card table-responsive" id="taskArray" style="display:none;">
                                    <table class="table tickets-main-table mb-0" style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th class="task-header">SL No</th>
                                                <th class="task-header" style="width: 73%;">Tasks</th>
                                            </tr>
                                        </thead>
                                        <tbody id="taskTableBody">
                                            <!-- Rows will be added dynamically here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 text-center">
                            <button type="submit" class="btn tickets-modal-submit-btn" id="add-template-btn"></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Template Status Change Modal -->
<div class="modal fade" id="activeModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title card-modal-head ml-auto" id="exampleModalLongTitle">Are You Sure?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <!-- <span aria-hidden="true">&times;</span> -->
                </button>
            </div>
            <div class="modal-body card-modal-body">

            </div>
            <div class="modal-footer card-modal-footer">
                <input type="hidden" id="template_status_id" value="">
                <input type="hidden" id="template_status" value="">
                <button type="button" class="btn btn-small modal-active-btn" id="modal-active-btn">Yes</button>
                <button type="button" class="btn btn-small modal-inactive-btn" id="modal-inactive-btn">No</button>
            </div>
        </div>
    </div>
</div>
<!-- //Template Status Change Modal -->

<!-- Template Task Status Change Modal -->
<div class="modal fade" id="taskStatusModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title card-modal-head ml-auto" id="exampleModalLongTitle">Are You Sure?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <!-- <span aria-hidden="true">&times;</span> -->
                </button>
            </div>
            <div class="modal-body card-modal-body">

            </div>
            <div class="modal-footer card-modal-footer">
                <input type="hidden" id="task_status_id" value="">
                <input type="hidden" id="task_status" value="">
                <button type="button" class="btn btn-small modal-active-btn" id="task-active-btn">Yes</button>
                <button type="button" class="btn btn-small modal-inactive-btn" id="task-inactive-btn">No</button>
            </div>
        </div>
    </div>
</div>
<!-- //Template Task Status Change Modal -->

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

    var subTaskTable = $('#subTaskTable').DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        dom: "<'row'<'col-sm-12'>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        ajax: {
            url: "{{ route('templates.data') }}",
            data: function(d) {
                // d.categoryId = $('#categoryFilterItem').val();
            }
        },
        columns: [{
                // data: 'templateName',
                // name: 'templateName',
                data: null,
                render: function(data, type, row, meta) {
                    // Render serial number
                    return meta.row + 1;
                },
                className: 'text-font-0'
            },
            {
                data: 'templateName',
                name: 'templateName',
                className: 'text-font-0'
            },
            {
                data: 'status',
                name: 'status',
                className: 'text-font-0'
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                className: 'text-center'
            },
        ]
    }).on('error.dt', function(e, settings, techNote, message) {
        console.log('DataTables error: ', message);
        // Prevent default alert behavior
        e.preventDefault();
    });

    subTaskTable.on('draw', function() {
        var info = subTaskTable.page.info();
        var totalTickets = info.recordsTotal;

        // Update the total tickets badge
        $('#totalTicketsBadge').text(totalTickets);
    });


});
</script>
<script>
// Assuming templateId is already available
const templateId = $('#templateId').val(); // Get the value of the templateId input field

let tasks = []; // Array to store tasks
let editingTaskId = null; // Track the task being edited

const taskNameInput = document.getElementById('taskName');
const taskTableBody = document.getElementById('taskTableBody');
const taskCard = document.getElementById('taskArray');

// Add Task Button Click Event
$('#addTaskButton').on('click', function(e, value) {

    const taskName = taskNameInput.value.trim();

    const templateId = $('#templateId').val();

    if (!taskName) {
        alert('Please enter a task!');
        return;
    }

    if (editingTaskId !== null) {
        // $('#addTaskButton').text('Update');
        // Update the existing task
        const taskIndex = tasks.findIndex(task => task.TASK_ID === editingTaskId);
        if (taskIndex !== -1) {
            tasks[taskIndex].TASK_NAME = taskName;
            $('#addTaskButton').text('Add');
        }
        editingTaskId = null; // Reset editing ID
    } else {
        // Add a new tas
        $('#addTaskButton').text('Add');

        const newTaskId = tasks.length + 1; // Generate a new ID for simplicity
        tasks.push({
            TASK_ID: newTaskId,
            TASK_NAME: taskName,
            IS_ACTIVE: 'Y', // Default to inactive
        });
    }

    // Clear the input field
    taskNameInput.value = '';

    // Make the table card visible
    if (tasks.length > 0) {
        taskCard.style.display = 'block';
    }

    // Render the task table
    renderTaskTable();

});

// Function to render the task table
function renderTaskTable() {
    // Clear existing table rows
    taskTableBody.innerHTML = '';

    const templateId = $('#templateId').val();

    // Populate rows dynamically
    tasks.forEach((task, index) => {

        const row = document.createElement('tr');

        row.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${task.TASK_NAME}</td>
                `;

        row.innerHTML += `
            <td>
                ${templateId ? `
                    <label class="ml-2 switch">
                        <input type="checkbox" 
                            name="task-status" 
                            class="task-status" 
                            onclick="getTaskStatus('${task.IS_ACTIVE}', ${task.TASK_ID})" 
                            data-id="${task.TASK_ID}"  
                            ${task.IS_ACTIVE === 'Y' ? 'checked' : ''}>
                        <span class="slider round"></span>
                    </label>` : 
                    `<button type="button" class="btn tickets-action-btn-transparent float-right ml-2" 
                        onclick="removeTask(${task.TASK_ID})" 
                        title="Remove">
                        <img src="${'{{ asset('public/img/icons/deactivate.png') }}'}" 
                            alt="Edit" 
                            height="20">
                    </button>`
                }
                <button type="button" class="btn tickets-action-btn-transparent float-right" 
                        onclick="editTask(${task.TASK_ID})" 
                        title="Edit">
                    <img src="${'{{ asset('public/img/icons/edit-btn.png') }}'}" 
                        alt="Edit" 
                        height="20">
                </button>
            </td>`;

        taskTableBody.appendChild(row);
    });
}

function getTemplates(id) {

    $('#addsubcatTask').find('input, select').val('');
    $('#taskTableBody').empty();
    $('#taskArray').hide();
    tasks = []; // Reset the task array

    $('#add-template-btn').text('Update');

    $('#addTaskButton').text('Add');

    if (id) {
        // Make an AJAX request to fetch already assigned details
        $.ajax({
            type: "POST",
            url: '{{ route("templates.get") }}',
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: {
                templateId: id
            },
            success: function(response) {
                if (response && response.length > 0) {
                    const template = response[0];

                    $('#templateId').val(template.templateId);
                    $('#templateName').val(template.templateName);
                    $('#status_subtask_add').val(template.isActive ? 'Y' : 'N');

                    // Populate task table if tasks exist
                    if (template.tasks && template.tasks.length > 0) {

                        tasks = template.tasks; // Store fetched tasks in the global array

                        renderTaskTable();
                        $('#taskArray').show();
                    }
                }
            },
            error: function(xhr, status, error) {
                // Handle errors here
                console.error(xhr.responseText);
            }
        });
    }

    $('#addsubcatTaskModal').modal('show');
}

function editTask(taskId) {

    $('#addTaskButton').text('Update');

    // Find the task by ID in the tasksArray
    const taskToEdit = tasks.find(task => task.TASK_ID === taskId);

    if (taskToEdit) {
        // Set the task name in the input field
        $('#taskName').val(taskToEdit.TASK_NAME);
        editingTaskId = taskId; // Track the current task ID being edited
    }
}

function removeTask(taskId) {
    const button = document.querySelector(`button[onclick="removeTask(${taskId})"]`);

    if (button) {
        // Get the parent row (tr) of the button
        const row = button.closest('tr');

        // Remove the row from the DOM
        if (row) {
            row.remove();
        }
    }

    // Remove the task from the array
    tasks = tasks.filter(task => task.TASK_ID !== taskId);

    // Reassign TASK_ID values
    tasks = tasks.map((task, index) => {
        task.TASK_ID = index + 1; // Reassign IDs starting from 1
        return task;
    });

    // Re-render the table with updated IDs
    renderTaskTable();
}

$('#addTemplateBtn').on('click', function() {
    // Reset the form fields
    $('#status_subtask_add').val('Y');
    $('#templateName').val('');
    $('#taskName').val('');
    $('#templateId').val('');

    $('#addsubcatTask').find('input, select').val('');
    $('#taskTableBody').empty();
    $('#taskArray').hide();
    tasks = []; // Reset the task array

    $('#add-template-btn').text('Submit');
    $('#addTaskButton').text('Add');


});

$("#addsubcatTask").submit(function(e) {
    e.preventDefault();
    $('#add-template-btn').prop('disabled', true);

    const templateId = $('#templateId').val();

    let formData = new FormData(this);

    formData.append('tasks', JSON.stringify(tasks)); // Add the tasks array to FormData

    $.ajax({
        type: "POST",
        url: templateId ? '{{ route("update.template") }}' : '{{ route("subcat.task.store") }}',
        dataType: 'json',
        contentType: false,
        processData: false,
        cache: false,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        data: formData,
        success: function(data) {
            // if (data['successCode'] == 1) {
            iziToast.show({
                title: 'Success',
                position: 'topRight',
                color: 'green',
                message: templateId ? 'Template Updated' : 'Template Created'
            });

            $('#add-template-btn').prop('disabled', false);

            $('#addsubcatTask').find('input, select').val('');
            tasks = [];
            $('#taskTableBody').empty();
            $('#taskArray').hide();
            $('#addsubcatTaskModal').modal('hide');
            $('#subTaskTable').DataTable().ajax.reload(null, false);
            // }

            $('#status_subtask_add').val('Y');
            $('#templateName').val('');
            $('#taskName').val('');
            $('#templateId').val('');
        },
        error: function(xhr, status, error) {
            console.error(xhr.responseText);
        }
    });
});

function getTemplateStatus(templateStatus) {

    var isChecked = templateStatus === 'Y'; // Check its state

    var Msgtext = isChecked ? 'Inactive' : 'Active';
    var txt = 'You want to ' + Msgtext + ' this status';
    $('.card-modal-body').text(txt);

    var rowId = $(event.target).data('id');
    $('#template_status_id').val(rowId);
    $('#template_status').val(templateStatus);

    $('#activeModalCenter').modal({
        backdrop: 'static',
        show: true
    });
}

function getTaskStatus(isActive, taskId) {
    var isChecked = isActive === 'Y'; // Check its state

    var Msgtext = isChecked ? 'Inactive' : 'Active';
    var txt = 'You want to ' + Msgtext + ' this status';
    $('.card-modal-body').text(txt);

    // var rowId = $(event.target).data('id');
    $('#task_status_id').val(taskId);
    $('#task_status').val(isActive);

    $('#taskStatusModal').modal({
        backdrop: 'static',
        show: true
    });
}

$('#modal-active-btn').on('click', function() {
    var templateId = $('#template_status_id').val();

    var templateStatus = $('#template_status').val();

    $.ajax({
        url: '{{route("template.status")}}',
        method: 'get',
        dataType: 'json',
        data: {
            templateId: templateId,
            templateStatus: templateStatus
        },
        success: function(data) {
            if (data.error == false) {
                iziToast.show({
                    title: 'Success',
                    position: 'topRight',
                    color: 'green',
                    message: data.msg
                });
                $('#activeModalCenter').modal('hide');
                $('#subTaskTable').DataTable().ajax.reload(null, false);
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
    $('#subTaskTable').DataTable().ajax.reload(null, false);
    $('#activeModalCenter').modal('hide');
});

$('#task-active-btn').on('click', function() {
    var taskId = $('#task_status_id').val();

    var taskStatus = $('#task_status').val();

    $.ajax({
        url: '{{route("task.status")}}',
        method: 'get',
        dataType: 'json',
        data: {
            taskId: taskId,
            taskStatus: taskStatus
        },
        success: function(data) {
            if (data.error == false) {
                iziToast.show({
                    title: 'Success',
                    position: 'topRight',
                    color: 'green',
                    message: data.msg
                });

                // Update the specific task in the `tasks` array
                const taskIndex = tasks.findIndex(task => task.TASK_ID == taskId);
                if (taskIndex !== -1) {
                    tasks[taskIndex].IS_ACTIVE = data.status;
                }

                // Re-render the task table with the updated array
                renderTaskTable();

                $('#taskStatusModal').modal('hide');


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

$('#task-inactive-btn').on('click', function() {

    const taskId = $('#task_status_id').val();
    const taskStatus = $('#task_status').val();
    // Update the specific task in the `tasks` array
    const taskIndex = tasks.findIndex(task => task.TASK_ID == taskId);
    if (taskIndex !== -1) {
        tasks[taskIndex].IS_ACTIVE = taskStatus;
    }

    // Re-render the task table with the updated array
    renderTaskTable();

    $('#taskStatusModal').modal('hide');
});
</script>

@endsection