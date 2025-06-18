<?php

namespace App\Http\Controllers\API;

use App\Models\SubItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class SubItemController extends Controller
{
    public function getSubItems(Request $request)
    {
        $deptId = $request->departmentId;
        $itemTypeId = $request->itemTypeId;

        try {

            $data = [];

            $items = DB::table('lkp_item')
                ->join('lkp_sub_category', 'lkp_item.SUB_CATEGORY_ID', '=', 'lkp_sub_category.SUB_CATEGORY_ID')
                ->join('lkp_category', 'lkp_item.CATEGORY_ID', '=', 'lkp_category.CATEGORY_ID')
                ->join('lkp_item_type', 'lkp_item.ITEM_TYPE_ID', '=', 'lkp_item_type.ITEM_TYPE_ID')
                ->select(
                    'lkp_item.*',
                    'lkp_sub_category.DISPLAY_NAME as SUB_CATEGORY_NAME',
                    'lkp_category.DISPLAY_NAME as CATEGORY_NAME',
                    'lkp_item_type.DISPLAY_NAME as ITEM_TYPE_NAME'
                )
                ->where('lkp_item.DEPARTMENT_ID', $deptId)
                ->where('lkp_item.ITEM_TYPE_ID', request('itemTypeId'))

                ->when(request('search'),       fn ($query) => $query->where('lkp_item.DISPLAY_NAME', 'like', '%' . request('search') . '%'))

                ->when(request('categoryId'),    fn ($query) => $query->where('lkp_item.CATEGORY_ID', request('categoryId')))
                ->when(request('subcategoryId'), fn ($query) => $query->where('lkp_item.SUB_CATEGORY_ID', request('subcategoryId')))

                // ->when(request('itemTypeId'), fn ($query) => $query->where('lkp_item.ITEM_TYPE_ID', request('itemTypeId')))
                ->orderBy('lkp_item.DISPLAY_NAME', 'asc')
                ->get();

            foreach ($items as $key => $value) {

                $data[$key] = [

                    'itemId'          => optional($value)->ITEM_ID,
                    'itemName'        => optional($value)->DISPLAY_NAME,
                    'itemTypeName'    => optional($value)->ITEM_TYPE_NAME,
                    'categoryId'      => optional($value)->CATEGORY_ID,
                    'categoryName'    => optional($value)->CATEGORY_NAME,
                    'subCategoryId'   => optional($value)->SUB_CATEGORY_ID,
                    'subCategoryName' => optional($value)->SUB_CATEGORY_NAME,
                    'isActive'        => optional($value)->ACTIVE_FLAG
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

    public function getSubItemsList(Request $request)
    {
        $deptId = $request->departmentId;
        $itemTypeId = $request->itemTypeId;

        try {

            $data = [];

            $items = DB::table('lkp_item')
                ->join('lkp_sub_category', 'lkp_item.SUB_CATEGORY_ID', '=', 'lkp_sub_category.SUB_CATEGORY_ID')
                ->join('lkp_category', 'lkp_item.CATEGORY_ID', '=', 'lkp_category.CATEGORY_ID')
                ->join('lkp_item_type', 'lkp_item.ITEM_TYPE_ID', '=', 'lkp_item_type.ITEM_TYPE_ID')
                ->select(
                    'lkp_item.*',
                    'lkp_sub_category.DISPLAY_NAME as SUB_CATEGORY_NAME',
                    'lkp_category.DISPLAY_NAME as CATEGORY_NAME',
                    'lkp_item_type.DISPLAY_NAME as ITEM_TYPE_NAME'
                )
                ->where('lkp_item.DEPARTMENT_ID', $deptId)

                ->when(request('search'),       fn ($query) => $query->where('lkp_item.DISPLAY_NAME', 'like', '%' . request('search') . '%'))

                ->when(request('categoryId'),    fn ($query) => $query->where('lkp_item.CATEGORY_ID', request('categoryId')))
                ->when(request('subcategoryId'), fn ($query) => $query->where('lkp_item.SUB_CATEGORY_ID', request('subcategoryId')))

                ->when(request('itemTypeId'), fn ($query) => $query->where('lkp_item.ITEM_TYPE_ID', request('itemTypeId')))
                ->get();

            foreach ($items as $key => $value) {

                $data[$key] = [

                    'itemId'          => optional($value)->ITEM_ID,
                    'itemName'        => optional($value)->DISPLAY_NAME,
                    'itemTypeName'    => optional($value)->ITEM_TYPE_NAME,
                    'categoryId'      => optional($value)->CATEGORY_ID,
                    'categoryName'    => optional($value)->CATEGORY_NAME,
                    'subCategoryId'   => optional($value)->SUB_CATEGORY_ID,
                    'subCategoryName' => optional($value)->SUB_CATEGORY_NAME,
                    'isActive'        => optional($value)->ACTIVE_FLAG
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

    public function storeItem(Request $request)
    {            
        try {
            $data = [];

            $item = new SubItem;

            $item->DISPLAY_NAME    = $request->item;
            $item->ITEM_TYPE_ID    = $request->item_type;
            $item->CATEGORY_ID     = $request->category;
            $item->SUB_CATEGORY_ID = $request->subcategory;
            $item->DEPARTMENT_ID   = $request->departmentId;
            $item->ACTIVE_FLAG     = 'Y';
            $item->CREATED_BY      = $request->userId;
            $item->CREATED_ON      = now();

            $item->save();

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
    public function getSubItem(Request $request)
    {        
        try {
            $data = [];

            $value = SubItem::find($request->itemId);
    
            $data[] = [
                'itemId'          => optional($value)->ITEM_ID,
                'itemName'        => optional($value)->DISPLAY_NAME,
                'itemTypeId'      => optional($value)->ITEM_TYPE_ID,
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
    public function updateItem(Request $request)
    {       
        try {
            $data = [];

            $item = SubItem::find($request->itemId);

            $item->DISPLAY_NAME    = $request->name;
            $item->ITEM_TYPE_ID    = $request->itemtype;
            $item->CATEGORY_ID     = $request->category;
            $item->SUB_CATEGORY_ID = $request->subcategory;
            $item->DEPARTMENT_ID   = $request->departmentId;
            $item->ACTIVE_FLAG     = $request->status;
            $item->MODIFIED_BY     = $request->userId;
            $item->MODIFIED_ON     = now();

            $item->save();

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