<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\PredefinedTasks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    //
    /**
     * Return List of all current categories
     */
    public function getCategories(Request $request)
    {
        $deptId = $request->departmentId;
        
        try {
            $data = [];

            $categories = Category::where('DEPARTMENT_ID', $deptId)
                                    ->where('DISPLAY_NAME', 'like', '%' . request('search') . '%')
                                    ->orderBy('DISPLAY_NAME','ASC')
                                    ->get();
          
            foreach ($categories as $key => $value) {
                $data[$key] = [
                    'categoryId' => isset($value['CATEGORY_ID']) ? $value['CATEGORY_ID'] : null,
                    'categoryName' => isset($value['DISPLAY_NAME']) ? $value['DISPLAY_NAME'] : null,
                    'isActive' => isset($value['ACTIVE_FLAG']) ? $value['ACTIVE_FLAG'] : null,
                ];
            }
            
            $this->apiResponse['successCode'] = 1;
            $this->apiResponse['message'] = 'Success';
            $this->apiResponse['data'] = $data;

            return response()->json($this->apiResponse);
        } catch (\Exception $e) {
            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = "Error: ";
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }

    public function getCategory(Request $request)
    {        
        try {
            $data = [];

            $value = Category::find($request->categoryId);

            $data[] = [
                    'categoryId' => isset($value['CATEGORY_ID']) ? $value['CATEGORY_ID'] : null,
                    'categoryName' => isset($value['DISPLAY_NAME']) ? $value['DISPLAY_NAME'] : null,
                    'isActive' => isset($value['ACTIVE_FLAG']) ? $value['ACTIVE_FLAG'] : null,
                ];
        
            $this->apiResponse['successCode'] = 1;
            $this->apiResponse['message'] = 'Success';
            $this->apiResponse['data'] = $data;

            return response()->json($this->apiResponse);
        } catch (\Exception $e) {
            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = "Error ";
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }

    /**
     * Add a new Category
     */
    public function addCategory(Request $request)
    {
        try {
            DB::beginTransaction();

            // Create a new Category
            $category = new Category();
            $category->DISPLAY_NAME = $request->categoryName;
            $category->DEPARTMENT_ID = $request->departmentId;
            $category->CREATED_BY = $request->userId;
            $category->save();

            DB::commit();

            $this->apiResponse['successCode'] = 1;
            $this->apiResponse['message'] = 'Category added successfully';

            return response()->json($this->apiResponse);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Could not add category ' ;

            return response()->json($this->apiResponse);
        }
    }

    /**
     * Get Category Details
     */
    public function getDetails(Request $request)
    {
        $categoryId = $request->categoryId;
        $category = Category::find($categoryId);
        $data = [];
        $data['categoryId'] = $category['CATEGORY_ID'];
        $data['categoryName'] = $category['DISPLAY_NAME'];
        return response()->json($data);
    }

    /**
     * Edit Category
     */
    public function updateCategory(Request $request)
    {
        $categoryId = $request->categoryId;
        try {
            DB::beginTransaction();

            // Find and Update Category
            $category = Category::find($categoryId);
            $category->DISPLAY_NAME   = $request->categoryName;
            $category->ACTIVE_FLAG    = $request->status;
            $category->MODIFIED_BY    = $request->userId;
            $category->save();

            DB::commit();

            $this->apiResponse['successCode'] = 1;
            $this->apiResponse['message'] = 'Category updated successfully';

            return response()->json($this->apiResponse);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Could not update category ';

            return response()->json($this->apiResponse);
        }
    }

    /**
     * Deactivate or Activate Category
    */
    
    public function updateCategoryActivation(Request $request)
    {
        $categoryId = $request->categoryId;
        try {
            DB::beginTransaction();

            // Find Category by ID
            $category = Category::find($categoryId);
            if ($category->ACTIVE_FLAG == 'Y'){
                $category->ACTIVE_FLAG = 'N';
            }                
            else{
                $category->ACTIVE_FLAG = 'Y';
            }
                
            $category->MODIFIED_BY = $request->userId;
            $category->save();

            DB::commit();

            $this->apiResponse['successCode'] = 1;
            $this->apiResponse['message'] = 'Updated successfully';

            return response()->json($this->apiResponse);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Could not update category ';

            return response()->json($this->apiResponse);
        }
    }

    public function getTemplateData(Request $request)
    {        
        $deptId = $request->departmentId;
        try {

            $data = [];

            $templates = DB::table('mstr_templates')
                ->select('mstr_templates.*')
                ->where('mstr_templates.DEPARTMENT_ID', $deptId)
                ->orderBy('TEMPLATE_NAME','asc')
                ->get();

            foreach ($templates as $value) {
                $data[] = [
                    'templateId'    => optional($value)->TEMPLATE_ID,
                    'templateName'  => optional($value)->TEMPLATE_NAME,
                    'isActive'      => optional($value)->IS_ACTIVE
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

    public function storePredefinedTasks(Request $request)
    {
        try {
            $template = PredefinedTasks::create([
                            'DEPARTMENT_ID' => $request->departmentId,
                            'TEMPLATE_NAME' => $request->templateName,
                            'CREATED_BY' => $request->userId
                        ]);

            $template->save();

            $tasks = json_decode($request->tasks, true);

            // Insert tasks into the database
            foreach ($tasks as $task) {
                DB::table('mstr_task_details')->insert([
                    'TEMPLATE_ID' => $template->TEMPLATE_ID,
                    'TASK_NAME' => $task['TASK_NAME'], // Extract TASK_NAME
                    'IS_ACTIVE' => 'Y',
                    'CREATED_ON' => now(),
                    'CREATED_BY' => $request->userId,
                ]);
            }

            if($template){
                $this->apiResponse['successCode']  = 1;
                $this->apiResponse['message']      = 'Successfully Created';
                $this->apiResponse['data']         = $template;

                return response()->json($this->apiResponse);
            }
            else{
                $this->apiResponse['successCode']  = 0;
                $this->apiResponse['message']      = 'Failed to Create Template';
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

    public function getTemplates(Request $request)
    {
        try {
            $data = [];

            $templateDet = PredefinedTasks::where(['TEMPLATE_ID' => $request->templateId,
                                            'DEPARTMENT_ID' => $request->departmentId])
                                        ->first();            

            $tasks = DB::table('mstr_task_details')
                    ->where('TEMPLATE_ID', $request->templateId)
                    ->select('TASK_NAME', 'IS_ACTIVE', 'TASK_ID') // Include TASK_ID
                    ->orderBy('TASK_NAME','asc')
                    ->get();

            $data[] = [
                    'templateId' => isset($templateDet['TEMPLATE_ID']) ? $templateDet['TEMPLATE_ID'] : null,
                    'templateName' => isset($templateDet['TEMPLATE_NAME']) ? $templateDet['TEMPLATE_NAME'] : null,
                    'isActive' => isset($templateDet['IS_ACTIVE']) ? $templateDet['IS_ACTIVE'] : null,
                    'tasks' => $tasks
                ];
        
            $this->apiResponse['successCode'] = 1;
            $this->apiResponse['message'] = 'Success';
            $this->apiResponse['data'] = $data;

            return response()->json($this->apiResponse);
        } catch (\Exception $e) {

            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = "Error " ;
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }

    public function updateTemplate(Request $request)
    {
        try {
            $template = PredefinedTasks::where(['TEMPLATE_ID' => $request->templateId,
                                                'DEPARTMENT_ID' => $request->departmentId])
                                            ->first();

            $template->TEMPLATE_NAME = $request->templateName;
            $template->MODIFIED_BY = $request->userId;
            $template->save();

            $tasks = json_decode($request->tasks, true);

            // Insert tasks into the database
            foreach ($tasks as $task) {
                $existingTask = DB::table('mstr_task_details')
                            ->where('TEMPLATE_ID', $request->templateId)
                            ->where('TASK_ID', $task['TASK_ID'])
                            ->first();

                if (!$existingTask) {
                    // Insert the new task
                    DB::table('mstr_task_details')->insert([
                        'TEMPLATE_ID' => $template->TEMPLATE_ID,
                        'TASK_NAME' => $task['TASK_NAME'],
                        'IS_ACTIVE' => 'Y',
                        'CREATED_ON' => now(),
                        'CREATED_BY' => $request->userId,
                    ]);
                }
                else{
                    DB::table('mstr_task_details')
                        ->where('TASK_ID', $task['TASK_ID'])
                        ->where('TEMPLATE_ID', $request->templateId)
                        ->update([
                            'TASK_NAME' => $task['TASK_NAME'],
                            'MODIFIED_ON' => now(),
                            'MODIFIED_BY' => $request->userId,
                        ]);
                }
            }

            if($template){
                $this->apiResponse['successCode']  = 1;
                $this->apiResponse['message']      = 'Successfully Created';
                $this->apiResponse['data']         = $template;

                return response()->json($this->apiResponse);
            }
            else{
                $this->apiResponse['successCode']  = 0;
                $this->apiResponse['message']      = 'Failed to Create Template';
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
}