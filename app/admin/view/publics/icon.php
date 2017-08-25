<!DOCTYPE html>
<html>
<head>
    <title>图标选择</title>
    <meta http-equiv="Access-Control-Allow-Origin" content="*">
    <link rel="stylesheet" href="__ADMIN_JS__/layui/css/layui.css">
    <link rel="stylesheet" href="__ADMIN_CSS__/style.css">
    <style type="text/css">
        .icon-list{}
        .icon-list li{ float:left;width:140px;margin:1px;text-align:center;background:#f2f2f2;height:80px;overflow:hidden;padding:5px 0;cursor:pointer;}
        .icon-list li i{font-size:30px!important;margin-bottom:10px;}
        .icon-list li span{display:block;}
        .icon-list li:hover{background:#aaa;color:#fff;}
        .page-tab-content{overflow:hidden;}
    </style>
</head>
<body align="center">
    <div class="layui-tab layui-tab-card">
        <ul class="layui-tab-title">
            <li class="layui-this"><a href="javascript:;">系统默认图标</a></li>
        </ul>
        <div class="layui-tab-content page-tab-content">
            <div class="layui-tab-item layui-show">
                <ul class="icon-list">
                    <li><i class="aicon ai-shezhi"></i><span>aicon ai-shezhi</span></li>
                    <li><i class="aicon ai-icon01"></i><span>aicon ai-icon01</span></li>
                    <li><i class="aicon ai-tuichu"></i><span>aicon ai-tuichu</span></li>
                    <li><i class="aicon ai-shouyeshouye"></i><span>aicon ai-shouyeshouye</span></li>
                    <li><i class="aicon ai-shuaxin2"></i><span>aicon ai-shuaxin2</span></li>
                    <li><i class="aicon ai-jinyong"></i><span>aicon ai-jinyong</span></li>
                    <li><i class="aicon ai-tianjia"></i><span>aicon ai-tianjia</span></li>
                    <li><i class="aicon ai-caidan"></i><span>aicon ai-caidan</span></li>
                    <li><i class="aicon ai-clear"></i><span>aicon ai-clear</span></li>
                    <li><i class="aicon ai-jinyong1"></i><span>aicon ai-jinyong1</span></li>
                    <li><i class="aicon ai-qiyong"></i><span>aicon ai-qiyong</span></li>
                    <li><i class="aicon ai-mokuaiguanli"></i><span>aicon ai-mokuaiguanli</span></li>
                    <li><i class="aicon ai-quanping"></i><span>aicon ai-quanping</span></li>
                    <li><i class="aicon ai-fanhui"></i><span>aicon ai-fanhui</span></li>
                    <li><i class="aicon ai-quanping1"></i><span>aicon ai-quanping1</span></li>
                </ul>
            </div>
        </div>
    </div>
<script src="__ADMIN_JS__/layui/layui.js"></script>
<script type="text/javascript">
layui.use(['jquery', 'element'], function() {
    var $ = layui.jquery, element = layui.element();
    var index = parent.layer.getFrameIndex(window.name);
    $('.icon-list li').click(function(){
        var _val = $(this).find('span').html();
        parent.document.getElementById('{:input("param.input/s")}').value = _val;
        parent.document.getElementById('{:input("param.show/s")}').setAttribute('class', _val);
        parent.layer.close(index);
    });
});
</script>
</body>
</html>