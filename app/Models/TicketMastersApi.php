<?php

namespace App\Models;

use GuzzleHttp\Psr7\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class TicketMastersApi extends Model
{
    use HasFactory;

    public  $baseUrl;
    private $accessKey;

    public function __construct()
    {
        $this->baseUrl   = 'http://localhost/tickets/v1/public/api';
        // $this->baseUrl   = 'https://tickets.iskconbangalore.net/v1/public/api';  
        $this->accessKey = '450!#kc@nHKRKkbngPiLnsg@498';
    }

    /**
     * Get all Categories
     */
    public function getCategories($data)
    {       
        $data['accessKey'] = $this->accessKey;
        $data['userId'] = Auth::user()->LOGIN_ID;
        $data['departmentId'] = session('code');

        $response = Http::accept('application/json')->post($this->baseUrl .'/get-categories', $data);

        return $response->collect();
    }

    /**
     * Add new Category
     */
    public function addCategory($details)
    {
        $data['accessKey'] = $this->accessKey;
        $data['categoryName'] = $details->category_name;
        $data['departmentId'] = session('code');
        $data['userId'] = Auth::user()->LOGIN_ID;

        $url = $this->baseUrl . '/add-category';

        $response = Http::accept('application/json')->post($url, $data);
        return $response->collect();
    }

    /**
     * Get Category details given category ID
     */
    public function getCategoryDetails($req)
    {
        $data['categoryId'] = $req->category_id;
        $data['accessKey'] = $this->accessKey;

        $response = Http::accept('application/json')->post($this->baseUrl . '/get-category-details', $data);
        return $response->collect();
    }

    /**
     * Update category Name in DB
     */
    public function updateCategory($details)
    {
        $data['accessKey'] = $this->accessKey;
        $data['categoryId'] = $details->categoryId;
        $data['categoryName'] = $details->categoryName;
        $data['status'] = $details->status;
        $data['userId'] = Auth::user()->LOGIN_ID;

        $url = $this->baseUrl . '/update-category';

        $response = Http::accept('application/json')->post($url, $data);
        return $response->collect();
    }

    /**
     * Deactivate or Activate Category
     */
    public function updateCategoryActivation($details)
    {
        $data['accessKey'] = $this->accessKey;
        $data['categoryId'] = $details->category_id;
        // $data['isActive'] = $details->activation_status;
        $data['userId'] = Auth::user()->LOGIN_ID;

        $url = $this->baseUrl . '/update-category-activation';
        $response = Http::accept('application/json')->post($url, $data);
        return $response->collect();
    }

    /**
     * Return all Subcategories
     */
    public function getSubCategories($data)
    {
        $data['accessKey'] = $this->accessKey;
        $data['userId'] = Auth::user()->LOGIN_ID;
        $data['departmentId'] = session('code');

        $response = Http::accept('application/json')->post($this->baseUrl . '/get-subcategories', $data);

        return $response->collect();
    }

    public function getSubCategoryList($data)
    {
        $data['accessKey'] = $this->accessKey;
        $data['userId'] = Auth::user()->LOGIN_ID;
        $data['departmentId'] = session('code');

        $response = Http::accept('application/json')->post($this->baseUrl . '/get-subcategory-list', $data);

        return $response->collect();
    }

    public function getSubCategory($data)
    {
        $data['accessKey'] = $this->accessKey;
        $data['userId'] = Auth::user()->LOGIN_ID;
        $data['departmentId'] = session('code');

        $response = Http::accept('application/json')->post($this->baseUrl . '/get-subcategory', $data);

        return $response->collect();
    }

    /**
     * Add Sub-Category
     */
    public function addSubCategory($details)
    {
        $data['accessKey'] = $this->accessKey;
        $data['userId'] = Auth::user()->LOGIN_ID;
        $data['departmentId'] = session('code');
        $data['subCategoryName'] = $details->sub_category_name;
        $data['sla'] = $details->sla;
        $data['categoryId'] = $details->category;

        $url = $this->baseUrl . '/add-subcategory';

        $response = Http::accept('application/json')->post($url, $data);
        return $response->collect();
    }

    /**
    * Get Details of sub-category given its ID
    */
    public function getSubCategoryDetails($req)
    {
        $data['accessKey'] = $this->accessKey;
        $data['subCategoryId'] = $req->sub_category_id;

        $url = $this->baseUrl . '/get-subcategory-details';

        $response = Http::accept('application/json')->post($url, $data);
        return $response->collect();
    }

    /**
    * Update Sub-Category
    */
    public function updateSubCategory($data)
    {
        $data['accessKey'] = $this->accessKey;
        $data['userId'] = Auth::user()->LOGIN_ID;
        $data['departmentId'] = session('code');
       
        $url = $this->baseUrl . '/update-subcategory';

        $response = Http::accept('application/json')->post($url, $data);

        return $response->collect();
    }

    /**
     * Activate / De-activate Sub-Category
     */
    public function updateSubCategoryActivation($req)
    {
        $data['accessKey'] = $this->accessKey;
        $data['subCategoryId'] = $req->sub_category_id;

        $url = $this->baseUrl . '/update-subcategory-activation';

        $response = Http::accept('application/json')->post($url, $data);
        return $response->collect();
    }
    public function getItems($data)
    {
        $data['accessKey'] = $this->accessKey;
        $data['userId'] = Auth::user()->LOGIN_ID;
        $data['departmentId'] = session('code');

        $response = Http::accept('application/json')->post($this->baseUrl . '/get-items', $data);

        return $response->collect();
    }
    public function getItemTypeList($data)
    {
        $data['accessKey'] = $this->accessKey;
        $data['userId'] = Auth::user()->LOGIN_ID;
        $data['departmentId'] = session('code');

        $response = Http::accept('application/json')->post($this->baseUrl . '/get-itemTypes-list', $data);

        return $response->collect();
    }

    public function getItem($data)
    {
        $data['accessKey'] = $this->accessKey;
        $data['userId'] = Auth::user()->LOGIN_ID;
        $data['departmentId'] = session('code');

        $response = Http::accept('application/json')->post($this->baseUrl . '/get-item', $data);

        return $response->collect();
    }
    public function storeItemType($data)
    {
        $data['accessKey'] = $this->accessKey;
        $data['userId'] = Auth::user()->LOGIN_ID;
        $data['departmentId'] = session('code');

        $response = Http::accept('application/json')->post($this->baseUrl . '/store-item-type', $data);

        return $response->collect();
    }
    public function updateItemType($data)
    {
        $data['accessKey'] = $this->accessKey;
        $data['userId'] = Auth::user()->LOGIN_ID;
        $data['departmentId'] = session('code');

        $response = Http::accept('application/json')->post($this->baseUrl . '/update-item-type', $data);

        return $response->collect();
    }
    public function getSubItems($data)
    {
        $data['accessKey'] = $this->accessKey;
        $data['userId'] = Auth::user()->LOGIN_ID;
        
        $response = Http::accept('application/json')->post($this->baseUrl . '/get-subitems', $data);

        return $response->collect();
    }
    public function getSubItemsList($data)
    {
        $data['accessKey'] = $this->accessKey;
        $data['userId'] = Auth::user()->LOGIN_ID;
        
        $response = Http::accept('application/json')->post($this->baseUrl . '/get-subitems-list', $data);

        return $response->collect();
    }

    public function getSubItem($data)
    {
        $data['accessKey'] = $this->accessKey;
        $data['userId'] = Auth::user()->LOGIN_ID;

        $response = Http::accept('application/json')->post($this->baseUrl . '/get-subitem', $data);

        return $response->collect();
    }
    public function storeItem($data)
    {
        $data['accessKey'] = $this->accessKey;
        $data['userId'] = Auth::user()->LOGIN_ID;
        $data['departmentId'] = session('code');

        $response = Http::accept('application/json')->post($this->baseUrl . '/store-item', $data);

        return $response->collect();
    }
    public function updateItem($data)
    {
        $data['accessKey'] = $this->accessKey;
        $data['userId'] = Auth::user()->LOGIN_ID;
        $data['departmentId'] = session('code');

        $response = Http::accept('application/json')->post($this->baseUrl . '/update-item', $data);

        return $response->collect();
    }
    public function getCategory($data)
    {       
        $data['accessKey'] = $this->accessKey;
        $data['userId'] = Auth::user()->LOGIN_ID;
       
        $response = Http::accept('application/json')->post($this->baseUrl .'/get-category', $data);

        return $response->collect();
    }
    public function subTaskInfo($data)
    {       
        $data['accessKey'] = $this->accessKey;
        $data['userId'] = Auth::user()->LOGIN_ID;
        $data['departmentId'] = session('code');       
       
        $response = Http::accept('application/json')->post($this->baseUrl .'/get-predefined-task', $data);

        return $response->collect();
    }
    public function subTaskUpdate($data)
    {       
        $data['accessKey'] = $this->accessKey;
        $data['userId'] = Auth::user()->LOGIN_ID;
       
        $response = Http::accept('application/json')->post($this->baseUrl .'/update-predefined-task', $data);

        return $response->collect();
    }
    public function getTemplateData($data)
    {
        $data['accessKey'] = $this->accessKey;
        $data['userId'] = Auth::user()->LOGIN_ID;
        $data['departmentId'] = session('code');

        $response = Http::accept('application/json')->post($this->baseUrl . '/templates-data', $data);

        return $response->collect();
    }

    public function getTemplates($data)
    {
        $data['accessKey'] = $this->accessKey;
        $data['userId'] = Auth::user()->LOGIN_ID;
        $data['departmentId'] = session('code');
       
        $response = Http::accept('application/json')->post($this->baseUrl .'/get-templates', $data);

        return $response->collect();
    }    
}