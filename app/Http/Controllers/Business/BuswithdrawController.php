<?php


namespace App\Http\Controllers\Business;




use App\Http\Requests\StoreRequest;
use App\Models\Bank;
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
        $data = Buswithdraw::where($map)->paginate(5)->appends($request->all());
        foreach ($data as $key =>$value){
            $data[$key]['money'] = $data[$key]['money']/100;
            $data[$key]['creatime'] =date("Y-m-d H:i:s",$value["creatime"]);
            if($data[$key]['endtime']!=''){
                $data[$key]['endtime'] =date("Y-m-d H:i:s",$value["endtime"]);
            }
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
        $busCount = BusCount::where('business_id',$business)->first();
        $busCount['balance']=$busCount['balance']/100;
        return view('buswithdraw.edit',['info'=>$info,'id'=>$id,'banklist'=>$data,'balance'=>$busCount]);
    }
    /**
     * 添加提现申请
     */
    public function store(StoreRequest $request){
        $order_sn = time().mt_rand(100000,999999);
        //获取银行卡的id
        $id = $request->input('bank_card');
        //获取输入的支付密码
        $pwd = $request->input('paypassword');
        //获取用户输入的金额
        $balance = $request->input('money')*100;
        //获取银行卡的信息
        $bankInfo = $id?Bank::find($id):[];
        //获取当前用户信息
        $business = Auth::id();
        //获取当前用户的余额
        $busCount = BusCount::where('business_id',$business)->first();
        $userInfo = $business?User::find($business):[];
        //效验支付密码
        if($balance>$busCount['balance']){
            return ['msg'=>'您输入的金额大于余额！请重新输入','status'=>0];
        }else if(md5(md5(HttpFilter($pwd)))!=$userInfo['paypassword']){
            return ['msg'=>'提现密码不正确！','statuc'=>0];
        }else{
            $bool= $this->lock($business);
            if($bool==true){
                //开启事物
                DB::beginTransaction();
                try{
                    BusCount::where('business_id',$business)->decrement('balance',$balance);
                    $count = DB::table('business_withdraw')->insert(['order_sn'=>$order_sn,'business_code'=>$business,'name'=>$bankInfo['name'],'deposit_name'=>$bankInfo['deposit_name'],'deposit_card'=>$bankInfo['deposit_card'],'money'=>$balance,'creatime'=>time()]);
                    if($count){
                        DB::commit();
                        $this->unlock($business);
                        return ['msg'=>'申请成功！请您耐心稍等','status'=>1];
                    }else{
                        DB::rollBack();
                        $this->unlock($business);
                        return ['msg'=>'申请失败！请重新填写信息','status'=>0];
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
        return view('buswithdraw.userInfo',['userinfo'=>$info]);
    }
    /**
     * 效验旧密码
     */
    public function valPwd(StoreRequest $request){
        //用户输入密码
        $pwd = $request->input('oldpwd');
        $id = Auth::id();
        $userInfo = $id?User::find($id):[];
        if(!App::make('hash')->check(HttpFilter($pwd),$userInfo['password'])){
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
}