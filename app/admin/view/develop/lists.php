<div class="layui-tab-item layui-show">
    <!--
    +----------------------------------------------------------------------
    | 列表页实例模板，可直接复制以下代码使用
    +----------------------------------------------------------------------
    -->
    <form class="page-list-form">
    <div class="layui-collapse page-tips">
      <div class="layui-colla-item">
        <h2 class="layui-colla-title">温馨提示</h2>
        <div class="layui-colla-content">
          <p>此页面为后台数据管理标准模板，您可以直接复制使用修改</p>
        </div>
      </div>
    </div>
    <div class="page-toolbar">
        <div class="layui-btn-group fl">
            <a href="{:url('add')}" class="layui-btn layui-btn-primary layui-icon layui-icon-add-circle-fine">&nbsp;添加</a>
            <a data-href="{:url('status?table=不含前缀的表名&val=1')}" class="layui-btn layui-btn-primary j-page-btns layui-icon layui-icon-play" data-table="dataTable">&nbsp;启用</a>
            <a data-href="{:url('status?table=不含前缀的表名&val=0')}" class="layui-btn layui-btn-primary j-page-btns layui-icon layui-icon-pause" data-table="dataTable">&nbsp;禁用</a>
            <a data-href="{:url('del?table=不含前缀的表名')}" class="layui-btn layui-btn-primary j-page-btns confirm layui-icon layui-icon-close red">&nbsp;删除</a>
        </div>
        <div class="page-filter fr">
            <form class="layui-form layui-form-pane" action="{:url()}" method="get">
            <div class="layui-form-item">
                <label class="layui-form-label">搜索</label>
                <div class="layui-input-inline">
                    <input type="text" name="q" lay-verify="required" placeholder="请输入关键词搜索" autocomplete="off" class="layui-input">
                </div>
            </div>
            </form>
        </div>
    </div>
    <div class="layui-form">
        <table class="layui-table mt10" lay-even="" lay-skin="row">
            <colgroup>
                <col width="50">
                <col width="150">
                <col width="200">
                <col width="300">
                <col width="100">
                <col width="80">
                <col>
            </colgroup>
            <thead>
                <tr>
                    <th><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                    <th>民族</th>
                    <th>出场时间</th>
                    <th>格言</th>
                    <th>排序</th>
                    <th>状态</th>
                    <th>操作</th>
                </tr> 
            </thead>
            <tbody>
                <tr>
                    <td><input type="checkbox" class="layui-checkbox checkbox-ids" name="ids[]" lay-skin="primary"></td>
                    <td>汉族</td>
                    <td>1989-10-14</td>
                    <td>人生似修行</td>
                    <td>
                        <input type="text" class="layui-input j-ajax-input input-sort" onkeyup="value=value.replace(/[^\d]/g,'')" 
value="100" data-value="" data-href="">
                    </td>
                    <td>
                        <input type="checkbox" name="status" checked="" value="0" lay-skin="switch" lay-filter="switchStatus" lay-text="正常|关闭" data-href="">
                    </td>
                    <td>
                        <div class="layui-btn-group">
                            <div class="layui-btn-group">
                            <a data-href="" class="layui-btn layui-btn-primary layui-btn-sm">文字</a>
                            <a data-href="" class="layui-btn layui-btn-primary layui-btn-sm"><i class="layui-icon">&#xe654;</i></a>
                            <a data-href="" class="layui-btn layui-btn-primary layui-btn-sm"><i class="layui-icon">&#xe642;</i></a>
                            <a data-href="" class="layui-btn layui-btn-primary layui-btn-sm j-tr-del"><i class="layui-icon">&#xe640;</i></a>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    </form>
</div>
{include file="block/layui" /}