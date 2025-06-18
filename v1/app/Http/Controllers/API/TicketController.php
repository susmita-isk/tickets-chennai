<?php

namespace App\Http\Controllers\API;

use File;
use DateTime;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Ticket;
use App\Models\Category;
use App\Models\ItemType;
use App\Models\Attachment;
use App\Models\Department;
use App\Models\HolidayList;
use App\Models\SubCategory;
use App\Models\TaskType;
use App\Models\TicketUpdate;
use App\Models\TicketPoints;
use Illuminate\Http\Request;
use App\Mail\TicketClosedMail;
use App\Mail\TicketCreatedMail;
use App\Models\PredefinedTasks;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Google_Client;
use GuzzleHttp\Client; 

class TicketController extends Controller
{
    private $accessKey;

    public function __construct()
    {
        $this->accessKey =  '729!#kc@nHKRKkbngsppnsg@491';
    }
    
    public function getTickets(Request $request)
    {
        try {
            $data = [];

            $tickets = Ticket::query()
                       ->leftJoin('mstr_users','ticket.TECHNICIAN_ID','=','mstr_users.EMPLOYEE_ID')
                       ->where('PROJECT_ID',  $request->departmentCode)
                       ->where('IS_CLOSED',  'N')
                       ->orderBy('TICKET_ID','DESC')
                       ->select('ticket.*','mstr_users.USER_NAME as TECHNICIAN_NAME')
                       ->get();

            foreach ($tickets as $key => $value) {
                $data[$key] = [
                    'ticketId'         => optional($value)->TICKET_ID,
                    'ticketNumber'     => optional($value)->TICKET_NO,
                    'subject'          => optional($value)->SUBJECT,
                    'attachment'       => optional($value)->ATTACHMENT,
                    'requester'        => optional($value)->USER_NAME,
                    'requestedOn'      => date('d-M-Y h:i A',strtotime(optional($value)->CREATED_ON)),
                    'department'       => optional($value)->DEPARTMENT_NAME,
                    'assignedTo'       => optional($value)->TECHNICIAN_NAME,
                    'status'           => optional($value)->STATUS,
                    'priority'         => optional($value)->PRIORITY, 
                ];
            }

            $this->apiResponse['successCode']  = 1;
            $this->apiResponse['message']      = 'Successful';
            $this->apiResponse['data']         = $data;

            return response()->json($this->apiResponse);
            
        } catch (\Exception $e) {

            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error ! Please Try Again';
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }
    public function storeTicket(Request $request)
    {
        try {
            $data = [];

            $departmentCode = Department::find($request->departmentCode)->DEPARTMENT_CODE;

            $year = now()->year;

            // Get the last ID from the database
            $lastId = Ticket::latest('TICKET_ID')->value('TICKET_ID') ?? 0;

            // Increment the last ID
            $lastId++;

            $last4 = str_pad($lastId, 4, '0', STR_PAD_LEFT);

            // Generate the serial number
            $serialNumber = "{$departmentCode}{$year}{$last4}";

            $ticket = new Ticket;

            $ticket->TICKET_NO       = $serialNumber;
            $ticket->TASK_NO         = 0;
            $ticket->PROJECT_ID      = $request->departmentCode;
            $ticket->MODE            = $request->mode;
            $ticket->SUBJECT         = $request->subject; 
            $ticket->DESCRIPTION     = $request->description; 
            $ticket->PRIORITY        = $request->priority;
            $ticket->REQUESTED_BY    = $request->employeeId;
            $ticket->USER_NAME       = $request->employeeName;
            $ticket->USER_MAIL       = $request->employeeMail;
            $ticket->DEPARTMENT_CODE = $request->code;
            $ticket->DEPARTMENT_NAME = $request->departmentName;
            $ticket->CREATED_BY      = $request->userId;
            $ticket->CREATED_ON      = now();

            $ticket->save();

            $yourCollection = request()->all();

            // Filter the collection to include only items starting with "attachments"
            $filteredCollection = collect($yourCollection)->filter(function ($value, $key) {
                return strpos($key, 'attachments') === 0;
            });

            // Convert the filtered collection to a plain array
            $attachmentsArray = $filteredCollection->toArray();

            $iterationNumber = 0; // Initialize the iteration number

            foreach($attachmentsArray as $value)
            {
                $attachment = new Attachment;

                $path = $value['name'];

                // Use pathinfo to get the file extension
                $extension = pathinfo($path, PATHINFO_EXTENSION);

                $imageName = $ticket->TICKET_NO . '_' .time() . '_' . $iterationNumber . '.' . $extension;

                $attachment->TICKET_ID  = $ticket->TICKET_ID;
                $attachment->ATTACHMENT = $imageName;

                $attachment->save();

                $data['attachments'][$iterationNumber] = $imageName;

                // Increment the iteration number
                $iterationNumber++;
            }

            Mail::to($request->employeeMail)->send(new TicketCreatedMail());            

            $this->apiResponse['successCode']  = 1;
            $this->apiResponse['message']      = 'Successful';
            $this->apiResponse['data']         = $data;

            return response()->json($this->apiResponse);
            
        } catch (\Exception $e) {

            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error ! Please Try Again';
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }
    public function updateTicket(Request $request)
    {
        try {
            $data = [];
          
            $ticket = Ticket::find($request->ticketId);

            $ticket->MODE            = $request->mode;
            $ticket->SUBJECT         = $request->subject; 
            $ticket->DESCRIPTION     = $request->description; 
            $ticket->PRIORITY        = $request->priority;
            $ticket->REQUESTED_BY    = $request->filled('employeeId') ? $request->employeeId :  $ticket->REQUESTED_BY;
            $ticket->USER_NAME       = $request->filled('employeeName') ? $request->employeeName : $ticket->USER_NAME;
            $ticket->DEPARTMENT_CODE = $request->code;
            $ticket->DEPARTMENT_NAME = $request->departmentName;
            $ticket->MODIFIED_BY      = $request->userId;
            $ticket->MODIFIED_ON      = now();

            $ticket->save();

            $this->apiResponse['successCode']  = 1;
            $this->apiResponse['message']      = 'Successful';
            $this->apiResponse['data']         = $data;

            return response()->json($this->apiResponse);
            
        } catch (\Exception $e) {

            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error ! Please Try Again';
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }
    public function getTicket(Request $request)
    {
        try {
            $data = [];          
            $value =   Ticket::query()
                    ->leftJoin('mstr_users', 'ticket.TECHNICIAN_ID', '=', 'mstr_users.EMPLOYEE_ID')
                    ->leftJoin('lkp_item_type', 'ticket.ITEM_TYPE_ID', '=', 'lkp_item_type.ITEM_TYPE_ID')
                    ->leftJoin('lkp_sub_category', 'ticket.SUB_CATEGORY_ID', '=', 'lkp_sub_category.SUB_CATEGORY_ID')
                    ->leftJoin('lkp_category', 'ticket.CATEGORY_ID', '=', 'lkp_category.CATEGORY_ID')
                    ->leftJoin('lkp_item', 'ticket.ITEM_ID', '=', 'lkp_item.ITEM_ID')
                    ->leftJoin('lkp_task_type', 'lkp_task_type.TASK_TYPE_ID', '=',  'ticket.TASK_TYPE_ID')
                    ->leftJoin('ticket_updates', 'ticket_updates.TICKET_ID', '=',  'ticket.TICKET_ID')
                    ->where('ticket.TICKET_ID', request('ticketId'))
                    ->select(
                        'ticket.*',
                        'mstr_users.USER_NAME as TECHNICIAN_NAME',
                        'lkp_category.DISPLAY_NAME as CATEGORY_NAME', // Replace with the actual column name in lkp_category
                        'lkp_sub_category.DISPLAY_NAME as SUB_CATEGORY_NAME', // Replace with the actual column name in lkp_sub_category
                        'lkp_item_type.DISPLAY_NAME as ITEM_TYPE_NAME', // Replace with the actual column name in lkp_item_type
                        'lkp_item.DISPLAY_NAME as ITEM_NAME',
                        'lkp_task_type.DISPLAY_NAME as ticketType',
                        'lkp_task_type.SLA as sla'
                    )
                    ->first();

            $totalTimeConsumed = $this->getTimeLeft(request('ticketId'), $value->PROJECT_ID);

            $sla = optional($value)->sla;
            $slaBreach = 'N';
            if($sla){
                $slaBreach = ($totalTimeConsumed > ($sla * 60)) ? 'Y' : 'N';
            }                       

            $data[] = [
                'ticketId'      => optional($value)->TICKET_ID,
                'ticketNumber'  => optional($value)->TICKET_NO,
                'taskNo'        => optional($value)->TASK_NO,
                'subject'       => optional($value)->SUBJECT,
                'description'   => optional($value)->DESCRIPTION,
                'requester'     => optional($value)->USER_NAME,
                'department'    => optional($value)->DEPARTMENT_NAME,
                'status'        => optional($value)->STATUS,
                'priority'      => optional($value)->PRIORITY,
                'progress'      => optional($value)->PROGRESS,
                'employeeId'    => optional($value)->REQUESTED_BY,
                'description'   => optional($value)->DESCRIPTION,
                'mode'          => optional($value)->MODE,
                'team'          => optional($value)->TEAM_NAME,
                'ticketType'    => optional($value)->ticketType,
                'sla'           => optional($value)->sla,
                'slaBreach'     => $slaBreach, 
                'timeConsumed'  => optional($value)->TIME_CONSUME,
                'idleTime'      => optional($value)->IDLE_TIME,
                'category'      => optional($value)->CATEGORY_NAME,
                'subCategory'   => optional($value)->SUB_CATEGORY_NAME,
                'itemType'      => optional($value)->ITEM_TYPE_NAME,
                'item'          => optional($value)->ITEM_NAME,
                'asset'         => optional($value)->ASSET_ID,
                'trust'         => optional($value)->TRUST_CODE,
                'technician'    => optional($value)->TECHNICIAN_NAME,
                'createdBy'     => optional($value)->CREATED_BY,
                'createdOn'     => is_null(optional($value)->CREATED_ON) ? ''  :date('d-M-Y h:i A',strtotime(optional($value)->CREATED_ON)),
                'assignedBy'     => optional($value)->ASSIGNED_BY,
                'assignedOn'    => is_null(optional($value)->ASSIGNED_ON) ? '' :date('d-M-Y h:i A',strtotime(optional($value)->ASSIGNED_ON)),
                'dueDate'       => is_null(optional($value)->DUE_DATE) ? '' :date('d-M-Y h:i A',strtotime(optional($value)->DUE_DATE)),
                'closedBy'     => optional($value)->CLOSED_BY,
                'closedOn'    => is_null(optional($value)->CLOSED_ON) ? '' :date('d-M-Y h:i A',strtotime(optional($value)->CLOSED_ON)),
                'feedbackPoint'  => optional($value)->FEEDBACK_POINT == 0 ? '' : optional($value)->FEEDBACK_POINT,
                'feedbackRemarks' => optional($value)->FEEDBACK_REMARKS,
                'feedbackDate'   => optional($value)->FEEDBACK_ON ? date('d-M-Y h:i A',strtotime(optional($value)->FEEDBACK_ON)) : '',
            ];
            
            $attachmentsArray = Attachment::where('TICKET_ID',$value->TICKET_ID)->where('IS_ACTIVE','Y')->get();

            foreach($attachmentsArray as $iterationNumber => $value)
            {                    
                $data['attachments'][$iterationNumber] = $value;    
            }

            $subtasks = Ticket::where('LINKED_TO',request('ticketId'))->get(['TICKET_ID','TICKET_NO','TASK_NO']);

            foreach($subtasks as $iterationNumber => $value)
            {                    
                $data['subtasks'][$iterationNumber] = $value;    
            }
            
            $this->apiResponse['successCode']  = 1;
            $this->apiResponse['message']      = 'Successfully Get Ticket Details';
            $this->apiResponse['data']         = $data;

            return response()->json($this->apiResponse);
            
        } catch (\Exception $e) {

            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error ! Please Try Again';
            $this->apiResponse['data'] = [];

            return response()->json($e->getMessage());
        }
    }
    public function assignTicket(Request $request)
    {
        try {
            $data = []; 

            $ticket = Ticket::find($request->ticketId); 

            $assignedOn = ($ticket->ASSIGNED_ON) ? $ticket->ASSIGNED_ON : now();
            // $assignedOn = date('Y-m-d H:i:s', strtotime('2024-10-10 17:00:00'));

            $technician = ($request->technicianId) ? $request->technicianId : $request->empId;
            
            // $result = $this->calculateSLA($assignedOn, $request->taskType,  $technician);

            // Extract variables from the result
            $slaDeadline = now();
            $idleTime = null;
          
            $ticket->TECHNICIAN_ID    =  $technician;
            $ticket->ASSIGNED_ON      = $assignedOn;
            $ticket->ASSIGNED_BY      = $request->userName;
            $ticket->DUE_DATE         = $slaDeadline;
            $ticket->CATEGORY_ID      = $request->categoryId;
            $ticket->SUB_CATEGORY_ID  = $request->subcategoryId;
            $ticket->ITEM_TYPE_ID     = $request->itemTypeId;
            $ticket->ITEM_ID          = $request->itemId;
            $ticket->ASSET_ID         = $request->assetId;
            $ticket->TASK_TYPE_ID     = $request->taskType;
            $ticket->TEAM_NAME        = $request->teamName;
            $ticket->IDLE_TIME        = null;
            $ticket->STATUS           = 'Open';
            $ticket->PROGRESS         = strtoLower($ticket->PROGRESS) =='new' ? 'In Progress' : $ticket->PROGRESS;
            $ticket->MODIFIED_BY      = $request->userName;
            $ticket->MODIFIED_ON      = now();
        
            $ticket->save();            

            // Log ticket movement
            $logTicketMovement = DB::table('log_ticket_movement')->insert([
                'TICKET_ID' => $ticket->TICKET_ID,
                'ALLOCATED_TO' =>  $technician,
                'ALLOCATED_BY' => $request->userName,
                'ALLOCATED_ON' => now(),
            ]);
            
            // Log status movement
            $logStatusMovement = DB::table('log_status_movement')->insert([
                'TICKET_ID' => $ticket->TICKET_ID,
                'CHANGED_TO' =>$ticket->PROGRESS,
                'CHANGED_BY' => $request->userName,
                'CHANGED_ON' => now(),
            ]);


            //////////////////////////////////////////////////////

            // Negative points to team members if ($ticket->CREATED_ON - $ticket->ASSIGNED_ON) > SLA       
            //if($ticket->TICKET_ID not in TicketPoints::pluck('TICKET_ID')->toArray()
            if(!DB::table('breached_tickets_points')->where('TICKET_ID', $ticket->TICKET_ID)->exists()){
                $slaOn = DB::table('department_details')
                                ->where('DEPARTMENT_ID', $ticket->PROJECT_ID)
                                ->first();           

                if ($slaOn && $slaOn->SLA_ON === 'CREATED_ON') {
                    $ticketPoints = TaskType::where('TASK_TYPE_ID', $ticket->TASK_TYPE_ID)->first();
                    $sla = ($ticketPoints->SLA)*60; //0

                    if($sla!== 0){
                        $createdOn = Carbon::parse($ticket->CREATED_ON);
                        $firstChangedOn = Carbon::parse($ticket->ASSIGNED_ON);

                        if ($createdOn->lessThan($firstChangedOn)) {
                            $workingStart = 10; // Start of working hours (10 AM)
                            $workingEnd = 18;   // End of working hours (6 PM)
                            $holidays = HolidayList::pluck('HOLIDAY')->toArray(); // Retrieve holiday list

                            $timeDifference = $this->calculateWorkingHours($createdOn, $firstChangedOn, $workingStart, $workingEnd, $holidays); 
                            
                            if($timeDifference > $sla) {
                                // Get team members who are eligible for negative points
                                $teamMembers = User::join('team_members', 'mstr_users.USER_ID', '=', 'team_members.TECHNICIAN')
                                                    ->join('team','team.TEAM_ID','=','team_members.TEAM_ID')  
                                                    ->where('team_members.IS_ACTIVE','Y')
                                                    ->where('team.TEAM_NAME', $ticket->TEAM_NAME)
                                                    ->where('team_members.IS_ELIGIBLE_FOR_NEGATIVE_POINTS', 'Y')
                                                    ->where('mstr_users.EMPLOYEE_ID', '!=', $ticket->TECHNICIAN_ID)
                                                    ->where('mstr_users.ACTIVE_FLAG', 'Y')
                                                    ->select('mstr_users.EMPLOYEE_ID','mstr_users.USER_NAME','mstr_users.USER_ID')
                                                    ->get();
                
                                foreach($teamMembers as $member)
                                {
                                    DB::table('breached_tickets_points')->insert([
                                        'TECHNICIAN_ID' => $member->EMPLOYEE_ID,
                                        'TICKET_ID' => $request->ticketId,                                       
                                        'POINTS' => -($ticketPoints->POINTS),
                                        'CREATED_ON' => now(),
                                        'CREATED_BY' => $request->userName,
                                        
                                    ]);
                                }
                            }
                        }
                    }                
                }
            }
           
            /////////////////////////////////////////////////////////////////


            $user_id = User::where(['USER_ID' => $request->userId])->first();        
           
            // Prepare the response
            $this->apiResponse['successCode']  = 1;
            $this->apiResponse['message']      = 'Successful';
            $this->apiResponse['data']         = $ticket;

            // Send the JSON response first
            $jsonResponse = response()->json($this->apiResponse);

            $mailFrom = DB::table('department_details')
                                ->where('DEPARTMENT_ID', $request->departmentId)
                                ->first();
            
            $isEmployee = User::where('IS_EMPLOYEE', 'Y')->where('EMPLOYEE_ID', $technician)->first();

            if(!$isEmployee){
                $user = User::where('EMPLOYEE_ID', $technician)->first()->USER_NAME;

                // Compose email subject
                $subject = "Request ID ## {$ticket->TICKET_NO} ## Assigned";

                // Compose email body
                $body  = "<!DOCTYPE html> <html><body>  ";
                $body .= "<p>Hare Krishna {$user},</p>";
                $body .= "<p>The following Ticket Number ## {$ticket->TICKET_NO} ## is assigned to you.<br><br>";
                $body .= "Requested by: {$ticket->USER_NAME}<br>";
                // $body .= "Assigned To:  {$user}<br>";
                $body .= "Title: {$ticket->SUBJECT}<br>";

                // Convert newlines to <br> and preserve formatting
                $descriptionFormatted = nl2br(e($ticket->DESCRIPTION));
                $body .= "Description:<br>{$descriptionFormatted}</p>";

                // $body .= "Description: {$ticket->DESCRIPTION}</p>";
                $body .= "<p>Kind Regards,<br>{$mailFrom->DISPLAY_NAME} Team<br><br>";
                $body .= "Note: This is an auto-generated email from our ticketing system.</p>"; 
                $body .= "</body></html>";

                $mailId = User::where('EMPLOYEE_ID', $technician)->first()->EMAIL;

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

                    // Send email
                    Mail::html($body, function ($message) use ($subject, $ticket, $mailId, $mailFrom) {
                        $message->from($mailFrom->SUPPORT_EMAIL_ID, $mailFrom->DISPLAY_NAME)
                        ->to($mailId)->subject($subject);
                    });
                }
            }
            else{

                $response = Http::post('https://hr.iskconbangalore.net/v1/api/login/employee-fcmid', [
                    'accessKey'  => '729!#kc@nHKRKkbngsppnsg@491', 
                    'employeeID' =>  $technician
                ]);

                // Check if the request was successful
                if ($response->successful()) {
                    // API call was successful, handle response
                    $responseData = $response->json(); // Get response data as JSON
                    
                    $fcmId = $responseData['fcmId'][0]['FCM_ID'];

                    $ticketUserName = $ticket->USER_NAME;
                    $ticketDepartmentName = $ticket->DEPARTMENT_NAME;

                    $formattedName = $ticketUserName . " (" . $ticketDepartmentName . ")";

                    $body =  $ticket->TICKET_NO ." - ". $formattedName ." - " .$ticket->SUBJECT;
                    
                    $title = "Ticket Assigned to you";

                    if($fcmId)
                    {
                        $this->sendNotification($fcmId, $body , $title);
                    }

                } else {
                    
                    // API call failed
                    $statusCode = $response->status(); // Get HTTP status code
                    $data[] = $statusCode;
                }

                $user = User::where('EMPLOYEE_ID', $technician)->first()->USER_NAME;

                // Compose email subject
                $subject = "Request ID ## {$ticket->TICKET_NO} ## Assigned";

                // Compose email body
                $body  = "<!DOCTYPE html> <html><body>  ";
                $body .= "<p>Hare Krishna {$user},</p>";
                $body .= "<p>The following Ticket Number ## {$ticket->TICKET_NO} ## is assigned to you.<br><br>";
                $body .= "Requested by: {$ticket->USER_NAME}<br>";
                // $body .= "Assigned To:  {$user}<br>";
                $body .= "Title: {$ticket->SUBJECT}<br>";

                // Convert newlines to <br> and preserve formatting
                $descriptionFormatted = nl2br(e($ticket->DESCRIPTION));
                $body .= "Description:<br>{$descriptionFormatted}</p>";

                // $body .= "Description: {$ticket->DESCRIPTION}</p>";
                $body .= "<p>Kind Regards,<br>{$mailFrom->DISPLAY_NAME} Team<br><br>";
                $body .= "Note: This is an auto-generated email from our ticketing system.</p>"; 
                $body .= "</body></html>";           
                               
                    
                // $mailId = $responseData['data'][0]['emailId'];
                $mailId = User::where('EMPLOYEE_ID', $technician)->first()->EMAIL;

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

                    // Send email
                    Mail::html($body, function ($message) use ($subject, $ticket, $mailId, $mailFrom) {
                        $message->from($mailFrom->SUPPORT_EMAIL_ID, $mailFrom->DISPLAY_NAME)
                            ->to($mailId)
                            ->subject($subject);
                    });
                }
                
            }
           
            modifyLogActivity('Assign Ticket',$request->ticketId,'TICKET ID',$request->userName);

            // Return the response
            return $jsonResponse;            
            
        } catch (\Exception $e) {

            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error ! Please Try Again';
            // $this->apiResponse['message'] = $e->getMessage();
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }
    
    public function assignSelfTicket(Request $request)
    {
        try {
            $data = []; 

            $ticket = Ticket::where('TICKET_ID',$request->ticketId)->first(); 

            if($ticket->TECHNICIAN_ID == null){                

                $assignedOn = now();
                // $assignedOn = date('Y-m-d H:i:s', strtotime('2024-10-10 17:00:00'));
                
                $technician = $request->technicianId;
                $userName = User::where('EMPLOYEE_ID', $request->technicianId)->first()->USER_NAME;
                $userID = User::where('EMPLOYEE_ID', $request->technicianId)->first()->LOGIN_ID;
                
                // $result = $this->calculateSLA($assignedOn, $request->taskType,  $request->technicianId);

                // Extract variables from the result
                $slaDeadline = now();
                $idleTime = null;
            
                $ticket->TECHNICIAN_ID    =  $request->technicianId;
                $ticket->ASSIGNED_ON      = now();
                $ticket->ASSIGNED_BY      = $userName;
                $ticket->DUE_DATE         = $slaDeadline;
                $ticket->CATEGORY_ID      = $request->categoryId;
                $ticket->SUB_CATEGORY_ID  = $request->subcategoryId;
                $ticket->ITEM_TYPE_ID     = $request->itemTypeId;
                $ticket->ITEM_ID          = $request->itemId;
                $ticket->ASSET_ID         = $request->assetId;
                $ticket->TASK_TYPE_ID     = $request->taskType;
                $ticket->TEAM_NAME        = $request->teamName;
                $ticket->IDLE_TIME        = null;
                $ticket->STATUS           = 'Open';
                $ticket->PROGRESS         = strtoLower($ticket->PROGRESS) =='new' ? 'In Progress' : $ticket->PROGRESS;
                $ticket->MODIFIED_BY      = $userID;
                $ticket->MODIFIED_ON      = now();
            
                $ticket->save();            

                // Log ticket movement
                $logTicketMovement = DB::table('log_ticket_movement')->insert([
                    'TICKET_ID' => $ticket->TICKET_ID,
                    'ALLOCATED_TO' =>  $request->technicianId,
                    'ALLOCATED_BY' => $userID,
                    'ALLOCATED_ON' => now(),
                ]);
                
                // Log status movement
                $logStatusMovement = DB::table('log_status_movement')->insert([
                    'TICKET_ID' => $ticket->TICKET_ID,
                    'CHANGED_TO' =>$ticket->PROGRESS,
                    'CHANGED_BY' => $userID,
                    'CHANGED_ON' => now(),
                ]);

                //////////////////////////////////////////////////////

                // Negative points to team members if ($ticket->CREATED_ON - $ticket->ASSIGNED_ON) > SLA   
                if(!DB::table('breached_tickets_points')->where('TICKET_ID', $ticket->TICKET_ID)->exists()){     
                    $slaOn = DB::table('department_details')
                                ->where('DEPARTMENT_ID', $ticket->PROJECT_ID)
                                ->first();           

                    if ($slaOn && $slaOn->SLA_ON === 'CREATED_ON') {

                        $ticketPoints = TaskType::where('TASK_TYPE_ID', $ticket->TASK_TYPE_ID)->first();
                        $sla = ($ticketPoints->SLA)*60; //0

                        if($sla!== 0){
                            $createdOn = Carbon::parse($ticket->CREATED_ON);
                            $firstChangedOn = Carbon::parse($ticket->ASSIGNED_ON);

                            if ($createdOn->lessThan($firstChangedOn)) {
                                $workingStart = 10; // Start of working hours (10 AM)
                                $workingEnd = 18;   // End of working hours (6 PM)
                                $holidays = HolidayList::pluck('HOLIDAY')->toArray(); // Retrieve holiday list

                                $timeDifference = $this->calculateWorkingHours($createdOn, $firstChangedOn, $workingStart, $workingEnd, $holidays); 
                                
                                if($timeDifference > $sla) {
                                    // Get team members who are eligible for negative points
                                    $teamMembers = User::join('team_members', 'mstr_users.USER_ID', '=', 'team_members.TECHNICIAN')
                                                        ->join('team','team.TEAM_ID','=','team_members.TEAM_ID')  
                                                        ->where('team_members.IS_ACTIVE','Y')
                                                        ->where('team.TEAM_NAME', $ticket->TEAM_NAME)
                                                        ->where('team_members.IS_ELIGIBLE_FOR_NEGATIVE_POINTS', 'Y')
                                                        ->where('mstr_users.EMPLOYEE_ID', '!=', $ticket->TECHNICIAN_ID)
                                                        ->where('mstr_users.ACTIVE_FLAG', 'Y')
                                                        ->select('mstr_users.EMPLOYEE_ID','mstr_users.USER_NAME','mstr_users.USER_ID')
                                                        ->get();

                                    foreach($teamMembers as $member)
                                    {
                                        DB::table('breached_tickets_points')->insert([
                                            'TECHNICIAN_ID' => $member->EMPLOYEE_ID,
                                            'TICKET_ID' => $request->ticketId,                                       
                                            'POINTS' => -($ticketPoints->POINTS),
                                            'CREATED_ON' => now(),
                                            'CREATED_BY' => $userName,                                    
                                        ]);
                                    }
                                }
                            }
                        }  
                    }  
                }  
                
                /////////////////////////////////////////////////////////////////           
           
                            
                // Prepare the response
                $this->apiResponse['successCode']  = 1;
                $this->apiResponse['message']      = 'Successful';
                $this->apiResponse['data']         = $ticket;

                // Send the JSON response first
                $jsonResponse = response()->json($this->apiResponse);
                
                $response = Http::post('https://hr.iskconbangalore.net/v1/api/login/employee-fcmid', [
                    'accessKey'  => '729!#kc@nHKRKkbngsppnsg@491', 
                    'employeeID' =>  $request->technicianId
                ]);

                // Check if the request was successful
                if ($response->successful()) {
                    // API call was successful, handle response
                    $responseData = $response->json(); // Get response data as JSON
                    
                    $fcmId = $responseData['fcmId'][0]['FCM_ID'];

                    $ticketUserName = $ticket->USER_NAME;
                    $ticketDepartmentName = $ticket->DEPARTMENT_NAME;

                    $formattedName = $ticketUserName . " (" . $ticketDepartmentName . ")";

                    $body =  $ticket->TICKET_NO ." - ". $formattedName ." - " .$ticket->SUBJECT;
                    
                    $title = "Ticket Assigned to you";

                    if($fcmId)
                    {
                        $this->sendNotification($fcmId, $body , $title);                    
                    }
                } else {
                    
                    // API call failed
                    $statusCode = $response->status(); // Get HTTP status code
                    $data[] = $statusCode;
                }

                $mailFrom = DB::table('department_details')
                                ->where('DEPARTMENT_ID', $ticket->PROJECT_ID)
                                ->first(); 

                // Compose email subject
                $subject = "Request ID ## {$ticket->TICKET_NO} ## Assigned";

                // Compose email body
                $body  = "<!DOCTYPE html> <html><body>  ";
                $body .= "<p>Hare Krishna {$userName},</p>";
                $body .= "<p>The following Ticket Number ## {$ticket->TICKET_NO} ## is assigned to you.<br><br>";
                $body .= "Requested by: {$ticket->USER_NAME}<br>";
                $body .= "Title: {$ticket->SUBJECT}<br>";

                // Convert newlines to <br> and preserve formatting
                $descriptionFormatted = nl2br(e($ticket->DESCRIPTION));
                $body .= "Description:<br>{$descriptionFormatted}</p>";
            
                // $body .= "Description: {$ticket->DESCRIPTION}</p>";
                $body .= "<p>Kind Regards,<br>{$mailFrom->DISPLAY_NAME} Team<br><br>";
                $body .= "Note: This is an auto-generated email from our ticketing system.</p>"; 
                $body .= "</body></html>";                                 
                    
                $mailId = User::where('EMPLOYEE_ID', $technician)->first()->EMAIL;

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

                    // Send email
                    Mail::html($body, function ($message) use ($subject, $ticket, $mailId, $mailFrom) {
                        $message->from($mailFrom->SUPPORT_EMAIL_ID, $mailFrom->DISPLAY_NAME)
                                ->to($mailId)
                                ->subject($subject);
                    });
                }
                 

                modifyLogActivity('Assign Self Ticket',$request->ticketId,'TICKET ID',$userID);

                // Return the response
                return $jsonResponse;   
            }   
            else{  $this->apiResponse['successCode']  = 0;
                $this->apiResponse['message']      = 'Ticket Already Assigned';
                $this->apiResponse['data']         = [];

                return response()->json($this->apiResponse);
            }                
            
        } catch (\Exception $e) {

            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error ! Please Try Again';
            // $this->apiResponse['message'] = $e->getMessage();
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }
    
    public function addTask(Request $request)
    {
        try {
            $data = [];
          
            $ticket = Ticket::find($request->ticketId);
            
            // Get the last ID from the database
            $lastId = Ticket::where('LINKED_TO',$request->ticketId)->latest('TICKET_ID')->value('TASK_NO');

            // Increment the last ID
            $lastId++;

            $last3 = str_pad($lastId, 3, '0', STR_PAD_LEFT);

            if(!$ticket->CLOSED_ON)
            {
                $task = new Ticket;

                $task->TICKET_NO       = $ticket->TICKET_NO;
                $task->LINKED_TO       = $ticket->TICKET_ID;
                $task->TASK_NO         = $last3;
                $task->PROJECT_ID      = $ticket->PROJECT_ID;
                $task->MODE            = $ticket->MODE;
                $task->SUBJECT         = $request->subject; 
                $task->DESCRIPTION     = $request->description; 
                $task->PRIORITY        = $ticket->PRIORITY;
                $task->TEAM_NAME       = $ticket->TEAM_NAME;
                $task->TASK_TYPE_ID    = $ticket->TASK_TYPE_ID;
                $task->REQUESTED_BY    = $ticket->REQUESTED_BY;
                $task->USER_NAME       = $ticket->USER_NAME;
                $task->DEPARTMENT_CODE = $ticket->DEPARTMENT_CODE;
                $task->DEPARTMENT_NAME = $ticket->DEPARTMENT_NAME;
                $task->CREATED_BY      = $request->userId;
                $task->CREATED_ON      = now();

                $task->save();  

                $this->apiResponse['successCode']  = 1;
                $this->apiResponse['message']      = 'Successful';
                $this->apiResponse['data']         = $data;

                return response()->json($this->apiResponse);
            }
            else{
                $this->apiResponse['successCode']  = 0;
                $this->apiResponse['message']      = 'Ticket Already Closed !!';
                $this->apiResponse['data']         = [];

                return response()->json($this->apiResponse);
            }                       
            
        } catch (\Exception $e) {
            $this->apiResponse['successCode'] = 0;

            $this->apiResponse['message'] = 'Error ! Please Try Again';
            // $this->apiResponse['message'] = $e->getMessage();
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }
    public function getTasks(Request $request)
    {
        try {
            $data = [];
             
            $tickets = Ticket::query()
                       ->leftJoin('mstr_users','ticket.TECHNICIAN_ID','=','mstr_users.EMPLOYEE_ID')
                       ->where('LINKED_TO',  $request->ticketId)

                       ->when(request('ticketNo'),      fn ($query) => $query->where('ticket.TICKET_NO', 'like', '%' . request('ticketNo') . '%'))
                       ->when(request('userName'),      fn ($query) => $query->where('ticket.REQUESTED_BY', request('userName')))
                       ->when(request('oldTicket'),     fn ($query) => $query->where('ticket.TICKET_NO', request('oldTicket')))
                       ->when(request('department'),    fn ($query) => $query->where('ticket.DEPARTMENT_CODE', request('department')))
                       ->when(request('technician'),    fn ($query) => $query->where('ticket.TECHNICIAN_ID', request('technician')))
                       ->when(request('requestedFrom'), fn ($query) => $query->whereDate('ticket.CREATED_ON','>=',date('Y-m-d',strtotime(request('requestedFrom')))))
                       ->when(request('requestedTo'),   fn ($query) => $query->whereDate('ticket.CREATED_ON','<=',date('Y-m-d',strtotime(request('requestedTo')))))
                       ->when(request('status'),        fn ($query) => $query->where('ticket.STATUS', request('status')))
                       ->when(request('progress'),      fn ($query) => $query->where('ticket.PROGRESS', request('progress')))
                       ->when(request('mode'),          fn ($query) => $query->where('ticket.MODE', request('mode')))
                       
                       ->orderBy('TICKET_ID','DESC')
                       ->select('ticket.*','mstr_users.USER_NAME as TECHNICIAN_NAME')
                       ->get();

            foreach ($tickets as $key => $value) {

                $data[$key] = [

                    'ticketId'         => optional($value)->TICKET_ID,
                    'ticketNumber'     => optional($value)->TICKET_NO . '-'. optional($value)->TASK_NO,
                    'subject'          => optional($value)->SUBJECT,
                    'requester'        => optional($value)->USER_NAME,
                    'requestedOn'      => date('d-M-Y h:i A',strtotime(optional($value)->CREATED_ON)),
                    'department'       => optional($value)->DEPARTMENT_NAME,
                    'assignedTo'       => optional($value)->TECHNICIAN_NAME,
                    'assignedOn'       => is_null(optional($value)->ASSIGNED_ON) ? ''  : date('d-M-Y h:i A',strtotime(optional($value)->ASSIGNED_ON)),
                    'closedOn'         => is_null(optional($value)->CLOSED_ON) ? ''  : date('d-M-Y h:i A',strtotime(optional($value)->CLOSED_ON)),
                    'status'           => optional($value)->PROGRESS,
                    'amount'           => optional($value)->COST,
                    'priority'         => optional($value)->PRIORITY,   
                ];
            }

            $this->apiResponse['successCode']  = 1;
            $this->apiResponse['message']      = 'Successful';
            $this->apiResponse['data']         = $data;

            return response()->json($this->apiResponse);
            
        } catch (\Exception $e) {

            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error ! Please Try Again';
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }
    public function getAttachments(Request $request)
    {
        try {
            $data = [];

            $attachments = Attachment::where('TICKET_ID',$request->ticketId)
                                    ->where('TICKET_UPDATE_ID', NULL)
                                    ->where('IS_ACTIVE','Y')
                                    ->get();

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
            
        } catch (\Exception $e) {

            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error ! Please Try Again';
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }
    public function getAttachmentsForAPI($ticketId)
    {
        $attachments = Attachment::where('TICKET_ID',$ticketId)
                                ->where('IS_ACTIVE','Y')
                                ->get(['ATTACHMENT as fileName'])
                                ->toArray();

        return $attachments;
    }
    public function gettTicketAttachmentsForAPI($ticketId)
    {
        $attachments = Attachment::where('TICKET_ID',$ticketId)
                                ->where('IS_ACTIVE','Y')
                                ->where('TICKET_UPDATE_ID', NULL)
                                ->get(['ATTACHMENT as fileName'])
                                ->toArray();

        return $attachments;
    }
    public function getUpdateAttachments($ticketUpdateId)
    {
        $attachments = Attachment::leftJoin('ticket_updates','ticket_updates.TICKET_UPDATE_ID','=','ticket_attachment.TICKET_UPDATE_ID')
                                ->where('ticket_attachment.TICKET_UPDATE_ID',$ticketUpdateId)
                                ->where('ticket_attachment.IS_ACTIVE','Y')
                                ->where('ticket_updates.ATTACHMENT','Y')
                                ->get(['ticket_attachment.ATTACHMENT as fileName'])
                                ->first();
                                
        //    return $attachments? 'http://192.168.3.250/tickets/public/updates/'.$attachments->fileName : null;
        return $attachments ? 'https://tickets.iskconbangalore.net/public/updates/'.$attachments->fileName : null;
    }
    public function getUpdateAttachmentslist($ticketUpdateId,$ticketId)
    {
        $attachments = Attachment::where(['TICKET_UPDATE_ID'=>$ticketUpdateId,'TICKET_ID'=>$ticketId])
                                ->where('IS_ACTIVE','Y')
                                ->get(['ATTACHMENT as fileName'])
                                ->first();

        // if ($attachments) {
        //     $fileName = $attachments->fileName;
        //     $url = 'https://tickets.iskconbangalore.net/public/updates/' . $fileName;
        //     $headers = @get_headers($url);
        //     if ($headers && strpos($headers[0], '200')) {
        //         return $url; // File exists, return the URL
        //     }else{
        //         return null;
        //     }
        // }else{
        //     return null;
        // }
    
        // File does not exist or no attachment found

        // return $attachments ? 'http://192.168.3.250/tickets/public/updates/'.$attachments->fileName : null;
        return $attachments ? 'https://tickets.iskconbangalore.net/public/updates/'.$attachments->fileName : null;
    }
    public function removeAttachment(Request $request)
    {
        try {
            $data = [];

            $attachment = Attachment::find($request->attachmentId);

            $attachment->IS_ACTIVE = 'N';
            $attachment->save();

            $attachmentsArray = Attachment::where('TICKET_ID',$attachment->TICKET_ID)->where('IS_ACTIVE','Y')->get();

            foreach($attachmentsArray as $iterationNumber => $value)
            {                
                $data['attachments'][$iterationNumber] = $value;
            }

            $this->apiResponse['successCode']  = 1;
            $this->apiResponse['message']      = 'Successful';
            $this->apiResponse['data']         = $data;

            return response()->json($this->apiResponse);
            
        } catch (\Exception $e) {

            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error ! Please Try Again';
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }
    public function statusUpdate(Request $request)
    {      
        try {          
            $data = [];              
 
            $ticket = Ticket::find($request->ticketId);                                   
 
            if($request->status == 'Release'){
                $taskType = TaskType::find($ticket->TASK_TYPE_ID);

                if ($taskType) {
                    $ticketPoints = new TicketPoints;
           
                    $ticketPoints->TICKET_ID      = $request->ticketId;
                    $ticketPoints->TECHNICIAN_ID  = $ticket->TECHNICIAN_ID;
                    $ticketPoints->STATUS         = $request->status;
                    $ticketPoints->POINTS         = -($taskType->POINTS);
                    $ticketPoints->STATUS_DATE    = now();

                    $ticketPoints->save();
                }
            }
           
            if($request->status == 'Release'){
                $ticket->IS_RELEASED = 'Y';
                $ticket->PROGRESS = 'New';
                $ticket->STATUS = 'New'; 
                $ticket->TECHNICIAN_ID = null;
            }

            if($request->status != 'Release') {               
           
                $ticket->PROGRESS = ($request->status) ? $request->status : $ticket->PROGRESS; 
                $ticket->TEAM_NAME = ($request->teamName) ? $request->teamName : $ticket->TEAM_NAME;
                $ticket->TECHNICIAN_ID = ($request->statusUpdateTechnician) ? $request->statusUpdateTechnician : $ticket->TECHNICIAN_ID;  

            }
            // if($request->status == 'On Hold'){  
               
            //     $ticket->PROGRESS = ($request->status) ? $request->status : 'New';
            // }

            $ticket->save();  
           
            $logUpdate = new TicketUpdate;
            $logUpdate->TICKET_ID   = $request->ticketId;
            $logUpdate->TECHNICIAN  = User::find($request->userId)->EMPLOYEE_ID;
            $logUpdate->LOG_DATE    = now();
            $logUpdate->STATUS      = ($request->status) ? $request->status : $ticket->PROGRESS;
            $logUpdate->DESCRIPTION = $request->remarks;
            $logUpdate->REASON      = $request->onholdReason;
                       
            if ($request->haveAttacment == 'Y') {
                $logUpdate->ATTACHMENT = 'Y';
            }
            $logUpdate->save();
 
            if($logUpdate->STATUS!= 'New')
            {
                // Log status movement
                $logStatusMovement = DB::table('log_status_movement')->insert([
                    'TICKET_ID' => $request->ticketId,
                    'CHANGED_TO' => $logUpdate->STATUS,
                    'CHANGED_BY' => $request->userName,
                    'CHANGED_ON' => now(),
                ]);
            }
 
            // Check if an attachment is present in the request            
            if ($request->haveAttacment === 'Y') {
                    // Insert attachment details into the ticket_attachment table
                DB::table('ticket_attachment')->insert([
                    'TICKET_ID'  => $request->ticketId,
                    'ATTACHMENT' => $request->filename,
                    'TICKET_UPDATE_ID' => $logUpdate->TICKET_UPDATE_ID,
                ]);                 
            }
           
            $this->apiResponse['successCode']  = 1;
            $this->apiResponse['message']      = 'Successful';
            $this->apiResponse['data']         = $ticket;
 
            return response()->json($this->apiResponse);
           
        }
        catch (\Exception $e) {
 
            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error ! Please Try Again';
            // $this->apiResponse['message'] = $e->getMessage();
            $this->apiResponse['data'] = [];
 
            return response()->json($this->apiResponse);
        }
    }
    public function categorizeTicket(Request $request)
    {
        try {
            $data = [];

            $ticket = Ticket::find($request->ticketId);

            $assetIdCategorize = explode('-', $request->assetId); // e.g., "IB-1234"            
            
            $trustCode = isset($assetIdCategorize[0]) ? $assetIdCategorize[0] : '';
            $assetId = isset($assetIdCategorize[1]) ? $assetIdCategorize[1] : '';
          
            $ticket->CATEGORY_ID       = $request->category;
            $ticket->SUB_CATEGORY_ID   = $request->subcategory;
            $ticket->ITEM_TYPE_ID      = $request->itemType;
            $ticket->ITEM_ID           = $request->item;
            $ticket->TRUST_CODE        = $trustCode ? $trustCode :  $ticket->TRUST_CODE;
            $ticket->ASSET_ID          = $assetId ? $assetId :  $ticket->ASSET_ID;
            $ticket->MODIFIED_BY       = $request->userName;
            $ticket->MODIFIED_ON       = now();

            $ticket->save();

            $this->apiResponse['successCode']  = 1;
            $this->apiResponse['message']      = 'Successful';
            $this->apiResponse['data']         = $ticket;

            return response()->json($this->apiResponse);
            
        } catch (\Exception $e) {

            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error ! Please Try Again';
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }
    // Web Ticket Close API
    public function closeTicket(Request $request)
    {
        try {
            $data = [];

            $ticket  = Ticket::find($request->ticketId);

            $results = Ticket::where('LINKED_TO', $request->ticketId)
                       ->whereIn('PROGRESS',['Open','New','On Hold','In Progress'])
                       ->get();
            
            if($results->count() != 0)
            {     
                $this->apiResponse['successCode']  =  0;
                $this->apiResponse['message']      = 'Please close the subtasks';
                $this->apiResponse['data']         =  [];

                return response()->json($this->apiResponse);
            }
           
            if($ticket->CATEGORY_ID == "" && $ticket->STATUS == 'Open'){

                $this->apiResponse['successCode']  = 0;
                $this->apiResponse['message']      = 'Please Categorize the Ticket';
                $this->apiResponse['data']         = [];

                return response()->json($this->apiResponse);
            }
            
            else{
                $ticket->IS_CLOSED         = 'N';
                $ticket->CLOSURE_CODE      = $request->status;// 'Resolved / Cancelled/ Transfered/ Deferred',
                $ticket->CLOSURE_REMARKS   = $request->remarks;//'Closure Remarks',
                $ticket->CLOSED_BY         = $request->userName;
                $ticket->CLOSED_ON         = now();
                $ticket->COST              = $request->cost;
                $ticket->EFFORT            = $request->effort;
                $ticket->STATUS            = 'Completed';
                $ticket->PROGRESS          = $request->status;
                $ticket->MODIFIED_BY       = $request->userName;
                $ticket->MODIFIED_ON       = now();                

                if($request->status == 'Resolved')
                {
                    $isReleaseTicket = TicketPoints::where(['TICKET_ID'=> $request->ticketId]) 
                                                        ->orderBy('POINT_ID','DESC')                           
                                                        ->first();
                    if($isReleaseTicket)
                    {
                        if($isReleaseTicket->STATUS == 'Release')
                        {
                            $ticketPoints = TaskType::where('TASK_TYPE_ID', $ticket->TASK_TYPE_ID)->first();
                            $sla = $ticketPoints->SLA; //0
                            if($sla!= 0)
                            {
                                $totalTimeConsumed = $this->getTimeLeft($request->ticketId, $ticket->PROJECT_ID);

                                if ($totalTimeConsumed < ($sla * 60)) { // SLA in minutes
                                    $ticket->POINTS = $ticketPoints->POINTS + 5;
                                }
                                else{
                                    $ticket->POINTS = $ticketPoints->POINTS;
                                }  
                            }
                            else{
                                $ticket->POINTS = $ticketPoints->POINTS;
                            }
                              
                        }else{
                            $ticketPoints = TaskType::where('TASK_TYPE_ID', $ticket->TASK_TYPE_ID)->first();
                            $sla = $ticketPoints->SLA; //0

                            if($sla!= 0){
                                $totalTimeConsumed = $this->getTimeLeft($request->ticketId, $ticket->PROJECT_ID);

                                if ($totalTimeConsumed < ($sla * 60)) { // SLA in minutes
                                    $ticket->POINTS = $ticketPoints->POINTS;
                                }
                                else{
                                    $ticket->POINTS = -($ticketPoints->POINTS);                                    
                                } 
                            }
                            else{
                                $ticket->POINTS = $ticketPoints->POINTS;
                            }                               
                        }
                      
                    }else{
                        $ticketPoints = TaskType::where('TASK_TYPE_ID', $ticket->TASK_TYPE_ID)->first();
                        $sla = $ticketPoints->SLA;

                        if($sla!= 0){

                            $totalTimeConsumed = $this->getTimeLeft($request->ticketId, $ticket->PROJECT_ID);

                            if ($totalTimeConsumed < ($sla * 60)) { // SLA in minutes
                                $ticket->POINTS = $ticketPoints->POINTS;
                            }
                            else{
                                $ticket->POINTS = -($ticketPoints->POINTS);                               
                            }  
                        }
                        else{
                            $ticket->POINTS = $ticketPoints->POINTS;
                        }               
                    }
                              
                }

                $isSlaExist = TaskType::where('TASK_TYPE_ID', $ticket->TASK_TYPE_ID)->first();
                if($isSlaExist)
                {
                    $sla = $isSlaExist->SLA; //0
                    if($sla!= 0)
                    {                    
                        $totalTimeConsumed = $this->getTimeLeft($request->ticketId, $ticket->PROJECT_ID);
                        if ($totalTimeConsumed < ($sla * 60)) { // SLA in minutes
                            $ticket->IS_SLA_BREACH = 'N';
                        }
                        else{
                            $ticket->IS_SLA_BREACH = 'Y';
                        } 
                    }
                    else{
                        $ticket->IS_SLA_BREACH = 'N';
                    } 
                }

                $ticket->save();     
                
                $logUpdate = new TicketUpdate;
                
                $logUpdate->TICKET_ID   = $request->ticketId;
                $logUpdate->TECHNICIAN  = User::find($request->userId)->EMPLOYEE_ID ? 
                                            User::find($request->userId)->EMPLOYEE_ID : $request->userName;
                $logUpdate->LOG_DATE    = now();
                $logUpdate->STATUS      = $request->status;
                $logUpdate->DESCRIPTION = $request->remarks;
                
                if ($request->haveAttacment == 'Y') {
                    $logUpdate->ATTACHMENT = 'Y';
                }
                $logUpdate->save();

                // Log status movement
                $logStatusMovement = DB::table('log_status_movement')->insert([
                    'TICKET_ID' =>  $request->ticketId,
                    'CHANGED_TO' => $request->status,
                    'CHANGED_BY' => $request->userName,
                    'CHANGED_ON' => now(),
                ]);

                // Check if an attachment is present in the request            
                if ($request->filename) {
                // if ($request->haveAttacment == 'Y') {
                        // Insert attachment details into the ticket_attachment table
                    DB::table('ticket_attachment')->insert([
                        'TICKET_ID'  => $request->ticketId,
                        'ATTACHMENT' => $request->filename,
                        'TICKET_UPDATE_ID' => $logUpdate->TICKET_UPDATE_ID,
                    ]);                                 
                }  

                $mailFrom = DB::table('department_details')
                                ->where('DEPARTMENT_ID', $request->departmentId)
                                ->first();
                                   
                if($request->status == 'Resolved')
                {
                    $response = Http::post('https://hr.iskconbangalore.net/v1/api/login/employee-fcmid', [
                        'accessKey'  => '729!#kc@nHKRKkbngsppnsg@491', 
                        'employeeID' =>  $ticket->REQUESTED_BY
                    ]);

                    // Check if the request was successful
                    if ($response->successful()) {
                        // API call was successful, handle response
                        $responseData = $response->json(); // Get response data as JSON
                        // Process the response data
                        // Example: $responseData['data']
                        $fcmId = $responseData['fcmId'][0]['FCM_ID'];

                        $ticketUserName = $ticket->USER_NAME;
						
                        $ticketDepartmentName = $ticket->DEPARTMENT_NAME;
                      
                        $body =  $ticket->TICKET_NO ." - ". $ticket->SUBJECT . "\n We value your feedback. Please provide using the ISKCON Service APP.";
                        
                        $title = "Ticket Resolved";

                        if($fcmId)
                       {
                           $this->sendNotification($fcmId, $body , $title);
                           
                       }

                    } else {
                        // API call failed
                        $statusCode = $response->status(); // Get HTTP status code
                        // Handle error based on status code
                        // Example: Log error, throw exception, etc.
                        $data[] = $statusCode;
                    }

                    // Compose email subject
                    $subject = "Request Id : ## {$ticket->TICKET_NO} ## has been resolved.";

                    // Compose email body
                    $body  = "<!DOCTYPE html> <html><body>  ";
                    $body .= "<p>Hare Krishna {$ticket->USER_NAME},</p>";
                    $body .= "<p>This is to inform you that our Service Desk has resolved Ticket No. ## {$ticket->TICKET_NO} ## for [{$ticket->SUBJECT}]<br><br>";
                    $body .= "Closure Remarks: {$request->remarks}<br><br>";
                    $body .= "We value your feedback. Please provide using the ISKCON Service APP.<br><br>";
                    $body .= "If the ticket has not been resolved, please reply to this email to reopen the ticket.<br><br>";
                    $body .= "If there is no response from you, we will assume that the ticket has been resolved and the ticket will be automatically closed after 48 hours.</p>";
                    $body .= "<p>With Regards,<br>{$mailFrom->DISPLAY_NAME} Team<br><br>";
                    $body .= "Note: This is an auto-generated email from our ticketing system.</p>";
                    $body .= "</body></html>";

                    $mailId = $ticket->USER_MAIL;

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
                        
                        // Send email
                        Mail::html($body, function ($message) use ($subject, $ticket, $mailId, $mailFrom) {
                            $message->from($mailFrom->SUPPORT_EMAIL_ID, $mailFrom->DISPLAY_NAME)
                                    ->to($mailId)
                                    ->subject($subject); 
                            });
                    }                    
                }   
                if($request->status == 'Cancelled')
                {
                    // Compose email subject
                    $subject = "Request: ##  {$ticket->TICKET_NO} ## raised by you was cancelled.";

                    // Compose email body
                    
                    $body  = "<!DOCTYPE html> <html><body>  ";
                    $body .= "<p>Hare Krishna {$ticket->USER_NAME},</p>";
                    $body .= "<p>Note that the request raised by you was cancelled.<br><br>";
                    $body .= "The request was for: {$ticket->SUBJECT}.<br>";
                    $body .= "Cancel Remarks: {$request->remarks}</p>";
                    $body .= "<p>Kind Regards,<br>{$mailFrom->DISPLAY_NAME} Team<br><br>";
                    $body .= "Note: This is an auto-generated email from our ticketing system.</p>";                
                    $body .= "</body></html>";
                    $mailId = $ticket->USER_MAIL;

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

                        Mail::html($body, function ($message) use ($subject, $ticket, $mailId, $mailFrom) {
                            $message->from($mailFrom->SUPPORT_EMAIL_ID, $mailFrom->DISPLAY_NAME)
                                    ->to($mailId)
                                    ->subject($subject); 
                            });
                    }                    
                }                  
                 
                $this->apiResponse['successCode']  = 1;
                $this->apiResponse['message']      = 'Task Closed';
                $this->apiResponse['data']         = $ticket;  
                 
                return response()->json($this->apiResponse); 
            }            
            
        } 
        catch (\Exception $e) {

            $this->apiResponse['successCode'] = 0;
            // $this->apiResponse['message'] = 'Error ! Please Try Again';
             $this->apiResponse['message'] = $e->getMessage();
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }   
    
    public function getTeamMembers(Request $request)
    {
        try {
            $data = [];

            $teamMembers = User::join('team_members', 'mstr_users.USER_ID', '=', 'team_members.TECHNICIAN')
                            ->join('team','team.TEAM_ID','=','team_members.TEAM_ID')  
                            ->where('team_members.IS_ACTIVE','Y')
                            ->where('team.TEAM_NAME', $request->teamName)
                            ->where('team_members.IS_ELIGIBLE_FOR_NEGATIVE_POINTS', 'Y')
                            ->where('mstr_users.EMPLOYEE_ID', '!=', $request->empId)
                            ->select('mstr_users.EMPLOYEE_ID','mstr_users.USER_NAME','mstr_users.USER_ID')
                            ->get();

            foreach ($teamMembers as $key => $value) {
                $data[$key] = [
                    'EMPLOYEE_ID' => optional($value)->EMPLOYEE_ID,
                    'USER_NAME'   => optional($value)->USER_NAME,
                    'USER_ID'     => optional($value)->USER_ID,
                ];
            }

            $this->apiResponse['successCode']  = 1;
            $this->apiResponse['message']      = 'Successful';
            $this->apiResponse['data']         = $data;

            return response()->json($this->apiResponse);
            
        } catch (\Exception $e) {

            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error ! Please Try Again';
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }

    public function reopenTicket(Request $request)
    {
        try {
            $data = [];

            $ticket = Ticket::find($request->ticketId);    
            
            $ticketPoints = new TicketPoints;
           
            $ticketPoints->TICKET_ID      = $request->ticketId;
            $ticketPoints->TECHNICIAN_ID  = ($ticket->TECHNICIAN_ID) ? $ticket->TECHNICIAN_ID : '';
            $ticketPoints->STATUS         = 'Reopened';
            $ticketPoints->POINTS         = $ticket->POINTS;
            $ticketPoints->STATUS_DATE    = now();
            $ticketPoints->save(); 

            $ticket->IS_CLOSED         = 'N';
            $ticket->CLOSURE_CODE      = NULL; // 'Completed / Cancelled/ Transfered/ Deferred',
            $ticket->CLOSURE_REMARKS   = NULL; //'Closure Remarks',
            $ticket->CLOSED_BY         = NULL; // 'Master: MSTR_USERS.LOGIN_ID',
            $ticket->CLOSED_ON         = NULL; // 'Date on which the request is closed',
            $ticket->COST              = NULL; //'Amount spent on the ticket',
            $ticket->EFFORT            = NULL; //'Effort spent on the tricket',
            $ticket->STATUS            = 'Open'; //' Open',
            $ticket->PROGRESS          = 'Reopened'; //' Reopened',
            $ticket->POINTS            = NULL;
            $ticket->MODIFIED_BY       = $request->userName;
            $ticket->MODIFIED_ON       = now();
            $ticket->save();

            $logUpdate = new TicketUpdate;
            
            $logUpdate->TICKET_ID   = $request->ticketId;
            $logUpdate->TECHNICIAN  = User::find($request->userId)->EMPLOYEE_ID;
            $logUpdate->LOG_DATE    = now();
            $logUpdate->STATUS      = 'Reopened';
            $logUpdate->DESCRIPTION = $request->remarks;

            // Check if an attachment is present in the request
            if ($request->hasFile('file')) {
                $attachment = $request->file('file');

                $originalName = pathinfo($attachment->getClientOriginalName(), PATHINFO_FILENAME);

                $attachmentName = $originalName. '_' .time(). '.' .$request->file->extension();                
            
                // Assuming you want to store the attachment path in the $data array
                $request->file->move(public_path('updates/attachments'), $attachmentName);

                $data['attachment_path'] = $attachmentName;
            
                // Assuming you have an 'attachment' field in your TicketUpdate model
                $logUpdate->ATTACHMENT = 'Y';
            }
            $logUpdate->save();

            // Log status movement
            $logStatusMovement = DB::table('log_status_movement')->insert([
                'TICKET_ID' => $request->ticketId,
                'CHANGED_TO' => 'Reopened',
                'CHANGED_BY' => $request->userName,
                'CHANGED_ON' => now(),
            ]);
           
            $response = Http::post('https://hr.iskconbangalore.net/v1/api/login/employee-fcmid', [
                'accessKey'  => '729!#kc@nHKRKkbngsppnsg@491', 
                'employeeID' =>  $ticket->REQUESTED_BY
            ]);            
    
            $this->apiResponse['successCode']  = 1;
            $this->apiResponse['message']      = 'Task Reopened';
            $this->apiResponse['data']         = $data;

            return response()->json($this->apiResponse);
            
        } catch (\Exception $e) {

            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error ! Please Try Again';
            // $this->apiResponse['message'] = $e->getMessage();
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }
    public function getUpdates(Request $request)
    {
        try {
            $data = [];

            $baseAttachmentUrl = $request->baseUrl;

            $updates = TicketUpdate::query()
                       ->leftJoin('mstr_users','ticket_updates.TECHNICIAN','=','mstr_users.EMPLOYEE_ID')
                       ->where('TICKET_ID',$request->ticketId)
                       ->select('ticket_updates.*','mstr_users.USER_NAME')
                       ->get();

            foreach ($updates as $key => $value) {

                $attachment = Attachment::where(['TICKET_UPDATE_ID' => $value->TICKET_UPDATE_ID])->first();

                $data[$key] = [
                    'ticketId'         => optional($value)->TICKET_ID,
                    'ticketUpdateId'   => optional($value)->TICKET_UPDATE_ID,
                    'ticketNo'         => optional($value)->TICKET_NO,
                    'technician'       => optional($value)->USER_NAME,
                    'status'           => optional($value)->STATUS,
                    'description'      => optional($value)->DESCRIPTION,
                    'reason'           => optional($value)->REASON,
                    'logDate'          => date('d-M-Y h:i A',strtotime(optional($value)->LOG_DATE)),                    
                    'workAttachment'   => optional($value)->ATTACHMENT ? url('updates/' . $value->ATTACHMENT) : null,
                    'updateAttachment' => $this->getUpdateAttachments(optional($value)->TICKET_UPDATE_ID) ?? '',
                    'updateAttachmentlist' => $this->getUpdateAttachmentslist(optional($value)->TICKET_UPDATE_ID,$value->TICKET_ID) ?? '',
                ];
            }

            $this->apiResponse['successCode']  = 1;
            $this->apiResponse['message']      = 'Successful';
            $this->apiResponse['data']         = $data;

            return response()->json($this->apiResponse);
            
        } catch (\Exception $e) {

            $this->apiResponse['successCode'] = 0;
            // $this->apiResponse['message'] = 'Error ! Please Try Again';
            $this->apiResponse['message'] = $e->getMessage();
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }
    public function updateTask(Request $request)
    {
        try {
            $data = [];

            $ticket = Ticket::find($request->ticketId);
   
            $ticket->SUBJECT           = $request->subject; 
            $ticket->DESCRIPTION       = $request->description; 
            $ticket->TEAM_NAME         = $request->taskTeamName; 
            $ticket->STATUS            = 'Open';
            $ticket->PROGRESS          = 'In Progress';
            $ticket->TASK_TYPE_ID      = $request->taskTicketType;
            $ticket->CATEGORY_ID       = $request->category;
            $ticket->SUB_CATEGORY_ID   = $request->subcategory;
            $ticket->ITEM_TYPE_ID      = $request->itemType;
            $ticket->ITEM_ID           = $request->item;
            $ticket->TRUST_CODE        = $request->filled('trust_code') ? $request->trust_code :  $ticket->TRUST_CODE;
            $ticket->ASSET_ID          = $request->filled('assetId') ? $request->assetId :  $ticket->ASSET_ID;
            $ticket->TECHNICIAN_ID     = $request->technician;
            $ticket->COST              = $request->cost;
            $ticket->ASSIGNED_ON       = now();
            $ticket->ASSIGNED_BY       = $request->userId;
            $ticket->MODIFIED_BY       = $request->userId;
            $ticket->MODIFIED_ON       = now();

            $ticket->save();

            // Log ticket movement
             $logTicketMovement = DB::table('log_ticket_movement')->insert([
                'TICKET_ID' => $ticket->TICKET_ID,
                'ALLOCATED_TO' =>  $request->technician,
                'ALLOCATED_BY' => $request->userId,
                'ALLOCATED_ON' => now(),
            ]);
            
            // Log status movement
            $logStatusMovement = DB::table('log_status_movement')->insert([
                'TICKET_ID' => $ticket->TICKET_ID,
                'CHANGED_TO' =>$ticket->PROGRESS,
                'CHANGED_BY' => $request->userId,
                'CHANGED_ON' => now(),
            ]);


            if($request->technician){
                $response = Http::post('https://hr.iskconbangalore.net/v1/api/login/employee-fcmid', [
                    'accessKey'  => '729!#kc@nHKRKkbngsppnsg@491', 
                    'employeeID' =>  $request->technician,
                    // Add any parameters required by the API
                ]);

                // Check if the request was successful
                if ($response->successful()) {
                    // API call was successful, handle response
                    $responseData = $response->json(); // Get response data as JSON
                    
                    $fcmId = $responseData['fcmId'][0]['FCM_ID'];

                    $ticketUserName = $ticket->USER_NAME;
                    $ticketDepartmentName = $ticket->DEPARTMENT_NAME;

                    $formattedName = $ticketUserName . " (" . $ticketDepartmentName . ")";

                    $body =  $ticket->TICKET_NO."-".$ticket->TASK_NO ." - ". $formattedName ." - " .$ticket->SUBJECT;
                    
                    $title = "Ticket Assigned to you";

                    if($fcmId)
                    {
                        $this->sendNotification($fcmId, $body , $title);
                    }

                } else {
                    
                    // API call failed
                    $statusCode = $response->status(); // Get HTTP status code
                    $data[] = $statusCode;
                }

                $user = User::where('EMPLOYEE_ID', $request->technician)->first()->USER_NAME;

                // Compose email subject
                $subject = "Request ID ## {$ticket->TICKET_NO}-{$ticket->TASK_NO} ## Assigned";

                // Compose email body
                $body  = "<!DOCTYPE html> <html><body>  ";
                $body .= "<p>Hare Krishna {$user},</p>";
                $body .= "<p>The following Ticket Number ## {$ticket->TICKET_NO}-{$ticket->TASK_NO} ## is assigned to you.<br><br>";
                $body .= "Requested by: {$ticket->USER_NAME}<br>";
                $body .= "Created by:  {$ticket->CREATED_BY}<br>";
                $body .= "Title: {$ticket->SUBJECT}<br>";

                // Convert newlines to <br> and preserve formatting
                $descriptionFormatted = nl2br(e($ticket->DESCRIPTION));
                $body .= "Description:<br>{$descriptionFormatted}</p>";
            
                // $body .= "Description: {$ticket->DESCRIPTION}</p>";
                $body .= "<p>Kind Regards,<br>IT Support Team<br><br>";
                $body .= "Note: This is an auto-generated email from our ticketing system.</p>"; 
                $body .= "</body></html>";                                
                    
                $mailId = User::where('EMPLOYEE_ID', $request->technician)->first()->EMAIL;

                if($mailId){
                    // Send email
                    Mail::html($body, function ($message) use ($subject, $ticket, $mailId) {
                        $message->to($mailId)->subject($subject);
                    });
                }
                 
            }
                        

            $this->apiResponse['successCode']  = 1;
            $this->apiResponse['message']      = 'Successful';
            $this->apiResponse['data']         = $data;

            return response()->json($this->apiResponse);
            
        } catch (\Exception $e) {

            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error ! Please Try Again';
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }
    
    public function getPredefinedTasks(Request $request)
    {
        try {
            $tasks = PredefinedTasks::query()                        
                        ->leftJoin('lkp_sub_category', 'mstr_templates.SUBCATEGORY_ID', '=', 'lkp_sub_category.SUB_CATEGORY_ID')
                        ->leftJoin('lkp_category', 'lkp_sub_category.CATEGORY_ID', '=', 'lkp_category.CATEGORY_ID')
                        ->leftJoin('lkp_item_type', 'mstr_templates.ITEM_TYPE', '=', 'lkp_item_type.ITEM_TYPE_ID')
                        ->leftJoin('lkp_item', 'mstr_templates.ITEM', '=', 'lkp_item.ITEM_ID')
                        ->when(request('item'),       fn ($query) => $query->where('mstr_templates.ITEM',  request('item') ))
                        ->when(request('subject'),       fn ($query) => $query->where('mstr_templates.SUBJECT', 'like', '%' . request('subject') . '%'))
                        ->when(request('description'),       fn ($query) => $query->where('mstr_templates.DESCRIPTION', 'like', '%' . request('description') . '%'))
                        ->where('mstr_templates.DEPARTMENT_ID',$request->departmentId)
                        ->select('mstr_templates.*',
                                'lkp_category.DISPLAY_NAME AS CATEGORY', 
                                'lkp_sub_category.DISPLAY_NAME AS SUBCATEGORY',
                                'lkp_item_type.DISPLAY_NAME AS ITEM_TYPE',
                                'lkp_item.DISPLAY_NAME AS ITEM')
                        ->orderBy('TEMPLATE_ID','desc')
                        ->get();

            $data = [];

            foreach ($tasks as $key => $value) {
                $data[$key] = [
                    'taskId'           => optional($value)->TEMPLATE_ID,
                    'subject'          => optional($value)->SUBJECT,
                    'description'      => optional($value)->DESCRIPTION,
                    'category'         => optional($value)->CATEGORY,
                    'subcategory'      => optional($value)->SUBCATEGORY,
                    'itemType'         => optional($value)->ITEM_TYPE,
                    'item'             => optional($value)->ITEM,
                    'status'           => optional($value)->IS_ACTIVE,
                ];
            }

            $this->apiResponse['successCode']  = 1;
            $this->apiResponse['message']      = 'Successful';
            $this->apiResponse['data']         = $data;

            return response()->json($this->apiResponse);
            
        } catch (\Exception $e) {

            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error ! Please Try Again';
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }
    public function getPredefinedTask(Request $request)
    {
        try {            
            $tasks = PredefinedTasks::query()
                        ->leftJoin('lkp_sub_category', 'mstr_templates.SUBCATEGORY_ID', '=', 'lkp_sub_category.SUB_CATEGORY_ID')
                        ->leftJoin('lkp_category', 'lkp_sub_category.CATEGORY_ID', '=', 'lkp_category.CATEGORY_ID')
                        ->leftJoin('lkp_item_type', 'mstr_templates.ITEM_TYPE', '=', 'lkp_item_type.ITEM_TYPE_ID')
                        ->leftJoin('lkp_item', 'mstr_templates.ITEM', '=', 'lkp_item.ITEM_ID')
                        // ->when(request('item'),       fn ($query) => $query->where('mstr_templates.ITEM',  request('item') ))
                        // ->when(request('subject'),       fn ($query) => $query->where('mstr_templates.SUBJECT', 'like', '%' . request('subject') . '%'))
                        // ->when(request('description'),       fn ($query) => $query->where('mstr_templates.DESCRIPTION', 'like', '%' . request('description') . '%'))
                        ->where(['mstr_templates.DEPARTMENT_ID' => $request->departmentId,
                                    'mstr_templates.TEMPLATE_ID' => $request->subTaskId])
                        ->select('mstr_templates.*',
                                'lkp_category.CATEGORY_ID AS CATEGORY', 
                                'lkp_sub_category.SUB_CATEGORY_ID AS SUBCATEGORY',
                                'lkp_item_type.ITEM_TYPE_ID AS ITEM_TYPE',
                                'lkp_item.ITEM_ID AS ITEM')
                        ->orderBy('TEMPLATE_ID','desc')
                        ->get();
                        
            $data = [];
 
            foreach ($tasks as $value) {
                $data[] = [
                    'taskId'           => optional($value)->TEMPLATE_ID,
                    'subject'          => optional($value)->SUBJECT,
                    'description'      => optional($value)->DESCRIPTION,
                    'category'         => optional($value)->CATEGORY,
                    'subcategory'      => optional($value)->SUBCATEGORY,
                    'itemType'         => optional($value)->ITEM_TYPE,
                    'item'             => optional($value)->ITEM,
                    'status'           => optional($value)->IS_ACTIVE,
                ];
            }     
                               
            $this->apiResponse['successCode']  = 1;
            $this->apiResponse['message']      = 'Successful';
            $this->apiResponse['data']         = $data;

            return response()->json($this->apiResponse);
            
        } catch (\Exception $e) {

            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error ! Please Try Again';
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }
    public function updatePredefinedTask(Request $request)
    {
        try {
            $data = [];

            $task = PredefinedTasks::find($request->taskId);

            $task->SUBCATEGORY_ID  = $request->subcategory;
            $task->SUBJECT         = $request->subject;
            $task->DESCRIPTION     = $request->description;
            $task->IS_ACTIVE       = $request->status;
            $task->CREATED_BY      = $request->userId;
            $task->CREATED_ON      = now();

            $task->save();

            $this->apiResponse['successCode']  = 1;
            $this->apiResponse['message']      = 'Successful';
            $this->apiResponse['data']         = $data;

            return response()->json($this->apiResponse);
            
        } catch (\Exception $e) {

            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error ! Please Try Again';
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
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
                    'click_action' => 'My Tickets',
                    'title' => $title,  // Notification title
                    'body' => $body,
                ],
            ],
        ]); 

        if($response){
            // echo "hello ", json_encode($response->json());  exit;
            return $response->json();   
        }              
    }

    public function getAccessToken()
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

    public function ticketNotificationSend(Request $request)
    {
        $accessToken = $this->getAccessToken();    

        $fcmId = $request->fcmId;
        $body = $request->body;
        $clickAction = $request->click_action;
        $title = $request->title;
        $image = $request->image;

        // foreach ($fcmIds as $fcmId) {
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
                        'body' => $body,
                        'title' => $title,   
                        'click_action' => $clickAction,                        
						'requireInteraction'=>"true",
                        'image' => $image,
                    ],
                ],
            ]); 

            if($response){
                // echo "hello ", json_encode($response->json());  exit;
                return $response->json();   
            }  
        // }   
    }

    public function satvataNotificationSend(Request $request)
    {
        $accessToken = $this->getSatvataAccessToken();  
        
        $fcmId = $request->fcmId;
        $body = $request->body;
        $clickAction = $request->click_action;
        $title = $request->title;

        // foreach ($fcmIds as $fcmId) {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/v1/projects/satvata-online/messages:send', [
                          
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
                        'body' => $body,
                        'title' => $title,   
                        'click_action' => 'Satvata',
						'requireInteraction'=>'true'
                    ],
                ],
            ]); 

            if($response){
                // echo "hello ", json_encode($response->json());  exit;
                return $response->json();   
            }  
        // }   
    }

    public function getSatvataAccessToken()
    {
        // Path to your service account JSON file
        $serviceAccountPath = storage_path('app/firebase/service-account-satvata.json');
        
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
    
    public function getUserTicketList(Request $request)
    {
        try {
            $data = [];

            $validator = Validator::make($request->all(), [
                'userId'         => 'required',
                'taskStaNew'     => 'nullable',
                'taskStaopen'    => 'nullable',
                'projectIdValue' => 'required',
                'taskStaNew'     => 'nullable',
                'taskStaopen'    => 'nullable'
            ]);

            if ($validator->fails()){

                $this->apiResponse['successCode'] = 0;
                $this->apiResponse['message'] = $validator->errors()->all();                   

                return response()->json($this->apiResponse);
            }
           
            $tickets = Ticket::query()
                    ->leftJoin('mstr_users','ticket.TECHNICIAN_ID','=','mstr_users.EMPLOYEE_ID')
                    ->leftJoin('lkp_category','ticket.CATEGORY_ID','=','lkp_category.CATEGORY_ID')
                    ->leftJoin('lkp_sub_category','ticket.SUB_CATEGORY_ID','=','lkp_sub_category.SUB_CATEGORY_ID')
                    ->where('ticket.PROJECT_ID',  $request->projectIdValue)
                    ->where('ticket.REQUESTED_BY',  $request->userId);
            
            if($request->taskStaopen == 'Y')
            {
                $tickets->whereIn('ticket.STATUS',['New','Open']);
            }
            else
            {
                $tickets->whereNotIn('ticket.STATUS',['New','Open']);
            }

            $tickets = $tickets->orderBy('ticket.TICKET_ID','DESC')
            ->select('ticket.*','mstr_users.USER_NAME as TECHNICIAN_NAME','mstr_users.LOGIN_ID as TECHNICIAN_LOGIN_ID','lkp_category.DISPLAY_NAME as CATEGORY','lkp_sub_category.DISPLAY_NAME as SUB_CATEGORY')
            ->get();

            $count = 1;

            foreach ($tickets as $key => $value) {

                $data[$key] = [
                    'requestNo' => optional($value)->TICKET_NO ?? '',
                    'selNo' => $count++,
                    'requestId' => optional($value)->TICKET_ID ?? '',
                    'assetId' => optional($value)->ASSET_ID ?? '',
                    'trustCode' => optional($value)->TRUST_CODE ?? '',
                    'requestSubject' => optional($value)->SUBJECT ?? '',
                    'requestDescription' => optional($value)->DESCRIPTION ?? '',
                    'taskDescription' => optional($value)->DESCRIPTION ?? '',
                    'taskTeamName' => optional($value)->TEAM_NAME ?? '',
                    'taskTeamId' => '',
                    'taskTechnicianName' => optional($value)->TECHNICIAN_NAME ?? '',
                    'taskTechnicianId' => optional($value)->TECHNICIAN_ID ?? '',
                    'taskStatus' => optional($value)->STATUS ?? '',
                    'userId' => $value->REQUESTED_BY ?? '',
                    'requesterName' => optional($value)->USER_NAME ?? '',
                    'requesterExtension' => optional($value)->EXTENSION ?? '',
                    'departmentCode' => optional($value)->DEPARTMENT_CODE ?? '',
                    'departmentName' => optional($value)->DEPARTMENT_NAME ?? '',
                    'mode' => optional($value)->MODE ?? '',
                    'priority' => optional($value)->PRIORITY ?? '',
                    'requestAssignedTo' => optional($value)->TECHNICIAN_NAME ?? '',
                    'requestTechnicianLoginId' => optional($value)->TECHNICIAN_LOGIN_ID ?? '',
                    'requestTeamId' => optional($value)->TEAM_ID ?? '',
                    'requestTeamName' => optional($value)->TEAM_NAME ?? '',
                    'requestGroup' => optional($value)->TASK_TYPE_NAME ?? '',
                    'requestGroupId' => optional($value)->TASK_TYPE_ID ?? '',
                    'requestAttachment' => $this->getAttachmentsForAPI(optional($value)->TICKET_ID) ?? '',
                    'requestCreatedBy' => optional($value)->CREATED_BY ?? '',
                    'requestStatus' => optional($value)->STATUS ?? '',
                    'taskType' => optional($value)->TASK_TYPE_ID ?? '',
                    'category' => optional($value)->CATEGORY ?? '',
                    'subcategory' => optional($value)->SUB_CATEGORY ?? '',
                    'taskTypeId' => optional($value)->TASK_SUBTYPE_ID ?? '',
                    'categoryeId' => optional($value)->CATEGORY_ID ?? '',
                    'subcategoryId' => optional($value)->SUB_CATEGORY_ID ?? '',
                    'itemTypeId' => optional($value)->ITEM_TYPE_ID ?? '',
                    'itemId' => optional($value)->ITEM_ID ?? '',
                    'taskCost' => optional($value)->COST ?? '',
                    'itemType' => optional($value)->ITEM_TYPE_DISPLAY_NAME ?? '',
                    'item' => optional($value)->DISPLAY_NAME ?? '',
                    'createdBy' => optional($value)->CREATED_BY ?? '',
                    'createdOntime' => date('d-M-Y h:i A', strtotime(optional($value)->CREATED_ON)) ?? '',
                    'taskCreatedOnSort' => date('d-M-Y h:i A', strtotime(optional($value)->CREATED_ON)) ?? '',
                    'taskClosedDateSort' => date('d-M-Y h:i A', strtotime(optional($value)->CLOSED_ON)) ?? '',
                    'dueDateAmPm' => date('d-M-Y h:i A', strtotime(optional($value)->DUE_DATE)) ?? '',
                    'requestDueDate' => date('d-M-Y h:i A', strtotime(optional($value)->DUE_DATE)) ?? '',
                    'requestDueDateObject' => optional($value)->DUE_DATE ?? '',
                    'taskCreatedOn' => date('d-M-Y h:i A', strtotime(optional($value)->CREATED_ON)) ?? '',
                    'taskClosedOn' => date('d-M-Y h:i A', strtotime(optional($value)->CLOSED_ON)) ?? '',
                    'taskSub' => optional($value)->SUBJECT ?? '',
                    'assignedOn' => date('d-M-Y h:i A', strtotime(optional($value)->ASSIGNED_ON)) ?? '',
                    'createdon' => date('d-M-Y h:i A', strtotime(optional($value)->CREATED_ON)) ?? '',
                    'status' => optional($value)->STATUS ?? '',
                    'progressStatus' => optional($value)->PROGRESS ?? '',
                    'taskNo' => optional($value)->TASK_NO ?? '',
                    'taskNof' => optional($value)->TASK_NO ?? '',
                    'isClosed' => optional($value)->IS_CLOSED ?? '',
                    'mobile' => optional($value)->MOBILE_NUMBER ?? '',
                    'effort' => optional($value)->EFFORT ?? '',
                    'feedbackPoint' => optional($value)->FEEDBACK_POINT ?? '',
                    'feedbackRemarks' => optional($value)->FEEDBACK_REMARKS ?? '',
                    'url' => "https://tickets.iskconbangalore.net/public/attachments/",
                    'requestComments' => optional($value)->CLOSURE_REMARKS ?? ''
                ];
            }

           $this->apiResponse['successCode'] = 1;
           $this->apiResponse['message'] = 'Successful';
           $this->apiResponse['data']    = $data;

           return response()->json($this->apiResponse);           
            
        } catch (\Exception $e) {
            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error ! Please Try Again';
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }
    public function getMyTicketDetails(Request $request)
    {
        try {
            $data = [];

            $validator = Validator::make($request->all(), [
                'userId'         => 'required',
                'ticketId'       => 'required',
                'projectIdValue' => 'required'
            ]);    

            if ($validator->fails()){

                $this->apiResponse['successCode'] = 0;
                $this->apiResponse['message'] = $validator->errors()->all();                    

                return response()->json($this->apiResponse);
            }
            $tickets = Ticket::query()
            ->leftJoin('mstr_users','ticket.TECHNICIAN_ID','=','mstr_users.EMPLOYEE_ID')
            ->leftJoin('lkp_category','ticket.CATEGORY_ID','=','lkp_category.CATEGORY_ID')
            ->leftJoin('lkp_sub_category','ticket.SUB_CATEGORY_ID','=','lkp_sub_category.SUB_CATEGORY_ID')
            ->where('ticket.PROJECT_ID',  $request->projectIdValue)
            ->where('ticket.REQUESTED_BY',  $request->userId)
            ->where('ticket.TICKET_ID',   $request->ticketId)
            ->orderBy('TICKET_ID','DESC')
            ->select('ticket.*','mstr_users.USER_NAME as TECHNICIAN_NAME','mstr_users.LOGIN_ID as TECHNICIAN_LOGIN_ID','lkp_category.DISPLAY_NAME as CATEGORY','lkp_sub_category.DISPLAY_NAME as SUB_CATEGORY')
            ->get();

            $count = 1;

            foreach ($tickets as $key => $value) {

                $data[$key] = [
                    
                    'requestNo' => optional($value)->TICKET_NO,
                    'selNo' => $count++,
                    'requestId' => optional($value)->TICKET_ID,
                    'assetId' =>   optional($value)->ASSET_ID,
                    'trustCode' => optional($value)->TRUST_CODE,
                    'requestSubject' => optional($value)->SUBJECT,
                    'requestDescription' => optional($value)->DESCRIPTION,
                    'taskDescription' => optional($value)->DESCRIPTION,
                    'taskTeamName' => optional($value)->TEAM_NAME,
                    'taskTeamId' => '',
                    'taskTechnicianName' => optional($value)->TECHNICIAN_NAME,
                    'taskTechnicianId' => optional($value)->TECHNICIAN_ID,
                    'taskStatus' => optional($value)->STATUS,
                    'userId' => $value->REQUESTED_BY,
                    'requesterName' => optional($value)->USER_NAME,
                    'requesterExtension' => optional($value)->EXTENSION,
                    'departmentCode' => optional($value)->DEPARTMENT_CODE,
                    'departmentName' => optional($value)->DEPARTMENT_NAME,
                    'mode' => optional($value)->MODE,
                    'priority' => optional($value)->PRIORITY,
                    'requestAssignedTo' => optional($value)->TECHNICIAN_NAME,
                    'requestTechnicianLoginId' => optional($value)->TECHNICIAN_LOGIN_ID,
                    'requestTeamId' => optional($value)->TEAM_ID,
                    'requestTeamName' => optional($value)->TEAM_NAME,
                    'requestGroup' => optional($value)->TASK_TYPE_NAME,
                    'requestGroupId' => optional($value)->TASK_TYPE_ID,
                    'requestAttachment' => optional($value)->ATTACHMENT,
                    'requestCreatedBy' => optional($value)->CREATED_BY,
                    'requestStatus' => optional($value)->STATUS,
                    'taskType' => optional($value)->TASK_TYPE_ID,
                    'category' => optional($value)->CATEGORY,
                    'subcategory' => optional($value)->SUB_CATEGORY,
                    'taskTypeId' => optional($value)->TASK_SUBTYPE_ID,
                    'categoryeId' => optional($value)->CATEGORY_ID,
                    'subcategoryId' => optional($value)->SUB_CATEGORY_ID,
                    'itemTypeId' => optional($value)->ITEM_TYPE_ID,
                    'itemId' => optional($value)->ITEM_ID,
                    'taskCost' => optional($value)->COST,
                    'itemType' => optional($value)->ITEM_TYPE_DISPLAY_NAME,
                    'item' => optional($value)->DISPLAY_NAME,
                    'createdBy' => optional($value)->CREATED_BY,
                    'createdOntime' => date('d-M-Y h:i A', strtotime(optional($value)->CREATED_ON)),
                    'taskCreatedOnSort' => date('d-M-Y h:i A', strtotime(optional($value)->CREATED_ON)),
                    'taskClosedDateSort' => date('d-M-Y h:i A', strtotime(optional($value)->CLOSED_ON)),
                    'dueDateAmPm' => date('d-M-Y h:i A', strtotime(optional($value)->DUE_DATE)),
                    'requestDueDate' => date('d-M-Y h:i A', strtotime(optional($value)->DUE_DATE)),
                    'requestDueDateObject' => optional($value)->DUE_DATE,
                    'taskCreatedOn' => date('d-M-Y h:i A', strtotime(optional($value)->CREATED_ON)),
                    'taskClosedOn' => date('d-M-Y h:i A', strtotime(optional($value)->CLOSED_ON)),
                    'taskSub' => optional($value)->SUBJECT,
                    'assignedOn' => date('d-M-Y h:i A', strtotime(optional($value)->ASSIGNED_ON)),
                    'createdon' => date('d-M-Y h:i A', strtotime(optional($value)->CREATED_ON))  ,  
                    'status' => optional($value)->STATUS,
                    'progressStatus' => optional($value)->PROGRESS,
                    'taskNo' => optional($value)->TASK_NO,
                    'taskNof' => optional($value)->TASK_NO,
                    'isClosed' => optional($value)->IS_CLOSED,
                    'mobile' => optional($value)->MOBILE_NUMBER,
                    'effort' => optional($value)->EFFORT,
                    'feedbackPoint' => optional($value)->FEEDBACK_POINT,
                    'feedbackRemarks' => optional($value)->FEEDBACK_REMARKS,
                    'url' => "http://dhananjaya.iskconbangalore.net:8081/itimsdocs/tickets/",
                    'requestComments' => optional($value)->CLOSURE_REMARKS
                ];                
            }

           $this->apiResponse['successCode'] = 1;
           $this->apiResponse['message'] = 'Successful';
           $this->apiResponse['data']    = $data;

           return response()->json($this->apiResponse);           
            
        } catch (\Exception $e) {

            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error ! Please Try Again';
            $this->apiResponse['data'] = [];

            return response()->json($e->getMessage());
        }
    }
    public function getMyWorkUpdates(Request $request)
    {
        try {
            $data = [];

            $validator = Validator::make($request->all(), [
                'loginId'         => 'required',
                'requestId'       => 'required',
            ]);    

            if ($validator->fails()){

                $this->apiResponse['successCode'] = 0;
                $this->apiResponse['message'] = $validator->errors()->all();                    

                return response()->json($this->apiResponse);
            }            
        
            $updates = TicketUpdate::query()
            ->leftJoin('ticket','ticket_updates.TICKET_ID','=','ticket.TICKET_ID')
            ->leftJoin('mstr_users','ticket_updates.TECHNICIAN','=','mstr_users.EMPLOYEE_ID')
            ->where('ticket_updates.TICKET_ID',$request->requestId)
            ->select('ticket_updates.*','mstr_users.USER_NAME','ticket.DESCRIPTION as TICKET_DESCRIPTION','ticket.TICKET_NO as TICKET_NO','ticket.USER_NAME as REQUESTER_NAME')
            ->get();

            foreach ($updates as $key => $value) {

                $data[$key] = [

                    'logDate'          => date('d-M-Y',strtotime(optional($value)->LOG_DATE)),
                    'taskDescription'  => optional($value)->TICKET_DESCRIPTION,
                    'technician'       => optional($value)->USER_NAME,
                    'requestNo'        => optional($value)->TICKET_NO,
                    'description'      => optional($value)->DESCRIPTION,
                    'progressStatus'   => optional($value)->STATUS,
                    'workAttachment'   => url('updates/attachments' . optional($value)->ATTACHMENT),
                    'userName'         => optional($value)->REQUESTER_NAME,
                ];   
            }

           $this->apiResponse['successCode'] = 1;
           $this->apiResponse['message'] = 'Successful';
           $this->apiResponse['data']    = $data;

           return response()->json($this->apiResponse);
           
            
        } catch (\Exception $e) {

            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error ! Please Try Again';
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }
    public function insertTicketDetails(Request $request)
    {
        try {
            $data = [];
            $accessToken = $this->getAccessToken();

            $data['accessToken'] = $accessToken;
            
            // $url = 'https://hr.iskconbangalore.net/v1/api/profile/get-personal-details';
            $url = env('APP_URL') . '/employee';

            // Make the HTTP POST request
            $response = Http::post($url, [
                'accessKey' => $this->accessKey,
                'hrEmployeeId' => $request->userId
            ]);

            if ($response->successful()) {
                // If successful, decode the JSON response
                $responseData = $response->json();                  
                
                $mailId = $responseData['data'][0]['emailId'];
                $deptCode = $responseData['data'][0]['department_code'];
                $deptName = $responseData['data'][0]['departmentName'];
                
            } else {
                // If the request failed, handle the error
                $statusCode = $response->status();
                $mailId = "";
                $deptCode ="";
                $deptName ="";
            }
           
            $validator = Validator::make($request->all(), [
                'description'    => 'nullable',
                'subject'        => 'required',
                'priority'       => 'nullable',
                'userId'         => 'nullable',
                'projectIdValue' => 'nullable',
                'loginId'        => 'nullable',
                'requesterName'  => 'nullable',
                'extension'      => 'nullable',
                'deptCode'       => 'nullable',
                'deptName'       => 'nullable',
                'category'       => 'nullable',
                'subCategory'    => 'nullable',
                'itemType'       => 'nullable',
                'item'           => 'nullable',
                'file'           => 'nullable',
                'files'          => 'nullable',
                'requesterMail'  => 'nullable',
                'employeeMail'   => 'nullable',
                'fileName'       => 'nullable',
                'teamName'       => 'nullable',
                'callMode'       => 'nullable',
            ]);            
    
            if ($validator->fails()){
                $this->apiResponse['successCode'] = 0;
                $this->apiResponse['message'] = $validator->errors()->all();                   

                return response()->json($this->apiResponse);
           }
           
           $projectId = '1';
           if(request('projectIdValue')){
             $projectId = request('projectIdValue');
           }
           
           $departmentCode = DB::table('department_details')
                    ->where('DEPARTMENT_ID', $projectId)
                    ->value('DEPARTMENT_CODE');
                       
            // $serialNumber = 'IT2024159297';
            $result= \DB::select("CALL generate_ticket_no(?, @batchCode)", [$projectId]);
            
           $result2 = \DB::select('SELECT @batchCode AS batchCode');
           if($result2 && isset($result2[0]->batchCode)) {
                $serialNumber = $result2[0]->batchCode;  
                }
            // Insert new ticket into the tickets table
            DB::transaction(function () use ($serialNumber, $request,$mailId, $deptCode,$projectId, $deptName ) {

                // Insert new ticket into the tickets table
                $ticket = Ticket::create([
                    'TICKET_NO'       => $serialNumber,
                    'TASK_NO'         => 0,
                    'PROJECT_ID'      => $projectId,
                    'TEAM_NAME'       => $request->teamName,
                    'MODE'            => $request->callMode,
                    'SUBJECT'         => $request->subject,
                    'DESCRIPTION'     => $request->description,
                    'PRIORITY'        => $request->priority,
                    'REQUESTED_BY'    => $request->loginId,
                    'USER_NAME'       => $request->requesterName,
                    'USER_MAIL'       => $mailId,
                    'DEPARTMENT_CODE' => $deptCode,
                    'DEPARTMENT_NAME' => $deptName,
                    'CREATED_BY'      => $request->requesterName,
                    'CREATED_ON'      => now(),
                ]);
                
              
                if ($request->has('file')) {
                    // Get the array of files
                    $files = $request->input('file');
                
                    // Retrieve the TICKET_ID of the inserted ticket
                    $ticketId = DB::getPdo()->lastInsertId();
                
                    // Initialize the iteration number
                    $iterationNumber = 0;
                
                    // Iterate over each file in the files array
                    foreach ($files as $fileData) {
                        // Get the base64 encoded file and original file name
                        $base64File = $fileData['file'];
                        $originalFileName = $fileData['fileName'];
                
                        // Generate a unique filename
                        $fileName = $serialNumber . '_' .  uniqid() . '_' . $originalFileName ;
                        if($originalFileName){    
                            // Call the external API
                            $response = Http::post('https://tickets.iskconbangalore.net/api/upload-image', [
                                'file' => $base64File,
                                'fileName' => $fileName,
                            ]);                    
                    
                            // Insert attachment details into the ticket_attachment table
                            DB::table('ticket_attachment')->insert([
                                'TICKET_ID' => $ticketId,
                                'ATTACHMENT' => $fileName,                        
                            ]);
                        }

                        $iterationNumber++;
                    }
                }
                
                $templateName = $request->templateName;

                if($templateName)
                {
                    $templateId = DB::table('mstr_templates')
                            ->where('TEMPLATE_NAME',$templateName)
                            ->value('TEMPLATE_ID');
                    
                    $tasks = DB::table('mstr_task_details')
                            ->where('TEMPLATE_ID',$templateId)
                            ->where('IS_ACTIVE','Y')
                            ->get();

                    foreach($tasks  as $task)
                    {
                        $lastId = Ticket::where('LINKED_TO',$ticket->TICKET_ID)->latest('TICKET_ID')->value('TASK_NO');

                        // Increment the last ID
                        $lastId++;

                        $last3 = str_pad($lastId, 3, '0', STR_PAD_LEFT);

                        $pretask = new Ticket;

                        $pretask->TICKET_NO       = $ticket->TICKET_NO;
                        $pretask->TASK_NO         = $last3;
                        $pretask->PROJECT_ID      = $ticket->PROJECT_ID;
                        $pretask->LINKED_TO       = is_null($ticket->LINKED_TO) ? $ticket->TICKET_ID : $ticket->LINKED_TO;
                        $pretask->MODE            = $ticket->MODE;
                        $pretask->SUBJECT         = $task->TASK_NAME;
                        $pretask->PRIORITY        = $ticket->PRIORITY;
                        $pretask->TEAM_NAME       = $ticket->TEAM_NAME;
                        $pretask->REQUESTED_BY    = $ticket->REQUESTED_BY;
                        $pretask->USER_NAME       = $ticket->USER_NAME;
                        $pretask->USER_MAIL       = $ticket->USER_MAIL;
                        $pretask->DEPARTMENT_CODE = $ticket->DEPARTMENT_CODE;
                        $pretask->DEPARTMENT_NAME = $ticket->DEPARTMENT_NAME;
                        $pretask->CREATED_BY      = 'Ticketadmin';
                        $pretask->CREATED_ON      = now();

                        $pretask->save();
                    } 
                }


                $response = Http::post('https://hr.iskconbangalore.net/v1/api/login/employee-fcmid', [
                    'accessKey'  => '729!#kc@nHKRKkbngsppnsg@491', 
                    'employeeID' =>  $request->loginId
                    // Add any parameters required by the API
                ]);

                // Check if the request was successful
                if ($response->successful()) {
                    // API call was successful, handle response
                    $responseData = $response->json(); // Get response data as JSON
                    // Process the response data
                    // Example: $responseData['data']
                    $fcmId = $responseData['fcmId'][0]['FCM_ID'];

                    $ticketUserName = $request->employeeName;
                    $ticketDepartmentName = $request->deptName;

                    $formattedName = $ticketUserName . " (" . $ticketDepartmentName . ")";

                    $body =  $serialNumber ." - ". $request->subject ." (" . $ticketDepartmentName .")";
                    
                    $title = "Ticket Logged";
                    if($fcmId)
                    {
                     $this->sendNotification($fcmId, $body , $title);
                    
                    }

                } else {
                    // API call failed
                    $statusCode = $response->status(); // Get HTTP status code
                    // Handle error based on status code
                    // Example: Log error, throw exception, etc.
                    $data[] = $statusCode;
                }

                if($mailId){
                    
                    $mailFrom = DB::table('department_details')
                                ->where('DEPARTMENT_ID', $projectId)
                                ->first();

                   
                    // Compose email subject
                    $subject = "Your request has been logged with Request ID ## {$serialNumber} ##";

                    // Compose email body
                    $body = "<html><body>";
                    $body .= "<p>Hare Krishna {$request->employeeName},</p>";
                    $body .= "<p>Thank you for contacting Service Desk. We acknowledge your request for: [{$request->subject}].<br><br>";
                    $body .= "The Request ID is {$serialNumber}. Please refer to this ID if you need to contact {$mailFrom->DEPARTMENT_NAME} for any clarifications.<br><br>";
                    $body .= "Our team member will revert on your request shortly.</p>";
                    $body .= "<p>Kind Regards,<br>{$mailFrom->DISPLAY_NAME} Team<br><br>";
                    $body .= "Note: This is an auto-generated email from our ticketing system.</p>"; 
                    $body .= "</body></html>";
                    
                    $mailId = $mailId;

                    config([
                        'mail.mailers.smtp.host' => $mailFrom->MAIL_HOST,
                        'mail.mailers.smtp.port' => 587,
                        'mail.mailers.smtp.encryption' => 'tls',
                        'mail.mailers.smtp.username' => $mailFrom->MAIL_USERNAME,
                        'mail.mailers.smtp.password' => $mailFrom->MAIL_PASSWORD,
                        'mail.from.address' => $mailFrom->SUPPORT_EMAIL_ID,
                        'mail.from.name' => $mailFrom->DISPLAY_NAME,
                    ]); 

                    if($projectId == 1){
                        // Send email
                        Mail::html($body, function ($message) use ($subject, $mailId, $mailFrom) {
                            $message->from($mailFrom->SUPPORT_EMAIL_ID, $mailFrom->DISPLAY_NAME)
                            ->to($mailId)->subject($subject);
                            // ->bcc('ashwini.e@iskconbangalore.org');
                        });
                    }
                    else{
                        // Send email
                        Mail::html($body, function ($message) use ($subject, $mailId, $mailFrom) {
                            $message->from($mailFrom->SUPPORT_EMAIL_ID, $mailFrom->DISPLAY_NAME)
                            ->to($mailId)->subject($subject);
                        });
                    }                    
                }  
                
                createLogActivity('App Log Ticket',$serialNumber,'TICKET NO',$request->requesterName);  
            });
            $this->apiResponse['successCode'] = 1;
            $this->apiResponse['message'] = 'Successful';
            $this->apiResponse['data']    = $data;

            return response()->json($this->apiResponse);
            
        } catch (\Exception $e) {
            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error ! Please Try Again';
            // $this->apiResponse['message'] = $e->getMessage();
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }
    public function getCatgeoryList(Request $request)
    {
        $data = [];

        $validator = Validator::make($request->all(), [
            'loginId'         => 'required',
            'projectId'       => 'nullable',
        ]);

        if ($validator->fails()){

            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = $validator->errors()->all();                   

            return response()->json($this->apiResponse);
        }

        $deptId = $request->projectId;
        
        try {
            $data = [];

            $categories = Category::query()
                                ->when($deptId, fn($q) => $q->where('DEPARTMENT_ID',$deptId))
                                ->orderBy('DISPLAY_NAME','ASC')            
                                ->get();
          
            foreach ($categories as $key => $value) {
                $data[$key] = [
                    'displayId' => isset($value['CATEGORY_ID']) ? $value['CATEGORY_ID'] : null,
                    'displayName' => isset($value['DISPLAY_NAME']) ? $value['DISPLAY_NAME'] : null,
                ];
            }            

            $this->apiResponse['successCode'] = 1;
            $this->apiResponse['message'] = 'Success';
            $this->apiResponse['data'] = $data;

            return response()->json($this->apiResponse);
        } catch (\Exception $e) {
            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error ! Please Try Again';
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }
    public function getSubCatgeoryList(Request $request)
    {  
        try {            
            $data = [];

            $validator = Validator::make($request->all(), [
                'loginId'         => 'required',
                'projectId'       => 'nullable',
                'category'        => 'required',
            ]);

            if ($validator->fails()){

                $this->apiResponse['successCode'] = 0;
                $this->apiResponse['message'] = $validator->errors()->all();                    

                return response()->json($this->apiResponse);
            }

           $deptId = $request->projectId;

            $subCategories = DB::table('lkp_sub_category')           
                ->when($deptId, fn($query) => $query->where('lkp_sub_category.DEPARTMENT_ID',$deptId))
                ->when(request('category'), fn ($query) => $query->where('lkp_sub_category.CATEGORY_ID', request('category')))
                ->orderBy('lkp_sub_category.DISPLAY_NAME','ASC') 
                ->get();

            foreach ($subCategories as $key => $value) {
                $data[$key] = [
                    'displayId' => optional($value)->SUB_CATEGORY_ID,
                    'displayName' => optional($value)->DISPLAY_NAME,
                ];
            }

            $this->apiResponse['successCode'] = 1;
            $this->apiResponse['message'] = '';
            $this->apiResponse['data'] = $data;

            return response()->json($this->apiResponse);
        } catch (\Exception $e) {
            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error getting subcategories: ' ;
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }
    public function getItemList(Request $request)
    {    
        try {
            $data = [];

            $validator = Validator::make($request->all(), [
                'loginId'         => 'required',
                'category'        => 'required',
                'subCategory'     => 'required',
                'itemType'        => 'required',
            ]);

            if ($validator->fails()){

                $this->apiResponse['successCode'] = 0;
                $this->apiResponse['message'] = $validator->errors()->all();                    

                return response()->json($this->apiResponse);
            }

            $items = DB::table('lkp_item')               
                ->when(request('itemTypeId'), fn ($query) => $query->where('lkp_item.ITEM_TYPE_ID', request('itemTypeId')))
                ->when(request('category'), fn ($query) => $query->where('lkp_item.CATEGORY_ID', request('category')))
                ->when(request('subCategory'), fn ($query) => $query->where('lkp_item.SUB_CATEGORY_ID', request('subCategory')))
                ->select('lkp_item.*')
				->orderBy('lkp_item.DISPLAY_NAME','ASC') 
                ->get();

            foreach ($items as $key => $value) {
                $data[$key] = [

                    'itemId'          => optional($value)->ITEM_ID,
                    'itemName'        => optional($value)->DISPLAY_NAME,
                ];
            }

            $this->apiResponse['successCode'] = 1;
            $this->apiResponse['message'] = 'Success';
            $this->apiResponse['data'] = $data;

            return response()->json($this->apiResponse);

        } catch (\Exception $e) {
            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Failed to fetch item types ';
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }
    public function getItemTypeList(Request $request)
    {
        try {
            $data = [];

            $validator = Validator::make($request->all(), [
                'loginId'         => 'required',
                'category'        => 'required',
                'subCategory'     => 'required',
            ]);

            if ($validator->fails()){

                $this->apiResponse['successCode'] = 0;
                $this->apiResponse['message'] = $validator->errors()->all();                    

                return response()->json($this->apiResponse);
            }

            $itemTypes = DB::table('lkp_item_type')                 
                ->when(request('category'), fn ($query) => $query->where('lkp_item_type.CATEGORY_ID', request('category')))
                ->when(request('subCategory'), fn ($query) => $query->where('lkp_item_type.SUB_CATEGORY_ID', request('subCategory')))
                ->select('lkp_item_type.*')				
				->orderBy('lkp_item_type.DISPLAY_NAME','ASC') 
                ->get();

            foreach ($itemTypes as $key => $value) {
                $data[$key] = [
                    'displayId'      => optional($value)->ITEM_TYPE_ID,
                    'displayName'    => optional($value)->DISPLAY_NAME,
                ];
            }

            $this->apiResponse['successCode'] = 1;
            $this->apiResponse['message'] = 'Success';
            $this->apiResponse['data'] = $data;

            return response()->json($this->apiResponse);

        } catch (\Exception $e) {
            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Failed to fetch item types ';
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }
    public function insertTicketFeedback(Request $request)
    {
        try {
            $data = [];

            $validator = Validator::make($request->all(), [

                'ticketId'        => 'required',
                'feedbackPoint'   => 'nullable',
                'feedbackRemark'  => 'nullable',
            ]);

            if ($validator->fails()){

                $this->apiResponse['successCode'] = 0;
                $this->apiResponse['message'] = $validator->errors()->all();                    

                return response()->json($this->apiResponse);
            }

            $ticket = Ticket::find($request->ticketId);
            $ticket->FEEDBACK_POINT = $request->feedbackPoint;
            $ticket->FEEDBACK_REMARKS = $request->feedbackRemark;
            $ticket->FEEDBACK_ON = now();

            $ticket->save();

            $this->apiResponse['successCode'] = 1;
            $this->apiResponse['message'] = 'Success';
            $this->apiResponse['data'] = $data;

            return response()->json($this->apiResponse);

        } catch (\Exception $e) {
            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error Please try again !!';
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }
    public function getProjectList(Request $request)
    {
        try {
            $data = [];

            $validator = Validator::make($request->all(), [
                'loginId'         => 'required',
            ]);

            if ($validator->fails()){

                $this->apiResponse['successCode'] = 0;
                $this->apiResponse['message'] = $validator->errors()->all();                    

                return response()->json($this->apiResponse);
            }

            $userId = DB::table('mstr_users')
                        ->where('LOGIN_ID', $request->loginId)
                        ->value('USER_ID');

            $userDepartments = DB::table('map_user_department')
                                    ->where('USER_ID', $userId)
                                    ->where('IS_ACTIVE', 'Y')
                                    ->pluck('DEPARTMENT_ID');

            $departments = Department::whereIn('DEPARTMENT_ID', $userDepartments)
                                    ->where('IS_ACTIVE', 'Y')
                                    ->orderBy('DEPARTMENT_NAME', 'ASC')
                                    ->get();

            foreach ($departments as $key => $value) {
                $data[$key] = [

                    'projectId'      => optional($value)->DEPARTMENT_ID,
                    'projectName'    => optional($value)->DEPARTMENT_NAME,
                    'status'         => optional($value)->IS_ACTIVE,
                ];
            }

            $this->apiResponse['successCode'] = 1;
            $this->apiResponse['message'] = 'Success';
            $this->apiResponse['data'] = $data;

            return response()->json($this->apiResponse);

        } catch (\Exception $e) {
            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Failed to fetch item types: ' . $e->getMessage();
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }
    public function getDepartmentList(Request $request)
    {      
        try {
            $data = [];

            $validator = Validator::make($request->all(), [
                'loginId'         => 'required',
            ]);

            if ($validator->fails()){

                $this->apiResponse['successCode'] = 0;
                $this->apiResponse['message'] = $validator->errors()->all();                    

                return response()->json($this->apiResponse);
            }

           $departments = Department::get();

            foreach ($departments as $key => $value) {
                $data[$key] = [

                    'projectId'      => optional($value)->DEPARTMENT_ID,
                    'projectName'    => optional($value)->DEPARTMENT_NAME,
                    'status'         => optional($value)->IS_ACTIVE,
                ];
            }

            $this->apiResponse['successCode'] = 1;
            $this->apiResponse['message'] = 'Success';
            $this->apiResponse['data'] = $data;

            return response()->json($this->apiResponse);

        } catch (\Exception $e) {
            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Failed to fetch item types' ;
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }
    public function getTechnicianId(Request $request)
    { 
        try {
            $data = [];

            $validator = Validator::make($request->all(), [
                'loginId'         => 'required',                
            ]);

            if ($validator->fails()){

                $this->apiResponse['successCode'] = 0;
                $this->apiResponse['message'] = $validator->errors()->all();                    

                return response()->json($this->apiResponse);
            }
            
            $user = User::where('EMPLOYEE_ID',$request->loginId)->first();

            $technician = DB::table('map_user_department')
                            ->where('USER_ID',$user->USER_ID)
                            ->where('ROLE','Technician')
                            ->exists();

            $data[] = [
                'technicianId'   => optional($user)->LOGIN_ID,
                'isApprover'     => $technician,
            ];

            $this->apiResponse['successCode'] = 1;
            $this->apiResponse['message'] = 'Success';
            $this->apiResponse['data'] = $data;

            return response()->json($this->apiResponse);

        } catch (\Exception $e) {
            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Failed to fetch item types ' ;
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }

    public function getTicketDetails(Request $request)
    {
        try {
            $data = [];

            $validator = Validator::make($request->all(), [
                'ticketId'       => 'required',
                'projectIdValue' => 'required'
            ]);

            if ($validator->fails()){

                $this->apiResponse['successCode'] = 0;
                $this->apiResponse['message'] = $validator->errors()->all();                    

                return response()->json($this->apiResponse);
            }

            $value = Ticket::query()
                            ->leftJoin('mstr_users','ticket.TECHNICIAN_ID','=','mstr_users.EMPLOYEE_ID')
                            ->leftJoin('lkp_category','ticket.CATEGORY_ID','=','lkp_category.CATEGORY_ID')
                            ->leftJoin('lkp_sub_category','ticket.SUB_CATEGORY_ID','=','lkp_sub_category.SUB_CATEGORY_ID')
                            ->leftJoin('lkp_item_type','lkp_item_type.ITEM_TYPE_ID','=','ticket.ITEM_TYPE_ID')
                            ->leftJoin('lkp_item','lkp_item.ITEM_ID','=','ticket.ITEM_ID')        
                            ->leftJoin('lkp_task_type', 'lkp_task_type.TASK_TYPE_ID', '=',  'ticket.TASK_TYPE_ID')   
                            ->where('ticket.PROJECT_ID',  $request->projectIdValue)
                            ->where('ticket.TICKET_ID',   $request->ticketId)
                            ->orderBy('TICKET_ID','DESC')
                            ->select('ticket.*',
                                    'mstr_users.USER_NAME as TECHNICIAN_NAME',
                                    'mstr_users.LOGIN_ID as TECHNICIAN_LOGIN_ID',
                                    'lkp_category.DISPLAY_NAME as CATEGORY',
                                    'lkp_sub_category.DISPLAY_NAME as SUB_CATEGORY',
                                    'lkp_item_type.DISPLAY_NAME as itemTypeName',
                                    'lkp_item.DISPLAY_NAME as itemName',
                                    'lkp_task_type.SLA as sla')
                            ->first();

            $totalTimeConsumed = $this->getTimeLeft(request('ticketId'), $value->PROJECT_ID);
            $sla = optional($value)->sla;
            $slaBreach = 'N';
            if($sla){
                $slaBreach = ($totalTimeConsumed > ($sla * 60)) ? 'Y' : 'N';
            }

            $count = 1;

            $data[] = [
                'requestNo' => optional($value)->TICKET_NO ?? '',
                'selNo' => $count++,
                'requestId' => optional($value)->TICKET_ID ?? '',
                'assetId' => optional($value)->ASSET_ID ?? '',
                'trustCode' => optional($value)->TRUST_CODE ?? '',
                'requestSubject' => optional($value)->SUBJECT ?? '',
                'requestDescription' => optional($value)->DESCRIPTION ?? '',
                'taskDescription' => optional($value)->DESCRIPTION ?? '',
                'taskTeamName' => optional($value)->TEAM_NAME ?? '',
                'taskTeamId' => '',
                'taskTechnicianName' => optional($value)->TECHNICIAN_NAME ?? '',
                'taskTechnicianId' => optional($value)->TECHNICIAN_ID ?? '',
                'taskStatus' => optional($value)->STATUS ?? '',
                'userId' => $value->REQUESTED_BY ?? '',
                'requesterName' => optional($value)->USER_NAME ?? '',
                'requesterExtension' => optional($value)->EXTENSION ?? '',
                'departmentCode' => optional($value)->DEPARTMENT_CODE ?? '',
                'departmentName' => optional($value)->DEPARTMENT_NAME ?? '',
                'mode' => optional($value)->MODE ?? '',
                'priority' => optional($value)->PRIORITY ?? '',
                'requestAssignedTo' => optional($value)->TECHNICIAN_NAME ?? '',
                'requestTechnicianLoginId' => optional($value)->TECHNICIAN_LOGIN_ID ?? '',
                'requestTeamId' => optional($value)->TEAM_ID ?? '',
                'requestTeamName' => optional($value)->TEAM_NAME ?? '',
                'requestGroup' => optional($value)->TASK_TYPE_NAME ?? '',
                'requestGroupId' => optional($value)->TASK_TYPE_ID ?? '',

                'requestAttachment' => $this->getAttachmentsForAPI(optional($value)->TICKET_ID) ?? '',
                
                'requestCreatedBy' => optional($value)->CREATED_BY ?? '',
                'requestStatus' => optional($value)->STATUS ?? '',
                'taskType' => optional($value)->TASK_TYPE_ID ?? '',

                'category' => optional($value)->CATEGORY ?? '',
                'categoryeId' => optional($value)->CATEGORY_ID ?? '',
                'subcategory' => optional($value)->SUB_CATEGORY ?? '',
                'subcategoryId' => optional($value)->SUB_CATEGORY_ID ?? '',
                'taskTypeId' => optional($value)->TASK_SUBTYPE_ID ?? '',
                'itemType' => optional($value)->itemTypeName ?? '',
                'itemTypeId' => optional($value)->ITEM_TYPE_ID ?? '',
                'item' => optional($value)->itemName ?? '',
                'itemId' => optional($value)->ITEM_ID ?? '',

                'taskCost' => optional($value)->COST ?? '',
                'createdBy' => optional($value)->CREATED_BY ?? '',
                'createdOntime' => date('d-M-Y h:i A', strtotime(optional($value)->CREATED_ON)) ?? '',
                'taskCreatedOnSort' => date('d-M-Y h:i A', strtotime(optional($value)->CREATED_ON)) ?? '',
                'taskClosedDateSort' => date('d-M-Y h:i A', strtotime(optional($value)->CLOSED_ON)) ?? '',
                'dueDateAmPm' => date('d-M-Y h:i A', strtotime(optional($value)->DUE_DATE)) ?? '',
                'requestDueDate' => date('d-M-Y h:i A', strtotime(optional($value)->DUE_DATE)) ?? '',
                'requestDueDateObject' => optional($value)->DUE_DATE ?? '',
                'taskCreatedOn' => date('d-M-Y h:i A', strtotime(optional($value)->CREATED_ON)) ?? '',
                'taskClosedOn' => date('d-M-Y h:i A', strtotime(optional($value)->CLOSED_ON)) ?? '',
                'taskSub' => optional($value)->SUBJECT ?? '',
                'assignedOn' => date('d-M-Y h:i A', strtotime(optional($value)->ASSIGNED_ON)) ?? '',
                'createdon' => date('d-M-Y h:i A', strtotime(optional($value)->CREATED_ON)) ?? '',
                'status' => optional($value)->STATUS ?? '',
                'progressStatus' => optional($value)->PROGRESS ?? '',
                'taskNo' => optional($value)->TASK_NO ?? '',
                'taskNof' => optional($value)->TASK_NO ?? '',
                'isClosed' => optional($value)->IS_CLOSED ?? '',
                'mobile' => optional($value)->MOBILE_NUMBER ?? '',
                'effort' => optional($value)->EFFORT ?? '',
                'feedbackPoint' => optional($value)->FEEDBACK_POINT ?? '',
                'feedbackRemarks' => optional($value)->FEEDBACK_REMARKS ?? '',
                'url' => "https://tickets.iskconbangalore.net/public/updates/",
                // 'url' => "http://192.168.3.250/tickets/public/updates/",
                'requestComments' => optional($value)->CLOSURE_REMARKS ?? '',

                'sla'           => optional($value)->sla,
                'slaBreach'     => $slaBreach, 
                'timeConsumed'  =>$totalTimeConsumed,
            ];              

           $this->apiResponse['successCode'] = 1;
           $this->apiResponse['message'] = 'Successful';
           $this->apiResponse['data']    = $data;

           return response()->json($this->apiResponse);
           
            
        } catch (\Exception $e) {

            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error ! Please Try Again';
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }
    public function getTechnicianList(Request $request)
    {
        try {
            $data = [];

            $validator = Validator::make($request->all(), [
                'loginId'       => 'required',
                'projectId' => 'required'
            ]);    

            if ($validator->fails()){

                $this->apiResponse['successCode'] = 0;
                $this->apiResponse['message'] = $validator->errors()->all();                    

                return response()->json($this->apiResponse);
            }

            $users = User::query()
                        ->leftJoin('map_user_department','mstr_users.USER_ID','=','map_user_department.USER_ID')
                        ->where('ROLE','Technician')
                        ->where('DEPARTMENT_ID',request('projectId'))
                        ->select('mstr_users.*','map_user_department.DEPARTMENT_ID')
                        ->get();
    
            foreach ($users as $key => $value) {

                $data[$key] = [                    
                    'groupId'    => optional($value)->DEPARTMENT_ID,
                    'technician' => optional($value)->USER_NAME,
                    'userId'     => optional($value)->USER_ID,
                    'userName'   => optional($value)->LOGIN_ID,                        
                ];                
            }

           $this->apiResponse['successCode'] = 1;
           $this->apiResponse['message'] = 'Successful';
           $this->apiResponse['data']    = $data;

           return response()->json($this->apiResponse);          
            
        } 
        catch (\Exception $e) {
            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error ! Please Try Again';
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }
    public function updateAssignTicket(Request $request)
    {
        try {
            $data = [];

            $validator = Validator::make($request->all(), [
                'technicianId' => 'required',
                'requestId'    =>  'required',
            ]);    

            if ($validator->fails()){

                $this->apiResponse['successCode'] = 0;
                $this->apiResponse['message'] = $validator->errors()->all();                  

                return response()->json($this->apiResponse);
            }

            $ticket = Ticket::find($request->requestId);

            $ticket->TECHNICIAN_ID = $request->technicianId;

            $ticket->save();

            $this->apiResponse['successCode'] = 1;
            $this->apiResponse['message'] = 'Successful';
            $this->apiResponse['data']    = $data;

           return response()->json($this->apiResponse);           
            
        } catch (\Exception $e) {

            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error ! Please Try Again';
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }
    public function getWorkUpdates(Request $request)
    {
        try {
            $data = [];

            $validator = Validator::make($request->all(), [
                'loginId'      => 'required',
                'requestId'    =>  'required',
            ]);    

            if ($validator->fails()){

                $this->apiResponse['successCode'] = 0;
                $this->apiResponse['message'] = $validator->errors()->all();                   

                return response()->json($this->apiResponse);
            }

            $updates = TicketUpdate::query()
                    ->leftJoin('ticket','ticket_updates.TICKET_ID','=','ticket.TICKET_ID')
                    ->leftJoin('mstr_users','ticket_updates.TECHNICIAN','=','mstr_users.EMPLOYEE_ID')
                    ->leftJoin('ticket_attachment','ticket_updates.TICKET_UPDATE_ID','=','ticket_attachment.TICKET_UPDATE_ID')
                    ->where('ticket_updates.TICKET_ID',$request->requestId)
                    ->select('ticket_updates.*','ticket_attachment.ATTACHMENT as FILE_NAME', 'mstr_users.USER_NAME','ticket.DESCRIPTION as TICKET_DESCRIPTION','ticket.TICKET_NO as TICKET_NO','ticket.USER_NAME as REQUESTER_NAME')
                    ->get();

            foreach ($updates as $value) {
                $data[] = [

                    'logDate'          => date('d-M-Y H:i:s',strtotime(optional($value)->LOG_DATE)),
                    'taskDescription'  => optional($value)->TICKET_DESCRIPTION,
                    'technician'       => optional($value)->USER_NAME,
                    'requestNo'        => optional($value)->TICKET_NO,
                    'description'      => optional($value)->DESCRIPTION,
                    'progressStatus'   => optional($value)->STATUS,
                    'workAttachment'   => url('https://tickets.iskconbangalore.net/public/updates/' . optional($value)->FILE_NAME),
                    // 'workAttachment'   => url(' http://192.168.3.250/tickets/public/updates/' . optional($value)->FILE_NAME),
                   
                    'userName'         => optional($value)->REQUESTER_NAME,
                ];   

            }
            if($data){
                $this->apiResponse['successCode'] = 1;
                $this->apiResponse['message'] = 'Successful';
                $this->apiResponse['data']    = $data;

                return response()->json($this->apiResponse);
            }
            else{
                $this->apiResponse['successCode'] = 0;
                $this->apiResponse['message'] = 'No Record Found';
                $this->apiResponse['data']    = [];

                return response()->json($this->apiResponse);
            }          
            
        } catch (\Exception $e) {

            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error ! Please Try Again';
            $this->apiResponse['data'] = [];

           return response()->json($this->apiResponse);
        }
    }
    public function categorizeRequest(Request $request)
    {
        try {
            $data = [];

            $validator = Validator::make($request->all(), [
                'loginId'      => 'required',
                'requestId'    => 'required',
                'taskType'     => 'required|integer',
                'taskSubType'  => 'required|integer',
                'category'     => 'required|integer',
                'subCategory'  => 'required|integer',
                'itemType'     => 'required|integer',
                'item'         => 'required|integer',
                'trustCode'    => 'nullable', // Adjust the validation rule as per your requirement
                'assetId'      => 'sometimes|nullable|integer', // 'sometimes' and 'nullable' means it's optional, but if present, it should be an integer
            ]);    

            if ($validator->fails()){

                $this->apiResponse['successCode'] = 0;
                $this->apiResponse['message'] = $validator->errors()->all();                    

                return response()->json($this->apiResponse);
            }

            $ticket = Ticket::find($request->requestId);

            $ticket->CATEGORY_ID       = $request->category;
            $ticket->SUB_CATEGORY_ID   = $request->subCategory;
            $ticket->ITEM_TYPE_ID      = $request->itemType;
            $ticket->ITEM_ID           = $request->item;
            $ticket->TRUST_CODE        = $request->filled('trustCode') ? $request->trustCode :  $ticket->TRUST_CODE;
            $ticket->ASSET_ID          = $request->filled('assetId') ? $request->assetId :  $ticket->ASSET_ID;
            $ticket->MODIFIED_BY       = $request->userId;
            $ticket->MODIFIED_ON       = now();

            $ticket->save();

            $this->apiResponse['successCode']  = 1;
            $this->apiResponse['message']      = 'Successful';
            $this->apiResponse['data']         = $ticket;

            return response()->json($this->apiResponse);
            
        } catch (\Exception $e) {

            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error ! Please Try Again';
            // $this->apiResponse['message'] = $e->getMessage();
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }
    // Mobile Ticket Close API
    public function updateRequestClosed(Request $request)
    {
        try {
            $data = [];

            $validator = Validator::make($request->all(), [
                'loginId'      => 'required',
                'effort'       => 'required',
                'cost'         => 'nullable',
                'taskStatus'   => 'required',
                'createdBy'    => 'required',
                'taskRequestId'=> 'required',
                'taskRemarks'  => 'nullable',
                'taskClosure'  => 'required',
                'attachment'   => 'nullable'
            ]);    

            if ($validator->fails()){

                $this->apiResponse['successCode'] = 0;
                $this->apiResponse['message'] = $validator->errors()->all();                    

                return response()->json($this->apiResponse);
            }

            $results = Ticket::where('LINKED_TO', $request->taskRequestId)
                            ->whereIn('PROGRESS',['Open','New','On Hold','In Progress'])
                            ->get();
            
            if($results->count() != 0)
            {     
                $this->apiResponse['successCode']  =  0;
                $this->apiResponse['message']      = 'Please close the subtasks';
                $this->apiResponse['data']         =  [];

                return response()->json($this->apiResponse);
            }

            $ticket = Ticket::find($request->taskRequestId);

	        $ticket->IS_CLOSED         = 'N';
            $ticket->CLOSURE_CODE      = $request->taskClosure;// 'Completed / Cancelled/ Transfered/ Deferred',
            $ticket->CLOSURE_REMARKS   = $request->taskRemarks;//'Closure Remarks',
            $ticket->CLOSED_BY         = $request->loginId;// 'Master: MSTR_USERS.LOGIN_ID',
            $ticket->CLOSED_ON         = now();// 'Date on which the request is closed',
            $ticket->COST              = $request->cost;//'Amount spent on the ticket',
            $ticket->EFFORT            = $request->effort;//'Effort spent on the tricket',
            $ticket->PROGRESS          = $request->taskStatus;
            $ticket->STATUS            = 'Completed';
            $ticket->MODIFIED_BY       = $request->loginId;
            $ticket->MODIFIED_ON       = now();

            if($request->taskStatus == 'Resolved')
            {
                $isReleaseTicket = TicketPoints::where(['TICKET_ID'=> $request->taskRequestId]) 
                                                        ->orderBy('POINT_ID','DESC')                           
                                                        ->first();

                if($isReleaseTicket)
                {
                    if($isReleaseTicket->STATUS == 'Release')
                    {
                        $ticketPoints = TaskType::where('TASK_TYPE_ID', $ticket->TASK_TYPE_ID)->first();
                        $sla = $ticketPoints->SLA;
                        if($sla!=0)
                        {
                            $totalTimeConsumed = $this->getTimeLeft($request->taskRequestId, $ticket->PROJECT_ID);

                            if ($totalTimeConsumed < ($sla * 60)) { // SLA in minutes
                                $ticket->POINTS = $ticketPoints->POINTS + 5;                                
                            }
                            else{
                                $ticket->POINTS = $ticketPoints->POINTS;
                            } 
                        }
                        else{
                            $ticket->POINTS = $ticketPoints->POINTS;
                        }
                           
                    }else{
                        $ticketPoints = TaskType::where('TASK_TYPE_ID', $ticket->TASK_TYPE_ID)->first();
                        $sla = $ticketPoints->SLA;
                        if($sla!=0)
                        {
                            $totalTimeConsumed = $this->getTimeLeft($request->taskRequestId, $ticket->PROJECT_ID);

                            if ($totalTimeConsumed < ($sla * 60)) { // SLA in minutes
                                $ticket->POINTS = $ticketPoints->POINTS;
                            }
                            else{
                                $ticket->POINTS = -($ticketPoints->POINTS);                               
                            } 
                        }
                        else{
                            $ticket->POINTS = $ticketPoints->POINTS;
                        }                           
                    }
                    
                }else{
                    $ticketPoints = TaskType::where('TASK_TYPE_ID', $ticket->TASK_TYPE_ID)->first();
                    $sla = $ticketPoints->SLA;
                    if($sla!=0)
                    {
                        $totalTimeConsumed = $this->getTimeLeft($request->taskRequestId, $ticket->PROJECT_ID);

                        if ($totalTimeConsumed < ($sla * 60)) { // SLA in minutes
                            $ticket->POINTS = $ticketPoints->POINTS;

                        }
                        else{
                            $ticket->POINTS = -($ticketPoints->POINTS);                            
                        } 
                    }
                    else{
                        $ticket->POINTS = $ticketPoints->POINTS;
                    }              
                }                   
            }  
            
            $isSlaExist = TaskType::where('TASK_TYPE_ID', $ticket->TASK_TYPE_ID)->first();
            if($isSlaExist)
                {
                $sla = $isSlaExist->SLA; //0
                if($sla!= 0)
                {                    
                    $totalTimeConsumed = $this->getTimeLeft($request->taskRequestId, $ticket->PROJECT_ID);
                    if ($totalTimeConsumed < ($sla * 60)) { // SLA in minutes
                        $ticket->IS_SLA_BREACH = 'N';
                    }
                    else{
                        $ticket->IS_SLA_BREACH = 'Y';
                    } 
                }
                else{
                    $ticket->IS_SLA_BREACH = 'N';
                } 
            }
            
            $ticket->save();

            $logUpdate = new TicketUpdate;
            
            $logUpdate->TICKET_ID   = $request->taskRequestId;
            $logUpdate->TECHNICIAN  = $ticket->TECHNICIAN_ID ? $ticket->TECHNICIAN_ID : $request->loginId;
            $logUpdate->LOG_DATE    = now();
            $logUpdate->STATUS      = $request->taskStatus;
            $logUpdate->DESCRIPTION = $request->taskRemarks;
            if ($request->hasFile('attachments')) {
                $logUpdate->ATTACHMENT = 'Y';
            }
            $logUpdate->save();

            // Log status movement
            $logStatusMovement = DB::table('log_status_movement')->insert([
                'TICKET_ID' => $request->taskRequestId,
                'CHANGED_TO' => $request->taskStatus,
                'CHANGED_BY' => $request->loginId,
                'CHANGED_ON' => now(),
            ]);

            // Check if an attachment is present in the request
            if ($request->hasFile('attachments')) {

                $base64File = $request->attachments;
                
                $imageName = $request->taskRequestId . '_'. uniqid() .'_' . $request->fileName;
                
                // Call the external API
                $response = Http::post('https://tickets.iskconbangalore.net/api/update-attachments', [
                    'file' => $base64File,
                    'fileName' => $imageName,
                ]);  

                // Insert attachment details into the ticket_attachment table
                DB::table('ticket_attachment')->insert([
                    'TICKET_ID'  => $request->taskRequestId,
                    'ATTACHMENT' => $imageName,
                    'TICKET_UPDATE_ID' => $logUpdate->TICKET_UPDATE_ID,
                ]);                    
            } 

            $mailFrom = DB::table('department_details')
                                ->where('DEPARTMENT_ID',$ticket->PROJECT_ID)
                                ->first();

		    if($request->taskStatus == 'Cancelled')
            {
                // Compose email subject
                $subject = "Request: ##  {$ticket->TICKET_NO} ## raised by you was cancelled.";

                // Compose email body
                $body  = "<!DOCTYPE html> <html><body>  ";
                $body .= "<p>Hare Krishna {$ticket->USER_NAME},</p>";
                $body .= "<p>Note that the request raised by you was cancelled.<br><br>";
                $body .= "The request was for: {$ticket->SUBJECT}.<br>";
                $body .= "Cancel Remarks: {$request->taskRemarks}</p>";
                $body .= "<p>Kind Regards,<br>{$mailFrom->DISPLAY_NAME} Team<br><br>";
                $body .= "Note: This is an auto-generated email from our ticketing system.</p>"; 
                $body .= "</body></html>";

                $mailId = $ticket->USER_MAIL;

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
            if($request->taskStatus == 'Resolved')
            {
                $response = Http::post('https://hr.iskconbangalore.net/v1/api/login/employee-fcmid', [
                    'accessKey'  => '729!#kc@nHKRKkbngsppnsg@491', 
                    'employeeID' =>  $ticket->REQUESTED_BY
                ]);

                // Check if the request was successful
                if ($response->successful()) {
                    // API call was successful, handle response
                    $responseData = $response->json(); // Get response data as JSON
                    // Process the response data
                    // Example: $responseData['data']
                    $fcmId = $responseData['fcmId'][0]['FCM_ID'];

                    $ticketUserName = $ticket->USER_NAME;
                    $ticketDepartmentName = $ticket->DEPARTMENT_NAME;

                    $formattedName = $ticketUserName . " (" . $ticketDepartmentName . ")";

                    $body =  $ticket->TICKET_NO ." - ". $formattedName;
                    
                    $title = "Ticket Resolved";

                    if($fcmId){
                        $this->sendNotification($fcmId, $body , $title);
                    }

                } else {
                    // API call failed
                    $statusCode = $response->status(); // Get HTTP status code
                    // Handle error based on status code                  
                    $data[] = $statusCode;
                }
            

                // Compose email subject
                $subject = "Request Id : ## {$ticket->TICKET_NO} ## has been resolved.";

                // Compose email body
                $body  = "<!DOCTYPE html> <html><body>  ";
                $body .= "<p>Hare Krishna {$ticket->USER_NAME},</p>";
                $body .= "<p>This is to inform you that our Service Desk has resolved Ticket No. ## {$ticket->TICKET_NO} ## for [{$ticket->SUBJECT}]<br><br>";
                $body .= "Closure Remarks: {$request->taskRemarks}<br><br>";
                $body .= "We value your feedback. Please provide using the ISKCON Service APP.<br><br>";
                $body .= "If the ticket has not been resolved, please reply to this email to reopen the ticket.<br>";
                $body .= "If there is no response from you, we will assume that the ticket has been resolved and the ticket will be automatically closed after 48 hours.</p>";
                $body .= "<p>With Regards,<br>{$mailFrom->DISPLAY_NAME} Team<br><br>";
                $body .= "Note: This is an auto-generated email from our ticketing system.</p>";
                $body .= "</body></html>";

                $mailId = $ticket->USER_MAIL;

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

            $this->apiResponse['successCode']  = 1;
            $this->apiResponse['message']      = 'Successful';
            $this->apiResponse['data']         = $data;

            return response()->json($this->apiResponse);
            
        } catch (\Exception $e) {

            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = "Error ! Please Try Again";
            $this->apiResponse['error'] = $e->getMessage();
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }
    public function saveWorkUpdates(Request $request)
    {
        try {
            $data = [];
 
            $validator = Validator::make($request->all(), [
                'accessKey'         => 'required',
                'loginId'           => 'required',
                'workRemarks'       => 'required',
                'ticketId'          => 'required',
                'workUpdateStatus'  => 'required',
                'progressCode'      => 'required',
                'requestId'         => 'required',
            ]);
 
            if ($validator->fails()){
 
                $this->apiResponse['successCode'] = 0;
                $this->apiResponse['message'] = $validator->errors()->all();                  
 
                return response()->json($this->apiResponse);
            }
 
            $ticket = Ticket::where(['TICKET_ID' => $request->ticketId])->first();
 
            if($request->progressCode == 'In Progress' || $request->progressCode == 'On Hold') 
            { 
                $ticket->PROGRESS = $request->progressCode;
                $ticket->STATUS = 'Open';              
            }
           
            if($request->progressCode == 'Release'){
                $ticket->IS_RELEASED = 'Y';
                $ticket->PROGRESS = 'New';
                $ticket->STATUS = 'New';  
                $ticket->TECHNICIAN_ID = null;
            }  
            
            $ticket->save();                 
 
            $logUpdate = new TicketUpdate;
           
            $logUpdate->TICKET_ID   = $request->ticketId;
            $logUpdate->TECHNICIAN  = $request->loginId;
            $logUpdate->LOG_DATE    = now();
            $logUpdate->STATUS      = $request->progressCode;
            $logUpdate->DESCRIPTION = $request->workRemarks;
            $logUpdate->REASON      = $request->onholdReason;
 
            if($request->progressCode == 'Release'){
                $taskType = TaskType::find($ticket->TASK_TYPE_ID);

                if ($taskType) {                   

                    $ticketPoints = new TicketPoints;
           
                    $ticketPoints->TICKET_ID      = $request->ticketId;
                    $ticketPoints->TECHNICIAN_ID  = $request->loginId;
                    $ticketPoints->STATUS         = $request->progressCode;
                    $ticketPoints->POINTS         = -($taskType->POINTS);
                    $ticketPoints->STATUS_DATE    = now();

                    $ticketPoints->save();
                }
            }
            if($request->filled('attachments')){
                $logUpdate->ATTACHMENT = 'Y';
            }
            $logUpdate->save();
 
            // Log status movement
            $logStatusMovement = DB::table('log_status_movement')->insert([
                'TICKET_ID' => $request->ticketId,
                'CHANGED_TO' => $request->progressCode,
                'CHANGED_BY' => $request->loginId,
                'CHANGED_ON' => now(),
            ]);
           
            // Check if an attachment is present in the request
            if($request->fileName){
                if ($request->filled('attachments')) {
 
                    $base64File = $request->attachments;
                
                    $imageName = $request->ticketId . '_'.  uniqid(). '_' . $request->fileName;
    
                    // Call the external API
                    $response = Http::post('https://tickets.iskconbangalore.net/api/update-attachments', [
                        'file' => $base64File,
                        'fileName' => $imageName,
                    ]);  
    
                    // Insert attachment details into the ticket_attachment table
                    DB::table('ticket_attachment')->insert([
                        'TICKET_ID'  => $request->ticketId,
                        'ATTACHMENT' => $imageName,
                        'TICKET_UPDATE_ID' => $logUpdate->TICKET_UPDATE_ID,
                    ]);                    
                } 
            }
                   
           
            $this->apiResponse['successCode']  = 1;
            $this->apiResponse['message']      = 'Successful';
            $this->apiResponse['data']         = $ticket;
 
            return response()->json($this->apiResponse);
           
        } catch (\Exception $e) {
 
            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error ! Please Try Again';
            // $this->apiResponse['message'] = $e->getMessage();
            $this->apiResponse['data'] = [];
 
            return response()->json($this->apiResponse);
        }
    }
    public function getTicketList(Request $request)
    {
        try {
            $data = [];

            $validator = Validator::make($request->all(), [
                'loginId'           => 'required',
                'categoryId'        => 'nullable',
                'ticketId'          => 'nullable',
                'status'            => 'nullable',      
                'progress'          => 'nullable',
                'ticketType'        => 'nullable',
                'deptName'          => 'nullable',
                'myTaskUserName'    => 'nullable',
                'projectIdValue'    => 'nullable', 
            ]);  

            if ($validator->fails()){

                $this->apiResponse['successCode'] = 0;
                $this->apiResponse['message'] = $validator->errors()->all();                    

                return response()->json($this->apiResponse);
            }

            if($request->status)
            {
                $status=$request->status;
                $wthoutSts='';
            }else{
                $status='';
                $wthoutSts='Yes';
            }

            $selfAssignTicket = $request->status === 'New' ? 'Yes' : 'No';
			            
            $userDet = User::where('EMPLOYEE_ID', request('loginId'))->first();
            $login_id = $userDet->USER_ID;			
            
            $tickets = Ticket::query()            
                    ->leftJoin('mstr_users','ticket.TECHNICIAN_ID','=','mstr_users.EMPLOYEE_ID')
                    ->leftJoin('lkp_category','ticket.CATEGORY_ID','=','lkp_category.CATEGORY_ID')
                    ->leftJoin('lkp_task_type','lkp_task_type.TASK_TYPE_ID','=','ticket.TASK_TYPE_ID')
                    ->leftJoin('lkp_sub_category','ticket.SUB_CATEGORY_ID','=','lkp_sub_category.SUB_CATEGORY_ID')
                    ->leftJoin('team', 'team.TEAM_NAME', '=', 'ticket.TEAM_NAME')
                    ->leftJoin('map_user_team', 'map_user_team.TEAM_ID', '=', 'team.TEAM_ID')                     
                    // ->where('ticket.IS_CLOSED','N')
                    
                    ->when($status, function ($query) use ($status, $login_id){
                        if ($status === 'New') {
                            return $query->where('ticket.STATUS', $status)
                                ->where(function ($subQuery) use ($login_id) {
                                    $subQuery->where('map_user_team.USER_ID', $login_id)
                                            ->orWhereNull('ticket.TEAM_NAME');
                                });
                               
                        } else {
                            return $query->where('ticket.STATUS', $status)
                            ->where('ticket.TECHNICIAN_ID', request('loginId'));
                        }
                    }) 
                        
                    ->when($wthoutSts, function ($query) use ($wthoutSts) {
                        return $query->whereIn('ticket.STATUS', ['New','Open','Completed'])
                            ->where('ticket.TECHNICIAN_ID', request('loginId'))
                            ->where('IS_CLOSED', 'N');
                    })
                    
                    // ->when(request('loginId'),        fn ($query) => $query->where('ticket.TECHNICIAN_ID', request('loginId')))
                    ->when(request('categoryId'),     fn ($query) => $query->where('ticket.CATEGORY_ID', request('categoryId')))
                    ->when(request('ticketId'),       fn ($query) => $query->where('ticket.TICKET_NO', request('ticketId')))
                    // ->when(request('status'),       fn ($query) => $query->where('ticket.STATUS', request('status')))
                    ->when(request('progress'),       fn ($query) => $query->where('ticket.PROGRESS', request('progress')))
                    ->when(request('team'),       fn ($query) => $query->where('ticket.TEAM_NAME', request('team')))
                    ->when(request('ticketType'),       fn ($query) => $query->where('ticket.TASK_TYPE_ID', request('ticketType')))
                    // ->when(request('deptName'),       fn ($query) => $query->where('ticket.PROJECT_ID', request('deptName')))
                    ->when(request('myTaskUserName'), fn ($query) => $query->where('ticket.USER_NAME', 'like', '%' . request('myTaskUserName') . '%'))
                    ->when(request('projectIdValue'), fn ($query) => $query->where('ticket.PROJECT_ID', request('projectIdValue')))
                    ->groupBy('ticket.TICKET_ID')
                    ->orderByRaw("CASE
                        WHEN ticket.PROGRESS = 'Open' THEN 1
                        WHEN ticket.PROGRESS = 'In Progress' THEN 2
                        WHEN ticket.PROGRESS = 'On Hold' THEN 3
                        WHEN ticket.PROGRESS = 'Reopened' THEN 4
                        ELSE 5               
                        END")
                    ->orderBy('ticket.CREATED_ON','desc')
                    ->select('ticket.*',
                            'mstr_users.USER_NAME as TECHNICIAN_NAME',
                            'mstr_users.LOGIN_ID as TECHNICIAN_LOGIN_ID',
                            'lkp_category.DISPLAY_NAME as CATEGORY',
                            'lkp_task_type.TASK_TYPE_ID',
                            'lkp_task_type.SLA as sla',
                            'lkp_sub_category.DISPLAY_NAME as SUB_CATEGORY'
                            )
					
                    ->get();                   

            $count = 1;

            foreach ($tickets as $value) {     
                
                $totalTimeConsumed = $this->getTimeLeft(optional($value)->TICKET_ID, $value->PROJECT_ID);
                $sla = optional($value)->sla;
                $slaBreach = 'N';
                if($sla){
                    $slaBreach = ($totalTimeConsumed > ($sla * 60)) ? 'Y' : 'N';
                }

                $data []= [
                    'requestNo' => optional($value)->TICKET_NO ?? '',
                    'selNo' => $count++,
                    'requestId' => optional($value)->TICKET_ID ?? '',
                    'assetId' => optional($value)->ASSET_ID ?? '',
                    'trustCode' => optional($value)->TRUST_CODE ?? '',
                    'requestSubject' => optional($value)->SUBJECT ?? '',
                    'requestDescription' => optional($value)->DESCRIPTION ?? '',
                    'taskDescription' => optional($value)->DESCRIPTION ?? '',
                    'taskTeamName' => optional($value)->TEAM_NAME ?? '',
                    'taskTeamId' => '',
                    'taskTechnicianName' => optional($value)->TECHNICIAN_NAME ?? '',
                    'taskTechnicianId' => optional($value)->TECHNICIAN_ID ?? '',
                    'taskStatus' => optional($value)->STATUS ?? '',
                    'status' => optional($value)->STATUS ?? '',
                    'progressStatus' => optional($value)->PROGRESS ?? '',
                    'isReleased' => optional($value)->IS_RELEASED ?? '',
                    'userId' => $value->REQUESTED_BY ?? '',
                    'requesterName' => optional($value)->USER_NAME ?? '',
                    'requesterExtension' => optional($value)->EXTENSION ?? '',
                    'departmentCode' => optional($value)->DEPARTMENT_CODE ?? '',
                    'departmentName' => optional($value)->DEPARTMENT_NAME ?? '',
                    'mode' => optional($value)->MODE ?? '',
                    'priority' => optional($value)->PRIORITY ?? '',
                    'requestAssignedTo' => optional($value)->TECHNICIAN_NAME ?? '',
                    'requestTechnicianLoginId' => optional($value)->TECHNICIAN_LOGIN_ID ?? '',
                    'requestTeamId' => optional($value)->TEAM_ID ?? '',
                    'requestTeamName' => optional($value)->TEAM_NAME ?? '',
                    'requestGroup' => optional($value)->TASK_TYPE_NAME ?? '',
                    'requestGroupId' => optional($value)->TASK_TYPE_ID ?? '',
                  
                    'requestAttachment' =>  $this->gettTicketAttachmentsForAPI(optional($value)->TICKET_ID) ?? '',
                    
                    'requestCreatedBy' => optional($value)->CREATED_BY ?? '',
                    'requestStatus' => optional($value)->STATUS ?? '',
                    'taskType' => optional($value)->TASK_TYPE_ID ?? '',
                    'category' => optional($value)->CATEGORY ?? '',
                    'subcategory' => optional($value)->SUB_CATEGORY ?? '',
                    'taskTypeId' => optional($value)->TASK_SUBTYPE_ID ?? '',
                    'categoryeId' => optional($value)->CATEGORY_ID ?? '',
                    'subcategoryId' => optional($value)->SUB_CATEGORY_ID ?? '',
                    'itemTypeId' => optional($value)->ITEM_TYPE_ID ?? '',
                    'itemId' => optional($value)->ITEM_ID ?? '',
                    'taskCost' => optional($value)->COST ?? '',
                    'itemType' => optional($value)->ITEM_TYPE_DISPLAY_NAME ?? '',
                    'item' => optional($value)->DISPLAY_NAME ?? '',
                    'createdBy' => optional($value)->CREATED_BY ?? '',
                    'createdOntime' => date('d-M-Y h:i A', strtotime(optional($value)->CREATED_ON)) ?? '',
                    'taskCreatedOnSort' => date('d-M-Y h:i A', strtotime(optional($value)->CREATED_ON)) ?? '',
                    'taskClosedDateSort' => date('d-M-Y h:i A', strtotime(optional($value)->CLOSED_ON)) ?? '',
                    'dueDateAmPm' => date('d-M-Y h:i A', strtotime(optional($value)->DUE_DATE)) ?? '',
                    'requestDueDate' => date('d-M-Y h:i A', strtotime(optional($value)->DUE_DATE)) ?? '',
                    'requestDueDateObject' => optional($value)->DUE_DATE ?? '',
                    'taskCreatedOn' => date('d-M-Y h:i A', strtotime(optional($value)->CREATED_ON)) ?? '',
                    'taskClosedOn' => date('d-M-Y h:i A', strtotime(optional($value)->CLOSED_ON)) ?? '',
                    'taskSub' => optional($value)->SUBJECT ?? '',
                    'assignedOn' => date('d-M-Y h:i A', strtotime(optional($value)->ASSIGNED_ON)) ?? '',
                    'createdon' => date('d-M-Y h:i A', strtotime(optional($value)->CREATED_ON)) ?? '',                    
                    'ticketPoints' => optional($value)->POINTS ?? '',
                    'taskNo' => optional($value)->TASK_NO ?? '',
                    'taskNof' => optional($value)->TASK_NO ?? '',
                    'isClosed' => optional($value)->IS_CLOSED ?? '',
                    'mobile' => optional($value)->MOBILE_NUMBER ?? '',
                    'effort' => optional($value)->EFFORT ?? '',
                    'feedbackPoint' => optional($value)->FEEDBACK_POINT ?? '',
                    'feedbackRemarks' => optional($value)->FEEDBACK_REMARKS ?? '',
                    'url' => "https://tickets.iskconbangalore.net/public/attachments/",
                    'requestComments' => optional($value)->CLOSURE_REMARKS ?? '',

                    'sla'           => optional($value)->sla,
                    'slaBreach'     => $slaBreach, 
                    'timeConsumed'  =>$totalTimeConsumed,                    
                ];
            }
                
            if($data){
                $this->apiResponse['successCode'] = 1;
                $this->apiResponse['message'] = 'Successful';
                $this->apiResponse['data']    = $data;

                return response()->json($this->apiResponse);
           }
            else{
                $this->apiResponse['successCode'] = 0;
                $this->apiResponse['message'] = 'No Record Found';
                $this->apiResponse['data']    = [];

                return response()->json($this->apiResponse);
           }           
            
        } catch (\Exception $e) {

            $this->apiResponse['successCode'] = 0;
            // $this->apiResponse['message'] = 'Error ! Please Try Again';
            $this->apiResponse['message'] = $e->getMessage();
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }
    public function getAssetRequired(Request $request)
    {
        try {
            $data = [];

            $validator = Validator::make($request->all(), [
                'subCategory'      => 'nullable', 
                'itemType'         => 'nullable', 
            ]);
                
            if ($validator->fails()){
                $this->apiResponse['successCode'] = 0;
                $this->apiResponse['message'] = $validator->errors()->all();                   

                return response()->json($this->apiResponse);
            }

           $value = ItemType::where('SUB_CATEGORY_ID',$request->subCategory)
                            ->where('ITEM_TYPE_ID',$request->itemType)
                            ->first();                            

            $data[] = [

                'displayId'     => optional($value)->ITEM_TYPE_ID,
                'displayName'   => optional($value)->DISPLAY_NAME,
                'assetIDStatus' => optional($value)->ASSET_ID_REQUIRED
            ];

           $this->apiResponse['successCode'] = 1;
           $this->apiResponse['message'] = 'Successful';
           $this->apiResponse['data']    = $data;

           return response()->json($this->apiResponse);  
        } 
        catch (\Exception $e) {

            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error ! Please Try Again';
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse); 
        }
    }
    public function calculateSLA($assignedOn, $taskType, $technicianId)
    {
        // Retrieve the list of holidays
        $holidays = HolidayList::pluck('HOLIDAY')->toArray();

        // API call to get the technician's week-off holiday
        $weekOffResponse = Http::post('https://hr.iskconbangalore.net/v1/api/profile/get-weekoff-date', [
            'accessKey' => '729!#kc@nHKRKkbngsppnsg@491',
            'employeeId' => $technicianId,
        ]);
        // If the API call is successful, add the week-off day to holidays
        if ($weekOffResponse->successful()) {
            $weekOffData = $weekOffResponse->json(); // Decode the JSON response
            foreach ($weekOffData['data'] as $weekOff) {
                $holidays[] = $weekOff['dates']; // Add each week-off date to the holidays array
            }
        }

        // Retrieve SLA for the given taskType (assumed to be in hours)
        $SLAforTicketTypeHours = optional(TaskType::find($taskType))->SLA ?? 0;

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
        $assignedOnTime = date('H:i:s', strtotime($assignedOn));

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
        return [
            'slaDeadline' => $slaDeadline,
            'idleTime' => $idleTime
        ];
    }

    // Get Ticket Type Lists
    public function getTicketType(Request $request)
    {
        try{
            $data=[];
            $taskType = TaskType::where(['ACTIVE_FLAG' => 'Y'])->get();

            foreach($taskType as $val){ 
                $data[]=[
 
                    'id'     => optional($val)->TASK_TYPE_ID, 
                    'name'   => optional($val)->DISPLAY_NAME,  
                ]; 
            } 
             
            $this->apiResponse['successCode'] = 1; 
            $this->apiResponse['message'] = 'Successful'; 
            $this->apiResponse['data']    = $data;
 
            return response()->json($this->apiResponse);
        }
        catch(\Exception $e){
            $this->apiResponse['successCode'] = 0; 
            $this->apiResponse['message'] = 'Error ! Please Try Again';
            $this->apiResponse['data']    = [];
 
            return response()->json($this->apiResponse);
        }
    }
    // Get Ticket Ids
    public function getTicketIds(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'loginId'=> 'required',
        ]);

        if ($validator->fails()){
            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = $validator->errors()->all();                     
            return response()->json($this->apiResponse);
        }
        try{
            $tickets = Ticket::where(['REQUESTED_BY' => $request->loginId])
                        ->where(function($query) {
                            $query->where('SUBJECT', 'like', '%[E-Mail ID] [DELETION]%')
                                ->orWhere('SUBJECT', 'like', '%[INTERNET ID] [DELETION]%')
                                ->orWhere('SUBJECT', 'like', '%[LOGIN ID] [DELETION]%');
                        })
                        ->get();            
            $data=[];
            foreach($tickets as $val)
            {
                $data[]=[
                    'ticketId'=>$val->TICKET_ID,
                    'ticketNo' => $val->TICKET_NO,
                    'subject' => $val->SUBJECT,
                    'description' => $val->DESCRIPTION,
                    'status' => $val->PROGRESS,
                    'employeeName' => $val->USER_NAME,
                    'employeeMail' => $val->USER_MAIL,     
                    'departmentName' => $val->DEPARTMENT_NAME,        
                ];                              
            }
            
            if($data){
                $this->apiResponse['successCode'] = 1;
                $this->apiResponse['message'] = 'Ticket IDs';
                $this->apiResponse['data'] = $data;
            
                return response()->json($this->apiResponse);
            }
            else{
                $this->apiResponse['successCode'] = 0;
                $this->apiResponse['message'] = 'No Record Found';
                $this->apiResponse['data']= [];
            
                return response()->json($this->apiResponse);
            }            
        }
        catch(\Exception $e){
            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error Please Try again !!';
            $this->apiResponse['data']=[];
            
            return response()->json($this->apiResponse);
        }   
    }

    public function calculateAGE(Request $request)
    {
        // Calculate the difference between current time and creation time
        $createdOn = $request->createdOn;

        $currentTime = date('d-m-Y H:i:s');

        $currentTime = strtotime($currentTime);
        $createdAt = strtotime($createdOn);

        $ageInSeconds = $currentTime - $createdAt;

        // Define working hours
        $startOfDay = 10; // 10 AM
        $endOfDay = 18;   // 6 PM
        $workingDaySeconds = ($endOfDay - $startOfDay) * 60 * 60;


        // Get the holiday list
        $holidays = HolidayList::pluck('HOLIDAY')->toArray();

        if($request->status == 'Open'){
            // API call to get the technician's week-off holiday
            $weekOffResponse = Http::post('https://hr.iskconbangalore.net/v1/api/profile/get-weekoff-date', [
                'accessKey' => '729!#kc@nHKRKkbngsppnsg@491',
                'employeeId' =>  $request->technicianId,
            ]);
            // If the API call is successful, add the week-off day to holidays
            if ($weekOffResponse->successful()) {
                $weekOffData = $weekOffResponse->json(); // Decode the JSON response
                foreach ($weekOffData['data'] as $weekOff) {
                    $holidays[] = $weekOff['dates']; // Add each week-off date to the holidays array
                }
            }
        }

        // Subtract holiday time
        foreach ($holidays as $holiday) {
            $holidayTimestamp = strtotime($holiday); // Convert each holiday to a timestamp

            // Check if the holiday falls between the created date and the current time
            if ($holidayTimestamp >= $createdAt && $holidayTimestamp <= $currentTime) {
                // Subtract 24 hours (86400 seconds) for each holiday
                $ageInSeconds -= (60 * 60 * 24);
            }
        }
    
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
    
        echo $ageString;
    }
    
    public function statusUpdateCal(Request $request)
    {      
        try {          
            $data = [];              
 
            $ticket = Ticket::find($request->ticketId);                        
 
            $ticket->PROGRESS = ($request->status) ? $request->status : 'New';             
 
            if($request->status == 'In Progress') {                
           
                $lastOnholdRecord = TicketUpdate::where(['STATUS' => 'On Hold', 'TICKET_ID' => $request->ticketId])
                                                ->orderBy('LOG_DATE', 'desc')
                                                ->first();
 
                if ($lastOnholdRecord) {
                    $lastOnhold = $lastOnholdRecord->LOG_DATE;
                    // $lastOnhold = $request->ohHoldTime;
                   
                    $ticketTypeHours = TaskType::where(['TASK_TYPE_ID' => $ticket->TASK_TYPE_ID])->first();
                   
                    // Convert $lastOnhold to a DateTime object
                    $lastOnholdDate = new DateTime($lastOnhold);
                   
                    // Get the current date and time
                    $now = new DateTime();                  
                   
                    // Get the holiday list
                    $holidays = HolidayList::pluck('HOLIDAY')->toArray();
 
                    // API call to get the technician's week-off holiday
                    $weekOffResponse = Http::post('https://hr.iskconbangalore.net/v1/api/profile/get-weekoff-date', [
                        'accessKey' => '729!#kc@nHKRKkbngsppnsg@491',
                        'employeeId' => User::find($request->userId)->EMPLOYEE_ID,
                    ]);
                    // If the API call is successful, add the week-off day to holidays
                    if ($weekOffResponse->successful()) {
                        $weekOffData = $weekOffResponse->json(); // Decode the JSON response
                        foreach ($weekOffData['data'] as $weekOff) {
                            $holidays[] = $weekOff['dates']; // Add each week-off date to the holidays array
                        }
                    }
 
                    $ohHoldIsHoliday = in_array($lastOnholdDate->format('Y-m-d'), $holidays);
 
                    // Define business hours
                    $startOfDay = 10;  // 10 AM
                    $endOfDay = 18;    // 6 PM
 
                    $minutesDifference = 0; // Initialize total difference in minutes
 
                    // Loop through each day from lastOnholdDate to now
                    while ($lastOnholdDate < $now) {
                        // Check if the current day is a holiday or weekend
                        $currentDate = $lastOnholdDate->format('Y-m-d');
                        // if (in_array($currentDate, $holidays) || $lastOnholdDate->format('N') >= 6) {
                        if (in_array($currentDate, $holidays) && !$ohHoldIsHoliday) {
                            // Skip holidays and weekends
                            $lastOnholdDate->modify('+1 day')->setTime($startOfDay, 0);
                            continue;
                        }
 
                        // If it's the same day, calculate only the hours remaining within business hours
                        if ($lastOnholdDate->format('Y-m-d') == $now->format('Y-m-d')) {
                            // Calculate time difference within business hours
                            $endOfDayTime = min($now, (clone $lastOnholdDate)->setTime($endOfDay, 0));
                            if ($lastOnholdDate->format('H') >= $endOfDay || $lastOnholdDate->format('H') < $startOfDay) {
                                // If the lastOnhold time is outside business hours, skip this day
                                $lastOnholdDate->modify('+1 day')->setTime($startOfDay, 0);
                                continue;
                            }
                            $diff = $lastOnholdDate->diff($endOfDayTime);
                            $minutesDiff = $diff->h * 60 + $diff->i; // Calculate the difference in minutes
                            $minutesDifference += $minutesDiff; // Add the difference to the total
                            break;
                        } else {
                            // Calculate time for a full business day
                            $endOfDayTime = (clone $lastOnholdDate)->setTime($endOfDay, 0);
                            $startOfDayTime = (clone $lastOnholdDate)->setTime($startOfDay, 0);
 
                            // If starting time is before 10 AM, start from 10 AM
                            if ($lastOnholdDate < $startOfDayTime) {
                                $lastOnholdDate = $startOfDayTime;
                            }
 
                            // If time is before 6 PM, calculate remaining time for the day
                            if ($lastOnholdDate < $endOfDayTime) {
                                $diff = $lastOnholdDate->diff($endOfDayTime);
                                $minutesDiff = $diff->h * 60 + $diff->i; // Convert difference to minutes
                                $minutesDifference += $minutesDiff; // Add the difference to the total
                            }
 
                            // Move to the next day
                            $lastOnholdDate->modify('+1 day')->setTime($startOfDay, 0);
                        }
                    }
                                                      
                    // Initialize SLA variables
                    $dueDate = new DateTime($ticket->DUE_DATE);         
                   
                    while ($minutesDifference > 0) {                      
                        // Calculate the remaining minutes in the current business day
                        $endOfBusinessDay = (clone $dueDate)->setTime($endOfDay, 0);
                        // echo "\nendOfBusinessDay ",$endOfBusinessDay->format('d-m-Y h:i A');
                        $availableMinutes = max(0, ($endOfBusinessDay->getTimestamp() - $dueDate->getTimestamp()) / 60);
  
                        if ($minutesDifference <= $availableMinutes) {  
                            // If remaining SLA fits within the current business day, add it and break
                            $minseconds = $minutesDifference * 60;
                            $dueDate->modify("+$minseconds seconds");               
                            break;
                        }
                        else {                                      
                            // If remaining SLA exceeds current business day's remaining time, subtract and move to next day
                           
                            $availableseconds = $availableMinutes * 60;
                            $dueDate->modify("+$availableseconds seconds"); 
                            $minutesDifference -= $availableMinutes;   
                            // Move to the next business day
                            $dueDate->modify('+1 day')->setTime($startOfDay, 0);
 
                            // Check if the new day is a holiday, if so, skip to the next day
                            while (in_array($dueDate->format('Y-m-d'), $holidays)) {
                                $dueDate->modify('+1 day')->setTime($startOfDay, 0);  // Move to the next valid business day
                            }                   
                        }
                    }    
                    $ticket->DUE_DATE = $dueDate->format('Y-m-d H:i:s');
                    $ticket->IDLE_TIME = $minutesDifference;
                }
            }
            if($request->status == 'On Hold'){
                $lastInProgressTime = TicketUpdate::where(['TICKET_ID' => $request->ticketId])
                                    ->orderBy('LOG_DATE', 'desc')
                                    ->first();
 
                $lastInProgress = '';
                if ($ticket->TIME_CONSUME == '0') {
                    $lastInProgress = new DateTime($ticket->ASSIGNED_ON);
                } else{
                    $lastInProgress = new DateTime($lastInProgressTime->LOG_DATE);
                }        
               
                // Get the current date and time
                $now = new DateTime($request->currentTime);
 
                // Sla Time
                $ticketTypeHours = TaskType::where(['TASK_TYPE_ID' => $ticket->TASK_TYPE_ID])->first('SLA');
               
                // Get the holiday list
                $holidays = HolidayList::pluck('HOLIDAY')->toArray();
 
                // API call to get the technician's week-off holiday
                $weekOffResponse = Http::post('https://hr.iskconbangalore.net/v1/api/profile/get-weekoff-date', [
                    'accessKey' => '729!#kc@nHKRKkbngsppnsg@491',
                    'employeeId' => User::find($request->userId)->EMPLOYEE_ID,
                ]);
                // If the API call is successful, add the week-off day to holidays
                if ($weekOffResponse->successful()) {
                    $weekOffData = $weekOffResponse->json(); // Decode the JSON response
                    foreach ($weekOffData['data'] as $weekOff) {
                        $holidays[] = $weekOff['dates']; // Add each week-off date to the holidays array
                    }
                }
 
                // Define business hours
                $startOfDay = 10;  // 10 AM
                $endOfDay = 18;    // 6 PM
 
                $minutesDifference = 0; // Initialize total difference in minutes
 
                // Loop through each day from lastOnholdDate to now
                while ($lastInProgress < $now) {
                    // Check if the current day is a holiday or weekend
                    $currentDate = $lastInProgress->format('Y-m-d');
                    // if (in_array($currentDate, $holidays) || $lastInProgress->format('N') >= 6) {
                    if (in_array($currentDate, $holidays)) {
                        // Skip holidays and weekends
                        $lastInProgress->modify('+1 day')->setTime($startOfDay, 0);
                        continue;
                    }
 
                    // If it's the same day, calculate only the hours remaining within business hours
                    if ($lastInProgress->format('Y-m-d') == $now->format('Y-m-d')) {
                        // Calculate time difference within business hours
                        $endOfDayTime = min($now, (clone $lastInProgress)->setTime($endOfDay, 0));
                        // if ($lastInProgress->format('H') >= $endOfDay || $lastInProgress->format('H') < $startOfDay) {
                        if ($lastInProgress->format('H') >= $startOfDay && $lastInProgress->format('H') < $endOfDay) {
                            $diff = $lastInProgress->diff($endOfDayTime);
                            $minutesDiff = ($diff->h * 60) + $diff->i;
                            $minutesDifference += $minutesDiff;
                        }                        
                        break;
                    } else {
                        // Calculate time for a full business day
                        $endOfDayTime = (clone $lastInProgress)->setTime($endOfDay, 0);
                        $startOfDayTime = (clone $lastInProgress)->setTime($startOfDay, 0);
 
                        // If starting time is before 10 AM, start from 10 AM
                        if ($lastInProgress < $startOfDayTime) {
                            $lastInProgress = $startOfDayTime;
                        }
 
                        // If time is before 6 PM, calculate remaining time for the day
                        if ($lastInProgress < $endOfDayTime) {
                            $diff = $lastInProgress->diff($endOfDayTime);
                            $minutesDiff = $diff->h * 60 + $diff->i; // Convert difference to minutes
                            $minutesDifference += $minutesDiff; // Add the difference to the total
                        }
                        // Move to the next day
                        $lastInProgress->modify('+1 day')->setTime($startOfDay, 0);
                    }
                }
               
                $ticket->TIME_CONSUME+= $minutesDifference;
               
            }
                 
            $ticket->save();              
           
            $this->apiResponse['successCode']  = 1;
            $this->apiResponse['message']      = 'Successful';
            $this->apiResponse['data']         = $ticket;
 
            return response()->json($this->apiResponse);           
        }
        catch (\Exception $e) {
 
            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error ! Please Try Again';
            // $this->apiResponse['message'] = $e->getMessage();
            $this->apiResponse['data'] = [];
 
            return response()->json($this->apiResponse);
        }
    }

    // Calculation SLA timing
    public function getTicketUpdates($ticketId)
    {
        return DB::table('log_status_movement')
                    ->where('TICKET_ID', $ticketId)
                    ->orderBy('CHANGED_ON', 'asc')
                    ->get();
    }

    public function getTimeLeft($ticketId, $deptId)
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

        // $weekOffResponse = Http::post('https://hr.iskconbangalore.net/v1/api/profile/get-weekoff-date', [
        //     'accessKey' => '729!#kc@nHKRKkbngsppnsg@491',
        //     'employeeId' => $technicianId,
        // ]);
        // // If the API call is successful, add the week-off day to holidays
        // if ($weekOffResponse->successful()) {
        //     $weekOffData = $weekOffResponse->json(); // Decode the JSON response
        //     foreach ($weekOffData['data'] as $weekOff) {
        //         $holidays[] = $weekOff['dates']; // Add each week-off date to the holidays array
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
                            ->where('DEPARTMENT_ID', $deptId)
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
                if ($currentStatus === 'Reopened') {
                    $start = Carbon::parse($updates[$i]->CHANGED_ON);
                    $totalUsedTime = 0;
                }
                        
                $totalUsedTime += $this->calculateWorkingHours($start, $end, $workingStart, $workingEnd, $holidays);
            }
        }

        $slaOn = DB::table('department_details')
                            ->where('DEPARTMENT_ID', $deptId)
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

    // show time left in ISKCON Service app 
    public function slaTimeLeft(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'ticketId'    => 'required',    
                'departmentId' => 'required',           
            ]);           
    
            if ($validator->fails()){
                $this->apiResponse['successCode'] = 0;
                $this->apiResponse['message'] = $validator->errors()->all();                   

                return response()->json($this->apiResponse);
           }

            $data = [];

            $ticketDet = Ticket::leftJoin('lkp_task_type', 'lkp_task_type.TASK_TYPE_ID', '=',  'ticket.TASK_TYPE_ID')
                                ->where('ticket.TICKET_ID', $request->ticketId)
                                ->where('ticket.PROJECT_ID', $request->departmentId)
                                ->select('ticket.TICKET_NO','lkp_task_type.SLA as sla',
                                'ticket.PROGRESS','ticket.STATUS')
                                ->first();

            $totalTimeConsumed = $this->getTimeLeft($request->ticketId,$request->departmentId);
            $sla = $ticketDet->sla;
            $timeShow = '';
            $isTimerRun = 'N';

            // Define working hours
            $startTime = Carbon::createFromTime(10, 0); // 10:00 AM
            $endTime = Carbon::createFromTime(18, 0);  // 6:00 PM

            $currentTime = Carbon::now();
            
            if($sla){
               
                $timeLeft = ($sla * 60) - $totalTimeConsumed;

                if ($timeLeft > 0) {
                    $hours = floor($timeLeft / 60);
                    $minutes = floor($timeLeft % 60);

                    if ($ticketDet->PROGRESS == 'Open' || $ticketDet->PROGRESS == 'In Progress') {
                        $timeShow = "{$hours}h {$minutes}m";
                        $isTimerRun = ($currentTime->between($startTime, $endTime)) ? 'Y' : 'N';
                    }
                    else if($ticketDet->STATUS == 'Completed' || $ticketDet->STATUS == 'Closed'){
                        $timeShow = "";
                        $isTimerRun = 'N';
                    }
                    else{
                        $timeShow = "{$hours}h {$minutes}m";
                        $isTimerRun = 'N';
                    }
                } else {
                    $timeShow = "EXPIRED";
                    $isTimerRun = 'N';
                }
            }
            $data[] = [

                'timeShow'   => $timeShow,
                'isTimerRun' => $isTimerRun,
            ];  

            $this->apiResponse['successCode']  = 1;
            $this->apiResponse['message']      = 'Successful';
            $this->apiResponse['data']         = $data;

            return response()->json($this->apiResponse);
        }
        catch (\Exception $e) 
        {
            $this->apiResponse['successCode']  = 0;
            $this->apiResponse['message']      = "Error please try again !";
            // $this->apiResponse['message']      = $e->getMessage();
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }

    // Get Engineer Point Data
    public function getEngineerPointData(Request $request)
    {
        try{
            $month = $request->month;
            $year = $request->year;
            $empId = $request->empId;
            $departmentId = $request->departmentId;
            
            // Get the first day of the month
            $from_date = date('Y-m-d', strtotime("$year-$month-01"));

            // Get the last day of the month
            $to_date = date('Y-m-t', strtotime("$year-$month-01"));
            
            $teamIds = DB::table('map_user_team')
                    ->leftJoin('mstr_users', 'mstr_users.USER_ID', '=', 'map_user_team.USER_ID')
                    ->where('mstr_users.EMPLOYEE_ID', $empId)
                    ->pluck('map_user_team.TEAM_ID');
                    
            $technicians = User::join('map_user_department', 'mstr_users.USER_ID', '=', 'map_user_department.USER_ID')
                            ->leftJoin('team_members', 'map_user_department.USER_ID', '=', 'team_members.TECHNICIAN')
                            ->leftJoin('team','team.TEAM_ID','=','team_members.TEAM_ID')
                            ->leftJoin('map_user_team', 'map_user_team.USER_ID', '=', 'mstr_users.USER_ID')
                            ->where('map_user_department.DEPARTMENT_ID',$departmentId)               
                            ->where('map_user_department.ROLE','Technician')
                            ->where('team_members.IS_ACTIVE','Y')
                            ->whereIn('map_user_team.TEAM_ID', $teamIds)
                            ->select('mstr_users.EMPLOYEE_ID','mstr_users.USER_NAME','team_members.TECHNICIAN')                
                            ->orderBy('mstr_users.USER_NAME', 'asc')
                            ->distinct()
                            ->get()->toArray();

            $ticketPoints = [];

            foreach ($technicians as $technician) { // Use keys (technician IDs)
                $assignedPoints = Ticket::query()
                                    ->when($from_date && $to_date, function ($query) use($from_date,$to_date) {
                                        return $query->whereDate('ticket.ASSIGNED_ON', '>=', $from_date)
                                                    ->whereDate('ticket.ASSIGNED_ON', '<=', $to_date);
                                    })
                                    ->where('TECHNICIAN_ID', $technician['EMPLOYEE_ID'])
                                    ->where('PROJECT_ID', $departmentId)
                                    // ->where('ticket.TEAM_NAME', 'ERP Support')
                                    ->sum('POINTS'); 
                
                $releasePoints = TicketPoints::where('TECHNICIAN_ID', $technician['EMPLOYEE_ID'])   
                                    ->sum('POINTS'); 

                $breachedPoints = DB::table('breached_tickets_points')                                   
                                    ->when($from_date && $to_date, function ($query) use ($from_date, $to_date) {
                                        return $query->whereDate('CREATED_ON', '>=', $from_date)
                                                    ->whereDate('CREATED_ON', '<=', $to_date);
                                    })                                
                                    ->where('TECHNICIAN_ID', $technician['EMPLOYEE_ID'])
                                    ->sum('POINTS');

                // Combine the points and associate them with the technician's ID
                $ticketPoints[] = [
                    'user' => $technician['USER_NAME'],
                    'empId' => $technician['EMPLOYEE_ID'],
                    'points' => $assignedPoints + $releasePoints + $breachedPoints,
                ];
            }

            $data=[
                'ticketPoints'=>$ticketPoints ?? [0],
            ];

            if (!empty($data['ticketPoints']))
            {
                $this->apiResponse['successCode']  = 1;
                $this->apiResponse['message']      = 'Engineer Total Points';
                $this->apiResponse['data']         = $data;  
                    
                return response()->json($this->apiResponse); 
            }
            else{
                $this->apiResponse['successCode']  = 0;
                $this->apiResponse['message']      = 'No Record Found';
                $this->apiResponse['data']         = [];  
                    
                return response()->json($this->apiResponse); 
            }
        }
        catch (\Exception $e) {
            $this->apiResponse['successCode']  = 0;
            $this->apiResponse['message']      = 'Error Please Try again';
            $this->apiResponse['data']         = [];  
                
            return response()->json($this->apiResponse); 
        }
    }   
}