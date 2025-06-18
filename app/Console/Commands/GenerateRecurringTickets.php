<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class GenerateRecurringTickets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recurring:tickets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate recurring tickets based on schedule';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try{
            // return 0;
            $today = now();

            // Fetch recurring tickets where the end date has not passed
            $recurringTickets = DB::table('recurring_tickets')
                                ->where('RECURRING_TILL', '>=', $today->toDateString())
                                ->get();
            // $recurringTickets = Ticket::whereNotNull('FREQUENCY')->get();

            foreach ($recurringTickets as $recTicket) {
                $shouldLogTicket = false;
                
                if ($recTicket->FREQUENCY === 'Weekly') {
                    // Check if today matches the specified weekday
                    if (strtolower($today->format('l')) === strtolower($recTicket->RECURRING_TILL)) {
                        $shouldLogTicket = true;
                    }
                } 
                elseif ($recTicket->FREQUENCY === 'Monthly') {
                    // Check if today matches the start date's day of the month
                    $startDate = Carbon::parse($recTicket->START_DATE);
                    if ($today->day === $startDate->day) {
                        $shouldLogTicket = true;
                    }
                }
                elseif ($recTicket->FREQUENCY === 'Daily') {
                    // Check if today less than the recurring till date
                    if (strtolower($today->format('l')) === strtolower($recTicket->RECURRING_TILL)) {
                        $shouldLogTicket = true;
                    }
                }
                
                // Check if today matches the scheduled weekday
                if ($shouldLogTicket) {
                
                    $result= \DB::select("CALL generate_ticket_no(?, @batchCode)", [$recTicket->PROJECT_ID]);
    
                    $result2 = \DB::select('SELECT @batchCode AS batchCode');
                    if($result2 && isset($result2[0]->batchCode)) {
                        $serialNumber = $result2[0]->batchCode;                
                  
                    
                        Ticket::create([
                            'TICKET_NO'       => $serialNumber,
                            'TASK_NO'         => 0,
                            'PROJECT_ID'      => $recTicket->PROJECT_ID,
                            'MODE'            => $recTicket->MODE,
                            'SUBJECT'         => $recTicket->SUBJECT,
                            'DESCRIPTION'     => $recTicket->DESCRIPTION,
                            'PRIORITY'        => $recTicket->PRIORITY,
                            'TEAM_NAME'       => $recTicket->TEAM_NAME,
                            'REQUESTED_BY'    => $recTicket->REQUESTED_BY,
                            'USER_NAME'       => $recTicket->USER_NAME,
                            'USER_MAIL'       => $recTicket->USER_MAIL,
                            'DEPARTMENT_CODE' => $recTicket->DEPARTMENT_CODE,
                            'DEPARTMENT_NAME' => $recTicket->DEPARTMENT_NAME,
                            'CREATED_BY'      => Auth::user()->LOGIN_ID,
                            'CREATED_ON'      => now()
                        ]);
                    }
                }
            }
            return response()->json(['success' => true, 'message' => 'Recurring tickets logged successfully.']);
        } 
        catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}