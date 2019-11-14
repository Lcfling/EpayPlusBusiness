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
            $date = $request->input('creattime');
            $time = strtotime($request->input('creattime'));
            $weeksuf = computeWeek($time,false);
        }else{
            $date = "本周";
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
        if(true==$request->input('export')&&true==$request->has('export')){
            $head = array('订单ID','订单号','积分','商户号','状态','支付类型','备注','创建时间');
            $data = $sql->select('order_id','order_sn','score','business_code','status','paycode','remark','creattime')->get()->toArray();
            foreach ($data as $key => $value){
                $data[$key]['creattime']=date("Y-m_d H:i:s",$value['creattime']);
            }
            exportExcel($head,$data,$date.'账户流水','',true);
        }else{
            $data =$sql->paginate(10)->appends($request->all());

            foreach ($data as $key =>$value){
                $data[$key]['creattime'] =date("Y-m-d H:i:s",$value["creattime"]);
            }
            return view('billflow.list',['list'=>$data,'input'=>$request->all()]);
        }
    }
}