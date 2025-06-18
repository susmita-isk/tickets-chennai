<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecurringTicket extends Model
{
    use HasFactory;

    protected $table = 'recurring_tickets';

    protected $primaryKey = 'RECURRING_ID';

    // Timestamps
    public $timestamps = false;

    // Allowng assignment

    protected $guarded = [];
}