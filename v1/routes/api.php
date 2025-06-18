<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ItemController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\TicketController;
use App\Http\Controllers\API\SubItemController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ItemTypeController;
use App\Http\Controllers\API\DepartmentController;
use App\Http\Controllers\API\SubCategoryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get('/generate-token',[TicketController::class, 'getSatvataAccessToken']); 
Route::get('/generate-iskcon-token',[TicketController::class, 'getAccessToken']); 

Route::middleware(['auth.accessKey'])->group(function () {

    Route::post('/get-departments', [DepartmentController::class, 'getDepartments']);

    Route::post('/add-technician', [UserController::class, 'addNewUser']);
    Route::post('/get-tickets', [TicketController::class, 'getTickets']);
    Route::post('/get-ticket', [TicketController::class, 'getTicket']);
    Route::post('/store-ticket', [TicketController::class, 'storeTicket']);
    Route::post('/update-ticket', [TicketController::class, 'updateTicket']);
    Route::post('/assign-ticket', [TicketController::class, 'assignTicket']);
    Route::post('/get-attachments', [TicketController::class, 'getAttachments']);
    Route::post('/remove-attachment', [TicketController::class, 'removeAttachment']);
    Route::post('/status-update', [TicketController::class, 'statusUpdate']);
    Route::post('/categorize-ticket', [TicketController::class, 'categorizeTicket']);
    Route::post('/close-ticket', [TicketController::class, 'closeTicket']);
    Route::post('/reopen-ticket', [TicketController::class, 'reopenTicket']);
    Route::post('/get-updates', [TicketController::class, 'getUpdates']);
    Route::post('/update-task', [TicketController::class, 'updateTask']);
    
    Route::post('/get-predefined-tasks',[TicketController::class, 'getPredefinedTasks']);
    Route::post('/get-predefined-task',[TicketController::class, 'getPredefinedTask']);
    Route::post('/update-predefined-task',[TicketController::class, 'updatePredefinedTask']);
    
    Route::post('/add-task', [TicketController::class, 'addTask']);
    Route::post('/get-tasks', [TicketController::class, 'getTasks']);

    Route::post('/get-categories', [CategoryController::class, 'getCategories']);
    Route::post('/get-category', [CategoryController::class, 'getCategory']);
    Route::post('/add-category', [CategoryController::class, 'addCategory']);
    Route::post('/get-category-details', [CategoryController::class, 'getDetails']);
    Route::post('/update-category', [CategoryController::class, 'updateCategory']);
    Route::post('/update-category-activation', [CategoryController::class, 'updateCategoryActivation']);
    Route::post('/store-predefined-tasks',[CategoryController::class, 'storePredefinedTasks']);
    Route::post('/get-templates', [CategoryController::class, 'getTemplates']);
    Route::post('update-template', [CategoryController::class, 'updateTemplate'])->name('update.template');

    Route::post('/get-subcategories', [SubCategoryController::class, 'getSubCategories']);
    Route::post('/get-subcategory-list',[SubCategoryController::class, 'getSubCategoryList']);
    Route::post('/get-subcategory', [SubCategoryController::class, 'getSubCategory']);
    Route::post('/add-subcategory', [SubCategoryController::class, 'addSubCategory']);
    Route::post('/get-subcategory-details', [SubCategoryController::class, 'getDetails']);
    Route::post('/update-subcategory', [SubCategoryController::class, 'updateSubCategory']);
    Route::post('/update-subcategory-activation', [SubCategoryController::class, 'updateSubCategoryActivation']);

    Route::post('/get-items', [ItemTypeController::class, 'getItemTypes']);
    Route::post('/get-itemTypes-list',[ItemTypeController::class, 'getItemTypesList']);
    Route::post('/get-item', [ItemTypeController::class, 'getItemType']);
    Route::post('/store-item-type', [ItemTypeController::class, 'storeItemType']);
    Route::post('/update-item-type', [ItemTypeController::class, 'updateItemType']);
    
    Route::post('/get-subitems', [SubItemController::class, 'getSubItems']);
    Route::post('/get-subitems-list', [SubItemController::class, 'getSubItemsList']);
    Route::post('/store-item', [SubItemController::class, 'storeItem']);
    Route::post('/update-item', [SubItemController::class, 'updateItem']);
    Route::post('/get-subitem', [SubItemController::class, 'getSubItem']);

    Route::post('/get-users', [UserController::class, 'getUsers']);
    Route::post('/get-teams', [UserController::class, 'getTeams']);
    Route::post('/get-ticket-types', [UserController::class, 'getTicketTypes']);

    Route::post('/get-user-teams', [UserController::class, 'getUserTeams']);

    // show time left in ISKCON Service app 
    Route::post('/sla-time-left',[TicketController::class, 'slaTimeLeft']);

    Route::post('/templates-data', [CategoryController::class, 'getTemplateData'])->name('templates.data');
    
    Route::post('/status-update-cal', [TicketController::class, 'statusUpdateCal']);

    Route::prefix('tickets')->group(function () {
    
        Route::post('/get-user-ticket-list',[TicketController::class, 'getUserTicketList']);
        Route::post('/get-my-ticket-details',[TicketController::class, 'getMyTicketDetails']);
        Route::post('/get-my-work-updates',[TicketController::class, 'getMyWorkUpdates']);
        Route::post('/insert-ticket-details',[TicketController::class, 'insertTicketDetails']);
        Route::post('/get-category-list',[TicketController::class, 'getCatgeoryList']);
        Route::post('/get-sub-category-list',[TicketController::class, 'getSubCatgeoryList']);
        Route::post('/get-item-list',[TicketController::class, 'getItemList']);
        Route::post('/get-item-type-list',[TicketController::class, 'getItemTypeList']);
        Route::post('/insert-ticket-feedback',[TicketController::class, 'insertTicketFeedback']);
        Route::post('/get-project-list',[TicketController::class, 'getProjectList']);
        Route::post('/get-departement-list',[TicketController::class, 'getDepartmentList']);
        Route::post('/get-technician-id',[TicketController::class, 'getTechnicianId']);
        Route::post('/get-ticket-details',[TicketController::class, 'getTicketDetails']);
        Route::post('/get-technician-list',[TicketController::class, 'getTechnicianList']);
        Route::post('/update-assign-ticket',[TicketController::class, 'updateAssignTicket']);
        Route::post('/get-work-updates',[TicketController::class, 'getWorkUpdates']);
        Route::post('/categorize-request',[TicketController::class, 'categorizeRequest']);
        Route::post('/update-request-closed',[TicketController::class, 'updateRequestClosed']);
        Route::post('/update-request-status',[TicketController::class, 'saveWorkUpdates']);
        Route::post('/get-ticket-list',[TicketController::class, 'getTicketList']);
        Route::post('/get-asset-required',[TicketController::class, 'getAssetRequired']);

        // Task Type List
        Route::post('/get-ticket-type',[TicketController::class, 'getTicketType']);
        // Get Ticket Ids
        Route::post('/get-ticket-ids',[TicketController::class, 'getTicketIds']);       

        // Calculate SLA
        Route::post('/calculate-sla',[TicketController::class, 'calSLA']); 

        // Calculate AGE
        Route::post('/calculate-age',[TicketController::class, 'calculateAGE']); 

        // Satvata Notification Send
        Route::post('/notification-send',[TicketController::class, 'satvataNotificationSend']); 

        // Ticket Notification Send
        Route::post('/ticket-notification-send',[TicketController::class, 'ticketNotificationSend']); 

        // Self Ticket Assign for Engineers
        Route::post('/assign-self-ticket', [TicketController::class, 'assignSelfTicket']);

        // Engineer Total Ticket Points
        Route::post('/get-engineer-points-data', [TicketController::class, 'getEngineerPointData'])->name('get.engineer.point.data');

        Route::post('/team-members', [TicketController::class, 'getTeamMembers'])->name('get.team.members');

        
      });
});