<form class="layui-form layui-form-pane" action="{:url()}" method="post" id="editForm">
    <div class="layui-tab-item layui-show" title="模块基本信息">
        <fieldset class="layui-elem-field layui-field-title">
          <legend>模块基本信息</legend>
        </fieldset>
        <div class="layui-form-item">
            <label class="layui-form-label">模块名</label>
            <div class="layui-input-inline w300">
                <input type="text" class="layui-input field-name" name="name" lay-verify="required" readonly>
            </div>
            <div class="layui-form-mid layui-word-aux">禁止修改</div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">模块标题</label>
            <div class="layui-input-inline w300">
                <input type="text" class="layui-input field-title" name="title" lay-verify="required" placeholder="请输入模块标题">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">模块标识</label>
            <div class="layui-input-inline w300">
                <input type="text" class="layui-input field-identifier" name="identifier" lay-verify="required" placeholder="请输入模块标识">
            </div>
            <div class="layui-form-mid layui-word-aux">格式：模块名(只能为字母).开发者标识(只能为字母、数字、下划线).module</div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">模块图标</label>
                <div class="layui-input-inline upload">
                    <button type="button" class="layui-btn layui-btn-primary layui-upload" lay-type="image" lay-data="{url: '{:url('icon?id='.$data_info['id'])}', exts:'png', accept:'image'}">上传模块图标</button>
                    <input type="hidden" class="upload-input field-icon" name="icon">
                    {if condition="!empty($data_info['icon'])"}
                    <img src="{$data_info['icon']}?v={:time()}" style="border-radius:5px;border:1px solid #ccc" width="36" height="36">
                    {else /}
                    <img src="" style="display:none;border-radius:5px;border:1px solid #ccc" width="36" height="36">
                    {/if}
                </div>
                <div class="layui-form-mid layui-word-aux"></div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">模块描述</label>
            <div class="layui-input-inline w300">
                <textarea  class="layui-textarea field-intro" name="intro" placeholder="请填写模块描述"></textarea>
            </div>
            <div class="layui-form-mid layui-word-aux"></div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">开发者</label>
            <div class="layui-input-inline w300">
                <input type="text" class="layui-input field-author" name="author" placeholder="请输入开发者">
            </div>
            <div class="layui-form-mid layui-word-aux">建议填写</div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">开发者网址</label>
            <div class="layui-input-inline w300">
                <input type="text" class="layui-input field-url" name="url" placeholder="请输入开发者网址">
            </div>
            <div class="layui-form-mid layui-word-aux">建议填写</div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">版本号</label>
            <div class="layui-input-inline w300">
                <input type="text" class="layui-input field-version" name="version" lay-verify="required" placeholder="请输入版本号">
            </div>
            <div class="layui-form-mid layui-word-aux">版本号格式采用三段式：主版本号.次版本号.修订版本号</div>
        </div>
    </div>
    <div class="layui-tab-item" title="模块配置">
        <table class="layui-table" lay-even="" lay-skin="row">
            <thead>
                <tr>
                    <th>排序[必填]</th>
                    <th>配置名称[必填]</th>
                    <th>配置变量名[必填]</th>
                    <th>配置类型[必填]</th>
                    <th>配置选项[选填]</th>
                    <th>默认值[选填]</th>
                    <th>配置提示[选填]</th>
                    <th width="50">操作</th>
                </tr> 
            </thead>
            <tbody>
                {volist name="module_info['config']" id="vo"}
                <tr class="config-tr">
                    <td><input type="text" name="config[sort][]" class="layui-input" lay-verify="required" value="{$vo['sort']}"></td>
                    <td><input type="text" name="config[title][]" class="layui-input" lay-verify="required" value="{$vo['title']}"></td>
                    <td><input type="text" name="config[name][]" class="layui-input" lay-verify="required" value="{$vo['name']}"></td>
                    <td>
                        <select name="config[type][]" type="select">
                        {volist name=":form_type()" id="v"}
                            <option value="{$key}" {if condition="$key eq $vo['type']"}selected{/if}>[{$key}]{$v}</option>
                        {/volist}
                        </select>
                    </td>
                    <td>
                        <textarea name="config[options][]" class="layui-textarea" style="min-height:20px;padding:0;" placeholder="选项值:选项名">{volist name="vo['options']" id="v"}{$key}:{$v}{php}echo "\r\n";{/php}{/volist}</textarea>
                    </td>
                    <td><input type="text" name="config[value][]" class="layui-input" value="{$vo['value']}"></td>
                    <td><input type="text" name="config[tips][]" class="layui-input" value="{$vo['tips']}"></td>
                    <td><a href="javascript:;" class="tr-del">删除</a></td>
                </tr>
                {/volist}
                <tr>
                    <td colspan="8" style="background-color:#f8f8f8">
                        <a class="layui-btn layui-btn-small j-add-tr" data-tpl="config">添加配置</a>
                        <span class="layui-word-aux">提示：当配置类型为单选按钮、多选框、下拉框、开关的时候，配置选项为必填，参考格式：选项值:选项名，多个选项请换行。</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="layui-tab-item" title="模块依赖">
        <fieldset class="layui-elem-field layui-field-title">
          <legend>模块依赖清单</legend>
        </fieldset>
        <table class="layui-table" lay-even="" lay-skin="row">
            <thead>
                <tr>
                    <th width="160">模块名</th>
                    <th>模块唯一标识</th>
                    <th>依赖版本</th>
                    <th width="160">对比方式</th>
                    <th width="50">操作</th>
                </tr> 
            </thead>
            <tbody>
                {volist name="module_info['module_depend']" id="vo"}
                <tr>
                    <td><input type="text" name="module_depend[name][]" class="layui-input" lay-verify="required" value="{$vo[0]}"></td>
                    <td><input type="text" name="module_depend[identifier][]" class="layui-input" lay-verify="required" value="{$vo[1]}"></td>
                    <td><input type="text" name="module_depend[version][]" class="layui-input" value="{$vo[2]}"></td>
                    <td>
                        <select name="module_depend[type][]">
                            <option value="<" {if condition="$vo[3] eq '<'"}selected{/if}>（ < ）小于</option>
                            <option value="<=" {if condition="$vo[3] eq '<='"}selected{/if}>（<=）小于等于</option>
                            <option value=">" {if condition="$vo[3] eq '>'"}selected{/if}>（ > ）大于</option>
                            <option value=">=" {if condition="$vo[3] eq '>='"}selected{/if}>（>=）大于等于</option>
                            <option value="=" {if condition="$vo[3] eq '='"}selected{/if}>（ = ）等于</option>
                        </select>
                    </td>
                    <td><a href="javascript:;" class="tr-del">删除</a></td>
                </tr>
                {/volist}
                <tr>
                    <td colspan="5" style="background-color:#f8f8f8">
                        <a class="layui-btn layui-btn-small j-add-tr" data-tpl="module">添加模块依赖</a>
                        <span class="layui-word-aux">如果您的模块有依赖其他模块，必须添加此清单</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="layui-tab-item" title="插件依赖">
        <fieldset class="layui-elem-field layui-field-title">
          <legend>插件依赖清单</legend>
        </fieldset>
        <table class="layui-table" lay-even="" lay-skin="row">
            <thead>
                <tr>
                    <th width="160">插件名</th>
                    <th>插件唯一标识</th>
                    <th>依赖版本</th>
                    <th width="160">对比方式</th>
                    <th width="50">操作</th>
                </tr> 
            </thead>
            <tbody>
                {volist name="module_info['plugin_depend']" id="vo"}
                <tr>
                    <td><input type="text" name="plugin_depend[name][]" class="layui-input" lay-verify="required" value="{$vo[0]}"></td>
                    <td><input type="text" name="plugin_depend[identifier][]" class="layui-input" lay-verify="required" value="{$vo[1]}"></td>
                    <td><input type="text" name="plugin_depend[version][]" class="layui-input" value="{$vo[2]}"></td>
                    <td>
                        <select name="plugin_depend[type][]">
                            <option value="<" {if condition="$vo[3] eq '<'"}selected{/if}>（ < ）小于</option>
                            <option value="<=" {if condition="$vo[3] eq '<='"}selected{/if}>（<=）小于等于</option>
                            <option value=">" {if condition="$vo[3] eq '>'"}selected{/if}>（ > ）大于</option>
                            <option value=">=" {if condition="$vo[3] eq '>='"}selected{/if}>（>=）大于等于</option>
                            <option value="=" {if condition="$vo[3] eq '='"}selected{/if}>（ = ）等于</option>
                        </select>
                    </td>
                    <td><a href="javascript:;" class="tr-del">删除</a></td>
                </tr>
                {/volist}
                <tr>
                    <td colspan="5" style="background-color:#f8f8f8">
                        <a class="layui-btn layui-btn-small j-add-tr" data-tpl="plugin">添加插件依赖</a>
                        <span class="layui-word-aux">如果您的模块有依赖其他插件，必须添加此清单</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="layui-tab-item" title="数据库设置">
        <div class="layui-form-item">
            <label class="layui-form-label">模块表前缀</label>
            <div class="layui-input-inline w300">
                <input type="text" class="layui-input field-db_prefix" name="db_prefix" lay-verify="required">
            </div>
            <div class="layui-form-mid layui-word-aux">当前模块有数据库表时必须配置</div>
        </div>
        <fieldset class="layui-elem-field layui-field-title">
          <legend>数据库表清单</legend>
        </fieldset>
        <table class="layui-table" lay-even="" lay-skin="row">
            <thead>
                <tr>
                    <th width="160">数据库表名</th>
                    <th width="50">操作</th>
                </tr> 
            </thead>
            <tbody>
                {volist name="module_info['tables']" id="vo"}
                <tr>
                    <td><input type="text" name="tables[]" class="layui-input" lay-verify="required" minlength="2" maxlength="50" value="{$vo}"></td>
                    <td><a href="javascript:;" class="tr-del">删除</a></td>
                </tr>
                {/volist}
                <tr>
                    <td colspan="2" style="background-color:#f8f8f8">
                        <a class="layui-btn layui-btn-small j-add-tr" data-tpl="table">添加记录</a>
                        <span class="layui-word-aux">有数据库表时必需添加此清单,<b class="red">不包含表前缀</b></span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="layui-tab-item" title="预埋钩子">
        <fieldset class="layui-elem-field layui-field-title">
          <legend>预埋钩子清单</legend>
        </fieldset>
        <table class="layui-table" lay-even="" lay-skin="row">
            <thead>
                <tr>
                    <th width="200">钩子名称</th>
                    <th>钩子描述</th>
                    <th width="50">操作</th>
                </tr> 
            </thead>
            <tbody>
                {volist name="module_info['hooks']" id="vo"}
                <tr>
                    <td><input type="text" name="hooks[key][]" class="layui-input" lay-verify="required" minlength="2" maxlength="50" value="{$key}"></td>
                    <td><input type="text" name="hooks[desc][]" class="layui-input" minlength="2" maxlength="100" lay-verify="required" value="{$vo}"></td>
                    <td><a href="javascript:;" class="tr-del">删除</a></td>
                </tr>
                {/volist}
                <tr>
                    <td colspan="5" style="background-color:#f8f8f8">
                        <a class="layui-btn layui-btn-small j-add-tr" data-tpl="hook">添加钩子</a>
                        <span class="layui-word-aux">必须重装模块后新添加的钩子才生效</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="layui-form-item">
        <div class="layui-input-block">
            <input type="hidden" class="field-id" name="id">
            <button type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit">提交保存</button>
            <a href="{:url('index')}" class="layui-btn layui-btn-primary ml10"><i class="aicon ai-fanhui"></i>返回</a>
        </div>
    </div>
</form>
<script type="text/html" id="configTr">
    <tr>
        <td><input type="text" name="config[sort][]" class="layui-input" lay-verify="required" value="{i}"></td>
        <td><input type="text" name="config[title][]" class="layui-input" lay-verify="required"></td>
        <td><input type="text" name="config[name][]" class="layui-input" lay-verify="required"></td>
        <td>
            <select name="config[type][]" class="field-type" type="select">
            {volist name=":form_type()" id="v"}
                <option value="{$key}">[{$key}]{$v}</option>
            {/volist}
            </select>
        </td>
        <td><textarea name="config[options][]" class="layui-textarea" style="min-height:20px;padding:0;" placeholder="选项值:选项名"></textarea></td>
        <td><input type="text" name="config[value][]" class="layui-input"></td>
        <td><input type="text" name="config[tips][]" class="layui-input"></td>
        <td><a href="javascript:;" class="tr-del">删除</a></td>
    </tr>
</script>
<script type="text/html" id="moduleTr">
    <tr>
        <td><input type="text" name="module_depend[name][]" class="layui-input" lay-verify="required"></td>
        <td><input type="text" name="module_depend[identifier][]" class="layui-input" lay-verify="required" placeholder="模块名.[应用ID].module.[应用分支ID]"></td>
        <td><input type="text" name="module_depend[version][]" class="layui-input" placeholder="主版本号.次版本号.修订版本号"></td>
        <td>
            <select name="module_depend[type][]">
                <option value="<">（ < ）小于</option>
                <option value="<=">（<=）小于等于</option>
                <option value=">">（ > ）大于</option>
                <option value=">=">（>=）大于等于</option>
                <option value="=">（ = ）等于</option>
            </select>
        </td>
        <td><a href="javascript:;" class="tr-del">删除</a></td>
    </tr>
</script>
<script type="text/html" id="pluginTr">
    <tr>
        <td><input type="text" name="plugin_depend[name][]" class="layui-input" lay-verify="required"></td>
        <td><input type="text" name="plugin_depend[identifier][]" class="layui-input" lay-verify="required" placeholder="插件名.[应用ID].plugins.[应用分支ID]"></td>
        <td><input type="text" name="plugin_depend[version][]" class="layui-input" placeholder="格式：主版本号.次版本号.修订版本号"></td>
        <td>
            <select name="plugin_depend[type][]">
                <option value="<">（ < ）小于</option>
                <option value="<=">（<=）小于等于</option>
                <option value=">">（ > ）大于</option>
                <option value=">=">（>=）大于等于</option>
                <option value="=">（ = ）等于</option>
            </select>
        </td>
        <td><a href="javascript:;" class="tr-del">删除</a></td>
    </tr>
</script>
<script type="text/html" id="hookTr">
    <tr>
        <td><input type="text" name="hooks[key][]" class="layui-input" lay-verify="required" minlength="2" maxlength="50" placeholder="支持字母、下划线、数字"></td>
        <td><input type="text" name="hooks[desc][]" class="layui-input" minlength="2" maxlength="100" lay-verify="required"></td>
        <td><a href="javascript:;" class="tr-del">删除</a></td>
    </tr>
</script>
<script type="text/html" id="tableTr">
    <tr>
        <td><input type="text" name="tables[]" class="layui-input" lay-verify="required" minlength="2" maxlength="50" placeholder="不含表前缀"></td>
        <td><a href="javascript:;" class="tr-del">删除</a></td>
    </tr>
</script>
{include file="block/layui" /}
<script type="text/javascript">
    var formData = {:json_encode($data_info)};

    layui.use(['jquery', 'form', 'upload'], function(){
        var $ = layui.jquery, form = layui.form, upload = layui.upload;

        upload.render({
            elem: '.layui-upload',
            url: '{:url('icon?id='.$data_info['id'])}'
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
                input.siblings('img').attr('src', res.msg).show();
            }
        });

        $('.j-add-tr').click(function(){
            var that = $(this), tpl = $('#'+that.attr('data-tpl')+'Tr').html(), len = that.parents('tbody').find('tr').length;
            that.parents('tr').before(tpl.replace(/{i}/g, len+99));
            form.render();
        });

        $(document).on('click', '.tr-del', function(){
            $(this).parent().parent().remove();
        });
    });
</script>
<script src="__ADMIN_JS__/footer.js"></script>