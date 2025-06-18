<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskType extends Model
{
    use HasFactory;

    protected $table = 'lkp_task_type';
    protected $primaryKey = 'TASK_TYPE_ID';

    // Timestamps
    public $timestamps = false;

    // Allowing assignment
    protected $guarded = [];
 
}