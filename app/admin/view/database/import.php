<form id="pageListForm" class="layui-form">
    <table class="layui-table mt10" lay-even="" lay-skin="row">
        <thead>
            <tr>
                <th>备份名称</th>
                <th>备份卷数</th>
                <th>备份压缩</th>
                <th>备份大小</th>
                <th>备份时间</th>
                <th>操作</th>
            </tr> 
        </thead>
        <tbody>
            {volist name="data_list" id="vo"}
            <tr>
                <td>{:date('Ymd-His', $vo['time'])}</td>
                <td>{$vo['part']}</td>
                <td>{$vo['compress']}</td>
                <td>{:round($vo['size']/1024, 2)} K</td>
                <td>{:date('Y-m-d H:i:s', $vo['time'])}</td>
                <td> 
                    <div class="layui-btn-group">
                        <a data-href="{:url('import?id='.strtotime($key))}" class="layui-btn layui-btn-primary layui-btn-small j-ajax">恢复</a>
                        <a data-href="{:url('del?id='.strtotime($key))}" class="layui-btn layui-btn-primary layui-btn-small j-tr-del">删除</a>
                    </div>
                </td>
            </tr>
            {/volist}
        </tbody>
    </table>
</form>
{include file="block/layui" /}