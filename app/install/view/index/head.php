<!DOCTYPE html>
<html>
<head>
    <title> 系统安装 - Powered by {:config('hisiphp.name')}</title>
    <meta http-equiv="Access-Control-Allow-Origin" content="*">
    <link rel="stylesheet" href="static/admin/js/layui/css/layui.css">
    <link rel="stylesheet" href="static/admin/css/style.css">
    <link rel="stylesheet" href="static/admin/css/install.css">
    <script src="static/admin/js/layui/layui.js"></script>
    <script>
    layui.config({
      base: 'static/admin/js/',
      version: '{:time()}'
    }).use('global');
    </script>
</head>
<body>
<div class="header">
    <h1>感谢您选择{:config('hisiphp.name')}</h1>
</div>