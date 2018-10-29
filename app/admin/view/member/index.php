<style type="text/css">
    .layui-table-body{overflow-x:auto;}
    .layui-table-cell{font-size:12px;}
</style>
<div class="page-toolbar">
    <div class="page-filter fr">
        <form class="layui-form layui-form-pane" action="{:url()}" id="hisiSearch" method="get">
        <div class="layui-form-item">
            <label class="layui-form-label">搜索</label>
            <div class="layui-input-inline">
                <input type="text" name="keyword" value="" lay-verify="required" placeholder="用户名、邮箱、手机、昵称" autocomplete="off" class="layui-input">
            </div>
        </div>
        </form>
    </div>
    <div class="layui-btn-group fl">
        <a href="{:url('add')}" class="layui-btn layui-btn-primary layui-icon layui-icon-add-circle-fine">&nbsp;添加</a>
        <a data-href="{:url('status?table=admin_member&val=1')}" class="layui-btn layui-btn-primary j-page-btns layui-icon layui-icon-play" data-table="dataTable">&nbsp;启用</a>
        <a data-href="{:url('status?table=admin_member&val=0')}" class="layui-btn layui-btn-primary j-page-btns layui-icon layui-icon-pause" data-table="dataTable">&nbsp;禁用</a>
        <a data-href="{:url('del?table=admin_member')}" class="layui-btn layui-btn-primary j-page-btns confirm layui-icon layui-icon-close red">&nbsp;删除</a>
    </div>
</div>
<form id="pageListForm">
<div class="layui-form">
    <table id="dataTable"></table>
    <script type="text/html" id="usernameTpl">
        <div style="line-height:18px;">
        <img src="{{ d.avatar ? d.avatar : '__ADMIN_IMG__/avatar.png' }}" width="60" height="60" class="fl">
        <p class="ml10 fl"><strong class="mcolor">{{ d.username }} ({{ d.nick }})</strong><br>手机：{{ d.mobile }}<br>邮箱：{{ d.email }}</p></div>
    </script>
    <script type="text/html" id="statusTpl">
        <input type="checkbox" name="status" value="{{ d.status }}" lay-skin="switch" lay-filter="switchStatus" lay-text="正常|关闭" {{ d.status == 1 ? 'checked' : '' }} data-href="{:url('status')}?table=admin_member&id={{ d.id }}">
    </script>
    <script type="text/html" title="操作按钮模板" id="buttonTpl">
        <a href="{:url('edit')}?id={{ d.id }}" class="layui-btn layui-btn-xs layui-btn-normal">修改</a><a href="{:url('del')}?id={{ d.id }}" class="layui-btn layui-btn-xs layui-btn-danger j-tr-del">删除</a>
    </script>
</div>
</form>
{include file="block/layui" /}
<script type="text/javascript">
    layui.use(['table'], function() {
        var table = layui.table;
        table.render({
            elem: '#dataTable'
            ,url: '{:url()}' //数据接口
            ,page: true //开启分页
            ,limit: 20
            ,text: {
                none : '暂无相关数据'
            }
            ,cols: [[ //表头
                {type:'checkbox'}
                ,{field: 'username', title: '账号', templet: '#usernameTpl'}
                ,{field: 'exper', title: '等级&经验', width: 120, templet: function(d){
                    return d.has_level.name+'<br>经验值：'+d.exper;
                }}
                ,{field: 'money', title: '资金', width: 100, templet: function(d) {
                    return '余额：'+d.money+'<br>冻结：'+d.frozen_money;
                }}
                ,{field: 'integral', title: '注册&登录', width: 180, templet: function(d) {
                    return '注册：'+d.ctime+'<br>登录：'+d.last_login_time;
                }}
                ,{field: 'status', title: '状态', width: 100, templet: '#statusTpl'}
                ,{title: '操作', width: 120, templet: '#buttonTpl'}
            ]]
        });
    });
</script>