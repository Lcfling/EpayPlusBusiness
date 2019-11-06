<div class="main-layout-header">
    <div class="menu-btn" id="hideBtn">
        <a href="javascript:;">
            <span class="iconfont">&#xe60e;</span>
        </a>
    </div>
    <ul class="layui-nav" lay-filter="rightNav">
        <li class="layui-nav-item">
            <div class="addBtn hidden-xs" data-desc="管理员信息" data-url="{{url('/business/buswithdraw/userinfo')}}">&nbsp;<i class="layui-icon">&#xe612;</i>&nbsp;商户&nbsp;</div>
        </li>
        <li class="layui-nav-item"><a href="{{url('/business/logout')}}">退出</a></li>
    </ul>
</div>