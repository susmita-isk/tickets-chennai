<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HolidayList extends Model
{
    use HasFactory;

    protected $table = 'lkp_holidays';
    protected $guarded = [];
}