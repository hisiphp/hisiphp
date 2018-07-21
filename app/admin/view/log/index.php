<div class="page-toolbar">
    <div class="layui-btn-group fl">
        <a data-href="{:url('del?table=admin_log')}" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="aicon ai-jinyong"></i>删除</a>
        <a href="{:url('clear')}" refresh="yes" class="layui-btn layui-btn-primary j-ajax confirm "><i class="aicon ai-clear"></i>清空所有</a>
    </div>
</div>
<table id="dataTable"></table>
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
                ,{field: 'uid', title: '操作者', templet: function(d) {
                    return '<a href="{:url()}?uid='+ d.uid +'&page=1" class="hisi-table-a-filter">' + d.user.nick + '</a>';
                }}
                ,{field: 'title', title: '操作名称'}
                ,{field: 'url', title: '操作地址', templet: function(d) {
                    return '<a href="' + d.url + '">' + d.url + '</a>';
                }}
                ,{field: 'remark', title: '操作备注'}
                ,{field: 'count', title: '操作统计'}
                ,{field: 'ip', title: 'IP地址'}
                ,{field: 'mtime', title: '最近访问'}
            ]]
        });
    });
</script>