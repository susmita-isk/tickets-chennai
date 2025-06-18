<?php

namespace App\Http\Controllers\API;

use App\Models\Department;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DepartmentController extends Controller
{
    public function getDepartments(Request $request)
    {
        try {
            $data = [];

            $departments = Department::query()
                           ->join('map_user_department', 'department_details.DEPARTMENT_ID', '=', 'map_user_department.DEPARTMENT_ID')
                           ->where('department_details.IS_ACTIVE', 'Y')
                           ->where('map_user_department.USER_ID',request('userId'))
                           ->select('department_details.*')
                           ->get();

            foreach ($departments as $key => $value) {
                $data[$key] = [
                    'departmentId'     => optional($value)->DEPARTMENT_ID,
                    'departmentCode'   => optional($value)->DEPARTMENT_CODE,
                    'name'             => optional($value)->DEPARTMENT_NAME,
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
}
