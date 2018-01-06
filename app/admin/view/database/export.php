<div class="page-toolbar">
    <div class="layui-btn-group fl">
        <a data-href="{:url('export')}" class="layui-btn layui-btn-primary j-page-btns">备份数据库</a>
        <a data-href="{:url('optimize')}" class="layui-btn layui-btn-primary j-page-btns">优化数据库</a>
        <a data-href="{:url('repair')}" class="layui-btn layui-btn-primary j-page-btns">修复数据库</a>
    </div>
</div>
<form id="pageListForm" class="layui-form">
    <table class="layui-table mt10" lay-even="" lay-skin="row">
        <colgroup>
            <col width="50">
        </colgroup>
        <thead>
            <tr>
                <th><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                <th>表名</th>
                <th>数据量</th>
                <th>大小</th>
                <th>冗余</th>
                <th>备注</th>
                <th>操作</th>
            </tr> 
        </thead>
        <tbody>
            {volist name="data_list" id="vo"}
            <tr>
                <td><input type="checkbox" name="ids[]" class="layui-checkbox checkbox-ids" value="{$vo['Name']}" lay-skin="primary"></td>
                <td>{$vo['Name']}</td>
                <td>{$vo['Rows']}</td>
                <td>{$vo['Data_length']/1024} kb</td>
                <td>{$vo['Data_free']/1024} kb</td>
                <td>{$vo['Comment']}</td>
                <td> 
                    <div class="layui-btn-group">
                        <a data-href="{:url('optimize?ids='.$vo['Name'])}" class="layui-btn layui-btn-primary layui-btn-small j-ajax">优化</a>
                        <a data-href="{:url('repair?ids='.$vo['Name'])}" class="layui-btn layui-btn-primary layui-btn-small j-ajax">修复</a>
                    </div>
                </td>
            </tr>
            {/volist}
        </tbody>
    </table>
</form>
{include file="block/layui" /}