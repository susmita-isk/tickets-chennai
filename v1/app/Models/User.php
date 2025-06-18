<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'mstr_users';
    protected $primaryKey = 'USER_ID';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'USER_NAME',
        'EMAIL',
        'MOBILE_NUMBER',
        'EMPLOYEE_ID',
        'LOGIN_ID',
        'PASSWORD',
        'ACTIVATED_ON',
        'ACTIVATED_BY',
        'INACTIVATED_ON',
        'INACTIVATED_BY',
        'FCM_ID',
        'REMARKS'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Since Password Column is named differently from default
     */
    public function getAuthPassword()
    {
        return $this->PASSWORD;
    }
}
