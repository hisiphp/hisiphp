<style type="text/css">
.layui-form-item .layui-form-label{width:150px;}
.layui-form-item .layui-input-inline{max-width:80%;width:auto;min-width:260px;}
.layui-form-mid{padding:0!important;}
.layui-form-mid code{color:#5FB878;}
</style>
<form action="{:url('?group='.input('param.group', 'base'))}" class="page-list-form layui-form layui-form-pane" method="post">
    {volist name="data_list" id="v"}
    {switch name="v['type']"}
        {case value="textarea"}
            <!--多行文本-->
            <div class="layui-form-item">
                <label class="layui-form-label">{$v['title']}</label>
                <div class="layui-input-inline">
                    <textarea rows="6"  class="layui-textarea" name="id[{$v['id']}]" autocomplete="off" placeholder="请填写{$v['title']}">{:htmlspecialchars_decode($v['value'])}</textarea>
                </div>
                <div class="layui-form-mid layui-word-aux">{:htmlspecialchars_decode($v['tips'])}<br>调用方式：<code>config('{if condition="isset($v['module'])"}module_{/if}{:input('param.group', 'base')}.{$v['name']}')</code></div>
            </div>
        {/case}
        {case value="array"}
            <!--文本域-->
            <div class="layui-form-item">
                <label class="layui-form-label">{$v['title']}</label>
                <div class="layui-input-inline">
                    <textarea rows="6" class="layui-textarea" name="id[{$v['id']}]" autocomplete="off" placeholder="请填写{$v['title']}">{$v['value']}</textarea>
                </div>
                <div class="layui-form-mid layui-word-aux">{:htmlspecialchars_decode($v['tips'])}<br>调用方式：<code>config('{if condition="isset($v['module'])"}module_{/if}{:input('param.group', 'base')}.{$v['name']}')</code></div>
            </div>
        {/case}
        {case value="switch"}
            <!--开关-->
            <div class="layui-form-item">
                <label class="layui-form-label">{$v['title']}</label>
                <div class="layui-input-inline">
                    <input type="checkbox" name="id[{$v['id']}]" value="1" lay-skin="switch" lay-text="{$v['options'][1]}|{$v['options'][0]}" {if condition="$v['value'] eq 1"}checked=""{/if}>
                </div>
                <div class="layui-form-mid layui-word-aux">{:htmlspecialchars_decode($v['tips'])}<br>调用方式：<code>config('{if condition="isset($v['module'])"}module_{/if}{:input('param.group', 'base')}.{$v['name']}')</code></div>
            </div>
        {/case}
        {case value="select"}
            <!--下拉框-->
            <div class="layui-form-item">
                <label class="layui-form-label">{$v['title']}</label>
                <div class="layui-input-inline">
                    <select name="id[{$v['id']}]">
                        {volist name="v['options']" id="vv"}
                            <option value="{$key}" {if condition="$key eq $v['value']"}selected{/if}>{$vv}</option>
                        {/volist}
                    </select>
                </div>
                <div class="layui-form-mid layui-word-aux">{:htmlspecialchars_decode($v['tips'])}<br>调用方式：<code>config('{if condition="isset($v['module'])"}module_{/if}{:input('param.group', 'base')}.{$v['name']}')</code></div>
            </div>
        {/case}
        {case value="radio"}
            <!--单选-->
            <div class="layui-form-item">
                <label class="layui-form-label">{$v['title']}</label>
                <div class="layui-input-inline">
                    {volist name="v['options']" id="vv"}<input type="radio" name="id[{$v['id']}]" value="{$key}" title="{$vv}" {if condition="$key eq $v['value']"}checked{/if}>
                    {/volist}
                </div>
                <div class="layui-form-mid layui-word-aux">{:htmlspecialchars_decode($v['tips'])}<br>调用方式：<code>config('{if condition="isset($v['module'])"}module_{/if}{:input('param.group', 'base')}.{$v['name']}')</code></div>
            </div>
        {/case}
        {case value="checkbox"}
            <!--多选-->
            <div class="layui-form-item">
                <label class="layui-form-label">{$v['title']}</label>
                <div class="layui-input-inline">
                    {volist name="v['options']" id="vv"}<input type="checkbox" name="id[{$v['id']}][]" value="{$key}" title="{$vv}" lay-skin="primary" {if condition="in_array($key, $v['value'])"}checked{/if}>
                    {/volist}
                </div>
                <div class="layui-form-mid layui-word-aux">{:htmlspecialchars_decode($v['tips'])}<br>调用方式：<code>config('{if condition="isset($v['module'])"}module_{/if}{:input('param.group', 'base')}.{$v['name']}')</code></div>
            </div>
        {/case}
        {case value="date"}
            <!--日期-->
            <div class="layui-form-item">
                <label class="layui-form-label">{$v['title']}</label>
                <div class="layui-input-inline">
                    <input type="text" class="layui-input layui-date" name="id[{$v['id']}]" value="{$v['value']}" autocomplete="off" placeholder="请填写{$v['title']}" onclick="layui.laydate({elem: this,format:'YYYY-MM-DD'})">
                </div>
                <div class="layui-form-mid layui-word-aux">{:htmlspecialchars_decode($v['tips'])}<br>调用方式：<code>config('{if condition="isset($v['module'])"}module_{/if}{:input('param.group', 'base')}.{$v['name']}')</code></div>
            </div>
        {/case}
        {case value="datetime"}
            <!--日期+时间-->
            <div class="layui-form-item">
                <label class="layui-form-label">{$v['title']}</label>
                <div class="layui-input-inline">
                    <input type="text" class="layui-input layui-date" name="id[{$v['id']}]" value="{$v['value']}" autocomplete="off" placeholder="请填写{$v['title']}" onclick="layui.laydate({elem: this,format:'YYYY-MM-DD hh:mm:ss'})">
                </div>
                <div class="layui-form-mid layui-word-aux">{:htmlspecialchars_decode($v['tips'])}<br>调用方式：<code>config('{if condition="isset($v['module'])"}module_{/if}{:input('param.group', 'base')}.{$v['name']}')</code></div>
            </div>
        {/case}
        {case value="image"}
            <!--图片-->
            <div class="layui-form-item">
                <label class="layui-form-label">{$v['title']}</label>
                <div class="layui-input-inline upload">
                    <button type="button" name="upload" class="layui-btn layui-btn-primary layui-upload" lay-type="image" lay-data="{ {if condition="!empty($v['url'])"}url: '{:url($v['url'])}', {/if}exts:'{:str_replace(',', '|', config('upload.upload_image_ext'))}', accept:'image'}">请上传{$v['title']}</button>
                    <input type="hidden" class="upload-input" name="id[{$v['id']}]" value="{$v['value']}">
                    {if condition="$v['value']"}
                        <img src="{$v['value']}" style="display:inline-block;border-radius:5px;border:1px solid #ccc" width="36" height="36">
                    {else /}
                        <img src="" style="display:none;border-radius:5px;border:1px solid #ccc" width="36" height="36">
                    {/if}
                </div>
                <div class="layui-form-mid layui-word-aux">{:htmlspecialchars_decode($v['tips'])}<br>调用方式：<code>config('{if condition="isset($v['module'])"}module_{/if}{:input('param.group', 'base')}.{$v['name']}')</code></div>
            </div>
        {/case}
        {case value="file"}
            <!--文件-->
            <div class="layui-form-item">
                <label class="layui-form-label">{$v['title']}</label>
                <div class="layui-input-inline upload">
                    <button type="button" name="upload" class="layui-btn layui-btn-primary layui-upload" lay-data="{ {if condition="!empty($v['url'])"}url: '{:url($v['url'])}', {/if}exts:'{:str_replace(',', '|', config('upload.upload_file_ext'))}', accept:'file'}">请上传{$v['title']}</button>
                    <input type="hidden" class="upload-input" name="id[{$v['id']}]" value="{$v['value']}">
                </div>
                <div class="layui-form-mid layui-word-aux">{:htmlspecialchars_decode($v['tips'])}<br>调用方式：<code>config('{if condition="isset($v['module'])"}module_{/if}{:input('param.group', 'base')}.{$v['name']}')</code></div>
            </div>
        {/case}
        {case value="hidden"}
            <input type="hidden" name="id[{$v['id']}]" value="{$v['value']}">
        {/case}
        {default /}
            <!--单行文本-->
            <div class="layui-form-item">
                <label class="layui-form-label">{$v['title']}</label>
                <div class="layui-input-inline">
                    <input type="text" class="layui-input" name="id[{$v['id']}]" value="{$v['value']}" autocomplete="off" placeholder="请填写{$v['title']}">
                </div>
                <div class="layui-form-mid layui-word-aux">{:htmlspecialchars_decode($v['tips'])}<br>调用方式：<code>config('{if condition="isset($v['module'])"}module_{/if}{:input('param.group', 'base')}.{$v['name']}')</code></div>
            </div>
    {/switch}
    <input type="hidden" name="type[{$v['id']}]" value="{$v['type']}">
    {if condition="isset($v['module'])"}
        <input type="hidden" name="module" value="{$v['module']}">
    {/if}
    {/volist}
    <div class="layui-form-item">
        <div class="layui-input-block">
            <button type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit">提交</button>
        </div>
    </div>
</form>
{include file="block/layui" /}
<script>
layui.use(['jquery', 'laydate', 'upload'], function() {
    var $ = layui.jquery, laydate = layui.laydate, layer = layui.layer, upload = layui.upload;
    upload.render({
        elem: '.layui-upload',
        url: '{:url("admin/annex/upload?thumb=no&water=no")}'
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
    // 日期渲染
    laydate.render({elem: '.layui-date'});
});
</script>