<?php
/**
 * 用户登陆
 *
 * @author      fzs
 * @Time: 2017/07/14 15:57
 * @version     1.0 版本号
 */
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Business\BaseController;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
class LoginController extends BaseController
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */
    use AuthenticatesUsers {authenticated as oriAuthenticated;}
    use AuthenticatesUsers {login as doLogin;}

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/business/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
    public function login(Request $request)
    {
        //判断账号是否存在
        $count = User::where('account','=',$request->input('account'))->count();
        if($count==0){
            return redirect('/business/login')->withErrors([trans('fzs.login.false_account')]);
        }
        $user = new User();
        if(!$this->verifyGooglex($request->input('ggkey'),HttpFilter($request->input('account')),$user)){
            return redirect('/business/login')->withErrors([trans('fzs.login.false_ggkey')]);
        }
        if($request->input('verity')==session('code'))return $this->doLogin($request);
        else return redirect('/business/login')->withErrors([trans('fzs.login.false_verify')]);
    }
    public function username()
    {
        return 'account';
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        return redirect('/business/');
    }

    protected function authenticated(Request $request, $user)
    {
       // Log::addLogs(trans('fzs.login.login_info'),'/business/login',$user->id);
        return $this->oriAuthenticated($request, $user);
    }

}
