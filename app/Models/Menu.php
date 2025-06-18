<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{

    protected $table = 'ctrl_links';
    protected $primaryKey = 'LINK_CODE';
    protected $guarded = [];
    public $timestamps = false;
}