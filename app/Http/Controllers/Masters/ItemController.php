<?php

namespace App\Http\Controllers\Masters;

use DataTables;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Models\TicketMastersApi;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    public function getItems(Request $request)
    {
        $api = new TicketMastersApi;

        if(!$request->filled('search'))
        {
            $request->merge(['search' => '']);
        }

        $request->merge(['departmentId' => session('code')]);
        
        $result = $api->getItems($request->all());
        
        return response()->json($result['data']);
    }
    public function index(Request $request)
    {
        $api = new TicketMastersApi;

        $request->merge(['departmentId' => session('code')]);
        
        $result = $api->getItemTypeList($request->all());
    

        if($request->ajax()) {

            return Datatables::of($result['data'])->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = ' <button class="btn tickets-action-btn-transparent" onclick="getItemInfo('. $row['itemTypeId'] .')" title="Edit">
                    <img src="'.asset('public/img/icons/edit-btn.png').'" alt="Edit" height="20">
                </button>';

                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
            } 

    }
    public function storeItemType(Request $request)
    {
        $api = new TicketMastersApi;

        $response = $api->storeItemType($request->all());

        return response($response,200);

    }
    public function getItem(Request $request)
    {
        $api = new TicketMastersApi;

        $response = $api->getItem($request->all());

        return response()->json($response['data']);

    }
    public function update(Request $request)
    {
        $api = new TicketMastersApi;

        $response = $api->updateItemType($request->all());

        return response($response,200);

    }

    public function fetchItems(Request $request)
    {
        try {
            $data = [];

            $items = Item::where('ITEM_TYPE_ID',$request->itemTypeId)->get();

            foreach ($items as $value) {

                $data[] = [

                    'itemId'   => optional($value)->ITEM_ID,
                    'itemName' => optional($value)->DISPLAY_NAME,
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

    public function getAssetSelection(Request $request)
    {
        try {
            $data = [];

            $itemTypes = DB::table('lkp_item_type')
                ->where('lkp_item_type.ITEM_TYPE_ID', $request->itemTypeId)
                ->first();

            $data[] = [
                'isAssetSelection' => $itemTypes->ASSET_SELECTION,
                'isAssetRequired'  => $itemTypes->ASSET_ID_REQUIRED,
            ];

            $this->apiResponse['successCode'] = 1;
            $this->apiResponse['message'] = 'Success';
            $this->apiResponse['data'] = $data;

            return response()->json($this->apiResponse);

        } catch (\Exception $e) {
            $this->apiResponse['successCode'] = 0;
            $this->apiResponse['message']      = "Error please try again !";
            $this->apiResponse['message']      = $e->getMessage();
            $this->apiResponse['data'] = [];

            return response()->json($this->apiResponse);
        }
    }
    
}