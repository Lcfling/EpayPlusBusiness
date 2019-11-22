@section('title', '流水列表')
@section('header')
    <div class="layui-inline">
    <button class="layui-btn layui-btn-small layui-btn-warm freshBtn"><i class="layui-icon">&#x1002;</i></button>
    </div>
    <div class="layui-inline">
        <input class="layui-input" name="creattime" placeholder="创建时间" lay-verify="creattime"  onclick="layui.laydate({elem: this, festival: true})" value="{{ $input['creattime'] or '' }}">
    </div>
    <div class="layui-inline">
        <button class="layui-btn layui-btn-normal" lay-submit lay-filter="formDemo">搜索</button>
        <button class="layui-btn layui-btn-normal" id="res">重置</button>
        <button class="layui-btn layui-btn-normal" name="export" value="export" lay-submit lay-filter="formDemo">导出</button>
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
        </colgroup>
        <thead>
        <tr>
            <th class="hidden-xs">订单号</th>
            <th class="hidden-xs">提现金额</th>
            <th class="hidden-xs">实际到账金额</th>
            <th class="hidden-xs">商户号</th>
            <th class="hidden-xs">状态</th>
            <th class="hidden-xs">备注</th>
            <th class="hidden-xs">创建时间</th>
        </tr>
        </thead>
        <tbody>
        @foreach($list as $info)
            <tr>
                <td class="hidden-xs">{{$info['order_sn']}}</td>
                <td>{{$info['score']}}</td>
                <td class="hidden-xs">{{$info['tradeMoney']}}</td>
                <td class="hidden-xs">{{$info['business_code']}}</td>
                <td class="hidden-xs">
                    @if($info['status']==1)
                        支付
                    @elseif($info['status']==2)
                        利润
                    @elseif($info['status']==3)
                        提现
                    @endif
                </td>
                <td class="hidden-xs">{{$info['remark']}}</td>
                <td class="hidden-xs">{{$info['creatime']}}</td>
            </tr>
        @endforeach
        </tbody>
        <div class="page-wrap">
            {{$list->render()}}
        </div>
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
            $('#res').click(function () {
                $("input[name='creattime']").val('');
                $('form').submit();
            });
        });
    </script>
@endsection
@extends('common.list')
