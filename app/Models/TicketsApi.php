<?php

namespace App\Models;

use CURLFile;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TicketsApi extends Model
{
    use HasFactory;

    public  $baseUrl;
    private $accessKey;

    public function __construct()
    {
        $this->baseUrl   = 'http://localhost/tickets/v1/public/api';   
        // $this->baseUrl   = 'https://tickets.iskconbangalore.net/v1/public/api';  
        $this->accessKey = '450!#kc@nHKRKkbngPiLnsg@498';
    }
    public function getDepartments()
    {
        $data['accessKey'] = $this->accessKey;
        $data['userId']    = Auth::id();

        $response = Http::accept('application/json')->post($this->baseUrl . '/get-departments', $data);

        return $response->collect();
    }

    /**
     * Add New Technician using API
     */
    public function addUser($user_details)
    {        
        // print_r($user_details); exit;

        $data['accessKey'] = $this->accessKey;
        $data['userId'] = Auth::user()->LOGIN_ID;
        $data['loggedinUserId'] = Auth::user()->LOGIN_ID;

        // $data['empName'] = $user_details->emp_name;
        $data['userName'] = $user_details->emp_name;
        $data['email'] = $user_details->user_email;
        $data['mobileNumber'] = $user_details->user_mobile;
        $data['employeeID'] = $user_details->employee_id;
        $data['loginID'] = $user_details->login_id ?? "";
        $data['password'] = $user_details->password ?? "";
        $data['departmentID'] = session('code');
        $data['role'] = $user_details->user_role;
        $data['roleName'] = $user_details->roleName;
        $data['teamId'] = $user_details->teamName;

        $url = $this->baseUrl . '/add-technician';

        $response = Http::accept('application/json')->post($url, $data);

        return $response;
    }
    public function getTickets($data)
    {
        $data['accessKey'] = $this->accessKey;
        $data['userId']    = Auth::user()->LOGIN_ID;

        $response = Http::accept('application/json')->post($this->baseUrl . '/get-tickets', $data);

        return $response->collect();
    }
    public function storeTicket($data)
    {        
        $data['accessKey'] = $this->accessKey;
        $data['userId']    = Auth::user()->LOGIN_ID;

        $attachments = session('attachments'); // Retrieve the array of file paths from the session
        // Add each file to the request using the attach method.

        if (isset($attachments) && !empty($attachments)) 
        {
            foreach ($attachments as $index => $attachment) {

                $data["attachments[{$index}]"] = new CURLFile(public_path("temp-images/{$attachment['name']}"));
            }
        }
       
        $response = Http::accept('application/json')->post($this->baseUrl . '/store-ticket', $data);

        return $response->collect();
    }
    public function getTicket($data)
    {
        $data['accessKey'] = $this->accessKey;
        $data['userId']    = Auth::user()->LOGIN_ID;

        $response = Http::accept('application/json')->post($this->baseUrl . '/get-ticket', $data);

        return $response->collect();
    }
    public function updateTicket($data)
    {        
        $data['accessKey'] = $this->accessKey;
        $data['userId']    = Auth::user()->LOGIN_ID;

        $response = Http::accept('application/json')->post($this->baseUrl .'/update-ticket', $data);

        return $response->collect();
    }
    public function assignTicket($data)
    {        
        $data['accessKey'] = $this->accessKey;
        $data['userId']    = Auth::id();
        $data['userName']  = Auth::user()->LOGIN_ID;
        $data['empId']  = Auth::user()->EMPLOYEE_ID;
        $data['departmentId'] = session('code');

        $response = Http::accept('application/json')->post($this->baseUrl .'/assign-ticket', $data);

        return $response->collect();
    }
    public function assignSelfTicket($data)
    {        
        $data['accessKey'] = $this->accessKey;
        $data['userId']    = Auth::id();
        $data['userName']  = Auth::user()->LOGIN_ID;
        $data['technicianId']  = Auth::user()->EMPLOYEE_ID;
        $data['departmentId'] = session('code');

        $response = Http::accept('application/json')->post($this->baseUrl .'/tickets/assign-self-ticket', $data);

        return $response->collect();
    }
    public function addTask($data)
    {        
        $data['accessKey'] = $this->accessKey;
        $data['userId']    = Auth::user()->LOGIN_ID;

        $response = Http::accept('application/json')->post($this->baseUrl .'/add-task', $data);

        return $response->collect();
    }
    public function getTasks($data)
    {   
        $data['accessKey'] = $this->accessKey;
        $data['userId']    = Auth::id();

        $response = Http::accept('application/json')->post($this->baseUrl .'/get-tasks', $data);

        return $response->collect();
    }
    public function getAttachments($data)
    {   
        $data['accessKey'] = $this->accessKey;
        $data['userId']    = Auth::id();

        $response = Http::accept('application/json')->post($this->baseUrl .'/get-attachments', $data);

        return $response->collect();
    }
    public function removeAttachment($data)
    {   
        $data['accessKey'] = $this->accessKey;
        $data['userId']    = Auth::user()->LOGIN_ID;

        $response = Http::accept('application/json')->post($this->baseUrl .'/remove-attachment', $data);

        return $response->collect();
    }
   
    public function statusUpdateTicket($data)
    {
        $data['accessKey'] = $this->accessKey;
        $data['userId']    =  Auth::id();
        $data['userName']  = Auth::user()->LOGIN_ID;
        
        $response = Http::accept('application/json')->post($this->baseUrl.'/status-update', $data);
      
        return $response->collect();
    }
    public function categorizeTicket($data)
    {   
        $data['accessKey'] = $this->accessKey;
        $data['userId']    = Auth::id();
        $data['userName']  = Auth::user()->LOGIN_ID;

        $response = Http::accept('application/json')->post($this->baseUrl .'/categorize-ticket', $data);

        return $response->collect();
    }
    public function closeTicket($data)
    {   
        $data['accessKey'] = $this->accessKey;
        $data['userId']    =  Auth::id();
        $data['userName']  = Auth::user()->LOGIN_ID;
        $data['departmentId'] = session('code');

        $response = Http::accept('application/json')->post($this->baseUrl .'/close-ticket', $data);

        // print_r($data); exit;

        return $response->collect();
    }
    public function cancelTicket($data)
    {   
        $data['accessKey'] = $this->accessKey;
        $data['userId']    = Auth::id();
       

        $response = Http::accept('application/json')->post($this->baseUrl .'/cancel-ticket', $data);

        return $response->collect();
    }
    public function reopenTicket($data)
    {   
        $data['accessKey'] = $this->accessKey;
        $data['userId']    = Auth::id();
        $data['userName']  = Auth::user()->LOGIN_ID;

        $response = Http::accept('application/json')->post($this->baseUrl .'/reopen-ticket', $data);

        return $response->collect();
    }
    public function getUpdates($data)
    {   
        $data['accessKey'] = $this->accessKey;
        $data['userId']    = Auth::user()->LOGIN_ID;
        $data['baseUrl'] =  url('public/updates/');

        $response = Http::accept('application/json')->post($this->baseUrl .'/get-updates', $data);

        return $response->collect();
    }
    public function updateTask($data)
    {   
        $data['accessKey'] = $this->accessKey;
        $data['userId']    = Auth::user()->LOGIN_ID;

        $response = Http::accept('application/json')->post($this->baseUrl .'/update-task', $data);

        return $response->collect();
    }
    public function storeSubcategoryTask($data)
    {   
        $data['accessKey'] = $this->accessKey;
        $data['userId']    =  Auth::user()->LOGIN_ID;
        $data['departmentId'] = session('code');

        $response = Http::accept('application/json')->post($this->baseUrl .'/store-predefined-tasks', $data);

        return $response->collect();
    }
    public function updateTemplate($data)
    {   
        $data['accessKey'] = $this->accessKey;
        $data['userId']    =  Auth::user()->LOGIN_ID;
        $data['departmentId'] = session('code');

        $response = Http::accept('application/json')->post($this->baseUrl .'/update-template', $data);

        return $response->collect();
    }

    public function getPredefinedTasks($data)
    {   
        $data['accessKey'] = $this->accessKey;
        $data['userId']    = Auth::id();
        $data['departmentId'] = session('code');

        $response = Http::accept('application/json')->post($this->baseUrl .'/get-predefined-tasks', $data);

        return $response->collect();
    }
    public function getTeams()
    {
        $data['accessKey'] = $this->accessKey;
        $data['departmentId'] = session('code');

        $response = Http::accept('application/json')->post($this->baseUrl . '/get-teams', $data);

        return $response->collect();
    }

    public function getUserBaseTeams()
    {
        $user = Auth::user(); // Get the logged-in user
        // $isAdmin = $user->is_admin; // Example check for admin

        $isAdmin = (userRoleName() == 'Admin') ? 'Yes' : '';
        
        $query = DB::table('team')
            ->where('DEPARTMENT_ID',session('code'))
            ->leftJoin('map_user_team', 'map_user_team.TEAM_ID', '=', 'team.TEAM_ID') 
            ->select('team.TEAM_ID as teamId', 'team.TEAM_NAME as teamName')
            ->distinct();

        if (!$isAdmin) {
            $query->where(function ($q) use ($user) {
                $q->where('map_user_team.USER_ID', Auth::id());
            });
        }

        return $query->get()->toArray();
    }   
}