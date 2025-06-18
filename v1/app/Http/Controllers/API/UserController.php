<?php

namespace App\Http\Controllers\API;

use App\Models\Team;
use App\Models\TaskType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    //
    /**
     * Create new Technician / Support Desk Exec. / Admin User
     */
    public function addNewUser(Request $request)
    {
        $hr_employee_id = $request->employeeID;

        // Check if user currently exists in mstr_users table: if no, create user
        // if yes, add details to map_user_department
        $user = DB::select('select * from mstr_users where EMPLOYEE_ID = ?', [$hr_employee_id]);

        if (count($user)) {
            // Employee ID found in mstr_users, just add mapping
            $user_id = $user[0]->USER_ID;


            DB::table('map_user_department')->insert([
                'USER_ID' => $user_id,
                'DEPARTMENT_ID' => $request->departmentID,
                'ROLE' => $request->role
            ]);

            $this->apiResponse['successCode'] = 1;
            $this->apiResponse['message'] = 'User added successfully';

            return response()->json($this->apiResponse);
        } else {
            // Create new user and then add mapping
            try {
                DB::beginTransaction();

                // Create new user
                $user = new User();
                $user->USER_NAME = $request->userName;
                $user->EMAIL = $request->email;
                $user->MOBILE_NUMBER = $request->mobileNumber;
                $user->EMPLOYEE_ID = $hr_employee_id;
                $user->LOGIN_ID = $request->loginID ?? "";
                $user->PASSWORD = isset($request->password) ? Hash::make($request->password) : '';
                $user->ACTIVATED_ON = date('Y-m-d H:i:s');
                $user->ACTIVATED_BY = $request->loggedinUserId;
                $user->save();

                $new_user_id = $user->USER_ID;

                // Add the user-department mapping
                DB::table('map_user_department')->insert([
                    'USER_ID' => $new_user_id,
                    'DEPARTMENT_ID' => $request->departmentID,
                    'ROLE' => $request->role
                ]);
              
                DB::commit();

                $this->apiResponse['successCode'] = 1;
                $this->apiResponse['message'] = 'User added successfully';
                $this->apiResponse['data'] = $user;

                return response()->json($this->apiResponse);
            } catch (\Exception $e) {
                //throw $th;

                DB::rollBack();

                $this->apiResponse['successCode'] = 0;
                $this->apiResponse['message'] = 'Could not add user';
                //  $this->apiResponse['message'] = $e->getMessage();

                return response()->json($this->apiResponse);
            }
        }
    }

    public function getUsers(Request $request)
    {
        try {
            $userData = [];
            $users = User::where('INACTIVATED_ON', null)->get();
            foreach ($users as $key => $value) {
                $userData[$key] = [
                    'userId' => optional($value)->USER_ID,
                    'loginId' => optional($value)->LOGIN_ID,
                    'userName' => optional($value)->USER_NAME,
                    'email' => optional($value)->EMAIL,
                    'mobileNumber' => optional($value)->MOBILE_NUMBER,
                    'employeeID' => optional($value)->EMPLOYEE_ID,
                    'joiningDate' => date('d-M-Y', strtotime(optional($value)->ACTIVATED_ON)),
                    'relievingDate' => date('d-M-Y', strtotime(optional($value)->INACTIVATED_ON))
                ];

                $this->apiResponse['successCode']  = 1;
                $this->apiResponse['message']      = 'Successful';
                $this->apiResponse['data']         = $data;

                return response()->json($this->apiResponse);
            }
        } catch (\Exception $e) {
            $this->apiResponse['successCode']  = 0;
            $this->apiResponse['message']      = 'Failed';
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }

    public function getTeams(Request $request)
    {
        try {
            $data = [];

            $teams = Team::where(['IS_ACTIVE' =>'Y',
                                    'DEPARTMENT_ID' => $request->departmentId])
                            ->orderBy('team.TEAM_NAME','asc')
                            ->get();

            foreach ($teams as $key => $value) {

                $data[$key] = [

                    'teamId'   => optional($value)->TEAM_ID,
                    'teamName' => optional($value)->TEAM_NAME,
                ];                
            }

            $this->apiResponse['successCode']  = 1;
            $this->apiResponse['message']      = 'Successful';
            $this->apiResponse['data']         = $data;

            return response()->json($this->apiResponse);
           
        } catch (\Exception $e) {
            $this->apiResponse['successCode']  = 0;
            $this->apiResponse['message']      = "Error please try again !";
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }
    public function getTicketTypes(Request $request)
    {
        try {
            $data = [];

            $ticketTypes = TaskType::where('TEAM_ID',$request->teamId)->get();

            foreach ($ticketTypes as $key => $value) {

                $data[$key] = [

                    'ticketTypeId'   => optional($value)->TASK_TYPE_ID,
                    'ticketTypeName' => optional($value)->DISPLAY_NAME,
                ];                
            }

            $this->apiResponse['successCode']  = 1;
            $this->apiResponse['message']      = 'Successful';
            $this->apiResponse['data']         = $data;

            return response()->json($this->apiResponse);
           
        } catch (\Exception $e) {
            $this->apiResponse['successCode']  = 0;
            $this->apiResponse['message']      = "Error please try again !";
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }

    public function getUserTeams(Request $request)
    {
        try {
            $data = [];
            $user = DB::table('mstr_users')
                        ->where('EMPLOYEE_ID',$request->empId)
                        ->first();// Get the logged-in user
            
            $teams = DB::table('team')
                        ->where('DEPARTMENT_ID',$request->departmentId)
                        ->leftJoin('map_user_team', 'map_user_team.TEAM_ID', '=', 'team.TEAM_ID') 
                        ->where('map_user_team.USER_ID',  $user->USER_ID)
                        ->select('team.TEAM_ID', 'team.TEAM_NAME')
                        ->orderBy('team.TEAM_NAME','asc')
                        ->get();

            foreach ($teams as $value) {
                $data[] = [

                    'teamId'   => optional($value)->TEAM_ID,
                    'teamName' => optional($value)->TEAM_NAME,
                ];                
            }

            $this->apiResponse['successCode']  = 1;
            $this->apiResponse['message']      = 'Successful';
            $this->apiResponse['data']         = $data;

            return response()->json($this->apiResponse);
           
        } catch (\Exception $e) {
            $this->apiResponse['successCode']  = 0;
            $this->apiResponse['message']      = "Error please try again !";
            // $this->apiResponse['message']      = $e->getMessage();
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }
}