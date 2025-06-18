<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubCategoryController extends Controller
{
    //
    /**
     * Return all current sub-categories
     */
    public function getSubCategories(Request $request)
    {
        $deptId = $request->departmentId;

        try {
            $data = [];

            $subCategories = DB::table('lkp_sub_category')
            ->join('lkp_category', 'lkp_sub_category.CATEGORY_ID', '=', 'lkp_category.CATEGORY_ID')
            ->select('lkp_sub_category.*', 'lkp_category.DISPLAY_NAME as CATEGORY_NAME')
            ->where('lkp_sub_category.DEPARTMENT_ID', $deptId)
            ->where('lkp_sub_category.DISPLAY_NAME', 'like', '%' . request('searchTerm') . '%')
            ->where('lkp_sub_category.CATEGORY_ID', request('categoryId'))

            // ->when(request('categoryId'), fn ($query) => $query->where('lkp_sub_category.CATEGORY_ID', request('categoryId')))
            
            ->orderBy('lkp_sub_category.DISPLAY_NAME','ASC') 
            ->distinct('lkp_sub_category.DISPLAY_NAME')
            ->get();

            foreach ($subCategories as $key => $value) {
                $data[$key] = [
                    'subCategoryId' => optional($value)->SUB_CATEGORY_ID,
                    'categoryId' => optional($value)->CATEGORY_ID,
                    'categoryName' => optional($value)->CATEGORY_NAME,
                    'subCategoryName' => optional($value)->DISPLAY_NAME,
                    'sla' => optional($value)->SLA,
                    'isActive' => optional($value)->ACTIVE_FLAG
                ];
            }

            $this->apiResponse['successCode'] = 1;
            $this->apiResponse['message'] = '';
            $this->apiResponse['data'] = $data;

            return response()->json($this->apiResponse);
        } catch (\Exception $e) {
            //throw $th;
            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error getting departments ' ;
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }

    public function getSubCategoryList(Request $request)
    {
        $deptId = $request->departmentId;

        try {
            $data = [];

            $subCategories = DB::table('lkp_sub_category')
            ->join('lkp_category', 'lkp_sub_category.CATEGORY_ID', '=', 'lkp_category.CATEGORY_ID')
            ->select('lkp_sub_category.*', 'lkp_category.DISPLAY_NAME as CATEGORY_NAME')
            ->where('lkp_sub_category.DEPARTMENT_ID', $deptId)
            ->where('lkp_sub_category.DISPLAY_NAME', 'like', '%' . request('searchTerm') . '%')
            
            ->when(request('categoryId'), fn ($query) => $query->where('lkp_sub_category.CATEGORY_ID', request('categoryId')))

            ->orderBy('lkp_sub_category.DISPLAY_NAME','ASC') 
            ->distinct('lkp_sub_category.DISPLAY_NAME')
            ->get();

            foreach ($subCategories as $key => $value) {
                $data[$key] = [
                    'subCategoryId' => optional($value)->SUB_CATEGORY_ID,
                    'categoryId' => optional($value)->CATEGORY_ID,
                    'categoryName' => optional($value)->CATEGORY_NAME,
                    'subCategoryName' => optional($value)->DISPLAY_NAME,
                    'sla' => optional($value)->SLA,
                    'isActive' => optional($value)->ACTIVE_FLAG
                ];
            }

            $this->apiResponse['successCode'] = 1;
            $this->apiResponse['message'] = '';
            $this->apiResponse['data'] = $data;

            return response()->json($this->apiResponse);
        } catch (\Exception $e) {
            
            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error getting departments ' ;
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }
    public function getSubCategory(Request $request)
    {
        $deptId = $request->departmentId;

        try {
            $data = [];

            $value = SubCategory::find($request->subCategoryId);

            $data[] = [
                'subCategoryId'   => optional($value)->SUB_CATEGORY_ID,
                'categoryId'      => optional($value)->CATEGORY_ID,
                'subCategoryName' => optional($value)->DISPLAY_NAME,
                'sla'             => optional($value)->SLA,
                'isActive'        => optional($value)->ACTIVE_FLAG
            ];

            $this->apiResponse['successCode'] = 1;
            $this->apiResponse['message'] = '';
            $this->apiResponse['data'] = $data;

            return response()->json($this->apiResponse);
        } catch (\Exception $e) {
            //throw $th;
            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error getting departments ' ;
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }

    /**
     * Add a new Sub-Category
     */
    public function addSubCategory(Request $request)
    {
        try {
            DB::beginTransaction();

            // Add a new Sub-Category
            $subCategory = new SubCategory();
            $subCategory->DISPLAY_NAME = $request->subCategoryName;
            $subCategory->CATEGORY_ID = $request->categoryId;
            $subCategory->DEPARTMENT_ID = $request->departmentId;
            $subCategory->SLA           = $request->sla;
            $subCategory->CREATED_BY = $request->userId;
            $subCategory->save();

            DB::commit();

            $this->apiResponse['successCode'] = 1;
            $this->apiResponse['message'] = 'Sub-category added successfully';

            return response()->json($this->apiResponse);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Could not add sub-category ' ;

            return response()->json($this->apiResponse);
        }
    }

    /**
     * Get Sub-Category Details
     */
    public function getDetails(Request $request)
    {
        $subCategoryId = $request->subCategoryId;
        $subCategory = DB::table('lkp_sub_category')->join('lkp_category', 'lkp_sub_category.CATEGORY_ID', '=', 'lkp_category.CATEGORY_ID')
            ->select('lkp_sub_category.*', 'lkp_category.DISPLAY_NAME as CATEGORY_NAME')
            ->where('SUB_CATEGORY_ID', $subCategoryId)
            ->first();

        $data = [];
        $data['subCategoryId'] = $subCategory->SUB_CATEGORY_ID;
        $data['subCategoryName'] = $subCategory->DISPLAY_NAME;
        $data['categoryName'] = $subCategory->CATEGORY_NAME;
        $data['categoryId'] = $subCategory->CATEGORY_ID;
        $data['sla'] = $subCategory->SLA;
        return response()->json($data);
    }

    /**
     * Update Sub-Category Details
     */
    public function updateSubCategory(Request $request)
    {
        $subCategoryId = $request->subCategoryId;

        try {
            DB::beginTransaction();

            // Find and Update SubCategory
            $category = SubCategory::find($subCategoryId);
            $category->DISPLAY_NAME = $request->name;
            $category->CATEGORY_ID = $request->category;
            $category->SLA        =  $request->sla;
            $category->ACTIVE_FLAG = $request->status;
            $category->MODIFIED_BY = $request->userId;
            $category->save();

            DB::commit();

            $this->apiResponse['successCode'] = 1;
            $this->apiResponse['message'] = 'Sub-Category updated successfully';

            return response()->json($this->apiResponse);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Could not update sub-category' ;

            return response()->json($this->apiResponse);
        }
    }

    /**
     * Activate or Deactivate Sub-Category
     */
    public function updateSubCategoryActivation(Request $request)
    {
        $subCategoryId = $request->subCategoryId;
        try {
            DB::beginTransaction();

            $subCategory = SubCategory::find($subCategoryId);
            if ($subCategory->ACTIVE_FLAG == 'Y')
                $subCategory->ACTIVE_FLAG = 'N';
            else
                $subCategory->ACTIVE_FLAG = 'Y';

            $subCategory->MODIFIED_BY = $request->userId;
            $subCategory->save();

            DB::commit();

            $this->apiResponse['successCode'] = 1;
            $this->apiResponse['message'] = 'Succesfully updated';

            return response()->json($this->apiResponse);
        } catch (\Exception $e) {
            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message'] = 'Error updating' ;

            return response()->json($this->apiResponse);
        }
    }
}