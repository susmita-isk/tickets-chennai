<?php

namespace App\Http\Controllers;

use App\Models\TicketsApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Ticket;
use App\Models\TaskType;
use App\Models\TicketPoints;
use App\Models\HolidayList;
use Carbon\Carbon;
use DataTables;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $api = new TicketsApi;

        $departments = $api->getDepartments();
       
        
        return view('home')->withDepartments($departments['data']??[]);
    }
    public function dashboard(Request $request)
    {
        $api = new TicketsApi;

        $response = $api->getTeams();

        return view('dashboard')->withTeams($response['data']);
    }
    
    // Get Engineer Point Chart Data
    public function getEngineerTicketChartData(Request $request)
    {
        $from_date = date('Y-m-d', strtotime($request->etFromDate));
        $to_date = date('Y-m-d', strtotime($request->etToDate));
        $teamId = $request->etTeamId;
        $isChecked = $request->isChecked;

        $hasTeam = $teamId ? true : false;
        
        $technicians = User::join('map_user_department', 'mstr_users.USER_ID', '=', 'map_user_department.USER_ID')
                        ->leftJoin('team_members', 'map_user_department.USER_ID', '=', 'team_members.TECHNICIAN')
                        ->leftJoin('team','team.TEAM_ID','=','team_members.TEAM_ID')
                        ->where('map_user_department.DEPARTMENT_ID',session('code'))   
                        ->where('team.DEPARTMENT_ID',session('code'))             
                        ->where('map_user_department.ROLE','Technician')
                        ->where('team_members.IS_ACTIVE','Y')
                        ->select('mstr_users.EMPLOYEE_ID','mstr_users.USER_NAME','team_members.TECHNICIAN') ;

        if ($hasTeam) {
            $technicians->whereIn('team.TEAM_NAME', $teamId);
        } else {
            // Get all TEAM_IDs for the current department
            $teamIds = DB::table('team')
                        ->where('DEPARTMENT_ID', session('code'))
                        ->pluck('TEAM_ID')
                        ->toArray();

            $technicians->whereIn('team.TEAM_ID', $teamIds);
        }

        $technicians = $technicians->orderBy('mstr_users.USER_NAME', 'asc')
                                    ->distinct()
                                    ->get();

        $ticketCounts = [];

        $ticketsQuery = Ticket::join('lkp_task_type', 'lkp_task_type.TASK_TYPE_ID', '=', 'ticket.TASK_TYPE_ID')
                    ->leftJoin('lkp_category','lkp_category.CATEGORY_ID','=','ticket.CATEGORY_ID')
                    ->select(
                        'TECHNICIAN_ID',
                        'lkp_task_type.DISPLAY_NAME as type',
                        'lkp_category.DISPLAY_NAME as category'
                    )
                    ->selectRaw('COUNT(*) as ticket_count')
                    ->where('PROJECT_ID', session('code'))
                    ->when($from_date && $to_date, function ($query) use($from_date,$to_date) {
                            return $query->whereDate('ticket.ASSIGNED_ON', '>=', $from_date)
                                        ->whereDate('ticket.ASSIGNED_ON', '<=', $to_date);
                        })
                    ->when($teamId, fn ($query) => $query->whereIn('ticket.TEAM_NAME', $teamId))
                    ->when($isChecked === 'Y', function ($query) {
                        return $query->where('ticket.CREATED_BY', '!=', 'Ticketadmin');
                    })
                    ->groupBy('TECHNICIAN_ID', 'lkp_task_type.DISPLAY_NAME', 'lkp_category.DISPLAY_NAME')
                    ->get();

        $technicianTickets = [];

        foreach ($ticketsQuery as $ticket) {
            $techId = $ticket->TECHNICIAN_ID;
            $type = $ticket->type ?? '';
            $category = $ticket->category ?? 'Not Categorized';

            $technicianTickets[$techId]['types'][$type]['count'] = ($technicianTickets[$techId]['types'][$type]['count'] ?? 0) + $ticket->ticket_count;
            
            $technicianTickets[$techId]['types'][$type]['categories'][] = [
                'name' => $category,
                'count' => $ticket->ticket_count,
            ];
           
            $technicianTickets[$techId]['total'] = ($technicianTickets[$techId]['total'] ?? 0) + $ticket->ticket_count;
        }

        $totalTickets = 0;

        foreach ($technicians as $tech) {
            $techId = $tech->EMPLOYEE_ID;
            $tech->ticket_count = $technicianTickets[$techId]['total'] ?? 0;
            $ticketTypes = [];
        
            if (!empty($technicianTickets[$techId]['types'])) {
                foreach ($technicianTickets[$techId]['types'] as $typeName => $typeData) {
                    $ticketTypes[] = [
                        'name' => $typeName,
                        'count' => $typeData['count'],
                        'categories' => $typeData['categories'],
                    ];
                }
            }
            $tech->setAttribute('ticket_types', $ticketTypes);
        
            $totalTickets += $tech->ticket_count;
        }
                        
        return response()->json([
            'technicians' => $technicians,
            'total_tickets' => $totalTickets
        ]);

    }    
    // Get Ticket Type Chart Data
    public function getTicketTypeChartData(Request $request)
    {
        $from_date = date('Y-m-d', strtotime($request->fromDate));
        $to_date = date('Y-m-d', strtotime($request->toDate));
        $teamId = $request->teamId;
        $isChecked = $request->isChecked;

        // List of ticket types
        $ticketTypes = TaskType::leftJoin('team','team.TEAM_ID','=','lkp_task_type.TEAM_ID')
                                ->where('ACTIVE_FLAG','Y')
                                ->where('team.DEPARTMENT_ID',session('code'))
                                ->when($teamId, fn ($query) => $query->whereIn('team.TEAM_NAME', $teamId))
                                ->orderBy('DISPLAY_NAME', 'asc')
                                ->select('lkp_task_type.TASK_TYPE_ID', 'lkp_task_type.DISPLAY_NAME')
                                ->get()
                                ->groupBy('DISPLAY_NAME') // Group by DISPLAY_NAME
                                ->map(function ($group) {
                                    return $group->map(function ($item) {
                                        return ['id' => $item->TASK_TYPE_ID]; // Map to desired ID format
                                    });
                                });

        $allTaskTypeIds = $ticketTypes->flatten()->toArray();

        $ticketTypesCounts = [];

        $ticketTypesCountsQuery = Ticket::join('lkp_task_type', 'lkp_task_type.TASK_TYPE_ID', '=', 'ticket.TASK_TYPE_ID')
                                    ->leftJoin('lkp_category','lkp_category.CATEGORY_ID','=','ticket.CATEGORY_ID')
                                    ->leftJoin('lkp_sub_category','lkp_sub_category.SUB_CATEGORY_ID','=','ticket.SUB_CATEGORY_ID')
                                    ->selectRaw('COUNT(*) as count, 
                                                lkp_task_type.DISPLAY_NAME as task_type_name,
                                                lkp_category.DISPLAY_NAME as category_name,
                                                lkp_sub_category.DISPLAY_NAME as sub_category_name')
                                    ->where('ticket.PROJECT_ID', session('code'))
                                    ->when($from_date && $to_date, function ($query) use ($from_date, $to_date) {
                                        return $query->whereDate('ticket.ASSIGNED_ON', '>=', $from_date)
                                            ->whereDate('ticket.ASSIGNED_ON', '<=', $to_date);
                                    })
                                    ->when($teamId, fn($query) => $query->whereIn('ticket.TEAM_NAME', $teamId)) 
                                    ->when($isChecked === 'Y', function ($query) {
                                                return $query->where('ticket.CREATED_BY', '!=', 'Ticketadmin');
                                            })
                                    ->whereIn('lkp_task_type.TASK_TYPE_ID', $allTaskTypeIds)
                                    ->groupBy('task_type_name', 'category_name', 'sub_category_name')
                                    ->get();

        $totalTicketsTypes = 0;
        $ticketTypesData = [];

        foreach ($ticketTypesCountsQuery as $ticket) {
            $taskType = $ticket->task_type_name ?? '';
            $category = $ticket->category_name ?? 'Not Categorized';
            $subCategory = $ticket->sub_category_name ?? 'Not Categorized';
        
            $ticketTypesData[$taskType]['count'] = ($ticketTypesData[$taskType]['count'] ?? 0) + $ticket->count;
            $ticketTypesData[$taskType]['categories'][$category]['count'] = ($ticketTypesData[$taskType]['categories'][$category]['count'] ?? 0) + $ticket->count;
            $ticketTypesData[$taskType]['categories'][$category]['sub_categories'][$subCategory] = ($ticketTypesData[$taskType]['categories'][$category]['sub_categories'][$subCategory] ?? 0) + $ticket->count;
        
            $totalTicketsTypes += $ticket->count;
        }

        return response()->json([ 
            'ticketTypesHierarchy' => $ticketTypesData,
            'totalTicketsTypes'=> $totalTicketsTypes
        ]);
        
    }
    
    // Get Ticket Status Chart
    public function getTicketStatusChartData(Request $request)
    {
        $from_date = date('Y-m-d', strtotime($request->fromDate));
        $to_date = date('Y-m-d', strtotime($request->toDate));
        $teamId = $request->teamId;
        $isChecked = $request->isChecked;
        
        // List of Ticket Status
        $ticketStatus = DB::table('lkp_progress')
                            ->distinct()
                            ->pluck('PROGRESS') // Extract only the STATUS column as a flat array                            
                            ->toArray();

        $ticketCounts = Ticket::leftJoin('lkp_category','lkp_category.CATEGORY_ID','=','ticket.CATEGORY_ID')
                        ->leftJoin('lkp_sub_category','lkp_sub_category.SUB_CATEGORY_ID','=','ticket.SUB_CATEGORY_ID')
                        ->when($from_date && $to_date, function ($query) use ($from_date, $to_date) {
                            return $query->whereDate('ticket.CREATED_ON', '>=', $from_date)
                                ->whereDate('ticket.CREATED_ON', '<=', $to_date);
                        })
                        ->when($teamId, function ($query) use ($teamId) {
                            return $query->whereIn('ticket.TEAM_NAME', $teamId);
                        })
                        ->when($isChecked === 'Y', function ($query) {
                            return $query->where('ticket.CREATED_BY', '!=', 'Ticketadmin');
                        })
                        ->where('PROJECT_ID', session('code'))
                        ->selectRaw('LOWER(ticket.PROGRESS) as progress, 
                            lkp_category.DISPLAY_NAME as category, 
                            lkp_sub_category.DISPLAY_NAME as sub_category, 
                            COUNT(*) as count')
                        ->groupBy('progress', 'category', 'sub_category')
                        ->get();

        $statusDrilldown = [];

        $totalTickets = 0;
    
        foreach ($ticketCounts as $ticket) {
            $status = ucfirst($ticket->progress); // Original case for chart label
            $category = $ticket->category ?? 'Not Categorized';
            $subCategory = $ticket->sub_category ?? 'Not Categorized';
        
            $totalTickets += $ticket->count;

            if (!isset($statusDrilldown[$status]['categories'][$category])) {
                $statusDrilldown[$status]['categories'][$category] = [
                    'count' => 0,
                    'sub_categories' => []
                ];
            }    
            
            $statusDrilldown[$status]['count'] = ($statusDrilldown[$status]['count'] ?? 0) + $ticket->count;
            $statusDrilldown[$status]['categories'][$category]['count'] = ($statusDrilldown[$status]['categories'][$category]['count'] ?? 0) + $ticket->count;
            $statusDrilldown[$status]['categories'][$category]['sub_categories'][$subCategory] = ($statusDrilldown[$status]['categories'][$category]['sub_categories'][$subCategory] ?? 0) + $ticket->count;
        }
        
        
        return response()->json([ 
            'drilldown' => $statusDrilldown,
            'totalTicketsStatus' => $totalTickets
        ]);       
     
    }
    // Get Feedback Report Chart Data
    public function getFeedbackReportChartData(Request $request)
    {
        $from_date = date('Y-m-d', strtotime($request->fromDate));
        $to_date = date('Y-m-d', strtotime($request->toDate));
        $teamId = $request->teamId;
        $isChecked = $request->isChecked;

        $hasTeam = $teamId ? true : false;
        
        $technicians = User::join('map_user_department', 'mstr_users.USER_ID', '=', 'map_user_department.USER_ID')
                ->leftJoin('team_members', 'map_user_department.USER_ID', '=', 'team_members.TECHNICIAN')
                ->leftJoin('team','team.TEAM_ID','=','team_members.TEAM_ID')
                ->where('map_user_department.DEPARTMENT_ID',session('code'))  
                ->where('team.DEPARTMENT_ID',session('code'))               
                ->where('map_user_department.ROLE','Technician')
                ->where('team_members.IS_ACTIVE','Y')
                ->select('mstr_users.EMPLOYEE_ID','mstr_users.USER_NAME','team_members.TECHNICIAN') ;

        if ($hasTeam) {
            $technicians->whereIn('team.TEAM_NAME', $teamId);
        } else {
            $teamIds = DB::table('team')
                ->where('DEPARTMENT_ID', session('code'))
                ->pluck('TEAM_ID')
                ->toArray();

            $technicians->whereIn('team.TEAM_ID', $teamIds);
        }

        $technicians = $technicians->orderBy('mstr_users.USER_NAME', 'asc')
                                    ->distinct()
                                    ->get();

        $tickets = Ticket::query()
                    ->when($from_date && $to_date, function ($query) use ($from_date, $to_date) {
                        return $query->whereDate('ticket.FEEDBACK_ON', '>=', $from_date)
                                    ->whereDate('ticket.FEEDBACK_ON', '<=', $to_date);
                    })
                    ->when($teamId, function ($query) use ($teamId) {
                        return $query->whereIn('ticket.TEAM_NAME', $teamId);
                    })
                    ->when($isChecked === 'Y', function ($query) {
                        return $query->where('ticket.CREATED_BY', '!=', 'Ticketadmin');
                    })
                    ->where('PROJECT_ID', session('code'))
                    ->select('TECHNICIAN_ID', 'FEEDBACK_POINT', 'FEEDBACK_ON')
                    ->get()
                    ->groupBy('TECHNICIAN_ID');

        $feedbackData = $technicians->map(function ($technician) use ($tickets) {
            $technicianTickets = $tickets->get($technician['EMPLOYEE_ID'], collect());

            $feedbackPoints = $technicianTickets->sum('FEEDBACK_POINT');
            $noOfUserFeedback = $technicianTickets->whereNotNull('FEEDBACK_ON')->count();

            $feedbackSummary = $technicianTickets->groupBy('FEEDBACK_POINT')->map(function ($group, $point) {
                return [
                    'star' => $point,
                    'ticket' => $group->count(),
                ];
            });
    
            return [
                'EMPLOYEE_ID' => $technician['EMPLOYEE_ID'],
                'USER_NAME' => $technician['USER_NAME'],
                'feedbackPoints' => $feedbackPoints,
                'noOfUserFeedback' => $noOfUserFeedback,
                'feedbackSummary' => $feedbackSummary,
            ];
        });

        $totalStarTickets = [
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
        ];

        // Calculate totals across all technicians
        foreach ($feedbackData as $technician) {
            foreach ($technician['feedbackSummary'] as $star => $summary) {
                $totalStarTickets[$star] += $summary['ticket'];
            }
        }

        $totalFeedbacks = $feedbackData->sum('noOfUserFeedback'); // Total feedback points across all technicians
        return response()->json([
            'technicians' => $feedbackData->toArray(),
            'totalFeedbacks' => $totalFeedbacks,
            'totalStarTickets' => $totalStarTickets,
        ]);  
    }
    // Get Department Ticket Chart Data
    public function getDeptTicketChartData(Request $request)
    {
        $from_date = date('Y-m-d', strtotime($request->fromDate));
        $to_date = date('Y-m-d', strtotime($request->toDate));
        $teamId = $request->teamId;
        $isChecked = $request->isChecked;

        // To 10 Department from Ticket Table
        $top10Departments = Ticket::leftJoin('lkp_task_type', 'lkp_task_type.TASK_TYPE_ID', '=', 'ticket.TASK_TYPE_ID')
                                ->leftJoin('lkp_category','lkp_category.CATEGORY_ID','=','ticket.CATEGORY_ID')
                                // ->select('DEPARTMENT_NAME', DB::raw('COUNT(*) AS ticket_count'))
                                ->selectRaw(
                                    'ticket.DEPARTMENT_NAME as department,
                                    lkp_task_type.DISPLAY_NAME as task_type,
                                    lkp_category.DISPLAY_NAME as category,
                                    COUNT(*) as count'
                                )
                                ->whereNotNull('DEPARTMENT_NAME')
                                ->where('PROJECT_ID', session('code'))                            
                                ->when($from_date && $to_date, function ($query) use($from_date,$to_date) {
                                    return $query->whereDate('ticket.CREATED_ON', '>=', $from_date)
                                                ->whereDate('ticket.CREATED_ON', '<=', $to_date);
                                })
                                ->when($teamId, fn ($query) => $query->whereIn('ticket.TEAM_NAME', $teamId))
                                ->when($isChecked === 'Y', function ($query) {
                                    return $query->where('ticket.CREATED_BY', '!=', 'Ticketadmin');
                                })
                                ->groupBy('department','task_type','category')
                                // ->orderBy('ticket_count', 'DESC')
                                // ->limit(10)
                                ->get();

        $deptData = [];
        $totalTickets = 0;

        foreach ($top10Departments as $dept) {
            $d = $dept->department;
            $tt = $dept->task_type ?? 'Unknown Type';
            $c = $dept->category ?? 'Not Categorized';
            $count = $dept->count;

            $totalTickets += $dept->count;
        
            $deptData[$d]['total'] = ($deptData[$d]['total'] ?? 0) + $count;
            $deptData[$d]['types'][$tt]['total'] = ($deptData[$d]['types'][$tt]['total'] ?? 0) + $count;
            $deptData[$d]['types'][$tt]['categories'][$c] = ($deptData[$d]['types'][$tt]['categories'][$c] ?? 0) + $count;
        }

        uasort($deptData, fn($a,$b)=> $b['total'] - $a['total']);
        $deptData = array_slice($deptData, 0, 10, true);

        return response()->json([
            'totalTicketsDept'=>$totalTickets,
            'departmentHierarchy' => $deptData
        ]);
    }
    // Get Engineer Point Chart
    public function getEngineerPointChartData(Request $request)
    {
        $from_date = date('Y-m-d', strtotime($request->fromDate));
        $to_date = date('Y-m-d', strtotime($request->toDate));
        $teamId = $request->teamId;
        $isChecked = $request->isChecked;

        $hasTeam = $teamId ? true : false;
        
        $technicians = User::join('map_user_department', 'mstr_users.USER_ID', '=', 'map_user_department.USER_ID')
                    ->leftJoin('team_members', 'map_user_department.USER_ID', '=', 'team_members.TECHNICIAN')
                    ->leftJoin('team','team.TEAM_ID','=','team_members.TEAM_ID')
                    ->where('map_user_department.DEPARTMENT_ID',session('code'))   
                    ->where('team.DEPARTMENT_ID',session('code'))            
                    ->where('map_user_department.ROLE','Technician')
                    ->where('team_members.IS_ACTIVE','Y')
                    ->select('mstr_users.EMPLOYEE_ID','mstr_users.USER_NAME','team_members.TECHNICIAN') ;

        if ($hasTeam) {
            $technicians->whereIn('team.TEAM_NAME', $teamId);
        } else {
            $teamIds = DB::table('team')
                ->where('DEPARTMENT_ID', session('code'))
                ->pluck('TEAM_ID')
                ->toArray();

            $technicians->whereIn('team.TEAM_ID', $teamIds);
        }

        $technicians = $technicians->orderBy('mstr_users.USER_NAME', 'asc')
                                    ->distinct()
                                    ->get();

        $assignedPoints = Ticket::query()
                            ->leftJoin('lkp_task_type', 'lkp_task_type.TASK_TYPE_ID', '=', 'ticket.TASK_TYPE_ID')
                            ->leftJoin('lkp_category','lkp_category.CATEGORY_ID','=','ticket.CATEGORY_ID')
                            ->select(
                                'ticket.TECHNICIAN_ID',
                                'lkp_task_type.DISPLAY_NAME as type',
                                'lkp_category.DISPLAY_NAME as category',
                                DB::raw('SUM(ticket.POINTS) as assigned_points')
                            )
                            ->where('PROJECT_ID', session('code'))
                            ->when($from_date && $to_date, function ($query) use ($from_date, $to_date) {
                                return $query->whereDate('ticket.CLOSED_ON', '>=', $from_date)
                                            ->whereDate('ticket.CLOSED_ON', '<=', $to_date);
                            })
                            ->when($teamId, function ($query) use ($teamId) {
                                return $query->whereIn('ticket.TEAM_NAME', $teamId);
                            })
                            ->when($isChecked === 'Y', function ($query) {
                                return $query->where('ticket.CREATED_BY', '!=', 'Ticketadmin');
                            })                                                        
                            ->groupBy('TECHNICIAN_ID', 'type', 'category')
                            ->get();

        $breachedPoints = DB::table('breached_tickets_points')
                            ->join('ticket', 'ticket.TICKET_ID', '=', 'breached_tickets_points.TICKET_ID')
                            ->leftJoin('lkp_task_type', 'lkp_task_type.TASK_TYPE_ID', '=', 'ticket.TASK_TYPE_ID')
                            ->leftJoin('lkp_category', 'lkp_category.CATEGORY_ID', '=', 'ticket.CATEGORY_ID')
                            // ->select('breached_tickets_points.TECHNICIAN_ID', 'ticket.TASK_TYPE_ID',
                            //      DB::raw('SUM(POINTS) as total_breached_points'))
                            ->select(
                                'breached_tickets_points.TECHNICIAN_ID',
                                'lkp_task_type.DISPLAY_NAME as type',
                                DB::raw("COALESCE(lkp_category.DISPLAY_NAME, 'Not Categorized') as category"),
                                DB::raw('SUM(breached_tickets_points.POINTS) as breached_points')
                            )
                            ->when($from_date && $to_date, function ($query) use ($from_date, $to_date) {
                                return $query->whereDate('breached_tickets_points.CREATED_ON', '>=', $from_date)
                                            ->whereDate('breached_tickets_points.CREATED_ON', '<=', $to_date);
                            })
                            ->groupBy('breached_tickets_points.TECHNICIAN_ID', 'lkp_task_type.DISPLAY_NAME', 'lkp_category.DISPLAY_NAME')
                            ->get();

        // Fetch released points in bulk
        $releasedPoints = TicketPoints::query()
                                    ->select('TECHNICIAN_ID', DB::raw('SUM(POINTS) as total_released_points'))
                                    ->when($from_date && $to_date, function ($query) use ($from_date, $to_date) {
                                        return $query->whereDate('STATUS_DATE', '>=', $from_date)
                                                    ->whereDate('STATUS_DATE', '<=', $to_date);
                                    })
                                    ->groupBy('TECHNICIAN_ID')
                                    ->get(); 

        $pointsHierarchy = [];  

        // Add assigned points to hierarchy
        foreach ($assignedPoints as $row) {
            $techId = $row->TECHNICIAN_ID;
            $type = $row->type ?? 'Unknown Type';
            $category = $row->category ?? 'Not Categorized';

            $pointsHierarchy[$techId]['total'] = 
                ($pointsHierarchy[$techId]['total'] ?? 0) + $row->assigned_points;

            $pointsHierarchy[$techId]['types'][$type]['total'] = 
                ($pointsHierarchy[$techId]['types'][$type]['total'] ?? 0) + $row->assigned_points;

            $pointsHierarchy[$techId]['types'][$type]['categories'][$category] = 
                ($pointsHierarchy[$techId]['types'][$type]['categories'][$category] ?? 0) + $row->assigned_points;
                        
        }

        // Add breached points to hierarchy
        foreach ($breachedPoints as $row) {
            $techId = $row->TECHNICIAN_ID;
            $type = $row->type ?? 'Unknown';
            $category = $row->category ?? 'Not Categorized';

            $pointsHierarchy[$techId]['types'][$type]['categories'][$category] = 
                ($pointsHierarchy[$techId]['types'][$type]['categories'][$category] ?? 0) + $row->breached_points;

            $pointsHierarchy[$techId]['types'][$type]['total'] = 
                ($pointsHierarchy[$techId]['types'][$type]['total'] ?? 0) + $row->breached_points;

            $pointsHierarchy[$techId]['total'] = 
                ($pointsHierarchy[$techId]['total'] ?? 0) + $row->breached_points;
        }
  
        $ticketPoints = [];
        $totalPoints = 0;
        
        // Combine the points and associate them with the technician's ID
        $ticketPoints = $technicians->map(function ($technician) use ($pointsHierarchy, $releasedPoints, ) {
            $techId = $technician['EMPLOYEE_ID'];
            $userName = $technician['USER_NAME'];

            $ticketTypes = [];

            $assignedTypes = $pointsHierarchy[$techId]['types'] ?? [];

            $assignedTotal = $pointsHierarchy[$techId]['total'] ?? 0;
            $releasedTotal = $releasedPoints->get($techId)->total_released_points ?? 0;
            
            foreach ($assignedTypes as $type => $data) {
                $categoryList = [];
        
                foreach ($data['categories'] as $category => $count) {
                    $categoryList[] = [
                        'name' => $category,
                        'count' => $count,
                    ];
                }
        
                $ticketTypes[] = [
                    'name' => $type,
                    'count' => $data['total'],
                    'categories' => $categoryList,
                ];
            }
            return [
                'EMPLOYEE_ID' => $techId,
                'USER_NAME' => $userName,
                'total_points' => $assignedTotal + $releasedTotal,
                'ticket_types' => $ticketTypes,
            ];
        });

        $totalPoints = $ticketPoints->sum('total_points'); // Total points across all technicians
                
        return response()->json([
            'totalTicketsPoints' => $totalPoints,
            'technicians' => $ticketPoints->values(), 
        ]);
    }
    // Get SLA Ticket Chart Data
    public function getSLATicketChartData(Request $request)
    { 
       $from_date = date('Y-m-d', strtotime($request->fromDate));
        $to_date = date('Y-m-d', strtotime($request->toDate));
        $teamId = $request->teamId;
        $isChecked = $request->isChecked;

        $hasTeam = !empty($teamId);

        $tickets = Ticket::join('mstr_users', 'mstr_users.EMPLOYEE_ID', '=', 'ticket.TECHNICIAN_ID')
                        ->leftJoin('lkp_task_type', 'lkp_task_type.TASK_TYPE_ID', '=', 'ticket.TASK_TYPE_ID')
                        ->leftJoin('lkp_category','lkp_category.CATEGORY_ID','=','ticket.CATEGORY_ID')                       
                        ->leftJoin('map_user_department', 'mstr_users.USER_ID', '=', 'map_user_department.USER_ID')
                        ->leftJoin('team', 'team.TEAM_NAME', '=', 'ticket.TEAM_NAME')
                        ->where('ticket.PROJECT_ID', session('code'))
                        ->where('map_user_department.ROLE', 'Technician')
                        ->where('map_user_department.DEPARTMENT_ID',session('code'))
                        ->where('team.DEPARTMENT_ID',session('code'))
                        ->whereIn('ticket.PROGRESS', ['Open', 'In Progress', 'On Hold', 'Resolved'])
                        ->when($from_date && $to_date, function ($query) use ($from_date, $to_date) {
                            return $query->whereDate('ticket.ASSIGNED_ON', '>=', $from_date)
                                        ->whereDate('ticket.ASSIGNED_ON', '<=', $to_date);
                        })
                        ->when($hasTeam, function ($query) use ($teamId) {
                            $query->whereIn('team.TEAM_NAME', $teamId);
                        }, function ($query) {
                            $teamIds = DB::table('team')
                                ->where('DEPARTMENT_ID', session('code'))
                                ->pluck('TEAM_ID')
                                ->toArray();

                            $query->whereIn('team.TEAM_ID', $teamIds);
                        }) 
                        ->when($isChecked === 'Y', function ($query) {
                            return $query->where('ticket.CREATED_BY', '!=', 'Ticketadmin');
                        }) 
                        ->select(
                            'ticket.TICKET_ID',
                            'ticket.TICKET_NO',
                            'ticket.STATUS',
                            'ticket.IS_SLA_BREACH',
                            'mstr_users.USER_NAME as technician_name',
                            'lkp_task_type.SLA',
                            'lkp_task_type.DISPLAY_NAME as type',
                            'lkp_category.DISPLAY_NAME as category'
                        )
                        ->orderBy('mstr_users.USER_NAME', 'asc')
                        ->get();

        $totalWithinSla = 0;
        $totalSlaBreach = 0;

        $engineerCategoryWiseWithinSla = []; 
        $engineerCategoryWiseSlaBreach = []; 
                 
        foreach ($tickets as $ticket) {
            $technicianName = $ticket->technician_name;
            $ticketType = $ticket->type ?? 'Unknown Type';
            $category = $ticket->category ?? 'Not Categorized';
        
            if ($ticket->STATUS == 'Open') {
                $totalTimeConsumed = $this->getTimeLeft($ticket->TICKET_ID, $ticket->TICKET_NO);
                if ($ticket->SLA && $totalTimeConsumed > ($ticket->SLA * 60)) {
                    $totalSlaBreach++;
                    $engineerCategoryWiseSlaBreach[$technicianName][$ticketType][$category] =
                        ($engineerCategoryWiseSlaBreach[$technicianName][$ticketType][$category] ?? 0) + 1;
                } else {
                    $totalWithinSla++;
                    $engineerCategoryWiseWithinSla[$technicianName][$ticketType][$category] =
                        ($engineerCategoryWiseWithinSla[$technicianName][$ticketType][$category] ?? 0) + 1;
                }
            } else {
                if ($ticket->IS_SLA_BREACH === 'N') {
                    $totalWithinSla++;
                    $engineerCategoryWiseWithinSla[$technicianName][$ticketType][$category] =
                        ($engineerCategoryWiseWithinSla[$technicianName][$ticketType][$category] ?? 0) + 1;
                } else {
                    $totalSlaBreach++;
                    $engineerCategoryWiseSlaBreach[$technicianName][$ticketType][$category] =
                        ($engineerCategoryWiseSlaBreach[$technicianName][$ticketType][$category] ?? 0) + 1;
                }
            }
        }

        // Transform category-wise data into { count, categories } format
        $formattedWithinSla = [];
        $formattedSlaBreach = [];

        foreach ($engineerCategoryWiseWithinSla as $engineer => $types) {
            foreach ($types as $type => $categories) {
                $formattedWithinSla[$engineer][$type] = [
                    'count' => array_sum($categories),
                    'categories' => $categories
                ];
            }
        }

        foreach ($engineerCategoryWiseSlaBreach as $engineer => $types) {
            foreach ($types as $type => $categories) {
                $formattedSlaBreach[$engineer][$type] = [
                    'count' => array_sum($categories),
                    'categories' => $categories
                ];
            }
        }
               
        return response()->json([
            'totalWithinSla'=>$totalWithinSla,
            'totalSlaBreach'=>$totalSlaBreach,
            'engineerCategoryWiseWithinSla' => $formattedWithinSla,
            'engineerCategoryWiseSlaBreach' => $formattedSlaBreach,
        ]);
    }   
   
    // Get Ticket Duration Chart Data
    public function getTicketDurationChartData(Request $request)
    {
        $from_date = date('Y-m-d', strtotime($request->fromDate));
        $to_date = date('Y-m-d', strtotime($request->toDate));
        $teamId = $request->teamId;
        $isChecked = $request->isChecked;

        $allTickets = Ticket::join('lkp_task_type', 'lkp_task_type.TASK_TYPE_ID', '=', 'ticket.TASK_TYPE_ID')
                            ->join('team', 'team.TEAM_ID', '=', 'lkp_task_type.TEAM_ID')
                            ->where('ticket.PROJECT_ID', session('code'))
                            ->where('team.DEPARTMENT_ID', session('code'))
                            ->when($from_date && $to_date, function ($query) use ($from_date, $to_date) {
                                return $query->whereDate('ticket.ASSIGNED_ON', '>=', $from_date)
                                            ->whereDate('ticket.ASSIGNED_ON', '<=', $to_date);
                            })
                            ->when($teamId, fn ($q) => $q->whereIn('ticket.TEAM_NAME', $teamId))
                            ->when($isChecked === 'Y', fn ($q) => $q->where('ticket.CREATED_BY', '!=', 'Ticketadmin'))
                            ->select([
                                'ticket.TICKET_ID',
                                'ticket.TICKET_NO',
                                'lkp_task_type.TASK_TYPE_ID',
                                'lkp_task_type.SLA',
                                'lkp_task_type.DISPLAY_NAME',
                            ])
                            ->get();

        $groupedTickets = $allTickets->groupBy('DISPLAY_NAME');

        $actualTicketTime =[]; 
        $spentTime = [];   

        foreach ($groupedTickets as $displayName => $tickets) {
            // Actual SLA Time
            $totalSlaTime = $tickets->sum(fn ($t) => $t->SLA * 1); // Ticket count × SLA
            $actualTicketTime[] = $totalSlaTime;
        
            // Spent Time (if SLA > 0)
            $totalMinutes = $tickets
                        // ->filter(fn ($ticket) => $ticket->SLA > 0)
                        ->map(fn ($ticket) => $this->getTimeLeft($ticket->TICKET_ID, $ticket->TICKET_NO))
                        ->sum();
        
            $spentTime[] = floor($totalMinutes / 60);
        }

        return response()->json([
            'actualTicketTime' => $actualTicketTime,
            'spentTime' => $spentTime,
            'ticketTypes' => $groupedTickets, // optional: list of DISPLAY_NAMEs
        ]);
    }
    // public function getTicketDurationChartData(Request $request)
    // {
    //     $from_date = date('Y-m-d', strtotime($request->fromDate));
    //     $to_date = date('Y-m-d', strtotime($request->toDate));
    //     $teamId = $request->teamId;
    //     $isChecked = $request->isChecked;

    //     // List of ticket types
    //     $ticketTypes = TaskType::leftJoin('team','team.TEAM_ID','=','lkp_task_type.TEAM_ID')
    //                             ->where('ACTIVE_FLAG','Y')
    //                             ->where('team.DEPARTMENT_ID',session('code'))
    //                             ->when($teamId, fn ($query) => $query->whereIn('team.TEAM_NAME', $teamId))                                
    //                             ->orderBy('DISPLAY_NAME', 'asc')
    //                             ->select('lkp_task_type.TASK_TYPE_ID','lkp_task_type.SLA', 'lkp_task_type.DISPLAY_NAME')
    //                             ->get()
    //                             ->groupBy('DISPLAY_NAME') // Group by DISPLAY_NAME
    //                             ->map(function ($group) {
    //                                 return $group->map(function ($item) {
    //                                     return [
    //                                         'id' => $item->TASK_TYPE_ID,
    //                                         'sla' => $item->SLA,
    //                                     ];
    //                                 });
    //                             });
        
    //     $allTaskTypeIds = $ticketTypes->flatten(1)->pluck('id')->toArray();

    //     // Fetch all tickets once
    //     $allTickets = Ticket::join('lkp_task_type', 'lkp_task_type.TASK_TYPE_ID', '=', 'ticket.TASK_TYPE_ID')
    //                         ->where('ticket.PROJECT_ID', session('code'))
    //                         ->whereIn('lkp_task_type.TASK_TYPE_ID', $allTaskTypeIds)
    //                         ->when($from_date && $to_date, function ($query) use ($from_date, $to_date) {
    //                             return $query->whereDate('ticket.ASSIGNED_ON', '>=', $from_date)
    //                                         ->whereDate('ticket.ASSIGNED_ON', '<=', $to_date);
    //                         })
    //                         ->when($teamId, fn ($q) => $q->whereIn('ticket.TEAM_NAME', $teamId))
    //                         ->when($isChecked === 'Y', fn ($q) => $q->where('ticket.CREATED_BY', '!=', 'Ticketadmin'))
    //                         ->select('ticket.TICKET_ID', 
    //                             'ticket.TICKET_NO', 
    //                             'lkp_task_type.TASK_TYPE_ID', 
    //                             'lkp_task_type.SLA')
    //                         ->get();

    //     $actualTicketTime =[]; 
    //     $spentTime = [];   
        
    //     foreach ($ticketTypes as $displayName => $ids) { // Use keys (technician IDs)
    //         $taskTypeIds = collect($ids)->pluck('id')->toArray();

    //         // Actual Time for each Ticket Type
    //         $totalSlaTime = 0;
    //         foreach ($ids as $type) {
    //             $typeId = $type['id'];
    //             $sla = $type['sla'];
    //             $ticketCount = $allTickets->where('TASK_TYPE_ID', $typeId)->count();
    //             $totalSlaTime += $sla * $ticketCount;
    //         }
    //         $actualTicketTime[] = $totalSlaTime;

    //         // Total Spent time 
    //         $totalMinutes = $allTickets
    //                     ->whereIn('TASK_TYPE_ID', $taskTypeIds)
    //                     ->map(fn ($ticket) => $this->getTimeLeft($ticket->TICKET_ID, $ticket->TICKET_NO))
    //                     ->sum();

    //         $hours = floor($totalMinutes / 60);
    //         $spentTime[] = $hours;            
    //     }       
        
    //     return response()->json([
    //         'ticketTypes'=>$ticketTypes,
    //         'actualTicketTime'=>$actualTicketTime,
    //         'spentTime'=>$spentTime ?? [0]
    //     ]);
    // }
    // Get Log Wise Ticket Chart Data
    public function getLogTicketChartData(Request $request)
    {
        $from_date = date('Y-m-d', strtotime($request->fromDate));
        $to_date = date('Y-m-d', strtotime($request->toDate));
        $teamId = $request->teamId;
        $isChecked = $request->isChecked;

        $logUsers = DB::table('mstr_log_users')->select('CREATED_BY','DETAILS')->get();

        $ticketsQuery = Ticket::leftJoin('lkp_task_type', 'lkp_task_type.TASK_TYPE_ID', '=', 'ticket.TASK_TYPE_ID')
                        ->leftJoin('lkp_category','lkp_category.CATEGORY_ID','=','ticket.CATEGORY_ID')
                        ->select(
                            'ticket.TECHNICIAN_ID',
                            'ticket.CREATED_BY',
                            'lkp_task_type.DISPLAY_NAME as type',
                            'lkp_category.DISPLAY_NAME as category',
                            DB::raw('COUNT(*) as ticket_count')
                        )
                        ->where('PROJECT_ID', session('code'))
                        ->when($from_date && $to_date, function ($query) use ($from_date, $to_date) {
                            return $query->whereDate('ticket.CREATED_ON', '>=', $from_date)
                                ->whereDate('ticket.CREATED_ON', '<=', $to_date);
                        })
                        ->when($isChecked === 'Y', function ($query) {
                            return $query->where('ticket.CREATED_BY', '!=', 'Ticketadmin');
                        })
                        ->when($teamId, fn($query) => $query->whereIn('ticket.TEAM_NAME', $teamId))
                        ->groupBy('ticket.TECHNICIAN_ID', 'ticket.CREATED_BY', 'lkp_task_type.DISPLAY_NAME', 'lkp_category.DISPLAY_NAME')
                        ->get();
                        
        $ticketCounts = [];
        $knownUserTicketCount = 0;
        $matchedCreatedBys = [];

        foreach ($logUsers as $logUser) {
            $createdBy = $logUser->CREATED_BY;
            $details = $logUser->DETAILS ? explode(',', $logUser->DETAILS) : [];

            $createdByArray = array_merge([$createdBy], $details);
            $matchedCreatedBys = array_merge($matchedCreatedBys, $createdByArray);

            // Filter relevant tickets for this user
            $userTickets = $ticketsQuery->filter(fn($t) => in_array($t->CREATED_BY, $createdByArray));

            // Group ticket types and categories
            $grouped = [];
            foreach ($userTickets as $t) {
                $type = $t->type ?? 'Unknown Type';
                $category = $t->category ?? 'Not Categorized';

                if (!isset($grouped[$type]['total'])) $grouped[$type]['total'] = 0;
                if (!isset($grouped[$type]['categories'][$category])) $grouped[$type]['categories'][$category] = 0;

                $grouped[$type]['total'] += $t->ticket_count;
                $grouped[$type]['categories'][$category] += $t->ticket_count;
            }

            $ticketTypes = [];
            foreach ($grouped as $type => $data) {
                $ticketTypes[] = [
                    'name' => $type,
                    'count' => $data['total'],
                    'categories' => array_map(fn($cat, $cnt) => ['name' => $cat, 'count' => $cnt], array_keys($data['categories']), array_values($data['categories'])),
                ];
            }

            $count = array_sum(array_column($ticketTypes, 'count'));
            $knownUserTicketCount += $count;

            $responseData[] = [
                'name' => $createdBy,
                'id' => $createdBy,
                'count' => $count,
                'ticket_types' => $ticketTypes,
            ];
            
        }

        // Get all tickets count
        $totalTickets = $ticketsQuery->sum('ticket_count');

        // Handle User Logs (unmatched CREATED_BY)
        $userLogTickets = $ticketsQuery->filter(fn($t) => !in_array($t->CREATED_BY, $matchedCreatedBys));

        $grouped = [];
        foreach ($userLogTickets as $t) {
            $type = $t->type ?? 'Unknown Type';
            $category = $t->category ?? 'Not Categorized';
        
            if (!isset($grouped[$type]['total'])) $grouped[$type]['total'] = 0;
            if (!isset($grouped[$type]['categories'][$category])) $grouped[$type]['categories'][$category] = 0;

            $grouped[$type]['total'] += $t->ticket_count;
            $grouped[$type]['categories'][$category] += $t->ticket_count;
        }

        $ticketTypes = [];
        foreach ($grouped as $type => $data) {
            $ticketTypes[] = [
                'name' => $type,
                'count' => $data['total'],
                'categories' => array_map(fn($cat, $cnt) => ['name' => $cat, 'count' => $cnt], array_keys($data['categories']), array_values($data['categories'])),
            ];
        }

        $responseData[] = [
            'name' => 'User Logs',
            'id' => 'User Logs',
            'count' => $totalTickets - $knownUserTicketCount,
            'ticket_types' => $ticketTypes,
        ];

        return response()->json([
            'ticketCounts' => $responseData,
            'totalLogTickets' => $totalTickets
        ]);
    }

    // Get Breached Ticket Chart Data
    public function getBreachedPointChartData(Request $request)
    {
        $from_date = date('Y-m-d', strtotime($request->fromDate));
        $to_date = date('Y-m-d', strtotime($request->toDate));
        $teamId = $request->teamId;
        $isChecked = $request->isChecked;

        // Fetch breached tickets with points
        $breachedTickets = DB::table('breached_tickets_points')
                            ->join('mstr_users', 'breached_tickets_points.TECHNICIAN_ID', '=', 'mstr_users.EMPLOYEE_ID')
                            ->join('ticket', 'breached_tickets_points.TICKET_ID', '=', 'ticket.TICKET_ID')

                            ->leftJoin('lkp_task_type', 'lkp_task_type.TASK_TYPE_ID', '=', 'ticket.TASK_TYPE_ID')
                            ->leftJoin('lkp_category','lkp_category.CATEGORY_ID','=','ticket.CATEGORY_ID')
                            ->select(
                                'breached_tickets_points.TECHNICIAN_ID',
                                'mstr_users.USER_NAME',
                                'ticket.CREATED_BY',
                                'lkp_task_type.DISPLAY_NAME as type',
                                'lkp_category.DISPLAY_NAME as category',
                                DB::raw('SUM(breached_tickets_points.POINTS) as total_breached_points')
                            )

                            ->when($from_date && $to_date, function ($query) use ($from_date, $to_date) {
                                return $query->whereDate('breached_tickets_points.CREATED_ON', '>=', $from_date)
                                            ->whereDate('breached_tickets_points.CREATED_ON', '<=', $to_date);
                            })
                            ->when($teamId, fn($query) => $query->whereIn('ticket.TEAM_NAME', $teamId))
                            ->when($isChecked === 'Y', function ($query) {
                                return $query->where('ticket.CREATED_BY', '!=', 'Ticketadmin');
                            })
                            ->groupBy('breached_tickets_points.TECHNICIAN_ID',
                                'mstr_users.USER_NAME',
                                'ticket.CREATED_BY',
                                'lkp_task_type.DISPLAY_NAME',
                                'lkp_category.DISPLAY_NAME',)
                            ->get();

        $groupedBreaches = [];

        foreach ($breachedTickets as $ticket) {
            $user = $ticket->USER_NAME;
            $type = $ticket->type ?? 'Unknown Type';
            $category = $ticket->category ?? 'Not Categorized';
            $points = $ticket->total_breached_points;

            if (!isset($groupedBreaches[$user])) {
                $groupedBreaches[$user] = [
                    'name' => $user,
                    'id' => $user,
                    'count' => 0,
                    'ticket_types' => []
                ];
            }

            $groupedBreaches[$user]['count'] += $points;

            if (!isset($groupedBreaches[$user]['ticket_types'][$type])) {
                $groupedBreaches[$user]['ticket_types'][$type] = [
                    'name' => $type,
                    'count' => 0,
                    'categories' => []
                ];
            }

            $groupedBreaches[$user]['ticket_types'][$type]['count'] += $points;

            if (!isset($groupedBreaches[$user]['ticket_types'][$type]['categories'][$category])) {
                $groupedBreaches[$user]['ticket_types'][$type]['categories'][$category] = [
                    'name' => $category,
                    'count' => 0
                ];
            }

            $groupedBreaches[$user]['ticket_types'][$type]['categories'][$category]['count'] += $points;
        }

        // Convert nested arrays to indexed arrays
        $responseData = array_map(function ($user) {
            $user['ticket_types'] = array_values(array_map(function ($type) {
                $type['categories'] = array_values($type['categories']);
                return $type;
            }, $user['ticket_types']));
            return $user;
        }, array_values($groupedBreaches));

        // Calculate total breached points
        $totalBreachedPoints = $breachedTickets->sum('total_breached_points');



        return response()->json([
            'breachedTickets' => $responseData,
            'totalBreachedPoints' => $totalBreachedPoints,
        ]);
    }    

    public function updatePassword(Request $request)
    {
            # Validation
            $request->validate([
                'old_password' => 'required',
                'new_password' => 'required|confirmed',
            ]);

            #Match The Old Password
            if(!Hash::check($request->old_password, auth()->user()->PASSWORD)){
                // return back()->with("error", "Old Password Doesn't match!");
                return response()->json([
                    'error' => true,
                    'message' => "Old Password doesn't match!",
                    'successCode' => 0
                ], 400);
            }

            #Update the new Password
            User::where(['USER_ID' => auth()->user()->USER_ID])->update([
                'PASSWORD' => Hash::make($request->new_password)
            ]);

            
            // return back()->with("status", "Password changed successfully!");
            return response()->json([
                'error' => false,
                'message' => "Password changed successfully!",
                'successCode' => 1,
            ]);
    }

    public function getTimeLeft($ticketId, $ticketNumber)
    {
        $ticket = Ticket::where('TICKET_ID',$ticketId)->first();

        $updates = DB::table('log_status_movement')
            ->select('log_status_movement.CHANGED_ON','log_status_movement.CHANGED_TO')
            ->where('TICKET_ID', $ticketId)
            ->orderBy('CHANGED_ON', 'asc')
            ->get();
            
        if ($updates->isEmpty()) {
            return 0; // No updates, return zero time
        }

        $workingStart = 10; // Start of working hours (10 AM)
        $workingEnd = 18;   // End of working hours (6 PM)
        $holidays = HolidayList::pluck('HOLIDAY')->toArray(); // Retrieve holiday list

        
        $totalUsedTime = 0; // In hours
            
        $firstStatus = $updates[0]->CHANGED_TO;
        $start = Carbon::parse($updates[0]->CHANGED_ON);

        $allSameStatus = true;
        foreach ($updates as $update) {
            if ($update->CHANGED_TO !== $firstStatus) {
                $allSameStatus = false;
                break;
            }
        }

        if (count($updates) === 1) {
            $singleUpdate = $updates[0];
            if ($singleUpdate->CHANGED_TO === 'Open' || $singleUpdate->CHANGED_TO === 'In Progress') {
                $start = Carbon::parse($singleUpdate->CHANGED_ON);
                $end = Carbon::now();

                // Calculate working hours between the single update and current time
                $totalUsedTime += $this->calculateWorkingHours($start, $end, $workingStart, $workingEnd, $holidays);

                $slaOn = DB::table('team')
                        ->where('TEAM_NAME', $ticket->TEAM_NAME)
                        ->first();                

                if ($slaOn && $slaOn->SLA_ON == 'CREATED_ON') {

                    $createdOn = Carbon::parse($ticket->CREATED_ON);
                    $firstChangedOn = Carbon::parse($singleUpdate->CHANGED_ON);

                    if ($createdOn->lessThan($firstChangedOn)) {
                        $gapTime = $this->calculateWorkingHours($createdOn, $firstChangedOn, $workingStart, $workingEnd, $holidays);
                        $totalUsedTime += $gapTime;
                    }
                }

            }
            return $totalUsedTime; // Return the total time immediately
        }

        if ($allSameStatus) {
            // All statuses are the same, calculate time from the first status to now
            if ($firstStatus === 'Open' || $firstStatus === 'In Progress') {
                $end = Carbon::now();
                
                $totalUsedTime += $this->calculateWorkingHours($start, $end, $workingStart, $workingEnd, $holidays);
            }
        } else {
            for ($i = 0; $i <= count($updates) - 1; $i++) {

                $currentStatus = $updates[$i]->CHANGED_TO;
                
                $start = Carbon::parse($updates[$i]->CHANGED_ON);
                
                // Skip intervals where the current status is "On Hold"
                if ($currentStatus === 'Cancelled' || $currentStatus === 'On Hold') {
                    continue;
                }

                // Determine the end time
                if (isset($updates[$i + 1])) {
                    $nextStatus = $updates[$i + 1]->CHANGED_TO;
                    
                    $end = Carbon::parse($updates[$i + 1]->CHANGED_ON);

                } else {
                    // Last status, calculate up to current time
                    if ($currentStatus === 'In Progress' || $currentStatus === 'Open' || $currentStatus === 'Reopened'){
                        $end = Carbon::now();
                    }                    
                }
                        
                $totalUsedTime += $this->calculateWorkingHours($start, $end, $workingStart, $workingEnd, $holidays);
            }
        }

        $slaOn = DB::table('team')
                            ->where('TEAM_NAME', $ticket->TEAM_NAME)
                            ->first();          

        if ($slaOn && $slaOn->SLA_ON == 'CREATED_ON') {       

            $createdOn = Carbon::parse($ticket->CREATED_ON);
            $firstChangedOn = Carbon::parse($updates[0]->CHANGED_ON);

            if ($createdOn->lessThan($firstChangedOn)) {
                $gapTime = $this->calculateWorkingHours($createdOn, $firstChangedOn, $workingStart, $workingEnd, $holidays);
                $totalUsedTime += $gapTime;
            }
        }
        
        return $totalUsedTime; // Total time in minutes
    }

    private function calculateWorkingHours($start, $end, $workingStart, $workingEnd, $holidays)
    {
        $totalWorkingHours = 0;
        $totalTime = 0;

        while ($start->lessThan($end)) {
            $currentDay = $start->format('Y-m-d');

            if (!in_array($currentDay, $holidays)) {
                $workStartTime = Carbon::createFromTime($workingStart, 0, 0, $start->timezone)->setDateFrom($start);
                $workEndTime = Carbon::createFromTime($workingEnd, 0, 0, $start->timezone)->setDateFrom($start);
                $lunchStart = Carbon::createFromTime(13, 0, 0, $start->timezone)->setDateFrom($start);
                $lunchEnd = Carbon::createFromTime(13, 30, 0, $start->timezone)->setDateFrom($start);
            
                if ($start->lessThan($workEndTime) && $end->greaterThan($workStartTime)) {
                    $intervalStart = $start->greaterThan($workStartTime) ? $start : $workStartTime;
                    $intervalEnd = $end->lessThan($workEndTime) ? $end : $workEndTime;

                    $intervalMinutes = $intervalStart->diffInMinutes($intervalEnd);

                    if($intervalStart->lessThan($lunchEnd) && $intervalEnd->greaterThan($lunchStart)) 
                    {
                        // $intervalMinutes += 30; // Add 30 minutes for lunch break
                        $overlapStart = $intervalStart->greaterThanOrEqualTo($lunchStart) ? $intervalStart : $lunchStart;
                        $overlapEnd = $intervalEnd->lessThanOrEqualTo($lunchEnd) ? $intervalEnd : $lunchEnd;

                        $lunchMinutes = $overlapStart->diffInMinutes($overlapEnd);
                        $intervalMinutes -= $lunchMinutes;
                    }
                
                    $totalWorkingHours += $intervalMinutes;
                     
                    // $totalWorkingHours += $intervalStart->diffInMinutes($intervalEnd);
                }
                
            }

            $start->addDay()->startOfDay();
        }

        return $totalWorkingHours;
    }
}