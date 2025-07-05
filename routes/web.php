<?php

use App\Models\HrApi;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Masters\ItemController;
use App\Http\Controllers\Masters\SubItemController;
use App\Http\Controllers\Masters\CategoryController;
use App\Http\Controllers\Masters\SubCategoryController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\HomeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/', fn() => redirect()->route('login'));

Route::get('login', [LoginController::class, 'showLoginPage']);
Route::post('login', [LoginController::class, 'loginAction']);

Route::get('/log-ticket', fn() => view('ticket_form'));
Route::post('/log-ticket', [TicketController::class,'logTicket'] )->name('log.ticket');


Auth::routes();

Route::middleware(['auth'])->group(function () {

Route::get('/tickets/{id?}', [TicketController::class, 'index'])->name('tickets');

    Route::middleware(['check_session_key'])->group(function () {
        
        Route::post('/store-img',[TicketController::class,'storeImage'] )->name('store.img');
        Route::delete('/delete-img/{uniqueFileId?}',[TicketController::class,'deleteImage'] )->name('delete.img');

        Route::post('/store-img-update',[TicketController::class,'storeImageUpdate'] )->name('store.img.update');
        Route::delete('/delete-img-update/{uniqueFileId?}',[TicketController::class,'deleteImageUpdate'] )->name('delete.img.update');

        Route::get('employees', [TicketController::class, 'getEmployees'])->name('employees.get');
        Route::get('departments-hr', [TicketController::class, 'getDepartments'])->name('departments.get');
        Route::post('employee', [TicketController::class, 'getEmployeeDetails'])->name('employee.get');

        Route::post('/create-ticket', [TicketController::class, 'storeTicket'])->name('tickets.create');
        Route::post('/update-ticket', [TicketController::class, 'updateTicket'])->name('tickets.update');
        Route::post('/get-ticket', [TicketController::class, 'getTicket'])->name('ticket.get');
        Route::post('/assign-ticket', [TicketController::class, 'assignTicket'])->name('tickets.assign');
        Route::get('/get-ticket-type',[TicketController::class,'getTicketType'])->name('get.ticket.type');

        Route::get('/tickets/attachments/{ticketId?}', [TicketController::class, 'getAttachments'])->name('tickets.attachments');
        Route::get('/tickets/attachments/remove/{attachmentId?}', [TicketController::class,'removeAttachment'])->name('attachment.remove');
        Route::post('/update-ticket-status', [TicketController::class, 'statusUpdateTicket'])->name('status.update');
        Route::post('/categorize-ticket', [TicketController::class, 'categorizeTicket'])->name('status.categorize');
        Route::post('/close-ticket', [TicketController::class, 'closeTicket'])->name('ticket.close');
        Route::get('/get-updates', [TicketController::class, 'getUpdates'])->name('tasks.updates');
        Route::post('/update-task', [TicketController::class, 'updateTask'])->name('task.update');
        Route::get('/predefined-tasks', [TicketController::class, 'getPredefinedTasks'])->name('predefined.tasks');

        Route::get('/my-tickets/{id?}', [TicketController::class, 'myTickets'])->name('user.tickets');
        Route::get('/all-tickets/{id?}', [TicketController::class, 'allTickets'])->name('all.tickets');
        Route::get('/assign-tickets', [TicketController::class, 'assignTickets'])->name('assign.tickets');
        Route::get('/recurring-tickets', [TicketController::class, 'recurringTicketsView'])->name('recurring.tickets.view');

        Route::get('/recurring-status',[TicketController::class,'recurringStatus'])->name('recurring.status');


        Route::get('/get-tasks', [TicketController::class, 'getTasks'])->name('tasks');
        Route::post('/create-task', [TicketController::class, 'addTask'])->name('task.add');
        Route::post('/reopen-ticket', [TicketController::class, 'reopenTicket'])->name('ticket.reopen');

        Route::post('/get-progress-options', [TicketController::class, 'getProgressOption'])->name('get.progress.option');
        Route::get('tickets-view/{ticketId?}/{ticketNumber?}/attachments/{attachmentTicketId?}', [TicketController::class, 'getAllAttachments'])->name('tickets.all.attachments');

        Route::get('tickets-view/{ticketId?}/{ticketNumber?}/ticket-updates', [TicketController::class, 'getTicketUpdates'])->name('ticket.all.updates');

        Route::get('tickets-view/{ticketId?}/{ticketNumber?}/get-time-left', [TicketController::class, 'getTimeLeft'])->name('get.time.left');

        // Calculate SLA
        Route::post('/calculate-sla',[TicketController::class, 'calSLA'])->name("calculate.sla"); 

        // Self Ticket Assign for Engineers
        Route::post('/assign-self-ticket', [TicketController::class, 'assignSelfTicket'])->name('assign.self.tickets');

        // Get User Allocated Asset Ids
        Route::get('/get-user-assets', [TicketController::class, 'getUserAssetId'])->name('get.user.assets');
        
        Route::get('/get-trusts', function(){ 

            $hrApi =   new HrApi;
            $trustResponse = $hrApi->getTrust();

            return response()->json($trustResponse['data'],200);
        
            })->name('trusts.get');

            
        Route::get('departments', function () {
            return view('departments');
        });

        Route::get('home', function () {
            return view('index');
        });

        Route::get('teams', function () {
            return view('teams');
        });

        Route::get('technicians', [UserController::class, 'index'])->name('technicians');
        Route::get('technicians-team', [UserController::class, 'getTechniciansForTeam'])->name('technicians.teams');
        Route::get('get-technicians', [UserController::class, 'getTechnicians'])->name('technicians.get');
        Route::post('technicians/get-names', [UserController::class, 'getAllUsers'])->name('get-technician-employee-names');
        Route::post('technicians/get-selected-user-details', [UserController::class, 'getUserDetails'])->name('get-selected-user-details');
        Route::post('technicians/add', [UserController::class, 'addUser'])->name('technicians.add');
        Route::post('technicians/change-status', [UserController::class, 'changeStatus'])->name('technicians.status');
        Route::post('technicians/edit', [UserController::class, 'editUser'])->name('technicians.edit');
        Route::post('technicians/role-edit', [UserController::class, 'editUserRole'])->name('technicians.role.edit');
        Route::get('teams', [UserController::class, 'getTeams'])->name('teams');
        Route::post('team-create', [UserController::class, 'storeTeam'])->name('team.create');
        Route::get('teams-teachnicians', [UserController::class, 'getTeamTeachnicians'])->name('teams-teachnicians');
        Route::post('assign-technician', [UserController::class, 'assignTechnician'])->name('teams-teachnicians.assign');
        Route::post('remove-technician', [UserController::class, 'removeTechnician'])->name('teams-teachnicians.remove');
        
        Route::post('team/sla-on', [UserController::class, 'teamSlaOn'])->name('teams.sla.on');

        Route::post('is-team-members-eligible', [UserController::class, 'isTeamMembersEligible'])->name('is-team.menbers.eligible');
        // Route::get('tickets/dept/{department}', function ($department) {
        //     session(['department_id' => $department]);
        //     return view('tickets');
        // });

        Route::get('tickets-view/{ticketId?}/{ticketNumber?}', [TicketController::class,'viewTicket'] )->name('ticket.view');

        Route::get('tickets/task', function () {
            return view('task_details');
        });

        /** Begin: Categories Page Routes */
        Route::get('get-categories', [CategoryController::class, 'getCategories'])->name('categories.get');
        Route::post('get-category', [CategoryController::class, 'getCategory'])->name('category.get');
        Route::get('categories', [CategoryController::class, 'index'])->name('categories-page');
        Route::post('categories/add', [CategoryController::class, 'add'])->name('categories.add');
        Route::post('categories/get-details', [CategoryController::class, 'getDetails'])->name('categories.get-details');
        Route::post('categories/update', [CategoryController::class, 'update'])->name('categories.update');
        Route::post('categories/update-activation', [CategoryController::class, 'updateActivation'])->name('categories.update-activation');

        Route::post('update-tasks', [CategoryController::class, 'updateTasks'])->name('update.tasks');
        
        Route::get('get-subcategories', [SubCategoryController::class, 'getSubCategories'])->name('subcategories.get');
        Route::post('get-subcategory', [SubCategoryController::class, 'getSubCategory'])->name('subcategory.get');
        Route::get('subcategories', [SubCategoryController::class, 'index'])->name('subcategories.list');
        Route::post('subcategories/add', [SubCategoryController::class, 'add'])->name('subcategories.add');
        Route::post('subcategories/get-details', [SubCategoryController::class, 'getDetails'])->name('subcategories.get-details');
        Route::post('subcategories/update', [SubCategoryController::class, 'update'])->name('subcategories.update');
        Route::post('subcategories/update-activation', [SubCategoryController::class, 'updateActivation'])->name('subcategories.update-activation');
        Route::post('subcategory/task-info', [SubCategoryController::class, 'subTaskInfo'])->name('subTaskInfo.info');
        Route::post('subcategory/task-update', [SubCategoryController::class, 'subTaskUpdate'])->name('predefined.task.update');

        Route::get('/get-items', [ItemController::class, 'getItems'])->name('items.get');
        Route::get('/items', [ItemController::class, 'index'])->name('items');
        Route::post('/store-item-type', [ItemController::class, 'storeItemType'])->name('item-type.store');
        Route::post('/item/update', [ItemController::class, 'update'])->name('item.update');
        Route::post('/get-item', [ItemController::class, 'getItem'])->name('item.get');
        Route::get('/fetch-items', [ItemController::class, 'fetchItems'])->name('get.items');

        // Get asset Id selection
        Route::get('/get-asset-selection', [ItemController::class, 'getAssetSelection'])->name('asset.selection');

        Route::get('get-subitems', [SubItemController::class, 'getSubItems'])->name('subitems.get');
        Route::get('subitems', [SubItemController::class, 'index'])->name('subitems');
        Route::post('/store-item', [SubItemController::class, 'storeItem'])->name('item.store');
        Route::post('/update-item', [SubItemController::class, 'updateItem'])->name('item.edit');
        Route::post('get-subitem', [SubItemController::class, 'getSubItem'])->name('subitem.get');

        Route::get('pending-tickets', [TicketController::class, 'pendingTickets'])->name('pending.tickets');
        Route::get('feedback-report', [TicketController::class, 'feedbackReport'])->name('feedback.report');
        
        Route::get('fetch-assigned-details/{ticketId}', [TicketController::class, 'getAssignmentDetails'])->name('assignment.details');

    });

    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');

    // Chart Graph
    Route::get('/get-chart-data', [HomeController::class, 'getChartData'])->name('get.chart.data');

    // Chart Engineer Total no of Tickets
    Route::get('/get-engineer-ticket-chart-data', [HomeController::class, 'getEngineerTicketChartData'])->name('get.engineer.ticket.chart.data');

    // Chart Ticket Types
    Route::get('/get-ticket-type-chart-data', [HomeController::class, 'getTicketTypeChartData'])->name('get.ticket.type.chart.data');

    // Chart SLA Ticket
    Route::get('/get-sla-ticket-chart-data', [HomeController::class, 'getSLATicketChartData'])->name('get.sla.ticket.chart.data');
    
    // Ticket Status Chart
    Route::get('/get-ticket-status-chart-data', [HomeController::class, 'getTicketStatusChartData'])->name('get.ticket.status.chart.data');
    
    // Feedback Report Chart
    Route::get('/get-feedback-report-chart-data', [HomeController::class, 'getFeedbackReportChartData'])->name('get.feedback.report.chart.data');

    // Department Ticket Wise Chart
    Route::get('/get-dept-ticket-chart-data', [HomeController::class, 'getDeptTicketChartData'])->name('get.dept.ticket.chart.data');
    
    // Engineer's Points Chart
    Route::get('/get-engineer-points-chart-data', [HomeController::class, 'getEngineerPointChartData'])->name('get.engineer.point.chart.data');

    // Ticket Duration Chart
    Route::get('/get-ticket-duration-chart-data', [HomeController::class, 'getTicketDurationChartData'])->name('get.ticket.duration.chart.data');

    // Log Wise Ticket Chart
    Route::get('/get-log-ticket-chart-data', [HomeController::class, 'getLogTicketChartData'])->name('get.log.ticket.chart.data');

    // Breached Points Chart
    Route::get('/get-breached-points-chart-data', [HomeController::class, 'getBreachedPointChartData'])->name('get.breached.point.chart.data');

    Route::post('/update-password', [HomeController::class, 'updatePassword'])->name('update.password');

            
    // Ticket Template 
    Route::get('ticket-template', [TemplateController::class, 'ticketTemplateIndex'])->name('ticket.template');
        // Get Ticket Template List
    Route::get('/templates-data', [TemplateController::class, 'getTemplateData'])->name('templates.data');
    // Store Ticket Template
    Route::post('/store-subcategory-task', [TemplateController::class, 'storeSubcategoryTask'])->name('subcat.task.store');
    Route::post('/get-templates', [TemplateController::class, 'getTemplates'])->name('templates.get');

    Route::post('update-template', [TemplateController::class, 'updateTemplate'])->name('update.template');
    Route::get('/template-status',[TemplateController::class,'templateStatus'])->name('template.status');

    Route::get('/task-status',[TemplateController::class,'taskStatus'])->name('task.status');

    Route::get('/get-ticket-tasks',[TemplateController::class,'getTicketTasks'])->name('get.ticket.tasks');
});

// SLA push notification for before 1h of SLA Over
Route::get('/sla-notify', [TicketController::class, 'SLANotify'])->name('sla.notify');

// Recurring Ticket
Route::get('/recurring-ticket',[TicketController::class, 'recurringTicket'])->name('recurring.ticket');

// Pending Ticket Report
Route::get('/pending-tickets-report',[TicketController::class, 'pendingTicketsReport'])->name('pending.tickets.report');

// Event Ticket closer
Route::get('/event-ticket-closer',[TicketController::class, 'eventTicketCloser'])->name('event.ticket.closer');