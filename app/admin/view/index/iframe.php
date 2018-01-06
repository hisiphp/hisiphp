<!DOCTYPE html>
<html>
<head>
    <title>管理控制台 -  Powered by {:config('hisiphp.name')}</title>
    <meta http-equiv="Access-Control-Allow-Origin" content="*">
    <link rel="stylesheet" href="__ADMIN_JS__/layui/css/layui.css?v={:config('hisiphp.version')}">
    <link rel="stylesheet" href="__ADMIN_CSS__/style.css?v={:config('hisiphp.version')}">
    <link rel="stylesheet" href="__STATIC__/fonts/typicons/min.css?v={:config('hisiphp.version')}">
    <link rel="stylesheet" href="__STATIC__/fonts/font-awesome/min.css?v={:config('hisiphp.version')}">
    <style type="text/css">
        .hs-iframe{width:100%;height:100%;}
        .layui-tab{position:absolute;left:0;top:0;height:100%;width:100%;z-index:10;margin:0;border:none;overflow:hidden;}
        .layui-tab-content{padding:0 0 0 10px;height:100%;}
        .layui-tab-item{height:100%;}
        .footer{position:fixed;left:0;bottom:0;z-index:998;}
    </style>
</head>
<body>
{php}
$ca = strtolower(request()->controller().'/'.request()->action());
{/php}
<div class="layui-layout layui-layout-admin">
    <div class="layui-header" style="z-index:999!important;">
        <div class="fl header-logo">管理控制台</div>
        <div class="fl header-fold"><a href="javascript:;" title="打开/关闭左侧导航" class="aicon ai-caidan" id="foldSwitch"></a></div>
        <ul class="layui-nav fl nobg main-nav">
            {volist name="_admin_menu" id="vo"}
                {if condition="($_admin_menu_parents['pid'] eq $vo['id'] and $ca neq 'plugins/run') or ($ca eq 'plugins/run' and $vo['id'] eq 3)"}
               <li class="layui-nav-item layui-this">
                {else /}
                <li class="layui-nav-item">
                {/if} 
                <a href="javascript:;">{$vo['title']}</a></li>
            {/volist}
        </ul>
        <ul class="layui-nav fr nobg head-info" lay-filter="">
            <li class="layui-nav-item">
                <a href="javascript:void(0);">{$admin_user['nick']}&nbsp;&nbsp;</a>
                <dl class="layui-nav-child">
                    <dd><a data-id="00" href="{:url('admin/user/info')}" class="admin-nav-item"><span style="color:#333">个人设置</span></a></dd>
                    <dd><a href="{:url('admin/user/iframe?val=0')}" class="j-ajax" refresh="yes">默认布局</a></dd>
                    <dd><a href="{:url('admin/publics/logout')}">退出登陆</a></dd>
                </dl>
            </li>
            <li class="layui-nav-item">
                <a href="javascript:void(0);">{$languages[cookie('admin_language')]['name']}&nbsp;&nbsp;</a>
                <dl class="layui-nav-child">
                    {volist name="languages" id="vo"}
                        {if condition="$vo['pack']"}
                        <dd><a href="{:url('admin/index/index')}?lang={$vo['code']}">{$vo['name']}</a></dd>
                        {/if}
                    {/volist}
                    <dd><a data-id="000" href="{:url('admin/language/index')}" class="admin-nav-item"><span style="color:#333">语言包管理</span></a></dd>
                </dl>
            </li>
            <li class="layui-nav-item"><a href="__ROOT_DIR__" target="_blank">前台</a></li>
            <li class="layui-nav-item"><a href="{:url('admin/index/clear')}" class="j-ajax" refresh="yes">清缓存</a></li>
            <li class="layui-nav-item"><a href="javascript:void(0);" id="lockScreen">锁屏</a></li>
        </ul>
    </div>
    <div class="layui-side layui-bg-black" id="switchNav">
        <div class="layui-side-scroll">
            {volist name="_admin_menu" id="v"}
            {if condition="($_admin_menu_parents['pid'] eq $v['id'] and $ca neq 'plugins/run') or ($ca eq 'plugins/run' and $v['id'] eq 3)"}
            <ul class="layui-nav layui-nav-tree">
            {else /}
            <ul class="layui-nav layui-nav-tree" style="display:none;">
            {/if}
                {volist name="v['childs']" id="vv" key="kk"}
                <li class="layui-nav-item {if condition="$kk eq 1"}layui-nav-itemed{/if}">
                    <a href="javascript:;"><i class="{$vv['icon']}"></i>{$vv['title']}<span class="layui-nav-more"></span></a>
                    <dl class="layui-nav-child">
                        {if condition="$vv['title'] eq '快捷菜单'"}
                            <dd><a class="admin-nav-item" data-id="0" href="{:url('admin/index/welcome')}"><i class="aicon ai-shouye"></i> 后台首页</a></dd>
                            {volist name="vv['childs']" id="vvv"}
                            <dd><a class="admin-nav-item" data-id="{$vvv['id']}" href="{:url($vvv['url'].'?'.$vvv['param'])}"><i class="{$vvv['icon']}"></i> {$vvv['title']}</a><i data-href="{:url('menu/del?ids='.$vvv['id'])}" class="layui-icon j-del-menu">&#xe640;</i></dd>
                            {/volist}
                        {else /}
                            {volist name="vv['childs']" id="vvv"}
                            <dd><a class="admin-nav-item" data-id="{$vvv['id']}" href="{if condition="strpos('http', $vvv['url']) heq false"}{:url($vvv['url'].'?'.$vvv['param'])}{else /}{$vvv['url']}{/if}"><i class="{$vvv['icon']}"></i> {$vvv['title']}</a></dd>
                            {/volist}
                        {/if}
                    </dl>
                </li>
                {/volist}
            </ul>
            {/volist}
        </div>
    </div>
    <div class="layui-body" id="switchBody">
        <div class="layui-tab layui-tab-card" lay-filter="hisiTab" lay-allowClose="true">
            <ul class="layui-tab-title">
                <li lay-id="0" class="layui-this">后台首页</li>
            </ul>
            <div class="layui-tab-content">
                <div class="layui-tab-item layui-show">
                    <iframe lay-id="0" src="{:url('index/welcome')}" width="100%" height="100%" frameborder="0" scrolling="yes" class="hs-iframe"></iframe>
                </div>
            </div>
        </div>
    </div>
    <div class="layui-footer footer">
        <span class="fl">Powered by <a href="{:config('hisiphp.url')}" target="_blank">{:config('hisiphp.name')}</a> v{:config('hisiphp.version')}</span>
        <span class="fr"> © 2017-2018 <a href="{:config('hisiphp.url')}" target="_blank">{:config('hisiphp.copyright')}</a> All Rights Reserved.</span>
    </div>
</div>
{include file="block/layui" /}
<script type="text/javascript">
    layui.use(['jquery', 'element', 'layer'], function() {
        var $ = layui.jquery, element = layui.element, layer = layui.layer;
        $('.layui-tab-content').height($(window).height() - 145);
        var tab = {
                add: function(title, url, id) {
                    element.tabAdd('hisiTab', {
                        title: '<i class="layui-icon j-ajax" data-href="{:url('admin/menu/quick')}?id='+id+'">&#xe600;&nbsp;</i>'+title,
                        content: '<iframe width="100%" height="100%" lay-id="'+id+'" frameborder="0" src="'+url+'" scrolling="yes" class="x-iframe"></iframe>',
                        id: id
                    });
                }, change: function(id) {
                  element.tabChange('hisiTab', id);
                }
            };
        $('.admin-nav-item').click(function(event) {
            var that = $(this);
            if ($('iframe[src="'+that.attr('href')+'"]')[0]) {
                tab.change(that.attr('data-id'));
                event.stopPropagation();
                return false;
            }
            if ($('iframe').length == 10) {
                layer.msg('最多可打开10个标签页');
                return false;
            }
            that.css({color:'#fff'});
            tab.add(that.text(), that.attr('href'), that.attr('data-id'));
            tab.change(that.attr('data-id'));
            event.stopPropagation();
            return false;
        });
        $(document).on('click', '.layui-tab-close', function() {
            $('.layui-nav-child a[data-id="'+$(this).parent('li').attr('lay-id')+'"]').css({color:'rgba(255,255,255,.7)'});
        });
    });
</script>
</body>
</html>