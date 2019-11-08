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
        if(true==$request->has('creattime')){
            $time = strtotime($request->input('creattime'));
            $weeksuf = computeWeek($time,false);
        }else{
            $weeksuf = computeWeek(time(),false);
        }
        $bill = new Billflow();
        $bill->setTable('business_billflow_'.$weeksuf);
        $sql = $bill->where('business_code','=',$id);
        if(true==$request->has('creattime')){
            $start=strtotime($request->input('creattime'));
            $end = strtotime('+1day',$start);
            $sql->whereBetween('creattime',[$start,$end]);
        }
        $data =$sql->paginate(10)->appends($request->all());
        foreach ($data as $key =>$value){
            $data[$key]['creattime'] =date("Y-m-d H:i:s",$value["creattime"]);
        }
        return view('billflow.list',['list'=>$data,'input'=>$request->all()]);
    }
}