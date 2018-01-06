<form class="layui-form layui-form-pane page-form" action="{:url()}" method="post" id="editForm">
    <div class="layui-form-item">
        <label class="layui-form-label">配置分组</label>
        <div class="layui-input-inline w300">
            <select name="group" class="field-group" type="select" lay-filter="group">
                {volist name=":config('sys.config_group')" id="v"}
                    <option value="{$key}"{if condition="$key eq input('param.group')"}selected{/if}>[{$key}]{$v}</option>
                {/volist}
            </select>
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">配置标题</label>
        <div class="layui-input-inline w300">
            <input type="text" class="layui-input field-title" name="title" lay-verify="required" autocomplete="off" placeholder="">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">配置名称</label>
        <div class="layui-input-inline w300">
            <input type="text" class="layui-input field-name" name="name" lay-verify="required" autocomplete="off" value="">
        </div>
        <div class="layui-form-mid layui-word-aux">由英文字母和下划线组成，必须以字母开头，如：web_name，调用方法：<span class="red">config('<span id="groupShow">{if condition="isset($data_info['group'])"}{$data_info['group']}{else /}{:input('param.group')}{/if}</span>.web_name')</span></div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">配置类型</label>
        <div class="layui-input-inline w300">
            <select name="type" class="field-type" type="select">
            {volist name=":form_type()" id="v"}
                <option value="{$key}">[{$key}]{$v}</option>
            {/volist}
            </select>
        </div>
        <div class="layui-form-mid layui-word-aux"></div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">配置默认值</label>
        <div class="layui-input-inline w300">
            <textarea rows="6"  class="layui-textarea field-value" name="value" lay-verify="" autocomplete="off" placeholder="配置类型为数组时请按如下格式填写：                                                     键值:键名                                                     键值:键名"></textarea>
        </div>
        <div class="layui-form-mid layui-word-aux">配置类型为数组时请按如下格式填写，其他类型随意：<br>键值:键名<br>键值:键名</div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">配置选项</label>
        <div class="layui-input-inline w300">
            <textarea rows="8" class="layui-textarea field-options jq_watermark" name="options" lay-verify="" autocomplete="off" placeholder="请严格按照以下格式设置：                                 选项值:选项名                                               选项值:选项名                                   "></textarea>
        </div>
        <div class="layui-form-mid layui-word-aux">此配置仅适用于配置类型为单选按钮、多选框、下拉框、开关。<br>参考格式如下：<br>1:北京<br>2:上海<br>3:广州<br>4:深圳
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">配置提示</label>
        <div class="layui-input-inline w300">
            <textarea  class="layui-textarea field-tips" name="tips" lay-verify="" autocomplete="off" placeholder="[选填项]"></textarea>
        </div>
        <div class="layui-form-mid layui-word-aux">关于此配置的提示信息或使用说明，支持HTML</div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">配置状态</label>
        <div class="layui-input-inline w300">
            <input type="radio" class="field-status" name="status" value="1" title="启用" checked>
            <input type="radio" class="field-status" name="status" value="0" title="禁用">
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-input-block">
            <input type="hidden" class="field-id" name="id">
            <button type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit">提交</button>
            <a href="{:url('index')}" class="layui-btn layui-btn-primary ml10"><i class="aicon ai-fanhui"></i>返回</a>
        </div>
    </div>
</form>
{include file="block/layui" /}
<script>
var formData = {:json_encode($data_info)};
layui.use(['form'], function() {
    var $ = layui.jquery, form = layui.form;
    form.on('select(group)', function(data) {
        $('#groupShow').html(data.value);
    });
});
</script>
<script src="__ADMIN_JS__/footer.js"></script>