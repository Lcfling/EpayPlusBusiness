<?php
/**
 * 用户登陆过后首页以及一些公共方法
 *
 * @author      fzs
 * @Time: 2017/07/14 15:57
 * @version     1.0 版本号
 */
namespace App\Http\Controllers\Business;
use App\Models\Admin;
use App\Models\Billflow;
use App\Models\BusCount;
use App\Models\Buswithdraw;
use App\Models\Order;
use App\Models\User;
use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
//use App\Http\Controllers\Controller;
class HomeController extends BaseController
{
    /**
     * 后台首页
     */
    public function index() {
        $menu = new Admin();
        return view('business.index',['menus'=>$menu->menus(),'mid'=>$menu->getMenuId(),'parent_id'=>$menu->getParentMenuId()]);
    }
    /**
     * 验证码
     */
    public function verify(){
        $phrase = new PhraseBuilder;
        $code = $phrase->build(4);
        $builder = new CaptchaBuilder($code, $phrase);
        $builder->setBackgroundColor(255, 255, 255);
        $builder->build(130,40);
        $phrase = $builder->getPhrase();
        Session::flash('code', $phrase); //存储验证码
        return response($builder->output())->header('Content-type','image/jpeg');
    }


    /**
     * 欢迎首页
     */
    public function welcome(){
        //获取当前登陆的用户
        $busines_code = Auth::id();
        //获取当前周
        $time = strtotime(date('Y-m-d'));
        $end = strtotime('+1day',$time);
        $week = computeWeek($time,false);
        //获取今日订单数
        $order = new Order();
        $order->setTable('order_'.$week);
        $sql = $order->where('business_code','=',$busines_code);
        $orderNum = $sql->whereBetween('creatime',[$time,$end])->count();

        //今日成功订单数
        $orderCount = $sql->whereBetween('creatime',[$time,$end])->where('status','=',1)->count();

        //获取今日总收入
        $orderMoney = $sql->where('status','=',1)->whereBetween('creatime',[$time,$end])->sum('tradeMoney');
        if($orderMoney==0){
            $orderMoney=0;
        }else{
            $orderMoney=$orderMoney/100;
        }
        //获取账户可提现余额
        $balance = BusCount::where('business_code','=',$busines_code)->first()->value('balance');
        if($balance==0){
            $balance=0;
        }else{
            $balance=$balance/100;
        }
        //获取今日未付订单数
        $orderNoPay = $sql->whereBetween('creatime',[$time,$end])->where('status','=',0)->count();
        //获取累计成交金额
        $account = BusCount::where('business_code','=',$busines_code)->first()->value('tol_sore');
        if($account==0){
            $account=0;
        }else{
            $account=$account/100;
        }
        //获取累计提现成功
        $drawCount = Buswithdraw::where('business_code','=',$busines_code)->where('status','=',1)->sum('money');
        if($drawCount==0){
            $drawCount=0;
        }else{
            $drawCount=$drawCount/100;
        }
        //<!-- 统计图 -->
        //x轴近七天
        $week=$this->get_weeks(time(),"m-d");
        $x = array_values($week);
        $data['x']=json_encode($x);

        //近七天时间戳数组
        $date = $this->get_weeks(time(),'Y-M-d');
        for ($i=1; $i<=7; $i++){
            $date[$i] = strtotime($date[$i]);
        }
        //y1全部订单
        $all_order[1]=$sql->whereBetween('creatime',[$date[1],$date[2]])->count('order_sn');
        $all_order[2]=$sql->whereBetween('creatime',[$date[2],$date[3]])->count('order_sn');
        $all_order[3]=$sql->whereBetween('creatime',[$date[3],$date[4]])->count('order_sn');
        $all_order[4]=$sql->whereBetween('creatime',[$date[4],$date[5]])->count('order_sn');
        $all_order[5]=$sql->whereBetween('creatime',[$date[5],$date[6]])->count('order_sn');
        $all_order[6]=$sql->whereBetween('creatime',[$date[6],$date[7]])->count('order_sn');
        $all_order[7]=$orderNum;
        $y1=array_values($all_order);
        $data['y1']=json_encode($y1);

        //y2成功订单
        $done_order[1]=$sql->whereBetween('creatime',[$date[1],$date[2]])->where('status','=',1)->count('order_sn');
        $done_order[2]=$sql->whereBetween('creatime',[$date[2],$date[3]])->where('status','=',1)->count('order_sn');
        $done_order[3]=$sql->whereBetween('creatime',[$date[3],$date[4]])->where('status','=',1)->count('order_sn');
        $done_order[4]=$sql->whereBetween('creatime',[$date[4],$date[5]])->where('status','=',1)->count('order_sn');
        $done_order[5]=$sql->whereBetween('creatime',[$date[5],$date[6]])->where('status','=',1)->count('order_sn');
        $done_order[6]=$sql->whereBetween('creatime',[$date[6],$date[7]])->where('status','=',1)->count('order_sn');
        $done_order[7]=$orderCount;
        $y2=array_values($done_order);
        $data['y2']=json_encode($y2);
        return view('admin.welcome',['data'=>$data,'orderNum'=>$orderNum,'orderCount'=>$orderCount,'orderMoney'=>$orderMoney,'balance'=>$balance,'orderNoPay'=>$orderNoPay,'score'=>$account,'drawMoney'=>$drawCount]);
    }
    /**
     * 排序
     */
    public function changeSort(Request $request){
        $data = $request->all();
        if(is_numeric($data['id'])){
            $res = DB::table('admin_'.$data['name'])->where('id',$data['id'])->update(['order'=>$data['val']]);
            if($res)return $this->resultJson('fzs.common.success', 1);
            else return $this->resultJson('fzs.common.fail', 0);
        }else{
            return $this->resultJson('fzs.common.wrong', 0);
        }
    }
    /**
     * 获取系统信息
     */
    protected function getSysInfo(){
        $sys_info['ip'] 			= GetHostByName($_SERVER['SERVER_NAME']);
        $sys_info['phpv']           = phpversion();
        $sys_info['web_server']     = $_SERVER['SERVER_SOFTWARE'];
        $sys_info['time']           = date("Y-m-d H:i:s");
        $sys_info['domain'] 		= $_SERVER['HTTP_HOST'];
        $mysqlinfo = DB::select("SELECT VERSION() as version");
        $sys_info['mysql_version']  = $mysqlinfo[0]->version;
        return $sys_info;
    }
    /**
     * 最近7日
     */
    public function get_weeks($time, $format){
        $time = $time != '' ? $time : time();
        //组合数据
        $date = [];
        for ($i=1; $i<=7; $i++){
            $date[$i] = date($format ,strtotime( '+' . $i-7 .' days', $time));
        }
        return $date;
    }
}
