<?php

namespace App\Models;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use App\Models\Interfaces\AdminUsersInterface;
use App\Models\Traits\AdminUsersTrait;
class User extends Model implements AuthenticatableContract, CanResetPasswordContract, AdminUsersInterface
{
    use Authenticatable, CanResetPassword, AdminUsersTrait;
    protected $table = 'business';
    protected $fillable = ['nickname', 'account', 'password','accessKey','shenfen','mobile','fee','status'];
    protected $hidden = ['password', 'remember_token','paypassword'];
    protected $userInfo;
    protected $primaryKey = 'business_code';
    public $timestamps = false;

    public function getUserInfo($account){
        $user =User::where('account',$account)->first();
        return $user;
    }
}
