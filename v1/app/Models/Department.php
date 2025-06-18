<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $table = 'department_details';

    protected $primaryKey = 'DEPARTMENT_ID';

    public $timestamps = false;

    // Allowing assignment

    protected $guarded = [];
}
