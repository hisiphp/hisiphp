<?php
// +----------------------------------------------------------------------
// | HisiPHP框架[基于ThinkPHP5开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 http://www.hisiphp.com
// +----------------------------------------------------------------------
// | HisiPHP承诺基础框架永久免费开源，您可用于学习和商用，但必须保留软件版权信息。
// +----------------------------------------------------------------------
// | Author: 橘子俊 <364666827@qq.com>，开发者QQ群：50304283
// +----------------------------------------------------------------------
// [ 应用入口文件 ]
header('Content-Type:text/html;charset=utf-8');
// 检测PHP环境
if(version_compare(PHP_VERSION,'5.5.0','<'))  die('PHP版本过低，最少需要PHP5.5，请升级PHP版本！');
// 定义应用目录
define('APP_PATH', __DIR__ . '/app/');
// 检查是否安装
if(!is_file(APP_PATH.'install/install.lock')) {
    if (!is_writable(__DIR__ . '/runtime')) {
        echo '请开启[runtime]文件夹的读写权限';
        exit;
    }
    define('BIND_MODULE', 'install');
}
// 加载框架引导文件
require __DIR__ . '/thinkphp/start.php';
