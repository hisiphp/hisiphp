<?php
// +----------------------------------------------------------------------
// | HisiPHP框架[基于ThinkPHP5.1开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 http://www.HisiPHP.com
// +----------------------------------------------------------------------
// | HisiPHP承诺基础框架永久免费开源，您可用于学习和商用，但必须保留软件版权信息。
// +----------------------------------------------------------------------
// | Author: 橘子俊 <364666827@qq.com>，开发者QQ群：50304283
// +----------------------------------------------------------------------
namespace app\common\behavior;

use Env;
use Request;
use Route;
use think\Container;

/**
 * 应用初始化行为
 */
class Init
{
    public function run()
    {
        define('ROOT_PATH', Env::get('root_path'));
        define('IN_SYSTEM', true);
        
        if (defined('INSTALL_ENTRANCE')) return;

        // 设置前台默认模块
        if (empty(Route::getBind()) && !defined('ENTRANCE')) {

            $map    = [];
            $map[]  = ['default', '=', 1];
            $map[]  = ['status', '=', 2];
            $modName = model('system/SystemModule')->where($map)->value('name');
            if ($modName) {
                Container::get('app')->bind($modName);
            }
            
        }

        // 系统版本
        $version = include_once(Env::get('root_path').'version.php');
        config($version);
    }
}
