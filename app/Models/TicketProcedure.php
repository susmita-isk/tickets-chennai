<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TicketProcedure extends Model
{
    // Set the connection for this model
    protected $connection = 'secondary_mysql';

    // Disable the table association for this model
    protected $table = null;

    /**
     * Call the stored procedure with XML data and get the output parameter.
     *
     * @param \SimpleXMLElement $xmlData
     * @return integer
     */
    public static function insertTicket($xmlString)
    {       
        // Define the SQL variable to store the output parameter
        DB::connection('secondary_mysql')->statement('SET @O_PARM_TICKET_ID = 0');

        // Call the stored procedure
        DB::connection('secondary_mysql')->statement('CALL itims_helpdesk.insert_ticket(?, @O_PARM_TICKET_ID)', [$xmlString]);

        // Retrieve the value of the output parameter
        $result = DB::connection('secondary_mysql')->select('SELECT @O_PARM_TICKET_ID AS O_PARM_TICKET_ID');

        return $result[0]->O_PARM_TICKET_ID;
    }
}

