<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class Rolelink extends Model
{
    protected $table = 'ctrl_role_links';
    // protected $primaryKey = 'ROLE_ID';
    protected $guarded = [];
    public $timestamps = false;
}