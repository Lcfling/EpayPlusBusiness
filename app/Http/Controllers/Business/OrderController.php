<?php


namespace App\Http\Controllers\Business;


use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequest;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Excel;

class OrderController extends Controller
{
    public function index(Request $request){
        $business = Auth::id();
        if(true==$request->has('creatime')){
            $time = strtotime($request->input('creatime'));
            $weeksuf = computeWeek($time,false);
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
        if(true==$request->input('export')&& true==$request->has('export')){
            $head = array('外部订单号','平台订单号','支付类型','真实付款金额','收款金额','支付状态','创建时间','支付时间','商户号');
            $data = $sql->select('out_order_sn','order_sn','payType','tradeMoney','sk_money','status','creatime','pay_time','business_code')->get()->toArray();
            foreach ($data as $key=>$value){
                $data[$key]['creatime']=date("Y-m-d H:i:s",$value["creatime"]);
                $data[$key]['pay_time']=date("Y-m-d H:i:s",$value["pay_time"]);
                if($data[$key]['payType']==0){
                    $data[$key]['payType']="默认";
                }else if($data[$key]['payType']==1){
                    $data[$key]['payType'] = "微信";
                }else{
                    $data[$key]['payType']="支付宝";
                }
                if($data[$key]['status']==0){
                    $data[$key]['status'] = "未支付";
                }else if($data[$key]['status']==1){
                    $data[$key]['status']="支付成功";
                }else if($data[$key]['status']==2){
                    $data[$key]['status']="过期";
                }else{
                    $data[$key]['status']="取消";
                }
                if($data[$key]['sk_status']==0){
                    $data[$key]['sk_status']="未收款";
                }else if($data[$key]['sk_status']==1){
                    $data[$key]['sk_status']="手动收款";
                }else{
                    $data[$key]['sk_status']="自动收款";
                }
            }
            exportExcel($head,$data,'订单信息','',true);
        }else{
            $data=$sql->paginate(5)->appends($request->all());
            foreach ($data as $key=>$value){
                $data[$key]['creatime']=date("Y-m-d H:i:s",$value["creatime"]);
                if($data[$key]['pay_time']!=0){
                    $data[$key]['pay_time']=date("Y-m-d H:i:s",$value["pay_time"]);
                }else{
                    $data[$key]['pay_time']="无";
                }

            }
            return view('order.list',['list'=>$data,'input'=>$request->all()]);
        }
    }

    public function export(StoreRequest $request){
        $weeksuf = computeWeek(time(),false);
        $order = new Order();
        $order->setTable('order_'.$weeksuf);
        $cellData = $order->select('out_order_sn','order_sn','payType','tradeMoney','sk_money','status','creatime','pay_time','business_code')->get()->toArray();
        foreach ($cellData as $key=>$value){
            $cellData[$key]['creatime']=date("Y-m-d H:i:s",$value["creatime"]);
            $cellData[$key]['pay_time']=date("Y-m-d H:i:s",$value["pay_time"]);
        }
        $head = array('外部订单号','平台订单号','支付类型','真实付款金额','收款金额','支付状态','创建时间','支付时间','商户号');
        exportExcel($head,$cellData,'订单信息','',true);
    }
}