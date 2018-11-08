<style type="text/css">
    .layui-table-tool-temp{padding-right:0;}
</style>
<table id="dataTable"></table>
{include file="block/layui" /}
<script type="text/javascript">
    layui.use(['table', 'jquery', 'layer', 'laytpl', 'form'], function() {
        var si, 
            table = layui.table, 
            $ = layui.jquery, 
            layer = layui.layer, 
            laytpl = layui.laytpl,
            form = layui.form,
            identifier = '{:config("hs_cloud.identifier")}',
            clientIp = '{:get_client_ip()}';

        var getParam = function(name) { 
                var value = "", isFound = !1, search = this.location.search; 
                if (search.indexOf("?") == 0 && search.indexOf("=") > 1) { 
                    var params = unescape(search).substring(1, search.length).split("&"), i = 0; 
                    while (i < params.length && !isFound) {
                        params[i].indexOf("=") > 0 && params[i].split("=")[0].toLowerCase() == name.toLowerCase() && (value = params[i].split("=")[1], isFound = !0), i++ 
                    }
                } 
                return value == "" && (value = null), value;
            };

        var install = function install(param) {
            $('.app-info').html('<div class="app-pay-success red">正在安装应用中，请勿刷新或关闭此页面</div>');
            $.get('{:url('install')}', param, function(res) {
                if (res.code == 1) {
                    $('.app-pay-success').removeClass('red').html('安装成功，稍后将自动刷新页面....');
                    setTimeout(function(){
                        location.reload();
                    }, 3000);
                } else {
                    $('.app-pay-success').html(res.msg);
                }
            }, 'json');
        };

        var specSelect = function() {
            var payment = $('.payment.layui-btn-normal').attr('data-value'),
                branchId = $('.branch_id.layui-btn-normal').attr('data-value'),
                price = $('.branch_id.layui-btn-normal').attr('data-price');
            $.ajax({
                url:'{$api_url}createOrder',
                data: {identifier: identifier,domain: '{:get_domain()}/', payment: payment, branch_id: branchId},
                dataType: 'jsonp',
                error: function(){
                    $('.app-order-id').html('支付二维码获取失败');
                },
                success: function(res) {
                    if (res.code == 200) {
                        var installParam = {
                            app_name: res.app_name, 
                            app_type: res.app_type, 
                            app_keys: res.app_keys, 
                            branch_id: branchId
                        };

                        if (res.app_keys != null && res.app_keys != '') {
                            $('.app-order-id').html('<a data-data=\''+JSON.stringify(installParam)+'\' class="layui-btn layui-btn-normal mt50" id="installBtn">点此安装</a>');
                        } else {
                            $('.app-price').html('￥'+price);
                            $('.app-order-id').html('<span class="red">支付时请备注订单号：'+res.order_id+'</span><br><a href="javascript:void(0);" class="layui-btn layui-btn-xs layui-btn-normal" id="showQuestion">点此查看常见问题</a>');
                            $('#qrcode').attr('src', res.qrcode).show();
                            // 定时刷新支付状态
                            var checkOrder = function() {
                                clearTimeout(si);
                                $.ajax({
                                    url:'{$api_url}checkOrder',
                                    data: {branch_id: branchId, identifier: identifier, domain: '{:get_domain()}/'},
                                    dataType: 'jsonp',
                                    success: function(result) {
                                        installParam.app_keys = result.app_keys;
                                        if (result.code == 200) {
                                            install(installParam);
                                        } else {
                                            si = setTimeout(function () {
                                                checkOrder();
                                            }, 5000);
                                        }
                                    }
                                });
                            }
                            setTimeout(function(){checkOrder()}, 5000);
                        }
                    } else {
                        $('.app-order-id').html(res.msg);
                    }
                }
            });
        };

        table.render({
            elem: '#dataTable'
            ,url: '{:url('', input('get.'))}'
            ,page: true
            ,toolbar: '#toolbar'
            ,defaultToolbar: []
            ,text: {none: '对不起！暂无相关应用，不过不用担心，一大波开发者正在开发中....'}
            ,cols: [[
                {field:'title', width:180, title: '应用名称', templet:function(d) {
                    return d.title+' <i data-title="'+d.title+'" data-data='+JSON.stringify(d.preview)+' class="layui-icon layui-icon-picture"></i>';
                }}
                ,{field:'intro', title: '应用简介'}
                ,{field:'author', width:100, title: '作者'}
                ,{field:'price', width:100, title: '价格', templet:function(d) {
                    return '￥'+d.price;
                }}
                ,{field:'sales', width:70, title: '下载'}
                ,{width:120, title: '操作', templet: '#buttonTpl'}
            ]]
            ,done:function(res, curr, count) {
                var type = getParam('type'), catId = getParam('cat_id');
                $('#type'+(type ? type : 1)).removeClass('layui-btn-primary').addClass('layui-btn-normal');
                $('#cats'+(catId ? catId : 0)).removeClass('layui-btn-primary').addClass('layui-btn-normal');
            }
        });

        // 按条件筛选
        $(document).on('click', '.app-filter', function() {
            var that = $(this), 
                _url = '{:url('')}',
                _id = that.attr('data-id'),
                type = getParam('type'),
                catId = getParam('cat_id');

            if (that.attr('data-type') == 1) {
                _url += '?type='+that.attr('data-id')+(catId ? '&cat_id='+catId : '');
            } else {
                _url += '?cat_id='+that.attr('data-id')+(type ? '&type='+type : '');
            }

            history.replaceState('', '', _url);
            table.reload('dataTable', {
              url: _url,
              page: 1
            });
            return false;
        });

        // 弹出安装界面
        $(document).on('click', '.pop-install', function() {
            var that = $(this), data = new Function('return '+ that.attr('data-data'))();
            if (identifier == null || identifier == '') {
                layer.open({
                    title:'登录云平台 / <a href="https://store.hisiphp.com/act/reg?domain={$_SERVER["SERVER_NAME"]}" target="_blank" class="mcolor">注册云平台</a>',
                    id:'popLoginBox',
                    area:'380px',
                    content:$('#popCloudBind').html(),
                    btn:['确认绑定', '取消'],
                    btnAlign:'c',
                    move:false,
                    yes:function(index) {
                        var tips = $('#resultTips'), 
                            account = $('#cloudAccount').val(), 
                            password = $('#cloudPassword').val();
                        tips.html('请稍后，云平台通信中...');
                        $.post('{:url("upgrade/index")}', {account: account, password: password}, function(res) {
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
                    },
                    success: function() {
                        $('#cloudForm .layui-word-aux').html('温馨提示：您需要登录云平台后才能安装此应用');
                    }
                });
            } else {
                laytpl($('#installTpl').html()).render(data, function(html) {
                    layer.open({
                        type: 1,
                        shade: 0.5,
                        title: '安装'+data.title,
                        skin: 'layui-layer-rim',
                        area: ['480px', '460px'],
                        content: html,
                        success: function(layero, index) {
                            specSelect();
                        },
                        cancel: function(index, layero) { 
                            clearTimeout(si);
                            return true; 
                        }  
                    });
                })
            }
            return false;
        });

        // 应用分支和支付方式切换
        $(document).on('click', '.app-spec-a', function() {
            var that = $(this);
            if (that.hasClass('layui-btn-normal')) {
                return false;
            }
            that.removeClass('layui-btn-primary').addClass('layui-btn-normal')
            that.siblings('a').addClass('layui-btn-primary').removeClass('layui-btn-normal');
            specSelect();
        });

        // 已购买的应用手动点击安装
        $(document).on('click', '#installBtn', function() {
            var that = $(this), param = new Function('return '+ that.attr('data-data'))();
            install(param);
        });

        $(document).on('click', '#showQuestion', function() {
            layer.open({
                type: 1,
                title: '购买常见问题',
                shadeClose: true,
                area:['500px', '300px'],
                content: $('#questionTpl').html()
            });
        });

        $(document).on('click', '.layui-icon-picture', function() {
            var that = $(this), data = new Function('return '+ that.attr('data-data'))(), json = [];
            for(var i in data) {
                json.push({alt: that.attr('data-title'), pid: '123', src: 'https://store.hisiphp.com'+data[i], 'thumb': 'https://store.hisiphp.com'+data[i]});
            }
            layer.photos({photos: {status: 1, start: 0, title: that.attr('data-title'), id: 1, data: json}});
        })
    });
</script>
<script type="text/html" id="questionTpl">
<pre>

  <span class="layui-badge layui-bg-blue">问</span> 付款成功了，还是显示未支付？
  <span class="layui-badge layui-bg-green">答</span> 支付成功后正常是会立即生效，2分钟内未生效请联系<a href="http://wpa.qq.com/msgrd?v=3&uin=364666827&site=qq&menu=yes" target="_top">QQ：364666827</a>

  <span class="layui-badge layui-bg-blue">问</span> 我想在本地测试没问题后再放上正式环境，怎么操作比较好？
  <span class="layui-badge layui-bg-green">答</span> 您可以通过<a href="https://jingyan.baidu.com/article/5bbb5a1b15c97c13eba1798a.html" class="red" target="_blank">修改hosts文件</a>实现线上域名本地解析，然后在绑定云平台。

  <span class="layui-badge layui-bg-blue">问</span> a.com已经购买过此应用了，为什么www.a.com还要再购买？
  <span class="layui-badge layui-bg-green">答</span> 应用授权采用单域名授权，单域名规则并不是以根域名来判断的。
</pre>
</script>
<script type="text/html" id="installTpl">
    <div class="layui-form">
        <dl class="app-spec">
            <dt>分支选择：</dt>
            <dd>
                {{# var i = 0; }}
                {{#  layui.each(d.branchs, function(index, item){ }}
                    <a href="javascript:void(0);" class="app-spec-a branch_id layui-btn layui-btn-xs {{ i == 0 ? 'layui-btn-normal' : 'layui-btn-primary' }}" data-value="{{ item.id }}" data-price="{{ item.price }}">{{ item.name }}</a>
                    {{# i++; }}
                {{#  }); }}
            </dd>
        </dl>
        <dl class="app-spec">
            <dt>支付方式：</dt>
            <dd>
                <a href="javascript:void(0);" data-value="wechat" class="layui-btn layui-btn-xs layui-btn-normal payment app-spec-a">微信支付</a>
                <a href="javascript:void(0);" data-value="alipay" class="layui-btn layui-btn-xs layui-btn-primary payment app-spec-a">支付宝支付</a>
            </dd>
        </dl>
        <dl class="app-spec">
            <dt>特别说明：</dt>
            <dd>
                <a href="javascript:void(0);" class="layui-btn layui-btn-xs layui-btn-danger">★本应用为单域名授权，购买成功后不支持域名变更★</a>
            </dd>
        </dl>
        <div class="layui-form-item app-info">
            <div class="app-price"></div>
            <div class="app-qrocde">
                <img src="" style="display:none;" id="qrcode" width="200" height="200" />
            </div>
            <div class="app-order-id"></div>
        </div>
    </div>
</script>
<script type="text/html" id="toolbar">
    <dl class="apps-filter-tr">
        <dt>应用类型：</dt>
        <dd>
            <a href="javascript:void(0);" id="type1" data-type="1" data-id="1" class="layui-btn layui-btn-primary layui-btn-xs app-filter">模块</a>
            <a href="javascript:void(0);" id="type2" data-type="1" data-id="2" class="layui-btn layui-btn-primary layui-btn-xs app-filter">插件</a>
            <a href="javascript:void(0);" id="type3" data-type="1" data-id="3" class="layui-btn layui-btn-primary layui-btn-xs app-filter">主题</a>
        </dd>
    </dl>
<!--     <dl class="apps-filter-tr">
        <dt>应用分类：</dt>
        <dd>
            <a href="javascript:void(0);" id="cats0" data-id="0" class="layui-btn layui-btn-primary layui-btn-xs app-filter">所有</a>
            {volist name="$data['cats']" id="vo"}
                <a href="javascript:void(0);" data-type="2" id="cats{$vo['id']}" data-id="{$vo['id']}" class="layui-btn layui-btn-primary layui-btn-xs app-filter">{$vo['name']}</a>
            {/volist}
        </dd>
    </dl> -->
</script>
<script type="text/html" id="buttonTpl">
    {{# if (d.install) { }}
        {{# if (d.upgrade == 1) { }}
            <a href="{:url('upgrade/lists')}?identifier={{ d.install }}" target="_blank" class="layui-btn layui-btn-xs">升级</a>
        {{# } else { }}
            <a href="javascript:void(0);" title="暂无新版本" class="layui-btn layui-btn-xs layui-disabled">升级</a>
        {{# } }}
    {{# } else { }}
        <a href="#{:url('install')}?app_id={{ d.id }}" data-data='{{ JSON.stringify(d) }}' class="layui-btn layui-btn-xs layui-btn-normal pop-install">安装</a>
    {{# } }}
    {{# if (d.preview_url) { }}
        <a href="{{ d.preview_url }}" class="layui-btn layui-btn-xs layui-btn-primary">演示</a>
    {{# } }}
</script>
{include file="block/bind_cloud" /}