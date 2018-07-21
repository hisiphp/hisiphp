{if condition="input('param.hisi_iframe') || cookie('hisi_iframe')"}
<!DOCTYPE html>
<html>
<head>
    <title>{$_admin_menu_current['title']} -  Powered by {:config('hisiphp.name')}</title>
    <meta http-equiv="Access-Control-Allow-Origin" content="*">
    <link rel="stylesheet" href="__ADMIN_JS__/layui/css/layui.css?v={:config('hisiphp.version')}">
    <link rel="stylesheet" href="__ADMIN_CSS__/theme.css?v={:config('hisiphp.version')}">
    <link rel="stylesheet" href="__ADMIN_CSS__/style.css?v={:config('hisiphp.version')}">
    <link rel="stylesheet" href="__STATIC__/fonts/typicons/min.css?v={:config('hisiphp.version')}">
    <link rel="stylesheet" href="__STATIC__/fonts/font-awesome/min.css?v={:config('hisiphp.version')}">
</head>
<body class="hisi-theme-{:cookie('hisi_admin_theme')}">
<div style="padding:0 10px;" class="mcolor">{:runhook('system_admin_tips')}</div>
{else /}
<!DOCTYPE html>
<html>
<head>
    <title>{if condition="$_admin_menu_current['url'] eq 'admin/index/index'"}管理控制台{else /}{$_admin_menu_current['title']}{/if} -  Powered by {:config('hisiphp.name')}</title>
    <meta http-equiv="Access-Control-Allow-Origin" content="*">
    <link rel="stylesheet" href="__ADMIN_JS__/layui/css/layui.css?v={:config('hisiphp.version')}">
    <link rel="stylesheet" href="__ADMIN_CSS__/theme.css?v={:config('hisiphp.version')}">
    <link rel="stylesheet" href="__ADMIN_CSS__/style.css?v={:config('hisiphp.version')}">
    <link rel="stylesheet" href="__STATIC__/fonts/typicons/min.css?v={:config('hisiphp.version')}">
    <link rel="stylesheet" href="__STATIC__/fonts/font-awesome/min.css?v={:config('hisiphp.version')}">
</head>
<body class="hisi-theme-{:cookie('hisi_admin_theme')}">
{php}
$ca = strtolower(request()->controller().'/'.request()->action());
{/php}
<div class="layui-layout layui-layout-admin">
    {include file="admin@block/menu" /}
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