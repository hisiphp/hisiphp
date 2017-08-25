{include file="block/header" /}
{switch name="$tab_type"}
    {case value="1"}
    {/* 分组切换[有链接] */}
        <div class="layui-tab layui-tab-card">
            <ul class="layui-tab-title">
                {volist name="tab_data['menu']" id="vo"}
                    {if condition="$vo['url'] eq $_admin_menu_current['url'] or (url($vo['url']) eq $tab_data['current'])"}
                    <li class="layui-this">
                    {else /}
                    <li>
                    {/if}
                    {if condition="substr($vo['url'], 0, 4) eq 'http'"}
                        <a href="{$vo['url']}" target="_blank">{$vo['title']}</a>
                    {else /}
                        <a href="{:url($vo['url'])}">{$vo['title']}</a>
                    {/if}
                    </li>
                {/volist}
                <div class="tool-btns">
                    <a href="javascript:location.reload();" title="刷新当前页面" class="aicon ai-shuaxin2 font18"></a>
                    <a href="javascript:;" class="aicon ai-quanping1 font18" id="fullscreen-btn" title="打开/关闭全屏"></a>
                </div>
            </ul>
            <div class="layui-tab-content page-tab-content">
                <div class="layui-tab-item layui-show">
                    {__CONTENT__}
                </div>
            </div>
        </div>
    {/case}
    {case value="2"}
    {/* 分组切换[无链接] */}
        <div class="layui-tab layui-tab-card">
            <ul class="layui-tab-title">
                {volist name="tab_data['menu']" id="vo" key="k"}
                    {if condition="$k eq 1"}
                    <li class="layui-this">
                    {else /}
                    <li>
                    {/if}
                    <a href="javascript:;">{$vo['title']}</a>
                    </li>
                {/volist}
                <div class="tool-btns">
                    <a href="javascript:location.reload();" title="刷新当前页面" class="aicon ai-shuaxin2 font18"></a>
                    <a href="javascript:;" class="aicon ai-quanping1 font18" id="fullscreen-btn" title="打开/关闭全屏"></a>
                </div>
            </ul>
            <div class="layui-tab-content page-tab-content">
                {__CONTENT__}
            </div>
        </div>
    {/case}
    {case value="3"}
    {/* 无需分组切换 */}
        {__CONTENT__}
    {/case}
    {default /}
    {/* 单个分组[无链接] */}
        <div class="layui-tab layui-tab-card">
            <ul class="layui-tab-title">
                <li class="layui-this">
                    <a href="javascript:;" id="curTitle">{$_admin_menu_current['title']}</a>
                </li>
                <div class="tool-btns">
                    <a href="javascript:location.reload();" title="刷新当前页面" class="aicon ai-shuaxin2 font18"></a>
                    <a href="javascript:;" class="aicon ai-quanping1 font18" id="fullscreen-btn" title="打开/关闭全屏"></a>
                </div>
            </ul>
            <div class="layui-tab-content page-tab-content">
                <div class="layui-tab-item layui-show">
                    {__CONTENT__}
                </div>
            </div>
        </div>
{/switch}
{include file="block/footer" /}