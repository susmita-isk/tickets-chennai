<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    protected $table = 'ticket_attachment';

    protected $primaryKey = 'ATTACHMENT_ID';

    public $timestamps = false;

    // Allowng assignment

    protected $guarded = [];
}