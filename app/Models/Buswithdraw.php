<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Buswithdraw extends Model
{
    protected $table = 'business_withdraw';
    protected $fillable = ['business_code','name','deposit_name','deposit_card','money','status'];
    protected $primaryKey = 'id';
}