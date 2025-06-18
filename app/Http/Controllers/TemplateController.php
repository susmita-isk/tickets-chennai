<?php

namespace App\Http\Controllers;

use DataTables;
use App\Http\Controllers\Controller;
use App\Models\TicketMastersApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\TicketsApi;
use App\Models\Template;
use App\Models\TemplateTask;
use Session;

class TemplateController extends Controller
{
    public function ticketTemplateIndex(Request $request)
    {
        $departmentName = DB::table('department_details')->where('DEPARTMENT_ID',session('code'))->first()->DEPARTMENT_NAME;

        $api = new TicketMastersApi;

        $request->merge(['departmentId' => session('code')]);
        
        $result = $api->getTemplateData($request->all());

        return view('ticket-template',compact('departmentName'));
    }

    public function getTemplateData(Request $request)
    {
        $api = new TicketMastersApi;

        $request->merge(['departmentId' => session('code')]);
        
        $result = $api->getTemplateData($request->all());
    
        if($request->ajax()) {

            return Datatables::of($result['data'])->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<button class="btn tickets-action-btn-transparent" onclick="getTemplates('. $row['templateId'] .')" title="Edit">
                                <img src="'.asset('public/img/icons/edit-btn.png').'" alt="Edit" height="20">
                            </button>';

                    return $btn;
                })
                ->addColumn('status', function ($row) {
                    $status='<label class="ml-2 switch">
                                <input type="checkbox" name="template-status" class="template-status" 
                                onclick="getTemplateStatus(\'' . $row['isActive'] . '\')"     
                                data-id="'. $row['templateId'] .'" ' . ($row['isActive'] === 'Y' ? 'checked' : '') . '>
                                <span class="slider round"></span>
                            </label>';               

                    return $status;
                })
                
                ->rawColumns(['action','status'])
                ->make(true);
            } 
    }

    public function storeSubcategoryTask(Request $request)
    {
        $api = new TicketsApi;

        $request->merge(['departmentId' => session('code')]);

        $result = $api->storeSubcategoryTask($request->all());

        return response()->json($result['data']);
    }

    public function getTemplates(Request $request)
    {
        $data = [];
        $templateDet = DB::table('mstr_templates')
                ->where([
                        'TEMPLATE_ID' => $request->templateId,
                        'DEPARTMENT_ID' => session('code'),
                    ])
                ->first();
        
        
        // Fetch associated tasks
       $tasks = DB::table('mstr_task_details')
                    ->where('TEMPLATE_ID', $request->templateId)
                    ->select('TASK_NAME', 'IS_ACTIVE', 'TASK_ID')// Select both TASK_ID and TASK_NAME
                    // ->orderBy('TASK_NAME','asc')
                    ->get()
                    ->toArray();

        $data[] = [
                'templateId' => $templateDet->TEMPLATE_ID,
                'templateName' => $templateDet->TEMPLATE_NAME,
                'isActive' => $templateDet->IS_ACTIVE,
                'tasks' => $tasks,
            ];

        return response()->json($data);     
    }

    public function updateTemplate(Request $request)
    {
        $api = new TicketsApi;

        $request->merge(['departmentId' => session('code')]);

        $result = $api->updateTemplate($request->all());

        return response()->json($result['data']);
    }

    public function templateStatus(Request $request){  

        $statusDet=Template::where('TEMPLATE_ID',$request->templateId)
                            ->where('DEPARTMENT_ID', session('code'))
                            ->first();
        $templateStatus = $request->templateStatus;
        if($statusDet) {           
            
            if($templateStatus == 'Y')
            {
                $statusDet->IS_ACTIVE = 'N';
                $msg='Status Disabled';
            }
            else{
                $statusDet->IS_ACTIVE = 'Y';
                $msg='Status Enabled';
            }
            $statusDet->save();
            
            $arr = [
            'error' => false,
            'msg' => $msg,
            ];
            return json_encode($arr);
        } 
        else {
            $arr = [
                'error' => true,
                'msg' => 'Error Updating Status. Please try again'
            ];
            return json_encode($arr);
        }
    }

    public function taskStatus(Request $request){  

        $statusDet= TemplateTask::where('TASK_ID',$request->taskId)
                            ->first();
        $taskStatus = $request->taskStatus;
        if($statusDet) {           
            
            if($taskStatus == 'Y')
            {
                $statusDet->IS_ACTIVE = 'N';
                $msg='Task Status Disabled';
            }
            else{
                $statusDet->IS_ACTIVE = 'Y';
                $msg='Task Status Enabled';
            }
            $statusDet->save();
            
            $arr = [
            'error' => false,
            'status' => $statusDet->IS_ACTIVE,
            'msg' => $msg,
            ];
            return json_encode($arr);
        } 
        else {
            $arr = [
                'error' => true,
                'msg' => 'Error Updating Status. Please try again'
            ];
            return json_encode($arr);
        }
    }

    public function getTicketTasks(Request $request)
    {
        $templateId = $request->input('templateId');
        
        $tasks = DB::table('mstr_task_details')                        
                        ->where(['TEMPLATE_ID'=>$templateId,'IS_ACTIVE' => 'Y'])
                        ->select('TASK_ID','TASK_NAME')
                        // ->orderBy('TASK_NAME','asc')
                        ->get();   
                
        return response()->json($tasks);  
    }

}