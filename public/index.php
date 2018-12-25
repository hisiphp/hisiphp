<?php
// +----------------------------------------------------------------------
// | HisiPHP框架[基于ThinkPHP5.1开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 http://www.HisiPHP.com
// +----------------------------------------------------------------------
// | HisiPHP提供个人非商业用途免费使用，商业需授权。
// +----------------------------------------------------------------------
// | Author: 橘子俊 <364666827@qq.com>，开发者QQ群：50304283
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
namespace think;

header('Content-Type:text/html;charset=utf-8');

// 检测PHP环境
if(version_compare(PHP_VERSION,'5.6.0','<'))  die('PHP版本过低，最少需要PHP5.6，请升级PHP版本！');

// 定义应用目录
define('APP_PATH', __DIR__ . '/application/');

// 加载基础文件
require __DIR__ . '/../thinkphp/base.php';

// 检查是否安装
if(!is_file('./../install.lock')) {

	define('INSTALL_ENTRANCE', true);
    Container::get('app')->bind('install')->run()->send();

} else {

    Container::get('app')->run()->send();
    
}