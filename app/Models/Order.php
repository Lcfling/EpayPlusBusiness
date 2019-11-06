<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table;
    protected $primaryKey = 'id';
    public $timestamps = false;
}