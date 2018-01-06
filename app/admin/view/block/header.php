{if condition="input('param.hisi_iframe') || cookie('hisi_iframe')"}
<!DOCTYPE html>
<html>
<head>
    <title>{$_admin_menu_current['title']} -  Powered by {:config('hisiphp.name')}</title>
    <meta http-equiv="Access-Control-Allow-Origin" content="*">
    <link rel="stylesheet" href="__ADMIN_JS__/layui/css/layui.css?v={:config('hisiphp.version')}">
    <link rel="stylesheet" href="__ADMIN_CSS__/style.css?v={:config('hisiphp.version')}">
    <link rel="stylesheet" href="__STATIC__/fonts/typicons/min.css?v={:config('hisiphp.version')}">
    <link rel="stylesheet" href="__STATIC__/fonts/font-awesome/min.css?v={:config('hisiphp.version')}">
</head>
<body>
<div style="padding:0 10px;" class="mcolor">{:runhook('system_admin_tips')}</div>
{else /}
<!DOCTYPE html>
<html>
<head>
    <title>{if condition="$_admin_menu_current['url'] eq 'admin/index/index'"}管理控制台{else /}{$_admin_menu_current['title']}{/if} -  Powered by {:config('hisiphp.name')}</title>
    <meta http-equiv="Access-Control-Allow-Origin" content="*">
    <link rel="stylesheet" href="__ADMIN_JS__/layui/css/layui.css?v={:config('hisiphp.version')}">
    <link rel="stylesheet" href="__ADMIN_CSS__/style.css?v={:config('hisiphp.version')}">
    <link rel="stylesheet" href="__STATIC__/fonts/typicons/min.css?v={:config('hisiphp.version')}">
    <link rel="stylesheet" href="__STATIC__/fonts/font-awesome/min.css?v={:config('hisiphp.version')}">
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
        <ul class="layui-nav fr nobg head-info">
            <li class="layui-nav-item">
                <a href="javascript:void(0);">{$admin_user['nick']}&nbsp;&nbsp;</a>
                <dl class="layui-nav-child">
                    <dd><a href="{:url('admin/user/info')}">个人设置</a></dd>
                    <dd><a href="{:url('admin/user/iframe?val=1')}" class="j-ajax" refresh="yes">框架布局</a></dd>
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
                    <dd><a href="{:url('admin/language/index')}">语言包管理</a></dd>
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
                            <dd><a class="admin-nav-item" href="{:url('admin/index/index')}"><i class="aicon ai-shouye"></i> 后台首页</a></dd>
                            {volist name="vv['childs']" id="vvv"}
                            <dd><a class="admin-nav-item" href="{:url($vvv['url'].'?'.$vvv['param'])}"><i class="{$vvv['icon']}"></i> {$vvv['title']}</a><i data-href="{:url('menu/del?ids='.$vvv['id'])}" class="layui-icon j-del-menu">&#xe640;</i></dd>
                            {/volist}
                        {else /}
                            {volist name="vv['childs']" id="vvv"}
                            <dd><a class="admin-nav-item" href="{if condition="strpos('http', $vvv['url']) heq false"}{:url($vvv['url'].'?'.$vvv['param'])}{else /}{$vvv['url']}{/if}"><i class="{$vvv['icon']}"></i> {$vvv['title']}</a></dd>
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
        <ul class="bread-crumbs">
            {volist name="_bread_crumbs" id="v"}
                {if condition="$key gt 0 && $i neq count($_bread_crumbs)"}
                    <li>></li>
                    <li><a href="{:url($v['url'].'?'.$v['param'])}">{$v['title']}</a></li>
                {elseif condition="$i eq count($_bread_crumbs)" /}
                    <li>></li>
                    <li><a href="javascript:void(0);">{$v['title']}</a></li>
                {else /}
                    <li><a href="javascript:void(0);">{$v['title']}</a></li>
                {/if}
            {/volist}
            <li><a href="{:url('admin/menu/quick?id='.$_admin_menu_current['id'])}" title="添加到首页快捷菜单" class="j-ajax">[+]</a></li>
        </ul>
        <div style="padding:0 10px;" class="mcolor">{:runhook('system_admin_tips')}</div>
        <div class="page-body">
{/if}