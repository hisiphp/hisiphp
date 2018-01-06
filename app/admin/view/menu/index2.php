<div class="layui-form menu-dl">
<form class="page-list-form">
    <div class="page-toolbar">
        <div class="layui-btn-group fl">
            <a href="{:url('add?pid='.$pid)}" class="layui-btn layui-btn-primary"><i class="aicon ai-tianjia"></i>添加子菜单</a>
            <a data-href="{:url('status?table=admin_menu&val=1')}" class="layui-btn layui-btn-primary j-page-btns"><i class="aicon ai-qiyong"></i>启用</a>
            <a data-href="{:url('status?table=admin_menu&val=0')}" class="layui-btn layui-btn-primary j-page-btns"><i class="aicon ai-jinyong1"></i>禁用</a>
            <a data-href="{:url('del')}" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="aicon ai-jinyong"></i>删除</a>
            <a href="{:url('export?id='.$pid)}" class="layui-btn layui-btn-primary"><i class="aicon ai-daochu"></i>导出</a>
        </div>
    </div>
    <dl class="menu-dl1 menu-hd mt10">
        <dt>菜单名称</dt>
        <dd>
            <span class="hd">排序</span>
            <span class="hd2">状态</span>
            <span class="hd3">操作</span>
        </dd>
    </dl>
    {volist name="menu_list" id="vv" key="kk"}
    <dl class="menu-dl1">
        <dt>
            <input type="checkbox" name="ids[{$kk}]" value="{$vv['id']}" class="checkbox-ids" lay-skin="primary" title="{$vv['title']}"><div class="layui-unselect layui-form-checkbox" lay-skin="primary"><span>{$vv['title']}</span><i class="layui-icon">&#xe626;</i></div>
            <input type="text" class="menu-sort j-ajax-input" name="sort[{$kk}]" onkeyup="value=value.replace(/[^\d]/g,'')" value="{$vv['sort']}" data-value="{$vv['sort']}" data-href="{:url('sort?table=admin_menu&ids='.$vv['id'])}">
            <input type="checkbox" name="status" value="{$vv['status']}" {if condition="$vv['status'] eq 1"}checked=""{/if} lay-skin="switch" lay-filter="switchStatus" lay-text="正常|关闭" data-href="{:url('status?table=admin_menu&ids='.$vv['id'])}"><div class="layui-unselect layui-form-switch layui-form-onswitch" lay-skin="_switch"><em>{if condition="$vv['status'] eq 1"}正常{else /}关闭{/if}</em><i></i></div>
            <div class="menu-btns">
                <a href="{:url('edit?id='.$vv['id'])}" title="编辑"><i class="layui-icon">&#xe642;</i></a>
                <a href="{:url('add?pid='.$vv['id'].'&mod='.$vv['module'])}" title="添加子菜单"><i class="layui-icon">&#xe654;</i></a>
                <a href="{:url('del?ids='.$vv['id'])}" title="删除"><i class="layui-icon">&#xe640;</i></a>
            </div>
        </dt>
        <dd>
            {php}
                $kk++;
            {/php}
            {volist name="vv['childs']" id="vvv" key="kkk"}
            {php}
                if ($vvv['title'] == '预留占位') continue;
                $kk++;
            {/php}
            <dl class="menu-dl2">
                <dt>
                    <input type="checkbox" name="ids[{$kk}]" value="{$vvv['id']}" class="checkbox-ids" lay-skin="primary" title="{$vvv['title']}"><div class="layui-unselect layui-form-checkbox" lay-skin="primary"><span>{$vvv['title']}</span><i class="layui-icon">&#xe626;</i></div>
                    <input type="text" class="menu-sort j-ajax-input" name="sort[{$kk}]" onkeyup="value=value.replace(/[^\d]/g,'')" value="{$vvv['sort']}" data-value="{$vvv['sort']}" data-href="{:url('sort?table=admin_menu&ids='.$vvv['id'])}">
                    <input type="checkbox" name="status" value="{$vvv['status']}" {if condition="$vvv['status'] eq 1"}checked=""{/if} lay-skin="switch" lay-filter="switchStatus" lay-text="正常|关闭" data-href="{:url('status?table=admin_menu&ids='.$vvv['id'])}"><div class="layui-unselect layui-form-switch layui-form-onswitch" lay-skin="_switch"><em>{if condition="$vvv['status'] eq 1"}正常{else /}关闭{/if}</em><i></i></div>
                    <div class="menu-btns">
                        <a href="{:url('edit?id='.$vvv['id'])}" title="编辑"><i class="layui-icon">&#xe642;</i></a>
                        <a href="{:url('add?pid='.$vvv['id'].'&mod='.$vvv['module'])}" title="添加子菜单"><i class="layui-icon">&#xe654;</i></a>
                        <a href="{:url('del?ids='.$vvv['id'])}" title="删除"><i class="layui-icon">&#xe640;</i></a>
                    </div>
                </dt>
                {php}
                    $kk++;
                {/php}
                {volist name="vvv['childs']" id="vvvv" key="kkkk"}
                {php}
                    $kk++;
                {/php}
                <dd>
                    <input type="checkbox" name="ids[{$kk}]" value="{$vvvv['id']}" class="checkbox-ids" lay-skin="primary" title="{$vvvv['title']}"><div class="layui-unselect layui-form-checkbox" lay-skin="primary"><span>{$vvvv['title']}</span><i class="layui-icon">&#xe626;</i></div>
                    <input type="text" class="menu-sort j-ajax-input" name="sort[{$kk}]" onkeyup="value=value.replace(/[^\d]/g,'')" value="{$vvvv['sort']}" data-value="{$vvvv['sort']}" data-href="{:url('sort?table=admin_menu&ids='.$vvvv['id'])}">
                    <input type="checkbox" name="status" value="{$vvvv['status']}" {if condition="$vvvv['status'] eq 1"}checked=""{/if} lay-skin="switch" lay-filter="switchStatus" lay-text="正常|关闭" data-href="{:url('status?table=admin_menu&ids='.$vvvv['id'])}"><div class="layui-unselect layui-form-switch layui-form-onswitch" lay-skin="_switch"><em>{if condition="$vvvv['status'] eq 1"}正常{else /}关闭{/if}</em><i></i></div>
                    <div class="menu-btns">
                        <a href="{:url('edit?id='.$vvvv['id'])}" title="编辑"><i class="layui-icon">&#xe642;</i></a>
                        <a href="{:url('add?pid='.$vvvv['id'].'&mod='.$vvvv['module'])}" title="添加子菜单"><i class="layui-icon">&#xe654;</i></a>
                        <a href="{:url('del?ids='.$vvvv['id'])}" title="删除之后无法恢复，您确定要删除吗？" class="j-ajax"><i class="layui-icon">&#xe640;</i></a>
                    </div>
                </dd>
                {/volist}
            </dl>
            {/volist}
        </dd>
    </dl>
    {php}
        $kk++;
    {/php}
    {/volist}
</form>
</div>
<div class="layui-tab-item layui-form menu-dl">
    <form class="page-list-form">
        <dl class="menu-dl1 menu-hd mt10">
            <dt>模块名称</dt>
            <dd>
                <span class="hd">排序</span>
                <span class="hd2">状态</span>
                <span class="hd3">操作</span>
            </dd>
        </dl>
        {volist name="menu_list" id="v" key="k"}
        <dl class="menu-dl1">
            <dt>
                <input type="checkbox" name="ids[{$k}]" class="checkbox-ids" value="{$v['id']}" lay-skin="primary" title="{$v['title']}"><div class="layui-unselect layui-form-checkbox" lay-skin="primary"><span>{$v['title']}</span><i class="layui-icon">&#xe626;</i></div>
                <input type="text" class="layui-input j-ajax-input menu-sort" name="sort[{$k}]" onkeyup="value=value.replace(/[^\d]/g,'')" value="{$v['sort']}" data-value="{$v['sort']}" data-href="{:url('sort?table=admin_menu&ids='.$v['id'])}">
                <input type="checkbox" name="status" value="{$v['status']}" {if condition="$v['status'] eq 1"}checked=""{/if} lay-skin="switch" lay-filter="switchStatus" lay-text="正常|关闭" data-href="{:url('status?table=admin_menu&ids='.$v['id'])}"><div class="layui-unselect layui-form-switch layui-form-onswitch" lay-skin="_switch"><em>{if condition="$v['status'] eq 1"}正常{else /}关闭{/if}</em><i></i></div>
                <div class="menu-btns">
                <a href="{:url('del?ids='.$v['id'])}" title="删除之后无法恢复，您确定要删除吗？" class="j-ajax"><i class="layui-icon">&#xe640;</i></a>
                </div>
            </dt>
        </dl>
        {/volist}
    </form>
</div>