<?php


namespace App\Http\Controllers\Business;


use App\Models\User;
use Illuminate\Support\Facades\Auth;

class InfoController extends BaseController
{
    public function index(){
        $id = Auth::id();
        $info = $id?User::find($id)->first():[];
        return view('info.list',['list'=>$info]);
    }
}