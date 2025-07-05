<?php

namespace App\Http\Controllers\Masters;

use DataTables;
use App\Http\Controllers\Controller;
use App\Models\TicketMastersApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\TicketsApi;

class CategoryController extends Controller
{
    //
    /**
     * Return Page listing all categories, or all categories as JSON
     * when page data is refreshed
     */
    public function index(Request $request)
    {
        $api = new TicketMastersApi;

        $categories = $api->getCategories($request->all());
        $subcategories = $api->getSubCategories([]);
        $subItems = DB::table('lkp_item')->where('ACTIVE_FLAG', 'Y')->get();
        
        if ($request->ajax() && !empty($categories)) { // put if $categories not empty

            return Datatables::of($categories['data'])->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<div class="d-flex justify-content-between table-actions-container">
                            <button class="btn tickets-action-btn-transparent" onclick="getCategInfo('. $row['categoryId'] .')"  title="Edit">
                                <img src="'. asset('public/img/icons/edit-btn.png') .'" alt="Edit" height="20">
                            </button>
                        </div>';

                    // $btn = '<div class="d-flex justify-content-between table-actions-container">
                    //     <button class="btn tickets-action-btn-transparent" onclick="getCategInfo('. $row->categoryId .')"  title="Edit">
                    //         <img src="'. asset('public/img/icons/edit-btn.png') .'" alt="Edit" height="20">
                    //     </button>
                    // </div>';

                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        } 
        return view('category', compact('categories', 'subcategories','subItems'));
    }

    /**
     * Add new Category within a selected department
     */
    public function add(Request $request)
    {
        $api = new TicketMastersApi;
        $result = $api->addCategory($request);
        return $result;
    }

    /**
     * Get details of a category given the ID
     */
    public function getDetails(Request $request)
    {
        $api = new TicketMastersApi;
        $details = $api->getCategoryDetails($request);
        return $details;
    }

    /**
     * Update category Name
     */
    public function update(Request $request)
    {
        $api = new TicketMastersApi;
        $result = $api->updateCategory($request);
        return $result;
    }

    /**
     * Update Category's Active Status
     */
    public function updateActivation(Request $request)
    {
        $api = new TicketMastersApi;
        $result = $api->updateCategoryActivation($request);
        return $result;
    }
    public function getCategories(Request $request)
    {
        $api = new TicketMastersApi;

        if(!$request->filled('search'))
        {
            $request->merge(['search' => '']);
        }

        $request->merge(['departmentId' => session('code')]);

        $result = $api->getCategories($request->all());

        return response()->json($result['data']);
    }
    public function getCategory(Request $request)
    {
        $api = new TicketMastersApi;

        $request->merge(['departmentId' => session('code')]);

        $result = $api->getCategory($request->all());

        return response()->json($result['data']);
    }

    public function updateTasks(Request $request)
    {
        $validated = $request->validate([
            'taskName' => 'required|string|max:255',
        ]);

        $task = DB::table('mstr_task_details')->find($request->taskId);

        if ($task) {
            $task->TASK_NAME = $request->taskName;
            $task->save();

            return response()->json(['success' => true, 'message' => 'Task updated successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'Task not found.'], 404);
    }
}