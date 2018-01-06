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
      <legend>关联插件排序</legend>
    </fieldset>
    <div class="layui-form-item">
        <div class="layui-input-inline w400 pl50">
            <ul class="hook-plugins-sort">
                <li><span>插件</span><span>排序</span></li>
                {volist name="hook_plugins" id="vo"}
                <li><span>{$vo['plugins']}</span><input type="text" class="layui-input j-ajax-input fl" style="width:50px;height:25px;" onkeyup="value=value.replace(/[^\d]/g,'')" value="{$vo['sort']}" data-value="{$vo['sort']}" data-href="{:url('sort?table=admin_hook_plugins&ids='.$vo['id'])}"></li>
                {/volist}
            </ul>
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