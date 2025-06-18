<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $table = 'ticket';

    protected $primaryKey = 'TICKET_ID';

    public $timestamps = false;

    // Allowng assignment

    protected $guarded = [];
}
