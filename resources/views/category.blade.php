@extends('layouts.main.app')

@section('page-title', 'Categories')

@section('css-content')
<link rel="stylesheet" href="{{asset('public/dist/css/category.css')}}">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.sidebar-mini.sidebar-collapse .main-sidebar,
.sidebar-mini.sidebar-collapse .main-sidebar::before {
    margin-left: 0;
    width: 2.2rem;
}
</style>
@endsection

@section('breadcrumb-menu')
<li class="breadcrumb-item active">Tickets</li>
@endsection

@section('page-content')

@php
$permission = permission();
@endphp
@if(in_array(Route::currentRouteName(),$permission))

<div class="container-fluid">
    {{-- For Action Buttons on the right side --}}
    <div class="mt-1" id="actionBtnsContainer">
        <button class="btn tickets-action-btn" id="filterCategoriesBtn" data-target="#filterCategoryModal"
            data-toggle="modal" title="Filter Categories" style="display: none;">
            <img src="{{asset('public/img/icons/filter.png')}}" alt="Filter Categories">
        </button>
        <button class="btn tickets-action-btn" id="addCategoryBtn" data-target="#addCategoryModal" data-toggle="modal"
            title="Add Category" style="display: none;">
            <i class="fas fa-plus"></i> Add
        </button>
        <button class="btn tickets-action-btn" id="filterSubCategoriesBtn" data-target="#filterSubCategoryModal"
            data-toggle="modal" title="Filter Subcategories" style="display: none;">
            <img src="{{asset('public/img/icons/filter.png')}}" alt="Filter Subcategories">
        </button>
        <button class="btn tickets-action-btn" id="addSubCategoryBtn" data-target="#addSubCategoryModal"
            data-toggle="modal" title="Add Subcategory" style="display: none;">
            <i class="fas fa-plus"></i> Add
        </button>
        <button class="btn tickets-action-btn" id="filterItemTypesBtn" data-target="#filterItemTypeModal"
            data-toggle="modal" title="Filter Item Types" style="display: none;">
            <img src="{{asset('public/img/icons/filter.png')}}" alt="Filter Item Types">
        </button>
        <button class="btn tickets-action-btn" id="addItemTypeBtn" data-target="#addItemTypeModal" data-toggle="modal"
            title="Add Item Type" style="display: none;">
            <i class="fas fa-plus"></i> Add
        </button>
        <button class="btn tickets-action-btn" id="filterItemsBtn" data-target="#filterItemModal" data-toggle="modal"
            title="Filter Items" style="display: none;">
            <img src="{{asset('public/img/icons/filter.png')}}" alt="Filter Items">
        </button>
        <button class="btn tickets-action-btn" id="addItemBtn" data-target="#addItemModal" data-toggle="modal"
            title="Add Item" style="display: none;">
            <i class="fas fa-plus"></i> Add
        </button>
        <button class="btn tickets-action-btn" id="filtersubcatTaskBtn" data-target="#filterSubcatTaskModal"
            data-toggle="modal" title="Filter Ticket Template" style="display: none;">
            <img src="{{asset('public/img/icons/filter.png')}}" alt="Filter Ticket Template">
        </button>

    </div>
    <div id="tabsContainer" class="mt-3 pb-3">
        <ul class="nav nav-tabs tickets-nav-tabs" id="categTablesTabsMenu" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link tickets-tab-link active" id="categoriesTabLink" type="button" data-toggle="tab"
                    data-target="#categoriesTabPanel" aria-controls="categoriesTabPanel" aria-selected="true">
                    <i class="fas fa-list"></i> Category
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link tickets-tab-link" id="subCategoriesTabLink" type="button" data-toggle="tab"
                    data-target="#subCategoriesTabPanel" aria-controls="subCategoriesTabPanel" aria-selected="">
                    <i class="fas fa-list"></i> Subcategory
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link tickets-tab-link" id="itemTypesTabLink" type="button" data-toggle="tab"
                    data-target="#itemTypesTabPanel" aria-controls="itemTypesTabPanel" aria-selected="">
                    <i class="fas fa-list"></i> Item Type
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link tickets-tab-link" id="itemsTabLink" type="button" data-toggle="tab"
                    data-target="#itemsTabPanel" aria-controls="itemsTabPanel" aria-selected="">
                    <i class="fas fa-list"></i> Item
                </button>
            </li>
        </ul>
        {{-- Tab content for Master tables --}}
        <div class="tab-content" id="categoryTablesContainer">
            <div class="tab-pane tickets-tab-pane fade show active" id="categoriesTabPanel" role="tabpanel"
                aria-labelledby="categoriesTabLink">
                <div class="tickets-tab-pane-content">
                    <div class="row">
                        <div class="col">
                            <div class="table-container">
                                <div class="table-responsive">
                                    <table class="table table-hover tickets-main-table" id="categoriesTable"
                                        style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>Sl No</th>
                                                <th>Category</th>
                                                <th width="20px;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane tickets-tab-pane fade" id="subCategoriesTabPanel" role="tabpanel"
                aria-labelledby="subCategoriesTabLink">
                <div class="tickets-tab-pane-content">
                    <div class="row">
                        <div class="col">
                            <div class="table-container">
                                <div class="table-responsive">
                                    <table class="table table-hover tickets-main-table" id="subCategoriesTable"
                                        style="width: 100%">
                                        <thead>
                                            <tr>
                                                <th>Sl No. </th>
                                                <th>Subcategory </th>
                                                <th>Category </th>
                                                <!-- <th>SLA (Hours)</th> -->
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane tickets-tab-pane fade" id="itemTypesTabPanel" role="tabpanel"
                aria-labelledby="itemTypesTabLink">
                <div class="tickets-tab-pane-content">
                    <div class="row">
                        <div class="col">
                            <div class="table-container">
                                <div class="table-responsive">
                                    <table class="table table-hover tickets-main-table" id="itemTypesTable"
                                        style="width : 100%">
                                        <thead>
                                            <tr>
                                                <th>Sl. No.</th>
                                                <th>Item Type</th>
                                                <th>Subcategory</th>
                                                <th>Category</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane tickets-tab-pane fade" id="itemsTabPanel" role="tabpanel"
                aria-labelledby="itemsTabLink">
                <div class="tickets-tab-pane-content">
                    <div class="row">
                        <div class="col">
                            <div class="table-container">
                                <div class="table-responsive">
                                    <table class="table table-hover tickets-main-table" id="itemsTable"
                                        style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>Sl No. </th>
                                                <th>Item </th>
                                                <th>Item Type </th>
                                                <th>Subcategory </th>
                                                <th>Category </th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- /.container-fluid -->

@else
<div class="box-body text-center mt-4">
    <div class="row">
        <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
            Oops! You don't have permission to access this page.
        </div>
    </div>
</div>
@endif

@endsection

<!-- Begin Add / Edit / Filter Modals -->

{{-- BEGIN Add Category Modal --}}
<div class="modal tickets-modal fade" id="addCategoryModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Add Category</h6>
                <button class="close" data-dismiss="modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form action="{{route('categories.add')}}" method="post" id="addCategoryForm" accept-charset="utf-8">
                    @csrf
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="category_name">Category</label>
                                <input type="text" name="category_name" class="form-control" id="category_name"
                                    placeholder="Category">
                            </div>
                        </div>
                        <div class="col-12 text-center">
                            <button type="submit" class="btn tickets-modal-submit-btn">Add</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
{{-- END Add Category Modal --}}

{{-- BEGIN Edit Category Modal --}}
<div class="modal tickets-modal fade" id="editCategoryModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Edit Category</h6>
                <button class="close" data-dismiss="modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="editCategoryForm">
                    @csrf
                    <div class="row">
                        <input type="hidden" name="categoryId" id="edit_category_id">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="edit_category_name">Category</label>
                                <input type="text" name="categoryName" class="form-control" id="edit_category_name"
                                    placeholder="Category">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="category_name">Status</label>
                                <select name="status" class="form-control" id="edit_category_status">
                                    <option value="Y">Active</option>
                                    <option value="N">InActive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 text-center">
                            <button type="submit" class="btn tickets-modal-submit-btn">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
{{-- END Edit Category Modal --}}

{{-- BEGIN Deactivate Category Modal --}}
<div class="modal tickets-modal fade" id="deactivateCategoryModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">Confirmation</div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col">
                        <div class="confirmation-text">
                            Are you sure you want to Deactivate this Category?
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-7"></div>
                    <div class="col d-flex justify-content-between">
                        <button class="btn tickets-modal-submit-btn" value="yes" id="confirmChangeCategoryActivationBtn"
                            onclick="changeCategoryActivation(this)">
                            Yes
                        </button>
                        <button class="btn tickets-modal-submit-btn" value="no" data-dismiss="modal">No</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- END Deactivate Category Modal --}}

{{-- BEGIN Add Subcategory Modal --}}
<div class="modal tickets-modal fade" id="addSubCategoryModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Add Subcategory</h6>
                <button class="close" data-dismiss="modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form action="{{route('subcategories.add')}}" method="post" id="addSubCategoryForm"
                    accept-charset="utf-8">
                    @csrf
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="sub_category_category_name">Category</label>
                                <select class="form-control" id="category" name="category" required>

                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="sub_category_name">Subcategory</label>
                                <input type="text" name="sub_category_name" class="form-control" id="sub_category_name"
                                    placeholder="Subcategory" required>
                            </div>
                        </div>

                        <!-- <div class="col-12">
                            <div class="form-group">
                                <label for="sub_category_name">SLA (Hours)</label>
                                <input type="text" name="sla" class="form-control" id="sla" placeholder="SLA" required>
                            </div>
                        </div> -->
                        <div class="col-12 text-center">
                            <button type="submit" class="btn tickets-modal-submit-btn">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
{{-- END Add Subcategory Modal --}}

{{-- BEGIN Edit Subcategory Modal --}}
<div class="modal tickets-modal fade" id="editSubCategoryModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Edit Subcategory</h6>
                <button class="close" data-dismiss="modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="editSubCategoryForm">
                    @csrf
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="edit_sub_category_category_name">Category</label>
                                <select class="form-control" id="categoryEdit" name="categoryEdit" required>

                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="edit_sub_category_name">Subcategory</label>
                                <input type="text" name="name" class="form-control" id="edit_sub_category_name"
                                    placeholder="Subcategory" required>
                                <input type="hidden" name="subCategoryId" id="edit_sub_category_id">
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label for="category_name">Status</label>
                                <select name="status" class="form-control" id="edit_sub_category_status" required>
                                    <option value="Y">Active</option>
                                    <option value="N">InActive</option>
                                </select>
                            </div>
                        </div>
                        <!-- <div class="col-12">
                            <div class="form-group">
                                <label for="category_name">SLA (Hours)</label>
                                <input type="text" name="sla" class="form-control" id="edit_sla" placeholder="SLA"
                                    required>
                            </div>
                        </div> -->
                        <div class="col-12 text-center">
                            <button type="submit" class="btn tickets-modal-submit-btn">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
{{-- END Edit Subcategory Modal --}}

{{-- BEGIN Activate/Deactivate Subcategory Modal --}}
<div class="modal tickets-modal fade" id="deactivateSubCategoryModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">Confirmation</div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col">
                        <div class="confirmation-text">
                            Are you sure you want to Deactivate this Subcategory?
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-7"></div>
                    <div class="col d-flex justify-content-between">
                        <button class="btn tickets-modal-submit-btn" value="yes" id="changeSubCategActivationBtn"
                            onclick="changeSubCategoryActivation(this)">Yes</button>
                        <button class="btn tickets-modal-submit-btn" value="no" data-dismiss="modal">No</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- END Activate/Deactivate Subcategory Modal --}}

{{-- BEGIN Add Item Type Modal --}}
<div class="modal tickets-modal fade" id="addItemTypeModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Add Item Type</h6>
                <button class="close" data-dismiss="modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="addItemTypeForm">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="item_type_category">Category</label><br>
                                <select class="form-control category" id="item_type_category" name="category"
                                    style="width: 100%;" required>

                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="item_type_sub_category">Subcategory</label><br>
                                <select name="subcategory" class="form-control subcategory" id="item_type_sub_category"
                                    name="sub_category" required>

                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="item_type_name">Item Type</label>
                                <input type="text" name="name" class="form-control" id="item_type_name"
                                    placeholder="Item Type" required>
                            </div>
                        </div>
                        <div class="col-12 text-center">
                            <button type="submit" class="btn tickets-modal-submit-btn">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
{{-- END Add Item Type Modal --}}

{{-- BEGIN Edit Item Type Modal --}}
<div class="modal tickets-modal fade" id="editItemTypeModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Edit Item Type</h6>
                <button class="close" data-dismiss="modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="editItemTypeForm">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="edit_item_type_category">Category</label>
                                <input type="hidden" name="itemTypeId" id="edit_item_type_id">
                                <select class="form-control" id="edit_item_type_category" name="category">

                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="edit_item_type_sub_category">Subcategory</label>
                                <select name="sub_category" class="form-control" id="edit_item_type_sub_category">

                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="edit_item_type">Item Type</label>
                                <input type="text" class="form-control" name="name" id="edit_item_type_name"
                                    placeholder="Item Type">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="category_name">Status</label>
                                <select name="status" class="form-control" id="edit_item_type_status">
                                    <option value="Y">Active</option>
                                    <option value="N">InActive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 text-center">
                            <button type="submit" class="btn tickets-modal-submit-btn">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
{{-- END Edit Item Type Modal --}}

{{-- BEGIN Deactivate Item Type Modal --}}
<div class="modal tickets-modal fade" id="deactivateItemTypeModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">Confirmation</div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col">
                        <div class="confirmation-text">
                            Are you sure you want to Deactivate this Item Type?
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-7"></div>
                    <div class="col d-flex justify-content-between">
                        <button class="btn tickets-modal-submit-btn" value="yes">Yes</button>
                        <button class="btn tickets-modal-submit-btn" value="no" data-dismiss="modal">No</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- END Deactivate Item Type Modal --}}

{{-- BEGIN Add Item Modal --}}
<div class="modal tickets-modal fade" id="addItemModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Add Item</h6>
                <button class="close" data-dismiss="modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="addItemForm">
                    @csrf
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="item_category">Category</label>
                                <select class="form-control category" id="item_category" name="category"
                                    style="width :100%" required>
                                    <option value="">Please Select</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="item_Sub_category">Subcategory</label>
                                <select class="form-control subcategory" id="item_sub_category" name="subcategory"
                                    required>
                                    <option value="">Please Select</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="itemType">Item Type</label>
                                <select id="item_type" class="form-control item" name="item_type" required>
                                    <option value="">Please Select</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="item_name">Item</label>
                                <input type="text" class="form-control" id="item_name" name="item" placeholder="Item">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="category_name">Status</label>
                                <select name="status" class="form-control" id="item_status">
                                    <option value="Y">Active</option>
                                    <option value="N">InActive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 text-center">
                            <button type="submit" class="btn tickets-modal-submit-btn">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
{{-- END Add Item Modal --}}

{{-- BEGIN Edit Item Modal --}}
<div class="modal tickets-modal fade" id="editItemModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Edit Item</h6>
                <button class="close" data-dismiss="modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="editItemForm">
                    @csrf
                    <input type="hidden" id="edit_item_id" name="itemId">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="edit_item_category">Category</label>
                                <select class="form-control" id="edit_item_category" name="category">
                                    <option value="">Please Select</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="edit_item_sub_category">Subcategory</label>
                                <select name="subcategory" class="form-control" id="edit_item_sub_category">
                                    <option value="">Please Select</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="editItemType">Item Type</label>
                                <select name="itemtype" id="edit_item_item_type" class="form-control ">
                                    <option value="">Please Select</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="edit_item_name">Item</label>
                                <input type="text" class="form-control" id="edit_item_name" name="name"
                                    placeholder="Item">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="category_name">Status</label>
                                <select name="status" class="form-control" id="edit_item_status">
                                    <option value="Y">Active</option>
                                    <option value="N">InActive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 text-center">
                            <button type="submit" class="btn tickets-modal-submit-btn">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
{{-- END Edit Item Modal --}}

{{-- BEGIN Deactivate Item Modal --}}
<div class="modal tickets-modal fade" id="deactivateItemModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">Confirmation</div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col">
                        <div class="confirmation-text">
                            Are you sure you want to Deactivate this Item?
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-7"></div>
                    <div class="col d-flex justify-content-between">
                        <button class="btn tickets-modal-submit-btn" value="yes">Yes</button>
                        <button class="btn tickets-modal-submit-btn" value="no" data-dismiss="modal">No</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- END Deactivate Item Modal --}}


{{-- BEGIN Edit Subcategory Task Modal --}}
<div class="modal tickets-modal fade" id="editsubcatTaskModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Edit Subcategory Task</h6>
                <button class="close" data-dismiss="modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="editsubcatTask">
                    @csrf
                    <input type="hidden" id="edit_taskId" name="taskId">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="category_name">Category</label>
                                <select name="category" class="form-control" id="sub_category_task_category_edit">
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="category_name">Subcategory</label>
                                <select name="subcategory" class="form-control" id="sub_category_task_edit">
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="category_name">Item Type</label>
                                <select name="itemType" class="form-control" id="sub_category_task_itemType_edit">
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="category_name">Item</label>
                                <select name="item" class="form-control" id="sub_category_task_item_edit">
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="category_name">Subject</label>
                                <input type="text" class="form-control" id="subject_task_edit" name="subject"
                                    placeholder="Subject">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="category_name">Description</label>
                                <textarea class="form-control" id="description_task_edit" name="description"
                                    placeholder="Description"></textarea>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="category_name">Status</label>
                                <select name="status" class="form-control" id="status_task_edit">
                                    <option value="Y">Active</option>
                                    <option value="N">InActive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 text-center">
                            <button type="submit" class="btn tickets-modal-submit-btn">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Filter Tickets Modal -->
<div class="modal tickets-modal fade" id="filterCategoryModal">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">Filter Category</div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12 mb-5">
                        <input type="text" class="form-control" id="categoryFilter" name="category"
                            placeholder="Category">
                    </div>
                    <div class="col text-right">
                        <button type="button" class="btn tickets-modal-submit-btn" id="filterBtnCategory">Apply</button>
                        <button type="reset" class="btn tickets-modal-submit-btn mr-2" id="clearBtnCategory">Clear
                            All</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Filter Tickets Modal -->
<div class="modal tickets-modal fade" id="filterSubCategoryModal">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">Filter Subcategory</div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12 mb-3">
                        <select class="form-control" id="categoryFilterSub" name="category">
                        </select>
                    </div>
                    <div class="col-sm-12 mb-2">
                        <input type="text" class="form-control" id="subcategoryFilter" name="subcategory"
                            placeholder="Subcategory">
                    </div>

                    <div class="col text-right">
                        <button type="button" class="btn tickets-modal-submit-btn" id="filterBtnSub">Apply</button>
                        <button type="reset" class="btn tickets-modal-submit-btn mr-2" id="clearBtnSub">Clear
                            All</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal tickets-modal fade" id="filterItemTypeModal">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">Filter Item Type</div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12 mb-2">
                        <input type="text" class="form-control" id="itemTypeFilter" name="category"
                            placeholder="Item Types">
                    </div>
                    <div class="col-sm-12 mb-3">
                        <select class="form-control category" id="categoryFilterIt" name="category">

                        </select>
                    </div>
                    <div class="col-sm-12 mb-3">
                        <select class="form-control subcategory" id="subcategoryFilterIt" name="category">

                        </select>
                    </div>
                    <div class="col text-right">
                        <button type="button" class="btn tickets-modal-submit-btn" id="filterBtnIt">Apply</button>
                        <button type="reset" class="btn tickets-modal-submit-btn mr-2" id="clearBtnIt">Clear
                            All</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal tickets-modal fade" id="filterItemModal">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">Filter Item </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12 mb-2">
                        <input type="text" class="form-control" id="itemFilter" placeholder="Item">
                    </div>
                    <div class="col-sm-12 mb-3">
                        <select class="form-control category" id="categoryFilterItem">

                        </select>
                    </div>
                    <div class="col-sm-12 mb-3">
                        <select class="form-control subcategory" id="subcategoryFilterItem">

                        </select>
                    </div>
                    <div class="col-sm-12 mb-3">
                        <select class="form-control item" id="itemTypeFilterItem">

                        </select>
                    </div>
                    <div class="col text-right">
                        <button type="button" class="btn tickets-modal-submit-btn" id="filterBtnItem">Apply</button>
                        <button type="reset" class="btn tickets-modal-submit-btn mr-2" id="clearBtnItem">Clear
                            All</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal tickets-modal fade" id="filterSubcatTaskModal">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">Filter Ticket Template </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12 mb-3">
                        <select class="form-control" id="filterSubcatItem" placeholder="Item">
                            <option value="">Select Item</option>
                            @foreach($subItems as $val)
                            <option value="{{$val->ITEM_ID}}">{{$val->DISPLAY_NAME}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-12 mb-3">
                        <input type="text" class="form-control" id="filterSubcatSubject" name="filterSubcatSubject"
                            placeholder="Subject">
                    </div>
                    <div class="col-sm-12 mb-3">
                        <input type="text" class="form-control" id="filterSubcatDesc" name="filterSubcatDesc"
                            placeholder="Description">
                    </div>
                    <div class="col text-right">
                        <button type="button" class="btn tickets-modal-submit-btn"
                            id="filterBtnTicketTemplate">Apply</button>
                        <button type="reset" class="btn tickets-modal-submit-btn mr-2" id="clearBtnTicketTemplate">Clear
                            All</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- / Filter Tickets Modal-->
<!-- End Modals -->

@section('js-content')
<script>
// URLs, CSRF token and global varaibles
const csrfToken = $('meta[name=csrf-token]').attr('content');
const categoryDetailsURL = '{{route("categories.get-details")}}';
const categoryActivationChangeURL = " {{route('categories.update-activation')}}";
const
    subCategoryDetailsURL = "{{route('subcategories.get-details')}}";
const
    subCategoryactivationChangeURL = "{{route('subcategories.update-activation')}}";
</script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js">
</script>
<script>
$(function() {


    loadCategories()

    $('.category').select2({
        placeholder: 'Category Name',
        ajax: {
            url: '{{ route("categories.get") }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    search: params.term,
                    type: 'public'
                };
            },
            processResults: function(data) {
                var options = data.map(function(category) {
                    return {
                        id: category.categoryId,
                        text: category.categoryName,
                    };
                });

                return {
                    results: options
                };
            },
        },
    }).on('select2:select', function(e) {
        var categoryId = e.params.data.id;

        // Subcategory Dropdown Setup
        $('.subcategory').select2({
            placeholder: 'Subcategory Name',
            ajax: {
                url: '{{ route("subcategories.get") }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        categoryId: categoryId,
                        search: params.term,
                        type: 'public'
                    };
                },
                processResults: function(data) {
                    var options = data.map(function(
                        subcategory) {
                        return {
                            id: subcategory
                                .subCategoryId,
                            text: subcategory
                                .subCategoryName,
                        };
                    });

                    return {
                        results: options
                    };
                },
            }
        }).on('select2:select', function(e) {
            var subcategoryId = e.params.data.id;

            // Items Dropdown Setup
            $('.item').select2({
                placeholder: 'Item Type',
                ajax: {
                    url: '{{ route("items.get") }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            subcategoryId: subcategoryId,
                            search: params.term,
                            type: 'public'
                        };
                    },
                    processResults: function(data) {
                        var options = data.map(
                            function(item) {
                                return {
                                    id: item
                                        .itemTypeId,
                                    text: item
                                        .itemTypeName,
                                };
                            });

                        return {
                            results: options
                        };
                    },
                }
            });
        });

        $('#sub_category_task_itemType_add').on('change', function() {
            var itemTypeId = $(
                '#sub_category_task_itemType_add').val();
            // var itemTypeId = e.params.data.id;

            // Items Dropdown Setup
            $('.items').select2({
                placeholder: 'Items',
                ajax: {
                    url: '{{ route("subitems.get") }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            itemTypeId: itemTypeId,
                            search: params.term,
                            type: 'public'
                        };
                    },
                    processResults: function(data) {
                        var options = data.map(
                            function(item) {
                                return {
                                    id: item
                                        .itemId,
                                    text: item
                                        .itemName,
                                };
                            });

                        return {
                            results: options
                        };
                    },

                }
            });
        });
    });


    $('.categoryTask').select2({
        placeholder: 'Category Name',
        ajax: {
            url: '{{ route("categories.get") }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    search: params.term,
                    type: 'public'
                };
            },
            processResults: function(data) {
                var options = data.map(function(category) {
                    return {
                        id: category.categoryId,
                        text: category.categoryName,
                    };
                });

                return {
                    results: options
                };
            },
        },
    });


    var subCategoriesTable = $('#subCategoriesTable').DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        // ordering: false,
        order: [],
        dom: "<'row'<'col-sm-12'>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        ajax: {
            url: "{{ route('subcategories.list') }}",
            data: function(d) {

                d.searchTerm = $('#subcategoryFilter').val();
                d.categoryId = $('#categoryFilterSub').val();

            }
        },
        columns: [{
                data: null,
                render: function(data, type, row, meta) {
                    // Render serial number
                    return meta.row + 1;
                },
                className: 'text-font-0'
            },
            {
                data: 'subCategoryName',
                name: 'subCategoryName',
                className: 'text-font-0'
            },
            {
                data: 'categoryName',
                name: 'categoryName',
                className: 'text-font-0'
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                className: 'text-center'
            },
        ]
    }).on('error.dt', function(e, settings, techNote, message) {
        console.log('DataTables error: ', message);
        // Prevent default alert behavior
        e.preventDefault();
    });

    $("#filterBtnSub").on('click', function() {

        subCategoriesTable.page.len(-1).draw();

        $('#subCategoriesTable').DataTable().ajax.reload(null, false);

        $('#filterSubCategoryModal').modal('hide');


    });

    $("#clearBtnSub").on('click', function() {

        $('#subcategoryFilter').val('');

        $('#categoryFilterSub').val('');

        $('#subCategoriesTable').DataTable().ajax.reload(null, false);

        subCategoriesTable.page.len(10).draw();

        $('#filterSubCategoryModal').modal('hide');


    });

    var categoriesTable = $('#categoriesTable').DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        ordering: false,
        dom: "<'row'<'col-sm-12'>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        ajax: {
            url: "{{ route('categories-page') }}",
            data: function(d) {

                d.search = $('#categoryFilter').val();

            }
        },
        columns: [{
                data: null,
                render: function(data, type, row, meta) {
                    // Render serial number
                    return meta.row + 1;
                },
                className: 'text-font-0'
            },
            {
                data: 'categoryName',
                name: 'categoryName',
                className: 'text-font-0'
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                className: 'text-center'
            },
        ]
    }).on('error.dt', function(e, settings, techNote, message) {
        console.log('DataTables error: ', message);
        // Prevent default alert behavior
        e.preventDefault();
    });

    $("#filterBtnCategory").on('click', function() {

        categoriesTable.page.len(-1).draw();

        $('#categoriesTable').DataTable().ajax.reload(null, false);

        $('#filterCategoryModal').modal('hide');


    });

    $("#clearBtnCategory").on('click', function() {

        $('#categoryFilter').val('');

        $('#categoriesTable').DataTable().ajax.reload(null, false);

        categoriesTable.page.len(10).draw();

        $('#filterCategoryModal').modal('hide');


    });

    var itemTypesTable = $('#itemTypesTable').DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        dom: "<'row'<'col-sm-12'>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        ajax: {
            url: "{{ route('items') }}",
            data: function(d) {

                d.search = $('#itemTypeFilter').val();
                d.categoryId = $('#categoryFilterIt').val();
                d.subcategoryId = $('#subcategoryFilterIt').val();

            }
        },
        columns: [{
                // data: 'itemTypeId',
                // name: 'itemTypeId',
                data: null,
                render: function(data, type, row, meta) {
                    // Render serial number
                    return meta.row + 1;
                },
                className: 'text-font-0'
            },
            {
                data: 'itemTypeName',
                name: 'itemTypeName',
                className: 'text-font-0'
            },
            {
                data: 'subCategoryName',
                name: 'subCategoryName',
                className: 'text-font-0'
            },
            {
                data: 'categoryName',
                name: 'categoryName',
                className: 'text-font-0'
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                className: 'text-center'
            },
        ]
    }).on('error.dt', function(e, settings, techNote, message) {
        console.log('DataTables error: ', message);
        // Prevent default alert behavior
        e.preventDefault();
    });

    $("#filterBtnIt").on('click', function() {

        itemTypesTable.page.len(-1).draw();

        $('#itemTypesTable').DataTable().ajax.reload(null, false);

        $('#filterItemTypeModal').modal('hide');


    });

    $("#clearBtnIt").on('click', function() {

        $('#categoryFilterIt').val(null).trigger('change');

        $('#subcategoryFilterIt').val(null).trigger('change');

        $('#itemTypeFilter').val('');

        $('#itemTypesTable').DataTable().ajax.reload(null, false);

        itemTypesTable.page.len(10).draw();

        $('#filterItemTypeModal').modal('hide');


    });

    var itemsTable = $('#itemsTable').DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        dom: "<'row'<'col-sm-12'>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        ajax: {
            url: "{{ route('subitems') }}",
            data: function(d) {

                d.categoryId = $('#categoryFilterItem').val();

                d.subcategoryId = $('#subcategoryFilterItem').val();

                d.itemTypeId = $('#itemTypeFilterItem').val();

                d.search = $('#itemFilter').val();

            }
        },
        columns: [{
                // data: 'itemId',
                // name: 'itemTypeId',
                data: null,
                render: function(data, type, row, meta) {
                    // Render serial number
                    return meta.row + 1;
                },
                className: 'text-font-0'
            },
            {
                data: 'itemName',
                name: 'itemTypeName',
                className: 'text-font-0'
            },
            {
                data: 'itemTypeName',
                name: 'itemTypeName',
                className: 'text-font-0'
            },
            {
                data: 'subCategoryName',
                name: 'subCategoryName',
                className: 'text-font-0'
            },
            {
                data: 'categoryName',
                name: 'categoryName',
                className: 'text-font-0'
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                className: 'text-center'
            },
        ]
    }).on('error.dt', function(e, settings, techNote, message) {
        console.log('DataTables error: ', message);
        // Prevent default alert behavior
        e.preventDefault();
    });


    var subTaskTable = $('#subTaskTable').DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        dom: "<'row'<'col-sm-12'>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        ajax: {
            url: "{{ route('templates.data') }}",
            data: function(d) {
                // d.categoryId = $('#categoryFilterItem').val();
            }
        },
        columns: [{
                // data: 'templateName',
                // name: 'templateName',
                data: null,
                render: function(data, type, row, meta) {
                    // Render serial number
                    return meta.row + 1;
                },
                className: 'text-font-0'
            },
            {
                data: 'templateName',
                name: 'templateName',
                className: 'text-font-0'
            },
            {
                data: 'status',
                name: 'status',
                className: 'text-font-0'
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                className: 'text-center'
            },
        ]
    }).on('error.dt', function(e, settings, techNote, message) {
        console.log('DataTables error: ', message);
        // Prevent default alert behavior
        e.preventDefault();
    });



    $("#filterBtnItem").on('click', function() {

        itemsTable.page.len(-1).draw();

        $('#itemsTable').DataTable().ajax.reload(null, false);

        $('#filterItemModal').modal('hide');


    });

    $('#filterBtnTicketTemplate').on('click', function() {

        subcatTasksTable.page.len(-1).draw();

        $('#subcatTasksTable').DataTable().ajax.reload(null, false);

        $('#filterSubcatTaskModal').modal('hide');
    });

    $('#clearBtnTicketTemplate').on('click', function() {
        $('#filterSubcatItem').val('');
        $('#filterSubcatSubject').val('');
        $('#filterSubcatDesc').val('');

        $('#subcatTasksTable').DataTable().ajax.reload(null, false);

        subcatTasksTable.page.len(10).draw();

        $('#filterSubcatTaskModal').modal('hide');
    });

    $("#clearBtnItem").on('click', function() {

        $('#categoryFilterItem').val(null).trigger('change');

        $('#subcategoryFilterItem').val(null).trigger('change');

        $('#itemTypeFilterItem').val(null).trigger('change');

        $('#itemFilter').val('');

        $('#itemsTable').DataTable().ajax.reload(null, false);

        itemsTable.page.len(10).draw();

        $('#filterItemModal').modal('hide');


    });


    $("#addItemTypeForm").submit(function(e) {
        // Prevent Default functionality
        e.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            method: "post",
            url: '{{route("item-type.store")}}',
            dataType: 'json',
            contentType: false,
            processData: false,
            cache: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: formData,
            success: function(data) {
                if (data['successCode'] == 1) {
                    iziToast.show({
                        title: 'Success',
                        position: 'topRight',
                        color: 'green',
                        message: 'Item Type'
                    });

                    $(':input', '#addItemTypeForm')
                        .not(
                            ':button, :submit, :reset, :hidden'
                        )
                        .val('')
                        .prop('checked', false)
                        .prop('selected', false);

                    $('#item_type_category').val(null)
                        .trigger('change');
                    $('#item_type_sub_category').val(null)
                        .trigger('change');
                    $('#addItemTypeModal').modal('hide');
                }

                $('#itemTypesTable').DataTable().ajax
                    .reload(null, false);
            }
        });

    });

    $("#addItemForm").submit(function(e) {
        // Prevent Default functionality
        e.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            method: "post",
            url: '{{route("item.store")}}',
            dataType: 'json',
            contentType: false,
            processData: false,
            cache: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: formData,
            success: function(data) {
                if (data['successCode'] == 1) {
                    iziToast.show({
                        title: 'Success',
                        position: 'topRight',
                        color: 'green',
                        message: 'Item Added'
                    });

                    $(':input', '#addItemForm')
                        .not(
                            ':button, :submit, :reset, :hidden'
                        )
                        .val('')
                        .prop('checked', false)
                        .prop('selected', false);

                    $('#item_category').val(null).trigger(
                        'change');
                    $('#item_sub_category').val(null)
                        .trigger('change');
                    $('#item_type').val(null).trigger(
                        'change');
                }

                $('#addItemModal').modal('hide');

                $('#itemsTable').DataTable().ajax.reload(
                    null, false);


            }
        });

    });

    $("#editItemForm").submit(function(e) {
        // Prevent Default functionality
        e.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            method: "post",
            url: '{{route("item.edit")}}',
            dataType: 'json',
            contentType: false,
            processData: false,
            cache: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: formData,
            success: function(data) {
                if (data['successCode'] == 1) {
                    iziToast.show({
                        title: 'Success',
                        position: 'topRight',
                        color: 'green',
                        message: 'Item Updated'
                    });

                    $(':input', '#addItemForm')
                        .not(
                            ':button, :submit, :reset, :hidden'
                        )
                        .val('')
                        .prop('checked', false)
                        .prop('selected', false);

                    $('#item_category').val(null).trigger(
                        'change');
                    $('#item_sub_category').val(null)
                        .trigger('change');
                    $('#item_type').val(null).trigger(
                        'change');
                }

                $('#editItemModal').modal('hide');

                $('#itemsTable').DataTable().ajax.reload(
                    null, false);


            }
        });

    });

    $("#editCategoryForm").submit(function(e) {
        // Prevent Default functionality
        e.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            method: "post",
            url: '{{route("categories.update")}}',
            dataType: 'json',
            contentType: false,
            processData: false,
            cache: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: formData,
            success: function(data) {
                if (data['successCode'] == 1) {
                    iziToast.show({
                        title: 'Success',
                        position: 'topRight',
                        color: 'green',
                        message: 'Category Updated'
                    });

                    $(':input', '#editCategoryForm')
                        .not(
                            ':button, :submit, :reset, :hidden'
                        )
                        .val('')
                        .prop('checked', false)
                        .prop('selected', false);

                }

                $('#editCategoryModal').modal('hide');

                $('#categoriesTable').DataTable().ajax
                    .reload(null, false);
            }
        });

    });

    $("#editSubCategoryForm").submit(function(e) {
        // Prevent Default functionality
        e.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            method: "post",
            url: '{{route("subcategories.update")}}',
            dataType: 'json',
            contentType: false,
            processData: false,
            cache: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: formData,
            success: function(data) {

                if (data['successCode'] == 1) {
                    iziToast.show({
                        title: 'Success',
                        position: 'topRight',
                        color: 'green',
                        message: 'Subcategory Updated'
                    });

                    $(':input', '#editSubCategoryForm')
                        .not(
                            ':button, :submit, :reset, :hidden'
                        )
                        .val('')
                        .prop('checked', false)
                        .prop('selected', false);


                }

                $('#editSubCategoryModal').modal('hide');

                $('#subCategoriesTable').DataTable().ajax
                    .reload(null, false);


            }
        });

    });

    $("#editItemTypeForm").submit(function(e) {
        // Prevent Default functionality
        e.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            method: "post",
            url: '{{route("item.update")}}',
            dataType: 'json',
            contentType: false,
            processData: false,
            cache: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: formData,
            success: function(data) {

                if (data['successCode'] == 1) {
                    iziToast.show({
                        title: 'Success',
                        position: 'topRight',
                        color: 'green',
                        message: 'Item Type Updated'
                    });

                    $(':input', '#editItemTypeForm')
                        .not(
                            ':button, :submit, :reset, :hidden'
                        )
                        .val('')
                        .prop('checked', false)
                        .prop('selected', false);

                }

                $('#editItemTypeModal').modal('hide');

                $('#itemTypesTable').DataTable().ajax
                    .reload(null, false);

            }
        });

    });


    var subcatTasksTable = $('#subcatTasksTable').DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        order: [],
        dom: "<'row'<'col-sm-12'>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        ajax: {
            url: "{{ route('predefined.tasks') }}",
            data: function(d) {
                d.item = $('#filterSubcatItem').val();
                d.subject = $('#filterSubcatSubject').val();
                d.description = $('#filterSubcatDesc').val();
            }
        },
        columns: [{
                data: 'taskId',
                name: 'taskId',
                className: 'text-font-0'
            },
            {
                data: 'category',
                name: 'category',
                className: 'text-font-0'
            },
            {
                data: 'subcategory',
                name: 'subcategory',
                className: 'text-font-0'
            },
            {
                data: 'itemType',
                name: 'itemType',
                className: 'text-font-0'
            },
            {
                data: 'item',
                name: 'item',
                className: 'text-font-0'
            },
            {
                data: 'subject',
                name: 'subject',
                className: 'text-font-0'
            },
            {
                data: 'description',
                name: 'description',
                className: 'text-font-0'
            },
            {
                data: 'status',
                name: 'status',
                className: 'text-font-0'
            },
            {
                data: 'action',
                name: 'action',
                orderable: true,
                searchable: false,
                className: 'text-center'
            },
        ]
    }).on('error.dt', function(e, settings, techNote, message) {
        console.log('DataTables error: ', message);
        // Prevent default alert behavior
        e.preventDefault();
    });


    $("#editsubcatTask").submit(function(e) {
        // Prevent Default functionality
        e.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            method: "post",
            url: '{{route("predefined.task.update")}}',
            dataType: 'json',
            contentType: false,
            processData: false,
            cache: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: formData,
            success: function(data) {

                if (data['successCode'] == 1) {
                    iziToast.show({
                        title: 'Success',
                        position: 'topRight',
                        color: 'green',
                        message: 'Task Updated'
                    });

                    $(':input', '#editsubcatTask')
                        .not(
                            ':button, :submit, :reset, :hidden'
                        )
                        .val('')
                        .prop('checked', false)
                        .prop('selected', false);
                }

                $('#editsubcatTaskModal').modal('hide');

                $('#subcatTasksTable').DataTable().ajax
                    .reload(null, false);

            }
        });

    });


    $('#categoryFilter').on('change', function(e, value) {

        var categoryId = $(this).val(); // Get the selected category ID

        // Make the AJAX request for subcategories based on the selected category
        $.ajax({
            url: '{{ route("subcategories.get") }}',
            dataType: 'json',
            delay: 250,
            data: {
                categoryId: categoryId,
            },
            success: function(response) {

                // Assuming the response is an array of subcategory objects
                var subcategories =
                    response; // Adjust this based on your actual response structure

                // Clear previous content if needed
                $('#sub_category_task_add').empty();

                $('#sub_category_task_add').append(
                    '<option value="">Select a Subcategory</option>'
                );

                // Append each subcategory to the subcategoryAssign element
                subcategories.forEach(function(
                    subcategory) {
                    $('#sub_category_task_add')
                        .append(
                            '<option value="' +
                            subcategory
                            .subCategoryId + '">' +
                            subcategory
                            .subCategoryName +
                            '</option>');
                });

                $('#sub_category_task_add').val(value);
            },
            error: function(xhr, status, error) {
                // Handle errors here
                console.error(xhr, status, error);
            }
        });
    });


});

function getCategInfo(categoryId) {
    $.ajax({
        method: "post",
        url: '{{route("category.get")}}',
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        data: {
            categoryId: categoryId
        },
        success: function(data) {

            $("#edit_category_id").val(data[0]?.categoryId);
            $("#edit_category_name").val(data[0]?.categoryName);
            $("#edit_category_status").val(data[0]?.isActive);
            $("#editCategoryModal").modal("show");


        }
    });
}

function getSubCategInfo(subCategoryId) {
    $.ajax({
        method: "post",
        url: '{{route("subcategory.get")}}',
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        data: {
            subCategoryId: subCategoryId
        },
        success: function(data) {

            $("#edit_sub_category_id").val(data[0]?.subCategoryId);
            $('#categoryEdit').val(data[0]?.categoryId);
            $("#edit_sub_category_name").val(data[0]?.subCategoryName);
            $("#edit_sub_category_category").val(data[0]?.categoryId);
            $("#edit_sub_category_status").val(data[0]?.isActive);
            $("#edit_sla").val(data[0]?.sla);
            $("#editSubCategoryModal").modal("show");


        }
    });

}

function getItemInfo(itemTypeId) {
    $.ajax({
        method: "post",
        url: '{{route("item.get")}}',
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        data: {
            itemTypeId: itemTypeId
        },
        success: function(data) {

            $("#edit_item_type_id").val(data[0]?.itemTypeId);
            $("#edit_item_type_name").val(data[0]?.itemTypeName);
            loadCategoryData(data[0]?.categoryId);
            loadSubcategoryData(data[0]?.categoryId, data[0]
                ?.subCategoryId);
            $('#edit_item_type_category').on('change', function() {
                var categoryId = $(this).val();
                loadSubcategoryData(categoryId, '');
            });
            $("#edit_item_type_status").val(data[0]?.isActive);

            $("#editItemTypeModal").modal("show");


        }
    });

}

function getSubItemInfo(itemId) {
    $.ajax({
        method: "post",
        url: '{{route("subitem.get")}}',
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        data: {
            itemId: itemId
        },
        success: function(data) {

            $("#edit_item_id").val(data[0]?.itemId);
            $("#edit_item_name").val(data[0]?.itemName);

            $("#edit_item_category").val(data[0]?.categoryId);
            loadCategoryItemData(data[0]?.categoryId);

            loadSubcategoryItemData(data[0]?.categoryId, data[0]?.subCategoryId);

            loadItemTypeData(data[0]?.subCategoryId, data[0]?.itemTypeId);
            $("#edit_item_status").val(data[0]?.isActive);

            $("#editItemModal").modal("show");

        }
    });
}

function getSubTaskInfo(subTaskId) {
    $.ajax({
        method: "post",
        url: '{{route("subTaskInfo.info")}}',
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        data: {
            subTaskId: subTaskId
        },
        success: function(data) {

            $("#sub_category_task_category_edit").val(data[0]?.category);
            loadCategoryTaskData(data[0]?.category);

            $("#edit_taskId").val(data[0]?.taskId);
            loadSubcategoryTaskData(data[0]?.subcategory);

            $("#sub_category_task_itemType_edit").val(data[0]?.itemType);
            loadItemTypeTaskData(data[0]?.itemType, data[0]?.subcategory);

            $("#sub_category_task_item_edit").val(data[0]?.item);
            loadItemTaskData(data[0]?.item, data[0]?.itemType);

            $("#subject_task_edit").val(data[0]?.subject);
            $("#description_task_edit").val(data[0]?.description);
            $("#status_task_edit").val(data[0]?.status);

            $("#editsubcatTaskModal").modal("show");


        }
    });


}

function loadCategoryData(value) {
    $.ajax({
        url: '{{ route("categories.get") }}',
        dataType: 'json',
        success: function(data) {
            var options = data.map(function(category) {
                return '<option value="' + category.categoryId +
                    '">' + category
                    .categoryName +
                    '</option>';
            });

            // Populate the category dropdown
            $('#edit_item_type_category').html(options);
            $('#edit_item_type_category').val(value);
        },
        error: function(error) {
            console.error('Error loading category data:', error);
        }
    });
}

function loadSubcategoryData(categoryId, value) {
    $.ajax({
        url: '{{ route("subcategories.get") }}',
        dataType: 'json',
        data: {
            categoryId: categoryId,
            type: 'public'
        },
        success: function(data) {
            var options = data.map(function(subcategory) {
                return '<option value="' + subcategory
                    .subCategoryId + '">' +
                    subcategory
                    .subCategoryName + '</option>';
            });

            // Populate the subcategory dropdown
            $('#edit_item_type_sub_category').html(options);

            // Set selected value
            $('#edit_item_type_sub_category').val(value);
        },
        error: function(error) {
            console.error('Error loading subcategory data:', error);
        }
    });
}

function loadSubcategoryTaskData(value) {
    $.ajax({
        url: '{{ route("subcategories.get") }}',
        dataType: 'json',
        success: function(data) {
            var options = data.map(function(subcategory) {
                return '<option value="' + subcategory
                    .subCategoryId + '">' +
                    subcategory
                    .subCategoryName + '</option>';
            });

            // Populate the subcategory dropdown
            $('#sub_category_task_edit').html(options);

            // Set selected value
            $('#sub_category_task_edit').val(value);
        },
        error: function(error) {
            console.error('Error loading subcategory data:', error);
        }
    });
}

function loadCategoryTaskData(value) {
    $.ajax({
        url: '{{ route("categories.get") }}',
        dataType: 'json',
        success: function(data) {
            var options = data.map(function(category) {
                return '<option value="' + category.categoryId +
                    '">' + category
                    .categoryName +
                    '</option>';
            });

            // Populate the category dropdown
            $('#sub_category_task_category_edit').html(options);
            $('#sub_category_task_category_edit').val(value);
        },
        error: function(error) {
            console.error('Error loading category data:', error);
        }
    });
}

function loadItemTypeTaskData(value, subcategoryId) {
    // Initialize Select2 for the item dropdown
    $.ajax({
        url: '{{ route("items.get") }}',
        dataType: 'json',
        success: function(data) {
            // Map the data to create the options for the Select2 dropdown
            var options = data.map(function(item) {
                return '<option value="' + item.itemTypeId + '">' +
                    item.itemTypeName +
                    '</option>';

            });

            // Populate the subcategory dropdown
            $('#sub_category_task_itemType_edit').html(options);


            // Optionally, set a selected value if needed
            $('#sub_category_task_itemType_edit').val(value);
        },
        error: function(error) {
            console.error('Error loading item type data:', error);
        }
    });

}

function loadItemTaskData(value, itemTypeId) {
    // Initialize Select2 for the item dropdown
    $.ajax({
        url: '{{ route("subitems.get") }}',
        dataType: 'json',
        success: function(data) {
            // Map the data to create the options for the Select2 dropdown
            var options = data.map(function(item) {
                return '<option value="' + item.itemId + '">' + item
                    .itemName +
                    '</option>';

            });

            // Populate the subcategory dropdown
            $('#sub_category_task_item_edit').html(options);

            // Optionally, set a selected value if needed
            $('#sub_category_task_item_edit').val(value);

        },
        error: function(error) {
            console.error('Error loading item type data:', error);
        }
    });

}

function loadCategoryItemData(value) {
    $.ajax({
        url: '{{ route("categories.get") }}',
        dataType: 'json',
        success: function(data) {
            var options = data.map(function(category) {
                return '<option value="' + category.categoryId +
                    '">' + category
                    .categoryName +
                    '</option>';
            });

            // Populate the category dropdown
            $('#edit_item_category').html(options);
            $('#edit_item_category').val(value);
        },
        error: function(error) {
            console.error('Error loading category data:', error);
        }
    });
}

function loadSubcategoryItemData(categoryId, value) {
    $.ajax({
        url: '{{ route("subcategories.get") }}',
        dataType: 'json',
        data: {
            categoryId: categoryId,
            type: 'public'
        },
        success: function(data) {
            var options = data.map(function(subcategory) {
                return '<option value="' + subcategory
                    .subCategoryId + '">' +
                    subcategory
                    .subCategoryName + '</option>';
            });

            // Populate the subcategory dropdown
            $('#edit_item_sub_category').html(options);

            // Set selected value
            $('#edit_item_sub_category').val(value);
        },
        error: function(error) {
            console.error('Error loading subcategory data:', error);
        }
    });
}


function loadItemTypeData(subCategoryId, value) {
    // Initialize Select2 for the item dropdown
    $.ajax({
        url: '{{ route("items.get") }}',
        dataType: 'json',
        data: {
            subcategoryId: subCategoryId,
            type: 'public'
        },
        success: function(data) {
            // Map the data to create the options for the Select2 dropdown
            var options = data.map(function(item) {
                return '<option value="' + item.itemTypeId + '">' +
                    item.itemTypeName +
                    '</option>';

            });

            // Populate the subcategory dropdown
            $('#edit_item_item_type').html(options);


            // Optionally, set a selected value if needed
            $('#edit_item_item_type').val(value);
        },
        error: function(error) {
            console.error('Error loading item type data:', error);
        }
    });

}

function loadCategories() {
    $.ajax({
        url: '{{ route("categories.get") }}',
        dataType: 'json',
        delay: 250,
        data: function(params) {
            return {
                search: params.term,
                type: 'public'
            };
        },
        success: function(response) {

            // Assuming the response is an array of category objects
            var categories =
                response // Adjust this based on your actual response structure

            // Clear previous content if needed
            $('#category').empty();

            $('#category').append(
                '<option value="">Select a Category</option>');
            $('#categoryEdit').append(
                '<option value="">Select a Category</option>');
            $('#categoryFilterSub').append(
                '<option value="">Select a Category</option>');
            $('#categoryFilterIt').append(
                '<option value="">Select a Category</option>');
            $('#categoryFilterItem').append(
                '<option value="">Select a Category</option>');

            // Append each category to the category element
            categories.forEach(function(category) {

                $('#category').append('<option value="' + category
                    .categoryId + '">' +
                    category
                    .categoryName + '</option>');
                $('#categoryEdit').append('<option value="' +
                    category.categoryId +
                    '">' + category
                    .categoryName + '</option>');
                $('#categoryFilterSub').append('<option value="' +
                    category.categoryId +
                    '">' +
                    category.categoryName + '</option>');
                // $('#categoryFilterIt').append('<option value="' + category.categoryId + '">' +
                //     category.categoryName + '</option>');
                $('#categoryFilterItem').append('<option value="' +
                    category
                    .categoryId + '">' +
                    category.categoryName + '</option>');
            });




        },
        error: function(xhr, status, error) {
            // Handle errors here
            console.error(xhr, status, error);
        }
    });

    $.ajax({
        url: '{{ route("subcategories.get") }}',
        dataType: 'json',
        delay: 250,
        data: {
            categoryId: $('#categoryFilterItem').val(),
        },
        success: function(response) {

            // Assuming the response is an array of subcategory objects
            var subcategories =
                response; // Adjust this based on your actual response structure

            // Clear previous content if needed
            $('#subcategoryFilterIt').empty();

            $('#subcategoryFilterIt').append(
                '<option value="">Select a Subcategory</option>');
            $('#subcategoryFilterItem').append(
                '<option value="">Select a Subcategory</option>');

            // Append each subcategory to the subcategoryFilterIt element
            subcategories.forEach(function(subcategory) {
                // $('#subcategoryFilterIt').append('<option value="' + subcategory.subCategoryId +
                //     '">' + subcategory.subCategoryName + '</option>');
                // $('#subcategoryFilterItem').append('<option value="' + subcategory.subCategoryId +
                //     '">' + subcategory.subCategoryName + '</option>');
            });


        },
        error: function(xhr, status, error) {
            // Handle errors here
            console.error(xhr, status, error);
        }
    });

    // Make the AJAX request for items based on the selected subcategory
    $.ajax({
        url: '{{ route("items.get") }}',
        dataType: 'json',
        delay: 250,
        data: {
            subcategoryId: $('#subcategoryFilterItem').val(),
            categoryId: $('#categoryFilterItem').val(),
        },
        success: function(data) {
            // Clear previous content if needed
            $('#itemTypeFilterItem').empty();

            // Add a placeholder option
            $('#itemTypeFilterItem').append(
                '<option value="">Item Type</option>');

            // Append each item option to the item selection
            // data.forEach(function(item) {
            //     $('#itemTypeFilterItem').append('<option value="' + item.itemTypeId + '">' + item
            //         .itemTypeName + '</option>');
            // });

        },
        error: function(xhr, status, error) {
            // Handle errors here
            console.error(xhr, status, error);
        }
    });
}
</script>

<script>
function enforceAlphanumeric(inputElement) {
    inputElement.addEventListener('input', function() {
        // Remove non-alphanumeric characters
        this.value = this.value.replace(/[^a-z0-9]/gi, '');
    });
}

// Apply the function to the desired input fields
const categoryInput = document.getElementById('category_name');
const categoryInputEdit = document.getElementById('edit_category_name');
// enforceAlphanumeric(categoryInputEdit);
// enforceAlphanumeric(categoryInput);
</script>
<script src="{{asset('public/dist/js/category.js')}}"></script>
@endsection