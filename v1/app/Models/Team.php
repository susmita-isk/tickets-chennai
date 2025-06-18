<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $table = 'team';

    protected $primaryKey = 'TEAM_ID';

    public $timestamps = false;

    // Allowng assignment

    protected $guarded = [];
}
