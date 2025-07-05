@extends('layouts.main.app')

@section('page-title', 'Dashboard')

@section('css-content')
<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.13.3/css/selectize.default.min.css">
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"> -->
<style>

.form-control {
    padding: 1px 5px;
    height: 30px;
}

.cus-btn {
    padding: 4px 5px;
    border: none;
}
.total-tickets-para{
    font-size: 14px;
    color: grey;
}

.submit-btn,
.submit-btn:hover,
.submit-btn:active {
    background: #1eb11e !important;
}

.count-no{
    color: #4150de;
    background: #bae7ba;
    padding: 2px 10px;
    border-radius: 12px;
}
.reset-btn,
.reset-btn:hover,
.reset-btn:active {
    background: #ba3232 !important;
}
.selectize-input{
    padding: 3px 8px;
    border-radius: .25rem;
    line-height: 1;
    display: block;
    width: 100%;
    font-size: 0.7rem;
    font-weight: 600;
    color: #000;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #ced4da;
    box-shadow: inset 0 0 0 transparent;
    transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
}
.selectize-input>input{
    width: 100%;
    color: #000;
    padding:1px!important
    /* font-size: 14px; */
}

.toggle-container {
    width: 57px;
    height: 29px;
    border-radius: 50px;
    position: relative;
    cursor: pointer;
    transition: background-color 0.5s ease;
    background: #ECECEC;
    /* box-shadow: -4px -3px 4px 0px rgba(0, 0, 0, 0.25) inset, 4px 0px 4px 0px rgba(0, 0, 0, 0.25) inset, 0px 4px 4px 0px rgba(0, 0, 0, 0.25) inset, 0px 6px 8px 3px rgba(0, 0, 0, 0.10) inset; */

    box-shadow: -4px -3px 4px 0px rgba(0, 0, 0, 0.25) inset, 4px 0px 4px 0px rgba(0, 0, 0, 0.25) inset, 0px 4px 4px 0px rgba(0, 0, 0, 0.25) inset, 0px 6px 8px 3px rgba(0, 0, 0, 0.10) inset;
}

.toggle-button {
    width: 23px;
    height: 23px;
    background-color:  #D9D9D9;
    border-radius: 50%;
    position: absolute;
    top: 2px;
    left: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: left 0.5s ease;
    /* box-shadow: -3px 2px 4px 0px rgba(0, 0, 0, 0.25) inset, 4px 0px 4px 0px rgba(0, 0, 0, 0.25) inset, 0px 3px 4px 0px rgba(0, 0, 0, 0.25) inset;
    filter: drop-shadow(1px 2px 6px rgba(0, 0, 0, 0.25)); */

    box-shadow: -3px 2px 4px 0px rgba(0, 0, 0, 0.25) inset, 4px 0px 4px 0px rgba(0, 0, 0, 0.25) inset, 0px 3px 4px 0px rgba(0, 0, 0, 0.25) inset;
    filter: drop-shadow(1px 2px 6px rgba(0, 0, 0, 0.25));
}

.toggle-container.active {
    /* border-radius: 50px;
    background: #FDAC0D;
    box-shadow: 0px -3px 3px 0px rgba(0, 0, 0, 0.25) inset, 2px 0px 6px 0px rgba(0, 0, 0, 0.25) inset, 0px 3px 8px 0px rgba(0, 0, 0, 0.25) inset, 0px 4px 5px 1px rgba(0, 0, 0, 0.10) inset; */

    border-radius: 50px;
    background: #FDAC0D;
    box-shadow: 0px -4px 4px 0px rgba(0, 0, 0, 0.25) inset, 5px 0px 4px 0px rgba(0, 0, 0, 0.25) inset, 0px 7px 4px 0px rgba(0, 0, 0, 0.25) inset, 0px 6px 8px 3px rgba(0, 0, 0, 0.10) inset;
}

.toggle-container.active .toggle-button {
    left: 29px;
    /* background-color: #FFB20D;
    box-shadow: -3px 2px 4px 0px rgba(0, 0, 0, 0.25) inset, 4px 0px 4px 0px rgba(0, 0, 0, 0.25) inset, 0px 3px 4px 0px rgba(0, 0, 0, 0.25) inset;
    filter: drop-shadow(1px 2px 6px rgba(0, 0, 0, 0.25)); */

    background-color: #FFB20D;
    box-shadow: -3px 2px 4px 0px rgba(0, 0, 0, 0.25) inset, 4px 0px 4px 0px rgba(0, 0, 0, 0.25) inset, 0px 3px 4px 0px rgba(0, 0, 0, 0.25) inset;
    filter: drop-shadow(1px 2px 6px rgba(0, 0, 0, 0.25));
}
.toggle-container.active .toggle-button .icon i{
    color:#ededed;
}
.toggle-container .toggle-button .icon i{
    color: #000;
    font-size: 12px;
}

/*.flatpickr-day.highlight {
    background-color: #ffd700 !important;
    color: #000 !important;
    border-radius: 50%;
}*/

.highcharts-credits{
    display:none;
}

.dataTables_processing {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(12%, -50%);
    background-color: rgba(255, 255, 255, 0.8);
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
    z-index: 9999;
}

.dataTables_processing::after {
    content: '';
    display: block;
    margin: 10px auto;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 4px solid #28a745;
    border-top: 4px solid transparent;
    animation: spin 1s linear infinite;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

@endsection

@section('breadcrumb-menu')
<li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('page-content')

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <!-- Loader -->
            <div id="chart-loader" class="dataTables_processing" style="display:none;">Processing...</div>       
            
            <!-- Global Dates and Teams -->
            <div class="card" style="background: #bccfbc;">
                <!-- Filter Content -->
                <div class="row">
                    <div class="col-md-6 col-lg-10">
                        <div class="card-body">
                            <div class="row justify-content-start">
                                <div class="col-md-2">
                                    <input type="text" class="form-control datepicker" id="global_from_date"
                                        name="global_from_date" placeholder="From Date (DD-MM-YY)"
                                        title="From Date (DD-MM-YY)">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control datepicker" id="global_to_date" name="global_to_date"
                                        placeholder="To Date (DD-MM-YY)" title="To Date (DD-MM-YY)">
                                </div>
                                <div class="col-md-4">
                                    <select name="globalTeamFilter" id="globalTeamFilter" multiple>
                                        <option value="">Team</option>
                                        @foreach ($teams as $item)
                                        <option value="{{ $item['teamName'] }}">{{ $item['teamName'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-2">
                                    <button type="button" class="btn btn-primary btn-sm cus-btn" style="background:#1c761c !important;"
                                        id="globalFilterBtn">Filter All Chart</button>
                                    <div id="all-chart-loader" class="dataTables_processing" style="display:none;">Processing...</div>   
                                </div>

                                <div class="col-md-1 d-flex align-items-center justify-content-center">                                
                                    <div class="toggle-container" onclick="toggleGlobalTicketButton(this)" id="global_checkBox" title="Exclude tickets log by system">
                                        <div class="toggle-button">
                                            <div class="icon">                                        
                                            <i class="fas fa-chart-bar"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--  // Global Dates and Teams -->

            <!-- Engineer's Ticket Bar chart -->
            <div class="card">
                <!-- Filter Content -->
                <div class="row">
                    <div class="col-md-6 col-lg-10">
                        <div class="card-body">
                            <div class="row ">
                                <div class="col-md-2">
                                    <input type="text" class="form-control datepicker" id="et_from_date"
                                        name="et_from_date" placeholder="From Date (DD-MM-YY)"
                                        title="From Date (DD-MM-YY)">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control datepicker" id="et_to_date" name="et_to_date"
                                        placeholder="To Date (DD-MM-YY)" title="To Date (DD-MM-YY)">
                                </div>
                                <div class="col-md-4">
                                    <select name="etTeamFilter" id="etTeamFilter" multiple>
                                        <option value="">Team</option>
                                        @foreach ($teams as $item)
                                        <option value="{{ $item['teamName'] }}">{{ $item['teamName'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-2">
                                    <button type="button" class="btn btn-primary btn-sm cus-btn submit-btn"
                                        id="etFilterBtn">Submit</button>
                                    <button type="button" class="btn btn-primary btn-sm cus-btn reset-btn"
                                        id="etResetBtn">Reset</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="wrapper">
                    <div class="chart p-2">
                        <div class="row">
                            <div class="col-md-3 graph-header-sec">
                                <h2 class="graph-header">Engineer's Ticket</h2>
                            </div>
                            <div class="col-md-7">
                                <h2 class="text-right mb-0 mt-2 total-tickets-para" id="total-tickets-no"> Total Tickets :
                                    <span class="count-no" id="engineer-total-ticket-count"></span>
                                </h2>
                            </div>
                            <div class="col-md-1 d-flex align-items-center justify-content-center">                                
                                <div class="toggle-container" onclick="toggletEngineerTicketButton(this)" id="et_checkBox" title="Exclude tickets log by system">
                                    <div class="toggle-button">
                                        <div class="icon">                                        
                                        <i class="fas fa-chart-bar"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                            <div id="engineerTicket" style="display: block;box-sizing: border-box;height: 430px;width: 100% !important;!i;!;"></div>                        
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- // Engineer's Ticket Bar chart -->

            <!-- Ticket Type Bar chart -->
            <div class="card">
                <!-- Filter Content -->
                <div class="row">
                    <div class="col-md-6 col-lg-10">
                        <div class="card-body">
                            <div class="row ">
                                <div class="col-md-2">
                                    <input type="text" class="form-control datepicker" id="t_type_from_date"
                                        name="t_type_from_date" placeholder="From Date (DD-MM-YY)"
                                        title="From Date (DD-MM-YY)">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control datepicker" id="t_type_to_date"
                                        name="t_type_to_date" placeholder="To Date (DD-MM-YY)"
                                        title="To Date (DD-MM-YY)">
                                </div>
                                <div class="col-md-4">
                                    <select name="tTypeTeamfilter" id="tTypeTeamfilter" multiple>
                                        <option value="">Team</option>
                                        @foreach ($teams as $item)
                                        <option value="{{ $item['teamName'] }}">{{ $item['teamName'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-2">
                                    <button type="button" class="btn btn-primary btn-sm cus-btn submit-btn"
                                        id="ttFilterBtn">Submit</button>
                                    <button type="button" class="btn btn-primary btn-sm cus-btn reset-btn"
                                        id="ttResetBtn">Reset</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="wrapper">
                    <div class="chart p-2">
                        <div class="row">
                            <div class="col-md-3 graph-header-sec">
                                <h2 class="graph-header">Ticket Type</h2>
                            </div>
                             <div class="col-md-7">
                                <h2 class="text-right mb-0 mt-2 total-tickets-para" id="total-tickets-no"> Total Tickets :
                                    <span class="count-no" id="total-ticket-type-count"></span>
                                </h2>
                            </div>
                            <div class="col-md-1 d-flex align-items-center justify-content-center">
                                <div class="toggle-container" onclick="toggleTicketTypeButton(this)" id="t_type_checkBox"
                                title="Exclude tickets log by system">
                                    <div class="toggle-button">
                                        <div class="icon">
                                        <i class="fas fa-chart-bar"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div id="ticketType" style="display: block;box-sizing: border-box;height: 430px;width: 100% !important;!i;!;"></div>                            
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- // Ticket Type Bar chart -->

            <!-- Ticket Status Graph -->
            <div class="card">
                <!-- Filter Content -->
                <div class="row">
                    <div class="col-md-6 col-lg-10">
                        <div class="card-body">
                            <div class="row ">
                                <div class="col-md-2">
                                    <input type="text" class="form-control datepicker" id="t_status_from_date"
                                        name="t_status_from_date" placeholder="From Date (DD-MM-YY)"
                                        title="From Date (DD-MM-YY)">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control datepicker" id="t_status_to_date"
                                        name="t_status_to_date" placeholder="To Date (DD-MM-YY)"
                                        title="To Date (DD-MM-YY)">
                                </div>
                                <div class="col-md-4">
                                    <select name="tStatusTeamFilter" id="tStatusTeamFilter" multiple>
                                        <option value="">Team</option>
                                        @foreach ($teams as $item)
                                        <option value="{{ $item['teamName'] }}">{{ $item['teamName'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-2">
                                    <button type="button" class="btn btn-primary btn-sm cus-btn submit-btn"
                                        id="tStatusFilterBtn">Submit</button>
                                    <button type="button" class="btn btn-primary btn-sm cus-btn reset-btn"
                                        id="tStatusResetBtn">Reset</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="wrapper">
                    <div class="chart p-2">
                        <div class="row">
                            <div class="col-md-3 graph-header-sec">
                                <h2 class="graph-header">Ticket Status</h2>
                            </div>
                             <div class="col-md-7">
                                <h2 class="text-right mb-0 mt-2 total-tickets-para" id="total-tickets-no"> Total Tickets :
                                    <span class="count-no" id="total-ticket-status-count"></span>
                                </h2>
                            </div>
                            <div class="col-md-1 d-flex align-items-center justify-content-center">
                               <div class="toggle-container" onclick="toggletTicketStatusButton(this)" id="t_status_checkBox"
                               title="Exclude tickets log by system">
                                    <div class="toggle-button">
                                        <div class="icon">
                                        <i class="fas fa-chart-bar"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                            <div id="ticketStatus" style="display: block;box-sizing: border-box;height: 430px;width: 100% !important;!i;!;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- // Ticket Status Graph -->

            <!-- Feedback Report Graph -->
            <div class="card">
                <!-- Filter Content -->
                <div class="row">
                    <div class="col-md-6 col-lg-10">
                        <div class="card-body">
                            <div class="row ">
                                <div class="col-md-2">
                                    <input type="text" class="form-control datepicker" id="feedback_from_date"
                                        name="feedback_from_date" placeholder="From Date (DD-MM-YY)"
                                        title="From Date (DD-MM-YY)">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control datepicker" id="feedback_to_date"
                                        name="feedback_to_date" placeholder="To Date (DD-MM-YY)"
                                        title="To Date (DD-MM-YY)">
                                </div>
                                <div class="col-md-4">
                                    <select name="feedbackTeamFilter" id="feedbackTeamFilter" multiple>
                                        <option value="">Team</option>
                                        @foreach ($teams as $item)
                                        <option value="{{ $item['teamName'] }}">{{ $item['teamName'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-2">
                                    <button type="button" class="btn btn-primary btn-sm cus-btn submit-btn"
                                        id="feedbackFilterBtn">Submit</button>
                                    <button type="button" class="btn btn-primary btn-sm cus-btn reset-btn"
                                        id="feedbackResetBtn">Reset</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="wrapper">
                    <div class="chart p-2">
                        <div class="row">
                            <div class="col-md-3 graph-header-sec">
                                <h2 class="graph-header">Feedback Report</h2>
                            </div>
                            <div class="col-md-3">
                                <h2 class="text-right mb-0 mt-2 total-tickets-para" id="total-tickets-no"> Total Feedbacks :
                                    <span class="count-no" id="total-feedback-ticket-count"></span>
                                </h2>
                            </div>
                            <div class="col-md-4">
                                <h2 class="text-center mb-0 mt-2 total-tickets-para" id="total-tickets-no">
                                    <span id="total-feedback-star"></span>
                                </h2>
                            </div>
                            <div class="col-md-1 d-flex align-items-center justify-content-center">
                                <div class="toggle-container" onclick="toggletFeedbackReportButton(this)" id="feedback_checkBox"
                                title="Exclude tickets log by system">
                                    <div class="toggle-button">
                                        <div class="icon">
                                        <i class="fas fa-chart-bar"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                            <div id="feedbackReport" style="display: block;box-sizing: border-box;height: 430px;width: 100% !important;!i;!;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- // Feedback Report Graph -->

            <!-- Department Ticket Graph -->
            <div class="card">
                <!-- Filter Content -->
                <div class="row">
                    <div class="col-md-6 col-lg-10">
                        <div class="card-body">
                            <div class="row ">
                                <div class="col-md-2">
                                    <input type="text" class="form-control datepicker" id="dept_from_date"
                                        name="dept_from_date" placeholder="From Date (DD-MM-YY)"
                                        title="From Date (DD-MM-YY)">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control datepicker" id="dept_to_date"
                                        name="dept_to_date" placeholder="To Date (DD-MM-YY)" title="To Date (DD-MM-YY)">
                                </div>
                                <div class="col-md-4">
                                    <select name="deptTeamFilter" id="deptTeamFilter" multiple>
                                        <option value="">Team</option>
                                        @foreach ($teams as $item)
                                        <option value="{{ $item['teamName'] }}">{{ $item['teamName'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-2">
                                    <button type="button" class="btn btn-primary btn-sm cus-btn submit-btn"
                                        id="deptFilterBtn">Submit</button>
                                    <button type="button" class="btn btn-primary btn-sm cus-btn reset-btn"
                                        id="deptResetBtn">Reset</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="wrapper">
                    <div class="chart p-2">
                        <div class="row">
                            <div class="col-md-3 graph-header-sec">
                                <h2 class="graph-header">Department Ticket</h2>
                            </div>
                             <div class="col-md-7">
                                <h2 class="text-right mb-0 mt-2 total-tickets-para" id="total-tickets-no"> Total Tickets :
                                    <span class="count-no" id="total-dept-ticket-count"></span>
                                </h2>
                            </div>
                            <div class="col-md-1 d-flex align-items-center justify-content-center">
                                <div class="toggle-container" onclick="toggleDeptTicketButton(this)" id="dept_checkBox"
                                title="Exclude tickets log by system">
                                    <div class="toggle-button">
                                        <div class="icon">
                                        <i class="fas fa-chart-bar"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                            <div id="departmentTicket" style="display: block;box-sizing: border-box;height: 430px;width: 100% !important;!i;!;"></div>  
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- // Department Ticket Graph -->

            <!-- Engineer Points Graph -->
            <div class="card">
                <!-- Filter Content -->
                <div class="row">
                    <div class="col-md-6 col-lg-10">
                        <div class="card-body">
                            <div class="row ">
                                <div class="col-md-2">
                                    <input type="text" class="form-control datepicker" id="ep_from_date"
                                        name="ep_from_date" placeholder="From Date (DD-MM-YY)"
                                        title="From Date (DD-MM-YY)">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control datepicker" id="ep_to_date" name="ep_to_date"
                                        placeholder="To Date (DD-MM-YY)" title="To Date (DD-MM-YY)">
                                </div>
                                <div class="col-md-4">
                                    <select name="epTeamFilter" id="epTeamFilter" multiple>
                                        <option value="">Team</option>
                                        @foreach ($teams as $item)
                                        <option value="{{ $item['teamName'] }}">{{ $item['teamName'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-2">
                                    <button type="button" class="btn btn-primary btn-sm cus-btn submit-btn"
                                        id="epFilterBtn">Submit</button>
                                    <button type="button" class="btn btn-primary btn-sm cus-btn reset-btn"
                                        id="epResetBtn">Reset</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="wrapper">
                    <div class="chart p-2">
                        <div class="row">
                            <div class="col-md-3 graph-header-sec">
                                <h2 class="graph-header">Engineer Points</h2>
                            </div>
                             <div class="col-md-7">
                                <h2 class="text-right mb-0 mt-2 total-tickets-para" id="total-tickets-no"> Total Ticket Points :
                                    <span class="count-no" id="total-ticket-points-count"></span>
                                </h2>
                            </div>
                            <div class="col-md-1 d-flex align-items-center justify-content-center">
                               <div class="toggle-container" onclick="toggletEngineerPointButton(this)" id="ep_checkBox"
                               title="Exclude tickets log by system">
                                    <div class="toggle-button">
                                        <div class="icon">
                                        <i class="fas fa-chart-bar"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                            <div id="ticketPoints" style="display: block;box-sizing: border-box;height: 430px;width: 100% !important;!i;!;"></div>  
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- // Engineer Points Graph -->

            <!-- SLA Ticket Graph -->
            <div class="card">
                <!-- Filter Content -->
                <div class="row">
                    <div class="col-md-6 col-lg-10">
                        <div class="card-body">
                            <div class="row ">
                                <div class="col-md-2">
                                    <input type="text" class="form-control datepicker" id="sla_from_date"
                                        name="sla_from_date" placeholder="From Date (DD-MM-YY)"
                                        title="From Date (DD-MM-YY)">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control datepicker" id="sla_to_date"
                                        name="sla_to_date" placeholder="To Date (DD-MM-YY)" title="To Date (DD-MM-YY)">
                                </div>
                                <div class="col-md-4">
                                    <select name="slaTeamFilter" id="slaTeamFilter" multiple>
                                        <option value="">Team</option>
                                        @foreach ($teams as $item)
                                        <option value="{{ $item['teamName'] }}">{{ $item['teamName'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-2">
                                    <button type="button" class="btn btn-primary btn-sm cus-btn submit-btn"
                                        id="slaFilterBtn">Submit</button>
                                    <button type="button" class="btn btn-primary btn-sm cus-btn reset-btn"
                                        id="slaResetBtn">Reset</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="wrapper">
                    <div class="chart p-2">
                        <div class="row">
                            <div class="col-md-3 graph-header-sec">
                                <h2 class="graph-header">SLA Ticket</h2>
                            </div>
                            <div class="col-md-4">
                                <h2 class="text-right mb-0 mt-2 total-tickets-para" id="total-tickets-no"> Within SLA Tickets :
                                    <span class="count-no" id="total-withinSla-ticket-count"></span>
                                </h2>
                            </div>
                            <div class="col-md-2">
                                <h2 class="mb-0 mt-2 total-tickets-para" id="total-tickets-no"> SLA Breach Tickets :
                                    <span class="count-no" id="total-slaBreach-ticket-count" 
                                    style="color: #000;background: #ee9292;"></span>
                                </h2>
                            </div>
                            <div class="col-md-1 d-flex align-items-center justify-content-center">
                                <div class="toggle-container" onclick="toggletSLATicketButton(this)" id="sla_checkBox"
                                title="Exclude tickets log by system">
                                    <div class="toggle-button">
                                        <div class="icon">
                                        <i class="fas fa-chart-bar"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                            <div id="slaTicket" style="display: block;box-sizing: border-box;height: 430px;width: 100% !important;!i;!;"></div> 
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- // SLA Ticket Graph -->

            <!-- Ticket Duration Graph -->
            <div class="card">
                <!-- Filter Content -->
                <div class="row">
                    <div class="col-md-6 col-lg-10">
                        <div class="card-body">
                            <div class="row ">
                                <div class="col-md-2">
                                    <input type="text" class="form-control datepicker" id="td_from_date"
                                        name="td_from_date" placeholder="From Date (DD-MM-YY)"
                                        title="From Date (DD-MM-YY)">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control datepicker" id="td_to_date" name="td_to_date"
                                        placeholder="To Date (DD-MM-YY)" title="To Date (DD-MM-YY)">
                                </div>
                                <div class="col-md-4">
                                    <select name="tdTeamFilter" id="tdTeamFilter" multiple>
                                        <option value="">Team</option>
                                        @foreach ($teams as $item)
                                        <option value="{{ $item['teamName'] }}">{{ $item['teamName'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-2">
                                    <button type="button" class="btn btn-primary btn-sm cus-btn submit-btn"
                                        id="tdFilterBtn">Submit</button>
                                    <button type="button" class="btn btn-primary btn-sm cus-btn reset-btn"
                                        id="tdResetBtn">Reset</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="wrapper">
                    <div class="chart p-2">
                        <div class="row">
                            <div class="col-md-3 graph-header-sec">
                                <h2 class="graph-header">Ticket Duration</h2>
                            </div>
                            <div class="col-md-7 d-flex align-items-center justify-content-end">
                               <div class="toggle-container" onclick="toggletTicketDurationButton(this)" id="td_checkBox"
                               title="Exclude tickets log by system">
                                    <div class="toggle-button">
                                        <div class="icon">
                                        <i class="fas fa-chart-bar"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                            <div id="ticketDuration" style="display: block;box-sizing: border-box;height: 430px;width: 100% !important;!i;!;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- // Ticket Duration Graph -->

            <!-- Log By Ticket Bar chart -->
            <div class="card">
                <!-- Filter Content -->
                <div class="row">
                    <div class="col-md-6 col-lg-10">
                        <div class="card-body">
                            <div class="row ">
                                <div class="col-md-2">
                                    <input type="text" class="form-control datepicker" id="logBy_from_date"
                                        name="logBy_from_date" placeholder="From Date (DD-MM-YY)"
                                        title="From Date (DD-MM-YY)">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control datepicker" id="logBy_to_date" name="logBy_to_date"
                                        placeholder="To Date (DD-MM-YY)" title="To Date (DD-MM-YY)">
                                </div>
                                <div class="col-md-4">
                                    <select name="logByTeamFilter" id="logByTeamFilter" multiple>
                                        <option value="">Team</option>
                                        @foreach ($teams as $item)
                                        <option value="{{ $item['teamName'] }}">{{ $item['teamName'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-2">
                                    <button type="button" class="btn btn-primary btn-sm cus-btn submit-btn"
                                        id="logByFilterBtn">Submit</button>
                                    <button type="button" class="btn btn-primary btn-sm cus-btn reset-btn"
                                        id="logByResetBtn">Reset</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="wrapper">
                    <div class="chart p-2">
                        <div class="row">
                            <div class="col-md-3 graph-header-sec">
                                <h2 class="graph-header">Log Wise Ticket</h2>
                            </div>
                             <div class="col-md-7">
                                <h2 class="text-right mb-0 mt-2 total-tickets-para" id="total-tickets-no"> Total Tickets :
                                    <span class="count-no" id="total-log-ticket-count"></span>
                                </h2>
                            </div>
                            <div class="col-md-1 d-flex align-items-center justify-content-center">
                               <div class="toggle-container" onclick="toggletLogWiseTicketButton(this)" id="logwise_checkBox"
                               title="Exclude tickets log by system">
                                    <div class="toggle-button">
                                        <div class="icon">
                                        <i class="fas fa-chart-bar"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                            <div id="logByTicket" style="display: block;box-sizing: border-box;height: 430px;width: 100% !important;!i;!;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- // Log By Ticket Bar chart -->

            <!-- Breached Ticket Points Graph -->
            <div class="card">
                <!-- Filter Content -->
                <div class="row">
                    <div class="col-md-6 col-lg-10">
                        <div class="card-body">
                            <div class="row ">
                                <div class="col-md-2">
                                    <input type="text" class="form-control datepicker" id="breached_from_date"
                                        name="breached_from_date" placeholder="From Date (DD-MM-YY)"
                                        title="From Date (DD-MM-YY)">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control datepicker" id="breached_to_date" name="breached_to_date"
                                        placeholder="To Date (DD-MM-YY)" title="To Date (DD-MM-YY)">
                                </div>
                                <div class="col-md-4">
                                    <select name="breachedTeamFilter" id="breachedTeamFilter" multiple>
                                        <option value="">Team</option>
                                        @foreach ($teams as $item)
                                        <option value="{{ $item['teamName'] }}">{{ $item['teamName'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-2">
                                    <button type="button" class="btn btn-primary btn-sm cus-btn submit-btn"
                                        id="breachedFilterBtn">Submit</button>
                                    <button type="button" class="btn btn-primary btn-sm cus-btn reset-btn"
                                        id="breachedResetBtn">Reset</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="wrapper">
                    <div class="chart p-2">
                        <div class="row">
                            <div class="col-md-3 graph-header-sec">
                                <h2 class="graph-header">Breached Ticket Points</h2>
                            </div>
                             <div class="col-md-7">
                                <h2 class="text-right mb-0 mt-2 total-tickets-para" id="total-tickets-no"> Total Points :
                                    <span class="count-no" id="total-breached-ticket-points-count"></span>
                                </h2>
                            </div>
                            <div class="col-md-1 d-flex align-items-center justify-content-center">
                               <div class="toggle-container" onclick="toggletBreachedPointButton(this)" id="breached_checkBox"
                               title="Exclude tickets log by system">
                                    <div class="toggle-button">
                                        <div class="icon">
                                        <i class="fas fa-chart-bar"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div id="breachedTicketPoints" style="display: block;box-sizing: border-box;height: 430px;width: 100% !important;!i;!;"></div> 
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- // Breached Ticket Points Graph -->

        </div>
    </div>
</section>
@endsection

@section('js-content')
<!-- Flatpickr JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.0.0/chart.min.js"
    integrity="sha512-lkEx3HSoujDP3+V+i46oZpNx3eK67QPiWiCwoeQgR1I+4kutWAuOSs3BxEUZt4U/mUfyY5uDHlypuQ1HHKVykA=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script> -->
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>

<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/data.js"></script>
<script src="https://code.highcharts.com/modules/drilldown.js"></script>

<!-- DataTables Buttons JS -->
<!-- <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js"></script> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.13.3/js/standalone/selectize.min.js"></script>

<!-- Toggle Button Functions -->
<script>
    function toggleGlobalTicketButton(elem) {
        elem.classList.toggle("active");

        const globalIsActive = elem.classList.contains("active");

        const localButtons = [
            document.getElementById('et_checkBox'),
            document.getElementById('t_type_checkBox'),
            document.getElementById('t_status_checkBox'),
            document.getElementById('feedback_checkBox'),
            document.getElementById('dept_checkBox'),
            document.getElementById('ep_checkBox'),
            document.getElementById('sla_checkBox'),
            document.getElementById('td_checkBox'),
            document.getElementById('logwise_checkBox'),
            document.getElementById('breached_checkBox')
        ];

        if(globalIsActive) {
            // Disable local buttons
            localButtons.forEach(button => {                
                button.classList.add("active");
            });
        } else {
            // Enable local buttons
            localButtons.forEach(button => {
                button.classList.remove("active");
            });
        }
        if (globalIsActive) {
            toggletEngineerTicketButton(document.getElementById('et_checkBox'), true);
            toggleTicketTypeButton(document.getElementById('t_type_checkBox'), true);
            toggletTicketStatusButton(document.getElementById('t_status_checkBox'), true);
            toggletFeedbackReportButton(document.getElementById('feedback_checkBox'), true);
            toggleDeptTicketButton(document.getElementById('dept_checkBox'), true);
            toggletEngineerPointButton(document.getElementById('ep_checkBox'), true);
            toggletSLATicketButton(document.getElementById('sla_checkBox'), true);
            toggletTicketDurationButton(document.getElementById('td_checkBox'), true);
            toggletLogWiseTicketButton(document.getElementById('logwise_checkBox'), true);
            toggletBreachedPointButton(document.getElementById('breached_checkBox'), true);
        }  
        else{
            toggletEngineerTicketButton(document.getElementById('et_checkBox'), false);
            toggleTicketTypeButton(document.getElementById('t_type_checkBox'), false);
            toggletTicketStatusButton(document.getElementById('t_status_checkBox'), false);
            toggletFeedbackReportButton(document.getElementById('feedback_checkBox'), false);
            toggleDeptTicketButton(document.getElementById('dept_checkBox'), false);
            toggletEngineerPointButton(document.getElementById('ep_checkBox'), false);
            toggletSLATicketButton(document.getElementById('sla_checkBox'), false);
            toggletTicketDurationButton(document.getElementById('td_checkBox'), false);
            toggletLogWiseTicketButton(document.getElementById('logwise_checkBox'), false);
            toggletBreachedPointButton(document.getElementById('breached_checkBox'), false);
        }    
    }

    function toggletEngineerTicketButton(elem, fromGlobal = null) {       
        if (fromGlobal === true) {
            // From global ON
            elem.classList.add("active");
        } else if (fromGlobal === false) {
            // From global OFF
            elem.classList.remove("active");
        } else {
            // Local toggle (no fromGlobal param passed)
            elem.classList.toggle("active");
        }
        getEngineerTicketChartData();
    }

    function toggleTicketTypeButton(elem, fromGlobal = null) {
        if (fromGlobal === true) {
            // From global ON
            elem.classList.add("active");
        } else if (fromGlobal === false) {
            // From global OFF
            elem.classList.remove("active");
        } else {
            // Local toggle (no fromGlobal param passed)
            elem.classList.toggle("active");
        }
        getTicketTypeChartData();
    }

    function toggletTicketStatusButton(elem, fromGlobal = null) {
         if (fromGlobal === true) {
            // From global ON
            elem.classList.add("active");
        } else if (fromGlobal === false) {
            // From global OFF
            elem.classList.remove("active");
        } else {
            // Local toggle (no fromGlobal param passed)
            elem.classList.toggle("active");
        }
        getTicketStatusChartData();
    }

    function toggletFeedbackReportButton(elem, fromGlobal = null) {
        if (fromGlobal === true) {
            // From global ON
            elem.classList.add("active");
        } else if (fromGlobal === false) {
            // From global OFF
            elem.classList.remove("active");
        } else {
            // Local toggle (no fromGlobal param passed)
            elem.classList.toggle("active");
        }
        getFeedbackReportChartData();
    }

    function toggleDeptTicketButton(elem, fromGlobal = null) {
        if (fromGlobal === true) {
            // From global ON
            elem.classList.add("active");
        } else if (fromGlobal === false) {
            // From global OFF
            elem.classList.remove("active");
        } else {
            // Local toggle (no fromGlobal param passed)
            elem.classList.toggle("active");
        }
        getDeptTicketChartData();
    }

    function toggletEngineerPointButton(elem, fromGlobal = null) {
        if (fromGlobal === true) {
            // From global ON
            elem.classList.add("active");
        } else if (fromGlobal === false) {
            // From global OFF
            elem.classList.remove("active");
        } else {
            // Local toggle (no fromGlobal param passed)
            elem.classList.toggle("active");
        }
        getEngineerPointChartData();
    }

    function toggletSLATicketButton(elem, fromGlobal = null) {
        if (fromGlobal === true) {
            // From global ON
            elem.classList.add("active");
        } else if (fromGlobal === false) {
            // From global OFF
            elem.classList.remove("active");
        } else {
            // Local toggle (no fromGlobal param passed)
            elem.classList.toggle("active");
        }
        getSLATicketChartData();
    }

    function toggletTicketDurationButton(elem, fromGlobal = null) {
        if (fromGlobal === true) {
            // From global ON
            elem.classList.add("active");
        } else if (fromGlobal === false) {
            // From global OFF
            elem.classList.remove("active");
        } else {
            // Local toggle (no fromGlobal param passed)
            elem.classList.toggle("active");
        }
        getTicketDurationChartData();
    }

    function toggletLogWiseTicketButton(elem, fromGlobal = null) {
         if (fromGlobal === true) {
            // From global ON
            elem.classList.add("active");
        } else if (fromGlobal === false) {
            // From global OFF
            elem.classList.remove("active");
        } else {
            // Local toggle (no fromGlobal param passed)
            elem.classList.toggle("active");
        }
        getLogWiseTicketChartData();
    }

    function toggletBreachedPointButton(elem, fromGlobal = null) {
        if (fromGlobal === true) {
            // From global ON
            elem.classList.add("active");
        } else if (fromGlobal === false) {
            // From global OFF
            elem.classList.remove("active");
        } else {
            // Local toggle (no fromGlobal param passed)
            elem.classList.toggle("active");
        }
        getBreachedTicketPointsChartData();
    }

</script>

<!-- // Toggle Button Functions -->
<script>
flatpickr(".datepicker", {
    dateFormat: "d-M-Y",
});
// Get today's date
const today = new Date();

// Calculate the first date of the current month (01-MM-YYYY)
const firstDate = new Date(today.getFullYear(), today.getMonth(), 1);

// Calculate the last date of the current month (last date of MM-YYYY)
const lastDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
</script>

<script>
    $('#globalTeamFilter').selectize({
        plugins: ['remove_button'],
        delimiter: ',',
        persist: false,
        create: false,
        valueField: 'id',
        labelField: 'text',
        searchField: ['text'],
        placeholder: 'Choose Team',
    });

    $('#etTeamFilter').selectize({
        plugins: ['remove_button'],
        delimiter: ',',
        persist: false,
        create: false,
        valueField: 'id',
        labelField: 'text',
        searchField: ['text'],
        placeholder: 'Choose Team',
    });

    $('#tTypeTeamfilter').selectize({
        plugins: ['remove_button'],
        delimiter: ',',
        persist: false,
        create: false,
        valueField: 'id',
        labelField: 'text',
        searchField: ['text'],
        placeholder: 'Choose Team',
    });

    $('#tStatusTeamFilter').selectize({
        plugins: ['remove_button'],
        delimiter: ',',
        persist: false,
        create: false,
        valueField: 'id',
        labelField: 'text',
        searchField: ['text'],
        placeholder: 'Choose Team',
    });

    $('#feedbackTeamFilter').selectize({
        plugins: ['remove_button'],
        delimiter: ',',
        persist: false,
        create: false,
        valueField: 'id',
        labelField: 'text',
        searchField: ['text'],
        placeholder: 'Choose Team',
    });

    $('#deptTeamFilter').selectize({
        plugins: ['remove_button'],
        delimiter: ',',
        persist: false,
        create: false,
        valueField: 'id',
        labelField: 'text',
        searchField: ['text'],
        placeholder: 'Choose Team',
    });

    $('#epTeamFilter').selectize({
        plugins: ['remove_button'],
        delimiter: ',',
        persist: false,
        create: false,
        valueField: 'id',
        labelField: 'text',
        searchField: ['text'],
        placeholder: 'Choose Team',
    });

    $('#slaTeamFilter').selectize({
        plugins: ['remove_button'],
        delimiter: ',',
        persist: false,
        create: false,
        valueField: 'id',
        labelField: 'text',
        searchField: ['text'],
        placeholder: 'Choose Team',
    });

    $('#tdTeamFilter').selectize({
        plugins: ['remove_button'],
        delimiter: ',',
        persist: false,
        create: false,
        valueField: 'id',
        labelField: 'text',
        searchField: ['text'],
        placeholder: 'Choose Team',
    });

    $('#logByTeamFilter').selectize({
        plugins: ['remove_button'],
        delimiter: ',',
        persist: false,
        create: false,
        valueField: 'id',
        labelField: 'text',
        searchField: ['text'],
        placeholder: 'Choose Team',
    });

    $('#breachedTeamFilter').selectize({
        plugins: ['remove_button'],
        delimiter: ',',
        persist: false,
        create: false,
        valueField: 'id',
        labelField: 'text',
        searchField: ['text'],
        placeholder: 'Choose Team',
    });
</script>
<!-- Chart Loader -->
<script>
    function showChartLoader() {
        $('#chart-loader').show();

        // Hide loader automatically after 30 seconds max (failsafe)
        // if (window.chartLoaderTimeout) clearTimeout(window.chartLoaderTimeout);
        //     window.chartLoaderTimeout = setTimeout(() => $('#chart-loader').hide(), 30000);
    }

    function hideChartLoader() {
        $('#chart-loader').hide();
        // if (window.chartLoaderTimeout) clearTimeout(window.chartLoaderTimeout);
    }
</script>
<!-- // Chart Loader -->
<!-- Global Date and teams -->
<script>
    var global_from_date = $("#global_from_date").flatpickr({
        dateFormat: 'd-M-Y', // Correct Flatpickr format
        defaultDate: firstDate, // Pre-set date
        onChange: function(selectedDates, dateStr, instance) {
            if (dateStr) {
                global_to_date.set("minDate", dateStr); // Set minimum date for global_to_date
            } else {
                global_to_date.set("minDate", null); // Clear minimum date for global_to_date
            }
        }
    });

    var global_to_date = $("#global_to_date").flatpickr({
        dateFormat: 'd-M-Y', // Correct Flatpickr format
        defaultDate: lastDate, // Pre-set date
        onChange: function(selectedDates, dateStr, instance) {
            if (dateStr) {
                global_from_date.set("maxDate", dateStr); // Set maximum date for global_from_date
            } else {
                global_from_date.set("maxDate", null); // Clear maximum date for global_from_date
            }
        }
    });

    $('#globalFilterBtn').click(function() {

        // Get selected Dates from the Flatpickr instances
        const fromDate = global_from_date.selectedDates[0];
        const toDate = global_to_date.selectedDates[0];

        // Format them using Flatpickr’s formatDate method
        const formattedFromDate = fromDate ? global_from_date.formatDate(fromDate, "d-M-Y") : '';
        const formattedToDate = toDate ? global_to_date.formatDate(toDate, "d-M-Y") : '';
        const selectedTeams = $('#globalTeamFilter')[0].selectize.getValue();

        // Set the values to local inputs
        $('#et_from_date').val(formattedFromDate);
        $('#et_to_date').val(formattedToDate);        
        $('#etTeamFilter')[0].selectize.setValue(selectedTeams);

        $('#t_type_from_date').val(formattedFromDate);
        $('#t_type_to_date').val(formattedToDate);        
        $('#tTypeTeamfilter')[0].selectize.setValue(selectedTeams);

        $('#t_status_from_date').val(formattedFromDate);
        $('#t_status_to_date').val(formattedToDate);        
        $('#tStatusTeamFilter')[0].selectize.setValue(selectedTeams);

        $('#feedback_from_date').val(formattedFromDate);
        $('#feedback_to_date').val(formattedToDate);        
        $('#feedbackTeamFilter')[0].selectize.setValue(selectedTeams);

         $('#dept_from_date').val(formattedFromDate);
        $('#dept_to_date').val(formattedToDate);        
        $('#deptTeamFilter')[0].selectize.setValue(selectedTeams);

        $('#ep_from_date').val(formattedFromDate);
        $('#ep_to_date').val(formattedToDate);        
        $('#epTeamFilter')[0].selectize.setValue(selectedTeams);

        $('#sla_from_date').val(formattedFromDate);
        $('#sla_to_date').val(formattedToDate);        
        $('#slaTeamFilter')[0].selectize.setValue(selectedTeams);

        $('#td_from_date').val(formattedFromDate);
        $('#td_to_date').val(formattedToDate);        
        $('#tdTeamFilter')[0].selectize.setValue(selectedTeams);

        $('#logBy_from_date').val(formattedFromDate);
        $('#logBy_to_date').val(formattedToDate);        
        $('#logByTeamFilter')[0].selectize.setValue(selectedTeams);

        $('#breached_from_date').val(formattedFromDate);
        $('#breached_to_date').val(formattedToDate);
        $('#breachedTeamFilter')[0].selectize.setValue(selectedTeams);

        getEngineerTicketChartData();
       
        getTicketTypeChartData();
       
        getTicketStatusChartData();
       
        getFeedbackReportChartData();
       
        getDeptTicketChartData();
        
        getEngineerPointChartData();
        
        getSLATicketChartData();
       
        getTicketDurationChartData();
        
        getLogWiseTicketChartData();

        getBreachedTicketPointsChartData();

    });
</script>
<!-- // Global Date and teams -->

<!-- Engineer's Ticket Chart JS -->
<script>
    var et_from_date = $("#et_from_date").flatpickr({
        dateFormat: 'd-M-Y', // Correct Flatpickr format
        defaultDate: firstDate, // Pre-set date
        onChange: function(selectedDates, dateStr, instance) {
            if (dateStr) {
                et_to_date.set("minDate", dateStr); // Set minimum date for et_to_date
            } else {
                et_to_date.set("minDate", null); // Clear minimum date for et_to_date
            }
        }
    });

    var et_to_date = $("#et_to_date").flatpickr({
        dateFormat: 'd-M-Y', // Correct Flatpickr format
        defaultDate: lastDate, // Pre-set date
        onChange: function(selectedDates, dateStr, instance) {
            if (dateStr) {
                et_from_date.set("maxDate", dateStr); // Set maximum date for et_from_date
            } else {
                et_from_date.set("maxDate", null); // Clear maximum date for et_from_date
            }
        }
    });

    $('#etFilterBtn').click(function() {
        getEngineerTicketChartData();
    });

    $('#etResetBtn').click(function() {

        // Reset Flatpickr inputs to default dates
        et_from_date.setDate(firstDate, true); // true to trigger the input change event
        et_to_date.setDate(lastDate, true); // true to trigger the input change event

        // Clear other inputs or dropdowns
        $("#etTeamFilter").val('');

        // Fetch chart data with the reset values
        getEngineerTicketChartData();
    });

    getEngineerTicketChartData();

    let engineerTicketChart;

    function getEngineerTicketChartData() {
        var etFromDate = $('#et_from_date').val();
        var etToDate = $('#et_to_date').val();
        var etTeamId = $('#etTeamFilter').val();

        // Get the checkbox state
        var isChecked = document.getElementById('et_checkBox').classList.contains('active') ? 'Y' : 'N';

        // Show loader
        showChartLoader();

        $.ajax({
            url: "{{ route('get.engineer.ticket.chart.data') }}",
            type: "GET", // Use POST if needed,
            dataType: 'JSON',
            data: {
                etFromDate: etFromDate,
                etToDate: etToDate,
                etTeamId: etTeamId,
                isChecked: isChecked,
            },
            success: function(data) {
                const chartData = [];
                const drilldownData = [];                

                // Convert PHP variables to JavaScript
                const technicians = (data.technicians ?? []);
                const totalTickets = data.total_tickets;

                document.getElementById('engineer-total-ticket-count').textContent = + totalTickets;

                technicians.forEach(tech => {
                    chartData.push({
                        name: tech.USER_NAME,
                        y: tech.ticket_count,
                        drilldown: tech.USER_NAME
                    });

                    const ticketTypeDrillData = [];

                    (tech.ticket_types || []).forEach(type => {
                        ticketTypeDrillData.push([type.name, type.count]);

                        // Push 2nd level drilldown: ticket category under type
                        drilldownData.push({
                        id: `${tech.USER_NAME}-${type.name}`,
                        name: `Categories of ${type.name} - ${tech.USER_NAME}`,
                        data: type.categories.map(cat => [cat.name, cat.count]),
                        pointWidth: 40
                        });
                    });
                
                    drilldownData.push({
                        id: tech.USER_NAME,
                        name: `Ticket Types - ${tech.USER_NAME}`,
                        data: ticketTypeDrillData.map(([typeName, count]) => ({
                            name: typeName,
                            y: count,
                            drilldown: `${tech.USER_NAME}-${typeName}`
                        })),
                        pointWidth: 40
                    });
                });

                if (engineerTicketChart) {
                    engineerTicketChart.destroy();
                }

                engineerTicketChart = Highcharts.chart('engineerTicket', {
                    chart: {
                        type: 'column',
                    },
                    title: { text: null },
                    xAxis: {
                        type: 'category',                    
                        labels: { style: { color: 'black' } ,
                            rotation: 305
                        }
                    },
                    yAxis: {
                        title: { text: null },                    
                        labels: { style: { color: 'black' } }
                    },
                    legend: { enabled: false },
                    tooltip: {
                        pointFormat: 'Tickets: <b>{point.y}</b>'
                    },
                    plotOptions: {
                        series: {
                            dataLabels: {
                                enabled: true,
                                style: { fontWeight: 'bold', color: 'black' }
                            }
                        }
                    },               
                    series: [{
                        name: 'Engineer',
                        colorByPoint: true,
                        data: chartData,
                        pointWidth: 40, 
                    }],
                    drilldown: {
                        series: drilldownData
                    }
                });

                // Hide loader after success
                hideChartLoader();
                
            },
            error: function(error) {
                hideChartLoader();
                console.error('Error fetching graph data:', error);
            },
        });
    }
</script>
<!-- // Engineer's Ticket Chart JS -->

<!-- Ticket Type Chart Js -->
<script>
    var t_type_from_date = $("#t_type_from_date").flatpickr({
        dateFormat: 'd-M-Y', // Correct Flatpickr format
        defaultDate: firstDate, // Pre-set date
        onChange: function(selectedDates, dateStr, instance) {
            if (dateStr) {
                t_type_to_date.set("minDate", dateStr); // Set minimum date for t_type_to_date
            } else {
                t_type_to_date.set("minDate", null); // Clear minimum date for t_type_to_date
            }
        }
    });

    var t_type_to_date = $("#t_type_to_date").flatpickr({
        dateFormat: 'd-M-Y', // Correct Flatpickr format
        defaultDate: lastDate, // Pre-set date
        onChange: function(selectedDates, dateStr, instance) {
            if (dateStr) {
                t_type_from_date.set("maxDate", dateStr); // Set maximum date for t_type_from_date
            } else {
                t_type_from_date.set("maxDate", null); // Clear maximum date for t_type_from_date
            }
        }
    });

    $('#ttFilterBtn').click(function() {
        getTicketTypeChartData();
    });

    $('#ttResetBtn').click(function() {

        // Reset Flatpickr inputs to default dates
        t_type_from_date.setDate(firstDate, true); // true to trigger the input change event
        t_type_to_date.setDate(lastDate, true); // true to trigger the input change event

        // Clear other inputs or dropdowns
        $("#tTypeTeamfilter").val('');

        // Fetch chart data with the reset values
        getTicketTypeChartData();
    });

    getTicketTypeChartData();

    let ticketTypeChart;

    function getTicketTypeChartData() {
        var fromDate = $('#t_type_from_date').val();
        var toDate = $('#t_type_to_date').val();
        var teamId = $('#tTypeTeamfilter').val();

        // var isChecked = $('#t_type_checkBox').is(':checked') ? 'Y' : 'N';
        var isChecked = document.getElementById('t_type_checkBox').classList.contains('active') ? 'Y' : 'N';

        // Show loader
        showChartLoader();

        $.ajax({
            url: "{{ route('get.ticket.type.chart.data') }}",
            type: "GET", // Use POST if needed,
            dataType: 'JSON',
            data: {
                fromDate: fromDate,
                toDate: toDate,
                teamId: teamId,
                isChecked: isChecked
            },
            success: function(data) {
                // Convert PHP variables to JavaScript
                const hierarchyData = (data.ticketTypesHierarchy ?? {});
                const totalTicketsTypes = data.totalTicketsTypes ?? 0;

                document.getElementById('total-ticket-type-count').textContent = + totalTicketsTypes;

                const chartData = [];
                const drilldownData = [];

                Object.entries(hierarchyData).forEach(([type, typeData]) => {
                    chartData.push({
                        name: type,
                        y: typeData.count,
                        drilldown: type
                    });

                    // Second level: Categories
                    const categoryDrillData = [];
                    Object.entries(typeData.categories).forEach(([category, catData]) => {
                        categoryDrillData.push({
                            name: category,
                            y: catData.count,
                            drilldown: `${type}||${category}`
                        });

                        // Third level: Sub-categories
                        const subCategoryDrillData = Object.entries(catData.sub_categories).map(
                            ([sub, count]) => [sub, count]
                        );

                        drilldownData.push({
                            id: `${type}||${category}`,
                            name: `Sub-Categories - ${category}`,
                            data: subCategoryDrillData,
                            pointWidth: 40
                        });
                    });

                    drilldownData.push({
                        id: type,
                        name: `Categories - ${type}`,
                        data: categoryDrillData,
                        pointWidth: 40
                    });
                });

                if (ticketTypeChart) {
                    ticketTypeChart.destroy();
                }

                // Ticket Type
                ticketTypeChart = Highcharts.chart('ticketType', {
                    chart: {
                        type: 'column',
                    },
                    accessibility: {
                        enabled: false
                    },
                    title: {
                        text: null // You can add a title here if needed
                    },
                    xAxis: {
                        type: 'category',
                        labels: {
                            rotation: -55,
                            style: { color: 'black' }
                        }
                    },
                    yAxis: {
                        title: { text: null },                    
                        min: 0,
                        allowDecimals: false,
                        labels: {
                            style: { color: 'black' }
                        }
                    },
                    legend: { enabled: false },
                    tooltip: {
                        pointFormat: 'Tickets: <b>{point.y}</b>'
                    },
                    plotOptions: {
                        series: {
                            dataLabels: {
                                enabled: true,
                                style: { 
                                    fontWeight: 'bold', 
                                    color: 'black' 
                                }
                            }
                        }
                    },               
                    series: [{
                        name: 'Ticket Types',
                        colorByPoint: true,
                        data: chartData,
                        pointWidth: 40, 
                    }],
                    drilldown: {
                        series: drilldownData
                    }
                });
                hideChartLoader();
            
            },
            error: function(error) {
                hideChartLoader();
                console.error('Error fetching graph data:', error);
                // alert('Failed to fetch data. Please try again.');
            },
        });
    }
</script>
<!-- // Ticket Type Chart Js -->

<!-- SLA Chart JS -->
<script>
    var sla_from_date = $("#sla_from_date").flatpickr({
        dateFormat: 'd-M-Y', // Correct Flatpickr format
        defaultDate: firstDate, // Pre-set date
        onChange: function(selectedDates, dateStr, instance) {
            if (dateStr) {
                sla_to_date.set("minDate", dateStr); // Set minimum date for sla_to_date
            } else {
                sla_to_date.set("minDate", null); // Clear minimum date for sla_to_date
            }
        }
    });

    var sla_to_date = $("#sla_to_date").flatpickr({
        dateFormat: 'd-M-Y', // Correct Flatpickr format
        defaultDate: lastDate, // Pre-set date
        onChange: function(selectedDates, dateStr, instance) {
            if (dateStr) {
                sla_from_date.set("maxDate", dateStr); // Set maximum date for sla_from_date
            } else {
                sla_from_date.set("maxDate", null); // Clear maximum date for sla_from_date
            }
        }
    });

    $('#slaFilterBtn').click(function() {
        getSLATicketChartData();
    });

    $('#slaResetBtn').click(function() {

        // Reset Flatpickr inputs to default dates
        sla_from_date.setDate(firstDate, true); // true to trigger the input change event
        sla_to_date.setDate(lastDate, true); // true to trigger the input change event

        // Clear other inputs or dropdowns
        $("#slaTeamFilter").val('');

        // Fetch chart data with the reset values
        getSLATicketChartData();
    });

    getSLATicketChartData();

    let slaTicketChart;

    function getSLATicketChartData() {
        var fromDate = $('#sla_from_date').val();
        var toDate = $('#sla_to_date').val();
        var teamId = $('#slaTeamFilter').val();

        var isChecked = document.getElementById('sla_checkBox').classList.contains('active') ? 'Y' : 'N';

        // Show loader
        showChartLoader();

        $.ajax({
            url: "{{ route('get.sla.ticket.chart.data') }}",
            type: "GET", // Use POST if needed,
            dataType: 'JSON',
            data: {
                fromDate: fromDate,
                toDate: toDate,
                teamId: teamId,
                isChecked: isChecked
            },
            success: function(data) {
              
                const totalWithinSla = data.totalWithinSla || 0;
                const totalSlaBreach = data.totalSlaBreach || 0;

                document.getElementById('total-withinSla-ticket-count').textContent = + totalWithinSla ;
                document.getElementById('total-slaBreach-ticket-count').textContent = + totalSlaBreach ;
            
                const withinSlaData = [];
                const slaBreachData = [];
                const drilldownData = [];

                // Top-level for Within SLA
                for (const [engineer, types] of Object.entries(data.engineerCategoryWiseWithinSla)) {
                    let totalCount = 0;

                    for (const typeData of Object.values(types)) {
                        totalCount += typeData.count || 0;
                    }
                
                    withinSlaData.push({
                        name: engineer,
                        y: totalCount,
                        drilldown: `${engineer}-within`
                    });            

                    // 2nd level drilldown: ticket types
                    const typeLevelData = [];
                    for (const [type, typeData] of Object.entries(types)) {
                        typeLevelData.push({
                            name: type,
                            y: typeData.count,
                            drilldown: `${engineer}-within-${type}`
                        });

                        // 3rd level drilldown: categories
                        const categoryData = [];
                        for (const [category, count] of Object.entries(typeData.categories || {})) {
                            categoryData.push([category, count]);
                        }

                        drilldownData.push({
                            id: `${engineer}-within-${type}`,
                            name: `${engineer} - ${type}`,
                            data: categoryData
                        });
                    }

                    drilldownData.push({
                        id: `${engineer}-within`,
                        name: `${engineer} - Within SLA`,
                        data: typeLevelData
                    });
                }

                // Top-level for SLA Breach
                for (const [engineer, types] of Object.entries(data.engineerCategoryWiseSlaBreach)) {
                    let totalCount = 0;

                    for (const typeData of Object.values(types)) {
                        totalCount += typeData.count || 0;
                    }

                    slaBreachData.push({
                        name: engineer,
                        y: totalCount,
                        drilldown: `${engineer}-breach`
                    });

                    // 2nd level drilldown: ticket types
                    const typeLevelData = [];
                    for (const [type, typeData] of Object.entries(types)) {
                        typeLevelData.push({
                            name: type,
                            y: typeData.count,
                            drilldown: `${engineer}-breach-${type}`
                        });

                        // 3rd level drilldown: categories
                        const categoryData = [];
                        for (const [category, count] of Object.entries(typeData.categories || {})) {
                            categoryData.push([category, count]);
                        }

                        drilldownData.push({
                            id: `${engineer}-breach-${type}`,
                            name: `${engineer} - ${type}`,
                            data: categoryData
                        });
                    }

                    drilldownData.push({
                        id: `${engineer}-breach`,
                        name: `${engineer} - SLA Breach`,
                        data: typeLevelData
                    });
                }

                if (slaTicketChart) {
                    slaTicketChart.destroy();
                }

                // SLA Ticket
                slaTicketChart = Highcharts.chart('slaTicket', {
                    chart: {
                        type: 'column',
                    },
                    accessibility: {
                        enabled: false
                    },
                    title: {
                        text: null // You can add a title here if needed
                    },
                    xAxis: {
                        type: 'category',                
                        labels: { 
                            style: { color: 'black' } ,
                            rotation: -55
                        }
                    },
                    yAxis: {
                        title: { text: null },                    
                        min: 0,
                        allowDecimals: false,
                        labels: {
                            style: { color: 'black' }
                        }
                    },
                    legend: { enabled: true },
                    tooltip: {
                        shared: true,
                        formatter: function () {
                            let tooltip = `<b>Tickets</b><br/>`;
                            this.points.forEach(point => {
                                tooltip += `${point.series.name}: <b>${point.y}</b><br/>`;
                            });
                            return tooltip;
                        }
                    },
                    plotOptions: {
                        column: {
                            grouping: true,        // Required to place columns side by side
                            groupPadding:0.1,   // Adjust space between groups
                            pointPadding: 0.05,   // Adjust space between columns
                            borderWidth: 0,
                            pointWidth: 20,        // Optional: consistent width
                            dataLabels: {
                                enabled: true,
                                style: {
                                    fontWeight: 'bold',
                                    color: 'black'
                                }
                            }
                        }
                    },            
                    series: [
                        {
                            name: 'Within SLA',
                            color: '#97cf8c',
                            data: withinSlaData
                        },
                        {
                            name: 'SLA Breach',
                            color: '#f46964',
                            data: slaBreachData
                        }
                    ],
                    drilldown: {
                        series: drilldownData
                    }
                }); 

                // Hide loader
                hideChartLoader();
            
            },
            error: function(error) {
                hideChartLoader();
                console.error('Error fetching graph data:', error);
                // alert('Failed to fetch data. Please try again.');
            },
        });
    }
</script>
<!-- //SLA Chart JS -->

<!-- Ticket Status Chart Js -->
<script>
    var t_status_from_date = $("#t_status_from_date").flatpickr({
        dateFormat: 'd-M-Y', // Correct Flatpickr format
        defaultDate: firstDate, // Pre-set date
        onChange: function(selectedDates, dateStr, instance) {
            if (dateStr) {
                t_status_to_date.set("minDate", dateStr); // Set minimum date for t_status_to_date
            } else {
                t_status_to_date.set("minDate", null); // Clear minimum date for t_status_to_date
            }
        }
    });

    var t_status_to_date = $("#t_status_to_date").flatpickr({
        dateFormat: 'd-M-Y', // Correct Flatpickr format
        defaultDate: lastDate, // Pre-set date
        onChange: function(selectedDates, dateStr, instance) {
            if (dateStr) {
                t_status_from_date.set("maxDate", dateStr); // Set maximum date for t_status_from_date
            } else {
                t_status_from_date.set("maxDate", null); // Clear maximum date for t_status_from_date
            }
        }
    });

    $('#tStatusFilterBtn').click(function() {
        getTicketStatusChartData();
    });

    $('#tStatusResetBtn').click(function() {

        // Reset Flatpickr inputs to default dates
        t_status_from_date.setDate(firstDate, true); // true to trigger the input change event
        t_status_to_date.setDate(lastDate, true); // true to trigger the input change event

        // Clear other inputs or dropdowns
        $("#tStatusTeamFilter").val('');

        // Fetch chart data with the reset values
        getTicketStatusChartData();
    });

    getTicketStatusChartData();

    let ticketStatusChart;

    function getTicketStatusChartData() {
        var fromDate = $('#t_status_from_date').val();
        var toDate = $('#t_status_to_date').val();
        var teamId = $('#tStatusTeamFilter').val();

        var isChecked = document.getElementById('t_status_checkBox').classList.contains('active') ? 'Y' : 'N';

        showChartLoader();

        $.ajax({
            url: "{{ route('get.ticket.status.chart.data') }}",
            type: "GET", // Use POST if needed,
            dataType: 'JSON',
            data: {
                fromDate: fromDate,
                toDate: toDate,
                teamId: teamId,
                isChecked: isChecked
            },
            success: function(data) {
                // Convert PHP variables to JavaScript
                const hierarchyData = (data.drilldown ?? []);

                // const ticketStatusCounts = data.statusCounts ?? {};
                const totalTicketsStatus = data.totalTicketsStatus;

                document.getElementById('total-ticket-status-count').textContent = + totalTicketsStatus;

                const chartData = [];
                const drilldownData = [];

                Object.entries(hierarchyData).forEach(([type, typeData]) => {
                    chartData.push({
                        name: type,
                        y: typeData.count,
                        drilldown: type
                    });

                    // Second level: Categories
                    const categoryDrillData = [];
                    Object.entries(typeData.categories).forEach(([category, catData]) => {
                        categoryDrillData.push({
                            name: category,
                            y: catData.count,
                            drilldown: `${type}||${category}`
                        });

                        // Third level: Sub-categories
                        const subCategoryDrillData = Object.entries(catData.sub_categories).map(
                            ([sub, count]) => [sub, count]
                        );

                        drilldownData.push({
                            id: `${type}||${category}`,
                            name: `Sub-Categories - ${category}`,
                            data: subCategoryDrillData,
                            pointWidth: 40
                        });
                    });

                    drilldownData.push({
                        id: type,
                        name: `Categories - ${type}`,
                        data: categoryDrillData,
                        pointWidth: 40
                    });
                });
            
                if (ticketStatusChart) {
                    ticketStatusChart.destroy();
                }

                // Ticket Status
                ticketStatusChart = Highcharts.chart('ticketStatus', {
                    chart: {
                        type: 'column',
                    },
                    accessibility: {
                        enabled: false
                    },
                    title: {
                        text: null // You can add a title here if needed
                    },
                    xAxis: {
                        type: 'category',
                        labels: {
                            rotation: -55,
                            style: { color: 'black' }
                        }
                    },
                    yAxis: {
                        title: { text: null },                    
                        min: 0,
                        allowDecimals: false,
                        labels: {
                            style: { color: 'black' }
                        }
                    },
                    legend: { enabled: false },
                    tooltip: {
                        pointFormat: 'Tickets: <b>{point.y}</b>'
                    },
                    plotOptions: {
                        series: {
                            dataLabels: {
                                enabled: true,
                                style: { 
                                    fontWeight: 'bold', 
                                    color: 'black' 
                                }
                            }
                        }
                    },               
                    series: [{
                        name: 'Ticket Types',
                        colorByPoint: true,
                        data: chartData,
                        pointWidth: 40, 
                    }],
                    drilldown: {
                        series: drilldownData
                    }
                });
                hideChartLoader();
            },
            error: function(error) {
                hideChartLoader();
                console.error('Error fetching graph data:', error);
                // alert('Failed to fetch data. Please try again.');
            },
        });
    }
</script>
<!-- // Ticket Status Chart Js -->

<!-- Feedback Report chart Js -->
<script>
    var feedback_from_date = $("#feedback_from_date").flatpickr({
        dateFormat: 'd-M-Y', // Correct Flatpickr format
        defaultDate: firstDate, // Pre-set date
        onChange: function(selectedDates, dateStr, instance) {
            if (dateStr) {
                feedback_to_date.set("minDate", dateStr); // Set minimum date for feedback_to_date
            } else {
                feedback_to_date.set("minDate", null); // Clear minimum date for feedback_to_date
            }
        }
    });

    var feedback_to_date = $("#feedback_to_date").flatpickr({
        dateFormat: 'd-M-Y', // Correct Flatpickr format
        defaultDate: lastDate, // Pre-set date
        onChange: function(selectedDates, dateStr, instance) {
            if (dateStr) {
                feedback_from_date.set("maxDate", dateStr); // Set maximum date for feedback_from_date
            } else {
                feedback_from_date.set("maxDate", null); // Clear maximum date for feedback_from_date
            }
        }
    });

    $('#feedbackFilterBtn').click(function() {
        getFeedbackReportChartData();
    });

    $('#feedbackResetBtn').click(function() {
        // Reset Flatpickr inputs to default dates
        feedback_from_date.setDate(firstDate, true); // true to trigger the input change event
        feedback_to_date.setDate(lastDate, true); // true to trigger the input change event

        // Clear other inputs or dropdowns
        $("#feedbackTeamFilter").val('');

        // Fetch chart data with the reset values
        getFeedbackReportChartData();
    });

    getFeedbackReportChartData();

    let feedbackReportChart;

    function getFeedbackReportChartData() {
        var fromDate = $('#feedback_from_date').val();
        var toDate = $('#feedback_to_date').val();
        var teamId = $('#feedbackTeamFilter').val();

        var isChecked = document.getElementById('feedback_checkBox').classList.contains('active') ? 'Y' : 'N';

        showChartLoader();

        $.ajax({
            url: "{{ route('get.feedback.report.chart.data') }}",
            type: "GET", // Use POST if needed,
            dataType: 'JSON',
            data: {
                fromDate: fromDate,
                toDate: toDate,
                teamId: teamId,
                isChecked: isChecked
            },
            success: function(data) {
                // Convert PHP variables to JavaScript
                const technicians = (data.technicians ?? []);
                // Extract engineer names and ticket data
                const engineerNames = technicians.map(tech => tech.USER_NAME);

                const feedbackPoints = technicians.map(tech => tech.feedbackPoints);
                const noOfUserFeedback = technicians.map(tech => tech.noOfUserFeedback);

                const feedbackSummary = technicians.map(tech => tech.feedbackSummary);
            
                const starColors = {
                    5: '#e5a8d7', // Green for 5-star
                    4: '#551fcf', // Yellow for 4-starf44336
                    3: '#e50e0e', // Orange for 3-star
                    2: '#9e9e9e', // Red for 2-star
                    1: '#d8831d', // Gray for 1-star
                };
              
                const totalFeedbacks = data.totalFeedbacks || 0;
                document.getElementById('total-feedback-ticket-count').textContent = + totalFeedbacks;

                const totalStarTickets = data.totalStarTickets || {};

                // Build a string like: 1⭐: 3 | 2⭐: 5 | 3⭐: 7 | 4⭐: 10 | 5⭐: 35
                const starsHtml = Object.entries(totalStarTickets)                    
                    .map(([star, count]) => {
                        const color = starColors[star] || "#000"; // fallback to black if not defined
                        return `<span style="font-weight: 800;">${star} ⭐ :</span>
                            <span style="color: #fff; background: ${color}; padding:0 10px;font-size: 12px;border-radius: 50%;">${count}</span>`;
                    })
                    .join(' ');

                document.getElementById('total-feedback-star').innerHTML = starsHtml;


                const feedbackDatasets = Object.keys(starColors)
                    .map(star => ({
                        label: `${star}-star`,
                        data: Array(technicians.length).fill(0), // Initialize data array with zeros
                        backgroundColor: starColors[star] || '#ccc',
                        borderColor: starColors[star] || '#ccc',
                    }));
                
                // Loop through each technician's feedback summary
                technicians.forEach((tech, techIndex) => {
                    const summary = tech.feedbackSummary || {};
                    Object.entries(summary).forEach(([star, data]) => {
                        const datasetIndex = feedbackDatasets.findIndex(ds => ds.label ===
                            `${star}-star`);
                        if (datasetIndex !== -1) {
                            feedbackDatasets[datasetIndex].data[techIndex] = data.ticket || '';
                        }
                    });
                });

                let allDataValues = [
                    ...noOfUserFeedback,
                    ...feedbackDatasets.flatMap(dataset => dataset.data)
                ];

                if (feedbackReportChart) {
                    feedbackReportChart.destroy();
                }   

                // Feedback Report
                feedbackReportChart = Highcharts.chart('feedbackReport', {
                    chart: {
                        type: 'column',
                        height: 400,
                        backgroundColor: 'transparent'
                    },
                    title: { text: null },
                    xAxis: {
                        categories: engineerNames,
                        title: null,
                        labels: { 
                            style: { color: 'black' },
                            rotation: -45
                        }
                    },
                    yAxis: {
                        min: 0,
                        title: null,
                        stackLabels: {
                            enabled: true,
                            formatter: function () {                               
                                if (this.stack === 'user') {
                                    return this.total; // Show 'No of Feedback' on top
                                    // return totalPoints;
                                } 
                                const index = this.x;
                                const totalPoints = feedbackPoints[index] || 0;                                   

                                return totalPoints; // Show total feedback points
                            },
                            style: {
                                fontWeight: 'bold',
                                color: 'black'
                            }
                        },
                        labels: { style: { color: 'black' } }
                    },
                    tooltip: {
                        shared: true,
                        formatter: function () {
                            let s = `<b>${this.points[0].key}</b><br/>`; // Engineer name
                            this.points.forEach(point => {
                                s += `${point.series.name}: ${point.y}<br/>`;
                            });
                            return s;
                        }
                    },
                    plotOptions: {
                        series: {
                            stacking: 'normal',
                            dataLabels: {
                                enabled: false,
                                style: { fontWeight: 'bold', color: 'black' }
                            }
                        }
                    },  
                    legend: {
                        reversed: false,
                        itemStyle: {
                            color: 'black',
                            fontWeight: 'normal'
                        }
                    },
                    series: [
                        {
                            name: 'No of Feedbacks',
                            data: noOfUserFeedback,
                            color: '#90e7c1',
                            stack: 'user',
                            pointWidth: 20, 
                        },
                        ...Object.keys(starColors)
                            .sort((a, b) => b - a) // 1 → 5
                            .map(star => ({
                                name: `${star}-star`,
                                data: technicians.map(tech => tech.feedbackSummary?.[star]?.ticket || 0),
                                color: starColors[star],
                                stack: 'feedback',
                                pointWidth: 20, 
                            }))
                    ]
                });     
                
                hideChartLoader();
            },
            error: function(error) {
                hideChartLoader();
                console.error('Error fetching graph data:', error);
                // alert('Failed to fetch data. Please try again.');
            },
        });
    }
</script>
<!-- // Feedback Report chart Js -->

<!-- Department Wise Ticket Chart JS -->
<script>
    var dept_from_date = $("#dept_from_date").flatpickr({
        dateFormat: 'd-M-Y', // Correct Flatpickr format
        defaultDate: firstDate, // Pre-set date
        onChange: function(selectedDates, dateStr, instance) {
            if (dateStr) {
                dept_to_date.set("minDate", dateStr); // Set minimum date for dept_to_date
            } else {
                dept_to_date.set("minDate", null); // Clear minimum date for dept_to_date
            }
        }
    });

    var dept_to_date = $("#dept_to_date").flatpickr({
        dateFormat: 'd-M-Y', // Correct Flatpickr format
        defaultDate: lastDate, // Pre-set date
        onChange: function(selectedDates, dateStr, instance) {
            if (dateStr) {
                dept_from_date.set("maxDate", dateStr); // Set maximum date for dept_from_date
            } else {
                dept_from_date.set("maxDate", null); // Clear maximum date for dept_from_date
            }
        }
    });

    $('#deptFilterBtn').click(function() {
        getDeptTicketChartData();
    });

    $('#deptResetBtn').click(function() {

        // Reset Flatpickr inputs to default dates
        dept_from_date.setDate(firstDate, true); // true to trigger the input change event
        dept_to_date.setDate(lastDate, true); // true to trigger the input change event

        // Clear other inputs or dropdowns
        $("#deptTeamFilter").val('');

        // Fetch chart data with the reset values
        getDeptTicketChartData();
    });

    getDeptTicketChartData();

    let departmentTicketChart;

    function getDeptTicketChartData() {
        var fromDate = $('#dept_from_date').val();
        var toDate = $('#dept_to_date').val();
        var teamId = $('#deptTeamFilter').val();

        var isChecked = document.getElementById('dept_checkBox').classList.contains('active') ? 'Y' : 'N';

        showChartLoader();

        $.ajax({
            url: "{{ route('get.dept.ticket.chart.data') }}",
            type: "GET", // Use POST if needed,
            dataType: 'JSON',
            data: {
                fromDate: fromDate,
                toDate: toDate,
                teamId: teamId,
                isChecked: isChecked
            },
            success: function(data) {
                // Convert PHP variables to JavaScript
                const deptData  = data.departmentHierarchy || {};

                const totalTicketsDept = data.totalTicketsDept || 0;

                document.getElementById('total-dept-ticket-count').textContent = + totalTicketsDept;  
                
                const chartData = [];
                const drilldownData = [];

                Object.entries(deptData).forEach(([dept, ddata]) => {
                    chartData.push({
                        name: dept,
                        y: ddata.total,
                        drilldown: dept
                    });

                    // Second level: Categories
                    const typeData = [];
                    Object.entries(ddata.types).forEach(([tt, tdata]) => {
                        const typeId = `${dept}||${tt}`;
                            typeData.push({
                                name: tt,
                                y: tdata.total,
                                drilldown: typeId 
                            });

                        // Third level: Sub-categories
                        const catSeriesData = Object.entries(tdata.categories).map(([c, cnt]) => [c, cnt]);

                        drilldownData.push({
                            id: typeId,
                            name: `Categories in ${tt} (${dept})`,
                            data: catSeriesData,
                            pointWidth: 40
                        });
                    });

                    drilldownData.push({
                        id: dept,
                        name: `Ticket Types in ${dept}`,
                        data: typeData,
                        pointWidth: 40
                    });
                });

                if (departmentTicketChart) {
                    departmentTicketChart.destroy();
                }

                // Department Wise Ticket
                departmentTicketChart = Highcharts.chart('departmentTicket', {
                    chart: { type: 'column' },
                    title: { text: null },
                    xAxis: {
                        type: 'category',
                        labels: {
                            rotation: -55,
                            style: { color: 'black' }
                        }
                    },
                    yAxis: {
                        title: { text: null },                    
                        min: 0,
                        allowDecimals: false,
                        labels: {
                            style: { color: 'black' }
                        }
                    },
                    legend: { enabled: false },
                    tooltip: { pointFormat: 'Tickets: <b>{point.y}</b>' },
                    plotOptions: {
                        series: {
                            dataLabels: {
                                enabled: true,
                                style: { 
                                    fontWeight: 'bold', 
                                    color: 'black' 
                                }
                            }
                        }
                    },               
                    series: [{
                        name: 'Department Tickets',
                        colorByPoint: true,
                        data: chartData,
                        pointWidth: 40, 
                    }],
                    drilldown: {
                        series: drilldownData
                    }
                });

                hideChartLoader();
            },
            error: function(error) {
                hideChartLoader();
                console.error('Error fetching graph data:', error);
                // alert('Failed to fetch data. Please try again.');
            },
        });
    }
</script>
<!-- // Department Wise Ticket Chart JS -->

<!-- Engineer Ticket Point Chart JS -->
<script>
    var ep_from_date = $("#ep_from_date").flatpickr({
        dateFormat: 'd-M-Y', // Correct Flatpickr format
        defaultDate: firstDate, // Pre-set date
        onChange: function(selectedDates, dateStr, instance) {
            if (dateStr) {
                ep_to_date.set("minDate", dateStr); // Set minimum date for ep_to_date
            } else {
                ep_to_date.set("minDate", null); // Clear minimum date for ep_to_date
            }
        }
    });

    var ep_to_date = $("#ep_to_date").flatpickr({
        dateFormat: 'd-M-Y', // Correct Flatpickr format
        defaultDate: lastDate, // Pre-set date
        onChange: function(selectedDates, dateStr, instance) {
            if (dateStr) {
                ep_from_date.set("maxDate", dateStr); // Set maximum date for ep_from_date
            } else {
                ep_from_date.set("maxDate", null); // Clear maximum date for ep_from_date
            }
        }
    });

    $('#epFilterBtn').click(function() {
        getEngineerPointChartData();
    });

    $('#epResetBtn').click(function() {

        // Reset Flatpickr inputs to default dates
        ep_from_date.setDate(firstDate, true); // true to trigger the input change event
        ep_to_date.setDate(lastDate, true); // true to trigger the input change event

        // Clear other inputs or dropdowns
        $("#epTeamFilter").val('');

        // Fetch chart data with the reset values
        getEngineerPointChartData();
    });

    getEngineerPointChartData();
    let ticketPointsChart;

    function getEngineerPointChartData() {
        var fromDate = $('#ep_from_date').val();
        var toDate = $('#ep_to_date').val();
        var teamId = $('#epTeamFilter').val();

        var isChecked = document.getElementById('ep_checkBox').classList.contains('active') ? 'Y' : 'N';

        showChartLoader();

        $.ajax({
            url: "{{ route('get.engineer.point.chart.data') }}",
            type: "GET", // Use POST if needed,
            dataType: 'JSON',
            data: {
                fromDate: fromDate,
                toDate: toDate,
                teamId: teamId,
                isChecked: isChecked
            },
            success: function(data) {
                // Convert PHP variables to JavaScript
                const technicians = (data.technicians ?? []);
                // Extract engineer names and ticket data
                const engineerNames = technicians.map(tech => tech.USER_NAME);

                const totalTicketsPoints = data.totalTicketsPoints || 0;
                document.getElementById('total-ticket-points-count').textContent = + totalTicketsPoints;

                const chartData = [];
                const drilldownData = [];

                technicians.forEach(tech => {
                    chartData.push({
                        name: tech.USER_NAME,
                        y: tech.total_points,
                        drilldown: tech.USER_NAME
                    });

                    const ticketPointsDrillData = [];

                    (tech.ticket_types || []).forEach(type => {
                        ticketPointsDrillData.push([type.name, type.count]);

                        // Push 2nd level drilldown: ticket category under type
                        drilldownData.push({
                            id: `${tech.USER_NAME}-${type.name}`,
                            name: `Categories of ${type.name} - ${tech.USER_NAME}`,
                            data: type.categories.map(cat => [cat.name, cat.count]),
                            pointWidth: 40
                        });
                    });
                
                    drilldownData.push({
                        id: tech.USER_NAME,
                        name: `Ticket Types - ${tech.USER_NAME}`,
                        data: ticketPointsDrillData.map(([typeName, count]) => ({
                            name: typeName,
                            y: count,
                            drilldown: `${tech.USER_NAME}-${typeName}`
                        })),
                        pointWidth: 40
                    });
                });
            
                if (ticketPointsChart) {
                    ticketPointsChart.destroy();
                }

                // Engineer's Ticket Points
                ticketPointsChart = Highcharts.chart('ticketPoints', {
                    chart: {
                        type: 'column',
                    },
                    accessibility: {
                        enabled: false
                    },
                    title: {
                        text: null // You can add a title here if needed
                    },
                    xAxis: {
                        type: 'category',
                        labels: {
                            style: { color: 'black' },
                            rotation: -55
                        }
                    },
                    yAxis: {
                        title: { text: null },                    
                        // min:-30,
                        allowDecimals: false,
                        labels: {
                            style: { color: 'black' }
                        }
                    },
                    legend: { enabled: false },
                    tooltip: {
                        pointFormat: 'Tickets: <b>{point.y}</b>'
                    },
                    plotOptions: {
                        series: {
                            dataLabels: {
                                enabled: true,
                                inside: false, // Important for negative values
                                style: { 
                                    fontWeight: 'bold', 
                                    color: 'black' 
                                }
                            }
                        }
                    },               
                    series: [{
                        name: 'Ticket Types',
                        colorByPoint: true,
                        data: chartData,
                        pointWidth: 40, 
                    }],
                    drilldown: {
                        // drillUpButton: {
                        //     relativeTo: 'spacingBox',
                        //     position: { x: 0, y: 0 }
                        // },
                        series: drilldownData
                    }
                });

                hideChartLoader();
            },
            error: function(error) {
                hideChartLoader();
                console.error('Error fetching graph data:', error);
                // alert('Failed to fetch data. Please try again.');
            },
        });
    }
</script>
<!-- // Engineer Ticket Point Chart JS -->

<!-- Ticket Duration Chart JS -->
<script>
    var td_from_date = $("#td_from_date").flatpickr({
        dateFormat: 'd-M-Y', // Correct Flatpickr format
        defaultDate: firstDate, // Pre-set date
        onChange: function(selectedDates, dateStr, instance) {
            if (dateStr) {
                td_to_date.set("minDate", dateStr); // Set minimum date for td_to_date
            } else {
                td_to_date.set("minDate", null); // Clear minimum date for td_to_date
            }
        }
    });

    var td_to_date = $("#td_to_date").flatpickr({
        dateFormat: 'd-M-Y', // Correct Flatpickr format
        defaultDate: lastDate, // Pre-set date
        onChange: function(selectedDates, dateStr, instance) {
            if (dateStr) {
                td_from_date.set("maxDate", dateStr); // Set maximum date for td_from_date
            } else {
                td_from_date.set("maxDate", null); // Clear maximum date for td_from_date
            }
        }
    });

    $('#tdFilterBtn').click(function() {
        getTicketDurationChartData();
    });

    $('#tdResetBtn').click(function() {

        // Reset Flatpickr inputs to default dates
        td_from_date.setDate(firstDate, true); // true to trigger the input change event
        td_to_date.setDate(lastDate, true); // true to trigger the input change event

        // Clear other inputs or dropdowns
        $("#tdTeamFilter").val('');

        // Fetch chart data with the reset values
        getTicketDurationChartData();
    });
    getTicketDurationChartData();

    let ticketDurationChart;

    function getTicketDurationChartData() {
        var fromDate = $('#td_from_date').val();
        var toDate = $('#td_to_date').val();
        var teamId = $('#tdTeamFilter').val();

        var isChecked = document.getElementById('td_checkBox').classList.contains('active') ? 'Y' : 'N';

        showChartLoader();

        $.ajax({
            url: "{{ route('get.ticket.duration.chart.data') }}",
            type: "GET", // Use POST if needed,
            dataType: 'JSON',
            data: {
                fromDate: fromDate,
                toDate: toDate,
                teamId: teamId,
                isChecked: isChecked
            },
            success: function(data) {
                // Convert PHP variables to JavaScript
                const ticketType = (data.ticketTypes ?? [0]);
                // Extract engineer names and ticket data
                const ticketTypes = Object.keys(ticketType);

                const actualTicketTime = data.actualTicketTime && data.actualTicketTime.length ? data.actualTicketTime : [0];
                const spentTime = data.spentTime && data.spentTime.length ? data.spentTime : [0];
              
                if (ticketDurationChart) {
                    ticketDurationChart.destroy();
                }

                // Duration Wise Ticket Type
                ticketDurationChart =  Highcharts.chart('ticketDuration', {
                    chart: {
                        type: 'column',
                        height: 400,
                        backgroundColor: 'transparent'
                    },
                    title: { text: null },
                    xAxis: {
                        categories: ticketTypes,
                        crosshair: true,
                        labels: {
                            rotation: -45,
                            style: { color: 'black' }
                        }
                    },
                    yAxis: {
                        min: 0,
                        title: { text: null },
                        labels: { style: { color: 'black' } }
                    },
                    tooltip: {
                        shared: true,
                        headerFormat: '<b>{point.key}</b><br/>',
                        pointFormat: '{series.name}: <b>{point.y}</b><br/>'
                    },
                    plotOptions: {
                        column: {
                            pointPadding: 0.1,
                            borderWidth: 0,
                            dataLabels: {
                                enabled: true,
                                style: {
                                    fontWeight: 'bold',
                                    color: 'black'
                                }
                            },
                            grouping: true,
                            groupPadding: 0.2
                        }
                    },
                    legend: {
                        enabled: true
                    },
                    series: [
                        {
                            name: 'Actual Time',
                            data: actualTicketTime,
                            color: '#0583e1',
                            pointWidth: 20, // Adjust width as needed
                        },
                        {
                            name: 'Spent Time',
                            data: spentTime,
                            color: '#fdbb03',
                            pointWidth: 20, // Adjust width as needed
                        }
                    ],
                    credits: { enabled: false }
                });

                hideChartLoader();
            },
            error: function(error) {
                hideChartLoader();
                console.error('Error fetching graph data:', error);
            },
        });
    }
</script>
<!-- // Ticket Duration Chart JS -->

<!-- Log Wise Ticket Chart JS -->
<script>
    var logBy_from_date = $("#logBy_from_date").flatpickr({
        dateFormat: 'd-M-Y', // Correct Flatpickr format
        defaultDate: firstDate, // Pre-set date
        onChange: function(selectedDates, dateStr, instance) {
            if (dateStr) {
                logBy_to_date.set("minDate", dateStr); // Set minimum date for logBy_to_date
            } else {
                logBy_to_date.set("minDate", null); // Clear minimum date for logBy_to_date
            }
        }
    });

    var logBy_to_date = $("#logBy_to_date").flatpickr({
        dateFormat: 'd-M-Y', // Correct Flatpickr format
        defaultDate: lastDate, // Pre-set date
        onChange: function(selectedDates, dateStr, instance) {
            if (dateStr) {
                logBy_from_date.set("maxDate", dateStr); // Set maximum date for logBy_from_date
            } else {
                logBy_from_date.set("maxDate", null); // Clear maximum date for logBy_from_date
            }
        }
    });

    $('#logByFilterBtn').click(function() {
        getLogWiseTicketChartData();
    });

    $('#logByResetBtn').click(function() {

        // Reset Flatpickr inputs to default dates
        logBy_from_date.setDate(firstDate, true); // true to trigger the input change event
        logBy_to_date.setDate(lastDate, true); // true to trigger the input change event

        // Clear other inputs or dropdowns
        $("#logByTeamFilter").val('');

        // Fetch chart data with the reset values
        getLogWiseTicketChartData();
    });

    getLogWiseTicketChartData();

    let logWiseTicketChart;

    function getLogWiseTicketChartData() {
        var fromDate = $('#logBy_from_date').val();
        var toDate = $('#logBy_to_date').val();
        var teamId = $('#logByTeamFilter').val();

        var isChecked = document.getElementById('logwise_checkBox').classList.contains('active') ? 'Y' : 'N';
        showChartLoader();

        $.ajax({
            url: "{{ route('get.log.ticket.chart.data') }}",
            type: "GET", // Use POST if needed,
            dataType: 'JSON',
            data: {
                fromDate: fromDate,
                toDate: toDate,
                teamId: teamId,
                isChecked: isChecked
            },
            success: function(data) {

                const logByUsers = data.ticketCounts ?? [];

                const totalLogTickets = data.totalLogTickets ?? 0;
                document.getElementById('total-log-ticket-count').textContent = + totalLogTickets;

                const chartData = [];
                const drilldownData = [];

                logByUsers.forEach(tech => {

                    chartData.push({
                        name: tech.name,
                        y: tech.count,
                        drilldown: tech.name
                    });

                    const typeLevel = [];

                    (tech.ticket_types || []).forEach(type => {
                        const typeId = `${tech.name}_${type.name}`;
                        typeLevel.push({
                            name: type.name,
                            y: type.count,
                            drilldown: typeId
                        });

                        // Drilldown: type → categories
                        const categoryLevel = type.categories.map(cat => [cat.name, cat.count]);

                        drilldownData.push({
                            name: type.name,
                            id: typeId,
                            data: categoryLevel,
                            pointWidth: 40
                        });
                    });

                    drilldownData.push({
                        name: tech.name,
                        id: tech.name,
                        data: typeLevel,
                        pointWidth: 40
                    });              
                
                });
                
                if (logWiseTicketChart) {
                    logWiseTicketChart.destroy();
                }

                // Engineer's Ticket
                logWiseTicketChart = Highcharts.chart('logByTicket', {
                    chart: {
                        type: 'column',
                    },
                    accessibility: {
                        enabled: false
                    },
                    title: {
                        text: null // You can add a title here if needed
                    },
                    xAxis: {
                        type: 'category',
                        labels: {
                            style: { color: 'black' },
                            rotation: -55
                        }
                    },
                    yAxis: {
                        title: { text: null },                    
                        // min:0,
                        allowDecimals: false,
                        labels: {
                            style: { color: 'black' }
                        }
                    },
                    legend: { enabled: false },
                    tooltip: {
                        pointFormat: 'Tickets: <b>{point.y}</b>'
                    },
                    plotOptions: {
                        series: {
                            dataLabels: {
                                enabled: true,
                                style: { 
                                    fontWeight: 'bold', 
                                    color: 'black' 
                                }
                            }
                        }
                    },               
                    series: [{
                        name: 'Ticket Types',
                        colorByPoint: true,
                        data: chartData,
                        pointWidth: 40, 
                    }],
                    drilldown: {
                        series: drilldownData
                    }
                });

                hideChartLoader();
            },
            error: function(error) {
                hideChartLoader();
                console.error('Error fetching graph data:', error);
            },
        });
    }
</script>
<!-- Log Wise Ticket Chart JS -->

<!-- Breached Ticket Points Chart -->
<script>
    var breached_from_date = $("#breached_from_date").flatpickr({
        dateFormat: 'd-M-Y', // Correct Flatpickr format
        defaultDate: firstDate, // Pre-set date
        onChange: function(selectedDates, dateStr, instance) {
            if (dateStr) {
                breached_to_date.set("minDate", dateStr); // Set minimum date for breached_to_date
            } else {
                breached_to_date.set("minDate", null); // Clear minimum date for breached_to_date
            }
        }
    });

    var breached_to_date = $("#breached_to_date").flatpickr({
        dateFormat: 'd-M-Y', // Correct Flatpickr format
        defaultDate: lastDate, // Pre-set date
        onChange: function(selectedDates, dateStr, instance) {
            if (dateStr) {
                breached_from_date.set("maxDate", dateStr); // Set maximum date for breached_from_date
            } else {
                breached_from_date.set("maxDate", null); // Clear maximum date for breached_from_date
            }
        }
    });

    $('#breachedFilterBtn').click(function() {
        getBreachedTicketPointsChartData();
    });
    $('#breachedResetBtn').click(function() {

        // Reset Flatpickr inputs to default dates
        breached_from_date.setDate(firstDate, true); // true to trigger the input change event
        breached_to_date.setDate(lastDate, true); // true to trigger the input change event

        // Clear other inputs or dropdowns
        $("#breachedTeamFilter").val('');

        // Fetch chart data with the reset values
        getBreachedTicketPointsChartData();
    });

    getBreachedTicketPointsChartData();
    let breachedTicketPointsChart;

    function getBreachedTicketPointsChartData() {
        var fromDate = $('#breached_from_date').val();
        var toDate = $('#breached_to_date').val();
        var teamId = $('#breachedTeamFilter').val();

        var isChecked = document.getElementById('breached_checkBox').classList.contains('active') ? 'Y' : 'N';

        showChartLoader();

        $.ajax({
            url: "{{ route('get.breached.point.chart.data') }}",
            type: "GET", // Use POST if needed,
            dataType: 'JSON',
            data: {
                fromDate: fromDate,
                toDate: toDate,
                teamId: teamId,
                isChecked: isChecked
            },
            success: function(data) {
                // Convert PHP variables to JavaScript
                const technicians = (data.breachedTickets ?? []);

                const totalTicketsPoints = data.totalBreachedPoints || 0;
                document.getElementById('total-breached-ticket-points-count').textContent = + totalTicketsPoints; 
                
                const chartData = [];
                const drilldownData = [];
                
                technicians.forEach(tech => {
                    chartData.push({
                        name: tech.name,
                        y: tech.count,
                        drilldown: tech.name
                    });

                    const ticketTypeDrillData = [];

                    (tech.ticket_types || []).forEach(type => {
                        ticketTypeDrillData.push([type.name, type.count]);

                        // Push 2nd level drilldown: ticket category under type
                        drilldownData.push({
                        id: `${tech.name}-${type.name}`,
                        name: `Categories of ${type.name} - ${tech.name}`,
                        data: type.categories.map(cat => [cat.name, cat.count]),
                        pointWidth: 40
                        });
                    });
                
                    drilldownData.push({
                        id: tech.name,
                        name: `Ticket Types - ${tech.name}`,
                        data: ticketTypeDrillData.map(([typeName, count]) => ({
                            name: typeName,
                            y: count,
                            drilldown: `${tech.name}-${typeName}`
                        })),
                        pointWidth: 40
                    });
                });


                if (breachedTicketPointsChart) {
                    breachedTicketPointsChart.destroy();
                }

                // Breached Ticket Points                
                breachedTicketPointsChart = Highcharts.chart('breachedTicketPoints', {
                    chart: {
                        type: 'column',
                    },
                    accessibility: {
                        enabled: false
                    },
                    title: {
                        text: null // You can add a title here if needed
                    },
                    xAxis: {
                        type: 'category',                    
                        labels: { style: { color: 'black' } ,
                            rotation: 305
                        }
                    },
                    yAxis: {
                        title: { text: null },                    
                        // min: minTicketPoint < 0 ? minTicketPoint - 5 : 0,
                        allowDecimals: false,
                        labels: {
                            style: { color: 'black' }
                        }
                    },
                    legend: { enabled: false },
                    tooltip: {
                        pointFormat: 'Tickets: <b>{point.y}</b>'
                    },
                    plotOptions: {
                        series: {
                            dataLabels: {
                                enabled: true,
                                inside: false, // Important for negative values
                                style: { 
                                    fontWeight: 'bold', 
                                    color: 'black' 
                                }
                            }
                        }
                    },               
                    series: [{
                        name: 'Breached Points',
                        colorByPoint: true,
                        data: chartData,
                        pointWidth: 40, 
                    }],
                    drilldown: {
                        series: drilldownData
                    }
                });     
                
                hideChartLoader();
            },
            error: function(error) {
                hideChartLoader();
                console.error('Error fetching graph data:', error);
                // alert('Failed to fetch data. Please try again.');
            },
        });
    }
</script>
<!-- // Breached Ticket Points Chart -->
@endsection