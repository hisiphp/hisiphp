<style type="text/css">
    #popLoginBox{padding:0 20px!important;}
</style>
<table class="layui-table" lay-skin="line">
    <thead>
        <tr>
            <th>系统信息</th>
        </tr> 
    </thead>
    <tbody>
        <tr>
            <td>云平台：<span class="mcolor" id="connectionStatus">...</span></td>
        </tr>
        <tr>
            <td>绑定账号：{if condition="config('hs_cloud.identifier')"}{:substr(config('hs_cloud.identifier'), 0, 5)}***{:substr(config('hs_cloud.identifier'), -5)} <a href="javascript:void(0);" class="mcolor2 cloudBind">重新绑定</a>{else /}<a href="javascript:void(0);" class="mcolor cloudBind">绑定云平台账号</a><span class="font12" style="color:#999"> [温馨提示：只有绑定了云平台账号，才可以使用云平台服务]</span>{/if}</td>
        </tr>
        <tr>
            <td>当前版本：v{:config('hisiphp.version')}&nbsp;&nbsp;{if condition="config('hs_cloud.identifier')"}<a href="{:url('lists')}" style="display:none" id="upgrade" class="mcolor">点此获取升级</a>{else /}<a href="javascript:layer.msg('请先绑定账号！');" style="display:none" id="upgrade" class="mcolor">点此获取升级</a>{/if}</td>
        </tr>
        <tr>
            <td>授权认证：{$_SERVER['SERVER_NAME']} <span class="red">未认证</span></td>
        </tr>
        <tr>
            <td>运行环境：{$_SERVER["SERVER_SOFTWARE"]}</td>
        </tr>
        <tr>
            <td>服务器时间：{:date("Y年n月j日 H:i:s")}</td>
        </tr>
    </tbody>
</table>
{include file="block/layui" /}
{include file="block/bind_cloud" /}
<script type="text/javascript">
layui.use(['jquery', 'layer'], function() {
    var $ = layui.jquery, layer = layui.layer;
    $.ajax({
        url:'{$api_url}connection',
        data:'domain={$_SERVER["SERVER_NAME"]}&version={:config("hisiphp.version")}',
        error:function(){
            $('#connectionStatus').html('<strong class="red">通信异常</strong>');
        },success:function(){
            $('#connectionStatus').html('通信正常');
            $('#upgrade').show();
        }
    });

    $('.cloudBind').on('click', function() {
        layer.open({
            title:'绑定云平台 / <a href="https://store.hisiphp.com/act/reg?domain={$_SERVER["SERVER_NAME"]}" target="_blank" class="mcolor">注册云平台</a>',
            id:'popLoginBox',
            area:'380px',
            content:$('#popCloudBind').html(),
            btn:['确认绑定', '取消'],
            btnAlign:'c',
            move:false,
            yes:function(index) {
                var tips = $('#resultTips');
                tips.html('请稍后，云平台通信中...');
                $.post('{:url('')}', $('#cloudForm').serialize(), function(res) {
                    if (res.code == 1) {
                        layer.msg(res.msg);
                        setTimeout(function() {
                            location.reload();
                        }, 3000);
                    } else {
                        tips.addClass('red').html(res.msg);
                        setTimeout(function() {
                            tips.removeClass('red').html('');
                        }, 3000);
                    }
                });
                return false;
            }
        });
    });
});
</script>