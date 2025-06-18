<?php

namespace App\Models;

use GuzzleHttp\Client;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use DB;

class HrApi extends Model
{
    use HasFactory;

    private $accessKey;
    private $apiBaseURL;

    public function __construct()
    {
        // $this->accessKey = '729!#kc@nHKRKkbngsppnsg@491';
        // $this->apiBaseURL = 'https://hr.iskconbangalore.net/v1/api';
    }

    /**
     * Get Employee names on Searching in the Select Employee dropdown for adding technician
     */
    public function getEmployees($input_data)
    {
        try{
            $employeeName = $input_data['emp_name'] ?? '';           

            $data = [];
            $query = DB::table('employee')->where('IS_ACTIVE','Y');
            $query->select(
                'EMPLOYEE_ID',
                'EMPLOYEE_NAME',
                'ENTITY_CODE',
                'DEPARTMENT',
                'DESIGNATION',
                'MOBILE_NUMBER',
                'EMAIL_ID',
                'HR_EMPLOYEE_ID',
                'PHOTO',              
                'IS_ACTIVE',
                
            );
            $query->when( $employeeName, function ($q , $employeeName) {
                return $q->where('EMPLOYEE_NAME' ,'like', '%'.$employeeName.'%');
            });

            $empList = $query->orderBy('EMPLOYEE_NAME','asc')->get();

            if (count($empList)>0)
            {
                foreach($empList as $value)
                {
                    $data[]=[
                        'employeeName' => $value->EMPLOYEE_NAME ?? '',
                        'designation' => $value->DESIGNATION ?? '',
                        'emailId' => $value->EMAIL_ID ?? '',
                        'department' => $value->DEPARTMENT ?? '',
                        'department_code' => $value->ENTITY_CODE ?? '',
                        'mobileNumber' => $value->MOBILE_NUMBER ?? '',
                        'isActive' => $value->IS_ACTIVE ?? '',
                        'hrEmployeeID' => $value->HR_EMPLOYEE_ID ?? '',
                        'employeeID' => $value->EMPLOYEE_ID ?? '',
                    ];
                }
                return [
                    'successCode' =>  1,
                    'message' => 'Successful',
                    'data' => $data
                ];
            }
            else
            {
                return [
                    'successCode' => 0,
                    'message' => 'No Results to Display',
                    'data' => []
                ];
            }
        }catch(\Exception $e){

            return [
                'successCode' => 0,
                'message' => "Error Please try again !!",
                'data' => []
            ];
        }
    }

    /**
     * Get Employee Details
     * -- From HR Profile API, may be needed to be changed to Admin API
     */
    public function getEmployeeDetails($hrEmployeeId)
    {
        try{
            $data=[];
            $profileDetails = DB::table('employee')
                                ->select('HR_EMPLOYEE_ID  as hrEmployeeId',
                                            'EMPLOYEE_NAME as employeeName',
                                            'DEPARTMENT as departmentName',
                                            'ENTITY_CODE as department_code',
                                            'MOBILE_NUMBER as mobile',
                                            'EMAIL_ID as emailId',
                                            'IS_ACTIVE as isActive',
                                            'DESIGNATION as designation',
                                            'PHOTO')
                                ->where('HR_EMPLOYEE_ID', $hrEmployeeId)
                                ->first();

            if ($profileDetails) {
                $data[] = [
                    "hrEmployeeId"     => $profileDetails->hrEmployeeId ?? '',
                    "employeeName"     => $profileDetails->employeeName ?? '',
                    "departmentName"   => $profileDetails->departmentName ?? '',
                    "emailId"          => $profileDetails->emailId ?? '',
                    "mobile"           => $profileDetails->mobile ?? '',
                    "isActive"         => $profileDetails->isActive ?? '',
                    "designation"      => $profileDetails->designation ?? '',
                    "department_code"  => $profileDetails->department_code ?? '',
                    "photoURL"         => "http://192.168.3.250/tickets-chennai/public/img/employee-photo/",
                    "photoName"        => $profileDetails->PHOTO ?? 'user.jpg',
                ];

                return [
                    'successCode' => 1,
                    'message' => 'Successful',
                    'data' => $data
                ];
            }
            else
            {
                return [
                    'successCode' => 0,
                    'message' => 'No Results to Display',
                    'data' => []
                ];
            }             
        }
        catch(\Exception $e){                
            return [
                'successCode' => 0,
                'message' => 'Error Please try again !!',
                'data' => []
            ];
        }
    }

    public function getDepartmentDetails()
    {
        try{
            $departments = DB::table('department_lists')
                            ->select('DEPT_CODE as deptCode', 'DEPT_NAME as deptName')
                            ->orderBy('DEPT_NAME', 'asc')
                            ->get();
            $data = [];
            foreach($departments as $department) {
                $data[] = [
                    'deptCode' => $department->deptCode ?? '',
                    'deptName' => $department->deptName ?? ''
                ];
            }
            return [
                'successCode' => 1,
                'message' => 'Successful',
                'data' => $data
            ];
        }
        catch(\Exception $e){
            return [
                'successCode' => 0,
                'message' => 'Error Please try again !!',
                'data' => []
            ];
        }
    }
    public function getTrust()
    {
        $response = Http::accept('application/json')->post($this->apiBaseURL.'/admin/get-trust-list',['accessKey' => $this->accessKey]);

        return $response->collect();

    }
    
}