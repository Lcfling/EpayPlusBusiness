<?php


namespace App\Http\Controllers\Business;


use App\Http\Requests\StoreRequest;
use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BankController extends BaseController
{
    /**
     * 数据列表
     */
    public function index(Request $request){
        $id = Auth::id();
        $map = array();
        $map['business_code']=$id;
        $data = Bank::where($map)->paginate(2)->appends($request->all());
        foreach ($data as $key =>$value){
            $data[$key]['creatime'] =date("Y-m-d H:i:s",$value["creatime"]);
        }
        return view('bank.list',['list'=>$data,'input'=>$request->all()]);
    }

    /**
     * 编辑页
     */
    public function edit($id=0){
        $info = $id?Bank::find($id):[];
        $bank = config('bank');
        $banklist=json_encode($bank);
        return view('bank.edit',['id'=>$id,'info'=>$info,'banklist'=>$banklist]);
    }
    /**
     * 保存添加数据
     */
    public function store(StoreRequest $request){
        $data = $request->all();
        $id = Auth::id();
        unset($data['_token']);
        $data['business_code']=$id;
        $data['name']=HttpFilter($data['name']);
        $data['deposit_name']=HttpFilter($data['deposit_name']);
        $data['deposit_card']=HttpFilter($data['deposit_card']);
        $data['status']=0;
        $data['creatime']=time();
        $count = Bank::insert($data);
        if ($count){
            return ['msg'=>'添加成功！','status'=>1];
        }else{
            return ['msg'=>'添加失败！','status'=>0];
        }
    }
    /**
     * 删除
     */
    public function destroy($id){
        $count = Bank::where('id','=',$id)->delete();
        if($count){
            return ['msg'=>'删除成功！','status'=>1];
        }else{
            return ['msg'=>'删除失败！','status'=>0];
        }
    }
}