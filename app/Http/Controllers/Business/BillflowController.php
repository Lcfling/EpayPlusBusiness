<?php


namespace App\Http\Controllers\Business;


use App\Models\Billflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillflowController extends BaseController
{
    /**
     * 数据列表
     */
    public function index(Request $request){
        $id = Auth::id();
        $map = array();
        $map['business_code']=$id;
        $data = Billflow::where($map)->paginate('2')->appends($request->all());
        foreach ($data as $key =>$value){
            $data[$key]['creattime'] =date("Y-m-d H:i:s",$value["creattime"]);
        }
        return view('billflow.list',['list'=>$data,'input'=>$request->all()]);
    }
}