@section('title', '订单列表')
@section('header')
    <div class="layui-inline">
    <button class="layui-btn layui-btn-small layui-btn-warm freshBtn"><i class="layui-icon">&#x1002;</i></button>
    </div>
    <div class="layui-inline">
        <input type="text" lay-verify="out_order_sn" value="{{ $input['out_order_sn'] or '' }}" name="out_order_sn" placeholder="请输入订单号" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-inline">
        <select name="status">
            <option value="">请选择账单类型</option>
            <option value="0" {{isset($input['status'])&&$input['status']=='0'?'selected':''}}>未支付</option>
            <option value="1" {{isset($input['status'])&&$input['status']=='1'?'selected':''}}>支付成功</option>
            <option value="2" {{isset($input['status'])&&$input['status']=='2'?'selected':''}}>过期</option>
            <option value="3" {{isset($input['status'])&&$input['status']=='3'?'selected':''}}>取消</option>
        </select>
    </div>
    <div class="layui-inline">
        <input class="layui-input" name="creatime" placeholder="开始日期" lay-verify="creatime"  onclick="layui.laydate({elem: this, festival: true})" value="{{ $input['creatime'] or '' }}">
    </div>
    <div class="layui-inline">
        <input class="layui-input" name="pay_time" placeholder="结束日期" lay-verify="pay_time" onclick="layui.laydate({elem: this, festival: true})" value="{{ $input['pay_time'] or '' }}">
    </div>
    <div class="layui-inline">
        <button class="layui-btn layui-btn-normal" lay-submit lay-filter="formDemo">搜索</button>
        <button class="layui-btn layui-btn-normal" id="res">重置</button>
        <button class="layui-btn layui-btn-normal" id="export" name="export" lay-submit lay-filter="formDemo" value="export">导出</button>
    </div>
@endsection
@section('table')
    <table class="layui-table" lay-even lay-skin="nob">
        <colgroup>
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
        </colgroup>
        <thead>
        <tr>
            <th class="hidden-xs">商户订单号</th>
            <th class="hidden-xs">商户号</th>
            <th class="hidden-xs">付款金额</th>
            <th class="hidden-xs">收款金额</th>
            <th class="hidden-xs">请求时间</th>
            <th class="hidden-xs">平台订单号</th>
            <th class="hidden-xs">完成时间</th>
            <th class="hidden-xs">通知时间</th>
            <th class="hidden-xs">到账金额</th>
            <th class="hidden-xs">账单状态</th>
        </tr>
        </thead>
        <tbody>
        @foreach($list as $info)
            <tr>
                <td class="hidden-xs">{{$info['out_order_sn']}}</td>
                <td class="hidden-xs">{{$info['business_code']}}</td>
                <td>{{$info['tradeMoney']}}</td>
                <td class="hidden-xs">{{$info['sk_money']}}</td>
                <td class="hidden-xs">{{$info['creatime']}}</td>
                <td class="hidden-xs">{{$info['order_sn']}}</td>
                <td class="hidden-xs">{{$info['pay_time']}}</td>
                <td class="hidden-xs">{{$info['callback_time']}}</td>
                <td class="hidden-xs">{{$info['tradeMoney']}}</td>
                <td class="hidden-xs">
                    @if($info['status']==0)
                        未支付
                    @elseif($info['status']==1)
                        支付成功
                    @elseif($info['status']==2)
                        过期
                    @else
                        取消
                    @endif
                </td>
            </tr>
            @if(!$list[0])
                <tr><td colspan="6" style="text-align: center;color: orangered;">暂无数据</td></tr>
            @endif
        @endforeach
        </tbody>
    </table>
    <div class="page-wrap">
        {{$list->render()}}
    </div>
@endsection
@section('js')
    <script>
        layui.use(['form', 'jquery','laydate', 'layer'], function() {
            var form = layui.form(),
                $ = layui.jquery,
                laydate = layui.laydate,
                layer = layui.layer
            ;
            laydate({istoday: true});
            form.render();
            form.on('submit(formDemo)', function(data) {
            });
            $('#res').click(function () {
                $("input[name='out_order_sn']").val('');
                $("select[name='status']").val('');
                $("input[name='creatime']").val('');
                $("input[name='pay_time']").val('');
                $('form').submit();
            });
        });
    </script>
@endsection
@extends('common.list')
