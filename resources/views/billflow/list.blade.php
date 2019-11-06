@section('title', '提现列表')
@section('header')
    <div class="layui-inline">
    <button class="layui-btn layui-btn-small layui-btn-warm freshBtn"><i class="layui-icon">&#x1002;</i></button>
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
        </colgroup>
        <thead>
        <tr>
            <th class="hidden-xs">订单ID</th>
            <th class="hidden-xs">订单号</th>
            <th class="hidden-xs">积分</th>
            <th class="hidden-xs">商户号</th>
            <th class="hidden-xs">状态</th>
            <th class="hidden-xs">支付类型</th>
            <th class="hidden-xs">备注</th>
            <th class="hidden-xs">创建时间</th>
        </tr>
        </thead>
        <tbody>
            <tr>
                <td class="hidden-xs">{{$list['order_id']}}</td>
                <td class="hidden-xs">{{$list['order_sn']}}</td>
                <td>{{$list['score']}}</td>
                <td class="hidden-xs">{{$list['business_code']}}</td>
                <td class="hidden-xs">
                    @if($list['status']==1)
                        支付
                    @elseif($list['status']==2)
                        利润
                    @endif
                </td>
                <td class="hidden-xs">{{$list['paycode']}}</td>
                <td class="hidden-xs">{{$list['remark']}}</td>
                <td class="hidden-xs">{{$list['creattime']}}</td>
            </tr>
        </tbody>
    </table>
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
        });
    </script>
@endsection
@extends('common.list')
