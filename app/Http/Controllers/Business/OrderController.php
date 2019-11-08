<?php


namespace App\Http\Controllers\Business;


use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request){
        $business = Auth::id();
        if(true==$request->has('creatime')){
            $time = strtotime($request->input('creatime'));
            $weeksuf = computeWeek($time,false);
            /*print_r($time);
            print_r($weeksuf);
            die();*/
        }else{
            $weeksuf = computeWeek(time(),false);
        }
        $order = new Order();
        $order->setTable('order_'.$weeksuf);
        $sql = $order->where('business_code','=',$business);
        if(true==$request->has('out_order_sn')){
            $sql->where('out_order_sn',$request->input('out_order_sn'));
        }
        if(true==$request->has('status')){
            $sql->where('status',$request->input('status'));
        }
        if(true==$request->has('creatime')&&false==$request->has('pay_time')){
            $start=strtotime($request->input('creatime'));
            $end = strtotime('+1day',$start);
            $sql->whereBetween('creatime',[$start,$end]);
        }
        if(true==$request->has('creatime')&&true==$request->has('pay_time')){
            $start=strtotime($request->input('creatime'));
            $end=strtotime($request->input('pay_time'));
            $sql->whereBetween('creatime',[$start,$end]);
        }
        $data=$sql->paginate(5)->appends($request->all());
        foreach ($data as $key=>$value){
            $data[$key]['creatime']=date("Y-m-d H:i:s",$value["creatime"]);
            $data[$key]['pay_time']=date("Y-m-d H:i:s",$value["pay_time"]);
        }
        return view('order.list',['list'=>$data,'input'=>$request->all()]);
    }
}