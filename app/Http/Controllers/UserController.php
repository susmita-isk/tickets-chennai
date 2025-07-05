<?php

namespace App\Http\Controllers;

use DataTables;
use App\Models\User;
use App\Models\HrApi;
use App\Models\TicketsApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    //
    /**
     * Return the Users (Technicians) page
     */
    public function index(Request $request)
    {
        $api = new TicketsApi;
        
        $response = $api->getTeams();
        // Retrieve all users initially
        $users = User::join('map_user_department', 'mstr_users.USER_ID', '=', 'map_user_department.USER_ID')
            ->where('DEPARTMENT_ID', session('code'))

            ->when(request('name'),        fn ($query) => $query->where('mstr_users.USER_NAME', 'like', '%' . request('name') . '%'))
            ->when(request('mobile'),      fn ($query) => $query->where('mstr_users.MOBILE', request('mobile')))
            ->when(request('employeeId'),  fn ($query) => $query->where('mstr_users.EMPLOYEE_ID', request('employeeId')))
            ->when(request('role'),        fn ($query) => $query->where('map_user_department.ROLE', request('role')))
           
            // ->orderByRaw("CASE WHEN ACTIVE_FLAG = 'Y' THEN 1 ELSE 0 END")
            ->select('mstr_users.*', 'map_user_department.ROLE')
            ->orderBy('ACTIVATED_ON','desc')
            ->get();

        if ($request->ajax()) {
            return Datatables::of($users)->addIndexColumn()
                ->addColumn('action', function ($row) {
                    // Action buttons based on user's ACTIVE_FLAG
                    $btn = '<div class="table-action-btns-container">
                        <button class="btn tickets-action-btn-transparent" onClick="statusChange(' . $row['USER_ID'] . ',\'' . $row['ACTIVE_FLAG'] . '\')" title="Deactivate" data-toggle="modal">
                            <img src="' . asset('public/img/icons/subtract.png') . '" alt="" width="20" height="auto" style="filter: saturate(100%) brightness(70%) hue-rotate(0deg) grayscale(' . ($row['ACTIVE_FLAG'] == 'Y' ? '0' : '100') . '%);">
                        </button>
                        <button class="btn tickets-action-btn-transparent" onClick="edit(' . $row['USER_ID'] . ',\'' . $row['USER_NAME'] . '\',\'' . $row['MOBILE_NUMBER'] . '\',\'' . $row['LOGIN_ID'] . '\',\'' . $row['EMAIL'] . '\')" title="Edit" data-toggle="modal">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button class="btn tickets-action-btn-transparent" onClick="roleChange(' . $row['USER_ID'] . ',\'' . $row['USER_NAME'] . '\',\'' . $row['ROLE'] . '\')" title="Role Change" data-toggle="modal">
                            <i class="fa fa-sync" aria-hidden="true"></i>
                        </button>
                    </div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $roles = DB::table('mstr_role')
                    ->select('ROLE_NAME','ROLE_ID')
                    ->get();

        return view('technicians',compact('roles'))
               ->withTeams($response['data']);
    }

    /**
     * Populate User Select Field when names are typed into the search box,
     *  while selecting new user to add
     */
    public function getAllUsers(Request $request)
    {
        $hrApi = new HrApi;
        $data = [];
        $data['results'] = [];
        $userList = $hrApi->getEmployees($request);

        // To set data as needed for select2 plugin's AJAX option for dropdown search
        if ($userList['successCode'] == 1) {
            $i = 0;
            foreach ($userList['data'] as $emp) {
                # code...
                $data['results'][$i]['id'] = $i + 1;
                $data['results'][$i]['text'] = $emp['employeeName'];
                $data['results'][$i]['hrEmployeeID'] = $emp['hrEmployeeID'];
                $data['emailId'][$i]['emailId'] = $emp['emailId'];
                $data['department'][$i]['department'] = $emp['department'];
                $data['mobileNumber'][$i]['mobileNumber'] = $emp['mobileNumber'];
                $i++;
            }
        }

        return $data;
    }

    /**
     * To display user details after selection
     */
    public function getUserDetails(Request $request)
    {
        $hrApi = new HrApi;
        $hrEmployeeId = $request->emp_id;
        $userDetails = $hrApi->getEmployeeDetails($hrEmployeeId);
        return $userDetails;
    }

    /**
     * Add a Technician or Support Desk Exec or Admin User
     */
    public function addUser(Request $request)
    {     
        // print_r($request->all());
        $api = new TicketsApi;
        $data = $api->addUser($request);
        return $data;
    }

    public function getTechnicians(Request $request)
    {
        $teamId = $request->input('teamId');
        
        $users = User::join('map_user_department', 'mstr_users.USER_ID', '=', 'map_user_department.USER_ID')
                   ->join('team_members', 'mstr_users.USER_ID', '=', 'team_members.TECHNICIAN')
                   ->where('DEPARTMENT_ID',session('code'))
                   ->when($teamId, function ($query, $teamId) {
                        return $query->where('team_members.TEAM_ID', $teamId)
                            ->where('team_members.IS_ACTIVE', 'Y');
                    })
                   ->where('ROLE','Technician')                   
                   ->where('ACTIVE_FLAG','Y')
                   ->orderBy('mstr_users.USER_NAME', 'asc')
                   ->select('mstr_users.EMPLOYEE_ID','mstr_users.USER_NAME','mstr_users.USER_ID') 
                   ->get();     
                
        return response()->json($users);        
    }
    public function getTechniciansForTeam(Request $request)
    {
        
        $users = User::where('ACTIVE_FLAG','Y')
                   ->select('mstr_users.EMPLOYEE_ID','mstr_users.USER_NAME','mstr_users.USER_ID') 
                   ->get();     
                
        return response()->json($users);        
    }
    public function changeStatus(Request $request)
    {
        $status = $request->status;

        $user = User::find($request->userId);

        if($status == 'Y')
        {
            $user->ACTIVE_FLAG  = 'Y';
            $user->ACTIVATED_ON = now();
            $user->ACTIVATED_BY = Auth::id();
        }
        else {
            
            $user->ACTIVE_FLAG    = 'N';
            $user->INACTIVATED_ON =  now();
            $user->INACTIVATED_BY =  Auth::id(); 
        }

        $user->save();

        return response()->json(['message' => 'Success']);        

    }
    public function editUser(Request $request)
    {
        $user = User::find($request->technicianId);

        $user->USER_NAME      = $request->technicianName;
        $user->EMAIL          = $request->technicianEmail;
        $user->MOBILE_NUMBER  = $request->technicianMobile;
        $user->LOGIN_ID       = $request->technicianLogin; 
        $user->PASSWORD      = isset($request->technicianPassword) ? Hash::make($request->technicianPassword) : '';

        $user->save();

        return response()->json(['message' => 'Success']);        
    }

    public function editUserRole(Request $request)
    {
        $map_user_department = DB::table('map_user_department')
                                ->where('USER_ID', $request->technicianId)
                                ->where('DEPARTMENT_ID', session('code'))
                                ->first();

        $ctrl_user_role = DB::table('ctrl_user_role')
                            ->where('LOGIN_ID', $request->technicianId)
                            ->first();

        if ($map_user_department && $ctrl_user_role) {
            DB::table('ctrl_user_role')
                ->where('LOGIN_ID', $request->technicianId)
                ->update(['ROLE_ID' => $request->roleId]);

            DB::table('map_user_department')
                ->where('USER_ID', $request->technicianId)
                ->where('DEPARTMENT_ID', session('code'))
                ->update(['ROLE' => $request->roleName]);

            return response()->json(['message' => 'Role updated successfully']);
        } else {
            return response()->json(['message' => 'User not found'], 404);
        }
    }

    public function  getTeams(Request $request)
    {
        if ($request->ajax()) {

            $teams = DB::table('team')
                        ->where('DEPARTMENT_ID', session('code'))
                        ->orderBy('team.TEAM_NAME', 'ASC')
                        ->select('team.*')
                        ->get();
    
            return Datatables::of($teams)->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<div class="table-actions-container">
                                <button class="btn tickets-action-btn-transparent mr-3" onClick="assign(' . $row->TEAM_ID . ',\'' .addslashes(str_replace(array("\r", "\n"), '', $row->TEAM_NAME)) . '\')" title="Assign">
                                    <img src="' . asset('public/img/icons/assign-ticket.png') . '" alt="" height="20">
                                </button>
                                 <button class="btn tickets-action-btn-transparent" onClick="editTeam(' . $row->TEAM_ID . ',\'' .addslashes(str_replace(array("\r", "\n"), '', $row->TEAM_NAME)) . '\',\'' . $row->IS_ACTIVE . '\',\''. $row->SLA_ON .'\')" title="Edit" data-toggle="modal">
                                    <img src="' . asset('public/img/icons/edit-btn.png') . '" alt="" height="20">
                                </button>
                            </div>';
                    return $btn;
                })                                       
               
                ->rawColumns(['action'])
                ->make(true);
            }
    }
    public function  getTeamTeachnicians(Request $request)
    {
        if ($request->ajax()) {

            $teamId = $request->input('teamId');

            $users = [];

            if($teamId)
            {
                $users = User::join('map_user_department', 'mstr_users.USER_ID', '=', 'map_user_department.USER_ID')
                            ->join('team_members', 'mstr_users.USER_ID', '=', 'team_members.TECHNICIAN')
                            ->where('DEPARTMENT_ID',session('code'))
                            ->when($teamId, function ($query, $teamId) {
                            return $query->where('team_members.TEAM_ID', $teamId);
                            })
                            ->where('ROLE','Technician')
                            ->where('team_members.IS_ACTIVE','Y')
                            ->select('mstr_users.EMPLOYEE_ID','mstr_users.USER_NAME',
                                'team_members.MEMBER_ID','team_members.IS_ELIGIBLE_FOR_NEGATIVE_POINTS',
                                DB::raw("DATE_FORMAT(team_members.ALLOCATED_ON, '%e-%M-%Y') AS ALLOCATED_ON")) 
                            ->get(); 
                
            }
    
            return Datatables::of($users)->addIndexColumn()
            ->addColumn('action', function ($row) {
                $checked = $row->IS_ELIGIBLE_FOR_NEGATIVE_POINTS == 'Y' ? 'checked' : '';

                $btn = '<div class="d-flex justify-content-center table-actions-container">
                            <button class="btn tickets-action-btn-transparent mr-2"  title="Assign">
                                <i class="fa fa-window-close" aria-hidden="true" style="color:green;" onClick="remove(' . $row->MEMBER_ID.')"></i>
                            </button>
                            <button class="btn tickets-action-btn-transparent green-checkbox"  title="Is Eligible for Negative Points">
                                <input type="checkbox" id="memberId" value="' . $row->MEMBER_ID . '"' . $checked . ' onchange="toggleEligibility(this)">                               
                            </button>
                        </div>';
                return $btn;
                })->rawColumns(['action'])->make(true);
        }
    }
    public function assignTechnician(Request $request)
    {                 
        // Get the current active technicians for the team
        $currentActiveTechnicians = DB::table('team_members')
                                        ->where('TEAM_ID', $request->teamId)
                                        ->pluck('TECHNICIAN')
                                        ->toArray();

        // Process each technician in the request
        foreach ($request->technicians as $value) {

            $userTeamExist = DB::table('map_user_team')
                            ->where('USER_ID',$value)
                            ->where('TEAM_ID',$request->teamId)
                            ->first();
                            
            if(!$userTeamExist){
                DB::table('map_user_team')->insert([
                    'USER_ID' => $value,
                    'TEAM_ID' => $request->teamId
                ]);
            }

            // Check if the technician is already active
            if (in_array($value, $currentActiveTechnicians)) {
                // Technician is already active, update the record
                DB::table('team_members')
                    ->updateOrInsert(
                        [
                            'TEAM_ID'      => $request->teamId,
                            'TECHNICIAN'   => $value,
                        ],
                        [
                            'ALLOCATED_ON' => now(),
                            'IS_ACTIVE'    => 'Y'
                        ]
                    );
                
                // Remove the technician from the $currentActiveTechnicians array
                // to track which technicians are still active after processing
                $key = array_search($value, $currentActiveTechnicians);
                unset($currentActiveTechnicians[$key]);

            } else {
                // Technician is new, insert a new record
                DB::table('team_members')->insert([
                    'TEAM_ID'      => $request->teamId,
                    'TECHNICIAN'   => $value,
                    'ALLOCATED_ON' => now(),
                    'IS_ACTIVE'    => 'Y'
                ]);
            }
        }

        // Handle removal of technicians that are no longer in the request
        // foreach ($currentActiveTechnicians as $inactiveTechnician) {
        //     // Deactivate or remove the technician from the team_members table
        //     DB::table('team_members')
        //         ->where('TEAM_ID', $request->teamId)
        //         ->where('TECHNICIAN', $inactiveTechnician)
        //         ->update(['IS_ACTIVE' => 'N']);
        // }

        return response()->json(['message' => 'Success']);     
    }
    public function  removeTechnician(Request $request)
    {
        DB::table('team_members')
        ->where('MEMBER_ID', $request->memberId)
        ->update(['IS_ACTIVE' => 'N']);

      return response()->json(['message' => 'Success']);     

    }

    public function storeTeam(Request $request)
    {
        DB::table('team')->insertGetId([
            'TEAM_NAME'  => $request->name, // Assuming you have a newTeamName field in your request
            'DEPARTMENT_ID' => session('code'),
            'IS_ACTIVE'  => 'Y',
            'CREATED_BY' => Auth::user()->LOGIN_ID,
            'CREATED_ON' => now()
        ]);

        return response()->json(['message' => 'Success']);     
    } 

    public function isTeamMembersEligible(Request $request)
    {
        // Update the eligibility status of the team member
        $updated = DB::table('team_members')
        ->where('MEMBER_ID', $request->memberId)
        ->update(['IS_ELIGIBLE_FOR_NEGATIVE_POINTS' => $request->isEligible]);

        if ($updated) {
            return response()->json(['success' => true, 'message' => 'Eligibility updated successfully.']);
        } else {
            return response()->json(['success' => false, 'message' => 'No changes made or member not found.']);
        }
    }
    public function teamSlaOn(Request $request)
    {
        // Update the SLA ON time for the team
        $updated = DB::table('team')
                    ->where('TEAM_ID', $request->teamId)
                    ->where('DEPARTMENT_ID', session('code'))
                    ->update(['SLA_ON' => $request->sla_on_time,
                              'IS_ACTIVE' => $request->team_is_active
                            ]);

        if ($updated) {
            return response()->json(['success' => true, 'message' => 'Team Data updated successfully.']);
        } else {
            return response()->json(['success' => false, 'message' => 'No changes found.']);
        }
    }
}