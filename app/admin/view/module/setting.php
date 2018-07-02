<style type="text/css">
.layui-form-item .layui-form-label{max-width:150px;min-width:120px;}
.layui-form-item .layui-input-inline{max-width:80%;width:auto;min-width:260px;}
</style>
<form class="layui-form" action="{:url()}" method="post">
    <div class="page-form">
        <fieldset class="layui-elem-field layui-field-title">
          <legend>【{$data_info['title']}】 模块配置</legend>
        </fieldset>
        {volist name="data_info['config']" id="v"}
        {switch name="v['type']"}
            {case value="textarea"}
                <!--多行文本-->
                <div class="layui-form-item">
                    <label class="layui-form-label">{$v['title']}</label>
                    <div class="layui-input-inline">
                        <textarea rows="6"  class="layui-textarea" name="{$v['name']}" autocomplete="off" placeholder="请填写{$v['title']}">{:htmlspecialchars_decode($v['value'])}</textarea>
                    </div>
                    <div class="layui-form-mid layui-word-aux">{:htmlspecialchars_decode($v['tips'])}</div>
                </div>
            {/case}
            {case value="array"}
                <!--文本域-->
                <div class="layui-form-item">
                    <label class="layui-form-label">{$v['title']}</label>
                    <div class="layui-input-inline">
                        <textarea rows="6" class="layui-textarea" name="{$v['name']}" autocomplete="off" placeholder="请填写{$v['title']}">{$v['value']}</textarea>
                    </div>
                    <div class="layui-form-mid layui-word-aux">{:htmlspecialchars_decode($v['tips'])}</div>
                </div>
            {/case}
            {case value="switch"}
                <!--开关-->
                <div class="layui-form-item">
                    <label class="layui-form-label">{$v['title']}</label>
                    <div class="layui-input-inline">
                        <input type="checkbox" name="{$v['name']}" value="1" lay-skin="switch" lay-text="{$v['options'][1]}|{$v['options'][0]}" {if condition="$v['value'] eq 1"}checked=""{/if}>
                    </div>
                    <div class="layui-form-mid layui-word-aux">{:htmlspecialchars_decode($v['tips'])}</div>
                </div>
            {/case}
            {case value="select"}
                <!--下拉框-->
                <div class="layui-form-item">
                    <label class="layui-form-label">{$v['title']}</label>
                    <div class="layui-input-inline">
                        <select name="{$v['name']}">
                            {volist name="v['options']" id="vv"}
                                <option value="{$key}" {if condition="$key eq $v['value']"}selected{/if}>{$vv}</option>
                            {/volist}
                        </select>
                    </div>
                    <div class="layui-form-mid layui-word-aux">{:htmlspecialchars_decode($v['tips'])}</div>
                </div>
            {/case}
            {case value="radio"}
                <!--单选-->
                <div class="layui-form-item">
                    <label class="layui-form-label">{$v['title']}</label>
                    <div class="layui-input-inline">
                        {volist name="v['options']" id="vv"}
                            <input type="radio" name="{$v['name']}" value="{$key}" title="{$vv}" {if condition="$key eq $v['value']"}checked{/if}>
                        {/volist}
                    </div>
                    <div class="layui-form-mid layui-word-aux">{:htmlspecialchars_decode($v['tips'])}</div>
                </div>
            {/case}
            {case value="checkbox"}
                <!--多选-->
                <div class="layui-form-item">
                    <label class="layui-form-label">{$v['title']}</label>
                    <div class="layui-input-inline">
                        {volist name="v['options']" id="vv"}
                            <input type="checkbox" name="{$v['name']}[]" value="{$key}" title="{$vv}" lay-skin="primary" {if condition="in_array($key, $v['value'])"}checked{/if}>
                        {/volist}
                    </div>
                    <div class="layui-form-mid layui-word-aux">{:htmlspecialchars_decode($v['tips'])}</div>
                </div>
            {/case}
            {case value="date"}
                <!--日期-->
                <div class="layui-form-item">
                    <label class="layui-form-label">{$v['title']}</label>
                    <div class="layui-input-inline">
                        <input type="text" class="layui-input layui-date" name="{$v['name']}" value="{$v['value']}" autocomplete="off" placeholder="请填写{$v['title']}" onclick="layui.laydate({elem: this,format:'YYYY-MM-DD'})">
                    </div>
                    <div class="layui-form-mid layui-word-aux">{:htmlspecialchars_decode($v['tips'])}</div>
                </div>
            {/case}
            {case value="datetime"}
                <!--日期+时间-->
                <div class="layui-form-item">
                    <label class="layui-form-label">{$v['title']}</label>
                    <div class="layui-input-inline">
                        <input type="text" class="layui-input layui-date" name="{$v['name']}" value="{$v['value']}" autocomplete="off" placeholder="请填写{$v['title']}" onclick="layui.laydate({elem: this,format:'YYYY-MM-DD hh:mm:ss'})">
                    </div>
                    <div class="layui-form-mid layui-word-aux">{:htmlspecialchars_decode($v['tips'])}</div>
                </div>
            {/case}
            {case value="image"}
                <!--图片-->
                <div class="layui-form-item">
                    <label class="layui-form-label">{$v['title']}</label>
                    <div class="layui-input-inline upload">
                        <button type="button" name="upload" class="layui-btn layui-btn-primary layui-upload" lay-data="{accept:'image'}" lay-type="image">请上传{$v['title']}</button>
                        <input type="hidden" class="upload-input" name="{$v['name']}" value="{$v['value']}">
                        {if condition="$v['value']"}
                            <img src="{$v['value']}" style="display:inline-block;border-radius:5px;border:1px solid #ccc" width="36" height="36">
                        {else /}
                            <img src="" style="display:none;border-radius:5px;border:1px solid #ccc" width="36" height="36">
                        {/if}
                    </div>
                    <div class="layui-form-mid layui-word-aux">{:htmlspecialchars_decode($v['tips'])}</div>
                </div>
            {/case}
            {case value="file"}
                <!--文件-->
                <div class="layui-form-item">
                    <label class="layui-form-label">{$v['title']}</label>
                    <div class="layui-input-inline upload">
                        <button type="button" name="upload" class="layui-btn layui-btn-primary layui-upload" lay-data="{accept:'file'}">请上传{$v['title']}</button>
                        <input type="hidden" class="upload-input" name="{$v['name']}" value="{$v['value']}">
                    </div>
                    <div class="layui-form-mid layui-word-aux">{:htmlspecialchars_decode($v['tips'])}</div>
                </div>
            {/case}
            {case value="hidden"}
                <input type="hidden" name="{$v['name']}" value="{$v['value']}">
            {/case}
            {default /}
                <!--单行文本-->
                <div class="layui-form-item">
                    <label class="layui-form-label">{$v['title']}</label>
                    <div class="layui-input-inline">
                        <input type="text" class="layui-input" name="{$v['name']}" value="{$v['value']}" autocomplete="off" placeholder="请填写{$v['title']}">
                    </div>
                    <div class="layui-form-mid layui-word-aux">{:htmlspecialchars_decode($v['tips'])}</div>
                </div>
        {/switch}
        {/volist}
        <div class="layui-form-item">
            <label class="layui-form-label"> </label>
            <div class="layui-input-block">
                <input type="hidden" class="field-id" name="id" value="{$data_info['id']}">
                <button type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit">提交</button>
                <a href="{:url('index')}" class="layui-btn layui-btn-primary ml10"><i class="aicon ai-fanhui"></i>返回</a>
            </div>
        </div>
    </div>
</form>
{include file="block/layui" /}
<script>
var formData = {:json_encode($data_info['config'])};
layui.use(['jquery', 'laydate', 'upload'], function() {
    var $ = layui.jquery, laydate = layui.laydate, layer = layui.layer, upload = layui.upload;
    upload.render({
        elem: '.layui-upload'
        url: '{:url("admin/annex/upload?group=m_".$data_info["name"])}'
        ,method: 'post'
        ,before: function(input) {
            layer.msg('文件上传中...', {time:3000000});
        },done: function(res, obj) {
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
    // 日期渲染
    laydate.render({elem: '.layui-date'});
});
</script>
<script src="__ADMIN_JS__/footer.js"></script>