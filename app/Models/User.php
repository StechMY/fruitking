<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, DefaultDatetimeFormat;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'phone', 'area_id', 'agent_id', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function sales()
    {
        return $this->hasMany(SalesRecord::class);
    }

    public function agent()
    {
        return $this->belongsTo(Administrator::class, 'agent_id');
    }
}
