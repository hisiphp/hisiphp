<form class="layui-form layui-form-pane" action="{:url()}" method="post" id="editForm">
<div class="page-form">
    <div class="layui-form-item">
        <label class="layui-form-label">钩子名称</label>
        <div class="layui-input-inline w200">
            <input type="text" class="layui-input field-name" name="name" lay-verify="required" autocomplete="off" placeholder="请填写钩子名称">
        </div>
        <div class="layui-form-mid layui-word-aux">由字母和下划线组成，如：system_hook</div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">钩子描述</label>
        <div class="layui-input-inline w300">
            <textarea  class="layui-textarea field-intro" name="intro" lay-verify="required" autocomplete="off" placeholder="请填写钩子描述"></textarea>
        </div>
    </div>
    {if condition="$hook_plugins"}
    <fieldset class="layui-elem-field layui-field-title">
      <legend>关联插件设置</legend>
    </fieldset>
    <div class="layui-form-item">
        <div class="layui-input-inline pl50" style="width:500px;">
            <table class="layui-table">
                <thead>
                    <tr>
                        <th>插件名</th>
                        <th>排序</th>
                        <th>状态</th>
                    </tr>
                </thead>
                <tbody>
                    {volist name="hook_plugins" id="vo"}
                    <tr>
                        <td>{$vo['plugins']}</td>
                        <td>
                            <input type="text" class="layui-input j-ajax-input fl" style="width:50px;height:25px;" onkeyup="value=value.replace(/[^\d]/g,'')" value="{$vo['sort']}" data-value="{$vo['sort']}" data-href="{:url('sort?table=admin_hook_plugins&ids='.$vo['id'])}">
                        </td>
                        <td>
                            <input type="checkbox" name="status" {if condition="$vo['status'] eq 1"}checked=""{/if} value="{$vo['status']}" lay-skin="switch" lay-filter="switchStatus" lay-text="启用|停用" data-href="{:url('hookPluginsStatus?ids='.$vo['id'])}">
                        </td>
                    </tr>
                    {/volist}
                </tbody>
            </table>
        </div>
    </div>
    {/if}
    <div class="layui-form-item">
        <div class="layui-input-block">
            <input type="hidden" class="field-id" name="id">
            <button type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit">提交</button>
            <a href="{:url('index')}" class="layui-btn layui-btn-primary ml10"><i class="aicon ai-fanhui"></i>返回</a>
        </div>
    </div>
</div>
</form>
{include file="block/layui" /}
<script>
var formData = {:json_encode($data_info)};
</script>
<script src="__ADMIN_JS__/footer.js"></script>