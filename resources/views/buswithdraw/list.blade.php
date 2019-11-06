@section('title', '提现列表')
@section('header')
    <div class="layui-inline">
    <button class="layui-btn layui-btn-small layui-btn-normal addBtn" data-desc="添加提现申请" data-url="{{url('/business/buswithdraw/0/edit')}}"><i class="layui-icon">&#xe654;</i></button>
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
            <th class="hidden-xs">商户号</th>
            <th class="hidden-xs">姓名</th>
            <th class="hidden-xs">银行卡名称</th>
            <th class="hidden-xs">银行卡号</th>
            <th class="hidden-xs">结算金额</th>
            <th class="hidden-xs">请求时间</th>
            <th class="hidden-xs">结算时间</th>
            <th class="hidden-xs">结算状态</th>
        </tr>
        </thead>
        <tbody>
        @foreach($list as $info)
            <tr>
                <td class="hidden-xs">{{$info['business_code']}}</td>
                <td class="hidden-xs">{{$info['name']}}</td>
                <td>{{$info['deposit_name']}}</td>
                <td class="hidden-xs">{{$info['deposit_card']}}</td>
                <td class="hidden-xs">{{$info['money']}}</td>
                <td class="hidden-xs">{{$info['creatime']}}</td>
                <td class="hidden-xs">{{$info['endtime']}}</td>
                <td class="hidden-xs">
                    @if($info['status']==0)
                        未结算
                    @elseif($info['status']==1)
                        已完成
                    @else
                        已拒绝
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
        });
    </script>
@endsection
@extends('common.list')
