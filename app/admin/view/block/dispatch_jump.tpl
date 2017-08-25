{__NOLAYOUT__}<!DOCTYPE html>
<html>
<head>
    <title>跳转提示</title>
    <meta http-equiv="Access-Control-Allow-Origin" content="*">
    <link rel="stylesheet" href="__ADMIN_CSS__/style.css">
    <style type="text/css">
        .dispatch-head{position:fixed;left:0;top:0;width:80%;height:60px;background:#000;padding:0 10%;}
        .dispatch-head h1{color:#fff;font-size:20px;font-weight:600}
        .dispatch-box{margin:200px auto 0;background:#fff;border-radius:10px;padding:30px 20px 15px 20px;overflow:hidden;box-shadow: 5px 5px 15px #888888;display:inline-block;}
        .dispatch-message{line-height:28px;display:block;overflow:hidden;font-size:24px;color:#444;text-align:left;min-width:360px;max-width:800px;padding:0 0 20px 0;}
        .dispatch-message .aicon{font-size:24px;}
        .dispatch-message .ai-error{color:#f00;}
        .dispatch-message .ai-success{color:#75b05e;}
        .dispatch-jump{font-size:12px;display:block;text-align:right;}
    </style>
</head>
<body>
    <div class="dispatch-head">
        <h1>跳转提示</h1>
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
</body>
</html>
