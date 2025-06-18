<?php

namespace App\Http\Controllers\API;

use App\Models\ItemType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ItemTypeController extends Controller
{
    //
    /**
     * Return all current Item types
     */
    public function getItemTypes(Request $request)
    {
        $deptId = $request->departmentId;
        $subCategoryId = $request->subcategoryId;

        try {
            $data = [];

            $itemTypes = DB::table('lkp_item_type')
                ->join('lkp_sub_category', 'lkp_item_type.SUB_CATEGORY_ID', '=', 'lkp_sub_category.SUB_CATEGORY_ID')
                ->join('lkp_category', 'lkp_item_type.CATEGORY_ID', '=', 'lkp_category.CATEGORY_ID')
                ->select(
                    'lkp_item_type.*',
                    'lkp_sub_category.DISPLAY_NAME as SUB_CATEGORY_NAME',
                    'lkp_category.DISPLAY_NAME as CATEGORY_NAME'
                )
                ->where('lkp_item_type.DEPARTMENT_ID', $deptId)
                // ->where('lkp_item_type.CATEGORY_ID', request('categoryId'))
                ->where('lkp_item_type.SUB_CATEGORY_ID', $request->subcategoryId)

                ->when(request('search'),       fn ($query) => $query->where('lkp_item_type.DISPLAY_NAME', 'like', '%' . request('search') . '%'))

                // ->when(request('categoryId'),    fn ($query) => $query->where('lkp_item_type.CATEGORY_ID', request('categoryId')))
                // ->when(request('subcategoryId'), fn ($query) => $query->where('lkp_item_type.SUB_CATEGORY_ID', request('subcategoryId')))

                ->orderBy('lkp_item_type.DISPLAY_NAME','ASC') 
                ->distinct('lkp_item_type.CATEGORY_ID')
                ->get();

            foreach ($itemTypes as $key => $value) {
                $data[$key] = [
                    'itemTypeId'      => optional($value)->ITEM_TYPE_ID,
                    'itemTypeName'    => optional($value)->DISPLAY_NAME,
                    'categoryId'      => optional($value)->CATEGORY_ID,
                    'categoryName'    => optional($value)->CATEGORY_NAME,
                    'subCategoryId'   => optional($value)->SUB_CATEGORY_ID,
                    'subCategoryName' => optional($value)->SUB_CATEGORY_NAME,
                    'isActive'        => optional($value)->ACTIVE_FLAG,
                    'isAssetSelection' => optional($value)->ASSET_SELECTION
                ];
            }

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

    public function getItemTypesList(Request $request)
    {
        $deptId = $request->departmentId;

        try {
            $data = [];

            $itemTypes = DB::table('lkp_item_type')
                ->join('lkp_sub_category', 'lkp_item_type.SUB_CATEGORY_ID', '=', 'lkp_sub_category.SUB_CATEGORY_ID')
                ->join('lkp_category', 'lkp_item_type.CATEGORY_ID', '=', 'lkp_category.CATEGORY_ID')
                ->select(
                    'lkp_item_type.*',
                    'lkp_sub_category.DISPLAY_NAME as SUB_CATEGORY_NAME',
                    'lkp_category.DISPLAY_NAME as CATEGORY_NAME'
                )
                ->where('lkp_item_type.DEPARTMENT_ID', $deptId)

                ->when(request('search'),       fn ($query) => $query->where('lkp_item_type.DISPLAY_NAME', 'like', '%' . request('search') . '%'))

                ->when(request('categoryId'),    fn ($query) => $query->where('lkp_item_type.CATEGORY_ID', request('categoryId')))
                ->when(request('subcategoryId'), fn ($query) => $query->where('lkp_item_type.SUB_CATEGORY_ID', request('subcategoryId')))

                ->orderBy('lkp_item_type.DISPLAY_NAME','ASC') 
                ->distinct('lkp_item_type.CATEGORY_ID')
                ->get();

            foreach ($itemTypes as $key => $value) {
                $data[$key] = [
                    'itemTypeId'      => optional($value)->ITEM_TYPE_ID,
                    'itemTypeName'    => optional($value)->DISPLAY_NAME,
                    'categoryId'      => optional($value)->CATEGORY_ID,
                    'categoryName'    => optional($value)->CATEGORY_NAME,
                    'subCategoryId'   => optional($value)->SUB_CATEGORY_ID,
                    'subCategoryName' => optional($value)->SUB_CATEGORY_NAME,
                    'isActive'        => optional($value)->ACTIVE_FLAG,
                    'isAssetSelection' => optional($value)->ASSET_SELECTION
                ];
            }

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

    public function storeItemType(Request $request)
    {
        $deptId = $request->departmentId;
        $subCategoryId = $request->subcategoryId;

        try {
            $data = [];

            $itemType = new ItemType;

            $itemType->DISPLAY_NAME    = $request->name;
            $itemType->CATEGORY_ID     = $request->category;
            $itemType->SUB_CATEGORY_ID = $request->subcategory;
            $itemType->DEPARTMENT_ID   = $request->departmentId;
            $itemType->ACTIVE_FLAG     = 'Y';
            $itemType->CREATED_BY      = $request->userId;
            $itemType->CREATED_ON      = now();

            $itemType->save();

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
    public function getItemType(Request $request)
    {        
        try {
            $data = [];

            $value = ItemType::find($request->itemTypeId);
            
            $data[] = [
                'itemTypeId'      => optional($value)->ITEM_TYPE_ID,
                'itemTypeName'    => optional($value)->DISPLAY_NAME,
                'categoryId'      => optional($value)->CATEGORY_ID,
                'subCategoryId'   => optional($value)->SUB_CATEGORY_ID,
                'isActive'        => optional($value)->ACTIVE_FLAG
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
    public function updateItemType(Request $request)
    {       
        try {
            $data = [];

            $itemType = ItemType::find($request->itemTypeId);

            $itemType->DISPLAY_NAME    = $request->name;
            $itemType->CATEGORY_ID     = $request->category;
            $itemType->SUB_CATEGORY_ID = $request->sub_category;
            $itemType->DEPARTMENT_ID   = $request->departmentId;
            $itemType->ACTIVE_FLAG     = $request->status;
            $itemType->CREATED_BY      = $request->userId;
            $itemType->CREATED_ON      = now();

            $itemType->save();

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
    
}