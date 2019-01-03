<?php
// +----------------------------------------------------------------------
// | HisiPHP框架[基于ThinkPHP5.1开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2021 http://www.HisiPHP.com
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
use app\system\model\SystemModule as SystemModule;

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

        $path = Request::instance()->pathinfo();
        $bind = Route::getBind();

        if ($path != '/' && strtolower($path) != 'index' && !$bind) {

            $path = explode('/', $path);

            if (isset($path[0]) && !empty($path[0])) {

                if (is_dir(Env::get('app_path').'/'.$path[0])) {
                    $bind = $path[0];
                }

            }

        }

        // 设置前台默认模块
        if (!defined('ENTRANCE') && !$bind) {

            $map    = [];
            $map[]  = ['default', '=', 1];
            $map[]  = ['status', '=', 2];

            if ($name = SystemModule::where($map)->value('name')) {
                Container::get('app')->bind($name);
            }
            
        }

        // 系统版本
        $version = include_once(Env::get('root_path').'version.php');
        config($version);
    }
}
