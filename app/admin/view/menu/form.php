<form class="layui-form layui-form-pane" action="{:url()}" method="post" id="editForm">
    <div class="layui-collapse page-tips">
      <div class="layui-colla-item">
        <h2 class="layui-colla-title">温馨提示</h2>
        <div class="layui-colla-content">
          <p>后台权限验证采用白名单方式，而白名单数据均来源于系统菜单。请严格按照系统要求填写菜单链接和扩展参数。</p>
        </div>
      </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">所属菜单</label>
        <div class="layui-input-inline">
            <select name="pid" class="field-pid" type="select" lay-filter="pid">
                <option value="0" level="0">顶级菜单</option>
                {$menu_option}
            </select>
        </div>
        <div class="layui-form-mid layui-word-aux">
            尽量选择与所属模块一致的菜单，根据 “[ ]” 里面的内容判断
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">菜单名称</label>
        <div class="layui-input-inline">
            <input type="text" class="layui-input field-title" name="title" lay-verify="required" autocomplete="off" placeholder="请输入菜单名称">
        </div>
        <div class="layui-form-mid layui-word-aux">
            必填，长度限制3-24个字节(1个汉字等于3个字节)
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">图标设置</label>
        <div class="layui-input-inline">
            <input type="text" class="layui-input field-icon" id="input-icon" name="icon" lay-verify="" autocomplete="off" placeholder="可自定义或使用系统图标">
        </div>
        <i class="{if condition="isset($data_info['icon'])"}{$data_info['icon']}{/if}" id="form-icon-preview"></i>
        <a href="{:url('publics/icon?input=input-icon&show=form-icon-preview')}" title="选择图标" class="layui-btn layui-btn-primary j-iframe-pop fl">选择图标</a>
        <div class="layui-form-mid layui-word-aux">
            可直接填写图标URL
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">菜单链接</label>
        <div class="layui-input-inline">
            <input type="text" class="layui-input field-url" name="url" lay-verify="required" autocomplete="off" placeholder="请严格按照参考格式填写">
        </div>
        <div class="layui-form-mid layui-word-aux">
            必填，模块格式：模块名/控制器名/方法名，插件直接设置：admin/plugins/run，<span class="red">请留意大小写</span>
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">扩展参数</label>
        <div class="layui-input-inline">
            <input type="text" class="layui-input field-param" name="param" lay-verify="" autocomplete="off" placeholder="请严格按照参考格式填写">
        </div>
        <div class="layui-form-mid layui-word-aux">
            选填，参考格式：a=123&b=345，插件格式：_p=插件名称&_c=插件控制器&_a=插件方法
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">状态设置</label>
        <div class="layui-input-inline">
            <input type="radio" class="field-status" name="status" value="1" title="启用" checked>
            <input type="radio" class="field-status" name="status" value="0" title="禁用">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">系统菜单</label>
        <div class="layui-input-inline">
            <input type="radio" class="field-system" name="system" value="1" title="是">
            <input type="radio" class="field-system" name="system" value="0" title="否" checked>
        </div>
        <div class="layui-form-mid layui-word-aux">
            设置为系统菜单后，无法删除
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">后台导航</label>
        <div class="layui-input-inline">
            <input type="radio" class="field-nav" name="nav" value="1" title="是" checked>
            <input type="radio" class="field-nav" name="nav" value="0" title="否">
        </div>
        <div class="layui-form-mid layui-word-aux">此设置只对前一二三级菜单有效</div>
    </div>
    <div class="layui-form-item">
        <div class="layui-input-block">
            <input type="hidden" class="field-id" name="id">
            <input type="hidden" name="module" value="{:input('param.mod', 'admin')}">
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
    if (formData) {
        $('.ass-level').val(parseInt($('.field-pid option:selected').attr('level'))+1);
    }
});
</script>
<script src="__ADMIN_JS__/footer.js"></script>