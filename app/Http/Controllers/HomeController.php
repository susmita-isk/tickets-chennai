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
            // $technicians->whereIn('team_members.TEAM_ID', [1, 2, 3, 4, 5]);
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

        $ticketsQuery = Ticket::query()
                    ->selectRaw('TECHNICIAN_ID, COUNT(*) as ticket_count')
                    ->where('PROJECT_ID', session('code'))
                    ->when($from_date && $to_date, function ($query) use($from_date,$to_date) {
                            return $query->whereDate('ticket.ASSIGNED_ON', '>=', $from_date)
                                        ->whereDate('ticket.ASSIGNED_ON', '<=', $to_date);
                        })
                    ->when($teamId, fn ($query) => $query->whereIn('ticket.TEAM_NAME', $teamId))
                    ->when($isChecked === 'Y', function ($query) {
                        return $query->where('ticket.CREATED_BY', '!=', 'Ticketadmin');
                    })
                    ->groupBy('TECHNICIAN_ID');
        
        $ticketCounts = $ticketsQuery->pluck('ticket_count', 'TECHNICIAN_ID');

        $totalTickets = 0;
        
        foreach ($technicians as $technician) {
            $technician->ticket_count = $ticketCounts[$technician->EMPLOYEE_ID] ?? 0; // Default to 0 if no tickets
            $totalTickets += $technician->ticket_count;
        }
        
        $data=[
            'technicians'=>$technicians
        ]; 
        
        return response()->json([
            'technicians' => $technicians,
            'total_tickets' => $totalTickets
        ]);

    }
    // Get Log Wise Ticket Chart Data
    public function getLogTicketChartData(Request $request)
    {
        $from_date = date('Y-m-d', strtotime($request->fromDate));
        $to_date = date('Y-m-d', strtotime($request->toDate));
        $teamId = $request->teamId;
        $isChecked = $request->isChecked;

        $categories = DB::table('mstr_log_users')->select('CREATED_BY','DETAILS')->get();

        $ticketsQuery = Ticket::query()
                        ->when($from_date && $to_date, function ($query) use ($from_date, $to_date) {
                            return $query->whereDate('ticket.CREATED_ON', '>=', $from_date)
                                ->whereDate('ticket.CREATED_ON', '<=', $to_date);
                        })
                        ->when($isChecked === 'Y', function ($query) {
                            return $query->where('ticket.CREATED_BY', '!=', 'Ticketadmin');
                        })
                        ->when($teamId, fn($query) => $query->whereIn('ticket.TEAM_NAME', $teamId))
                        ->where('PROJECT_ID', session('code'));

        $ticketCounts = [];

        foreach ($categories as $category) {
            $createdBy = $category->CREATED_BY;
            $details = $category->DETAILS ? explode(',', $category->DETAILS) : [];

            $createdByArray = array_merge([$createdBy], $details);

            $count = (clone $ticketsQuery)->whereIn('CREATED_BY', $createdByArray)->count();

            $ticketCounts[] = ['name' => $createdBy, 'count' => $count];
        }

        $totalLogTickets = 0;

        $totalTickets = $ticketsQuery->count();
        $categorizedTickets = array_sum(array_column($ticketCounts, 'count'));

        $totalLogTickets = $totalTickets;

        $ticketCounts[] = [
            'name' => 'User Logs',
            'count' => $totalTickets - $categorizedTickets,
        ];

        return response()->json([
            'ticketCounts' => $ticketCounts,
            'totalLogTickets' => $totalLogTickets,
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
            ->selectRaw('lkp_task_type.DISPLAY_NAME, COUNT(*) as count')
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
            ->groupBy('lkp_task_type.DISPLAY_NAME')
            ->pluck('count', 'lkp_task_type.DISPLAY_NAME');

        $totalTicketsTypes = 0;
        // Map the counts back to the `$ticketTypes` structure
        $ticketTypesCounts = $ticketTypes->mapWithKeys(function ($ids, $displayName) use ($ticketTypesCountsQuery, &$totalTicketsTypes) {
            return [$displayName => $ticketTypesCountsQuery[$displayName] ?? 0];
        });   
        
        $totalTicketsTypes = $ticketTypesCounts->sum();
        
        return response()->json([ 
            'ticketTypes'=>$ticketTypes,
            'ticketTypesCounts'=>$ticketTypesCounts ?? [0],
            'totalTicketsTypes'=> $totalTicketsTypes
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

        // Fetch technicians with a single query
        $technicians = User::leftjoin('map_user_department', 'mstr_users.USER_ID', '=', 'map_user_department.USER_ID')
            ->leftJoin('team_members', 'map_user_department.USER_ID', '=', 'team_members.TECHNICIAN')
            ->leftJoin('team', 'team.TEAM_ID', '=', 'team_members.TEAM_ID')
            ->leftJoin('ticket', 'ticket.TECHNICIAN_ID', '=', 'mstr_users.EMPLOYEE_ID')

            ->where('map_user_department.DEPARTMENT_ID', session('code'))
            ->where('team.DEPARTMENT_ID',session('code'))  
            ->where('map_user_department.ROLE', 'Technician')
            ->where('team_members.IS_ACTIVE', 'Y')
            ->when($hasTeam, function ($query) use ($teamId) {
                $query->whereIn('team.TEAM_NAME', $teamId);
                $query->whereIn('ticket.TEAM_NAME', $teamId);
            }, function ($query) {
                // $query->whereIn('team_members.TEAM_ID', [1, 2, 3, 4, 5]);
                $teamIds = DB::table('team')
                    ->where('DEPARTMENT_ID', session('code'))
                    ->pluck('TEAM_ID')
                    ->toArray();

                $query->whereIn('team.TEAM_ID', $teamIds);
            })
           
            ->where('ticket.PROJECT_ID', session('code'))
            ->when($from_date && $to_date, function ($query) use ($from_date, $to_date) {
                return $query->whereDate('ticket.ASSIGNED_ON', '>=', $from_date)
                            ->whereDate('ticket.ASSIGNED_ON', '<=', $to_date);
            })
            ->select('mstr_users.EMPLOYEE_ID',
                    'mstr_users.USER_NAME',
                    'team_members.TECHNICIAN',
                    DB::raw("COUNT(ticket.TICKET_ID) as total_tickets"),
                )
            ->groupBy('mstr_users.EMPLOYEE_ID', 'mstr_users.USER_NAME', 'team_members.TECHNICIAN')
            ->orderBy('mstr_users.USER_NAME', 'asc')
            ->distinct()
            ->get();
        
        $slaTicketCounts = [];

        $totalWithinSla = 0;
        $totalSlaBreach = 0;
                 
        foreach ($technicians as $technician) { 
            $technicianId = $technician['EMPLOYEE_ID'];
            $technicianTickets = Ticket::query()
                            ->leftJoin('lkp_task_type', 'lkp_task_type.TASK_TYPE_ID', '=', 'ticket.TASK_TYPE_ID')   
                            ->where('PROJECT_ID', session('code'))                       
                            ->when($from_date && $to_date, function ($query) use($from_date,$to_date) {
                                        return $query->whereDate('ticket.ASSIGNED_ON', '>=', $from_date)
                                                    ->whereDate('ticket.ASSIGNED_ON', '<=', $to_date);
                                    })   
                            ->where(['ticket.TECHNICIAN_ID' => $technicianId]) 
                            ->whereIn('ticket.PROGRESS', ['Open', 'In Progress', 'On Hold', 'Resolved'])
                            ->when($teamId, fn($query) => $query->whereIn('ticket.TEAM_NAME', $teamId))   
                            ->when($isChecked === 'Y', function ($query) {
                                return $query->where('ticket.CREATED_BY', '!=', 'Ticketadmin');
                            })    
                            ->select('ticket.TICKET_ID', 
                                    'ticket.TICKET_NO', 
                                    'lkp_task_type.SLA as SLA',
                                    'ticket.STATUS',
                                    'ticket.IS_SLA_BREACH')
                            ->get();         
            
            $withinSla = 0;
            $slaBreach = 0;
            
            foreach ($technicianTickets as $ticket) {
                if($ticket->STATUS == 'Open')
                {
                    // Calculate time consumed for the ticket
                    $totalTimeConsumed = $this->getTimeLeft($ticket->TICKET_ID, $ticket->TICKET_NO);
                    // Check SLA
                    $sla = $ticket->SLA;
                    if ($sla) {
                        if ($totalTimeConsumed > ($sla * 60)) {
                            $slaBreach++;
                        } else {
                            $withinSla++;
                        }
                    }
                }
                else{
                    if($ticket->IS_SLA_BREACH === 'N'){                       
                        $withinSla++;
                    }
                    else{
                        $slaBreach++;
                    }
                }                
            }
                        
            $slaTicketCounts[$technicianId] = [
                'withinSla' => $withinSla,
                'slaBreach' => $slaBreach
            ];    
            
            $totalWithinSla += $withinSla;
            $totalSlaBreach += $slaBreach;
        }
        
        $data=[
            'technicians'=>$technicians,
            'slaTicketCounts'=>$slaTicketCounts ?? [0],
        ];
       
        return response()->json([
            'technicians'=>$technicians,
            'slaTicketCounts'=>$slaTicketCounts ?? [0],
            'totalWithinSla'=>$totalWithinSla,
            'totalSlaBreach'=>$totalSlaBreach
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

        $ticketCounts = Ticket::query()
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
                        ->selectRaw('LOWER(PROGRESS) as progress, COUNT(*) as count')
                        ->groupBy('progress')
                        ->pluck('count', 'progress') // Returns an associative array: [status => count]
                        ->toArray();
        
        $totalTickets = 0;

        $statusCounts = array_reduce($ticketStatus, function ($result, $status) use ($ticketCounts) {
            $result[$status] = $ticketCounts[strtolower($status)] ?? 0;
            return $result;
        }, []);

        $totalTickets = array_sum($statusCounts); // Total tickets across all statuses
       
        return response()->json([
            'ticketStatus'=>$ticketStatus,
            'statusCounts'=>$statusCounts,
            'totalTicketsStatus'=>$totalTickets
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
        $top10Departments = Ticket::select('DEPARTMENT_NAME', DB::raw('COUNT(*) AS ticket_count'))
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
                                ->groupBy('DEPARTMENT_NAME')
                                ->orderBy('ticket_count', 'DESC')
                                ->limit(10)
                                ->get();

        $deptTickets = [];

        $totalTickets = 0;

        foreach ($top10Departments as $dept) {
            $deptTickets[] = [
                'department_name' => $dept->DEPARTMENT_NAME,
                'ticket_count' => $dept->ticket_count
            ];
            $totalTickets += $dept->ticket_count;
        }

        return response()->json([
            'top10Departments'=>$top10Departments,
            'deptTickets'=>$deptTickets,
            'totalTicketsDept'=>$totalTickets
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
                            ->where('PROJECT_ID', session('code'))
                            ->select('TECHNICIAN_ID', DB::raw('SUM(POINTS) as total_assigned_points'))
                            ->groupBy('TECHNICIAN_ID')
                            ->get()
                            ->keyBy('TECHNICIAN_ID');

        // Fetch released points in bulk
        $releasedPoints = TicketPoints::query()
                            ->select('TECHNICIAN_ID', DB::raw('SUM(POINTS) as total_released_points'))
                            ->when($from_date && $to_date, function ($query) use ($from_date, $to_date) {
                                return $query->whereDate('STATUS_DATE', '>=', $from_date)
                                            ->whereDate('STATUS_DATE', '<=', $to_date);
                            })
                            ->groupBy('TECHNICIAN_ID')
                            ->get()
                            ->keyBy('TECHNICIAN_ID');

        $breachedPoints = DB::table('breached_tickets_points')
                            ->select('TECHNICIAN_ID', DB::raw('SUM(POINTS) as total_breached_points'))
                            ->when($from_date && $to_date, function ($query) use ($from_date, $to_date) {
                                return $query->whereDate('CREATED_ON', '>=', $from_date)
                                            ->whereDate('CREATED_ON', '<=', $to_date);
                            })
                            ->groupBy('TECHNICIAN_ID')
                            ->get()
                            ->keyBy('TECHNICIAN_ID');

        $ticketPoints = [];

        $totalPoints = 0;
        
        // Combine the points and associate them with the technician's ID
        $ticketPoints = $technicians->map(function ($technician) use ($assignedPoints, $releasedPoints, $breachedPoints) {
            $assignedPoints = $assignedPoints->get($technician['EMPLOYEE_ID'])->total_assigned_points ?? 0;
            $releasedPoints = $releasedPoints->get($technician['EMPLOYEE_ID'])->total_released_points ?? 0;
            $breachedPoints = $breachedPoints->get($technician['EMPLOYEE_ID'])->total_breached_points ?? 0;

            return [
                'EMPLOYEE_ID' => $technician['EMPLOYEE_ID'],
                'USER_NAME' => $technician['USER_NAME'],
                'total_points' => $assignedPoints + $releasedPoints + $breachedPoints, // Total points for the technician
            ];
        });

        $totalPoints = $ticketPoints->sum('total_points'); // Total points across all technicians
                
        return response()->json([
            'technicians' => $ticketPoints->toArray(),
            'totalTicketsPoints' => $totalPoints
        ]);
    }
    // Get Ticket Duration Chart Data
    public function getTicketDurationChartData(Request $request)
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
                                ->select('lkp_task_type.TASK_TYPE_ID','lkp_task_type.SLA', 'lkp_task_type.DISPLAY_NAME')
                                ->get()
                                ->groupBy('DISPLAY_NAME') // Group by DISPLAY_NAME
                                ->map(function ($group) {
                                    return $group->map(function ($item) {
                                        return [
                                            'id' => $item->TASK_TYPE_ID,
                                            'sla' => $item->SLA,
                                        ];
                                    });
                                });
        $allTaskTypeIds = $ticketTypes->flatten(1)->pluck('id')->toArray();

        $ticketsGroupedByTaskType = Ticket::join('lkp_task_type', 'lkp_task_type.TASK_TYPE_ID', '=', 'ticket.TASK_TYPE_ID')
            ->when($from_date && $to_date, function ($query) use ($from_date, $to_date) {
                return $query->whereDate('ticket.ASSIGNED_ON', '>=', $from_date)
                            ->whereDate('ticket.ASSIGNED_ON', '<=', $to_date);
            })
            ->when($teamId, fn($query) => $query->whereIn('ticket.TEAM_NAME', $teamId))
            ->when($isChecked === 'Y', function ($query) {
                return $query->where('ticket.CREATED_BY', '!=', 'Ticketadmin');
            })
            ->where('ticket.PROJECT_ID', session('code'))
            ->whereIn('lkp_task_type.TASK_TYPE_ID', $allTaskTypeIds)
            ->select('lkp_task_type.TASK_TYPE_ID',
                    'ticket.TICKET_ID', 
                    'ticket.TICKET_NO',
                    DB::raw('SUM(lkp_task_type.SLA) AS total_time'))
            ->groupBy('lkp_task_type.TASK_TYPE_ID')
            ->get()
            ->keyBy('TASK_TYPE_ID');
         
        
        $actualTicketTime =[]; 
        $spentTime = [];
        foreach ($ticketTypes as $displayName => $ids) { // Use keys (technician IDs)
            $taskTypeIds = collect($ids)->pluck('id')->toArray();
           
            $totalTime = $taskTypeIds
                ? collect($taskTypeIds)
                    ->map(fn($id) => $ticketsGroupedByTaskType->get($id)->total_time ?? 0)
                    ->sum()
                : 0;

            $actualTicketTime[] = $totalTime;  
            
            $tickets = Ticket::join('lkp_task_type', 'lkp_task_type.TASK_TYPE_ID', '=', 'ticket.TASK_TYPE_ID')
                        ->when($from_date && $to_date, function ($query) use ($from_date, $to_date) {
                            return $query->whereDate('ticket.ASSIGNED_ON', '>=', $from_date)
                                        ->whereDate('ticket.ASSIGNED_ON', '<=', $to_date);
                        })
                        ->when($teamId, fn($query) => $query->whereIn('ticket.TEAM_NAME', $teamId))
                        ->where('ticket.PROJECT_ID', session('code'))
                        ->whereIn('lkp_task_type.TASK_TYPE_ID', $taskTypeIds)
                        ->select('ticket.TICKET_ID', 'ticket.TICKET_NO', 'lkp_task_type.TASK_TYPE_ID')
                        ->get();

            $totalMinutes = $tickets->map(fn($ticket) => $this->getTimeLeft($ticket->TICKET_ID, $ticket->TICKET_NO))
                        ->sum();

            $hours = floor($totalMinutes / 60);
            $minutes = $totalMinutes % 60;

            $spentTime[] = $hours;
            
        }
       
        $data=[
            'ticketTypes'=>$ticketTypes,
            'actualTicketTime'=>$actualTicketTime,
            'spentTime'=>$spentTime ?? [0]
        ];
        
        return response()->json(['ticketTypes'=>$ticketTypes,
            'actualTicketTime'=>$actualTicketTime,
            'spentTime'=>$spentTime ?? [0]]);
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
                            ->when($from_date && $to_date, function ($query) use ($from_date, $to_date) {
                                return $query->whereDate('breached_tickets_points.CREATED_ON', '>=', $from_date)
                                            ->whereDate('breached_tickets_points.CREATED_ON', '<=', $to_date);
                            })
                            ->when($teamId, fn($query) => $query->whereIn('ticket.TEAM_NAME', $teamId))
                            ->when($isChecked === 'Y', function ($query) {
                                return $query->where('ticket.CREATED_BY', '!=', 'Ticketadmin');
                            })
                            ->select('breached_tickets_points.TECHNICIAN_ID',
                                    'mstr_users.USER_NAME',
                                    DB::raw('SUM(breached_tickets_points.POINTS) as total_breached_points'))
                            ->groupBy('breached_tickets_points.TECHNICIAN_ID', 'mstr_users.USER_NAME')
                            ->get();

        // Calculate total breached points
        $totalBreachedPoints = $breachedTickets->sum('total_breached_points');

        return response()->json([
            'breachedTickets' => $breachedTickets,
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
        // $updates = $this->getTicketUpdates($ticketId);
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