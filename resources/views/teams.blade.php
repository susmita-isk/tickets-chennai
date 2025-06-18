@extends('layouts.main.app')

@section('page-title', 'Teams')

@section('css-content')
<link rel="stylesheet" href="{{asset('public/dist/css/teams.css')}}">
@endsection

@section('breadcrumb-menu')
<li class="breadcrumb-item active">Teams</li>
@endsection

@section('page-content')
<div class="container-fluid">
    <div class="text-right">
        <button class="btn btn-default tickets-action-btn" id="addTeamBtn" data-toggle="modal" data-target="#createTeamModal">
            <i class="fas fa-plus"></i>&nbsp; Add
        </button>
    </div>
    <div id="teams-container-card">

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
                                        <button class="btn tickets-action-btn-transparent" data-target="#assignTechnicianModal" data-toggle="modal">
                                            <img src="{{asset('public/img/icons/assign-technician.png')}}" alt="" width="20" height="auto">
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>IT Infrastructure</td>
                                    <td>
                                        <button class="btn tickets-action-btn-transparent" data-target="#assignTechnicianModal" data-toggle="modal">
                                            <img src="{{asset('public/img/icons/assign-technician.png')}}" alt="" width="20" height="auto">
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>ERP Support</td>
                                    <td>
                                        <button class="btn tickets-action-btn-transparent" data-target="#assignTechnicianModal" data-toggle="modal">
                                            <img src="{{asset('public/img/icons/assign-technician.png')}}" alt="" width="20" height="auto">
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>4</td>
                                    <td>Website</td>
                                    <td>
                                        <button class="btn tickets-action-btn-transparent" data-target="#assignTechnicianModal" data-toggle="modal">
                                            <img src="{{asset('public/img/icons/assign-technician.png')}}" alt="" width="20" height="auto">
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>5</td>
                                    <td>Application</td>
                                    <td>
                                        <button class="btn tickets-action-btn-transparent" data-target="#assignTechnicianModal" data-toggle="modal">
                                            <img src="{{asset('public/img/icons/assign-technician.png')}}" alt="" width="20" height="auto">
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>6</td>
                                    <td>Dhananjaya</td>
                                    <td>
                                        <button class="btn tickets-action-btn-transparent" data-target="#assignTechnicianModal" data-toggle="modal">
                                            <img src="{{asset('public/img/icons/assign-technician.png')}}" alt="" width="20" height="auto">
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>7</td>
                                    <td>HR Support</td>
                                    <td>
                                        <button class="btn tickets-action-btn-transparent" data-target="#assignTechnicianModal" data-toggle="modal">
                                            <img src="{{asset('public/img/icons/assign-technician.png')}}" alt="" width="20" height="auto">
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>8</td>
                                    <td>Facility</td>
                                    <td>
                                        <button class="btn tickets-action-btn-transparent" data-target="#assignTechnicianModal" data-toggle="modal">
                                            <img src="{{asset('public/img/icons/assign-technician.png')}}" alt="" width="20" height="auto">
                                        </button>
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
                                        <button class="btn tickets-action-btn-transparent" data-target="#confirmRemoveTechnicianModal" data-toggle="modal">
                                            <img src="{{asset('public/img/icons/deactivate.png')}}" alt="" width="20" height="auto">
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>Kanhu Chran Gouda</td>
                                    <td>18-Sep-2023</td>
                                    <td>
                                        <button class="btn tickets-action-btn-transparent" data-target="#confirmRemoveTechnicianModal" data-toggle="modal">
                                            <img src="{{asset('public/img/icons/deactivate.png')}}" alt="" width="20" height="auto">
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>Nishanth Kumar P</td>
                                    <td>01-Oct-2023</td>
                                    <td>
                                        <button class="btn tickets-action-btn-transparent" data-target="#confirmRemoveTechnicianModal" data-toggle="modal">
                                            <img src="{{asset('public/img/icons/deactivate.png')}}" alt="" width="20" height="auto">
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>4</td>
                                    <td>Sreedhar S</td>
                                    <td>01-Oct-2023</td>
                                    <td>
                                        <button class="btn tickets-action-btn-transparent" data-target="#confirmRemoveTechnicianModal" data-toggle="modal">
                                            <img src="{{asset('public/img/icons/deactivate.png')}}" alt="" width="20" height="auto">
                                        </button>
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
                            <label for="create_team_name" class="col-form-label">Team</label>
                            <input type="text" name="" id="create_team_name" class="form-control" placeholder="Enter Team Name">
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
                            <label for="technician_to_assign" class="col-form-label">Technician</label>
                            <select name="" id="technician_to_assign" class="form-control">
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
@endsection

@section('js-content')
@endsection