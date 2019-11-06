<?php


namespace App\Http\Controllers\Business;


use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class InfoController extends Controller
{
    public function index(){
        $id = Auth::id();
        $info = $id?User::find($id)->first():[];
        return view('info.list',['list'=>$info]);
    }
}