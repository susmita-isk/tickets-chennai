@extends('layouts.main.app')

@section('page-title', 'Departments')

@section('css-content')
<link rel="stylesheet" href="{{asset('public/dist/css/pages/page1/page1.css')}}">
<style>
    .sidebar-mini.sidebar-collapse .main-sidebar, .sidebar-mini.sidebar-collapse .main-sidebar::before {
    margin-left: 0;
    width: 2.2rem;
}
</style>   
@endsection

@section('page-content')
<div class="container-fluid">
    <div id="actionBtnsContainer">
        <button class="btn btn-default tickets-action-btn d-none" id="addTeamBtn" data-toggle="modal" data-target="#createTeamModal">
            <i class="fas fa-plus"></i>&nbsp; Add
        </button>
        <button class="btn btn-default tickets-action-btn d-none" id="addTechnicanBtn" data-toggle="modal" data-target="#addNewTechnicianModal">
            <i class="fas fa-plus"></i>&nbsp; Add
        </button>
    </div>
    <ul class="nav nav-tabs" id="ticketsMainPageTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link tickets-tab-link active" id="departments-tab" data-toggle="tab" data-target="#departments_tab_panel" type="button" aria-controls="departments_tab_panel" aria-selected="true">
                <img src="{{asset('public/img/menu1.svg')}}" width="14" height="auto"> Department
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link tickets-tab-link" id="teams-tab" data-toggle="tab" data-target="#teams_tab_panel" type="button" aria-controls="teams_tab_panel" aria-selected="false">
                <img src="{{asset('public/img/teams1.svg')}}" width="14" height="auto"> Teams
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link tickets-tab-link" id="technicians-tab" data-toggle="tab" data-target="#technicians_tab_panel" type="button" aria-controls="technicians_tab_panel" aria-selected="false">
                <img src="{{asset('public/img/hand-holding-wrench1.svg')}}" width="14" height="auto"> Technicians
            </button>
        </li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane tickets-tab-pane fade show active" id="departments_tab_panel" role="tabpanel" aria-labelledby="departments-tab">
            {{-- Departments Tab --}}
            <div class="tickets-tab-pane-content ">
                <div class="row mb-2 p-3">
                    <div class="col-sm-6">
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
                                        <a href="{{url('tickets')}}" class="btn tickets-card-link">Open</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
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
                </div>
                <div class="row mt-2 mb-2 p-3">
                    <div class="col-sm-6">
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
                                        <a href="{{url('tickets')}}" class="btn tickets-card-link">Open</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
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
                                        <a href="{{url('tickets')}}" class="btn tickets-card-link">Open</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-2 mb-2 p-3">
                    <div class="col-sm-6">
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

        <div class="tab-pane tickets-tab-pane fade" id="teams_tab_panel" role="tabpanel" aria-labelledby="teams-tab">
            {{-- Teams Tab --}}
            <div class="tickets-tab-pane-content">
                <div class="row">
                    <div class="col-sm-6">
                        {{-- Teams Table --}}
                        <div class="table-container">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover tickets-table">
                                    <thead>
                                        <tr>
                                            <th>ID <i class="fas fa-angle-down"></i></th>
                                            <th>Subject <i class="fas fa-angle-down"></i></th>
                                            <th>Assign <i class="fas fa-angle-down"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>IT Support</td>
                                            <td class="text-left">
                                                <a href="#assignTechnicianModal" data-toggle="modal">
                                                    <img src="{{asset('public/img/icons/assign-technician.png')}}" alt="" width="20" height="auto">
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>2</td>
                                            <td>IT Infrastructure</td>
                                            <td>
                                                <a href="#">
                                                    <img src="{{asset('public/img/icons/assign-technician.png')}}" alt="" width="20" height="auto">
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>3</td>
                                            <td>ERP Support</td>
                                            <td>
                                                <a href="#">
                                                    <img src="{{asset('public/img/icons/assign-technician.png')}}" alt="" width="20" height="auto">
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>4</td>
                                            <td>Website</td>
                                            <td>
                                                <a href="#">
                                                    <img src="{{asset('public/img/icons/assign-technician.png')}}" alt="" width="20" height="auto">
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>5</td>
                                            <td>Application</td>
                                            <td>
                                                <a href="#">
                                                    <img src="{{asset('public/img/icons/assign-technician.png')}}" alt="" width="20" height="auto">
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>6</td>
                                            <td>Dhananjaya</td>
                                            <td>
                                                <a href="#">
                                                    <img src="{{asset('public/img/icons/assign-technician.png')}}" alt="" width="20" height="auto">
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>7</td>
                                            <td>HR Support</td>
                                            <td>
                                                <a href="#">
                                                    <img src="{{asset('public/img/icons/assign-technician.png')}}" alt="" width="20" height="auto">
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>8</td>
                                            <td>Facility</td>
                                            <td>
                                                <a href="#">
                                                    <img src="{{asset('public/img/icons/assign-technician.png')}}" alt="" width="20" height="auto">
                                                </a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        {{-- Recently Allocated Technicians Table --}}
                        <div class="table-container">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover tickets-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Technician</th>
                                            <th>Allocated On</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>Amrita Ghosh</td>
                                            <td>10-Aug-2023</td>
                                            <td>
                                                <a href="#confirmRemoveTechnicianModal" data-toggle="modal">
                                                    <img src="{{asset('public/img/icons/deactivate.png')}}" alt="" width="20" height="auto">
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>2</td>
                                            <td>Kanhu Chran Gouda</td>
                                            <td>18-Sep-2023</td>
                                            <td>
                                                <a href="#confirmRemoveTechnicianModal" data-toggle="modal">
                                                    <img src="{{asset('public/img/icons/deactivate.png')}}" alt="" width="20" height="auto">
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>3</td>
                                            <td>Nishanth Kumar P</td>
                                            <td>01-Oct-2023</td>
                                            <td>
                                                <a href="#confirmRemoveTechnicianModal" data-toggle="modal">
                                                    <img src="{{asset('public/img/icons/deactivate.png')}}" alt="" width="20" height="auto">
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>4</td>
                                            <td>Sreedhar S</td>
                                            <td>01-Oct-2023</td>
                                            <td>
                                                <a href="#confirmRemoveTechnicianModal" data-toggle="modal">
                                                    <img src="{{asset('public/img/icons/deactivate.png')}}" alt="" width="20" height="auto">
                                                </a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane tickets-tab-pane fade" id="technicians_tab_panel" role="tabpanel" aria-labelledby="technicians-tab">
            {{-- Technicians tab --}}
            <div class="tickets-tab-pane-content">
                <div class="row">
                    <div class="col">
                        <div class="table-container">
                            <div class="table-responsive">
                                <table class="table table-hover" id="techniciansTable">
                                    <thead>
                                        <tr>
                                            <td>Login <i class="fas fa-angle-down"></i></td>
                                            <td>Name <i class="fas fa-angle-down"></i></td>
                                            <td>Mobile No <i class="fas fa-angle-down"></i></td>
                                            <td>Joining Date <i class="fas fa-angle-down"></i></td>
                                            <td>Relieving Date <i class="fas fa-angle-down"></i></td>
                                            <td>Action(s)</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Abhilash</td>
                                            <td>Abhilash S</td>
                                            <td>8974568956</td>
                                            <td>02-May-2018</td>
                                            <td></td>
                                            <td>
                                                <div class="table-action-btns-container d-flex">
                                                    <a href="#confirmDeactivateTechnicianModal" title="Deactivate" data-toggle="modal">
                                                        <img src="{{asset('public/img/icons/subtract.png')}}" alt="" width="20" height="auto">
                                                    </a>
                                                    <a href="#relieveTechnicianModal" data-toggle="modal" title="Relieve">
                                                        <img src="{{asset('public/img/icons/edit.png')}}" alt="" width="20" height="auto">
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Abhilash</td>
                                            <td>Abhilash S</td>
                                            <td>8974568956</td>
                                            <td>02-May-2018</td>
                                            <td></td>
                                            <td>
                                                <div class="table-action-btns-container d-flex">
                                                    <a href="#confirmDeactivateTechnicianModal" title="Deactivate" data-toggle="modal">
                                                        <img src="{{asset('public/img/icons/subtract.png')}}" alt="" width="20" height="auto">
                                                    </a>
                                                    <a href="#relieveTechnicianModal" data-toggle="modal" title="Relieve">
                                                        <img src="{{asset('public/img/icons/edit.png')}}" alt="" width="20" height="auto">
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Abhilash</td>
                                            <td>Abhilash S</td>
                                            <td>8974568956</td>
                                            <td>02-May-2018</td>
                                            <td></td>
                                            <td>
                                                <div class="table-action-btns-container d-flex">
                                                    <a href="#confirmActivateTechnicianModal" title="Activate" data-toggle="modal">
                                                        <img src="{{asset('public/img/icons/activate.png')}}" alt="" width="20" height="auto">
                                                    </a>
                                                    <a href="#relieveTechnicianModal" data-toggle="modal" title="Relieve">
                                                        <img src="{{asset('public/img/icons/edit.png')}}" alt="" width="20" height="auto">
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Abhilash</td>
                                            <td>Abhilash S</td>
                                            <td>8974568956</td>
                                            <td>02-May-2018</td>
                                            <td></td>
                                            <td>
                                                <div class="table-action-btns-container d-flex">
                                                    <a href="#confirmDeactivateTechnicianModal" title="Deactivate" data-toggle="modal">
                                                        <img src="{{asset('public/img/icons/subtract.png')}}" alt="" width="20" height="auto">
                                                    </a>
                                                    <a href="#relieveTechnicianModal" data-toggle="modal" title="Relieve">
                                                        <img src="{{asset('public/img/icons/edit.png')}}" alt="" width="20" height="auto">
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
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

<!-- Modals -->
<!-- Modal for Adding Team -->
<div class="modal tickets-modal fade" id="createTeamModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                Create Team
                <button class="close" data-dismiss="modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form action="" method="post">
                    <div class="row">
                        <div class="col form-group">
                            <label for="" class="col-form-label">Team</label>
                            <input type="text" name="" id="" class="form-control" placeholder="Enter Team Name">
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

<!-- Modal for assigning technician -->
<div class="modal tickets-modal fade" id="assignTechnicianModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                Assign Technician
                <button class="close" data-dismiss="modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form action="" method="post">
                    <div class="row">
                        <div class="col form-group">
                            <label for="" class="col-form-label">Technician</label>
                            <select name="" id="" class="form-control">
                                <option value="">Please Select</option>
                            </select>
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

<!-- Confirmation Modal for Removing Technician -->
<div class="modal tickets-modal fade" id="confirmRemoveTechnicianModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">Confirmation</div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col">
                        <div class="confirmation-text">
                            Are you sure you want to Remove this technician?
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-7"></div>
                    <div class="col d-flex justify-content-between">
                        <button class="btn tickets-modal-submit-btn" value="yes">Yes</button>
                        <button class="btn tickets-modal-submit-btn" value="no" data-dismiss="modal">No</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Collecting New Technician Details -->
<div class="modal tickets-modal fade" id="addNewTechnicianModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                Create Technician
                <button class="close" data-dismiss="modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form action="" method="post">
                    <div class="row">
                        <div class="col form-group">
                            <label for="" class="col-form-label">Technician Name</label>
                            <input type="text" class="form-control" placeholder="Enter Name">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col form-group">
                            <label for="" class="col-form-label">Mobile Number</label>
                            <input type="text" class="form-control" placeholder="Mobile">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col form-group">
                            <label for="" class="col-form-label">Joining Date</label>
                            <input type="text" class="form-control" placeholder="DD-MMM-YYYY">
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
                <form action="" method="post">
                    <div class="row">
                        <div class="col form-group">
                            <label for="" class="col-form-label">Technician Name</label>
                            <input type="text" class="form-control" placeholder="Enter Name">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col form-group">
                            <label for="" class="col-form-label">Mobile Number</label>
                            <input type="text" class="form-control" placeholder="Mobile">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col form-group">
                            <label for="" class="col-form-label">Joining Date</label>
                            <input type="text" class="form-control" placeholder="DD-MMM-YYYY">
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
                            <label for="" class="col-form-label">Relieving Date</label>
                            <input type="text" class="form-control" placeholder="DD-MMM-YYYY">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col text-center">
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
                        <div class="confirmation-text">
                            Are you sure you want to Deactivate this technician?
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-7"></div>
                    <div class="col d-flex justify-content-between">
                        <button class="btn tickets-modal-submit-btn" value="yes">Yes</button>
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
                        <div class="confirmation-text">
                            Are you sure you want to Activate this technician?
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-7"></div>
                    <div class="col d-flex justify-content-between">
                        <button class="btn tickets-modal-submit-btn" value="yes">Yes</button>
                        <button class="btn tickets-modal-submit-btn" value="no" data-dismiss="modal">No</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@section('js-content')
<script src="{{asset('public/dist/js/departments.js')}}"></script>
@endsection