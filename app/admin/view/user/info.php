<form class="layui-form" action="{:url()}" method="post" id="editForm">
<div class="page-form">
    <div class="layui-form-item">
        <label class="layui-form-label">用户名</label>
        <div class="layui-input-inline">
            <input type="text" class="layui-input field-username" name="username" lay-verify="required" autocomplete="off" placeholder="请输入用户名" readonly="">
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
        <label class="layui-form-label">确认密码</label>
        <div class="layui-input-inline">
            <input type="password" class="layui-input" name="password_confirm" lay-verify="password" autocomplete="off" placeholder="******">
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
        <label class="layui-form-label"> </label>
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