<?php

namespace App\Http\Controllers\Masters;

use DataTables;
use Illuminate\Http\Request;
use App\Models\TicketMastersApi;
use App\Http\Controllers\Controller;

class SubItemController extends Controller
{
    public function getSubItems(Request $request)
    {
        $api = new TicketMastersApi;

        if(!$request->filled('search'))
        {
            $request->merge(['search' => '']);
        }

        $request->merge(['departmentId' => session('code')]);

        $result = $api->getSubItems($request->all());

        return response()->json($result['data']);
    }
    public function index(Request $request)
    {
        $api = new TicketMastersApi;

        $request->merge(['departmentId' => session('code')]);

        $result = $api->getSubItemsList($request->all());

        if($request->ajax()) {

            return Datatables::of($result['data'])->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = ' <button class="btn tickets-action-btn-transparent" onclick="getSubItemInfo('.$row['itemId'].')" title="Edit">
                    <img src="'.asset('public/img/icons/edit-btn.png').'" alt="Edit" height="20">
                </button>';

                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
            } 

    }
    public function storeItem(Request $request)
    {
        $api = new TicketMastersApi;

        $response = $api->storeItem($request->all());

        return response($response,200);

    }
    public function updateItem(Request $request)
    {
        $api = new TicketMastersApi;

        $response = $api->updateItem($request->all());

        return response($response,200);

    }
    public function getSubItem(Request $request)
    {
        $api = new TicketMastersApi;

        $response = $api->getSubItem($request->all());

        return response()->json($response['data']);
    }
}
