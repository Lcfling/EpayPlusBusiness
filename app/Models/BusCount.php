<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class BusCount extends Model
{
    protected $table = 'business_count';
    protected $primaryKey = 'id';
    public $timestamps = false;
}