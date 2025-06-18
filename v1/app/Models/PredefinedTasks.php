<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PredefinedTasks extends Model
{
    use HasFactory;

     
    protected $table = 'mstr_templates';
    protected $primaryKey = 'TEMPLATE_ID';

    // Timestamps
    const CREATED_AT = 'CREATED_ON';
    const UPDATED_AT = 'MODIFIED_ON';

    // Allowing assignment
    protected $guarded = [];
}