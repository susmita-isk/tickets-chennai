@extends('layouts.main.app')

@section('page-title', 'Technicians')


@section('css-content')
<link rel="stylesheet" href="{{asset('public/dist/css/category.css')}}">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.13.3/css/selectize.default.min.css">
<style>
.sidebar-mini.sidebar-collapse .main-sidebar,
.sidebar-mini.sidebar-collapse .main-sidebar::before {
    margin-left: 0;
    width: 2.2rem;
}

input[type=number]::-webkit-outer-spin-button,
input[type=number]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type=number] {
    -moz-appearance: textfield;
    /* Firefox */
    appearance: textfield;
    /* Standard syntax */
}

.green-checkbox input[type="checkbox"] {
    appearance: none;
    -webkit-appearance: none;
    width: 15px;
    height: 14px;
    border: 2px solid green;
    border-radius: 3px;
    outline: none;
    cursor: pointer;
    position: relative;
}

/* Checked style */
.green-checkbox input[type="checkbox"]:checked {
  background-color: green;
  border-color: green;
}
.green-checkbox input[type="checkbox"]:checked::after {
    content: '✓';
    color: white;
    font-size: 11px;
    font-weight: 900;
    position: absolute;
    top: -3px;
    left: 1px;
}
</style>
@endsection



@section('breadcrumb-menu')
<li class="breadcrumb-item active">Tickets</li>
@endsection



@section('page-content')

@php
$permission = permission();
@endphp
@if(in_array(Route::currentRouteName(),$permission))

<div class="container-fluid">
    {{-- For Action Buttons on the right side --}}
    <div class="mt-1" id="actionBtnsContainer">
        <button class="btn tickets-action-btn" id="filterTechnicianBtn" data-toggle="modal"
            data-target="#filterTechnicianModal" title="Fiter Techncians" style="display: none;">
            <img src="{{asset('public/img/icons/filter.png')}}" alt="Filter Techncians">
        </button>
        <button class="btn tickets-action-btn" id="addTechnicianBtn" data-toggle="modal"
            data-target="#addNewTechnicianModal" title="Add Techncians" style="display: none;">
            <i class="fas fa-plus"></i>Add
        </button>

        <button class="btn tickets-action-btn" id="teamAddBtn" data-target="#addTeamModal" data-toggle="modal"
            title="Add Team" style="display: none;">
            <i class="fas fa-plus"></i> Add
        </button>
    </div>
    <div id="tabsContainer" class="mt-3 pb-3">
        <ul class="nav nav-tabs tickets-nav-tabs" id="categTablesTabsMenu" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link tickets-tab-link active" id="categoriesTabLink" type="button" data-toggle="tab"
                    data-target="#categoriesTabPanel" aria-controls="categoriesTabPanel" aria-selected="true">
                    <i class="fas fa-list"></i>Technicians
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link tickets-tab-link" id="subCategoriesTabLink" type="button" data-toggle="tab"
                    data-target="#subCategoriesTabPanel" aria-controls="subCategoriesTabPanel" aria-selected="">
                    <i class="fas fa-list"></i> Teams
                </button>
            </li>
        </ul>
        {{-- Tab content for Master tables --}}
        <div class="tab-content" id="categoryTablesContainer">
            <div class="tab-pane tickets-tab-pane fade show active" id="categoriesTabPanel" role="tabpanel"
                aria-labelledby="categoriesTabLink">
                <div class="tickets-tab-pane-content">

                    <div class="row">
                        <div class="col">
                            <div class="table-container">
                                <table class="table table-hover" id="techniciansTable">
                                    <thead>
                                        <td>Employee ID </td>
                                        <td>Login</td>
                                        <td>Role</td>
                                        <td>Name</td>
                                        <td>Mobile No</td>
                                        <td>Email</td>
                                        <td>Action(s)</td>
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
            <div class="tab-pane tickets-tab-pane fade" id="subCategoriesTabPanel" role="tabpanel"
                aria-labelledby="subCategoriesTabLink">
                <div class="tickets-tab-pane-content">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header" style="background-color: darkgreen; color: white;">
                                    Team
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover tickets-main-table" id="teamTable"
                                            style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th>Sl No.</th>
                                                    <th>Name</th>
                                                    <th class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Data will be populated by DataTable -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header" style="background-color: darkgreen; color: white;">
                                    Technicians
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover tickets-main-table" id="teamTechnicianTable"
                                            style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th>Sl No.</th>
                                                    <th>Name</th>
                                                    <th>Allocated On</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Data will be populated by DataTable -->
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


<!-- Modals -->
<!-- Modal for Collecting New User / Technician Details -->
<div class="modal tickets-modal fade" id="addNewTechnicianModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                Create Technician
                <button class="close" data-dismiss="modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="addNewTechnicianForm">
                    <div class="row">
                        <div class="col form-group">
                            <label for="technicianName" class="col-form-label">Technician Name</label>
                            <!-- <input type="text" class="form-control" placeholder="Enter Name"> -->
                            {{-- Searchable select box w/ autocomplete --}}
                            <select name="employee_id" id="technicianName">
                                <option value="">Please Select</option>
                            </select>
                        </div>
                    </div>
                    <div class="row" id="addSelectedUserDetails" style="display: none;">
                        <div class="col-12 mb-2">
                            <strong class="info-label fweight500">Employee ID:</strong>
                            <span id="selectedUserEmpId"></span>
                            <input type="hidden" name="employee_id" id="userEmpId">
                        </div>
                        <div class="col-12 mb-2">
                            <strong class="info-label fweight500">Department:</strong>
                            <span id="selectedUserDepartment"></span>
                        </div>
                        <input type="hidden" name="user_email" id="newUserEmail" value="">
                        <input type="hidden" name="user_mobile" id="newUserMobile" value="">
                        <input type="hidden" name="emp_name" id="newUserName" value="">
                    </div>
                    <div class="row">
                        <div class="col form-group">
                            <label for="userRole" class="col-form-label">Role</label>
                            <select name="user_role" id="userRole" class="form-control">
                                <option value="">Please Select</option>
                                <!-- <option value="Technician">Technician</option>
                                <option value="Admin">Admin</option>
                                <option value="Support Desk">Support Desk</option> -->
                                @foreach ($roles as $val)
                                <option value={{$val->ROLE_NAME }}>{{ $val->ROLE_NAME }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    {{-- <div class="row d-none" id="teamNameRow">
                        <div class="col form-group">
                            <label for="teamName" class="col-form-label">Team</label>
                            <select name="teamName" id="teamName" class="form-control">
                                <option value="">Please Select</option>
                                 @foreach ($teams as $item)
                                 <option value={{ $item['teamId'] }}>{{ $item['teamName'] }}</option>
                    @endforeach
                    </select>
            </div>
        </div> --}}
        <div class="row d-none" id="newUserCredentialsRow">
            <div class="col form-group">
                <label for="addTechnicianUserID" class="col-form-label">User ID</label>
                <input type="text" class="form-control" name="login_id" id="addTechnicianUserID"
                    placeholder="Login ID">
            </div>
            <div class="col form-group">
                <label for="addTechnicianPassword" class="col-form-label">Password</label>
                <input type="password" class="form-control" name="password" id="addTechnicianPassword"
                    placeholder="Password">
            </div>
        </div>
        <div class="row">
            <div class="col text-center">
                <button class="btn btn-success tickets-modal-submit-btn">Save</button>
            </div>
        </div>
        </form>
    </div>
</div>
</div>
</div>

<!-- Modal for Editing Technician Details -->
<div class="modal tickets-modal fade" id="editTechnicianDetailsModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                Edit Technician
                <button class="close" data-dismiss="modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="editTechnician">
                    @csrf
                    <input type="hidden" name="technicianId" id="technicianIdEdit">
                    <div class="row">
                        <div class="col form-group">
                            <label for="" class="col-form-label">Technician Name</label>
                            <input type="text" class="form-control" placeholder="Enter Name" id="technicianNameEdit"
                                required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col form-group">
                            <label for="" class="col-form-label">Mobile Number</label>
                            <input type="text" class="form-control" placeholder="Mobile" id="technicianMobileEdit"
                                maxlength="10" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col form-group">
                            <label for="" class="col-form-label">Email</label>
                            <input type="text" class="form-control" placeholder="Email" id="technicianEmailEdit"
                                required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col form-group">
                            <label for="" class="col-form-label">Login</label>
                            <input type="text" class="form-control" placeholder="Login" id="technicianLoginEdit"
                                required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col form-group">
                        <label for="" class="col-form-label">Password</label>
                            <input type="text" class="form-control" placeholder="password" id="technicianPasswordEdit"
                                required>

                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 text-center">
                            <button class="btn btn-success tickets-modal-submit-btn" id="editTechnician">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal for getting relieving date for technician -->
<div class="modal tickets-modal fade" id="relieveTechnicianModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                Relieve Technician
                <button class="close" data-dismiss="modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form action="" method="post">
                    <div class="row">
                        <div class="col form-group">
                            <label for="relievingDate" class="col-form-label">Relieving Date</label>
                            <input type="hidden" name="userId" id="deactiveUserId">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 text-center">
                            <button class="btn btn-success tickets-modal-submit-btn">Continue</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal for Removing Technician -->
<div class="modal tickets-modal fade" id="confirmDeactivateTechnicianModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">Confirmation</div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col">
                        <input type="hidden" name="userId" id="deactiveUserId">
                        <div class="confirmation-text">
                            Are you sure you want to Deactivate this technician?
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-7"></div>
                    <div class="col d-flex justify-content-between">
                        <button class="btn tickets-modal-submit-btn" value="yes" id="deactiveBtn">Yes</button>
                        <button class="btn tickets-modal-submit-btn" value="no" data-dismiss="modal">No</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal for Removing Technician -->
<div class="modal tickets-modal fade" id="confirmActivateTechnicianModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">Confirmation</div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col">
                        <input type="hidden" name="userId" id="activeUserId">
                        <div class="confirmation-text">
                            Are you sure you want to Activate this technician?
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-7"></div>
                    <div class="col d-flex justify-content-between">
                        <button class="btn tickets-modal-submit-btn" value="yes" id="activeBtn">Yes</button>
                        <button class="btn tickets-modal-submit-btn" value="no" data-dismiss="modal">No</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Technician Details -->
<div class="modal tickets-modal fade" id="filterTechnicianModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">Filter</div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-6 mb-3">
                        <input type="text" class="form-control" id="filtername" placeholder="Name" name="name">
                    </div>
                    <div class="col-sm-6 mb-3">
                        <select name="role" id="filterRole" class="form-control">
                            <option value="">Please Select Role</option>
                            <option value="Technician">Technician</option>
                            <option value="Admin">Admin</option>
                            <option value="Support Desk">Support Desk</option>
                        </select>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <input type="number" id="filterMobile" class="form-control" placeholder="Mobile Number">
                    </div>
                    <div class="col-sm-6 mb-3">
                        <input type="text" id="filterEmployeeId" class="form-control" placeholder="Employee ID">
                    </div>

                    <div class="col-12 mb-2 text-right">
                        <button type="reset" class="btn tickets-modal-submit-btn mr-2" id="resetTaskBtn">Clear
                            All</button>
                        <button type="button" class="btn tickets-modal-submit-btn" id="filterBtnTasks">Apply</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal tickets-modal fade" id="assignTechnicianModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">Add Techncians for <span id="teamName"></span> </div>
            <div class="modal-body">
                <form>
                    @csrf
                    <div class="row">
                        <input type="hidden" name="teamId" id="teamId">
                        <div class="col-sm-12 mb-3">
                            <select name="technicianInput" id="technicianInput" class="technician" multiple>
                            </select>
                        </div>
                        <div class="col-12 mb-2 text-right">
                            <button type="button" class="btn tickets-modal-submit-btn"
                                id="assignTechnicianBtn">Add</button>
                        </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
<div class="modal tickets-modal fade" id="addTeamModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">Add Team</div>
            <div class="modal-body">
                <form>
                    @csrf
                    <div class="row">

                        <div class="col-sm-12 mb-3">
                            <input type="text" id="teamNameStore" name="teamName" class="form-control"
                                placeholder="Team">
                        </div>

                        <div class="col-12 mb-2 text-right">
                            <button type="button" class="btn tickets-modal-submit-btn" id="addTeamBtn">Add</button>
                        </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>


@section('js-content')
<script>
const csrfToken = $('meta[name=csrf-token]').attr('content');
const getEmployeesURL = "{{route('get-technician-employee-names')}}";
const selectedUserDetailsURL = "{{route('get-selected-user-details')}}";
</script>
<script src="{{asset('public/dist/js/technicians.js')}}"></script>
<!-- Flatpickr JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.13.3/js/standalone/selectize.min.js"></script>
<script>
var select;
$(function() {

    var teamId;
    /* ---------------------------------------------------------------------------------- */

    $('#technicianName').change(function() {
        var selectedValue = $(this).val();
        var selectedText = $(this).find('option:selected').text();

        const selectedOption = $(this).find('option:selected');
        const empId = selectedOption.val();
        const empName = selectedOption.text(); // Or from data-* attribute
        const department = selectedOption.data('department'); // if available

        if (selectedValue) {
            $('#userEmpId').val(empId);
            $('#selectedUserEmpId').text(empId);
            $('#selectedUserDepartment').text(department);
            $('#addSelectedUserDetails').show();
        } else {
            $('#userEmpId').val('');
            $('#selectedUserEmpId').text('');
            $('#addSelectedUserDetails').hide();
        }
    });

    var $select = $('#technicianInput').selectize({
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
                url: '{{ route("technicians.teams") }}',
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
                                .USER_ID, // Adjust with your actual technician ID column
                            text: res[i]
                                .USER_NAME // Adjust with your actual technician name column
                        });
                    }

                    // Refresh options to reflect the changes
                    selectize.refreshOptions();
                    select = $select[0].selectize;
                }
            });
        }
    });

    var $select = $('#technicianName').selectize({
        plugins: ['remove_button'],
        delimiter: ',',
        persist: false,
        create: false,
        valueField: 'id',
        labelField: 'text',
        searchField: ['text'],
        placeholder: 'Technician Name',

        load: function(query, callback) {
            if (!query.length) return callback();
            $.ajax({
                url: getEmployeesURL,
                type: 'POST',
                dataType: 'json',
                data: {
                    type: 'public',
                    emp_name: query,
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    var options = [];
                    for (var i = 0; i < res.results.length; i++) {
                        options.push({
                            text: res.results[i].text,
                            id: res.results[i] .hrEmployeeID,
                            email: res.emailId[i].emailId,
                            department: res.department[i].department,
                            mobile: res.mobileNumber[i].mobileNumber,
                            name: res.results[i].text
                        });
                    }
                    callback(options);
                },
                error: function() {
                    callback();
                }
            });
        },
        onChange: function(value) {
            var selectedData = this.options[value];
            if (selectedData) {
                $('#addSelectedUserDetails').show();
                $('#selectedUserEmpId').text(value);
                $('#userEmpId').val(value);
                $('#selectedUserDepartment').text(selectedData.department);
                $('#newUserEmail').val(selectedData.email);
                $('#newUserMobile').val(selectedData.mobile);
                $('#newUserName').val(selectedData.name);
            } else {
                $('#addSelectedUserDetails').hide();
            }
        }
    });


    // Handle the button click
    $('#assignTechnicianBtn').click(function() {

        var selectize = $select[0].selectize;
        var selectedTechnicians = selectize.getValue();
        var technicians = $('#technicianInput').val();
        var teamId = $('#teamId').val();
        // Send AJAX request to assign technicians
        // Get CSRF token
        var csrfToken = $('meta[name="csrf-token"]').attr('content');

        $.ajax({
            url: "{{ route('teams-teachnicians.assign') }}", // Replace with your actual URL to assign technicians
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            data: {
                teamId: teamId,
                technicians: technicians
            },
            success: function(response) {
                console.log('Technicians assigned successfully:', response);
                // Handle success (e.g., show a message, update the UI, etc.)
                iziToast.show({
                    title: 'Success',
                    position: 'topRight',
                    color: 'green',
                    message: 'Technicians added to team'
                });

                $('#assignTechnicianModal').modal('hide');
                $('#teamTable').DataTable().ajax.reload(null, false);
                $('#techniciansTable').DataTable().ajax.reload(null, false);
                $('#teamTechnicianTable').DataTable().ajax.reload(null, false);
            },
            error: function(xhr, status, error) {
                console.error('Error assigning technicians:', error);
                // Handle error (e.g., show a message, etc.)
            }
        });
    });

    $('#addTeamBtn').click(function() {

        var name = $('#teamNameStore').val();
        var csrfToken = $('meta[name="csrf-token"]').attr('content');

        $.ajax({
            url: "{{ route('team.create') }}", // Replace with your actual URL to assign technicians
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            data: {
                name: name,
            },
            success: function(response) {
                console.log('New Team Created', response);
                // Handle success (e.g., show a message, update the UI, etc.)
                iziToast.show({
                    title: 'Success',
                    position: 'topRight',
                    color: 'green',
                    message: 'New Team Created'
                });

                $('#teamTable').DataTable().ajax.reload(null, false);
                $('#techniciansTable').DataTable().ajax.reload(null, false);
                $('#addTeamModal').modal('hide');
            },
            error: function(xhr, status, error) {
                console.error('Error assigning technicians:', error);
                // Handle error (e.g., show a message, etc.)
            }
        });
    });


    $("#filterTechnicianBtn").show();
    $("#addTechnicianBtn").show();

    // On loading a particular tab, show only the action buttons for that tab (filter / add)
    $("#categoriesTabLink").on('shown.bs.tab', function(ev) {
        $("#actionBtnsContainer button").hide();
        $("#filterTechnicianBtn").show();
        $("#addTechnicianBtn").show();
    });

    $("#subCategoriesTabLink").on('shown.bs.tab', function(ev) {
        $("#actionBtnsContainer button").hide();
        $("#filterSubCategoriesBtn").show();
        $("#teamAddBtn").show();
    });

    // On closing add category modal, remove validation errors and reset form
    $("#addCategoryModal").on('hidden.bs.modal', function() {
        $("#addCategoryForm").trigger('reset');
        $("#addCategoryForm").data('validator').resetForm();
        $("#addCategoryForm .form-control").removeClass('error').removeAttr('aria-invalid');
    });

    // On closing edit category modal, remove validation errors and reset form
    $("#editCategoryModal").on('hidden.bs.modal', function() {
        $("#editCategoryForm").trigger('reset');
        $("#editCategoryForm").data('validator').resetForm();
        $("#editCategoryForm .form-control").removeClass('error').removeAttr('aria-invalid');
    });

    // On closing add sub-category modal, remove validation errors and reset form
    $("#addTeamModal").on('hidden.bs.modal', function() {
        $("#addTeamForm").trigger('reset');
        $("#addTeamForm").data('validator').resetForm();
        $("#addTeamForm .form-control").removeClass('error').removeAttr('aria-invalid');
    });

    // On closing edit sub-category modal, remove validation errors and reset form
    $("#editSubCategoryModal").on('hidden.bs.modal', function() {
        $("#editSubCategoryForm").trigger('reset');
        $("#editSubCategoryForm").data('validator').resetForm();
        $("#editSubCategoryForm .form-control").removeClass('error').removeAttr('aria-invalid');
    });


    flatpickr(".datepicker", {
        dateFormat: "d-M-Y"
    });



    var techniciansTable = $('#techniciansTable').DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        dom: "<'row'<'col-sm-12'>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        ajax: {
            url: "{{ route('technicians') }}",
            data: function(d) {

                d.name = $('#filtername').val();
                d.mobile = $('#filterMobile').val();
                d.employeeId = $('#filterEmployeeId').val();
                d.role = $('#filterRole').val();


            }
        },
        columns: [{
                data: 'EMPLOYEE_ID',
                name: 'employeeID',
                className: 'text-font-0',
                searchable: true
            },
            {
                data: 'LOGIN_ID',
                name: 'loginid',
                className: 'text-font-0',
                searchable: true
            },
            {
                data: 'ROLE',
                name: 'role',
                className: 'text-font-0',
                searchable: true
            },
            {
                data: 'USER_NAME',
                name: 'username',
                className: 'text-font-0',
                searchable: true
            },
            {
                data: 'MOBILE_NUMBER',
                name: 'attachment',
                className: 'text-font-0',
                searchable: true
            },
            {
                data: 'EMAIL',
                name: 'email',
                className: 'text-font-0',
                searchable: true
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                className: 'text-center'
            },
        ]
    });


    var teamsTable = $('#teamTable').DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        dom: "<'row'<'col-sm-12'>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        ajax: {
            url: "{{ route('teams') }}",
            data: function(d) {

            }
        },
        columns: [{
                data: null,
                render: function(data, type, row, meta) {
                    // Render serial number
                    return meta.row + 1;
                },
                className: 'text-font-0'
            },
            {
                data: 'TEAM_NAME',
                name: 'subcategoryname',
                className: 'text-font-0 team-name'
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                className: 'text-center'
            },
        ]
    });


    var teamTechnicianTable = $('#teamTechnicianTable').DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        dom: "<'row'<'col-sm-12'>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        ajax: {
            url: "{{ route('teams-teachnicians') }}",
            data: function(d) {

                d.teamId = teamId;
            }
        },
        columns: [{
                data: null,
                render: function(data, type, row, meta) {
                    // Render serial number
                    return meta.row + 1;
                },
                className: 'text-font-0'
            },
            {
                data: 'USER_NAME',
                name: 'username',
                className: 'text-font-0'
            },
            {
                data: 'ALLOCATED_ON',
                name: 'allocated_on',
                orderable: false,
                searchable: false,
                className: 'text-center'
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                className: 'text-center'
            },
        ]
    });

    // Add click event listener for TEAM_NAME column
    $('#teamTable tbody').on('click', 'td.team-name', function() {
        // Get the data for the clicked row
        var data = teamsTable.row($(this).closest('tr')).data();
        // Pass TEAM_ID to a variable
        teamId = data.TEAM_ID;

        // Now you can use the teamId variable as needed
        $('#teamTechnicianTable').DataTable().ajax.reload(null, false);
    });



    $('#activeBtn').on('click', function() {

        let userId = $('#activeUserId').val();

        $.ajax({
            url: '{{ route("technicians.status") }}',
            method: 'POST', // Change to POST
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: {
                // Include any data you want to send with the POST request
                userId: userId,
                status: 'Y'
                // ...
            },
            success: function(response) {
                // Handle the success response
                iziToast.show({
                    title: 'Success',
                    position: 'topRight',
                    color: '#9cd5a9', // Set the color to your desired color
                    message: 'Status Upadated'
                });

                $('#confirmActivateTechnicianModal').modal("hide");
                $('#techniciansTable').DataTable().ajax.reload(null, false);
            },
            error: function(error) {
                // Handle the error response
                console.error('Error fetching data:', error);
            }
        });

    });

    $('#deactiveBtn').on('click', function() {

        let userId = $('#deactiveUserId').val();

        $.ajax({
            url: '{{ route("technicians.status") }}',
            method: 'POST', // Change to POST
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: {
                // Include any data you want to send with the POST request
                userId: userId,
                status: 'N'
                // ...
            },
            success: function(response) {
                // Handle the success response
                iziToast.show({
                    title: 'Success',
                    position: 'topRight',
                    color: '#9cd5a9', // Set the color to your desired color
                    message: 'Status Upadated'
                });

                $('#confirmDeactivateTechnicianModal').modal("hide");
                $('#techniciansTable').DataTable().ajax.reload(null, false);

            },
            error: function(error) {
                // Handle the error response
                console.error('Error fetching data:', error);
            }
        });

    });

    $("#filterBtnTasks").on('click', function() {

        techniciansTable.page.len(-1).draw();

        $('#filterTechnicianModal').modal('hide');

    });

    $("#resetTaskBtn").on('click', function() {

        $('#filtername').val('');
        $('#filterMobile').val('');
        $('#filterEmployeeId').val('');
        $('#filterRole').val('');

        techniciansTable.ajax.reload(null, false);

        techniciansTable.page.len(10).draw();

        $('#filterTechnicianModal').modal('hide');


    });

    $("#addNewTechnicianForm").submit(function(e) {
        // Prevent Default functionality
        e.preventDefault();

        // Serialize form data
        var formData = $(this).serialize();

        $.ajax({
            method: "post",
            url: '{{route("technicians.add")}}',
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: formData,
            success: function(data) {
                console.log("Success");
                if (data.successCode == 1) {
                    toastr.success(data.message, '', {
                        closeButton: true
                    });
                    $('#techniciansTable').DataTable().ajax.reload(null, false);
                    $('#addNewTechnicianModal').modal('hide');

                    // $('#technicianName').val('');
                    // $('#userRole').val('');
                } else {
                    toastr.error(data.message, '', {
                        closeButton: true
                    });
                }

            },
            error: function() {
                console.log("Error");
                toastr.error('Some error occured. Please try again', '', {
                    closeButton: true
                });
            }
        });

    });



    $('#editTechnician').on('submit', function(e) {
        e.preventDefault(); // Prevent the default form submission

        // Get form data
        var formData = {
            technicianId: $('#technicianIdEdit').val(),
            technicianName: $('#technicianNameEdit').val(),
            technicianMobile: $('#technicianMobileEdit').val(),
            technicianEmail: $('#technicianEmailEdit').val(),
            technicianLogin: $('#technicianLoginEdit').val(),
            technicianPassword: $('#technicianPasswordEdit').val(),
        };

        var csrfToken = $('meta[name="csrf-token"]').attr('content');

        // Perform AJAX request
        $.ajax({
            url: "{{ route('technicians.edit') }}", // Replace with your server endpoint
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken // Include the CSRF token in the headers
            },
            success: function(response) {
                // Handle success response
                alert('Form submitted successfully!');
                $('#editTechnicianDetailsModal').modal('hide');
                $('#techniciansTable').DataTable().ajax.reload(null, false);
            },
            error: function(xhr, status, error) {
                // Handle error response
                alert('An error occurred: ' + error);
            }
        });
    });




    /* ---------------------------------------------------------------------------------- */

});

function statusChange(userId, status) {
    if (status === 'Y') {
        // Set the id attribute of the hidden input to the userId value
        $('#deactiveUserId').val(userId);

        $('#confirmDeactivateTechnicianModal').modal("show");
    } else {
        // Set the id attribute of the hidden input to the userId value
        $('#activeUserId').val(userId);

        $('#confirmActivateTechnicianModal').modal("show");
    }
}

function edit(id, name, mobile, loginId, email) {
    $('#technicianIdEdit').val(id);
    $('#technicianNameEdit').val(name);
    $('#technicianMobileEdit').val(mobile);
    $('#technicianEmailEdit').val(email);
    $('#technicianLoginEdit').val(loginId);    
    $('#editTechnicianDetailsModal').modal('show');
}

function assign(id, name) {
    $('#teamName').html(name);
    $('#teamId').val(id);

    $.ajax({
        url: '{{ route("technicians.get") }}',
        dataType: 'json',
        delay: 250,
        data: {
            teamId: id
        }, // Pass the teamId as a parameter
        success: function(response) {
            // Assuming the response is an array of technician objects
            var technicians = response; // Adjust this based on your actual response structure
            // Extract EMPLOYEE_IDs
            var employeeIds = technicians.map(function(technician) {
                return technician.USER_ID;
            });

            select.setValue(employeeIds);

        },
        error: function(xhr, status, error) {
            // Handle errors here
            console.error(xhr, status, error);
        }
    });

    $('#assignTechnicianModal').modal('show');
}

function remove(id) {
    // Perform AJAX request
    $.ajax({
        url: "{{ route('teams-teachnicians.remove') }}", // Replace with your server endpoint
        type: 'POST',
        data: {
            memberId: id
        },
        headers: {
            'X-CSRF-TOKEN': csrfToken // Include the CSRF token in the headers
        },
        success: function(response) {
            // Handle success response
            alert('Removed successfully!');
            $('#teamTechnicianTable').DataTable().ajax.reload(null, false);
        },
        error: function(xhr, status, error) {
            // Handle error response
            alert('An error occurred: ' + error);
        }
    });
}

function toggleEligibility(checkbox){
    const memberId = checkbox.value;
    const isEligible = checkbox.checked ? 'Y' : 'N';

    $.ajax({
        url: "{{ route('is-team.menbers.eligible') }}", // Replace with your server endpoint
        type: 'POST',
        data: {
            memberId: memberId,
            isEligible: isEligible
            
        },      
        headers: {
            'X-CSRF-TOKEN': csrfToken // Include the CSRF token in the headers
        },
        success: function(response) {
            // Handle success response
            alert('Updated successfully!');
            $('#teamTechnicianTable').DataTable().ajax.reload(null, false);
        },
        error: function(xhr, status, error) {
            // Handle error response
            alert('An error occurred: ' + error);
        }
    });
}
const technicianMobileEdit = document.getElementById('technicianMobileEdit');

// Ensure only numbers are allowed, and limit the length to 10 digits
technicianMobileEdit.addEventListener('input', function() {
    // Remove all non-digit characters
    this.value = this.value.replace(/\D/g, '');

    // Limit the input to 10 digits
    if (this.value.length > 10) {
        this.value = this.value.slice(0, 10);
    }
});
</script>
@endsection