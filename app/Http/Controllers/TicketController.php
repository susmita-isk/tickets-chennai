<?php

namespace App\Http\Controllers;

use File;
use Session;
use DateTime;
use PHPMailer;
use XMLWriter;
use DataTables;
use Carbon\Carbon;
use Google_Client;
use App\Models\User;
use App\Models\HrApi;
use App\Models\Ticket;
use GuzzleHttp\Client; 
use App\Models\TaskType;
use App\Models\Template;
use App\Models\TicketsApi;
use App\Models\HolidayList;
use Illuminate\Http\Request;
use App\Models\RecurringTicket;
use App\Models\TicketProcedure;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TicketController extends Controller
{
    public function index(Request $request)
    {
       if(request('id'))
        {
            // Clear the previous session
            session()->forget('code');
            
            session()->put('code', request('id'));
        }else{
            if (empty(session('code'))) {
                // If session code is empty, redirect to the home route
                return redirect()->route('home');
            }else{
                return redirect()->route('tickets',['id'=>session('code')]);
            }
        }

        $api = new TicketsApi;

        $response = $api->getTeams();

        $permissions = userMenu(); 
        
        $departmentName = DB::table('department_details')->where('DEPARTMENT_ID',session('code'))->first()->DEPARTMENT_NAME;
    
        $templates = Template::where(['DEPARTMENT_ID' => session('code'),
                                        'IS_ACTIVE' => 'Y'])
                                ->orderBy('TEMPLATE_NAME','asc')
                                ->get();
       if ($request->ajax()) {
        if($request->status)
        {
            $status=$request->status;
            $wthoutSts='';
        }else{
            $status='';
            $wthoutSts='Yes';
        }

        $userEmployeeId = (userRoleName() == 'User') ? 'Yes' : '';
        
        $admin = (userRoleName() == 'Admin') ? 'Yes' : '';
        $start = $request->start;
        $length = $request->length;
        $ticketquery=DB::table('ticket');
        if (!empty($request->order)) {
                $orderColumnIndex = $request->order[0]['column']; // Column index
                $orderDirection = $request->order[0]['dir']; // 'asc' or 'desc'

                // Get column name based on the index
                $columns = $request->columns;
                $orderColumnName = $columns[$orderColumnIndex]['data'];
                
                if($orderColumnName =='ticketNumber')
                {
                    $orderColumnName = 'TICKET_NO';
                }
                else if($orderColumnName == 'SUBJECT')
                {
                    $orderColumnName = 'ticket.SUBJECT';
                }
                else if($orderColumnName == 'USER_NAME')
                {
                    $orderColumnName = 'ticket.USER_NAME';
                }
                else if($orderColumnName == 'CREATED_ON')
                {
                    $orderColumnName = 'ticket.CREATED_ON';
                }
                else if($orderColumnName == 'DEPARTMENT_NAME')
                {
                    $orderColumnName = 'ticket.DEPARTMENT_NAME';
                }
                else if($orderColumnName == 'TECHNICIAN_NAME')
                {
                    $orderColumnName = 'mstr_users.USER_NAME';
                }
                else if($orderColumnName == 'CREATED_BY')
                {
                    $orderColumnName = 'ticket.CREATED_BY';
                }
                else if($orderColumnName == 'PROGRESS')
                {
                    $orderColumnName = 'ticket.PROGRESS';
                }
                else if($orderColumnName == 'TEAM_NAME')
                {
                    $orderColumnName = 'ticket.TEAM_NAME';
                }                
                else if($orderColumnName == 'action')
                {
                    $orderColumnName = 'ticket.CREATED_ON';
                }
                else{
                    $orderColumnName = 'ticket.CREATED_ON';
                }
                // Apply order to the query
                $ticketquery->orderBy($orderColumnName, $orderDirection);
        }else{
                $ticketquery->orderByRaw("CASE
            WHEN ticket.PROGRESS = 'New' THEN 1
            WHEN ticket.PROGRESS = 'Open' THEN 2
            WHEN ticket.PROGRESS = 'In Progress' THEN 3
            WHEN ticket.PROGRESS = 'On Hold' THEN 4
            ELSE 5                
            END");
        }
             
        $tickets = $ticketquery
            ->leftJoin('mstr_users', 'ticket.TECHNICIAN_ID', '=', 'mstr_users.EMPLOYEE_ID')
            ->leftJoin('ticket_attachment', 'ticket.TICKET_ID', '=', 'ticket_attachment.TICKET_ID')
            ->leftJoin('team', 'team.TEAM_NAME', '=', 'ticket.TEAM_NAME')
            ->leftJoin('map_user_team', 'map_user_team.TEAM_ID', '=', 'team.TEAM_ID') 
            ->leftJoin('lkp_task_type', 'lkp_task_type.TASK_TYPE_ID', '=', 'ticket.TASK_TYPE_ID')   
            ->where('PROJECT_ID', request('id'))
            
            ->when($status, function ($query) use ($status) {
              return $query->whereIn('ticket.STATUS', $status);
            })  
            ->when($wthoutSts, function ($query) use ($wthoutSts) {
              return $query->whereIn('ticket.STATUS', ['New','Open','Completed'])->where('IS_CLOSED', 'N');
            })

            ->when($userEmployeeId == 'Yes', function ($query) {
                return $query->where('ticket.REQUESTED_BY', Auth::user()->EMPLOYEE_ID);
            }, function ($query) use ($admin) {
                if ($admin == 'Yes') {
                    return $query;
                } else {
                    return $query->where('map_user_team.USER_ID', Auth::id());
                }
            })
             
            ->whereRaw("CONCAT(ticket.TICKET_NO, '-', ticket.TASK_NO) LIKE ?", ['%' . request('ticketNo') . '%'])
            // ->when(request('ticketNo'),      fn ($query) => $query->where('ticket.TICKET_NO', 'like', '%' . request('ticketNo') . '%'))
            ->when(request('subject'),       fn ($query) => $query->where('ticket.SUBJECT', 'like', '%' . request('subject') . '%'))
            ->when(request('description'),       fn ($query) => $query->where('ticket.DESCRIPTION', 'like', '%' . request('description') . '%'))
            ->when(request('asset'),         fn ($query) => $query->where('ticket.ASSET_ID', request('asset')))
            ->when(request('userName'),      fn ($query) => $query->where('ticket.REQUESTED_BY', request('userName')))            
            ->when(request('oldTicket'),     fn ($query) => $query->where('ticket.TICKET_NO', request('oldTicket')))
            ->when(request('department'),    fn ($query) => $query->where('ticket.DEPARTMENT_CODE', request('department')))
            ->when(request('technician'),    fn ($query) => $query->where('ticket.TECHNICIAN_ID', request('technician')))
            ->when(request('requestedFrom'), fn ($query) => $query->whereDate('ticket.CREATED_ON', '>=', date('Y-m-d', strtotime(request('requestedFrom')))))
            ->when(request('requestedTo'),   fn ($query) => $query->whereDate('ticket.CREATED_ON', '<=', date('Y-m-d', strtotime(request('requestedTo')))))
            ->when(request('category'),      fn ($query) => $query->where('ticket.CATEGORY_ID', request('category')))
            ->when(request('subcategory'),   fn ($query) => $query->where('ticket.SUB_CATEGORY_ID', request('subcategory')))
            ->when(request('item'),          fn ($query) => $query->where('ticket.ITEM_ID', request('item')))
            ->when(request('itemType'),      fn ($query) => $query->where('ticket.ITEM_TYPE_ID', request('itemType')))
            ->when(request('progress'),      fn ($query) => $query->whereIn('ticket.PROGRESS', request('progress')))
            ->when(request('mode'),          fn ($query) => $query->where('ticket.MODE', request('mode')))
            ->when(request('teamId'),        fn ($query) => $query->where('ticket.TEAM_NAME', request('teamId')))
            ->when(request('createdBy'),     fn ($query) => $query->where('ticket.CREATED_BY', 'like', '%' .request('createdBy'). '%'))
            ->select('ticket.*','mstr_users.USER_NAME as TECHNICIAN_NAME',   
                'lkp_task_type.SLA as slaIndicator',
                DB::raw('CASE WHEN ticket_attachment.TICKET_ID IS NOT NULL THEN 1 ELSE 0 END AS has_attachment'),
                DB::raw('SUBSTRING(ticket.SUBJECT, 1, 50) AS trimmed_subject'))
            ->groupBy('ticket.TICKET_ID', 'mstr_users.USER_NAME','lkp_task_type.SLA')
            ->orderby('ticket.CREATED_ON','desc');
            // ->limit($length)
            // ->get();
	
        return Datatables::of($tickets)->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (!(userRoleName() == 'User')) {
                    $disabled = (strtolower($row->STATUS) == 'open') || (strtolower($row->STATUS) == 'new') ? '' : 'disabled';
                    $btn = '<div class="d-flex justify-content-between table-actions-container">
                    <button class="btn tickets-action-btn-transparent" onClick="edit(' . $row->TICKET_ID . ')" title="Edit">
                                    <img src="' . asset('public/img/icons/edit-btn.png') . '" alt="" height="20">
                                </button>
                                <button class="btn tickets-action-btn-transparent" onClick="assign(' . $row->TICKET_ID . ',\'' . $row->TICKET_NO . '\')" title="Assign"' . $disabled . '>
                                    <img src="' . asset('public/img/icons/assign-ticket.png') . '" alt="" height="20">
                                </button>
                            </div>';                                
                    return $btn;
                }
                return '';
            })
            ->addColumn('attachment', function ($row) {
                if($row->has_attachment) {

                    if(DB::table('ticket_attachment')->where('TICKET_ID',$row->TICKET_ID)->where('TICKET_UPDATE_ID', NULL)->where('IS_ACTIVE','Y')->exists())
                    {
                        return '<i class="fas fa-link" style="color : green;" onClick="fetchAttachmentsAndAppendToModal('. $row->TICKET_ID .')"></i>';
                    }
                    else
                    {
                        return '';
                    }                   

                } else {
                    return '';
                }
            })
            ->addColumn('priority', function ($row) {
                if ($row->PRIORITY == 'High') {
                    return '<span class="badge bg-danger">' . $row->PRIORITY . '</span>';
                } elseif ($row->PRIORITY == 'Medium') {
                    return '<span class="badge bg-warning">' . $row->PRIORITY . '</span>';
                } else {
                    return '<span class="badge bg-success">' . $row->PRIORITY . '</span>';
                }
            })
            ->addColumn('CREATED_ON', function ($row) {                
                return date('d-M-Y h:i A',strtotime(optional($row)->CREATED_ON));
            })
            ->addColumn('ticketNumber', function ($row) {             
                $taskNo = '';
                if($row->TASK_NO != 0)
                {
                    $taskNo = '-'.$row->TASK_NO;
                }

                // Add star if FEEDBACK_POINT is not 0
                $star = '';
                if ($row->FEEDBACK_POINT != 0) {
                    $starPoint = '';
                    // Generate the stars
                    for ($i = 0; $i < $row->FEEDBACK_POINT; $i++) {
                        $starPoint .= '*';
                    }                    
                    // Tooltip with HTML (stars and remarks), use <br> for line breaks 
                    $feedbackTooltip = htmlspecialchars($starPoint) . ' ' . htmlspecialchars($row->FEEDBACK_REMARKS);
        
                    $star = '<i class="fa fa-star star-icon" style="color: #d38f1b;" data-toggle="tooltip" data-html="true" data-placement="top" title="' . $feedbackTooltip . '"></i>';
                    // $star = '<i class="fa fa-star star-icon" style="color: #d38f1b;" data-tooltip="true"></i>' . $customTooltip;
                }
                $isPrior = '';
                if ($row->PRIORITY == 'High') {
                    $isPrior = '<span style="color:red;font-size: 14px;">!</span>';
                }
                
                $color = '#343a40'; 

                $isSLA = $row->IS_SLA_BREACH;    

                if($row->STATUS == 'Open'){
                    $totalTimeConsumed = $this->getTimeLeft($row->TICKET_ID, $row->TICKET_NO);

                    $sla = $row->slaIndicator;
                    if($sla){
                        $isSLA = ($totalTimeConsumed > ($sla * 60)) ? 'Y' : 'N';                   
                    }
                }
                if($isSLA == 'Y'){
                    $color = 'red'; 
                }                                
                return '<a href="' . route('ticket.view', ['ticketId' => $row->TICKET_ID, 'ticketNumber' => $row->TICKET_NO]) . '" style="color: ' . $color . ';">' . $star .' '.$isPrior.' '. $row->TICKET_NO .''.$taskNo. '</a>';
            })
            ->rawColumns(['action', 'attachment', 'priority', 'ticketNumber'])
            ->make(true);
        }

        $taskType = DB::table('lkp_task_type')->where(['ACTIVE_FLAG' => 'Y'])->get();

        $userName = '';
        $userEmpId = '';
        if ((userRoleName() == 'User')) {
            $userName = Auth::user()->USER_NAME;
            $userEmpId = Auth::user()->EMPLOYEE_ID;
        }

        $frequencies = ['Once','Daily','Weekly','Monthly'];
        $weekdays = DB::table('lkp_weekdays')->get();
        $statuses = DB::table('lkp_status')->get();


        $progresses = DB::table('lkp_progress')
                        ->where('STATUS_CODE','Completed')
                        ->orderBy('PROGRESS','asc')
                        ->get();

        $openProgresses = DB::table('lkp_progress')
                        ->join('map_dept_progress', 'lkp_progress.PROGRESS_ID', '=', 'map_dept_progress.PROGRESS_ID')
                        ->where('map_dept_progress.DEPARTMENT_ID', session('code'))
                        ->where('STATUS_CODE','Open')
                        ->orderBy('PROGRESS','asc')
                        ->get();        

        return view('tickets',compact('taskType',
                                    'userName',
                                    'userEmpId',
                                    'permissions',
                                    'templates',
                                    'frequencies',
                                    'weekdays',
                                    'statuses',
                                    'progresses',
                                    'openProgresses'))
                        ->withTeams($response['data'])
                        ->withDepartmentName($departmentName);
    }
    public function storeTicket(Request $request)
    {
        try{  
            // Validate the request data
            $rules = [
                // 'employeeName'    => 'required',
                'teamName'        => 'required',
                'mode'            => 'required',
                'priority'        => 'required',
                'subject'         => 'required',
                // 'employeeId'    => 'required',
            ];
            $messages = [
                // 'employeeName.required' => 'Please Enter User Name',
                'teamName.required'     => 'Please Select Team Name',
                'mode.required'         => 'Please Select Mode',
                'priority.required'     => 'Please Select Priority',
                'subject.required'      => 'Please Enter Subject',
                // 'employeeId.required'   => 'Employee ID is required',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }


            $departmentCode = DB::table('department_details')
                                ->where('DEPARTMENT_ID', session('code'))
                                ->value('DEPARTMENT_CODE');

            $empname = preg_replace("/\s*\(.*?\)/", "", $request->employeeName);
            $empname = trim($empname);
                
            if ($request->frequency === 'Once' || $request->frequency === '') {
               $result= \DB::select("CALL generate_ticket_no(?, @batchCode)", [session('code')]);
   
                $result2 = \DB::select('SELECT @batchCode AS batchCode');
                if($result2 && isset($result2[0]->batchCode)) {
                    $serialNumber = $result2[0]->batchCode;                
                } 

                $subject = $request->subject;

                if ($request->add_subject_text) {
                    $subject .= ' [' . $request->add_subject_text . ']';
                }

                // Insert new ticket into the tickets table
                DB::transaction(function () use ($serialNumber, $request, $empname, $subject) {
                    $ticket = Ticket::create([
                        'TICKET_NO'       => $serialNumber,
                        'TASK_NO'         => 0,
                        'PROJECT_ID'      => session('code'),
                        'MODE'            => $request->mode,
                        'SUBJECT'         => $subject,
                        'DESCRIPTION'     => $request->description,
                        'PRIORITY'        => $request->priority,
                        'TEAM_NAME'       => $request->teamName,
                        'REQUESTED_BY'    => ($request->employeeId) ? $request->employeeId : $request->selectedEmployee,
                        'USER_NAME'       => $empname,
                        'USER_MAIL'       => $request->employeeMail,
                        'DEPARTMENT_CODE' => $request->code,
                        'DEPARTMENT_NAME' => $request->departmentName,
                        'CREATED_BY'      => Auth::user()->LOGIN_ID,
                    ]);  
                    
                    $selectedTasks = $request->input('tasks');
                    // $tasks = json_decode($selectedTasks, true);

                    if (is_array($selectedTasks) && !empty($selectedTasks)) {
                        foreach($selectedTasks  as $task)
                        {
                            $lastId = Ticket::where('LINKED_TO',$ticket->TICKET_ID)->latest('TICKET_ID')->value('TASK_NO');

                            // Increment the last ID
                            $lastId++;

                            $last3 = str_pad($lastId, 3, '0', STR_PAD_LEFT);

                            $taskSubject = $task;
                            if ($request->add_subject_text) {
                                $taskSubject .= ' [' . $request->add_subject_text . ']';
                            }

                            $pretask = new Ticket;

                            $pretask->TICKET_NO       = $ticket->TICKET_NO;
                            $pretask->TASK_NO         = $last3;
                            $pretask->PROJECT_ID      = $ticket->PROJECT_ID;
                            $pretask->LINKED_TO       = is_null($ticket->LINKED_TO) ? $ticket->TICKET_ID : $ticket->LINKED_TO;
                            $pretask->MODE            = $ticket->MODE;
                            $pretask->SUBJECT         = $taskSubject;
                            $pretask->PRIORITY        = $ticket->PRIORITY;
                            $pretask->TEAM_NAME       = $ticket->TEAM_NAME;
                            $pretask->REQUESTED_BY    = $ticket->REQUESTED_BY;
                            $pretask->USER_NAME       = $ticket->USER_NAME;
                            $pretask->USER_MAIL       = ($ticket->USER_MAIL) ? $ticket->USER_MAIL : null;
                            $pretask->DEPARTMENT_CODE = $ticket->DEPARTMENT_CODE;
                            $pretask->DEPARTMENT_NAME = $ticket->DEPARTMENT_NAME;
                            $pretask->CREATED_BY      = 'Ticketadmin';
                            $pretask->CREATED_ON      = now();

                            $pretask->save();
                        }                       
                    }


                    // Convert the filtered collection to a plain array
                    $attachmentsArray =  session('attachments') ?? [];

                    $iterationNumber = 0; // Initialize the iteration number

                    // Retrieve the TICKET_ID of the inserted ticket
                    $ticketId = $ticket->TICKET_ID;

                    if ($request->hasFile('attached_files')) {

                        foreach ($request->file('attached_files') as $file) {                
                            
                            // Generate a unique image name
                            $originalName = $file->getClientOriginalName();

                            $imageName = $serialNumber . '_' . $originalName;

                            // Insert attachment details into the ticket_attachment table
                            DB::table('ticket_attachment')->insert([
                                'TICKET_ID'  => $ticketId,
                                'ATTACHMENT' => $imageName
                            ]);

                            // Move the file to the attachments directory
                            $file->move(public_path("attachments"), $imageName);

                            // Increment the iteration number
                            $iterationNumber++;
                        }
                    }
                    createLogActivity('Log Ticket',$serialNumber,'TICKET NO',Auth::user()->LOGIN_ID);  

                    $this->apiResponse['successCode']  = 1;
                    $this->apiResponse['message']      = 'Successful';
                    $this->apiResponse['data']         = $ticket;
                });
                try{
                  
                    $mailFrom = DB::table('department_details')
                                ->where('DEPARTMENT_ID', session('code'))
                                ->first();

                    // Compose email subject
                    $subject = "Your request has been logged with Request ID ## {$serialNumber} ##";

                    // Compose email body
                    $body  = "<!DOCTYPE html> <html><body>  ";
                    $body .= "<p>Hare Krishna {$request->employeeName},</p>";
                    $body .= "<p>Thank you for contacting Service Desk. We acknowledge your request for: [{$request->subject}].<br><br>";
                    $body .= "The Request ID is {$serialNumber}. Please refer to this ID if you need to contact {$mailFrom->DEPARTMENT_NAME} for any clarifications.<br><br>";
                    $body .= "Our team member will revert on your request shortly.</p>";
                    $body .= "<p>Kind Regards,<br>{$mailFrom->DISPLAY_NAME} Team<br><br>";
                    $body .= "Note: This is an auto-generated email from our ticketing system.</p>"; 
                    $body .= "</body></html>";         
                    
                    $mailId = $request->employeeMail;
                    
                    if($mailId){
                        config([
                            'mail.mailers.smtp.host' => $mailFrom->MAIL_HOST,
                            'mail.mailers.smtp.port' => 587,
                            'mail.mailers.smtp.encryption' => 'tls',
                            'mail.mailers.smtp.username' => $mailFrom->MAIL_USERNAME,
                            'mail.mailers.smtp.password' => $mailFrom->MAIL_PASSWORD,
                            'mail.from.address' => $mailFrom->SUPPORT_EMAIL_ID,
                            'mail.from.name' => $mailFrom->DISPLAY_NAME,
                        ]);    
                       
                        Mail::html($body, function ($message) use ($subject, $mailId, $mailFrom) {
                            $message->from($mailFrom->SUPPORT_EMAIL_ID, $mailFrom->DISPLAY_NAME)
                            ->to($mailId)->subject($subject);                   
                        });                
                    }
                }
                catch(Exception $e){
                }                   
            }     
            else{

                $recurringTicket = RecurringTicket::create([
                    'PROJECT_ID'      => session('code'),
                    'SUBJECT'         => $request->subject,
                    'DESCRIPTION'     => $request->description,
                    'PRIORITY'        => $request->priority,
                    'TEAM_NAME'       => $request->teamName,
                    'REQUESTED_BY'    => ($request->employeeId) ? $request->employeeId : $request->selectedEmployee,
                    'USER_NAME'       => $empname,
                    'USER_MAIL'       => $request->employeeMail,
                    'DEPARTMENT_CODE' => $request->code,
                    'DEPARTMENT_NAME' => $request->departmentName,
                    'FREQUENCY'       => $request->frequency,
                    'WEEKDAY'         => $request->weekday,
                    'START_DATE'      => ($request->start_date) ? date('Y-m-d H:i:s', strtotime($request->start_date)) : null,
                    'RECURRING_TILL'  => ($request->recurring_till) ? date('Y-m-d H:i:s', strtotime($request->recurring_till)) : date('Y-m-d'),
                    'CREATED_BY'      => Auth::user()->LOGIN_ID,
                    'CREATED_ON'      => now()
                ]);

                if($recurringTicket){
                    $this->apiResponse['successCode']  = 1;
                    $this->apiResponse['message']      = 'Successfully Scheduled';
                    $this->apiResponse['data']         = $recurringTicket;
                }
                else{
                    $this->apiResponse['successCode']  = 0;
                    $this->apiResponse['message']      = 'Failed to Schedule !!';
                    $this->apiResponse['data']         = [];
                }

                $iterationNumber = 0; // Initialize the iteration number

                // Retrieve the TICKET_ID of the inserted ticket
                $recurringTicketId = $recurringTicket->RECURRING_ID;

                if ($request->hasFile('attached_files')) {
                    foreach($request->attached_files as $file) {                    
                        // Generate a unique image name
                        $originalName = $file->getClientOriginalName();

                        $imageName = $recurringTicketId . '_' . $originalName;

                        // Insert attachment details into the ticket_attachment table
                        DB::table('recurring_tickets_attachment')->insert([
                            'RECURRING_ID'  => $recurringTicketId,
                            'ATTACHMENT' => $imageName
                        ]);

                        // Move the file to the attachments directory
                        $file->move(public_path("attachments"), $imageName);

                        // Increment the iteration number
                        $iterationNumber++;
                    }
                }
                
                $today = now();

                if($request->frequency === 'Daily' || 
                    ($request->frequency === 'Weekly' && $request->weekday === strtoUpper($today->format('l'))) || 
                    ($request->frequency === 'Monthly' && date('Y-m-d',strtotime($request->start_date)) === date('Y-m-d'))){

                    $result= \DB::select("CALL generate_ticket_no(?, @batchCode)", [session('code')]);
   
                    $result2 = \DB::select('SELECT @batchCode AS batchCode');
                    if($result2 && isset($result2[0]->batchCode)) {
                        $serialNumber = $result2[0]->batchCode;                
                    } 
                    
                    // Insert new ticket into the tickets table
                    DB::transaction(function () use ($serialNumber, $request, $empname) {
                        $ticket = Ticket::create([
                            'TICKET_NO'       => $serialNumber,
                            'TASK_NO'         => 0,
                            'PROJECT_ID'      => session('code'),
                            'MODE'            => $request->mode,
                            'SUBJECT'         => $request->subject,
                            'DESCRIPTION'     => $request->description,
                            'PRIORITY'        => $request->priority,
                            'TEAM_NAME'       => $request->teamName,
                            'REQUESTED_BY'    => ($request->employeeId) ? $request->employeeId : $request->selectedEmployee,
                            'USER_NAME'       => $empname,
                            'USER_MAIL'       => $request->employeeMail,
                            'DEPARTMENT_CODE' => $request->code,
                            'DEPARTMENT_NAME' => $request->departmentName,
                            'CREATED_BY'      => Auth::user()->LOGIN_ID,
                        ]); 
                        
                        $iterationNumber = 0; // Initialize the iteration number

                        // Retrieve the TICKET_ID of the inserted ticket
                        $ticketId = $ticket->TICKET_ID;
                        // $ticketId = "160195";

                        if ($request->hasFile('attached_files')) {
                            
                            foreach($request->attached_files as $file) { 
                                               
                                // Generate a unique image name
                                $originalName = $file->getClientOriginalName();
                                
                                $imageName = $serialNumber . '_' . $originalName;
                                
                                // Insert attachment details into the ticket_attachment table
                                DB::table('ticket_attachment')->insert([
                                    'TICKET_ID'  => $ticketId,
                                    'ATTACHMENT' => $imageName
                                ]);
                                
                                // Move the file to the attachments directory
                                // $file->move(public_path("attachments"), $imageName);

                                $filePath = public_path("attachments") . '/' . $imageName;

                                file_put_contents($filePath, $imageName);
                               
                                // Increment the iteration number
                                $iterationNumber++;
                            }
                        }  
                    });     
                        
                    createLogActivity('Log Ticket',$serialNumber,'TICKET NO',Auth::user()->LOGIN_ID); 

                    if($ticket){
                        $this->apiResponse['successCode']  = 1;
                        $this->apiResponse['message']      = 'Successfully Created';
                        $this->apiResponse['data']         = $ticket;
                    }
                    else{
                        $this->apiResponse['successCode']  = 0;
                        $this->apiResponse['message']      = 'Failed to Create !!';
                        $this->apiResponse['data']         = [];
                    }                        
                                     
                }                               
            }            
                       
            return response()->json($this->apiResponse);
        }
        catch (\Throwable $e) {
            $this->apiResponse['successCode']  = 0;
            $this->apiResponse['message']      = 'Error Please try again !!';
            // $this->apiResponse['error']      = $e->getMessage();
            $this->apiResponse['data']         = [];

            return response()->json($this->apiResponse);
        }
    }
    public function updateTicket(Request $request)
    {
        $empname = preg_replace('/\s*\(.*?\)/', '', $request->employeeName);          
        $empname = trim($empname);

       // Update the ticket details in the tickets table
        DB::table('ticket')
            ->where('TICKET_ID', $request->ticketId)
            ->update([
                'MODE'            => $request->mode,
                'SUBJECT'         => $request->subject,
                'DESCRIPTION'     => $request->description,
                'PRIORITY'        => $request->priority,
                'TEAM_NAME'       => $request->teamName,
                'REQUESTED_BY'    => $request->filled('employeeId') ? $request->employeeId : DB::raw('REQUESTED_BY'),
                'USER_NAME'       => $request->filled('employeeName') ? $empname : DB::raw('USER_NAME'),
                'DEPARTMENT_CODE' => $request->code,
                'DEPARTMENT_NAME' => $request->departmentName,
                'MODIFIED_BY'     => $request->userId,
                'MODIFIED_ON'     => now(),
            ]);

        // Get the updated ticket number
        $ticketNumber = DB::table('ticket')
                ->where('TICKET_ID', $request->ticketId)
                ->value('TICKET_NO');

        $iterationNumber = DB::table('ticket_attachment')
                                    ->where('TICKET_ID', $request->ticketId)
                                    ->count(); // Initialize the iteration number

        // Retrieve the TICKET_ID of the inserted ticket
        $ticketId = $request->ticketId;

        // Ensure that $request->attached_files_update is an array and not null
        if ($request->hasFile('attached_files_update')) {

            foreach($request->attached_files_update as $file) {
                // Generate a unique image name
                $originalName = $file->getClientOriginalName();
                $imageName = $ticketNumber . '_' . uniqid() . '_' . $originalName;

                // Insert attachment details into the ticket_attachment table
                DB::table('ticket_attachment')->insert([
                    'TICKET_ID' => $ticketId,
                    'ATTACHMENT' => $imageName,
                ]);

                // Move the file to the attachments directory
                $file->move(public_path("attachments"), $imageName);

                // Increment the iteration number
                $iterationNumber++;
            }
        }

        modifyLogActivity('Update Ticket',$request->ticketId,'TICKET ID',Auth::user()->LOGIN_ID);

        return response(['successCode' => 1],200);

    }
    public function assignTicket(Request $request)
    {
        $api = new TicketsApi;

        $response = $api->assignTicket($request->all());

        return response($response,200);
    }
    public function assignSelfTicket(Request $request)
    {
        $api = new TicketsApi;

        $response = $api->assignSelfTicket($request->all());

        return response($response,200);
    }
    public function getTicketType(Request $request)
    {
        // $getTicketType=DB::table('lkp_task_type')
        //                 ->select('TASK_TYPE_ID','DISPLAY_NAME')
        //                 ->where(['TEAM_ID'=>$request->teamName,'ACTIVE_FLAG' => 'Y'])
        //                 ->get();
        // $html='<option value="">Please Select</option>';
        // foreach($getTicketType as $val)
        // {
        //     $html.='<option value="'.$val->TASK_TYPE_ID.'">'.$val->DISPLAY_NAME.'</option>';
        // }
        // echo $html;

        $teamId = $request->input('teamName');
        
        $tickets = DB::table('lkp_task_type')                        
                        ->where(['TEAM_ID'=>$teamId,'ACTIVE_FLAG' => 'Y'])
                        ->select('TASK_TYPE_ID','DISPLAY_NAME')
                        ->get();   
                
        return response()->json($tickets);  
    }
    public function storeImage(Request $request)
    {
        $file = $request->file('attached_files');
        
        $fileName = uniqid() . '_' . $file->getClientOriginalName();

        // Store additional file information in the session
        $attachmentInfo = [
            'id' => uniqid(), // Generate a unique ID for each file
            'name' => $fileName,
            // Add more details as needed
        ];

        $request->session()->push('attachments', $attachmentInfo);

        // Move the file to the public path
        $file->move(public_path('/temp-docs'), $fileName);

        return response()->json($fileName);
    }
    public function deleteImage(Request $request)
    {
        $nameToRemove = request('uniqueFileId');

        // Retrieve the array of file information from the session
        $attachments = $request->session()->get('attachments', []);

        $attachments = array_filter($attachments, function ($attachment) use ($nameToRemove) {
            return $attachment['name'] !== $nameToRemove;
        });
        
        $filePath = "temp-docs/{$nameToRemove}";
        File::delete(public_path($filePath));

        $request->session()->put('attachments', $attachments);

        return response()->json(['message' => 'File deleted successfully']);
        
    }
    public function storeImageUpdate(Request $request)
    {
        $file = $request->file('attached_files_update');
        
        $fileName = uniqid() . '_' . $file->getClientOriginalName();

        // Store additional file information in the session
        $attachmentInfo = [
            'id' => uniqid(), // Generate a unique ID for each file
            'name' => $fileName,
            // Add more details as needed
        ];

        $request->session()->push('update-attachments', $attachmentInfo);

        // Move the file to the public path
        $file->move(public_path('/temp-docs/update'), $fileName);

        return response()->json($fileName);
    }
    public function deleteImageUpdate(Request $request)
    {
        $nameToRemove = request('uniqueFileId');

        // Retrieve the array of file information from the session
        $attachments = $request->session()->get('update-attachments', []);

        $attachments = array_filter($attachments, function ($attachment) use ($nameToRemove) {
            return $attachment['name'] !== $nameToRemove;
        });
        
        $filePath = "temp-docs/update/{$nameToRemove}";
        File::delete(public_path($filePath));

        $request->session()->put('attachments', $attachments);

        return response()->json(['message' => 'File deleted successfully']);        
    }

    public function getEmployees(Request $request)
    {
        $api = new HrApi;

        if(!$request->filled('emp_name'))
        {
            $request->merge(['emp_name' => '']);
        }
        
        $data = $api->getEmployees($request->all());

        return response()->json($data['data']);        
    }
    public function getEmployeeDetails(Request $request)
    {
        $api = new HrApi;
        
        $data = $api->getEmployeeDetails($request->employeeId);

        return response()->json($data['data']);        
    }
    public function getTicket(Request $request)
    {
        $api = new TicketsApi;

        $data = $api->getTicket($request->all());

        return response()->json($data);
    }
    public function viewTicket(Request $request)
    {
        $api = new TicketsApi;

        $data = $api->getTicket(['ticketId' => request('ticketId')]);

        $response = $api->getTeams();

        $hasActiveAttachments = DB::table('ticket_attachment')
                            ->where('TICKET_ID', $request->ticketId)
                            ->where('IS_ACTIVE', 'Y')
                            ->exists();
                            
        $logUpdates = DB::table('log_ticket_movement')
                            ->where('TICKET_ID', request('ticketId'))
                            ->orderBy('ALLOCATED_ON', 'desc')
                            ->get();

        $logUpdates->each(function($logUpdate){
            $logUpdate->ALLOCATED_TO = DB::table('mstr_users')->where('EMPLOYEE_ID', $logUpdate->ALLOCATED_TO)->pluck('USER_NAME')->first();
            $logUpdate->ALLOCATED_ON = Carbon::parse($logUpdate->ALLOCATED_ON)->format('d-m-Y g:i A');
        });


        $holidayList = HolidayList::pluck('HOLIDAY')->toArray();
        
        return view('ticket_details',compact('holidayList','logUpdates'))
                ->withTeams($response['data'])
               ->withData($data['data'][0]??[])
               ->withTasks($data['data']['subtasks']??[])
               ->with('hasActiveAttachments', $hasActiveAttachments);
    }
    public function addTask(Request $request)
    {
        $api = new TicketsApi;

        $data = $api->addTask($request->all());

        return response()->json($data);
    }
    public function getTasks(Request $request)
    {
        $api = new TicketsApi;

        $data = $api->getTasks($request->all());

        if ($request->ajax()) {
            return Datatables::of($data['data'])->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<div class="d-flex justify-content-between">
                                <button class="btn tickets-action-btn-transparent" onClick="edit('. $row['ticketId'] .')" title="Edit">
                                    <img src="'.asset('public/img/icons/edit-btn.png').'" alt="Edit Task" height="24">
                                </button>
                            </div>';
                    return $btn;
                })
                ->addColumn('ticketNumber', function ($row) {
                   
                    return '<a href="' . route('ticket.view', ['ticketId' => $row['ticketId'], 'ticketNumber' => $row['ticketNumber']]) . '" class="text-dark">' . $row['ticketNumber'] .'</a>';
                })
                ->rawColumns(['action','ticketNumber'])
                ->make(true);
        }    

        return response()->json($data);
    }
    public function getAttachments(Request $request)
    {
        $api = new TicketsApi;

        $request->merge(['ticketId' => request('ticketId')]);

        $data = $api->getAttachments($request->all());

        return response()->json($data);
    }
    public function getAllAttachments($ticketId, $ticketNumber, $attachmentTicketId)
    {
        try {
            $data = [];            

            $attachments = DB::table('ticket_attachment')
                                ->where('TICKET_ID', $ticketId)
                                ->whereNull('TICKET_UPDATE_ID') 
                                ->where('IS_ACTIVE','Y')
                                ->get();

            // print_r("hi");exit;
            foreach ($attachments as $key => $value) {
                $data[$key] = [
                    'attachmentId'    => optional($value)->ATTACHMENT_ID,
                    'attachment'      => optional($value)->ATTACHMENT,
                ];
            }

            $this->apiResponse['successCode']  = 1;
            $this->apiResponse['message']      = 'Successful';
            $this->apiResponse['data']         = $data;

            return response()->json($this->apiResponse);            
        }  
        catch (\Exception $e) {
            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error ! Please Try Again';
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }
    public function removeAttachment(Request $request)
    {
        $api = new TicketsApi;

        $request->merge(['attachmentId' => request('attachmentId')]);

        $data = $api->removeAttachment($request->all());

        return response()->json($data);
    }
    public function getDepartments()
    {
        $api = new HrApi;

        $data = $api->getDepartmentDetails();

        return response()->json($data['data']);
    }
    public function myTickets(Request $request)
    {
        $api = new TicketsApi;
      
        session()->put('code', request('id'));

        if($request->status)
        {
            $status = $request->status;
            $wthoutSts = '';
        }
        else{
            $status = '';
            $wthoutSts = 'Yes';
        }

        $tickets = DB::table('ticket')
        ->leftJoin('mstr_users', 'ticket.TECHNICIAN_ID', '=', 'mstr_users.EMPLOYEE_ID')
        // ->join('team', 'team.TEAM_NAME', '=', 'ticket.TEAM_NAME')
        // ->join('map_user_team', 'map_user_team.TEAM_ID', '=', 'team.TEAM_ID')            
        // ->where('map_user_team.USER_ID', Auth::id())
        ->where('PROJECT_ID', request('id'))
        ->where('TECHNICIAN_ID', auth()->user()->EMPLOYEE_ID)
		// ->where('STATUS', 'Open')

        ->when($status, function ($query) use ($status) {
                return $query->whereIn('ticket.STATUS', $status);
                })  
        ->when($wthoutSts, function ($query) use ($wthoutSts) {
            return $query->where('STATUS', 'Open');
            })

        ->when(request('ticketNo'),      fn ($query) => $query->where('ticket.TICKET_NO', request('ticketNo')))
        ->when(request('userName'),      fn ($query) => $query->where('ticket.REQUESTED_BY', request('userName')))
        ->when(request('subject'),       fn ($query) => $query->where('ticket.SUBJECT', 'like', '%' . request('subject') . '%'))
        ->when(request('description'),  fn ($query) => $query->where('ticket.DESCRIPTION', 'like', '%' . request('description') . '%'))
        ->when(request('department'),    fn ($query) => $query->where('ticket.DEPARTMENT_CODE', request('department')))
        ->when(request('mode'),          fn ($query) => $query->where('ticket.MODE', request('mode')))
        ->when(request('asset'),         fn ($query) => $query->where('ticket.ASSET_ID', request('asset')))
        ->when(request('teamId'),        fn ($query) => $query->whereIn('ticket.TEAM_NAME', request('teamId')))
        ->when(request('requestedFrom'), fn ($query) => $query->whereDate('ticket.CREATED_ON', '>=', date('Y-m-d', strtotime(request('requestedFrom')))))
        ->when(request('technician'),     fn ($query) => $query->where('ticket.TECHNICIAN_ID', request('technician')))
        ->when(request('requestedTo'),    fn ($query) => $query->whereDate('ticket.CREATED_ON', '<=', date('Y-m-d', strtotime(request('requestedTo')))))
        ->when(request('category'),      fn ($query) => $query->where('ticket.CATEGORY_ID', request('category')))
        ->when(request('item'),          fn ($query) => $query->where('ticket.ITEM_ID', request('item')))
        ->when(request('subcategory'),   fn ($query) => $query->where('ticket.SUB_CATEGORY_ID', request('subcategory')))
        ->when(request('itemType'),      fn ($query) => $query->where('ticket.ITEM_TYPE_ID', request('itemType')))
        // ->when(request('status'),        fn ($query) => $query->whereIn('ticket.STATUS', request('status')))
        ->when(request('progress'),      fn ($query) => $query->whereIn('ticket.PROGRESS', request('progress')))         
        ->when(request('createdBy'),     fn ($query) => $query->where('ticket.CREATED_BY', 'like', '%' .request('createdBy'). '%'))

        ->orderByRaw("CASE
               WHEN ticket.PROGRESS = 'Open' THEN 1
               WHEN ticket.PROGRESS = 'In Progress' THEN 2
               WHEN ticket.PROGRESS = 'On Hold' THEN 3
                ELSE 4
            END")
        ->orderBy('TICKET_ID', 'DESC')
        ->select('ticket.*', 'mstr_users.USER_NAME as TECHNICIAN_NAME')
        ->get();
    
       if ($request->ajax()) {
        return Datatables::of($tickets)->addIndexColumn()
            ->addColumn('action', function ($row) {

                $disabled = (!is_null($row->CLOSURE_CODE)) ? 'disabled' : '';

                 $btn = '<div class="btn-group">';

                if ($disabled != 'disabled') {

                    $btn .= '<button class="btn tickets-action-btn-transparent ml-1" onClick="statusUpdate('. $row->TICKET_ID .', \''. $row->TICKET_NO .'\', \''.$row->STATUS.'\')" title="Edit">
                    <img src="'.asset('public/img/icons/edit-btn.png') .'" alt="" height="20">
                    </button>
                    <button class="btn tickets-action-btn-transparent ml-1" onClick="categorize('. $row->TICKET_ID .')" title="Categorize">
                        <img src="'.asset('public/img/icons/categorize.png') .'" alt="" height="20">
                    </button>';

                    $btn .= '<button class="btn tickets-action-btn-transparent ml-1" onClick="closeTask('. $row->TICKET_ID .', \''. $row->TICKET_NO .'\')" title="Close Task">
                                <img src="' .asset('public/img/icons/deactivate.png').'" alt="" height="20">
                            </button>
                            <button class="btn tickets-action-btn-transparent ml-1" onClick="releaseTicket('. $row->TICKET_ID .', \''. $row->TICKET_NO .'\', \''.$row->STATUS.'\')" title="Release Ticket">
                                <img src="'.asset('public/img/icons/release.png') .'" alt="" height="20">
                            </button>'; 
                            
                } else {
    
                    $btn .= '<button class="btn tickets-action-btn-transparent ml-1" onClick="reopenTask('. $row->TICKET_ID .', \''. $row->TICKET_NO .'\')" title="Reopen Task">
                                <i class="fas fa-undo-alt" style="color:green;"></i>
                            </button>';
                }

                $btn .= '</div>';

                return $btn;
            })
            ->addColumn('attachment', function ($row) {
                // return '<i class="fas fa-link" style="color : green;" onClick="fetchAttachmentsAndAppendToModal('. $row->TICKET_ID .')"></i>';
                    if(DB::table('ticket_attachment')->where('TICKET_ID',$row->TICKET_ID)->where('IS_ACTIVE','Y')->exists())
                    {
                        return '<i class="fas fa-link" style="color : green;" onClick="fetchAttachmentsAndAppendToModal('. $row->TICKET_ID .')"></i>';
                    }
                    else
                    {
                        return '';
                    }  
            })
            ->addColumn('priority', function ($row) {
                if ($row->PRIORITY == 'High') {
                    return '<span class="badge bg-danger">' . $row->PRIORITY . '</span>';
                } elseif ($row->PRIORITY == 'Medium') {
                    return '<span class="badge bg-warning">' . $row->PRIORITY . '</span>';
                } else {
                    return '<span class="badge bg-success">' . $row->PRIORITY . '</span>';
                }
            })
            ->addColumn('CREATED_ON', function ($row) {
                
                return date('d-M-Y h:i A',strtotime(optional($row)->CREATED_ON));
            })
            ->addColumn('ticketNumber', function ($row) {
                $taskNo = '';

                if($row->TASK_NO != 0)
                {
                    $taskNo = '-'.$row->TASK_NO;
                }

                return '<a href="' . route('ticket.view', ['ticketId' => $row->TICKET_ID, 'ticketNumber' => $row->TICKET_NO]) . '" class="text-dark">' . $row->TICKET_NO .''.$taskNo. '</a>';
            })
            ->rawColumns(['action', 'attachment', 'priority', 'ticketNumber'])
            ->make(true);
        }

        return view('tickets');
    }
    public function allTickets(Request $request)
    {        
        session()->put('code', request('id'));

		if($request->status || $request->excelStatus || $request->excelFromDate)
        {
            $status = $request->status;
            $wthoutSts = '';

            $excelStatus = $request->excelStatus;
            $wthoutExcelSts =' ';
        }
        else{
            $status = '';
            $wthoutSts = 'Yes';

            $excelStatus = '';
            $wthoutExcelSts = 'Yes';
        }

        if ((userRoleName() == 'User')) {
            $userEmployeeId = Auth::user()->EMPLOYEE_ID;
            $withOutEmpId = '';
        }
        else {
            $userEmployeeId = '';
            $withOutEmpId = 'Yes';
        }
        
        $admin = (userRoleName() == 'Admin') ? 'Yes' : '';

        $start = $request->start;
        $length = $request->length;
        $ticketquery=DB::table('ticket');
        if (!empty($request->order)) {
            $orderColumnIndex = $request->order[0]['column']; // Column index
            $orderDirection = $request->order[0]['dir']; // 'asc' or 'desc'

            // Get column name based on the index
            $columns = $request->columns;
            $orderColumnName = $columns[$orderColumnIndex]['data'];
            
            if($orderColumnName =='ticketNumber')
            {
                $orderColumnName = 'ticket.TICKET_NO';
            }
            else if($orderColumnName == 'SUBJECT')
            {
                $orderColumnName = 'ticket.SUBJECT';
            }
            else if($orderColumnName == 'USER_NAME')
            {
                $orderColumnName = 'ticket.USER_NAME';
            }
            else if($orderColumnName == 'CREATED_ON')
            {
                $orderColumnName = 'ticket.CREATED_ON';
            }
            else if($orderColumnName == 'DEPARTMENT_NAME')
            {
                $orderColumnName = 'ticket.DEPARTMENT_NAME';
            }
            else if($orderColumnName == 'TECHNICIAN_NAME')
            {
                $orderColumnName = 'mstr_users.USER_NAME';
            }
            else if($orderColumnName == 'CREATED_BY')
            {
                $orderColumnName = 'ticket.CREATED_BY';
            }
            else if($orderColumnName == 'PROGRESS')
            {
                $orderColumnName = 'ticket.PROGRESS';
            }
            else if($orderColumnName == 'TEAM_NAME')
            {
                $orderColumnName = 'ticket.TEAM_NAME';
            }                
            else if($orderColumnName == 'action')
            {
                $orderColumnName = 'ticket.CREATED_ON';
            }
            else if($orderColumnName == 'ASSIGNED_ON')
            {
                $orderColumnName = 'ticket.ASSIGNED_ON';
            }
            else if($orderColumnName == 'CLOSED_ON')
            {
                $orderColumnName = 'ticket.CLOSED_ON';
            }
            else if($orderColumnName == 'AGE')
            {
                $orderColumnName = 'ticket.CLOSED_ON';
            }
            else if($orderColumnName == 'POINTS')
            {
                $orderColumnName = 'ticket.POINTS';
            }
            else{
                $orderColumnName = 'ticket.TICKET_NO';
            }
            // Apply order to the query
            $ticketquery->orderBy($orderColumnName, $orderDirection);
        }else{
                $ticketquery->orderByRaw("CASE
            WHEN ticket.PROGRESS = 'New' THEN 1
            WHEN ticket.PROGRESS = 'Open' THEN 2
            WHEN ticket.PROGRESS = 'In Progress' THEN 3
            WHEN ticket.PROGRESS = 'On Hold' THEN 4
            ELSE 5                
            END");
        }

        $tickets = $ticketquery
            ->leftJoin('mstr_users', 'ticket.TECHNICIAN_ID', '=', 'mstr_users.EMPLOYEE_ID')
            ->leftJoin('lkp_category','lkp_category.CATEGORY_ID','=','ticket.CATEGORY_ID')
            ->leftJoin('lkp_sub_category','lkp_sub_category.SUB_CATEGORY_ID','=','ticket.SUB_CATEGORY_ID')
            ->leftJoin('lkp_item_type','lkp_item_type.ITEM_TYPE_ID','=','ticket.ITEM_TYPE_ID')
            ->leftJoin('lkp_item','lkp_item.ITEM_ID','=','ticket.ITEM_ID')
            ->leftJoin('lkp_task_type','lkp_task_type.TASK_TYPE_ID','=','ticket.TASK_TYPE_ID')
            //  ->leftJoin('ticket_updates','ticket_updates.TICKET_ID','=','ticket.TICKET_ID')
            ->leftJoin('ticket_updates', function ($join) {
                $join->on('ticket_updates.TICKET_ID', '=', 'ticket.TICKET_ID')
                    ->whereRaw('ticket_updates.LOG_DATE = (SELECT MAX(tu.LOG_DATE) FROM ticket_updates tu WHERE tu.TICKET_ID = ticket.TICKET_ID)');
            })

            ->leftJoin('team', 'team.TEAM_NAME', '=', 'ticket.TEAM_NAME')
            ->leftJoin('map_user_team', 'map_user_team.TEAM_ID', '=', 'team.TEAM_ID')            
            ->where('PROJECT_ID', request('id'))
            ->when($status, function ($query) use ($status) {
                return $query->whereIn('ticket.STATUS', $status);
                })  
            ->when($wthoutSts, function ($query) use ($wthoutSts) {
                return $query->whereIn('ticket.STATUS', ['New','Open','Completed'])->where('IS_CLOSED', 'N');
                })
            ->when($excelStatus, function ($query) use ($excelStatus) {
                return $query->whereIn('ticket.STATUS', $excelStatus);
                })  
            ->when($admin == 'Yes', function ($query) {
                    return $query;
                }, function ($query) {
                    return $query->where('map_user_team.USER_ID', Auth::id());
                })
            ->when(request('ticketNo'),      fn ($query) => $query->where('ticket.TICKET_NO', request('ticketNo')))
            ->when(request('userName'),      fn ($query) => $query->where('ticket.REQUESTED_BY', request('userName')))
            ->when(request('subject'),       fn ($query) => $query->where('ticket.SUBJECT', 'like', '%' . request('subject') . '%'))
            ->when(request('description'),   fn ($query) => $query->where('ticket.DESCRIPTION', 'like', '%' . request('description') . '%'))
            ->when(request('department'),    fn ($query) => $query->where('ticket.DEPARTMENT_CODE', request('department')))
            ->when(request('mode'),          fn ($query) => $query->where('ticket.MODE', request('mode')))
            ->when(request('asset'),         fn ($query) => $query->where('ticket.ASSET_ID', request('asset')))
            ->when(request('teamId'),        fn ($query) => $query->whereIn('ticket.TEAM_NAME', request('teamId')))
            ->when(request('requestedFrom'), fn ($query) => $query->whereDate('ticket.CREATED_ON', '>=', date('Y-m-d', strtotime(request('requestedFrom')))))
            ->when(request('technician'),     fn ($query) => $query->where('ticket.TECHNICIAN_ID', request('technician')))
            ->when(request('requestedTo'),    fn ($query) => $query->whereDate('ticket.CREATED_ON', '<=', date('Y-m-d', strtotime(request('requestedTo')))))
            ->when(request('category'),      fn ($query) => $query->where('ticket.CATEGORY_ID', request('category')))
            ->when(request('item'),          fn ($query) => $query->where('ticket.ITEM_ID', request('item')))
            ->when(request('subcategory'),   fn ($query) => $query->where('ticket.SUB_CATEGORY_ID', request('subcategory')))
            ->when(request('itemType'),      fn ($query) => $query->where('ticket.ITEM_TYPE_ID', request('itemType')))
        // ->when(request('status'),        fn ($query) => $query->whereIn('ticket.STATUS', request('status')))
            ->when(request('progress'),      fn ($query) => $query->whereIn('ticket.PROGRESS', request('progress'))) 
            ->when(request('subject'),       fn ($query) => $query->where('ticket.SUBJECT', 'like', '%' . request('subject') . '%'))      
            ->when(request('createdBy'),     fn ($query) => $query->where('ticket.CREATED_BY', 'like', '%' .request('createdBy'). '%'))
                            
            ->when(request('excelFromDate'), fn ($query) => $query->whereDate('ticket.CREATED_ON','>=',date('Y-m-d',strtotime(request('excelFromDate')))))
            ->when(request('excelToDate'),   fn ($query) => $query->whereDate('ticket.CREATED_ON','<=',date('Y-m-d',strtotime(request('excelToDate')))))
            // ->when(request('excelStatus'),   fn ($query) => $query->whereIn('ticket.STATUS', request('excelStatus')))
            ->when(request('excelProgress'), fn ($query) => $query->whereIn('ticket.PROGRESS', request('excelProgress')))

            ->orderByRaw("CASE
                    WHEN ticket.PROGRESS = 'New' THEN 1
                    WHEN ticket.PROGRESS = 'Open' THEN 2
                    WHEN ticket.PROGRESS = 'In Progress' THEN 3
                    WHEN ticket.PROGRESS = 'On Hold' THEN 4
                    ELSE 5                
                    END")        
            ->orderBy('ticket.CREATED_ON', 'DESC')
            ->select('ticket.*', 'mstr_users.USER_NAME as TECHNICIAN_NAME',
                'lkp_category.DISPLAY_NAME as categoryName',
                'lkp_sub_category.DISPLAY_NAME as subCatName',
                'lkp_item_type.DISPLAY_NAME as itemTypeName',
                'lkp_item.DISPLAY_NAME as itemName',
                'lkp_task_type.DISPLAY_NAME as ticketType',
                'ticket_updates.DESCRIPTION as lastWorkUpdates',
                'lkp_task_type.SLA as sla',
                'ticket.IS_SLA_BREACH as isSlaBreach',)
            ->groupBy('ticket.TICKET_ID', 'mstr_users.USER_NAME', 
                'lkp_category.DISPLAY_NAME', 
                'lkp_sub_category.DISPLAY_NAME', 
                'lkp_item_type.DISPLAY_NAME', 
                'lkp_item.DISPLAY_NAME', 
                'lkp_task_type.DISPLAY_NAME',
                'lkp_task_type.SLA',
                'ticket_updates.DESCRIPTION',  
                'ticket.IS_SLA_BREACH'              
            );
            // ->get();
   
    
       if ($request->ajax()) {
        return Datatables::of($tickets)->addIndexColumn()
            ->addColumn('action', function ($row) {

                $disabled = (!is_null($row->CLOSURE_CODE)) ? 'disabled' : '';
                
                $btn = '<div class="btn-group">';
                if (!(userRoleName() == 'User')) {
                    if ($disabled != 'disabled') {

                        $btn .= '<button class="btn tickets-action-btn-transparent ml-1" onClick="statusUpdate('. $row->TICKET_ID .',\''. $row->TICKET_NO .'\' ,\''. $row->STATUS .'\')" title="Edit">
                        <img src="'.asset('public/img/icons/edit-btn.png') .'" alt="" height="20">
                        </button>
                        <button class="btn tickets-action-btn-transparent ml-1" onClick="categorize('. $row->TICKET_ID .')" title="Categorize">
                            <img src="'.asset('public/img/icons/categorize.png') .'" alt="" height="20">
                        </button>';

                        if(!$row->TECHNICIAN_ID){
                            $btn .= '<button class="btn tickets-action-btn-transparent  ml-1" onClick="cancelTask('. $row->TICKET_ID .', \''. $row->TICKET_NO .'\')" title="Cancel Task">
                            <img src="' .asset('public/img/icons/deactivate.png').'" alt="" height="20">
                            </button>';
                        }   
                        else{                            
                            if ($row->PROGRESS != 'On Hold')
                            {
                                $btn .= '<button class="btn tickets-action-btn-transparent  ml-1" onClick="closeTask('. $row->TICKET_ID .', \''. $row->TICKET_NO .'\')" title="Close Task" >
                                    <img src="' .asset('public/img/icons/deactivate.png').'" alt="" height="20">
                                </button>';
                            }
                            else
                            {
                                $btn .= '<button class="btn tickets-action-btn-transparent  ml-1" onClick="closeTask('. $row->TICKET_ID .', \''. $row->TICKET_NO .'\')" title="Close Task">
                                    <img src="' .asset('public/img/icons/deactivate.png').'" alt="" height="20">
                                </button>';
                            } 
                            $btn .='<button class="btn tickets-action-btn-transparent ml-1" onClick="releaseTicket('. $row->TICKET_ID .', \''. $row->TICKET_NO .'\', \''.$row->STATUS.'\')" title="Release Ticket">
                                <img src="'.asset('public/img/icons/release.png') .'" alt="" height="20">
                            </button>';
                        }                                            
                    
                    } else {    

                        $btn .= '<button class="btn tickets-action-btn-transparent ml-1" onClick="reopenTask('. $row->TICKET_ID .', \''. $row->TICKET_NO .'\')" title="Reopen Task">
                                    <i class="fas fa-undo-alt" style="color:green;"></i>
                                </button>';
                    }
                }
                else{
                    $btn .= '';
                }

                $btn .= '</div>';

                return $btn;
            })
            ->addColumn('attachment', function ($row) {
                // return '<i class="fas fa-link" style="color : green;" onClick="fetchAttachmentsAndAppendToModal('. $row->TICKET_ID .')"></i>';
                    if(DB::table('ticket_attachment')->where('TICKET_ID',$row->TICKET_ID)->where('IS_ACTIVE','Y')->exists())
                    {
                        return '<i class="fas fa-link" style="color : green;" onClick="fetchAttachmentsAndAppendToModal('. $row->TICKET_ID .')"></i>';
                    }
                    else
                    {
                        return '';
                    } 
            })
            ->addColumn('priority', function ($row) {
                if ($row->PRIORITY == 'High') {
                    return '<span class="badge bg-danger">' . $row->PRIORITY . '</span>';
                } elseif ($row->PRIORITY == 'Medium') {
                    return '<span class="badge bg-warning">' . $row->PRIORITY . '</span>';
                } else {
                    return '<span class="badge bg-success">' . $row->PRIORITY . '</span>';
                }
            })
            ->addColumn('CREATED_ON', function ($row) {
                
                return date('d-M-Y h:i A',strtotime(optional($row)->CREATED_ON));
            })
            ->addColumn('trimmed_subject', function ($row) {
                // Check if the subject is longer than 10 characters and trim it
                $trimmedSubject = strlen($row->SUBJECT) > 10 ? substr($row->SUBJECT, 0, 50) . '...' : $row->SUBJECT;
                return '<span title="' . htmlentities($row->SUBJECT) . '">' . htmlentities($trimmedSubject) . '</span>';
                // return strlen($row->SUBJECT) > 50 ? substr($row->SUBJECT, 0, 50) . '...' : $row->SUBJECT;
            })
            ->addColumn('full_subject', function ($row) {
                return $row->SUBJECT; // Full subject for export
            })
            ->addColumn('ticketNumber', function ($row) {
                $taskNo = '';

                if($row->TASK_NO != 0)
                {
                    $taskNo = '-'.$row->TASK_NO;
                }

                return '<a href="' . route('ticket.view', ['ticketId' => $row->TICKET_ID, 'ticketNumber' => $row->TICKET_NO]) . '" class="text-dark">' . $row->TICKET_NO .''.$taskNo. '</a>';
            })
            ->addColumn('ASSIGNED_ON', function ($row) {
                
                if ($row->ASSIGNED_ON !== null) {
                    return date('d-M-Y h:i A', strtotime($row->ASSIGNED_ON));
                } else {
                    return null; // or any other value you want to return if ASSIGNED_ON is null
                }
            })
            ->addColumn('CLOSED_ON', function ($row) {
                
                if ($row->CLOSED_ON !== null) {
                    return date('d-M-Y h:i A', strtotime($row->CLOSED_ON));
                } else {
                    return null; // or any other value you want to return if ASSIGNED_ON is null
                }
            })
            ->addColumn('AGE', function ($row) {
                // Calculate the difference between current time and creation time
                $createdOn = optional($row)->CREATED_ON;
                $createdAt = strtotime($createdOn);
                $closeedOn = strtotime(optional($row)->CLOSED_ON);
                $ageInSeconds = $closeedOn - $createdAt;
            
                // Convert seconds to days, hours, minutes
                $days = floor($ageInSeconds / (60 * 60 * 24));
                $hours = floor(($ageInSeconds % (60 * 60 * 24)) / (60 * 60));
                $minutes = floor(($ageInSeconds % (60 * 60)) / 60);
            
                // Construct the age string
                $ageString = '';
                if ($days > 0) {
                    $ageString .= $days . ' days ';
                }
                if ($hours > 0) {
                    $ageString .= $hours . ' hours ';
                }
                if ($minutes > 0) {
                    $ageString .= $minutes . ' minutes';
                }
            
                return $ageString;
            })
            ->addColumn('slaBreach', function ($row) {
               $isSLA = $row->isSlaBreach == 'Y' ? 'Yes' : 'No';              
                if($row->STATUS == 'Open'){
                    $totalTimeConsumed = $this->getTimeLeft($row->TICKET_ID, $row->TICKET_NO);
                    // $totalTimeConsumed = $this->getTimeLeft($row->TICKET_ID, $row->TICKET_NO, $row->TECHNICIAN_ID);

                    $sla = $row->sla;
                    if($sla){
                        $isSLA = ($totalTimeConsumed > ($sla * 60)) ? 'Yes' : 'No';
                    }
                }                   
                return $isSLA;
            })
            ->addColumn('pendingTime', function ($row) {
                // Assuming $row->endDate contains the end date of the countdown
                $endDate = $row->DUE_DATE;
                $currentDate = now(); // Use current time

                // Working hours and holidays (assuming you have holidays stored)
                $workingHoursStart = 10; // 10 AM
                $workingHoursEnd = 18; // 6 PM
                 $holidays = HolidayList::pluck('HOLIDAY')->toArray();

                // Calculate the remaining working hours
                $pendingTime = $this->calculateWorkingHoursLeft($currentDate, $endDate, $holidays, $workingHoursStart, $workingHoursEnd);

                // Return formatted countdown string
                return $pendingTime;
            })
            ->rawColumns(['action', 'attachment', 'priority', 'ticketNumber','trimmed_subject','full_subject','slaBreach','pendingTime'])
            ->make(true);
        }

        return view('tickets');
    }

    public function assignTickets(Request $request)
    {
        $api = new TicketsApi;

        $response = $api->getTeams();

        $api = new TicketsApi();
        $getUserBaseTeams = $api->getUserBaseTeams();
        
        // Check if the session code is empty
        if (empty(session('code'))) {
            // If session code is empty, redirect to the home route
            return redirect()->route('home');
        }

        $departmentName = DB::table('department_details')->where('DEPARTMENT_ID',session('code'))->first()->DEPARTMENT_NAME;

        $admin = (userRoleName() == 'Admin') ? 'Yes' : 'No';
        
        $tickets = DB::table('ticket')
        ->leftJoin('mstr_users as users', 'ticket.TECHNICIAN_ID', '=', 'users.EMPLOYEE_ID')
        ->leftJoin('lkp_item_type', 'ticket.ITEM_TYPE_ID', '=', 'lkp_item_type.ITEM_TYPE_ID')
        ->leftJoin('lkp_sub_category', 'ticket.SUB_CATEGORY_ID', '=', 'lkp_sub_category.SUB_CATEGORY_ID')
        ->leftJoin('lkp_category', 'ticket.CATEGORY_ID', '=', 'lkp_category.CATEGORY_ID')
        ->leftJoin('lkp_item', 'ticket.ITEM_ID', '=', 'lkp_item.ITEM_ID')
        ->leftJoin('team', 'team.TEAM_NAME', '=', 'ticket.TEAM_NAME')
        ->leftJoin('map_user_team', 'map_user_team.TEAM_ID', '=', 'team.TEAM_ID')

        ->where('PROJECT_ID', session('code'))
        ->where('IS_CLOSED', 'N')
        ->where('STATUS', 'New')
        
        ->when($admin == 'Yes', function ($query) {
            return $query;
            }, function ($query) {
                return $query->where(function ($subQuery) {
                    $subQuery->where('map_user_team.USER_ID', Auth::id())
                            ->orWhereNull('ticket.TEAM_NAME');
                });
            })
    
        ->when(request('ticketNo'),      fn ($query) => $query->where('ticket.TICKET_NO', 'like', '%' . request('ticketNo') . '%'))
        ->when(request('userName'),      fn ($query) => $query->where('ticket.REQUESTED_BY', request('userName')))
        ->when(request('oldTicket'),     fn ($query) => $query->where('ticket.TICKET_NO', request('oldTicket')))
        ->when(request('department'),    fn ($query) => $query->where('ticket.DEPARTMENT_CODE', request('department')))
        ->when(request('technician'),    fn ($query) => $query->where('ticket.TECHNICIAN_ID', request('technician')))
        ->when(request('requestedFrom'), fn ($query) => $query->whereDate('ticket.CREATED_ON','>=',date('Y-m-d',strtotime(request('requestedFrom')))))
        ->when(request('requestedTo'),   fn ($query) => $query->whereDate('ticket.CREATED_ON','<=',date('Y-m-d',strtotime(request('requestedTo')))))
        ->when(request('status'),        fn ($query) => $query->whereIn('ticket.STATUS', request('status')))
        ->when(request('progress'),      fn ($query) => $query->whereIn('ticket.PROGRESS', request('progress')))
        ->when(request('mode'),          fn ($query) => $query->where('ticket.MODE', request('mode')))
        ->when(request('teamId'),        fn ($query) => $query->where('ticket.TEAM_NAME', request('teamId')))
        ->when(request('createdBy'),        fn ($query) => $query->where('ticket.CREATED_BY','like', '%' . request('createdBy')  . '%'))
    
       ->orderByRaw("CASE
                WHEN team.TEAM_NAME IS NOT NULL THEN 1
                ELSE 2
            END")
        ->orderBy('ticket.CREATED_ON', 'DESC')
        ->select('ticket.*','users.USER_NAME as TECHNICIAN_NAME', 
                'lkp_category.DISPLAY_NAME as CATEGORY', 
                'lkp_sub_category.DISPLAY_NAME as SUB_CATEGORY', 
                'lkp_item_type.DISPLAY_NAME as ITEM_TYPE',
                'lkp_item.DISPLAY_NAME as ITEM',
                'team.TEAM_ID as teamId', 
                'team.TEAM_NAME as teamName')
                ->distinct()
        ->get();    

        // Get all TICKET_IDs from the tickets
        $ticketIds = $tickets->pluck('TICKET_ID');
           
       if ($request->ajax()) {
        return Datatables::of($tickets)->addIndexColumn() 
           
            ->addColumn('CREATED_ON', function ($row) {
                
                return date('d-M-Y h:i A',strtotime(optional($row)->CREATED_ON));
            })
            ->addColumn('ASSIGNED_ON', function ($row) {                
                if ($row->ASSIGNED_ON !== null) {
                    return date('d-M-Y h:i A', strtotime($row->ASSIGNED_ON));
                } else {
                    return null; // or any other value you want to return if ASSIGNED_ON is null
                }
            })
            ->addColumn('ticketNumber', function ($row) {
                $taskNo = '';

                if($row->TASK_NO != 0)
                {
                    $taskNo = '-'.$row->TASK_NO;
                }

                $color = '#343a40'; 
                if($row->IS_RELEASED == 'Y'){                    
                    $color = '#FFAD62'; 
                } 

                return '<a href="' . route('ticket.view', ['ticketId' => $row->TICKET_ID, 'ticketNumber' => $row->TICKET_NO]) . ' " style="color: ' . $color . ';">' . $row->TICKET_NO .''.$taskNo. '</a>';
            })
            
            ->addColumn('action', function ($row) {
                    // Render the button only if ASSIGNED_ON is null
                    $btn = '<div class="d-flex justify-content-between table-actions-container">
                                <button class="btn tickets-action-btn-transparent" 
                                        onClick="assignSelfTicket(' . $row->TICKET_ID . ', \'' . $row->TICKET_NO . '\')" 
                                        title="Assign">
                                    <img src="' . asset('public/img/icons/assign-ticket.png') . '" alt="" height="20">
                                </button>';
                                       
                return $btn;
            })          
            ->rawColumns(['action', 'ticketNumber']) 
            ->make(true);
        }

        return view('assign-tickets',compact('departmentName','getUserBaseTeams'))->withTeams($response['data']);
    }
  
    public function calculateWorkingHoursLeft($now, $endDate, $holidays, $workingHoursStart, $workingHoursEnd)
    {
       if (!($now instanceof Carbon)) {
            $now = Carbon::parse($now);
        }

        if (!($endDate instanceof Carbon)) {
            $endDate = Carbon::parse($endDate);
        }
        // Convert holidays to a format for comparison
        $holidayDates = array_map(function ($holiday) {
            return Carbon::parse($holiday)->startOfDay();
        }, $holidays);

        // Initialize total remaining hours
        $totalHours = 0;

        // Loop through the dates from now until the endDate
        $currentDate = clone $now;

        while ($currentDate->lessThanOrEqualTo($endDate)) {
            // Check if the current day is a weekend or holiday
            if (in_array($currentDate->startOfDay(), $holidayDates)) {
                // Skip weekends and holidays
                $currentDate->addDay();
                continue;
            }

            // Calculate working hours for the current day
            if ($currentDate->isSameDay($now)) {
                // If today, calculate the remaining working hours from now
                $hoursLeftToday = min($workingHoursEnd, $endDate->hour) - max($workingHoursStart, $now->hour);
            } elseif ($currentDate->isSameDay($endDate)) {
                // If it's the end day, calculate hours until the endDate time
                $hoursLeftToday = $endDate->hour - $workingHoursStart;
            } else {
                // Otherwise, it's a full working day
                $hoursLeftToday = $workingHoursEnd - $workingHoursStart;
            }

            $totalHours += max(0, $hoursLeftToday); // Add only positive hours

            // Move to the next day
            $currentDate->addDay()->setTime(0, 0);
        }

        // Convert total hours into days, hours, minutes, and seconds
        return $this->convertHoursToDHMS($totalHours);
    }

    // Function to convert hours to days, hours, minutes, seconds
    public function convertHoursToDHMS($totalHours)
    {
        // Assuming 8 working hours per day
        $days = floor($totalHours / 8); // Get the number of full working days
        $remainingHours = $totalHours % 8; // Remaining hours after full days

        // Convert hours to minutes and seconds for more granular countdown
        $totalMinutes = (($totalHours % (1000 * 60 * 60)) / (1000 * 60));  // Get decimal part and convert to minutes
        $minutes = floor($totalMinutes); // Full minutes
        $seconds = floor(($totalMinutes - $minutes) * 60); // Remaining seconds

        $remainingHours += $days * 8;

        // Return the result in the format of days, hours, minutes, and seconds
        return "{$remainingHours}h {$minutes}m {$seconds}s";
    }

    public function statusUpdateTicket(Request $request)
    {
        $api = new TicketsApi;
        $haveAttachment = 'N';
        $imageName='';
        if ($request->hasFile('file')) {
            // Get the uploaded file
            $uploadedFile = $request->file('file');
            $originalName = $uploadedFile->getClientOriginalName();
            // Extract the original file name (without the extension)
            $imageName = $request->ticketId.'_'. uniqid() . '_' . $originalName;
            $uploadedFile->move(public_path("updates"), $imageName);
            $haveAttachment = 'Y';
        }
        $request['haveAttacment'] = $haveAttachment;
        $request['filename'] = $imageName;
        
        $data = $api->statusUpdateTicket($request->all());

        return response()->json($data);
    }
    public function categorizeTicket(Request $request)
    {
        $api = new TicketsApi;

        $data = $api->categorizeTicket($request->all());

        return response()->json($data);
    }
    public function closeTicket(Request $request)
    {
        $api = new TicketsApi;

        $haveAttachment = 'N';
        $imageName='';
        if ($request->hasFile('file')) {
                // Get the uploaded file
            $uploadedFile = $request->file('file');
            $originalName = $uploadedFile->getClientOriginalName();

            if($originalName){
                // Extract the original file name (without the extension)
                $imageName = $request->ticketId.'_'. uniqid() . '_' . $originalName;
                $uploadedFile->move(public_path("updates"), $imageName);
                $haveAttachment = 'Y';
            }            
        }
        $request['haveAttacment'] = $haveAttachment;
        $request['filename'] = $imageName;       

        $data = $api->closeTicket($request->all());        

        return response()->json($data);
    }    
    public function reopenTicket(Request $request)
    {
        $api = new TicketsApi;

        $data = $api->reopenTicket($request->all());

        return response()->json($data);
    }
    public function getUpdates(Request $request)
    {
        $api = new TicketsApi;

        $data = $api->getUpdates($request->all());

        return response()->json($data);
    }
    public function updateTask(Request $request)
    {
        $api = new TicketsApi;

        $data = $api->updateTask($request->all());

        return response()->json($data);
    }
  
    public function getPredefinedTasks(Request $request)
    {
        $api = new TicketsApi;

        $data = $api->getPredefinedTasks($request->all());

        if ($request->ajax()) {
            return Datatables::of($data['data'])->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<div class="d-flex justify-content-between table-actions-container">
                    <button class="btn tickets-action-btn-transparent" onClick="getSubTaskInfo('. $row['taskId'] .')" title="Edit">
                        <img src="'.asset('public/img/icons/edit-btn.png') .'" alt="" height="20">
                    </button>
                    </div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
            }

        return response()->json($data);
    }
    public function pendingTickets(Request $request)
    {
        $api = new TicketsApi;

        $response = $api->getTeams();
        
        // Check if the session code is empty
        if (empty(session('code'))) {
            // If session code is empty, redirect to the home route
            return redirect()->route('home');
        }

        $departmentName = DB::table('department_details')->where('DEPARTMENT_ID',session('code'))->first()->DEPARTMENT_NAME;
        
        $admin = (userRoleName() == 'Admin') ? 'Yes' : '';

        $tickets = DB::table('ticket')
        ->distinct()
        ->leftJoin('mstr_users as users', 'ticket.TECHNICIAN_ID', '=', 'users.EMPLOYEE_ID')
        ->leftJoin('lkp_item_type', 'ticket.ITEM_TYPE_ID', '=', 'lkp_item_type.ITEM_TYPE_ID')
        ->leftJoin('lkp_sub_category', 'ticket.SUB_CATEGORY_ID', '=', 'lkp_sub_category.SUB_CATEGORY_ID')
        ->leftJoin('lkp_category', 'ticket.CATEGORY_ID', '=', 'lkp_category.CATEGORY_ID')
        ->leftJoin('lkp_item', 'ticket.ITEM_ID', '=', 'lkp_item.ITEM_ID')
        ->leftJoin('team', 'team.TEAM_NAME', '=', 'ticket.TEAM_NAME')
        ->leftJoin('map_user_team', 'map_user_team.TEAM_ID', '=', 'team.TEAM_ID')  
        
        ->where('PROJECT_ID', session('code'))
        ->where('IS_CLOSED', 'N')
        ->whereIn('PROGRESS', ['Open', 'New', 'In Progress', 'On Hold'])
        ->when($admin == 'Yes', function ($query) {
                return $query;
            }, function ($query) {
                return $query->where('map_user_team.USER_ID', Auth::id());
            })
    
        ->when(request('ticketNo'),      fn ($query) => $query->where('ticket.TICKET_NO', 'like', '%' . request('ticketNo') . '%'))
        ->when(request('userName'),      fn ($query) => $query->where('ticket.REQUESTED_BY', request('userName')))
        ->when(request('oldTicket'),     fn ($query) => $query->where('ticket.TICKET_NO', request('oldTicket')))
         ->when(request('subject'),       fn ($query) => $query->where('ticket.SUBJECT', 'like', '%' . request('subject') . '%'))
        ->when(request('description'),  fn ($query) => $query->where('ticket.DESCRIPTION', 'like', '%' . request('description') . '%'))
        ->when(request('department'),    fn ($query) => $query->where('ticket.DEPARTMENT_CODE', request('department')))
        ->when(request('technician'),    fn ($query) => $query->where('ticket.TECHNICIAN_ID', request('technician')))
        ->when(request('requestedFrom'), fn ($query) => $query->whereDate('ticket.CREATED_ON','>=',date('Y-m-d',strtotime(request('requestedFrom')))))
        ->when(request('requestedTo'),   fn ($query) => $query->whereDate('ticket.CREATED_ON','<=',date('Y-m-d',strtotime(request('requestedTo')))))
        ->when(request('status'),        fn ($query) => $query->whereIn('ticket.STATUS', request('status')))
        ->when(request('progress'),      fn ($query) => $query->whereIn('ticket.PROGRESS', request('progress')))
        ->when(request('mode'),          fn ($query) => $query->where('ticket.MODE', request('mode')))
        ->when(request('teamId'),        fn ($query) => $query->where('ticket.TEAM_NAME', request('teamId')))
        ->when(request('createdBy'),        fn ($query) => $query->where('ticket.CREATED_BY','like', '%' . request('createdBy')  . '%'))
    
        ->orderByRaw("CASE
                WHEN ticket.PROGRESS = 'New' THEN 1
                WHEN ticket.PROGRESS = 'Open' THEN 2
                ELSE 3
                END")
        ->orderBy('ticket.CREATED_ON', 'DESC')
        ->select('ticket.*','users.USER_NAME as TECHNICIAN_NAME', 
                'lkp_category.DISPLAY_NAME as CATEGORY', 
                'lkp_sub_category.DISPLAY_NAME as SUB_CATEGORY', 
                'lkp_item_type.DISPLAY_NAME as ITEM_TYPE',
                'lkp_item.DISPLAY_NAME as ITEM')
        ->get();    

        // Get all TICKET_IDs from the tickets
        $ticketIds = $tickets->pluck('TICKET_ID');

        // Fetch last updates for these tickets
        $lastUpdates = DB::table('ticket_updates')
            ->select('TICKET_ID', 'DESCRIPTION')
            ->whereIn('TICKET_ID', $ticketIds)
            ->orderBy('TICKET_UPDATE_ID', 'desc')
            ->get()
            ->groupBy('TICKET_ID')
            ->map(function ($updates) {
                return $updates->first();
            });
    
       if ($request->ajax()) {
        return Datatables::of($tickets)->addIndexColumn()          
            ->addColumn('priority', function ($row) {
                if ($row->PRIORITY == 'High') {
                    return '<span class="badge bg-danger">' . $row->PRIORITY . '</span>';
                } elseif ($row->PRIORITY == 'Medium') {
                    return '<span class="badge bg-warning">' . $row->PRIORITY . '</span>';
                } else {
                    return '<span class="badge bg-success">' . $row->PRIORITY . '</span>';
                }
            })
            ->addColumn('CREATED_ON', function ($row) {
                
                return date('d-M-Y h:i A',strtotime(optional($row)->CREATED_ON));
            })
            ->addColumn('ASSIGNED_ON', function ($row) {                
                if ($row->ASSIGNED_ON !== null) {
                    return date('d-M-Y h:i A', strtotime($row->ASSIGNED_ON));
                } else {
                    return null; // or any other value you want to return if ASSIGNED_ON is null
                }
            })
            ->addColumn('AGE', function ($row) {
                // Calculate the difference between current time and creation time
                $createdOn = optional($row)->CREATED_ON;
                $createdAt = strtotime($createdOn);
                $currentTime = time();
                $ageInSeconds = $currentTime - $createdAt;
            
                // Convert seconds to days, hours, minutes
                $days = floor($ageInSeconds / (60 * 60 * 24));
                $hours = floor(($ageInSeconds % (60 * 60 * 24)) / (60 * 60));
                $minutes = floor(($ageInSeconds % (60 * 60)) / 60);
            
                // Construct the age string
                $ageString = '';
                if ($days > 0) {
                    $ageString .= $days . ' days ';
                }
                if ($hours > 0) {
                    $ageString .= $hours . ' hours ';
                }
                if ($minutes > 0) {
                    $ageString .= $minutes . ' minutes';
                }
            
                return $ageString;
            })
            ->addColumn('ticketNumber', function ($row) {
                $taskNo = '';

                if($row->TASK_NO != 0)
                {
                    $taskNo = '-'.$row->TASK_NO;
                }

                return '<a href="' . route('ticket.view', ['ticketId' => $row->TICKET_ID, 'ticketNumber' => $row->TICKET_NO]) . '" class="text-dark">' . $row->TICKET_NO .''.$taskNo. '</a>';
            })
            ->addColumn('LAST_UPDATE', function ($row) use ($lastUpdates) {
                // Get the last update for the current ticket
                $lastUpdate = $lastUpdates->get($row->TICKET_ID);
        
                // Return the DESCRIPTION if a last update is found, otherwise return an empty string
                return $lastUpdate ? $lastUpdate->DESCRIPTION : '';
            })          
            ->rawColumns(['action', 'attachment', 'priority', 'ticketNumber']) 
            ->make(true);
        }

        return view('pending-tickets',compact('departmentName'))->withTeams($response['data']);
    }
    public function feedbackReport(Request $request)
    {
        $api = new TicketsApi;

        $response = $api->getTeams();
        // Check if the session code is empty
        if (empty(session('code'))) {
            // If session code is empty, redirect to the home route
            return redirect()->route('home');
        }

        $departmentName = DB::table('department_details')->where('DEPARTMENT_ID',session('code'))->first()->DEPARTMENT_NAME;

            $tickets = DB::table('ticket')
            ->leftJoin('mstr_users', 'ticket.TECHNICIAN_ID', '=', 'mstr_users.EMPLOYEE_ID')
            ->leftJoin('ticket_attachment', 'ticket.TICKET_ID', '=', 'ticket_attachment.TICKET_ID')
            ->leftJoin('lkp_item_type', 'ticket.ITEM_TYPE_ID', '=', 'lkp_item_type.ITEM_TYPE_ID')
            ->leftJoin('lkp_sub_category', 'ticket.SUB_CATEGORY_ID', '=', 'lkp_sub_category.SUB_CATEGORY_ID')
            ->leftJoin('lkp_category', 'ticket.CATEGORY_ID', '=', 'lkp_category.CATEGORY_ID')
            ->leftJoin('lkp_item', 'ticket.ITEM_ID', '=', 'lkp_item.ITEM_ID')
            ->where('PROJECT_ID', session('code'))
            
            ->when(request('ticketNo'),      fn ($query) => $query->where('ticket.TICKET_NO', 'like', '%' . request('ticketNo') . '%'))
            ->when(request('userName'),      fn ($query) => $query->where('ticket.REQUESTED_BY', request('userName')))
            ->when(request('department'),    fn ($query) => $query->where('ticket.DEPARTMENT_CODE', request('department')))
            ->when(request('technician'),    fn ($query) => $query->where('ticket.TECHNICIAN_ID', request('technician')))
            ->when(request('teamId'),        fn ($query) => $query->whereIn('ticket.TEAM_NAME', request('teamId')))

            ->when(!request('requestedFrom') && !request('requestedTo'), 
                function ($query) {

                    $from_date = date("Y-m-01"); // First day of the current month
                    $to_date = date("Y-m-t");   // Last day of the current month

                    return $query->whereBetween('ticket.FEEDBACK_ON', [$from_date, $to_date]);
            })
            
            ->when(request('requestedFrom') && request('requestedTo'), function ($query) {
                return $query->whereDate('ticket.FEEDBACK_ON', '>=', date('Y-m-d', strtotime(request('requestedFrom'))))
                            ->whereDate('ticket.FEEDBACK_ON', '<=', date('Y-m-d', strtotime(request('requestedTo'))));
            })

            ->when(request('requestedFrom') && !request('requestedTo'), function ($query) {
                return $query->whereDate('ticket.FEEDBACK_ON', '>=', date('Y-m-d', strtotime(request('requestedFrom'))));
            })
            ->when(!request('requestedFrom') && request('requestedTo'), function ($query) {
                return $query->whereDate('ticket.FEEDBACK_ON', '<=', date('Y-m-d', strtotime(request('requestedTo'))));
            })
            
            ->when(request('feedbackpoint'), fn ($query) => $query->where('ticket.FEEDBACK_POINT', request('feedbackpoint')))
           
            ->orderBy('CREATED_ON', 'DESC')
            ->select('ticket.*', 'mstr_users.USER_NAME as TECHNICIAN_NAME', 
                'lkp_category.DISPLAY_NAME as CATEGORY', 
                'lkp_sub_category.DISPLAY_NAME as SUB_CATEGORY', 
                'lkp_item_type.DISPLAY_NAME as ITEM_TYPE', 
                DB::raw('CASE WHEN ticket_attachment.TICKET_ID IS NOT NULL THEN 1 ELSE 0 END AS has_attachment'))
                ->distinct()
            ->get();

       if ($request->ajax()) {
        return Datatables::of($tickets)->addIndexColumn()                     
            ->addColumn('priority', function ($row) {
                if ($row->PRIORITY == 'High') {
                    return '<span class="badge bg-danger">' . $row->PRIORITY . '</span>';
                } elseif ($row->PRIORITY == 'Medium') {
                    return '<span class="badge bg-warning">' . $row->PRIORITY . '</span>';
                } else {
                    return '<span class="badge bg-success">' . $row->PRIORITY . '</span>';
                }
            })
            ->addColumn('CREATED_ON', function ($row) {
                
                return date('d-M-Y h:i A',strtotime(optional($row)->CREATED_ON));
            })
            ->addColumn('FEEDBACK_ON', function ($row) {
                
                return date('d-M-Y h:i A',strtotime(optional($row)->FEEDBACK_ON));
            })
            ->addColumn('ASSIGNED_ON', function ($row) {
                
                return date('d-M-Y h:i A',strtotime(optional($row)->ASSIGNED_ON));
            })
            ->addColumn('CLOSED_ON', function ($row) {

                return date('d-M-Y h:i A',strtotime(optional($row)->CLOSED_ON));
            })
            ->addColumn('ticketNumber', function ($row) {

                $taskNo = '';

                if($row->TASK_NO != 0)
                {
                    $taskNo = '-'.$row->TASK_NO;
                }

                return '<a href="' . route('ticket.view', ['ticketId' => $row->TICKET_ID, 'ticketNumber' => $row->TICKET_NO]) . '" class="text-dark">' . $row->TICKET_NO .''.$taskNo. '</a>';
            })
            ->rawColumns(['action', 'priority', 'ticketNumber'])
            ->make(true);
        }

        return view('feedback-report',compact('departmentName'))->withTeams($response['data']);
    }
    public function getAssignmentDetails(Request $request)
    {
        $data =  DB::table('ticket')
                    ->where('TICKET_ID',$request->ticketId)
                    ->select('ticket.*', DB::raw("DATE_FORMAT(DUE_DATE, '%d-%b-%Y %h:%i %p') as DUE_DATE"))
                    ->first();       

       // Return the data as a JSON response
       return response()->json($data);        
       
    }
    public function logTicket(Request $request)
    {
        // Gather input data
        $requesterName  = $request->input('requester_name');
        $subject        = $request->input('subject');
        $description    = $request->input('description');
        $userId         = $request->input('user_id');
        $callMode       = $request->input('call_mode');
        $priority       = $request->input('priority');
        $deptCode       = $request->input('department_code');
        $extension      = $request->input('extension');
        $projectIdValue = 1;
        $taskType       = $request->input('task_type');
        $taskSubType    = $request->input('task_subtype');
        $category       = $request->input('category_id');
        $subCategory    = $request->input('sub_category_id');
        $itemType       = $request->input('item_type_id');
        $item           = $request->input('item_id');
        $createdBy      = $userId;
        $createdOn      = now()->toDateTimeString();

        // Generate XML string
        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElement('TICKET');

        $writer->startElement('USER_ID');
        $writer->text($userId);
        $writer->endElement();

        $writer->startElement('USER_NAME');
        $writer->text($requesterName);
        $writer->endElement();

        $writer->startElement('MODE');
        $writer->text($callMode);
        $writer->endElement();

        $writer->startElement('PRIORITY');
        $writer->text($priority);
        $writer->endElement();

        $writer->startElement('SUBJECT');
        $writer->text($subject);
        $writer->endElement();

        $writer->startElement('DESCRIPTION');
        $writer->text($description);
        $writer->endElement();

        $writer->startElement('STATUS');
        $writer->text('New');
        $writer->endElement();

        $writer->startElement('DEPARTMENT_CODE');
        $writer->text($deptCode);
        $writer->endElement();

        $writer->startElement('EXTENSION');
        $writer->text($extension);
        $writer->endElement();

        $writer->startElement('PROJECT_ID');
        $writer->text($projectIdValue);
        $writer->endElement();

        $writer->startElement('TASK_TYPE_ID');
        $writer->text($taskType);
        $writer->endElement();

        $writer->startElement('TASK_SUBTYPE_ID');
        $writer->text($taskSubType);
        $writer->endElement();

        $writer->startElement('CATEGORY_ID');
        $writer->text($category);
        $writer->endElement();

        $writer->startElement('SUB_CATEGORY_ID');
        $writer->text($subCategory);
        $writer->endElement();

        $writer->startElement('ITEM_TYPE_ID');
        $writer->text($itemType);
        $writer->endElement();

        $writer->startElement('ITEM_ID');
        $writer->text($item);
        $writer->endElement();

        $writer->startElement('CREATED_BY');
        $writer->text($createdBy);
        $writer->endElement();

        $writer->startElement('CREATED_ON');
        $writer->text($createdOn);
        $writer->endElement();

        $writer->endElement(); // TICKET
        $writer->endDocument();

        $xmlContent = $writer->outputMemory();

        // Call the model method to insert the ticket and get the output parameter
        $ticketId = TicketProcedure::insertTicket($xmlContent);

        // Handle attached files if any
        if ($request->hasFile('attachment')) {

            $file = $request->file('attachment');
            // Generate a unique image name
            $originalName = $file->getClientOriginalName();

            // Insert attachment details into the ticket_attachment table
            DB::connection('secondary_mysql')
                                ->table('ticket')
                                ->where('TICKET_ID',$ticketId)
                                ->update(['ATTACHMENT' => $originalName]);

            // Move the file to the attachments directory
            $file->move(public_path("attachments"), $originalName);
        }

        return redirect()->back()->with('success', 'Ticket created successfully');
    }
    public function uploadImage(Request $request)
    {
        // Get the base64 encoded file
        $base64File = $request->input('file');
        $fileName = $request->input('fileName');
        
        // Decode the base64 file
        $fileData = base64_decode($base64File);

        // Save the file to the attachments directory
        $filePath = public_path("attachments/") . $fileName;

        file_put_contents($filePath, $fileData);

        return response()->json(['message' => 'File uploaded successfully.'], 200);
    }
    public function updateAttachments(Request $request)
    {
        // Get the base64 encoded file
        $base64File = $request->input('file');
        $fileName = $request->input('fileName');
        
        // Decode the base64 file
        $fileData = base64_decode($base64File);

        // Save the file to the attachments directory
        $filePath = public_path("updates/") . $fileName;

        file_put_contents($filePath, $fileData);

        return response()->json(['message' => 'File uploaded successfully.'], 200);
    }
    protected function sendNotification($fcmId, $body ,$title)
    {
        $accessToken = $this->getAccessToken();  

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/v1/projects/iskcon-hr/messages:send', [
        
            'message' => [
                'token' => $fcmId,
                'android' => [
                    'priority' => 'high',                    
                ],               
                'apns' => [
                   'headers' => [
                        'apns-priority' => '10', // High priority for iOS
                    ],
                    'payload' => [
                        'aps' => [
                            'alert' => [
                                'title' => $title,  // Notification title
                                'body' => $body,    // Notification body
                            ],
                            'sound' => 'default',   // Notification sound
                            'badge' => 1,           // Badge count (optional)
                            'content-available' => 1,  // Silent push notification flag
                        ],
                    ],
                ],               
                'data' => [
                    'click_action' => 'Ticket', 
                    'title' => $title,  // Notification title
                    'body' => $body,               
                ],
            ],
        ]); 

        if($response){
            return $response->json();   
        }              
    }

    private function getAccessToken()
    {
        // Path to your service account JSON file
        $serviceAccountPath = storage_path('app/firebase/service-account.json');
        
        $client = new Google_Client();
        $client->setAuthConfig($serviceAccountPath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        
        // Fetch the OAuth2 token
        $token = $client->fetchAccessTokenWithAssertion();

        // Handle any errors during token fetch
        if (isset($token['error'])) {
            throw new \Exception('Failed to get access token: ' . $token['error_description']);
        }
        
        return $token['access_token'];
    }
    public function getProgressOption(Request $request){
        $html='';
        if (is_array($request->status)) {
            foreach ($request->status as $status) {

                $progresses = DB::table('lkp_progress')
                                    ->where('STATUS_CODE',$status)
                                    ->get();                    
                
                foreach ($progresses as $val) {
                    $html .= "<option value=\"{$val->PROGRESS}\">{$val->PROGRESS}</option>";
                }                
            }
        }

        return response($html);   
    }
    // Calculate SLA
    public function calSLA(Request $request)
    {
       // Retrieve the list of holidays
        $holidays = HolidayList::pluck('HOLIDAY')->toArray();

        // API call to get the technician's week-off holiday
        $weekOffResponse = Http::post('https://hr.iskconbangalore.net/v1/api/profile/get-weekoff-date', [
            'accessKey' => '729!#kc@nHKRKkbngsppnsg@491',
            'employeeId' => $request->technicianId,
        ]);
        // If the API call is successful, add the week-off day to holidays
        if ($weekOffResponse->successful()) {
            $weekOffData = $weekOffResponse->json(); // Decode the JSON response
            foreach ($weekOffData['data'] as $weekOff) {
                $holidays[] = $weekOff['dates']; // Add each week-off date to the holidays array
            }
        }

        $ticket = DB::table('ticket')->where(['TICKET_ID' => $request->ticketId])->first();
        $assignedOn = ($ticket->ASSIGNED_ON) ? $ticket->ASSIGNED_ON : now();

        // Retrieve SLA for the given taskType (assumed to be in hours)
        $SLAforTicketTypeHours = optional(TaskType::find($request->taskType))->SLA ?? 0;

        // Convert SLA from hours to minutes
        $SLAforTicketTypeMinutes = $SLAforTicketTypeHours * 60;

        // Proceed with the $assignedOn timestamp as a Carbon instance
        $assignedOnTimestamp = Carbon::parse($assignedOn);  

        // Define business hours
        $startOfDay = 10; // 10 AM
        $endOfDay = 18;   // 6 PM

        // Define Lunch break time
        $lunchStartTime = date('H:i:s', strtotime('13:00:00'));  // 1:00 PM 
        $lunchEndTime = date('H:i:s', strtotime('13:30:00'));;  // 1:30 PM (expressed in hours)

        // Initialize the SLA deadline to the assignedOn timestamp
        $slaDeadline = $assignedOnTimestamp->copy();
        $minutesRemaining = $SLAforTicketTypeMinutes;

        while ($minutesRemaining > 0) {
           
            // If the current time is outside business hours, move to the next business day
            if ($slaDeadline->hour < $startOfDay) {                
                $slaDeadline->setTime($startOfDay, 0);
            } 

            if ($slaDeadline->hour >= $endOfDay) {
                $slaDeadline->addDay()->setTime($startOfDay, 0);
                // Check if the new day is a holiday, skip holidays
                while (in_array($slaDeadline->toDateString(), $holidays)) {
                    $slaDeadline->addDay()->setTime($startOfDay, 0);
                }
                continue;
            }            

            // Calculate the remaining minutes in the current business day
            $endOfBusinessDay = $slaDeadline->copy()->setTime($endOfDay, 0);
            $availableMinutes = $slaDeadline->diffInMinutes($endOfBusinessDay, false);

            if ($minutesRemaining <= $availableMinutes) {  
                
                // If remaining SLA fits within the current business day, add it and break
                $slaDeadline->addMinutes($minutesRemaining);                
                break;
            } 
            else {                
                // If remaining SLA exceeds current business day's remaining time, subtract and move to next day
                $slaDeadline->addMinutes($availableMinutes);
                $minutesRemaining -= $availableMinutes;

                // Move to the next business day
                $slaDeadline->addDay()->setTime($startOfDay, 0);

                // Check if the new day is a holiday, if so, skip to the next day
                while (in_array($slaDeadline->toDateString(), $holidays)) {
                    $slaDeadline->addDay()->setTime($startOfDay, 0);
                }
            }
        }

        $slaDeadlineTime = date('H:i:s', strtotime($slaDeadline));
        $assignedOnTime = date('H:i:s', strtotime($request->assignedOn));

        if ($SLAforTicketTypeHours == '4' && $slaDeadlineTime >= $lunchStartTime && $assignedOnTime <= $lunchStartTime) { 
                    
            $slaDeadline->addMinutes(30); // Add 30 minutes for lunch
        }
        else if ($SLAforTicketTypeHours == '4' && ($assignedOnTime >= $lunchStartTime && $assignedOnTime < $lunchEndTime)) { 
                    
            $slaDeadline->addMinutes(30); // Add 30 minutes for lunch
        }

        // Normalize both dates to the start of their respective days
        $assignedOnStartOfDay = $assignedOnTimestamp->copy()->startOfDay();
        $slaDeadlineStartOfDay = $slaDeadline->copy()->startOfDay();

        // Calculate the number of days between the two normalized dates
        $daysDifference = $assignedOnStartOfDay->diffInDays($slaDeadlineStartOfDay);
       
        $idleTime = $daysDifference * 16;

        // Return the resulting timestamp and days difference
        return response()->json([
            'slaDeadline' => $slaDeadline->format('d-m-Y h:i A'),// Assuming $slaDeadline is a UTC timestamp or DateTime
            'idleTime' => $idleTime // Any additional data you might be returning
        ]);
    }

    public function getTimeLeft($ticketId, $ticketNumber)
    {
        $updates = DB::table('log_status_movement')
            ->select('log_status_movement.CHANGED_ON','log_status_movement.CHANGED_TO')
            ->where('TICKET_ID', $ticketId)
            ->orderBy('CHANGED_ON', 'asc')
            ->get();
            
        if ($updates->isEmpty()) {
            return 0; // No updates, return zero time
        }

        $workingStart = 10; // Start of working hours (10 AM)
        $workingEnd = 18;   // End of working hours (6 PM)
        $holidays = HolidayList::pluck('HOLIDAY')->toArray(); // Retrieve holiday list

        // if($technicianId)
        // {
        //     $weekOffResponse = Http::post('https://hr.iskconbangalore.net/v1/api/profile/get-weekoff-date', [
        //         'accessKey' => '729!#kc@nHKRKkbngsppnsg@491',
        //         'employeeId' => $technicianId,
        //     ]);
        //     // If the API call is successful, add the week-off day to holidays
        //     if ($weekOffResponse->successful()) {
        //         $weekOffData = $weekOffResponse->json(); // Decode the JSON response
        //         foreach ($weekOffData['data'] as $weekOff) {
        //             $holidays[] = $weekOff['dates']; // Add each week-off date to the holidays array
        //         }
        //     }
        // }

        $totalUsedTime = 0; // In hours
            
        $firstStatus = $updates[0]->CHANGED_TO;
        $start = Carbon::parse($updates[0]->CHANGED_ON);

        $allSameStatus = true;
        foreach ($updates as $update) {
            if ($update->CHANGED_TO !== $firstStatus) {
                $allSameStatus = false;
                break;
            }
        }

        if (count($updates) === 1) {
            $singleUpdate = $updates[0];
            if ($singleUpdate->CHANGED_TO === 'Open' || $singleUpdate->CHANGED_TO === 'In Progress') {
                $start = Carbon::parse($singleUpdate->CHANGED_ON);
                $end = Carbon::now();

                // Calculate working hours between the single update and current time
                $totalUsedTime += $this->calculateWorkingHours($start, $end, $workingStart, $workingEnd, $holidays);

                $slaOn = DB::table('department_details')
                            ->where('DEPARTMENT_ID', session('code'))
                            ->first();

                $ticket = Ticket::where('TICKET_ID',$ticketId)->first();

                if ($slaOn && $slaOn->SLA_ON == 'CREATED_ON') {

                    $createdOn = Carbon::parse($ticket->CREATED_ON);
                    $firstChangedOn = Carbon::parse($singleUpdate->CHANGED_ON);

                    if ($createdOn->lessThan($firstChangedOn)) {
                        $gapTime = $this->calculateWorkingHours($createdOn, $firstChangedOn, $workingStart, $workingEnd, $holidays);
                        $totalUsedTime += $gapTime;
                    }
                }

            }
            return $totalUsedTime; // Return the total time immediately
        }

        if ($allSameStatus) {
            // All statuses are the same, calculate time from the first status to now
            if ($firstStatus === 'Open' || $firstStatus === 'In Progress') {
                $end = Carbon::now();
                
                $totalUsedTime += $this->calculateWorkingHours($start, $end, $workingStart, $workingEnd, $holidays);
                // echo "\nhi ",$totalUsedTime;
            }
        } else {
            for ($i = 0; $i <= count($updates) - 1; $i++) {

                $currentStatus = $updates[$i]->CHANGED_TO;
                
                $start = Carbon::parse($updates[$i]->CHANGED_ON);
                        
                // Skip intervals where the current status is "On Hold"
                if ($currentStatus === 'Cancelled' || $currentStatus == 'On Hold') {
                    continue;
                }
                
                // Determine the end time
                if (isset($updates[$i + 1])) {
                    $nextStatus = $updates[$i + 1]->CHANGED_TO;
                    
                    $end = Carbon::parse($updates[$i + 1]->CHANGED_ON);

                } else {
                    // Last status, calculate up to current time
                    if ($currentStatus === 'In Progress' || $currentStatus === 'Open' || $currentStatus === 'Reopened'){
                        $end = Carbon::now();
                    }                    
                }
                // Track the last "Reopened" status
                if ($currentStatus === 'Reopened') {
                    $start = Carbon::parse($updates[$i]->CHANGED_ON);
                    $totalUsedTime = 0;
                }    
                
                $totalUsedTime += $this->calculateWorkingHours($start, $end, $workingStart, $workingEnd, $holidays);
            }            
        }
        
        $slaOn = DB::table('department_details')
                            ->where('DEPARTMENT_ID', session('code'))
                            ->first();

        $ticket = Ticket::where('TICKET_ID',$ticketId)->first();

        if ($slaOn && $slaOn->SLA_ON == 'CREATED_ON') {       

            $createdOn = Carbon::parse($ticket->CREATED_ON);
            $firstChangedOn = Carbon::parse($updates[0]->CHANGED_ON);

            if ($createdOn->lessThan($firstChangedOn)) {
                $gapTime = $this->calculateWorkingHours($createdOn, $firstChangedOn, $workingStart, $workingEnd, $holidays);
                $totalUsedTime += $gapTime;
            }
        }

       return $totalUsedTime; // Total time in minutes
    }

    private function calculateWorkingHours($start, $end, $workingStart, $workingEnd, $holidays)
    {
        $totalWorkingHours = 0;
        $totalTime = 0;

        while ($start->lessThan($end)) {
            $currentDay = $start->format('Y-m-d');

            if (!in_array($currentDay, $holidays)) {
                $workStartTime = Carbon::createFromTime($workingStart, 0, 0, $start->timezone)->setDateFrom($start);
                $workEndTime = Carbon::createFromTime($workingEnd, 0, 0, $start->timezone)->setDateFrom($start);
                $lunchStart = Carbon::createFromTime(13, 0, 0, $start->timezone)->setDateFrom($start);
                $lunchEnd = Carbon::createFromTime(13, 30, 0, $start->timezone)->setDateFrom($start);
            
                if ($start->lessThan($workEndTime) && $end->greaterThan($workStartTime)) {
                    $intervalStart = $start->greaterThan($workStartTime) ? $start : $workStartTime;
                    $intervalEnd = $end->lessThan($workEndTime) ? $end : $workEndTime;

                    $intervalMinutes = $intervalStart->diffInMinutes($intervalEnd);

                    if($intervalStart->lessThan($lunchEnd) && $intervalEnd->greaterThan($lunchStart)) 
                    {
                        // $intervalMinutes += 30; // Add 30 minutes for lunch break
                        $overlapStart = $intervalStart->greaterThanOrEqualTo($lunchStart) ? $intervalStart : $lunchStart;
                        $overlapEnd = $intervalEnd->lessThanOrEqualTo($lunchEnd) ? $intervalEnd : $lunchEnd;

                        $lunchMinutes = $overlapStart->diffInMinutes($overlapEnd);
                        $intervalMinutes -= $lunchMinutes;
                    }
                
                    $totalWorkingHours += $intervalMinutes;
                     
                    // $totalWorkingHours += $intervalStart->diffInMinutes($intervalEnd);
                }                
            }

            $start->addDay()->startOfDay();
        }

        return $totalWorkingHours;
    }

    // Recurring Ticket
    public function recurringTicket(Request $request)
    {
        try{            
            $today = now();

            $dailyTickets = DB::table('recurring_tickets')
                            ->leftJoin('recurring_tickets_attachment','recurring_tickets_attachment.RECURRING_ID','=','recurring_tickets.RECURRING_ID')
                            ->where('FREQUENCY', 'Daily')
                            ->where('RECURRING_TILL', '>=', $today->toDateString())
                            ->where('recurring_tickets.IS_ACTIVE','Y')
                            ->whereRaw('DAYOFWEEK(CURDATE()) != 1') // Exclude Sundays
                            ->get();
                            
            $weeklyTickets = DB::table('recurring_tickets')
                                    ->leftJoin('recurring_tickets_attachment','recurring_tickets_attachment.RECURRING_ID','=','recurring_tickets.RECURRING_ID')
                                    ->where('FREQUENCY', 'Weekly')
                                    ->where('RECURRING_TILL', '>=', $today->toDateString())
                                    ->where('WEEKDAY',$today->format('l'))
                                    ->where('recurring_tickets.IS_ACTIVE','Y')
                                    ->get();

            $monthlyTickets = DB::table('recurring_tickets')
                                    ->leftJoin('recurring_tickets_attachment','recurring_tickets_attachment.RECURRING_ID','=','recurring_tickets.RECURRING_ID')
                                    ->where('FREQUENCY', 'Monthly')
                                    ->where('RECURRING_TILL', '>=', $today->toDateString())
                                    ->where('START_DATE',date('Y-m-d'))
                                    ->where('recurring_tickets.IS_ACTIVE','Y')
                                    ->get();

            $recurringTickets = $dailyTickets
                                ->merge($weeklyTickets)
                                ->merge($monthlyTickets);                       

            $tickets = [];
            foreach ($recurringTickets as $recTicket) {                
              
                $result= \DB::select("CALL generate_ticket_no(?, @batchCode)", [$recTicket->PROJECT_ID]);
                $result2 = \DB::select('SELECT @batchCode AS batchCode');
                
                if($result2 && isset($result2[0]->batchCode)) {
                    $serialNumber = $result2[0]->batchCode;  
                    DB::transaction(function () use ($serialNumber, $recTicket) {              
                        $tickets = Ticket::create([
                                        'TICKET_NO'       => $serialNumber,
                                        'TASK_NO'         => 0,
                                        'PROJECT_ID'      => $recTicket->PROJECT_ID,                        
                                        'SUBJECT'         => $recTicket->SUBJECT,
                                        'DESCRIPTION'     => $recTicket->DESCRIPTION,
                                        'PRIORITY'        => $recTicket->PRIORITY,
                                        'TEAM_NAME'       => $recTicket->TEAM_NAME,
                                        'REQUESTED_BY'    => $recTicket->REQUESTED_BY,
                                        'USER_NAME'       => $recTicket->USER_NAME,
                                        'USER_MAIL'       => $recTicket->USER_MAIL,
                                        'DEPARTMENT_CODE' => $recTicket->DEPARTMENT_CODE,
                                        'DEPARTMENT_NAME' => $recTicket->DEPARTMENT_NAME,
                                        'CREATED_BY'      => 'Ticketadmin',
                                        'CREATED_ON'      => now()
                        ]);

                        if($recTicket->ATTACHMENT)
                        {
                            $recAttachment = DB::table('ticket_attachment')->insert([
                                'TICKET_ID'  => $tickets->TICKET_ID,
                                'ATTACHMENT' => $recTicket->ATTACHMENT,
                            ]); 
                        }                     
                    });
                }
            }
            
            return response()->json(['success' => true, 'message' => 'Recurring tickets logged successfully.']);
        } 
        catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => "Error Try Again, "]);
        }
    }
  
    // Get User allocated Asset Id
    public function getUserAssetId(Request $request){
        try {
            if ($request->ticketId) {

                $ticket = Ticket::where('TICKET_ID',$request->ticketId)->first();
                                
                $empId = $ticket->REQUESTED_BY;                               
            
                $client = new Client();
            
                //URL for this request different from Base URL:
                $url = "http://dhananjaya.iskconbangalore.net:8081/assetapi/assets/get-user-allocated-assets";
        
                //Important to keep text/plain
                $request = $client->post(
                    $url,
                    [
                        'headers' => [
                            'Content-Type' => 'text/plain',
                        ],
                        'body' => $empId
                    ]
                );
                // $response = $request->getBody()->getContents();
                $response = $request->getBody();

                $responseData = json_decode($request->getBody(), true);
                return response()->json(['data' => $responseData]);
                
            }
            
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to load assets.'], 500);
        }
    }

    // SLA Notification for before 1h
    public function SLANotify(Request $request)
    {
        try{
            $tickets = Ticket::leftJoin('lkp_task_type','lkp_task_type.TASK_TYPE_ID','=','ticket.TASK_TYPE_ID')
                    ->orderBy('ticket.CREATED_ON','desc')
                    ->whereIn('ticket.PROGRESS', ['Open','In Progress'])
                    ->where('ticket.IS_CLOSED', 'N')
                    ->where('ticket.PROJECT_ID', session('code'))
                    ->select('ticket.TICKET_ID',
                            'ticket.TICKET_NO',
                            'ticket.TECHNICIAN_ID as employeeId',
                            'lkp_task_type.TASK_TYPE_ID',
                            'lkp_task_type.SLA as sla'
                            )
                    ->get(); 

            foreach ($tickets as $val) {   
                $totalTimeConsumed = $this->getTimeLeft(optional($val)->TICKET_ID, optional($val)->TICKET_NO);
                $sla = optional($val)->sla;
                $timeLeft = ($sla*60) - $totalTimeConsumed;
                
                if($timeLeft > 0){
                    if($timeLeft <= "60" && $timeLeft >= "10"){
                        echo optional($val)->TICKET_NO,"-", $timeLeft;  
                        echo "\n";
                        $response = Http::post('https://hr.iskconbangalore.net/v1/api/login/employee-fcmid', [
                            'accessKey'  => '729!#kc@nHKRKkbngsppnsg@491', 
                            'employeeID' =>  optional($val)->employeeId,
                            // Add any parameters required by the API
                        ]);

                        // Check if the request was successful
                        if ($response->successful()) {
                            // API call was successful, handle response
                            $responseData = $response->json(); // Get response data as JSON                

                            // Process the response data
                            $fcmId = $responseData['fcmId'][0]['FCM_ID'];
                            // echo $fcmId; exit;
                            if($fcmId){
                                $body =  optional($val)->TICKET_NO ." - ". $timeLeft." minutes left Hurry !";
                                
                                $title = "SLA Warning !";

                                $currentTime = now()->format('H:i:s');
                                $currentDay = now()->format('l');
                                // $currentTime = Carbon::createFromFormat('H:i:s', '18:00:00')->format('H:i:s'); 
                                // $currentDay = Carbon::createFromFormat('l', 'Wednesday')->format('l');
                                
                                if($currentDay !== 'Sunday' && ($currentTime >= '10:00:00' && $currentTime <= '18:00:00'))
                                {
                                    echo "hello";
                                    $this->sendNotification($fcmId, $body, $title);  
                                }                                
                            }              
                        }
                    }
                }
            }
            return response()->json(['success' => true, 'message' => 'Notification Sent successfully.']);
        }
        catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => "Error please try again."]);
        }
    }

    // Recurring Ticket View
    public function recurringTicketsView(Request $request)
    {
        $api = new TicketsApi;

        $response = $api->getTeams();
        
        // Check if the session code is empty
        if (empty(session('code'))) {
            // If session code is empty, redirect to the home route
            return redirect()->route('home');
        }

        $departmentName = DB::table('department_details')->where('DEPARTMENT_ID',session('code'))->first()->DEPARTMENT_NAME;
        
        $recurringTickets = DB::table('recurring_tickets')
        ->where('PROJECT_ID', session('code'))     
        ->orderBy('recurring_tickets.RECURRING_ID', 'DESC')
        ->select('recurring_tickets.*')
        ->get();
           
       if ($request->ajax()) {
        return Datatables::of($recurringTickets)->addIndexColumn() 
           
            ->addColumn('CREATED_ON', function ($row) {
                
                return date('d-M-Y h:i A',strtotime(optional($row)->CREATED_ON));
            })
            ->addColumn('RECURRING_TILL', function ($row) {
                
                return date('d-M-Y',strtotime(optional($row)->RECURRING_TILL));
            })    
            ->addColumn('START_DATE', function ($row) {
                
                return (optional($row)->START_DATE) ? date('d-M-Y',strtotime(optional($row)->START_DATE)) : '';
            })          
            ->addColumn('action', function ($row) {
                $color = $row->IS_ACTIVE === 'Y' ? 'green' : 'red';
                    // Render the button only if ASSIGNED_ON is null
                    $btn = '<button class="btn tickets-action-btn-transparent" onClick="recurringStatus(' . $row->RECURRING_ID . ', \'' . $row->IS_ACTIVE . '\')" 
                                data-toggle="modal" title="' . ($row->IS_ACTIVE === 'Y' ? 'Inactive' : 'Active') . '">
                                <i class="fas fa-ban" style="font-size: 23px; color: '.$color.'" ></i>
                            </button>';
                                       
                return $btn;
            })
          
            ->rawColumns(['action']) 
            ->make(true);
        }

        return view('recurring-tickets-view',compact('departmentName'))->withTeams($response['data']);
    }

    // Recurring Status Change
    public function recurringStatus(Request $request)
    {
        try{
            $recurring = RecurringTicket::where('RECURRING_ID',$request->recurringId)->first();

            if($recurring->IS_ACTIVE == 'Y') {           
                $recurring->IS_ACTIVE = 'N';
                $recurring->save();

                $msg='Status Disabled';

                $arr = [
                    'error' => false,
                    'msg' => $msg,
                ];
                return json_encode($arr);
            }            
            else{
                $recurring->IS_ACTIVE = 'Y';
                $recurring->save();

                $msg='Status Enabled';

                $arr = [
                    'error' => false,
                    'msg' => $msg,
                ];
                return json_encode($arr);
            }            
        }
        catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => "Error please try again."]);
        }
    }

    // Pending Tickets Report
    public function pendingTicketsReport(Request $request)
    {
        try{       
            
            $departments = DB::table('department_details')
                                    ->whereIn('DEPARTMENT_ID',[1,4])
                                    // ->orderBy('DEPARTMENT_ID','desc')
                                    ->get();    

            foreach($departments as $val){
                $teamIds = DB::table('team')
                            ->where('DEPARTMENT_ID', $val->DEPARTMENT_ID)
                            ->pluck('TEAM_ID')
                            ->toArray();               

                // Fetch tickets excluding 'Temporary Issues' for technician-specific counts
                $tickets = DB::table('ticket')
                    ->distinct()
                    ->leftJoin('mstr_users as users', 'ticket.TECHNICIAN_ID', '=', 'users.EMPLOYEE_ID')
                    ->leftJoin('lkp_item_type', 'ticket.ITEM_TYPE_ID', '=', 'lkp_item_type.ITEM_TYPE_ID')
                    ->leftJoin('lkp_sub_category', 'ticket.SUB_CATEGORY_ID', '=', 'lkp_sub_category.SUB_CATEGORY_ID')
                    ->leftJoin('lkp_category', 'ticket.CATEGORY_ID', '=', 'lkp_category.CATEGORY_ID')
                    ->leftJoin('lkp_item', 'ticket.ITEM_ID', '=', 'lkp_item.ITEM_ID')
                    ->leftJoin('lkp_task_type', 'ticket.TASK_TYPE_ID', '=', 'lkp_task_type.TASK_TYPE_ID')
                    ->leftJoin('team', 'team.TEAM_NAME', '=', 'ticket.TEAM_NAME')
                    ->leftJoin('map_user_team', 'map_user_team.TEAM_ID', '=', 'team.TEAM_ID')  
                    ->where('PROJECT_ID',$val->DEPARTMENT_ID)
                    ->where('IS_CLOSED', 'N')
                    ->whereIn('PROGRESS', ['Open', 'New', 'In Progress', 'On Hold'])
                    ->orderBy('ticket.TEAM_NAME', 'asc')
                    ->orderBy('users.USER_NAME', 'asc')
                    ->select('ticket.*','users.USER_NAME as TECHNICIAN_NAME', 
                            'lkp_category.DISPLAY_NAME as CATEGORY', 
                            'lkp_sub_category.DISPLAY_NAME as SUB_CATEGORY', 
                            'lkp_item_type.DISPLAY_NAME as ITEM_TYPE',
                            'lkp_item.DISPLAY_NAME as ITEM',
                            'lkp_task_type.DISPLAY_NAME as ticketType')
                    ->get();         
                

                $totalTickets = $tickets->count();

                $ticketIds = $tickets->pluck('TICKET_ID');

                // Fetch last updates for these tickets
                $lastUpdates = DB::table('ticket_updates')
                    ->select('TICKET_ID', 'DESCRIPTION')
                    ->whereIn('TICKET_ID', $ticketIds)
                    ->orderBy('TICKET_UPDATE_ID', 'desc')
                    ->get()
                    ->groupBy('TICKET_ID')
                    ->map(function ($updates) {
                        return $updates->first();
                    });

                $technicians = User::join('map_user_department', 'mstr_users.USER_ID', '=', 'map_user_department.USER_ID')
                    ->leftJoin('team_members', 'map_user_department.USER_ID', '=', 'team_members.TECHNICIAN')
                    ->leftJoin('team','team.TEAM_ID','=','team_members.TEAM_ID')
                    ->where('map_user_department.DEPARTMENT_ID', $val->DEPARTMENT_ID)               
                    ->where('map_user_department.ROLE','Technician')
                    ->where('team_members.IS_ACTIVE','Y')
                    ->select('mstr_users.EMPLOYEE_ID','mstr_users.USER_NAME')
                    ->whereIn('team_members.TEAM_ID', $teamIds)
                    ->orderBy('mstr_users.USER_NAME', 'asc')
                    ->distinct()
                    ->get();
                

                // Compose email subject
                $subject = "Pending Tickets";

                // Compose email body
                $body = "<html>
                    <head>
                        <meta name='viewport' content='width=device-width, initial-scale=1'>
                        <style>  
                            .table-container {
                                width: 50%;
                                font-family: sans-serif;
                            }
                            /* Mobile (max-width: 600px) */
                            @media only screen and (max-width: 600px) {                           
                                .table-container {
                                    width: 50%;
                                    padding: 10px;
                                }
                            }
                            @media only screen and (max-width: 450px) {                            
                                .table-container {
                                    width: 100%;
                                    padding: 10px;
                                }
                            }
                        </style>
                    </head>
                    <body>";
                $body .= "<p style='font-size: 15px;'>Hare Krishna Prabhu,</p>";
                $body .= "<p style='font-size: 15px;'>The following are pending tickets.<br></p>";

                $body .= "<div class='table-container'>";

                // Technicians Total Pending Tickets Table
                $body .= "<table class='table table-hover table-striped tickets-main-table'
                    border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; text-align: left; width: 100%;'>";

                $body .= "<thead>";
                $body .= "<tr style='background-color: rgb(141 210 228);font-size: 12px;font-family: sans-serif;'>
                            <th>Sl No</th>
                            <th>Technicians</th>
                            <th>Pending Tickets</th>
                            <th>Pending Since</th>
                        </tr>";
                $body .= "</thead><tbody style='font-size:12px;font-family:sans-serif;'>";

                // Add technicians rows
                foreach ($technicians as $index => $technician) {

                    $technicianTickets  = $tickets->where('TECHNICIAN_ID', $technician->EMPLOYEE_ID)
                                                ->where('ticketType', '!=', 'Temporary Issues');
                        
                    $pendingTickets = $technicianTickets->count();

                    // Find the oldest ticket's CREATED_ON date
                    $oldestPendingDate = $technicianTickets->min('CREATED_ON');
                    $formattedOldestDate = $oldestPendingDate ? date('d-M-Y h:i A', strtotime($oldestPendingDate)) : '';

                    $body .= "<tr>
                                <td>" . ($index + 1) . "</td>
                                <td>{$technician->USER_NAME}</td>
                                <td>{$pendingTickets}</td> 
                                <td>{$formattedOldestDate}</td>
                            </tr>";
                }

                // Unassigned Tickets
                $unassignedTicketsByTeam = $tickets->where('TECHNICIAN_ID', null)
                                                    ->groupBy('TEAM_NAME');

                foreach ($unassignedTicketsByTeam as $teamName => $teamTickets) {

                    $oldestPendingDate = $teamTickets->min('CREATED_ON');
                    $formattedOldestDate = $oldestPendingDate ? date('d-M-Y h:i A', strtotime($oldestPendingDate)) : '';

                    $teamName = $teamName ? : 'No Team';

                    $newStageTickets = $teamTickets->count();
                    $body .= "<tr>
                                <td colspan='2' style='text-align: center;'>Unassigned ({$teamName})</td>
                                <td>{$newStageTickets}</td>
                                <td>{$formattedOldestDate}</td>
                            </tr>";
                }

                // Temporary Issue Tickets
                $temporayTickets = $tickets->where('ticketType','Temporary Issues')
                                                ->count();

                $exceptionTickets = $totalTickets - $temporayTickets;

                $body .="<tr style='font-weight: 600;'>
                            <td colspan='2' style='text-align: center;'>Total</td>
                            <td>{$exceptionTickets}</td>
                        </tr>";

                $body .= "</tbody></table><br>";
                $body .="</div>";

                if($temporayTickets > 0){
                    $body .="<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 50%; text-align: left;'>
                            <tr>
                                <td colspan='2' style='text-align: center;'>Temporary Issue Tickets</td>
                                <td>{$temporayTickets}</td>
                            </tr>
                        </table>";
                }
                
                        
                $body .= "<p style='font-size: 15px;'>Ticket details:<br></p>";         
                

                // Group tickets by team name while excluding 'Temporary Issues'
                $groupedTickets = $tickets->where('ticketType', '!=', 'Temporary Issues')->groupBy('TEAM_NAME');

                // Iterate over each team group to create a separate table
                foreach ($groupedTickets as $teamName => $teamTickets) {
                    $teamName = $teamName ? : 'Unassigned Team';
                    // Add team header
                    $body .= "<h5 style='text-align: left; font-family: sans-serif; color: #000;'>{$teamName} Tickets:</h5>";

                    //Pending Tickets List  Table
                    $body .= "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%; text-align: left;'>";
                    $body .= "<thead>";
                    $body .= "<tr style='background-color: rgb(141 210 228);font-size: 12px;font-family: sans-serif;'>
                                <th>Sl No</th>
                                <th>Ticket No</th>
                                <th>Requester Name</th>
                                <th style='width: 150px;'>Subject</th>
                                <th>Created Date</th>
                                <th>Assigned On</th>
                                <th>Team Name</th>
                                <th>Assigned To</th>
                                <th>Ticket Type</th>
                                <th>Task Level Status</th>
                                <th>Progress</th>
                                <th>Last Work Update</th>
                            </tr>";
                    $body .= "</thead><tbody style='font-size:12px;font-family:sans-serif;'>";
                    
                    // Add ticket rows
                    foreach ($teamTickets as $index => $ticket) {

                        $lastUpdate = $lastUpdates->get($ticket->TICKET_ID);
                        $lastWorkUpdate = $lastUpdate ? $lastUpdate->DESCRIPTION : '';

                        $taskNo = '';

                        if($ticket->TASK_NO != 0)
                        {
                            $taskNo = '-'.$ticket->TASK_NO;
                        }
                        $ticketNumber = $ticket->TICKET_NO .''.$taskNo.'';

                        $assignedOn = $ticket->ASSIGNED_ON ? date('d-M-Y h:i A', strtotime($ticket->ASSIGNED_ON)) : '';
                                    
                        $body .= "<tr>
                                    <td>" . ($index + 1) . "</td>
                                    <td>{$ticketNumber}</td>
                                    <td>{$ticket->USER_NAME}</td>
                                    <td>{$ticket->SUBJECT}</td>
                                    <td>" . date('d-M-Y h:i A', strtotime($ticket->CREATED_ON)) . "</td>
                                    <td>" . $assignedOn . "</td>
                                    <td>{$ticket->TEAM_NAME}</td>
                                    <td>{$ticket->TECHNICIAN_NAME}</td>
                                    <td>{$ticket->ticketType}</td>
                                    <td>{$ticket->STATUS}</td>
                                    <td>{$ticket->PROGRESS}</td>
                                    <td>{$lastWorkUpdate}</td>
                                </tr>";
                    }

                    $body .= "</tbody></table><br>";
                }

                // Temporary Issue Tickets Details
                // Group tickets by team name while including 'Temporary Issues'
                $tempoTicketsDet = $tickets->where('ticketType','Temporary Issues')->values();

                if ($tempoTicketsDet->isNotEmpty()) {

                    $body .= "<h5 style='text-align: left; font-family: sans-serif; color: #000;'>Temporary Issue Tickets:</h5>";

                    $body .= "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%; text-align: left;'>";
                    $body .= "<thead>";
                    $body .= "<tr style='background-color: rgb(141 210 228);font-size: 12px;font-family: sans-serif;'>
                                <th>Sl No</th>
                                <th>Ticket No</th>
                                <th>Requester Name</th>
                                <th style='width: 150px;'>Subject</th>
                                <th>Created Date</th>
                                <th>Assigned On</th>
                                <th>Team Name</th>
                                <th>Assigned To</th>
                                <th>Ticket Type</th>
                                <th>Task Level Status</th>
                                <th>Progress</th>
                                <th>Last Work Update</th>
                            </tr>";
                    $body .= "</thead><tbody style='font-size:12px;font-family:sans-serif;'>";
               
                    // Add ticket rows
                    foreach ($tempoTicketsDet as $index => $ticket) {

                        $lastUpdate = $lastUpdates->get($ticket->TICKET_ID);
                        $lastWorkUpdate = $lastUpdate ? $lastUpdate->DESCRIPTION : '';

                        $taskNo = '';

                        if($ticket->TASK_NO != 0)
                        {
                            $taskNo = '-'.$ticket->TASK_NO;
                        }
                        $ticketNumber = $ticket->TICKET_NO .''.$taskNo.'';

                        $assignedOn = $ticket->ASSIGNED_ON ? date('d-M-Y h:i A', strtotime($ticket->ASSIGNED_ON)) : '';
                                    
                        $body .= "<tr>
                                    <td>" . ($index + 1) . "</td>
                                    <td>{$ticketNumber}</td>
                                    <td>{$ticket->USER_NAME}</td>
                                    <td>{$ticket->SUBJECT}</td>
                                    <td>" . date('d-M-Y h:i A', strtotime($ticket->CREATED_ON)) . "</td>
                                    <td>" . $assignedOn . "</td>
                                    <td>{$ticket->TEAM_NAME}</td>
                                    <td>{$ticket->TECHNICIAN_NAME}</td>
                                    <td>{$ticket->ticketType}</td>
                                    <td>{$ticket->STATUS}</td>
                                    <td>{$ticket->PROGRESS}</td>
                                    <td>{$lastWorkUpdate}</td>
                                </tr>";
                    }
                }                

                $body .= "</tbody></table><br>"; 

                $body .= "<p style='font-size: 15px;'>Thanks & regards,<br></p>";
                $body .= "<p style='font-size: 15px;font-weight:600;'>$val->DEPARTMENT_NAME<br><br></p>";
                $body .= "<p style='font-size: 15px;'>Note: This is an auto-generated email from our ticketing system.</p>"; 
                $body .= "</body></html>";                              
                         
                      
                $recipients = [];
                if (!empty($val->REPORT_EMAIL_IDS)) {
                    $recipients = array_map('trim', explode(',', $val->REPORT_EMAIL_IDS));
                }                  

                require_once base_path('public/PHPMailer/PHPMailerAutoload.php');
 
                $mailTo = $recipients;
         
                // $mailBCC = "samskriti.entries@iskconbangalore.net";
                $mail = new \PHPMailer;
                // $mail->SMTPDebug = 4;                               // Enable verbose debug output
                $mail->isSMTP();                                      // Set mailer to use SMTP
                $mail->Host = $val->MAIL_HOST;  // Specify main and backup SMTP servers
                $mail->SMTPAuth = true;                               // Enable SMTP authentication
                $mail->Username = $val->MAIL_USERNAME;          
                $mail->Password = $val->MAIL_PASSWORD;
                $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
                $mail->Port = 587;                                    // TCP port to connect to
                $mail->setFrom($val->SUPPORT_EMAIL_ID, $val->DISPLAY_NAME);
                // $mail->addAddress($mailTo);
                foreach ($mailTo as $address) {
                    if (!empty($address)) {
                        $mail->addAddress($address);
                    }
                }
                $mail->isHTML(true);                                // Set email format to HTML
                $mail->Subject = $subject;
                $mail->Body    = $body;
                // $mail->AltBody = strip_tags($body);
                $mail->send();                 
               
            } 
            
            return response()->json(['success' => true, 'message' => 'Pending Tickets Reports Sent Successfully.']);
        }
        catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => "Error Please Try Again",]);
        }
    }

    // Event Ticket Closer
    public function eventTicketCloser(Request $rquest)
    {
        $result = \DB::select("CALL event_ticket_closer()");   
        if($result){
            echo "Called";
        }
        else{
            echo "Failed !";
        }
    }    
}