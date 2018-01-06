<form class="page-list-form">
<div class="page-toolbar">
    <div class="layui-btn-group fl">
        <a href="{:url('addLevel')}" class="layui-btn layui-btn-primary"><i class="aicon ai-tianjia"></i>添加</a>
        <a data-href="{:url('status?val=1')}" class="layui-btn layui-btn-primary j-page-btns"><i class="aicon ai-qiyong"></i>启用</a>
        <a data-href="{:url('status?val=0')}" class="layui-btn layui-btn-primary j-page-btns"><i class="aicon ai-jinyong1"></i>禁用</a>
        <a data-href="{:url('delLevel')}" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="aicon ai-jinyong"></i>删除</a>
    </div>
</div>
<div class="layui-form">
    <table class="layui-table mt10" lay-even="" lay-skin="row">
        <colgroup>
            <col width="50">
        </colgroup>
        <thead>
            <tr>
                <th><input type="checkbox" name="" lay-skin="primary" lay-filter="allChoose"></th>
                <th>等级名称</th>
                <th>等级简介</th>
                <th>折扣%</th>
                <th>最小经验值</th>
                <th>最大经验值</th>
                <th>创建时间</th>
                <th>状态</th>
                <th>默认</th>
                <th>操作</th>
            </tr> 
        </thead>
        <tbody>
            {volist name="data_list" id="v"}
            <tr>
                <td><input type="checkbox" name="ids[]" value="{$v['id']}" class="layui-checkbox checkbox-ids" lay-skin="primary"></td>
                <td>{$v['name']}</td>
                <td>{$v['intro']}</td>
                <td>{$v['discount']}</td>
                <td>{$v['min_exper']}</td>
                <td>{$v['max_exper']}</td>
                <td>{$v['ctime']}</td>
                <td><input type="checkbox" name="status" {if condition="$v['status'] eq 1"}checked=""{/if} value="{$v['status']}" lay-skin="switch" lay-filter="switchStatus" lay-text="正常|关闭" data-href="{:url('status?table=admin_member_level&ids='.$v['id'])}"></td>
                <td><input type="checkbox" name="default" {if condition="$v['default'] eq 1"}checked=""{/if} value="{$v['default']}" lay-skin="switch" lay-filter="switchStatus" lay-text="正常|关闭" data-href="{:url('setDefault?id='.$v['id'])}"></td>
                <td>
                    <div class="layui-btn-group">
                        <div class="layui-btn-group">
                        <a href="{:url('editLevel?id='.$v['id'])}" class="layui-btn layui-btn-primary layui-btn-small"><i class="layui-icon">&#xe642;</i></a>
                        <a data-href="{:url('delLevel?ids='.$v['id'])}" class="layui-btn layui-btn-primary layui-btn-small j-tr-del"><i class="layui-icon">&#xe640;</i></a>
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