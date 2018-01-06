<div class="layui-tab-item layui-show">
    <!--
    +----------------------------------------------------------------------
    | 添加修改实例模板，可直接复制以下代码使用
    | select元素需要加type="select"
    | 所有可编辑的表单元素需要按以下格式添加class名：class="field-字段名"
    +----------------------------------------------------------------------
    -->
    <div class="layui-collapse page-tips">
      <div class="layui-colla-item">
        <h2 class="layui-colla-title">温馨提示</h2>
        <div class="layui-colla-content">
          <p>此页面为后台[添加/修改]标准模板，您可以直接复制使用修改</p>
        </div>
      </div>
    </div>
    <form class="layui-form layui-form-pane" action="{:url()}" id="editForm" method="post">
        <fieldset class="layui-elem-field layui-field-title">
          <legend>表单集合</legend>
        </fieldset>
        <div class="layui-form-item">
            <label class="layui-form-label">角色分组</label>
            <div class="layui-input-inline">
                <select name="role_id" class="field-role_id" type="select">
                    <option value="0">超级管理员</option>
                    <option value="1" selected="">普通管理员</option>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">用户名</label>
            <div class="layui-input-inline">
                <input type="text" class="layui-input field-username" name="username" lay-verify="title" autocomplete="off" placeholder="请输入用户名">
            </div>
            <div class="layui-form-mid layui-word-aux">表单操作提示</div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">会员</label>
            <div class="layui-input-inline">
                <input type="text" class="layui-input" name="member" lay-verify="" autocomplete="off" placeholder="会员选择">
            </div>
            <a href="{:url('admin/member/pop?callback=func')}" title="选择会员" class="layui-btn layui-btn-primary j-iframe-pop fl">选择会员</a>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">系统图标</label>
            <div class="layui-input-inline">
                <input type="text" class="layui-input" id="input-icon" name="icon" lay-verify="" autocomplete="off" placeholder="可自定义或使用系统图标">
            </div>
            <i class="" id="form-icon-preview"></i>
            <a href="{:url('publics/icon?input=input-icon&show=form-icon-preview')}" class="layui-btn layui-btn-primary j-iframe-pop fl" title="选择图标">选择图标</a>
        </div>
        <!--图片-->
        <div class="layui-form-item">
            <label class="layui-form-label">图片上传</label>
            <div class="layui-input-inline upload">
                <button type="button" name="upload" class="layui-btn layui-btn-primary layui-upload" lay-type="image" lay-data="{accept:'image'}">请上传图片</button>
                <input type="hidden" class="upload-input" name="image" value="">
                <img src="" style="display:none;border-radius:5px;border:1px solid #ccc" width="36" height="36">
            </div>
            <div class="layui-form-mid layui-word-aux"></div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">昵&nbsp;&nbsp;&nbsp;&nbsp;称</label>
            <div class="layui-input-inline">
                <input type="text" class="layui-input field-nick" name="nick" lay-verify="title" autocomplete="off" placeholder="请输入用户名">
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
                <input type="text" class="layui-input field-email" name="email" lay-verify="title" autocomplete="off" placeholder="请输入邮箱地址">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">联系手机</label>
            <div class="layui-input-inline">
                <input type="text" class="layui-input field-mobile" name="mobile" lay-verify="title" autocomplete="off" placeholder="请输入手机号码">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">CKEditor</label>
            <div class="layui-input-block">
                <textarea id="ckeditor" name="content">CKEditor 1</textarea>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">CKEditor</label>
            <div class="layui-input-block">
                <textarea id="ckeditor2" name="content">CKEditor 2</textarea>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">kindEditor</label>
            <div class="layui-input-block">
                <textarea id="kindeditor1" name="content1">kindEditor 1</textarea>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">kindEditor</label>
            <div class="layui-input-block">
                <textarea id="kindeditor2" name="content2">kindEditor 2</textarea>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">UEditor</label>
            <div class="layui-input-block">
                <textarea id="UEditor1" name="content3">kindEditor 2</textarea>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">UEditor</label>
            <div class="layui-input-block">
                <textarea id="UEditor2" name="content3">kindEditor 2</textarea>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">UMditor</label>
            <div class="layui-input-block">
                <textarea id="UMeditor1" name="UMditor1">UMditor 1</textarea>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">UMditor</label>
            <div class="layui-input-block">
                <textarea id="UMeditor2" name="UMditor2">UMditor 2</textarea>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">状&nbsp;&nbsp;&nbsp;&nbsp;态</label>
            <div class="layui-input-inline">
                <input type="radio" class="field-status" name="status" value="1" title="启用">
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
</div>
<div class="layui-tab-item">
    <style type="text/css">
    .site-demo-code{
    left: 0;
    top: 0;
    width: 100%;
    height: 600px;
    border: none;
    padding: 10px;
    font-size: 12px;
    background-color: #F7FBFF;
    color: #881280;
    font-family: Courier New;}
    </style>
    <textarea class="layui-border-box site-demo-text site-demo-code" spellcheck="false" readonly>
<!--
+----------------------------------------------------------------------
| 添加修改实例模板，Ctrl+A 可直接复制以下代码使用
| select元素需要加type="select"
| 所有可编辑的表单元素需要按以下格式添加class名：class="field-字段名"
+----------------------------------------------------------------------
-->
<div class="layui-collapse page-tips">
  <div class="layui-colla-item">
    <h2 class="layui-colla-title">温馨提示</h2>
    <div class="layui-colla-content">
      <p>此页面为后台[添加/修改]标准模板，您可以直接复制使用修改</p>
    </div>
  </div>
</div>
{literal}
<form class="layui-form layui-form-pane" action="{:url('')}" id="editForm" method="post">
    <fieldset class="layui-elem-field layui-field-title">
      <legend>表单集合</legend>
    </fieldset>
    <div class="layui-form-item">
        <label class="layui-form-label">角色分组</label>
        <div class="layui-input-inline">
            <select name="role_id" class="field-role_id" type="select">
                <option value="0">超级管理员</option>
                <option value="1" selected="">普通管理员</option>
            </select>
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">用户名</label>
        <div class="layui-input-inline">
            <input type="text" class="layui-input field-username" name="username" lay-verify="title" autocomplete="off" placeholder="请输入用户名">
        </div>
        <div class="layui-form-mid layui-word-aux">表单操作提示</div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">会员</label>
        <div class="layui-input-inline">
            <input type="text" class="layui-input" name="member" lay-verify="" autocomplete="off" placeholder="会员选择">
        </div>
        <a href="{:url('admin/member/pop?callback=func')}" class="layui-btn layui-btn-primary j-iframe-pop fl">选择会员</a>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">系统图标</label>
        <div class="layui-input-inline">
            <input type="text" class="layui-input" id="input-icon" name="icon" lay-verify="" autocomplete="off" placeholder="可自定义或使用系统图标">
        </div>
        <i class="" id="form-icon-preview"></i>
        <a href="{:url('admin/publics/icon?input=input-icon&show=form-icon-preview')}" class="layui-btn layui-btn-primary j-iframe-pop fl">选择图标</a>
    </div>
    <!--图片-->
    <div class="layui-form-item">
        <label class="layui-form-label">图片上传</label>
        <div class="layui-input-inline upload">
            <button type="button" name="upload" class="layui-btn layui-btn-primary layui-upload" lay-type="image" lay-data="{accept:'image'}">请上传图片</button>
            <input type="hidden" class="upload-input" name="image" value="">
            <img src="" style="display:none;border-radius:5px;border:1px solid #ccc" width="36" height="36">
        </div>
        <div class="layui-form-mid layui-word-aux"></div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">昵&nbsp;&nbsp;&nbsp;&nbsp;称</label>
        <div class="layui-input-inline">
            <input type="text" class="layui-input field-nick" name="nick" lay-verify="title" autocomplete="off" placeholder="请输入用户名">
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
            <input type="text" class="layui-input field-email" name="email" lay-verify="title" autocomplete="off" placeholder="请输入邮箱地址">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">联系手机</label>
        <div class="layui-input-inline">
            <input type="text" class="layui-input field-mobile" name="mobile" lay-verify="title" autocomplete="off" placeholder="请输入手机号码">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">CKEditor</label>
        <div class="layui-input-block">
            <[删除我]textarea id="ckeditor" name="content">CKEditor 1</[删除我]textarea>
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">CKEditor</label>
        <div class="layui-input-block">
            <[删除我]textarea id="ckeditor2" name="content">CKEditor 2</[删除我]textarea>
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">kindEditor</label>
        <div class="layui-input-block">
            <[删除我]textarea name="content1">kindEditor 1</[删除我]textarea>
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">kindEditor</label>
        <div class="layui-input-block">
            <[删除我]textarea name="content2">kindEditor 2</[删除我]textarea>
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">UEditor</label>
        <div class="layui-input-block">
            <[删除我]textarea id="UEditor1" name="content3">kindEditor 2</[删除我]textarea>
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">UEditor</label>
        <div class="layui-input-block">
            <[删除我]textarea id="UEditor2" name="content3">kindEditor 2</[删除我]textarea>
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">UMditor</label>
        <div class="layui-input-block">
            <[删除我]textarea id="UMeditor1" name="UMditor1">UMditor 1</[删除我]textarea>
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">UMditor</label>
        <div class="layui-input-block">
            <[删除我]textarea id="UMeditor2" name="UMditor2">UMditor 2</[删除我]textarea>
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">状&nbsp;&nbsp;&nbsp;&nbsp;态</label>
        <div class="layui-input-inline">
            <input type="radio" class="field-status" name="status" value="1" title="启用">
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
{include file="admin@block/layui" /}
{/literal}
<script>
/* 修改模式下需要将数据放入此变量 */
var formData = {literal}{:json_encode($data_info)};{/literal}
// 会员选择回调函数
function func(data) {
    var $ = layui.jquery;
    $('input[name="member"]').val('['+data[0]['id']+']'+data[0]['username']);
}
layui.use(['upload'], function() {
    var $ = layui.jquery, layer = layui.layer, upload = layui.upload;
    /**
     * 附件上传url参数说明
     * @param string $from 来源
     * @param string $group 附件分组,默认sys[系统]，模块格式：m_模块名，插件：p_插件名
     * @param string $water 水印，参数为空默认调用系统配置，no直接关闭水印，image 图片水印，text文字水印
     * @param string $thumb 缩略图，参数为空默认调用系统配置，no直接关闭缩略图，如需生成 500x500 的缩略图，则 500x500多个规格请用";"隔开
     * @param string $thumb_type 缩略图方式
     * @param string $input 文件表单字段名
     */
    upload.render({
        elem: '.layui-upload'
        ,url: '{literal}{:url("admin/annex/upload?water=&thumb=&from=&group=")}{/literal}'
        ,method: 'post'
        ,before: function(input) {
            layer.msg('文件上传中...', {time:3000000});
        },done: function(res, index, upload) {
            var obj = this.item;
            if (res.code == 0) {
                layer.msg(res.msg);
                return false;
            }
            layer.closeAll();
            var input = $(obj).parents('.upload').find('.upload-input');
            if ($(obj).attr('lay-type') == 'image') {
                input.siblings('img').attr('src', res.data.file).show();
            }
            input.val(res.data.file);
        }
    });
});
</script>
{literal}
<!--
/**
 * editor 富文本编辑器
 * @param array $obj 编辑器的容器ID
 * @param string $name [为了方便大家能在系统设置里面灵活选择编辑器，建议不要指定此参数]，目前支持的编辑器[ueditor,umeditor,ckeditor,kindeditor]
 * @param string $upload [选填]附件上传地址
 */
-->
{:editor(['UMeditor1', 'UMeditor2'])}
{:editor(['kindeditor1', 'kindeditor2'], 'kindeditor')}
{:editor(['UEditor1', 'UEditor2'], 'ueditor')}
{:editor(['ckeditor', 'ckeditor2'], 'ckeditor')}
<script src="__ADMIN_JS__/footer.js"></script>
{/literal}
    </textarea>
</div>

{include file="block/layui" /}
<script>
/* 修改模式下需要将数据放入此变量 */
var formData = {"id":1,"role_id":1,"nick":"\u8d85\u7ea7\u7ba1\u7406\u5458","email":"chenf4hua12@qq.com","mobile":13888888888,"status":0};
// 会员选择回调函数
function func(data) {
    var $ = layui.jquery;
    $('input[name="member"]').val('['+data[0]['id']+']'+data[0]['username']);
}
layui.use(['upload'], function() {
    var $ = layui.jquery, layer = layui.layer, upload = layui.upload;
    /**
     * 附件上传url参数说明
     * @param string $from 来源
     * @param string $group 附件分组,默认sys[系统]，模块格式：m_模块名，插件：p_插件名
     * @param string $water 水印，参数为空默认调用系统配置，no直接关闭水印，image 图片水印，text文字水印
     * @param string $thumb 缩略图，参数为空默认调用系统配置，no直接关闭缩略图，如需生成 500x500 的缩略图，则 500x500多个规格请用";"隔开
     * @param string $thumb_type 缩略图方式
     * @param string $input 文件表单字段名
     */
    upload.render({
        elem: '.layui-upload'
        ,url: '{:url("admin/annex/upload?water=&thumb=&from=&group=")}'
        ,method: 'post'
        ,before: function(input) {
            layer.msg('文件上传中...', {time:3000000});
        },done: function(res, index, upload) {
            var obj = this.item;
            if (res.code == 0) {
                layer.msg(res.msg);
                return false;
            }
            layer.closeAll();
            var input = $(obj).parents('.upload').find('.upload-input');
            if ($(obj).attr('lay-type') == 'image') {
                input.siblings('img').attr('src', res.data.file).show();
            }
            input.val(res.data.file);
        }
    });
});
</script>
<!--
/**
 * editor 富文本编辑器
 * @param array $obj 编辑器的容器ID
 * @param string $name [为了方便大家能在系统设置里面灵活选择编辑器，建议不要指定此参数]，目前支持的编辑器[ueditor,umeditor,ckeditor,kindeditor]
 * @param string $upload [选填]附件上传地址
 */
-->
{:editor(['UMeditor1', 'UMeditor2'])}
{:editor(['kindeditor1', 'kindeditor2'], 'kindeditor')}
{:editor(['ckeditor', 'ckeditor2'], 'ckeditor')}
{:editor(['UEditor1', 'UEditor2'], 'ueditor')}
<script src="__ADMIN_JS__/footer.js"></script>