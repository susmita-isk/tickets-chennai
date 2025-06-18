<?php

namespace App\Http\Controllers\Masters;

use DataTables;
use App\Http\Controllers\Controller;
use App\Models\TicketMastersApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubCategoryController extends Controller
{
    //
    /**
     * Return JSON listing of all Sub-Categories
     */
    public function index(Request $request)
    {
        $api = new TicketMastersApi;

        $subcategories = $api->getSubCategoryList($request->all());

        if ($request->ajax()) {

            return Datatables::of($subcategories['data'])->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = ' <button class="btn tickets-action-btn-transparent" onclick="getSubCategInfo('.$row['subCategoryId'].')" title="Edit">
                    <img src="'.asset('public/img/icons/edit-btn.png').'" alt="Edit" height="20">
                </button>';

                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
            }         
    }

    /**
     * Add a new sub-category
     */
    public function add(Request $request)
    {
        $api = new TicketMastersApi;
        $result = $api->addSubCategory($request);
        return $result;
    }

    /**
     * Get details of a Sub-category given the ID
     */
    public function getDetails(Request $request)
    {
        $api = new TicketMastersApi;
        $details = $api->getSubCategoryDetails($request);
        return $details;
    }

    /**
     * Update Sub-category Name
     */
    public function update(Request $request)
    {
        $api = new TicketMastersApi;
        $result = $api->updateSubCategory($request->all());
        return $result;
    }

    /**
     * Update Sub-Category's Active Status - Deactivate / Activate
     */
    public function updateActivation(Request $request)
    {
        $api = new TicketMastersApi;
        $result = $api->updateSubCategoryActivation($request);
        return $result;
    }
    public function getSubCategories(Request $request)
    {
        $api = new TicketMastersApi;

        if(!$request->filled('search'))
        {
            $request->merge(['search' => '']);
        }

        $request->merge(['departmentId' => session('code')]);

        $result = $api->getSubCategories($request->all());

        return response()->json($result['data']);
    }
    public function getSubCategory(Request $request)
    {
        $api = new TicketMastersApi;

        $result = $api->getSubCategory($request->all());

        return response()->json($result['data']);
    }
    public function subTaskInfo(Request $request)
    {
        $api = new TicketMastersApi;

        $result = $api->subTaskInfo($request->all());

        return response()->json($result['data']);
    }
    public function subTaskUpdate(Request $request)
    {
        $api = new TicketMastersApi;

        $result = $api->subTaskUpdate($request->all());

        return response()->json($result);
    }    
}