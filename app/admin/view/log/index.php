<div class="page-toolbar">
    <div class="layui-btn-group fl">
        <a data-href="{:url('del?table=admin_log')}" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="aicon ai-jinyong"></i>删除</a>
        <a href="{:url('clear')}" refresh="yes" class="layui-btn layui-btn-primary j-ajax confirm "><i class="aicon ai-clear"></i>清空所有</a>
    </div>
</div>
<form id="pageListForm">
<div class="layui-form">
    <table class="layui-table mt10" lay-even="" lay-skin="row">
        <colgroup>
            <col width="50">
        </colgroup>
        <thead>
            <tr>
                <th><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                <th>操作者</th>
                <th>操作名称</th>
                <th>操作地址</th>
                <th>操作备注</th>
                <th>操作统计</th>
                <th>IP地址</th>
                <th>最近访问</th>
            </tr> 
        </thead>
        <tbody>
            {volist name="data_list" id="vo"}
            <tr>
                <td><input type="checkbox" name="ids[]" class="layui-checkbox checkbox-ids" value="{$vo['id']}" lay-skin="primary"></td>
                <td><a href="{:url('?uid='.$vo['uid'])}">{$vo['username']['nick']}</a></td>
                <td>{$vo['title']}</td>
                <td><a href="{:url($vo['url'])}">{$vo['url']}</a></td>
                <td>{$vo['remark']}</td>
                <td>{$vo['count']}</td>
                <td>{$vo['ip']}</td>
                <td>{$vo['mtime']}</td>
            </tr>
            {/volist}
        </tbody>
    </table>
    {$pages}
</div>
</form>
{include file="block/layui" /}