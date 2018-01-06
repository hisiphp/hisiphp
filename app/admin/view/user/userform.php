<form class="layui-form layui-form-pane" action="{:url()}" method="post" id="editForm">
<div class="layui-tab-item layui-show layui-form-pane">
    <div class="layui-form-item">
        <label class="layui-form-label">角色分组</label>
        <div class="layui-input-inline">
            <select name="role_id" class="field-role_id" type="select">
                {$role_option}
            </select>
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">用户名</label>
        <div class="layui-input-inline">
            <input type="text" class="layui-input field-username" name="username" lay-verify="required" autocomplete="off" placeholder="请输入用户名">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">昵&nbsp;&nbsp;&nbsp;&nbsp;称</label>
        <div class="layui-input-inline">
            <input type="text" class="layui-input field-nick" name="nick" lay-verify="required" autocomplete="off" placeholder="请输入用户名">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">登陆密码</label>
        <div class="layui-input-inline">
            <input type="password" class="layui-input" name="password" lay-verify="password" autocomplete="off" placeholder="******">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">联系邮箱</label>
        <div class="layui-input-inline">
            <input type="text" class="layui-input field-email" name="email" lay-verify="" autocomplete="off" placeholder="请输入邮箱地址">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">联系手机</label>
        <div class="layui-input-inline">
            <input type="text" class="layui-input field-mobile" name="mobile" lay-verify="" autocomplete="off" placeholder="请输入手机号码">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">状&nbsp;&nbsp;&nbsp;&nbsp;态</label>
        <div class="layui-input-inline">
            <input type="radio" class="field-status" name="status" value="1" title="启用" checked>
            <input type="radio" class="field-status" name="status" value="0" title="禁用">
        </div>
    </div>
</div>
<div class="layui-tab-item layui-form">
    <div class="layui-collapse page-tips">
      <div class="layui-colla-item">
        <h2 class="layui-colla-title">温馨提示</h2>
        <div class="layui-colla-content layui-show">
            <p>
            默认使用当前用户的角色分组，您可以针对此用户单独设置角色分组以外的权限；如果您更改了角色分组未保存，则单独设置权限会无效哦！
            </p>
        </div>
      </div>
    </div>
    <div class="layui-form-item role-list-form">
    {volist name="menu_list" id="v"}
        <dl class="role-list-form-top">
            <dt><input type="checkbox" name="auth[]" lay-filter="roleAuth" value="{$v['id']}" data-parent="0" data-level="1" lay-skin="primary" title="{$v['title']}"></dt>
            <dd>
                {volist name="v['childs']" id="vv"}
                <dl>
                    <dt><input type="checkbox" name="auth[]" lay-filter="roleAuth" value="{$vv['id']}" data-pid="{$vv['pid']}" data-level="2" lay-skin="primary" title="{$vv['title']}"></dt>
                    <dd>
                        {volist name="vv['childs']" id="vvv"}
                        <dl>
                            <dt><input type="checkbox" name="auth[]" lay-filter="roleAuth" value="{$vvv['id']}" data-pid="{$vvv['pid']}" data-level="3" lay-skin="primary" title="{$vvv['title']}"></dt>
                            <dd>
                                {volist name="vvv['childs']" id="vvvv"}
                                    <input type="checkbox" name="auth[]" lay-filter="roleAuth" value="{$vvvv['id']}" data-pid="{$vvvv['pid']}" data-level="4" lay-skin="primary" title="{$vvvv['title']}">
                                {/volist}
                            </dd>
                        </dl>
                        {/volist}
                    </dd>
                </dl>
                {/volist}
            </dd>
        </dl>
    {/volist}
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
    /* 有BUG 待完善*/
    form.on('checkbox(roleAuth)', function(data) {
        var child = $(data.elem).parent('dt').siblings('dd').find('input');
        /* 自动选中父节点 */
        var check_parent = function (id) {
            var self = $('.role-list-form input[value="'+id+'"]');
            var pid = self.attr('data-pid') || '';
            self.prop('checked', true);
            if (pid == '') {
                return false;
            }
            check_parent(pid);
        };
        /* 自动选中子节点 */
        child.each(function(index, item) {
            item.checked = data.elem.checked;
        });
        check_parent($(data.elem).attr('data-pid'));
        form.render('checkbox');
    });

    /* 权限赋值 */
    if (formData) {
        for(var i in formData['auth']) {
            $('.role-list-form input[value="'+formData['auth'][i]+'"]').prop('checked', true);
        }
        form.render('checkbox');
    }
});
</script>
<script src="__ADMIN_JS__/footer.js"></script>