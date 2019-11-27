<?php


namespace App\Http\Controllers\Business;

use App\Http\Requests\StoreRequest;
use App\Models\Bank;
use App\Models\Billflow;
use App\Models\BusCount;
use App\Models\Buswithdraw;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BuswithdrawController extends BaseController
{
    public function index(Request $request){
        $id = Auth::id();
        $map = array();
        $map['business_code']=$id;
        if(true==$request->has('status')){
            $map['status']=$request->input('status');
        }
        $data = Buswithdraw::where($map)->orderBy('creatime','desc')->paginate(10)->appends($request->all());
        foreach ($data as $key =>$value){
            $data[$key]['money'] = $data[$key]['money']/100;
            $data[$key]['creatime'] =date("Y-m-d H:i:s",$value["creatime"]);
            if($data[$key]['endtime']!=''){
                $data[$key]['endtime'] =date("Y-m-d H:i:s",$value["endtime"]);
            }
            $data[$key]['tradeMoney']=$data[$key]['tradeMoney']/100;
            $data[$key]['feemoney']=$data[$key]['feemoney']/100;
        }
        return view('buswithdraw.list',['list'=>$data,'input'=>$request->all()]);
    }
    /**
     * 编辑页
     */
    public function edit($id=0){
        $info = $id?Buswithdraw::find($id):[];
        //获取当前登录用户的银行卡列表
        $business = Auth::id();
        $data = Bank::get()->where('business_code',$business)->where('status',0);
        $busCount = BusCount::where('business_code',$business)->first();
        $busCount['balance']=$busCount['balance']/100;
        //获取提现手继续
        $fee = DB::table('admin_options')->where('key','=','one_time_draw')->value('value');
        return view('buswithdraw.edit',['info'=>$info,'id'=>$id,'banklist'=>$data,'balance'=>$busCount,'fee'=>$fee/100]);
    }
    /**
     * 添加提现申请
     */
    public function store(StoreRequest $request){
        //提现单号
        $order_sn = date("YmdHis",time()).mt_rand(100000,999999);
        //获取银行卡的id
        $id = $request->input('bank_card');
        //获取输入的支付密码
        $pwd = $request->input('paypassword');
        //获取提现手继续
        $fee = DB::table('admin_options')->where('key','=','one_time_draw')->value('value');
        //获取用户输入的金额
        $balance = $request->input('money')*100;
        //获取银行卡的信息
        $bankInfo = $id?Bank::find($id):[];
        //获取当前用户信息
        $business = Auth::id();
        //获取当前用户的余额
        $busCount = BusCount::where('business_code',$business)->first();
        $userInfo = $business?User::find($business):[];

        //效验支付密码
        if($userInfo['paypassword']==null||$userInfo['paypassword']==''){
            return ["msg"=>'您还没有设置支付密码，请点击右上角进入设置','status'=>0];
        }else if($balance>$busCount['balance']){
            return ['msg'=>'余额不足，不能提现！','status'=>0];
        }else if(md5(md5(HttpFilter($pwd)))!=$userInfo['paypassword']){
            return ['msg'=>'提现密码不正确！','statuc'=>0];
        }else{
            $bool= $this->lock($business);
            if($bool==true){
                //开启事物
                DB::beginTransaction();
                try{
                    $busCon = BusCount::onWriteConnection()->where('business_code',$business)->lockForUpdate()->first();
                    if((int)$busCon['balance']<(int)$request->input('money')){
                        $this->unlock($business);
                        DB::rollBack();
                        return ['msg'=>'您输入的金额大于余额！请重新输入','status'=>0];
                    }else{
                        $num = BusCount::where('business_code',$business)->decrement('balance',(int)$balance);
                        if($num){
                            $count = DB::table('business_withdraw')->insert(['order_sn'=>HttpFilter($order_sn),'business_code'=>$business,'name'=>HttpFilter($bankInfo['name']),'deposit_name'=>HttpFilter($bankInfo['deposit_name']),'deposit_card'=>HttpFilter($bankInfo['deposit_card']),'money'=>(int)$balance,'tradeMoney'=>(int)$balance-(int)$fee,'creatime'=>time(),'feemoney'=>(int)$fee]);
                            if($count){
                                //获取当前周
                                $week = computeWeek(time(),false);
                                $bill = new Billflow();
                                $bill->setTable('business_billflow_'.$week);
                                $res = $bill->insert(['order_sn'=>HttpFilter($order_sn),'score'=>-(int)$balance-(int)$fee,'tradeMoney'=>(int)$balance,'business_code'=>$business,'status'=>3,'remark'=>'商户提现扣除','creatime'=>time()]);
                                if($res){
                                    DB::commit();
                                    $this->unlock($business);
                                    return ['msg'=>'申请成功！请您耐心稍等','status'=>1];
                                }else{
                                    DB::rollBack();
                                    $this->unlock($business);
                                    return ['msg'=>'申请失败！请重新填写信息','status'=>0];
                                }
                            }else{
                                DB::rollBack();
                                $this->unlock($business);
                                return ['msg'=>'申请失败！请重新填写信息','status'=>0];
                            }
                        }else{
                            $this->unlock($business);
                            DB::rollBack();
                            return ['发现错误，请联系管理员！','status'=>0];
                        }
                    }
                }catch (Exception $e) {
                    DB::rollBack();
                    $this->unlock($business);
                    return ['msg'=>'发生异常！事物进行回滚！','status'=>0];
                }
            }else{
                return ['msg'=>' 请忽重复提交数据！','status'=>0];
            }
        }
    }
    /**
     * 个人信息
     */
    public function userInfo(){
        $id = Auth::id();
        $info = $id?User::find($id):[];
        return view('buswithdraw.userinfo',['userinfo'=>$info]);
    }
    /**
     * 效验旧密码
     */
    public function valPwd(StoreRequest $request){
        //用户输入密码
        $pwd = $request->input('oldpwd');
        $id = Auth::id();
        $userInfo = $id?User::find($id):[];
        if(!App::make('hash')->check(HttpFilter($pwd),HttpFilter($userInfo['password']))){
            return ['msg'=>'旧密码不正确！','status'=>1];
        }
    }
    /**
     * 修改密码
     */
    public function resPwd(StoreRequest $request){
        //用户输入的旧密码
        $oldpwd = $request->input('oldpwd');
        //用户输入的新密码
        $pwd = $request->input('pwd');
        $id = Auth::id();
        $userInfo = $id?User::find($id):[];
        if(!App::make('hash')->check(HttpFilter($oldpwd),$userInfo['password'])){
            return ['msg'=>'旧密码不正确','status'=>0];
        }else{
            $count = DB::table('business')->where('business_code',$id)->update(['password'=>bcrypt(HttpFilter($pwd)),'updatetime'=>time()]);
            if ($count){
                return ['msg'=>'修改成功！','status'=>1];
            }else{
                return ['msg'=>'修改失败！','status'=>0];
            }
        }
    }
    /**
     * 效验原支付密码
     */
    public function valPaypwd(StoreRequest $request){
        //旧密码
        $oldpaypwd = $request->input('oldpaypwd');
        $id = Auth::id();
        $userInfo = $id?User::find($id):[];
        if(md5(md5(HttpFilter($oldpaypwd)))!=$userInfo['paypassword']){
            return ['msg'=>'旧密码错误！','status'=>1];
        }
    }
    /**
     * 修改支付密码
     */
    public function resPaypwd(StoreRequest $request){
        //旧密码
        $oldpaypwd = $request->input('oldpaypwd');
        //新密码
        $paypwd = $request->input('paypwd');
        $id = Auth::id();
        $userInfo = $id?User::find($id):[];
        if(md5(md5(HttpFilter($oldpaypwd)))!=$userInfo['paypassword']){
            return ['msg'=>'旧密码错误！'];
        }else{
            $count = DB::table('business')->where('business_code',$id)->update(['paypassword'=>md5(md5(HttpFilter($paypwd))),'updatetime'=>time()]);
            if($count){
                return ['msg'=>'修改成功！','status'=>1];
            }else{
                return ['msg'=>'修改失败！','status'=>0];
            }
        }
    }
    /**
     * 修改个人资料
     */
    public function resInfo(StoreRequest $request){
        //获取数据
        $nickname = $request->input('nickname');
        //获取当前用户
        $id = Auth::id();
        $count = User::where('business_code',$id)->update(['nickname'=>HttpFilter($nickname)]);
        if($count){
            return ['msg'=>'修改成功！','status'=>1];
        }else{
            return ['msg'=>'修改失败！','status'=>0];
        }
    }
    /**
     * 设置支付密码
     */
    public function setPayPwd(StoreRequest $request){
        //获取支付密码
        $paypassword = $request->input('paypassword');
        //获取当前的认证用户
        $business = Auth::id();
        $userInfo = $business?User::find($business):[];
        if($userInfo['paypassword']!=null||$userInfo['paypassword']!=''){
            return ['msg'=>'错误！','msg'=>0];
        }else{
            $count = User::where('business_code',$business)->update(['paypassword'=>md5(md5(HttpFilter($paypassword)))]);
            if($count){
                return ['msg'=>'设置成功！','status'=>1];
            }else{
                return ['msg'=>'设置失败！','status'=>0];
            }
        }
    }
}