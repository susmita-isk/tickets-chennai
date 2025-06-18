<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketPoints extends Model
{
    use HasFactory;

    protected $table = 'ticket_points';

    protected $primaryKey = 'POINT_ID';

    public $timestamps = false;

    // Allowng assignment

    protected $guarded = [];
}