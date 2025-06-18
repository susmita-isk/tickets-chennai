<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketUpdate extends Model
{
    use HasFactory;

    protected $table = 'ticket_updates';

    protected $primaryKey = 'TICKET_UPDATE_ID';

    public $timestamps = false;

    // Allowng assignment

    protected $guarded = [];
}
