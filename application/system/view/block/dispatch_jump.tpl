{__NOLAYOUT__}<!DOCTYPE html>
<html>
<head>
<title>跳转提示</title>
<!-- 针对移动端优化 -->
<meta name="viewport" content="width=device-width,initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta name="apple-mobile-web-app-status-bar-style" content="black" />
<meta name="apple-mobile-web-app-capable" content="yes" /><!-- 删除苹果默认的工具栏和菜单栏 -->
<meta name="apple-mobile-web-app-status-bar-style" content="black" /><!-- 设置苹果工具栏颜色 -->
<meta name="format-detection" content="telephone=no, email=no" /><!-- 忽略页面中的数字识别为电话，忽略email识别 -->
<link rel="stylesheet" href="__ADMIN_CSS__/style.css">
<style type="text/css">
    .dispatch-head{position:fixed;left:0;top:0;width:80%;height:60px;background:#000;padding:0 10%;}
    .dispatch-head h1{color:#fff;font-size:20px;font-weight:600}
    .dispatch-box{margin:200px auto 0;background:#fff;border-radius:10px;padding:30px 20px 15px 20px;overflow:hidden;box-shadow: 1px 1px 5px #888888;display:inline-block;}
    .dispatch-message{line-height:28px;display:block;overflow:hidden;font-size:24px;color:#444;text-align:left;padding:0 0 20px 0;}
    .dispatch-message .aicon{font-size:24px;}
    .dispatch-message .ai-error{color:#f00;}
    .dispatch-message .ai-success{color:#75b05e;}
    .dispatch-jump{font-size:12px;display:block;text-align:right;}
</style>
</head>
<body>
    <?php
        if ((input('param.hisi_iframe') || cookie('hisi_iframe')) && defined('ENTRANCE')) {
    ?>
        <div style="text-align:center;">
            <div class="dispatch-box" style="margin:100px auto 0">
                <div class="dispatch-message">
                    <?php switch ($code) {?>
                        <?php case 1:?>
                            <div class="success"><i class="aicon ai-success">&nbsp;</i><span><?php echo($msg);?></span></div>
                        <?php break;?>
                        <?php case 0:?>
                            <div class="error"><i class="aicon ai-error">&nbsp;</i><span><?php echo($msg);?></span></div>
                        <?php break;?>
                    <?php } ?>
                </div>
            </div>
        </div>
    <?php
        } else {
    ?>
        <div class="dispatch-head">
            <h1>操作提示</h1>
        </div>
        <div style="text-align:center;">
            <div class="dispatch-box">
                <div class="dispatch-message">
                    <?php switch ($code) {?>
                        <?php case 1:?>
                            <div class="success"><i class="aicon ai-success">&nbsp;</i><span><?php echo($msg);?></span></div>
                        <?php break;?>
                        <?php case 0:?>
                            <div class="error"><i class="aicon ai-error">&nbsp;</i><span><?php echo($msg);?></span></div>
                        <?php break;?>
                    <?php } ?>
                </div>
                <div class="dispatch-jump">
                    页面将在 <b id="wait"><?php echo($wait);?></b> 秒后自动<a id="href" href="<?php echo($url);?>">跳转</a>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            (function(){
                var wait = document.getElementById('wait'),
                    href = document.getElementById('href').href;
                var interval = setInterval(function(){
                    var time = --wait.innerHTML;
                    if(time <= 0) {
                        location.href = href;
                        clearInterval(interval);
                    };
                }, 1000);
            })();
        </script>
    <?php
    }
    ?>
</body>
</html>