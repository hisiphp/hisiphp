<form class="page-list-form">
<div class="layui-btn-group">
    <a href="{:url('add?group='.input('param.group'))}" class="layui-btn layui-btn-primary"><i class="aicon ai-tianjia"></i>添加</a>
    <a data-href="{:url('status?table=admin_config&val=1')}" class="layui-btn layui-btn-primary j-page-btns"><i class="aicon ai-qiyong"></i>启用</a>
    <a data-href="{:url('status?table=admin_config&val=0')}" class="layui-btn layui-btn-primary j-page-btns"><i class="aicon ai-jinyong1"></i>禁用</a>
    <a data-href="{:url('del')}" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="aicon ai-jinyong"></i>删除</a>
</div>
<div class="layui-form">
    <table class="layui-table mt10" lay-even="" lay-skin="row">
        <colgroup>
            <col width="50">
        </colgroup>
        <thead>
            <tr>
                <th><input type="checkbox" name="" lay-skin="primary" lay-filter="allChoose"></th>
                <th>标识</th>
                <th>标题</th>
                <th>类型</th>
                <th>状态</th>
                <th>排序</th>
                <th>操作</th>
            </tr> 
        </thead>
        <tbody>
            {volist name="data_list" id="v"}
            <tr>
                <td><input type="checkbox" name="ids[{$key}]" value="{$v['id']}" class="layui-checkbox checkbox-ids" lay-skin="primary"></td>
                <td>{$v['name']}</td>
                <td>{$v['title']}</td>
                <td>{:form_type($v['type'])}</td>
                <td><input type="checkbox" name="status" {if condition="$v['status'] eq 1"}checked=""{/if} value="{$v['status']}" lay-skin="switch" lay-filter="switchStatus" lay-text="正常|关闭" data-href="{:url('status?table=admin_config&ids='.$v['id'])}"></td>
                <td><input type="text" class="layui-input j-ajax-input input-sort" onkeyup="value=value.replace(/[^\d]/g,'')" value="{$v['sort']}" data-value="{$v['sort']}" data-href="{:url('sort?table=admin_config&ids='.$v['id'])}"></td>
                <td>
                    <div class="layui-btn-group">
                        <div class="layui-btn-group">
                        <a href="{:url('edit?id='.$v['id'])}" class="layui-btn layui-btn-primary layui-btn-small"><i class="layui-icon">&#xe642;</i></a>
                        <a data-href="{:url('del?ids='.$v['id'])}" class="layui-btn layui-btn-primary layui-btn-small j-tr-del"><i class="layui-icon">&#xe640;</i></a>
                        </div>
                    </div>
                </td>
            </tr>
            {/volist}
        </tbody>
    </table>
    {$pages}
</div>
</form>
{include file="block/layui" /}