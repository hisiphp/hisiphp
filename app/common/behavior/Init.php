<?php
// +----------------------------------------------------------------------
// | HisiPHP框架[基于ThinkPHP5开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 http://www.HisiPHP.com
// +----------------------------------------------------------------------
// | HisiPHP提供个人非商业用途免费使用，商业需授权。
// +----------------------------------------------------------------------
// | Author: 橘子俊 <364666827@qq.com>，开发者QQ群：50304283
// +----------------------------------------------------------------------
namespace app\common\behavior;
use app\admin\model\AdminModule as ModuleModel;
/**
 * 应用初始化行为
 */
class Init
{
    public function run(&$params)
    {
        define('IN_SYSTEM', true);
        // 安装操作直接return
        if(defined('BIND_MODULE') && BIND_MODULE == 'install') return;
        $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/';
        $default_module = false;
        if ($path_info != '/') {
            $_path_info = explode('/', $path_info);
            if (isset($_path_info[1]) && !empty($_path_info[1])) {
                if (is_dir('./app/'.$_path_info[1]) || $_path_info[1] == 'plugins') {
                    $default_module = true;
                    if ($_path_info[1] == 'plugins') {
                        define('BIND_MODULE', 'index');
                        define('PLUGIN_ENTRANCE', true);
                    }
                }
            }

        }
        // 设置路由
        config('route_config_file', ModuleModel::moduleRoute());
        if (!defined('PLUGIN_ENTRANCE') && !defined('CLOUD_ENTRANCE') && $default_module === false) {
            // 设置前台默认模块
            $map = [];
            $map['default'] = 1;
            $map['status'] = 2;
            $map['name'] =  ['neq', 'admin'];
            $def_mod = ModuleModel::where($map)->value('name');
            if ($def_mod && !defined('ENTRANCE')) {
                define('BIND_MODULE', $def_mod);
                config('url_controller_layer', 'home');
            }
        }
    }
}
