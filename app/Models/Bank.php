<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $table = 'business_bank';
    protected $fillable = ['business_code','name','deposit_name','deposit_card','status','creatime'];
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $bankInfo;
}