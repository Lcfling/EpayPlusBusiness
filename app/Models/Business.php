<?php

namespace App\Models;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class Business extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword;
    protected $primaryKey = 'business_code';
    protected $table = 'business';
    protected $fillable = ['account', 'mobile', 'password'];
    protected $hidden = ['password', 'remember_token'];

    const CREATED_AT = 'creatime';
    const UPDATED_AT = 'updatetime';
    protected $userInfo;
}