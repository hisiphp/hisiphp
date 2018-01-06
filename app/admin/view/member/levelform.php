<form class="layui-form layui-form-pane" action="{:url()}" method="post" id="editForm">
    <div class="layui-form-item">
        <label class="layui-form-label">等级名称</label>
        <div class="layui-input-inline">
            <input type="text" class="layui-input field-name" name="name" lay-verify="title" autocomplete="off" placeholder="请输入等级名称">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">等级折扣</label>
        <div class="layui-input-inline">
            <input type="text" class="layui-input field-discount" name="discount" value="100" lay-verify="number" autocomplete="off" placeholder="请输入等级折扣">
        </div>
        <div class="layui-form-mid layui-word-aux">折扣率单位为%，如输入90，表示该会员等级的用户可以以产品销售价的90%购买</div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">最小经验值</label>
        <div class="layui-input-inline">
            <input type="text" class="layui-input field-min_exper" name="min_exper" value="0" lay-verify="number" autocomplete="off" placeholder="请输入最小经验值">
        </div>
        <div class="layui-form-mid layui-word-aux">设置会员等级所需要的最低经验值下限</div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">最大经验值</label>
        <div class="layui-input-inline">
            <input type="text" class="layui-input field-max_exper" name="max_exper" value="0" lay-verify="number" autocomplete="off" placeholder="请输入最大经验值">
        </div>
        <div class="layui-form-mid layui-word-aux">设置会员等级所需要的最大经验值上限</div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">会员有效期</label>
        <div class="layui-input-inline">
            <input type="text" class="layui-input field-expire w100" name="expire" value="0" lay-verify="number" autocomplete="off" placeholder="请输入会员有效期" style="display:inline-block;"><label>&nbsp;天</label>
        </div>
        <div class="layui-form-mid layui-word-aux">设置会员注册后多少天到期，设置为0表示永久</div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">等级状态</label>
        <div class="layui-input-inline">
            <input type="radio" class="field-status" name="status" value="1" title="启用">
            <input type="radio" class="field-status" name="status" value="0" title="禁用">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">默认等级</label>
        <div class="layui-input-inline">
            <input type="radio" class="field-default" name="default" value="1" title="是">
            <input type="radio" class="field-default" name="default" value="0" title="否">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">等级简介</label>
        <div class="layui-input-inline">
            <textarea  class="layui-textarea field-intro" name="intro" lay-verify="" autocomplete="off" placeholder="[选填]角色简介"></textarea>
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-input-block">
            <input type="hidden" class="field-id" name="id">
            <button type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit">提交</button>
            <a href="{:url('level')}" class="layui-btn layui-btn-primary ml10"><i class="aicon ai-fanhui"></i>返回</a>
        </div>
    </div>
</form>
{include file="block/layui" /}
<script>
var formData = {:json_encode($data_info)};
</script>
<script src="__ADMIN_JS__/footer.js"></script>