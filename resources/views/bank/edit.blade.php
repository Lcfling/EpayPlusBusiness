@section('title', '添加银行卡')
@section('content')
    <div class="layui-form-item">
        <label class="layui-form-label">开户姓名：</label>
        <div class="layui-input-block">
            <input type="text" value="{{$info['name'] or ''}}" name="name" required lay-verify="name" placeholder="请输入开户行姓名" autocomplete="off" class="layui-input">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">银行卡号：</label>
        <div class="layui-input-block">
            <input type="text" value="{{$info['deposit_card'] or ''}}" id="bank_idcard" name="deposit_card" required lay-verify="deposit_card" placeholder="请输入银行卡号" autocomplete="off" class="layui-input">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">开户行：</label>
        <div class="layui-input-block">
            <input type="text" value="{{$info['deposit_name'] or ''}}" id="bank_name" name="deposit_name" required readonly="readonly" lay-verify="deposit_name" placeholder="此框为自动填充并且不能为空！" autocomplete="off" class="layui-input">
        </div>
    </div>
@endsection
@section('id',$id)
@section('js')
    <script>
        layui.use(['form','jquery','laypage', 'layer'], function() {
            var form = layui.form(),
                $ = layui.jquery;
            var banklist={!! $banklist!!};//不转义字符
            form.render();
            var layer = layui.layer;
            form.verify({
                name: [/[\u4e00-\u9fa5]{2,30}$/, '请输入正确的姓名'],
                deposit_card: function (value) {
                    if(value==null||value==''){
                        return "银行卡号不能为空！";
                    }else if(value.length<14 && value.length>19){
                        return "银行卡格式有误！";
                    }
                },
                deposit_name:function (value) {
                    if(value==null || value==''){
                        return "开户行不能为空";
                    }
                }
            });
            $("#bank_idcard").blur(function(){
                var value=$(this).val();
                $.post("https://ccdcapi.alipay.com/validateAndCacheCardInfo.json",{cardNo:value,cardBinCheck:'true'},function(res){
                    //console.log(res); //不清楚返回值的打印出来看
                    //{"cardType":"DC","bank":"ICBC","key":"622200****412565805","messages":[],"validated":true,"stat":"ok"}
                    if(res.validated){
                        var name=banklist[res.bank];
                        //console.log(name);
                        $('#bank_name').val(name);
                        $('#bank_name').text(name);
                    }else{
                        layer.msg('银行卡号错误',{icon:5});
                        //setTimeout($("#deposit_card").focus(),1000); //获取焦点
                        $('#bank_name').val('');
                        $('#bank_name').text('');
                        return false;
                    }
                },'json');
            });
            form.on('submit(formDemo)', function(data) {
                $.ajax({
                    url:"{{url('/business/bank')}}",
                    data:$('form').serialize(),
                    type:'post',
                    dataType:'json',
                    success:function(res){
                        if(res.status == 1){
                            layer.msg(res.msg,{icon:6});
                            var index = parent.layer.getFrameIndex(window.name);
                            setTimeout('parent.layer.close('+index+')',2000);
                        }else{
                            layer.msg(res.msg,{shift: 6,icon:5});
                        }
                    },
                    error : function(XMLHttpRequest, textStatus, errorThrown) {
                        layer.msg('网络失败', {time: 1000});
                    }
                });
                return false;
            });
        });
        //银行卡正则校验
        function checkBankno(bankno) {
            var lastNum = bankno.substr(bankno.length - 1, 1);//取出最后一位（与luhm进行比较）
            var first15Num = bankno.substr(0, bankno.length - 1);//前15或18位
            var newArr = [];

            for (var i = first15Num.length - 1; i > -1; i--) { //前15或18位倒序存进数组
                newArr.push(first15Num.substr(i, 1));
            }

            var arrJiShu = []; //奇数位*2的积 <9
            var arrJiShu2 = []; //奇数位*2的积 >9
            var arrOuShu = []; //偶数位数组
            for (var j = 0; j < newArr.length; j++) {
                if ((j + 1) % 2 == 1) {//奇数位
                    if (parseInt(newArr[j]) * 2 < 9)
                        arrJiShu.push(parseInt(newArr[j]) * 2); else
                        arrJiShu2.push(parseInt(newArr[j]) * 2);
                }
                else //偶数位
                    arrOuShu.push(newArr[j]);
            }

            var jishu_child1 = [];//奇数位*2 >9 的分割之后的数组个位数
            var jishu_child2 = [];//奇数位*2 >9 的分割之后的数组十位数
            for (var h = 0; h < arrJiShu2.length; h++) {
                jishu_child1.push(parseInt(arrJiShu2[h]) % 10);
                jishu_child2.push(parseInt(arrJiShu2[h]) / 10);
            }

            var sumJiShu = 0; //奇数位*2 < 9 的数组之和
            var sumOuShu = 0; //偶数位数组之和
            var sumJiShuChild1 = 0; //奇数位*2 >9 的分割之后的数组个位数之和
            var sumJiShuChild2 = 0; //奇数位*2 >9 的分割之后的数组十位数之和
            var sumTotal = 0;
            for (var m = 0; m < arrJiShu.length; m++) {
                sumJiShu = sumJiShu + parseInt(arrJiShu[m]);
            }
            for (var n = 0; n < arrOuShu.length; n++) {
                sumOuShu = sumOuShu + parseInt(arrOuShu[n]);
            }
            for (var p = 0; p < jishu_child1.length; p++) {
                sumJiShuChild1 = sumJiShuChild1 + parseInt(jishu_child1[p]);
                sumJiShuChild2 = sumJiShuChild2 + parseInt(jishu_child2[p]);
            }
            //计算总和
            sumTotal = parseInt(sumJiShu) + parseInt(sumOuShu) + parseInt(sumJiShuChild1) + parseInt(sumJiShuChild2);
            //计算Luhm值
            var k = parseInt(sumTotal) % 10 == 0 ? 10 : parseInt(sumTotal) % 10;
            var luhm = 10 - k;
            if (lastNum == luhm) {
                return true;
            }
            else {
                return false;
            }
        }
    </script>
@endsection
@extends('common.edit')
